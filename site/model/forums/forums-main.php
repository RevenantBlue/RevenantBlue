<?php
namespace RevenantBlue\Site;
use RevenantBlue;
use \PDO;

class Forums extends RevenantBlue\Db {

	public  $whiteList = array( 
		'id'
	  , 'alias'
	  , 'date_posted'
	  , 'forum_title'
	  , 'forum_alias'
	  , 'forum_description'
	  , 'archived'
	  , 'topic_id'
	  , 'user_id'
	  , 'topic_title'
	  , 'topic_content'
	  , 'post_content'
	  , 'num_of_views'
	  , 'num_of_posts'
	  , 'best_answer', 'topic_starter_best_answer', 'hide_last_post_info', 'see_topic_list', 'show_subforums'
	  , 'permission_denied_msg', 'html', 'bb_code', 'polls', 'poll_bump', 'topic_rating'
	  , 'post_count_incr', 'min_posts_to_post', 'min_posts_to_view', 'view_other_members_topics'
	  , 'approval_emails', 'date_cut_off', 'default_sort_key', 'default_sort_order', 'default_sort_order', 'disable_tagging'
	  , 'edit_posts', 'edit_topics', 'delete_posts', 'delete_topics', 'hide_posts', 'unhide_posts', 'edit_topic_titles'
	  , 'view_all_content', 'view_ip', 'open_topics', 'close_topics', 'splite_and_merge', 'toggle_answered'
	  , 'mass_move', 'mass_prune', 'move_topics', 'pin_topics', 'unpin_topics', 'toggle_topic_visibility'
	  , 'toggle_post_visibility', 'warn_users', 'flag_spammer'
	);
	private $forumsTable;
	private $forumTopicsTable;
	private $forumPostsTable;
	private $forumPermsTable;
	private $forumACLTable;
	private $forumModTable;
	private $forumReportedTable;
	private $pollsTable;
	private $pollChoicesTable;
	private $userVotesTable;
	private $closureTable;
	private $favTopicTable;
	private $forumLikesTable;
	private $usersTable;

	public function __construct() {
		$this->forumsTable = PREFIX . 'forums';
		$this->forumTopicsTable = PREFIX . 'forum_topics';
		$this->forumPostsTable = PREFIX . 'forum_posts';
		$this->forumPermsTable = PREFIX . 'forum_permissions';
		$this->forumACLTable = PREFIX . 'acl_forum_permissions';
		$this->forumModTable = PREFIX . 'forum_moderators';
		$this->forumReportedTable = PREFIX . 'forum_reported_posts';
		$this->pollsTable = PREFIX . 'forum_polls';
		$this->pollChoicesTable = PREFIX . 'forum_poll_choices';
		$this->userVotesTable = PREFIX . 'forum_poll_votes';
		$this->closureTable = PREFIX . 'forums_closure';
		$this->favTopicTable = PREFIX . 'user_forum_topic_favorites';
		$this->forumLikesTable = PREFIX . 'forum_likes';
		$this->usersTable = PREFIX . 'users';
	}

	// Loads all of the root node forums for recursion.
	public function loadAllForums($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, ft.topic_title AS last_reply_topic_title, ft.topic_alias AS last_reply_topic_alias, 
					(SELECT path_length FROM $this->closureTable
					 WHERE ancestor = t.root AND descendant = t.descendant)
					AS root_distance
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t ON f.id = t.ancestor
				 LEFT JOIN $this->usersTable as u ON f.last_reply_user_id = u.id
				 LEFT JOIN $this->forumTopicsTable AS ft ON f.last_reply_topic = ft.id
				 WHERE f.id = t.ancestor AND f.id = t.descendant
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countAllForums() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumsTable");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	// Load the forum root nodes
	public function loadForums($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, ft.topic_title AS last_reply_topic_title, ft.topic_alias AS last_reply_topic_alias, u.avatar_small, 
					(SELECT path_length FROM $this->closureTable
					 WHERE ancestor = t.root AND descendant = t.descendant)
				   AS root_distance
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t ON f.id = t.ancestor
				 LEFT JOIN $this->usersTable as u ON f.last_reply_user_id = u.id
				 LEFT JOIN $this->forumTopicsTable AS ft ON f.last_reply_topic = ft.id
				 WHERE t.root = t.ancestor AND t.path_length = 0
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	// Gets the total number of root nodes to determine the total records for pagination.
	public function countForums() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t ON f.id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0;"
			);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadForumsBySearch($limit, $offset, $searchWord, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, u.avatar_small, (SELECT path_length FROM $this->closureTable
												   WHERE ancestor = t.root AND descendant = t.descendant)
												   AS root_distance
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t ON f.id = t.ancestor
				 LEFT JOIN $this->usersTable as u ON f.last_reply_user_id = u.id
				 WHERE f.name LIKE :searchWord AND f.id = t.ancestor AND f.id = t.descendant
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countForumsBySearch($searchWord) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->forumsTable
				 WHERE name LIKE :searchWord"
			);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadForumsByPublished($limit, $offset, $publishedState, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, u.avatar_small, (SELECT path_length FROM $this->closureTable
												   WHERE ancestor = t.root AND descendant = t.descendant)
												   AS root_distance
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t ON f.id = t.ancestor
				 LEFT JOIN $this->usersTable as u ON f.last_reply_user_id = u.id
				 WHERE f.published = :published AND f.id = t.ancestor AND f.id = t.descendant
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countForumsByPublished($publishedState) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumsTable WHERE published = :published");
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadForumTopics($limit, $offset, $forumId, $pinned, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT ft.*, u.avatar, u.avatar_small
				 FROM $this->forumTopicsTable AS ft
				 LEFT JOIN $this->usersTable as u ON ft.last_reply_user_id = u.id
				 WHERE forum_id = :forumId AND pinned = :pinned
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':pinned', $pinned, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function countTopics() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT COUNT(*) AS num_of_topics FROM $this->forumTopicsTable");
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['num_of_topics'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function loadRecentTopics($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, ft.*, u.avatar, u.avatar_small
				 FROM $this->forumTopicsTable AS ft
				 LEFT JOIN $this->forumsTable AS f ON ft.forum_id = f.id
				 LEFT JOIN $this->usersTable AS u ON ft.last_reply_user_id = u.id
				 ORDER BY ft.$orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function loadFavoriteTopics($limit, $offset, $userId, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, ft.*, u.avatar, u.avatar_small FROM $this->favTopicTable AS favt
				 LEFT JOIN $this->forumTopicsTable AS ft ON favt.topic_id = ft.id
				 LEFT JOIN $this->forumsTable AS f ON ft.forum_id = f.id
				 LEFT JOIN $this->usersTable AS u ON ft.last_reply_user_id = u.id
				 WHERE favt.user_id = :userId
				 ORDER BY ft.$orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadForumPosts($limit, $offset, $topicId, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT fp.*, u.forum_post_count AS forum_post_count, u.avatar AS avatar, u.avatar_small AS avatar_small
				 FROM $this->forumPostsTable AS fp
				 LEFT JOIN $this->usersTable as u ON fp.user_id = u.id
				 WHERE topic_id = :topicId
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countPostsForTopic($topicId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT COUNT(*) AS numOfPosts FROM $this->forumPostsTable WHERE topic_id = :topicId");
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['numOfPosts'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadForumPostsByPost($limit, $offset, $topicId, $postNumber, $split, $lowerLimit, $totalNumOfPosts) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT fp.*, u.forum_post_count AS forum_post_count, u.avatar AS avatar, u.avatar_small AS avatar_small
				 FROM $this->forumPostsTable AS fp
				 LEFT JOIN $this->usersTable as u ON fp.user_id = u.id
				 WHERE topic_id = :topicId AND post_order >= :lowerLimit
				 ORDER BY date_posted ASC, post_order < $split, post_order ASC
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->bindParam(':lowerLimit', $lowerLimit, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function loadUserTopics($limit, $offset, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->forumTopicsTable
				 WHERE user_id = :userId
				 ORDER BY date_posted DESC
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function countNumOfUserTopics($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT COUNT(*) AS num_of_topics FROM $this->forumTopicsTable
				 WHERE user_id = :userId"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['num_of_topics'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadUserPosts($limit, $offset, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT fp.*, ft.topic_title AS topic_title, ft.topic_alias AS topic_alias
				 FROM $this->forumPostsTable as fp
				 LEFT JOIN $this->forumTopicsTable AS ft ON fp.topic_id = ft.id
				 WHERE fp.user_id = :userId
				 ORDER BY date_posted DESC
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countNumOfUserPosts($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT COUNT(*) AS num_of_posts FROM $this->forumPostsTable
				 WHERE user_id = :userId"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['num_of_posts'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadReportedPosts($limit, $offset) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT fr.*, fp.topic_id, fp.id AS post_id, fp.post_content, fp.post_order, ft.topic_title, 
						fp.user_id AS poster_id, u.username, u2.username AS posted_by, 
						(SELECT COUNT(*) FROM rb_forum_reported_posts WHERE post_id = fp.id) AS num_of_reports
				 FROM $this->forumReportedTable as fr
				 LEFT JOIN $this->forumPostsTable AS fp ON fr.post_id = fp.id
				 LEFT JOIN $this->forumTopicsTable AS ft ON fp.topic_id = ft.id
				 LEFT JOIN $this->usersTable as u on u.id = fr.user_id
				 LEFT JOIN $this->usersTable as u2 ON u2.id = fp.user_id
				 ORDER BY fr.date_reported DESC
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadReportedPostsCondensed($limit, $offset) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT fr.*, COUNT(post_id) AS num_of_reports, fp.topic_id, fp.post_content, fp.post_order, ft.topic_title, u.username
				 FROM $this->forumReportedTable as fr
				 LEFT JOIN $this->forumPostsTable AS fp ON fr.post_id = fp.id
				 LEFT JOIn $this->forumTopicsTable AS ft ON fp.topic_id = ft.id
				 ORDER BY fr.date_reported DESC
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countReportedPosts() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT COUNT(*) AS num_of_reported_posts FROM $this->forumReportedTable"
			);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['num_of_reported_posts'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertForum($forum, $parentId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->forumsTable
					 (user_id, username, forum_title, forum_alias, forum_description, date_posted)
				 VALUES
					 (:userId, :username, :title, :alias, :description, NOW())"
			);
			$stmt->bindValue(':userId', $forum['userId'], PDO::PARAM_INT);
			$stmt->bindParam(':username', $forum['username'], PDO::PARAM_STR);
			$stmt->bindValue(':title', $forum['title'], PDO::PARAM_STR);
			$stmt->bindValue(':alias', $forum['alias'], PDO::PARAM_STR);
			$stmt->bindValue(':description', $forum['description'], PDO::PARAM_STR);
			$stmt->execute();
			$forumId = self::$dbh->lastInsertId('id');
			// Insert the hierarchical relationship in the closure table.
			if(empty($parentId)) $parentId = $forumId;
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length)
					SELECT t.ancestor, :forumId, t.path_length + 1
					FROM $this->closureTable AS t
					WHERE t.descendant = :parentId
					UNION ALL
					SELECT :forumId, :forumId, 0"
			);
			$stmt1->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt1->bindParam(':parentId', $parentId, PDO::PARAM_INT);
			$stmt1->execute();
			// Get the root node for the new forum and update the closure table with the root value.
			$rootNode = $this->getRootNodeForChild($forumId);
			$stmt2 = self::$dbh->prepare(
				"UPDATE $this->closureTable AS t SET root = :rootNode
				 WHERE t.descendant = :forumId"
			);
			$stmt2->bindParam(':rootNode', $rootNode, PDO::PARAM_INT);
			$stmt2->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			if($success === TRUE) {
				return $forumId;
			}
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function insertForumRolePermission($forumId, $roleId, $permId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("INSERT IGNORE INTO $this->forumACLTable (forum_id, role_id, perm_id) VALUES (:forumId, :roleId, :permId)");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->bindParam(':permId', $permId, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertModeratorPermission($forumId, $modId, $type) {
		try {
			if(!self::$dbh) $this->connect();
			if($type === 'user') {
				$stmt = self::$dbh->prepare("INSERT INTO $this->forumModTable (forum_id, user_id) VALUES (:forumId, :modId)");
			} elseif($type === 'role') {
				$stmt = self::$dbh->prepare("INSERT INTO $this->forumModTable (forum_id, role_id) VALUES (:forumId, :modId)");
			}
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':modId', $modId, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertTopic($topicArray) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->forumTopicsTable
					(forum_id, topic_title, topic_content, topic_alias, user_id, username, username_alias, state, date_posted, last_reply_username, last_reply_date, last_reply_username_alias, last_reply_user_id)
				 VALUES
					(:forumId, :topicTitle, :topicContent, :topicAlias, :userId, :username, :usernameAlias, :state, NOW(), :lastReplyUsername, NOW(), :lastReplyUsernameAlias, :lastReplyUserId)"
			);
			$stmt->bindParam(':forumId', $topicArray['forumId'], PDO::PARAM_INT);
			$stmt->bindParam(':topicTitle', $topicArray['title'], PDO::PARAM_STR);
			$stmt->bindParam(':topicContent', $topicArray['content'], PDO::PARAM_STR);
			$stmt->bindParam(':topicAlias', $topicArray['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':userId', $topicArray['userId'], PDO::PARAM_INT);
			$stmt->bindParam(':username', $topicArray['username'], PDO::PARAM_STR);
			$stmt->bindParam(':usernameAlias', $topicArray['usernameAlias'], PDO::PARAM_STR);
			$stmt->bindParam(':state', $topicArray['published'], PDO::PARAM_INT);
			$stmt->bindParam(':lastReplyUsername', $topicArray['username'], PDO::PARAM_STR);
			$stmt->bindParam(':lastReplyUsernameAlias', $topicArray['usernameAlias'], PDO::PARAM_STR);
			$stmt->bindParam(':lastReplyUserId', $topicArray['userId'], PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertPost($postArray) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->forumPostsTable
					(forum_id, topic_id, post_content, user_id, username, username_alias, date_posted, post_order)
				 VALUES
					(:forumId, :topicId, :postContent, :userId, :username, :usernameAlias, NOW(), :postOrder)"
			);
			$stmt->bindParam(':forumId', $postArray['forumId'], PDO::PARAM_INT);
			$stmt->bindParam(':topicId', $postArray['topicId'], PDO::PARAM_INT);
			$stmt->bindParam(':postContent', $postArray['content'], PDO::PARAM_STR);
			$stmt->bindParam(':userId', $postArray['userId'], PDO::PARAM_INT);
			$stmt->bindParam(':username', $postArray['username'], PDO::PARAM_STR);
			$stmt->bindParam(':usernameAlias', $postArray['usernameAlias'], PDO::PARAM_STR);
			$stmt->bindParam(':postOrder', $postArray['postOrder'], PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
		   if ($e->errorInfo[1] == 1062) {
				// Duplicate entry
				$postArray['postOrder'] += 1;
				// Try again.
				$this->insertPost($postArray);
		   } else {
				// An error other than duplicate entry occurred
				$this->errorLog($e);
		   }

		}
	}

	public function insertFavoriteTopic($userId, $topicId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->favTopicTable
					(user_id, topic_id)
				 VALUES
					(:userId, :topicId)"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertPoll($title) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->pollsTable (title) VALUES (:title)"
			);
			$stmt->bindParam(':title', $title, PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function insertPollChoice($pollId, $choice) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->pollChoicesTable (poll_id, choice) VALUES (:pollId, :choice)"
			);
			$stmt->bindParam(':pollId', $pollId, PDO::PARAM_INT);
			$stmt->bindParam(':choice', $choice, PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertReportedPost($postId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT IGNORE INTO $this->forumReportedTable
					(post_id, user_id, date_reported)
				 VALUES
					(:postId, :userId, NOW())"
			);
			$stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertid();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function insertUserVote($pollId, $choiceId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT IGNORE INTO $this->userVotesTable (poll_id, poll_choice, user_id) VALUES (:pollId, :choiceId, :userId)"
			);
			$stmt->bindParam(':pollId', $pollId, PDO::PARAM_INT);
			$stmt->bindParam(':choiceId', $choiceId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare(
				"UPDATE $this->pollChoicesTable SET votes = votes + 1 WHERE id = :choiceId"
			);
			$stmt2->bindParam(':choiceId', $choiceId, PDO::PARAM_INT);
			$stmt2->execute();
			self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}
	
	public function updateForum($forum) {
		try {
			if(!self::$dbh) $this->connect();
			$parentId = $this->getParentId($forum['id']);
			// If the parent id has changed from the original move the node/subtree.
			if($parentId !== $forum['parentId'] && !empty($forum['parentId']) && $forum['parentId'] !== 0) {
				$moveSubTree = $this->moveSubTreeToParent($forum['id'], $forum['parentId']);
				if(empty($moveSubTree)) {
					self::$dbh->rollBack();
					return FALSE;
				}
			} elseif($parentId !== $forum['parentId'] && $forum['parentId'] == 0 || empty($forum['parentId'])) {
				$moveSubTree = $this->moveSubTreeToRoot($forum['id']);
				if(empty($moveSubTree)) {
					self::$dbh->rollBack();
					return FALSE;
				}
			}
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->forumsTable
				 SET forum_title       = :title
				  , forum_alias       = :alias
				  , archived          = :archived
				  , forum_description = :description
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $forum['id'], PDO::PARAM_INT);
			$stmt->bindValue(':title', $forum['title'], PDO::PARAM_STR);
			$stmt->bindValue(':alias', $forum['alias'], PDO::PARAM_STR);
			$stmt->bindValue(':archived', $forum['archived'], PDO::PARAM_INT);
			$stmt->bindValue(':description', $forum['description'], PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function updateForumSection($forum) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->forumsTable
				 SET forum_title       = :title,
					forum_alias       = :alias,
					forum_description = :description
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $forum['id'], PDO::PARAM_INT);
			$stmt->bindValue(':title', $forum['title'], PDO::PARAM_STR);
			$stmt->bindValue(':alias', $forum['alias'], PDO::PARAM_STR);
			$stmt->bindValue(':description', $forum['description'], PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateForumPermission($id, $permission, $status) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->forumsTable SET $permission = :status WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':status', $status, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateModerator($id, $permission, $status) {
		try {
			if(!self::$dbh) $this->connect();
			$query =" UPDATE $this->forumModTable SET $permission = :status WHERE id = :id";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':status', $status, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateForumModerator($forumId, $modId, $type, $permission, $status) {
		try {
			if(!self::$dbh) $this->connect();
			if($type === 'user') {
				$query =" UPDATE $this->forumModTable SET $permission = :status WHERE forum_id = :forumId AND user_id = :modId";
			} elseif($type === 'role') {
				$query = "UPDATE $this->forumModTable SET $permission = :status WHERE forum_id = :forumId AND role_id = :modId";
			}
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':modId', $modId, PDO::PARAM_INT);
			$stmt->bindParam(':status', $status, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updatePost($postArr) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->forumPostsTable SET post_content = :content WHERE id = :id");
			$stmt->bindParam(':content', $postArr['content'], PDO::PARAM_STR);
			$stmt->bindParam(':id', $postArr['id'], PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteForum($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("DELETE FROM $this->forumsTable WHERE id = :forumId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			self::$dbh->commit();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteTopic($topicId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("DELETE FROM $this->forumTopicsTable WHERE id = :topicId");
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("DELETE FROM $this->forumPostsTable WHERE topic_id = :topicId");
			$stmt2->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt2->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deletePost($postId, $topicId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("SELECT post_order FROM $this->forumPostsTable WHERE id = :postId");
			$stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$postOrder = $result['post_order'];
			$stmt2 = self::$dbh->prepare("DELETE FROM $this->forumPostsTable WHERE id = :postId");
			$stmt2->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt2->execute();
			$postDeleted = $stmt2->rowCount();
			$stmt3 = self::$dbh->prepare(
				"UPDATE $this->forumPostsTable SET post_order = post_order - 1 
				 WHERE topic_id = :topicId AND post_order > :postOrder"
			);
			$stmt3->bindParam(':postOrder', $postOrder, PDO::PARAM_INT);
			$stmt3->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt3->execute();
			$reorder = $stmt3->rowCount();
			$stmt4 = self::$dbh->prepare("DELETE FROM $this->forumReportedTable WHERE post_id = :postId");
			$stmt4->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt4->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteFavoriteTopic($userId, $topicId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->favTopicTable WHERE user_id = :userId AND topic_id = :topicId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteForumLeafNode($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt1 = self::$dbh->prepare("DELETE FROM $this->closureTable WHERE descendant = :forumId");
			$stmt1->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt1->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteForumTree($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"DELETE FROM $this->closureTable
				 WHERE descendant IN
				   (SELECT descendant FROM (SELECT descendant FROM $this->closureTable WHERE ancestor = :forumId) AS t)"
			);
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteForumRolePermission($forumId, $roleId, $permId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->forumACLTable WHERE forum_id = :forumId AND role_id = :roleId AND perm_id = :permId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->bindParam(':permId', $permId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteAllRolePermissionsForForum($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->forumACLTable WHERE forum_id = :forumId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteForumTopics($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->forumTopicsTable WHERE forum_id = :forumId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteForumPosts($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->forumPostsTable WHERE forum_id = :forumId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteModerator($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->forumModTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteReportedPost($postId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->forumReportedTable WHERE post_id = :postId AND user_id = :userId");
			$stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function moveTopic($topicId, $moveTo) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->forumPostsTable SET forum_id = :moveTo WHERE topic_id = :topicId"
			);
			$stmt->bindparam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->bindParam(':moveTo', $moveTo, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare(
				"UPDATE $this->forumTopicsTable SET forum_id = :moveTo WHERE id = :topicId"
			);
			$stmt2->bindParam(':moveTo', $moveTo, PDO::PARAM_INT);
			$stmt2->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt2->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}
	
	public function moveSubTreeToRoot($forumIdToMove) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"DELETE a FROM $this->closureTable AS a
				 JOIN $this->closureTable AS d ON a.descendant = d.descendant
				 LEFT JOIN $this->closureTable AS x
				 ON x.ancestor = d.ancestor AND x.descendant = a.ancestor
				 WHERE d.ancestor = :forumIdToMove AND x.ancestor IS NULL"
			);
			$stmt->bindParam(':forumIdToMove', $forumIdToMove, PDO::PARAM_INT);
			$stmt->execute();
			$stmt1 = self::$dbh->prepare(
				"UPDATE $this->closureTable SET root = :forumIdToMove
				 WHERE ancestor IN
					(SELECT descendant FROM 
						(SELECT descendant FROM $this->closureTable WHERE ancestor = :forumIdToMove) 
					AS t)"
			);
			$stmt1->bindParam(':forumIdToMove', $forumIdToMove, PDO::PARAM_INT);
			$stmt1->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function moveSubTreeToParent($forumIdToMove, $newParent) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"DELETE a FROM $this->closureTable AS a
				 JOIN $this->closureTable AS d ON a.descendant = d.descendant
				 LEFT JOIN $this->closureTable AS x ON x.ancestor = d.ancestor AND x.descendant = a.ancestor
				 WHERE d.ancestor = :forumIdToMove AND x.ancestor IS NULL"
			);
			$stmt->bindParam(':forumIdToMove', $forumIdToMove, PDO::PARAM_INT);
			$stmt->execute();
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length, root)
					SELECT supertree.ancestor, subtree.descendant, supertree.path_length + subtree.path_length + 1, supertree.root
						FROM $this->closureTable AS supertree
						JOIN $this->closureTable AS subtree
						WHERE subtree.ancestor = :forumIdToMove AND supertree.descendant = :newParent"
				);
			$stmt1->bindParam(':forumIdToMove', $forumIdToMove, PDO::PARAM_INT);
			$stmt1->bindParam(':newParent', $newParent, PDO::PARAM_INT);
			$stmt1->execute();
			$stmt2 = self::$dbh->prepare(
				"UPDATE $this->closureTable SET root = :newParent
				 WHERE ancestor IN
					SELECT descendant FROM 
						(SELECT descendant FROM $this->closureTable WHERE ancestor = :forumIdToMove)
					AS t)"
				);
			$stmt2->bindParam(':forumIdToMove', $forumIdToMove, PDO::PARAM_INT);
			$stmt2->bindParam(':newParent', $newParent, PDO::PARAM_INT);
			$stmt2->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function getRootNodes() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t ON f.id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0
				 ORDER BY weight ASC"
			);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRootNodeIds() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.id
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t ON f.id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0;"
			);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRootNodesWithLimit($limit, $offset) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, ft.topic_title AS last_reply_topic_title, ft.topic_alias AS last_reply_topic_alias, u.avatar_small, 
					(SELECT path_length FROM $this->closureTable
				     WHERE ancestor = t.root AND descendant = t.descendant)
				   AS root_distance
				FROM $this->forumsTable AS f
				LEFT JOIN $this->closureTable AS t ON f.id = t.ancestor
				LEFT JOIN $this->usersTable as u ON f.last_reply_user_id = u.id
				LEFT JOIN $this->forumTopicsTable AS ft ON f.last_reply_topic = ft.id
				WHERE t.root = t.ancestor AND t.path_length = 0
				ORDER BY weight ASC
				LIMIT :limit
				OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRootNodeForChild($descendant) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, MAX(path_length)
				 FROM $this->forumsTable AS f
				 JOIN $this->closureTable AS t ON f.id = t.ancestor
				 WHERE t.descendant = :descendant"
			);
			$stmt->bindParam(':descendant', $descendant, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['ancestor'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRootByOrder($order) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t ON f.id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0 AND f.weight = :order"
			);
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getParentId($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("
				SELECT MAX(ancestor) AS parent FROM $this->closureTable as t
				RIGHT JOIN $this->forumsTable AS f
				ON t.descendant = f.id
				WHERE f.id = :id AND t.ancestor != t.descendant"
			);
			$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['parent'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAllChildNodes($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, ft.topic_title AS last_reply_topic_title, ft.topic_alias AS last_reply_topic_alias, u.avatar_small, 
					(SELECT path_length FROM $this->closureTable
				     WHERE ancestor = t.root AND descendant = t.descendant)
				     AS root_distance
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable as t ON f.id = t.descendant
				 LEFT JOIN $this->usersTable AS u ON f.last_reply_user_id = u.id
				 LEFT JOIN $this->forumTopicsTable AS ft ON f.last_reply_topic = ft.id
				 WHERE ancestor = :forumId AND t.path_length = 1
				 ORDER BY weight ASC"
			);
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfChildren($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable as t
				 ON f.id = t.descendant
				 WHERE ancestor = :forumId AND t.path_length = 1"
			);
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAncestors($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.* FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t ON f.id = t.ancestor
				 WHERE t.descendant = :forumId"
			);
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDescendants($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, ft.topic_title AS last_reply_topic_title, ft.topic_alias AS last_reply_topic_alias, u.avatar_small, 
						(SELECT path_length FROM $this->closureTable
						 WHERE ancestor = t.root AND descendant = t.descendant)
					AS root_distance
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t ON f.id = t.descendant
				 LEFT JOIN $this->usersTable as u ON f.last_reply_user_id = u.id
				 LEFT JOIN $this->forumTopicsTable AS ft ON f.last_reply_topic = ft.id
				 WHERE t.ancestor = :forumId"
			);
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDescendantIds($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT descendant FROM $this->closureTable
				 WHERE ancestor = :forumId"
			);
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDescendantsWithoutSelf($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, ft.topic_title AS last_reply_topic_title, ft.topic_alias AS last_reply_topic_alias, u.avatar_small,
					(SELECT path_length FROM $this->closureTable
					 WHERE ancestor = t.root AND descendant = t.descendant)
					AS root_distance
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t ON f.id = t.descendant
				 LEFT JOIN $this->usersTable as u ON f.last_reply_user_id = u.id
				 LEFT JOIN $this->forumTopicsTable AS ft ON f.last_reply_topic = ft.id
				 WHERE t.ancestor = :forumId AND t.descendant != :forumId"
			);
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getPathLength($ancestor, $descendant) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT path_length FROM $this->closureTable
				 WHERE ancestor = :ancestor AND descendant = :descendant"
			);
			$stmt->bindParam(':ancestor', $ancestor, PDO::PARAM_INT);
			$stmt->bindParam(':descendant', $descendant, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['path_length'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getForumById($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t
				 ON f.id = t.ancestor
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getForumByAlias($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT f.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->forumsTable AS f
				 LEFT JOIN $this->closureTable AS t
				 ON f.id = t.ancestor
				 WHERE forum_alias = :alias"
			);
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getForumTitle($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT forum_title FROM $this->forumsTable WHERE id = :id");
			$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['forum_title'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setArchived($forumId, $archived) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->forumsTable SET archived = :archived WHERE id = :id");
			$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':archived', $archived, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getPublished($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT published FROM $this->forumsTable WHERE id = :id");
			$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['published'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function setPublished($forumId, $published) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->forumsTable SET published = :published WHERE id = :id");
			$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':published', $published, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getFeatured($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT featured FROM $this->forumsTable WHERE id = :id");
			$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['featured'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function setFeatured($forumId, $featured) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->forumsTable SET featured = :featured WHERE id = :id");
			$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':featured', $featured, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getAllForumIds() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT id FROM $this->forumsTable");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getUserModeratorsForForum($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT user_id FROM $this->forumModTable WHERE forum_id = :forumId AND user_id != ''");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getRoleModeratorsForForum($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT role_id FROM $this->forumModTable WHERE forum_id = :forumId AND role_id != ''");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getModerator($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumModTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getForumsToModerateByUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumModTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getForumsToModerateByRole($roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumModTable WHERE role_id = :roleId");
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getForumByUserModerator($forumId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumModTable WHERE forum_id = :forumId AND user_id = :userId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getForumByRoleModerator($forumId, $roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumModTable WHERE forum_id = :forumId AND role_id = :roleId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getModeratorId($forumId, $modId, $type) {
		try {
			if(!self::$dbh) $this->connect();
			if($type === 'user') {
				$stmt = self::$dbh->prepare("SELECT id FROM $this->forumModTable WHERE forum_id = :forumId AND user_id = :modId");
			} elseif($type === 'role') {
				$stmt = self::$dbh->prepare("SELECT id FROM $this->forumModTable WHERE forum_id = :forumId AND role_id = :modId");
			}
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':modId', $modId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getForumPermissions() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumPermsTable ORDER BY id ASC");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getForumPermission($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumPermsTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getForumPermissionByName($name) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumPermsTable WHERE forum_permission = :name");
			$stmt->bindParam(':name', $name, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getForumPermissionByAlias($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumPermsTable WHERE forum_permission_alias = :alias");
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getForumRolePermission($forumId, $roleId, $permId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumACLTable WHERE forum_id = :forumId AND role_id = :roleId AND perm_id = :permId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':roleId', $role_id, PDO::PARAM_INT);
			$stmt->bindParam(':permId', $permId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getForumRolePermissions($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumACLTable WHERE forum_id = :forumId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getForumRolePermissionsByRole($forumId, $roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumACLTable WHERE forum_id = :forumId AND role_id = :roleId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function getAllRolePermissions($roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumACLTable WHERE role_id = :roleId");
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function setWeight($forumId, $weight) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->forumsTable SET weight = :weight WHERE id = :forumId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':weight', $weight, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getTopics() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumTopicsTable");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfTopics() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT COUNT(*) AS count FROM $this->forumTopicsTable");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['count'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfTopicsForForum($forumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT COUNT(*) AS count FROM $this->forumTopicsTable WHERE forum_id = :forumId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['count'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getTopic($topicId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumTopicsTable WHERE id = :topicId");
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getTopicByAlias($topicAlias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumTopicsTable WHERE topic_alias = :topicAlias");
			$stmt->bindParam(':topicAlias', $topicAlias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getTopicTitle($topicId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT topic_title FROM $this->forumTopicsTable WHERE id = :topicId");
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['topic_title'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getFavoriteTopics($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT topic_id FROM $this->favTopicTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getFavoriteTopic($userId, $topicId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->favTopicTable WHERE user_id = :userId AND topic_id = :topicId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getUsersThatFavoritedTopic($topicId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->favTopicTable WHERE topic_id = :topicId");
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setTopicTitle($topicId, $title) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->forumTopicsTable SET topic_title = :title WHERE id = :topicId");
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->bindParam(':title', $title, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setTopicLock($topicId, $locked) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->forumTopicsTable SET locked = :locked WHERE id = :topicId");
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->bindParam(':locked', $locked, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setTopicPin($topicId, $pinned) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->forumTopicsTable SET pinned = :pinned WHERE id = :topicId");
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->bindParam(':pinned', $pinned, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getPost($postId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->forumPostsTable WHERE id = :postId");
			$stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getUserIdForPost($postId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT user_id FROM $this->forumPostsTable WHERE id = :postId");
			$stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['user_id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfPosts() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT COUNT(*) AS num_of_posts FROM $this->forumPostsTable");
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['num_of_posts'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getPostNumber($postId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT post_order FROM $this->forumPostsTable WHERE id = :postId");
			$stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['post_order'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getPostIdByOrder($postOrder, $topicId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT id FROM $this->forumPostsTable WHERE post_order = :postOrder AND topic_id = :topicId");
			$stmt->bindParam(':postOrder', $postOrder, PDO::PARAM_INT);
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function incrementNumOfTopicsForForum($forumId, $numOfIncrements = false) {
		try {
			if(!self::$dbh) $this->connect();
			if(empty($numOfIncrements)) {
				$numOfIncrements = 1;
			}
			$stmt = self::$dbh->prepare("UPDATE $this->forumsTable SET num_of_topics = num_of_topics + :numOfIncrements WHERE id = :forumId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':numOfIncrements', $numOfIncrements, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function decrementNumOfTopicsForForum($forumId, $numOfDecrements = false) {
		try {
			if(!self::$dbh) $this->connect();
			if(empty($numOfDecrements)) {
				$numOfDecrements = 1;
			}
			$stmt = self::$dbh->prepare("UPDATE $this->forumsTable SET num_of_topics = num_of_topics - :numOfDecrements WHERE id = :forumId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':numOfDecrements', $numOfDecrements, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function incrementNumOfPostsForForum($forumId, $numOfIncrements = false) {
		try {
			if(!self::$dbh) $this->connect();
			if(empty($numOfIncrements)) {
				$numOfIncrements = 1;
			}
			$stmt = self::$dbh->prepare("UPDATE $this->forumsTable SET num_of_posts = num_of_posts + :numOfIncrements WHERE id = :forumId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':numOfIncrements', $numOfIncrements, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function decrementNumOfPostsForForum($forumId, $numOfDecrements = false) {
		try {
			if(!self::$dbh) $this->connect();
			if(empty($numOfDecrements)) {
				$numOfDecrements = 1;
			}
			$stmt = self::$dbh->prepare("UPDATE $this->forumsTable SET num_of_posts = num_of_posts - :numOfDecrements WHERE id = :forumId");
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':numOfDecrements', $numOfDecrements, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function incrementNumOfPostsForTopic($topicId, $numOfIncrements = false) {
		try {
			if(!self::$dbh) $this->connect();
			if(empty($numOfIncrements)) {
				$numOfIncrements = 1;
			}
			$stmt = self::$dbh->prepare("UPDATE $this->forumTopicsTable SET num_of_posts = num_of_posts + :numOfIncrements WHERE id = :topicId");
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->bindParam(':numOfIncrements', $numOfIncrements, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function decrementNumOfPostsForTopic($topicId, $numOfDecrements = false) {
		try {
			if(!self::$dbh) $this->connect();
			if(empty($numOfDecrements)) {
				$numOfDecrements = 1;
			}
			$stmt = self::$dbh->prepare("UPDATE $this->forumTopicsTable SET num_of_posts = num_of_posts - :numOfDecrements WHERE id = :topicId");
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->bindParam(':numOfDecrements', $numOfDecrements, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setForumLastPostUser($forumId, $userId, $username, $usernameAlias, $lastReplyTopic, $lastReplyDate) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->forumsTable
				 SET last_reply_username       = :username
				   , last_reply_username_alias = :usernameAlias
				   , last_reply_user_id        = :userId
				   , last_reply_topic          = :lastReplyTopic
				   , last_reply_date           = :lastReplyDate
				 WHERE id = :forumId"
			);
			$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->bindParam(':usernameAlias', $usernameAlias, PDO::PARAM_STR);
			$stmt->bindParam(':lastReplyTopic', $lastReplyTopic, PDO::PARAM_INT);
			$stmt->bindParam(':lastReplyDate', $lastReplyDate, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setTopicLastPostUser($topicId, $userId, $username, $usernameAlias, $lastReplyDate) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->forumTopicsTable
				 SET last_reply_username       = :username
				   , last_reply_username_alias = :usernameAlias
				   , last_reply_user_id        = :userId
				   , last_reply_date           = :lastReplyDate
				 WHERE id = :topicId"
			);
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->bindParam(':usernameAlias', $usernameAlias, PDO::PARAM_STR);
			$stmt->bindParam(':lastReplyDate', $lastReplyDate, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDateForPost($postId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT date_posted FROM $this->forumPostsTable WHERE id = :postId");
			$stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['date_posted'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function incrementTopicViews($topicId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->forumTopicsTable
				 SET num_of_views = num_of_views + 1
				 WHERE id = :topicId"
			);
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function setBestAnswer($postId, $value) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->forumPostsTable SET best_answer = :value WHERE id = :postId");
			$stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt->bindParam(':value', $value, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function likePost($postId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("UPDATE $this->forumPostsTable SET likes = likes + 1 WHERE id = :postId");
			$stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("INSERT INTO $this->forumLikesTable (user_id, post_id) VALUES(:userId, :postId)");
			$stmt2->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt2->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt2->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}
	
	public function unlikePost($postId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("UPDATE $this->forumPostsTable SET likes = likes - 1 WHERE id = :postId");
			$stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("DELETE FROM $this->forumLikesTable WHERE user_id = :userId AND post_id = :postId");
			$stmt2->bindParam(':postId', $postId, PDO::PARAM_INT);
			$stmt2->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt2->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}
	
	public function getReportedPost($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->forumReportedTable
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getChoicesForPoll($pollId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT pt.title, pc.* FROM $this->pollChoicesTable AS pc 
				 LEFT JOIN $this->pollsTable AS pt ON pc.poll_id = pt.id
				 WHERE poll_id = :pollId"
			);
			$stmt->bindParam(':pollId', $pollId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function setTopicIdForPoll($pollId, $topicId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->pollsTable SET topic_id = :topicId WHERE id = :pollId"
			);
			$stmt->bindParam(':pollId', $pollId, PDO::PARAM_INT);
			$stmt->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare(
				"UPDATE $this->forumTopicsTable SET poll_id = :pollId WHERE id = :topicId"
			);
			$stmt2->bindParam(':pollId', $pollId, PDO::PARAM_INT);
			$stmt2->bindParam(':topicId', $topicId, PDO::PARAM_INT);
			$stmt2->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}
	
	public function getVoteForUser($pollId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->userVotesTable WHERE poll_id = :pollId AND user_id = :userId"
			);
			$stmt->bindParam(':pollId', $pollId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
