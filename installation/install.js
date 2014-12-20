$(document).ready(function() {
	
	// Buttonize
	$(".rb-btn").button();
	
	"use strict";
	document.getElementById("install-form").reset();
	// Form validations
	$("#install-form").validate({
		rules: {
			location: {
				required: true
			},
			adminUsername: {
				required: true,
				minlength: 3
			},
			adminPass: {
				required: true,
				minlength: 5
			},
			confirmAdminPass: {
				required: true,
				equalTo: "#admin-pass"
			},
			adminEmail: {
				required: true,
				email: true
			},
			dbName: {
				required: true
			},
			dbPrefix: {
				required: true
			},
			dbLocation: {
				required: true
			},
			clientDbUser: {
				required: true
			},
			clientDbPass: {
				required: true
			},
			confirmClientDbPass: {
				required: true,
				equalTo: "#client-db-pass"
			},
			adminDbUser: {
				required: true
			},
			adminDbPass: {
				required: true
			},
			confirmAdminDbPass: {
				required: true,
				equalTo: "#admin-db-pass"
			}
		},
		messages: {
			adminUsername: {
				required: "Please enter a username",
				rangelength: $.validator.format("Enter at least {0} characters")
			},
			adminPass: {
				required: "Please enter your password",
				rangelength: $.validator.format("Enter at least {0} characters")
			},
			confirmAdminPass: {
				required: "Please enter the password confirmation",
				equalTo: "Confirmation does not match the password"
			},
			adminEmail: {
				required: "Please enter a valid email address.",
				minlength: "Please enter a valid email address.",
			},
			dbName: {
				required: "Please enter a name for the database"
			},
			dbPrefix: {
				required: "Please enter a database prefix"
			},
			dbLocation: {
				required: "Please enter the location of the MySQL Database"
			},
			clientDbUser: {
				required: "Please enter a username"
			},
			clientDbPass: {
				required: "Please enter a password"
			},
			confirmClientDbPass: {
				required: "Please enter a password confirmation",
				equalTo: "Confirmation does not match the password"
			},
			adminDbUser: {
				required: "Please enter a username"
			},
			adminDbPass: {
				required: "Please enter a password"
			},
			confirmAdminDbPass: {
				required: "Please enter a password confirmation",
				equalTo: "Confirmation does not match the password"
			}
		}
	});
	
	$("#install-form").submit(function(e) {
		
	});
	$("#restore-config").click(function() { 
		$("#install-form").validate().cancelSubmit = true;
	});
	$("#redirect-to-admin").click(function(e) {
		e.preventDefault();
		console.log(window.HTTP_SERVER);
		window.location = window.HTTP_ADMIN;
	});
	$("#redirect-to-site").click(function(e) {
		e.preventDefault();
		console.log(window.HTTP_SERVER);
		window.location = window.HTTP_SERVER;
	})

});
