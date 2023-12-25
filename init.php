<?php
/**
 * Initialize the system and load all core files.
 * @since 1.3.0[a]
 *
 * LOADING ORDER:
 * - constants.php -- The defined constants.
 * - critical-functions.php [CRIT_FUNC] -- Critical functions required for the system to run.
 * - config.php [DB_CONFIG] -- The database config.
 * - debug.php [DEBUG_FUNC] -- Debugging functions.
 * - global-functions.php [GLOBAL_FUNC] -- Global functions (available to front and back end).
 * - class-query.php -- The Query class. Interacts with the database.
 * - schema.php [DB_SCHEMA] -- The database schema.
 * - maintenance.php (optional) -- This file is only loaded if the system is in maintenance mode.
 * ## The following only load if BASE_INIT is undefined or false. ##
 * - update.php -- The system updater.
 * - functions.php [FUNC] -- The primary functions file.
 * - theme-functions.php -- Theme-specific functions.
 * - load-theme.php -- The theme loader.
 * - sitemap-index.php -- The sitemap index generator.
 * - load-template.php -- The page template loader.
 */

require_once __DIR__ . '/includes/constants.php';
require_once CRIT_FUNC;

checkPHPVersion();

// Set up a new config file if it's missing
if(!file_exists(DB_CONFIG)) redirect(ADMIN . '/setup.php');

require_once DB_CONFIG;
require_once DEBUG_FUNC;
require_once GLOBAL_FUNC;

$rs_query = new Query;
checkDBStatus();

require_once DB_SCHEMA;

$schema = dbSchema();
$tables = $rs_query->showTables();

// Check whether the database is installed
if(empty($tables)) redirect(ADMIN . '/install.php');

// Ensure all required tables exist
foreach($schema as $key => $value) {
	if(!$rs_query->tableExists($key)) {
		$rs_query->doQuery($schema[$key]);
		populateTable($key);
	}
}

// Fetch the user's session data if they're logged in
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session']))
	$session = getOnlineUser($_COOKIE['session']);

// Maintenance mode checks
if((defined('MAINT_MODE') && MAINT_MODE) &&
	(!isAdmin() && !isLogin() && !is404()) && !isset($session)
) {
	// We're in maintenance mode
	require_once PATH . INC . '/maintenance.php';
} else {
	registerDefaultPostTypes();
	registerDefaultTaxonomies();
	
	// Check whether only the base files and functions should be initialized
	if(!defined('BASE_INIT') || (defined('BASE_INIT') && !BASE_INIT)) {
		// Check for software updates
		if(file_exists(PATH . INC . '/update.php') && isset($session))
			require_once PATH . INC . '/update.php';
		
		require_once FUNC;
		
		if(isLogin()) {
			// We're logging in
			handleSecureLogin();
		} elseif(!isAdmin() && !is404()) {
			// Initialize the theme
			require_once PATH . INC . '/theme-functions.php';
			require_once PATH . INC . '/load-theme.php';
			
			// Determine the type of page being viewed (e.g., post, term, etc.)
			// This must fire AFTER theme loads to include custom post types, taxonomies, etc.
			guessPageType();
			
			// Initialize sitemaps and page template
			include_once PATH . INC . '/sitemap-index.php';
			require_once PATH . INC . '/load-template.php';
		}
	}
} // Onward!