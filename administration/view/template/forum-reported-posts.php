<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/forums/forums-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Reported Posts | Forum';
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
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" />
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/file-icons.css" />
<?php loadMainCss(); ?>
</head>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<span>Reported Forum Posts</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="#" id="action-delete-post-and-report">
						<span class="ui-icon ui-icon-alert"></span>
						Delete Post and Report
					</a>
				</li>
				<li>
					<a href="#" id="action-delete-report-only">
						<span class="ui-icon ui-icon-trash"></span>
						Delete Report Only
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
		<div id="toolbar-box">
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-delete-post-and-report">
						<a href="#">
							<span class="ui-icon ui-icon-alert"></span>
							<span class="toolbar-text">Delete Post and Report</span>
						</a>
					</li>
					<li id="toolbar-delete-report-only">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-text">Delete Report Only</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-options">
						<a id="options" href="#">
							<span class="ui-icon ui-icon-gear"></span>
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
		<div class="clearfix"></div>
		<div id="element-box">
			<div id="content-padding">
				<form id="adminForm" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="forum-action" name="forumAction" />
					</div>
					<div class="clearfix"></div>
					<table id="overview">
						<thead>
							<tr class="overview-top">
								<th class="width1pcnt">
									<input id="selectAll" type="checkbox" class="overview-check-all" />
								</th>
								<th class="left">
									Topic
								</th>
								<th>
									Content
								</th>
								<th>
									Post Number
								</th>
								<?php if(isset($_GET['type']) && $_GET['type'] === 'condensed'): ?>
								<th>
									Number Of Reports
								</th>
								<?php else: ?>
								<th>
									Reported By
								</th>
								<th>
									Posted By
								</th>
								<?php endif; ?>
								<th>
									Number of Reports
								</th>
								<th>
									Id
								</th>
							</tr>
						</thead>
						<tbody>
						<?php if(!empty($reportedPosts)): ?>
						<?php foreach($reportedPosts as $reportedPost): ?>
							<tr>
								<td class="width1pcnt">
									<input id="cb-<?php echo hsc($reportedPost['id']); ?>" type="checkbox" class="overview-check" name="forumCheck[]" value="<?php echo hsc($reportedPost['id']);?>" />
								</td>
								<td class="left vAlignTop">
									<?php echo hsc($reportedPost['topic_title']); ?>
								</td>
								<td class="left overview-content">
									<?php echo hsc($reportedPost['post_content']); ?>
								</td>
								<td>
									<?php echo hsc($reportedPost['post_order']); ?>
								</td>
								<?php if(isset($_GET['type']) && $_GET['type'] === 'condensed'): ?>
								<td>
									<?php echo hsc($reportedPost['num_of_reported_posts']); ?>
								</td>
								<?php else: ?>
								<td>
									<a href="<?php echo HTTP_ADMIN . 'users/' . $reportedPost['user_id'] . '/edit'; ?>">
										<?php echo hsc($reportedPost['username']); ?>
									</a>
								</td>
								<td>
									<a href="<?php echo HTTP_ADMIN . 'users/' . $reportedPost['post_id'] . '/edit'; ?>">
										<?php echo hsc($reportedPost['posted_by']); ?>
									</a>
								</td>
								<?php endif; ?>
								<td>
									<?php echo hsc($reportedPost['num_of_reports']); ?>
								</td>
								<td>
									<?php echo hsc($reportedPost['id']); ?>
								</td>
							</tr>
						<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="6">
									No posts have been reported.
								</td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
				</form>
				<div id="options-form" title="Forum | Reported Posts Options">
					<div class="panel">
						<div class="panel-column">
							<div class="element-top">Options to display</div>
							<div class="element-body options-checklist">
								<form action="<?php echo HTTP_SERVER; ?>forum" method="post">
								<?php foreach($optionsForPage as $optionForPage): ?>
									<label>
										<input type="checkbox" id="option-<?php echo hsc($optionForPage['id']); ?>" class="optionChange"
											   <?php if(in_array($optionForPage['id'], $userOptions)): ?>
											   checked="checked"
											   <?php endif; ?>
										/>
									<?php echo hsc($optionForPage['option_name']); ?>
									</label>
								<?php endforeach; ?>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
