<?php
/**
 * Module registry functions.
 * @since 1.4.0-beta_snap-03
 *
 * @package ReallySimpleCMS
 *
 * ## FUNCTIONS [6] ##
 * - registerModule(string $name, array $args): ?array
 * - unregisterModule(string $name, bool $del_data): bool
 * - runModulesRegister(): void
 * - loadModuleReg(string $name): void
 * - moduleExists(string $name): bool
 * - isActiveModule(string $name): bool
 */

/**
 * Register a module.
 * @since 1.4.0-beta_snap-03
 *
 * @param string $name -- The module's name.
 * @param array $args (optional) -- The args.
 * @return null|array
 */
function registerModule(string $name, array $args = array()): ?array {
	global $rs_register;
	
	return $rs_register->registerModule($name, $args);
}

/**
 * Unregister a module.
 * @since 1.4.0-beta_snap-03
 *
 * @param string $name -- The module's name.
 * @param bool $del_data (optional) -- Whether to delete all associated data.
 * @return bool
 */
function unregisterModule(string $name, bool $del_data = false): bool {
	global $rs_register;
	
	return $rs_register->unregisterModule($name, $del_data);
}

/**
 * Register all available modules.
 * @since 1.4.0-beta_snap-03
 */
function runModulesRegister(): void {
	global $rs_register;
	
	// Required modules
	$required_modules = $rs_register::REQUIRED_MODULES;
	
	foreach($required_modules as $module) loadModuleReg($module);
	
	// Additional modules
	$addtl_modules = array_diff(scandir(PATH . MODULES), array_merge($required_modules, array('.', '..', 'backups')));
	
	foreach($addtl_modules as $module) loadModuleReg($module);
	
	// Activate all required modules (non-essential modules can be activated or deactivated from the dashboard)
	# activateRequiredModules();
}

/**
 * Try to load a module's register file.
 * @since 1.4.0-beta_snap-04
 *
 * @param string $name -- The theme's name.
 */
function loadModuleReg(string $name): void {
	$reg = slash(PATH . MODULES) . slash($name) . $name . '.php';
	
	if(file_exists($reg)) requireFile($reg);
}

/**
 * Check whether a module exists.
 * @since 1.4.0-beta_snap-03
 *
 * @param string $name -- The module's name.
 * @return bool
 */
function moduleExists(string $name): bool {
	global $rs_modules;
	
	$installed = array_diff(scandir(PATH . MODULES), array('.', '..', 'backups'));
	
	return !empty($rs_modules) && array_key_exists($name, $rs_modules) && in_array($name, $installed, true);
}

/**
 * Check whether a specified module is active.
 * @since 1.4.0-beta_snap-04
 *
 * @param string $name -- The module's name.
 * @return bool
 */
function isActiveModule(string $name): bool {
	$active_modules = unserialize(getSetting('active_modules'));
	
	return in_array($name, $active_modules, true);
}