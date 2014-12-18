$(document).ready(function() {
	var noForumError = "No sections or forums were selected.\n\nPlease select a section or forum and try again."
	  , x;
	
	// Build the forum structure for the forum overview.
	if(typeof(forumList) !== "undefined") {
		//console.log(forumList);
		buildForum(forumList, false, function() {
			var previousParent
			  , currentParent;
			
			$("#forum-sortable").nestedSortable({
				handle           : ".dd-handle",
				items            : "li.forum-node",
				toleranceElement : "> div",
				protectRoot      : true,
				maxLevels        : 3,
				placeholder      : 'placeholder',
				start            : function(event, ui) {
					if($(ui.item).hasClass("root")) {
						previousParent = $(ui.item).prop("id");
					} else {
						previousParent = $(ui.item).parents(".root").prop("id");
					}
					// Set the placeholder width and height to match that of the dragged forum/section.
					ui.placeholder.height(ui.item.height());
					ui.placeholder.width(ui.item.width() - 25);
					ui.placeholder.css("margin-left", ui.item.css("margin-left"));
				},
				update           : function(event, ui) {
					// Send updated hierarchy to the server for ordering.
					var forumHierarchy = $( "#forum-sortable" ).nestedSortable("toHierarchy")
					  , currentParent
					  , currentId
					  , id;
					if($(ui.item).hasClass("root")) {
						currentParent = $(ui.item).prop("id");
					} else {
						currentParent = $(ui.item).parents(".root").prop("id");
					}
					currentId = currentParent.replace("forum-", "");
					console.log(forumHierarchy);
					// Remove placeholder if it exists
					if($("#forum-" + currentId).has(".add-forum-holder").length) {
						console.log("Removing Placeholder");
						$("#forum-" + currentId + " > #forum-" + currentId + "-children > .forum-placeholder").remove();
					}
					$.ajax({
						url: HTTP_ADMIN + 'forums',
						type: 'POST',
						datatype: 'json',
						data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
							type           : "forum",
							action         : "reorder",
							forumHierarchy : forumHierarchy,
							csrfToken      : $('#csrf-token').val()
						})),
						success: function(data, textStatus, jqXHR) {
							data = JSON.parse(data);
							console.log(data);
						}
					});
					if($("#" + previousParent + " > ol").children().length === 0) {
						prevId = previousParent.replace("forum-", "");
						$("#forum-" + prevId + "-children").append(
							'<li class="forum-child forum-depth-1 forum-placeholder">' +
								'<div class="child-node clearfix add-forum-holder">' +
									'<span>No forums exist for this section. </span>' +
									'<a href="' + HTTP_ADMIN + 'forums/new?sectionId=' + prevId + '">Add Forum</a>' +
								'</div>' +
							'</li>'
						);
					}
				},
				stop : function() {
					$("#forum-sortable").css({ overflow : false });
				}
			});
			
			$(".dd-handle").each(function() {
				$(this).mousedown(function() {
					$("#forum-sortable").css({overflow : "auto"});
				});
			});
			// If a section does not have any forums show a row letting a user add one
			$(".root").each(function(index, value) {
				var id = $(this).prop("id").replace("forum-", "");
				if($(this).has(".child-node").length === 0) {
					$("#forum-" + id + "-children").append(
						'<li class="forum-child forum-depth-1 forum-placeholder">' +
							'<div class="child-node clearfix add-forum-holder">' +
								'<span>No forums exist for this section. </span>' +
								'<a href="' + HTTP_ADMIN + 'forums/new?sectionId=' + id + '">Add Forum</a>' +
							'</div>' +
						'</li>'
					);
				}
			});
		});
	}
	
	// Build the forum moderators for each forum
	if(typeof(forumMods) !== "undefined") {
		buildForumModerators(forumMods, function() {
			
		});
	}
	
	// Check selected forums for a new moderator
	if(typeof(checkedForums) !== "undefined") {
		for(x = 0; x < checkedForums.length; x++) {
			$("#forums-to-moderate option[value='" + checkedForums[x] + "']").prop("selected", "selected");
		}
	}
	
	// User autocomplete
	$("#user-moderator").keyup(function(e) {
		var keyCode = (e.keyCode ? e.keyCode : e.which);
		if(keyCode !== 13 && keyCode <= 90 && keyCode >= 48) {
			$.ajax({
				url: HTTP_ADMIN + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : "forum",
					action    : "user-autocomplete",
					search    : $("#user-moderator").val(),
					users     : "",
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					data = JSON.parse(data);
					console.log(data);
					$("#user-moderator").autocomplete({
						source : data.users
					});
				}
			});
		}
	});
	
	// Click Handlers
	
	// Redirect for creating a new forum
	$("#new-forum-btn").click(function(e) {
		e.preventDefault();
		window.location.href = HTTP_ADMIN + 'forums/new';
	});
	// Redirect for creating a new section
	$("#new-section-btn").click(function(e) {
		e.preventDefault();
		window.location.href = HTTP_ADMIN + 'forums/section/new';
	});
	
	// AJAX add a moderator
	$("#add-user-mod-btn").click(function(e) {
		e.preventDefault();
		var username = $("#user-moderator").val(), response;
		
		if(!username) {
			return false;
		}
		
		$.ajax({
			url: HTTP_ADMIN + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
				type      : "forum",
				action    : "get-user-id",
				username  : username,
				userId    : "",
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
			}
		}).done(function() {
			var isRoot, x;
			CMS.getCheck();
			for(x = 0; x < CMS.ids.length; x++) {
				isRoot = $("#cb-" + CMS.ids[x]).hasClass("root-check");
				if(isRoot) {
					delete CMS.ids[x];
				}
				isRoot = null;
			}
			// If there are no checked forums return false;
			if(CMS.ids.length === 0) {
				return false;
			}
			// Remove undefined array elements.
			CMS.ids = $.grep(CMS.ids, function(n) { return n; });
			// Turn into JSON string to append to the query string.
			CMS.ids = encodeURIComponent(JSON.stringify(CMS.ids));
			window.location.href = HTTP_ADMIN + "forums/moderators/new?userId=" + response.userId + "&forums=" + CMS.ids;
		});
	});
	
	$(document).on("click", "#add-role-mod-btn", function(e) {
		e.preventDefault();
		var roleId
		  , isRoot
		  , x;
		roleId = $("#role-moderator").val();
		CMS.getCheck();
		for(x = 0; x < CMS.ids.length; x++) {
			isRoot = $("#cb-" + CMS.ids[x]).hasClass("root-check");
			if(isRoot) {
				delete CMS.ids[x];
			}
			isRoot = null;
		}
		// Remove undefined array elements.
		CMS.ids = $.grep(CMS.ids, function(n) { return n; });
		// Turn into JSON string to append to the query string.
		CMS.ids = encodeURIComponent(JSON.stringify(CMS.ids));
		window.location.href = HTTP_ADMIN + "forums/moderators/new?roleId=" + roleId + "&forums=" + CMS.ids;
	});
	
	// Forum Overview clicks
	$("#toolbar-edit-forum, #action-edit-forum").click(function(e) {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noForumError);
		} else {
			CMS.submitButton("forum", "edit");
		}
	});
	$("#toolbar-edit-permissions, #action-edit-permissions").click(function(e) {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noForumError);
		} else {
			CMS.submitButton("forum", "edit-permissions");
		}
	});
	$("#toolbar-publish-forum, #action-publish-forum").click(function(e) {
		CMS.submitButton("forum", "publish");
	});
	$("#toolbar-unpublish-forum, #action-unpublish-forum").click(function(e) {
		CMS.submitButton("forum", "unpublish");
	});
	$("#toolbar-feature-forum, #action-feature-forum").click(function(e) {
		CMS.submitButton("forum", "feature");
	});
	$("#toolbar-disregard-forum, #action-disregard-forum").click(function(e) {
		CMS.submitButton("forum", "disregard");
	});
	$("#toolbar-delete-forum, #action-delete-forum").click(function(e) {
		CMS.submitButton("forum", "delete-forum");
	});
	
	// Forum profile clicks
	$("#toolbar-save-forum, #action-save-forum").click(function(e) {
		CMS.submitButton("forum", "save");
	});
	
	$("#toolbar-close-forum, #action-close-forum").click(function(e) {
		window.location.href = HTTP_ADMIN + "forums";
	});

	$("#toolbar-save-close-forum, #action-save-close-forum").click(function(e) {
		CMS.submitButton("forum", "save-close-forum");
	});
	
	$("#toolbar-save-new-forum, #action-save-new-forum").click(function(e) {
		CMS.submitButton("forum", "save-new-forum");
	});
	
	// Reported forum posts clicks
	$("#toolbar-delete-post-and-report, #action-delete-post-and-report").click(function(e) {
		CMS.submitButton("forum", "delete-post-and-report");
	});
	
	$("#toolbar-delete-report-only, #action-delete-report-only").click(function(e) {
		CMS.submitButton("forum", "delete-report-only");
	}); 
	
	// Global Forum Options clicks
	$("#submit-forum-globals").click(function(e) {
		e.preventDefault();
		$.ajax({
			url: HTTP_ADMIN + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
				type              : "forum",
				action            : "forumglobals",
				numOfTopicsToShow : $("#num-of-topics-to-show").val(),
				numOfPostsToShow  : $("#num-of-posts-to-show").val(),
				csrfToken         : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				if(typeof(response.success) !== "undefined") {
					$("#options-form > .element > .element-body").effect('highlight', '#CBC825', 1000);
				}
			}
		});
	});
	
	// Forum section profile clicks
	$("#toolbar-save-forum-section, #action-save-forum-section").click(function(e) {
		CMS.submitButton("forum", "save-section");
	});
	$("#toolbar-close-forum-section, #action-close-forum-section").click(function(e) {
		window.location.href = HTTP_ADMIN + "forums";
	});
	
	// Forum Moderator Profile clicks
	$("#toolbar-save-moderator, #action-save-moderator").click(function(e) {
		CMS.submitButton("forum", "save-moderator");
	});
	$("#toolbar-close-moderator, #action-close-moderator").click(function(e) {
		window.location.href = HTTP_ADMIN + "forums";
	});
	$("#grant-all-forum-perms").click(function() {
		$(".radio-yes > label > input").prop("checked", "checked");
	});
	$("#deny-all-forum-perms").click(function() {
		$(".radio-no > label > input").prop("checked", "checked");
	});
	// Moderator toggle button on forum overview clicks.
	$(document).on("click", ".moderator-toggle", function(e) {
		e.stopPropagation();
		var forumId = $(this).prop("id").replace("moderator-toggle-", "");
		
		if($("#moderator-wrap-" + forumId).is(":visible")) {
			$("#moderator-wrap-" + forumId).hide();
		} else {
			// Hide other open moderator drop downs
			$(".moderators-wrap").hide();
			//console.log(forumId);
			window.setTimeout(function() {
				$("#moderator-wrap-" + forumId).show();
			}, 10);
		}
	});
	$(document).on("click", ".del-user-mod", function() {
		var id = $(this).prop("id").replace("del-mod-", "");
		$.ajax({
			url: HTTP_ADMIN + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
				type      : "forum",
				action    : "delete-user-moderator",
				id        : id,
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				$("#moderator-" + id).remove();
			}
		});
	})
	$(document).on("click", ".del-role-mod", function() {
		var id = $(this).prop("id").replace("del-mod-", "");
		$.ajax({
			url: HTTP_ADMIN + 'forums',
			type: 'POST',
			datatype: 'json',
			data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
				type      : "forum",
				action    : "delete-role-moderator",
				id        : id,
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				$("#moderator-" + id).remove();
			}
		});
	});
	
	$(document).mouseup(function(e) {
		var moderatorPanelsVisible = $(".forum-moderators:visible").length;
		// Hide forum moderators
		//console.log($(".forum-moderators").has(e.target).length);
		//console.log(moderatorPanelsVisible);
		if(($(".forum-moderators").has(e.target).length === 0) && moderatorPanelsVisible !== 0) {
			// Micro timeout to override the main-actions click function from firing.
			window.setTimeout(function() {
				$(".moderators-wrap").hide();
			}, 10);
		}
		
	});
	// Section and forum profile clicks.
	
	// Check the forum/section permission when its table cell is clicked. All three click handlers are required for proper function.
	$(document).on("click", ".forum-perm", function(e) {
		e.stopPropagation();
		// If the toggle itself is clicked then skip the click handler.
		if($(e.target).hasClass('forum-perm-toggle')) {
			return;
		}
		// Toggle the fourm permission.
		var permission = "#" + $(this).children("label").children(".forum-perm-toggle").prop("id");
		if($(permission).is(":checked")) {
			$(permission).prop("checked", false);
		} else {
			$(permission).prop("checked", true);
		}
	});
	
	$('.forum-perm').on("click", ".forum-perm-label", function(e) {
		if($(e.target).hasClass('forum-perm-toggle')) {
			return;
		}
		var permission = "#" + $(this).children(".forum-perm-toggle").prop("id");
		if($(permission).is(":checked")) {
			$(permission).prop("checked", false);
		} else {
			$(permission).prop("checked", true);
		}
	});
	
	// Change forum type clicks
	$("#type-standard").click(function(e) {
		$("#postable-settings").show();
	});
	$("#type-subcontainer").click(function(e) {
		$("#postable-settings").hide();
	});
	$("#type-redirect").click(function(e) {
		$("#postable-settings").hide();
	});
	
	if($("#type-standard").is(":checked")) {
		$("#postable-settings").show();
	} else if($("#type-subcontainer").is(":checked")) {
		$("#postable-settings").hide();
	} else if($("#type-redirect").is(":checked")) {
		$("#postable-settings").hide();
	}
	
	// Forum role permissions clicks
	$(".check-all-forum-perms").click(function(e) {
		var perm = $(this).prop("id").replace("check-", "");
		if($(this).is(":checked")) {
			$(".forum-perm-" + perm).prop("checked", true);
		} else {
			$(".forum-perm-" + perm).prop("checked", false);
		}
	});
});
function buildForum(forumArr, isChild, callback) {
	for(forum in forumArr) {
		//console.log(forumArr[forum].id);
		//console.log(forumArr.id);
		//console.log(forumArr);
		if(!isChild && forumArr[forum].id) {
			$("#forum-sortable").append(
				'<li id="forum-' + forumArr[forum].id + '" class="root forum-node">' + 
					'<div class="root-node clearfix">' +
						'<div class="forum-desc">' +
							'<div class="show-handle expand"></div>' +
							'<div class="dd-handle root-handle sprites"></div>' +
							'<div class="forum-title">' + 
								'<a href="' + HTTP_ADMIN + 'forums/section/' + forumArr[forum].id + '">' + forumArr[forum].title + '</a>' +
							'</div>' +
						'</div>' +
						'<div class="forum-actions">' +
							'<span id="featured-' + forumArr[forum].id + '" class="icon-20-spacing fltrght forum-featured"></span>' +
							'<span id="published-' + forumArr[forum].id + '" class="icon-20-spacing fltrght forum-published"></span>' +
							'<span class="forum-checkbox">' +
								'<input id="cb-' + forumArr[forum].id + '" type="checkbox" class="overview-check root-check" name="forumCheck[]" value="' + forumArr[forum].id + '" />' +
							'</span>' +
						'</div>' +
					'</div>' +
				'</li>'
			);
			setForumIcons(forumArr[forum]);
			if(typeof(forumArr[forum].children) === "object") {
				$("#forum-" + forumArr[forum].id).append(
					'<ol id="forum-' + forumArr[forum].id + '-children">' +
					
					'</ol>'
				);
				for(child in forumArr[forum].children) {
					//console.log(forumArr[forum].children);
					buildForum(forumArr[forum].children, true);
				}
			}
		} else {
			//console.log(forumArr);
			if(forumArr[forum].id) {
				$("#forum-" + forumArr[forum].ancestor + "-children").append(
					'<li id="forum-' + forumArr[forum].id + '" class="forum-child forum-node forum-depth-1">' +
						'<div class="child-node clearfix">' +
							'<div class="forum-desc">' +
								'<div class="dd-handle sprites"></div>' +
								'<div class="forum-title">' + 
									'<a href="' + HTTP_ADMIN + 'forums/' + forumArr[forum].id + '">' + forumArr[forum].title + '</a>' +
								'</div>' +
							'</div>' +
							'<div id="moderator-toggle-' + forumArr[forum].id + '" class="moderator-toggle" style="display: none;">' +
								'<span>Moderators</span>' +
								'<span class="moderators-dd"></span>' +
							'</div>' +
							'<div id="moderator-wrap-' + forumArr[forum].id + '" class="moderators-wrap" style="display: none;">' +
								'<div id="moderators-' + forumArr[forum].id + '" class="forum-moderators">' +
									'<h6 id="user-head-' + forumArr[forum].id + '">Users</h6>' +
									'<ul id="user-moderators-' + forumArr[forum].id + '" class="user-moderators">' +
									
									'</ul>' +
									'<h6 id="role-head-' + forumArr[forum].id + '">Roles</h6>' +
									'<ul id="role-moderators-' + forumArr[forum].id + '" class="role-moderators">' +
									
									'</ul>' +
								'</div>' +
							'</div>' +
							'<div class="forum-actions">' +
								'<span id="featured-' + forumArr[forum].id + '" class="icon-20-spacing forum-featured"></span>' +
								'<span id="published-' + forumArr[forum].id + '" class="icon-20-spacing forum-published"></span>' +
								'<span class="forum-checkbox">' +
									'<input id="cb-' + forumArr[forum].id + '" type="checkbox" class="overview-check" name="forumCheck[]" value="' + forumArr[forum].id + '" />' +
								'</span>' +
							'</div>' +
						'</div>' +
					'</li>'
				);
			}
			setForumIcons(forumArr[forum]);
			if(typeof(forumArr[forum].children) === "object") {
				$("#forum-" + forumArr[forum].id).append(
					'<ol id="forum-' + forumArr[forum].id + '-children">' +
					
					'</ol>'
				);
				for(child in forumArr[forum].children) {
					//console.log(forumArr[forum].children);
					//console.log(typeof(forumArr[forum].children));
					if(typeof(forumArr[forum].children) === "object") {
						buildForum(forumArr[forum].children, true);
					}
				}
			}
		}
	}
	if(typeof(callback) === 'function') {
		callback();
	}
}

function buildForumModerators(forumModerators, callback) {
	//console.log(forumModerators);
	var id;
	for(moderatorType in forumModerators) {
		//console.log(moderatorType);
		for(forumId in forumModerators[moderatorType]) {
			$("#moderator-toggle-" + forumId).show();
			if(moderatorType === 'users') {
				for(userModerators in forumModerators[moderatorType][forumId]) {
					id = userModerators;
					//console.log(userModerators);
					for(userId in forumModerators[moderatorType][forumId][userModerators]) {
						//console.log(userId);
						$("#user-moderators-" + forumId).append(
							'<li id="moderator-' + id + '">' +
								'<a href="' + HTTP_ADMIN + 'forums/moderators/' + id + '">' + 
									forumModerators[moderatorType][forumId][userModerators][userId] + 
								'</a>' +
								'<span id="del-mod-' + id + '" class="ui-icon ui-icon-trash fltrght del-user-mod"></span>' +
							'</li>'
						);
					}
				}
			} else if(moderatorType === 'roles') {
				for(roleModerators in forumModerators[moderatorType][forumId]) {
					id = roleModerators
					//console.log(roleModerators);
					for(roleId in forumModerators[moderatorType][forumId][roleModerators]) {
						$("#role-moderators-" + forumId).append(
							'<li id="moderator-' + id + '">' +
								'<a href="' + HTTP_ADMIN + 'forums/moderators/' + id + '">' +
									forumModerators[moderatorType][forumId][roleModerators][roleId] +
								'</a>' +
								'<span id="del-mod-' + id + '" class="ui-icon ui-icon-trash fltrght del-role-mod"></span>' +
							'</li>'
						);
					}
				}
			}
			// Hide empty user moderator list header
			if($("#user-moderators-" + forumId + " li").length === 0) {
				$("#user-head-" + forumId).hide();
			} else {
				$("#user-head-" + forumId).show();
			}
			// Hide empty role moderator list header
			if($("#role-moderators-" + forumId + " li").length === 0) {
				$("#role-head-" + forumId).hide();
			} else {
				$("#role-head-" + forumId).show();
			}
		}
	}
	callback();
}

function setForumIcons(forumArr) {
	// Set the published icon.
	if(forumArr.published == 1) {
		$("#published-" + forumArr.id).addClass("icon-20-check");
	} else {
		$("#published-" + forumArr.id).addClass("icon-20-disabled");
	}
	// Set the featured icon.
	if(forumArr.featured == 1) {
		$("#featured-" + forumArr.id).addClass("icon-20-star");
	} else {
		$("#featured-" + forumArr.id).addClass("icon-20-gray-disabled");
	}
}

// Check category when clicking anywhere in box.
$(document).on("click", ".root-node", function(e) {
	e.stopPropagation();
	var sectionId = $(this).parent().prop("id").replace("forum-", "");
	$("#cb-" + sectionId).trigger("click");
});


// Check forum when clicking anywhere in box.
$(document).on("click", ".child-node", function(e) {
	e.stopPropagation();
	var forumId = $(this).parent().prop("id").replace("forum-", "");
	// Don't check the forum/section if clicking moderator options, for some reason the even bubbles down even after stopPropagation, don't have time to fuck with it.
	if(!$(e.target).parents(".moderators-wrap").length) {
		$("#cb-" + forumId).trigger("click");
	}
});

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
