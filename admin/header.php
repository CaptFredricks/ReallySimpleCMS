<?php
// Load initialization files
require_once dirname(__DIR__).'/init.php';

// Include functions
require_once PATH.ADMIN.INC.'/functions.php';
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php getSetting('site_title'); ?> &rtrif; Admin Dashboard</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<meta name="theme-color" content="#e0e0e0">
		<?php getStylesheet('buttons.css'); ?>
		<?php getAdminStylesheet('style.css'); ?>
	</head>
	<body>
		<header id="admin-header">
			<a id="site-title" href="/"><?php getSetting('site_title'); ?></a>
		</header>
		<div id="admin-nav-wrap"></div>
		<nav id="admin-nav">
			<ul class="nav">
				<?php
				adminNavItem('Dashboard', 'index.php');
				adminNavItem('Pages', '', array(array('List Pages', 'Create Page'), array('posts.php?type=page', 'posts.php?type=page&action=create')));
				adminNavItem('Posts', '', array(array('List Posts', 'Create Post', 'List Categories'), array('posts.php', 'posts.php?action=create', 'categories.php')));
				adminNavItem('Media', '', array(array('List Media', 'Upload Media'), array()));
				adminNavItem('Navigation', '', array(array('', ''), array()));
				adminNavItem('Widgets', '', array(array('List Widgets', 'Create Widget'), array()));
				adminNavItem('Users', '', array(array('List Users', 'Create User', 'Your Profile'), array('users.php', 'users.php?action=create', '')));
				adminNavItem('Settings', 'settings.php');
				?>
			</ul>
		</nav>