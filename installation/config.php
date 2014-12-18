<?php
namespace RevenantBlue;

// Version
define("VERSION", '0.3.12');
// Set the development environment.
define('DEVELOPMENT_ENVIRONMENT', TRUE);
// Set the server OS
define('SERVER_OS', 'LINUX');
// Name of the location of the backend
define('BACKEND_NAME', '');
// Database prefix.
define('PREFIX', '');
// Database name
define('DB_NAME', '');
// Set system constants
define('DB_CONN', '');
define('DB_CONN_LOCAL', '');
define('DB_HOST', '');
define('DB_HOST_LOCAL', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_USER_ADMIN', '');
define('DB_PASS_ADMIN', '');
define('CSRF_KEY', '');
define('USERS_KEY', '');
define('HASH_ALGORITHM', '');
define('HASH_ITERATIONS', '');
define('HASH_LENGTH', '');
define('ACCESS_CODE', '');
// Set Redis constants
define('REDIS', FALSE);
define('REDIS_HOST', '');
define('REDIS_PORT', '');
define('REDIS_SESSIONS', FALSE);
define('REDIS_PHP_NODE_SESSION_SHARING', FALSE);
















if(DEVELOPMENT_ENVIRONMENT === TRUE) {
	if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === 443 || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
		define('HTTP_SERVER', 'https://local-domain/');
		define('HTTP_SERVER_DIR', 'https://local-domain/site/');
		define('NODE_SERVER', 'https://local-domain:4000/');
		define('NODE', 'https://local-domain:4000');
		define('HTTP_IMAGE', 'https://local-domain/images/');
		define('HTTP_ADMIN', 'https://local-domain/rbadmin/');
		define('HTTP_ADMIN_DIR', 'https://local-domain/administration/');
		define('HTTPS_ADMIN_DIR', 'https://local-domain/administration/');
		define('HTTP_GALLERY', 'https://local-domain/images/photogallery/');
		define('HTTP_MEDIA', 'https://local-domain/media/');
		define('HTTP_FORUM', 'https://local-domain/forums/');
		define('HTTP_AVATARS', 'https://local-domain/images/avatars/');
		define('HTTP_CPANEL', 'https://local-domain/cpanel/');
		define('HTTP_LOGIN', 'https://local-domain/login/');
	} else {
		// HTTP
		define('HTTP_SERVER', 'http://local-domain/');
		define('HTTP_SERVER_DIR', 'http://local-domain/site/');
		define('NODE_SERVER', 'http://local-domain:4000/');
		define('NODE', 'http://local-domain:4000');
		define('HTTP_IMAGE', 'http://local-domain/images/');
		define('HTTP_ADMIN', 'http://local-domain/rbadmin/');
		define('HTTP_ADMIN_DIR', 'http://local-domain/administration/');
		define('HTTPS_ADMIN_DIR', 'https://local-domain/administration/');
		define('HTTP_GALLERY', 'http://local-domain/images/photogallery/');
		define('HTTP_MEDIA', 'http://local-domain/media/');
		define('HTTP_FORUM', 'http://local-domain/forums/');
		define('HTTP_AVATARS', 'http://local-domain/images/avatars/');
		define('HTTP_CPANEL', 'http://local-domain/cpanel/');
		define('HTTP_LOGIN', 'http://local-domain/login/');
	}
	
	// Define the host name.
	define('HOST_NAME', 'local-domain');
	
	if(SERVER_OS === 'WINDOWS') {
		// DIR
		define('DIR_ROOT', 'windows-path/');
		define('DIR_SITE_ROOT', 'windows-path/');
		define('DIR_MYSQL', '/usr/bin/');
		define('DIR_APPLICATION', 'windows-path/site/');
		define('DIR_ADMIN', 'windows-path/administration/');
		define('DIR_SYSTEM', 'windows-path/system/');
		define('DIR_DATABASE', 'windows-path/system/database/');
		define('DIR_LANGUAGE', 'windows-path/site/language/');
		define('DIR_TEMPLATE', 'windows-path/site/view/template/');
		define('DIR_ADMIN_TEMPLATE', 'windows-path/administration/view/template/');
		define('DIR_CONFIG', 'windows-path/system/config/');
		define('DIR_IMAGE', 'windows-path/images/');
		define('DIR_AVATARS', 'windows-path/images/avatars/');
		define('DIR_MEDIA', 'windows-path/media/');
		define('DIR_GALLERY', 'windows-path/images/photogallery/');
		define('DIR_CACHE', 'windows-path/system/cache/');
		define('DIR_DOWNLOAD', 'windows-path/download/');
		define('DIR_BACKUP', 'backup-path/');
		define('DIR_LOGS', 'windows-path/system/logs/');
	} elseif(SERVER_OS === 'LINUX') {
		// DIR
		define('DIR_ROOT', 'linux-path/');
		define('DIR_SITE_ROOT', 'linux-path/');
		define('DIR_MYSQL', '/usr/bin/');
		define('DIR_APPLICATION', 'linux-path/site/');
		define('DIR_ADMIN', 'linux-path/administration/');
		define('DIR_SYSTEM', 'linux-path/system/');
		define('DIR_DATABASE', 'linux-path/system/database/');
		define('DIR_LANGUAGE', 'linux-path/site/language/');
		define('DIR_TEMPLATE', 'linux-path/site/view/template/');
		define('DIR_ADMIN_TEMPLATE', 'linux-path/administration/view/template/');
		define('DIR_CONFIG', 'linux-path/system/config/');
		define('DIR_IMAGE', 'linux-path/images/');
		define('DIR_AVATARS', 'linux-path/images/avatars/');
		define('DIR_MEDIA', 'linux-path/media/');
		define('DIR_GALLERY', 'linux-path/images/photogallery/');
		define('DIR_CACHE', 'linux-path/system/cache/');
		define('DIR_DOWNLOAD', 'linux-path/download/');
		define('DIR_BACKUP', 'backup-path/');
		define('DIR_LOGS', 'linux-path/system/logs/');
	}
} elseif(DEVELOPMENT_ENVIRONMENT === FALSE) {
	if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === 443 || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
		// HTTPS
		define('HTTP_SERVER', 'https://web-domain/');
		define('HTTP_SERVER_DIR', 'https://web-domain/site/');
		define('NODE_SERVER', 'https://web-domain:4000/');
		define('NODE', 'https://web-domain:4000');
		define('HTTP_IMAGE', 'https://web-domain/images/');
		define('HTTP_ADMIN', 'https://web-domain/rbadmin/');
		define('HTTP_ADMIN_DIR', 'https://web-domain/administration/');
		define('HTTP_GALLERY', 'https://web-domain/images/photogallery/');
		define('HTTP_MEDIA', 'https://web-domain/media/');
		define('HTTP_FORUM', 'https://web-domain/forums/');
		define('HTTP_AVATARS', 'https://web-domain/images/avatars/');
		define('HTTP_CPANEL', 'https://web-domain/cpanel/');
		define('HTTP_LOGIN', 'https://web-domain/login/');
	} else {
		// HTTP
		define('HTTP_SERVER', 'http://web-domain/');
		define('HTTP_SERVER_DIR', 'http://web-domain/site/');
		define('NODE_SERVER', 'http://web-domain:4000/');
		define('NODE', 'http://web-domain:4000');
		define('HTTP_IMAGE', 'http://web-domain/images/');
		define('HTTP_ADMIN', 'http://web-domain/rbadmin/');
		define('HTTP_ADMIN_DIR', 'http://web-domain/administration/');
		define('HTTP_GALLERY', 'http://web-domain/images/photogallery/');
		define('HTTP_MEDIA', 'http://web-domain/media/');
		define('HTTP_FORUM', 'http://web-domain/forums/');
		define('HTTP_AVATARS', 'http://web-domain/images/avatars/');
		define('HTTP_CPANEL', 'http://web-domain/cpanel/');
		define('HTTP_LOGIN', 'http://web-domain/login/');
	}
	
	// HTTPS constant for testing SSL certificate existence.
	define('HOST_NAME', 'web-domain');
	
	// DIR
	define('DIR_ROOT', 'cwd/');
	define('DIR_SITE_ROOT', 'cwd/');
	define('DIR_MYSQL', '/usr/bin/');
	define('DIR_APPLICATION', 'cwd/site/');
	define('DIR_ADMIN', 'cwd/administration/');
	define('DIR_SYSTEM', 'cwd/system/');
	define('DIR_DATABASE', 'cwd/system/database/');
	define('DIR_LANGUAGE', 'cwd/site/language/');
	define('DIR_TEMPLATE', 'cwd/site/view/template/');
	define('DIR_ADMIN_TEMPLATE', 'cwd/administration/view/template/');
	define('DIR_CONFIG', 'cwd/system/config/');
	define('DIR_IMAGE', 'cwd/images/');
	define('DIR_AVATARS', 'cwd/images/avatars/');
	define('DIR_MEDIA', 'cwd/media/');
	define('DIR_GALLERY', 'cwd/images/photogallery/');
	define('DIR_CACHE', 'cwd/system/cache/');
	define('DIR_DOWNLOAD', 'cwd/download/');
	define('DIR_BACKUP', 'backup-path/');
	define('DIR_LOGS', 'cwd/system/logs/');
}
