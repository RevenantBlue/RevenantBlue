// JavaScript Document

// Global variables to keep track of the number of history items.
var jcropApi
  , urlHistory = null
  , historyCount = 0
  , extendedHistory = null
  , mediaWidth
  , mediaHeight
  , mediaWidthArr = []
  , mediaHeightArr = []
  , origWidthArr = []
  , origHeightArr = []
  , sortableArr = []
  , attachId
  , csrfToken;

// JqueryUi sortable function
function loadSortable(id) {
	'use strict';
	var sortable;
	// Ensure the sortable is only included once.
	sortable = $('div.panel:data(sortable)');
	if(!sortable.length) {
		//Initialize the sortable jqueryui feature for the panels.
		$('#image-editor-' + id).find('.panel').sortable({
			connectWith : '.panel',
			handle : $('.element-top'),
			placeholder : 'profile-placeholder',
			start : function(e, ui) {
				ui.placeholder.height(ui.item.height());
			},
			update : function(e, ui) {
				$('.panel').each(function() {
					if($(this).children().length === 0) {
						$(this).css('margin-bottom', 0);
					}
				});
			}
		});
		$('#image-editor-' + id).find('.panel-column').addClass('ui-widget ui-widget-content ui-helper-clearfix ui-corner-all')
			.find('.element-top')
				.addClass('ui-widget-header ui-corner-all' )
				.prepend('<span class="ui-icon ui-icon-minusthick"></span>')
				.end()
			.find('.element-body');

		$('.element-top .ui-icon').click(function() {
			var elementTop = $(this).parents(".element-top")
			  , elemIcon = $(this).parents(".element-top").children(".ui-icon");
			$(this).toggleClass( 'ui-icon-minusthick' ).toggleClass('ui-icon-plusthick');
			$(this).parents('.panel-column:first' ).find( '.element-body').toggle();
			// Show/hide the bottom border of the element-top div when toggling visibility.
			if(elemIcon.hasClass("ui-icon-minusthick")) {
				console.log("plus");
				elementTop.css({
					"border-bottom" : "1px solid #C0C0C0"
				})
			} else {
				console.log("minus");
				elementTop.css({
					"border-bottom" : "none"
				})
			}
		});
		// Adds placeholder support for unsupporting browswers.
		$('input[placeholder], textarea[placeholder]').placeholder();

		// Hide scale and restore image.
		$('.scale-image > .panel-column > .element-top > .ui-icon-minusthick').toggleClass('ui-icon-plusthick').toggleClass('ui-icon-minusthick');
		$('.scale-image > .panel-column > .element-body').hide();
		$('.restore-image > .panel-column > .element-top > .ui-icon-minusthick').toggleClass('ui-icon-plusthick').toggleClass('ui-icon-minusthick');
		$('.restore-image > .panel-column > .element-body').hide();
	}
}

// Image Editor

// Function that builds the history URL depending on the action.
function buildImageUrl(propertyName, obj, imageId) {
	'use strict';
	var imageUrl = $('#edit-image-preview-container-' + imageId + ' > .jcrop-holder > img').attr('src'),
		imageSrc = imageUrl.match(/&history=(.*)/),
		historyDiff, x;
	// If restoring an image.
	if(propertyName === 'restore' || propertyName === 'save') {
		urlHistory = null;
		extendedHistory = null;
		historyCount = 0;
		imageUrl = imageUrl.match(/.*(&csrfToken=)([^&]*)/);
		return imageUrl[0] + '&timestamp=' + new Date().getTime();
	}
	// If history exists append the crop action to the history GET variable
	if(imageSrc !== null) {
		urlHistory = imageSrc[0].replace('&history=', '');
		urlHistory = decodeURIComponent(urlHistory);
		urlHistory = urlHistory.replace('&history=', '');
		urlHistory = JSON.parse(urlHistory);
		// If undo or redo has been called.
		if(propertyName === 'undo' || propertyName === 'redo') {
			if(propertyName === 'undo') {
				// Remove the last object from the history query string.
				urlHistory.pop();
				urlHistory = JSON.stringify(urlHistory);
				imageUrl = imageUrl.match(/.*(&csrfToken=)([^&]*)/);
				// If history still exists after the undo, append it.
				if(historyCount > 0) {
					imageUrl = imageUrl[0] + '&history=' + encodeURIComponent(urlHistory);
				}
			} else if(propertyName === 'redo') {
				historyDiff = extendedHistory.length - historyCount;
				urlHistory = JSON.parse(JSON.stringify(extendedHistory));
				// Remove the last elements from the history array until it is one greater than the current history.
				for(x = 1; x < historyDiff; x++) {
					urlHistory.pop();
				}
				urlHistory = JSON.stringify(urlHistory);
				imageUrl = imageUrl.match(/.*(&csrfToken=)([^&]*)/);
				imageUrl = imageUrl[0] + '&history=' + encodeURIComponent(urlHistory);
			}
			return imageUrl;
		}  else {
			historyCount = urlHistory.length;
			urlHistory.push({});
			urlHistory[historyCount][propertyName] = obj;
			// Increase history count + 1.
			historyCount = urlHistory.length;
			urlHistory = JSON.stringify(urlHistory);
			// Remove the old histroy from the imageUrl variable
			imageUrl = imageUrl.match(/.*(&csrfToken=)([^&]*)/);
			imageUrl = imageUrl[0] + '&history=' + encodeURIComponent(urlHistory);
		}
	} else {
		urlHistory = [{}];
		urlHistory[0][propertyName] = obj;
		// Increase history count + 1.
		historyCount = urlHistory.length;
		urlHistory = JSON.stringify(urlHistory);
		// Build the final URL.
		imageUrl = imageUrl + '&history=' + encodeURIComponent(urlHistory);
	}
	// If there is extended history remove it and disable the redo option.
	extendedHistory = null;
	// Set the new extended history.
	extendedHistory = JSON.parse(urlHistory);
	if($('#tool-redo-' + imageId).hasClass('tool-redo')) {
		$('#tool-redo-' + imageId).addClass('tool-redo-inactive').removeClass('tool-redo cursor-pointer');
	}
	// If the save button is disabled, enable it.
	if($('#save-image-' + imageId).is(':disabled')) {
		$('#save-image-' + imageId).button('enable');
	}
	// Activate undo
	$('#tool-undo-' + imageId).removeClass('tool-undo-inactive').addClass('tool-undo cursor-pointer');
	return imageUrl;
}

function setCropWidth(event) {
	'use strict';
	var id = event.data.id, coords, newWidth, distance, excessWidth;
	coords = jcropApi.tellSelect();
	newWidth = Math.abs(parseInt($('#crop-selection-width-' + id).val(), 10) + coords.x);
	// The difference between the new and old heights.
	distance = newWidth - coords.x2;
	// If the new height given is greater than the height of the image adjust the crop box accordingly.
	if((distance + newWidth > mediaWidth) || Math.round(coords.x2) === mediaWidth) {
		excessWidth = Math.round((distance + coords.x2) - mediaWidth);
		// Change the crop width to the left if the distance is less than 0 or to the right if it's greater.
		if(distance < 0) {
			jcropApi.setSelect([coords.x, coords.y, mediaWidth + excessWidth, coords.y2]);
		} else {
			jcropApi.setSelect([coords.x - excessWidth, coords.y, mediaWidth, coords.y2]);
		}
	} else {
		jcropApi.setSelect([coords.x, coords.y, newWidth, coords.y2]);
	}
}

function setCropHeight(event) {
	'use strict';
	var id = event.data.id, coords, newHeight, excessHeight, distance;
	coords = jcropApi.tellSelect();
	newHeight = Math.abs(parseInt($('#crop-selection-height-' + id).val(), 10) + coords.y);
	// If nothing has been changed exit the function.
	if(newHeight === coords.y2) return false;
	// The difference between the new and old heights.
	distance = newHeight - coords.y2;
	// If the new height given is greater than the height of the image adjust the crop box accordingly.
	if((distance + newHeight > mediaHeight) || Math.round(coords.y2) === mediaHeight) {
		excessHeight = Math.round((distance + coords.y2) - mediaHeight);
		// Change the crop width to the left if the distance is less than 0 or to the right if it's greater.
		if(distance < 0) {
			jcropApi.setSelect([coords.x, coords.y, coords.x2, mediaHeight + excessHeight]);
		} else {
			jcropApi.setSelect([coords.x, coords.y - excessHeight, coords.x2, mediaHeight]);
		}
	} else {
		jcropApi.setSelect([coords.x, coords.y, coords.x2, newHeight]);
	}
}

function changeCropRatio(event) {
	'use strict';
	var id = event.data.id, coords, aspectWidth, aspectHeight, newHeight, difference;
	// Get image id
	if(!$('#crop-ratio-width-' + id).val() || !$('#crop-ratio-height-' + id).val()) { return false; }
	coords = jcropApi.tellSelect();
	aspectWidth = $('#crop-ratio-width-' + id).val();
	aspectHeight = $('#crop-ratio-height-' + id).val();
	newHeight = Math.round((aspectHeight / aspectWidth) * coords.w);
	difference = Math.abs(newHeight - coords.y2);
	if(difference < 0) {
		jcropApi.setSelect([coords.x, coords.y, coords.x2, coords.y + newHeight]);
	} else {
		jcropApi.setSelect([coords.x, coords.y, coords.x2, coords.y + newHeight]);
	}
	// Clear out the ratio after change.
	$('#crop-ratio-width-' + id + ', #crop-ratio-height-' + id).val('');
}

function cropImage(event) {
	'use strict';
	var id = event.data.id, currentOptions, coords, c, imageUrl;
	// Disable crop clicks if inactive
	if($('#crop-tool-' + id).hasClass('tool-crop-inactive')) {
		return false;
	}
	currentOptions = jcropApi.getOptions();
	coords = jcropApi.tellSelect();
	c = {
		x: Math.round(coords.x),
		y: Math.round(coords.y),
		w: Math.round(coords.w),
		h: Math.round(coords.h),
		cw: currentOptions.trueSize[0],
		ch: currentOptions.trueSize[1]
	};
	imageUrl = buildImageUrl('c', c, id);
	// Disable ui clicks.
	disableImageEditorIcons(id);
	$('#image-editor-main-' + id).fadeTo('fast', 0.6);
	// Reset the jcrop interface.
	jcropApi.release();
	jcropApi.setImage(imageUrl, function() {
		jcropApi.setOptions({
			trueSize: [c.w, c.h]
		});
		// Update the new scale and clear out the current selection and fade in the new image.
		$('#scale-width-' + id).val(c.w);
		$('#scale-height-' + id).val(c.h);
		$('#crop-selection-width-' + id + ', #crop-selection-height-' + id).val('');
		$('#image-editor-main-' + id).fadeTo('fast', 1);
		// Enable ui clicks
		enableImageEditorIcons(id);
	});
}

function flipHorizontally(event) {
	'use strict';
	var id = event.data.id, f, imageUrl;
	// Disable flip if inactive
	if($('#tool-flip-horz-' + id).hasClass('tool-flip-horz-inactive')) {
		return false;
	}
	// Flip an image vertically or horizontally.
	f = { f : 2 };
	imageUrl = buildImageUrl('f', f, id);
	// Disable ui clicks.
	disableImageEditorIcons(id);
	$('#image-editor-main-' + id).fadeTo('fast', 0.6);
	// Reset the jcrop interface.
	jcropApi.release();
	jcropApi.setImage(imageUrl, function() {
		// Clear out the current selection and fade in the new image.
		$('#crop-selection-width-' + id + ', #crop-selection-height-' + id).val('');
		$('#image-editor-main-' + id).fadeTo('fast', 1);
		// Enable ui clicks
		enableImageEditorIcons(id);
	});
}

function flipVertically(event) {
	'use strict';
	var id = event.data.id, f, imageUrl;
	// Disable flip if inactive
	if($('#tool-flip-vert-' + id).hasClass('tool-flip-vert-inactive')) {
		return false;
	}
	// Flip an image vertically or vertically.
	f = { f : 1 };
	imageUrl = buildImageUrl('f', f, id);
	// Disable ui clicks.
	disableImageEditorIcons(id);
	$('#image-editor-main-' + id).fadeTo('fast', 0.6);
	// Reset the jcrop interface.
	jcropApi.release();
	jcropApi.setImage(imageUrl, function() {
		// Clear out the current selection and fade in the new image.
		$('#crop-selection-width-' + id + ', #crop-selection-height-' + id).val('');
		$('#image-editor-main-' + id).fadeTo('fast', 1);
		enableImageEditorIcons(id);
	});
}

function rotateClockwise(event) {
	'use strict';
	var id = event.data.id, currentOptions, r, imageUrl;
	// Disable rotation if inactive.
	if($('#tool-rotate-c-' + id).hasClass('tool-rotate-cc-inactive')) {
		return false;
	}
	currentOptions = jcropApi.getOptions();
	// Rotate an image clockwise
	r = { r : 90 };
	// Set the current size for undo/redo.
	r.cw = currentOptions.trueSize[0];
	r.ch = currentOptions.trueSize[1];
	// Build the URL.
	imageUrl = buildImageUrl('r', r, id);
	// Disable ui clicks.
	disableImageEditorIcons(id);
	$('#image-editor-main-' + id).fadeTo('fast', 0.6);
	// Reset the jcrop interface.
	jcropApi.release();
	jcropApi.setImage(imageUrl, function() {
		currentOptions = jcropApi.getOptions();
		// Change the true size to match the new dimensions (basically just reverse the previous dimensions).
		jcropApi.setOptions({
			trueSize: [currentOptions.trueSize[1], currentOptions.trueSize[0]]
		});
		$('#image-editor-main-' + id).fadeTo('fast', 1);
		enableImageEditorIcons(id);
	});
	$('#scale-width-' + id).val(currentOptions.trueSize[1]);
	$('#scale-height-' + id).val(currentOptions.trueSize[0]);
	// Clear out the current selection and fade in the new image.
	$('#crop-selection-width-' + id + ', #crop-selection-height-' + id).val('');
}

function rotateCounterClockwise(event) {
	'use strict';
	var id = event.data.id, currentOptions, r, imageUrl;
	// Disable rotation if inactive.
	if($('#tool-rotate-cc-' + id).hasClass('tool-rotate-cc-inactive')) {
		return false;
	}
	currentOptions = jcropApi.getOptions();
	// Rotate an image counter clockwise
	r = { r : -90 };
	// Set the current size for undo/redo.
	r.cw = currentOptions.trueSize[0];
	r.ch = currentOptions.trueSize[1];
	// Build the URL.
	imageUrl = buildImageUrl('r', r, id);
	imageUrl = imageUrl.replace(/&rand=([0-9]*[^&])/, '&rand=' + rand(10000, 99999));
	// Disable ui clicks.
	disableImageEditorIcons(id);
	$('#image-editor-main-' + id).fadeTo('fast', 0.6);
	// Reset the jcrop interface.
	jcropApi.release();
	jcropApi.setImage(imageUrl, function() {
		currentOptions = jcropApi.getOptions();
		// Change the true size to match the new dimensions (basically just reverse the previous dimensions).
		jcropApi.setOptions({
			trueSize: [currentOptions.trueSize[1], currentOptions.trueSize[0]]
		});
		$('#scale-width-' + id).val(currentOptions.trueSize[1]);
		$('#scale-height-' + id).val(currentOptions.trueSize[0]);
		// Clear out the current selection and fade in the new image.
		$('#crop-selection-width-' + id + ', #crop-selection-height-' + id).val('');
		$('#image-editor-main-' + id).fadeTo('fast', 1);
		enableImageEditorIcons(id);
	});
}

function undoImageEdit(event) {
	'use strict';
	var id = event.data.id, currentHistory, imageUrl;
	// Reset true size on undo depending on the action being undone.
	currentHistory = JSON.parse(urlHistory);
	if(currentHistory === null || typeof(currentHistory[currentHistory.length - 1]) === "undefined" || $("#tool-undo-" + id).hasClass("tool-undo-inactive")) {
		return false;
	}
	// Set the width and height of the previous image so edits are correct.
	if(currentHistory[currentHistory.length - 1].hasOwnProperty('c')) {
		// Undoing a crop
		jcropApi.setOptions({
			trueSize: [currentHistory[currentHistory.length - 1].c.cw, currentHistory[currentHistory.length -1].c.ch]
		});
		$('#scale-width-' + id).val(currentHistory[currentHistory.length - 1].c.cw);
		$('#scale-height-' + id).val(currentHistory[currentHistory.length - 1].c.ch);
	} else if(currentHistory[currentHistory.length - 1].hasOwnProperty('r')) {
		// Undoing a rotate
		jcropApi.setOptions({
				trueSize: [currentHistory[currentHistory.length -1].r.cw, currentHistory[currentHistory.length -1].r.ch]
		});
		$('#scale-width-' + id).val(currentHistory[currentHistory.length - 1].r.cw);
		$('#scale-height-' + id).val(currentHistory[currentHistory.length - 1].r.ch);
	} else if(currentHistory[currentHistory.length - 1].hasOwnProperty('s')) {
		// Undoing a scale.
		jcropApi.setOptions({
			trueSize: [currentHistory[currentHistory.length - 1].s.cw, currentHistory[currentHistory.length - 1].s.ch]
		});
		$('#scale-width-' + id).val(currentHistory[currentHistory.length - 1].s.cw);
		$('#scale-height-' + id).val(currentHistory[currentHistory.length - 1].s.ch);
	}
	// If for some reason the undo is active and there is no history - deactivate it.
	if(historyCount === 0 || typeof(historyCount) === 'undefined') {
		$('#tool-undo-' + id).addClass('tool-undo-inactive').removeClass('tool-undo cursor-pointer');
		return false;
	}
	// If no extended history set it to the current history.
	imageUrl = buildImageUrl('undo', '', id);
	// Disable ui clicks.
	disableImageEditorIcons(id);
	// Reduce the history count by 1
	historyCount -= 1;
	// If the history count has reach 0 deactivate undo and disable save.
	if(historyCount === 0) {
		$('#tool-undo-' + id).addClass('tool-undo-inactive').removeClass('tool-undo cursor-pointer');
		if($('#save-image-' + id).is(':enabled')) {
			 $('#save-image-' + id).button('disable').removeClass('ui-state-focus ui-state-hover ui-state-active');
		}
	}
	$('#tool-redo-' + id).addClass('tool-redo cursor-pointer').removeClass('tool-redo-inactive');
	$('#image-editor-main-' + id).fadeTo('fast', 0.6);
	// Reset the jcrop interface.
	jcropApi.release();
	jcropApi.setImage(imageUrl, function() {
		// Clear out the current selection and fade in the new image.
		$('#crop-selection-width-' + id + ', #crop-selection-height-' + id).val('');
		$('#image-editor-main-' + id).fadeTo('fast', 1);
		enableImageEditorIcons(id);
	});
}

function redoImageEdit(event) {
	'use strict';
	var id = event.data.id, imageUrl, currentHistory;
	// If redo is inactive disable clicks.
	if(currentHistory === null || $('#tool-redo-' + id).hasClass('tool-redo-inactive')) {
		return false;
	}
	imageUrl = buildImageUrl('redo', '', id);
	imageUrl = imageUrl.replace(/&rand=([0-9]*[^&])/, '&rand=' + rand(10000, 99999));
	// Disable ui clicks.
	disableImageEditorIcons(id);
	// Reset true size on redo depending on the action being undone.
	currentHistory = JSON.parse(urlHistory);
	if(currentHistory[currentHistory.length - 1].hasOwnProperty('c')) {
		// Redo crop
		jcropApi.setOptions({
			trueSize: [currentHistory[currentHistory.length - 1].c.w, currentHistory[currentHistory.length -1 ].c.h]
		});
		$('#scale-width-' + id).val(currentHistory[currentHistory.length - 1].c.w);
		$('#scale-height-' + id).val(currentHistory[currentHistory.length - 1].c.h);
	} else if(currentHistory[currentHistory.length - 1].hasOwnProperty('r')) {
		// Redo rotate
		jcropApi.setOptions({
				trueSize: [currentHistory[currentHistory.length -1].r.ch, currentHistory[currentHistory.length -1].r.cw]
		});
		$('#scale-width-' + id).val(currentHistory[currentHistory.length - 1].r.ch);
		$('#scale-height-' + id).val(currentHistory[currentHistory.length - 1].r.cw);
	} else if(currentHistory[currentHistory.length - 1].hasOwnProperty('s')) {
		// Redo scale
		jcropApi.setOptions({
				trueSize: [currentHistory[currentHistory.length -1].s.w, currentHistory[currentHistory.length -1].s.h]
		});
		$('#scale-width-' + id).val(currentHistory[currentHistory.length - 1].s.w);
		$('#scale-height-' + id).val(currentHistory[currentHistory.length - 1].s.h);
	}
	// Increase the history count by 1
	historyCount += 1;
	// If the history count is equal to the current extended history disabled the redo option.
	if(historyCount === extendedHistory.length) {
		$('#tool-redo-' + id).addClass('tool-redo-inactive').removeClass('tool-redo cursor-pointer');
	}
	$('#tool-undo-' + id).addClass('tool-undo cursor-pointer').removeClass('tool-undo-inactive');
	// Begin fade
	$('#image-editor-main-' + id).fadeTo('fast', 0.6);
	// If the save option is disabled; enable it.
	if($('#save-image-' + id).is(':disabled') && historyCount >= 1) {
		 $('#save-image-' + id).button('enable');
	}
	// Reset the jcrop interface.
	jcropApi.release();
	jcropApi.setImage(imageUrl, function() {
		// Clear out the current selection and fade in the new image.
		$('#crop-selection-width-' + id + ', #crop-selection-height-' + id).val('');
		$('#image-editor-main-' + id).fadeTo('fast', 1);
		enableImageEditorIcons(id);
	});
}

function scaleImage(event) {
	'use strict';
	var id = event.data.id, opts, newWidth, newHeight, s, imageUrl;
	opts = jcropApi.getOptions();
	newWidth = $('#scale-width-' + id).val();
	newHeight = $('#scale-height-' + id).val();
	s = {
		w  : newWidth,
		h  : newHeight,
		cw : opts.trueSize[0],
		ch : opts.trueSize[1]
	};
	imageUrl = buildImageUrl('s', s, id);
	// Disable ui clicks.
	disableImageEditorIcons(id);
	$('#image-editor-main-' + id).fadeTo('fast', 0.6);
	// Reset the jcrop interface.
	jcropApi.release();
	jcropApi.setImage(imageUrl, function() {
		// Change the true size to match the new dimensions (basically just reverse the previous dimensions).
		jcropApi.setOptions({
			trueSize: [newWidth, newHeight]
		});
		// Clear out the current selection and fade in the new image.
		$('#crop-selection-width-' + id + ', #crop-selection-height-' + id).val('');
		$('#image-editor-main-' + id).fadeTo('fast', 1);
		enableImageEditorIcons(id);
	});
}

function changeImageScale(event) {
	'use strict';
	var id = event.data.id, opts, scaleTotal, scaleWidthRatio, scaleHeightRatio, newWidth, newHeight;
	opts = jcropApi.getOptions();
	scaleTotal = opts.trueSize[0] + opts.trueSize[1];
	scaleWidthRatio = Math.round(opts.trueSize[0]) / Math.round(opts.trueSize[1]);
	scaleHeightRatio = Math.round(opts.trueSize[1]) / Math.round(opts.trueSize[0]);
	if($(this).hasClass('scale-width')) {
		newWidth = Math.round($(this).val());
		$('#scale-height-' + id).val(Math.round(newWidth * scaleHeightRatio));
	} else if($(this).hasClass('scale-height')) {
		newHeight = Math.round($(this).val());
		$('#scale-width-' + id).val(Math.round(newHeight * scaleWidthRatio));
	}
}

function restoreImage(event) {
	'use strict';
	var id = event.data.id, imageUrl, adminObj, jsonStr, d;
	// Disable any clicks.
	disableImageEditorIcons(id);
	imageUrl = buildImageUrl('restore', '', id);
	imageUrl = imageUrl.replace(/&rand=([0-9]*[^&])/, '&rand=' + rand(10000, 99999));
	adminObj = {
		type: 'media',
		action: 'restore',
		id: id,
		restoreAll: $('#restore-all-' + id).is(':checked'),
		csrfToken : $('#csrf-token').val()
	};
	jsonStr = JSON.stringify(adminObj);
	$('#media-save-ajax-' + id).show();
	$('#image-editor-main-' + id).fadeTo('fast', 0.6);
	$.ajax({
		url: HTTP_ADMIN + 'media',
		type: 'POST',
		datatype: 'json',
		data: 'adminRequest=' + encodeURIComponent(jsonStr),
		success: function(data, textStatus, jqXHR) {
			// Refresh main icon
			d = new Date();
			$('#preview-thumb-' + id).attr('src', HTTP_ADMIN + 'media/' + id + '?id=' + id + '&adminRequest=thumb-preview&csrfToken=' + csrfToken + '&' + d.getTime());
			$('#edit-image-thumb-' + id).attr('src', HTTP_ADMIN + 'media/' + id + '?id=' + id + '&adminRequest=thumb-preview&csrfToken=' + csrfToken + '&' + d.getTime());
			setTimeout(function() {
				jcropApi.release();
				jcropApi.setImage(imageUrl, function() {
					jcropApi.setOptions({
						trueSize: [origWidthArr[id], origHeightArr[id]]
					});
					// Set the global media width and height.
					mediaWidthArr[id] = origWidthArr[id];
					mediaHeightArr[id] = origHeightArr[id];
					mediaWidth = origWidthArr[id];
					mediaHeight = origHeightArr[id];
					$('#image-editor-main-' + id).fadeTo('fast', 1);
					// Close the image editor.
					if($('#media-uploads').length !== 0) {
						// Skip
					} else {
						$('#image-editor-' + id).toggle();
						$('#edit-btn-' + id).toggle();
						$('#media-properties-' + id).toggle();
					}
					// Clear out the current selection and fade in the new image.
					$('#crop-selection-width-' + id + ', #crop-selection-height-' + id).val('');
					$('#image-editor-main-' + id).fadeTo('fast', 1);
					// Enable icons
					enableImageEditorIcons(id);
					$('#media-save-ajax-' + id).hide();
				});
			}, 2500);
		}
	});
	// Hide restore box
	$('#restore-box-' + id).hide();
	// Deactivate the undo tool if active.
	$('#tool-undo-' + id).addClass('tool-undo-inactive').removeClass('tool-undo cursor-pointer');
	// If the save option is enabled; disabled it.
	if($('#save-image-' + id).is(':enabled')) {
		$('#save-image-' + id).button('disable');
	}
}

function saveImage(event) {
	'use strict';
	var id = event.data.id, opts, historyArr, adminObj, imageUrl, jsonStr, d;
	// Disable any clicks.
	disableImageEditorIcons(id);
	opts = jcropApi.getOptions();
	historyArr = JSON.parse(urlHistory);
	adminObj = {
		id : id,
		type :      'media',
		action :    'update-image',
		history :   historyArr,
		applyTo :   $('input[name=thumbOpts-' + id + ']:checked', '#adminForm').val(),
		csrfToken : $('#csrf-token').val(),
		width : mediaWidth,
		height : mediaHeight
	};
	jsonStr = JSON.stringify(adminObj);
	$('#image-editor-main-' + id).fadeTo('fast', 0.6);
	$('#media-save-ajax-' + id).show();
	$.ajax({
		url: HTTP_ADMIN + 'media',
		type: 'POST',
		datatype: 'json',
		data: 'adminRequest=' + encodeURIComponent(jsonStr),
		success: function(data, textStatus, jqXHR) {
			setTimeout(function() {
				// Refresh main icon
				d = new Date();
				$('#preview-thumb-' + id).attr('src', HTTP_ADMIN + 'media/' + id + '?id=' + id + '&adminRequest=thumb-preview&csrfToken=' + csrfToken + '&' + d.getTime());
				$('#edit-image-thumb-' + id).attr('src', HTTP_ADMIN + 'media/' + id + '?id=' + id + '&adminRequest=thumb-preview&csrfToken=' + csrfToken + '&' + d.getTime());
				$('#edit-image-preview' + id).attr('src',HTTP_ADMIN + 'media/' + id + '?id=' + id + '&adminRequest=image-preview&csrfToken=' + csrfToken + '&timestamp=' + d.getTime());
			}, 1500);
			setTimeout(function() {
				imageUrl = buildImageUrl('save', '', id);
				imageUrl = imageUrl.replace(/&rand=([0-9]*[^&])/, '&rand=' + rand(10000, 99999));
				$('#edit-image-preview-' + id).attr('src', imageUrl);
				jcropApi.setImage(imageUrl, function() {
					if($('#apply-thumb-' + id).is(':checked')) {
						// Set the width and height if just creating a thumbnail.
						jcropApi.setOptions({
							trueSize: [mediaWidth, mediaHeight]
						});
						mediaWidthArr[id] = mediaWidth;
						mediaHeightArr[id] = mediaHeight;
					} else {
						// Set the media width and height in the main array.
						mediaWidthArr[id] = opts.trueSize[0];
						mediaHeightArr[id] = opts.trueSize[1];
						mediaWidth = opts.trueSize[0];
						mediaHeight = opts.trueSize[1];
						jcropApi.setOptions({
							trueSize: [opts.trueSize[0], opts.trueSize[1]]
						});
					}
					$('#image-editor-main-' + id).fadeTo('fast', 1);
					// Clear out the current selection and fade in the new image.
					$('#crop-selection-width-' + id + ', #crop-selection-height-' + id).val('');
					$('#image-editor-main-' + id).fadeTo('fast', 1);
					// Enable icons
					enableImageEditorIcons(id);
					$('#media-save-ajax-' + id).hide();
				});
			}, 1500);
			// Show restore box
			$('#restore-box-' + id).show();
			// Disable undo if its active and if the save option is enabled; disabled it.
			$('#tool-undo-' + id).addClass('tool-undo-inactive').removeClass('tool-undo cursor-pointer');
		}
	}).done(function() {
		setTimeout(function() {
			// Close the image editor.
			$('#image-editor-' + id).toggle();
			$('#edit-btn-' + id).toggle();
			$('#media-properties-' + id).toggle();
		}, 1500);
	});
}

// To disable clicks during execution.
function disableImageEditorIcons(id) {
	$(document).off('click.rb-image-editor', '#crop-tool-' + id, cropImage);
	$(document).off('click.rb-image-editor', '#tool-flip-horz-' + id, flipHorizontally);
	$(document).off('click.rb-image-editor', '#tool-flip-vert-' + id, flipVertically);
	$(document).off('click.rb-image-editor', '#tool-rotate-c-' + id, rotateClockwise);
	$(document).off('click.rb-image-editor', '#tool-rotate-cc-' + id, rotateCounterClockwise);
	$(document).off('click.rb-image-editor', '#tool-undo-' + id, undoImageEdit);
	$(document).off('click.rb-image-editor', '#tool-redo-' + id, redoImageEdit);
	$('#scale-image-' + id).button('disable').removeClass('ui-state-focus ui-state-hover ui-state-active');
	$('#restore-image-' + id).button('disable').removeClass('ui-state-focus ui-state-hover ui-state-active');
	$('#save-image-' + id).button('disable').removeClass('ui-state-focus ui-state-hover ui-state-active');
}
// To enable clicks after execution.
function enableImageEditorIcons(id) {
	$(document).on('click.rb-image-editor', '#crop-tool-' + id, { id: id }, cropImage);
	$(document).on('click.rb-image-editor', '#tool-flip-horz-' + id, { id: id }, flipHorizontally);
	$(document).on('click.rb-image-editor', '#tool-flip-vert-' + id, { id: id }, flipVertically);
	$(document).on('click.rb-image-editor', '#tool-rotate-c-' + id, { id: id }, rotateClockwise);
	$(document).on('click.rb-image-editor', '#tool-rotate-cc-' + id, { id: id}, rotateCounterClockwise);
	$(document).on('click.rb-image-editor', '#tool-undo-' + id, { id: id }, undoImageEdit);
	$(document).on('click.rb-image-editor', '#tool-redo-' + id, { id: id }, redoImageEdit);
	$('#scale-image-' + id).button('enable');
	$('#restore-image-' + id).button('enable');
	$('#save-image-' + id).button('enable');
}

function loadImageEditor(id) {
	'use strict';
	// Enable jcrop
	$('#edit-image-preview-' + id).Jcrop({

	}, function() {
		jcropApi = this;
		jcropApi.setOptions({
			bgColor: 'transparent',
			trueSize: [mediaWidth,mediaHeight],
			onSelect: function() {
				$('.tool-crop-inactive').addClass('tool-crop cursor-pointer').removeClass('tool-crop-inactive');
			},
			onChange : function(coord) {
				$('.crop-selection-width').val(Math.round(coord.w));
				$('.crop-selection-height').val(Math.round(coord.h));
			},
			onRelease: function() {
				$('.tool-crop').addClass('tool-crop-inactive').removeClass('tool-crop cursor-pointer');
			}
		});
		jcropApi.release();
		// If manually entering the selection numbers change the crop selection accordingly
		$(document).on('change.rb-image-editor', '.crop-selection-width', { id: id }, setCropWidth);
		$(document).on('change.rb-image-editor', '.crop-selection-height', { id: id }, setCropHeight);
		// Crop ratio changes
		$(document).on('change.rb-image-editor', '.crop-ratio-width, .crop-ratio-height', { id: id }, changeCropRatio);
		// Crop clicks.
		$(document).on('click.rb-image-editor', '#crop-tool-' + id, { id: id }, cropImage);
		// Flip clicks
		$(document).on('click.rb-image-editor', '#tool-flip-horz-' + id, { id: id }, flipHorizontally);
		$(document).on('click.rb-image-editor', '#tool-flip-vert-' + id, { id: id }, flipVertically);
		// Rotate clicks
		$(document).on('click.rb-image-editor', '#tool-rotate-c-' + id, { id: id }, rotateClockwise);
		$(document).on('click.rb-image-editor', '#tool-rotate-cc-' + id, { id: id}, rotateCounterClockwise);
		// Undo clicks
		$(document).on('click.rb-image-editor', '#tool-undo-' + id, { id: id }, undoImageEdit);
		// Redo clicks
		$(document).on('click.rb-image-editor', '#tool-redo-' + id, { id: id }, redoImageEdit);
		// Scale clicks
		$(document).on('click.rb-image-editor', '#scale-image-' + id, { id: id }, scaleImage);
		// Scale changes
		$(document).on('change.rb-image-editor', '.scale-width, .scale-height', { id: id }, changeImageScale);
		// Restore clicks
		$(document).on('click.rb-image-editor', '#restore-image-' + id, { id: id }, restoreImage);
		// Save image
		$(document).one('click.rb-image-editor', '#save-image-' + id, { id: id }, saveImage);
	});
}

// Get image dimensions for images that have already been uploaded.
function getImageDimensions(id) {
	var adminObj, jsonStr, response, dimensions;
	adminObj = {
		type: 'media',
		action: 'get-image-dimensions',
		id : id,
		csrfToken : $('#csrf-token').val()
	}
	jsonStr = JSON.stringify(adminObj);
	$.ajax({
		url: HTTP_ADMIN + 'media',
		type: 'POST',
		datatype: 'json',
		data: 'adminRequest=' + encodeURIComponent(jsonStr),
		success: function(data, textStatus, jqXHR) {
			response = JSON.parse(data);
			dimensions = {
				width: response.width,
				height: response.height
			};
		}
	});
	$(document).ajaxStop(function() {
		return dimensions;
	});
}

// Translate keypress (used for attach search autocomplete).
function checkKey(e){
	switch (e.keyCode) {
		case 40:
			return 'down';
			break;
		case 38:
			return 'up';
			break;
		case 13:
			return 'enter';
			break;
		default:
			return false;
			break;
	 }
}

$(document).ready(function() {
	'use strict';
	// Define the global csrf-token variable.
	csrfToken = $("#csrf-token").val();
	// Define function variables.
	var sortable, openIds = [], x, noMediaError = "No media files selected, please select a media file from the table below and try again.";
	// Set the jqueryUi buttons.

	$('.save-image').button({ disabled: true });

	// Function to reposition the media-meta table if opening multiple image editors.
	function closeOpenIds(openId) {
		if(typeof(openId) !== "undefined") {
			$("#media-profile-" + openId).after($("#media-meta-" + openId));
			$("#image-editor-main-" + openId + "> #media-meta-" + openId).remove();
			$("#media-meta-" + openId).css("margin-top", "0");
		} else {
			// Go through each open image editor and put the media-meta table back in its original position.
			for(x = 0; x < openIds.length; x++) {
				// Copy and paste the media-meta table to its original position under the media-profile table.
				$("#media-profile-" + openIds[x]).after($("#media-meta-" + openIds[x]));
				// Remove the old media-meta table.
				$("#image-editor-main-" + openIds[x] + "> #media-meta-" + openIds[x]).remove();
				// Reset the media-meta margin back to 0.
				$("#media-meta-" + openIds[x]).css("margin-top", "0");
			}
		}
	}

	// Handle toggling of file details in the media overview.
	$(document).on("click", ".details-toggle", function() {
		var id = this.id.replace("toggle-", "")
		$('#media-body-' + id).toggle();
		$('#media-meta-' + id).toggle();
		if($('#media-body-' + id).is(':visible')) {
			$(this).html("Hide");
			$("#file-" + id).css("border-bottom", "1px solid #C0C0C0");
		} else {
			$(this).html("Show");
			$("#file-" + id).css("border-bottom", "none");
		}
	});
	$(document).on("click", ".delete-media", function() {

	});
	// Open the image editor
	$(document).on('click.rb-image-editor', '.edit-image', function(e) {
		var id = $(this).prop("id").replace("edit-btn-", ""), fileId, imageUrl, dimensions;
		
		e.preventDefault();
		
		// If editing after an upload else if editing an previously uploaded file
		if($("#media-uploads").length !== 0 || $('.media-attachment').length !== 0) {
			// Assign global width and height.
			mediaWidth = mediaWidthArr[id];
			mediaHeight = mediaHeightArr[id];
			// If the image has not been assigned the jqueryui sortable, assign it.
			if(sortableArr.indexOf(id) === -1) {
				// Load the jqueryUi sortable.
				loadSortable(id);
				sortableArr.push(id);
			}
			if(typeof(jcropApi) !== "undefined") {
				jcropApi.destroy();
				if(urlHistory) { urlHistory = null }
				if(extendedHistory) { extendedHistory = null }
				historyCount = 0;
				// Disable undo
				$("#tool-undo-" + id).addClass("tool-undo-inactive").removeClass("tool-undo cursor-pointer");
				$("#save-image-" + id).button("disable").removeClass("ui-state-focus ui-state-hover ui-state-active");
				// Disable redo
				$("#tool-redo-" + id).addClass("tool-redo-inactive").removeClass("tool-redo cursor-pointer");
			}
			$("#media-ajax-" + id).show();
			loadImageEditor(id);
			// Set image
			imageUrl = $("#edit-image-preview-" + id).attr("src");
			// Check for rand and update it, if it's not there set it.
			if(imageUrl.match(/&rand=([0-9]*[^&])/)) {
				imageUrl = imageUrl.replace(/&rand=([0-9]*[^&])/, '&rand=' + rand(10000, 99999));
			} else {
				imageUrl = imageUrl + '&rand=' + rand(10000, 99999);
			}
			$('#edit-image-preview-' + id).attr('src', imageUrl);
			setTimeout(function() {
				if(typeof(jcropApi) !== 'undefined') {
					jcropApi.setImage(imageUrl, function() {

					});
				}
				$('#media-ajax-' + id).hide();
				$('.image-editor-placeholder').hide();
				// Reset width and height.
				$('#scale-width-' + id).val(mediaWidth);
				$('#scale-height-' + id).val(mediaHeight);
				// Close all other image editors and show only the currently selected one.
				$('.media-properties').show();
				$('.edit-image').show();
				$('#edit-btn-' + id).hide();
				$('#media-properties-' + id).hide();
				$('#image-editor-' + id).show();
				closeOpenIds();
				// Move the attributes table inside of the image editor to make it line up better.
				$("#media-meta-" + id).appendTo("#image-editor-main-" + id).css("margin-top", "60px");
				// Add the opened id to the opened ids array
				openIds.push(id);
			}, 2000);
		} else {
			$('#media-ajax-' + id).show();
			loadImageEditor(id);
			setTimeout(function() {
				$('#media-ajax-' + id).hide();
				$('#edit-btn-' + id).toggle();
				$('#media-properties-' + id).toggle();
				$('#image-editor-' + id).toggle();
				closeOpenIds();
				// Move the attributes table inside of the image editor to make it line up better.
				$("#media-meta-" + id).appendTo("#image-editor-main-" + id).css("margin-top", "60px");
				// Add the opened id to the opened ids array
				openIds.push(id);
			}, 2000);
		}
	});
	// Closing the image editor window.
	$(document).on('click.rb-image-editor', '.close-image', function(e) {
		var id = $(this).attr('id').replace('close-image-', '');
		
		e.preventDefault();
		
		openIds.remove(id);
		if($('#media-uploads').length !== 0) {
			$('#edit-btn-' + id).toggle();
			$('#media-properties-' + id).toggle();
			$('#image-editor-' + id).toggle();
		} else {
			$('#edit-btn-' + id).toggle();
			$('#media-properties-' + id).toggle();
			$('#image-editor-' + id).toggle();
		}
		// Move the attributes table inside of the image editor to make it line up better.
		closeOpenIds(id);
	});

	// Media overview filter change.
	$('#typeFilter, #attachFilter').change(function() {
		document.getElementById('adminForm').submit();
	});

	// jqueryUI modal window for media attachments.
	if($('.attach-media').length !== 0) {
		$('#media-attach-form').dialog({
			autoOpen    : false,
			height      : 500,
			width       : 900,
			dialogClass : "media-attach-form",
			modal       : true,
			open        : function() {
				hideMenus();
				
				$('.ui-widget-overlay').bind('click', function(){
					$('#media-attach-form').dialog('close');
				});
			},
			buttons: {
				Attach: function() {
					var attachments = [], adminObj, jsonStr, response, x;
					$.each($('input[name="mediaAttachment[]"]:checked'), function() {
						attachments.push($(this).val());
					});

					$.ajax({
						url: HTTP_ADMIN + 'media',
						type: 'POST',
						datatype: 'json',
						data: 'adminRequest=' + encodeURIComponent(JSON.stringify({
							id          : attachId,
							type        : 'media',
							action      : 'attach-media',
							attachTo    : $('input:radio[name="attachSearch"]:checked').val().toLowerCase(), // tell whether or not we're dealing with articles or pages
							attachments : attachments,
							csrfToken   : $('#csrf-token').val()
						})),
						success: function(data, textStatus, jqXHR) {
							response = JSON.parse(data);
							if(response.attached.length !== 0) {
								for(x = 0; x < response.attached.length; x++) {
									if(response.attachTo === 'articles') {
										$('#media-attach-' + response.id).parent().before(
										'<p class="fltlft">' +
											'<a href="' + response.attached[x].url + '">' + response.attached[x].title + '</a>' +
											'<span>, ' + response.currentDate + '</span>' +
										'</p>' +
										'<p id="delete-attachment-' + response.id + '" class="small-close fltlft"> </p>' +
										'<p class="clearfix"> </p>');
									} else if(response.attachTo === 'pages') {
										$('#media-attach-' + response.id).parent().before(
										'<p>' +
											'<a href="' + response.attached[x].url + '">' + response.attached[x].title + '</a>' +
											'<span>, ' + response.currentDate + '</span>' +
										'</p>' +
										'<p id="delete-attachment-' + response.id + '" class="small-close fltlft"> </p>' +
										'<p class="clearfix"> </p>');
									}
								}
							}
						}
					});
					$(this).dialog('close');
					$(this).dialog('close');
					$('#article-page-search').val('');
					$('table#search-results').html('');
					$('table#search-results-list').hide();
				},
				Close: function() {
					showMenus();
					$(this).dialog('close');
					$('#article-page-search').val('');
					$('table#search-results').html('');
					$('table#search-results-list').hide();
				}
			}
		});

		$('.attach-media').click(function() {
			$('#media-attach-form').dialog('open');
		});
	} else {
		$('#media-attach-form').hide();
	}

	// Set id for attach form and clear out any existing results.
	$('.attach-media').click(function() {
		attachId = this.id.replace('media-attach-', '');
		$('#article-page-search').val('');
		$('table#search-results').html('');
		$('#media-attach-form').dialog('open');
		return false;
	});

	// Select search options click.
	$('#attach-select-search').click(function() {
		$('#attach-search-options').toggle();
	});

	// Search option click
	$('input:radio[name="attachSearch"]').click(function() {
		var optionValue;
		optionValue = $(this).val();
		$('#attach-select-search').text(optionValue);
	});
	// Clear attach search if switching between articles and pages
	$('input:radio[name="attachSearch"]').change(function() {
		//$("#article-page-search").val('');
	});

	// Handle enter clicks if no autocomplete options have been selected.
	if($('#attach-select-search').is(':focus')) {
		if($('#search-results').children('autocomplete-highlight').length === 0) {
			$(document).keypress(function(e) {
				var keyPressed, adminObj, jsonStr, searchWord;
				e.preventDefault();
				keyPressed = checkKey(e);
				if(keyPressed === 'enter') {

				}
			});
		}
	}

	// Attach form autocomplete
	$('#article-page-search').keyup(function(e) {
		var adminObj, id, searchWord, jsonStr, response, x, keyDir;
		// Keyup only on letters.
		if(e.keyCode <= 90 && e.keyCode >= 48) {
			// Clear results table.
			$('#search-results').html('');
			searchWord = $('input#article-page-search').val();
			if(searchWord === '') {
				return false;
			}
			adminObj = {
				id : attachId,
				type : 'media',
				action : 'search-' + $('input:radio[name="attachSearch"]:checked').val().toLowerCase(), // tell whether or not we're dealing with articles or pages
				searchWord : searchWord,
				results : '',
				csrfToken : $('#csrf-token').val()
			};
			jsonStr = JSON.stringify(adminObj);
			$.ajax({
				url: HTTP_ADMIN + 'media',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(jsonStr),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					// Clear out previous results.
					$('#search-results').html('');
					for(x = 0; x < response.results.length; x++) {
						$('#search-results').append(
							'<tr id="' + response.results[x].id + '" class="bold">' +
								'<td>' +
									response.results[x].title.replace(response.searchWord, '<span style="font-weight: normal; ">' + response.searchWord + '</span>') +
								'</td>' +
							'</tr>'
						);
					}
				}
			});
		}
	});

	// Handle autocomplete behavior
	// Used to be separated to deal with IE bugs, not bothering now after loss of $.browser support in 1.9.
	$(document).keypress(function(e) {
		var keyDir, autoText, searchWord, adminObj, jsonStr, response, x;
		keyDir = checkKey(e);
		if($('#search-results').children().length > 0) {
			if($('#search-results tbody').children('.autocomplete-highlight').length != 0) {
				if(keyDir === 'down') {
					$('tr.autocomplete-highlight', '#search-results').removeClass('autocomplete-highlight').next().addClass('autocomplete-highlight');
				} else if(keyDir === 'up') {
					$('tr.autocomplete-highlight', '#search-results').removeClass('autocomplete-highlight').prev().addClass('autocomplete-highlight');
				}

			} else {
				if(keyDir === 'down') {
					$('#search-results tbody').children('tr:first').addClass('autocomplete-highlight');
				} else if(keyDir === 'up') {
					$('#search-results tbody').children('tr:last').addClass('autocomplete-highlight');
				}
			}
		}
		if(keyDir === 'enter') {
			e.preventDefault();
			// Test if an autocomplete has been selected if not the search word is the current value.
			autoText = $('#search-results tbody').children('.autocomplete-highlight').text();
			if(autoText.length > 0){
				$('#article-page-search').val(autoText);
			}
			searchWord = $('input#article-page-search').val();
			$('#search-results').html('');
			// If there results present remove them else retreive the new results.
			if($('tbody#search-results-details').children().length > 0) {
				$('table#search-results-list').hide();
				$('tbody#search-results-details').html('');
			}
			if(searchWord === '') {
				return false;
			}
			adminObj = {
				id : attachId,
				type : 'media',
				action : 'get-search-result',
				contentToSearch : $('input:radio[name="attachSearch"]:checked').val().toLowerCase(),
				searchWord : searchWord,
				csrfToken : $('#csrf-token').val()
			};
			jsonStr = JSON.stringify(adminObj);
			$.ajax({
				url: HTTP_ADMIN + 'media',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(jsonStr),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					// Show the details of the search
					$('#search-results-list').show();
					for(x = 0; x < response.results.length; x++) {
						$('tbody#search-results-details').append(
							'<tr id="' + response.results[x].id + '">' +
								'<td>' +
									'<input type="checkbox" name="mediaAttachment[]" value="' + response.contentToSearch + '-' + response.results[x].id + '" />' +
								'</td>' +
								'<td class="left">' +
									response.results[x].title +
								'</td>' +
								'<td class="left">' +
									response.results[x].date_posted +
								'</td>' +
								'<td class="left">' +
									response.results[x].published +
								'</td>' +
							'</tr>'
						);
					}
				}
			});
		}
	});

	// Inserting an attachment into an article.
	$(document).on('click', '.insert-media-btn', function(e) {
		e.preventDefault();
		var mediaId = window.parent.$(this).attr('id').replace('insert-into-article-', ''), editorId = window.parent.tinyMCE.activeEditor.editorId, title, alt, caption,
			description, align, imageTemplate, url, linkUrl, templateId, media, adminObj, response, jsonStr;
		// If an image insert an image else insert a link to the media.
		if($('#media-sizes-' + mediaId).length !== 0) {
			title = $('#uploads-' + mediaId + '-title').val();
			alt = $('#uploads-' + mediaId + '-alt').val();
			caption = $('#uploads-' + mediaId + '-caption').val();
			description = $('#uploads-' + mediaId + '-description').val();
			align = $('input[name="mediaAlign[' + mediaId + ']"]:checked').val();
			templateId = $('input[name="imageTemplate[' + mediaId + ']"]:checked').val();
			adminObj = {
				type : 'media',
				action : 'get-url-for-image',
				id : mediaId,
				templateId : templateId,
				csrfToken : $('#csrf-token').val()
			};
			jsonStr = JSON.stringify(adminObj);
			$.ajax({
				url: HTTP_ADMIN + 'media',
				type: 'POST',
				datatype: 'json',
				data: 'adminRequest=' + encodeURIComponent(jsonStr),
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);
					if(response.thumbUrl !== null) {
						url = response.thumbUrl
					} else {
						url = $('#link-url-' + mediaId).val();
					}
				}
			}).done(function() {
				// Build the image attachment.
				media = '<img';
				if(typeof(url) !== 'undefined' && url !== null) {
					media += ' src="' + url + '"';
				}
				if(typeof(title) !== "undefined" && title !== null) {
					media = media + ' title="' + title + '"';
				}
				if(typeof(alt) !== "undefined" && alt !== null) {
					media = media + ' alt="' + alt + '"';
				}
				media = media + ' />';
				window.parent.$('#' + editorId).tinymce().execCommand('mceInsertContent', false, media);
				window.setTimeout(function() {
					window.parent.$('#' + editorId).tinymce().execCommand('mceAutoResize');
					// Close the add media dialog.
					$('#add-media-window').dialog('close');
				}, 100);
			});
		} else {

		}
	});

	// Delete media attachment
	$(document).on('click', '.deleteMediaAttach', function(e) {
		var attachId, adminObj, jsonStr, response;
		attachId = this.id.replace('delete-attachment-', '');
		adminObj = {
			type : 'media',
			action : 'delete-media-attachment',
			id : attachId,
			csrfToken : $('#csrf-token').val()
		};
		jsonStr = JSON.stringify(adminObj);
		$.ajax({
			url: HTTP_ADMIN + 'media',
			type: 'POST',
			datatype: 'json',
			data: 'adminRequest=' + encodeURIComponent(jsonStr),
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
				$('#media-attachment-' + attachId).remove();
			}
		}).done(function() {

		});
	});

	// Ensure the sortable is only included once.
	sortable = $('div.panel:data(sortable)');
	if(!sortable.length) {
		//Initialize the sortable jqueryui feature for the panels.
		$( '.panel' ).sortable({
			connectWith : '.panel',
			handle : $('.element-top'),
			placeholder : 'profile-placeholder',
			start : function(e, ui) {
				ui.placeholder.height(ui.item.height());
			},
			update : function(e, ui) {
				$('.panel').each(function() {
					if($(this).children().length === 0) {
						$(this).css('margin-bottom', 0);
					}
				});
			}
		});

		$('.panel-column').addClass('ui-widget ui-widget-content ui-helper-clearfix ui-corner-all')
			.find('.element-top')
				.addClass('ui-widget-header ui-corner-all' )
				.prepend("<span class='ui-icon ui-icon-minusthick'></span>")
				.end()
			.find('.element-body');

		$('.element-top .ui-icon').click(function() {
			var elementTop = $(this).parents(".element-top")
			  , elemIcon = $(this).parents(".element-top").children(".ui-icon");
			$(this).toggleClass( 'ui-icon-minusthick' ).toggleClass('ui-icon-plusthick');
			$(this).parents('.panel-column:first' ).find( '.element-body').toggle();
			// Show/hide the bottom border of the element-top div when toggling visibility.
			if(elemIcon.hasClass("ui-icon-minusthick")) {
				console.log("plus");
				elementTop.css({
					"border-bottom" : "1px solid #C0C0C0"
				})
			} else {
				console.log("minus");
				elementTop.css({
					"border-bottom" : "none"
				})
			}
		});
		// Adds placeholder support for unsupporting browswers.
		$('input[placeholder], textarea[placeholder]').placeholder();

		// Hide scale and restore image.
		$('.scale-image > .panel-column > .element-top > .ui-icon-minusthick').toggleClass('ui-icon-plusthick').toggleClass('ui-icon-minusthick');
		$('.scale-image > .panel-column > .element-body').hide();
		$('.restore-image > .panel-column > .element-top > .ui-icon-minusthick').toggleClass('ui-icon-plusthick').toggleClass('ui-icon-minusthick');
		$('.restore-image > .panel-column > .element-body').hide();
	}
	// Edit media clicks.
	$("#toolbar-edit, #action-edit-media").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noMediaError);
		} else {
			CMS.submitButton('media', 'edit');
		}
	});
	// Delete media clicks.
	$("#toolbar-delete, #action-delete-media").click(function() {
		if(typeof(boxesChecked) === "undefined" || boxesChecked == 0) {
			alert(noMediaError);
		} else {
			CMS.submitButton('media', 'delete');
		}
	});
	// Publish media button clicks.
	$(".publish-media-content").click(function() {
		$("#media-state").val(1);
		CMS.submitButton("media", "save-close");
	});
	// Draft media button clicks
	$(".draft-media-content").click(function() {
		$("#media-state").val(2);
		CMS.submitButton("media", "save");
	});
	// Save media clicks
	$("#toolbar-save, #action-save-media").click(function() {
		CMS.submitButton("media", "save");
	});
	// Save and new media clicks
	$("#toolbar-save-new, #action-save-new-media").click(function() {
		CMS.submitButton("media", "save-new");
	});
	// Save and close media clicks
	$("#toolbar-save-close, #action-save-close-media").click(function() {
		selectNavItem("#medias-link", "#new-media-link");
		CMS.submitButton("media", "save-close");
	});
	// Close the media editor clicks
	$("#toolbar-close-media, #action-close-media").click(function() {
		selectNavItem("#medias-link", "#new-media-link");
		window.location.href = HTTP_ADMIN + "medias";
	});
	
	// Expand media thumbnail
	if($(".expand-thumbnail").length !== 0) {
		$(".expand-thumbnail").fancybox({
			type: 'image',
			padding: 5,
			autoSize: true,
			autoScale: true,
			autoCenter: true,
			beforeLoad: function() {
				var id = this.element[0].id.replace("expand-thumb-", "")
				  , href = $("#expand-" + id).val();
				this.href = href;
				hideMenus(true);
			},
			beforeClose: function() {
				showMenus();
			}
		});
	}
});
