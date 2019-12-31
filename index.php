<?php
/**
 * The starting point of the ReallySimpleCMS initialization.
 * @since 1.0.0[a]
 */

// Include the initialization file
require_once __DIR__.'/init.php';

// Include functions
require_once PATH.INC.'/functions.php';

// Fetch the post object
$rs_post = getPost();

// Include the theme's index file
require_once PATH.CONT.'/index.php';