<?php
namespace RevenantBlue\Admin;
use RevenantBlue;
use RevenantBlue\Pager;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'model/users/users-main.php';

$pager = new Pager;
// Load ACL Roles
$roles = $acl->loadRoles();
$modules = $acl->loadModules();
$moduleGroups = $acl->getModuleGroups();

// Load the roles array for administrering roles and role permissions.
if(aclVerify('administer permissions')) {
	if(isset($_GET['controller'])) {
		if($_GET['controller'] === 'roles' && !isset($_GET['role'])) {
			if(!empty($_POST['roleToSearch'])) {
				header('Location: ' . HTTP_ADMIN . 'users/roles?search=' . urlencode($_POST['roleToSearch']) . '&order=name&sort=asc');
				exit;
			} elseif(isset($_GET['search']) && isset($_GET['order']) && isset($_GET['sort'])) {
				if(in_array($_GET['order'], $acl->whiteList)) {
					$pager->totalRecords = $acl->countRolesBySearch($_GET['search'], $_GET['order'], $_GET['sort']);
					$pager->paginate();
					$roles = $acl->loadRolesBySearch($pager->limit, $pager->offset, $_GET['search'], $_GET['order'], $_GET['sort']);
				} else {
					header('Location: ' . HTTP_ADMIN . 'users/roles', TRUE, 302);
					exit;
				}
			} else {
				$pager->totalRecords = $acl->getNumOfRoles();
				if(isset($_GET['order']) && isset($_GET['sort']) && in_array($_GET['order'], $acl->whiteList)) {
					$pager->paginate();
					$roles = $acl->loadRolesOverview($pager->limit, $pager->offset, $_GET['order'], $_GET['sort']);
				} else {
					$pager->paginate();
					$roles = $acl->loadRolesOverview($pager->limit, $pager->offset, 'rank', 'desc');
				}
			}
			$optionsForPage = $users->getOptionsByGroup('roles');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'roles');
		} elseif($_GET['controller'] === 'roles' && isset($_GET['role'])) {
			// Get the role id when editing a specific role.
			$role = $acl->getRoleById((int)$_GET['role']);
			$permissions = $acl->loadPermissionsForRole($role['id']);
			
			$optionsForPage = $users->getOptionsByGroup('role perms');
			
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'role perms');
		} elseif($_GET['controller'] === 'role-profile') {
			$optionsForPage = $users->getOptionsByGroup('role profile');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'role profile');
		}
	}
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	if(isset($_POST['roleAction'])) {
		// Handle create new role requests.
		if(isset($_POST['name'])) {
			$roleValidation = new RoleValidation;
			$newRoleName = $roleValidation->validateRoleName($_POST['name']);
			if(empty($roleValidation->errors)) {
				$newRoleId = $acl->insertRole($newRoleName);
				$moduleIds = $acl->getAllModuleIds();
				foreach($moduleIds as $moduleId) {
					$acl->insertPermissions($moduleId['id'], $newRoleId);
				}
				if(isset($moduleIds) && isset($newRoleId)) {
					$_SESSION['success'] = "New role created successfully";
				} else {
					$_SESSION['errors'] = "An error occured while creating this role";
				}
			} else {
				$_SESSION['errors'] = $roleValidation->errors;
			}
			
			if(empty($_SESSION['errors'])) {
				switch($_POST['roleAction']) {
					case "save":
						header('Location: ' . HTTP_ADMIN . 'users/roles/' . $newRoleId, TRUE, 302);
						exit;
						break;
					case "save-close":
						header('Location: ' . HTTP_ADMIN . 'users/roles/', TRUE, 302);
						exit;
						break;
					case "save-new":
						header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
						exit;
						break;
				}
			} else {
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			}
		} elseif($_POST['roleAction'] === 'edit' && isset($_POST['roleChecked'])) {
			// Handle a request to edit a particular role.
			if(is_numeric($_POST['roleChecked'][0])) {
				header('Location: ' . HTTP_ADMIN . 'users/roles/' . (int)$_POST['roleChecked'][0], TRUE, 302);
				exit;
			}
		} elseif($_POST['roleAction'] === 'delete' && isset($_POST['roleChecked'])) {
			// Handle a request to delete a role(s).
			foreach($_POST['roleChecked'] as $roleIdToDelete) {
				$roleIdToDelete = (int)$roleIdToDelete;
				// Do not allow the locked roles to be deleted.
				if(!array_key_exists($roleIdToDelete, $acl->lockedRoles)) {
					$acl->deleteRole($roleIdToDelete);
					$_SESSION['success'][] = "Role deleted successfully.";
				} elseif(array_key_exists($roleIdToDelete, $acl->lockedRoles)) {
					$_SESSION['errors'][] = "You cannot delete a locked role.  These roles are required for proper security.";
				}
			}
			
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
	}
	// Handle the updating of role permissions.
	if(isset($_POST['rolePermsAction']) && isset($_POST['roleId'])) {

		if(isset($_POST['roleName'])) {
			// Get the role check if the role is locked'
			$role = $acl->getRoleById((int)$_POST['roleId']);
			
			if($role['name'] === $_POST['roleName'] && !array_key_exists((int)$_POST['roleId'], $acl->lockedRoles)) {
				if(empty($_SESSION['errors'])) {
					$updateRoleName = $acl->updateRoleName($_POST['roleId'], $_POST['roleName']);
				}
				if(!isset($updateRoleName)) {
					$_SESSION['errors'][] = "An error occured while updating this role's name.";
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
				}
			} elseif($role['name'] !== $_POST['roleName'] && array_key_exists($_POST['roleId'], $acl->lockedRoles)) {
				$_SESSION['errors'][] = "This role's name is locked and cannot be changed.";
			}
		}
		// If changing backend/frontend privileges.
		// Get the old privileges for the role.
		$oldPerms = $acl->loadPermissionsForRole((int)$_POST['roleId']);
		$usersWithRole = $users->getUsersByRoleId((int)$_POST['roleId']);
		foreach($_POST['perm'] as $moduleId => $perm) {
			$roleId = $_POST['roleId'];
			switch($perm) {
				case 'on':
					$perm = 1;
					break;
				case 'off':
					$perm = 0;
					break;
				default:
					$perm = 0;
					break;
			}
			$acl->updatePrivilegesForRole($moduleId, $perm, $roleId);
		}
		// If removing backend permissions for a role.
		if((!isset($oldPerms[53]) || (int)$oldPerms[53] === 1) && $_POST['perm'][53] === 'off') {
			$backendOptions = $users->getOptionsByLocation('backend');
			foreach($backendOptions as $backendOption) {
				foreach($usersWithRole as $user) {
					if(!aclVerify('backend admin', $user['user_id'])) {
						$users->deleteAllOptionsForUser($user['user_id']);
					}
				}
			}
		}
		// If adding backend permissions for a role.
		if((!isset($oldPerms[53]) || (int)$oldPerms[53] == 0) && $_POST['perm'][53] === 'on') {
			$backendOptions = $users->getOptionsByLocation('backend');
			foreach($backendOptions as $backendOption) {
				foreach($usersWithRole as $user) {
					if(aclVerify('backend admin', $user['user_id'])) {
						$users->insertUserOption($user['user_id'], $backendOption['id']);
					}
				}
			}
		}
		switch($_POST['rolePermsAction']) {
			case 'save':
				$_SESSION['success'] = "Role updated successfully.";
				if(isset($_POST['roleName'])) {
					header('Location: ' . HTTP_ADMIN . 'users/roles/' . $role['id'], TRUE, 302);
				} else {
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				}
				exit;
				break;
			case 'save-close':
				$_SESSION['success'] = "Role updated successfully.";
				header('Location: ' . HTTP_ADMIN . 'users/roles/', TRUE, 302);
				exit;
				break;
			default:
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
				break;
		}
	}
	
	// Handle the manipulation of individual permissions
	if(isset($_POST['permissionAction'])) {
		
		if($_POST['permissionAction'] === 'add-permission') {
			$module['name'] = isset($_POST['name']) ? $_POST['name'] : '';
			$module['group'] = isset($_POST['group']) ? $_POST['group'] : '';
			$module['description'] = isset($_POST['description']) ? $_POST['description'] : '';
			$module['securityRisk'] = isset($_POST['securityRisk']) ? $_POST['securityRisk'] : '';
			
			$moduleId = $acl->insertModule($module['name'], $module['group'], $module['description'], $module['securityRisk']);
			
			if(empty($moduleId)) {
				$_SESSION['errors'] = 'An error occurred while adding a module permission.';
			} else {
				$_SESSION['success'] = 'Module permission added successfully.';
			}
			
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
	}
	
	if(isset($_POST['adminRequest'])) {
		$adminReq = json_decode($_POST['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type == 'role') {
			if($adminReq->action === 'set-ranks') {
				if(!empty($adminReq->ranks)) {
					foreach($adminReq->ranks as $rank => $roleId) {
						$acl->setRoleRank((int)$roleId, (int)$rank);
					}
				}
			}
		}
		
		echo json_encode($adminReq);
		exit;
	}
}


// Validation class for the role name.
class RoleValidation {
	public $errors = array();

	public function validateRoleName($name) {
		// Ensure that the role name isn't empty.
		if(empty($name)) $this->errors[] = "No role name was provided; please enter a name and try again.";
		// Test the role name to ensure proper format.
		if(preg_match('/[^a-zA-Z0-9 ]/', $name)) $this->errors[] = "The role name can only contain letters numbers and spaces.";
		return $name;
	}
}
