<?php
/**
 * Named constants for the CMS.
 * @since 2.3.0[a]
 */

/*------------------------------------*\
    VERSIONS
\*------------------------------------*/

// Minimum supported PHP version
define('PHP_MINIMUM', '8.0');

// Recommended PHP version
define('PHP_RECOMMENDED', '8.1');

// Current CMS version
define('CMS_VERSION', '1.3.11');

// Current jQuery version
define('JQUERY_VERSION', '3.6.3');

// Current Font Awesome icons version
define('ICONS_VERSION', '6.2.1');

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
define('STYLES', INC . '/css');

// Path to the scripts directory
define('SCRIPTS', INC . '/js');

// Path to the themes directory
define('THEMES', CONT . '/themes');

// Path to the uploads directory
define('UPLOADS', CONT . '/uploads');

/*------------------------------------*\
    CORE FILES
\*------------------------------------*/

// Path to the database configuration file
define('DB_CONFIG', PATH . '/config.php');

// Path to the database schema file
define('DB_SCHEMA', PATH . INC . '/schema.php');

// Path to the Query class
define('QUERY_CLASS', PATH . INC . '/class-query.php');

// Path to the primary functions file
define('FUNC', PATH . INC . '/functions.php');

// Path to the debugging functions file
define('DEBUG_FUNC', PATH . INC . '/debug.php');

// Path to the critical functions file
define('CRIT_FUNC', PATH . INC . '/critical-functions.php');

// Path to the global functions file
define('GLOBAL_FUNC', PATH . INC . '/global-functions.php');

// Path to the admin functions file
define('ADMIN_FUNC', PATH . ADMIN . INC . '/functions.php');

/*------------------------------------*\
    MISCELLANEOUS
\*------------------------------------*/

// The name of the CMS engine
define('CMS_ENGINE', 'ReallySimpleCMS');
define('CMS_NAME', CMS_ENGINE); // deprecated