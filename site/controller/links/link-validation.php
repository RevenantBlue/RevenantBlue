<?php
namespace RevenantBlue\Site;

require_once(DIR_APPLICATION . 'controller/common/global-validation.php');

class LinkValidation extends GlobalValidation {
	
	public  $link = array();
	public  $id;
	public  $title;
	public  $url;
	public  $alias;
	public  $description;
	public  $categoryIds;
	public  $target;
	public  $author;
	public  $state;
	public  $rel;
	public  $weight;
	private $links;

	public function __construct($link) {
		
		global $acl;
		global $config;
		
		// Instantiate the link model
		$this->links = new Links;
		$this->imageDirectory = DIR_IMAGE . 'links/';
		
		// Get highest ranked role, true means to return the role's id instead of its name.
		$highestRoleId = $acl->getHighestRankedRoleForUser($_SESSION['userId'], TRUE);
		// Get the content filter.
		$contentFormat = $config->getContentFilterForRole($highestRoleId);
		
		$this->id = isset($link['id']) ? (int)$link['id'] : '';
		$this->title = $this->validateTitle($link['name'], 'link name');
		$this->url = $this->validateString($link['url'], 255, 'The URL must be less than or equal to 255 characters');
		$this->description = $this->validateContent($link['description'], $contentFormat['format_id'], TRUE);
		$this->categoryIds = $link['categories'];
		$this->target = $this->validateTarget($link['target']);
		$this->author = $link['author'];
		$this->published = $this->validateState($link['published']);
		$this->rel = trim($link['rel']);
		$this->image = $this->validateString($link['image'], 255, 'An error occurred while creating the image file name.');
		$this->imageAlt = $this->validateString($link['imageAlt'], 255, 'The image alt attribute must be less than 255 characters.');
		$this->imagePath = $this->validateString($link['imagePath'], 255, 'Image path cannot exceed 255 characters');
		$this->weight = (int)$link['weight'];
		
		$this->link = array(
			'id'          => $this->id,
			'name'        => $this->title,
			'url'         => $this->url,
			'description' => $this->description,
			'categoryIds' => $this->categoryIds,
			'target'      => $this->target,
			'author'      => $this->author,
			'published'   => $this->published,
			'rel'         => $this->rel,
			'image'       => $this->image,
			'imageAlt'    => $this->imageAlt,
			'imagePath'   => $this->imagePath,
			'weight'      => $this->weight
		);
	}
	
	public function checkForDuplicateAlias($alias) {
		$aliasExists = $this->links->getLinkByAlias($alias);
		if(!empty($aliasExists)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public function validateTarget($target) {
		if($target === '_none' || $target === '_top' || $target === '_blank') {
			$this->target = $target;
			return $target;
		} else {
			$this->target = $target = '_none';
			return $target;
		}
	}
}

class LinkCategoryValidation extends GlobalValidation {
	
	public  $linkCategory;
	public  $id;
	public  $name;
	public  $alias;
	public  $description;
	public  $createdBy;
	public  $orderOfItem;
	private $links;
	
	public function __construct($linkCategory) {
		global $acl;
		global $config;
		
		// Instantiate the link model
		$this->links = new Links;
		
		// Get highest ranked role, true means to return the role's id instead of its name.
		$highestRoleId = $acl->getHighestRankedRoleForUser($_SESSION['userId'], TRUE);
		// Get the content filter.
		$contentFormat = $config->getContentFilterForRole($highestRoleId);

		$this->linkCategory['id'] = isset($linkCategory['id']) ? (int)$linkCategory['id'] : '';
		$this->linkCategory['name'] = $this->validateTitle($linkCategory['name'], 'link category name');
		$this->linkCategory['alias'] = $this->validateAlias($linkCategory['alias'], TRUE);
		$this->linkCategory['description'] = $this->validateContent($linkCategory['description'], $contentFormat, TRUE);
		$this->linkCategory['orderOfItem'] = $this->getOrderOfItem();
	
	}
	
	public function checkForDuplicateAlias($alias) {
		$linkCategory = $this->links->getLinkCategoryByAlias($alias);
		if(!empty($linkCategory['id']) && !empty($this->linkCategory['id'])) {
			if((int)$this->linkCategory['id'] === (int)$linkCategory['id']) {
				return FALSE;
			} else {
				return TRUE;
			}
		} elseif(!empty($linkCategory['id'])) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public function getOrderOfItem() {
		$this->orderOfItem = $this->links->countLinkCategories() + 1;
		
		return $this->orderOfItem;
	}
}
