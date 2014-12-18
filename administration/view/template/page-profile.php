<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/pages/pages-c.php';
$title = 'Pages';
require_once 'head.php';
require_once 'ui.php';
loadMainJs();
loadTinyMce();
loadJqueryUi();
loadElastic();
loadCodeMirror();
loadUnderscore();
?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/timepicker/timepicker.min.js"></script>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/pages.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/pages.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/profile.min.js"></script>
<?php endif; ?>
<script type="text/javascript">
$(document).ready(function() {
	cmHtmlEditor = CodeMirror.fromTextArea(document.getElementById('html-editor'), {
		mode:  'htmlmixed',
		indentWithTabs : true,
		indentUnit : 4,
		tabSize : 4,
		lineNumbers : true
	});
	cmFullscreenHtmlEditor = CodeMirror.fromTextArea(document.getElementById('fullscreen-html-editor'), {
		mode:  'htmlmixed',
		indentWithTabs : true,
		indentUnit : 4,
		tabSize : 4,
		lineNumbers : true
	});
});
<?php if(!empty($disableTinymce) && $disableTinymce): ?>
var disableTinymce = true;
<?php else: ?>
var disableTinymce = false;
<?php endif; ?>
</script>
<?php loadMainCss(); ?>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/overrides.css" type="text/css" />
</head>
<body id="main-iframe-body">
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<div id="action-menu-wrap">
			<a id="action-menu-link">
				<?php if(isset($_GET['id'])): ?>
					<span>Edit Page</span>
				<?php else: ?>
					<span>Create Page</span>
				<?php endif; ?>
				<div class="menu-darr"></div>
			</a>
			<a class="fltrght" id="toggleSidePanel" href="#">+/- Toggle side panel</a>
			<ul id="action-menu">
				<li>
					<a href="<?php echo HTTP_ADMIN; ?>pages/new">
						<span class="ui-icon ui-icon-document"></span>
						New Page
					</a>
				</li>
				<li>
					<a href="#" id="action-publish-page">
						<span class="ui-icon ui-icon-check"></span>
						Publish
					</a>
				</li>
				<li>
					<a href="#" id="action-save-page">
						<span class="ui-icon ui-icon-disk"></span>
						Save
					</a>
				</li>
				<li>
					<a href="#" id="action-save-new-page">Save and New</a>
				</li>
				<li>
					<a href="#" id="action-save-close-page">Save and Close</a>
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
					<a href="#" id="action-close-page">
						<span class="ui-icon ui-icon-close"></span>
						Close
					</a>
				</li>
			</ul>
		</div>
		<?php displayNotifications(); ?>
		<?php if(isset($page)): ?>
		<?php displayBreadCrumbs(false, false, array('title' => $page['page'], 'url' => HTTP_ADMIN . 'pages/' . $page['id'])); ?>
		<?php else: ?>
		<?php displayBreadCrumbs(false, false, array('title' => 'Create Page', 'url' => HTTP_ADMIN . 'pages/new')); ?>
		<?php endif; ?>
		<div id="toolbar-box" class="clearfix option-93" <?php if(!in_array(93, $userOptions)): ?>style="display: none;"<?php endif; ?>>
			<div id="toolbar" class="toolbar-list">
				<ul>
					<li id="toolbar-save-page">
						<a href="#">
							<span class="ui-icon ui-icon-disk"> </span>
							<span class="toolbar-text">Save</span>
						</a>
					</li>
					<li id="toolbar-publish-page" class="quick-publish-page">
						<a href="#">
							<span class="ui-icon ui-icon-check"> </span>
							<span class="toolbar-text">Publish</span>
						</a>
					</li>
					<li class="toolbar-divider"> </li>
					<li id="toolbar-close-page">
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
				<div class="profile-main">
					<div id="profile-padding">
						<div class="element">
							<div class="element-top">
								<span>Page Name</span>
							</div>
							<div class="element-body">
								<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
								<input type="hidden" id="page-action" name="pageAction" />
								<?php if(isset($page)): ?>
								<input id="id" type="hidden" name="id" value="<?php echo hsc($page['id']);?>" />
								<?php endif; ?>
								<input id="title" type="text" name="page" placeholder="Enter the page name here"
									<?php if(isset($_SESSION['page']->page)): ?>
									   value="<?php echo hsc($_SESSION['page']->pageName); ?>"
									<?php elseif(isset($page['page'])): ?>
									   value="<?php echo hsc($page['page']); ?>"
									<?php endif; ?>
								/>
							</div>
						</div>
						<div class="element content-editor">
							<div class="element-top">Page Body</div>
							<div class="element-body">
								<div id="content-editor-selection">
									<div id="add-media-container">
										<a id="add-media" href="#">+ Add Media</a>
									</div>
									<ul>
										<li id="content-editor-tab">
											<a href="#html-editor-wrap">HTML</a>
										</li>
										<li id="html-editor-tab">
											<a href="#visual-editor-wrap">Visual</a>
										</li>
									</ul>
									<div id="html-editor-wrap">
										<div class="tinymce-html-editor-wrapper">
											<div class="tinymce-html-editor-ui">
												<ul>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-bold" title="Bold"><strong>b</strong></button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-italic" title="Italic"><em>i</em></button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-link" title="Link">a</button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-bquote" title="Block quote">b-quote</button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-img" title="Image">img</button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-ul" title="Unordered list">ul</button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-ol" title="Ordered list">ol</button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-li" title="List item">li</button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-code" title="Code"><code>code</code></button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-del" title="Del"><del>del</del></button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-ins" title="Ins"><ins>ins</ins></button>
													</li>
													<li class="html-editor-buttons insert-html">
														<button class="html-editor-fullscreen" title="Toggle Fullscreen">fullscreen</button>
													</li>
												</ul>
											</div>
											<textarea id="html-editor"><?php if(isset($_SESSION['page']->body)): echo $_SESSION['page']->body; elseif(isset($page['body'])): echo $page['body']; endif; ?></textarea>
										</div>
									</div>
									<div id="visual-editor-wrap">
										<textarea id="content-editor" name="content" spellcheck="true" class="<?php if($disableTinymce): ?>mceNoEditor<?php endif; ?>"><?php if(isset($_SESSION['page']->body)): echo $_SESSION['page']->body; elseif(isset($page['body'])): echo $page['body']; endif; ?></textarea>
									</div>
									<div class="clear"> </div>
								</div>
							</div>
							<p id="autosave"></p>
						</div>
						<div class="panel main-panel">
							<div class="panel-column">
								<div class="element content-editor">
									<div class="element-top">Page Head</div>
									<div class="element-body textarea-body">
										<textarea id="page-head-editor" name="head" class="fill-it no-border"><?php if(isset($_SESSION['page']->head)): echo $_SESSION['page']->head; elseif(isset($page['head'])): echo $page['head']; endif; ?></textarea>
									</div>
								</div>
							</div>
						</div>
						<?php if(!empty($pageRevisions)): ?>
						<div class="panel main-panel">
							<div class="panel-column">
								<div class="element">
									<div class="element-top">Revisions</div>
									<div class="element-body">
										<?php if(isset($page)): ?>
										<table class="overview no-border width100pcnt">
											<tr>
												<th class="width60pcnt left">Date Created</th>
												<th class="left">Author</th>
											</tr>
											<?php foreach($pageRevisions as $revision): ?>
											<tr>
												<td class="left">
													<a href="<?php echo HTTP_ADMIN . 'pages/revision/' . $revision['revision_id'] . "/" . $page['id']; ?>">
														<?php echo date('F d, Y - h:i s A', strtotime($revision['revision_date'])); ?>
													</a>
													<?php if(!empty($revision['type'])): ?>
													<span>[<?php echo hsc($revision['type']); ?>]</span>
													<?php endif; ?>
												</td>
												<td class="left"><?php echo hsc($revision['revision_username']); ?></td>
											</tr>
											<?php endforeach; ?>
										</table>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
						<?php endif; ?>
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
											<tr>
												<td>
													<label for="title" class="profile-label">Title</label>
												</td>
												<td>
													<input id="page-title" class="profile-text" type="text" name="title"
														   <?php if(isset($_SESSION['page']->title)): ?>
														   value="<?php echo hsc($_SESSION['page']->title); ?>"
														   <?php elseif(isset($page['title'])): ?>
														   value="<?php echo hsc($page['title']); ?>"
														   <?php endif; ?>
														   title="The contents inside the <title></title> element for the page"
													/>
												</td>
											</tr>
											<tr>
												<td>
													<label for="alias" class="profile-label">Alias</label>
												</td>
												<td>
													<input id="alias" class="profile-text" type="text" name="alias"
														   <?php if(isset($_SESSION['page']->alias)): ?>
														   value="<?php echo hsc($_SESSION['page']->alias); ?>"
															<?php elseif(isset($page['alias'])): ?>
															value="<?php echo hsc($page['alias']); ?>"
															<?php endif; ?>
														   title="Leave this blank and the backend will fill in a default value, which is the title in lower case and with dashes instead of spaces. You may enter the Alias manually. Use lowercase letters and hypens (-). No spaces or underscores are allowed. The Alias will be used in the SEF URL. Default value will be a date and time if the title is typed in non-latin letters."
													/>
												</td>
											</tr>
											<tr>
												<td><label class="profile-label">State</label></td>
												<td>
													<select id="page-state" name="published" class="profile-slctfont">
														<?php if(aclVerify('publish pages') || aclVerify('administer pages')): ?>
														<option <?php if((isset($_SESSION['page']->published) && $_SESSION['page']->published == 1) || (isset($page['published']) && $page['published'] == 1)): ?>
																selected="selected"
																<?php endif; ?>
																value="1">Published
														</option>
														<option <?php if((isset($_SESSION['page']->published) && $_SESSION['page']->published == 0) || (isset($page['published']) && $page['published'] == 0)): ?>
																selected="selected"
																<?php endif;?>
																value="0">Unpublished
														</option>
														<?php endif; ?>
														<option <?php if((isset($_SESSION['page']->published) && $_SESSION['page']->published == 2) || (isset($page['published']) && $page['published'] == 2)): ?>
																selected="selected"
																<?php endif; ?>
																value="2">Draft
														</option>
														<option <?php if((isset($_SESSION['page']->published) && $_SESSION['page']->published == 3) || (isset($page['published']) && $page['published'] == 3)): ?>
																selected="selected"
																<?php endif; ?>
																value="3">Pending Approval
														</option>
													</select>
												</td>
											</tr>
											<?php if(aclVerify('administer pages') || aclVerify('edit any page')): ?>
											<tr>
												<td class="panel-spacing">
													<label for="author">Created By</label>
												</td>
												<td>
													<select id="author" name="author">
													<?php foreach($backendUsers as $backendUser): ?>
														<?php if(!isset($_SESSION['page']->author) && isset($page['author']) && $backendUser['id'] === $page['author']): ?>
														<option value="<?php echo hsc($page['author']); ?>" selected="selected">
															<?php echo hsc($backendUser['username']); ?>
														</option>
														<?php elseif(isset($_SESSION['page']->author) && $backendUser['id'] === $_SESSION['page']->author): ?>
														<option value="<?php echo hsc($backendUser['id']); ?>" selected="selected">
															<?php echo hsc($backendUser['username']);?>
														</option>
														<?php elseif(!isset($_SESSION['page']) && !isset($page) && $_SESSION['userId'] == $backendUser['id']): ?>
														<option value="<?php echo hsc($backendUser['id']); ?>" selected="selected">
															<?php echo hsc($backendUser['username']);?>
														</option>
														<?php else: ?>
														<option value="<?php echo hsc($backendUser['id']); ?>">
															<?php echo hsc($backendUser['username']);?>
														</option>
														<?php endif; ?>
													<?php endforeach;?>
													</select>
												</td>
											</tr>
											<?php endif; ?>
											<tr>
												<td>
													<label class="panel-spacing fltlft" for="date-posted">Date Created</label>
												</td>
												<td id="create-date-picker">
													<input id="date-created" class="panel-input profile-text" name="dateCreated" type="text"
															<?php if(isset($page['date_created'])): ?>
															value="<?php echo hsc($page['date_created']); ?>"
															<?php elseif(isset($_SESSION['page']->dateCreated)): ?>
															value="<?php echo hsc($_SESSION['page']->dateCreated); ?>"
															<?php endif; ?> 
													/>
												</td>
											</tr>
											<?php if(!empty($page['date_modified'])): ?>
											<tr>
												<td>
													<label class="panel-spacing fltlft" for="date-modified">Last Modified</label>
												</td>
												<td>
													<?php echo date('F d, Y h:i s', strtotime($page['date_modified'])); ?>
												</td>
											</tr>
											<?php endif; ?>
											<tr>
												<td>
													<label class="panel-spacing fltlft" for="page-template">Template</label>
												</td>
												<td>
													<select id="page-template" name="template">
														<?php foreach($pageTemplates as $pageTemplate): ?>
														<option value="<?php echo hsc($pageTemplate['id']); ?>" <?php if(isset($page) && $page['template'] === $pageTemplate['id']):?>selected="selected"<?php endif; ?>>
															<?php echo hsc($pageTemplate['template_name']); ?>
														</option>
														<?php endforeach; ?>
													</select>
												</td>
											</tr>
											<tr>
												<td>
													<label class="panel-spacing fltlft" for="content-format">Content Format</label>
												</td>
												<td>
													<select id="content-format" name="contentFormat">
													<?php foreach($contentFormats as $contentFormat): ?>
														<?php if(isset($page) && $page['content_format'] === $contentFormat['id']): ?>
														<option value="<?php echo hsc($contentFormat['id']); ?>" selected="selected">
															<?php echo hsc($contentFormat['format_name']); ?>
														</option>
														<?php else: ?>
														<option value="<?php echo hsc($contentFormat['id']); ?>">
															<?php echo hsc($contentFormat['format_name']); ?>
														</option>
														<?php endif; ?>
													<?php endforeach; ?>
													</select>
												</td>
											</tr>
											<tr>
												<td class="vAlignTop">
													<label class="panel-spacing fltlft">Subdomain</label>
												</td>
												<td>
													<label for="subdomain-true" class="center-label">
														<input type="radio" id="subdomain" name="subdomain" class="center-toggle" value="1"
															<?php if(isset($_SESSION['page']->subdomain) && (int)$_SESSION['page']->subdomain === 1): ?>
															   checked="checked"
															<?php elseif(isset($page['subdomain']) && (int)$page['subdomain'] === 1): ?>
															   checked="checked"
															<?php endif; ?>
														/>
														Yes
													</label>
													<label for="subdomain-false" class="center-label">
														<input type="radio" id="subdomain-false" name="subdomain" class="center-toggle" value="0"
															<?php if(isset($_SESSION['page']->subdomain) && (int)$_SESSION['page']->subdomain === 0): ?>
															   checked="checked"
															<?php elseif(isset($page['subdomain']) && (int)$page['subdomain'] === 0): ?>
															   checked="checked"
															<?php elseif(!isset($page) && !isset($_SESSION['page'])): ?>
															   checked="checked"
															<?php endif; ?>
														/>
														No
													</label>
												</td>
											</tr>
										</table>
										<div class="standout-space clearfix">
											<div class="fltlft">
												<button id="publish-btn" class="quick-publish-page rb-btn blue-btn">Publish</button>
											</div>
											<div class="fltrght">
												<button id="draft-btn" class="draft-page-content rb-btn light-gray-btn">Draft</button>
											</div>
										</div>
										<div class="clearfix"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel">
							<div class="panel-column">
								<div class="element">
									<div class="element-top">Metadata Options</div>
									<div class="element-body">
										<div class="metadata-details">
											<table class="panel-content">
												<tr>
													<td class="align-top panel-spacing"><label for="metaDescription">Description</label></td>
													<td>
														<textarea id="metaDescription" name="metaDescription" title="A short description (30 characters or less) for this page."><?php if(isset($_SESSION['page']->metaDescription)): echo hsc($_SESSION['page']->metaDescription); elseif(isset($page['meta_description'])): echo hsc($page['meta_description']); endif; ?></textarea>
													</td>
												</tr>
												<tr>
													<td class="align-top panel-spacing">
														<label for="metaKeywords">Keywords</label>
													</td>
													<td>
														<textarea id="metaKeywords" name="metaKeywords" title="Comma separated keywords for this page"><?php if(isset($_SESSION['page']->metaKeywords)): echo hsc($_SESSION['page']->metaKeywords); elseif(isset($page['meta_keywords'])): echo hsc($page['meta_keywords']); endif; ?></textarea>
													</td>
												</tr>
												<tr>
													<td>
														<label for="metaRobots">Robots</label>
													</td>
													<td>
														<input id="metaRobots" type="text" name="metaRobots" class="profile-text" maxlength="100"
															   title="Leave blank unles you do not want this page indexed by search engines.  In that case use: noindex"
															<?php if(isset($_SESSION['page']->metaRobots)): ?>
															   value="<?php echo hsc($_SESSION['page']->metaRobots); ?>"
															<?php elseif(isset($page['meta_robots'])): ?>
															   value="<?php echo hsc($page['meta_robots']); ?>"
															<?php endif; ?>
														/>
													</td>
												</tr>
												<tr>
													<td><label for="metaAuthor">Author</label></td>
													<td>
														<input id="metaAuthor" type="text" name="metaAuthor" class="profile-text" title="The author of this page" maxlength="100"
															<?php if(isset($_SESSION['page']->metaAuthor)): ?>
															   value="<?php echo hsc($_SESSION['page']->metaAuthor); ?>"
															<?php elseif(isset($page['meta_author'])): ?>
															   value="<?php echo hsc($page['meta_author']); ?>"
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
						<div class="panel">
							<div>
								<div class="element">
									<div class="element-top">Page Options</div>
									<div class="element-body">
										<table class="panel-content">
											<tr>
												<td class="panel-spacing">
													<label>Disable Tinymce</label>
												</td>
												<td>
													<select id="disable-tinymce" name="attribs[disable_tinymce]">
														<option value="0" <?php if((isset($page['disable_tinymce']) && $page['disable_tinymce'] == 0)): ?>selected="selected"<?php endif; ?>>
															No
														</option>
														<option value="1" <?php if((isset($page['disable_tinymce']) && $page['disable_tinymce'] == 1)): ?>selected="selected"<?php endif; ?>>
															Yes
														</option>
													</select>
												</td>
											</tr>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
			<div class="clearfix"></div>
			<div id="options-form" title="Page Options">
				<div class="panel">
					<div class="panel-column">
						<div class="element">
							<div class="element-top">Options to display</div>
							<div class="element-body options-checklist">
								<form action="<?php echo HTTP_SERVER; ?>pages" method="post">
									<table>
									<?php foreach($optionsForPage as $optionForPage): ?>
										<tr>
											<td>
												<label>
													<input type="checkbox" id="option-<?php echo hsc($optionForPage['id']); ?>" class="optionChange"
														   <?php if(in_array($optionForPage['id'], $userOptions)): ?>
														   checked="checked"
														   <?php endif; ?>
													/>
												<?php echo hsc($optionForPage['option_name']); ?>
												</label>
											</td>
										</tr>
									<?php endforeach; ?>
									</table>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="add-media-window" title="Attach Media">
				<ul>
					<li><a href="#upload-comp">From Computer</a></li>
					<li><a href="#upload-url">From URL</a></li>
					<li><a id="load-media-library" href="#media-lib">Media Library</a></li>
				</ul>
				<div id="upload-comp">
					<iframe></iframe>
				</div>
				<div id="upload-url">
					<p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>
				</div>
				<div id="media-lib">
					<iframe></iframe>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="fullscreen-overlay" style="display: none;"></div>
<div id="media-window-loader" class="ajax-page-loader" style="display: none;"></div>
<div id="fullscreen-fader" class="fullscreen-overlay" style="display: none;"></div>
<div id="fullscreen-wrapper" style="display: none;">
	<div id="fullscreen-menu"></div>
	<div id="fullscreen-container">
		<label for="fullscreen-title" class="screen-reader">Enter title here</label>
		<input type="text" id="fullscreen-title" placeholder="Enter title here" />
		<div id="fullscreen-editor-wrap">
			<div id="fullscreen-html-menu" class="cirkuitSkin mceExternalToolbar">
				<a class="mceButton mceButtonEnabled mceIcon mce_fullscreen_close close-fullscreen-html-editor" title="Exit Fullscreen">
					<span class="mceIcon mceIcon mce_fullscreen_close_custom"></span>
				</a>
			</div>
			<div id="fullscreen-editor-selection">
				<ul id="fullscreen-editor-selector">
					<li id="fullscreen-editor-tab">
						<a href="#fullscreen-visual-editor-wrap">Visual</a>
					</li>
					<li id="fullscreen-html-editor-tab">
						<a href="#fullscreen-html-editor-wrap">HTML</a>
					</li>
				</ul>
				<div id="fullscreen-visual-editor-wrap">
					<textarea id="fullscreen-editor" class="<?php if($disableTinymce): ?>mceNoEditor<?php endif; ?>"></textarea>
				</div>
				<div id="fullscreen-html-editor-wrap">
					<textarea id="fullscreen-html-editor"></textarea>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>
</div>
<?php displayFooter();?>
<?php pageCleanUp(); ?>
