<?php
/**
 * Handle AJAX requests to the server.
 * @since 1.1.0[b]{ss-03}
 */

// Tell the CMS that it should only initialize the base files and functions
define('BASE_INIT', true);

// Include the initialization file
require_once dirname(__DIR__).'/init.php';

// Include functions
require_once PATH.INC.'/functions.php';

// Check whether the session cookie is set and the user's session is valid
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session'])) {
	// Fetch the user's data
	$session = getOnlineUser($_COOKIE['session']);
}

// Make sure the POST method is being used
if(isset($_POST)) {
	// Check whether a comment reply has been passed to the server
	if(isset($_POST['data_submit']) && $_POST['data_submit'] === 'reply') {
		// Create a Comment object
		$rs_comment = new Comment;
		
		// Submit the comment
		echo $rs_comment->createComment($_POST);
	}
	
	// Check whether a request to edit a comment has been passed to the server
	if(isset($_POST['data_submit']) && $_POST['data_submit'] === 'edit') {
		// Create a Comment object
		$rs_comment = new Comment;
		
		// Update the comment
		$rs_comment->updateComment($_POST);
	}
	
	// Check whether a request to delete a comment has been passed to the server
	if(isset($_POST['data_submit']) && $_POST['data_submit'] === 'delete') {
		// Create a Comment object
		$rs_comment = new Comment;
		
		// Delete the comment
		$rs_comment->deleteComment($_POST['id']);
	}
	
	// Check whether a comment vote has been passed to the server
	if(isset($_POST['data_submit']) && $_POST['data_submit'] === 'vote') {
		// Create a Comment object
		$rs_comment = new Comment;
		
		// Check whether the vote should be increased or decreased
		if(!(int)$_POST['vote'])
			echo $rs_comment->incrementVotes($_POST['id'], $_POST['type']);
		else
			echo $rs_comment->decrementVotes($_POST['id'], $_POST['type']);
	}
	
	// Check whether a request to load more comments or refresh the comment feed has been passed to the server
	if(isset($_POST['data_submit']) && ($_POST['data_submit'] === 'load' || $_POST['data_submit'] === 'refresh')) {
		// Create a Post object
		$rs_post = new Post($_POST['post_slug']);
		
		// Create a Comment object
		$rs_comment = new Comment($rs_post->getPostId());
		
		// Load the comments
		$rs_comment->loadComments($_POST['start'], $_POST['count']);
	}
	
	// Check whether a request to refresh the comment feed has been passed to the server
	if(isset($_POST['data_submit']) && $_POST['data_submit'] === 'checkupdates') {
		// Create a Post object
		$rs_post = new Post($_POST['post_slug']);
		
		// Create a Comment object
		$rs_comment = new Comment;
		
		// Fetch the comment count
		$rs_comment->getCommentCount($rs_post->getPostId());
	}
}