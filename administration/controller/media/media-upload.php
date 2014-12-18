<?php
namespace RevenantBlue\Admin;
use RevenantBlue\ThumbnailGenerator;
use \stdClass;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
/**
 * upload.php
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 */

// HTTP headers for no cache etc
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// If organizing media into month/year files
if($globalSettings['organize_media_folders']['value']) $currentDate = getdate();

// Get upload folder
$uploadFolder = $globalSettings['media_upload_url']['value'];

// Settings
// If an upload url has been specified in the global settings use it, else use the temporary directory.
if(!empty($uploadFolder) && is_dir(DIR_SITE_ROOT . $uploadFolder)) {
	if(isset($currentDate)) {
		if(!is_dir(DIR_SITE_ROOT . $uploadFolder . '/' . $currentDate['year'] . '/' . $currentDate['mon'])) {
			@mkdir(DIR_SITE_ROOT . $uploadFolder . '/' . $currentDate['year'] . '/' . $currentDate['mon'], 0755, TRUE);
		}
		$targetDir = DIR_SITE_ROOT . $uploadFolder . '/' . $currentDate['year'] . '/' . $currentDate['mon'];
		$relativePath = $uploadFolder . '/' . $currentDate['year'] . '/' . $currentDate['mon'];
	} else {
		if(!is_dir(DIR_SITE_ROOT . $uploadFolder)) {
			@mkdir(DIR_SITE_ROOT . $uploadFolder, 0755, TRUE);
			if(!is_dir(DIR_SITE_ROOT . $uploadFolder)) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 32400 "message": "The upload directory could not be created. Ensure that you have write permission on your server."}, "id" : "id"}');
			}
		}
		$targetDir = DIR_SITE_ROOT . $uploadFolder;
		$relativePath = $uploadFolder;
	}
} else {
	$targetDir = DIR_MEDIA;
	if(!is_dir($targetDir)) @mkdir($targetDir, 0755);
	if(isset($currentDate)) {
		$targetDir = $targetDir . $currentDate['year'] . '/' . $currentDate['mon'];
		if(!is_dir($targetDir)) @mkdir($targetDir, 0755, TRUE);
		$relativePath = 'media' . '/' . $currentDate['year'] . '/' . $currentDate['mon'];
	}
}

$cleanupTargetDir = true; // Remove old files
$maxFileAge = 5 * 3600; // Temp file age in seconds

// 5 minutes execution time
@set_time_limit(5 * 60);

// Uncomment this one to fake upload time
// usleep(5000);

// Get the file name
if (isset($_REQUEST["name"])) {
	$fileName = $_REQUEST["name"];
} elseif (!empty($_FILES)) {
	$fileName = $_FILES["file"]["name"];
} else {
	$fileName = uniqid("file-");
}

// Clean the fileName for security reasons
$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

// Chunking might be enabled
$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

// Make sure the fileName is unique but only if chunking is disabled
if ($chunks < 2 && file_exists($targetDir . '/' . $fileName)) {
	$ext = strrpos($fileName, '.');
	$fileName_a = substr($fileName, 0, $ext);
	$fileName_b = substr($fileName, $ext);

	$count = 1;
	while (file_exists($targetDir . '/' . $fileName_a . '_' . $count . $fileName_b))
		$count++;

	$fileName = $fileName_a . '_' . $count . $fileName_b;
}

$filePath = $targetDir . '/' . $fileName;
$relativeUrl = $relativePath . '/' . $fileName;

// Create target dir
if (!file_exists($targetDir))
	@mkdir($targetDir);

//$targetDir = 'uploads';
$cleanupTargetDir = true; // Remove old files
$maxFileAge = 5 * 3600; // Temp file age in seconds


// Create target dir
if (!file_exists($targetDir)) {
	@mkdir($targetDir);
}

// Remove old temp files
if ($cleanupTargetDir) {
	if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
	}

	while (($file = readdir($dir)) !== false) {
		$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

		// If temp file is current file proceed to the next
		if ($tmpfilePath == "{$filePath}.part") {
			continue;
		}

		// Remove temp file if it is older than the max age and is not the current file
		if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
			@unlink($tmpfilePath);
		}
	}
	closedir($dir);
}	


// Open temp file
if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
	die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
}

if (!empty($_FILES)) {
	if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
	}

	// Read binary input stream and append it to temp file
	if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
	}
} else {
	if (!$in = @fopen("php://input", "rb")) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
	}
}

while ($buff = fread($in, 4096)) {
	fwrite($out, $buff);
}

@fclose($out);
@fclose($in);

// Check if file has been uploaded
if (!$chunks || $chunk == $chunks - 1) {
	// Strip the temp .part suffix off 
	rename("{$filePath}.part", $filePath);
}

// Update the database with the media file
if(file_exists($filePath)) {
	$fileInfo = pathinfo($filePath);
	require_once DIR_ADMIN . 'model/media/media-main.php';
	require_once DIR_ADMIN . 'controller/common/global-validation.php';
	require_once DIR_SYSTEM . 'library/thumbnail-generator.php';
	$globalValidation = new GlobalValidation;
	$mediaName = $fileName;
	$mediaAlias = $globalValidation->validateAlias($fileName, FALSE);
	$mediaAuthor = $_SESSION['userId'];
	$mediaTitle = $fileInfo['filename'];
	$mediaBackupPath = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '-bak.' . $fileInfo['extension'];

	// Define image mime types
	$imageMimes = array('image/jpeg', 'image/png', 'image/gif');

	// Get the mime type for the
	$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
	$mediaMimeType = finfo_file($finfo, $filePath);
	finfo_close($finfo);
	
	$mediaMeta = '';

	// If the file is an image
	if(in_array($mediaMimeType, $imageMimes)) {
		$mediaImageType = exif_imagetype($filePath);
		// Get image exif data
		if($mediaImageType === IMAGETYPE_JPEG) {
			$exifData = exif_read_data($filePath);
			// Get meta data for images.
			$mediaMeta['model'] = isset($exifData['Model']) ? $exifData['Model'] : '';
			$mediaMeta['aperature'] = isset($exifData['AperatureValue']) ? $exifData['AperatureValue'] : '';
			$mediaMeta['focal_length'] = isset($exifData['FocalLength']) ? $exifData['FocalLength'] : '';
			$mediaMeta['shutter_speed'] = isset($exifData['ShutterSpeedValue']) ? $exifData['ShutterSpeedValue'] : '';
			$mediaMeta['owner'] = isset($exifData['OwnerName']) ? $exifData['OwnerName'] : '';
			$mediaMeta['created_timestamp'] = isset($exifData['FileDateTime']) ? $exifData['FileDateTime'] : '';
			$mediaMeta = serialize($mediaMeta);
		}
		// Get dimensions for image
		if(!empty($mediaImageType)) {
			// Get dimensions
			$imageDimensions = getimagesize($filePath);
			$imageWidth = $imageDimensions[0];
			$imageHeight = $imageDimensions[1];
			$imageSize = $imageWidth . ' x ' . $imageHeight;
		}
	}
	// Create backup for the future
	copy($filePath, $mediaBackupPath);
	$mediaArr = array(
		'mediaName'       => $mediaName,
		'mediaAlias'      => $mediaAlias,
		'mediaAuthor'     => $mediaAuthor,
		'mediaTitle'      => $mediaTitle,
		'mediaMimeType'   => $mediaMimeType,
		'mediaMeta'       => $mediaMeta,
		'mediaUrl'        => HTTP_SERVER . $relativeUrl,
		'mediaPath'       => $filePath,
		'mediaOrigPath'   => $mediaBackupPath,
		'mediaExt'        => $fileInfo['extension']
	);
	$mediaArr['mediaOrigWidth'] = isset($imageWidth) ?  $imageWidth : '';
	$mediaArr['mediaOrigHeight'] = isset($imageHeight) ? $imageHeight : '';
	$mediaArr['mediaWidth'] = isset($imageWidth) ?  $imageWidth : '';
	$mediaArr['mediaHeight'] = isset($imageHeight) ? $imageHeight : '';
	$media = new Media;
	$mediaId = $media->insertMedia($mediaArr);
	// Insert thumbnails for media images.
	if(!empty($mediaId) && !empty($mediaImageType)) {
		$imageTemplates = $config->loadImageTemplates();
		// Create thumbnail.
		foreach($imageTemplates as $imageTemplate) {
			$thumbUrl = $targetDir . '/' . $fileInfo['filename'] .  '-' . $imageTemplate['template_width'] . 'X' . $imageTemplate['template_height'] . '.' . $fileInfo['extension'];
			$relativeThumbUrl = HTTP_SERVER . $relativePath . '/' . $fileInfo['filename'] .  '-' . $imageTemplate['template_width'] . 'X' . $imageTemplate['template_height'] . '.' . $fileInfo['extension'];
			// Instantiate the thumbnail generator.
			$thumbnailGenerator = new ThumbnailGenerator($filePath);
			// Resize and save thumbnail from source image.
			if($imageWidth < $imageTemplate['template_width'] && $imageHeight < $imageTemplate['template_height']) {
				$thumbnailGenerator->resizeImage($imageWidth, $imageHeight, $imageTemplate['template_type']);
			} else {
				$thumbnailGenerator->resizeImage($imageTemplate['template_width'], $imageTemplate['template_height'], $imageTemplate['template_type']);
			}
			$thumbnailGenerator->saveImage($thumbUrl, $imageTemplate['template_quality']);
			// Insert into database.
			$media->insertMediaThumb($mediaId, $imageTemplate['id'], $imageTemplate['template_width'], $imageTemplate['template_height'], $relativeThumbUrl, $thumbUrl);
			// Check for default thumbnail
			if($globalSettings['media_thumb_template']['value'] === $imageTemplate['id']) {
				$mediaThumb = $relativeThumbUrl;
				$media->setThumbUrl($mediaId, $relativeThumbUrl);
			}
		}
	}
	$adminRequestObj = new stdClass;
	$adminRequestObj->id = $mediaId;
	$adminRequestObj->mediaTitle = $mediaArr['mediaTitle'];
	$adminRequestObj->mediaUrl = $mediaArr['mediaUrl'];
	$adminRequestObj->mediaThumb = isset($mediaThumb) ? $mediaThumb : '';
	$adminRequestObj->mediaType = $mediaArr['mediaMimeType'];
	$adminRequestObj->uploadDate = date('M d, Y', time());
	$adminRequestObj->imageSize = isset($imageSize) ? $imageSize : '';
	$adminRequestObj->width = isset($imageWidth) ? $imageWidth : '';
	$adminRequestObj->height = isset($imageHeight) ? $imageHeight : '';
	// Return JSON resposne
	echo json_encode($adminRequestObj);
	exit;
}
