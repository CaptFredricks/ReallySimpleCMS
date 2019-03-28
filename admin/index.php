<?php
// Include header
require_once __DIR__.'/header.php';
?>
<div class="wrapper">
	<h1>Admin Dashboard</h1>
	<?php
	// Bars for the graph
	$bars = array(
		array('posts', 'type', 'post'),
		array('posts', 'type', 'page'),
		array('users')
	);
	
	// Create bar graph
	statsBarGraph($bars);
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';
?>