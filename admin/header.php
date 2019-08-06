<?php
// Load initialization files
require_once dirname(__DIR__).'/init.php';

// Include functions
require_once PATH.ADMIN.INC.'/functions.php';

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
	</head>
	<body class="<?php echo $current_page; ?>">
		<header id="admin-header">
			<a id="site-title" href="/"><?php getSetting('site_title'); ?></a>
		</header>
		<div id="admin-nav-wrap"></div>
		<nav id="admin-nav-menu">
			<ul class="menu">
				<?php
				adminNavMenuItem('dashboard', 'index.php');
				adminNavMenuItem('pages', '', array(array('List Pages', 'Create Page'), array('posts.php?type=page', 'posts.php?type=page&action=create')));
				adminNavMenuItem('posts', '', array(array('List Posts', 'Create Post', 'List Categories'), array('posts.php', 'posts.php?action=create', 'categories.php')));
				adminNavMenuItem('media', '', array(array('List Media', 'Upload Media'), array()));
				adminNavMenuItem('navigation', '', array(array('', ''), array()));
				adminNavMenuItem('widgets', '', array(array('List Widgets', 'Create Widget'), array()));
				adminNavMenuItem('users', '', array(array('List Users', 'Create User', 'Your Profile'), array('users.php', 'users.php?action=create', '')));
				adminNavMenuItem('settings', 'settings.php');
				?>
			</ul>
		</nav>