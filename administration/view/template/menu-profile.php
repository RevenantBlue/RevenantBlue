<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/menus/menus-c.php';
require_once DIR_ADMIN . 'controller/articles/articles-c.php';
$title = isset($menu) ? 'Menu | ' . $menu['menu_name'] : 'Menu | New Menu';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
loadTinyMce();
loadPlupload();
?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/timepicker/timepicker.min.js"></script>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/menus.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/menus.min.js"></script>
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
					<span>Edit Menu</span>
				<?php else: ?>
					<span>Create Menu</span>
				<?php endif; ?>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>menus/new">
						<span class="ui-icon ui-icon-plus"></span>
						New Menu
					</a>
				</li>
				<li>
					<a href="#" id="action-publish-menu">
						<span class="ui-icon ui-icon-check"></span>
						Publish
					</a>
				</li>
				<li>
					<a href="#" id="action-save-menu">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-new-menu">Save and New</a>
				</li>
				<li>
					<a href="#" id="action-save-close-menu">Save and Close</a>
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
					<a href="#" id="action-close-menu">
						<span class="ui-icon ui-icon-close"></span>
						Close
					</a>
				</li>
			</ul>
			<a class="fltrght" id="toggleSidePanel" href="#">+/- Toggle side panel</a>
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($menu)): ?>
		<?php displayBreadCrumbs(false, false, array('title' => $menu['menu_name'], 'url' => HTTP_ADMIN . 'menus/' . $menu['id'])); ?>
		<?php else: ?>
		<?php displayBreadCrumbs(false, false, array('title' => 'Create Menu', 'url' => HTTP_ADMIN . 'menus/new')); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix option-107" <?php if(!in_array(107, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save-menu">
						<a href="#">
							<span class="ui-icon ui-icon-check"> </span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close">
						<a href="<?php echo HTTP_ADMIN . 'menus/'; ?>">
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
					<input type="hidden" id="menu-action" name="menuAction" />
				</div>
				<div class="profile-main">
					<div id="profile-padding">
						<div class="element">
							<div class="element-top">Name</div>
							<div class="element-body">
								<?php if(isset($menu['id'])): ?>
								<input id="id" type="hidden" name="id" value="<?php echo hsc($menu['id']);?>" />
								<?php endif; ?>
								<input id="title" type="text" name="name" size="40" placeholder="Enter menu name here"
									<?php if(isset($_SESSION['menu']->name)): ?> 
									   value="<?php echo hsc($_SESSION['menu']->name); ?>"
									<?php elseif(isset($menu['menu_name'])): ?>
									   value="<?php echo hsc($menu['menu_name']); ?>"
									<?php endif; ?> 
								/>
							</div>
						</div>
						<div class="element content-editor option-108" <?php if(!in_array(108, $userOptions)): ?>style="display: none;"<?php endif; ?>>
							<div class="element-top">Description</div>
							<div class="element-body">
								<label for="description-editor" class="backend_label"></label>
								<div>
									<textarea id="description-editor" name="description">
									<?php if(isset($_SESSION['menu']->description)): ?>
										<?php echo hsc($_SESSION['menu']->description); ?>
									<?php elseif(isset($menu['description'])): ?>
										<?php echo hsc($menu['description']); ?>
									<?php endif; ?>
									</textarea>
								</div>
							</div>
						</div>
						<div class="element">
							<div class="element-top">URL</div>
							<div class="element-body">
								<input type="text" class="main-text" name="url" 
									   <?php if(isset($_SESSION['menu']->url)): ?>
									   value="<?php echo hsc($_SESSION['menu']->url);  ?>"
									   <?php elseif(isset($menu['menu_url'])): ?>
									   value="<?php echo hsc($menu['menu_url']); ?>" 
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
					</div>
				</div>
				<div class="profile-details">
					<div id="detail-padding">
						<div class="panel option-109" <?php if(!in_array(109, $userOptions)): ?> style="display: none;" <?php endif; ?>>
							<div class="panel-column">
								<div class="element">
									<div class="element-top">Current Image</div>
									<div class="element-body">
										<table class="panel-content width100pcnt">
											<tr>
												<td id="profile-image">
													<?php if(!empty($menu['image'])): ?>
													<img class="profile-image" src="<?php echo HTTP_IMAGE . 'menus/' . hsc($menu['image']); ?>" alt="Uploaded Image For Menu" />
													<?php elseif(!empty($_SESSION['menu']->image)): ?>
													<img class="profile-image" src="<?php echo HTTP_IMAGE . 'menus/' . hsc($_SESSION['menu']->image); ?>" alt="Uploaded Image For Menu" />
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
											<tr>
												<td>
													<label for="alias" class="backend_label">Alias</label>
												</td>
												<td>
													<input id="alias" type="text" class="profile-text" name="alias" title="Leave this blank and the backend will fill in a default value, which is the title in lower case and with dashes instead of spaces. You may enter the Alias manually. Use lowercase letters and hypens (-). No spaces or underscores are allowed. The Alias will be used in the SEF URL. Default value will be a date and time if the title is typed in non-latin letters."
														<?php if(isset($_SESSION['menu']->alias)): ?>
															value="<?php echo hsc($_SESSION['menu']->alias) ?>"
														<?php elseif(isset($menu['menu_alias'])): ?>
															value="<?php echo hsc($menu['menu_alias']); ?>"
														<?php endif; ?>
													/>
												</td>
											</tr>
											<tr>
												<td class="vAlignTop">
													<label class="profile-label">Parent</label>
												</td>
												<td>
													<select name="parent" class="profile-slctfont" size="10">
														<option value="0">- No Parent -</option>
													<?php foreach($parentList as $parent): ?>
														<?php if(isset($descendants) && in_multiarray($parent['id'], $descendants)): ?>
															<?php continue; ?>
														<?php elseif(isset($_SESSION['menu']->parentMenu) && $_SESSION['menu']->parentMenu == $parent['id']): ?>
														<option value="<?php echo hsc($parent['id']); ?>" selected="selected">
															<?php echo "- " . str_repeat('- ', $parent['root_distance']) . hsc($_SESSION['menu']->parentMenu); ?>
														</option>
														<?php elseif(isset($menu['parent_id']) && $parent['id'] == $menu['parent_id']): ?>
														<option value="<?php echo hsc($parent['id']); ?>" selected="selected">
															<?php echo "- " . str_repeat('- ', $parent['root_distance']) . hsc($parent['name']); ?>
														</option>
														<?php elseif(isset($_GET['parent']) && $_GET['parent'] === $parent['id']): ?>
														<option value="<?php echo hsc($parent['id']); ?>" selected="selected">
															<?php echo "- " . str_repeat('- ', $parent['root_distance']) . hsc($parent['name']); ?>
														</option>
														<?php else: ?>
														<option value="<?php echo hsc($parent['id']); ?>">
															<?php echo "- " . str_repeat('- ', $parent['root_distance']) . hsc($parent['name']); ?>
														</option>
														<?php endif; ?>
													<?php endforeach; ?>
													</select>
												</td>
											</tr>
											<tr>
												<td><label class="profile-label">Published</label></td>
												<td>
													<select name="published" class="profile-slctfont">
														<option <?php if((isset($_SESSION['menu']->published) && $_SESSION['menu']->published == 1) || (isset($menu['published']) && $menu['published'] == 1)): ?>
																selected="selected"
																<?php endif; ?>
																value="1">
															Published
														</option>
														<option <?php if((isset($_SESSION['menu']->published) && $_SESSION['menu']->published == 0) || (isset($menu['published']) && $menu['published'] == 0)): ?>
																selected="selected"
																<?php endif;?>
																value="0">
															Unpublished
														</option>
													</select>
												</td>
											</tr>
											<tr class="option-109" <?php if(!in_array(109, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td class="vAlignTop">
													<label class="profile-label">Image</label>
												</td>
												<td>
													<div>
														<input type="hidden" id="menu-img" name="image"
															   <?php if(!empty($menu['image'])): ?>
															   value="<?php echo hsc($menu['image']); ?>"
															   <?php elseif(!empty($_SESSION['menu']->image)): ?>
															   value="<?php echo $_SESSION['menu']->image; ?>"
															   <?php endif; ?>
														/>
														<input type="hidden" id="menu-img-path" name="imagePath"
															   <?php if(!empty($menu['image_path'])): ?>
															   value="<?php echo hsc($menu['image_path']); ?>"
															   <?php elseif(!empty($_SESSION['menu']->imagePath)): ?>
															   value="<?php echo $_SESSION['menu']->imagePath; ?>"
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
													</div>
												</td>
											</tr>
											<tr class="option-109" <?php if(!in_array(109, $userOptions)): ?> style="display: none;" <?php endif; ?>>
												<td>
													<label class="profile-label">Image Alt</label>
												</td>
												<td>
													<input type="text" id="img-alt" class="profile-text" title="Alt tag attribute for the menu's image" name="imageAlt" name="imageAlt" 
														   title="The alt attribate the for the uploaded image" 
														   <?php if(!empty($menu['image_alt'])): ?>
														   value="<?php echo hsc($menu['image_alt']); ?>"
														   <?php elseif(!empty($_SESSION['menu']->imageAlt)): ?>
														   value="<?php echo hsc($_SESSION['menu']->imageAlt); ?>"
														   <?php endif; ?>
													/>
												</td>
											</tr>
											<tr>
												<td class="panel-spacing">
													<label for="createdBy">Created By</label>
												</td>
												<td>
													<select id="created-by" name="createdBy">
													<?php foreach($backendUsers as $backendUser): ?>
														<?php if(!isset($_SESSION['menu']->createdBy) && isset($menu['created_by']) && $backendUser['id'] == $menu['created_by']): ?>
														<option value="<?php echo hsc($menu['created_by']); ?>" selected="selected">
															<?php echo hsc($backendUser['username']); ?>
														</option>
														<?php elseif(isset($_SESSION['menu']->createdBy) && $backendUser['id'] === $_SESSION['menu']->createdBy): ?>
														<option value="<?php echo hsc($backendUser['id']); ?>" selected="selected">
															<?php echo hsc($backendUser['username']);?>
														</option>
														<?php elseif(!isset($_SESSION['menu']) && !isset($menu) && $_SESSION['userId'] == $backendUser['id']): ?>
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
											<tr>
												<td>
													<label class="panel-spacing fltlft" for="date-posted">Date Created</label>
												</td>
												<td>
													<input id="date-created" class="panel-input profile-text" name="dateCreated" type="text"
														<?php if(isset($menu['date_created'])): ?>
														   value="<?php echo hsc($menu['date_created']); ?>"
														<?php elseif(isset($_SESSION['menu']->dateCreated)): ?>
															value="<?php echo hsc($_SESSION['menu']->dateCreated); ?>"
														<?php endif; ?>
													/>
												</td>
											</tr>
											<tr>
												<td>
													<label class="panel-spacing panel-text-only fltlft">Modified By</label>
												</td>
												<td>
													<?php if(isset($menu['modified_by'])) echo hsc($menu['modified_by']); ?>
												</td>
											</tr>
											<tr>
												<td>
													<label class="panel-spacing panel-text-only fltlft" for="date-posted">Modified Date</label>
												</td>
												<td>
													<?php if(isset($menu['date_modified'])): ?>
														<?php echo hsc($menu['date_modified']); ?>
													<?php elseif(isset($_SESSION['menu']->dateModified)): ?>
														<?php echo hsc($_SESSION['menu']->dateModified); ?>
													<?php endif; ?>
												</td>
											</tr>
										</table>
									<div class="clear"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
			<div class="clear"></div>
			<div id="options-form" title="Menu Options">
				<div class="panel">
					<div class="panel-column">
						<div class="element">
							<div class="element-top">Options to display</div>
							<div class="element-body options-checklist">
								<form action="<?php echo HTTP_SERVER; ?>menus" method="post">
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
<?php menuCleanUp(); ?>
