<?php
/**
 * This file contains polyfills of core PHP functions that were introduced in later versions
 *  than an individual installation might be running on.
 * All functions in this file are subject to be removed once the required PHP version is incremented.
 * @since 1.3.1-beta
 *
 * @package ReallySimpleCMS
 */

// Determine if a string contains a given substring
// Replaces most instances of strpos() !== false
if(!function_exists('str_contains')) {
	// Added in PHP 8.0
	function str_contains(string $haystack, string $needle): bool {
		return !empty($needle) && strpos($haystack, $needle) !== false;
	}
}

// Checks if a string starts with a given substring
if(!function_exists('str_starts_with')) {
	// Added in PHP 8.0
	function str_starts_with(string $haystack, string $needle): bool {
		return strpos($haystack, $needle) === 0;
	}
}

// Checks if a string ends with a given substring
if(!function_exists('str_ends_with')) {
	// Added in PHP 8.0
	function str_ends_with(string $haystack, string $needle): bool {
		$length = strlen($needle);
		return $length > 0 ? substr($haystack, -$length) === $needle : true;
	}
}