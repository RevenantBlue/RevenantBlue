/**
 *
 * Copyright 2013, Revenant Blue
 * Released under GPL License.
 *
 *
 * Date: 2013-12-09
 */
$(document).ready(function() {
	
	"use strict";
	
	$(window).load(function() {
		// Show the main section once everything is loaded
		$("#main").css("visibility", "visible");
	});
	
	$(window).load(function() {
		// Pulsate unread messages and notifications icons
		if($("#unread-msgs-icon").length !== 0) {
			$("#unread-msgs-icon").effect("pulsate", { times : 5 }, 5000, function() {
				
			}).addClass("unread");
		}
		
		if($("#unread-notifs-icon").length !== 0) {
			$("#unread-notifs-icon").effect("pulsate", { times : 5 }, 5000, function() {
				
			}).addClass("unread");
		}
	});
	
	// Form validations
	if($("#submit-login").length !== 0) {
		// Load validation for login forms.
		/*
		$("#main-form").validate({
			rules: {
				password: {
					required: true,
					minlength: 5
				},
				email: {
					required: true,
					email: true
				}
			},
			messages: {
				password: {
					required: "Please enter your password",
					rangelength: $.format("Enter at least {0} characters")
				},
				email: {
					required: "Please enter a valid email address",
					minlength: "Please enter a valid email address",
					remote: $.format("{0} is already in use")
				}
			}
		});
		*/
	}
	if($("#submit-reg").length !== 0) {
		/*
		$("#main-form").validate({
			rules: {
				"username" : {
					required  : true,
					minlength : 3,
					accept    : "[a-zA-Z0-9_]+"
				},
				"password" : {
					required: true,
					minlength: 5
				},
				"confirmPassword" : {
					required: true,
					minlength: 5,
					equalTo: "#reg-password"
				},
				"regEmail" : {
					required : true,
					email    : true
				}
			},
			messages : {
				"username" : {
					required  : "Please provide a username",
					minlength : $.format("Enter at least {0} characters"),
					accept    : "Only letters, numbers, and underscores are allowed",    
					remote    : $.format("{0} is already in use")
				},
				"password" : {
					required    : "Please enter your password",
					rangelength : $.format("Enter at least {0} characters")
				},
				"confirmPassword" : {
					required  : "Repeat your password",
					minlength : $.format("Enter at least {0} characters"),
					equalTo   : "Your password does not match the confirmation"
				},
				"email" : {
					required  : "Please enter a valid email address",
					minlength : "Please enter a valid email address",
					// remote    : $.format("{0} is already in use")
				}
			}
		});
		*/
	}
	
	// Buttons
	$(".rb-btn").button();
	
	// Hide the profile drop down when clicking outside of it after it has been opened.
	$(document).mouseup(function(e) {
		var actionMenu = $("#main-user-actionmenu")
		  , actionMenuLink = $(e.target).parents("#main-actions").length
		  , mainMenu = $("#main-menu")
		  , mainMenuLink = $(e.target).parents("#main-menu").length;
		  
		if((actionMenuLink === 1 || actionMenu.has(e.target).length === 0) && actionMenu.is(":visible")) {
			// Micro timeout to override the main-actions click function from firing.
			window.setTimeout(function() {	
				actionMenu.hide();
			}, 10);
		}
	});
	// Click handlers
	$("#main-actions").click(function(e) {
		$("#main-user-actionmenu").toggle();
	});
	
	$("#resend-email-verification").click(function(e) {
		e.preventDefault();
		$.ajax({
			url: HTTP_SERVER,
			type: 'POST',
			datatype: 'json',
			data: 'appRequest=' + encodeURIComponent(JSON.stringify({
				type        : 'global',
				action      : 'resend-email-verification',
				userId      : $("#user-id").val(),
				csrfToken   : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				
			}
		});
	});
	
	$("#register-button").click(function(e) {
		e.preventDefault();
		window.location = HTTP_SERVER + 'register';
	});
});

// JQuery extensions
// hasParent for jquery
jQuery.extend(jQuery.fn, {
    // Name of our method & one argument (the parent selector)
    hasParent: function(p) {
        // Returns a subset of items using jQuery.filter
        return this.filter(function() {
            // Return truthy/falsey based on presence in parent
            return $(p).find(this).length;
        });
    }
});

jQuery.validator.addMethod("accept", function(value, element, param) {
    return value.match(new RegExp("^" + param + "$"));
});

// Strip trailingSlash
function stripTrailingSlash(str) {
    if(str.substr(-1) == '/') {
        return str.substr(0, str.length - 1);
    }
    return str;
}

// Conver seconds to time
function secondsToTime(secs) {
    var hours, divisor, minutes, seconds, obj;
    hours = Math.floor(secs / (60 * 60));

    divisor_for_minutes = secs % (60 * 60);
    minutes = Math.floor(divisor_for_minutes / 60);

    divisor_for_seconds = divisor_for_minutes % 60;
    seconds = Math.ceil(divisor_for_seconds);

    obj = {
        "h": hours,
        "m": minutes,
        "s": seconds
    };
    return obj;
}

Object.objSize = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

// Determines if element is in the current viewport
function isElementInViewport(el) {
	var rect = el.getBoundingClientRect();

	return (
		rect.top >= 0 &&
		rect.left >= 0 &&
		rect.bottom <= (window.innerHeight || document. documentElement.clientHeight) && /*or $(window).height() */
		rect.right <= (window.innerWidth || document. documentElement.clientWidth) /*or $(window).width() */
	);
}
