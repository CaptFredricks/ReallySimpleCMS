<?php
// Stop execution if the file is accessed directly
if(!defined('PATH')) exit('You do not have permission to access this directory.');

// Include the header
getHeader();
?>
<article>
	<?php getPost('FeatImage', 'sample-page'); ?>
	<h1><?php getPost('Title', 'sample-page'); ?></h1>
	<?php getPost('Content', 'sample-page'); ?>
</article>
<?php
// Include the footer
getFooter();