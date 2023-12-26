<?php
/**
 * Try to load a front end theme. Default to a basic fallback theme if none are found.
 * @since 2.3.0-alpha
 *
 * @package ReallySimpleCMS
 */

// Check whether the themes directory exists and create it if not
if(!file_exists(PATH . THEMES)) mkdir(PATH . THEMES);

// Check whether the themes directory is empty or the current theme is broken
if(isEmptyDir(PATH . THEMES) || !file_exists(slash(PATH . THEMES) . getSetting('theme'))) {
	$theme_path = null;
} else {
	$theme_path = slash(PATH . THEMES) . getSetting('theme');
	
	if(file_exists($theme_path . '/functions.php')) require_once $theme_path . '/functions.php';
	
	// Set a default theme version if none is set in the theme's functions.php
	if(!defined('THEME_VERSION')) define('THEME_VERSION', CMS_VERSION);
}