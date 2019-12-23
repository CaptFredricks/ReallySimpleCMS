<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php getSetting('site_title'); ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="theme-color" content="<?php getSetting('theme_color'); ?>">
		<link type="image/x-icon" href="<?php echo getMediaSrc(getSetting('site_icon', false)); ?>" rel="icon">
		<?php getStylesheet('style.css'); ?>
		<?php getStylesheet('font-awesome.min.css', '5.11.2'); ?>
		<?php getThemeStylesheet('style.css'); ?>
		<?php getScript('jquery.min.js', '3.4.1'); ?>
	</head>
	<body>
		<header class="header">
			<div class="top-bar">
				<div class="site-logo">
					<a href="/">
						<?php echo getMedia(getSetting('site_logo', false)); ?>
					</a>
				</div>
				<div class="social-menu-wrap">
					<?php getWidget('social-media'); ?>
					<div class="nav-menu-toggle">
						<i class="fas fa-bars"></i>
					</div>
					<div class="nav-menu-wrap">
						<?php getMenu('main-menu'); ?>
					</div>
				</div>
			</div>
			<div class="nav-menu-overlay"></div>
		</header>
		<div class="wrapper">