<?php
/**
 * Global functions (front end and back end accessible).
 * @since 1.2.0[a]
 */

// Current CMS version
const VERSION = 'Version 1.5.3 (&alpha;)';

/**
 * Display copyright on the admin dashboard.
 * @since 1.2.0[a]
 *
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function RSCopyright($echo = true) {
	$content = '&copy; '.date('Y').' <a href="/">ReallySimpleCMS</a> &bull; Created by <a href="https://jacefincham.com/" target="_blank">Jace Fincham</a>';
	
	if($echo)
		echo $content;
	else
		return $content;
}

/**
 * Display CMS version on the admin dashboard.
 * @since 1.2.0[a]
 *
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function RSVersion($echo = true) {
	if($echo)
		echo VERSION;
	else
		return VERSION;
}

/**
 * Fetch a stylesheet.
 * @since 1.3.3[a]
 *
 * @param string $stylesheet
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getStylesheet($stylesheet, $echo = true) {
	if($echo)
		echo '<link rel="stylesheet" href="'.trailingSlash(STYLES).$stylesheet.'">';
	else
		return '<link rel="stylesheet" href="'.trailingSlash(STYLES).$stylesheet.'">';
}

/**
 * Fetch a script.
 * @since 1.3.3[a]
 *
 * @param string $script
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getScript($script, $echo = true) {
	if($echo)
		echo '<script src="'.trailingSlash(SCRIPTS).$script.'"></script>';
	else
		return '<script src="'.trailingSlash(SCRIPTS).$script.'"></script>';
}

/**
 * Retrieve a setting from the database.
 * @since 1.2.5[a]
 *
 * @param string $name
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getSetting($name, $echo = true) {
	global $rs_query;

	$setting = $rs_query->selectRow('settings', 'value', array('name'=>$name));

	if($echo)
		echo $setting['value'];
	else
		return $setting['value'];
}

/**
 * Trim text down to a certain number of words.
 * @since 1.2.5[a]
 *
 * @param string $text
 * @param int $num_words (optional; default: 50)
 * @param string $more (optional; default: '')
 * @return string
 */
function trimWords($text, $num_words = 50, $more = '') {
	if(empty($more)) $more = '&hellip;';

	$words = explode(' ', $text);

	if(count($words) > $num_words) {
		$words = array_slice($words, 0, $num_words);
		$trimmed_text = implode(' ', $words).$more;
	} else {
		$trimmed_text = $text;
	}

	return $trimmed_text;
}

/**
 * Add a trailing slash to a string.
 * @since 1.3.1[a]
 *
 * @param string $text
 * @return string
 */
function trailingSlash($text) {
	return $text.'/';
}