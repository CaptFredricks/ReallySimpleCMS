<?php
/**
 * Admin dashboard header.
 * @since 1.0.2-alpha
 *
 * @package ReallySimpleCMS
 */

require_once dirname(__DIR__) . '/init.php';
requireFiles(array(RS_ADMIN_FUNC, RS_FRONT_FUNC));

ob_start();

// Verify that the user is logged in
if(!isset($_COOKIE['session']) || !isValidSession($_COOKIE['session'])) {
	$login_slug = getSetting('login_slug');
	$redirect = ($_SERVER['REQUEST_URI'] !== '/admin/' ? 'redirect=' . urlencode($_SERVER['PHP_SELF']) : '');
	
	// If not, redirect to the login page
	if(!empty($login_slug))
		redirect('/login.php?secure_login=' . $login_slug . (!empty($redirect) ? '&' . $redirect : ''));
	else
		redirect('/login.php' . (!empty($redirect) ? '?' . $redirect : ''));
}

// Current page data for nav menu
$current_page = getCurrentPage();

// All registered notices
$rs_notices = array();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php
		domTagPr('title', array(
			'content' => getPageTitle() . ' ▸ ' . getSetting('site_title') . ' &mdash; ' . RS_ENGINE
		));
		
		domTagPr('meta', array(
			'charset' => 'UTF-8'
		));
		
		domTagPr('meta', array(
			'name' => 'viewport',
			'content' => 'width=device-width, initial-scale=1.0'
		));
		
		domTagPr('meta', array(
			'name' => 'robots',
			'content' => 'noindex, nofollow'
		));
		
		domTagPr('meta', array(
			'name' => 'theme-color',
			'content' => '#e0e0e0'
		));
		
		domTagPr('link', array(
			'type' => 'image/x-icon',
			'href' => getMediaSrc(getSetting('site_icon')),
			'rel' => 'icon'
		));
		
		adminHeaderScripts();
		?>
	</head>
	<body class="<?php echo $current_page; ?>">
		<header id="admin-header">
			<?php
			// Site title
			domTagPr('a', array(
				'id' => 'site-title',
				'href' => '/',
				'content' => domTag('i', array(
					'class' => 'fa-solid fa-house-chimney'
				)) . domTag('span', array(
					'content' => getSetting('site_title')
				))
			));
			?>
			<div class="user-dropdown">
				<?php
				// Display name
				domTagPr('span', array(
					'content' => 'Welcome, ' . $rs_session['display_name']
				));
				
				// Small avatar
				echo getMedia($rs_session['avatar'], array(
					'class' => 'avatar',
					'width' => 20,
					'height' => 20
				));
				
				$user_dropdown = array();
				
				// Large avatar
				$user_dropdown[] = getMedia($rs_session['avatar'], array(
					'class' => 'avatar-large',
					'width' => 100,
					'height' => 100
				));
				
				// User profile
				$user_dropdown[] = domTag('li', array(
					'content' => domTag('a', array(
						'href' => ADMIN . '/profile.php',
						'content' => 'My Profile'
					))
				));
				
				// User stats
				$user_dropdown[] = domTag('li', array(
					'content' => domTag('a', array(
						'href' => ADMIN . '/stats.php',
						'content' => 'My Stats'
					))
				));
				
				// Log out
				$user_dropdown[] = domTag('li', array(
					'content' => domTag('a', array(
						'href' => '../login.php' . getQueryString(array(
							'action' => 'logout'
						)),
						'content' => 'Log Out'
					))
				));
				
				domTagPr('ul', array(
					'class' => 'user-dropdown-menu',
					'content' => implode('', $user_dropdown)
				));
				?>
			</div>
		</header>
		<div id="admin-nav-wrap"></div>
		<nav id="admin-nav-menu">
			<ul class="menu">
				<?php registerAdminMenu(); ?>
			</ul>
		</nav>
		<?php
		// No JavaScript notice
		domTagPr('noscript', array(
			'id' => 'no-js',
			'class' => 'header-notice',
			'content' => 'Warning! Your browser either does not support or is set to disable ' . domTag('a', array(
				'href' => 'https://www.w3schools.com/js/default.asp',
				'target' => '_blank',
				'rel' => 'noreferrer noopener',
				'content' => 'JavaScript'
			)) . '. Some features may not work as expected.'
		));
		
		// PHP deprecation notice
		if(version_compare(PHP_VERSION, PHP_RECOMMENDED, '<')) {
			domTagPr('div', array(
				'id' => 'php-deprecation',
				'class' => 'header-notice',
				'content' => domTag('strong', array(
					'content' => 'Notice'
				)) . ': Your server\'s PHP version, ' . domTag('strong', array(
					'content' => PHP_VERSION
				)) . ', is below the recommended PHP version, ' . domTag('strong', array(
					'content' => PHP_RECOMMENDED
				)) . '. Consider upgrading to the recommended version.'
			));
		}
		?>
		<div class="wrapper clear">