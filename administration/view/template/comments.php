<?php
namespace RevenantBlue\Admin;
use RevenantBlue\TableSorter;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/comments/comments-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Comments';
require_once 'head.php';
require_once 'ui.php';
loadJqueryUi();
loadMainJs();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/comments.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/comments.min.js"></script>
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
				<span>Comments</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="#" id="action-edit-comment">Edit</a>
				</li>
				<li>
					<a href="#">Status</a>
					<ul>
						<li>
							<a href="#" id="action-publish-comment">
								<span class="ui-icon ui-icon-check"></span>
								Publish
							</a>
						</li>
						<li>
							<a href="#" id="action-unpublish-comment">
								<span class="ui-icon ui-icon-radio-off"></span>
								Unpublish
							</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#" id="action-delete-comment">
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
				<form id="comment-search-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="text" id="comment-search" class="med-small-text overview-search" name="commentToSearch" placeholder="Search by title" />
					</div>
					<div>
						<button class="sprites search-sprite sprite-space-16" name="submitCommentSearch" type="submit" value="1"></button>
					</div>
				</form>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-96" <?php if(!in_array(96, $userOptions)): ?>style="display: none"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<?php if(aclVerify('edit own comments') || aclVerify('edit all comments') || aclVerify('administer comments')): ?>
					<li id="toolbar-edit-comment">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"></span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<?php endif; ?>
					<?php if(aclVerify('administer comments')): ?>
					<li id="toolbar-publish-comment">
						<a href="#">
							<span class="ui-icon ui-icon-check"></span>
							<span class="toolbar-text">Publish</span>
						</a>
					</li>
					<li id="toolbar-unpublish-comment">
						<a href="#">
							<span class="ui-icon ui-icon-radio-off"></span>
							<span class="toolbar-text">Unpublish</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<?php endif; ?>
					<?php if(aclVerify('delete own comments') || aclVerify('delete all comments') || aclVerify('administer comments')): ?>
					<li id="toolbar-delete-comment">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-text">Delete</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<?php endif; ?>
					<li id="toolbar-options-comment">
						<a href="#">
							<span class="ui-icon ui-icon-gear"></span>
							<span class="toolbar-text">Options</span>
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
								<select name="publishedFilter" class="user-filter" onChange="Javascript: CMS.submitButton('comment', 'publishedFilter');">
									<option selected="selected" disabled="disabled">--State--</option>
									<option value="1">Published</option>
									<option value="0">Unpublished</option>
									<option value="2">Pending</option>
									<option value="3">Spam</option>
									<option value="4">Removed</option>
								</select>
							</div>
						</div>
					</div>
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="comment-action" name="commentAction" value="" />
					</div>
					<div class="links links-top"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu?></div>
					<div class="clearfix"></div>
					<table id="comments-overview" class="overview">
						<?php if(isset($_GET['title'])): ?>
						<tr class="overview-top">
							<th class="width1pcnt">
								<input id="selectAll" type="checkbox" class="overview-check-all" />
							</th>
							<th class="left">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'search', 'com_content'); ?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'published', 'com_content'); ?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), '', 'com_content'); ?>">
								<?php endif; ?>
								Content <?php echo TableSorter::displaySortIcon('com_content', 'asc'); ?>
								</a>
							</th>
							<th class="width10pcnt left">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'search', 'com_author');?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'published', 'com_author');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), '', 'com_author');?>">
								<?php endif; ?>
								Name <?php echo TableSorter::displaySortIcon('com_author', 'asc'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'search', 'com_published');?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'published', 'com_published');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), '', 'com_published');?>">
								<?php endif; ?>
								Published <?php echo  TableSorter::displaySortIcon('com_published', 'asc'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'search', 'com_likes');?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'published', 'com_likes');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), '', 'com_likes');?>">
								<?php endif; ?>
								Likes <?php echo  TableSorter::displaySortIcon('com_likes', 'asc'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'search', 'com_dislikes');?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'published', 'com_dislikes');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), '', 'com_dislikes');?>">
								<?php endif; ?>
								Dislikes <?php echo  TableSorter::displaySortIcon('com_dislikes', 'asc'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'search', 'com_flags');?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'published', 'com_flags');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), '', 'com_flags');?>">
								<?php endif; ?>
								Flags <?php echo  TableSorter::displaySortIcon('com_flags', 'asc'); ?>
								</a>
							</th>
							<th class="width15pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'search', 'com_date'); ?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'published', 'com_date'); ?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments' . urlencode($_GET['title']), '', 'com_date'); ?>">
								<?php endif; ?>
								Date Posted <?php echo TableSorter::displaySortIcon('com_date', 'desc', TRUE); ?>
								</a>
							</th>
							<th class="width10pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'search', 'com_ip'); ?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'published', 'com_ip'); ?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), '', 'com_ip'); ?>">
								<?php endif; ?>
								Ip Address <?php echo TableSorter::displaySortIcon('com_ip', 'asc'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'search', 'com_id'); ?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), 'published', 'com_id'); ?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/' . urlencode($_GET['title']), '', 'com_id'); ?>">
								<?php endif; ?>
								Id <?php echo TableSorter::displaySortIcon('com_id', 'asc'); ?>
								</a>
							</th>
						</tr>
						<?php else: ?>
						<tr class="overview-top">
							<th class="width1pcnt">
								<input id="selectAll" type="checkbox" onClick="Javascript: CMS.checkAll();" />
							</th>
							<th class="left">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'search', 'com_content'); ?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'published', 'com_content'); ?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', '', 'com_content'); ?>">
								<?php endif; ?>
								Content <?php echo TableSorter::displaySortIcon('com_content', 'asc'); ?>
								</a>
							</th>
							<th class="width10pcnt left">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'search', 'com_author');?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'published', 'com_author');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', '', 'com_author');?>">
								<?php endif; ?>
								Name <?php echo TableSorter::displaySortIcon('com_author', 'asc'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'search', 'com_published');?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'published', 'com_published');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', '', 'com_published');?>">
								<?php endif; ?>
								Published <?php echo  TableSorter::displaySortIcon('com_published', 'asc'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'search', 'com_likes');?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'published', 'com_likes');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', '', 'com_likes');?>">
								<?php endif; ?>
								Likes <?php echo  TableSorter::displaySortIcon('com_likes', 'asc'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'search', 'com_dislikes');?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'published', 'com_dislikes');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', '', 'com_dislikes');?>">
								<?php endif; ?>
								Dislikes <?php echo  TableSorter::displaySortIcon('com_dislikes', 'asc'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'search', 'com_flags');?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'published', 'com_flags');?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', '', 'com_flags');?>">
								<?php endif; ?>
								Flags <?php echo  TableSorter::displaySortIcon('com_flags', 'asc'); ?>
								</a>
							</th>
							<th class="width15pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'search', 'com_date'); ?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'published', 'com_date'); ?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', '', 'com_date'); ?>">
								<?php endif; ?>
								Date Posted <?php echo TableSorter::displaySortIcon('com_date', 'desc', TRUE); ?>
								</a>
							</th>
							<th class="width10pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'search', 'com_ip'); ?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'published', 'com_ip'); ?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', '', 'com_ip'); ?>">
								<?php endif; ?>
								Ip Address <?php echo TableSorter::displaySortIcon('com_ip', 'asc'); ?>
								</a>
							</th>
							<th class="width5pcnt">
								<?php if(isset($_GET['search'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'search', 'com_id'); ?>">
								<?php elseif(isset($_GET['published'])): ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', 'published', 'com_id'); ?>">
								<?php else: ?>
								<a class="link" href="<?php echo TableSorter::sortLink('/comments/', '', 'com_id'); ?>">
								<?php endif; ?>
								Id <?php echo TableSorter::displaySortIcon('com_id', 'asc'); ?>
								</a>
							</th>
						</tr>
						<?php endif; ?>
						<?php if(!empty($commentList)): ?>
						<?php foreach($commentList as $num=>$comment): ?>
						<?php $class = ($num % 2) + 1; ?>
						<tr class="overview-row<?php echo $class; ?>">
							<td>
								<input id="cb-<?php echo hsc($num); ?>" type="checkbox" class="overview-check" name="commentCheck[]" value="<?php echo hsc($comment['com_id']);?>" />
							</td>
							<td class="left">
								<?php if(isset($_GET['title'])): ?>
								<p class="hier-levels fltlft"><?php echo str_repeat('|&mdash; ', $comment['root_distance']); ?></p>
								<?php endif; ?>
								<p class="fltlft width90pcnt">
									<a href="<?php echo HTTP_ADMIN . 'comments/' . $comment['com_id'] . '/edit/'; ?>">
										<?php echo substr(strip_tags($comment['com_content']), 0, 200); ?>
										<?php if(strlen($comment['com_content']) >= 200): ?>
										<span> ...</span>
										<?php endif; ?>
									</a>
								</p>
							</td>
							<td class="left"><?php echo hsc($comment['com_author']); ?></td>
							<td id="published-<?php echo hsc($comment['com_id']); ?>">
								<?php if($comment['com_published'] == 1): ?>
								<span class="icon-20-check icon-20-spacing"> </span>
								<?php elseif($comment['com_published'] == 2): ?>
								<span class="icon-20-pending icon-20-spacing"> </span>
								<?php elseif($comment['com_published'] == 3): ?>
								<span class="icon-20-spam icon-20-spacing"> </span>
								<?php elseif($comment['com_published'] == 4): ?>
								<span class="icon-20-trash icon-20-spacing"> </span>
								<?php else: ?>
								<span class="icon-20-disabled icon-20-spacing"> </span>
								<?php endif; ?>
							</td>
							<td><?php echo hsc($comment['com_likes']); ?></td>
							<td><?php echo hsc($comment['com_dislikes']);?></td>
							<td><?php echo hsc($comment['com_flags']);?></td>
							<td><?php echo hsc($comment['com_date']); ?></td>
							<td><?php echo hsc($comment['com_ip']); ?></td>
							<td>
								<?php echo hsc($comment['com_id']); ?>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php else: ?>
						<tr class="overview-row1">
							<td colspan="9"><p>There are no comments.</p></td>
						</tr>
						<?php endif; ?>
					</table>
					<div class="links links-bottom"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu?></div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
