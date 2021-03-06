$(document).ready(function() {
	if(typeof(localStorage) !== "undefined") {
		if(typeof(localStorage.currentUrl) !== "undefined" && localStorage.currentUrl) {
			//console.log(localStorage.currentUrl);
		} else {
			$("#dashboard-main, #dashboard-overview").show();
		}
	}
	
	// Clear failex logins click
	$("#clear-failed-logins").click(function(e) {
		e.preventDefault();
		$.ajax({
			url: HTTP_ADMIN + "dashboard",
			type: "POST",
			datatype: "json",
			data: "adminRequest=" + encodeURIComponent(JSON.stringify({
				type      : "dashboard",
				action    : "clear-failed-logins",
				ip        : $("#ip").val(),
				csrfToken : $("#csrf-token").val()
			})),
			success: function(data, textStatus, jqXHR) {
				$(".failed-login").fadeOut(function() {
					$(".failed-login").remove();
					$("#failed-login-head").hide();
					$("#no-failed-logins").show();
				});
			}
		});
	});
});
