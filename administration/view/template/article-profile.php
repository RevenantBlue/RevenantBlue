<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/articles/articles-c.php';
$title = isset($article) ? 'Articles | ' . hsc($article['title']) : 'Articles | New Article';
require_once 'ui.php';
require_once 'head.php';
loadMainJs();
loadTinyMce();
loadJqueryUi();
loadPlupload();
loadElastic();
loadCodeMirror();
loadUnderscore();
?>
<script type="text/javascript">
var pluploadUrl = HTTP_ADMIN + 'articles';
</script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/timepicker/timepicker.min.js"></script>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/articles.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/articles.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.min.js"></script>
<?php endif; ?>
<script type="text/javascript">
var articleAttach = true, imageTemplates
<?php if(isset($_GET['controller']) && $_GET['controller'] === 'article-profile' && !empty($imageTemplates)): ?>
imageTemplates = JSON.parse(JSON.stringify(<?php echo json_encode($imageTemplates); ?>));
<?php endif; ?>
<?php if(!empty($disableTinymce) && $disableTinymce): ?>
var disableTinymce = true;
<?php else: ?>
var disableTinymce = false;
<?php endif; ?>

$(document).ready(function() {
	cmHtmlEditor = CodeMirror.fromTextArea(document.getElementById('html-editor'), {
		mode:  'htmlmixed',
		indentWithTabs : true,
		indentUnit : 4,
		tabSize : 4,
		lineNumbers : true
		
	});
	cmFullscreenHtmlEditor = CodeMirror.fromTextArea(document.getElementById('fullscreen-html-editor'), {
		mode:  'htmlmixed',
		indentWithTabs : true,
		indentUnit : 4,
		tabSize : 4,
		lineNumbers : true
		
	});
});
</script>
<?php loadMainCss(); ?>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" type="text/css" />
</head>
<body id="main-iframe-body">
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<?php if(isset($_GET['id'])): ?>
					<span>Edit Article</span>
				<?php else: ?>
					<span>Create Article</span>
				<?php endif; ?>
				<div class="menu-darr"></div>
			</a>
			<a class="fltrght" id="toggleSidePanel" href="#">+/- Toggle side panel</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>articles/new">
						<span class="ui-icon ui-icon-document"></span>
						New Article
					</a>
				</li>
				<li>
					<a href="#" id="action-quick-publish-article" class="quick-publish-article">
						<span class="ui-icon ui-icon-check"></span>
						Publish
					</a>
				</li>
				<li>
					<a href="#" id="action-save-article">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-new-article">Save and New</a>
				</li>
				<li>
					<a href="#" id="action-save-close-article">Save and Close</a>
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
					<a href="#" id="action-close-article">
						<span class="ui-icon ui-icon-close"></span>
						Close
					</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($article)): ?>
		<?php displayBreadCrumbs($article['title'], HTTP_ADMIN . 'articles/' . $article['id'] . '/edit', array('title' => 'Article Profile', 'url' => HTTP_ADMIN . 'articles/new')); ?>
		<?php else: ?>
		<?php displayBreadCrumbs(false, false, array('title' => 'Create Article', 'url' => HTTP_ADMIN . 'articles/new')); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="option-66" <?php if(!in_array(66, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save-article">
						<a href="#">
							<span class="ui-icon ui-icon-disk"></span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li id="toolbar-publish-article" class="quick-publish-article">
						<a href="#">
							<span class="ui-icon ui-icon-check"> </span>
							<span class="toolbar-text">Publish</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close-article">
						<a href="#">
							<span class="ui-icon ui-icon-close"> </span>
							<span class="toolbar-text">Close</span>
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
							<span class="ui-icon ui-icon-help"> </span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
				<div class="clear"></div>
			</div>
		</div>
		<div class="clearfix"></div>
		<div id="element-box">
			<form id="adminForm" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post" enctype="multipart/form-data">
				<div>
					<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					<input type="hidden" id="article-action" name="articleAction" />
				</div>
				<div class="profile-main">
					<div id="profile-padding">
						<div class="element">
							<div class="element-top">
								<span>Title</span>
							</div>
							<div class="element-body">
								<?php if(isset($article['id'])): ?>
								<input id="id" type="hidden" name="id" value="<?php echo hsc($article['id']);?>" />
								<?php endif; ?>
								<input id="title" type="text" name="title" placeholder="Enter title here"
									<?php if(isset($_SESSION['article']->title)): ?>
									   value="<?php echo hsc($_SESSION['article']->title); ?>"
									<?php elseif(isset($article['title'])): ?>
									   value="<?php echo hsc($article['title']); ?>"
									<?php endif; ?>
								/>
							</div>
						</div>
						<div class="element content-editor">
							<div class="element-top">Content</div>
							<div class="element-body">
								<div id="content-editor-selection">
									<div id="add-media-container">
										<a id="add-media" href="#">+ Add Media</a>
									</div>
									<ul>
										<li id="content-editor-tab">
											<a href="#html-editor-wrap">HTML</a>
										</li>
										<li id="html-editor-tab">
											<a href="#visual-editor-wrap">Visual</a>
										</li>
									</ul>
									<div id="html-editor-wrap">
										<div class="tinymce-html-editor-wrapper">
											<div class="tinymce-html-editor-ui">
												<ul>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-bold" title="Bold"><strong>b</strong></button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-italic" title="Italic"><em>i</em></button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-link" title="Link">a</button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-bquote" title="Block quote">b-quote</button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-img" title="Image">img</button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-ul" title="Unordered list">ul</button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-ol" title="Ordered list">ol</button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-li" title="List item">li</button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-code" title="Code"><code>code</code></button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-del" title="Del"><del>del</del></button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-ins" title="Ins"><ins>ins</ins></button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-fullscreen" title="Toggle Fullscreen">fullscreen</button>
													</li>
												</ul>
											</div>
											<textarea id="html-editor"><?php if(isset($_SESSION['article']->content)): echo $_SESSION['article']->content; elseif(isset($article['content'])): echo $article['content']; endif; ?></textarea>
										</div>
									</div>
									<div id="visual-editor-wrap">
										<textarea id="content-editor" name="content" spellcheck="true" class="<?php if($disableTinymce): ?>mceNoEditor<?php endif; ?>"><?php if(isset($_SESSION['article']->content)): echo $_SESSION['article']->content; elseif(isset($article['content'])): echo $article['content']; endif; ?></textarea>
									</div>
									<div class="clearfix"> </div>
								</div>
							</div>
							<p id="autosave"></p>
						</div>
						<div class="panel main-panel option-1" <?php if(!in_array(1, $userOptions)): ?> style="display: none;" <?php endif; ?>>
							<div class="panel-column">
								<div class="element content-editor">
									<div class="element-top">Summary</div>
									<div class="element-body">
										<textarea id="summary-editor" name="summary" rows="40"><?php if(isset($_SESSION['article']->summary)): echo $_SESSION['article']->summary; elseif(isset($article['summary'])): echo $article['summary']; endif; ?></textarea>
									</div>
								</div>
							</div>
						</div>
						<?php if(!empty($articleRevisions)): ?>
						<div class="panel main-panel option-9" <?php if(!in_array(9, $userOptions)): ?> style="display: none;" <?php endif; ?>>
							<div class="panel-column">
								<div class="element">
									<div class="element-top">Revisions</div>
									<div class="element-body">
										<?php if(isset($article)): ?>
										<table class="overview no-border width100pcnt">
											<tr>
												<th class=" no-border width60pcnt left">Date Created</th>
												<th class="left">Author</th>
											</tr>
											<?php foreach($articleRevisions as $revision): ?>
											<tr>
												<td class="left">
													<a href="<?php echo HTTP_ADMIN . 'articles/revision/' . $revision['revision_id'] . "/" . $article['id']; ?>">
														<?php echo date('F d, Y - h:i s A', strtotime($revision['revision_date'])); ?>
													</a>
													<?php if(!empty($revision['type'])): ?>
													<span> [ <?php echo hsc($revision['type']); ?> ] </span>
													<?php endif; ?>
												</td>
												<td class="left">
													<?php echo hsc($revision['revision_username']); ?>
												</td>
											</tr>
											<?php endforeach; ?>
										</table>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
				<div class="profile-details">
					<div id="detail-padding">
						<div class="panel">
							<div class="panel-column">
								<div class="element">
									<div class="element-top">Current Image</div>
									<div class="element-body">
										<table class="panel-content width100pcnt">
											<tr>
												<td id="profile-image">
													<?php if(!empty($article['image'])): ?>
													<img class="profile-image" src="<?php echo HTTP_IMAGE . 'articles/' . hsc($article['image']); ?>" alt="Uploaded Image For Article" />
													<?php elseif(!empty($_SESSION['article']->image)): ?>
													<img class="profile-image" src="<?php echo HTTP_IMAGE . 'articles/' . hsc($_SESSION['article']->image); ?>" alt="Uploaded Image For Article" />
													<?php else: ?>
													No Image Uploaded
													<?php endif; ?>
												</td>
											</tr>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="panel">
							<div class="panel-column">
								<div class="element">
									<div class="element-top">Publishing Options</div>
									<div class="element-body">
										<table class="panel-content">
											<tr class="option-5" <?php if(!in_array(5, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label for="alias" class="profile-label">Alias</label>
												</td>
												<td>
													<input id="alias" class="profile-text" type="text" name="alias"
														   <?php if(isset($_SESSION['article']->alias)): ?>
														   value="<?php echo hsc($_SESSION['article']->alias); ?>"
															<?php elseif(isset($article['alias'])): ?>
															value="<?php echo hsc($article['alias']); ?>"
															<?php endif; ?>
														   title="Leave this blank and the backend will fill in a default value, which is the title in lower case and with dashes instead of spaces. You may enter the Alias manually. Use lowercase letters and hypens (-). No spaces or underscores are allowed. The Alias will be used in the SEF URL. Default value will be a date and time if the title is typed in non-latin letters."
													/>
												</td>
											</tr>
											<tr>
												<td><label class="profile-label">State</label></td>
												<td>
													<select id="article-state" name="published" class="profile-slctfont">
														<?php if(aclVerify('publish articles') || aclVerify('administer articles')): ?>
														<option <?php if((isset($_SESSION['article']->published) && $_SESSION['article']->published == 1) || (isset($article['published']) && $article['published'] == 1)): ?>
																selected="selected"
																<?php endif; ?>
																value="1">Published
														</option>
														<option <?php if((isset($_SESSION['article']->published) && $_SESSION['article']->published == 0) || (isset($article['published']) && $article['published'] == 0)): ?>
																selected="selected"
																<?php endif;?>
																value="0">Unpublished
														</option>
														<?php endif; ?>
														<option <?php if((isset($_SESSION['article']->published) && $_SESSION['article']->published == 2) || (isset($article['published']) && $article['published'] == 2)): ?>
																selected="selected"
																<?php endif; ?>
																value="2">Draft
														</option>
														<option <?php if((isset($_SESSION['article']->published) && $_SESSION['article']->published == 3) || (isset($article['published']) && $article['published'] == 3)): ?>
																selected="selected"
																<?php endif; ?>
																value="3">Pending Approval
														</option>
													</select>
												</td>
											</tr>
											<tr>
												<td>
													<label for="featured" class="profile-label">Featured</label>
												</td>
												<td>
													<select id="featured" name="featured" class="profile-slctfont">
														<option <?php if((isset($_SESSION['article']->featured) && $_SESSION['article']->featured == 1) || (isset($article['featured']) && $article['featured'] == 1)): ?>
																selected="selected"
																<?php endif; ?>
																value="1">Yes
														</option>
														<option <?php if((isset($_SESSION['article']->featured) && $_SESSION['article']->featured == 0) || (isset($article['featured']) && $article['featured'] == 0)): ?>
																selected="selected"
																<?php elseif(!isset($_SESSION['article']) && !isset($article)): ?>
																selected="selected"
																<?php endif;?>
																value="0">No
														</option>
													</select>
												</td>
											</tr>
											<tr class="option-6" <?php if(!in_array(6, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td class="vAlignTop">
													<label class="profile-label">Image</label>
												</td>
												<td>
													<input type="hidden" id="img" name="image"
														   <?php if(!empty($article['image'])): ?>
														   value="<?php echo hsc($article['image']); ?>"
														   <?php elseif(!empty($_SESSION['article']->image)): ?>
														   value="<?php echo $_SESSION['article']->image; ?>"
														   <?php endif; ?>
													/>
													<input type="hidden" id="img-path" name="imagePath"
														   <?php if(!empty($article['imagePath'])): ?>
														   value="<?php echo hsc($article['imagePath']); ?>"
														   <?php elseif(!empty($_SESSION['article']->imagePath)): ?>
														   value="<?php echo $_SESSION['article']->imagePath; ?>"
														   <?php endif; ?>
													/>
													<button id="upload-img" class="upload-splash-img rb-btn">Upload</button>
													<button id="delete-img" class="upload-splash-img rb-btn">Delete</button>
													<div>
														<label class="fltlft right-space" for="img-width">
															width
															<input type="text" id="img-width" class="smaller-text" title="Enter the width of the image, the height will adjust proportionatly if left empty" />
														</label>
														<label class="fltlft" for="img-height">
															height
															<input type="text" id="img-height" class="smaller-text" title="Enter the height of the image, the width will adjust proportionatly if left empty" />
														</label>
													</div>
												</td>
											</tr>
											<tr class="option-6" <?php if(!in_array(6, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label class="profile-label">Image Alt</label>
												</td>
												<td>
													<input type="text" id="img-alt" class="profile-text" title="Alt tag attribute for the article's image" name="imageAlt"
														   title="The alt attribate the for the uploaded image"
														   <?php if(!empty($article['image_alt'])): ?>
														   value="<?php echo hsc($article['image_alt']); ?>"
														   <?php elseif(!empty($_SESSION['article']->imageAlt)): ?>
														   value="<?php echo hsc($_SESSION['article']->imageAlt); ?>"
														   <?php endif; ?>
													/>
												</td>
											</tr>
											<?php if(aclVerify('administer articles') || aclVerify('edit any article')): ?>
											<tr class="option-7"  <?php if(!in_array(7, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td class="panel-spacing">
													<label for="author">Created By</label>
												</td>
												<td>
													<select id="author" name="author">
													<?php foreach($backendUsers as $backendUser): ?>
														<?php if(!isset($_SESSION['article']->author) && isset($article['author']) && $backendUser['id'] === $article['author']): ?>
														<option value="<?php echo hsc($article['author']); ?>" selected="selected">
															<?php echo hsc($backendUser['username']); ?>
														</option>
														<?php elseif(isset($_SESSION['article']->author) && $backendUser['id'] === $_SESSION['article']->author): ?>
														<option value="<?php echo hsc($backendUser['id']); ?>" selected="selected">
															<?php echo hsc($backendUser['username']);?>
														</option>
														<?php elseif(!isset($_SESSION['article']) && !isset($article) && $_SESSION['userId'] == $backendUser['id']): ?>
														<option value="<?php echo hsc($backendUser['id']); ?>" selected="selected">
															<?php echo hsc($backendUser['username']);?>
														</option>
														<?php else: ?>
														<option value="<?php echo hsc($backendUser['id']); ?>"><?php echo hsc($backendUser['username']);?></option>
														<?php endif; ?>
													<?php endforeach;?>
													</select>
												</td>
											</tr>
											<?php endif; ?>
											<tr class="option-8"  <?php if(!in_array(8, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label class="panel-spacing fltlft" for="date-posted">Date Posted</label>
												</td>
												<td id="create-date-picker">
													<input id="date-posted" class="panel-input profile-text" name="datePosted" type="text"
															<?php if(isset($article['date_posted'])): ?>
															value="<?php echo hsc($article['date_posted']); ?>"
															<?php elseif(isset($_SESSION['article']->datePosted)): ?>
															value="<?php echo hsc($_SESSION['article']->datePosted); ?>"
															<?php endif; ?>
													/>
												</td>
											</tr>
											<?php if(!empty($article['date_edited'])): ?>
											<tr>
												<td>
													<label class="panel-spacing fltlft" for="date-modified">Last Modified</label>
												</td>
												<td>
													<?php echo date('F d, Y h:i s', strtotime($article['date_edited'])); ?>
												</td>
											</tr>
											<?php endif; ?>
											<tr>
												<td>
													<label class="panel-spacing fltlft" for="content-format">Content Format</label>
												</td>
												<td>
													<select id="content-format" name="contentFormat">
													<?php foreach($contentFormats as $contentFormat): ?>
														<?php if(isset($article) && $article['content_format'] === $contentFormat['id']): ?>
														<option value="<?php echo hsc($contentFormat['id']); ?>" selected="selected">
															<?php echo hsc($contentFormat['format_name']); ?>
														</option>
														<?php else: ?>
														<option value="<?php echo hsc($contentFormat['id']); ?>">
															<?php echo hsc($contentFormat['format_name']); ?>
														</option>
														<?php endif; ?>
													<?php endforeach; ?>
													</select>
												</td>
											</tr>
											<tr>
												<td class="vAlignTop">
													<label class="panel-spacing fltlft" for="weight">Weight</label>
												</td>
												<td>
													<input type="text" id="weight" class="profile-weight-slider" name="weight"
														<?php if(isset($article['weight'])): ?>
														   value="<?php echo hsc($article['weight']); ?>"
														<?php elseif(isset($_SESSION['article']->weight)): ?>
														   value="<?php echo hsc($_SESSION['article']->weight); ?>"
														<?php endif; ?>
													/>
													<div id="weight-slider"></div>
												</td>
											</tr>
										</table>
										<div>
											<button id="publish-btn" class="quick-publish-article rb-btn">Publish</button>
										</div>
										<div>
											<button id="draft-btn" class="draft-article-content rb-btn">Draft</button>
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel">
							<div id="article-category-tool" class="panel-column option-2" <?php if(!in_array(2, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<div class="element">
									<div class="element-top">Categories</div>
									<div class="element-body">
										<div id="categories-tabs">
											<ul>
												<li><a href="#categories-tab-1">All</a></li>
												<li><a href="#categories-tab-2">Most Used</a></li>
											</ul>
											<div id="all-category-tabs">
												<div id="categories-tab-1">
													<ul id="category-check-list-all" class="category-check-list">
													<?php foreach($categoryList as $category): ?>
														<li>
														<?php if(!isset($catForArticle) || (isset($catForArticle) && !in_multiarray($category['cat_id'], $catForArticle))):  ?>
															<label id="category-label-<?php echo hsc($category['cat_id']); ?>" class="center-label" style="margin-left:<?php echo (int)$category['root_distance'] * 10; ?>px;">
																<input id="cat-<?php echo hsc($category['cat_id']); ?>" class="cat-<?php echo hsc($category['cat_id']); ?> article-category center-toggle" type="checkbox" name="articleCategories[]" value="<?php echo hsc($category['cat_id']); ?>" />
																<?php echo hsc($category['cat_name']); ?>
															</label>
														<?php else: ?>
															<label id="category-label-<?php echo hsc($category['cat_id']); ?>" class="center-label" style="margin-left:<?php echo (int)$category['root_distance'] * 10; ?>px;">
																<input id="cat-<?php echo hsc($category['cat_id']); ?>" class="cat-<?php echo hsc($category['cat_id']); ?> article-category center-toggle" type="checkbox" name="articleCategories[]" value="<?php echo hsc($category['cat_id']); ?>" checked="checked" />
																<?php echo hsc($category['cat_name']); ?>
															</label>
														<?php endif; ?>
														</li>
													<?php endforeach; ?>
													</ul>
												</div>
												<div id="categories-tab-2">
													<ul id="category-check-list-most-used" class="category-check-list">
													<?php foreach($popularCategories as $popularCategory): ?>
														<li>
															<label class="center-label">
															<?php if(isset($catForArticle) && in_multiarray($popularCategory['category_id'], $catForArticle)): ?>
															<input id="pop-cat-<?php echo hsc($popularCategory['category_id']); ?>"  class="article-category popular-category center-toggle" type="checkbox" name="articleCategories[]" value="<?php echo hsc($popularCategory['category_id']); ?>" checked="checked" />
															<?php else: ?>
															<input id="pop-cat-<?php echo hsc($popularCategory['category_id']); ?>" class="article-category popular-category center-toggle" type="checkbox" name="articleCategories[]" value="<?php echo hsc($popularCategory['category_id']); ?>" />
															<?php endif; ?>
															<?php echo hsc($popularCategory['cat_name']); ?>
															</label>
														</li>
													<?php endforeach; ?>
													</ul>
												</div>
											</div>
										</div>
										<div id="category-adder">
											<h4>
												<a id="category-add-toggle" href="#">+ Add New Category</a>
											</h4>
											<div class="new-category-panel">
												<input id="new-category-name" class="width100pcnt block" type="text" name="categoryName" />
												<select id="new-category-parent" class="width100pcnt block" name="categoryParent" >
												<option value="0">-- Parent Category --</option>
												<?php foreach($categoryList as $category): ?>
													<option id="category-<?php echo hsc($category['cat_id']); ?>" value="<?php echo hsc($category['cat_id']); ?>">
														<?php echo hsc(str_repeat('- ', $category['root_distance']) . $category['cat_name']); ?>
													</option>
												<?php endforeach; ?>
												</select>
												<button id="create-category" name="newCategoryAsync" class="rb-btn">Add New Category</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="article-tags-panel" class="panel">
							<div class="panel-column">
								<div class="element">
									<div class="element-top">Tags</div>
									<div class="element-body panel-padding">
										<div class="form-item clearfix">
											<label for="tags-to-add" class="screen-reader">Tags</label>
											<input type="hidden" id="hidden-tags" name="hiddenTags" value="<?php if(!empty($articleTagsStr)): echo hsc($articleTagsStr); endif; ?>" />
											<input type="text" id="tags-to-add" name="tagsToAdd" size="30" />
											<button id="add-tag" class="rb-btn" name="addTag">Add</button>
											<div class="admin-description">
												<p>Separate each tag with a comma.</p>
											</div>
											<div id="selected-tags" class="form-item">
											<?php if(!empty($articleTags)): ?>
												<?php foreach($articleTags as $articleTag): ?>
												<span>
													<a id="tag-db-<?php echo hsc($articleTag['id']); ?>" class="ui-icon ui-icon-close remove-tag">x</a>
													<?php echo hsc($articleTag['tag_name']); ?>
												</span>
												<?php endforeach; ?>
											<?php endif; ?>
											</div>
										</div>
										<div>
											<a id="show-popular-tags" class="clearfix clearfix" href="#">Select from the popular tags list</a>
										</div>
										<div id="popular-tags" class="vert-space" style="display: none;">
											<div>
											<?php foreach($popularTags as $popularTag): ?>
												<a id="popular-tag-<?php echo hsc($popularTag['id']); ?>" class="popular-tag tag-link tag-spacing" href="#">
													<?php echo $popularTag['tag_name']; ?>
												</a>
											<?php endforeach; ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel">
							<div class="panel-column option-4" <?php if(!in_array(4, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<div class="element">
									<div class="element-top">Article Options</div>
									<div class="element-body">
										<table class="panel-content">
											<tr>
												<td class="panel-spacing">
													<label>Show Intro Text</label>
												</td>
												<td>
													<select id="show-intro-text" name="attribs[show_intro_text]">
														<option value="1" <?php if((isset($article['show_intro_text']) && $article['show_intro_text'] == 1) || (int)$globalSettings['show_intro_text']['value'] === 1): ?>selected="selected"<?php endif; ?>>
															Show
														</option>
														<option value="0" <?php if((isset($article['show_intro_text']) && $article['show_intro_text'] == 0) || (int)$globalSettings['show_intro_text']['value'] === 0): ?>selected="selected"<?php endif; ?>>
															Hide
														</option>
													</select>
												</td>
											</tr>
											<tr>
												<td class="panel-spacing">
													<label>Allow Comments</label>
												</td>
												<td>
													<select id="allow-comments" name="attribs[allow_comments]">
														<option value="1" <?php if((isset($article['allow_comments']) && $article['allow_comments'] == 1) || (int)$globalSettings['allow_comments']['value'] === 1): ?>selected="selected"<?php endif; ?>>
															Allow
														</option>
														<option value="0" <?php if((isset($article['allow_comments']) && $article['allow_comments'] == 0) || (int)$globalSettings['allow_comments']['value'] === 0): ?>selected="selected"<?php endif; ?>>
															Deny
														</option>
													</select>
												</td>
											</tr>
											<tr>
												<td class="panel-spacing">
													<label>Disable Tinymce</label>
												</td>
												<td>
													<select id="disable-tinymce" name="attribs[disable_tinymce]">
														<option value="0" <?php if((isset($article['disable_tinymce']) && $article['disable_tinymce'] == 0)): ?>selected="selected"<?php endif; ?>>
															No
														</option>
														<option value="1" <?php if((isset($article['disable_tinymce']) && $article['disable_tinymce'] == 1)): ?>selected="selected"<?php endif; ?>>
															Yes
														</option>
													</select>
												</td>
											</tr>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="panel">
							<div class="panel-column option-3" <?php if(!in_array(3, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<div class="element">
									<div class="element-top">Metadata Options</div>
									<div class="element-body">
										<div class="metadata-details">
											<table class="panel-content">
												<tr>
													<td class="align-top panel-spacing"><label for="metaDescription">Description</label></td>
													<td>
														<textarea id="metaDescription" name="metaDescription" title="A short description (30 characters or less) for this article." cols="20" rows="2"><?php if(isset($_SESSION['article']->metaDescription)): echo hsc($_SESSION['article']->metaDescription); elseif(isset($article['meta_description'])): echo hsc($article['meta_description']); endif; ?></textarea>
													</td>
												</tr>
												<tr>
													<td class="align-top panel-spacing">
														<label for="metaKeywords">Keywords</label>
													</td>
													<td>
														<textarea id="metaKeywords" name="metaKeywords" title="Comma separated keywords for this article" cols="20" rows="5"><?php if(isset($_SESSION['article']->metaKeywords)): echo hsc($_SESSION['article']->metaKeywords); elseif(isset($article['meta_keywords'])): echo hsc($article['meta_keywords']); endif; ?></textarea>
													</td>
												</tr>
												<tr>
													<td>
														<label for="metaRobots">Robots</label>
													</td>
													<td>
														<input id="metaRobots" type="text" name="metaRobots" class="profile-text" maxlength="100"
															   title="Leave blank unles you do not want this article indexed by search engines.  In that case use: noindex"
															<?php if(isset($_SESSION['article']->metaRobots)): ?>
															   value="<?php echo hsc($_SESSION['article']->metaRobots); ?>"
															<?php elseif(isset($article['meta_robots'])): ?>
															   value="<?php echo hsc($article['meta_robots']); ?>"
															<?php endif; ?>
														/>
													</td>
												</tr>
												<tr>
													<td><label for="metaAuthor">Author</label></td>
													<td>
														<input id="metaAuthor" type="text" name="metaAuthor" title="The author of this article" class="profile-text" maxlength="100"
															<?php if(isset($_SESSION['article']->metaAuthor)): ?>
															   value="<?php echo hsc($_SESSION['article']->metaAuthor); ?>"
															<?php elseif(isset($article['meta_author'])): ?>
															   value="<?php echo hsc($article['meta_author']); ?>"
															<?php endif; ?>
														/>
													</td>
												</tr>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
			<div class="clear"></div>
			<div id="options-form" title="Article Options">
				<div class="panel">
					<div class="panel-column">
						<div class="element">
							<div class="element-top">Options to display</div>
							<div class="element-body options-checklist">
								<form action="<?php echo HTTP_SERVER; ?>articles" method="post">
								<table>
								<?php foreach($optionsForPage as $optionForPage): ?>
									<tr>
										<td>
											<label>
												<input type="checkbox" id="option-<?php echo hsc($optionForPage['id']); ?>" class="optionChange"
													   <?php if(in_array($optionForPage['id'], $userOptions)): ?>
													   checked="checked"
													   <?php endif; ?>
												/>
											<?php echo hsc($optionForPage['option_name']); ?>
											</label>
										</td>
									</tr>
								<?php endforeach; ?>
								</table>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="add-media-window" title="Attach Media">
				<ul>
					<li><a href="#upload-comp">From Computer</a></li>
					<li><a href="#upload-url">From URL</a></li>
					<li><a id="load-media-library" href="#media-lib">Media Library</a></li>
				</ul>
				<div id="upload-comp">
					<iframe id="media-upload-ifr"></iframe>
				</div>
				<div id="upload-url">
				</div>
				<div id="media-lib">
					<iframe id="media-overview-ifr"></iframe>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="fullscreen-overlay" style="display: none;"></div>
<div id="media-window-loader" class="ajax-page-loader" style="display: none;"></div>
<div id="fullscreen-fader" class="fullscreen-overlay" style="display: none;"></div>
<div id="fullscreen-wrapper" style="display: none;">
	<div id="fullscreen-menu"></div>
	<div id="fullscreen-container">
		<label for="fullscreen-title" class="screen-reader">Enter title here</label>
		<input type="text" id="fullscreen-title" placeholder="Enter title here" />
		<div id="fullscreen-editor-wrap">
			<div id="fullscreen-html-menu" class="cirkuitSkin mceExternalToolbar">
				<a class="mceButton mceButtonEnabled mceIcon mce_fullscreen_close close-fullscreen-html-editor" title="Exit Fullscreen">
					<span class="mceIcon mceIcon mce_fullscreen_close_custom"></span>
				</a>
			</div>
			<div id="fullscreen-editor-selection">
				<ul id="fullscreen-editor-selector">
					<li id="fullscreen-editor-tab">
						<a href="#fullscreen-visual-editor-wrap">Visual</a>
					</li>
					<li id="fullscreen-html-editor-tab">
						<a href="#fullscreen-html-editor-wrap">HTML</a>
					</li>
				</ul>
				<div id="fullscreen-visual-editor-wrap">
					<textarea id="fullscreen-editor" class="<?php if($disableTinymce): ?>mceNoEditor<?php endif; ?>"></textarea>
				</div>
				<div id="fullscreen-html-editor-wrap">
					<textarea id="fullscreen-html-editor"></textarea>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>
</div>
<?php displayFooter();?>
<?php articleCleanUp(); ?>
