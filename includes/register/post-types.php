<?php
/**
 * Post type registry functions.
 * @since 1.3.15-beta
 *
 * @package ReallySimpleCMS
 *
 * ## FUNCTIONS [4] ##
 * - registerPostType(string $name, array $args): ?array
 * - unregisterPostType(string $name, bool $del_posts): bool
 * - runPostTypesRegister(): void
 * - postTypeExists(string $name): bool
 */

/**
 * Register a post type.
 * @since 1.3.15-beta
 *
 * @param string $name -- The post type's name.
 * @param array $args (optional) -- The args.
 * @return null|array
 */
function registerPostType(string $name, array $args = array()): ?array {
	global $rs_register;
	
	return $rs_register->registerPostType($name, $args);
}

/**
 * Unregister a post type.
 * @since 1.3.15-beta
 *
 * @param string $name -- The post type's name.
 * @param bool $del_posts -- Whether to delete all post data from the database.
 * @return bool
 */
function unregisterPostType(string $name, bool $del_posts = false): bool {
	global $rs_register;
	
	return $rs_register->unregisterPostType($name, $del_posts);
}

/**
 * Register all available post types.
 * @since 1.0.1-beta
 */
function runPostTypesRegister(): void {
	// Page
	registerPostType('page', array(
		'actions' => array('_defaults_'),
		'hierarchical' => true,
		'menu_item' => array(
			'icon' => array('copy', 'regular'),
			'submenu' => array('list_items', 'create_item'),
			'index' => 15
		),
		'menu_icon' => array('copy', 'regular') # deprecated
	));
	
	// Post
	registerPostType('post', array(
		'actions' => array('_defaults_'),
		'menu_item' => array(
			'link' => 'posts.php',
			'icon' => 'newspaper',
			'submenu' => array('list_items', 'create_item', 'categories'),
			'index' => 16
		),
		'menu_link' => 'posts.php', # deprecated
		'menu_icon' => 'newspaper', # deprecated
		'comments' => true,
		'taxonomies' => array(
			'category'
		)
	));
	
	// Media
	registerPostType('media', array(
		'labels' => array(
			'create_item' => 'Upload Media',
			'create_button' => 'Upload New',
			'duplicate_item' => 'Replace Media',
			'exclude' => array('bulk_update', 'bulk_delete')
		),
		'actions' => array('upload', 'edit', 'delete', 'view', 'replace'),
		'show_in_nav_menus' => false,
		'menu_item' => array(
			'link' => 'media.php',
			'icon' => 'images',
			'submenu' => array('list_items', 'create_item'),
			'index' => 17
		),
		'menu_link' => 'media.php', # deprecated
		'menu_icon' => 'images' # deprecated
	));
	
	// Nav_menu_item
	registerPostType('nav_menu_item', array(
		'labels' => array(
			'name' => 'Menu Items',
			'name_singular' => 'Menu Item'
		),
		'public' => false,
		'create_privileges' => false
	));
	
	// Widget
	registerPostType('widget', array(
		'actions' => array('create', 'edit', 'delete'),
		'public' => false,
		'menu_item' => array(
			'link' => 'widgets.php',
			'index' => 2
		),
		'menu_link' => 'widgets.php' # deprecated
	));
}

/**
 * Check whether a post type exists.
 * @since 1.0.5-beta
 *
 * @param string $name -- The post type's name.
 * @return bool
 */
function postTypeExists(string $name): bool {
	global $rs_post_types;
	
	return !empty($rs_post_types) && array_key_exists($name, $rs_post_types);
}