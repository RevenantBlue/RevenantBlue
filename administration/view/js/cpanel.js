$(document).ready(function() {
	
	var editor; // The CKEditor for messages.
	 
	// Build the tabs for the messenger.
	if($("#cpanel-menu").length !== 0) {
		
		// Set the default tab.
		switch(defaultTab) {
			case 'messages':
				defaultTab = 0;
				break;
			case 'notifications':
				defaultTab = 1;
				break;
			case 'notification-options':
				defaultTab = 2;
				break;
			default:
				defaultTab = 0;
				break;
		}
		
		$("#cpanel-menu").tabs({
			active: defaultTab,
			activate: function(ui) {
				var activeTab = $("#cpanel-menu").tabs("option", "active");
				
				// Mark unread notifications as read
				if(activeTab == 1) {
					$.ajax({
						url: HTTP_ADMIN + 'messages',
						type: 'POST',
						datatype: 'json',
						data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
							type        : 'cpanel',
							action      : 'mark-notifications-as-read',
							csrfToken   : $('#csrf-token').val()
						})),
						success: function(data, textStatus, jqXHR) {
							parent.window.$("#notifs-icon").prop("title", "You have no unread notifications").removeClass("active");
						}
					});
				}
			}
		});
	}
	
	// Private Messages
	if($('#messages-menu').length !== 0) {
		// Private message tabs.
		$('#messages-menu').tabs();
		
		// Message composer modal and click to pop up.
		$('#message-composer').dialog({
			autoOpen   : false,
			width      : 650,
			position   : {
				within : '#fixed-inner',
				of : '#fixed-inner',
				at : 'center',
				my : 'center'
			},
			open       : function() {
				// Define the CKEditor var and set the height of the message-editor
				
				// CKEditor the message-editor
				if($("#message-editor").length !== 0) {
					$("#message-editor").ckeditor({
						forcePasteAsPlainText : true,
						baseFloatZIndex       : 156000,
						width      : 550,
						height     : 225
					});
				}
				
				if($("#message-editor").length !== 0) {
					editor = CKEDITOR.instances['message-editor']
					  , dragStartOutside = true;
					editor.on("instanceReady", function(ev) {
						// Remove the resize handles, counter intuitive but ckeditor won't let me change the config dynamically for just one editor.
						$(".cke_resizer").remove();
						
						ev.editor.document.on('dragstart', function(ev) {
						   dragStartOutside = false;
						});
						ev.editor.document.on('drop', function(ev) {
						   if(dragStartOutside) {
							  ev.data.preventDefault(true);
						   }
						   dragStartOutside = true;
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
							errorLabelContainer: "#message-composer-errors"
						});
					});
				}
			},
			beforeClose      : function() {
				msgValidator.resetForm();
				$("#message-recipient, #message-subject, #message-editor").val("");
			},
			buttons : [
				{
					id: 'send-pm',
					class: 'rb-btn blue-btn',
					text: 'Send Message',
					click: function(e) {
						e.preventDefault();
						// Validate the private message
						var valid = msgValidator.form();
						
						// Check the editor content
						if(editor.getData().length === 0) {
							alert('You cannot send an empty message.');
							valid = false;
							return false;
						}
						
						if (valid) {
							$.ajax({
								url: HTTP_ADMIN + 'messages',
								type: 'POST',
								datatype: 'json',
								data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
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
					url: HTTP_ADMIN + 'messages',
					type: 'POST',
					datatype: 'json',
					data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
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
			autoOpen   : false,
			position   : {
				within : '#fixed-inner',
				of : '#fixed-inner',
				at : 'center',
				my : 'center'
			},
			open       : function() {
				
				var messageId = $(this).prop("id").replace("inbox-msg-content-", "");
				
				// Set reply button Id
				$("#inbox-msg-content-" + messageId)
					.siblings(".ui-dialog-buttonpane")
					.children(".ui-dialog-buttonset")
					.children(".reply-btn")
					.prop("id", "reply-to-msg-" + messageId);
				
				// Mark the message as read in the database.
				$.ajax({
					url: HTTP_ADMIN + 'messages',
					type: 'POST',
					datatype: 'json',
					data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
						type      : 'cpanel',
						action    : 'mark-message-as-read',
						messageId : messageId,
						csrfToken : $('.csrf-token').val()
					})),
					success: function(data, textStatus, jqXHR) {
						var response = JSON.parse(data);
						//console.log($("#inbox-open-" + response.messageId).length);
						$("#inbox-open-" + response.messageId).removeClass("bold");
					}
				});
			},
			close : function() {
				
			},
			buttons    : [
				{
					"text"  : "Reply",
					"class" : "rb-btn blue-btn reply-btn",
					"click" : function() {
						msgId = $(this).prop("id").replace("inbox-msg-content-", "");
						replySubject = $(this).siblings(".ui-dialog-titlebar").children(".ui-dialog-title").text();
						replyContent = '<p></p>' +
										'<hr />' + 
										$(this).children(".inner").html();
						
						replyRecipient = $("#sender-" + msgId + " > .avatar-username > a").text().trim();
						//console.log(replyRecipient);
						$(this).dialog("close");
						
						//console.log(replyRecipient);
						// Load the reply into the message composer.
						$("#message-recipient").val(replyRecipient);
						$("#message-subject").val('RE: ' + replySubject);
						
						// Open the message composer.
						$("#message-composer").dialog("open");
						
						window.setTimeout(function() {
							editor.focus();
						}, 1000);
						
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
		$("#messages").on("click", ".open-inbox-msg", function(e) {
			e.preventDefault();
			
			var id = $(this).prop("id").replace("inbox-open-", "")
			  , unopened = $(this).hasClass("bold")
			  , numOfUnreadMsgs = parseInt(parent.window.$("#msgs-icon").text(), 10);
			
			// If only one message is unread else if more than 1 message is unread
			if(unopened) {
				if(numOfUnreadMsgs === 1) {
					// Decrement the number of unread messages.
					numOfUnreadMsgs -= 1;
					parent.window.$("#msgs-icon").text("").prop("title", "You have no unread messages").removeClass("active");
				} else {
					// Decrement the number of unread messages.
					numOfUnreadMsgs -= 1;
					// Overkill to ensure it reads 'message' for 1 unread message remaining.
					if(numOfUnreadMsgs === 1) {
						parent.window.$("#msgs-icon").text(numOfUnreadMsgs).prop("title", "You have " + numOfUnreadMsgs + " unread message");
					} else {
						parent.window.$("#msgs-icon").text(numOfUnreadMsgs).prop("title", "You have " + numOfUnreadMsgs + " unread messages");
					}
				}
			}
			
			$("#inbox-msg-content-" + id).dialog("open");
		});
		
		
		// Put message content into dialog window
		$('.sent-msg-content').dialog({
			width      : 800,
			modal      : true,
			autoOpen   : false,
			open       : function() {
				hideMenus(true);
			},
			close      : function() {
				showMenus(true);
			},
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
		$("#messages").on("click", ".open-sent-msg", function(e) {
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
				url: HTTP_ADMIN + 'messages',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
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
				url: HTTP_ADMIN + 'messages',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
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
		$("#messages").on("click", "#delete-messages", function(e) {
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
					url: HTTP_ADMIN + 'messages',
					type: 'POST',
					datatype: 'json',
					data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
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
	$("#notifications").on("click", "#select-all-notifications", function(e) {
		if($(this).is(":checked")) {
			$(".notification-cb").prop("checked", true);
		} else {
			$(".notification-cb").prop("checked", false);
		}
	});
	
	// Delete notifications
	$("#notifications").on("click", "#delete-notifications", function(e) {
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
			url: HTTP_ADMIN + 'messages',
			type: 'POST',
			datatype: 'json',
			data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
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
