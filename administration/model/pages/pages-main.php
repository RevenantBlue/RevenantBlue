<?php
namespace RevenantBlue\Admin;
use RevenantBlue;
use \PDO;

class Pages extends RevenantBlue\Db {
	
	private $pagesTable;
	private $templateTable;
	private $revisionTable;
	private $usersTable;
	public $whiteList = array(
		'disable_tinymce'
	);
	
	public function __construct() {
		$this->pagesTable = PREFIX . 'pages';
		$this->templateTable = PREFIX . 'page_templates';
		$this->revisionTable = PREFIX . 'page_revisions';
		$this->usersTable = PREFIX . 'users';
	}
	
	public function loadPage($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT p.*, t.template_name, t.template_alias
				 FROM $this->pagesTable AS p
				 LEFT JOIN $this->templateTable as t ON p.template = t.id
				 WHERE p.alias = :alias"
			);
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function loadPageById($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT p.*, t.template_name, t.template_alias
				 FROM $this->pagesTable as p
				 LEFT JOIN $this->templateTable as t ON p.template = t.id
				 WHERE p.id = :id"
			);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function loadPages($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT p.*, t.template_name, t.template_alias, u.username AS username
				 FROM $this->pagesTable as p
				 LEFT JOIN $this->templateTable as t ON p.template = t.id
				 LEFT JOIN $this->usersTable as u ON u.id = p.author
				 ORDER BY p.$orderBy $sort
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
	
	public function countPages() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT COUNT(*) AS numOfPages FROM $this->pagesTable"
			);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['numOfPages'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function loadTemplates() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->templateTable"
			);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function loadTemplate($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->templateTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function insertPage($page) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->pagesTable
					 (page, alias, author, title, head, body, date_created, published, template, meta_description, meta_keywords, meta_author, meta_robots, subdomain, content_format)
				 VALUES
					 (:page, :alias, :author, :title, :head, :body, :dateCreated, :published, :template, :metaDescription, :metaKeywords, :metaAuthor, :metaRobots, :subdomain, :contentFormat)"
			);
			$stmt->bindParam(':page', $page['page'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $page['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':author', $page['author'], PDO::PARAM_INT);
			$stmt->bindParam(':head', $page['head'], PDO::PARAM_STR);
			$stmt->bindParam(':body', $page['body'], PDO::PARAM_STR);
			$stmt->bindParam(':title', $page['title'], PDO::PARAM_STR);
			$stmt->bindParam(':published', $page['published'], PDO::PARAM_INT);
			$stmt->bindParam(':dateCreated', $page['dateCreated'], PDO::PARAM_STR);
			$stmt->bindParam(':template', $page['template'], PDO::PARAM_INT);
			$stmt->bindParam(':metaDescription', $page['metaDescription'], PDO::PARAM_STR);
			$stmt->bindParam(':metaKeywords', $page['metaKeywords'], PDO::PARAM_STR);
			$stmt->bindParam(':metaAuthor', $page['metaAuthor'], PDO::PARAM_STR);
			$stmt->bindParam(':metaRobots', $page['metaRobots'], PDO::PARAM_STR);
			$stmt->bindParam(':subdomain', $page['subdomain'], PDO::PARAM_INT);
			$stmt->bindParam(':contentFormat', $page['contentFormat'], PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function insertPageQuick($page) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->pagesTable
					 (page, alias, author, title, head, body, date_created, published, template)
				 VALUES
					 (:page, :alias, :author, :title, :head, :body, :dateCreated, :published, :template)"
			);
			$stmt->bindParam(':page', $page['page'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $page['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':author', $page['author'], PDO::PARAM_INT);
			$stmt->bindParam(':head', $page['head'], PDO::PARAM_STR);
			$stmt->bindParam(':body', $page['body'], PDO::PARAM_STR);
			$stmt->bindParam(':title', $page['title'], PDO::PARAM_STR);
			$stmt->bindParam(':published', $page['published'], PDO::PARAM_INT);
			$stmt->bindParam(':dateCreated', $page['dateCreated'], PDO::PARAM_STR);
			$stmt->bindParam(':template', $page['template'], PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function insertTemplate($template) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->templateTable
					(template_name, template_alias, template_description)
				 VALUES
					(:name, :alias, :description)"
			);
			$stmt->bindParam(':name', $template['name'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $template['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':description', $template['description'], PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function insertRevision($page, $type, $current) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->revisionTable
					(page_id, author, page, title, body, head, revision_date, type, current)
				 VALUES
					(:pageId, :author, :page, :title, :body, :head, NOW(), :type, :current)"
			);
			$stmt->bindParam(':pageId', $page['id'], PDO::PARAM_INT);
			$stmt->bindParam(':author', $page['author'], PDO::PARAM_STR);
			$stmt->bindParam(':page', $page['page'], PDO::PARAM_STR);
			$stmt->bindParam(':title', $page['title'], PDO::PARAM_STR);
			$stmt->bindParam(':body', $page['body'], PDO::PARAM_STR);
			$stmt->bindParam(':head', $page['head'], PDO::PARAM_STR);
			$stmt->bindParam(':type', $type, PDO::PARAM_STR);
			$stmt->bindValue(':current', $current, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function updatePage($page) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->pagesTable SET
					page             = :page
				  , alias            = :alias
				  , author           = :author
				  , head             = :head
				  , body             = :body
				  , title            = :title
				  , published        = :published
				  , date_created     = :dateCreated
				  , date_modified    =  NOW()
				  , template         = :template
				  , meta_description = :metaDescription
				  , meta_keywords    = :metaKeywords
				  , meta_author      = :metaAuthor
				  , meta_robots      = :metaRobots
				  , subdomain        = :subdomain
				  , content_format   = :contentFormat
				WHERE id = :id"
			);
			$stmt->bindParam(':id', $page['id'], PDO::PARAM_INT);
			$stmt->bindParam(':page', $page['page'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $page['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':author', $page['author'], PDO::PARAM_INT);
			$stmt->bindParam(':head', $page['head'], PDO::PARAM_STR);
			$stmt->bindParam(':body', $page['body'], PDO::PARAM_STR);
			$stmt->bindParam(':title', $page['title'], PDO::PARAM_STR);
			$stmt->bindParam(':published', $page['published'], PDO::PARAM_INT);
			$stmt->bindParam(':dateCreated', $page['dateCreated'], PDO::PARAM_STR);
			$stmt->bindParam(':template', $page['template'], PDO::PARAM_INT);
			$stmt->bindParam(':metaDescription', $page['metaDescription'], PDO::PARAM_STR);
			$stmt->bindParam(':metaKeywords', $page['metaKeywords'], PDO::PARAM_STR);
			$stmt->bindParam(':metaAuthor', $page['metaAuthor'], PDO::PARAM_STR);
			$stmt->bindParam(':metaRobots', $page['metaRobots'], PDO::PARAM_STR);
			$stmt->bindParam(':subdomain', $page['subdomain'], PDO::PARAM_INT);
			$stmt->bindParam(':contentFormat', $page['contentFormat'], PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function updateTemplate($template) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->templateTable SET
					template_name = :name
				  , template_alias = :alias
				  , template_description = :description
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $template['id'], PDO::PARAM_INT);
			$stmt->bindParam(':name', $template['name'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $template['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':description', $template['description'], PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function updateRevision($revisionId, $page, $type, $current) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->revisionTable SET
					author  = :author
				  , page    = :page
				  , title   = :title
				  , body    = :body
				  , head    = :head
				  , type    = :type
				  , current = :current
				 WHERE revision_id = :revisionId AND page_id = :pageId"
			);
			$stmt->bindParam(':revisionId', $revisionId, PDO::PARAM_INT);
			$stmt->bindParam(':pageId', $page['id'], PDO::PARAM_STR);
			$stmt->bindParam(':author', $page['author'], PDO::PARAM_STR);
			$stmt->bindParam(':page', $page['page'], PDO::PARAM_STR);
			$stmt->bindParam(':title', $page['title'], PDO::PARAM_STR);
			$stmt->bindParam(':body', $page['body'], PDO::PARAM_STR);
			$stmt->bindParam(':head', $page['head'], PDO::PARAM_STR);
			$stmt->bindParam(':current', $current, PDO::PARAM_INT);
			$stmt->bindParam(':type' , $type, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function deletePage($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->pagesTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount(); 
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function deleteTemplate($id) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("DELETE FROM $this->templateTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("UPDATE $this->pagesTable SET template = '' WHERE template = :id");
			$stmt2->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt2->execute();
			return self::$dbh->commit(); 
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}
	
	public function deleteRevision($revisionId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->revisionTable WHERE revision_id = :id");
			$stmt->bindParam(':id', $revisionId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function restoreRevision($pageId, $revisionId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt1 = self::$dbh->prepare(
				"UPDATE $this->revisionTable
				 SET current = 0, type = ''
				 WHERE current = 1 AND page_id = :pageId"
			);
			$stmt1->bindParam(':pageId', $pageId, PDO::PARAM_INT);
			$stmt1->execute();
			$stmt2 = self::$dbh->prepare(
				"UPDATE $this->revisionTable
				 SET current = 1, type = 'Current Revision'
				 WHERE revision_id = :revisionId"
			);
			$stmt2->bindParam(':revisionId', $revisionId, PDO::PARAM_INT);
			$stmt2->execute();
			$stmt3 = self::$dbh->prepare(
				"SELECT @page := page, @body := body
				 FROM $this->revisionTable
				 WHERE revision_id = :revisionId"
			);
			$stmt3->bindParam(':revisionId', $revisionId, PDO::PARAM_INT);
			$stmt3->execute();
			$stmt4 = self::$dbh->prepare(
				"UPDATE $this->pagesTable
				 SET page = @page, body = @body
				 WHERE id = :pageId"
			);
			$stmt4->bindParam(':pageId', $pageId, PDO::PARAM_INT);
			$stmt4->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->commit();
			$this->errorLog($e);
		}
	}
	
	public function getTemplateForPage($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT p.template, t.template_name, t.template_alias, t.template_description
				 FROM $this->pagesTable as p
				 LEFT JOIN $this->templateTable as t ON p.template = t.id
				 WHERE p.alias = :alias"
			);
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getNumOfRevisions($pageId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT revision_number FROM $this->pagesTable WHERE id = :id");
			$stmt->bindParam(':id', $pageId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['revision_number'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRevisions($pageId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, u.username AS revision_username
				 FROM $this->revisionTable AS pr
				 LEFT JOIN $this->usersTable as u ON pr.author = u.id
				 WHERE page_id = :pageId ORDER BY revision_date DESC"
			);
			$stmt->bindParam(':pageId', $pageId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getRevision($revisionId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->revisionTable WHERE revision_id = :revisionId");
			$stmt->bindParam(':revisionId', $revisionId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCurrentRevision($pageId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->revisionTable WHERE page_id = :pageId AND current = 1");
			$stmt->bindParam(':pageId', $pageId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function clearCurrentRevision($pageId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->revisionTable SET current = 0 WHERE page_id = :pageId");
			$stmt->bindParam(':pageId', $pageId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setRevisionTypeForCurrent($pageId, $type) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->revisionTable 
				 SET type = :type
				 WHERE type = 'Current Revision' AND page_id = :pageId"
			);
			$stmt->bindParam(':pageId', $pageId, PDO::PARAM_INT);
			$stmt->bindValue(':type', $type, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRevisionAuthor($revisionId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT author FROM $this->revisionTable WHERE revision_id = :revisionId");
			$stmt->bindParam(':revisionId', $revisionId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['author'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getPublishedState($pageId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT published FROM $this->pagesTable WHERE id = :id");
			$stmt->bindParam(':id', $pageId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['published'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setPublishedState($pageId, $publishedState) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "UPDATE $this->pagesTable SET published = :publishedState
					  WHERE id = :id";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':publishedState', $publishedState, PDO::PARAM_BOOL);
			$stmt->bindParam(':id', $pageId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getIdByAlias($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT id FROM $this->pagesTable WHERE alias = :alias");
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getTemplateIdByAlias($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT id FROM $this->templateTable WHERE alias = :alias");
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function setAttribute($pageId, $attribName, $attribValue) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "UPDATE $this->pagesTable SET $attribName = :attribValue WHERE id = :pageId";
			// Query required because the column name is being inserted via the attribName value
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':pageId', $pageId, PDO::PARAM_STR);
			$stmt->bindParam(':attribValue', $attribValue, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
