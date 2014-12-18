<?php
require_once(DIR_APPLICATION . 'controller/common/site-c.php');
require_once(DIR_TEMPLATE . 'ui.php');
$title = '404 | ';
require_once(DIR_TEMPLATE . 'head.php');
?>
<?php loadSiteCss(); ?>
</head>
<body>
	<form id="main-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
		<?php displayHeader(); ?>
		<section id="main">
			<div id="dialogs-container"></div>
			<div id="main-outer" class="main-box">
				<div id="four-oh-four">
					<h1>404 Error - The page you have requested does not exist.</h1>
				</div>
			</div>
		</section>    
    </form>
<?php displayFooter(); ?>
