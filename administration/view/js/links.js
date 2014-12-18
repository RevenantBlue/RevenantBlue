$(document).ready(function() {
	var noLinkError = "No links were selected. \n\nPlease select a link from the table below and try again.",
		noLinkCatError = "No link categories were selected. \n\nPlease select a link category from the table below and try again.";
	
	// On change events for the link overview filters.
	if($('#link-published-filter, #link-category-filter').length !== 0) {
		$('#link-published-filter').change(function() {
			$('#adminForm').submit();
		});
		$('#link-category-filter').change(function() {
			$('#adminForm').submit();
		});
	}
	
	// Set plupload info for the link profile image
	if($("#profile-image").length !== 0) {
		pluploadUrl = HTTP_ADMIN + 'links?adminRequest={"type":"link","action":"upload-image"}&csrfToken=' + $('#csrf-token').val();
		pluploadType = "link";
		pluploadAction = "upload-image";
		pluploadController = HTTP_ADMIN + "links?csrfToken=" + $('#csrf-token').val();
		deleteImgUrl = HTTP_ADMIN + "links";
	}
	
	// Publish/unpublish links
	$('.publish-link').click(function(e) {
		e.preventDefault();
	});

	// Dynamically add link category
	$('#add-link-category').click(function(e) {
		e.preventDefault();
		
		if($('#link-cat-name').val() === '') {
			return false;
		}
		
		$.ajax({
			url: HTTP_ADMIN + 'links',
			type: 'POST',
			datetype: 'json',
			data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
				type      : 'link',
				action    : 'add-link-category',
				name      : $('#link-cat-name').val(),
				alias     : $('#link-cat-alias').val(),
				desc      : $('#link-cat-desc').val(),
				csrfToken : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				
				var order = $("#link-categories > tbody").children().length + 1;
				// Clear form fields
				$('#link-cat-name, #link-cat-alias, #link-cat-desc').val('');
				
				// Append new link category.
				if(typeof(response.id) !== 'undefined') {
					$("#link-categories > tbody").append(
						'<tr id="overview-row-' + response.id + '">' +
							'<td>' +
								'<input id="cb-' + response.id + '" type="checkbox" class="overview-check" name="linkCategoryCheck[]" value="' + response.id + '" />' +
							'</td>' +
							'<td class="left">' +
								'<a href="' + HTTP_ADMIN + 'links/categories/' + response.id + '">' +
									response.name +
								'</a>' +
							'</td>' +
							'<td>' +
								'<input type="text" name="orderNumber" size="1" maxlength="4" value="' + order + '" />' +
							'</td>' +
						'</tr>'
					);
					
					if($(".option-64").is(":visible")) {
						$("#overview-row-" + response.id).append(
							'<td class="option-64">' +
								0 +
							'</td>'
						);
					} else {
						$("#overview-row-" + response.id).append(
							'<td class="option-64" style="display: none;">' +
								0 +
							'</td>'
						);
					}
					
					if($(".option-83").is(":visible")) {
						$("#overview-row-" + response.id).append(
							'<td class="option-64">' +
								response.id +
							'</td>'
						);
					} else {
						$("#overview-row-" + response.id).append(
							'<td class="option-64" style="display: none;">' +
								response.id +
							'</td>'
						);
					}
				}
			}
		});
	});
	$("#toolbar-edit-link, #action-edit-link").click(function(e) {
		e.preventDefault();
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noLinkError);
		} else {
			CMS.submitButton('link', 'edit');
		}
	});
	// Publish link clicks.
	$("#toolbar-publish-link, #action-publish-link").click(function(e) {
		e.preventDefault();
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noLinkError);
		} else {
			CMS.submitButton('link', 'publish');
		}
	});
	// Unpublish link clicks.
	$("#toolbar-unpublish-link, #action-unpublish-link").click(function(e) {
		e.preventDefault();
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noLinkError);
		} else {
			CMS.submitButton('link', 'unpublish');
		}
	});
	// Delete link clicks.
	$("#toolbar-delete-link, #action-delete-link").click(function(e) {
		e.preventDefault();
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noLinkError);
		} else {
			CMS.submitButton("link", "delete");
		}
	});
	// Publish link button clicks.
	$(".publish-link-content").click(function(e) {
		e.preventDefault();
		$("#link-state").val(1);
		CMS.submitButton("link", "save-close");
	});
	// Draft link button clicks
	$(".draft-link-content").click(function(e) {
		e.preventDefault();
		$("#link-state").val(2);
		CMS.submitButton("link", "save");
	});
	// Save link clicks
	$("#toolbar-save-link, #action-save-link").click(function(e) {
		e.preventDefault();
		CMS.submitButton("link", "save");
	});
	// Save and new link clicks
	$("#toolbar-save-new-link, #action-save-new-link").click(function(e) {
		e.preventDefault();
		CMS.submitButton("link", "save-new");
	});
	// Save and close link clicks
	$("#toolbar-save-close-link, #action-save-close-link").click(function(e) {
		e.preventDefault();
		selectNavItem("#links-link", "#new-link-link");
		CMS.submitButton("link", "save-close");
	});
	// Close the link editor clicks
	$("#toolbar-close-link, #action-close-link").click(function(e) {
		e.preventDefault();
		selectNavItem("#links-link", "#new-link-link");
		window.location.href = HTTP_ADMIN + "links";
	});
	$(".quick-publish-link").click(function(e) {
		e.preventDefault();
		$("#link-state").val(1);
		CMS.submitButton("link", "save-close");
	});
	// Edit link category clicks.
	$("#toolbar-edit-link-category, #action-edit-link-category").click(function(e) {
		e.preventDefault();
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noLinkCatError);
		} else {
			CMS.submitButton("link category", "edit");
		}
	});
	// Delete link category clicks.
	$("#toolbar-delete-link-category, #action-delete-link-category").click(function(e) {
		e.preventDefault();
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noLinkCatError);
		} else {
			CMS.submitButton('link category', 'delete');
		}
	});
	// Save link category clicks
	$("#toolbar-save-link-category, #action-save-link-category").click(function(e) {
		e.preventDefault();
		CMS.submitButton("link category", "save");
	});
	// Save and new link category clicks
	$("#toolbar-save-new-link-category, #action-save-new-link-category").click(function(e) {
		e.preventDefault();
		CMS.submitButton("link category", "save-new");
	});
	// Save and close link category clicks
	$("#toolbar-save-close-link-category, #action-save-close-link-category").click(function(e) {
		e.preventDefault();
		selectNavItem("#link-categories-link", "");
		CMS.submitButton("link category", "save-close");
	});
	// Close the link category editor clicks
	$("#toolbar-close-link-category, #action-close-link-category").click(function(e) {
		e.preventDefault();
		selectNavItem("#link-categories-link", "");
		window.location.href = HTTP_ADMIN + "links/categories";
	});
	
	// Click event when a category is checked in the link profile.
	$(".category-check").click(function() {
		var categoryId;
		if($(this).hasClass("popular-category")) {
			categoryId = $(this).prop("id").replace("pop-cat-", "");
			
			if(!$(this).is(":checked")) {
				$("#pop-cat-" + categoryId + ", #cat-" + categoryId).prop(false);
			} else {
				$("#pop-cat-" + categoryId + ", #cat-" + categoryId).prop("checked", true);
			}
		} else {
			categoryId = $(this).prop("id").replace("cat-", "");
			
			if(!$(this).is(":checked")) {
				$("#cat-" + categoryId + ", #pop-cat-" + categoryId).prop("checked", false);
			} else {
				$("#cat-" + categoryId + ", #pop-cat-" + categoryId).prop("checked", true);
			}
		}
	});

	// Link profile category selector
	$("#submit-new-category").click(function(e) {
		e.preventDefault();
		if($("#new-category-name").val() === "") {
			return false;
		}

		$.ajax({
			url: HTTP_ADMIN + 'links',
			type: 'POST',
			datatype: 'json',
			data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
				type           : 'link',
				action         : 'add-link-category',
				id             : "",
				name           : $("#new-category-name").val(),
				alias          : "",
				desc           : "",
				csrfToken      : $('#csrf-token').val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				var category, myList, catCheck, catLabel, catOption, id, parentSelect;
				
				// Prepend the new category li element
				$("#category-check-list-all").prepend(
					'<li id="category-li-' + response.id + '"></li>'
				);
				
				// Add the label and checkbox to the newly created li element for the new link category.
				$("#category-li-" + response.id).append(
					'<label id="category-label-' + response.id + '" class="center-label">' +
						'<input type="checkbox" id="cat-' + response.id + '" class="center-toggle" value="' + response.id + '" name="linkCategories[]" />' +
						response.name +
					'</label>'
				);
				
				$('#category-label-' + response.id).focus();
				$('#category-li-' + response.id).effect('highlight', '#CBC825', 1000);
				
				$('#new-category-name').val('');
				$('#new-category-parent').prop("selectedIndex", 0);
			}
		});
		return false;
	});

	//Initialize the tabs for the category selector.
	if($("#categories-tabs").length !== 0) {
		$("#categories-tabs").tabs();
	}
	if($("#category-adder").length !== 0) {
		$(".new-category-panel").hide();
		$("#category-add-toggle").click(function(e) {
			e.preventDefault();
			$(".new-category-panel").slideToggle();
		});
	}

	// Click event when a category is checked.
	$(".link-category").click(function(e) {
		e.preventDefault();
		var categoryId;
		if($(this).hasClass("popular-category")) {
			categoryId = $(this).prop("id").replace("pop-cat-", "");
			
			if(!$(this).is(":checked")) {
				$("#pop-cat-" + categoryId + ", #cat-" + categoryId).prop("checked", false);
			} else {
				$("#pop-cat-" + categoryId + ", #cat-" + categoryId).prop("checked", true);
			}
		} else {
			categoryId = $(this).prop("id").replace("cat-", "");
			
			if(!$(this).is(":checked")) {
				$("#cat-" + categoryId + ", #pop-cat-" + categoryId).prop("checked", false);
			} else {
				$("#cat-" + categoryId + ", #pop-cat-" + categoryId).prop("checked", true);
			}
		}
	});
	
	if($("#link-categories").length !== 0) {
		//console.log('here');
		getOrder = getURLParameter("order");
		searchParam = getURLParameter("search");
		publishParam = getURLParameter("state");
		featureParam = getURLParameter("featured");
		// Only enable table drag and drop if the default ordering is used.
		if(getOrder === null) {
			$('#link-categories').tableDnD({
				onDragClass : 'tableDnD-drag',
				onDrop : function(table, row) {
					orderFields = document.getElementsByName('orderNumber');
					for(x = 0; x < orderFields.length; x++ ) {
						orderFields[x].value = (x + 1);
					}
					$.ajax({
						url      : HTTP_ADMIN + 'links',
						type     : "post",
						datatype : 'json',
						data     : "adminRequest=" + encodeURIComponent(JSON.stringify({
							type      : 'link-category',
							action    : 'reorder',
							order     : $.tableDnD.serialize(),
							csrfToken : $("#csrf-token").val()
						})),
						success: function(data, textStatus, jqXHR) {
							
						}
					});
				},
			});
		}
	}
});
