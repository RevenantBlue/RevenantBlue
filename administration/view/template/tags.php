<?php
namespace RevenantBlue\Admin;
use RevenantBlue\TableSorter;

require_once DIR_ADMIN . 'controller/tags/tags-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Tags';
require_once 'head.php';
require_once 'ui.php';
loadJqueryUi();
loadMainJs();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/tags.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/tags.min.js"></script>
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
				<span>Tags</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>tags/new">
						<span class="ui-icon ui-icon-plus"></span>
						New Tag
					</a>
				</li>
				<li>
					<a href="#" id="action-edit-tag">Edit</a>
				</li>
				<li>
					<a href="#" id="action-delete-tag">
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
				<form id="tag-search-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="text" id="tag-search" class="med-small-text overview-search" name="tagToSearch" placeholder="Search by tag name" />
					</div>
					<div>
						<button class="sprites search-sprite sprite-space-16" name="submitTagSearch" type="submit" value="1"></button>
					</div>
				</form>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-72" <?php if(!in_array(72, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-new">
						<a class="" href="<?php echo HTTP_ADMIN; ?>tags/new/">
							<span class="ui-icon ui-icon-plus"> </span>
							<span class="toolbar-text">New</span>
						</a>
					</li>
					<li id="toolbar-edit">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"></span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-delete">
						<a href="#">
							<span class="ui-icon ui-icon-trash"> </span>
							<span class="toolbar-text">Delete</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-options">
						<a id="options" href="#">
							<span class="ui-icon ui-icon-gear"> </span>
							<span class="toolbar-text">Options</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help">
						<a href="#">
							<span class="icon-40-help"> </span>
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
						<input type="hidden" id="tag-action" name="tagAction" value="" />
					</div>
					<div class="clear"></div>
					<div class="profile-main">
						<div id="main-profile-padding">
							<div class="links links-top"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
							<table id="overview" class="width100pcnt fltlft">
								<thead>
									<tr class="overview-top">
										<th class="width1pcnt">
											<input id="selectAll" type="checkbox" class="overview-check-all" />
										</th>
										<th class="left">
											<?php if(isset($_GET['search'])): ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/tags', 'search', 'tag_name', 'desc');?>">
											<?php else: ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/tags', '', 'tag_name', 'desc');?>">
											<?php endif; ?>
												Tag <?php echo TableSorter::displaySortIcon('tag_name', 'asc', TRUE); ?>
											</a>
										</th>
										<th class="width20pcnt left option-77" <?php if(!in_array(77, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											<?php if(isset($_GET['search'])): ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/tags', 'search', 'tag_alias');?>">
											<?php else: ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/tags', '', 'tag_alias');?>">
											<?php endif; ?>
												Alias <?php echo  TableSorter::displaySortIcon('tag_alias', 'asc'); ?>
											</a>
										</th>
										<th class="width40pcnt left option-76" <?php if(!in_array(76, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											Description
										</th>
										<th class="width10pcnt">
											<?php if(isset($_GET['search'])): ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/tags', 'search', 'popularity', 'desc'); ?>">
											<?php else: ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/tags', '', 'popularity', 'desc'); ?>">
											<?php endif; ?>
												Articles <?php echo TableSorter::displaySortIcon('popularity', 'desc'); ?>
											</a>
										</th>
										<th class="width5pcnt option-75" <?php if(!in_array(75, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											<?php if(isset($_GET['search'])): ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/tags', 'search', 'tagId'); ?>">
											<?php else: ?>
											<a class="link" href="<?php echo TableSorter::sortLink('/tags', '', 'tagId'); ?>">
											<?php endif; ?>
												Id <?php echo TableSorter::displaySortIcon('tagId', 'asc'); ?>
											</a>
										</th>
									</tr>
								</thead>
								<tbody>
								<?php if(!empty($tagsList)): ?>
								<?php foreach($tagsList as $num=>$tag): ?>
								<?php $class = ($num % 2) + 1; ?>
									<tr class="overview-row<?php echo hsc($class); ?>">
										<td>
											<input id="cb-<?php echo hsc($num); ?>" type="checkbox" class="overview-check" name="tagCheck[]" value="<?php echo hsc($tag['tagId']);?>" />
										</td>
										<td class="left">
											<a href="<?php echo HTTP_ADMIN . 'tags/' . hsc($tag['tag_alias']); ?>"><?php echo hsc($tag['tag_name']); ?></a>
										</td>
										<td class="left option-77" <?php if(!in_array(77, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											<?php echo hsc($tag['tag_alias']); ?>
										</td>
										<td class="left option-76" <?php if(!in_array(76, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											<?php echo strip_tags($tag['tag_description']); ?>
										</td>
										<td>
											<?php echo hsc($tag['popularity']); ?>
										</td>
										<td class="option-75" <?php if(!in_array(75, $userOptions)): ?>style="display: none;"<?php endif; ?>>
											<?php echo hsc($tag['tagId']); ?>
										</td>
									</tr>
								<?php endforeach; ?>
								<?php else: ?>
									<tr class="overview-row1">
										<td colspan="11">
											<?php if(isset($_GET['search'])): ?>
											Your search did not match any records.
											<?php elseif(isset($_GET['published']) || isset($_GET['featured'])): ?>
											No tags available with the current filter.
											<?php else: ?>
											No tags have been created yet.
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
							<div class="element">
								<div class="element-top">Popular tags</div>
								<div class="element-body">
								<?php foreach($popularTags as $popularTag): ?>
									<a href="<?php echo HTTP_ADMIN . 'tags/' . $popularTag['tag_alias']; ?>" class="popular-tag tag-link tag-spacing"><?php echo hsc($popularTag['tag_name']); ?></a>
								<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
				</form>
				<div id="options-form" title="Tags Options">

				</div>
			</div>
		</div>
		<div class="clear"> </div>
	</div>
</div>
<?php displayFooter(); ?>
