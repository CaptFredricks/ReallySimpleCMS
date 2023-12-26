<?php
/**
 * Handle AJAX requests to the server.
 * @since 1.1.0-beta_snap-03
 *
 * @package ReallySimpleCMS
 */

// Only initialize the base files and functions
define('BASE_INIT', true);

require_once dirname(__DIR__) . '/init.php';
require_once FUNC;

// Fetch the user's session data if they're logged in
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session']))
	$session = getOnlineUser($_COOKIE['session']);

if(isset($_POST)) {
	// Check whether a comment reply has been passed to the server
	if(isset($_POST['data_submit']) && $_POST['data_submit'] === 'reply') {
		$rs_comment = new \Engine\Comment;
		echo $rs_comment->createComment($_POST);
	}
	
	// Check whether a request to edit a comment has been passed to the server
	if(isset($_POST['data_submit']) && $_POST['data_submit'] === 'edit') {
		$rs_comment = new \Engine\Comment;
		$rs_comment->updateComment($_POST);
	}
	
	// Check whether a request to delete a comment has been passed to the server
	if(isset($_POST['data_submit']) && $_POST['data_submit'] === 'delete') {
		$rs_comment = new \Engine\Comment;
		$rs_comment->deleteComment($_POST['id']);
	}
	
	// Check whether a comment vote has been passed to the server
	if(isset($_POST['data_submit']) && $_POST['data_submit'] === 'vote') {
		$rs_comment = new \Engine\Comment;
		
		// Check whether the vote should be increased or decreased
		if(!(int)$_POST['vote'])
			echo $rs_comment->incrementVotes($_POST['id'], $_POST['type']);
		else
			echo $rs_comment->decrementVotes($_POST['id'], $_POST['type']);
	}
	
	// Check whether a request to load more comments or refresh the comment feed has been passed to the server
	if(isset($_POST['data_submit']) && ($_POST['data_submit'] === 'load' || $_POST['data_submit'] === 'refresh')) {
		$rs_post = new \Engine\Post($_POST['post_slug']);
		$rs_comment = new \Engine\Comment($rs_post->getPostId());
		$rs_comment->loadComments($_POST['start'], $_POST['count'] ?? 1);
	}
	
	// Check whether a request to refresh the comment feed has been passed to the server
	if(isset($_POST['data_submit']) && $_POST['data_submit'] === 'checkupdates') {
		$rs_post = new \Engine\Post($_POST['post_slug']);
		$rs_comment = new \Engine\Comment;
		echo $rs_comment->getCommentCount($rs_post->getPostId());
	}
}