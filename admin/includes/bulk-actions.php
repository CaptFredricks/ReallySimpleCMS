<?php
/**
 * Submit bulk actions via AJAX.
 * @since 1.2.7[b]
 */

// Include named constants
require_once dirname(dirname(__DIR__)).'/includes/constants.php';

// Current admin page URI
define('ADMIN_URI', $_POST['uri']);

// Include debugging functions
require_once PATH.INC.'/debug.php';

// Include database configuration
require_once PATH.'/config.php';

// Include Query class
require_once PATH.INC.'/class-query.php';

// Create a Query object
$rs_query = new Query;

// Include global functions
require_once PATH.INC.'/globals.php';

// Include admin functions
require_once PATH.ADMIN.INC.'/functions.php';

// Check whether the session cookie is set and the user's session is valid
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session'])) {
	// Fetch the user's data
	$session = getOnlineUser($_COOKIE['session']);
}

switch($_POST['page']) {
	case 'comments':
		// Create a Comment object
		$rs_comment = new Comment;
		
		switch($_POST['action']) {
			case 'approve':
				if(!empty($_POST['selected'])) {
					// Approve all selected comments
					foreach($_POST['selected'] as $id) $rs_comment->approveComment($id);
				}
				break;
			case 'unapprove':
				if(!empty($_POST['selected'])) {
					// Unapprove all selected comments
					foreach($_POST['selected'] as $id) $rs_comment->unapproveComment($id);
				}
				break;
		}
		
		// Display the list comments page
		echo $rs_comment->listComments();
		break;
}