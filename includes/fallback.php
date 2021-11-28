<?php
/**
 * Fallback theme used by the CMS if there are no themes installed.
 * @since 2.3.0[a]
 */
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo CMS_NAME; ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php getStylesheet('style.min.css'); ?>
	</head>
	<body class="fallback-theme">
		<div class="wrapper">
			<h1>Welcome to <?php getSetting('site_title'); ?>!</h1>
			<p>You're seeing this page because you have no themes installed. Create one at <code><?php echo PATH; ?>/content/themes/[your-theme]</code> to get started.</p>
			<hr>
			<p>&copy; <?php echo date('Y'); ?> <?php echo CMS_NAME; ?>. All rights reserved.</p>
		</div>
	</body>
</html>