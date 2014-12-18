<?php
namespace RevenantBlue\Admin;
use RevenantBlue;
use \PDO;

class PhotoGallery extends RevenantBlue\Db {

	public  $whiteList = array('id', 'title', 'state', 'featured', 'order_of_item', 'hits', 'date_created');
	private $galleryTable;			// Holds the name of the table to be used throughout the class.  Must contain neseted set structure.
	private $closureTable;			// The closure table for keeping track of the hierarchy.
	private $imageQualityTable;
	private $templateTable;

	public function __construct() {
		$this->galleryTable = PREFIX . 'photo_gallery';    
		$this->closureTable = PREFIX . 'photo_gallery_closure';
		$this->imageQualityTable = PREFIX . 'image_quality';
		$this->templateTable = PREFIX . 'photogallery_template';
	}

	public function getRootNodes() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.*, c.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = c.root AND descendant = c.descendant)
								   AS root_distance
				 FROM $this->galleryTable AS g
				 LEFT JOIN $this->closureTable AS c ON g.id = c.ancestor
				 WHERE c.root = c.ancestor AND c.path_length = 0;"
			);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadAlbums($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.*, DATE_FORMAT(date_created, '%m-%d-%Y %h:%i %p') AS date_created, DATE_FORMAT(modified_date, '%m-%d-%Y %h:%i %p') AS modified_date, c.*
					  , (SELECT path_length FROM $this->closureTable WHERE ancestor = c.root AND descendant = c.descendant) AS root_distance
				 FROM $this->galleryTable AS g
				 LEFT JOIN $this->closureTable AS c ON g.id = c.ancestor
				 WHERE c.root = c.ancestor AND c.path_length = 0
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

	public function countAlbums() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.*, c.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = c.root AND descendant = c.descendant)
								   AS root_distance
				 FROM $this->galleryTable AS g
				 LEFT JOIN $this->closureTable AS c ON g.id = c.ancestor
				 WHERE c.root = c.ancestor AND c.path_length = 0"
			);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadAlbumsBySearch($limit, $offset, $searchWord, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.*, c.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = c.root AND descendant = c.descendant)
								   AS root_distance
				 FROM $this->galleryTable AS g
				 LEFT JOIN $this->closureTable AS c ON g.id = c.ancestor
				 WHERE c.root = c.ancestor AND c.path_length = 0 AND g.title LIKE :searchWord
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

	public function countAlbumsBySearch($searchWord) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.*, c.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = c.root AND descendant = c.descendant)
								   AS root_distance
				 FROM $this->galleryTable AS g
				 LEFT JOIN $this->closureTable AS c ON g.id = c.ancestor
				 WHERE c.root = c.ancestor AND c.path_length = 0 AND g.title LIKE :searchWord"
			);
			$stmt->bindValue(':searchWord', $searchWord, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadAlbumsByState($limit, $offset, $state, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.*, c.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = c.root AND descendant = c.descendant)
								   AS root_distance
				 FROM $this->galleryTable AS g
				 LEFT JOIN $this->closureTable AS c ON g.id = c.ancestor
				 WHERE c.root = c.ancestor AND c.path_length = 0 AND g.state = :state
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':state', $state, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countAlbumsByState($state) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.*, c.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = c.root AND descendant = c.descendant)
								   AS root_distance
				 FROM $this->galleryTable AS g
				 LEFT JOIN $this->closureTable AS c ON g.id = c.ancestor
				 WHERE c.root = c.ancestor AND c.path_length = 0 AND g.state = :state"
			);
			$stmt->bindParam(':state', $state, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadAlbumsByFeatured($limit, $offset, $featured, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.*, c.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = c.root AND descendant = c.descendant)
								   AS root_distance
				 FROM $this->galleryTable AS g
				 LEFT JOIN $this->closureTable AS c ON g.id = c.ancestor
				 WHERE c.root = c.ancestor AND c.path_length = 0 AND g.featured = :featured
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':featured', $featured, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countAlbumsByFeatured($featured) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.*, c.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = c.root AND descendant = c.descendant)
								   AS root_distance
				 FROM $this->galleryTable AS g
				 LEFT JOIN $this->closureTable AS c ON g.id = c.ancestor
				 WHERE c.root = c.ancestor AND c.path_length = 0 AND g.featured = :featured"
			);
			$stmt->bindParam(':featured', $featured, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadAlbumList($limit, $offset, $stateState) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->galleryTable WHERE type='album' AND state >= :stateState
				 ORDER BY order_of_item
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':stateState', $stateState, PDO::PARAM_BOOL);
			$stmt->execute();
			$depthList = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $depthList;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadPhotos($albumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.*, c.*, (SELECT path_length FROM $this->closureTable
								   WHERE ancestor = c.root AND descendant = c.descendant)
								   AS root_distance
				 FROM $this->galleryTable as g
				 LEFT JOIN $this->closureTable as c
				 ON g.id = c.descendant
				 WHERE ancestor = :albumId AND c.path_length = 1
				 ORDER BY order_of_item ASC"
			);
			$stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadPhotosLimit($parentId, $orderOfItem) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->galleryTable as g
				 LEFT JOIN $this->closureTable as c
				 ON c.descendant = g.id
				 WHERE ancestor = :parentId AND order_of_item=:orderOfItem AND ancestor != descendant
				 ORDER BY order_of_item ASC
				 LIMIT 1
				 OFFSET 0"
			);
			$stmt->bindParam(':parentId', $parentId, PDO::PARAM_INT);
			$stmt->bindParam(':orderOfItem', $orderOfItem, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadFeaturedPhotos($limit, $offset) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->galleryTable
				 WHERE type=:type AND featured=:frontpage
				 ORDER BY order_of_item
				 LIMIT  :limit
				 OFFSET :offset"
			);
			$stmt->bindValue(':type', 'photo', PDO::PARAM_STR);
			$stmt->bindValue(':frontpage', 1, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			$frontPageImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $frontPageImages;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertGalleryNode($gallery, $parentId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->galleryTable
					 (title, alias, image, image_alt, image_path, state, featured, image_size, description, order_of_item, date_created,
					  created_by, meta_description, meta_keywords, meta_robots, meta_author, template, image_url)
				 VALUES
					 (:title, :alias, :image, :imageAlt, :imagePath, :state, :featured, :imageSize, :description, :orderOfItem,
					  :dateCreated,  :createdBy, :metaDescription, :metaKeywords, :metaRobots, :metaAuthor, :template, :imageUrl)"
			);
			$stmt->bindValue(':title', $gallery['title'], PDO::PARAM_STR);
			$stmt->bindValue(':alias', $gallery['alias'], PDO::PARAM_STR);
			$stmt->bindValue(':image', $gallery['image'], PDO::PARAM_STR);
			$stmt->bindParam(':imageAlt', $gallery['imageAlt'], PDO::PARAM_STR);
			$stmt->bindParam(':imagePath', $gallery['imagePath'], PDO::PARAM_STR);
			$stmt->bindValue(':state', $gallery['state'], PDO::PARAM_INT);
			$stmt->bindValue(':featured', $gallery['featured'], PDO::PARAM_INT);
			$stmt->bindValue(':imageSize', $gallery['imageSize'], PDO::PARAM_INT);
			$stmt->bindValue(':description', $gallery['description'], PDO::PARAM_STR);
			$stmt->bindValue(':orderOfItem', $gallery['orderOfItem'], PDO::PARAM_INT);
			$stmt->bindValue(':dateCreated', $gallery['dateCreated'], PDO::PARAM_STR);
			$stmt->bindValue(':createdBy', $gallery['createdBy'], PDO::PARAM_STR);
			$stmt->bindValue(':metaDescription', $gallery['metaDescription'], PDO::PARAM_STR);
			$stmt->bindValue(':metaKeywords', $gallery['metaKeywords'], PDO::PARAM_STR);
			$stmt->bindValue(':metaRobots', $gallery['metaRobots'], PDO::PARAM_STR);
			$stmt->bindValue(':metaAuthor', $gallery['metaAuthor'], PDO::PARAM_STR);
			$stmt->bindValue(':template', $gallery['template'], PDO::PARAM_INT);
			$stmt->bindValue(':imageUrl', $gallery['imageUrl'], PDO::PARAM_STR);
			$stmt->execute();
			$newAlbumId = self::$dbh->lastInsertId('id');
			// Insert the hierarchical relationship in the closure table.
			if(empty($parentId)) $parentId = $newAlbumId;
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length)
				 SELECT t.ancestor, :albumId, t.path_length+1
				 FROM $this->closureTable AS t
				 WHERE t.descendant = :parentId
				 UNION ALL
				 SELECT :albumId, :albumId, 0"
			);
			$stmt1->bindParam(':albumId', $newAlbumId, PDO::PARAM_INT);
			$stmt1->bindParam(':parentId', $parentId, PDO::PARAM_INT);
			$stmt1->execute();
			// Get the root node for the new gallery and update the closure table with the root value.
			$rootNode = $this->getRootNodeForChild($newAlbumId);
			$stmt2 = self::$dbh->prepare(
				"UPDATE $this->closureTable AS t SET root = :rootNode
				 WHERE t.descendant = :albumId"
			);
			$stmt2->bindParam(':rootNode', $rootNode, PDO::PARAM_INT);
			$stmt2->bindParam(':albumId', $newAlbumId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			if($success === TRUE) {
				return $newAlbumId;
			}
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function insertGalleryNodeQuick($gallery, $parentId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->galleryTable
					 (title, alias, parent_alias, image, state, image_size, order_of_item, date_created, created_by, image_url, image_path)
				 VALUES
					 (:title, :alias, :parentAlias, :image, :state, :imageSize, :orderOfItem, :dateCreated,  :createdBy, :imageUrl, :imagePath)"
			);
			$stmt->bindValue(':title', $gallery['title'], PDO::PARAM_STR);
			$stmt->bindValue(':alias', $gallery['alias'], PDO::PARAM_STR);
			$stmt->bindValue(':parentAlias', $gallery['parentAlias'], PDO::PARAM_STR);
			$stmt->bindValue(':image', $gallery['image'], PDO::PARAM_STR);
			$stmt->bindValue(':imageSize', $gallery['imageSize'], PDO::PARAM_INT);
			$stmt->bindValue(':state', $gallery['state'], PDO::PARAM_INT);
			$stmt->bindValue(':orderOfItem', $gallery['orderOfItem'], PDO::PARAM_INT);
			$stmt->bindValue(':dateCreated', $gallery['dateCreated'], PDO::PARAM_STR);
			$stmt->bindValue(':createdBy', $gallery['createdBy'], PDO::PARAM_STR);
			$stmt->bindValue(':imageUrl', $gallery['imageUrl'], PDO::PARAM_STR);
			$stmt->bindValue(':imagePath', $gallery['imagePath'], PDO::PARAM_STR);
			$stmt->execute();
			$newAlbumId = self::$dbh->lastInsertId('id');
			// Insert the hierarchical relationship in the closure table.
			if(empty($parentId)) $parentId = $newAlbumId;
			$stmt1 = self::$dbh->prepare(
				"INSERT INTO $this->closureTable (ancestor, descendant, path_length)
				 SELECT t.ancestor, :albumId, t.path_length+1
				 FROM $this->closureTable AS t
				 WHERE t.descendant = :parentId
				 UNION ALL
				 SELECT :albumId, :albumId, 0"
			);
			$stmt1->bindParam(':albumId', $newAlbumId, PDO::PARAM_INT);
			$stmt1->bindParam(':parentId', $parentId, PDO::PARAM_INT);
			$stmt1->execute();
			// Get the root node for the new gallery and update the closure table with the root value.
			$rootNode = $this->getRootNodeForChild($newAlbumId);
			$stmt2 = self::$dbh->prepare("UPDATE $this->closureTable AS t SET root = :rootNode
										  WHERE t.descendant = :albumId");
			$stmt2->bindParam(':rootNode', $rootNode, PDO::PARAM_INT);
			$stmt2->bindParam(':albumId', $newAlbumId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			if($success === TRUE) {
				return $newAlbumId;
			}
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function insertTemplate($templateName, $thumbWidth, $thumbHeight, $imageWidth, $imageHeight, $type) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->templateTable
				   (template_name, thumbnail_width, thumbnail_height, image_width, image_height, type)
				 VALUES
				   (:templateName, :thumbWidth, :thumbHeight, :imageWidth, :imageHeight, :type)"
			);
			$stmt->bindParam(':templateName', $templateName, PDO::PARAM_STR);
			$stmt->bindParam(':thumbWidth', $thumbWidth, PDO::PARAM_INT);
			$stmt->bindParam(':thumbHeight', $thumbHeight, PDO::PARAM_INT);
			$stmt->bindParam(':imageWidth', $imageWidth, PDO::PARAM_INT);
			$stmt->bindParam(':imageHeight', $imageHeight, PDO::PARAM_INT);
			$stmt->bindParam(':type', $type, PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateAlbum($album) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->galleryTable
				 SET title = :title
				   , alias = :alias
				   , image = :image
				   , image_alt = :imageAlt
				   , image_path = :imagePath
				   , state = :state
				   , featured = :featured
				   , image_size = :imageSize
				   , description = :description
				   , date_created = :dateCreated
				   , created_by = :createdBy
				   , meta_description = :metaDescription
				   , meta_keywords = :metaKeywords
				   , meta_robots = :metaRobots
				   , meta_author = :metaAuthor
				   , template = :template
				 WHERE id = :id"
			);
			$stmt->bindValue(':id', $album['id'], PDO::PARAM_INT);
			$stmt->bindValue(':title', $album['title'], PDO::PARAM_STR);
			$stmt->bindValue(':alias', $album['alias'], PDO::PARAM_STR);
			$stmt->bindValue(':image', $album['image'], PDO::PARAM_STR);
			$stmt->bindParam(':imageAlt', $album['imageAlt'], PDO::PARAM_STR);
			$stmt->bindParam(':imagePath', $album['imagePath'], PDO::PARAM_STR);
			$stmt->bindValue(':state', $album['state'], PDO::PARAM_INT);
			$stmt->bindValue(':featured', $album['featured'], PDO::PARAM_INT);
			$stmt->bindValue(':imageSize', $album['imageSize'], PDO::PARAM_INT);
			$stmt->bindValue(':description', $album['description'], PDO::PARAM_STR);
			$stmt->bindValue(':dateCreated', $album['dateCreated'], PDO::PARAM_STR);
			$stmt->bindValue(':createdBy', $album['createdBy'], PDO::PARAM_STR);
			$stmt->bindValue(':metaDescription', $album['metaDescription'], PDO::PARAM_STR);
			$stmt->bindValue(':metaKeywords', $album['metaKeywords'], PDO::PARAM_STR);
			$stmt->bindValue(':metaRobots', $album['metaRobots'], PDO::PARAM_STR);
			$stmt->bindValue(':metaAuthor', $album['metaAuthor'], PDO::PARAM_STR);
			$stmt->bindValue(':template', $album['template'], PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updatePhoto($photoArr) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->galleryTable
				 SET title = :title, state = :state, featured = :featured, description = :description, image_alt = :alt, image_caption = :caption
				 WHERE id = :id"
			);
			$stmt->bindValue(':id', $photoArr['id'], PDO::PARAM_INT);
			$stmt->bindValue(':title', $photoArr['title'], PDO::PARAM_STR);
			$stmt->bindValue(':state', $photoArr['state'], PDO::PARAM_INT);
			$stmt->bindValue(':featured', $photoArr['featured'], PDO::PARAM_INT);
			$stmt->bindValue(':description', $photoArr['description'], PDO::PARAM_STR);
			$stmt->bindValue(':caption', $photoArr['caption'], PDO::PARAM_STR);
			$stmt->bindValue(':alt', $photoArr['alt'], PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updatePhotoAfterAliasChange($photoArr) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->galleryTable
				 SET parent_alias = :parentAlias, image_url = :imageUrl, image_path = :imagePath
				 WHERE id = :id"
			);
			$stmt->bindValue(':id', $photoArr['id'], PDO::PARAM_INT);
			$stmt->bindValue(':parentAlias', $photoArr['parentAlias'], PDO::PARAM_STR);
			$stmt->bindValue(':imageUrl', $photoArr['imageUrl'], PDO::PARAM_STR);
			$stmt->bindValue(':imagePath', $photoArr['imagePath'], PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateOrderOfItem($id, $order) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->galleryTable SET order_of_item = :order WHERE id=:id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateTemplate($id, $thumbWidth, $thumbHeight, $imageWidth, $imageHeight, $type) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->templateTable
				   SET thumbnail_width = :thumbWidth, thumbnail_height = :thumbHeight, image_width = :imageWidth, image_height = :imageHeight, type = :type
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':thumbWidth', $thumbWidth, PDO::PARAM_INT);
			$stmt->bindParam(':thumbHeight', $thumbHeight, PDO::PARAM_INT);
			$stmt->bindParam(':imageHeight', $imageHeight, PDO::PARAM_INT);
			$stmt->bindParam(':imageWidth', $imageWidth, PDO::PARAM_INT);
			$stmt->bindParam(':type', $type, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteGalleryItem($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"DELETE FROM $this->galleryTable
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deletePhotoNode($photoId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"DELETE FROM $this->closureTable
				 WHERE descendant = :photoId"
			);
			$stmt->bindParam(':photoId', $photoId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteAlbumNode($albumId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"DELETE FROM $this->closureTable
				 WHERE descendant IN
				 (SELECT descendant FROM (SELECT descendant FROM $this->closureTable WHERE ancestor = :albumId) AS t)"
			);
			$stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
			$stmt->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteTemplate($templateId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"DELETE FROM $this->templateTable
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $templateId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRootNodeIds() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.id
				 FROM $this->galleryTable AS c
				 LEFT JOIN $this->closureTable AS c ON g.id = t.ancestor
				 WHERE c.root = c.ancestor AND c.path_length = 0;"
			);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfRootNodes() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.id
				 FROM $this->galleryTable AS g
				 LEFT JOIN $this->closureTable AS c ON g.id = c.ancestor
				 WHERE c.root = c.ancestor AND c.path_length = 0;"
			);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRootNodeForChild($descendant) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.*, c.*, MAX(path_length)
				 FROM $this->galleryTable AS g
				 JOIN $this->closureTable AS c ON g.id = c.ancestor
				 WHERE c.descendant = :descendant"
			);
			$stmt->bindParam(':descendant', $descendant, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['ancestor'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfPhotos($albumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT g.*, c.* FROM $this->galleryTable AS g
				 JOIN $this->closureTable AS c ON g.id = c.descendant
				 WHERE c.ancestor = :albumId AND c.ancestor != c.descendant"
			);
			$stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getOrderOfItem($parent) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "SELECT order_of_item FROM $this->galleryTable WHERE parent=:parent";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':parent', $parent, PDO::PARAM_STR);
			$stmt->execute();
			$numberOfRows = $stmt->rowCount();
			return $numberOfRows;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setOrderOfItemOnDelete($id, $albumId = NULL) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("SELECT @currentOrder := order_of_item FROM $this->galleryTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			if(isset($albumId)) {
				$stmt2 = self::$dbh->prepare(
					"UPDATE $this->galleryTable AS g
					 LEFT JOIN $this->closureTable as c ON g.id = c.descendant
					 SET g.order_of_item = g.order_of_item - 1
					 WHERE g.order_of_item > @currentOrder - 1 AND c.ancestor = :albumId AND c.descendant != c.ancestor"
				);
				$stmt2->bindParam(':albumId', $albumId, PDO::PARAM_INT);
				$stmt2->execute();
			} else {
				$stmt2 = self::$dbh->prepare(
					"UPDATE $this->galleryTable AS g
					 LEFT JOIN $this->closureTable as c ON g.id = c.ancestor
					 SET order_of_item = order_of_item - 1
					 WHERE g.order_of_item  > @currentOrder - 1 AND c.ancestor = c.root"
				);
				$stmt2->execute();
			}
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function getGalleryItem($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, DATE_FORMAT(date_created, '%m-%d-%Y %h:%i %p') AS date_created, DATE_FORMAT(modified_date, '%m-%d-%Y %h:%i %p') AS modified_date
				 FROM $this->galleryTable WHERE id = :id"
			);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getGalleryItemByAlias($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, DATE_FORMAT(date_created, '%m-%d-%Y %h:%i %p') AS date_created, DATE_FORMAT(modified_date, '%m-%d-%Y %h:%i %p') AS modified_date
				 FROM $this->galleryTable WHERE alias=:alias");
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAlias($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT alias FROM $this->galleryTable WHERE id=:id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['alias'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getImage($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT image, image_path, image_alt, image_url FROM $this->galleryTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function setImage($id, $fileName, $filePath) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->galleryTable 
				SET image = :image
				  , image_path = :imagePath
				WHERE id = :id"
			);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':image', $fileName, PDO::PARAM_STR);
			$stmt->bindParam(':imagePath', $filePath, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getIdByAlias($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT id FROM $this->galleryTable WHERE alias = :alias");
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAliasById($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT alias FROM $this->galleryTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['alias'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getPhoto($photoId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->galleryTable WHERE id = :id");
			$stmt->bindParam(':id', $photoId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getParentId($photoId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT MAX(ancestor) AS parent FROM $this->closureTable as c
				 RIGHT JOIN $this->galleryTable as g
				 ON c.descendant = g.id
				 WHERE g.id = :id AND c.ancestor != c.descendant"
			);
			$stmt->bindParam(':id', $photoId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['parent'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getPhotoByOrder($photoOrder, $albumId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT alias FROM $this->galleryTable AS g
				 LEFT JOIN $this->closureTable AS c ON g.id = c.descendant
				 WHERE order_of_item = :orderOfItem AND c.ancestor = :albumId AND c.ancestor != c.descendant"
			);
			$stmt->bindParam(':orderOfItem', $photoOrder, PDO::PARAM_INT);
			$stmt->bindParam('albumId', $albumId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['alias'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAlbumList() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT title FROM $this->galleryTable
				 WHERE type='album'
				 ORDER BY title ASC"
			);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setPublishState($id, $publishState) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->galleryTable SET state = :publishState
				 WHERE id = :id"
			);
			$stmt->bindParam(':publishState', $publishState, PDO::PARAM_BOOL);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setHit($title) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "UPDATE  $this->galleryTable SET hits = hits + 1 WHERE title = :title";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':title', $title, PDO::PARAM_STR);
			$stmt->execute();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfFrontPageImages() {
		try {
			if(!self::$dbh) $this->connect();
			$query = "SELECT * FROM $this->galleryTable
					  WHERE featured = 1";
			$stmt = self::$dbh->prepare($query);
			$stmt->execute();
			$result = $stmt->rowCount();
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getImageName($title) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "SELECT title FROM $this->galleryTable WHERE title = :name";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':title', $title, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function fixOrderOfItem($parent) {
		try {
			if(!self::$dbh) $this->connect();
			$numOfNodes = self::getNumOfNodes($parent);
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->galleryTable WHERE parent = :parent");
			$stmt->bindParam(':parent', $parent, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$x = 1;
			foreach($result as $r) {
				$stmt2 = self::$dbh->prepare("UPDATE $this->galleryTable SET order_of_item = $x WHERE title = :title");
				$stmt2->bindParam(':title', $r['title'], PDO::PARAM_STR);
				$stmt2->execute();
				$x++;
			}
			self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function getImageQuality($imageType) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "SELECT quality FROM $this->imageQualityTable WHERE image_type=:imageType";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':imageType', $imageType, PDO::PARAM_STR);
			$stmt->execute();
			$imageQuality = $stmt->fetch(PDO::FETCH_ASSOC);
			return $imageQuality['quality'];
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function setImageQuality($imageType, $quality) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "UPDATE $this->imageQualityTable SET quality=:quality WHERE image_type=:imageType";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':imageType', $imageType, PDO::PARAM_STR);
			$stmt->bindParam(':quality', $quality, PDO::PARAM_INT);
			$stmt->execute();
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function getState($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT state FROM $this->galleryTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['state'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getFeatured($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT featured FROM $this->galleryTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['featured'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setState($id, $state) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->galleryTable SET state = :state WHERE id = :id");
			$stmt->bindParam(':state', $state, PDO::PARAM_INT);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setFeatured($id, $featured) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "UPDATE $this->galleryTable SET featured = :featured WHERE id = :id";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':featured', $featured, PDO::PARAM_BOOL);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function getTemplate($albumId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"SELECT template FROM $this->galleryTable
				 WHERE id = :albumId"
			);
			$stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$templateId = $result['template'];
			$stmt2 = self::$dbh->prepare(
				"SELECT * FROM $this->templateTable
				 WHERE id = :templateId"
			);
			$stmt2->bindParam(':templateId', $templateId, PDO::PARAM_INT);
			$stmt2->execute();
			$result = $stmt2->fetch(PDO::FETCH_ASSOC);
			self::$dbh->commit();
			return $result;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function getTemplates() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->templateTable");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getTemplateById($templateId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->templateTable WHERE id = :templateId"
			);
			$stmt->bindParam(':templateId', $templateId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
