// JavaScript Document

// Global variables
var editorTabs
  , fullscreenEditorTabs
  , activeEditor = "content-editor"
  , revisionId = ""
  , pluploadUrl
  , pluploadType
  , pluploadController;


$(document).ready(function() {
	var noArticleError = "No articles were selected. \n\nPlease select an article from the table below and try again."
	  , noRevisionError = "No revisions selected, please select a revision from the table below and try again."
	  , uploader;

	//Initialize the date/timepicker
	if($("#date-posted").length !== 0) {
		$("#date-posted").datetimepicker({
			showOn: "both",
			buttonImage: HTTP_IMAGE + "icons/admin/calendar_icon2.png",
			buttonImageOnly: true,
			ampm: true,
			dateFormat: "yy-mm-dd",
			timeFormat: "hh:mm TT"
		});
	}
	
	// Set plupload info
	if($("#profile-image").length !== 0) {
		pluploadUrl = HTTP_ADMIN + 'articles?adminRequest={"type":"article","action":"upload-image"}&csrfToken=' + $('#csrf-token').val();
		pluploadType = "article";
		pluploadAction ="upload-image";
		pluploadController = HTTP_ADMIN + "articles?csrfToken=" + $("#csrf-token").val();
		deleteImgUrl = HTTP_ADMIN + "articles";
	}

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
	$(".article-category").click(function() {
		var categoryId;
		if($(this).hasClass("popular-category")) {
			categoryId = $(this).prop("id").replace("pop-cat-", "");
			
			if(!$(this).is(":checked")) {
				$("#pop-cat-" + categoryId + ", #cat-" + categoryId).prop("checked", true);
			} else {
				$("#pop-cat-" + categoryId + ", #cat-" + categoryId).prop("checked", false);
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
	
	// Submit a new category click
	$("#create-category").click(function(e) {
		e.preventDefault();
		CMS.submitButton('article', 'add-category');
	});
	
	// Tags
	if($("#add-tag").length !== 0) {
		// Add tag
		$("#add-tag").click(function(e) {
			e.preventDefault();
			var tagsToAdd, numOfTags, tagsArr, currentTags, tagsStr, currentTagsArr, x, i;
			tagsToAdd = $("#tags-to-add").val();
			numOfTags = $("#selected-tags").children().length + 1;
			if(tagsToAdd.length === 0) {
				return false;
			}
			// Array of tags to add.
			tagsArr = tagsToAdd.split(",");
			// Remove any duplicates
			tagsArr = removeDuplicates(tagsArr);
			// Current tags in input field
			currentTags = $("#hidden-tags").val();
			// Contains final string to add to input field
			tagsStr = "";
			// If tags exist explode them into an array to check for duplicate.
			if(currentTags.length > 0) {
				currentTagsArr = currentTags.split(",");
			}
			if(tagsArr.length > 0) {
				for(x = 0; x < tagsArr.length; x++) {
					// Trim whitespace
					tagsArr[x] = trim(tagsArr[x]);
					// Remove duplicates.
					if(typeof(currentTagsArr) !== "undefined") {
						for(i = 0; i < currentTagsArr.length; i++) {
							if(currentTagsArr[i] === tagsArr[x]) {
								tagsArr[x] = null;
							}
						}
					}
					if(tagsArr[x] !== null) {
						if((x === 0 && tagsArr.length === 1) || (x === (tagsArr.length - 1))) {
							tagsStr += tagsArr[x];
						} else {
							tagsStr += tagsArr[x] + ",";
						}
						$("#selected-tags").append(
							'<span>' +
								'<a id="tag-' + numOfTags + '" class="ui-icon ui-icon-close remove-tag"></a>' +
								tagsArr[x] +
							'</span>'
						);
						numOfTags++;
					}
				}
			}
			$("#tags-to-add").val("");
			// If no tags have been added add them else append additional tags.
			if(currentTags.length === 0) {
				currentTags = tagsStr;
			} else {
				currentTags += "," + tagsStr;
			}
			$("#hidden-tags").val(currentTags);
		});
		// Remove tag
		$("#article-tags-panel").on("click", ".remove-tag", function(e) {
			var tagsToRemove, tagToRemove, currentTags, newCurrentTags, lastChar;
			
			e.preventDefault();
			
			// Hackishly get the text of the parent span and trim the whitespace.
			tagToRemove = $(this).parent().justText().trim();
			$(this).parent().remove();
			currentTags = $("#hidden-tags").val();
			// Try removing a tag followed by a comma.
			newCurrentTags = currentTags.replace(tagToRemove + ",", "");
			// If after trying to remove a tag followed by comma does not work remove a tag without a comma.
			if(newCurrentTags === currentTags) {
				newCurrentTags = currentTags.replace(tagToRemove, "");
			}
			// If there is a trailing comma, remove it.
			lastChar = newCurrentTags[newCurrentTags.length-1];
			if(lastChar === ",") {
				newCurrentTags = newCurrentTags.slice(0, -1);
			}
			$("#hidden-tags").val(newCurrentTags);
		});
		// Show popular tags
		$("#article-tags-panel").on("click", "#show-popular-tags", function(e) {
			e.preventDefault();
			$("#popular-tags").slideToggle();
		});
		// Add popular tag.
		$("#article-tags-panel").on("click", ".popular-tag", function(e) {
			var tagToAdd, currentTags, currentTagsArr, numOfTags, x;
			
			e.preventDefault();
			
			// Get tag to add
			tagToAdd = $(this).justText().trim();
			// Current tags in input field
			currentTags = $("#hidden-tags").val();
			numOfTags = $("#selected-tags").children().length + 1;
			// If tags exist explode them into an array to check for duplicate.
			if(currentTags.length > 0) {
				currentTagsArr = currentTags.split(",");
				// Check for duplicates
				for(x = 0; x < currentTagsArr.length; x++) {
					if(tagToAdd === currentTagsArr[x]) {
						return false;
					}
				}
			}
			$("#selected-tags").append('<span><a id="tag-' + numOfTags + '" class="remove-tag">x</a>' + tagToAdd);
			// If no tags have been added add them else append additional tags.
			if(currentTags.length === 0) {
				currentTags = tagToAdd;
			} else {
				currentTags += "," + tagToAdd;
			}
			$("#hidden-tags").val(currentTags);
		});
	}
	
	// Autosave article.
	if($("#content-editor").length !== 0) {
		var currentContent
		  , idForArticle = ""
		  , autoSave = false
		  , newIdInput
		  , newIdAdjacent;
		
		autoSave = true;
		
		$(document).everyTime(30000, function() {
			switch(activeEditor) {
				// Autosave article depending on which editor is active.
				case "content-editor":
					// Set the current content.
					if(typeof(currentContent) === "undefined") {
						if(disableTinymce) {
							currentContent = $("#content-editor").val();
						} else {
							currentContent = $("#content-editor").html();
						}
					}
					// Check for any changes to the current content, if there are changes autosave and update the current content.
					if(disableTinymce) {
						if($("#content-editor").val() !== currentContent) {
							// Autosave article
							autosaveArticle($("#content-editor").val());
							// Update current content
							currentContent = $("#content-editor").val();
						}
					} else {
						if($("#content-editor").html() !== currentContent) {
							// Autosave article
							autosaveArticle($("#content-editor").html());
							// Update current content
							currentContent = $("#content-editor").html();
						}
					}
					break;
				case "html-editor":
					if($("#html-editor").val() !== currentContent) {
						// Set the current content.
						if(typeof(currentContent) === "undefined") {
							currentContent = cmHtmlEditor.getValue();;
						}
						// Check for any changes to the current content, if there are changes autosave and update the current content.
						if($("#html-editor").val() !== currentContent) {
							// Autosave article
							autosaveArticle(cmHtmlEditor.getValue());
							// Update current content
							currentContent = cmHtmlEditor.getValue();
						}
					}
					break;
				case "fullscreen-editor":
					if($("#html-editor").val() !== currentContent) {
						// Set the current content.
						if(typeof(currentContent) === "undefined") {
							currentContent = $("#fullscreen-editor").val();
						}
						// Check for any changes to the current content, if there are changes autosave and update the current content.
						if($("#html-editor").val() !== currentContent) {
							// Autosave article
							autosaveArticle($("#fullscreen-editor").val());
							// Update current content
							currentContent = $("#fullscreen-editor").val();
						}
					}
					break;
				case "fullscreen-html-editor":
					if($("#html-editor").val() !== currentContent) {
						// Set the current content.
						if(typeof(currentContent) === "undefined") {
							currentContent = $("#fullscreen-html-editor").val();
						}
						// Check for any changes to the current content, if there are changes autosave and update the current content.
						if($("#html-editor").val() !== currentContent) {
							// Autosave article
							autosaveArticle(cmFullscreenHtmlEditor.getValue());
							// Update current content
							currentContent = cmFullscreenHtmlEditor.getValue();
						}
					}
					break;
				default:
					break;
			}
		});
	}

	// Media attachment modal link clicks
	$(document).on("click", ".url-none", function(e) {
		e.preventDefault();
		var id = this.id.replace("url-none-", "");
		$("#link-url-" + id).val("");
	});
	
	$(document).on("click", ".url-file", function(e) {
		e.preventDefault();
		var id = this.id.replace("url-file-", "");
		$.ajax({
			type: "POST",
			url: HTTP_ADMIN + "media",
			datatype: "json",
			data: "adminRequest=" + encodeURIComponent(JSON.stringify({
				type      : "media",
				action    : "get-file-url",
				id        : id,
				csrfToken : $("#csrf-token").val()
			})),
			success: function(data, textStatus, jqXHR) {
				var response = JSON.parse(data);
				$("#link-url-" + id).val(response.urlFile);
			}
		});
	});
	
	// Edit article clicks.
	$("#toolbar-edit, #action-edit-article").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noArticleError);
		} else {
			CMS.submitButton("article", "edit");
		}
	});
	
	// Publish article clicks.
	$("#toolbar-publish, #action-publish-article").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noArticleError);
		} else {
			CMS.submitButton("article", "publish");
		}
	});
	
	// Unpublish article clicks.
	$("#toolbar-unpublish, #action-unpublish-article").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noArticleError);
		} else {
			CMS.submitButton("article", "unpublish");
		}
	});
	// Feature article clicks.
	$("#toolbar-feature, #action-feature-article").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noArticleError);
		} else {
			CMS.submitButton("article", "featured");
		}
	});
	
	// Remove feature article clicks.
	$("#toolbar-nofeature, #action-nofeature-article").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noArticleError);
		} else {
			CMS.submitButton("article", "remove-featured");
		}
	});
	
	// Delete article clicks.
	$("#toolbar-delete, #action-delete-article").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noArticleError);
		} else {
			CMS.submitButton("article", "delete");
		}
	});
	
	// Publish article button clicks.
	$(".quick-publish-article").click(function() {
		$("#article-state").val(1);
		CMS.submitButton("article", "save-close");
	});
	
	// Draft article button clicks
	$(".draft-article-content").click(function() {
		$("#article-state").val(2);
		CMS.submitButton("article", "save");
	});
	
	// Save article clicks
	$("#toolbar-save-article, #action-save-article").click(function() {
		CMS.submitButton("article", "save");
	});
	
	// Save and new article clicks
	$("#toolbar-save-new-article, #action-save-new-article").click(function() {
		CMS.submitButton("article", "save-new");
	});
	
	// Save and close article clicks
	$("#toolbar-save-close-article, #action-save-close-article").click(function() {
		selectNavItem("#articles-link", "#new-article-link");
		CMS.submitButton("article", "save-close");
	});
	
	// Close the article editor clicks
	$("#toolbar-close-article, #action-close-article").click(function() {
		selectNavItem("#articles-link", "#new-article-link");
		window.location.href = HTTP_ADMIN + "articles";
	});
	
	// Submit article globals clicks
	$("#submit-article-globals").click(function(e) {
		e.preventDefault();
		var allowComments, showIntroText, disableTinymce;
		
		allowComments = $("#allow-comments").is(":checked") ? 1 : 0;
		showIntroText = $("#show-intro-text").is(":checked") ? 1 : 0;
		
		$.ajax({
			type: "POST",
			url: HTTP_ADMIN + "articles",
			datatype: "json",
			data: "adminRequest=" + encodeURIComponent(JSON.stringify({
				type               : "article",
				action             : "update-globals",
				allowComments      : allowComments,
				showIntroText      : showIntroText,
				deletedCommentText : $("#deleted-comment-text").val(),
				csrfToken          : $("#csrf-token").val()
			})),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				$("#link-url-" + id).val(response.urlFile);
			}
		});
	});
	
	$("#toolbar-compare-revisions, #action-compare-revisions").click(function() {
		CMS.submitButton('article-revision', 'compare');
	});
	$("#toolbar-restore-revision, #action-restore-revision").click(function() {
		if(boxesChecked == 0) { 
			alert(noRevisionError);
		} else {
			//console.log("restoring revision");
			CMS.submitButton("article-revision", "restore"); 
		}
	});
	$("#toolbar-delete-revision, #action-delete-revision").click(function() {
		if(boxesChecked == 0) {
			alert(noRevisionError);
		} else {
			CMS.submitButton("article-revision", "delete");
		}
	});
});

function autosaveArticle(content) {
	var action = "autosave"
	  , checkedCategories = "";
	
	if(checkedCategories) {
		// Get checked categories.
		$("#all-category-tabs input[name='articleCategories[]']:checked:enabled").each(function() {
			checkedCategories.push($(this).val());
		});
	}
	
	if($("#id").length !== 0)  {
		idForArticle = $("#id").val();
		action = "revision";
	}
	
	if(typeof(idForArticle) === "undefined") {
		idForArticle = "";
	}
	
	if(typeof(revisionId) === "undefined") {
		revisionId = "";
	}
	 
	 // autosave an article
	 $.ajax({
		 type: "POST",
		 url: HTTP_ADMIN + "articles",
		 datatype: "json",
		 data: "adminRequest=" + encodeURIComponent(JSON.stringify({
			type            : "article",
			action          : action,
			id              : idForArticle,
			title           : $("#title").val(),
			alias           : $("#alias").val(),
			author          : $("#author").val(),
			datePosted      : $("#date-posted").val(),
			content         : content,
			summary         : $("#summary-editor").html(),
			image           : $("#img").val(),
			imageAlt        : $("#img-alt").val(),
			imagePath       : $("#img-path").val(),
			categories      : checkedCategories,
			featured        : $("#featured").val(),
			contentFormatId : $("#content-format").val(),
			metaDescription : $("#metaDescription").val(),
			metaKeywords    : $("#metaKeywords").val(),
			metaAuthor      : $("#metaAuthor").val(),
			metaRobots      : $("#metaRobots").val(),
			revisionId      : revisionId,
			authorUsername  : $("#author option:selected").text(),
			csrfToken       : $("#csrf-token").val()
		})),
		success: function(data, textStatus, jqXHR) {
			var response = JSON.parse(data);
			idForArticle = response.id;
			// If new article has been autosaved create the id elemnt so the article can be updated.
			if(response.action === "autosave" && typeof(response.errors) === "undefined") {
				$("#adminForm").prepend(
					'<input type="hidden" id="' + response.id + '" name="id" value="' + response.id + '" />' 
				);
				$("#autosave").html("Draft saved on " + response.lastSave);
				$("#autosave").effect("highlight", {}, 2000, function() {
					
				});
			// Handle article revisions for already posted articles.
			} else if(response.action === "revision") {
				// Set the article id to the revision id.
				revisionId = response.revisionId;
				$("#autosave").html("Revision by " + response.authorUsername + " saved on " + response.lastSave);
				$("#autosave").effect("highlight", {}, 2000, function() {

				});
			}
		 }
	});
}
