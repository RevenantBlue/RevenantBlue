<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/categories/categories-c.php';
require_once DIR_ADMIN . 'model/categories/categories-main.php';
$title = 'Categories';
require_once 'head.php';
require_once 'ui.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
loadMainJs();
loadJqueryUi();
loadJqueryNestable();
?>
<script type="text/javascript">
<?php if(!empty($categoriesJSON)): ?>
var categories = <?php echo $categoriesJSON; ?>
<?php endif; ?>
</script>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/categories.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/categories.min.js"></script>
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
				<span>Categories</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>categories/new">
						<span class="ui-icon ui-icon-plus"></span>
						New
					</a>
				</li>
				<li>
					<a href="#" id="action-edit-category">
						Edit
					</a>
				</li>
				<li>
					<a href="#">Status</a>
					<ul>
						<li>
							<a href="#" id="action-publish-category">
								<span class="ui-icon ui-icon-check"></span>
								Publish
							</a>
						</li>
						<li>
							<a href="#" id="action-unpublish-category">
								<span class="ui-icon ui-icon-radio-off"></span>
								Unpublish
							</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#" id="action-delete-category">
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
				<form id="category-search-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="text" id="article-search" class="med-small-text overview-search" name="categoryToSearch" placeholder="Search by category name" />
					</div>
					<div>
						<button class="sprites search-sprite sprite-space-16" name="submitCategorySearch" type="submit" value="1"></button>
					</div>
				</form>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-67" <?php if(!in_array(67, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-new">
						<a class="" href="<?php echo HTTP_ADMIN; ?>categories/new/">
							<span class="ui-icon ui-icon-plus"> </span>
							<span class="toolbar-text">New</span>
						</a>
					</li>
					<li id="toolbar-edit">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"> </span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-publish">
						<a href="#">
							<span class="ui-icon ui-icon-check"> </span>
							<span class="toolbar-text">Publish</span>
						</a>
					</li>
					<li id="toolbar-unpublish">
						<a href="#">
							<span class="ui-icon ui-icon-radio-off"> </span>
							<span class="toolbar-text">Unpublish</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-delete">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-text">Delete</span>
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
							<span class="ui-icon ui-icon-help"></span>
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
				<form id="adminForm" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
					<div class="element filter-menu fltlft">
						<div class="element-top">
							<h3 class="element-head">Filter By</h3>
						</div>
						<div class="element-body">
							<div class="element-body-content">
								<select name="publishedFilter" class="user-filter" onChange="Javascript: CMS.submitButton('article', 'publishedFilter');">
									<option selected="selected" disabled="disabled">--State--</option>
									<option value="1">Published</option>
									<option value="0">Unpublished</option>
								</select>
							</div>
						</div>
					</div>
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="category-action" name="categoryAction" />
					</div>
					<div class="clearfix"></div>
					<?php if(!isset($_GET['search']) && !isset($_GET['published']) && $categoriesJSON !== '[]'): ?>
					<ol id="categories-sortable" class="hier-sortable small-nodes"></ol>
					<?php elseif(isset($_GET['search']) || isset($_GET['published'])): ?>
					<div class="links links-top"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
					<div class="clearfix"></div>
					<table id="overview">
						<tr class="overview-top">
							<th class="width1pcnt">
								<input id="selectAll" type="checkbox" class="overview-check-all" />
							</th>
							<th class="left">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/categories', 'search', 'cat_name'); ?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/categories', 'published', 'cat_name'); ?>">
								<?php endif; ?>
								Category Name <?php echo TableSorter::displaySortIcon('cat_name', 'asc'); ?></a>
							</th>
							<th class="width10pcnt option-20" <?php if(!in_array(20, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/categories', 'search', 'cat_published'); ?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/categories', 'published', 'cat_published'); ?>">
								<?php endif; ?>
								Published <?php echo TableSorter::displaySortIcon('cat_published', 'asc'); ?></a>
							</th>
							<th class="width10pcnt option-22" <?php if(!in_array(22, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								# of Articles
							</th>
							<th class="width5pcnt option-23" <?php if(!in_array(23, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/categories', 'search', 'cat_id'); ?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/categories', 'published', 'cat_id'); ?>">
								<?php endif; ?>
								Id <?php echo TableSorter::displaySortIcon('cat_id', 'asc'); ?></a>
							</th>
						</tr>
						<?php foreach($categoryList as $num => $category): ?>
						<tr class="overview-row">
							<td>
								<input id="cb-<?php echo hsc($num); ?>" type="checkbox" name="categoryCheck[]" value="<?php echo hsc($category['cat_id']);?>" onClick="Javascript: CMS.isChecked(this);" />
							</td>
							<td class="left">
								<a href="<?php echo HTTP_ADMIN . 'categories/' . $category['cat_id'] . "/edit/"; ?>"><?php echo hsc($category['cat_name']); ?></a>
							</td>
							<td id="published<?php echo hsc($category['cat_id']); ?>" class="option-20" <?php if(!in_array(20, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<?php if($category['cat_published'] == 1 ): ?>
								<span class="icon-20-check icon-20-spacing"> </span>
								<?php else: ?>
								<span class="icon-20-disabled icon-20-spacing"> </span>
								<?php endif; ?>
							</td>
							<td class="option-22" <?php if(!in_array(22, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<?php echo hsc($articles->getNumOfArticlesForCategory($category['cat_id'])); ?>
							</td>
							<td class="option-23" <?php if(!in_array(23, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<?php echo hsc($category['cat_id']); ?>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php if(empty($categoryList)): ?>
						<tr class="overview-row1">
							<td colspan="11">
								<?php if(isset($_GET['search'])): ?>
								Your search did not match any records.
								<?php elseif(isset($_GET['published'])): ?>
								No categories available with the current filter.
								<?php endif; ?>
							</td>
						</tr>
						<?php endif; ?>
					</table>
					<div class="links links-bottom"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
					<?php else: ?>
					<div class="element">
						<div class="element-body">
							No Categories have been created yet. 
							<a href="<?php echo HTTP_ADMIN; ?>menus/new">Add Category</a>
						</div>
					</div>
					<?php endif; ?>
				</form>
			</div>
			<div id="options-form" title="Category Options">
				<div class="panel">
					<div class="panel-column">
						<div class="element">
							<div class="element-top">Options to display</div>
							<div class="element-body options-checklist">
								<form action="<?php echo HTTP_SERVER; ?>articles" method="post">
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
