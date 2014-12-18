<?php
namespace RevenantBlue\Admin;
use RevenantBlue\TableSorter;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/users/users-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Users';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/users.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/users.min.js"></script>
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
				<span>Users</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>users/new">
						<span class="ui-icon ui-icon-plus"></span>
						New User
					</a>
				</li>
				<li>
					<a href="#" id="action-edit-user">Edit</a>
				</li>
				<li>
					<a href="#">Status</a>
					<ul>
						<li>
							<a href="#" id="action-activate-user">
								<span class="ui-icon ui-icon-check"></span>
								Activate
							</a>
						</li>
						<li>
							<a href="#" id="action-enable-user">
								<span class="ui-icon ui-icon-radio-on"></span>
								Enable
							</a>
						</li>
						<li>
							<a href="#" id="action-block-user">
								<span class="ui-icon ui-icon-radio-off"></span>
								Block
							</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#" id="action-delete-user">
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
						<input type="text" id="user-search" class="med-small-text overview-search" name="userToSearch" placeholder="Search by username" />
					</div>
					<div>
						<button class="sprites search-sprite sprite-space-16" name="submitUserSearch" type="submit" value="1"></button>
					</div>
				</form>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix clearfix option-85" <?php if(!in_array(85, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-new">
						<a href="<?php echo HTTP_ADMIN; ?>users/new/">
							<span class="ui-icon ui-icon-plus"> </span>
							<span class="toolbar-text">New</span>
						</a>
					</li>
					<li id="toolbar-edit-user">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"> </span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-activate-user">
						<a href="#">
							<span class="ui-icon ui-icon-check"></span>
							<span class="toolbar-text">Activate</span>
						</a>
					</li>
					<li id="toolbar-enable-user">
						<a href="#">
							<span class="ui-icon ui-icon-radio-on"></span>
							<span class="toolbar-text">Enable</span>
						</a>
					</li>
					<li id="toolbar-block-user">
						<a href="#">
							<span class="ui-icon ui-icon-radio-off"></span>
							<span class="toolbar-text">Block</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-delete-user">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-text">Delete</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-options-user">
						<a href="#">
							<span class="ui-icon ui-icon-gear"></span>
							<span class="toolbar-text">Options</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-user">
						<a href="#"><span class="ui-icon ui-icon-help"></span>
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
					<div class="element filter-menu fltlft">
						<div class="element-top">
							<h3 class="element-head">Filter By</h3>
						</div>
						<div class="element-body">
							<div class="element-body-content">
								<select name="userStatusFilter" class="user-filter" onChange="Javascript: CMS.submitButton('user', 'statusFilter');">
									<option selected="selected" disabled="disabled">--State--</option>
									<option value="1">Enabled</option>
									<option value="0">Disabled</option>
								</select>
								<select name="userActivationFilter" class="user-filter" onChange="Javascript: CMS.submitButton('user', 'activationFilter');">
									<option selected="selected" disabled="disabled">--Activation--</option>
									<option value="1">Activated</option>
									<option value="0">Deactivated</option>
								</select>
								<select name="userRoleFilter" class="user-filter" onChange="Javascript: CMS.submitButton('user', 'roleFilter');">
									<option selected="selected" disabled="disabled">--Role--</option>
									<?php foreach($roles as $role): ?>
									<?php if($role['name'] !== 'Anonymous User'): ?>
									<option value="<?php echo hsc($role['id']); ?>"><?php echo hsc($role['name']); ?></option>
									<?php endif; ?>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="user-action" name="userAction" />
					</div>
					<div class="links links-top"><?php echo $pager->menu; echo $pager->limitMenu;?></div>
					<div class="clearfix"></div>
					<table id="overview">
						<tr class="overview-top">
							<th class="width1pcnt">
								<input id="selectAll" type="checkbox" class="overview-check-all" />
							</th>
							<th class="left">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'search', 'username');?>">
								<?php elseif(isset($_GET['status'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'status', 'username');?>">
								<?php elseif(isset($_GET['activated'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'activated', 'username');?>">
								<?php elseif(isset($_GET['role'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'role', 'username');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', '', 'username');?>">
								<?php endif; ?>
								Username <?php echo TableSorter::displaySortIcon('username'); ?>
								</a>
							</th>
							<th class="width10pcnt" >
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'search', 'first_name');?>">
								<?php elseif(isset($_GET['status'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'status', 'first_name');?>">
								<?php elseif(isset($_GET['activated'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'activated', 'first_name');?>">
								<?php elseif(isset($_GET['role'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'role', 'first_name');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', '', 'first_name');?>">
								<?php endif; ?>
								First Name <?php echo TableSorter::displaySortIcon('first_name', 'asc'); ?>
								</a>
							</th>
							<th class="width10pcnt" >
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'search', 'last_name');?>">
								<?php elseif(isset($_GET['status'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'status', 'last_name');?>">
								<?php elseif(isset($_GET['activated'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'activated', 'last_name');?>">
								<?php elseif(isset($_GET['role'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'role', 'last_name');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', '', 'last_name');?>">
								<?php endif; ?>
								Last Name <?php echo TableSorter::displaySortIcon('last_name', 'asc'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'search', 'enabled');?>">
								<?php elseif(isset($_GET['status'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'status', 'enabled');?>">
								<?php elseif(isset($_GET['activated'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'activated', 'enabled');?>">
								<?php elseif(isset($_GET['role'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'role', 'enabled');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', '', 'enabled');?>">
								<?php endif; ?>
								Enabled <?php echo TableSorter::displaySortIcon('enabled'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'search', 'activated');?>">
								<?php elseif(isset($_GET['status'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'status', 'activated');?>">
								<?php elseif(isset($_GET['activated'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'activated', 'activated');?>">
								<?php elseif(isset($_GET['role'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'role', 'activated');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', '', 'activated');?>">
								<?php endif; ?>
								Activated <?php echo TableSorter::displaySortIcon('activated'); ?>
								</a>
							</th>
							<th class="width10pcnt">
								<span>Roles</span>
							</th>
							<th class="width15pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'search', 'email');?>">
								<?php elseif(isset($_GET['status'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'status', 'email');?>">
								<?php elseif(isset($_GET['activated'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'activated', 'email');?>">
								<?php elseif(isset($_GET['role'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'role', 'email');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', '', 'email');?>">
								<?php endif; ?>
								Email <?php echo TableSorter::displaySortIcon('email'); ?>
								</a>
							</th>
							<th class="width10pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'search', 'date_joined');?>">
								<?php elseif(isset($_GET['status'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'status', 'date_joined');?>">
								<?php elseif(isset($_GET['activated'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'activated', 'date_joined');?>">
								<?php elseif(isset($_GET['role'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'role', 'date_joined');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', '', 'date_joined');?>">
								<?php endif; ?>
								Date Joined <?php echo TableSorter::displaySortIcon('date_joined'); ?>
								</a>
							</th>
							<th class="width10pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'search', 'last_login');?>">
								<?php elseif(isset($_GET['status'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'status', 'last_login');?>">
								<?php elseif(isset($_GET['activated'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'activated', 'last_login');?>">
								<?php elseif(isset($_GET['role'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'role', 'last_login');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', '', 'last_login');?>">
								<?php endif; ?>
								Last Login <?php echo TableSorter::displaySortIcon('last_login'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'search', 'id');?>">
								<?php elseif(isset($_GET['status'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'status', 'id');?>">
								<?php elseif(isset($_GET['activated'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'activated', 'id');?>">
								<?php elseif(isset($_GET['role'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', 'role', 'id');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/users', '', 'id');?>">
								<?php endif; ?>
								Id <?php echo TableSorter::displaySortIcon('id'); ?>
								</a>
							</th>
						</tr>
						<?php if(isset($userList)): ?>
						<?php foreach($userList as $num=>$user): ?>
						<?php $class = ($num % 2) +1; ?>
						<tr class="overview-row<?php echo hsc($class);?>">
							<td>
								<input id="cb-<?php echo hsc($num); ?>" type="checkbox" name="userCheck[]" class="overview-check" value="<?php echo hsc($user['id']);?>" />
							</td>
							<td class="left">
								<a href="<?php echo HTTP_ADMIN; ?>users/<?php echo hsc($user['id']); ?>/edit"><?php echo hsc($user['username']);?></a>
							</td>
							<td>
								<?php echo hsc($user['first_name']);?>
							</td>
							<td>
								<?php echo hsc($user['last_name']);?>
							</td>
							<td id="enabled-<?php echo hsc($user['id']);?>">
								<div>
								<?php if($user['enabled'] == true): ?>
								<span class="icon-20-check icon-20-spacing"></span>
								<?php else: ?>
								<span class="icon-20-disabled icon-20-spacing"></span>
								<?php endif;?>
								</div>
							</td>
							<td id="activated-<?php echo hsc($user['id']); ?>">
								<?php if($user['activated'] == true): ?>
									<span class="icon-20-check icon-20-spacing"> </span>
								<?php else: ?>
									<div>
									<span class="icon-20-disabled icon-20-spacing"></span>
									</div>
								<?php endif;?>
							</td>
							<td>
								<ul>
								<?php $rolesForUser = $acl->getRolesForUser($user['id']); ?>
								<?php foreach($rolesForUser as $role): ?>
									<?php $role = $acl->getRoleById($role['role_id']); ?>
									<?php if($role['name'] != "Anonymous User"): ?>
										<li><?php echo hsc($role['name']); ?></li>
									<?php endif; ?>
								<?php endforeach; ?>
								</ul>
							</td>
							<td><?php echo hsc($user['email']);?></td>
							<td><?php echo hsc($user['date_joined']);?></td>
							<td><?php echo hsc($user['last_login']);?></td>
							<td><?php echo hsc($user['id']);?></td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>
						<?php if(empty($userList)): ?>
						<tr class="overview-row1">
							<td colspan="11">
								Your search did not match any records.
							</td>
						</tr>
						<?php endif; ?>
					</table>
					<div class="links links-bottom"><?php echo $pager->menu; echo $pager->limitMenu;?></div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
