<?php
/**
 * Upload to the media library via the upload modal. Uses AJAX to submit data.
 * @since 2.1.6-alpha
 *
 * @package ReallySimpleCMS
 */

// Only initialize the base files and functions
define('BASE_INIT', true);

require_once dirname(dirname(__DIR__)) . '/init.php';
requireFile(RS_ADMIN_FUNC);

echo uploadMediaFile($_FILES['media_upload']);