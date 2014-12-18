<?php
namespace RevenantBlue\Admin;
use RevenantBlue\Pager;

// Security Check
if(!aclVerify('administer comments') && !aclVerify('view comments')) {
	header('Location: ' . HTTP_ADMIN, true, 302);
	exit;
}

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/comments/comments-hierarchy.php';
require_once DIR_ADMIN . 'controller/comments/comment-validation.php';
require_once DIR_ADMIN . 'model/comments/comments-main.php';
require_once DIR_ADMIN . 'model/articles/articles-main.php';

$comments = new Comments;
$articles = new Articles;
$pager = new Pager;

// Set the limit for the articles overview.
$numOfCommentsToShow = $users->getOptionValueForUser($_SESSION['userId'], 105);
if(!empty($numOfCommentsToShow)) $pager->limit = $numOfCommentsToShow;

// Process GET request when viewing comments for a particular blog entry
if(isset($_GET['title'])) {
	$alias = $_GET['title'];
	$id = $articles->getIdByAlias($alias);
	$commentsParent = $articles->getTitleById($id);
	if(!empty($_POST['submitCommentSearch'])) {
		header('Location: ' . HTTP_ADMIN . 'comments?title=' . $_GET['title'] . '&search=' . $_POST['commentToSearch'] . '&order=com_date&sort=desc', true, 302);
		exit;
	} elseif(isset($_POST['publishedFilter'])) {
		header('Location: ' . HTTP_ADMIN . 'comments/' . $_GET['title'] . '?published=' . $_POST['publishedFilter'] . '&order=com_date&sort=desc', true, 302);
		exit;
	} elseif(isset($_GET['search'])) {
		if(in_array($_GET['order'], $articles->whiteList)) {
			$pager->totalRecords = $articles->countCommentsBySearch($_GET['search'], $id);
			$pager->paginate();
			$commentList = $commentss->loadCommentsBySearch($pager->limit, $pager->offset, $_GET['search'], 'com_date', 'desc', $id);
		} else {
			header('Location: ' . HTTP_ADMIN . 'comments/', true, 302);
			exit;	
		}
	} elseif(isset($_GET['published'])) {
		if(in_array($_GET['order'], $comments->whiteList)) {
			$pager->totalRecords = $comments->countCommentsByPublished($_GET['published'], $id);
			$pager->paginate();
			$commentList = $comments->loadCommentsByPublished($pager->limit, $pager->offset, $_GET['published'], $_GET['order'], $_GET['sort'], $id);
		} else {
			header('Location: ' . HTTP_ADMIN . 'comments/', true, 302);
			exit;	
		}		
	} else {	
		if(isset($_GET['order']) && isset($_GET['sort']) && in_array($_GET['order'], $comments->whiteList)) {
			$pager->totalRecords = $comments->countComments($id);
			$pager->paginate();
			$commentList = $comments->loadComments($pager->limit, $pager->offset, $id, $_GET['order'], $_GET['sort']);	
		} else {
			// Build the comment hierarchy.
			$pager->totalRecords = $comments->countComments($id);
			$pager->paginate();
			$commentList = CommentHierarchy::buildComments($pager->limit, $pager->offset, $id);
			$commentList = CommentHierarchy::displayComments($commentList);
		}
	}
	$pager->displayItemsPerPage();
	$pager->menu = preg_replace('/index.php\?page=([^\/]*)&amp;controller=admin-comments&amp;title=([^\/>"]*)/', 'admin/comments/${2}/p${1}', $pager->menu);
	$pager->limitMenu = str_replace('index.php?page=', BACKEND_NAME . '/comments/' . $alias . '/p', $pager->limitMenu);
	$pager->limitMenu = str_replace('&amp;controller=admin-comments&amp;title=' . $alias, '', $pager->limitMenu);
} else {
	if(!empty($_POST['submitCommentSearch'])) {
		header('Location: ' . HTTP_ADMIN . 'comments?search=' . $_POST['commentToSearch'] . '&order=com_date&sort=desc', true, 302);
		exit;
	} elseif(isset($_POST['publishedFilter'])) {
		header('Location: ' . HTTP_ADMIN . 'comments?published=' . $_POST['publishedFilter'] . '&order=com_date&sort=desc', true, 302);
		exit;
	} elseif(isset($_GET['search'])) {
		if(in_array($_GET['order'], $articles->whiteList)) {
			$pager->totalRecords = $articles->countCommentsBySearch($id, $_GET['search']);
			$pager->paginate();
			$commentList = $commentss->loadCommentsBySearch($pager->limit, $pager->offset, $id, $_GET['search'], 'com_date', 'desc');
		} else {
			header('Location: ' . HTTP_ADMIN . 'comments/', true, 302);
			exit;	
		}
	} elseif(isset($_GET['published'])) {
		if(in_array($_GET['order'], $comments->whiteList)) {
			$pager->totalRecords = $comments->countCommentsByPublished($_GET['published']);
			$pager->paginate();
			$commentList = $comments->loadCommentsByPublished($pager->limit, $pager->offset, $_GET['published'], $_GET['order'], $_GET['sort']);
		} else {
			header('Location: ' . HTTP_ADMIN . 'comments/', true, 302);
			exit;	
		}		
	} else {	
		if(isset($_GET['order']) && isset($_GET['sort']) && in_array($_GET['order'], $comments->whiteList)) {
			$pager->totalRecords = $comments->countAllComments();
			$pager->paginate();
			$commentList = $comments->loadAllComments($pager->limit, $pager->offset, $_GET['order'], $_GET['sort']);	
		} else {
			$pager->totalRecords = $comments->countAllComments();
			$pager->paginate();
			$commentList = $comments->loadAllComments($pager->limit, $pager->offset, 'com_date', 'desc');
		}
	}
	$pager->displayItemsPerPage();
	$pager->menu = str_replace('index.php?page=', BACKEND_NAME . '/comments/p', $pager->menu);
	$pager->menu = str_replace('&amp;controller=admin-comments', '', $pager->menu);
	$pager->limitMenu = str_replace('index.php?page=', BACKEND_NAME . '/comments/p', $pager->limitMenu);
	$pager->limitMenu = str_replace('&amp;controller=admin-comments', '', $pager->limitMenu);
}

if(isset($_GET['id']) && is_numeric($_GET['id'])) {
	$comment = $comments->getCommentById($_GET['id']);	
}

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset($_GET['controller'])) {
		if($_GET['controller'] === 'comments') {
			$optionsForPage = $users->getOptionsByGroup('comments');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'comments');
			// If the user changes the number of records to show, store the change in the personal options table, the only argument is the option's ID.
			setNumToShow(105);
		} elseif($_GET['controller'] === 'comment-profile') {
			$optionsForPage = $users->getOptionsByGroup('comment profile');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'comment profile');
		}
	}
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Process edit comment request.
	if(isset($_POST['commentAction']) && $_POST['commentAction'] === 'edit' && isset($_POST['commentCheck'])) {
		header('Location: ' . HTTP_ADMIN . 'comments/' . urlencode($_POST['commentCheck'][0]) . '/edit', true, 302);
		exit;
	}
	// Process delete comment request.
	if((aclVerify('delete all comments') || aclVerify('delete own comments')) && isset($_POST['commentAction']) && $_POST['commentAction'] === 'delete' && is_array($_POST['commentCheck'])) { 
		// If the user has can only delete comments created by themselves.
		if(aclVerify('delete own comments') && !aclVerify('delete all comments')) {
			foreach($_POST['commentCheck'] as $key => $commentId) {
				$commentToDel = $comments->getCommentById($commentId);
				// Only delete if the comment belongs to the author.
				if($commentToDel['com_author'] === $_SESSION['username']) {
					// Get the descendants for the comment to delete.
					$descendants = $comments->getDescendants($commentId);
					// If the comment has descendants and the descendants do not belong to them they will not be able to delete their comment only remove it.
					if(!empty($descendants)) {
						// Check the author of each descendant to see if it matches the current user.  If not throw an error.
						foreach($descendants as $descendant) {
							if($descendant['com_author'] !== $_SESSION['username']) {
								$descendantsByOtherAuthors = TRUE;	
							}
						}
						if($descendantsByOtherAuthors) {
							$_SESSION['errors'] = "Could not permenantly delete your comment because other have replied to this comment already";
							$comments->updateContent($commentId, 'Comment removed by author.');
							$comments->setState($commentId, 4);
						}
					// If there are no descendants go ahead and delete the comment.
					} else {
						$comments->deleteCommentLeafNode($commentId);	
						$comments->deleteComment($commentId);
					}
				}
			}
		// If the user can delete all comments.
		} elseif(aclVerify('delete all comments')) {
			foreach($_POST['commentCheck'] as $key => $commentId) {
				$descendants = $comments->getDescendants($commentId);
				// If the comment being deleted has descendants delete them and the ancestor comment.
				if(!empty($descendants)) {
					foreach($descendants as $descendant) {
						$comments->deleteCommentTree($descendant['com_id']);	
						$comments->deleteComment($descendant['com_id']);
					}
					$comments->deleteCommentLeafNode($commentId);
					$comments->deleteComment($commentId);
				// Else just delete the comment.	
				} else {
					$comments->deleteCommentLeafNode($commentId);
					$comments->deleteComment($commentId);			
				}
			}
		}
		$_SESSION['success'] = 'Comments deleted succesfully.';
		// Redirect the uesr
		if(isset($_GET['title'])) {
			header('Location: ' . HTTP_ADMIN . "comments/" . $alias, TRUE, 302);
			exit;	
		} else {
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;	
		}
	}
	// Update comment request.
	if((aclVerify('edit all comments') || aclVerify('edit own comments')) && isset($_POST['commentAction']) && ($_POST['commentAction'] === 'save' || $_POST['commentAction'] === 'save-close')) {
		$commentArr = array( 'id'      => $_POST['id'],
		                     'author'  => $_POST['author'],
		                     'content' => $_POST['content'],
							 'state'   => $_POST['state'],
							 'website' => $_POST['website'],
							 'email'   => $_POST['email'], 
							 'date'    => $_POST['datePosted'] );
		// Validate the comment.
		$commentValidation = new CommentValidation($commentArr);
		// If no errors have occured update the comment in the database.
		if(empty($commentValidation->errors)) {
			$updateComment = $comments->updateComment($commentValidation->comment);
			if($updateComment) {
				$_SESSION['success'] = "Comment updated successfully.";
			} else {
				$_SESSION['errors'] = "An error occured while updating the databse.";	
			}
		} else {
			$_SESSION['errors'] = $commentValidation->errors;	
		}
		// Reload page depending ona ction.
		switch($_POST['commentAction']) {
			case 'save':
				header('Location: ' . HTTP_ADMIN . 'comments/' . urlencode($commentValidation->id) . '/edit', true, 302);
				exit;
			case 'save-close':
				header('Location: ' . HTTP_ADMIN . 'comments/' . $articles->getAlias($comment['article_id']), true, 302);  
				exit;
				break;
			default:
				header('Location: ' . $_SERVER['REQUEST_URI'], true, 302);
				exit;
				break;	
		}
	}
	if(isset($_POST['adminRequest'])) {	
		// Decode the incoming JSON request
		$adminReq = json_decode($_POST['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type == 'comment') {
			for($x=0; $x < count($adminReq->ids); $x++) {
				if($adminReq->action == "publish") {
					$comments->setState($adminReq->ids[$x], 1);	
					$adminReq->publish[$x] = 1;
				} elseif($adminReq->action == "unpublish") {
					$comments->setState($adminReq->ids[$x], 0);	
					$adminReq->publish[$x] = 0;					
				}
			}
		}
		echo json_encode($adminReq); 
		exit;
	}
	// If not POST requests match then redirect the uesr to the page he requested.
	$_SESSION['errors'] = 'Invalid POST request.';
	header('Location:' . $_SERVER['REQUEST_URI'], TRUE, 302);
	exit;
}
