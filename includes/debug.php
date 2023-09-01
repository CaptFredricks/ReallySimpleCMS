<?php
/**
 * Debugging functions.
 * @since 1.0.1[a]
 */

if(!defined('DEBUG_MODE')) define('DEBUG_MODE', false);

// Check whether the CMS is in debug mode
if(DEBUG_MODE === true && !ini_get('display_errors'))
	ini_set('display_errors', 1);
elseif(DEBUG_MODE === false && ini_get('display_errors'))
	ini_set('display_errors', 0);

/**
 * Generate an error log.
 * @since 1.0.1[a]
 *
 * @param object $exception -- The exception.
 */
function logError(object $exception): void {
	$timestamp = date('[d-M-Y H:i:s T]', time());
	error_log($timestamp . ' ' . $exception->getMessage() . chr(10), 3, 'error_log');
}