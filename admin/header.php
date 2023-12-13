<?php
/**
 * Admin dashboard header.
 * @since 1.0.2[a]
 */

// Initialization file
require_once dirname(__DIR__) . '/init.php';

// Admin functions
require_once ADMIN_FUNC;

// Site-wide functions
require_once FUNC;

// Theme functions
if(file_exists(slash(PATH . THEMES) . getSetting('theme') . '/functions.php'))
	require_once slash(PATH . THEMES) . getSetting('theme') . '/functions.php';

// Start output buffering
ob_start();

// Fetch the user's session data if they're logged in
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session'])) {
	$session = getOnlineUser($_COOKIE['session']);
} else {
	$login_slug = getSetting('login_slug');
	$redirect = ($_SERVER['REQUEST_URI'] !== '/admin/' ? 'redirect=' . urlencode($_SERVER['PHP_SELF']) : '');
	
	// Redirect them to the login page if their session is invalid
	if(!empty($login_slug))
		redirect('/login.php?secure_login=' . $login_slug . (!empty($redirect) ? '&' . $redirect : ''));
	else
		redirect('/login.php' . (!empty($redirect) ? '?' . $redirect : ''));
}

$current_page = getCurrentPage();
$notices = array();
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo getPageTitle(); ?> &rtrif; <?php putSetting('site_title'); ?> &mdash; <?php echo CMS_NAME; ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<meta name="theme-color" content="#e0e0e0">
		<link type="image/x-icon" href="<?php echo getMediaSrc(getSetting('site_icon')); ?>" rel="icon">
		<?php adminHeaderScripts(); ?>
	</head>
	<body class="<?php echo $current_page; ?>">
		<header id="admin-header">
			<a id="site-title" href="/">
				<i class="fa-solid fa-house-chimney"></i>
				<span><?php putSetting('site_title'); ?></span>
			</a>
			<div class="user-dropdown">
				<span>Welcome, <?php echo $session['display_name']; ?></span>
				<?php
				echo getMedia($session['avatar'], array(
					'class' => 'avatar',
					'width' => 20,
					'height' => 20
				));
				?>
				<ul class="user-dropdown-menu">
					<?php
					echo getMedia($session['avatar'], array(
						'class' => 'avatar-large',
						'width' => 100,
						'height' => 100
					));
					?>
					<li><a href="profile.php">My Profile</a></li>
					<li><a href="../login.php?action=logout">Log Out</a></li>
				</ul>
			</div>
		</header>
		<div id="admin-nav-wrap"></div>
		<nav id="admin-nav-menu">
			<ul class="menu">
				<?php adminNavMenu(); ?>
			</ul>
		</nav>
		<noscript id="no-js" class="header-notice">Warning! Your browser either does not support or is set to disable <a href="https://www.w3schools.com/js/default.asp" target="_blank" rel="noreferrer noopener">JavaScript</a>. Some features may not work as expected.</noscript>
		<?php if(version_compare(PHP_VERSION, PHP_RECOMMENDED, '<')): ?>
			<div id="php-deprecation" class="header-notice">Notice: Your server's PHP version, <?php echo PHP_VERSION; ?>, is below the recommended PHP version, <?php echo PHP_RECOMMENDED; ?>. Consider upgrading to the recommended version.</div>
		<?php endif; ?>
		<div class="wrapper clear">