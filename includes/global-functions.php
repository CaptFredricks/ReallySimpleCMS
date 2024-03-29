<?php
/**
 * Global variables and functions (front end and back end accessible).
 * @since 1.2.0[a]
 */

require_once PATH . INC . '/dom-tags.php';
require_once PATH . INC . '/polyfill-functions.php';

// Set the server timezone
ini_set('date.timezone', date_default_timezone_get());

$post_types = array();
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
				'datakey' => $key,
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
	
	foreach($roles as $role) {
		$rs_query->insert('user_roles', array(
			'name' => $role,
			'is_default' => 1
		));
	}
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
				// User
				$privileges = array();
				break;
			case 2:
				// Editor
				$privileges = array(
					1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15, 32, 46
				);
				break;
			case 3:
				// Moderator
				$privileges = array(
					1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14,
					15, 16, 17, 18, 19, 20, 24, 25, 26, 28, 29, 30,
					32, 33, 34, 36, 37, 38, 39, 40, 41, 42, 43, 46
				);
				break;
			case 4:
				// Administrator
				$privileges = array(
					1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14,
					15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26,
					27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38,
					39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49
				);
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
	
	$usermeta = array(
		'first_name' => '',
		'last_name' => '',
		'display_name' => $args['username'],
		'avatar' => 0,
		'theme' => 'default',
		'dismissed_notices' => ''
	);
	
	foreach($usermeta as $key => $value) {
		$rs_query->insert('usermeta', array(
			'user' => $user,
			'datakey' => $key,
			'value' => $value
		));
	}
	
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
		'login_slug' => '',
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
 * @param string $name -- The post type's name.
 * @param array $labels (optional) -- Any predefined labels.
 * @return array
 */
function getPostTypeLabels(string $name, array $labels = array()): array {
	$name_default = ucwords(str_replace(
		array('_', '-'), ' ',
		($name === 'media' ? $name : $name . 's')
	));
	$name_singular = ucwords(str_replace(array('_', '-'), ' ', $name));
	
	$defaults = array(
		'name' => $name_default,
		'name_lowercase' => strtolower($name_default),
		'name_singular' => $name_singular,
		'list_items' => 'List ' . $name_default,
		'create_item' => 'Create ' . $name_singular,
		'edit_item' => 'Edit ' . $name_singular,
		'duplicate_item' => 'Duplicate ' . $name_singular
	);
	$labels = array_merge($defaults, $labels);
	
	return $labels;
}

/**
 * Register a post type.
 * @since 1.0.0[b]
 *
 * @param string $name -- The post type's name.
 * @param array $args (optional) -- The args.
 */
function registerPostType(string $name, array $args = array()): void {
	global $rs_query, $post_types, $taxonomies;
	
	if(!is_array($post_types)) $post_types = array();
	
	$name = sanitize($name);
	
	if(empty($name) || strlen($name) > 20)
		exit('A post type\'s name must be between 1 and 20 characters long.');
	
	// If the name is already registered, abort
	if(isset($post_types[$name]) || isset($taxonomies[$name])) return;
	
	$defaults = array(
		'slug' => $name,
		'labels' => array(),
		'public' => true,
		'hierarchical' => false,
		'create_privileges' => true,
		'show_in_stats_graph' => null,
		'show_in_admin_menu' => null,
		'show_in_admin_bar' => null,
		'show_in_nav_menus' => null,
		'menu_link' => 'posts.php?type=' . $name,
		'menu_icon' => null,
		'comments' => false,
		'taxonomies' => array()
	);
	$args = array_merge($defaults, $args);
	
	// Remove any unrecognized args
	foreach($args as $key => $value)
		if(!array_key_exists($key, $defaults)) unset($args[$key]);
	
	if(is_null($args['show_in_stats_graph'])) $args['show_in_stats_graph'] = $args['public'];
	if(is_null($args['show_in_admin_menu'])) $args['show_in_admin_menu'] = $args['public'];
	if(is_null($args['show_in_admin_bar'])) $args['show_in_admin_bar'] = $args['public'];
	if(is_null($args['show_in_nav_menus'])) $args['show_in_nav_menus'] = $args['public'];
	
	$default_post_types = array('page', 'media', 'post', 'nav_menu_item', 'widget');
	$args['is_default'] = in_array($name, $default_post_types, true) ? true : false;
	$args['name'] = $name;
	$args['labels'] = getPostTypeLabels($name, $args['labels']);
	$args['label'] = $args['labels']['name'];
	
	// Add the post type to the global array
	$post_types[$name] = $args;
	
	if($args['create_privileges']) {
		$name_lowercase = str_replace(' ', '_', $args['labels']['name_lowercase']);
		
		$privileges = array(
			'can_view_' . $name_lowercase,
			'can_create_' . $name_lowercase,
			'can_edit_' . $name_lowercase,
			'can_delete_' . $name_lowercase
		);
		
		$db_privileges = $rs_query->select('user_privileges', '*', array('name' => array('IN',
			$privileges[0],
			$privileges[1],
			$privileges[2],
			$privileges[3]
		)));
		
		if(empty($db_privileges)) {
			$insert_ids = array();
			
			for($i = 0; $i < count($privileges); $i++) {
				$insert_ids[] = $rs_query->insert('user_privileges', array('name' => $privileges[$i]));
				
				if($privileges[$i] === 'can_view_' . $name_lowercase ||
					$privileges[$i] === 'can_create_' . $name_lowercase ||
					$privileges[$i] === 'can_edit_' . $name_lowercase) {
						
					// Editor
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Editor'),
						'privilege' => $insert_ids[$i]
					));
					// Moderator
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Moderator'),
						'privilege' => $insert_ids[$i]
					));
					// Administrator
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Administrator'),
						'privilege' => $insert_ids[$i]
					));
				} elseif($privileges[$i] === 'can_delete_' . $name_lowercase) {
					// Moderator
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Moderator'),
						'privilege' => $insert_ids[$i]
					));
					// Administrator
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
 * @param string $name -- The post type's name.
 */
function unregisterPostType(string $name): void {
	global $rs_query, $post_types;
	
	$name = sanitize($name);
	
	// Delete the existing post type as long as it's not one of the defaults
	if((postTypeExists($name) || array_key_exists($name, $post_types)) && !$post_types[$name]['is_default']) {
		$rs_query->delete('posts', array('type' => $name));
		
		$type = str_replace(' ', '_', $post_types[$name]['labels']['name_lowercase']);
		$privileges = array(
			'can_view_' . $type,
			'can_create_' . $type,
			'can_edit_' . $type,
			'can_delete_' . $type
		);
		
		foreach($privileges as $privilege) {
			$rs_query->delete('user_relationships', array(
				'privilege' => getUserPrivilegeId($privilege)
			));
			$rs_query->delete('user_privileges', array('name' => $privilege));
		}
		
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
		'taxonomies' => array(
			'category'
		)
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
 * @param string $name -- The taxonomy's name.
 * @param array $labels (optional) -- Any predefined labels.
 * @return array
 */
function getTaxonomyLabels(string $name, array $labels = array()): array {
	$name_default = ucwords(str_replace(
		array('_', '-'), ' ',
		($name === 'category' ? 'Categories' : $name . 's')
	));
	$name_singular = ucwords(str_replace(array('_', '-'), ' ', $name));
	
	$defaults = array(
		'name' => $name_default,
		'name_lowercase' => strtolower($name_default),
		'name_singular' => $name_singular,
		'list_items' => 'List ' . $name_default,
		'create_item' => 'Create ' . $name_singular,
		'edit_item' => 'Edit ' . $name_singular,
	);
	$labels = array_merge($defaults, $labels);
	
	return $labels;
}

/**
 * Register a taxonomy.
 * @since 1.0.1[b]
 *
 * @param string $name -- The taxonomy's name.
 * @param string $post_type -- The associated post type.
 * @param array $args (optional) -- The args.
 */
function registerTaxonomy(string $name, string $post_type, array $args = array()): void {
	global $rs_query, $taxonomies, $post_types;
	
	if(!is_array($taxonomies)) $taxonomies = array();
	
	$name = sanitize($name);
	
	if(empty($name) || strlen($name) > 20)
		exit('A taxonomy\'s name must be between 1 and 20 characters long.');
	
	// If the name is already registered, abort
	if(isset($taxonomies[$name]) || isset($post_types[$name])) return;
	
	$taxonomy = $rs_query->selectRow('taxonomies', '*', array('name' => $name));
	
	if(empty($taxonomy))
		$rs_query->insert('taxonomies', array('name' => $name));
	
	$defaults = array(
		'slug' => $name,
		'labels' => array(),
		'public' => true,
		'hierarchical' => false,
		'create_privileges' => true,
		'show_in_stats_graph' => null,
		'show_in_admin_menu' => null,
		'show_in_admin_bar' => null,
		'show_in_nav_menus' => null,
		'menu_link' => 'terms.php?taxonomy=' . $name,
		'default_term' => array(
			'name' => '',
			'slug' => ''
		)
	);
	$args = array_merge($defaults, $args);
	
	// Remove any unrecognized args
	foreach($args as $key => $value)
		if(!array_key_exists($key, $defaults)) unset($args[$key]);
	
	if(is_null($args['show_in_stats_graph'])) $args['show_in_stats_graph'] = $args['public'];
	if(is_null($args['show_in_admin_menu'])) $args['show_in_admin_menu'] = $args['public'];
	if(is_null($args['show_in_admin_bar'])) $args['show_in_admin_bar'] = $args['public'];
	if(is_null($args['show_in_nav_menus'])) $args['show_in_nav_menus'] = $args['public'];
	
	$default_taxonomies = array('category', 'nav_menu');
	$args['is_default'] = in_array($name, $default_taxonomies, true) ? true : false;
	$args['post_type'] = $post_type;
	$args['name'] = $name;
	$args['labels'] = getTaxonomyLabels($name, $args['labels']);
	$args['label'] = $args['labels']['name'];
	
	// Add the taxonomy to the global array
	$taxonomies[$name] = $args;
	
	if($args['create_privileges']) {
		$name_lowercase = str_replace(' ', '_', $args['labels']['name_lowercase']);
		
		$privileges = array(
			'can_view_' . $name_lowercase,
			'can_create_' . $name_lowercase,
			'can_edit_' . $name_lowercase,
			'can_delete_' . $name_lowercase
		);
		
		$db_privileges = $rs_query->select('user_privileges', '*', array(
			'name' => array('IN', $privileges[0], $privileges[1], $privileges[2], $privileges[3])
		));
		
		if(empty($db_privileges)) {
			$insert_ids = array();
			
			for($i = 0; $i < count($privileges); $i++) {
				$insert_ids[] = $rs_query->insert('user_privileges', array('name' => $privileges[$i]));
				
				if($privileges[$i] === 'can_view_' . $name_lowercase ||
					$privileges[$i] === 'can_create_' . $name_lowercase ||
					$privileges[$i] === 'can_edit_' . $name_lowercase) {
						
					// Editor
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Editor'),
						'privilege' => $insert_ids[$i]
					));
					// Moderator
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Moderator'),
						'privilege' => $insert_ids[$i]
					));
					// Administrator
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Administrator'),
						'privilege' => $insert_ids[$i]
					));
				} elseif($privileges[$i] === 'can_delete_'.$name_lowercase) {
					// Moderator
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Moderator'),
						'privilege' => $insert_ids[$i]
					));
					// Administrator
					$rs_query->insert('user_relationships', array(
						'role' => getUserRoleId('Administrator'),
						'privilege' => $insert_ids[$i]
					));
				}
			}
		}
	}
	
	if(!empty($args['default_term']['name']) && !empty($args['default_term']['slug'])) {
		$term = $rs_query->selectRow('terms', 'COUNT(*)', array('slug' => $args['default_term']['slug'])) > 0;
		
		if(!$term) {
			$rs_query->insert('terms', array(
				'name' => $args['default_term']['name'],
				'slug' => $args['default_term']['slug'],
				'taxonomy' => getTaxonomyId($name)
			));
		}
	}
}

/**
 * Unregister a taxonomy.
 * @since 1.0.5[b]
 *
 * @param string $name -- The taxonomy's name.
 */
function unregisterTaxonomy(string $name): void {
	global $rs_query, $taxonomies;
	
	$name = sanitize($name);
	
	// Delete the existing taxonomy as long as it's not one of the defaults
	if((taxonomyExists($name) || array_key_exists($name, $taxonomies)) && !$taxonomies[$name]['is_default']) {
		$terms = $rs_query->select('terms', 'id', array('taxonomy' => getTaxonomyId($name)));
		
		foreach($terms as $term) {
			$rs_query->delete('term_relationships', array('term' => $term));
			$rs_query->delete('terms', array('id' => $term));
		}
		
		$rs_query->delete('taxonomies', array('name' => $name));
		
		$taxonomy = str_replace(' ', '_', $taxonomies[$name]['labels']['name_lowercase']);
		$privileges = array(
			'can_view_' . $taxonomy,
			'can_create_' . $taxonomy,
			'can_edit_' . $taxonomy,
			'can_delete_' . $taxonomy
		);
		
		foreach($privileges as $privilege) {
			$rs_query->delete('user_relationships', array('privilege' => getUserPrivilegeId($privilege)));
			$rs_query->delete('user_privileges', array('name' => $privilege));
		}
		
		if(array_key_exists($name, $taxonomies)) unset($taxonomies[$name]);
	}
}

/**
 * Register default taxonomies.
 * @since 1.0.4[b]
 */
function registerDefaultTaxonomies(): void {
	// Category
	registerTaxonomy('category', 'post', array(
		'menu_link' => 'categories.php',
		'default_term' => array(
			'name' => 'Uncategorized',
			'slug' => 'uncategorized'
		)
	));
	
	// Nav_menu
	registerTaxonomy('nav_menu', '', array(
		'labels' => array(
			'name' => 'Menus',
			'name_lowercase' => 'menus',
			'name_singular' => 'Menu',
			'list_items' => 'List Menus',
			'create_item' => 'Create Menu',
			'edit_item' => 'Edit Menu'
		),
		'public' => false,
		'create_privileges' => false,
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
	
	return $rs_query->selectRow('user_relationships', 'COUNT(*)', array(
		'role' => $role,
		'privilege' => $id
	)) > 0;
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
		if(strtoupper($logic) === 'AND') {
			if(userHasPrivilege($privilege, $role) === false) return false;
		} elseif(strtoupper($logic) === 'OR') {
			if(userHasPrivilege($privilege, $role) === true) return true;
		}
	}
	
	if(strtoupper($logic) === 'AND')
		return true;
	elseif(strtoupper($logic) === 'OR')
		return false;
}

// Include the user privileges functions file
//require_once PATH.INC.'/user-privileges.php';

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
	
	$name = sanitize($name);
	
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
	
	$name = sanitize($name);
	
	return (int)$rs_query->selectField('user_privileges', 'id', array('name' => $name)) ?? 0;
}

/*------------------------------------*\
    MISCELLANEOUS
\*------------------------------------*/

/**
 * Check whether a directory is empty.
 * @since 2.3.0[a]
 *
 * @param string $dir
 * @return nullable (null|bool)
 */
function isEmptyDir($dir): ?bool {
	if(!is_readable($dir)) return null;
	
	$handle = opendir($dir);
	
	while(($entry = readdir($handle)) !== false)
		if($entry !== '.' && $entry !== '..') return false;
	
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
	$login_slug = getSetting('login_slug');
	
	return str_starts_with($_SERVER['REQUEST_URI'], '/login.php') ||
		(!empty($login_slug) && str_contains($_SERVER['REQUEST_URI'], $login_slug));
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
 * @param string $script -- The script to load.
 * @param string $version (optional) -- The script's version.
 * @return string
 */
function getScript(string $script, string $version = CMS_VERSION): string {
	return '<script src="' . slash(SCRIPTS) . $script .
		(!empty($version) ? '?v=' . $version : '') . '"></script>';
}

/**
 * Output a script file.
 * @since 1.3.0[b]
 *
 * @param string $script -- The script to load.
 * @param string $version (optional) -- The script's version.
 */
function putScript(string $script, string $version = CMS_VERSION): void {
	echo getScript($script, $version);
}

/**
 * Fetch a stylesheet.
 * @since 1.3.3[a]
 *
 * @param string $stylesheet -- The stylesheet to load.
 * @param string $version (optional) -- The stylesheet's version.
 * @return string
 */
function getStylesheet(string $stylesheet, string $version = CMS_VERSION): string {
	return '<link href="' . slash(STYLES) . $stylesheet .
		(!empty($version) ? '?v=' . $version : '') . '" rel="stylesheet">';
}

/**
 * Output a stylesheet.
 * @since 1.3.0[b]
 *
 * @param string $stylesheet -- The stylesheet to load.
 * @param string $version (optional) -- The stylesheet's version.
 */
function putStylesheet(string $stylesheet, string $version = CMS_VERSION): void {
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
 * @param string $name -- The post's name.
 * @param int $parent (optional) -- The post's parent.
 * @param string $slug (optional) -- The post's slug.
 * @return string
 */
function getPermalink(string $name, int $parent = 0, string $slug = ''): string {
	global $rs_query, $post_types, $taxonomies;
	
	if(array_key_exists($name, $post_types)) {
		$table = 'posts';
		
		if($name !== 'post' && $name !== 'page') {
			if($post_types[$name]['slug'] !== $name)
				$base = str_replace('_', '-', $post_types[$name]['slug']);
			else
				$base = str_replace('_', '-', $name);
		}
	} elseif(array_key_exists($name, $taxonomies)) {
		$table = 'terms';
		
		if($taxonomies[$name]['slug'] !== $name)
			$base = str_replace('_', '-', $taxonomies[$name]['slug']);
		else
			$base = str_replace('_', '-', $name);
	}
	
	$permalink = array();
	
	while((int)$parent !== 0) {
		$item = $rs_query->selectRow($table, array('slug', 'parent'), array('id' => $parent));
		$parent = (int)$item['parent'];
		$permalink[] = $item['slug'];
	}
	
	$permalink = implode('/', array_reverse($permalink));
	
	// Construct the full permalink and return it
	return '/' . (isset($base) ? slash($base) : '') .
		(!empty($permalink) ? slash($permalink) : '') .
		(!empty($slug) ? slash($slug) : '');
}

/**
 * Check whether a user's session is valid.
 * @since 2.0.1[a]
 *
 * @param string $session -- The session data.
 * @return bool
 */
function isValidSession(string $session): bool {
	global $rs_query;
	
	return $rs_query->selectRow('users', 'COUNT(*)', array('session' => $session)) > 0;
}

/**
 * Fetch an online user's data.
 * @since 2.0.1[a]
 *
 * @param string $session -- The session data.
 * @return array
 */
function getOnlineUser(string $session): array {
	global $rs_query;
	
	$user = $rs_query->selectRow('users', array('id', 'username', 'role'), array(
		'session' => $session
	));
	
	$usermeta = array('display_name', 'avatar', 'theme', 'dismissed_notices');
	
	foreach($usermeta as $meta) {
		$user[$meta] = $rs_query->selectField('usermeta', 'value', array(
			'user' => $user['id'],
			'datakey' => $meta
		));
	}
	
	$user['dismissed_notices'] = unserialize($user['dismissed_notices']);
	
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
	
	$media = $rs_query->selectField('postmeta', 'value', array(
		'post' => $id,
		'datakey' => 'filepath'
	));
	
	if(!empty($media))
		return slash(UPLOADS) . $media;
	else
		return '//:0';
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
	
	$src = getMediaSrc($id);
	
	if(empty($args['cached'])) $args['cached'] = true;
	
	if($args['cached'] === true && $src !== '//:0') {
		$modified = $rs_query->selectField('posts', 'modified', array('id' => $id));
		$src .= '?cached=' . formatDate($modified, 'YmdHis');
	}
	
	$mime_type = $rs_query->selectField('postmeta', 'value', array(
		'post' => $id,
		'datakey' => 'mime_type'
	));
	
	// Determine what kind of HTML tag to construct based on the media's MIME type
	if(str_starts_with($mime_type, 'image') || $src === '//:0') {
		// Image tag
		$alt_text = $rs_query->selectField('postmeta', 'value', array(
			'post' => $id,
			'datakey' => 'alt_text'
		));
		$props = array_merge(array('src' => $src, 'alt' => $alt_text), $args);
		$tag = '<img';
		
		foreach($props as $key => $value) {
			if($key === 'cached') continue;
			
			$tag .= ' ' . $key . '="' . $value . '"';
		}
		
		return $tag . '>';
	} elseif(str_starts_with($mime_type, 'audio')) {
		// Audio tag
		return '<audio' . (!empty($args['class']) ? ' class="' . $args['class'] . '"' : '') . ' src="' .
			$src . '"></audio>';
	} elseif(str_starts_with($mime_type, 'video')) {
		// Video tag
		return '<video' . (!empty($args['class']) ? ' class="' . $args['class'] . '"' : '') . ' src="' .
			$src . '"></video>';
	} else {
		// Anchor tag
		if(empty($args['link_text']))
			$args['link_text'] = $rs_query->selectField('posts', 'title', array('id' => $id));
		
		return '<a' . (!empty($args['class']) ? ' class="' . $args['class'] . '"' : '') . ' href="' .
			$src . '"' . (!empty($args['newtab']) && $args['newtab'] === 1 ?
			' target="_blank" rel="noreferrer noopener"' : '') . '>' . $args['link_text'] . '</a>';
	}
}

/**
 * Fetch a taxonomy's id.
 * @since 1.5.0[a]
 *
 * @param string $name -- The taxonomy's name.
 * @return int
 */
function getTaxonomyId(string $name): int {
	global $rs_query;
	
	$name = sanitize($name);
	
	return (int)$rs_query->selectField('taxonomies', 'id', array('name' => $name)) ?? 0;
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
	$words = explode(' ', $text);
	
	if(count($words) > $num_words) {
		// Trim the text down to the number of words specified
		$words = array_slice($words, 0, $num_words);
		
		return implode(' ', $words) . $more;
	} else {
		return $text;
	}
}

/**
 * Sanitize a string of text.
 * @since 1.0.0[b]
 *
 * @param string $text -- The text to sanitize.
 * @param string $regex (optional) -- The regex pattern.
 * @param bool $lc (optional) -- Whether to format in lowercase.
 * @return string
 */
function sanitize(string $text, string $regex = '/[^a-z0-9_\-]/', bool $lc = true): string {
	$text = strip_tags($text);
	
	if($lc) $text = strtolower($text);
	
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
	if($link) {
		echo '<a id="' . (!empty($args['id']) ? $args['id'] : '') . '"' .
			' class="' . (!empty($args['class']) ? $args['class'] . ' ' : '') . 'button" href="' .
			($args['link'] ?? '#') . '"' . (!empty($args['title']) ? ' title="' . $args['title'] . '"' : '') .
			'>' . ($args['label'] ?? 'Button') . '</a>';
	} else {
		echo '<button id="' . (!empty($args['id']) ? $args['id'] : '') . '"' .
			' class="' . (!empty($args['class']) ? $args['class'] . ' ' : '') . 'button"' .
			(!empty($args['title']) ? ' title="' . $args['title'] . '"' : '') . '>' .
			($args['label'] ?? 'Button') . '</button>';
	}
}

/**
 * Add a trailing slash to a string.
 * @since 1.3.1[a]
 * @deprecated since 1.3.6[b]
 *
 * @param string $text
 * @return string
 */
function trailingSlash($text): string {
	return slash($text);
}

/**
 * Format a date string.
 * @since 1.2.1[a]
 *
 * @param string $date -- The raw date.
 * @param string $format (optional) -- The date format.
 * @return string
 */
function formatDate(string $date, string $format = 'Y-m-d H:i:s'): string {
	return date_format(date_create($date), $format);
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
function generateHash($length = 20, $special_chars = true, $salt = ''): string {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	
	if($special_chars) $chars .= '!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
	
	$hash = '';
	
	for($i = 0; $i < (int)$length; $i++)
		$hash .= substr($chars, rand(0, strlen($chars) - 1), 1);
	
	if(!empty($salt)) $hash = substr(md5(md5($hash . $salt)), 0, (int)$length);
	
	return $hash;
}

/**
 * Generate a random password.
 * @since 1.3.0[a]
 *
 * @param int $length (optional; default: 16)
 * @param bool $special_chars (optional; default: true)
 * @return string
 */
function generatePassword($length = 16, $special_chars = true): string {
	return generateHash($length, $special_chars);
}