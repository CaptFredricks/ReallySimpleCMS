<?php
// Include the header
getHeader();
?>
<div class="wrapper">
	<article class="article-content">
		<h1><?php echo ucwords(str_replace(array('_', '-'), ' ', getTermTaxonomy())); ?>: <?php putTermName(); ?></h1>
		<?php getRecentPosts(10, null); ?>
	</article>
</div>
<?php
// Include the footer
getFooter();