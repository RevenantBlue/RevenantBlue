<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/forums/forums-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Section | Forums';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/forums.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/forums.min.js"></script>
<?php endif; ?>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" type="text/css" />
<?php loadMainCss(); ?>
</head>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<?php if(isset($forumSection)): ?>
				<span>Edit Forum Section</span>
				<?php else: ?>
				<span>New Forum Section</span>
				<?php endif; ?>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>forums/section/new">
						<span class="ui-icon ui-icon-plus"></span>
						New Forum Section
					</a>
				</li>
				<?php if(!empty($optionsForPage)): ?>
				<li>
					<a href="#">Screen Options</a>
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
				<?php endif; ?>
				<li><a href="#">Help</a></li>
				<li>
					<a href="#">About</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($forumSection)): ?>
		<?php displayBreadCrumbs($forumSection['forum_title'], HTTP_ADMIN . 'forums/' . $forumSection['id'], array('title' => 'Forums', 'url' => HTTP_ADMIN . 'forums')); ?>
		<?php else: ?>
		<?php displayBreadCrumbs('Add New Section', HTTP_ADMIN . 'forums/section/new', array('title' => 'Forums', 'url' => HTTP_ADMIN . 'forums')); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix option-102" <?php if(!in_array(102, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<?php if(aclVerify('backend forum admin')): ?>
					<li id="toolbar-save-forum-section">
						<a href="#">
							<span class="ui-icon ui-icon-disk"></span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close-forum-section">
						<a href="#">
							<span class="ui-icon ui-icon-close"></span>
							<span class="toolbar-text">Close</span>
						</a>
					</li>
					<?php endif; ?>
					<li id="toolbar-options">
						<a id="options" href="#">
							<span class="ui-icon ui-icon-gear"> </span>
							<span class="toolbar-text">Options</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help">
						<a href="#">
							<span class="ui-icon ui-icon-help"> </span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="clearfix"> </div>
		<div id="element-box">
			<div id="content-padding">
				<form id="adminForm" action="" method="post">
					<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					<input type="hidden" id="forum-action" name="forumAction" />
					<?php if(isset($forumSection)): ?>
					<div class="profile-main">
						<div class="profile-padding">
							<div class="element">
								<div class="element-top">Title</div>
								<div class="element-body">
									<input type="hidden" name="id" value="<?php echo hsc($forumSection['id']);?>" />
									<input id="title" type="text" name="title" size="40" placeholder="Enter section name here"
									<?php if(isset($_SESSION['section']->title)): ?>
										value="<?php echo hsc($_SESSION['section']->title); ?>"
									<?php else: ?>
										value="<?php echo hsc($forumSection['forum_title']) . '"'; ?>"
									<?php endif; ?> />
								</div>
							</div>
							<table id="forum-perms-table">
								<tr>
									<th class="divider" colspan="2">Section Role Permissions</th>
								</tr>
								<tr>
									<th class="top-left">
										Select All
									</th>
									<td>
										<input type="checkbox" id="check-view-forum" class="check-all-forum-perms" />
									</td>
								</tr>
								<?php foreach($roles as $role): ?>
								<tr>
									<th id="role-<?php echo hsc($role['id']); ?>">
										<?php echo hsc($role['name']); ?>
									</th>
									<td class="forum-perm">
										<label class="center-label forum-perm-label">
											<input type="hidden" name="rolePerms[<?php echo hsc($role['id']); ?>][<?php echo hsc($viewPerm['forum_permission_alias']); ?>]" value="off" />
											<input type="checkbox" id="view-forum-<?php echo hsc($role['id']); ?>" class="forum-perm-toggle center-toggle forum-perm-view-forum" name="rolePerms[<?php echo hsc($role['id']); ?>][<?php echo hsc($viewPerm['forum_permission_alias']); ?>]"
											<?php foreach($rolePermissions as $rolePerm): ?>
												<?php if($rolePerm['role_id'] === $role['id'] && $rolePerm['perm_id'] === $viewPerm['id']): ?>
												   checked="checked"
												<?php endif; ?>
											<?php endforeach; ?>
											/>
											View Forum
										</label>
									</td>
								</tr>
								<?php endforeach; ?>
							</table>
						</div>
					</div>
					<div class="profile-details">
						<div class="detail-padding">
							<div class="element">
								<div class="element-top">Basic Settings</div>
								<div class="element-body">
									<table class="panel-tbl-std">
										<tr>
											<th>
												Alias
											</th>
											<td>
												<input type="text" name="alias" class="profile-text" 
												<?php if(isset($_SESSION['section']->alias)): ?>
													value="<?php echo hsc($_SESSION['section']->alias); ?>"
												<?php else: ?>
													value="<?php echo hsc($forumSection['forum_alias']); ?>"
												<?php endif; ?>
												/>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
					<?php else: ?>
					<div class="profile-main">
						<div class="profile-padding">
							<div class="element">
								<div class="element-top">Section Name</div>
								<div class="element-body">
									<input id="title" type="text" name="title" size="40" placeholder="Enter section name here"
									<?php if(isset($forumSectionValidate->title)): ?>
										value="<?php echo hsc($forumSectionValidate->title); ?>"
									<?php endif; ?> />
								</div>
							</div>
							<table id="forum-perms-table">
								<tr>
									<th class="divider" colspan="2">Section Role Permissions</th>
								</tr>
								<tr>
									<th class="top-left">
										Select All
									</th>
									<td>
										<input type="checkbox" id="check-view-forum" class="check-all-forum-perms" />
									</td>
								</tr>
								<?php foreach($roles as $role): ?>
								<tr>
									<th id="role-<?php echo hsc($role['id']); ?>">
										<?php echo hsc($role['name']); ?>
									</th>
									<td class="forum-perm">
										<label class="center-label forum-perm-label">
											<input type="hidden" name="rolePerms[<?php echo hsc($role['id']); ?>][<?php echo hsc($viewPerm['forum_permission_alias']); ?>]" value="off" />
											<input type="checkbox" id="view-forum-<?php echo hsc($role['id']); ?>" class="forum-perm-toggle center-toggle forum-perm-view-forum" name="rolePerms[<?php echo hsc($role['id']); ?>][<?php echo hsc($viewPerm['forum_permission_alias']); ?>]" />
											View Forum
										</label>
									</td>
								</tr>
								<?php endforeach; ?>
							</table>
						</div>
					</div>
					<div class="profile-details">
						<div class="detail-padding">
							<div class="element">
								<div class="element-top">Basic Settings</div>
								<div class="element-body">
									<table class="panel-tbl-std">
										<tr>
											<th>
												Alias
											</th>
											<td>
												<input type="text" name="alias" class="profile-text" 
												<?php if(isset($_SESSION['section']->alias)): ?>
													value="<?php echo hsc($_SESSION['section']->alias); ?>"
												<?php endif; ?>
												/>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</form>
			</div>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
