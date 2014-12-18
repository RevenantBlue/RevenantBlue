<?php
namespace RevenantBlue\Admin;

require_once DIR_SYSTEM . 'library/thumbnail-generator.php';
require_once DIR_ADMIN . 'controller/common/content-filtering.php';

class GlobalValidation {

	public  $errors = array();
	public  $id;
	public  $title;
	public  $alias;
	public  $state;
	public  $description;
	public  $createDate;
	public  $metaDescription;
	public  $metaKeywords;
	public  $metaRobots;
	public  $metaAuthor;
	private $namePattern = "/[^a-zA-Z0-9- ']/";
	private $titlePattern = "/[^a-zA-Z0-9-_ \?\.!@\(\)><$,']\=/";

	public function validateId($id) {
		// Check to make ensure that the id field is not empty.
		if(empty($id)) $this->errors[] = "The id field cannot be empty.";
		// Check to make sure the id field is a number.
		if(!is_numeric($id)) $this->errors[] = "The id field must be a number.";
		// Assign the id value to the id property.
		$this->id = $id;
		return $this->id;
	}

	public function validateAuthor($author) {
		$this->author = $author;
		return $author;
	}

	public function validateTitle($title, $titleName = 'title') {
		// Trim any whitespace.
		$title = trim($title);
		// Check to ensure that the title is not empty.
		if(empty($title)) $this->errors[] = 'The ' . $titleName . ' cannot be left blank.';
		// Check to ensure that the title is less than 255 characters.
		if(strlen($title) > 255) $this->errors[] = 'The ' . $titleName . ' cannot be longer than 255 characters.';
		// Check the title for illegal characters
		/*
		if(preg_match($this->titlePattern, $title) == 1) {
			$this->errors[] = 'The ' . $titleName . " can only contain letters, numbers, spaces, and the following symbols. - ! ? @ ' = > < $ ,";
		}
		*/
		// Assign $title to the title property.
		$this->title = $title;
		return $title;
	}

	public function validateAlias($alias, $checkForDuplicate = TRUE) {
		if(!empty($alias)) {
			$alias = $this->createAlias($alias);
		} elseif(isset($this->title)) {
			// Set the alias to the value of the title with spaces replaced by hyphens.
			$alias = $this->createAlias($this->title);
		} elseif(isset($this->name)) {
			$alias = $this->createAlias($this->name);
		}
		if($checkForDuplicate === TRUE) {
			$this->alias = $alias;
			$num = 2;
			while($this->checkForDuplicateAlias($this->alias) === TRUE) {
				$this->alias = $alias . '-' . $num;
				$num++;
			}
		}
		return $this->alias;
	}

	public function createAlias($alias) {
		// If the alias provided is empty assign it the title property if it isn't empty.
		if(empty($alias) && !empty($this->title)) $alias = $this->title;

		// Everything to lower and no spaces at the beginning or end and ensure it's no longer than 255 characters to preven buffer overflow.
		$alias = substr(strtolower(trim($alias)), 0, 255);

		// If file remove the file extension
		$alias = $this->removeFileExtension($alias);

		// Replace accent characters, depends your language is needed
		$alias = $this->replaceAccents($alias);

		// Adding - for spaces and union characters
		$find = array(' ', '&', '\r\n', '\n', '+',',');
		$alias = str_replace($find, '-', $alias);

		// Delete and replace rest of special chars
		$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
		$repl = array('', '-', '');
		$alias = preg_replace ($find, $repl, $alias);

		// Return the friendly url
		if(empty($this->alias)) $this->alias = $alias;
		return $alias;
	}

	public function validateCreateDate($createDate = NULL) {
		// Replace the dashes with slashes for strtotime to function correctly.
		$createDate = str_replace('/', '-', $createDate);
		// If the create date field is empty and the id field is not empty (which means the article is being edited - assign the create date to the old create date.
		if(empty($createDate)) {
			$this->createDate = $this->datePosted = date('Y-m-d H:i:s', time());
			return $this->createDate;
		} else {
			$date = strtotime(str_replace('-', '/', $createDate));
			$validDate = isValidTimeStamp($date);
			if($validDate === TRUE) {
				$this->createDate = $this->datePosted = date('Y-m-d H:i:s', $date);
				return $this->createDate;
			} else {
				$this->createDate = $this->datePosted = $createDate;
				$this->errors[] = "The date posted/created is invalid.  The correct date/time format is MM-DD-YY HH:MM AM/PM";
			}
		}
		return $createDate;
	}

	public function validateState($state) {
		// Check to ensure that the published state is not empty.
		if(!isset($state)) $this->errors[] = "The published field cannot be empty.";
		// Check to ensure that the pubilshed value is a boolean type.
		if(!is_numeric($state)) $this->errors[] = "Invalid value for the published field.";
		// Assign the publishState value to the publishState property.
		$this->state = $state;
		return $this->state;
	}

	public function validateFeatured($featured) {
		// Check to ensure that the front page setting is not empty.
		if(!isset($featured)) $this->errors[] = "The featured value cannot be empty.";
		// Check to ensure that the front page value is a boolean type.
		if(!is_numeric($featured)) $this->errors[] = "Invalid value for the featured field.";
		// Assign the featured value to the featured proprety.
		$this->featured = $featured;
	}

	public function validateContent($content, $contentFormatId, $allowEmpty = FALSE, $emptyError = FALSE) {
		// Check to ensure the content is not empty.
		if(empty($content) && $allowEmpty === FALSE) {
			$this->errors[] = $emptyError;
			return FALSE;
		}
		// If the content is empty and there is no error for having an empty content field, skip validation.
		if(empty($content) && $allowEmpty === TRUE) {
			return FALSE;
		}
		
		$content = ContentFilter::filterContent($content, $contentFormatId);
		return $content;
	}

	public function validateString($string, $strLength, $strLengthError, $regExp = false, $regExpError = false) {
		if(strlen($string) > $strLength) {
			$this->errors[] = $strLengthError;
		}
		// Trim whitespace and return the string.
		return trim($string);
	}

	public function validateDescription($desc) {
		$this->description = $desc;
		return $desc;
	}

	public function validateMetaDescription($metadesc) {
		$this->metaDescription = $metadesc;
	}

	public function validateMetaKeywords($keywords) {
		$this->metaKeywords = $keywords;
	}

	public function validateMetaRobots($robots) {
		$this->metaRobots = $robots;
	}

	public function validateMetaAuthor($author) {
		$this->metaAuthor = $author;
	}

	public function validateEmail($email, $required = TRUE, $duplicateCheck = FALSE) {
		global $users;

		if(empty($email) && $required === FALSE) {
			$this->email = '';
			return FALSE;
		}
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
		// Allow for localhost email addresses under development environment
		if(DEVELOPMENT_ENVIRONMENT === TRUE && !empty($email)) {
			$localhostTest = explode('@', $email);
			if($localhostTest[1] === 'localhost') $validEmail = TRUE;
		}
		if(!empty($email) && !$validEmail) {
			$this->errors[] = 'The e-mail address provided is not formatted correctly.';
		} elseif(empty($email) && $required === TRUE) {
			$this->errors[] = 'No e-mail address was provided.';
		}

		if($duplicateCheck) {
			// Check for duplicate email address
			$userExists = $users->getUserByEmail($email);
			if(!empty($userExists)) {
				if(!empty($_SESSION['userId']) && $_SESSION['userId'] == $userExists['id']) {
					// If the current user's id is equal that of the user that has the email address they submitted.
				} else {
					$this->errors[] = 'An account with this email address already exists.';
				}
			}
		}
		$this->email = $email;
		return $this->email;
	}

	public function validateUrl($url) {
		$validUrl = filter_var($url, FILTER_VALIDATE_URL);
		if(!empty($url) && !$validUrl) {
			$this->errors[] = "The url for the website provided is not formatted correctly.";
		}
		$this->url = $url;
		return $this->url;
	}

	public function validateTags($tagsToValidate) {
		$tagsToValidate = explode(',', $tagsToValidate);
		// Require the tags model
		require_once DIR_ADMIN . 'model/common/common-main.php';
		$tags = new Tags;
		foreach($tagsToValidate as $tagToValidate) {
			$tagExists = $tags->getTagByName($tagToValidate);
			if(empty($tagExists)) {
				$tagAlias = $this->createAlias($tagToValidate);
				$tags->insertTag($tagToValidate, $tagAlias, '');
			}
		}
	}

	public function replaceAccents($var) {
		//replace for accents catalan spanish and more
		$accents = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$replacements = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		$var = str_replace($accents, $replacements, $var);
		return $var;
	}

	public function removeFileExtension($string) {
		$pathParts = pathinfo($string);
		return $pathParts['filename'];
	}

	public function getContentFormatForUser($userId = FALSE) {
		global $acl;
		global $config;

		$userId = empty($userId) ? $_SESSION['userId'] : $userId;
		// Get highest ranked role, true means to return the role's id instead of its name.
		$highestRoleId = $acl->getHighestRankedRoleForUser($userId, TRUE);
		// Get the content filter.
		return $contentFormat = $config->getContentFilterForRole($highestRoleId);
	}

	public function validateRequired($requiredString, $error) {
		if(empty($requiredString) && $requiredString != '0') {
			$this->errors[] = $error;
		} else {
			return $requiredString;
		}
	}
}
