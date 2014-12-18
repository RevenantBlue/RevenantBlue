<?php
namespace RevenantBlue\Admin;
use RevenantBlue\TableSorter;

require_once DIR_ADMIN . 'controller/media/media-c.php';
require_once DIR_SYSTEM . 'library/tablesorter.php';
$title = 'Media';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
if(isset($_GET['attach'])) {
	loadJcrop();
}
loadFancyBox();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/media.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/file-icon-picker.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/media.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/file-icon-picker.min.js"></script>
<?php endif; ?>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/js/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css" />
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" />
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/file-icons.css" />
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/media.css" />
<?php
if(!isset($_GET['attach'])) {
	loadMainCss();
}
?>
</head>
<?php if(!isset($_GET['attach'])): ?>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
<?php else: ?>
<body style="overflow: auto;">
<div id="content-box">
	<div id="padding">
<?php endif; ?>
		<?php if(!isset($_GET['attach'])): ?>
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<span>Media</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>upload">
						<span class="ui-icon ui-icon-arrowthick-1-n"></span>
						Upload Media
					</a>
				</li>
				<li>
					<a href="#" id="action-edit-media">Edit</a>
				</li>
				<li>
					<a href="#" id="action-delete-media">
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
				<form id="media-search-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="text" id="media-search" class="med-small-text overview-search" name="mediaToSearch" placeholder="Search by filename" />
					</div>
					<div>
						<button class="sprites search-sprite sprite-space-16" name="submitMediaSearch" type="submit" value="1"></button>
					</div>
				</form>
			</div>
		</div>
		<?php displayNotifications(); ?>
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-84" <?php if(!in_array(84, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-upload">
						<a href="<?php echo HTTP_ADMIN . 'upload'; ?>">
							<span class="ui-icon ui-icon-arrowthick-1-n"> </span>
							<span class="toolbar-text">Upload</span>
						</a>
					</li>
					<li id="toolbar-edit">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"> </span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-delete">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-text">Delete</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-options">
						<a id="options" href="#">
							<span class="ui-icon ui-icon-gear"></span>
							<span class="toolbar-text">Options</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-help">
						<a href="#">
							<span class="ui-icon ui-icon-help"></span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<?php endif; ?>
		<div class="clearfix"></div>
		<div id="element-box" style="background-color: #FFF;">
			<div id="content-padding">
				<form id="adminForm" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
					<div class="element filter-menu fltlft">
						<div class="element-top">
							<h3 class="element-head">Filter By</h3>
						</div>
						<div class="element-body">
							<div class="element-body-content">
								<select id="attachFilter" name="attachFilter" class="user-filter">
									<option selected="selected" disabled="disabled">--Attached--</option>
									<option value="attached">Attached</option>
									<option value="unattached">Unattached</option>
								</select>
								<select id="typeFilter" name="typeFilter" class="user-filter">
									<option selected="selected" disabled="disabled">--File type--</option>
									<option value="image">Image</option>
									<option value="video">Video</option>
								</select>
							</div>
						</div>
					</div>
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
						<input type="hidden" id="mediaAction" name="mediaAction" />
					</div>
					<div class="clearfix"></div>
					<?php if(!isset($_GET['attach'])): ?>
					<div class="links links-top"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
					<div class="clearfix"></div>
					<table id="media-overview" class="overview">
						<thead>
							<tr class="overview-top">
								<th class="width1pcnt">
									<input id="selectAll" type="checkbox" name="mediaCheck[]" class="overview-check-all" />
								</th>
								<th class="width5pcnt">Thumb</th>
								<th class="left">
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'search', 'media_name');?>">
									<?php elseif(isset($_GET['attached'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'attached', 'media_name'); ?>">
									<?php elseif(isset($_GET['type'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'type', 'media_name'); ?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', '', 'media_name');?>">
									<?php endif; ?>
									Filename <?php echo TableSorter::displaySortIcon('media_name', 'asc'); ?>
									</a>
								</th>
								<th class="width10pcnt option-12 left">
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'search', 'media_username'); ?>">
									<?php elseif(isset($_GET['attached'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'attached', 'media_username'); ?>">
									<?php elseif(isset($_GET['type'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'type', 'media_username'); ?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', '', 'media_username'); ?>">
									<?php endif; ?>
									Author <?php echo TableSorter::displaySortIcon('media_username', 'asc'); ?>
									</a>
								</th>
								<th class="width20pcnt option-17 left">Attachments</th>
								<th class="width10pcnt option-13">
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'search', 'date_posted'); ?>">
									<?php elseif(isset($_GET['attached'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'attached', 'date_posted'); ?>">
									<?php elseif(isset($_GET['type'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'type', 'date_posted'); ?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', '', 'date_posted'); ?>">
									<?php endif; ?>
									Date Posted <?php echo TableSorter::displaySortIcon('date_posted', 'desc', TRUE); ?>
									</a>
								</th>
								<th class="width5pcnt option-15">
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'search', 'comments'); ?>">
									<?php elseif(isset($_GET['attached'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'attached', 'comments'); ?>">
									<?php elseif(isset($_GET['type'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'type', 'comments'); ?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', '', 'comments'); ?>">
									<?php endif; ?>
									Comments <?php echo TableSorter::displaySortIcon('comments', 'asc'); ?>
									</a>
								</th>
								<th class="width5pcnt option-16">
									<?php if(isset($_GET['search'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'search', 'id'); ?>">
									<?php elseif(isset($_GET['attached'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'attached', 'id'); ?>">
									<?php elseif(isset($_GET['type'])): ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', 'type', 'id'); ?>">
									<?php else: ?>
									<a class="link" href="<?php echo TableSorter::sortLink('/media', '', 'id'); ?>">
									<?php endif; ?>
									Id <?php echo TableSorter::displaySortIcon('id', 'asc'); ?>
									</a>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php if(!empty($mediaFiles)): ?>
							<?php foreach($mediaFiles as $num => $mediaFile): ?>
							<?php $class = ($num % 2) + 1; ?>
							<tr class="overview-row<?php echo hsc($class); ?>">
								<td>
									<input id="cb-<?php echo hsc($num); ?>" type="checkbox" class="overview-check" name="mediaCheck[]" value="<?php echo hsc($mediaFile['id']);?>" />
								</td>
								<td id="media-icon-<?php echo hsc($mediaFile['id']); ?>">
									<?php if(in_array($mediaFile['media_mime_type'], $imageMimes)): ?>
									<input id="expand-<?php echo hsc($mediaFile['id']); ?>" type="hidden" value="<?php echo $mediaFile['media_url']; ?>" />
									<img id="expand-thumb-<?php echo hsc($mediaFile['id']); ?>" src="<?php echo $mediaFile['media_thumb_url']?>" class="media-icon expand-thumbnail" alt="" />
									<?php else: ?>
									<script type="text/javascript">
										var extIcon = getExtensionIcon('<?php echo hsc($mediaFile['media_ext']); ?>', 64);
										$("#media-icon-<?php echo hsc($mediaFile['id']); ?>").append('<p class="media-icon ' + extIcon + '"></p>');
									</script>
									<?php endif; ?>
								</td>
								<td class="left">
									<a href="<?php echo HTTP_ADMIN . 'media/' . $mediaFile['id']; ?>"><?php echo hsc($mediaFile['media_name']); ?></a>
								</td>
								<td id="author<?php echo hsc($mediaFile['id']); ?>" class="option-18 left">
									<?php echo hsc($users->getUsernameById($mediaFile['media_author'])); ?>
									<input type="hidden" name="id" value="<?php echo hsc($mediaFile['id']); ?>" />
								</td>
								<td class="option-19 left">
									<?php if(!empty($mediaAttachments[$mediaFile['id']])): ?>
									<?php foreach($mediaAttachments[$mediaFile['id']] as $mediaAttachment): ?>
									<div id="media-attachment-<?php echo hsc($mediaAttachment['id']); ?>" class="clearfix">
										<div class="fltlft">
											<?php if(!empty($mediaAttachment['article_id'])): ?>
											<a href="<?php echo HTTP_ADMIN . 'articles/' . hsc($mediaAttachment['article_id']) . '/edit'; ?>"><?php echo hsc($mediaAttachment['title']); ?></a><span>, <?php echo date('m d, Y', strtotime($mediaAttachment['date_attached'])); ?> </span>
											<?php elseif(!empty($mediaAttachment['page_id'])): ?>
											<a href="<?php echo HTTP_ADMIN . 'pages/' . hsc($mediaAttachment['page_id']) . '/edit'; ?>"><?php echo hsc($mediaAttachment['title']); ?></a>
											<?php endif; ?>
										</div>
										<div class="fltlft">
											<p id="delete-attachment-<?php echo hsc($mediaAttachment['id']); ?>" class="small-close deleteMediaAttach"> </p>
										</div>
									</div>
									<?php endforeach; ?>
									<?php endif; ?>
									<p class="clerafix">
										<a id="media-attach-<?php echo hsc($mediaFile['id']); ?>" class="attach-media" href="#">Attach</a>
									</p>
								</td>
								<td class="option-13">
									<?php echo hsc(nicetime($mediaFile['date_posted'])); ?>
								</td>
								<td class="option-15">
								</td>
								<td class="option-16">
									<?php echo hsc($mediaFile['id']); ?>
								</td>
							</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr class="overview-row1">
								<td colspan="11">
									<?php if(isset($_GET['search'])): ?>
									Your search did not match any records.
									<?php elseif(isset($_GET['type']) || isset($_GET['attached'])): ?>
									No media files available with the current filter.
									<?php else: ?>
									No media files have been uploaded yet.
									<?php endif; ?>
								</td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
					<div class="links links-bottom"><?php if(isset($pager)) echo $pager->menu; echo $pager->limitMenu; ?></div>
					<?php else: ?>
					<div id="media-attach-list" class="vert-space">
						<?php if(!empty($mediaFiles)): ?>
						<?php foreach($mediaFiles as $mediaFile): ?>
						<script type="text/javascript">
							// Set the width and height for the image editor.
							origWidthArr[<?php echo hsc($mediaFile['id']); ?>] = '<?php echo hsc($mediaFile['media_width']); ?>';
							origHeightArr[<?php echo hsc($mediaFile['id']); ?>] = '<?php echo hsc($mediaFile['media_height']); ?>';
							mediaWidthArr[<?php echo hsc($mediaFile['id']); ?>] = '<?php echo hsc($mediaFile['media_width']); ?>';
							mediaHeightArr[<?php echo hsc($mediaFile['id']); ?>] = '<?php echo hsc($mediaFile['media_height']); ?>';
						</script>
						<table id="media-profile-<?php echo hsc($mediaFile['id']); ?>" class="media-profile media-details media-attachment width100pcnt">
							<thead>
								<tr>
									<td id="media-data-<?php echo hsc($mediaFile['id']); ?>" colspan="2">
										<div id="<?php echo hsc($mediaFile['id']); ?>" class="media-upload">
											<?php if(!empty($mediaFile['media_thumb_url'])): ?>
											<div id="icon-<?php echo hsc($mediaFile['id']); ?>" class="media-file-icon media-file-spacing">
												<img class="smallnail" src="<?php echo hsc($mediaFile['media_thumb_url']); ?>" alt="<?php echo hsc($mediaFile['media_alt']); ?>" />
											</div>
											<?php else: ?>
											<div id="icon-<?php echo hsc($mediaFile['id']); ?>" class="<?php echo getExtensionIcon($mediaFile['media_ext']); ?>-32 media-file-icon media-file-spacing"></div>
											<?php endif; ?>
											<div class="media-upload-file"><?php echo hsc($mediaFile['media_title']); ?></div>
											<a id="toggle-<?php echo hsc($mediaFile['id']); ?>" class="details-toggle">Show</a>
										</div>
									</td>
								</tr>
							</thead>
							<tbody id="media-body-<?php echo hsc($mediaFile['id']); ?>" style="display: none;">
								<tr id="media-properties-<?php echo hsc($mediaFile['id']); ?>" class="media-properties">
									<td id="preview-thumb-container-<?php echo hsc($mediaFile['id']); ?>" class="media-attribs preview-thumb-container">
										<?php if(!empty($mediaFile['media_thumb_url'])): ?>
										<img id="preview-thumb-<?php echo hsc($mediaFile['id']); ?>" class="media-image-icon" src="<?php echo HTTP_ADMIN; ?>media/<?php echo hsc($mediaFile['id']); ?>?id=<?php echo hsc($mediaFile['id']); ?>&adminRequest=thumb-preview&csrfToken=<?php echo hsc($csrfToken); ?>&timestamp=<?php echo time(); ?>" alt="<?php echo hsc($mediaFile['media_alt']); ?>" />
										<?php else: ?>
										<p class="media-details-icon sprite-<?php echo hsc($mediaFile['media_ext']); ?>-64"></p>
										<?php endif; ?>
									</td>
									<td id="media-attribs-<?php echo hsc($mediaFile['id']); ?>" class="media-attribs media-attribs-container">
										<p><strong>File name:</strong><?php echo hsc($mediaFile['media_title']); ?></p>
										<p><strong>Mime type:</strong><?php echo hsc($mediaFile['media_mime_type']); ?></p>
										<p><strong>Upload date:</strong><?php echo hsc(nicetime($mediaFile['date_posted'])); ?></p>
									</td>
								</tr>
								<?php if(!empty($mediaFile['media_thumb_url'])): ?>
								<tr id="image-editor-<?php echo hsc($mediaFile['id']); ?>" style="display: none;" class="image-editor-placeholder">
									<td colspan="2">
										<div class="image-editor-wrap">
											<table class="image-editor">
												<tbody>
													<tr>
														<td id="image-editor-main-<?php echo hsc($mediaFile['id']); ?>" class="image-editor-main width60pcnt">
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
																<img id="edit-image-preview-<?php echo hsc($mediaFile['id']); ?>" class="media-image-icon edit-image-preview" src="<?php echo HTTP_ADMIN; ?>media/<?php echo hsc($mediaFile['id']); ?>?id=<?php echo hsc($mediaFile['id']); ?>&adminRequest=image-preview&csrfToken=<?php echo hsc($csrfToken); ?>&timestamp=<?php echo time(); ?>" />
															</div>
															<div class="width60pcnt">
																<input type="button" id="save-image-<?php echo hsc($mediaFile['id']); ?>" class="save-image button-spacing rb-btn blue-btn fltlft" value="Save Image" />
																<input type="button" id="close-image-<?php echo hsc($mediaFile['id']); ?>" class="close-image button-spacing fltlft rb-btn light-gray-btn" value="Close" />
																<img id="media-save-ajax-<?php echo hsc($mediaFile['id']); ?>" src="<?php echo HTTP_IMAGE; ?>admin/gifs/ajax-loader-snake.gif" alt="loading" class="media-ajax block" style="display: none;" />
															</div>
														</td>
														<td class="width40pcnt">
															<div class="detail-padding">
																<div id="restore-box-<?php echo hsc($mediaFile['id']); ?>" class="panel restore-image" style="display: none;">
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
																						'Restore all images and thumbnails
																					</label>
																				</p>
																				<p>
																					<input type="button" id="restore-image-<?php echo hsc($mediaFile['id']); ?>" class="restore-orig-image rb-btn light-gray-btn" value="Restore Image" />
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
																					<em>Original dimensions:  <?php echo hsc($mediaFile['media_width']); ?> &times; <?php echo hsc($mediaFile['media_height']); ?></em>
																				</p>
																				<p>
																					<input type="text" id="scale-width-<?php echo hsc($mediaFile['id']); ?>" class="scale-width small-text" name="scaleWidth" value="<?php echo hsc($mediaFile['media_width']); ?>" />
																					<span> &times; </span>
																					<input type="text" id="scale-height-<?php echo hsc($mediaFile['id']); ?>" class="scale-height small-text" name="scaleHeight" value="<?php echo hsc($mediaFile['media_height']); ?>" />
																				</p>
																				<p>
																					<input type="button" id="scale-image-<?php echo hsc($mediaFile['id']); ?>" class="tool-scale rb-btn light-gray-btn" name="scaleButton" value="Scale Image" />
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
																					<input type="text" id="crop-ratio-width-<?php echo hsc($mediaFile['id']); ?>" class="crop-ratio-width small-text" name="cropRatioWidth" />
																					<span> : </span>
																					<input type="text" id="crop-ratio-height-<?php echo hsc($mediaFile['id']); ?>" class="crop-ratio-height small-text" name="cropRatioHeight" />
																				</p>
																				<p>
																					<label>Selection</label>
																					<input type="text" id="crop-selection-width-<?php echo hsc($mediaFile['id']); ?>" class="crop-selection-width small-text" name="cropSelectionWidth" />
																					<span> &times; </span>
																					<input type="text" id="crop-selection-height-<?php echo hsc($mediaFile['id']); ?>" class="crop-selection-height small-text" name="cropSelectionHeight" />
																				</p>
																			</div>
																		</div>
																	</div>
																</div>
																<div class="detail-padding">
																	<div class="panel thumbnail-preview">
																		<div class="panel-column">
																			<div class="element">
																				<div class="element-top">Thumbnail preview</div>
																				<div class="element-body">
																					<img id="edit-image-thumb-<?php echo hsc($mediaFile['id']); ?>" class="media-image-thumb edit-image-thumb" src="<?php echo HTTP_ADMIN; ?>media/<?php echo hsc($mediaFile['id']); ?>?id=<?php echo hsc($mediaFile['id']); ?>&adminRequest=thumb-preview&csrfToken=<?php echo hsc($csrfToken); ?>&timestamp=<?php echo time(); ?>" />
																				</div>
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
																					<span>All images</span>
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
												</tbody>
											</table>
										</div>
									</td>
								</tr>
								<tr id="edit-image-<?php echo hsc($mediaFile['id']); ?>">
									<td>
										<div>
											<input id="edit-btn-<?php echo hsc($mediaFile['id']); ?>" class="edit-image button-spacing rb-btn blue-btn fltlft" type="button" value="Edit Image" />
											<img id="media-ajax-<?php echo hsc($mediaFile['id']); ?>" src="<?php echo HTTP_IMAGE; ?>admin/gifs/ajax-loader-snake.gif" alt="loading" class="media-ajax block fltrght" style="display: none;" />
										</div>
									</td>
								</tr>
								<?php endif; ?>
								<tr>
									<td colspan="2">&nbsp;</td>
								</tr>
							</tbody>
						</table>
						<table id="media-meta-<?php echo hsc($mediaFile['id']); ?>" class="media-meta media-details" style="display: none;">
							<tr id="media-title-<?php echo hsc($mediaFile['id']); ?>">
								<th>
									<label for="uploads-<?php echo hsc($mediaFile['id']); ?>-title">Title</label>
								</th>
								<td class="media-inputs">
									<input type="hidden" name="uploads[<?php echo hsc($mediaFile['id']); ?>][id]" value="<?php echo hsc($mediaFile['id']); ?>" />
									<input type="text" id="uploads-<?php echo hsc($mediaFile['id']); ?>-title" class="media-field" name="uploads[<?php echo hsc($mediaFile['id']); ?>][title]" value="<?php echo hsc($mediaFile['media_title']); ?>" />
								</td>
							</tr>
							<?php if(!empty($mediaFile['media_thumb_url'])): ?>
							<tr>
								<th>
									<label for="uploads-<?php echo hsc($mediaFile['id']); ?>-alt">Alternate text</label>
								</th>
								<td class="media-inputs">
									<input type="text" id="uploads-<?php echo hsc($mediaFile['id']); ?>-alt" class="media-field" name="uploads[<?php echo hsc($mediaFile['id']); ?>][alt]" value="<?php echo hsc($mediaFile['media_alt']); ?>" />
								</td>
							</tr>
							<?php endif; ?>
							<tr>
								<th>
									<label for="uploads-<?php echo hsc($mediaFile['id']); ?>-caption">Caption</label>
								</th>
								<td class="media-inputs">
									<input type="text" id="uploads-<?php echo hsc($mediaFile['id']); ?>-caption" class="media-field" name="uploads[<?php echo hsc($mediaFile['id']); ?>][caption]" value="<?php echo hsc($mediaFile['media_caption']); ?>" />
								</td>
							</tr>
							<tr>
								<th>
									<label for="uploads-<?php echo hsc($mediaFile['id']); ?>-description">Description</label>
								</th>
								<td class="media-inputs">
									<textarea id="uploads-<?php echo hsc($mediaFile['id']); ?>-description" class="media-field" name="uploads[<?php echo hsc($mediaFile['id']); ?>][description]"><?php echo hsc($mediaFile['media_description']); ?></textarea>
								</td>
							</tr>
							<tr id="media-link-<?php echo hsc($mediaFile['id']); ?>">
								<th>
									<label id="media-link-label-<?php echo hsc($mediaFile['id']); ?>" for="link-url-<?php echo hsc($mediaFile['id']); ?>">Link URL</label>
								</th>
								<td id="media-link-container-<?php echo hsc($mediaFile['id']); ?>" class="media-inputs">
									<input type="hidden" id="url-<?php echo hsc($mediaFile['id']); ?>" value="<?php echo hsc($mediaFile['media_url']); ?>" />
									<input type="text" id="link-url-<?php echo hsc($mediaFile['id']); ?>" class="media-field" name="uploads[<?php echo hsc($mediaFile['id']); ?>][url]" readonly value="<?php echo hsc($mediaFile['media_url']); ?>" />
									<?php if(!empty($mediaFile['media_thumb_url'])): ?>
									<div>
										<button id="url-none-<?php echo hsc($mediaFile['id']); ?>" class="url-none">None</button>
										<button id="url-file-<?php echo hsc($mediaFile['id']); ?>" class="url-file">File URL</button>
									</div>
									<?php endif; ?>
								</td>
							</tr>
							<tr id="media-align-<?php echo hsc($mediaFile['id']); ?>">
								<th>
									<label for="media-align-<?php echo hsc($mediaFile['id']); ?>">Alignment</label>
								</th>
								<td>
									<label class="inline">
										<input type="radio" name="mediaAlign[<?php echo hsc($mediaFile['id']); ?>]" class="center-toggle" checked="checked" />
										None
									</label>
									<label class="center-label inline">
										<input type="radio" name="mediaAlign[<?php echo hsc($mediaFile['id']); ?>]" class="center-toggle" />
										Left
									</label>
									<label class="center-label inline">
										<input type="radio" name="mediaAlign[<?php echo hsc($mediaFile['id']); ?>]" class="center-toggle" />
										Center
									</label>
									<label class="center-label inline">
										<input type="radio" name="mediaAlign[<?php echo hsc($mediaFile['id']); ?>]" class="center-toggle" />
										Right
									</label>
								</td>
							</tr>
							<tr>
								<th>
									<label>Size</label>
								</th>
								<td id="media-sizes-<?php echo hsc($mediaFile['id']); ?>">
									<?php foreach($imageTemplates as $imageTemplate): ?>
									<p>
										<label>
											<input type="radio" name="imageTemplate[<?php echo hsc($mediaFile['id']); ?>]" class="center-toggle" value="<?php echo hsc($imageTemplate['id']); ?>" <?php if($imageTemplate['template_name'] === 'Medium'): ?>checked="checked" <?php endif; ?> />
											<?php echo hsc($imageTemplate['template_name'] . ' ( ' . $imageTemplate['template_width'] . ' × ' . $imageTemplate['template_height'] . ' )'); ?>
										</label>
									</p>
									<?php endforeach; ?>
									<p>
										<label>
											<input type="radio" name="imageTemplate[<?php echo hsc($mediaFile['id']); ?>]" class="center-toggle" value="Full size" />
											Full size ( <?php echo hsc($mediaFile['media_width'] . ' × ' . $mediaFile['media_height']); ?>
										</label>
									</p>
								</td>
							</tr>
							<tr>
								<th>

								</th>
								<td>
									<button id="insert-into-article-<?php echo hsc($mediaFile['id']); ?>" class="insert-media-btn vert-space">Insert into Article</button>
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<a id="delete-<?php echo hsc($mediaFile['id']); ?>" class="delete-media" href="#">Delete</a>
								</td>
							</tr>
						</table>
						<?php endforeach; ?>
						<?php else: ?>
						<div class="no-media-files padding">No media files have been uploaded yet.</div>
						<?php endif; ?>
					</div>
					<div class="vert-space">
						<button type="submit" id="submitMediaUploads" name="submitMediaUploads" class="rb-btn blue-btn blue-btn vert-space">Submit changes</button>
					</div>
					<?php endif; ?>
				</form>
			</div>
		</div>
		<div id="media-attach-form" title="Attach Media">
			<div id="media-attach-wrapper">
				<form action="<?php echo HTTP_ADMIN . 'media'; ?>" method="post">
					<div id="attach-search-wrap">
						<label for="article-page-search" class="inline hide">Enter title to search</label>
						<input id="article-page-search" type="text" class="large-text blended-input vert-space" placeholder="Enter title to search" value="" />
						<span id="attach-select-search">Articles</span>
						<ul id="attach-search-options" style="display: none;">
							<li>
								Select search option:
							</li>
							<li>
								<label for="article-search" class="center-label">
									<input id="article-search" type="radio" class="center-toggle" name="attachSearch" checked="checked" value="Articles" />
									<span>Articles</span>
								</label>
							</li>
							<li>
								<label for="page-search" class="center-label">
									<input id="page-search" type="radio" class="center-toggle" name="attachSearch" value="Pages" />
									<span>Pages</span>
								</label>
							</li>
						</ul>
					</div>
					<div id="article-page-results">
						<table id="search-results">

						</table>
						<table id="search-results-list" class="width100pcnt overview" style="display: none;">
							<thead>
								<tr class="overview-top">
									<th class="width1pcnt">&nbsp;</th>
									<th class="left">Title</th>
									<th class="left">Date</th>
									<th class="left">Status</th>
								</tr>
							</thead>
							<tbody id="search-results-details">

							</tbody>
						</table>
					</div>
				</form>
			</div>
		</div>
		<?php if(!isset($_GET['attach'])): ?>
		<div id="options-form" title="Media Options">
			<div class="panel">
				<div class="panel-column">
					<div class="element">
						<div class="element-top">Options to display</div>
						<div class="element-body options-checklist">
							<form action="<?php echo HTTP_SERVER; ?>media" method="post">
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
		<?php endif; ?>
	</div>
</div>
<?php displayFooter(); ?>
