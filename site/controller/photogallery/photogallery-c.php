<?php
namespace RevenantBlue\Site;

require_once(DIR_APPLICATION . 'controller/common/site-c.php');
require_once(DIR_APPLICATION . 'model/photogallery/photogallery-main.php');
require_once(DIR_SYSTEM . 'library/paginate.php');

// Load the photo gallery model
$gallery = new PhotoGallery;

// Process GET request when user click on a category to view its albums (subcategories)
if($_SERVER['REQUEST_METHOD'] == 'GET') {

	if(isset($_GET['controller']) && $_GET['controller'] === 'gallery') {
		// Load images into the gallery module
		if(isset($_GET['album'])) {
			$album = $gallery->getGalleryItemByAlias($_GET['album']);
			// If the id exists load the images placed under that album.
			if(!empty($album)) {
				$photos = $gallery->loadPhotos($album['id']);
				$gallery->setHit($album['id']);		
			// Else redirect the user back to the main portfolio page.
			} else {
				header('Location: ' . HTTP_SERVER . 'gallery', TRUE, 302);
				exit;
			}
		} else {
			// Load the main category results from the database.
			$numOfAlbums = $gallery->countAlbums();
			$albums = $gallery->loadAlbums($numOfAlbums, 0, 'order_of_item', 'asc');
		}
	}
}
