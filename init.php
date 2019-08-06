<?php
/**
 * Initialize the CMS.
 * @since 1.3.0[a]
 */

// Absolute path to the root directory
if(!defined('PATH')) define('PATH', __DIR__);

// Path to the includes directory
if(!defined('INC')) define('INC', '/includes');

// Path to the admin directory
if(!defined('ADMIN')) define('ADMIN', '/admin');

// Path to the content directory
if(!defined('CONT')) define('CONT', '/content');

// Try to initialize the CMS or run setup if config file doesn't exist
if(file_exists(PATH.INC.'/config.php')) {
	// Include debugging
	require_once PATH.INC.'/debug.php';
	
	// Include database configuration
	require_once PATH.INC.'/config.php';
	
	// Include Query class
	require_once PATH.INC.'/class-query.php';
	
	// Create a Query object
	$rs_query = new Query;
	
	// Check that the database has been installed and there are no problems
	if($rs_query->conn_status) {
		// Get a list of tables in the database
		$data = $rs_query->showTables();
		
		// Redirect to the install page if there are no tables
		if(empty($data)) {
			header('Location: '.ADMIN.'/install.php');
			exit;
		}
	}
	
	// Include global functions
	require_once PATH.INC.'/globals.php';
	
	// Path to stylesheets directory
	if(!defined('STYLES')) define('STYLES', INC.'/css');
	
	// Path to scripts directory
	if(!defined('SCRIPTS')) define('SCRIPTS', INC.'/js');
	
	// Path to the uploads directory
	if(!defined('UPLOADS')) define('UPLOADS', '/content/uploads');
} else {
	// Redirect to the setup page
	header('Location: '.ADMIN.'/setup.php');
}