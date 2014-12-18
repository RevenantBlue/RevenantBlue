<?php
namespace RevenantBlue\Site;
use RevenantBlue\ACL;
use RevenantBlue;

// Require the model and global functions
require_once DIR_APPLICATION . 'model/users/users-main.php';
require_once DIR_APPLICATION . 'controller/users/users-validation.php';
require_once DIR_APPLICATION . 'controller/menus/menus-c.php';

//Initialize Session Handler
if(defined('REDIS_SESSIONS') && REDIS_SESSIONS === TRUE) {
	$handler = new \RevenantBlue\RedisSessionHandler;
	session_set_save_handler($handler, TRUE);
} else {
	$handler = new \RevenantBlue\_SessionHandler;
	session_set_save_handler($handler, TRUE);
}

// Force HTTPS
if($globalSettings['frontend_ssl']['value'] || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443) {
	forceHTTPS();
}
// Start the session if there are no session errors, session start should be called before any whitespace.
/*ENABLE WITH HTTPS
**ini_set('session.cookie_secure',1);
*/
//ini_set('session.cookie_httponly',1);
//ini_set('session.use_only_cookies',1);

if($globalSettings['frontend_login_limit']['value'] !== 'none') {
	ini_set('session.cookie_lifetime', (int)$globalSettings['frontend_login_limit']['value']);
} else {
	ini_set('session.cookie_lifetime', 640000000);
}

session_name('frontend');
session_start();

// Instantiate the user model
$users = new Users;

// Instantiate the ACL model
$acl = new ACL;

// Set the session's section if they're a new, anonymous user
if(!isset($_SESSION['userId']) && !isset($_SESSION['tempId']) && REDIS_SESSIONS === FALSE) {
	// Handle anonymous users and make their information is logged for use.
	$_SESSION['tempId'] = mt_rand(100000, 9999999);
	session_write_close();
	$users->setSessionSection(session_id(), 'frontend');
	// Set the JSON object of their data for use on the forums.
	$userData = '{ "username": "Anonymous", "userId": "' . $_SESSION['tempId'] . '" }';
	$users->setSessionUserData(session_id(), $userData);
	ini_set('session.cache_limiter', null);
	session_start();
}

// Get the form key for the user.
$csrfToken = getCsrfToken();

// Set the login attempts session variable if it has not been set yet.
if(!isset($_SESSION['loginAttempts'])) {
	$_SESSION['loginAttempts'] = 0;
}

// If the current user has been inactive for more than 15 minutes end their session and log them out and bring them to the login error page
if($globalSettings['frontend_login_limit']['value'] !== 'none') {
	if(isset($_SESSION['lastActivity']) && (time() - $_SESSION['lastActivity']) > (int)$globalSettings['frontend_login_limit']['value']) {
		clearSession();
		$_SESSION['errors'] = "You have been logged out due to inactivity.";
		header('Location: ' . HTTP_SERVER . 'login', TRUE, 302);
		exit;
	}
}

// Update time of last user activity if user is logged in currently.
session_write_close();
if(isset($_SESSION['username'])) {
	// Set the user's last activity date and time to the database.
	$users->setLastActivity($_SESSION['userId'], session_id());
	// Store the time as a session variable for timeout testing.
	$_SESSION['lastActivity'] = time();
} else {
	$users->setLastActivity('', session_id());
	$_SESSION['lastActivity'] = time();
}
ini_set('session.cache_limiter', null);
session_start();

// Update time of last user activity if user is logged in currently.
if(isset($_SESSION['username']) && isset($_SESSION['userId'])) {
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
		$rdh->zadd(PREFIX . 'frontend-logged-users-online', time(), '{ 
				"username": "' . $_SESSION['username'] . '", 
				"userId": "' . $_SESSION['userId'] . '",
				"usernameAlias": "' . $_SESSION['usernameAlias'] . '" 
			}'
		);
		
		$rdh->zadd(PREFIX . 'frontend-online-users', time(), $_SESSION['username']);
	}
} else {
	if(REDIS_SESSIONS === TRUE) {
		// Update the user's online status.
		$rdh = $redis->loadRedisHandler();
		$rdh->zadd(PREFIX . 'frontend-anon-users-online', time(), '{ "username": "Anonymous", "userId": "' . $_SESSION['tempId'] . '" }');
	}
}

// If the session roles are not set assign the anonymous role to the user.
if(!isset($_SESSION['roles'])) {
	$anonRole = $acl->getRoleByName('Anonymous');
	$_SESSION['roles'][] = $anonRole['id'];
}

// If the user is logged in and they are attempting to visit the login page redirect them to the homepage.
if(isset($_GET['controller']) && $_GET['controller'] === 'login' && !empty($_SESSION['userId'])) {
	header('Location: ' . HTTP_SERVER, TRUE, 302);
	exit;
}

// Handle global site GET requests
if($_SERVER['REQUEST_METHOD'] === 'GET') {
	
	// Set the previous URL for all pages except the login page.
	// This allows the user to be redirected to the page they were on before logging into the system.
	// Prevent the login and register pages from being stored.
	if((isset($_GET['controller']) && $_GET['controller'] !== 'login' && $_GET['controller'] !== 'register')
	|| !isset($_GET['controller'])) {
		
		$_SESSION['previousURL'] = $_SERVER['REQUEST_URI'];
		
	}
	
	// Logged in specific logic for users.
	if(isset($_SESSION['userId'])) {
		// Load the number of notifications the user has on their account.
		$_SESSION['numOfUnreadNotifs'] = $users->countNotificationsForUser($_SESSION['userId'], TRUE);
		$_SESSION['numOfUnreadMsgs'] = $users->countInboxMessagesForUser($_SESSION['userId'], TRUE);
	}
	
	if(isset($_GET['logout']) && $_GET['logout'] === 'true') {
		clearSession();
		if(isset($_GET['action']) && $_GET['action'] === 'to-index') {
			header('Location: ' . HTTP_SERVER, TRUE, 302);
			exit;
		} else {
			// Remove the logout paramater from the querystring.
			$requestURI = str_replace('&logout=true', '', $_SERVER['REQUEST_URI']);
			header('Location: ' . $requestURI, TRUE, 302);
			exit;
		}
	}
	
	if(isset($_GET['appRequest'])) {
		if(!isset($_GET['csrfToken'])) {
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		} else {
			$legitCsrfToken = checkcsrfToken();
			if($legitCsrfToken == FALSE) {
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			}
		}
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
}

// Handle global site POST requests.
if($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	// If the form key was not set with the POST request discard the form.
	if((!isset($_POST['csrfToken']) && !isset($_GET['csrfToken'])) && !isset($_POST['appRequest']) && !isset($_GET['appRequest'])) {
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	}

	// Ensure that the csrfToken submitted with the POST request is legitimate.  If it is not, discard the form.
	if(isset($_POST['csrfToken']) || isset($_GET['csrfToken'])) {
		$legitcsrfToken = checkcsrfToken();
		if($legitcsrfToken == FALSE) {
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
	}

	// Handle Login Event
	if(isset($_POST['submitLogin'])) {
		if(!empty($_POST['email']) && !empty($_POST['password'])) {
			$loginValidation = new LoginValidation($_POST['email'], $_POST['password'], 'email');
		} else {
			$_SESSION['errors'] = 'Invalid email address or password';
			header('Location: ' . HTTP_SERVER . 'login', TRUE, 302);
			exit;
		}
		// If no errors have occured, return true.
		if(!empty($loginValidation->errors)) {
			$_SESSION['errors'] = $loginValidation->errors;
			header('Location: ' . HTTP_SERVER . 'login', TRUE, 302);
			exit;
		} else {
			// MySQL / Redis Session specifics
			if(defined(REDIS_SESSIONS) && REDIS_SESSIONS === TRUE) {
				// Set the user's session id in the redis database for cross site cookie sharing.
				$usersRedis->setUserSessionCookie($_SESSION['username'], session_id());
			}
			if(!empty($_SESSION['previousURL'])) {
				if($_SESSION['previousURL'] === HTTP_SERVER . 'register') {
					header('Location: ' . HTTP_SERVER, TRUE, 302);
					exit;
				} else {
					header('Location: ' . $_SESSION['previousURL'], TRUE, 302);
					exit;
				}
			} else {
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			}
		}
	}
	
	// Handle Log Out Event
	if(isset($_POST['action']) && $_POST['action'] === 'logout' && isset($_POST['csrfToken'])) {
		clearSession();
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	}
	
	// New user request
	if(isset($_POST['registerUser'])) {

		$user['firstName'] = isset($_POST['firstName']) ? $_POST['firstName'] : '';
		$user['lastName'] = isset($_POST['lastName']) ? $_POST['lastName'] : '';
		$user['username'] = isset($_POST['username']) ? $_POST['username'] : '';
		$user['password'] = isset($_POST['password']) ? $_POST['password'] : '';
		$user['confirmPassword'] = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
		$user['email'] = isset($_POST['email']) ? $_POST['email'] : '';
		$user['systemEmail'] = isset($_POST['systemEmail']) ? $_POST['systemEmail'] : '';
		$user['roles'] = array(4 => 'on');
		
		// Load the user validation class.
		$newUser = new NewUserValidation($user);
		
		// Apply frontend options to the account.
		$frontendOptions = $users->getOptionsByLocation('frontend');
		
		foreach($frontendOptions as $frontendOption) {
			$users->insertUserOption($newUser->id, $frontendOption['id']);
		}
		if(!empty($newUser->id) && empty($newUser->errors)) {
			$_SESSION['success'] = 'Your account has been created successfully.
			                        An email has been sent to the email address you provided.
			                        Please click the link in the verification email to login to your account.';
		} else {
			$_SESSION['errors'] = $newUser->errors;
			header('Location: ' . HTTP_SERVER . 'register', TRUE, 302);
			exit;
		}
		
		header('Location: ' . HTTP_SERVER . 'login', TRUE, 302);
		exit;
	}
	
	// AJAX app requests
	if(isset($_POST['appRequest'])) {
		
		$appReqObj = json_decode($_POST['appRequest']);
		if($appReqObj->type === 'global') {
			
			// Check for duplicate username
			if($appReqObj->action === 'check duplicate username') {
				$usernameExists = $users->getUserByUsername($appReqObj->username);
				if(empty($usernameExists)) {
					echo "true";
				} else {
					echo "false";
				}
			// Check for duplicate email
			} elseif($appReqObj->action === 'check duplicate email') {
				$emailExists = $users->getUserByEmail($appReqObj->email);
				if(empty($emailExists)) {
					echo "true";
				} else {
					echo "false";
				}
			} elseif($appReqObj->action === 'resend-email-verification') {
				// Resend email confirmation.
				if(!empty($appReqObj->userId)) {
					$userData = $users->getUserData($appReqObj->userId);
					
					// Delete previous email verification code.
					$users->deleteCode($userData['id'], 'verify email');
					
					$userValidation = new UserValidation;
					
					$emailTemplate = $userValidation->parseEmailTemplate('email-verification', $userData['id'], 'email verification');
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
					
					// Manually send the e-mail verification to ensure prompt delivery to the new user
					require_once DIR_SYSTEM . 'cron-jobs/send-mail.php';
				}
			} elseif($appReqObj->action === 'reset password') {
				// Reset password.
				$userValidation = new UserValidation;
				$usernameEmail = isset($appReqObj->usernameEmail) ? $appReqObj->usernameEmail : '';
				$isEmail = $users->getUserByEmail($usernameEmail);
				$isUsername = $users->getUserByUsername($usernameEmail);
				if($isEmail || $isUsername) {
					if(!empty($isEmail)) {
						$userData = $isEmail;
					} else {
						$userData = $isUsername;
					}
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
						die('{
								"jsonrpc" : "2.0",
								"error" : {
									"code": "",
									"message": ""
								},
								"result" : {
									"message": "An email has been sent to ' . hsc($userData['email']) . ' with instructions on how to reset your password."
								},
								"id" : "id"
							 }'
						);
					}
				} else {
					die('{"jsonrpc" : "2.0", "error" : {"code": "", "message": "Username or email does not exist."}, "id" : "id"}');
				}
			}
			// Exit here to allow for other appRequests to execute.
			exit;
		}
	}
}

function siteCleanUp() {
	$_SESSION['userIdError'] = '';
	unset($_SESSION['userIdError']);
}
