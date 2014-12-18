// JavaScript Document

// Set the value of the number of boxes checked for the adminForm id.
var boxesChecked = 0
  , adminUrl = window.location.href
  , urlAction = $.url(adminUrl).param("action")
  , urlError = $.url(adminUrl).param("error")
  , CMS
  , loginExists;

// Check is a global refresh has been called
if(urlAction === "refreshAdmin") {
	// This is to prevent the login screen from showing up when the session is killed after a timeout.
	if(urlError) {
		parent.window.location.href = HTTP_ADMIN + "?error=" + urlError;
	} else {
		parent.window.location.href = HTTP_ADMIN;
	}
}

function resetMenu() {
	$("#left-nav-main > li > a").removeClass("selected selected-no-bottom selected-no-top selected-no-both selected-border");
	$("#left-nav-main > li > a > span.nav-arr").removeClass("nav-darr");
	$("#left-nav-main > li > div.submenu").hide();
}
// Update iframe
function updateIframe(ifrSrc) {
	window.setTimeout(function() {
		if($("#left-nav").length !== 0) {
			$("#main-iframe").prop("src", ifrSrc);
		}
	}, 10);
}

// Function that selects a nav item and unselects the previously selected one.
function selectNavItem(selectedNav, previousNav) {
	var navHref = $(selectedNav).prop("href");
	if($("#left-nav").length !== 0) {
		$(selectedNav).addClass("selected selected-border");
		$(previousNav).removeClass("selected selected-border");
		$(selectedNav).parent(".submenu").show();
		$(selectedNav).closest($(".top-nav > a > .nav-arr")).addClass("nav-darr");
		console.log($(selectedNav).closest($(".top-nav > a > .nav-arr")));
	} else {
		window.parent.$(selectedNav).addClass("selected selected-border");
		window.parent.$(previousNav).removeClass("selected selected-border");
		window.parent.$(selectedNav).parent(".submenu").show();
		window.parent.$(selectedNav).closest($(".top-nav > a > .nav-arr")).addClass("nav-darr");
	}
}
// Toggling the left nav pane.
$(document).on("click", "#nav-toggle", function() {
	var leftNav = $("#left-nav")
	  , leftNavWidth = $("#left-nav").css("width")
	  , fixedInner = $("#main-iframe").contents().find("#fixed-inner")
	  , fixedInnerMargin = fixedInner.css("margin");
	
	// If no margin exists for the fixed-inner element for some reason then supply it manually.
	if(typeof(fixedInnerMargin) === "undefined" || !fixedInnerMargin) {
		fixedInnerMargin = "55px 10px 10px 230px";
	}
	leftNav.animate({
		left: parseInt(leftNav.css('left'), 10) === 0 ? -leftNav.outerWidth() : 0
	})
	$("#vert-separator, #vert-separator-fix").animate({
		left: parseInt($("#vert-separator").css('left'), 10) === 220 ? -$("#vert-separator").outerWidth() : 220
	});
	if($("#left-nav").is(":visible")) {
		window.setTimeout(function() {
			$("#left-nav, #vert-separator, #vert-separator-fix").hide();
			fixedInner.css("margin", "55px 10px 54px 15px");
			$("#nav-toggle").html("›");
			localStorage.leftNavVisible = 0;
		}, 500);
	} else {
		//console.log(fixedInnerMargin);
		//console.log(fixedInner);
		$("#left-nav, #vert-separator, #vert-separator-fix").show();
		fixedInner.prop("style" ,'');
		window.setTimeout(function() {
			$("#nav-toggle").html("‹");
		}, 500);
		localStorage.leftNavVisible = 1;
	}
});

$(window).on("beforeunload", unloadPage);
$(window).on("unload", unloadPage);

function unloadPage() {
	if($("#main-iframe").length !== 0) {
		// Set the current iframe src.
		localStorage.currentUrl = document.getElementById("main-iframe").contentWindow.location.href;
		// Set the current left nav structure.
		localStorage.nav = $("#left-nav").html();
	}
}

$(document).ready(function() {
	$("#main-iframe").ready(function() {
		// Show the left nav menu once the main-iframe is loaded
		window.setTimeout(function() {
			$("#left-nav, #nav-toggle, #vertical-separator").show();
		}, 10);
		
		// If the page was refreshed restore the iframe and the left nav pane to their previous states.
		if(typeof(localStorage) !== "undefined") {
			if(!username) {
				localStorage.currentUrl = "";
			}
			$("#main-iframe").load(function() {
				var iframeSrc = document.getElementById("main-iframe").contentWindow.location.href;
				//console.log(localStorage.currentUrl);
				//console.log(iframeSrc);
				//console.log(iframeSrc === localStorage.currentUrl);
				// Refresh behavior
				if(username && localStorage.currentUrl) {
					$("#left-nav").html(localStorage.nav);
					updateIframe(localStorage.currentUrl);
					localStorage.currentUrl = "";
				}
				// Show the iframe.
				$("#main-iframe").css("visibility", "visible");
			});
		}
		// Fallback in case the iframe doesn't show (this happens randomly)
		$("#main-iframe").css("visibility", "visible");
	});
	// Left Nav menu drop down clicks.
	$(document).on("click", "#left-nav-main li a", function(e) {
		var navId = "#" + $(this).prop("id")
		  , topNavId = "#" + $(this).parent().prop("id")
		  , navHref = $(this).prop("href");

		// Reset the current URL to override a bug when firefox loses focus.
		localStorage.currentUrl = "";
		
		// Handle clicks on top-nav items with a submenu
		if($(navId).siblings(".submenu")) {
			// If the submenu is visible collapse it else show it.
			if($(navId).siblings(".submenu").is(":visible")) {
				if($(navId).hasClass("selected")) {
					$(navId + " > span.nav-arr").removeClass("nav-darr");
					$(navId).siblings(".submenu").hide();
				}
			} else {
				$(navId + " > span.nav-arr").addClass("nav-darr");
				$(navId).siblings(".submenu").show();
			}
			// Remove all selected styles from the menu.
			$("#left-nav-main li a").removeClass("selected selected-border");
		}
		// Add the selected class to the the link.
		$(navId).addClass("selected");
		// Add the selected border if it isn't a top nav item.
		if($(topNavId).hasClass("top-nav") || $(navId).hasClass("top-nav")) {
			if(topNavId === "#nav-home") {
				$(navId).addClass("selected-no-bottom");
			} else if(topNavId === "#nav-config") {
				$(navId).addClass("selected-no-top");
			} else {
				$(navId).addClass("selected-no-both");
			}
		} else {
			$(navId).addClass("selected-border");
		}
		if(!$(navId + " > span.nav-arr").hasClass("nav-darr") && !$(navId).parent().hasClass("submenu") && !$(navId).hasClass("no-submenu")) {
			e.preventDefault();
			return false;
		}
		// Update the src of the iframe
		updateIframe(navHref);
		// Save the current nav structure.
		if(typeof(localStorage) !== "undefined") {
			localStorage.currentNavStructure = $("#left-nav").html();
		}
	});
	// Adjust margin of fixed inner depending on left nav visibiility
	if(!window.parent.$("#left-nav").is(":visible")) {
		window.parent.$("#main-iframe").contents().find("#fixed-inner").css("margin", "51px 10px");
	}
	// Change the overflow type on hover.
	$("#left-nav").hoverIntent(function() {
		$("#left-nav-wrap").css("overflow", "auto");
	}, function() {
		$("#left-nav-wrap").css("overflow", "hidden");
	});
	
	// Jquery UI buttons
	$(".rb-btn").button().unbind('focus');
	
	// Main user actions toggle
	$("#main-user-actions").click(function() {
		$("#main-user-actionmenu").toggle();
	});
	
	// Clear out unread notifications when clicking on the icon
	$("#notifs-icon").click(function() {
		$(this).prop("title", "You have no unread messages").text("").removeClass("active");
	});
	
	// Submit login click handler
	$("#submit-login").click(function(e) {
		e.preventDefault();
		if(typeof(localStorage.currentUrl) === "undefined") {
			delete localStorage.currentUrl;
		}
		$("#submit-login").val("login");
		$("#loginForm").submit();
	});
});

// Action Menu click behavior.
$(document).mouseup(function(e) {
	var actionMenu = $("#main-user-actionmenu")
	  , actionMenuLink = $(e.target).parents("#main-actions").length;
	
	if(actionMenu.length === 0) {
		actionMenu = window.parent.$("#main-user-actionmenu");
	}
	  
	if((actionMenuLink === 1 || actionMenu.has(e.target).length === 0) && actionMenu.is(":visible")) {
		// Micro timeout to override the main-actions click function from firing.
		window.setTimeout(function() {	
			actionMenu.hide();
		}, 10);
	}
});
