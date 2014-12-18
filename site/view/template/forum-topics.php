<?php
namespace RevenantBlue\Site;

require_once DIR_APPLICATION . 'controller/forums/forums-c.php';
require_once 'ui.php';
$title = 'Forums | ';
require_once 'head.php';
?>
<?php if(!empty($forumList)): ?>
<?php endif; ?>
<?php loadCKEditor(); ?>
<script type="text/javascript">
var RB = new Object;

RB.forum = {
	forumId     : parseInt("<?php if(isset($currentForum)): echo $currentForum['id']; endif; ?>", 10)
  , topicLimit  : parseInt("<?php if(isset($_GET['limit'])): echo hsc($_GET['limit']); endif; ?>", 10)
  , topicOffset : parseInt("<?php if(isset($_GET['offset'])): echo hsc($_GET['offset']); endif; ?>", 10)
  , numOfTopics : parseInt(<?php if(isset($numOfTopics)): echo $numOfTopics; else: echo '""'; endif;?>, 10)
  , favoriteTopics : <?php if(isset($favoriteTopics)): echo json_encode($favoriteTopics); else: echo '""'; endif; ?>
  , isModerator : "<?php if(!empty($isModerator)): echo TRUE; endif; ?>"
  , moderatorPerms : <?php if(!empty($moderatorPerms)): echo json_encode($moderatorPerms); else: echo '""'; endif; ?>
  , autoloadTopics : false
  , postsPerPage : parseInt("<?php echo $globalSettings['num_of_posts_to_show']['value']; ?>", 10)
};

<?php if(!empty($globalSettings['small_avatar_location']['value'])): ?>
var defaultAvatarSmall = "<?php echo hsc($defaultSmallAvatar); ?>";
<?php endif; ?>

<?php if(!empty($forumList)): ?>
var subForumList = <?php if(!empty($forumList)): echo $forumList; endif; ?>;
<?php endif; ?>
</script>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/forums.js"></script>
<?php loadSiteCss(); ?>
</head>
<body>
	<form id="main-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
		<?php displayHeader(); ?>
		<?php echo $sideNav; ?>
		<nav id="top-menu" class="top-menu clearfix">
			<?php if(!empty($isSubForumContainer)): ?>
			<?php displayBreadCrumbs($currentForum['forum_title'], HTTP_SERVER . 'forums/' . $currentForum['forum_alias'] . '/' . $currentForum['id'], $breadcrumbs); ?>
			<?php elseif(!empty($isForumSection)): ?>
			<?php displayBreadCrumbs($currentForum['forum_title'], HTTP_SERVER . 'forums/' . $currentForum['forum_alias'] . '/' . $currentForum['id'], $breadcrumbs); ?>
			<?php else: ?>
			<?php displayBreadCrumbs($currentForum['forum_title'], HTTP_SERVER . 'forums/' . $currentForum['forum_alias'] . '/' . $currentForum['id'], $breadcrumbs); ?>
			<?php endif; ?>
		</nav>
		<section id="main" class="clearfix" style="visibility: hidden;">
			<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
			<input type="hidden" id="main-form-action" name="action" />
			<div id="main-outer" class="forum-outer clearfix">
				<div id="main-inner">
					<section id="main-content">
						<?php displayNotifications(); ?>
						<div id="forum-head" class="clearfix">
							<?php displayForumSearch(); ?>
						</div>
						<?php if(!isset($_GET['action']) || $_GET['action'] !== 'start-topic'): ?>
							<?php if(!empty($subforumContainer)): ?>
							<ul id="forum">
								<li>
									<div class="root-node clearfix first-root">
										<div class="forum-desc">
											<div class="forum-title">
												<h3>
													<a href="<?php echo HTTP_SERVER; ?>forums/<?php echo hsc($currentForum['forum_alias']) . '/' . hsc($currentForum['id']); ?>">
														<?php echo hsc($currentForum['forum_title']); ?>
													</a>
												</h3>
											</div>
										</div>
									</div>
								</li>
								<li>
									<table id="forum-<?php echo hsc($currentForum['id']); ?>-children" class="forum-children">
										<?php foreach($forumList as $subforum): ?>
										<tr id="forum-<?php echo hsc($subforum['id']); ?>" class="forum-child forum-node forum-depth-1">
											<td id="forum-attribs-<?php echo hsc($subforum['id']); ?>" class="forum-title">
												<h4>
													<a href="<?php echo HTTP_SERVER . 'forums/' . hsc($subforum['forum_alias']) . '/' . hsc($subforum['id']); ?>">
														<?php echo hsc($subforum['forum_title']); ?>
													</a>
												</h4>
												<div id="forum-description-<?php echo hsc($subforum['id']); ?>" class="forum-description">
													<p>
														<?php echo $subforum['forum_description']; ?>
													</p>
												</div>
											</td>
										</tr>
										<?php endforeach; ?>
									</table>
								</li>
							</ul>
							<?php elseif(!empty($forumSection)): ?>
							<ul id="forum" style="display: none;">

							</ul>
							<?php else: ?>
							<?php if(!empty($subforums) && !empty($currentForum['show_subforums'])): ?>
							<ul id="forum" class="subforum-tbl vert-space">
								<li>
									<div class="root-node clearfix first-root subforum-header">
										<div class="forum-desc">
											<div class="forum-title">
												<h3>Subforums</h3>
											</div>
										</div>
									</div>
									<table class="forum-children">
									<?php foreach($subforums as $subforum): ?>
										<tr id="forum-attribs-<?php echo hsc($subforum['id']); ?>" class="forum-child forum-node forum-depth-1">
											<td class="forum-title">
												<h4>
													<a href="<?php echo HTTP_FORUM . hsc($subforum['forum_alias']) . '/' . hsc($subforum['id']); ?>">
														<?php echo hsc($subforum['forum_title']); ?>
													</a>
												</h4>
												<div id="forum-description">
													<?php echo hsc($subforum['forum_description']); ?>
												</div>
											</td>
											<td></td>
											<td class="forum-topic-reply-count">
												<ul>
													<li>
														<?php echo hsc($subforum['num_of_topics']); ?> topics
													</li>
													<li>
														<?php echo hsc($subforum['num_of_posts']); ?> replies
													</li>
												</ul>
											</td>
											<td class="forum-last-post">
												<div class="last-reply-avatar fltlft">
													<a href="<?php echo HTTP_SERVER . 'forums/user/' . $subforum['last_reply_username_alias'] . '/' . $subforum['last_reply_user_id']; ?>">
														<?php if(!empty($subforum['avatar_small'])): ?>
														<img src="<?php echo HTTP_AVATARS . hsc($subforum['avatar_small']); ?>" alt="<?php echo hsc($subforum['last_reply_username']); ?>" title="<?php echo hsc($subforum['last_reply_username']); ?>" />
														<?php elseif(!empty($globalSettings['small_avatar_location']['value']) && !empty($subforum['last_reply_date'])): ?>
														<img src="<?php echo hsc($defaultSmallAvatar); ?>" alt="<?php echo hsc($subforum['last_reply_username']); ?>" title="<?php echo hsc($subforum['last_reply_username']); ?>" />
														<?php else: ?>
														<div class="no-avatar-small">
															<div class="inner">
																NA
															</div>
														</div>
														<?php endif; ?>
													</a>
												</div>
												<ul class="last-reply-info" <?php if(empty($subforum['last_reply_date'])): ?>style="margin-top: 10px;"<?php endif; ?>>
													<?php if(!empty($subforum['last_reply_date'])): ?>
													<li>
														<a href="<?php echo HTTP_FORUM . 'topic/' . $subforum['last_reply_topic_alias'] . '/' . $subforum['last_reply_topic']; ?>">
															<?php echo hsc($subforum['last_reply_topic_title']); ?>
														</a>
													</li>
													<li>
														<a href="<?php echo HTTP_SERVER . 'forums/user/' . $subforum['last_reply_username_alias'] . '/' . $subforum['last_reply_user_id']; ?>">
															<?php echo hsc($subforum['last_reply_username']); ?>
														</a>
													</li>
													<li>
														<?php echo hsc($subforum['last_reply_date']); ?>
													</li>
													<?php else: ?>
													No Posts
													<?php endif; ?>
												</ul>
											</td>
										</tr>
									<?php endforeach; ?>
									</table>
								</li>
							</ul>
							<?php endif; ?>
							<div id="forum-topics-head">
								<div class="inner clearfix">
									<div id="forum-title">
										<h3>
											<?php echo hsc($currentForum['forum_title']); ?>
										</h3>
									</div>
									<ul>
										<li>
											<?php if(!empty($forumPerms['start-topics'])): ?>
											<button id="create-forum-topic" class="rb-btn gray-btn fltrght">Start New Topic</button>
											<?php else: ?>
											<div class="disabled-btn">You cannot start a new topic</div>
											<?php endif; ?>
										</li>
									</ul>
								</div>
							</div>
							<table id="forum-topics">
							<?php if(!empty($topics) || !empty($pinnedTopics)): ?>
								<thead>
									<tr>
										<?php if(!empty($_SESSION['isModerator']) && !empty($isModerator)): ?>
										<th colspan="6">
										<?php else: ?>
										<th colspan="5">
										<?php endif; ?>
											<ul id="topic-order-options">
												<li id="order-by-activity">Last Activity</li>
												<li id="order-by-date">Date Created</li>
												<li id="order-by-replies">Most Replies</li>
												<li id="order-by-views">Most Views</li>
											</ul>
											<ul id="topic-autoloader" class="fltrght">
												<li>
													<label for="autoload-topics-toggle">Autoload Topics: </label>
												</li>
												<li id="autoload-topics-toggle" class="autoload-toggle-wrap toggle-off">
													<div>Off</div>
												</li>
											</ul>
										</th>
									</tr>
									<tr id="topic-banner">
										<th></th>
										<th>Title</th>
										<th>Tags</th>
										<th>Info</th>
										<th>Last Reply</th>
										<?php if(!empty($_SESSION['isModerator']) && !empty($isModerator) && !empty($moderatorPerms[$currentForum['id']])): ?>
										<th>Moderate</th>
										<?php endif; ?>
									</tr>
								</thead>
								<tbody>
									<?php foreach($topics as $topic): ?>
									<tr id="topic-<?php echo hsc($topic['id']); ?>" class="topic-row" data-num-of-posts="<?php echo hsc($topic['num_of_posts']); ?>" data-alias="<?php echo hsc($topic['topic_alias']); ?>" data-paginated="false">
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
												<?php if($currentForum['see_topic_list'] == 1 && empty($forumPerms['read-topics'])): ?>
													<?php echo hsc($topic['topic_title']); ?>
												<?php else: ?>
												<a href="<?php echo HTTP_FORUM . 'topic/' . $topic['topic_alias'] . '/' . $topic['id']; ?>">
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
										<?php if(!empty($_SESSION['isModerator']) && !empty($isModerator) && !empty($moderatorPerms[$topic['forum_id']])): ?>
										<td class="mod-column">
											<div id="mod-opts-wrap-<?php echo hsc($topic['id']); ?>" class="mod-opts-wrap" style="display: none";>
												<div class="mod-option-icon sprites" title="Moderator Actions"></div>
												<span class="arrow-down"></span>
												<ul id="mod-options-<?php echo hsc($topic['id']); ?>" style="display: none;" class="mod-options">
												<?php if(!empty($moderatorPerms[$topic['forum_id']]['edit_topics']) && $moderatorPerms[$topic['forum_id']]['edit_topics']): ?>
													<li id="edit-topic-<?php echo hsc($topic['id']); ?>" class="edit-topics">
														<a href="#">
															Edit title
														</a>
														<span class="ui-icon ui-icon-pencil"></span>
													</li>
												<?php endif; ?>
												<?php if(!empty($moderatorPerms[$topic['forum_id']]['pin_topics']) && $moderatorPerms[$topic['forum_id']]['pin_topics'] && $topic['pinned'] == 0): ?>
													<li id="pin-topic-<?php echo hsc($topic['id']); ?>" class="pin-topics">
														<a href="#">
															Pin
														</a>
														<span class="sprites pin"></span>
													</li>
												<?php elseif(!empty($moderatorPerms[$topic['forum_id']]['unpin_topics']) && $moderatorPerms[$topic['forum_id']]['unpin_topics'] && $topic['pinned'] == 1): ?>
													<li id="unpin-topic-<?php echo hsc($topic['id']); ?>" class="unpin-topics">
														<a href="#">
															Unpin
														</a>
														<span class="sprites unpin"></span>
													</li>
												<?php endif; ?>
												<?php if(!empty($moderatorPerms[$topic['forum_id']]['open_topics']) && $moderatorPerms[$topic['forum_id']]['open_topics'] && $topic['locked'] == 0): ?>
												<li id="lock-topic-<?php echo hsc($topic['id']); ?>" class="lock-topics" <?php if($moderatorPerms[$topic['forum_id']]['open_topics'] && $topic['locked'] == 1): ?>style="display: none;"<?php endif; ?>>
													<a href="#">
														Lock
													</a>
													<span class="ui-icon ui-icon-locked"></span>
												</li>
												<?php elseif(!empty($moderatorPerms[$topic['forum_id']]['close_topics']) && $moderatorPerms[$topic['forum_id']]['close_topics'] && $topic['locked'] == 1): ?>
												<li id="unlock-topic-<?php echo hsc($topic['id']); ?>" class="unlock-topics" <?php if($moderatorPerms[$topic['forum_id']]['close_topics'] && $topic['locked'] == 0): ?>style="display: none;"<?php endif; ?>>
													<a href="#">
														Unlock
													</a>
													<span class="ui-icon ui-icon-unlocked"></span>
												</li>
												<?php endif; ?>
												<?php if(!empty($moderatorPerms[$topic['forum_id']]['move_topics']) && $moderatorPerms[$topic['forum_id']]['move_topics']): ?>
													<li id="move-topic-<?php echo hsc($topic['id']); ?>" class="move-topics">
														<a href="#">
															Move
														</a>
														<span class="ui-icon ui-icon-transferthick-e-w"></span>
													</li>
												<?php endif; ?>
												<?php if(!empty($moderatorPerms[$topic['forum_id']]['toggle_topic_visibility']) && $moderatorPerms[$topic['forum_id']]['toggle_topic_visibility']): ?>
													<li id="hide-topic-<?php echo hsc($topic['id']); ?>" class="hide-topics">
														<a href="#">
															Hide
														</a>
														<span class="ui-icon ui-icon-radio-on"></span>
													</li>
												<?php endif; ?>
												<?php if(!empty($moderatorPerms[$topic['forum_id']]['delete_topics']) && $moderatorPerms[$topic['forum_id']]['delete_topics']): ?>
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
								<tfoot>
									<tr>
										<?php if(!empty($_SESSION['isModerator']) && !empty($isModerator)): ?>
										<td colspan="6" class="load-more">
										<?php else: ?>
										<td colspan="5" class="load-more">
										<?php endif; ?>
											<a id="load-more-topics" href="#" <?php if($numOfTopics <= (int)$_GET['limit'] + (int)$_GET['offset'] ): ?>style="display: none"<?php endif; ?>>
												Load More Topics
											</a>
										</td>
									</tr>
								</tfoot>
							<?php else: ?>
								<tfoot>
									<tr>
										<?php if(empty($forumPerms['read-topics']) && $currentForum['see_topic_list'] != 1): ?>
										<td>You do not have permission to view the topics in this forum.</td>
										<?php else: ?>
										<td class="load-more">No topics have been posted in this forum.</td>
										<?php endif; ?>
									</tr>
								</tfoot>
							<?php endif; ?>
							</table>
							<?php endif; ?>
						<?php endif; ?>
					</section>
				</div>
			</div>
		</section>
		<section id="topic-panel" style="display: none;" class="editor-resizable">
			<div id="panel-max">
				<input type="hidden" id="forum-id" name="forumId" value="<?php echo hsc($currentForum['id']); ?>" />
				<input type="hidden" id="poll-id" name="pollId" />
				<div class="grab-handle ui-resizable-handle ui-resizable-n">
					<span class="grab-handle-icon sprites"></span>
				</div>
				<div class="inner vert-space clearfix">
					<div id="topic-attribs">
						<input type="text" id="topic-title" class="forum-input" name="title" placeholder="A brief, one sentence topic title" />
					</div>
					<div id="topic-options-toggle">
						<span class="sprites gear"></span>
						<span>Topic Options</span>
					</div>
					<div id="editors" class="editor-resizable">
						<div id="editor-wrap">
							<textarea id="forum-editor" name="content" class="editor-resizable"></textarea>
						</div>
						<div id="editor-preview-wrap" class="vert-space">
							<div id="editor-preview" class="editor-resizable"></div>
						</div>
					</div>
					<div class="editor-buttons clearfix">
						<button type="button" id="submit-topic" class="rb-btn blue-btn">Post New Topic</button>
						<button type="button" id="cancel-topic" class="rb-btn light-gray-btn">Cancel</button>
						<a id="toggle-preview" class="fltrght">Hide Preview</a>
					</div>
				</div>
			</div>
			<div id="topic-extras" style="display: none;">
				<div class="inner">
					<ul>
						<li class="topic-extras-head">
							Options
						</li>
						<?php if(!empty($currentForum['polls'])): ?>
						<li>
							<a id="open-poll-mgr" href="#">Manage Topic Poll</a>
						</li>
						<?php endif; ?>
						<li>
							<label for="follow-topic" class="center-label">
								<input type="checkbox" id="follow-topic" class="center-toggle" name="topicOpts[follow]" />
								Follow Topic
							</label>
						</li>
					</ul>
				</div>
			</div>
			<div id="panel-min" style="display:none;">
				<div class="inner">
					<a href="#">You have started a topic</a>
					<span id="maximize-panel-arrow-up"></span>
				</div>
			</div>
		</section>
	</form>
	<div id="poll-manager">
		<div class="inner">
			<form id="poll-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
				<div class="vert-space">
					<label for="poll-title" class="forum-label">
						Poll Title
					</label>
					<input type="text" id="poll-title" class="forum-input" name="pollTitle" />
				</div>
				<div class="vert-space">
					<label class="forum-label">Poll Choices</label>
					<div id="poll-choices">
						<div class="poll-choice-wrap clearfix">
							<input class="poll-choice large-text fltlft" type="text" />
							<a href="#" class="poll-choice-del">Delete</a>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
	<?php if(isset($topic) && !empty($moderatorPerms[$topic['forum_id']]['move_topics']) 
	      && $moderatorPerms[$topic['forum_id']]['move_topics']): ?>
	<div id="move-topic-manager">
		<div class="inner">
			<form id="move-topic-form">
				<label for="move-to-forum">Select a forum</label>
				<select id="move-to-forum" name="moveToForum" size="15">
				<?php foreach($moveTopicForumList as $forumParent): ?>
					<option value="<?php echo hsc($forumParent['id']); ?>" <?php if(!isset($forumParent['moveable'])): ?>disabled="disabled"<?php endif; ?>>
					<?php if((int)$forumParent['root_distance'] > 0): ?>
						<?php echo str_repeat('- ', (int)$forumParent['root_distance']) . $forumParent['forum_title']; ?>
					<?php else: ?>
						<?php echo hsc($forumParent['forum_title']); ?>
					<?php endif; ?>
					</option>
				<?php endforeach; ?>
				</select>
			</form>
		</div>
	</div>
	<?php endif; ?>
<?php displayFooter(); ?>
