<?php
namespace RevenantBlue\Admin;
use RevenantBlue\TableSorter;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/roles/roles-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Roles';
require_once 'head.php';
require_once 'ui.php';
loadJqueryUi();
loadMainJs();
loadTableDragNDrop();
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
	<?php if(!isset($_GET['role'])): ?>
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<span>Roles</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>users/roles/new" id="action-new-role">
						<span class="ui-icon ui-icon-plus"></span>
						New Role
					</a>
				</li>
				<li>
					<a href="#" id="action-edit-role">Edit</a>
				</li>
				<li>
					<a href="#" id="action-delete-role">
						<span class="ui-icon ui-icon-trash"></span>
						Delete
					</a>
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
			<div class="search-wrap">
				<form id="user-search-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="text" id="role-search" class="med-small-text overview-search" name="roleToSearch" placeholder="Search by role name" />
					</div>
					<div>
						<button class="sprites search-sprite sprite-space-16" name="submitRoleToSearch" type="submit" value="1"></button>
					</div>
				</form>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(false, false, array('title' => 'Roles', 'url' => HTTP_ADMIN . 'users/roles')); ?>
		<div id="toolbar-box" class="clearfix option-91" <?php if(!in_array(91, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-new-role">
						<a href="<?php echo HTTP_ADMIN; ?>users/roles/new">
							<span class="ui-icon ui-icon-plus"></span>
							<span class="toolbar-text">New</span>
						</a>
					</li>
					<li id="toolbar-edit-role">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"></span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-delete-role">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-text">Delete</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-options">
						<a href="#">
							<span class="ui-icon ui-icon-gear"></span>
							<span class="toolbar-text">Options</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help">
						<a href="#">
							<span class="ui-icon ui-icon-help"></span>
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
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="role-action" name="roleAction" />
				   </div>
				   <div class="clearfix"></div>
					<table id="overview">
						<thead>
							<?php if(!isset($_GET['search'])): ?>
							<tr class="overview-top">
								<th class="width1pcnt">
									<input id="selectAll" type="checkbox" class="overview-check-all" />
								</th>
								<th class="width50pcnt left">
									<a class="link" href="<?php echo TableSorter::sortLink('admin/users/roles', '', 'name'); ?>">
										Role Name <?php echo TableSorter::displaySortIcon('name'); ?>
									</a>
								</th>
								<th class="width10pcnt">Number of Users</th>
								<th class="width5pcnt">
									Rank
								</th>
								<th class="width5pcnt">
									<a class="link" href="<?php echo TableSorter::sortLink('admin/users/roles', '', 'id'); ?>">
										Id <?php echo TableSorter::displaySortIcon('id'); ?>
									</a>
								</th>
							</tr>
							<?php elseif(isset($_GET['search'])): ?>
							<tr class="overview-top">
								<th class="width1pcnt">
									<input id="selectAll" type="checkbox" class="overview-check-all" />
								</th>
								<th class="width50pcnt left">
									<a class="link" href="<?php echo TableSorter::sortLink('admin/users/roles', 'search', 'name'); ?>">
										Role Name <?php echo TableSorter::displaySortIcon('name'); ?>
									</a>
								</th>
								<th class="width10pcnt">
									Number of Users
								</th class="width5pcnt">
								<th>
									Rank
								</th>
								<th class="width5pcnt">
									<a class="link" href="<?php echo TableSorter::sortLink('admin/users/roles', 'search', 'id'); ?>">
										Id <?php echo TableSorter::displaySortIcon('id'); ?>
									</a>
								</th>
							</tr>
							<?php endif; ?>
						</thead>
						<tbody>
							<?php foreach($roles as $num => $role): ?>
							<tr id="role-<?php echo hsc($role['id']); ?>" <?php if($role['name'] === 'Banned'): ?>class="nodrag nodrop"<?php endif; ?>>
								<td>
									<input id="cb-<?php echo hsc($num); ?>" type="checkbox" class="overview-check" name="roleChecked[]" value="<?php echo hsc($role['id']); ?>" />
								</td>
								<td class="left">
									<a href="<?php echo HTTP_ADMIN . 'users/roles/' . $role['id'];?>/">
										<?php echo hsc($role['name']); ?>
									</a>
									<?php if(in_array($role['name'], $acl->lockedRoles)): ?>
									<em> (locked)</em>
									<?php endif; ?>
								</td>
								<td>
									<?php echo hsc($users->getNumberOfUsersByRoleId($role['id'])); ?>
								</td>
								<td class="role-rank" data-id="<?php echo hsc($role['id']); ?>">
									<?php echo hsc($role['rank']); ?>
								</td>
								<td>
									<?php echo hsc($role['id']); ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
						<?php if(empty($roles)): ?>
						<tbody>
							<tr class="overview-row1">
								<td colspan="10"><p>Your search did not match any roles.</p></td>
							</tr>
						</tbody>
						<?php endif; ?>
					</table>
				</form>
			</div>
		</div>
	<?php elseif(isset($_GET['role']) && isset($permissions)): ?>
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<span>Role Permissions</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="#" id="action-save-role-perms">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-close-role-perms">Save and Close</a>
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
		<?php displayBreadCrumbs($role['name'], HTTP_ADMIN . 'users/roles/' . $_GET['role'], array('title' => 'Roles', 'url' => HTTP_ADMIN . 'users/roles')); ?>
		<div id="toolbar-box" class="clearfix option-95" <?php if(!in_array(95, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save-role-perms">
						<a href="#">
							<span class="ui-icon ui-icon-check"> </span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close-role-perms">
						<a href="<?php echo HTTP_ADMIN; ?>users/roles/">
							<span class="ui-icon ui-icon-close"></span>
							<span class="toolbar-text">Close</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-role-perms">
						<a href="#">
							<span class="ui-icon ui-icon-help"></span>
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
					<div>
						<input type="hidden" id="role-perms-action" name="rolePermsAction" />
						<input type="hidden" id="roleId" name="roleId" value="<?php echo hsc($role['id']);?>" />
						<input type="hidden" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					</div>
					<div class="element fltlft">
						<div class="element-top">
							<h3 class="element-head">
								<span>Role Name</span>
							</h3>
						</div>
						<div class="element-body">
							<div class="element-body-content">
								<input type="text" size="30" name="roleName" value="<?php echo hsc($role['name']); ?>"
								<?php if($_GET['role'] == "Anonymous User" || $role['name'] == 'Authenticated User' || $role['name'] == 'Administrator'): ?>
								disabled="disabled"
								<?php endif; ?>
								/>
							</div>
						</div>
					</div>
					<div class="fltrght top-space">
						<button id="add-permission" class="rb-btn light-gray-btn top-space">Add permission</button>
					</div>
					<div class="clear"></div>
					<div class="expand-collapse">
						<a id="expand-role-table" href="#">Expand All - </a>
						<a id="collapse-role-table" href="#">Collapse All</a>
					</div>
					<div class="element">
						<div class="element-top"><?php echo hsc($role['id']); ?> Permissions</div>
						<div class="element-body">
							<table class="overview role-perms-list element-table width100pcnt">
								<?php foreach($modules as $num => $module): ?>
								<?php if($module['order_of_item'] == 1): ?>
								<tbody>
									<tr class="role-perms-row">
										<td class="module-role" colspan="2">
											<span id="<?php echo hsc(strtolower(str_replace(' ', '', $module['module_group']))); ?>" class="icon-20-collapse"> </span>
											<h3><?php echo hsc(ucwords($module['module_group'])); ?></h3>
										</td>
									</tr>
								</tbody>
								<tbody class="<?php echo hsc(strtolower(str_replace(' ', '', $module['module_group']))); ?> role-head">
									<tr class="role-perms-row">
										<td class="subhead permission">Permissions</td>
										<td class="subhead permission-check width30pcnt">Allow</td>
										<td class="subhead width50pcnt">Description</td>
									</tr>
								</tbody>
								<?php endif; ?>
								<tbody class="<?php echo hsc(strtolower(str_replace(' ', '', $module['module_group']))); ?> role-permissions">
									<tr class="role-perms-row">
										<td class="permission">
											<?php echo hsc($module['name']);?>
										</td>
										<td class="permission-check">
											<?php if(isset($permissions[$module['id']]) && $permissions[$module['id']] == true): ?>
												<?php if(array_key_exists($module['id'], $acl->lockedAdminPerms) && $role['id'] == 2): ?>
												<input type="hidden" name="perm[<?php echo hsc($module['id']);?>]" value="on" />
												<input type="checkbox" name="perm[<?php echo hsc($module['id']);?>]" checked="checked" disabled="disabled" />
												<?php else: ?>
												<input type="hidden" name="perm[<?php echo hsc($module['id']);?>]" value="off" />
												<input type="checkbox" name="perm[<?php echo hsc($module['id']);?>]" checked="checked" />
												<?php endif; ?>
											<?php else: ?>
											<input type="hidden" name="perm[<?php echo hsc($module['id']);?>]" value="off" />
											<input type="checkbox" name="perm[<?php echo hsc($module['id']);?>]" />
											<?php endif; ?>
										</td>
										<td>
											<em class="permission-desc"><?php echo hsc($module['description']); ?></em>
											<?php if($module['security_risk'] == TRUE): ?>
											<div>
												<em class="permission-warning">Warning: Give to trusted roles only; this permission has security implications.</em>
											</div>
											<?php endif; ?>
										</td>
									</tr>
								</tbody>
								<?php endforeach; ?>
								<tbody>
									<tr id="role-perms-foot">
										<td colspan="4"></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div id="add-permission-dialog">
			<div class="inner">
				<form id="add-permission-form" class="std-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<input type="hidden" name="csrfToken" value="<?php echo $csrfToken; ?>" />
					<input type="hidden" name="permissionAction" value="add-permission" />
					<div class="form-item">
						<label for="module-name">
							Module Name
						</label>
						<input type="text" id="module-name" name="name" class="large-text" />
					</div>
					<div class="form-item">
						<label for="module-group">
							Module Group
						</label>
						<input type="text" id="module-group" name="group" class="large-text" />
					</div>
					<div class="form-item">
						<label for="module-description">
							Module Description
						</label>
						<input type="text" id="module-description" name="description" class="large-text" />
					</div>
					<div class="form-item">
						<label for="module-security-risk" class="center-label">
							Security Risk
							<input type="hidden" name="securityRisk" value="0" />
							<input type="checkbox" id="module-security-risk" name="securityRisk" class="center-toggle" />
						</label>
					</div>
				</form>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
<?php displayFooter(); ?>
