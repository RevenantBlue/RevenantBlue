<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/common/global-validation.php';
require_once DIR_ADMIN . 'controller/common/content-filtering.php';
require_once DIR_ADMIN . 'model/categories/categories-main.php';

class ArticleValidation extends GlobalValidation {

	public  $errors = array();                                   // Contains an array that stores all errors that occur during validation.
	public  $article;                                            // Array containing all aspects of the current article.
	public  $id;                                                 // Contains the id for the article entry being updated.
	public  $author;                                             // Contains the author's name of the article article being posted.
	public  $categoryIds;                                        // Contains the value of the category id for the article being posted.
	public  $title;                                              // Contains the title of the article article being posted.
	public  $alias;                                              // Contains the alias of the article article being posted.
	public  $datePosted;                                         // Date the article was created.
	public  $image;                                              // Contains the $_FILES global information for the article image being uploaded.
	public  $imageAlt;
	public  $imagePath;
	public  $imageName;                                          // Contains the name and the extension of the image.
	public  $imageUploadPath;                                    // Contains the upload path of the article image being uploaded.
	public  $imageDirectory;
	public  $imageWidth = 165;
	public  $imageHeight = 120;
	public  $summary;                                            // Contains the summary of the article article being posted.
	public  $content;                                            // Contains the content of the article article being posted.
	public  $publishState;                                       // Contains the value of the published state of the article.
	public  $featured;                                           // Contains the value of the front page settings for the article.
	public  $categoryName;                                       // Contains the value of the new category name being created.
	public  $tags;
	public  $attribs;                                            // Array of article attributes.
	public  $metaDescription;                                    // Contains the meta description of the current article.
	public  $metaKeywords;                                       // Contains the meta keywords for this article.
	public  $metaRobots;                                         // Contains the meta robots for this article.
	public  $metaAuthor;                                         // Contains the meta author for this article.
	private $namePattern = "/[^a-zA-Z0-9- ']/";
	private $titlePattern = "/[^a-zA-Z0-9- \?\.!@']/";
	private $articles;                                           // Contains the article model object for database calls.
	private $categories;                                         // Contains the category model.
	private $thumbWidth;                                         // Contains the default width of the blog image icon.
	private $thumbHeight;                                        // Contains the default height of the blog image icon.
	private $articleUrl;                                         // The url location relative to the host name.  Examples: /blog/ or /articles/categoryname/
	private $config;
	
	public function __construct($article = NULL, $articleUrl = NULL) {
		require_once DIR_ADMIN . 'model/articles/articles-main.php';
		require_once DIR_SYSTEM . 'library/thumbnail-generator.php';
		
		global $config;
		
		$this->articles = new Articles;
		$this->categories = new Categories;
		$this->config = $config;
		$this->imageDirectory = DIR_IMAGE . 'articles/';
		
		if(isset($article)) {
			// Validate array elements.
			if(!empty($article['id'])) $this->validateId($article['id']);
			$this->validateAuthor($article['author']);
			$this->validateCategoryIds($article['categories']);
			$this->validateTitle($article['title']);
			$this->alias = $this->validateAlias($article['alias']);
			$this->datePosted = $this->validateCreateDate($article['datePosted']);
			$this->content = $this->validateContent($article['content'], $article['contentFormatId'], FALSE, 'You cannot post an article without content.');
			$this->summary = $this->validateContent($article['summary'], $article['contentFormatId'], TRUE);
			$this->image = $this->validateString($article['image'], 255, 'An error occurred while creating the image file name.');
			$this->imageAlt = $this->validateString($article['imageAlt'], 255, 'The image alt attribute must be less than 255 characters.');
			$this->imagePath = $article['imagePath'];
			$this->validatePublishedState($article['published']);
			$this->validateFeatured($article['featured']);
			$this->metaDescription = $this->validateMetaTags($article['metaDescription']);
			$this->metaKeywords = $this->validateMetaTags($article['metaKeywords']);
			$this->metaRobots = $this->validateMetaTags($article['metaRobots']);
			$this->metaAuthor = $this->validateMetaTags($article['metaAuthor']);
			$this->weight = !empty($article['weight']) ? (int)$article['weight'] : 1;
			$this->attribs = !empty($article['attribs']) ? $this->validateAttribs($article['attribs']) : '';
			$this->tags = !empty($article['tags']) ? $this->validateArticleTags($article['tags']) : '';
			$this->contentFormat =  $article['contentFormatId'];

			// Build the validated article array for database inseration.
			$this->article = array( 
				'id'              => $this->id,
				'author'          => $this->author,
				'title'           => $this->title,
				'alias'           => $this->alias,
				'datePosted'      => $this->datePosted,
				'location'        => $this->articleUrl,
				'image'           => $this->image,
				'imageAlt'        => $this->imageAlt,
				'imagePath'       => $this->imagePath,
				'content'         => $this->content,
				'summary'         => $this->summary,
				'published'       => $this->publishState,
				'featured'        => $this->featured,
				'metaDescription' => $this->metaDescription,
				'metaKeywords'    => $this->metaKeywords,
				'metaRobots'      => $this->metaRobots,
				'metaAuthor'      => $this->metaAuthor,
				'weight'          => $this->weight,
				'contentFormat'   => $this->contentFormat
			);
		}
	}

	public function validateCategoryIds($categoryIds = array()) {
		if(empty($categoryIds)) {
			$this->categoryIds[] = 1;
		} else {
			foreach($categoryIds as $categoryId) {
				// Check to see if the categoryId argument is empty.
				if(empty($categoryId)) $categoryId = 1;
				// Check to ensure that the category id is a legitimate category.
				$legitCategory = $this->categories->getCategoryById((int)$categoryId);
				if(empty($legitCategory)) {
					$this->errors[] = "Invalid category";
				} else {
					// Assign the categoryId value to the categoryId property.
					$this->categoryIds[] = (int)$categoryId;
					// Keep only unique values since we have multiple selections from the all/popular category tabs.
					$this->categoryIds = array_unique($this->categoryIds);
				}
			}
		}
	}

	public function validateImage($image) {
		// If an image has been submitted set the value to the image property else return true.
		if(!empty($image) && $image['size'] != 0) {
			$this->image = $image;
		} elseif(isset($this->id)) {
			$this->image['name'] = $this->articles->getArticleImage($this->id);
			return TRUE;
		} else {
			return FALSE;
		}
		// Check to see if the file is legit.
		if(is_uploaded_file($this->image['tmp_name'])) {
			// Change the name of the image to a unique random name.
			$extension = explode('.', $this->image['name']);
			$this->image['name'] = md5(uniqid()) . "." . strtolower($extension[1]);
			// Validate the image to ensure that it's either .jpg, .gif, or .png format.
			if(exif_imagetype($this->image['tmp_name']) === IMAGETYPE_JPEG
			|| exif_imagetype($this->image['tmp_name']) === IMAGETYPE_GIF
			|| exif_imagetype($this->image['tmp_name']) === IMAGETYPE_PNG) {
			} else {
				$this->errors[] = "Invalid image format.  Only .jpg, .gif, and .png formats are allowed.";
				return FALSE;
			}
			// Check to see if an error occured during uploading the image to the server.
			if($this->image['error'] > 0) {
				$this->errors[] = "An error occured while uploading the image to the server:  " . $this->image['error'];
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
			$this->errors[] = "An error occured while uploading the article image to the server.";
			return false;
		}
		if(empty($this->errors)) {
			// Set the directory structure to create for the new article image.
			if(!is_dir($this->imageDirectory)) {
				$createDir = mkdir($this->imageDirectory, 0777, TRUE);
				if($createDir === FALSE) {
					$this->error[] = "An error occured while uploading the file to the server.  Make sure you have write privileges enabled on your server.";
				}
			}
			// Upload the article image to the server.
			if(is_dir($this->imageDirectory)) {
				$uploadPath = $this->imageDirectory . $this->image['name'];
				if(move_uploaded_file($this->image['tmp_name'], $uploadPath)) {
					// Check image width and height to ensure it falls within the 3000w x 3000h max constraint.
					// Get image resource.
					$imageResource = openImage($uploadPath);
					if(imagesx($imageResource) > 3000 || imagesy($imageResource) > 3000) {
						if(imagesx($imageResource) > 3000) {
							$this->errors[] = "The article image cannot exceed 3000px in width.  Image width: " . imagesx(openImage($uploadPath)) . "px.";
							unlink($uploadPath);
							return FALSE;
						} elseif(imagesy($imageResource) > 3000) {
							$this->errors[] = "The article image cannot exceed 3000px in height.  Image height: " . imagesy(openImage($uploadPath)) . "px.";
							unlink($uploadPath);
							return FALSE;
						}
					} else {
						$existingImage = $this->articles->getArticleImage($this->id);
						if(!empty($existingImage) && file_exists($existingImage)) {
							if(!unlink($this->imageDirectory . $existingImage)) {
								$this->errors[] = "An error occured while updating the image for the article.";
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
				$this->errors[] = "An error occured while uploading the article imaget to the server.";
				return false;
			}
		} else {
			return false;
		}
	}

	public function validatePublishedState($publishState) {
		// Check to ensure that the published state is not empty.
		if(!isset($publishState)) $this->errors[] = "The published field cannot be empty.";
		// Check to ensure that the pubilshed value is a boolean type.
		if(!is_numeric($publishState)) $this->errors[] = "Invalid value for the published field.";
		// Assign the publishState value to the publishState property.
		$this->publishState = $publishState;
	}

	public function validateAttribs($attribs) {
		if(empty($attribs)) {
			return FALSE;
		} else {
			return $attribs;
		}
		
		/*
		foreach($attribs as $attrName => $attrValue) {
			if($attrValue === 'Use Global'	|| is_numeric($attrValue) && $attrValue <= 2) {
				if($attrValue === 'Use Global') {
					$globalSetting = $this->config->getGlobalByAlias($attrName);
					$attrValue = $globalSetting['value'];
				}
				$this->attribs[$attrName] = $attrValue;
			} else {
				$this->errors[] = "One or more attributes contain illegal values.";
				return FALSE;
			}
		}
		*/
	}

	public function validateMetaTags($metaTag) {
		if(!isset($metaTag)) {
			break;
		} else {
			$metaTag = trim($metaTag);
			return $metaTag;
		}
	}

	public function validateArticleTags($tagsToValidate) {
		// Validate the tags to ensure they exists through the GlobalValidation class.
		$this->validateTags($tagsToValidate);
		// Create an array for iteration and database storage.
		$this->tags = explode(',', $tagsToValidate);
		
		return $this->tags;
	}

	protected function checkForDuplicateAlias($alias) {
		$id = $this->articles->getIdByAlias($alias);
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
