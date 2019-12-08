<?php
// Include the initialization file
require_once dirname(__DIR__).'/init.php';

// Include functions
require_once PATH.ADMIN.INC.'/functions.php';

// Start output buffering
ob_start();

// Check whether the session cookie is set and the user's session is valid
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session'])) {
	// Fetch the user's data
	$session = getOnlineUser($_COOKIE['session']);
} else {
	// Redirect to the login page
	redirect('../login.php');
}

// Fetch the current page
$current_page = getCurrentPage();
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo getPageTitle(); ?> &rtrif; <?php getSetting('site_title'); ?> &mdash; ReallySimpleCMS</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<meta name="theme-color" content="#e0e0e0">
		<link type="image/x-icon" href="<?php echo getMedia(getSetting('site_icon', false)); ?>" rel="icon">
		<?php adminHeaderScripts(); ?>
	</head>
	<body class="<?php echo $current_page; ?>">
		<header id="admin-header">
			<a id="site-title" href="/">
				<?php
				// Check whether a site logo has been set
				if(!empty(getSetting('site_logo', false))) {
					// Display the site logo
					?>
					<img src="<?php echo getMedia(getSetting('site_logo', false)); ?>" height="20">
					<span><?php getSetting('site_title'); ?></span>
					<?php
				} else {
					// Display the site title
					getSetting('site_title');
				}
				?>
			</a>
			<div class="user-dropdown">
				<span>Welcome, <?php echo $session['username']; ?></span>
				<img class="avatar" src="<?php echo !empty($session['avatar']) ? trailingSlash(UPLOADS).$session['avatar'] : '//:0'; ?>" width="20" height="20">
				<ul class="user-dropdown-menu">
					<img class="avatar-large" src="<?php echo !empty($session['avatar']) ? trailingSlash(UPLOADS).$session['avatar'] : '//:0'; ?>" width="100" height="100">
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
				adminNavMenuItem(array('id'=>'media'), array(array('link'=>'media.php', 'caption'=>'List Media'), array('id'=>'media-upload', 'link'=>'media.php?action=upload', 'caption'=>'Upload Media')), 'images');
				adminNavMenuItem(array('id'=>'customization'), array(array('id'=>'menus', 'link'=>'menus.php', 'caption'=>'List Menus'), array('id'=>'widgets', 'link'=>'widgets.php', 'caption'=>'List Widgets')), 'palette');
				adminNavMenuItem(array('id'=>'users'), array(array('link'=>'users.php', 'caption'=>'List Users'), array('id'=>'users-create', 'link'=>'users.php?action=create', 'caption'=>'Create User'), array('id'=>'profile', 'link'=>'profile.php', 'caption'=>'Your Profile')), 'users');
				adminNavMenuItem(array('id'=>'settings'), array(array('link'=>'settings.php', 'caption'=>'General'), array('id'=>'design', 'link'=>'settings.php?page=design', 'caption'=>'Design'), array('id'=>'user-roles', 'link'=>'settings.php?page=user_roles', 'caption'=>'User Roles')), 'cogs');
				?>
			</ul>
		</nav>
		<noscript class="notice-nojs">Warning! Your browser either does not support or is set to disable <a href="https://www.w3schools.com/js/default.asp" target="_blank">JavaScript</a>. Some features may not work as expected.</noscript>