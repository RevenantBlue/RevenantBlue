$(document).ready(function() {
	$("#main-menu-toggle").click(function(e) {
		$("#main-menu").toggle("slide", {
			direction : "right"
		}, 200);
	});
	
	// Main menu clicking functionality to hide it when clicking outside of the menu space or toggle.
	$(document).mouseup(function(e) {
		var mainMenu = $("#main-menu")
		  , mainMenuLink = $(e.target).parents("#main-menu").length
		  , mainMenuToggle = $("#main-menu-toggle")
		  , mainMenuToggleLink = $(e.target).parents("#main-menu-toggle").length;
		
		if((mainMenuLink === 0 || mainMenu.has(e.target).length === 0) && mainMenu.is(":visible") && mainMenuToggleLink === 0 && mainMenuToggle.has(e.target).length === 0) {
			window.setTimeout(function() {
				mainMenu.hide("slide", {
					direction : "right"
				}, 200);
			}, 10);
		}
	});
	
	// Gallery fancy box functionality
	if($(".photo-box").length !== 0) {
		$(".photo-box").fancybox({
			transitionIn       : 'slide',
			transitionOut      : 'slide',
			speedIn            : 200, 
			speedOut           : 100
		});
	}
	
	// Send contact
	$("#send-contact").click(function(e) {
		e.preventDefault();
		$.ajax({
			type: "POST",
			url: HTTP_SERVER + 'contact',
			datatype: "json",
			data: "appRequest=" + encodeURIComponent(JSON.stringify({
				type      : "contact",
				action    : "email-contact",
				name      : $("#name").val(),
				email     : $("#email").val(),
				message   : $("#message").val(),
				csrfToken : $("#csrf-token").val()
			})),
			success: function(data, textStatus, jqXHR) {
				$("input, textarea").val("");
				$("#contact-form").fadeOut(500, function() {
					$("#contact-form-sent").fadeIn(2000);
				});
			}
		});
	});
});
