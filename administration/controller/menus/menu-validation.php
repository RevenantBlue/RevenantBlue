<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/common/global-validation.php';
require_once DIR_ADMIN . 'model/menus/menus-main.php';

class MenuValidation extends GlobalValidation {

	public  $errors;
	public  $id;
	public  $name;
	public  $alias;
	public  $url;
	public  $image;
	public  $imageAlt;
	public  $imagePath;
	public  $imageDirectory;
	public  $published;
	public  $orderOfItem;
	public  $description;
	public  $createdBy;
	public  $dateCreated;
	private $menus;

	public function __construct($menu = NULL) {
		global $acl;
		global $config;
		
		// Instantiate the menu model
		$this->menus = new Menus;
		$this->imageDirectory = DIR_IMAGE . 'menus/';
		
		// Get highest ranked role, true means to return the role's id instead of its name.
		$highestRoleId = $acl->getHighestRankedRoleForUser($_SESSION['userId'], TRUE);
		// Get the content filter.
		$contentFormat = $config->getContentFilterForRole($highestRoleId);
		
		// Build the menu array.
		if(!empty($menu['id'])) {
			$this->validateId($menu['id']);
		}
		
		$this->name = $this->validateTitle($menu['name']);
		$this->alias = $this->validateAlias($menu['alias'], TRUE);
		$this->url = $this->validateString($menu['url'], 255, 'The URL must be less than or equal to 255 characters');
		$this->parentMenu = $this->validateParent($menu['parent']);
		$this->published = $this->validateState($menu['published']);
		$this->description = $this->validateContent($menu['description'], $contentFormat, TRUE);
		$this->image = $this->validateString($menu['image'], 255, 'An error occurred while creating the image file name.');
		$this->imageAlt = $this->validateString($menu['imageAlt'], 255, 'The image alt attribute must be less than 255 characters.');
		$this->imagePath = $this->validateString($menu['imagePath'], 255, 'Image path cannot exceed 255 characters');
		$this->validateOrder();
		$this->dateCreated = $this->validateCreateDate($menu['datePosted']);
		$this->createdBy = $this->validateCreatedBy($menu['createdBy']);

		
		$this->menu = array(
			'id'              => $this->id,
			'name'            => $this->title,
			'alias'           => $this->alias,
			'url'             => $this->url,
			'parent'          => $this->parentMenu,
			'published'       => $this->published,
			'orderOfItem'     => $this->orderOfItem,
			'description'     => $this->description,
			'image'           => $this->image,
			'imageAlt'        => $this->imageAlt,
			'imagePath'       => $this->imagePath,
			'createdBy'       => $this->createdBy,
			'dateCreated'     => $this->dateCreated
		);
	}

	public function validateParent($parent) {
		if(!empty($parent)) {
			switch($parent) {
				case is_numeric($parent) && $parent !== 0:
					$parentExists = $this->menus->getMenuById($parent);
					if(empty($parentExists)) $this->errors[] = "The parent you have selected does not exist.";
					$this->parentMenu = $parent;
					break;
				case is_numeric($parent) && $parent == 0;
					$this->parentMenu = $parent;
					break;
			}
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
		if($this->parentMenu == 0) {
			$numOfRoots = count($this->menus->getRootNodes());
			$this->orderOfItem = ($numOfRoots + 1);
		} else {
			$numOfChildren = count($this->menus->getAllChildNodes($this->parentMenu));
			$this->orderOfItem = ($numOfChildren + 1);
		}
	}

	protected function checkForDuplicateAlias($alias) {
		$menu = $this->menus->getMenuByAlias($alias);
		if(!empty($menu['id']) && !empty($this->id)) {
			if((int)$this->id === (int)$menu['id']) {
				return FALSE;
			} else {
				return TRUE;
			}
		} elseif(!empty($menu['id'])) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
