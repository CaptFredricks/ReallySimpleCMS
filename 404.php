<?php
/**
 * Error page for HTTP 404 (Not Found) error responses.
 * @since 2.2.0-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/init.php';
require_once FUNC;

// Fetch the user's session data if they're logged in
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session']))
	$session = getOnlineUser($_COOKIE['session']);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Not Found ▸ <?php putSetting('site_title'); ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<meta name="theme-color" content="<?php putSetting('theme_color'); ?>">
		<link type="image/x-icon" href="<?php echo getMediaSrc(getSetting('site_icon')); ?>" rel="icon">
		<?php headerScripts(array('button', 'jquery')); ?>
	</head>
	<body class="<?php echo bodyClasses('not-found'); ?>">
		<div class="wrapper">
			<h1>Oops! The requested page could not be found.</h1>
			<h3>It may have been moved or deleted. <a href="/">Return home</a>?</h3>
		</div>
		<?php if($session) adminBar(); ?>
	</body>
</html>