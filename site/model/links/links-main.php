<?php
namespace RevenantBlue\Site;
use RevenantBlue;
use \PDO;

class Links extends RevenantBlue\Db {

	public  $whiteList = array(
		'id'
	  , 'link_name'
	  , 'link_url'
	  , 'link_image'
	  , 'link_description'
	  , 'link_target'
	  , 'link_rel'
	  , 'link_author'
	  , 'create_date'
	  , 'published'
	  , 'category_title'
	  , 'category_alias'
	  , 'category_description'
	  , 'category_image'
	);
	private $linksTable;                  // Contains data on individual links.
	private $linkJoinTable;               // A many to many relationship table between links and categories
	private $linkCatTable;                // Contains the names of link categories.
	private $usersTable;

	public function __construct() {
		$this->linksTable = PREFIX . 'links';
		$this->linkJoinTable = PREFIX . 'link_category_rels';
		$this->linkCatTable = PREFIX . 'link_categories'; 
		$this->usersTable = PREFIX . 'users';
	}

	public function loadLinksOverview($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->linksTable
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

	public function countLinks() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->linksTable");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadLinkSearch($limit, $offset, $searchWord, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->linksTable AS l
				 WHERE link_name LIKE :searchWord
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countLinkSearch($searchWord) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->linksTable
				 WHERE link_name LIKE :searchWord"
			);
			$stmt->bindValue(':searchWord', $searchWord, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadLinksByPublished($limit, $offset, $publishedState, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->linksTable
				 WHERE published = :published
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countLinksByPublished($publishedState) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->linksTable WHERE published = :published");
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadLinksByCategory($limit, $offset, $categoryId, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT l.* FROM $this->linksTable AS l
				 LEFT JOIN $this->linkJoinTable AS lj ON lj.link_id = l.id
				 WHERE lj.category_id = :categoryId
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countLinksByCategory($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->linksTable AS l
				 LEFT JOIN $this->linkJoinTable AS lj ON lj.link_id = l.id
				 WHERE lj.category_id = :categoryId"
			);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function loadLinksByPublishedAndCategory($limit, $offset, $published, $categoryId, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT l.* FROM $this->linksTable AS l
				 LEFT JOIN $this->linkJoinTable AS lj ON lj.link_id = l.id
				 WHERE l.published = :published AND lj.category_id = :categoryId
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':published', $published, PDO::PARAM_INT);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countLinksByPublishedAndCategory($published, $categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->linksTable AS l
				 LEFT JOIN $this->linkJoinTable AS lj ON lj.link_id = l.id
				 WHERE l.published = :published AND lj.category_id = :categoryId"
			);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->bindParam(':published', $published, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadCategoryOverview($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT lc.*, COUNT(lj.id) AS num_of_links FROM $this->linkCatTable AS lc
				 LEFT JOIN $this->linkJoinTable AS lj ON lj.category_id = lc.id
				 GROUP BY lc.id
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

	public function countLinkCategories() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->linkCatTable");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadCategorySearch($limit, $offset, $searchWord, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT lc.*, COUNT(lj.id) AS num_of_links FROM $this->linkCatTable AS lc
				 LEFT JOIN $this->linkJoinTable AS lj ON lj.category_id = lc.id
				 WHERE category_title LIKE :searchWord
				 GROUP BY lc.id
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset");
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countCategorySearch($searchWord) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->linkCatTable AS lc
				 WHERE category_title LIKE :searchWord"
			);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertLink($link) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->linksTable 
					(link_name, create_date, link_url, published, link_image, link_description, link_target, link_rel, link_author, link_weight)
				 VALUES
					(:linkName, NOW(), :linkUrl, :published, :linkImage, :linkDescription, :linkTarget, :linkRel, :linkAuthor, :linkWeight)"
			);
			$stmt->bindParam(':linkName', $link['name'], PDO::PARAM_STR);
			$stmt->bindParam(':linkUrl', $link['url'], PDO::PARAM_STR);
			$stmt->bindParam(':published', $link['published'], PDO::PARAM_INT);
			$stmt->bindParam(':linkImage', $link['image'], PDO::PARAM_STR);
			$stmt->bindParam(':linkDescription', $link['description'], PDO::PARAM_STR);
			$stmt->bindParam(':linkTarget', $link['target'], PDO::PARAM_STR);
			$stmt->bindParam(':linkRel', $link['rel'], PDO::PARAM_STR);
			$stmt->bindParam(':linkAuthor', $link['author'], PDO::PARAM_INT);
			$stmt->bindParam(':linkWeight', $link['weight'], PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertCategory($linkCatArr) {
		try {
			if(!self::$dbh) $this->connect();
			// Get the number of categories
			$stmt = self::$dbh->prepare(
				"SELECT COUNT(*) AS num_of_categories FROM $this->linkCatTable"
			);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$orderOfItem = (int)$result['num_of_categories'] + 1;
			
			$stmt2 = self::$dbh->prepare(
				"INSERT INTO $this->linkCatTable
					(category_title, category_alias, category_description, category_image, order_of_item)
				 VALUES
					(:categoryTitle, :categoryAlias, :categoryDescription, :categoryImage, :orderOfItem)"
			);
			$stmt2->bindParam(':categoryTitle', $linkCatArr['name'], PDO::PARAM_STR);
			$stmt2->bindParam(':categoryAlias', $linkCatArr['alias'], PDO::PARAM_STR);
			$stmt2->bindParam(':categoryDescription', $linkCatArr['description'], PDO::PARAM_STR);
			$stmt2->bindParam(':categoryImage', $linkCatArr['image'], PDO::PARAM_STR);
			$stmt2->bindParam(':orderOfItem', $orderOfItem, PDO::PARAM_INT);
			$stmt2->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertCategoryForLink($linkId, $linkCatId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->linkJoinTable (link_id, category_id)
				 VALUES (:linkId, :categoryId)"
			);
			$stmt->bindParam(':linkId', $linkId, PDO::PARAM_INT);
			$stmt->bindParam(':categoryId', $linkCatId, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateLink($link) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->linksTable
				 SET link_name = :linkName
				   , link_url = :linkUrl
				   , published = :published
				   , link_image = :linkImage
				   , link_description = :linkDescription
				   , link_target = :linkTarget
				   , link_rel = :linkRel
				   , link_author = :linkAuthor
				   , link_weight = :linkWeight
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $link['id'], PDO::PARAM_INT);
			$stmt->bindParam(':linkName', $link['name'], PDO::PARAM_STR);
			$stmt->bindParam(':linkUrl', $link['url'], PDO::PARAM_STR);
			$stmt->bindParam(':published', $link['published'], PDO::PARAM_STR);
			$stmt->bindParam(':linkImage', $link['image'], PDO::PARAM_STR);
			$stmt->bindParam(':linkDescription', $link['description'], PDO::PARAM_STR);
			$stmt->bindParam(':linkTarget', $link['target'], PDO::PARAM_STR);
			$stmt->bindParam(':linkRel', $link['rel'], PDO::PARAM_STR);
			$stmt->bindParam(':linkAuthor', $link['author'], PDO::PARAM_INT);
			$stmt->bindParam(':linkWeight', $link['weight'], PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateCategory($linkCat) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->linkCatTable
				 SET category_title = :categoryTitle, category_description = :categoryDescription, category_image = :categoryImage
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $linkCat['id'], PDO::PARAM_INT);
			$stmt->bindParam(':categoryTitle', $linkCat['name'], PDO::PARAM_STR);
			$stmt->bindParam(':categoryDescription', $linkCat['description'], PDO::PARAM_STR);
			$stmt->bindParam(':categoryImage', $linkCat['image'], PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteLink($linkId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("DELETE FROM $this->linksTable WHERE id = :linkId");
			$stmt->bindParam(':linkId', $linkId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("DELETE FROM $this->linkJoinTable WHERE link_id = :linkId");
			$stmt2->bindParam(':linkId', $linkId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteCategory($linkCatId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("SELECT order_of_item FROM $this->linkCatTable WHERE id = :linkCatId");
			$stmt->bindParam(':linkCatId', $linkCatId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$order = (int)$result['order_of_item'];
			$stmt2 = self::$dbh->prepare("UPDATE $this->linkCatTable SET order_of_item = order_of_item - 1 WHERE order_of_item > :order");
			$stmt2->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt2->execute();
			$stmt3 = self::$dbh->prepare("DELETE FROM $this->linkCatTable WHERE id = :linkCatId ");
			$stmt3->bindParam(':linkCatId', $linkCatId, PDO::PARAM_INT);
			$stmt3->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteCategoryForLink($linkId, $linkCatId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->linkJoinTable WHERE link_id = :linkId AND category_id = :linkCatId ");
			$stmt->bindParam(':linkId', $linkId, PDO::PARAM_INT);
			$stmt->bindParam(':linkCatId', $linkCatId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getLink($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->linksTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCategoriesForLink($linkId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->linkJoinTable AS lj
										 LEFT JOIN $this->linksTable AS l ON l.id = lj.link_id
										 LEFT JOIN $this->linkCatTable AS lc ON lc.id = lj.category_id
										 WHERE l.id = :linkId");
			$stmt->bindParam(':linkId', $linkId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getCategoryIdsForLink($linkId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT lj.category_id FROM $this->linkJoinTable AS lj
				 LEFT JOIN $this->linksTable AS l ON l.id = lj.link_id
				 LEFT JOIN $this->linkCatTable AS lc ON lc.id = lj.category_id
				 WHERE l.id = :linkId"
			);
			$stmt->bindParam(':linkId', $linkId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCategoryNames($sort = 'DESC') {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT id, category_title FROM $this->linkCatTable ORDER BY category_title $sort");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getPopularCategories() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, COUNT(ljt.link_id) AS num_of_links
				 FROM $this->linksTable AS l
				 LEFT JOIN $this->linkJoinTable AS ljt ON l.id = ljt.link_id
				 LEFT JOIN $this->linkCatTable AS lc ON lc.id = ljt.category_id
				 GROUP BY ljt.category_id"
			);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorlog($e);
		}
	}

	public function setLinkState($linkId, $state) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->linksTable SET published = :state WHERE id = :id");
			$stmt->bindParam(':id', $linkId, PDO::PARAM_INT);
			$stmt->bindParam(':state', $state, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getLinkCategory($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->linkCatTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getLinkCategoryByAlias($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->linkCatTable WHERE category_alias = :alias");
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getImage($linkId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT link_image, link_image_alt, link_image_path FROM $this->linksTable WHERE id = :id");
			$stmt->bindParam(':id', $linkId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function setImage($linkId, $fileName, $path) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->linksTable SET link_image = :fileName, link_image_path = :path WHERE id = :id");
			$stmt->bindParam(':id', $linkId, PDO::PARAM_INT);
			$stmt->bindParam(':fileName', $fileName, PDO::PARAM_STR);
			$stmt->bindParam(':path', $path, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getIdByOrder($order) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT id FROM $this->linkCatTable WHERE order_of_item = :order");
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function setCategoryOrder($id, $order) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->linkCatTable SET order_of_item = :order WHERE id = :id");
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
