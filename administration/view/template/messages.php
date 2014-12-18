<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/users/cpanel-c.php';
$title = 'Messages';
require_once 'head.php';
require_once 'ui.php';
loadJqueryUi();
loadJqueryValidation();
loadCKEditor();
loadMainJs();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/cpanel.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/cpanel.min.js"></script>
<?php endif; ?>
<script type="text/javascript">
<?php if(isset($_GET['tab'])): ?>
var defaultTab = <?php echo json_encode($_GET['tab']); ?>;
<?php else: ?>
var defaultTab = "";
<?php endif; ?>
</script>
<?php loadMainCss(); ?>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" type="text/css" />
</head>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<span>Messages</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>users/new">
						<span class="ui-icon ui-icon-plus"></span>
						Compose
					</a>
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
		<nav id="cpanel-menu">
			<ul>
				<li>
					<a href="#messages">Messages</a>
				</li>
				<li>
					<a href="#notifications">Notifications</a>
				</li>
				<li>
					<a href="#notification-options">Notification Options</a>
				</li>
			</ul>
			<div id="messages">
				<div class="inner">
					<form id="messages-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
						<div class="fltlft">
							<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						</div>
						<div class="fltrght clearfix">
							<button id="compose-message" class="rb-btn blue-btn horz-space">Compose</button>
							<button id="delete-messages" class="rb-btn light-gray-btn">Delete</button>
						</div>
						<div class="clearfix"></div>
						<nav id="messages-menu">
							<ul id="message-tabs">
								<li>
									<a href="#messages-inbox">Inbox</a>
								</li>
								<li>
									<a href="#messages-sent">Sent</a>
								</li>
							</ul>
							<div id="messages-inbox">
								<table id="inbox" class="messages std-table clearfix vert-space">
									<thead>
										<tr>
											<th class="width1pcnt center">
												<input id="select-all-inbox-msgs" type="checkbox" />
											</th>
											<th>
												Subject
											</th>
											<th class="width20pcnt">
												From
											</th>
											<th class="width15pcnt">
												Received
											</th>
										</tr>
									</thead>
									<tbody>
									<?php if(!empty($inboxMessages)): ?>
										<?php foreach($inboxMessages as $inboxMsg): ?>
										<tr id="inbox-msg-row-<?php echo hsc($inboxMsg['id']); ?>">
											<td>
												<input id="inbox-msg-<?php echo hsc($inboxMsg['id']); ?>" type="checkbox" class="inbox-msg-cb" />
											</td>
											<td>
												<a id="inbox-open-<?php echo hsc($inboxMsg['id']); ?>" class="open-inbox-msg <?php if($inboxMsg['is_read'] == 0): ?>bold<?php endif; ?>" href="#">
													<?php echo hsc($inboxMsg['msg_subject']); ?>
												</a>
												<?php if($inboxMsg['friend_request'] == 1): ?>
												<div id="inbox-msg-request-<?php echo hsc($inboxMsg['id']); ?>">
													<div id="friend-request-<?php echo hsc($inboxMsg['id']); ?>" class="friend-request">
														<?php displayFriendRequest($inboxMsg['sender_username'], $inboxMsg['id']); ?>
													</div>
												</div>
												<?php else: ?>
												<div id="inbox-msg-content-<?php echo hsc($inboxMsg['id']); ?>" class="inbox-msg-content" title="<?php echo hsc($inboxMsg['msg_subject']); ?>">
													<div class="inner">
														<p class="bold">
															<span class="msg-content-sender">
																<?php echo hsc($inboxMsg['sender_username']); ?>
															</span>
															<span>wrote </span>
														</p>
														<?php echo $inboxMsg['msg_content']; ?>
													</div>
												</div>
												<?php endif; ?>
											</td>
											<td id="sender-<?php echo hsc($inboxMsg['id']); ?>">
												<div class="fltlft">
													<a href="<?php echo HTTP_FORUM . 'user/' . $inboxMsg['username_alias'] . '/' . $inboxMsg['sender_id']; ?>">
														<?php if(!empty($inboxMsg['avatar_small'])): ?>
														<img src="<?php echo HTTP_AVATARS . $inboxMsg['avatar_small']; ?>" alt="<?php echo hsc($inboxMsg['sender_username']); ?>" />
														<?php elseif(!empty($globalSettings['default_avatar']['value'])): ?>
														<img src="<?php echo $globalSettings['default_avatar']['value']; ?>" alt="<?php echo hsc($inboxMsg['sender_username']); ?>" />
														<?php else: ?>
														<div class="no-avatar-small">
															<div class="inner">
																NA
															</div>
														</div>
														<?php endif; ?>
													</a>
												</div>
												<div class="avatar-username fltlft">
													<a href="<?php echo HTTP_FORUM . 'user/' . $inboxMsg['username_alias'] . '/' . $inboxMsg['sender_id']; ?>">
														<?php echo hsc($inboxMsg['sender_username']); ?>
													</a>
												</div>
											</td>
											<td>
												<?php echo nicetime($inboxMsg['date_sent']); ?>
											</td>
										</tr>
										<?php endforeach; ?>
									<?php endif; ?>
									<tr id="no-more-inbox-msgs" <?php if(!empty($inboxMessages)): ?>style="display: none"<?php endif; ?>>
										<td class="center" colspan="4">
											You have no messages.
										</td>
									</tr>
									</tbody>
								</table>
							</div>
							<div id="messages-sent">
								<table id="sent-box" class="messages std-table clearfix vert-space">
									<thead>
										<tr>
											<th class="width1pcnt center">
												<input id="select-all-sent-msgs" type="checkbox" />
											</th>
											<th class="width60pcnt">
												Subject
											</th>
											<th>
												Recipient
											</th>
											<th class="width15pcnt">
												Received
											</th>
										</tr>
									</thead>
									<tbody>
									<?php if(!empty($sentMessages)): ?>
										<?php foreach($sentMessages as $sentMsg): ?>
										<tr id="sent-msg-row-<?php echo hsc($sentMsg['id']); ?>">
											<td>
												<input id="sent-msg-<?php echo hsc($sentMsg['id']); ?>" type="checkbox" class="sent-msg-cb" />
											</td>
											<td>
												<a id="sent-msg-open-<?php echo hsc($sentMsg['id']); ?>" class="open-sent-msg" href="#">
													<?php echo hsc($sentMsg['msg_subject']); ?>
												</a>
												<div id="sent-msg-content-<?php echo hsc($sentMsg['id']); ?>" class="sent-msg-content" title="<?php echo hsc($sentMsg['msg_subject']); ?>">
													<div class="inner">
														<p class="bold">
															<span class="msg-content-sender">
																<?php echo hsc($sentMsg['sender_username']); ?>
															</span>
															<span>wrote </span>
														</p>
														<?php echo $sentMsg['msg_content']; ?>
													</div>
												</div>
											</td>
											<td>
												<?php echo hsc($sentMsg['recipient_username']); ?>
											</td>
											<td>
												<?php echo nicetime($sentMsg['date_sent']); ?>
											</td>
										</tr>
										<?php endforeach; ?>
									<?php endif; ?>
									<tr id="no-more-sent-msgs" <?php if(!empty($sentMessages)): ?>style="display: none"<?php endif; ?>>
										<td class="center" colspan="4">
											You have no messages.
										</td>
									</tr>
									</tbody>
								</table>
							</div>
						</nav>
					</form>
				</div>
			</div>
			<div id="notifications">
				<div class="inner">
					<form id="notify-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
						<div class="fltlft">
							<input type="hidden" class="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						</div>
						<div class="fltrght">
							<button id="delete-notifications" class="rb-btn light-gray-btn">Delete</button>
						</div>
						<div class="clearfix"></div>
						<table id="notifications" class="messages std-table clearfix vert-space">
							<thead>
								<tr>
									<th class="width1pcnt center">
										<input id="select-all-notifications" type="checkbox" />
									</th>
									<th class="width70pcnt">
										Notification
									</th>
									<th class="width15pcnt">
										Received
									</th>
								</tr>
							</thead>
							<tbody>
							<?php if(!empty($notifications)): ?>
								<?php foreach($notifications as $notification): ?>
								<tr id="notification-row-<?php echo hsc($notification['id']); ?>">
									<td>
										<input id="notification-<?php echo hsc($notification['id']); ?>" type="checkbox" class="notification-cb" />
									</td>
									<td>
										<span <?php if($notification['is_read'] != 1): ?>class="bold"<?php endif; ?>>
											<?php echo $notification['notification_content']; ?>
										</span>
									</td>
									<td>
										<?php echo nicetime($notification['date_created']); ?>
									</td>
								</tr>
								<?php endforeach; ?>
							<?php endif; ?>
							<tr id="no-more-notifications" <?php if(!empty($notifications)): ?>style="display: none;"<?php endif; ?>>
								<td class="center" colspan="5">
									You have no notifications.
								</td>
							</tr>
							</tbody>
						</table>
					</form>
				</div>
			</div>
			<div id="notification-options">
				<div class="inner">
					<form id="notify-opts-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
						<div class="fltleft clearfix">
							<input type="hidden" class="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						</div>
						<table id="notification-opts-tbl" class="std-table vert-space clearfix">
							<thead>
								<tr>
									<th>
									
									</th>
									<th class="width10pcnt center">
										notification
									</th>
									<th class="width10pcnt center">
										email
									</th>
								</tr>
							</thead>
							<tbody>
								<tr class="section">
									<th colspan="3">
										Topics and Posts
									</th>
								</tr>
								<tr>
									<th>
										When someone replies to a topic I have favorited notify me by
									</th>
									<td>
										<input type="hidden" name="notify[1]" value="off" />
										<input type="checkbox" name="notify[1]"
											   <?php if(!empty($notiOpts[1]) && $notiOpts[1]['by_system'] == 1): ?>
											   checked="checked"
											   <?php endif ?>
										 />
									</td>
									<td>
										<input type="hidden" name="emailNotify[1]" value="off" />
										<input type="checkbox" name="emailNotify[1]"
											   <?php if(!empty($notiOpts[1]) && $notiOpts[1]['by_email'] == 1): ?>
											   checked="checked"
											   <?php endif ?>
										/>
									</td>
								</tr>
								<tr>
									<th>
										When someone @username's me in a post notify me by
									</th>
									<td>
										<input type="hidden" name="notify[2]" value="off" />
										<input type="checkbox" name="notify[2]"
											   <?php if(!empty($notiOpts[2]) && $notiOpts[2]['by_system'] == 1): ?>
											   checked="checked"
											   <?php endif ?>
										/>
									</td>
									<td>
									
									</td>
								</tr>
								<tr>
									<th>
										When someone likes my post notify me by
									</th>
									<td>
										<input type="hidden" name="notify[3]" value="off" />
										<input type="checkbox" name="notify[3]"
											   <?php if(!empty($notiOpts[3]) && $notiOpts[3]['by_system'] == 1): ?>
											   checked="checked"
											   <?php endif ?>
										/>
									</td>
									<td>
									
									</td>
								</tr>
								<tr class="section">
									<th colspan="3">
										Friends
									</th>
								</tr>
								<tr>
									<th>
										When someone adds you as a friend notify me by
									</th>
									<td>
										<input type="hidden" name="notify[4]" value="off" />
										<input type="checkbox" name="notify[4]"
											   <?php if(!empty($notiOpts[4]) && $notiOpts[4]['by_system'] == 1): ?>
											   checked="checked"
											   <?php endif ?>
										/> 
									</td>
									<td>
										<input type="hidden" name="emailNotify[4]" value="off" />
										<input type="checkbox" name="emailNotify[4]"
											   <?php if(!empty($notiOpts[4]) && $notiOpts[4]['by_email'] == 1): ?>
											   checked="checked"
											   <?php endif ?>
										/>
									</td>
								</tr>
								<tr>
									<th>
										When someone accepts my friend request notify me by
									</th>
									<td>
										<input type="hidden" name="notify[5]" value="off" />
										<input type="checkbox" name="notify[5]"
											   <?php if(!empty($notiOpts[5]) && $notiOpts[5]['by_system'] == 1): ?>
											   checked="checked"
											   <?php endif ?>
										/>
									</td>
									<td>
										<input type="hidden" name="emailNotify[5]" value="off" />
										<input type="checkbox" name="emailNotify[5]"
											   <?php if(!empty($notiOpts[5]) && $notiOpts[5]['by_email'] == 1): ?>
											   checked="checked"
											   <?php endif ?>
										/>
									</td>
								</tr>
								<tr class="section">
									<th colspan="3">
										Private Messages
									</th>
								</tr>
								<tr>
									<th>
										When I am sent a private message notify me by
									</th>
									<td>
										<input type="hidden" name="notify[6]" value="off" />
										<input type="checkbox" name="notify[6]"
											   <?php if(!empty($notiOpts[6]) && $notiOpts[6]['by_system'] == 1): ?>
											   checked="checked"
											   <?php endif ?>
										/>
									</td>
									<td>
										<input type="hidden" name="emailNotify[6]" value="off" />
										<input type="checkbox" name="emailNotify[6]"
											   <?php if(!empty($notiOpts[6]) && $notiOpts[6]['by_email'] == 1): ?>
											   checked="checked"
											   <?php endif ?>
										/>
									</td>
								</tr>
							</tbody>
						</table>
						<div class="vert-space">
							<button id="update-notification-opts" type="submit" class="rb-btn blue-btn standout-space" name="updateNotificationOpts">
								Update Notification Options
							</button>
						</div>
					</form>
				</div>
			</div>
		</nav>
	</div>
</div>
<div id="message-composer" style="display: none;" title="Compose Message">
	<form id="message-form" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<div id="message-composer-errors"></div>
		<div id="pm-form">
			<div class="btm-space">
				<label for="message-recipient" class="upper-label rb-dialog-label">Recipient</label>
				<input type="hidden" id="user-exists" />
				<input type="text" id="message-recipient" class="normal-text rb-dialog-input" name="msgRecipient" />
			</div>
			<div class="btm-space">
				<label for="message-subject" class="rb-dialog-label">Subject</label>
				<input type="text" id="message-subject" class="large-text rb-dialog-input" name="msgSubject" />
			</div>
			<div class="top-space btm-space">
				<textarea id="message-editor" name="msgContent"></textarea>
			</div>
		</div>
		<div id="pm-sent" style="display: none">
			Message Sent!
		</div>
	</form>
</div>
<?php displayFooter(); ?>
