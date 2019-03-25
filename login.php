<?php
/**
 * Log into the admin dashboard.
 * @since Alpha 1.3.3
 */

// Include the initialization file
require_once __DIR__.'/init.php';
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php getSetting('site_title'); ?> &rtrif; Log In</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<?php getStylesheet('style.css'); ?>
		<?php getStylesheet('buttons.css'); ?>
	</head>
	<body class="login">
		<div class="wrapper">
			<h1><?php getSetting('site_title'); ?> Log In</h1>
			<form action="" method="post">
				<?php
				
				?>
			</form>
		</div>
	</body>
</html>