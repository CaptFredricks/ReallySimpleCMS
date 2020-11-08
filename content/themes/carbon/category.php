<?php
// Include the header
getHeader();
?>
<div class="wrapper">
	<article class="article-content">
		<h1>Category: <?php $rs_category->getCategoryName(); ?></h1>
		<?php getRecentPosts(10, null); ?>
	</article>
</div>
<?php
// Include the footer
getFooter();