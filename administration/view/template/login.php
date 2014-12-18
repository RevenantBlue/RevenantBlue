<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';

if(isset($_SESSION['userId'])) {
	$title = 'Dashboard';
} else {
	$title = 'Login';
}
require_once 'head.php';
require_once 'ui.php';
loadJqueryUi();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/admin.js"></script>
<?php elseif(DEVELOPMENT_ENVIRONMENT === FALSE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/admin.min.js"></script>
<?php endif; ?>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" type="text/css" />
</head>
<?php if(!isset($_SESSION['username']) || !aclVerify('backend admin')): ?>
<body id="login-body">
	<div id="login">
		<h1>
			<img src="<?php echo HTTP_IMAGE . "admin/revenantblue-logo.png"; ?>" alt="<?php echo hsc($globalSettings['site_title']['value']); ?>" />
		</h1>
		<form id="loginForm" class="clearfix" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
		<?php if(isset($_SESSION['errors'])): ?>
			<div id="login-errors">
				<?php displayLoginMessages(); ?>
			</div>
		<?php endif; ?>
		<?php if(!isset($_GET['action'])): ?>
			<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
			<p>
				<label>Username<input id="username" class="login-text" type="text" size="20" name="username" /></label>
			</p>
			<p>
				<label>Password<input id="password" class="login-text" type="password" size="20" name="password" /></label>
			</p>
			<p>
				<input type="hidden" value="login" name="submitLogin" />
				<button id="submit-login" class="fltrght rb-btn light-gray-btn" type="submit" name="submitLogin">Login</button>
			</p>
			<div class="clearfix"> </div>
			<div class="login-sublink">
				<a href="<?php echo rtrim(HTTP_ADMIN, '/'); ?>?action=lostpassword">Lost your password?</a>
			</div>
			<div class="login-sublink">
				<?php if(!empty($globalSettings['site_name']['value'])): ?>
				<a href="<?php echo HTTP_SERVER; ?>">Back to <?php echo hsc($globalSettings['site_name']['value']); ?></a>
				<?php else: ?>
				<a href="<?php echo HTTP_SERVER; ?>">Back to site</a>
				<?php endif; ?>
			</div>
		<?php else: ?>
			<?php if(!isset($_SESSION['success'])): ?>
			<div id="login-info">
				<span>Please enter your username or e-mail address. You will be provided with a link that will allow you to change your password.</span>
			</div>
			<input type="hidden" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
			<p>
				<label>
					<span>Username or E-mail</span>
					<input id="lostPassword" class="login-text" type="text" size="20" name="lostPassword" />
				</label>
			</p>
			<p>
				<input class="fltrght" type="submit" name="submitLostPass" value="Reset Password" />
			</p>
			<div class="clearfix"> </div>
			<?php else: ?>
			<div id="login-info">
				<span><?php displayLoginMessages(); ?></span>
			</div>
			<?php endif; ?>
			<div class="login-sublink">
				<a href="<?php echo HTTP_ADMIN ?>">Back to login</a>
			</div>
			<div class="login-sublink">
				<a href="<?php echo HTTP_SERVER; ?>">Back to <?php echo hsc($globalSettings['site_name']['value']); ?></a>
			</div>
		<?php endif; ?>
		</form>
	</div>
<?php elseif(isset($_SESSION['username']) && aclVerify('backend admin')): ?>
<body>
<?php displayBanner($globalSettings); ?>
<?php displayLeftMenu($globalSettings); ?>
<iframe id="main-iframe" name="mainIframe" src="<?php echo HTTP_ADMIN; ?>dashboard" style="visibility: hidden;">

</iframe>
<?php endif; ?>
<?php displayFooter(); ?>
