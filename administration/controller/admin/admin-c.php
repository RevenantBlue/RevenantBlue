<?php
namespace RevenantBlue\Admin;
use RevenantBlue;

// Require the model and global functions
require_once DIR_ADMIN . 'model/users/users-main.php';
require_once DIR_ADMIN . 'controller/users/users-validation.php';

//Initialize Session Handler
if(defined('REDIS_SESSIONS') && REDIS_SESSIONS === TRUE) {
	$handler = new RevenantBlue\RedisSessionHandlerAdmin;
	session_set_save_handler($handler, TRUE);
} else {
	$handler = new RevenantBlue\_SessionHandler;
	session_set_save_handler($handler, TRUE);
}

// Force HTTPS
if((int)$globalSettings['backend_ssl']['value'] === 1 || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443) {
	forceHTTPS();
}

// Start the session if there are no session errors.
ini_set('session.cookie_httponly',1);
ini_set('session.use_only_cookies',1);

// Get the frontend login limit for use when setting a multidomain cookie.
$backendLimit = $globalSettings['backend_login_limit']['value'] === 'none' ? 31556940 : (int)$globalSettings['backend_login_limit']['value'];

session_name('backend');
session_set_cookie_params($backendLimit, '/', '.' . HOST_NAME);
session_start();

// Instantiate the user model
$users = new Users;

// Instantiate the ACL model
$acl = new RevenantBlue\ACL;

// Get the form key for the user.
$csrfToken = getCsrfToken();

// Set the userOptions variable to an empty array.
if(!isset($userOptions)) {
	$userOptions = array();
}

if(!isset($optionsForPage)) {
	$optionsForPage = array();
}

// Check for errors after an admin refresh.
if(isset($_GET['error'])) {
	if($_GET['error'] === 'accessDenied') {
		$_SESSION['errors'] = "You do not have permission to access this resource.";
	} elseif($_GET['error'] === 'timeout') {
		$_SESSION['errors'] = "You have been logged out due to inactivity.";
	}
}

// If the current user has been inactive for more than the specified limit 
// end their session and log them out and bring them to the login error page
if($globalSettings['backend_login_limit']['value'] !== 'none') {
	if(isset($_SESSION['lastActivity']) && (time() - (int)$_SESSION['lastActivity']) > (int)$globalSettings['backend_login_limit']['value']) {
		clearSession();
		session_start();
		header('Location: ' . HTTP_ADMIN . '?action=refreshAdmin&error=timeout', TRUE, 302);
		exit;
	}
}

// If the user hasn't been logged in and they are not at the login page clear the current session and throw an accses denied error and redirect them to the login page.
if(!isset($_SESSION['username']) && !in_array(HTTP_ADMIN, selfURL())) {
	if(isset($_GET['action']) && $_GET['action'] === 'lostpassword') {

	} elseif(isset($_GET['action']) && $_GET['action'] === 'refreshAdmin') {

	} elseif(isset($_GET['access']) && $_GET['access'] === 'u34p') {

	} else {
		// Skip session reset and error generation if an error is already present.
		if(!isset($_SESSION['username']) && !isset($_GET['error'])) {
			clearSession();
			session_start();
			$_SESSION['errors'] = "You do not have permission to access this resource.";
		}
		if(!isset($_GET['error'])) {
			header('Location: ' . HTTP_ADMIN . '?action=refreshAdmin&error=accessDenied', TRUE, 302);
			exit;
		} else {
			header('Location: ' . HTTP_ADMIN, TRUE, 302);
			exit;
		}
	}
}

// If the user is logged in but their role does not allow them to access the backend clear the current session and redirect them to the login page.
if(isset($_SESSION['username']) && !aclVerify('backend admin')) {
	clearSession();
	session_start();
	header('Location: ' . HTTP_ADMIN . '?action=refreshAdmin?error=accessDenied', TRUE, 302);
	exit;
}

// Update time of last user activity if user is logged in currently.
if(isset($_SESSION['userId']) && isset($_SESSION['username'])) {
	$_SESSION['lastActivity'] = time();
	// If Redis Sessions are being used update the key to reset the expiration time.
	if(REDIS_SESSIONS === TRUE) {
		if($globalSettings['session_ttl']['value'] !== 'none') {
			$redis->expire(PREFIX . $_SESSION['userId'], (int)$globalSettings['session_ttl']['value']);
		} else {
			$redis->expire(PREFIX . $_SESSION['userId'], 31556900);
		}
		// Update the user's online status.
		$rdh = $redis->loadRedisHandler();
		$rdh->zadd(PREFIX . 'backend-logged-users-online', time(), $_SESSION['username']);
	}
} else {
	if(REDIS_SESSIONS === TRUE) {
		// Update the user's online status.
		$rdh = $redis->loadRedisHandler();
		$rdh->zadd(PREFIX . 'backend-anon-users-online', time(), $_SESSION['tempId']);
	}
}

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	// If in maintenance mode only allow users who have the appropriate permissions.
	if(isset($_GET['access']) && $_GET['access'] === ACCESS_CODE) {
		$_SESSION['mAccess'] = true;
	} elseif(!empty($globalSettings['maintenance_mode']['value']) && !aclVerify('use site in maintenance mode') && !isset($_SESSION['mAccess'])) {
		echo hsc($globalSettings['maintenance_message']['value']);
		exit;
	}
	
	// Logged in specific logic for users.
	if(isset($_SESSION['userId'])) {
		// Load the number of notifications the user has on their account.
		$_SESSION['numOfUnreadNotifs'] = $users->countNotificationsForUser($_SESSION['userId'], TRUE);
		$_SESSION['numOfUnreadMsgs'] = $users->countInboxMessagesForUser($_SESSION['userId'], TRUE);
	}
}

// Handle global admin POST requests.
if($_SERVER['REQUEST_METHOD'] === 'POST') {
	// If the form key was not set with the POST request discard the form.
	if((!isset($_POST['csrfToken']) && !isset($_GET['csrfToken'])) && !isset($_POST['adminRequest']) && !isset($_POST['adminRequestGlobal'])) {
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	}

	// Ensure that the csrfToken submitted with the POST request is legitimate.  If it is not, discard the form.
	if(isset($_POST['csrfToken']) || isset($_POST['adminRequest']) || isset($_POST['csrfToken']) || isset($_GET['csrfToken'])) {
		$legitcsrfToken = checkcsrfToken();
		if($legitcsrfToken === FALSE) {
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
	}

	// Handle login Event.
	if(isset($_POST['submitLogin'])) {
		$loginValidation = new LoginValidation($_POST['username'], $_POST['password']);

		if(isset($loginValidation->errors)) {
			$_SESSION['errors'] = $loginValidation->errors;
		}
		header('Location: ' .  HTTP_ADMIN, TRUE, 302);
		exit;
	}
	// Handle logout Event.
	if(isset($_POST['logout']) && isset($_POST['csrfToken'])) {
		clearSession();
		header('Location: ' . HTTP_ADMIN, TRUE, 302);
		exit;
	}
	// Lost password request.
	if(isset($_POST['submitLostPass']) && isset($_POST['lostPassword'])) {
		$userValidation = new UserValidation;
		$isEmail = $users->getUserByEmail($_POST['lostPassword']);
		$isUsername = $users->getUserByUsername($_POST['lostPassword']);
		if($isEmail || $isUsername) {
			if(!empty($isEmail)) {
				$userData = $isEmail;
			} else {
				$userData = $isUsername;
			}
			// Include the swift mailer library
			require_once DIR_SYSTEM . 'library/swiftmailer/lib/swift_required.php';
			$emailTemplate = $userValidation->parseEmailTemplate('password-recovery', $userData['id'], 'password reset');
			$sentEmail = $userValidation->sendUserEmail(
				$emailTemplate['subject']
			  , $emailTemplate['body']
			  , $userData['email']
			  , $userData['first_name']
			  , $userData['last_name']
			  , $globalSettings['system_email']['value']
			  , $globalSettings['site_name']['value']
			  , $userData['id']
			);
			if($sentEmail) {
				$_SESSION['success'] = 'An email has been sent to ' . hsc($userData['email']) . ' with instructions on how to reset your password.';
				header('Location: ' . HTTP_ADMIN . '?action=lostpassword', TRUE, 302);
				exit;
			}
		} else {
			$_SESSION['errors'][] = "Username or email does not exist.";
			header('Location: ' . HTTP_ADMIN . '?action=lostpassword', TRUE, 302);
			exit;
		}
	}
}
if($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset($_GET['adminRequest'])) {
		if(!isset($_GET['csrfToken'])) {
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		} else {
			$legitcsrfToken = checkcsrfToken();
			if($legitcsrfToken == FALSE) {
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			}
		}
	}
}
// Dashboard controller
if(isset($_GET['controller']) && $_GET['controller'] === 'dashboard') {
	require_once DIR_SYSTEM . 'engine/models.php';
	$articles = new Articles;
	$comments = new Comments;
	$categories = new Categories;
	$forums = new Forums;
	$links = new Links;
	$media = new Media;
	$menus = new Menus;
	$pages = new Pages;
	$tags = new tags;
	$photogallery = new PhotoGallery;
	$numOfArticles = $articles->getNumOfArticles();
	$numOfPublishedArticles = $articles->countArticlesByPublished(1);
	$numOfUnpublishedArticles = $articles->countArticlesByPublished(0);
	$numOfDraftArticles = $articles->countArticlesByPublished(2);
	$numOfPendingArticles = $articles->countArticlesByPublished(3);
	$numOfFeaturedArticles = $articles->countArticlesByFeatured(1);
	$numOfCategories = $categories->getNumOfCategories();
	$numOfAlbums = $photogallery->getNumOfRootNodes();
	$numOfPublishedAlbums = $photogallery->countAlbumsByState(1);
	$numOfUnpublishedAlbums = $photogallery->countAlbumsByState(0);
	$numOfComments = $comments->countAllComments();
	$numOfApprovedComments = $comments->countCommentsByPublished(1);
	$numOfPendingComments = $comments->countCommentsByPublished(2);
	$numOfSpamComments = $comments->countCommentsByPublished(3);
	$numOfMediaFiles = $media->countMediaLibrary();
	$numOfMediaImages = $media->countMediaByType("image");
	$numOfMediaVideos = $media->countMediaByType("video");
	$numOfUsers = $users->countUserOverview();
	$numOfEnabledUsers = $users->countUserStatus(1);
	$numOfDisabledUsers = $users->countUserStatus(0);
	$numOfPendingUsers = $users->countUserActivation(0);
	$numOfPosts = $forums->getNumOfPosts();
	$numOfTopics = $forums->getNumOfTopics();
	$numOfForums = $forums->countAllForums();
	$numOfMenus = $menus->countMenus();
	$numOfLinks = $links->countLinks();
	$numOfTags = $tags->countTags();
	$numOfPages = $pages->countPages();
	
	$failedLogins = $users->getFailedLogins(date('Y-m-d h:s:i', time() - 259200), 10, 0);
	
	if(REDIS) {
		$rdh = $redis->loadRedisHandler();
	}
	
	$activeTimeLimit = time() - (int)$globalSettings['active_user_limit']['value'];
	
	if(REDIS_SESSIONS === TRUE) {
		$backendActiveUsers['anon'] = $rdh->zcount(PREFIX . 'backend-anon-users-online', (int)$activeTimeLimit , time());
		$backendActiveUsers['loggedIn'] = $rdh->zcount(PREFIX . 'backend-logged-users-online', (int)$activeTimeLimit, time());
		$frontendActiveUsers['anon'] = $rdh->zcount(PREFIX . 'frontend-anon-users-online', (int)$activeTimeLimit, time());
		$frontendActiveUsers['loggedIn'] = $rdh->zcount(PREFIX . 'frontend-logged-users-online', (int)$activeTimeLimit, time());
	} else {
		$backendActiveUsers['anon'] = count($users->getActiveSessionsBySection('backend', time(), $globalSettings['active_user_limit']['value'], FALSE));
		$backendActiveUsers['loggedIn'] = count($users->getActiveSessionsBySection('backend', time(), $globalSettings['active_user_limit']['value'],  TRUE));
		$frontendActiveUsers['anon'] = count($users->getActiveSessionsBySection('frontend', time(), $globalSettings['active_user_limit']['value'],  FALSE));
		$frontendActiveUsers['loggedIn'] = count($users->getActiveSessionsBySection('frontend', time(), $globalSettings['active_user_limit']['value'],  TRUE));
	}
}

// Post requests for the dashboard
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['controller']) && $_GET['controller'] === 'dashboard') {
	if(isset($_POST['adminRequest'])) {
		$adminReq = json_decode($_POST['adminRequest']);
		if($adminReq->type === 'dashboard') {
			if($adminReq->action === 'clear-failed-logins') {
				$users->deleteAllFailedLogins();
			}
		}
	}
}

// Handle global ajax json requests
if(isset($_POST['adminRequestGlobal'])) {
	$adminReq = json_decode($_POST['adminRequestGlobal']);
	if($adminReq->type === 'options' && $adminReq->action === 'change') {
		if($adminReq->status === 'on') {
			$addOption = $users->insertUserOption($_SESSION['userId'], (int)$adminReq->id);
			$adminReq->success = $addOption;
		} elseif($adminReq->status === 'off') {
			$deleteOption = $users->deleteUserOption($_SESSION['userId'], (int)$adminReq->id);
			$adminReq->success = $deleteOption;
		}
	}
	echo json_encode($adminReq);
	exit;
}



// Admin Global Functions

function setNumToShow($optionId) {
	// Using the global $users model.
	global $users;

	if(isset($_GET['limit'])) {
		// Filter the GET request.
		if($_GET['limit'] != 'All') {
			$numToShow = (int)$_GET['limit'];
		} else {
			$numToShow = 'All';
		}
		$currentNumToShow = $users->getOptionValueForUser($_SESSION['userId'], $optionId);

		// If the current number to show is empty make sure the option exists for that user.
		if(empty($currentNumToShow)) {
			$users->insertUserOption($_SESSION['userId'], (int)$optionId);
		}

		// If the current number of galleries to show does not equal the new number add the updated value to the user's options.
		if(empty($currentNumToShow) || (!empty($currentNumToShow) && $currentNumToShow != $numToShow)) {
			$users->setOptionValueForUser($_SESSION['userId'], $optionId, $numToShow);
		}
	}
}
