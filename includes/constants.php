<?php
/**
 * Named constants for the CMS.
 * @since 2.3.0[a]
 */

// Minimum supported PHP version
define('PHP', '7.3');

// Current CMS version
define('VERSION', '1.2.0.3');

// Absolute path to the root directory
define('PATH', dirname(__DIR__));

// Path to the admin directory
define('ADMIN', '/admin');

// Path to the includes directory
define('INC', '/includes');

// Path to the content directory
define('CONT', '/content');

// Path to the stylesheets directory
define('STYLES', INC.'/css');

// Path to the scripts directory
define('SCRIPTS', INC.'/js');

// Path to the themes directory
define('THEMES', CONT.'/themes');

// Path to the uploads directory
define('UPLOADS', CONT.'/uploads');