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

/**
 * Create a custom error handler.
 * @since 1.3.12[b]
 *
 * @param int $errno -- The error level.
 * @param string $message -- The error message.
 * @return bool
 */
function errorHandler(int $errno, string $message): bool {
	if(!(error_reporting() & $errno)) return false;
	
	switch($errno) {
		case E_USER_ERROR:
			echo '<p><b>Error</b>: ' . $message . '</p>';
			exit(1);
		case E_USER_WARNING:
			echo '<p><b>Warning</b>: ' . $message . '</p>';
			break;
		case E_USER_NOTICE:
			echo '<p><b>Notice</b>: ' . $message . '</p>';
			break;
	}
	
	return true;
}

/**
 * Output a deprecation notice.
 * @since 1.3.12[b]
 */
function deprecated(): void {
	set_error_handler('errorHandler');
	
	if(DEBUG_MODE === true) {
		$caller = next(debug_backtrace());
		
		trigger_error('the <i>' . $caller['function'] . '</i> function is deprecated in <b>' .
			$caller['file'] . '</b> on line <b>' . $caller['line'] . '</b>');
	}
}