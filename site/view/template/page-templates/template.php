<?php
namespace RevenantBlue\Site;

require_once(DIR_APPLICATION . 'controller/common/site-c.php');
require_once(DIR_APPLICATION . 'controller/custom/custom-c.php');
require_once(DIR_TEMPLATE . 'ui.php');
$title = isset($pageToLoad) ? hsc($pageToLoad['title']) : '';
require_once(DIR_TEMPLATE . 'head.php');
echo $pageToLoad['head'];
loadSiteCss();
?>
</head>
<body>
	<form id="main-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
		<?php displayHeader(); ?>
		<?php echo $sideNav; ?>
		<section id="main" style="visibility: hidden;">
			<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
			<input type="hidden" id="main-form-action" name="action" />
			<div id="main-outer">
				<div id="main-inner" class="clearfix">
					<div id="menu-collapse-waypoint"></div>
					<section id="main-content">
						<div id="page-wrap">
							<?php echo $pageToLoad['body']; ?>
						</div>
					</section>
				</div>
			</div>
		</section>
	</form>
<?php displayFooter(); ?>
