<?php
/**
 * Taxonomy registry functions.
 * @since 1.3.15-beta
 *
 * @package ReallySimpleCMS
 *
 * ## FUNCTIONS [5] ##
 * - registerTaxonomy(string $name, string $post_type, array $args): ?array
 * - unregisterTaxonomy(string $name, bool $del_terms): bool
 * - runTaxonomiesRegister(): void
 * - taxonomyExists(string $name): bool
 * - getTaxonomyId(string $name): int
 */

/**
 * Register a taxonomy.
 * @since 1.3.15-beta
 *
 * @param string $name -- The taxonomy's name.
 * @param string $post_type -- The associated post type.
 * @param array $args (optional) -- The args.
 * @return null|array
 */
function registerTaxonomy(string $name, string $post_type, array $args = array()): ?array {
	global $rs_register;
	
	return $rs_register->registerTaxonomy($name, $post_type, $args);
}

/**
 * Unregister a taxonomy.
 * @since 1.3.15-beta
 *
 * @param string $name -- The taxonomy's name.
 * @param bool $del_terms -- Whether to delete all term data from the database.
 * @return bool
 */
function unregisterTaxonomy(string $name, bool $del_terms = false): bool {
	global $rs_register;
	
	return $rs_register->unregisterTaxonomy($name, $del_terms);
}

/**
 * Register all available taxonomies.
 * @since 1.0.4-beta
 */
function runTaxonomiesRegister(): void {
	// Category
	registerTaxonomy('category', 'post', array(
		'actions' => array('_defaults_'),
		'menu_item' => array(
			'link' => 'categories.php'
		),
		'menu_link' => 'categories.php', # deprecated
		'default_term' => array(
			'name' => 'Uncategorized',
			'slug' => 'uncategorized'
		)
	));
	
	// Nav_menu
	registerTaxonomy('nav_menu', '', array(
		'labels' => array(
			'name' => 'Menus',
			'name_lowercase' => 'menus',
			'name_singular' => 'Menu',
			'list_items' => 'List Menus',
			'create_item' => 'Create Menu',
			'edit_item' => 'Edit Menu'
		),
		'public' => false,
		'create_privileges' => false,
		'menu_item' => array(
			'link' => 'menus.php'
		),
		'menu_link' => 'menus.php' # deprecated
	));
}

/**
 * Check whether a taxonomy exists.
 * @since 1.0.5-beta
 *
 * @param string $name -- The taxonomy's name.
 * @return bool
 */
function taxonomyExists(string $name): bool {
	global $rs_taxonomies;
	
	return !empty($rs_taxonomies) && array_key_exists($name, $rs_taxonomies);
}

/**
 * Fetch a taxonomy's id.
 * @since 1.5.0-alpha
 *
 * @param string $name -- The taxonomy's name.
 * @return int
 */
function getTaxonomyId(string $name): int {
	global $rs_query;
	
	$name = sanitize($name);
	
	return (int)$rs_query->selectField(getTable('ta'), 'id', array(
		'name' => $name
	)) ?? 0;
}