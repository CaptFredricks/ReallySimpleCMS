<?php
/**
 * Front end functions.
 * @since 1.0.0[a]
 */

// Autoload classes
spl_autoload_register(function($class_name) {
	require_once PATH.INC.'/class-'.strtolower($class_name).'.php';
});

//$rs_post = new Post;

// Generate a cookie hash based on the site's URL
define('COOKIE_HASH', md5(getSetting('site_url', false)));

/**
 * Include the theme's header file.
 * @since 1.5.5[a]
 *
 * @return null
 */
function getHeader() {
	// Include header with full path
	include_once PATH.CONT.'/header.php';
}

/**
 * Include the theme's footer file.
 * @since 1.5.5[a]
 *
 * @return null
 */
function getFooter() {
	// Include footer with full path
	include_once PATH.CONT.'/footer.php';
}

/**
 * Generate a random hash.
 * @since 2.0.5[a]
 *
 * @param int $length (optional; default: 20)
 * @param bool $special_chars (optional; default: true)
 * @param string $salt (optional; default: '')
 * @return string
 */
function generateHash($length = 20, $special_chars = true, $salt = '') {
	// Regular characters
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	
	// If desired, add the special characters
	if($special_chars) $chars .= '!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
	
	// Create an empty variable to hold the hash
	$hash = '';
	
	// Construct a randomized hash
	for($i = 0; $i < (int)$length; $i++)
		$hash .= substr($chars, rand(0, strlen($chars) - 1), 1);
	
	// Add any salt that's been provided and hash it with md5
	if(!empty($salt)) $hash = substr(md5(md5($hash.$salt)), 0, (int)$length);
	
	// Return the hash
	return $hash;
}

/**
 * Format an email message with HTML and CSS.
 * @since 2.0.5[a]
 *
 * @param string $heading
 * @param array $fields
 * @return string
 */
function formatEmail($heading, $fields) {
	$content = '<div style="background-color: #ededed; padding: 3rem 0;">';
	$content .= '<div style="background-color: #fdfdfd; border: 1px solid #cdcdcd; border-top-color: #ededed; color: #101010 !important; margin: 0 auto; padding: 0.75rem 1.5rem; width: 60%;">';
	$content .= !empty($heading) ? '<h2 style="text-align: center;">'.$heading.'</h2>' : '';
	$content .= !empty($fields['name']) && !empty($fields['email']) ? '<p style="margin-bottom: 0;"><strong>Name:</strong> '.$fields['name'].'</p><p style="margin-top: 0;"><strong>Email:</strong> '.$fields['email'].'</p>' : '';
	$content .= '<p style="border-top: 1px dashed #adadad; padding-top: 1em;">'.str_replace("\r\n", '<br>', $fields['message']).'</p>';
	$content .= '</div></div>';
	
	// Return the content
	return $content;
}