<?php
/**
 * Initialize the CMS.
 * @since 1.3.0[a]
 */

// Include named constants
require_once __DIR__.'/includes/constants.php';

// Check whether the server is running the required PHP version
if(phpversion() < PHP)
	exit('<p>The minimum version of PHP that is supported by ReallySimpleCMS is '.PHP.'; your server is running on '.phpversion().'. Please upgrade to the minimum required version or higher to use this CMS.</p>');

// Try to initialize the CMS or run setup if the config file doesn't exist
if(file_exists(PATH.'/config.php')) {
	// Include the debugging functions
	require_once PATH.INC.'/debug.php';
	
	// Include the database configuration
	require_once PATH.'/config.php';
	
	// Include the Query class
	require_once PATH.INC.'/class-query.php';
	
	// Include the global functions
	require_once PATH.INC.'/globals.php';
	
	// Create a Query object
	$rs_query = new Query;
	
	// Check that the database has been installed and there are no problems
	if($rs_query->conn_status) {
		// Include the database schema
		require_once PATH.INC.'/schema.php';
		
		// Fetch the database schema
		$schema = dbSchema();
		
		// Get a list of tables in the database
		$tables = $rs_query->showTables();
		
		// Check whether the database is installed
		if(empty($tables)) {
			// Redirect to the installation page
			header('Location: '.ADMIN.'/install.php');
			exit;
		}
		
		// Loop through the schema
		foreach($schema as $key=>$value) {
			// Check whether the table exists in the database
			if(!$rs_query->tableExists($key)) {
				// Create the table
				$rs_query->doQuery($schema[$key]);
				
				// Populate the table
				populateTable($key);
			}
		}
		
		// Include the sitemap index generator
		include_once PATH.INC.'/sitemap-index.php';
	} else {
		// Terminate execution, because something is very wrong
		exit;
	}
	
	// Register the default post types
	registerDefaultPostTypes();
	
	// Register the default taxonomies
	registerDefaultTaxonomies();
	
	// Check whether only the base files and functions should be initialized
	if(!defined('BASE_INIT') || (defined('BASE_INIT') && !BASE_INIT)) {
		// Check whether an 'update.php' file exists and include it if so
		if(file_exists(PATH.INC.'/update.php')) require_once PATH.INC.'/update.php';
		
		// Check whether the user is viewing the admin dashboard, the log in page, or the 404 not found page
		if(!isAdmin() && !isLogin() && !is404()) {
			// Include functions
			require_once PATH.INC.'/functions.php';
			
			// Include the theme loader file
			require_once PATH.INC.'/load-theme.php';
			
			// Check whether the current post is a preview and the id is valid
			if(isset($_GET['preview']) && $_GET['preview'] === 'true' && isset($_GET['id']) && $_GET['id'] > 0) {
				// Create a Post object
				$rs_post = new Post;
			} else {
				// Fetch the URI
				$raw_uri = $_SERVER['REQUEST_URI'];
				
				// Check whether the current page is the home page
				if($raw_uri === '/' || strpos($raw_uri, '/?') === 0) {
					// Fetch the home page's id from the database
					$home_page = $rs_query->selectField('settings', 'value', array('name'=>'home_page'));
					
					// Fetch the home page's slug from the database
					$slug = $rs_query->selectField('posts', 'slug', array('id'=>$home_page));
				} else {
					// Create an array from the post's URI
					$uri = explode('/', $raw_uri);
					
					// Filter out any empty array values
					$uri = array_filter($uri);
					
					// Check whether the last element of the array is the slug
					if(strpos(end($uri), '?') !== false) {
						// Pop the query string off the end of the array
						array_pop($uri);
					}
					
					// Fetch the slug from the URI array
					$slug = array_pop($uri);
				}
				
				// Check whether the current page is a post or a term
				if($rs_query->selectRow('posts', 'COUNT(slug)', array('slug'=>$slug)) > 0) {
					// Create a Post object
					$rs_post = new Post;
				} elseif($rs_query->selectRow('terms', 'COUNT(slug)', array('slug'=>$slug, 'taxonomy'=>getTaxonomyId('category'))) > 0) {
					// Create Category and Term objects
					$rs_category = $rs_term = new Category;
				} else {
					// Create a Term object
					$rs_term = new Term;
				}
			}

			// Check whether the session cookie is set and the user's session is valid
			if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session'])) {
				// Fetch the user's data
				$session = getOnlineUser($_COOKIE['session']);
			}

			// Include the template loader file
			require_once PATH.INC.'/load-template.php';
		}
	}
} else {
	// Redirect to the setup page
	header('Location: '.ADMIN.'/setup.php');
	exit;
}