<?php
namespace RevenantBlue\Admin;
use RevenantBlue;
use \PDO;

class Tags extends RevenantBlue\Db {

	public  $whiteList = array('id', 'tagId', 'tag_name', 'tag_alias', 'tag_description', 'tag_id', 'article_id', 'popularity');
	private $tagsTable;
	private $articleTagsTable;

	public function __construct() {
		$this->tagsTable = PREFIX . 'tags';
		$this->articleTagsTable = PREFIX . 'article_tags';
	}

	public function loadTags($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT *, t.id AS tagId, COUNT(*) - 1 AS popularity
										 FROM $this->tagsTable AS t
										 LEFT JOIN $this->articleTagsTable AS ats ON t.id = ats.tag_id
										 GROUP BY t.id
										 ORDER BY $orderBy $sort
										 LIMIT :limit
										 OFFSET :offset");
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countTags() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->tagsTable");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadPopularArticleTags($limit) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT DISTINCT *, t.id AS tagId, count(ats.tag_id) - 1 AS popularity FROM $this->tagsTable AS t
										 LEFT JOIN $this->articleTagsTable AS ats ON t.id = ats.tag_id
										 WHERE ats.article_id != ''
										 GROUP BY t.id
										 ORDER BY popularity DESC
										 LIMIT :limit");
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadTagSearch($limit, $offset, $searchWord, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT *, t.id AS tagId, COUNT(*) - 1 AS popularity
										 FROM $this->tagsTable AS t
										 LEFT JOIN $this->articleTagsTable AS ats ON t.id = ats.tag_id
										 WHERE tag_name LIKE :searchWord
										 GROUP BY t.id
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

	public function countTagSearch($searchWord) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->tagsTable
										 WHERE tag_name LIKE :searchWord");
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadTag($tagId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->tagsTable WHERE tag_id = :tagId");
			$stmt->bindParam(':tagId', $tagId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertTag($tagName, $tagAlias, $tagDescription) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("INSERT INTO $this->tagsTable (tag_name, tag_alias, tag_description) VALUES (:tagName, :tagAlias, :tagDescription)");
			$stmt->bindParam(':tagName', $tagName, PDO::PARAM_STR);
			$stmt->bindParam(':tagAlias', $tagAlias, PDO::PARAM_STR);
			$stmt->bindParam(':tagDescription', $tagDescription, PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertArticleTag($tagId, $articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("INSERT INTO $this->articleTagsTable (tag_id, article_id) VALUES (:tagId, :articleId)");
			$stmt->bindParam(':tagId', $tagId, PDO::PARAM_INT);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateTag($tagId, $tagName, $tagAlias, $tagDescription) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->tagsTable
										 SET tag_name = :tagName, tag_alias = :tagAlias, tag_description = :tagDescription
										 WHERE id = :tagId");
			$stmt->bindParam(':tagId', $tagId, PDO::PARAM_INT);
			$stmt->bindParam(':tagName', $tagName, PDO::PARAM_STR);
			$stmt->bindParam(':tagAlias', $tagAlias, PDO::PARAM_STR);
			$stmt->bindParam(':tagDescription', $tagDescription, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteTag($tagId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("DELETE FROM $this->tagsTable WHERE id = :tagId");
			$stmt->bindParam(':tagId', $tagId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("DELETE FROM $this->articleTagsTable WHERE tag_id = :tagId");
			$stmt2->bindParam(':tagId', $tagId, PDO::PARAM_INT);
			$stmt2->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteArticleTag($tagId, $articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->articleTagsTable WHERE tag_id = :tagId AND article_id = :articleId");
			$stmt->bindParam(':tagId', $tagId, PDO::PARAM_INT);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function getTagByName($tagName) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->tagsTable WHERE tag_name = :tagName");
			$stmt->bindParam(':tagName', $tagName, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getTagByAlias($tagAlias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->tagsTable WHERE tag_alias = :tagAlias");
			$stmt->bindParam(':tagAlias', $tagAlias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getTagsForArticle($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT *, t.tag_name FROM $this->articleTagsTable AS ats
										 RIGHT JOIN $this->tagsTable AS t ON ats.tag_id = t.id
										 WHERE article_id = :articleId");
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getTagNamesForArticle($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT t.tag_name FROM $this->articleTagsTable AS ats
										 RIGHT JOIN $this->tagsTable AS t ON ats.tag_id = t.id
										 WHERE article_id = :articleId");
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getTagIdByName($tagName) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT id FROM $this->tagsTable WHERE tag_name = :tagName");
			$stmt->bindParam(':tagName', $tagName, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfArticlesForTag($tagId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->articleTagsTable WHERE tag_id = :tagId");
			$stmt->bindParam(':tagId', $tagId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
