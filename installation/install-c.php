<?php
namespace RevenantBlue\Admin;
use RevenantBlue\Db;
use RevenantBlue;
use \PDO;

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

session_start();

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	
	if(isset($_GET['installed'])) {
		require_once('../config.php');
		require_once('../system/database/db.php');
		require_once('../system/database/redis.php');
		require_once('../administration/model/config/config-main.php');
		require_once('../administration/startup.php');
	} else {
		if(!is_writable('../config.php')) {
			$errors[] = 'Cannot write to /config.php, please allow temporary write privileges to this file.';
		}
		if(!is_writable('nginx.conf')) {
			$errors[] = 'Cannot write to /installation/nginx.conf, please allow temporary write privilegs to this file';
		}
		
		exec('dpkg -s cron', $output);
		
		foreach($output as $line) {
			if(stristr($line, 'ok installed')) {
				$cronInstalled = TRUE;
				break;
			}
		}
		
		if(empty($cronInstalled)) {
			$errors[] = 'Cron is not installed on your server. Without cron installed, scheduled tasks, including the mail queue, will not be operational.';
		}
		
		/*
		$htaccess = file('../.htaccess');
		print_r3($htaccess);
		*/
	}
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	if(isset($_POST['submitConfig'])) {
		//print_r3($_POST);
		foreach($_POST as $name => $value) {
			$_SESSION['install'][$name] = $value;
		}
		
		//print_r3($_SESSION); exit;
		
		// Set the csrf key
		$csrfKey = !empty($_POST['csrfKey']) ? $_POST['csrfKey'] : getRandomString(mt_rand(64,128));
		
		// Set the two parts of the key for AES encryption
		$usersKey = !empty($_POST['aesKey']) ? $_POST['aesKey'] : getRandomString(mt_rand(32, 64));
		
		// Set the hash configurations
		$hashIterations = !empty($_POST['hashIterations']) ? $_POST['hashIterations']  : mt_rand(4000, 9000);
		$hashLength = !empty($_POST['hashLength']) ? $_POST['hashLength']: 64;
		
		// Set the config.php constants
		$configFile = file('../config.php');
		
		foreach($configFile as $key => $configLine) {
			// Only overwrite the paths/urls
			if($key >= 49) {
				$configFile[$key] = str_replace('rbadmin', $_POST['adminLocation'], $configLine);
			}
		}
		
		//print_r3($configFile);
		
		// Set the development environment
		$configFile[6] = "define('DEVELOPMENT_ENVIRONMENT', TRUE);" . PHP_EOL;
		
		// Set the sever os
		$configFile[8] = "define('SERVER_OS', '" . strtoupper($_POST['os']) . "');" . PHP_EOL;

		// Set the backend name
		$configFile[10] = "define('BACKEND_NAME', '" . $_POST['adminLocation'] . "');" . PHP_EOL;
		// Set the database prefix
		$configFile[12] = "define('PREFIX', '" . $_POST['dbPrefix'] . "');" . PHP_EOL;
		// Set the name for the revenantblue database
		$configFile[14] = "define('DB_NAME', '" . $_POST['dbName'] . "');" . PHP_EOL;
		
		$configFile[16] = "define('DB_CONN', 'mysql:host=" . $_POST['dbHost'] . ";dbname=" . $_POST['dbName'] . ";charset=utf8');" . PHP_EOL;
		$configFile[17] = "define('DB_CONN_LOCAL', 'mysql:host=" . $_POST['dbHost'] . ";dbname=" . $_POST['dbName'] . ";charset=utf8');" . PHP_EOL;
		$configFile[18] = "define('DB_HOST', '" . $_POST['dbHost'] . "');" . PHP_EOL;
		$configFile[19] = "define('DB_HOST_LOCAL', '" . $_POST['dbHost'] . "');" . PHP_EOL;
		$configFile[20] = "define('DB_USER', '" . $_POST['clientDbUser'] . "');" . PHP_EOL;
		$configFile[21] = "define('DB_PASS', '" . $_POST['clientDbPass'] . "');" . PHP_EOL;
		$configFile[22] = "define('DB_USER_ADMIN', '" . $_POST['adminDbUser'] . "');" . PHP_EOL;
		$configFile[23] = "define('DB_PASS_ADMIN', '" . $_POST['adminDbPass'] . "');" . PHP_EOL;
		$configFile[24] = "define('CSRF_KEY', '" . $csrfKey . "');" . PHP_EOL;
		$configFile[25] = "define('USERS_KEY', '" . $usersKey . "');" . PHP_EOL;
		$configFile[26] = empty($_POST['hashType']) ? "define('HASH_ALGORITHM', 'sha512');" . PHP_EOL : "define('HASH_ALGORITHM', '" . strtolower($_POST['hashType']) + "');" . PHP_EOL;
		$configFile[27] = "define('HASH_ITERATIONS', '" . $hashIterations . "');" . PHP_EOL;
		$configFile[28] = "define('HASH_LENGTH', '" . $hashLength . "');" . PHP_EOL;
		$configFile[29] = "define('ACCESS_CODE', '" . $_POST['accessCode'] . "');" . PHP_EOL;
		
		// Redis configuration
		$configFile[31] = "define('REDIS', " . $_POST['enableRedis'] . ");" . PHP_EOL;
		$configFile[32] = "define('REDIS_HOST', '" . $_POST['redisHost'] . "');" . PHP_EOL;
		$configFile[33] = "define('REDIS_PORT', '" . $_POST['redisPort'] . "');" . PHP_EOL;
		$configFile[34] = "define('REDIS_SESSIONS', " . $_POST['redisSessions'] . ");" . PHP_EOL;
		$configFile[35] = "define('REDIS_PHP_NODE_SESSION_SHARING', " . $_POST['redisPhpSharing'] . ");" . PHP_EOL;
		
		// Build the current working directory.
		$cwd = str_replace('/installation', '', getcwd());
		
		// Implode the config file for further editing.
		$configFile = implode('', $configFile);
		
		// Add the current working directory to the config file based on operating system.
		if($_POST['os'] === 'LINUX') {
			$configFile = str_replace('linux-path', $cwd, $configFile);
		} elseif($_POST['os'] === 'WINDOWS') {
			$configFile = str_replace('windows-path', $cwd, $configFile);
		}
		// Add the mysql path to the config file.
		// $configFile = str_replace('mysql-path', $_POST['mysqlPath'], $configFile);
		
		// Get the Domain name
		$domainName = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		
		// If the user provided a local domain set it else make the local domain localhost
		if(!empty($_POST['localDomain'])) {
			$configFile = str_replace('local-domain', $_POST['localDomain'], $configFile);
		} else {
			$configFile = str_replace('local-domain', 'localhost', $configFile);
		}
		
		// Set the web domain
		if(!empty($_POST['productionDomain'])) {
			$configFile = str_replace('web-domain', $_POST['productionDomain'], $configFile);
		} else {
			$configFile = str_replace('web-domain', $domainName, $configFile);
		}
		
		// If the user provided a local directory for revenant blue add it to the config file.
		if(!empty($_POST['localDirectory'])) {
			$configFile = str_replace('local-path', $_POST['localDirectory'], $configFile);
		} else {
			$configFile = str_replace('local-path', $cwd, $configFile);
		}
		
		// Set the backup path.
		if(!empty($_POST['backupPath'])) {
			$configFile = str_replace('backup-path', $_POST['backupPath'], $configFile);
		}
		
		$configFile = str_replace('cwd', $cwd, $configFile);
		
		// Replace the config file.
		file_put_contents('../config.php', $configFile);
		
		
		if($_POST['server'] === 'APACHE') {
			// Edit the htaccess file and set the backend location environment variable.
			$htaccess = file('htaccess.txt');
			/*
			foreach($htaccess as $key => $htLine) {
				$htaccess[$key] = str_replace("rbadmin", $_POST['adminLocation'], $htLine);
			}
			* */
			file_put_contents('../.htaccess', implode('', $htaccess));
		} elseif($_POST['server'] === 'NGINX') {
			// Edit the NGINX configuration file
			$nginxConf = file('nginx.conf');
			
			$nginxLength = count($nginxConf);
			
			for($x = 0; $x < $nginxLength; $x++) {
				if(!empty($_POST['localDomain']) && $x === 27) {
					$nginxConf[$x] = str_replace('127.0.0.1', $_POST['localDomain'], $nginxConf[$x]);
				} else if($x === 23) {
					$nginxConf[$x] = str_replace('/path/to/root', $cwd, $nginxConf[$x]);
				} else {
					$nginxConf[$x] = str_replace("rbadmin", $_POST['adminLocation'], $nginxConf[$x]);
				}
			}
			file_put_contents('nginx.conf', implode('', $nginxConf));
		}
		
		require_once('../config.php');
		require_once('../system/database/db.php');
		require_once('../system/database/redis.php');
		
		class DbTest extends Db {
			
			public function testDb() {
				if(!self::$dbh) {
					$mysqlErr = $this->connect(TRUE);
					return $mysqlErr;
				}
			}
			
			public function buildCmdsForPrefixConcat() {
				try {
					if(!self::$dbh) $this->connect();
					$stmt = self::$dbh->prepare(
						"SELECT CONCAT('ALTER TABLE ', TABLE_NAME, ' RENAME TO " . PREFIX . "', TABLE_NAME, ';')
						 FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'"
					);
					$stmt->execute();
					return $stmt->fetchAll(PDO::FETCH_COLUMN);
				} catch(PDOException $e) {
					$_SESSION['errors'] = 'An error occurred while attempting to build the table list for prefix concatention.';
					restoreConfig();
					header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
					exit;
				}
			}
			
			public function executeStmt($sqlStmt) {
				try {
					if(!self::$dbh) $this->connect();
					$stmt = self::$dbh->prepare($sqlStmt);
					$stmt->execute();
				} catch(PDOException $e) {
					
				}
			}
		}
		
		$installPrep = new DbTest;
		$dbError = $installPrep->testDb();
		
		if(!empty($dbError)) {
			$_SESSION['errors'] = 'An error occurred while connecting to your MySQL database. Make sure that the database for Revenant Blue has been created and that the MySQL admin user has the create table privileges for the database.';
			// Restore the config file.
			restoreConfig();
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		}
		
		// Remove existing database tables if a failed install. Run it twice because of foreign keys existing and breaking the first iteration.
		exec('mysql -h ' . DB_HOST . ' -u ' . DB_USER_ADMIN . ' -p' . DB_PASS_ADMIN .  ' -Nse \'show tables\' ' . DB_NAME . ' | while read table; do mysql -u ' . DB_USER_ADMIN . ' -p' . DB_PASS_ADMIN . ' -e "drop table $table" ' .  DB_NAME . '; done');
		exec('mysql -h ' . DB_HOST . ' -u ' . DB_USER_ADMIN . ' -p' . DB_PASS_ADMIN .  ' -Nse \'show tables\' ' . DB_NAME . ' | while read table; do mysql -u ' . DB_USER_ADMIN . ' -p' . DB_PASS_ADMIN . ' -e "drop table $table" ' .  DB_NAME . '; done');
		
		// Install the database
		exec('mysql -h ' . DB_HOST . ' -u ' . DB_USER_ADMIN . ' -p' . DB_PASS_ADMIN  . ' ' . DB_NAME . ' < ' . DIR_ROOT . 'installation/rb.sql', $output);
		
		// Set the prefixes for the tables.
		$prefixCmds = $installPrep->buildCmdsForPrefixConcat();
		if(empty($prefixCmds)) {
			$_SESSION['errors'] = 'An error occurred while concatenating prefixes to the table names.';
			// Restore the config file.
			restoreConfig();
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		} else {
			foreach($prefixCmds as $prefixCmd) {
				$installPrep->executeStmt($prefixCmd);
			}
		}
		
		require_once('../administration/model/config/config-main.php');
		require_once('../administration/startup.php');
		require_once('../system/engine/acl.php');
		require_once('../administration/model/users/users-main.php');
		require_once('../administration/controller/users/users-validation.php');
		$users = new Users;
		
		$newAdminUser = array(
			'firstName'       => ''
		  , 'lastName'        => ''
		  , 'username'        => $_POST['adminUsername']
		  , 'password'        => $_POST['adminPass']
		  , 'confirmPassword' => $_POST['confirmAdminPass']
		  , 'email'           => $_POST['adminEmail']
		  , 'enabled'         => 1
		  , 'activated'       => 1
		  , 'roles'           => array(2 => 'on', 4 => 'on')
		  , 'systemEmail'     => 1
		);
		
		$newUser = new NewUserValidation($newAdminUser);

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
			header('Location: ' . $_SERVER['REQUEST_URI'] . '?installed=true', TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['restoreConfig'])) {
		restoreConfig();
		$_SESSION['success'] = "The configuration file has been restored to its pre-installation defaults";
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	} elseif(isset($_POST['appRequest'])) {
		$appRequestObj = json_decode($_POST['appRequest']);
		if($appRequestObj->type === 'installation' && $appRequestObj->action === 'del-install-dir') {
			require_once('../config.php');
			require_once('../system/database/db.php');
			require_once('../system/database/redis.php');
			require_once('../administration/model/config/config-main.php');
			require_once('../administration/startup.php');
			rrmdir('../installation');
			if(is_dir('../installation')) {
				$appRequestObj->error = "Revenant Blue could not delete folder. Check your permissions or delete the folder yourself.";
			}
		}
		echo json_encode($appRequestObj);
		exit;
	}
}

function restoreConfig() {
	copy('config.php', '../config.php');
	copy('htaccess.txt', '../.htaccess');
	copy('nginx.txt', 'nginx.conf');
	if(file_exists('../.htaccess')) {
		unlink('../.htaccess');
	}
}

function getRandomString($length = 8, $symbols = FALSE) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	if($symbols === TRUE) {
		$characters .= '!@#$%^&*';
	}
	$string = '';

	for ($i = 0; $i < $length; $i++) {
		$string .= $characters[mt_rand(0, strlen($characters) - 1)];
	}

	return $string;
}

function unpadPKCS7($data, $blockSize) {
	$length = strlen ( $data );
	if ($length > 0) {
		$first = substr ( $data, - 1 );
	   
		if (ord ( $first ) <= $blockSize) {
			for($i = $length - 2; $i > 0; $i --)
				if (ord ( $data [$i] != $first ))
					break;
		   
			return substr ( $data, 0, $i );
		}
	}
	return $data;
}

function print_r3($array) {
	echo "<pre>";
	print_r($array);
	echo "</pre>";
}
function hsc2($var, $quote_style = ENT_QUOTES, $charset = 'UTF-8') {
	$var = htmlspecialchars($var, $quote_style, $charset);
	return $var;
}

function installCleanUp() {
	unset($_SESSION['install']);
}

?>

<?php function displayInstallNotifications() { ?>
<?php if(!empty($_SESSION['errors'])): ?>
	<?php if(is_array($_SESSION['errors'])): ?>
		<?php foreach($_SESSION['errors'] as $key=>$error): ?>
			<?php if(is_array($error)): ?>
				<?php foreach($error as $key2 => $error2): ?>
				 <div id="errors<?php echo $key2; ?>" class="errors notifications">
					<span class="icon-40-error icon-40-spacing"> </span>
					<p class="error-txt"><?php echo hsc2($error2); ?></p>
					<span class="icon-20-close-notification icon-20-spacing" onclick="Javscript: $('#errors<?php echo $key2; ?>').remove();"> </span>
				</div>
				<?php endforeach; ?>
			<?php else: ?>
			<div id="errors<?php echo $key; ?>" class="errors notifications">
				<span class="icon-40-error icon-40-spacing"> </span>
				<p class="error-txt"><?php echo hsc2($error); ?></p>
				<span class="icon-20-close-notification icon-20-spacing" onclick="Javscript: $('#errors<?php echo $key; ?>').remove();"> </span>
			</div>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php else: ?>
		<div id="errors" class="errors notifications">
			<span class="icon-40-error icon-40-spacing"> </span>
			<p class="error-txt"><?php echo hsc2($_SESSION['errors']); ?></p>
			<span class="icon-20-close-notification icon-20-spacing" onclick="Javscript: $('#errors').remove();"> </span>
		</div>
	<?php endif; ?>
	<?php unset($_SESSION['errors']); ?>
<?php endif; ?>
<?php if(!empty($_SESSION['success'])): ?>
	<?php if(is_array($_SESSION['success'])): ?>
		<?php foreach($_SESSION['success'] as $key=>$success): ?>
		<div id="success<?php echo $key; ?>" class="success notifications">
			<span class="icon-40-inform icon-40-spacing"> </span>
			<p class="success-txt"><?php echo hsc2($success); ?></p>
			<span class="icon-20-close-notification icon-20-spacing" onclick="Javscript: $('#success<?php echo $key; ?>').remove();"> </span>
		</div>
		<?php endforeach; ?>
	<?php else: ?>
		<div id="success" class="success notifications">
			<span class="icon-40-inform icon-40-spacing"> </span>
			<p class="success-txt"><?php echo hsc2($_SESSION['success']); ?></p>
			<span class="icon-20-close-notification icon-20-spacing" onclick="Javscript: $('#success').remove();"> </span>
		</div>
	<?php endif; ?>
	<?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php }
