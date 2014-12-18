<?php
namespace RevenantBlue\Admin;
use RevenantBlue;
use \PDO;

class Articles extends RevenantBlue\Db {

	public  $pageData;            //  Contains an array with all of the articles that will be sent to the controller for pagination.
	public  $comments;            //  Contains an array with all of the comments that appear for a certain article.
	public  $numOfComments;       //  Contains a string with the number of comments per entry.
	public  $categories;          //  Contains an array with the names of all the categories.
	public  $popularLimit = 10;   //  Contains the limit value brought in from the database.
	public  $whiteList = array( 
		'id', 'author', 'article_username', 'title', 'alias', 'image_name', 'image', 'date_posted', 'year_posted', 'month_posted', 'day_posted', 'categoryId',
		'cat_name', 'content', 'summary', 'date_edited', 'revision_number', 'hits', 'published', 'featured', 'comment_state',
		'meta_description', 'meta_keywords', 'meta_robots', 'meta_author', 'show_intro_text', 'allow_comments', 'disable_tinymce'
	);
	public  $commentWhiteList = array('com_id', 'com_name', 'com_content', 'com_date', 'com_published', 'com_likes', 'com_dislikes', 'com_flags', 'com_ip');
	public  $categoryWhiteList = array('cat_id', 'cat_name', 'cat_published');
	private $articlesTable;
	private $articleRevTable;
	private $categoriesTable;
	private $articleCategoryTable;
	private $commentsTable;
	private $commentsClosureTable;
	private $likesTable;
	private $commentLikesTable;
	private $commentFlagsTable;
	private $tagsTable;
	private $articleTagsTable;
	private $usersTable;

	public function __construct() {
		$this->articlesTable = PREFIX . 'articles';
		$this->articleRevTable = PREFIX . 'article_revisions';
		$this->categoriesTable = PREFIX . 'categories';
		$this->articleCategoryTable = PREFIX . 'article_categories';
		$this->commentsTable = PREFIX . 'article_comments';
		$this->commentsClosureTable = PREFIX . 'article_comments_closure';
		$this->likesTable = PREFIX . 'article_likes';
		$this->commentLikesTable = PREFIX . 'article_comment_likes';
		$this->commentFlagsTable = PREFIX . 'article_comment_flags';
		$this->tagsTable = PREFIX . 'tags';
		$this->articleTagsTable = PREFIX . 'article_tags';
		$this->usersTable = PREFIX . 'users';
	}
	
	public function loadArticles($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, a.id AS id, u.id as user_id, u.username AS article_username, COUNT(c.article_id) AS num_of_comments
				 FROM $this->articlesTable AS a
				 LEFT JOIN $this->usersTable AS u ON a.author = u.id
				 LEFT JOIN $this->commentsTable AS c ON a.id = c.article_id
				 GROUP BY a.id
				 ORDER BY a.$orderBy $sort
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

	public function countArticles() {
		if(!self::$dbh) $this->connect();
		$stmt = self::$dbh->prepare("SELECT * FROM $this->articlesTable");
		$stmt->execute();
		return $stmt->rowCount();
	}

	public function loadArticleSearch($limit, $offset, $searchWord, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, a.id AS id, u.id as user_id, u.username AS article_username, COUNT(c.article_id) AS num_of_comments
				 FROM $this->articlesTable AS a
				 LEFT JOIN $this->usersTable AS u ON a.author = u.id
				 LEFT JOIN $this->commentsTable AS c ON c.article_id = a.id
				 WHERE title LIKE :searchWord
				 GROUP BY a.id
				 ORDER BY a.$orderBy $sort
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

	public function countArticleSearch($searchWord) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->articlesTable WHERE title LIKE :searchWord");
			$stmt->bindValue(':searchWord', $searchWord, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadArticlesByPublished($limit, $offset, $publishedState, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT *, a.id AS id, u.id as user_id, u.username AS article_username
										 FROM $this->articlesTable AS a
										 LEFT JOIN $this->usersTable as u ON a.author = u.id
										 WHERE published = :published
										 ORDER BY a.$orderBy $sort
										 LIMIT :limit
										 OFFSET :offset");
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countArticlesByPublished($publishedState) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->articlesTable WHERE published = :published");
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadArticlesByFeatured($limit, $offset, $featuredState, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, a.id AS id, u.id AS user_id, u.username AS article_username, COUNT(c.article_id) AS num_of_comments
				 FROM $this->articlesTable AS a
				 LEFT JOIN $this->usersTable AS u ON a.author = u.id
				 LEFT JOIN $this->commentsTable AS c ON c.article_id = a.id
				 WHERE featured = :featured
				 GROUP BY a.id
				 ORDER BY a.$orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':featured', $featuredState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countArticlesByFeatured($featuredState) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->articlesTable WHERE featured = :featured");
			$stmt->bindParam(':featured', $featuredState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadArticlesByCategory($limit, $offset, $categoryAlias, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, a.id AS id, u.id as user_id, u.username AS article_username, COUNT(com.article_id) AS num_of_comments 
				 FROM $this->articlesTable AS a
				 LEFT JOIN $this->articleCategoryTable AS ac ON a.id = ac.article_id
				 RIGHT JOIN $this->categoriesTable AS c ON ac.category_id = c.cat_id
				 LEFT JOIN $this->usersTable AS u ON a.author = u.id
				 LEFT JOIN $this->commentsTable AS com ON com.article_id = a.id
				 WHERE cat_alias = :category AND a.id = ac.article_id
				 GROUP BY a.id
				 ORDER BY a.$orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':category', $categoryAlias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countArticlesByCategory($categoryAlias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->articlesTable AS a
				 LEFT JOIN $this->articleCategoryTable AS ac ON a.id = ac.article_id
				 RIGHT JOIN $this->categoriesTable AS c ON ac.category_id = c.cat_id
				 WHERE cat_alias = :category AND a.id = ac.article_id"
			);
			$stmt->bindParam(':category', $categoryAlias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadArticlesByTag($limit, $offset, $tagAlias, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, a.id AS id, u.id as user_id, u.username AS article_username, COUNT(c.article_id) AS num_of_comments
				 FROM $this->articlesTable AS a
				 LEFT JOIN $this->articleTagsTable AS at ON a.id = at.article_id
				 LEFT JOIN $this->tagsTable AS t ON at.tag_id = t.id
				 LEFT JOIN $this->usersTable AS u ON a.author = u.id
				 LEFT JOIN $this->commentsTable AS c ON c.article_id = a.id
				 WHERE t.tag_alias = :tagAlias
				 GROUP BY a.id
				 ORDER BY a.$orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':tagAlias', $tagAlias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countArticlesByTag($tagAlias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->articlesTable AS a
				 LEFT JOIN $this->articleTagsTable AS at ON a.id = at.article_id
				 LEFT JOIN $this->tagsTable AS t ON t.id = at.tag_id
				 WHERE t.tag_alias = :tagAlias"
			);
			$stmt->bindParam(':tagAlias', $tagAlias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function loadArticlesByPublishedAndCategory($limit, $offset, $categoryAlias, $orderBy, $sort, $publishedState = 1) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("
				SELECT a.*, u.id as user_id, u.username AS article_username, COUNT(com.article_id) AS num_of_comments
				FROM $this->articlesTable AS a
				LEFT JOIN $this->articleCategoryTable AS ac
				ON a.id = ac.article_id
				RIGHT JOIN $this->categoriesTable AS c
				ON ac.category_id = c.cat_id
				LEFT JOIN $this->usersTable AS u ON a.author = u.id
				LEFT JOIN $this->commentsTable AS com ON com.article_id = a.id
				WHERE cat_alias = :category  AND a.id = ac.article_id AND a.published = :published
				GROUP BY a.id
				ORDER BY a.$orderBy $sort
				LIMIT :limit
				OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':category', $categoryAlias, PDO::PARAM_STR);
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countArticlesByPublishedAndCategory($categoryAlias, $publishedState = 1) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->articlesTable AS a
				 LEFT JOIN $this->articleCategoryTable AS ac ON a.id = ac.article_id
				 RIGHT JOIN $this->categoriesTable AS c ON ac.category_id = c.cat_id
				 WHERE cat_alias = :category  AND a.id = ac.article_id AND a.published = :published"
			);
			$stmt->bindParam(':category', $categoryAlias, PDO::PARAM_STR);
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadArticlesByAuthor($limit, $offset, $authorName, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, a.id AS id, u.id as user_id, u.username AS article_username, COUNT(c.article_id) AS num_of_comments
				 FROM $this->articlesTable AS a
				 LEFT JOIN $this->usersTable AS u ON a.author = u.id
				 LEFT JOIN $this->commentsTable AS c ON c.article_id = a.id
				 WHERE author = :author
				 GROUP BY a.id
				 ORDER BY a.$orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':author', $authorName, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countArticlesByAuthor($authorName) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->articlesTable WHERE author = :author");
			$stmt->bindParam(':author', $authorName, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadArticle($alias, $published = '') {
		try {
			if(!self::$dbh) $this->connect();
			if(!empty($published)) {
				$query = "SELECT a.*, a.id AS id, u.id as user_id, u.username AS article_username, COUNT(c.article_id) AS num_of_comments
						  FROM $this->articlesTable AS a
						  LEFT JOIN $this->usersTable AS u ON a.author = u.id
						  LEFT JOIN $this->commentsTable AS c ON a.id = c.article_id
						  WHERE a.alias = :alias AND published = :published
						  GROUP BY a.id";
			} else {
				$query = "SELECT a.*, a.id AS id, u.id as user_id, u.username AS article_username, COUNT(c.article_id) AS num_of_comments
						  FROM $this->articlesTable AS a
						  LEFT JOIN $this->usersTable AS u ON a.author = u.id
						  LEFT JOIN $this->commentsTable AS c ON a.id = c.article_id
						  WHERE alias = :alias
						  GROUP BY a.id";
			}
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			if(!empty($published)) {
				$stmt->bindParam(':published', $published, PDO::PARAM_INT);
			}
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadArticleById($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT a.*, a.id AS id, u.id as user_id, u.username AS article_username, COUNT(c.article_id) AS num_of_comments
				 FROM $this->articlesTable AS a
				 LEFT JOIN $this->commentsTable AS c ON a.id = c.article_id
				 LEFT JOIN $this->usersTable AS u ON u.id = a.author
				 WHERE a.id = :id
				 GROUP BY a.id"
			);
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertArticle($article) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->articlesTable
					(author, title, alias, image, image_alt, date_posted, content, summary, published, featured, meta_description, meta_keywords, meta_author, meta_robots, weight, content_format)
				 VALUES
					(:author, :title, :alias, :image, :imageAlt, :datePosted, :content, :summary, :published, :featured, :metaDescription, :metaKeywords, :metaAuthor, :metaRobots, :weight, :contentFormat)"
			);
			$params = array(
				':author'          => $article['author'],
				':title'           => $article['title'],
				':alias'           => $article['alias'],
				':image'           => $article['image'],
				':imageAlt'        => $article['imageAlt'],
				':datePosted'      => $article['datePosted'],
				':content'         => $article['content'],
				':summary'         => $article['summary'],
				':published'       => $article['published'],
				':featured'        => $article['featured'],
				':metaDescription' => $article['metaDescription'],
				':metaKeywords'    => $article['metaKeywords'],
				':metaAuthor'      => $article['metaAuthor'],
				':metaRobots'      => $article['metaRobots'],
				':weight'          => $article['weight'],
				':contentFormat'   => $article['contentFormat']
			);
			$stmt->execute($params);
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function insertArticleQuick($article) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->articlesTable
					(author, title, alias, date_posted, content, summary, published, featured, content_format)
				 VALUES
					(:author, :title, :alias, :datePosted, :content, :summary, :published, :featured, :contentFormat)"
			);
			$params = array(
				':author'          => $article['author'],
				':title'           => $article['title'],
				':alias'           => $article['alias'],
				':datePosted'      => $article['datePosted'],
				':content'         => $article['content'],
				':summary'         => $article['summary'],
				':published'       => $article['published'],
				':featured'        => $article['featured'],
				':contentFormat'   => $article['contentFormat']
			);
			$stmt->execute($params);
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function insertRevision($article, $type, $current) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->articleRevTable
					(article_id, author, title, content, revision_date, type, current)
				 VALUES
					(:articleId, :author, :title, :content, NOW(), :type, :current)"
			);
			$stmt->bindParam(':articleId', $article['id'], PDO::PARAM_INT);
			$stmt->bindParam(':author', $article['author'], PDO::PARAM_STR);
			$stmt->bindParam(':title', $article['title'], PDO::PARAM_STR);
			$stmt->bindParam(':content', $article['content'], PDO::PARAM_STR);
			$stmt->bindParam(':type', $type, PDO::PARAM_STR);
			$stmt->bindValue(':current', $current, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function insertArticleCategory($articleId, $categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->articleCategoryTable
					 (article_id, category_id)
				 VALUES (:articleId, :categoryId)"
			);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertArticleLike($articleId, $userId, $ip) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->likesTable
					 (article_id, user_id, ip
				 VALUES
					 (:articleId, :userId, :ip)"
			);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("UPDATE $this->articlesTable SET likes = likes +1 WHERE id = :articleId");
			$stmt2->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function updateArticle($article) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->articlesTable 
				 SET  author           = :author
					, title            = :title
					, alias            = :alias
					, date_posted      = :datePosted
					, content          = :content
					, summary          = :summary
					, revision_number  = :revision_number
					, date_edited      = NOW()
					, image            = :image
					, image_alt        = :imageAlt
					, image_path       = :imagePath
					, published        = :published
					, featured         = :featured
					, meta_description = :metaDescription
					, meta_keywords    = :metaKeywords
					, meta_robots      = :metaRobots
					, meta_author      = :metaAuthor
					, weight           = :weight
					, content_format   = :contentFormat
				 WHERE id = :id"
			);
			$params = array( 
				':id'              => $article['id'],
				':author'          => $article['author'],
				':title'           => $article['title'],
				':alias'           => $article['alias'],
				':datePosted'      => $article['datePosted'],
				':content'         => $article['content'],
				':summary'         => $article['summary'],
				':revision_number' => $this->getNumOfRevisions($article['id']) + 1,
				':image'           => $article['image'],
				':imageAlt'        => $article['imageAlt'],
				':imagePath'       => $article['imagePath'],
				':published'       => $article['published'],
				':featured'        => $article['featured'],
				':metaDescription' => $article['metaDescription'],
				':metaKeywords'    => $article['metaKeywords'],
				':metaRobots'      => $article['metaRobots'],
				':metaAuthor'      => $article['metaAuthor'],
				':weight'          => $article['weight'],
				':contentFormat'   => $article['contentFormat']
			);
			$stmt->execute($params);
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateArticleQuick($article) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->articlesTable 
				 SET  author             = :author
					, title              = :title
					, alias              = :alias
					, date_posted        = :datePosted
					, content            = :content
					, summary            = :summary
					, revision_number    = :revisionNumber
					, date_edited        = NOW()
					, published          = :published
					, featured           = :featured
					, meta_description   = :metaDescription
					, meta_keywords      = :metaKeywords
					, meta_robots        = :metaRobots
					, meta_author        = :metaAuthor
					, content_format     = :contentFormat
				  WHERE id = :id"
			);
			$params = array( 
				':id'              => $article['id'],
				':author'          => $article['author'],
				':title'           => $article['title'],
				':alias'           => $article['alias'],
				':datePosted'      => $article['datePosted'],
				':content'         => $article['content'],
				':summary'         => $article['summary'],
				':revisionNumber'  => $this->getNumOfRevisions($article['id']) + 1,
				':published'       => $article['published'],
				':featured'        => $article['featured'],
				':metaDescription' => $article['metaDescription'],
				':metaKeywords'    => $article['metaKeywords'],
				':metaRobots'      => $article['metaRobots'],
				':metaAuthor'      => $article['metaAuthor'],
				':contentFormat'   => $article['contentFormat']
			);
			$stmt->execute($params);
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateRevision($revisionId, $article, $type, $current) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->articleRevTable
				 SET author = :author, title = :title, content = :content, type = :type, current = :current
				 WHERE revision_id = :revisionId AND article_id = :articleId"
			);
			$stmt->bindParam(':revisionId', $revisionId, PDO::PARAM_INT);
			$stmt->bindParam(':articleId', $article['id'], PDO::PARAM_STR);
			$stmt->bindParam(':author', $article['author'], PDO::PARAM_STR);
			$stmt->bindParam(':title', $article['title'], PDO::PARAM_STR);
			$stmt->bindParam(':content', $article['content'], PDO::PARAM_STR);
			$stmt->bindParam(':current', $current, PDO::PARAM_INT);
			$stmt->bindParam(':type' , $type, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteArticle($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("DELETE FROM $this->articlesTable WHERE id = :id");
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			$rowCount = $stmt->rowCount();
			$stmt1 = self::$dbh->prepare("DELETE FROM $this->commentsClosureTable WHERE ancestor = :id");
			$stmt1->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt1->execute();
			$stmt2 = self::$dbh->prepare("DELETE FROM $this->commentsTable WHERE article_id = :id");
			$stmt2->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt2->execute();
			$stmt3 = self::$dbh->prepare("DELETE FROM $this->articleRevTable WHERE article_id = :id");
			$stmt3->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt3->execute();
			$stmt4 = self::$dbh->prepare("DELETE FROM $this->articleCategoryTable WHERE article_id = :id");
			$stmt4->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt4->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteRevision($revisionId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->articleRevTable WHERE revision_id = :id");
			$stmt->bindParam(':id', $revisionId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteCategoryForArticle($articleId, $categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"DELETE FROM $this->articleCategoryTable
				 WHERE article_id = :articleId AND category_id = :categoryId"
			);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteArticleLike($articleId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->likesTable WHERE article_id = :articleId AND user_id = :userId");
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("UPDATE $this->articlesTable SET likes = likes - 1 WHERE id = :articleId");
			$stmt2->bindParam(':articleId', $commentId, PDO::PARAM_INT);
			$stmt2->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}
	
	public function restoreRevision($articleId, $revisionId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt1 = self::$dbh->prepare(
				"UPDATE $this->articleRevTable
				 SET current = 0, type = ''
				 WHERE current = 1 AND article_id = :articleId"
			);
			$stmt1->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt1->execute();
			$stmt2 = self::$dbh->prepare(
				"UPDATE $this->articleRevTable
				 SET current = 1, type = 'Current Revision'
				 WHERE revision_id = :revisionId"
			);
			$stmt2->bindParam(':revisionId', $revisionId, PDO::PARAM_INT);
			$stmt2->execute();
			$stmt3 = self::$dbh->prepare(
				"SELECT @title := title, @content := content
				 FROM $this->articleRevTable
				 WHERE revision_id = :revisionId"
			);
			$stmt3->bindParam(':revisionId', $revisionId, PDO::PARAM_INT);
			$stmt3->execute();
			$stmt4 = self::$dbh->prepare(
				"UPDATE $this->articlesTable
				 SET title = @title, content = @content
				 WHERE id = :articleId"
			);
			$stmt4->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt4->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->commit();
			$this->errorLog($e);
		}
	}

	public function getArticleAuthors() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT a.author, u.username FROM $this->articlesTable AS a
				 LEFT JOIN $this->usersTable AS u ON a.author = u.id
				 GROUP BY u.username"
			);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCategoryForArticle($articleId, $categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->articleCategoryTable WHERE article_id = :articleId AND category_id = :categoryId"
			);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCategoriesForArticle($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT category_id FROM $this->articleCategoryTable WHERE article_id = :articleId");
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCategoriesForArticleFull($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->articleCategoryTable as ac
				 LEFT JOIN $this->categoriesTable AS c ON c.cat_id = ac.category_id
				 WHERE ac.article_id = :articleId"
			);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getArticlesForCategory($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, a.id AS id FROM $this->articlesTable AS a
				 RIGHT JOIN $this->categoriesTable AS c ON a.category_id = c.cat_id
				 LEFT JOIN $this->articleCategoryTable AS ac ON a.id = ac.article_id
				 WHERE ac.category_id = :categoryId"
			);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getIdByAlias($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT id FROM $this->articlesTable WHERE alias=:alias");
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getTitleById($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT title FROM $this->articlesTable WHERE id = :id");
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['title'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAlias($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT alias FROM $this->articlesTable WHERE id = :id");
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['alias'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfComments($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			//The right join creates a one to many relationship between the articles table and the comments table.
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->commentsTable AS com
				 RIGHT JOIN $this->articlesTable AS art ON art.id = com.article_id
				 WHERE art.id = :id AND com.article_id = :id"
			);
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfCommentsByTitle($title) {
		try {
			if(!self::$dbh) $this->connect();
			//The right join creates a one to many relationship between the articles table and the comments table.
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("SELECT @titleId := id FROM $this->articlesTable WHERE title=:title");
			$stmt->bindParam(':title', $title, PDO::PARAM_STR);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare(
				"SELECT * FROM $this->commentsTable AS comments
				 RIGHT JOIN $this->articlesTable AS articles ON articles.id = comments.article_id
				 WHERE articles.id=@titleId AND comments.article_id=@titleId"
			);
			$stmt2->execute();
			self::$dbh->commit();
			$numOfRows = $stmt2->rowCount();
			return $numOfRows;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog();
		}
	}

	public function getCategoryNames() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT cat_id, cat_name FROM $this->categoriesTable ORDER BY cat_id ASC");
			$stmt->execute();
			$categories = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
			return $categories;
		}
		catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCategoryNamesAlpha() {
		try {
			if(!self::$dbh) $this->connect();
			$query = "SELECT * FROM $this->categoriesTable ORDER BY cat_name ASC";
			$stmt = self::$dbh->prepare($query);
			$stmt->execute();
			$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $categories;
		}
		catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCategoryPublished($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT cat_published FROM $this->categoriesTable WHERE cat_id = :id");
			$stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['cat_published'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setCategoryPublished($categoryId, $publishedState) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->categoriesTable SET cat_published = :published WHERE cat_id = :id");
			$stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
			$stmt->bindParam(':published', $publishedState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfArticlesForCategory($categoryId) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "SELECT * FROM $this->articleCategoryTable
					  WHERE category_id = :categoryId";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getPopularCategories($limit = 10) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT category_id, cat_name, COUNT(*)AS num_of_articles
				 FROM $this->articleCategoryTable AS ac
				 INNER JOIN $this->categoriesTable AS c ON ac.category_id = c.cat_id
				 GROUP BY category_id
				 ORDER BY num_of_articles DESC
				 LIMIT :limit"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCategoryByName($categoryName) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->categoriesTable WHERE cat_name = :categoryName");
			$stmt->bindParam(':categoryName', $categoryName, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfArticles() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT COUNT(*) AS numOfArticles FROM $this->articlesTable");
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['numOfArticles'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getArticleByTitle($title) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->articlesTable WHERE title = :title");
			$stmt->bindParam(':title', $title, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getArticleByImageName($imageName) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->articlesTable WHERE image_name = :imageName");
			$stmt->bindParam(':imageName', $imageName, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getPublishedState($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT published FROM $this->articlesTable WHERE id = :id");
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['published'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setPublishedState($articleId, $publishedState) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "UPDATE $this->articlesTable SET published = :publishedState
					  WHERE id = :id";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':publishedState', $publishedState, PDO::PARAM_BOOL);
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setHit($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "UPDATE $this->articlesTable SET hits = hits + 1 WHERE id = :id";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getAuthor($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT author FROM $this->articlesTable WHERE id = :id");
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['author'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getArticleImage($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT image, image_alt, image_path FROM $this->articlesTable WHERE id = :id");
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function setArticleImage($articleId, $fileName, $path) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->articlesTable SET image = :fileName, image_path = :path WHERE id = :id");
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->bindParam(':fileName', $fileName, PDO::PARAM_STR);
			$stmt->bindParam(':path', $path, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getFeatured($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT featured FROM $this->articlesTable WHERE id = :id");
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['featured'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setFeatured($articleId, $featuredState) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->articlesTable SET featured = :featured WHERE id = :id");
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->bindParam(':featured', $featuredState, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCreateDateById($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT date_posted FROM $this->articlesTable WHERE id = :id");
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['date_posted'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setAttribute($articleId, $attribName, $attribValue) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "UPDATE $this->articlesTable SET $attribName = :attribValue WHERE id = :articleId";
			// Query required because the column name is being inserted via the attribName value
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_STR);
			$stmt->bindParam(':attribValue', $attribValue, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfRevisions($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT revision_number FROM $this->articlesTable WHERE id = :id");
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['revision_number'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRevisions($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, u.username AS revision_username FROM $this->articleRevTable AS ar
				 LEFT JOIN $this->usersTable as u ON ar.author = u.id
				 WHERE article_id = :articleId ORDER BY revision_date DESC"
			);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRevision($revisionId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->articleRevTable WHERE revision_id = :revisionId");
			$stmt->bindParam(':revisionId', $revisionId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getCurrentRevision($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->articleRevTable WHERE article_id = :articleId AND current = 1");
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function clearCurrentRevision($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->articleRevTable SET current = 0 WHERE article_id = :articleId");
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setRevisionTypeForCurrent($articleId, $type) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->articleRevTable 
				 SET type = :type
				 WHERE type = 'Current Revision' AND article_id = :articleId"
			);
			$stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
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
			$stmt = self::$dbh->prepare("SELECT author FROM $this->articleRevTable WHERE revision_id = :revisionId");
			$stmt->bindParam(':revisionId', $revisionId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['author'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getArticleTitle($articleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT title FROM $this->articlesTable WHERE id = :id");
			$stmt->bindParam(':id', $articleId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['title'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function articleTitleSearch($searchWord) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->articlesTable WHERE title LIKE :searchWord");
			$stmt->bindValue(':searchWord', $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
