<?php
namespace RevenantBlue\Site;

require_once(DIR_APPLICATION . 'controller/users/user-cpanel-c.php');
require_once 'ui.php';
$title = 'Control Panel | ';
require_once 'head.php';
?>
<?php loadCKEditor(); ?>
<?php loadPlupload(); ?>
<?php loadPurl(); ?>
<script type="text/javascript">
// Set the max avatar size and the tab name.
var url = $.url(window.location.href)
  , maxAvatarSize = "<?php if(isset($maxAvatarSize)): echo $maxAvatarSize; endif; ?>"
  , tab = url.segment(2)
  , sendToUsername;

<?php if(!empty($friendUsernames)): ?>
var friendUsernamesObj = <?php echo json_encode($friendUsernames); ?>
  , friendUsernames = [], friendId;

for(friendId in friendUsernamesObj) {
    friendUsernames.push(friendUsernamesObj[friendId]);
}
<?php endif; ?>
</script>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/cpanel.js"></script>
<script type="text/javascript">
$(window).load(function() {
// If sending a private message to a specific user autofill that user's name in the message recipient text input.
<?php if(!empty($_GET['sendTo'])): ?>
	<?php if(!empty($sendToUsername)): ?>
		sendToUsername = "<?php echo hsc($sendToUsername); ?>";
		//console.log(sendToUsername);
		$("#message-recipient").val(sendToUsername);
	<?php endif; ?>
	$("#message-composer").dialog("open");
	$("#message-subject").focus();
<?php endif; ?>
});
</script>
<?php loadSiteCss(); ?>
</head>
<body>
	<div id="main-form-placeholder">
		<?php displayHeader(); ?>
		<?php echo $sideNav; ?>
		<nav id="top-menu" class="top-menu clearfix">
			<?php displayBreadCrumbs(); ?>
		</nav>
		<section id="main" style="visibility: hidden;">
			<div id="main-outer" class="fluid-wrapper">
				<div id="main-inner" class="clearfix">
					<section id="main-content">
						<?php displayNotifications(); ?>
						<section id="control-panel">
							<nav id="cpanel-menu">
								<ul>
									<li>
										<a href="#cpanel-general">General</a>
									</li>
									<li>
										<a href="#cpanel-profile">Profile Settings</a>
									</li>
									<li>
										<a href="#cpanel-messages">Messages</a>
									</li>
									<li>
										<a href="#cpanel-notify">Notifications</a>
									</li>
									<li>
										<a href="#cpanel-notify-opts">Notification Options</a>
									</li>
								</ul>
								<div id="cpanel-general">
									<div class="inner">
										<form id="general-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
											<div>
												<input type="hidden" class="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
												<h2>General Settings</h2>
											</div>
											<table class="std-table">
												<tbody>
													<tr>
														<th>Email Address</th>
														<td>
															<input type="email" class="normal-text" name="email" value="<?php echo hsc($user['email']); ?>" />
														</td>
													</tr>
													<tr>
														<th>First Name</th>
														<td>
															<input type="text" class="normal-text" name="firstName" value="<?php echo hsc($user['first_name']); ?>" />
														</td>
													</tr>
													<tr>
														<th>Last Name</th>
														<td>
															<input type="text" class="normal-text" name="lastName" value="<?php echo hsc($user['last_name']); ?>" />
														</td>
													</tr>
												</tbody>
											</table>
											<div class="vert-space">
												<button id="general-settings-btn" type="submit" name="updateGeneral" class="rb-btn blue-btn top-space">
													Update General Settings
												</button>
											</div>
										</form>
									</div>
								</div>
								<div id="cpanel-profile">
									<div class="inner">
										<form id="profile-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
											<div class="clearfix">
												<input type="hidden" class="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
												<h2 class="fltlft">Profile Settings</h2>
												<a class="fltrght" href="<?php echo HTTP_SERVER . 'forums/user/'. $user['username_alias'] . '/' . $user['id']; ?>">
													View Forum Profile
												</a>
											</div>
											<table class="std-table profile-table">
												<tr>
													<th>
														Avatar
													</th>
													<td>
														<?php if(!empty($user['avatar'])): ?>
														<div class="avatar fltlft">
															<img src="<?php echo HTTP_AVATARS . hsc($user['avatar']); ?>" alt="<?php echo hsc($user['username']); ?>'s Avatar" />
														</div>
														<?php elseif(!empty($globalSettings['avatar_location']['value'])): ?>
														<div class="avatar fltlft">
															<img src="<?php echo hsc($defaultAvatar); ?>" alt="Default Avatar" />
														</div>
														<?php else: ?>
														<div class="no-avatar fltlft">
															<div class="inner">
																<span>No Avatar</span>
															</div>
														</div>
														<?php endif; ?>
														<div id="avatar-upload">
															<input id="upload-avatar" type="file" style="display: none;" />
															<button id="upload-avatar-btn" class="rb-btn blue-btn bottom-space">Upload Avatar</button>
														</div>
														<div class="avatar-upload-info">
															<p>Avatar must be less than or equal to <?php echo hsc($maxAvatarSize); ?>kb</p>
															<p>Avatar dimensions: <?php echo hsc($avatarWidth); ?>px &times; <?php echo hsc($avatarHeight); ?>px</p>
														</div>
													</td>
												</tr>
												<tr>
													<th>Timezone</th>
													<td>
														<select class="input_select" id="timezone" name="timezone">
															<?php foreach($timezones as $offset => $timezone): ?>
															<option <?php if((int)$user['timezone'] === $offset): ?>selected="selected"<?php endif; ?> value="<?php echo $offset; ?>">
																<?php echo hsc($timezone); ?>
															</option>
															<?php endforeach; ?>
														</select>
													</td>
												</tr>
												<tr>
													<th class="valign-top">
														Privacy
													</th>
													<td>
														<div class="bottom-space">
															<span>
																Who can view your topics and posts in your profile
															</span>
														</div>
														<ul class="std-list">
															<li>
																<label class="center-label">
																	<input type="radio" class="center-toggle" name="privacy[show_posts_topics]" value="0" 
																		   <?php if($user['topic_post_privacy'] == 0): ?>
																		   checked="checked"
																		   <?php endif; ?>
																	/>
																	Everyone
																</label>
															</li>
															<li>
																<label class="center-label">
																	<input type="radio" class="center-toggle" name="privacy[show_posts_topics]" value="1"
																		   <?php if($user['topic_post_privacy'] == 1): ?>
																		   checked="checked"
																		   <?php endif; ?>
																	/>
																	Friends Only
																</label>
															</li>
															<li>
																<label class="center-label">
																	<input type="radio" class="center-toggle" name="privacy[show_posts_topics]" value="2" 
																		   <?php if($user['topic_post_privacy'] == 2): ?>
																		   checked="checked"
																		   <?php endif; ?>
																	/>
																	Nobody
																</label>
															</li>
														</ul>
													</td>
												</tr>
												<tr>
													<th class="valign-top">
														<label for="user-friends">Friends</label>
													</th>
													<td>
														<ul class="std-list">
															<li>
																<label class="center-label">
																	<input type="checkbox" id="user-show-friends" name="showFriends" class="center-toggle" 
																		   <?php if($user['show_friends'] == 1): ?>
																		   checked="checked"
																		   <?php endif; ?>
																	/>
																	Show my friends in my profile
																</label>
															</li>
															<li>
																<label class="center-label">
																	<input type="checkbox" id="user-authorize-friends" name="authorizeFriends" class="center-toggle"
																		<?php if($user['authorize_friends'] == 1): ?>
																		checked="checked"
																		<?php endif; ?>
																	/>
																	Require friend request authorization
																</label>
															</li>
														</ul>
													</td>
												</tr>
												<tr>
													<th>
														Birthday
													</th>
													<td>
														<div class="fltlft right-space">
															<label for="birth-month" class="upper-label">Month</label>
															<select id="birth-month" name="birthMonth">
																<option <?php if(empty($bdayMonth)): ?>selected="selected"<?php endif; ?> value="0">
																	--
																</option>
																<?php foreach($birthMonths as $key => $birthMonth): ?>
																<option <?php if($bdayMonth === $key): ?>selected="selected"<?php endif; ?> value="<?php echo $key; ?>">
																	<?php echo $birthMonth; ?>
																</option>
																<?php endforeach; ?>
															</select>
														</div>
														<div class="fltlft right-space">
															<label for="birth-day" class="upper-label">Day</label>
															<select id="birth-day" name="birthDay">
																<option <?php if(empty($bdayDay)): ?>selected="selected"<?php endif; ?> value="">
																	--
																</option>
																<?php foreach($birthDays as $birthDay): ?>
																<option <?php if($bdayDay === $birthDay): ?>selected="selected"<?php endif; ?> value="<?php echo hsc($birthDay); ?>">
																	<?php echo $birthDay; ?>
																</option>
																<?php endforeach; ?>
															</select>
														</div>
														<div class="fltlft right-space">
															<label for="birth-year" class="upper-label">Year</label>
															<select id="birth-year" name="birthYear">
																<option <?php if(empty($bdayYear)): ?>selected="selected"<?php endif; ?> value="">
																	--
																</option>
																<?php foreach($birthYears as $birthYear): ?>
																<option <?php if($bdayYear === $birthYear): ?>selected="selected"<?php endif; ?> value="<?php echo $birthYear; ?>">
																	<?php echo $birthYear; ?>
																</option>
																<?php endforeach; ?>
															</select>
														</div>
													</td>
												</tr>
												<tr>
													<th>
														<label for="user-gender">Gender</label>
													</th>
													<td>
														<select id="user-gender" name="gender">
															<option <?php if(empty($user['gender'])): ?>selected="selected"<?php endif; ?> value="">
																--
															</option>
															<option <?php if($user['gender'] === 'm'): ?>selected="selected"<?php endif; ?> value="m">
																Male
															</option>
															<option	<?php if($user['gender'] === 'f'): ?>selected="selected"<?php endif; ?> value="f">
																Female
															</option>
														</select>
													</td>
												</tr>
												<tr>
													<th>
														<label for="user-location">Location</label>
													</th>
													<td>
														<input id="user-location" type="text" name="location" class="normal-text"
															   <?php if(isset($user['location'])): ?>
															   value="<?php echo hsc($user['location']); ?>"
															   <?php endif; ?>
														/>
													</td>
												</tr>
												<tr>
													<th>
														Interests
													</th>
													<td>
														<input id="user-interests" type="text" name="interests" class="large-text"
															   <?php if(isset($user['interests'])): ?>
															   value="<?php echo hsc($user['interests']); ?>"
															   <?php endif; ?>
														/>
													</td>
												</tr>
												<tr>
													<th class="valign-top">
														Contact Methods
													</th>
													<td>
														<ul id="contact-methods" class="std-list">
															<li>
																<label for="aim-contact" class="upper-label">AIM</label>
																<input id="aim-contact" type="text" name="aim" class="normal-text"
																	   <?php if(isset($user['aim_contact'])): ?>
																	   value="<?php echo hsc($user['aim_contact']); ?>"
																	   <?php endif; ?>
																/>
															</li>
															<li>
																<label for="facebook-contact" class="upper-label">Facebook</label>
																<input id="facebook-contact" type="text" name="facebook" class="normal-text" value="<?php echo hsc($user['facebook_contact']); ?>" />
															</li>
															<li>
																<label for="googleplus-contact" class="upper-label">Google+</label>
																<input id="googleplus-contact" type="text" name="googleplus" class="normal-text" value="<?php echo hsc($user['googleplus_contact']); ?>" />
															</li>
															<li>
																<label for="msn-contact" class="upper-label">MSN</label>
																<input id="msn-contact" type="text" name="msn" class="normal-text" value="<?php echo hsc($user['msn_contact']); ?>" />
															</li>
															<li>
																<label for="skype-contact" class="upper-label">Skype</label>
																<input id="skype-contact" type="text" name="skype" class="normal-text" value="<?php echo hsc($user['skype_contact']); ?>" />
															</li>
															<li>
																<label for="twitter-contact" class="upper-label">Twitter</label>
																<input id="twitter-contact" type="text" name="twitter" class="normal-text" value="<?php echo hsc($user['twitter_contact']); ?>" />
															</li>
															<li>
																<label for="website-contact" class="upper-label">Website URL</label>
																<input id="website-contact" type="text" name="website" class="normal-text" value="<?php echo hsc($user['website_contact']); ?>" />
															</li>
														</ul>
													</td>
												</tr>
												<tr>
													<th class="valign-top">About Me</th>
													<td>
														<textarea id="about-me-editor" name="aboutMe"><?php echo $user['about_me']; ?></textarea>
													</td>
												</tr>
												<tr>
													<th colspan="2">
														<button id="update-forum-profile" type="submit" class="rb-btn blue-btn top-space" name="updateProfile" value="1">Update Profile</button>
													</th>
												</tr>
											</table>
										</form>
									</div>
								</div>
								<div id="cpanel-messages">
									<div class="inner">
										<form id="messages-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
											<div class="fltlft">
												<input type="hidden" class="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
												<h2>Messages</h2>
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
																	<div id="inbox-msg-content-<?php echo hsc($inboxMsg['id']); ?>" class="inbox-msg-content msg-content" title="<?php echo hsc($inboxMsg['msg_subject']); ?>">
																		<div class="inner">
																			<p class="bold">
																				<strong class="msg-content-sender">
																					<?php echo hsc($inboxMsg['sender_username']); ?> wrote
																				</strong>
																			</p>
																			<div id="msg-content-<?php echo hsc($inboxMsg['id']); ?>">
																				<?php echo $inboxMsg['msg_content']; ?>
																			</div>
																		</div>
																	</div>
																	<?php endif; ?>
																</td>
																<td id="sender-<?php echo hsc($inboxMsg['id']); ?>">
																	<div class="fltlft">
																		<a href="<?php echo HTTP_FORUM . 'user/' . $inboxMsg['username_alias'] . '/' . $inboxMsg['sender_id']; ?>">
																			<?php if(!empty($inboxMsg['avatar_small'])): ?>
																			<img src="<?php echo HTTP_AVATARS . $inboxMsg['avatar_small']; ?>" alt="<?php echo hsc($inboxMsg['sender_username']); ?>" />
																			<?php elseif(!empty($globalSettings['small_avatar_location']['value'])): ?>
																			<img src="<?php echo hsc($defaultSmallAvatar); ?>" alt="<?php echo hsc($inboxMsg['sender_username']); ?>" />
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
																	<div id="sent-msg-content-<?php echo hsc($sentMsg['id']); ?>" class="sent-msg-content msg-content" title="<?php echo hsc($sentMsg['msg_subject']); ?>">
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
								<div id="cpanel-notify">
									<div class="inner">
										<form id="notify-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
											<div class="fltlft">
												<input type="hidden" class="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
												<h2>Notifications</h2>
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
								<div id="cpanel-notify-opts">
									<div class="inner">
										<form id="notify-opts-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
											<div class="fltleft clearfix">
												<input type="hidden" class="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
												<h2>Notification Options</h2>
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
						</section>
					</section>
				</div>
			</div>
		</section>
	</div>
	<div id="message-composer" style="display: none;" title="Compose Message">
		<form id="message-form" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<div id="message-composer-errors"></div>
			<div id="pm-form">
				<div class="bottom-space">
					<label for="message-recipient" class="upper-label">Recipient</label>
					<input type="hidden" id="user-exists" />
					<input type="text" id="message-recipient" class="normal-text" name="msgRecipient" />
				</div>
				<div class="bottom-space">
					<label for="message-subject" class="upper-label">Subject</label>
					<input type="text" id="message-subject" class="large-text" name="msgSubject" />
				</div>
				<div class="top-space bottom-space">
					<textarea id="message-editor" name="msgContent"></textarea>
				</div>
			</div>
			<div id="pm-sent" style="display: none">
				Message Sent!
			</div>
		</form>
	</div>
<?php displayFooter(); ?>

