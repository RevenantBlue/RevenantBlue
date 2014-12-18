<?php
namespace RevenantBlue\Site;

require_once DIR_APPLICATION . 'controller/articles/articles-c.php';
require_once 'ui.php';
if(isset($article)) {
	$title = $article['title'] . ' | News | ';
} else {
	$title = 'Article | ';
}
require_once 'head.php';
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER_DIR; ?>view/js/articles.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER_DIR; ?>view/js/articles.min.js"></script>
<?php endif; ?>
<?php loadCKEditor(); ?>
<?php loadSiteCss(); ?>
</head>
<body>
	<form id="main-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post" style="min-height: 0;">
		<div>
			<input type="hidden" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
			<input type="hidden" id="main-form-action" name="action" />
		</div>
	</form>
	<div id="main-form-placeholder">
		<?php displayHeader(); ?>
		<?php echo $sideNav; ?>
		<section id="main">
			<div id="main-outer" class="main-box">
				<div id="main-inner">
					<?php if(isset($article) && isset($_GET['title'])): ?>
					<article id="article" class="page-section">
						<div class="inner">
							<div class="article-image">
								<a href="#">
									<?php if(!empty($article['image'])): ?>
									<img src="<?php echo HTTP_IMAGE . 'articles/' .  hsc($article['image']); ?>" alt="<?php echo hsc($article['image_alt']); ?>" />
									<?php endif; ?>
								</a>
							</div>
							<div class="article clearfix">
								<div class="article-title">
									<h2>
										<a href="<?php echo HTTP_SERVER . 'article/' . hsc($article['alias']);?>"><?php echo hsc($article['title']);?></a>
									</h2>
								</div>
								<div class="article-author">
									<p>
										Written By: <?php echo hsc($article['article_username']); ?> | <?php echo date("F d, Y", strtotime($article['date_posted'])); ?>
									</p>
								</div>
								<div class="content">
									<?php echo $article['content']; ?>
								</div>
								<div class="article-bottom">
									<?php if($article['allow_comments'] == 1): ?>
									<div class="bottom_comment_number">
										<a href="<?php echo HTTP_SERVER . "article/" . hsc($article['alias']); ?>#comments">Comments (<?php echo hsc($article['num_of_comments']); ?>)</a>
										<span class="bottom-separator">|</span>
										<span>
											<a title="Leave a Comment" href="<?php echo HTTP_SERVER . "article/" . hsc($article['alias']);?>#leave-a-comment">Leave a Comment</a>
										</span>
									</div>
									<?php else: ?>
									<div>
										<p class="comments-disabled">Comments Disabled</p>
									</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<?php if($article['allow_comments'] == 1): ?>
							<?php if(aclVerify('view comments')): ?>
							<?php foreach($commentList as $comment): ?>
							<div id="comment-<?php echo hsc($comment['com_id']); ?>" class="comment" style="margin-left: <?php if(20 * ($comment['root_distance']) <= 260 && $comment['root_distance'] >= 1): echo 20 * ($comment['root_distance']); elseif($comment['root_distance'] > 1): echo 260; endif; ?>px; width: <?php if(620 - 20 * $comment['root_distance'] >= 380): echo 650 - 20 * $comment['root_distance']; else: echo 380; endif;?>px;">
								<div class="inner">
									<div class="comment-name-and-date">
										<span class="comment-name"><?php echo hsc($comment['com_author']);?></span>
										<a class="comment-date" title="Link to comment by <?php echo hsc($comment['com_author']); ?>" href="#comment-<?php echo hsc($comment['com_id']); ?>">
											<span><?php echo nicetime($comment['com_date']);?></span>
										</a>
									</div>
									<div class="comment-content"><?php echo $comment['com_content'];?></div>
									<ul class="comment-footer">
										<?php if(aclVerify('post comments')): ?>
										<li id="comment-replyto-<?php echo hsc($comment['com_id']); ?>" class="comment-reply">Reply</li>
										<?php else: ?>
										<li>
											<a href="<?php echo HTTP_SERVER; ?>login">Login to reply</a>
										</li>
										<?php endif; ?>
										<?php if(isset($_SESSION['userId']) && $comments->getCommentLike($comment['com_id'], $_SESSION['userId'])): ?>
										<li id="comment-like-<?php echo hsc($comment['com_id']); ?>" class="comment-likeit">Liked</li>
										<?php elseif(isset($_SESSION['userId'])): ?>
										<li id="comment-like-<?php echo hsc($comment['com_id']); ?>" class="comment-likeit">Like</li>
										<?php endif; ?>
										<?php if($comment['com_likes'] > 1): ?>
										<li id="comment-likes-<?php echo hsc($comment['com_id']); ?>" class="comment-likes"><?php echo hsc($comment['com_likes']);?> people liked this.</li>
										<?php elseif($comment['com_likes'] == 1): ?>
										<li id="comment-likes-<?php echo hsc($comment['com_id']); ?>" class="comment-likes"><?php echo hsc($comment['com_likes']);?> person liked this.</li>
										<?php else: ?>
										<li id="comment-likes-<?php echo hsc($comment['com_id']); ?>" class="comment-likes"></li>
										<?php endif; ?>
										<?php if(isset($_SESSION['userId']) && $comments->getCommentFlag($comment['com_id'], $_SESSION['userId'])):?>
										<li id="comment-flag-<?php echo hsc($comment['com_id']); ?>" class="comment-flag">You have flagged this comment.</li>
										<?php elseif(isset($_SESSION['userId'])): ?>
										<li id="comment-flag-<?php echo hsc($comment['com_id']); ?>" class="comment-flag">Flag</li>
										<?php endif; ?>
									</ul>
								</div>
								<div id="comment-reply-<?php echo hsc($comment['com_id']); ?>" style="display: none; clear: both;">
									<form action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
										<div class="comment-reply-box">
											<label for="reply-<?php echo hsc($comment['com_id']); ?>" class="comment-label">Replying to <?php echo hsc($comment['com_author']); ?></h2>
											<div>
												<input type="hidden" id="csrfToken-<?php echo hsc($comment['com_id']); ?>" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
												<input type="hidden" name="id" value="<?php echo hsc($comment['article_id']); ?>" />
												<input type="hidden" id="reply-to-<?php echo hsc($comment['com_id']); ?>" name="commentId" value="<?php echo hsc($comment['com_id']); ?>" />
												<textarea class="comment-editor" placeholder="Type your comment here." id="reply-<?php echo hsc($comment['com_id']); ?>" name="reply" rows="7" cols="30"></textarea>
											</div>
											<button id="submit-reply-<?php echo hsc($comment['com_id']); ?>" class="submit-reply" name="submitReply">Submit Reply</button>
											<button id="cancel-reply-<?php echo hsc($comment['com_id']); ?>" class="cancel-reply">Cancel</button>
										</div>
									</form>
								</div>
							</div>
							<?php endforeach; ?>
							<?php endif; ?>
							<div>
								<a id="leave-a-comment" href="#leave-a-comment"></a>
							</div>
							<div id="post-comment">
								<h3>LEAVE A COMMENT</h3>
								<div id="post-comment-content">
									<div class="comment_error">
										<?php displayNotifications(); ?>
									</div>
									<?php if(aclVerify('post comments')): ?>
									<form id="comment-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
										<div>
											<input type="hidden" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
											<input type="hidden" name="id" value="<?php echo hsc($article['id']);?>" />
											<?php if(!isset($_SESSION['username'])): ?>
											<label for="comment-author" class="comment-label">Name</label>
											<input type="text" id="com-author" size="50" name="author" maxlength="30" />
											<?php endif; ?>
											<label for="com-content" class="comment-label">Comment</label>
											<textarea class="comment-editor" placeholder="Type your comment here." cols="50" rows="15" id="com-content" name="comment"><?php
											if(isset($_SESSION['comment'])) echo hsc($_SESSION['comment']['content']);
											?></textarea>
										</div>
										<button id="submit-comment" name="submitComment" value="1">Submit Comment</button>
									</form>
									<?php elseif(!aclVerify('post comments') && isset($_SESSION['username'])): ?>
									<div>
										<p>You do not have permission to post comments.</p>
									</div>
									<?php else: ?>
									<div>
										<p>You must <a class="hover" href="<?php echo HTTP_SERVER; ?>login">login</a> or <a class="hover" href="<?php echo HTTP_SERVER; ?>register">register</a> a new account before posting comments.</p>
									</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
						</div>
					</article>
					<?php endif; ?>
				</div>
			</div>
			<div class="clearfix"></div>
		</section>
	</div>
	<div id="reply-dialog" title="Comment Reply">
		<form id="reply-form" action="<?php echo hsc($_SERVER['REQUEST_URI']); ?>" method="post">
			<div class="comment-reply-box">
				<div class="top-space clearfix">
					<input type="hidden" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
					<input type="hidden" name="submitReply" value="1" />
					<input type="hidden" id="reply-article-id" name="id" value="<?php echo hsc($article['id']); ?>" />
					<input type="hidden" id="reply-comment-id" name="commentId" />
					<textarea class="comment-editor" placeholder="Type your comment here." name="reply"></textarea>
				</div>
			</div>
		</form>
	</div>
<?php commentCleanup(); ?>
<?php displayFooter(); ?>
