<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/forums/forums-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Forums';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
loadJqueryNestable();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/forums.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/forums.min.js"></script>
<?php endif; ?>
<script type="text/javascript">
	<?php if(!empty($forumList)): ?>
	var forumList = <?php echo $forumList; ?>;
	<?php endif; ?>
	<?php if(!empty($forumMods)): ?>
	var forumMods = <?php echo $forumMods; ?>;
	<?php endif; ?>
</script>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" type="text/css" />
<?php loadMainCss(); ?>
</head>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<span>Forums</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>forums/new">
						<span class="ui-icon ui-icon-plus"></span>
						New Forum
					</a>
				</li>
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>forums/section/new">
						<span class="ui-icon ui-icon-plusthick"></span>
						New Section
					</a>
				</li>
				<li>
					<a href="#" id="action-edit-forum">Edit</a>
				</li>
				<li>
					<a href="#">Status</a>
					<ul>
						<li>
							<a href="#" id="action-publish-forum">
								<span class="ui-icon ui-icon-check"></span>
								Publish
							</a>
						</li>
						<li>
							<a href="#" id="action-unpublish-forum">
								<span class="ui-icon ui-icon-radio-off"></span>
								Unpublish
							</a>
						</li>
						<li>
							<a href="#" id="action-feature-forum">
								<span class="ui-icon ui-icon-star"></span>
								Feature
							</a>
						</li>
						<li>
							<a href="#" id="action-disregard-forum">
								<span class="ui-icon ui-icon-cancel"></span>
								Disregard
							</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#" id="action-delete-forum">
						<span class="ui-icon ui-icon-trash"></span>
						Delete
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
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-99" <?php if(!in_array(99, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<?php if(aclVerify('create forum') || aclVerify('backend forum admin')): ?>
					<li id="toolbar-new-forum">
						<a class="" href="<?php echo HTTP_ADMIN; ?>forums/new/">
							<span class="ui-icon ui-icon-plus"> </span>
							<span class="toolbar-text">New Forum</span>
						</a>
					</li>
					<?php endif ?>
					<?php if(aclVerify('create section') || aclVerify('backend forum admin')): ?>
					<li id="toolbar-new-section">
						<a href="<?php echo HTTP_ADMIN; ?>forums/section/new">
							<span class="ui-icon ui-icon-plusthick"></span>
							<span class="toolbar-text">New Section</span>
						</a>
					</li>
					<?php endif; ?>
					<?php if(aclVerify('backend forum admin')): ?>
					<li id="toolbar-edit-forum">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"></span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<li id="toolbar-edit-permissions">
						<a href="#">
							<span class="ui-icon ui-icon-key"></span>
							<span class="toolbar-text">Permissions</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-publish-forum">
						<a href="#">
							<span class="ui-icon ui-icon-check"> </span>
							<span class="toolbar-text">Publish</span>
						</a>
					</li>
					<li id="toolbar-unpublish-forum">
						<a href="#">
							<span class="ui-icon ui-icon-radio-off"> </span>
							<span class="toolbar-text">Unpublish</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-feature-forum">
						<a href="#">
							<span class="ui-icon ui-icon-star"> </span>
							<span class="toolbar-text">Feature</span>
						</a>
					</li>
					<li id="toolbar-disregard-forum">
						<a href="#">
							<span class="ui-icon ui-icon-cancel"> </span>
							<span class="toolbar-text">Disregard</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-delete-forum">
						<a href="#">
							<span class="ui-icon ui-icon-trash"> </span>
							<span class="toolbar-text">Delete</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<?php endif; ?>
					<li id="toolbar-options">
						<a id="options" href="#">
							<span class="ui-icon ui-icon-gear"> </span>
							<span class="toolbar-text">Options</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-forum">
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
				<form id="adminForm" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<div id="add-mod-menu" class="element filter-menu fltlft">
						<input id="csrf-token" type="hidden" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input id="forum-action" type="hidden" name="forumAction" />
						<div class="element-top">Add Moderator to Selected Forum(s)</div>
						<div class="element-body">
							<div id="user-mod-wrap">
								<input id="user-moderator" type="text" class="normal-text" placeholder="Username" />
								<button id="add-user-mod-btn" class="blue-btn rb-btn button">Add User Moderator</button>
							</div>
							<div id="role-mod-wrap">
								<select id="role-moderator" class="normal">
									<?php foreach($roles as $role): ?>
									<option value="<?php echo hsc($role['id']); ?>"><?php echo hsc($role['name']); ?></option>
									<?php endforeach; ?>
								</select>
								<button id="add-role-mod-btn" class="rb-btn dark-btn button">Add Role Moderator</button>
							</div>
						</div>
					</div>
					<div class="forum-btns horz-btn-wrap fltrght">
						<button id="new-section-btn" class="rb-btn gray-btn button">Add New Section</button>
						<button id="new-forum-btn" class="rb-btn light-gray-btn button">Add New Forum</button>
					</div>
					<div class="clearfix"></div>
					<?php if($forumList !== '[]'): ?>
					<ol id="forum-sortable" class="hier-sortable"></ol>
					<?php else: ?>
					<div class="element">
						<div class="element-body">
							No Sections have been created yet. 
							<a href="<?php echo HTTP_ADMIN; ?>forums/section/new">Add Section</a>
						</div>
					</div>
					<?php endif; ?>
				</form>
			</div>
		</div>
		<div id="options-form" title="Forum Options" style="display: none;">
			<div class="panel">
				<div class="panel-column">
					<form id="options-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
						<div class="element">
							<div class="element-top">Global Settings</div>
							<div class="element-body">
								<table class="overview width100pcnt no-border">
									<tr>
										<td class="width60pcnt left">
											<label for="num-of-topics-to-show">Number of topics to show at once</label>
										</td>
										<td>
											<input id="num-of-topics-to-show" type="text" class="small-text"
												<?php if(!empty($globalSettings['num_of_topics_to_show']['value'])): ?>
												value="<?php echo hsc($globalSettings['num_of_topics_to_show']['value']); ?>"
												<?php endif; ?>
											/>
										</td>
									</tr>
									<tr>
										<td class="left">
											<label for="num-of-posts-to-show">Number of posts to show at once</label>
										</td>
										<td>
											<input id="num-of-posts-to-show" type="text" class="small-text" 
												<?php if(!empty($globalSettings['num_of_posts_to_show']['value'])): ?>
												value="<?php echo hsc($globalSettings['num_of_posts_to_show']['value']); ?>"
												<?php endif; ?>
											/>
										</td>
									</tr>
								</table>
								<div class="vert-space">
									<button id="submit-forum-globals" class="rb-btn">Save</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
