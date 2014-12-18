<?php
namespace RevenantBlue\Admin;

//require_once '../../model/categories/categories-main.php';
require_once DIR_ADMIN . 'controller/common/global-validation.php';
require_once DIR_ADMIN . 'model/categories/categories-main.php';

class CategoryValidation extends GlobalValidation {

	public  $errors;
	public  $category;
	public  $id;
	public  $title;
	public  $alias;
	public  $parentCategory;
	public  $image;
	public  $imageAlt;
	public  $imagePath;
	public  $imageDirectory;
	public  $imageUplodatPath;
	public  $imageWidth = 165;
	public  $imageHeight = 120;
	public  $state;
	public  $orderOfItem;
	public  $description;
	public  $createdBy;
	public  $dateCreated;
	public  $metaDescription;
	public  $metaKeywords;
	public  $metaAuthor;
	public  $metaRobots;
	public  $categories;

	public function __construct($category = NULL) {
		global $acl;
		global $config;
		
		// Instantiate the category model
		$this->categories = new Categories;
		$this->imageDirectory = DIR_IMAGE . 'categories/';
		
		// Get highest ranked role, true means to return the role's id instead of its name.
		$highestRoleId = $acl->getHighestRankedRoleForUser($_SESSION['userId'], TRUE);
		// Get the content filter.
		$contentFormat = $config->getContentFilterForRole($highestRoleId);
		
		// Build the category array.
		if(!empty($category['id'])) {
			$this->validateId($category['id']);
		}
		
		$this->title = $this->validateTitle($category['title']);
		$this->alias = $this->validateAlias($category['alias'], TRUE);
		$this->parentCategory = $this->validateParent($category['parent']);
		$this->state = $this->validateState($category['published']);
		$this->description = $this->validateContent($category['description'], $contentFormat, TRUE);
		$this->image = $this->validateString($category['image'], 255, 'An error occurred while creating the image file name.');
		$this->imageAlt = $this->validateString($category['imageAlt'], 255, 'The image alt attribute must be less than 255 characters.');
		$this->imagePath = $this->validateString($category['imagePath'], 255, 'Image path cannot exceed 255 characters');
		$this->validateOrder();
		$this->dateCreated = $this->validateCreateDate($category['datePosted']);
		$this->createdBy = $this->validateCreatedBy($category['createdBy']);

		if(isset($category['metaDescription'])) $this->validateMetaDescription($category['metaDescription']);
		if(isset($category['metaKeywords'])) $this->validateMetaKeywords($category['metaKeywords']);
		if(isset($category['metaRobots'])) $this->validateMetaRobots($category['metaRobots']);
		if(isset($category['metaAuthor'])) $this->validateMetaAuthor($category['metaAuthor']);

		$this->category = array(
			'id'              => $this->id,
			'title'           => $this->title,
			'alias'           => $this->alias,
			'parent'          => $this->parentCategory,
			'state'           => $this->state,
			'orderOfItem'     => $this->orderOfItem,
			'description'     => $this->description,
			'image'           => $this->image,
			'imageAlt'        => $this->imageAlt,
			'imagePath'       => $this->imagePath,
			'createdBy'       => $this->createdBy,
			'dateCreated'     => $this->dateCreated,
			'metaDescription' => $this->metaDescription,
			'metaKeywords'    => $this->metaKeywords,
			'metaRobots'      => $this->metaRobots,
			'metaAuthor'      => $this->metaAuthor
		);
	}

	public function validateParent($parent) {
		switch($parent) {
			case is_numeric($parent) && $parent !== 0:
				$parentExists = $this->categories->getCategoryById($parent);
				if(empty($parentExists)) $this->errors[] = "The parent you have selected does not exist.";
				$this->parentCategory = $parent;
				break;
			case is_numeric($parent) && $parent == 0;
				$this->parentCategory = $parent;
				break;
		}
		return $parent;
	}

	public function validateDescription($desc) {
		$this->description = $desc;
	}

	public function validateCreatedBy($createdBy) {
		$this->createdBy = $createdBy;
		return $createdBy;
	}

	public function validateOrder() {
		if($this->parentCategory == 0) {
			$numOfRoots = count($this->categories->getRootNodes());
			$this->orderOfItem = ($numOfRoots + 1);
		} else {
			$numOfChildren = count($this->categories->getAllChildNodes($this->parentCategory));
			$this->orderOfItem = ($numOfChildren + 1);
		}
	}

	public function validateImage($image) {
		// If an image has been submitted set the value to the image property else return false.
		if(isset($image) && $image['size'] != 0) {
			$this->image = $image;
		} elseif(isset($this->id)) {
			$this->image['name'] = $this->categories->getCategoryImage($this->id);
			return TRUE;
		} else {
			return TRUE;
		}
		// Check to see if the file is legit.
		if(is_uploaded_file($this->image['tmp_name'])) {
			// Change the name of the image to a unique random name.
			$extension = explode('.', $this->image['name']);
			$this->image['name'] = md5(uniqid()) . "." . strtolower($extension[1]);
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
			if(!empty($this->image['size'])) {
				$this->image['size'] = number_format($this->image['size'] / 1024, 0);
			}
			// Assign the property imageName with the name of the image file for the article.
			$this->imageName = hsc($this->image['name']);
		} else {
			$this->errors[] = "An error occured while uploading the image to the server.";
			return FALSE;
		}
		if(empty($this->errors)) {
			// Set the directory structure to create for the new article image.
			if(!is_dir($this->imageDirectory)) {
				$createDir = mkdir($this->imageDirectory, 0777, true);
				if($createDir === FALSE) {
					$this->errors[] = "An error occured while uploading the file to the server.  Make sure you have write privileges enabled on your server.";
					return FALSE;
				}
			}
			// Upload the image to the server.
			if(is_dir($this->imageDirectory)) {
				$uploadPath = $this->imageDirectory . $this->image['name'];
				if(move_uploaded_file($this->image['tmp_name'], $uploadPath)) {
					// Check image width and height to ensure it falls within the 3000w x 3000h max constraint.
					if(imagesx(openImage($uploadPath)) > 3000 || imagesy(openImage($uploadPath)) > 3000) {
						if(imagesx(openImage($uploadPath)) > 3000) {
							$this->errors[] = "The image cannot exceed 3000px in width.  Image width: " . imagesx(openImage($uploadPath)) . "px.";
							unlink($uploadPath);
							return FALSE;
						} elseif(imagesy(openImage($uploadPath)) > 3000) {
							$this->errors[] = "The image cannot exceed 3000px in height.  Image height: " . imagesy(openImage($uploadPath)) . "px.";
							unlink($uploadPath);
							return FALSE;
						}
					} else {
						$existingImage = $this->categories->getCategoryImage($this->id);
						if(!empty($existingImage) && file_exists($this->imageDirectory . $existingImage)) {
							if(!unlink($this->imageDirectory . $existingImage)) {
								$this->errors[] = "An error occured while updating the image.";
							}
						}
						// Initialize the thumbnail generator with the source image.
						$thumbnailGenerator = new ThumbnailGenerator($uploadPath);
						// Resize the image and crop to fit.
						$thumbnailGenerator->resizeImage($this->imageWidth, $this->imageHeight, 'crop');
						$saveSuccess = $thumbnailGenerator->saveImage($uploadPath);
						if($saveSuccess === FALSE) {
							unlink($uploadPath);
							$this->errors[] = "An error occured while uploading the article image to the server.";
						}
						$this->imageUploadPath = $uploadPath;
						return true;
					}
				} else {
					$this->errors[] = "An error occured while uploading the article image to the server.";
					return false;
				}
			} else {
				$this->errors[] = "An error occured while uploading the article imaget to the server. herp";
				return false;
			}
		} else {
			return false;
		}
	}

	protected function checkForDuplicateAlias($alias) {
		$id = $this->categories->getIdByAlias($alias);
		if(!empty($id)) {
			if(!empty($this->id) && (int)$this->id === (int)$id) {
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			return FALSE;
		}
	}
}
