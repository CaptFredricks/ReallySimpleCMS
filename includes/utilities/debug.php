<?php
/**
 * Debugging functions.
 * @since 1.0.1-alpha
 *
 * @package ReallySimpleCMS
 */

requireFile(PATH . UTILS . '/backward-compat.php'); // Backward compatibility changes

// Turn debugging off unless pre-defined to prevent accidental display of errors
if(!defined('DEBUG_MODE')) define('DEBUG_MODE', false);

// Check whether the system is in debug mode
if(DEBUG_MODE === true && !ini_get('display_errors'))
	ini_set('display_errors', 1);
elseif(DEBUG_MODE === false && ini_get('display_errors'))
	ini_set('display_errors', 0);

/**
 * Generate any missing content directories.
 * @since 1.3.15-beta
 */
function checkContentDir() {
	$dirs = array(MODULES, THEMES, ADMIN_THEMES, UPLOADS);
	
	foreach($dirs as $dir)
		if(!file_exists(PATH . $dir)) mkdir(PATH . $dir);
}

/**
 * Display a deprecation notice.
 * @since 1.3.12-beta
 */
function deprecated(): void {
	global $rs_error;
	
	$rs_error->generateDeprecation();
}