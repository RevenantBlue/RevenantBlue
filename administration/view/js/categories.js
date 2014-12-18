// JavaScript Document
$(document).ready(function() {
	var noCatAlert = "No categories selected, please select a category from the table below and try again.";
	
	// Build the sortable category list if the categoriesJSON variable is not undefined
	if(typeof(categories) !== "undefined") {
		buildCategories(categories, false, function() {
			var previousParent
			  , currentParent;
			
			$("#categories-sortable").nestedSortable({
				handle           : ".dd-handle",
				items            : "li.category-node",
				toleranceElement : "> div",
				protectRoot      : true,
				maxLevels        : 10,
				placeholder      : 'placeholder',
				start            : function(event, ui) {
					if($(ui.item).hasClass("root")) {
						previousParent = $(ui.item).prop("id");
					} else {
						previousParent = $(ui.item).parents(".root").prop("id");
					}

					// Set the placeholder width and height to match that of the dragged category.
					ui.placeholder.height(ui.item.height());
					ui.placeholder.width(ui.item.width() - 35);
					ui.placeholder.css("margin-left", ui.item.css("margin-left"));
				},
				update           : function(event, ui) {
					// Send updated hierarchy to the server for ordering.
					var categoryHierarchy = $("#categories-sortable").nestedSortable("toHierarchy")
					  , currentParent
					  , currentId
					  , id;
					
					if($(ui.item).hasClass("root")) {
						currentParent = $(ui.item).prop("id");
					} else {
						currentParent = $(ui.item).parents(".root").prop("id");
					}
					currentId = currentParent.replace("category-", "");
					//console.log(categoryHierarchy);
					// Remove placeholder if it exists
					if($("#category-" + currentId).has(".add-node-holder").length) {
						console.log("Removing Placeholder");
						$("#category-" + currentId + " > #category-" + currentId + "-children > .node-placeholder").remove();
					}
					$.ajax({
						url: HTTP_ADMIN + 'categories',
						type: 'POST',
						datatype: 'json',
						data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
							type              : "category",
							action            : "reorder",
							categoryHierarchy : categoryHierarchy,
							csrfToken         : $('#csrf-token').val()
						})),
						success: function(data, textStatus, jqXHR) {
							var response = JSON.parse(data);
							//console.log(data);
						}
					});
					// Show add a subcategory to an empty root category, skip the unassigned root category though.
					if($("#" + previousParent + " > ol").children().length === 0) {
						prevId = previousParent.replace("category-", "");
						if(prevId != 1) {
							$("#category-" + prevId + "-children").append(
								'<li class="node-child node-depth-1 node-placeholder">' +
									'<div class="child-node clearfix add-node-holder">' +
										'<span>No subcategories exist for this category. </span>' +
										'<a href="' + HTTP_ADMIN + 'categories/new?parent=' + prevId + '">Add Subcategory</a>' +
									'</div>' +
								'</li>'
							);
						}
					}
				}
			});
			// If a category does not have any subcategories show a row letting a user add one, skip the unassigned category though.
			$(".root").each(function(index, value) {
				var id = $(this).prop("id").replace("category-", "");
				if($(this).has(".child-node").length === 0) {
					if(id != 1) {
						$("#category-" + id + "-children").append(
							'<li class="node-child node-depth-1 node-placeholder">' +
								'<div class="child-node clearfix add-node-holder">' +
									'<span>No subcategories exist for this category. </span>' +
									'<a href="' + HTTP_ADMIN + 'categories/new?parent=' + id + '">Add Subcategory</a>' +
								'</div>' +
							'</li>'
						);
					}
				}
			});
		});
	}
	
	// Build the datetime picker
	if($('#date-posted').length != 0) {	
		$('#date-posted').datetimepicker({
				showOn: "both",
				buttonImage: HTTP_IMAGE + "icons/admin/calendar_icon2.png",
				buttonImageOnly: true,
				ampm: true,
				dateFormat: 'yy-mm-dd',
				timeFormat: 'hh:mm TT'
		});
	}
	
	// Set plupload info for the link profile image
	if($("#profile-image").length !== 0) {
		pluploadUrl =  HTTP_ADMIN + 'categories?adminRequest={"type":"category","action":"upload-image"}&csrfToken=' + $('#csrf-token').val();
		pluploadType = "category";
		pluploadAction = "upload-image";
		pluploadController = HTTP_ADMIN + "categories?csrfToken=" + $('#csrf-token').val();
		deleteImgUrl = HTTP_ADMIN + "categories";
	}
	
	// Edit category clicks.
	$("#toolbar-edit, #action-edit-category").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noCatAlert); 
		} else { 
			CMS.submitButton('category', 'edit');
		}
	});
	// Publish category clicks.
	$("#toolbar-publish, #action-publish-category").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noCatAlert); 
		} else { 
			CMS.submitButton('category', 'publish');
		}
	});
	// Unpublish category clicks.
	$("#toolbar-unpublish, #action-unpublish-category").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noCatAlert); 
		} else { 
			CMS.submitButton('category', 'unpublish');
		}
	});
	// Delete category clicks.
	$("#toolbar-delete, #action-delete-category").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) { 
			alert(noCatAlert);
		} else { 
			CMS.submitButton('category', 'delete');
		}
	});
	// Publish category button clicks.
	$(".publish-category-content").click(function() {
		$("#category-state").val(1);
		CMS.submitButton("category", "save-close");
	});
	// Save category clicks
	$("#toolbar-save, #action-save-category").click(function() {
		CMS.submitButton("category", "save");
	});
	// Save and new category clicks
	$("#toolbar-save-new, #action-save-new-category").click(function() {
		CMS.submitButton("category", "save-new");
	});
	// Save and close category clicks
	$("#toolbar-save-close, #action-save-close-category").click(function() {
		selectNavItem("#categories-link", "#new-category-link");
		CMS.submitButton("category", "save-close");
	});
	// Close the category editor clicks
	$("#toolbar-close-category, #action-close-category").click(function() {
		selectNavItem("#categories-link", "#new-category-link");
		window.location.href = HTTP_ADMIN + "categories";
	});
});

// Build category sortable
function buildCategories(categories, isChild, callback) {
	for(category in categories) {
		//console.log(categories[category].cat_id);
		//console.log(categories.id);
		//console.log(categories);
		//console.log(category);
		if(!isChild && categories[category].id) {
			$("#categories-sortable").append(
				'<li id="category-' + categories[category].id + '" class="root category-node">' + 
					'<div class="root-node clearfix">' +
						'<div class="node-desc">' +
							'<div class="show-handle expand"></div>' +
							'<div class="dd-handle root-handle sprites"></div>' +
							'<div class="node-title">' + 
								'<a href="' + HTTP_ADMIN + 'categories/' + categories[category].id + '">' + categories[category].name + '</a>' +
							'</div>' +
						'</div>' +
						'<div class="node-actions">' +
							'<span id="featured-' + categories[category].id + '" class="icon-20-spacing fltrght category-featured"></span>' +
							'<span id="published-' + categories[category].id + '" class="icon-20-spacing fltrght category-published"></span>' +
							'<span class="category-checkbox node-checkbox">' +
								'<input id="cb-' + categories[category].id + '" type="checkbox" class="overview-check root-check" name="categoryCheck[]" value="' + categories[category].id + '" />' +
							'</span>' +
						'</div>' +
					'</div>' +
				'</li>'
			);
			setCategoryAttribs(categories[category]);
			if(typeof(categories[category].children) === "object") {
				$("#category-" + categories[category].id).append(
					'<ol id="category-' + categories[category].id + '-children">' +
					
					'</ol>'
				);
				for(child in categories[category].children) {
					//console.log(categories[category].children);
					buildCategories(categories[category].children, true);
				}
			}
		} else {
			//console.log(categories);
			if(categories[category].id) {
				$("#category-" + categories[category].ancestor + "-children").append(
					'<li id="category-' + categories[category].id + '" class="node-child category-node node-depth-1">' +
						'<div class="child-node clearfix">' +
							'<div class="node-desc">' +
								'<div class="dd-handle sprites"></div>' +
								'<div class="node-title">' + 
									'<a href="' + HTTP_ADMIN + 'categories/' + categories[category].id + '">' + categories[category].name + '</a>' +
								'</div>' +
							'</div>' +
							'<div class="node-actions">' +
								'<span id="featured-' + categories[category].id + '" class="icon-20-spacing category-featured"></span>' +
								'<span id="published-' + categories[category].id + '" class="icon-20-spacing category-published"></span>' +
								'<span class="category-checkbox node-checkbox">' +
									'<input id="cb-' + categories[category].id + '" type="checkbox" class="overview-check" name="categoryCheck[]" value="' + categories[category].id + '" />' +
								'</span>' +
							'</div>' +
						'</div>' +
					'</li>'
				);
			}
			setCategoryAttribs(categories[category]);
			if(typeof(categories[category].children) === "object") {
				$("#category-" + categories[category].id).append(
					'<ol id="category-' + categories[category].id + '-children">' +
					
					'</ol>'
				);
				for(child in categories[category].children) {
					//console.log(categories[category].children);
					//console.log(typeof(categories[category].children));
					if(typeof(categories[category].children) === "object") {
						buildCategories(categories[category].children, true);
					}
				}
			}
		}
	}
	if(typeof(callback) === 'function') {
		callback();
	}
}

function setCategoryAttribs(category) {
	// Set the published icon.
	if(category.published == 1) {
		$("#published-" + category.id).addClass("icon-20-check");
	} else {
		$("#published-" + category.id).addClass("icon-20-disabled");
	}
	// Set the featured icon.
	if(category.featured == 1) {
		$("#featured-" + category.id).addClass("icon-20-star");
	} else {
		$("#featured-" + category.id).addClass("icon-20-gray-disabled");
	}
}

// Toggling show handle on root nodes.
$(document).on("click", ".show-handle", function(e) {
	e.stopPropagation();
	if($(this).hasClass("expand")) {
		$(this).parents(".root").children("ol").hide();
		$(this).removeClass("expand").addClass("collapse");
	} else {
		$(this).parents(".root").children("ol").show();
		$(this).removeClass("collapse").addClass("expand");
	}
});
