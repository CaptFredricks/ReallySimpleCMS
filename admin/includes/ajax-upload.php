<?php
/**
 * Upload to the media library via the upload modal.
 * Uses AJAX to submit data.
 * @since 2.1.6[a]
 */

// Only initialize the base files and functions
define('BASE_INIT', true);

// Initialization file
require_once dirname(dirname(__DIR__)) . '/init.php';

// Admin functions
require_once ADMIN_FUNC;

echo uploadMediaFile($_FILES['media_upload']);