<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/categories/categories-c.php';
require_once DIR_ADMIN . 'controller/comments/comments-c.php';
$title = 'Comment | Edit';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
loadTinyMce();
?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/timepicker/timepicker.min.js"></script>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/comments.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/comments.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.min.js"></script>
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
				<span>Edit Comment</span>
				<div class="menu-darr"></div>
			</a>
			<a class="fltrght" id="toggleSidePanel" href="#">+/- Toggle side panel</a>
			<ul id="action-menu">
				<li>
					<a href="#" id="action-publish-comment">
						<span class="ui-icon ui-icon-check"></span>
						Publish
					</a>
				</li>
				<li>
					<a href="#" id="action-save-comment">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-close-comment">Save and Close</a>
				</li>
				<li>
					<a href="#">
						<span class="ui-icon ui-icon-gear"></span>
						Screen Options
					</a>
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
					<a href="#">
						About
					</a>
				</li>
				<li>
					<a href="#" id="action-close-comment">
						<span class="ui-icon ui-icon-close"></span>
						Close
					</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-87" <?php if(!in_array(87, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save-comment">
						<a href="#">
							<span class="ui-icon ui-icon-disk"></span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close-comment">
						<a href="<?php echo HTTP_ADMIN . 'comments/' . urlencode(($articles->getAlias($comment['article_id']))); ?>">
							<span class="ui-icon ui-icon-close"> </span>
							<span class="toolbar-text">Close</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-comment">
						<a href="#">
							<span class="ui-icon ui-icon-help"></span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
				<div class="clear"></div>
			</div>
		</div>
		<div class="clear"></div>
		<div id="element-box">
			<form id="adminForm" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post" enctype="multipart/form-data">
				<div class="profile-main">
					<div id="profile-padding">
						<div class="element">
							<div class="element-top">Comment Details</div>
							<div class="element-body">
								<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
								<input type="hidden" name="id" value="<?php echo hsc($_GET["id"]); ?>" />
								<input type="hidden" id="comment-action" name="commentAction" />
								<table id="profile-content">
									<tr>
										<td><label for="author" class="profile-label">Author</label></td>
										<td class="width80pcnt">
											<input id="author" type="text" class="readonly" name="author" size="40" value="<?php echo hsc($comment['com_author']); ?>"
												   readonly="readonly" />
										</td>
									</tr>
									<tr>
										<td><label for="state" class="profile-label">State</label></td>
										<td>
											<select id="state" name="state">
												<option value="1" <?php if($comment['com_published'] == 1) echo 'selected="selected"'; ?>>Published</option>
												<option value="0" <?php if($comment['com_published'] == 0) echo 'selected="selected"'; ?>>Unpublished</option>
												<option value="2" <?php if($comment['com_published'] == 2) echo 'selected="selected"'; ?>>Pending</option>
												<option value="3" <?php if($comment['com_published'] == 3) echo 'selected="selected"'; ?>>Spam</option>
												<option value="4" <?php if($comment['com_published'] == 4) echo 'selected="selected"'; ?>>Removed</option>
											</select>
										</td>
									</tr>
									<tr>
										<td><label for="email" class="profile-label">Email</label></td>
										<td>
											<input id="email" type="text" name="email" size="40"
											<?php if(isset($globalValidate->email)): echo 'value="' . hsc($globalValidate->email) . '"'; ?>
											<?php else: echo 'value="' . hsc($comment['com_email']) . '"'; ?>
											<?php endif; ?> />
										</td>
									</tr>
									<tr>
										<td><label for="website" class="profile-label">Website</label></td>
										<td>
											<input id="website" type="text" name="website" size="40"
											<?php if(isset($globalValidate->website)): echo 'value="' . hsc($globalValidate->website) . '"'; ?>
											<?php else: echo 'value="' . hsc($comment['com_website']) . '"'; ?>
											<?php endif; ?> />
										</td>
									</tr>
									<tr>
										<td><label for="ip" class="profile-label">IP Address</label></td>
										<td class="width90pcnt">
											<input id="name" type="text" class="readonly" name="name" size="40" readonly disabled="disabled"
											value="<?php echo hsc($comment['com_ip']); ?>" />
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="element content-editor">
							<div class="element-top">Comment</div>
							<div class="element-body">
								<label for="description-editor" class="profile-label"></label>
								<textarea id="description-editor" name="content" cols="100" rows="20"><?php echo hsc($comment['com_content']); ?></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="profile-details">
					<div id="detail-padding">
						<div class="panel">
							<div class="panel-column">
								<div class="element">
									<div class="element-top">Publishing Options</div>
									<div class="element-body">
										<table class="panel-content">
											<tr>
												<td class="panel-spacing">
													<label>Created By</label>
												</td>
												<td>
													<p><?php echo hsc($comment['com_author']); ?></p>
												</td>
											</tr>
											<tr>
												<td><label class="panel-spacing fltlft" for="date-posted">Date Posted</label></td>
												<td id="create-date-picker">
													<input id="date-posted" class="panel-input profile-text" name="datePosted" type="text"
														   <?php if(isset($comment['com_date'])): ?>
															  <?php echo 'value="' . hsc($comment['com_date']) . '"'; ?>
														  <?php elseif(isset($commentValidate->createDate)): ?>
															  <?php echo 'value="' . hsc($commentValidate->createDate) . '"'; ?>
														  <?php endif; ?>
													/>
												</td>
											</tr>
											<tr>
												<td><label class="panel-spacing panel-text-only fltlft">Modified By</label></td>
												<td><?php if(isset($comment['com_modified_by'])) echo hsc($comment['com_modified_by']); ?></td>
											</tr>
											<tr>
												<td><label class="panel-spacing panel-text-only fltlft" for="date-posted">Modified Date</label></td>
												<td>
													<?php if(isset($comment['com_modified_date'])): ?>
														<?php echo hsc($comment['com_modified_date']); ?>
													<?php endif; ?>
												</td>
											</tr>
										</table>
										<div>
											<button id="publish-comment-btn" class="publish-btn rb-btn">Publish</button>
										</div>
										<div>
											<button id="unpublish-comment-btn" class="unpublish-btn rb-btn">Unpublish</button>
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		<div class="clear"></div>
	</div>
</div>
<?php displayFooter(); ?>
