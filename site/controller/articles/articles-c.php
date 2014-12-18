<?php
namespace RevenantBlue\Site;
use RevenantBlue;
use RevenantBlue\Pager;

require_once(DIR_APPLICATION . 'controller/common/site-c.php');
require_once(DIR_APPLICATION . 'controller/comments/comment-validation.php');
require_once(DIR_APPLICATION . 'controller/comments/comments-hierarchy.php');
require_once(DIR_APPLICATION . 'model/articles/articles-main.php');
require_once(DIR_APPLICATION . 'model/comments/comments-main.php');
require_once(DIR_SYSTEM . 'library/paginate.php');

// Instantiate Classes
$articles = new Articles;
$categories = new Categories;
$comments = new Comments;
$pager = new Pager('news');

$pager->limit = 15;

// Build the pager for the main articles page.
if(!isset($_GET['category']) && !isset($_GET['title'])) {
	if(aclVerify('administer articles')) {
		$pager->totalRecords = $articles->countArticlesByCategory('news');
		$pager->paginate();
		$articleEntries = $articles->loadArticlesByCategory($pager->limit, $pager->offset, 'news', 'date_posted', 'desc');
	} else {
		$pager->totalRecords = $articles->countArticlesByPublishedAndCategory('news', 1);
		$pager->paginate();
		$articleEntries = $articles->loadArticlesByPublishedAndCategory($pager->limit, $pager->offset, 'news', 'date_posted', 'desc', 1);
	}
	
	$pager->menu = str_replace('index.php?page=', 'news/p', $pager->menu);
	$pager->menu = str_replace('&amp;controller=articles', '', $pager->menu);
	foreach($articleEntries as $entryKey => $entry) {
		$categoriesForArticle = $articles->getCategoriesForArticleFull($entry['id']);
		foreach($categoriesForArticle as $key => $categoryForArticle) {
			if($categoryForArticle['cat_published'] != 1) {
				unset($categoriesForArticle[$key]);
			}
		}
		$articleEntries[$entryKey]['author'] = $users->getUsernameById($entry['author']);
	}
}
// Load the popular entries list
$popularEntries = $articles->loadArticlesByPublished(10, 0, 1, 'hits', 'desc');

// Load the recent comments list
$recentComments = $comments->loadCommentsByPublished(5, 0, 1, 'com_date', 'desc');

// Load the categories list
$categoryList = $categories->loadCategoriesByPublished(10, 0, 1, 'cat_name', 'desc');

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	// Process GET request to load an individual article.
	if(isset($_GET['title'])) {
		$article = $articles->loadArticle($_GET['title']);
		if(!empty($article)) {
			$article['author'] = $users->getUsernameById($article['author']);
			$articles->setHit($article['id']);
			$categoriesForArticle = $articles->getCategoriesForArticleFull($article['id']);
			// If the user doesn' t have permission to view non-published articles or if the article variable is empty redirect the user to the articles overview.
			if(!aclVerify('administer articles') && $article['published'] != 1) {
				header('Location: ' . HTTP_SERVER, TRUE, 302);
				exit;
			}
			$numOfCommentsForArticle = $comments->countCommentsByPublished(1, $article['id']);
			$commentList = CommentHierarchy::buildComments(1000, 0, $article['id']);
			$commentList = CommentHierarchy::displayComments($commentList);
		} else {
			// If no article exists with that title redirect back to the news section
			header('Location: ' . HTTP_SERVER . 'news/', TRUE, 302);
			exit;
		}
	}

	// Proces GET requset to load all articles articles for a certain category.
	if(isset($_GET['category'])) {
		$category = $categories->getCategoryByAlias($_GET['category']);
		if(!empty($category)) {
			$pager->totalRecords = $articles->countArticlesByCategory($_GET['category']);
			$pager->paginate();
			$articlesByCategory = $articles->loadArticlesByCategory($pager->limit, $pager->offset, $category['cat_alias'], 'cat_create_date', 'DESC');
			$pager->menu = str_replace('index.php?page=', 'news/category/' . $category['cat_alias'], $pager->menu);
			$pager->menu = str_replace('&amp;controller=articles', '', $pager->menu);
		}
	}
}

// Upon comment submit send the comment to the database and then redirect the user back to the comments section of the articles.
if($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Check to ensure the user has the right to post comments.
	if(isset($_POST['submitComment']) || isset($_POST['submitReply'])) {
		if(aclVerify('post comments')) {
			if(isset($_SESSION['userId'])) {
				$userData = $users->getUserData($_SESSION['userId']);
			}

			$comment['id'] = isset($_POST['id']) ? $_POST['id'] : '';
			$comment['author'] = isset($_SESSION['userId']) ? $userData['username'] : '';
			$comment['author'] = isset($_POST['author']) && empty($comment['author']) ? $_POST['author'] : $comment['author'];
			$comment['author'] = !empty($comment['author']) ? $comment['author'] : 'Anonymous';
			$comment['content'] = isset($_POST['comment']) ? $_POST['comment'] : '';
			$comment['content'] = !empty($_POST['reply']) ? $_POST['reply'] : $comment['content'];
			$comment['website'] = isset($_POST['website']) ? $_POST['website'] : '';
			$comment['email'] = isset($_POST['email']) ? $_POST['email'] : '';
			$comment['date'] = date('Y-m-d H:i:s', time());
			$comment['ip'] = $_SERVER['REMOTE_ADDR'];
			$comment['replyToId'] = isset($_POST['commentId']) ? (int)$_POST['commentId'] : '';

			// If the user has the ability to publish comments set the state to 1 else set it to 2 (pending approval).
			if(empty($comment['state']) && aclVerify('skip comment approval')) {
				$comment['state'] = 1;
			} else {
				$comment['state'] = 2;
			}
			// Validate the comment
			$commentValidate = new CommentValidation($comment);
			
			if(empty($commentValidate->errors) && empty($_SESSION['errors'])) {
				if(!empty($comment['replyToId']) && !empty($_POST['reply'])) {
					$newCommentId = $comments->insertComment($commentValidate->comment, $comment['replyToId']);
				} else {
					$newCommentId = $comments->insertComment($commentValidate->comment);
				}
				if(empty($newCommentId)) {
					$_SESSION['errors'] = "Your comment cannot be posted at this time.";
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
				} else {
					header('Location: ' . $_SERVER['REQUEST_URI'] . '#comment-' . $newCommentId, TRUE, 302);
					exit;
				}

			} else {
				$_SESSION['errors'][] = $commentValidate->errors;
				$_SESSION['comment'] = $commentValidate->comment;
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			}
		} else {
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
	}
	
	// Handle all articles AJAX requests
	if(isset($_POST['appRequest'])) {
		$appReq = json_decode($_POST['appRequest']);
		if($appReq->type === "article") {
			if($appReq->action === "likeComment") {
				if(isset($_SESSION['userId']) && aclVerify('like comments')) {
					$commentLiked = $comments->getCommentLike($appReq->commentId, $_SESSION['userId']);
					if(empty($commentLiked)) {
						$liked = $comments->insertCommentLike($appReq->commentId, $_SESSION['userId'], $_SERVER['REMOTE_ADDR']);
					} else {
						$removeLiked = $comments->deleteCommentLike($appReq->commentId, $_SESSION['userId'], $_SERVER['REMOTE_ADDR']);
					}
				} elseif(aclVerify('like comments')) {
					$commentLiked = $comments->getCommentLikeByIp($appReq->commentId, $_SERVER['REMOTE_ADDR']);
					if(empty($commentLiked)) {
						$liked = $comments->insertCommentLike($appReq->commentId, $_SESSION['tempId'], $_SERVER['REMOTE_ADDR']);
					} else {
						$removeLiked = $comments->deleteCommentLike($appReq->commentId, $_SESSION['tempId'], $_SERVER['REMOTE_ADDR']);
					}
				}
				if(!empty($liked)) $appReq->liked = 1;
				if(!empty($removeLiked)) $appReq->removeLiked = 1;
				$appReq->numOfLikes = $comments->getNumOfLikes($appReq->commentId);
			} elseif($appReq->action === "flagComment") {
				if(isset($_SESSION['userId']) && aclVerify('flag comments')) {
					$commentFlag = $comments->getCommentFlag($appReq->commentId, $_SESSION['userId']);
					if(empty($commentFlag)) {
						$flagged = $comments->insertCommentFlag($appReq->commentId, $_SESSION['userId'], $_SERVER['REMOTE_ADDR']);
					} else {
						$removeFlagged = $comments->deleteCommentFlag($appReq->commentId, $_SESSION['userId'], $_SERVER['REMOTE_ADDR']);
					}
				} elseif(aclVerify('flag comments')) {
					$commentFlag = $comments->getCommentFlag($appReq->commentId, $_SESSION['tempId'], $_SERVER['REMOTE_ADDR']);
					if(empty($commentFlag)) {
						$flagged = $comments->insertCommentFlag($appReq->commentId, $_SESSION['tempId'], $_SERVER['REMOTE_ADDR']);
					} else {
						$removeFlagged = $comments->deleteCommentFlag($appReq->commentId, $_SESSION['tempId'], $_SERVER['REMOTE_ADDR']);
					}
				}
				if(!empty($flagged)) $appReq->flagged = 1;
				if(!empty($removeFlagged)) $appReq->removeFlagged = 1;
			} elseif($appReq->action === 'load-articles' && (aclVerify('view articles') || aclVerify('administer articles'))) {
				if(!empty($appReq->category) && isset($appReq->limit) && isset($appReq->offset)) {
					$appReq->articles = $articles->loadArticlesByPublishedAndCategory(
						(int)$appReq->limit
					  , (int)$appReq->offset
					  , $appReq->category
					  , 'date_posted'
					  , 'DESC'
					);
					
				} elseif(isset($appReq->limit) && isset($appReq->offset)) {
					$appReq->articles = $articles->loadArticlesByPublished(
						(int)$appReq->limit
					  , (int)$appReq->offset
					  , 1
					  , 'date_posted'
					  , 'DESC'
					);
				}
				
				if(!empty($appReq->trimText)) {
					foreach($appReq->articles as $key => $article) {
						$appReq->articles[$key]['content'] = getWords($article['content'], 30, '<p>') . '...';
					}
				}
				
				foreach($appReq->articles as $key => $article) {
					$appReq->articles[$key]['year'] = date('Y', strtotime($appReq->articles[$key]['date_posted']));
					$appReq->articles[$key]['month'] = date('F', strtotime($appReq->articles[$key]['date_posted']));
					$appReq->articles[$key]['day'] = date('d', strtotime($appReq->articles[$key]['date_posted']));
				}
			}
		}
		
		echo json_encode($appReq);
		exit;
	}
}

function commentCleanup() {
	if(isset($_SESSION['comment'])) {
		unset($_SESSION['comment']);
	}
}
