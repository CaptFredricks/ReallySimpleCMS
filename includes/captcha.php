<?php
/**
 * Create a captcha for the login form.
 * @since 1.3.5[a]
 */

// Start the session
session_start();

// Generate md5 hash
$hash = md5(rand(0, 999));

// Create a security code
$secure_login = substr($hash, 15, 5);

// Set the session value
$_SESSION['secure_login'] = $secure_login;

// Set the image dimensions
$width = 120;
$height = 30;

// Create the image
$image = imagecreate($width, $height);

// Set the background color
$bg_color = imagecolorallocate($image, 0, 0, 0);

// Create image background
imagefill($image, 0, 0, $bg_color);

// Set the lines color
$line_color = imagecolorallocate($image, 140, 0, 0);

// Add lines to make the code harder to break
for($i = 0; $i < 45; $i++) {
	// Generate random x and y values
	$pos_x1 = rand(0, $width);
	$pos_x2 = rand(0, $width);
	$pos_y1 = rand(0, $height);
	$pos_y2 = rand(0, $height);
	
	// Create the lines
	imageline($image, $pos_x1, $pos_y1, $pos_x2, $pos_y2, $line_color);
}

// Set the text color
$text_color = imagecolorallocate($image, 255, 0, 0);

// Pick a random spot to place the text
$pos_x = rand(5, $width - 50);
$pos_y = rand(5, $height - 20);

// Create image text
imagestring($image, 10, $pos_x, $pos_y, $secure_login, $text_color);

// Set header content-type
header('Content-Type: image/gif');

// Output the image as a gif
imagegif($image);

// Destroy the image
imagedestroy($image);