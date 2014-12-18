<?php
namespace RevenantBlue\Site;

require_once DIR_APPLICATION . 'controller/articles/articles-c.php';
require_once DIR_APPLICATION . 'controller/custom/custom-c.php';
require_once 'ui.php';
$title = '';
require_once 'head.php';
loadCKEditor();
?>
<?php if(DEVELOPMENT_ENVIRONMENT === TRUE): ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER_DIR; ?>view/js/articles.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo HTTP_SERVER_DIR; ?>view/js/articles.min.js"></script>
<?php endif; ?>
<script type="text/javascript">

<?php if(isset($pager->limit)): ?>
	var limit = parseInt('<?php echo hsc($pager->limit); ?>', 10);
<?php else: ?>
	var limit = 10;
<?php endif; ?>

var offset = 10;

<?php if(isset($pager->totalRecords)): ?>
	var totalNumOfEntries = parseInt('<?php echo hsc($pager->totalRecords); ?>', 10);
<?php endif; ?>
</script>
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
					<?php if(isset($articleEntries)): ?>
						<div id="article-main">
						<?php foreach($articleEntries as $key => $article): ?>
							<?php if((int)$key === 0): ?>
							<div id="index-main-article" class="index-articles">
							<?php elseif((int)$key >= 1 && (int)$key <= 3): ?>
							<?php if((int)$key === 1): ?>
							<div id="index-secondary-articles">
							<?php endif; ?>
								<div class="index-secondary-article index-articles">
							<?php else: ?>
								<div class="index-tertiary-article index-articles">
							<?php endif; ?>
									<div class="inner">
										<div class="article-image">
											<a href="#">
												<img src="<?php echo HTTP_IMAGE . 'articles/' .  hsc($article['image']); ?>" alt="<?php echo hsc($article['image_alt']); ?>" />
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
											<?php if(!empty($article['summary'])): ?>
											<div class="content">
												<?php echo $article['summary']; ?>
											</div>
											<div class="bottom-space">
												<p>
													<a href="<?php echo HTTP_SERVER . 'article/' . hsc($article['alias']);?>" class="read-more-link">Read more...</a>
												</p>
											</div>
											<?php else: ?>
											<div class="content">
												<?php echo $article['content']; ?>
											</div>
											<?php endif; ?>
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
								<?php if((int)$key === 3): ?>
								</div>
								<div class="clearfix"></div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
					<?php if(!empty($pager->totalRecords) && isset($pager->limit) && $pager->totalRecords > $pager->limit): ?>
					<div id="load-more-entries">
						<div class="inner">
							Show more articles
						</div>
					</div>
					<?php endif; ?>
					<?php elseif(isset($article) && isset($_GET['title'])): ?>
						<article id="blog-article" class="page-section">
							<div class="article-entry">
								<div class="article-head clearfix">
									<div class="article-title">
										<h1>
											<a href="<?php echo HTTP_SERVER . 'blog/' . hsc($article['alias']);?>"> <?php echo hsc($article['title']);?></a>
										</h1>
									</div>
									<div class="article-author">
										<p>Written By: <?php echo hsc($article['article_username']); ?> | <?php echo date("F d, Y", strtotime($article['date_posted'])); ?></p>
									</div>
								</div>
								<div class="content"><?php echo $article['content'];?></div>
								<div id="comments">
									<span class="comment-head2">COMMENTS</span>
									<span class="comment-head3">(<?php echo hsc($numOfCommentsForArticle); ?>)</span>
								</div>
								<?php if((int)$article['allow_comments'] === 1): ?>
								<?php if(aclVerify('view comments')): ?>
								<?php foreach($commentList as $comment): ?>
								<div id="comment-<?php echo hsc($comment['com_id']); ?>" class="comment" 
									 style="<?php if(30 * ($comment['root_distance']) <= 210 && $comment['root_distance'] >= 1): ?>
												margin-left: <?php echo 30 * (int)$comment['root_distance'] . 'px;'; ?> 
											<?php elseif($comment['root_distance'] > 1): ?>
												margin-left: <?php echo 260 . 'px;'; ?>
											<?php endif; ?> 
											<?php if(620 - 30 * $comment['root_distance'] >= 380): ?>
												width: <?php echo 650 - 30 * $comment['root_distance']; ?>
											<?php else: ?> 
												width: <?php echo 380 . 'px;'; ?>
											<?php endif; ?>">
									<div class="inner">
										<input id="comment-author-<?php echo hsc($comment['com_id']); ?>" type="hidden" value="<?php echo hsc($comment['com_author']); ?>" />
										<input id="comment-article-<?php echo hsc($comment['com_id']); ?>" type="hidden" value="<?php echo hsc($comment['article_id']); ?>" />
										<div class="comment-name-and-date">
											<span class="comment-date">
												<?php echo nicetime($comment['com_date']);?>
											</span>
											<a class="comment-permalink ui-icon ui-icon-link" title="Permalink for comment <?php echo hsc($comment['com_id']); ?>" href="#comment-<?php echo hsc($comment['com_id']); ?>"></a>
										</div>
										<div class="comment-content"><?php echo $comment['com_content'];?></div>
										<ul class="comment-footer clearfix">
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
									<div id="comment-reply-<?php echo hsc($comment['com_id']); ?>" style="display: none; clear: both;"></div>
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
										<div class="vert-space">
											<input type="hidden" name="csrfToken" value="<?php echo hsc($csrfToken); ?>" />
											<input type="hidden" name="id" value="<?php echo hsc($article['id']);?>" />
											<textarea placeholder="Type your comment here." id="com-content" name="comment"><?php if(isset($_SESSION['comment'])) echo hsc($_SESSION['comment']['content']); ?></textarea>
										</div>
										<div id="main-captcha" class="vert-space">
											<div id="captcha">
												<?php echo recaptcha_get_html($publickey); ?>
											</div>
										</div>
										<button id="submit-comment" type="submit" class="rb-btn blue-btn vert-space" name="submitComment" value="1">
											Submit Comment
										</button>
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
								<?php else: ?>
								<div id="comments-disabled">Comments are disabled for this article.</div>
								<?php endif; ?>
							</div>
						</article>
					<?php endif; ?>
					</section>
				</div>
			</div>
		</section>
	</form>
<?php displayFooter(); ?>
<?php commentCleanup(); ?>
