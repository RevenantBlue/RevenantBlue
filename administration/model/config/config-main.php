<?php
namespace RevenantBlue\Admin;
use RevenantBlue;
use \PDO;

class Config extends RevenantBlue\Db {

	private $errorLog;
	private $fullKey;
	private $globalTable;
	private $ipBlockTable;
	private $emailsTable;
	private $imageTemplatesTable;
	private $userAccountCodesTable;
	private $contentFormatsTable;
	private $contentFiltersTable;
	private $filterSettingsTable;
	private $formatFiltersTable;
	private $roleFormatsTable;
	private $rolesTable;
	private $cacheTable;
	private $tasksTable;
	

	public function __construct(/*DependencyContainer $dep*/) {
		/*
		$this->dbh = $dep->createConnection();
		$this->errorLog = $dep->errorLog();
		*/
		$this->globalTable = PREFIX . 'global_settings';
		$this->ipBlockTable = PREFIX . 'blocked_ips';
		$this->emailsTable = PREFIX . 'email_templates';
		$this->imageTemplatesTable = PREFIX . 'image_templates';
		$this->userAccountCodesTable = PREFIX . 'users_account_codes';
		$this->contentFormatsTable = PREFIX . 'content_formats';
		$this->contentFiltersTable = PREFIX . 'content_filters';
		$this->filterSettingsTable = PREFIX . 'content_filter_settings';
		$this->formatFiltersTable = PREFIX . 'content_format_filters';
		$this->roleFormatsTable = PREFIX . 'content_role_formats';
		$this->rolesTable = PREFIX . 'acl_roles';
		$this->cacheTable = PREFIX . 'cache';
		$this->tasksTable = PREFIX . 'scheduled_tasks';
	}

	
	private function organizeGlobalSettings($globalPDOArray) {
		foreach($globalPDOArray as $key => $globalSetting) {
			$organizedArray[$globalSetting['alias']] = $globalSetting;
		}
		return $organizedArray;
	}

	private function buildKey() {
		try {
			$this->fullKey = CSRF_KEY;
		} catch(Exception $e) {
			$this->errorLog($e);
		}
	}

	public function loadGlobalSettings() {
		try {
			$this->buildKey();
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT *, AES_DECRYPT(secure_value, :key) AS secure_value FROM $this->globalTable");
			$stmt->bindParam(':key', $this->fullKey, PDO::PARAM_STR);
			$stmt->execute();
			return $this->organizeGlobalSettings($stmt->fetchAll(PDO::FETCH_ASSOC));
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadBlockedIps() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->ipBlockTable");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadEmailTemplates() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->emailsTable");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadImageTemplates() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->imageTemplatesTable ORDER BY id ASC");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadContentFormats() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->contentFormatsTable ORDER BY order_of_item");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadCache() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->cacheTable ORDER BY cache_key ASC");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadTasks() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->tasksTable ORDER BY order_of_item ASC");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertIpToBlock($ip) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("INSERT INTO $this->ipBlockTable (ip_address) VALUES (:ip)");
			$stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertFormat($formatName, $formatAlias, $orderOfItem) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->contentFormatsTable
				   (format_name, format_alias, order_of_item)
				 VALUES
				   (:formatName, :formatAlias, :orderOfItem)"
			);
			$stmt->bindParam(':formatName', $formatName, PDO::PARAM_STR);
			$stmt->bindParam(':formatAlias', $formatAlias, PDO::PARAM_STR);
			$stmt->bindParam(':orderOfItem', $orderOfItem, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertFilterSettingForFormat($formatId, $filterId, $filterSetting, $filterValue) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->filterSettingsTable
				   (format_id, filter_id, filter_setting, filter_value)
				 VALUES
				   (:formatId, :filterId, :filterSetting, :filterValue)"
			);
			$stmt->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt->bindParam(':filterId', $filterId, PDO::PARAM_INT);
			$stmt->bindParam(':filterSetting', $filterSetting, PDO::PARAM_STR);
			$stmt->bindParam(':filterValue', $filterValue, PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertFilterForFormat($filterId, $formatId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("INSERT INTO $this->formatFiltersTable (filter_id, format_id) VALUES (:filterId, :formatId)");
			$stmt->bindParam(':filterId', $filterId, PDO::PARAM_INT);
			$stmt->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertRoleForFormat($roleId, $formatId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("INSERT INTO $this->roleFormatsTable (role_id, format_id) VALUES (:roleId, :formatId)");
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertImageTemplate($templateName, $templateWidth, $templateHeight, $templateType, $templateQuality = 85) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->imageTemplatesTable
				   (template_name, template_width, template_height, template_type, template_quality)
				 VALUES
				   (:templateName, :templateWidth, :templateHeight, :templateType, :templateQuality)"
			);
			$stmt->bindParam(':templateName', $templateName, PDO::PARAM_STR);
			$stmt->bindParam(':templateWidth', $templateWidth, PDO::PARAM_INT);
			$stmt->bindParam(':templateHeight', $templateHeight, PDO::PARAM_INT);
			$stmt->bindParam(':templateType', $templateType, PDO::PARAM_STR);
			$stmt->bindParam(':templateQuality', $templateQuality, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertEmailTemplate($template) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->emailsTable
				   (template_name, template_alias, subject, body, description)
				 VALUES
				   (:templateName, :templateAlias, :subject, :body, :description)"
			);
			$stmt->bindParam(':templateName', $template['name'], PDO::PARAM_STR);
			$stmt->bindParam(':subject', $template['subject'], PDO::PARAM_STR);
			$stmt->bindParam(':description', $template['description'], PDO::PARAM_STR);
			$stmt->bindParam(':body', $template['body'], PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertCache($key, $description, $serialized) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("INSERT INTO $this->cacheTable (cache_key, description, serialized) VALUES (:key, :description, :serialized)");
			$stmt->bindParam(':key', $key, PDO::PARAM_STR);
			$stmt->bindParam(':description', $description, PDO::PARAM_STR);
			$stmt->bindParam(':serialized', $serialized, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function insertTask($task) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->tasksTable
					(name, alias, description, command, cronjob, minutes, hours, days_of_month, months, days_of_week, log)
				VALUES
					(:name, :alias, :description, :command, :cronjob, :minutes, :hours, :daysOfMonth, :months, :daysOfWeek, :log)"
			);
			$stmt->bindParam(':name', $task['name'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $task['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':description', $task['description'], PDO::PARAM_STR);
			$stmt->bindParam(':command', $task['command'], PDO::PARAM_STR);
			$stmt->bindParam(':cronjob', $task['cronjob'], PDO::PARAM_STR);
			$stmt->bindParam(':minutes', $task['minutes'], PDO::PARAM_STR);
			$stmt->bindParam(':hours', $task['hours'], PDO::PARAM_STR);
			$stmt->bindParam(':daysOfMonth', $task['daysOfMonth'], PDO::PARAM_STR);
			$stmt->bindParam(':months', $task['months'], PDO::PARAM_STR);
			$stmt->bindParam(':daysOfWeek', $task['daysOfWeek'], PDO::PARAM_STR);
			$stmt->bindParam(':log', $task['log'], PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateConfiguration($value, $configAlias, $secure = FALSE) {
		try {
			if(!self::$dbh) $this->connect();
			if($secure) {
				$this->buildKey();
				$stmt = self::$dbh->prepare("UPDATE $this->globalTable SET secure_value = AES_ENCRYPT(:value, :key) WHERE alias = :configAlias");
				$stmt->bindParam(':key', $this->fullKey, PDO::PARAM_STR);
			} else {
				$stmt = self::$dbh->prepare("UPDATE $this->globalTable SET value = :value WHERE alias = :configAlias");
			}
			$stmt->bindParam(':configAlias', $configAlias, PDO::PARAM_INT);
			$stmt->bindParam(':value', $value, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateEmailTemplate($id, $subject, $body) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->emailsTable SET subject = :subject, body = :body WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
			$stmt->bindParam(':body', $body, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateFormat($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->");
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateFormatOrder($formatId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->contentFormatsTable SET order_of_item = :orderOfItem WHERE format_id = :formatId");
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateFilterOrder($filterId, $formatId, $orderOfItem) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->formatFiltersTable
				 SET order_of_item = :orderOfItem
				 WHERE filter_id = :filterId AND format_id = :formatId"
			);
			$stmt->bindParam(':filterId', $filterId, PDO::PARAM_INT);
			$stmt->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt->bindParam(':orderOfItem', $orderOfItem, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateFilterSettingForFormat($settingId, $filterValue) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->filterSettingsTable
				 SET filter_value = :filterValue
				 WHERE id = :settingId"
			);
			$stmt->bindParam(':settingId', $settingId, PDO::PARAM_INT);
			$stmt->bindParam(':filterValue', $filterValue, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateImageTemplate($templateId, $width, $height, $type, $quality) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->imageTemplatesTable
				 SET template_width = :width, template_height = :height, template_type = :type, template_quality = :quality
				 WHERE id = :templateId"
			);
			$stmt->bindParam(':templateId', $templateId, PDO::PARAM_INT);
			$stmt->bindParam(':width', $width, PDO::PARAM_INT);
			$stmt->bindParam(':height', $height, PDO::PARAM_INT);
			$stmt->bindParam(':type', $type, PDO::PARAM_STR);
			$stmt->bindParam(':quality', $quality, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateTask($task) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->tasksTable SET
					name = :name
				  , alias = :alias
				  , description = :description
				  , command = :command
				  , cronjob = :cronjob
				  , minutes = :minutes
				  , hours = :hours
				  , days_of_month = :daysOfMonth
				  , months = :months
				  , days_of_week = :daysOfWeek
				  , log = :log
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $task['id'], PDO::PARAM_INT);
			$stmt->bindParam(':name', $task['name'], PDO::PARAM_STR);
			$stmt->bindParam(':alias', $task['alias'], PDO::PARAM_STR);
			$stmt->bindParam(':description', $task['description'], PDO::PARAM_STR);
			$stmt->bindParam(':command', $task['command'], PDO::PARAM_STR);
			$stmt->bindParam(':cronjob', $task['cronjob'], PDO::PARAM_STR);
			$stmt->bindParam(':minutes', $task['minutes'], PDO::PARAM_STR);
			$stmt->bindParam(':hours', $task['hours'], PDO::PARAM_STR);
			$stmt->bindParam(':daysOfMonth', $task['daysOfMonth'], PDO::PARAM_STR);
			$stmt->bindParam(':months', $task['months'], PDO::PARAM_STR);
			$stmt->bindParam(':daysOfWeek', $task['daysOfWeek'], PDO::PARAM_STR);
			$stmt->bindParam(':log', $task['log'], PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}


	public function deleteBlockedIp($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->ipBlockTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteFilterForFormat($filterId, $formatId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->formatFiltersTable WHERE filter_id = :filterId AND format_id = :formatId");
			$stmt->bindParam(':filterId', $filterId, PDO::PARAM_INT);
			$stmt->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteFormat($formatId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("DELETE FROM $this->contentFormatsTable WHERE id = :formatId");
			$stmt->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("DELETE FROM $this->filterSettingsTable WHERE format_id = :formatId");
			$stmt2->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt2->execute();
			$stmt3 = self::$dbh->prepare("DELETE FROM $this->formatFiltersTable WHERE format_id = :formatId");
			$stmt3->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt3->execute();
			$stmt4 = self::$dbh->prepare("DELETE FROM $this->roleFormatsTable WHERE format_id = :formatId");
			$stmt4->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt4->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteRoleForFormat($roleId, $formatId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->roleFormatsTable WHERE role_id = :roleId AND format_id = :formatId");
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteImageTemplate($templateId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->imageTemplatesTable WHERE id = :templateId");
			$stmt->bindParam(':templateId', $templateId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteEmailTemplate($templateId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->emailsTable WHERE id = :templateId");
			$stmt->bindParam(':templateId', $templateId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteCache($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->cacheTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteTask($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->tasksTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getBannedIp($ip) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->ipBlockTable WHERE ip_address = :ip");
			$stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getEmailTemplate($templateAlias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->emailsTable WHERE template_alias = :templateAlias");
			$stmt->bindParam(':templateAlias', $templateAlias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getContentFilters() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->contentFiltersTable");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getContentFilterForRole($roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->contentFormatsTable AS cf
				 LEFT JOIN $this->roleFormatsTable AS rf ON cf.id = rf.format_id
				 WHERE rf.role_id = :roleId
				 GROUP BY cf.id
				 ORDER BY cf.order_of_item
				 LIMIT 1"
			);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getFormatFilters($formatId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, fft.order_of_item as filter_order FROM $this->formatFiltersTable as fft
				 LEFT JOIN $this->contentFormatsTable as cft ON cft.id = fft.format_id
				 LEFT JOIN $this->contentFiltersTable as cflt ON cflt.id = fft.filter_id
				 WHERE cft.id = :formatId
				 ORDER BY fft.order_of_item"
			);
			$stmt->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getFormatFiltersByRole($roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT cft.id, cft.format_name FROM $this->contentFormatsTable as cft
				 LEFT JOIN $this->roleFormatsTable AS rft ON cft.id = rft.format_id
				 WHERE rft.role_id = :roleId"
			);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getFormatFiltersByAlias($formatAlias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT *, fft.order_of_item as filter_order FROM $this->formatFiltersTable as fft
				 LEFT JOIN $this->contentFormatsTable as cft ON cft.id = fft.format_id
				 LEFT JOIN $this->contentFiltersTable as cflt ON cflt.id = fft.filter_id
				 WHERE cft.format_alias = :formatAlias
				 ORDER BY fft.order_of_item"
			);
			$stmt->bindParam(':formatAlias', $formatAlias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	// Returns a column role names for the format or an array containing the id and name depending on the justNames argument, default is FALSE.
	public function getRolesForFormat($formatId, $justNames = FALSE) {
		try {
			if(!self::$dbh) $this->connect();
			if($justNames) {
				$stmt = self::$dbh->prepare(
					"SELECT r.name FROM $this->roleFormatsTable as rft
					 LEFT JOIN $this->rolesTable as r ON r.id = rft.role_id
					 WHERE rft.format_id = :formatId"
				);
				$stmt->bindParam(':formatId', $formatId, PDO::PARAM_INT);
				$stmt->execute();
				return $stmt->fetchAll(PDO::FETCH_COLUMN);
			} else {
				$stmt = self::$dbh->prepare(
					"SELECT r.id, r.name FROM $this->roleFormatsTable as rft
					 LEFT JOIN $this->rolesTable as r ON r.id = rft.role_id
					 WHERE rft.format_id = :formatId"
				);
				$stmt->bindParam(':formatId', $formatId, PDO::PARAM_INT);
				$stmt->execute();
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfFormats() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->contentFormatsTable");
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setContentFormatOrder($formatId, $order) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->contentFormatsTable SET order_of_item = :order WHERE id = :formatId");
			$stmt->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt->bindParam(':order', $order, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getSettingsForFilters($formatId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->filterSettingsTable WHERE format_id = :formatId");
			$stmt->bindParam(':formatId', $formatId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function verifyCode($code, $type) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->userAccountCodesTable
				 WHERE user_code = :code AND type = :type AND date_created > :time"
			);
			$stmt->bindParam(':code', $code, PDO::PARAM_STR);
			$stmt->bindParam(':type', $type, PDO::PARAM_STR);
			$stmt->bindValue(':time', time() - 86400, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteCode($userId, $code) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->userAccountCodesTable WHERE user_id = :userId AND user_code = :code");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':code', $code, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getImageTemplate($templateId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->imageTemplatesTable WHERE id = :templateId");
			$stmt->bindParam(':templateId', $templateId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getGlobalByAlias($globalAlias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->globalTable WHERE alias = :alias");
			$stmt->bindParam(':alias', $globalAlias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getCacheKey($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->cacheTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getTask($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->tasksTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function getTaskByAlias($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->tasksTable WHERE alias = :alias");
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
