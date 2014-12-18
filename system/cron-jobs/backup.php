<?php
// Since this script is called from the command line
// we need to set the directories so we can load our configuration and mail-queue object.

$webRoot = dirname(dirname(dirname(__FILE__)));
$cwd = dirname(dirname(__FILE__));

require_once $webRoot . '/config.php';
require_once DIR_DATABASE . 'db.php';
require_once DIR_DATABASE . 'redis.php';
require_once DIR_ADMIN . 'model/config/config-main.php';
require_once DIR_SYSTEM . 'startup.php';
require_once DIR_ADMIN . 'model/common/backup.php';

$backups = new BackupData;

if(!is_writable(DIR_BACKUP)) {
	echo 'The backup directory ' . DIR_BACKUP . ' is not writable. Please ensure your server has write permissions to this directory';
}

// Backup database.
$newBackupId = $backups->backupDatabase();

// Check if backups need to be deleted from the database.
if($globalSettings['delete_backups_older_than']['value'] !== 'never') {
	
	$days = strtotime('-' . $globalSettings['delete_backups_older_than']['value'] . ' days');
	
	$prevBackups = $backups->loadBackups();
	
	foreach($prevBackups as $backup) {
		
		$backupDate = strtotime($backup['backup_date']);
		
		if($backupDate < $days) {
			$backups->deleteBackup($backup['id']);
		}
	}

}
