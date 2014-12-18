<?php
namespace RevenantBlue\Site;

require_once(DIR_APPLICATION . 'controller/forums/forums-c.php');

class ForumHierarchy {
	
	public  static $forumRolePerms;
	private static $forums;

	// Build the tree structure.
	public static function buildForums($limit, $offset, $published = TRUE) {
		
		// Use the forumRolePerms variable from the forums controller.
		self::$forums = new Forums;
		$rootNodes = self::$forums->getRootNodesWithLimit($limit, $offset);
		$tree = array();
		if(!empty($rootNodes)) {
			foreach($rootNodes as $forum) {
				// Skip the section if it isn't published.
				if($published && (int)$forum['published'] === 0) {
					continue;
				} else {
					// Check if the user has the permission to view the forum section
					$forumPerms = getForumPermsForUser($_SESSION['roles'], self::$forumRolePerms, $forum['id']);

					// If the user can't view this forum section move onto the next.
					if(empty($forumPerms['view-forum'])) {
						continue;
					} else {
						// Build the forum array and recusrively iterate through its children.
						$tree[] = array(
							'id'                  => $forum['id'],
							'ancestor'            => $forum['ancestor'],
							'root'                => $forum['id'],
							'rootDistance'        => $forum['root_distance'],
							'pathLength'          => $forum['path_length'],
							'title'               => $forum['forum_title'],
							'description'         => $forum['forum_description'],
							'alias'               => $forum['forum_alias'],
							'published'           => $forum['published'],
							'featured'            => $forum['featured'],
							'weight'              => $forum['weight'],
							'archived'            => $forum['archived'],
							'numOfTopics'         => $forum['num_of_topics'],
							'numOfPosts'          => $forum['num_of_posts'],
							'lastReplyDate'       => nicetime($forum['last_reply_date'], 'No Posts'),
							'lastReplyUserId'     => $forum['last_reply_user_id'],
							'lastReplyUsername'   => $forum['last_reply_username'],
							'lastReplyAlias'      => $forum['last_reply_username_alias'],
							'lastReplyTopic'      => $forum['last_reply_topic'],
							'lastReplyTopicTitle' => $forum['last_reply_topic_title'],
							'lastReplyTopicAlias' => $forum['last_reply_topic_alias'],
							'avatarSmall'         => $forum['avatar_small'],
							'hideLastPostInfo'    => $forum['hide_last_post_info'], 
							'children'            => self::getTree($forum['ancestor'], $forum['id'])
						);
					}
				}
			}
		}
		return $tree;
	}
	
	public static function buildSubForums($rootId, $showParent = TRUE, $published = TRUE) {
		// Build the tree structure.
		self::$forums = new Forums;
		if($showParent) {
			$subforums = self::$forums->getDescendants($rootId);
		} else {
			$subforums = self::$forums->getDescendantsWithoutSelf($rootId);
		}
		$tree = array();
		if(!empty($subforums)) {
			foreach($subforums as $subforum) {
				// Skip the section if it isn't published.
				if($published && (int)$subforum['published'] === 0) {
					continue;
				} else {
					// Check if the user has the permission to view the forum
					$forumPerms = getForumPermsForUser($_SESSION['roles'], self::$forumRolePerms, $subforum['id']);
					// If the user can't view this forum move onto the next.
					if(empty($forumPerms['view-forum']) && $subforum['forum_type'] !== 'subforum-container') {
						continue;
					} else {
						$tree[] = array(
							'id'                  => $subforum['id'],
							'ancestor'            => $subforum['ancestor'],
							'root'                => $subforum['id'],
							'rootDistance'        => $subforum['root_distance'],
							'pathLength'          => $subforum['path_length'],
							'title'               => $subforum['forum_title'],
							'description'         => $subforum['forum_description'],
							'alias'               => $subforum['forum_alias'],
							'published'           => $subforum['published'],
							'featured'            => $subforum['featured'],
							'weight'              => $subforum['weight'],
							'archived'            => $subforum['archived'],
							'numOfTopics'         => $subforum['num_of_topics'],
							'numOfPosts'          => $subforum['num_of_posts'],
							'lastReplyDate'       => nicetime($subforum['last_reply_date'], 'No Posts'),
							'lastReplyUserId'     => $subforum['last_reply_user_id'],
							'lastReplyUsername'   => $subforum['last_reply_username'],
							'lastReplyAlias'      => $subforum['last_reply_username_alias'],
							'lastReplyTopic'      => $subforum['last_reply_topic'],
							'lastReplyTopicTitle' => $subforum['last_reply_topic_title'],
							'lastReplyTopicAlias' => $subforum['last_reply_topic_alias'],
							'avatarSmall'         => $subforum['avatar_small'],
							'hideLastPostInfo'    => $subforum['hide_last_post_info'],
							'children'            => self::getTree($subforum['descendant'], $subforum['id'])
						);
					}
				}
			}
		}
		return $tree;
	}

	public static function getTree($rootId, $root, $published = TRUE) {
		
		self::$forums = new Forums;
		$arr = array();
		$result = self::$forums->getAllChildNodes($rootId);

		foreach($result as $row) {
			// Skip the forum if it isn't published.
			if($published && (int)$row['published'] === 0) {
				continue;
			} else {
				// Check if the user has the permission to view the forum
				$forumPerms = getForumPermsForUser($_SESSION['roles'], self::$forumRolePerms, $row['id']);

				// If the user can't view this forum move onto the next.
				if(empty($forumPerms['view-forum']) && $row['forum_type'] !== 'subforum-container') {
					continue;
				} else {
					$arr[] = array(
						'id'                  => $row['id'],
						'ancestor'            => $row['ancestor'],
						'root'                => $root,
						'rootDistance'        => $row['root_distance'],
						'pathLength'          => $row['path_length'],
						'title'               => $row['forum_title'],
						'description'         => $row['forum_description'],
						'alias'               => $row['forum_alias'],
						'published'           => $row['published'],
						'featured'            => $row['featured'],
						'weight'              => $row['weight'],
						'archived'            => $row['archived'],
						'numOfTopics'         => $row['num_of_topics'],
						'numOfPosts'          => $row['num_of_posts'],
						'lastReplyDate'       => nicetime($row['last_reply_date'], 'No Posts'),
						'lastReplyUserId'     => $row['last_reply_user_id'],
						'lastReplyUsername'   => $row['last_reply_username'],
						'lastReplyAlias'      => $row['last_reply_username_alias'],
						'lastReplyTopic'      => $row['last_reply_topic'],
						'lastReplyTopicTitle' => $row['last_reply_topic_title'],
						'lastReplyTopicAlias' => $row['last_reply_topic_alias'],
						'avatarSmall'         => $row['avatar_small'],
						'hideLastPostInfo'    => $row['hide_last_post_info'],
						'children'            => self::getTree($row['descendant'], $root)
					);
				}
			}
		}
	   return $arr;
	}

	public static function displayForums($inarray, &$toarray = array()) {
		// Flatten multidimensional array for display purposes and return their keys to the original database results.
		foreach($inarray as $inkey => $inval) {
			$toarray[$inval['id']]['root_distance'] = $inval['rootDistance'];
			$toarray[$inval['id']]['forum_title'] = $inval['title'];
			$toarray[$inval['id']]['forum_description'] = $inval['description'];
			$toarray[$inval['id']]['forum_alias'] = $inval['alias'];
			$toarray[$inval['id']]['id'] = $inval['id'];
			$toarray[$inval['id']]['archived'] = $inval['archived'];
			$toarray[$inval['id']]['weight'] = $inval['weight'];
			$toarray[$inval['id']]['ancestor'] = $inval['ancestor'];
			$toarray[$inval['id']]['root'] = $inval['root'];
			$toarray[$inval['id']]['path_length'] = $inval['pathLength'];
			$toarray[$inval['id']]['published'] = $inval['published'];
			$toarray[$inval['id']]['featured'] = $inval['featured'];
			$toarray[$inval['id']]['num_of_topics'] = $inval['numOfTopics'];
			$toarray[$inval['id']]['num_of_posts'] = $inval['numOfPosts'];
			$toarray[$inval['id']]['avatar_small'] = $inval['avatarSmall'];
			$toarray[$inval['id']]['hide_last_post_info'] = $inval['hideLastPostInfo'];
			$toarray[$inval['id']]['last_reply_date'] = $inval['lastReplyDate'];
			$toarray[$inval['id']]['last_reply_user_id'] = $inval['lastReplyUserId'];
			$toarray[$inval['id']]['last_reply_username'] = $inval['lastReplyUsername'];
			$toarray[$inval['id']]['last_reply_username_alias'] = $inval['lastReplyAlias'];
			$toarray[$inval['id']]['last_reply_topic'] = $inval['lastReplyTopic'];
			$toarray[$inval['id']]['last_reply_topic_title'] = $inval['lastReplyTopicTitle'];
			$toarray[$inval['id']]['last_reply_topic_alias'] = $inval['lastReplyTopicAlias'];
			
			if(is_array($inval['children'])) {
				self::displayForums($inval['children'], $toarray);
			}
		}
		if(count($inarray == 1)) return $toarray;
	}
}
