<?php
namespace RevenantBlue\Admin;
use RevenantBlue\Pager;
use RevenantBlue\ThumbnailGenerator;

require_once DIR_ADMIN . 'controller/pages/page-validation.php';
require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_SYSTEM . 'library/fine-diff.php';
require_once DIR_ADMIN . 'model/pages/pages-main.php';

$pages = new Pages;

$pager = new Pager;

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset($_GET['controller'])) {
		if($_GET['controller'] === 'pages') {
			// Set the limit for the pages overview.
			$pager->limit = 10;
			$numOfPagesToShow = (int)$users->getOptionValueForUser($_SESSION['userId'], 106);
			if(!empty($numOfPagesToShow)) {
				$pager->limit = $numOfPagesToShow;
			}
			$pageList = $pages->loadPages($pager->limit, $pager->offset, 'date_created', 'DESC');
			// If the user changes the number of records to show, store the change in the personal options table, the only argument is the option's ID.
			setNumToShow(106);
			
			$optionsForPage = $users->getOptionsByGroup('pages');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'pages');
		} elseif($_GET['controller'] === 'page-profile') {
			
			// Placeholder for future tinymce global disable switch.
			$disableTinymce = false;
			
			if(!empty($_GET['id'])) {
				$page = $pages->loadPageById((int)$_GET['id']);
				// Clean up the html output of the body and head with PHP Tidy
				$page['body'] = tidyHTML($page['body'], TRUE);
				$page['head'] = tidyHTML($page['head'], TRUE);
				
				if(!empty($page['disable_tinymce'])) {
					$disableTinymce = TRUE;
				} else {
					$disableTinymce = FALSE;
				}
				
				// Get the revisions for the page
				$pageRevisions = $pages->getRevisions((int)$_GET['id']);
			}
			
			// Get the available page templates
			$pageTemplates = $pages->loadTemplates();

			// Get the content formats available for the user.
			$rolesForUser = $acl->getRolesForUser($_SESSION['userId']);
			foreach($rolesForUser as $roleForUser) {
				$formats = $config->getFormatFiltersByRole($roleForUser['role_id']);
				foreach($formats as $format) {
					$contentFormats[$format['id']] = $format;
				}
			}

			// Get the users with backend access.
			$backendUsers = $users->getUsersWithBackendAccess();
			
			$optionsForPage = $users->getOptionsByGroup('page profile');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'page profile');
		} elseif($_GET['controller'] == 'page-revisions' && isset($_GET['id']) && is_numeric($_GET['id'])) {
			$escapedTags = array('&lt;p&gt;', '&lt;/p&gt;', '&lt;ins&gt;', '&lt;/ins&gt;', '&lt;del&gt;', '&lt;/del&gt;');
			$unescapedTags = array('<p>', '</p>', '<ins>', '</ins>', '<del>', '</del>');
			// If comparing revisions else show the selected revision.
			if(isset($_GET['compare'])) {
				$oldRevision = $pages->getRevision($_GET['compare']);
				$newRevision = $pages->getRevision($_GET['id']);
				if(isset($_GET['granularity']) && is_numeric($_GET['granularity'])) {
					$granularity = $_GET['granularity'];
				} else {
					$granularity = 1;
				}
				$granularityStacks = array(
					FineDiff::$paragraphGranularity,
					FineDiff::$sentenceGranularity,
					FineDiff::$wordGranularity,
					FineDiff::$characterGranularity
				);
				$pageId = (int)$_GET['pageId'];
				$pageRevisions = $pages->getRevisions($pageId);
				$granularity = (int)$_GET['granularity'];
				// Compare the revisions.
				$diff = new FineDiff($oldRevision['body'], $newRevision['body'], $granularityStacks[$granularity]);
				// store opcodes for later use...
				$renderedDiff = str_replace($escapedTags, $unescapedTags, hsc(html_entity_decode(html_entity_decode($diff->renderDiffToHTML()))));
			} else {
				$revisionId = $_GET['id'];
				$pageId = $_GET['pageId'];
				$revision = $pages->getRevision($revisionId);
				$pageRevisions = $pages->getRevisions($pageId);
				$revision['content'] = str_replace($escapedTags, $unescapedTags, hsc($revision['body']));
			}
		} elseif($_GET['controller'] === 'page-templates') {
			$pageTemplates = $pages->loadTemplates();
			foreach($pageTemplates as $template) {
				if(!file_exists(DIR_TEMPLATE . 'page-templates/' . $template['template_alias'] . '.php')) {
					$_SESSION['errors'][] = "The page template '" . $template['template_name'] . "' does not exist at " . DIR_TEMPLATE . 'page-templates/' . $template['template_alias'] . '.php'; 
				}
			}
		} elseif($_GET['controller'] === 'page-template-profile' && isset($_GET['id'])) {
			$pageTemplate = $pages->loadTemplate((int)$_GET['id']);
		}
	}
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
		
	if(!empty($_POST['submitPageSearch'])) {
		header('Location: ' . HTTP_ADMIN . "pages?search=" . urlencode($_POST['pageToSearch']) . "&order=date_posted&sort=desc", TRUE, 302);
		exit;
	} elseif(isset($_POST['pageAction'])) {
		// Edit page request
		if($_POST['pageAction'] === 'edit' && is_array($_POST['pageCheck'])) {
			header('Location: ' . HTTP_ADMIN . 'pages/' . $_POST['pageCheck'][0] . '/edit/', TRUE, 302);
			exit;
		} elseif($_POST['pageAction'] === 'delete') {
			// Delete page request
			if(aclVerify('administer pages')) {
				foreach($_POST['pageCheck'] as $id) {
					$deleteSuccess[$id] = $pages->deletePage($id);
					
					if(!empty($deleteSuccess[$id])) {
						$_SESSION['success'] = "Page(s) deleted successfully.";
					} else {
						$_SESSION['errors'] = "An error occured while deleting a page.";
					}
				}
			}
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		} elseif($_POST['pageAction'] === 'save' || $_POST['pageAction'] === 'save-new' || $_POST['pageAction'] === 'save-close') {
			// Process information for a new page.
			if(aclVerify('edit own page') || aclVerify('edit any page') || aclVerify('administer pages')) {
				$page = array();
				$page['id'] = isset($_POST['id']) ? $_POST['id'] : '';
				$page['author'] = isset($_POST['author']) ? $_POST['author'] : $_SESSION['userId'];
				$page['page'] = isset($_POST['page']) ? $_POST['page'] : 'page';
				$page['title'] = isset($_POST['title']) ? $_POST['title'] : '';
				$page['alias'] = isset($_POST['alias']) ? $_POST['alias'] : '';
				$page['dateCreated'] = isset($_POST['dateCreated']) ? $_POST['dateCreated'] : '';
				$page['body'] = isset($_POST['content']) ? $_POST['content'] : '';
				$page['head'] = isset($_POST['head']) ? $_POST['head'] : '';
				$page['published'] = isset($_POST['published']) ? $_POST['published'] : 0;
				$page['template'] = isset($_POST['template']) ? $_POST['template'] : '';
				$page['contentFormatId'] = isset($_POST['contentFormat']) ? $_POST['contentFormat'] : '';
				$page['metaDescription'] = isset($_POST['metaDescription']) ? $_POST['metaDescription'] : '';
				$page['metaKeywords'] = isset($_POST['metaKeywords']) ? $_POST['metaKeywords'] : '';
				$page['metaRobots'] = isset($_POST['metaRobots']) ? $_POST['metaRobots'] : '';
				$page['metaAuthor'] = isset($_POST['metaAuthor']) ? $_POST['metaAuthor'] : '';
				$page['subdomain'] = isset($_POST['subdomain']) ? $_POST['subdomain'] : '';
				$page['attribs'] = isset($_POST['attribs']) ? $_POST['attribs'] : '';
				
				// Instantiate the page validation class and validate the page array.
				$pageValidate = new PageValidation($page);
				
				// If a new page has been submitted.
				if(empty($_POST['id']) && empty($pageValidate->errors)) {
					$newPageId = $pages->insertPage($pageValidate->page);
	
					if(!empty($newPageId)) {
						$pageValidate->id = $newPageId;
						
						// Set the id of the new page for revision insertion.
						$pageValidate->page['id'] = $newPageId;
						
						$_SESSION['success'] = 'The page was created successfully.';
						
						// Insert the revision
						$revisionId = $pages->insertRevision($pageValidate->page, 'Current Revision', 1);
						if(empty($revisionId)) {
							$_SESSION['errors'][] = "An error occured while creating the revision for this page.";
						}
					} else {
						$_SESSION['errors'][] = "An error occured while inserting the page into the database.";
					}
				}
				// If an edited page is being submitted.
				if(isset($_POST['id']) && empty($pageValidate->errors)) {
					// Process the entry for the database.
					$updateSuccess = $pages->updatePage($pageValidate->page);
					
					if(!empty($updateSuccess)) {
						// Create a new current revision, remove current status from old revision.
						$pages->clearCurrentRevision($pageValidate->id);
						$pages->setRevisionTypeForCurrent($pageValidate->id, '');
						// Set the id for revision insertion.
						$pageValidate->page['id'] = $pageValidate->id;
						// Insert page revision and set it to the current revision for that page.
						$pages->insertRevision($pageValidate->page, 'Current Revision', 1);
						$_SESSION['success'] = "Page updated successfully.";
					}
				}
				
				// Update the attribtes.
				if(!empty($pageValidate->attribs) && is_array($pageValidate->attribs) && !empty($pageValidate->id)) {
					foreach($pageValidate->attribs as $attribName => $attribValue) {
						if(in_array($attribName, $pages->whiteList)) {
							$updateAttribs[$attribName] = $pages->setAttribute($pageValidate->id, $attribName, $attribValue);
						}
					}
				}
				
				if(!empty($pageValidate->errors) || !empty($_SESSION['errors'])) {
					$_SESSION['errors'][] = $pageValidate->errors;
					$_SESSION['page'] = $pageValidate;
					if(empty($_POST['id']) && !empty($newPageId)) {
						header('Location: ' . HTTP_ADMIN . 'pages/' . (int)$newPageId, TRUE, 302);
						exit;
					} elseif(isset($_POST['id'])) {
						header('Location: ' . HTTP_ADMIN . 'pages/' . (int)$pageValidate->id, TRUE, 302);
						exit;
					} else {
						header('Location: ' . HTTP_ADMIN . 'pages/new/', TRUE, 302);
						exit;
					}
				} else {
					// Clear out the page session object.
					if(isset($_SESSION['page'])) unset($_SESSION['page']);
					// Redirect user.
					switch($_POST['pageAction']) {
						case 'save':
							if(empty($_POST['id']) && !empty($newPageId)) {
								header('Location: ' . HTTP_ADMIN . 'pages/' . urlencode($newPageId), TRUE, 302);
								exit;
							} elseif(isset($_POST['id'])) {
								header('Location: ' . HTTP_ADMIN . 'pages/' . urlencode($pageValidate->id), TRUE, 302);
								exit;
							}
						case 'save-close':
							header('Location: ' . HTTP_ADMIN . 'pages/', TRUE, 302);
							exit;
							break;
						case 'save-new':
							header('Location: ' . HTTP_ADMIN . 'pages/new/', TRUE, 302);
							exit;
							break;
						default:
							header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
							exit;
					}
				}
			}
		} elseif($_POST['pageAction'] === 'save-template' || $_POST['pageAction'] === 'save-close-template' || $_POST['pageAction'] === 'save-new-template') {
			
			$template['id'] = isset($_POST['id']) ? $_POST['id'] : '';
			$template['name'] = isset($_POST['name']) ? $_POST['name'] : '';
			$template['alias'] = isset($_POST['alias']) ? $_POST['alias'] : '';
			$template['description'] = isset($_POST['description']) ? $_POST['description'] : ''; 
			
			$contentFormat = getContentFormatForUser($_SESSION['userId']);
			
			$validation = new PageValidation;
			
			$template['name'] = $validation->validateTitle($template['name']);
			$template['alias'] = $validation->validateAlias($template['alias']);
			$template['description'] = $validation->validateContent($template['description'], $contentFormat['format_id'], TRUE);
			
			if(empty($template['id'])) {
				$newTemplateId = $pages->insertTemplate($template);
			} else {
				$updated = $pages->updateTemplate($template);
				if(empty($updated)) {
					$_SESSION['errors'] = 'An error occurred while updating the page template';
				} else {
					$_SESSION['success'] = 'The page template was updated successfully.';
				}
			}
			
			if(!empty($pageValidate->errors) || !empty($_SESSION['errors'])) {
				$_SESSION['errors'] = $pageValidate->errors;
				$_SESSION['pageTemplate'] = $pageValidate;
				if(empty($_POST['id']) && !empty($newPageId)) {
					header('Location: ' . HTTP_ADMIN . 'pages/templates/' . (int)$newPageId, TRUE, 302);
					exit;
				} elseif(isset($_POST['id'])) {
					header('Location: ' . HTTP_ADMIN . 'pages/templates' . (int)$pageValidate->id, TRUE, 302);
					exit;
				} else {
					header('Location: ' . HTTP_ADMIN . 'pages/new/', TRUE, 302);
					exit;
				}
			} else {
				// Clear out the page session object.
				if(isset($_SESSION['page'])) unset($_SESSION['pageTemplate']);
				
				// Redirect user.
				switch($_POST['pageAction']) {
					case 'save-template':
						if(empty($_POST['id']) && !empty($newPageId)) {
							header('Location: ' . HTTP_ADMIN . 'pages/templates/' . (int)$newTemplateId, TRUE, 302);
							exit;
						} elseif(isset($_POST['id'])) {
							header('Location: ' . HTTP_ADMIN . 'pages/templates/' . (int)$template['id'], TRUE, 302);
							exit;
						}
					case 'save-close-template':
						header('Location: ' . HTTP_ADMIN . 'pages/templates', TRUE, 302);
						exit;
						break;
					case 'save-new-template':
						header('Location: ' . HTTP_ADMIN . 'pages/templates/new/', TRUE, 302);
						exit;
						break;
					default:
						header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
						exit;
				}
			}
		} elseif($_POST['pageAction'] === 'delete-template') {
			// Delete page template request
			if(aclVerify('administer pages')) {
				foreach($_POST['templateCheck'] as $id) {
					$deleteSuccess[$id] = $pages->deleteTemplate($id);
					
					if(!empty($deleteSuccess[$id])) {
						$_SESSION['success'] = "Page template(s) deleted successfully.";
					} else {
						$_SESSION['errors'] = "An error occured while deleting a page template.";
					}
				}
				
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			}
		}
	} elseif(isset($_POST['revisionAction'])) {
		if(aclVerify('edit own page') || aclVerify('edit any page') || aclVerify('administer pages')) {
			// Page revision requests
			// Delete revision request.
			if(isset($_POST['revisionCheck']) && $_POST['revisionAction'] === "delete") {
				if(aclVerify('delete own revisions') && !aclVerify('delete all revisions')) {
					foreach($_POST['revisionCheck'] as $revisionToDelete) {
						if($_SESSION['username'] === $pages->getRevisionAuthor($revisionToDelete)) {
							$deleteRevSuccess[] = $pages->deleteRevision($revisionToDelete);
						}
						if(isset($deleteRevSuccess) && !in_array(0, $deleteRevSuccess)) {
							$_SESSION['success'] = "Revison(s) deleted successfully.";
						} elseif(isset($deleteRevSuccess) && in_array(0, $deleteRevSuccess)) {
							$_SESSION['success'] = "Revision(s) deleted successfully.";
							$_SESSION['errors'] = "An error occured while deleting one or more revisions";
						} else {
							$_SESSION['errors'] = "No revisions were deleted";
						}
						if($revisionToDelete == $_GET['id']) {
							$viewedRevDeleted = TRUE;
						}
					}
				} elseif(aclVerify('delete all revisions')) {
					foreach($_POST['revisionCheck'] as $revisionToDelete) {
						$deleteRevSuccess[] = $pages->deleteRevision($revisionToDelete);
						
						if($revisionToDelete == $_GET['id']) {
							$viewedRevDeleted = TRUE;
						}
					}
					$_SESSION['success'] = "Revison(s) deleted successfully.";
				} else {
					$_SESSION['errors'] = "You do not have permission to delete page revisions";
				}
				if(empty($viewedRevDeleted)) {
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
				} else {
					header('Location: ' . HTTP_ADMIN . 'pages/' . (int)$_GET['pageId'], TRUE, 302);
					exit;
				}
			}
			// Compare revision request
			if(isset($_POST['oldRevision']) && isset($_POST['newRevision']) && isset($_POST['granularity']) && $_POST['revisionAction'] === "compare") {
				header('Location: ' . HTTP_ADMIN . 'pages/revision/' . $_POST['newRevision'] . '/' . $_GET['pageId'] . '/' . $_POST['oldRevision'] . "/" . $_POST['granularity'], true, 302);
				exit;
			}
			// Restore revision request
			if(isset($_POST['revisionCheck']) && $_POST['revisionAction'] === "restore") {
				$revisionRestored = $pages->restoreRevision($_GET['pageId'], $_POST['revisionCheck'][0]);
				if(!empty($revisionRestored)) {
					$_SESSION['success'] = 'The page was successfully restored to the selected revision.';
				} else {
					$_SESSION['errors'] = 'An error occurred while restoring the revision.';
				}
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			}
		}
	} elseif(isset($_POST['adminRequest'])) {
		// Handle all page AJAX requests with JSON.
		$adminReq = json_decode($_POST['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type == 'page') {
			if(aclVerify('edit own page') || aclVerify('edit any page') || aclVerify('administer pages')) {
				if($adminReq->action == "autosave") {
					$page['page'] = isset($adminReq->page) ? $adminReq->page : '';
					$page['author'] = isset($adminReq->author) ? $adminReq->author : '';
					$page['title'] = isset($adminReq->title) ? $adminReq->title : '';
					$page['alias'] = isset($adminReq->alias) ? $adminReq->alias : '';
					$page['body'] = isset($adminReq->body) ? $adminReq->body : '';
					$page['head'] = isset($adminReq->head) ? $adminReq->head : '';
					$page['published'] = 2;
					$page['template'] = isset($adminReq->template) ? $adminReq->template : '';
					$page['dateCreated'] = isset($adminReq->dateCreated) ? $adminReq->dateCreated : date('m-d-Y h:i:s', time());
					$page['contentFormatId'] = isset($adminReq->contentFormatId) ? $adminReq->contentFormatId : '';
					$page['metaDescription'] = isset($adminReq->metaDescription) ? $adminReq->metaDescription : '';
					$page['metaKeywords'] = isset($adminReq->metaKeywords) ? $adminReq->metaKeywords : '';
					$page['metaAuthor'] = isset($adminReq->metaAuthor) ? $adminReq->metaAuthor : '';
					$page['metaRobots'] = isset($adminReq->metaRobots) ? $adminReq->metaRobots : '';
					$page['subdomain'] = isset($adminReq->subdomain) ? $adminReq->subdomain : '';
					
					// Add the last saved date.
					$adminReq->lastSave = date('m-d-Y h:i:s A', time());
					// Validate the page.
					$pageValidate = new PageValidation($page);
					// If updating an autosaved draft.
					if(!empty($adminReq->id) && empty($pageValidate->errors)) {
						$updateSuccess = $pages->updatePage($pageValidate->page);
						
					// If creating an autosaved draft.
					} elseif(empty($pageValidate->errors) && empty($pageValidate->id)) {
						// Insert the draft page.
						$newPageId = $pages->insertPageQuick($pageValidate->page);
						$adminReq->id = $newPageId;
						
					} elseif(!empty($pageValidate->errors)) {
						$adminReq->errors = $pageValidate->errors;
					}
				// If revising an alredy published page.
				} elseif($adminReq->action == "revision") {
					// Add the last saved date.
					$adminReq->lastSave = date('m-d-Y h:i:s A', time());
					// Validate the page.
					$pageValidate = new PageValidation;
					$pageValidate->validateId($adminReq->id);
					$pageValidate->validateTitle($adminReq->title);
					$pageValidate->validateContent($adminReq->body, $adminReq->contentFormatId);
					// Assign the validated variables to an array.
					$page = array( 
						'id'         => $pageValidate->id,
						'author'     => $adminReq->author,
						'page'       => $adminReq->page,
						'title'      => $pageValidate->title,
						'body'       => $pageValidate->body,
						'head'       => $pageValidate->head,
						'revisionId' => (int)$adminReq->revisionId,
					);
					// If updating an autosaved draft.
					if(!empty($adminReq->revisionId) && empty($pageValidate->errors)) {
						// Set the revision id.
						$updateSuccess = $pages->updateRevision($adminReq->revisionId, $page, 'Autosave', 0);
					// If creating a new revision
					} elseif(empty($pageValidate->errors) && empty($adminReq->revisionId)) {
						// Insert the page revision.
						$revisionId = $pages->insertRevision($page, 'Autosave', 0);
						// Set the id to the revision id for further auto revision updates.
						$adminReq->revisionId = $revisionId;
					} elseif(!empty($pageValidate->errors)) {
						$adminReq->errors = $pageValidate->errors;
					}
				} elseif($adminReq->action === 'publish' || $adminReq->action === 'unpublish') {
					for($x = 0; $x < count($adminReq->ids); $x++) {
						if(is_numeric($adminReq->ids[$x])) {
							if($adminReq->action === "publish") {
								$pages->setPublishedState($adminReq->ids[$x], 1);
								$adminReq->publish[$x] = $pages->getPublishedState($adminReq->ids[$x]);
							} elseif($adminReq->action === "unpublish") {
								$pages->setPublishedState($adminReq->ids[$x], 0);
								$adminReq->publish[$x] = $pages->getPublishedState($adminReq->ids[$x]);
							}
						}
					}
				} elseif($adminReq->action === 'add-template') {
					$template['name'] = !empty($adminReq->name) ? $adminReq->name : '';
					$template['alias'] = !empty($adminReq->alias) ? $adminReq->alias : '';
					$template['description'] = !empty($adminReq->description) ? $adminReq->description : '';
					
					$validation = new PageTemplateValidation($template);
					
					$adminReq->name = $validation->name;
					$adminReq->alias = $validation->alias;
					$adminReq->description = $validation->description;
					
					$adminReq->id = $pages->insertTemplate($template);
				}
			}
		}
		
		echo json_encode($adminReq);
		exit;
	}
		
	$_SESSION['errors'] = 'Post error occurred. The requested action did not match any response';
	header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
	exit;
}

function pageCleanUp() {
	if(isset($_SESSION['page'])) {
		unset($_SESSION['page']);
	}
	
	if(isset($_SESSION['pageTemplate'])) {
		unset($_SESSION['pageTemplate']);
	}
}
