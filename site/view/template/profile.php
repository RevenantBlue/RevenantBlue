<?php
namespace RevenantBlue\Site;

require_once DIR_APPLICATION . 'controller/users/users-c.php';
$title = 'Profile | ';
require_once 'ui.php';
require_once 'head.php';
loadJqueryValidation();
?>
<?php loadSiteCss(); ?>
</head>
<body>
	<form id="main-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
		<?php displayHeader(); ?>
		<?php echo $sideNav; ?>
		<section id="main">
			<div id="dialogs-container"></div>
			<input type="hidden" id="formkey" name="formkey" value="<?php echo hsc($formKey); ?>" />
			<input type="hidden" id="main-form-action" name="action" />
			<div id="main-outer" class="main-box">
				<div id="main-inner">
					<?php displayNotifications(); ?>
					<?php if(!empty($_SESSION['username']) && isset($userData)): ?>
					<?php displayBreadCrumbs(); ?>
					<section id="profile" class="page-section">
						<h1><?php echo hsc($userData['username']); ?></h1>
						<div>
							<p id="profile-notifications" class="notifications" style="display: none;"></p>
							<ul>
								<li>
									<label for="email">Email Address</label>
									<input type="email" id="email" value="<?php echo hsc($userData['email']); ?>" />
								</li>
								<li>
									<label for="merc-corp">Merc Corporation</label>
									<input type="text" id="merc-corp" value="<?php echo hsc($userProfile['corp']); ?>"/>
								</li>
								<li>
									<label for="house-affiliation">House Affiliation</label>
									<select id="house-affiliation">
										<?php foreach($greatHouses as $houseName => $houseDesc): ?>
										<option value="<?php echo hsc($houseName); ?>" <?php if($userProfile['affiliation'] === $houseName): ?>selected="selected"<?php endif; ?>>
											<?php echo hsc($houseName . ' (' . $houseDesc . ')'); ?>
										</option>
										<?php endforeach; ?>
									</select>
								</li>
								<li>
									<label for="pref-class">Preferred Weight Class</label>
									<select id="pref-class">
										<?php foreach($weights as $weight): ?>
										<option value="<?php echo hsc($weight); ?>" <?php if($userProfile['prefClass'] === $weight): ?>selected="selected"<?php endif; ?>>
											<?php echo hsc($weight); ?>
										</option>
										<?php endforeach; ?>
									</select>
								</li>
								<li>
									<label for="pref-mech">Preferred Mech Chassis</label>
									<select id="pref-mech">
										<?php foreach($mechs as $mech): ?>
										<option value="<?php echo hsc($mech); ?>" <?php if($userProfile['prefMech'] === $mech): ?>selected="selected"<?php endif; ?>>
											<?php echo hsc($mech); ?>
										</option>
										<?php endforeach; ?>
									</select>
								</li>
							</ul>
						</div>
						<div>
							<button id="save-profile">Save</button>
						</div>
					</section>
					<?php else: ?>
						<?php if(isset($_GET['verify'])): ?>
						<?php if(isset($emailVerified) && isset($accountActivated)): ?>
						<div>
							<p>Your account has been activated.</p>
							<p>
								<a href="<?php echo HTTP_SERVER . 'login'; ?>">Login to your account.</a>
							</p>
						</div>
						<?php endif; ?>
						<?php elseif(isset($_GET['pwreset']) && !empty($pwReset)): ?>
						<h3 class="clean-header">Reset Password</h3>
						<input type="hidden" name="id" value="<?php echo hsc($pwReset['user_id']); ?>" />
						<input type="hidden" name="code" value="<?php echo hsc($pwReset['user_code']); ?>" />
						<label>
							<span>Password</span>
							<input type="password" name="newPassword" />
						</label>
						<label>
							<span>Confirm password</span>
							<input type="password" name="confirmNewPassword" />
						</label>
						<input type="submit" name="submitPasswordReset" value="Reset Password" />
						<?php elseif(isset($_GET['approval']) && !empty($approval)): ?>
						<h3 class="clean-header">Enter the password for your account.</h3>
						<input type="hidden" name="id" value="<?php echo hsc($approval['user_id']); ?>" />
						<input type="hidden" name="code" value="<?php echo hsc($approval['user_code']); ?>" />
						<label>
							<span>Password</span>
							<input type="password" name="newPassword" />
						</label>
						<label>
							<span>Confirm password</span>
							<input type="password" name="confirmNewPassword" />
						</label>
						<input type="submit" name="submitPasswordReset" value="Reset Password" />
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</form>
	<section id="modal-windows">
		<?php loadAccountModals(); ?>
	</section>    
<?php displayFooter(); ?>
