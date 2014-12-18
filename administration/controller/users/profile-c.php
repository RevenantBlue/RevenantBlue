<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/users/users-validation.php';
require_once DIR_ADMIN . 'model/users/users-main.php';

$users = new admin\Users;
// Load ACL Roles
$roles = $acl->loadRoles();

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	$userData = $users->getUserData($_SESSION['userId']);
	$userRoles = $acl->getRolesOnlyForUser($_SESSION['userId']);
	// Set the user options for the user profile
	$optionsForPage = $users->getOptionsByGroup('profile');
	$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'profile');
}
if($_SERVER['REQUEST_METHOD'] === 'POST') {
	if(isset($_POST['profileSubmit'])) {
		$user['id'] = $_SESSION['userId'];
		$user['firstName'] = isset($_POST['firstName']) ? $_POST['firstName'] : '';
		$user['lastName'] = isset($_POST['lastName']) ? $_POST['lastName'] : '';
		$user['username'] = isset($_POST['username']) ? $_POST['username'] : '';
		$user['password'] = isset($_POST['password']) ? $_POST['password'] : '';
		$user['confirmPassword'] = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
		$user['email'] = isset($_POST['email']) ? $_POST['email'] : '';
		$user['systemEmail'] = isset($_POST['systemEmail']) ? $_POST['systemEmail'] : '';
		$user['enabled'] = 1;
		// Build user roles.
		$userRoles = $acl->getRolesOnlyForUser($_SESSION['userId']);
		foreach($userRoles as $userRole) {
			$user['roles'][$userRole] = 'on';
		}
		// Validate user update.
		$editUser = new EditUserValidation($user, TRUE);
		
		if(!empty($editUser->errors)) {
			$_SESSION['errors'] = $editUser->errors;
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
		if(empty($editUser->errors)) {
			if(isset($editUser)) {
				$_SESSION['success'] = "Profile updated successfully.";
			}
			switch($_POST['profileSubmit']) {
				case 'save':
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
					break;
				case 'save-close':
					header('Location: ' . HTTP_ADMIN, TRUE, 302);
					exit;
				default:
					header('Location: ' . HTTP_ADMIN . 'profile', true, 302);
					exit;
					break;
			}
		}
	}
}
