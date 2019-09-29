<?php
/**
 * Global functions (front end and back end accessible).
 * @since 1.2.0[a]
 */

// Current CMS version
const VERSION = '1.8.6';

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
		echo 'Version '.VERSION.' (&alpha;)';
	else
		return 'Version '.VERSION.' (&alpha;)';
}

/**
 * Redirect to a specified url.
 * @since 1.7.2[a]
 *
 * @param string $url
 * @return null
 */
function redirect($url) {
	// Set the header location to the specified url
	header('Location: '.$url);
	
	// Stop any further script execution
	exit;
}

/**
 * Fetch a stylesheet.
 * @since 1.3.3[a]
 *
 * @param string $stylesheet
 * @param string $version (optional; default: VERSION)
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getStylesheet($stylesheet, $version = VERSION, $echo = true) {
	if($echo)
		echo '<link rel="stylesheet" href="'.trailingSlash(STYLES).$stylesheet.(!empty($version) ? '?version='.$version : '').'">';
	else
		return '<link rel="stylesheet" href="'.trailingSlash(STYLES).$stylesheet.(!empty($version) ? '?version='.$version : '').'">';
}

/**
 * Fetch a script.
 * @since 1.3.3[a]
 *
 * @param string $script
 * @param string $version (optional; default: VERSION)
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getScript($script, $version = VERSION, $echo = true) {
	if($echo)
		echo '<script src="'.trailingSlash(SCRIPTS).$script.(!empty($version) ? '?version='.$version : '').'"></script>';
	else
		return '<script src="'.trailingSlash(SCRIPTS).$script.(!empty($version) ? '?version='.$version : '').'"></script>';
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
	// Extend the Query class
	global $rs_query;
	
	// Fetch the setting from the database
	$setting = $rs_query->selectRow('settings', 'value', array('name'=>$name));
	
	// Display or return the setting based upon the value of $echo
	if($echo)
		echo $setting['value'];
	else
		return $setting['value'];
}

/**
 * Determine whether a user has the specified privilege.
 * @since 1.7.2[a]
 *
 * @param int $role
 * @param string $privilege
 * @return bool
 */
function userHasPrivilege($role, $privilege) {
	// Extend the Query class
	global $rs_query;
	
	// Fetch the privilege's id from the database
	$db_privilege = $rs_query->selectRow('user_privileges', 'id', array('name'=>$privilege));
	
	// Fetch any relationships between the user's role and the specified privilege
	$relationship = $rs_query->selectRow('user_relationships', 'COUNT(*)', array('role'=>$role, 'privilege'=>$db_privilege['id']));
	
	// Return true if the relationship count is greater than zero
	return $relationship > 0;
}

/**
 * Trim text down to a specified number of words.
 * @since 1.2.5[a]
 *
 * @param string $text
 * @param int $num_words (optional; default: 50)
 * @param string $more (optional; default: '&hellip;')
 * @return string
 */
function trimWords($text, $num_words = 50, $more = '&hellip;') {
	// Split the text into an array of words
	$words = explode(' ', $text);
	
	if(count($words) > $num_words) {
		// Trim the text down to the number of words specified
		$words = array_slice($words, 0, $num_words);
		
		// Return the trimmed text
		return implode(' ', $words).$more;
	} else {
		// Return the untrimmed text
		return $text;
	}
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