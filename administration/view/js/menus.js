$(document).ready(function() {
	
	var noMenuError = "No menus were selected. \n\nPlease select an menu from the table below and try again."

	// Build the sortable menu list if the menusJSON variable is not undefined
	if(typeof(menus) !== "undefined") {
		buildMenus(menus, false, function() {
			var previousParent
			  , currentParent;
			
			$("#menus-sortable").nestedSortable({
				handle           : ".dd-handle",
				items            : "li.menu-node",
				toleranceElement : "> div",
				protectRoot      : true,
				maxLevels        : 10,
				placeholder      : 'placeholder',
				start            : function(event, ui) {
					if($(ui.item).hasClass("root")) {
						previousParent = $(ui.item).prop("id");
					} else {
						previousParent = $(ui.item).parents(".root").prop("id");
					}
					// Set the placeholder width and height to match that of the dragged menu.
					// Use ui.item.height() ui.item.width() if outerHeight() doesn't work anymore
					ui.placeholder.height(ui.item.height());
					ui.placeholder.width(ui.item.width() - 35);
					ui.placeholder.css("margin-left", ui.item.css("margin-left"));
				},
				update           : function(event, ui) {
					// Send updated hierarchy to the server for ordering.
					var menuHierarchy = $("#menus-sortable").nestedSortable("toHierarchy")
					  , currentParent
					  , currentId
					  , id;
					
					if($(ui.item).hasClass("root")) {
						currentParent = $(ui.item).prop("id");
					} else {
						currentParent = $(ui.item).parents(".root").prop("id");
					}
					currentId = currentParent.replace("menu-", "");
					//console.log(menuHierarchy);
					// Remove placeholder if it exists
					if($("#menu-" + currentId).has(".add-node-holder").length) {
						console.log("Removing Placeholder");
						$("#menu-" + currentId + " > #menu-" + currentId + "-children > .node-placeholder").remove();
					}
					$.ajax({
						url: HTTP_ADMIN + 'menus',
						type: 'POST',
						datatype: 'json',
						data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
							type              : "menu",
							action            : "reorder",
							menuHierarchy : menuHierarchy,
							csrfToken         : $('#csrf-token').val()
						})),
						success: function(data, textStatus, jqXHR) {
							var response = JSON.parse(data);
							//console.log(data);
						}
					});
					if($("#" + previousParent + " > ol").children().length === 0) {
						prevId = previousParent.replace("menu-", "");
						$("#menu-" + prevId + "-children").append(
							'<li class="node-child node-depth-1 node-placeholder">' +
								'<div class="child-node clearfix add-node-holder">' +
									'<span>No submenus exist for this menu. </span>' +
									'<a href="' + HTTP_ADMIN + 'menus/new?parent=' + prevId + '">Add Submenu</a>' +
								'</div>' +
							'</li>'
						);
					}
				}
			});
			// If a menu does not have any submenus show a row letting a user add one
			$(".root").each(function(index, value) {
				var id = $(this).prop("id").replace("menu-", "");
				if($(this).has(".child-node").length === 0) {
					$("#menu-" + id + "-children").append(
						'<li class="node-child node-depth-1 node-placeholder">' +
							'<div class="child-node clearfix add-node-holder">' +
								'<span>No submenus exist for this menu. </span>' +
								'<a href="' + HTTP_ADMIN + 'menus/new?parent=' + id + '">Add Submenu</a>' +
							'</div>' +
						'</li>'
					);
				}
			});
		});
	}

	//Initialize the date/timepicker
	if($("#date-created").length !== 0) {
		$("#date-created").datetimepicker({
			showOn: "both",
			buttonImage: HTTP_IMAGE + "icons/admin/calendar_icon2.png",
			buttonImageOnly: true,
			ampm: true,
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm TT"
		});
	}
	
	// Set plupload info for the profile image
	if($("#profile-image").length !== 0) {
		pluploadUrl = HTTP_ADMIN + 'menus?adminRequest={"type":"link","action":"upload-image"}&csrfToken=' + $('#csrf-token').val();
		pluploadType = "menu";
		pluploadAction = "upload-image";
		pluploadController = HTTP_ADMIN + "menus?csrfToken=" + $('#csrf-token').val();
		deleteImgUrl = HTTP_ADMIN + "menus";
	}
	
	// Edit menu clicks.
	$("#toolbar-edit, #action-edit-menu").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noMenuError);
		} else {
			CMS.submitButton("menu", "edit");
		}
	});
	// Publish menu clicks.
	$("#toolbar-publish-menu, #action-publish-menu").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noMenuError);
		} else {
			CMS.submitButton("menu", "publish");
		}
	});
	// Unpublish menu clicks.
	$("#toolbar-unpublish-menu, #action-unpublish-menu").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noMenuError);
		} else {
			CMS.submitButton("menu", "unpublish");
		}
	});
	// Delete menu clicks.
	$("#toolbar-delete-menu, #action-delete-menu").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noMenuError);
		} else {
			CMS.submitButton("menu", "delete");
		}
	});
	// Publish menu button clicks.
	$(".quick-publish-menu").click(function() {
		$("#menu-state").val(1);
		CMS.submitButton("menu", "save-close");
	});
	// Draft menu button clicks
	$(".draft-menu-content").click(function() {
		$("#menu-state").val(2);
		CMS.submitButton("menu", "save");
	});
	// Save menu clicks
	$("#toolbar-save-menu, #action-save-menu").click(function() {
		CMS.submitButton("menu", "save");
	});
	// Save and new menu clicks
	$("#toolbar-save-new-menu, #action-save-new-menu").click(function() {
		CMS.submitButton("menu", "save-new");
	});
	// Save and close menu clicks
	$("#toolbar-save-close-menu, #action-save-close-menu").click(function() {
		selectNavItem("#menus-link", "#new-menu-link");
		CMS.submitButton("menu", "save-close");
	});
	// Close the menu editor clicks
	$("#toolbar-close-menu, #action-close-menu").click(function() {
		selectNavItem("#menus-link", "#new-menu-link");
		window.location.href = HTTP_ADMIN + "menus";
	});
	
	// Image uploader
	if($("#upload-menu-img").length !== 0) {
		var lastUploadedImg; // Keep track of the last image uploaded. Will delete it on following uploads.

		var uploader = new plupload.Uploader({
			runtimes : 'html5,flash,silverlight,browserplus,html4',
			browse_button : 'upload-menu-img',
			url : HTTP_ADMIN + 'menus?adminRequest={"type":"menu","action":"upload-image"}&csrfToken=' + $('#csrf-token').val(),
			flash_swf_url : HTTP_ADMIN_DIR + 'view/js/plupload/js/Moxie.swf',
			silverlight_xap_url : HTTP_ADMIN_DIR + 'view/js/plupload/js/Moxie.xap',
			filters : [
				{title : "Image files", extensions : "jpg,jpeg,gif,png"}
			],
			 
		 
			init: {
				PostInit: function() {
					$("#upload-img-info").html("");
				},
		 
				FilesAdded: function(up, files) {
					
					adminReq = {
						type       : "menu",
						action     : "upload-image",
						width      : $("#menu-img-width").val(),
						height     : $("#menu-img-height").val(),
						lastUpload : lastUploadedImg
					}
					
					if($("#id").length !== 0) {
						adminReq.id = $("#id").val();
					}
					
					adminReq = JSON.stringify(adminReq);
					//console.log(adminReq);
					// Set the URL of the controller and build the adminReqObj for parsing by the backend.
					up.settings.url = HTTP_ADMIN + 'menus?adminRequest=' + adminReq + '&csrfToken=' + $('#csrf-token').val(),
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
						// Update menu image
						window.setTimeout(function() {
							$("#menu-image").html(
								'<a href="' + response.imageURL + '">' +
									'<img class="profile-image" src="' + response.imageURL + '" alt="Uploaded Menu Image" target="_blank" />' +
								'</a>'
							);
						}, 10);
					}
					
					$("#menu-img").val(response.fileName);
					$("#menu-img-path").val(response.imagePath);
					
					lastUploadedImg = response.imagePath;
					// Clear out the width and height
					// Enable delete image button
					$("#delete-menu-img").button("enable");
					
					//console.log(file);
				}
			}
		});
		
		uploader.init();
	}
	
});

// Build menu sortable
function buildMenus(menus, isChild, callback) {
	for(menu in menus) {
		//console.log(menus[menu].cat_id);
		//console.log(menus.id);
		//console.log(menus);
		//console.log(menu);
		if(!isChild && menus[menu].id) {
			$("#menus-sortable").append(
				'<li id="menu-' + menus[menu].id + '" class="root menu-node">' + 
					'<div class="root-node clearfix">' +
						'<div class="node-desc">' +
							'<div class="show-handle expand"></div>' +
							'<div class="dd-handle root-handle sprites"></div>' +
							'<div class="node-title">' + 
								'<a href="' + HTTP_ADMIN + 'menus/' + menus[menu].id + '">' + menus[menu].name + '</a>' +
							'</div>' +
						'</div>' +
						'<div class="node-actions">' +
							'<span id="featured-' + menus[menu].id + '" class="icon-20-spacing fltrght menu-featured"></span>' +
							'<span id="published-' + menus[menu].id + '" class="icon-20-spacing fltrght menu-published"></span>' +
							'<span class="menu-checkbox node-checkbox">' +
								'<input id="cb-' + menus[menu].id + '" type="checkbox" class="overview-check root-check" name="menuCheck[]" value="' + menus[menu].id + '" />' +
							'</span>' +
						'</div>' +
					'</div>' +
				'</li>'
			);
			setMenuAttribs(menus[menu]);
			if(typeof(menus[menu].children) === "object") {
				$("#menu-" + menus[menu].id).append(
					'<ol id="menu-' + menus[menu].id + '-children">' +
					
					'</ol>'
				);
				for(child in menus[menu].children) {
					//console.log(menus[menu].children);
					buildMenus(menus[menu].children, true);
				}
			}
		} else {
			//console.log(menus);
			if(menus[menu].id) {
				$("#menu-" + menus[menu].ancestor + "-children").append(
					'<li id="menu-' + menus[menu].id + '" class="node-child menu-node node-depth-1">' +
						'<div class="child-node clearfix">' +
							'<div class="node-desc">' +
								'<div class="dd-handle sprites"></div>' +
								'<div class="node-title">' + 
									'<a href="' + HTTP_ADMIN + 'menus/' + menus[menu].id + '">' + menus[menu].name + '</a>' +
								'</div>' +
							'</div>' +
							'<div class="node-actions">' +
								'<span id="featured-' + menus[menu].id + '" class="icon-20-spacing menu-featured"></span>' +
								'<span id="published-' + menus[menu].id + '" class="icon-20-spacing menu-published"></span>' +
								'<span class="menu-checkbox node-checkbox">' +
									'<input id="cb-' + menus[menu].id + '" type="checkbox" class="overview-check" name="menuCheck[]" value="' + menus[menu].id + '" />' +
								'</span>' +
							'</div>' +
						'</div>' +
					'</li>'
				);
			}
			setMenuAttribs(menus[menu]);
			if(typeof(menus[menu].children) === "object") {
				$("#menu-" + menus[menu].id).append(
					'<ol id="menu-' + menus[menu].id + '-children">' +
					
					'</ol>'
				);
				for(child in menus[menu].children) {
					//console.log(menus[menu].children);
					//console.log(typeof(menus[menu].children));
					if(typeof(menus[menu].children) === "object") {
						buildMenus(menus[menu].children, true);
					}
				}
			}
		}
	}
	if(typeof(callback) === 'function') {
		callback();
	}
}

function setMenuAttribs(menu) {
	// Set the published icon.
	if(menu.published == 1) {
		$("#published-" + menu.id).addClass("icon-20-check");
	} else {
		$("#published-" + menu.id).addClass("icon-20-disabled");
	}
	// Set the featured icon.
	if(menu.featured == 1) {
		$("#featured-" + menu.id).addClass("icon-20-star");
	} else {
		$("#featured-" + menu.id).addClass("icon-20-gray-disabled");
	}
}

// Toggling show handle on root nodes.
$(document).on("click", ".show-handle", function(e) {
	e.stopPropagation();
	if($(this).hasClass("expand")) {
		$(this).parents(".root").children("ol").hide();
		$(this).removeClass("expand").addClass("collapse");
	} else {
		$(this).parents(".root").children("ol").show();
		$(this).removeClass("collapse").addClass("expand");
	}
});
