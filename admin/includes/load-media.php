<?php
/**
 * Load the media library in the upload modal.
 * @since 2.1.2[a]
 */

// Tell the CMS that it should only initialize the base files and functions
define('BASE_INIT', true);

// Include the initialization file
require_once dirname(dirname(__DIR__)).'/init.php';

// Include admin functions
require_once ADMIN_FUNC;

// Fetch the media's type
$media_type = $_GET['media_type'] ?? 'all';

// Check whether the media's type is 'image'
if($media_type === 'image') {
	// Load only images from the media library
	loadMedia(true);
} else {
	// Load the full media library
	loadMedia();
}