// JavaScript Document

$(function() {
	var uploader = new plupload.Uploader({
		runtimes            : 'html5,gears,flash,silverlight,browserplus,html4',
		container           : 'plupload-container',
		browse_button       : 'plupload-browser-button',
		max_file_size       : '1000mb',
		url                 : HTTP_ADMIN + 'photogallery-upload-controller',
        flash_swf_url       : HTTP_ADMIN_DIR + 'view/js/plupload/js/plupload.flash.swf',
        silverlight_xap_url : HTTP_ADMIN_DIR + 'view/js/plupload/js/plupload.silverlight.xap',
		drop_element        : 'drag-drop-area',
		chunk_size          : '8mb',
		filters             : [
			{
				title : "Allowed Extensions", 
				extensions : "jpg,png,gif"
			}
		],
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
				'<div id="' + file.id + '" class="media-upload">' +
					'<div id="icon-' + file.id + '" class="' + file.extensionIcon + ' media-file-icon media-file-spacing"></div>' +
					'<div class="media-upload-file">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>' +
					'<div id="progressbar-' + file.id + '" class="progressbar"></div>' +
				'</div>' +
				'<table id="media-profile-' + file.id + '" class="media-profile media-details">' +
					'<thead>' +
						'<tr>' +
							'<td id="media-data-' + file.id + '" colspan="2"></td>' +
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
		up.settings.url = HTTP_ADMIN + 'photogallery-upload-controller?csrfToken=' + $("#csrf-token").val() + '&album=' + albumAlias;
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
	});

	uploader.bind('FileUploaded', function(up, file, info) {
		var response, ext = getFileExtension(file.name);
		response = JSON.parse(info.response);
		if(response.image !== false) {
			// Make the extension icon the thumbnail using maxWidth an maxHeight to resize.
			$('#icon-' + file.id).removeClass(file.extensionIcon).removeClass('media-file-icon').append('<img src="' + response.imageUrl +'" class="smallnail" />');
			previewIcon = '';
		}
		$('#' + file.id).addClass('media-upload');
		function addDetails() {
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
			$('#media-body-' + response.id).append(
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
								'<label for="uploads[][title]">Title</label>' +
							'</th>' +
							'<td class="media-inputs">' +
								'<input type="hidden" name="uploads[' + response.id + '][id]" value="' + response.id + '" />' +
								'<input type="text" id="uploads[' + response.id + '][title]" class="media-field" name="uploads[' + response.id + '][title]" value="' + response.imageTitle + '" />' +
							'</td>' +
						'</tr>' +
						'<tr>' +
							'<th>' +
								'<label for="uploads[' + response.id + '][caption]">Caption</label>' +
							'</th>' +
							'<td class="media-inputs">' +
								'<input type="text" id="uploads[' + response.id + '][caption]" class="media-field" name="uploads[' + response.id + '][caption]" />' +
							'</td>' +
						'</tr>' +
						'<tr>' +
							'<th>' +
								'<label for="uploads[' + response.id + '][description]">Description</label>' +
							'</th>' +
							'<td class="media-inputs">' +
								'<textarea id="uploads[' + response.id + '][description]" class="media-field" name="uploads[' + response.id + '][description]"></textarea>' +
							'</td>' +
						'</tr>' +
						'<tr>' +
							'<th>' +
								'<label for="uploads[' + response.id + '][url]">Location</label>' +
							'</th>' +
							'<td class="media-inputs">' +
								'<input type="text" id="uploads[' + response.id + '][url]" class="media-field" name="uploads[' + response.id + '][url]" readonly="readonly" value="' + response.imageUrl + '" />' +
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
			$('#media-attribs-' + response.id).append('<p><strong>Dimensions:</strong>' + response.imageSize + '</p>');
			$('#preview-thumb-container-' + response.id).empty().append('<img id="preview-thumb-' + response.id + '" src="' + response.thumbUrl +'" class="media-image-icon" />');
			$('#media-title-' + response.id).after(
				'<tr>' +
					'<th>' +
						'<label for="uploads[' + response.id + '][alt]">Alternate text</label>' +
					'</th>' +
					'<td>' +
						'<input type="text" id="uploads[' + response.id + '][alt]" class="media-field" name="uploads[' + response.id + '][alt]" />' +
					'</td>' +
				'</tr>'
			);
			$("#album-images-sortable").append(
				'<li id="photoList_' + response.id + '">' +
					'<a href="' + HTTP_GALLERY + response.parentAlias + '/' + response.imageAlias + '">' +
                        '<img alt="" src="' + HTTP_GALLERY + response.parentAlias + '/thumbs/admin-thumb/' + response.fileName + '" class="handle photo_thumbs">' +
                    '</a>' +	
					'<input type="checkbox" onclick="Javascript: CMS.isChecked(this);" value="' + response.id + '" name="imageCheck[]" id="cb' + response.order + '" class="fltlft">' +
					'<span id="state' + response.id + '" class="fltlft icon-20-spacing">' +
						'<span id="state' + response.id + '" class="icon-20-check icon-20-spacing fltlft"> </span>' +
					'</span>' +
					'<span id="featured' + response.id + '" class="fltlft icon-20-spacing">' +
						'<span class="icon-20-gray-disabled icon-20-spacing fltlft"> </span>' +
					'</span>' +
				'</li>'
			);
			
			$(".save-image").button({ disabled: true });
		}
		window.setTimeout(addDetails, 1000);
	});
	uploader.bind('UploadComplete', function(up, file, info) {
		$(":button:contains('Submit Changes')").removeProp("disabled").prop('name', 'submitImageUploads').removeClass("ui-state-disabled");
	});
});
