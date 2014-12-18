<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/photogallery/photogallery-c.php';
require_once 'ui.php';
$title = 'Photogallery | ' . hsc($album['title']);
require_once 'head.php';
loadMainJs();
loadJqueryUi();
loadPlupload();
loadFancyBox();
?>
<script type="text/javascript">
var csrfToken = "<?php echo hsc($csrfToken); ?>", albumAlias = "<?php echo hsc($album['alias']); ?>";
</script>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/file-icon-picker.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/photogallery.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/media.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/file-icon-picker.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/photogallery.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/media.min.js"></script>
<?php endif; ?>
<?php loadMainCss(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/media.css" />
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" />
</head>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<span><?php echo hsc($album['title']); ?> Album</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>articles/new">
						<span class="ui-icon ui-icon-arrowthick-1-n"></span>
						Upload
					</a>
				</li>
				<li>
					<a href="#" id="action-edit-article">Edit</a>
				</li>
				<li>
					<a href="#">Status</a>
					<ul>
						<li>
							<a href="#" id="action-publish-image">
								<span class="ui-icon ui-icon-check"></span>
								Publish
							</a>
						</li>
						<li>
							<a href="#" id="action-unpublish-image">
								<span class="ui-icon ui-icon-radio-off"></span>
								Unpublish
							</a>
						</li>
						<li>
							<a href="#" id="action-feature-image">
								<span class="ui-icon ui-icon-star"></span>
								Feature
							</a>
						</li>
						<li>
							<a href="#" id="action-nofeature-image">
								<span class="ui-icon ui-icon-cancel"></span>
								Disregard
							</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#" id="action-delete-image">
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
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($album)): ?>
		<?php displayBreadCrumbs(false, false, array('title' => $album['title'], 'url' => HTTP_ADMIN . 'photogallery/' . $album['alias'])); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix option-73">
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-upload">
						<a class="toolbar-upload" href="#">
							<span id="upload-photos" class="ui-icon ui-icon-arrowthick-1-n"></span>
							<span class="toolbar-text">Upload</span>
						</a>
					</li>
					<li id="toolbar-edit">
						<a href="#">
							<span class="ui-icon ui-icon-pencil"></span>
							<span class="toolbar-text">Edit</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-publish-image">
						<a href="#">
							<span class="ui-icon ui-icon-check"></span>
							<span class="toolbar-text">Publish</span>
						</a>
					</li>
					<li id="toolbar-unpublish-image">
						<a href="#">
						<span class="ui-icon ui-icon-radio-off"></span>
						<span class="toolbar-text">Unpublish</span>
					</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-feature-image">
						<a href="#">
							<span class="ui-icon ui-icon-star"></span>
							<span class="toolbar-text">Feature</span>
						</a>
					</li>
					<li id="toolbar-nofeature-image">
						<a href="#">
							<span class="ui-icon ui-icon-cancel"></span>
							<span class="toolbar-text">Disregard</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-delete-album-image">
						<a href="#">
							<span class="ui-icon ui-icon-trash"></span>
							<span class="toolbar-text">Delete</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close">
						<a href="<?php echo HTTP_ADMIN; ?>photogallery">
							<span class="ui-icon ui-icon-close"></span>
							<span class="toolbar-text">Close</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-options">
						<a href="#"><span class="ui-icon ui-icon-gear"></span>
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
		<div id="element-box">
			<div id="content-padding">
			<form id="adminForm" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post" enctype="multipart/form-data">
			<div>
				<input type="hidden" id="album-id" value="<?php echo hsc($album['id']); ?>" />
				<input type="hidden" id="album-alias" value="<?php echo hsc($album['alias']); ?>" />
				<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
				<input type="hidden" id="gallery-action" name="galleryAction" />
			</div>
			<div class="clear"></div>
			<div class="element album-images">
				<div class="element-top">
					<label>
						<input id="selectAll" class="overview-check-all fltlft" type="checkbox" />
						<h3 class="element-head"><?php echo hsc($album['title']); ?>'s Photos</h3>
					</label>
				</div>
				<div class="element-body form-item">
					<ul id="album-images-sortable">
						<?php foreach($photos as $num=>$photo): ?>
						<li id="photoList_<?php echo hsc($photo['id']); ?>">
							<a href="<?php echo HTTP_ADMIN . 'photogallery/' . $album['alias'] . '/' . $photo['alias']; ?>">
								<img class="handle photo_thumbs" src="<?php echo HTTP_GALLERY . "admin-thumb-" . $photo['image']; ?>"
									 alt="<?php echo hsc($photo['title']); ?>"
								/>
							</a>
							<input type="checkbox" class="fltlft overview-check" id="cb-<?php echo hsc($num); ?>" name="imageCheck[]" value="<?php echo hsc($photo['id']); ?>"
							/>
							<span id="state-<?php echo hsc($photo['id']); ?>" class="fltlft icon-20-spacing">
								<?php if($photo['state'] == 1): ?>
								<span id="state<?php echo hsc($photo['id']); ?>" class="icon-20-check icon-20-spacing fltlft"> </span>
								<?php else: ?>
								<span class="icon-20-disabled icon-20-spacing fltlft"> </span>
								<?php endif; ?>
							</span>
							<span id="featured-<?php echo hsc($photo['id']); ?>" class="fltlft icon-20-spacing">
								<?php if($photo['featured'] == 1): ?>
								<span class="icon-20-star icon-20-spacing fltlft"> </span>
								<?php else: ?>
								<span class="icon-20-gray-disabled icon-20-spacing fltlft"> </span>
								<?php endif; ?>
							</span>
						</li>
						<?php endforeach; ?>
					</ul>
					<div class="clear"></div>
				</div>
			</div>
			</form>
			<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
<div id="upload-photos-form" title="Upload Photos" class="popup">
	<div id="container">
		<form id="photo-upload" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post" enctype="multipart/form-data">
			<div>
			 	<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
				<input type="hidden" id="gallery-action" name="galleryAction" />
			</div>
			<?php if($browser['name'] != 'msie'): ?>
			<div id="drag-drop-area" style="height: 200px;">
				<div id="drag-drop-middle">
					<p>Drop files here</p>
					<p>or</p>
					<div id="plupload-container" style="margin: 0 auto;">
						<button id="plupload-browser-button" class="rb-btn">Upload Files</button>
					</div>
				</div>
				<div id="filelist">

				</div>
			</div>
			<?php else: ?>
			<div id="plupload-container" style="margin: 0 auto;">
				<button id="plupload-browser-button" class="rb-btn">Upload Files</button>
			</div>
			<?php endif; ?>
			<br />
			<div id="media-uploads">

			</div>
			<br />
		</form>
	</div>
</div>
<?php displayFooter(); ?>
<?php galleryCleanUp(); ?>
