<?php
// Include the initialization file
require_once dirname(__DIR__).'/init.php';

// Include admin functions
require_once PATH.ADMIN.INC.'/functions.php';

// Upload the media
echo uploadMediaFile($_FILES['media_upload']);