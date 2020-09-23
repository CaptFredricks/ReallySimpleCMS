<?php
/**
 * Handle Ajax requests to the server.
 * @since 1.1.0[b]{ss-03}
 */

// Include named constants
require_once __DIR__.'/constants.php';

// Include the debugging functions
require_once PATH.INC.'/debug.php';

// Include the database configuration
require_once PATH.'/config.php';

// Include the Query class
require_once PATH.INC.'/class-query.php';

// Include the global functions
require_once PATH.INC.'/globals.php';

// Create a Query object
$rs_query = new Query;

// Include functions
require_once PATH.INC.'/functions.php';

// Make sure the POST method is being used
if(isset($_POST)) {
	// Check whether a comment upvote has been passed to the server
	if(isset($_POST['type']) && isset($_POST['id']) && isset($_POST['vote'])) {
		// Create a Comment object
		$rs_comment = new Comment;
		
		// Check whether the vote should be increased or decreased
		if(!(int)$_POST['vote'])
			echo $rs_comment->incrementVotes($_POST['id'], $_POST['type']);
		else
			echo $rs_comment->decrementVotes($_POST['id'], $_POST['type']);
	}
}