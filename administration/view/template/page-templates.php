<?php
namespace RevenantBlue\Admin;
use RevenantBlue\TableSorter;

require_once DIR_ADMIN . 'controller/pages/pages-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Page Templates | Pages | ';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
loadTableDragNDrop();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/pages.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/pages.min.js"></script>
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
				<span>Link Categories</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>pages/templates/new">
						<span class="ui-icon ui-icon-plus"></span>
						New Link Template
					</a>
				</li>
				<li>
					<a href="#" id="action-edit-page-template">Edit</a>
				</li>
				<li>
					<a href="#" id="action-delete-page-template">
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
			<div class="search-wrap">
				<form id="article-search-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="text" id="page-template-search" class="med-small-text overview-search" name="linkTemplateToSearch" placeholder="Search by title" />
					</div>
					<div>
						<button class="sprites search-sprite sprite-space-16" name="submitLinkTemplateSearch" type="submit" value="1"></button>
					</div>
				</form>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix">
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-new-page-template">
						<a href="<?php echo HTTP_ADMIN; ?>pages/templates/new">
							<span class="ui-icon ui-icon-plus"></span>
							<span class="toolbar-text">New</span>
						</a>
					</li>
					<li id="toolbar-edit-page-template">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"></span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-delete-page-template">
						<a href="#">
							<span class="ui-icon ui-icon-trash"> </span>
							<span class="toolbar-text">Delete</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-options-page-template">
						<a id="options" href="#">
							<span class="ui-icon ui-icon-gear"> </span>
							<span class="toolbar-text">Options</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-page-template">
						<a href="#">
							<span class="ui-icon ui-icon-help"> </span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="clearfix"></div>
		<div id="element-box">
			<div id="content-padding">
				<form id="adminForm" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="page-action" name="pageAction" />
					</div>
					<div class="clear"></div>
					<div class="profile-main">
						<div id="main-profile-padding">
							<div class="pages pages-top"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
							<table id="page-templates" class="overview">
								<thead>
									<tr class="overview-top">
										<th class="width1pcnt">
											<input id="selectAll" type="checkbox" class="overview-check-all" />
										</th>
										<th class="left">
											<?php if(isset($_GET['search'])): ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/pages/templates', 'search', 'template_name');?>">
											<?php else: ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/pages/templates', '', 'template_name');?>">
											<?php endif; ?>
												Template name <?php echo TableSorter::displaySortIcon('template_name', 'asc', TRUE); ?>
											</a>
										</th>
										<th class="width10pcnt left">
											<?php if(isset($_GET['search'])): ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/pages/templates', 'search', 'template_alias');?>">
											<?php else: ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/pages/templates', '', 'template_alias');?>">
											<?php endif; ?>
												Alias <?php echo  TableSorter::displaySortIcon('template_alias', 'asc'); ?>
											</a>
										</th>
										<th class="left">
											Description
										</th>
										<th class="width5pcnt">
											<?php if(isset($_GET['search'])): ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/pages/templates', 'search', 'id'); ?>">
											<?php else: ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/pages/templates', '', 'id'); ?>">
											<?php endif; ?>
												Id <?php echo TableSorter::displaySortIcon('id', 'asc'); ?>
											</a>
										</th>
									</tr>
								</thead>
								<tbody>
								<?php if(!empty($pageTemplates)): ?>
								<?php foreach($pageTemplates as $template): ?>
									<tr id="<?php echo hsc($template['id']); ?>">
										<td>
											<input id="cb-<?php echo hsc($template['id']); ?>" type="checkbox" class="overview-check" name="templateCheck[]" value="<?php echo hsc($template['id']);?>" />
										</td>
										<td class="left">
											<a href="<?php echo HTTP_ADMIN . 'pages/templates/' . hsc($template['id']); ?>">
												<?php echo hsc($template['template_name']); ?>
											</a>
										</td>
										<td class="left">
											<?php echo hsc($template['template_alias']); ?>
										</td>
										<td class="left">
											<?php echo hsc($template['template_description']); ?>
										</td>
										<td>
											<?php echo hsc($template['id']); ?>
										</td>
									</tr>
								<?php endforeach; ?>
								<?php else: ?>
									<tr id="no-templates-placeholder">
										<td colspan="11">
											<?php if(isset($_GET['search'])): ?>
											<p>Your search did not match any records.</p>
											<?php else: ?>
											<p>No page templates have been created yet.</p>
											<?php endif; ?>
										</td>
									</tr>
								<?php endif; ?>
								</tbody>
							</table>
							<div class="pages pages-bottom"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
						</div>
					</div>
					<div class="profile-details profile-details-fix">
						<div class="detail-padding">
							<div id="page-template-panel" class="element">
								<div class="element-top">Add Page Template</div>
								<div class="element-body">
									<div class="std-padding">
										<label for="template-name" class=" form-space">Name</label>
										<input type="text" id="template-name" class="form-space" />
										<label for="template-alias" class="form-space">Alias</label>
										<input type="text" id="template-alias" class="form-space" />
										<label for="template-desc" class="form-space">Description</label>
										<textarea id="template-desc" class="form-space" rows="10" name="description"></textarea>
									</div>
									<div class="std-padding">
										<button id="add-page-template" class="rb-btn vert-space">Add Page Template</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
				<div id="options-form" title="Link Template Options">
					<div class="panel">
						<div class="panel-column">
							<div class="element">
								<div class="element-top">Options to display</div>
								<div class="element-body options-checklist">
									<form action="<?php echo HTTP_SERVER; ?>pages/templates" method="post">
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
</div>
<?php displayFooter(); ?>
