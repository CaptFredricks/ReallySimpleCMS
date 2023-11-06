<?php
/**
 * Initialize the CMS and load all core files.
 * @since 1.3.0[a]
 */

// Named constants
require_once __DIR__ . '/includes/constants.php';

// Critical functions
require_once CRIT_FUNC;

checkPHPVersion();

// Set up a new config file if it's missing
if(!file_exists(DB_CONFIG)) redirect(ADMIN . '/setup.php');

// Database configuration
require_once DB_CONFIG;

// Debugging functions
require_once DEBUG_FUNC;

// Global functions
require_once GLOBAL_FUNC;

$rs_query = new Query;

if(!$rs_query->conn_status)
	exit('<p>There is a problem with your database connection. Check your <code>config.php</code> file located in the <code>root</code> directory of your installation.</p>');

// Database schema
require_once DB_SCHEMA;

$schema = dbSchema();
$tables = $rs_query->showTables();

// Check whether the database is installed
if(empty($tables)) redirect(ADMIN . '/install.php');

// Ensure all required tables exist
foreach($schema as $key => $value) {
	if(!$rs_query->tableExists($key)) {
		// Create the table
		$rs_query->doQuery($schema[$key]);
		populateTable($key);
	}
}

// Fetch the user's session data if they're logged in
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session']))
	$session = getOnlineUser($_COOKIE['session']);

// Maintenance mode checks
if((defined('MAINT_MODE') && MAINT_MODE) &&
	(!isAdmin() && !isLogin() && !is404()) && !isset($session)) {
		
	require_once PATH . INC . '/maintenance.php';
} else {
	registerDefaultPostTypes();
	registerDefaultTaxonomies();
	
	// Check whether only the base files and functions should be initialized
	if(!defined('BASE_INIT') || (defined('BASE_INIT') && !BASE_INIT)) {
		if(file_exists(PATH . INC . '/update.php') && isset($session))
			require_once PATH . INC . '/update.php';
		
		if(!isAdmin() && !isLogin() && !is404()) {
			// Site-wide functions
			require_once FUNC;
			
			// Determine the type of page being viewed (e.g., post, term, etc.)
			guessPageType();
			
			// Initialize the theme, sitemaps, and page template
			require_once PATH . INC . '/theme-functions.php';
			require_once PATH . INC . '/load-theme.php';
			include_once PATH . INC . '/sitemap-index.php';
			require_once PATH . INC . '/load-template.php';
		}
	}
}