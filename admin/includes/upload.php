<?php
/**
 * Upload media to the media library via the upload modal.
 * @since 2.1.6[a]
 */

// Tell the CMS that it should only initialize the base files and functions
define('BASE_INIT', true);

// Include the initialization file
require_once dirname(dirname(__DIR__)).'/init.php';

// Include admin functions
require_once ADMIN_FUNC;

// Upload the media
echo uploadMediaFile($_FILES['media_upload']);