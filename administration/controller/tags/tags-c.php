<?php
namespace RevenantBlue\Admin;
use RevenantBlue\Pager;

require_once DIR_ADMIN . 'controller/common/global-validation.php';
require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'model/tags/tags-main.php';

$tags = new Tags;

$popularTags = $tags->loadPopularArticleTags(40);

$pager = new Pager;
// Set the limit for the articles overview.
$numOfTagsToShow = $users->getOptionValueForUser($_SESSION['userId'], 50);
if(!empty($numOfTagsToShow)) $pager->limit = $numOfTagsToShow;
if(!empty($_POST['submitTagSearch'])) {
	header('Location: ' . HTTP_ADMIN . "tags?search=" . urlencode($_POST['tagToSearch']) . "&order=tag_name&sort=asc", true, 302);
	exit;
} elseif(isset($_GET['search']) && isset($_GET['order']) && isset($_GET['sort'])) {
	if(in_array($_GET['order'], $tags->whiteList)) {
		$pager->totalRecords = $tags->countTagSearch($_GET['search']);
		$pager->paginate();
		$tagsList = $tags->loadTagSearch($pager->limit, $pager->offset, $_GET['search'], $_GET['order'], $_GET['sort']);
	} else {
		header('Location: ' . HTTP_ADMIN . 'tags/', TRUE, 302);
		exit;
	}
} else {
	$pager->totalRecords = $tags->countTags();
	if(isset($_GET['order']) && isset($_GET['sort'])) {
		$pager->paginate();
		$tagsList = $tags->loadTags($pager->limit, $pager->offset, $_GET['order'], $_GET['sort']);
	} else {
		$pager->paginate();
		$tagsList = $tags->loadTags($pager->limit, $pager->offset, 'tag_name', 'asc');
	}
}

$pager->displayItemsPerPage();
$pager->menu = str_replace('index.php?page=', BACKEND_NAME . '/tags/p', $pager->menu);
$pager->menu = str_replace('&amp;controller=admin-tags', '', $pager->menu);
$pager->limitMenu = str_replace('index.php?page=', BACKEND_NAME . '/tags/p', $pager->limitMenu);
$pager->limitMenu = str_replace('&amp;controller=admin-tags', '', $pager->limitMenu);

// Handle GET requests
if($_SERVER['REQUEST_METHOD'] === 'GET') {
	// Tags overview requirements.
	if(isset($_GET['controller']) && $_GET['controller'] == 'tags') {
		$optionsForPage = $users->getOptionsByGroup('tags');
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'tags');
		$popularTags = $tags->loadPopularArticleTags(50);
	}
	// If the user change the number of records to show, store the change in the personal options table.
	setNumToShow(50);

	// Tag profile requirements.
	if(isset($_GET['controller']) && $_GET['controller'] === 'tag-profile') {
		if(isset($_GET['tag'])) {
			$tag = $tags->getTagByAlias($_GET['tag']);
		}
		// Load the user options for the tag profile group - this determines which options to show for the user.
		$optionsForPage = $users->getOptionsByGroup('tag profile');
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'tag profile');
	}
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Edit article request
	if(isset($_POST['tagAction']) && $_POST['tagAction'] === 'edit' && is_array($_POST['tagCheck'])) {
		header('Location: ' . HTTP_ADMIN . 'tags/' . $_POST['tagCheck'][0], TRUE, 302);
		exit;
	}
	// Create/Update tag
	if(isset($_POST['tagAction']) && $_POST['tagAction'] != 'delete') {
		$globalValidate = new GlobalValidation;
		$tag['id'] = isset($_POST['id']) ? $_POST['id'] : '';
		$tag['name'] = isset($_POST['name']) ? $_POST['name'] : '';
		$tag['alias'] = isset($_POST['alias']) ? $_POST['alias'] : '';
		$tag['description'] = isset($_POST['description']) ? $_POST['description'] : '';
		$tag['name'] = $globalValidate->validateTitle($tag['name'], 'tag name');
		$tag['alias'] = $globalValidate->createAlias($tag['alias']);
		$tag['description'] = $globalValidate->validateDescription($tag['description']);
		if(empty($globalValidate->errors)) {
			if(isset($_POST['id'])) {
				$updatedTag = $tags->updateTag($tag['id'], $tag['name'], $tag['alias'], $tag['description']);
				if(empty($updatedTag)) {
					$_SESSION['errors'][] = "An error occurred while updating the tag";
				} else {
					$_SESSION['success'] = 'The \'' . $tag['name'] . '\' was updated successfully.';
				}
			} else {
				$newTagId = $tags->insertTag($tag['name'], $tag['alias'], $tag['description']);
				if(empty($newTagId)) {
					$_SESSION['errors'][] = "An error occurred while inserting the tag into the databse.";
				} else {
					$_SESSION['success'] = 'The \'' . $tag['name'] . '\' tag was created successfully.';
				}
			}
		}
		if(!empty($globalValidate->errors) || !empty($_SESSION['errors'])) {
			$_SESSION['errors'][] = $globalValidate->errors;
			$_SESSION['tag'] = $globalValidate;
			if(empty($_POST['id']) && !empty($newArticleId)) {
				header('Location: ' . HTTP_ADMIN . 'tags/' . urlencode($tag['alias']), TRUE, 302);
				exit;
			} elseif(isset($_POST['id'])) {
				header('Location: ' . HTTP_ADMIN . 'tags/' . urlencode($tag['alias']), TRUE, 302);
				exit;
			}	else {
				header('Location: ' . HTTP_ADMIN . 'tags/new/', TRUE, 302);
				exit;
			}
		} else {
			// Clear out the article session object.
			if(isset($_SESSION['tag'])) unset($_SESSION['tag']);
			// Redirect user.
			switch($_POST['tagAction']) {
				case 'save':
					if(empty($_POST['id']) && !empty($newArticleId)) {
						header('Location: ' . HTTP_ADMIN . 'tags/' . urlencode($tag['alias']), TRUE, 302);
						exit;
					} elseif(isset($_POST['id'])) {
						header('Location: ' . HTTP_ADMIN . 'tags/' . urlencode($tag['alias']), TRUE, 302);
						exit;
					}
				case 'save-close':
					header('Location: ' . HTTP_ADMIN . 'tags/', TRUE, 302);
					exit;
					break;
				case 'save-new':
					header('Location: ' . HTTP_ADMIN . 'tags/new/', TRUE, 302);
					exit;
					break;
				default:
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
			}
		}
	// Delete tags
	} elseif(isset($_POST['tagAction']) && $_POST['tagAction'] === 'delete' && is_array($_POST['tagCheck'])) {
		foreach($_POST['tagCheck'] as $tagToDelete) {
			$success[] = $tags->deleteTag((int)$tagToDelete);
		}
		if(in_array('', $success)) {
			$_SESSION['errors'][] = "An error occured while deleting one or more tags.";
		} else {
			if(count($_POST['tagCheck']) > 1) {
				$_SESSION['success'] = "Tags deleted successfully.";
			} else {
				$_SESSION['success'] = "Tag deleted successfully.";
			}
		}
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	}
	// Ajax requests
}
