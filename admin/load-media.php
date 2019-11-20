<?php
// Include the initialization file
require_once dirname(__DIR__).'/init.php';

// Include admin functions
require_once PATH.ADMIN.INC.'/functions.php';

// Fetch the media's type
$media_type = $_GET['media_type'] ?? 'all';

// Check whether the media type is 'image'
if($media_type === 'image') {
	// Load only images from the media library
	loadMedia(true);
} else {
	// Load the full media library
	loadMedia();
}