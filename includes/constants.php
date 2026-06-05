<?php
/**
 * Named constants for the system.
 * @since 2.3.0-alpha
 *
 * @package ReallySimpleCMS
 *
 * ## CONSTANTS [33] ##
 * { SOFTWARE VERSIONS [5] }
 * - string PHP_MINIMUM
 * - string PHP_RECOMMENDED
 * - string RS_VERSION
 * - string JQUERY_VERSION
 * - string ICONS_VERSION
 * { DIRECTORIES [16] }
 * - string PATH
 * - string INC
 * - string RES
 * - string ADMIN
 * - string CONT
 * - string SETUP
 * - string AJAX
 * - string MODALS
 * - string REGISTER
 * - string UTILS
 * - string STYLES
 * - string SCRIPTS
 * - string MODULES
 * - string THEMES
 * - string ADMIN_THEMES
 * - string UPLOADS
 * { CORE FILES [8] }
 * - string RS_CONFIG
 * - string RS_SCHEMA
 * - string RS_DEBUG_FUNC
 * - string RS_CRIT_FUNC
 * - string RS_GLOBAL_FUNC
 * - string RS_FRONT_FUNC
 * - string RS_ADMIN_FUNC
 * - string RS_THEME_FUNC
 * { MISCELLANEOUS [4] }
 * - string RS_ENGINE
 * - string RS_DEVELOPER
 * - string RS_PROJ_START
 * - array RS_LEAD_DEV
 */

/*------------------------------------*\
    SOFTWARE VERSIONS
\*------------------------------------*/

/**
 * Minimum supported PHP version.
 * @since 2.1.9-alpha
 *
 * @var string
 */
define('PHP_MINIMUM', '8.1');

/**
 * Recommended PHP version.
 * @since 1.2.5-beta
 *
 * @var string
 */
define('PHP_RECOMMENDED', '8.2');

/**
 * Current system version.
 * @since 1.2.0-alpha
 *
 * @var string
 */
define('RS_VERSION', '1.3.16.4-beta');

/**
 * Current jQuery version (required JavaScript library).
 * @since 1.2.5-beta
 *
 * @var string
 */
define('JQUERY_VERSION', '3.7.1');

/**
 * Current Font Awesome version (required icons library).
 * @since 1.2.5-beta
 *
 * @var string
 */
define('ICONS_VERSION', '6.2.1');

/*------------------------------------*\
    DIRECTORIES
\*------------------------------------*/

/**
 * Absolute path to the root directory.
 * @since 1.3.0-alpha
 *
 * @var string
 */
define('PATH', dirname(__DIR__));

/**
 * Path to the `includes` directory.
 * @since 1.3.0-alpha
 *
 * @var string
 */
define('INC', '/includes');

/**
 * Path to the `resources` directory.
 * @since 1.3.12-beta
 *
 * @var string
 */
define('RES', '/resources');

/**
 * Path to the `admin` directory.
 * @since 1.3.0-alpha
 *
 * @var string
 */
define('ADMIN', '/admin');

/**
 * Path to the `content` directory.
 * @since 1.5.5-alpha
 *
 * @var string
 */
define('CONT', '/content');

/**
 * Path to the `setup` directory.
 * @since 1.3.14-beta
 *
 * @var string
 */
define('SETUP', '/setup');

/**
 * Path to the `ajax` directory.
 * @since 1.3.15-beta
 *
 * @var string
 */
define('AJAX', INC . '/ajax');

/**
 * Path to the `modals` directory.
 * @since 1.3.15-beta
 *
 * @var string
 */
define('MODALS', INC . '/modals');

/**
 * Path to the `register` directory.
 * @since 1.3.15-beta
 *
 * @var string
 */
define('REGISTER', INC . '/register');

/**
 * Path to the `utilities` directory.
 * @since 1.3.16-beta
 *
 * @var string
 */
define('UTILS', INC . '/utilities');

/**
 * Path to the `css` (stylesheets) directory.
 * @since 1.3.0-alpha
 *
 * @var string
 */
define('STYLES', RES . '/css');

/**
 * Path to the `js` (scripts) directory.
 * @since 1.3.0-alpha
 *
 * @var string
 */
define('SCRIPTS', RES . '/js');

/**
 * Path to the `modules` directory.
 * @since 1.3.16-beta
 *
 * @var string
 */
define('MODULES', CONT . '/modules');

/**
 * Path to the `themes` directory.
 * @since 2.3.0-alpha
 *
 * @var string
 */
define('THEMES', CONT . '/themes');

/**
 * Path to the `admin-themes` directory.
 * @since 2.3.1-alpha
 *
 * @var string
 */
define('ADMIN_THEMES', CONT . '/admin-themes');

/**
 * Path to the `uploads` directory.
 * @since 1.3.0-alpha
 *
 * @var string
 */
define('UPLOADS', CONT . '/uploads');

/*------------------------------------*\
    CORE FILES
\*------------------------------------*/

/**
 * Path to the database configuration file.
 * @since 1.3.0-beta
 *
 * @var string
 */
define('RS_CONFIG', PATH . '/config.php');

/**
 * Path to the database schema file.
 * @since 1.3.0-beta
 *
 * @var string
 */
define('RS_SCHEMA', PATH . UTILS . '/schema.php');

/**
 * Path to the debugging functions file.
 * @since 1.3.0-beta
 *
 * @var string
 */
define('RS_DEBUG_FUNC', PATH . UTILS . '/debug.php');

/**
 * Path to the critical functions file.
 * @since 1.3.9-beta
 *
 * @var string
 */
define('RS_CRIT_FUNC', PATH . INC . '/functions-critical.php');

/**
 * Path to the global functions file.
 * @since 1.3.0-beta
 *
 * @var string
 */
define('RS_GLOBAL_FUNC', PATH . INC . '/functions-global.php');

/**
 * Path to the front end functions file.
 * @since 1.3.0-beta
 *
 * @var string
 */
define('RS_FRONT_FUNC', PATH . INC . '/functions-front.php');

/**
 * Path to the admin functions file.
 * @since 1.3.0-beta
 *
 * @var string
 */
define('RS_ADMIN_FUNC', PATH . INC . '/functions-admin.php');

/**
 * Path to the theme functions file.
 * @since 1.3.16-beta
 *
 * @var string
 */
define('RS_THEME_FUNC', PATH . INC . '/functions-theme.php');

/*------------------------------------*\
    MISCELLANEOUS
\*------------------------------------*/

/**
 * The name of the CMS engine.
 * @since 1.2.7-beta
 *
 * @var string
 */
define('RS_ENGINE', 'ReallySimpleCMS');

/**
 * Developer name.
 * @since 1.3.14-beta
 *
 * @var string
 */
define('RS_DEVELOPER', 'ReallySimpleSoftware');

/**
 * Project start date.
 * @since 1.3.14-beta
 *
 * @var string
 */
define('RS_PROJ_START', '2019');

## CREDITS ##

/**
 * Lead developer.
 * @since 1.3.14-beta
 *
 * @var array
 */
define('RS_LEAD_DEV', array(
	'name' => 'Jace Fincham',
	'url' => 'https://jacefincham.com/'
));