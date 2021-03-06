// JavaScript Document

// Plupload uploader
$(document).ready(function() {
	"use strict";
	
	$("#plupload-browser-button").click(function(e) {
		e.preventDefault();
	});
	
	if($("#plupload-container").length !== 0 && $("#plupload-browser-button").length !== 0) {
		var uploader;
		// Initialize plupload.
		uploader = new plupload.Uploader({
			runtimes : 'html5,flash,silverlight,browserplus,html4',
			container : 'plupload-container',
			browse_button : 'plupload-browser-button',
			max_file_size : '1000mb',
			url : HTTP_ADMIN + 'media-upload',
			flash_swf_url : HTTP_ADMIN_DIR + 'view/js/plupload/js/plupload.flash.swf',
			silverlight_xap_url : HTTP_ADMIN_DIR + 'view/js/plupload/js/plupload.silverlight.xap',
			drop_element : 'drag-drop-area',
			chunk_size : '8mb',
			filters : [
				{
					title : "Allowed Extensions",
					extensions : allowedExtensions
				}
			]
		});

		uploader.bind('Init', function(up, params) {
			//$('#filelist').html("<div>Current runtime: " + params.runtime + "</div>");
		});

		uploader.init();

		uploader.bind('FilesAdded', function(up, files) {
			
			$.each(files, function(i, file) {
				var ext = getFileExtension(file.name);
				file.extensionIcon = getExtensionIcon(ext, 32);
				$('#media-uploads').append(
					'<table id="media-profile-' + file.id + '" class="media-profile media-details">' +
						'<thead id="media-head-' + file.id + '>' +
							'<tr>' +
								'<td id="media-data-' + file.id + '" colspan="2">' +
									'<div id="' + file.id + '" class="media-upload">' +
										'<div id="icon-' + file.id + '" class="' + file.extensionIcon + ' media-file-icon media-file-spacing"></div>' +
										'<div class="media-upload-file">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>' +
										'<div id="progressbar-' + file.id + '" class="progressbar"></div>' +
									'</div>' +
								'</td>' +
							'</tr>' +
						'</thead>' +
						'<tbody id="media-body-' + file.id + '" style="display: none;">' +

						'</tbody>' +
					'</table>');
				uploader.start();
			});
			up.refresh(); // Reposition Flash/Silverlight
		});

		uploader.bind('BeforeUpload', function (up, file) {
			up.settings.url = HTTP_ADMIN + 'media-upload?csrfToken=' + document.getElementById('csrf-token').value;
			if(!$("#media-uploads").is(":visible")) {
				$("#media-uploads").show();
			}
		});

		uploader.bind('UploadProgress', function(up, file) {
			$(function() {
				$( "#progressbar-" + file.id ).progressbar({
					value: file.percent
				}).children('.ui-progressbar-value')
				  .html('<div class="progressbar-value">' + file.percent.toPrecision(3) + '%</div>')
				  .css("display", "block")
			});
			$('#' + file.id).addClass('media-upload');
			$('#progressbar-' + file.id).append('<div>' + file.percent + '%</div>');
		});

		uploader.bind('Error', function(up, err) {
			$('#media-uploads').append(
				'<div class="media-upload errors">' +
					'<div class="media-upload-file">' +
						'<span class="ui-icon ui-icon-alert fltlft"></span>' +
						'<span class="media-error-txt">Error: ' + err.message  + (err.file ? " File: " + err.file.name : "") + '</span>' +
					'</div>' +
				'</div>'
			);
			if(!$("#media-uploads").is(":visible")) {
				$("#media-uploads").show();
			}
			up.refresh(); // Reposition Flash/Silverlight
		});

		uploader.bind('FileUploaded', function(up, file, info) {
			var response = JSON.parse(info.response), ext = getFileExtension(file.name), previewIcon, x;
			if(response.mediaThumb != false) {
				// Make the extension icon the thumbnail using maxWidth an maxHeight to resize.
				$('#icon-' + file.id).removeClass(file.extensionIcon).removeClass('media-file-icon').append('<img src="' + response.mediaThumb +'" class="smallnail" />');
				previewIcon = '';
			} else {
				previewIcon = getExtensionIcon(ext, 64);
			}
			$('#' + file.id).addClass('media-upload');

			var x;
			$('#progressbar-' + file.id).remove();
			$('#' + file.id).append(
				'<a id="toggle-' + response.id + '" class="details-toggle">Show</a>'
			);
			//
			// Switch the file id to the main id, hackish but fuck it.
			$("#media-body-" + file.id).prop("id", "media-body-" + response.id);
			$("#media-profile-" + file.id).prop("id", "media-profile-" + response.id);
			$("#media-attribs-" + file.id).prop("id", "media-attribs-" + response.id);
			$("#" + file.id).prop("id", "file-" + response.id);
			//
			//

			// Append the file specific data to the table.
			$("#media-body-" + response.id).append(
				'<tr id="media-properties-' + response.id + '" class="media-properties">' +
					'<td id="preview-thumb-container-' + response.id + '" class="media-attribs preview-thumb-container">' +
						'<p class="media-details-icon ' + previewIcon + '"></p>' +
					'</td>' +
					'<td id="media-attribs-' + response.id + '" class="media-attribs media-attribs-container">' +
						'<p><strong>File name:</strong> ' + file.name + '</p>' +
						'<p><strong>Mime type:</strong> ' + response.mediaType + '</p>' +
						'<p><strong>Upload date:</strong>' + response.uploadDate + '</p>' +
					'</td>' +
				'</tr>' +
				'<tr>' +
					'<td colspan="2">&nbsp;</td>' +
				'</tr>'
			);
			$("#media-profile-" + response.id).after(
				'<table id="media-meta-' + response.id + '" class="media-meta media-details" style="display: none;">' +
					'<tr id="media-title-' + response.id + '">' +
						'<th>' +
							'<label for="uploads-' + response.id + '-title">Title</label>' +
						'</th>' +
						'<td class="media-inputs">' +
							'<input type="hidden" name="uploads[' + response.id + '][id]" value="' + response.id + '" />' +
							'<input type="text" id="uploads-' + response.id + '-title" class="media-field" name="uploads[' + response.id + '][title]" value="' + response.mediaTitle + '" />' +
						'</td>' +
					'</tr>' +
					'<tr>' +
						'<th>' +
							'<label for="uploads-' + response.id + '-caption">Caption</label>' +
						'</th>' +
						'<td class="media-inputs">' +
							'<input type="text" id="uploads-' + response.id + '-caption" class="media-field" name="uploads[' + response.id + '][caption]" />' +
						'</td>' +
					'</tr>' +
					'<tr>' +
						'<th>' +
							'<label for="uploads-' + response.id + '-description">Description</label>' +
						'</th>' +
						'<td class="media-inputs">' +
							'<textarea id="uploads-' + response.id + '-description" class="media-field" name="uploads[' + response.id + '][description]"></textarea>' +
						'</td>' +
					'</tr>' +
					'<tr id="media-link-' + response.id + '">' +
						'<th>' +
							'<label id="media-link-label-' + response.id + '" for="link-url-' + response.id + '">Location</label>' +
						'</th>' +
						'<td id="media-link-container-' + response.id + '" class="media-inputs">' +
							'<input type="hidden" id="url-"' + response.id + '" value="' + response.mediaUrl + '" />' +
							'<input type="text" id="link-url-' + response.id + '" class="media-field" name="uploads[' + response.id + '][url]" readonly="readonly" value="' + response.mediaUrl + '" />' +
						'</td>' +
					'</tr>' +
					'<tr>' +
						'<td></td>' +
						'<td>' +
							'<a id="delete-' + response.id + '" class="delete-media" href="#">Delete</a>' +
						'</td>' +
					'</tr>' +
				'</table>'
			);
			// If attaching media to an article.
			if((typeof(articleAttach) !== "undefined" && articleAttach === true) || (typeof(window.parent.articleAttach) !== "undefined" && window.parent.articleAttach === true)) {
				$("#media-link-label-" + response.id).text("Link URL");
				$("#media-link-container-" + response.id).append(
					'<div>' +
						'<button id="url-none-' + response.id + '" class="url-none">None</button>' +
						'<button id="url-file-' + response.id + '" class="url-file">File URL</button>' +
					'</div>'
				);
				if(response.imageSize.length > 0) {
					$("#media-link-" + response.id).after(
						'<tr id="media-align-' + response.id + '">' +
							'<th>' +
								'<label for="media-align-' + response.id + '">Alignment</label>' +
							'</th>' +
							'<td>' +
								'<label class="inline">' +
									'<input type="radio" name="mediaAlign" class="center-toggle" checked="checked" />' +
									'None' +
								'</label>' +
								'<label class="center-label inline">' +
									'<input type="radio" name="mediaAlign" class="center-toggle" />' +
									'Left' +
								'</label>' +
								'<label class="center-label inline">' +
									'<input type="radio" name="mediaAlign" class="center-toggle" />' +
									'Center' +
								'</label>' +
								'<label class="center-label inline">' +
									'<input type="radio" name="mediaAlign" class="center-toggle" />' +
									'Right' +
								'</label>' +
							'</td>' +
						'</tr>' +
						'<tr>' +
							'<th>' +

							'</th>' +
							'<td>' +
								'<button id="insert-into-article-' + response.id + '" class="insert-media-btn vert-space">Insert into Article</button>' +
							'</td>' +
						'</tr>'
					);
					$("#media-align-" + response.id).after(
						'<tr>' +
							'<th>' +
								'<label>Size</label>' +
							'</th>' +
							'<td id="media-sizes-' + response.id + '">' +
							'</td>' +
						'</tr>'
					);
					if(typeof(imageTemplates) === "undefined") {
						// If loaded in an iframe grabe the image templates global - this is sloppy but it will have to do for now.
						var imageTemplates = window.parent.imageTemplates;
					}
					for(x = 0; x < imageTemplates.length; x++) {
						$("#media-sizes-" + response.id).append(
							'<p>' +
								'<label>' +
									'<input id="template-' + imageTemplates[x].id + '" type="radio" name="imageTemplate[' + imageTemplates[x].id + ']" class="center-toggle" value="' + imageTemplates[x].id + '" />' +
									imageTemplates[x].template_name + ' (' + imageTemplates[x].template_width + ' &times; ' + imageTemplates[x].template_height + ')' +
								'</label>' +
							'</p>'
						);
					}
					// Make the medium image template the default template.
					$('#template-13').prop('checked', 'checked');
				}
			}
			// If the file being uploaded is an image
			if(response.imageSize.length > 0) {
				// Set the width and height
				origWidthArr[response.id] = response.width;
				origHeightArr[response.id] = response.height;
				mediaWidthArr[response.id] = response.width;
				mediaHeightArr[response.id] = response.height;
				$('#media-attribs-' + response.id).append('<p><strong>Dimensions:</strong>' + response.imageSize + '</p>');
				$('#preview-thumb-container-' + response.id).empty().append('<img id="preview-thumb-' + response.id + '" src="' + response.mediaThumb +'" class="media-image-icon" />');
				$('#media-properties-' + response.id).after(
					'<tr id="image-editor-' + response.id + '" style="display: none;" class="image-editor-placeholder">' +
						'<td colspan="2">' +
							'<div class="image-editor-wrap">' +
								'<table class="image-editor">' +
									'<tbody>' +
										'<tr>' +
											'<td id="image-editor-main-' + response.id + '" class="image-editor-main width60pcnt">' +
												'<div id="image-tools-' + response.id + '" class="image-editor-tools clearfix">' +
													'<div id="crop-tool-' + response.id + '" class="image-tools tool-crop-inactive" title="Crop">' +
													'</div>' +
													'<div id="tool-rotate-cc-' + response.id + '" class="image-tools tool-rotate tool-rotate-cc cursor-pointer" title="Rotate counter-clockwise">' +

													'</div>' +
													'<div id="tool-rotate-c-' + response.id + '" class="image-tools tool-rotate tool-rotate-c cursor-pointer" title="Rotate clockwise">' +

													'</div>' +
													'<div id="tool-flip-vert-' + response.id + '" class="image-tools tool-flip tool-flip-vert cursor-pointer" title="Flip vertically">' +

													'</div>' +
													'<div id="tool-flip-horz-' + response.id + '" class="image-tools tool-flip tool-flip-horz cursor-pointer" title="Flip horizontally">' +

													'</div>' +
													'<div id="tool-undo-' + response.id + '" class="image-tools tool-undo-inactive" title="Undo">' +

													'</div>' +
													'<div id="tool-redo-' + response.id + '" class="image-tools tool-redo-inactive" title="Redo">' +

													'</div>' +
												'</div>' +
												'<div id="edit-image-preview-container-' + response.id + '">' +
													'<img id="edit-image-preview-' + response.id + '" class="media-image-icon edit-image-preview" src="' + HTTP_ADMIN + 'media/' + response.id + '?id=' + response.id + '&adminRequest=image-preview&csrfToken=' + csrfToken + '&timestamp=' + new Date().getTime() + '" />' +
												'</div>' +
												'<div class="width60pcnt">' +
													'<button id="save-image-' + response.id + '" class="save-image button-spacing rb-btn blue-btn fltlft">Save Image</button>' +
													'<button id="close-image-' + response.id + '" class="close-image button-spacing rb-btn light-gray-btn fltlft">Close</button' +
													'<img id="media-save-ajax-' + response.id + '" src="' + HTTP_IMAGE + 'admin/gifs/ajax-loader-snake.gif" alt="loading" class="media-ajax block" style="display: none;" />' +
												'</div>' +
											'</td>' +
											'<td class="media-tools width40pcnt">' +
												'<div class="detail-padding">' +
													'<div id="restore-box-' + response.id + '" class="panel restore-image" style="display: none;">' +
														'<div class="panel-column">' +
															'<div class="element">' +
																'<div class="element-top">Restore Image</div>' +
																'<div class="element-body">' +
																	'<p>' +
																		'<em>Discard any changes and restore the original image.</em>' +
																	'</p>' +
																	'<p>' +
																		'<label for="restore-all-' + response.id + '" class="center-label">' +
																			'<input type="checkbox" id="restore-all-' + response.id + '" class="center-toggle" name="restoreAllImages" />' +
																			'Restore all images and thumbnails' +
																		'</label>' +
																	'</p>' +
																	'<p>' +
																		'<button id="restore-image-' + response.id + '" class="restore-orig-image rb-btn light-gray-btn">Restore Image</button>' +
																	'</p>' +
																'</div>' +
															'</div>' +
														'</div>' +
													'</div>' +
													'<div class="panel scale-image">' +
														'<div class="panel-column">' +
															'<div class="element">' +
																'<div class="element-top">Scale image</div>' +
																'<div class="element-body">' +
																	'<p>' +
																		'<em>For best results scale the image before performing additional editing actions.</em>' +
																	'</p>' +
																	'<p>' +
																		'<em>Original dimensions: ' + response.width + '&times;' + response.height + '</em>' +
																	'</p>' +
																	'<p>' +
																		'<input type="text" id="scale-width-' + response.id + '" class="scale-width small-text" name="scaleWidth" value="' + response.width + '" />' +
																		'<span> &times; </span>' +
																		'<input type="text" id="scale-height-' + response.id + '" class="scale-height small-text" name="scaleHeight" value="' + response.height + '" />' +
																	'</p>' +
																	'<p>' +
																		'<button id="scale-image-' + response.id + '" class="tool-scale rb-btn light-gray-btn" name="scaleButton">Scale Image</button>' +
																	'</p>' +
																'</div>' +
															'</div>' +
														'</div>' +
													'</div>' +
													'<div class="panel crop-image">' +
														'<div class="panel-column">' +
															'<div class="element">' +
																'<div class="element-top">Crop image</div>' +
																'<div class="element-body">' +
																	'<p>' +
																		'<label>Aspect ratio</label>' +
																		'<input type="text" id="crop-ratio-width-' + response.id + '" class="crop-ratio-width small-text" name="cropRatioWidth" />' +
																		'<span> : </span>' +
																		'<input type="text" id="crop-ratio-height-' + response.id + '" class="crop-ratio-height small-text" name="cropRatioHeight" />' +
																	'</p>' +
																	'<p>' +
																		'<label>Selection</label>' +
																		'<input type="text" id="crop-selection-width-' + response.id + '" class="crop-selection-width small-text" name="cropSelection" />' +
																		'<span> &times; </span>' +
																		'<input type="text" id="crop-selection-height-' + response.id + '" class="crop-selection-height small-text" name="cropSelectionheight" />' +
																	'</p>' +
																'</div>' +
															'</div>' +
														'</div>' +
													'</div>' +
													'<div class="panel thumbnail-preview">' +
														'<div class="panel-column">' +
															'<div class="element">' +
																'<div class="element-top">Thumbnail preview</div>' +
																'<div class="element-body">' +
																	'<img id="edit-image-thumb-' + response.id + '" class="media-image-thumb edit-image-thumb" src="' + HTTP_ADMIN + 'media/' + response.id + '?id=' + response.id + '&adminRequest=thumb-preview&csrfToken=' + csrfToken + '&timestamp=' + new Date().getTime() + '" />' +
																'</div>' +
															'</div>' +
														'</div>' +
													'</div>' +
													'<div class="panel thumbnail-options">' +
														'<div class="panel-column">' +
															'<div class="element">' +
																'<div class="element-top">Apply changes to (on save)</div>' +
																'<div class="element-body">' +
																	'<label for="apply-all-' + response.id + '" class="center-label">' +
																		'<input type="radio" id="apply-all-' + response.id + '" class="center-toggle" checked="checked" name="thumbOpts-' + response.id + '" value="all">' +
																		'<span>All images</span>' +
																	'</label>' +
																	'<label for="apply-all-but-thumb-' + response.id + '" class="center-label">' +
																		'<input type="radio" id="apply-all-but-thumb-' + response.id + '" class="center-toggle" name="thumbOpts-' + response.id + '" value="allNoThumb" />' +
																		'<span>All images except thumbnail</span>' +
																	'</label>' +
																	'<label for="apply-thumb-' + response.id + '" class="center-label">' +
																		'<input type="radio" id="apply-thumb-' + response.id + '" class="center-toggle" name="thumbOpts-' + response.id + '" value="thumbOnly" />' +
																		'<span>Thumbnail only</span>' +
																	'</label>' +
																	'<label for="apply-image-' + response.id + '" class="center-label">' +
																		'<input type="radio" id="apply-image-' + response.id + '" class="center-toggle" name="thumbOpts-' + response.id + '" value="imageOnly" />' +
																		'<span>Image only</span>' +
																	'</label>' +
																'</div>' +
															'</div>' +
														'</div>' +
													'</div>' +
												'</div>' +
											'</td>' +
										'</tr>' +
									'</tbody>' +
								'</table>' +
							'</div>' +
						'</td>' +
					'</tr>' +
					'<tr id="edit-image-' + response.id + '">' +
						'<td>' +
							'<div>' +
								'<button id="edit-btn-' + response.id + '" class="edit-image button-spacing rb-btn blue-btn fltlft">Edit Image</button>' +
								'<img id="media-ajax-' + response.id + '" src="' + HTTP_IMAGE + 'admin/gifs/ajax-loader-snake.gif" alt="loading" class="media-ajax block fltrght" style="display: none;" />' +
							'</div>' +
						'</td>' +
					'</tr>'
				);
				$('#media-title-' + response.id).after(
					'<tr>' +
						'<th>' +
							'<label for="uploads-' + response.id + '-alt">Alternate text</label>' +
						'</th>' +
						'<td>' +
							'<input type="text" id="uploads-' + response.id + '][alt]" class="media-field" name="uploads[' + response.id + '][alt]" />' +
						'</td>' +
					'</tr>'
				);

				$(".save-image").button({ disabled: true });
			}
		});
		uploader.bind('UploadComplete', function(up, file, info) {
			if($('#submitMediaUploads').length === 0) {
				$('#container').append(
					'<div class="form-item">' +
						'<button type="submit" id="submitMediaUploads" class="rb-btn" name="submitMediaUploads">Submit Changes</button>' +
					'</div>'
				);
			}
			
			$('.rb-btn').button();
		});
	}
});
