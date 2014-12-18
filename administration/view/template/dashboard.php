<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
if(isset($_SESSION['userId'])) {
	$title = 'Dashboard';
} else {
	$title = 'Login';
}
require_once 'head.php';
require_once 'ui.php';
loadJqueryUi();
loadMainJs();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/dashboard.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_ADMIN_DIR; ?>view/js/dashboard.min.js"></script>
<?php endif; ?>	
<?php loadMainCss(); ?>
</head>
<body>
<div class="main-iframe-top"> </div>
<div id="fixed-wrap" class="clearfix">
	<div id="fixed-inner" class="clearfix">
		<?php displayNotifications(); ?>
		<form id="adminForm" action="" method="post">
			<div id="main-window">
				<input type="hidden" id="csrf-token" value="<?php echo $csrfToken; ?>" />
				<div id="main-window-inner">
					<div id="dashboard-main" class="fltlft" style="display: none;">
						<div class="inner">
							<div class="element">
								<div class="element-top">Users Online</div>
								<div class="element-body">
									<table class="overview no-border width100pcnt left">
										<tr>
											<th>Admin Registered</th>
											<td><?php echo $backendActiveUsers['loggedIn']; ?></td>
										</tr>
										<tr>
											<th>Frontend Registered</th>
											<td><?php echo $frontendActiveUsers['loggedIn']; ?></td>
										</tr>
										<tr>
											<th>Frontend Anonymous</th>
											<td><?php echo $frontendActiveUsers['anon']; ?></td>
										</tr>
										<tr>
											<th>Total Users Online</th>
											<td>
												<?php echo $backendActiveUsers['loggedIn'] + $frontendActiveUsers['loggedIn'] + $frontendActiveUsers['anon']; ?>
											</td>
										</tr>
									</table>
								</div>
							</div>
							<div class="element">
								<div class="element-top">
									Failed Logins
									<a id="clear-failed-logins" href="#" class="fltrght">clear</a>
								</div>
								<div class="element-body">
									<table id="failed-logins" class="overview no-border width100pcnt left">
										<tr id="failed-login-head" <?php if(empty($failedLogins)): ?>style="display: none;"<?php endif; ?>>
											<th>Username</th>
											<th>Data</th>
											<th>IP Address</th>
										</tr>
										<?php if(!empty($failedLogins)): ?>
										<?php foreach($failedLogins as $failedLogin): ?>
										<tr class="failed-login">
											<td><?php echo hsc($failedLogin['login_username']); ?></td>
											<td><?php echo date('M d, Y', strtotime($failedLogin['login_date'])); ?></td>
											<td>
												<?php if($globalSettings['log_user_ips']['value']): ?>
													<?php echo hsc($failedLogin['login_ip']); ?>
												<?php else: ?>
												IP Address Logging Disabled
												<?php endif; ?>
											</td>
										</tr>
										<?php endforeach; ?>
										<?php endif; ?>
										<tr id="no-failed-logins" <?php if(!empty($failedLogins)): ?>style="display: none;"<?php endif; ?>>
											<td colspan="3">There are no falied login attempts</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
					<div id="dashboard-overview" class="fltrght" style="display: none;">
						<div class="dash-overview-unit">
							<div class="dash-overview-head">
								<h3>Articles</h3>
								<span class="dash-unit-close ui-icon ui-icon-close"></span>
							</div>
							<div class="dash-overview-body">
								<table class="dash-unit-detail">
									<tr>
										<th>
											Published
										</th>
										<td>
											<a class="dash-articles-links" href="<?php echo HTTP_ADMIN; ?>articles?published=1&order=date_posted&sort=desc">
												<?php echo hsc($numOfPublishedArticles); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Pending Approval
										</th>
										<td>
											<a class="dash-articles-links" href="<?php echo HTTP_ADMIN; ?>articles?published=3&order=date_posted&sort=desc">
												<?php echo hsc($numOfPendingArticles); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Featured
										</th>
										<td>
											<a class="dash-articles-links" href="<?php echo HTTP_ADMIN; ?>articles?featured=1&order=date_posted&sort=desc">
												<?php echo hsc($numOfFeaturedArticles); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Drafts
										</th>
										<td>
											<a class="dash-articles-links" href="<?php echo HTTP_ADMIN; ?>articles?published=2&order=date_posted&sort=desc">
												<?php echo hsc($numOfDraftArticles); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Unpublished
										</th>
										<td>
											<a class="dash-articles-links" href="<?php echo HTTP_ADMIN; ?>articles?published=0&order=date_posted&sort=desc">
												<?php echo hsc($numOfUnpublishedArticles); ?>
											</a>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="dash-overview-unit">
							<div class="dash-overview-head">
								<h3>Categories</h3>
								<span class="dash-unit-close ui-icon ui-icon-close"></span>
							</div>
							<div class="dash-overview-body">
								<table class="dash-unit-detail">
									<tr>
										<th>
											Categories
										</th>
										<td>
											<a class="dash-comments-links" href="<?php echo HTTP_ADMIN; ?>categories">
												<?php echo hsc($numOfCategories); ?>
											</a>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="dash-overview-unit">
							<div class="dash-overview-head">
								<h3>Comments</h3>
								<span class="dash-unit-close ui-icon ui-icon-close"></span>
							</div>
							<div class="dash-overview-body">
								<table class="dash-unit-detail">
									<tr>
										<th>
											Comments
										</th>
										<td>
											<a class="dash-comments-links" href="<?php echo HTTP_ADMIN; ?>comments?state=1&order=date_posted&sort=desc">
												<?php echo hsc($numOfComments); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Approved
										</th>
										<td>
											<a class="dash-comments-links" href="<?php echo HTTP_ADMIN; ?>comments?state=1&order=date_posted&sort=desc">
												<?php echo hsc($numOfApprovedComments); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Pending
										</th>
										<td>
											<a class="dash-comments-links" href="<?php echo HTTP_ADMIN; ?>comments?state=1&order=date_posted&sort=desc">
												<?php echo hsc($numOfPendingComments); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Flagged
										</th>
										<td>
											<a class="dash-comments-links" href="<?php echo HTTP_ADMIN; ?>comments?state=1&order=date_posted&sort=desc">
												<?php echo hsc($numOfSpamComments); ?>
											</a>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="dash-overview-unit">
							<div class="dash-overview-head">
								<h3>Forums</h3>
								<span class="dash-unit-close ui-icon ui-icon-close"></span>
							</div>
							<div class="dash-overview-body">
								<table class="dash-unit-detail">
									<tr>
										<th>
											Posts
										</th>
										<td>
											<?php echo hsc($numOfPosts); ?>
										</td>
									</tr>
									<tr>
										<th>
											Topics
										</th>
										<td>
											<?php echo hsc($numOfTopics); ?>
										</td>
									</tr>
									<tr>
										<th>
											Forums
										</th>
										<td>
											<a href="<?php echo HTTP_ADMIN; ?>forums">
												<?php echo hsc($numOfForums); ?>
											</a>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="dash-overview-unit">
							<div class="dash-overview-head">
								<h3>Links</h3>
								<span class="dash-unit-close ui-icon ui-icon-close"></span>
							</div>
							<div class="dash-overview-body">
								<table class="dash-unit-detail">
									<tr>
										<th>
											Links
										</th>
										<td>
											<a class="dash-comments-links" href="<?php echo HTTP_ADMIN; ?>links">
												<?php echo hsc($numOfLinks); ?>
											</a>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="dash-overview-unit">
							<div class="dash-overview-head">
								<h3>Media</h3>
								<span class="dash-unit-close ui-icon ui-icon-close"></span>
							</div>
							<div class="dash-overview-body">
								<table class="dash-unit-detail">
									<tr>
										<th>
											Files
										</th>
										<td>
											<a class="dash-media-links" href="<?php echo HTTP_ADMIN; ?>media">
												<?php echo hsc($numOfMediaFiles); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Images
										</th>
										<td>
											<a class="dash-media-links" href="<?php echo HTTP_ADMIN; ?>media?type=image&order=date_posted&sort=desc">
												<?php echo hsc($numOfMediaImages); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Video
										</th>
										<td>
											<a class="dash-media-links" href="<?php echo HTTP_ADMIN; ?>media?type=video&order=date_posted&sort=desc">
												<?php echo hsc($numOfMediaVideos); ?>
											</a>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="dash-overview-unit">
							<div class="dash-overview-head">
								<h3>Menus</h3>
								<span class="dash-unit-close ui-icon ui-icon-close"></span>
							</div>
							<div class="dash-overview-body">
								<table class="dash-unit-detail">
									<tr>
										<th>
											Menus
										</th>
										<td>
											<a class="dash-comments-links" href="<?php echo HTTP_ADMIN; ?>menus">
												<?php echo hsc($numOfMenus); ?>
											</a>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="dash-overview-unit">
							<div class="dash-overview-head">
								<h3>Pages</h3>
								<span class="dash-unit-close ui-icon ui-icon-close"></span>
							</div>
							<div class="dash-overview-body">
								<table class="dash-unit-detail">
									<tr>
										<th>
											Pages
										</th>
										<td>
											<a class="dash-comments-links" href="<?php echo HTTP_ADMIN; ?>pages">
												<?php echo hsc($numOfPages); ?>
											</a>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="dash-overview-unit">
							<div class="dash-overview-head">
								<h3>Photo Gallery</h3>
								<span class="dash-unit-close ui-icon ui-icon-close"></span>
							</div>
							<div class="dash-overview-body">
								<table class="dash-unit-detail">
									<tr>
										<th>
											Albums
										</th>
										<td>
											<a class="dash-gallery-links" href="<?php echo HTTP_ADMIN; ?>photogallery?state=1&order=date_posted&sort=desc">
												<?php echo hsc($numOfAlbums); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Published
										</th>
										<td>
											<a class="dash-gallery-links" href="<?php echo HTTP_ADMIN; ?>photogallery?state=1&order=date_posted&sort=desc">
												<?php echo hsc($numOfPublishedAlbums); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Unpublished
										</th>
										<td>
											<a class="dash-gallery-links" href="<?php echo HTTP_ADMIN; ?>comments?state=1&order=date_posted&sort=desc">
												<?php echo hsc($numOfUnpublishedAlbums); ?>
											</a>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="dash-overview-unit">
							<div class="dash-overview-head">
								<h3>Tags</h3>
								<span class="dash-unit-close ui-icon ui-icon-close"></span>
							</div>
							<div class="dash-overview-body">
								<table class="dash-unit-detail">
									<tr>
										<th>
											Tags
										</th>
										<td>
											<a class="dash-comments-links" href="<?php echo HTTP_ADMIN; ?>tags">
												<?php echo hsc($numOfTags); ?>
											</a>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="dash-overview-unit">
							<div class="dash-overview-head">
								<h3>Users</h3>
								<span class="dash-unit-close ui-icon ui-icon-close"></span>
							</div>
							<div class="dash-overview-body">
								<table class="dash-unit-detail">
									<tr>
										<th>
											Users
										</th>
										<td>
											<a class="dash-users-links" href="<?php echo HTTP_ADMIN; ?>users">
												<?php echo hsc($numOfUsers	); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Pending Activation
										</th>
										<td>
											<a class="dash-users-links" href="<?php echo HTTP_ADMIN; ?>users?activated=0&order=date_joined&sort=desc">
												<?php echo hsc($numOfPendingUsers); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Enabled
										</th>
										<td>
											<a class="dash-users-links" href="<?php echo HTTP_ADMIN; ?>users?enabled=1&order=date_joined&sort=desc">
												<?php echo hsc($numOfEnabledUsers); ?>
											</a>
										</td>
									</tr>
									<tr>
										<th>
											Disabled
										</th>
										<td>
											<a class="dash-users-links" href="<?php echo HTTP_ADMIN; ?>users?enabled=0&order=date_joined&sort=desc">
												<?php echo hsc($numOfDisabledUsers); ?>
											</a>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<div id="dashboard">

					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<?php displayFooter(); ?>
