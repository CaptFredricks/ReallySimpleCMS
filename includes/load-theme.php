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
	// Set the file path for the current theme
	$theme_dir = trailingSlash(PATH.THEMES).getSetting('theme', false);
	
	// Check whether the theme has an index file
	if(!file_exists($theme_dir.'/index.php')) {
		// Load the fallback theme
		require_once PATH.INC.'/fallback.php';
	} else {
		// Check whether the theme has a functions.php file and include it if so
		if(file_exists($theme_dir.'/functions.php')) require_once $theme_dir.'/functions.php';
		
		// Include the theme's index file
		require_once $theme_dir.'/index.php';
	}
}