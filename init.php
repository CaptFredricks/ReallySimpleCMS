<?php
/**
 * Initialize the CMS.
 * @since Alpha 1.3.0
 */

// Absolute path to the root directory
if(!defined('PATH')) define('PATH', __DIR__);

// Path to the includes directory
if(!defined('INC')) define('INC', '/includes');

// Path to the admin directory
if(!defined('ADMIN')) define('ADMIN', '/admin');

// Try to initialize the CMS or run setup if config file doesn't exist
if(file_exists(PATH.INC.'/config.php')) {
	// Include debugging
	require_once PATH.INC.'/debug.php';
	
	// Include database configuration
	require_once PATH.INC.'/config.php';
	
	// Include global functions
	require_once PATH.INC.'/globals.php';
	
	// Path to stylesheets directory
	if(!defined('STYLES')) define('STYLES', INC.'/css');
	
	// Path to scripts directory
	if(!defined('SCRIPTS')) define('SCRIPTS', INC.'/js');
	
	// Path to the uploads directory
	if(!defined('UPLOADS')) define('UPLOADS', PATH.'/content/uploads');
} else {
	header('Location: '.ADMIN.'/setup.php');
}