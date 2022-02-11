<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php pageTitle(); ?></title>
		<?php metaTags(); ?>
		<?php headerScripts('button', array(array('style'))); ?>
	</head>
	<body class="<?php echo bodyClasses(); ?>">
		<header class="header">
			<div class="top-bar">
				<div class="site-logo">
					<a href="/">
						<?php echo getMedia(getSetting('site_logo')); ?>
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