<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/media/media-c.php';
$title = 'Media | Upload';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
loadPlupload();
loadJCrop();
?>
<script type="text/javascript">
// Allowed extensions for the plupload uploader.
var allowedExtensions = "<?php echo hsc($globalSettings['media_allowed_extensions']['value']); ?>";
</script>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/media.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/media-plupload.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/file-icon-picker.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/media.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/media-plupload.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/file-icon-picker.min.js"></script>
<?php endif; ?>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/file-icons.css" />
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/media.css" />
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/js/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css" />
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" type="text/css" />
<?php if(empty($_GET['attach'])): ?>
<?php loadMainCss(); ?>
<?php endif; ?>
</head>
<?php if(empty($_GET['attach'])): ?>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
<?php else: ?>
<body style="overflow: auto">
<div id="content-box">
	<div class="padding">
<?php endif; ?>
		<?php if(empty($_GET['attach'])): // Disable toolbar for iframe loading. ?>
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<span>Media Upload</span>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
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
		<?php displayBreadCrumbs(); ?>
		<div id="toolbar-box" class="clearfix option-86" <?php if(!in_array(86, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-media-upload-options">
						<a href="#">
							<span class="ui-icon ui-icon-gear"></span>
							<span class="toolbar-text">Options</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-media-upload-help">
						<a href="#">
							<span class="ui-icon ui-icon-help"></span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
				<div class="clearfix"></div>
			</div>
		</div>
		<?php endif; ?>
		<div class="clearfix"></div>
		<div id="element-box">
			<form id="adminForm" action="<?php echo hsc(HTTP_ADMIN . 'media'); ?>" method="post">
				<div id="content-padding">
					<div id="container">
						<?php if($browser['name'] != 'msie'): ?>
						<div id="drag-drop-area" style="height: 200px;">
							<div id="drag-drop-middle">
								<p>Drop files here</p>
								<p>or</p>
								<div id="plupload-container" style="margin: 0 auto;">
									<button id="plupload-browser-button" class="rb-btn" style="display: block; margin: 0 auto;">Upload Files</button>
								</div>
							</div>
							<div id="filelist">

							</div>
						</div>
						<?php else: ?>
						<div id="plupload-container" style="margin: 0 auto;">
							<button id="plupload-browser-button" class="rb-btn" style="display: block; margin: 0 auto;">Upload Files</button>
						</div>
						<?php endif; ?>
						<br />
						<div id="media-uploads">

						</div>
						<br />
					</div>
					<div id="my-div"></div>
					<div>
						<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					</div>
					<div class="clear"></div>
				</div>
			</form>
		</div>
		<br />
	</div>
</div>
<?php displayFooter(); ?>
