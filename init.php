<?php
/**
 * Initialize the system and load all core files.
 * @since 1.3.0-alpha
 *
 * @package ReallySimpleCMS
 */

/**
 * ## PHASE 1: CRITICAL SETUP ##
 * - constants.php -- System-defined constants.
 * - critical-functions.php [RS_CRIT_FUNC] -- Functions required for the system to run.
 */

require_once __DIR__ . '/includes/constants.php';
require_once RS_CRIT_FUNC;

/**
 * ## PHASE 2: BASE SETUP ##
 * - config.php [RS_CONFIG] -- The database config. Set up the config file if it doesn't exist.
 * - debug.php [RS_DEBUG_FUNC] -- Debugging functions.
 * - functions-global.php [RS_GLOBAL_FUNC] -- Global functions (available to front and back end).
 * - class-query.php [$rs_query] -- The Query class. Interacts with the database.
 * - schema.php [RS_SCHEMA] -- The database schema. Install database if no tables exist.
 * - $rs_session -- The user's session data.
 * - $rs_register/$rs_ad_register -- Registry loaders.
 * - maintenance.php (optional) -- This file is only loaded if the system is in maintenance mode.
 * The following only load if `BASE_INIT` is undefined or false:
 * - update.php -- The system updater.
 */

baseSetup();

/**
 * ## PHASE 3: THEME SETUP ##
 * The following only load if `BASE_INIT` is undefined or false:
 * - functions-front.php [RS_FRONT_FUNC] -- The front end functions file.
 * - functions-theme.php [RS_THEME_FUNC] -- Theme-specific functions.
 * - load-theme.php -- The theme loader.
 * - sitemaps.php -- The sitemap generator.
 * - load-template.php -- The page template loader.
 */

themeSetup();

// Onward!