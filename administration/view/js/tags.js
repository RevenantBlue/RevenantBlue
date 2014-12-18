// Javascript Document
$(document).ready(function() {
	var noTagError = "No tags selected, please select a tag from the table below and try again.";
	// Edit tag clicks.
	$("#toolbar-edit, #action-edit-tag").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noTagError); 
		} else { 
			CMS.submitButton("tag", "edit"); 
		}
	});
	// Publish tag clicks.
	$("#toolbar-publish, #action-publish-tag").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noTagError); 
		} else { 
			CMS.submitButton("tag", "publish"); 
		}
	});
	// Unpublish tag clicks.
	$("#toolbar-unpublish, #action-unpublish-tag").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noTagError); 
		} else { 
			CMS.submitButton("tag", "unpublish");
		}
	});
	// Feature tag clicks.
	$("#toolbar-feature, #action-feature-tag").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noTagError); 
		} else { 
			CMS.submitButton("tag", "featured"); 
		}
	});                 
	// Remove feature tag clicks.
    $("#toolbar-nofeature, #action-nofeature-tag").click(function() { 
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noTagError); 
		} else { 
			CMS.submitButton("tag", "remove-featured"	); 
		}
	});
	// Delete tag clicks.
	$("#toolbar-delete, #action-delete-tag").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noTagError);
		} else { 
			CMS.submitButton("tag", "delete");
		}
	});
	// Publish tag button clicks.
	$(".publish-tag-content").click(function() {
		$("#tag-state").val(1);
		CMS.submitButton("tag", "save-close");
	});
	// Draft tag button clicks
	$(".draft-tag-content").click(function() {
		$("#tag-state").val(2);
		CMS.submitButton("tag", "save");
	});
	// Save tag clicks
	$("#toolbar-save, #action-save-tag").click(function() {
		CMS.submitButton("tag", "save");
	});
	// Save and new tag clicks
	$("#toolbar-save-new, #action-save-new-tag").click(function() {
		CMS.submitButton("tag", "save-new");
	});
	// Save and close tag clicks
	$("#toolbar-save-close, #action-save-close-tag").click(function() {
		selectNavItem("#tags-link", "#new-tag-link");
		CMS.submitButton("tag", "save-close");
	});
	// Close the tag editor clicks
	$("#toolbar-close-tag, #action-close-tag").click(function() {
		selectNavItem("#tags-link", "#new-tag-link");
		window.location.href = HTTP_ADMIN + "tags";
	});
});
