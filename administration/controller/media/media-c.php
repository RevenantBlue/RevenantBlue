<?php
namespace RevenantBlue\Admin;
use RevenantBlue\ThumbnailGenerator;
use RevenantBlue\Pager;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'model/media/media-main.php';
require_once DIR_ADMIN . 'model/articles/articles-main.php';
require_once DIR_SYSTEM . 'library/detect-browser.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';

$browser = getBrowser();

$media = new Media;

$articles = new Articles;

$pager = new Pager;

$imageMimes = array( 'image/jpeg', 'image/gif', 'image/png' );

if(isset($_GET['controller'])) {
	if($_GET['controller'] === 'media') {
		$numOfFilesToShow = $users->getOptionValueForUser($_SESSION['userId'], 51);
		if(!empty($numOfFilesToShow)) $pager->limit = $numOfFilesToShow;
		// Add the query string if the media overview is being accessed from the article section.
		$attachQuery = isset($_GET['attach']) ? '&attach=true' : '';
		// Load the image templates
		$imageTemplates = $config->loadImageTemplates();
		if(!empty($_POST['submitMediaSearch'])) {
			header('Location: ' . HTTP_ADMIN . 'media?search=' . urlencode($_POST['mediaToSearch']) . '&order=date_posted&sort=desc' . $attachQuery, TRUE, 302);
			exit;
		} elseif(!empty($_POST['attachFilter'])) {
			header('Location: ' . HTTP_ADMIN . 'media?attached=' . urlencode($_POST['attachFilter']) . '&order=date_posted&sort=desc' . $attachQuery, TRUE, 302);
			exit;
		} elseif(!empty($_POST['typeFilter'])) {
			header('Location: ' . HTTP_ADMIN . 'media?type=' . urlencode($_POST['typeFilter']) . '&order=date_posted&sort=desc' . $attachQuery, TRUE, 302);
			exit;
		} elseif(isset($_GET['search']) && isset($_GET['order']) && isset($_GET['sort'])) {
			if(in_array($_GET['order'], $media->whiteList)) {
				$pager->totalRecords = $media->countMediaSearch($_GET['search']);
				$pager->paginate();
				$mediaFiles = $media->loadMediaSearch($pager->limit, $pager->offset, $_GET['search'], $_GET['order'], $_GET['sort']);
			} else {
				header('Location: ' . HTTP_ADMIN . 'media/' . $attachQuery, TRUE, 302);
				exit;
			}
		} elseif(isset($_GET['attached']) && isset($_GET['order']) && isset($_GET['sort'])) {
			if(in_array($_GET['order'], $media->whiteList)) {
				$pager->totalRecords = $media->countMediaByAttached($_GET['attached']);
				$pager->paginate();
				$mediaFiles = $media->loadMediaByAttached($pager->limit, $pager->offset, $_GET['attached'], $_GET['order'], $_GET['sort']);
			} else {
				header('Location: ' . HTTP_ADMIN . 'media/' . $attachQuery, TRUE, 302);
				exit;
			}
		} elseif(isset($_GET['type']) && isset($_GET['order']) && isset($_GET['sort'])) {
			if(in_array($_GET['order'], $media->whiteList)) {
				$pager->totalRecords = $media->countMediaByType($_GET['type']);
				$pager->paginate();
				$mediaFiles = $media->loadMediaByType($pager->limit, $pager->offset, $_GET['type'], $_GET['order'], $_GET['sort']);
			} else {
				header('Location: ' . HTTP_ADMIN . 'media/' . $attachQuery, TRUE, 302);
				exit;
			}
		} else {
			$pager->totalRecords = $media->countMediaLibrary();
			if(isset($_GET['order']) && isset($_GET['sort']) && in_array($_GET['order'], $media->whiteList)) {
				$pager->paginate();
				$mediaFiles = $media->loadMediaLibrary($pager->limit, $pager->offset, $_GET['order'], $_GET['sort']);
			} else {
				$pager->paginate();
				$mediaFiles = $media->loadMediaLibrary($pager->limit, $pager->offset, 'date_posted', 'desc');
			}
		}

		// Get media attachments
		if(!empty($mediaFiles)) {
			foreach($mediaFiles as $mediaFile) {
				$attachments = $media->getMediaAttachments($mediaFile['id']);
				foreach($attachments as $attachment) {
					if(!isset($mediaAttachments[$mediaFile['id']])) {
						$mediaAttachments[$mediaFile['id']] = array();
					}
					if(!empty($attachment['article_id'])) {
						$attachment['title'] = $articles->getArticleTitle($attachment['article_id']);
					} elseif(!empty($attachment['page_id'])) {
						$attachment['title'] = $articles->getPageTitle($attachment['page_id']);
					}
					array_push($mediaAttachments[$mediaFile['id']], $attachment);
				}
			}
		}

		$pager->displayItemsPerPage();
		$pager->menu = str_replace('index.php?page=', BACKEND_NAME . '/media/p', $pager->menu);
		$pager->menu = str_replace('&amp;controller=admin-media', '', $pager->menu);
		$pager->limitMenu = str_replace('index.php?page=', BACKEND_NAME . '/media/p', $pager->limitMenu);
		$pager->limitMenu = str_replace('&amp;controller=admin-media', '', $pager->limitMenu);

		// If the user changes the number of records to show, store the change in the personal options table.
		setNumToShow(51);
		
		// Get the user options for the medie overview.
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'media');
		$optionsForPage = $users->getOptionsByGroup('media');
	} elseif($_GET['controller'] === 'media-profile') {
		if(isset($_GET['id'])) {
			$mediaFile = $media->getMedia((int)$_GET['id']);
			if(empty($mediaFile)) {
				header('Location: ' . HTTP_ADMIN . 'media', TRUE, 302);
				exit;
			} else {
				$mediaDimensions = getimagesize($mediaFile['media_url']);
				$mediaWidth = $mediaDimensions[0];
				$mediaHeight = $mediaDimensions[1];
			}
		}
		// Get user options for the media profile.
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'media profile');
		$optionsForPage = $users->getOptionsByGroup('media profile');
	} elseif($_GET['controller'] === 'media-upload') {
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'media upload');
		$optionsForPage = $users->getOptionsByGroup('media upload');
	}
}
if($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Handle media upload requests
	if(isset($_POST['submitMediaUploads']) && !empty($_POST['uploads']) && is_array($_POST['uploads'])) {
		// Updating multiple media types from the plupload uploader.
		foreach($_POST['uploads'] as $upload) {
			$mediaArr['mediaId'] = isset($upload['id']) ? $upload['id'] : '';
			$mediaArr['mediaTitle'] = isset($upload['title']) ? $upload['title'] : '';
			$mediaArr['mediaDescription'] = isset($upload['description']) ? $upload['description'] : '';
			$mediaArr['mediaCaption'] = isset($upload['caption']) ? $upload['caption'] : '';
			$mediaArr['mediaAlt'] = isset($upload['alt']) ? $upload['alt'] : '';
			$globalValidate = new GlobalValidation;
			$mediaArr['mediaTitle'] = $globalValidate->validateTitle($mediaArr['mediaTitle']);
			$mediaArr['mediaDescription'] = $globalValidate->validateDescription($mediaArr['mediaDescription']);
			$updateMedia = $media->updateMedia($mediaArr['mediaId'], $mediaArr);
		}
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	} elseif(isset($_POST['mediaAction']) && $_POST['mediaAction'] === 'edit' && is_array($_POST['mediaCheck'])) {
		header('Location: ' . HTTP_ADMIN . 'media/' . (int)$_POST['mediaCheck'][0], TRUE, 302);
		exit;
	} elseif(isset($_POST['mediaAction']) && $_POST['mediaAction'] !== 'delete') {
		$mediaArr['mediaId'] = isset($_POST['mediaId']) ? $_POST['mediaId'] : '';
		$mediaArr['mediaTitle'] = isset($_POST['mediaTitle']) ? $_POST['mediaTitle'] : '';
		$mediaArr['mediaDescription'] = isset($_POST['mediaDescription']) ? $_POST['mediaDescription'] : '';
		$mediaArr['mediaCaption'] = isset($_POST['mediaCaption']) ? $_POST['mediaCaption'] : '';
		$mediaArr['mediaAlt'] = isset($_POST['mediaAlt']) ? $_POST['mediaAlt'] : '';
		$globalValidate = new GlobalValidation;
		$mediaArr['mediaTitle'] = $globalValidate->validateTitle($mediaArr['mediaTitle']);
		$mediaArr['mediaDescription'] = $globalValidate->validateDescription($mediaArr['mediaDescription']);
		// Update the media item.
		if(empty($globalValidate->errors)) {
			$success = $media->updateMedia($mediaArr['mediaId'], $mediaArr);
			if(empty($success)) {
				$_SESSION['errors'][] = 'An error occured while updating the file';
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			} else {
				$_SESSION['success'][] = 'File successfully updated.';
			}
		} else {
			$_SESSION['errors'] = $globalValidate->errors;
			$_SESSION['mediaFile'] = $mediaArr;
		}
		switch($_POST['mediaAction']) {
			case 'save':
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
				break;
			case 'save-close':
				header('Location: ' . HTTP_ADMIN . 'media/', TRUE, 302);
				exit;
				break;
			default:
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
				break;
		}
	} elseif(isset($_POST['mediaAction']) && $_POST['mediaAction'] === 'delete' && is_array($_POST['mediaCheck'])) {
		foreach($_POST['mediaCheck'] as $mediaId) {
			// Delete thumbnails.
			$thumbsToDel = $media->getMediaThumbs($mediaId);
			if(!empty($thumbsToDel)) {
				foreach($thumbsToDel as $thumbToDel) {
					if(file_exists($thumbToDel['thumbnail_path'])) {
						unlink($thumbToDel['thumbnail_path']);
					}
				}
			}
			// Delete media
			$mediaToDel = $media->getMedia($mediaId);
			if(file_exists($mediaToDel['media_path'])) {
				unlink($mediaToDel['media_path']);
			}
			if(file_exists($mediaToDel['media_orig_path'])) {
				unlink($mediaToDel['media_orig_path']);
			}
			// Delete database entries.
			$success[] = $media->deleteMedia($mediaId);
		}
		if(in_array('', $success)) {
			$_SESSION['errors'][] = "An error occurred while deleting media files";
		} else {
			$_SESSION['success'] = 'Media files deleted successdully';
		}
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	} elseif(isset($_POST['adminRequest'])) {
		$adminReq = json_decode($_POST['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type)) {
			// If dynamically updating an image.
			if($adminReq->type === 'media' && $adminReq->action === 'update-image') {
				$mediaToUpdate = $media->getMedia($adminReq->id);
				// Update image dimensions
				$media->updateMediaDimensions($adminReq->id, $adminReq->width, $adminReq->height);
				$imageGenerator = new ThumbnailGenerator($mediaToUpdate['media_path']);
				// If history exists execute the last history option.
				if(!empty($adminReq->history)) {
					// Go through each image edit action and perform it then saving the width and height of the edited image for proper resizing later.
					foreach($adminReq->history as $imageAction) {
						foreach($imageAction as $action => $attribs) {
							switch($action) {
								case 'c':
									$imageGenerator->targetCrop($attribs);
									$imageWidth = $imageGenerator->editWidth;
									$imageHeight = $imageGenerator->editHeight;
									break;
								case 'f':
									$imageGenerator->flipImage($attribs->f);
									$imageWidth = $imageGenerator->editWidth;
									$imageHeight = $imageGenerator->editHeight;
									break;
								case 'r':
									$imageGenerator->rotateImage($attribs->r);
									$imageWidth = $imageGenerator->editWidth;
									$imageHeight = $imageGenerator->editHeight;
									break;
								case 's':
									$imageGenerator->resizeImage($attribs->w, $attribs->h, 'auto');
									$imageWidth = $imageGenerator->editWidth;
									$imageHeight = $imageGenerator->editHeight;
									break;
							}
						}
					}
				}
				$imageTemplates = $config->loadImageTemplates();
				$imageThumbs = $media->getMediaThumbs($adminReq->id);
				$thumbTemplateIds = organizePDO($imageThumbs, 'id', 'template_id');
				$thumbInfo = pathinfo($mediaToUpdate['media_url']);
				$pathInfo = pathinfo($mediaToUpdate['media_path']);
				// Save images depending on the applyTo property.
				switch($adminReq->applyTo) {
					case 'all':
						// Save the image
						$imageGenerator->saveImage($mediaToUpdate['media_path'], $globalSettings['media_image_quality']['value']);
						// Get the pathinfo for the URL and directory path and build the new links for database insertion.
						foreach($imageTemplates as $template) {
							foreach($imageThumbs as $imageThumb) {
								// Insert a new thumbnail if it doesn't exist.
								if($template['id'] === $imageThumb['template_id']) {
									$thumbLocation = $thumbInfo['dirname'] . '/' . $thumbInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $thumbInfo['extension'];
									$thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $pathInfo['extension'];
									$thumbGenerator = new ThumbnailGenerator($mediaToUpdate['media_path']);
									$thumbGenerator->resizeImage($template['template_width'], $template['template_height'], $template['template_type']);
									$thumbGenerator->saveImage($thumbPath, $template['template_quality']);
								} elseif(!in_array($template['id'], $thumbTemplateIds)) {
									$thumbLocation = $thumbInfo['dirname'] . '/' . $thumbInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $thumbInfo['extension'];
									$thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $pathInfo['extension'];
									// Insert new thumbnail
									$media->insertMediaThumb($adminReq->id, $template['id'], $template['template_width'], $template['template_height'], $thumbLocation, $thumbPath);
									$thumbGenerator = new ThumbnailGenerator($mediaToUpdate['media_path']);
									$thumbGenerator->resizeImage($template['template_width'], $template['template_height'], $template['template_type']);
									$thumbGenerator->saveImage($thumbPath, $template['template_quality']);
								}
							}
						}
						// If the image hasn't been edited set the flag.
						if(empty($mediaToUpdate['media_edit_flag'])) $media->setEditFlag($adminReq->id, TRUE);
						break;
					case 'allNoThumb':
						// Save the image
						$imageGenerator->saveImage($mediaToUpdate['media_path'], $globalSettings['media_image_quality']['value']);
						// Get the pathinfo for the URL and directory path and build the new links for database insertion.
						foreach($imageTemplates as $template) {
							foreach($imageThumbs as $imageThumb) {
								if($template['template_name'] === "Thumbnail") {
									// Skip the thumbnail.
									continue;
								} elseif($template['id'] === $imageThumb['template_id']) {
									// Save all but thumbnail.
									$thumbLocation = $thumbInfo['dirname'] . '/' . $thumbInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $thumbInfo['extension'];
									$thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $pathInfo['extension'];
									$imageGenerator = new ThumbnailGenerator($mediaToUpdate['media_path']);
									$imageGenerator->resizeImage($template['template_width'], $template['template_height'], $template['template_type']);
									$imageGenerator->saveImage($thumbPath, $template['template_quality']);
								} elseif(!in_array($template['id'], $thumbTemplateIds)) {
									$media->insertMediaThumb($adminReq->id, $template['id'], $template['template_width'], $template['template_height'], $thumbLocation, $thumbPath);
									$thumbLocation = $thumbInfo['dirname'] . '/' . $thumbInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $thumbInfo['extension'];
									$thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $pathInfo['extension'];
									$thumbnailGenerator = new ThumbnailGenerator($mediaToUpdate['media_path']);
									$thumbnailGenerator->resizeImage($template['template_width'], $template['template_height'], $template['template_type']);
									$thumbnailGenerator->saveImage($thumbPath);
								}
								// If the image hasn't been edited set the flag.
								if(empty($mediaToUpdate['media_edit_flag'])) $media->setEditFlag($adminReq->id, TRUE);
							}
						}
						break;
					case 'thumbOnly':
						// Get the pathinfo for the URL and directory path and build the new links for database insertion.
						foreach($imageTemplates as $template) {
							if($template['template_name'] === "Thumbnail") {
								$thumbLocation = $thumbInfo['dirname'] . '/' . $thumbInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $thumbInfo['extension'];
								$thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $pathInfo['extension'];
								$imageGenerator->resizeImage($template['template_width'], $template['template_height'], $template['template_type']);
								$imageGenerator->saveImage($thumbPath);
							}
							// If the image hasn't been edited set the flag.
							if(empty($mediaToUpdate['media_edit_flag'])) $media->setEditFlag($adminReq->id, TRUE);
						}
						break;
					case 'imageOnly':
						$imageGenerator->saveImage($mediaToUpdate['media_path']);
						break;
					default:
						return FALSE;
						break;
				}
			} elseif($adminReq->type === 'media' && $adminReq->action === 'delete') {
				$mediaToDel = $media->getMedia($adminReq->id);
				$mediaThumbsToDel = $media->getMediaThumbs($adminReq->id);
			} elseif($adminReq->type === 'media' && $adminReq->action === 'restore') {
				$mediaToRestore = $media->getMedia($adminReq->id);
				$mediaInfo = pathinfo($mediaToRestore['media_path']);
				// Replace current image with the original image.
				copy($mediaToRestore['media_orig_path'], $mediaToRestore['media_path']);
				// Add/update thumbnails as appropriate.
				if(!empty($adminReq->restoreAll)) {
					$imageTemplates = $config->loadImageTemplates();
					$imageThumbs = $media->getMediaThumbs($adminReq->id);
					$thumbTemplateIds = organizePDO($imageThumbs, 'id', 'template_id');
					$thumbInfo = pathinfo($mediaToRestore['media_url']);
					$pathInfo = pathinfo($mediaToRestore['media_path']);
					foreach($imageTemplates as $template) {
						foreach($imageThumbs as $imageThumb) {
							if($template['id'] === $imageThumb['template_id']) {
								$thumbLocation = $thumbInfo['dirname'] . '/' . $thumbInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $thumbInfo['extension'];
								$thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $pathInfo['extension'];
								$thumbnailGenerator = new ThumbnailGenerator($mediaToRestore['media_path']);
								$thumbnailGenerator->resizeImage($template['template_width'], $template['template_height'], $template['template_type']);
								$thumbnailGenerator->saveImage($thumbPath, $template['template_quality']);
							} elseif(!in_array($template['id'], $thumbTemplateIds)) {
								$media->insertMediaThumb($adminReq->id, $template['id'], $template['template_width'], $template['template_height'], $thumbLocation, $thumbPath);
								$thumbLocation = $thumbInfo['dirname'] . '/' . $thumbInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $thumbInfo['extension'];
								$thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '-' . $template['template_width'] . 'X' . $template['template_height'] . '.' . $pathInfo['extension'];
								$thumbnailGenerator = new ThumbnailGenerator($mediaToRestore['media_path']);
								$thumbnailGenerator->resizeImage($template['template_width'], $template['template_height'], $template['template_type']);
								$thumbnailGenerator->saveImage($thumbPath, $template['template_quality']);
							} else {
								continue;
							}
						}
					}
				}
				// Remove the edit flag.
				$media->setEditFlag($adminReq->id, FALSE);
			} elseif($adminReq->type === 'media' && $adminReq->action === 'search-articles') {
				// Require the articles model.
				require_once DIR_ADMIN . 'model/articles/articles-main.php';
				$articles = new Articles;
				$adminReq->results = $articles->articleTitleSearch($adminReq->searchWord);
			} elseif($adminReq->type === 'media' && $adminReq->action === 'search-pages') {
				// Require the pages model.
				require_once DIR_ADMIN . 'model/pages/pages-main.php';
			} elseif($adminReq->type === 'media' && $adminReq->action === 'get-search-result') {
				if(isset($adminReq->contentToSearch) && $adminReq->contentToSearch === 'articles') {
					// Require the articles model.
					require_once DIR_ADMIN . 'model/articles/articles-main.php';
					$articles = new Articles;
					$adminReq->results = $articles->articleTitleSearch($adminReq->searchWord);
					$currentAttachments = $media->getArticleAttachment($adminReq->id, $adminReq->searchWord);
					if(!empty($currentAttachments)) {
						$adminReq->currentAttachments = $currentAttachments;
					}
				} elseif(isset($adminReq->contentToSearch) && $adminReq->contentToSearch === 'pages') {
					// Require the page model.
					require_once DIR_ADMIN . 'model/pages/pages-main.php';
					$pages = new Pages;
				}
			} elseif($adminReq->type === 'media' && $adminReq->action === 'attach-media') {
				if(is_array($adminReq->attachments)) {
					if($adminReq->attachTo === 'articles') {
						foreach($adminReq->attachments as $attachment) {
							$articleId = str_replace('articles-', '', $attachment);
							// If the atttachment doesn't exist, attach it.
							if(!$media->getArticleAttachment($adminReq->id, $articleId)) {
								$media->insertMediaAttachment($adminReq->id, $articleId, FALSE);
								$attachedArticle = $articles->loadArticleById($articleId);
								$adminReq->attached[] = array('title' => $attachedArticle['title'], 'url' => HTTP_ADMIN . 'articles/' . $articleId . '/edit');
								$adminReq->currentDate = date('m-d-Y', time());
							} else {
								$adminReq->errors = "This media file has already been attached to one or more articles.";
							}
						}
					} elseif($adminReq->attachTo === 'pages') {
						foreach($adminReq->attachments as $attachment) {
							$pageId = str_replace('pages-', '', $attachment);
							if(!$media->getArticleAttachment($adminReq->id, $articleId)) {
								$media->insertMediaAttachments($adminReq->id, FALSE, $pageId);
								$attachedArticle = $media->loadPageById($pageId);
								$adminReq->attached[] = array('title' => $page['title'], 'url' => HTTP_ADMIN . 'articles/' . $pageId . '/edit');
							} else {
								$adminReq->errors = "This media file has already been attached to one or more articles.";
							}
						}
					}
				}
			} elseif($adminReq->action === 'get-file-url') {
				$mediaFile = $media->getMedia($adminReq->id);
				$adminReq->urlFile = $mediaFile['media_url'];
			} elseif($adminReq->action === 'get-image-dimensions') {
				// Get the dimensions of a media file.
				$mediaFile = $media->getMedia($adminReq->id);
				$file_headers = @get_headers($mediaFile['media_url']);
				if($file_headers[0] !== 'HTTP/1.1 404 Not Found') {
					$imageDimensions = @getimagesize($mediaFile['media_url']);
					$adminReq->width = $imageDimensions[0];
					$adminReq->height = $imageDimensions[1];
				} else {
					die('{"jsonrpc" : "2.0", "error" : {"message": "Could not get the image dimensions because the file does not exist."}, "id" : ' . $adminReq->id . '}');
				}
			} elseif($adminReq->action === 'get-url-for-image') {
				if(!empty($adminReq->templateId)) {
					$mediaThumb = $media->getMediaThumbByTemplate((int)$adminReq->id, (int)$adminReq->templateId);
					$adminReq->thumbUrl = $mediaThumb['thumbnail_location'];
				} else {
					$mediaImage = $media->getMedia((int)$adminReq->id);
					$adminReq->mediaThumb = $mediaImage['media_url'];
				}
			} elseif($adminReq->action === 'delete-media-attachment') {
				$media->deleteMediaAttachment($adminReq->id);
			}
		}
		echo json_encode($adminReq);
		exit;
	}
}

if(isset($_GET['adminRequest'])) {
	// Get the preview image for the media image editor
	if(isset($_GET['adminRequest']) && $_GET['adminRequest'] === 'image-preview' && isset($_GET['id'])) {
		// Get the media file.
		$mediaFile = $media->getMedia((int)$_GET['id']);
		require_once DIR_SYSTEM . 'library/thumbnail-generator.php';
		// Instantiate the thumbnail generator.
		$imageGenerator = new ThumbnailGenerator($mediaFile['media_url']);
		// If history exists execute the last history option.
		if(isset($_GET['history'])) {
			$history = urldecode($_GET['history']);
			$history = json_decode($history);
			// Go through each image edit action and perform it then saving the width and height of the edited image for proper resizing later.
			foreach($history as $imageAction) {
				foreach($imageAction as $action => $attribs) {
					switch($action) {
						case 'c':
							$imageGenerator->targetCrop($attribs);
							$imageWidth = $imageGenerator->editWidth;
							$imageHeight = $imageGenerator->editHeight;
							break;
						case 'f':
							$imageGenerator->flipImage($attribs->f);
							$imageWidth = $imageGenerator->editWidth;
							$imageHeight = $imageGenerator->editHeight;
							break;
						case 'r':
							$imageGenerator->rotateImage($attribs->r);
							$imageWidth = $imageGenerator->editWidth;
							$imageHeight = $imageGenerator->editHeight;
							break;
						case 's':
							$imageGenerator->resizeImage($attribs->w, $attribs->h, 'auto');
							$imageWidth = $imageGenerator->editWidth;
							$imageHeight = $imageGenerator->editHeight;
							break;
					}
				}
			}
		}
		// Get dimensions
		$imageSize = getimagesize($mediaFile['media_url']);
		$imageWidth = !isset($imageWidth) ? $imageSize[0] : $imageWidth;
		$imageHeight = !isset($imageHeight) ? $imageSize[1] : $imageHeight;
		if($imageWidth > 400 || $imageHeight > 400) {
			// Resize and save thumbnail from source image.
			$imageGenerator->resizeImage(400, 400, 'auto');
		} else {
			$imageGenerator->resizeImage($imageWidth, $imageHeight, 'exact');
		}
		$imageGenerator->ajaxImage();
	} elseif(isset($_GET['adminRequest']) && $_GET['adminRequest'] === 'thumb-preview' && isset($_GET['id'])) {
		$mediaFile = $media->getMedia((int)$_GET['id']);
		if(empty($mediaFile['media_thumb_url'])) {
			die('{"jsonrpc" : "2.0", "error" : {"message": "The filename cannot be empty when using get_headers()."}, "id" : ' . (int)$_GET['id'] . '}');
		} else {
			$fileHeaders = @get_headers($mediaFile['media_thumb_url']);
		}
		if($fileHeaders[0] === 'HTTP/1.1 404 Not Found') {
			// Thumbnail doesn't exist.
			exit;
		} else {
			// Thumbnail exists.
			$thumbSize = getimagesize($mediaFile['media_thumb_url']);
		}
		$thumbWidth = $thumbSize[0];
		$thumbHeight = $thumbSize[1];
		require_once DIR_SYSTEM . 'library/thumbnail-generator.php';
		$thumbnailGenerator = new ThumbnailGenerator($mediaFile['media_thumb_url']);
		$thumbnailGenerator->resizeImage($thumbWidth, $thumbHeight, 'auto');
		$thumbnailGenerator->ajaxImage();
	}
	exit;
}

function mediaCleanUp() {
	if(isset($_SESSION['mediaFile'])) unset($_SESSION['mediaFile']);
}

function getExtensionIcon($fileExtension) {
	
	switch($fileExtension) {
		case 'aac': case 'AAC':
			$extIcon = 'sprite-aac';
			break;
		case 'ai': case 'AI':
			$extIcon = 'sprite-ai';
			break;
		case 'aiff': case 'AIFF':
			$extIcon = 'sprite-aiff';
			break;
		case 'avi': case 'AVI':
			$extIcon = 'sprite-avi';
			break;
		case 'bmp': case 'BMP':
			$extIcon = 'sprite-bmp';
			break;
		case 'c': case 'C':
			$extIcon = 'sprite-c';
			break;
		case 'cpp': case 'CPP':
			$extIcon = 'sprite-cpp';
			break;
		case 'css': case 'CSS':
			$extIcon = 'sprite-css';
			break;
		case 'dat': case 'DAT':
			$extIcon = 'sprite-dat';
			break;
		case 'dmg': case 'DMG':
			$extIcon = 'sprite-dmg';
			break;
		case 'doc': case 'DOC': case 'docx': case 'DOCX':
			$extIcon = 'sprite-doc';
			break;
		case 'dotx': case 'DOTX':
			$extIcon = 'sprite-dotx';
			break;
		case 'dwg': case 'DWG':
			$extIcon = 'sprite-dwg';
			break;
		case 'dxf': case 'DXF':
			$extIcon = 'sprite-dxf';
			break;
		case 'eps': case 'EPS':
			$extIcon = 'sprite-eps';
			break;
		case 'exe': case 'EXE':
			$extIcon = 'sprite-exe';
			break;
		case 'flv': case 'FLV':
			$extIcon = 'sprite-flv';
			break;
		case 'gif': case 'GIF':
			$extIcon = 'sprite-gif';
			break;
		case 'h': case 'H':
			$extIcon = 'sprite-h';
			break;
		case 'hpp': case 'HPP':
			$extIcon = 'sprite-hpp';
			break;
		case 'htm': case 'HTM': case 'html': case 'HTML':
			$extIcon = 'sprite-html';
			break;
		case 'ics': case 'ICS':
			$extIcon = 'sprite-ics';
			break;
		case 'iso': case 'ISO':
			$extIcon = 'sprite-iso';
			break;
		case 'java': case 'JAVA':
			$extIcon = 'sprite-java';
			break;
		case 'jpeg': case 'JPEG': case 'jpg': case 'JPG':
			$extIcon = 'sprite-jpg';
			break;
		case 'key': case 'KEY':
			$extIcon = 'sprite-key';
			break;
		case 'mid': case 'MID':
			$extIcon = 'sprite-mid';
			break;
		case 'mp3': case 'MP3':
			$extIcon = 'sprite-mp3';
			break;
		case 'mp4': case 'MP4':
			$extIcon = 'sprite-mp4';
			break;
		case 'mpg': case 'MPG':
			$extIcon = 'sprite-mpg';
			break;
		case 'odf': case 'ODF':
			$extIcon = 'sprite-odf';
			break;
		case 'ods': case 'ODS':
			$extIcon = 'sprite-ods';
			break;
		case 'odt': case 'ODT':
			$extIcon = 'sprite-odt';
			break;
		case 'otp': case 'OTP':
			$extIcon = 'sprite-otp';
			break;
		case 'ots': case 'OTS':
			$extIcon = 'sprite-ots';
			break;
		case 'ott': case 'OTT':
			$extIcon = 'sprite-ott';
			break;
		case 'pdf': case 'PDF':
			$extIcon = 'sprite-pdf';
			break;
		case 'php': case 'PHP':
			$extIcon = 'sprite-php';
			break;
		case 'png': case 'PNG':
			$extIcon = 'sprite-png';
			break;
		case 'ppt': case 'PPT':
			$extIcon = 'sprite-ppt';
			break;
		case 'psd': case 'PSD':
			$extIcon = 'sprite-psd';
			break;
		case 'py': case 'PY':
			$extIcon = 'sprite-py';
			break;
		case 'qt': case 'QT':
			$extIcon = 'sprite-qt';
			break;
		case 'rar': case 'RAR':
			$extIcon = 'sprite-rar';
			break;
		case 'rb': case 'RB':
			$extIcon = 'sprite-rb';
			break;
		case 'rtf': case 'RTF':
			$extIcon = 'sprite-rtf';
			break;
		case 'sql': case 'SQL':
			$extIcon = 'sprite-sql';
			break;
		case 'tga': case 'TGA':
			$extIcon = 'sprite-tga';
			break;
		case 'tgz': case 'TGZ':
			$extIcon = 'sprite-tgz';
			break;
		case 'tiff': case 'TIFF':
			$extIcon = 'sprite-tiff';
			break;
		case 'txt': case 'TXT':
			$extIcon = 'sprite-txt';
			break;
		case 'wav': case 'WAV':
			$extIcon = 'sprite-wav';
			break;
		case 'xls': case 'XLS':
			$extIcon = 'sprite-xls';
			break;
		case 'xlsx': case 'XLSX':
			$extIcon = 'sprite-xlsx';
			break;
		case 'xml': case 'XML':
			$extIcon = 'sprite-xml';
			break;
		case 'yml': case 'YML':
			$extIcon = 'sprite-yml';
			break;
		case 'zip': case 'ZIP':
			$extIcon = 'sprite-zip';
			break;
		default:
			$extIcon = 'sprite-_blank';
			break;
	}
	
	return $extIcon;
}
