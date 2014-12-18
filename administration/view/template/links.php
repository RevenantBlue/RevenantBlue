<?php
namespace RevenantBlue\Admin;
use RevenantBlue\TableSorter;

require_once DIR_ADMIN . 'controller/links/links-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Links';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/links.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/links.min.js"></script>
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
				<span>Links</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>links/new">
						<span class="ui-icon ui-icon-plus"></span>
						New Link
					</a>
				</li>
				<li>
					<a href="#" id="action-edit-link">Edit</a>
				</li>
				<li>
					<a href="#">Status</a>
					<ul>
						<li>
							<a href="#" id="action-publish-link">
								<span class="ui-icon ui-icon-check"></span>
								Publish
							</a>
						</li>
						<li>
							<a href="#" id="action-unpublish-link">
								<span class="ui-icon ui-icon-radio-off"></span>
								Unpublish
							</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#" id="action-delete-link">
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
						<input type="text" id="article-search" class="med-small-text overview-search" name="linkToSearch" placeholder="Search by title" />
					</div>
					<div>
						<button class="sprites search-sprite sprite-space-16" name="submitLinkSearch" type="submit" value="1"></button>
					</div>
				</form>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-70" <?php if(!in_array(70, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-new-link">
						<a href="<?php echo HTTP_ADMIN; ?>links/new/">
							<span class="ui-icon ui-icon-plus"></span>
							<span class="toolbar-text">New</span>
						</a>
					</li>
					<li id="toolbar-edit-link">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"></span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-publish-link">
						<a href="#">
							<span class="ui-icon ui-icon-check"></span>
							<span class="toolbar-text">Publish</span>
						</a>
					</li>
					<li id="toolbar-unpublish-link">
						<a href="#">
							<span class="ui-icon ui-icon-radio-off"></span>
							<span class="toolbar-text">Unpublish</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-delete-link">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-trash">Delete</span>
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
							<span class="toolbar=text">Help</span>
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
					<div class="element filter-menu fltlft">
						<div class="element-top">
							<h3 class="element-head">Filter By</h3>
						</div>
						<div class="element-body">
							<div class="element-body-content">
								<select id="link-published-filter" name="publishedFilter" class="user-filter">
									<option selected="selected" disabled="disabled">--State--</option>
									<option value="1">Published</option>
									<option value="0">Unpublished</option>
								</select>
								<select id="link-category-filter" name="categoryFilter" class="user-filter">
									<option selected="selected" disabled="disabled">--Category--</option>
									<?php foreach($allLinkCategories as $catId => $catTitle): ?>
									<option value="<?php echo hsc($catId); ?>"><?php echo hsc($catTitle); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="link-action" name="linkAction" />
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
									<a class="link" href="<?php echo TableSorter::sortLink('/links', 'search', 'link_name');?>">
									<?php elseif(isset($_GET['published'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', 'published', 'link_name');?>">
									<?php elseif(isset($_GET['category'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', 'category', 'link_name');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', '', 'link_name');?>">
									<?php endif; ?>
										Link Name <?php echo TableSorter::displaySortIcon('link_name', 'asc'); ?>
									</a>
								</th>
								<th class="left option-55" <?php if(!in_array(55, $userOptions)): ?> style="display: none;" <?php endif; ?>>URL</th>
								<th class="width7pcnt option-56" <?php if(!in_array(56, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', 'search', 'published');?>">
									<?php elseif(isset($_GET['published'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', 'published', 'published');?>">
									<?php elseif(isset($_GET['category'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', 'category', 'published');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', '', 'published');?>">
									<?php endif; ?>
										Published <?php echo  TableSorter::displaySortIcon('published', 'asc'); ?>
									</a>
								</th>
								<th class="left option-57" <?php if(!in_array(57, $userOptions)): ?> style="display: none;" <?php endif; ?>>Description</th>
								<th class="width15pcnt left option-58" <?php if(!in_array(58, $userOptions)): ?> style="display: none;" <?php endif; ?>>Categories</th>
								<th class="width10pcnt option-59" <?php if(!in_array(59, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', 'search', 'create_date'); ?>">
									<?php elseif(isset($_GET['published'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', 'published', 'create_date'); ?>">
									<?php elseif(isset($_GET['category'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', 'category', 'create_date'); ?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', '', 'create_date'); ?>">
									<?php endif; ?>
										Date Created <?php echo TableSorter::displaySortIcon('create_date', 'desc', TRUE); ?>
									</a>
								</th>
								<th class="width5pcnt option-60" <?php if(!in_array(60, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', 'search', 'id'); ?>">
									<?php elseif(isset($_GET['published'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', 'published', 'id'); ?>">
									<?php elseif(isset($_GET['category'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', 'category', 'id'); ?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/links', '', 'id'); ?>">
									<?php endif; ?>
										Id <?php echo TableSorter::displaySortIcon('id', 'asc'); ?>
									</a>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php if(!empty($linkList)): ?>
						<?php foreach($linkList as $num => $link): ?>
						<?php $class = ($num % 2) + 1; ?>
							<tr class="overview-row<?php echo hsc($class); ?>">
								<td>
									<input id="cb-<?php echo hsc($num); ?>" type="checkbox" class="overview-check" name="linkCheck[]" value="<?php echo hsc($link['id']);?>" />
								</td>
								<td class="left">
									<a href="<?php echo HTTP_ADMIN; ?>links/<?php echo hsc($link['id']); ?>"><?php echo hsc($link['link_name']); ?></a>
								</td>
								<td class="left option-55" <?php if(!in_array(55, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php echo hsc($link['link_url']); ?>
								</td>
								<td id="published<?php echo hsc($link['id']); ?>" class="option-56" <?php if(!in_array(56, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if($link['published'] == 1 ): ?>
									<span class="icon-20-check icon-20-spacing"> </span>
									<?php elseif($link['published'] == 0): ?>
									<span class="icon-20-disabled icon-20-spacing"> </span>
									<?php endif; ?>
								</td>
								<td class="left option-57" <?php if(!in_array(57, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php echo hsc($link['link_description']); ?>
								</td>
								<td class="left option-58" <?php if(!in_array(58, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if(!empty($link['categories'])): ?>
										<?php foreach($link['categories'] as $key => $linkCategory): ?>
										<a href="#"><?php echo hsc($linkCategory['title']); ?></a>
										<?php if($key + 1 < count($link['categories'])): ?>, <?php endif; ?>
										<?php endforeach; ?>
									<?php endif; ?>
								</td>
								<td class="option-59" <?php if(!in_array(59, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php echo hsc(nicetime($link['create_date'])); ?>
								</td>
								<td class="width5pcnt option-60" <?php if(!in_array(60, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php echo hsc($link['id']); ?>
								</td>
							</tr>
						<?php endforeach; ?>
						<?php else: ?>
							<tr class="overview-row1">
								<td colspan="11">
									<?php if(isset($_GET['search'])): ?>
									Your search did not match any records.
									<?php elseif(isset($_GET['published']) || isset($_GET['category'])): ?>
									No links available with the current filter.
									<?php else: ?>
									No links have been created yet.
									<?php endif; ?>
								</td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
					<div class="links links-bottom"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
				</form>
				<div id="options-form" title="Link Options">
					<div class="panel">
						<div class="panel-column">
							<div class="element-top">Options to display</div>
							<div class="element-body options-checklist">
								<form action="<?php echo HTTP_SERVER; ?>links" method="post">
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
