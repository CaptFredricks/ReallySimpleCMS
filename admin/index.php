<?php
// Include the header
require_once __DIR__.'/header.php';
?>
<div class="wrapper clear">
	<div class="heading-wrap">
		<h1>Admin Dashboard</h1>
	</div>
	<?php
	// Bars for the graph
	$bars = array(
		array('posts', 'type', 'page'),
		array('posts', 'type', 'post'),
		array('users')
	);
	
	// Construct the bar graph
	statsBarGraph($bars);
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';