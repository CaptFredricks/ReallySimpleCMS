<?php
/**
 * Named constants for the CMS.
 * @since 2.3.0[a]
 */

// Minimum supported PHP version
if(!defined('PHP')) define('PHP', '7.3');

// Absolute path to the root directory
if(!defined('PATH')) define('PATH', dirname(__DIR__));

// Path to the admin directory
if(!defined('ADMIN')) define('ADMIN', '/admin');

// Path to the includes directory
if(!defined('INC')) define('INC', '/includes');

// Path to the content directory
if(!defined('CONT')) define('CONT', '/content');

// Path to the stylesheets directory
if(!defined('STYLES')) define('STYLES', INC.'/css');

// Path to the scripts directory
if(!defined('SCRIPTS')) define('SCRIPTS', INC.'/js');

// Path to the themes directory
if(!defined('THEMES')) define('THEMES', CONT.'/themes');

// Path to the uploads directory
if(!defined('UPLOADS')) define('UPLOADS', CONT.'/uploads');