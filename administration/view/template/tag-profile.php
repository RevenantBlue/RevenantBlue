<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/tags/tags-c.php';
$title = isset($tag) ? 'Tags | ' . hsc($tag['tag_name']) : 'Tags | New Tag';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadJqueryUi();
loadTinyMce();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/tags.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/tags.min.js"></script>
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
				<?php if(isset($_GET['tag'])): ?>
					<span>Edit Tag</span>
				<?php else: ?>
					<span>Create Tag</span>
				<?php endif; ?>
				<div class="menu-darr"></div>
			</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>tags/new">
						<span class="ui-icon ui-icon-plus"></span>
						New Article
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
					<a href="#" id="action-close-tag">
						<span class="ui-icon ui-icon-close"></span>
						Close
					</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($tag)): ?>
		<?php displayBreadCrumbs($tag['username'], HTTP_ADMIN . 'tags/' . $tag['id'] . '/edit', array('title' => 'Tag Profile', 'url' => HTTP_ADMIN . 'tags/new')); ?>
		<?php else: ?>
		<?php displayBreadCrumbs(false, false, array('title' => 'New Tag', 'url' => HTTP_ADMIN . 'tags/new')); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix option-78" <?php if(!in_array(78, $userOptions)):?>style="display: none"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save">
						<a href="#">
							<span class="ui-icon ui-icon-disk"> </span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close">
						<a href="<?php echo HTTP_ADMIN . 'tags/'; ?>">
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
							<span class="ui-icon ui-icon-help"></span>
							<span class="toolbar-text">Help</span>
						</a>
					</li>
				</ul>
				<div class="clear"></div>
			</div>
		</div>
		<div class="clear"></div>
		<div id="element-box">
			<form id="adminForm" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">
				<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
				<input type="hidden" id="tagAction" name="tagAction" />
				<div class="profile-main">
					<div id="profile-padding">
						<div class="element">
							<div class="element-top">Tag name</div>
							<div class="element-body">
								<?php if(isset($tag['id'])): ?>
								<input type="hidden" name="id" value="<?php echo hsc($tag['id']);?>" />
								<?php endif; ?>
								<input id="title" type="text" name="name" placeholder="Enter tag name here"
								<?php if(isset($tagValidate->title)): ?>
									value="<?php echo hsc($tagValidate->title); ?>"
								<?php elseif(isset($tag['tag_name'])): ?>
									value="<?php echo hsc($tag['tag_name']); ?>"
								<?php endif; ?>
								/>
							</div>
						</div>
						<div class="element content-editor">
							<div class="element-top">Description</div>
							<div class="element-body">
								<label for="description-editor" class="backend_label"></label>
								<div>
									<textarea id="description-editor" name="description" cols="100" rows="20"><?php if(isset($tagValidate->description)): ?><?php echo hsc($tagValidate->description); elseif(isset($tag['tag_description'])): echo hsc($tag['tag_description']); endif; ?></textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="profile-details">
					<div id="detail-padding">
						<div class="panel">
							<div class="panel-column">
								<div class="element">
									<div class="element-top">Publishing Options</div>
									<div class="element-body">
										<table class="panel-content">
											<tr class="option-25">
												<td>
													<label for="alias" class="backend_label">Alias</label>
												</td>
												<td>
													<input id="alias" type="text" name="alias" class="profile-text"  title="Leave this blank and the backend will fill in a default value, which is the title in lower case and with dashes instead of spaces. You may enter the Alias manually. Use lowercase letters and hypens (-). No spaces or underscores are allowed. The Alias will be used in the SEF URL. Default value will be a date and time if the title is typed in non-latin letters."
													<?php if(isset($_SESSION['tag']->alias)): ?>
														   value="<?php echo hsc($_SESSION['tag']->alias); ?>"
													<?php elseif(isset($tag['tag_alias'])): ?>
														   value="<?php echo hsc($tag['tag_alias']); ?>"
													<?php endif; ?>
													/>
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
			<div id="options-form" title="Tag Options">
				<div class="panel">
					<div class="panel-column">
						<div class="element">
							<div class="element-top">Options to display</div>
							<div class="element-body options-checklist">
								<form action="<?php echo HTTP_SERVER; ?>articles" method="post">

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
