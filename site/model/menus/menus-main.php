<?php
namespace RevenantBlue\Site;
use RevenantBlue;
use RevenantBlue\Db;
use \PDO;

class Menus extends Db {

	public  $whiteList = array(
		'id'
	  , 'menu_name'
	  , 'image'
	  , 'order_of_item'
	  , 'published'
	  , 'date_created'
	  , 'created_by'
	);
	private $menusTable;
	private $closureTable;

	public function __construct() {
		$this->menusTable = PREFIX . 'menus';
		$this->closureTable = PREFIX . 'menus_closure';
	}

	// Loads all of the root node menus for recursion.
	public function loadAllMenus($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable AS t ON m.id = t.ancestor
				 WHERE m.id = t.ancestor AND m.id = t.descendant
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

	public function countAllMenus() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->menusTable");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	// Load the menu root nodes
	public function loadMenus($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable AS t ON m.id = t.ancestor
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
	public function countMenus() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, t.*
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable AS t ON m.id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0;"
			);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadMenusBySearch($limit, $offset, $searchWord, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable AS t ON m.id = t.ancestor
				 WHERE m.menu_name LIKE :searchWord AND m.id = t.ancestor AND m.id = t.descendant
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

	public function countMenusBySearch($searchWord) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->menusTable
				 WHERE menu_name LIKE :searchWord"
			);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadMenusByPublished($limit, $offset, $publishedState, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("
				SELECT m.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable AS t ON m.id = t.ancestor
				 WHERE m.published = :published AND m.id = t.ancestor AND m.id = t.descendant
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

	public function countMenusByPublished($publishedState) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->menusTable WHERE published = :published");
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertMenu($menu, $parentId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->menusTable
					 (menu_name, menu_alias, menu_url, image, image_alt, image_path, published, order_of_item, description, date_created, created_by)
				 VALUES
					 (:name, :alias, :url, :image, :imageAlt, :imagePath, :published, :orderOfItem, :description, :dateCreated,  :createdBy)"
				);
			$stmt->bindParam(':name', $menu['name'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $menu['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':url', $menu['url'], PDO::PARAM_STR);
			$stmt->bindParam(':image', $menu['image'], PDO::PARAM_STR);
			$stmt->bindParam(':imageAlt', $menu['imageAlt'], PDO::PARAM_STR);
			$stmt->bindParam(':imagePath', $menu['imagePath'], PDO::PARAM_STR);
			$stmt->bindParam(':published', $menu['state'], PDO::PARAM_INT);
			$stmt->bindParam(':orderOfItem', $menu['orderOfItem'], PDO::PARAM_INT);
			$stmt->bindParam(':description', $menu['description'], PDO::PARAM_STR);
			$stmt->bindParam(':dateCreated', $menu['dateCreated'], PDO::PARAM_STR);
			$stmt->bindParam(':createdBy', $menu['createdBy'], PDO::PARAM_STR);
			$stmt->execute();
			$menuId = self::$dbh->lastInsertId('id');
			// Insert the hierarchical relationship in the closure table.
			if(empty($parentId)) $parentId = $menuId;
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length)
				 SELECT t.ancestor, :menuId, t.path_length+1
				 FROM $this->closureTable AS t
				 WHERE t.descendant = :parentId
				 UNION ALL
					SELECT :menuId, :menuId, 0"
			);
			$stmt1->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt1->bindParam(':parentId', $parentId, PDO::PARAM_INT);
			$stmt1->execute();
			// Get the root node for the new menu and update the closure table with the root value.
			$rootNode = $this->getRootNodeForChild($menuId);
			$stmt2 = self::$dbh->prepare(
				"UPDATE $this->closureTable AS t SET root = :rootNode
				 WHERE t.descendant = :menuId"
			);
			$stmt2->bindParam(':rootNode', $rootNode, PDO::PARAM_INT);
			$stmt2->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			if($success === TRUE) {
				return $menuId;
			}
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function insertMenuQuick($menu, $parentId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->menusTable
					 (menu_name, menu_alias, menu_url, published, order_of_item, date_created, created_by)
				 VALUES
					 (:name, :alias, :url, :published, :orderOfItem, :dateCreated,  :createdBy)"
			);
			$stmt->bindParam(':name', $menu['name'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $menu['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':url', $menu['url'], PDO::PARAM_STR);
			$stmt->bindParam(':published', $menu['state'], PDO::PARAM_INT);
			$stmt->bindParam(':orderOfItem', $menu['orderOfItem'], PDO::PARAM_INT);
			$stmt->bindParam(':dateCreated', $menu['dateCreated'], PDO::PARAM_STR);
			$stmt->bindParam(':createdBy', $menu['createdBy'], PDO::PARAM_STR);
			$stmt->execute();
			$menuId = self::$dbh->lastInsertId('id');
			// Insert the hierarchical relationship in the closure table.
			if(empty($parentId)) $parentId = $menuId;
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length)
				 SELECT t.ancestor, :menuId, t.path_length+1
				 FROM $this->closureTable AS t
				 WHERE t.descendant = :parentId
				 UNION ALL
				 SELECT :menuId, :menuId, 0"
			);
			$stmt1->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt1->bindParam(':parentId', $parentId, PDO::PARAM_INT);
			$stmt1->execute();
			// Get the root node for the new menu and update the closure table with the root value.
			$rootNode = $this->getRootNodeForChild($menuId);
			$stmt2 = self::$dbh->prepare("UPDATE $this->closureTable AS t SET root = :rootNode
										  WHERE t.descendant = :menuId");
			$stmt2->bindParam(':rootNode', $rootNode, PDO::PARAM_INT);
			$stmt2->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			if($success === TRUE) {
				return $menuId;
			}
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateMenu($menu) {
		try {
			if(!self::$dbh) $this->connect();
			$parentId = $this->getParentId($menu['id']);
			// If the parent id hAS mhanged from the original move the node/subtree.
			if($parentId !== $menu['parent'] && !empty($menu['parent']) && $menu['parent'] !== 0) {
				$moveSubTree = $this->moveSubTreeToParent($menu['id'], $menu['parent']);
				if(empty($moveSubTree)) {
					self::$dbh->rollBack();
					return FALSE;
				}
			} elseif($parentId !== $menu['parent'] && $menu['parent'] == 0 || empty($menu['parent'])) {
				$moveSubTree = $this->moveSubTreeToRoot($menu['id']);
				if(empty($moveSubTree)) {
					self::$dbh->rollBack();
					return FALSE;
				}
			}
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->menusTable
				 SET menu_name = :name
				   , menu_alias = :alias
				   , menu_url = :url
				   , published = :state
				   , description = :description
				   , date_created = :dateCreated
				   , created_by = :createdBy
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $menu['id'], PDO::PARAM_INT);
			$stmt->bindParam(':name', $menu['name'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $menu['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':url', $menu['url'], PDO::PARAM_STR);
			$stmt->bindParam(':state', $menu['state'], PDO::PARAM_INT);
			$stmt->bindParam(':description', $menu['description'], PDO::PARAM_STR);
			$stmt->bindParam(':dateCreated', $menu['dateCreated'], PDO::PARAM_STR);
			$stmt->bindParam(':createdBy', $menu['createdBy'], PDO::PARAM_STR);
			$stmt->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			unlink(DIR_IMAGE . 'menus/' . $image['image']['name']);
			$this->errorLog($e);
		}
	}

	public function updateOrder($menuId1, $order1, $menuId2, $order2) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("UPDATE $this->menusTable SET order_of_item = :order WHERE id = :id");
			$stmt->bindParam(':id', $menuId1, PDO::PARAM_INT);
			$stmt->bindParam(':order', $order1, PDO::PARAM_INT);
			$stmt->execute();
			$stmt1 = self::$dbh->prepare("UPDATE $this->menusTable SET order_of_item = :order WHERE id = :id");
			$stmt1->bindParam(':id', $menuId2, PDO::PARAM_INT);
			$stmt1->bindParam(':order', $order2, PDO::PARAM_INT);
			$stmt1->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteMenu($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->menusTable WHERE id = :menuId");
			$stmt->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteMenuLeafNode($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->closureTable WHERE descendant = :menuId");
			$stmt->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteMenuTree($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"DELETE FROM $this->closureTable
				 WHERE descendant IN
					(SELECT descendant FROM (SELECT descendant FROM $this->closureTable WHERE ancestor = :menuId) AS t)"
			);
			$stmt->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function moveSubTreeToRoot($menuIdToMove) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"DELETE a FROM $this->closureTable AS a
				 JOIN $this->closureTable AS d ON a.descendant = d.descendant
				 LEFT JOIN $this->closureTable AS x
				 ON x.ancestor = d.ancestor AND x.descendant = a.ancestor
				 WHERE d.ancestor = :menuIdToMove AND x.ancestor IS NULL"
			);
			$stmt->bindParam(':menuIdToMove', $menuIdToMove, PDO::PARAM_INT);
			$stmt->execute();
			$stmt1 = self::$dbh->prepare(
				"UPDATE $this->closureTable SET root = :menuIdToMove
				 WHERE ancestor IN
					(SELECT descendant FROM (SELECT descendant FROM $this->closureTable WHERE ancestor = :menuIdToMove) AS t)"
			);
			$stmt1->bindParam(':menuIdToMove', $menuIdToMove, PDO::PARAM_INT);
			$stmt1->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function moveSubTreeToParent($menuIdToMove, $newParent) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"DELETE a FROM $this->closureTable AS a
				 JOIN $this->closureTable AS d ON a.descendant = d.descendant
				 LEFT JOIN $this->closureTable AS x
				 ON x.ancestor = d.ancestor AND x.descendant = a.ancestor
				 WHERE d.ancestor = :menuIdToMove AND x.ancestor IS NULL"
			);
			$stmt->bindParam(':menuIdToMove', $menuIdToMove, PDO::PARAM_INT);
			$stmt->execute();
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length, root)
				 SELECT supertree.ancestor, subtree.descendant, supertree.path_length + subtree.path_length + 1, supertree.root
				 FROM $this->closureTable AS supertree
				 JOIN $this->closureTable AS subtree
				 WHERE subtree.ancestor = :menuIdToMove AND supertree.descendant = :newParent"
			);
			$stmt1->bindParam(':menuIdToMove', $menuIdToMove, PDO::PARAM_INT);
			$stmt1->bindParam(':newParent', $newParent, PDO::PARAM_INT);
			$stmt1->execute();
			$stmt2 = self::$dbh->prepare(
				"UPDATE $this->closureTable SET root = :newParent
				 WHERE ancestor IN
					(SELECT descendant FROM (SELECT descendant FROM $this->closureTable WHERE ancestor = :menuIdToMove) AS t)"
			);
			$stmt2->bindParam(':menuIdToMove', $menuIdToMove, PDO::PARAM_INT);
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
				"SELECT m.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable AS t ON m.id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0
				 ORDER BY order_of_item ASC"
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
				"SELECT m.id
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable AS t ON m.id = t.ancestor
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
				"SELECT m.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable AS t ON m.id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0
				 ORDER BY order_of_item ASC
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
				"SELECT m.*, t.*, MAX(path_length)
				 FROM $this->menusTable AS m
				 JOIN $this->closureTable AS t ON m.id = t.ancestor
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
				"SELECT m.*
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable AS t ON m.id = t.ancestor
				 WHERE t.root = t.ancestor AND t.path_length = 0 AND m.order_of_item = :order"
			);
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getParentId($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT MAX(ancestor) AS parent FROM $this->closureTable as t
				 RIGHT JOIN $this->menusTable AS m
				 ON t.descendant = m.id
				 WHERE m.id = :id AND t.ancestor != t.descendant"
			);
			$stmt->bindParam(':id', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['parent'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAllChildNodes($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable as t
				 ON m.id = t.descendant
				 WHERE ancestor = :menuId AND t.path_length = 1
				 ORDER BY order_of_item ASC"
			);
			$stmt->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfChildren($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable as t
				 ON m.id = t.descendant
				 WHERE ancestor = :menuId AND t.path_length = 1"
			);
			$stmt->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getChildByOrder($menuId, $order) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable as t
				 ON m.id = t.descendant
				 WHERE ancestor = :menuId AND t.path_length = 1 AND order_of_item = :order"
			);
			$stmt->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAncestors($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, t.* FROM $this->menusTable AS m
				 JOIN $this->closureTable AS t ON m.id = t.ancestor
				 WHERE t.descendant = :menuId"
			);
			$stmt->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDescendants($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, t.* FROM $this->menusTable AS m
				 JOIN $this->closureTable AS t ON m.id = t.descendant
				 WHERE t.ancestor = :menuId"
			);
			$stmt->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDescendantIds($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT descendant FROM $this->closureTable
				 WHERE ancestor = :menuId"
			);
			$stmt->bindParam(':menuId', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getDescendantsWithoutSelf($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, t.* FROM $this->menusTable AS m
				 JOIN $this->closureTable AS t ON m.id = t.descendant
				 WHERE t.ancestor = :menuId AND t.descendant != :menuId"
			);
			$stmt->bindParam(':menuId', $menuId, PDO::PARAM_INT);
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

	public function getMenuById($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable AS t
				 ON m.id = t.ancestor
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getMenuByAlias($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, t.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = t.root AND descendant = t.descendant)
								   AS root_distance
				 FROM $this->menusTable AS m
				 LEFT JOIN $this->closureTable AS t
				 ON m.id = t.ancestor
				 WHERE menu_alias = :alias"
			);
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getMenuName($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT menu_name FROM $this->menusTable WHERE id=:id");
			$stmt->bindParam(':id', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['menu_name'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getState($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT published FROM $this->menusTable WHERE id=:id");
			$stmt->bindParam(':id', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['published'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setState($menuId, $state) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->menusTable SET published = :state WHERE id=:id");
			$stmt->bindParam(':id', $menuId, PDO::PARAM_INT);
			$stmt->bindParam(':state', $state, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getOrder($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT order_of_item FROM $this->menusTable WHERE id = :id");
			$stmt->bindParam(':id', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setOrder($menuId, $order) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->menusTable SET order_of_item = :order WHERE id = :id");
			$stmt->bindParam(':id', $menuId, PDO::PARAM_INT);
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfMenus() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->menusTable");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getOrderOfItem($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT order_of_item FROM $this->menusTable WHERE id = :id");
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
			$stmt = self::$dbh->prepare("UPDATE $this->menusTable SET order_of_item = :orderOfItem WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':orderOfItem', $orderOfItem, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getImage($menuId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT image, image_alt, image_path FROM $this->menusTable WHERE id = :id");
			$stmt->bindParam(':id', $menuId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setImage($menuId, $fileName, $path) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->menusTable SET image = :fileName, image_path = :path WHERE id = :id");
			$stmt->bindParam(':id', $menuId, PDO::PARAM_INT);
			$stmt->bindParam(':fileName', $fileName, PDO::PARAM_STR);
			$stmt->bindParam(':path', $path, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
