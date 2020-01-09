<?php
/**
 * The starting point of the ReallySimpleCMS initialization.
 * @since 1.0.0[a]
 */

// Include the initialization file
require_once __DIR__.'/init.php';

// Include functions
require_once PATH.INC.'/functions.php';

// Create a Post object
$rs_post = new Post;

// Check whether the front end theme has a functions.php file and include it if so
if(file_exists(PATH.CONT.'/functions.php')) require_once PATH.CONT.'/functions.php';

// Check whether the session cookie is set and the user's session is valid
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session'])) {
	// Fetch the user's data
	$session = getOnlineUser($_COOKIE['session']);
}

// Include the theme's index file
require_once PATH.CONT.'/index.php';