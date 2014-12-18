<?php
namespace RevenantBlue\Site;

require_once DIR_APPLICATION . 'controller/custom/custom-c.php';
require_once 'ui.php';
$title = 'Contact | ';
require_once 'head.php';
loadJqueryValidation();
?>
<script type="text/javascript">
$(document).ready(function() {
	$("#nav-contact > a").addClass("selected");
});
</script>
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
						<div id="contact-form">
							<h2 id="contact-content-header" class="clean-header">contact</h2>
							<input type="hidden" id="csrf-token" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
							<label for="name" class="form-label block">NAME</label>
							<input type="text" name="name" id="name" class="large-text" />
							<label for="email" class="form-label block">EMAIL</label>
							<input type="text" name="email" id="email" class="large-text" />
							<label for="message" class="form-label block">MESSAGE</label>
							<textarea id="message" name="message"></textarea>
							<div class="vert-space">
								<button id="send-contact" class="rb-btn gray-btn" name="sendContact">Send</button>
							</div>
						</div>
						<div id="contact-form-sent" style="display: none;">
							Your message was sent successfully!
						</div>
					</section>
				</div>
			</div>
		</div>
	</div>
</div>
<?php displayFooter(); ?>
