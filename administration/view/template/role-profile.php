<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/roles/roles-c.php';
$title = 'Roles';
require_once 'head.php';
require_once 'ui.php';
loadJqueryUi();
loadMainJs();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/roles.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/roles.min.js"></script>
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
				<span>Create Role</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>roles/new" id="action-new-role">
						<span class="ui-icon ui-icon-plus"></span>
						New Role
					</a>
				</li>
				<li>
					<a href="#" id="action-save-role">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-new-role">Save and New</a>
				</li>
				<li>
					<a href="#" id="action-save-close-role">Save and Close</a>
				</li>
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
				<li><a href="#">Help</a></li>
				<li>
					<a href="#">About</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(false, false, array('title' => 'Create Role', 'url' => HTTP_ADMIN . 'users/roles/new')); ?>
		<div id="toolbar-box" class="clearfix option-92" <?php if(!in_array(92, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save-role">
						<a href="#">
							<span class="ui-icon ui-icon-disk"></span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close-role">
						<a href="<?php echo HTTP_ADMIN . 'users/roles'; ?>">
							<span class="ui-icon ui-icon-close"></span>
							<span class="toolbar-text">Close</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-role">
						<a href="#">
							<span class="ui-icon ui-icon-help"></span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
				<div class="clear"></div>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<div class="clear"></div>
		<div id="element-box">
			<div id="content-padding">
				<form id="adminForm" action="" method="post">
					<div class="element">
						<div class="element-top">
							<h3 class="element-head">Role Name</h3>
						</div>
						<div class="element-body">
							<div class="element-body-content">
								<ul>
									<li>
										<input type="hidden" id="role-action" name="roleAction" value="" />
										<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo  hsc($csrfToken); ?>" />
										<input type="text" class="normal-text" id="name" name="name" value="<?php if(isset($newUser->name)) echo hsc($newUser->name);?>" />
									</li>
								</ul>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
