<?php
namespace RevenantBlue;

// Front Controller
if(isset($_GET['controller'])) {
	$pageToDisplay = $_GET['controller'];
} else {
	$pageToDisplay = 'index';
}

$adminPrefix = substr($pageToDisplay, 0, 6);

if($adminPrefix === 'admin-' || $pageToDisplay === 'media-upload') {
	require_once 'administration/index.php';
} else {
	require_once 'site/index.php';
}
