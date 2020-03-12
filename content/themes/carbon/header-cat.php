<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php $rs_category->getCategoryName(); ?> &rtrif; <?php getSetting('site_title'); ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="theme-color" content="<?php getSetting('theme_color'); ?>">
		<meta name="description" content="">
		<meta property="og:title" content="<?php $rs_category->getCategoryName(); ?>">
		<meta property="og:type" content="website">
		<meta property="og:url" content="<?php $rs_category->getTermUrl(); ?>">
		<meta property="og:image" content="<?php echo getMediaSrc(getSetting('site_logo', false)); ?>">
		<meta property="og:description" content="">
		<link type="image/x-icon" href="<?php echo getMediaSrc(getSetting('site_icon', false)); ?>" rel="icon">
		<?php headerScripts('button', array(array('style'))); ?>
	</head>
	<body class="<?php echo bodyClasses(); ?>">
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
		<main class="content" role="main">