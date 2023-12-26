<?php
/**
 * Create a captcha for the login form.
 * @since 1.3.5-alpha
 *
 * @package ReallySimpleCMS
 */

session_start();

$hash = md5(rand(0, 999));
$secure_login = substr($hash, 15, 5);
$_SESSION['secure_login'] = $secure_login;

// Image dimensions
$width = 120;
$height = 30;

$image = imagecreate($width, $height);
$bg_color = imagecolorallocate($image, 0, 0, 0);
imagefill($image, 0, 0, $bg_color);

$line_color = imagecolorallocate($image, 140, 0, 0);

// Add lines to make the code harder to break
for($i = 0; $i < 45; $i++) {
	$pos_x1 = rand(0, $width);
	$pos_x2 = rand(0, $width);
	$pos_y1 = rand(0, $height);
	$pos_y2 = rand(0, $height);
	
	imageline($image, $pos_x1, $pos_y1, $pos_x2, $pos_y2, $line_color);
}

$text_color = imagecolorallocate($image, 255, 0, 0);

// Pick a random spot to place the text
$pos_x = rand(5, $width - 50);
$pos_y = rand(5, $height - 20);
imagestring($image, 10, $pos_x, $pos_y, $secure_login, $text_color);

header('Content-Type: image/gif');

imagegif($image);
imagedestroy($image);