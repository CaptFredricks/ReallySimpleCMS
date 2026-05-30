<?php
/**
 * Load the media library in the upload modal.
 * @since 2.1.2-alpha
 *
 * @package ReallySimpleCMS
 */

// Only initialize the base files and functions
define('BASE_INIT', true);

require_once dirname(dirname(__DIR__)) . '/init.php';
requireFile(RS_ADMIN_FUNC);

// Fetch the media's type
$media_type = $_GET['media_type'] ?? 'all';

if($media_type === 'image') {
	// Load only images
	loadMedia(true);
} else {
	// Load the full media library
	loadMedia();
}