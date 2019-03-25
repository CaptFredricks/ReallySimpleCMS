<?php require_once 'header.php'; ?>

	<div class="wrapper">
		<h1>Admin Dashboard</h1>
		<?php
		$bars = array(
			array('posts'),
			array('posts', 'type', 'page'),
			array('users')
		);
		
		statsBarGraph($bars);
		?>
	</div>

<?php require_once 'footer.php'; ?>