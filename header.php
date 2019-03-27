<?php
// Load initialization files
require_once __DIR__.'/init.php';

// Include functions
require_once PATH.INC.'/functions.php';
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php getSetting('site_title'); ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="theme-color" content="#e0e0e0">
		<?php getStylesheet('style.css'); ?>
	</head>
	<body>