<?php
namespace RevenantBlue\Admin;
use RevenantBlue\Pager;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/users/users-validation.php';
require_once DIR_ADMIN . 'model/users/users-main.php';

// Begin user permission check.
if(aclVerify('administer users')) {
// Load ACL Roles
$roles = $acl->loadRoles();
$modules = $acl->loadModules();
$moduleGroups = $acl->getModuleGroups();

// Instantiate the pagination class.
$pager = new Pager;

if($_SERVER['REQUEST_METHOD'] == 'GET') {
	if(isset($_GET['controller'])) {
		if($_GET['controller'] === 'users') {
			// Set the limit for the users overview.
			$numOfUsersToShow = $users->getOptionValueForUser($_SESSION['userId'], 103);
			if(!empty($numOfUsersToShow)) {
				$pager->limit = $numOfUsersToShow;
			}
			// Set the user list to display the current search results.
			if(isset($_GET['search']) && isset($_GET['order']) && isset($_GET['sort'])) {
				// Check to ensure that the order GET request is in the white list of secure columns.
				if(in_array($_GET['order'], $users->whiteList)) {
					$pager->totalRecords = $users->countUserSearch($_GET['search'], $_GET['order'], $_GET['sort']);
					$pager->paginate();
					$userList = $users->loadUserOverviewByUserSearch($pager->limit, $pager->offset, $_GET['search'], $_GET['order'], $_GET['sort']);
				} else {
					header('Location: ' . HTTP_ADMIN . 'users/', true, 302);
					exit;
				}
			// Set the user to display the current status filter results.
			} elseif(isset($_GET['enabled']) && isset($_GET['order']) && isset($_GET['sort'])) {
				if(in_array($_GET['order'], $users->whiteList)) {
					$pager->totalRecords = $users->countUserStatus($_GET['enabled'], $_GET['order'], $_GET['sort']);
					$pager->paginate();
					$userList = $users->loadUserOverviewStatusFilter($pager->limit, $pager->offset, $_GET['enabled'], $_GET['order'], $_GET['sort']);
				} else {
					header('Location: ' . HTTP_ADMIN . 'users/', true, 302);
					exit;
				}
			} elseif(isset($_GET['activated']) && isset($_GET['order']) && isset($_GET['sort'])) {
				if(in_array($_GET['order'], $users->whiteList)) {
					$pager->totalRecords = $users->countUserActivation($_GET['activated'], $_GET['order'], $_GET['sort']);
					$pager->paginate();
					$userList = $users->loadUserOverviewActivationFilter($pager->limit, $pager->offset, $_GET['activated'], $_GET['order'], $_GET['sort']);
				} else {
					header('Location: ' . HTTP_ADMIN . 'users/', true, 302);
					exit;
				}
			} elseif(isset($_GET['role']) && isset($_GET['order']) && isset($_GET['sort'])) {
				if(in_array($_GET['order'], $users->whiteList)) {
					$pager->totalRecords = $users->countUserRoleFilter($_GET['role'], $_GET['order'], $_GET['sort']);
					$pager->paginate();
					$userList = $users->loadUserOverviewRoleFilter($pager->limit, $pager->offset, $_GET['role'], $_GET['order'], $_GET['sort']);
				} else {
					header('Location: ' . HTTP_ADMIN . 'users/', true, 302);
					exit;
				}
			// Load the standard user overview.
			} else {
				$pager->totalRecords = $users->getNumOfUsers();
				if(isset($_GET['order']) && isset($_GET['sort']) && in_array($_GET['order'], $users->whiteList)) {
					$pager->paginate();
					$userList = $users->loadUserOverview($pager->limit, $pager->offset, $_GET['order'], $_GET['sort']);
				} else {
					$pager->paginate();
					$userList = $users->loadUserOverview($pager->limit, $pager->offset, 'username', 'asc');
				}
			}
			$pager->displayItemsPerPage();
			$pager->menu = str_replace('index.php?page=', BACKEND_NAME . '/users/p', $pager->menu);
			$pager->menu = str_replace('&amp;controller=admin-users', '', $pager->menu);
			$pager->limitMenu = str_replace('index.php?page=', BACKEND_NAME . '/users/p', $pager->limitMenu);
			$pager->limitMenu = str_replace('&amp;controller=admin-users', '', $pager->limitMenu);

			// Set the user options for the user overview
			$optionsForPage = $users->getOptionsByGroup('users');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'users');
			// If the user changes the number of records to show, store the change in the personal options table.
			setNumToShow(103);
		} elseif($_GET['controller'] === 'user-profile') {
			if(isset($_GET['id'])) {
				$userData = $users->getUserData((int)$_GET['id']);
				$userRoles = $acl->getRolesOnlyForUser($userData['id']);
			}
			// Set the user options for the user profile
			$optionsForPage = $users->getOptionsByGroup('user profile');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'user profile');
		} elseif($_GET['controller'] === 'roles') {
			if(isset($_GET['role'])) {
				$role = $acl->getRoleByName($_GET['role']);
				$permissions = $acl->loadPermissionsForRole($role['id']);
			}
			// Set the user options for the roles overview
			$optionsForPage = $users->getOptionsByGroup('roles');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'user roles');
		}
	}
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	// Set the user list to display new search results.
	if(!empty($_POST['userToSearch'])) {
		header('Location: ' . HTTP_ADMIN . 'users?search=' . urlencode($_POST['userToSearch']) . "&order=username&sort=asc", true, 302);
		exit;
	// Set the user list to display the results from the status filter.
	} elseif(isset($_POST['userStatusFilter'])) {
		header('Location: ' . HTTP_ADMIN . 'users?enabled=' . urlencode($_POST['userStatusFilter']) . "&order=username&sort=asc", true, 302);
		exit;
	// Set the user list to display the results from the activation filter.
	} elseif(isset($_POST['userActivationFilter'])) {
		header('Location: ' . HTTP_ADMIN . 'users?activated=' . urlencode($_POST['userActivationFilter']) . "&order=username&sort=asc", true, 302);
		exit;
	// Set the user list to display the results from the role filter.
	} elseif(isset($_POST['userRoleFilter'])) {
		header('Location: ' . HTTP_ADMIN . 'users?role=' . urlencode($_POST['userRoleFilter']) . "&order=username&sort=asc", true, 302);
		exit;
	}
	
	// Handle New/Edit user requests.
	if(isset($_POST['userAction'])) {
		// Process new user request.
		if($_POST['userAction'] === 'edit' && isset($_POST['userCheck'])) {
			header('Location: ' . HTTP_ADMIN . 'users/' . (int)$_POST['userCheck'][0] . '/edit', TRUE, 302);
			exit;
		} elseif($_POST['userAction'] === 'save' || $_POST['userAction'] === 'save-close' || $_POST['userAction'] === 'save-new') {
			$user['id'] = isset($_POST['userId']) ? $_POST['userId'] : '';
			$user['firstName'] = isset($_POST['firstName']) ? $_POST['firstName'] : '';
			$user['lastName'] = isset($_POST['lastName']) ? $_POST['lastName'] : '';
			$user['username'] = isset($_POST['username']) ? $_POST['username'] : '';
			$user['password'] = isset($_POST['password']) ? $_POST['password'] : '';
			$user['confirmPassword'] = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
			$user['email'] = isset($_POST['email']) ? $_POST['email'] : '';
			$user['systemEmail'] = isset($_POST['systemEmail']) ? $_POST['systemEmail'] : '';
			$user['enabled'] = isset($_POST['enabled']) ? $_POST['enabled'] : '';
			$user['roles'] = isset($_POST['roles']) ? $_POST['roles'] : '';
			
			if(isset($_POST['userId'])) {
				// Validate user update.
				$updatedUser = new EditUserValidation($user);
				
				if(!empty($updatedUser->errors)) {
					$_SESSION['errors'] = $updatedUser->errors;
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
				} else {
					// Add options to display for that user, MySQL insert stmt set to IGNORE to avoid duplicate entry errors.
					if(aclVerify('backend admin', $updatedUser->id)) {
						$backendOptions = $users->getOptionsByLocation('backend');
						foreach($backendOptions as $backendOption) {
							$users->insertUserOption($updatedUser->id, $backendOption['id']);
						}
					}
					if(aclVerify('frontend admin', $updatedUser->id)) {
						$frontendOptions = $users->getOptionsByLocation('frontend');
					}
				}
			} else {
				$newUser = new NewUserValidation($user);
				
				if(!empty($newUser->errors)) {
					$_SESSION['errors'] = $newUser->errors;
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
				} else {
					// Add options to display for that user.
					if(aclVerify('backend admin', $newUser->id)) {
						$backendOptions = $users->getOptionsByLocation('backend');
						foreach($backendOptions as $backendOption) {
							$users->insertUserOption($newUser->id, $backendOption['id']);
						}
					}
					if(aclVerify('frontend admin', $newUser->id)) {
						$frontendOptions = $users->getOptionsByLocation('frontend');
					}
				}
			}
			
			if(empty($newUser->errors) && empty($editUser->errors)) {
				if(isset($newUser)) {
					$_SESSION['success'] = "User created successfully.";
					$userData = $users->getUserData($newUser->id);
					$userValidation = new UserValidation;
					// Since the user was created by the administrator send an email notifying the user that an account has been created for them.
					$emailTemplate = $userValidation->parseEmailTemplate('welcome-created-by-admin', $newUser->id, 'admin approval');
					$userValidation->sendUserEmail(
						$emailTemplate['subject']
					  , $emailTemplate['body']
					  , $newUser->email
					  , $newUser->firstName
					  , $newUser->lastName
					  , $globalSettings['system_email']['value']
					  , $globalSettings['site_name']['value']
					  , $newUser->id
					);
				}
				if(isset($updatedUser)) {
					$_SESSION['success'] = "User updated successfully.";
				}
				
				switch($_POST['userAction']) {
					case "save":
						if(isset($newUser)) {
							header('Location: ' . HTTP_ADMIN . 'users/' . $newUser->id . '/edit', TRUE, 302);
							exit;
						} elseif(isset($updatedUser)) {
							header('Location: ' . HTTP_ADMIN . 'users/' . $updatedUser->id . '/edit', TRUE, 302);
							exit;
						}
						break;
					case "save-close":
						header('Location: ' . HTTP_ADMIN . 'users/', TRUE, 302);
						exit;
						break;
					case "save-new":
						header('Location: ' . HTTP_ADMIN . 'users/new/', TRUE, 302);
						exit;
						break;
					default:
						header('Location: ' . HTTP_ADMIN . 'users/', TRUE, 302);
						exit;
						break;
				}
			}
		}
	} elseif(isset($_POST['adminRequest'])) {
		$adminReq = json_decode($_POST['adminRequest']);
		// Handle block/unblock user(s) JSON requests.
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type == "user" && aclVerify('administer users')) {
			for($x = 0; $x < count($adminReq->ids); $x++) {
				if(is_numeric($adminReq->ids[$x])) {
					// Enable user.
					if($adminReq->action == "enable") {
						$users->setUserStatus($adminReq->ids[$x], 1);
						$adminReq->enabled[$x] = $users->getUserStatus($adminReq->ids[$x]);
					// Disable user.
					} elseif($adminReq->action == "block") {
						$users->setUserStatus($adminReq->ids[$x], 0);
						$adminReq->enabled[$x] = $users->getUserStatus($adminReq->ids[$x]);
					// Activate user.
					} elseif($adminReq->action == "activate") {
						$users->setUserActivation($adminReq->ids[$x], 1);
						$adminReq->activated[$x] = $users->getUserActivation($adminReq->ids[$x]);
						// Send email activation
						$userValidation = new UserValidation;
						$userData = $users->getUserData($adminReq->ids[$x]);
						// Include the swift mailer library
						require_once DIR_SYSTEM . 'library/swiftmailer/lib/swift_required.php';
						$emailTemplate = $userValidation->parseEmailTemplate('account-activation', $adminReq->ids[$x]);
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
					// Delete user.
					} elseif($adminReq->action == "delete") {
						// Check to ensure that the user has the appropriate privileges to delete their own account.
						// If the user is trying to delete their own account and has permissiong to do so.
						if($_SESSION['userId'] == $adminReq->ids[$x] && aclVerify('cancel own user account')) {
							$adminReq->deleted = $users->deleteUser($adminReq->ids[$x]);
						// If the user is trying to delete their own account an do not have permission to do so.
						} elseif($_SESSION['userId'] == $adminReq->ids[$x] && !aclverify('cancel own user account')) {
							$_SESSION['errors'][] = "You cannot delete your own account.";
						} elseif($_SESSION['userId'] != $adminReq->ids[$x] && aclVerify('administer users')) {
							$adminReq->deleted = $users->deleteUser($adminReq->ids[$x]);
							if(isset($adminReq->deleted)) {
								$_SESSION['success'] = "User(s) deleted successfully.";
							} else {
								$_SESSION['errors'] = "An error occured while deleting the user(s).";
							}
						}
					}
				}
			}
		}
		echo json_encode($adminReq);
		exit;
	} else {
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	}
}
// End user permission check.
} else {
	header('Location: ' . HTTP_ADMIN, TRUE, 302);
	exit;
}
