<?php

namespace RevenantBlue;
use \PDO;

class ACL extends Db {

	public  $listAcl;
	public  $whiteList = array('id', 'name', 'rank', 'module_group', 'order_of_item', 'module_id', 'role_id', 'allow');
	public  $lockedRoles = array(2 => 'Administrator', 4 => 'Member', 5 => 'Anonymous', 6 => 'Banned');
	public  $lockedAdminPerms = array(23 => 'Administer users', 31 => 'Administer permissions');
	private $rolesTable;
	private $modulesTable;
	private $permsTable;
	private $userRolesTable;

	public function __construct() {
		$this->rolesTable = PREFIX . "acl_roles";
		$this->modulesTable = PREFIX . "acl_modules";
		$this->permsTable = PREFIX . "acl_permissions";
		$this->userRolesTable = PREFIX . "acl_user_roles";
	}

	public function loadRoles() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->rolesTable ORDER BY rank DESC");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function countRoles() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->rolesTable");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadRolesOverview($limit, $offset, $order, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->rolesTable
				 ORDER BY $order $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadRolesBySearch($limit, $offset, $searchWord, $order, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "SELECT * FROM $this->rolesTable
			          WHERE name LIKE :searchWord
				      ORDER BY $order $sort
					  LIMIT :limit
					  OFFSET :offset";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countRolesBySearch($searchWord, $order, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "SELECT COUNT(*) AS total_records FROM $this->rolesTable
			          WHERE name LIKE :searchWord
				      ORDER BY $order $sort";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['total_records'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadModules() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->modulesTable ORDER BY module_group, order_of_item");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadModulesByRole($moduleGroupName) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->modulesTable WHERE module_group = :moduleGroupName ORDER BY order_of_item");
			$stmt->bindParam(':moduleGroupName', $moduleGroupName, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadPermissionsForRole($roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT module_id, allow FROM $this->permsTable WHERE role_id = :roleId ORDER BY module_id");
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function loadRolesForUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->userRolesTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function verifyPermission($moduleName, $roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT perms.allow FROM $this->permsTable AS perms
				 INNER JOIN $this->modulesTable AS modules ON modules.id = perms.module_id
				 WHERE perms.role_id = :roleId AND modules.name = :moduleName AND perms.allow = :allow"
			);
			$stmt->bindParam(':moduleName', $moduleName, PDO::PARAM_STR);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->bindValue(':allow', 1, PDO::PARAM_BOOL);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['allow'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertModule($moduleName, $moduleGroup, $description, $securityRisk = FALSE) {
		try {
			if(!self::$dbh) $this->connect();
			$currentOrder = self::getOrderOfModule($moduleGroup);
			$roles = self::getAllRoles();
			self::$dbh->beginTransaction();
			$query = "INSERT INTO $this->modulesTable (name, module_group, order_of_item, description, security_risk)
			          VALUES (:moduleName, :moduleGroup, :orderOfItem, :description, :securityRisk)";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':moduleName', $moduleName, PDO::PARAM_STR);
			$stmt->bindParam(':moduleGroup', $moduleGroup, PDO::PARAM_STR);
			$stmt->bindValue(':orderOfItem', $currentOrder + 1, PDO::PARAM_INT);
			$stmt->bindParam(':description', $description, PDO::PARAM_STR);
			$stmt->bindParam(':securityRisk', $securityRisk, PDO::PARAM_BOOL);
			$stmt->execute();
			$newModuleId = self::getModuleIdByName($moduleName);
			// Insert the new module into the permissions table for all of the existing roles.
			foreach($roles as $role) {
				$stmt2 = self::$dbh->prepare("INSERT INTO $this->permsTable (module_id, role_id) VALUES (:moduleId, :roleId)");
				$stmt2->bindParam(':moduleId', $newModuleId, PDO::PARAM_INT);
				$stmt2->bindParam(':roleId', $role['id'], PDO::PARAM_INT);
				$stmt2->execute();
			}
			return self::$dbh->commit();
		} catch(PDOExceptione $e) {
			self::$dbh->rollBack();
			$this->errorlog($e);
		}
	}

	public function insertRole($roleName) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->rolesTable SET rank = rank + 1 WHERE rank > 0"
			);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare(
				"INSERT INTO $this->rolesTable (name, rank) VALUES (:roleName, 1)"
			);
			$stmt2->bindParam(':roleName', $roleName, PDO::PARAM_STR);
			$stmt2->execute();
			$roleId = self::$dbh->lastInsertId();
			self::$dbh->commit();
			return (int)$roleId;
		} catch(PDOExceptione $e) {
			self::$dbh->rollBack();
			$this->errorlog($e);
		}
	}

	public function insertPermissions($moduleId, $roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("INSERT INTO $this->permsTable (module_id, role_id, allow) VALUES (:moduleId, :roleId, 0)");
			$stmt->bindParam(':moduleId', $moduleId, PDO::PARAM_INT);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOExceptione $e) {
			$this->errorlog($e);
		}
	}

	public function insertUserRole($userId, $roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("INSERT INTO $this->userRolesTable (user_id, role_id) VALUES (:userId, :roleId)");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updatePrivilegesForRole($moduleId, $allow, $roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->permsTable SET allow = :allow WHERE module_id = :moduleId AND role_id = :roleId");
			$stmt->bindParam(':allow', $allow, PDO::PARAM_BOOL);
			$stmt->bindParam(':moduleId', $moduleId, PDO::PARAM_INT);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function updateRoleName($roleId, $newName) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->rolesTable SET name = :newName WHERE id = :roleId");
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->bindParam(':newName', $newName, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteUserRole($userId, $roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->userRolesTable WHERE role_id=:roleId AND user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function deleteRole($roleId) {
		// Will delete the role and all permissions it has been assigned.
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"SELECT @rank := rank FROM $this->rolesTable WHERE id = :roleId"
			);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt1 = self::$dbh->prepare(
				"UPDATE $this->rolesTable SET rank = rank - 1 WHERE rank > @rank"
			);
			$stmt1->execute();
			$stmt2 = self::$dbh->prepare(
				"DELETE FROM $this->rolesTable WHERE id = :roleId"
			);
			$stmt2->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt2->execute();
			$stmt3 = self::$dbh->prepare(
				"DELETE FROM $this->userRolesTable WHERE role_id = :roleId"
			);
			$stmt3->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt3->execute();
			$stmt4 = self::$dbh->prepare(
				"DELETE FROM $this->permsTable WHERE role_id = :roleId"
			);
			$stmt4->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt4->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteModule($moduleId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare(
				"SELECT @currentOrder := order_of_item, @currentModuleGrp := module_group
				 FROM $this->modulesTable
				 WHERE id = :moduleId"
			);
			$stmt->bindParam(':moduleId', $moduleId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("DELETE FROM $this->modulesTable WHERE id = :moduleId");
			$stmt2->bindParam(':moduleId', $moduleId, PDO::PARAM_INT);
			$stmt2->execute();
			$stmt3 = self::$dbh->prepare(
				"UPDATE $this->modulesTable SET order_of_item = order_of_item - 1
				 WHERE order_of_item > @currentOrder AND module_group = @currentModuleGrp"
			);
			$stmt3->execute();
			self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function getRoleById($roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->rolesTable WHERE id = :id");
			$stmt->bindParam(':id', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function getRoleByName($roleName) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->rolesTable WHERE name = :name");
			$stmt->bindParam(':name', $roleName, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}
	
	public function getOrderOfModule($moduleGroup) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT COUNT(*) AS total FROM $this->modulesTable WHERE module_group = :moduleGroup");
			$stmt->bindParam(':moduleGroup', $moduleGroup, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['total'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getModuleGroups() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT module_group FROM $this->modulesTable GROUP BY module_group");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfRoles() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT COUNT(*) AS total_records FROM $this->rolesTable");
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['total_records'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRole($userId, $roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->userRolesTable WHERE user_id = :userId AND role_id = :roleId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {

		}
	}

	public function getAllRoles() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->rolesTable");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRolesForUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->userRolesTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getRoleIdsForUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT role_id FROM $this->userRolesTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getRolesOnlyForUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT role_id FROM $this->userRolesTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getHighestRankedRoleForUser($userId, $returnId = FALSE) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT
					r.id, urt.user_id, urt.role_id, r.name, r.rank
				FROM
					$this->userRolesTable AS urt
				LEFT JOIN $this->rolesTable AS r ON r.id = urt.role_id
				WHERE
					urt.user_id = :userId
				ORDER BY r.rank DESC
				LIMIT 1"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if($returnId === TRUE) {
				return $result['role_id'];
			} else {
				return $result['name'];
			}
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getModuleIdByName($moduleName) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT id FROM $this->modulesTable WHERE name = :moduleName");
			$stmt->bindParam(':moduleName', $moduleName, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getAllModuleIds() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT id FROM $this->modulesTable");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getModuleGroup($moduleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT module_group FROM $this->modulesTable");
			$stmt->bindParam(':moduleId', $moduleId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function setRoleRank($roleId, $rank) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->rolesTable SET rank = :rank WHERE id = :roleId");
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->bindParam(':rank', $rank, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
