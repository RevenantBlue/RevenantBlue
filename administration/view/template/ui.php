<?php
namespace RevenantBlue\Admin;
?>
<?php function loadMainJs() { ?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/main.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/main.min.js"></script>
<?php endif; ?>
<?php } ?>

<?php function loadTinyMce() { ?>
<?php global $globalSettings; ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	'use strict';
	$('#content-editor:not(".mceNoEditor"), #article-editor, #summary-editor, #description-editor').tinymce({
		script_url : "<?php echo HTTP_ADMIN_DIR; ?>view/js/tinymce/jscripts/tiny_mce/tiny_mce_gzip.php",
	   // General options
		editor_selector : "tinyMCESimpleTextShort",
		skin : "cirkuit",
		theme : "advanced",
		dialog_type : "modal",
		relative_urls : false,
		remove_script_host : false,
		convert_urls : false,
		remove_linebreaks : true,
		valid_elements: '*[*]',
		editor_deselector : "mceNoEditor",
		doctype : "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' " + "'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>",
		element_formt : 'xhtml',
		theme_advanced_font_sizes : "10px,12px,13px,14px,16px,18px,20px",
		font_size_style_values : "10px,12px,13px,14px,16px,18px,20px",
		extended_valid_elements : "iframe[src|width|height|name|align|frameborder|scrolling]",
		plugins: "autolink,lists,spellchecker,pagebreak,style,table,advhr,advlink,iespell,inlinepopups,insertdatetime,media,searchreplace,print,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,pdw",
		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough, | ,bullist,numlist,blockquote, | ,justifyleft,justifycenter,justifyright, | ,link,unlink, | ,fullscreen-custom,pdw_toggle",
		theme_advanced_buttons2 : "formatselect,fontsizeselect,justifyfull,pastetext,pasteword,|,search,replace, | ,outdent,indent",
		theme_advanced_buttons3 : "undo,redo, |, anchor,image,cleanup, | ,forecolor,backcolor, | ,sub,sup,charmap, | , help",
		theme_advanced_buttons4 : "tablecontrols, |, hr,removeformat,visualaid, | ,iespell,media,advhr,|,print,|,ltr,rtl",
		theme_advanced_buttons5 : "styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage,preview",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		height : 400,
		theme_advanced_resizing : true,
		theme_advanced_resizing_use_cookie : false,
		theme_advanced_resize_horizontal : false,

		fullscreen_settings : {
			//width : "640",
			//height: "480"
		},
		// List the element ids that will be using tinymce.
		gecko_spellcheck : false,
		// Skin options
		pdw_toggle_on : 1,
		pdw_toggle_toolbars : "2,3,4,5",
		setup : function(ed) {
			// Give the article editor the fullscreen button.
			if(ed.id === "content-editor") {
				ed.addButton("fullscreen-custom", {
					title   : "Toggle Fullscreen",
					class   : "mceIcon mce_fullscreen_custom",
					onclick : function() {
						openEditorFullscreen(function() {
							//$("#fullscreen-html-editor").val(currentContent);
							// Set the fullscreen tab as the active tab.
							fullscreenEditorTabs.tabs("option", "active", 0);
							activeEditor = "fullscreen-editor";
							if(disableTinymce) {
								$("#fullscreen-editor").val($("#content-editor").html());
							} else {
								$("#fullscreen-editor").html($("#content-editor").html());
							}
						});
					}
				});
			}
			// Reduce the height of the description-editor
			if(ed.id === "description-editor") {

			}
			// Required to have proper page refreshing whilst tinymce editor has focus.
			ed.onKeyDown.add(function(ed, e) {
				if(e.which === 116) {
					localStorage.refresh = 'refresh';
				}
			});
			ed.onKeyDown.add(function(ed, e) {
				if(ed.id === "summary-editor") {
					
					if(e.which != 9) {
						return false;
					}
					if(e.shiftKey && !e.ctrlKey && !e.altKey) {
						if($("#content-editor").length !== 0) {
							if(ed && !ed.isHidden()) {
								$("#content-editor_tbl td.mceToolbar > a").focus();
							}
						} else if($("#description-editor").length !== 0) {
							if(ed && !ed.isHidden()) {
								$("#description-editor_tbl td.mceToolbar > a").focus();
							}
						}
					}
				} else if(ed.id === "content-editor" || ed.id === "description-editor") {
					if(e.which != 9) {
						return false;
					}
					if(e.shiftKey && !e.ctrlKey && !e.altKey) {
						if($("#title").length !== 0) {
							$("#title").focus();
							e.preventDefault();
						}
					}
				}
			});
		}
	});
	
	$('#fullscreen-editor').tinymce({
		script_url : '<?php echo HTTP_ADMIN_DIR; ?>view/js/tinymce/jscripts/tiny_mce/tiny_mce_gzip.php',
	   // General options
		editor_selector : "tinyMCESimpleTextShort",
		skin : "cirkuit",
		theme : "advanced",
		dialog_type : "modal",
		relative_urls : false,
		remove_script_host : false,
		convert_urls : false,
		remove_linebreaks : true,
		valid_elements: '*[*]',
		editor_deselector : "mceNoEditor",
		doctype : "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' " + "'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>",
		element_formt : 'xhtml',
		theme_advanced_font_sizes : "10px,12px,13px,14px,16px,18px,20px",
		font_size_style_values : "10px,12px,13px,14px,16px,18px,20px",
		extended_valid_elements : "iframe[src|width|height|name|align|frameborder|scrolling]",
		plugins: "tabfocus,autoresize,autolink,wordcount,xhtmlxtras",
		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline, | ,bullist,numlist,blockquote, | ,link,unlink, | ,addmedia, | ,fullscreen-close-custom",
		theme_advanced_toolbar_location : "external",
		theme_advanced_toolbar_align : "left",
		theme_advanced_resizing : true,
		theme_advanced_statusbar_location : 'none',
		theme_advanced_path : false,
		setup : function(ed) {
			// Hide the html buttons on startup.
			$('#fullscreen-html-menu > a').hide();
			ed.addButton('fullscreen-close-custom', {
				title: 'Toggle Fullscreen',
				'class': 'mceIcon mce_fullscreen_close_custom',
				onclick: function() {
					closeEditorFullscreen(function() {
						activeEditor = 'content-editor';
						editorTabs.tabs("option", "active", 1);
						if(disableTinymce) {
							$("#content-editor").val($("#fullscreen-editor").val());
						} else {
							$("#content-editor").html($("#fullscreen-editor").html());
						}
					});
				}
			});
			ed.addButton('addmedia', {
				title: 'Add Media',
				'class': 'mceIcon mce_addmedia',
				onclick: function() {
					$("#add-media-window").dialog("open");
				}
			});
			// Required to have proper page refreshing whilst tinymce editor has focus.
			ed.onKeyDown.add(function(ed, e) {
				if(e.which === 116) {
					localStorage.refresh = 'refresh';
				}
			});
		}
	});
});
</script>
<?php } ?>

<?php function loadCKEditor() { ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/ckeditor/adapters/jquery.js"></script>
<?php } ?>

<?php function loadJqueryUi() { ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/bower-components/jquery-ui/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/js/jquery-ui-1.11.0/jquery-ui.min.css" />
<?php } ?>

<?php function loadTableDragNDrop() { ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/tablednd.js"></script>
<?php } ?>

<?php function loadJqueryValidation() { ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/jquery-validation-1.11.1/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/jquery-validation-1.11.1/additional-methods.min.js"></script>
<script type="text/javascript">
	// Custom validation.
	$.validator.addMethod('validateCheckboxOneOrMore', function (value) {
		return $('.require-one-check:checked').size() != 0; 
	}, 'Check one');
</script>
<?php } ?>

<?php function loadPlupload() { ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/plupload/js/plupload.full.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/plupload/js/jquery.plupload.queue/jquery.plupload.queue.min.js"></script>
<?php } ?>

<?php function loadJCrop() { ?>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/js/jcrop/jquery.Jcrop.min.css" />
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/jcrop/jquery.color.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/jcrop/jquery.Jcrop.min.js"></script>
<?php } ?>

<?php function loadElastic() { ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/elastic/jquery.elastic.source.js"></script>
<?php } ?>

<?php function loadSlimScroll() { ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/slimscroll.js"></script>
<?php } ?>

<?php function loadJqueryNestable() { ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/jquery-nestable.js"></script>
<?php } ?>

<?php function loadCodeMirror() { ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/bower-components/codemirror/lib/codemirror.js"></script>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/js/bower-components/codemirror/lib/codemirror.css">
<script src="<?php echo HTTP_ADMIN_DIR; ?>view/js/bower-components/codemirror/mode/css/css.js"></script>
<script src="<?php echo HTTP_ADMIN_DIR; ?>view/js/bower-components/codemirror/mode/javascript/javascript.js"></script>
<script src="<?php echo HTTP_ADMIN_DIR; ?>view/js/bower-components/codemirror/mode/php/php.js"></script>
<script src="<?php echo HTTP_ADMIN_DIR; ?>view/js/bower-components/codemirror/mode/xml/xml.js"></script>
<script src="<?php echo HTTP_ADMIN_DIR; ?>view/js/bower-components/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="<?php echo HTTP_ADMIN_DIR; ?>view/js/bower-components/codemirror/mode/htmlembedded/htmlembedded.js"></script>
<?php } ?>

<?php function loadUnderscore() { ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/underscore/underscore.js"></script>
<?php } ?>

<?php function loadFancyBox() { ?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/fancybox-2.1.5/source/jquery.fancybox.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/fancybox-2.1.5/source/jquery.fancybox.pack.js"></script>
<?php endif; ?>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/js/fancybox-2.1.5/source/jquery.fancybox.css"></script>
<?php } ?>

<?php function loadMainCss() { ?>
<link rel="stylesheet" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/main.css" type="text/css" />
<?php } ?>

<?php function displayLogin() { ?>
<?php global $csrfToken; ?>
<div class="login">
	<form action="<?php echo HTTP_ADMIN; ?>" method="post">
		<table id="login-table">
			<tr>
				<td>
					<input type="hidden" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					<label for="username" class="login-labels">Username</label>
					<input type="text" id="username" name="username" size="20" class="login-input" tabindex="1" />
				</td>
				<td>
					<label for="password" class="login-labels">Password</label>
					<input type="password" id="password" name="password" size="20" class="login-input" tabindex="2" />
				</td>
				<td>
					<input type="submit" value="Login" name="submitLogin" class="login-submit" tabindex="3" />
				</td>
			</tr>
		</table>
	</form>
</div>
<?php } ?>

<?php function displayLogOut() { ?>
<?php global $csrfToken; ?>
<form id="logout-form" class="fltrght" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
	<div class="logout fltrght">
		<input type="hidden" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
		<input type="hidden" name="logout" value="logout" />
		<button id="logout" class="rb-btn std-btn" name="logout">LOG OUT</button>
	</div>
</form>
<?php } ?>

<?php function displayBanner($globalSettings) { ?>
<div id="admin-banner">
	<div id="admin-banner-inner">
		<div>
			<a href="<?php echo HTTP_ADMIN; ?>" title="Revenant Blue Home">
				<img id="admin-logo" src="<?php echo HTTP_IMAGE; ?>admin/revenant-blue-logo-abbv.png" alt="<?php echo hsc($globalSettings['site_title']['value']); ?>" />
			</a>
		</div>
		<?php if(empty($_SESSION['username']) && empty($sessId) && !isset($loginValidation->errors) && !isset($_SESSION['error'])): ?>
			<?php displayLogin(); ?>
		<?php elseif(isset($_SESSION['userId']) && isset($_SESSION['username']) && session_name() == 'backend' && aclVerify('backend admin')):?>
			<?php displayLogOut(); ?>
		<ul id="notifications-menu">
			<li>
				<a id="notifications-link" href="<?php echo HTTP_ADMIN; ?>notifications" target="mainIframe">
					<?php if(!empty($_SESSION['numOfUnreadNotifs'])): ?>
					<span id="notifs-icon" class="ui-icon ui-icon-notice active fltlft" title="You have <?php echo hsc($_SESSION['numOfUnreadNotifs']); ?> unread notifications">
						<?php echo hsc($_SESSION['numOfUnreadNotifs']); ?>
					</span>
					<?php else: ?>
					<span id="notifs-icon" class="ui-icon ui-icon-notice fltlft" title="You have no unread notifications"></span>
					<?php endif; ?>
				</a>
			</li>
			<li>
				<a id="private-messages-link" href="<?php echo HTTP_ADMIN; ?>messages" target="mainIframe">
					<?php if(!empty($_SESSION['numOfUnreadMsgs'])): ?>
					<span id="msgs-icon" class="ui-icon ui-icon-mail-closed active fltlft" title="You have <?php echo hsc($_SESSION['numOfUnreadMsgs']); ?> unread messages">
						<?php echo hsc($_SESSION['numOfUnreadMsgs']); ?>
					</span>
					<?php else: ?>
					<span id="msgs-icon" class="ui-icon ui-icon-mail-closed fltlft" title="You have no unread messages"></span>
					<?php endif; ?>
				</a>
			</li>
		</ul>
		<div id="main-actions" class="controls fltrght">
			<a id="main-user-actions" href="#">
				<span><?php echo hsc($_SESSION['username']); ?></span>
				<div class="header-ddarr inline-block"> </div>
			</a>
			<div id="main-user-actionmenu" class="actionmenu autowidth" style="display: none;">
				<div class="inner">
					<div class="pointer-top"></div>
					<div class="pointer-bottom"></div>
					<h3><?php echo hsc($_SESSION['username']); ?></h3>
					<ul>
						<li>
							<a href="<?php echo HTTP_ADMIN . "profile"; ?>" target="mainIframe">Profile</a>
						</li>
						<li>
							<a href="<?php echo HTTP_ADMIN . "messages" ?>" target="mainIframe">Messages</a>
						</li>
						<li>
							<a href="http://www.revenantblue.com">Revenant Blue website</a>
							<a href="http://www.revenantblue.com/contact">Contact us</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
<?php } ?>

<?php function displayLeftMenu() { ?>
<?php if(!empty($_SESSION['userId'])): ?>
<nav id="left-nav" style="display: none">
	<div id="left-nav-wrap">
		<ul id="left-nav-main">
			<li id="nav-home" class="top-nav">
				<a id="home-link" href="<?php echo HTTP_ADMIN; ?>dashboard" target="mainIframe" class="left-nav-link selected selected-no-bottom no-submenu">
					<span class="menu-txt">Home</span>
					<span class="left-nav-icon sprites"></span>
				</a>
			</li>
			<li id="nav-articles" class="top-nav">
				<a id="articles-link" href="<?php echo HTTP_ADMIN; ?>articles" target="mainIframe" class="left-nav-link">
					<span class="menu-txt">Articles</span>
					<span class="left-nav-icon sprites"></span>
					<span class="nav-arr"></span>
				</a>
				<div class="submenu">
					<a id="new-article-link" href="<?php echo HTTP_ADMIN; ?>articles/new" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Create Article</span>
						<span class="left-nav-icon sprites"></span>
					</a>
					<a id="comments-link" href="<?php echo HTTP_ADMIN; ?>comments" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Comments</span>
						<span class="left-nav-icon sprites"></span>
					</a>
				</div>
			</li>
			<li id="nav-pages" class="top-nav">
				<a id="pages-link" href="<?php echo HTTP_ADMIN; ?>pages" target="mainIframe" class="left-nav-link">
					<span class="menu-txt">Pages</span>
					<span class="left-nav-icon sprites"></span>
					<span class="nav-arr"></span>
				</a>
				<div class="submenu">
					<a id="new-page-link" href="<?php echo HTTP_ADMIN; ?>pages/new" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Create Page</span>
						<span class="left-nav-icon sprites"></span>
					</a>
					<a id="page-templates-link" href="<?php echo HTTP_ADMIN; ?>pages/templates" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Page Templates</span>
						<span class="left-nav-icon sprites"></span>
					</a>
				</div>
			</li>
			<li id="nav-forums" class="top-nav">
				<a id="forums-link" href="<?php echo HTTP_ADMIN; ?>forums" target="mainIframe" class="left-nav-link">
					<span class="menu-txt">Forums</span>
					<span class="left-nav-icon sprites"></span>
					<span class="nav-arr"></span>
				</a>
				<div class="submenu">
					<a id="new-forum-link" href="<?php echo HTTP_ADMIN; ?>forums/new" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Add New Forum</span>
						<span class="left-nav-icon sprites"></span>
					</a>
					<a id="new-forum-section-link" href="<?php echo HTTP_ADMIN; ?>forums/section/new" target="mainIframe" class="left-nav-link">
						<span class="menu-text">Add New Section</span>
						<span class="left-nav-icon sprites"></span>
					</a>
					<a id="report-posts-link" href="<?php echo HTTP_ADMIN; ?>forums/reported-posts" target="mainIframe" class="left-nav-link">
						<span class="menu-text">Reported Posts</span>
						<span class="left-nav-icon sprites"></span>
					</a>
					<a id="attachments-link" href="<?php echo HTTP_ADMIN; ?>forums/attachments" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Attachments</span>
						<span class="left-nav-icon sprites"></span>
					</a>
				</div>
			</li>
			<li id="nav-galleries" class="top-nav">
				<a id="galleries-link" href="<?php echo HTTP_ADMIN; ?>photogallery" target="mainIframe" class="left-nav-link">
					<span class="menu-txt">Galleries</span>
					<span class="left-nav-icon sprites"></span>
					<span class="nav-arr"></span>
				</a>
				<div class="submenu">
					<a id="new-album-link" href="<?php echo HTTP_ADMIN; ?>photogallery/new" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Create Album</span>
						<span class="left-nav-icon sprites"></span>
					</a>
				</div>
			</li>
			<li id="nav-categories" class="top-nav">
				<a id="categories-link" href="<?php echo HTTP_ADMIN; ?>categories" target="mainIframe" class="left-nav-link">
					<span class="menu-txt">Categories</span>
					<span class="left-nav-icon sprites"></span>
					<span class="nav-arr"></span>
				</a>
				<div class="submenu">
					<a id="new-category-link" href="<?php echo HTTP_ADMIN; ?>categories/new" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Create Category</span>
						<span class="left-nav-icon sprites"></span>
					</a>
				</div>
			</li>
			<li id="nav-menus" class="top-nav">
				<a id="menus-link" href="<?php echo HTTP_ADMIN; ?>menus" target="mainIframe" class="left-nav-link">
					<span class="menu-txt">Menus</span>
					<span class="left-nav-icon sprites"></span>
					<span class="nav-arr"></span>
				</a>
				<div class="submenu">
					<a id="new-menu-link" href="<?php echo HTTP_ADMIN; ?>menus/new" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Add Menu</span>
						<span class="left-nav-icon sprites"></span>
					</a>
				</div>
			</li>
			<li id="nav-tags" class="top-nav">
				<a id="tags-link" href="<?php echo HTTP_ADMIN; ?>tags" target="mainIframe" class="left-nav-link">
					<span class="menu-txt">Tags</span>
					<span class="left-nav-icon sprites"></span>
					<span class="nav-arr"></span>
				</a>
				<div class="submenu">
					<a id="new-tag-link" href="<?php echo HTTP_ADMIN; ?>tags/new" target="mainIframe" class="left-nav-links">
						<span class="menu-txt">Create Tag</span>
						<span class="left-nav-icon sprites"></span>
					</a>
				</div>
			</li>
			<li id="nav-media" class="top-nav">
				<a id="media-link" href="<?php echo HTTP_ADMIN; ?>media" target="mainIframe" class="left-nav-link">
					<span class="menu-txt">Media</span>
					<span class="left-nav-icon sprites"></span>
					<span class="nav-arr"></span>
				</a>
				<div class="submenu">
					<a id="media-upload-link" href="<?php echo HTTP_ADMIN; ?>/upload" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Upload Media</span>
						<span class="left-nav-icon sprites"></span>
					</a>
				</div>
			</li>
			<li id="nav-links" class="top-nav">
				<a id="links-link" href="<?php echo HTTP_ADMIN; ?>links" target="mainIframe" class="left-nav-link">
					<span class="menu-txt">Links</span>
					<span class="left-nav-icon sprites"></span>
					<span class="nav-arr"></span>
				</a>
				<div class="submenu">
					<a id="new-link-link" href="<?php echo HTTP_ADMIN; ?>links/new" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">New Link</span>
						<span class="left-nav-icon sprites"></span>
					</a>
					<a id="linkcats-link" href="<?php echo HTTP_ADMIN; ?>links/categories" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Link Categories</span>
						<span class="left-nav-icon sprites"></span>
					</a>
				</div>
			</li>
			<li id="nav-plugins" class="top-nav">
				<a id="plugins-link" href="<?php echo HTTP_ADMIN; ?>plugins" target="mainIframe" class="left-nav-link">
					<span class="menu-txt">Plugins</span>
					<span class="left-nav-icon sprites"></span>
					<span class="nav-arr"></span>
				</a>
			</li>
			<li id="nav-users" class="top-nav">
				<a id="users-link" href="<?php echo HTTP_ADMIN; ?>users" target="mainIframe" class="left-nav-link">
					<span class="menu-txt">Users</span>
					<span class="left-nav-icon sprites"></span>
					<span class="nav-arr"></span>
				</a>
				<div class="submenu">
					<a id="new-user-link" href="<?php echo HTTP_ADMIN; ?>users/new" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">New User</span>
						<span class="left-nav-icon sprites"></span>
					</a>
					<a id="roles-link" href="<?php echo HTTP_ADMIN; ?>users/roles" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Roles</span>
						<span class="left-nav-icon sprites"></span>
					</a>
					<a id="new-role-link" href="<?php echo HTTP_ADMIN; ?>users/roles/new" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Create Role</span>
						<span class="left-nav-icon sprites"></span>
					</a>
				</div>
			</li>
			<li id="nav-config" class="top-nav">
				<a id="config-link" href="<?php echo HTTP_ADMIN; ?>config" target="mainIframe" class="left-nav-link">
					<span class="menu-txt">Configuration</span>
					<span class="left-nav-icon sprites"></span>
					<span class="nav-arr"></span>
				</a>
				<div class="submenu">
					<a id="global-settings-link" href="<?php echo HTTP_ADMIN; ?>config/global-settings" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Global Settings</span>
						<span class="left-nav-icon sprites"></span>
					</a>
					<a id="account-settings-link" href="<?php echo HTTP_ADMIN; ?>config/account-settings" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Account Settings</span>
						<span class="left-nav-icon sprites"></span>
					</a>
					<a id="site-security-link" href="<?php echo HTTP_ADMIN; ?>config/site-security" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Site Security</span>
						<span class="left-nav-icon sprites"></span>
					</a>
					<a id="scheduled-tasks-link" href="<?php echo HTTP_ADMIN; ?>config/scheduled-tasks/" target="mainIframe" class="left-nav-link">
						<span class="menu-txt">Scheduled Tasks</span>
						<span class="left-nav-icon sprites"></span>
					</a>
				</div>
			</li>
		</ul>
		<div class="footer-push"> </div>
	</div>
	<div id="nav-footer">

	</div>
</nav>
<div id="nav-toggle">‹</div>
<div id="vert-separator"> </div>
<div id="vert-separator-fix"> </div>
<?php endif; ?>
<?php } ?>

<?php function displayBreadCrumbs($target = false, $url = false, $overrideUrl = false) { ?>
	<?php if(isset($_GET['controller']) && $_GET['controller'] !== 'dashboard'): ?>
		<div id="breadcrumbs">
			<a id="bc-home" href="<?php echo HTTP_ADMIN; ?>dashboard" class="inactive breadcrumb">HOME</a>
			<span class="spacer"> / </span>
			<?php if(!empty($_GET['section'])): ?>
			<a id="bc-<?php echo hsc($_GET['section']); ?>" href="<?php echo HTTP_ADMIN . str_replace('-', '', hsc($_GET['section'])); ?>" class="inactive">
				<?php echo strtoupper(str_replace('-', ' ', hsc($_GET['section']))); ?>
			</a>
			<span class="spacer"> / </span>
			<?php endif; ?>
			<?php if(is_array($overrideUrl)): ?>
			<a id="bc-<?php echo hsc($_GET['controller']); ?>" href="<?php echo hsc($overrideUrl['url']); ?>" class="<?php if(empty($target)): ?>active<?php else: ?>inactive<?php endif; ?> breadcrumb">
				<?php echo strtoupper(str_replace('-', ' ', $overrideUrl['title'])); ?>
			</a>
			<?php else: ?>
			<a id="bc-<?php echo hsc($_GET['controller']); ?>" href="<?php echo HTTP_ADMIN . hsc($_GET['controller']); ?>" class="<?php if(empty($target)): ?>active<?php else: ?>inactive<?php endif; ?> breadcrumb">
				<?php echo strtoupper(str_replace('-', ' ', hsc($_GET['controller']))); ?>
			</a>
			<?php endif; ?>
			<?php if(!empty($target) && !empty($url)): ?>
			<span class="spacer"> / </span>
			<a href="<?php echo hsc($url); ?>" target="mainIframe"><?php echo strtoupper(hsc($target)); ?></a>
			<?php endif; ?>
		</div>
	<?php else: ?>
		<div id="breadcrumbs">
			<a id="bc-home" href="<?php echo HTTP_ADMIN; ?>dashboard" class="breadcrumb">HOME</a>
		</div>
	<?php endif; ?>
<?php } ?>

<?php function displayNotifications() { ?>
<?php if(!empty($_SESSION['errors'])): ?>
	<?php if(is_array($_SESSION['errors'])): ?>
		<?php foreach($_SESSION['errors'] as $key=>$error): ?>
			<?php if(is_array($error)): ?>
				<?php foreach($error as $key2 => $error2): ?>
				 <div id="errors<?php echo $key2; ?>" class="errors clearfix">
					<p class="error-txt"><?php echo hsc($error2); ?></p>
					<span class="ui-icon ui-icon-close"> </span>
				</div>
				<?php endforeach; ?>
			<?php else: ?>
			<div id="errors<?php echo $key; ?>" class="errors clearfix">
				<p class="error-txt"><?php echo hsc($error); ?></p>
				<span class="ui-icon ui-icon-close"> </span>
			</div>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php else: ?>
		<div class="errors clearfix">
			<p class="error-txt"><?php echo hsc($_SESSION['errors']); ?></p>
			<span class="ui-icon ui-icon-close"> </span>
		</div>
	<?php endif; ?>
	<?php unset($_SESSION['errors']); ?>
<?php endif; ?>
<?php if(!empty($_SESSION['success'])): ?>
	<?php if(is_array($_SESSION['success'])): ?>
		<?php foreach($_SESSION['success'] as $key=>$success): ?>
		<div id="success<?php echo $key; ?>" class="success clearfix">
			<p class="success-txt"><?php echo hsc($success); ?></p>
			<span class="ui-icon ui-icon-close"> </span>
		</div>
		<?php endforeach; ?>
	<?php else: ?>
		<div id="success" class="success clearfix">
			<p class="success-txt"><?php echo hsc($_SESSION['success']); ?></p>
			<span class="ui-icon ui-icon-close"> </span>
		</div>
	<?php endif; ?>
	<?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php } ?>

<?php function displayLoginMessages() { ?>
	<?php if(isset($_SESSION['success'])): ?>
		<span><?php echo $_SESSION['success']; ?></span>
		<?php unset($_SESSION['success']); ?>
	<?php elseif(isset($_SESSION['errors'])): ?>
		<?php if(is_array($_SESSION['errors'])): ?>
			<?php foreach($_SESSION['errors'] as $error): ?>
			<span><?php echo $error; ?></span>
			<?php endforeach; ?>
		<?php else: ?>
			<span><?php echo $_SESSION['errors']; ?></span>
		<?php endif; ?>
		<?php unset($_SESSION['errors']); ?>
	<?php endif; ?>
<?php } ?>

<?php function displayFooter() { ?>
</body>
</html>
<?php } ?>
