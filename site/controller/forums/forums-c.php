<?php
namespace RevenantBlue\Site;
use RevenantBlue\Pager;
use RevenantBlue\ThumbnailGenerator;
use \DateTime;
use \stdClass;

require_once DIR_APPLICATION . 'controller/common/site-c.php';
require_once DIR_APPLICATION . 'controller/forums/forum-hierarchy.php';
require_once DIR_APPLICATION . 'model/forums/forums-main.php';

$forums = new Forums;
// Get the list of roles.
$roles = $acl->loadRoles();

$defaultAvatar = HTTP_AVATARS . 'default/' . $globalSettings['avatar_location']['value'];
$defaultSmallAvatar = HTTP_AVATARS . 'default/' . $globalSettings['small_avatar_location']['value'];

// Load the role permissions for all forums
if(REDIS === TRUE) {
	$forumRolePerms = $redis->get(PREFIX . 'forum:rolePermissions', TRUE);
	
	if(empty($forumRolePerms)) {
		$redis->set(PREFIX . 'forum:rolePermissions', json_encode(getRoleForumPermissions()));
		$forumRolePerms = $redis->get(PREFIX . 'forum:rolePermissions', TRUE);
	}
} else {
	$forumRolePerms = getRoleForumPermissions();
}

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	
	// Set the user's friends and favorite topics and check for moderation privileges.
	if(isset($_SESSION['userId'])) {
		// Set the user's friends
		if(!isset($_SESSION['userFriends'])) {
			$numOfFriends = (int)$users->countFriendsForUser($_SESSION['userId']);
			$userFriends = $users->getFriendsForUser($_SESSION['userId'], $numOfFriends);
			foreach($userFriends as $userFriend) {
				$friends[$userFriend['id']] = $userFriend;
			}
			if(!empty($friends)) {
				$_SESSION['friends'] = $friends;
			}
		}
		
		// Get the user's favorite topics.
		if(REDIS === TRUE) {
			$favoriteTopics = $redis->get(PREFIX . 'user:' . $_SESSION['userId'] . ':favoriteTopics', TRUE);
			if(empty($favoriteTopics)) {
				$redis->set(PREFIX . 'user:' . $_SESSION['userId'] . ':favoriteTopics', json_encode($forums->getFavoriteTopics($_SESSION['userId'])));
				$favoriteTopics = $redis->get(PREFIX . 'user:' . $_SESSION['userId'] . ':favoriteTopics', TRUE);
			}
		} else {
			$favoriteTopics = $forums->getFavoriteTopics($_SESSION['userId']);
		}
		
		if(isset($_SESSION['moderatorPerms']) && isset($_SESSION['forumsToModerate'])) {
			$moderatorPerms = $_SESSION['moderatorPerms'];
			$forumsToModerate = $_SESSION['forumsToModerate'];
		}
		
		if(empty($moderatorPerms) && REDIS === TRUE && $redis->hexists(PREFIX . 'moderatorPerms', $_SESSION['userId'])) {
			$moderatorPerms = json_decode($redis->hget(PREFIX . 'moderatorPerms', $_SESSION['userId']), TRUE);
			$forumsToModerate = json_decode($redis->hget(PREFIX . 'forumsToModerate', $_SESSION['userId']), TRUE);
		}
		
		// Verify if the user is a moderator and build their permission structure.
		if(!isset($_SESSION['isModerator']) || ($_SESSION['isModerator'] === TRUE && ((REDIS === TRUE && !$redis->hexists(PREFIX . 'moderatorPerms', $_SESSION['userId'])) || !isset($_SESSION['moderatorPerms'])))) {
			if(empty($moderatorPerms)) {
				// Build the moderator permissions array
				// $moderatorPerms[0] is an array of forum ids that the user has mod privileges for
				// $moderatorPerms[1] is an array of the permissions for each forum indexed by forum id
				$moderatorPerms = buildModeratorPermissions();
				$forumsToModerate = $moderatorPerms[0];
				$moderatorPerms = $moderatorPerms[1];
			}
		}
		
		// If moderatorPerms is set falg it with the session variable and $isModerator variable
		if(!empty($moderatorPerms)) {
			$_SESSION['isModerator'] = $isModerator = TRUE;
		}
	}
	
	//print_r2($moderatorPerms); exit;
	
	if(isset($_GET['controller'])) {
		
		if($_GET['controller'] === 'forums') {
			$totalRootNodes = (int)$forums->countForums();
			$totalForums = $forums->countAllForums();
			$rootNodes = $forums->getRootNodeIds();
			
			// Only load the forums if root nodes exist.
			if($totalRootNodes !== 0) {
				
				ForumHierarchy::$forumRolePerms = $forumRolePerms;
				$forumList = ForumHierarchy::buildForums($totalRootNodes, 0);
				
				$forumList = json_encode($forumList);
			
				// Load the recent topic list
				$recentTopics = $forums->loadRecentTopics(50, 0, 'last_reply_date', 'DESC');

				// Make changes to the recent topics list for moderators and for viewing perms.
				
				foreach($recentTopics as $key => $recentTopic) {
					// Fitler out any recent topics that shouldn't be viewed by the user
					
					// Get the forum permissions for the user
					$forumPerms = getForumPermsForUser($_SESSION['roles'], $forumRolePerms, $recentTopic['forum_id']);
					
					if(empty($forumPerms['view-forum'])) {
						unset($recentTopics[$key]);
					}
					
					// Get moderator permissions if available.
					if(isset($_SESSION['isModerator'])) {
						if(isset($forumsToModerate)) {
							if(in_array($recentTopic['forum_id'], $forumsToModerate)) {
								$recentTopics[$key]['canModerate'] = TRUE;
							}
						}
					} elseif(isset($_SESSION['forumsToModerate'])) {
						if(in_array($recentTopic['forum_id'], $_SESSION['forumsToModerate'])) {
							$recentTopics[$key]['canModerate'] = TRUE;
						}
					}
				}
				
				// If the user is logged in load their favorite topics.
				if(isset($_SESSION['userId'])) {
					$favoriteTopics = $forums->loadFavoriteTopics(100, 0, $_SESSION['userId'], 'last_reply_date', 'DESC');
				}

				// Ensure all favorite topics are still viewable by the user
				if(!empty($favoriteTopics)) {
					foreach($favoriteTopics as $key => $favoriteTopic) {
						// Get the forum permissions for the user
						$forumPerms = getForumPermsForUser($_SESSION['roles'], $forumRolePerms, $favoriteTopic['forum_id']);
						
						if(empty($forumPerms['view-forum'])) {
							unset($favoriteTopics[$key]);
						}
					}
				}
				
				$activeUsers['anon'] = array();
				$activeUsers['loggedIn'] = array();
				
				// Get the active user list
				if(REDIS_SESSIONS === TRUE) {
					$activeUsers['anon'] = $rdh->zrangebyscore(PREFIX . 'frontend-anon-users-online', time() - 1800, time());
					$activeUsers['loggedIn'] = $rdh->zrangebyscore(PREFIX . 'frontend-logged-users-online', time() - 1800, time());
				} else {
					session_write_close();
					$activeAnonUsers = $users->getActiveSessionsBySection('frontend', time(), 1800,  FALSE);
					$activeLoggedUsers = $users->getActiveSessionsBySection('frontend', time(), 1800,  TRUE);
					session_start();
					
					$activeUsers['anon'] = array();
					$activeUsers['loggedIn'] = array();
					
					foreach($activeAnonUsers as $activeUser) {
						$activeUsers['anon'][] = $activeUser['user_data_json'];
					}
					
					foreach($activeLoggedUsers as $activeUser) {
						$activeUsers['loggedIn'][] = $activeUser['user_data_json'];
					}
				}
				
				$activeUsers['total'] = count($activeUsers['anon']) + count($activeUsers['loggedIn']);
				$activeUsers['anonCount'] = count($activeUsers['anon']);
				$activeUsers['loggedCount'] = count($activeUsers['loggedIn']);
			}
		} else if($_GET['controller'] === 'forum-topics') {
			// Set the topic limit
			$topicLimit = 30;
			
			if(isset($_GET['forum'])) {
				// Get the parent forum
				$parentForumId = $forums->getParentId((int)$_GET['id']);
				$parentForum = $forums->getForumById($parentForumId);
				$currentForum = $forums->getForumById((int)$_GET['id']);
				$parentParentId = $forums->getParentId($parentForum['id']);
				
				if(!empty($parentParentId)) {
					$parentParentForum = $forums->getForumById($parentParentId);
				}
				
				// Check if user is a moderator for the forum
				if(isset($_SESSION['userId']) && REDIS === TRUE && $redis->hexists(PREFIX . 'forumsToModerate', $_SESSION['userId'])) {
					$forumsToModerate = json_decode($redis->hget(PREFIX . 'forumsToModerate', $_SESSION['userId']));
					if(in_array($currentForum['id'], $forumsToModerate)) {
						$isModerator = TRUE;
					}
				}
				// Get the forum permissions for the user
				$forumPerms = getForumPermsForUser($_SESSION['roles'], $forumRolePerms, $currentForum['id']);
				
				//print_r2($_SESSION['roles']);
				//print_r2($forumRolePerms);
				//print_R2($currentForum);
				//print_R2($forumPerms); exit;
				
			}

			// Loading a subforum container.
			if($currentForum['forum_type'] === 'subforum-container' || !$parentForumId) {
				// If a subforum container loaded
				if($currentForum['forum_type'] === 'subforum-container') {
					$subforumContainer = TRUE;
					// Set breadcrumbs
					$breadcrumbs = array(
						'0' => array(
							'title' => 'Forums',
							'url'   => HTTP_SERVER . 'forums'
						),
						'1' => array(
							'title' => $parentForum['forum_title'],
							'url'   => HTTP_SERVER . 'forums/' . $parentForum['forum_alias'] . '/' . $parentForum['id']
						)
					);
					// Get subforums.
					$forumList = $forums->getDescendantsWithoutSelf($currentForum['id']);
				} else {
					$forumSection = TRUE;
					// Set breadcrumbs
					$breadcrumbs = array(
						'0' => array(
							'title' => 'Forums',
							'url'   => HTTP_SERVER . 'forums'
						)
					);
					// Get subforums.
					ForumHierarchy::$forumRolePerms = $forumRolePerms; 
					$forumList = ForumHierarchy::buildSubForums($currentForum['id']);
					$forumList = json_encode($forumList);
				}
			} else {
				// Load the topics for the forum.
				
				// If the user can't view the forum send them a 404 page.
				if(empty($forumPerms['view-forum'])) {
					header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
					include_once DIR_SITE_ROOT . '404.php';
					exit;
				}
				
				// Set the breadcrumbs information.
				$breadcrumbs = array(
					'0' => array(
						'title' => 'Forums', 
						'url'   => HTTP_SERVER . 'forums'
					),
				);
				
				// Check to see if user has read topic permission
				if(!empty($forumPerms['read-topics']) || $currentForum['see_topic_list']) {
					// Add the parent forum's parent if it exists.
					if(!empty($parentParentForum)) {
						array_push($breadcrumbs, array(
							'title' => $parentParentForum['forum_title'],
							'url'   => HTTP_SERVER . 'forums/' . $parentParentForum['forum_alias'] . '/' . $parentParentForum['id']
						));
					}
					
					// Add the forum's parent.
					array_push($breadcrumbs, array(
						'title' => $parentForum['forum_title'], 
						'url'   => HTTP_SERVER . 'forums/' . $parentForum['forum_alias'] . '/' . $parentForum['id']
					));

					// Get subforums
					//$subforums = $forums->getDescendantsWithoutSelf($currentForum['id']);
					
					//print_r2($subforums);
					ForumHierarchy::$forumRolePerms = $forumRolePerms; 
					$subforums = ForumHierarchy::buildSubForums($currentForum['id'], FALSE);
					$subforums = ForumHierarchy::displayForums($subforums);
					
					// Build the topic pager.
					$pager = new Pager;
					$pager->limit = $_GET['limit'] = (int)$globalSettings['num_of_topics_to_show']['value'];
					$pager->offset = $_GET['offset'] = 0;
					
					// Load the topics.
					if(isset($_GET['sort']) && isset($_GET['sort']) && in_array($_GET['order'], $forums->whiteList)) {
						$topics = $forums->loadForumTopics($pager->limit, $pager->offset, (int)$_GET['id'], 0, $_GET['order'], $_GET['sort']);
					} else {
						$topics = $forums->loadForumTopics($pager->limit, $pager->offset, (int)$_GET['id'], 0, 'last_reply_date', 'desc');
						$pinnedTopics = $forums->loadForumTopics($pager->limit, $pager->offset, (int)$_GET['id'], 1, 'last_reply_date', 'desc');
						$topics = array_merge($pinnedTopics, $topics);
					}

					// Count the number of topics
					$numOfTopics = count($topics);
					
					$limit = $pager->limit;
					$offset = $pager->offset;
					
					// Build the 'Move Topic Forum List' for moderators.
					// They will only be shown forums where they have the 'move_topics' perm.
					
					//print_r2($moderatorPerms); 
					if(isset($moderatorPerms)) {
						$totalRootNodes = $forums->countForums();
						$totalForums = $forums->countAllForums();
						$rootNodes = $forums->getRootNodeIds();
						$moveTopicForumList = ForumHierarchy::buildForums($totalRootNodes, 0);
						$moveTopicForumList = ForumHierarchy::displayForums($moveTopicForumList);
						
						$topicMoveableForums = array_intersect_key($moderatorPerms, $moveTopicForumList);
						foreach($moveTopicForumList as $key => $forum) {
							if(!empty($topicMoveableForums[$key]['move_topics']) && $topicMoveableForums[$key]['move_topics'] == 1 && $key !== (int)$currentForum['id']) {
								$moveTopicForumList[$key]['moveable'] = true;
							}
						}
					}
				}
			}
			
		} else if($_GET['controller'] === 'forum-posts') {
			$pager = new Pager;
			$pager->limit = $_GET['limit'] = !empty($globalSettings['num_of_posts_to_show']['value']) ? (int)$globalSettings['num_of_posts_to_show']['value'] : 30;
			$pager->offset = $_GET['offset'] = 0;
			
			// Get the total number of posts for the topic.
			$totalNumOfPosts = (int)$forums->countPostsForTopic((int)$_GET['id']);
			// Load the posts for this topic.
			if(isset($_GET['postId']) || isset($_GET['page'])) {
				// If a page has been requested get the post number for the start of that page
				// Else if no page was requested use the postId to get the post number.
				if(isset($_GET['page'])) {
					$pageNum = (int)str_replace('page-', '', $_GET['page']);
					
					if($pageNum === 1) {
						$postNumber = 1;
						$pager->offset = $_GET['offset'] = 0;
					} else {
						$postNumber = (($pageNum - 1)  * (int)$globalSettings['num_of_posts_to_show']['value']);
						$pager->offset = $_GET['offset'] = $postNumber;
					}
				} elseif(isset($_GET['postId'])) {
					if($_GET['postId'] === 'first') {
						// Set the header to target the first post in the topic.
						$postNumber = 1;
						$postId = $forums->getPostIdByOrder($postNumber, (int)$_GET['id']);
						header('Location:' . str_replace('/first', '', $_SERVER['REQUEST_URI']));
						exit;
					} elseif($_GET['postId'] === 'last') {
						// Set the header to target the last post in the topic.
						$postNumber = $totalNumOfPosts;
						$postId = $forums->getPostIdByOrder($postNumber, (int)$_GET['id']);
						header('Location:' . str_replace('last', $postId . '#', $_SERVER['REQUEST_URI']));
						exit;
					} else {
						$postNumber = (int)$forums->getPostNumber((int)$_GET['postId']);
					}
				}
				
				//print_r2($_GET);
				//var_dump($postNumber); 
				//var_dump($pageNum); 
				//exit;
				if(isset($_GET['page'])) {
					// Load the posts
					$posts = $forums->loadForumPosts($pager->limit, $pager->offset, (int)$_GET['id'], 'date_posted', 'asc');
					// Set the last post number
					$lastPostNum = $postNumber + $pager->limit > $totalNumOfPosts ? $totalNumOfPosts : $postNumber + $pager->limit;
					if((int)$_GET['page'] === 1) {
						$noMorePostsUp = true;
					} elseif($postNumber + $pager->limit >= $totalNumOfPosts) {
						$noMorePostsDown = true;
					}
				} else {
					$split = $pager->limit / 2;
					// Check if the split fits the number of posts to show.
					if((int)$globalSettings['num_of_posts_to_show']['value'] >= $totalNumOfPosts) {
						//echo 'num_of_posts_to_show great than the toal num of posts';
						// Load the posts
						$posts = $forums->loadForumPosts($pager->limit, $pager->offset, (int)$_GET['id'], 'date_posted', 'asc');
						// Set the last post number
						$lastPostNum = $totalNumOfPosts;
					} elseif($postNumber - $split < 0) {
						//echo 'postNumber - split < 0';
						$split = $firstPostNum = 1;
						$lowerLimit = 1;

						// Let the frontend know that there are no more posts to show up.
						$noMorePostsUp = true;
						// Load the posts
						$posts = $forums->loadForumPostsByPost($pager->limit, $pager->offset, (int)$_GET['id'], $postNumber, $split, $lowerLimit, $totalNumOfPosts);
						// Set the last post number
						$lastPostNum = $postNumber + $split;
					} elseif($postNumber === $totalNumOfPosts && $postNumber > $pager->limit) {
						//echo 'postNumber === totalNumOfPosts && postNumber > $pager->limit';
						if($totalNumOfPosts < $pager->limit) {
							$_GET['offset'] = $pager->offset = 0;
						} else {
							$_GET['offset'] = $pager->offset = $totalNumOfPosts - $pager->limit;
						}
						$posts = $forums->loadForumPosts($pager->limit, $pager->offset, (int)$_GET['id'], 'date_posted', 'asc');
						$firstPostNum = $totalNumOfPosts - $pager->limit;
						$lastPostNum = $totalNumOfPosts;
						// Let the frontend know that there are no more posts down the page to load
						$noMorePostsDown = TRUE;
					} elseif($postNumber + $split > $totalNumOfPosts) {
						//echo '$postNumber + $split > $totalNumOfPosts';
						$overLimit = ($postNumber + $split) - $totalNumOfPosts;
						$pager->offset = $_GET['offset'] = $totalNumOfPosts - (int)$globalSettings['num_of_posts_to_show']['value'];
						$firstPostNum = $totalNumOfPosts - $pager->limit;
			
						// Let the frontend know that there are no more posts to show down
						$noMorePostsDown = true;
						// Load the posts.
						$posts = $forums->loadForumPosts($pager->limit, $pager->offset, (int)$_GET['id'], 'date_posted', 'asc');
						// Set the last post number
						$lastPostNum = $postNumber + $split;
					} else {
						$pager->offset = $_GET['offset'] = $firstPostNum = $postNumber - $split;
						// Load the posts
						$posts = $forums->loadForumPosts($pager->limit, $pager->offset, (int)$_GET['id'], 'date_posted', 'asc');
						// Set the last post number
						$lastPostNum = $postNumber + $split;
					}
				}

				if(empty($posts)) {
					// If no posts throw a 404 redirect
					header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
					include_once DIR_SITE_ROOT . '404.php';
					exit;
				}
			} else {
				$posts = $forums->loadForumPosts($pager->limit, $pager->offset, (int)$_GET['id'], 'date_posted', 'asc');
				$lastPostNum = $pager->limit;
				$noMorePostsUp = TRUE;
				if(count($posts) !== $pager->limit) {
					$noMorePostsDown = TRUE;
				}
			}
			
			// Get the posts's topic
			$topic = $forums->getTopic((int)$_GET['id']);
			$topicJSON = json_encode($topic);
			// Get the topic's forum
			$forum = $forums->getForumById($topic['forum_id']);
			$forumJSON = json_encode($forum);
			// Add a view to the topic
			$forums->incrementTopicViews($topic['id']);
			
			// Get the poll for the topic
			if(!empty($topic['poll_id'])) {
				$poll = $forums->getChoicesForPoll($topic['poll_id']);
				$poll = json_encode($poll);
				
				if(isset($_SESSION['userId'])) {
					$hasVoted = $forums->getVoteForUser((int)$topic['poll_id'], $_SESSION['userId']);
				}
			}
			
			$parentForumId = $forums->getParentId($topic['forum_id']);
			$parentForum = $forums->getForumById($topic['forum_id']);
			$currentForum = $forums->getForumById($topic['forum_id']);
			$parentParentId = $forums->getParentId($topic['forum_id']);
			
			// Get the forum permissions for the user
			$forumPerms = getForumPermsForUser($_SESSION['roles'], $forumRolePerms, $currentForum['id']);
			
			// If the user does not have permission to view the forum then show them a 404 page.
			if(empty($forumPerms['view-forum'])) {
				header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
				include_once DIR_SITE_ROOT . '404.php';
				exit;
			}
			
			if(!empty($parentParentId)) {
				$parentParentForum = $forums->getForumById($parentParentId);
			}
			// Set the breadcrumbs information.
			$breadcrumbs = array(
				'0' => array(
					'title' => 'Forums', 
					'url'   => HTTP_SERVER . 'forums'
				)
			);
			// Add the parent forum's parent if it exists.
			if(!empty($parentParentForum)) {
				array_push($breadcrumbs, array(
					'title' => $parentParentForum['forum_title'],
					'url'   => HTTP_SERVER . 'forums/' . $parentParentForum['forum_alias'] . '/' . $parentParentForum['id']
				));
			}
			// Add the forum's parent.
			array_push($breadcrumbs, array(
				'title' => $parentForum['forum_title'], 
				'url'   => HTTP_SERVER . 'forums/' . $parentForum['forum_alias'] . '/' . $parentForum['id']
			));
		
		} elseif($_GET['controller'] === 'forum-user') {
			if(empty($_GET['userId'])) {
				// If no user id is present send a 404 error
				header('HTTP/1.0 404 Not Found');
				include_once DIR_SITE_ROOT . '404.php';
				exit;
			}
			
			// Get user's username from the id given
			$username = isset($_GET['userId']) ? $users->getUsernameById((int)$_GET['userId']) : '';
			$userId = isset($_GET['userId']) ? (int)$_GET['userId'] : '';
			
			// Check to see if the user viewing the profile is the profile owner.
			$myProfile = isset($_SESSION['userId']) && $_SESSION['userId'] == $userId ? TRUE : '';
			
			// Increment the user's profile view count if the person viewing isn't the user currently logged in.
			if((!empty($_SESSION['userId']) && empty($myProfile)) || empty($_SESSION['userId'])) {
				$users->incrementProfileViews($userId);
			}
			
			$user = $users->getUserData($userId);
			
			// If the user messed up the URL and entered a user id that does not match the username alias presented.
			if((!empty($_GET['username']) && $_GET['username'] !== $user['username_alias']) || empty($_GET['username'])) {
				header('HTTP/1.0 404 Not Found');
				include_once DIR_SITE_ROOT . '404.php';
				exit;
			}
			
			// Check to see if the user has been active within 15 minutes, if so show them as online.
			if(REDIS_SESSIONS === TRUE) {
				$rdh = $redis->loadRedisHandler();
				$userLastActivity = $rdh->zscore(PREFIX . 'frontend-online-users', $user['username']);
				$userOnline = $userLastActivity >= time() - 900;
			} else {
				session_write_close();
				$userOnline = $users->getOnlineUser((int)$userId, 900);
				session_start();
			}

			// Load the topics started by the user
			$totalUserTopics = $forums->countNumOfUserTopics($userId);
			$topicPager = new Pager;
			$topicPager->limit = 20;
			$userTopics = $forums->loadUserTopics($topicPager->limit, $topicPager->offset, $userId);
			
			// Load recent posts for the user
			$totalUserPosts = $forums->countNumOfUserPosts($userId);
			$postPager = new Pager;
			$postPager->limit = 20;
			
			$recentPosts = $forums->loadUserPosts($postPager->limit, $postPager->offset, $userId);
			
			// Ensure the user has correct permissions for viewing each of the user's posts.
			foreach($recentPosts as $key => $recentPost) {
				// Get the forum permissions for the user
				$forumPerms = getForumPermsForUser($_SESSION['roles'], $forumRolePerms, $recentPost['forum_id']);
				
				if(empty($forumPerms['view-forum'])) {
					unset($recentPosts[$key]);
				}
			}
			
			$topRole = $acl->getHighestRankedRoleForUser($userId);
			//var_dump($topRole); exit;
			// Recent friends
			$numOfFriends = (int)$users->countFriendsForUser($userId);
			$recentFriends = $users->getFriendsForUser($userId, 20, 'id', 'DESC');
			$friends = $users->getFriendsForUser($userId, $numOfFriends);
			$friendIds = getFriendIds($friends);
			$profileViews = (int)$user['forum_profile_views'];
			$memberTitle = !empty($user['member_title']) ? $user['member_title'] : $topRole;
			$memberAge = !empty($user['birthday']) && $user['birthday'] !== '1969-12-31 00:00:00' && $user['birthday'] !== '1969-12-31 19:00:00'  ? calcUserAge($user['birthday']) : 'Age Unknown';
			$birthday = !empty($user['birthday']) && $user['birthday'] !== '1969-12-31 00:00:00' && $user['birthday'] !== '1969-12-31 19:00:00' ? date('F j, Y', strtotime($user['birthday'])) : 'Birthday Unknown';
			$gender = !empty($user['gender']) ? $user['gender'] : 'Not Specified';
			$location = !empty($user['location']) ? $user['location'] : '';
			$interests = !empty($user['interests']) ? $user['interests'] : '';
			
			// Pluralize the role.
			$topRole .= 's';

			// Build breadcrumbs.
			$breadcrumbs = array(
				'0' => array(
					'title' => 'Forums', 
					'url'   => HTTP_SERVER . 'forums'
				)
			);
		}
	}
}


if($_SERVER['REQUEST_METHOD'] === 'POST') {
	if(isset($_POST['action']) && $_POST['action'] === 'post-topic') {
		
		// Require the validation controller.
		require_once DIR_APPLICATION . 'controller/forums/forum-post-validation.php';
		
		$topic['id'] = isset($_POST['id']) ? $_POST['id'] : '';
		$topic['forumId'] = isset($_POST['forumId']) ? $_POST['forumId'] : '';
		$topic['title'] = isset($_POST['title']) ? $_POST['title'] : '';
		$topic['content'] = isset($_POST['content']) ? $_POST['content'] : '';
		$topic['published'] = 1;
		$topic['userId'] = isset($_SESSION['userId']) ? $_SESSION['userId'] : 1;
		$topic['username'] = isset($_SESSION['username']) ? $_SESSION['username'] : 'Anonymous';
		
		// Validate and insert the topic into the databases, TRUE denotes that we are validating a forum topic.
		$forumPostValidation = new ForumPostValidation($topic, TRUE);
		
		if(empty($forumPostValidation->errors)) {
			
			// Insert the topic.
			$newTopicId = $forums->insertTopic($forumPostValidation->topic);
			// Assign the topic Id to the validation object to ship to the databse.
			$forumPostValidation->topic['topicId'] = $newTopicId;
			
			// A new topic is also the first reply.
			$newPostId = $forums->insertPost($forumPostValidation->topic);
			
			// If a poll was attached then set the topic id for that poll
			if(!empty($_POST['pollId'])) {
				$forums->setTopicIdForPoll((int)$_POST['pollId'], $newTopicId);
			}
			
			// Increment the number of topics/posts for the forum and topic.
			$forums->incrementNumOfTopicsForForum($forumPostValidation->topic['forumId']);
			$forums->incrementNumOfPostsForForum($forumPostValidation->topic['forumId']);
			
			//$forums->incrementNumOfPostsForTopic($newTopicId);
			
			$dateOfLastPost = $forums->getDateForPost($newPostId);
			
			// Update the last post information for the forum.
			$forums->setForumLastPostUser(
				$forumPostValidation->forumId
			  , $forumPostValidation->userId
			  , $forumPostValidation->username
			  , $forumPostValidation->usernameAlias
			  , $forumPostValidation->topic['topicId']
			  , $dateOfLastPost
			);
			// Set the last post info for the topic.
			$forums->setTopicLastPostUser(
				$forumPostValidation->topicId
			  , $forumPostValidation->userId
			  , $forumPostValidation->username
			  , $forumPostValidation->usernameAlias
			  , $dateOfLastPost
			);
			
			$userId = isset($_SESSION['userId']) ? $_SESSION['userId'] : 1;
			
			// Increment the user's post count by one.
			if(REDIS === TRUE) {
				$currentPostCount = $redis->get(PREFIX . 'user:' . $userId . ':forumPostCount');
				if(!empty($currentPostCount)) {
					$redis->set(PREFIX . 'user:' . $userId . ':forumPostCount', (int)$currentPostCount + 1);
				} else {
					$redis->set(PREFIX . 'user:' . $userId . ':forumPostCount', 1);
				}
			}
			
			$users->incrementForumPostCount($userId);
			
			$topicLocation = HTTP_SERVER . 'forums/topic/' . $forumPostValidation->topic['alias'] . '/' . $newTopicId;
			
			// Notify users that have favorited this topic
			notifyReplyToFavTopic($newTopicId);
			
			// Notify at signed users
			notifyAtSignedUsers($forumPostValidation->content, $topicLocation . '/' . $newPostId);
			
			// Redirect to the newly created topic. 
			header('Location: ' . $topicLocation, TRUE, 302);
			exit;
		} else {
			$_SESSION['errors'] = $forumPostValidation->errors;
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['newPost'])) {
		
		// Inserting a new post to a topic.
		// Require the validation controller.
		require_once DIR_APPLICATION . 'controller/forums/forum-post-validation.php';
		
		$post['id'] = isset($_POST['id']) ? $_POST['id'] : '';
		$post['topicId'] = isset($_POST['topicId']) ? $_POST['topicId'] : '';
		$post['forumId'] = isset($_POST['forumId']) ? $_POST['forumId'] : '';
		$post['content'] = isset($_POST['content']) ? $_POST['content'] : '';
		$post['published'] = 1;
		$post['userId'] = isset($_SESSION['userId']) ? $_SESSION['userId'] : 1;
		$post['username'] = isset($_SESSION['username']) ? $_SESSION['username'] : 'Anonymous';
		
		// Validate and insert the topic into the databases
		$forumPostValidation = new ForumPostValidation($post);
		
		if(empty($forumPostValidation->errors)) {
			// Insert the post.
			$newPostId = $forums->insertPost($forumPostValidation->post);
			// Assign the topic Id to the validation object to ship to the databse.
			$forumPostValidation->post['postId'] = $newPostId;
			// Increment the number of posts for the forum and topic.
			$forums->incrementNumOfPostsForForum($forumPostValidation->post['forumId']);
			$forums->incrementNumOfPostsForTopic($forumPostValidation->post['topicId']);
			
			$dateOfLastPost = $forums->getDateForPost($newPostId);
			
			// Update the last post information for the forum.
			$forums->setForumLastPostUser(
				$forumPostValidation->forumId
			  , $forumPostValidation->userId
			  , $forumPostValidation->username
			  , $forumPostValidation->usernameAlias
			  , $forumPostValidation->post['topicId']
			  , $dateOfLastPost
			);
			// Set the last post info for the topic.
			$forums->setTopicLastPostUser(
				$forumPostValidation->topicId
			  , $forumPostValidation->userId
			  , $forumPostValidation->username
			  , $forumPostValidation->usernameAlias
			  , $dateOfLastPost
			);
			
			$userId = isset($_SESSION['userId']) ? $_SESSION['userId'] : 1;
			
			// Increment the cached user's post count by one.
			if(REDIS === TRUE) {
				$currentPostCount = $redis->get(PREFIX . 'user:' . $userId . ':forumPostCount');
				if(!empty($currentPostCount)) {
					$redis->set(PREFIX . 'user:' . $userId . ':forumPostCount', (int)$currentPostCount + 1);
				} else {
					$redis->set(PREFIX . 'user:' . $userId . ':forumPostCount', 1);
				}
			}
			
			// Increment the database user's posts by one.
			$users->incrementForumPostCount($userId);
			
			// Set the post location.
			$postLocation = HTTP_SERVER . 'forums/topic/' . $_POST['topicAlias']. '/' . (int)$post['topicId'] . '/' . $newPostId;
			
			// Notify users that have favorited this topic
			notifyReplyToFavTopic($forumPostValidation->topicId);
			
			// Notify at signed users in the post.
			notifyAtSignedUsers($forumPostValidation->content, $postLocation); 
			
			header('Location: ' . $postLocation, TRUE, 302);
			exit;
		} else {
			$_SESSION['errors'] = $forumPostValidation->errors;
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['updatePost'])) {
		
		// Require the validation controller.
		require_once DIR_APPLICATION . 'controller/forums/forum-post-validation.php';
		
		$post['id'] = isset($_POST['postId']) ? $_POST['postId'] : '';
		$post['topicId'] = isset($_POST['topicId']) ? $_POST['topicId'] : '';
		$post['forumId'] = isset($_POST['forumId']) ? $_POST['forumId'] : '';
		$post['topicAlias'] = isset($_POST['topicAlias']) ? $_POST['topicAlias'] : '';
		$post['content'] = isset($_POST['content']) ? $_POST['content'] : '';
		$post['userId'] = $_SESSION['userId'];
		$post['username'] = $_SESSION['username'];
		
		// Validate and upate the post.
		$forumPostValidation = new ForumPostValidation($post);
		// Update Post
		$postUpdated = $forums->updatePost($forumPostValidation->post);
		
		header('Location: ' . HTTP_SERVER . 'forums/topic/' . $post['topicAlias'] . '/' . (int)$post['topicId'] . '/' . (int)$post['id'], TRUE, 302);
		exit;
	} elseif(isset($_POST['appRequest'])) {
		$appReq = json_decode($_POST['appRequest']);
		if(isset($appReq->type) && isset($appReq->action) && $appReq->type === 'forum') {
			if($appReq->action === 'insert-poll') {
				if(!empty($appReq->choices) && !empty($appReq->title)) {
					$appReq->pollId = $forums->insertPoll($appReq->title);
					
					if(!empty($appReq->pollId)) {
						foreach($appReq->choices as $choice) {
							$forums->insertPollChoice((int)$appReq->pollId, $choice);
						}
					}
				}
			} elseif($appReq->action === 'favorite' || $appReq->action === 'remove-favorite') {
				if(!empty($_SESSION['userId'])) {
					// Check to see if the favorite exists.
					$favExists = $forums->getFavoriteTopic($_SESSION['userId'], (int)$appReq->topicId);
					
					if($appReq->action === 'favorite' && empty($favExists)) {
						$success = $forums->insertFavoriteTopic($_SESSION['userId'], (int)$appReq->topicId);
						if($success) {
							$favoriteTopics[] = (int)$appReq->topicId;
						}
					} elseif($appReq->action === 'remove-favorite' && !empty($favExists)) {
						$forums->deleteFavoriteTopic($_SESSION['userId'], (int)$appReq->topicId);
						
						if(REDIS === TRUE && $redis->exists(PREFIX . 'user:' . $_SESSION['userId'] . ':favoriteTopics')) {
							$currentFavTopics = $redis->get(PREFIX . 'user:' . $_SESSION['userId'] . ':favoriteTopics', TRUE);
							if(!empty($currentFavTopics)) {
								
								if(!empty($currentFavTopics)) {
									$key = array_search((int)$appReq->topicId, $currentFavTopics);
									
									$redis->del(PREFIX . 'user:' . $_SESSION['userId'] . ':favoriteTopics');

									if(!empty($key)) {
										unset($favoriteTopics[$key]);
									}
								}
							}
						}
					}
				} else {
					$appReq->error = 'Anonymous users cannot favorite topics.';
				}
			} elseif($appReq->action === 'load-more-topics') {
				// Get forum to check for permissions
				$forum = $forums->getForumById($appReq->forumId);
				
				if(!empty($_SESSION['userId'])) {
					// Load moderator permissions
					$moderatorPerms = loadPermsForModerator($_SESSION['userId']);
				}
				
				// Check if the user can read topics on this forum. 
				$forumPerms = getForumPermsForUser($_SESSION['roles'], $forumRolePerms , $forum['id']);
				
				if($forum['see_topic_list'] != 1 && (empty($forumPerms['view-forum']) || empty($forumPerms['read-topics']))) { 
					exit;
				}
				
				// Dynamically load more topics.
				$appReq->offset = (int)$appReq->offset + (int)$appReq->limit;
				if((empty($appReq->order) || empty($appReq->sort) || !in_array($appReq->order, $forums->whiteList))) {
					$appReq->topicList = $forums->loadForumTopics((int)$appReq->limit, $appReq->offset, (int)$appReq->forumId, 0, 'last_reply_date', 'DESC');
				} else {
					$appReq->topicList = $forums->loadForumTopics((int)$appReq->limit, (int)$appReq->offset, (int)$appReq->forumId, 0, $appReq->order, $appReq->sort);
				}
				
				if(!empty($appReq->topicList)) {
					foreach($appReq->topicList as $key => $topic) {
						$appReq->topicList[$key]['date_posted'] = nicetime($appReq->topicList[$key]['date_posted']);
						if(!empty($appReq->topicList[$key]['last_reply_date'])) {
							$appReq->topicList[$key]['last_reply_date'] = nicetime($appReq->topicList[$key]['last_reply_date']);
						} else {
							$appReq->topicList[$key]['last_reply_date'] = 'No Replies';
						}
						$appReq->topicList[$key]['username'] = $users->getUsernameById($appReq->topicList[$key]['user_id']);
					}
				}
				
				$numOfTopicsForForum = $forums->getNumOfTopicsForForum($appReq->forumId);
				
				if($numOfTopicsForForum <= ($appReq->offset) || (count($appReq->topicList) !== (int)$appReq->limit)) {
					$appReq->noMoreTopics = TRUE;
				}
			} elseif($appReq->action === 'update-topic-title') {
				if(aclVerify('frontend forum admin')) {
					
					if(empty($appReq->forumId)) {
						$topic = $forums->getTopic($appReq->topicId);
						$appReq->forumId = $topic['forum_id'];
					}
					
					$moderatorPerms = getModeratorPerms();
					
					// Update the topic title.
					if(!empty($moderatorPerms) && $moderatorPerms[$appReq->forumId]['edit_topics']) {
						$appReq->titleUpdated = $forums->setTopicTitle($appReq->topicId, $appReq->title);
						$appReq->title = hsc($appReq->title);
					}
				}
			} elseif($appReq->action === 'lock-topic') {
				if(aclVerify('frontend forum admin')) {
					$moderatorPerms = getModeratorPerms();
					if(!empty($moderatorPerms) && $moderatorPerms[$appReq->forumId]['open_topics']) {
						$forums->setTopicLock($appReq->topicId, 1);
					}
				}
			} else if($appReq->action === 'unlock-topic') {
				if(aclVerify('frontend forum admin')) {
					$moderatorPerms = getModeratorPerms();
					if(!empty($moderatorPerms) && $moderatorPerms[$appReq->forumId]['close_topics']) {
						$forums->setTopicLock($appReq->topicId, 0);
					}
				}
			} else if($appReq->action === 'pin-topic') {
				if(aclVerify('frontend forum admin')) {
					$moderatorPerms = getModeratorPerms();
					if(!empty($moderatorPerms) && $moderatorPerms[$appReq->forumId]['pin_topics']) {
						$forums->setTopicPin($appReq->topicId, 1);
					}
				}
			} else if($appReq->action === 'unpin-topic') {
				if(aclVerify('frontend forum admin')) {
					$moderatorPerms = getModeratorPerms();
					if(!empty($moderatorPerms) && $moderatorPerms[$appReq->forumId]['unpin_topics']) {
						$forums->setTopicPin($appReq->topicId, 0);
					}
				}
			} else if($appReq->action === 'move-topic') {
				if(aclVerify('frontend forum admin')) {
					$moderatorPerms = getModeratorPerms();
					if(!empty($moderatorPerms) && $moderatorPerms[$appReq->forumId]['move_topics']) {
						$appReq->topicMoved = $forums->moveTopic((int)$appReq->topicId, (int)$appReq->moveTo);
					}
				}
			} elseif($appReq->action === 'delete-topic') {
				// Make sure the user has the right to delete topics.
				if(aclVerify('frontend forum admin')) {
					
					if(empty($appReq->forumId)) {
						$topic = $forums->getTopic($appReq->topicId);
						$appReq->forumId = $topic['forum_id'];
					}
					

					if(!empty($moderatorPerms) && $moderatorPerms[$appReq->forumId]['delete_topics']) {
						$topicId = (int)$appReq->topicId;
						$numOfPosts = $forums->countPostsForTopic($topicId);
						$appReq->topicDeleted = $forums->deleteTopic($topicId);
						
						// Decrement the number of posts for the forum and topic.
						$forums->decrementNumOfTopicsForForum($topicId);
						$forums->decrementNumOfPostsForTopic($topicId);
					}
				}
			} elseif($appReq->action === 'delete-post') {
				if(aclVerify('frontend forum admin')) {
					if(REDIS === TRUE && $redis->hexists(PREFIX . 'moderatorPerms', $_SESSION['userId'])) {
						$moderatorPerms = json_decode($redis->hget(PREFIX . 'moderatorPerms', $_SESSION['userId']), TRUE);
					} elseif(isset($_SESSION['moderatorPerms'])) {
						$moderatorPerms = $_SESSION['moderatorPerms'];
					}
					if(!empty($moderatorPerms) && $moderatorPerms[$appReq->forumId]['delete_posts']) {
						if(!empty($appReq->topicId) && !empty($appReq->forumId)) {
							$appReq->postDeleted = $forums->deletePost((int)$appReq->postId, (int)$appReq->topicId);
							// Decrement user's post count
							$postUserId = $forums->getUserIdForPost((int)$appReq->postId);
							$users->decrementForumPostCount((int)$appReq->userId);
							// Decrement the number of posts for the forum and topic where the post was located.
							$forums->decrementNumOfPostsForForum((int)$appReq->forumId);
							$forums->decrementNumOfPostsForTopic((int)$appReq->topicId);
						}
					}
				}
			} elseif($appReq->action === 'load-more-posts-down') {
				$limit = (int)$appReq->limit;
				$offset = (int)$appReq->offset;
				$topicId = (int)$appReq->topicId;
				
				// Get the total number of posts for the topic.
				$totalNumOfPosts = $forums->countPostsForTopic($topicId);
				
				// Load more posts.
				$appReq->posts = $forums->loadForumPosts($limit, $offset, $topicId, 'date_posted', 'asc');
			
				// Check if there are more posts to load
				if($totalNumOfPosts <= $offset + $limit) {
					$appReq->noMorePostsDown = TRUE;
				} else if($offset === 0) {
					$appReq->noMorePostsUp = TRUE;
				}
				
				$appReq = buildPosts($appReq->posts, $appReq, 'down');
			} elseif($appReq->action === 'load-more-posts-up') {
				$limit = (int)$appReq->limit;
				$offset = (int)$appReq->offset;
				$topicId = (int)$appReq->topicId;
				
				// Get the total number of posts for the topic.
				$totalNumOfPosts = $forums->countPostsForTopic($topicId);
				
				// Load more posts.
				$appReq->posts = $forums->loadForumPosts($limit, $offset, $topicId, 'date_posted', 'asc');
			
				// Check if there are more posts to load
				if($totalNumOfPosts <= $offset + $limit) {
					$appReq->noMorePostsDown = TRUE;
				} else if($offset === 0) {
					$appReq->noMorePostsUp = TRUE;
				}
				
				$appReq = buildPosts($appReq->posts, $appReq, 'up');
				
				$appReq->posts = array_reverse($appReq->posts);
			} elseif($appReq->action === 'add-friend') {
				$friendId = (int)$appReq->friendId;
				$friendUsername = $users->getUsernameById($friendId);
				// Check if friend requires authorization
				
				// Check if the receiving user wants to be notified of a message.
				$authRequired = $users->getFriendAuthorization($friendId);

				if(!empty($authRequired) && $authRequired == 1) {
					
					$message = array(
						'senderId'          => $_SESSION['userId']
					  , 'recipientId'       => $friendId
					  , 'subject'           => 'You have received friend request from ' . $_SESSION['username']
					  , 'content'           => ''
					  , 'senderUsername'    => $_SESSION['username']
					  , 'recipientUsername' => $friendUsername
					);
					// Send a message to the friend asking for authorization.
					$users->insertPrivateMessage($message, TRUE, TRUE);
					
					$appReq->friendRequest = TRUE;
				} else {
					// Insert the user's friend.
					$appReq->friendAdded = $users->addFriend($_SESSION['userId'], $friendId);
					$friendData = $users->getUserData($friendId);
					$userData = $users->getUserData($_SESSION['userId']);
					// Add the friend to the friend session array.
					$friend[$friendId] = array(
						'id'           => $friendId,
						'avatar'       => $friendData['avatar'],
						'avatar_small' => $friendData['avatar_small']
					);
					
					if(!empty($_SESSION['friends']) && is_array($_SESSION['friends'])) {
						array_push($_SESSION['friends'], $friend);
					}
					
					// Set the friend's avatars for the frontend.
					if(!empty($userData['avatar_small'])) {
						$appReq->avatar = $userData['avatar'];
						$appReq->avatarSmall = $userData['avatar_small'];
					} elseif(!empty($globalSettings['avatar_location']['value']) && !empty($globalSettings['small_avatar_location']['value'])) {
						$appReq->avatar = $globalSettings['avatar_location']['value'];
						$appReq->avatarSmall = $globalSettings['small_avatar_location']['value'];
					}
					
					
					// Set user data
					$appReq->userId = $userData['id'];
					$appReq->username = $userData['username'];
					$appReq->userAlias = $userData['username_alias'];
					// Set friend data
					$appReq->friendUsername = $friendData['username'];
					$appReq->friendAlias = $friendData['username_alias'];
					
					// Get the friend's notification option for when he/she is added as a friend by another user.
					$friendNotify = $users->getNotificationOptionForUser($friendId, 4);
					
					// Make a system notification if they want a system notification
					if($friendNotify['by_system'] == 1) {
						$users->insertNotification($friendId, $_SESSION['username'] . ' added you as a friend.');
					}
					
					// Send an email notification if they want an email notification
					if($friendNotify['by_email'] == 1) {
						
					}
				}
			} elseif($appReq->action === 'remove-friend') {
				$friendId = (int)$appReq->friendId;
				// Delete the user's friend.
				$appReq->friendRemoved = $users->removeFriend($_SESSION['userId'], $friendId);
				// Remove the friend from the session array
				if(!empty($_SESSION['friends']) && is_array($_SESSION['friends']) && in_array($friendId, $_SESSION['friends'][$friendId])) {
					unset($_SESSION['friends'][$friendId]);
				}
				// Set the user id for the frontend
				$appReq->userId = $_SESSION['userId'];
			} elseif($appReq->action === 'load-more-user-posts') {
				// Load the next set of user posts.
				$appReq->userPosts = $forums->loadUserPosts((int)$globalSettings['num_of_posts_to_show']['value'], (int)$appReq->offset, $appReq->userId);
				
				// Get the total number of user posts.
				$totalPosts = $forums->countNumOfUserPosts($appReq->userId);
				
				// If there are no more posts let the client know.
				if($totalPosts <= (int)$appReq->offset + (int)$globalSettings['num_of_posts_to_show']['value']) {
					$appReq->noMorePosts = TRUE;
				}
				// Ensure that the user has permission to view these posts.
				foreach($appReq->userPosts as $key =>$userPost) {
					$forumPerms = getForumPermsForUser($_SESSION['roles'], $forumRolePerms, $userPost['forum_id']);
					
					if(empty($forumPerms['view-forum'])) {
						unset($appReq->userPosts);
					}
					
					$appReq->userPosts[$key]['date_posted'] = nicetime($appReq->userPosts[$key]['date_posted']);
				}
				
			} elseif($appReq->action === 'load-more-user-topics') {
				// Load the next set of user topics
				$appReq->userTopics = $forums->loadUserTopics((int)$globalSettings['num_of_topics_to_show']['value'], (int)$appReq->offset, $appReq->userId);
				
				// Get the total number of user posts.
				$totalTopics = $forums->countNumOfUserTopics((int)$appReq->userId);
				
				$appReq->totalTopics = $totalTopics;

				// If there are no more topics let the client know.
				if((int)$totalTopics <= intval((int)$appReq->offset + (int)$globalSettings['num_of_topics_to_show']['value'])) {
					$appReq->noMoreTopics = TRUE;
				}
				
				// Ensure that the user has permsision to view these topics.
				foreach($appReq->userTopics as $key => $userTopic) {
					$forumPerms = getForumPermsForUser($_SESSION['roles'], $forumRolePerms, $userTopic['forum_id']);
					
					if(empty($forumPerms['view-forum'])) {
						unset($appReq->userTopics[$key]);
					}
					
					$appReq->userTopics[$key]['date_posted'] = nicetime($appReq->userTopics[$key]['date_posted']);
				}
			} elseif($appReq->action === 'best-answer-post') {
				if(!empty($appReq->prevId) && !empty($appReq->id)) {
					$forums->setBestAnswer($appReq->prevId, 0);
					$forums->setBestAnswer($appReq->id, 1);
				}
			} elseif($appReq->action === 'report-post') {
				if(isset($_SESSION['userId'])) {
					$forums->insertReportedPost($appReq->postId, $_SESSION['userId']);
				} else {
					$appReq->error = 'You must be logged in to report a post';
				}
			} elseif($appReq->action === 'unreport-post') {
				if(isset($_SESSION['userId'])) {
					$deleted = $forums->deleteReportedPost((int)$appReq->postId, (int)$_SESSION['userId']);
				} else {
					$appReq->error = 'You must be logged in to unreport a post';
				}
			} elseif($appReq->action === 'vote') {
				// Check if user has voted in a poll.
				if(isset($_SESSION['userId'])) {
					$userVoted = $forums->getVoteForUser((int)$appReq->pollId, $_SESSION['userId']);
					if(empty($userVoted)) {
						$appReq->voted = $forums->insertUserVote((int)$appReq->pollId, (int)$appReq->choiceId, $_SESSION['userId']);
					
						if(!empty($appReq->topicId)) {
							
							$currentForum = $forums->getForumById((int)$appReq->forumId);
							
							if($currentForum['poll_bump']) {
								
								$dateOfLastPost = date('Y-m-d H:i:s', time());
								
								// Update the last post information for the forum.
								$forums->setForumLastPostUser(
									$appReq->forumId
								  , $_SESSION['userId']
								  , $_SESSION['username']
								  , $_SESSION['usernameAlias']
								  , $appReq->topicId
								  , $dateOfLastPost
								);
								// Set the last post info for the topic.
								$forums->setTopicLastPostUser(
									$appReq->topicId
								  , $_SESSION['userId']
								  , $_SESSION['username']
								  , $_SESSION['usernameAlias']
								  , $dateOfLastPost
								);
							}
						}
					}
					
					$appReq->poll = $forums->getChoicesForPoll((int)$appReq->pollId);
					$appReq->poll = json_encode($appReq->poll);
				}
			}
		}
		echo json_encode($appReq);
		exit;
	}
	// Special purpose AJAX requests.
	if(isset($_GET['appRequest'])) {
		$appReq = json_decode($_GET['appRequest']);
		if(isset($appReq->type) && $appReq->type === 'forum' && isset($appReq->action)) {
			if($appReq->action === 'upload-avatar') {
				// Upload the avatar image.
				pluploadForumImage(DIR_AVATARS);
			}
		}
		echo json_encode($appReq);
		exit;
	}
	
	// If the POST request wasn't handled redirect the user back to the page they came from.
	header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
	exit;
}

function buildModeratorPermissions() {
	global $forums, $acl, $redis;
	
	// Get the forums that the user can moderate based on their individual user id.
	$forumsToModerateByUser = $forums->getForumsToModerateByUser($_SESSION['userId']);
	// Iterate through each forum and store the forum id in the array.
	foreach($forumsToModerateByUser as $key => $forumToModerateByUser) {
		// Store the forum ids that the user has permission to mod for caching.
		$userForumsToModerate[] = $forumToModerateByUser['forum_id'];
		// Store the individual permissions for comparison with user's role permissions.
		$permsByUser[$forumToModerateByUser['forum_id']] = $forumToModerateByUser;
	}
	// Get the user's roles.
	$roles = $acl->getRolesForUser($_SESSION['userId']);
	
	// Iterate through each of the user's assigned roles to get moderation privileges.
	foreach($roles as $role) {
		$forumsToModerateByRole = $forums->getForumsToModerateByRole($role['role_id']);
		// Iterate through forum to moderate and add its id to the array.
		foreach($forumsToModerateByRole as $key => $forumToModerateByRole) {
			// Store the forum ids that the user's roles has permission to mod for caching.
			$roleForumsToModerate[] = $forumToModerateByRole['forum_id'];
			// Store the individual permissions for comparison with the user's user specific permissions.
			$permsByRole[$forumToModerateByRole['forum_id']] = $forumToModerateByRole;
		}
	}
	
	//print_r2($permsByUser);
	//print_r2($permsByRole);
	
	// Compare and combine the user's role and user specific permissions.  
	if(!empty($permsByUser) && !empty($userForumsToModerate) && !empty($roleForumsToModerate)) {
		foreach($permsByUser as $userForumId => $userForumPerms) {
			foreach($permsByRole as $roleForumId => $roleForumPerms) {
				// If the user shares the same forum in both their user an role permissions, combine thier permissions into one.
				// If the user or role has been granted a permission then that user will have it.
				// In order to deny the moderator certain permissions for that forum the user must not have the privilege granted to them
				// neither by inheritance from their role nor assigned to them speciifically by user.
				if($userForumId === $roleForumId) {
					foreach($userForumPerms as $userPermKey => $userPermValue) {
						foreach($roleForumPerms as $rolePermKey => $rolePermValue) {
							if($userPermKey === $rolePermKey) {
								if($userPermValue === $rolePermValue) {
									$finalPerms[$userForumId][$userPermKey] = $userPermValue;
								} elseif($userPermValue !== $rolePermValue) {
									if($userPermValue == 1 || $rolePermValue == 1) {
										$finalPerms[$userForumId][$userPermKey] = 1;
									} else {
										$finalPerms[$userForumId][$userPermKey] = 0;
									}
								}
							}
						}
					}
				} else {
					$finalPerms[$roleForumId] = $roleForumPerms;
				}
			}
		}
		// Merage the forum ids from the user and role arrays.
		$forumsToModerate = array_merge($userForumsToModerate, $roleForumsToModerate);
		// Keep only unique values of these array, no duplicates.
		$forumsToModerate = array_unique($forumsToModerate);
	} elseif(!empty($userForumsToModerate) && !empty($permsByUser) && empty($permsByRole)) {
		foreach($permsByUser as $userForumId => $userForumPerms) {
			foreach($userForumPerms as $userPermKey => $userPermValue) {
				$finalPerms[$userForumId][$userPermKey] = $userPermValue;
			}
		}
		$forumsToModerate = $userForumsToModerate;
	} elseif(!empty($roleForumsToModerate) && !empty($permsByRole) && empty($permsByUser)) {
		foreach($permsByRole as $roleForumId => $roleForumPerms) {
			foreach($roleForumPerms as $rolePermKey => $rolePermValue) {
				$finalPerms[$roleForumId][$rolePermKey] = $rolePermValue;
			}
		}
		$forumsToModerate = $roleForumsToModerate;
	}
	
	if(!empty($forumsToModerate)) {
		$_SESSION['isModerator'] = TRUE;
		if(REDIS === TRUE) {
			$redis->hset(PREFIX . 'forumsToModerate', $_SESSION['userId'], json_encode($forumsToModerate));
			$redis->hset(PREFIX . 'moderatorPerms', $_SESSION['userId'], json_encode($finalPerms));
		} else {
			$_SESSION['forumsToModerate'] = $forumsToModerate;
			$_SESSION['moderatorPerms'] = $finalPerms;
		}
		return array($forumsToModerate, $finalPerms);
	} else {
		$_SESSION['isModerator'] = FALSE;
		return FALSE;
	}
}

function buildPosts($posts, $appReq, $direction) {
	global $users;
	global $acl;
	global $redis;
	
	foreach($posts as $key => $post) {
		// Make the date posted look nice.
		$appReq->posts[$key]['date_posted'] = nicetime($post['date_posted']);
		
		// Set the user's post count
		if(REDIS === TRUE && $redis->exists(PREFIX . 'user:' . $post['user_id'] . ':forumPostCount')) {
			$userPostCount = (int)$redis->get(PREFIX . 'user:' . $post['user_id'] . ':forumPostCount');
				if($userPostCount === 1) {
					$appReq->posts[$key]['post_count_label'] = 'post';
				} else {
					$appReq->posts[$key]['post_count_label'] = 'posts';
				}
				$appReq->posts[$key]['user_post_count'] = $userPostCount;
		} else {
			$userPostCount = (int)$users->getUserForumPostCount($post['user_id']);
			if($userPostCount === 1) {
				$appReq->posts[$key]['post_count_label'] = 'post';
			} else {
				$appReq->posts[$key]['post_count_label'] = 'posts';
			}
			$appReq->posts[$key]['user_post_count'] = $userPostCount;
		}
		// Get each user's highest rank role to display.
		$appReq->highest_ranked_role[$post['id']] = $acl->getHighestRankedRoleForUser($post['user_id']);
		
		// Get moderator permissions
		if(isset($_SESSION['userId'])) {
			
			if(REDIS === TRUE) {
				$moderatorPerms = json_decode($redis->hget(PREFIX . 'moderatorPerms', $_SESSION['userId']), TRUE);
			} elseif(isset($_SESSION['moderatorPerms'])) {
				$moderatorPerms = $_SESSION['moderatorPerms'];
			}
			
			if(!empty($moderatorPerms)) {
				// Set moderator permissions
				if($moderatorPerms[$appReq->posts[$key]['forum_id']]['delete_posts'] == 1) {
					$appReq->modPerms[$post['id']]['delete'] = TRUE;
				}
				if(($post['user_id'] === $_SESSION['userId']) && $moderatorPerms[$appReq->posts[$key]['forum_id']]['edit_posts'] == 1) {
					$appReq->modPerms[$post['id']]['edit'] = TRUE;
				}
			}
		}
	}
	
	return $appReq;
}

function calcUserAge($birthdayDate) {
	$date = new DateTime($birthdayDate);
	$now = new DateTime();
	$interval = $now->diff($date);
	return $interval->y;
}

function pluploadForumImage($targetDir) {
	global $globalSettings;
	global $users;
	global $config;
	
	// HTTP headers for no cache etc
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	// If the directory does not exist, create it.
	if(!is_dir($targetDir)) {
		@mkdir($targetDir, 0755, TRUE);
	}

	$cleanupTargetDir = TRUE; // Remove old files
	$maxFileAge = 5 * 3600; // Temp file age in seconds

	// 5 minutes execution time
	@set_time_limit(5 * 60);

	// Uncomment this one to fake upload time
	// usleep(5000);

	// Get parameters
	$chunks = 0; $chunk = 0; $fileName = 0;
	if(isset($_POST['chunk'])) $chunk = intval($_POST['chunk']);
	if(isset($_POST['chunks'])) $chunks = intval($_POST['chunks']);
	if(isset($_POST['name'])) $fileName = $_POST['name'];
	if(isset($_GET['chunk'])) $chunk = intval($_GET['chunk']);
	if(isset($_GET['chunks'])) $chunks = intval($_GET['chunks']);
	if(isset($_GET['name'])) $fileName = $_GET['name'];

	// Clean the fileName for security reasons
	$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

	// Make sure the fileName is unique but only if chunking is disabled
	if($chunks < 2 && file_exists($targetDir . $fileName)) {
		$ext = strrpos($fileName, '.');
		//$fileName_a = substr($fileName, 0, $ext);
		$fileName_a = uniqid('avatar-', TRUE);
		$fileName_b = substr($fileName, $ext);

		$count = 1;
		while (file_exists($targetDir . $fileName_a . '_' . $count . $fileName_b)) {
			$count++;
		}

		$fileName = $fileName_a . '_' . $count . $fileName_b;
	}

	$filePath = $targetDir . $fileName;

	// Remove old temp files
	if($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
		while(($file = readdir($dir)) !== false) {
			$tmpfilePath = $targetDir . $file;

			// Remove temp file if it is older than the max age and is not the current file
			if(preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
				@unlink($tmpfilePath);
			}
		}
		closedir($dir);
	} else {
		die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
	}

	// Look for the content type header
	if(isset($_SERVER["HTTP_CONTENT_TYPE"])) {
		$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
	}

	if(isset($_SERVER["CONTENT_TYPE"])) {
		$contentType = $_SERVER["CONTENT_TYPE"];
	}

	if(empty($contentType)) {
		exit;
	}
	// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
	if(strpos($contentType, "multipart") !== false) {
		if(isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if($out) {
				// Read binary input stream and append it to temp file
				$in = fopen($_FILES['file']['tmp_name'], "rb");

				if($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else {
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
				}
				fclose($in);
				fclose($out);
				@unlink($_FILES['file']['tmp_name']);
			} else {
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			}
		} else {
			die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		}
	} else {
		// Open temp file
		$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
		if($out) {
			// Read binary input stream and append it to temp file
			$in = fopen("php://input", "rb");

			if($in) {
				while ($buff = fread($in, 4096))
					fwrite($out, $buff);
			} else {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
			fclose($in);
			fclose($out);
		} else {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
	}

	// Check if file has been uploaded
	if(!$chunks || $chunk == $chunks - 1) {
		// Strip the temp .part suffix off
		rename("{$filePath}.part", $filePath);
	}

	// Update the database with the media file
	if(file_exists($filePath)) {
		$fileInfo = pathinfo($filePath);
		require_once DIR_SYSTEM . 'library/thumbnail-generator.php';
		$globalValidation = new GlobalValidation;
		$mediaName = $fileName;
		
		// Set the filename
		$fileName = uniqid('avatar-', TRUE);
		
		// Define image mime types
		$imageMimes = array('image/jpeg', 'image/png', 'image/gif');

		// Get the mime type for the
		$mediaMimeType = mime_content_type($filePath);

		// Insert thumbnails for media images.
		$imageTemplate = $config->getImageTemplate($globalSettings['avatar_template']['value']);
		
		
		// Create thumbnail.
		$thumbPath = DIR_AVATARS . $fileName . '.' . $fileInfo['extension'];
		$thumbURL = HTTP_AVATARS . $fileName . '.' . $fileInfo['extension'];
		$smallThumbPath = DIR_AVATARS. $fileName .  '-small'  . '.' . $fileInfo['extension'];
		$smallThumbURL = HTTP_AVATARS . $fileName . '-small' . '.' . $fileInfo['extension'];

		// Instantiate the thumbnail generator.
		$thumbnailGenerator = new ThumbnailGenerator($filePath);
		// Resize and save the image.
		$thumbnailGenerator->resizeImage($imageTemplate['template_width'], $imageTemplate['template_height'], $imageTemplate['template_type']);
		// Save the image file.
		$thumbnailGenerator->saveImage($thumbPath, $imageTemplate['template_quality']);
		// Make a thumbnail for the avatar image
		$thumbnailGenerator = new ThumbnailGenerator($filePath);
		$thumbnailGenerator->resizeImage(32, 32, 'exact');
		$thumbnailGenerator->saveImage($smallThumbPath, $imageTemplate['template_quality']);
		
		// Delete source image
		if(file_exists($filePath)) {
			unlink($filePath);
		}
		
		// Delete previous avatars
		$userData = $users->getUserData($_SESSION['userId']);
		if(is_file(DIR_AVATARS . $userData['avatar_path'])) {
			unlink(DIR_AVATARS . $userData['avatar_path']);
		}
		if(is_file(DIR_AVATARS . $userData['avatar_small_path'])) {
			unlink(DIR_AVATARS . $userData['avatar_small_path']);
		}
		
		// Update the user's avatar.
		$avatarUpdated = $users->setUserAvatar(
			$_SESSION['userId']
		  , array(
			    'normal'     => $fileName . '.' . $fileInfo['extension']
			  , 'small'      => $fileName . '-small' . '.' . $fileInfo['extension']
			  , 'normalPath' => $fileName . '.' . $fileInfo['extension']
			  , 'smallPath'  => $fileName . '-small' . '.' . $fileInfo['extension']
			)
		);
		
		$adminRequestObj = new stdClass;
		$adminRequestObj->avatarUpdated = $avatarUpdated;
		$adminRequestObj->avatarURL = $thumbURL;
		// Return JSON resposne
		echo json_encode($adminRequestObj);
		exit;
	}
}

function loadPermsForModerator($userId) {
	
	global $redis;
	
	if(REDIS === TRUE && $redis->hexists(PREFIX . 'moderatorPerms', $userId)) {
		$moderatorPerms = json_decode($redis->hget(PREFIX . 'moderatorPerms', $userId), TRUE);
		return $moderatorPerms;
	} elseif(isset($_SESSION['moderatorPerms'])) {
		return $_SESSION['moderatorPerms'];
	}
	
	return FALSE;
}

function getRoleForumPermissions() {
	global $forums, $acl;
	
	$roles = $acl->loadRoles();
	
	foreach($roles as $role) {
		$roleIds[] = $role['id'];
	}
	
	foreach($roleIds as $roleId) {
		$rolePermissions[] = $forums->getAllRolePermissions((int)$roleId);
	}
	
	foreach($rolePermissions as $rolePermSet) {
		foreach($rolePermSet as $rolePerm) {
			$permAlias = convertPermIdToAlias($rolePerm['perm_id']);
			$rolePerms[$rolePerm['forum_id']][$rolePerm['role_id']][$permAlias] = $rolePerm['perm_id'];
		}
	}
	
	if(empty($rolePerms)) {
		return array();
	} else {
		return $rolePerms;
	}
}

function getForumPermsForUser($userRoles, $forumRolePerms, $forumId) {

	// Iterate through each role id and build an array of permissions indexed by the role id
	foreach($userRoles as $roleId) {
		if(!empty($forumRolePerms[$forumId][$roleId])) {
			$forumPerms[] = $forumRolePerms[$forumId][$roleId];
		}
	}
	
	if(!empty($forumPerms)) {
		// Combine each role's permissions to get the final permission for the forum for the user.
		foreach($forumPerms as $forumPerm) {
			foreach($forumPerm as $permAlias => $permId) {
				$finalPerms[$permAlias] = $permId;
			}
		}
	}
	
	if(!empty($finalPerms)) {
		return $finalPerms;
	} else {
		return FALSE;
	}
}

function convertPermIdToAlias($permId) {
	// Convert forum permission ids to aliases without using the databse.
	switch($permId) {
		case '1':
			$permAlias = 'view-forum';
			break;
		case '2':
			$permAlias = 'read-topics';
			break;
		case '3':
			$permAlias = 'reply-topics';
			break;
		case '4':
			$permAlias = 'start-topics';
			break;
		case '5':
			$permAlias = 'upload';
			break;
		case '6':
			$permAlias = 'download';
			break;
		default:
			$permAlias = $rolePerm['perm_id'];
			break;
	}
	
	return $permAlias;
}

function notifyAtSignedUsers($content, $postURL) {
	global $users;
	
	// Pull at the usernames that were at signed.
	$numToNotify = preg_match_all('/(@\w+)/', $content, $usersToNotify);
	
	//print_r2($usersToNotify);
	
	if(!empty($numToNotify) && $numToNotify !== 0) {
		
		foreach($usersToNotify[0] as $userToNotify) {
			$username = str_replace('@', '', $userToNotify);
			//var_dump($username);
			$userExists = $users->getUserByUsername($username);
			
			//print_r2($userExists); exit;
			
			if(!empty($userExists)) {
				// 2 is the id for the user at sign notification.
				$notification = $users->getNotificationOptionForUser($userExists['id'], 2);
				$currentUsername = isset($_SESSION['username']) ? $_SESSION['username'] : 'Anonymous';
				
				// If the user at signed themselves do not send a notification and move onto the next at signed user.
				if($currentUsername === $userExists['username']) {
					continue;
				}
				
				$postLink = '<a href="' . $postURL . '">post</a>';
				
				$notifyMsg = hsc($currentUsername) . ' has mentioned you in this ' . $postLink . '.';
				
				if($notification['by_system'] == 1) {
					$users->insertNotification($userExists['id'], $notifyMsg);
				}
			}
		}
	}
}

function notifyReplyToFavTopic($topicId) {
	global $forums, $users;
	
	$topic = $forums->getTopic($topicId);
	
	$favUsers = $forums->getUsersThatFavoritedTopic($topicId);
	
	if(!empty($favUsers) && is_array($favUsers)) {
		foreach($favUsers as $favUser) {
			
			// If the current user is one of the users that favorited this topic skip sending them a notification.
			if(isset($_SESSION['userId']) && $_SESSION['userId'] === $favUser['user_id']) {
				continue;
			}
			// 1 is the id for the reply to favorite topic notification option.
			$notification = $users->getNotificationOptionForUser($favUser['user_id'], 1);
			
			$topicLink = '<a href="' . HTTP_FORUM . 'topic/' . $topic['topic_alias'] . '/' . $topicId . '">topic</a>';
			
			if($notification['by_system'] == 1) {
				$users->insertNotification($favUser['user_id'], hsc($_SESSION['username']) . ' has replied to a ' . $topicLink . ' that you favorited.');
			} else if($notification['by_email'] == 1) {
				require_once DIR_SYSTEM . 'engine/mail-queue';
				
				$email['subject'] = "";
				$email['body'] = "";
				$email['to'] = "";
				$email['toFirst'] = $toFirst;
				$email['toLast'] = $toLast;
				$email['from'] = $fromEmail;
				$email['fromName'] = $fromName;
				$email['sender'] = $fromEmail;
			}
		}
	}
}

function notifyLikedPost($postId, $userId) {
	global $forums, $users;
}

function getFriendIds($friends) {
	if(!empty($friends)) {
		foreach($friends as $friend) {
			$friendIds[] = $friend['id'];
		}
		return $friendIds;
	} else {
		return FALSE;
	}
}

function getFriendUsernames($friends) {
	foreach($friends as $friend) {
		$friendUsernames[$friend['id']] = $friend['username'];
	}
	return $friendUsernames;
}

function getModeratorPerms() {
	global $redis;
	
	// Return the forum moderator permissions for the current user.
	if(REDIS === TRUE && $redis->hexists(PREFIX . 'moderatorPerms', $_SESSION['userId'])) {
		$moderatorPerms = json_decode($redis->hget(PREFIX . 'moderatorPerms', $_SESSION['userId']), TRUE);
	} elseif(isset($_SESSION['moderatorPerms'])) {
		$moderatorPerms = $_SESSION['moderatorPerms'];
	}
	
	return $moderatorPerms;
}
