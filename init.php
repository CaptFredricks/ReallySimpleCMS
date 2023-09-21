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
		if(file_exists(PATH . INC . '/update.php')) require_once PATH . INC . '/update.php';
		
		if(!isAdmin() && !isLogin() && !is404()) {
			// Site-wide functions
			require_once FUNC;
			
			// Initialize the theme
			require_once PATH . INC . '/theme-functions.php';
			require_once PATH . INC . '/load-theme.php';
			
			// Check whether the current post is a preview and the id is valid
			if(isset($_GET['preview']) && $_GET['preview'] === 'true' && isset($_GET['id']) && $_GET['id'] > 0) {
				$rs_post = new Post;
			} else {
				// Fetch the URI
				$raw_uri = $_SERVER['REQUEST_URI'];
				
				// Check whether the current page is the home page
				if($raw_uri === '/' || str_starts_with($raw_uri, '/?')) {
					$home_page = $rs_query->selectField('settings', 'value', array('name' => 'home_page'));
					$slug = $rs_query->selectField('posts', 'slug', array('id' => $home_page));
				} else {
					// Create an array from the post's URI
					$uri = explode('/', $raw_uri);
					
					// Filter out any empty array values
					$uri = array_filter($uri);
					
					// Check whether the last element of the array is the slug
					if(str_starts_with(end($uri), '?')) {
						// Pop the query string off the end of the array
						array_pop($uri);
					}
					
					// Fetch the slug from the URI array
					$slug = array_pop($uri);
				}
				
				// Check whether the current page is a post or a term
				if($rs_query->selectRow('posts', 'COUNT(slug)', array('slug' => $slug)) > 0) {
					$rs_post = new Post;
				} elseif($rs_query->selectRow('terms', 'COUNT(slug)', array('slug' => $slug)) > 0) {
					$rs_term = new Term;
				} else {
					// Catastrophic failure, abort
					redirect('/404.php');
					//exit('The CMS has encountered a critical error. Please try again later.');
				}
			}
			
			// Initialize the sitemaps and page template
			include_once PATH . INC . '/sitemap-index.php';
			require_once PATH . INC . '/load-template.php';
		}
	}
}