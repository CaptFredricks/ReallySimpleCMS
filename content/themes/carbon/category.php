<?php
/**
 * Carbon theme - category template.
 * @since 2.4.0-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Carbon
 */

getHeader();
?>
<div class="wrapper">
	<article class="article-content">
		<h1>Category: <?php putCategoryName(); ?></h1>
		<?php getRecentPosts(10, 0); ?>
	</article>
</div>
<?php
getFooter();