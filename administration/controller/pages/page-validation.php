<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/common/global-validation.php';
require_once DIR_ADMIN . 'controller/common/content-filtering.php';
require_once DIR_ADMIN . 'model/categories/categories-main.php';

class PageValidation extends GlobalValidation {

	public  $errors = array();                                  // Contains an array that stores all errors that occur during validation.
	public  $page;                                               // Array containing all aspects of the current page.
	public  $id;                                                 // Contains the id for the page entry being updated.
	public  $author;                                             // Contains the author's name of the page page being posted.
	public  $pageName;
	public  $title;                                              // Contains the title of the page page being posted.
	public  $alias;                                              // Contains the alias of the page page being posted.
	public  $dateCreated;                                        // Date the page was created.
	public  $head;                                               // Contains the head of the page page being posted.
	public  $body;                                               // Contains the body of the page page being posted.
	public  $publishState;                                       // Contains the value of the published state of the page.
	public  $template;
	public  $metaKeywords;
	public  $metaDescription;
	public  $subdomain;
	public  $attribs;
	public  $contentFormat;
	private $namePattern = "/[^a-zA-Z0-9- ']/";
	private $titlePattern = "/[^a-zA-Z0-9- \?\.!@']/";
	private $pages;                                              // Contains the page model object for database calls.
	private $config;
	
	public function __construct($page = NULL, $pageUrl = NULL) {
		require_once DIR_ADMIN . 'model/pages/pages-main.php';
		
		global $config;
		
		$this->pages = new Pages;
		$this->categories = new Categories;
		$this->config = $config;
		
		if(isset($page)) {
			// Validate array elements.
			if(!empty($page['id'])) {
				$this->id = $this->validateId($page['id']);
			}
			$this->validateAuthor($page['author']);
			$this->pageName = $this->validateTitle($page['page']);
			$this->validateAlias($page['alias']);
			$this->title = $this->validateString($page['title'], 255, 'The page title must be less than 255 characters.');
			$this->dateCreated = $this->validateCreateDate($page['dateCreated']);
			$this->body = $this->validateContent($page['body'], $page['contentFormatId'], FALSE, 'You cannot create a page with an empty body.');
			$this->head = $this->validateContent($page['head'], $page['contentFormatId'], TRUE);
			$this->publishState = (int)$page['published'];
			$this->template = (int)$page['template'];
			$this->metaDescription = $page['metaDescription'];
			$this->metaKeywords = $page['metaKeywords'];
			$this->metaAuthor = $page['metaAuthor'];
			$this->metaRobots = $page['metaRobots'];
			$this->subdomain = (int)$page['subdomain'];
			$this->attribs = !empty($page['attribs']) ? $page['attribs'] : '';
			$this->contentFormat =  $page['contentFormatId'];
			
			// Build the validated page array for database inseration.
			$this->page = array( 
				'id'              => $this->id,
				'author'          => $this->author,
				'page'            => $this->pageName,
				'title'           => $this->title,
				'alias'           => $this->alias,
				'dateCreated'     => $this->dateCreated,
				'template'        => $this->template,
				'body'            => $this->body,
				'head'            => $this->head,
				'published'       => $this->publishState,
				'metaDescription' => $this->metaDescription,
				'metaKeywords'    => $this->metaKeywords,
				'metaAuthor'      => $this->metaAuthor,
				'metaRobots'      => $this->metaRobots,
				'subdomain'       => $this->subdomain,
				'contentFormat'   => $this->contentFormat
			);
		}
	}

	public function validatePublishedState($publishState) {
		// Check to ensure that the published state is not empty.
		if(empty($publishState)) {
			$this->errors[] = "The published field cannot be empty.";
		}
		// Check to ensure that the pubilshed value is a boolean type.
		if(!is_numeric($publishState)) {
			$this->errors[] = "Invalid value for the published field.";
		}
		// Assign the publishState value to the publishState property.
		$this->publishState = $publishState;
	}


	protected function checkForDuplicateAlias($alias) {
		$id = $this->pages->getIdByAlias($alias);
		if(!empty($id)) {
			if(isset($this->id) && (int)$this->id === (int)$id) {
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			return FALSE;
		}
	}
}

class PageTemplateValidation extends GlobalValidation {
	
	public  $pageTemplate;
	public  $id;
	public  $name;
	public  $alias;
	public  $description;
	private $pages;
	
	public function __construct($pageTemplate) {
		global $acl;
		global $config;
		
		// Instantiate the link model
		$this->pages = new Pages;
		
		$contentFormat = getContentFormatForUser($_SESSION['userId']);
		
		$this->id = isset($pageTemplate['id']) ? (int)$pageTemplate['id'] : '';
		$this->name = $this->validateTitle($pageTemplate['name'], 'page template name');
		$this->alias = $this->validateAlias($pageTemplate['alias'], TRUE);
		$this->description = $this->validateContent($pageTemplate['description'], $contentFormat, TRUE);
		
		
		$this->pageTemplate = array(
			'id'          => $this->id
		  , 'name'        => $this->name
		  , 'alias'       => $this->alias
		  , 'description' => $this->description
		);
	}
	
	protected function checkForDuplicateAlias($alias) {
		$existingPage = $this->pages->loadPage($alias);
		if(!empty($existingPage['id']) && !empty($this->id)) {
			if((int)$existingPage['id'] === (int)$this->id) {
				return FALSE;
			} else {
				return TRUE;
			}
		} elseif(!empty($existingPage['id'])) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
