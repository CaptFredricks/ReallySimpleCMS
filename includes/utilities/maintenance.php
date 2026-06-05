<?php
/**
 * Maintenance page used by the system if it's in maintenance mode.
 * Maintenance mode is useful for making potentially breaking changes on the website,
 *  and the system will only display it to logged out viewers.
 * @since 1.3.6-beta
 *
 * @package ReallySimpleCMS
 */

requireFile(RS_FRONT_FUNC);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Under Maintenance ▸ <?php echo putSetting('site_title'); ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link type="image/x-icon" href="<?php echo getMediaSrc(getSetting('site_icon')); ?>" rel="icon">
		<?php headerScripts(array('button', 'jquery')); ?>
	</head>
	<body class="maintenance-page">
		<?php
		// Content
		domTagPr('div', array(
			'class' => 'wrapper',
			'content' => domTag('h1', array(
				'content' => 'Welcome to ' . getSetting('site_title') . '!'
			)) . domTag('p', array(
				'content' => 'This site is currently down for scheduled maintenance.'
			)) . domTag('p', array(
				'content' => 'Check back again later to see if the maintenance has ended.'
			))
		));
		
		// Copyright
		domTagPr('p', array(
			'class' => 'copyright',
				'content' => '&copy; ' . date('Y') . ' ' . domTag('a', array(
				'href' => 'https://github.com/ReallySimpleSystems/ReallySimpleCMS',
				'target' => '_blank',
				'rel' => 'noreferrer noopener',
				'content' => RS_ENGINE
			)) . ' &ndash; ' . domTag('em', array(
				'content' => 'powered by ' . RS_DEVELOPER
			)) . ' &bull; All Rights Reserved.'
		));
		
		if(!empty($rs_session)) adminBar();
		?>
	</body>
</html>
<?php
// Prevent further execution of scripts or content output
exit;