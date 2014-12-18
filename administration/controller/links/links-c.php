<?php
namespace RevenantBlue\Admin;
use RevenantBlue\Pager;

require_once DIR_ADMIN . 'controller/links/link-validation.php';
require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'model/links/links-main.php';

$links = new Links;

$pager = new Pager;

// Load the requirements for each controller.
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['controller'])) {
	if($_GET['controller'] === 'links') {
		
		// Set the limit for the links overview.
		$numOfLinksToShow = $users->getOptionValueForUser($_SESSION['userId'], 52);
		if(!empty($numOflinksToShow)) {
			$pager->limit = $numOflinksToShow;
		}
		
		// Load the appropriate link list depending on the filter/sort direction.
		if(isset($_GET['search']) && isset($_GET['order']) && isset($_GET['sort'])) {
			if(in_array($_GET['order'], $links->whiteList)) {
				$pager->totalRecords = $links->countLinkSearch($_GET['search']);
				$pager->paginate();
				$linkList = $links->loadLinkSearch($pager->limit, $pager->offset, $_GET['search'], $_GET['order'], $_GET['sort']);
			} else {
				header('Location: ' . HTTP_ADMIN . 'links/', TRUE, 302);
				exit;
			}
		} elseif(isset($_GET['published'])) {
			if(in_array($_GET['order'], $links->whiteList)) {
				$pager->totalRecords = $links->countlinksByPublished($_GET['published']);
				$pager->paginate();
				$linkList = $links->loadLinksByPublished($pager->limit, $pager->offset, $_GET['published'], $_GET['order'], $_GET['sort']);
			} else {
				header('Location: ' . HTTP_ADMIN . 'links/', TRUE, 302);
				exit;
			}
		} elseif(isset($_GET['category'])) {
			if(in_array($_GET['order'], $links->whiteList)) {
				$pager->totalRecords = $links->countLinksByCategory((int)$_GET['category']);
				$pager->paginate();
				$linkList = $links->loadLinksByCategory($pager->limit, $pager->offset, $_GET['category'], $_GET['order'], $_GET['sort']);
			} else {
				header('Location: ' . HTTP_ADMIN . 'links/', TRUE, 302);
				exit;
			}
		} elseif($_GET['controller'] === 'links') {
			$pager->totalRecords = $links->countLinks();
			if(isset($_GET['order']) && isset($_GET['sort']) && in_array($_GET['order'], $links->whiteList)) {
				$pager->paginate();
				$linkList = $links->loadLinksOverview($pager->limit, $pager->offset, $_GET['order'], $_GET['sort']);
			} else {
				$pager->paginate();
				$linkList = $links->loadLinksOverview($pager->limit, $pager->offset, 'create_date', 'desc');
			}
		}
		
		$pager->displayItemsPerPage();
		$pager->menu = str_replace('index.php?page=', BACKEND_NAME . '/links/p', $pager->menu);
		$pager->menu = str_replace('&amp;controller=admin-links', '', $pager->menu);
		$pager->limitMenu = str_replace('index.php?page=', BACKEND_NAME . '/links/p', $pager->limitMenu);
		$pager->limitMenu = str_replace('&amp;controller=admin-links', '', $pager->limitMenu);
		
		// Load the current categories for the link category filter.
		$allLinkCategories = $links->getCategoryNames();
		
		// Build the categories array for each link.
		foreach($linkList as $key => $link) {
			$linkCategories = $links->getCategoriesForLink($link['id']);
			foreach($linkCategories as $category) {
				$categories[] =  array('id' => $category['id'], 'title' => $category['category_title']);
			}
			if(!empty($categories)) $linkList[$key]['categories'] = $categories;
			unset($categories);
		}
		
		$optionsForPage = $users->getOptionsByGroup('links');
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'links');

		// If the user changes the number of records to show, store the change in the personal options table, the only argument is the option's ID.
		setNumToShow(52);
		
	} elseif($_GET['controller'] === 'link-categories') {
		
		// Set the limit for the link categories overview.
		$numOfLinkCatsToShow = $users->getOptionValueForUser($_SESSION['userId'], 53);
		if(!empty($numOflinksToShow)) {
			$pager->limit = $numOflinksToShow;
		}
		// Load the appropriate link list depending on the filter/sort direction.
		if(!empty($_POST['submitLinkCategorySearch'])) {
			header('Location: ' . HTTP_ADMIN . "links/categories?search=" . urlencode($_POST['linkCategoryToSearch']) . "&order=category_title&sort=asc", true, 302);
			exit;
		} elseif(isset($_GET['search']) && isset($_GET['order']) && isset($_GET['sort'])) {
			if(in_array($_GET['order'], $links->whiteList)) {
				$pager->totalRecords = $links->countCategorySearch($_GET['search']);
				$pager->paginate();
				$linkCategories = $links->loadCategorySearch($pager->limit, $pager->offset, $_GET['search'], $_GET['order'], $_GET['sort']);
			} else {
				header('Location: ' . HTTP_ADMIN . 'links/categories', true, 302);
				exit;
			}
		} elseif($_GET['controller'] === 'link-categories') {
			$pager->totalRecords = $links->countLinks();
			if(isset($_GET['order']) && isset($_GET['sort']) && in_array($_GET['order'], $links->whiteList)) {
				$pager->paginate();
				$linkCategories = $links->loadCategoryOverview($pager->limit, $pager->offset, $_GET['order'], $_GET['sort']);
			} else {
				$pager->paginate();
				$linkCategories = $links->loadCategoryOverview($pager->limit, $pager->offset, 'order_of_item', 'ASC');
			}
		}
		$pager->displayItemsPerPage();
		$pager->menu = str_replace('index.php?page=', BACKEND_NAME . '/links/p', $pager->menu);
		$pager->menu = str_replace('&amp;controller=admin-links', '', $pager->menu);
		$pager->limitMenu = str_replace('index.php?page=', BACKEND_NAME . '/links/p', $pager->limitMenu);
		$pager->limitMenu = str_replace('&amp;controller=admin-links', '', $pager->limitMenu);
		
		$optionsForPage = $users->getOptionsByGroup('link categories');
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'link categories');
		// If the user changes the number of records to show, store the change in the personal options table, the only argument is the option's ID.
		setNumToShow(53);
		
	} elseif($_GET['controller'] === 'link-profile') {
		// If editing a link.
		
		if(isset($_GET['id'])) {
			$link = $links->getLink((int)$_GET['id']);
			$catIdsForLink = $links->getCategoryIdsForLink((int)$_GET['id']);
			$catsForLink = $links->getCategoriesForLink((int)$_GET['id']);
		}
		$numOfCategories = $links->countLinkCategories();
		$linkCategories = $links->loadCategoryOverview($numOfCategories, 0, 'category_title', 'DESC');
		$popularCategories = $links->getPopularCategories();
		$optionsForPage = $users->getOptionsByGroup('link profile');
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'link profile');
	} elseif($_GET['controller'] === 'link-category-profile') {
		if(isset($_GET['id'])) {
			$linkCategory = $links->getLinkCategory((int)$_GET['id']);
		}
		$optionsForPage = $users->getOptionsByGroup('link category profile');
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'link category profile');
	}
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	// Post redirects.
	if(!empty($_POST['submitLinkSearch'])) {
		header('Location: ' . HTTP_ADMIN . "links?search=" . urlencode($_POST['linkToSearch']) . "&order=create_date&sort=desc", true, 302);
		exit;
	} elseif(isset($_POST['publishedFilter'])) {
		header('Location: ' . HTTP_ADMIN . "links?published=" . urlencode($_POST['publishedFilter']) . "&order=create_date&sort=desc", true, 302);
		exit;
	} elseif(isset($_POST['categoryFilter'])) {
		header('Location: ' . HTTP_ADMIN . 'links?category=' . urlencode($_POST['categoryFilter']) . '&order=create_date&sort=desc', true, 302);
		exit;
	}
	// Link POST requests
	if(isset($_POST['linkAction'])) {
		if($_POST['linkAction'] === 'save' || $_POST['linkAction'] === 'save-new' || $_POST['linkAction'] === 'save-close') {
			$link = array();
			$link['id'] = isset($_POST['id']) ? $_POST['id'] : '';
			$link['name'] = isset($_POST['name']) ? $_POST['name'] : '';
			$link['url'] = isset($_POST['url']) ? $_POST['url'] : '';
			$link['description'] = isset($_POST['description']) ? $_POST['description'] : '';
			$link['target'] = isset($_POST['target']) ? $_POST['target'] : '_none';
			$link['categories'] = isset($_POST['linkCategories']) ? array_unique($_POST['linkCategories']) : '';
			$link['published'] = isset($_POST['published']) ? $_POST['published'] : '';
			$link['rel'] = isset($_POST['rel']) ? $_POST['rel'] : '';
			$link['image'] = isset($_POST['image']) ? $_POST['image'] : '';
			$link['imageAlt'] = isset($_POST['imageAlt']) ? $_POST['imageAlt'] : '';
			$link['imagePath'] = isset($_POST['imagePath']) ? $_POST['imagePath'] : '';
			$link['author'] = $_SESSION['userId'];
			$link['weight'] = isset($_POST['weight']) ? $_POST['weight'] : 1;
			
			// Validate link
			$linkValidate = new LinkValidation($link);

			// Insert the new link and its categories if the link validated without any errors.
			if(empty($linkValidate->errors)) {
				if(empty($link['id'])) {
					// Insert the new link
					$newLinkId = $links->insertLink($linkValidate->link);
					if(!empty($linkValidate->categoryIds)) {
						foreach($linkValidate->categoryIds as $linkCategory) {
							// Insert the new categories for the link
							$links->insertCategoryForLink($newLinkId, $linkCategory);
						}
					}
					if(!empty($newLinkId)) {
						$_SESSION['success'] = 'New link created successfully.';
					} else {
						$_SESSION['errors'] = 'A database error occurred while creating the link.';
					}
				} else {
					// Update link
					$linkUpdated = $links->updateLink($linkValidate->link);
					
					// Compare the old and new categories for this article and make the changes accordingly.
					$currentCategories = $links->getCategoryIdsForLink($linkValidate->id);
					
					$categoriesToInsert = array_unique(array_diff($linkValidate->categoryIds, $currentCategories));
					$categoriesToDelete = array_unique(array_diff($currentCategories, $linkValidate->categoryIds));
					
					// Insert and delete categories as needed.
					if(!empty($categoriesToInsert)) {
						foreach($categoriesToInsert as $category) {
							$links->insertCategoryForLink($linkValidate->id, $category);
						}
					}
					
					if(!empty($categoriesToDelete)) {
						foreach($categoriesToDelete as $category) {
							$links->deleteCategoryForLink($linkValidate->id, $category);
						}
					}
					
					if($linkUpdated) {
						$_SESSION['success'] = 'The link was updated successfully.';
					} else {
						$_SESSION['errors'] = 'An error occurred while attmpting to update the link';
					}
				}
			} else {
				$_SESSION['link'] = $linkValidate->link;
				$_SESSION['errors'] = $linkValidate->errors;
				header('Location: ' . HTTP_ADMIN . 'links', TRUE, 302);
				exit;
			}
			
			// Redirect user.
			switch($_POST['linkAction']) {
				case 'save':
					if(empty($_POST['id']) && !empty($newLinkId)) {
						header('Location: ' . HTTP_ADMIN . 'links/' . (int)$newLinkId, TRUE, 302);
						exit;
					} elseif(isset($_POST['id'])) {
						header('Location: ' . HTTP_ADMIN . 'links/' . (int)$_POST['id'], TRUE, 302);
						exit;
					}
				case 'save-close':
					header('Location: ' . HTTP_ADMIN . 'links/', TRUE, 302);
					exit;
					break;
				case 'save-new':
					header('Location: ' . HTTP_ADMIN . 'links/new', TRUE, 302);
					exit;
					break;
				default:
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
					break;
			}
		} elseif($_POST['linkAction'] === 'edit' && is_array($_POST['linkCheck'])) {
			header('Location: ' . HTTP_ADMIN . 'links/' . (int)$_POST['linkCheck'][0], TRUE, 302);
			exit;
		} elseif($_POST['linkAction'] === 'delete' && is_array($_POST['linkCheck'])) {
			// Delete the link and its category relations.
			foreach($_POST['linkCheck'] as $linkId) {
				$deletedLinks[] = $links->deleteLink($linkId);
			}
			if(in_array('', $deletedLinks)) {
				$_SESSION['errors'] = "An error occured while deleting one or more links.";
			} else {
				$_SESSION['success'] = "Links deleted successfully.";
			}
			header('Location: ' . HTTP_ADMIN . 'links', TRUE, 302);
			exit;
		}
	} else if(isset($_POST['linkCatAction'])) {
		// Create/Edit/Update link category
		if($_POST['linkCatAction'] === 'save' || $_POST['linkCatAction'] === 'save-new' || $_POST['linkCatAction'] === 'save-close') {
			$globalValidate = new GlobalValidation;
			$linkCategory['id'] = isset($_POST['id']) ? $_POST['id'] : '';
			$linkCategory['name'] = isset($_POST['name']) ? $_POST['name'] : '';
			$linkCategory['alias'] = isset($_POST['alias']) ? $_POST['alias'] : '';
			$linkCategory['description'] = isset($_POST['description']) ? $_POST['description'] : '';
			
			$linkCatValidation = new LinkCategoryValidation($linkCategory);

			if(empty($linkCatValidation->errors)) {
				if(isset($_POST['id'])) {
					$updatedLinkCat = $links->updateCategory($linkCatValidation->linkCategory);
					if(empty($updatedLinkCat)) {
						$_SESSION['errors'][] = "An error occurred while updating the link category";
					} else {
						$_SESSION['success'] = 'The ' . $linkCategory['name'] . ' was updated successfully.';
					}
				} else {
					$newLinkCatId = $links->insertCategory($linkCatValidation->linkCategory);
					if(empty($newLinkCatId)) {
						$_SESSION['errors'][] = "An error occurred while inserting the link category into the databse.";
					} else {
						$_SESSION['success'] = 'The ' . $linkCategory['name'] . ' link category was created successfully.';
					}
				}
			}
			
			if(!empty($linkCatValidation->errors) || !empty($_SESSION['errors'])) {
				$_SESSION['errors'][] = $linkCatValidation->errors;
				$_SESSION['linkCategory'] = $linkCatValidation;
				
				if(empty($_POST['id']) && !empty($newLinkCatId)) {
					header('Location: ' . HTTP_ADMIN . 'links/categories/' . (int)$newLinkCatId, TRUE, 302);
					exit;
				} elseif(isset($_POST['id'])) {
					header('Location: ' . HTTP_ADMIN . 'links/categories' . (int)$_POST['id'], TRUE, 302);
					exit;
				}	else {
					header('Location: ' . HTTP_ADMIN . 'links/categories/new/', TRUE, 302);
					exit;
				}
			} else {
				// Clear out the link category session object.
				if(isset($_SESSION['linkCategory'])) unset($_SESSION['linkCategory']);
				// Redirect user.
				switch($_POST['linkCatAction']) {
					case 'save':
						if(empty($_POST['id']) && !empty($newLinkCatId)) {
							header('Location: ' . HTTP_ADMIN . 'links/categories/' . (int)$newLinkCatId, TRUE, 302);
							exit;
						} elseif(isset($_POST['id'])) {
							header('Location: ' . HTTP_ADMIN . 'links/categories/' . (int)$_POST['id'], TRUE, 302);
							exit;
						}
					case 'save-close':
						header('Location: ' . HTTP_ADMIN . 'links/categories/', TRUE, 302);
						exit;
						break;
					case 'save-new':
						header('Location: ' . HTTP_ADMIN . 'links/categories/new/', TRUE, 302);
						exit;
						break;
					default:
						header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
						exit;
				}
			}
		} elseif($_POST['linkCatAction'] === 'edit' && is_array($_POST['linkCatCheck'])) {
			// Edit link category
			header('Location: ' . HTTP_ADMIN . 'links/categories/' . (int)$_POST['linkCatCheck'][0], TRUE, 302);
			exit;
		} elseif(isset($_POST['linkCatAction']) && $_POST['linkCatAction'] === 'delete' && !empty($_POST['linkCatCheck']) && is_array($_POST['linkCatCheck'])) {
			// Delete selected link categories
			foreach($_POST['linkCatCheck'] as $linkCatToDel) {
				$success[] = $links->deleteCategory((int)$linkCatToDel);
			}
			if(in_array('', $success)) {
				$_SESSION['errors'][] = "An error occured while deleting one or more link categories.";
			} else {
				if(count($_POST['linkCatCheck'] > 1)) {
					$_SESSION['success'] = "Link categories deleted successfully.";
				} else {
					$_SESSION['succcess'] = "Link category deleted successfully.";
				}
			}
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['adminRequest'])) {
		$adminReq = json_decode($_POST['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type === 'link' && isset($adminReq->action)) {
			if($adminReq->action === 'publish' || $adminReq->action === 'unpublish') {
				for($x = 0; $x < count($adminReq->ids); $x++) {
					if(is_numeric($adminReq->ids[$x])) {
						if($adminReq->action === "publish") {
							$links->setLinkState($adminReq->ids[$x], 1);
							$adminReq->publish[$x] = 1;
						} elseif($adminReq->action === "unpublish") {
							$links->setLinkState($adminReq->ids[$x], 0);
							$adminReq->publish[$x] = 0;
						}
					}
				}
			} elseif($adminReq->action === 'add-link-category') {
				if(!empty($adminReq->name)) {
					$alias = isset($adminReq->alias) ? $adminReq->alias : '';
					$desc = isset($adminReq->desc) ? $adminReq->desc : '';
					
					$linkCategory = array(
						'name'        => $adminReq->name
					  , 'alias'       => $alias
					  , 'description' => $desc
					);
					
					$linkCatValidation = new LinkCategoryValidation($linkCategory);
					
					$adminReq->id = $links->insertCategory($linkCatValidation->linkCategory);
				}
			} elseif($adminReq->action === 'delete-image') {
				if(!empty($adminReq->id)) {
					// Get category info in order to delete the physical image file.
					$link = $links->getImage($adminReq->id);
					
					// Delete the physical category image as well as the database url/alt/path entries.
					if(!empty($link)) {
						if(file_exists($link['link_image_path'])) {
							@unlink($link['link_image_path']);
							$links->setImage($adminReq->id, '', '');
							$adminReq->success = TRUE;
						}
					}
				} elseif(!empty($adminReq->imagePath)) {
					if(file_exists($adminReq->imagePath)) {
						$imageInfo = pathinfo($adminReq->imagePath);
						if($imageInfo['dirname'] === DIR_IMAGE . 'links') {
							@unlink($adminReq->imagePath);
							$adminReq->success = TRUE;
						}
					}
				}
			}
		} elseif($adminReq->type === 'link-category') {
			if($adminReq->action === 'reorder') {
				$adminReq->order = str_replace('link-categories[]=', '', $adminReq->order);
				$adminReq->order = explode('&', $adminReq->order);
				// Update the link categofies order.
				foreach($adminReq->order as $newOrder => $order) {
					$orderPair = explode('.', $order);
					$catId = (int)$orderPair[0];
					$links->setCategoryOrder($catId, (int)$newOrder);
				}
			}
		}
		echo json_encode($adminReq);
		exit;
	} elseif(isset($_GET['adminRequest'])) {
		// Special ajax request for the link image upload. Image content sent via POST and the adminReq via GET.
		$adminReq = json_decode($_GET['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type == 'link') {
			if(aclVerify('administer links')) {
				if($adminReq->action === 'upload-image') {
					$width = !empty($adminReq->width) ? (int)$adminReq->width : '';
					$height = !empty($adminReq->height) ? (int)$adminReq->height : '';
					// Upload the new image.
					$adminResponse = pluploadImage(DIR_IMAGE . 'links/', $width, $height, HTTP_IMAGE . 'links/', 'link-');
					if(!empty($adminResponse) && is_object($adminResponse)) {
						if(!empty($adminReq->id) && $adminReq->id !== 'undefined') {
							// Get the previous image.
							$prevImage = $links->getImage($adminReq->id);
							// Delete the previous image.
							if(!empty($prevImage['link_image_path']) && file_exists($prevImage['link_image_path'])) {
								@unlink($prevImage['link_image_path']);
							}
							// Update the link with the new image.
							$imageUpdated = $links->setImage($adminReq->id, $adminResponse->fileName, $adminResponse->imagePath);
						} elseif(!empty($adminReq->imagePath)) {
							$imageInfo = pathinfo($adminReq->imagePath);
							if($imageInfo['dirname'] === DIR_IMAGE . 'links') {
								@unlink($adminReq->imagePath);
							}
						}
						echo json_encode($adminResponse);
						exit;
					}
				}
			}
		}
		echo json_encode($adminReq);
		exit;
	} else {
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	}
}

function linkCatCleanUp() {
	if(isset($_SESSION['linkCategory'])) unset($_SESSION['linkCategory']);
}
