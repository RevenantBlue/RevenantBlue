<?php
namespace RevenantBlue\Admin;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<title><?php echo hsc($title); ?> | Revenant Blue</title>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_ADMIN_DIR; ?>view/css/admin.css" />
<link rel="icon" href="<?php echo HTTP_IMAGE; ?>icons/favicons/revblue-favicon.ico" type="image/x-icon" />
<link href='//fonts.googleapis.com/css?family=Roboto:400,100,100italic,300,300italic,400italic,500,500italic,700,700italic,900,900italic|Roboto+Condensed:400,300,300italic,400italic,700,700italic' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Ubuntu+Mono:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/bower-components/jquery/dist/jquery.min.js"></script>
<script type="text/javascript">
var HTTP_ADMIN = "<?php echo HTTP_ADMIN; ?>"
  , HTTP_ADMIN_DIR = "<?php echo HTTP_ADMIN_DIR; ?>"
  , HTTP_SERVER = "<?php echo HTTP_SERVER; ?>"
  , HTTP_GALLERY = "<?php echo HTTP_GALLERY; ?>"
  , HTTP_IMAGE = "<?php echo HTTP_IMAGE; ?>"
  , HTTP_GALLERY = "<?php echo HTTP_GALLERY; ?>"
  , username = "<?php if(isset($_SESSION['username'])) echo hsc($_SESSION['username']); ?>"
  , rbTitle = "<?php if(isset($title)) echo hsc($title); ?> | Revenant Blue";
</script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/purl/purl.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/hoverintent/hoverintent.js"></script>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/jquery-plugins.js"></script>
