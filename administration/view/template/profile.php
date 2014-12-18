<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/users/profile-c.php';
$title = isset($userData) ? 'Users | ' . hsc($userData['username']) : 'Users | New User';
require_once 'head.php';
require_once 'ui.php';
loadJqueryUi();
loadMainJs();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/users.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/users.min.js"></script>
<?php endif; ?>
<?php loadMainCss(); ?>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" type="text/css" />
</head>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<?php if(isset($userData)): ?>
					<span><?php echo hsc($_SESSION['username']); ?>'s Profile</span>
				<?php endif; ?>
				<div class="menu-darr"></div>
			</a>
			<a class="fltrght" id="toggleSidePanel" href="#">+/- Toggle side panel</a>
			<ul id="action-menu">
				<li>
					<a href="#" id="action-save-profile">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-close-profile">Save and Close</a>
				</li>
				<li>
					<a href="#">
						<span class="ui-icon ui-icon-gear"></span>
						Screen Options
					</a>
					<ul>
						<?php foreach($optionsForPage as $optionForPage): ?>
						<li>
							<a id="screen-option-<?php echo hsc($optionForPage['id']); ?>" class="screen-option action-no-close" href="#">
								<span class="<?php if(in_array($optionForPage['id'], $userOptions)): ?>ui-icon ui-icon-check<?php endif; ?>"></span>
								<?php echo hsc($optionForPage['option_name']); ?>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
				</li>
				<li>
					<a href="#">Help</a>
				</li>
				<li>
					<a href="#">
						About
					</a>
				</li>
				<li>
					<a href="#" id="action-close-profile">
						<span class="ui-icon ui-icon-close"></span>
						Close
					</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($userData)): ?>
		<?php displayBreadCrumbs($userData['username'], HTTP_ADMIN . 'profile/' . $userData['username']); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix option-98" <?php if(!in_array(98, $userOptions)): ?>style="display: none"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save-profile">
						<a href="#">
							<span class="ui-icon ui-icon-disk"> </span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close-profile">
						<a href="<?php echo HTTP_ADMIN; ?>">
							<span class="ui-icon ui-icon-close"> </span>
							<span class="toolbar-text">Close</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-profile">
						<a href="#">
							<span class="ui-icon ui-icon-help"> </span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="clear"></div>
		<div id="element-box">
			<div id="content-padding">
				<form id="adminForm" action="" method="post">
					<?php if(isset($userData)): ?>
					<div class="element">
						<div class="element-top"><?php echo hsc($userData['username']); ?>'s Profile</div>
						<div class="element-body">
							<table class="overview no-border overview-form left width100pcnt">
								<tr>
									<th>
										<label for="firstName">First Name</label>
									</th>
									<td>
										<input type="text" size="40" id="firstName" name="firstName" value="<?php echo hsc($userData['first_name']);?>" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="lastName">Last Name</label>
									</th>
									<td>
										<input type="text" size="40" id="lastName" name="lastName" value="<?php echo hsc($userData['last_name']);?>" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="username">Username</label>
									</th>
									<td>
										<?php if(aclVerify('change own username') && $_SESSION['userId'] != $userData['id']): ?>
										<input type="text" size="40" id="username" name="username" value="<?php echo hsc($userData['username']);?>" />
										<?php elseif($_SESSION['userId'] == $userData['id']): ?>
										<input type="hidden" name="username" value="<?php echo hsc($userData['username']); ?>" />
										<input type="text" size="40" id="username" name="username" value="<?php echo hsc($userData['username']);?>" disabled="disabled" />
										<?php else: ?>
										<input type="text" size="40" id="username" name="username" value="<?php echo hsc($userData['username']);?>" />
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<th>
										<label for="password">Password</label>
									</th>
									<td>
										<input type="password" id="password" name="password" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="confirmPassword">Confirm Password</label>
									</th>
									<td>
										<input type="password" id="confirmPassword" name="confirmPassword" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="email">Email</label>
									</th>
									<td>
										<input type="text" size="40" id="email" name="email" value="<?php echo $userData['email']; ?>" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="regDate">Registration Date</label>
									</th>
									<td>
										<?php echo hsc($userData['date_joined']);?>
									</td>
								</tr>
								<tr>
									<th>
										<label for="lastVisitDate">Last Visit Date</label>
									</th>
									<td>
										<?php echo hsc($userData['last_login']);?>
									</td>
								</tr>
								<tr>
									<th class="vAlignTop">
										<label for="systemEmail">Receive System Email</label>
									</th>
									<td>
										<ul>
											<li>
												<label for="systemEmailTrue" class="center-label">
													<input type="radio" id="systemEmailTrue" class="center-toggle" name="systemEmail" value="1" <?php if($userData['system_email'] == true) echo 'checked="checked"'; ?> />
													Yes
												</label>
											</li>
											<li>
												<label for="systemEmailFalse" class="center-label">
													<input type="radio" id="systemEmailFalse" class="center-toggle" name="systemEmail" value="0" <?php if($userData['system_email'] == false) echo 'checked="checked"'; ?> />
													No
												</label>
											</li>
										</ul>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<?php endif; ?>
				</form>
			</div>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
