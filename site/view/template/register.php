<?php
namespace RevenantBlue\Site;

require_once DIR_APPLICATION . 'controller/users/users-c.php';
require_once 'ui.php';
$title = 'Login | ';
require_once 'head.php';
loadJqueryValidation();
?>
<?php loadSiteCss(); ?>
</head>
<body>
	<form id="main-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
		<?php displayHeader(); ?>
		<?php echo $sideNav; ?>
		<section id="main" class="v-align" style="visibility: hidden;">
			<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
			<input type="hidden" id="main-form-action" name="action" />
			<div id="dialogs-container"></div>
			<div id="main-outer" class="v-align">
				<div id="main-inner" class="v-align">
					<section id="login-dialog" class="clearfix">
						<h4>Register</h4>
						<div class="inner">
							<?php displayNotifications(); ?>
							<div>
								<input type="hidden" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
							</div>
							<p class="form-field">
								<label for="reg-email">Email Address</label>
								<input type="email" id="reg-email" name="email" />
							</p>
							<p class="form-field">
								<label for="reg-username">Username</label>
								<input type="text" id="reg-username" name="username" />
							</p>
							<p class="form-field">
								<label for="reg-password">Password</label>
								<input type="password" id="reg-password" name="password" />
							</p>
							<p class="form-field">
								<label for="reg-pwd-confirm">Confirm Password</label>
								<input type="password" id="reg-pwd-confirm" name="confirmPassword" />
							</p>
							<p class="form-field">
								<label for="receive-email" class="center-label">
									<input type="hidden" name="systemEmail" value="off" />
									<input type="checkbox" id="system-email" class="center-toggle" name="systemEmail" />
									Receive notifications by email about updates and downtime
								</label>
							</p>
							<p>
								<button id="submit-reg" class="rb-btn blue-btn vert-space" name="registerUser">REGISTER</button>
							</p>
						</div>
					</section>
				</div>
			</div>
		</section>
	</form>
<?php displayFooter(); ?>
