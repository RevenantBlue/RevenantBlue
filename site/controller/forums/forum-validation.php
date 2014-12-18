<?php
namespace RevenantBlue\Site;

require_once(DIR_APPLICATION . 'controller/common/global-validation.php');
require_once(DIR_APPLICATION . 'controller/common/content-filtering.php');
require_once(DIR_APPLICATION . 'model/forums/forums-main.php');

class ForumValidation extends GlobalValidation {
	
	public $forum;
	public $id;
	public $alias;
	public $description;
	public $parentId;
	public $perms;
	public $rolePerms;
	
	public function __construct($forumArr = NULL, $section = FALSE) {
		require_once(DIR_ADMIN . 'model/forums/forums-main.php');
		$this->forums = new Forums;
		if($forumArr && $section === FALSE) {
			if(!empty($forumArr['id'])) {
				$this->id = $this->validateId($forumArr['id']);
			}
			$this->title = $this->validateTitle($forumArr['title'], 'Forum Name');
			$this->alias = $this->validateAlias($forumArr['alias']);
			$this->description = $this->validateDescription($forumArr['description']);
			$this->parentId = $this->validateParentId($forumArr['parentId']);
			$this->perms = $forumArr['perms'];
			$this->rolePerms = $forumArr['rolePerms'];
			$this->weight = (int)$forumArr['weight'];
			$this->archived = (int)$forumArr['archived'];
			$this->userId = $forumArr['userId'];
			
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
				'userId'      => $this->userId
			);
		} else if($forumArr && $section === TRUE) {
			if(!empty($forumArr['id'])) {
				$this->id = $this->validateId($forumArr['id']);
			}
			$this->title = $this->validateTitle($forumArr['title'], 'Section Name');
			$this->alias = $this->validateAlias($forumArr['alias']);
			$this->description = $this->validateDescription($forumArr['description']);
			$this->userId = $forumArr['userId'];
			
			$this->forum = array(
				'id'          => $this->id,
				'title'       => $this->title,
				'alias'       => $this->alias,
				'description' => $this->description,
				'rolePerms'   => $this->rolePerms,
				'userId'      => $this->userid
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

class ForumProfileValidation extends GlobalValidation {
	
	public  $id;
	public  $profile;
	public  $timezone;
	public  $showPostsTopics;
	public  $birthday;
	public  $gender;
	public  $location;
	public  $interests;
	public  $contacts;
	public  $aboutMe;
	private $users;
	
	public function __construct($profileArr) {
		global $forums;
		global $acl;
		global $config;
		// Get highest ranked role, true means to return the role's id instead of its name.
		$highestRoleId = $acl->getHighestRankedRoleForUser($_SESSION['userId'], TRUE);
		// Get the content filter.
		$contentFormat = $config->getContentFilterForRole($highestRoleId);
		
		$this->users = new Users;
		$this->id = $_SESSION['userId'];
		$this->timezone = $this->validateTimezone($profileArr['timezone']);
		$this->showPostsTopics = (int)$profileArr['privacy']['show_posts_topics'];
		$this->showFriends = (int)$profileArr['showFriends'];
		$this->authorizeFriends = (int)$profileArr['authorizeFriends'];
		$this->birthday = $this->validateBirthday($profileArr['birthday']);
		$this->gender = $this->validateGender($profileArr['gender']);
		$this->location = $this->validateString($profileArr['location'], 50, 'Location must be less than or equal to 50 characters');
		$this->interests = $this->validateString($profileArr['interests'], 100, 'Interests must be less than or equal to 100 characters');
		$this->contacts = $profileArr['contacts'];
		$this->aboutMe = $this->validateContent($profileArr['aboutMe'], $contentFormat, TRUE); // TRUE lets the content pass even if no content was provided.
		
		$this->profile = array(
			'id'               => $this->id,
			'timezone'         => $this->timezone,
			'showPostsTopics'  => $this->showPostsTopics,
			'showFriends'      => $this->showFriends,
			'authorizeFriends' => $this->authorizeFriends,
			'birthday'         => $this->birthday,
			'gender'           => $this->gender,
			'location'         => $this->location,
			'interests'        => $this->interests,
			'contacts'         => $this->contacts,
			'aboutMe'          => $this->aboutMe
		);
	}
	
	protected function validateTimezone($timezone) {
		$timezone = (int)$timezone;
		if($timezone < -12 || $timezone > 14) {
			$this->errors[] = 'Invallid timezone.';
		}
		$this->timezone = $timezone;
		return $this->timezone;
	}
	
	protected function validateBirthday($birthday) {
		if(isset($birthday['day']) && isset($birthday['month']) && isset($birthday['year'])) {
			$this->birthday = strtotime($birthday['month'] . '/' . $birthday['day'] . '/' . $birthday['year']);
			// Create a MySQL datetime format for database storage.
			$this->birthday = date("Y-m-d H:i:s", $this->birthday);
			return $this->birthday;
		} else {
			$this->errors[] = 'Invalid birthday.';
		}
	}
	
	protected function validateGender($gender) {
		if(empty($gender) || $gender === 'm' || $gender === 'f') {
			$this->gender = $gender;
			return $this->gender;
		} else {
			$this->errors[] = 'Invalid gender.';
		}
	}
}
