<?php
// Stop execution if the file is accessed directly
if(!defined('PATH')) exit('You do not have permission to access this directory.');

// Include the header
getHeader();

// Check whether the post has a featured image
if(!is_null($rs_post) && $rs_post->postHasFeatImage()): ?>
	<div class="featured-image-wrap">
		<?php $rs_post->getPostFeatImage(); ?>
	</div>
<?php endif; ?>
<div class="wrapper">
	<article class="article-content">
		<h1><?php !is_null($rs_post) ? $rs_post->getPostTitle() : $rs_term->getTermName(); ?></h1>
		<?php !is_null($rs_post) ? $rs_post->getPostContent() : getRecentPosts(10, null); ?>
	</article>
</div>
<?php
// Include the footer
getFooter();