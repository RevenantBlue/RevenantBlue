<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/common/global-validation.php';

class GalleryValidation extends GlobalValidation {

	public  $errors = array();                                // Array that contains the errors that occur during validation.
	public  $album;
	public  $photo;
	public  $id;                                              // Holds the validated id for the photo.
	public  $title;                                           // Holds the name of the album currently being validated.
	public  $alias;
	public  $dateCreated;
	public  $dirFormatAlbum;                                  // Holds the name of the album in lower case and with spaces replaced by underscores.
	public  $imageFile;                                       // Holds the image file currently being validated.
	public  $uploadPath;                                       // Holds the path name to the directory for saving the image to the server.
	public  $imageTitle;                                      // Holds the title of the image being uploaded.
	public  $imageAlias;                                      // Holds the image name in a directory friendly format.
	public  $imageSize;
	public  $imageDescription;                                // Holds the description of the image being uploaded.
	public  $state;                                           // Holds tth for the administrative thumbnail that was created.
	public  $featured;                                        // Holsd the value of the front page setting for a photo.
	public  $orderOfItem;
	public  $template;
	private $image;
	private $gallery;                                         // Holds an instance of the Photo Gallery class.
	private $namePattern = "/[^a-zA-Z0-9- ']/";
	private $titlePattern = "/[^a-zA-Z0-9- \?\.!@']/";
	private $dirSpacePattern = "/ /";                         // Set the pattern to be used for replacing spaces with hypens in the directory tree.
	private $thumbLocation;                                   // Holds the directory path for the thumbnail image that was created.
	private $adminThumbLocation;                              // Holds the directory pa image that have an error after upload.
	private $imageQuality = 85;                               // The quality of the jpeg image being created, scale is from 1-100.
	private $sourceImage;                                     // Used for keeping track of the source image location for getting the image size after resizing.
	private $imageDirectory;

	public function __construct($galleryArr = NULL, $galleryImage = FALSE) {
		require_once DIR_ADMIN . 'model/photogallery/photogallery-main.php';
		require_once DIR_SYSTEM . 'library/thumbnail-generator.php';

		$this->gallery = new PhotoGallery;

		if(!empty($galleryArr) && empty($galleryImage)) {
			if(!empty($galleryArr['id'])) $this->validateId($galleryArr['id']);
			$this->validateTitle($galleryArr['title']);
			$this->validateAlias($galleryArr['alias']);
			$this->validateCreateDate($galleryArr['dateCreated']);
			$this->validateCreatedBy($galleryArr['createdBy']);
			$this->validateState($galleryArr['state']);
			$this->validateFeatured($galleryArr['featured']);
			$this->validateOrder();
			$this->validateDescription($galleryArr['description']);
			$this->validateMetaDescription($galleryArr['metaDescription']);
			$this->validateMetaKeywords($galleryArr['metaKeywords']);
			$this->validateMetaRobots($galleryArr['metaRobots']);
			$this->validateMetaAuthor($galleryArr['metaAuthor']);
			$this->template = (int)$galleryArr['template'];
			if(!empty($this->id)) $this->checkForAliasChange();
			$this->image = $this->validateString($galleryArr['image'], 255, 'An error occurred while creating the image file name.');
			$this->imageAlt = $this->validateString($galleryArr['imageAlt'], 255, 'The image alt attribute must be less than 255 characters.');
			$this->imagePath = $this->validateString($galleryArr['imagePath'], 255, 'Image path cannot exceed 255 characters');
			
			$this->album = array( 
				'id'              => $this->id,
				'title'           => $this->title,
				'alias'           => $this->alias,
				'dateCreated'     => $this->dateCreated,
				'createdBy'       => $this->createdBy,
				'image'           => $this->image,
				'imageAlt'        => $this->imageAlt,
				'imagePath'       => $this->imagePath,
				'imageName'       => $this->imageTitle,
				'imageSize'       => $this->imageSize,
				'imageUrl'        => '',
				'description'     => $this->description,
				'state'           => $this->state,
				'featured'        => $this->featured,
				'orderOfItem'     => $this->orderOfItem,
				'metaDescription' => $this->metaDescription,
				'metaKeywords'    => $this->metaKeywords,
				'metaRobots'      => $this->metaRobots,
				'metaAuthor'      => $this->metaAuthor,
				'template'        => $this->template
			);
		} else {
			$this->validateId($galleryArr['id']);
			$this->validateTitle($galleryArr['title']);
			$this->validateState($galleryArr['state']);
			$this->validateFeatured($galleryArr['featured']);
			$this->validateDescription($galleryArr['description']);

			$this->photo = array(
				'id'            => $this->id,
				'title'         => $this->title,
				'state'         => $this->state,
				'featured'      => $this->featured,
				'description'   => $this->description,
				'caption'       => $galleryArr['caption'],
				'alt'           => $galleryArr['alt']
			);
		}
	}

	public function validateCreatedBy($createdBy) {
		$this->createdBy = $createdBy;
	}

	public function validateAlbumImage($image) {
		// Set the directory structure to create for the new album.
		$newAlbumDirectory = DIR_GALLERY . $this->alias . "/thumbs/admin-thumb";
		if(!is_dir($newAlbumDirectory)) {
			if(!mkdir($newAlbumDirectory, 0755, TRUE)) {
				$this->errors[] = "Couldn't create the directory for the album. Ensure that you have the appropriate permissions set for the photogallery directory.";
			}
		}
		
		// If an image has been submitted set the value to the image property else return false.
		if(!empty($image) && $image['size'] !== 0) {
			$this->image = $image;
		} elseif(isset($this->id)) {
			$this->imageAlias = $this->gallery->getImage($this->id);
			return TRUE;
		} else {
			return TRUE;
		}
		// Check to see if the file is legit.
		if(is_uploaded_file($this->image['tmp_name'])) {
			// Change the name of the image to a unique random name.
			$extension = explode('.', $this->image['name']);
			$this->image['name'] = md5(uniqid($extension[0])) . "." . strtolower($extension[1]);
			$this->imageAlias = $this->image['name'];
			// Validate the image to ensure that it's either .jpg, .gif, or .png format.
			if(exif_imagetype($this->image['tmp_name']) == IMAGETYPE_JPEG
			|| exif_imagetype($this->image['tmp_name']) == IMAGETYPE_GIF
			|| exif_imagetype($this->image['tmp_name']) == IMAGETYPE_PNG) {
				// Do nothing and continue the validation process.
			} else {
				$this->errors[] = "Invalid image format.  Only .jpg, .gif, and .png formats are allowed.";
			}
			// Check to see if an error occured during uploading the image to the server.
			if($this->image['error'] > 0) {
				$this->errors[] = "An error occured while uploading the image to the server.";
			}
			// Check to ensure that the size is less than or equal to 7MB
			if(!empty($this->image['size']) && $this->image['size'] >= 7340032) {
				$this->errors[] = "The source image must be less than 7MB in size.";
			}
			// Set the image size to display in KB instead of Bytes.
			$this->imageSize = number_format($this->image['size'] / 1024, 0);

			// Assign the property imageName with the name of the image file for the article.
			$this->imageName = hsc($this->image['name']);
		} else {
			$this->errors[] = "An error occured while uploading the image to the server.";
			return FALSE;
		}
		// If no errors have occured, the directory exists, and the albumName property is set process the upload of the album image and retun the array.
		if(empty($this->errors) && isset($this->alias)) {
			// Set the upload path for the album and replace spaces with underscores for proper directory format.
			$this->uploadPath = DIR_GALLERY . $this->alias;
			if(is_dir($this->uploadPath)) {
				$this->uploadPath = $this->uploadPath . "/" . $this->imageAlias;
				if(move_uploaded_file($this->image['tmp_name'], $this->uploadPath)) {
					// Check image width and height to ensure it falls within the 3000w x 3000h max constraint.
					if(imagesx(openImage($this->uploadPath)) > 3000 || imagesy(openImage($this->uploadPath)) > 3000) {
						if(imagesx(openImage($this->uploadPath)) > 3000) {
							$this->errors[] = "The image cannot exceed 3000px in width.  Image width: " . imagesx(openImage($this->uploadPath)) . "px.";
							unlink($this->uploadPath);
							return FALSE;
						} elseif(imagesy(openImage($this->uploadPath)) > 3000) {
							$this->errors[] = "The image cannot exceed 3000px in height.  Image height: " . imagesy(openImage($this->uploadPath)) . "px.";
							unlink($this->uploadPath);
							return FALSE;
						}
					} else {
						// Set the upload path for the album.
						$this->uploadPath = DIR_GALLERY . $this->alias;
						$sourceImage = $this->uploadPath . "/" . $this->imageAlias;

						// Get the image quality for the image.
						switch(exif_imagetype($sourceImage)) {
							case IMAGETYPE_JPEG:
								$this->imageQuality = $this->gallery->getImageQuality('jpg');
								break;
							case IMAGETYPE_GIF:
								$this->imageQuality = $this->gallery->getImageQuality('gif');
								break;
							case IMAGETYPE_PNG :
								$this->imageQuality = $this->gallery->getImageQuality('png');
								break;
							default:
								$this->imageQuality = 85;
								break;
						}

						$thumbnailGenerator = new ThumbnailGenerator($sourceImage);
						// Resize and save thumbnail from source image.
						$thumbnailGenerator->resizeImage(215, 120, 'crop');
						$thumbnailGenerator->saveImage($this->uploadPath . "/" . "thumbs" . "/" . $this->imageAlias, $this->imageQuality);

						$thumbnailGenerator = new ThumbnailGenerator($sourceImage);
						// Resize and save admin thumbnail from source image.
						$thumbnailGenerator->resizeImage(92, 50, 'crop');
						$thumbnailGenerator->saveImage($this->uploadPath . "/" . "thumbs" . "/" . "admin-thumb" . "/" . $this->imageAlias, $this->imageQuality);

					}
				} else {
					$this->errors[] = "An error occured while uploading your file to the server.  Please try again.";
					return false;
				}
			} else {
				rrmdir($this->uploadPath);
				$this->errors[] = "Could not save the file to the server at this time, please try again.";
				return false;
			}
		} else {
			return false;
		}
	}

	public function validateImageAlbum($album) {
		// Check to ensure that the album to place the image is not empty.
		if(empty($album))  {
			$this->errors[] = "The album for this uploaded image cannot be empty.  Please select an album from the list and try again.";
		}

		// Make sure the album is a valid album by referencing the photo gallery database.
		if($this->getAlbumByName($album) == NULL) {
			$this->errors[] = "The album provided is not valid.  Please select an album from the provided list and try again.";
		}

		// Make sure that the album name is under 100 characters long.
		if(strlen($album) > 100 || strlen($album) < 0) {
			$this->errors[] = "The album name must be between 1-100 characters long.";
		}

		// If there are no errors process and store the album name in the albumName property.
		if(empty($this->errors)) {
			$this->albumName = ucwords($album);
			return true;
		} else {
			return false;
		}
	}

	public function validateImageDescription($imageDescription) {
		// Make sure the image description is less than 1000 characters in length.
		if(strlen($imageDescription[$x]) > 1000) {
			$this->errors[] = "The image descirption must be 1000 characters or less.";
		}
		// Store the array as a property.
		$this->imageDescription = $imageDescription;
		// If there are no errors filter the image description and return true.
		if(empty($this->errors)) {
			return true;
		} else {
			return false;
		}
	}

	public function validateOrder($parentId = NULL) {
		if(empty($parentId)) {
			$numOfRootNodes = $this->gallery->getNumOfRootNodes();
			if(!empty($numOfRootNodes)) {
				$this->orderOfItem = $numOfRootNodes + 1;
			} else {
				$this->orderOfItem = 1;
			}
		} elseif(isset($parentId)) {
			$numOfPhotos = $this->gallery->getNumOfPhotos($parentId);
			if(!empty($numOfPhotos)) {
				$this->orderOfItem = $numOfPhotos + 1;
			} else {
				$this->orderOfItem = 1;
			}
		} elseif(!empty($this->id)) {
			$numOfPhotos = $this->gallery->getNumOfPhotos($this->id);
			if(!empty($numOfPhotos)) {
				$this->orderOfItem = $numOfPhotos + 1;
			} else {
				$this->orderOfItem = 1;
			}
		}
	}

	protected function checkForDuplicateAlias($alias) {
		// If the alias exists and the id is not set (not updating an existing gallery node).
		$existingId = $this->gallery->getIdByAlias($alias);
		if(!empty($existingId) && !empty($this->id)) {
			// If the alias has not changed it is not a duplicate.
			if((int)$existingId === (int)$this->id) {
				return FALSE;
			} else {
				return TRUE;
			}
		} elseif(!empty($existingId)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	// When changing an alias - update the album's images with the new alias.
	public function checkForAliasChange() {
		$node = $this->gallery->getGalleryItem($this->id);
		$currentAlias = $node['alias'];
		$parentAlias = $node['parent_alias'];
		if($currentAlias !== $this->alias && empty($parentAlias)) {
			if(!is_dir(DIR_GALLERY . $this->alias)) {
				rename(DIR_GALLERY . $currentAlias, DIR_GALLERY . $this->alias);
			}
			$photos = $this->gallery->loadPhotos($this->id);
			foreach($photos as $photo) {
				$photoArr['id'] = $photo['id'];
				$photoArr['parentAlias'] = $this->alias;
				$photoArr['imageUrl'] = HTTP_GALLERY . $this->alias . '/' . $photo['image'];
				$photoArr['imagePath'] = DIR_GALLERY . $this->alias . '/' . $photo['image'];
				$this->gallery->updatePhotoAfterAliasChange($photoArr);
			}
		}
	}
}
