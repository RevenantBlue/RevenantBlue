<?php
namespace RevenantBlue\Admin;
use RevenantBlue\TableSorter;

require_once DIR_ADMIN . 'controller/articles/articles-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Articles';
require_once 'head.php';
require_once 'ui.php';
loadJqueryUi();
loadMainJs();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/articles.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/articles.min.js"></script>
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
				<span>Articles</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>articles/new">
						<span class="ui-icon ui-icon-document"></span>
						New Article
					</a>
				</li>
				<li>
					<a href="#" id="action-edit-article">Edit</a>
				</li>
				<li>
					<a href="#">Status</a>
					<ul>
						<li>
							<a href="#" id="action-publish-article">
								<span class="ui-icon ui-icon-check"></span>
								Publish
							</a>
						</li>
						<li>
							<a href="#" id="action-unpublish-article">
								<span class="ui-icon ui-icon-radio-off"></span>
								Unpublish
							</a>
						</li>
						<li>
							<a href="#" id="action-feature-article">
								<span class="ui-icon ui-icon-star"></span>
								Feature
							</a>
						</li>
						<li>
							<a href="#" id="action-nofeature-article">
								<span class="ui-icon ui-icon-cancel"></span>
								Disregard
							</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#" id="action-delete-article">
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
						<input type="text" id="article-search" class="med-small-text overview-search" name="articleToSearch" placeholder="Search by title" />
					</div>
					<div>
						<button class="sprites search-sprite sprite-space-16" name="submitArticleSearch" type="submit" value="1"></button>
					</div>
				</form>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-65" <?php if(!in_array(65, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<?php if(aclVerify('create article') || aclVerify('administer articles')): ?>
					<li id="toolbar-new">
						<a class="" href="<?php echo HTTP_ADMIN; ?>articles/new/">
							<span class="ui-icon ui-icon-plus"> </span>
							<span class="toolbar-text">New</span>
						</a>
					</li>
					<?php endif; ?>
					<?php if(aclVerify('edit own article') || aclVerify('edit any article') || aclVerify('administer articles')): ?>
					<li id="toolbar-edit">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"> </span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<?php endif; ?>
					<?php  if(aclVerify('administer articles')): ?>
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
					<li id="toolbar-feature">
						<a href="#">
							<span class="ui-icon ui-icon-star"> </span>
							<span class="toolbar-text">Feature</span>
						</a>
					</li>
					<li id="toolbar-nofeature">
						<a href="#">
							<span class="ui-icon ui-icon-cancel"> </span>
							<span class="toolbar-text">Disregard</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<?php endif; ?>
					<?php if(aclVerify('delete own articles') || aclVerify('delete all articles') || aclVerify('administer articles')): ?>
					<li id="toolbar-delete">
						<a href="#">
							<span class="ui-icon ui-icon-trash"> </span>
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
							<span class="ui-icon ui-icon-help"> </span>
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
				<form id="adminForm" action="" method="post">
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
									<option value="2">Draft</option>
									<option value="3">Pending</option>
								</select>
								<select name="featuredFilter" class="user-filter" onChange="Javascript: CMS.submitButton('article', 'featuredFilter');">
									<option selected="selected" disabled="disabled">--Featured--</option>
									<option value="1">Featured</option>
									<option value="0">Not Featured</option>
								</select>
								<select name="categoryFilter" class="user-filter" onChange="Javascript: CMS.submitButton('article', 'categoryFilter');">
									<option selected="selected" disabled="disabled">--Category--</option>
									<?php foreach($categoryList as $category): ?>
									<option value="<?php echo hsc($category['cat_alias']); ?>"><?php echo hsc($category['cat_name']); ?></option>
									<?php endforeach; ?>
								</select>
								<select name="authorFilter" class="user-filter" onChange="Javascript: CMS.submitButton('article', 'authorFilter');">
									<option selected="selected" disabled="disabled">--Author--</option>
									<?php foreach($authors as $author): ?>
									<option value="<?php echo hsc($author['author']);?>"><?php echo hsc($author['username']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="article-action" name="articleAction" />
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
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'search', 'title');?>">
									<?php elseif(isset($_GET['published'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'published', 'title');?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'featured', 'title');?>">
									<?php elseif(isset($_GET['category'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'category', 'title');?>">
									<?php elseif(isset($_GET['tag'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'tag', 'title');?>">
									<?php elseif(isset($_GET['author'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'author', 'title');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', '', 'title');?>">
									<?php endif; ?>
										Title <?php echo TableSorter::displaySortIcon('title', 'asc'); ?>
									</a>
								</th>
								<th class="width7pcnt option-19" <?php if(!in_array(19, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'search', 'published');?>">
									<?php elseif(isset($_GET['published'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'published', 'published');?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'featured', 'published');?>">
									<?php elseif(isset($_GET['category'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'category', 'published');?>">
									<?php elseif(isset($_GET['tag'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'tag', 'published');?>">
									<?php elseif(isset($_GET['author'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'author', 'published');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', '', 'published');?>">
									<?php endif; ?>
										Published <?php echo  TableSorter::displaySortIcon('published', 'asc'); ?>
									</a>
								</th>
								<th class="width7pcnt option-18" <?php if(!in_array(18, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'search', 'featured');?>">
									<?php elseif(isset($_GET['published'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'published', 'featured');?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'featured', 'featured');?>">
									<?php elseif(isset($_GET['category'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'category', 'featured');?>">
									<?php elseif(isset($_GET['tag'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'tag', 'featured');?>">
									<?php elseif(isset($_GET['author'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'author', 'featured');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', '', 'featured');?>">
									<?php endif; ?>
										Featured <?php echo TableSorter::displaySortIcon('featured', 'asc'); ?>
									</a>
								</th>
								<th class="width10pcnt option-11" <?php if(!in_array(11, $userOptions)): ?> style="display: none;" <?php endif; ?>>Categories</th>
								<th class="width15pcnt left option-17" <?php if(!in_array(17, $userOptions)): ?> style="display: none;" <?php endif; ?>>Tags</th>
								<th class="width10pcnt option-12" <?php if(!in_array(12, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'search', 'article_username'); ?>">
									<?php elseif(isset($_GET['published'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'published', 'article_username'); ?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'featured', 'article_username'); ?>">
									<?php elseif(isset($_GET['category'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'category', 'article_username'); ?>">
									<?php elseif(isset($_GET['tag'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'tag', 'article_username');?>">
									<?php elseif(isset($_GET['author'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'author', 'article_username');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', '', 'article_username'); ?>">
									<?php endif; ?>
										Author <?php echo TableSorter::displaySortIcon('article_username', 'asc'); ?>
									</a>
								</th>
								<th class="width10pcnt option-13" <?php if(!in_array(13, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'search', 'date_posted'); ?>">
									<?php elseif(isset($_GET['published'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'published', 'date_posted'); ?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'featured', 'date_posted'); ?>">
									<?php elseif(isset($_GET['category'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'category', 'date_posted'); ?>">
									<?php elseif(isset($_GET['tag'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'tag', 'date_posted');?>">
									<?php elseif(isset($_GET['author'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'author', 'date_posted');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', '', 'date_posted'); ?>">
									<?php endif; ?>
										Date Posted <?php echo TableSorter::displaySortIcon('date_posted', 'desc', TRUE); ?>
									</a>
								</th>
								<th class="width5pcnt option-14" <?php if(!in_array(14, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'search', 'hits'); ?>">
									<?php elseif(isset($_GET['published'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'published', 'hits'); ?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'featured', 'hits'); ?>">
									<?php elseif(isset($_GET['category'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'category', 'hits'); ?>">
									<?php elseif(isset($_GET['tag'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'tag', 'hits');?>">
									<?php elseif(isset($_GET['author'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'author', 'hits');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', '', 'hits'); ?>">
									<?php endif; ?>
										Hits <?php echo TableSorter::displaySortIcon('hits', 'asc'); ?>
									</a>
								</th>
								<th class="width10pcnt option-15" <?php if(!in_array(15, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'search', 'comments'); ?>">
									<?php elseif(isset($_GET['published'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'published', 'comments'); ?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'featured', 'comments'); ?>">
									<?php elseif(isset($_GET['category'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'category', 'comments'); ?>">
									<?php elseif(isset($_GET['tag'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'tag', 'comments');?>">
									<?php elseif(isset($_GET['author'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'author', 'comments');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', '', 'comments'); ?>">
									<?php endif; ?>
										Comments <?php echo TableSorter::displaySortIcon('comments', 'asc'); ?>
									</a>
								</th>
								<th class="width5pcnt option-16" <?php if(!in_array(16, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'search', 'id'); ?>">
									<?php elseif(isset($_GET['published'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'published', 'id'); ?>">
									<?php elseif(isset($_GET['featured'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'featured', 'id'); ?>">
									<?php elseif(isset($_GET['category'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'category', 'id'); ?>">
									<?php elseif(isset($_GET['tag'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'tag', 'id');?>">
									<?php elseif(isset($_GET['author'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', 'author', 'id');?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/articles', '', 'id'); ?>">
									<?php endif; ?>
										Id <?php echo TableSorter::displaySortIcon('id', 'asc'); ?>
									</a>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php if(!empty($articleList)): ?>
						<?php foreach($articleList as $num=>$article): ?>
						<?php $class = ($num % 2) + 1; ?>
							<tr class="overview-row<?php echo hsc($class); ?>">
								<td>
									<input id="cb-<?php echo hsc($num); ?>" type="checkbox" class="overview-check" name="articleCheck[]" value="<?php echo hsc($article['id']);?>" />
								</td>
								<td class="left">
									<?php if(aclVerify('administer articles') || aclVerify('edit any article')
										  || (aclVerify('edit own article') && $article['author'] === $_SESSION['username'])): ?>
									<a href="<?php echo HTTP_ADMIN . "articles/" . $article['id'] . "/edit";?>"><?php echo hsc($article['title']); ?></a>
									<?php else: ?>
									<?php echo hsc($article['title']); ?>
									<?php endif; ?>
									<?php if($article['published'] == 2): ?>
									<span id="draft-note-<?php echo hsc($article['id']); ?>" class="bold"> - Draft</span>
									<?php endif; ?>
								</td>
								<td id="published-<?php echo hsc($article['id']); ?>" class="option-19" <?php if(!in_array(19, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if($article['published'] == 1 ): ?>
									<span class="icon-20-check icon-20-spacing"> </span>
									<?php elseif($article['published'] == 0): ?>
									<span class="icon-20-disabled icon-20-spacing"> </span>
									<?php elseif($article['published'] == 2): ?>
									<span class="icon-20-draft icon-20-spacing"> </span>
									<?php elseif($article['published'] == 3): ?>
									<span class="icon-20-pending icon-20-spacing"> </span>
									<?php endif; ?>
								</td>
								<td id="featured<?php echo hsc($article['id']); ?>" class="option-18" <?php if(!in_array(18, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php if($article['featured'] == 1): ?>
									<span class="icon-20-star icon-20-spacing"> </span>
									<?php else: ?>
									<span class="icon-20-gray-disabled icon-20-spacing"> </span>
									<?php endif; ?>
									<input type="hidden" name="id" value="<?php echo hsc($article['id']); ?>" />
								</td>
								<td class="option-11" <?php if(!in_array(11, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php $catsForArticle = $articles->getCategoriesForArticle($article['id']); ?>
									<?php foreach($catsForArticle as $category): ?>
										<?php echo hsc($categories->getCategoryName($category)) . "<br />"; ?>
									<?php endforeach; ?>
								</td>
								<td class="left option-17" <?php if(!in_array(17, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php foreach($articleTags[$article['id']] as $key => $articleTag): ?>
										<a href="<?php echo HTTP_ADMIN . 'articles?tag=' . $articleTag['tag_alias']; ?>"><?php echo $articleTag['tag_name']; ?></a>
										<?php if($key < count($articleTags[$article['id']]) - 1): ?>
											<?php echo ','; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</td>
								<td class="option-12" <?php if(!in_array(12, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php echo hsc($article['article_username']); ?>
								</td>
								<td class="option-13" <?php if(!in_array(13, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php echo hsc(nicetime($article['date_posted'])); ?>
								</td>
								<td class="option-14" <?php if(!in_array(14, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php echo hsc($article['hits']); ?>
								</td>
								<td class="option-15" <?php if(!in_array(15, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<?php if($articles->getNumOfComments($article['id']) == 0): ?>
									None
									<?php else: ?>
									<a title="View Edit or Delete Comments for this Article" href="<?php echo HTTP_ADMIN . 'comments/' . urlencode($article['alias']);?>">View(<?php echo hsc($articles->getNumOfComments($article['id']));?>)</a>
								<?php endif; ?>
								</td>
								<td class="option-16" <?php if(!in_array(16, $userOptions)): ?> style="display: none;" <?php endif; ?>>
									<?php echo hsc($article['id']); ?>
								</td>
							</tr>
						<?php endforeach; ?>
						<?php else: ?>
							<tr class="overview-row1">
								<td colspan="11">
									<?php if(isset($_GET['search'])): ?>
									Your search did not match any records.
									<?php elseif(isset($_GET['published']) || isset($_GET['featured']) || isset($_GET['author']) || isset($_GET['category'])): ?>
									No articles available with the current filter.
									<?php elseif(isset($_GET['tag'])): ?>
									No articles with the '<?php echo hsc($_GET['tag']); ?>' tag were found.
									<?php else: ?>
									No articles have been created yet.
									<?php endif; ?>
								</td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
					<div class="links links-bottom"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
				</form>
				<div id="options-form" title="Article Options">
					<div class="panel">
						<div class="panel-column">
							<div class="element">
								<div class="element-top">Global Settings</div>
								<div class="element-body">
									<table class="overview width100pcnt no-border">
										<tr>
											<td class="width60pcnt left">
												<label for="show-intro-text" class="center-label">Show Summary in Article</label>
											</td>
											<td>
												<input id="show-intro-text" type="checkbox" class="center-toggle" <?php if((int)$globalSettings['show_intro_text']['value'] === 1): ?>checked="checked"<?php endif; ?> />
											</td>
										</tr>
										<tr>
											<td class="left">
												<label for="allow-comments" class="center-label">Allow article comments</label>
											</td>
											<td>
												<input id="allow-comments" type="checkbox" class="center-toggle" <?php if((int)$globalSettings['allow_comments']['value'] === 1): ?>checked="checked"<?php endif; ?> />
											</td>
										</tr>
										<tr>
											<td class="left">
												<label for="delete-comment-text">Text to show when a comment has been deleted</label>
											</td>
											<td>
												<input id="deleted-comment-text" type="text" class="normal-text" <?php if($globalSettings['deleted_comment_text']['value']): ?>value="<?php echo hsc($globalSettings['deleted_comment_text']['value']);?>"<?php endif; ?> />
											</td>
										</tr>
									</table>
									<div class="vert-space">
										<button id="submit-article-globals">Save</button>
									</div>
								</div>
							</div>
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
</div>
<?php articleCleanUp(); ?>
<?php displayFooter(); ?>
