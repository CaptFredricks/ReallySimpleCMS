<?php
// Include the header
getHeader();
?>
<div class="wrapper">
	<article class="article-content">
		<h1><?php putTermTaxName(); ?>: <?php putTermName(); ?></h1>
		<?php getRecentPosts(10, null); ?>
	</article>
</div>
<?php
// Include the footer
getFooter();