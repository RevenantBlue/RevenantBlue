<?php
namespace RevenantBlue\Site;
use RevenantBlue;
use DateTimeZone;
use DirectoryIterator;
use HTMLPurifier;
use HTMLPurifier_Config;
use \stdClass;

// Instantiate the configuration model
$config = new Config;
//$dependencies = new DependencyContainer(array('adminUser' => FALSE));
//$config = new Config($dependencies);

// Load Redis
if(REDIS === TRUE) {
	$redis = new RevenantBlue\RedisCommand;
}

// Load global settings, try the redis cache first else load it from the database.
if(REDIS === TRUE) {
	$globalSettings = $redis->get(PREFIX . 'globalSettings', TRUE);
	if(empty($globalSettings)) {
		$redis->set(PREFIX . 'globalSettings', json_encode($config->loadGlobalSettings()));
		$globalSettings = $redis->get(PREFIX . 'globalSettings', TRUE);
	}
} else {
	$globalSettings = $config->loadGlobalSettings();
}

/** Set Timezone **/
if(!empty($globalSettings['timezone']['value'])) {
	date_default_timezone_set($globalSettings['timezone']['value']);
} else {
	date_default_timezone_set('America/New_York');
}

// Development environment switch
if(DEVELOPMENT_ENVIRONMENT === TRUE) {
	error_reporting(E_ALL);
	ini_set('display_errors','On');
	set_error_handler('RevenantBlue\Site\errorLogger');
} elseif(DEVELOPMENT_ENVIRONMENT === FALSE) {
	error_reporting(E_ALL & ~E_DEPRECATED);
	ini_set('display_errors','Off');
	set_error_handler('RevenantBlue\Site\errorLogger');
} else {
	error_reporting(E_ALL);
	ini_set('display_errors','Off');
	set_error_handler('RevenantBlue\Site\errorLogger');
}

$timezones = array(
	'-12'   => 'GMT - 12:00 hours) Enitwetok, Kwajalien',
	'-11'   => 'GMT - 11:00 hours) Midway Island, Samoa',
	'-10'   => 'GMT - 10:00 hours) Hawaii',
	'-9.5'  => 'GMT - 9:30 hours) French Polynesia',
	'-9'    => 'GMT - 9:00 hours) Alaska',
	'-8'    => 'GMT - 8:00 hours) Pacific Time (US & Canada)',
	'-7'    => 'GMT - 7:00 hours) Mountain Time (US & Canada)',
	'-6'    => 'GMT - 6:00 hours) Central Time (US & Canada), Mexico City',
	'-5'    => 'GMT - 5:00 hours) Eastern Time (US & Canada), Bogota, Lima',
	'-4.5'  => 'GMT - 4:30 hours) Bolivarian Time',
	'-4'    => 'GMT - 4:00 hours) Atlantic Time (Canada), Caracas, La Paz',
	'-3.5'  => 'GMT - 3:30 hours) Newfoundland',
	'-3'    => 'GMT - 3:00 hours) Brazil, Buenos Aires, Falkland Is.',
	'-2'    => 'GMT - 2:00 hours) Mid-Atlantic, Ascention Is., St Helena',
	'-1'    => 'GMT - 1:00 hours) Azores, Cape Verde Islands',
	'0'     => 'GMT   Casablanca, Dublin, London, Lisbon, Monrovia',
	'1'     => 'GMT + 1:00 hours) Brussels, Copenhagen, Madrid, Paris, Rome',
	'2'     => 'GMT + 2:00 hours) Kaliningrad, South Africa',
	'3'     => 'GMT + 3:00 hours) Baghdad, Riyadh, Moscow, Nairobi',
	'3.5'   => 'GMT + 3:30 hours) Tehran',
	'4'     => 'GMT + 4:00 hours) Abu Dhabi, Baku, Muscat, Tbilisi',
	'4.5'   => 'GMT + 4:30 hours) Kabul',
	'5'     => 'GMT + 5:00 hours) Ekaterinburg, Karachi, Tashkent',
	'5.5'   => 'GMT + 5:30 hours) Bombay, Calcutta, Madras, New Delhi',
	'5.75'  => 'GMT + 5:45 hours) Kathmandu',
	'6 '    => 'GMT + 6:00 hours) Almaty, Bangladesh, Dhakra',
	'6.5'   => 'GMT + 6:30 hours) Yangon, Naypyidaw, Bantam',
	'7 '    => 'GMT + 7:00 hours) Bangkok, Hanoi, Jakarta',
	'8'     => 'GMT + 8:00 hours) Hong Kong, Perth, Singapore, Taipei',
	'8.75'  => 'GMT + 8:45 hours) Caiguna, Eucla',
	'9'     => 'GMT + 9:00 hours) Osaka, Sapporo, Seoul, Tokyo, Yakutsk',
	'9.5'   => 'GMT + 9:30 hours) Adelaide, Darwin',
	'10'    => 'GMT + 10:00 hours) Melbourne, Papua New Guinea, Sydney',
	'10.5'  => 'GMT + 10:30 hours) Lord Howe Island',
	'11'    => 'GMT + 11:00 hours) Magadan, New Caledonia, Solomon Is.',
	'11.5'  => 'GMT + 11:30 hours) Burnt Pine, Kingston',
	'12'    => 'GMT + 12:00 hours) Auckland, Fiji, Marshall Islands',
	'12.75' => 'GMT + 12:45 hours) Chatham Islands',
	'13'    => 'GMT + 13:00 hours) Kamchatka, Anadyr',
	'14'    => 'GMT + 14:00 hours) Kiritimati'
);

// Set the user's timezone if set.
if(!empty($_SESSION['timezone'])) {
	date_default_timezone_set($timezones[$_SESSION['timezone']]);
}

// Create a token to protect against CSRF attacks
function getCsrfToken() {
	if(!isset($_SESSION['userId'])) {
		if(!isset($_SESSION['tempId'])) {
			$_SESSION['tempId'] = mt_rand(100000, 9999999);
		}
		$token = dechex($_SESSION['tempId']) . "." . (time() + 100000) . "." . dechex(mt_rand());
	} elseif(isset($_SESSION['userId'])) {
		$token = dechex($_SESSION['userId']) . "." . (time() + 100000) . "." . dechex(mt_rand());
	}
	$hash = sha1(CSRF_KEY . '-' . $token);
	$signed = $token . '-' . $hash;
	return $signed;
}

// Check the legitimacy of the csrf protection token.
function checkCsrfToken() {
	if(isset($_POST['csrfToken']) || isset($_GET['csrfToken']) || isset($_POST['adminRequest'])) {
		$legitToken = FALSE;
		if(isset($_POST['csrfToken'])) {
			$token = $_POST['csrfToken'];
		} elseif(isset($_GET['csrfToken'])) {
			$token = $_GET['csrfToken'];
		} elseif(isset($_POST['adminRequest'])) {
			$adminRequest = json_decode($_POST['adminRequest']);
			if(!empty($adminRequest->csrfToken)) {
				$token = $adminRequest->csrfToken;
			} else {
				return FALSE;
			}
		} elseif(isset($_GET['appRequest'])) {
			$appRequest = json_decode($_GET['appRequest']);
			if(!empty($appRequest->csrfToken)) {
				$token = $appRequest->csrfToken;
			} else {
				return FALSE;
			}
		}
		// Separate the token and the hash.
		$tokenParts = explode('-', $token);
		if(count($tokenParts) == 2) {
			// Assign the array values to variables.
			list($token, $hash) = $tokenParts;
			// Separate the date/time created from the token
			$tokenParts = explode('.', $token);
			list($userId, $expire) = $tokenParts;
			if(isset($_SESSION['userId'])) {
				if($hash == sha1(CSRF_KEY . '-' . $token) && hexdec($userId) == $_SESSION['userId'] && $expire >= (time() - 100000)) {
					$legitToken = TRUE;
				}
			} elseif(isset($_SESSION['tempId'])) {
				if($hash == sha1(CSRF_KEY . "-" . $token) && hexdec($userId) == $_SESSION['tempId'] && $expire >= (time() - 100000)) {
					$legitToken = TRUE;
				}
			}
		}
		return $legitToken;
	} else {
		return FALSE;
	}
}

// Initialize error handler.
function errorLogger($type, $msg, $file, $line) {
	// Define the log file
	$errorLog = "error.log";
	// Construct the error string
	$errorString = "Date: " . date("d-m-Y H:i:s", time()) . "\n";
	$errorString .= "Error type: $type\n";
	$errorString .= "Error message: $msg\n";
	$errorString .= "Script: $file($line)\n";

	// Check to make sure server headers were sent because
	// sometimes the error logger will be run from the shell and no server information will be available at the time.
	if(isset($_SERVER['HTTP_HOST'])) {
		$errorString .= "Host: " . $_SERVER['HTTP_HOST'] . "\n";
		$errorString .= "Client: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
		$errorString .= "Client IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
		$errorString .= "Request URI: " . $_SERVER['REQUEST_URI'] . "\n\n\n\n";
	}

	// Log the error string to the specified log file
	error_log($errorString, 3, DIR_LOGS . $errorLog);
	// Discards current buffer contents if output buffering is active and stop buffering.
	if(ob_start() == 1) ob_end_clean();
	// Display error page
	if(DEVELOPMENT_ENVIRONMENT == TRUE) {
		echo "We're sorry, but this page could not be displayed because of an internal error. <br /><br />The error has been recorded and will be rectified as soon as possible. Our apologies for the inconvenience.<br /><br />";
		echo "<b>Error: </b>" . $msg . " on <b>Line</b> " . $line . " in " . $file . " <b>type</b> " . $type;
		// Exit to ensure no further code is executed.
		exit;
	}
}

function outLog($data) {
	if(is_array($data) || is_object($data)) {
		foreach($data as $key => $value) {
			file_put_contents(DIR_LOGS . 'output.log', $key . ' => ' . $value . "\n", FILE_APPEND);
		}
		file_put_contents(DIR_LOGS . 'output.log', "\n\n\n", FILE_APPEND);
	} else {
		file_put_contents(DIR_LOGS . 'output.log', $data . "\n\n\n", FILE_APPEND);
	}
}

function getFileName() {
	$currentFile = basename($_SERVER["PHP_SELF"]);
	$parts = Explode('.', $currentFile);
	return $parts[0];
}
// Provides a clean pre formatted textual output of the contents of an array.
function print_r2($val){
	echo '<div style="margin-left: 250px; margin-top: 60px;">';
	echo '<pre>';
	print_r($val);
	echo  '</pre>';
	echo '</div>';
}
// Recursive version of in_array for use with multidimensional arrays.
function in_array_r($needle, $array) {
	$found = false;
	foreach($array as $item) {
		if($item === $needle) {
			$found = true;
			break;
		} elseif(is_array($item)) {
			$found = in_array_r($needle, $item);
		}
	}
	return $found;
}
// A function that will determine whether a value exists within a multidimensional array.
function in_multiarray($needle, $haystack) {
	if( is_array($haystack) || is_object($haystack)) {
		 // if $needle is in $haystack object
		 if(is_object($haystack)) {
			 $temp_array = get_object_vars($haystack);
			 if(in_array($needle, $temp_array)) {
				 return TRUE;
			 }
		 }
		 // if $needle is in $haystack return true
		 if( is_array($haystack) && in_array($needle, $haystack)) {
			 return TRUE;
		 }
		 // if $needle isn't in $haystack, then check foreach element
		 foreach($haystack as $array_element) {
			 // if $haystack_element is an array or is an object call the in_multiarray function to this element
			 // if in_multiarray returns TRUE, than return is in array, else check next element
			 if((is_array($array_element) || is_object($array_element)) && in_multiarray($needle, $array_element)) {
				 return TRUE;
				 exit;
			 }
		 }
	 }
	// if isn't in array return FALSE
	return FALSE;
}
// Recursive array_unique
function array_unique_deep($array) {
	$values = array();
	//ideally there would be some is_array() testing for $array here...
	foreach ($array as $part) {
		if(is_array($part)) {
			$values = array_merge($values, array_unique_deep($part));
		} else {
			$values[] = $part;
		}
	}
	return array_unique($values);
}
// Removes empty values from an array.
function removeEmptyValues($array) {
	foreach($array as $key => $value) {
		trim($value);
		if($array[$key] == '') {
			unset($array[$key]);
		}
	}
	return $array;
}
// Applies htmlspecialchars to a a variable.
function hsc($var, $quote_style = ENT_QUOTES, $charset = 'UTF-8') {
	$var = htmlspecialchars($var, $quote_style, $charset);
	return $var;
}

function hsc_decode($var, $quote_style = ENT_QUOTES) {
	$var = htmlspecialchars_decode($var, $quote_style);
	return $var;
}
// Recursively applies htmlspecialcharacters to an array.
function hsc_deep($mixed, $quote_style = ENT_QUOTES, $charset = 'UTF-8') {
	if (is_array($mixed)) {
		foreach($mixed as $key => $value) {
			$mixed[$key] = hsc_deep($value, $quote_style, $charset);
		}
	} elseif (is_string($mixed)) {
		$mixed = htmlspecialchars(htmlspecialchars_decode($mixed, $quote_style), $quote_style, $charset);
	}
	return $mixed;
}
// Strip tags and attributes
// Example: stripTagsAttribs($string,'<strong><em><a>','href,rel');
function stripTagsAttribs($string, $allowtags = NULL, $allowattributes = NULL){
	$string = strip_tags($string,$allowtags);
	if (!is_null($allowattributes)) {
		if(!is_array($allowattributes))
			$allowattributes = explode(",",$allowattributes);
		if(is_array($allowattributes))
			$allowattributes = implode(")(?<!",$allowattributes);
		if (strlen($allowattributes) > 0)
			$allowattributes = "(?<!".$allowattributes.")";
		$string = preg_replace_callback("/<[^>]*>/i",create_function(
			'$matches',
			'return preg_replace("/ [^ =]*'.$allowattributes.'=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);'
		),$string);
	}
	return $string;
}
// Organizes and indents HTML.
function tidyHTML($content, $trusted = FALSE) {
	require_once DIR_SYSTEM . '/library/htmlpurifier-4.6.0/library/HTMLPurifier.auto.php';
	$config = HTMLPurifier_Config::createDefault();
	$config->set('Output.TidyFormat', true);

	// Set trusted configuration settings.
	if($trusted) {
		$config->set('Attr.EnableID', true);
	}

	$purifier = new HTMLPurifier($config);
	$content = $purifier->purify($content);
	return $content;
}
// Organize a PDO associative array into a key => value array based on certain columns.
function organizePDO($PDOAssoc, $keyColumn, $valueColumn) {
	foreach($PDOAssoc as $array) {
		$result[$array[$keyColumn]] = $array[$valueColumn];
	}
	return $result;
}
// pbkdf2 hashing algorithim for password encryption

function pbkdf2($password, $salt, $iterationCount, $keyLength, $algorithm = 'sha512', $rawOutput = TRUE) {
	
	if(defined('HASH_ALGORITHM') && !empty(HASH_ALGORITHM)) {
		$algorithm = HASH_ALGORITHM;
	} else {
		$algorithm = strtolower($algorithm);
	}
	
	if(!in_array($algorithm, hash_algos(), true)) {
		$_SESSION['errors'][] = 'PBKDF2 ERROR: Invalid hash algorithm.';
	}
	if($iterationCount <= 0 || $keyLength <= 0) {
		$_SESSION['errors'][] = 'PBKDF2 ERROR: Invalid parameters.';
	}
	if(function_exists("hash_pbkdf2")) {
		if (!$rawOutput) {
			$keyLength = $keyLength * 2;
		}
		$finalHash = hash_pbkdf2($algorithm, $password, $salt, $iterationCount, $keyLength, $rawOutput);
	} else {
		$hashLength = strlen(hash($algorithm, null, true));
		$keyBlocks = ceil($keyLength / $hashLength);
		$derivedKey = '';

		for($block = 1; $block <= $keyBlocks; $block++) {
			$iteratedBlock = $binaryOutput = hash_hmac($algorithm, $salt . pack('N', $block), $password, true);
			for($i = 1; $i < $iterationCount; $i++) {
				$iteratedBlock ^= ($binaryOutput = hash_hmac($algorithm, $binaryOutput, $password, true));
			}
			$finalHash .= $iteratedBlock;
		}
	}
	// Return derived key of correct length
	return substr($finalHash, 0, $keyLength);
}

// token generator for creating random token strings.
function tokenGenerator($length, $sha1 = false) {
	$chars = array( 'Q', '@', '8', 'y', '%', '^', '5', 'Z', '#', 'G', '_', 'O', '`',
					'S', '-', 'N', '<', 'D', '{', '}', '[', ']', 'h', ';', 'W', '.',
					'/', '|', ':', '1', 'E', 'L', '4', '&', '6', '7', '#', '9', 'a',
	   				'A', 'b', 'B', '~', 'C', 'd', '>', 'e', '2', 'f', 'P', 'g', '!',
					'?', 'H', 'i', 'X', 'U', 'J', 'k', 'r', 'l', '3', 't', 'M', 'n',
					'=', 'o', '+', 'p', 'F', 'q', '!', 'K', 'R', 's', 'c', 'm', 'T',
					'v', 'j', 'u', 'V', 'w', ',', 'x', 'I', '$', 'Y', 'z', '*'
				  );
	// Array indice friendly variable of the number of chars.
	$numOfChars = count($chars) - 1;
	$token = '';
	for($x=0; $x < $length; $x++) {
		$token .= $chars[mt_rand(0, $numOfChars)];
	}

	if($sha1) {
		//Number of 32 char chunks
		$chunks = ceil(strlen($token) / 40);
		if(!isset($sha1Token)) $shaToken = '';

		for($i = 1; $i <= $chunks; $i++) {
			$sha1Token = sha1(substr($token, $i * 40 - 40, 40));
		}
		$token = substr($sha1Token, 0, $length);
	}

	outLog($token);
	return $token;
}
// All inclusive session clearing
function clearSession() {
	session_unset();
	session_destroy();
	$_SESSION = array();
}
// Recursively remove directory and all of its contents
function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if(filetype($dir . "/" . $object) == "dir") {
					rrmdir($dir."/".$object);
				} else {
					chmod($dir . "/" . $object, 0777);
					unlink($dir . "/" . $object);
				}
			}
		}
		reset($objects);
		rmdir($dir);
	}
}
// Open an image for gd to mainpulate
function openImage($file) {
	 switch(exif_imagetype($file)) {
		 case IMAGETYPE_JPEG:
			 $img = @imagecreatefromjpeg($file);
			 break;
		 case IMAGETYPE_GIF:
			 $img = @imagecreatefromgif($file);
			 break;
		 case IMAGETYPE_PNG:
			 $img = @imagecreatefrompng($file);
			 break;
		 default:
			 $img = false;
			 break;
	 }
	return $img;
}
// Save an open gd image
function saveImage($imagePath, $imageResource, $savePath, $imageQuality = 100) {
	// Save the resized file to the savePath depending on the image type of the original file.
	switch(exif_imagetype($imagePath)) {
		case IMAGETYPE_JPEG:
			 imagejpeg($imageResource, $savePath, $imageQuality);
			 break;
		case IMAGETYPE_GIF:
			imagegif($imageResource, $savePath);
			break;
		case IMAGETYPE_PNG:
			// Scale quality from 0-100 to 0-9
			$scaleQuality = round(($imageQuality / 100) * 9);
			// Invert quality setting as 0 is best, not 9
			$invertScaleQuality = 9 - $scaleQuality;
			imagepng($imageResource, $savePath, $invertScaleQuality);
			break;
		default:
			break;
	}
}
// Used for verifying that a user has certain permissions.
function aclVerify($moduleName, $userId = FALSE) {
	$acl = new RevenantBlue\ACL;
	
	$users = new Users;
	
	if(empty($userId) && !empty($_SESSION['userId'])) {
		$userId = $_SESSION['userId'];
	}
	// Check for a legitimate userId.
	if(isset($userId)) {
		$checkUser = $users->getUsernameById($userId);
	}

	if(isset($checkUser)) {
		$roles = $acl->loadRolesForUser($userId);
		// If there are no roles assigned to this user return false.
		if(empty($roles)) {
			return FALSE;
		}

		foreach($roles as $role) {
			$verify[] = $acl->verifyPermission($moduleName, $role['role_id']);
		}

		if(in_array(1, $verify)) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		// Handle anonymous users.
		$verify = $acl->verifyPermission($moduleName, 5);

		if(!empty($verify)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

// Converts a unix timestamp into a reading friendly format.
function nicetime($date, $error = 'No date provided') {
	// date must be in format - 'YYYY-MM-DD HH:MM' in order to use this function.
	if(empty($date)) {
		return $error;
	}
	$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
	$lengths = array("60","60","24","7","4.35","12","10");
	$now = time();
	$unixDate = strtotime($date);
	// check validity of date
	if(empty($unixDate)) return "Bad date";
	// is it future date or past date
	if($now > $unixDate) {
		$difference = $now - $unixDate;
		$tense = "ago";
	} elseif($now - $unixDate === 0) {
		return "right now";
	} else {
		$difference = $unixDate - $now;
		$tense = "from now";
	}
	for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
		$difference /= $lengths[$j];
	}
	$difference = round($difference);
	if($difference != 1) $periods[$j] .= "s";
	return "$difference $periods[$j] {$tense}";
}
// Ensures that a timestamp is a timestamp that PHP understands.
function isValidTimeStamp($timestamp) {
	if((int) $timestamp === $timestamp && $timestamp <= PHP_INT_MAX & $timestamp >= -PHP_INT_MAX) {
		return TRUE;
	}
}
// Get the contents of a directory recursively.
function getDir($dir) {
	// open handler for the directory
	$iter = new DirectoryIterator($dir);

	foreach( $iter as $item ) {
		//echo $item->getFileInfo() . "<br />";
		// make sure you don't try to access the current dir or the parent
		if ($item != '.' && $item != '..') {
			if( $item->isDir() ) {
				// call the function on the folder
				getDir("$dir/$item");
			} else {
				// print files
				echo $item->getFileName() . "<br />";
			}
		}
	}
}
// Get the current timezones through the DateTimeZone object.
function getTimezones() {
	$regions = array(
		'Africa' => DateTimeZone::AFRICA,
		'America' => DateTimeZone::AMERICA,
		'Antarctica' => DateTimeZone::ANTARCTICA,
		'Asia' => DateTimeZone::ASIA,
		'Atlantic' => DateTimeZone::ATLANTIC,
		'Europe' => DateTimeZone::EUROPE,
		'Indian' => DateTimeZone::INDIAN,
		'Pacific' => DateTimeZone::PACIFIC
	);
	// Get each time zone in a multidimensional with the parent key containing the group name.
	foreach ($regions as $name => $mask) {
		$tzlist[$name] = preg_replace('/([^\/]*)\//', '', DateTimeZone::listIdentifiers($mask));
	}
	return $tzlist;
}
// Force HTTPS even if the server is not doing so already.
function forceHTTPS() {
	if(isset($_SERVER['HTTP_HOST'])) {
		$httpsURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		// Initialize https if it isn't already active.
		if(!isset( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] !== 'on' ) {
			ini_set('session.cookie_secure',1);
			if(!headers_sent()) {
				header( "Status: 301 Moved Permanently" );
				header( "Location: $httpsURL" );
				exit();
			} else {
			  die( '<script type="javascript">document.location.href="'.$httpsURL.'";</script>' );
			}
		}
	}
}
// Get the current URL for the page.
function selfURL() {
	$selfURL = array();
	// Check for HTTPS
	if($_SERVER['SERVER_PORT'] === 443 || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
		$protocol = 'https://';
	} else {
		$protocol = 'http://';
	}
	$host = str_replace('www.', '', $_SERVER['HTTP_HOST']);

	$selfURL[0] = $protocol . $host . $_SERVER['REQUEST_URI'];
	$selfURL[1] = $protocol . $host . $_SERVER['REQUEST_URI'] . '/';
	$selfURL[2] = rtrim($protocol . $host . $_SERVER['REQUEST_URI'], '/');
	return $selfURL;
}
// Get the execution time of a page. Must be placed at the end of template.
function executionTime($time) {
	$tmp = floor($time);
	$minutes = $tmp / 60;
	$seconds = ($tmp % 60) + ($time - $tmp);

	$output = 'Script took ';
	if ($minutes > 0)   $output .= $minutes . ' minutes and ';
	$output .= $seconds . ' seconds to complete.';
	echo $output;
}
// Encrypt a string.
function encrypt($decrypted, $password, $salt='!kQm*fF3pXe1Kbm%9') {
	// Build a 256-bit $key which is a SHA256 hash of $salt and $password.
	$key = hash('SHA256', $salt . $password, true);
	// Build $iv and $iv_base64.  We use a block size of 128 bits (AES compliant) and CBC mode.  (Note: ECB mode is inadequate as IV is not used.)
	srand(); $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
	if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) return false;
	// Encrypt $decrypted and an MD5 of $decrypted using $key.  MD5 is fine to use here because it's just to verify successful decryption.
	$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $decrypted . md5($decrypted), MCRYPT_MODE_CBC, $iv));
	// We're done!
	return $iv_base64 . $encrypted;
}
// Decrypt a string.
function decrypt($encrypted, $password, $salt='!kQm*fF3pXe1Kbm%9') {
	// Build a 256-bit $key which is a SHA256 hash of $salt and $password.
	$key = hash('SHA256', $salt . $password, true);
	// Retrieve $iv which is the first 22 characters plus ==, base64_decoded.
	$iv = base64_decode(substr($encrypted, 0, 22) . '==');
	// Remove $iv from $encrypted.
	$encrypted = substr($encrypted, 22);
	// Decrypt the data.  rtrim won't corrupt the data because the last 32 characters are the md5 hash; thus any \0 character has to be padding.
	$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($encrypted), MCRYPT_MODE_CBC, $iv), "\0\4");
	// Retrieve $hash which is the last 32 characters of $decrypted.
	$hash = substr($decrypted, -32);
	// Remove the last 32 characters from $decrypted.
	$decrypted = substr($decrypted, 0, -32);
	// Integrity check.  If this fails, either the data is corrupted, or the password/salt was incorrect.
	if (md5($decrypted) != $hash) return false;
	// Yay!
	return $decrypted;
}
function clearCache() {
	global $redis;
	$redis->del(PREFIX . 'globalSettings');
}
function pluploadImage($targetDir, $width = NULL, $height = NULL, $imageURL, $prefix = 'image') {
	global $globalSettings;
	global $users;
	global $config;

	// HTTP headers for no cache etc
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	// If the directory does not exist, create it.
	if(!is_dir($targetDir)) {
		@mkdir(DIR_IMAGE . 'articles/temp', 0755, TRUE);
	}

	$cleanupTargetDir = TRUE; // Remove old files
	$maxFileAge = 5 * 3600; // Temp file age in seconds

	// 5 minutes execution time
	@set_time_limit(5 * 60);

	// Uncomment this one to fake upload time
	// usleep(5000);

	// Get parameters
	$chunks = 0; $chunk = 0; $fileName = 0;
	if(isset($_POST['chunk'])) $chunk = intval($_POST['chunk']);
	if(isset($_POST['chunks'])) $chunks = intval($_POST['chunks']);
	if(isset($_POST['name'])) $fileName = $_POST['name'];
	if(isset($_GET['chunk'])) $chunk = intval($_GET['chunk']);
	if(isset($_GET['chunks'])) $chunks = intval($_GET['chunks']);
	if(isset($_GET['name'])) $fileName = $_GET['name'];

	// Clean the fileName for security reasons
	$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

	// Make sure the fileName is unique but only if chunking is disabled
	if($chunks < 2) {
		$ext = strrpos($fileName, '.');
		//$fileName_a = substr($fileName, 0, $ext);
		$fileName_a = uniqid($prefix, TRUE);
		$fileName_b = substr($fileName, $ext);

		$fileName = $fileName_a . $fileName_b;
	}

	$filePath = $targetDir . '/' . $fileName;

	// Remove old temp files
	if($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
		while(($file = readdir($dir)) !== false) {
			$tmpfilePath = $targetDir . '/' . $file;

			// Remove temp file if it is older than the max age and is not the current file
			if(preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
				@unlink($tmpfilePath);
			}
		}
		closedir($dir);
	} else {
		die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
	}

	// Look for the content type header
	if(isset($_SERVER["HTTP_CONTENT_TYPE"])) {
		$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
	}

	if(isset($_SERVER["CONTENT_TYPE"])) {
		$contentType = $_SERVER["CONTENT_TYPE"];
	}

	if(empty($contentType)) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Content type header not sent."}, "id" : "id"}');
	}
	// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
	if(strpos($contentType, "multipart") !== false) {
		if(isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if($out) {
				// Read binary input stream and append it to temp file
				$in = fopen($_FILES['file']['tmp_name'], "rb");

				if($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else {
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
				}
				fclose($in);
				fclose($out);
				@unlink($_FILES['file']['tmp_name']);
			} else {
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			}
		} else {
			die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		}
	} else {
		// Open temp file
		$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
		if($out) {
			// Read binary input stream and append it to temp file
			$in = fopen("php://input", "rb");

			if($in) {
				while ($buff = fread($in, 4096))
					fwrite($out, $buff);
			} else {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
			fclose($in);
			fclose($out);
		} else {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
	}

	// Check if file has been uploaded
	if(!$chunks || $chunk == $chunks - 1) {
		// Strip the temp .part suffix off
		rename("{$filePath}.part", $filePath);
	}

	if(file_exists($filePath)) {
		$fileInfo = pathinfo($filePath);
		require_once DIR_SYSTEM . 'library/thumbnail-generator.php';
		$globalValidation = new GlobalValidation;

		// Define image mime types
		$imageMimes = array('image/jpeg', 'image/png', 'image/gif');

		// Get the mime type for the
		$mediaMimeType = mime_content_type($filePath);

		// Resize image if requested.
		$imagePath = $targetDir . '/' . $fileName;
		$imageURL .= $fileName;

		// Check if the image is supposed to be resized.
		if(!empty($width) || !empty($height)) {
			// Instantiate the thumbnail generator.
			$thumbnailGenerator = new ThumbnailGenerator($filePath);
			// Resize and save the image if width or height was set.
			if(!empty($width) && empty($height)) {
				$thumbnailGenerator->resizeImage($width, '', 'auto');
			} elseif(empty($width) && !empty($height)) {
				$thumbnailGenerator->resizeImage('', $height, 'auto');
			} else {
				$thumnailGenerator->resizeImage($width, $height, 'auto');
			}

			// Save the image file.
			$thumbnailGenerator->saveImage($imagePath, 85);

			// Delete source image
			if(file_exists($filePath)) {
				unlink($filePath);
			}

			$filePath = $imagePath;
		}

		$adminRequestObj = new stdClass;
		$adminRequestObj->imageURL = $imageURL;
		$adminRequestObj->imagePath = $filePath;
		$adminRequestObj->fileName = $fileName;

		return $adminRequestObj;
	}
}

function getContentFormatForUser($userId) {

	global $acl, $config;

	if(empty($userId)) {
		$userId = $_SESSION['userId'];
	}
	// Get highest ranked role, true means to return the role's id instead of its name.
	$highestRoleId = $acl->getHighestRankedRoleForUser($userId, TRUE);
	// Get the content filter.
	$contentFormat = $config->getContentFilterForRole($highestRoleId);
}
