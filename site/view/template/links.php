<?php
namespace RevenantBlue\Site;

require_once DIR_APPLICATION . 'controller/links/links-c.php';
require_once DIR_APPLICATION . 'controller/custom/custom-c.php';
require_once 'ui.php';
$title = 'Links | ';
require_once 'head.php';
loadJqueryValidation();
?>
<?php loadSiteCss(); ?>
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
					<?php if(!empty($linksList)): ?>
						<div id="link-categories">
						<?php foreach($linksList as $catName => $linkSet): ?>
							<?php if(!empty($linkSet)): ?>
							<div class="link-category-wrap">
								<h3 class="link-category">
									<?php echo hsc($catName); ?>
								</h3>
								<div class="link-set clearfix">
									<?php foreach($linkSet as $link): ?>
									<div class="link">
										<div class="inner">
											<a href="<?php echo hsc($link['link_url']); ?>" target="<?php echo hsc($link['link_target']); ?>">
												<?php echo hsc($link['link_name']); ?>
											</a>
										</div>
									</div>
									<?php endforeach; ?>
								</div>
							</div>
							<?php endif; ?>
						<?php endforeach; ?>
						</div>
					<?php endif; ?>
					</section>
				</div>
			</div>
		</section>
	</form>
<?php siteCleanup(); ?>
<?php displayFooter(); ?>
