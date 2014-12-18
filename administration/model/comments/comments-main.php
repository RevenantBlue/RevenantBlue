<?php
namespace RevenantBlue\Admin;
use RevenantBlue;
use \PDO;

class Comments extends RevenantBlue\Db {

	public  $whiteList = array(
		'com_id'
	  , 'com_author'
	  , 'com_date'
	  , 'com_published'
	  , 'com_ip'
	  , 'com_likes'
	  , 'com_content'
	  , 'com_dislikes'
	  , 'com_flags'
	);
	private $commentsTable;
	private $closureTable;
	private $commentLikesTable;
	private $commentFlagsTable;

	public function __construct() {
		$this->commentsTable = PREFIX . 'article_comments';
		$this->closureTable = PREFIX . 'article_comments_closure';
		$this->commentLikesTable = PREFIX . 'article_comment_likes';
		$this->commentFlagsTable = PREFIX . 'article_comment_flags';
	}
	// Loads all of the root node comments for recursion.
	public function loadAllComments($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->commentsTable
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
	public function countAllComments() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->commentsTable");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	// Load all comments for particular article.
	public function loadComments($limit, $offset, $articleId, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->commentsTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.com_id = t.ancestor
				 WHERE c.article_id = :articleId
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	// Gets the total number of root nodes to determine the total records for pagination.
	public function countComments($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*
				 FROM $this->commentsTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.com_id = t.ancestor
				 WHERE c.article_id = :articleId"
			);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadCommentsBySearch($limit, $offset, $searchWord, $orderBy, $sort, $articleId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			if(isset($articleId)) {
				$stmt = self::$dbh->prepare(
					"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
									   WHERE ancestor = t.root AND descendant = t.descendant)
									   AS root_distance
					 FROM $this->commentsTable AS c
					 LEFT JOIN $this->closureTable AS t ON c.com_id = t.ancestor
					 WHERE c.com_author LIKE :searchWord AND c.com_id = t.ancestor AND c.com_id = t.descendant AND c.article_id = :articleId
					 ORDER BY $orderBy $sort
					 LIMIT :limit
					 OFFSET :offset"
				);
				$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			} else {
				$stmt = self::$dbh->prepare(
					"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
									   WHERE ancestor = t.root AND descendant = t.descendant)
									   AS root_distance
					 FROM $this->commentsTable AS c
					 LEFT JOIN $this->closureTable AS t ON c.com_id = t.ancestor
					 WHERE c.com_author LIKE :searchWord AND c.com_id = t.ancestor AND c.com_id = t.descendant
					 ORDER BY $orderBy $sort
					 LIMIT :limit
					 OFFSET :offset"
				);
			}
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countCommentsBySearch($searchWord, $articleId) {
		try {
			if(!self::$dbh) $this->connect();
			if(isset($articleId)) {
				$stmt = self::$dbh->prepare("SELECT * FROM $this->commentsTable
											 WHERE com_author LIKE :searchWord AND article_id = :articleId");
				$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			} else {
				$stmt = self::$dbh->prepare("SELECT * FROM $this->commentsTable
											 WHERE com_author LIKE :searchWord");
			}
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadCommentsByPublished($limit, $offset, $publishedState, $orderBy, $sort, $articleId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			// If an article id has been provided.
			if(isset($articleId)) {
				$stmt = self::$dbh->prepare(
					"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
									   WHERE ancestor = t.root AND descendant = t.descendant)
									   AS root_distance
					 FROM $this->commentsTable AS c
					 LEFT JOIN $this->closureTable AS t ON c.com_id = t.ancestor
					 WHERE c.com_published = :published AND c.com_id = t.ancestor AND c.com_id = t.descendant AND article_id = :articleId
					 ORDER BY $orderBy $sort
					 LIMIT :limit
					 OFFSET :offset"
				);
				$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			// Else load all comments.
			} else {
				$stmt = self::$dbh->prepare(
					"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
									   WHERE ancestor = t.root AND descendant = t.descendant)
									   AS root_distance
					 FROM $this->commentsTable AS c
					 LEFT JOIN $this->closureTable AS t ON c.com_id = t.ancestor
					 WHERE c.com_published = :published AND c.com_id = t.ancestor AND c.com_id = t.descendant
					 ORDER BY $orderBy $sort
					 LIMIT :limit
					 OFFSET :offset"
				);
			}
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countCommentsByPublished($publishedState, $articleId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			if(isset($articleId)) {
				$stmt = self::$dbh->prepare("SELECT * FROM $this->commentsTable WHERE com_published = :published AND article_id = :articleId");
				$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			} else {
				$stmt = self::$dbh->prepare("SELECT * FROM $this->commentsTable WHERE com_published = :published");
			}
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertComment($comment, $parentId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->commentsTable
					 (article_id, com_author, com_date, com_published, com_content, com_ip, com_email, com_website)
				 VALUES
					 (:articleId, :name, NOW(), :published, :content, :ip, :email, :website)"
			);
			$stmt->bindValue(':articleId', $comment['id'], PDO::PARAM_INT);
			$stmt->bindValue(':name', $comment['author'], PDO::PARAM_STR);
			$stmt->bindValue(':published', $comment['state'], PDO::PARAM_INT);
			$stmt->bindValue(':content', $comment['content'], PDO::PARAM_STR);
			$stmt->bindValue(':ip', $comment['ip'], PDO::PARAM_STR);
			$stmt->bindValue(':email', $comment['email'], PDO::PARAM_STR);
			$stmt->bindValue(':website', $comment['website'], PDO::PARAM_STR);
			$stmt->execute();
			$commentId = self::$dbh->lastInsertId('com_id');
			// Insert the hierarchical relationship in the closure table.
			if(empty($parentId)) $parentId = $commentId;
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length)
					SELECT t.ancestor, :commentId, t.path_length+1
					FROM $this->closureTable AS t
					WHERE t.descendant = :parentId
					UNION ALL
					SELECT :commentId, :commentId, 0"
			);
			$stmt1->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt1->bindParam(':parentId', $parentId, PDO::PARAM_INT);
			$stmt1->execute();
			// Get the root node for the new comment and update the closure table with the root value.
			$rootNode = $this->getRootNodeForChild($commentId);
			$stmt2 = self::$dbh->prepare("UPDATE $this->closureTable AS t SET root = :rootNode
										  WHERE t.descendant = :commentId");
			$stmt2->bindParam(':rootNode', $rootNode, PDO::PARAM_INT);
			$stmt2->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			if($success === TRUE) {
				return $commentId;
			}
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function insertCommentQuick($comment, $parentId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->commentsTable
					(com_author, com_alias, com_published, com_order_of_item, com_create_date, com_created_by)
				 VALUES
					(:author, :alias, :published, :orderOfItem, NOW(),  :createdBy)"
			);
			$stmt->bindValue(':author', $comment['author'], PDO::PARAM_STR);
			$stmt->bindValue(':alias', $comment['alias'], PDO::PARAM_STR);
			$stmt->bindValue(':published', $comment['state'], PDO::PARAM_INT);
			$stmt->bindValue(':orderOfItem', $comment['orderOfItem'], PDO::PARAM_INT);
			$stmt->bindValue(':createdBy', $comment['createdBy'], PDO::PARAM_STR);
			$stmt->execute();
			$commentId = self::$dbh->lastInsertId('com_id');
			// Insert the hierarchical relationship in the closure table.
			if(empty($parentId)) $parentId = $commentId;
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length)
					SELECT t.ancestor, :commentId, t.path_length+1
					FROM $this->closureTable AS t
					WHERE t.descendant = :parentId
					UNION ALL
					SELECT :commentId, :commentId, 0"
			);
			$stmt1->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt1->bindParam(':parentId', $parentId, PDO::PARAM_INT);
			$stmt1->execute();
			// Get the root node for the new comment and update the closure table with the root value.
			$rootNode = $this->getRootNodeForChild($commentId);
			$stmt2 = self::$dbh->prepare("UPDATE $this->closureTable AS t SET root = :rootNode
										  WHERE t.descendant = :commentId");
			$stmt2->bindParam(':rootNode', $rootNode, PDO::PARAM_INT);
			$stmt2->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			if($success === TRUE) {
				return $commentId;
			}
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertCommentLike($commentId, $userId, $ip) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->commentLikesTable
					(comment_id, user_id, ip)
				 VALUES
					(:commentId, :userId, :ip)"
			);
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("UPDATE $this->commentsTable SET com_likes = com_likes +1 WHERE com_id = :commentId");
			$stmt2->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function insertCommentFlag($commentId, $userId, $ip) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->commentFlagsTable
					(comment_id, user_id, ip)
				 VALUES
					(:commentId, :userId, :ip)"
			);
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("UPDATE $this->commentsTable SET com_flags = com_flags +1 WHERE com_id = :commentId");
			$stmt2->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function updateComment($comment) {
		try {
			$stmt = self::$dbh->prepare(
				"UPDATE $this->commentsTable
				 SET com_author = :author
				  , com_published = :state
				  , com_content = :content
				  , com_modified_by = :modifiedBy
				  , com_date = :createDate
				  ,  com_website = :website
				  , com_email = :email
				  , com_modified_date = :modifiedDate
				WHERE com_id = :id"
			);
			$stmt->bindValue(':id', $comment['id'], PDO::PARAM_INT);
			$stmt->bindValue(':author', $comment['author'], PDO::PARAM_STR);
			$stmt->bindValue(':state', $comment['state'], PDO::PARAM_INT);
			$stmt->bindValue(':content', $comment['content'], PDO::PARAM_INT);
			$stmt->bindValue(':createDate', $comment['createDate'], PDO::PARAM_STR);
			$stmt->bindValue(':website', $comment['website'], PDO::PARAM_STR);
			$stmt->bindValue(':email', $comment['email'], PDO::PARAM_STR);
			$stmt->bindValue(':modifiedBy', $_SESSION['username'], PDO::PARAM_STR);
			$stmt->bindValue(':modifiedDate', date('Y-m-d H:i:s', time()), PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateOrder($commentId1, $order1, $commentId2, $order2) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("UPDATE $this->commentsTable SET com_order_of_item = :order WHERE com_id = :id");
			$stmt->bindParam(':id', $commentId1, PDO::PARAM_INT);
			$stmt->bindParam(':order', $order1, PDO::PARAM_INT);
			$stmt->execute();
			$stmt1 = self::$dbh->prepare("UPDATE $this->commentsTable SET com_order_of_item = :order WHERE com_id = :id");
			$stmt1->bindParam(':id', $commentId2, PDO::PARAM_INT);
			$stmt1->bindParam(':order', $order2, PDO::PARAM_INT);
			$stmt1->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}
	public function updateContent($commentId, $content) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->commentsTable SET com_content = :content WHERE com_id = :id");
			$stmt->bindParam(':id', $commentId, PDO::PARAM_INT);
			$stmt->bindParam(':content', $content, PDO::PARAM_STR);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function deleteComment($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->commentsTable WHERE com_id = :commentId");
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteCommentLeafNode($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt1 = self::$dbh->prepare("DELETE FROM $this->closureTable
										  WHERE descendant = :commentId");
			$stmt1->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt1->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteCommentTree($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"DELETE FROM $this->closureTable
				 WHERE descendant IN
					(SELECT descendant FROM (SELECT descendant FROM $this->closureTable WHERE ancestor = :commentId) AS t)"
			);
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteCommentLike($commentId, $userId, $ip) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			if(isset($ip)) {
				$stmt = self::$dbh->prepare("DELETE FROM $this->commentLikesTable WHERE comment_id = :commentId AND user_id = :userId");
				$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
				$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
				$stmt->execute();
			} else {
				$stmt = self::$dbh->prepare("DELETE FROM $this->commentLikesTable WHERE comment_id = :commentId AND ip = :ip");
				$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
				$stmt->bindParam(':ip', $ip, PDO::PARAM_INT);
				$stmt->execute();
			}
			$stmt2 = self::$dbh->prepare("UPDATE $this->commentsTable SET com_likes = com_likes - 1 WHERE com_id = :commentId");
			$stmt2->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollback();
			$this->errorLog($e);
		}
	}

	public function deleteCommentFlag($commentId, $userId, $ip) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			if(isset($ip)) {
				$stmt = self::$dbh->prepare("DELETE FROM $this->commentFlagsTable WHERE comment_id = :commentId AND user_id = :userId");
				$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
				$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
				$stmt->execute();
			} else {
				$stmt = self::$dbh->prepare("DELETE FROM $this->commentFlagsTable WHERE comment_id = :commentId AND ip = :ip");
				$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
				$stmt->bindParam(':ip', $ip, PDO::PARAM_INT);
				$stmt->execute();
			}
			$stmt2 = self::$dbh->prepare("UPDATE $this->commentsTable SET com_flags = com_flags - 1 WHERE com_id = :commentId");
			$stmt2->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollback();
			$this->errorLog($e);
		}
	}

	public function moveSubTreeToRoot($commentIdToMove) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();

			$stmt = self::$dbh->prepare(
				"DELETE a FROM $this->closureTable AS a
				 JOIN $this->closureTable AS d ON a.descendant = d.descendant
				 LEFT JOIN $this->closureTable AS x
				 ON x.ancestor = d.ancestor AND x.descendant = a.ancestor
				 WHERE d.ancestor = :commentIdToMove AND x.ancestor IS NULL"
			);
			$stmt->bindParam(':commentIdToMove', $commentIdToMove, PDO::PARAM_INT);
			$stmt->execute();
			$stmt1 = self::$dbh->prepare(
				"UPDATE $this->closureTable SET root = :commentIdToMove
				  WHERE ancestor IN
					  (SELECT descendant FROM (SELECT descendant FROM $this->closureTable WHERE ancestor = :commentIdToMove) AS t)");
			$stmt1->bindParam(':commentIdToMove', $commentIdToMove, PDO::PARAM_INT);
			$stmt1->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function moveSubTreeToParent($commentIdToMove, $newParent) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"DELETE a FROM $this->closureTable AS a
				 JOIN $this->closureTable AS d ON a.descendant = d.descendant
				 LEFT JOIN $this->closureTable AS x
				 ON x.ancestor = d.ancestor AND x.descendant = a.ancestor
				 WHERE d.ancestor = :commentIdToMove AND x.ancestor IS NULL"
			);
			$stmt->bindParam(':commentIdToMove', $commentIdToMove, PDO::PARAM_INT);
			$stmt->execute();
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length, root)
					SELECT supertree.ancestor, subtree.descendant, supertree.path_length + subtree.path_length + 1, supertree.root
					FROM $this->closureTable AS supertree
					JOIN $this->closureTable AS subtree
					WHERE subtree.ancestor = :commentIdToMove AND supertree.descendant = :newParent"
			);
			$stmt1->bindParam(':commentIdToMove', $commentIdToMove, PDO::PARAM_INT);
			$stmt1->bindParam(':newParent', $newParent, PDO::PARAM_INT);
			$stmt1->execute();
			$stmt2 = self::$dbh->prepare(
				"UPDATE $this->closureTable SET root = :newParent
				 WHERE ancestor IN
					(SELECT descendant FROM (SELECT descendant FROM $this->closureTable WHERE ancestor = :commentIdToMove) AS t)"
			);
			$stmt2->bindParam(':commentIdToMove', $commentIdToMove, PDO::PARAM_INT);
			$stmt2->bindParam(':newParent', $newParent, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function getRootNodes($orderBy = 'com_date', $sort = 'DESC') {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->commentsTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.com_id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0
				 ORDER BY $orderBy $sort"
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
				"SELECT c.com_id
				 FROM $this->commentsTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.com_id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0;"
			);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRootNodesWithLimit($limit, $offset, $orderBy = 'com_date', $sort = 'ASC') {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->commentsTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.com_id = t.ancestor
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

	public function getRootNodesByArticleId($limit, $offset, $articleId, $orderBy = 'com_date', $sort = 'ASC') {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->commentsTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.com_id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0 AND c.article_id = :articleId
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRootNodesForArticle($limit, $offset, $articleId, $state, $orderBy = 'com_date', $sort = 'ASC') {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->commentsTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.com_id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0 AND c.article_id = :articleId AND c.com_published = :state
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->bindParam(':state', $state, PDO::PARAM_INT);
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
				"SELECT c.*, t.*, MAX(path_length)
				 FROM $this->commentsTable AS c
				 JOIN $this->closureTable AS t ON c.com_id = t.ancestor
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
				"SELECT c.* FROM $this->commentsTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.com_id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0 AND c.com_order_of_item = :order"
			);
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getParentId($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT MAX(ancestor) AS parent FROM $this->closureTable as t
				 RIGHT JOIN $this->commentsTable as c
				 ON t.descendant = c.com_id
				 WHERE c.com_id = :id AND t.ancestor != t.descendant"
			);
			$stmt->bindParam(':id', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['parent'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAllChildNodes($commentId, $orderBy = 'com_date', $sort = 'ASC') {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->commentsTable as c
				 LEFT JOIN $this->closureTable as t
				 ON c.com_id = t.descendant
				 WHERE ancestor = :commentId AND t.path_length = 1
				 ORDER BY com_date ASC"
			);
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfChildren($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->commentsTable as c
				 LEFT JOIN $this->closureTable as t
				 ON c.com_id = t.descendant
				 WHERE ancestor = :commentId AND t.path_length = 1"
			);
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getChildByOrder($commentId, $order) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->commentsTable as c
				 LEFT JOIN $this->closureTable as t
				 ON c.com_id = t.descendant
				 WHERE ancestor = :commentId AND t.path_length = 1 AND com_date = :order"
			);
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAncestors($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.* FROM $this->commentsTable AS c
				 JOIN $this->closureTable AS t ON c.com_id = t.ancestor
				 WHERE t.descendant = :commentId"
			);
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDescendants($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.* FROM $this->commentsTable AS c
				 JOIN $this->closureTable AS t ON c.com_id = t.descendant
				 WHERE t.ancestor = :commentId"
			);
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDescendantIds($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT descendant FROM $this->closureTable
				 WHERE ancestor = :commentId"
			);
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDescendantsWithoutSelf($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.* FROM $this->commentsTable AS c
				 JOIN $this->closureTable AS t ON c.com_id = t.descendant
				 WHERE t.ancestor = :commentId AND t.descendant != :commentId"
			);
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
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

	public function getCommentById($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->commentsTable AS c
				 LEFT JOIN $this->closureTable AS t
				 ON c.com_id = t.ancestor
				 WHERE com_id = :id"
			);
			$stmt->bindParam(':id', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCommentName($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT com_author FROM $this->commentsTable WHERE com_id=:id");
			$stmt->bindParam(':id', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['com_author'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getState($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT com_published FROM $this->commentsTable WHERE com_id=:id");
			$stmt->bindParam(':id', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['com_published'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setState($commentId, $state) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->commentsTable SET com_published = :state WHERE com_id=:id");
			$stmt->bindParam(':id', $commentId, PDO::PARAM_INT);
			$stmt->bindParam(':state', $state, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCommentImage($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT com_image FROM $this->commentsTable WHERE com_id = :commentId");
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['com_image'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getIdByAlias($commentAlias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT com_id FROM $this->commentsTable WHERE com_alias = :alias");
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['com_id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfComments() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->commentsTable");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfLikes($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT com_likes FROM $this->commentsTable WHERE com_id = :commentId");
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['com_likes'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCommentLike($commentId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->commentLikesTable WHERE comment_id = :commentId AND user_id = :userId");
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCommentLikeByIp($commentId, $ip) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->commentLikesTable WHERE comment_id = :commentId AND ip = :ip");
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->bindParam(':ip', $ip, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCommentFlag($commentId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->commentFlagsTable WHERE comment_id = :commentId AND user_id = :userId");
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCommentFlagByIp($commentId, $ip) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->commentFlagsTable WHERE comment_id = :commentId AND ip = :ip");
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->bindParam(':ip', $ip, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfCommentFlags($commentId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->commentFlagsTable WHERE comment_id = :commentId");
			$stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
