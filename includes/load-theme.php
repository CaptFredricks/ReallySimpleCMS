<?php
/**
 * Try to load a front end theme. Default to a basic fallback theme if none are found.
 * @since 2.3.0[a]
 */

// Check whether the themes directory exists and create it if not
if(!file_exists(PATH.THEMES)) mkdir(PATH.THEMES);

// Check whether the themes directory is empty or the current theme is broken
if(isEmptyDir(PATH.THEMES) || !file_exists(trailingSlash(PATH.THEMES).getSetting('theme', false))) {
	// Load the fallback theme
	require_once PATH.INC.'/fallback.php';
} else {
	// Construct the file path for the current theme
	$theme_path = trailingSlash(PATH.THEMES).getSetting('theme', false);
	
	// Check whether the theme has an index file
	if(!file_exists($theme_path.'/index.php')) {
		// Load the fallback theme
		require_once PATH.INC.'/fallback.php';
	} else {
		// Check whether the theme has a functions.php file and include it if so
		if(file_exists($theme_path.'/functions.php')) require_once $theme_path.'/functions.php';
		
		// Include the template loader file
		require_once PATH.INC.'/load-template.php';
	}
}