<?php
namespace RevenantBlue\Site;

require_once DIR_APPLICATION . 'controller/common/site-c.php';

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset($_GET['controller'])) {
		if($_GET['controller'] === 'links') {
			require_once DIR_APPLICATION . 'model/links/links-main.php';
			
			$links = new Links;
			
			$numOfCategories = $links->countLinkCategories();
			$linkCategories = $links->loadCategoryOverview($numOfCategories, 0, 'order_of_item', 'ASC');
			
			foreach($linkCategories as $category) {
				$linksList[$category['category_title']] = $links->loadLinksByPublishedAndCategory((int)$category['num_of_links'], 0, 1, $category['id'], 'link_weight', 'DESC');
			}
		}
	} else {
		// If no controller has been called we're assuming the index page has loaded.

	}
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	if(isset($_POST['appRequest'])) {
		$appReq = json_decode($_POST['appRequest']);
			if($appReq->type === "contact" && $appReq->action === "email-contact") {
			require_once DIR_APPLICATION . 'controller/users/users-validation.php';
			$userValidate = new UserValidation;
			
			// Get the content filter.
			$contentFormat = $config->getContentFilterForRole(5);
			
			$name = $userValidate->validateString($appReq->name, 100, 'The name field cannot be longer than 100 characters');
			$email = $userValidate->validateEmail($appReq->email, TRUE);
			$message = $userValidate->validateContent($appReq->message, $contentFormat['id'], FALSE, 'The message field cannot be empty.');
			
			if(empty($userValidate->errors)) {
				$userValidate->sendUserEmail(
					'New contact message received at ' . $globalSettings['site_name']['value']
				  , $message
				  , $globalSettings['system_email']['value']
				  , $globalSettings['site_name']['value']
				  , ''
				  , $email
				  , $name
				  , 1
				);
			} else {
				$appReq->errors = $userValidate->errors;
			}
		}
		echo json_encode($appReq);
		exit;
	}
}
