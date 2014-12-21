<?php
namespace RevenantBlue\Site;

// Load the side menu
MenuBuilder::$title = 'Toggle Main Menu';
MenuBuilder::$style = 'display: none;';
$sideNav = MenuBuilder::displayMenu('main-menu', 'main-menu', '');
?>

<?php function loadCKEditor() { ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	if($(".comment-editor").length !== 0) {
		$(".comment-editor").ckeditor({
			forcePasteAsPlainText: true
		});
	}
	if($("#about-me-editor").length !== 0) {
		$("#about-me-editor").ckeditor({
			contentsCss: "<?php echo HTTP_SERVER; ?>site/view/css/forum-cke.css",
			forcePasteAsPlainText: true
		});
	}
});
</script> 
<?php } ?>

<?php function loadJqueryPlugins() { ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/jquery-plugins.js"></script>
<?php } ?>

<?php function loadJqueryUi() { ?>
<script src="<?php echo HTTP_SERVER; ?>site/view/js/bower-components/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo HTTP_SERVER; ?>site/view/js/jquery-ui/jquery-ui.min.css" type="text/css" />
<?php } ?>

<?php function loadJqueryEasing() { ?>
<script src="<?php echo HTTP_SERVER; ?>site/view/js/easing/jquery-easing.js" type="text/javascript"></script>
<?php } ?>

<?php function loadJqueryCycle() { ?>
<script src="<?php echo HTTP_SERVER; ?>site/view/js/cycle/jquery-cycle.js" type="text/javascript"></script>
<?php } ?>

<?php function loadPlupload() { ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/plupload/js/plupload.full.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/plupload/js/jquery.plupload.queue/jquery.plupload.queue.min.js"></script>
<?php } ?>

<?php function loadJqueryValidation() { ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/bower-components/jquery-validation/dist/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/bower-components/jquery-validation/dist/additional-methods.min.js"></script>
<?php } ?>

<?php function loadJqueryWaypoints($sticky = FALSE) { ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/bower-components/jquery-waypoints/waypoints.min.js"></script>
<?php if(!empty($sticky)): ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/bower-components/jquery-waypoints/shortcuts/sticky-elements/waypoints-sticky.min.js"></script>
<?php endif; ?>
<?php } ?>

<?php function loadStickyFloat() { ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/stickyfloat.js"></script>
<?php } ?>

<?php function loadJqueryAppear() { ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/jquery-appear.js"></script>
<?php } ?>

<?php function loadHighlightJs() { ?>
<link rel="stylesheet" href="<?php echo HTTP_SERVER; ?>site/view/js/highlight.js/styles/default.css">
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/highlight.js/highlight.pack.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<?php } ?>

<?php function loadPurl() { ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/purl/purl.js"></script>
<script type="text/javascript">
	var url = $.url("<?php echo hsc($_SERVER['REQUEST_URI']); ?>");
</script>
<?php } ?>

<?php function loadLoadingDots() { ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER; ?>site/view/js/jquery.loadingdotdotdot.js"></script>
<?php } ?>

<?php function loadSiteCss() { ?>
<link rel="stylesheet" href="<?php echo HTTP_SERVER; ?>site/view/css/site.css" type="text/css" />
<?php } ?>

<?php function displayLogo() { ?>
<a href="<?php echo HTTP_SERVER; ?>">
	<img src="<?php echo HTTP_IMAGE; ?>site/revenant-blue-logo-abbv.png" alt="Revenant Blue" />
</a>
<?php } ?>

<?php function loadFancyBox() { ?>
<script src="<?php echo HTTP_SERVER; ?>site/view/js/fancybox-2.1.5/lib/jquery.mousewheel-3.0.6.pack.js" type="text/javascript"></script>
<script src="<?php echo HTTP_SERVER; ?>site/view/js/fancybox-2.1.5/source/jquery.fancybox.pack.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo HTTP_SERVER; ?>site/view/js/fancybox-2.1.5/source/jquery.fancybox.css">
<?php } ?>

<?php function loadGoogleAnalytics() { ?>

<?php } ?>

<?php function displayHeader() { ?>
<header>
	<div id="banner" class="clearfix">
		<div id="banner-inner">
			<?php displayLogo(); ?>
			<?php displayTopNav(); ?>
		</div>
	</div>
</header>
<?php } ?>

<?php function displayTopNav() { ?>
<nav id="main-nav">
	<section id="account">
		<div class="inner clearfix">
			<ul>
			<?php if(!isset($_SESSION['userId'])): ?>
				<li>
					<a id="register-link" href="<?php echo HTTP_SERVER . 'register'; ?>">register</a>
				</li>
				<li class="separator">
					<span>|</span>
				</li>
				<li>
					<a id="login-link" href="<?php echo HTTP_SERVER . 'login'; ?>">login</a>
				</li>
			<?php else: ?>
				<li>
					<a id="logout-link" href="<?php echo $_SERVER['REQUEST_URI'] . '&amp;logout=true'; ?>">logout</a>
				</li>
				<?php if(isset($_SESSION['userId'])): ?>
				<li class="separator">
					<span>|</span>
				</li>
				<li>
					<a id="notifications-link" href="<?php echo HTTP_SERVER; ?>cpanel/notifications">
						<?php if(!empty($_SESSION['numOfUnreadNotifs'])): ?>
						<span id="unread-notifs-icon" class="ui-icon ui-icon-notice active fltlft" title="You have <?php echo hsc($_SESSION['numOfUnreadNotifs']); ?> unread notifications"></span>
						<sup class="unread-things">
							<?php echo hsc($_SESSION['numOfUnreadNotifs']); ?>
						</sup>
						<?php else: ?>
						<span id="notifs-icon" class="ui-icon ui-icon-notice fltlft" title="You have no unread notifications"></span>
						<?php endif; ?>
					</a>
				</li>
				<li class="separator">
					<span>|</span>
				</li>
				<li>
					<a id="private-messages-link" href="<?php echo HTTP_SERVER; ?>cpanel/messenger">
						<?php if(!empty($_SESSION['numOfUnreadMsgs'])): ?>
						<span id="unread-msgs-icon" class="ui-icon ui-icon-mail-closed active fltlft" title="You have <?php echo hsc($_SESSION['numOfUnreadMsgs']); ?> unread messages"></span>
						<sup class="unread-things">
							<?php echo hsc($_SESSION['numOfUnreadMsgs']); ?>
						</sup>
						<?php else: ?>
						<span id="msgs-icon" class="ui-icon ui-icon-mail-closed fltlft" title="You have no unread messages"></span>
						<?php endif; ?>
					</a>
				</li>
				<?php endif; ?>
				<li class="separator">
					<span>|</span>
				</li>
				<li>
					<a href="<?php echo HTTP_SERVER . 'cpanel'; ?>"><?php echo hsc($_SESSION['username']); ?></a>
				</li>
			<?php endif; ?>
			</ul>
			
		</div>
	</section>
	<div id="main-menu-toggle">
		<div class="menu-toggle-bar"></div>
		<div class="menu-toggle-bar"></div>
		<div class="menu-toggle-bar"></div>
	</div>
</nav>
<?php } ?>

<?php function displayFooter() { ?>
		<footer>
			<div class="inner">
			
			</div>
		</footer>
	</body>
</html>
<?php  } ?>

<?php function displayBreadCrumbs($target = false, $url = false, $overrideUrl = false) { ?>
	<?php if(isset($_GET['controller'])): ?>
		<section id="breadcrumbs">
			<a href="<?php echo HTTP_SERVER; ?>" class="inactive breadcrumb">HOME</a>
			<span class="spacer"> / </span>
			
			<?php if(is_array($overrideUrl)): ?>
				<?php foreach($overrideUrl as $key => $breadcrumb): ?>
					<a href="<?php echo $breadcrumb['url']; ?>" class="<?php if(empty($target) && $key + 1 === count($overrideUrl)): ?>active<?php else: ?>inactive<?php endif; ?> breadcrumb">
						<?php echo strtoupper(str_replace('-', ' ', $breadcrumb['title'])); ?>
					</a>
					<?php if($key + 1 !== count($overrideUrl)): ?>
					<span class="spacer"> / </span>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php else: ?>
			<a href="<?php echo HTTP_SERVER . hsc($_GET['controller']); ?>"
			   class="<?php if(empty($target) && empty($_GET['section'])): ?>active<?php else: ?>inactive<?php endif; ?> breadcrumb">
				<?php echo strtoupper(str_replace('-', ' ', hsc($_GET['controller']))); ?>
			</a>
				<?php if(!empty($target) || !empty($_GET['section'])): ?>
				<span class="spacer"> / </span>
				<?php endif; ?>
			<?php endif; ?>
			<?php if(!empty($_GET['section']) && empty($overrideUrl)): ?>
			<a href="<?php echo HTTP_SERVER . str_replace('-', '', hsc($_GET['section'])); ?>" class="inactive">
				<?php echo strtoupper(str_replace('-', ' ', hsc($_GET['section']))); ?>
			</a>
				<?php if(!empty($target) && !empty($url)): ?>
				<span class="spacer"> / </span>
				<?php endif; ?>
			<?php endif; ?>
			
			<?php if(!empty($target) && !empty($url)): ?>
			<span class="spacer"> / </span>
			<a href="<?php echo hsc($url); ?>" class="active"><?php echo strtoupper(hsc($target)); ?></a>
			<?php endif; ?>
		</section>
	<?php else: ?>
		<section id="breadcrumbs">
			<a id="bc-home" href="<?php echo HTTP_SERVER; ?>" class="breadcrumb active">HOME</a>
		</section>
	<?php endif; ?>
<?php } ?>

<?php function displayForumSearch() { /*>
	<div id="forum-search">
		<div class="inner">
			<input type="text" id="forum-search-input" name="forumSearch" />
		</div>
	</div>
<?php */} ?>

<?php function loadAccountModals() { ?>
	<?php global $csrfToken; ?>
	<?php if(empty($_SESSION['userId'])): ?>
	<div id="login-modal" title="LOGIN">
		<form id="login-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
			<div>
				<input type="hidden" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
			</div>
			<p>
				<label for="login-email">Email Address</label>
				<input type="email" id="login-email" name="email" value="" />
			</p>
			<p>
				<label for="login-password">Password</label>
				<input type="password" id="login-password" name="password" />
			</p>
			<p>
				<button id="submit-login" class="vert-space" name="submitLogin">LOGIN</button>
			</p>
			<p>
				Not a member?
			</p>
			<p>
				<button id="need-to-register" class="vert-space cancel">Register</button>
			</p>
			<p class="bottom-links fltrght">
				<a id="forgot-pw-link" href="#">Forgot your password?</a>
			</p>
		</form>
	</div>
	<div id="reg-modal" title="REGISTER">
		<form id="reg-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
			<div>
				<input type="hidden" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
			</div>
			<p>
				<label for="reg-email">Email Address</label>
				<input type="email" id="reg-email" name="regEmail" />
			</p>
			<p>
				<label for="reg-username">Username</label>
				<input type="text" id="reg-username" name="regUsername" />
			</p>
			<p>
				<label for="reg-password">Password</label>
				<input type="password" id="reg-password" name="regPassword" />
			</p>
			<p>
				<label for="reg-pwd-confirm">Confirm Password</label>
				<input type="password" id="reg-pwd-confirm" name="regPasswordConfirm" />
			</p>
			<p>
				<input type="hidden" name="receiveEmail" value="off" />
				<label for="receive-email" class="center-label">
					<input type="checkbox" id="receive-email" class="center-toggle" name="recieveEmail" />
					Receive notifications by email about updates and downtime
				</label>
			</p>
			<p>
				<button id="submit-reg" class="vert-space" name="submitUserReg">REGISTER</button>
			</p>
		</form>
	</div>
	<div id="pwd-reset-modal" title="RESET PASSWORD">
		<form id="pwd-reset-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
			<div class="standout-space">
				<p>
					<span>Please enter your e-mail address or username.</span>
					<br />
					<span>An e-mail will be sent to you that will allow you to reset your password.</span>
				</p>
			</div>
			<p>
				<label for="username-email">Username or E-mail</label>
				<input type="text" id="username-email" name="usernameEmail" />
			</p>
			<p>
				<button id="reset-password" name="resetPasword">Reset Password</button>
			</p>
		</form>
		<div class="bottom-links">
			<a id="back-to-login" href="#">Back to Login</a>
		</div>
	</div>
	<?php endif; ?>
<?php } ?>

<?php function displayNotifications() { ?>
<?php if(!empty($_SESSION['errors'])): ?>
	<?php if(is_array($_SESSION['errors'])): ?>
		<?php foreach($_SESSION['errors'] as $key=>$error): ?>
			<?php if(is_array($error)): ?>
				<?php foreach($error as $key2 => $error2): ?>
				 <div id="errors<?php echo $key2; ?>" class="errors notifications">
					<span class="icon-40-error icon-40-spacing"> </span>
					<p class="error-txt"><?php echo hsc($error2); ?></p>
					<span class="icon-20-close-notification icon-20-spacing" onclick="Javscript: $('#errors<?php echo $key2; ?>').remove();"> </span>
				</div>
				<?php endforeach; ?>
			<?php else: ?>
			<div id="errors<?php echo $key; ?>" class="errors notifications">
				<span class="icon-40-error icon-40-spacing"> </span>
				<p class="error-txt"><?php echo hsc($error); ?></p>
				<span class="icon-20-close-notification icon-20-spacing" onclick="Javscript: $('#errors<?php echo $key; ?>').remove();"> </span>
			</div>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php else: ?>
		<div id="errors" class="errors notifications">
			<span class="icon-40-error icon-40-spacing"> </span>
			<p class="error-txt"><?php echo hsc($_SESSION['errors']); ?></p>
			<span class="icon-20-close-notification icon-20-spacing" onclick="Javscript: $('#errors').remove();"> </span>
		</div>
	<?php endif; ?>
	<?php unset($_SESSION['errors']); ?>
<?php endif; ?>
<?php if(!empty($_SESSION['success'])): ?>
	<?php if(is_array($_SESSION['success'])): ?>
		<?php foreach($_SESSION['success'] as $key=>$success): ?>
		<div id="success<?php echo $key; ?>" class="success notifications">
			<span class="icon-40-inform icon-40-spacing"> </span>
			<p class="success-txt"><?php echo hsc($success); ?></p>
			<span class="icon-20-close-notification icon-20-spacing" onclick="Javscript: $('#success<?php echo $key; ?>').remove();"> </span>
		</div>
		<?php endforeach; ?>
	<?php else: ?>
		<div id="success" class="success notifications">
			<span class="icon-40-inform icon-40-spacing"> </span>
			<p class="success-txt"><?php echo hsc($_SESSION['success']); ?></p>
			<span class="icon-20-close-notification icon-20-spacing" onclick="Javscript: $('#success').remove();"> </span>
		</div>
	<?php endif; ?>
	<?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php } ?>

<?php function displayFriendRequest($username, $msgId) { ?>
	<div class="bottom-space">
		<p>
			<?php echo hsc($username); ?> wants to be your friend.
		</p>
		<p>
			Do you accept their friend request?
		</p>
	</div>
	<div class="vert-space">
		<button id="accept-friend-<?php echo hsc($msgId); ?>" class="accept-friend rb-btn blue-btn fltlft">Accept</button>
		<button id="decline-friend-<?php echo hsc($msgId); ?>" class="decline-friend rb-btn light-gray-btn fltlft horz-space">Decline</button>
	</div>
<?php } ?>
