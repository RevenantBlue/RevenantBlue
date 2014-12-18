<?php
namespace RevenantBlue\Site;
use RevenantBlue;
use \PDO;

class Categories extends RevenantBlue\Db {

	public  $whiteList = array(
		'cat_id'
	  , 'cat_name'
	  , 'cat_alias'
	  , 'cat_image'
	  , 'cat_order_of_item'
	  , 'cat_published'
	  , 'cat_date_created'
	  , 'cat_created_by'
	);
	private $categoriesTable;
	private $closureTable;

	public function __construct() {
		$this->categoriesTable = PREFIX . 'categories';
		$this->closureTable = PREFIX . 'categories_closure';
	}

	// Loads all of the root node categories for recursion.
	public function loadAllCategories($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->categoriesTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.cat_id = t.ancestor
				 WHERE c.cat_id = t.ancestor AND c.cat_id = t.descendant
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

	public function countAllCategories() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->categoriesTable");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	// Load the category root nodes
	public function loadCategories($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->categoriesTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.cat_id = t.ancestor
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
	public function countCategories() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*
				 FROM $this->categoriesTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.cat_id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0;"
			);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadCategoriesBySearch($limit, $offset, $searchWord, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->categoriesTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.cat_id = t.ancestor
				 WHERE c.cat_name LIKE :searchWord AND c.cat_id = t.ancestor AND c.cat_id = t.descendant
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

	public function countCategoriesBySearch($searchWord) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->categoriesTable
				 WHERE cat_name LIKE :searchWord"
			);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadCategoriesByPublished($limit, $offset, $publishedState, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("
				SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->categoriesTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.cat_id = t.ancestor
				 WHERE c.cat_published = :published AND c.cat_id = t.ancestor AND c.cat_id = t.descendant
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

	public function countCategoriesByPublished($publishedState) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->categoriesTable WHERE cat_published = :published");
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertCategory($category, $parentId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->categoriesTable
					 (cat_name, cat_alias, cat_image, cat_image_alt, cat_image_path, cat_published, cat_order_of_item, cat_description, cat_date_created, cat_created_by,
					  cat_meta_description, cat_meta_keywords, cat_meta_robots, cat_meta_author)
				 VALUES
					 (:title, :alias, :image, :imageAlt, :imagePath, :published, :orderOfItem, :description, :dateCreated,  :createdBy, :metaDescription,
					  :metaKeywords, :metaRobots, :metaAuthor)"
				);
			$stmt->bindParam(':title', $category['title'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $category['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':image', $category['image'], PDO::PARAM_STR);
			$stmt->bindParam(':imageAlt', $category['imageAlt'], PDO::PARAM_STR);
			$stmt->bindParam(':imagePath', $category['imagePath'], PDO::PARAM_STR);
			$stmt->bindParam(':published', $category['state'], PDO::PARAM_INT);
			$stmt->bindParam(':orderOfItem', $category['orderOfItem'], PDO::PARAM_INT);
			$stmt->bindParam(':description', $category['description'], PDO::PARAM_STR);
			$stmt->bindParam(':dateCreated', $category['dateCreated'], PDO::PARAM_STR);
			$stmt->bindParam(':createdBy', $category['createdBy'], PDO::PARAM_STR);
			$stmt->bindParam(':metaDescription', $category['metaDescription'], PDO::PARAM_STR);
			$stmt->bindParam(':metaKeywords', $category['metaKeywords'], PDO::PARAM_STR);
			$stmt->bindParam(':metaRobots', $category['metaRobots'], PDO::PARAM_STR);
			$stmt->bindParam(':metaAuthor', $category['metaAuthor'], PDO::PARAM_STR);
			$stmt->execute();
			$categoryId = self::$dbh->lastInsertId('cat_id');
			// Insert the hierarchical relationship in the closure table.
			if(empty($parentId)) $parentId = $categoryId;
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length)
				 SELECT t.ancestor, :categoryId, t.path_length+1
				 FROM $this->closureTable AS t
				 WHERE t.descendant = :parentId
				 UNION ALL
					SELECT :categoryId, :categoryId, 0"
			);
			$stmt1->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt1->bindParam(':parentId', $parentId, PDO::PARAM_INT);
			$stmt1->execute();
			// Get the root node for the new category and update the closure table with the root value.
			$rootNode = $this->getRootNodeForChild($categoryId);
			$stmt2 = self::$dbh->prepare("UPDATE $this->closureTable AS t SET root = :rootNode
										  WHERE t.descendant = :categoryId");
			$stmt2->bindParam(':rootNode', $rootNode, PDO::PARAM_INT);
			$stmt2->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			if($success === TRUE) {
				return $categoryId;
			}
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function insertCategoryQuick($category, $parentId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->categoriesTable
					 (cat_name, cat_alias, cat_published, cat_order_of_item, cat_date_created, cat_created_by)
				 VALUES
					 (:title, :alias, :published, :orderOfItem, :dateCreated,  :createdBy)"
			);
			$stmt->bindParam(':title', $category['title'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $category['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':published', $category['state'], PDO::PARAM_INT);
			$stmt->bindParam(':orderOfItem', $category['orderOfItem'], PDO::PARAM_INT);
			$stmt->bindParam(':dateCreated', $category['dateCreated'], PDO::PARAM_STR);
			$stmt->bindParam(':createdBy', $category['createdBy'], PDO::PARAM_STR);
			$stmt->execute();
			$categoryId = self::$dbh->lastInsertId('cat_id');
			// Insert the hierarchical relationship in the closure table.
			if(empty($parentId)) $parentId = $categoryId;
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length)
				 SELECT t.ancestor, :categoryId, t.path_length+1
				 FROM $this->closureTable AS t
				 WHERE t.descendant = :parentId
				 UNION ALL
				 SELECT :categoryId, :categoryId, 0"
			);
			$stmt1->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt1->bindParam(':parentId', $parentId, PDO::PARAM_INT);
			$stmt1->execute();
			// Get the root node for the new category and update the closure table with the root value.
			$rootNode = $this->getRootNodeForChild($categoryId);
			$stmt2 = self::$dbh->prepare("UPDATE $this->closureTable AS t SET root = :rootNode
										  WHERE t.descendant = :categoryId");
			$stmt2->bindParam(':rootNode', $rootNode, PDO::PARAM_INT);
			$stmt2->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			if($success === TRUE) {
				return $categoryId;
			}
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateCategory($category) {
		try {
			if(!self::$dbh) $this->connect();
			$parentId = $this->getParentId($category['id']);
			// If the parent id has changed from the original move the node/subtree.
			if($parentId !== $category['parent'] && !empty($category['parent']) && $category['parent'] !== 0) {
				$moveSubTree = $this->moveSubTreeToParent($category['id'], $category['parent']);
				if(empty($moveSubTree)) {
					self::$dbh->rollBack();
					return FALSE;
				}
			} elseif($parentId !== $category['parent'] && $category['parent'] == 0 || empty($category['parent'])) {
				$moveSubTree = $this->moveSubTreeToRoot($category['id']);
				if(empty($moveSubTree)) {
					self::$dbh->rollBack();
					return FALSE;
				}
			}
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->categoriesTable
				 SET cat_name = :title, cat_alias = :alias, cat_published = :state, cat_description = :description,
					cat_date_created = :dateCreated, cat_created_by = :createdBy, cat_meta_description = :metaDescription,
					cat_meta_keywords = :metaKeywords, cat_meta_robots = :metaRobots, cat_meta_author = :metaAuthor
				 WHERE cat_id = :id"
			);
			$stmt->bindParam(':id', $category['id'], PDO::PARAM_INT);
			$stmt->bindParam(':title', $category['title'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $category['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':state', $category['state'], PDO::PARAM_INT);
			$stmt->bindParam(':description', $category['description'], PDO::PARAM_STR);
			$stmt->bindParam(':dateCreated', $category['dateCreated'], PDO::PARAM_STR);
			$stmt->bindParam(':createdBy', $category['createdBy'], PDO::PARAM_STR);
			$stmt->bindParam(':metaDescription', $category['metaDescription'], PDO::PARAM_STR);
			$stmt->bindParam(':metaKeywords', $category['metaKeywords'], PDO::PARAM_STR);
			$stmt->bindParam(':metaRobots', $category['metaRobots'], PDO::PARAM_STR);
			$stmt->bindParam(':metaAuthor', $category['metaAuthor'], PDO::PARAM_STR);
			$stmt->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			unlink(DIR_IMAGE . 'categories/' . $image['image']['name']);
			$this->errorLog($e);
		}
	}

	public function updateOrder($categoryId1, $order1, $categoryId2, $order2) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("UPDATE $this->categoriesTable SET cat_order_of_item = :order WHERE cat_id = :id");
			$stmt->bindParam(':id', $categoryId1, PDO::PARAM_INT);
			$stmt->bindParam(':order', $order1, PDO::PARAM_INT);
			$stmt->execute();
			$stmt1 = self::$dbh->prepare("UPDATE $this->categoriesTable SET cat_order_of_item = :order WHERE cat_id = :id");
			$stmt1->bindParam(':id', $categoryId2, PDO::PARAM_INT);
			$stmt1->bindParam(':order', $order2, PDO::PARAM_INT);
			$stmt1->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteCategory($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->categoriesTable WHERE cat_id = :categoryId");
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteCategoryLeafNode($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->closureTable WHERE descendant = :categoryId");
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteCategoryTree($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"DELETE FROM $this->closureTable
				 WHERE descendant IN
					(SELECT descendant FROM (SELECT descendant FROM $this->closureTable WHERE ancestor = :categoryId) AS t)"
			);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function moveSubTreeToRoot($categoryIdToMove) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"DELETE a FROM $this->closureTable AS a
				 JOIN $this->closureTable AS d ON a.descendant = d.descendant
				 LEFT JOIN $this->closureTable AS x
				 ON x.ancestor = d.ancestor AND x.descendant = a.ancestor
				 WHERE d.ancestor = :categoryIdToMove AND x.ancestor IS NULL"
			);
			$stmt->bindParam(':categoryIdToMove', $categoryIdToMove, PDO::PARAM_INT);
			$stmt->execute();
			$stmt1 = self::$dbh->prepare("UPDATE $this->closureTable SET root = :categoryIdToMove
										  WHERE ancestor IN
											  (SELECT descendant FROM (SELECT descendant FROM $this->closureTable WHERE ancestor = :categoryIdToMove) AS t)");
			$stmt1->bindParam(':categoryIdToMove', $categoryIdToMove, PDO::PARAM_INT);
			$stmt1->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function moveSubTreeToParent($categoryIdToMove, $newParent) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"DELETE a FROM $this->closureTable AS a
				 JOIN $this->closureTable AS d ON a.descendant = d.descendant
				 LEFT JOIN $this->closureTable AS x
				 ON x.ancestor = d.ancestor AND x.descendant = a.ancestor
				 WHERE d.ancestor = :categoryIdToMove AND x.ancestor IS NULL"
			);
			$stmt->bindParam(':categoryIdToMove', $categoryIdToMove, PDO::PARAM_INT);
			$stmt->execute();
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length, root)
				 SELECT supertree.ancestor, subtree.descendant, supertree.path_length + subtree.path_length + 1, supertree.root
				 FROM $this->closureTable AS supertree
				 JOIN $this->closureTable AS subtree
				 WHERE subtree.ancestor = :categoryIdToMove AND supertree.descendant = :newParent"
			);
			$stmt1->bindParam(':categoryIdToMove', $categoryIdToMove, PDO::PARAM_INT);
			$stmt1->bindParam(':newParent', $newParent, PDO::PARAM_INT);
			$stmt1->execute();
			$stmt2 = self::$dbh->prepare(
				"UPDATE $this->closureTable SET root = :newParent
					WHERE ancestor IN
					(SELECT descendant FROM (SELECT descendant FROM $this->closureTable WHERE ancestor = :categoryIdToMove) AS t)"
			);
			$stmt2->bindParam(':categoryIdToMove', $categoryIdToMove, PDO::PARAM_INT);
			$stmt2->bindParam(':newParent', $newParent, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function getRootNodes() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->categoriesTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.cat_id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0
				 ORDER BY cat_order_of_item ASC"
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
				"SELECT c.cat_id
				 FROM $this->categoriesTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.cat_id = t.ancestor
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
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->categoriesTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.cat_id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0
				 ORDER BY cat_order_of_item ASC
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
				"SELECT c.*, t.*, MAX(path_length)
				 FROM $this->categoriesTable AS c
				 JOIN $this->closureTable AS t ON c.cat_id = t.ancestor
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
				"SELECT c.*
				 FROM $this->categoriesTable AS c
				 LEFT JOIN $this->closureTable AS t ON c.cat_id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0 AND c.cat_order_of_item = :order"
			);
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getParentId($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT MAX(ancestor) AS parent FROM $this->closureTable as t
				 RIGHT JOIN $this->categoriesTable as c
				 ON t.descendant = c.cat_id
				 WHERE c.cat_id = :id AND t.ancestor != t.descendant"
			);
			$stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['parent'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAllChildNodes($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->categoriesTable as c
				 LEFT JOIN $this->closureTable as t
				 ON c.cat_id = t.descendant
				 WHERE ancestor = :categoryId AND t.path_length = 1
				 ORDER BY cat_order_of_item ASC"
			);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfChildren($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->categoriesTable as c
				 LEFT JOIN $this->closureTable as t
				 ON c.cat_id = t.descendant
				 WHERE ancestor = :categoryId AND t.path_length = 1"
			);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getChildByOrder($categoryId, $order) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->categoriesTable as c
				 LEFT JOIN $this->closureTable as t
				 ON c.cat_id = t.descendant
				 WHERE ancestor = :categoryId AND t.path_length = 1 AND cat_order_of_item = :order"
			);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAncestors($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.* FROM $this->categoriesTable AS c
				 JOIN $this->closureTable AS t ON c.cat_id = t.ancestor
				 WHERE t.descendant = :categoryId"
			);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDescendants($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.* FROM $this->categoriesTable AS c
				 JOIN $this->closureTable AS t ON c.cat_id = t.descendant
				 WHERE t.ancestor = :categoryId"
			);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDescendantIds($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT descendant FROM $this->closureTable
				 WHERE ancestor = :categoryId"
			);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDescendantsWithoutSelf($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.* FROM $this->categoriesTable AS c
				 JOIN $this->closureTable AS t ON c.cat_id = t.descendant
				 WHERE t.ancestor = :categoryId AND t.descendant != :categoryId"
			);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
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

	public function getCategoryById($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->categoriesTable AS c
				 LEFT JOIN $this->closureTable AS t
				 ON c.cat_id = t.ancestor
				 WHERE cat_id=:id"
			);
			$stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCategoryByAlias($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT c.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->categoriesTable AS c
				 LEFT JOIN $this->closureTable AS t
				 ON c.cat_id = t.ancestor
				 WHERE cat_alias = :alias"
			);
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCategoryName($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT cat_name FROM $this->categoriesTable WHERE cat_id=:id");
			$stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['cat_name'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getState($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT cat_published FROM $this->categoriesTable WHERE cat_id=:id");
			$stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['cat_published'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setState($categoryId, $state) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->categoriesTable SET cat_published = :state WHERE cat_id=:id");
			$stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
			$stmt->bindParam(':state', $state, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getOrder($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT cat_order_of_item FROM $this->categoriesTable WHERE cat_id = :id");
			$stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setOrder($categoryId, $order) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->categoriesTable SET cat_order_of_item = :order WHERE cat_id = :id");
			$stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getIdByAlias($categoryAlias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT cat_id FROM $this->categoriesTable WHERE cat_alias = :alias");
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['cat_id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfCategories() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->categoriesTable");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getOrderOfItem($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT cat_order_of_item FROM $this->categoriesTable WHERE cat_id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['order_of_item'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setOrderOfItem($id, $orderOfItem) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->categoriesTable SET cat_order_of_item = :orderOfItem WHERE cat_id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':orderOfItem', $orderOfItem, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getImage($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT cat_image, cat_image_alt, cat_image_path FROM $this->categoriesTable WHERE cat_id = :id");
			$stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setImage($categoryId, $fileName, $path) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->categoriesTable SET cat_image = :fileName, cat_image_path = :path WHERE cat_id = :id");
			$stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
			$stmt->bindParam(':fileName', $fileName, PDO::PARAM_STR);
			$stmt->bindParam(':path', $path, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
