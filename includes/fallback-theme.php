<?php
/**
 * Fallback theme used by the CMS if there are no themes installed.
 * @since 2.3.0-alpha
 *
 * @package ReallySimpleCMS
 */

// Determine whether the site is in debug mode
$debug = false;
if(defined('DEBUG_MODE') && DEBUG_MODE) $debug = true;
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php putSetting('site_title'); ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php
		if($debug)
			putStylesheet('style.css');
		else
			putStylesheet('style.min.css');
		?>
	</head>
	<body class="fallback-theme">
		<div class="wrapper">
			<h1>Welcome to <?php putSetting('site_title'); ?>!</h1>
			<p>You're seeing this message either because you have no themes installed, or your active theme's directory was renamed.</p>
			<p>You can create a theme by adding a new directory in <code><?php echo PATH; ?>/content/themes</code> or through the admin dashboard.</p>
			<hr>
			<article>
				<?php if(isPost()): ?>
					<h2><?php putPostTitle(); ?></h2>
					<?php putPostContent(); ?>
				<?php else: ?>
					<h2><?php putTermTaxName(); ?>: <?php putTermName(); ?></h2>
					<?php putTermPosts(); ?>
				<?php endif; ?>
			</article>
		</div>
		<p class="copyright">&copy; <?php echo date('Y'); ?> <?php echo CMS_ENGINE; ?>. All rights reserved.</p>
	</body>
</html>