<?php
namespace RevenantBlue\Admin;
use RevenantBlue\Pager;
use RevenantBlue\ThumbnailGenerator;

// Security Check.
if(!aclVerify('administer categories')) {
	header('Location: ' . HTTP_ADMIN, TRUE, 302);
	exit;
}

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/categories/category-validation.php';
require_once DIR_ADMIN . 'controller/categories/category-hierarchy.php';
require_once DIR_ADMIN . 'model/articles/articles-main.php';
require_once DIR_ADMIN . 'model/categories/categories-main.php';
require_once DIR_SYSTEM . 'library/paginate.php';

$categories = new Categories;
$articles = new Articles;
$pager = new Pager;

// Category page requirements
if($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset($_GET['controller'])) {
		if($_GET['controller'] === 'categories') {
			if(isset($_GET['search'])) {
				if(in_array($_GET['order'], $categories->whiteList)) {
					$pager->totalRecords = $categories->countCategoriesBySearch($_GET['search']);
					$pager->paginate();
					$categoryList = $categories->loadCategoriesBySearch($pager->limit, $pager->offset, $_GET['search'], $_GET['order'], $_GET['sort']);
				} else {
					header('Location: ' . HTTP_ADMIN . 'categories/', TRUE, 302);
					exit;
				}
			} elseif(isset($_GET['published'])) {
				if(in_array($_GET['order'], $categories->whiteList)) {
					$pager->totalRecords = $categories->countCategoriesByPublished($_GET['published']);
					$pager->paginate();
					$categoryList = $categories->loadCategoriesByPublished($pager->limit, $pager->offset, $_GET['published'], $_GET['order'], $_GET['sort']);
				} else {
					header('Location: ' . HTTP_ADMIN . 'categories/', true, 302);
					exit;
				}
			} else {
				if(isset($_GET['order']) && in_array($_GET['order'], $articles->categoryWhiteList) && isset($_GET['sort'])) {
					$pager->totalRecords = $categories->countCategories();
					$pager->paginate();
					$categoryList = $categories->loadAllCategories($pager->limit, $pager->offset, $_GET['order'], $_GET['sort']);
				} else {
					
					$totalRootNodes = $categories->countCategories();
					$totalCategories = $categories->countAllCategories();
					$rootNodes = $categories->getRootNodeIds();
					$categoryList = CategoryHierarchy::buildCategories($totalRootNodes, 0);
					$categoriesJSON = json_encode($categoryList);
					
					//echo $totalRootNodes . "<br />";
					//echo $totalCategories . "<br />";
				}
			}

			$pager->displayItemsPerPage();
			$pager->menu = str_replace('index.php?page=', BACKEND_NAME . '/categories/p', $pager->menu);
			$pager->menu = str_replace('&amp;controller=admin-categories', '', $pager->menu);
			$pager->limitMenu = str_replace('index.php?page=', BACKEND_NAME . '/categories/p', $pager->limitMenu);
			$pager->limitMenu = str_replace('&amp;controller=admin-categories', '', $pager->limitMenu);

			$totalCategories = $categories->countAllCategories();
			$rootNodes = $categories->getRootNodeIds();
			
			// User options for categories overview.
			$optionsForPage = $users->getOptionsByGroup('categories');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'categories');
		} elseif($_GET['controller'] === 'category-profile') {
			$optionsForPage = $users->getOptionsByGroup('category profile');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'category profile');
			$backendUsers = $users->getUsersWithBackendAccess();
			$totalRootNodes = $categories->countCategories();
			// Build parent list for parent selector.
			$parentList = CategoryHierarchy::buildCategories($totalRootNodes, 0);
			$parentList = CategoryHierarchy::displayCategories($parentList);
			
			// If editing a category load the requirements.
			if(isset($_GET['id'])) {
				$parentId = (int)$_GET['id'];
				$category = $categories->getCategoryById($parentId);
				$category['parent_id'] = $categories->getParentId($parentId);
				$descendants = $categories->getDescendantIds($parentId);
			}
		}
	}
}

if($_SERVER['REQUEST_METHOD'] == "POST") {
	// Redirects for editing a category, searching categories or filtering categories by their published state.
	if(isset($_POST['submitCategorySearch'])) {
		header('Location: ' . HTTP_ADMIN . 'categories?search=' . $_POST['categoryToSearch'] . '&order=cat_name&sort=asc', TRUE, 302);
		exit;
	} elseif(isset($_POST['publishedFilter'])) {
		header('Location: ' . HTTP_ADMIN . 'categories?published=' . $_POST['publishedFilter'] . '&order=cat_name&sort=asc', TRUE, 302);
		exit;
	} elseif(isset($_POST['categoryAction'])) {
		
		if($_POST['categoryAction'] === "edit") {
			if(is_numeric($_POST['categoryCheck'][0])) {
				header('Location: ' . HTTP_ADMIN . 'categories/' . $_POST['categoryCheck'][0], TRUE, 302);
				exit;
			}
		} elseif(aclVerify('administer categories') && $_POST['categoryAction'] === 'delete') {
			// Delete category request
			foreach($_POST['categoryCheck'] as $id) {
				$categoryExists = $categories->getCategoryById((int)$id);
				// If attempting to delete the unassigned category.
				if($categoryExists['cat_alias'] === 'unassigned' || $categoryExists['cat_id'] === '1' || $categoryExists['cat_id'] === 1 || $categoryExists['cat_name'] === 'Unassigned') {
					$_SESSION['errors'][] = "Cannot delete the Unassigned category.  This category is a built-in and required for proper CMS functionality.";
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
				}
				if(!empty($categoryExists)) {
					$descendants = $categories->getDescendantsWithoutSelf($id);
					// If the category has no children.
					if(empty($descendants)) {
						$imageToDelete = $categories->getImage($id);
						if(!empty($imageToDelete['cat_image_path']) && file_exists($imageToDelete['cat_image_path'])) {
							$imgDeleted = unlink($imageToDelete['cat_image_path']);
							if(!empty($imageToDelete) && $imgDeleted === FALSE) {
								$_SESSION['errors'][] = "An error occured while deleting the category: [id=$id]";
							}
						}

						$articlesWithCategory = $articles->getArticlesForCategory($id);
						if(!empty($articlesWithCategory)) {
							foreach($articlesWithCategory as $article) {
								$articles->deleteCategoryForArticle($article['article_id'], $id);
								$remainingCategories = $articles->getCategoriesForArticle($article['article_id']);
								// If the article is not assigned to any categories and has not been assigned the unassigned category assign it to the unassigned category.
								if(!in_multiarray(1, $remainingCategories) && count($remainingCategories) < 1) {
									$articles->insertArticleCategory($article['article_id'], 1);
								}
							}
						}
						// Delete the leaf node paths from the closure table and the category itself.
						reorderCategories($id);
						$categories->deleteCategoryLeafNode($id);
						$categories->deleteCategory($id);
					// If the category has child categories.
					} else {
						$descendants = $categories->getDescendants($id);
						foreach($descendants as $descendant) {
							// Delete the category image if it exists.
							// Delete the category in the article categories table.
							// Replace the category for the article with unassigned	if the article has no other category assigned to it.
							$imageToDelete = $categories->getImage($id);
							if(!empty($imageToDelete['cat_image_path']) && file_exists($imageToDelete['cat_image_path'])) {
								$imgDeleted = unlink($imageToDelete['cat_image_path']);
								if(!empty($imageToDelete) && $imgDeleted === FALSE) {
									$_SESSION['errors'][] = "An error occured while deleting the category: [id=$id]";
								}
							}
							$articlesWithCategory = $articles->getArticlesForCategory($descendant['cat_id']);
							if(!empty($articlesWithCategory)) {
								foreach($articlesWithCategory as $article) {
									$articles->deleteCategoryForArticle($article['article_id'], $descendant['cat_id']);
									$remainingCategories = $articles->getCategoriesForArticle($article['article_id']);
									// If the article is not assigned to any categories and has not been assigned the unassigned category assign it to the unassigned category.
									if(!in_multiarray(1, $remainingCategories) && count($remainingCategories) < 1) {
										$articles->insertArticleCategory($article['article_id'], 1);
									}
									// Delete the category closure paths as well as the category itself.
									reorderCategories($id);
									$categories->deleteCategoryTree($descendant['cat_id']);
									$categories->deleteCategory($descendant['cat_id']);
								}
							}
						}
						reorderCategories($id);
						$categories->deleteCategoryTree($id);
						$categories->deleteCategory($id);
					}
				}
			}
			$_SESSION['success'] = 'The selected categories were deleted successfully.';
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		} elseif($_POST['categoryAction'] === 'save' || $_POST['categoryAction'] === 'save-close' || $_POST['categoryAction'] === 'save-new') {
			$category['id'] = isset($_POST['id']) ? $_POST['id'] : '';
			$category['title'] = isset($_POST['title']) ? $_POST['title'] : '';
			$category['alias'] = isset($_POST['alias']) ? $_POST['alias'] : '';
			$category['parent'] = isset($_POST['parent']) ? $_POST['parent'] : '';
			$category['image'] = isset($_POST['image']) ? $_POST['image'] : '';
			$category['imageAlt'] = isset($_POST['imageAlt']) ? $_POST['imageAlt'] : '';
			$category['imagePath'] = isset($_POST['imagePath']) ? $_POST['imagePath'] : '';
			$category['datePosted'] = isset($_POST['datePosted']) ? $_POST['datePosted'] : '';
			$category['createdBy'] = isset($_POST['createdBy']) ? $_POST['createdBy'] : '';
			$category['description'] = isset($_POST['description']) ? $_POST['description'] : '';
			$category['published'] = isset($_POST['published']) ? $_POST['published'] : '';
			$category['metaDescription'] = isset($_POST['metaDescription']) ? $_POST['metaDescription'] : '';
			$category['metaKeywords'] = isset($_POST['metaKeywords']) ? $_POST['metaKeywords'] : '';
			$category['metaRobots'] = isset($_POST['metaRobots']) ? $_POST['metaRobots'] : '';
			$category['metaAuthor'] = isset($_POST['metaAuthor']) ? $_POST['metaAuthor'] : '';

			// Instantiate the category validation class and validate the article array.
			$categoryValidate = new CategoryValidation($category);
			// If a new article has been submitted.
			if(empty($_POST['id']) && empty($categoryValidate->errors)) {
				$newCategory = TRUE;
				$newCategoryId = $categories->insertCategory($categoryValidate->category, $categoryValidate->parentCategory);
				if(!empty($newCategoryId)) $_SESSION['success'] = "Category created successfully.";
			}
			// If an edited category is being submitted.
			if(isset($_POST['id']) && empty($categoryValidate->errors)) {
				$editCategory = TRUE;
				// Process the category for the database.
				$updateSuccess = $categories->updateCategory($categoryValidate->category);
				if(!empty($updateSuccess)) $_SESSION['success'] = "Category updated successfully.";
			}
			if(!empty($categoryValidate->errors) || !empty($_SESSION['erors'])) {
				$_SESSION['errors'] = $categoryValidate->errors;
				$_SESSION['categories'] = $categoryValidate;
				// Redirect back to the previous page to show the errors.
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			} else {
				switch($_POST['categoryAction']) {
					case 'save':
						if(isset($newCategory)) {
							header('Location: ' . HTTP_ADMIN . 'categories/' . $newCategoryId , TRUE, 302);
							exit;
						} elseif(isset($editCategory)) {
							header('Location: ' . $_SERVER['REQUEST_URI'], true, 302);
							exit;
						}
						break;
					case 'save-close':
						header('Location: ' . HTTP_ADMIN . 'categories/', true, 302);
						exit;
						break;
					case 'save-new':
						header('Location: ' . HTTP_ADMIN . 'categories/new/', true, 302);
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
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type === 'category') {
			if($adminReq->action === 'publish' || $adminReq->action === 'unpublish') {
				for($x = 0; $x < count($adminReq->ids); $x++) {
					if(is_numeric($adminReq->ids[$x])) {
						if($adminReq->action == 'publish') {
							$categories->setState($adminReq->ids[$x], 1);
							$adminReq->publish[$x] = $categories->getState($adminReq->ids[$x]);
						} elseif($adminReq->action === 'unpublish') {
							$categories->setState($adminReq->ids[$x], 0);
							$adminReq->publish[$x] = $categories->getState($adminReq->ids[$x]);
						}
					}
				}
			} elseif($adminReq->action === 'delete-image') {
				if(!empty($adminReq->id)) {
					// Get category info in order to delete the physical image file.
					$category = $categories->getImage($adminReq->id);
					
					// Delete the physical category image as well as the database url/alt/path entries.
					if(!empty($category)) {
						if(file_exists($category['cat_image_path'])) {
							@unlink($category['cat_image_path']);
							$categories->setImage($adminReq->id, '', '');
							$adminReq->success = TRUE;
						}
					}
				} elseif(!empty($adminReq->imagePath)) {
					if(file_exists($adminReq->imagePath)) {
						$imageInfo = pathinfo($adminReq->imagePath);
						if($imageInfo['dirname'] === DIR_IMAGE . 'categories') {
							@unlink($adminReq->imagePath);
							$adminReq->success = TRUE;
						}
					}
				}
			} elseif($adminReq->action === 'reorder') {
				$categoryOrder = 0;
				// Organize the sections
				foreach($adminReq->categoryHierarchy as $category) {
					$categoryOrder += 1;
					$categories->setOrder($category->id, $categoryOrder);
					if(!empty($category->children)) {
						reorderSubcategories($category->children, $category->id);
					}
				}
			}
		}
		echo json_encode($adminReq);
		exit;
	} elseif(isset($_GET['adminRequest'])) {
		// Special ajax request for the category image upload. Image content sent via POST and the adminReq via GET.
		$adminReq = json_decode($_GET['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type == 'category') {
			if(aclVerify('administer categories')) {
				if($adminReq->action === 'upload-image') {
					$width = !empty($adminReq->width) ? (int)$adminReq->width : '';
					$height = !empty($adminReq->height) ? (int)$adminReq->height : '';
					// Upload the new image.
					$adminResponse = pluploadImage(DIR_IMAGE . 'categories/', $width, $height, HTTP_IMAGE . 'categories/', 'category-');
					if(!empty($adminResponse) && is_object($adminResponse)) {
						if(!empty($adminReq->id) && $adminReq->id !== 'undefined') {
							// Get the previous image.
							$prevImage = $categories->getImage($adminReq->id);
							// Delete the previous image.
							if(!empty($prevImage['image_path']) && file_exists($prevImage['image_path'])) {
								@unlink($prevImage['image_path']);
							}
							// Update the category with the new image.
							$imageUpdated = $categories->setImage($adminReq->id, $adminResponse->fileName, $adminResponse->imagePath);
						} elseif(empty($adminReq->id)) {
							if(!empty($adminReq->imagePath)) {
								$imageInfo = pathinfo($adminReq->imagePath);
								if($imageInfo['dirname'] === DIR_IMAGE . 'categories') {
									@unlink($adminReq->imagePath);
								}
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

// Category cleanup runs after the template has loaded
function categoryCleanUp() {
	if(isset($_SESSION['category'])) {
		unset($_SESSION['category']);
	}
}

// Reorder categories upon deletion
function reorderCategories($categoryId) {
	// Set the categories variable to the global categories variable which contains an instance of the categories model.
	global $categories;
	// Get the root nodes
	$rootNodeIds = $categories->getRootNodeIds();
	$categoryToDelete = $categories->getCategoryById($categoryId);
	// If the category being deleted is a root node.
	if(in_array($categoryId, $rootNodeIds)) {
		// Get all of the root nodes for iteration.
		$rootNodes = $categories->getRootNodes();
		foreach($rootNodes as $rootNode) {
			// If the root node's order is less than the order of the root node being deleted.
			if($rootNode['cat_order_of_item'] > $categoryToDelete['cat_order_of_item']) {
				$categories->setOrderOfItem($rootNode['cat_id'], $rootNode['cat_order_of_item'] - 1);
			}
		}

	// If the category being deleted is a child node.
	} else {
		// Get the parent id.
		$parentId = $categories->getParentId($categoryId);
		// Get all of the child categories of the parent which are also the siblings of the category being deleted.
		$siblings = $categories->getDescendants($parentId);
		foreach($siblings as $sibling) {
			// If the sibling's order is less than the category being deleted.
			if($sibling['cat_order_of_item'] > $categoryToDelete['cat_order_of_item']) {
				$categories->setOrderOfItem($sibling['cat_id'], $sibling['cat_order_of_item'] - 1);
			}
		}
	}
}

function reorderSubcategories($subcategories, $parentId) {
	global $categories;
	
	$subcategoryOrder = 0;
	
	// Organize the categories
	foreach($subcategories as $subcategory) {
		$subcategoryOrder += 1;
		$currentParent = $categories->getParentId($subcategory->id);
		if($currentParent !== $parentId ) {
			$categories->moveSubTreeToParent($subcategory->id, $parentId);
		}
		$categories->setOrder($subcategory->id, $subcategoryOrder);
		
		if(!empty($subcategory->children)) {
			reorderSubcategories($subcategory->children, $subcategory->id);
		}
	}
}
