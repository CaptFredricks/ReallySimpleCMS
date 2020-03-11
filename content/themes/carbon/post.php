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
<section class="wrapper">
	<article class="article-content">
		<h1><?php $rs_post->getPostTitle(); ?></h1>
		<?php $rs_post->getPostContent(); ?>
		<p>Categories: <?php $rs_post->getPostCategories(); ?></p>
	</article>
</section>
<?php
// Include the footer
getFooter();