<?php
namespace RevenantBlue\Site;

require_once(DIR_APPLICATION . 'controller/forums/forums-c.php');
require_once 'ui.php';
$title = 'Forums | ';
require_once 'head.php';
?>
<?php loadPurl(); ?>
<script type="text/javascript">
	var limit = parseInt('<?php echo (int)$_GET['limit']; ?>', 10)
	  , offset = parseInt('<?php echo (int)$_GET['offset']; ?>', 10)
	  , lastPostNum = parseInt('<?php if(isset($lastPostNum)): echo hsc($lastPostNum); endif; ?>', 10)
	  , numOfPosts = parseInt('<?php echo hsc($globalSettings['num_of_posts_to_show']['value']); ?>', 10)
		<?php if((int)$_GET['offset'] === 0): ?>
	  , postStart = 'beginning'
		<?php else: ?>
	  , postStart = 'end'
		<?php endif; ?>
	  , noMorePostsUp = '<?php if(isset($noMorePostsUp)) echo $noMorePostsUp; ?>'
	  , noMorePostsDown = '<?php if(isset($noMorePostsDown)) echo $noMorePostsDown; ?>'
	  , firstPost = '<?php if(isset($firstPost)) echo $firstPost; ?>'
	  , url = $.url(window.location.href)
	  <?php if(!empty($globalSettings['avatar_location']['value'])): ?>
	  , defaultAvatar = '<?php echo hsc($defaultAvatar); ?>'
	  <?php endif; ?>
	  , autoloadPosts = false;
	  
	<?php if(!empty($topicJSON)) :?>
	var topic = <?php echo $topicJSON; ?>
	<?php endif; ?>
	  
	<?php if(!empty($forumJSON)): ?>
	var forum = <?php echo $forumJSON; ?>
	<?php endif; ?>
	  
	<?php if(!empty($poll)): ?>
	var poll = JSON.parse('<?php echo $poll; ?>');
	
		<?php if(!empty($hasVoted)): ?>
		var hasVoted = true;
		<?php endif; ?>
	
	$(document).ready(function() {
		buildPoll(poll);
	});
	<?php endif; ?>
	
	var upLimit = limit
	  , downLimit = limit
	  , upOffset = offset
	  , downOffset = offset;
</script>
<?php loadCKEditor(); ?>
<?php loadLoadingDots(); ?>
<?php loadStickyFloat(); ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/forums.js"></script>
<?php loadSiteCss(); ?>
</head>
<body>
	<form id="main-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
		<?php displayHeader(); ?>
		<?php echo $sideNav; ?>
		<nav id="top-menu" class="top-menu clearfix">
			<?php displayBreadCrumbs(substr($topic['topic_title'], 0, 60), HTTP_SERVER . 'forums/topic/' . $topic['topic_alias'] . '/' . $topic['id'], $breadcrumbs); ?>
		</nav>
		<section id="main" class="clearfix" style="visibility: hidden;">
			<div id="main-outer" class="forum-outer clearfix">
				<div class="load-more-posts-up"></div>
				<div id="main-inner" class="clearfix">
					<section id="main-content" class="main-forum-content">
						<?php displayNotifications(); ?>
						<div id="forum-head" class="clearfix">
							<?php displayForumSearch(); ?>
						</div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="main-form-action" name="action" />
						<div id="forum-posts-head" class="clearfix">
							<div class="inner clearfix">
								<div id="topic-title">
									<h3>
										<?php echo hsc($topic['topic_title']); ?>
									</h3>
								</div>
								<ul id="forum-posts-btns-top">
									<li>
										<?php if(!empty($forumPerms['reply-topics'])): ?>
										<button class="create-forum-post rb-btn gray-btn fltrght">Reply to topic</button>
										<?php else: ?>
										<div class="disabled-btn fltrght">You cannot reply to this topic</div>
										<?php endif; ?>
									</li>
								</ul>
							</div>
						</div>
						<?php if($currentForum['polls']): ?>
						<div id="forum-poll" style="display: none;">
							<div class="inner">
								<input type="hidden" id="poll-id" />
								<h4 id="forum-poll-title"></h4>
								<ul id="forum-poll-choices" <?php if(!isset($_SESSION['userId']) || !empty($hasVoted)): ?>style="display: none;"<?php endif; ?>>
								
								</ul>
								<ul id="forum-poll-results" <?php if(empty($hasVoted)): ?>style="display: none;"<?php endif; ?>>
								
								</ul>
								<div id="forum-poll-btns" <?php if(!isset($_SESSION['userId']) || !empty($hasVoted)): ?>style="display: none;"<?php endif; ?>>
									<button id="poll-vote" class="rb-btn">Vote</button>
									<button id="poll-view-results" class="rb-btn">View Results</button>
								</div>
							</div>
						</div>
						<?php endif; ?>
						<section id="forum-posts" class="clearfix infinite-container">
						<div class="load-more-posts-up"></div>
						<?php if(!empty($posts)): ?>
							<?php foreach($posts as $key => $post): ?>
							<div id="post-<?php echo hsc($post['id']); ?>" class="post-wrap clearfix post-number-<?php echo hsc($post['post_order']); ?>">
								<div class="inner clearfix">
									<div class="post clearfix">
										<div class="post-head">
											<span class="post-username">
												<a id="username-for-post-<?php echo hsc($post['id']); ?>" href="<?php echo HTTP_SERVER . 'forums/user/' . hsc($post['username_alias']) . '/' . hsc($post['user_id']); ?>">
													<?php echo hsc($post['username']); ?>
												</a>
											</span>
											<span class="post-permalink">
												<a id="post-permalink-<?php echo hsc($post['id']); ?>" href="<?php echo HTTP_SERVER . 'forums/topic/' . $topic['topic_alias'] . '/' . $topic['id']; ?>/<?php echo hsc($post['id']); ?>#" title="Permalink for post <?php echo hsc($post['post_order']); ?>">
													#<?php echo hsc($post['post_order']); ?>
												</a>
											</span>
											<input type="hidden" id="post-number-<?php echo hsc($post['post_order']); ?>" value="<?php echo hsc($post['id']); ?>" />
										</div>
										<div class="main-post">
											<div class="post-author-info">
												<input type="hidden" id="post-<?php echo hsc($post['id']); ?>-userid" value="<?php echo hsc($post['user_id']); ?>" />
												<p class="post-role">
													<?php echo hsc($acl->getHighestRankedRoleForUser($post['user_id'])); ?>
												</p>
												<div class="user-avatar">
													<?php if(!empty($post['avatar'])): ?>
													<div class="avatar">
														<img src="<?php echo HTTP_AVATARS . hsc($post['avatar']); ?>" alt="<?php echo hsc($post['username']); ?>'s Avatar" />
													</div>
													<?php elseif(!empty($globalSettings['avatar_location']['value'])): ?>
													<div class="avatar">
														<img src="<?php echo hsc($defaultAvatar); ?>" alt="<?php echo hsc($post['username']); ?>" />
													</div>
													<?php else: ?>
													<div class="no-avatar">
														<div class="inner">
															<span>No Avatar</span>
														</div>
													</div>
													<?php endif; ?>
												</div>
												<p class="post-user-post-count">
													<?php if($post['user_id'] != 1): ?>
													<span class="post-count-for-<?php echo hsc($post['user_id']); ?>">
														<?php echo hsc($post['forum_post_count']); ?>
													</span>
													<span class="post-count-label-for-<?php echo hsc($post['user_id']); ?>">
														<?php if($post['forum_post_count'] == 1): ?>
														 post
														<?php else: ?>
														 posts
														<?php endif; ?>
													</span>
													<?php endif; ?>
												</p>
											</div>
											<div class="post-body clearfix">
												<p id="posted-info-<?php echo hsc($post['id']); ?>" class="posted-info">
													Posted <?php echo nicetime($post['date_posted']); ?>
												</p>
												<?php if($post['best_answer'] == 1): ?>
												<p id="best-answer-<?php echo hsc($post['id']); ?>" class="best-answer">
													<span class="ui-icon ui-icon-check"></span>
													<span>Best Answer</span>
												</p>
												<?php endif; ?>
												<div id="post-content-<?php echo hsc($post['id']); ?>" class="post-content">
												<?php echo $post['post_content']; ?>
												</div>
											</div>
										</div>
										<div id="post-footer-<?php echo hsc($post['id']); ?>" class="post-footer clearfix">
											<ul class="post-menu">
											<?php if(isset($_SESSION['userId'])): ?>
												<?php if(!empty($isModerator) && !empty($moderatorPerms) && !empty($moderatorPerms[$topic['forum_id']]['delete_posts']) && $moderatorPerms[$topic['forum_id']]['delete_posts'] == 1): ?>
												<li id="delete-post-<?php echo hsc($post['id']); ?>" class="post-menu-delete">
													Delete
												</li>
												<?php endif; ?>
												<?php if((!empty($_SESSION['userId']) && $post['user_id'] === $_SESSION['userId']) 
													  || (!empty($isModerator) && !empty($moderatorPerms) && $moderatorPerms[$topic['forum_id']]['edit_posts'] == 1)): ?>
												<li id="edit-post-<?php echo hsc($post['id']); ?>" class="post-menu-edit">
													Edit
												</li>
												<?php endif; ?>
												<li id="quote-post-<?php echo hsc($post['id']); ?>" class="post-menu-quote">
													Quote
												</li>
												<?php if((int)$forum['best_answer'] === 1 && ($post['post_order'] != 1 
													  && ($_SESSION['userId'] == $topic['user_id'] && $post['best_answer'] != 1 && $forum['topic_starter_best_answer'] == 1) 
													  || ($post['post_order'] != 1 && !empty($moderatorPerms[$topic['forum_id']]['toggle_answered']) && $moderatorPerms[$topic['forum_id']]['toggle_answered'] == 1))):
												?>
												<li id="best-answer-post-<?php echo hsc($post['id']); ?>" class="post-menu-best-answer">
													<span class="ui-icon ui-icon-check"></span>
													<span>Best Answer</span>
												</li>
												<?php endif; ?>
												<li id="report-post-<?php echo hsc($post['id']); ?>" class="post-menu-report">
													Report
												</li>
											<?php endif; ?>
											</ul>
										</div>
									</div>
								</div>
							</div>
							<?php endforeach; ?>
						<?php endif; ?>
						<div class="load-more-posts-down"></div>
						</section>
						<?php if(!empty($forumPerms['reply-topics'])): ?>
						<button class="create-forum-post rb-btn gray-btn fltrght">Reply to topic</button>
						<?php else: ?>
						<div class="disabled-btn fltrght">You cannot reply to this topic</div>
						<?php endif; ?>
						<div class="end-of-forum"></div>
					</section>
					<nav id="post-scroller">
						<div id="post-scroller-btns" class="clearfix vert-space">
							<?php if(!empty($forumPerms['reply-topics'])): ?>
							<button class="create-forum-post rb-btn gray-btn">Reply to topic</button>
							<?php else: ?>
							<div class="disabled-btn">You cannot reply to this topic</div>
							<?php endif; ?>
						</div>
						<div>
							<ul id="post-autoloader" class="clearfix vert-space">
								<li>
									<label for="autoload-posts-toggle">Autoload Posts: </label>
								</li>
								<li id="autoload-posts-toggle" class="autoload-toggle-wrap toggle-on">
									<div>On</div>
								</li>
							</ul>
						</div>
						<div id="posts-loading" style="display: none;"></div>
						<div class="inner clearfix">
							<ul id="posts-nav">
								<li>
									<h5 id="current-post" title="Current Post"></h5>
								</li>
								<li class="of">
									of
								</li>
								<li>
									<h5 id="total-posts" title="Total Posts">
										<?php echo hsc($totalNumOfPosts); ?>
									</h5>
								</li>
								<li class="jump-arrow">
									<a class="nav-tri-up nav-tri" href="<?php echo HTTP_SERVER . 'forums/topic/' . $topic['topic_alias'] . '/' . $topic['id']; ?>/first">
									
									</a>
									<a class="nav-tri-down nav-tri" href="<?php echo HTTP_SERVER . 'forums/topic/' . $topic['topic_alias'] . '/' . $topic['id']; ?>/last">
									
									</a>
								</li>
							</ul>
							<div class="bkgd"></div>
						</div>
					</nav>
				</div>
			</div>
		</section>
		<section id="post-panel" style="display: none;" class="editor-resizable">
			<div id="panel-max">
				<input type="hidden" id="topic-id" name="topicId" value="<?php echo hsc($topic['id']); ?>" />
				<input type="hidden" id="forum-id" name="forumId" value="<?php echo hsc($topic['forum_id']); ?>" />
				<input type="hidden" id="topic-alias" name="topicAlias" value="<?php echo hsc($topic['topic_alias']); ?>" />
				<input type="hidden" id="post-id" name="postId" />
				<div class="grab-handle ui-resizable-handle ui-resizable-n">
					<span class="grab-handle-icon sprites"></span>
				</div>
				<div class="inner vert-space clearfix">
					<div id="editors" class="editor-resizable">
						<div id="editor-wrap">
							<textarea id="forum-editor" name="content" class="editor-resizable"></textarea>
						</div>
						<div id="editor-preview-wrap" class="vert-space">
							<div id="editor-preview" class="editor-resizable"></div>
						</div>
					</div>
					<div class="editor-buttons clearfix">
						<button type="submit" id="submit-post" class="rb-btn blue-btn" name="newPost" value="submit-new-post">Post Reply</button>
						<button type="submit" id="update-post" class="rb-btn blue-btn" name="updatePost" style="display: none;" value="update-post">Update Post</button>
						<button type="button" id="cancel-post" class="rb-btn light-gray-btn">Cancel</button>
						<a id="toggle-preview" class="fltrght">Hide Preview</a>
					</div>
				</div>
			</div>
			<div id="panel-min" style="display:none;">
				<div class="inner">
					<a href="#">You have started a post</a>
					<span id="maximize-panel">Go back to your post</span>
				</div>
			</div>
		</section>
	</form>
<?php displayFooter(); ?>

