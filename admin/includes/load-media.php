<?php
/**
 * Load the media library in the upload modal.
 * @since 2.1.2[a]
 */

// Only initialize the base files and functions
define('BASE_INIT', true);

// Initialization file
require_once dirname(dirname(__DIR__)) . '/init.php';

// Admin functions
require_once ADMIN_FUNC;

// Fetch the media's type
$media_type = $_GET['media_type'] ?? 'all';

if($media_type === 'image') {
	// Load only images
	loadMedia(true);
} else {
	// Load the full media library
	loadMedia();
}