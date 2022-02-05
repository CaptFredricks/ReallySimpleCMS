<?php
/**
 * Submit bulk actions via AJAX.
 * @since 1.2.7[b]
 */

// Tell the CMS that it should only initialize the base files and functions
define('BASE_INIT', true);

// Include the initialization file
require_once dirname(dirname(__DIR__)).'/init.php';

// Current admin page URI
define('ADMIN_URI', $_POST['uri']);

// Include admin functions
require_once PATH.ADMIN.INC.'/functions.php';

// Check whether the session cookie is set and the user's session is valid
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session'])) {
	// Fetch the user's data
	$session = getOnlineUser($_COOKIE['session']);
}

// Create empty variables to hold the current post type or taxonomy
$post_type = $type = '';
$taxonomy = $tax = '';

// Loop through the registered post types
foreach($post_types as $key => $value) {
	// If the post type is 'widget', skip to the next type
	if($key === 'widget') continue;
	
	// Check whether the post type's name matches the current page
	if($value['labels']['name_lowercase'] === $_POST['page']) {
		$post_type = $_POST['page'];
		$type = $key;
	}
}

// Loop through the registered taxonomies
foreach($taxonomies as $key => $value) {
	// Check whether the taxonomy's name matches the current page
	if($value['labels']['name_lowercase'] === $_POST['page']) {
		$taxonomy = $_POST['page'];
		$tax = $key;
	}
}

switch($_POST['page']) {
	case $post_type:
		// Create a Post object
		$rs_post = new Post(0, $post_types[$type]);
		
		// Check whether at least one record has been selected
		if(!empty($_POST['selected'])) {
			// Update the status of all selected posts
			foreach($_POST['selected'] as $id) $rs_post->updatePostStatus($_POST['action'], $id);
		}
		
		// Display the "List Posts" page
		echo $rs_post->listPosts();
		break;
	case 'comments':
		// Create a Comment object
		$rs_comment = new Comment;
		
		// Check whether at least one record has been selected
		if(!empty($_POST['selected'])) {
			// Update the status of all selected comments
			foreach($_POST['selected'] as $id) $rs_comment->updateCommentStatus($_POST['action'], $id);
		}
		
		// Display the "List Comments" page
		echo $rs_comment->listComments();
		break;
	case 'widgets':
		// Create a Widget object
		$rs_widget = new Widget;
		
		// Check whether at least one record has been selected
		if(!empty($_POST['selected'])) {
			// Update the status of all selected widgets
			foreach($_POST['selected'] as $id) $rs_widget->updateWidgetStatus($_POST['action'], $id);
		}
		
		// Display the "List Widgets" page
		echo $rs_widget->listWidgets();
		break;
}