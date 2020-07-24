<?php
/**
 * Global variables and functions (front end and back end accessible).
 * @since 1.2.0[a]
 */

// Current CMS version
const VERSION = '1.0.5';

// Post types
$post_types = array();

// Taxonomies
$taxonomies = array();

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
			$base = str_replace('_', '-', $type);
			break;
		default:
			// Check whether the case matches one of the defined custom post types
			if(array_key_exists($type, $post_types)) {
				// The posts table should be searched
				$table = 'posts';
				
				// Set the base slug for the post type
				$base = str_replace('_', '-', $type);
			} else {
				// Return false because the type is not recognized
				return false;
			}
	}
	
	// Create an empty permalink array
	$permalink = array();
	
	while((int)$parent !== 0) {
		// Fetch the parent post or term from the database
		$item = $rs_query->selectRow($table, array('slug', 'parent'), array('id'=>$parent));
		
		// Set the new parent id
		$parent = (int)$item['parent'];
		
		// Add to the permalink array
		$permalink[] = $item['slug'];
	}
	
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
	
	// Fetch the number of times the user appears in the database and return true if it does
	return $rs_query->selectRow('users', 'COUNT(*)', array('session'=>$session)) > 0;
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
	
	// Fetch any relationships between the user's role and the specified privilege and return true if there are
	return $rs_query->selectRow('user_relationships', 'COUNT(*)', array('role'=>$role, 'privilege'=>$id)) > 0;
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
	
	// Sanitize the taxonomy's name
	$name = sanitize($name);
	
	// Fetch the taxonomy's id from the database and return it
	return (int)$rs_query->selectField('taxonomies', 'id', array('name'=>$name)) ?? 0;
}

/**
 * Set all post type labels.
 * @since 1.0.1[b]
 *
 * @param string $post_type
 * @param array $labels (optional; default: array())
 * @return array
 */
function getPostTypeLabels($post_type, $labels = array()) {
	// Set the default and singular names
	$name = ucwords(str_replace(array('_', '-'), ' ', ($post_type === 'media' ? $post_type : $post_type.'s')));
	$name_singular = ucwords(str_replace(array('_', '-'), ' ', $post_type));
	
	// Set the default labels
	$defaults = array(
		'name'=>$name,
		'name_lowercase'=>strtolower($name),
		'name_singular'=>$name_singular,
		'list_items'=>'List '.$name,
		'create_item'=>'Create '.$name_singular,
		'edit_item'=>'Edit '.$name_singular,
		'taxonomy'=>'',
		'taxonomy_singular'=>''
	);
	
	// Merge the defaults with the provided labels
	$labels = array_merge($defaults, $labels);
	
	// Return the labels
	return $labels;
}

/**
 * Register a post type.
 * @since 1.0.0[b]
 *
 * @param string $name
 * @param array $args (optional; default: array())
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
	$defaults = array(
		'labels'=>array(),
		'public'=>true,
		'hierarchical'=>false,
		'create_privileges'=>true,
		'show_in_stats_graph'=>null,
		'show_in_admin_menu'=>null,
		'show_in_admin_bar'=>null,
		'show_in_nav_menus'=>null,
		'menu_link'=>'posts.php',
		'menu_icon'=>null,
		'taxonomy'=>''
	);
	
	// Merge the defaults with the provided arguments
	$args = array_merge($defaults, $args);
	
	// Loop through the args array
	foreach($args as $key=>$value) {
		// Remove any unrecognized arguments from the array
		if(!array_key_exists($key, $defaults)) unset($args[$key]);
	}
	
	// Check whether the post type behaves like a post (hierarchical === false)
	if($args['hierarchical'] === false) {
		// Check whether a custom taxonomy has been specified
		if(!empty($args['taxonomy'])) {
			// Fetch any taxonomies that have the same name as the specified one
			$taxonomy = $rs_query->selectRow('taxonomies', '*', array('name'=>$args['taxonomy']));
			
			// Check whether the taxonomy already exists
			if(empty($taxonomy)) {
				// Set the taxonomy to 'category'
				$args['taxonomy'] = 'category';
			}
		}
	}
	
	// Set 'show_in_stats_graph' to the value of 'public' if not specified
	if(is_null($args['show_in_stats_graph'])) $args['show_in_stats_graph'] = $args['public'];
	
	// Set 'show_in_admin_menu' to the value of 'public' if not specified
	if(is_null($args['show_in_admin_menu'])) $args['show_in_admin_menu'] = $args['public'];
	
	// Set 'show_in_admin_bar' to the value of 'public' if not specified
	if(is_null($args['show_in_admin_bar'])) $args['show_in_admin_bar'] = $args['public'];
	
	// Set 'show_in_nav_menus' to the value of 'public' if not specified
	if(is_null($args['show_in_nav_menus'])) $args['show_in_nav_menus'] = $args['public'];
	
	// Set the default post types
	$default_post_types = array('page', 'media', 'post', 'nav_menu_item', 'widget');
	
	// Tag the post type as default if its name is in the $default_post_types array
	$args['default'] = in_array($name, $default_post_types, true) ? true : false;
	
	// Add the post type's name to the list of arguments
	$args['name'] = $name;
	
	// Set the default labels
	$args['labels'] = getPostTypeLabels($name, $args['labels']);
	
	// Set the label
	$args['label'] = $args['labels']['name'];
	
	// Assign the arguments to the global post types array
	$post_types[$name] = $args;
	
	// Check whether privileges should be created for the post type
	if($args['create_privileges']) {
		// Replace any spaces in the name with underscores
		$name_lowercase = str_replace(' ', '_', $args['labels']['name_lowercase']);
		
		// Create an array of privileges for the post type
		$privileges = array('can_view_'.$name_lowercase, 'can_create_'.$name_lowercase, 'can_edit_'.$name_lowercase, 'can_delete_'.$name_lowercase);
		
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
				if($privileges[$i] === 'can_view_'.$name_lowercase || $privileges[$i] === 'can_create_'.$name_lowercase || $privileges[$i] === 'can_edit_'.$name_lowercase) {
					// Insert new user role relationships into the database
					$rs_query->insert('user_relationships', array('role'=>getUserRoleId('Editor'), 'privilege'=>$insert_ids[$i]));
					$rs_query->insert('user_relationships', array('role'=>getUserRoleId('Moderator'), 'privilege'=>$insert_ids[$i]));
					$rs_query->insert('user_relationships', array('role'=>getUserRoleId('Administrator'), 'privilege'=>$insert_ids[$i]));
				} elseif($privileges[$i] === 'can_delete_'.$name_lowercase) {
					// Insert new user role relationships into the database
					$rs_query->insert('user_relationships', array('role'=>getUserRoleId('Moderator'), 'privilege'=>$insert_ids[$i]));
					$rs_query->insert('user_relationships', array('role'=>getUserRoleId('Administrator'), 'privilege'=>$insert_ids[$i]));
				}
			}
		}
	}
}

/**
 * Unregister a post type.
 * @since 1.0.5[b]
 *
 * @param string $name
 * @return null
 */
function unregisterPostType($name) {
	// Extend the Query object and the post types array
	global $rs_query, $post_types;
	
	// Sanitize the post type's name
	$name = sanitize($name);
	
	// Check whether the post type is in the database or the name is in the post types array and isn't a default post type
	if((postTypeExists($name) || array_key_exists($name, $post_types)) && !$post_types[$name]['default']) {
		// Delete any posts of the type being unregistered
		$rs_query->delete('posts', array('type'=>$name));
		
		// Create a type name from the post type's label
		$type = str_replace(' ', '_', $post_types[$name]['labels']['name_lowercase']);
		
		// Create an array to hold privileges associated with the unregistered post type
		$privileges = array('can_view_'.$type, 'can_create_'.$type, 'can_edit_'.$type, 'can_delete_'.$type);
		
		// Loop through the user privileges and delete any privileges or relationships associated with the unregistered post type
		foreach($privileges as $privilege) {
			$rs_query->delete('user_relationships', array('privilege'=>getUserPrivilegeId($privilege)));
			$rs_query->delete('user_privileges', array('name'=>$privilege));
		}
		
		// Remove the post type from the post types array if it exists
		if(array_key_exists($name, $post_types)) unset($post_types[$name]);
	}
}

/**
 * Register default post types.
 * @since 1.0.1[b]
 *
 * @return null
 */
function registerDefaultPostTypes() {
	// Page
	registerPostType('page', array(
		'hierarchical'=>true,
		'menu_link'=>'posts.php?type=page',
		'menu_icon'=>array('copy', 'regular')
	));
	
	// Post
	registerPostType('post', array(
		'labels'=>array(
			'taxonomy'=>'Categories',
			'taxonomy_singular'=>'Category'
		),
		'menu_icon'=>'newspaper',
		'taxonomy'=>'category'
	));
	
	// Media
	registerPostType('media', array(
		'labels'=>array(
			'create_item'=>'Upload Media'
		),
		'show_in_nav_menus'=>false,
		'menu_link'=>'media.php',
		'menu_icon'=>'images'
	));
	
	// Nav_menu_item
	registerPostType('nav_menu_item', array(
		'labels'=>array(
			'name'=>'Menu Items',
			'name_singular'=>'Menu Item'
		),
		'public'=>false,
		'create_privileges'=>false
	));
	
	// Widget
	registerPostType('widget', array(
		'public'=>false,
		'menu_link'=>'widgets.php'
	));
}

/**
 * Set all taxonomy labels.
 * @since 1.0.4[b]
 *
 * @param string $taxonomy
 * @param array $labels (optional; default: array())
 * @return array
 */
function getTaxonomyLabels($taxonomy, $labels = array()) {
	// Set the default and singular names
	$name = ucwords(str_replace(array('_', '-'), ' ', ($taxonomy === 'category' ? 'Categories' : $taxonomy.'s')));
	$name_singular = ucwords(str_replace(array('_', '-'), ' ', $taxonomy));
	
	// Set the default labels
	$defaults = array(
		'name'=>$name,
		'name_lowercase'=>strtolower($name),
		'name_singular'=>$name_singular,
		'list_items'=>'List '.$name,
		'create_item'=>'Create '.$name_singular,
		'edit_item'=>'Edit '.$name_singular,
	);
	
	// Merge the defaults with the provided labels
	$labels = array_merge($defaults, $labels);
	
	// Return the labels
	return $labels;
}

/**
 * Register a taxonomy.
 * @since 1.0.1[b]
 *
 * @param string $name
 * @param array $args (optional; default: array())
 * @return null
 */
function registerTaxonomy($name, $args = array()) {
	// Extend the Query object and the taxonomies array
	global $rs_query, $taxonomies;
	
	// Make sure the taxonomies global is an array
	if(!is_array($taxonomies)) $taxonomies = array();
	
	// Sanitize the name
	$name = sanitize($name);
	
	// Check whether the taxonomy's name is valid
	if(empty($name) || strlen($name) > 20)
		exit('A taxonomy\'s name must be between 1 and 20 characters long.');
	
	// Fetch any taxonomies that have the same name as the newly registered one
	$taxonomy = $rs_query->selectRow('taxonomies', '*', array('name'=>$name));
	
	// Check whether the taxonomy already exists
	if(empty($taxonomy)) {
		// Insert the new taxonomy into the database
		$rs_query->insert('taxonomies', array('name'=>$name));
	}
	
	// Set the default arguments
	$defaults = array(
		'labels'=>array(),
		'public'=>true,
		'hierarchical'=>false,
		'create_privileges'=>true,
		'show_in_stats_graph'=>null,
		'show_in_admin_menu'=>null,
		'show_in_admin_bar'=>null,
		'show_in_nav_menus'=>null,
		'menu_link'=>'terms.php',
		'post_type'=>''
	);
	
	// Merge the defaults with the provided arguments
	$args = array_merge($defaults, $args);
	
	// Loop through the args array
	foreach($args as $key=>$value) {
		// Remove any unrecognized arguments from the array
		if(!array_key_exists($key, $defaults)) unset($args[$key]);
	}
	
	// Set 'show_in_stats_graph' to the value of 'public' if not specified
	if(is_null($args['show_in_stats_graph'])) $args['show_in_stats_graph'] = $args['public'];
	
	// Set 'show_in_admin_menu' to the value of 'public' if not specified
	if(is_null($args['show_in_admin_menu'])) $args['show_in_admin_menu'] = $args['public'];
	
	// Set 'show_in_admin_bar' to the value of 'public' if not specified
	if(is_null($args['show_in_admin_bar'])) $args['show_in_admin_bar'] = $args['public'];
	
	// Set 'show_in_nav_menus' to the value of 'public' if not specified
	if(is_null($args['show_in_nav_menus'])) $args['show_in_nav_menus'] = $args['public'];
	
	// Set the default taxonomies
	$default_taxonomies = array('category', 'nav_menu');
	
	// Tag the taxonomy as default if its name is in the $default_taxonomies array
	$args['default'] = in_array($name, $default_taxonomies, true) ? true : false;
	
	// Add the taxonomy's name to the list of arguments
	$args['name'] = $name;
	
	// Set the default labels
	$args['labels'] = getTaxonomyLabels($name, $args['labels']);
	
	// Set the label
	$args['label'] = $args['labels']['name'];
	
	// Assign the arguments to the global post types array
	$taxonomies[$name] = $args;
	
	// Check whether privileges should be created for the taxonomy
	if($args['create_privileges']) {
		// Replace any spaces in the name with underscores
		$name_lowercase = str_replace(' ', '_', $args['labels']['name_lowercase']);
		
		// Create an array of privileges for the taxonomy
		$privileges = array('can_view_'.$name_lowercase, 'can_create_'.$name_lowercase, 'can_edit_'.$name_lowercase, 'can_delete_'.$name_lowercase);
		
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
				if($privileges[$i] === 'can_view_'.$name_lowercase || $privileges[$i] === 'can_create_'.$name_lowercase || $privileges[$i] === 'can_edit_'.$name_lowercase) {
					// Insert new user role relationships into the database
					$rs_query->insert('user_relationships', array('role'=>getUserRoleId('Editor'), 'privilege'=>$insert_ids[$i]));
					$rs_query->insert('user_relationships', array('role'=>getUserRoleId('Moderator'), 'privilege'=>$insert_ids[$i]));
					$rs_query->insert('user_relationships', array('role'=>getUserRoleId('Administrator'), 'privilege'=>$insert_ids[$i]));
				} elseif($privileges[$i] === 'can_delete_'.$name_lowercase) {
					// Insert new user role relationships into the database
					$rs_query->insert('user_relationships', array('role'=>getUserRoleId('Moderator'), 'privilege'=>$insert_ids[$i]));
					$rs_query->insert('user_relationships', array('role'=>getUserRoleId('Administrator'), 'privilege'=>$insert_ids[$i]));
				}
			}
		}
	}
}

/**
 * Unregister a taxonomy.
 * @since 1.0.5[b]
 *
 * @param string $name
 * @return null
 */
function unregisterTaxonomy($name) {
	// Extend the Query object and the taxonomies array
	global $rs_query, $taxonomies;
	
	// Sanitize the taxonomy's name
	$name = sanitize($name);
	
	// Check whether the taxonomy is in the database or the name is in the taxonomies array and isn't a default taxonomy
	if((taxonomyExists($name) || array_key_exists($name, $taxonomies)) && !$taxonomies[$name]['default']) {
		// Select any terms associated with the taxonomy
		$terms = $rs_query->select('terms', 'id', array('taxonomy'=>getTaxonomyId($name)));
		
		// Loop through the terms and delete them and any relationships associated with them
		foreach($terms as $term) {
			$rs_query->delete('term_relationships', array('term'=>$term));
			$rs_query->delete('terms', array('id'=>$term));
		}
		
		// Delete the taxonomy from the database
		$rs_query->delete('taxonomies', array('name'=>$name));
		
		// Create a taxonomies name from the taxonomy's label
		$taxonomy = str_replace(' ', '_', $taxonomies[$name]['labels']['name_lowercase']);
		
		// Create an array to hold privileges associated with the unregistered taxonomy
		$privileges = array('can_view_'.$taxonomy, 'can_create_'.$taxonomy, 'can_edit_'.$taxonomy, 'can_delete_'.$taxonomy);
		
		// Loop through the user privileges and delete any privileges or relationships associated with the unregistered taxonomy
		foreach($privileges as $privilege) {
			$rs_query->delete('user_relationships', array('privilege'=>getUserPrivilegeId($privilege)));
			$rs_query->delete('user_privileges', array('name'=>$privilege));
		}
		
		// Remove the taxonomy from the taxonomies array if it exists
		if(array_key_exists($name, $taxonomies)) unset($taxonomies[$name]);
	}
}

/**
 * Register default taxonomies.
 * @since 1.0.4[b]
 *
 * @return null
 */
function registerDefaultTaxonomies() {
	// Category
	registerTaxonomy('category', array(
		'menu_link'=>'categories.php'
	));
	
	// Nav_menu
	registerTaxonomy('nav_menu', array(
		'labels'=>array(
			'name'=>'Menus',
			'name_lowercase'=>'menus',
			'name_singular'=>'Menu',
			'list_items'=>'List Menus',
			'create_item'=>'Create Menu',
			'edit_item'=>'Edit Menu'
		),
		'public'=>false,
		'menu_link'=>'menus.php'
	));
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
