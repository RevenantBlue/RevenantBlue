$(document).ready(function() {
	
	var noPageError = "No pages were selected. \n\nPlease select an page from the table below and try again."
	  , noRevisionError = "No revisions selected, please select a revision from the table below and try again."
	
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
	
	// Edit page clicks.
	$("#toolbar-edit, #action-edit-page").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noPageError);
		} else {
			CMS.submitButton("page", "edit");
		}
	});
	// Publish page clicks.
	$("#toolbar-publish-page, #action-publish-page").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noPageError);
		} else {
			CMS.submitButton("page", "publish");
		}
	});
	// Unpublish page clicks.
	$("#toolbar-unpublish-page, #action-unpublish-page").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noPageError);
		} else {
			CMS.submitButton("page", "unpublish");
		}
	});
	// Delete page clicks.
	$("#toolbar-delete-page, #action-delete-page").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noPageError);
		} else {
			CMS.submitButton("page", "delete");
		}
	});
	// Publish page button clicks.
	$(".quick-publish-page").click(function() {
		$("#page-state").val(1);
		CMS.submitButton("page", "save-close");
	});
	// Draft page button clicks
	$(".draft-page-content").click(function() {
		$("#page-state").val(2);
		CMS.submitButton("page", "save");
	});
	// Save page clicks
	$("#toolbar-save-page, #action-save-page").click(function() {
		CMS.submitButton("page", "save");
	});
	// Save and new page clicks
	$("#toolbar-save-new-page, #action-save-new-page").click(function() {
		CMS.submitButton("page", "save-new");
	});
	// Save and close page clicks
	$("#toolbar-save-close-page, #action-save-close-page").click(function() {
		selectNavItem("#pages-link", "#new-page-link");
		CMS.submitButton("page", "save-close");
	});
	// Close the page editor clicks
	$("#toolbar-close-page, #action-close-page").click(function() {
		selectNavItem("#pages-link", "#new-page-link");
		window.location.href = HTTP_ADMIN + "pages";
	});
	
	// Page template clicks
	
	// Delete page templates
	$("#toolbar-delete-page-template, #action-delete-page-template").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noPageError);
		} else {
			CMS.submitButton("page-template", "delete");
		}
	});
	// Save page template
	$("#toolbar-save-page-template, #action-save-page-template").click(function() {
		CMS.submitButton("page-template", "save");
	});
	// Save and new page template
	$("#toolbar-save-new-page-template, #action-save-new-page-template").click(function() {
		CMS.submitButton("page-template", "save-new");
	});
	// Save and close page template
	$("#toolbar-save-close-page-template, #action-save-close-page-template").click(function() {
		CMS.submitButton("page-template", "save-close");
	});
	// Close the page template
	$("#toolbar-close-page-template, #action-close-page-template").click(function() {
		window.location.href = HTTP_ADMIN + "pages/templates";
	});
	
	// Page revisions clicks
	
	$("#toolbar-compare-revisions, #action-compare-revisions").click(function() {
		CMS.submitButton('page-revision', 'compare');
	});
	
	$("#toolbar-restore-revision, #action-restore-revision").click(function() {
		if(boxesChecked == 0) { 
			alert(noRevisionError);
		} else {
			//console.log("restoring revision");
			CMS.submitButton("page-revision", "restore"); 
		}
	});
	
	$("#toolbar-delete-revision, #action-delete-revision").click(function() {
		if(boxesChecked == 0) {
			alert(noRevisionError);
		} else {
			CMS.submitButton("page-revision", "delete");
		}
	});
	
	// Adding a page template dynamically
	$("#add-page-template").click(function(e) {
		e.preventDefault();
		$.ajax({
			url: HTTP_ADMIN + "pages",
			type: "POST",
			datatype: "json",
			data: "adminRequest=" + encodeURIComponent(JSON.stringify({
				type        : 'page',
				action      : 'add-template',
				name        : $("#template-name").val(),
				alias       : $("#template-alias").val(),
				description : $("#template-desc").val(),
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				
				if(response.description === false) {
					response.description = '';
				}
				
				$("#page-templates > tbody").append(
					'<tr id="overview-row-' + response.id + '">' +
						'<td>' +
							'<input id="cb-' + response.id + '" type="checkbox" class="overview-check" name="pageTemplateCheck[]" value="' + response.id + '" />' +
						'</td>' +
						'<td class="left">' +
							'<a href="' + HTTP_ADMIN + 'pages/templates/' + response.id + '">' +
								response.name +
							'</a>' +
						'</td>' +
						'<td>' +
							response.alias +
						'</td>' +
						'<td class="left">' +
							response.description +
						'</td>' +
						'<td>' +
							response.id +
						'</td>' +
					'</tr>'
				);
				
				$("#overview-row-" + response.id).effect('highlight', '#CBC825', 1000);
				
				$("#template-name, #template-alias, #template-desc").val("");
				
				$("#no-templates-placeholder").hide();
			}
		});
	});
	
	// Autosave page.
	if($("#content-editor").length !== 0) {
		var currentContent
		  , idForPage = ""
		  , autoSave = false
		  , newIdInput
		  , newIdAdjacent;
		
		autoSave = true;
		
		$(document).everyTime(30000, function() {
			switch(activeEditor) {
				// Autosave page depending on which editor is active.
				case "content-editor":
					// Set the current content.
					if(typeof(currentContent) === "undefined") {
						currentContent = $("#content-editor").html();
					}
					// Check for any changes to the current content, if there are changes autosave and update the current content.
					if($("#content-editor").html() !== currentContent) {
						// Autosave page
						autosavePage($("#content-editor").html());
						// Update current content
						currentContent = $("#content-editor").html();
					}
					break;
				case "html-editor":
					if($("#html-editor").val() !== currentContent) {
						// Set the current content.
						if(typeof(currentContent) === "undefined") {
							currentContent = $("#html-editor").val();
						}
						// Check for any changes to the current content, if there are changes autosave and update the current content.
						if($("#html-editor").val() !== currentContent) {
							// Autosave page
							autosavePage($("#html-editor").val());
							// Update current content
							currentContent = $("#html-editor").val();
						}
					}
					break;
				case "fullscreen-editor":
					if($("#html-editor").val() !== currentContent) {
						// Set the current content.
						if(typeof(currentContent) === "undefined") {
							currentContent = $("#fullscreen-editor").val();
						}
						// Check for any changes to the current content, if there are changes autosave and update the current content.
						if($("#html-editor").val() !== currentContent) {
							// Autosave page
							autosavePage($("#fullscreen-editor").val());
							// Update current content
							currentContent = $("#fullscreen-editor").val();
						}
					}
					break;
				case "fullscreen-html-editor":
					if($("#html-editor").val() !== currentContent) {
						// Set the current content.
						if(typeof(currentContent) === "undefined") {
							currentContent = $("#fullscreen-html-editor").val();
						}
						// Check for any changes to the current content, if there are changes autosave and update the current content.
						if($("#html-editor").val() !== currentContent) {
							// Autosave page
							autosavePage($("#fullscreen-html-editor").val());
							// Update current content
							currentContent = $("#fullscreen-html-editor").val();
						}
					}
					break;
				default:
					break;
			}
		});
	}
});
	
function autosavePage(body) {
	var action = "autosave";
	
	if($("#id").length !== 0)  {
		idForPage = $("#id").val();
		action = "revision";
	}
	
	if(typeof(idForPage) === "undefined") {
		idForPage = "";
	}
	
	if(typeof(revisionId) === "undefined") {
		revisionId = "";
	}
	 
	 // autosave a page
	 $.ajax({
		 type: "POST",
		 url: HTTP_ADMIN + "pages",
		 datatype: "json",
		 data: "adminRequest=" + encodeURIComponent(JSON.stringify({
			type            : "page",
			action          : action,
			id              : idForPage,
			page            : $("#title").val(),
			title           : $("#page-title").val(),
			alias           : $("#alias").val(),
			author          : $("#author").val(),
			datePosted      : $("#date-created").val(),
			body            : body,
			head            : $("#page-head-editor").html(),
			contentFormatId : $("#content-format").val(),
			template        : $("#template").val(),
			metaDescription : $("#metaDescription").val(),
			metaKeywords    : $("#metaKeywords").val(),
			metaAuthor      : $("#metaAuthor").val(),
			metaRobots      : $("#metaRobots").val(),
			revisionId      : revisionId,
			authorUsername  : $("#author option:selected").text(),
			csrfToken       : $("#csrf-token").val()
		})),
		success: function(data, textStatus, jqXHR) {
			var response = JSON.parse(data);
			idForPage = response.id;
			// If new page has been autosaved create the id elemnt so the page can be updated.
			if(response.action === "autosave" && typeof(response.errors) === "undefined") {
				$("#adminForm").prepend(
					'<input type="hidden" id="' + response.id + '" name="id" value="' + response.id + '" />' 
				);
				$("#autosave").html("Draft saved on " + response.lastSave);
				$("#autosave").effect("highlight", {}, 2000, function() {
					
				});
			// Handle page revisions for already posted pages.
			} else if(response.action === "revision") {
				// Set the page id to the revision id.
				revisionId = response.revisionId;
				$("#autosave").html("Revision by " + response.authorUsername + " saved on " + response.lastSave);
				$("#autosave").effect("highlight", {}, 2000, function() {

				});
			}
		 }
	});
}
