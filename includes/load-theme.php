<?php
/**
 * Try to load a front end theme. Default to a basic fallback theme if none are found.
 * @since 2.3.0-alpha
 *
 * @package ReallySimpleCMS
 */

if(!isEmptyDir(PATH . THEMES) && file_exists(slash(PATH . THEMES) . getSetting('active_theme'))) {
	$rs_theme_path = slash(PATH . THEMES) . getSetting('active_theme');
	$included_files = get_included_files();
	
	if(file_exists($rs_theme_path . '/functions.php') && !in_array($rs_theme_path . '/functions.php', $included_files, true))
		requireFile($rs_theme_path . '/functions.php');
	
	if(!defined('THEME_VERSION')) define('THEME_VERSION', RS_VERSION);
}