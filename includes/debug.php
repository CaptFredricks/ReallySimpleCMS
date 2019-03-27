<?php
/**
 * Create an error log.
 * @since Alpha 1.0.1
 *
 * @param object $exception
 * @return null
 */
function logError($exception) {
	$timestamp = date('[d-M-Y H:i:s T]', time());
	error_log($timestamp." ".$exception->getMessage()."\n", 3, 'error_log');
}