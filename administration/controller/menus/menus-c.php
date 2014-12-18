<?php
namespace RevenantBlue\Admin;
use RevenantBlue\Pager;

require_once DIR_ADMIN . 'controller/menus/menu-validation.php';
require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/menus/menu-hierarchy.php';
require_once DIR_ADMIN . 'model/articles/articles-main.php';
require_once DIR_ADMIN . 'model/menus/menus-main.php';
require_once DIR_SYSTEM . 'library/paginate.php';

$menus = new Menus;
$articles = new Articles;
$pager = new Pager;

// Security Check.
if(!aclVerify('administer menus')) {
	$_SESSION['errors'] = 'You do not have permission to access the menus module.';
	header('Location: ' . HTTP_ADMIN, TRUE, 302);
	exit;
}

// Menu page requirements
if($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset($_GET['controller'])) {
		if($_GET['controller'] === 'menus') {
			if(isset($_GET['search'])) {
				if(in_array($_GET['order'], $menus->whiteList)) {
					$pager->totalRecords = $menus->countMenusBySearch($_GET['search']);
					$pager->paginate();
					$menuList = $menus->loadMenusBySearch($pager->limit, $pager->offset, $_GET['search'], $_GET['order'], $_GET['sort']);
				} else {
					header('Location: ' . HTTP_ADMIN . 'menus/', TRUE, 302);
					exit;
				}
			} elseif(isset($_GET['published'])) {
				if(in_array($_GET['order'], $menus->whiteList)) {
					$pager->totalRecords = $menus->countMenusByPublished($_GET['published']);
					$pager->paginate();
					$menuList = $menus->loadMenusByPublished($pager->limit, $pager->offset, $_GET['published'], $_GET['order'], $_GET['sort']);
				} else {
					header('Location: ' . HTTP_ADMIN . 'menus/', true, 302);
					exit;
				}
			} else {
				if(isset($_GET['order']) && in_array($_GET['order'], $articles->menuWhiteList) && isset($_GET['sort'])) {
					$pager->totalRecords = $menus->countMenus();
					$pager->paginate();
					$menuList = $menus->loadAllMenus($pager->limit, $pager->offset, $_GET['order'], $_GET['sort']);
				} else {
					
					$totalRootNodes = $menus->countMenus();
					$totalMenus = $menus->countAllMenus();
					$rootNodes = $menus->getRootNodeIds();
					$menuList = MenuHierarchy::buildMenus($totalRootNodes, 0);
					$menusJSON = json_encode($menuList);
				}
			}

			$pager->displayItemsPerPage();
			$pager->menu = str_replace('index.php?page=', BACKEND_NAME . '/menus/p', $pager->menu);
			$pager->menu = str_replace('&amp;controller=admin-menus', '', $pager->menu);
			$pager->limitMenu = str_replace('index.php?page=', BACKEND_NAME . '/menus/p', $pager->limitMenu);
			$pager->limitMenu = str_replace('&amp;controller=admin-menus', '', $pager->limitMenu);

			$totalMenus = $menus->countAllMenus();
			$rootNodes = $menus->getRootNodeIds();
			
			// User options for menus overview.
			$optionsForPage = $users->getOptionsByGroup('menus');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'menus');
		} elseif($_GET['controller'] === 'menu-profile') {
			$optionsForPage = $users->getOptionsByGroup('menu profile');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'menu profile');
			$backendUsers = $users->getUsersWithBackendAccess();
			$totalRootNodes = $menus->countMenus();
			// Build parent list for parent selector.
			$parentList = MenuHierarchy::buildMenus($totalRootNodes, 0);
			$parentList = MenuHierarchy::displayMenus($parentList);
			
			// If editing a menu load the requirements.
			if(isset($_GET['id'])) {
				$parentId = (int)$_GET['id'];
				$menu = $menus->getMenuById($parentId);
				$menu['parent_id'] = $menus->getParentId($parentId);
				$descendants = $menus->getDescendantIds($parentId);
			}
		}
	}
}

if($_SERVER['REQUEST_METHOD'] == "POST") {
	// Redirects for editing a menu, searching menus or filtering menus by their published state.
	if(isset($_POST['submitMenuSearch'])) {
		header('Location: ' . HTTP_ADMIN . 'menus?search=' . $_POST['menuToSearch'] . '&order=name&sort=asc', TRUE, 302);
		exit;
	} elseif(isset($_POST['publishedFilter'])) {
		header('Location: ' . HTTP_ADMIN . 'menus?published=' . $_POST['publishedFilter'] . '&order=name&sort=asc', TRUE, 302);
		exit;
	} elseif(isset($_POST['menuAction'])) {
		
		if($_POST['menuAction'] === "edit") {
			if(is_numeric($_POST['menuCheck'][0])) {
				header('Location: ' . HTTP_ADMIN . 'menus/' . $_POST['menuCheck'][0], TRUE, 302);
				exit;
			}
		} elseif(aclVerify('administer menus') && $_POST['menuAction'] === 'delete') {
			// Delete menu request
			foreach($_POST['menuCheck'] as $id) {
				$menuExists = $menus->getMenuById((int)$id);
				if(!empty($menuExists)) {
					$descendants = $menus->getDescendantsWithoutSelf($id);
					// If the menu has no children.
					if(empty($descendants)) {
						$imageToDelete = $menus->getImage($id);
						if(!empty($imageToDelete['image_path']) && file_exists($imageToDelete['image_path'])) {
							$imgDeleted = unlink($imageToDelete['image_path']);
							if(!empty($imageToDelete) && $imgDeleted === FALSE) {
								$_SESSION['errors'][] = "An error occured while deleting the menu: [id=$id]";
							}
						}
						// Delete the leaf node paths from the closure table and the menu itself.
						reorderMenus($id);
						$menus->deleteMenuLeafNode($id);
						$menus->deleteMenu($id);
					} else {
						// If the menu has child menus.
						$descendants = $menus->getDescendants($id);
						foreach($descendants as $descendant) {
							// Delete the menu image if it exists.
							// Delete the menu in the article menus table.
							// Replace the menu for the article with unassigned	if the article has no other menu assigned to it.
							$imageToDelete = $menus->getImage($id);
							if(!empty($imageToDelete['image_path']) && file_exists($imageToDelete['image_path'])) {
								$imgDeleted = unlink($imageToDelete['image_path']);
								if(!empty($imageToDelete) && $imgDeleted === FALSE) {
									$_SESSION['errors'][] = "An error occured while deleting the menu: [id=$id]";
								}
							}
						}
						reorderMenus($id);
						$menus->deleteMenuTree($id);
						$menus->deleteMenu($id);
					}
				}
			}
			$_SESSION['success'] = 'The selected menus were deleted successfully.';
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		} elseif($_POST['menuAction'] === 'save' || $_POST['menuAction'] === 'save-close' || $_POST['menuAction'] === 'save-new') {
			$menu['id'] = isset($_POST['id']) ? $_POST['id'] : '';
			$menu['name'] = isset($_POST['name']) ? $_POST['name'] : '';
			$menu['alias'] = isset($_POST['alias']) ? $_POST['alias'] : '';
			$menu['url'] = isset($_POST['url']) ? $_POST['url'] : '';
			$menu['parent'] = isset($_POST['parent']) ? $_POST['parent'] : '';
			$menu['image'] = isset($_POST['image']) ? $_POST['image'] : '';
			$menu['imageAlt'] = isset($_POST['imageAlt']) ? $_POST['imageAlt'] : '';
			$menu['imagePath'] = isset($_POST['imagePath']) ? $_POST['imagePath'] : '';
			$menu['datePosted'] = isset($_POST['datePosted']) ? $_POST['datePosted'] : '';
			$menu['dateCreated'] = isset($_POST['createdBy']) ? $_POST['dateCreated'] : '';
			$menu['description'] = isset($_POST['description']) ? $_POST['description'] : '';
			$menu['published'] = isset($_POST['published']) ? $_POST['published'] : '';
			$menu['createdBy'] = isset($_POST['createdBy']) ? $_POST['createdBy'] : '';

			// Instantiate the menu validation class and validate the article array.
			$menuValidate = new MenuValidation($menu);
			// If a new article has been submitted.
			if(empty($_POST['id']) && empty($menuValidate->errors)) {
				$newMenu = TRUE;
				$newMenuId = $menus->insertMenu($menuValidate->menu, $menuValidate->parentMenu);
				if(!empty($newMenuId)) $_SESSION['success'] = "Menu created successfully.";
			}
			// If an edited menu is being submitted.
			if(isset($_POST['id']) && empty($menuValidate->errors)) {
				$editMenu = TRUE;
				// Process the menu for the database.
				$updateSuccess = $menus->updateMenu($menuValidate->menu);
				if(!empty($updateSuccess)) $_SESSION['success'] = "Menu updated successfully.";
			}
			if(!empty($menuValidate->errors) || !empty($_SESSION['erors'])) {
				$_SESSION['errors'] = $menuValidate->errors;
				$_SESSION['menu'] = $menuValidate;
				// Redirect back to the previous page to show the errors.
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			} else {
				switch($_POST['menuAction']) {
					case 'save':
						if(isset($newMenu)) {
							header('Location: ' . HTTP_ADMIN . 'menus/' . $newMenuId , TRUE, 302);
							exit;
						} elseif(isset($editMenu)) {
							header('Location: ' . $_SERVER['REQUEST_URI'], true, 302);
							exit;
						}
						break;
					case 'save-close':
						header('Location: ' . HTTP_ADMIN . 'menus/', true, 302);
						exit;
						break;
					case 'save-new':
						header('Location: ' . HTTP_ADMIN . 'menus/new/', true, 302);
						exit;
						break;
					default:
						header('Location: ' . $_SERVER['REQUEST_URI'], true, 302);
						exit;
						break;
				}
			}
		}
	} elseif(isset($_POST['adminRequest'])) {
			// Handle all AJAX requests with JSON.
		$adminReq = json_decode($_POST['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type === 'menu') {
			if($adminReq->action === 'publish' || $adminReq->action === 'unpublish') {
				for($x = 0; $x < count($adminReq->ids); $x++) {
					if(is_numeric($adminReq->ids[$x])) {
						if($adminReq->action == 'publish') {
							$menus->setState($adminReq->ids[$x], 1);
							$adminReq->publish[$x] = $menus->getState($adminReq->ids[$x]);
						} elseif($adminReq->action === 'unpublish') {
							$menus->setState($adminReq->ids[$x], 0);
							$adminReq->publish[$x] = $menus->getState($adminReq->ids[$x]);
						}
					}
				}
			} elseif($adminReq->action === 'delete-image') {
				if(!empty($adminReq->id)) {
					// Get menu info in order to delete the physical image file.
					$menu = $menus->getImage($adminReq->id);
					
					// Delete the physical menu image as well as the database url/alt/path entries.
					if(!empty($menu)) {
						if(file_exists($menu['image_path'])) {
							@unlink($menu['image_path']);
							$menus->setImage($adminReq->id, '', '');
							$adminReq->success = TRUE;
						}
					}
				} elseif(!empty($adminReq->imagePath)) {
					if(file_exists($adminReq->imagePath)) {
						$imageInfo = pathinfo($adminReq->imagePath);
						if($imageInfo['dirname'] === DIR_IMAGE . 'menus') {
							@unlink($adminReq->imagePath);
							$adminReq->success = TRUE;
						}
					}
				}
			} elseif($adminReq->action === 'reorder') {
				$menuOrder = 0;
				// Organize the sections
				foreach($adminReq->menuHierarchy as $menu) {
					$menuOrder += 1;
					$menus->setOrder($menu->id, $menuOrder);
					if(!empty($menu->children)) {
						reorderSubmenus($menu->children, $menu->id);
					}
				}
			}
		}
		echo json_encode($adminReq);
		exit;
	} elseif(isset($_GET['adminRequest'])) {
		// Special ajax request for the menu image upload. Image content sent via POST and the adminReq via GET.
		$adminReq = json_decode($_GET['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type == 'menu') {
			if(aclVerify('administer menus')) {
				if($adminReq->action === 'upload-image') {
					$width = !empty($adminReq->width) ? (int)$adminReq->width : '';
					$height = !empty($adminReq->height) ? (int)$adminReq->height : '';
					// Upload the new image.
					$adminResponse = pluploadImage(DIR_IMAGE . 'menus/', $width, $height, HTTP_IMAGE . 'menus/', 'menu-');
					if(!empty($adminResponse) && is_object($adminResponse)) {
						if(!empty($adminReq->id) && $adminReq->id !== 'undefined') {
							// Get the previous image.
							$prevImage = $menus->getImage($adminReq->id);
							// Delete the previous image.
							if(!empty($prevImage['image_path']) && file_exists($prevImage['image_path'])) {
								@unlink($prevImage['image_path']);
							}
							// Update the menu with the new image.
							$imageUpdated = $menus->setImage($adminReq->id, $adminResponse->fileName, $adminResponse->imagePath);
						} elseif(!empty($adminReq->imagePath)) {
							$imageInfo = pathinfo($adminReq->imagePath);
							if($imageInfo['dirname'] === DIR_IMAGE . 'menus') {
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
	}
	// If no matches on the POST submit redirect the user to the page they were on previously.
	header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
	exit;
}

// Menu cleanup runs after the template has loaded
function menuCleanUp() {
	if(isset($_SESSION['menu'])) {
		unset($_SESSION['menu']);
	}
}

// Reorder menus upon deletion
function reorderMenus($menuId) {
	// Set the menus variable to the global menus variable which contains an instance of the menus model.
	global $menus;
	// Get the root nodes
	$rootNodeIds = $menus->getRootNodeIds();
	$menuToDelete = $menus->getMenuById($menuId);
	// If the menu being deleted is a root node.
	if(in_array($menuId, $rootNodeIds)) {
		// Get all of the root nodes for iteration.
		$rootNodes = $menus->getRootNodes();
		foreach($rootNodes as $rootNode) {
			// If the root node's order is less than the order of the root node being deleted.
			if($rootNode['order_of_item'] > $menuToDelete['order_of_item']) {
				$menus->setOrderOfItem($rootNode['id'], $rootNode['order_of_item'] - 1);
			}
		}

	// If the menu being deleted is a child node.
	} else {
		// Get the parent id.
		$parentId = $menus->getParentId($menuId);
		// Get all of the child menus of the parent which are also the siblings of the menu being deleted.
		$siblings = $menus->getDescendants($parentId);
		foreach($siblings as $sibling) {
			// If the sibling's order is less than the menu being deleted.
			if($sibling['order_of_item'] > $menuToDelete['order_of_item']) {
				$menus->setOrderOfItem($sibling['id'], $sibling['order_of_item'] - 1);
			}
		}
	}
}

function reorderSubmenus($submenus, $parentId) {
	global $menus;
	
	$submenuOrder = 0;
	
	// Organize the menus
	foreach($submenus as $submenu) {
		$submenuOrder += 1;
		$currentParent = $menus->getParentId($submenu->id);
		if($currentParent !== $parentId ) {
			$menus->moveSubTreeToParent($submenu->id, $parentId);
		}
		$menus->setOrder($submenu->id, $submenuOrder);
		
		if(!empty($submenu->children)) {
			reorderSubmenus($submenu->children, $submenu->id);
		}
	}
}
