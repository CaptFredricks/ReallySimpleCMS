<?php
/**
 * PHP Exception/Error page.
 * Displays a user-friendly message if ini `display_errors` is off,
 *  and the backtrace of the error if the setting is on.
 * @since 1.3.14-beta
 *
 * @package ReallySimpleCMS
 */
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Error ▸ <?php echo RS_ENGINE; ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link type="image/x-icon" href="<?php echo getMediaSrc(getSetting('site_icon')); ?>" rel="icon">
		<?php headerScripts(array('button', 'jquery')); ?>
	</head>
	<body class="error-page">
		<div class="wrapper">
			<h1>An Error Occurred</h1>
			<main class="content" role="main">
				<?php $rs_error->generateError(); ?>
			</main>
			<p class="powered-by">Powered by <?php echo RS_DEVELOPER; ?></p>
		</div>
		<?php if(!empty($rs_session)) adminBar(); ?>
	</body>
</html>
<?php
// Prevent further execution of scripts or content output
exit;