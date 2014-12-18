// Javascript Document
$(document).ready(function() {
	var noUserError = "No users were selected. \n\nPlease select a user from the table below and try again.";
	
	// Edit user clicks.
	$("#toolbar-edit-user, #action-edit-user").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noUserError); 
		} else { 
			CMS.submitButton("user", "edit"); 
		}
	});
	// Publish user clicks.
	$("#toolbar-enable-user, #action-enable-user").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noUserError); 
		} else { 
			CMS.submitButton("user", "enable"); 
		}
	});
	// Unpublish user clicks.
	$("#toolbar-block-user, #action-block-user").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noUserError); 
		} else { 
			CMS.submitButton("user", "block");
		}
	});
	// Activate user clicks.
	$("#toolbar-activate-user, #action-activate-user").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noUserError);
		} else {
			CMS.submitButton("user", "activate");
		}
	});
	// Delete user clicks.
	$("#toolbar-delete-user, #action-delete-user").click(function() {
		var approveDel;
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noUserError);
		} else {
			approveDel = confirm("Are you sure that you want to delete the selected user(s)?");
			if(approveDel) {
				CMS.submitButton("user", "delete");
			}
		}
	});
	// Create user button clicks.
	$(".publish-user-content").click(function() {
		$("#user-state").val(1);
		CMS.submitButton("user", "save-close");
	});
	// Save user clicks
	$("#toolbar-save-user, #action-save-user").click(function() {
		CMS.submitButton("user", "save");
	});
	// Save and new user clicks
	$("#toolbar-save-new-user, #action-save-new-user").click(function() {
		CMS.submitButton("user", "save-new");
	});
	// Save and close user clicks
	$("#toolbar-save-close-user, #action-save-close-user").click(function() {
		selectNavItem("#users-link", "#new-user-link");
		CMS.submitButton("user", "save-close");
	});
	// Close the user editor clicks
	$("#toolbar-close-user, #action-close-user").click(function() {
		selectNavItem("#users-link", "#new-user-link");
		window.location.href = HTTP_ADMIN + "users";
	});
	
	// User Profile Clicks
	// Save profile
	$("#toolbar-save-profile, #action-save-profile").click(function() {
		CMS.submitButton("profile", "save");
	});
	// Save and close profile
	$("#toolbar-save-close-profile, #action-save-close-profile").click(function() {
		CMS.submitButton("profile", "save-close");
	});
});
