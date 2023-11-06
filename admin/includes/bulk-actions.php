<?php
/**
 * Submit bulk actions via AJAX.
 * @since 1.2.7[b]
 */

// Only initialize the base files and functions
define('BASE_INIT', true);

// Initialization file
require_once dirname(dirname(__DIR__)) . '/init.php';

define('ADMIN_URI', $_POST['uri']);

// Admin functions
require_once ADMIN_FUNC;

// Site-wide functions
require_once FUNC;

$theme_path = slash(PATH . THEMES) . getSetting('theme');

// Theme functions
if(file_exists($theme_path . '/functions.php')) require_once $theme_path . '/functions.php';

// Fetch the user's session data if they're logged in
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session']))
	$session = getOnlineUser($_COOKIE['session']);

$post_type = $type = '';
$taxonomy = $tax = '';

foreach($post_types as $key => $value) {
	if($key === 'widget') continue;
	
	if($value['labels']['name_lowercase'] === $_POST['page']) {
		$post_type = $_POST['page'];
		$type = $key;
	}
}

foreach($taxonomies as $key => $value) {
	if($value['labels']['name_lowercase'] === $_POST['page']) {
		$taxonomy = $_POST['page'];
		$tax = $key;
	}
}

switch($_POST['page']) {
	case $post_type:
		$rs_post = new Post(0, $post_types[$type]);
		
		// Update all selected posts
		if(!empty($_POST['selected']))
			foreach($_POST['selected'] as $id) $rs_post->updatePostStatus($_POST['action'], $id);
		
		echo $rs_post->listRecords();
		break;
	case 'comments':
		$rs_comment = new Comment;
		
		switch($_POST['action']) {
			case 'delete_spam':
				// Delete all spam comments
				$rs_comment->deleteSpamComments();
				break;
			default:
				// Update all selected comments
				if(!empty($_POST['selected']))
					foreach($_POST['selected'] as $id) $rs_comment->updateCommentStatus($_POST['action'], $id);
		}
		
		echo $rs_comment->listRecords();
		break;
	case 'widgets':
		$rs_widget = new Widget;
		
		// Update all selected widgets
		if(!empty($_POST['selected']))
			foreach($_POST['selected'] as $id) $rs_widget->updateWidgetStatus($_POST['action'], $id);
		
		echo $rs_widget->listRecords();
		break;
	case 'users':
		$rs_user = new User;
		
		// Update all selected users
		if(!empty($_POST['selected']))
			foreach($_POST['selected'] as $id) $rs_user->updateUserRole($_POST['action'], $id);
		
		echo $rs_user->listRecords();
		break;
}