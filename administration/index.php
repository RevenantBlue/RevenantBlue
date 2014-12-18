<?php
namespace RevenantBlue\Site;
use \stdClass;

// Config
require_once __DIR__ . '/../config.php';
// Database
require_once DIR_SYSTEM . 'database/db.php';
require_once DIR_SYSTEM . 'engine/dependencies.php';
// Redis Datastore
require_once DIR_SYSTEM . 'database/redis.php';
// Configuration settings
require_once DIR_ADMIN . 'model/config/config-main.php';
// Startup
require_once DIR_ADMIN . 'startup.php';
// Session
require_once DIR_SYSTEM . 'engine/session.php';
// ACL
require_once DIR_SYSTEM . 'engine/acl.php';
// Pagination
require_once DIR_SYSTEM . 'library/paginate.php';

// Deny access to banned ip addresses
if(REDIS === TRUE) {
	$rdh = $redis->loadRedisHandler();
	$isBanned = $rdh->sismember(PREFIX . 'bannedIps', $_SERVER['REMOTE_ADDR']);
	if($isBanned) {
		header('HTTP/1.1 403 Forbidden');
		echo "Your IP address has been banned on this site.";
		exit;
	}
} else {
	if($config->getBannedIp($_SERVER['REMOTE_ADDR'])) {
		header('HTTP/1.1 403 Forbidden');
		echo "Your IP address has been banned on this site.";
		exit;
	}
}

// Front Controller
if(isset($_GET['controller'])) {
	$pageToDisplay = $_GET['controller'];
} else {
	$pageToDisplay = 'index';
}

if(strstr($pageToDisplay, 'admin')) {
	// Admin pages
	$pageToDisplay = str_replace('admin-', '', $pageToDisplay);
	
	if(is_file(DIR_ADMIN_TEMPLATE . $pageToDisplay . '.php')) {
		$_GET['controller'] = $pageToDisplay;
		include_once DIR_ADMIN_TEMPLATE . $pageToDisplay . '.php';
	} else {
		include_once DIR_SITE_ROOT . '404.php';
	}
} elseif($pageToDisplay === 'media-upload') {
	include_once DIR_ADMIN . 'controller/media/media-upload.php';
} 
