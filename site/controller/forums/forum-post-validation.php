<?php
namespace RevenantBlue\Site;

require_once(DIR_APPLICATION . 'controller/common/global-validation.php');
require_once(DIR_APPLICATION . 'controller/common/content-filtering.php');
require_once(DIR_APPLICATION . 'model/forums/forums-main.php');

class ForumPostValidation extends GlobalValidation {
	
	public $topic;
	public $post;
	public $id;
	public $topicAlias;
	public $content;
	public $forumId;
	public $topicId;
	public $published;
	public $userId;
	public $username;
	public $usernameAlias;
	private $isTopic;
	
	public function __construct($postArr = NULL, $topic = FALSE) {
		global $acl;
		global $config;
		$this->forums = new Forums;
		
		// Get highest ranked role, true means to return the role's id instead of its name.
		if(isset($_SESSION['userId'])) {
			$highestRoleId = $acl->getHighestRankedRoleForUser($_SESSION['userId'], TRUE);
		} else {
			$highestRoleId = 5;
		}
		// Get the content filter.
		$contentFormat = $config->getContentFilterForRole($highestRoleId);

		// Validate forum post.
		if(!empty($postArr) && $topic === FALSE) {
			
			$this->isTopic = FALSE;
			
			if(!empty($postArr['id'])) {
				$this->id = $this->validateRequredId($postArr['id'], 'id');
			}
			
			$this->content = $this->validateContent($postArr['content'], $contentFormat['id'], FALSE, 'You cannot post a reply without content.');
			$this->forumId = $this->validateRequredId($postArr['forumId'], 'forumId');
			$this->topicId = $this->validateRequredId($postArr['topicId'], 'topicId');
			$this->published = isset($postArr['published']) ? (int)$postArr['published'] : '';
			$this->userId = $postArr['userId'];
			$this->username = isset($postArr['username']) ? $postArr['username'] : '';
			$this->usernameAlias = $this->validateAlias($postArr['username']);
			$this->validatePostOrder($this->topicId);
			$this->post = array(
				'id'            => $this->id,
				'content'       => $this->content,
				'forumId'       => $this->forumId,
				'topicId'       => $this->topicId,
				'published'     => $this->published,
				'userId'        => $this->userId,
				'username'      => $this->username,
				'usernameAlias' => $this->usernameAlias,
				'postOrder'     => $this->postOrder
			);
		} else if(!empty($postArr) && $topic === TRUE) {
			// Validate forum topic.
			
			$this->isTopic = TRUE;
			
			if(!empty($postArr['id'])) {
				$this->id = $this->validateRequredId($postArr['id'], 'id');
			}
			$this->title = $this->validateTitle($postArr['title'], 'Topic title');
			$this->topicAlias = $this->validateAlias($postArr['title']);
			$this->content = $this->validateContent($postArr['content'], $contentFormat['id'], FALSE, 'You cannot post a topic without content');
			$this->userId = $postArr['userId'];
			$this->username = $postArr['username'];
			$this->usernameAlias = $this->validateAlias($postArr['username']);
			$this->published = $postArr['published'];
			$this->forumId = (int)$postArr['forumId'];
			
			$this->topic = array(
				'id'            => $this->id,
				'forumId'       => $this->forumId,
				'title'         => $this->title,
				'alias'         => $this->topicAlias,
				'content'       => $this->content,
				'published'     => $this->published,
				'userId'        => $this->userId,
				'username'      => $this->username,
				'usernameAlias' => $this->usernameAlias,
				'postOrder'     => 1
			);
		}
	}
	
	protected function validateRequredId($value, $name) {
		if($name === 'id') {
			if(empty($value)) {
				$this->errors[] = 'Id is required when updating, no id was found.';
			} else {
				$this->id = (int)$value;
				return $this->id;
			}
		} elseif($name === 'forumId') {
			if(empty($value)) {
				$this->errors[] = 'No forum id provided';
			} else {
				$this->forumId = (int)$value;
				return $this->forumId;
			}
		} elseif($name === 'topicId') {
			if(empty($value)) {
				$this->errors[] = 'No topic id provided.';
			} else {
				$this->topicId = (int)$value;
				return $this->topicId;
			}
		}
	}
	
	protected function validatePostOrder($topicId) {
		if(!empty($topicId)) {
			$numOfPosts = $this->forums->countPostsForTopic($topicId);
			$this->postOrder = (int)$numOfPosts + 1;
		}
	}
	
	protected function checkForDuplicateAlias($alias) {
		$topic = $this->forums->getTopicByAlias($alias);
		$id = $topic['id'];
		if(!empty($id) && !empty($this->id)) {
			if((int)$this->id === (int)$id) {
				return FALSE;
			} else {
				return TRUE;
			}
		} elseif(!empty($id)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
