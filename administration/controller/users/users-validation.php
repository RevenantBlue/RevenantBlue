<?php
namespace RevenantBlue\Admin;
use RevenantBlue;
use RevenantBlue\MailHandler;

require_once DIR_ADMIN . 'model/users/users-main.php';
require_once DIR_ADMIN . 'controller/common/global-validation.php';

class LoginValidation {

	public  $errors = array();
	public  $errorTitle;
	private $userId;
	private $username;
	private $type;
	private $password;
	private $salt;
	private $login;
	private $groupRequirement;
	private $users;
	private $acl;
	private $globalSettings;
	private $redis;
	private $rdh;
	private static $usernameForSession;
	private static $newSalt;
	private static $accountType = 2;
	public  static $sessId;

	public function __construct($username, $password, $loginType = 'username') {
		
		global $globalSettings;
		
		$this->acl = new RevenantBlue\ACL;
		$this->users = new Users;
		$this->globalSettings = $globalSettings;
		$this->redis = new RevenantBlue\RedisCommand;
		$this->rdh = $this->redis->loadRedisHandler();
		
		if($loginType === 'username') {
			$this->loginValidation($username, $password);
		} elseif($loginType === 'email') {
			$this->emailLoginValidation($username, $password);
		}
	}

	private function loginValidation($username, $password) {
		
		global $globalSettings;
		
		// Check to see whether the username is empty.
		if(!empty($username) && empty($password)) {
			$this->errors[] = "Invalid password.";
			return FALSE;
		}
		if(empty($username) && !empty($password)) {
			$this->errors[] = "Invalid username.";
			return FALSE;
		}
		if(empty($username) && empty($password)) {
			$this->errors[] = "Invalid username and password.";
			return FALSE;
		}
		
		// Filter the username
		$this->username = trim(substr($username, 0, 50));
		
		// Retrieve the salt for the user if the username is correct.
		$this->salt = $this->users->getSalt($this->username);
		if(empty($this->salt)) {
			$this->errors[] = "Invalid username and password.";
			return FALSE;
		}
		
		// Filter the password.
		$this->password = trim(substr($password, 0, 50));
		
		// Create the hash for the password.
		$this->password = pbkdf2($this->salt . $this->password . $this->salt, $this->salt, HASH_ITERATIONS, HASH_LENGTH);
		
		// Login the user if no errors have occurred.
		if(empty($this->errors)) {
			$this->login = $this->users->loginUser($this->username, $this->password);
			if(empty($this->login)) {
				// Insert a failed login
				$this->users->insertFailedLogin($this->username, 'backend', $_SERVER['REMOTE_ADDR']);
				$this->errors[] = "Invalid username and password.";
				return FALSE;
			}
		} else {
			// Insert a failed login
			$this->users->insertFailedLogin($this->username, 'backend', $_SERVER['REMOTE_ADDR']);
			return FALSE;
		}
		
		// If user is banned do not allow them to login to the system.
		if($this->login['enabled'] == 0) {
			$this->errors[] = "Your account has been disabled and cannot login.";
			return FALSE;
		}
		
		// Get the user's ip address
		$ip = $_SERVER['REMOTE_ADDR'];
		
		// Get the previous page the user visited if available
		$previousURL = isset($_SESSION['previousURL']) ? $_SESSION['previousURL'] : '';
		
		// Update the user's IP address if logging is enabled.
		if($globalSettings['log_user_ips']['value']) {
			$this->users->updateUserIp($ip, $this->username);
		}
		
		// Set the tempId and previous session id for removal of the old session.
		$tempId = $_SESSION['tempId'];
		$prevSessionId = session_id();
		
		//  Regenerate session id upon login to prevent session hijacking, method used ensures proper session id regeneration.
		$_SESSION = array();
		session_destroy();
		session_start();
		session_regenerate_id(TRUE);
		$sid = session_id();
		$sid = session_id($sid);

		// Store the session variables that will be used to track the user through their session.
		$_SESSION['username'] = $this->username;
		$_SESSION['lastActivity'] = time();
		$_SESSION['previousURL'] = $previousURL;

		// If the user is logged in set their user id and timezone.
		if(isset($_SESSION['username']) && empty($_SESSION['userId'])) {
			$_SESSION['userId'] = $this->users->getUserId($_SESSION['username']);
			// Set the timezone for the user.
			$user = $this->users->getUserData($_SESSION['userId']);
			
			$_SESSION['usernameAlias'] = $user['username_alias'];
			$_SESSION['timezone'] = $user['timezone'];
			// Set the user's roles
			$_SESSION['roles'] = $this->acl->getRoleIdsForUser($_SESSION['userId']);
			// PHP won't let you manipulate the session's table until you call session_write_close()
			session_write_close();
			if(REDIS_SESSIONS === TRUE) {
				// Remove the user from the anonymous active users z-set and remove the old anonymous session.
				$this->rdh->zrem(PREFIX . 'backend-anon-users-online', $tempId);
				$this->rdh->del(PREFIX . 'session:' . $prevSessionId);
			} else {
				// Set the session's userId
				$this->users->setUserIdForSession($_SESSION['userId'], session_id());
				// Set the session's section
				$this->users->setSessionSection($sid, 'backend');
				// Set the JSON string for the user
				$this->users->setSessionUserData($sid, 
					'{ 
						"username": "' . $user['username'] . '", 
						"userId": "' . $user['id'] . '",
						"usernameAlias": "' . $user['username_alias'] . '" 
					}'
				);
			}
		}

		// Update the last login date for the user.
		$this->users->setLastLogin($this->username);

		return true;
	}
	
	private function emailLoginValidation($email, $password) {
		
		global $globalSettings;
		
		// Check to see whether the username is empty.
		if(!empty($email) && empty($password)) {
			$this->errors[] = "Invalid password.";
			return FALSE;
		}
		if(empty($email) && !empty($password)) {
			$this->errors[] = "Invalid email address.";
			return FALSE;
		}
		if(empty($email) && empty($password)) {
			$this->errors[] = "Invalid email address and password.";
			return FALSE;
		}
		
		// Validate the email address.
		$this->validateEmail(trim($email));
		if(!empty($this->errors)) {
			return FALSE;
		}
		
		// Get the user data by email.
		$user = $this->users->getUserByEmail($email);
		$this->username = $user['username'];
		
		// Retrieve the salt for the user if the username is correct.
		$this->salt = $this->users->getSalt($user['username']);
		if(empty($this->salt)) {
			$this->errors[] = "Invalid email address and password.";
			return FALSE;
		}
		
		// Filter the password.
		$this->password = trim(substr($password, 0, 50));
		
		// Create the hash for the password.
		$this->password = pbkdf2($this->salt . $this->password . $this->salt, $this->salt, HASH_ITERATIONS, HASH_LENGTH);
		
		// Login the user if no errors have occurred.
		if(empty($this->errors)) {
			$this->login = $this->users->loginUser($this->username, $this->password);
			if(empty($this->login)) {
				$this->errors[] = "Invalid email address and password.";
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
		// If user is banned do not allow them to login to the system.
		if($this->login['enabled'] == 0) {
			$this->errors[] = 'Your account has been disabled.';
			return FALSE;
		}
		
		// Get the user's ip address
		$ip = $_SERVER['REMOTE_ADDR'];
		
		// Update the user's IP address if logging is enabled.
		if($globalSettings['log_user_ips']['value']) {
			$this->users->updateUserIp($ip, $this->username);
		}
		
		// Get the user's id.
		$userId = $this->users->getUserId($this->username);
		
		// GEt the user's data.
		$user = $this->users->getUserData((int)$userId);
		
		// Check if user has been activated
		if(!$user['activated']) {
			// Check for e-mail verification
			if($globalSettings['new_user_email_verify']['value'] == 1) {
				$emailVerified = $this->users->getUserAccountCode($userId, 'verify email');
				if(!empty($emailVerified)) {
					$_SESSION['errors'][] = 'You need to verify your email address before before you can login.';
					$_SESSION['userIdError'] = $user['id'];
					header('Location: ' . HTTP_LOGIN . '?action=resend-email-verification', TRUE, 302);
					exit;
				}
			}
		}
		
		// Get the previous page the user visited if available
		$previousURL = isset($_SESSION['previousURL']) ? $_SESSION['previousURL'] : '';
		
		// Set the tempId for removal of old session.
		$tempId = $_SESSION['tempId'];
		$prevSessionId = session_id();
		
		//  Regenerate session id upon login to prevent session hijacking, method used ensures proper session id regeneration.
		$_SESSION = array();
		session_destroy();
		session_start();
		session_regenerate_id(true);
		$sid = session_id();
		$sid = session_id($sid);

		// Store the session variables that will be used to track the user through their session.
		$_SESSION['username'] = $this->username;
		$_SESSION['lastActivity'] = time();
		$_SESSION['previousURL'] = $previousURL;

		// If the user is logged in and the user id has not been set in the session handler; set it.
		if(isset($_SESSION['username']) && empty($_SESSION['userId'])) {
			$_SESSION['userId'] = $this->users->getUserId($_SESSION['username']);
			// Set the user's timezone
			$user = $this->users->getUserData($_SESSION['userId']);
			$_SESSION['usernameAlias'] = $user['username_alias'];
			$_SESSION['timezone'] = $user['timezone'];
			// Set the user's roles
			$_SESSION['roles'] = $this->acl->getRoleIdsForUser($_SESSION['userId']);
			// PHP won't let you manipulate the session's table until you call session_write_close()
			session_write_close();
			// Make session edits after closing session writing.
			if(REDIS_SESSIONS === TRUE) {
				// Remove the user from the anonymous active users z-set and remove the old anonymous session.
				$this->rdh->zrem(PREFIX . 'frontend-anon-users-online', $tempId);
				$this->rdh->del(PREFIX . 'session:' . $prevSessionId);
			} else {
				// Set the session's userId
				$this->users->setUserIdForSession($_SESSION['userId'], session_id());
				// Set the session's section
				$this->users->setSessionSection($sid, 'frontend');
				// Set the JSON string for the user
				$this->users->setSessionUserData($sid, 
					'{ 
						"username": "' . $user['username'] . '", 
						"userId": "' . $user['id'] . '",
						"usernameAlias": "' . $user['username_alias'] . '" 
					}'
				);
			}
		}

		// Update the last login date for the user.
		$this->users->setLastLogin($this->username);
	}

	public static function getSessionId($username) {
		$users = new Users;
		self::$usernameForSession = trim(substr($username, 0, 32));
		self::$sessId = $users->getSessionByUsername(self::$usernameForSession);
		return self::$sessId;
	}

	private function __clone() {
		// Make clone private to prevent any attempt at cloning this class.
	}
}

class UserValidation extends GlobalValidation {

	public $errors = array();
	protected $users;
	protected $config;

	public function __construct() {
		$this->config = new Config;
		$this->users = new Users;
	}
	public function loadGlobalSettings() {
		$this->globalSettings = $this->config->loadGlobalSettings();
	}
	public function validateName($name) {
		// Ensure that the name is not empty.
		if(empty($name)) $this->errors[] = "No name provided";
		return trim($name);
	}
	public function validateUsername($username) {
		// Ensure that the username is not empty.
		if(empty($username)) $this->errors[] = "No username provided";
		// Test the username to ensure proper length.
		if(strlen($username) > 32) $this->errors[] = "Your username cannot be longer than 32 characters.";
		// Test the username to ensure proper format.
		//if(preg_match('/[^a-zA-Z0-9 ]/', $username)) $this->errors[] = "The username can only contain letters numbers and spaces.";
		return trim($username);
	}
	public function validatePassword($password) {
		// Ensure that the password is not empty.
		if(empty($password)) $this->errors[] = "No password provided";
		// Test the  password to ensure proper length.
		if(strlen($password) > 32) $this->errors[] = "Your password cannot be longer than 32 characters.";
		// Test the password for a capital letter.
		if(!preg_match('/[A-Z]/', $password)) $this->errors[] = "Your password must contain at least one capital letter.";
		// Test the password for a number.
		if(!preg_match('/[0-9]/', $password)) $this->errors[] = "Your password must contain at least one number.";
		return trim($password);
	}
	public function validateRoles($roles) {
		// Ensure that at least one role is selected for the user.
		if(empty($roles)) {
			$this->errors[] = "No roles were provided, please select at least one role and try again.";
		} else {
			foreach($roles as $roleId => $status) {
				$statusCheck[] = $status;
			}
			if(!in_array('on', $statusCheck)) $this->errors[] = "No roles were provided, please select at least one role and try again.";
		}
		return $roles;
	}

	public function sendUserEmail($subject, $body, $toEmail, $toFirst, $toLast, $fromEmail, $fromName, $toId) {
		// Load the global settings.
		$this->loadGlobalSettings();
		// Require the swiftmailer library.
		require_once DIR_SYSTEM . 'library/swiftmailer/lib/swift_required.php';
		require_once DIR_SYSTEM . 'engine/mail-queue.php';
		
		$mh = new MailHandler;
		
		$email['subject'] = $subject;
		$email['body'] = "<html>\r\n<body>\r\n<div>\r\n" . nl2br($body) . "\r\n</div>\r\n</body>\r\n</html>";
		$email['to'] = $toEmail;
		$email['toFirst'] = $toFirst;
		$email['toLast'] = $toLast;
		$email['from'] = $fromEmail;
		$email['fromName'] = $fromName;
		$email['sender'] = $fromEmail;
		$email['toId'] = $toId;
		
		$mailId = $mh->insertMailToSend($email);
		
		// Manually run the send mail cronjob asynchronously to ensure the user gets their mail promptly.
		exec('php-cli /var/www/revenantblue.com/system/cron-jobs/send-mail.php > /dev/null &');
		
		return $mailId;
	}

	public function parseEmailTemplate($templateName, $recipientUserId, $action = NULL, $message = array(), $forum = array()) {
		
		global $globalSettings, $users;
		
		$userData = $this->users->getUserData($recipientUserId);
		// Session required information
		if(!empty($_SESSION['userId'])) {
			$messageSender = $this->users->getUserData($_SESSION['userId']);
		}
		$emailTemplate = $this->config->getEmailTemplate($templateName);
		// Generic code generated
		$code = tokenGenerator(16, TRUE);
		
		//var_dump($templateName);
		//print_R2($emailTemplate);
		
		// Replace the email variables with their respective values from the global settings table.
		$emailTemplate['subject'] = str_replace('[site:name]', $globalSettings['site_name']['value'], $emailTemplate['subject']);
		$emailTemplate['body'] = str_replace('[site:name]', $globalSettings['site_name']['value'], $emailTemplate['body']);
		
		$emailTemplate['subject'] = str_replace('[site:url]', '<a href="' . $globalSettings['site_address']['value'] . '">' . $globalSettings['site_address']['value'] . '</a>', $emailTemplate['subject']);
		$emailTemplate['body'] = str_replace('[site:url]', '<a href="' . $globalSettings['site_address']['value'] . '">' . $globalSettings['site_address']['value'] . '</a>', $emailTemplate['body']);
		
		$emailTemplate['subject'] = str_replace('[user:name]', $userData['username'], $emailTemplate['subject']);
		$emailTemplate['body'] = str_replace('[user:name]', $userData['username'], $emailTemplate['body']);
		
		$emailTemplate['subject'] = str_replace('[user:mail]', $userData['email'], $emailTemplate['subject']);
		$emailTemplate['body'] = str_replace('[user:mail]', $userData['email'], $emailTemplate['body']);
		
		$emailTemplate['subject'] = str_replace('[site:login-url]', '<a href="' . HTTP_LOGIN . '">' . HTTP_LOGIN . '</a>', $emailTemplate['subject']);
		$emailTemplate['body'] = str_replace('[site:login-url]', '<a href="' . HTTP_LOGIN . '">' . HTTP_LOGIN . '</a>', $emailTemplate['body']);
		
		$emailTemplate['subject'] = str_replace('[site:url-brief]', $globalSettings['site_address']['value'], $emailTemplate['subject']);
		$emailTemplate['body'] = str_replace('[site:url-brief]', $globalSettings['site_address']['value'], $emailTemplate['body']);
		
		$emailTemplate['subject'] = str_replace('[user:edit-url]', $globalSettings['site_address']['value'], $emailTemplate['subject']);
		$emailTemplate['body'] = str_replace('[user:edit-url]', $globalSettings['site_address']['value'], $emailTemplate['body']);
		
		if(!empty($message)) {
			$message['content'] = isset($message['content']) ? $message['content'] : '';
			$message['subject'] = isset($message['subject']) ? $message['subject'] : '';
			
			$emailTemplate['subject'] = str_replace('[message:from]', $_SESSION['username'], $emailTemplate['subject']);
			$emailTemplate['body'] = str_replace('[message:from]', $_SESSION['username'], $emailTemplate['body']);
			
			$emailTemplate['subject'] = str_replace('[message:subject]', $message['subject'], $emailTemplate['subject']);
			$emailTemplate['body'] = str_replace('[message:subject]', $message['subject'], $emailTemplate['body']);
			
			$emailTemplate['subject'] = str_replace('[message:content]', $message['content'], $emailTemplate['subject']);
			$emailTemplate['body'] = str_replace('[message:content]', $message['content'], $emailTemplate['body']);
		}
		
		if(!empty($forum)) {
			$forum['topicName'] = isset($forum['topicName']) ? $forum['topicName'] : '';
			$forum['postPermalink'] = isset($forum['postPermalink']) ? $forum['postPermalink'] : '';
			
			$emailTemplate['subject'] = str_replace('[forum:topic-name]', $forum['topicName'], $emailTemplate['subject']);
			$emailTemplate['body'] = str_replace('[forum:topic-name]', $forum['topicName'], $emailTemplate['body']);
			
			$emailTemplate['subject'] = str_replace('[forum:post-permalink]', $forum['postPermalink'], $emailTemplate['subject']);
			$emailTemplate['body'] = str_replace('[forum:post-permalink]', $forum['postPermaLink'], $emailTemplate['body']);
		}
		
		switch($action) {
			case 'verify email':
				$users->insertUserCode($recipientUserId, $code, 'verify email');
				$emailTemplate['body'] = str_replace('[user:one-time-login-url]', '<a href="' . rtrim(HTTP_LOGIN, '/') . '?verify=' . $code . '">' . rtrim(HTTP_LOGIN, '/') . '?verify=' . $code . '</a>', $emailTemplate['body']);
				break;
			case 'password reset':
				$users->insertUserCode($recipientUserId, $code, 'password reset');
				$emailTemplate['body'] = str_replace('[user:one-time-login-url]', '<a href="' . rtrim(HTTP_LOGIN, '/') . '?pwreset=' . $code . '">' . rtrim(HTTP_LOGIN, '/') . '?pwreset=' . $code . '</a>', $emailTemplate['body']);
				break;
			case 'admin approval':
				$users->insertUserCode($recipientUserId, $code, 'admin approval');
				$emailTemplate['body'] = str_replace('[user:one-time-login-url]', '<a href="' . rtrim(HTTP_LOGIN, '/') . '?approval=' . $code . '">' . rtrim(HTTP_LOGIN, '/') . '?approval=' . $code . '</a>', $emailTemplate['body']);
				break;
			case 'cancel account':
				$users->insertUserCode($recipientUserId, $code, 'cancel account');
				$emailTemplate['body'] = str_replace('[user:cancel-url]', '<a href="' . rtrim(HTTP_LOGIN, '/') . '?cancel=' . $code . '">' . rtrim(HTTP_LOGIN, '/') . '?cancel=' . $code . '</a>', $emailTemplate['body']);
				break;
		}
		
		// Return the email template
		return $emailTemplate;
	}

	public function validatePasswordReset($userId, $password, $confirmPassword) {
		// Ensure that the password and confirm password fields match.
		if($password !== $confirmPassword) $this->errors[] = "The password confirmation does not match the password that you have provided.";
		// Create the salt for this new user.
		$salt = base64_encode(mcrypt_create_iv(64, MCRYPT_DEV_URANDOM));
		// Create the password hash to store in the database.
		$password = pbkdf2($salt . $password . $salt, $salt, HASH_ITERATIONS, HASH_LENGTH);
		// Store the new password in the database.
		$storePassword = $this->users->setNewPassword($userId, $password, $salt);
		if(empty($storePassword)) $this->errors[] = "An error occurred while reseting your password.";
	}
	
	public function checkForDuplicateAlias($alias) {
		global $users;
		$id = $users->getUserByAlias($alias);
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

class NewUserValidation extends UserValidation {

	public  $firstName;
	public  $lastName;
	public  $username;
	public  $usernameAlias;
	protected $password;
	protected $confirmPassword;
	public  $email;
	public  $systemEmail;
	public  $enabled;
	public  $activated;
	public  $roles;
	public  $errors;
	private $acl;

	public function __construct($user) {
		
		global $globalSettings;
		
		$this->globalSettings = $globalSettings;
		$this->users = new Users;
		$this->acl = new RevenantBlue\ACL;
		$this->config = new Config;
		
		$this->firstName = !empty($user['firstName']) ? $this->validateName($user['firstName'], 'first name') : '';
		$this->lastName = !empty($user['lastName']) ? $this->validateName($user['lastName'], 'last name') : '';
		$this->username = $this->validateUsername($user['username']);
		$this->usernameAlias = $this->validateAlias($this->username);
		$this->password = $this->validatePassword($user['password']);
		$this->confirmPassword = $user['confirmPassword'];
		$this->validateEmail($user['email'], TRUE, TRUE);
		$this->systemEmail = $user['systemEmail'];
		// Set account ability
		if($globalSettings['who_can_register']['value'] === 'vistors_approval_req' || $globalSettings['who_can_register']['value'] === 'admin only') {
			$this->enabled = 0;
		} else {
			$this->enabled = 1;
		}
		// Set account activation.
		if(!empty($user['activated'])) {
			$this->activated = (int)$user['activated'];
		} else {
			if((int)$globalSettings['new_user_email_verify']['value'] === 1) {
				$this->activated = 0;
			} else {
				$this->activated = 1;
			}
		}
		// Validate the roles for the user.
		$this->roles = $this->validateRoles($user['roles']);
		$this->createNewUser();
	}

	private function createNewUser() {
		// Ensure that the username does not exist.
		$userExists = $this->users->getUserByUsername($this->username);
		
		if(!empty($userExists)) {
			$this->errors[] = 'This username is already in use, please select another username.';
		}
		// Ensure that the password and confirm password fields match.
		if($this->password !== $this->confirmPassword) {
			$this->errors[] = 'The password confirmation does not match the password that you have provided.';
		}
		// Create the salt for this new user.
		$salt = base64_encode(mcrypt_create_iv(64, MCRYPT_DEV_URANDOM));
		// Create the password hash to store in the database.
		$this->password = pbkdf2($salt . $this->password . $salt, $salt, HASH_ITERATIONS, HASH_LENGTH);
		// Store the username and password in the database.
		if(empty($this->errors)) {
			// Determine user's activation

			$user['firstName'] = $this->firstName;
			$user['lastName'] = $this->lastName;
			$user['username'] = $this->username;
			$user['usernameAlias'] = $this->usernameAlias;
			$user['password'] = $this->password;
			$user['email'] = $this->email;
			$user['enabled'] = $this->enabled;
			$user['activated'] = $this->activated;
			$user['systemEmail'] = $this->systemEmail;
			$user['ip'] = $_SERVER['REMOTE_ADDR'];
			$user['salt'] = $salt;
			
			//print_r2($user);
			//var_dump($user['password']);
			//var_dump($user['salt']);
			//exit;
			
			// Insert the user into the database.
			$this->id = $this->users->insertUser($user);
			
			// Add the roles selected for this user.
			foreach($this->roles as $role => $roleStatus) {
				if($roleStatus === "on") {
					$createUserRoles[] = $this->acl->insertUserRole($this->id, $role);
				}
			}
			
			// Add the user notification options for the user.
			$notifications = $this->users->getNotificationOptions();
			foreach($notifications as $notification) {
				$this->users->insertNotificationOption($this->id, $notification['id']); 
			}
			
			if(!empty($this->id) && !empty($createUserRoles)) {
				// If email verification has been enabled send a registration email to that user.
				if($this->globalSettings['new_user_email_verify']['value']) {
					// Include the swift mailer library
					require_once DIR_SYSTEM . 'library/swiftmailer/lib/swift_required.php';
					// If an email verification is required for a new user send a confirmation email, else send the welcome email.
					if($this->globalSettings['new_user_email_verify']['value']) {
						$emailTemplate = $this->parseEmailTemplate('email-verification', $this->id, 'verify email');
						$this->sendUserEmail(
							$emailTemplate['subject']
						  , $emailTemplate['body']
						  , $this->email
						  , $this->firstName
						  , $this->lastName
						  , $this->globalSettings['system_email']['value']
						  , $this->globalSettings['site_name']['value']
						  , $this->id
						);
					} else {
						$emailTemplate = $this->parseEmailTemplate('welcome-no-approval', $this->id);
						$this->sendUserEmail(
							$emailTemplate['subject']
						  , $emailTemplate['body']
						  , $this->email
						  , $this->firstName
						  , $this->lastName
						  , $this->globalSettings['system_email']['value']
						  , $this->globalSettings['site_name']['value']
						  , $this->id
						);
					}
					// If admin approval is required send the appropriate email.
					if($this->globalSettings['who_can_register']['value'] === 'visitors_approval_req') {
						$emailTemplate = $this->parseEmailTemplate('welcome-awaiting-approval', $this->id, 'admin approval');
						$this->sendUserEmail(
							$emailTemplate['subject']
						  , $emailTemplate['body']
						  , $this->email
						  , $this->firstName
						  , $this->lastName
						  , $this->globalSettings['system_email']['value']
						  , $this->globalSettings['site_name']['value']
						  , $this->id
						);
					}
				}
				return TRUE;
			} else {
				$this->errors[] = "An error occurred while creating your account.  Please try again.";
			}
		}
	}

	public function checkForDuplicateAlias($alias) {
		// Place holder dependency for the global validation class.
		// We have already checked for a duplicate username so checking a duplicate alias would be redundant.
		return FALSE;
	}

	private function __clone() {
		// Make clone private to prevent any attempt at cloning this class.
	}
}

class EditUserValidation extends UserValidation {

	public  $id;
	public  $firstName;
	public  $lastName;
	public  $username;
	public  $email;
	public  $systemEmail;
	public  $enabled;
	public  $errors;
	private $roles;
	private $acl;

	public function __construct($user) {
		$this->users = new Users;
		$this->acl = new RevenantBlue\ACL;
		
		$currentUserData = $this->users->getUserData($user['id']);
		
		// Build the user data to update.
		$this->id = $user['id'];
		// Set first name
		if(!empty($currentUserData['first_name']) && empty($user['firstName'])) {
			$this->firstName = '';
		} elseif(!empty($user['firstName'])) {
			$this->firstName = $user['firstName'];
		} else {
			$this->firstName = $currentUserData['first_name'];
		}
		// Set last name
		if(!empty($currentUserData['last_name']) && empty($user['lastName'])) {
			$this->lastName = '';
		} elseif(!empty($user['lastName'])) {
			$this->lastName = $user['lastName'];
		} else {
			$this->lastName = $currentUserData['last_name'];
		}
		
		$this->username = $this->validateUsername($user['username']);
		
		// If the email address has changed check for duplicates.
		if(!empty($user['email']) && $currentUserData['email'] !== $user['email']) {
			$this->validateEmail($user['email'], TRUE, TRUE);
		} elseif(!empty($user['email'])) {
			$this->validateEmail($user['email']);
		} else {
			$this->email = $currentUserData['email'];
		}
		
		if(!empty($user['password']) && !empty($user['confirmPassword'])) {
			$this->password = $this->validatePassword($user['password']);
			$this->confirmPassword = $user['confirmPassword'];
		}
		
		$this->systemEmail = !empty($user['systemEmail']) ? $user['systemEmail'] : $currentUserData['system_email'];
		$this->enabled = !empty($user['enabled']) ? $user['enabled'] : $currentUserData['enabled'];
		$this->roles = $user['roles'];
		
		$user = array(
			'id'          => $this->id,
			'firstName'   => $this->firstName,
			'lastName'    => $this->lastName,
			'username'    => $this->username,
			'email'       => $this->email,
			'systemEmail' => $this->systemEmail,
			'enabled'     => $this->enabled,
			'roles'       => $this->roles
		);
		
		// Update the user.
		$this->updateUser($user);
	}


	private function updateUser($user) {
		// If the user does not have permission to change their username set the edited username to their original username.
		if($_SESSION['userId'] == $this->id && !aclVerify('change own username')) {
			$this->username = $this->users->getUsernameById($_SESSION['userId']);
		}
		$currentUsername = $this->users->getUsernameById($this->id);
		// Check to ensure that the username selected is unique has not been taken by another user.
		if($currentUsername !== $this->username) {
			$usernameExists = $this->users->getUserByUsername($this->username);
			if(!empty($usernameExists)) {
				$this->errors[] = "The username selected is already in use, please select another username.";
			}
		}
		
		// Ensure that the password and confirm password fields match.
		if(!empty($this->password) && !empty($this->confirmPassword)) {
			if($this->password !== $this->confirmPassword) {
				$this->errors[] = 'The password confirmation does not match the password that you have provided.';
			}
		}
		
		if(empty($this->errors)) {
			$userUpdated = $this->users->updateUser($user);
			// Insert and delete user roles as necessary.
			foreach($this->roles as $roleId => $roleStatus) {
				$role = $this->acl->getRole($this->id, $roleId);
				if(empty($role) && $roleStatus == 'on') {
					$this->acl->insertUserRole($this->id, $roleId);
				} elseif(!empty($role) && $roleStatus == 'off') {
					$this->acl->deleteUserRole($this->id, $roleId);
				}
			}
			// Reset the password if a new password was provided
			if(!empty($this->password)) {
				$this->validatePasswordReset($user['id'], $this->password, $this->confirmPassword); 
			}
			// Return success or errors.
			if(!empty($userUpdated)) {
				$_SESSION['success'] = "User successfully updated.";
			} else {
				$_SESSION['errors'] = 'An error occurred while updating the user information';
			}
		} elseif(empty($this->errors) && empty($editUser)) {
			$this->errors[] = "An error occurred while updating, please try again.";
		}
	}
	
	public function checkForDuplicateAlias($alias) {
		// Place holder dependency for the global validation class.
		// We have already checked for a duplicate username so checking a duplicate alias would be redundant.
		return FALSE;
	}
	
	private function __clone() {
		// Make clone private to prevent any attempt at cloning this class.
	}
}
