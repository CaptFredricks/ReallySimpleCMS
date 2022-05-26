<?php
/**
 * Initialize the CMS.
 * @since 1.3.0[a]
 */

// Include named constants
require_once __DIR__ . '/includes/constants.php';

// Check whether the server is running the required PHP version
if(version_compare(PHP_VERSION, PHP_MINIMUM, '<'))
	exit('<p>The minimum version of PHP that is supported by ' . CMS_NAME . ' is ' . PHP_MINIMUM . '; your server is running on ' . PHP_VERSION . '. Please upgrade to the minimum required version or higher to use this CMS.</p>');

// Check whether the configuration file exists
if(!file_exists(DB_CONFIG)) {
	// Redirect to the setup page
	header('Location: ' . ADMIN . '/setup.php');
	exit;
}

// Include debugging functions
require_once DEBUG_FUNC;

// Include the database configuration
require_once DB_CONFIG;

// Include the Query class
require_once QUERY_CLASS;

// Include global functions
require_once GLOBAL_FUNC;

// Check whether the 'DEBUG_MODE' constant has been defined and define it if not
if(!defined('DEBUG_MODE')) define('DEBUG_MODE', false);

// Check whether the CMS is in debug mode and update the 'display_errors' ini value accordingly
if(DEBUG_MODE === true && !ini_get('display_errors'))
	ini_set('display_errors', 1);
elseif(DEBUG_MODE === false && ini_get('display_errors'))
	ini_set('display_errors', 0);

$rs_query = new Query;

// Check whether the database connection is working
if(!$rs_query->conn_status)
	exit('<p>There is a problem with your database connection. Check your <code>config.php</code> file located in the <code>root</code> directory of your installation.</p>');

// Include the database schema
require_once DB_SCHEMA;

$schema = dbSchema();
$tables = $rs_query->showTables();

// Check whether the database is installed
if(empty($tables)) {
	header('Location: ' . ADMIN . '/install.php');
	exit;
}

foreach($schema as $key => $value) {
	if(!$rs_query->tableExists($key)) {
		// Create the table
		$rs_query->doQuery($schema[$key]);
		populateTable($key);
	}
}

registerDefaultPostTypes();
registerDefaultTaxonomies();

// Check whether only the base files and functions should be initialized
if(!defined('BASE_INIT') || (defined('BASE_INIT') && !BASE_INIT)) {
	if(file_exists(PATH . INC . '/update.php')) require_once PATH . INC . '/update.php';
	
	// Check whether the user is viewing the admin dashboard, the log in page, or the 404 not found page
	if(!isAdmin() && !isLogin() && !is404()) {
		// Include functions
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
		
		// Fetch the user's session data if they're logged in
		if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session']))
			$session = getOnlineUser($_COOKIE['session']);
		
		// Initialize the sitemaps and page template
		include_once PATH . INC . '/sitemap-index.php';
		require_once PATH . INC . '/load-template.php';
	}
}