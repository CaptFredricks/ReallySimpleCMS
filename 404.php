<?php
/**
 * Error page for HTTP 404 (Not Found) error responses.
 * @since 2.2.0[a]
 */

// Include the initialization file
require_once __DIR__.'/init.php';

// Include functions
require_once PATH.INC.'/functions.php';

// Check whether the session cookie is set and the user's session is valid
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session'])) {
	// Fetch the user's data
	$session = getOnlineUser($_COOKIE['session']);
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Not Found &rtrif; <?php getSetting('site_title'); ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<meta name="theme-color" content="<?php getSetting('theme_color'); ?>">
		<link type="image/x-icon" href="<?php echo getMediaSrc(getSetting('site_icon', false)); ?>" rel="icon">
		<?php getStylesheet('style.css'); ?>
		<?php getStylesheet('font-awesome.min.css', '5.12.0'); ?>
	</head>
	<body class="<?php echo bodyClasses('not-found'); ?>">
		<div class="wrapper">
			<h1>Oops! The requested page could not be found.</h1>
			<h3>It may have been moved or deleted. <a href="/">Return home</a>?</h3>
		</div>
		<?php if($session) adminBar(); ?>
	</body>
</html>