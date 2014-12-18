<?php
namespace RevenantBlue\Site;

require_once(DIR_APPLICATION . 'model/categories/categories-main.php');

class CommentValidation extends GlobalValidation {
	
	public  $errors = array();                                   // Contains an array that stores all errors that occur during validation.
	public  $article;                                            // Array containing all aspects of the current article.
	public  $id;                                                 // Contains the id for the comment.
	public  $author;                                             // Contains the name of the author of the comment.
	public  $content;                                            // Contains the content of the comment.
	public  $website;                                            // Contains the website of the person leaving a comment.
	public  $email;                                              // Contains the email address of the person leaving a comment.   
	public  $createDate;                                         // Date the comment was created.
	private $namePattern = "/[^a-zA-Z0-9- '\.]/";
	private $titlePattern = "/[^a-zA-Z0-9- \?\.!@']/";                      
	private $comments;                                           // Contains the comment model object for database calls.
	 

	public function __construct($comment = NULL) {
		
		global $acl, $config;
		
		require_once(DIR_APPLICATION . 'model/comments/comments-main.php');
		
		$this->articles = new Articles;
		$this->categories = new Categories;
		$this->imageDirectory = DIR_IMAGE . 'articles/'; 
		
		// Get highest ranked role, true means to return the role's id instead of its name.
		if(isset($_SESSION['userId'])) {
			$highestRoleId = $acl->getHighestRankedRoleForUser($_SESSION['userId'], TRUE);
		} else {
			$highestRoleId = 5;
		}
		// Get the content filter.
		$contentFormat = $config->getContentFilterForRole($highestRoleId);
		
		if(isset($comment)) {
			$this->validateId($comment['id']);
			$this->validateAuthor($comment['author']);
			$this->validateContent($comment['content'], (int)$contentFormat['format_id'], FALSE, 'You cannot post an empty comment');
			$this->validateState($comment['state']);
			$this->validateCreateDate($comment['date']);
			$this->validateEmail($comment['email'], FALSE);
			$this->validateUrl($comment['website']);
			$this->comment = array( 
				'id'              => $this->id,
				'author'          => $this->author, 
				'createDate'      => $this->createDate,
				'content'         => $this->content,
				'state'           => $this->state, 
				'website'         => $this->url,
				'email'           => $this->email, 
				'ip'              => $comment['ip']
			);
		}
	}
	
	public function validateAuthor($authorName) {
		// Trim any whitespace.
		$authorName = trim($authorName);
		// Check to ensure the name is not empty.
		if(empty($authorName)) $this->errors[] = "The name field is required.";
		// Check to ensure that the author's name is less than 255 characters.
		if(strlen($authorName) > 100) $this->errors[] = "The name field cannot be longer than 100 characters.";
		// Check to ensure that the name only contains letters, numbers, spaces, or hyphens.
		if(preg_match($this->namePattern, $authorName) == 1) {
			$this->errors[] = "The name field can only contain letters, numbers, spaces, apostrophes, periods, or hyphens.";	
		}
		// Assign the authorName value to the author property. 
		$this->author = $authorName;
		return $authorName;
	}
}
