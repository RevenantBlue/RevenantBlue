<?php
namespace RevenantBlue;

class ThumbnailGenerator {

	  // *** Class variables
     private $image;
     private $imageOriginal;
	 private $width;
     private $height;
	 public  $editWidth;
	 public  $editHeight;
	 public  $cropFlag;
	 public  $imageResized;
	 public  $editedImage;
	 private $editFlag;
	 private $exifType;
	 private $mimeType;

    public function __construct($fileName)  {
		// Store original file for later use.
		$this->imageOriginal = $fileName;
		// Get mime type
		$this->exifType = exif_imagetype($this->imageOriginal);

		// Open up the file
		$this->image = $this->openImage($fileName);

        // *** Get width and height
        $this->width  = imagesx($this->image);
        $this->height = imagesy($this->image);
     }

	private function openImage($file) {
		 switch($this->exifType) {
			 case IMAGETYPE_JPEG:
				 $img = @imagecreatefromjpeg($file);
				 break;
			 case IMAGETYPE_GIF:
				 $img = @imagecreatefromgif($file);
				 break;
			 case IMAGETYPE_PNG:
				 $img = @imagecreatefrompng($file);
				 break;
			 default:
				 $img = FALSE;
				 break;
		 }
		return $img;
	}

	public function resizeImage($newWidth, $newHeight, $option = "auto") {
		if(isset($this->editedImage)) {
			unset($this->image);
			$this->image = $this->editedImage;
			$this->width = $this->editWidth;
			$this->height = $this->editHeight;
		}

		// *** Get optimal width and height - based on $option
		$optionArray = $this->getDimensions($newWidth, $newHeight, strtolower($option));

		$optimalWidth  = $optionArray['optimalWidth'];
		$optimalHeight = $optionArray['optimalHeight'];

		// *** Resample - create image canvas of x, y size
		$this->editedImage = imagecreatetruecolor($optimalWidth, $optimalHeight);
		// Save the alpha channel if png or gif.
		$this->saveTransparency();

		imagecopyresampled($this->editedImage, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);

		// *** if option is 'crop', then crop too
		if ($option == 'crop') {
			$this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
		}

		$this->editFlag = TRUE;
		$this->editWidth = imagesx($this->editedImage);
		$this->editHeight = imagesy($this->editedImage);
	}

	public function targetCrop($coords) {
		// If cropping original image else crop the cropped image.
		$crop = empty($this->editFlag) ? $this->image : $this->editedImage;

		$this->editedImage = imagecreatetruecolor($coords->w, $coords->h);
		$this->saveTransparency();
		imagecopyresampled($this->editedImage, $crop, 0, 0, $coords->x, $coords->y, $coords->w, $coords->h, $coords->w, $coords->h);
		imagedestroy($crop);
		$this->editWidth = imagesx($this->editedImage);
		$this->editHeight = imagesy($this->editedImage);

		// Let the generator know that at target crop has been performed in case of subsequent croppings to the already cropped image. /sigh this has been a huge pain in the ass.
		$this->editFlag = TRUE;
	}

	public function flipImage($mode) {
		// Set the main parameters depending on whether or not an edited image exists.
		$width = empty($this->editFlag) ? $this->width : $this->editWidth;
		$height = empty($this->editFlag) ? $this->height : $this->editHeight;
		$imageToFlip = empty($this->editFlag) ? $this->image : $this->editedImage;

		$src_x = 0;
		$src_y = 0;
		$srcWidth = $width;
		$srcHeight = $height;

		switch($mode) {
			case 1: //vertical
				$src_y = $height -1;
				$srcHeight = -$height;
				break;
			case 2: //horizontal
				$src_x = $width -1;
				$srcWidth = -$width;
				break;
			case 3: //both
				$src_x = $width -1;
				$src_y = $height -1;
				$srcWidth = -$width;
				$srcHeight = -$height;
				break;
			default:
				return FALSE;
				break;
		}
		$this->editedImage = imagecreatetruecolor($width, $height);
		$this->saveTransparency();
		// Flip it
		imagecopyresampled($this->editedImage, $imageToFlip, 0, 0, $src_x, $src_y, $width, $height, $srcWidth, $srcHeight);
		// Update the edited width and height.
		$this->editWidth = imagesx($this->editedImage);
		$this->editHeight = imagesy($this->editedImage);
		// Set the edit flag
		$this->editFlag = TRUE;
	}

	public function rotateImage($angle) {
		// Set the main parameters depending on whether or not an edited image exists.
		$imageToRotate = empty($this->editFlag) ? $this->image : $this->editedImage;
		
		$imageExif = exif_imagetype($this->imageOriginal);
		switch($angle) {
			case 90:
				switch($imageExif) {
					case IMAGETYPE_JPEG:
						$this->editedImage = imagerotate($imageToRotate, 270, 0);
						break;
					case IMAGETYPE_GIF: case IMAGETYPE_PNG:
						$transparency = imagecolorallocatealpha($imageToRotate, 0, 0, 0, 127);
						$this->editedImage = imagerotate($imageToRotate, 270, 1);
						$this->saveTransparency();
						break;
					default:
						break;
				}
				break;
			case -90:
				switch($imageExif) {
					case IMAGETYPE_JPEG:
						$this->editedImage = imagerotate($imageToRotate, 90, 0);
						break;
					case IMAGETYPE_GIF: case IMAGETYPE_PNG:
						$transparency = imagecolorallocatealpha($imageToRotate, 0, 0, 0, 127);
						$this->editedImage = imagerotate($imageToRotate, 90, 1);
						$this->saveTransparency();
						break;
					default:
						break;
				}
				break;
			default:
				break;
		}
		$this->editWidth = imagesx($this->editedImage);
		$this->editHeight = imagesy($this->editedImage);
		$this->editFlag = TRUE;
	}

	private function getDimensions($newWidth, $newHeight, $option) {
	   switch ($option) {
			case 'exact':
				$optimalWidth = $newWidth;
				$optimalHeight = $newHeight;
				break;
			case 'portrait':
				$optimalWidth = $this->getSizeByFixedHeight($newHeight);
				$optimalHeight= $newHeight;
				break;
			case 'landscape':
				$optimalWidth = $newWidth;
				$optimalHeight= $this->getSizeByFixedWidth($newWidth);
				break;
			case 'auto':
				$optionArray = $this->getSizeByAuto($newWidth, $newHeight);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;
			case 'crop':
				$optionArray = $this->getOptimalCrop($newWidth, $newHeight);
				$optimalWidth = $optionArray['optimalWidth'];
				$optimalHeight = $optionArray['optimalHeight'];
				break;
		}
		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}

	private function getSizeByFixedHeight($newHeight) {
		// Get the new width by multiplying the new height by the original width/height ratio.
		$ratio = $this->width / $this->height;
		$newWidth = $newHeight * $ratio;
		return $newWidth;
	}

	private function getSizeByFixedWidth($newWidth) {
		$ratio = $this->height / $this->width;
		$newHeight = $newWidth * $ratio;
		return $newHeight;
	}

	private function getSizeByAuto($newWidth, $newHeight) {
		if ($this->height < $this->width) {
		// *** Image to be resized is wider (landscape)
			$optimalWidth = $newWidth;
			$optimalHeight = $this->getSizeByFixedWidth($newWidth);
		} elseif ($this->height > $this->width) {
		// *** Image to be resized is taller (portrait)
			$optimalWidth = $this->getSizeByFixedHeight($newHeight);
			$optimalHeight = $newHeight;
		} else {
		// *** Image to be resizerd is a square
			if ($newHeight < $newWidth) {
				$optimalWidth = $newWidth;
				$optimalHeight = $this->getSizeByFixedWidth($newWidth);
			} else if ($newHeight > $newWidth) {
				$optimalWidth = $this->getSizeByFixedHeight($newHeight);
				$optimalHeight = $newHeight;
			} else {
				// *** Sqaure being resized to a square
				$optimalWidth = $newWidth;
				$optimalHeight = $newHeight;
			}
		}
		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}

	private function getOptimalCrop($newWidth, $newHeight) {

		$heightRatio = $this->height / $newHeight;
		$widthRatio  = $this->width /  $newWidth;

		if ($heightRatio < $widthRatio) {
			$optimalRatio = $heightRatio;
		} else {
			$optimalRatio = $widthRatio;
		}

		$optimalHeight = $this->height / $optimalRatio;
		$optimalWidth  = $this->width  / $optimalRatio;

		return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
	}

	private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight) {
		// Find center - this will be used for the crop
		$cropStartX = ($optimalWidth / 2) - ($newWidth / 2);
		$cropStartY = ($optimalHeight / 2) - ($newHeight / 2);

		$crop = $this->editedImage;

		// Now crop from center to exact requested size
		$this->editedImage = imagecreatetruecolor($newWidth , $newHeight);
		// Save the alpha channel if png or gif.
		$this->saveTransparency();
		imagecopyresampled($this->editedImage, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
	}

	private function saveTransparency() {
		// If a png or gif save the alpha transparency.
		if($this->exifType === IMAGETYPE_PNG || $this->exifType === IMAGETYPE_GIF) {
			if(isset($this->editedImage) && is_resource($this->editedImage)) {
				imagealphablending($this->editedImage, FALSE);
				imagesavealpha($this->editedImage, TRUE);
			} elseif(isset($this->image) && is_resource($this->image)) {
				imagealphablending($this->image, FALSE);
				imagesavealpha($this->image, TRUE);
				$this->editedImage = $this->image;
			}
		}
	}

	public function ajaxImage() {
		// Output the image to the browser using headers, capture the output stream with the output buffer for further image manipulation.
		ob_start();
		switch(exif_imagetype($this->imageOriginal)) {
			case IMAGETYPE_JPEG:
				 header('Content-Type: image/jpeg');
				 $ajaxImage = imagejpeg($this->editedImage);
				 break;
			case IMAGETYPE_GIF:
				header('Content-Type: image/gif');
				$ajaxImage = imagegif($this->editedImage);
				break;
			case IMAGETYPE_PNG:
				header('Content-Type: image/png');
				$ajaxImage = imagepng($this->editedImage);
				break;
			default:
				break;
		}
		if(is_resource($this->editedImage)) {
			imagedestroy($this->editedImage);
			unset($this->editedImage);
		}
		if(isset($this->editFlag) && empty($this->editedImage)) {
			$this->editedImage = imagecreatefromstring(ob_get_contents());
			ob_end_flush();
		}
		return $ajaxImage;
	}

	public function saveImage($savePath, $imageQuality = 100, $dontDestroy = FALSE) {
		// Save the resized file to the savePath depending on the image type of the original file.
		if(!empty($this->exifType)) {
			// If the image was large enough to use exif_imagetype
			switch($this->exifType) {
				case IMAGETYPE_JPEG:
					 $saveSuccess = imagejpeg($this->editedImage, $savePath, $imageQuality);
					 break;
				case IMAGETYPE_GIF:
					$saveSuccess = imagegif($this->editedImage, $savePath);
					break;
				case IMAGETYPE_PNG:
					// Scale quality from 0-100 to 0-9
					$scaleQuality = round(($imageQuality / 100) * 9);
					// Invert quality setting as 0 is best, not 9
					$invertScaleQuality = 9 - $scaleQuality;
					// Save the image.
					$saveSuccess = imagepng($this->editedImage, $savePath, $invertScaleQuality);
					break;
				default:
					$saveSuccess = FALSE;
					break;
			}
		} else {
			// If the image is too small for exif_imagetype, use the extension.
			switch($this->mimeType) {
				case 'jpeg': case 'jpg':
					 $saveSuccess = imagejpeg($this->editedImage, $savePath, $imageQuality);
					 break;
				case 'gif':
					$saveSuccess = imagegif($this->editedImage, $savePath);
					break;
				case 'png':
					// Scale quality from 0-100 to 0-9
					$scaleQuality = round(($imageQuality / 100) * 9);
					// Invert quality setting as 0 is best, not 9
					$invertScaleQuality = 9 - $scaleQuality;
					// Save the image.
					$saveSuccess = imagepng($this->editedImage, $savePath, $invertScaleQuality);
					break;
				default:
					$saveSuccess = FALSE;
					break;
			}
		}
		if(is_resource($this->editedImage) && is_resource($this->image) && $dontDestroy === FALSE) {
			imagedestroy($this->editedImage);
			imagedestroy($this->image);
		}
		return $saveSuccess;
	}

	public function destroyResources() {
		if(is_resource($this->editedImage)) imagedestroy($this->editedImage);
		if(is_resource($this->image)) imagedestroy($this->image);
	}
}
