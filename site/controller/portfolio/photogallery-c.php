<?php
namespace RevenantBlue\Site;
use RevenantBlue;
use RevenantBlue\Pager;

require_once(DIR_APPLICATION . 'model/photogallery/photogallery-main.php');
require_once(DIR_SYSTEM . 'library/paginate.php');

// Load the photo gallery model
$gallery = new PhotoGallery;

// Set the default gallery mode to the classic template
$galleryStyle = 'advanced';

// Load the pagination class
$pager = new Pager;
$pager->totalRecords = $gallery->countAlbums();
$pager->limit = 16;
$pager->delta = 5;
$pager->alwaysShowPrev = TRUE;
$pager->alwaysShowNext = TRUE;
$pager->firstAndLast = FALSE;
$pager->previousName = "PREVIOUS SCREEN";
$pager->nextName = "NEXT SCREEN";
$pager->paginate();
$pager->menu = str_replace('index.php?page=', 'portfolio/p', $pager->menu);
$pager->menu = str_replace('&amp;controller=portfolio', '', $pager->menu);

// Load the main category results from the database.
$albums = $gallery->loadAlbums($pager->limit, $pager->offset, 'order_of_item', 'asc');
$numOfAlbums = count($albums);
if(isset($numOfAlbums) && $numOfAlbums < 16) $numOfAlbums = (16 - $numOfAlbums) - 1;
if(isset($numOfAlbums) && $numOfAlbums > 16) $numOfAlbums = ($numOfAlbums - 16) - 1;
if(isset($numOfAlbums)) $placeholders = ceil($numOfAlbums / 4);

// Process GET request when user click on a category to view its albums (subcategories)
if($_SERVER['REQUEST_METHOD'] == 'GET') {
	
	// Load images into the gallery module
	if(isset($_GET['album'])) {
		$album = $gallery->getGalleryItemByAlias($_GET['album']);
		// If the id exists load the images placed under that album.
		if(!empty($album)) {
			$photos = $gallery->loadPhotos($album['id']);
			$gallery->setHit($album['id']);		
		// Else redirect the user back to the main portfolio page.
		} else {
			header('Location: ' . HTTP_SERVER . 'portfolio', true, 302);
			exit;	
		}
	}
}
