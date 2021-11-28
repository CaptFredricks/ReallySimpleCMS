<?php
/**
 * Named constants for the CMS.
 * @since 2.3.0[a]
 */

/*------------------------------------*\
    VERSIONS
\*------------------------------------*/

// Minimum supported PHP version
define('PHP_MINIMUM', '7.3');

// Recommended PHP version
define('PHP_RECOMMENDED', '7.4');

// Current CMS version
define('VERSION', '1.2.7');

// Current jQuery version
define('JQUERY_VERSION', '3.6.0');

// Current Font Awesome icons version
define('ICONS_VERSION', '5.15.4');

/*------------------------------------*\
    DIRECTORIES
\*------------------------------------*/

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

/*------------------------------------*\
    MISCELLANEOUS
\*------------------------------------*/

// The name of the CMS
define('CMS_NAME', 'ReallySimpleCMS');