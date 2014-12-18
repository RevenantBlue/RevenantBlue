<?php
namespace RevenantBlue\Site;

require_once DIR_APPLICATION . 'controller/forums/forums-c.php';
require_once 'ui.php';
$title = 'Forums | ';
require_once 'head.php';
?>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/forums.js"></script>
<script type="text/javascript">
	<?php if(!empty($forumList)): ?>
	var forumList = <?php echo $forumList; ?>;
	<?php else: ?>
	var forumList = '';
	<?php endif; ?>
	
	<?php if(!empty($activeUsers)): ?>
	var activeUsers = [];
	<?php foreach($activeUsers['loggedIn'] as $activeUser): ?>
		activeUsers.push(JSON.parse(<?php echo json_encode($activeUser); ?>));
	<?php endforeach; ?>
	$(document).ready(function() {
		setActiveUsers(activeUsers);
	});
	<?php endif; ?>
	<?php if(!empty($globalSettings['small_avatar_location']['value'])): ?>
	var defaultAvatarSmall = "<?php echo hsc($defaultSmallAvatar); ?>";
	<?php endif; ?>
</script>
<?php loadSiteCss(); ?>
</head>
<body>
	<form id="main-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
		<?php displayHeader(); ?>
		<?php echo $sideNav; ?>
		<nav id="top-menu" class="top-menu clearfix">
			<?php displayBreadCrumbs(); ?>
		</nav>
		<section id="main" class="clearfix" style="visibility: hidden;">
			<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
			<input type="hidden" id="main-form-action" name="action" />
			<div id="main-outer" class="forum-outer">
				<div id="main-inner">
					<section id="main-content">
						<?php if(!empty($forumList)): ?>
						<div id="forum-tabs">
							<ul id="forum-styles" class="clearfix">
								<li>
									<a href="#std-forum">Forum</a>
								</li>
								<li>
									<a href="#recent-topics">
										Recent Topics
									</a>
								</li>
								<?php if(!empty($favoriteTopics)): ?>
								<li>
									<a href="#favorite-topics">
										Favorite Topics
									</a>
								</li>
								<?php endif; ?>
							</ul>
							<div id="std-forum" class="forum-tab">
								<ul id="forum" style="display: none;">
								
								</ul>
							</div>
							<div id="recent-topics" class="forum-tab">
								<table class="forum-topics">
								<?php if(!empty($recentTopics)): ?>
									<thead>
										<tr id="topic-banner">
											<th></th>
											<th>Title</th>
											<th>Tags</th>
											<th>Info</th>
											<th>Last Reply</th>
											<?php if(!empty($_SESSION['isModerator']) && !empty($isModerator)): ?>
											<th>Moderate</th>
											<?php endif; ?>
										</tr>
									</thead>
									<tbody>
										<?php foreach($recentTopics as $topic): ?>
										<tr id="topic-<?php echo hsc($topic['id']); ?>" class="topic-row">
											<td id="fav-<?php echo hsc($topic['id']); ?>" class="fav-stars" title="Add/Remove favorite topic">
												<span class="fav-star <?php if(isset($favoriteTopics) && in_array($topic['id'], $favoriteTopics)): ?>favorited<?php endif; ?>">
													☆
												</span>
												<?php if(!empty($topic['locked']) && (int)$topic['locked'] !== 0): ?>
												<span id="topic-lock-<?php echo hsc($topic['id']); ?>" class="sprites locked"></span>
												<?php endif; ?>
											</td>
											<td id="topic-title-<?php echo hsc($topic['id']); ?>" class="topic-title width40pcnt">
												<h4>
													<?php if($topic['see_topic_list'] == 1 && empty($forumPerms['read-topics'])): ?>
														<?php echo hsc($topic['topic_title']); ?>
													<?php else: ?>
													<a href="<?php echo HTTP_FORUM . 'topic/' . $topic['topic_alias'] . '/' . $topic['id']; ?>">
														<?php echo hsc($topic['topic_title']); ?>
													</a>
													<?php endif; ?>
												</h4>
												<p>
													<span>Started by </span>
													<a href="<?php echo HTTP_FORUM . 'user/' . hsc($topic['username_alias'] . '/' . $topic['user_id']); ?>">
														<?php echo hsc($topic['username']); ?>
													</a>
													<span>, <?php echo nicetime($topic['date_posted']); ?></span>
												</p>
											</td>
											<td id="topic-tags-<?php echo hsc($topic['id']); ?>" class="topic-tags width10pcnt">
												<?php if(!empty($topic['pinned']) && (int)$topic['pinned'] !== 0): ?>
												<span id="pinned-<?php echo hsc($topic['id']); ?>" class="forum-tags pinned">Pinned</span>
												<?php endif; ?>
											</td>
											<td class="topic-info forum-list width10pcnt">
												<ul>
													<li>
														<span>
															<?php echo hsc($topic['num_of_posts']); ?> replies
														</span>
													</li>
													<li>
														<span>
															<?php echo hsc($topic['num_of_views']); ?> views
														</span>
													</li>
												</ul>
											</td>
											<td class="topic-last-reply width20pcnt">
												<div class="last-reply-avatar fltlft">
													<a href="<?php echo HTTP_SERVER . 'forums/user/' . $topic['last_reply_username_alias'] . '/' . $topic['last_reply_user_id']; ?>">
														<?php if(!empty($topic['avatar_small'])): ?>
														<img src="<?php echo HTTP_AVATARS . hsc($topic['avatar_small']); ?>" alt="<?php echo hsc($topic['last_reply_username']); ?>" title="<?php echo hsc($topic['last_reply_username']); ?>" />
														<?php elseif(!empty($globalSettings['small_avatar_location']['value'])): ?>
														<img src="<?php echo hsc($defaultSmallAvatar); ?>" alt="<?php echo hsc($topic['last_reply_username']); ?>" title="<?php echo hsc($topic['last_reply_username']); ?>" />
														<?php else: ?>
														<div class="no-avatar-small">
															<div class="inner">
																NA
															</div>
														</div>
														<?php endif; ?>
													</a>
												</div>
												<ul class="last-reply-info">
													<li>
														<a href="<?php echo HTTP_SERVER . 'forums/user/' . $topic['last_reply_username_alias'] . '/' . $topic['last_reply_user_id']; ?>">
															<?php echo hsc($topic['last_reply_username']); ?>
														</a>
													</li>
													<li>
														<span>
															<?php if(!empty($topic['last_reply_date'])): ?>
																<?php echo nicetime($topic['last_reply_date']); ?>
															<?php else: ?>
																No Replies
															<?php endif; ?>
														</span>
													</li>
												</ul>
											</td>
											<?php if(!empty($_SESSION['isModerator']) && !empty($moderatorPerms) && !empty($topic['canModerate'])): ?>
											<td class="mod-column">
												<div id="mod-opts-wrap-<?php echo hsc($topic['id']); ?>" class="mod-opts-wrap" style="display: none;">
													<div class="mod-option-icon sprites" title="Moderator Actions"></div>
													<span class="arrow-down"></span>
													<ul id="mod-options-<?php echo hsc($topic['id']); ?>" style="display: none;" class="mod-options">
													<?php if($moderatorPerms[$topic['forum_id']]['edit_topics']): ?>
														<li id="edit-topic-<?php echo hsc($topic['id']); ?>" class="edit-topics">
															<a href="#">
																Edit title
															</a>
															<span class="ui-icon ui-icon-pencil"></span>
														</li>
													<?php endif; ?>
													<?php if($moderatorPerms[$topic['forum_id']]['pin_topics'] && $topic['pinned'] == 0): ?>
														<li id="pin-topic-<?php echo hsc($topic['id']); ?>" class="pin-topics">
															<a href="#">
																Pin
															</a>
															<span class="sprites pin"></span>
														</li>
													<?php elseif($moderatorPerms[$topic['forum_id']]['unpin_topics'] && $topic['pinned'] == 1): ?>
														<li id="unpin-topic-<?php echo hsc($topic['id']); ?>" class="unpin-topics">
															<a href="#">
																Unpin
															</a>
															<span class="sprites unpin"></span>
														</li>
													<?php endif; ?>
													<?php if($moderatorPerms[$topic['forum_id']]['close_topics'] && $topic['locked'] == 0): ?>
														<li id="lock-topic-<?php echo hsc($topic['id']); ?>" class="lock-topics">
															<a href="#">
																Lock
															</a>
															<span class="ui-icon ui-icon-locked"></span>
														</li>
													<?php elseif($moderatorPerms[$topic['forum_id']]['open_topics'] && $topic['locked'] == 1): ?>
														<li id="unlock-topic-<?php echo hsc($topic['id']); ?>" class="unlock-topics">
															<a href="#">
																Unlock
															</a>
															<span class="ui-icon ui-icon-unlocked"></span>
														</li>
													<?php endif; ?>
													<?php if($moderatorPerms[$topic['forum_id']]['move_topics']): ?>
														<li id="move-topic-<?php echo hsc($topic['id']); ?>" class="move-topics">
															<a href="#">
																Move
															</a>
															<span class="ui-icon ui-icon-transferthick-e-w"></span>
														</li>
													<?php endif; ?>
													<?php if($moderatorPerms[$topic['forum_id']]['toggle_topic_visibility']): ?>
														<li id="hide-topic-<?php echo hsc($topic['id']); ?>" class="hide-topics">
															<a href="#">
																Hide
															</a>
															<span class="ui-icon ui-icon-radio-on"></span>
														</li>
													<?php endif; ?>
													<?php if($moderatorPerms[$topic['forum_id']]['delete_topics']): ?>
														<li id="delete-topic-<?php echo hsc($topic['id']); ?>" class="delete-topics">
															<a href="#">
																Delete
															</a>
															<span class="ui-icon ui-icon-trash"></span>
														</li>
													<?php endif; ?>
													</ul>
												</div>
											</td>
											<?php endif; ?>
										</tr>
										<?php endforeach; ?>
									</tbody>
								<?php else: ?>
									<tfoot>
										<tr>
											<td class="load-more">No topics have been posted in this forum.</td>
										</tr>
									</tfoot>
								<?php endif; ?>
								</table>
							</div>
							<?php if(!empty($favoriteTopics)): ?>
							<div id="favorite-topics" class="forum-tab">
								<table class="forum-topics">
								<?php if(!empty($favoriteTopics)): ?>
									<thead>
										<tr id="topic-banner">
											<th></th>
											<th>Title</th>
											<th>Tags</th>
											<th>Info</th>
											<th>Last Reply</th>
											<?php if(!empty($_SESSION['isModerator']) && !empty($isModerator)): ?>
											<th>Moderate</th>
											<?php endif; ?>
										</tr>
									</thead>
									<tbody>
										<?php foreach($favoriteTopics as $topic): ?>
										<tr id="topic-<?php echo hsc($topic['id']); ?>" class="topic-row">
											<td id="fav-<?php echo hsc($topic['id']); ?>" class="fav-stars" title="Add/Remove favorite topic">
												<span class="fav-star favorited">
													☆
												</span>
												<?php if(!empty($topic['locked']) && (int)$topic['locked'] !== 0): ?>
												<span id="topic-lock-<?php echo hsc($topic['id']); ?>" class="sprites locked"></span>
												<?php endif; ?>
											</td>
											<td id="topic-title-<?php echo hsc($topic['id']); ?>" class="topic-title width40pcnt">
												<h4>
													<?php if($topic['see_topic_list'] == 1 && empty($forumPerms['read-topics'])): ?>
														<?php echo hsc($topic['topic_title']); ?>
													<?php else: ?>
													<a href="<?php echo HTTP_SERVER . 'forums/topic/' . $topic['topic_alias'] . '/' . $topic['id']; ?>">
														<?php echo hsc($topic['topic_title']); ?>
													</a>
													<?php endif; ?>
												</h4>
												<p>
													<span>Started by </span>
													<a href="<?php echo HTTP_SERVER . 'forums/user/' . hsc($topic['username_alias'] . '/' . $topic['user_id']); ?>">
														<?php echo hsc($topic['username']); ?>
													</a>
													<span>, <?php echo nicetime($topic['date_posted']); ?></span>
												</p>
											</td>
											<td id="topic-tags-<?php echo hsc($topic['id']); ?>" class="topic-tags width10pcnt">
												<?php if(!empty($topic['pinned']) && (int)$topic['pinned'] !== 0): ?>
												<span id="pinned-<?php echo hsc($topic['id']); ?>" class="forum-tags pinned">Pinned</span>
												<?php endif; ?>
											</td>
											<td class="topic-info forum-list width10pcnt">
												<ul>
													<li>
														<span>
															<?php echo hsc($topic['num_of_posts']); ?> replies
														</span>
													</li>
													<li>
														<span>
															<?php echo hsc($topic['num_of_views']); ?> views
														</span>
													</li>
												</ul>
											</td>
											<td class="topic-last-reply width20pcnt">
												<div class="last-reply-avatar fltlft">
													<a href="<?php echo HTTP_SERVER . 'forums/user/' . $topic['last_reply_username_alias'] . '/' . $topic['last_reply_user_id']; ?>">
														<?php if(!empty($topic['avatar_small'])): ?>
														<img src="<?php echo HTTP_AVATARS . hsc($topic['avatar_small']); ?>" alt="<?php echo hsc($topic['last_reply_username']); ?>" title="<?php echo hsc($topic['last_reply_username']); ?>" />
														<?php elseif(!empty($globalSettings['small_avatar_location']['value'])): ?>
														<img src="<?php echo hsc($defaultSmallAvatar); ?>" alt="<?php echo hsc($topic['last_reply_username']); ?>" title="<?php echo hsc($topic['last_reply_username']); ?>" />
														<?php else: ?>
														<div class="no-avatar-small">
															<div class="inner">
																NA
															</div>
														</div>
														<?php endif; ?>
													</a>
												</div>
												<ul class="last-reply-info">
													<li>
														<a href="<?php echo HTTP_SERVER . 'forums/user/' . $topic['last_reply_username_alias'] . '/' . $topic['last_reply_user_id']; ?>">
															<?php echo hsc($topic['last_reply_username']); ?>
														</a>
													</li>
													<li>
														<span>
															<?php if(!empty($topic['last_reply_date'])): ?>
																<?php echo nicetime($topic['last_reply_date']); ?>
															<?php else: ?>
																No Replies
															<?php endif; ?>
														</span>
													</li>
												</ul>
											</td>
											<?php if(!empty($_SESSION['isModerator']) && !empty($isModerator)): ?>
											<td class="mod-column">
												<div id="mod-opts-wrap-<?php echo hsc($topic['id']); ?>" class="mod-opts-wrap" style="display: none";>
													<div class="mod-option-icon sprites" title="Moderator Actions"></div>
													<span class="arrow-down"></span>
													<ul id="mod-options-<?php echo hsc($topic['id']); ?>" style="display: none;" class="mod-options">
													<?php if(!empty($moderatorPerms[$topic['forum_id']])): ?>
														<?php if($moderatorPerms[$topic['forum_id']]['edit_topics']): ?>
															<li id="edit-topic-<?php echo hsc($topic['id']); ?>" class="edit-topics">
																<a href="#">
																	Edit title
																</a>
																<span class="ui-icon ui-icon-pencil"></span>
															</li>
														<?php endif; ?>
														<?php if($moderatorPerms[$topic['forum_id']]['pin_topics'] && $topic['pinned'] == 0): ?>
															<li id="pin-topic-<?php echo hsc($topic['id']); ?>" class="pin-topics">
																<a href="#">
																	Pin
																</a>
																<span class="sprites pin"></span>
															</li>
														<?php elseif($moderatorPerms[$topic['forum_id']]['unpin_topics'] && $topic['pinned'] == 1): ?>
															<li id="unpin-topic-<?php echo hsc($topic['id']); ?>" class="unpin-topics">
																<a href="#">
																	Unpin
																</a>
																<span class="sprites unpin"></span>
															</li>
														<?php endif; ?>
														<?php if($moderatorPerms[$topic['forum_id']]['close_topics'] && $topic['locked'] == 0): ?>
															<li id="lock-topic-<?php echo hsc($topic['id']); ?>" class="lock-topics">
																<a href="#">
																	Lock
																</a>
																<span class="ui-icon ui-icon-locked"></span>
															</li>
														<?php elseif($moderatorPerms[$topic['forum_id']]['open_topics'] && $topic['locked'] == 1): ?>
															<li id="unlock-topic-<?php echo hsc($topic['id']); ?>" class="unlock-topics">
																<a href="#">
																	Unlock
																</a>
																<span class="ui-icon ui-icon-unlocked"></span>
															</li>
														<?php endif; ?>
														<?php if($moderatorPerms[$topic['forum_id']]['move_topics']): ?>
															<li id="move-topic-<?php echo hsc($topic['id']); ?>" class="move-topics">
																<a href="#">
																	Move
																</a>
																<span class="ui-icon ui-icon-transferthick-e-w"></span>
															</li>
														<?php endif; ?>
														<?php if($moderatorPerms[$topic['forum_id']]['toggle_topic_visibility']): ?>
															<li id="hide-topic-<?php echo hsc($topic['id']); ?>" class="hide-topics">
																<a href="#">
																	Hide
																</a>
																<span class="ui-icon ui-icon-radio-on"></span>
															</li>
														<?php endif; ?>
														<?php if($moderatorPerms[$topic['forum_id']]['delete_topics']): ?>
															<li id="delete-topic-<?php echo hsc($topic['id']); ?>" class="delete-topics">
																<a href="#">
																	Delete
																</a>
																<span class="ui-icon ui-icon-trash"></span>
															</li>
														<?php endif; ?>
													<?php endif; ?>
													</ul>
												</div>
											</td>
											<?php endif; ?>
										</tr>
										<?php endforeach; ?>
									</tbody>
								<?php else: ?>
									<tfoot>
										<tr>
											<td class="load-more">No topics have been posted in this forum.</td>
										</tr>
									</tfoot>
								<?php endif; ?>
								</table>
							</div>
							<?php endif; ?>
						</div>
						<?php endif;?>
					</section>
					<?php if(!empty($activeUsers)): ?>
					<div id="forum-stats">
						<p class="bold">
							<?php if($activeUsers['total'] === 1): ?>
								<?php echo $activeUsers['total'] . ' user online (in the past 30 minutes)'; ?>
							<?php else: ?>
								<?php echo $activeUsers['total'] . ' users online (in the past 30 minutes)'; ?>
							<?php endif; ?>
						</p>
						<p>
							<?php if($activeUsers['loggedCount'] === 1): ?>
								<?php echo $activeUsers['loggedCount'] . ' member, ' . $activeUsers['anonCount'] . ' guests'; ?>
							<?php elseif($activeUsers['anonCount'] === 1): ?>
								<?php echo $activeUsers['loggedCount'] . ' members, ' . $activeUsers['anonCount'] . ' guest'; ?>
							<?php else: ?>
								<?php echo $activeUsers['loggedCount'] . ' members, ' . $activeUsers['anonCount'] . ' guests'; ?>
							<?php endif; ?>
						</p>
						<div id="users-online">
						
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</form>
<?php displayFooter(); ?>
