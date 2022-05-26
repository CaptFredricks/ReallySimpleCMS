<?php
/**
 * Create an error log.
 * @since 1.0.1[a]
 *
 * @param object $exception
 */
function logError($exception): void {
	$timestamp = date('[d-M-Y H:i:s T]', time());
	error_log($timestamp . ' ' . $exception->getMessage() . chr(10), 3, 'error_log');
}