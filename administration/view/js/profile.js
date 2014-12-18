// JavaScript Document

var editorTabs
  , fullscreenEditorTabs
  , activeEditor = "content-editor";

// Function for fading in the menu during fullscreen
function showFullscreen(duration, delay) {
	duration = typeof duration !== "undefined" ? duration : 0;
	delay = typeof delay !== "undefined" ? delay : 0;
	$("#fullscreen-editor-wrap, #fullscreen-title").delay(delay).animate({
		borderLeftColor:   "#CCCCCC",
		borderTopColor:    "#CCCCCC",
		borderRightColor:  "#CCCCCC",
		borderBottomColor: "#CCCCCC"
	}, duration);
	$("#fullscreen-menu, .mceExternalToolbar, #fullscreen-html-editor-tab, #fullscreen-editor-tab").delay(delay).fadeTo(duration, 1);
}

// Function for fading out the menu during fullscreen.
function hideFullscreen(duration, delay) {
	duration = typeof duration !== "undefined" ? duration : 0;
	delay = typeof delay !== "undefined" ? delay : 0;
	// Fade the borders and and menu.
	$("#fullscreen-editor-wrap, #fullscreen-title").delay(delay).animate({
		  borderLeftColor:   "transparent",
		  borderTopColor:    "transparent",
		  borderRightColor:  "transparent",
		  borderBottomColor: "transparent"
	}, duration);
	$("#fullscreen-menu, .mceExternalToolbar, #fullscreen-html-editor-tab, #fullscreen-editor-tab").delay(delay).fadeTo(duration, 0);
}

function openEditorFullscreen(callback) {
	var currentMceContent
	  , editorId
	  , hoverConfig
	  , menuHoverFlag = false
	  , tabHoverFlag = false
	  , mceHoverFlag = false
	  , activeTab
	  , htmlContent;
	
	// Set the current title for the fullscreen editor.
	$("#fullscreen-title").val($("#title").val());
	
	// Show the custom fullscreen, hide the main screen, set the current content for the fullscreen editor.
	if($("#fullscreen-overlay").length !== 0) {
		$("#fullscreen-overlay, #fullscreen-wrapper").show();
		hideMenus();
	}
	
	// Set the fullscreen as the active text editor, if currently viewing html set the html as the current fullscreen choice.
	$("#" + editorId).removeProp("name");
	
	// Deselect the bold button (for some reason it's active until you click the tinymce editor
	$("#fullscreen-editor_bold").removeClass("mceButtonActive");
	
	// Initalize the fullscreen by showing it then fading it out.
	showFullscreen(0);
	hideFullscreen("slow", 3000);
	
	// Set the active tab of the fullscreen editor to match the standard editor
	activeTab = editorTabs.tabs("option", "active");

	// Go through a bunch of bullshit with hover intent to make the menu hovering look right. I'm sure there is a better way /sigh.
	$("#fullscreen-menu").hoverIntent({
		over: function() {
			if(!mceHoverFlag && !tabHoverFlag) {
				showFullscreen(250);
			}
		},
		interval: 0,
		timeout: 4000,
		out: function() {
			if(!mceHoverFlag && !tabHoverFlag) {
				hideFullscreen(500);
			}
		}
	}).mouseenter(function() {
		menuHoverFlag = true;
	}).mouseleave(function() {
		menuHoverFlag = false;
	});
	$(".mceExternalToolbar").hoverIntent({
		over: function() {
			if(!menuHoverFlag && !tabHoverFlag) {
				showFullscreen(250);
			}
		},
		interval: 0,
		timeout: 4000,
		out: function() {
			if(!menuHoverFlag && !tabHoverFlag) {
				hideFullscreen(500);
			}
		}
	}).mouseenter(function() {
		mceHoverFlag = true;
	}).mouseleave(function() {
		mceHoverFlag = false;
	});
	$("#fullscreen-editor-selector").hoverIntent({
		over: function() {
			if(!mceHoverFlag && !menuHoverFlag) {
				showFullscreen(250);
			}
		},
		interval: 0,
		timeout: 4000,
		out: function() {
			if(!mceHoverFlag && !menuHoverFlag) {
				hideFullscreen(500);
			}
		}
	}).mouseenter(function() {
		tabHoverFlag = true;
	}).mouseleave(function() {
		tabHoverFlag = false;
	});
	
	if(typeof(callback) === "function") {
		callback();
	}
}

function closeEditorFullscreen(callback) {
	var activeTab;
	// Show the main screen, hide the custom fullscreen.
	if($("#fullscreen-overlay").length !== 0) {
		$("#fullscreen-overlay, #fullscreen-wrapper").hide();
		showMenus();
		// Transfer the content depending on whether or not the fullscreen visual or html editor were active.
		activeTab = fullscreenEditorTabs.tabs("option", "active");
		if(activeTab === 0) {
			//console.log(activeEditor);
			
			// Set the active editor
			activeEditor = "content-editor";
			$('#html-editor').prop('name', '');
			$('#content-editor').prop('name', 'content');
			//console.log(activeEditor);
			// Set the standard Visual editor to active.
			editorTabs.tabs("option", "active", 1);
			if(disableTinymce) {
				$("#content-editor").val($("#fullscreen-editor").val());
			} else {
				$("#content-editor").html($("#fullscreen-editor").val());
			}
		} else if(activeTab === 1) {
			//console.log(activeEditor);
			// Set the active editor
			activeEditor = "html-editor";
			$('#html-editor').prop('name', 'content');
			$('#content-editor').prop('name', '');
			//console.log(activeEditor);
			// Set the standard HTML editor to active.
			editorTabs.tabs("option", "active", 0);
			//$("#html-editor").val($("#fullscreen-html-editor").val());
			cmHtmlEditor.getDoc().setValue(cmFullscreenHtmlEditor.getValue());
		}
		// Set the current title for the standard editor.
		$("#title").val($("#fullscreen-title").val());
	}
	// Deselect the bold button (for some reason it's active until you click the tinymce editor
	$("#content-editor_bold").removeClass("mceButtonActive");
	
	if(typeof(callback) === "function") {
		callback();
	}
}


$(document).ready(function() {
	// Collapse side panel
	$('#toggleSidePanel').click(function() {
		$('.profile-details').toggle();
		if($('.profile-details').is(':hidden')) {
			$('#profile-padding').css('margin-right', '0');
		} else {
			$('#profile-padding').css('margin-right', '330px');
		}
	});
	//Initialize the portlet jqueryui feature for the panels.
	$(function() {
		"use strict";
		var sortable;
		// Ensure the sortable is only loaded once.
		sortable = $('div.panel:data(sortable)');
		if(!sortable.length){
			$( ".panel" ).sortable({
				connectWith : ".panel",
				handle : $(".element-top"),
				placeholder : 'profile-placeholder',
				start : function(e, ui) {
					ui.placeholder.width(ui.item.width());
					ui.placeholder.height(ui.item.height());
				},
				update : function(e, ui) {
					$(".panel").each(function() {
						if($(this).children().length === 0) {
							$(this).css('margin-bottom', 0);
						}
					});
				}
			});

			$( ".panel-column" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
				.find( ".element-top" )
					.addClass( "ui-widget-header ui-corner-all" )
					.prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
					.end()
				.find( ".element-body" );

			$( ".element-top .ui-icon" ).click(function() {
				var elementTop = $(this).parents(".element-top"), elemIcon = $(this).parents(".element-top").children(".ui-icon");
				$( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
				$( this ).parents( ".panel-column:first" ).find( ".element-body" ).toggle();
				// Show/hide the bottom border of the element-top div when toggling visibility.
				if(elemIcon.hasClass("ui-icon-minusthick")) {
					//console.log("plus");
					elementTop.css({
						"border-bottom" : "1px solid #C0C0C0"
					})
				} else {
					//console.log("minus");
					elementTop.css({
						"border-bottom" : "none"
					})
				}
			});
		}
	});
	// Adds placeholder support for unsupporting browswers.
	$('input[placeholder], textarea[placeholder]').placeholder();
	
	// Weight slider
	// Article weight slider
	 $("#weight-slider").slider({
		range: "max",
		min: -50,
		max: 50,
		value: 1,
		slide: function(event, ui) {
			$("#weight").val( ui.value );
		}
	});
	
	// If the value for the article-weight input has not been set - set it else set the slider to match the weight.
	if($("#weight").length !== 0) {
		if($("#weight").val() === "") {
			$("#weight").val( $("#weight-slider").slider("value") );
		} else {
			$("#weight-slider").slider("option", "value", $("#weight").val());
		}
		$("#weight").change(function() {
			$("#weight-slider").slider("option", "value", $("#weight").val());
		});
	}
	
	/***********************************************/
	/****CONTENT EDITOR TABS/FULLSCREEN BEHAVIOR****/
	/***********************************************/
	if($("#content-editor-selection").length !== 0) {
		// Visual/HTML tabs for content editor.
		editorTabs = $("#content-editor-selection").tabs({
			active         : 1,
			beforeActivate : function(event, ui) {
				var activeTab
				  , htmlContent
				  , selected;
				// Set the active tab for the editor.
				activeTab = editorTabs.tabs("option", "active");
				
				// Visual Editor = 0 / HTML Editor = 1
				if(activeTab === 0) {
					// Set the active editor
					activeEditor = "content-editor";
					$('#html-editor').prop('name', '');
					$('#content-editor').prop('name', 'content');
					// Get the current HTML content from the editor.
					//htmlContent = $("#html-editor").val();
					htmlContent = cmHtmlEditor.getValue();
					// Replace all tabs with 4 spaces to coincide with the PHP tidy function on the server which replaces 4 spaces with tabs
					// for HTML formatting and the use of tabbing in the HTML editor.
					//htmlContent = htmlContent.replace(/\t/g, '');
					// Set the current HTML content into the WYSIWYG editor and make it the active editor by assigning it the content name.
					if(disableTinymce) {
						$("#content-editor").val(htmlContent).prop("name", "content");
					} else {
						$("#content-editor").html(htmlContent).prop("name", "content");
					}
					// Remove the name property from the HTML editor.
					$("#html-editor").removeProp("name");
				} else if(activeTab === 1) {
					// Set the active editor
					activeEditor = "html-editor";
					$('#html-editor').prop('name', 'content');
					$('#content-editor').prop('name', '');
					
					if(disableTinymce) {
						htmlContent = $("#content-editor").val();
					} else {
						htmlContent = $("#content-editor").html();
					}
					$("#html-editor").prop("name", "content");
					$("#content-editor").removeProp("name");
					
					if(disableTinymce) {
						htmlContent = _.unescape(htmlContent);
					}

					$.ajax({
						type: "POST",
						url: HTTP_ADMIN + "articles",
						datatype: "json",
						data: "adminRequest=" + encodeURIComponent(JSON.stringify({
							type            : "article",
							action          : "tidy-article-content",
							content         : htmlContent,
							tinymceDisabled : disableTinymce,
							csrfToken       : $("#csrf-token").val()
						})),
						success: function(data, textStatus, jqXHR) {
							// Trim the trailing whitespace from the data.
							var response = $.trim(data);
							//$("#html-editor").val(htmlspecialchars_decode(response, "ENT_QUOTES"));
							if(disableTinymce) {
								cmHtmlEditor.getDoc().setValue(_.unescape(response.replace(/&#039;/g, "'")));
							} else {
								cmHtmlEditor.getDoc().setValue(_.unescape(response.replace(/&#039;/g, "'")));
							}
						}
					});
				}
			}
		});

		// Fullscreen visual/HTML tabs for article editor.
		fullscreenEditorTabs = $("#fullscreen-editor-selection").tabs({
			// HTML Editor = 1 / Visual Editor = 0
			activate : function(event, ui) {
				var activeTab, editorId = tinyMCE.activeEditor.editorId, htmlContent, response;
				// Get the currently active tab.
				activeTab = fullscreenEditorTabs.tabs('option', 'active');
				if(activeTab === 1) {
					//console.log(activeTab);
					//console.log(activeEditor);
					if(activeEditor === 'html-editor') {
						htmlContent = cmHtmlEditor.getValue();
					} else {
						if(disableTinymce) {
							htmlContent = $("#" + activeEditor).val();
						} else {
							htmlContent = $("#" + activeEditor).html();
						}
					}

					// Set the active editor
					activeEditor = "fullscreen-html-editor";
					
					// Get the content from the Tinymce Visual editor, tidy it on the server, and send it back to the HTML fullscreen editor.
					$("#fullscreen-html-menu > a").show();
					
					$.ajax({
						type: "POST",
						url: HTTP_ADMIN + "articles",
						datatype: "json",
						data: "adminRequest=" + encodeURIComponent(JSON.stringify({
							type      : "article",
							action    : "tidy-article-content",
							content   : htmlContent,
							csrfToken : $("#csrf-token").val()
						})),
						success: function(data, textStatus, jqXHR) {
							var response = $.trim(data);
							if(disableTinymce) {
								cmFullscreenHtmlEditor.getDoc().setValue(_.unescape(response.replace(/&#039;/g, "'")));
							} else {
								cmFullscreenHtmlEditor.getDoc().setValue(_.unescape(response.replace(/&#039;/g, "'")));
							}
							/*
							$("#fullscreen-html-editor").val(htmlspecialchars_decode(response, "ENT_QUOTES"));
							// Make textarea elastic.
							$("#fullscreen-html-editor").elastic();
							*/
						}
					});
				} else if(activeTab === 0) {
					//console.log(activeTab);
					// Set the active editor
					activeEditor = "fullscreen-editor";
					
					// Get the content from the fullscreen HTML editor and send it to the Tinymce Visual editor.
					$("#fullscreen-html-menu > a").hide();
					//htmlContent = $("#fullscreen-html-editor").val();
					htmlContent = cmFullscreenHtmlEditor.getValue();
					// Replace all tabs with 4 spaces to coincide with the PHP tidy function on the server which replaces 4 spaces with tabs
					// for HTML formatting and the use of tabbing in the HTML editor.
					//htmlContent = htmlContent.replace(/\t/g, '');
					
					// Set the fullscreen editor's content.
					/*
					window.setTimeout(function() {
						$("#fullscreen-editor").html(htmlContent);
					}, 100);
					* */
					if(disableTinymce) {
						$("#fullscreen-editor").val(htmlContent).prop("name", "content");
					} else {
						$("#fullscreen-editor").html(htmlContent).prop("name", "content");
					}
				}
			}
		});
		
		if($("#fullscreen-wrapper").length !== 0) {
			// Resize full screen editor clicks.
			$(document).on("keypress", function(e) {
				if($("#fullscreen-wrapper").is(":visible")) {
					var fullscreenWidth = $("#fullscreen-container").width();
					if(e.altKey && e.which == 61) {
						// Alt and equals to increase fullscreen size.
						$("#fullscreen-container").width(fullscreenWidth + 50);
					} else if(e.altKey && e.which == 45) {
						// Alt and minus to reduce fullscreen size.
						$("#fullscreen-container").width(fullscreenWidth - 50);
					} else if(e.altKey && e.which == 48) {
						// ALT 0 Key to return to normail fullscreen size.
						$("#fullscreen-container").css("width", "");
					}
				}
			});
		}

		// HTML editor button hover effect - trying to get away from :hover.
		$(".html-editor-buttons button").hover(function() {
			$(this).css({
				"border": "1px solid #BBBBBB",
				"cursor": "pointer",
				"background-color": "#E4E4E4"
			});
		}, function() {
			$(this).css({
				"border": "1px solid #CCCCCC",
				"background-color": "#F0F0F0"
			});
		});

		// HTML editor button clicks.
		$(".html-editor-bold").click(function(e) {
			e.preventDefault();
			var cursorPos, editor;
			if($(this).parent().hasClass("insert-html")) {
				editor = document.getElementById("html-editor");
				cursorPos = getInputSelection(editor);
				//$("#html-editor").insertAtCaret("<strong></strong>");
				//$("#html-editor").selectRange(cursorPos.start + 8, cursorPos.start + 8);
				cmHtmlEditor.replaceSelection("<strong></strong>");
			}
		});
		$(".html-editor-italic").click(function(e) {
			e.preventDefault();
			var cursorPos, editor;
			if($(this).parent().hasClass("insert-html")) {
				editor = document.getElementById("html-editor");
				cursorPos = getInputSelection(editor);
				//$("#html-editor").insertAtCaret("<em></em>");
				//$("#html-editor").selectRange(cursorPos.start + 4, cursorPos.start + 4);
				cmHtmlEditor.replaceSelection("<em></em>");
			}
		});
		$(".html-editor-link").click(function(e) {
			e.preventDefault();
			var cursorPos, editor;
			if($(this).parent().hasClass("insert-html")) {
				editor = document.getElementById("html-editor")
				cursorPos = getInputSelection(editor);
				//$("#html-editor").insertAtCaret('<a href="" title=""></a>');
				//$("#html-editor").selectRange(cursorPos.start + 9, cursorPos.start + 9);
				cmHtmlEditor.replaceSelection('<a href="" title=""></a>');
			}
		});
		$(".html-editor-bquote").click(function(e) {
			e.preventDefault();
			var cursorPos, editor;
			if($(this).parent().hasClass("insert-html")) {
				editor = document.getElementById("html-editor");
				cursorPos = getInputSelection(editor);
				//$("#html-editor").insertAtCaret("<blockquote></blockquote>");
				//$("#html-editor").selectRange(cursorPos.start + 12, cursorPos.start + 12);
				cmHtmlEditor.replaceSelection("<blockquote></blockquote>");
			}
		});
		$(".html-editor-img").click(function(e) {
			e.preventDefault();
			var cursorPos, editor;
			if($(this).parent().hasClass("insert-html")) {
				editor = document.getElementById("html-editor");
				cursorPos = getInputSelection(editor);
				//$("#html-editor").insertAtCaret('<img src="" />');
				//$("#html-editor").selectRange(cursorPos.start + 10, cursorPos.start + 10);
				cmHtmlEditor.replaceSelection('<img src="" />');
			}
		});
		$(".html-editor-ul").click(function(e) {
			e.preventDefault();
			var cursorPos, editor;
			if($(this).parent().hasClass("insert-html")) {
				editor = document.getElementById("html-editor");
				cursorPos = getInputSelection(editor);
				//$("#html-editor").insertAtCaret("<ul></ul>");
				//$("#html-editor").selectRange(cursorPos.start + 4, cursorPos.start + 4);
				cmHtmlEditor.replaceSelection("<ul></ul>");
			}
		});
		$(".html-editor-ol").click(function(e) {
			e.preventDefault();
			var cursorPos, editor;
			if($(this).parent().hasClass("insert-html")) {
				editor = document.getElementById("html-editor");
				cursorPos = getInputSelection(editor);
				//$("#html-editor").insertAtCaret("<ol></ol>");
				//$("#html-editor").selectRange(cursorPos.start + 4, cursorPos.start + 4);
				cmHtmlEditor.replaceSelection("<ol></ol>");
			}
		});
		$(".html-editor-li").click(function(e) {
			e.preventDefault();
			var cursorPos, editor;
			if($(this).parent().hasClass("insert-html")) {
				editor = document.getElementById("html-editor");
				cursorPos = getInputSelection(editor);
				//$("#html-editor").insertAtCaret("<li></li>");
				//$("#html-editor").selectRange(cursorPos.start + 4, cursorPos.start + 4);
				cmHtmlEditor.replaceSelection("<li></li>");
			}
		});
		$(".html-editor-code").click(function(e) {
			e.preventDefault();
			var cursorPos, editor;
			if($(this).parent().hasClass("insert-html")) {
				editor = document.getElementById("html-editor");
				cursorPos = getInputSelection(editor);
				//$("#html-editor").insertAtCaret("<code></code>");
				//$("#html-editor").selectRange(cursorPos.start + 6, cursorPos.start + 6);
				cmHtmlEditor.replaceSelection("<code></code>");
			}
		});
		$(".html-editor-del").click(function(e) {
			e.preventDefault();
			var cursorPos, editor;
			if($(this).parent().hasClass("insert-html")) {
				editor = document.getElementById("html-editor");
				cursorPos = getInputSelection(editor);
				//$("#html-editor").insertAtCaret("<del></del>");
				//$("#html-editor").selectRange(cursorPos.start + 5, cursorPos.start + 5);
				cmHtmlEditor.replaceSelection("<del></del>");
			}
		});
		$(".html-editor-ins").click(function(e) {
			e.preventDefault();
			var cursorPos, editor;
			if($(this).parent().hasClass("insert-html")) {
				editor = document.getElementById("html-editor");
				cursorPos = getInputSelection(editor);
				//$("#html-editor").insertAtCaret("<ins></ins>");
				//$("#html-editor").selectRange(cursorPos.start + 5, cursorPos.start + 5);
				cmHtmlEditor.replaceSelection("<ins></ins>");
			}
		});
		$(".html-editor-fullscreen").click(function(e) {
			e.preventDefault();
			var currentContent
			//currentContent = $("#html-editor").val();
			currentContent = cmHtmlEditor.getValue();
			//console.log(currentContent);
			// Set the fullscreen editor value to the value in the standard HTML editor.
			openEditorFullscreen(function() {
				//$("#fullscreen-html-editor").val(currentContent);
				// Set the fullscreen tab as the active tab.
				fullscreenEditorTabs.tabs("option", "active", 1);
				activeEditor = 'fullscreen-html-editor';
				cmFullscreenHtmlEditor.getDoc().setValue(currentContent);
			});
		});

		$(".close-fullscreen-html-editor").click(function(e) {
			closeEditorFullscreen(function() {
				currentContent = cmFullscreenHtmlEditor.getValue();
				activeEditor = 'html-editor';
				cmHtmlEditor.getDoc().setValue(currentContent);
			});
		});

		// Focus on the tinymce article editor (it deafults to the summary editor).
		if($("#content-editor").length !== 0) {
			$("#title").focus();
		}
	}
	
	// Add media attachment modal window
	if($("#add-media").length !== 0) {
		$("#add-media-window").find("script").remove();
		$("#add-media-window").dialog({
			autoOpen: false,
			width: 800,
			height: 800,
			modal: true,
			dialogClass: 'add-media-window',
			open: function() {
				hideMenus();
				$(".ui-widget-overlay").bind("click", function(){
					$("#add-media-window").dialog("close");
				});
				$("#add-media-window").parent().hide();
				$("#media-window-loader").show();
				
				window.setTimeout(function() {
					$("#upload-comp > iframe").prop("src", HTTP_ADMIN + "upload?attach=true").prop("height", "700px").prop("width", "100%").prop("style", "display: block;");
					$("#media-lib > iframe").prop("src", HTTP_ADMIN + "media?attach=true").prop("height", "700px").prop("width", "100%").prop("style", "display: block;");
				}, 1000);
				window.setTimeout(function() {
					$("#media-window-loader").hide();
					$("#add-media-window").parent().show();
				}, 1500);
			},
			close : function() {
				// If not in fullscreen show the menus.
				if(!$("#fullscreen-wrapper").is(":visible")) {
					showMenus();
				}
			}
		}).tabs();
		// Open the window
		$("#add-media").click(function() {
			$("#add-media-window").dialog("open");
		});
	}
	
	// Profile image plupload behavior
	// Image uploader
	if($("#upload-img").length !== 0) {
		var lastUploadedImg; // Keep track of the last image uploaded. Will delete it on following uploads.

		var uploader = new plupload.Uploader({
			runtimes : 'html5,flash,silverlight,html4',
			browse_button : 'upload-img',
			url : pluploadUrl,
			flash_swf_url : HTTP_ADMIN_DIR + 'view/js/plupload/js/Moxie.swf',
			silverlight_xap_url : HTTP_ADMIN_DIR + 'view/js/plupload/js/Moxie.xap',
			filters : [
				{title : "Image files", extensions : "jpg,gif,png"}
			],

			init: {
				PostInit: function() {
					$("#upload-img-info").html("");
				},
		 
				FilesAdded: function(up, files) {
					
					adminReq = {
						type       : pluploadType,
						action     : pluploadAction,
						width      : $("#img-width").val(),
						height     : $("#img-height").val(),
						lastUpload : lastUploadedImg
					}
					
					if($("#id").length !== 0) {
						adminReq.id = $("#id").val();
					}
					
					adminReq = JSON.stringify(adminReq);
					//console.log(adminReq);
					// Set the URL of the controller and build the adminReqObj for parsing by the backend.
					up.settings.url = pluploadController + '&adminRequest=' + adminReq;
					$.each(files, function(i, file) {
						$('#upload-img-info').append(
							'<div id="' + file.id + '">' +
								file.name + ' (' + plupload.formatSize(file.size) + ') <strong></strong>' +
							'</div>');
					});
					
					uploader.start();

					up.refresh(); // Reposition Flash/Silverlight
				},
		 
				UploadProgress: function(up, file) {
					$('#' + file.id + " strong").html(file.percent + "%");
				},
		 
				Error: function(up, err) {
					$('#upload-img-info').append(
						'<div class="error">' +
							'Error: ' + err.message +
						'</div>'
					);

					up.refresh(); // Reposition Flash/Silverlight
				},
				
				FileUploaded: function(up, file, info) {
					var response = JSON.parse(info.response);
					
					// Show 100% completion
					$("#" + file.id + " strong").html("100%");
					
					window.setTimeout(function() {
						$("#" + file.id).fadeOut(5000, function() {
							$(this).remove();
						});
					}, 15000);
					
					// Set the URL for the last uploaded image. Will be sent to the server on following uploads for deletion.
					if(typeof(response.imageURL) !== "undefined") {
						// Update article image
						window.setTimeout(function() {
							$("#profile-image").html(
								'<a href="' + response.imageURL + '">' +
									'<img class="profile-image" src="' + response.imageURL + '" alt="Uploaded Article Image" target="_blank" />' +
								'</a>'
							);
						}, 10);
					}
					
					$("#img").val(response.fileName);
					$("#img-path").val(response.imagePath);
					
					lastUploadedImg = response.imagePath;
					// Clear out the width and height
					// Enable delete image button
					$("#delete-img").button("enable");
					
					//console.log(file);
				}
			}
		});
		
		uploader.init();
	}
	// Delete link image
	if($("#delete-img").length !== 0) {

		// Disable image delete if no image present.
		if($("#profile-image").children().length === 0) {
			$("#delete-img").button("disable");
		}

		// Delete image button clicks
		$("#delete-img").click(function(e) {
			e.preventDefault();
			$.ajax({
				type     : "POST",
				url      : deleteImgUrl,
				datatype : "json",
				data     : "adminRequest=" + encodeURIComponent(JSON.stringify({
					type               : pluploadType,
					action             : "delete-image",
					id                 : $("#id").val(),
					imagePath          : $("#img-path").val(),
					csrfToken          : $("#csrf-token").val()
				})),
				success: function(data, textStatus, jqXHR) {
					var response = JSON.parse(data);
					if(typeof(response.success) !== "undefined" && response.success) {
						$("#image").val("");
						$("#image-path").val("");
						$("#image-alt").val("");
						$("#profile-image").text("No Image Uploaded");
						$("#delete-img").button("disable");
					}
				}
			});
		});
	}
});
