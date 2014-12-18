<?php
namespace RevenantBlue\Admin;
use RevenantBlue;
use \PDO;

class BackupData extends RevenantBlue\Db {
	private $backupTable;
	
	public function __construct() {
		$this->backupTable = PREFIX . 'backups';
	}
	
	public function loadBackups() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->backupTable
				 ORDER BY backup_date DESC
				 LIMIT 10"
			);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getBackup($backupId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->backupTable WHERE id = :backupId");
			$stmt->bindParam(':backupId', $backupId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function backupDatabase() {
		// Increase execution time for long backups.
		ini_set('max_execution_time', 50000);
		// Create filename
		$backupFile = 'rb-backup-' . time() . '.sql';
		$backupPath = DIR_BACKUP . $backupFile;
		// Backup folder path
		if(DEVELOPMENT_ENVIRONMENT === TRUE) {
			if(SERVER_OS === 'WINDOWS') {
				exec(DIR_MYSQL . 'bin/mysqldump.exe -h ' . DB_HOST_LOCAL . ' -u ' . DB_USER_ADMIN . ' -p' . DB_PASS_ADMIN . ' ' . DB_NAME . ' > ' . $backupPath . ' 2>&1', $output);
			} elseif(SERVER_OS === 'LINUX') {
				exec( DIR_MYSQL . 'mysqldump -h ' . DB_HOST_LOCAL . ' -u ' . DB_USER_ADMIN . ' -p' . DB_PASS_ADMIN . ' ' . DB_NAME . ' > ' . $backupPath, $output, $return);
			}
		} elseif(DEVELOPMENT_ENVIRONMENT === FALSE) {
			exec(DIR_MYSQL . 'mysqldump -h ' . DB_HOST_LOCAL . ' -u ' . DB_USER_ADMIN . ' -p' . DB_PASS_ADMIN . ' ' . DB_NAME . ' > ' . $backupPath . ' 2>&1', $output);
		}
		// Add backup to database.
		try {
			if(!self::$dbh) $this->connect(TRUE);
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->backupTable
				   (backup_file, backup_path, backup_date)
				 VALUES
				   (:backupFile, :backupPath, NOW())"
			);
			$stmt->bindParam(':backupFile', $backupFile, PDO::PARAM_STR);
			$stmt->bindParam(':backupPath', $backupPath, PDO::PARAM_STR);
			$stmt->execute();
			$success = self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
		return $success;
	}

	public function recoverDatabase($backupId) {
		// Increase execution time for long backups.
		ini_set('max_execution_time', 50000);
		// Get backup file info from the database.
		try {
			if(!self::$dbh) $this->connect(TRUE);
			$stmt = self::$dbh->prepare("SELECT * FROM $this->backupTable WHERE id = :backupId");
			$stmt->bindParam(':backupId', $backupId, PDO::PARAM_INT);
			$stmt->execute();
			$backupData = $stmt->fetch(PDO::PARAM_STR);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
		// Restore the database
		if(DEVELOPMENT_ENVIRONMENT === TRUE) {
			if(SERVER_OS === 'WINDOWS') {
				exec('mysql -h ' . DB_HOST_LOCAL . ' -u ' . DB_USER_ADMIN . ' -p' . DB_PASS_ADMIN . '--ignore-table=' . DB_NAME . '.' . $this->backupTable . ' ' . DB_NAME . ' < ' . $backupData['backup_path'], $output);
			} elseif(SERVER_OS === 'LINUX') {
				exec('mysql -h ' . DB_HOST_LOCAL . ' -u ' . DB_USER_ADMIN . ' -p' . DB_PASS_ADMIN . '--ignore-table=' . DB_NAME . '.' . $this->backupTable . ' ' . DB_NAME . ' < ' . $backupData['backup_path'], $output);
			}
		} elseif(DEVELOPMENT_ENVIORNMENT === FALSE) {
			exec('mysql -h ' . DB_HOST . ' -u ' . DB_USER_ADMIN . ' -p' . DB_PASS_ADMIN . '--ignore-table=' . DB_NAME . '.' . $this->backupTable . ' ' . DB_NAME . ' < ' . $backupData['backup_path'], $output);
		} else {
			return FALSE;
		}
		return isset($output) ? array('Output' => $output) : array();
	}

	public function deleteBackup($backupId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->backupTable WHERE id = :backupId");
			$stmt->bindParam(':backupId', $backupId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
}
