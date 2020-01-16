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
} else {
	// Redirect to the setup page
	header('Location: '.ADMIN.'/setup.php');
	exit;
}