<?php
/**
 * Carbon theme - taxonomy template.
 * @since 1.0.6-beta
 *
 * @package ReallySimpleCMS
 * @subpackage Carbon
 */

getHeader();
?>
<div class="wrapper">
	<article class="article-content">
		<h1><?php putTermTaxName(); ?>: <?php putTermName(); ?></h1>
		<?php getRecentPosts(10, null); ?>
	</article>
</div>
<?php
getFooter();