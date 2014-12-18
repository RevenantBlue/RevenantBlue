<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/common/global-validation.php';
require_once DIR_ADMIN . 'model/categories/categories-main.php';

class CommentValidation extends GlobalValidation {

	public  $errors = array();                                   // Contains an array that stores all errors that occur during validation.
	public  $article;                                            // Array containing all aspects of the current article.
	public  $id;                                                 // Contains the id for the comment.
	public  $author;                                             // Contains the name of the author of the comment.
	public  $content;                                            // Contains the content of the comment.
	public  $website;                                            // Contains the website of the person leaving a comment.
	public  $email;                                              // Contains the email address of the person leaving a comment.
	public  $datePosted;                                         // Date the comment was created.
	private $namePattern = "/[^a-zA-Z0-9- '\.]/";
	private $titlePattern = "/[^a-zA-Z0-9- \?\.!@']/";
	private $comments;                                           // Contains the comment model object for database calls.


	public function __construct($commentArray = NULL) {
		require_once DIR_ADMIN . 'model/comments/comments-main.php';
		
		global $acl, $config;
		
		$this->articles = new Articles;
		$this->categories = new Categories;
		$this->imageDirectory = DIR_IMAGE . 'articles/';
		
		$contentFormat = getContentFormatForUser($_SESSION['userId']);
		
		if(isset($commentArray)) {
			$this->validateId($commentArray['id']);
			$this->validateAuthor($commentArray['author']);
			$this->description = $this->validateContent($commentArray['content'], $contentFormat['format_id'], TRUE);
			$this->validateState($commentArray['state']);
			$this->datePosted = $this->validateCreateDate($commentArray['date']);
			$this->validateEmail($commentArray['email']);
			$this->validateUrl($commentArray['website']);
			$this->comment = array( 
				'id'              => $this->id,
				'author'          => $this->author,
				'createDate'      => $this->datePosted,
				'content'         => $this->content,
				'state'           => $this->state,
				'website'         => $this->url,
				'email'           => $this->email
			);
		}
	}

	public function validateAuthor($authorName) {
		// Trim any whitespace.
		$authorName = trim($authorName);
		// Check to ensure the name is not empty.
		if(empty($authorName)) $this->errors[] = "The created by field is required and cannot be left blank.";
		// Check to ensure that the author's name is less than 255 characters.
		if(strlen($authorName) > 255) $this->errors[] = "The created by field cannot be longer than 255 characters.";
		// Check to ensure that the name only contains letters, numbers, spaces, or hyphens.
		if(preg_match($this->namePattern, $authorName) == 1) {
			$this->errors[] = "The author's name can only contain letters, numbers, spaces, apostrophes, periods, or hyphens.";
		}
		// Assign the authorName value to the author property.
		$this->author = $authorName;
		return $authorName;
	}

}
