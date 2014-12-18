<?php
namespace RevenantBlue\Site;
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta content="<?php echo hsc($globalSettings['site_description']['value']); ?>" name="description" />
		<title><?php echo hsc($title) . hsc($globalSettings['site_title']['value']); ?></title>
		<meta content="Revenant Blue" name="generator" />
		<script src="<?php echo HTTP_SERVER; ?>site/view/js/bower-components/jquery/dist/jquery.min.js" type="text/javascript"></script>
		<script type="text/javascript">
			var HTTP_SERVER = "<?php echo HTTP_SERVER; ?>"
			  , NODE_SERVER = "<?php echo NODE_SERVER; ?>"
			  , NODE = "<?php echo NODE; ?>"
			  , HTTP_FORUM = "<?php echo HTTP_FORUM; ?>"
			  , HTTP_AVATARS = "<?php echo HTTP_AVATARS; ?>"
			  , HTTP_CPANEL = "<?php echo HTTP_CPANEL; ?>"
			  , HTTP_GALLERY = "<?php echo HTTP_GALLERY; ?>"
			  , HTTP_IMAGE = "<?php echo HTTP_IMAGE; ?>";
		</script>
		<?php if(isset($_SESSION['userId'])): ?>
		<script type="text/javascript">
			var username = "<?php echo hsc($_SESSION['username']); ?>"
			  , userId = "<?php echo hsc($_SESSION['userId']); ?>";
		</script>
		<?php endif; ?>
		<?php loadJqueryPlugins(); ?>
		<?php loadJqueryUi(); ?>
		<?php loadJqueryValidation(); ?>
		<?php loadJqueryWaypoints(TRUE); ?>
		<?php loadPurl(); ?>
		<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
		<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/main.js"></script>
		<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/custom.js"></script>
		<?php else: ?>
		<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/main.min.js"></script>
		<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/custom.min.js"></script>
		<?php loadGoogleAnalytics(); ?>
		<?php endif; ?>
		<link rel="icon" href="<?php echo HTTP_IMAGE; ?>icons/favicons/favicon.ico" />
		<link href='http://fonts.googleapis.com/css?family=Roboto:400,100,100italic,300,300italic,400italic,500,500italic,700,700italic,900,900italic|Roboto+Condensed:400,300,300italic,400italic,700,700italic' rel='stylesheet' type='text/css'>

