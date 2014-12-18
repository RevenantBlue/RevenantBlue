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
			<div id="main-outer" class="v-align">
				<div id="main-inner" class="clearfix v-align">
					<section id="main-content">
						<?php if(isset($_GET['verify']) && isset($emailVerified) && isset($accountActivated)): ?>
						<section id="login-dialog" class="clearfix">
							<div class="standout-space">
								<div class="inner">
									<div>
										<p>Your account has been activated.</p>
										<p>
											<a href="<?php echo HTTP_SERVER . 'login'; ?>">Login to your account.</a>
										</p>
									</div>
								</div>
							</div>
						</section>
						<?php elseif(isset($_GET['pwreset']) && !empty($pwReset)): ?>
						<section id="login-dialog" class="clearfix">
							<div class="standout-space">
								<div class="inner">
									<h2 class="clean-header">Reset Password</h2>
									<input type="hidden" name="id" value="<?php echo hsc($pwReset['user_id']); ?>" />
									<input type="hidden" name="code" value="<?php echo hsc($pwReset['user_code']); ?>" />
									<p class="form-field">
										<label>
											<span>Password</span>
											<input type="password" name="newPassword" />
										</label>
									</p>
									<p>
										<label>
											<span>Confirm password</span>
											<input type="password" name="confirmNewPassword" />
										</label>
									</p>
									<button type="submit" class="rb-btn light-gray-btn top-space" name="submitPasswordReset">Reset Password</button>
								</div>
							</div>
						</section>
						<?php elseif(isset($_GET['approval']) && !empty($approval)): ?>
						<section id="login-dialog" class="clearfix">
							<h4>
								Create New Password
							</h4>
							<div class="standout-space">
								<div class="inner">
									<input type="hidden" name="id" value="<?php echo hsc($approval['user_id']); ?>" />
									<input type="hidden" name="code" value="<?php echo hsc($approval['user_code']); ?>" />
									<p class="form-field">
										<label>
											<span>Password</span>
											<input type="password" name="newPassword" />
										</label>
									</p>
									<p class="form-field">
										<label>
											<span>Confirm password</span>
											<input type="password" name="confirmNewPassword" />
										</label>
									</p>
									<input type="submit" name="submitPasswordReset" class="rb-btn" value="Reset Password" />
								</div>
							</div>
						</section>
						<?php elseif(isset($_GET['action']) && $_GET['action'] === 'resend-email-verification' && isset($_SESSION['userIdError'])): ?>
						<section id="login-dialog" class="clearfix">
							<div class="standout-space">
								<div class="inner">
									<?php displayNotifications(); ?>
									<h2 class="clean-header bottom-space">Resend Email Verification</h2>
									<?php if(isset($_SESSION['userIdError'])): ?>
									<input type="hidden" id="user-id" name="id" value="<?php echo hsc($_SESSION['userIdError']); ?>" />
									<?php endif; ?>
									<p class="vert-space">
										You can resend your confirmation email if you accidentally deleted (it happens).
									</p>
									<button id="resend-email-verification" class="rb-btn top-space">Resend Verification Email</button>
								</div>
							</div>
						</section>
						<?php else: ?>
							<?php if(!isset($_GET['lostpass'])): ?>
							<section id="login-dialog" class="clearfix">
								<h4>
									Login
								</h4>
								<div class="inner">
									<?php displayNotifications(); ?>
									<p class="form-field">
										<label for="login-email">Email Address</label>
										<input type="email" id="login-email" name="email" value="" />
									</p>
									<p class="form-field">
										<label for="login-password">Password</label>
										<input type="password" id="login-password" name="password" />
									</p>
									<p>
										<button type="submit" id="submit-login" class="rb-btn blue-btn" name="submitLogin" value="submit-login">Login</button>
									</p>
									<p>
										Not a member?
									</p>
									<p>
										<button id="register-button" class="rb-btn vert-space cancel">Register</button>
									</p>
									<p id="bottom-links">
										<a href="<?php echo HTTP_SERVER; ?>login?lostpass=true">Forgot your password?</a>
									</p>
								</div>
							</section>
							<?php else: ?>
							<section id="login-dialog" class="clearfix">
								<h4>
									Reset Password
								</h4>
								<div class="inner">
									<p>
										<span>Please enter your e-mail address or username.</span>
										<br />
										<span>An e-mail will be sent to you that will allow you to reset your password.</span>
									</p>
									<p class="form-field">
										<label for="username-email">Username or E-mail</label>
										<input type="text" id="username-email" name="usernameEmail" />
									</p>
									<div class="standout-space">
										<button id="lost-password-btn" class="rb-btn light-gray-btn" name="submitLostPassword">Reset Password</button>
									</div>
									<div class="vert-space bottom-links">
										<a href="<?php echo HTTP_LOGIN; ?>">Back to Login</a>
									</div>
								</div>
							</section>
							<?php endif; ?>
						<?php endif; ?>
					</section>
				</div>
			</div>
		</section>
	</form>
	<section id="modal-windows">
		<?php if(!isset($_GET['lostpass'])): ?>

		<?php elseif(isset($_GET['lostpass'])): ?>
		<div id="pwd-reset-dialog" title="Password Reset">
			<form id="pwd-reset-form" action=<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
				<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />

			</form>
		</div>
		<?php endif; ?>
	</section>
<?php siteCleanup(); ?>
<?php displayFooter(); ?>
