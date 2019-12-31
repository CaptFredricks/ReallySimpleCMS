<?php
// Stop execution if the file is accessed directly
if(!defined('PATH')) exit('You do not have permission to access this directory.');

// Include the header
getHeader();

// Fetch the post object
$rs_post = getPost();
?>
<div class="featured-image-wrap">
	<?php $rs_post->getPostFeatImage(); ?>
</div>
<section class="wrapper">
	<article class="post-content post-id-<?php $rs_post->getPostId(); ?>">
		<h1><?php $rs_post->getPostTitle(); ?></h1>
		<?php $rs_post->getPostContent(); ?>
	</article>
</section>
<?php
// Include the footer
getFooter();