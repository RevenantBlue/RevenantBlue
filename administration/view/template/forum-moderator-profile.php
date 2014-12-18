<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/forums/forums-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Forums';
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
<script type="text/javascript">
	<?php if(!empty($_GET['forums'])): ?>
	var checkedForums = <?php echo $_GET['forums']; ?>;
	<?php endif; ?>
	//console.log(checkedForums);
</script>
</head>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<?php if(isset($forumModerator)): ?>
				<span>Edit Forum Moderator</span>
				<?php else: ?>
				<span>New Forum Moderator</span>
				<?php endif; ?>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>forums/moderators/new">
						<span class="ui-icon ui-icon-plus"></span>
						New Forum Moderator
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
				<li>
					<a href="#">Help</a>
				</li>
				<li>
					<a href="#">About</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($moderator)): ?>
		<?php displayBreadCrumbs('Edit Moderator', hsc($_SERVER['REQUEST_URI']), array('title' => 'Forums', 'url' => HTTP_ADMIN . 'forums')); ?>
		<?php else: ?>
		<?php displayBreadCrumbs('Add New Moderator', HTTP_ADMIN . 'forums/moderator/new', array('title' => 'Forums' , 'url' => HTTP_ADMIN . 'forums')); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix" <?php if(!in_array(101, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<?php if(aclVerify('backend forum admin')): ?>
					<li id="toolbar-save-moderator">
						<a href="#">
							<span class="ui-icon ui-icon-disk"></span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close-moderator">
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
				<div class="clearfix"></div>
			</div>
		</div>
		<div class="clearfix"> </div>
		<div id="element-box">
			<div id="content-padding">
				<form id="adminForm" action="" method="post">
					<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					<input type="hidden" id="forum-action" name="forumAction" />
					<input type="hidden" id="new-mod-user-id" name="userId"
						<?php if(isset($_GET['userId'])): ?>
							value="<?php echo (int)hsc($_GET['userId']); ?>" 
						<?php elseif(!empty($moderator['user_id'])): ?>
							value="<?php echo hsc($moderator['user_id']); ?>"
						<?php endif; ?>
					/>
					<input type="hidden" id="new-mod-role-id" name="roleId"
						<?php if(isset($_GET['roleId'])): ?>
							value="<?php echo (int)hsc($_GET['roleId']); ?>"
						<?php elseif(!empty($moderator['role_id'])): ?>
							value="<?php echo hsc($moderator['role_id']); ?>"
						<?php endif; ?> 
					/>
					<input type="hidden" id="moderator-id" name="moderatorId" <?php if(isset($moderator)): ?>value="<?php echo hsc($moderator['id']); ?>" <?php endif; ?> />
					<?php if(isset($moderator)): ?>
					<div class="element">
						<?php if(!empty($moderator['user_id'])): ?>
						<div class="element-top">User Moderator</div>
						<?php else: ?>
						<div class="element-top">Role Moderator</div>
						<?php endif; ?>
						<div class="element-body">
							<input type="hidden" name="id" value="<?php echo hsc($moderator['id']);?>" />
							<?php echo hsc($moderatorName); ?>
						</div>
					</div>
					<table id="select-forum-tbl" class="overview yes-no-tbl">
						<tr>
							<th>
								Select Forums
							</th>
							<td class="select-forums">
								<select id="forums-to-moderate" name="forums[]" size="10" multiple="multiple">
								<?php foreach($forumList as $forum): ?>
									<option value="<?php echo hsc($forum['id']); ?>" <?php if($forum['id'] === $moderator['forum_id']): ?>selected="selected" <?php else: ?>disabled="disabled"<?php endif; ?>>
									<?php if((int)$forum['path_length'] > 0): ?>
										<?php echo str_repeat('- ', (int)$forum['path_length']) . $forum['forum_title']; ?>
									<?php else: ?>
										<?php echo hsc($forum['forum_title']); ?>
									<?php endif; ?>
									</option>
								<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>
					<table class="overview yes-no-tbl">
						<tr>
							<th class="divider" colspan="2">General</th>
						</tr>
						<tr>
							<th>Grant or deny all</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" id="grant-all-forum-perms" class="center-toggle" name="selectAll" />
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" id="deny-all-forum-perms" class="center-toggle" name="selectAll" />
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can edit posts and polls
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][edit_posts]" value="1" <?php if($moderator['edit_posts']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][edit_posts]" value="0" <?php if(!$moderator['edit_posts']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can edit topic titles
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][edit_topics]" value="1" <?php if($moderator['edit_topics']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][edit_topics]" value="0" <?php if(!$moderator['edit_topics']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can delete posts
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][delete_posts]" value="1" <?php if($moderator['delete_posts']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][delete_posts]" value="0" <?php if(!$moderator['delete_posts']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can delete topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][delete_topics]" value="1" <?php if($moderator['delete_topics']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][delete_topics]" value="0" <?php if(!$moderator['delete_topics']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can hide posts
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][hide_posts]" value="1" <?php if($moderator['hide_posts']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][hide_posts]" value="0" <?php if(!$moderator['hide_posts']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can unhide posts
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][unhide_posts]" value="1" <?php if($moderator['unhide_posts']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][unhide_posts]" value="0" <?php if(!$moderator['unhide_posts']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can edit all topic titles
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][edit_topic_titles]" value="1" <?php if($moderator['edit_topic_titles']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][edit_topic_titles]" value="0" <?php if(!$moderator['edit_topic_titles']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can view hidden content
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][view_all_content]" value="1" <?php if($moderator['view_all_content']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][view_all_content]" value="0" <?php if(!$moderator['view_all_content']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can view IP addresses
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][view_ip]" value="1" <?php if($moderator['view_ip']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][view_ip]" value="0" <?php if(!$moderator['view_ip']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can open locked topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][open_topics]" value="1" <?php if($moderator['open_topics']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][open_topics]" value="0" <?php if(!$moderator['open_topics']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can lock topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][close_topics]" value="1" <?php if($moderator['close_topics']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][close_topics]" value="0" <?php if(!$moderator['close_topics']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can move topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][move_topics]" value="1" <?php if($moderator['move_topics']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][move_topics]" value="0" <?php if(!$moderator['move_topics']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can pin topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][pin_topics]" value="1" <?php if($moderator['pin_topics']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][pin_topics]" value="0" <?php if(!$moderator['pin_topics']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can unpin topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][unpin_topics]" value="1" <?php if($moderator['unpin_topics']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][unpin_topics]" value="0" <?php if(!$moderator['unpin_topics']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can split and merge topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][split_and_merge]" value="1" <?php if($moderator['split_and_merge']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][split_and_merge]" value="0" <?php if(!$moderator['split_and_merge']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Toggle answered state
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][toggle_answered]" value="1" <?php if($moderator['toggle_answered']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][toggle_answered]" value="0" <?php if(!$moderator['toggle_answered']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th class="divider" colspan="2">Moderator Control Panel Settings</th>
						</tr>
						<tr>
							<th>
								Can mass move topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][mass_move]" value="1" <?php if($moderator['mass_move']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][mass_move]" value="0" <?php if(!$moderator['mass_move']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can mass prune topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][mass_prune]" value="1" <?php if($moderator['mass_prune']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][mass_prune]" value="0" <?php if(!$moderator['mass_prune']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can set topics' visibility
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][toggle_topic_visibility]" value="1" <?php if($moderator['toggle_topic_visibility']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][toggle_topic_visibility]" value="0" <?php if(!$moderator['toggle_topic_visibility']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can set posts' visibility
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][toggle_post_visibility]" value="1" <?php if($moderator['toggle_post_visibility']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][toggle_post_visibility]" value="0" <?php if(!$moderator['toggle_post_visibility']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th class="divider" colspan="2">
								Other Permissions
							</th>
						</tr>
						<tr>
							<th>
								Can warn users
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][warn_users]" value="1" <?php if($moderator['warn_users']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][warn_users]" value="0" <?php if(!$moderator['warn_users']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can flag user as a spammer
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][flag_spammer]" value="1" <?php if($moderator['flag_spammer']): ?>checked="checked"<?php endif; ?> />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][flag_spammer]" value="0" <?php if(!$moderator['flag_spammer']): ?>checked="checked"<?php endif; ?> />
										No
									</label>
								</span>
							</td>
						</tr>
					</table>
					<?php else: ?>
					<div class="element">
						<?php if(isset($_GET['userId']) && isset($userModerator)): ?>
						<div class="element-top">User Moderator</div>
						<div class="element-body">
							<?php echo hsc($userModerator); ?>
						</div>
						<?php elseif(isset($_GET['roleId']) && isset($roleModerator)): ?>
						<div class="element-top">Role Moderator</div>
						<div class="element-body">
							<?php echo hsc($roleModerator); ?>
						</div>
						<?php endif; ?>
					</div>
					<table id="select-forum-tbl" class="overview yes-no-tbl">
						<tr>
							<th>
								Select Forums
							</th>
							<td class="select-forums">
								<select id="forums-to-moderate" name="forums[]" size="10" multiple="multiple">
								<?php foreach($forumList as $forum): ?>
									<?php if((int)$forum['path_length'] > 0): ?>
									<option value="<?php echo hsc($forum['id']); ?>">
										<?php echo str_repeat('- ', (int)$forum['path_length']) . $forum['forum_title']; ?>
									</option>
									<?php else: ?>
									<option value="<?php echo hsc($forum['id']); ?>" disabled="disabled">
										<?php echo hsc($forum['forum_title']); ?>
									</option>
									<?php endif; ?>
								<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>
					<table class="overview yes-no-tbl">
						<tr>
							<th class="divider" colspan="2">General</th>
						</tr>
						<tr>
							<th>Grant or deny all permissions</th>
							<td>
								<span class="radio-yes center">
									<label class="center-label">
										<input type="radio" id="grant-all-forum-perms" class="center-toggle" name="selectAll" />
									</label>
								</span>
								<span class="radio-no center">
									<label class="center-label">
										<input type="radio" id="deny-all-forum-perms" class="center-toggle" name="selectAll" />
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can edit posts and polls
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][edit_posts]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][edit_posts]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can edit topic titles
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][edit_topics]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][edit_topics]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can delete posts
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][delete_posts]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][delete_posts]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can delete topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][delete_topics]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][delete_topics]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can hide posts
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][hide_posts]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][hide_posts]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can unhide posts
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][unhide_posts]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][unhide_posts]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can edit all topic titles
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][edit_topic_titles]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][edit_topic_titles]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can view hidden content
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][view_all_content]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][view_all_content]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can view IP addresses
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][view_ip]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][view_ip]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can open locked topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][open_topics]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][open_topics]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can lock topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][close_topics]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][close_topics]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can move topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][move_topics]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][move_topics]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can pin topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][pin_topics]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][pin_topics]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can unpin topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][unpin_topics]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][unpin_topics]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can split and merge topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][split_and_merge]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][split_and_merge]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Toggle answered state
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][toggle_answered]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][toggle_answered]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th class="divider" colspan="2">Moderator Control Panel Settings</th>
						</tr>
						<tr>
							<th>
								Can mass move topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][mass_move]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][mass_move]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can mass prune topics
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][mass_prune]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][mass_prune]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can set topics' visibility
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][toggle_topic_visibility]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][toggle_topic_visibility]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can set posts' visibility
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][toggle_post_visibility]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][toggle_post_visibility]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th class="divider" colspan="2">
								Other Permissions
							</th>
						</tr>
						<tr>
							<th>
								Can warn users
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][warn_users]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][warn_users]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
						<tr>
							<th>
								Can flag user as a spammer
							</th>
							<td>
								<span class="radio-yes">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][flag_spammer]" value="1" />
										Yes
									</label>
								</span>
								<span class="radio-no">
									<label class="center-label">
										<input type="radio" class="center-toggle" name="modPerms[][flag_spammer]" checked="checked" value="0" />
										No
									</label>
								</span>
							</td>
						</tr>
					</table>
					<?php endif; ?>
				</form>
			</div>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
