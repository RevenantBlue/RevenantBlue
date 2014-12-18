<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/categories/categories-c.php';
require_once DIR_ADMIN . 'controller/articles/articles-c.php';
$title = isset($category) ? 'Category | ' . $category['cat_name'] : 'Category | New Category';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
loadTinyMce();
loadPlupload();
?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/timepicker/timepicker.min.js"></script>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/categories.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/categories.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.min.js"></script>
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
				<?php if(isset($_GET['id'])): ?>
					<span>Edit Category</span>
				<?php else: ?>
					<span>Create Category</span>
				<?php endif; ?>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>categories/new">
						<span class="ui-icon ui-icon-plus"></span>
						New Category
					</a>
				</li>
				<li>
					<a href="#" id="action-publish-category">
						<span class="ui-icon ui-icon-check"></span>
						Publish
					</a>
				</li>
				<li>
					<a href="#" id="action-save-category">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-new-category">Save and New</a>
				</li>
				<li>
					<a href="#" id="action-save-close-category">Save and Close</a>
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
					<a href="#" id="action-close-category">
						<span class="ui-icon ui-icon-close"></span>
						Close
					</a>
				</li>
			</ul>
			<a class="fltrght" id="toggleSidePanel" href="#">+/- Toggle side panel</a>
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($category)): ?>
		<?php displayBreadCrumbs(false, false, array('title' => $category['cat_name'], 'url' => HTTP_ADMIN . 'categories/' . $category['cat_id'])); ?>
		<?php else: ?>
		<?php displayBreadCrumbs(false, false, array('title' => 'Create Category', 'url' => HTTP_ADMIN . 'categories/new')); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix option-68" <?php if(!in_array(68, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save">
						<a href="#">
							<span class="ui-icon ui-icon-check"> </span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close">
						<a href="<?php echo HTTP_ADMIN . 'categories/'; ?>">
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
			<form id="adminForm" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">
				<div>
					<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					<input type="hidden" id="category-action" name="categoryAction" />
				</div>
				<div class="profile-main">
					<div id="profile-padding">
						<div class="element">
							<div class="element-top">Title</div>
							<div class="element-body">
								<?php if(isset($category['cat_id'])): ?>
								<input id="id" type="hidden" name="id" value="<?php echo hsc($category['cat_id']);?>" />
								<?php endif; ?>
								<input id="title" type="text" name="title" size="40" placeholder="Enter category name here"
									<?php if(isset($_SESSION['category']->title)): ?> 
									   value="<?php echo hsc($_SESSION['category']->title); ?>"
									<?php elseif(isset($category['cat_name'])): ?>
									   value="<?php echo hsc($category['cat_name']); ?>"
									<?php endif; ?> 
								/>
							</div>
						</div>
						<div class="element content-editor option-29" <?php if(!in_array(29, $userOptions)): ?>style="display: none;"<?php endif; ?>>
							<div class="element-top">Description</div>
							<div class="element-body">
								<label for="description-editor" class="backend_label"></label>
								<div>
									<textarea id="description-editor" name="description" cols="100" rows="20">
									<?php if(isset($_SESSION['category']->description)): ?>
										<?php echo hsc($_SESSION['category']->description); ?>
									<?php elseif(isset($category['cat_description'])): ?>
										<?php echo hsc($category['cat_description']); ?>
									<?php endif; ?>
									</textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="profile-details">
					<div id="detail-padding">
						<div class="panel option-28" <?php if(!in_array(28, $userOptions)): ?> style="display: none;" <?php endif; ?>>
							<div class="panel-column">
								<div class="element">
									<div class="element-top">Current Image</div>
									<div class="element-body">
										<table class="panel-content width100pcnt">
											<tr>
												<td id="profile-image">
													<?php if(!empty($category['cat_image'])): ?>
													<img class="profile-image" src="<?php echo HTTP_IMAGE . 'categories/' . hsc($category['cat_image']); ?>" alt="Uploaded Image For Category" />
													<?php elseif(!empty($_SESSION['category']->image)): ?>
													<img class="profile-image" src="<?php echo HTTP_IMAGE . 'categories/' . hsc($_SESSION['category']->image); ?>" alt="Uploaded Image For Category" />
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
											<tr class="option-25" <?php if(!in_array(25, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td><label for="alias" class="backend_label">Alias</label></td>
												<td>
													<input id="alias" type="text" class="profile-text" name="alias" title="Leave this blank and the backend will fill in a default value, which is the title in lower case and with dashes instead of spaces. You may enter the Alias manually. Use lowercase letters and hypens (-). No spaces or underscores are allowed. The Alias will be used in the SEF URL. Default value will be a date and time if the title is typed in non-latin letters."
														<?php if(isset($_SESSION['category']->alias)): ?>
															value="<?php echo hsc($_SESSION['category']->alias) ?>"
														<?php elseif(isset($category['cat_alias'])): ?>
															value="<?php echo hsc($category['cat_alias']); ?>"
														<?php endif; ?>
													/>
												</td>
											</tr>
											<tr class="option-26" <?php if(!in_array(26, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label class="profile-label">Parent</label>
												</td>
												<td>
													<select name="parent" class="profile-slctfont">
														<option value="0">- No Parent -</option>
													<?php foreach($parentList as $parent): ?>
														<?php if(isset($descendants) && in_multiarray($parent['cat_id'], $descendants)): ?>
															<?php continue; ?>
														<?php elseif(isset($_SESSION['category']->parentCategory) && $_SESSION['category']->parentCategory == $parent['cat_id']): ?>
														<option value="<?php echo hsc($parent['cat_id']); ?>" selected="selected">
															<?php echo "- " . str_repeat('- ', $parent['root_distance']) . hsc($_SESSION['category']->parentCategory); ?>
														</option>
														<?php elseif(isset($category['parent_id']) && $parent['cat_id'] == $category['parent_id']): ?>
														<option value="<?php echo hsc($parent['cat_id']); ?>" selected="selected">
															<?php echo "- " . str_repeat('- ', $parent['root_distance']) . hsc($parent['cat_name']); ?>
														</option>
														<?php elseif(isset($_GET['parent']) && $_GET['parent'] === $parent['cat_id']): ?>
														<option value="<?php echo hsc($parent['cat_id']); ?>" selected="selected">
															<?php echo "- " . str_repeat('- ', $parent['root_distance']) . hsc($parent['cat_name']); ?>
														</option>
														<?php else: ?>
														<option value="<?php echo hsc($parent['cat_id']); ?>">
															<?php echo "- " . str_repeat('- ', $parent['root_distance']) . hsc($parent['cat_name']); ?>
														</option>
														<?php endif; ?>
													<?php endforeach; ?>
													</select>
												</td>
											</tr>
											<tr class="option-27" <?php if(!in_array(27, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td><label class="profile-label">State</label></td>
												<td>
													<select name="published" class="profile-slctfont">
														<option <?php if((isset($_SESSION['category']->published) && $_SESSION['category']->published == 1) || (isset($category['cat_published']) && $category['cat_published'] === 1)): ?>
																selected="selected"
																<?php endif; ?>
																value="1">
															Published
														</option>
														<option <?php if((isset($_SESSION['category']->published) && $_SESSION['category']->published == 0) || (isset($category['cat_published']) && $category['cat_published'] === 0)): ?>
																selected="selected"
																<?php endif;?>
																value="0">
															Unpublished
														</option>
													</select>
												</td>
											</tr>
											<tr class="option-28" <?php if(!in_array(28, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td class="vAlignTop">
													<label class="profile-label">Image</label>
												</td>
												<td>
													<input type="hidden" id="img" name="image"
														   <?php if(!empty($category['cat_image'])): ?>
														   value="<?php echo hsc($category['cat_image']); ?>"
														   <?php elseif(!empty($_SESSION['category']->image)): ?>
														   value="<?php echo $_SESSION['category']->image; ?>"
														   <?php endif; ?>
													/>
													<input type="hidden" id="img-path" name="imagePath"
														   <?php if(!empty($category['cat_image_path'])): ?>
														   value="<?php echo hsc($category['cat_image_path']); ?>"
														   <?php elseif(!empty($_SESSION['category']->imagePath)): ?>
														   value="<?php echo $_SESSION['category']->imagePath; ?>"
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
											<tr class="option-28" <?php if(!in_array(28, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label class="profile-label">Image Alt</label>
												</td>
												<td>
													<input type="text" id="img-alt" class="profile-text" title="Alt tag attribute for the category's image" name="imageAlt" name="imageAlt" 
														   title="The alt attribate the for the uploaded image" 
														   <?php if(!empty($category['image_alt'])): ?>
														   value="<?php echo hsc($category['image_alt']); ?>"
														   <?php elseif(!empty($_SESSION['category']->imageAlt)): ?>
														   value="<?php echo hsc($_SESSION['category']->imageAlt); ?>"
														   <?php endif; ?>
													/>
												</td>
											</tr>
											<tr class="option-30" <?php if(!in_array(30, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td class="panel-spacing">
													<label for="created-by">Created By</label>
												</td>
												<td>
													<select id="created-by" name="createdBy">
													<?php foreach($backendUsers as $backendUser): ?>
														<?php if(!isset($_SESSION['category']->createdBy) && isset($category['cat_created_by']) && $backendUser['id'] === $category['cat_created_by']): ?>
														<option value="<?php echo hsc($category['cat_created_by']); ?>" selected="selected">
															<?php echo hsc($backendUser['username']); ?>
														</option>
														<?php elseif(isset($_SESSION['category']->createdBy) && $backendUser['id'] === $_SESSION['category']->createdBy): ?>
														<option value="<?php echo hsc($backendUser['id']); ?>" selected="selected">
															<?php echo hsc($backendUser['username']);?>
														</option>
														<?php elseif(!isset($_SESSION['category']) && !isset($category) && $_SESSION['userId'] == $backendUser['id']): ?>
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
											<tr class="option-31" <?php if(!in_array(31, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label class="panel-spacing fltlft" for="date-posted">Date Created</label>
												</td>
												<td>
													<input id="date-posted" class="panel-input profile-text" name="datePosted" type="text"
														<?php if(isset($category['cat_date_posted'])): ?>
														   value="<?php echo hsc($category['cat_date_posted']); ?>"
														<?php elseif(isset($_SESSION['category']->datePosted)): ?>
															value="<?php echo hsc($_SESSION['category']->datePosted); ?>"
														<?php endif; ?>
													/>
												</td>
											</tr>
											<tr class="option-32" <?php if(!in_array(32, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label class="panel-spacing panel-text-only fltlft">Modified By</label>
												</td>
												<td>
													<?php if(isset($category['cat_modified_by'])) echo hsc($category['cat_modified_by']); ?>
												</td>
											</tr>
											<tr class="option-33" <?php if(!in_array(33, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label class="panel-spacing panel-text-only fltlft" for="date-posted">Modified Date</label>
												</td>
												<td>
													<?php if(isset($category['cat_modify_date'])): ?>
														<?php echo hsc($category['cat_modify_date']); ?>
													<?php elseif(isset($_SESSION['category']->modifyDate)): ?>
														<?php echo hsc($_SESSION['category']->modifyDate); ?>
													<?php endif; ?>
												</td>
											</tr>
										</table>
									<div class="clear"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel">
							<div class="panel-column option-24" <?php if(!in_array(24, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<div class="element">
									<div class="element-top">Category Options</div>
									<div class="element-body">

									</div>
								</div>
							</div>
						</div>
						<div class="panel">
							<div class="panel-column option-34" <?php if(!in_array(34, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<div class="element">
									<div class="element-top">Metadata Options</div>
									<div class="element-body">
										<div class="metadata-details">
											<table class="panel-content">
												<tr>
													<td class="align-top panel-spacing"><label for="metaDescription">Description</label></td>
													<td>
														<textarea id="metaDescription" name="metaDescription" title="A short description (30 characters or less) for this category." cols="20" rows="2"><?php if(isset($_SESSION['category']->metaDescription)): echo hsc($_SESSION['category']->metaDescription); elseif(isset($category['meta_description'])): echo hsc($category['meta_description']); endif; ?></textarea>
													</td>
												</tr>
												<tr>
													<td class="align-top panel-spacing">
														<label for="metaKeywords">Keywords</label>
													</td>
													<td>
														<textarea id="metaKeywords" name="metaKeywords" title="Comma separated keywords for this category" cols="20" rows="5"><?php if(isset($_SESSION['category']->metaKeywords)):echo hsc($_SESSION['category']->metaKeywords); elseif(isset($category['meta_keywords'])): echo hsc($category['meta_keywords']); endif; ?></textarea>
													</td>
												</tr>
												<tr>
													<td><label for="meta_data_robots">Robots</label></td>
													<td>
														<input type="text" id="meta_data_robots" class="profile-text" name="metaRobots" maxlength="100"
															   title="Leave blank unles you do not want this category indexed by search engines.  In that case use: noindex"
															<?php if(isset($_SESSION['category']->metaRobots)): ?>
															   value="<?php echo hsc($_SESSION['category']->metaRobots); ?>"
															<?php elseif(isset($category['meta_robots'])): ?>
																value="<?php echo hsc($category['meta_robots']); ?>"
															<?php endif; ?>
														/>
													</td>
												</tr>
												<tr>
													<td><label for="metaAauthor">Author</label></td>
													<td>
														<input type="text" id="metaAuthor" class="profile-text" name="metaAuthor" title="The author of this category" maxlength="100"
															<?php if(isset($_SESSION['category']->metaAuthor)): ?>
															   value="<?php echo hsc($_SESSION['category']->metaAuthor); ?>"
															<?php elseif(isset($category['meta_author'])): ?>
															   value="<?php echo hsc($category['meta_author']); ?>"
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
			<div id="options-form" title="Category Options">
				<div class="panel">
					<div class="panel-column">
						<div class="element">
							<div class="element-top">Options to display</div>
							<div class="element-body options-checklist">
								<form action="<?php echo HTTP_SERVER; ?>categories" method="post">
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
<?php categoryCleanUp(); ?>
