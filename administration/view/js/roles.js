// JavaScript Document
$(document).ready(function() {
	var elements, element, group, x
	  , noRoleError = "No role was selected. \n\nPlease select a role from the table below and try again.";
	
	$(document).on("click", ".icon-20-expand", function() {
		var moduleGroup = "." + $(this).prop("id");
		$(this).removeClass("icon-20-expand").addClass("icon-20-collapse");
		$(moduleGroup).show();
	});
	$(document).on("click", ".icon-20-collapse", function() {	
		var moduleGroup = "." + $(this).prop("id");
		$(this).removeClass("icon-20-collapse").addClass("icon-20-expand");
		$(moduleGroup).hide();
	});
	$('#expand-role-table').click(function() {
		$(".role-head, .role-permissions").show();
		$(".icon-20-expand").removeClass("icon-20-expand").addClass("icon-20-collapse");
	});
	$('#collapse-role-table').click(function() {
		$(".role-head, .role-permissions").hide();
		$(".icon-20-collapse").removeClass("icon-20-collapse").addClass("icon-20-expand");
	});
	// Edit role clicks.
	$("#toolbar-edit-role, #action-edit-role").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noRoleError); 
		} else { 
			CMS.submitButton("role", "edit"); 
		}
	});
	// Delete role clicks.
	$("#toolbar-delete-role, #action-delete-role").click(function() {
		var approveDel;
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noRoleError);
		} else { 
			approveDel = confirm("Are you sure that you want to delete the selected role(s)?");
			if(approveDel) {
				CMS.submitButton("role", "delete");
			}
		}
	});
	// Create role button clicks.
	$(".publish-role-content").click(function() {
		$("#role-state").val(1);
		CMS.submitButton("role", "save-close");
	});
	// Save role clicks
	$("#toolbar-save-role, #action-save-role").click(function() {
		CMS.submitButton("role", "save");
	});
	// Save and new role clicks
	$("#toolbar-save-new, #action-save-new-role").click(function() {
		CMS.submitButton("role", "save-new");
	});
	// Save and close role clicks
	$("#toolbar-save-close, #action-save-close-role").click(function() {
		selectNavItem("#roles-link", "#new-role-link");
		CMS.submitButton("role", "save-close");
	});
	// Close the role editor clicks
	$("#toolbar-close-role, #action-close-role").click(function() {
		selectNavItem("#roles-link", "#new-role-link");
		window.location.href = HTTP_ADMIN + "roles";
	});
	// Save role perms clicks
	$("#toolbar-save-role-perms, #action-save-role-perms").click(function() {
		CMS.submitButton("role-perms", "save");
	});
	// Save and close role clicks
	$("#toolbar-save-close-role-perms, #action-save-close-role-perms").click(function() {
		selectNavItem("#roles-link", "#new-role-link");
		CMS.submitButton("role-perms", "save-close");
	});
	
	// Add permission click
	$("#add-permission").click(function(e) {
		e.preventDefault();
		$("#add-permission-dialog").dialog("open");
		console.log('here');
	});
	
	$("#add-permission-dialog").dialog({
		autoOpen    : false,
		height      : 400,
		width       : 700,
		modal       : true,
		title       : 'Add Permission',
		dialogClass : "popup",
		open: function() {
			hideMenus();
		},
		close: function() {
			$(this).dialog("close");
			showMenus();
		},
		buttons     : [
			{
				text  : "Add Permission",
				name  : 'addPermission',
				type  : 'submit',
				click : function() {
					$(this).dialog("close");
					showMenus();
					$("#add-permission-form").submit();
				}
			},
			
			{
				text  : "Close",
				click : function() {
					$(this).dialog("close");
					showMenus();
				}
			}
		]
	});
	
	// Drag and drop role order
	$('#overview').tableDnD({
		onDragClass : 'tableDnD-drag',
		onDragStart : function() {
			console.log('dragging');
		},
		onDrop : function(table, row) {
			var numOfRoles = $('.role-rank').length - 1
			  , ranks = {}
			  , x;
			  
			x = numOfRoles;
			
			console.log($.tableDnD.serialize());
			
			$('.role-rank').each(function() {
				var currentRank = parseInt($(this).text(), 10);
				if(currentRank !== 0) {
					ranks[x] = $(this).data('id');
					$(this).text(x);
				} else {
					return;
				}
				x -= 1;
			});
			
			console.log(ranks);
			$.ajax({
				url   : HTTP_ADMIN + 'users/roles',
				type  : "post",
				datatype : 'json',
				data  : "adminRequest=" + encodeURIComponent(JSON.stringify({
					type       : 'role',
					action     : 'set-ranks',
					ranks      : ranks,
					csrfToken  : $("#csrf-token").val()
				}))
			});
		},
	});
});
