<?php
// Include the header
getHeader('header-cat');
?>
<section class="wrapper">
	<article class="article-content">
		<h1><?php $rs_category->getCategoryName(); ?></h1>
		<?php getRecentPosts(10, null); ?>
	</article>
</section>
<?php
// Include the footer
getFooter();