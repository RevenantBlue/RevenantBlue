// JavaScript Document
$(document).ready(function() {
	
	var adminObj, jsonStr, response, tabs;
	
	$('#block-ip').click(function(e) {
		e.preventDefault();
		if($("#adminForm").valid()) {
			$.ajax({
				url: HTTP_ADMIN + "config",
				type: "POST",
				datatype: "json",
				data: "adminRequest=" + encodeURIComponent(JSON.stringify({
					type      : "config",
					action    : "block-ip",
					ip        : $("#ip").val(),
					csrfToken : $("#csrf-token").val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					if(response.success) {
						var id = response.success;
						// Show the blocked ips table if it's hidden.
						$("#blocked-ips-table").show();
						// Hide the no blocked ips message.
						$("#no-blocked-ips").hide();
						$("#blocked-ips-table > tbody > .overview-top").after(
							'<tr id="blocked-ip-' + id + '">' +
								'<td class="left">' + 
									'<input type="hidden" id="ip-' + id + '" value="' + response.id + '" />' +
									response.ip + 
								'</td>' +
								'<td class="left">' +
									'<a id="unblock-ip-' + id + '" class="unblock-ip" href="#unblock-ip">Unblock</a>' +
								'</td>' +
							'</tr>'
						);
					}	$("#blocked-ip-" + id).effect("highlight", "#F0EB00", 3000);
				}
			})
		} else {
			return false;
		}
	});
	$("#blocked-ips-table").on("click", ".unblock-ip", function(e) {
		e.preventDefault();
		var id = $(this).prop("id");
		id = id.replace("unblock-ip-", "");
		$.ajax({
			type: "POST",
			url: HTTP_ADMIN + "config",
			datatype: "json",
			data: "adminRequest=" + encodeURIComponent(JSON.stringify({
				type      : "config",
				action    : "unblock-ip",
				id        : id,
				ip        : $("#ip-" + id).val(),
				csrfToken : $("#csrf-token").val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				if($("#blocked-ips-table").is(":hidden")) {
					$("#blocked-ips-table").show();
				}
				$("#blocked-ip-" + response.id).fadeOut( function() {
					$(this).remove();
					if($("#blocked-ips-table > tbody").children().length === 2) {
						$("#no-blocked-ips").show();
					}
				});
			}
		});
	});
	$("#verticalTabs").tabs().addClass("ui-tabs-vertical ui-helper-clearfix");
	$("#verticalTabs li").removeClass("ui-corner-top").addClass("ui-corner-left");

	function reorderFilters() {
		var order = 1;
		$(".filter-order").each(function() {
			if($(this).is(":visible")) {
				$(this).val(order);
				order++;
			}
		})
	}
	function reorderFormats() {
		var order = 1;
		$(".format-order").each(function() {
			$(this).val(order);
			order++;
		})
	}

	if($(".verticalTabs-1").length !== 0 && $(".verticalTabs-2").length !== 0) {
		if($(".verticalTabs-1").is(":hidden") && $("#filter-4").is(":checked")) {
			$(".verticalTabs-2").addClass("ui-tabs-selected ui-state-active");
			$("#verticalTabs-2").removeClass("ui-tabs-hide");
		}
		if($(".vertical-tabs-2").is(":hidden") && $("#filter-1").is(":checked")) {
			$(".verticalTabs-1").addClass("ui-tabs-selected ui-state-active");
			$("#verticalTabs-1").removeClass("ui-tabs-hide");
		}
		if(!$("#filter-1").is(":checked")) {
			$("#verticalTabs-1").hide();
		}
	}

	if($("#filter-1").is(":checked")) {
		$(".verticalTabs-1 .filter-1 #verticalTabs-1").show();
	}
	if($("#filter-4").is(":checked")) {
		$(".verticalTabs-2 .filter-4 #verticalTabs-2").show();
	}

	$("#filter-1").click(function() {
		$("#verticalTabs-1").toggle();
		$(".verticalTabs-1").toggle();
		$(".filter-1").toggle();
		if($("#filter-1").is(":checked")) {
			if($("#filter-4").is(":checked")) $("#verticalTabs-2").hide();
			$(".verticalTabs-1").addClass("ui-tabs-selected ui-state-active");
			$(".verticalTabs-1").removeClass("ui-tabs-hide");
			$("#verticalTabs-1").removeClass("ui-tabs-hide");
			$(".verticalTabs-1").show();
			$("#verticalTabs-1").show();
		} else if(!$("#filter-1").is(":checked")) {
			$(".verticalTabs-1").removeClass("ui-tabs-selected ui-state-active");
			$(".verticalTabs-1").addClass("ui-tabs-hide");
			$("#verticalTabs-1").addClass("ui-tabs-hide");
			$(".verticalTabs-1").hide();
			$("#verticalTabs-1").hide();
		}
		if($("#filter-4").is(":checked") && $("#verticalTabs-2").hasClass("ui-tabs-hide")) {
			$("#verticalTabs-2").removeClass("ui-tabs-hide");
			if($(this).is(":hidden")) $(this).show();
		} else if(!$("#filter-4").is(":checked")) {
			$("#verticalTabs-2").addClass("ui-tabs-hide");
		}
		if($("#filter-4").is(":checked") && $(".verticalTabs-2").hasClass("ui-tabs-selected")) {
			$(".verticalTabs-2").removeClass("ui-tabs-selected ui-state-active");
		} else if($("#filter-4").is(":checked") && !$("#filter-1").is(":checked")) {
			$(".verticalTabs-2").addClass("ui-tabs-selected ui-state-active");
		}
		if($("#verticalTabs-1").is(":hidden") && $("#filter-4").is(":checked")) {
			$(".verticalTabs-2").addClass("ui-tabs-selected ui-state-active");
			$("#verticalTabs-2").removeClass("ui-tabs-hide");
			$(".verticalTabs-1").removeClass("ui-tabs-selected ui-state-active");
		}
		reorderFilters();
	});
	
	$("#filter-2").click(function() {
		$(".filter-2").toggle();
		reorderFilters();
	});
	
	$("#filter-3").click(function() {
		$(".filter-3").toggle();
		reorderFilters();
	});
	
	$("#filter-4").click(function() {
		$("#verticalTabs-2").toggle();
		$(".verticalTabs-2").toggle();
		$(".filter-4").toggle();
		if($("#filter-4").is(":checked")) {
			if($("#filter-1").is(":checked")) $("#verticalTabs-1").hide();
			$(".verticalTabs-2").addClass("ui-tabs-selected ui-state-active")
			$(".verticalTabs-2").removeClass("ui-tabs-hide");
			$("#verticalTabs-2").removeClass("ui-tabs-hide");
			$(".verticalTabs-2").show();
			$("#verticalTabs-2").show();
		} else if(!$("#filter-4").is(":checked")) {
			$(".verticalTabs-2").removeClass("ui-tabs-selected ui-state-active")
			$(".verticalTabs-2").addClass("ui-tabs-hide");
			$("#verticalTabs-2").addClass("ui-tabs-hide");
			$(".verticalTabs-2").hide();
			$("#verticalTabs-2").hide();
		}
		if($("#filter-1").is(":checked") && $("#verticalTabs-1").hasClass("ui-tabs-hide")) {
			$("#verticalTabs-1").removeClass("ui-tabs-hide");
			if($(this).is(":hidden")) $(this).show();
		} else  if(!$("#filter-1").is(":checked")) {
			$("#verticalTabs-1").addClass("ui-tabs-hide");
		}
		if($("#filter-4").is(":checked") && $(".verticalTabs-1").hasClass("ui-tabs-selected")) {
			$(".verticalTabs-1").removeClass("ui-tabs-selected ui-state-active");
		} else if($("#filter-1").is(":checked") && !$("#filter-4").is(":checked")) {
			$(".verticalTabs-1").addClass("ui-tabs-selected ui-state-active");
		}
		if($("#verticalTabs-2").is(":hidden") && $("#filter-1").is(":checked")) {
			$(".verticalTabs-1").addClass("ui-tabs-selected ui-state-active");
			$("#verticalTabs-1").removeClass("ui-tabs-hide");
			$(".verticalTabs-2").removeClass("ui-tabs-selected ui-state-active");
		}
		reorderFilters();
	});
	
	$("#filter-5").click(function() {
		$(".filter-5").toggle();
		reorderFilters();
	});
	
	$("#filter-6").click(function() {
		$(".filter-6").toggle();
		reorderFilters();
	});
	
	$("#filter-7").click(function() {
		$(".filter-7").toggle();
		reorderFilters();
	});
	
	// Sortable table rows for the format and filter tables.
	if($("#content-formats").length != 0) {
		$("#content-formats").tableDnD({
			onDragClass : "tableDnD-drag",
			onDrop : function(table, row) {
				var formatId, formatOrder = {}, x = 1;
				reorderFormats();
				$("#content-formats > tbody > tr").each(function(e) {
					formatId = $(this).prop("id").replace("format-", "");
					if(typeof(formatId) !== "undefined") {
						console.log(formatId);
						formatOrder[x] = formatId;
					}
					x++;
				});
				// Dynamic reorder of content formats.
				$.ajax({
					type: "POST",
					url: HTTP_ADMIN + "config",
					datatype: "json",
					data: "adminRequest=" + encodeURIComponent(JSON.stringify({
						type        : "config",
						action      : "reorder-formats",
						formatOrder : formatOrder,
						csrfToken   : $("#csrf-token").val()
					})),
					success: function(data, textStatus, jqXHR) {
						response = JSON.parse(data);
						console.log(formatOrder);
					}
				});
			}
		});
	}
	
	if($("#content-filters").length != 0) {
		$("#content-filters").tableDnD({
			onDragClass : "tableDnD-drag",
			onDrop : function(table, row) {
				// Reorder the filters on the frontend.
				reorderFilters();
			}
		});
	}

	// Delete format request.
	$("#content-formats").on("click", ".removeFormat", function(e) {
		var formatId = $(this).prop("id")
		  , formatId = formatId.replace("remove-format-", "")
		  , removeFormatConfirm = confirm("Are you sure that you want to delete this format?");
		  
		if(removeFormatConfirm) {
			$.ajax({
				type: "POST",
				url: HTTP_ADMIN + "config",
				datatype: "json",
				data: "adminRequest=" + encodeURIComponent(JSON.stringify({
					type      : "config",
					action    : "delete-format",
					id        : formatId,
					csrfToken : $("#csrf-token").val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					$("#format-" + response.id).fadeOut( function() {
						$(this).remove();
					});
				}
			});
		}
	});

	// Image templates
	// Add template toggle.
	if($("#add-template").length != 0) {
		$("#add-template").click(function() {
			$("#add-template-form").slideToggle();
		});
	}
	// New template ajax request
	$("#submitNewTemplate").click(function() {
		$.ajax ({
			url: HTTP_ADMIN + "config",
			type: "POST",
			datatype: "json",
			data: "adminRequest=" + encodeURIComponent(JSON.stringify({
				type            : "config",
				action          : "new-template",
				templateName    : $("templateName").val(),
				imageWidth      : $("templateWidth").val(),
				imageHeight     : $("templateHeight").val(),
				templateType    : $("templateType").val(),
				templateQuality : $("templateQuality").val(),
				csrfToken       : $("#csrf-token").val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				newId = response.id;
				lastRowClass = $("tr:last", "#template-list").prop("class");
				lastRowClass = lastRowClass.replace("overview-row", "");
				if(lastRowClass == "1") {
					newRowClass = "2";
				} else {
					newRowClass = "1";
				}
				$("#template-list > tbody:last").append(
					'<tr id="template-' + newId +'" class="overview-row' + newRowClass + '">' +
						'<td class="left">' + response.templateName + '</td>' +
						'<td>' +
							'<input type="text" id="imageWidth-' + newId + '" size="5" value="' + response.imageWidth + '"/>' +
						'</td>' +
						'<td>' +
							'<input type="text" id="imageHeight-' + newId + '" size="5" value="' + response.imageHeight + '"/>' +
						'</td>' +
						'<td>' +
							'<select id="templateType-' + newId + '">' +
								'<option selected="selected" value="' + response.templateType + '">' + response.templateType + '</option>' +
								'<option value="exact">exact</option>' +
								'<option value="portrait">portrait</option>' +
								'<option value="landscape">landscape</option>' +
								'<option value="crop">crop</option>' +
								'<option value="auto">auto</option>' +
							'</select>' +
						'</td>' +
						'<td>' +
							'<input id="templateQuality-' + newId + '" type="text" value="' + response.templateQuality + '" size="5" />' +
						'</td>' +
						'<td>' +
							'<p>' +
								'<span id="save-template-' + newId + '" class="save-template">Save</span>' +
								'<span | </span>' +
								'<span id="delete-template-' + newId + '" class="delete-template">Delete</span>' +
							'</p>' +
						'</td>' +
					'</tr>'
				);
				$("#template-" + response.id).effect("highlight", "#F0EB00", 3000);
			 }
		});
	});

	// Update template ajax request
	$(document).on("click", ".save-template", function() {
		var id = $(this).prop("id");
		id = id.replace("save-template-", "");
		$.ajax({
			url: HTTP_ADMIN + "config",
			type: "POST",
			datetype: "json",
			data: "adminRequest=" + encodeURIComponent(JSON.stringify({
				type            : "config",
				action          : "save-template",
				id              : id,
				templateWidth   : $("templateWidth-" + id).val(),
				templateHeight  : $("templateHeight-" + id).val(),
				templateType    : $("templateType-" + id).val(),
				templateQuality : $("templateQuality-" + id).val(),
				csrfToken       : $("#csrf-token").val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				$("templateWidth-" + id).val(response.templateWidth);
				$("templateHeight-" + id).val(response.templateHeight);
				$("templateType-" + id).val(response.templateType);
				$("templateQuality-" + id).val(response.templateQuality);
				$("#template-" + response.id).effect("highlight", "#F0EB00", 3000);
			}
		});
	});
	// Delete template ajax request
	$(document).on("click", ".delete-template", function() {
		var id = $(this).prop("id");
		id = id.replace("delete-template-", "");
		$.ajax ({
			url: HTTP_ADMIN + "config",
			type: "POST",
			datetype: "json",
			data: "adminRequest=" + encodeURIComponent(JSON.stringify({
				type         : "config",
				action       : "delete-template",
				id           : id,
				csrfToken    : $("#csrf-token").val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				$("#template-" + response.id).fadeOut("slow").delay(2000);
				$(this).remove();
			}
		});
	});

	// Initially disable the restore backup button until a backup has been selected.
	$("#restore-backup, #delete-backup").button({disabled : true});

	// Handle manual backup clicks.
	$("#create-backup").click(function(e) {
		e.preventDefault();
		var adminObj, jsonStr, response;
		$("#backup-ajax").show();
		$("#backup-complete").hide();
		$.ajax({
			url: HTTP_ADMIN + "config",
			type: "POST",
			datetype: "json",
				data: "adminRequest=" + encodeURIComponent(JSON.stringify({
				type      : "config",
				action    : "backup-database",
				csrfToken : $("#csrf-token").val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				
				// Clear out the errors if there are any.
				$("#backup-errors").html("");
				if(typeof(response.errors) === "undefined") {
					// Add the new backup
					$("#backup-list-table tbody tr:first").before(
						'<tr id="backup-' + response.id + '" class="backup-row hover-row">' +
							'<td>' +
								'<input type="radio" id="restore-db-' + response.id + '" name="restoreDb" value="' + response.id + '" />' +
							'</td>' +
							'<td>' +
								response.backupDate +
							'</td>' +
							'<td>' +
								response.backupFile +
							'</td>' +
							'<td>' +
								response.backupPath +
							'</td>' +
						'</tr>'
					);
					$("#backup-ajax").hide();
					$("#backup-complete").show().effect("highlight", {}, 2000, function() {

					});
					$("#backup-" + response.id).effect("highlight", {}, 2000, function() {

					});
					// Remove the focus class to fix jquery ui bug after clicking button.
					$("#create-backup").removeClass("ui-state-focus");
					// Remove the no database placeholder
					$("#no-database-placeholder").remove();
				} else {
					$("#backup-errors").append(
						'<p class="error">' + response.errors + '</p>'
					);
				}
			}
		});
	});

	// Backup table row clicks.
	$(document).on("click", ".backup-row", function() {
		var id = $(this).prop("id").replace("backup-", "");
		$("#restore-db-" + id ).prop("checked", true);
		$("#restore-backup, #delete-backup").button("enable");
	});
	// Recover database clicks.
	$(document).on("click", "#restore-backup", function(e) {
		var recoverDb;
		recoverDb = confirm("Recovering the database will result in the loss data entered from the point of recovery.\n\nRestoring the database may orphan media files on the filesystem.\n\n Are you sure you want to recover the database?");
		if(recoverDb) {
			$("#adminForm").submit();
		}
	});
	// Delete database clicks.
	$(document).on("click", "#delete-backup", function(e) {
		e.preventDefault();
		var deleteBackup, backupId, adminObj, jsonStr, response;
		backupId = $('input[name="restoreDb"]:checked').val();
		deleteBackup = confirm("Are you sure that you want to delete this backup file?");
		if(deleteBackup) {
			$.ajax({
				url: HTTP_ADMIN + "config",
				type: "POST",
				datetype: "json",
				data: "adminRequest=" + encodeURIComponent(JSON.stringify({
					type      : "config",
					action    : "delete-backup",
					backupId  : backupId,
					csrfToken : $("#csrf-token").val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
				}
			}).done(function() {
				$("#backup-" + backupId).fadeOut("slow", function() {
					$("#backup-" + backupId).remove();
					if($("#existing-backups").children().length === 0) {
						$("#existing-backups").append(
							'<tr id="no-database-placeholder">' +
								'<td colspan="4" class="center">There are currently no backups available to be restored.</td>' +
							'</tr>'
						);
					}
				});
			});
		}
	});
	// Auto delete backup changes
	$("#auto-delete-backups").change(function(e) {
		var frequency, adminObj, jsonStr, response;
		frequency = $("#auto-delete-backups").val();
		$("#auto-del-backup-ajax").show();
		$("#auto-del-backup-complete").hide();
		$.ajax({
			url: HTTP_ADMIN + "config",
			type: "POST",
			datetype: "json",
			data: "adminRequest=" + encodeURIComponent(JSON.stringify({
				type      : "config",
				action    : "change-auto-del-backups-frequency",
				frequency : frequency,
				csrfToken : $("#csrf-token").val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
			}
		}).done(function() {
			$("#auto-del-backup-ajax").hide();
			$("#auto-del-backup-complete").show().effect("highlight", {}, 2000, function() {
				
			});
		});
	});
	
	// Clear APC Cache
	$("#clear-apc-cache").click(function(e) {
		e.preventDefault();
		$("#ajax-loader").show();
		$("#action-complete").hide();
		$.ajax({
			url      : HTTP_ADMIN + "config",
			type     : "POST",
			datatype : "json",
			data     : "adminRequest=" + encodeURIComponent(JSON.stringify({
				type      : "config",
				action    : "clear-cache",
				csrfToken : $("#csrf-token").val()
			})),
			success  : function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
			}
		}).done(function() {
			$("#ajax-loader").hide();
			$("#action-complete").show().effect("highlight", {}, 2000, function() {

			});
		});
	});
	
	if($("#add-email-template-btn").length !== 0) {
		// Email template validation.
		$("#adminForm").validate({
			rules: {
				newEmailTemplateName : {
					required  : true
				},
				newEmailTemplateSubject : {
					required : true
				},
				newEmailTemplateBody : {
					required : true
				}
			},
			messages : {
				newEmailTemplateName : {
					required  : "Please provide a name for your email template."
				},
				newEmailTemplateSubject : {
					required : "Please provide a subject for your email template."
				},
				newEmailTemplateBody : {
					required : "The body of your email template cannot be blank."
				}
			},
			errorPlacement: function(error, element) {
				error.appendTo("#new-email-template-errors");
			}
		});
		
		// Add e-mail template button clicks
		$("#add-email-template-btn").click(function(e) {
			e.preventDefault();
			$("#add-email-template-form").slideToggle('slow', function() {
				
			});
		});
		
		$("#create-email-template").click(function(e) {
			e.preventDefault();
			$.ajax({
				url      : HTTP_ADMIN + "config",
				type     : "POST",
				datatype : "json",
				data     : "adminRequest=" + encodeURIComponent(JSON.stringify({
					type        : "config",
					action      : "insert-email-template",
					name        : $("#new-email-template-name").val(),
					description : $("#new-email-template-desc").val(),
					subject     : $("#new-email-template-subject").val(),
					body        : $("#new-email-template-body").val(),
					csrfToken   : $("#csrf-token").val()
				})),
				success  : function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					$(".email-templates-list > ul").append(
						'<li id="email-template-tab-' + response.newTemplateId + '">' +
							'<a href="#verticalTabs-' + response.newTemplateId + '">' +
								response.name +
							'</a>' +
							'<div class="clearfix"> </div>' +
						'</li>'
					);
					$("#email-template-content").append(
						'<div id="verticalTabs-' + response.newTemplateId + '">' +
							'<div class="fltrght">' +
								'<button id="delete-email-template-' + response.newTemplateId + '" class="delete-email-template-btn rb-btn light-gray-btn vert-space">' +
									'Delete Template' +
								'</button>' +
							'</div>' +
							'<div>' +
								'<h2 class="vert-space">Edit email templates</h2>' +
								'<p>' +
									response.description +
								'</p>' +
								'<p>' +
									'Available variables are: [site:name], [site:url], [user:name], [user:mail], [site:login-url], [site:url-brief], [user:edit-url], [user:one-time-login-url], [user:cancel-url].' + 
								'</p>' +
							'</div>' +
							'<div class="form-item">' +
								'<label class="label-regular width100pcnt">Subject</label>' +
								'<input class="width100pcnt" type="text" name="config[email][' + response.newTemplateId + '][subject]" value="' + response.subject + '" />' +
							'</div>' +
							'<div class="form-item">' +
								'<label class="label-regular width100pcnt">Body</label>' +
								'<textarea class="width100pcnt" rows="20" name="config[email][' + response.body + '][body]">' +
									response.body +
								'</textarea>' +
							'</div>' +
						'</div>'
					);
					
					// Clear the template form fields.
					$("#new-email-template-name").val(""),
					$("#new-email-template-desc").val(""),
					$("#new-email-template-subject").val(""),
					$("#new-email-template-body").val(""),
					
					// Hide the new template form.
					$("#add-email-template-form").slideToggle('slow', function() {
						
					});
					
					$("#verticalTabs").tabs("refresh");
					
					// Buttonize new delete button.
					$(".rb-btn").button();
				}
			});
		});
		
		// Delete email template button clicks
		$("#verticalTabs").on("click", ".delete-email-template-btn", function(e) {
			e.preventDefault();
			
			var id = $(this).prop("id").replace("delete-email-template-", ""), confirmDelete;
			
			confirmDelete = confirm("Are you sure that you want to delete this email template?");
			if(confirmDelete) {
				$.ajax({
					url      : HTTP_ADMIN + "config",
					type     : "POST",
					datatype : "json",
					data     : "adminRequest=" + encodeURIComponent(JSON.stringify({
						type        : "config",
						action      : "delete-email-template",
						id          : id,
						csrfToken   : $("#csrf-token").val()
					})),
					success  : function(data, textStatus, jqXHR) {
						var response = JSON.parse(data);
						if(typeof(response.templateDeleted) !== "undefined" && parseInt(response.templateDeleted) === 1) {
							$("#email-template-tab-" + id).remove();
							$("#verticalTabs-" + id).remove();
							$("#verticalTabs").tabs("refresh");
							$("#verticalTabs").tabs("option", "active", 0);
						}
					}
				});
			} else {
				return false;
			}
		});
	}
	
	// Clear error log
	$("#toolbar-clear-error-log").click(function(e) {
		e.preventDefault();
		var confirmDelete
		
		confirmDelete = confirm("Are you sure you want to clear the error log?");
		if(confirmDelete) {
			$.ajax({
				url      : HTTP_ADMIN + "config",
				type     : "POST",
				datatype : "json",
				data     : "adminRequest=" + encodeURIComponent(JSON.stringify({
					type        : "config",
					action      : "clear-error-log",
					csrfToken   : $("#csrf-token").val()
				})),
				success  : function(data, textStatus, jqXHR) {
					var response = JSON.parse(data);
					$("#error-log").html("There are currently no errors logged.");
				}
			});
		}
	});
	
	// Add cache dialog
	$("#add-cache-key-modal").dialog({
		width      : 800,
		modal      : true,
		autoOpen   : false,
		title      : 'Add key',
		open       : function() {
			hideMenus(true);
		},
		close      : function() {
			showMenus(true);
		},
		buttons : [
			{
				id: 'add-key',
				class: 'rb-btn blue-btn',
				text: 'Add Key',
				click: function(e) {
					$.ajax({
						url      : HTTP_ADMIN + "config",
						type     : "POST",
						datatype : "json",
						data     : "adminRequest=" + encodeURIComponent(JSON.stringify({
							type        : "config",
							action      : "add-cache-key",
							key         : $("#new-cache-key").val(),
							description : $("#new-cache-desc").val(),
							serialized  : $("input[name='keySerialized']").val(),
							csrfToken   : $("#csrf-token").val()
						})),
						success  : function(data, textStatus, jqXHR) {
							var response = JSON.parse(data);
							
							$("#add-cache-key-modal").dialog("close");
							$("#new-cache-key, #new-cache-desc").val("");
							
							$("#cache-table > tbody > .overview-top").after(
								'<tr id="key-' + response.keyId + '">' +
									'<td>' +
										'<input id="cb-' + response.keyId + '" type="checkbox" class="overview-check" name="cacheCheck[]" value="' + response.keyId + '" />' +
									'</td>' +
									'<td>' +
										'<a id="view-cache-' + response.keyId + '" class="view-cache" href="' + HTTP_ADMIN + 'config?cache-key=' + response.keyId + '" target="_blank">' +
											response.key +
										'</a>' +
									'</td>' +
									'<td>' +
										response.description +
									'</td>' +
								'</tr>'
							);
							
							// Hide the placeholder for when no cache keys are being tracked.
							$("#no-cache-keys").hide();
							$("#key-" + response.keyId).effect("highlight", "#F0EB00", 3000);
						}
					});
				}
			},
			
			{
				class: 'light-gray-btn',
				text: 'Cancel',
				click: function(e) {
					$("#add-cache-key-modal").dialog("close");
				}
			}
		]
	});
	
	// Add cache key click.
	$("#toolbar-add-cache-key").click(function(e) {
		$("#add-cache-key-modal").dialog("open");
	});
	
	// Delete cache key click.
	$("#toolbar").on("click", "#toolbar-delete-cache-key", function(e) {
		e.preventDefault();
		
		CMS.getCheck();
		
		var checked = CMS.ids;
		
		$.ajax({
			url      : HTTP_ADMIN + "config",
			type     : "POST",
			datatype : "json",
			data     : "adminRequest=" + encodeURIComponent(JSON.stringify({
				type        : "config",
				action      : "delete-cache-keys",
				ids         : checked,
				csrfToken   : $("#csrf-token").val()
			})),
			success  : function(data, textStatus, jqXHR) {
				var response = JSON.parse(data)
				  , x;
				
				for(x = 0; x <= response.ids.length; x++) {
					$("#cache-key-" + response.ids[x]).fadeOut( function() {
						$(this).remove();
						
						if($("#cache-table > tbody").children().length === 1) {
							$("#no-cache-keys").show();
						}
					});
				}
			}
		});
	});
	
	// Add task toolbar click.
	$("#toolbar-add-task").click(function(e) {
		// Clear any previous errors.
		$("#task-modal > .inner > .errors").html("");
		// Reset the form fields.
		$("#task-name, #task-desc, #cronjob").val("");
		$("#minutes, #hours, #days-of-the-month, #months, #days-of-the-week").val("*")
		// Show the add task button and hide the update task button.
		$("#add-task").show();
		$("#update-task").hide();
		// Open the task modal.
		$("#task-modal").dialog("open");
	});
	
	// Add scheduled task dialog
	$("#task-modal").dialog({
		width      : 800,
		modal      : true,
		autoOpen   : false,
		title      : 'Task Manager',
		open       : function() {
			hideMenus(true);
			translateCron();
		},
		close      : function() {
			showMenus(true);
			// Clear out the error box.
			$("#task-modal > .inner > .errors").html("");
		},
		buttons : [
			{
				id: 'add-task',
				class: 'rb-btn blue-btn',
				text: 'Add Task',
				click: function(e) {
					// Clear any errors from previous attempts.
					$("#task-modal > .inner > .errors").html("");
					createTask();
				}
			},
			{
				id: 'update-task',
				class: 'rb-btn blue-btn',
				text: 'Update Task',
				style: 'display: none;',
				click: function(e) {
					var id = $("#current-id").val();
					
					// Clear any previous errors.
					$("#task-modal > .inner > .errors").html("");
					
					updateTask(id);
				}
			},
			{
				class: 'rb-btn light-gray-btn',
				text: 'Cancel',
				click: function(e) {
					$("#task-modal").dialog("close");
				}
			}
		]
	});
	
	// Translate a cron job into readable english.
	$("#translate-cronjob").click(function(e) {
		e.preventDefault();
		translateCron();
	});
	
	// Open task modal when updating task
	$("#tasks").on("click", ".view-task", function(e) {
		e.preventDefault();
		var id = $(this).prop("id").replace("view-task-", "");
		$("#task-name").val($("#name-" + id).val());
		$("#task-desc").val($("#description-" + id).val());
		$("#cronjob").val($("#command-" + id).val());
		$("#minutes").val($("#minutes-" + id).val());
		$("#hours").val($("#hours-" + id).val());
		$("#days-of-month").val($("#days-of-month-" + id).val());
		$("#months").val($("#months-" + id).val());
		$("#days-of-week").val($("#days-of-week-" + id).val());
		if($("#log-" + id).val() == 1) {
			$("#log-file-true").prop("checked", true);
		} else {
			$("#log-file-false").prop("checked", true);
		}
		
		$("#add-task").hide();
		$("#update-task").show();
		
		$("#current-id").val(id);
		
		$("#task-modal").dialog("open");
	});
	
	$("#toolbar-delete-task").click(function(e) {
		e.preventDefault();
		// Get checked tasks
		CMS.getCheck();
		
		var checked = CMS.ids;
		
		$.ajax({
			url      : HTTP_ADMIN + "config",
			type     : "POST",
			datatype : "json",
			data     : "adminRequest=" + encodeURIComponent(JSON.stringify({
				type        : "config",
				action      : "delete-cronjob",
				ids         : checked,
				csrfToken   : $("#csrf-token").val()
			})),
			success  : function(data, textStatus, jqXHR) {
				var response = JSON.parse(data)
				  , x;
				
				for(x = 0; x <= response.ids.length; x++) {
					$("#task-" + response.ids[x]).fadeOut( function() {
						$(this).remove();
						
						if($("#tasks > tbody").children().length === 2) {
							$("#no-tasks").show();
						}
					});
				}
			}
		});
	});
	
	// Function that creates a task.
	function createTask() {
		$.ajax({
			url      : HTTP_ADMIN + "config",
			type     : "POST",
			datatype : "json",
			data     : "adminRequest=" + encodeURIComponent(JSON.stringify({
				type              : "config",
				action            : "add-cronjob",
				name              : $("#task-name").val(),
				description       : $("#task-desc").val(),
				command           : $("#cronjob").val(),
				minutes           : $("#minutes").val(),
				hours             : $("#hours").val(),
				daysOfMonth       : $("#days-of-month").val(),
				months            : $("#months").val(),
				daysOfWeek        : $("#days-of-week").val(),
				years             : $("#years").val(),
				log               : $("input[name='logFile']").val(),
				csrfToken         : $("#csrf-token").val()
			})),
			success  : function(data, textStatus, jqXHR) {
				var response = JSON.parse(data);
				
				if(typeof(response.taskId) !== "undefined") {
					$("#task-modal").dialog("close");
					
					$("#task-name, #task-desc, #cronjob, #minutes, #hours, #days-of-the-month, #months, #days-of-the-week").val(""),
					
					$("#tasks > tbody > .overview-top").after(
						'<tr id="task-' + response.taskId + '">' +
							'<td>' +
								'<input id="cb-' + response.taskId + '" type="checkbox" class="overview-check" name="taskCheck[]" value="' + response.taskId + '" />' +
							'</td>' +
							'<td>' +
								'<input type="hidden" id="minutes-' + response.taskId + '" value="' + response.minutes + '" />' +
								'<input type="hidden" id="hours-' + response.taskId + '" value="' + response.hours + '" />' +
								'<input type="hidden" id="days-of-month-' + response.taskId + '" value="' + response.daysOfMonth + '" />' +
								'<input type="hidden" id="months-' + response.taskId + '" value="' + response.months + '" />' +
								'<input type="hidden" id="days-of-week-' + response.taskId + '" value="' + response.daysOfWeek + '" />' +
								'<input type="hidden" id="command-' + response.taskId + '" value="' + response.command + '" />' +
								'<input type="hidden" id="name-' + response.taskId + '" value="' + response.name + '" />' +
								'<input type="hidden" id="description-' + response.taskId + '" value="' + response.description + '" />' +
								'<input type="hidden" id="log-' + response.taskId + '" value="' + response.log + '" />' +
								'<a id="view-task-' + response.taskId + '" class="view-task" href="#">' +
									response.name +
								'</a>' +
							'</td>' +
							'<td>' +
								response.description +
							'</td>' +
							'<td>' +
								response.cronjob +
							'</td>' +
						'</tr>'
					);
					
					// Hide the placeholder for when no cache keys are being tracked.
					$("#no-tasks").hide();
					$("#task-" + response.taskId).effect("highlight", "#F0EB00", 3000);
				} else if(typeof(response.errors) !== "undefined") {
					for(x = 0; x < response.errors.length; x++) {
						$("#task-modal > .inner > .errors").append(
							'<p>' +
								response.errors[x] +
							'</p>'
						);
					}
				}
			}
		});
	}
	
	// Function that updates a task.
	function updateTask(id) {
		$.ajax({
			url      : HTTP_ADMIN + "config",
			type     : "POST",
			datatype : "json",
			data     : "adminRequest=" + encodeURIComponent(JSON.stringify({
				type              : "config",
				action            : "update-cronjob",
				id                : id,
				name              : $("#task-name").val(),
				description       : $("#task-desc").val(),
				command           : $("#cronjob").val(),
				minutes           : $("#minutes").val(),
				hours             : $("#hours").val(),
				daysOfMonth       : $("#days-of-month").val(),
				months            : $("#months").val(),
				daysOfWeek        : $("#days-of-week").val(),
				years             : $("#years").val(),
				log               : $("input[name='logFile']").val(),
				csrfToken         : $("#csrf-token").val()
			})),
			success  : function(data, textStatus, jqXHR) {
				var response = JSON.parse(data), x;
				
				if(typeof(response.success) !== "undefined") {
					$("#task-modal").dialog("close");
					
					$("#task-name, #task-desc, #cronjob, #minutes, #hours, #days-of-the-month, #months, #days-of-the-week").val(""),
					
					$("#view-task-" + response.id + ", #name-" + response.id).html(response.name); 
					$("#description-" + response.id + ", #task-desc-" + response.id).html(response.description);
					$("#command-" + response.id + ", #task-command-" + response.id).html(response.command);
					$("#name-" + response.id).html(response.name);
					$("#desc-" + response.id).html(response.description);
					$("#minutes-" + response.id).html(response.minutes);
					$("#hours-" + response.id).html(response.hours);
					$("#days-of-month-" + response.id).html(response.daysOfMonth);
					$("#months-" + response.id).html(response.months);
					$("#days-of-week-" + response.id).html(response.daysOfWeek);
					$("#log-" + response.id).html(response.log);
					$("#command-" + response.id).html(response.command);
					
					// Hide the placeholder for when no cache keys are being tracked.
					$("#no-tasks").hide();
					$("#task-" + response.id).effect("highlight", "#F0EB00", 3000);
				} else if(typeof(response.errors) !== "undefined") {
					for(x = 0; x < response.errors.length; x++) {
						$("#task-modal > .inner > .errors").append(
							'<p>' +
								response.errors[x] +
							'</p>'
						);
					}
				}
				
			}
		});
	}

	function translateCron() {
		$.ajax({
			url      : HTTP_ADMIN + "config",
			type     : "POST",
			datatype : "json",
			data     : "adminRequest=" + encodeURIComponent(JSON.stringify({
				type       : "config",
				action     : "translate-cron",
				cronjob    : $("#minutes").val() + " " + $("#hours").val() + " " + $("#days-of-month").val() + " " + $("#months").val() + " " + $("#days-of-week").val() + " " + $("#years").val(),
				csrfToken  : $("#csrf-token").val()
			})),
			success  : function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				if(response.inEnglish) {
					$("#task-readable > .inner").html(
						'<span>This task will run </span><span class="translated-cron">' + response.inEnglish + '</span>'
					);
				} else if(response.error) {
					$("#task-readable > .inner").html(
						'<span class="error">Error: </span><span class="translated-cron error">' + response.error + '</span>'
					);
				}
			}
		});
	}
});
