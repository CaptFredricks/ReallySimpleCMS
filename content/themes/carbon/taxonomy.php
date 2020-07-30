<?php
// Include the header
getHeader('header-tax');
?>
<section class="wrapper">
	<article class="article-content">
		<h1><?php echo ucwords(str_replace(array('_', '-'), ' ', $rs_term->getTermTaxonomy(false))); ?>: <?php $rs_term->getTermName(); ?></h1>
		<?php getRecentPosts(10, null); ?>
	</article>
</section>
<?php
// Include the footer
getFooter();