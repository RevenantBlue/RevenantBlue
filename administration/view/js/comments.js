// JavaScript Document
$(document).ready(function() {
	var noCommentError = "No comments were seleceted. \n\nPlease select a comment from the table below and try again.";
	// Buttonize the publish/unpublish comment buttons
	$("#publish-comment-btn, #unpublish-comment-btn").button();
	//Initialize the date/timepicker
	if($('#date-posted').length != 0) {
		$('#date-posted').datetimepicker({
				showOn: "both",
				buttonImage: HTTP_IMAGE + "icons/admin/calendar_icon2.png",
				buttonImageOnly: true,
				ampm: true,
				dateFormat: 'yy-mm-dd',
				timeFormat: 'hh:mm TT'
		});
	}
	// Edit comment clicks.
	$("#toolbar-edit-comment, #action-edit-comment").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noCommentError); 
		} else { 
			CMS.submitButton("comment", "edit"); 
		}
	});
	// Publish comment clicks.
	$("#toolbar-publish-comment, #action-publish-comment").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noCommentError); 
		} else {
			CMS.submitButton("comment", "publish"); 
		}
	});
	// Unpublish comment clicks.
	$("#toolbar-unpublish-comment, #action-unpublish-comment").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noCommentError); 
		} else { 
			CMS.submitButton("comment", "unpublish");
		}
	});
	// Delete comment clicks.
	$("#toolbar-delete-comment, #action-delete-comment").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noCommentError);
		} else { 
			CMS.submitButton("comment", "delete");
		}
	});
	// Publish comment button clicks.
	$(".publish-btn").click(function() {
		$("#state").val(1);
		CMS.submitButton("comment", "save-close");
	});
	// Draft comment button clicks
	$(".unpublish-btn").click(function() {
		$("#state").val(0);
		CMS.submitButton("comment", "save-close");
	});
	// Save comment clicks
	$("#toolbar-save-comment, #action-save-comment").click(function() {
		CMS.submitButton("comment", "save");
	});
	// Save and new comment clicks
	$("#toolbar-save-new-comment, #action-save-new-comment").click(function() {
		CMS.submitButton("comment", "save-new");
	});
	// Save and close comment clicks
	$("#toolbar-save-close-comment, #action-save-close-comment").click(function() {
		selectNavItem("#comments-link", "#new-comment-link");
		CMS.submitButton("comment", "save-close");
	});
	// Close the comment editor clicks
	$("#toolbar-close-comment, #action-close-comment").click(function() {
		selectNavItem("#comments-link", "#new-comment-link");
		window.location.href = HTTP_ADMIN + "comments";
	});
});
