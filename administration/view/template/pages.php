<?php
namespace RevenantBlue\Admin;
use RevenantBlue\TableSorter;

require_once DIR_ADMIN . 'controller/pages/pages-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Pages';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/pages.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/pages.min.js"></script>
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
				<span>Pages</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
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
		<div id="toolbar-box" class="clearfix option-89" <?php if(!in_array(89, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<?php if(aclVerify('create pages') || aclVerify('administer pages')): ?>
					<li id="toolbar-new-page">
						<a href="<?php echo HTTP_ADMIN; ?>pages/new/">
							<span class="ui-icon ui-icon-plus"></span>
							<span class="toolbar-text">New</span>
						</a>
					</li>
					<?php endif; ?>
					<?php if(aclVerify('administer pages')): ?>
					<li id="toolbar-edit-page">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"></span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<?php endif; ?>
					<?php if(aclVerify('administer pages')): ?>
					<li id="toolbar-publish-page">
						<a href="#">
							<span class="ui-icon ui-icon-check"> </span>
							<span class="toolbar-text">Publish</span>
						</a>
					</li>
					<li id="toolbar-unpublish-page">
						<a href="#">
							<span class="ui-icon ui-icon-radio-off"> </span>
							<span class="toolbar-text">Unpublish</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-delete-page">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-text">Delete</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
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
							<span class="ui-icon ui-icon-help"></span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
				<div class="clear"></div>
			</div>
		</div>
		<div id="element-box">
			<div id="content-padding">
				<form id="adminForm" action="" method="post">
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="page-action" name="pageAction" />
					</div>
					<div class="links links-top"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
					<div class="clearfix"></div>
					<table id="overview">
						<thead>
							<tr class="overview-top">
								<th class="width1pcnt">
									<input id="selectAll" type="checkbox" class="overview-check-all" />
								</th>
								<th class="left">
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/pages', 'search', 'page');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/pages', '', 'page');?>">
									<?php endif; ?>
										Name <?php echo TableSorter::displaySortIcon('page', 'asc'); ?>
									</a>
								</th>
								<th class="width10pcnt">
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/pages', 'search', 'alias');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/pages', '', 'alias');?>">
									<?php endif; ?>
										Alias <?php echo  TableSorter::displaySortIcon('alias', 'asc'); ?>
									</a>
								</th>
								<th class="width7pcnt">
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/pages', 'search', 'published');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/pages', '', 'published');?>">
									<?php endif; ?>
										Published <?php echo  TableSorter::displaySortIcon('published', 'asc'); ?>
									</a>
								</th>
								<th class="width10pcnt">Template</th>
								<th class="width10pcnt">
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/pages', 'search', 'username'); ?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/pages', '', 'username'); ?>">
									<?php endif; ?>
										Author <?php echo TableSorter::displaySortIcon('username', 'asc'); ?>
									</a>
								</th>
								<th class="width10pcnt">
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/pages', 'search', 'date_created');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/pages', '', 'date_created');?>">
									<?php endif; ?>
										Date Created <?php echo TableSorter::displaySortIcon('date_created', 'asc'); ?>
									</a>
								</th>
								<th class="width5pcnt">
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/pages', 'search', 'id'); ?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/pages', '', 'id'); ?>">
									<?php endif; ?>
										Id <?php echo TableSorter::displaySortIcon('id', 'asc'); ?>
									</a>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php if(!empty($pageList)): ?>
						<?php foreach($pageList as $page): ?>
							<tr>
								<td>
									<input id="cb-<?php echo hsc($page['id']); ?>" type="checkbox" class="overview-check" name="pageCheck[]" value="<?php echo hsc($page['id']);?>" />
								</td>
								<td class="left">
									<?php if(aclVerify('administer pages')): ?>
									<a href="<?php echo HTTP_ADMIN . "pages/" . hsc($page['id']); ?>">
										<?php echo hsc($page['page']); ?>
									</a>
									<?php else: ?>
										<?php echo hsc($page['page']); ?>
									<?php endif; ?>
									<?php if($page['published'] == 2): ?>
									<span id="draft-note-<?php echo hsc($page['id']); ?>" class="bold"> - Draft</span>
									<?php endif; ?>
								</td>
								<td>
									<?php echo hsc($page['alias']); ?>
								</td>
								<td id="published-<?php echo hsc($page['id']); ?>">
									<?php if($page['published'] == 1 ): ?>
									<span class="icon-20-check icon-20-spacing"> </span>
									<?php elseif($page['published'] == 0): ?>
									<span class="icon-20-disabled icon-20-spacing"> </span>
									<?php elseif($page['published'] == 2): ?>
									<span class="icon-20-draft icon-20-spacing"> </span>
									<?php elseif($page['published'] == 3): ?>
									<span class="icon-20-pending icon-20-spacing"> </span>
									<?php endif; ?>
								</td>
								<td>
									<?php echo hsc($page['template_name']); ?>
								</td>
								<td>
									<?php echo hsc($page['username']); ?>
								</td>
								<td>
									<?php echo hsc(nicetime($page['date_created'])); ?>
								</td>
								<td>
									<?php echo hsc($page['id']); ?>
								</td>
							</tr>
						<?php endforeach; ?>
						<?php else: ?>
							<tr class="overview-row1">
								<td colspan="11">
									<?php if(isset($_GET['search'])): ?>
									Your search did not match any records.
									<?php elseif(isset($_GET['published']) || isset($_GET['featured']) || isset($_GET['author']) || isset($_GET['category'])): ?>
									No pages available with the current filter.
									<?php else: ?>
									No pages have been created yet.
									<?php endif; ?>
								</td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
					<div class="links links-bottom"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
				</form>
				<div id="options-form" title="Page Options">
					<div class="panel">
						<div class="panel-column">
							<div class="element">
								<div class="element-top">Options to display</div>
								<div class="element-body options-checklist">
									<form action="<?php echo HTTP_SERVER; ?>pages" method="post">
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
<?php pageCleanUp(); ?>
