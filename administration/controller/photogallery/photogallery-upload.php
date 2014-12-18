<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'controller/photogallery/photogallery-validation.php';
require_once DIR_ADMIN . 'controller/common/global-validation.php';
require_once DIR_ADMIN . 'model/photogallery/photogallery-main.php';
require_once DIR_SYSTEM . 'library/thumbnail-generator.php';
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

// Load photogallery model.
$photogallery = new PhotoGallery;
// Load the global validation class.
$globalValidation = new GlobalValidation;

if(isset($_GET['album'])) {
	$album = $_GET['album'];
	$albumId = $photogallery->getIdByAlias($album);
	if(empty($albumId)) {
		die('{"jsonrpc" : "2.0", "error" : {"code": 901, "message": "The album selected does not exist, please create a new album and try again."}, "id" : "id"}');	
	}
}
$uploadDirectory = DIR_IMAGE . 'photogallery/' . $album . '/';

// Settings
// If an upload url has been specified in the global settings use it, else use the temporary directory.
if(!is_dir($uploadDirectory)) {
	die('{"jsonrpc" : "2.0", "error" : {"code": 901, "message": "The album selected does not exist, please create a new album and try again."}, "id" : "id"}');
}
$targetDir = $uploadDirectory;
$relativePath = 'images/photogallery/' . $album;

$cleanupTargetDir = true; // Remove old files
$maxFileAge = 5 * 3600; // Temp file age in seconds

// 5 minutes execution time
@set_time_limit(5 * 60);

// Uncomment this one to fake upload time
//usleep(5000);

// Get parameters
$chunks = 0; $chunk = 0; $fileName = 0;
if(isset($_POST['chunk'])) $chunk = intval($_POST['chunk']);
if(isset($_POST['chunks'])) $chunks = intval($_POST['chunks']);
if(isset($_POST['name'])) $fileName = $_POST['name'];
if(isset($_GET['chunk'])) $chunk = intval($_GET['chunk']);
if(isset($_GET['chunks'])) $chunks = intval($_GET['chunks']);
if(isset($_GET['name'])) $fileName = $_GET['name'];

// Check extension
$allowedExtensions = array('jpg', 'jpeg', 'gif', 'png');	
$fileInfo = pathinfo($fileName);
// Get the file extension to append back onto the filename after sanitization.
$fileExt = $fileInfo['extension'];
// Sanitize the filename and add the extension back on since the validateAlias function will remove it by default.
$fileName = $globalValidation->validateAlias($fileName, FALSE) . '.' . $fileExt;


if(!in_array(strtolower($fileInfo['extension']), $allowedExtensions)) {
	// If the extension isn't an image file.
	die('{"jsonrpc" : "2.0", "error" : {"code": 900, "message": "Invalid extension, valid extensions are jpg, gif, png."}, "id" : "id"}');
}

// Clean the fileName for security reasons
$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

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

// Remove old temp files	
if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
	while (($file = readdir($dir)) !== false) {
		$tmpfilePath = $targetDir . '/' . $file;

		// Remove temp file if it is older than the max age and is not the current file
		if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
			@unlink($tmpfilePath);
		}
	}

	closedir($dir);
} else
	die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
	

// Look for the content type header
if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
	$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

if (isset($_SERVER["CONTENT_TYPE"]))
	$contentType = $_SERVER["CONTENT_TYPE"];

// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
if (strpos($contentType, "multipart") !== false) {
	if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
		// Open temp file
		$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
		if ($out) {
			// Read binary input stream and append it to temp file
			$in = fopen($_FILES['file']['tmp_name'], "rb");

			if ($in) {
				while ($buff = fread($in, 4096))
					fwrite($out, $buff);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			fclose($in);
			fclose($out);
			@unlink($_FILES['file']['tmp_name']);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
	} else
		die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
} else {
	// Open temp file
	$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
	if ($out) {
		// Read binary input stream and append it to temp file
		$in = fopen("php://input", "rb");

		if ($in) {
			while ($buff = fread($in, 4096))
				fwrite($out, $buff);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

		fclose($in);
		fclose($out);
	} else
		die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
}

// Check if file has been uploaded
if (!$chunks || $chunk == $chunks - 1) {
	// Strip the temp .part suffix off 
	rename("{$filePath}.part", $filePath);
}

// Update the database with the media file
if(file_exists($filePath)) {
	$fileInfo = pathinfo($filePath);
	$extension = strtolower($fileInfo['extension']);
	require_once DIR_ADMIN . 'controller/photogallery/photogallery-validation.php';
	require_once DIR_ADMIN . 'model/photogallery/photogallery-main.php';
	require_once DIR_ADMIN . 'controller/common/global-validation.php';
	require_once DIR_SYSTEM . 'library/thumbnail-generator.php';
	$galleryValidation = new GalleryValidation;
	$imageName = $fileName;
	$imageAlias = $galleryValidation->validateAlias($fileName, TRUE);
	$imageAuthor = $_SESSION['userId'];
	$imageTitle = $fileInfo['filename'];
	$imageSize = filesize($filePath);
	$imageDimensions = getimagesize($filePath);
	// Get the mime type for the
	$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
	$imageMimeType = finfo_file($finfo, $filePath);
	finfo_close($finfo);
	
	$imageMeta = ''; 
	$imageType = exif_imagetype($filePath);
	$imageUrl = HTTP_GALLERY . $album . '/' . $imageAlias . '.' . $extension;
	// Get image exif data
	if($imageType === IMAGETYPE_JPEG) {
		$exifData = exif_read_data($filePath);
		// Get meta data for images.
		$imageMeta['model'] = isset($exifData['Model']) ? $exifData['Model'] : '';
		$imageMeta['aperature'] = isset($exifData['AperatureValue']) ? $exifData['AperatureValue'] : ''; 
		$imageMeta['focal_length'] = isset($exifData['FocalLength']) ? $exifData['FocalLength'] : '';
		$imageMeta['shutter_speed'] = isset($exifData['ShutterSpeedValue']) ? $exifData['ShutterSpeedValue'] : '';
		$imageMeta['owner'] = isset($exifData['OwnerName']) ? $exifData['OwnerName'] : '';
		$imageMeta['created_timestamp'] = isset($exifData['FileDateTime']) ? $exifData['FileDateTime'] : '';
		$imageMeta = serialize($imageMeta);
	}
	$globalValidate = new GlobalValidation;
	$galleryValidate = new GalleryValidation;
	$globalValidate->validateCreateDate();
	$galleryValidate->validateOrder($albumId);
	$imageArr = array(
		'title'        => '',
		'alias'        => $imageAlias,
		'parentAlias'  => $album,
		'image'        => $fileName,
		'imageSize'    => $imageSize,
		'state'        => 1,
		'orderOfItem'  => $galleryValidate->orderOfItem,
		'createdBy'    => $_SESSION['userId'],
		'createDate'   => $globalValidate->createDate,
		'imageUrl'     => $imageUrl,
		'imagePath'    => $filePath,
		'mimeType'     => $imageMimeType
	);
	$imageId = $photogallery->insertGalleryNodeQuick($imageArr, $albumId);
	$newPhoto = $photogallery->getPhoto($imageId);
	// Insert thumbnails for media images.
	if(!empty($imageId)) {
		$imageQuality = $globalSettings['media_image_quality']['value'];
		// Get the template for the album.
		$template = $photogallery->getTemplate((int)$albumId);
		// Resize and save the source image to fit the gallery.
		$thumbnailGenerator = new ThumbnailGenerator($filePath);
		$thumbnailGenerator->resizeImage((int)$template['image_width'], (int)$template['image_height'], $template['type']);
		$thumbnailGenerator->saveImage($filePath, $imageQuality);
		// Resize and save thumbnail from source image.
		$thumbnailGenerator = new ThumbnailGenerator($filePath);
		$thumbnailGenerator->resizeImage((int)$template['thumbnail_width'], (int)$template['thumbnail_height'], $template['type']);
		$thumbnailGenerator->saveImage($uploadDirectory . "thumbs/" . $fileName, $imageQuality);
		// Resize and save admin thumbnail from source image.
		$thumbnailGenerator = new ThumbnailGenerator($filePath);
		$thumbnailGenerator->resizeImage(175, 125, 'crop');
		$thumbnailGenerator->saveImage($uploadDirectory . 'thumbs/admin-thumb/' . $fileName, $imageQuality);	
	}
	$adminReq = new stdClass;
	$adminReq->id = $imageId;
	$adminReq->imageTitle = $imageArr['title'];
	$adminReq->imageUrl = $imageUrl;
	$adminReq->imageAlias = $imageAlias;
	$adminReq->fileName = $fileName;
	$adminReq->parentAlias = $album;
	$adminReq->thumbUrl = HTTP_ADMIN . 'photogallery/' . $album . '?id=' . $imageId . '&amp;adminRequest=image-preview&amp;csrfToken=' . $formKey;
	$adminReq->imageType = $imageArr['mimeType'];
	$adminReq->order = $newPhoto['order_of_item'];
	$adminReq->uploadDate = date('M d, Y', time());
	$adminReq->imageSize = isset($imageSize) ? $imageSize : '';
	$adminReq->width = isset($imageWidth) ? $imageWidth : '';
	$adminReq->height = isset($imageHeight) ? $imageHeight : '';
	$adminReq->errors = isset($errors) ? $errors : '';
	// Return JSON resposne
	echo json_encode($adminReq);
	exit;
}
