<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/links/links-c.php';
$title = isset($link) ? 'Links | ' . hsc($link['link_name']) : 'Links | New Link';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadTinyMce();
loadJqueryUi();
loadElastic();
loadPlupload();
?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/timepicker/timepicker.min.js"></script>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/links.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/links.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.min.js"></script>
<?php endif; ?>
<script type="text/javascript"></script>
<?php loadMainCss(); ?>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" type="text/css" />
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/main.css" type="text/css" />
</head>
<body id="main-iframe-body">
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<?php if(isset($_GET['id'])): ?>
					<span>Edit Link</span>
				<?php else: ?>
					<span>Create Link</span>
				<?php endif; ?>
				<div class="menu-darr"></div>
			</a>
			<a class="fltrght" id="toggleSidePanel" href="#">+/- Toggle side panel</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>links/new">
						<span class="ui-icon ui-icon-document"></span>
						New Link
					</a>
				</li>
				<li>
					<a href="#" id="action-quick-publish-link" class="quick-publish-link">
						<span class="ui-icon ui-icon-check"></span>
						Publish
					</a>
				</li>
				<li>
					<a href="#" id="action-save-link">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-new-link">Save and New</a>
				</li>
				<li>
					<a href="#" id="action-save-close-link">Save and Close</a>
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
				<li>
					<a href="#">Help</a>
				</li>
				<li>
					<a href="#">
						About
					</a>
				</li>
				<li>
					<a href="#" id="action-close-link">
						<span class="ui-icon ui-icon-close"></span>
						Close
					</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php if(!empty($link)): ?>
			<?php displayBreadCrumbs('', '', array('title' => $link['link_name'], 'url' => $_SERVER['REQUEST_URI'])); ?>
		<?php else: ?>
			<?php displayBreadCrumbs('', '', array('title' => 'New Link', 'url' => HTTP_ADMIN . 'links/new')); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix option-97" <?php if(!in_array(97, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save-link">
						<a href="#">
							<span class="ui-icon ui-icon-disk"> </span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li id="toolbar-quick-publish-link" class="quick-publish-link">
						<a href="#">
							<span class="ui-icon ui-icon-check"> </span>
							<span class="toolbar-text">Publish</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close-link">
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
		<div class="clear"></div>
		<div id="element-box">
			<form id="adminForm" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post" enctype="multipart/form-data">
				<div>
					<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					<input type="hidden" id="link-action" name="linkAction" />
				</div>
				<div class="profile-main">
					<div id="profile-padding">
						<div class="element">
							<div class="element-top">
								<span>Name</span>
							</div>
							<div class="element-body">
								<input id="id" type="hidden" name="id"
									   <?php if(!empty($link['id'])): ?>
									   value="<?php echo hsc($link['id']);?>"
									   <?php endif; ?>
								/>
								<input id="title" type="text" name="name" placeholder="Enter title here"
									<?php if(isset($_SESSION['link']->title)): ?>
									   value="<?php echo hsc($_SESSION['link']->title); ?>"
									<?php elseif(isset($link['link_name'])): ?>
									   value="<?php echo hsc($link['link_name']); ?>"
									<?php endif; ?> 
								/>
							</div>
						</div>
						<div class="element content-editor">
							<div class="element-top">Description</div>
							<div class="element-body">
								<textarea id="description-editor" name="description"><?php if(isset($_SESSION['link']->description)): echo $_SESSION['link']->description; elseif(isset($link['link_description'])): echo $link['link_description']; endif; ?></textarea>
							</div>
						</div>
						<div class="element">
							<div class="element-top">URL</div>
							<div class="element-body">
								<input type="text" class="main-text" name="url" 
									   <?php if(isset($_SESSION['link']->url)): ?>
									   value="<?php echo hsc($_SESSION['link']->url);  ?>"
									   <?php elseif(isset($link['link_url'])): ?>
									   value="<?php echo hsc($link['link_url']); ?>" 
									   <?php endif; ?>
								/>
								<p>
									Example:
									<code>http://www.revenantblue.com</code>
									— absolute and relative URLs are allowed.
									<br />
									<br />
									If using a relative URL please make sure you make it absolute in your template for SEO and other reasons.
								</p>
							</div>
						</div>
						<div class="element">
							<div class="element-top">Target</div>
							<div class="element-body">
								<label for="target-blank" class="center-label vert-space">
									<input type="radio" id="target-blank" class="center-toggle" name="target" value="_blank"
										<?php if(isset($_SESSION['link']->target) && $_SESSION['link']->target === '_blank'): ?>
										checked="checked"
										<?php elseif(isset($link['link_target']) && $link['link_target'] === '_blank'): ?>
										checked="checked"
										<?php endif; ?>  
									/>
									<code>_blank</code>
									<span> - new tab or window</span>
								</label>
								<label for="target-top" class="vert-space">
									<input type="radio" id="target-top" class="center-toggle" name="target" value="_top" 
										<?php if(isset($_SESSION['link']->target) && $_SESSION['link']->target === '_top'): ?>
										checked="checked"
										<?php elseif(isset($link['link_target']) && $link['link_target'] === '_top'): ?>
										checked="checked"
										<?php endif; ?> 
									/>
									<code>_top</code>
									<span> - current tab or window, with no frames</span>
								</label>
								<label for="target-none" class="center-label vert-space">
									<input type="radio" id="target-none" class="center-toggle" name="target" value="_none" 
										<?php if(isset($_SESSION['link']->target) && $_SESSION['link']->target === '_none'): ?>
										checked="checked"
										<?php elseif(isset($link['link_target']) && $link['link_target'] === '_none'): ?>
										checked="checked"
										<?php endif; ?> 
									/>
									<code>_none</code>
									<span> - current tab or window</span>
								</label>
							</div>
						</div>
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
													<?php if(!empty($link['link_image'])): ?>
													<img class="profile-image" src="<?php echo HTTP_IMAGE . 'links/' . hsc($link['link_image']); ?>" alt="Uploaded Image For Category" />
													<?php elseif(!empty($_SESSION['link']->image)): ?>
													<img class="profile-image" src="<?php echo HTTP_IMAGE . 'links/' . hsc($_SESSION['link']->image); ?>" alt="Uploaded Image For Category" />
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
									<div class="element-top">Link Options</div>
									<div class="element-body clearfix">
										<table class="panel-content">
											<tr>
												<td>
													<label for="rel" class="profile-label">Rel</label>
												</td>
												<td>
													<input id="rel" class="profile-text" type="text" name="rel" 
														   <?php if(isset($_SESSION['link']->rel)): ?>
														   value="<?php echo hsc($_SESSION['link']->rel); ?>"
														   <?php elseif(!empty($link['link_rel'])): ?>
														   value="<?php echo hsc($link['link_rel']); ?>"
														   <?php endif; ?>
														   title="Leave this blank and the backend will fill in a default value, which is the title in lower case and with dashes instead of spaces. You may enter the Alias manually. Use lowercase letters and hypens (-). No spaces or underscores are allowed. The Alias will be used in the SEF URL. Default value will be a date and time if the title is typed in non-latin letters."
													/>
												</td>
											</tr>
											<tr>
												<td class="vAlignTop">
													<label class="profile-label">Image</label>
												</td>
												<td>
													<input type="hidden" id="img" name="image"
														   <?php if(!empty($link['link_image'])): ?>
														   value="<?php echo hsc($link['link_image']); ?>"
														   <?php elseif(!empty($_SESSION['link']->image)): ?>
														   value="<?php echo $_SESSION['link']->image; ?>"
														   <?php endif; ?>
													/>
													<input type="hidden" id="img-path" name="imagePath"
														   <?php if(!empty($link['link_image_path'])): ?>
														   value="<?php echo hsc($link['link_image_path']); ?>"
														   <?php elseif(!empty($_SESSION['link']->imagePath)): ?>
														   value="<?php echo $_SESSION['link']->imagePath; ?>"
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
													<div id="upload-img-info"></div>
												</td>
											</tr>
											<tr>
												<td>
													<label class="profile-label">Image Alt</label>
												</td>
												<td>
													<input type="text" id="img-alt" class="profile-text" title="Alt tag attribute for the link's image" name="imageAlt" name="imageAlt" 
														   title="The alt attribate the for the uploaded image" 
														   <?php if(!empty($link['image_alt'])): ?>
														   value="<?php echo hsc($link['image_alt']); ?>"
														   <?php elseif(!empty($_SESSION['link']->imageAlt)): ?>
														   value="<?php echo hsc($_SESSION['link']->imageAlt); ?>"
														   <?php endif; ?>
													/>
												</td>
											</tr>
											<tr>
												<td>
													<label class="profile-label">State</label>
												</td>
												<td>
													<select id="link-state" name="published" class="profile-slctfont">
														<?php if(aclVerify('publish articles') || aclVerify('administer articles')): ?>
														<option <?php if(isset($_SESSION['link']->published) && $_SESSION['link']->published == 1): ?>
																selected="selected"
																<?php elseif(isset($link['published']) && $link['published'] == 1): ?>
																selected="selected"
																<?php endif; ?>
																value="1">Published
														</option>
														<option <?php if((isset($_SESSION['link']->published) && $_SESSION['link']->published == 0)): ?>
																selected="selected"
																<?php elseif(isset($link['published']) && $link['published'] == 0): ?>
																selected="selected"
																<?php endif;?>
																value="0">Unpublished
														</option>
														<?php endif; ?>
													</select>
												</td>
											</tr>
											<tr>
												<td class="vAlignTop">
													<label class="panel-spacing fltlft" for="weight">Weight</label>
												</td>
												<td>
													<input type="text" id="weight" class="profile-weight-slider" name="weight"
														<?php if(isset($link['link_weight'])): ?>
														   value="<?php echo hsc($link['link_weight']); ?>"
														<?php elseif(isset($_SESSION['link']->weight)): ?>
														   value="<?php echo hsc($_SESSION['link']->weight); ?>"
														<?php endif; ?>
													/>
													<div id="weight-slider"></div>
												</td>
											</tr>
										</table>
										<div>
											<button id="publish-link-btn" class="publish-btn quick-publish-link rb-btn">Publish</button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel">
							<div id="category-tool" class="panel-column">
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
													<?php foreach($linkCategories as $category): ?>
														<li>
														<?php if(!empty($catIdsForLink) && in_array($category['id'], $catIdsForLink)): ?>
															<label id="label-li-<?php echo hsc($category['id']); ?>" class="center-label">
																<input id="cat-<?php echo hsc($category['id']); ?>" class="center-toggle category-check" type="checkbox" name="linkCategories[]" value="<?php echo hsc($category['id']); ?>" checked="checked" />
																<?php echo hsc($category['category_title']); ?>
															</label>
														<?php else: ?>
															<label id="label-li-<?php echo hsc($category['id']); ?>" class="center-label">
																<input id="cat-<?php echo hsc($category['id']); ?>" class="center-toggle category-check" type="checkbox" name="linkCategories[]" value="<?php echo hsc($category['id']); ?>" />
																<?php echo hsc($category['category_title']); ?>
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
															<?php if(!empty($catIdsForLink) && in_array($popularCategory['id'], $catIdsForLink)): ?>
															<input id="pop-cat-<?php echo hsc($popularCategory['id']); ?>" class="popular-category center-toggle category-check" type="checkbox" name="linkeCategories[]" value="<?php echo hsc($popularCategory['id']); ?>" checked="checked" />
															<?php else: ?>
															<input id="pop-cat-<?php echo hsc($popularCategory['id']); ?>"  class="popular-category center-toggle category-check" type="checkbox" name="linkCategories[]" value="<?php echo hsc($popularCategory['id']); ?>" />
															<?php endif; ?>
															<?php echo hsc($popularCategory['category_title']); ?>
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
												<button id="submit-new-category" class="rb-btn" name="newCategoryAsync">Add New Category</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
			<div class="clear"></div>
		</div>
	</div>
</div>
<?php displayFooter();?>
