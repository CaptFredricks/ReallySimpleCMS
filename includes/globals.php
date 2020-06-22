<?php
/**
 * Global functions (front end and back end accessible).
 * @since 1.2.0[a]
 */

// Current CMS version
const VERSION = '1.0.0';

// Custom post types
$post_types = array();

/**
 * Display the copyright information on the admin dashboard.
 * @since 1.2.0[a]
 *
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function RSCopyright($echo = true) {
	$content = '&copy; '.date('Y').' <a href="/">ReallySimpleCMS</a> &bull; Created by <a href="https://jacefincham.com/" target="_blank" rel="noreferrer noopener">Jace Fincham</a>';
	
	if($echo)
		echo $content;
	else
		return $content;
}

/**
 * Display the CMS version on the admin dashboard.
 * @since 1.2.0[a]
 *
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function RSVersion($echo = true) {
	if($echo)
		echo 'Version '.VERSION.' (&beta;)';
	else
		return 'Version '.VERSION.' (&beta;)';
}

/**
 * Redirect to a specified URL.
 * @since 1.7.2[a]
 *
 * @param string $url
 * @param int $status (optional; default: 302)
 * @return null
 */
function redirect($url, $status = 302) {
	// Set the header location to the specified URL
	header('Location: '.$url, true, $status);
	
	// Stop any further script execution
	exit;
}

/**
 * Check whether a directory is empty.
 * @since 2.3.0[a]
 *
 * @param string $dir
 * @return null|bool (null on unreadable directory, bool otherwise)
 */
function isEmptyDir($dir) {
	// Check whether the directory is readable and return null if so
	if(!is_readable($dir)) return null;
	
	// Open the directory handle
	$handle = opendir($dir);
	
	// Loop through the directory's contents
	while(($entry = readdir($handle)) !== false) {
		// Check whether the current entry is anything other than '.' or '..' and return false if so
		if($entry !== '.' && $entry !== '..') return false;
	}
	
	// Return true otherwise
	return true;
}

/**
 * Check whether a post is the website's home page.
 * @since 1.4.0[a]
 *
 * @param int $id
 * @return bool
 */
function isHomePage($id) {
	// Extend the Query object
	global $rs_query;
	
	// Return true if the post is the home page
	return (int)$rs_query->selectField('settings', 'value', array('name'=>'home_page')) === $id;
}

/**
 * Fetch a script file.
 * @since 1.3.3[a]
 *
 * @param string $script
 * @param string $version (optional; default: VERSION)
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getScript($script, $version = VERSION, $echo = true) {
	if($echo)
		echo '<script src="'.trailingSlash(SCRIPTS).$script.(!empty($version) ? '?v='.$version : '').'"></script>';
	else
		return '<script src="'.trailingSlash(SCRIPTS).$script.(!empty($version) ? '?v='.$version : '').'"></script>';
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
		echo '<link href="'.trailingSlash(STYLES).$stylesheet.(!empty($version) ? '?v='.$version : '').'" rel="stylesheet">';
	else
		return '<link href="'.trailingSlash(STYLES).$stylesheet.(!empty($version) ? '?v='.$version : '').'" rel="stylesheet">';
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
	// Extend the Query object
	global $rs_query;
	
	// Fetch the setting from the database
	$setting = $rs_query->selectField('settings', 'value', array('name'=>$name));
	
	if($echo)
		echo $setting;
	else
		return $setting;
}

/**
 * Construct a permalink.
 * @since 2.2.2[a]
 *
 * @param string $type
 * @param int $parent
 * @param string $slug (optional; default: '')
 * @return string|bool (string on recognized type, bool on unrecognized type)
 */
function getPermalink($type, $parent, $slug = '') {
	// Extend the Query object and the post types array
	global $rs_query, $post_types;
	
	switch($type) {
		case 'post': case 'page':
			// The posts table should be searched
			$table = 'posts';
			break;
		case 'term': case 'category':
			// The terms table should be searched
			$table = 'terms';
			
			// Set the base slug for categories
			$base = $type;
			break;
		default:
			// Check whether the case matches one of the defined custom post types
			if(array_key_exists($type, $post_types)) {
				// The posts table should be searched
				$table = 'posts';
				
				// Set the base slug for categories
				$base = $type;
			} else {
				// Return false because the type is not recognized
				return false;
			}
	}
	
	// Create an empty permalink array
	$permalink = array();
	
	while($parent !== 0) {
		// Fetch the parent post or term from the database
		$item = $rs_query->selectRow($table, array('slug', 'parent'), array('id'=>$parent));
		
		// Set the new parent id
		$parent = (int)$item['parent'];
		
		// Add to the permalink array
		$permalink[] = $item['slug'];
	};
	
	// Reverse and merge the permalink array
	$permalink = implode('/', array_reverse($permalink));
	
	// Construct the full permalink
	$permalink = (isset($base) ? '/'.$base : '').(!empty($permalink) ? '/'.$permalink : '').(!empty($slug) ? '/'.$slug : '').'/';
	
	// Return the permalink
	return $permalink;
}

/**
 * Check whether a user's session is valid.
 * @since 2.0.1[a]
 *
 * @param string $session
 * @return bool
 */
function isValidSession($session) {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the number of times the user appears in the database
	$count = $rs_query->selectRow('users', 'COUNT(*)', array('session'=>$session));
	
	// Return true if the count is greater than zero
	return $count > 0;
}

/**
 * Fetch an online user's data.
 * @since 2.0.1[a]
 *
 * @param string $session
 * @return array
 */
function getOnlineUser($session) {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the user from the database
	$user = $rs_query->selectRow('users', array('id', 'username', 'role'), array('session'=>$session));
	
	// Fetch the user's avatar from the database
	$user['avatar'] = $rs_query->selectField('usermeta', 'value', array('user'=>$user['id'], '_key'=>'avatar'));
	
	// Fetch the user's admin theme from the database
	$user['theme'] = $rs_query->selectField('usermeta', 'value', array('user'=>$user['id'], '_key'=>'theme'));
	
	// Return the user data
	return $user;
}

/**
 * Check whether a user has a specified privilege.
 * @since 1.7.2[a]
 *
 * @param int $role
 * @param string $privilege
 * @return bool
 */
function userHasPrivilege($role, $privilege) {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the privilege's id from the database
	$id = $rs_query->selectField('user_privileges', 'id', array('name'=>$privilege));
	
	// Fetch any relationships between the user's role and the specified privilege
	$relationship = $rs_query->selectRow('user_relationships', 'COUNT(*)', array('role'=>$role, 'privilege'=>$id));
	
	// Return true if the relationship count is greater than zero
	return $relationship > 0;
}

/**
 * Fetch the source of a specified media item.
 * @since 2.1.5[a]
 *
 * @param int $id
 * @return string
 */
function getMediaSrc($id) {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the media from the database
	$media = $rs_query->selectField('postmeta', 'value', array('post'=>$id, '_key'=>'filename'));
	
	// Check whether the media exists
	if(!empty($media)) {
		// Return the path to the media
		return trailingSlash(UPLOADS).$media;
	} else {
		// Return an empty path
		return '//:0';
	}
}

/**
 * Fetch a specified media item.
 * @since 2.2.0[a]
 *
 * @param int $id
 * @param array $props (optional; default: array())
 * @return string
 */
function getMedia($id, $props = array()) {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the media's source
	$src = getMediaSrc($id);
	
	// Fetch the media's MIME type
	$mime_type = $rs_query->selectField('postmeta', 'value', array('post'=>$id, '_key'=>'mime_type'));
	
	// Determine what kind of HTML tag to construct based on the media's MIME type
	if(strpos($mime_type, 'image') !== false || $src === '//:0') {
		// Fetch the image's alt text
		$alt_text = $rs_query->selectField('postmeta', 'value', array('post'=>$id, '_key'=>'alt_text'));
		
		// Construct an image tag
		return '<img'.(!empty($props['class']) ? ' class="'.$props['class'].'"' : '').' src="'.$src.'" alt="'.$alt_text.'"'.(!empty($props['width']) ? ' width="'.$props['width'].'"' : '').(!empty($props['height']) ? ' height="'.$props['height'].'"' : '').'>';
	} elseif(strpos($mime_type, 'audio') !== false) {
		// Construct an audio tag
		return '<audio'.(!empty($props['class']) ? ' class="'.$props['class'].'"' : '').' src="'.$src.'"></audio>';
	} elseif(strpos($mime_type, 'video') !== false) {
		// Construct a video tag
		return '<video'.(!empty($props['class']) ? ' class="'.$props['class'].'"' : '').' src="'.$src.'"></video>';
	} else {
		// Check whether any link text has been provided
		if(empty($props['link_text'])) {
			// Fetch the media's title from the database
			$props['link_text'] = $rs_query->selectField('posts', 'title', array('id'=>$id));
		}
		
		// Construct an anchor tag
		return '<a'.(!empty($props['class']) ? ' class="'.$props['class'].'"' : '').' href="'.$src.'">'.$props['link_text'].'</a>';
	}
}

/**
 * Fetch a taxonomy's id.
 * @since 1.5.0[a]
 *
 * @param string $name
 * @return int
 */
function getTaxonomyId($name) {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the taxonomy's id from the database
	$id = (int)$rs_query->selectField('taxonomies', 'id', array('name'=>$name));
	
	// Return the taxonomy's id
	return $id ?? 0;
}

/**
 * Register a custom post type.
 * @since 1.0.0[b]
 *
 * @param string $name
 * @param array $args
 * @return null
 */
function registerPostType($name, $args = array()) {
	// Extend the Query object and the post types array
	global $rs_query, $post_types;
	
	// Make sure the post types global is an array
	if(!is_array($post_types)) $post_types = array();
	
	// Sanitize the post type's name
	$name = sanitize($name);
	
	// Check whether the post type's name is valid
	if(empty($name) || strlen($name) > 20)
		exit('A post type\'s name must be between 1 and 20 characters long.');
	
	// Set the default arguments
	$defaults = array('label'=>'', 'label_singular'=>'', 'icon'=>null);
	
	// Merge the defaults with the provided arguments
	$args = array_merge($defaults, $args);
	
	// Set 'label_singular' to the value of 'label' if it's not set
	if(empty($args['label_singular'])) $args['label_singular'] = $args['label'];
	
	// Add the post type's name to the list of arguments
	$args['name'] = $name;
	
	// Assign the arguments to the global post types array
	$post_types[$name] = $args;
	
	// Create an array of privileges for the post type
	$privileges = array('can_view_'.$name.'s', 'can_create_'.$name.'s', 'can_edit_'.$name.'s', 'can_delete_'.$name.'s');
	
	// Fetch any privileges that match the ones in the array
	$db_privileges = $rs_query->select('user_privileges', '*', array('name'=>array('IN', $privileges[0], $privileges[1], $privileges[2], $privileges[3])));
	
	// Check whether the privileges exist in the database
	if(empty($db_privileges)) {
		// Create an empty array to hold the new privileges' ids
		$insert_ids = array();
		
		// Loop through the privileges
		for($i = 0; $i < count($privileges); $i++) {
			// Insert the new privileges into the database
			$insert_ids[] = $rs_query->insert('user_privileges', array('name'=>$privileges[$i]));
			
			// Determine which privileges should be assigned to which roles
			if($privileges[$i] === 'can_view_'.$name.'s' || $privileges[$i] === 'can_create_'.$name.'s' || $privileges[$i] === 'can_edit_'.$name.'s') {
				// Insert new user role relationships into the database
				$rs_query->insert('user_relationships', array('role'=>2, 'privilege'=>$insert_ids[$i]));
				$rs_query->insert('user_relationships', array('role'=>3, 'privilege'=>$insert_ids[$i]));
				$rs_query->insert('user_relationships', array('role'=>4, 'privilege'=>$insert_ids[$i]));
			} elseif($privileges[$i] === 'can_delete_'.$name.'s') {
				// Insert new user role relationships into the database
				$rs_query->insert('user_relationships', array('role'=>3, 'privilege'=>$insert_ids[$i]));
				$rs_query->insert('user_relationships', array('role'=>4, 'privilege'=>$insert_ids[$i]));
			}
		}
	}
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
 * Sanitize a string of text.
 * @since 1.0.0[b]
 *
 * @param string $text
 * @return string
 */
function sanitize($text) {
	// Convert the string to lowercase
	$text = strtolower($text);
	
	// Sanitize the string
	$sanitized = preg_replace('/[^a-z0-9_\-]/', '', $text);
	
	// Return the sanitized string
	return $sanitized;
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

/**
 * Format a date string.
 * @since 1.2.1[a]
 *
 * @param string $date
 * @param string $format (optional; default: 'Y-m-d H:i:s')
 * @return string
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
	return date_format(date_create($date), $format);
}

/**
 * Generate a random password.
 * @since 1.3.0[a]
 *
 * @param int $length (optional; default: 16)
 * @param bool $special_chars (optional; default: true)
 * @param bool $extra_special_chars (optional; default: false)
 * @return string
 */
function generatePassword($length = 16, $special_chars = true, $extra_special_chars = false) {
	// Regular characters
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	
	// If desired, add the special characters
	if($special_chars) $chars .= '!@#$%^&*()';
	
	// If desired, add the extra special characters
	if($extra_special_chars) $chars .= '-_[]{}<>~`+=,.;:/?|';
	
	// Create an empty variable to hold the password
	$password = '';
	
	// Generate a random password
	for($i = 0; $i < (int)$length; $i++)
		$password .= substr($chars, rand(0, strlen($chars) - 1), 1);
	
	// Return the password
	return $password;
}
