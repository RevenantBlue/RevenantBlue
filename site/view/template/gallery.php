<?php
namespace RevenantBlue\Site;

require_once DIR_APPLICATION . 'controller/photogallery/photogallery-c.php';
require_once 'ui.php';
$title = 'Gallery | ';
require_once 'head.php';
?>
<?php loadFancyBox(); ?>
<?php loadSiteCss(); ?>
</head>
<body>
	<form id="main-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
		<?php displayHeader(); ?>
		<?php echo $sideNav; ?>
		<section id="main">
			<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
			<input type="hidden" id="main-form-action" name="action" />
			<div id="dialogs-container"></div>
			<div id="main-outer" class="main-box">
				<div id="main-inner" class="clearfix">
					<section id="main-content">
					<?php if(isset($albums)): ?>
						<ul id="albums">
						<?php foreach($albums as $album): ?>
							<li>
								<div class="album-wrap">
									<div class="inner">
										<a href="<?php echo HTTP_SERVER . 'gallery/' . $album['alias']; ?>">
											<img class="album-image" src="<?php echo HTTP_GALLERY . 'thumb-' . hsc($album['image']); ?>" alt="<?php echo hsc($album['image_alt']); ?>" title="<?php echo hsc($album['title']); ?>" />
											<p class="description">
												<?php echo hsc($album['title']); ?>
											</p>
										</a>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
						</ul>
					<?php elseif(isset($album)): ?>
						<ul id="photos">
						<?php foreach($photos as $photo): ?>
							<li>
								<div class="photo-wrap">
									<div class="inner">
										<a id="<?php hsc($photo['image']); ?>" class="photo-box" rel="gallery" href="<?php echo HTTP_GALLERY . hsc($photo['image']); ?>">
											<img class="photo" src="<?php echo HTTP_GALLERY . 'thumb-' . hsc($photo['image']); ?>" alt="<?php echo hsc($photo['image_alt']); ?>" title="<?php echo hsc($photo['title']); ?>" />
										</a>
										<p class="description">
											<?php echo hsc($photo['title']); ?>
										</p>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					</section>
				</div>
			</div>
		</section>
	</form>
<?php displayFooter(); ?>
