<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/photogallery/photogallery-c.php';
$title = isset($album) && isset($currentPhoto) ? 'Photogallery | ' . hsc($album['title']) . ' | ' . $currentPhoto['title'] : 'Photogallery - Images';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
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
				<span><?php echo hsc($album['title']); ?> Album Image</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="#" id="action-save-image">
						<span class="ui-icon ui-icon-disk"></span>
						<span class="toolbar-text">Save</span>
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
				<li id="toolbar-close">
					<a href="<?php echo HTTP_ADMIN; ?>photogallery/<?php echo hsc($album['alias']); ?>">
						<span class="ui-icon ui-icon-close"></span>
						<span class="toolbar-text">Close</span>
					</a>
				</li>
				<li><a href="#">Help</a></li>
				<li>
					<a href="#">About</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($currentPhoto)): ?>
		<?php displayBreadCrumbs($currentPhoto['image'], HTTP_ADMIN . 'photogallery/' . $album['alias'] . '/' . $currentPhoto['alias'], array('title' => $album['title'], 'url' => HTTP_ADMIN . 'photogallery/' . $album['alias'])); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix option-74 clearfix" <?php if(!in_array(74, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save-image">
						<a href="#">
							<span class="ui-icon ui-icon-disk"></span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-cancel">
						<a href="<?php echo HTTP_ADMIN; ?>photogallery/<?php echo $album['alias']; ?>">
							<span class="ui-icon ui-icon-close"> </span>
							<span class="toolbar-text">Close</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help-image">
						<a href="#">
							<span class="ui-icon ui-icon-help"> </span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
				<div class="clear"> </div>
			</div>
		</div>
		<div id="element-box">
			<div id="content-padding">
			<form id="adminForm" action="" method="post">
				<div class="element">
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="gallery-action" name="galleryAction" />
					</div>
					<div class="clear"></div>
					<?php if(isset($currentPhoto)): ?>
					<div class="element-top">
						<h3 class="element-head">
							<?php echo hsc($album['title']); ?>
							- Photo <?php echo hsc($currentPhoto['order_of_item']); ?> of <?php if(isset($numOfPhotos)) echo hsc($numOfPhotos); ?>
						</h3>
					</div>
					<div class="element-body">
						<div class="photo_detail_main">
							<a <?php echo $pager->prevLink; ?>>
								<span id="navleft" class="icon-40-navleft-inactive icon-40-spacing"> </span>
							</a>
							<a <?php echo $pager->nextLink; ?>>
								<span id="navright" class="icon-40-navright-inactive icon-40-spacing"> </span>
							</a>
						   <img id="photo_image" src="<?php echo HTTP_ADMIN . 'photogallery' . '?id=' . hsc($currentPhoto['id']) . '&amp;adminRequest=image-preview&amp;csrfToken=' . $csrfToken; ?>"
								alt="<?php echo hsc($currentPhoto['image_alt']);?>"
							/>
						</div>
					</div>
				</div>
				<div class="element photoAttributes">
					<div class="element-top">
						<div class="element-head">Attributes</div>
					</div>
						<div id="media-file" class="element-body">
						<table class="media-details">
							<tr>
								<th>
									<label for="image-title">Title</label>
								</th>
								<td>
									<input type="hidden" id="id" name="id" value="<?php echo hsc($currentPhoto['id']); ?>" />
									<input type="text" id="image-title" class="media-field" name="title" value="<?php if(isset($_SESSION['photo']['title'])): echo hsc($_SESSION['photo']['title']); else: echo hsc($currentPhoto['title']); endif;?>" />
								</td>
							</tr>
							<tr>
								<th>
									<label class="profile-label">Featured</label>
								</th>
								<td>
									<select name="featured" class="profile-slctfont">
										<option <?php if((isset($_SESSION['photo']['featured']) && $_SESSION['photo']['featured'] == 1) || $currentPhoto['featured'] == 1): ?>
												selected="selected"
												<?php endif; ?>
												value="1">Yes
										</option>
										<option <?php if(isset($_SESSION['photo']['featured']) && $_SESSION['photo']['featured'] == 0 || $currentPhoto['featured'] == 0): ?>
												selected="selected"
												<?php elseif(empty($currentPhoto)): ?>
												selected="selected"
												<?php endif;?>
												value="0">No
										</option>
									</select>
								</td>
							</tr>
							<tr>
								<th>
									<label class="profile-label">State</label>
								</th>
								<td>
									<select name="state" class="profile-slctfont">
										<option <?php if(isset($_SESSION['photo']['published']) && $_SESSION['photo']['published'] == 1 || $currentPhoto['state'] == 1): ?>
												selected="selected"
												<?php endif; ?>
												value="1">Published
										</option>
										<option <?php if((isset($_SESSION['photo']['published']) && $_SESSION['photo']['published'] == 0) || $currentPhoto['state'] == 0): ?>
												selected="selected"
												<?php elseif(empty($currentPhoto)): ?>
												selected="selected"
												<?php endif;?>
												value="0">Unpublished
										</option>
									</select>
								</td>
							</tr>
							<tr>
								<th>
									<label for="image-alt">Alternate text</label>
								</th>
								<td>
									<input type="text" id="image-alt" class="media-field" name="alt" value="<?php if(isset($_SESSION['photo']['alt'])): echo hsc($_SESSION['photo']['alt']); else:  echo hsc($currentPhoto['image_alt']); endif;?>" />
								</td>
							</tr>
							<tr>
								<th>
									<label for="image-caption">Caption</label>
								</th>
								<td class="media-inputs">
									<input type="text" id="image-caption" class="media-field" name="caption" value="<?php if(isset($_SESSION['photo']['caption'])): echo hsc($_SESSION['photo']['caption']); else:  echo hsc($currentPhoto['image_caption']); endif;?>" />
								</td>
							</tr>
							<tr>
								<th class="vAlignTop">
									<label for="image-description">Description</label>
								</th>
								<td class="media-inputs">
									<textarea id="image-description" class="media-field" name="description"><?php if(isset($_SESSION['photo']['description'])): echo hsc($_SESSION['photo']['description']); else: echo hsc($currentPhoto['description']); endif;?></textarea>
								</td>
							</tr>
							<tr>
								<th>
									<label for="image-location">Location</label>
								</th>
								<td class="media-inputs">
									<pre><?php echo hsc($currentPhoto['image_url']); ?></pre>
								</td>
							</tr>
						</table>
					<?php endif; ?>
					</div>
				</div>
			</form>
			<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
<?php galleryCleanUp(); ?>
<?php displayFooter(); ?>
