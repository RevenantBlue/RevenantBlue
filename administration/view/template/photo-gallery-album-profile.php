<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/photogallery/photogallery-c.php';
$title = isset($album) ? 'Photogallery | ' . hsc($album['title']) : 'Photogallery | New Album';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
loadTinyMce();
loadPlupload();
?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/timepicker/timepicker.min.js"></script>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/photogallery.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/photogallery.min.js"></script>
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
				<?php if(isset($_GET['album'])): ?>
					<span>Edit Album</span>
				<?php else: ?>
					<span>Create Album</span>
				<?php endif; ?>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>photogallery/new">
						<span class="ui-icon ui-icon-document"></span>
						New Album
					</a>
				</li>
				<li>
					<a href="#" id="action-publish-album">
						<span class="ui-icon ui-icon-check"></span>
						Publish
					</a>
				</li>
				<li>
					<a href="#" id="action-save-album">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-new-album">Save and New</a>
				</li>
				<li>
					<a href="#" id="action-save-close-album">Save and Close</a>
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
					<a href="#" id="action-close-album">
						<span class="ui-icon ui-icon-close"></span>
						Close
					</a>
				</li>
			</ul>
			<a class="fltrght" id="toggleSidePanel" href="#">+/- Toggle side panel</a>
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($album)): ?>
		<?php displayBreadCrumbs(false, false, array('title' => $album['title'], 'url' => HTTP_ADMIN . 'photogallery/' . $album['id'] . '/edit')); ?>
		<?php else: ?>
		<?php displayBreadCrumbs(false, false, array('title' => 'Create Album', 'url' => HTTP_ADMIN . 'photogallery/new')); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix option-71" <?php if(!in_array(71, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save-album">
						<a href="#">
							<span class="ui-icon ui-icon-disk"> </span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close-album">
						<a href="<?php echo HTTP_ADMIN; ?>photogallery">
							<span class="ui-icon ui-icon-close"> </span>
							<span class="toolbar-text">Close</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-options-album">
						<a id="options" href="#">
							<span class="ui-icon ui-icon-gear"> </span>
							<span class="toolbar-text">Options</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-album">
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
					<input type="hidden" id="gallery-action" name="galleryAction" />
					<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
				</div>
				<div class="profile-main">
					<div id="profile-padding">
						<div class="element">
							<div class="element-top">Title</div>
							<div class="element-body">
									<?php if(isset($album['id'])): ?>
									<input type="hidden" id="id" name="id" value="<?php echo hsc($album['id']);?>" />
									<?php endif; ?>
									<input id="title" type="text" name="title" size="40" placeholder="Enter album name here"
										<?php if(isset($_SESSION['album']->title)): ?>
										value="<?php echo hsc($_SESSION['album']->title); ?>"
										<?php elseif(isset($album['title'])): ?>
										value="<?php echo hsc($album['title']); ?>"
										<?php endif; ?>
									/>
							</div>
						</div>
						<div class="element content-editor">
							<div class="element-top">Description</div>
							<div class="element-body">
								<textarea id="description-editor" name="description" cols="100" rows="20"><?php if(isset($_SESSION['album']->description)): echo $_SESSION['album']->description; elseif(isset($album['description'])): echo $album['description']; endif; ?></textarea>
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
												<td id="album-image">
													<?php if(!empty($album['image'])): ?>
													<a href="<?php echo hsc(HTTP_GALLERY . $album['image']); ?>">
														<img id="album-image" class="profile-image" src="<?php echo hsc(HTTP_IMAGE . 'photogallery/' . $album['image']); ?>" alt="Uploaded Image For Album" />
													</a>
													<?php elseif(!empty($_SESSION['album']->image)): ?>
													<a href="<?php echo hsc(HTTP_GALLERY . $_SESSION['album']->image); ?>">
														<img id="album-image" class="profile-image" src="<?php echo hsc(HTTP_IMAGE . 'photogallery/' . $_SESSION['album']->image); ?>" alt="Uploaded Image For Album" />
													</a>
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
											<tr class="option-42" <?php if(!in_array(42, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td><label for="alias" class="backend_label">Alias</label></td>
												<td>
													<input id="alias" type="text" class="profile-text" name="alias"
														<?php if(isset($_SESSION['album']->alias)): ?>
														   value="<?php echo hsc($_SESSION['album']->alias); ?>"
														   <?php elseif(isset($album['alias'])): ?>
														   value="<?php echo hsc($album['alias']); ?>"
														   <?php endif; ?>
														   title="Leave this blank and the backend will fill in a default value, which is the title in lower case and with dashes instead of spaces. You may enter the Alias manually. Use lowercase letters and hypens (-). No spaces or underscores are allowed. The Alias will be used in the SEF URL. Default value will be a date and time if the title is typed in non-latin letters."
													/>
												</td>
											</tr>
											<tr>
												<td>
													<label class="profile-label">State</label>
												</td>
												<td>
													<select name="state" class="profile-slctfont">
														<option <?php if((isset($_SESSION['album']->published) && $_SESSION['album']->published == 1) || (isset($album['state']) && $album['state'] == 1)): ?>
																selected="selected"
																<?php endif; ?>
																value="1">Published
														</option>
														<option <?php if((isset($_SESSION['album']->published) && $_SESSION['album']->published == 0) || (isset($album['state']) && $album['state'] == 0)): ?>
																selected="selected"
																<?php endif;?>
																value="0">Unpublished
														</option>
													</select>
												</td>
											</tr>
											<tr>
												<td><label class="profile-label">Featured</label></td>
												<td>
													<select name="featured" class="profile-slctfont">
														<option <?php if((isset($_SESSION['album']->featured) && $_SESSION['album']->featured == 1) || (isset($album['featured']) && $album['featured'] == 1)): ?>
																selected="selected"
																<?php endif; ?>
																value="1">Yes
														</option>
														<option <?php if((isset($_SESSION['album']->featured) && $_SESSION['album']->featured == 0) || (isset($album['featured']) && $album['featured'] == 0)): ?>
																selected="selected"
																<?php endif;?>
																value="0">No
														</option>
													</select>
												</td>
											</tr>
											<tr class="option-43" <?php if(!in_array(43, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td class="vAlignTop">
													<label class="profile-label">Image</label>
												</td>
												<td>
													<input type="hidden" id="album-img" name="image"
														   <?php if(!empty($album['image'])): ?>
														   value="<?php echo hsc($album['image']); ?>"
														   <?php elseif(!empty($_SESSION['album']->image)): ?>
														   value="<?php echo $_SESSION['album']->image; ?>"
														   <?php endif; ?>
													/>
													<input type="hidden" id="album-img-path" name="imagePath"
														   <?php if(!empty($album['image_path'])): ?>
														   value="<?php echo hsc($album['image_path']); ?>"
														   <?php elseif(!empty($_SESSION['album']->imagePath)): ?>
														   value="<?php echo $_SESSION['album']->imagePath; ?>"
														   <?php endif; ?>
													/>
													<button id="upload-album-img" class="upload-splash-img rb-btn">Upload</button>
													<button id="delete-album-img" class="upload-splash-img rb-btn">Delete</button>
													<div>
														<label class="fltlft right-space" for="album-img-width">
															width
															<input type="text" id="album-img-width" class="smaller-text" title="Enter the width of the image, the height will adjust proportionatly if left empty" />
														</label>
														<label class="fltlft" for="album-img-height">
															height
															<input type="text" id="album-img-height" class="smaller-text" title="Enter the height of the image, the width will adjust proportionatly if left empty" />
														</label>
													</div>
													<div id="upload-img-info"></div>
												</td>
											</tr>
											<tr class="option-43" <?php if(!in_array(43, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label class="profile-label">Image Alt</label>
												</td>
												<td>
													<input type="text" id="album-img-alt" class="profile-text" title="Alt tag attribute for the album's image" name="imageAlt" name="imageAlt" 
														   title="The alt attribate the for the uploaded image" 
														   <?php if(!empty($album['image_alt'])): ?>
														   value="<?php echo hsc($album['image_alt']); ?>"
														   <?php elseif(!empty($_SESSION['album']->imageAlt)): ?>
														   value="<?php echo hsc($_SESSION['album']->imageAlt); ?>"
														   <?php endif; ?>
													/>
												</td>
											</tr>
											<tr class="option-44" <?php if(!in_array(44, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td class="panel-spacing">
													<label>Created By</label>
												</td>
												<td>
													<select name="createdBy">
													<?php foreach($backendUsers as $backendUser): ?>
														<?php if(!isset($_SESSION['album']->createdBy) && isset($album['created_by']) && $backendUser['id'] === $album['created_by']): ?>
														<option value="<?php echo hsc($album['created_by']); ?>" selected="selected">
															<?php echo hsc($backendUser['username']); ?>
														</option>
														<?php elseif(isset($_SESSION['album']->createdBy) && $backendUser['id'] === $_SESSION['album']->createdBy): ?>
														<option value="<?php echo hsc($backendUser['id']); ?>" selected="selected">
															<?php echo hsc($backendUser['username']);?>
														</option>
														<?php elseif(!isset($_SESSION['album']) && !isset($album) && $_SESSION['userId'] == $backendUser['id']): ?>
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
											<tr class="option-45" <?php if(!in_array(45, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label class="panel-spacing fltlft" for="date-posted">Created Date</label>
												</td>
												<td>
													<input id="date-posted" class="panel-input profile-text" name="dateCreated" type="text"
														<?php if(isset($album['date_created'])): ?>
														   value="<?php echo hsc($album['date_created']); ?>"
														<?php elseif(isset($_SESSION['album']->dateCreated)): ?>
														   value="<?php echo hsc($_SESSION['album']->dateCreated); ?>"
														<?php endif; ?> 
													/>
												</td>
											</tr>
											<tr class="option-48" <?php if(!in_array(48, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label class="panel-spacing panel-text-only fltlft">Modified By</label>
												</td>
												<td>
													<?php if(isset($album['modified_by'])) echo hsc($album['modified_by']); ?>
												</td>
											</tr>
											<tr class="option-48" <?php if(!in_array(48, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label class="panel-spacing panel-text-only fltlft">Modified Date</label>
												</td>
												<td>
													<?php if(isset($album['modify_date'])): ?>
														<?php echo hsc($album['modify_date']); ?>
													<?php elseif(isset($_SESSION['album']->modifyDate)): ?>
														<?php echo hsc($_SESSION['album']->modifyDate); ?>
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
							<div class="panel-column option-46" <?php if(!in_array(46, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<div class="element">
									<div class="element-top">Gallery Options</div>
									<div class="element-body">
										<table class="panel-content">
											<tr>
												<td class="panel-spacing">Template</td>
												<td class="">
													<select id="template" name="template">
													<?php foreach($templates as $template): ?>
														<?php if(isset($_SESSION['album']->template) && $_SESSION['album']->template === $template['id']): ?>
														<option selected="selected" value="<?php echo hsc($template['id']); ?>" title="Thumbnail Width: <?php echo hsc($template['thumbnail_width']); ?> Thumbnail Height: <?php echo hsc($template['thumbnail_height']); ?> Image Width: <?php echo hsc($template['image_width']); ?> Image Height: <?php echo hsc($template['image_height']); ?>"><?php echo hsc($template['template_name'])?></option>
														<?php elseif(isset($album['template']) && $album['template'] === $template['id']): ?>
														<option selected="selected" value="<?php echo hsc($template['id']); ?>" title="Thumbnail Width: <?php echo hsc($template['thumbnail_width']); ?> Thumbnail Height: <?php echo hsc($template['thumbnail_height']); ?> Image Width: <?php echo hsc($template['image_width']); ?> Image Height: <?php echo hsc($template['image_height']); ?>"><?php echo hsc($template['template_name'])?></option>
														<?php else: ?>
														<option value="<?php echo hsc($template['id']); ?>" title="Thumbnail Width: <?php echo hsc($template['thumbnail_width']); ?> Thumbnail Height: <?php echo hsc($template['thumbnail_height']); ?> Image Width: <?php echo hsc($template['image_width']); ?> Image Height: <?php echo hsc($template['image_height']); ?>"><?php echo hsc($template['template_name'])?></option>
														<?php endif; ?>
													<?php endforeach; ?>
													</select>
												</td>
											</tr>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="panel">
							<div class="panel-column  option-47" <?php if(!in_array(47, $userOptions)): ?> style="display: none;" <?php endif; ?>>
								<div class="element">
									<div class="element-top">Metadata Options</div>
									<div class="element-body">
										<div class="metadata-details">
											<table class="panel-content">
												<tr>
													<td class="align-top panel-spacing"><label for="meta_data_desc">Description</label></td>
													<td>
														<textarea id="meta_data_desc" name="metaDescription" title="A short description (30 characters or less) for this album." cols="20" rows="3"><?php if(isset($_SESSION['album']->metaDescription)): echo hsc($_SESSION['album']->metaDescription); elseif(isset($album['meta_description'])): echo hsc($album['meta_description']); endif; ?></textarea>
													</td>
												</tr>
												<tr>
													<td class="align-top panel-spacing">
														<label for="meta_data_keywords">Keywords</label>
													</td>
													<td>
														<textarea id="meta_data_keywords" name="metaKeywords" title="Comma separated keywords for this album"
																  cols="20" rows="3"><?php if(isset($_SESSION['album']->metaKeywords)): echo hsc($_SESSION['album']->metaKeywords); elseif(isset($album['meta_keywords'])): echo hsc($album['meta_keywords']); endif; ?></textarea>
													</td>
												</tr>
												<tr>
													<td>
														<label for="meta_data_robots">Robots</label>
													</td>
													<td>
														<input type="text" id="meta_data_robots" class="profile-text" name="metaRobots" maxlength="100"
															   title="Leave blank unles you do not want this album indexed by search engines.  In that case use: noindex"
															<?php if(isset($_SESSION['album']->metaRobots)): ?>
															   value="<?php echo hsc($_SESSION['album']->metaRobots); ?>"
															<?php elseif(isset($album['meta_robots'])): ?>
															   value="<?php echo hsc($album['meta_robots']); ?>" 
															<?php endif; ?>
														/>
													</td>
												</tr>
												<tr>
													<td>
														<label for="meta_data_author">Author</label>
													</td>
													<td>
														<input type="text" id="meta_data_author" class="profile-text" name="metaAuthor"
															   title="The author of this album" maxlength="100"
															<?php if(isset($_SESSION['album']->metaAuthor)): ?>
															   value="<?php echo hsc($_SESSION['album']->metaAuthor); ?>"
															<?php elseif(isset($album['meta_author'])): ?>
															   value="<?php echo hsc($album['meta_author']); ?>"
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
			<div id="options-form" title="Photogallery Options">
				<div class="panel">
					<div class="panel-column">
						<div class="element">
							<div class="element-top">Options to display</div>
							<div class="element-body options-checklist">
								<form action="<?php echo HTTP_SERVER; ?>photogallery" method="post">
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
<?php galleryCleanUp(); ?>
