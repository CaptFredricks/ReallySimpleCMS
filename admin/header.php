<?php
// Load initialization files
require_once dirname(__DIR__).'/init.php';

// Include functions
require_once PATH.ADMIN.INC.'/functions.php';

// Start the session
session_start();

// Fetch the current page
$current_page = getCurrentPage();
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
		<?php getStylesheet('fa-icons.css', '5.11.2'); ?>
	</head>
	<body class="<?php echo $current_page; ?>">
		<header id="admin-header">
			<a id="site-title" href="/"><?php getSetting('site_title'); ?></a>
			<div class="user-dropdown">
				<span>Welcome, <?php echo $_SESSION['username']; ?></span>
				<ul class="user-dropdown-menu">
					<li><a href="profile.php">My Profile</a></li>
					<li><a href="../login.php?action=logout">Log Out</a></li>
				</ul>
			</div>
		</header>
		<div id="admin-nav-wrap"></div>
		<nav id="admin-nav-menu">
			<ul class="menu">
				<?php
				adminNavMenuItem(array('id'=>'dashboard', 'link'=>'index.php'), array(), 'tachometer-alt');
				adminNavMenuItem(array('id'=>'pages'), array(array('link'=>'posts.php?type=page', 'caption'=>'List Pages'), array('id'=>'pages-create', 'link'=>'posts.php?type=page&action=create', 'caption'=>'Create Page')), array('copy', 'regular'));
				adminNavMenuItem(array('id'=>'posts'), array(array('link'=>'posts.php', 'caption'=>'List Posts'), array('id'=>'posts-create', 'link'=>'posts.php?action=create', 'caption'=>'Create Post'), array('id'=>'categories', 'link'=>'categories.php', 'caption'=>'List Categories')), 'newspaper');
				adminNavMenuItem(array('id'=>'media'), array(array('caption'=>'List Media'), array('id'=>'media-upload', 'caption'=>'Upload Media')), 'images');
				adminNavMenuItem(array('id'=>'customization'), array(array('id'=>'menus', 'link'=>'menus.php', 'caption'=>'List Menus'), array('id'=>'widgets', 'link'=>'widgets.php', 'caption'=>'List Widgets')), 'palette');
				adminNavMenuItem(array('id'=>'users'), array(array('link'=>'users.php', 'caption'=>'List Users'), array('id'=>'users-create', 'link'=>'users.php?action=create', 'caption'=>'Create User'), array('id'=>'profile', 'link'=>'profile.php', 'caption'=>'Your Profile')), 'users');
				adminNavMenuItem(array('id'=>'settings'), array(array('link'=>'settings.php', 'caption'=>'General'), array('id'=>'user-roles', 'link'=>'settings.php?page=user_roles', 'caption'=>'User Roles')), 'cogs');
				?>
			</ul>
		</nav>
		<noscript class="notice-nojs">Warning! Your browser either does not support or is set to disable <a href="https://www.w3schools.com/js/default.asp" target="_blank">JavaScript</a>. Some features may not work as expected.</noscript>