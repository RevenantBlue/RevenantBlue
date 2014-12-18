<?php
namespace RevenantBlue\Admin;
use RevenantBlue\Pager;
use RevenantBlue\ThumbnailGenerator;

require_once DIR_ADMIN . 'controller/articles/article-validation.php';
require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/categories/category-validation.php';
require_once DIR_ADMIN . 'controller/categories/category-hierarchy.php';
require_once DIR_ADMIN . 'model/articles/articles-main.php';
require_once DIR_ADMIN . 'model/categories/categories-main.php';
require_once DIR_ADMIN . 'model/comments/comments-main.php';
require_once DIR_SYSTEM . 'library/fine-diff.php';
require_once DIR_ADMIN . 'controller/common/content-filtering.php';
require_once DIR_ADMIN . 'model/tags/tags-main.php';

// Security Check.
if(!aclVerify('view articles') && !aclVerify('administer articles')) {
	header('Location: ' . HTTP_ADMIN, TRUE, 302);
	exit;
}

// Instantiate the article model.
$articles = new Articles;

// Instantiate the categories model.
$categories = new Categories;

// Load the pagination class.
$pager = new Pager;

// Load the comments class.
$comments = new Comments;

// Load the Tags class
$tags = new Tags;

// Set the limit for the articles overview.
$numOfArticlesToShow = $users->getOptionValueForUser($_SESSION['userId'], 49);
if(!empty($numOfArticlesToShow)) $pager->limit = $numOfArticlesToShow;

// Set the article list to display new search results.
if($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset($_GET['search']) && isset($_GET['order']) && isset($_GET['sort'])) {
		if(in_array($_GET['order'], $articles->whiteList)) {
			$pager->totalRecords = $articles->countArticleSearch($_GET['search']);
			$pager->paginate();
			$articleList = $articles->loadArticleSearch($pager->limit, $pager->offset, $_GET['search'], $_GET['order'], $_GET['sort']);
		} else {
			header('Location: ' . HTTP_ADMIN . 'articles/', TRUE, 302);
			exit;
		}
	} elseif(isset($_GET['published'])) {
		if(in_array($_GET['order'], $articles->whiteList)) {
			$pager->totalRecords = $articles->countArticlesByPublished($_GET['published']);
			$pager->paginate();
			$articleList = $articles->loadArticlesByPublished($pager->limit, $pager->offset, $_GET['published'], $_GET['order'], $_GET['sort']);
		} else {
			header('Location: ' . HTTP_ADMIN . 'articles/', TRUE, 302);
			exit;
		}
	} elseif(isset($_GET['featured'])) {
		if(in_array($_GET['order'], $articles->whiteList)) {
			$pager->totalRecords = $articles->countArticlesByFeatured($_GET['featured']);
			$pager->paginate();
			$articleList = $articles->loadArticlesByFeatured($pager->limit, $pager->offset, $_GET['featured'], $_GET['order'], $_GET['sort']);
		} else {
			header('Location: ' . HTTP_ADMIN . 'articles/', TRUE, 302);
			exit;
		}
	} elseif(isset($_GET['category'])) {
		if(in_array($_GET['order'], $articles->whiteList)) {
			$pager->totalRecords = $articles->countArticlesByCategory($_GET['category']);
			$pager->paginate();
			$articleList = $articles->loadArticlesByCategory($pager->limit, $pager->offset, $_GET['category'], $_GET['order'], $_GET['sort']);
		} else {
			header('Location: ' . HTTP_ADMIN . 'articles/', TRUE, 302);
			exit;
		}
	} elseif(isset($_GET['tag'])) {
		if(!isset($_GET['order']) || !isset($_GET['sort'])) {
			header('Location: ' . HTTP_ADMIN . 'articles/?tag=' . $_GET['tag'] . '&order=date_posted&sort=desc', TRUE, 302);
			exit;
		} else {
			if(in_array($_GET['order'], $articles->whiteList)) {
				$pager->totalRecords = $articles->countArticlesByTag($_GET['tag']);
				$pager->paginate();
				$articleList = $articles->loadArticlesByTag($pager->limit, $pager->offset, $_GET['tag'], $_GET['order'], $_GET['sort']);
			} else {
				header('Location: ' . HTTP_ADMIN . 'articles/', TRUE, 302);
				exit;
			}
		}
	} elseif(isset($_GET['author']) && isset($_GET['order']) && isset($_GET['sort'])) {
		if(in_array($_GET['order'], $articles->whiteList)) {
			$pager->totalRecords = $articles->countArticlesByAuthor($_GET['author']);
			$pager->paginate();
			$articleList = $articles->loadArticlesByAuthor($pager->limit, $pager->offset, $_GET['author'], $_GET['order'], $_GET['sort']);
		} else {
			header('Location: ' . HTTP_ADMIN . 'articles/', true, 302);
			exit;
		}
	} else {
		$pager->totalRecords = $articles->getNumOfArticles();
		if(isset($_GET['order']) && isset($_GET['sort']) && in_array($_GET['order'], $articles->whiteList)) {
			$pager->paginate();
			$articleList = $articles->loadArticles($pager->limit, $pager->offset, $_GET['order'], $_GET['sort']);
		} else {
			$pager->paginate();
			$articleList = $articles->loadArticles($pager->limit, $pager->offset, 'date_posted', 'desc');
		}
	}
	$pager->displayItemsPerPage();
	$pager->menu = str_replace('index.php?page=', BACKEND_NAME . '/articles/p', $pager->menu);
	$pager->menu = str_replace('&amp;controller=admin-articles', '', $pager->menu);
	$pager->limitMenu = str_replace('index.php?page=', BACKEND_NAME . '/articles/p', $pager->limitMenu);
	$pager->limitMenu = str_replace('&amp;controller=admin-articles', '', $pager->limitMenu);

	$backendUsers = $users->getUsersWithBackendAccess();

	// Build category array for the category select/option menu
	$categoryList = CategoryHierarchy::buildCategories($categories->countCategories(), 0);
	$categoryList = CategoryHierarchy::displayCategories($categoryList);
	$popularCategories = $articles->getPopularCategories();

	// Load the list of authors that have created articles.
	$authors = $articles->getArticleAuthors();
}

// Handle GET requests
if($_SERVER['REQUEST_METHOD'] == 'GET') {
	
	// Article overview requirements.
	if(isset($_GET['controller']) && $_GET['controller'] == 'articles') {
		$optionsForPage = $users->getOptionsByGroup('articles');
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'articles');
		// Build tag array for individual articles.
		if(!empty($articleList)) {
			foreach($articleList as $article) {
				$tagsForArticle = $tags->getTagsForArticle($article['id']);
				$articleTags[$article['id']] = array();
				foreach($tagsForArticle as $tagForArticle) {
					array_push($articleTags[$article['id']], array('tag_name' => $tagForArticle['tag_name'], 'tag_alias' => $tagForArticle['tag_alias']));
				}
			}
		}
		// If the user changes the number of records to show, store the change in the personal options table, the only argument is the option's ID.
		setNumToShow(49);
	}
	
	// Article profile requirements.
	if(isset($_GET['controller']) && $_GET['controller'] === 'article-profile') {
		// Load image templates for the media attachment section.
		$imageTemplates = $config->loadImageTemplates();

		// Load the user options for the article profile group - this determines which options to show for the user.
		$optionsForPage = $users->getOptionsByGroup('article profile');
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'article profile');
		
		// Placeholder for future tinymce global disable switch.
		$disableTinymce = false;
		
		// Get the content formats available for the user.
		$rolesForUser = $acl->getRolesForUser($_SESSION['userId']);
		foreach($rolesForUser as $roleForUser) {
			$formats = $config->getFormatFiltersByRole($roleForUser['role_id']);
			foreach($formats as $format) {
				$contentFormats[$format['id']] = $format;
			}
		}
		$popularTags = $tags->loadPopularArticleTags(40);
	}
	
	// Article edit profile requirements.
	if(isset($_GET['controller']) && $_GET['controller'] === 'article-profile' && isset($_GET['id']) && is_numeric($_GET['id'])) {
		if(!aclVerify('edit own article') && !aclVerify('edit any article') && !aclVerify('administer articles')) {
			header('Location: ' . HTTP_ADMIN . 'articles/', true, 302);
			exit;
		}
		$articleId = (int)$_GET['id'];
		$article = $articles->loadArticleById($articleId);
		
		if(!empty($article['disable_tinymce'])) {
			$disableTinymce = TRUE;
		} else {
			$disableTinymce = FALSE;
		}
		
		// Clean up the html output of the content and summary with HTMLPurifier only if tinymce is active.
		if(!$disableTinymce) {
			$article['content'] = tidyHTML($article['content'], TRUE);
			if(!empty($article['summary'])) {
				$article['summary'] = tidyHTML($article['summary']. TRUE);
			}
		}
		
		//print_r2(hsc($article['content'])); 
		// Get the revisions for the article
		$articleRevisions = $articles->getRevisions($articleId);
		// Get the tags for the article.
		$articleTags = $tags->getTagsForArticle($articleId);
		// Builds the string for the hiddenTags input element.
		$articleTagsStr = '';
		foreach($articleTags as $articleTag) {
			$articleTagsStr .= $articleTag['tag_name'] . ',';
		}
		$articleTagsStr = rtrim($articleTagsStr, ',');
		
		// Get the assigned categories for the article.
		$catForArticle = $articles->getCategoriesForArticle($article['id']);
	}
	
	if(isset($_GET['controller']) && $_GET['controller'] == 'article-revisions' && isset($_GET['id']) && is_numeric($_GET['id'])) {
		$escapedTags = array('&lt;p&gt;', '&lt;/p&gt;', '&lt;ins&gt;', '&lt;/ins&gt;', '&lt;del&gt;', '&lt;/del&gt;');
		$unescapedTags = array('<p>', '</p>', '<ins>', '</ins>', '<del>', '</del>');
		// If comparing revisions else show the selected revision.
		if(isset($_GET['compare'])) {
			$oldRevision = $articles->getRevision($_GET['compare']);
			$newRevision = $articles->getRevision($_GET['id']);
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
			$articleId = (int)$_GET['articleId'];
			$articleRevisions = $articles->getRevisions($articleId);
			$granularity = (int)$_GET['granularity'];
			// Compare the revisions.
			$diff = new FineDiff($oldRevision['content'], $newRevision['content'], $granularityStacks[$granularity]);
			// store opcodes for later use...
			$renderedDiff = str_replace($escapedTags, $unescapedTags, hsc(html_entity_decode(html_entity_decode($diff->renderDiffToHTML()))));
		} else {
			$revisionId = $_GET['id'];
			$articleId = $_GET['articleId'];
			$revision = $articles->getRevision($revisionId);
			$articleRevisions = $articles->getRevisions($articleId);
			$revision['content'] = str_replace($escapedTags, $unescapedTags, hsc($revision['content']));
		}
	}
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	if(!empty($_POST['submitArticleSearch'])) {
		header('Location: ' . HTTP_ADMIN . "articles?search=" . urlencode($_POST['articleToSearch']) . "&order=date_posted&sort=desc", TRUE, 302);
		exit;
	} elseif(isset($_POST['publishedFilter'])) {
		header('Location: ' . HTTP_ADMIN . "articles?published=" . urlencode($_POST['publishedFilter']) . "&order=date_posted&sort=desc", TRUE, 302);
		exit;
	} elseif(isset($_POST['featuredFilter'])) {
		header('Location: ' . HTTP_ADMIN . 'articles?featured=' . urlencode($_POST['featuredFilter']) . '&order=date_posted&sort=desc', TRUE, 302);
		exit;
	} elseif(isset($_POST['categoryFilter'])) {
		header('Location: ' . HTTP_ADMIN . 'articles?category=' . urlencode($_POST['categoryFilter']) . '&order=date_posted&sort=desc', TRUE, 302);
		exit;
	} elseif(isset($_POST['authorFilter'])) {
		header('Location: ' . HTTP_ADMIN . 'articles?author=' . urlencode($_POST['authorFilter']) . '&order=date_posted&sort=desc', TRUE, 302);
		exit;
	} elseif(isset($_POST['articleAction'])) {
		// Edit article request
		if($_POST['articleAction'] === 'edit' && is_array($_POST['articleCheck'])) {
			header('Location: ' . HTTP_ADMIN . 'articles/' . $_POST['articleCheck'][0] . '/edit/', TRUE, 302);
			exit;
		}
		// Delete article request
		if((aclVerify('delete all articles') || aclVerify('delete own article')) && $_POST['articleAction'] === 'delete') {
			foreach($_POST['articleCheck'] as $id) {
				// Delete the article's image if it exists.
				$imageToDelete = $articles->getArticleImage($id);
				if(!empty($imageToDelete) && file_exists($imageToDelete['image_path'])) {
					$imgDeleted = unlink($imageToDelete['image_path']);
				}
				$deleteSuccess[$id] = $articles->deleteArticle($id);
				// Delete article comments.
				$numOfCommentsForArticle = $comments->countComments($id);
				$commentsForArticle = $comments->loadComments($numOfCommentsForArticle, 0, $id, 'com_id', 'asc');
				foreach($commentsForArticle as $commentToDel) {
					$comments->deleteCommentTree($commentToDel['com_id']);
					$comments->deleteComment($commentToDel['com_id']);
				}
				if(!empty($deleteSuccess[$id])) {
					$_SESSION['success'] = "Article(s) deleted successfully.";
				} else {
					$_SESSION['errors'] = "An error occured while deleting an article.";
				}
			}
			
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
		// Process information for a new article.
		if((aclVerify('edit own article') || aclVerify('edit any article') || aclVerify('administer articles')) && ($_POST['articleAction'] === 'save' || $_POST['articleAction'] === 'save-new' || $_POST['articleAction'] === 'save-close')) {
			$article = array();
			$article['id'] = isset($_POST['id']) ? $_POST['id'] : '';
			$article['author'] = isset($_POST['author']) ? $_POST['author'] : $_SESSION['userId'];
			$article['title'] = isset($_POST['title']) ? $_POST['title'] : '';
			$article['alias'] = isset($_POST['alias']) ? $_POST['alias'] : '';
			$article['datePosted'] = isset($_POST['datePosted']) ? $_POST['datePosted'] : '';
			$article['content'] = isset($_POST['content']) ? $_POST['content'] : '';
			$article['summary'] = isset($_POST['summary']) ? $_POST['summary'] : '';
			$article['image'] = isset($_POST['image']) ? $_POST['image'] : '';
			$article['imageAlt'] = isset($_POST['imageAlt']) ? $_POST['imageAlt'] : '';
			$article['imagePath'] = isset($_POST['imagePath']) ? $_POST['imagePath'] : '';
			$article['featured'] = isset($_POST['featured']) ? $_POST['featured'] : '';
			$article['published'] = isset($_POST['published']) ? $_POST['published'] : '';
			$article['tags'] = isset($_POST['hiddenTags']) ? $_POST['hiddenTags'] : '';
			$article['categories'] = isset($_POST['articleCategories']) ? $_POST['articleCategories'] : '';
			$article['metaDescription'] = isset($_POST['metaDescription']) ? $_POST['metaDescription'] : '';
			$article['metaKeywords'] = isset($_POST['metaKeywords']) ? $_POST['metaKeywords'] : '';
			$article['metaRobots'] = isset($_POST['metaRobots']) ? $_POST['metaRobots'] : '';
			$article['metaAuthor'] = isset($_POST['metaAuthor']) ? $_POST['metaAuthor'] : '';
			$article['attribs'] = isset($_POST['attribs']) ? $_POST['attribs'] : '';
			$article['contentFormatId'] = isset($_POST['contentFormat']) ? $_POST['contentFormat'] : '';
			$article['weight'] = isset($_POST['weight']) ? $_POST['weight'] : 0;
			
			// Instantiate the article validation class and validate the article array.
			$articleValidate = new ArticleValidation($article);

			// If a new article has been submitted.
			if(empty($_POST['id']) && empty($articleValidate->errors)) {
				$newArticleId = $articles->insertArticle($articleValidate->article);
				
				$articleValidate->id = $newArticleId;
				if(!empty($newArticleId)) {
					// Insert the categories for the article.
					foreach($articleValidate->categoryIds as $categoryId) {
						$insertCategory[] = $articles->insertArticleCategory($newArticleId, $categoryId);
					}
					if(isset($insertCategory) && !in_array(NULL, $insertCategory)) {
						$_SESSION['success'] = "Article created successfully.";
					} else {
						// Assign the new article to the unassigned category.
						$articles->insertArticleCategory($newArticleId, 1);
						$_SESSION['success'] = "Article created successfully.";
					}
					
					// Insert tags
					if(!empty($articleValidate->tags)) {
						foreach($articleValidate->tags as $newTag) {
							$tagId = $tags->getTagIdByName($newTag);
							$tags->insertArticleTag($tagId, $newArticleId);
						}
					}
					
					// Set the id of the new article for revision insertion.
					$articleValidate->article['id'] = $newArticleId;
					// Insert the revision
					$revisionId = $articles->insertRevision($articleValidate->article, 'Current Revision', 1);
					if(empty($revisionId)) $_SESSION['errors'][] = "An error occured while creating the revision for this article.";
				} else {
					$_SESSION['errors'][] = "An error occured while inserting the article into the database.";
				}
			}
			// If an edited article is being submitted.
			if(isset($_POST['id']) && empty($articleValidate->errors)) {
				// Process the entry for the database.
				$updateSuccess = $articles->updateArticle($articleValidate->article);
				if(!empty($updateSuccess)) {
					// Compare the old and new categories for this article and make the changes accordingly.
					$currentCategories = $articles->getCategoriesForArticle($articleValidate->id);
					$categoriesToInsert = (array_diff($articleValidate->categoryIds, $currentCategories));
					$categoriesToDelete = (array_diff($currentCategories, $articleValidate->categoryIds));
					foreach($categoriesToInsert as $category) {
						$articles->insertArticleCategory($articleValidate->id, $category);
					}
					foreach($categoriesToDelete as $category) {
						$articles->deleteCategoryForArticle($articleValidate->id, $category);
					}
					// Compare the current tags to the new tags and make changes.
					$newTags = !empty($articleValidate->tags) ? $articleValidate->tags : array();

					$tags = new Tags;
					$currentTags = $tags->getTagNamesForArticle($articleValidate->id);
					// Insert/delete tags depending on changes if necessary.
					$tagsToInsert = (array_diff($newTags, $currentTags));
					$tagsToDelete = (array_diff($currentTags, $newTags));
					
					// Insert new article tags
					foreach($tagsToInsert as $tagToInsert) {
						$tagId = $tags->getTagIdByName($tagToInsert);
						$tags->insertArticleTag($tagId, $articleValidate->id);
					}
					// Delete removed article tags
					foreach($tagsToDelete as $tagToDelete) {
						$tagId = $tags->getTagIdByName($tagToDelete);
						$tags->deleteArticleTag($tagId, $articleValidate->id);
					}
					// Create a new current revision, remove current status from old revision.
					$articles->clearCurrentRevision($articleValidate->id);
					$articles->setRevisionTypeForCurrent($articleValidate->id, '');
					// Set the id for revision insertion.
					$articleValidate->article['id'] = $articleValidate->id;
					// Insert article revision and set it to the current revision for that article.
					$articles->insertRevision($articleValidate->article, 'Current Revision', 1);
					$_SESSION['success'] = "Article updated successfully.";
				}
			}
			
			// Update the article attributes for the article.
			if(!empty($articleValidate->attribs) && is_array($articleValidate->attribs) && !empty($articleValidate->id)) {
				foreach($articleValidate->attribs as $attribName => $attribValue) {
					if(in_array($attribName, $articles->whiteList)) {
						$articleId = isset($newArticleId) ? $newArticleId : $articleValidate->id;
						$updateAttribs[$attribName] = $articles->setAttribute($articleId, $attribName, $attribValue);
					}
				}
			}
			
			if(!empty($articleValidate->errors) || !empty($_SESSION['errors'])) {
				$_SESSION['errors'][] = $articleValidate->errors;
				$_SESSION['article'] = $articleValidate;
				if(empty($_POST['id']) && !empty($newArticleId)) {
					header('Location: ' . HTTP_ADMIN . 'articles/' . urlencode($newArticleId) . '/edit', true, 302);
					exit;
				} elseif(isset($_POST['id'])) {
					header('Location: ' . HTTP_ADMIN . 'articles/' . urlencode($articleValidate->id) . '/edit', true, 302);
					exit;
				}	else {
					header('Location: ' . HTTP_ADMIN . 'articles/new/', true, 302);
					exit;
				}
			} else {
				// Clear out the article session object.
				if(isset($_SESSION['article'])) unset($_SESSION['article']);
				// Redirect user.
				switch($_POST['articleAction']) {
					case 'save':
						if(empty($_POST['id']) && !empty($newArticleId)) {
							header('Location: ' . HTTP_ADMIN . 'articles/' . urlencode($newArticleId) . '/edit', true, 302);
							exit;
						} elseif(isset($_POST['id'])) {
							header('Location: ' . HTTP_ADMIN . 'articles/' . urlencode($articleValidate->id) . '/edit', true, 302);
							exit;
						}
					case 'save-close':
						header('Location: ' . HTTP_ADMIN . 'articles/', true, 302);
						exit;
						break;
					case 'save-new':
						header('Location: ' . HTTP_ADMIN . 'articles/new/', true, 302);
						exit;
						break;
					default:
						header('Location: ' . $_SERVER['REQUEST_URI'], true, 302);
						exit;
				}
			}
		}
	} elseif(isset($_POST['adminRequest'])) {
		// Handle all article AJAX requests with JSON.
		$adminReq = json_decode($_POST['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type == 'article') {
			if(aclVerify('edit own article') || aclVerify('edit any article') || aclVerify('administer articles')) {
				if($adminReq->action === "add-category") {
					$categoryArr = array( 
						'title'       => $adminReq->categoryName,
						'alias'       => '',
						'parent'      => $adminReq->parentId,
						'published'   => 1,
						'datePosted'  => '',
						'description' => '',
						'image'       => '',
						'imagePath'   => '',
						'imageAlt'    => '',
						'createdBy'   => $users->getUsernameById($_SESSION['userId'])
					);
					$categoryValidate = new CategoryValidation($categoryArr);
					$newCategoryId = $categories->insertCategoryQuick($categoryValidate->category, $categoryValidate->parentCategory);
					if(!empty($newCategoryId)) {
						$newCategory = $categories->getCategoryById($newCategoryId);
						$adminReq->categoryId = $newCategoryId;
						$adminReq->rootDistance = $newCategory['root_distance'];
					} else {
						$adminReq->error = "An error occured while creating this category";
					}
				} elseif($adminReq->action == "autosave") {
					$article = array( 
						'author'          => $adminReq->author,
						'title'           => $adminReq->title,
						'alias'           => $adminReq->alias,
						'datePosted'      => $adminReq->datePosted,
						'content'         => $adminReq->content,
						'summary'         => $adminReq->summary,
						'image'           => $adminReq->image,
						'imageAlt'        => $adminReq->imageAlt,
						'imagePath'       => $adminReq->imagePath,
						'featured'        => $adminReq->featured,
						'published'       => 2,
						'contentFormatId' => $adminReq->contentFormatId,
						'categories'      => $adminReq->categories,
						'metaDescription' => $adminReq->metaDescription,
						'metaKeywords'    => $adminReq->metaKeywords,
						'metaRobots'      => $adminReq->metaRobots,
						'metaAuthor'      => $adminReq->metaAuthor
					);
					// Add the last saved date.
					$adminReq->lastSave = date('m-d-Y h:i:s A', time());
					// Validate the article.
					$articleValidate = new ArticleValidation($article);
					//hack: if no date posted was provided clear the auto generated date from the validation.
					if(empty($adminReq->datePosted)) $article['datePosted'] = '';
					// If updating an autosaved draft.
					if(!empty($adminReq->id) && empty($articleValidate->errors)) {
						$updateSuccess = $articles->updateArticleQuick($articleValidate->article);
						if(!empty($updateSuccess)) {
							$currentCategories = $articles->getCategoriesForArticle($articleValidate->id);
							$categoriesToInsert = (array_diff($articleValidate->categoryIds, $currentCategories));
							$categoriesToDelete = (array_diff($currentCategories, $articleValidate->categoryIds));
							foreach($categoriesToInsert as $category) {
								$articles->insertArticleCategory($articleValidate->id, $category);
							}
							foreach($categoriesToDelete as $category) {
								$articles->deleteCategoryForArticle($articleValidate->id, $category);
							}
						}
					// If creating an autosaved draft.
					} elseif(empty($articleValidate->errors) && empty($articleValidate->id)) {
						// Insert the draft article.
						$newArticleId = $articles->insertArticleQuick($articleValidate->article);
						$adminReq->id = $newArticleId;
						// Insert the categories for the article.
						if(empty($articleValidate->categoryIds)) {
							$articleValidate->categoryIds[] = 1;
						} else {
							foreach($articleValidate->categoryIds as $categoryId) {
								// Test for existing category.
								$existingCategory = $articles->getCategoryForArticle($newArticleId, $categoryId);
								if(!$existingCategory) {
									$insertCategory[] = $articles->insertArticleCategory($newArticleId, $categoryId);
								}
							}
						}
					} elseif(!empty($articleValidate->errors)) {
						$adminReq->errors = $articleValidate->errors;
					}
				// If revising an alredy published article.
				} elseif($adminReq->action == "revision") {
					// Add the last saved date.
					$adminReq->lastSave = date('m-d-Y h:i:s A', time());
					// Validate the article.
					$articleValidate = new ArticleValidation;
					$articleValidate->validateId($adminReq->id);
					$articleValidate->validateTitle($adminReq->title);
					$articleValidate->validateContent($adminReq->content, $adminReq->contentFormatId);
					// Assign the validated variables to an array.
					$article = array( 
						'id'         => $articleValidate->id,
						'author'     => $adminReq->author,
						'title'      => $articleValidate->title,
						'content'    => $articleValidate->content,
						'revisionId' => (int)$adminReq->revisionId,
					);
					// Only insert or update a revision if content exists.
					if(!empty($article->content)) {
						// If updating an autosaved draft.
						if(!empty($adminReq->revisionId) && empty($articleValidate->errors)) {
							// Set the revision id.
							$updateSuccess = $articles->updateRevision($adminReq->revisionId, $article, 'Autosave', 0);
						// If creating a new revision
						} elseif(empty($articleValidate->errors) && empty($adminReq->revisionId)) {
							// Insert the article revision.
							$revisionId = $articles->insertRevision($article, 'Autosave', 0);
							// Set the id to the revision id for further auto revision updates.
							$adminReq->revisionId = $revisionId;
						} elseif(!empty($articleValidate->errors)) {
							$adminReq->errors = $articleValidate->errors;
						}
					}
				} elseif($adminReq->action === 'publish' || $adminReq->action === 'unpublish' || $adminReq->action === 'featured' || $adminReq->action === 'remove-featured') {
					for($x = 0; $x < count($adminReq->ids); $x++) {
						if(is_numeric($adminReq->ids[$x])) {
							if($adminReq->action === "publish") {
								$articles->setPublishedState($adminReq->ids[$x], 1);
								$adminReq->publish[$x] = $articles->getPublishedState($adminReq->ids[$x]);
							} elseif($adminReq->action === "unpublish") {
								$articles->setPublishedState($adminReq->ids[$x], 0);
								$adminReq->publish[$x] = $articles->getPublishedState($adminReq->ids[$x]);
							} elseif($adminReq->action === "featured") {
								$articles->setFeatured($adminReq->ids[$x], 1);
								$adminReq->featured[$x] = $articles->getFeatured($adminReq->ids[$x]);
							} elseif($adminReq->action === "remove-featured") {
								$articles->setFeatured($adminReq->ids[$x], 0);
								$adminReq->featured[$x] = $articles->getFeatured($adminReq->ids[$x]);
							}
						}
					}
				} elseif($adminReq->action === 'delete-image') {
					if(!empty($adminReq->id)) {
						// Get article info in order to delete the physical image file.
						$article = $articles->loadArticleById($adminReq->id);
						// Delete the physical article image as well as the database url/alt/path entries.
						if(!empty($article)) {
							@unlink($article['image_path']);
							$articles->setArticleImage($adminReq->id, '', '');
							
							$adminReq->success = TRUE;
						}
					} elseif(!empty($adminReq->imagePath)) {
						if(file_exists($adminReq->imagePath)) {
							$imgPath = pathinfo($adminReq->imagePath);
							if($imgPath['dirname'] === DIR_IMAGE . 'articles') {
								@unlink($adminReq->imagePath);
								$adminReq->success = TRUE;
							}
						}
					}
				} elseif($adminReq->action === 'update-globals') {
					if(isset($adminReq->allowComments)) {
						$updated = $config->updateConfiguration((int)$adminReq->allowComments, 'allow_comments');
					}
					
					if(isset($adminReq->showIntroText)) {
						$config->updateConfiguration((int)$adminReq->showIntroText, 'show_intro_text');
					}
					
					if(isset($adminReq->deletedCommentText)) {
						$config->updateConfiguration($adminReq->deletedCommentText, 'deleted_comment_text');
					}
					
					if(REDIS === TRUE) {
						clearCache();
					}
				} elseif($adminReq->action === 'tidy-article-content') {
					if(!empty($adminReq->tinymceDisabled) && $adminReq->tinymceDisabled) {
						echo $adminReq->content;
					} else {
						echo hsc(tidyHTML($adminReq->content, TRUE));
					}
					exit;
				}
			}
		}
		echo json_encode($adminReq);
		exit;
	} elseif(isset($_POST['revisionAction'])) {
		if(aclVerify('edit own article') || aclVerify('edit any article') || aclVerify('administer articles')) {
			// Article revision requests
			// Delete revision request.
			if(isset($_POST['revisionCheck']) && $_POST['revisionAction'] === "delete") {
				if(aclVerify('delete own revisions') && !aclVerify('delete all revisions')) {
					foreach($_POST['revisionCheck'] as $revisionToDelete) {
						if($_SESSION['username'] === $articles->getRevisionAuthor($revisionToDelete)) {
							$deleteRevSuccess[] = $articles->deleteRevision($revisionToDelete);
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
						$deleteRevSuccess[] = $articles->deleteRevision($revisionToDelete);
						
						if($revisionToDelete == $_GET['id']) {
							$viewedRevDeleted = TRUE;
						}
					}
				} else {
					$_SESSION['errors'] = "You do not have permission to delete article revisions";
				}
				if(empty($viewedRevDeleted)) {
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
				} else {
					header('Location: ' . HTTP_ADMIN . 'articles/' . (int)$_GET['articleId'] . '/edit', TRUE, 302);
					exit;
				}
			}
			// Compare revision request
			if(isset($_POST['oldRevision']) && isset($_POST['newRevision']) && isset($_POST['granularity']) && $_POST['revisionAction'] === "compare") {
				header('Location: ' . HTTP_ADMIN . 'articles/revision/' . $_POST['newRevision'] . '/' . $_GET['articleId'] . '/' . $_POST['oldRevision'] . "/" . $_POST['granularity'], true, 302);
				exit;
			}
			// Restore revision request
			if(isset($_POST['revisionCheck']) && $_POST['revisionAction'] === "restore") {
				$revisionRestored = $articles->restoreRevision($_GET['articleId'], $_POST['revisionCheck'][0]);
				if(!empty($revisionRestored)) {
					$_SESSION['success'] = 'The article was successfully restored to the selected revision.';
				} else {
					$_SESSION['errors'] = 'An error occurred while restoring the revision.';
				}
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			}
		}
	} elseif(isset($_GET['adminRequest'])) {
		// Special ajax request for the article image upload. Image content sent via POST and the adminReq via GET.
		$adminReq = json_decode($_GET['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type == 'article') {
			if(aclVerify('edit own article') || aclVerify('edit any article') || aclVerify('administer articles')) {
				if($adminReq->action === 'upload-image') {
					$width = !empty($adminReq->width) ? (int)$adminReq->width : '';
					$height = !empty($adminReq->height) ? (int)$adminReq->height : '';
					// Upload the new image.
					$adminResponse = pluploadImage(DIR_IMAGE . 'articles/', $width, $height, HTTP_IMAGE . 'articles/', 'article-');
					if(!empty($adminResponse) && is_object($adminResponse)) {
						if(!empty($adminReq->id) && $adminReq->id !== 'undefined') {
							// Get the author's id
							$articles->getAuthor($adminReq->id);
							// Get the previous image.
							$prevImage = $articles->getArticleImage($adminReq->id);
							// Delete the previous image.
							if(!empty($prevImage['image_path']) && file_exists($prevImage['image_path'])) {
								@unlink($prevImage['image_path']);
							}
							// Update the article with the new image.
							$imageUpdated = $articles->setArticleImage($adminReq->id, $adminResponse->fileName, $adminResponse->imagePath);
						} elseif(!empty($adminReq->imagePath)) {
							$imageInfo = pathinfo($adminReq->imagePath);
							if($imageInfo['dirname'] === DIR_IMAGE . 'articles') {
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
		// If no POST requests matched return the user to the previous page.
		$_SESSION['errors'] = 'The server was not able to respond to your POST submission.';
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	}
}

function articleCleanUp() {
	if(isset($_SESSION['article'])) {
		unset($_SESSION['article']);
	}
}

function deleteArticleImage($articleId) {
	global $articles;
	
	$imagePath = $articles->getImageInfo($articleId);

}

function insertFakeArticles($numOfArticles) {
	
	global $articles;
	
	$titles = array(
		'Proactively harness distinctive action items'
	  , 'Credibly simplify holistic innovation'
	  , 'Synergistically fabricate out-of-the-box opportunities'
	  , 'Efficiently evisculate bleeding-edge growth strategies'
	  , 'Fungibly restore installed base paradigms'
	  , 'Continually mesh accurate systems'
	  , 'Fungibly mesh extensible clouds'
	  , 'Monotonectally strategize 24/7 quality vectors'
	  , 'Dramatically fabricate customer directed methods of empowerment'
	  , 'Interactively reconceptualize premium resources'
	  , 'Objectively plagiarize cross functional metrics'
	  , 'Intrinsicly envisioneer distinctive meta-services'
	  , 'Collaboratively maintain go forward synergy'
	  , 'Rapidiously re-engineer ubiquitous nosql'
	);
	
	require_once DIR_ADMIN . 'controller/articles/article-validation.php';
	for($x = 0; $x <= $numOfArticles; $x++) {
		$articleValidation = new ArticleValidation;
		
		$article['author'] = $_SESSION['userId'];
		$article['title'] = $titles[array_rand($titles)];
		$article['alias'] = $articleValidation->validateAlias($article['title'], TRUE);
		$article['image'] = '';
		$article['imageAlt'] = '';
		$article['content'] = nl2br("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed sit amet enim quis arcu ultrices interdum ut id metus. Nullam dignissim erat dolor, molestie condimentum est dapibus id. Donec diam metus, bibendum vel adipiscing at, blandit ac sapien. Nam a metus ultricies, tempor tortor in, gravida ante. Fusce quis mauris a felis consectetur sodales varius nec ligula. Donec ultrices enim non semper lobortis. Interdum et malesuada fames ac ante ipsum primis in faucibus. Praesent ipsum nibh, mattis sed est ac, sagittis tempor turpis. Donec nec leo ut urna rhoncus ullamcorper. Phasellus sed mauris luctus, aliquet nulla sit amet, lacinia nisi. Fusce feugiat felis neque, nec hendrerit sem ultricies vel. Donec eu justo quis nisi tristique egestas. In bibendum, enim ut bibendum aliquam, turpis felis gravida leo, ac sollicitudin risus dui aliquet tortor. Nullam odio nulla, rhoncus sit amet elit non, placerat pellentesque nulla.
\n\nDonec tincidunt bibendum elit, sit amet pharetra erat ullamcorper viverra. Vivamus in neque dui. Cras lacinia egestas tristique. Duis quis tincidunt nibh, in elementum lectus. Quisque massa sem, ornare ac orci sed, scelerisque faucibus metus. Proin semper risus sapien, sed ultricies turpis congue eget. Ut ultrices tortor dolor, eget malesuada lorem commodo vel. Donec consequat justo ligula, eget cursus tellus feugiat et. Morbi et facilisis velit. Duis blandit eu ante a suscipit. Pellentesque facilisis massa augue, id euismod felis auctor et. Cras accumsan dui diam, vel faucibus orci ultricies non. Aliquam sit amet diam iaculis, condimentum velit molestie, pharetra tellus.
\n\nMaecenas semper tortor vitae volutpat pellentesque. Nulla eleifend, massa eget semper posuere, ante lorem feugiat est, sagittis vulputate libero quam et dui. Nulla vel cursus augue. Vivamus tincidunt metus enim, vel porttitor tellus cursus quis. Nam nec odio et nisi accumsan porttitor. Pellentesque bibendum risus id est semper, at luctus mi viverra. Nulla tempus congue semper. Suspendisse a velit scelerisque, euismod diam vitae, aliquam ipsum. Suspendisse tempus magna id bibendum pellentesque. Vestibulum lacinia odio sed porta mattis.");
		$article['summary'] = "Fusce aliquam ligula vitae lobortis viverra. Curabitur nec justo vitae odio scelerisque sagittis vel eu urna. Nullam vestibulum felis a placerat varius. Sed ultricies metus et eleifend tristique. Integer interdum porta sem, ullamcorper vestibulum sapien. Aenean sagittis molestie lectus, vitae rutrum massa varius eu. Quisque dignissim feugiat felis, sed eleifend ipsum accumsan non. Duis convallis mi eros, vel imperdiet felis porta in.";
		$article['categories'] = array('2');
		$article['datePosted'] = date("Y-m-d H:i:s", time());
		$article['published'] = 1;
		$article['featured'] = 0;
		$article['metaDescription'] = '';
		$article['metaKeywords'] = '';
		$article['metaAuthor'] = '';
		$article['metaRobots'] = '';
		$article['weight'] = 1;

		$newArticleId = $articles->insertArticle($article);
		
		foreach($article['categories'] as $categoryId) {
			$insertCategory[] = $articles->insertArticleCategory($newArticleId, $categoryId);
		}
		sleep(2);
	}
}

//insertFakeArticles(40);
