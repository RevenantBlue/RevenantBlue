<?php
namespace RevenantBlue\Admin;
use RevenantBlue\Pager;
use RevenantBlue\SimplePaginator;
use RevenantBlue\ThumbnailGenerator;
use \stdClass;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/photogallery/photogallery-validation.php';
require_once DIR_ADMIN . 'model/photogallery/photogallery-main.php';
require_once DIR_SYSTEM . 'library/simple-paginator.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
require_once DIR_SYSTEM . 'library/detect-browser.php';

$browser = getBrowser();

// Initialize the photoGallery object
$gallery = new photoGallery;

// Initalize the image validation class
$validate = new GalleryValidation;

// Initialize the pager
$pager = new pager;

function displayThumb($albumAlias, $imageAlias, $adminThumb = FALSE) {
	if($adminThumb) {
		return HTTP_GALLERY . hsc($albumAlias . "/thumbs/admin-thumb/" . $imageAlias);
	} else {
		return HTTP_GALLERY . hsc($albumAlias . "/thumbs/" . $imageAlias);
	}
}

// Load requirements for the photogallery overview
if(isset($_GET['controller'])) {
	if($_GET['controller'] === 'photo-gallery') {
		// Set the limit
		$pager->limit = 10;
		// Set the article list to display new search results.
		if(!empty($_POST['submitAlbumSearch'])) {
			header('Location: ' . HTTP_ADMIN . "photogallery?search=" . urlencode($_POST['albumToSearch']) . "&order=title&sort=asc", true, 302);
			exit;
		} elseif(isset($_POST['stateFilter'])) {
			header('Location: ' . HTTP_ADMIN . "photogallery?state=" . urlencode($_POST['stateFilter']) . "&order=title&sort=asc", true, 302);
			exit;
		} elseif(isset($_POST['featuredFilter'])) {
			header('Location: ' . HTTP_ADMIN . 'photogallery?featured=' . urlencode($_POST['featuredFilter']) . '&order=title&sort=asc', true, 302);
			exit;
		} elseif(isset($_GET['search']) && isset($_GET['order']) && isset($_GET['sort'])) {
			if(in_array($_GET['order'], $gallery->whiteList)) {
				$pager->totalRecords = $gallery->countAlbumsBySearch($_GET['search']);
				$pager->paginate();
				$albumList = $gallery->loadAlbumsBySearch($pager->limit, $pager->offset, $_GET['search'], $_GET['order'], $_GET['sort']);
			} else {
				header('Location: ' . HTTP_ADMIN . 'photogallery/', true, 302);
				exit;
			}
		} elseif(isset($_GET['state'])) {
			if(in_array($_GET['order'], $gallery->whiteList)) {
				$pager->totalRecords = $gallery->countAlbumsByState($_GET['state']);
				$pager->paginate();
				$albumList = $gallery->loadAlbumsByState($pager->limit, $pager->offset, $_GET['state'], $_GET['order'], $_GET['sort']);
			} else {
				header('Location: ' . HTTP_ADMIN . 'photogallery', true, 302);
				exit;
			}
		} elseif(isset($_GET['featured'])) {
			if(in_array($_GET['order'], $gallery->whiteList)) {
				$pager->totalRecords = $gallery->countAlbumsByFeatured($_GET['featured']);
				$pager->paginate();
				$albumList = $gallery->loadAlbumsByFeatured($pager->limit, $pager->offset, $_GET['featured'], $_GET['order'], $_GET['sort']);
			} else {
				header('Location: ' . HTTP_ADMIN . 'photogallery', true, 302);
				exit;
			}
		} else {
			$pager->totalRecords = $gallery->countAlbums();
			if(isset($_GET['order']) && isset($_GET['sort']) && in_array($_GET['order'], $gallery->whiteList)) {
				$pager->paginate();
				$albumList = $gallery->loadAlbums($pager->limit, $pager->offset, $_GET['order'], $_GET['sort']);
			} else {
				$pager->paginate();
				$albumList = $gallery->loadAlbums($pager->limit, $pager->offset, 'order_of_item', 'ASC');
			}
		}
		
		$pager->displayItemsPerPage();
		$pager->menu = str_replace('index.php?page=', BACKEND_NAME . '/photogallery/p', $pager->menu);
		$pager->menu = str_replace('&amp;controller=admin-photogallery', '', $pager->menu);
		$pager->limitMenu = str_replace('index.php?page=', BACKEND_NAME . '/photogallery/p', $pager->limitMenu);
		$pager->limitMenu = str_replace('&amp;controller=admin-photogallery', '', $pager->limitMenu);

		$backendUsers = $users->getUsersWithBackendAccess();

		$templates = $gallery->getTemplates();
		$templateTypes = array('exact', 'portrait', 'landscape', 'crop', 'auto');
		$optionsForPage = $users->getOptionsByGroup('photogallery');
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'photogallery');
		
		// If the user changes the number of records to show, store the change in the personal options table, the only argument is the option's ID.
		setNumToShow(104);
	} elseif($_GET['controller'] === 'photo-gallery-album-profile') {
		// Load the profile for the photogallery album.
		$optionsForPage = $users->getOptionsByGroup('photogallery profile');
		$backendUsers = $users->getUsersWithBackendAccess();
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'photogallery profile');
		$templates = $gallery->getTemplates();
		$templateTypes = array('exact', 'portrait', 'landscape', 'crop', 'auto');
	} elseif(($_GET['controller'] === 'photo-gallery-album-profile' || $_GET['controller'] === 'photo-gallery-album-images') && isset($_GET['album'])) {
		// Load the album and filter the name to a screen friendly format.
		$albumId = $gallery->getIdByAlias($_GET['album']);
		$album = $gallery->getGalleryItem($albumId);
		$photos = $gallery->loadPhotos($albumId);
		// Load the photogallery album images user options.
		if($_GET['controller'] === 'photogallery-album-images') {
			$optionsForPage = $users->getOptionsByGroup('photogallery images');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'photogallery images');
		}
	} elseif(isset($_GET['album']) && $_GET['controller'] === 'photo-gallery-image-profile') {
		
		$albums = $gallery->getRootNodes();
		$album = $gallery->getGalleryItemByAlias($_GET['album']);
		$albumId = $gallery->getIdByAlias($_GET['album']);
		$optionsForPage = $users->getOptionsByGroup('photogallery image');
		$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'photogallery image');
		if(!empty($albumId)) {
			$albumAlias = $_GET['album'];
		}
	}

	// Load the individual photo for manipulation
	if(isset($_GET['photo']) && isset($_GET['album'])) {
		$photoRequest = $_GET['photo'];
		$photoId = $gallery->getIdByAlias($photoRequest);
		$albumId = $gallery->getParentId($photoId);
		$album = $gallery->getGalleryItemByAlias($_GET['album']);
		
		$currentPhoto = $gallery->getGalleryItemByAlias($_GET['photo']);
		
		// Get the number of photos for the album.
		$numOfPhotos = $gallery->getNumofPhotos($albumId);
		// Iniitialize the simple pager.
		$pager = new SimplePaginator;
		
		$pager->totalRecords = $numOfPhotos;
		$pager->currentPage = (int)$currentPhoto['order_of_item'];
		$pager->paginate();
		
		// Load the previous and next image name for the pagination system to work correctly.
		$previousImage = $pager->currentPage - 1;
		$nextImage = $pager->currentPage + 1;
		if($previousImage == 0) {
			$previousImage = $numOfPhotos;
		}
		if($nextImage > $numOfPhotos) {
			$nextImage = 1;
		}
		$previousImage = $gallery->getPhotoByOrder($previousImage, $album['id']);
		$nextImage = $gallery->getPhotoByOrder($nextImage, $album['id']);
		
		// Replace the urls to work with mod rewrite.
		
		$pager->prevLink = str_replace('index.php?page=', BACKEND_NAME . '/photogallery/' . $album['alias'] . '/' . $previousImage . '/p', $pager->prevLink);
		$pager->prevLink = str_replace('&amp;section=photo-gallery&amp;album=' .  $album['alias'], '', $pager->prevLink);
		$pager->prevLink = str_replace('&amp;photo=' . $currentPhoto['alias'], '', $pager->prevLink);

		$pager->nextLink = str_replace('index.php?page=', BACKEND_NAME . '/photogallery/' . $album['alias'] . '/' . $nextImage . '/p', $pager->nextLink);
		$pager->nextLink = str_replace('&amp;section=photo-gallery&amp;album=' . $album['alias'], '', $pager->nextLink);
		$pager->nextLink = str_replace('&amp;photo=' .  $currentPhoto['alias'], '', $pager->nextLink);

	}
	
	// Load an album for editing.
	if(isset($_GET['album']) && $_GET['controller'] == 'photo-gallery-album-profile') {
		$album = $gallery->getGalleryItemByAlias($_GET['album']);
	}
}


if($_SERVER['REQUEST_METHOD'] == 'POST') {

	if(isset($_POST['galleryAction'])) {
		
		if($_POST['galleryAction'] === 'edit-album') {
			// Edit album request
			$albumAlias = $gallery->getAliasById($_POST['albumCheck'][0]);
			header('Location: ' . HTTP_ADMIN . 'photogallery/' . $albumAlias . '/edit/', TRUE, 302);
			exit;
		} elseif($_POST['galleryAction'] === 'edit-image') {
			// Edit image request
			header('Location: ' . HTTP_ADMIN . 'photogallery/' . $_POST['imageCheck'][0] . '/edit/', TRUE, 302);
			exit;
		} elseif($_POST['galleryAction'] === 'save-album' || $_POST['galleryAction'] === 'save-close-album' || $_POST['galleryAction'] === 'save-new-album') {
			// Process request to create and update an album.
			// Build album array.
			$album['id'] = isset($_POST['id']) ? $_POST['id'] : '';
			$album['title'] = isset($_POST['title']) ? $_POST['title'] : '';
			$album['alias'] = isset($_POST['alias']) ? $_POST['alias'] : '';
			$album['dateCreated'] = isset($_POST['dateCreated']) ? $_POST['dateCreated'] : '';
			$album['createdBy'] = isset($_POST['createdBy']) ? $_POST['createdBy'] : '';
			$album['description'] = isset($_POST['description']) ? $_POST['description'] : '';
			$album['featured'] = isset($_POST['featured']) ? $_POST['featured'] : '';
			$album['state'] = isset($_POST['state']) ? $_POST['state'] : '';
			$album['metaDescription'] = isset($_POST['metaDescription']) ? $_POST['metaDescription'] : '';
			$album['metaKeywords'] = isset($_POST['metaKeywords']) ? $_POST['metaKeywords'] : '';
			$album['metaRobots'] = isset($_POST['metaRobots']) ? $_POST['metaRobots'] : '';
			$album['metaAuthor'] = isset($_POST['metaAuthor']) ? $_POST['metaAuthor'] : '';
			$album['template'] = isset($_POST['template']) ? $_POST['template'] : '';
			$album['image'] = isset($_POST['image']) ? $_POST['image'] : '';
			$album['imageAlt'] = isset($_POST['imageAlt']) ? $_POST['imageAlt'] : '';
			$album['imagePath'] = isset($_POST['imagePath']) ? $_POST['imagePath'] : '';

			// Instantiate the article validation class and validate the article array.
			$albumValidate = new GalleryValidation($album);
			// If a new album has been submitted.
			if(empty($album['id']) && empty($albumValidate->errors)) {
				$newAlbum = TRUE;
				$newAlbumId = $gallery->insertGalleryNode($albumValidate->album);
				if(!empty($newAlbumId)) {
					$_SESSION['success'] = "Album created successfully.";
				} else {
					$_SESSION['errors'][] = "An error occured while inserting the album into the databse.";
				}
			}
			// If an edited article is being submitted.
			if(!empty($album['id']) && empty($albumValidate->errors)) {
				$editAlbum = TRUE;
				// Process the entry for the database.
				$updateSuccess = $gallery->updateAlbum($albumValidate->album);
				if(!empty($updateSuccess)) {
					$_SESSION['success'] = "Album updated successfully.";
				}
			}
			if(!empty($albumValidate->errors) || !empty($_SESSION['errors'])) {
				$_SESSION['errors'] = $albumValidate->errors;
				$_SESSION['album'] = $albumValidate->album;
			} else {
				switch($_POST['galleryAction']) {
					case 'save-album':
						if(isset($newAlbum) && !empty($newAlbumId)) {
							header('Location: ' . HTTP_ADMIN . 'photogallery/' . $gallery->getAliasById($newAlbumId) . '/edit', TRUE, 302);
							exit;
						} elseif(isset($editAlbum)) {
							header('Location: ' . HTTP_ADMIN . 'photogallery/' . $gallery->getAliasById($albumValidate->id) . '/edit', TRUE, 302);
							exit;
						}
						break;
					case 'save-close-album':
						header('Location: ' . HTTP_ADMIN . 'photogallery/', TRUE, 302);
						exit;
						break;
					case 'save-new-album':
						header('Location: ' . HTTP_ADMIN . 'photogallery/new/', TRUE, 302);
						exit;
						break;
					default:
						header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
						exit;
						break;
				}
			}
		} elseif($_POST['galleryAction'] === 'save-image' || $_POST['galleryAction'] === 'save-close-image') {
			$image['id'] = isset($_POST['id']) ? $_POST['id'] : '';
			$image['title'] = isset($_POST['title']) ? $_POST['title'] : '';
			$image['state'] = isset($_POST['state']) ? $_POST['state'] : '';
			$image['featured'] = isset($_POST['featured']) ? $_POST['featured'] : '';
			$image['description'] = isset($_POST['description']) ? $_POST['description'] : '';
			$image['caption'] = isset($_POST['caption']) ? $_POST['caption'] : '';
			$image['alt'] = isset($_POST['alt']) ? $_POST['alt'] : '';

			// Validate the photo attributes.
			$photoValidate = new GalleryValidation($image, TRUE);
			// Update the photo.
			$updateSuccess = $gallery->updatePhoto($photoValidate->photo);
			
			if(empty($updateSuccess) || !empty($photoValidate->errors)) {
				$_SESSION['errors'] = $photoValidate->errors;
				$_SESSION['photo'] = $imageArr;
			}
			if(empty($photoValidate->errors) || empty($_SESSION['errors'])) {
				$_SESSION['success'] = 'Image updated successfully';
			}
			switch($_POST['galleryAction']) {
				case 'save-image':
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
				case 'save-close-image':
					header('Location: ' . HTTP_ADMIN . 'photogallery/' . $gallery->getAliasById($photoValidate->id), TRUE, 302);
					exit;
					break;
				default:
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
					break;
			}
		} elseif($_POST['galleryAction'] === 'submit-image-uploads' && !empty($_POST['uploads']) && is_array($_POST['uploads'])) {
			// Updating image data from the plupload uploader.
			foreach($_POST['uploads'] as $upload) {
				$image['id'] = isset($upload['id']) ? $upload['id'] : '';
				$image['title'] = isset($upload['title']) ? $upload['title'] : '';
				$image['description'] = isset($upload['description']) ? $upload['description'] : '';
				$image['caption'] = isset($upload['caption']) ? $upload['caption'] : '';
				$image['alt'] = isset($upload['alt']) ? $upload['alt'] : '';
				$image['state'] = 1;
				$image['featured'] = 0;
				$globalValidate = new GlobalValidation;
				$image['title'] = $globalValidate->validateTitle($imageArr['title']);
				$image['description'] = $globalValidate->validateDescription($imageArr['description']);
				$updateImage = $gallery->updatePhoto($imageArr);
			}
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		} elseif($_POST['galleryAction'] === 'delete-album' && aclVerify('administer photo gallery')) {
			
			// Delete an album.
			foreach($_POST['albumCheck'] as $id) {
				$albumToDelete = $gallery->getGalleryItem($id);
				// Delete the image and its thumbnails.
				if(is_file(DIR_GALLERY  . $albumToDelete['image'])) {
					@unlink(DIR_GALLERY . $albumToDelete['image']);
				}
				if(is_file(DIR_GALLERY . 'thumb-' . $albumToDelete['image'])) {
					@unlink(DIR_GALLERY . 'thumb-' . $albumToDelete['image']);
				}
				if(is_file(DIR_GALLERY . 'admin-thumb-' . $albumToDelete['image'])) {
					@unlink(DIR_GALLERY . 'admin-thumb-' . $albumToDelete['image']);
				}
				
				// Update the order of items in the database.
				$gallery->setOrderOfItemOnDelete($id);
				// Delete each photo.
				$photosForAlbum = $gallery->loadPhotos($id);
				foreach($photosForAlbum as $photoToDelete) {
					// Delete the photo.
					if(file_exists(DIR_GALLERY . $photoToDelete['image'])) {
						@unlink(DIR_GALLERY . $photoToDelete['image']);
					}
					if(file_exists(DIR_GALLERY . 'thumb-' . $photoToDelete['image'])) {
						@unlink(DIR_GALLERY . 'thumb-' . $photoToDelete['image']);
					}
					if(file_exists(DIR_GALLERY . 'admin-thumb-' . $photoToDelete['image'])) {
						@unlink(DIR_GALLERY . 'admin-thumb-' . $photoToDelete['image']);
					}
					
					$deleteSuccess[] = $gallery->deletePhotoNode($photoToDelete['id']);
					$deleteSuccess[] = $gallery->deleteGalleryItem($photoToDelete['id']);
				}
				// Delete the album and all of its images.
				$deletedSuccess[] = $gallery->deleteAlbumNode($id);
				$deleteSuccess[] = $gallery->deleteGalleryItem($id);
				if(in_array(NULL, $deleteSuccess)) {
					$_SESSION['errors'] = "An error occured while deleting one or more albums.";
				}
				if(in_array(1, $deleteSuccess)) {
					$_SESSION['success'] = "Albums successfully deleted";
				}
			}
			// Reload the page to refelect the changes.
			header('Location: ' . HTTP_ADMIN . 'photogallery', TRUE, 302);
			exit;
		} elseif($_POST['galleryAction'] === 'delete-image') {
			// Delete a photo
			foreach($_POST['imageCheck'] as $key => $imageId) {
				$albumIdForPhoto = $gallery->getParentId($imageId);
				$albumForPhoto = $gallery->getGalleryItem($albumIdForPhoto);
				$photoDel = $gallery->getPhoto($imageId);
				$photoDelInfo = pathinfo($photoDel['image']);
				// Delete the image and its thumbnails.
				if(is_file(DIR_GALLERY  . $photoDel['image'])) {
					@unlink(DIR_GALLERY . $photoDel['image']);
				}
				if(is_file(DIR_GALLERY . 'thumbs-' . $photoDel['image'])) {
					@unlink(DIR_GALLERY . 'thumbs-' . $photoDel['image']);
				}
				if(is_file(DIR_GALLERY . 'admin-thumb-' . $photoDel['image'])) {
					@unlink(DIR_GALLERY . 'admin-thumb-' . $photoDel['image']);
				}
				
				// Update the order of items in the database.
				$gallery->setOrderOfItemOnDelete($photoDel['id'], $albumForPhoto['id']);
				// Delete the photo.
				$deleteSuccess[] = $gallery->deletePhotoNode($imageId);
				$deleteSuccess[] = $gallery->deleteGalleryItem($imageId);
				if(in_array(NULL, $deleteSuccess)) {
					$_SESSION['errors'] = "An error occured while deleting one or more images.";
				}
				if(in_array(1, $deleteSuccess)) {
					$_SESSION['success'] = "The image(s) were deleted successfully";
				}
			}
			header("Location: " . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['adminRequest'])) {
		// Handle all photogallery AJAX requests with JSON.
		$adminReq = json_decode($_POST['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type)) {
			if($adminReq->type == 'gallery-album' || $adminReq->type == 'gallery-image') {
				for($x=0; $x < count($adminReq->ids); $x++) {
					if(is_numeric($adminReq->ids[$x])) {
						if($adminReq->action == "publish") {
							$gallery->setState($adminReq->ids[$x], 1);
							$adminReq->publish[$x] = $gallery->getState($adminReq->ids[$x]);
						} elseif($adminReq->action == "unpublish") {
							$gallery->setState($adminReq->ids[$x], 0);
							$adminReq->publish[$x] = $gallery->getState($adminReq->ids[$x]);
						} elseif($adminReq->action == "featured") {
							$gallery->setFeatured($adminReq->ids[$x], 1);
							$adminReq->featured[$x] = $gallery->getFeatured($adminReq->ids[$x]);
						} elseif($adminReq->action == "remove-featured") {
							$gallery->setFeatured($adminReq->ids[$x], 0);
							$adminReq->featured[$x] = $gallery->getFeatured($adminReq->ids[$x]);
						}
					}
				}
			} elseif($adminReq->type === 'album' && $adminReq->action == 'reorder') {
				// Prepare the serialized order string for iteration and database entry.
				$albumOrder = str_replace('album-overview[]=', '', $adminReq->order);
				$albumOrder = str_replace('album_', '', $albumOrder);
				$albumOrder = explode('&', $albumOrder);
				array_shift($albumOrder);
				$order = 1;
				for($x = 0; $x < count($albumOrder); $x++) {
					$gallery->updateOrderOfItem($albumOrder[$x], $order);
					$order++;
				}
			// Album template requests.
			} elseif($adminReq->type === 'gallery' && $adminReq->action === 'new-template') {
				$newId = $gallery->insertTemplate($adminReq->templateName, $adminReq->thumbWidth, $adminReq->thumbHeight, $adminReq->imageWidth, $adminReq->imageHeight, $adminReq->templateType);
				$adminReq->id = $newId;
			} elseif($adminReq->type === 'gallery' && $adminReq->action === 'save-template') {
				$update = $gallery->updateTemplate($adminReq->id, $adminReq->thumbWidth, $adminReq->thumbHeight, $adminReq->imageWidth, $adminReq->imageHeight, $adminReq->templateType);
				$adminReq->update = $update;
			} elseif($adminReq->type === 'gallery' && $adminReq->action === 'delete-template') {
				$delete = $gallery->deleteTemplate($adminReq->id);
			} elseif($adminReq->type === 'album-image' && $adminReq->action == 'reorder') {
				// Prepare the serialized order string for iteration and database entry.
				$imageOrder = str_replace('photoList[]=', '', $adminReq->order);
				$imageOrder = explode('&', $imageOrder);
				$order = 1;
				// Iterate through the imageOrder array and update the order for each image id.
				for($x = 0; $x < count($imageOrder); $x++) {
					$gallery->updateOrderOfItem($imageOrder[$x], $order);
					$order++;
				}
			} elseif($adminReq->action === 'delete-album-image') {
				if(!empty($adminReq->id)) {
					// Get category info in order to delete the physical image file.
					$album = $gallery->getGalleryItem($adminReq->id);
					
					// Delete the physical album image as well as the database url/alt/path entries.
					if(!empty($album)) {
						if(file_exists($album['image_path'])) {
							if(file_exists(DIR_GALLERY . $album['image'])) {
								@unlink(DIR_GALLERY . $album['image']);
							}
							if(file_exists(DIR_GALLERY . 'thumb-' . $album['image'])) {
								@unlink(DIR_GALLERY . 'thumb-' . $album['image']);
							}
							if(file_exists(DIR_GALLERY . 'admin-thumb-' . $album['image'])) {
								@unlink(DIR_GALLERY . 'admin-thumb-' . $album['image']);
							}
							$gallery->setImage($adminReq->id, '', '');
							$adminReq->success = TRUE;
						}
					}
				} elseif(!empty($adminReq->imagePath)) {
					if(file_exists($adminReq->imagePath)) {
						$imageInfo = pathinfo($adminReq->imagePath);
						if($imageInfo['dirname'] === DIR_IMAGE . 'photogallery') {
							if(file_exists(DIR_GALLERY . $imageInfo['basename'])) {
								@unlink(DIR_GALLERY . $imageInfo['basename']);
							}
							if(file_exists(DIR_GALLERY . 'thumb-' . $imageInfo['basename'])) {
								@unlink(DIR_GALLERY . 'thumb-' . $imageInfo['basename']);
							}
							if(file_exists(DIR_GALLERY . 'admin-thumb-' . $imageInfo['basename'])) {
								@unlink(DIR_GALLERY . 'admin-thumb-' . $imageInfo['basename']);
							}
							$adminReq->success = TRUE;
						}
					}
				}
			}
		}
		echo json_encode($adminReq);
		exit;
	} elseif(isset($_GET['adminRequest'])) {
		// Special ajax request for the article image upload. Image content sent via POST and the adminReq via GET.
		$adminReq = json_decode($_GET['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type === 'gallery') {
			if(aclVerify('administer photo gallery')) {
				if($adminReq->action === 'upload-album-image') {
					$width = !empty($adminReq->width) ? (int)$adminReq->width : '';
					$height = !empty($adminReq->height) ? (int)$adminReq->height : '';
					
					$imageQuality = $globalSettings['media_image_quality']['value'];
					
					// Upload the new image.
					$adminRes = pluploadImage(DIR_GALLERY, $width, $height, HTTP_GALLERY, 'album-');
					
					if(!empty($adminRes) && is_object($adminRes)) {
						if(!empty($adminReq->id) && $adminReq->id !== 'undefined') {
							// Get the template
							$template = $gallery->getTemplate($adminReq->id);
							// Get the album
							$album = $gallery->getGalleryItem($adminReq->id);
							// Get the previous image.
							$prevImage = $gallery->getImage($adminReq->id);
							// Delete the previous image.
							if(!empty($prevImage['image_path']) && file_exists($prevImage['image_path'])) {
								if(file_exists(DIR_GALLERY . $prevImage['image'])) {
									@unlink(DIR_GALLERY . $prevImage['image']);
								}
								if(file_exists(DIR_GALLERY . 'thumb-' . $prevImage['image'])) {
									@unlink(DIR_GALLERY . 'thumb-' . $prevImage['image']);
								}
								if(file_exists(DIR_GALLERY . 'admin-thumb-' . $prevImage['image'])) {
									@unlink(DIR_GALLERY . 'admin-thumb-' . $prevImage['image']);
								}
							}
							// Make thumbnail
							$thumbnailGenerator = new ThumbnailGenerator($adminRes->imagePath);
							$thumbnailGenerator->resizeImage((int)$template['thumbnail_width'], (int)$template['thumbnail_height'], $template['type']);
							$thumbnailGenerator->saveImage(DIR_GALLERY . 'thumb-' . $adminRes->fileName, $imageQuality);
							
							$thumbnailGenerator = new ThumbnailGenerator($adminRes->imagePath);
							$thumbnailGenerator->resizeImage(175, 125, 'crop');
							$thumbnailGenerator->saveImage(DIR_GALLERY . 'admin-thumb-' . $adminRes->fileName, $imageQuality);
							
							// Update the article with the new image.
							$imageUpdated = $gallery->setImage($adminReq->id, $adminRes->fileName, $adminRes->imagePath);
						} elseif(empty($adminReq->id)) {
							if(!empty($adminReq->imagePath)) {
								$imageInfo = pathinfo($adminReq->imagePath);
								if($imageInfo['dirname'] === DIR_GALLERY) {
									if(file_exists(DIR_GALLERY . $imageInfo['basename'])) {
										@unlink(DIR_GALLERY . $imageInfo['basename']);
									}
									if(file_exists(DIR_GALLERY . 'thumb-' . $imageInfo['basename'])) {
										@unlink(DIR_GALLERY . 'thumb-' . $imageInfo['basename']);
									}
									if(file_exists(DIR_GALLERY . 'admin-thumb-' . $imageInfo['basename'])) {
										@unlink(DIR_GALLERY . 'admin-thumb-' . $imageInfo['basename']);
									}
								}
							}
							
							if(!empty($adminReq->template)) {
								$template = $gallery->getTemplateById((int)$adminReq->template);
							
								// Make thumbnail
								$thumbnailGenerator = new ThumbnailGenerator($adminRes->imagePath);
								$thumbnailGenerator->resizeImage((int)$template['thumbnail_width'], (int)$template['thumbnail_height'], $template['type']);
								$thumbnailGenerator->saveImage(DIR_IMAGE . 'photogallery/' . 'thumb-' . $adminRes->fileName, $imageQuality);
								
								$thumbnailGenerator = new ThumbnailGenerator($adminRes->imagePath);
								$thumbnailGenerator->resizeImage(175, 125, 'crop');
								$thumbnailGenerator->saveImage(DIR_IMAGE . 'photogallery/' . 'admin-thumb-' . $adminRes->fileName, $imageQuality);
							}
						}
						
						echo json_encode($adminRes);
						exit;
					}
				} elseif($adminReq->action === 'upload-images') {
					if(!empty($adminReq->albumId) && !empty($adminReq->albumAlias)) {
						pluploadAlbumImages($adminReq->albumId, $adminReq->albumAlias);
					}
				}
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
		$imageFile = $gallery->getPhoto((int)$_GET['id']);
		require_once DIR_SYSTEM . 'library/thumbnail-generator.php';
		// Get dimensions
		$imageSize = getimagesize($imageFile['image_path']);
		$imageWidth = !isset($imageWidth) ? $imageSize[0] : $imageWidth;
		$imageHeight = !isset($imageHeight) ? $imageSize[1] : $imageHeight;
		$imageGenerator = new Thumbnailgenerator($imageFile['image_path']);
		if($imageWidth > 400 || $imageHeight > 400) {
			// Resize and save thumbnail from source image.
			$imageGenerator->resizeImage(400, 400, 'auto');
		}
		$imageGenerator->ajaxImage();
	}
}

function galleryCleanUp() {
	if(isset($_SESSION['photo'])) unset($_SESSION['photo']);
	if(isset($_SESSION['album'])) unset($_SESSION['album']);
}

function pluploadAlbumImages($albumId, $albumAlias) {
	
	global $gallery;
	global $globalSettings;
	
	// HTTP headers for no cache etc
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	// Load the global validation class.
	$globalValidation = new GlobalValidation;
	
	$uploadDirectory = DIR_IMAGE . 'photogallery/';

	// Settings
	// If an upload url has been specified in the global settings use it, else use the temporary directory.
	if(!is_dir($uploadDirectory)) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 901, "message": "The album selected does not exist, please create a new album and try again."}, "id" : "id"}');
	}
	
	$targetDir = $uploadDirectory;
	$relativePath = 'images/photogallery';

	$cleanupTargetDir = true; // Remove old files
	$maxFileAge = 5 * 3600; // Temp file age in seconds

	// 5 minutes execution time
	@set_time_limit(5 * 60);

	// Uncomment this one to fake upload time
	//usleep(5000);

	// Get parameters
	$chunks = 0; $chunk = 0; $fileName = 0;
	if(isset($_POST['chunk'])) $chunk = intval($_POST['chunk']);
	if(isset($_POST['chunks'])) $chunks = intval($_POST['chunks']);
	if(isset($_POST['name'])) $fileName = $_POST['name'];
	if(isset($_GET['chunk'])) $chunk = intval($_GET['chunk']);
	if(isset($_GET['chunks'])) $chunks = intval($_GET['chunks']);
	if(isset($_GET['name'])) $fileName = $_GET['name'];

	// Check extension
	$allowedExtensions = array('jpg', 'jpeg', 'gif', 'png');
	$fileInfo = pathinfo($fileName);
	// Get the file extension to append back onto the filename after sanitization.
	$fileExt = $fileInfo['extension'];
	// Sanitize the filename and add the extension back on since the validateAlias function will remove it by default.
	$fileName = $globalValidation->validateAlias($fileName, FALSE) . '.' . $fileExt;


	if(!in_array(strtolower($fileInfo['extension']), $allowedExtensions)) {
		// If the extension isn't an image file.
		die('{"jsonrpc" : "2.0", "error" : {"code": 900, "message": "Invalid extension, valid extensions are jpg, gif, png."}, "id" : "id"}');
	}

	// Clean the fileName for security reasons
	$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

	// Make sure the fileName is unique but only if chunking is disabled
	if ($chunks < 2 && file_exists($targetDir . '/' . $fileName)) {
		$ext = strrpos($fileName, '.');
		$fileName_a = substr($fileName, 0, $ext);
		$fileName_b = substr($fileName, $ext);

		$count = 1;
		while (file_exists($targetDir . '/' . $fileName_a . '_' . $count . $fileName_b))
			$count++;

		$fileName = $fileName_a . '_' . $count . $fileName_b;
	}

	$filePath = $targetDir . '/' . $fileName;
	$relativeUrl = $relativePath . '/' . $fileName;

	// Create target dir
	if (!file_exists($targetDir))
		@mkdir($targetDir);

	// Remove old temp files
	if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
		while (($file = readdir($dir)) !== false) {
			$tmpfilePath = $targetDir . '/' . $file;

			// Remove temp file if it is older than the max age and is not the current file
			if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
				@unlink($tmpfilePath);
			}
		}

		closedir($dir);
	} else
		die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');


	// Look for the content type header
	if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
		$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

	if (isset($_SERVER["CONTENT_TYPE"]))
		$contentType = $_SERVER["CONTENT_TYPE"];

	// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
	if (strpos($contentType, "multipart") !== false) {
		if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen($_FILES['file']['tmp_name'], "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
				fclose($in);
				fclose($out);
				@unlink($_FILES['file']['tmp_name']);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
	} else {
		// Open temp file
		$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
		if ($out) {
			// Read binary input stream and append it to temp file
			$in = fopen("php://input", "rb");

			if ($in) {
				while ($buff = fread($in, 4096))
					fwrite($out, $buff);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

			fclose($in);
			fclose($out);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
	}

	// Check if file has been uploaded
	if (!$chunks || $chunk == $chunks - 1) {
		// Strip the temp .part suffix off
		rename("{$filePath}.part", $filePath);
	}

	// Update the database with the media file
	if(file_exists($filePath)) {
		$fileInfo = pathinfo($filePath);
		$extension = strtolower($fileInfo['extension']);
		require_once DIR_ADMIN . 'controller/photogallery/photogallery-validation.php';
		require_once DIR_ADMIN . 'controller/common/global-validation.php';
		require_once DIR_SYSTEM . 'library/thumbnail-generator.php';
		$galleryValidation = new GalleryValidation;
		$imageName = $fileName;
		$imageAlias = $galleryValidation->validateAlias($fileName, TRUE);
		$imageAuthor = $_SESSION['userId'];
		$imageTitle = $fileInfo['filename'];
		$imageSize = filesize($filePath);
		$imageDimensions = getimagesize($filePath);
		$imageMimeType = mime_content_type($filePath);
		$imageMeta = '';
		$imageType = exif_imagetype($filePath);
		$imageUrl = HTTP_GALLERY . '/' . $fileName;
		// Get image exif data
		if($imageType === IMAGETYPE_JPEG) {
			$exifData = exif_read_data($filePath);
			// Get meta data for images.
			$imageMeta['model'] = isset($exifData['Model']) ? $exifData['Model'] : '';
			$imageMeta['aperature'] = isset($exifData['AperatureValue']) ? $exifData['AperatureValue'] : '';
			$imageMeta['focal_length'] = isset($exifData['FocalLength']) ? $exifData['FocalLength'] : '';
			$imageMeta['shutter_speed'] = isset($exifData['ShutterSpeedValue']) ? $exifData['ShutterSpeedValue'] : '';
			$imageMeta['owner'] = isset($exifData['OwnerName']) ? $exifData['OwnerName'] : '';
			$imageMeta['created_timestamp'] = isset($exifData['FileDateTime']) ? $exifData['FileDateTime'] : '';
			$imageMeta = serialize($imageMeta);
		}
		$globalValidate = new GlobalValidation;
		$galleryValidate = new GalleryValidation;
		$globalValidate->validateCreateDate();
		$galleryValidate->validateOrder($albumId);
		$imageArr = array( 
			'title'        => '',
			'alias'        => $imageAlias,
			'parentAlias'  => $albumAlias,
			'image'        => $fileName,
			'imageSize'    => $imageSize,
			'state'        => 1,
			'orderOfItem'  => $galleryValidate->orderOfItem,
			'createdBy'    => $_SESSION['userId'],
			'dateCreated'  => $globalValidate->createDate,
			'imageUrl'     => $imageUrl,
			'imagePath'    => $filePath,
			'mimeType'     => $imageMimeType
		);
		$imageId = $gallery->insertGalleryNodeQuick($imageArr, $albumId);
		$newPhoto = $gallery->getPhoto($imageId);
		// Insert thumbnails for media images.
		if(!empty($imageId)) {
			$imageQuality = $globalSettings['media_image_quality']['value'];
			// Get the template for the album.
			$template = $gallery->getTemplate((int)$albumId);
			// Resize and save the source image to fit the gallery.
			$thumbnailGenerator = new ThumbnailGenerator($filePath);
			$thumbnailGenerator->resizeImage((int)$template['image_width'], (int)$template['image_height'], $template['type']);
			$thumbnailGenerator->saveImage($filePath, $imageQuality);
			// Resize and save thumbnail from source image.
			$thumbnailGenerator = new ThumbnailGenerator($filePath);
			$thumbnailGenerator->resizeImage((int)$template['thumbnail_width'], (int)$template['thumbnail_height'], $template['type']);
			$thumbnailGenerator->saveImage(DIR_IMAGE . 'photogallery/thumb-' . $fileName, $imageQuality);
			// Resize and save admin thumbnail from source image.
			$thumbnailGenerator = new ThumbnailGenerator($filePath);
			$thumbnailGenerator->resizeImage(175, 125, 'crop');
			$thumbnailGenerator->saveImage(DIR_IMAGE . 'photogallery/admin-thumb-' . $fileName, $imageQuality);
		}
		$adminReq = new stdClass;
		$adminReq->id = $imageId;
		$adminReq->imageTitle = $imageArr['title'];
		$adminReq->imageUrl = $imageUrl;
		$adminReq->imageAlias = $imageAlias;
		$adminReq->fileName = $fileName;
		$adminReq->parentAlias = $albumAlias;
		$adminReq->thumbUrl = HTTP_ADMIN . 'photogallery/' . $albumAlias . '?id=' . $imageId . '&amp;adminRequest=image-preview&amp;csrfToken=' . hsc($_GET['csrfToken']);
		$adminReq->imageType = $imageArr['mimeType'];
		$adminReq->order = $newPhoto['order_of_item'];
		$adminReq->uploadDate = date('M d, Y', time());
		$adminReq->imageSize = isset($imageSize) ? $imageSize : '';
		$adminReq->width = isset($imageWidth) ? $imageWidth : '';
		$adminReq->height = isset($imageHeight) ? $imageHeight : '';
		$adminReq->errors = isset($errors) ? $errors : '';
		// Return JSON resposne
		echo json_encode($adminReq);
		exit;
	}
}
