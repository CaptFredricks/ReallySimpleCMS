<?php
// Stop execution if the file is accessed directly
if(!defined('PATH')) exit('You do not have permission to access this directory.');

// Include the header
getHeader();

// Check whether the post has a featured image
if($rs_post->postHasFeatImage()): ?>
	<div class="featured-image-wrap">
		<?php $rs_post->getPostFeatImage(); ?>
	</div>
<?php endif; ?>
<div class="wrapper">
	<article class="article-content">
		<h1 class="post-title"><?php $rs_post->getPostTitle(); ?></h1>
		<div class="post-meta"><span class="author">by <?php $rs_post->getPostAuthor(); ?></span><span class="date"><i class="far fa-clock"></i> <?php $rs_post->getPostDate(); ?></span></div>
		<?php $rs_post->getPostContent(); ?>
		<p class="post-categories">Categories: <?php $rs_post->getPostTerms(); ?></p>
	</article>
</div>
<?php
// Include the footer
getFooter();