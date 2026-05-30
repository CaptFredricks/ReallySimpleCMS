<?php
/**
 * Registry for the Carbon theme (default).
 * @since 1.11.1
 *
 * @package ReallySimpleCMS
 * @subpackage Carbon
 */

$theme = 'carbon';

if(function_exists('registerTheme')) {
	registerTheme($theme, array(
		'author' => array(
			'name' => 'Jace Fincham',
			'url' => 'https://jacefincham.com/'
		),
		'version' => '1.11.1'
	));
}

// Backward compat
if(!defined('THEME_VERSION')) {
	global $rs_themes;
	
	define('THEME_VERSION', $rs_themes[$theme]['version']);
}

if(isActiveTheme($theme)) {
	
	/*------------------------------------*\
		REGISTRIES
	\*------------------------------------*/
	
	/**
	 * Register custom post types.
	 * @since 1.0.0-beta
	 */
	// registerPostType($slug, $args);

	/**
	 * Register custom taxonomies.
	 * @since 1.0.1-beta
	 */
	// registerTaxonomy($name, $post_type, $args);

	/**
	 * Register theme menus.
	 * @since 1.0.0-beta
	 */
	registerMenu('Main Menu', 'main-menu');
	registerMenu('Footer Menu', 'footer-menu');

	/**
	 * Register theme widgets.
	 * @since 1.0.0-beta
	 */
	registerWidget('Social Media', 'social-media');
	registerWidget('Get in contact with us!', 'business-info');
	registerWidget('Copyright', 'copyright');
}