<?php
/**
 * Admin theme registry functions.
 * @since 1.3.15-beta
 *
 * @package ReallySimpleCMS
 *
 * ## FUNCTIONS [6] ##
 * - registerAdminTheme(string $name, array $args): ?array
 * - unregisterAdminTheme(string $name, bool $del_data): bool
 * - runAdminThemesRegister(): void
 * - loadAdminThemeReg(string $theme): void
 * - loadAdminTheme(string $stylesheet, string $version): void
 * - adminThemeExists(string $name): bool
 */

/**
 * Register an admin theme.
 * @since 1.3.15-beta
 *
 * @param string $name -- The theme's name.
 * @param array $args (optional) -- The args.
 * @return null|array
 */
function registerAdminTheme(string $name, array $args = array()): ?array {
	global $rs_ad_register;
	
	return $rs_ad_register->registerAdminTheme($name, $args);
}

/**
 * Unregister an admin theme.
 * @since 1.3.15-beta
 *
 * @param string $name -- The theme's name.
 * @param bool $del_data (optional) -- Whether to delete all associated data.
 * @return bool
 */
function unregisterAdminTheme(string $name, bool $del_data = false): bool {
	global $rs_ad_register;
	
	return $rs_ad_register->unregisterAdminTheme($name, $del_data);
}

/**
 * Register all available admin themes.
 * @since 1.3.15-beta
 */
function runAdminThemesRegister(): void {
	global $rs_ad_register;
	
	// Default admin themes
	$default_themes = $rs_ad_register::DEFAULT_ADMIN_THEMES;
	
	foreach($default_themes as $theme) loadAdminThemeReg($theme);
	
	// Custom admin themes
	$custom_themes = array_diff(scandir(PATH . ADMIN_THEMES), array_merge($default_themes, array('.', '..', 'backups')));
	
	foreach($custom_themes as $theme) loadAdminThemeReg($theme);
}

/**
 * Try to load an admin theme's register file.
 * @since 1.3.15-beta
 *
 * @param string $name -- The theme's name.
 */
function loadAdminThemeReg(string $name): void {
	$reg = slash(PATH . ADMIN_THEMES) . slash($name) . $name . '.php';
	
	if(file_exists($reg)) requireFile($reg);
}

/**
 * Load an admin theme's stylesheet.
 * @since 2.3.1-alpha
 *
 * @param string $path -- The path to the stylesheet.
 * @param string $version (optional) -- The stylesheet's version.
 */
function loadAdminTheme(string $path, string $version = RS_VERSION): void {
	domTagPr('link', array(
		'href' => $path . (!empty($version) ? '?v=' . $version : ''),
		'rel' => 'stylesheet'
	));
}

/**
 * Check whether an admin theme exists.
 * @since 1.3.15-beta
 *
 * @param string $name -- The theme's name.
 * @return bool
 */
function adminThemeExists(string $name): bool {
	global $rs_admin_themes;
	
	return !empty($rs_admin_themes) && array_key_exists($name, $rs_admin_themes);
}