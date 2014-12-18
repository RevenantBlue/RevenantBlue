<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/users/users-c.php';
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
				<?php if(isset($_GET['id'])): ?>
					<span>Edit User</span>
				<?php else: ?>
					<span>New User</span>
				<?php endif; ?>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="#" id="action-save-user">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-new-user">Save and New</a>
				</li>
				<li>
					<a href="#" id="action-save-close-user">Save and Close</a>
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
					<a href="#" id="action-close-article">
						<span class="ui-icon ui-icon-close"></span>
						Close
					</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($userData)): ?>
		<?php displayBreadCrumbs($userData['username'], HTTP_ADMIN . 'users/' . $userData['id'] . '/edit', array('title' => 'User Profile', 'url' => HTTP_ADMIN . 'users/new')); ?>
		<?php else: ?>
		<?php displayBreadCrumbs(false, false, array('title' => 'New User', 'url' => HTTP_ADMIN . 'users/new')); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="option-94" <?php if(!in_array(94, $userOptions)): ?>style="display: none"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save-user">
						<a href="#">
							<span class="ui-icon ui-icon-disk"> </span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close-user">
						<a href="<?php echo HTTP_ADMIN; ?>users/">
							<span class="ui-icon ui-icon-close"> </span>
							<span class="toolbar-text">Close</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-user">
						<a href="#">
							<span class="ui-icon ui-icon-help"> </span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="clearfix"></div>
		<div id="element-box">
			<div id="content-padding">
				<form id="adminForm" action="" method="post">
					<input type="hidden" id="user-action" name="userAction" />
					<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					<div class="element">
						<div class="element-top">
							<?php if(isset($userData)): ?>
							<h3 class="element-head">Edit User - <?php echo hsc($userData['username']);?></h3>
							<?php else: ?>
							<h3 class="element-head">New User</h3>
							<?php endif; ?>
						</div>
						<div class="element-body">
							<?php if(isset($userData['id'])): ?>
							<input type="hidden" name="userId" value="<?php echo hsc($userData['id']); ?>" />
							<?php endif; ?>
							<table class="overview no-border overview-form left width100pcnt">
								<tr>
									<th class="width20pcnt">
										<label for="firstName">First Name</label>
									</th>
									<td>
										<input type="text" size="40" id="firstName" name="firstName"
											<?php if(isset($_SESSION['user'])): ?>
											   value="<?php echo hsc($_SESSION['user']->firstName); ?>"
											<?php elseif(isset($userData['first_name'])): ?>
											   value="<?php echo hsc($userData['first_name']);?>"
											<?php endif; ?>
										/>
									</td>
								</tr>
								<tr>
									<th>
										<label for="lastName">Last Name</label>
									</th>
									<td>
										<input type="text" size="40" id="lastName" name="lastName"
											<?php if(isset($_SESSION['user']->lastName)): ?>
											   value="<?php echo hsc($_SESSION['user']->lastName); ?>"
											<?php elseif(isset($userData['last_name'])): ?>
											   value="<?php echo hsc($userData['last_name']);?>"
											<?php endif; ?>
										/>
									</td>
								</tr>
								<tr>
									<th>
										<label for="username">Username</label>
									</th>
									<td>
										<?php if(aclVerify('change own username') && isset($userData['id']) && $_SESSION['userId'] != $userData['id']): ?>
										<input type="text" size="40" id="username" name="username" value="<?php echo hsc($userData['username']);?>" />
										<?php elseif(isset($userData['id']) && $_SESSION['userId'] == $userData['id']): ?>
										<input type="hidden" name="username" value="<?php echo hsc($userData['username']); ?>" />
										<input type="text" size="40" id="username" name="username" value="<?php echo hsc($userData['username']);?>" disabled="disabled" />
										<?php elseif(isset($userData['username'])): ?>
										<input type="text" size="40" id="username" name="username" value="<?php echo hsc($userData['username']);?>" />
										<?php else: ?>
										<input type="text" size="40" id="username" name="username" />
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
										<input type="password" id="confirm-password" name="confirmPassword" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="email">Email</label>
									</th>
									<td>
										<input type="text" size="40" id="email" name="email"
											<?php if(isset($_SESSION['user']->email)): ?>
											   value="<?php echo hsc($_SESSION['user']->email); ?>"
											<?php elseif(isset($userData['email'])): ?>
											   value="<?php echo hsc($userData['email']); ?>"
											<?php endif; ?>
										/>
									</td>
								</tr>
								<?php if(isset($userData)): ?>
								<tr>
									<th>
										<label for="regDate">Registration Date</label>
									</th>
									<td>
										<pre><?php if(isset($userData['date_joined'])): echo hsc($userData['date_joined']); endif; ?></pre>
									</td>
								</tr>
								<tr>
									<th>
										<label for="lastVisitDate">Last Visit Date</label>
									</th>
									<td>
										<pre><?php if(isset($userData['last_login'])): echo hsc($userData['last_login']); endif; ?></pre>
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<th class="vAlignTop">
										<label for="systemEmail">Receive System Email</label>
									</th>
									<td>
										<ul>
											<li>
												<label for="systemEmailTrue" class="center-label">
													<input type="radio" id="systemEmailTrue" class="center-toggle" name="systemEmail" value="1" 
														<?php if(isset($_SESSION['user']->systemEmail) && $_SESSION['user']->systemEmail == true): ?>
															checked="checked"
														<?php elseif(isset($userData['system_email']) && $userData['system_email'] == true): ?>
														   checked="checked" 
														<?php endif; ?> 
													/>
													Yes
												</label>
											</li>
											<li>
												<label for="systemEmailFalse" class="center-label">
													<input type="radio" id="systemEmailFalse" class="center-toggle" name="systemEmail" value="0"
														<?php if(isset($_SESSION['system_email']) && $_SESSION['system_email'] == false): ?>
														   checked="checked"
														<?php elseif(isset($userData['system_email']) && $userData['system_email'] == false): ?>
														   checked="checked"
														<?php elseif(!isset($userdata)): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</li>
										</ul>
									</td>
								</tr>
								<tr>
									<th class="vAlignTop">
										<label for="blockUser">Block User</label>
									</th>
									<td>
										<ul>
											<li>
												<label for="enabledTrue" class="center-label">
													<input type="radio" id="enabledTrue" class="center-toggle" name="enabled" value="0" 
														<?php if(isset($_SESSION['user']->enabled) && $_SESSION['user']->enabled == false): ?>
														   checked="checked"
														<?php elseif(isset($userData['enabled']) && $userData['enabled'] == false): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</li>
											<li>
												<label for="enabledFalse" class="center-label">
													<input type="radio" id="enabledFalse" class="center-toggle" name="enabled" value="1" 
														<?php if(isset($_SESSION['user']->enabled) && $_SESSION['user']->enabled == false): ?>
														   checked="checked"
														<?php elseif(isset($userData['enabled']) && $userData['enabled'] == true): ?>
														   checked="checked"
														<?php elseif(!isset($userData)): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</li>
										</ul>
									</td>
								</tr>
								<tr>
									<th class="vAlignTop">
										Roles
									</th>
									<td>
										<ul id="rolesList">
										<?php foreach($roles as $role): ?>
											<?php if(isset($userRoles) && in_array($role['id'], $userRoles) && $role['name'] !== 'Anonymous User'): ?>
											<li>
												<label class="center-label">
													<input type="hidden" name="roles[<?php echo hsc($role['id']); ?>]" value="off" />
													<input type="checkbox" name="roles[<?php echo hsc($role['id']); ?>]" class="center-toggle" checked="checked" />
													<?php echo hsc($role['name']); ?>
												</label>
											</li>
											<?php elseif($role['name'] === 'Member'): ?>
											<li>
												<label class="center-label">
													<input type="hidden" name="roles[<?php echo hsc($role['id']); ?>]" value="on" />
													<input type="checkbox" name="roles[<?php echo hsc($role['id']); ?>]" class="center-toggle" disabled="disabled"
														   checked="checked"
													/>
													<?php echo hsc($role['name']); ?>
												</label>
											</li>
											<?php elseif($role['name'] !== 'Anonymous User'): ?>
											<li>
												<label class="center-label">
													<input type="hidden" name="roles[<?php echo hsc($role['id']); ?>]" value="off" />
													<input type="checkbox" name="roles[<?php echo hsc($role['id']); ?>]" class="center-toggle" />
													<?php echo hsc($role['name']); ?>
												</label>
											</li>
											<?php endif; ?>
										<?php endforeach; ?>
										</ul>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
