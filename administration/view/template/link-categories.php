<?php
namespace RevenantBlue\Admin;
use RevenantBlue\TableSorter;

require_once DIR_ADMIN . 'controller/links/links-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Link Categories';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
loadTableDragNDrop();
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
				<span>Link Categories</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>links/categories/new">
						<span class="ui-icon ui-icon-plus"></span>
						New Link Category
					</a>
				</li>
				<li>
					<a href="#" id="action-edit-link-category">Edit</a>
				</li>
				<li>
					<a href="#" id="action-delete-link-category">
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
						<input type="text" id="link-category-search" class="med-small-text overview-search" name="linkCategoryToSearch" placeholder="Search by title" />
					</div>
					<div>
						<button class="sprites search-sprite sprite-space-16" name="submitLinkCategorySearch" type="submit" value="1"></button>
					</div>
				</form>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-81" <?php if(!in_array(81, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-new-link-category">
						<a href="<?php echo HTTP_ADMIN; ?>links/categories/new">
							<span class="ui-icon ui-icon-plus"></span>
							<span class="toolbar-text">New</span>
						</a>
					</li>
					<li id="toolbar-edit-link-category">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"></span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-delete-link-category">
						<a href="#">
							<span class="ui-icon ui-icon-trash"> </span>
							<span class="toolbar-text">Delete</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-options-link-category">
						<a id="options" href="#">
							<span class="ui-icon ui-icon-gear"> </span>
							<span class="toolbar-text">Options</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-link-category">
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
						<input type="hidden" id="linkCatAction" name="linkCatAction" />
					</div>
					<div class="clear"></div>
					<div class="profile-main">
						<div id="main-profile-padding">
							<div class="links links-top"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
							<table id="link-categories" class="overview">
								<thead>
									<tr class="overview-top">
										<th class="width1pcnt">
											<input id="selectAll" type="checkbox" class="overview-check-all" />
										</th>
										<th class="left">
											<?php if(isset($_GET['search'])): ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/links/categories', 'search', 'link_name');?>">
											<?php else: ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/links/categories', '', 'link_name');?>">
											<?php endif; ?>
												Category name <?php echo TableSorter::displaySortIcon('category_title', 'asc', TRUE); ?>
											</a>
										</th>
										<th class="width10pcnt option-62 left" <?php if(!in_array(63, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											<?php if(isset($_GET['search'])): ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/links/categories', 'search', 'category_alias');?>">
											<?php else: ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/links/categories', '', 'category_alias');?>">
											<?php endif; ?>
												Alias <?php echo  TableSorter::displaySortIcon('category_alias', 'asc'); ?>
											</a>
										</th>
										<th class="left option-63" <?php if(!in_array(63, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											Description
										</th>
										<?php if(!isset($_GET['search']) && !isset($_GET['sort']) && !isset($_GET['order'])): ?>
										<th id="link-categories-ordering" class="width10pcnt">
											<a class="link" href="<?php echo TableSorter::sortLink('/links/categories', '', 'order_of_item');?>">
											Ordering <?php echo TableSorter::displaySortIcon('order_of_item', 'asc', TRUE); ?>
											</a>
										</th>
										<?php endif; ?>
										<th class="width10pcnt option-64" <?php if(!in_array(64, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											Links
										</th>
										<th class="width5pcnt option-83" <?php if(!in_array(83, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											<?php if(isset($_GET['search'])): ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/links/categories', 'search', 'id'); ?>">
											<?php else: ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/links/categories', '', 'id'); ?>">
											<?php endif; ?>
												Id <?php echo TableSorter::displaySortIcon('id', 'asc'); ?>
											</a>
										</th>
									</tr>
								</thead>
								<tbody>
								<?php if(!empty($linkCategories)): ?>
								<?php foreach($linkCategories as $num => $category): ?>
								<?php $class = ($num % 2) + 1; ?>
									<tr id="<?php echo hsc($category['id']); ?>.<?php echo hsc($category['order_of_item']); ?>">
										<td>
											<input id="cb-<?php echo hsc($category['id']); ?>" type="checkbox" class="overview-check" name="linkCatCheck[]" value="<?php echo hsc($category['id']);?>" />
										</td>
										<td class="left">
											<a href="<?php echo HTTP_ADMIN . 'links/categories/' . hsc($category['id']); ?>"><?php echo hsc($category['category_title']); ?></a>
										</td>
										<td class="left option-62" <?php if(!in_array(63, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											<?php echo hsc($category['category_alias']); ?>
										</td>
										<td class="option-63 left" <?php if(!in_array(63, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											<?php echo hsc($category['category_description']); ?>
										</td>
										<?php if(!isset($_GET['search']) && !isset($_GET['sort']) && !isset($_GET['order'])): ?>
										<td>
											<input type="text" name="orderNumber" size="1" maxlength="4" value="<?php echo hsc($category['order_of_item']); ?>" />
										</td>
										<?php endif; ?>
										<td class="left option-64 center" <?php if(!in_array(64, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											<?php echo hsc($category['num_of_links']); ?>
										</td>
										<td class="option-83" <?php if(!in_array(83, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											<?php echo hsc($category['id']); ?>
										</td>
									</tr>
								<?php endforeach; ?>
								<?php else: ?>
									<tr class="overview-row1">
										<td colspan="11">
											<?php if(isset($_GET['search'])): ?>
											Your search did not match any records.
											<?php elseif(isset($_GET['published']) || isset($_GET['featured'])): ?>
											No link categories available with the current filter.
											<?php else: ?>
											No link categories have been created yet.
											<?php endif; ?>
										</td>
									</tr>
								<?php endif; ?>
								</tbody>
							</table>
							<div class="links links-bottom"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
						</div>
					</div>
					<div class="profile-details profile-details-fix">
						<div class="detail-padding">
							<div id="link-category-panel" class="element">
								<div class="element-top">Add Link Category</div>
								<div class="element-body">
									<div class="std-padding">
										<label for="link-cat-name" class=" form-space">Name</label>
										<input type="text" id="link-cat-name" class="form-space" />
										<label for="link-cat-desc" class="form-space">Description</label>
										<textarea id="link-cat-desc" class="form-space" rows="10"></textarea>
									</div>
									<div class="std-padding">
										<button id="add-link-category" class="rb-btn vert-space">Add Link Category</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
				<div id="options-form" title="Link Category Options">
					<div class="panel">
						<div class="panel-column">
							<div class="element">
								<div class="element-top">Options to display</div>
								<div class="element-body options-checklist">
									<form action="<?php echo HTTP_SERVER; ?>links/categories" method="post">
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
