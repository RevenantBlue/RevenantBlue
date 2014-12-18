<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/media/media-c.php';
if(isset($mediaFile)) $title = 'Media | ' . $mediaFile['media_title'];
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
loadJCrop();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/media.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/file-icon-picker.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/media.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/file-icon-picker.min.js"></script>
<?php endif; ?>
<script type="text/javascript">
var ext, extIcon, csrfToken = "<?php echo hsc($csrfToken); ?>";
$(document).ready(function() {
<?php if(!in_array($mediaFile['media_mime_type'], $imageMimes)): ?>
	ext = '<?php echo hsc($mediaFile['media_ext']); ?>';
	extIcon = getExtensionIcon(ext, 64);
	$("#generic-file-icon").addClass(extIcon);
<?php else: ?>
	// Jcrop
	mediaWidth = <?php echo hsc($mediaWidth); ?>;
	mediaHeight = <?php echo hsc($mediaHeight); ?>;
<?php endif; ?>
});
</script>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/file-icons.css" />
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/media.css" />
<?php loadMainCss(); ?>
</head>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<span>Media File</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>media/new">
						<span class="ui-icon ui-icon-arrowthick-1-n"></span>
						Upload Media
					</a>
				</li>
				<li>
					<a href="#" id="action-save-media">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-close-media">Save and Close</a>
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
					<a href="#" id="action-close-media">
						<span class="ui-icon ui-icon-close"></span>
						Close
					</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-80" <?php if(!in_array(80, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save">
						<a href="#">
							<span class="ui-icon ui-icon-disk"></span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close">
						<a href="<?php echo HTTP_ADMIN; ?>media/">
							<span class="ui-icon ui-icon-close"></span>
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
			<div id="content-padding">
				<form id="adminForm" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">
					<div class="element">
						<div class="element-top"><?php echo hsc($mediaFile['media_name']); ?></div>
						<div id="media-file" class="element-body">
							<input type="hidden" id="mediaAction" name="mediaAction" value="" />
							<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
							<input type="hidden" id="imageWidth" name="imageWidth" value="<?php echo hsc($mediaWidth); ?>" />
							<input type="hidden" id="imageHeight" name="imageHeight" value="<?php echo hsc($mediaHeight); ?>" />
							<table id="media-profile-<?php echo hsc($mediaFile['id']); ?>" class="media-details">
								<tr id="media-properties-<?php echo hsc($mediaFile['id']); ?>">
									<td id="preview-thumb-container-<?php echo hsc($mediaFile['id']); ?>" class="media-attribs width10pcnt">
										<?php if(in_array($mediaFile['media_mime_type'], $imageMimes)): ?>
										<img id="preview-thumb-<?php echo hsc($mediaFile['id']); ?>" src="<?php echo $mediaFile['media_thumb_url']?>" class="media-image-icon" alt="" />
										<?php else: ?>
											<p id="generic-file-icon" class="media-details-icon"></p>
										<?php endif; ?>
									</td>
									<td class="media-attribs width90pcnt">
										<p><strong>File name:</strong><?php echo hsc($mediaFile['media_name']); ?></p>
										<p><strong>Mime type:</strong><?php echo hsc($mediaFile['media_mime_type']); ?></p>
										<p><strong>Upload date:</strong><?php echo hsc(nicetime($mediaFile['date_posted'])); ?></p>
										<?php if(!empty($mediaFile['date_modified'])): ?>
										<p><strong>Last modified:</strong><?php echo hsc(nicetime($mediaFile['date_modified'])); ?></p>
										<?php endif; ?>
										<?php if(!empty($mediaFile['media_thumb_url'])): ?>
										<p><strong>Dimensions:</strong><?php echo hsc($mediaWidth) . ' x ' . hsc($mediaHeight); ?></p>
										<?php endif; ?>
									</td>
								</tr>
								<?php if(!empty($mediaFile['media_thumb_url'])): ?>
								<tr id="image-editor-<?php echo hsc($mediaFile['id']); ?>" class="image-editor" style="display: none;">
									<td colspan="2">
										<div class="image-editor-wrap">
											<table class="image-editor">
												<tr>
													<td id="image-editor-main-<?php echo hsc($mediaFile['id']); ?>" class="width60pcnt">
														<div id="image-tools-<?php echo hsc($mediaFile['id']); ?>" class="image-editor-tools clearfix">
															<div id="crop-tool-<?php echo hsc($mediaFile['id']); ?>" class="image-tools tool-crop-inactive" title="Crop">

															</div>
															<div id="tool-rotate-cc-<?php echo hsc($mediaFile['id']); ?>" class="image-tools tool-rotate tool-rotate-cc cursor-pointer" title="Rotate counter-clockwise">

															</div>
															<div id="tool-rotate-c-<?php echo hsc($mediaFile['id']); ?>" class="image-tools tool-rotate tool-rotate-c cursor-pointer" title="Rotate clockwise">

															</div>
															<div id="tool-flip-vert-<?php echo hsc($mediaFile['id']); ?>" class="image-tools tool-flip tool-flip-vert cursor-pointer" title="Flip vertically">

															</div>
															<div id="tool-flip-horz-<?php echo hsc($mediaFile['id']); ?>" class="image-tools tool-flip tool-flip-horz cursor-pointer" title="Flip horizontally">

															</div>
															<div id="tool-undo-<?php echo hsc($mediaFile['id']); ?>" class="image-tools tool-undo-inactive" title="Undo">

															</div>
															<div id="tool-redo-<?php echo hsc($mediaFile['id']); ?>" class="image-tools tool-redo-inactive" title="Redo">

															</div>
														</div>
														<div id="edit-image-preview-container-<?php echo hsc($mediaFile['id']); ?>">
															<img id="edit-image-preview-<?php echo hsc($mediaFile['id']); ?>" class="media-image-icon edit-image-preview" src="<?php echo HTTP_ADMIN . 'media/' . $mediaFile['id'] . '?id=' . $mediaFile['id'] . '&amp;adminRequest=image-preview&amp;csrfToken=' . $csrfToken; ?>" alt="<?php if(isset($mediaFile['mediaAlt'])) echo hsc($mediaFile['mediaAlt']); ?>" />
														</div>
														<div class="width60pcnt">
															<button id="save-image-<?php echo hsc($mediaFile['id']); ?>" class="save-image button-spacing rb-btn blue-btn fltlft">Save Image</button>
															<button id="close-image-<?php echo hsc($mediaFile['id']); ?>" class="close-image button-spacing rb-btn light-gray-btn fltlft">Close</button>
															<img id="media-save-ajax-<?php echo hsc($mediaFile['id']); ?>" src="<?php echo hsc(HTTP_IMAGE); ?>admin/gifs/ajax-loader-snake.gif" alt="loading" class="media-ajax block fltlft" style="display: none;" />
														</div>
													</td>
													<td class="width30pcnt">
														<div class="detail-padding">
															<div class="panel thumbnail-preview">
																<div class="panel-column">
																	<div class="element">
																		<div class="element-top">Thumbnail preview</div>
																		<div class="element-body">
																			<img id="edit-image-thumb-<?php echo hsc($mediaFile['id']); ?>" class="media-image-thumb edit-image-thumb" src="<?php echo HTTP_ADMIN . 'media/' . $mediaFile['id'] . '?id=' . $mediaFile['id'] . '&amp;adminRequest=thumb-preview&amp;csrfToken=' . $csrfToken; ?>" alt="<?php if(isset($mediaFile['mediaAlt'])) echo hsc($mediaFile['mediaAlt']); ?>" />
																		</div>
																	</div>
																</div>
															</div>
															<div id="restore-box-<?php echo hsc($mediaFile['id']); ?>" class="panel restore-image" <?php if(empty($mediaFile['media_edit_flag'])): ?>style="display: none;"<?php endif; ?>>
																<div class="panel-column">
																	<div class="element">
																		<div class="element-top">Restore Image</div>
																		<div class="element-body">
																			<p>
																				<em>Discard any changes and restore the original image.</em>
																			</p>
																			<p>
																				<label for="restore-all-<?php echo hsc($mediaFile['id']); ?>" class="center-label">
																					<input type="checkbox" id="restore-all-<?php echo hsc($mediaFile['id']); ?>" class="center-toggle" name="restoreAllImages" />
																					Restore all images and thumbnails
																				</label>
																			</p>
																			<p>
																				<button id="restore-image-<?php echo hsc($mediaFile['id']); ?>" class="restore-orig-image rb-btn light-gray-btn">Restore Image</button>
																			</p>
																		</div>
																	</div>
																</div>
															</div>
															<div class="panel scale-image">
																<div class="panel-column">
																	<div class="element">
																	<div class="element-top">Scale image</div>
																		<div class="element-body">
																			<p>
																				<em>For best results scale the image before performing additional editing actions.</em>
																			</p>
																			<p>
																				<em>Original dimensions: <?php echo hsc($mediaWidth) . ' &times; ' . hsc($mediaHeight); ?></em>
																			</p>
																			<p>
																				<input type="text" id="scale-width-<?php echo hsc($mediaFile['id']); ?>" class="scale-width small-text" name="scaleWidth" value="<?php echo hsc($mediaWidth); ?>" />
																				<span> &times; </span>
																				<input type="text" id="scale-height-<?php echo hsc($mediaFile['id']); ?>" class="scale-height small-text" name="scaleHeight" value="<?php echo hsc($mediaHeight); ?>" />
																			</p>
																			<p>
																				<button id="scale-image-<?php echo hsc($mediaFile['id']); ?>" class="tool-scale rb-btn light-gray-btn" name="scaleButton">Scale Image</button>
																			</p>
																		</div>
																	</div>
																</div>
															</div>
															<div class="panel crop-image">
																<div class="panel-column">
																	<div class="element">
																		<div class="element-top">Crop image</div>
																		<div class="element-body">
																			<p>
																				<label>Aspect ratio</label>
																				<input type="text" id="crop-ratio-width-<?php echo hsc($mediaFile['id']); ?>" class="crop-ratio-width small-text" name="cropRatioWidth" value="" />
																				<span> : </span>
																				<input type="text" id="crop-ratio-height-<?php echo hsc($mediaFile['id']); ?>" class="crop-ratio-height small-text" name="cropRatioHeight" value="" />
																			</p>
																			<p>
																				<label>Selection</label>
																				<input type="text" id="crop-selection-width-<?php echo hsc($mediaFile['id']); ?>" class="crop-selection-width small-text" name="cropSelection" value="" />
																				<span> &times; </span>
																				<input type="text" id="crop-selection-height-<?php echo hsc($mediaFile['id']); ?>" class="crop-selection-height small-text" name="cropSelectionheight" value="" />
																			</p>
																		</div>
																	</div>
																</div>
															</div>
															<div class="panel thumbnail-options">
																<div class="panel-column">
																	<div class="element">
																		<div class="element-top">Apply changes to (on save)</div>
																		<div class="element-body">
																			<label for="apply-all-<?php echo hsc($mediaFile['id']); ?>" class="center-label">
																				<input type="radio" id="apply-all-<?php echo hsc($mediaFile['id']); ?>" class="center-toggle" checked="checked" name="thumbOpts-<?php echo hsc($mediaFile['id']); ?>" value="all">
																				<span>All image sizes</span>
																			</label>
																			<label for="apply-all-but-thumb-<?php echo hsc($mediaFile['id']); ?>" class="center-label">
																				<input type="radio" id="apply-all-but-thumb-<?php echo hsc($mediaFile['id']); ?>" class="center-toggle" name="thumbOpts-<?php echo hsc($mediaFile['id']); ?>" value="allNoThumb" />
																				<span>All images except thumbnail</span>
																			</label>
																			<label for="apply-thumb-<?php echo hsc($mediaFile['id']); ?>" class="center-label">
																				<input type="radio" id="apply-thumb-<?php echo hsc($mediaFile['id']); ?>" class="center-toggle" name="thumbOpts-<?php echo hsc($mediaFile['id']); ?>" value="thumbOnly" />
																				<span>Thumbnail only</span>
																			</label>
																			<label for="apply-image-<?php echo hsc($mediaFile['id']); ?>" class="center-label">
																				<input type="radio" id="apply-image-<?php echo hsc($mediaFile['id']); ?>" class="center-toggle" name="thumbOpts-<?php echo hsc($mediaFile['id']); ?>" value="imageOnly" />
																				<span>Image only</span>
																			</label>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</td>
												</tr>
											</table>
										</div>
									</td>
								</tr>
								<tr id="edit-image-<?php echo hsc($mediaFile['id']); ?>">
									<td>
										<div>
											<button id="edit-btn-<?php echo hsc($mediaFile['id']); ?>" class="edit-image button-spacing rb-btn blue-btn fltlft">Edit Image</button>
											<img id="media-ajax-<?php echo hsc($mediaFile['id']); ?>" src="<?php echo hsc(HTTP_IMAGE); ?>admin/gifs/ajax-loader-snake.gif" alt="loading" class="media-ajax block fltrght" style="display: none;" />
										</div>
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
							</table>
							<table id="media-meta-<?php echo hsc($mediaFile['id']); ?>" class="media-details">
								<tr>
									<th>
										<label for="mediaTitle">Title</label>
									</th>
									<td>
										<input type="hidden" id="mediaId" name="mediaId" value="<?php echo hsc($mediaFile['id']); ?>" />
										<input type="text" id="mediaTitle" class="media-field" name="mediaTitle" value="<?php if(isset($_SESSION['mediaFile'])): echo hsc($_SESSION['mediaFile']['mediaTitle']); else: echo hsc($mediaFile['media_title']); endif;?>" />
									</td>
								</tr>
								<?php if(!empty($mediaFile['media_thumb_url'])): ?>
								<tr>
									<th>
										<label for="mediaAlt">Alternate text</label>
									</th>
									<td>
										<input type="text" id="mediaAlt" class="media-field" name="mediaAlt" value="<?php if(isset($_SESSION['mediaFile'])): echo hsc($_SESSION['mediaFile']['mediaAlt']); else:  echo hsc($mediaFile['media_alt']); endif;?>" />
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<th>
										<label for="mediaCaption">Caption</label>
									</th>
									<td class="media-inputs">
										<input type="text" id="mediaCaption" class="media-field" name="mediaCaption" value="<?php if(isset($_SESSION['mediaFile'])): echo hsc($_SESSION['mediaFile']['mediaCaption']); else:  echo hsc($mediaFile['media_caption']); endif;?>" />
									</td>
								</tr>
								<tr>
									<th>
										<label for="mediaDescription">Description</label>
									</th>
									<td class="media-inputs">
										<textarea id="mediaDescription" class="media-field" name="mediaDescription"><?php if(isset($_SESSION['mediaFile'])): echo hsc($_SESSION['mediaFile']['mediaDescription']); else: echo hsc($mediaFile['media_description']); endif;?></textarea>
									</td>
								</tr>
								<tr>
									<th>
										<label for="mediaLocation">Location</label>
									</th>
									<td class="media-inputs">
										<input type="text" id="mediaLocation" class="media-field readonly" name="medidaLocation" readonly value="<?php echo hsc($mediaFile['media_url']); ?>" />
									</td>
								</tr>
							</table>
						</div>
					</div>
				</form>
			</div>
			<div class="clear"></div>
			<div id="options-form" title="Media Options">
				<div class="panel">
					<div class="panel-column">
						<div class="element">
							<div class="element-top">Options to display</div>
							<div class="element-body options-checklist">

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php mediaCleanUp(); ?>
<?php displayFooter();?>
