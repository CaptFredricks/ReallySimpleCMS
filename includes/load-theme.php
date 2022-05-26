<?php
/**
 * Try to load a front end theme. Default to a basic fallback theme if none are found.
 * @since 2.3.0[a]
 */

// Check whether the themes directory exists and create it if not
if(!file_exists(PATH . THEMES)) mkdir(PATH . THEMES);

// Check whether the themes directory is empty or the current theme is broken
if(isEmptyDir(PATH . THEMES) || !file_exists(trailingSlash(PATH . THEMES) . getSetting('theme'))) {
	$theme_path = null;
} else {
	$theme_path = trailingSlash(PATH . THEMES) . getSetting('theme');
	
	if(file_exists($theme_path . '/functions.php')) require_once $theme_path . '/functions.php';
}