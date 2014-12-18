var currentUrl, boxesChecked = 0;

$(document).ready(function() {
	// Set the document title for the parent window and the page itself
	$(window).load(function() {
		parent.document.title = rbTitle;
	});

	// check local storage for nav visibility.
	if(localStorage.leftNavVisible === '1') {

	} else if(localStorage.leftNavVisible === '0') {
		window.parent.$("#left-nav, #vert-separator, #vert-separator-fix").hide();
		$("#fixed-inner").css("margin", "55px 10px 54px 15px");
		window.parent.$("#left-nav").css("left", "-220px");
		window.parent.$("#vert-separator, #vert-separator-fix").css("left", "-1px");
		window.parent.$("#nav-toggle").html("›");
	}

	// Reset adminForm
	$("#adminForm").trigger("reset")

	// Buttons
	$(".rb-btn").button();

	// Ensure that the iframe matches the parent document height.
	window.parent.$(window).resize(function() {
	   //window.parent.$("#main-iframe").height(window.parent.document.documentElement.clientHeight);
	});

	// Dashboard link clicks
	$(".dash-articles-links").click(function() {
		selectMenuItem("#articles-link", "#home-link", "selected selected-border");
	});

	// Action menu behavior
	if($("#action-menu").is(":visible")) {
		$("#action-menu").hide();
		$("#action-menu-link").css("border-bottom", "1px solid #C0C0C0;");
	}
	$('#action-menu').menu({
		select: function(e, ui) {
			if($(ui).hasClass("action-no-close")) {

			} else {
				// Hide the menu after a selection has been made.
				$("#action-menu").hide();
			}

		},
		icons: { submenu: "ui-icon-triangle-1-e" }
	});

	// Toggle the action menu on click.
	$("#action-menu-link").click(function() {
		$("#action-menu").toggle();
	});

	// Hide the menu when clicking outside of it after it has been opened.
	$(document).mouseup(function (e) {
		var mainActionMenu = window.parent.$("#main-user-actionmenu")
		  , mainActionMenuLink = $(e.target).parents("#fixed-wrap").length
		  , actionMenu =  $("#action-menu")
		  , moderatorPanelsVisible = $(".forum-moderators:visible").length;
		//console.log(e.target);
		//console.log(actionMenuLink);
		// Hide main action meny when clicking anywhere inside the iframe.
		if(!$(e.target).has(actionMenu) || actionMenu.is(":visible")) {
			window.setTimeout(function() {
				actionMenu.hide();
			}, 10);
		}
		// Hide action meny when clicking anywhere inside the iframe.
		if(mainActionMenuLink || mainActionMenu.is(":visible")) {
			window.setTimeout(function() {
				mainActionMenu.hide();
			}, 10);
		}
		// Hide forum moderators
		//console.log($(".forum-moderators").has(e.target).length);
		//console.log(moderatorPanelsVisible);
		if(($(".forum-moderators").has(e.target).length === 0) && moderatorPanelsVisible !== 0) {
			// Micro timeout to override the main-actions click function from firing.
			window.setTimeout(function() {
				$(".moderator-wrap").hide();
			}, 10);
		}
	});

	// Ajax options change
	$(document).on("click", ".optionChange", function() {
		var optionsStatus, idForOption = $(this).attr('id');
		if($('#' + idForOption).is(':checked')) {
			optionStatus = 'on';
		} else {
			optionStatus = 'off';
		}
		idForOption = idForOption.replace('option-', '');
		adminObj = {
			type      : 'options',
			action    : 'change',
			id        : idForOption,
			status    : optionStatus,
			csrfToken : $('#csrf-token').val()
		};
		jsonStr = JSON.stringify(adminObj);
		$.ajax({
			 type: 'POST',
			 url: HTTP_ADMIN + 'articles',
			 datatype: 'json',
			 data: 'adminRequestGlobal=' + encodeURIComponent(jsonStr),
			 success: function(data, textStatus, jqXHR) {
			 	response = JSON.parse(data);
				if(response.success && response.status === 'on') {
					$('.option-' + response.id).attr('style', '');
					$("#screen-option-" + response.id + " > span").addClass("ui-icon ui-icon-check");
				} else if(response.success && response.status === 'off') {
					$('.option-' + response.id).attr('style', 'display: none;');
					$("#screen-option-" + response.id + " > span").removeClass("ui-icon ui-icon-check");
				}
			 }
		});
	});

	$(document).on("click", ".screen-option", function() {
		var optionStatus, idForOption = $(this).prop('id');
		if($("#" + idForOption + " > span").hasClass("ui-icon-check")) {
			optionStatus = "off";
		} else {
			optionStatus = "on";
		}
		idForOption = idForOption.replace("screen-option-", "");
		adminObj = {
			type      : "options",
			action    : "change",
			id        : idForOption,
			status    : optionStatus,
			csrfToken : $("#csrf-token").val()
		};
		jsonStr = JSON.stringify(adminObj);
		$.ajax({
			type: "POST",
			url: HTTP_ADMIN + "articles",
			datatype: "json",
			data: "adminRequestGlobal=" + encodeURIComponent(jsonStr),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				if(response.success && response.status === "on") {
					$(".option-" + response.id).attr("style", "");
					$("#option-" + response.id).prop("checked", true);
					$("#screen-option-" + response.id + " > span").addClass("ui-icon ui-icon-check");
				} else if(response.success && response.status === "off") {
					$(".option-" + response.id).attr("style", "display: none;");
					$("#option-" + response.id).prop("checked", false);
					$("#screen-option-" + response.id + " > span").removeClass("ui-icon ui-icon-check");
				}
			}
		});
	});

	// jqueryUI modal window for the options form.
	if($("#options").length !== 0) {
		$("#options-form").dialog({
			autoOpen: false,
			width: 700,
			height: 600,
			modal: true,
			dialogClass: "options",
			open: function() {
				$(".ui-widget-overlay").bind("click", function(){
					$("#options-form").dialog("close");
				});
				hideMenus(true);
			},
			close: function() {
				showMenus();
			},
			buttons: {
				Close: function() {
					$( this ).dialog( "close" );
				},
			}
		});

		$("#options").click(function() {
			$("#options-form").dialog("open");
		});
	}

	// Closing notifications clicks
	$(".success > span.ui-icon-close").click(function() {
		$(this).parent().remove();
	});
	$(".errors > span.ui-icon-close").click(function() {
		$(this).parent().remove();
	});

	// Tabbing from title to tinymce editor fix
	$('#title').bind('keydown.editor-focus', function(e) {
		var ed
		  , editorId;

		if($("#content-editor").length !== 0) {
			editorId = "content-editor";
		} else if($("#description-editor").length !== 0) {
			editorId = "description-editor";
		}

		if(e.which != 9) {
			return;
		}
		if(!e.ctrlKey && !e.altKey && !e.shiftKey) {
			if(typeof(tinymce) !== "undefined")
				ed = tinymce.get(editorId);

			if(ed && !ed.isHidden()) {
				$("#" + editorId + "_tbl td.mceToolbar > a").focus();
			} else
				$("#" + editorId).focus();
				e.preventDefault();
			}
		});
	});

// Overview check clicks
$(document).on("click", ".overview-check", function(e) {
	e.stopPropagation();
	CMS.isChecked(this);
	//console.log(boxesChecked);
});
$(document).on("click", ".overview-check-all", function(e) {
	e.stopPropagation();
	CMS.checkAll();
});

function selectNavItem(selectedNav, previousNav) {
	var navHref = $(selectedNav).prop("href");
	window.parent.$(selectedNav).addClass("selected selected-border");
	window.parent.$(previousNav).removeClass("selected selected-border");
	window.parent.$(selectedNav).parent(".submenu").show();
	window.parent.$(selectedNav).closest($(".top-nav > a > .nav-arr")).addClass("nav-darr");
}

// Select a left nav item programatially.
function selectMenuItem(id, prevId, classes) {
	window.parent.$(prevId).removeClass("selected selected-border selected-no-bottom selected-no-top");
	window.parent.$(id).addClass(classes).siblings(".submenu").show();
	window.parent.$(id + " > .nav-arr").addClass("nav-darr");
}

// Show and hide the main menu for fullscreen purposes
function hideMenus(quick) {
	if(quick === true) {
		window.parent.$("#left-nav, #nav-toggle, #vert-separator, #vert-separator-fix, #admin-banner").hide();
	} else {
		window.parent.$("#left-nav, #nav-toggle, #vert-separator, #vert-separator-fix, #admin-banner").fadeOut(1000);
	}
}
function showMenus(quick) {
	var leftNavMargin;
	if(quick) {
		window.parent.$("#nav-toggle, #admin-banner").show();
	} else {
		window.parent.$("#nav-toggle, #admin-banner").fadeIn(1000);
	}
	leftNavMargin = window.parent.$("#left-nav").css("left");
	leftNavOuter = "-" + window.parent.$("#left-nav").outerWidth() + "px";
	if(leftNavMargin !== leftNavOuter) {
		if(quick) {
			window.parent.$("#left-nav, #vert-separator, #vert-separator-fix").show();
		} else {
			window.parent.$("#left-nav, #vert-separator, #vert-separator-fix").fadeIn(1000);
		}
	}
}

// Breadcrumb clicks

$(document).on("click", ".breadcrumb", function(e) {
	var prevId = "#" + window.parent.$("#left-nav-main li a.selected").prop("id")
	  , id = "#" + $(this).prop("id").replace("bc-", "") + "-link";
	selectMenuItem(id, prevId, "selected");

});


CMS = {

	adminObj  : {},  // Contains the methods and properties that will be sent to the backend via ajax and JSON.
	inputs    : [],  // Contains an array of elements for the currently selected form.
	ids       : [],  // Contains the user ids that have been selected for the action.

	isChecked : function(field) {

		boxesChecked = 0;

		$(".overview-check").each(function() {
			if($(this).is(":checked")) {
				boxesChecked += 1;
			}
		});

		return boxesChecked;
	},

	getCheck : function() {
		var x = 0;
		// Clear the checked box count and ids property.
		boxesChecked = 0;
		this.ids = [];
		// Get the user ids from the value of the checked checkboxes.
		this.inputs = document.getElementById('adminForm').elements;
		for(x = 0; x < this.inputs.length; x++) {
			if(this.inputs[x].type === 'checkbox' && this.inputs[x].checked === true) {
				this.ids[x] = this.inputs[x].value;
			}
		}
		// Remove undefined elements.
		this.ids = $.grep(this.ids,function(n) { return(n) });
	},

	checkAll :  function() {
		var selectAll = document.getElementById('selectAll')
		  , inputs = document.getElementById('adminForm').elements, x = 0;
		if(selectAll.checked === true) {
			for(x = 0; x < inputs.length; x++) {
				inputs[x].checked = true;
			}
			boxesChecked = Math.ceil(inputs.length / 2);
		} else {
			for(x = 0; x < inputs.length; x++) {
				inputs[x].checked = false;
			}
			boxesChecked = 0;
		}
	},

	clearForm : function (formId) {
		var elements = formId.elements, i = 0;
		// Iterate through the form's elements settings their values to null.
		for(i = 0; i < elements.length; i++) {
			fieldType = elements[i].type.toLowerCase();
			switch(fieldType) {
				case 'text':
				case 'password':
				case 'textarea':
					elements[i].value = '';
					break;
				case 'radio':
				case 'checkbox':
					if(elements[i].checked) {
						elements[i].checked = false;
					}
					break;
				case 'select-one':
				case 'select-multi':
					elements[i].selectedIndex = -1;
					break;
				default:
					break;
			}
		}
		formId.reset();
	},

	submitButton : function(type, action) {
		// The submit button method is used as a global catch for form submissions.
		// All submissions end with a hard submit of the main admin form.
		// AJAX requests return false to prevent the submission of the form.

		var jsonStr, response, deleteConfirm, x = 0;
		// Handle logout request
		if(type === "global" && action === "logout") {
			$("#logout").submit();
		} else if(type === "user" && action === "enable" || action === "block") {
			this.getCheck();
			for(x = 0; x < CMS.ids.length; x++) {
				$("#enabled-" + this.ids[x]).html('<span class="icon-16-ajaxload icon-20-spacing"> </span>');
			}
			// Begin the ajax request.
			$.ajax({
				url: HTTP_ADMIN + 'users',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					enabled   : "",
					csrfToken : $("#csrf-token").val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						if(parseInt(response.enabled[x]) === 1) {
							$("#enabled-" + response.ids[x]).html('<span class="icon-20-check icon-20-spacing"> </span>');
						} else if(parseInt(response.enabled[x]) === 0) {
							$("#enabled-" + response.ids[x]).html('<span class="icon-20-disabled icon-20-spacing"> </span>');
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === "user" && action === "activate") {
			this.getCheck();
			for(x = 0; x < CMS.ids.length; x++) {
				$("#activated-" + this.ids[x]).html('<span class="icon-16-ajaxload icon-20-spacing"> </span>');
			}
			// Begin the ajax request
			$.ajax({
				url: HTTP_ADMIN + 'users',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					activated : "",
					csrfToken : $("#csrf-token").val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					$('#blocked-ip-' + response.id).remove();

					for(x = 0; x < CMS.ids.length; x++) {
						if(parseInt(response.activated[x]) === 1) {
							$("#activated-" + response.ids[x]).html('<span class="icon-20-check icon-20-spacing"> </span>');
						} else if(parseInt(response.activated[x]) === 0) {
							$("#activated-" + response.ids[x]).html('<span class="icon-20-disabled icon-20-spacing"> </span>');
						}
					}

					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === 'user' && action === 'delete') {
			this.getCheck();
			$.ajax({
				url: HTTP_ADMIN + 'users',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type       : type,
					action     : action,
					ids        : this.ids,
					deleted    : '',
					csrfToken  : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					window.location.href = HTTP_ADMIN  + 'users/';
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === 'user' && action === 'edit') {
			$('#user-action').val("edit");
		} else if(type === 'user' && action === 'save') {
			$("#user-action").val("save");
		} else if(type === 'user' && action === 'save-close') {
			$("#user-action").val("save-close");
		} else if(type === 'user' && action === 'save-new') {
			$("#user-action").val("save-new");
		} else if((type === 'user' && action === 'statusFilter') || action === 'activationFilter' || action === 'roleFilter') {

		} else if(type === 'role' && action === 'edit') {
			$("#role-action").val("edit");
		} else if(type === 'role' && action === 'delete') {
			$("#role-action").val("delete");
		} else if(type === 'role' && action === 'save') {
			$("#role-action").val("save");
		} else if(type === 'role' && action === 'save-close') {
			$("#role-action").val("save-close");
		} else if(type === 'role' && action === 'save-new') {
			$("#role-action").val("save-new");
		} else if(type === 'role-perms' && action === 'save') {
			$("#role-perms-action").val("save");
		} else if(type === 'role-perms' && action === 'save-close') {
			$("#role-perms-action").val("save-close");
		} else if(type === 'article' && action === 'edit') {
			$("#article-action").val("edit");
		} else if(type === 'article' && (action === 'publish' || action === 'unpublish')) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					$("published-" + this.ids[x]).html('<span class="icon-16-ajaxload icon-20-spacing"> </span>');
				}
			}
			$.ajax({
				url: HTTP_ADMIN + 'articles',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					publish   : '',
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						response.publish[x] = parseInt(response.publish[x]);
						if(response.publish[x] === 1) {
							$('#published-' + response.ids[x]).html(
								'<span class="icon-20-check icon-20-spacing"> </span>'
							);
						} else if(response.publish[x] === 0) {
							$('#published-' + response.ids[x]).html(
								'<span class="icon-20-disabled icon-20-spacing"> </span>'
							);
						}
						if($('#draft-note-' + response.ids[x]).length != 0) {
							$('#draft-note-' + response.ids[x]).remove();
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === 'article' && (action === 'featured' || action === 'remove-featured')) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					$("#featured-" + this.ids[x]).html('<span class="icon-16-ajaxload icon-20-spacing"> </span>');
				}
			}
			$.ajax({
				url: HTTP_ADMIN + 'articles',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					featured  : '',
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						if(response.featured[x] === '1') {
							$('#featured-' + response.ids[x]).html('<span class="icon-20-star icon-20-spacing"> </span>');
						} else if(response.featured[x] === '0') {
							$('#featured-' + response.ids[x]).html('<span class="icon-20-gray-disabled icon-20-spacing"> </span>');
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === 'article' && action === 'delete') {
			deleteConfirm = confirm('The article(s) selected will be permenantly deleted.\n\nAre you sure you want to delete the article(s)?');
			if(deleteConfirm) {
				$("#article-action").val("delete");
			} else {
				return false;
			}
		} else if(type === 'article' && action === 'save') {
			$("#article-action").val("save");
		} else if(type === 'article' && action === 'save-new') {
			$("#article-action").val("save-new");
		} else if(type === 'article' && action === 'save-close') {
			$("#article-action").val("save-close");
		} else if(type === 'article' && action === 'publishedFilter') {

		} else if(type === 'article' && action === 'featuredFilter') {

		} else if(type === 'article' && action === 'categoryFilter') {

		} else if(type === 'article' && action === 'authorFilter') {

		} else if(type === 'article' && action === 'add-category') {

			if($("#new-category-name").val() === "") {
				return false;
			}
			
			// Begin the ajax request.
			$.ajax({
				url: HTTP_ADMIN + 'articles',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type           : type,
					action         : action,
					categoryId     : '',
					categoryName   : $("#new-category-name").val(),
					parentId       : $("#new-category-parent").val(),
					csrfToken      : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					if(response.parentId != 0) {
						var category, myList, catCheck, catLabel, id, parent, catOption, parentDepth;

						parentDepth = str_repeat('- ', response.rootDistance) + response.categoryName;

						// Insert the new category into the parent category select element
						$("#category-" + response.parentId).after(
							'<option id="category-' + response.categoryId + '" value="' + response.categoryId + '">' +
								parentDepth +
							'</option>'
						);
						// Insert the new category li element after its parent.
						$("#category-li-" + response.parentId).after(
							'<li id="category-li-' + response.categoryId + '"></li>'
						);

						$("#category-li-" + response.categoryId).append(
							'<label id="category-label-' + response.categoryId + '" class="center-label" style="margin-left: ' + parseInt(response.rootDistance, 10) * 10 + 'px;">' +
								'<input type="checkbox" id="cat-' + response.categoryId + '" class="center-toggle article-category" name="articleCategories[]" value="' + response.categoryId + '" />' +
								 response.categoryName +
							'</label>'
						);

						$('#category-label-' + response.categoryId).focus();
						$('#category-li-' + response.categoryId).effect('highlight', '#CBC825', 1000);
					} else {
						var category, myList, catCheck, catLabel, catOption, id, parentSelect;

						// Insert the new category into the parent category select element
						$("#new-category-parent").append(
							'<option id="category-' + response.categoryId + '" value="' + response.categoryId + '">' +
								 response.categoryName +
							'</option>'
						);
						// Insert the new category li element.
						$("#category-check-list-all").prepend(
							'<li id="category-li-' + response.categoryId + '"></li>'
						);

						$("#category-li-" + response.categoryId).append(
							'<label id="category-label-' + response.categoryId + '" class="center-label" style="margin-left: ' + parseInt(response.rootDistance, 10) * 10 + 'px;">' +
								'<input type="checkbox" id="cat-' + response.categoryId + '" class="center-toggle article-category" value="' + response.categoryId + '" name="articlesCategories[]" />' +
								 response.categoryName +
							'</label>'
						);

						$('#category-label-' + response.categoryId).focus();
						$('#category-li-' + response.categoryId).effect('highlight', '#CBC825', 1000);
					}
					$('#new-category-name').val('');
					$('#new-category-parent').prop("selectedIndex", 0);
				}
			});

			return false;
		} else if(type === 'article-revision' && action === 'delete') {
			$("#revision-action").val("delete");
		} else if(type === 'article-revision' && action === 'compare') {
			$("#revision-action").val("compare");
		} else if(type === 'article-revision' && action === 'restore') {
			$("#revision-action").val("restore");
		} else if(type === 'comment' && action === 'publishedFilter') {

		} else if(type === 'comment' && (action === 'publish' || action === 'unpublish')) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					$("#published-" + this.ids[x]).html('<span class="icon-16-ajaxload icon-20-spacing"> </span>');
				}
			}
			$.ajax({
				url: HTTP_ADMIN + 'comments',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					publish   : '',
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for( x = 0; x < CMS.ids.length; x++) {
						response.publish[x] = parseInt(response.publish[x]);
						if(response.publish[x] !== null) {
							if(response.publish[x] === 1) {
								$("#published-" + response.ids[x]).html('<span class="icon-20-check icon-20-spacing"> </span>');
							} else if(response.publish[x] === 0) {
								$("#published-" + response.ids[x]).html('<span class="icon-20-disabled icon-20-spacing"> </span>');
							}
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === 'category' && (action === 'publish' || action === 'unpublish')) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					replaceElem('published' + this.ids[x], '<span class="icon-16-ajaxload icon-20-spacing"> </span>');
				}
			}
			$.ajax({
				url: HTTP_ADMIN + 'categories',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					publish   : '',
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						response.publish[x] = parseInt(response.publish[x]);
						if(response.publish[x] === 1) {
							$("#published" + response.ids[x]).html('<span class="icon-20-check icon-20-spacing"></span>');
						} else if(response.publish[x] === 0) {
							$("#published" + response.ids[x]).html('<span class="icon-20-disabled icon-20-spacing"></span>');
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === 'comment' && action === 'edit') {
			$("#comment-action").val("edit");
		} else if(type === 'comment' && action === 'delete') {
			$("#comment-action").val("delete");
		} else if(type === 'comment' && action === 'save') {
			$("#comment-action").val("save");
		} else if(type === 'comment' && action === 'save-close') {
			$("#comment-action").val("save-close");
		} else if(type === 'category' && action === 'edit') {
			$("#category-action").val("edit");
		} else if(type === 'category' && action === 'delete') {
			$('#category-action').val('delete');
		} else if(type === 'category' && action === 'save') {
			$('#category-action').val('save');
		} else if(type === 'category' && action === 'save-close') {
			$('#category-action').val('save-close');
		} else if(type === 'category' && action === 'save-new') {
			$('#category-action').val('save-new');
		} else if(type === 'gallery-album' && action === 'edit') {
			$('#gallery-action').val('edit-album');
		} else if(type === 'gallery-album' && (action === 'publish' || action === 'unpublish')) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					$("#state-" + this.ids[x]).html('<span class="icon-16-ajaxload icon-20-spacing"> </span>');
				}
			}
			$.ajax({
				url: HTTP_ADMIN + 'photogallery',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					publish   : '',
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						response.publish[x] = parseInt(response.publish[x]);
						if(response.publish[x] === 1) {
							$('#state-' + response.ids[x]).html('<span class="icon-20-check icon-20-spacing"></span>');
						} else if(response.publish[x] === 0) {
							$('#state-' + response.ids[x]).html('<span class="icon-20-disabled icon-20-spacing"></span>');
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === 'gallery-album' && (action === 'featured' || action === 'remove-featured')) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					$("#featured-" + this.ids[x]).html('<span class="icon-16-ajaxload icon-20-spacing"> </span>');
				}
			}
			$.ajax({
				url: HTTP_ADMIN + 'photogallery',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					featured  : '',
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						if(response.featured[x] === '1') {
							$("#featured-" + response.ids[x]).html('<span class="icon-20-star icon-20-spacing"></span>');
						} else if(response.featured[x] === '0') {
							$("#featured-" + response.ids[x]).html('<span class="icon-20-gray-disabled icon-20-spacing"></span>');
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === 'gallery-album' && action === 'stateFilter') {

		} else if(type === 'gallery-album' && action === 'featuredFilter') {

		} else if(type === 'gallery-album' && action === 'save') {
			$('#gallery-action').val('save-album');
		} else if(type === 'gallery-album' && action === 'save-close') {
			$('#gallery-action').val('save-close-album');
		} else if(type === 'gallery-album' && action === 'save-new') {
			$('#gallery-action').val('save-new-album');
		} else if(type === 'gallery-album' && action === 'delete') {
			$('#gallery-action').val('delete-album');
		} else if(type === 'gallery-image' && action === 'edit') {
			$('#gallery-action').val('edit-image');
		} else if(type === 'gallery-image' && (action === 'publish' || action === 'unpublish')) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					$("#state-" + this.ids[x]).html('<span class="icon-16-ajaxload icon-20-spacing"> </span>');
				}
			}
			// Begin the ajax request.
			$.ajax({
				url: HTTP_ADMIN + 'photogallery',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					publish   : '',
					csrfToken : $('#csrf-token').val(),
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						response.publish[x] = parseInt(response.publish[x]);
						if(response.publish[x] === 1) {
							$("#state-" + response.ids[x]).html('<span class="icon-20-check icon-20-spacing"> </span>');
						} else if(response.publish[x] === 0) {
							$("#state-" + response.ids[x]).html('<span class="icon-20-disabled icon-20-spacing"> </span>');
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === 'gallery-image' && (action === 'featured' || action === 'remove-featured')) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					$("#featured-" + this.ids[x]).html('<span class="icon-16-ajaxload icon-20-spacing"> </span>');
				}
			}
			$.ajax({
				url: HTTP_ADMIN + 'photogallery',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					featured  : '',
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						if(response.featured[x] === '1') {
							$("#featured-" + response.ids[x]).html('<span class="icon-20-star icon-20-spacing"></span>');
						} else if(response.featured[x] === '0') {
							$("#featured-" + response.ids[x]).html('<span class="icon-20-gray-disabled icon-20-spacing"></span>');
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === 'gallery-image' && action === 'delete') {
			$('#gallery-action').val('delete-image');
		} else if(type === 'gallery-image' && action === 'save') {
			$('#gallery-action').val('save-image');
		} else if(type === 'gallery-image' && action === 'save-close') {
			$('#gallery-action').val('save-close-image');
		} else if(type === 'contact' && action === 'delete') {

		} else if(type === "tag" && action === "edit") {
			$("#tag-action").val("edit");
		} else if(type === 'tag' && action === 'save') {
			$('#tagAction').val('save');
		} else if(type === 'tag' && action === 'save-close') {
			$('#tagAction').val('save-close');
		} else if(type === 'tag' && action === 'save-new') {
			$('#tagAction').val('save-new');
		} else if(type === 'tag' && action === 'delete') {
			$("#tag-action").val("delete");
		} else if(type === 'media' && action === 'edit') {
			$("#mediaAction").val("edit");
		} else if(type === 'media' && action === 'delete') {
			$('#mediaAction').val('delete');
		} else if(type === 'media' && action === 'save') {
			$("#mediaAction'").val("save");
		} else if(type === 'media' && action === 'save-close') {
			$("#mediaAction").val("save-close");
		} else if(type === 'link' && (action === 'publish' || action === 'unpublish')) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					$("#published-" + this.ids[x]).html('<span class="icon-16-ajaxload icon-20-spacing"> </span>');
				}
			}
			$.ajax({
				url: HTTP_ADMIN + 'links',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					publish   : '',
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						response.publish[x] = parseInt(response.publish[x]);
						if(response.publish[x] === 1) {
							$("#published-" + response.ids[x]).html('<span class="icon-20-check icon-20-spacing"> </span>');
						} else if(response.publish[x] === 0) {
							$("#published-" + response.ids[x]).html('<span class="icon-20-disabled icon-20-spacing"> </span>');
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === "link" && action === "delete") {
			$("#link-action").val("delete");
		} else if(type === "link" && action === "save") {
			$("#link-action").val("save");
		} else if(type === "link" && action === "save-close") {
			$("#link-action").val("save-close");
		} else if(type === "link" && action === "save-new") {
			$("#link-action").val("save-new");
		} else if(type === "link category" && action === "edit") {
			$("#linkCatAction").val("edit");
		} else if(type === "link category" && action === "delete") {
			$("#linkCatAction").val("delete");
		} else if(type === "link category" && action === "save") {
			$("#linkCatAction").val("save");
		} else if(type === "link category" && action === "save-new") {
			$("#linkCatAction").val("save-new");
		} else if(type === "link category" && action === "save-close") {
			$("#linkCatAction").val("save-close");
		} else if(type === "profile" && action === "save") {
			$("#profile-action").val("save");
		} else if(type === "profile" && action === "save-close") {
			$("#profile-action").val("save-close");
		} else if(type === "forum" && action === "edit") {
			$("#forum-action").val("edit");
		} else if(type === "forum" && action === "edit-permissions") {
			$("#forum-action").val("edit-permissions");
		} else if(type === "forum" && (action === "publish" || action === "unpublish")) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					$("#published-" + this.ids[x]).removeClass("icon-20-check icon-20-disabled").addClass("icon-16-ajaxload");
				}
			}
			$.ajax({
				url: HTTP_ADMIN + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					published : '',
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						response.published[x] = parseInt(response.published[x]);
						if(response.published[x] === 1) {
							$("#published-" + response.ids[x]).removeClass("icon-16-ajaxload").addClass("icon-20-check");
						} else if(response.published[x] === 0) {
							$("#published-" + response.ids[x]).removeClass("icon-16-ajaxload").addClass("icon-20-disabled");
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === "forum" && (action === "feature" || action === "disregard")) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					$("#featured-" + this.ids[x]).removeClass("icon-20-star icon-20-gray-disabled").addClass("icon-16-ajaxload");
				}
				if($("#featured-" + this.ids[x]).hasParent(".root")) {
					$("#featured-" + this.ids[x]).addClass("dark-ajax-fix");
				}
			}
			$.ajax({
				url: HTTP_ADMIN + 'forums',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					featured  : '',
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						response.featured[x] = parseInt(response.featured[x]);
						if(response.featured[x] === 1) {
							$("#featured-" + response.ids[x]).removeClass("icon-16-ajaxload").addClass("icon-20-star");
						} else if(response.featured[x] === 0) {
							$("#featured-" + response.ids[x]).removeClass("icon-16-ajaxload").addClass("icon-20-gray-disabled");
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === "forum" && action === "delete-forum") {
			$("#forum-action").val("delete-forum");
		} else if(type === "forum" && action === "save-section") {
			$("#forum-action").val("save-section");
		} else if(type === "forum" && action === "save") {
			$("#forum-action").val("save-forum");
		} else if(type === "forum" && action === "save-close-forum") {
			$("#forum-action").val("save-close-forum");
		} else if(type === "forum" && action === "save-new-forum") {
			$("#forum-action").val("save-new-forum");
		} else if(type === "forum" && action === "save-moderator") {
			$("#forum-action").val("save-moderator");
		} else if(type === "forum" && action === "delete-post-and-report") {
			$("#forum-action").val("delete-post-and-report");
		} else if(type === "forum" && action === "delete-report-only") {
			$("#forum-action").val("delete-report-only");
		} else if(type === 'page' && (action === 'publish' || action === 'unpublish')) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					$('#published-' + this.ids[x]).html(
						'<span class="icon-16-ajaxload icon-20-spacing"> </span>'
					);
				}
			}
			$.ajax({
				url: HTTP_ADMIN + 'pages',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					publish   : '',
					csrfToken : $('#csrf-token').val()
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						response.publish[x] = parseInt(response.publish[x]);
						if(response.publish[x] === 1) {
							$("#published-" + response.ids[x]).html('<span class="icon-20-check icon-20-spacing"> </span>');
						} else if(response.publish[x] === 0) {
							$("#published-" + response.ids[x]).html('<span class="icon-20-disabled icon-20-spacing"> </span>');
						}
						if($("#draft-note-" + response.ids[x]).length !== 0) {
							$("#draft-note-" + response.ids[x]).remove();
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		} else if(type === 'page' && action === 'delete') {
			$("#page-action").val("delete");
		} else if(type === 'page' && action === 'save') {
			$("#page-action").val("save");
		} else if(type === 'page' && action === 'save-close') {
			$("#page-action").val("save-close");
		} else if(type === 'page' && action === 'save-new') {
			$("#page-action").val("save-new");
		} else if(type === 'page-template' && action === 'delete') {
			$("#page-action").val("delete-template");
		} else if(type === 'page-template' && action === 'save') {
			$("#page-action").val("save-template");
		} else if(type === 'page-template' && action === 'save-close') {
			$("#page-action").val("save-close-template");
		} else if(type === 'page-template' && action === 'save-new') {
			$("#page-action").val("save-new-template");
		} else if(type === 'page-revision' && action === 'delete') {
			$("#revision-action").val("delete");
		} else if(type === 'page-revision' && action === 'compare') {
			$("#revision-action").val("compare");
		} else if(type === 'page-revision' && action === 'restore') {
			$("#revision-action").val("restore");
		} else if(type === 'menu' && action === 'delete') {
			$("#menu-action").val("delete");
		} else if(type === 'menu' && action === 'save') {
			$("#menu-action").val("save");
		} else if(type === 'menu' && action === 'save-close') {
			$("#menu-action").val("save-close");
		} else if(type === 'menu' && action === 'save-new') {
			$("#menu-action").val("save-new");
		} else if(type === 'menu' && (action === 'publish' || action === 'unpublish')) {
			this.getCheck();
			for(x = 0; x < this.ids.length; x++) {
				if(is_numeric(this.ids[x])) {
					$("#published-" + this.ids[x]).replaceWith(
						'<span id="published-' + this.ids[x] + '" class="icon-16-ajaxload icon-20-spacing"> </span>'
					);
				}
			}
			// Begin the ajax request.
			$.ajax({
				url: HTTP_ADMIN + 'menus',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
					type      : type,
					action    : action,
					ids       : this.ids,
					publish   : '',
					csrfToken : $('#csrf-token').val(),
				})),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					for(x = 0; x < CMS.ids.length; x++) {
						response.publish[x] = parseInt(response.publish[x]);
						if(response.publish[x] === 1) {
							$("#published-" + response.ids[x]).replaceWith(
								'<span id="published-' + response.ids[x] + '" class="icon-20-check icon-20-spacing"> </span>'
							);
						} else if(response.publish[x] === 0) {
							$("#published-" + response.ids[x]).replaceWith(
								'<span id="published-' + response.ids[x] + '" class="icon-20-disabled icon-20-spacing"> </span>'
							);
						}
					}
					CMS.clearForm(document.getElementById('adminForm'));
				}
			});

			return false;
		}
		// Submit the admin form
		$("#adminForm").submit();
	},

	orderButton : function(type, id, orderDir) {
		if(type === 'category') {
			$("#adminForm").submit();
		}
	}
};

function is_numeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

function replaceElem(id, content) {
	document.getElementById(id).innerHTML = content;
}

function insertAfter(newchild, refchild) {
 refchild.parentNode.insertBefore(newchild,refchild.nextSibling);
}

function str_repeat(pattern, count) {
	if (count < 1) return '';
	var result = '';
	while (count > 0) {
		if (count & 1) result += pattern;
		count >>= 1, pattern += pattern;
	};
	return result;
};

function getFileExtension(fileName) {
	return fileName.split('.').pop();
}

function rand(minValue, maxValue) {
	return Math.floor(Math.random() * (maxValue - minValue + 1) + minValue);
}

// Get the URL paramter.
function getURLParameter(name) {
	return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null;
}
// IE8 and under compatibiliy to enable the indexOf method for javascript.
if(!Array.prototype.indexOf) {
	Array.prototype.indexOf = function(elt /*, from*/) {
		var len = this.length >>> 0, from = Number(arguments[1]) || 0;
		from = (from < 0) ? Math.ceil(from) : Math.floor(from);
		if (from < 0) {
			from += len;
		}
		for (; from < len; from++) {
			if (from in this && this[from] === elt) {
				return from;
			}
		}
		return -1;
	};
}
Array.prototype.remove = function(){
	var what, a = arguments, L = a.length, ax;
	while(L && this.length){
		what = a[--L];
		while((ax = this.indexOf(what))!= -1){
			this.splice(ax, 1);
		}
	}
	return this;
}
// Function for decoding htmlspecialchars encoded html from the server.
function htmlspecialchars_decode (string, quote_style) {
	var optTemp = 0,
		i = 0,        noquotes = false;
	if (typeof quote_style === 'undefined') {
		quote_style = 2;
	}
	string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');    var OPTS = {
		'ENT_NOQUOTES': 0,
		'ENT_HTML_QUOTE_SINGLE': 1,
		'ENT_HTML_QUOTE_DOUBLE': 2,
		'ENT_COMPAT': 2,        'ENT_QUOTES': 3,
		'ENT_IGNORE': 4
	};
	if (quote_style === 0) {
		noquotes = true;    }
	if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
		quote_style = [].concat(quote_style);
		for (i = 0; i < quote_style.length; i++) {
			// Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
			if (OPTS[quote_style[i]] === 0) {
				noquotes = true;
			} else if (OPTS[quote_style[i]]) {
				optTemp = optTemp | OPTS[quote_style[i]];
			}        }
		quote_style = optTemp;
	}
	if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
		string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should        // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
	}
	if (!noquotes) {
		string = string.replace(/&quot;/g, '"');
	}    // Put this in last place to avoid escape being double-decoded
	string = string.replace(/&amp;/g, '&');

	return string;
}

// Function to get the cursor position.
function getInputSelection(el) {
	var start = 0, end = 0, normalizedValue, range,
		textInputRange, len, endRange;

	if (typeof el.selectionStart == 'number' && typeof el.selectionEnd == 'number') {
		start = el.selectionStart;
		end = el.selectionEnd;
	} else {
		range = document.selection.createRange();

		if (range && range.parentElement() == el) {
			len = el.value.length;
			normalizedValue = el.value.replace(/\r\n/g, '\n');

			// Create a working TextRange that lives only in the input
			textInputRange = el.createTextRange();
			textInputRange.moveToBookmark(range.getBookmark());

			// Check if the start and end of the selection are at the very end
			// of the input, since moveStart/moveEnd doesn't return what we want
			// in those cases
			endRange = el.createTextRange();
			endRange.collapse(false);

			if (textInputRange.compareEndPoints('StartToEnd', endRange) > -1) {
				start = end = len;
			} else {
				start = -textInputRange.moveStart('character', -len);
				start += normalizedValue.slice(0, start).split('\n').length - 1;

				if (textInputRange.compareEndPoints('EndToEnd', endRange) > -1) {
					end = len;
				} else {
					end = -textInputRange.moveEnd('character', -len);
					end += normalizedValue.slice(0, end).split('\n').length - 1;
				}
			}
		}
	}

	return {
		start: start,
		end: end
	};
}

// Jquery extensions.

// Jquery function that will insert text a caret position.
$.fn.extend({
	insertAtCaret: function(myValue) {
		var obj, sel, startPos, endPos, topScroll;
		if(typeof this[0].name !== 'undefined') {
			obj = this[0];
		} else {
			obj = this;
		}
		
		obj.value += myValue;
		obj.focus();
	},

	selectRange: function(start, end) {
		return this.each(function() {
			if(this.setSelectionRange) {
				this.focus();
				this.setSelectionRange(start, end);
			} else if(this.createTextRange) {
				var range = this.createTextRange();
				range.collapse(true);
				range.moveEnd('character', end);
				range.moveStart('character', start);
				range.select();
			}
		});
	},
	// Jquery function that determines whether a given element has a parent.
	hasParent: function(p) {
		// Returns a subset of items using jQuery.filter
		return this.filter(function() {
			// Return truthy/falsey based on presence in parent
			return $(p).find(this).length;
		});
	}
});

// Funtion to get just the text.
jQuery.fn.justText = function() {
	return $(this).clone()
				  .children()
				  .remove()
				  .end()
				  .text();
};
function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}
function ltrim(stringToTrim) {
	return stringToTrim.replace(/^\s+/,"");
}
function rtrim(stringToTrim) {
	return stringToTrim.replace(/\s+$/,"");
}
function in_array(needle, haystack) {
	var i;
	for(i = 0; i < haystack.length; i++) {
		if(this[i] === needle) {
			return true;
		}
	}
	return false;
}
function removeDuplicates(arr) {
	var i, len = arr.length, out = [], obj = {};
	for(i = 0; i < len; i++) {
		obj[arr[i]]=0;
	}
	for(i in obj) {
		out.push(i);
	}
	return out;
}
