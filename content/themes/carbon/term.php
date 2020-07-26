<?php
// Include the header
getHeader('header-term');
?>
<section class="wrapper">
	<article class="article-content">
		<h1><?php echo ucwords(str_replace(array('_', '-'), ' ', $rs_term->getTermTaxonomy(false).'s')); ?>: <?php $rs_term->getTermName(); ?></h1>
		<?php getRecentPosts(10, null); ?>
	</article>
</section>
<?php
// Include the footer
getFooter();