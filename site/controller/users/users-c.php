<?php
namespace RevenantBlue\Site;

require_once(DIR_APPLICATION . 'controller/common/site-c.php');

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	// If the user is not currently logged into the system.
	if(empty($_SESSION['username'])) {
		if(isset($_GET['verify'])) {
			$emailVerified = $config->verifyCode($_GET['verify'], 'verify email');
			if($emailVerified) {
				// Activated the user.
				$accountActivated = $users->setUserActivation($emailVerified['user_id'], 1);
				
				// Send account activated email.
				$userValidation = new UserValidation;
				$userData = $users->getUserData($emailVerified['user_id']);
				$emailTemplate = $userValidation->parseEmailTemplate('account-activation', $userData['id']);
				$userValidation->sendUserEmail(
					$emailTemplate['subject']
				  , $emailTemplate['body']
				  , $userData['email']
				  , $userData['first_name']
				  , $userData['last_name']
				  , $globalSettings['system_email']['value']
				  , $globalSettings['site_name']['value']
				  , $userData['id']
				);
				
				// Delete the user code.
				$config->deleteCode($emailVerified['user_id'], $emailVerified['user_code']);
			} else {
				header('Location: ' . HTTP_SERVER, TRUE, 302);
				exit;
			}
		} elseif(isset($_GET['pwreset'])) {
			// Handle password reset requests from users.
			$pwReset = $config->verifyCode($_GET['pwreset'], 'password reset');
			if(!$pwReset) {
				header('Location: ' . HTTP_SERVER, TRUE, 302);
				exit;
			}
		} elseif(isset($_GET['approval'])) {
			$approval = $config->verifyCode($_GET['approval'], 'admin approval');
			if(!$approval) {
				header('Location: ' . HTTP_SERVER, TRUE, 302);
				exit;
			}
		} elseif(isset($_GET['cancel'])) {

		}
	}
}
if($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Password Reset
	if(isset($_POST['submitPasswordReset']) && isset($_POST['newPassword']) && isset($_POST['confirmNewPassword']) && isset($_POST['id']) && isset($_POST['code'])) {
		$userValidation = new UserValidation();
		$userValidation->validatePasswordReset($_POST['id'], $_POST['newPassword'], $_POST['confirmNewPassword']);
		if(empty($userValidation->errors)) {
			$_SESSION['success'] = "Your password was successfully changed.";
			$config->deleteCode($_POST['id'], $_POST['code']);
		} else {
			$_SESSION['errors'] = $userValidation->errors;
		}
		header('Location: ' . HTTP_LOGIN, TRUE, 302);
		exit;
	} elseif(isset($_POST['submitLostPassword']) && isset($_POST['usernameEmail'])) {
		// Lost password request.
		$userValidation = new UserValidation;
		$isEmail = $users->getUserByEmail($_POST['usernameEmail']);
		$isUsername = $users->getUserByUsername($_POST['usernameEmail']);
		if($isEmail || $isUsername) {
			if(!empty($isEmail)) {
				$userData = $isEmail;
			} else {
				$userData = $isUsername;
			}
			// Include the swift mailer library
			require_once(DIR_SYSTEM . 'library/swiftmailer/lib/swift_required.php');
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
			
			$_SESSION['success'] = 'An email has been sent to ' . hsc($userData['email']) . ' with instructions on how to reset your password.';
			header('Location: ' . HTTP_LOGIN . '?action=lostpassword', TRUE, 302);
			exit;
		} else {
			$_SESSION['errors'][] = "Username or email does not exist.";
			header('Location: ' . HTTP_LOGIN . '?action=lostpassword', TRUE, 302);
			exit;
		}
	}
}
