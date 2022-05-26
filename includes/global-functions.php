<?php
/**
 * Global variables and functions (front end and back end accessible).
 * @since 1.2.0[a]
 */

// Include the backward compatible functions file
require_once PATH . INC . '/backward-compat.php';

// Array to hold all existing post types
$post_types = array();

// Array to hold all existing taxonomies
$taxonomies = array();

/*------------------------------------*\
    DATABASE & INSTALLATION
\*------------------------------------*/

/**
 * Populate a database table.
 * @since 1.0.8[b]
 *
 * @param string $table
 */
function populateTable($table): void {
	// Extend the Query class
	global $rs_query;
	
	// Fetch the database schema
	$schema = dbSchema();
	
	switch($table) {
		case 'postmeta':
		case 'posts':
			$names = array('postmeta', 'posts');
			
			foreach($names as $name) {
				if($rs_query->tableExists($name)) {
					$rs_query->dropTable($name);
					$rs_query->doQuery($schema[$name]);
				}
			}
			
			$admin_user_role = getUserRoleId('Administrator');
			$admin = $rs_query->selectField('users', 'id', array('role' => $admin_user_role), 'id', 'ASC', '1');
			
			populatePosts($admin);
			break;
		case 'settings':
			populateSettings();
			break;
		case 'taxonomies':
			populateTaxonomies();
			break;
		case 'terms':
		case 'term_relationships':
			$names = array('terms', 'term_relationships');
			
			foreach($names as $name) {
				if($rs_query->tableExists($name)) {
					$rs_query->dropTable($name);
					$rs_query->doQuery($schema[$name]);
				}
			}
			
			$post = $rs_query->selectField('posts', 'id', array(
				'status' => 'published',
				'type' => 'post'
			), 'id', 'ASC', '1');
			
			populateTerms($post);
			break;
		case 'usermeta':
		case 'users':
			$names = array('usermeta', 'users');
			
			foreach($names as $name) {
				if($rs_query->tableExists($name)) {
					$rs_query->dropTable($name);
					$rs_query->doQuery($schema[$name]);
				}
			}
			
			populateUsers();
			break;
		case 'user_privileges':
		case 'user_relationships':
		case 'user_roles':
			$names = array('user_privileges', 'user_relationships', 'user_roles');
			
			foreach($names as $name) {
				if($rs_query->tableExists($name)) {
					$rs_query->dropTable($name);
					$rs_query->doQuery($schema[$name]);
				}
			}
			
			populateUserRoles();
			populateUserPrivileges();
			break;
	}
}

/**
 * Populate the `posts` database table.
 * @since 1.3.7[a]
 * @deprecated from 1.7.0[a] to 1.0.8[b]
 *
 * @param int $author
 * @return array
 */
function populatePosts($author): array {
	// Extend the Query class
	global $rs_query;
	
	// Create a sample page
	$post['home_page'] = $rs_query->insert('posts', array(
		'title' => 'Sample Page',
		'author' => $author,
		'date' => 'NOW()',
		'content' => '<p>This is just a sample page to get you started.</p>',
		'status' => 'published',
		'slug' => 'sample-page',
		'type' => 'page'
	));
	
	// Create a sample blog post
	$post['blog_post'] = $rs_query->insert('posts', array(
		'title' => 'Sample Blog Post',
		'author' => $author,
		'date' => 'NOW()',
		'content' => '<p>This is your first blog post. Feel free to remove this text and replace it with your own.</p>',
		'status' => 'published',
		'slug' => 'sample-post'
	));
	
	$postmeta = array(
		'home_page' => array(
			'title' => 'Sample Page',
			'description' => 'Just a simple meta description for your sample page.',
			'feat_image' => 0,
			'template' => 'default'
		),
		'blog_post' => array(
			'title' => 'Sample Blog Post',
			'description' => 'Just a simple meta description for your first blog post.',
			'feat_image' => 0,
			'comment_status' => 1,
			'comment_count' => 0
		)
	);
	
	foreach($postmeta as $metadata) {
		foreach($metadata as $key => $value) {
			$rs_query->insert('postmeta', array(
				'post' => $post[key($postmeta)],
				'_key' => $key,
				'value' => $value
			));
		}
		
		next($postmeta);
	}
	
	return $post;
}

/**
 * Populate the `user_roles` database table.
 * @since 1.0.8[b]
 */
function populateUserRoles(): void {
	// Extend the Query object
	global $rs_query;
	
	$roles = array('User', 'Editor', 'Moderator', 'Administrator');
	
	foreach($roles as $role)
		$rs_query->insert('user_roles', array('name' => $role, '_default' => 'yes'));
}

/**
 * Populate the `user_privileges` and `user_relationships` database tables.
 * @since 1.0.8[b]
 */
function populateUserPrivileges(): void {
	// Extend the Query object
	global $rs_query;
	
	$admin_pages = array(
		'pages',
		'posts',
		'categories',
		'media',
		'comments',
		'themes',
		'menus',
		'widgets',
		'users',
		'login_attempts',
		'login_blacklist',
		'login_rules',
		'settings',
		'user_roles'
	);
	$privileges = array('can_view_', 'can_create_', 'can_edit_', 'can_delete_');
	
	foreach($admin_pages as $admin_page) {
		foreach($privileges as $privilege) {
			switch($admin_page) {
				case 'media':
					if($privilege === 'can_create_') $privilege = 'can_upload_';
					break;
				case 'comments':
					// Skip 'can_create_' for comments
					if($privilege === 'can_create_') continue 2;
					break;
				case 'login_attempts':
					// Skip 'can_create_', 'can_edit_', and 'can_delete_' for settings
					if($privilege === 'can_create_' || $privilege === 'can_edit_' || $privilege === 'can_delete_')
						continue 2;
					break;
				case 'settings':
					// Skip 'can_view_', 'can_create_', and 'can_delete_' for settings
					if($privilege === 'can_view_' || $privilege === 'can_create_' || $privilege === 'can_delete_')
						continue 2;
					break;
			}
			
			$rs_query->insert('user_privileges', array('name' => $privilege . $admin_page));
		}
	}
	
	/**
	 * List of privileges:
	 * 1 => 'can_view_pages', 2 => 'can_create_pages', 3 => 'can_edit_pages', 4 => 'can_delete_pages',
	 * 5 => 'can_view_posts', 6 => 'can_create_posts', 7 => 'can_edit_posts', 8 => 'can_delete_posts',
	 * 9 => 'can_view_categories', 10 => 'can_create_categories', 11 => 'can_edit_categories', 12 => 'can_delete_categories',
	 * 13 => 'can_view_media', 14 => 'can_upload_media', 15 => 'can_edit_media', 16 => 'can_delete_media',
	 * 17 => 'can_view_comments', 18 => 'can_edit_comments', 19 => 'can_delete_comments',
	 * 20 => 'can_view_themes', 21 => 'can_create_themes', 22 => 'can_edit_themes', 23 => 'can_delete_themes',
	 * 24 => 'can_view_menus', 25 => 'can_create_menus', 26 => 'can_edit_menus', 27 => 'can_delete_menus',
	 * 28 => 'can_view_widgets', 29 => 'can_create_widgets', 30 => 'can_edit_widgets', 31 => 'can_delete_widgets',
	 * 32 => 'can_view_users', 33 => 'can_create_users', 34 => 'can_edit_users', 35 => 'can_delete_users',
	 * 36 => 'can_view_login_attempts',
	 * 37 => 'can_view_login_blacklist', 38 => 'can_create_login_blacklist', 39 => 'can_edit_login_blacklist', 40 => 'can_delete_login_blacklist',
	 * 41 => 'can_view_login_rules', 42 => 'can_create_login_rules', 43 => 'can_edit_login_rules', 44 => 'can_delete_login_rules',
	 * 45 => 'can_edit_settings',
	 * 46 => 'can_view_user_roles', 47 => 'can_create_user_roles', 48 => 'can_edit_user_roles', 49 => 'can_delete_user_roles'
	 */
	
	$roles = $rs_query->select('user_roles', 'id', array('id' => array('IN', 1, 2, 3, 4)), 'id');
	
	foreach($roles as $role) {
		switch($role['id']) {
			case 1:
				// Set the privileges for the 'user' role
				$privileges = array();
				break;
			case 2:
				// Set the privileges for the 'editor' role
				$privileges = array(1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15, 32, 46);
				break;
			case 3:
				// Set the privileges for the 'moderator' role
				$privileges = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 24, 25, 26, 28, 29, 30, 32, 33, 34, 36, 37, 38, 39, 40, 41, 42, 43, 46);
				break;
			case 4:
				// Set the privileges for the 'administrator' role
				$privileges = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49);
				break;
		}
		
		foreach($privileges as $privilege)
			$rs_query->insert('user_relationships', array('role' => $role['id'], 'privilege' => $privilege));
	}
}

/**
 * Populate the `users` database table.
 * @since 1.3.1[a]
 * @deprecated from 1.7.0[a] to 1.0.8[b]
 *
 * @param array $args (optional; default: array())
 * @return int
 */
function populateUsers($args = array()): int {
	// Extend the Query class
	global $rs_query;
	
	$defaults = array(
		'username' => 'admin',
		'password' => '12345678',
		'email' => 'admin@rscmswebsite.com',
		'role' => getUserRoleId('Administrator')
	);
	
	$args = array_merge($defaults, $args);
	
	// Encrypt password
	$hashed_password = password_hash($args['password'], PASSWORD_BCRYPT, array('cost' => 10));
	
	// Create an admin user
	$user = $rs_query->insert('users', array(
		'username' => $args['username'],
		'password' => $hashed_password,
		'email' => $args['email'],
		'registered' => 'NOW()',
		'role' => $args['role']
	));
	$usermeta = array('first_name' => '', 'last_name' => '', 'avatar' => 0, 'theme' => 'default');
	
	foreach($usermeta as $key => $value)
		$rs_query->insert('usermeta', array('user' => $user, '_key' => $key, 'value' => $value));
	
	return $user;
}

/**
 * Populate the `settings` database table.
 * @since 1.3.0[a]
 * @deprecated from 1.7.0[a] to 1.0.8[b]
 *
 * @param array $args (optional; default: array())
 */
function populateSettings($args = array()): void {
	// Extend the Query class
	global $rs_query;
	
	$defaults = array(
		'site_title' => 'My Website',
		'description' => 'A new ' . CMS_NAME . ' website!',
		'site_url' => (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'],
		'admin_email' => 'admin@rscmswebsite.com',
		'default_user_role' => getUserRoleId('User'),
		'home_page' => $rs_query->selectField('posts', 'id', array(
			'status' => 'published',
			'type' => 'page'
		), 'id', 'ASC', '1'),
		'do_robots' => 1,
		'enable_comments' => 1,
		'auto_approve_comments' => 0,
		'allow_anon_comments' => 0,
		'track_login_attempts' => 0,
		'delete_old_login_attempts' => 0,
		'site_logo' => 0,
		'site_icon' => 0,
		'theme' => 'carbon',
		'theme_color' => '#ededed'
	);
	
	$args = array_merge($defaults, $args);
	
	foreach($args as $name => $value)
		$rs_query->insert('settings', array('name' => $name, 'value' => $value));
}

/**
 * Populate the `taxonomies` database table.
 * @since 1.5.0[a]
 * @deprecated from 1.7.0[a] to 1.0.8[b]
 */
function populateTaxonomies(): void {
	// Extend the Query class
	global $rs_query;
	
	$taxonomies = array('category', 'nav_menu');
	
	foreach($taxonomies as $taxonomy)
		$rs_query->insert('taxonomies', array('name' => $taxonomy));
}

/**
 * Populate the `terms` database table.
 * @since 1.5.0[a]
 * @deprecated from 1.7.0[a] to 1.0.8[b]
 *
 * @param int $post
 */
function populateTerms($post): void {
	// Extend the Query class
	global $rs_query;
	
	$term = $rs_query->insert('terms', array(
		'name' => 'Uncategorized',
		'slug' => 'uncategorized',
		'taxonomy' => getTaxonomyId('category'),
		'count' => 1
	));
	
	$rs_query->insert('term_relationships', array('term' => $term, 'post' => $post));
}

/*------------------------------------*\
    POST TYPES & TAXONOMIES
\*------------------------------------*/

/**
 * Set all post type labels.
 * @since 1.0.1[b]
 *
 * @param string $post_type
 * @param array $labels (optional; default: array())
 * @return array
 */
function getPostTypeLabels($post_type, $labels = array()): array {
	// Set the default and singular names
	$name = ucwords(str_replace(array('_', '-'), ' ', ($post_type === 'media' ? $post_type : $post_type.'s')));
	$name_singular = ucwords(str_replace(array('_', '-'), ' ', $post_type));
	
	// Set the default labels
	$defaults = array(
		'name' => $name,
		'name_lowercase' => strtolower($name),
		'name_singular' => $name_singular,
		'list_items' => 'List '.$name,
		'create_item' => 'Create '.$name_singular,
		'edit_item' => 'Edit '.$name_singular
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
 */
function registerPostType($name, $args = array()): void {
	// Extend the Query object and the post types and taxonomies arrays
	global $rs_query, $post_types, $taxonomies;
	
	// Make sure the post types global is an array
	if(!is_array($post_types)) $post_types = array();
	
	// Sanitize the post type's name
	$name = sanitize($name);
	
	// Check whether the post type's name is valid
	if(empty($name) || strlen($name) > 20)
		exit('A post type\'s name must be between 1 and 20 characters long.');
	
	// Check whether the name is already registered and don't bother to proceed if it is
	if(isset($post_types[$name]) || isset($taxonomies[$name])) return;
	
	// Set the default arguments
	$defaults = array(
		'labels' => array(),
		'public' => true,
		'hierarchical' => false,
		'create_privileges' => true,
		'show_in_stats_graph' => null,
		'show_in_admin_menu' => null,
		'show_in_admin_bar' => null,
		'show_in_nav_menus' => null,
		'menu_link' => 'posts.php?type='.$name,
		'menu_icon' => null,
		'comments' => false,
		'taxonomy' => ''
	);
	
	// Merge the defaults with the provided arguments
	$args = array_merge($defaults, $args);
	
	// Loop through the args array
	foreach($args as $key => $value) {
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
		$privileges = array(
			'can_view_'.$name_lowercase,
			'can_create_'.$name_lowercase,
			'can_edit_'.$name_lowercase,
			'can_delete_'.$name_lowercase
		);
		
		// Fetch any privileges that match the ones in the array
		$db_privileges = $rs_query->select('user_privileges', '*', array('name' => array('IN',
			$privileges[0],
			$privileges[1],
			$privileges[2],
			$privileges[3]
		)));
		
		// Check whether the privileges exist in the database
		if(empty($db_privileges)) {
			// Create an empty array to hold the new privileges' ids
			$insert_ids = array();
			
			// Loop through the privileges
			for($i = 0; $i < count($privileges); $i++) {
				// Insert the new privileges into the database
				$insert_ids[] = $rs_query->insert('user_privileges', array('name' => $privileges[$i]));
				
				// Determine which privileges should be assigned to which roles
				if($privileges[$i] === 'can_view_'.$name_lowercase || $privileges[$i] === 'can_create_'.$name_lowercase || $privileges[$i] === 'can_edit_'.$name_lowercase) {
					// Insert new user role relationships into the database
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Editor'),
						'privilege' => $insert_ids[$i]
					));
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Moderator'),
						'privilege' => $insert_ids[$i]
					));
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Administrator'),
						'privilege' => $insert_ids[$i]
					));
				} elseif($privileges[$i] === 'can_delete_'.$name_lowercase) {
					// Insert new user role relationships into the database
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Moderator'),
						'privilege' => $insert_ids[$i]
					));
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Administrator'),
						'privilege' => $insert_ids[$i]
					));
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
 */
function unregisterPostType($name): void {
	// Extend the Query object and the post types array
	global $rs_query, $post_types;
	
	// Sanitize the post type's name
	$name = sanitize($name);
	
	// Check whether the post type is in the database or the name is in the post types array and isn't a default post type
	if((postTypeExists($name) || array_key_exists($name, $post_types)) && !$post_types[$name]['default']) {
		// Delete any posts of the type being unregistered
		$rs_query->delete('posts', array('type' => $name));
		
		// Create a type name from the post type's label
		$type = str_replace(' ', '_', $post_types[$name]['labels']['name_lowercase']);
		
		// Create an array to hold privileges associated with the unregistered post type
		$privileges = array('can_view_'.$type, 'can_create_'.$type, 'can_edit_'.$type, 'can_delete_'.$type);
		
		// Loop through the user privileges and delete any privileges or relationships associated with the unregistered post type
		foreach($privileges as $privilege) {
			$rs_query->delete('user_relationships', array('privilege' => getUserPrivilegeId($privilege)));
			$rs_query->delete('user_privileges', array('name' => $privilege));
		}
		
		// Remove the post type from the post types array if it exists
		if(array_key_exists($name, $post_types)) unset($post_types[$name]);
	}
}

/**
 * Register default post types.
 * @since 1.0.1[b]
 */
function registerDefaultPostTypes(): void {
	// Page
	registerPostType('page', array(
		'hierarchical' => true,
		'menu_icon' => array('copy', 'regular')
	));
	
	// Post
	registerPostType('post', array(
		'menu_link' => 'posts.php',
		'menu_icon' => 'newspaper',
		'comments' => true,
		'taxonomy' => 'category'
	));
	
	// Media
	registerPostType('media', array(
		'labels' => array(
			'create_item' => 'Upload Media'
		),
		'show_in_nav_menus' => false,
		'menu_link' => 'media.php',
		'menu_icon' => 'images'
	));
	
	// Nav_menu_item
	registerPostType('nav_menu_item', array(
		'labels' => array(
			'name' => 'Menu Items',
			'name_singular' => 'Menu Item'
		),
		'public' => false,
		'create_privileges' => false
	));
	
	// Widget
	registerPostType('widget', array(
		'public' => false,
		'menu_link' => 'widgets.php'
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
function getTaxonomyLabels($taxonomy, $labels = array()): array {
	// Set the default and singular names
	$name = ucwords(str_replace(array('_', '-'), ' ', ($taxonomy === 'category' ? 'Categories' : $taxonomy.'s')));
	$name_singular = ucwords(str_replace(array('_', '-'), ' ', $taxonomy));
	
	// Set the default labels
	$defaults = array(
		'name' => $name,
		'name_lowercase' => strtolower($name),
		'name_singular' => $name_singular,
		'list_items' => 'List '.$name,
		'create_item' => 'Create '.$name_singular,
		'edit_item' => 'Edit '.$name_singular,
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
 */
function registerTaxonomy($name, $args = array()): void {
	// Extend the Query object and the taxonomies and post types arrays
	global $rs_query, $taxonomies, $post_types;
	
	// Make sure the taxonomies global is an array
	if(!is_array($taxonomies)) $taxonomies = array();
	
	// Sanitize the name
	$name = sanitize($name);
	
	// Check whether the taxonomy's name is valid
	if(empty($name) || strlen($name) > 20)
		exit('A taxonomy\'s name must be between 1 and 20 characters long.');
	
	// Check whether the name is already registered and don't bother to proceed if it is
	if(isset($taxonomies[$name]) || isset($post_types[$name])) return;
	
	// Fetch any taxonomies that have the same name as the newly registered one
	$taxonomy = $rs_query->selectRow('taxonomies', '*', array('name' => $name));
	
	// Check whether the taxonomy already exists
	if(empty($taxonomy)) {
		// Insert the new taxonomy into the database
		$rs_query->insert('taxonomies', array('name' => $name));
	}
	
	// Set the default arguments
	$defaults = array(
		'labels' => array(),
		'public' => true,
		'hierarchical' => false,
		'create_privileges' => true,
		'show_in_stats_graph' => null,
		'show_in_admin_menu' => null,
		'show_in_admin_bar' => null,
		'show_in_nav_menus' => null,
		'menu_link' => 'terms.php?taxonomy='.$name
	);
	
	// Merge the defaults with the provided arguments
	$args = array_merge($defaults, $args);
	
	// Loop through the args array
	foreach($args as $key => $value) {
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
		$db_privileges = $rs_query->select('user_privileges', '*', array(
			'name' => array('IN', $privileges[0], $privileges[1], $privileges[2], $privileges[3])
		));
		
		// Check whether the privileges exist in the database
		if(empty($db_privileges)) {
			// Create an empty array to hold the new privileges' ids
			$insert_ids = array();
			
			// Loop through the privileges
			for($i = 0; $i < count($privileges); $i++) {
				// Insert the new privileges into the database
				$insert_ids[] = $rs_query->insert('user_privileges', array('name' => $privileges[$i]));
				
				// Determine which privileges should be assigned to which roles
				if($privileges[$i] === 'can_view_'.$name_lowercase || $privileges[$i] === 'can_create_'.$name_lowercase || $privileges[$i] === 'can_edit_'.$name_lowercase) {
					// Insert new user role relationships into the database
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Editor'),
						'privilege' => $insert_ids[$i]
					));
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Moderator'),
						'privilege' => $insert_ids[$i]
					));
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Administrator'),
						'privilege' => $insert_ids[$i]
					));
				} elseif($privileges[$i] === 'can_delete_'.$name_lowercase) {
					// Insert new user role relationships into the database
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Moderator'),
						'privilege' => $insert_ids[$i]
					));
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Administrator'),
						'privilege' => $insert_ids[$i]
					));
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
 */
function unregisterTaxonomy($name): void {
	// Extend the Query object and the taxonomies array
	global $rs_query, $taxonomies;
	
	// Sanitize the taxonomy's name
	$name = sanitize($name);
	
	// Check whether the taxonomy is in the database or the name is in the taxonomies array and isn't a default taxonomy
	if((taxonomyExists($name) || array_key_exists($name, $taxonomies)) && !$taxonomies[$name]['default']) {
		// Select any terms associated with the taxonomy
		$terms = $rs_query->select('terms', 'id', array('taxonomy' => getTaxonomyId($name)));
		
		// Loop through the terms and delete them and any relationships associated with them
		foreach($terms as $term) {
			$rs_query->delete('term_relationships', array('term' => $term));
			$rs_query->delete('terms', array('id' => $term));
		}
		
		// Delete the taxonomy from the database
		$rs_query->delete('taxonomies', array('name' => $name));
		
		// Create a taxonomies name from the taxonomy's label
		$taxonomy = str_replace(' ', '_', $taxonomies[$name]['labels']['name_lowercase']);
		
		// Create an array to hold privileges associated with the unregistered taxonomy
		$privileges = array(
			'can_view_'.$taxonomy,
			'can_create_'.$taxonomy,
			'can_edit_'.$taxonomy,
			'can_delete_'.$taxonomy
		);
		
		// Loop through the user privileges and delete any privileges or relationships associated with the unregistered taxonomy
		foreach($privileges as $privilege) {
			$rs_query->delete('user_relationships', array('privilege' => getUserPrivilegeId($privilege)));
			$rs_query->delete('user_privileges', array('name' => $privilege));
		}
		
		// Remove the taxonomy from the taxonomies array if it exists
		if(array_key_exists($name, $taxonomies)) unset($taxonomies[$name]);
	}
}

/**
 * Register default taxonomies.
 * @since 1.0.4[b]
 */
function registerDefaultTaxonomies(): void {
	// Category
	registerTaxonomy('category', array(
		'menu_link' => 'categories.php'
	));
	
	// Nav_menu
	registerTaxonomy('nav_menu', array(
		'labels' => array(
			'name' => 'Menus',
			'name_lowercase' => 'menus',
			'name_singular' => 'Menu',
			'list_items' => 'List Menus',
			'create_item' => 'Create Menu',
			'edit_item' => 'Edit Menu'
		),
		'public' => false,
		'menu_link' => 'menus.php'
	));
}

/*------------------------------------*\
    USER PRIVILEGES
\*------------------------------------*/

/**
 * Check whether a user has a specified privilege.
 * @since 1.7.2[a]
 *
 * @param string $privilege
 * @param int $role (optional; default: null)
 * @return bool
 */
function userHasPrivilege($privilege, $role = null): bool {
	// Extend the Query object and the user's session data
	global $rs_query, $session;
	
	if(is_null($role)) $role = $session['role'];
	
	$id = $rs_query->selectField('user_privileges', 'id', array('name' => $privilege));
	
	// Fetch any relationships between the user's role and the specified privilege and return true if there are
	return $rs_query->selectRow('user_relationships', 'COUNT(*)', array('role' => $role, 'privilege' => $id)) > 0;
}

/**
 * Check whether a user has a specified group of privileges.
 * @since 1.2.0[b]{ss-02}
 *
 * @param array $privileges (optional; default: array())
 * @param string $logic (optional; default: 'AND')
 * @param int $role (optional; default: null)
 * @return bool
 */
function userHasPrivileges($privileges = array(), $logic = 'AND', $role = null): bool {
	if(!is_array($privileges)) $privileges = (array)$privileges;
	
	foreach($privileges as $privilege) {
		// Check which logic operator is being used
		if(strtoupper($logic) === 'AND') {
			// Return false if one of the privileges is not found
			if(userHasPrivilege($privilege, $role) === false) return false;
		} elseif(strtoupper($logic) === 'OR') {
			// Return true if one of the privileges is found
			if(userHasPrivilege($privilege, $role) === true) return true;
		}
	}
	
	// Check which logic operator is being used
	if(strtoupper($logic) === 'AND') {
		// Return true if all of the privileges are found
		return true;
	} elseif(strtoupper($logic) === 'OR') {
		// Return false if none of the privileges are found
		return false;
	}
}

// Include the user privileges functions file
require_once PATH.INC.'/user-privileges.php';

/*------------------------------------*\
    MISCELLANEOUS
\*------------------------------------*/

/**
 * Redirect to a specified URL.
 * @since 1.7.2[a]
 *
 * @param string $url
 * @param int $status (optional; default: 302)
 */
function redirect($url, $status = 302): void {
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
 * @return nullable (null|bool)
 */
function isEmptyDir($dir): ?bool {
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
function isHomePage($id): bool {
	// Extend the Query object
	global $rs_query;
	
	// Return true if the post is the home page
	return (int)$rs_query->selectField('settings', 'value', array('name' => 'home_page')) === $id;
}

/**
 * Check whether the user is viewing a page on the admin dashboard.
 * @since 1.0.6[b]
 *
 * @return bool
 */
function isAdmin(): bool {
	return str_starts_with($_SERVER['REQUEST_URI'], '/admin/');
}

/**
 * Check whether the user is viewing the log in page.
 * @since 1.0.6[b]
 *
 * @return bool
 */
function isLogin(): bool {
	return str_starts_with($_SERVER['REQUEST_URI'], '/login.php');
}

/**
 * Check whether the user is viewing the 404 not found page.
 * @since 1.0.6[b]
 *
 * @return bool
 */
function is404(): bool {
	return str_starts_with($_SERVER['REQUEST_URI'], '/404.php');
}

/**
 * Fetch a script file.
 * @since 1.3.3[a]
 *
 * @param string $script
 * @param string $version (optional; default: VERSION)
 * @return string
 */
function getScript($script, $version = VERSION): string {
	return '<script src="'.trailingSlash(SCRIPTS).$script.(!empty($version) ? '?v='.$version : '').'"></script>';
}

/**
 * Output a script file.
 * @since 1.3.0[b]
 *
 * @param string $script
 * @param string $version (optional; default: VERSION)
 */
function putScript($script, $version = VERSION): void {
	echo getScript($script, $version);
}

/**
 * Fetch a stylesheet.
 * @since 1.3.3[a]
 *
 * @param string $stylesheet
 * @param string $version (optional; default: VERSION)
 * @return string
 */
function getStylesheet($stylesheet, $version = VERSION): string {
	return '<link href="'.trailingSlash(STYLES).$stylesheet.(!empty($version) ? '?v='.$version : '').'" rel="stylesheet">';
}

/**
 * Output a stylesheet.
 * @since 1.3.0[b]
 *
 * @param string $stylesheet
 * @param string $version (optional; default: VERSION)
 */
function putStylesheet($stylesheet, $version = VERSION): void {
	echo getStylesheet($stylesheet, $version);
}

/**
 * Retrieve a setting from the database.
 * @since 1.2.5[a]
 *
 * @param string $name
 * @return string
 */
function getSetting($name): string {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the setting from the database and return it
	return $rs_query->selectField('settings', 'value', array('name' => $name));
}

/**
 * Output a setting from the database.
 * @since 1.3.0[b]
 *
 * @param string $name
 */
function putSetting($name): void {
	echo getSetting($name);
}

/**
 * Construct a permalink.
 * @since 2.2.2[a]
 *
 * @param string $type
 * @param int $parent (optional; default: 0)
 * @param string $slug (optional; default: '')
 * @return string
 */
function getPermalink($type, $parent = 0, $slug = ''): string {
	// Extend the Query object and the post types and taxonomies arrays
	global $rs_query, $post_types, $taxonomies;
	
	// Check whether the type matches one of the defined post types
	if(array_key_exists($type, $post_types)) {
		// The posts table should be searched
		$table = 'posts';
		
		// Check whether the post type is of type 'post' or 'page'
		if($type !== 'post' && $type !== 'page') {
			// Set the base slug for the post type
			$base = str_replace('_', '-', $type);
		}
	} // Check whether the type matches one of the defined taxonomies
	elseif(array_key_exists($type, $taxonomies)) {
		// The terms table should be searched
		$table = 'terms';
		
		// Set the base slug for the term
		$base = str_replace('_', '-', $type);
	}
	
	// Create an empty permalink array
	$permalink = array();
	
	while((int)$parent !== 0) {
		// Fetch the parent post or term from the database
		$item = $rs_query->selectRow($table, array('slug', 'parent'), array('id' => $parent));
		
		// Set the new parent id
		$parent = (int)$item['parent'];
		
		// Add to the permalink array
		$permalink[] = $item['slug'];
	}
	
	// Reverse and merge the permalink array
	$permalink = implode('/', array_reverse($permalink));
	
	// Construct the full permalink and return it
	return '/'.(isset($base) ? trailingSlash($base) : '')
		.(!empty($permalink) ? trailingSlash($permalink) : '')
		.(!empty($slug) ? trailingSlash($slug) : '');
}

/**
 * Check whether a user's session is valid.
 * @since 2.0.1[a]
 *
 * @param string $session
 * @return bool
 */
function isValidSession($session): bool {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the number of times the user appears in the database and return true if it does
	return $rs_query->selectRow('users', 'COUNT(*)', array('session' => $session)) > 0;
}

/**
 * Fetch an online user's data.
 * @since 2.0.1[a]
 *
 * @param string $session
 * @return array
 */
function getOnlineUser($session): array {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the user from the database
	$user = $rs_query->selectRow('users', array('id', 'username', 'role'), array('session' => $session));
	
	// Fetch the user's avatar from the database
	$user['avatar'] = $rs_query->selectField('usermeta', 'value', array(
		'user' => $user['id'],
		'_key' => 'avatar'
	));
	
	// Fetch the user's admin theme from the database
	$user['theme'] = $rs_query->selectField('usermeta', 'value', array('user' => $user['id'], '_key' => 'theme'));
	
	// Return the user data
	return $user;
}

/**
 * Fetch the source of a specified media item.
 * @since 2.1.5[a]
 *
 * @param int $id
 * @return string
 */
function getMediaSrc($id): string {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the media from the database
	$media = $rs_query->selectField('postmeta', 'value', array('post' => $id, '_key' => 'filename'));
	
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
 * @param array $args (optional; default: array())
 * @return string
 */
function getMedia($id, $args = array()): string {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the media's source
	$src = getMediaSrc($id);
	
	// Set the cached prop to true by default
	if(empty($args['cached'])) $args['cached'] = true;
	
	// Check whether the media should be cached
	if($args['cached'] === true && $src !== '//:0') {
		// Fetch the media's modified date from the database
		$modified = $rs_query->selectField('posts', 'modified', array('id' => $id));
		
		// Add the modified date to the source
		$src .= '?cached='.formatDate($modified, 'YmdHis');
	}
	
	// Fetch the media's MIME type from the database
	$mime_type = $rs_query->selectField('postmeta', 'value', array('post' => $id, '_key' => 'mime_type'));
	
	// Determine what kind of HTML tag to construct based on the media's MIME type
	if(str_starts_with($mime_type, 'image') || $src === '//:0') {
		// Fetch the image's alt text
		$alt_text = $rs_query->selectField('postmeta', 'value', array('post' => $id, '_key' => 'alt_text'));
		
		// Add the 'src' and 'alt' props to the args array
		$props = array_merge(array('src' => $src, 'alt' => $alt_text), $args);
		
		// Start the opening portion of the tag
		$tag = '<img';
		
		// Loop through the args
		foreach($props as $key => $value) {
			// Skip over the 'cached' arg
			if($key === 'cached') continue;
			
			// Add the property and its value to the tag
			$tag .= ' '.$key.'="'.$value.'"';
		}
		
		// Return the constructed tag
		return $tag.'>';
	} elseif(str_starts_with($mime_type, 'audio')) {
		// Construct an audio tag
		return '<audio'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').' src="'.$src.'"></audio>';
	} elseif(str_starts_with($mime_type, 'video')) {
		// Construct a video tag
		return '<video'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').' src="'.$src.'"></video>';
	} else {
		// Check whether any link text has been provided
		if(empty($args['link_text'])) {
			// Fetch the media's title from the database
			$args['link_text'] = $rs_query->selectField('posts', 'title', array('id' => $id));
		}
		
		// Construct an anchor tag
		return '<a'.(
				!empty($args['class']) ? ' class="'.$args['class'].'"' : ''
			).' href="'.$src.'"'.(
				!empty($args['newtab']) && $args['newtab'] === 1 ? ' target="_blank" rel="noreferrer noopener"' : ''
			).'>'.$args['link_text'].'</a>';
	}
}

/**
 * Fetch a taxonomy's id.
 * @since 1.5.0[a]
 *
 * @param string $name
 * @return int
 */
function getTaxonomyId($name): int {
	// Extend the Query object
	global $rs_query;
	
	// Sanitize the taxonomy's name
	$name = sanitize($name);
	
	// Fetch the taxonomy's id from the database and return it
	return (int)$rs_query->selectField('taxonomies', 'id', array('name' => $name)) ?? 0;
}

/**
 * Fetch a user role's id.
 * @since 1.0.5[b]
 *
 * @param string $name
 * @return int
 */
function getUserRoleId($name): int {
	// Extend the Query object
	global $rs_query;
	
	// Sanitize the role's name
	$name = sanitize($name);
	
	// Fetch the user role's id from the database and return it
	return (int)$rs_query->selectField('user_roles', 'id', array('name' => $name)) ?? 0;
}

/**
 * Fetch a user privilege's id.
 * @since 1.0.5[b]
 *
 * @param string $name
 * @return int
 */
function getUserPrivilegeId($name): int {
	// Extend the Query object
	global $rs_query;
	
	// Sanitize the privilege's name
	$name = sanitize($name);
	
	// Fetch the user privilege's id from the database and return it
	return (int)$rs_query->selectField('user_privileges', 'id', array('name' => $name)) ?? 0;
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
function trimWords($text, $num_words = 50, $more = '&hellip;'): string {
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
 * @param string $regex (optional; default: '/[^a-z0-9_\-]/')
 * @param bool $lc (optional; default: true)
 * @return string
 */
function sanitize($text, $regex = '/[^a-z0-9_\-]/', $lc = true): string {
	// Strip all HTML and PHP tags from the text
	$text = strip_tags($text);
	
	// Check whether the text should be converted to lowercase and convert it if so
	if($lc) $text = strtolower($text);
	
	// Sanitize the text and return it
	return preg_replace($regex, '', $text);
}

/**
 * Create a button.
 * @since 1.2.7[b]
 *
 * @param array $args (optional; default: array())
 * @param bool $link (optional; default: false)
 */
function button($args = array(), $link = false): void {
	if($link)
		echo '<a class="'.(!empty($args['class']) ? $args['class'].' ' : '').'button" href="'.($args['link'] ?? '#').'"'.(!empty($args['title']) ? ' title="'.$args['title'].'"' : '').'>'.($args['label'] ?? 'Button').'</a>';
	else
		echo '<button class="'.(!empty($args['class']) ? $args['class'].' ' : '').'button"'.(!empty($args['title']) ? ' title="'.$args['title'].'"' : '').'>'.($args['label'] ?? 'Button').'</button>';
}

/**
 * Add a trailing slash to a string.
 * @since 1.3.1[a]
 *
 * @param string $text
 * @return string
 */
function trailingSlash($text): string {
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
function formatDate($date, $format = 'Y-m-d H:i:s'): string {
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
function generatePassword($length = 16, $special_chars = true, $extra_special_chars = false): string {
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