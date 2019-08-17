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
		<?php getStylesheet('buttons.css', VERSION); ?>
		<?php getAdminStylesheet('style.css', VERSION); ?>
	</head>
	<body class="<?php echo $current_page; ?>">
		<header id="admin-header">
			<a id="site-title" href="/"><?php getSetting('site_title'); ?></a>
		</header>
		<div id="admin-nav-wrap"></div>
		<nav id="admin-nav-menu">
			<ul class="menu">
				<?php
				// Construct the admin nav menu
				adminNavMenuItem('dashboard', 'index.php');
				adminNavMenuItem('pages', '', array(array('caption'=>'List Pages', 'link'=>'posts.php?type=page'), array('caption'=>'Create Page', 'link'=>'posts.php?type=page&action=create')));
				adminNavMenuItem('posts', '', array(array('caption'=>'List Posts', 'link'=>'posts.php'), array('caption'=>'Create Post', 'link'=>'posts.php?action=create'), array('caption'=>'List Categories', 'link'=>'categories.php')));
				adminNavMenuItem('media', '', array(array('caption'=>'List Media'), array('caption'=>'Upload Media')));
				adminNavMenuItem('navigation', '', array(array(), array()));
				adminNavMenuItem('widgets', '', array(array('caption'=>'List Widgets'), array('caption'=>'Create Widget')));
				adminNavMenuItem('users', '', array(array('caption'=>'List Users', 'link'=>'users.php'), array('caption'=>'Create User', 'link'=>'users.php?action=create'), array('caption'=>'Your Profile')));
				adminNavMenuItem('settings', 'settings.php');
				?>
			</ul>
		</nav>