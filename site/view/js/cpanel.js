$(document).ready(function() {
	
	var notificationsRead = false // Keeps track if the user's notifications have been marked as read
	  , editor
	  , msgValidator;
	  
	if($("#message-editor").length !== 0) {
		$("#message-editor").ckeditor({
			contentsCss: HTTP_SERVER + 'site/view/css/forum-cke.css',
			forcePastAsPlainText: true,
			height: 175,
			disableNativeSpellChecker: false,
			removePlugins: 'contextmenu,liststyle,tabletools',
			allowedContent: {
				'b i ul ol strong em u s sub sup': true,
				'h1 h2 h3 p blockquote li a object div': {
					styles: 'text-align'
				},
				p: {
					attributes: 'class',
					classes: 'quote-citation'
				},
				a: { 
					attributes: 'href,target,class',
					classes:    'ui-icon,ui-icon-arrowreturnthick-1-w'
				},
				img: {
					attributes: 'src,alt',
					styles: 'width,height',
					classes: 'left,right'
				},
				iframe: {
					attributes: 'width,height,src,allowfullscreen,frameborder,title'
				}
			}
		});
	}
	if($("#about-me-editor").length !== 0) {
		$("#about-me-editor").ckeditor({
			contentsCss: HTTP_SERVER + 'site/view/css/forum-cke.css',
			forcePasteAsPlainText: true
		});
	}
	
	// Forum control panel specific requirements
	if($('#cpanel-menu').length !== 0) {
		cpanelTabs = $('#cpanel-menu').tabs({
			activate : function(e, ui) {
				var tab = ui.newPanel.prop("id")
				  , currentTitle = document.title
				  , cpanelSeg = '/' + url.segment(1);
				  
				switch(tab) {
					case 'cpanel-general':
						window.history.pushState('', currentTitle, cpanelSeg + '/general');
						break;
					case 'cpanel-profile':
						window.history.pushState('', currentTitle, cpanelSeg + '/profile');
						break;
					case 'cpanel-messages':
						window.history.pushState('', currentTitle, cpanelSeg + '/messenger');
						break;
					case 'cpanel-notify':
						window.history.pushState('', currentTitle, cpanelSeg + '/notifications');
						// Mark unread notifications as read
						if(!notificationsRead) {
							$.ajax({
								url: HTTP_CPANEL,
								type: 'POST',
								datatype: 'json',
								data: 'appRequest=' + encodeURIComponent(JSON.stringify({
									type        : 'cpanel',
									action      : 'mark-notifications-as-read',
									csrfToken   : $('.csrf-token').val()
								})),
								success: function(data, textStatus, jqXHR) {
									notificationsRead = true;
								}
							});
						}
						break;
					case 'cpanel-notify-opts':
						window.history.pushState('', currentTitle, cpanelSeg + '/notification-opts');
						break;
					default:
						break;
				}
			}
		}).addClass('ui-tabs-vertical ui-helper-clearfix');
		$('#forum-cpanel-menu li').removeClass('ui-corner-top').addClass('ui-corner-left');
		
		// Set the active tab
		if(typeof(tab) !== 'undefined') {
			switch(tab) {
				case 'profile':
					cpanelTabs.tabs('option', 'active', 1);
					break;
				case 'messenger':
					cpanelTabs.tabs('option', 'active', 2);
					break;
				case 'notifications':
					cpanelTabs.tabs('option', 'active', 3);
					break;
				case 'notification-opts':
					cpanelTabs.tabs('option', 'active', 4);
					break;
				default:
					cpanelTabs.tabs('option', 'active', 0);
					break;
			}
		}
		
		// Plupload for avatar
		uploader = new plupload.Uploader({
			runtimes : 'html5,gears,flash,silverlight,browserplus',
			browse_button : 'upload-avatar-btn',
			container : 'avatar-upload',
			max_file_size : maxAvatarSize + 'kb',
			url : HTTP_SERVER + 'forums?appRequest={"type":"forum","action":"upload-avatar","csrfToken":"' + $('#csrf-token').val() + '"}',
			flash_swf_url : '/plupload/js/plupload.flash.swf',
			silverlight_xap_url : '/plupload/js/plupload.silverlight.xap',
			filters : [
				{title : "Image files", extensions : "jpg,gif,png"}
			],
			resize : {
				width : 128,
				height : 128,
				quality : 90
			}
		});

		uploader.bind('Init', function(up, params) {
			
		});

		uploader.init();

		uploader.bind('FilesAdded', function(up, files) {
			$.each(files, function(i, file) {
				$('#avatar-upload').append(
					'<div id="' + file.id + '">' +
					file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
				'</div>');
			});
			
			uploader.start();

			up.refresh(); // Reposition Flash/Silverlight
		});

		uploader.bind('UploadProgress', function(up, file) {
			$('#' + file.id + " b").html(file.percent + "%");
		});

		uploader.bind('Error', function(up, err) {
			$('#avatar-upload').append(
				'<div class="error">' +
					'Error: ' + err.message +
				'</div>'
			);

			up.refresh(); // Reposition Flash/Silverlight
		});

		uploader.bind('FileUploaded', function(up, file, info) {
			var response = JSON.parse(info.response);
			
			$('#' + file.id + " b").html("100%");
			
			// Update avatar image
			window.setTimeout(function() {
				$('.avatar > img').prop('src', response.avatarURL);
			}, 10);
			
			//console.log(file);
		});
	}
	
	// Private Messages
	if($('#messages-menu').length !== 0) {
		// Private message tabs.
		$('#messages-menu').tabs();
		
		// Message composer modal and click to pop up.
		$('#message-composer').dialog({
			width       : 600,
			modal       : false,
			autoOpen    : false,
			beforeClose : function() {
				msgValidator.resetForm();
				$("#message-recipient, #message-subject, #message-editor").val("");
			},
			buttons     : [
				{
					id: 'send-pm',
					class: 'rb-btn blue-btn',
					text: 'Send Message',
					click: function(e) {
						e.preventDefault();
						// Validate the private message
						var valid = msgValidator.form();
						
						// Check the editor content
						if(CKEDITOR.instances['message-editor'].getData().length === 0) {
							alert('You cannot send an empty message.');
							valid = false;
							return false;
						}
						if(valid) {
							$.ajax({
								url: HTTP_CPANEL,
								type: 'POST',
								datatype: 'json',
								data: 'appRequest=' + encodeURIComponent(JSON.stringify({
									type        : 'cpanel',
									action      : 'send-pm',
									recipient   : $('#message-recipient').val(),
									subject     : $('#message-subject').val(),
									content     : $('#message-editor').val(),
									csrfToken   : $('#csrf-token').val()
								})),
								success: function(data, textStatus, jqXHR) {
									response = JSON.parse(data);
									if(typeof(response.messageSent) !== "undefined") {
										// Hide the buttonpane
										$("#message-composer").parents().find(".ui-dialog-buttonpane").hide();
										// Hide the PM form.
										$("#pm-form").hide();
										$("#pm-sent").show();

										// Show the message sent window and close it
										window.setTimeout(function() {
											$("#message-composer").dialog("close");
											$("#pm-form").show();
											// Clear the editor.
											$("#message-recipient, #message-subject, #message-editor").val("");
											$("#pm-sent").hide();
											// Show the buttonpane
											$("#message-composer").parents().find(".ui-dialog-buttonpane").show();
										}, 2500);
										
										if(typeof(response.sentMsgId) !== "undefined") {
											$("#no-more-sent-msgs").hide();
											// Add the sent message to the user's sent box
											$("#sent-box > tbody").prepend(
												'<tr id="sent-msg-row-' + response.sentMsgId + '">' +
													'<td>' +
														'<input id="sent-msg-' + response.sentMsgId + '" type="checkbox" class="sent-msg-cb" />' +
													'</td>' +
													'<td>' +
														'<a href="#">' +
															response.subject +
														'</a>' +
													'</td>' +
													'<td>' +
														response.recipientUsername +
													'</td>' +
													'<td>' +
														response.dateSent +
													'</td>' +
												'</tr>'
											);
										}
									}
								}
							});
						}
					}
				},
				{
					id: 'close-pm',
					class: 'rb-btn light-gray-btn',
					text: 'Cancel',
					click: function(e) {
						e.preventDefault();
						$("#message-composer").dialog("close");
					}
				}
			]
		});
		
		$('#compose-message').click(function(e) {
			e.preventDefault();
			$('#message-composer').dialog('open');
		});
		
		// Check the server to make sure the recipient's user id exists.
		$("#message-recipient").typing({
			start : function(e, element) {
				$("#message-composer-errors > .username-exists").remove();
			},
			stop : function(e, element) {
				$.ajax({
					url: HTTP_CPANEL,
					type: 'POST',
					datatype: 'json',
					data: 'appRequest=' + encodeURIComponent(JSON.stringify({
						type        : 'cpanel',
						action      : 'check-if-user-exists',
						recipient   : $("#message-recipient").val(),
						csrfToken   : $('#csrf-token').val()
					})),
					success: function(data, textStatus, jqXHR) {
						var response = JSON.parse(data);
						if(typeof(response.userExists) !== "undefined" && response.userExists) {
							$("#user-exists").val("1");
						} else {
							$("#message-composer-errors > .username-exists").remove();
							$("#message-composer-errors").append(
								'<p class="error username-exists">' +
									'The username you entered for the recipient does not exist.' +
								'</p>'
							);
						}
					}
				});
			},
			delay : 1500
		});
		
		// Message validation
		msgValidator = $("#message-form").validate({
			rules: {
				msgRecipient : {
					required  : true
				},
				msgSubject : {
					required : true
				}
			},
			messages : {
				msgRecipient : {
					required  : "Please provide a recipient for this message."
				},
				msgSubject : {
					required : "Please provide a subject for this message."
				}
			},
			errorElement: "p",
			wrapper: "div",
			errorLabelContainer: "#message-composer-errors"
		});
		
		// Check All inbox or sent messages clicks
		$("#select-all-inbox-msgs").click(function(e) {
			if($(this).is(":checked")) {
				$(".inbox-msg-cb").prop("checked", true);
			} else {
				$(".inbox-msg-cb").prop("checked", false);
			}
		});
		$("#select-all-sent-msgs").click(function(e) {
			if($(this).is(":checked")) {
				$(".sent-msg-cb").prop("checked", true);
			} else {
				$(".sent-msg-cb").prop("checked", false);
			}
		});
		
		// Put message content into dialog window
		$('.inbox-msg-content').dialog({
			width      : 800,
			modal      : false,
			autoOpen   : false,
			open       : function() {
				var messageId = $(this).prop("id").replace("inbox-msg-content-", "");
				// Set reply button Id
				
				$("#inbox-msg-content-" + messageId)
					.siblings(".ui-dialog-buttonpane")
					.children(".ui-dialog-buttonset")
					.children(".reply-btn")
					.prop("id", "reply-to-msg-" + messageId);
				
				$.ajax({
					url: HTTP_CPANEL,
					type: 'POST',
					datatype: 'json',
					data: 'appRequest=' + encodeURIComponent(JSON.stringify({
						type      : 'cpanel',
						action    : 'mark-message-as-read',
						messageId : messageId,
						csrfToken   : $('.csrf-token').val()
					})),
					success: function(data, textStatus, jqXHR) {
						var response = JSON.parse(data);
						//console.log($("#inbox-open-" + response.messageId).length);
						$("#inbox-open-" + response.messageId).removeClass("bold");
					}
				});
			},
			buttons    : [
				{
					"text"  : "Reply",
					"class" : "rb-btn blue-btn reply-btn",
					"click" : function() {
						var msgId = $(this).prop("id").replace("inbox-msg-content-", "");
						var replySubject = $(this).siblings(".ui-dialog-titlebar").children(".ui-dialog-title").text();
						
						var replyRecipient = $("#sender-" + msgId).text().trim();
						$(this).dialog("close");
						
						//console.log(replyRecipient);
						// Load the reply into the message composer.
						$("#message-recipient").val(replyRecipient);
						$("#message-subject").val('RE: ' + replySubject);
						var quotedPost = 
							'<p class="quote-citation">' + replyRecipient + '</p>' +
							'<blockquote>' +
								$('#msg-content-' + msgId).html() +
							'</blockquote>' +
							'<p> </p>';
						$('#message-editor').val(quotedPost);
						// Open the message composer.
						$("#message-composer").dialog("open");
						
						editor.focus();
						
					}
				},
				{
					"text"  : "Close",
					"class" : "rb-btn light-gray-btn",
					"click" : function() {
						$(this).dialog("close");
					}
				}
			]
		});
		
		// Open inbox message
		$("#cpanel-messages").on("click", ".open-inbox-msg", function(e) {
			e.preventDefault();
			var id = $(this).prop("id").replace("inbox-open-", "");
			
			$("#inbox-msg-content-" + id).dialog("open");
		});
		
		
		// Put message content into dialog window
		$('.sent-msg-content').dialog({
			width      : 800,
			modal      : false,
			autoOpen   : false,
			buttons    : [
				{
					"text"  : "Close",
					"class" : "rb-btn light-gray-btn",
					"click" : function() {
						$(this).dialog("close");
					}
				}
			]
		});
		
		// Open sent message
		$("#cpanel-messages").on("click", ".open-sent-msg", function(e) {
			e.preventDefault();
			var id= $(this).prop("id").replace("sent-msg-open-", "");
			$("#sent-msg-content-" + id).dialog("open");
		});
		
		// Friend request messages
		// Accept friend
		$(".accept-friend").on("click", function(e) {
			e.preventDefault();
			var messageId = $(this).prop("id").replace("accept-friend-", "");
			$.ajax({
				url: HTTP_CPANEL,
				type: 'POST',
				datatype: 'json',
				data: 'appRequest=' + encodeURIComponent(JSON.stringify({
					type        : 'cpanel',
					action      : 'accept-friend-request',
					messageId   : messageId,
					csrfToken   : $('.csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					var response = JSON.parse(data);
					$("#inbox-msg-content-" + response.messageId).dialog("close");
					$("#inbox-msg-row-" + response.messageId).fadeOut('slow', function() {
						$(this).remove();
					});
				}
			});
		});
		
		// Decline friend
		$(".decline-friend").on("click", function(e) {
			e.preventDefault();
			var messageId = $(this).prop("id").replace("decline-friend-", "");
			$.ajax({
				url: HTTP_CPANEL,
				type: 'POST',
				datatype: 'json',
				data: 'appRequest=' + encodeURIComponent(JSON.stringify({
					type        : 'cpanel',
					action      : 'decline-friend-request',
					messageId   : messageId,
					csrfToken   : $('.csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					var response = JSON.parse(data);
					$("#inbox-msg-content-" + response.messageId).dialog("close");
					$("#inbox-msg-row-" + response.messageId).fadeOut('slow', function() {
						$(this).remove();
					});
				}
			});
		});
		
		// Private message recipient friend autocomplete
		if(typeof(friendUsernames) !== "undefined") {
			$("#message-recipient").autocomplete({
				source : friendUsernames
			});
		}
		
		// Delete private messages
		$("#cpanel-messages").on("click", "#delete-messages", function(e) {
			e.preventDefault();
			var deletionConfirmed, inboxMsgIds = [], sentMsgIds = [];
			
			deletionConfirmed = confirm('Please confirm that you want to delete the selected messages');
		
			if(deletionConfirmed) {
				// Get the ids of the messages that need to be deleted.
				$(".inbox-msg-cb").each(function() {
					if($(this).is(":checked")) {
						inboxMsgIds.push($(this).prop("id").replace("inbox-msg-", ""));
					}
				});
				$(".sent-msg-cb").each(function() {
					if($(this).is(":checked")) {
						sentMsgIds.push($(this).prop("id").replace("sent-msg-", ""));
					}
				});
				
				// Asynchronous deletion
				$.ajax({
					url: HTTP_CPANEL,
					type: 'POST',
					datatype: 'json',
					data: 'appRequest=' + encodeURIComponent(JSON.stringify({
						type        : 'cpanel',
						action      : 'delete-messages',
						inboxMsgIds : inboxMsgIds,
						sentMsgIds  : sentMsgIds,
						csrfToken   : $('.csrf-token').val()
					})),
					success: function(data, textStatus, jqXHR) {
						var response = JSON.parse(data), x;

						if(typeof(response.inboxMsgsDeleted) !== "undefined") {
							for(x = 0; x < response.inboxMsgIds.length; x++) {
								if(typeof(response.inboxMsgsDeleted[inboxMsgIds[x]]) !== "undefined" && response.inboxMsgsDeleted[inboxMsgIds[x]] == 1) {
									$("#inbox-msg-row-" + inboxMsgIds[x]).fadeOut('slow', function() {
										$(this).remove();
										if($("#inbox > tbody").children().length === 1) {
											$("#no-more-inbox-msgs").show();
											$("#unread-msgs-icon").removeClass("unread");
										}
									});
								}
							}
						}
						if(typeof(response.sentMsgsDeleted) !== "undefined") {
							for(x = 0; x < response.sentMsgIds.length; x++) {
								if(typeof(response.sentMsgsDeleted[sentMsgIds[x]]) !== "undefined" && response.sentMsgsDeleted[sentMsgIds[x]] == 1) {
									$("#sent-msg-row-" + sentMsgIds[x]).fadeOut('slow', function() {
										$(this).remove();
										if($("#sent-box > tbody").children().length === 1) {
											$("#no-more-sent-msgs").show();
										}
									});
								}
							}
						}
					}
				});
			}
		});
	}
	
	
	// Notifications
	// Select All notifications
	$("#cpanel-notify").on("click", "#select-all-notifications", function(e) {
		if($(this).is(":checked")) {
			$(".notification-cb").prop("checked", true);
		} else {
			$(".notification-cb").prop("checked", false);
		}
	});
	
	// Delete notifications
	$("#cpanel-notify").on("click", "#delete-notifications", function(e) {
		e.preventDefault();
		var deletionConfirmed, notificationIds = [];
		
		deletionConfirmed = confirm('Please confirm that you want to delete the selected notifications');
	
		if(deletionConfirmed) {
			$(".notification-cb").each(function() {
				if($(this).is(":checked")) {
					notificationIds.push($(this).prop("id").replace("notification-", ""));
				}
			});
		}
		
		// Asynchronous deletion
		$.ajax({
			url: HTTP_CPANEL,
			type: 'POST',
			datatype: 'json',
			data: 'appRequest=' + encodeURIComponent(JSON.stringify({
				type            : 'cpanel',
				action          : 'delete-notifications',
				notificationIds : notificationIds,
				csrfToken       : $('.csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				var response = JSON.parse(data);
				
				if(typeof(response.notificationsDeleted) !== "undefined") {
					for(x = 0; x < response.notificationIds.length; x++) {
						if(typeof(response.notificationsDeleted[notificationIds[x]]) !== "undefined" && response.notificationsDeleted[notificationIds[x]] == 1) {
							$("#notification-row-" + notificationIds[x]).fadeOut('slow', function() {
								$(this).remove();
								if($("#notifications > tbody").children().length === 1) {
									$("#no-more-notifications").show();
								}
							});
						}
					}
				}
			}
		});
	});
});
