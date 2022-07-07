<?php
// Stop execution if the file is accessed directly
if(!defined('PATH')) exit('You do not have permission to access this directory.');

getHeader();

if(postHasFeaturedImage()): ?>
	<div class="featured-image-wrap">
		<?php putPostFeaturedImage(); ?>
	</div>
<?php endif; ?>
<div class="wrapper">
	<article class="article-content">
		<h1 class="post-title"><?php putPostTitle(); ?></h1>
		<div class="post-meta"><span class="author">by <?php putPostAuthor(); ?></span><span class="date"><i class="fa-regular fa-clock"></i> <?php putPostDate(); ?></span></div>
		<?php putPostContent(); ?>
		<p class="post-categories">Categories: <?php putPostTerms(); ?></p>
	</article>
	<section class="comments">
		<h2>Comments</h2>
		<?php getPostComments(); ?>
	</section>
</div>
<?php getFooter(); ?>