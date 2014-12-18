<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/forums/forums-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Forum | Forums';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
loadTinyMce();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/forums.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/forums.min.js"></script>
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
				<?php if(isset($forum)): ?>
				<span>Edit Forum</span>
				<?php else: ?>
				<span>Add New Forum</span>
				<?php endif; ?>
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
					<a href="#" id="action-save-forum">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-new-forum">Save and New</a>
				</li>
				<li>
					<a href="#" id="action-save-close-forum">Save and Close</a>
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
			<div class="search-wrap">
				<form id="forum-search-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="text" id="forum-search" class="med-small-text overview-search" name="forumToSearch" placeholder="Search by title" />
					</div>
					<div>
						<button class="sprites search-sprite sprite-space-16" name="submitForumSearch" type="submit" value="1"></button>
					</div>
				</form>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($forum)): ?>
		<?php displayBreadCrumbs($forum['forum_title'], HTTP_ADMIN . 'forums/' . $forum['id'], array('title' => 'Forums', 'url' => HTTP_ADMIN . 'forums')); ?>
		<?php else: ?>
		<?php displayBreadCrumbs('Add New Forum', HTTP_ADMIN . 'forums/new', array('title' => 'Forums', 'url' => HTTP_ADMIN . 'forums')); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix option-100" <?php if(!in_array(100, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<?php if(aclVerify('backend forum admin')): ?>
					<li id="toolbar-save-forum">
						<a href="#">
							<span class="ui-icon ui-icon-disk"></span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close-forum">
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
				<form id="adminForm" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					<input type="hidden" id="forum-action" name="forumAction" />
					<?php if(isset($forum)): ?>
					<input type="hidden" name="id" value="<?php echo hsc($forum['id']); ?>" />
					<div class="profile-main">
						<div class="profile-padding">
							<div class="element">
								<div class="element-top">Forum title</div>
								<div class="element-body">
									<input type="text" id="title" name="title" placeholder="Enter forum title here"
										   <?php if(isset($forum['forum_title'])): ?>
											value="<?php echo hsc($forum['forum_title']); ?>"
											<?php elseif(isset($_SESSION['forum']->title)): ?> 
											value="<?php echo hsc($_SESSION['forum']->title); ?>"
											<?php endif; ?>
									/>
								</div>
							</div>
							<div class="element content-editor">
								<div class="element-top">Description</div>
								<div class="element-body">
									<textarea id="description-editor" name="description"><?php if(isset($forum['forum_description'])): echo $forum['forum_description']; elseif(isset($_SESSION['forum'])): echo $_SESSION['forum']->description; endif; ?></textarea>
								</div>
							</div>
							<div id="postable-settings">
								<table class="overview yes-no-tbl profile-tbl">
									<tr>
										<th class="divider" colspan="2">Basic Permissions</th>
									</tr>
									<tr>
										<th>Best answer feature</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[best_answer]" value="on"
														<?php if(isset($_SESSION['forum']->perms['best_answer']) && $_SESSION['forum']->perms['best_answer'] === 1): ?>
														   checked="checked"
														<?php elseif((int)$forum['best_answer'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[best_answer]" value="off"
														<?php if(isset($_SESSION['forum']->perms['best_answer']) && $_SESSION['forum']->perms['best_answer'] === 0): ?>
														   checked="checked"
														<?php elseif((int)$forum['best_answer'] === 0): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>Topic starter can choose best answer</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[topic_starter_best_answer]" value="on"
														<?php if(isset($_SESSION['forum']->perms['topic_starter_best_answer']) && $_SESSION['forum']->perms['topic_starter_best_answer'] === 1): ?>
														   checked="checked"
														<?php elseif((int)$forum['topic_starter_best_answer'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[topic_starter_best_answer]" value="off"
														<?php if(isset($_SESSION['forum']->perms['topic_starter_best_answer']) && $_SESSION['forum']->perms['topic_starter_best_answer'] === 0): ?>
														   checked="checked"
														<?php elseif((int)$forum['topic_starter_best_answer'] === 0): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											<p>
												Hide last post info
											</p>
											<p class="description">
												Will hide information about the last post of this forum on main forum page.
											</p>
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[hide_last_post_info]" value="on"
														<?php if(isset($_SESSION['forum']->perms['hide_last_post_info']) && $_SESSION['forum']->perms['hide_last_post_info'] === 1): ?>
														   checked="checked"
														<?php elseif((int)$forum['hide_last_post_info']): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[hide_last_post_info]" value="off"
														<?php if(isset($_SESSION['forum']->perms['hide_last_post_info']) && $_SESSION['forum']->perms['hide_last_post_info'] === 0): ?>
														   checked="checked"
														<?php elseif((int)$forum['hide_last_post_info'] === 0): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											<p>
												Topic list visible to all members
											</p>
											<p class="description">
												Will show a topic list for members who can view the forum but not its topics.
											</p>
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[see_topic_list]" value="on"
														<?php if(isset($_SESSION['forum']->perms['see_topic_list']) && $_SESSION['forum']->perms['see_topic_list'] === 1): ?>
														   checked="checked"
														<?php elseif((int)$forum['see_topic_list'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[see_topic_list]" value="off"
														<?php if(isset($_SESSION['forum']->perms['see_topic_list']) && $_SESSION['forum']->perms['see_topic_list'] === 0): ?>
														   checked="checked"
														<?php elseif((int)$forum['see_topic_list'] === 0): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											<p>
												Show subforums above topic list
											</p>
											<p class="description">
												Will show a container of this forum's subforums if they exist.
											</p>
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[show_subforums]" value="on"
														<?php if(isset($_SESSION['forum']->perms['show_subforums']) && $_SESSION['forum']->perms['show_subforums'] === 1): ?>
														   checked="checked"
														<?php elseif((int)$forum['show_subforums'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[show_subforums]" value="off"
														<?php if(isset($_SESSION['forum']->perms['show_subforums']) && $_SESSION['forum']->perms['show_subforums'] === 0): ?>
														   checked="checked"
														<?php elseif((int)$forum['show_subforums'] === 0): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
								</table>
								<table class="overview yes-no-tbl profile-tbl">
									<tr>
										<th class="divider" colspan="2">Post Permissions</th>
									</tr>
									<tr>
										<th>
											<p>
												Allow HTML in posts.
											</p>
											<p class="description">
												HTML can be used while posting - does not override a role's permissions.
											</p>
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[html]" value="on"
														<?php if(isset($_SESSION['forum']->perms['html']) && $_SESSION['forum']->perms['html'] === 1): ?>
														   checked="checked"
														<?php elseif((int)$forum['html'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[html]" value="off"
														<?php if(isset($_SESSION['forum']->perms['html']) && $_SESSION['forum']->perms['html'] === 0): ?>
														   checked="checked"
														<?php elseif((int)$forum['html'] === 0): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											Allow BB Code
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[bb_code]" value="on"
														<?php if(isset($_SESSION['forum']->perms['bb_code']) && $_SESSION['forum']->perms['bb_code'] === 0): ?>
														   checked="checked"
														<?php elseif((int)$forum['bb_code'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[bb_code]" value="off"
														<?php if(isset($_SESSION['forum']->perms['bb_code']) && $_SESSION['forum']->perms['bb_code'] === 1): ?>
														   checked="checked"
														<?php elseif((int)$forum['bb_code'] === 0): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											Enable Polls
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[polls]" value="on"
														<?php if(isset($_SESSION['forum']->perms['polls']) && $_SESSION['forum']->perms['polls'] === 0): ?>
														   checked="checked"
														<?php elseif((int)$forum['polls'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[polls]" value="off"
														<?php if(isset($_SESSION['forum']->perms['polls']) && $_SESSION['forum']->perms['polls'] === 1): ?>
														   checked="checked"
														<?php elseif((int)$forum['polls'] === 0): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											<p>
												Poll votes bump topic
											</p>
											<p class="description">
												Will move a topic with a poll to the top when sorted by date of last post.
											</p>
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[poll_bump]" value="on"
														<?php if(isset($_SESSION['forum']->perms['poll_bump']) && $_SESSION['forum']->perms['poll_bump'] === 1): ?>
														   checked="checked"
														<?php elseif((int)$forum['poll_bump'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[poll_bump]" value="off"
														<?php if(isset($_SESSION['forum']->perms['poll_bump']) && $_SESSION['forum']->perms['poll_bump'] === 0): ?>
														   checked="checked"
														<?php elseif((int)$forum['poll_bump'] === 0): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											Allow topic ratings
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[topic_rating]"
														<?php if(isset($_SESSION['forum']->perms['topic_rating']) && $_SESSION['forum']->perms['topic_rating'] === 1): ?>
														   checked="checked"
														<?php elseif((int)$forum['topic_rating'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[topic_rating]"
														<?php if(isset($_SESSION['forum']->perms['topic_rating']) && $_SESSION['forum']->perms['topic_rating'] === 0): ?>
														   checked="checked"
														<?php elseif((int)$forum['topic_rating'] === 0): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											<p>
												Posting increases user's post count
											</p>
											<p class="description">
												If enabled will increment the user's post count by 1 after posting.
											</p>
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[post_count_incr]" value="on"
														<?php if(isset($_SESSION['forum']->perms['post_count_incr']) && $_SESSION['forum']->perms['post_count_incr'] === 0): ?>
														   checked="checked"
														<?php elseif((int)$forum['post_count_incr'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[post_count_incr]" value="off"
														<?php if(isset($_SESSION['forum']->perms['post_count_incr']) && $_SESSION['forum']->perms['post_count_incr'] === 1): ?>
														   checked="checked"
														<?php elseif((int)$forum['post_count_incr'] === 0): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
								</table>
								<table class="yes-no-tbl overview profile-tbl no-radio">
									<tr>
										<th class="divider" colspan="2">Restrictions</th>
									</tr>
									<tr>
										<th>
											Minimum posts to post
										</th>
										<td>
											<input type="text" class="small-text" name="perms[min_posts_to_post]"
												<?php if(isset($_SESSION['forum']->perms['min_posts_to_post'])): ?>
												   value="<?php echo hsc($_SESSION['forum']->perms['min_posts_to_post']); ?>"
												<?php else: ?>
												   value="<?php echo hsc($forum['min_posts_to_post']); ?>"
												<?php endif; ?>
											/>
										</td>
									</tr>
									<tr>
										<th>
											Mimimum posts to view
										</th>
										<td>
											<input type="text" class="small-text" name="perms[min_posts_to_view]" />
										</td>
									</tr>
								</table>
								<table class="yes-no-tbl overview profile-tbl no-radio">
									<tr>
										<th class="divider" colspan="2">Post Moderation</th>
									</tr>
									<tr>
										<th>
											Moderation method
										</th>
										<td>
											<select id="moderation-method" name="perms[moderation-method]">
												<option value="0" <?php if((isset($_SESSION['forum']->perms['moderation_method']) && $_SESSION['forum']->perms['moderation_method'] === 0) || $forum['moderation_method'] === 0): ?>selected="selected"<?php endif; ?>>
													None
												</option>
												<option value="1" <?php if((isset($_SESSION['forum']->perms['moderation_method']) && $_SESSION['forum']->perms['moderation_method'] === 1) || $forum['moderation_method'] === 1): ?>selected="selected"<?php endif; ?>>
													Moderate new replies and topics
												</option>
												<option value="2" <?php if((isset($_SESSION['forum']->perms['moderation_method']) && $_SESSION['forum']->perms['moderation_method'] === 2) || $forum['moderation_method'] === 2): ?>selected="selected"<?php endif; ?>>
													Moderate new topics but not new replies
												</option>
												<option value="3" <?php if((isset($_SESSION['forum']->perms['moderation_method']) && $_SESSION['forum']->perms['moderation_method'] === 3) || $forum['moderation_method'] === 3): ?>selected="selected"<?php endif; ?>>
													Moderate new replies but not new topics
												</option>
											</select>
										</td>
									</tr>
									<tr>
										<th>
											E-mail addresses to send moderation approval requests
										</th>
										<td>
											<input type="text" class="med-text" name="perms[approval_emails]"
												<?php if(isset($_SESSION['forum']->perms['approval_emails'])): ?>
												value="<?php echo hsc($_SESSION['forum']->perms['approval_emails']); ?>"
												<?php else: ?>
												value="<?php echo hsc($forum['approval_emails']); ?>"
												<?php endif; ?>
											/>
										</td>
									</tr>
								</table>
								<table class="overview yes-no-tbl profile-tbl no-radio">
									<tr>
										<th class="divider" colspan="2">Sorting and Ordering</th>
									</tr>
									<tr>
										<th>
											Date cutoff for topic display
										</th>
										<td>
											<select id="date-cut-off" name="perms[date_cut_off]">
												<option value="0" <?php if((isset($_SESSION['forum']->perms['date_cut_off']) && $_SESSION['forum']->perms['date_cut_off'] === 0) || $forum['date_cut_off'] === 0): ?>selected="selected"<?php endif; ?>>
													Show all topics
												</option>
												<option value="1" <?php if((isset($_SESSION['forum']->perms['date_cut_off']) && $_SESSION['forum']->perms['date_cut_off'] === 1) || $forum['date_cut_off'] === 1): ?>selected="selected"<?php endif; ?>>
													1 Day
												</option>
												<option value="5" <?php if((isset($_SESSION['forum']->perms['date_cut_off']) && $_SESSION['forum']->perms['date_cut_off'] === 5) || $forum['date_cut_off'] === 5): ?>selected="selected"<?php endif; ?>>
													5 Days
												</option>
												<option value="7" <?php if((isset($_SESSION['forum']->perms['date_cut_off']) && $_SESSION['forum']->perms['date_cut_off'] === 7) || $forum['date_cut_off'] === 7): ?>selected="selected"<?php endif; ?>>
													7 Days
												</option>
												<option value="10" <?php if((isset($_SESSION['forum']->perms['date_cut_off']) && $_SESSION['forum']->perms['date_cut_off'] === 10) || $forum['date_cut_off'] === 10): ?>selected="selected"<?php endif; ?>>
													10 Days
												</option>
												<option value="15" <?php if((isset($_SESSION['forum']->perms['date_cut_off']) && $_SESSION['forum']->perms['date_cut_off'] === 15) || $forum['date_cut_off'] === 15): ?>selected="selected"<?php endif; ?>>
													15 Days
												</option>
												<option value="20" <?php if((isset($_SESSION['forum']->perms['date_cut_off']) && $_SESSION['forum']->perms['date_cut_off'] === 20) || $forum['date_cut_off'] === 20): ?>selected="selected"<?php endif; ?>>
													20 Days
												</option>
												<option value="30" <?php if((isset($_SESSION['forum']->perms['date_cut_off']) && $_SESSION['forum']->perms['date_cut_off'] === 30) || $forum['date_cut_off'] === 30): ?>selected="selected"<?php endif; ?>>
													30 Days
												</option>
												<option value="60" <?php if((isset($_SESSION['forum']->perms['date_cut_off']) && $_SESSION['forum']->perms['date_cut_off'] === 60) || $forum['date_cut_off'] === 60): ?>selected="selected"<?php endif; ?>>
													60 Days
												</option>
												<option value="90" <?php if((isset($_SESSION['forum']->perms['date_cut_off']) && $_SESSION['forum']->perms['date_cut_off'] === 90) || $forum['date_cut_off'] === 90): ?>selected="selected"<?php endif; ?>>
													90 Days
												</option>
												<option value="180" <?php if((isset($_SESSION['forum']->perms['date_cut_off']) && $_SESSION['forum']->perms['date_cut_off'] === 180) || $forum['date_cut_off'] === 180): ?>selected="selected"<?php endif; ?>>
													180 Days
												</option>
												<option value="365" <?php if((isset($_SESSION['forum']->perms['date_cut_off']) && $_SESSION['forum']->perms['date_cut_off'] === 365) || $forum['date_cut_off'] === 365): ?>selected="selected"<?php endif; ?>>
													365 Days
												</option>
											</select>
										</td>
									</tr>
									<tr>
										<th>
											Sort topics by
										</th>
										<td>
											<select id="" name="perms[default_sort_key]">
												<option value="date" <?php if((isset($_SESSION['forum']->perms['default_sort_key']) && $_SESSION['forum']->perms['default_sort_key'] === 'date') || $forum['default_sort_key'] === 'date'): ?>selected="selected"<?php endif; ?>>
													Date of last post
												</option>
												<option value="title" <?php if((isset($_SESSION['forum']->perms['default_sort_key']) && $_SESSION['forum']->perms['default_sort_key'] === 'title') || $forum['default_sort_key'] === 'title'): ?>selected="selected"<?php endif; ?>>
													Topic title
												</option>
												<option value="name" <?php if((isset($_SESSION['forum']->perms['default_sort_key']) && $_SESSION['forum']->perms['default_sort_key'] === 'name') || $forum['default_sort_key'] === 'name'): ?>selected="selected"<?php endif; ?>>
													Topic starter's name
												</option>
												<option value="posts" <?php if((isset($_SESSION['forum']->perms['default_sort_key']) && $_SESSION['forum']->perms['default_sort_key'] === 'posts') || $forum['default_sort_key'] === 'posts'): ?>selected="selected"<?php endif; ?>>
													Number of posts in topic
												</option>
												<option value="views" <?php if((isset($_SESSION['forum']->perms['default_sort_key']) && $_SESSION['forum']->perms['default_sort_key'] === 'views') || $forum['default_sort_key'] === 'views'): ?>selected="selected"<?php endif; ?>>
													Number of views
												</option>
												<option value="topic-start-date" <?php if((isset($_SESSION['forum']->perms['default_sort_key']) && $_SESSION['forum']->perms['default_sort_key'] === 'topic-start-date') || $forum['default_sort_key'] === 'topic-start-date'): ?>selected="selected"<?php endif; ?>>
													Date of topic creation
												</option>
											</select>
										</td>
									</tr>
									<tr>
										<th>
											Topic sort direction
										</th>
										<td>
											<select id="" name="perms[default_sort_order]">
												<option value="asc" <?php if((isset($_SESSION['forum']->perms['default_sort_key']) && $_SESSION['forum']->perms['default_sort_key'] === 'asc') || $forum['default_sort_key'] === 'asc'): ?>selected="selected"<?php endif; ?>>
													Ascending ( 0 - 9 / A - Z )
												</option>
												<option value="desc" <?php if((isset($_SESSION['forum']->perms['default_sort_key']) && $_SESSION['forum']->perms['default_sort_key'] === 'desc') || $forum['default_sort_key'] === 'desc'): ?>selected="selected"<?php endif; ?>>
													Descending ( 9 - 0 / Z - A )
												</option>
											</select>
										</td>
									</tr>
								</table>
								<table id="forum-perms-table" class="full-table">
									<tr>
										<th class="divider" colspan="2">Forum Role Permissions</th>
									</tr>
									<tr>
										<th class="top-left">
											Select All
										</th>
										<?php foreach($forumPermissions as $forumPermission): ?>
										<td>
											<input type="checkbox" id="check-<?php echo hsc($forumPermission['forum_permission_alias']); ?>" class="check-all-forum-perms" />
										</td>
										<?php endforeach; ?>
									</tr>
									<?php foreach($roles as $role): ?>
									<tr>
										<th id="role-<?php echo hsc($role['id']); ?>">
											<?php echo hsc($role['name']); ?>
										</th>
										<?php foreach($forumPermissions as $forumPermission): ?>
										<td class="forum-perm">
											<label class="center-label forum-perm-label">
												<input type="hidden" name="rolePerms[<?php echo hsc($role['id']); ?>][<?php echo hsc($forumPermission['forum_permission_alias']); ?>]" value="off" />
												<input type="checkbox" id="<?php echo hsc($forumPermission['forum_permission_alias']); ?>-<?php echo hsc($role['id']); ?>" 
													   class="forum-perm-toggle center-toggle forum-perm-<?php echo hsc($forumPermission['forum_permission_alias']); ?>"
													   name="rolePerms[<?php echo hsc($role['id']); ?>][<?php echo hsc($forumPermission['forum_permission_alias']); ?>]"
													   <?php foreach($rolePermissions as $rolePerm): ?>
															<?php if($rolePerm['role_id'] === $role['id'] && $rolePerm['perm_id'] === $forumPermission['id']): ?>
															checked="checked"
															<?php endif; ?>
													   <?php endforeach; ?>
												/>
												<?php echo hsc($forumPermission['forum_permission']); ?>
											</label>
										</td>
										<?php endforeach; ?>
									</tr>
									<?php endforeach; ?>
								</table>
							</div>
						</div>
					</div>
					<div class="profile-details">
						<div class="detail-padding">
							<div class="element">
								<div class="element-top">Basic Settings</div>
								<div class="element-body">
									<table class="panel-tbl-std">
										<tr>
											<th class="vAlignTop">
												Type
											</th>
											<td>
												<ul>
													<li>
														<label for="type-standard" class="center-label">
															<input type="radio" id="type-standard" class="center-toggle" name="type" value="standard" <?php if($forum['forum_type'] === 'standard'): ?>checked="checked"<?php endif; ?> />
															Standard forum
														</label>
														<p class="description">Normal, postable forum</p>
													</li>
													<li>
														<label for="type-subcontainer" class="center-label">
															<input type="radio" id="type-subcontainer" class="center-toggle" name="type" value="subforum-container" <?php if($forum['forum_type'] === 'subforum-container'): ?>checked="checked"<?php endif; ?> />
															Subforum container
														</label>
														<p class="description">Non postable list of subforums</p>
													</li>
													<li>
														<label for="type-redirect" class="center-label">
															<input type="radio" id="type-redirect" class="center-toggle" name="type" value="redirect" <?php if($forum['forum_type'] === 'redirect'): ?>checked="checked"<?php endif; ?> />
															Redirect forum
														</label>
														<p class="description">Redirects users to a URL</p>
													</li>
												</ul>
											</td>
										</tr>
										<tr>
											<th class="vAlignTop">
												Parent
											</th>
											<td class="select-forums">
												<select id="forums-to-moderate" name="parentId" size="10">
												<?php foreach($forumList as $forumParent): ?>
													<option value="<?php echo hsc($forumParent['id']); ?>" <?php if($parentId === $forumParent['id']): ?>selected="selected"<?php endif; ?> <?php if($forumParent['root_distance'] >= 2 || $forum['id'] === $forumParent['id']): ?>disabled="disabled"<?php endif; ?>>
													<?php if((int)$forumParent['root_distance'] > 0): ?>
														<?php echo str_repeat('- ', (int)$forumParent['root_distance']) . $forumParent['forum_title']; ?>
													<?php else: ?>
														<?php echo hsc($forumParent['forum_title']); ?>
													<?php endif; ?>
													</option>
												<?php endforeach; ?>
												</select>
											</td>
										</tr>
										<tr>
											<th>
												Alias
											</th>
											<td>
												<input type="text" name="alias" class="profile-text" 
												<?php if(isset($_SESSION['forum']->alias)): ?>
													value="<?php echo hsc($_SESSION['forum']->alias); ?>"
												<?php else: ?>
													value="<?php echo hsc($forum['forum_alias']); ?>"
												<?php endif; ?>
												/>
											</td>
										</tr>
										<tr>
											<th>
												Password
											</th>
											<td>
												<input type="password" name="password" class="profile-text" />
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
					<?php else: ?>
					<div class="profile-main">
						<div class="profile-padding">
							<div class="element">
								<div class="element-top">Forum title</div>
								<div class="element-body">
									<input type="text" id="title" name="title" placeholder="Enter forum title here"
										   <?php if(isset($_SESSION['forum']->title)): ?>
										   value="<?php if(isset($_SESSION['forum']->title)): echo hsc($_SESSION['forum']->title); endif; ?>"
										   <?php endif; ?>
									/>
								</div>
							</div>
							<div class="element content-editor">
								<div class="element-top">Description</div>
								<div class="element-body">
									<textarea id="description-editor" name="description"><?php if(isset($_SESSION['forum']->description)): echo $_SESSION['forum']->description; endif; ?></textarea>
								</div>
							</div>
							<div id="postable-settings">
								<table class="overview yes-no-tbl profile-tbl">
									<tr>
										<th class="divider" colspan="2">Basic Permissions</th>
									</tr>
									<tr>
										<th>Best answer feature</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[best_answer]" value="on"
														<?php if(isset($_SESSION['forum']->perms['best_answer']) && $_SESSION['forum']->perms['best_answer'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[best_answer]" value="off"
														<?php if(isset($_SESSION['forum']->perms['best_answer']) && $_SESSION['forum']->perms['best_answer'] === 0): ?>
														   checked="checked"
														<?php elseif(!isset($_SESSION['forum']->perms['best_answer'])): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>Topic starter can choose best answer</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[topic_starter_best_answer]" value="on"
														<?php if(isset($_SESSION['forum']->perms['topic_starter_best_answer']) && $_SESSION['forum']->perms['topic_starter_best_answer'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[topic_starter_best_answer]" value="off"
														<?php if(isset($_SESSION['forum']->perms['topic_starter_best_answer']) && $_SESSION['forum']->perms['topic_starter_best_answer'] === 0): ?>
														   checked="checked"
														<?php elseif(!isset($_SESSION['forum']->perms['topic_starter_best_answer'])): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											<p>
												Hide last post info
											</p>
											<p class="description">
												Will hide information about the last post of this forum on main forum page.
											</p>
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[hide_last_post_info]" value="on"
														<?php if(isset($_SESSION['forum']->perms['hide_last_post_info']) && $_SESSION['forum']->perms['hide_last_post_info'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[hide_last_post_info]" value="off"
														<?php if(isset($_SESSION['forum']->perms['hide_last_post_info']) && $_SESSION['forum']->perms['hide_last_post_info'] === 0): ?>
														   checked="checked"
														<?php elseif(!isset($_SESSION['forum']->perms['hide_last_post_info'])): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											<p>
												Topic list visible to all members
											</p>
											<p class="description">
												Will show a topic list for members who can view the forum but not its topics.
											</p>
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[see_topic_list]" value="on"
														<?php if(isset($_SESSION['forum']->perms['see_topic_list']) && $_SESSION['forum']->perms['see_topic_list'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[see_topic_list]" value="off"
														<?php if(isset($_SESSION['forum']->perms['see_topic_list']) && $_SESSION['forum']->perms['see_topic_list'] === 0): ?>
														   checked="checked"
														<?php elseif(!isset($_SESSION['forum']->perms['see_topic_list'])): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											<p>
												Show subforums above topic list
											</p>
											<p class="description">
												Will show a container of this forum's subforums if they exist.
											</p>
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[show_subforums]" value="on"
														<?php if(isset($_SESSION['forum']->perms['show_subforums']) && $_SESSION['forum']->perms['show_subforums'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[show_subforums]" value="off"
														<?php if(isset($_SESSION['forum']->perms['show_subforums']) && $_SESSION['forum']->perms['show_subforums'] === 0): ?>
														   checked="checked"
														<?php elseif(!isset($_SESSION['forum']->perms['show_subforums'])): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
								</table>
								<table class="overview yes-no-tbl profile-tbl">
									<tr>
										<th class="divider" colspan="2">Post Permissions</th>
									</tr>
									<tr>
										<th>
											<p>
												Allow HTML in posts.
											</p>
											<p class="description">
												HTML can be used while posting - does not override a role's permissions.
											</p>
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[html]" value="on"
														<?php if(isset($_SESSION['forum']->perms['html']) && $_SESSION['forum']->perms['html'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[html]" value="off"
														<?php if(isset($_SESSION['forum']->perms['html']) && $_SESSION['forum']->perms['html'] === 0): ?>
														   checked="checked"
														<?php elseif(!isset($_SESSION['forum']->perms['html'])): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											Allow BB Code
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[bb_code]" value="on"
														<?php if(isset($_SESSION['forum']->perms['bb_code']) && $_SESSION['forum']->perms['bb_code'] === 0): ?>
														   checked="checked"
														<?php elseif(!isset($_SESSION['forum']->perms['bb_code'])): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[bb_code]" value="off"
														<?php if(isset($_SESSION['forum']->perms['bb_code']) && $_SESSION['forum']->perms['bb_code'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											Enable Polls
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[polls]" value="on"
														<?php if(isset($_SESSION['forum']->perms['polls']) && $_SESSION['forum']->perms['polls'] === 0): ?>
														   checked="checked"
														<?php elseif(!isset($_SESSION['forum']->perms['polls'])): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[polls]" value="off"
														<?php if(isset($_SESSION['forum']->perms['polls']) && $_SESSION['forum']->perms['polls'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											<p>
												Poll votes bump topic
											</p>
											<p class="description">
												Will move a topic with a poll to the top when sorted by date of last post.
											</p>
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[poll_bump]" value="on"
														<?php if(isset($_SESSION['forum']->perms['poll_bump']) && $_SESSION['forum']->perms['poll_bump'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[poll_bump]" value="off"
														<?php if(isset($_SESSION['forum']->perms['poll_bump']) && $_SESSION['forum']->perms['poll_bump'] === 0): ?>
														   checked="checked"
														<?php elseif(!isset($_SESSION['forum']->perms['poll_bump'])): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											Allow topic ratings
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[topic_rating]"
														<?php if(isset($_SESSION['forum']->perms['topic_rating']) && $_SESSION['forum']->perms['topic_rating'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[topic_rating]"
														<?php if(isset($_SESSION['forum']->perms['topic_rating']) && $_SESSION['forum']->perms['topic_rating'] === 0): ?>
														   checked="checked"
														<?php elseif(!isset($_SESSION['forum']->perms['topic_rating'])): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
									<tr>
										<th>
											<p>
												Posting increases user's post count
											</p>
											<p class="description">
												If enabled will increment the user's post count by 1 after posting.
											</p>
										</th>
										<td>
											<span class="radio-yes">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[post_count_incr]" value="on"
														<?php if(isset($_SESSION['forum']->perms['post_count_incr']) && $_SESSION['forum']->perms['post_count_incr'] === 0): ?>
														   checked="checked"
														<?php elseif(!isset($_SESSION['forum']->perms['post_count_incr'])): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													Yes
												</label>
											</span>
											<span class="radio-no">
												<label class="center-label">
													<input type="radio" class="center-toggle" name="perms[post_count_incr]" value="off"
														<?php if(isset($_SESSION['forum']->perms['post_count_incr']) && $_SESSION['forum']->perms['post_count_incr'] === 1): ?>
														   checked="checked"
														<?php endif; ?>
													/>
													No
												</label>
											</span>
										</td>
									</tr>
								</table>
								<table class="yes-no-tbl overview profile-tbl no-radio">
									<tr>
										<th class="divider" colspan="2">Restrictions</th>
									</tr>
									<tr>
										<th>
											Minimum posts to post
										</th>
										<td>
											<input type="text" class="small-text" name="perms[min_posts_to_post]" />
										</td>
									</tr>
									<tr>
										<th>
											Mimimum posts to view
										</th>
										<td>
											<input type="text" class="small-text" name="perms[min_posts_to_view]" />
										</td>
									</tr>
								</table>
								<table class="yes-no-tbl overview profile-tbl no-radio">
									<tr>
										<th class="divider" colspan="2">Post Moderation</th>
									</tr>
									<tr>
										<th>
											Moderation method
										</th>
										<td>
											<select id="moderation-method" name="perms[moderation_method]">
												<option value="0">None</option>
												<option value="1">Moderate new replies and topics</option>
												<option value="2">Moderate new topics but not new replies</option>
												<option value="3">Moderate new replies but not new topics</option>
											</select>
										</td>
									</tr>
									<tr>
										<th>
											E-mail addresses to send moderation approval requests
										</th>
										<td>
											<input type="text" class="med-text" name="perms[approval_emails]" />
										</td>
									</tr>
								</table>
								<table class="overview yes-no-tbl profile-tbl no-radio">
									<tr>
										<th class="divider" colspan="2">Sorting and Ordering</th>
									</tr>
									<tr>
										<th>
											Date cutoff for topic display
										</th>
										<td>
											<select id="date-cut-off" name="perms[date_cut_off]">
												<option value="0">Show all topics</option>
												<option value="1">1 Day</option>
												<option value="5">5 Days</option>
												<option value="7">7 Days</option>
												<option value="10">10 Days</option>
												<option value="15">15 Days</option>
												<option value="20">20 Days</option>
												<option value="30">30 Days</option>
												<option value="60">60 Days</option>
												<option value="90">90 Days</option>
												<option value="180">180 Days</option>
												<option value="365">365 Days</option>
											</select>
										</td>
									</tr>
									<tr>
										<th>
											Sort topics by
										</th>
										<td>
											<select id="" name="perms[default_sort_key]">
												<option value="date">Date of last post</option>
												<option value="title">Topic title</option>
												<option value="name">Topic starter's name</option>
												<option value="posts">Number of posts in topic</option>
												<option value="views">Number of views</option>
												<option value="topic-start-date">Date of topic creation</option>
											</select>
										</td>
									</tr>
									<tr>
										<th>
											Topic sort direction
										</th>
										<td>
											<select id="" name="perms[default_sort_order]">
												<option value="asc">Ascending ( 0 - 9 / A - Z )</option>
												<option value="desc">Descending ( 9 - 0 / Z - A )</option>
											</select>
										</td>
									</tr>
								</table>
								<table id="forum-perms-table" class="full-table">
									<tr>
										<th class="divider" colspan="2">Forum Role Permissions</th>
									</tr>
									<tr>
										<th class="top-left">
											Select All
										</th>
										<?php foreach($forumPermissions as $forumPermission): ?>
										<td>
											<input type="checkbox" id="check-<?php echo hsc($forumPermission['forum_permission_alias']); ?>" class="check-all-forum-perms" />
										</td>
										<?php endforeach; ?>
									</tr>
									<?php foreach($roles as $role): ?>
									<tr>
										<th id="role-<?php echo hsc($role['id']); ?>">
											<?php echo hsc($role['name']); ?>
										</th>
										<?php foreach($forumPermissions as $forumPermission): ?>
										<td class="forum-perm">
											<label class="center-label forum-perm-label">
												<input type="hidden" name="rolePerms[<?php echo hsc($role['id']); ?>][<?php echo hsc($forumPermission['forum_permission_alias']); ?>]" value="off" />
												<input type="checkbox" id="<?php echo hsc($forumPermission['forum_permission_alias']); ?>-<?php echo hsc($role['id']); ?>" 
													   class="forum-perm-toggle center-toggle forum-perm-<?php echo hsc($forumPermission['forum_permission_alias']); ?>"
													   name="rolePerms[<?php echo hsc($role['id']); ?>][<?php echo hsc($forumPermission['forum_permission_alias']); ?>]"
												/>
												<?php echo hsc($forumPermission['forum_permission']); ?>
											</label>
										</td>
										<?php endforeach; ?>
									</tr>
									<?php endforeach; ?>
								</table>
							</div>
						</div>
					</div>
					<div class="profile-details">
						<div class="detail-padding">
							<div class="element">
								<div class="element-top">Basic Settings</div>
								<div class="element-body">
									<table class="panel-tbl-std">
										<tr>
											<th class="vAlignTop">
												Type
											</th>
											<td>
												<ul>
													<li>
														<label for="type-standard" class="center-label">
															<input type="radio" id="type-standard" class="center-toggle" name="type" value="standard" <?php if(!isset($_SESSION['forum']->type) || isset($_SESSION['forum']->type) && $_SESSION['forum']->type === 'standard'): ?>checked="checked"<?php endif; ?> />
															Standard forum
														</label>
														<p class="description">Normal, postable forum</p>
													</li>
													<li>
														<label for="type-subcontainer" class="center-label">
															<input type="radio" id="type-subcontainer" class="center-toggle" name="type" value="subforum-container" <?php if(isset($_SESSION['forum']->type) && $_SESSION['forum']->type === 'subforum-container'):?>checked="checked"<?php endif; ?> />
															Subforum container
														</label>
														<p class="description">Non postable list of subforums</p>
													</li>
													<li>
														<label for="type-redirect" class="center-label">
															<input type="radio" id="type-redirect" class="center-toggle" name="type" value="redirect" <?php if(isset($_SESSION['forum']->type) && $_SESSION['forum']->type === 'redirect'):?>checked="checked"<?php endif; ?> />
															Redirect forum
														</label>
														<p class="description">Redirects users to a URL</p>
													</li>
												</ul>
											</td>
										</tr>
										<tr>
											<th class="vAlignTop">
												Parent
											</th>
											<td class="select-forums">
												<select id="forums-to-moderate" name="parentId" size="10">
												<?php foreach($forumList as $forumParent): ?>
													<option value="<?php echo hsc($forumParent['id']); ?>" <?php if((isset($_GET['sectionId']) && $_GET['sectionId'] == $forumParent['id']) || (isset($_SESSION['forum']->parentId) && $_SESSION['forum']->parentId === $forumParent['id'])): ?>selected="selected"<?php endif; ?> <?php if($forumParent['root_distance'] >= 2): ?>disabled="disabled"<?php endif; ?>>
													<?php if((int)$forumParent['root_distance'] > 0): ?>
														<?php echo str_repeat('- ', (int)$forumParent['root_distance']) . $forumParent['forum_title']; ?>
													<?php else: ?>
														<?php echo hsc($forumParent['forum_title']); ?>
													<?php endif; ?>
													</option>
												<?php endforeach; ?>
												</select>
											</td>
										</tr>
										<tr>
											<th>
												Alias
											</th>
											<td>
												<input type="text" name="alias" class="profile-text" 
												<?php if(isset($_SESSION['forum']->alias)): ?>
													value="<?php echo hsc($_SESSION['forum']->alias); ?>"
												<?php endif; ?>
												/>
											</td>
										</tr>
										<tr>
											<th>
												Password
											</th>
											<td>
												<input type="password" name="password" class="profile-text" />
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</form>
			</div>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
<?php forumCleanUp(); ?>
