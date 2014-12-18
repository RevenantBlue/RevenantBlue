<?php
namespace RevenantBlue\Admin;
use RevenantBlue;
use \PDO;

class Media extends RevenantBlue\Db {

	public  $whiteList = array('id', 'media_author', 'media_username', 'media_name', 'media_alias', 'date_posted', 'media_mime_type', 'media_title', 'media_caption', 'media_description', 'media_alt', 'media_url', 'media_ext');
	private $mediaTable;
	private $mediaThumbsTable;
	private $mediaAttachTable;
	private $usersTable;

	public function __construct() {
		$this->mediaTable = PREFIX . 'media';
		$this->mediaThumbsTable = PREFIX . 'media_thumbnails';
		$this->mediaAttachTable = PREFIX . 'media_attachments';
		$this->usersTable = PREFIX . 'users';
	}

	public function loadMediaLibrary($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, m.id AS id, u.username AS media_username
				 FROM $this->mediaTable as m
				 LEFT JOIN $this->usersTable as u ON m.media_author = u.id
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

	public function countMediaLibrary() {
		if(!self::$dbh) $this->connect();
		$stmt = self::$dbh->prepare("SELECT * FROM $this->mediaTable");
		$stmt->execute();
		return $stmt->rowCount();
	}

	public function loadMediaSearch($limit, $offset, $searchWord, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT m.*, m.id AS id, u.username AS media_username FROM $this->mediaTable as m
				 LEFT JOIN $this->usersTable AS u ON m.media_author = u.id
				 WHERE m.media_name LIKE :searchWord
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

	public function countMediaSearch($searchWord) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->mediaTable
				 WHERE media_name LIKE :searchWord"
			);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadMediaByAttached($limit, $offset, $attached, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			if($attached === 'unattached') {
				$havingClause = 'HAVING num_of_attachments = 0';
			} else {
				$havingClause = 'HAVING num_of_attachments > 0';
			}
			$stmt = self::$dbh->prepare(
				"SELECT m.*, m.id AS id, u.username AS media_username, COUNT(mat.media_id) AS num_of_attachments FROM media AS m
				 LEFT JOIN media_attachments AS mat ON mat.media_id = m.id
				 LEFT JOIN $this->usersTable AS u ON m.media_author = u.id
				 GROUP BY m.id
				 $havingClause
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

	public function countMediaByAttached($attached) {
		try {
			if(!self::$dbh) $this->connect();
			if($attached === 'unattached') {
				$havingClause = 'HAVING num_of_attachments = 0';
			} else {
				$havingClause = 'HAVING num_of_attachments > 0';
			}
			$stmt = self::$dbh->prepare(
				"SELECT *, COUNT(mat.media_id) AS num_of_attachments FROM media AS m
				 LEFT JOIN media_attachments AS mat ON mat.media_id = m.id
				 GROUP BY m.id
				 $havingClause"
			);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadMediaByType($limit, $offset, $mimeType, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			switch($mimeType) {
				case 'image':
					$whereClause = "WHERE media_mime_type = 'image/jpeg' OR media_mime_type = 'image/gif' OR media_mime_type = 'image/png'";
					break;
				case 'video':
					$whereClause = "WHERE media_mime_type = 'video/mp4' OR media_mime_type = 'video/mpeg' OR media_mime_type = 'video/x-flv' OR media_mime_type = 'video/x-msvideo' OR media_mime_type = 'video/quicktime' OR media_mime_type = 'video/x-m4v'";
					break;
			}
			$stmt = self::$dbh->prepare(
				"SELECT m.*, m.id AS id, u.username AS media_username FROM $this->mediaTable as m
				 LEFT JOIN $this->usersTable as u ON m.media_author = u.id
				 $whereClause
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

	public function countMediaByType($mimeType) {
		try {
			if(!self::$dbh) $this->connect();
			switch($mimeType) {
				case 'image':
					$whereClause = "WHERE media_mime_type = 'image/jpeg' OR media_mime_type = 'image/gif' OR media_mime_type = 'image/png'";
					break;
				case 'video':
					$whereClause = "WHERE media_mime_type = 'video/mp4' OR media_mime_type = 'video/mpeg' OR media_mime_type = 'video/x-flv' OR media_mime_type = 'video/x-msvideo' OR media_mime_type = 'video/quicktime' OR media_mime_type = 'video/x-m4v'";
					break;
			}
			$stmt = self::$dbh->prepare("SELECT * FROM $this->mediaTable $whereClause");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertMedia($mediaArr) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->mediaTable
				   (media_author, media_name, media_alias, date_posted, media_title, media_mime_type, media_meta, media_url, media_path, media_orig_path, media_orig_width, media_orig_height, media_ext, media_width, media_height)
				 VALUES
				   (:mediaAuthor, :mediaName, :mediaAlias, NOW(), :mediaTitle, :mediaMimeType, :mediaMeta, :mediaUrl, :mediaPath, :mediaOrigPath, :mediaOrigWidth, :mediaOrigHeight, :mediaExt, :mediaWidth, :mediaHeight)");
			$stmt->bindParam(':mediaName', $mediaArr['mediaName'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaAlias', $mediaArr['mediaAlias'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaAuthor', $mediaArr['mediaAuthor'], PDO::PARAM_INT);
			$stmt->bindParam(':mediaTitle', $mediaArr['mediaTitle'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaMimeType', $mediaArr['mediaMimeType'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaMeta', $mediaArr['mediaMeta'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaUrl', $mediaArr['mediaUrl'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaPath', $mediaArr['mediaPath'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaOrigPath', $mediaArr['mediaOrigPath'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaOrigWidth', $mediaArr['mediaOrigWidth'], PDO::PARAM_INT);
			$stmt->bindParam(':mediaOrigHeight', $mediaArr['mediaOrigHeight'], PDO::PARAM_INT);
			$stmt->bindParam(':mediaExt', $mediaArr['mediaExt'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaWidth', $mediaArr['mediaWidth'], PDO::PARAM_INT);
			$stmt->bindParam(':mediaHeight', $mediaArr['mediaHeight'], PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertMediaThumb($mediaId, $templateId, $thumbWidth, $thumbHeight, $thumbLocation, $thumbPath) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->mediaThumbsTable
				   (media_id, template_id, thumbnail_width, thumbnail_height, thumbnail_location, thumbnail_path)
				 VALUES
				   (:mediaId, :templateId, :thumbWidth, :thumbHeight, :thumbLocation, :thumbPath)"
			);
			$stmt->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt->bindParam(':templateId', $templateId, PDO::PARAM_INT);
			$stmt->bindParam(':thumbWidth', $thumbWidth, PDO::PARAM_INT);
			$stmt->bindParam(':thumbHeight', $thumbHeight, PDO::PARAM_INT);
			$stmt->bindParam(':thumbLocation', $thumbLocation, PDO::PARAM_STR);
			$stmt->bindParam(':thumbPath', $thumbPath, PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertMediaAttachment($mediaId, $articleId = FALSE, $pageId = FALSE) {
		try {
			if(!self::$dbh) $this->connect();
			if($articleId !== FALSE) {
				$stmt = self::$dbh->prepare("INSERT INTO $this->mediaAttachTable (media_id, article_id, date_attached) VALUES (:mediaId, :articleId, NOW())");
				$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			} elseif($pageId != FALSE) {
				$stmt = self::$dbh->prepare("INSERT INTO $this->mediaAttachTable (media_id, page_id, date_attached) VALUES (:mediaId, :pageId, NOW())");
				$stmt->bindParam(':pageId', $pageId, PDO::PARAM_INT);
			}
			$stmt->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateMedia($id, $mediaArr = array()) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->mediaTable
				 SET media_title = :mediaTitle, media_caption = :mediaCaption, date_modified = NOW(), media_description = :mediaDescription, media_alt = :mediaAlt
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':mediaTitle', $mediaArr['mediaTitle'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaDescription', $mediaArr['mediaDescription'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaCaption', $mediaArr['mediaCaption'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaAlt', $mediaArr['mediaAlt'], PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateMediaDimensions($id, $width, $height) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->mediaTable
				 SET media_width = :mediaWidth, media_height = :mediaHeight, date_modified = NOW()
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':mediaWidth', $mediaArr['mediaWidth'], PDO::PARAM_STR);
			$stmt->bindParam(':mediaHeight', $mediaArr['mediaHeight'], PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteMedia($mediaId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("DELETE FROM $this->mediaTable WHERE id = :mediaId");
			$stmt->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("DELETE FROM $this->mediaThumbsTable WHERE media_id = :mediaId");
			$stmt2->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt2->execute();
			$stmt3 = self::$dbh->prepare("DELETE FROM $this->mediaAttachTable WHERE media_id = :mediaId");
			$stmt3->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt3->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteMediaThumbs($mediaId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->mediaThumbsTable WHERE media_id = :mediaId");
			$stmt->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteMediaAttachment($attachId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->mediaAttachTable WHERE id = :attachId");
			$stmt->bindParam(':attachId', $attachId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getMedia($mediaId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->mediaTable WHERE id = :mediaId");
			$stmt->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getMediaByAlias($mediaAlias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->mediaTable WHERE media_alias = :mediaAlias");
			$stmt->bindParam(':mediaAlias', $mediaAlias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getMediaThumbByTemplate($mediaId, $templateId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->mediaThumbsTable WHERE media_id = :mediaId AND template_id = :templateId");
			$stmt->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt->bindParam(':templateId', $templateId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getMediaThumbs($mediaId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->mediaThumbsTable WHERE media_id = :mediaId");
			$stmt->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getMediaAttachments($mediaId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->mediaAttachTable WHERE media_id = :mediaId");
			$stmt->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getArticleAttachment($mediaId, $articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->mediaAttachTable WHERE media_id = :mediaId AND article_id = :articleId");
			$stmt->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getPageAttachment($mediaId, $pageId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->mediaAttachTable WHERE media_id = :mediaId AND page_id = :pageId");
			$stmt->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt->bindParam(':pageId', $pageId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setThumbUrl($mediaId, $thumbUrl) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->mediaTable SET media_thumb_url = :thumbUrl WHERE id = :mediaId");
			$stmt->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt->bindParam(':thumbUrl', $thumbUrl, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setEditFlag($mediaId, $switch) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->mediaTable SET media_edit_flag = :switch WHERE id = :mediaId");
			$stmt->bindParam(':mediaId', $mediaId, PDO::PARAM_INT);
			$stmt->bindParam(':switch', $switch, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function setValue($id, $column, $value) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this-> mediaTable SET $column = :value WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':value', $value, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
