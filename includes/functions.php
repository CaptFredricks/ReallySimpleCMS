<?php
/**
 * Front end functions.
 * @since 1.0.0[a]
 */

// Autoload classes
spl_autoload_register(function($class_name) {
	require_once 'class-'.strtolower($class_name).'.php';
});

//$rs_post = new Post;

/**
 * Include the theme's header file.
 * @since 1.5.5[a]
 *
 * @return null
 */
function getHeader() {
	// Include header with full path
	include_once PATH.CONT.'/header.php';
}

/**
 * Include the theme's footer file.
 * @since 1.5.5[a]
 *
 * @return null
 */
function getFooter() {
	// Include footer with full path
	include_once PATH.CONT.'/footer.php';
}