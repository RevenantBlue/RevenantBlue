<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/common/global-validation.php';
require_once DIR_ADMIN . 'controller/common/content-filtering.php';
require_once DIR_ADMIN . 'model/forums/forums-main.php';

class ForumValidation extends GlobalValidation {
	
	public $forum;
	public $id;
	public $alias;
	public $description;
	public $parentId;
	public $perms;
	public $rolePerms;
	
	public function __construct($forum = NULL, $section = FALSE) {
		global $acl;
		global $config;
		
		require_once DIR_ADMIN . 'model/forums/forums-main.php';
		$this->forums = new Forums;
		
		// Get highest ranked role, true means to return the role's id instead of its name.
		$highestRoleId = $acl->getHighestRankedRoleForUser($_SESSION['userId'], TRUE);
		// Get the content filter.
		$contentFormat = $config->getContentFilterForRole($highestRoleId);

		if($forum && $section === FALSE) {
			if(!empty($forum['id'])) {
				$this->id = $this->validateId($forum['id']);
			}
			$this->title = $this->validateTitle($forum['title'], 'Forum Name');
			$this->alias = $this->validateAlias($forum['alias']);
			$this->description = $this->validateContent($forum['description'], $contentFormat['id'], TRUE);
			$this->parentId = $this->validateParentId($forum['parentId']);
			$this->perms = $forum['perms'];
			$this->rolePerms = $forum['rolePerms'];
			$this->weight = (int)$forum['weight'];
			$this->archived = (int)$forum['archived'];
			$this->forum = array(
				'id'          => $this->id,
				'title'       => $this->title,
				'alias'       => $this->alias,
				'description' => $this->description,
				'parentId'    => $this->parentId,
				'perms'       => $this->perms,
				'rolePerms'   => $this->rolePerms,
				'weight'      => $this->weight,
				'archived'    => $this->archived,
				'userId'      => $_SESSION['userId']
			);
		} else if($forum && $section === TRUE) {
			if(!empty($forum['id'])) {
				$this->id = $this->validateId($forum['id']);
			}
			$this->title = $this->validateTitle($forum['title'], 'Section Name');
			$this->alias = $this->validateAlias($forum['alias']);
			$this->description = $this->validateContent($forum['description'], $contentFormat, TRUE);
			
			$this->forum = array(
				'id'          => $this->id,
				'title'       => $this->title,
				'alias'       => $this->alias,
				'description' => $this->description,
				'rolePerms'   => $this->rolePerms,
				'userId'      => $_SESSION['userId']
			);
		}
	}
	
	public function validateParentId($parentId) {
		if(empty($parentId)) {
			$this->errors[] = 'No parent forum was selected. Please select a parent forum from the drop down list or create a section instead.';
		}
		return $parentId;
	}
	
	protected function checkForDuplicateAlias($alias) {
		$forum = $this->forums->getForumByAlias($alias);
		$id = $forum['id'];
		if(!empty($id)) {
			if(isset($this->id) && $this->id === $id) {
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			return FALSE;
		}
	}
}
