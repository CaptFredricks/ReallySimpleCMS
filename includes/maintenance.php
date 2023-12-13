<?php
/**
 * Maintenance screen used by the CMS if it's in maintenance mode.
 * @since 1.3.6[b]
 *
 * Maintenance mode is useful for making potentially breaking changes on the website,
 *  and the CMS will only display it to logged out viewers.
 */
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Under Maintenance ▸ <?php putSetting('site_title'); ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php putStylesheet('style.min.css'); ?>
	</head>
	<body class="maintenance">
		<div class="wrapper">
			<h1>Welcome to <?php putSetting('site_title'); ?>!</h1>
			<p>This site is currently down for scheduled maintenance.</p>
			<p>Check back again later to see if the maintenance has ended.</p>
		</div>
		<p class="copyright">&copy; <?php echo date('Y'); ?> <?php echo CMS_NAME; ?>. All rights reserved.</p>
	</body>
</html>