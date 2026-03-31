<?php
/**
 * Error page for HTTP 404 (Not Found) error responses.
 * @since 2.2.0-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/init.php';
requireFile(RS_FUNC);
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
		<?php
		echo domTag('div', array(
			'class' => 'wrapper',
			'content' => domTag('h1', array(
				'content' => 'Oops! The requested page could not be found.'
			)) . domTag('h3', array(
				'content' => 'It may have been moved or deleted. ' . domTag('a', array(
					'href' => '/',
					'content' => 'Return home'
				)) . '?'
			))
		));
		
		if($rs_session) adminBar();
		?>
	</body>
</html>