<?php
/**
 * Carbon theme - index template.
 * @since 1.5.5-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Carbon
 */

// Stop execution if the file is accessed directly
if(!defined('PATH')) exit('You do not have permission to access this resource.');

getHeader();

if(isPost() && postHasFeaturedImage()): ?>
	<div class="featured-image-wrap">
		<?php putPostFeaturedImage(); ?>
	</div>
<?php endif; ?>
<div class="wrapper">
	<article class="article-content">
		<h1><?php isPost() ? putPostTitle() : putTermName(); ?></h1>
		<?php isPost() ? putPostContent() : getRecentPosts(10, null); ?>
	</article>
</div>
<?php
getFooter();