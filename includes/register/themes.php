<?php
/**
 * Theme registry functions.
 * @since 1.3.15-beta
 *
 * @package ReallySimpleCMS
 *
 * ## FUNCTIONS [9] ##
 * - registerTheme(string $name, array $args): ?array
 * - unregisterTheme(string $name, bool $del_data): bool
 * - runThemesRegister(): void
 * - loadThemeReg(string $theme): void
 * - themeExists(string $name): bool
 * - isBrokenTheme(string $path): bool
 * - isActiveTheme(string $name): bool
 * - registerMenu(string $name, string $slug): void
 * - registerWidget(string $title, string $slug): void
 */

/**
 * Register a theme.
 * @since 1.3.15-beta
 *
 * @param string $name -- The theme's name.
 * @param array $args (optional) -- The args.
 * @return null|array
 */
function registerTheme(string $name, array $args = array()): ?array {
	global $rs_register;
	
	return $rs_register->registerTheme($name, $args);
}

/**
 * Unregister a theme.
 * @since 1.3.15-beta
 *
 * @param string $name -- The theme's name.
 * @param bool $del_data (optional) -- Whether to delete all associated data.
 * @return bool
 */
function unregisterTheme(string $name, bool $del_data = false): bool {
	global $rs_register;
	
	return $rs_register->unregisterTheme($name, $del_data);
}

/**
 * Register all available themes.
 * @since 1.3.15-beta
 */
function runThemesRegister(): void {
	global $rs_register;
	
	// Default themes
	$default_themes = $rs_register::DEFAULT_THEMES;
	
	foreach($default_themes as $theme) loadThemeReg($theme);
	
	// Custom themes
	$custom_themes = array_diff(scandir(PATH . THEMES), array_merge($default_themes, array('.', '..', 'backups')));
	
	foreach($custom_themes as $theme) loadThemeReg($theme);
}

/**
 * Try to load a theme's register file.
 * @since 1.3.15-beta
 *
 * @param string $name -- The theme's name.
 */
function loadThemeReg(string $name): void {
	$reg = slash(PATH . THEMES) . slash($name) . $name . '.php';
	
	if(file_exists($reg)) requireFile($reg);
}

/**
 * Check whether a theme exists.
 * @since 2.3.1-alpha
 *
 * @param string $name -- The theme's name.
 * @return bool
 */
function themeExists(string $name): bool {
	global $rs_themes;
	
	$installed = array_diff(scandir(PATH . THEMES), array('.', '..', 'backups'));
	
	return !empty($rs_themes) && array_key_exists($name, $rs_themes) && in_array($name, $installed, true);
}

/**
 * Check whether a theme is broken.
 * @since 1.3.9-beta
 *
 * @param string $name -- The theme's name.
 * @param string $path -- The theme's file path.
 * @return bool
 */
function isBrokenTheme(string $name, string $path): bool {
	return !file_exists(slash($path) . $name . '.php') || !file_exists($path . '/index.php');
}

/**
 * Check whether a specified theme is active.
 * @since 2.3.1-alpha
 *
 * @param string $name -- The theme's name.
 * @return bool
 */
function isActiveTheme(string $name): bool {
	return $name === getSetting('active_theme');
}

/**
 * Register a menu.
 * @since 1.0.0-beta
 *
 * @param string $name -- The menu's name.
 * @param string $slug -- The menu's slug.
 */
function registerMenu(string $name, string $slug): void {
	global $rs_query;
	
	$slug = sanitize($slug);
	
	$menu = $rs_query->selectRow(getTable('t'), '*', array(
		'slug' => $slug,
		'taxonomy' => getTaxonomyId('nav_menu')
	));
	
	if(empty($menu)) {
		$rs_query->insert(getTable('t'), array(
			'name' => $name,
			'slug' => $slug,
			'taxonomy' => getTaxonomyId('nav_menu')
		));
	}
}

/**
 * Register a widget.
 * @since 1.0.0-beta
 *
 * @param string $title -- The widget's title.
 * @param string $slug -- The widget's slug.
 */
function registerWidget(string $title, string $slug): void {
	global $rs_query;
	
	$slug = sanitize($slug);
	
	$widget = $rs_query->selectRow(getTable('p'), '*', array(
		'slug' => $slug,
		'type' => 'widget'
	));
	
	if(empty($widget)) {
		$rs_query->insert(getTable('p'), array(
			'title' => $title,
			'created' => 'NOW()',
			'content' => '',
			'status' => 'active',
			'slug' => $slug,
			'type' => 'widget'
		));
	}
}