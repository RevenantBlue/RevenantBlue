<?php
namespace RevenantBlue\Admin;

require_once 'install-c.php';
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Installation | Revenant Blue</title>
		<script src="../site/view/js/bower-components/jquery/dist/jquery.min.js" type="text/javascript"></script>
		<script src="../site/view/js/bower-components/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
		<script type="text/javascript" src="../site/view/js/bower-components/jquery-validation/dist/jquery.validate.min.js"></script>
		<script type="text/javascript" src="../site/view/js/bower-components/jquery-validation/dist/additional-methods.min.js"></script>
		<script type="text/javascript" src="../site/view//js/json/douglascrockford-JSON-js-8e0b15c/json2.js"></script>
		<script type="text/javascript" src="install.js"></script>
		<?php if(isset($_GET['installed']) && defined('HTTP_SERVER')): ?>
		<script>
			var HTTP_SERVER = '<?php echo HTTP_SERVER; ?>'
			  , HTTP_ADMIN = '<?php echo HTTP_ADMIN; ?>';
		</script>
		<?php endif; ?>
		<link href='http://fonts.googleapis.com/css?family=Roboto:400,300,300italic,400italic,500,500italic,700,700italic,900,900italic|Roboto+Condensed:400,400italic,700,700italic|Roboto+Slab' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="install.css" type="text/css" />
		<link rel="stylesheet" href="../site/view/js/jquery-ui/jquery-ui.min.css" />
		<link rel="icon" href="../images/icons/favicons/favicon.ico" />
	</head>
	<body>
		<section id="main" class="clearfix" <?php if(isset($_GET['installed'])): ?>style="height: 100%;" <?php endif; ?>>
			<header class="clearfix">
				<img class="fltlft" src="../images/site/revenant-blue-logo-abbv.png" alt="RB-Logo" />
			</header>
			<div class="inner">
				<section id="errors">
				<?php displayInstallNotifications(); ?>
				<?php if(isset($errors)): ?>
					<?php foreach($errors as $error): ?>
					<div class="errors">
						<?php echo $error; ?>
					</div>
					<?php endforeach; ?>
				<?php endif; ?>
				</section>
				<section id="configuration">
					<h1>Installation</h1>
					<form id="install-form" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
						<?php if(!isset($_GET['installed'])): ?>
						<table class="config-table">
							<tr>
								<th colspan="3">Administration</th>
							</tr>
							<tr>
								<td class="config-name username">
									<label for="admin-username">Username</label>
								</td>
								<td class="config-input username">
									<div class="input-wrap">
										<input type="text" id="admin-username" name="adminUsername"
											<?php if(isset($_SESSION['install']['adminUsername'])): ?>
											   value="<?php echo $_SESSION['install']['adminUsername']; ?>"
											<?php endif; ?>
										/>
									</div>
								</td>
								<td class="desc username">
									The username used for the revenantblue administrator. Avoid using generic names like 'admin'.
								</td>
							</tr>
							<tr>
								<td class="config-name password">
									<label for="admin-pass">Password</label>
								</td>
								<td class="config-input password">
									<div class="input-wrap">
										<input type="password" id="admin-pass" name="adminPass" />
									</div>
								</td>
								<td class="desc password">
									The password for the revenantblue administrator.
								</td>
							</tr>
							<tr>
								<td class="config-name confirm">
									<label for="confirm-admin-pass">Confirm Password</label>
								</td>
								<td class="config-input confirm">
									<div class="input-wrap">
										<input type="password" id="confirm-admin-pass" name="confirmAdminPass">
									</div>
								</td>
								<td class="desc confirm">
									Confirm the password for the revenantblue administrator.
								</td>
							</tr>
							<tr>
								<td class="config-name email">
									<label for="admin-email">E-mail Address</label>
								</td>
								<td class="config-input email">
									<div class="input-wrap">
										<input type="email" id="admin-email" name="adminEmail"
											<?php if(isset($_SESSION['install']['adminEmail'])): ?>
											   value="<?php echo $_SESSION['install']['adminEmail']; ?>"
											<?php endif; ?>
										/>
									</div>
								</td>
								<td class="desc email">
									The e-mail address for the administrative account.
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label>Development Environment</label>
								</td>
								<td class="config-input">
									<p>
										<label for="development-env-true" class="center-label">
											<input type="radio" id="development-env-true" class="center-toggle" name="developmentEnv" value="TRUE"
												<?php if(isset($_SESSION['install']['developmentEnv']) && $_SESSION['install']['developmentEnv'] == 'TRUE'): ?>
												   checked="checked"
												<?php else: ?>
												   checked="checked"
												<?php endif; ?>
											/>
											True
										</label>
									</p>
									<p>
										<label for="development-env-false" class="center-label">
											<input type="radio" id="development-env-false" class="center-toggle" name="developmentEnv" value="FALSE"
												<?php if(isset($_SESSION['install']['developmentEnv']) && $_SESSION['install']['developmentEnv'] == 'FALSE'): ?>
												   checked="checked"
												<?php endif; ?>
											/>
											False
										</label>
									</p>
								</td>
								<td class="desc">
									<p>
										Set the development environment constant located in the config.php file. You will have to switch this manually in the file to change between environments.
									</p>
									<p>
										<strong>Be sure to change the DEVELOPMENT_ENVIRONMENT constant to FALSE in the config.php file if uploading your local site to a web facing server.</strong>
									</p>
									<p>
										<strong>If your local and web directory locations are different for the revenantblue folder please include the optional local directory location under the optional settings.</strong>
									</p>
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label>Server OS</label>
								</td>
								<td class="config-input">
									<p>
										<label for="os-linux" class="center-label">
											<input type="radio" id="os-linux" class="center-toggle" name="os" value="LINUX" checked="checked" />
											Linux
										</label>
									</p>
								</td>
								<td class="desc">
									Set the operating system used by the server. If the server is running on a mac use Linux.
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label>Web Server</label>
								</td>
								<td class="config-input">
									<p>
										<label for="server-apache" class="center-label">
											<input type="radio" id="server-apache" class="center-toggle" name="server" value="APACHE" checked="checked" />
											Apache
										</label>
									</p>
									<p>
										<label for="server-nginx" class="center-label">
											<input type="radio" id="server-nginx" class="center-toggle" name="server" value="NGINX" />
											NGINX
										</label>
									</p>
								</td>
								<td class="desc">
									The webserver you will be using to run your Revenant Blue installation. <strong>If you are using NGINX please copy the nginx.conf file to your NGINX's "sites-available" directory after installation.</strong>
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label for="admin-location">Backend location</label>
								</td>
								<td class="config-input">
									<div class="input-wrap">
										<input type="text" id="admin-location" name="adminLocation"
											<?php if(isset($_SESSION['install']['adminLocation'])): ?>
											   value="<?php echo $_SESSION['install']['adminLocation']; ?>"
											<?php else: ?>
											   value="rbadmin"
											<?php endif; ?>
										/>
									</div>
								</td>
								<td class="desc">
									<p>
										The name used to access revenant blue's backend. 
									</p>
									<p>
										For example if you want the administrative backend to be located at 'http://www.mysite.com/rbadmin' you would enter 'rbadmin' in this field.
									</p>
									<p>
										<strong>Using the name 'admin' is not advised.</strong>
									</p>
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label>Path to Backup Directory</label>
								</td>
								<td class="config-input">
									<p>
										<label for="backup-path" class="center-label">
											<input type="text" id="backup-path" name="backupPath"
												   <?php if(isset($_SESSION['install']['backupPath'])): ?>
												   value="<?php echo $_SESSION['install']['backupPath']; ?>"
												   <?php endif; ?>
											/>
										</label>
									</p>
								</td>
								<td class="desc">
									<p>
										Path to the directory where you want to store your site's backups. If you do not provide a location the built-in backup function will not work.
									</p>
									<p>
										<strong>Write permission to this directory is required for proper function.</strong>
									</p>
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label>Access Code</label>
								</td>
								<td class="config-input">
									<p>
										<label for="access-code">
											<input type="text" id="access-code" name="accessCode"
												<?php if(isset($_SESSION['install']['accessCode'])): ?>
												   value="<?php echo $_SESSION['install']['accessCode']; ?>"
												<?php endif; ?>
											/>
										</label>
									</p>
								</td>
								<td class="desc">
									<p>
										The code used to access the website when it is in maintenance mode. The access should be passed with the GET parameter 'access'.
									</p>
									<p>
										<strong>Example: </strong> http://www.yourdomain.com?access=<strong>my-access-code123</strong>
									</p>
								</td>
							</tr>
						</table>
						<table class="config-table">
							<tr>
								<th colspan="3">MySQL Settings</th>
							</tr>
							<tr>
								<td class="config-name">
									<label for="db-name">Database Name</label>
								</td>
								<td class="config-input">
									<div class="input-wrap">
										<input type="text" id="db-name" name="dbName"
											<?php if(isset($_SESSION['install']['dbName'])): ?>
											   value="<?php echo $_SESSION['install']['dbName']; ?>"
											<?php else: ?>
											   value="revenantblue"
											<?php endif; ?>
										/>
									</div>
								</td>
								<td class="desc">
									Name the database to be used for revenantblue. This database must be created prior to installation.
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label for="db-prefix">Database Prefix</label>
								</td>
								<td class="config-input">
									<div class="input-wrap">
										<input type="text" id="db-prefix" class="sm-txt" name="dbPrefix"
											<?php if(isset($_SESSION['install']['dbPrefix'])): ?>
											   value="<?php echo $_SESSION['install']['dbPrefix']; ?>"
											<?php else: ?>
											   value="rb_"/>
											<?php endif; ?>
									</div>
								</td>
								<td class="desc">
									<p>
										Choose a unique prefix to be used to separate the cache and database from other revenantblue installations.
									</p>
									<p>
										<strong>Using the same prefix for multiple sites on the same server will cause caching irregularities between the sites.</strong>
									</p>
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label for="db-location">MySQL Location</label>
								</td>
								<td class="config-input">
									<div class="input-wrap">
										<input type="text" id="db-location" name="dbHost" value="localhost" />
									</div>
								</td>
								<td class="desc">
									Enter the IP address or URL of the MySQL database where revenantblue will be installed. If the database is on the same server as revenantblue then 'localhost' or '127.0.0.1' should be used.
								</td>
							</tr>
							<tr>
								<td class="config-name username">
									<label for="client-db-user">Client MySQL Username</label>
								</td>
								<td class="config-input username">
									<div class="input-wrap">
										<input type="text" id="client-db-user" name="clientDbUser"
											<?php if(isset($_SESSION['install']['clientDbUser'])): ?>
											   value="<?php echo $_SESSION['install']['clientDbUser']; ?>"
											<?php endif; ?>
										/>
									</div>
								</td>
								<td class="desc username">
									<strong>Do not use the 'root' user.</strong> Select a username for the your clients' database access. The client user has restricted access to the database.
								</td>
							</tr>
							<tr>
								<td class="config-name password">
									<label for="client-db-pass">Client MySQL Password</label>
								</td>
								<td class="config-input password">
									<div class="input-wrap">
										<input type="password" id="client-db-pass" name="clientDbPass" />
									</div>
								</td>
								<td class="desc password">
									The password for the MySQL client user.
								</td>
							</tr>
							<tr>
								<td class="config-name confirm">
									<label for="confirm-client-db-pass">Confirm Password</label>
								</td>
								<td class="config-input confirm">
									<div class="input-wrap">
										<input type="password" id="confirm-client-db-pass" name="confirmClientDbPass" />
									</div>
								</td>
								<td class="desc confirm">
									Confirm the password for the MySQL client user.
								</td>
							</tr>
							<tr>
								<td class="config-name username">
									<label for="admin-db-user">Admin MySQL Username</label>
								</td>
								<td class="config-input username">
									<div class="input-wrap">
										<input type="text" id="admin-db-user" name="adminDbUser"
											<?php if(isset($_SESSION['install']['adminDbUser'])): ?>
											   value="<?php echo $_SESSION['install']['adminDbUser']; ?>"
											<?php endif; ?>
										/>
									</div>
								</td>
								<td class="desc username">
									<strong>Do not use the 'root' user.</strong> Select a username for administrative MySQL access. This access is only used for users who have backend priviliges to this site and has many more rights than the client MySQL user.
								</td>
							</tr>
							<tr>
								<td class="config-name password">
									<label for="admin-db-pass">Admin MySQL Password</label>
								</td>
								<td class="config-input password">
									<div class="input-wrap">
										<input type="password" id="admin-db-pass" name="adminDbPass" />
									</div>
								</td>
								<td class="desc password">
									The password for the MySQL admin user.
								</td>
							</tr>
							<tr>
								<td class="config-name confirm">
									<label for="confirm-admin-db-pass">Confirm Password</label>
								</td>
								<td class="config-input confirm">
									<div class="input-wrap">
										<input type="password" id="confirm-admin-db-pass" name="confirmAdminDbPass" />
									</div>
								</td>
								<td class="desc confirm">
									Confirm the password for the MySQL admin user.
								</td>
							</tr>
							<tr>
								<td class="config-name">
									
								</td>
								<td class="config-input">
								
								</td>
								<td class="desc">
									<p>
										Revenant Blue uses two MySQL users, one for clients and one for administrators. One user can be used for both.
									</p>
									<p>
										Recommended settings for client access:
									</p>
									<p>
										GRANT SELECT, INSERT, UPDATE, DELETE ON database.* TO 'clientAccess'@'localhost';
									</p>
								</td>
							</tr>
						</table>
						<table class="config-table">
							<tr>
								<th colspan="3">Redis Settings (optional)</th>
							</tr>
							<tr>
								<td class="config-name">
									Redis Features
								</td>
								<td class="config-input">
									<p>
										<label class="center-label">
											<input type="radio" class="center-toggle" name="enableRedis" value="TRUE"
												<?php if(isset($_SESSION['install']['enableRedis']) && $_SESSION['install']['enableRedis'] == 'TRUE'): ?>
												   checked="checked"
												<?php endif; ?>
											/>
											Enable
										</label>
									</p>
									<p>
										<label class="center-label">
											<input type="radio" class="center-toggle" name="enableRedis" value="FALSE"
												<?php if(isset($_SESSION['install']['enableRedis']) && $_SESSION['install']['enableRedis'] == 'FALSE'): ?>
												   checked="checked"
												<?php elseif(!isset($_SESSION['install'])): ?>
												   checked="checked"
												<?php endif; ?>
											/>
											
											Disable
										</label>
									</p>
								</td>
								<td class="desc">
									<p>
										Enable or disable Redis features of Revenant Blue.
									</p>
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label for="redis-port">Redis Port</label>
								</td>
								<td class="config-input">
									<p>
										<input id="redis-port" type="text" name="redisPort"
											<?php if(isset($_SESSION['install']['redisPort'])): ?>
											   value="<?php echo $_SESSION['install']['redisPort']; ?>"
											<?php else: ?>
											   value="6379"
											<?php endif; ?>
										/>
									</p>
								</td>
								<td class="desc">
									<p>
										The port on which your Redis server is listening. If you did not manually change your port setting then the Redis default of 6379 should be used.
									</p>
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label for="redis-host">Redis Host</label>
								</td>
								<td class="config-input">
									<input id="redis-host" type="text" name="redisHost"
										<?php if(isset($_SESSION['install']['redisHost'])): ?>
										   value="<?php echo $_SESSION['install']['redisHost']; ?>"
										<?php else: ?>
										   value="127.0.0.1"
										<?php endif; ?>
									/>
								</td>
								<td class="desc">
									<p>
										The host address of your redis installation. Keep set to 127.0.0.1 unless redis is hosted on a separate server from your web directory.
									</p>
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label>Redis Session Handler</label>
								</td>
								<td class="config-input">
									<p>
										<label class="center-label">
											<input type="radio" class="center-toggle" name="redisSessions" value="TRUE"
												<?php if(isset($_SESSION['install']['redisSessions']) && $_SESSION['install']['redisSessions'] == 'TRUE'): ?>
												   checked="checked"
												<?php endif; ?>
											/>
											Yes
										</label>
									</p>
									<p>
										<label class="center-label">
											<input type="radio" class="center-toggle" name="redisSessions" value="FALSE"
												<?php if(isset($_SESSION['install']['redisSessions']) && $_SESSION['install']['redisSessions'] == 'FALSE'): ?>
												   value="<?php echo $_SESSION['install']['redisSessions']; ?>" checked="checked"
												<?php elseif(!isset($_SESSION['install'])): ?>
												   checked="checked"
												<?php endif; ?>
											/>
											No
										</label>
									</p> 
								</td>
								<td class="desc">
									<p>
										Allows PHP sessions to be handled via Redis instead of MySQL.
									</p>
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label>PHP/Node.js Redis Session Sharing</label>
								</td>
								<td class="config-input">
									<p>
										<label class="center-label">
											<input type="radio" class="center-toggle" name="redisPhpSharing" value="TRUE"
												<?php if(isset($_SESSION['install']['redisPhpSharing']) && $_SESSION['install']['redisPhpSharing'] == 'TRUE'): ?>
												   checked="checked"
												<?php endif; ?>
											/>
											On
										</label>
									</p>
									<p>
										<label class="center-label">
											<input type="radio" class="center-toggle" name="redisPhpSharing" value="FALSE"
												<?php if(isset($_SESSION['install']['redisPhpSharing']) && $_SESSION['install']['redisPhpSharing'] == 'FALSE'): ?>
												   checked="checked"
												<?php elseif(!isset($_SESSION['install'])): ?>
												   checked="checked"
												<?php endif; ?>
											/>
											Off
										</label>
									</p>
								</td>
								<td class="desc">
									<p>
										Makes PHP sessions accessible by node.js. 
										It is not advised to use PHP/node.js session sharing if you are not planning to integrate your node application with Revenant Blue. 
										Your site will still work fine with session sharing enabled but no node application, it will just add a slight, unnecessary, session overhead to your website.
									</p>
								</td>
							</tr>
						</table>
						<table class="config-table">
							<tr>
								<th colspan="3">Optional (Auto Generated unless specified)</th>
							</tr>
							<tr>
								<td class="config-name">
									<label for="local-directory">Path to Local Directory</label>
								</td>
								<td class="config-input">
									<div class="input-wrap">
										<input type="text" id="local-directory" name="localDirectory"
											<?php if(isset($_SESSION['install']['localDirectory'])): ?>
											   value="<?php echo $_SESSION['install']['localDirectory']; ?>"
											<?php endif; ?>
										/>
									</div>
								</td>
								<td class="desc">
									<p>
										The path to the local directory for revenantblue. If the local directory is in a different location than the web facing directory enter it here. Otherwise it is assumed that both directories are in the same location.
									</p>
									<p>
										<strong>Example: '/home/www/revenantblue/'</strong>
									</p>
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label for="local-domain">Local Domain</label>
								</td>
								<td class="config-input">
									<div class="input-wrap">
										<input type="text" id="local-domain" name="localDomain" 
											<?php if(isset($_SESSION['install']['localDomain'])): ?>
											   value="<?php echo $_SESSION['install']['localDomain']; ?>"
											<?php endif; ?>
										/>
									</div>
								</td>
								<td class="desc">
									<p>
										If the local domain is located somewhere besides 'localhost' or '127.0.0.1' enter that location here. 
									</p>
									<p>
										<strong>Example: 192.168.1.190</strong>
									</p>
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label for="local-domain">Production Domain</label>
								</td>
								<td class="config-input">
									<div class="input-wrap">
										<input type="text" id="production-domain" name="productionDomain" 
											<?php if(isset($_SESSION['install']['productionDomain'])): ?>
											   value="<?php echo $_SESSION['install']['productionDomain']; ?>"
											<?php endif; ?>
										/>
									</div>
								</td>
								<td class="desc">
									<p>
										If you know the domain for your production server then fill this in, otherwise the production server will be defaulted the server on which you are installing Revenant Blue. If this is incorrect then you wil have to manually set the production domain in the main config.php file. 
									</p>
									<p>
										<strong>Example: revenantblue.com</strong>
									</p>
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label for="csrf-key">CSRF Key</label>
								</td>
								<td class="config-input">
									<div class="input-wrap">
										<input type="text" id="csrf-key" name="csrfKey" />
									</div>
								</td>
								<td class="desc">
									The key used for building the CSRF token that will be used to stop XSS attacks on form submissions.
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label for="pw-algorithm">Hashing Algorithm</label>
								</td>
								<td class="config-input">
									<div class="input-wrap">
										<input type="text" id="pw-algorithm" name="hashType" />
									</div>
								</td>
								<td class="desc">
									The algorithm used for hasing passwords. The default algorithm is sha512.
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label for="pw-iterations">Number of Hash Iterations</label>
								</td>
								<td class="config-input">
									<div class="input-wrap">
										<input type="text" id="pw-iterations" name="hashIterations" />
									</div>
								</td>
								<td class="desc">
									Number of hashing iterations for passwords.
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label for="hash-length">Hash Length</label>
								</td>
								<td class="config-input">
									<div class="input-wrap">
										<input type="text" id="hash-length" name="hashLength" />
									</div>
								</td>
								<td class="desc">
									The length of the hashed password after iteration.
								</td>
							</tr>
							<tr>
								<td class="config-name">
									<label for="aes-key">AES256 Encryption Key</label>
								</td>
								<td class="config-input">
									<div class="input-wrap">
										<input type="text" id="aes-key" name="aesKey" />
									</div>
								</td>
								<td class="desc">
									The key used to encrypt hashed passwords in the database with AES 256 encryption.
								</td>
							</tr>
						</table>
						<div id="install-actions">
							<button type="submit" id="submit-config" name="submitConfig" class="rb-btn blue-btn" value="1">Install Revenant Blue</button>
							<button type="submit" id="restore-config" name="restoreConfig" class="rb-btn light-gray-btn" value="1">Restore Config Files to Defaults</button>
						</div>
						<?php elseif(isset($_GET['installed'])): ?>
						<section id="install-success">
							<div class="inner">
								<h3>Revenant Blue was installed successfully!</h3>
								<div id="del-notify">
									<h5>If you're using NGINX please copy the 'nginx.conf' file from the installation directory and place it in your NGINX 'sites-available' directory
									<h5>Make sure you delete the installation directory!</h5>
									<h5>Keeping it in the web directory is a huge security risk.</h5>
								</div>
								<div id="redirect-buttons">
									<button id="redirect-to-admin" class="rb-btn">Take me to the backend</button>
									<button id="redirect-to-site" class="rb-btn">Take me to the site</button>
								</div>
							</div>
						</section>
						<?php endif; ?>
					</form>
				</section>
			</div>
		</section>
	</body>
</html>
<?php installCleanUp(); ?>
