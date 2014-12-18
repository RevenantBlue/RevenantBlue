<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/config/config-c.php';
$title = 'Configuration';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryValidation();
loadJqueryUi();
loadTableDragNDrop();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/config.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/config.min.js"></script>
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
				<span>Configuration</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
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
		<?php if(!empty($_GET['setting'])): ?>
		<?php displayBreadCrumbs(str_replace('-', ' ', $_GET['setting']), HTTP_ADMIN . 'config/' . $_GET['setting']); ?>
		<?php else: ?>
		<?php displayBreadCrumbs(); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix option-88" <?php if(!in_array(88, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<?php if(isset($_GET['setting'])): ?>
				<ul>
					<?php if($_GET['setting'] === 'logging-and-errors'): ?>
					<li id="toolbar-clear-error-log">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-text">Clear Log</span>
						</a>
					</li>
					<?php elseif($_GET['setting'] === 'performance'): ?>
					<li id="toolbar-add-cache-key">
						<a href="#">
							<span class="ui-icon ui-icon-plusthick"></span>
							<span class="toolbar-text">Add Key</span>
						</a>
					</li>
					<li id="toolbar-delete-cache-key">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-text">Delete Key</span>
						</a>
					</li>
					<?php elseif($_GET['setting'] === 'scheduled-tasks'): ?>
					<li id="toolbar-add-task">
						<a href="#">
							<span class="ui-icon ui-icon-plusthick"></span>
							<span class="toolbar-text">New Task</span>
						</a>
					</li>
					<li id="toolbar-delete-task">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-text">Delete Task</span>
						</a>
					</li>
					<?php endif; ?>
				</ul>
				<?php endif; ?>
				<div class="clear"></div>
			</div>
		</div>
		<div class="clearfix"> </div>
		<form id="adminForm" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
			<div id="element-box">
				<div id="content-padding">
					<?php if(!isset($_GET['setting'])): ?>
					<div class="admin-left">
						<div class="admin-widget">
							<div class="element">
								<div class="element-top">General</div>
								<div class="element-body panel-padding">
									<ul class="admin-list">
										<li>
											<a href="<?php echo HTTP_ADMIN; ?>config/global-settings">Global settings</a>
											<div class="admin-description">Change site name, e-mail address, number of posts per page, and error pages.</div>
										</li>
										<li class="node">
											<a href="<?php echo HTTP_ADMIN; ?>config/scheduled-tasks">Scheduled tasks</a>
											<div class="admin-description">Determine how often the task scheduler is run.</div>
										</li>
										<li class="node">
											<a href="<?php echo HTTP_ADMIN; ?>config/site-security">Site security</a>
											<div class="admin-description">Change ssl security for the backend and frontend.</div>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="admin-widget">
							<div class="element">
								<div class="element-top">Content and publishing</div>
								<div class="element-body panel-padding">
									<ul class="admin-list">
										<li>
											<a href="<?php echo HTTP_ADMIN; ?>config/content-filtering">Content filtering</a>
											<div class="admin-description">Assign and customize content filtering for each user role.</div>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="admin-widget">
							<div class="element">
								<div class="element-top">Media</div>
								<div class="element-body panel-padding">
									<ul class="admin-list">
										<li>
											<a href="<?php echo HTTP_ADMIN; ?>config/media-settings">Media settings</a>
											<div class="admin-description">Configure how and where media files are uploaded to the server.</div>
										</li>
										<li class="node">
											<a href="<?php echo HTTP_ADMIN; ?>config/image-templates">Image templates</a>
											<div class="admin-description">Manage image sizes used when uploading image files.</div>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="admin-widget">
							<div class="element">
								<div class="element-top">Search and metadata</div>
								<div class="element-body panel-padding">
									<ul class="admin-list">
										<li>
											<a href="<?php echo HTTP_ADMIN; ?>config/clean-urls">Clean URLs</a>
											<div class="admin-description">Enable or disable SEO friendly URL rewriting.</div>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<div class="admin-right">
						<div class="admin-widget">
							<div class="element">
								<div class="element-top">Users</div>
								<div class="element-body panel-padding">
									<ul class="admin-list">
										<li>
											<a href="<?php echo HTTP_ADMIN; ?>config/account-settings">Account settings</a>
											<div class="admin-description">Configure options that affect all users on the website.</div>
										</li>
										<li class="node">
											<a href="<?php echo HTTP_ADMIN; ?>config/ip-addresses">IP address blocking</a>
											<div class="admin-description">Add and remove blocked ip addresses.</div>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="admin-widget">
							<div class="element">
								<div class="element-top">User Interface</div>
								<div class="element-body panel-padding">
									<ul class="admin-list">
										<li>
											<a href="<?php echo HTTP_ADMIN; ?>config/personalization">Personalization</a>
											<div class="admin-description">Manage personalization options for users.</div>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="admin-widget">
							<div class="element">
								<div class="element-top">Development</div>
								<div class="element-body panel-padding">
									<ul class="admin-list">
										<li>
											<a href="<?php echo HTTP_ADMIN; ?>config/performance">Performance</a>
											<div class="admin-description">Manage page caching options.</div>
										</li>
										<li class="node">
											<a href="<?php echo HTTP_ADMIN; ?>config/logging-and-errors">Logging and errors</a>
											<div class="admin-description">Manage logs and errors generated by Revenant Blue</div>
										</li>
										<li class="node">
											<a href="<?php echo HTTP_ADMIN; ?>config/maintenance">Maintenance</a>
											<div class="admin-description">Manage backups and database recovery. Take the website offline or bring it back online.</div>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="admin-widget">
							<div class="element">
								<div class="element-top">Web Services</div>
								<div class="element-body panel-padding">
									<ul class="admin-list">
										<li>
											<a href="#">RSS</a>
											<div class="admin-description">Configure the output of RSS feeds.</div>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'account-settings'): ?>
					<div class="element">
						<div class="element-top">Registration and Cancellation</div>
						<div class="element-body panel-padding setting-group">
							<div class="sub-setting">
								<h2>Who can register</h2>
								<label class="setting-label center-label">
									<input class="center-toggle" type="radio" value="admin only" name="config[who_can_register]" <?php if($globalSettings['who_can_register']['value'] === 'admin only'): ?>checked="checked"<?php endif; ?> />
									Administrators only.
								</label>
								<label class="setting-label center-label">
									<input class="center-toggle" type="radio" value="visitors" name="config[who_can_register]" <?php if($globalSettings['who_can_register']['value'] === 'visitors'): ?>checked="checked"<?php endif; ?> />
									Vistors
								</label>
								<label class="setting-label center-label">
									<input class="center-toggle" type="radio" value="vistors_approval_req" name="config[who_can_register]" <?php if($globalSettings['who_can_register']['value'] === 'visitors_approval_req'): ?>checked="checked"<?php endif; ?> />
									Vistors, but administrative approval is required.
								</label>

							</div>
							<div class="sub-setting">
								<h2>Email Verification</h2>
								<label for="userEmailVerification" class="setting-label center-label">
									<input type="hidden" name="config[new_user_email_verify]" value="0" />
									<input class="center-toggle" type="checkbox" value="1" name="config[new_user_email_verify]" <?php if($globalSettings['new_user_email_verify']['value']): ?>checked="checked"<?php endif; ?> />
									Require email verification when a new user creates an account.
								</label>
							</div>
							<div class="sub-setting">
								<h2>Disabling an account</h2>
								<label class="setting-label center-label">
									<input class="center-toggle" type="radio" value="keep content" name="config[disabling_an_account]" <?php if($globalSettings['disabling_an_account']['value'] === 'keep content'): ?>checked="checked"<?php endif; ?> />
									Disable the account and keep its content.
								</label>
								<label class="setting-label center-label">
									<input class="center-toggle" type="radio" value="unpublish content" name="config[disabling_an_account]" <?php if($globalSettings['disabling_an_account']['value'] === 'unpublish content'): ?>checked="checked"<?php endif; ?> />
									Disable the account and unpublish its content.
								</label>
							</div>
							<div class="sub-setting">
								<h2>Deleting an account</h2>
								<label class="setting-label center-label">
									<input class="center-toggle" type="radio" value="assign content to anon" name="config[deleting_an_account]" <?php if($globalSettings['deleting_an_account']['value'] === 'assign content to anon'): ?>checked="checked"<?php endif; ?> />
									Delete the account and assign the user's content to the Anonymous user.
								</label>
								<label class="setting-label center-label">
									<input class="center-toggle" type="radio" value="delete content" name="config[deleting_an_account]" <?php if($globalSettings['deleting_an_account']['value'] === 'delete content'): ?>checked="checked"<?php endif; ?> />
									Delete the account as well as all content assigned the user.
								</label>
							</div>
							<div class="sub-setting">
								<h2>Account notifications</h2>
								<label class="setting-label center-label">
									<input type="hidden" name="config[notify_user_on_block]" value="0" />
									<input class="center-toggle" type="checkbox" value="1" name="config[notify_user_on_block]" <?php if($globalSettings['notify_user_on_block']['value']): ?>checked="checked" <?php endif; ?>/>
									Notify user when their account has been blocked.
								</label>
								<label class="setting-label center-label">
									<input type="hidden" name="config[notify_user_on_cancel]" value="0" />
									<input class="center-toggle" type="checkbox" value="1" name="config[notify_user_on_cancel]" <?php if($globalSettings['notify_user_on_cancel']['value']): ?>checked="checked" <?php endif; ?> />
									Notify user when their account has been cancelled.
								</label>
							</div>
						</div>
					</div>
					<div class="element">
						<div class="element-top">Limits and Logging</div>
						<div class="element-body panel-padding setting-group">
							<div class="sub-setting">
								<div class="vert-space">
									<label class="setting-label">
										Front End Login Limit
									</label>
									<select name="config[frontend_login_limit]">
										<option value="none" <?php if($globalSettings['frontend_login_limit']['value'] === 'none'): ?>selected="selected"<?php endif; ?>>
											No Limit
										</option>
										<option value="300" <?php if($globalSettings['frontend_login_limit']['value'] === '300'): ?>selected="selected"<?php endif; ?>>
											5 Minutes
										</option>
										<option value="600" <?php if($globalSettings['frontend_login_limit']['value'] === '600'): ?>selected="selected"<?php endif; ?>>
											10 Minutes
										</option>
										<option value="900" <?php if($globalSettings['frontend_login_limit']['value'] === '900'): ?>selected="selected"<?php endif; ?>>
											15 Minutes
										</option>
										<option value="1800" <?php if($globalSettings['frontend_login_limit']['value'] === '1800'): ?>selected="selected"<?php endif; ?>>
											30 Minutes
										</option>
										<option value="3600" <?php if($globalSettings['frontend_login_limit']['value'] === '3600'): ?>selected="selected"<?php endif; ?>>
											1 Hour
										</option>
										<option value="10800" <?php if($globalSettings['frontend_login_limit']['value'] === '10800'): ?>selected="selected"<?php endif; ?>>
											3 Hours
										</option>
										<option value="21600" <?php if($globalSettings['frontend_login_limit']['value'] === '21600'): ?>selected="selected"<?php endif; ?>>
											6 Hours
										</option>
										<option value="43200" <?php if($globalSettings['frontend_login_limit']['value'] === '43200'): ?>selected="selected"<?php endif; ?>>
											12 Hours
										</option>
										<option value="86400" <?php if($globalSettings['frontend_login_limit']['value'] === '86400'): ?>selected="selected"<?php endif; ?>>
											1 Day
										</option>
										<option value="259200" <?php if($globalSettings['frontend_login_limit']['value'] === '259200'): ?>selected="selected"<?php endif; ?>>
											3 Days
										</option>
										<option value="604800" <?php if($globalSettings['frontend_login_limit']['value'] === '604800'): ?>selected="selected"<?php endif; ?>>
											1 Week
										</option>
										<option value="2419200" <?php if($globalSettings['frontend_login_limit']['value'] === '2419200'): ?>selected="selected"<?php endif; ?>>
											1 Month
										</option>
									</select>
								</div>
								<div class="vert-space">
									<label class="setting-label">
										Back End Login Limit
									</label>
									<select name="config[backend_login_limit]">
										<option value="none" <?php if($globalSettings['backend_login_limit']['value'] === 'none'): ?>selected="selected"<?php endif; ?>>
											No Limit
										</option>
										<option value="300" <?php if($globalSettings['backend_login_limit']['value'] === '300'): ?>selected="selected"<?php endif; ?>>
											5 Minutes
										</option>
										<option value="600" <?php if($globalSettings['backend_login_limit']['value'] === '600'): ?>selected="selected"<?php endif; ?>>
											10 Minutes
										</option>
										<option value="900" <?php if($globalSettings['backend_login_limit']['value'] === '900'): ?>selected="selected"<?php endif; ?>>
											15 Minutes
										</option>
										<option value="1800" <?php if($globalSettings['backend_login_limit']['value'] === '1800'): ?>selected="selected"<?php endif; ?>>
											30 Minutes                                                       
										</option>
										<option value="3600" <?php if($globalSettings['backend_login_limit']['value'] === '3600'): ?>selected="selected"<?php endif; ?>>
											1 Hour
										</option>
										<option value="10800" <?php if($globalSettings['backend_login_limit']['value'] === '10800'): ?>selected="selected"<?php endif; ?>>
											3 Hours
										</option>
										<option value="21600" <?php if($globalSettings['backend_login_limit']['value'] === '21600'): ?>selected="selected"<?php endif; ?>>
											6 Hours
										</option>
										<option value="43200" <?php if($globalSettings['backend_login_limit']['value'] === '43200'): ?>selected="selected"<?php endif; ?>>
											12 Hours
										</option>
										<option value="86400" <?php if($globalSettings['backend_login_limit']['value'] === '86400'): ?>selected="selected"<?php endif; ?>>
											1 Day
										</option>
										<option value="259200" <?php if($globalSettings['backend_login_limit']['value'] === '259200'): ?>selected="selected"<?php endif; ?>>
											3 Days
										</option>
										<option value="604800" <?php if($globalSettings['backend_login_limit']['value'] === '604800'): ?>selected="selected"<?php endif; ?>>
											1 Week
										</option>
										<option value="2419200" <?php if($globalSettings['backend_login_limit']['value'] === '2419200'): ?>selected="selected"<?php endif; ?>>
											1 Month
										</option>
									</select>
								</div>
							</div>
							<div class="sub-setting">
								<label class="setting-label center-label">
									<input type="hidden" name="config[log_user_ips]" value="0" />
									<input type="checkbox" class="center-toggle" value="1" name="config[log_user_ips]" 
									       <?php if($globalSettings['log_user_ips']): ?>
									       checked="checked"
									       <?php endif; ?>
									/>
									Log User IP Addresseses
								</label>
							</div>
							<div class="sub-setting">
								<label for="active-user-limit" class="setting-label">
									Limit for active user tracking
								</label>
								<select id="active-user-limit" name="config[active_user_limit]">
									<option value="300" <?php if($globalSettings['active_user_limit']['value'] === '30'): ?>selected="selected"<?php endif; ?>>
										30 Seconds
									</option>
									<option value="300" <?php if($globalSettings['active_user_limit']['value'] === '60'): ?>selected="selected"<?php endif; ?>>
										1 Minute
									</option>
									<option value="300" <?php if($globalSettings['active_user_limit']['value'] === '180'): ?>selected="selected"<?php endif; ?>>
										3 Minutes
									</option>
									<option value="300" <?php if($globalSettings['active_user_limit']['value'] === '300'): ?>selected="selected"<?php endif; ?>>
										5 Minutes
									</option>
									<option value="600" <?php if($globalSettings['active_user_limit']['value'] === '600'): ?>selected="selected"<?php endif; ?>>
										10 Minutes
									</option>
									<option value="900" <?php if($globalSettings['active_user_limit']['value'] === '900'): ?>selected="selected"<?php endif; ?>>
										15 Minutes
									</option>
									<option value="1800" <?php if($globalSettings['active_user_limit']['value'] === '1800'): ?>selected="selected"<?php endif; ?>>
										30 Minutes
									</option>
									<option value="3600" <?php if($globalSettings['active_user_limit']['value'] === '3600'): ?>selected="selected"<?php endif; ?>>
										1 Hour
									</option>
									<option value="10800" <?php if($globalSettings['active_user_limit']['value'] === '10800'): ?>selected="selected"<?php endif; ?>>
										3 Hours
									</option>
								</select>
								<div class="admin-description">
									The maximum amount of time after a user's last activity before they are no longer considered active.
								</div> 
							</div>
						</div>
					</div>
					<div class="element">
						<div class="element-top">Personalization</div>
						<div class="element-body panel-padding setting-group">
							<div class="sub-setting">
								<label class="setting-label center-label">
									<input type="hidden" name="config[allow_user_signatures]" value="0" />
									<input class="center-toggle" type="checkbox" value="1" name="config[allow_user_signatures]"
										   <?php if($globalSettings['allow_user_signatures']['value']): ?>
										   checked="checked"
										   <?php endif; ?>
									/>
									Allow user signatures.
								</label>
								<label class="setting-label center-label">
									<input type="hidden" name="config[allow_user_avatars]" value="0" />
									<input class="center-toggle" type="checkbox" value="1" name="config[allow_user_avatars]"
										   <?php if($globalSettings['allow_user_avatars']['value']): ?>
										   checked="checked"
										   <?php endif; ?>
									/>
									Notify user when their account has been cancelled.
								</label>
							</div>
							<div class="sub-setting">
								<h2>Default avatar</h2>
								<input type="text" name="config[avatar_location]" value="<?php echo hsc($globalSettings['avatar_location']['value']);  ?>" size="80" />
								<div class="admin-description">
									URL for the picture to display for users with no custom picture selected. Leave blank for none.
								</div>
							</div>
							<div class="sub-setting">
								<h2>Default small avatar</h2>
								<input type="text" name="config[small_avatar_location]" value="<?php echo hsc($globalSettings['small_avatar_location']['value']); ?>" size="80" />
								<div class="admin-description">
									URL for the smaller version of the default avatar.
								</div>
							</div>
							<div class="sub-setting">
								<h2>Avatar image template</h2>
								<select name="config[avatar_template]">
									<option value="none">None</option>
									<?php foreach($imageTemplates as $imageTemplate): ?>
									<option value="<?php echo hsc($imageTemplate['id']); ?>" <?php if($globalSettings['avatar_template']['value'] === $imageTemplate['id']): ?>selected="seleceted"<?php endif; ?>><?php echo hsc($imageTemplate['template_name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="sub-setting">
								<h2>Maximum avatar size</h2>
								<label>
									<input type="text" name="config[max_avatar_size]" value="<?php echo hsc($globalSettings['max_avatar_size']['value']);  ?>" size="8" />
									<span>KB</span>
								</label>
							</div>
						</div>
					</div>
					<div class="element">
						<div class="element-top">Emails</div>
						<div class="element-body panel-padding setting-group">
							<div class="sub-setting">
								<div id="verticalTabs" class="email-templates-list">
									<ul>
										<?php foreach($emailTemplates as $emailTemplate): ?>
										<li id="email-template-tab-<?php echo hsc($emailTemplate['id']); ?>">
											<a href="#verticalTabs-<?php echo hsc($emailTemplate['id']); ?>">
												<?php echo hsc($emailTemplate['template_name']); ?>
											</a>
											<div class="clearfix"> </div>
										</li>
										<?php endforeach; ?>
									</ul>
									<div id="email-template-content" class="vertical-tabs-padding clearfix">
										<?php foreach($emailTemplates as $emailTemplate): ?>
										<div id="verticalTabs-<?php echo hsc($emailTemplate['id']); ?>">
											<?php if(empty($emailTemplate['locked'])): ?>
											<div class="fltrght">
												<button id="delete-email-template-<?php echo hsc($emailTemplate['id']); ?>" class="delete-email-template-btn rb-btn light-gray-btn vert-space">
													Delete Template
												</button>
											</div>
											<?php endif; ?>
											<div>
												<h2 class="vert-space">Edit email templates</h2>
												<p>
													<?php echo hsc($emailTemplate['description']); ?>
												</p>
												<p>Available variables are: [site:name], [site:url], [user:name], [user:mail], [site:login-url], [site:url-brief], [user:edit-url], [user:one-time-login-url], [user:cancel-url].</p>
											</div>
											<div class="form-item">
												<label class="label-regular width100pcnt">Subject</label>
												<input class="width100pcnt" type="text" name="config[email][<?php echo hsc($emailTemplate['id']); ?>][subject]" value="<?php echo hsc($emailTemplate['subject']); ?>" />
											</div>
											<div class="form-item">
												<label class="label-regular width100pcnt">Body</label>
												<textarea class="width100pcnt" rows="20" name="config[email][<?php echo hsc($emailTemplate['id']); ?>][body]"><?php echo hsc($emailTemplate['body']); ?></textarea>
											</div>
										</div>
										<?php endforeach; ?>
									</div>
									<div>
										<button id="add-email-template-btn" class="rb-btn blue-btn">Add Email Template</button>
										<div id="add-email-template-form" class="clearfix" style="display: none;">
											<div>
												<h2>Add a new email template form</h2>
												<p>Available variables are: [site:name], [site:url], [user:name], [user:mail], [site:login-url], [site:url-brief], [user:edit-url], [user:one-time-login-url], [user:cancel-url].</p>
											</div>
											<div id="new-email-template-errors" class="form-errors">
											
											</div>
											<div class="form-item">
												<label class="label-regular width100pcnt">Template Name</label>
												<input id="new-email-template-name" type="text" class="width100pcnt" name="newEmailTemplateName" />
											</div>
											<div class="form-item">
												<label class="label-regular width100pcnt">Description</label>
												<input id="new-email-template-desc" type="text" class="width100pcnt" name="newEmailTemplateDesc" />
											</div>
											<div class="form-item">
												<label class="label-regular width100pcnt">Subject</label>
												<input id="new-email-template-subject" type="text" class="width100pcnt" name="newEmailTemplateSubject" />
											</div>
											<div class="form-item">
												<label class="label-regular width100pcnt">Body</label>
												<textarea id="new-email-template-body" class="width100pcnt" rows="20" name="newEmailTemplateBody"></textarea>
											</div>
											<button id="create-email-template" class="rb-btn light-gray-btn">Create Email Template</button>
										</div>
									</div>
								</div>
							</div>
							<button type="submit" class="config-submit rb-btn gray-btn" name="submitAccountSettings" value="Save configuration">Save configuration</button>
						</div>
					</div>
					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'ip-addresses'): ?>
					<script type="text/javascript">
					$(document).ready(function() {
						$("input:submit").button();
						$("#adminForm").validate({
							rules : {
								ip : {
									required   : true,
									ipv4       : true,
								},
							},
							messages : {
								ip : "Invalid IPv4 address.",

							},
						});
					});
					</script>
					<div class="element">
						<div class="element-top">Block IP Addresses</div>
						<div class="element-body pannel-padding setting-group">
							<div class="sub-setting">IP addresses listed here are blocked from accessing your website and will instead receive a message explaining the reason for the denial of service.</div>
							<input type="text" style="display: none;" />
							<label for="ip" class="setting-label std-label">IP Address</label>
							<input id="ip" type="text" class="large-text" name="ip" />
							<div class="setting-description btm-space">Enter a valid IP Address.</div>
							<button id="block-ip" class="rb-btn blue-btn top-space">Block</button>
						</div>
					</div>
					<table id="blocked-ips-table" class="overview width100pcnt left">
						<tr class="overview-top left">
							<th class="width50pcnt">Blocked IP Addresses</th>
							<th class="width50pcnt">Actions</th>
						</tr>
						<?php if(!empty($blockedIps)): ?>
						<?php foreach($blockedIps as $key => $blockedIp): ?>
						<?php $num = ($key % 2) + 1; ?>
						<tr id="blocked-ip-<?php echo hsc($blockedIp['id']); ?>"  left">
							<td>
								<input type="hidden" id="ip-<?php echo hsc($blockedIp['id']); ?>" value="<?php echo hsc($blockedIp['ip_address']); ?>" />
								<?php echo hsc($blockedIp['ip_address']); ?>
							</td>
							<td>
								<a id="unblock-ip-<?php echo hsc($blockedIp['id']); ?>" class="unblock-ip" href="#unblock-ip">Unblock</a>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>
						<tr id="no-blocked-ips" <?php if(!empty($blockedIps)): ?>style="display: none;"<?php endif; ?>>
							<td colspan="2">
								No IP Addresses have been blocked.
							</td>
						</tr>
					</table>
					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'content-filtering'): ?>
					<script type="text/javascript">
					$(document).ready(function() {
						var filterErrors = false;
						$("input:submit").button();
						$("#adminForm").validate({
							groups : {
								filterBoxes : "filters[1] filters[2] filters[3] filters[4] filters[5]"
							},
							rules : {
								formatName : {
									required : true
								}
							},
							messages : {
								formatName : "The format name is required.",
								"filters[1]" : "At least one filter must be selected.",
								"filters[2]" : "At least one filter must be selected.",
								"filters[3]" : "At least one filter must be selected.",
								"filters[4]" : "At least one filter must be selected.",
								"filters[5]" : "At least one filter must be selected.",
								"filters[6]" : "At least one filter must be selected."
							},
							errorPlacement : function(error, element) {
								var name = element.attr("name");
								if(name == 'filters[1]' || name == 'filters[2]' || name == 'filters[3]' || name == 'filters[4]' || name == 'filters[5]' || name === 'filters[6]') {
									if(filterErrors === false) error.insertAfter('#active-filters');
									filterErrors = true;
								} else {
									error.insertAfter(element);
								}
							}
						});
					});
					</script>
					<div class="element">
						<div class="element-top">Content Filters</div>
						<div class="element-body panel-padding setting-group">
							<?php if(isset($_GET['format']) && isset($_GET['action']) && $_GET['action'] === 'edit'): ?>
							<div class="sub-setting">
								<p>A text format contains filters that change the user input, for example stripping out malicious HTML or making URLs clickable. Filters are executed from top to bottom and the order is important, since one filter may prevent another filter from doing its job. For example, when URLs are converted into links before disallowed HTML tags are removed, all links may be removed. When this happens, the order of filters may need to be re-arranged.</p>
							</div>
							<div class="sub-setting">
								<div class="form-item">
									<label class="bold">
										Format name
									</label>
									<input type="text" class="normal-text" name="formatName" value="<?php echo hsc($format['format_name']); ?>" />
									<input type="hidden" name="formatId" value="<?php echo hsc($format['format_id']); ?>" />
								</div>
								<div class="form-item clearfix">
									<h2>Roles</h2>
									<?php foreach($roles as $role): ?>
									<label class="center-label vert-space">
										<input type="checkbox" class="form-checkbox center-toggle" name="roles[<?php echo hsc($role['id']); ?>]" value="<?php echo hsc($role['id']); ?>" <?php if(in_array($role['name'], $rolesForFormat)): ?>checked="checked"<?php endif; ?> />
										<?php echo hsc($role['name']); ?>
									</label>
									<?php endforeach; ?>
								</div>
								<div class="form-item clearfix">
									<h2 id="active-filters">Active filters</h2>
									<?php foreach($contentFilters as $contentFilter): ?>
									<label id="filter-label-<?php echo hsc($contentFilter['id']); ?>" class="center-label vert-space">
										<input type="checkbox" class="form-checkbox require-one-check validateCheckboxOneOrMore center-toggle" id="filter-<?php echo hsc($contentFilter['id']); ?>" name="filters[<?php echo hsc($contentFilter['id']); ?>]" <?php if(in_array($contentFilter['filter_name'], $activeFilters->name)): ?>checked="checked"<?php endif; ?> />
										<?php echo hsc($contentFilter['filter_name']); ?>
									</label>
									<?php endforeach; ?>
								</div>
								<div class="form-item">
									<h2>Filter processing order</h2>
									<table id="content-filters" class="overview width100pcnt">
										<thead>
											<tr class="overview-top nodrop nodrag">
												<th class="left">Filter name</th>
												<th class="left">Order</th>
											</tr>
										</thead>
										<tbody>
										<?php if(!empty($formatFilters)): ?>
											<?php foreach($formatFilters as $formatFilter): ?>
											<tr id="filter-<?php echo hsc($formatFilter['filter_id']); ?>" class="filter-<?php echo hsc($formatFilter['filter_id']); ?>">
												<td class="left width60pcnt">
													<?php echo hsc($formatFilter['filter_name']); ?>
												</td>
												<td class="left width40pcnt">
													<input type="text" class="filter-order" readonly name="filterOrder[<?php echo hsc($formatFilter['id']); ?>]" value="<?php echo hsc($formatFilter['filter_order']); ?>" size="2" />
												</td>
											</tr>
											<?php endforeach; ?>
										<?php endif; ?>
										<?php if(!empty($inactiveFilters)): ?>
											<?php foreach($inactiveFilters as $inactiveFilter): ?>
											<tr id="filter-<?php echo hsc($formatFilter['filter_id']); ?>" class="filter-<?php echo hsc($inactiveFilter['id']); ?>" style="display: none;">
												<td class="left width60pcnt">
													<?php echo hsc($inactiveFilter['filter_name']); ?>
												</td>
												<td class="left width40pcnt">
													<input type="text" class="filter-order" readonly name="filterOrder[<?php echo hsc($inactiveFilter['id']); ?>]" value="<?php echo hsc($inactiveFilter['filter_order']); ?>" size="2" />
												</td>
											</tr>
											<?php endforeach; ?>
										<?php endif; ?>
										</tbody>
									</table>
								</div>
								<div class="form-item">
									<h2>Filter settings</h2>
									<div id="verticalTabs">
										<ul>
											<li class="verticalTabs-1" <?php if(!in_array("Limit allowed HTML tags", $activeFilters->name)): ?>style="display: none;"<?php endif; ?>>
												<a href="#verticalTabs-1">Limit allowed HTML tags</a>
												<div class="clearfix"> </div>
											</li>
											<li class="verticalTabs-2" <?php if(!in_array("Convert URLs into links", $activeFilters->name)): ?>style="display: none;"<?php endif; ?>>
												<a href="#verticalTabs-2">Convert URLs to HTML links</a>
												<div class="clearfix"> </div>
											</li>
										</ul>
										<div class="vertical-tabs-padding clearfix">
											<div id="verticalTabs-1" <?php if(!in_array("Limit allowed HTML tags", $activeFilters->name)): ?>style="display: none;"<?php endif; ?>>
												<div class="form-item">
													<label class="label-regular width100pcnt">Allowed HTML tags</label>
													<input class="width100pcnt" type="text" name="filter-setting[<?php echo hsc($filterSettings[0]['id']); ?>]" value="<?php echo $filterSettings[0]['filter_value']; ?>" />
													<div class="admin-description">A list of HTML tags that can be used. JavaScript event attributes, JavaScript URLs, and CSS are always stripped.</div>
												</div>
												<div class="form-item">
													<label class="setting-label">
														<input class="hidden" type="hidden" name="filter-setting[<?php echo hsc($filterSettings[1]['id']); ?>]" value="0" />
														<input class="form-checkbox" type="checkbox" name="filter-setting[<?php echo hsc($filterSettings[1]['id']); ?>]" <?php if($filterSettings[1]['filter_value']): ?>checked="checked"<?php endif; ?> value="1" />
														<span>Add rel="nofollow" to all links.</span>
													</label>
												</div>
											</div>
											<div id="verticalTabs-2" <?php if(!in_array("Convert URLs into links", $activeFilters->name)): ?>style="display: none;"<?php endif; ?>>
												<div class="form-item">
													<label class="label-regular width100pcnt">Maximum link length</label>
													<input class="width100pcnt" type="text" name="filter-setting[<?php echo hsc($filterSettings[2]['id']); ?>]" value="<?php echo hsc($filterSettings[2]['filter_value']); ?>" />
													<div>Number of characters</div>
													<br />
													<div class="admin-description">URLs longer than this number of characters will be truncated to prevent long strings that break formatting. The link itself will be retained; just the text portion of the link will be truncated.</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="form-item">
									<button type="submit" class="config-submit rb-btn" name="submitFormatFilters">Save configuration</button>
								</div>
							</div>
						</div>
						<?php elseif(isset($_GET['action']) && $_GET['action'] === 'new'): ?>
						<div class="sub-setting">
							<p>A text format contains filters that change the user input, for example stripping out malicious HTML or making URLs clickable.</p>
							<p>Filters are executed from top to bottom and the order is important, since one filter may prevent another filter from doing its job.</p>
							<p>For example, when URLs are converted into links before disallowed HTML tags are removed, all links may be removed. When this happens, the order of filters may need to be re-arranged.</p>
						</div>
						<div class="sub-setting">
							<div class="form-item">
								<label>
									<span class="bold">Format name</span>
								</label>
								<input type="text" class="normal-text" name="formatName" value="<?php if(isset($newFormat['formatName'])) echo hsc($newFormat['formatName']); ?>" />
							</div>
							<div class="form-item clearfix">
								<h2>Roles</h2>
								<?php foreach($roles as $role): ?>
								<label class=" center-label">
									<input type="checkbox" class="form-checkbox center-toggle" name="roles[<?php echo hsc($role['id']); ?>]" value="<?php echo hsc($role['id']); ?>" <?php if(isset($newformat['roles']) && in_array($newformat['roles']['id'], $newformat['roles'])): ?>checked="checked"<?php endif; ?> />
									<?php echo hsc($role['name']); ?>
								</label>
								<?php endforeach; ?>
							</div>
							<div class="form-item clearfix">
								<h2 id="active-filters">Active filters</h2>
								<?php foreach($contentFilters as $contentFilter): ?>
								<label id="filter-label-<?php echo hsc($contentFilter['id']); ?>" class="center-label">
									<input type="checkbox" class="form-checkbox require-one-check validateCheckboxOneOrMore center-toggle" id="filter-<?php echo hsc($contentFilter['id']); ?>" name="filters[<?php echo hsc($contentFilter['id']); ?>]" <?php if(isset($newformat['filters']) && in_array($contentFilter['filtername'], $newformat['filters'])): ?>checked="checked"<?php endif; ?> />
									<?php echo hsc($contentFilter['filter_name']); ?>
								</label>
								<?php endforeach; ?>
							</div>
							<div class="form-item">
								<h2>Filter processing order</h2>
								<table id="content-filters" class="overview width100pcnt">
									<thead>
										<tr class="overview-top nodrag nodrop">
											<th class="left">Filter name</th>
											<th class="left">Order</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach($contentFilters as $contentFilter): ?>
										<tr id="filter-<?php echo hsc($contentFilter['id']); ?>" class="filter-<?php echo hsc($contentFilter['id']); ?>" style="display: none;">
											<td class="left width60pcnt"><?php echo hsc($contentFilter['filter_name']); ?></td>
											<td class="left width40pcnt"><input type="text" class="filter-order" readonly name="filterOrder[<?php echo hsc($contentFilter['id']); ?>]" value="<?php ?>" size="2" /></td>
										</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
							<div class="form-item">
								<h2>Filter settings</h2>
								<div id="verticalTabs">
									<ul>
										<li class="verticalTabs-1" style="display: none;">
											<a href="#verticalTabs-1">Limit allowed HTML tags</a>
											<div class="clearfix"> </div>
										</li>
										<li class="verticalTabs-2" style="display: none;">
											<a href="#verticalTabs-2">Convert URLs to HTML links</a>
											<div class="clearfix"> </div>
										</li>
									</ul>
									<div class="vertical-tabs-padding clearfix">
										<div id="verticalTabs-1" style="display: none;">
											<div class="form-item">
												<label class="label-regular width100pcnt">Allowed HTML tags</label>
												<input class="width100pcnt" type="text" name="filter-setting[]" value="<?php if(isset($newFormat)) echo '1';?>" />
												<div class="admin-description">A list of HTML tags that can be used. JavaScript event attributes, JavaScript URLs, and CSS are always stripped.</div>
											</div>
											<div class="form-item">
												<label class="setting-label">
													<input class="hidden" type="hidden" name="filter-setting[]" value="0" />
													<input class="form-checkbox" type="checkbox" name="filter-setting[]" <?php if(isset($newformat)): ?>checked="checked"<?php endif; ?> value="1" />
													<span>Add rel="nofollow" to all links.</span>
												</label>
											</div>
										</div>
										<div id="verticalTabs-2" style="display: none;">
											<div class="form-item">
												<label class="label-regular width100pcnt">Maximum link length</label>
												<input class="width100pcnt" type="text" name="filter-setting[]" value="<?php if(isset($newFormat)) echo '3'; ?>" />
												<div>Number of characters</div>
												<br />
												<div class="admin-description">URLs longer than this number of characters will be truncated to prevent long strings that break formatting. The link itself will be retained; just the text portion of the link will be truncated.</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="form-item">
								<button type="submit" class="rb-btn config-submit" name="submitNewFormat">Save configuration</button>
							</div>
						</div>
						<?php else: ?>
						<p>Text formats define the HTML tags, code, and other formatting that can be used when entering text. Improper text format configuration is a security risk.</p>
						<p>Text formats are presented on content editing pages in the order defined on this page. The first format available to a user will be selected by default.</p>
						<br />
						<div class="sub-setting">
							<a href="<?php echo HTTP_ADMIN . 'config/content-filtering/new'; ?>">+ Add new format</a>
						</div>
						<table id="content-formats" class="overview width100pcnt">
							<thead>
								<tr class="overview-top nodrag nodrop">
									<th class="width15pcnt">Format name</th>
									<th class="width70pcnt">Roles</th>
									<th>Order</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($contentFormats as $contentFormat): ?>
								<tr id="format-<?php echo hsc($contentFormat['id']); ?>">
									<td class="cursor-move">
										<a href="<?php echo HTTP_ADMIN . 'config/content-filtering/' . $contentFormat['format_alias'] . '/edit'; ?>"><?php echo hsc($contentFormat['format_name']); ?></a>
									</td>
									<td class="left">
										<?php echo hsc($rolesForFormat[$contentFormat['id']]); ?>
									</td>
									<td>
										<input id="format-<?php echo hsc($contentFormat['id']); ?>" class="format-order" type="text" size="2" readonly value="<?php echo hsc($contentFormat['order_of_item']); ?>" />
									</td>
									<td class="action-column">
										<a href="<?php echo HTTP_ADMIN . 'config/content-filtering/' . $contentFormat['format_alias'] . '/edit'; ?>">Configure</a>
										<span>  | </span>
										<a id="remove-format-<?php echo hsc($contentFormat['id']); ?>" class="removeFormat" href="#">Remove</a>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						<?php endif; ?>
					</div>
					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'media-settings'): ?>
					<div class="element">
						<div class="element-top">Media settings</div>
						<div class="element-body panel-padding setting-group">
							<table class="settings-table">
								<tr>
									<th>
										<label for="thumbnailTemplate">Image template for thumbnails</label>
									</th>
									<td>
										<select id="thumbnailTemplate" name="config[media_thumb_template]">
											<?php foreach($imageTemplates as $imageTemplate): ?>
											<option value="<?php echo hsc($imageTemplate['id']); ?>" <?php if($globalSettings['media_thumb_template']['value'] === $imageTemplate['id']): ?>selected="selected"<?php endif;?>><?php echo hsc($imageTemplate['template_name']); ?></option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th>
										<label for="media-extensions">Allowed extensions for upload</label>
									</th>
									<td>
										<textarea id="media-extensions" name="config[media_allowed_extensions]" rows="4" cols="113"><?php if(!empty($globalSettings['media_allowed_extensions']['value'])): ?><?php echo hsc($globalSettings['media_allowed_extensions']['value']); ?><?php endif; ?></textarea>
									</td>
								</tr>
								<tr>
									<th>
										<label for="mediaFolder">Relative URL for upload storage</label>
									</th>
									<td>
										<input type="text" id="mediaFolder" class="config-text large-text" name="config[media_upload_url]" value="<?php if(isset($globalSettings['media_upload_url'])): echo hsc($globalSettings['media_upload_url']['value']); endif; ?>" />
										<div class="admin-description">
											 Provide the relative URL for the storage of media uploads. If this field is left empty files will be saved in the temporary directory listed in your php.ini file.
										</div>
									</td>
								</tr>
								<tr>
									<th>
										<label for="fullMediaFolder">Full path for upload storage</label>
									</th>
									<td>
										<input type="text" id="fullMediaFolder" class="config-text large-text" name="config[media_full_url]" value="<?php if(isset($globalSettings['media_full_url']['value'])): echo hsc($globalSettings['media_full_url']['value']); endif; ?>" />
										<div class="admin-description">
											 Optionally, you can provide the full URL for the storage of media uploads. The server must be able to write to this directory.
										</div>
									</td>
								</tr>
								<tr>
									<th colspan="2">
										<label for="organizeFolders" class="center-label">
											<input type="hidden" name="config[organize_media_folders]" value="" />
											<input type="checkbox" id="organizeFolders" class="center-toggle" name="config[organize_media_folders]" <?php if(!empty($globalSettings['organize_media_folders']['value'])): ?>checked="checked"<?php endif; ?> />
											Organize folders by month and year
										</label>
									</th>
								</tr>
								<tr>
									<th>
										<button type="submit" class="rb-btn config-submit" name="submitMediaSettings">Submit Configuration</button>
									</th>
								</tr>
							</table>
						</div>
					</div>
					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'image-templates'): ?>
					<div class="element">
						<div class="element-top">Image templates</div>
						<div class="element-body panel-padding setting-group">
							<form action="<?php echo HTTP_ADMIN; ?>config" method="post">
								<table id="template-list" class="overview fltlft width100pcnt">
									<thead>
										<tr class="option-top">
											<th class="left">Template Name</th>
											<th>Image Width</th>
											<th>Image Height</th>
											<th>Type</th>
											<th>Quality</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach($imageTemplates as $key => $template): ?>
										<?php $num = ($key % 2) + 1; ?>
										<tr id="template-<?php echo hsc($template['id']); ?>" class="overview-row<?php echo $num; ?>">
											<td class="left"><?php echo hsc($template['template_name']); ?></td>
											<td><input type="text" id="templateWidth-<?php echo hsc($template['id']); ?>" size="5" value="<?php echo hsc($template['template_width']); ?>" /></td>
											<td><input type="text" id="templateHeight-<?php echo hsc($template['id']); ?>" size="5" value="<?php echo hsc($template['template_height']); ?>" /></td>
											<td>
												<select id="templateType-<?php echo hsc($template['id']); ?>">
												<?php foreach($templateTypes as $templateType): ?>
													<?php if($template['template_type'] === $templateType): ?>
													<option value="<?php echo hsc($template['template_type']); ?>" selected="selected"><?php echo hsc($template['template_type']); ?></option>
													<?php else: ?>
													<option value="<?php echo hsc($templateType); ?>"><?php echo hsc($templateType); ?></option>
													<?php endif; ?>
												<?php endforeach; ?>
												</select>
											</td>
											<td>
												<input type="text" id="templateQuality-<?php echo hsc($template['id']); ?>" size="5" value="<?php echo hsc($template['template_quality']); ?>" />
											</td>
											<td>
												<p><span id="save-template-<?php echo hsc($template['id']); ?>" class="save-template">Save</span> | <span id="delete-template-<?php echo hsc($template['id']); ?>" class="delete-template">Delete</span></p>
											</td>
										</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</form>
							<div class="clear"> </div>
							<ul>
								<li>
									<div id="add-template" class="adder">
										<h4>+ Add Template</h4>
									</div>
									<div id="add-template-form">
										<form action="<?php echo HTTP_ADMIN; ?>config" method="post">
											<label class="adder-label">Template name
												<input class="adder-input" type="text" id="templateName" name="templateName" size="30" />
											</label>
											<label class="adder-label">Width
												<input class="adder-input" type="text" id="templateWidth" name="templateWidth" size="30" />
											</label>
											<label class="adder-label">Height
												<input class="adder-input" type="text" id="templateHeight" name="templateHeight" size="30" />
											</label>
											<label class="adder-label">Type
												<select class="adder-input" id="templateType">
													<?php foreach($templateTypes as $templateType): ?>
													<option value="<?php echo hsc($templateType); ?>"><?php echo hsc($templateType); ?></option>
													<?php endforeach; ?>
												</select>
											</label>
											<label class="adder-label">Quality
												<input class="adder-input" type="text" id="templateQuality" name="templateQuality" size="5" />
											</label>
											<button id="submitNewTemplate" name="submitNewTemplate" class="rb-btn">Create Template</button>
										</form>
									</div>
								</li>
							</ul>
						</div>
					</div>
					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'clean-urls'): ?>

					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'regional-settings'): ?>

					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'global-settings'): ?>
					<div class="element">
						<div class="element-top">Site details</div>
						<div class="element-body pannel-padding">
							<table class="settings-table">
								<tr>
									<th>
										<label for="siteAddress">Site name</label>
									</th>
									<td>
										<input id="siteAddress" type="text" class="config-text normal-text" name="config[site_name]" value="<?php if(isset($globalSettings['site_name']['value'])) echo hsc($globalSettings['site_name']['value']); ?>" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="siteAddress">Site address</label>
									</th>
									<td>
										<input id="siteAddress" type="text" class="config-text normal-text" name="config[site_address]" value="<?php if(isset($globalSettings['site_address']['value'])) echo hsc($globalSettings['site_address']['value']); ?>" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="cmsAddress">Revenant Blue address</label>
									</th>
									<td>
										<input id="cmsAddress" type="text" class="config-text normal-text" name="config[admin_address]" value="<?php if(isset($globalSettings['admin_address']['value'])) echo hsc($globalSettings['admin_address']['value']); ?>" />
										<span class="setting-description">The Revenant Blue address must share the same domain as the site address.
									</td>
								</tr>
								<tr>
									<th>
										<label for="siteTitle">Site title</label>
									</th>
									<td>
										<input id="siteTitle" type="text" class="config-text normal-text" name="config[site_title]" value="<?php if(isset($globalSettings['site_title']['value'])) echo hsc($globalSettings['site_title']['value']); ?>" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="siteDescription">Description</label>
									</th>
									<td>
										<input id="siteDescription" type="text" class="config-text normal-text" name="config[site_description]" value="<?php if(isset($globalSettings['site_description']['value'])) echo hsc($globalSettings['site_description']['value']); ?>" />
										<span class="setting-description">Describe your site in as few words as possible. This field is usually used for the meta description.</span>
									</td>
								</tr>
								<tr>
									<th>
										<label for="system-email">System email</label>
									</th>
									<td>
										<input id="system-email" type="email" class="config-text normal-text" name="config[system_email]" value="<?php if(isset($globalSettings['system_email']['value'])) echo hsc($globalSettings['system_email']['value']); ?>" />
										<span class="setting-description">This is the email address that will show in the From field when sending automated emails.</span>
									</td>
								</tr>
								<tr>
									<th>
										<label for="smtp-port">SMTP Server</label>
									</th>
									<td>
										<input id="smtp-server" type="text" class="config-text normal-text" name="config[smtp_server]" value="<?php if(isset($globalSettings['smtp_server']['value'])) echo hsc($globalSettings['smtp_server']['value']); ?>" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="smtp-port">
											SMTP Port
										</label>
									</th>
									<td>
										<input id="smtp-server" type="text" class="config-text small-text" name="config[smtp_port]" value="<?php if(isset($globalSettings['smtp_port']['value'])) echo hsc($globalSettings['smtp_port']['value']); ?>" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="">Email username</label>
									</th>
									<td>
										<input id="smtp-server" type="text" class="config-text normal-text" name="config[email_username]" value="<?php if(isset($globalSettings['email_username']['value'])) echo hsc($globalSettings['email_username']['value']); ?>" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="">Email password</label>
									</th>
									<td>
										<input id="smtp-server" type="password" class="config-text normal-text" name="config[email_password]" value="<?php if(isset($globalSettings['email_password']['secure_value'])) echo hsc($globalSettings['email_password']['value']); ?>" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="timezone">Timezone</label>
									</th>
									<td>
										<select class="config-text" name="config[timezone]">
										<?php foreach($timezones as $key => $timezoneGrp): ?>
											<optgroup label="<?php echo hsc($key); ?>">
											<?php foreach($timezoneGrp as $timezone): ?>
											<?php if($globalSettings['timezone']['value'] === $key . '/' . $timezone): ?>
											<option style="font-size: 12px;" selected="selected" value="<?php echo $key . '/' . $timezone; ?>"><?php echo hsc((str_replace('_', ' ', $timezone))); ?></option>
											<?php else: ?>
											<option style="font-size: 12px;" value="<?php echo $key . '/' . $timezone; ?>"><?php echo hsc((str_replace('_', ' ', $timezone))); ?></option>
											<?php endif; ?>
											<?php endforeach; ?>
											</optgroup>
										<?php endforeach; ?>
										</select>
										<span class="setting-description">Select a city that shares your timezone.</span>
										<div>
											<span>Daylight Saving time begins on <?php echo date($globalSettings['date_format']['value'], $nextDayLightSavings) . ' at ' . date($globalSettings['time_format']['value'], $nextDayLightSavings); ?></span>
										</div>
									</td>
								</tr>
								<tr>
									<th>
										<label>Date Format</label>
									</th>
									<td>
										<label class="center-label" title="F j, Y">
											<input type="radio" class="center-toggle" value="F j, Y" name="config[date_format]" <?php if($globalSettings['date_format']['value'] === 'f j, y'): ?>checked="checked"<?php endif; ?> />
											<span><?php echo date('F j, Y', $theTime); ?></span>
										</label>
										<label class="center-label" title="Y/m/d">
											<input type="radio" class="center-toggle" value="Y/m/d" name="config[date_format]" <?php if($globalSettings['date_format']['value'] === 'y/m/d'): ?>checked="checked"<?php endif; ?> />
											<span><?php echo date('Y/m/d', $theTime); ?></span>
										</label>
										<label class="center-label" title="m/d/Y">
											<input type="radio" class="center-toggle" value="m/d/Y" name="config[date_format]" <?php if($globalSettings['date_format']['value'] === 'm/d/y'): ?>checked="checked"<?php endif; ?> />
											<span><?php echo date('m/d/Y', $theTime); ?></span>
										</label>
										<label class="center-label" title="d/m/Y">
											<input type="radio" class="center-toggle" value="d/m/Y" name="config[date_format]" <?php if($globalSettings['date_format']['value'] === 'd/m/y'): ?>checked="checked"<?php endif; ?> />
											<span><?php echo date('d/m/Y', $theTime); ?></span>
										</label>
										<label class="center-label" title="custom" style="display: inline-block;">
											<input type="radio"class="center-toggle" name="config[date_format]" <?php if($globalSettings['date_format']['value'] !== "f j, y" && $globalSettings['date_format']['value'] !== 'y/m/d' && $globalSettings['date_format']['value'] !== 'm/d/y' && $globalSettings['date_format']['value'] !== 'd/m/y'): ?>checked="checked"<?php endif; ?> />
											<span>Custom: </span>
										</label>
										<input type="text" class="config-text small-text" name="custom_date_format" value="<?php echo hsc($globalSettings['date_format']['value']); ?>" />
										<span><?php echo hsc(date($globalSettings['date_format']['value'], $theTime)); ?></span>
										<div>
											<a href="http://php.net/manual/en/function.date.php">Description of custom date and time formatting options.</a>
										</div>
									</td>
								</tr>
								<tr>
									<th>
										<label>Time format</label>
									</th>
									<td>
										<label class="center-label" title="g:i a">
											<input type="radio" class="center-toggle" value="g:i a" name="config[time_format]" <?php if($globalSettings['time_format']['value'] === 'g:i a'): ?>checked="checked"<?php endif; ?> />
											<span><?php echo date('g:i a', $theTime); ?></span>
										</label>
										<label class="center-label" title="Y/m/d">
											<input type="radio" class="center-toggle" value="g:i A" name="config[time_format]" <?php if($globalSettings['time_format']['value'] === 'g:i a'): ?>checked="checked"<?php endif; ?> />
											<span><?php echo date('g:i A', $theTime); ?></span>
										</label>
										<label class="center-label" title="m/d/Y">
											<input type="radio" class="center-toggle" value="H:i" name="config[time_format]" <?php if($globalSettings['time_format']['value'] === 'h:i'): ?>checked="checked"<?php endif; ?> />
											<span><?php echo date('H:i', $theTime); ?></span>
										</label>
										<label class="center-label" title="custom" style="display: inline-block;">
											<input type="radio"class="center-toggle" name="config[time_format]" <?php if($globalSettings['time_format']['value'] !== 'g:i a' && $globalSettings['time_format']['value'] !== 'g:i a' && $globalSettings['time_format']['value'] !== 'h:i'): ?>checked="checked"<?php endif; ?> />
											<span>Custom: </span>
										</label>
										<input type="text" class="config-text small-text" name="custom_time_format" value="<?php echo hsc($globalSettings['time_format']['value']); ?>" />
										<span><?php echo hsc(date($globalSettings['time_format']['value'], $theTime)); ?></span>
									</td>
								</tr>
								<tr>
									<td>
										<button type="submit" class="rb-btn config-submit" name="submitSiteInfo">Save configuration</button>
									</td>
								</tr>
							</table>
						</div>
					</div>
					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'scheduled-tasks'): ?>
					<div class="element">
						<div class="element-top">Scheduled Tasks</div>
						<div class="element-body">
							<input type="hidden" id="current-id" />
							<table id="tasks" class="overview width100pcnt left">
								<tr class="overview-top">
									<th class="width1pcnt">
										<input id="selectAll" type="checkbox" class="overview-check-all" />
									</th>
									<th class="width20pcnt">
										Name
									</th>
									<th class="width30pcnt">
										Description
									</th>
									<th>
										Cronjob
									</th>
								</tr>
								<?php if(!empty($tasks)): ?>
								<?php foreach($tasks as $task): ?>
								<tr id="task-<?php echo hsc($task['id']); ?>">
									<td>
										<input id="cb-<?php echo hsc($task['id']); ?>" type="checkbox" class="overview-check" name="taskCheck[]" value="<?php echo hsc($task['id']);?>" />
									</td>
									<td id="task-name-<?php echo hsc($task['id']); ?>">
										<input type="hidden" id="minutes-<?php echo hsc($task['id']); ?>" value="<?php echo hsc($task['minutes']); ?>" />
										<input type="hidden" id="hours-<?php echo hsc($task['id']); ?>" value="<?php echo hsc($task['hours']); ?>" />
										<input type="hidden" id="days-of-month-<?php echo hsc($task['id']); ?>" value="<?php echo hsc($task['days_of_month']); ?>" />
										<input type="hidden" id="months-<?php echo hsc($task['id']); ?>" value="<?php echo hsc($task['months']); ?>" />
										<input type="hidden" id="days-of-week-<?php echo hsc($task['id']); ?>" value="<?php echo hsc($task['days_of_week']); ?>" />
										<input type="hidden" id="command-<?php echo hsc($task['id']); ?>" value="<?php echo hsc($task['command']); ?>" />
										<input type="hidden" id="name-<?php echo hsc($task['id']); ?>" value="<?php echo hsc($task['name']); ?>" />
										<input type="hidden" id="description-<?php echo hsc($task['id']); ?>" value="<?php echo hsc($task['description']); ?>" />
										<input type="hidden" id="log-<?php echo hsc($task['id']); ?>" value="<?php echo hsc($task['log']); ?>" />
										<a id="view-task-<?php echo hsc($task['id']); ?>" class="view-task" href="#">
											<?php echo hsc($task['name']); ?>
										</a>
									</td>
									<td id="task-desc-<?php echo hsc($task['id']); ?>">
										<?php echo hsc($task['description']); ?>
									</td>
									<td id="task-command-<?php echo hsc($task['id']); ?>">
										<?php echo hsc($task['cronjob']); ?>
									</td>
								</tr>
								<?php endforeach; ?>
								<?php endif; ?>
								<tr id="no-tasks" <?php if(!empty($tasks)): ?>style="display: none;"<?php endif; ?>>
									<td colspan="5" class="center">There are no scheduled tasks running.</td>
								</tr>
							</table>
						</div>
					</div>
					<div id="task-modal">
						<div class="inner">
							<div class="errors fltrght"></div>
							<label for="task-name" class="setting-label std-label">Name</label>
							<input type="text" id="task-name" class="normal-text btm-space" />
							<label for="task-desc" class="setting-label std-label">Description</label>
							<textarea id="task-desc" class="normal-text btm-space"></textarea>
							<label for="cronjob" class="setting-label std-label">Command</label>
							
							<div class="clearfix vert-space bottom-space">
								<input type="text" id="cronjob" class="large-text btm-space" />
							</div>
							<label class="std-label btm-space">Task Schedule</label>
							<table id="tasks-schedule" class="overview center vert-space clearfix no-border width100pcnt">
								<tr>
									<td>
										<label>Minutes</label>
									</td>
									<td>
										<label>Hours</label>
									</td>
									<td>
										<label>Days / Month</label>
									</td>
									<td>
										<label>Months</label>
									</td>
									<td>
										<label>Days / Week</label>
									</td>
									<td style="display: none;">
										<label>Years</label>
									</td>
								</tr>
								<tr>
									<td>
										<input type="text" id="minutes" class="small-text center" value="*" />
									</td>
									<td>
										<input type="text" id="hours" class="small-text center" value="*" />
									</td>
									<td>
										<input type="text" id="days-of-month" class="small-text center" value="*" />
									</td>
									<td>
										<input type="text" id="months" class="small-text center" value="*" />
									</td>
									<td>
										<input type="text" id="days-of-week" class="small-text center" value="*" />
									</td>
									<td style="display: none;">
										<input type="text" id="years" class="small-text center" value="*" />
									</td>
								</tr>
								<tr>
									<td colspan="5">
										<div id="task-readable" class="btm-space">
											<div class="inner std-padding">
											
											</div>
										</div>
										<button id="translate-cronjob" class="rb-btn blue-btn">Translate Cronjob</button>
									</td>
								</tr>
							</table>
							<div class="clearfix"></div>
							<label class="std-label top-space">
								Log Output
							</label>
							<label class="setting-label center-label">
								<input type="radio" id="log-file-true" name="logFile" value="1" class="center-toggle" checked="checked" />
								Yes
							</label>
							<label class="setting-label center-label">
								<input type="radio" id="log-file-false" name="logFile" value="0" class="center-toggle" />
								No
							</label>
							<span class="setting-description">All logs will be stored in <code>/system/logs/tasks</code> directory.</span>
						</div>
					</div>
					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'site-security'): ?>
					<div class="element">
						<div class="element-top">HTTPS SSL/TLS options</div>
						<div class="element-body panel-padding setting-group">
							<div class="sub-setting">
								<h2>Front end security</h2>
								<label class="setting-label">
									<input type="hidden" name="config[frontend_ssl]" value="0" />
									<input class="form-checkbox" type="checkbox" value="1" name="config[frontend_ssl]" <?php if($globalSettings['frontend_ssl']['value']): ?>checked="checked"<?php endif; ?> />
									<span>Enable SSL/TLS security on the front end.</span>
								</label>
								<h2>Back end security</h2>
								<label class="setting-label">
									<input type="hidden" name="config[backend_ssl]" value="0" />
									<input class="form-checkbox" type="checkbox" value="1" name="config[backend_ssl]" <?php if($globalSettings['backend_ssl']['value']): ?>checked="checked"<?php endif; ?> />
									<span>Enable SSL/TLS security on the back end.</span>
								</label>
								<span class="setting-description">Please ensure that your server is configured correctly and your domain has an active SSL certificate.</span>
								<div class="form-item">
									<button type="submit" class="rb-btn config-submit" name="submitSiteSecurity">Save configuration</button>
								</div>
							</div>
						</div>
					</div>
					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'personalization'): ?>

					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'performance'): ?>
					<div class="element">
						<div class="element-top">Cache</div>
						<div class="element-body">
							<table id="cache-table" class="overview width100pcnt left">
								<tr class="overview-top">
									<th class="width1pcnt">
										<input id="selectAll" type="checkbox" class="overview-check-all" />
									</th>
									<th>
										Key
									</th>
									<th class="width50pcnt">
										Description
									</th>
								</tr>
								<?php foreach($cacheList as $cache): ?>
								<tr id="cache-key-<?php echo hsc($cache['id']); ?>">
									<td>
										<input id="cb-<?php echo hsc($cache['id']); ?>" type="checkbox" class="overview-check" name="cacheCheck[]" value="<?php echo hsc($cache['id']);?>" />
									</td>
									<td>
										<a id="view-cache-<?php echo hsc($cache['id']); ?>" class="view-cache" href="<?php echo HTTP_ADMIN; ?>config?cache-key=<?php echo hsc($cache['id']); ?>" target="_blank">
											<?php echo hsc($cache['cache_key']); ?>
										</a>
									</td>
									<td>
										<?php echo hsc($cache['description']); ?>
									</td>
								</tr>
								<?php endforeach; ?>
								<tr id="no-cache-keys" <?php if(!empty($cacheList)): ?>style="display: none;"<?php endif; ?>>
									<td colspan="3" class="center">There are no cache keys currently being tracked.</td>
								</tr>
							</table>
						</div>
					</div>
					<div id="add-cache-key-modal">
						<div class="inner">
							<label for="new-cache-key" class="setting-label std-label">Key Name</label>
							<input type="text" id="new-cache-key" class="normal-text btm-space" />
							<label for="new-cache-desc" class="setting-label std-label">Description</label>
							<textarea id="new-cache-desc" class="normal-text btm-space"></textarea>
							<label class="std-label">Serialized</label>
							<label class="setting-label center-label">
								<input type="radio" name="keySerialized" class="center-toggle" value="1" />
								Yes
							</label>
							<label class="setting-label center-label">
								<input type="radio" name="keySerialized" class="center-toggle" value="0" checked="checked" />
								No
							</label>
						</div>
					</div>
					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'logging-and-errors'): ?>
					<div class="element">
						<div class="element-top">Error Log</div>
						<div id="error-log" class="element-body">
						<?php foreach($errorLog as $line): ?>
							<?php echo $line; ?><br />
						<?php endforeach; ?>
						</div>
					</div>
					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'maintenance'): ?>
					<div class="element">
						<div class="element-top">Backups</div>
						<div class="element-body panel-padding setting-group">
							<div id="backup-errors">
							
							</div>
							<div class="sub-setting vert-space clearfix">
								<h2 class="vert-space">Manually backup database</h2>
								<button id="create-backup" class="fltlft rb-btn blue-btn">Backup Database</button>
								<img id="backup-ajax" class="media-ajax block fltrght" alt="loading.." src="<?php echo HTTP_IMAGE; ?>admin/gifs/ajax-loader-snake.gif" style="display: none;" />
								<p id="backup-complete" class="fltlft ajax-complete" style="display: none;">Done!</p>
							</div>
							<div class="sub-setting">
								<h2 class="vert-space">Recover database from backup</h2>
								<table id="backup-list-table" class="overview width100pcnt left">
									<thead>
										<tr class="overview-top">
											<th class="width1pcnt"></th>
											<th>Date</th>
											<th>File</th>
											<th class="width60pcnt">Location</th>
										</tr>
									</thead>
									<tbody id="existing-backups">
										<?php foreach($backupsList as $backup): ?>
										<tr id="backup-<?php echo hsc($backup['id']); ?>" class="backup-row hover-row">
											<td>
												<input type="radio" id="restore-db-<?php echo hsc($backup['id']); ?>" name="restoreDb" value="<?php echo hsc($backup['id']); ?>" />
											</td>
											<td>
												<?php echo hsc(date('d-M-Y h:i A', strtotime($backup['backup_date']))); ?>
											</td>
											<td>
												<?php echo hsc($backup['backup_file']); ?>
											</td>
											<td>
												<?php echo hsc($backup['backup_path']); ?>
											</td>
										</tr>
										<?php endforeach; ?>
										<?php if(empty($backupsList)): ?>
										<tr id="no-database-placeholder">
											<td colspan="4" class="center">There are currently no backups available to be restored.</td>
										</tr>
										<?php endif; ?>
									</tbody>
								</table>
								<div class="vert-space">
									<button id="restore-backup" type="submit" name="restoreBackup" class="rb-btn">Recover Database</button>
									<button id="delete-backup" class="rb-btn">Delete Backup</button>
								</div>
							</div>
							<div class="sub-setting clearfix">
								<h2 class="vert-space">Automatically delete backups older than</h2>
								<div>
									<select id="auto-delete-backups" class="fltlft">
										<option value="never">Never</option>
										<option value="1" <?php if($globalSettings['delete_backups_older_than']['value'] === '1'): ?>selected="selected"<?php endif; ?>>1 Day</option>
										<option value="5" <?php if($globalSettings['delete_backups_older_than']['value'] === '5'): ?>selected="selected"<?php endif; ?>>5 Days</option>
										<option value="7" <?php if($globalSettings['delete_backups_older_than']['value'] === '7'): ?>selected="selected"<?php endif; ?>>7 days</option>
										<option value="10" <?php if($globalSettings['delete_backups_older_than']['value'] === '10'): ?>selected="selected"<?php endif; ?>>10 Days</option>
										<option value="14" <?php if($globalSettings['delete_backups_older_than']['value'] === '14'): ?>selected="selected"<?php endif; ?>>14 Days</option>
										<option value="30" <?php if($globalSettings['delete_backups_older_than']['value'] === '30'): ?>selected="selected"<?php endif; ?>>30 Days</option>
										<option value="90" <?php if($globalSettings['delete_backups_older_than']['value'] === '90'): ?>selected="selected"<?php endif; ?>>90 Days</option>
										<option value="180" <?php if($globalSettings['delete_backups_older_than']['value'] === '180'): ?>selected="selected"<?php endif; ?>>180 Days</option>
										<option value="365" <?php if($globalSettings['delete_backups_older_than']['value'] === '365'): ?>selected="selected"<?php endif; ?>>365 Days</option>
									</select>
									<img id="auto-del-backup-ajax" class="media-ajax block fltrght" alt="loading.." src="<?php echo HTTP_IMAGE; ?>admin/gifs/ajax-loader-snake.gif" style="display: none;" />
									<p id="auto-del-backup-complete" class="fltlft ajax-complete" style="display: none;">Done!</p>
								</div>
							</div>
						</div>
					</div>
					<div class="element">
						<div class="element-top">Maintenance mode</div>
						<div class="element-body panel-padding setting-group">
							<div class="sub-setting">
								<input type="hidden" name="config[maintenance_mode]" value="0" />
								<label class="setting-label">
									<input class="form-checkbox" type="checkbox" value="1" name="config[maintenance_mode]" <?php if($globalSettings['maintenance_mode']['value']): ?>checked="checked"<?php endif; ?> />
									<span>Place the site into maintenance mode.</span>
								</label>
								<span class="setting-description">When enabled, only users with the "Use the site in maintenance mode" permission are able to access your site to perform maintenance; all other visitors see the maintenance mode message configured below. Authorized users can log in directly via the user login page.</span>
								<div class="form-item">
									<h2>Maintenance mode message</h2>
									<label class="settings-label">
										<textarea class="width90pcnt" rows="10" name="config[maintenance_message]"><?php echo hsc($globalSettings['maintenance_message']['value']); ?></textarea>
									</label>
									<span class="setting-description">Message to show visitors when the site is in maintenance mode.</span>
								</div>
								<div class="form-item">
									<button type="submit" class="rb-btn config-submit" name="submitMaintenance">Save configuration</button>
								</div>
							</div>
						</div>
					</div>
					<?php elseif(isset($_GET['setting']) && $_GET['setting'] === 'apc'): ?>
						<?php require_once DIR_SYSTEM . 'library/apc.php'; ?>
					<?php endif; ?>
					<div class="clear"></div>
				</div>
			</div>
		</form>
	</div>
</div>
<?php displayFooter(); ?>
