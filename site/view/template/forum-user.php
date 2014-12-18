<?php
namespace RevenantBlue\Site;

require_once(DIR_APPLICATION . 'controller/forums/forums-c.php');
require_once 'ui.php';
$title = 'Forums | ';
require_once 'head.php';
?>
<?php loadPurl(); ?>
<script type="text/javascript">
var url = $.url(window.location.href);
</script>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/forums.js"></script>
<?php loadSiteCss(); ?>
</head>
<body>
	<form id="main-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
		<?php displayHeader(); ?>
		<?php echo $sideNav; ?>
		<nav id="top-menu" class="top-menu clearfix">
			<?php displayBreadCrumbs('Viewing Profile: ' . $username, HTTP_SERVER . 'forums/user/' . hsc($_GET['username']) . '/' . (int)$userId, $breadcrumbs); ?>
		</nav>
		<section id="main" style="visibility: hidden">
			<div id="main-outer">
				<div id="main-inner" class="clearfix">
					<section id="main-content">
						<?php displayNotifications(); ?>
						<div id="forum-head" class="clearfix">
							<?php displayForumSearch(); ?>
						</div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="main-form-action" name="action" />
						<section id="forum-profile" class="clearfix">
							<div class="fltlft" id="forum-profile-user-icon">
								<?php if(!empty($user['avatar'])): ?>
								<div class="avatar">
									<img src="<?php echo HTTP_AVATARS . hsc($user['avatar']); ?>" alt="<?php echo hsc($user['username']); ?>'s Avatar" />
								</div>
								<?php elseif(!empty($globalSettings['avatar_location']['value'])): ?>
								<div class="avatar">
									<img src="<?php echo hsc($defaultAvatar); ?>" alt="Default Avatar" />
								</div>
								<?php else: ?>
								<div class="no-avatar">
									<div class="inner">
										<span>No Avatar</span>
									</div>
								</div>
								<?php endif; ?>
							</div>
							<?php if(!empty($myProfile)): ?>
							<div class="fltrght">
								<button id="edit-my-profile" class="rb-btn blue-btn">Edit Profile</button>
							</div>
							<?php elseif(isset($_SESSION['userId'])): ?>
							<div class="fltrght">
								<button id="open-messenger" class="rb-btn light-gray-btn right-space">Send Message</button>
								<input id="friend-id" type="hidden" value="<?php echo hsc($user['id']); ?>" />
								<?php if(!empty($_SESSION['friends']) && array_key_exists($user['id'], $_SESSION['friends'])): ?>
									<button id="remove-friend" class="rb-btn gray-btn">Remove Friend</button>
									<button id="add-friend" class="rb-btn blue-btn" style="display: none;">Add Friend</button>
								<?php else: ?>
									<button id="add-friend" class="rb-btn blue-btn">Add Friend</button>
									<button id="remove-friend" class="rb-btn gray-btn" style="display: none;">Remove Friend</button>
								<?php endif; ?>
							</div>
							<?php endif; ?>
							<div id="forum-profile-general" class="clearfix">
								<div class="inner">
									<h3>
										<?php echo hsc($username); ?>
									</h3>
									<ul>
										<li>
											Member Since: <?php echo date('F, d Y', strtotime($user['date_joined'])); ?>
										</li>
										<li>
											Last Active: <?php echo date('F, d Y - h:i  A T', strtotime($user['last_activity'])); ?>
										</li>
										<li>
										<?php if(!empty($userOnline)): ?>
											<span class="forum-tags user-online">Online</span>
										<?php else: ?>
											<span class="forum-tags user-offline">Offline</span>
										<?php endif; ?>
										</li>
									</ul>
								</div>
							</div>
						</section>
						<nav id="forum-profile-menu">
							<ul>
								<li>
									<a href="#forum-profile-overview">Overview</a>
								</li>
								<?php if($user['topic_post_privacy'] == 0 || ($user['topic_post_privacy'] == 1 && !empty($friendIds) && !empty($_SESSION['userId']) && in_array($_SESSION['userId'], $friendIds))): ?>
								<li>
									<a href="#forum-profile-posts">Posts</a>
								</li>
								<?php endif; ?>
								<?php if($user['topic_post_privacy'] == 0 || ($user['topic_post_privacy'] == 1 && !empty($friendIds) && !empty($_SESSION['userId']) && in_array($_SESSION['userId'], $friendIds))): ?>
								<li>
									<a href="#forum-profile-topics">Topics</a>
								</li>
								<?php endif; ?>
								<?php if($user['show_friends'] == 1): ?>
								<li>
									<a href="#forum-profile-friends">Friends</a>
								</li>
								<?php endif; ?>
							</ul>
							<div id="forum-profile-overview">
								<div class="inner">
									<div id="user-recent-friends" class="element profile-block">
										<div class="element-head">
											Friends
										</div>
										<div class="element-body clearfix">
										<?php if(!empty($recentFriends)): ?>
											<?php foreach($recentFriends as $recentFriend): ?>
												<div id="recent-friend-<?php echo hsc($recentFriend['id']); ?>" class="recent-friend">
													<a href="<?php echo hsc(HTTP_FORUM . 'user/' . $recentFriend['username_alias'] . '/' . $recentFriend['id']); ?>">
														<?php if(!empty($recentFriend['avatar_small'])): ?>
														<img src="<?php echo HTTP_AVATARS . hsc($recentFriend['avatar_small']); ?>" title="<?php echo hsc($recentFriend['username']); ?>" />
														<?php elseif(!empty($globalSettings['small_avatar_location']['value'])): ?>
														<img src="<?php echo hsc($defaultSmallAvatar); ?>" title="<?php echo hsc($recentFriend['username']); ?>" />
														<?php else: ?>
														<div class="no-avatar">
															<div class="inner">
																<span>No Avatar</span>
															</div>
														</div>
														<?php endif; ?>
													</a>
												</div>
											<?php endforeach; ?>
											<em id="no-recent-friends" style="display: none;">
												<?php echo hsc($username); ?> has not added any friends yet
											</em>
										<?php else: ?>
											<em id="no-recent-friends">
												<?php echo hsc($username); ?> has not added any friends yet
											</em>
										<?php endif; ?>
										</div>
									</div>
									<div class="element profile-main">
										<div class="element-head">General</div>
										<div class="element-body">
											<table class="list-table">
												<tr>
													<th>Group</th>
													<td>
														<?php echo hsc($topRole); ?>
													</td>
												</tr>
												<tr>
													<th>Post Count</th>
													<td>
														<?php echo hsc($user['forum_post_count']); ?>
													</td>
												</tr>
												<tr>
													<th>Profile Views</th>
													<td>
														<?php echo $profileViews; ?>
													</td>
												</tr>
												<tr>
													<th>Member Title</th>
													<td>
														<?php echo hsc($memberTitle); ?>
													</td>
												</tr>
												<tr>
													<th>Age</th>
													<td>
														<?php echo hsc($memberAge); ?>
													</td>
												</tr>
												<tr>
													<th>Birthday</th>
													<td>
														<?php echo $birthday; ?>
													</td>
												</tr>
												<?php if(!empty($location)): ?>
												<tr>
													<th>Location</th>
													<td>
														<?php echo hsc($location); ?>
													</td>
												</tr>
												<?php endif; ?>
												<?php if(!empty($interests)): ?>
												<tr>
													<th>Interests</th>
													<td>
														<?php echo hsc($interests); ?>
													</td>
												</tr>
												<?php endif; ?>
											</table>
										</div>
									</div>
									<?php if(!empty($user['about_me'])): ?>
									<div class="element profile-main">
										<div class="element-head">About Me</div>
										<div class="element-body">
											<?php echo $user['about_me']; ?>
										</div>
									</div>
									<?php endif; ?>
									<div class="element profile-main">
										<div class="element-head">Contact Information</div>
										<div class="element-body">
											<ul class="std-list">
												<?php if(!empty($user['aim_contact'])): ?>
												<li>
													<span class="contact-name std-head">
														AIM
													</span>
													<span>
														<?php echo hsc($user['aim_contact']); ?>
													</span>
												</li>
												<?php endif; ?>
												<?php if(!empty($user['facebook_contact'])): ?>
												<li>
													<span class="contact-name std-head">
														Facebook
													</span>
													<span>
														<?php echo hsc($user['facebook_contact']); ?>
													</span>
												</li>
												<?php endif; ?>
												<?php if(!empty($user['googleplus_contact'])): ?>
												<li>
													<span class="contact-name std-head">
														Google+
													</span>
													<span>
														<?php echo hsc($user['googleplus_contact']); ?>
													</span>
												</li>
												<?php endif; ?>
												<?php if(!empty($user['msn_contact'])): ?>
												<li>
													<span class="contact-name std-head">
														MSN
													</span>
													<span>
														<?php echo hsc($user['msn_contact']); ?>
													</span>
												</li>
												<?php endif; ?>
												<?php if(!empty($user['skype_contact'])): ?>
												<li>
													<span class="contact-name std-head">
														Skype
													</span>
													<span>
														<?php echo hsc($user['skype_contact']); ?>
													</span>
												</li>
												<?php endif; ?>
												<?php if(!empty($user['twitter_contact'])): ?>
												<li>
													<span class="contact-name std-head">
														Twitter
													</span>
													<span>
														<?php echo hsc($user['twitter_contact']); ?>
													</span>
												</li>
												<?php endif; ?>
												<?php if(!empty($user['website_contact'])): ?>
												<li>
													<span class="contact-name std-head">
														Website
													</span>
													<span>
														<?php echo hsc($user['website_contact']); ?>
													</span>
												</li>
												<?php endif; ?>
											</ul>
										</div>
									</div>
								</div>
							</div>
							<?php if($user['topic_post_privacy'] == 0 || ($user['topic_post_privacy'] == 1 && !empty($friendIds) && !empty($_SESSION['userId']) && in_array($_SESSION['userId'], $friendIds))): ?>
							<div id="forum-profile-posts">
								<div class="inner">
									<h3>
										<?php echo hsc($username); ?>'s Posts
									</h3>
									<?php if(!empty($recentPosts)): ?>
										<?php foreach($recentPosts as $recentPost): ?>
										<div class="user-post">
											<div class="user-post-topic">
												<a href="<?php echo HTTP_SERVER . 'forums/topic/' . $recentPost['topic_alias'] . '/' . $recentPost['topic_id'] . '/' . $recentPost['id']; ?>#">
													In Topic: <?php echo hsc($recentPost['topic_title']); ?>
												</a>
											</div>
											<div>
												<div class="user-post-date">
													Posted <?php echo nicetime($recentPost['date_posted']); ?>
												</div>
												<div class="post-content">
													<?php echo $recentPost['post_content']; ?>
												</div>
											</div>
										</div>
										<?php endforeach; ?>
									<?php else: ?>
									<p>
										<?php echo hsc($username); ?> has not made any posts yet.
									</p>
									<?php endif; ?>
								</div>
							</div>
							<?php endif; ?>
							<?php if($user['topic_post_privacy'] == 0 || ($user['topic_post_privacy'] == 1 && !empty($friendIds) && !empty($_SESSION['userId']) && in_array($_SESSION['userId'], $friendIds))): ?>
							<div id="forum-profile-topics">
								<div class="inner">
									<h3>
										Topics started by <?php echo hsc($username); ?>
									</h3>
									<?php if(!empty($userTopics)): ?>
										<?php foreach($userTopics as $userTopic): ?>
										<div class="user-topic">
											<a href="<?php echo HTTP_SERVER . 'forums/topic/' . $userTopic['topic_alias'] . '/' . $userTopic['id']; ?>">
												<?php echo hsc($userTopic['topic_title']); ?>
											</a>
											<div class="user-post-date">
												Started <?php echo nicetime($recentPost['date_posted']); ?>
											</div>
										</div>
										<?php endforeach; ?>
									<?php else: ?>
									<p>
										<?php echo hsc($username); ?> has not started any topics.
									</p>
									<?php endif; ?>
								</div>
							</div>
							<?php endif; ?>
							<?php if($user['show_friends'] == 1): ?>
							<div id="forum-profile-friends">
								<div class="inner clearfix">
								<?php if(!empty($friends)): ?>
									<?php foreach($friends as $friend): ?>
									<div id="friend-<?php echo hsc($friend['id']); ?>" class="friend">
										<a href="<?php echo hsc(HTTP_FORUM . 'user/' . $friend['username_alias'] . '/' . $friend['id']); ?>" class="friend-avatar">
											<?php if(!empty($friend['avatar'])): ?>
											<img src="<?php echo HTTP_AVATARS . hsc($friend['avatar']); ?>" title="<?php echo hsc($friend['username']); ?>" />
											<?php elseif(!empty($globalSettings['avatar_location']['value'])): ?>
											<img src="<?php echo hsc($defaultAvatar); ?>" title="<?php echo hsc($friend['username']); ?>" />
											<?php else: ?>
											<div class="no-avatar">
												<div class="inner">
													<span>No Avatar</span>
												</div>
											</div>
											<?php endif; ?>
										</a>
										<a href="<?php echo hsc(HTTP_FORUM . 'user/' . $friend['username_alias'] . '/' . $friend['id']); ?>">
											<?php echo hsc($friend['username']); ?>
										</a>
									</div>
									<?php endforeach; ?>
									<em id="no-friends" style="display: none;">
										<?php echo hsc($username); ?> has not added any friends yet.
									</em>
								<?php else: ?>
									<em id="no-friends">
										<?php echo hsc($username); ?> has not added any friends yet.
									</em>
								<?php endif; ?>
								</div>
							</div>
							<?php endif; ?>
						</nav>
					</section>
				</div>
			</div>
		</section>
	</form>
<?php displayFooter(); ?>
