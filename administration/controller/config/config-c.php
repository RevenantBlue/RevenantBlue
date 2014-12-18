<?php
namespace RevenantBlue\Admin;
use \CronSchedule;
use DateTimeZone;
use \stdClass;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/common/global-validation.php';
require_once DIR_ADMIN . 'controller/config/config-validation.php';
require_once DIR_ADMIN . 'model/config/config-main.php';
require_once DIR_SYSTEM . 'library/cron-interpreter/cron-interpreter.php';

// Get the current time.
$theTime = time();

if($_SERVER['REQUEST_METHOD'] === "GET") {
	// Load the user options for the configuration page - this determines which options to show for the user.
	$optionsForPage = $users->getOptionsByGroup('config');
	$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'config');
	// Load configuration requirements based on the setting.
	if(isset($_GET['setting'])) {
		if($_GET['setting'] === 'ip-addresses')	{
			$blockedIps = $config->loadBlockedIps();
		} elseif($_GET['setting'] === 'global-settings') {
			$timezones = getTimezones();
			$currentTimezone = $globalSettings['timezone']['value'];
			$timezoneObj = new DateTimeZone($currentTimezone);
			$timezoneTransiations = $timezoneObj->getTransitions($theTime, $theTime + 31556926);
			$nextDayLightSavings = $timezoneTransiations[1]['ts'];
		} elseif($_GET['setting'] === 'account-settings') {
			$emailTemplates = $config->loadEmailTemplates();
			$imageTemplates = $config->loadImageTemplates();
		} elseif($_GET['setting'] === 'content-filtering') {
			// If editing a format.
			if(isset($_GET['format']) && isset($_GET['action']) && $_GET['action'] === 'edit') {
				$contentFilters = $config->getContentFilters();
				$formatFilters = $config->getFormatFiltersByAlias($_GET['format']);
				$numOfFilters = count($formatFilters);
				foreach($contentFilters as $contentFilter) {
					if(!in_multiarray($contentFilter['filter_name'], $formatFilters)) {
						$contentFilter['filter_order'] = $numOfFilters + 1;
						$inactiveFilters[] = $contentFilter;
						$numOfFilters += 1;
					}
				}
				// Build active filters object for the format.
				$activeFilters = new stdClass;
				foreach($formatFilters as $formatFilter) {
					$activeFilters->order[] = $formatFilter['filter_name'];
					$activeFilters->id[] = $formatFilter['filter_id'];
					$activeFilters->name[] = $formatFilter['filter_name'];
				}
				// Assign the format name as a key value pair in the array.
				$format['format_name'] = $formatFilters[0]['format_name'];
				$format['format_id'] = $formatFilters[0]['format_id'];
				$roles = $acl->loadRoles();
				$rolesForFormat = $config->getRolesForFormat($format['format_id'], TRUE);
				// Load the settings for filters that have them for this particular format.
				$filterSettings = $config->getSettingsForFilters($format['format_id']);
			// If creating a new format.
			} elseif(isset($_GET['action']) && $_GET['action'] === 'new') {
				$roles = $acl->loadRoles();
				$contentFilters = $config->getContentFilters();
			} else {
				$contentFormats = $config->loadContentFormats();
				// Build the structure that will be used to display the roles for reach content format.
				foreach($contentFormats as $contentFormat) {
					$rolesForFormat[$contentFormat['id']] = $config->getRolesForFormat($contentFormat['id'], TRUE);
				}
				// Proper comma separation for role string.
				foreach($rolesForFormat as $key => $value) {
					$rolesForFormat[$key] = implode(', ', $rolesForFormat[$key]);
				}
			}
		} elseif($_GET['setting'] === 'image-templates') {
			$imageTemplates = $config->loadImageTemplates();
			$templateTypes = array('exact', 'portrait', 'landscape', 'crop', 'auto');
		} elseif($_GET['setting'] === 'media-settings') {
			$imageTemplates = $config->loadImageTemplates();
		} elseif($_GET['setting'] === 'maintenance') {
			require_once DIR_ADMIN . 'model/common/backup.php';
			$backups = new BackupData;
			$backupsList = $backups->loadBackups();
		} elseif($_GET['setting'] === 'logging-and-errors') {
			$errorLog = @file(DIR_SYSTEM . 'logs/error.log');
			if(empty($errorLog) || count($errorLog) === 1) {
				if(!file_exists(DIR_SYSTEM . 'logs/error.log')) {
					$errorLog[0] = 'No error log was found at ' . DIR_SYSTEM . 'logs/error.log.';
				} else {
					$errorLog[0] = 'There are currently no errors logged.';
				}
			}
		} elseif($_GET['setting'] === 'performance') {
			$cacheList = $config->loadCache();
		} elseif($_GET['setting'] === 'scheduled-tasks') {
			$tasks = $config->loadTasks();
			
			$cron = CronSchedule::fromCronString('* * * * * *', 'en');
			$cronDefault = $cron->asNaturalLanguage();
		}
	}
	
	if(isset($_GET['cache-key'])) {
		$cachedKey = $config->getCacheKey((int)$_GET['cache-key']);
		$rdh = $redis->loadRedisHandler();
		if(aclVerify('administer site config')) {
			if($cachedKey['serialized']) {
				print_r2(hsc_deep(unserialize($rdh->get(PREFIX . $cachedKey['cache_key']))));
			} else {
				print_r2(json_decode($rdh->get(PREFIX . $cachedKey['cache_key'])));
			}
			exit;
		}
	}
}

if($_SERVER['REQUEST_METHOD'] === "POST") {
	if(isset($_POST['submitSiteInfo'])) {
		$configValidation = new ConfigValidation;
		$configValidation->validateUrl($_POST['config']['site_address']);
		$configValidation->validateUrl($_POST['config']['admin_address']);
		$configValidation->validateEmail($_POST['config']['system_email']);
		if(empty($configValidation->errors)) {
			// If the location of the admin site has been changed
			if($globalSettings['admin_address']['value'] !== $_POST['config']['admin_address']) {
				$oldAdminLocation = str_replace($globalSettings['site_address']['value'] . '/', '', $globalSettings['admin_address']['value']);
				$newAdminLocation = str_replace($globalSettings['site_address']['value'] . '/', '', $_POST['config']['admin_address']);
				/*
				// Update htaccess with the new admin location.
				$htaccess = file_get_contents(DIR_SITE_ROOT . '.htaccess');
				$htaccess = str_replace($oldAdminLocation . '/', $newAdminLocation . '/', $htaccess);
				file_put_contents(DIR_SITE_ROOT . '.htaccess', $htaccess);
				// Update the configuration file with the new admin location.
				$sysConfig = file_get_contents(DIR_SITE_ROOT . 'config.php';
				$sysConfig = str_replace($oldAdminLocation . '/', $newAdminLocation . '/', $sysConfig);
				file_put_contents(DIR_SITE_ROOT . 'config.php', $sysConfig);
				*/
			}
			foreach($_POST['config'] as $alias => $value) {
				if($alias === 'date_format' && $value === "on") {
					$value = $_POST['custom_date_format'];
				}
				if($alias === 'time_format' && $value === "on") {
					$value = $_POST['custom_time_format'];
				}
				// Separate secure additions to the global configurations by adding the true flag to the update method call.
				if($alias === 'email_password') {
					$updateConfig[] = $config->updateConfiguration($value, $alias, 1);
				} else {
					$updateConfig[] = $config->updateConfiguration($value, $alias , 0);
				}
			}
			if(!in_array(0, $updateConfig)) {
				$_SESSION['success'] = "Site configuration successfully updated.";
			} else {
				$_SESSION['errors'] = "An error occured while updating the configuration settings.";
			}
			// Delete the global settings cache and have it rebuilt with the following request.
			if(REDIS === TRUE) {
				$redis->del(PREFIX . 'globalSettings');
			}
			
			header('Location: ' . HTTP_ADMIN . 'config/global-settings', TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['submitAccountSettings'])) {
		foreach($_POST['config'] as $alias => $value) {
			if(is_array($value)) {
				foreach($_POST['config']['email'] as $emailTemplateId => $emailTemplateData) {
					$updateConfig[] = $config->updateEmailTemplate($emailTemplateId, $emailTemplateData['subject'], $emailTemplateData['body']);
				}
			} else {
				$updateConfig[$alias] = $config->updateConfiguration($value, $alias);
			}
		}
		
		//print_r2($updateConfig); exit;
		
		if(!in_array(0, $updateConfig)) {
			$_SESSION['success'] = "Site configuration successfully updated.";
		} else {
			$_SESSION['errors'] = "An error occured while updating the configuration settings.";
		}
		
		// Delete the global settings cache and have it rebuilt with the following request.
		if(REDIS === TRUE) {
			$redis->del(PREFIX . 'globalSettings');
		}
		
		header('Location: ' . HTTP_ADMIN . 'config/account-settings', TRUE, 302);
		exit;
	} elseif(isset($_POST['submitSiteSecurity'])) {
		
		$sslConnection = @fsockopen('ssl://' . HOST_NAME, 443, $errno, $errstr, 30);
		
		if(!empty($sslConnection)) {
			foreach($_POST['config'] as $alias => $value) {
				$updateConfig[] = $config->updateConfiguration($value, $alias);
			}
			if(!in_array(0, $updateConfig)) {
				$_SESSION['success'] = "Site configuration successfully updated.";
			} else {
				$_SESSION['errors'] = "An error occured while updating the configuration settings.";
			}
			
			// Delete the global settings cache and have it rebuilt with the following request.
			if(REDIS === TRUE) {
				$redis->del(PREFIX . 'globalSettings');
			}
			
			$_SESSION['success'] = 'Security settings updated successfully.';
		} else {
			$_SESSION['errors'] = 'Could not connect to port 443 via SSL. Please ensure you have configured your server and certificates correctly.';
		}
		
		header('Location: ' . HTTP_ADMIN . 'config/site-security', TRUE, 302);
		exit;
	} elseif(isset($_POST['submitMaintenance'])) {
		foreach($_POST['config'] as $alias => $value) {
			$updateConfig[] = $config->updateConfiguration($value, $alias);
		}
		if(!in_array(0, $updateConfig)) {
			$_SESSION['success'] = "Site configuration successfully updated.";
		} else {
			$_SESSION['errors'] = "An error occured while updating the configuration settings.";
		}
		
		// Delete the global settings cache and have it rebuilt with the following request.
		if(REDIS === TRUE) {
			$redis->del(PREFIX . 'globalSettings');
		}
		
		header('Location: ' . HTTP_ADMIN . 'config/maintenance', TRUE, 302);
		exit;
	} elseif(isset($_POST['submitNewFormat'])) {
		if(!isset($_POST['filters']) || empty($_POST['formatName'])) {
			if(!isset($_POST['filters'])) $_SESSION['errors'][] = "You must select at least one filter to assign to this format.";
			if(empty($_POST['formatName'])) $_SESSION['errors'][] = "The format name cannot be empty.";
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
		$globalValidation = new GlobalValidation;
		$formatName = trim($_POST['formatName']);
		$formatAlias = $globalValidation->createAlias($formatName);
		$formatOrder = (int)$config->getNumOfFormats() + 1;
		$newFormatId = $config->insertFormat($formatName, $formatAlias, $formatOrder);
		// Insert the roles selected for this format.
		if(!empty($_POST['roles'])) {
			foreach($_POST['roles'] as $role) {
				$config->insertRoleForFormat($role, $newFormatId);
			}
		}
		// Insert selected filters for the format
		foreach($_POST['filters'] as $filterId => $filterStatus) {
			$filterId = (int)$filterId;
			$config->insertFilterForFormat($filterId, $newFormatId);
		}
		// Order the filters
		foreach($_POST['filterOrder'] as $filterId => $filterOrder) {
			$config->updateFilterOrder($filterId, $newFormatId, $filterOrder);
		}
		// Insert filter specific settings.
		$config->insertFilterSettingForFormat($newFormatId, 1, 'Allowed HTML tags', $_POST['filter-setting'][0]);
		$config->insertFilterSettingForFormat($newFormatId, 1, 'Add rel="no follow" to all links', $_POST['filter-setting'][1]);
		$config->insertFilterSettingForFormat($newFormatId, 4, 'Maximum link text length', $_POST['filter-setting'][2]);
		if(empty($_SESSION['errors'])) {
			clearCache();
			$_SESSION['success'] = 'The ' . hsc($_POST['formatName']) . ' format has been successfully saved.';
			header('Location: ' . HTTP_ADMIN . 'config/content-filtering', TRUE, 302);
			exit;
		} else {
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['submitFormatFilters']) && isset($_POST['formatId']) && isset($_POST['filter-setting'])) {
		// If no roles have been selected declare the $_POST variable.
		if(!isset($_POST['roles'])) $_POST['roles'] = array();
		if(!isset($_POST['filters'])) {
			$_SESSION['errors'] = "At least one filter must be selected.";
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
		$rolesForFormat = $config->getRolesForFormat($_POST['formatId']);
		// Organize the roles in an array with the id => name format.
		$rolesForFormat = organizePDO($rolesForFormat, 'id', 'name');
		$rolesToAdd = array_diff_key($_POST['roles'], $rolesForFormat);
		$rolesToRemove = array_diff_key($rolesForFormat, $_POST['roles']);
		// Add new roles.
		if(!empty($rolesToAdd)) {
			foreach($rolesToAdd as $roleToAdd) {
				$config->insertRoleForFormat($roleToAdd, $_POST['formatId']);
			}
		}
		// Remove old roles
		if(!empty($rolesToRemove)) {
			foreach($rolesToRemove as $roleToRemove) {
				$config->deleteRoleForFormat($roleToRemove, $_POST['formatId']);
			}
		}
		// Load the filters that are currently being used for this format.
		$formatFilters = organizePDO($config->getFormatFilters($_POST['formatId']), 'filter_id', 'filter_name');
		// Find the difference between the new filters and the old filters so the old ones can be inserted/deleted.
		$filtersToAdd = array_diff_key($_POST['filters'], $formatFilters);
		$filtersToRemove = array_diff_key($formatFilters,$_POST['filters']);
		// Add new filters.
		if(!empty($filtersToAdd)) {
			foreach($filtersToAdd as $filterId => $filterName) {
				$config->insertFilterForFormat($filterId, $_POST['formatId']);
			}
		}
		// Remove old filters.
		if(!empty($filtersToRemove)) {
			foreach($filtersToRemove as $filterId => $filterName) {
				$config->deleteFilterForFormat($filterId, $_POST['formatId']);
			}
		}
		// Set the filter order
		if(isset($_POST['filterOrder']) && is_array($_POST['filterOrder'])) {
			foreach($_POST['filterOrder'] as $filterId => $filterOrder) {
				// Ensure that the filter is active. Without this, inactive filters will also be added the database.
				if(array_key_exists($filterId, $_POST['filters'])) {
					$config->updateFilterOrder($filterId, $_POST['formatId'], $filterOrder);
				}
			}
		}
		// Load the settings for filters that have them for this particular format.
		foreach($_POST['filter-setting'] as $settingId => $settingValue) {
			$config->updateFilterSettingForFormat($settingId, $settingValue);
		}
		if(empty($_SESSION['errors'])) {
			$_SESSION['success'] = "Content format updated successfully.";
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['submitMediaSettings'])) {
		foreach($_POST['config'] as $alias => $value) {
			// Remove trailing slash
			if($alias === 'media_upload_url') $value = rtrim($value, '/');
			$updateConfig[] = $config->updateConfiguration($value, $alias);
		}
		if(!in_array(0, $updateConfig)) {
			$_SESSION['success'] = "Site configuration successfully updated.";
		} else {
			$_SESSION['errors'] = "An error occured while updating the configuration settings.";
		}
		// Remove the old cached global settings.
		$redis->del(PREFIX . 'globalSettings');
		
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	// Handle all article AJAX requests with JSON.
	} elseif(isset($_POST['adminRequest'])) {
		$adminReq = json_decode($_POST['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type)) {
			if($adminReq->type === 'config') {
				if($adminReq->action === 'block-ip' && isset($adminReq->ip) && aclVerify('administer site config')) {
					$validIp = filter_var($adminReq->ip, FILTER_VALIDATE_IP);
					if($validIp) {
						// Place the block in the database.
						$success = $config->insertIpToBlock($validIp);
						// Place the block in Redis if Redis has been enabled.
						if(REDIS === TRUE) {
							$rdh = $redis->loadRedisHandler();
							$rdh->sadd(PREFIX . 'bannedIps', $validIp);
						}
						$adminReq->success = $success;
					}
				} elseif($adminReq->action === 'unblock-ip' && isset($adminReq->id) && aclVerify('administer site config')) {
					$success = $config->deleteBlockedIp((int)$adminReq->id);
					// Remove the user from the redis set if redis caching is enabled.
					if(REDIS === TRUE && !empty($adminReq->ip)) {
						$rdh = $redis->loadRedisHandler();
						$rdh->srem(PREFIX . 'bannedIps', $adminReq->ip);
					}
					$adminReq->success = $success;
				} elseif($adminReq->action === 'reorder-formats' && isset($adminReq->formatOrder) && is_object($adminReq->formatOrder)) {
					foreach($adminReq->formatOrder as $order => $formatId) {
						$config->setContentFormatOrder($formatId, $order);
					}
				} elseif($adminReq->action === 'delete-format' && isset($adminReq->id) && aclVerify('administer site config')) {
					$success = $config->deleteFormat($adminReq->id);
					$adminReq->success = $success;
				} elseif($adminReq->action === 'new-template') {
					$newId = $config->insertImageTemplate($adminReq->templateName, $adminReq->imageWidth, $adminReq->imageHeight, $adminReq->templateType, $adminReq->templateQuality);
					$adminReq->id = $newId;
				} elseif($adminReq->action === 'save-template') {
					$update = $config->updateImageTemplate($adminReq->id, $adminReq->templateWidth, $adminReq->templateHeight, $adminReq->templateType, $adminReq->templateQuality);
					$adminReq->update = $update;
				} elseif($adminReq->action === 'delete-template') {
					$delete = $config->deleteImageTemplate($adminReq->id);
				} elseif($adminReq->action === 'backup-database') {
					require_once DIR_ADMIN . 'model/common/backup.php';
					$backups = new BackupData;
					if(!is_writable(DIR_BACKUP)) {
						$adminReq->errors = 'The backup directory ' . DIR_BACKUP . ' is not writable. Please ensure your server has write permissions to this directory';
					}
					$newBackupId = $backups->backupDatabase();
					$newBackup = $backups->getBackup($newBackupId);
					$adminReq->id = $newBackup['id'];
					$adminReq->backupDate = date('d-M-Y h:i A', strtotime($newBackup['backup_date']));
					$adminReq->backupFile = $newBackup['backup_file'];
					$adminReq->backupPath = $newBackup['backup_path'];
				} elseif($adminReq->action === 'change-auto-del-backups-frequency') {
					$config->updateConfiguration($adminReq->frequency, 'delete_backups_older_than');
					// Delete the global settings cache and have it rebuilt with the following request.
					if(REDIS === TRUE) {
						$redis->del(PREFIX . 'globalSettings');
					}
				} elseif($adminReq->action === 'delete-backup') {
					require_once DIR_ADMIN . 'model/common/backup.php';
					$backups = new BackupData;
					$backups->deleteBackup($adminReq->backupId);
				} elseif($adminReq->action === 'clear-cache') {
					clearCache();
					$adminReq->success = true;
				} elseif($adminReq->action === 'insert-email-template') {
					$validation = new GlobalValidation;
					$contentFormat = $validation->getContentFormatForUser();
					$emailTemplate['name'] = isset($adminReq->name) ? $adminReq->name : '';
					$emailTemplate['alias'] = $validation->validateAlias($adminReq->name, FALSE); 
					$emailTemplate['description'] = isset($adminReq->description) ? $adminReq->description : '';
					$emailTemplate['subject'] = isset($adminReq->subject) ? $adminReq->subject : '';
					$emailTemplate['body'] = isset($adminReq->body) ? $adminReq->body : '';
					
					// Insert the new template if there are no errors
					if(empty($validation->errors)) {
						$templateExists = $config->getEmailTemplate($emailTemplate['alias']);
						if(empty($templateExists)) {
							$adminReq->newTemplateId = $config->insertEmailTemplate($emailTemplate);
						} else {
							$adminReq->errors = 'You cannot have two email templates with the same name.';
						}
					} else {
						$adminReq->errors = $validation->errors;
					}
					
				} elseif($adminReq->action === 'delete-email-template') {
					if(!empty($adminReq->id)) {
						$adminReq->templateDeleted = $config->deleteEmailTemplate($adminReq->id);
					}
				} elseif($adminReq->action === 'clear-error-log') {
					$adminReq->success = file_put_contents(DIR_SYSTEM . 'logs/error.log', '');
				} elseif($adminReq->action === 'add-cache-key') {
					if(!empty($adminReq->key) && !empty($adminReq->description)) {
						$adminReq->keyId = $config->insertCache($adminReq->key, $adminReq->description, $adminReq->serialized);
					}
				} elseif($adminReq->action === 'delete-cache-keys') {
					if(!empty($adminReq->ids) && is_array($adminReq->ids)) {
						foreach($adminReq->ids as $keyId) {
							$cacheKey = $config->getCacheKey($keyId);
							// Delete the key from the redis store.
							if(REDIS) {
								$rdh = $redis->loadRedisHandler();
								$rdh->del(PREFIX . $cacheKey['cache_key']);
							}
							// Delete the key from the database.
							$config->deleteCache($keyId);
						}
					}
				} elseif($adminReq->action === 'get-cache-key') {
					if(REDIS) {
						$rdh = $redis->loadRedisHandler();
						$adminReq->keyValue = $rdh->get(PREFIX . $key);
					}
				} elseif($adminReq->action === 'translate-cron') {
					if(!empty($adminReq->cronjob)) {
						try {
							$cron = CronSchedule::fromCronString($adminReq->cronjob, 'en');
							$adminReq->inEnglish = $cron->asNaturalLanguage();
						} catch(Exception $e) {
							$adminReq->error = $e->getMessage();
						}
					}
				} elseif($adminReq->action === 'add-cronjob') {
					$output = exec("crontab -l 2>&1", $cronFile);

					$task['name'] = isset($adminReq->name) ? $adminReq->name : '';
					$task['description'] = isset($adminReq->description) ? $adminReq->description : '';
					$task['command'] = isset($adminReq->command) ? $adminReq->command : '';
					$task['minutes'] = isset($adminReq->minutes) ? $adminReq->minutes : '';
					$task['hours'] = isset($adminReq->hours) ? $adminReq->hours :'';
					$task['daysOfMonth'] = isset($adminReq->daysOfMonth) ? $adminReq->daysOfMonth : '';
					$task['months'] = isset($adminReq->months) ? $adminReq->months : '';
					$task['daysOfWeek'] = isset($adminReq->daysOfWeek) ? $adminReq->daysOfWeek : '';
					$task['years'] = isset($adminReq->years) ? $adminReq->years : '';
					$task['log'] = isset($adminReq->log) ? $adminReq->log : 1;
					
					$taskValidation = new TaskValidation($task);
					
					if(empty($taskValidation->errors)) {
						$taskValidation->task['cronjob'] = $cronjob = $adminReq->minutes . ' ' . $adminReq->hours . ' ' . $adminReq->daysOfMonth . ' ' . $adminReq->months . ' ' . $adminReq->daysOfWeek . ' ' . $adminReq->command;
						if($task['log']) {
							$cronjob .= ' > ' . DIR_LOGS . 'tasks/' . $taskValidation->alias . '.log';
						}
						
						// If there are no crontabs remove the entry.
						if(stristr($cronFile[0], 'no crontab')) {
							$cronFile[0] = $cronjob;
						} else {
							array_push($cronFile, $cronjob);
						}
						
						// Implode array to make the cron file.
						$cronFile = implode(PHP_EOL, $cronFile);
						$cronFile .= PHP_EOL;
						
						// Remove any old log files with the same name
						if(file_exists(DIR_LOGS . 'tasks/' . $taskValidation->alias . '.log')) {
							@unlink(DIR_LOGS . 'tasks/' . $taskValidation->alias . '.log');
						}
						
						// Add the finalized cronjob to the validated task array.
						$adminReq->cronjob = $taskValidation->task['cronjob'] = $cronjob;
						
						// Build the cron file.
						file_put_contents(DIR_SYSTEM . 'tmp/crontab.txt', $cronFile); 
						// Install the crontab.
						$output = shell_exec('crontab ' . DIR_SYSTEM . 'tmp/crontab.txt 2>&1');
						// Remove the temporary file.
						@unlink(DIR_SYSTEM . 'tmp/crontab.txt');
						
						//print_r2($taskValidation->task); exit;
						// Check for crontab installation errors, if no errors go ahead and insert the new task.
						if(stristr($output, "can't install")) {
							$adminReq->errors[] = $output;
						} else {
							$adminReq->taskId = $config->insertTask($taskValidation->task);
						}
					} else {
						$adminReq->errors = $taskValidation->errors;
					}
				} elseif($adminReq->action === 'update-cronjob') {
					$output = exec("crontab -l 2>&1", $cronFile);
					
					// If no id is present kill the request.
					if(empty($adminReq->id)) {
						exit;
					}
					
					$task['id'] = $adminReq->id;
					$task['name'] = isset($adminReq->name) ? $adminReq->name : '';
					$task['description'] = isset($adminReq->description) ? $adminReq->description : '';
					$task['command'] = isset($adminReq->command) ? $adminReq->command : '';
					$task['minutes'] = isset($adminReq->minutes) ? $adminReq->minutes : '';
					$task['hours'] = isset($adminReq->hours) ? $adminReq->hours :'';
					$task['daysOfMonth'] = isset($adminReq->daysOfMonth) ? $adminReq->daysOfMonth : '';
					$task['months'] = isset($adminReq->months) ? $adminReq->months : '';
					$task['daysOfWeek'] = isset($adminReq->daysOfWeek) ? $adminReq->daysOfWeek : '';
					$task['years'] = isset($adminReq->years) ? $adminReq->years : '';
					$task['log'] = isset($adminReq->log) ? $adminReq->log : 1;
					
					$taskValidation = new TaskValidation($task);
					
					if(empty($taskValidation->errors)) {
						$oldTask = $config->getTask($adminReq->id);
						
						//var_dump($oldCronjob); var_dump($cronFile[0]); var_dump($cronFile[1]); exit;
						
						// Search for the previous cronjob and remove it from the list.
						$key = array_search($oldTask['cronjob'], $cronFile);
						
						if($key !== FALSE) {
							unset($cronFile[$key]);
						}
						
						// If there are no crontabs remove the entry.
						if(!empty($cronFile[0]) && stristr($cronFile[0], 'no crontab')) {
							unset($cronFile[0]);
						}
						
						$cronjob = $adminReq->minutes . ' ' . $adminReq->hours . ' ' . $adminReq->daysOfMonth . ' ' . $adminReq->months . ' ' . $adminReq->daysOfWeek . ' ' . $adminReq->command;
						
						if((int)$taskValidation->task['log'] === 1) {
							$cronjob .= ' > ' . DIR_LOGS . 'tasks/' . $taskValidation->task['alias'] . '.log';
						}
						
						// Add the finalized cronjob to the validated task array.
						$taskValidation->task['cronjob'] = $cronjob;
						
						array_push($cronFile, $cronjob);
						
						//var_dump($cronjob);
						//var_dump($cronFile[0]);
						//print_r2($cronFile); exit;
						
						// Implode array to make the cron file.
						$cronFile = implode(PHP_EOL, $cronFile);
						$cronFile .= PHP_EOL;
						
						//print_r2($cronFile); exit;
						
						// Build the cron file.
						file_put_contents(DIR_SYSTEM . 'tmp/crontab.txt', $cronFile); 
						// Install the crontab.
						$output = shell_exec('crontab ' . DIR_SYSTEM . 'tmp/crontab.txt 2>&1');
						// Remove the temporary file.
						@unlink(DIR_SYSTEM . 'tmp/crontab.txt');
						
						// Check for installation errors
						if(stristr($output, "can't install")) {
							$adminReq->errors[] = $output;
						} else {
							$adminReq->success = $config->updateTask($taskValidation->task);
						}
					} else {
						$adminReq->errors = $taskValidation->errors;
					}
				} elseif($adminReq->action === 'delete-cronjob') {
					if(!empty($adminReq->ids) && is_array($adminReq->ids)) {
						
						foreach($adminReq->ids as $taskId) {
							
							$task = $config->getTask($taskId);
							
							//var_dump($task['cronjob']); var_dump($cronFile[0]); exit;
							$output = exec("crontab -l 2>&1", $cronFile);
							
							// Search for the previous cronjob and remove it from the list.
							$key = array_search($task['cronjob'], $cronFile);
							
							if($key !== FALSE) {
								unset($cronFile[$key]);
							}

							// Implode array to make the cron file.
							$cronFile = implode(PHP_EOL, $cronFile);
							$cronFile .= PHP_EOL;
							
							//print_r2($cronFile); exit;
							
							// Build the cron file.
							file_put_contents(DIR_SYSTEM . 'tmp/crontab.txt', $cronFile); 
							
							// Install the crontab.
							$output = shell_exec('crontab ' . DIR_SYSTEM . 'tmp/crontab.txt 2>&1');
							
							// Remove the temporary file.
							@unlink(DIR_SYSTEM . 'tmp/crontab.txt');
							
							// Delete the key from the database.
							$config->deleteTask($taskId);
						}
					}
				}
			}
		}
		echo json_encode($adminReq);
		exit;
	} elseif(isset($_POST['restoreBackup']) && !empty($_POST['restoreDb'])) {
		require_once DIR_ADMIN . 'model/common/backup.php';
		$backups = new BackupData;
		$success = $backups->recoverDatabase((int)$_POST['restoreDb']);
		if(!empty($success)) {
			$_SESSION['success'] = "The backup was successfully restored.";
		} else {
			$_SESSION['errors'] = "An error occured while restoring the database backup.";
		}
		clearCache();
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	} else {
		$_SESSION['errors'] = "An error occured while processing your request.";
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	}
}
