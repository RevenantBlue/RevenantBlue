<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/pages/pages-c.php';

if(isset($revision['title'])) {
	$title = 'Revisions | ' . $revision['title'];
} elseif(isset($newRevision['title'])) {
	$title = 'Revisions | ' . $newRevision['title'];
} else {
	$title = 'Revisions';
}
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/timepicker/timepicker.min.js"></script>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/pages.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/pages.min.js"></script>
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
				<span>Page Revisions</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li id="action-compare-revisions">
					<a href="#">
						<span class="ui-icon ui-icon-transferthick-e-w"></span>
						Compare Revisions
					</a>
				</li>
				<li id="action-restore-revision">
					<a href="#" id="action-quick-publish-page" class="quick-publish-page">
						<span class="ui-icon ui-icon-arrowrefresh-1-e"></span>
						Restore revision
					</a>
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
					<a href="<?php echo HTTP_ADMIN; ?>pages" id="action-close-page">
						<span class="ui-icon ui-icon-close"></span>
						Close
					</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php if(!empty($revision)): ?>
		<?php displayBreadCrumbs($revision['title'], '#', array('title' => 'Revisions', 'url' => '#')); ?>
		<?php elseif(isset($newRevision)): ?>
		<?php displayBreadCrumbs($newRevision['title'], '#', array('title' => 'Revisions', 'url' => '#')); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix">
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-compare-revisions">
						<a href="#">
							<span class="ui-icon ui-icon-transferthick-e-w"></span>
							<span class="toolbar-text">Compare</span>
						</a>
					</li>
					<li id="toolbar-restore-revision">
						<a href="#">
							<span class="ui-icon ui-icon-arrowrefresh-1-e"></span>
							<span class="toolbar-text">Restore</span>
						</a>
					</li>
					<li id="toolbar-delete-revision">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-text">Delete</span>
						</a>
					</li>
					<li class="toolbar-divider"></li>
					<li id="toolbar-cancel">
						<a href="<?php echo HTTP_ADMIN; ?>pages/">
							<span class="ui-icon ui-icon-close"></span>
							<span class="toolbar-text">Close</span>
						</a>
					</li>
					<li class="toolbar-divider"></li>
					<li id="toolbar-help">
						<a href="#">
							<span class="ui-icon ui-icon-help"></span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="clearfix"></div>
		<div id="element-box">
			<?php if(isset($_GET['id']) && isset($_GET['controller']) && $_GET['controller'] == 'page-revisions'): ?>
			<form id="adminForm" action="" method="post" enctype="multipart/form-data">
				<div id="content-padding">
					<div class="element">
						<div class="element-top">Revision</div>
						<div class="element-body">
							<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
							<input type="hidden" id="revision-action" name="revisionAction" />
							<?php if(isset($revision)): ?>
							<table id="page-revision-tbl" class="panel-content width100pcnt">
								<tr>
									<th class="width10pcnt">
										<p>Page</p>
									</th>
									<td class="width90pcnt">
										<p><?php echo hsc($revision['page']); ?></p>
									</td>
								</tr>
								<tr>
									<th class="vAlignTop">
										<p>Body</p>
									</th>
									<td>
										<div class="revision">
										<?php echo $revision['content']; ?>
										</div>
									</td>
								</tr>
							</table>
							<?php elseif(isset($renderedDiff)): ?>
							<table id="revision-tbl" class="panel-content width100pcnt">
								<tr>
									<th class="width10pcnt">
										<p>Page</p>
									</th>
									<td class="width90pcnt">
										<p><?php if(isset($newRevision['page'])) echo hsc($newRevision['page']); ?></p>
									</td>
								</tr>
								<tr>
									<th class="vAlignTop">
										<p>Body</p>
									</th>
									<td>
										<div class="revision">
										<?php echo $renderedDiff; ?>
										</div>
									</td>
								</tr>
							</table>
							<?php endif; ?>
						</div>
					</div>
					<table class="overview width100pcnt">
						<tr class="overview-top">
							<th class="width5pcnt">Select</th>
							<th class="width1pcnt">Old</th>
							<th class="width1pcnt">New</th>
							<th class="left">Date Created</th>
							<th class="width10pcnt">Author</th>
						</tr>
						<?php if(isset($pageId)): ?>
						<?php foreach($pageRevisions as $num => $revision): ?>
						<tr class="overview-row1">
							<td>
								<input id="cb-<?php echo hsc($num); ?>" type="checkbox" class="overview-check" name="revisionCheck[]" value="<?php echo hsc($revision['revision_id']); ?>" />
								<?php if((int)$revision['current'] === 1): ?>
								<input type="hidden" name="currentRevId" value="<?php echo hsc($revision['id']); ?>" />
								<?php endif; ?>
							</td>
							<td>
								<input type="radio" name="oldRevision" value="<?php echo hsc($revision['revision_id']); ?>"
									   <?php if(isset($_GET['compare']) && $_GET['compare'] === $revision['revision_id']): ?>
									   checked="checked"
									   <?php elseif(!isset($_GET['compare']) && $num === count($pageRevisions) - 1): ?>
									   checked="checked"
									   <?php endif; ?>
								/>
							</td>
							<td>
								<input type="radio" name="newRevision" value="<?php echo hsc($revision['revision_id']); ?>"
									   <?php if(isset($_GET['id']) && $_GET['id'] === $revision['revision_id']): ?>
										   checked="checked"
									   <?php elseif(!isset($_GET['id']) && $revision['type'] === "Current Revision"): ?>
										   checked="checked"
									   <?php endif; ?>
								/>
							</td>
							<td class="left">
								<a href="<?php echo HTTP_ADMIN . "pages/revision/" . $revision['revision_id'] . "/" . $pageId; ?>">
									<?php echo hsc(date('M d, Y', strtotime($revision['revision_date']))); ?>
								</a>
								<?php if(!empty($revision['type'])): ?>
								<span>[<?php echo hsc($revision['type']); ?>]</span>
								<?php endif; ?>
							</td>
							<td>
								<?php echo $users->getUsernameById($revision['author']); ?>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>
					</table>
				</div>
			</div>
			<div class="content-details">
				<div id="detail-padding">
					<div class="panel">
						<div class="panel-column">
							<div class="element vert-space">
								<div class="element-top">Granularity</div>
								<div class="element-body">
									<ul id="granularity" class="std-list">
										<li class="block">
											<label class="center-label">
												<input type="radio" class="center-toggle" name="granularity" value="0"
													<?php if(isset($granularity) && $granularity === 0): ?>
													   checked="checked"
													<?php endif; ?>
												/>
												Paragraph
											</label>
										</li>
										<li class="block">
											<label class="center-label">
												<input type="radio" class="center-toggle" name="granularity" value="1"
													<?php if(isset($granularity) && $granularity === 1): ?>
													   checked="checked"
													<?php elseif(!isset($granularity)): ?>
													   checked="checked"
													<?php endif; ?>
												/>
												Sentence
											</label>
										</li>
										<li class="block">
											<label class="center-label">
												<input type="radio" class="center-toggle" name="granularity" value="2"
													<?php if(isset($granularity) && $granularity == 2): ?>
													   checked="checked"
													<?php endif; ?>
												/>
												Word
											</label>
										</li>
										<li class="block">
											<label class="center-label">
												<input type="radio" class="center-toggle" name="granularity" value="3"
													<?php if(isset($granularity) && $granularity === 3): ?>
													   checked="checked"
													<?php endif; ?>
												/>
												Character
											</label>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
			<?php endif; ?>
		<div class="clear"></div>
	</div>
</div>
<?php displayFooter(); ?>
