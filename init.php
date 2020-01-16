<?php
/**
 * Initialize the CMS.
 * @since 1.3.0[a]
 */

// Minimum supported PHP version
if(!defined('PHP')) define('PHP', '7.3');

// Check whether the server is running the required PHP version
if(phpversion() < PHP)
	exit('<p>The minimum version of PHP that is supported by ReallySimpleCMS is '.PHP.'; your server is running on '.phpversion().'. Please upgrade to the minimum required version or higher to use this CMS.</p>');

// Absolute path to the root directory
if(!defined('PATH')) define('PATH', __DIR__);

// Path to the includes directory
if(!defined('INC')) define('INC', '/includes');

// Path to the admin directory
if(!defined('ADMIN')) define('ADMIN', '/admin');

// Path to the content directory
if(!defined('CONT')) define('CONT', '/content');

// Try to initialize the CMS or run setup if the config file doesn't exist
if(file_exists(PATH.'/config.php')) {
	// Include debugging functions
	require_once PATH.INC.'/debug.php';
	
	// Include the database configuration
	require_once PATH.'/config.php';
	
	// Include the Query class
	require_once PATH.INC.'/class-query.php';
	
	// Create a Query object
	$rs_query = new Query;
	
	// Check that the database has been installed and there are no problems
	if($rs_query->conn_status) {
		// Include the database schema
		require_once PATH.INC.'/schema.php';
		
		// Fetch the database schema tables
		$tables = dbSchema();
		
		// Get a list of tables in the database
		$data = $rs_query->showTables();
		
		// Check whether there are the proper number of tables in the database
		if(empty($data) || count($data) < count($tables)) {
			// Redirect to the installation page
			header('Location: '.ADMIN.'/install.php');
			exit;
		}
	}
	
	// Include global functions
	require_once PATH.INC.'/globals.php';
	
	// Path to the stylesheets directory
	if(!defined('STYLES')) define('STYLES', INC.'/css');
	
	// Path to the scripts directory
	if(!defined('SCRIPTS')) define('SCRIPTS', INC.'/js');
	
	// Path to the uploads directory
	if(!defined('UPLOADS')) define('UPLOADS', CONT.'/uploads');
} else {
	// Redirect to the setup page
	header('Location: '.ADMIN.'/setup.php');
	exit;
}