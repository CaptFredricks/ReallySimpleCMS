<?php
/**
 * Administrative functions.
 * @since 1.0.2[a]
 */

// Path to the admin stylesheets directory
if(!defined('ADMIN_STYLES')) define('ADMIN_STYLES', ADMIN.INC.'/css');

// Path to the admin scripts directory
if(!defined('ADMIN_SCRIPTS')) define('ADMIN_SCRIPTS', ADMIN.INC.'/js');

// Path to the admin themes directory
if(!defined('ADMIN_THEMES')) define('ADMIN_THEMES', CONT.'/admin-themes');

// Current admin page URI
if(!defined('ADMIN_URI')) define('ADMIN_URI', $_SERVER['PHP_SELF']);

// Autoload classes
spl_autoload_register(function($class_name) {
	// Find all uppercase characters in the class name
	preg_match_all('/[A-Z]/', $class_name, $matches, PREG_SET_ORDER);
	
	// Check whether the class name contains multiple uppercase characters
	if(count($matches) > 1) {
		// Remove the first match
		array_shift($matches);
		
		// Loop through the matches
		foreach($matches as $match) {
			// Flatten the array
			$match = implode($match);
			
			// Insert hyphens before every match
			$class_name = substr_replace($class_name, '-', strpos($class_name, $match), 0);
		}
	}
	
	// Include the class
	require_once PATH.ADMIN.INC.'/class-'.strtolower($class_name).'.php';
});

/*------------------------------------*\
    HEADER, FOOTER, & NAV MENU
\*------------------------------------*/

/**
 * Fetch the current admin page.
 * @since 1.5.4[a]
 *
 * @return string
 */
function getCurrentPage(): string {
	// Extend the Query object and the post types and taxonomies arrays
	global $rs_query, $post_types, $taxonomies;
	
	// Extract the current page from the filename
	$current = basename($_SERVER['PHP_SELF'], '.php');
	
	// Check whether the server request contains a query string
	if(!empty($_SERVER['QUERY_STRING'])) {
		// Fetch the query string and separate it by its parameters
		$query_params = explode('&', $_SERVER['QUERY_STRING']);
		
		// Loop through the query parameters
		foreach($query_params as $query_param) {
			// Check whether the query parameter contains 'type'
			if(str_contains($query_param, 'type')) {
				// Set the current page
				$current = str_replace(' ', '_', $post_types[substr($query_param, strpos($query_param, '=') + 1)]['labels']['name_lowercase']);
			}
			
			// Check whether the query parameter contains 'taxonomy'
			if(str_contains($query_param, 'taxonomy')) {
				// Set the current page
				$current = str_replace(' ', '_', $taxonomies[substr($query_param, strpos($query_param, '=') + 1)]['labels']['name_lowercase']);
			}
			
			// Check whether the query parameter contains 'action'
			if(str_contains($query_param, 'action')) {
				// Fetch the current action
				$action = substr($query_param, strpos($query_param, '=') + 1);
				
				// Create an array of pages to exclude
				$exclude = array('themes', 'menus', 'widgets');
				
				// Loop through the taxonomies array
				foreach($taxonomies as $taxonomy) {
					// Assign each taxonomy's name to the array
					$exclude[] = str_replace(' ', '_', $taxonomy['labels']['name_lowercase']);
				}
				
				switch($action) {
					case 'create':
					case 'upload':
						// Check whether the current page should be excluded
						if(in_array($current, $exclude, true)) {
							// Break out of the switch statement
							break;
						} else {
							// Add the action's name to the current page
							$current .= '-'.$action;
							break;
						}
				}
			}
			
			// Check whether the query parameter contains 'page'
			if(str_contains($query_param, 'page=')) {
				// Fetch the current page
				$page = substr($query_param, strpos($query_param, '=') + 1);
				
				// Replace any underscores with dashes
				$current = str_replace('_', '-', $page);
				break;
			}
		}
		
		// Check whether the current page is the 'Edit Post' page
		if($current === 'posts' && isset($_GET['id'])) {
			// Fetch the number of times the post appears in the database
			$count = $rs_query->selectRow('posts', 'COUNT(*)', array('id' => $_GET['id']));
			
			// Check whether the count is zero
			if($count === 0) {
				// Redirect to the 'List Posts' page
				redirect('posts.php');
			} else {
				// Fetch the post's type from the database
				$type = $rs_query->selectField('posts', 'type', array('id' => $_GET['id']));
				
				// Set the current page
				$current = str_replace(' ', '_', $post_types[$type]['labels']['name_lowercase']);
			}
		} // Check whether the current page is the 'Edit Term' page
		elseif($current === 'terms' && isset($_GET['id'])) {
			// Fetch the number of times the term appears in the database
			$count = $rs_query->selectRow('terms', 'COUNT(*)', array('id' => $_GET['id']));
			
			// Check whether the count is zero
			if($count === 0) {
				// Redirect to the 'List Categories' page
				redirect('categories.php');
			} else {
				// Fetch the term's taxonomy id from the database
				$tax_id = $rs_query->selectField('terms', 'taxonomy', array('id' => $_GET['id']));
				
				// Fetch the term's taxonomy from the database
				$taxonomy = $rs_query->selectField('taxonomies', 'name', array('id' => $tax_id));
				
				// Set the current page
				$current = str_replace(' ', '_', $taxonomies[$taxonomy]['labels']['name_lowercase']);
			}
		}
	}
	
	// Return the current page
	return $current === 'index' ? 'dashboard' : $current;
}

/**
 * Fetch an admin page's title.
 * @since 2.1.11[a]
 *
 * @return string
 */
function getPageTitle(): string {
	// Extend the Query object and the post types and taxonomies arrays
	global $rs_query, $post_types, $taxonomies;
	
	// Perform some checks based on what the current page is
	if(basename($_SERVER['PHP_SELF']) === 'index.php')
		$title = 'Dashboard';
	elseif(isset($_GET['type'])) {
		// Fetch the post type's label
		$title = $post_types[$_GET['type']]['label'] ?? 'Posts';
	} elseif(basename($_SERVER['PHP_SELF']) === 'posts.php' && isset($_GET['action']) && $_GET['action'] === 'edit') {
		// Fetch the post's type from the database
		$type = $rs_query->selectField('posts', 'type', array('id' => $_GET['id']));
		
		// Replace any underscores or hyphens with spaces and capitalize each word
		$title = ucwords(str_replace(array('_', '-'), ' ', $type.'s'));
	} elseif(isset($_GET['taxonomy'])) {
		// Fetch the taxonomy's label
		$title = $taxonomies[$_GET['taxonomy']]['label'] ?? 'Terms';
	} elseif(isset($_GET['page']) && $_GET['page'] === 'user_roles') {
		// Replace any underscores with spaces and capitalize each word
		$title = ucwords(str_replace('_', ' ', $_GET['page']));
	} else {
		// Extract the page title from the filename and capitalize it
		$title = ucfirst(basename($_SERVER['PHP_SELF'], '.php'));
	}
	
	// Return the title
	return $title;
}

/**
 * Output an admin script file.
 * @since 1.2.0[a]
 *
 * @param string $script
 * @param string $version (optional; default: VERSION)
 */
function adminScript($script, $version = VERSION): void {
	echo '<script src="'.trailingSlash(ADMIN_SCRIPTS).$script.(!empty($version) ? '?v='.$version : '').'"></script>';
}

/**
 * Output an admin stylesheet.
 * @since 1.2.0[a]
 *
 * @param string $stylesheet
 * @param string $version (optional; default: VERSION)
 */
function adminStylesheet($stylesheet, $version = VERSION): void {
	echo '<link href="'.trailingSlash(ADMIN_STYLES).$stylesheet.(!empty($version) ? '?v='.$version : '').'" rel="stylesheet">';
}

/**
 * Output an admin theme's stylesheet.
 * @since 2.3.1[a]
 *
 * @param string $stylesheet
 * @param string $version (optional; default: VERSION)
 */
function adminThemeStylesheet($stylesheet, $version = VERSION): void {
	echo '<link href="'.trailingSlash(ADMIN_THEMES).$stylesheet.(!empty($version) ? '?v='.$version : '').'" rel="stylesheet">';
}

/**
 * Load all admin header scripts and stylesheets.
 * @since 2.0.7[a]
 */
function adminHeaderScripts(): void {
	// Extend the user's session data
	global $session;
	
	// Button stylesheet
	putStylesheet('button.min.css');
	
	// Admin stylesheet
	adminStylesheet('style.min.css');
	
	// Check whether the user has a custom admin theme selected
	if($session['theme'] !== 'default') {
		// Filename for the admin theme stylesheet
		$filename = $session['theme'].'.css';
		
		// Check whether the stylesheet exists
		if(file_exists(trailingSlash(PATH.ADMIN_THEMES).$filename)) {
			// Admin theme stylesheet
			adminThemeStylesheet($filename);
		}
	}
	
	// Font Awesome icons stylesheet
	putStylesheet('font-awesome.min.css', ICONS_VERSION);
	
	// Font Awesome font-face rules stylesheet
	putStylesheet('font-awesome-rules.min.css');
	
	// JQuery library
	putScript('jquery.min.js', JQUERY_VERSION);
}

/**
 * Load all admin footer scripts and stylesheets.
 * @since 2.0.7[a]
 */
function adminFooterScripts(): void {
	// Admin script file
	adminScript('script.js');
}

/**
 * Display the copyright information on the admin dashboard.
 * @since 1.2.0[a]
 */
function RSCopyright(): void {
	?>
	&copy; <?php echo date('Y'); ?> <a href="/"><?php echo CMS_NAME; ?></a> &bull; Created by <a href="https://jacefincham.com/" target="_blank" rel="noreferrer noopener">Jace Fincham</a>
	<?php
}

/**
 * Display the CMS version on the admin dashboard.
 * @since 1.2.0[a]
 */
function RSVersion(): void {
	echo 'Version '.VERSION.' (&beta;)';
}

/**
 * Create a nav menu item for the admin navigation.
 * @since 1.2.5[a]
 *
 * @param array $item (optional; default: array())
 * @param array $submenu (optional; default: array())
 * @param string|array $icon (optional; default: null)
 */
function adminNavMenuItem($item = array(), $submenu = array(), $icon = null): void {
	// Fetch the current page
	$current = getCurrentPage();
	
	// Return if the menu item is not an array
	if(!empty($item) && !is_array($item)) return;
	
	// Fetch the menu item id
	$item_id = $item['id'] ?? 'menu-item';
	
	// Fetch the menu item link
	$item_link = isset($item['link']) ? trailingSlash(ADMIN).$item['link'] : 'javascript:void(0)';
	
	// Fetch the menu item caption
	$item_caption = $item['caption'] ?? ucwords(str_replace(array('_', '-'), ' ', $item_id));
	
	// Check whether the item id matches the current page
	if($item_id === $current) {
		// Give the menu item a CSS class
		$item_class = 'current-menu-item';
	} // Otherwise, check whether the submenu is empty
	elseif(!empty($submenu)) {
		// Loop through the submenu items
		foreach($submenu as $sub_item) {
			// Check whether the submenu item id matches the current page
			if(!empty($sub_item['id']) && $sub_item['id'] === $current) {
				// Give the menu item a CSS class
				$item_class = 'child-is-current';
				
				// Break out of the loop
				break;
			}
		}
	}
	?>
	<li<?php echo !empty($item_class) ? ' class="'.$item_class.'"' : ''; ?>>
		<a href="<?php echo $item_link; ?>">
			<?php
			// Check whether an icon has been provided
			if(!empty($icon)) {
				// Check whether the icon parameter is an array
				if(is_array($icon)) {
					switch($icon[1]) {
						case 'regular':
							?>
							<i class="far fa-<?php echo $icon[0]; ?>"></i>
							<?php
							break;
						case 'solid':
						default:
							?>
							<i class="fas fa-<?php echo $icon[0]; ?>"></i>
							<?php
					}
				} else {
					?>
					<i class="fas fa-<?php echo $icon; ?>"></i>
					<?php
				}
			} else {
				?>
				<i class="fas fa-code-branch"></i>
				<?php
			}
			?>
			<span><?php echo $item_caption; ?></span>
		</a>
		<?php
		// Check whether the submenu parameters have been specified
		if(!empty($submenu)) {
			// Return if the submenu is not an array
			if(!is_array($submenu)) return;
			?>
			<ul class="submenu">
				<?php
				// Loop through the submenu items
				foreach($submenu as $sub_item) {
					// Break out of the loop if the menu item is not an array
					if(!empty($sub_item) && !is_array($sub_item)) break;
					
					// Check whether the menu item is empty
					if(!empty($sub_item)) {
						// Fetch the submenu item id
						$sub_item_id = $sub_item['id'] ?? $item_id;
						
						// Fetch the submenu item link
						$sub_item_link = isset($sub_item['link']) ? trailingSlash(ADMIN).$sub_item['link'] : 'javascript:void(0)';
						
						// Fetch the submenu item caption
						$sub_item_caption = $sub_item['caption'] ?? ucwords(str_replace('-', ' ', $sub_item_id));
						?>
						<li<?php echo $sub_item_id === $current ? ' class="current-submenu-item"' : ''; ?>>
							<a href="<?php echo $sub_item_link; ?>"><?php echo $sub_item_caption; ?></a>
						</li>
						<?php
					}
				}
				?>
			</ul>
			<?php
		}
		?>
	</li>
	<?php
}

/**
 * Construct the admin nav menu.
 * @since 1.0.0[b]
 */
function adminNavMenu(): void {
	// Extend the user's session data and the post types and taxonomies arrays
	global $session, $post_types, $taxonomies;
	
	// Dashboard
	adminNavMenuItem(array('id' => 'dashboard', 'link' => 'index.php'), array(), 'tachometer-alt');
	
	// Loop through the post types
	foreach($post_types as $post_type) {
		// Skip any post type that has 'show_in_admin_menu' set to false
		if(!$post_type['show_in_admin_menu']) continue;
		
		// Create an id from the post type's label
		$id = str_replace(' ', '_', $post_type['labels']['name_lowercase']);
		
		// Check whether the post type has a valid taxonomy associated with it
		if(!empty($post_type['taxonomy']) && array_key_exists($post_type['taxonomy'], $taxonomies)) {
			// Create an id from the taxonomy's label
			$tax_id = str_replace(' ', '_', $taxonomies[$post_type['taxonomy']]['labels']['name_lowercase']);
		}
		
		// Check whether the user has sufficient privileges to view the post type
		if(userHasPrivilege($session['role'], 'can_view_'.$id)) {
			adminNavMenuItem(array('id' => $id), array( // Submenu
				array( // List <post_type>
					'link' => $post_type['menu_link'],
					'caption' => $post_type['labels']['list_items']
				),
				(userHasPrivilege($session['role'], ($post_type['name'] === 'media' ? 'can_upload_media' : 'can_create_'.$id)) ? array( // Create <post_type>
					'id' => $id === 'media' ? $id.'-upload' : $id.'-create',
					'link' => $post_type['menu_link'].($post_type['name'] === 'media' ? '?action=upload' : ($post_type['name'] === 'post' ? '?action=create' : '&action=create')),
					'caption' => $post_type['labels']['create_item']
				) : null),
				(!empty($post_type['taxonomy']) && array_key_exists($post_type['taxonomy'], $taxonomies) && userHasPrivilege($session['role'], 'can_view_'.$tax_id) && $taxonomies[$post_type['taxonomy']]['show_in_admin_menu'] ? array( // Taxonomy
					'id' => $tax_id,
					'link' => $taxonomies[$post_type['taxonomy']]['menu_link'],
					'caption' => $taxonomies[$post_type['taxonomy']]['labels']['list_items']
				) : null)
			), $post_type['menu_icon']);
		}
	}
	
	// Check whether the user has sufficient privileges to view comments
	if(userHasPrivilege($session['role'], 'can_view_comments')) {
		adminNavMenuItem(array(
			'id' => 'comments',
			'link' => 'comments.php'
		), array(), array('comments', 'regular'));
	}
	
	// Check whether the user has sufficient privileges to view customization options
	if(userHasPrivileges($session['role'], array('can_view_themes', 'can_view_menus', 'can_view_widgets'), 'OR')) {
		adminNavMenuItem(array('id' => 'customization'), array( // Submenu
			(userHasPrivilege($session['role'], 'can_view_themes') ? array(
				'id' => 'themes',
				'link' => 'themes.php',
				'caption' => 'List Themes'
			) : null),
			(userHasPrivilege($session['role'], 'can_view_menus') ? array(
				'id' => 'menus',
				'link' => 'menus.php',
				'caption' => 'List Menus'
			) : null),
			(userHasPrivilege($session['role'], 'can_view_widgets') ? array(
				'id' => 'widgets',
				'link' => 'widgets.php',
				'caption' => 'List Widgets'
			) : null)
		), 'palette');
	}
	
	// Users/user profile
	adminNavMenuItem(array('id' => 'users'), array( // Submenu
		(userHasPrivilege($session['role'], 'can_view_users') ? array(
			'link' => 'users.php',
			'caption' => 'List Users'
		) : null),
		(userHasPrivilege($session['role'], 'can_create_users') ? array(
			'id' => 'users-create',
			'link' => 'users.php?action=create',
			'caption' => 'Create User'
		) : null),
		array('id' => 'profile', 'link' => 'profile.php', 'caption' => 'Your Profile')
	), 'users');
	
	// Check whether the user has sufficient privileges to view login options
	if(userHasPrivileges($session['role'], array('can_view_login_attempts', 'can_view_login_blacklist', 'can_view_login_rules'), 'OR')) {
		adminNavMenuItem(array('id' => 'logins'), array( // Submenu
			(userHasPrivilege($session['role'], 'can_view_login_attempts') ? array(
				'link' => 'logins.php',
				'caption' => 'Attempts'
			) : null),
			(userHasPrivilege($session['role'], 'can_view_login_blacklist') ? array(
				'id' => 'blacklist',
				'link' => 'logins.php?page=blacklist',
				'caption' => 'Blacklist'
			) : null),
			(userHasPrivilege($session['role'], 'can_view_login_rules') ? array(
				'id' => 'rules',
				'link' => 'logins.php?page=rules',
				'caption' => 'Rules'
			) : null)
		), 'sign-in-alt');
	}
	
	// Check whether the user has sufficient privileges to view settings
	if(userHasPrivileges($session['role'], array('can_edit_settings', 'can_view_user_roles'), 'OR')) {
		adminNavMenuItem(array('id' => 'settings'), array( // Submenu
			(userHasPrivilege($session['role'], 'can_edit_settings') ? array(
				'link' => 'settings.php',
				'caption' => 'General'
			) : null),
			(userHasPrivilege($session['role'], 'can_edit_settings') ? array(
				'id' => 'design',
				'link' => 'settings.php?page=design',
				'caption' => 'Design'
			) : null),
			(userHasPrivilege($session['role'], 'can_view_user_roles') ? array(
				'id' => 'user-roles',
				'link' => 'settings.php?page=user_roles',
				'caption' => 'User Roles'
			) : null)
		), 'cogs');
	}
}

/*------------------------------------*\
    DASHBOARD
\*------------------------------------*/

/**
 * Get statistics for a specific set of table entries.
 * @since 1.2.5[a]
 *
 * @param string $table
 * @param string $field (optional; default: '')
 * @param string $value (optional; default: '')
 * @return int
 */
function getStatistics($table, $field = '', $value = ''): int {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the entry counts for the specified tables
	if(empty($field) || empty($value))
		return $rs_query->select($table, 'COUNT(*)');
	else
		return $rs_query->select($table, 'COUNT(*)', array($field => $value));
}

/**
 * Create and display a bar graph of site statistics.
 * @since 1.2.4[a]
 */
function statsBarGraph(): void {
	// Extend the post types and taxonomies arrays
	global $post_types, $taxonomies;
	
	// Create empty arrays to hold the bar data and the stats data
	$bars = $stats = array();
	
	// Loop through the post types
	foreach($post_types as $key => $value) {
		// Skip any post type that has 'show_in_stats_graph' set to false
		if(!$post_types[$key]['show_in_stats_graph']) continue;
		
		// Assign each post type to the bar data array
		$bars[$key] = $value;
		
		// Assign the post type's stats to its dataset
		$bars[$key]['stats'] = getStatistics('posts', 'type', $bars[$key]['name']);
		
		// Assign the post type's stats to the stats array
		$stats[] = $bars[$key]['stats'];
	}
	
	// Loop through the taxonomies
	foreach($taxonomies as $key => $value) {
		// Skip any taxonomy that has 'show_in_stats_graph' set to false
		if(!$taxonomies[$key]['show_in_stats_graph']) continue;
		
		// Assign each post type to the bar data array
		$bars[$key] = $value;
		
		// Assign the post type's stats to its dataset
		$bars[$key]['stats'] = getStatistics('terms', 'taxonomy', getTaxonomyId($bars[$key]['name']));
		
		// Assign the post type's stats to the stats array
		$stats[] = $bars[$key]['stats'];
	}
	
	// Find the max count
	$max_count = max($stats);
	
	// Divide the max count by 25 and round it up to the nearest whole number
	$num = ceil($max_count / 25);
	
	// Multiply the number times 5
	$num *= 5;
	?>
	<input type="hidden" id="max-ct" value="<?php echo $num * 5; ?>">
	<div id="stats-graph">
		<ul class="graph-y">
			<?php
			// Loop through the Y axis values
			for($i = 5; $i >= 0; $i--) {
				?>
				<li><span class="value"><?php echo $i * $num; ?></span></li>
				<?php
			}
			?>
		</ul>
		<ul class="graph-content">
			<?php
			// Loop through the bars
			foreach($bars as $bar) {
				?>
				<li style="width: <?php echo 1 / count($bars) * 100; ?>%;">
					<a class="bar" href="<?php echo $bar['menu_link']; ?>" title="<?php echo $bar['label']; ?>: <?php echo $bar['stats'].($bar['stats'] === 1 ? ' entry' : ' entries'); ?>"><?php echo $bar['stats']; ?></a>
				</li>
				<?php
			}
			?>
			<ul class="graph-overlay">
				<?php
				// Loop through the overlay items
				for($j = 5; $j >= 0; $j--) {
					?>
					<li></li>
					<?php
				}
				?>
			</ul>
		</ul>
		<ul class="graph-x">
			<?php
			// Loop through the bars
			foreach($bars as $bar) {
				?>
				<li style="width: <?php echo 1 / count($bars) * 100; ?>%;">
					<a class="value" href="<?php echo $bar['menu_link']; ?>" title="<?php echo $bar['label']; ?>: <?php echo $bar['stats'].($bar['stats'] === 1 ? ' entry' : ' entries'); ?>"><?php echo $bar['label']; ?></a>
				</li>
				<?php
			}
			?>
		</ul>
		<span class="graph-y-label">Count</span>
		<span class="graph-x-label">Category</span>
	</div>
	<?php
}

/**
 * Construct a widget for the admin dashboard.
 * @since 1.2.1[b]
 *
 * @param string $name
 */
function dashboardWidget($name): void {
	// Extend the Query object
	global $rs_query;
	?>
	<div class="dashboard-widget">
		<?php
		switch($name) {
			case 'comments':
				?>
				<h2>Comments</h2>
				<ul>
					<?php
					// Fetch the approved comment count from the database
					$approved = $rs_query->select('comments', 'COUNT(*)', array('status' => 'approved'));
					?>
					<li><a href="/admin/comments.php?status=approved">Approved</a>: <strong class="value"><?php echo $approved; ?></strong></li>
					<?php
					// Fetch the pending comment count from the database
					$pending = $rs_query->select('comments', 'COUNT(*)', array('status' => 'unapproved'));
					?>
					<li><a href="/admin/comments.php?status=unapproved">Pending</a>: <strong class="value"><?php echo $pending; ?></strong></li>
				</ul>
				<?php
				break;
			case 'users':
				?>
				<h2>Users</h2>
				<ul>
					<?php
					// Fetch all users' sessions from the database
					$users = $rs_query->select('users', 'session');
					
					// Initialize online and offline user counts to zero
					$online = $offline = 0;
					
					// Loop through the users
					foreach($users as $user) {
						// Check whether the user's session is null and increment the appropriate value
						if(is_null($user['session']))
							$offline += 1;
						else
							$online += 1;
					}
					?>
					<li><a href="/admin/users.php">Online</a>: <strong class="value"><?php echo $online; ?></strong></li>
					<li><a href="/admin/users.php">Offline</a>: <strong class="value"><?php echo $offline; ?></strong></li>
				</ul>
				<?php
				break;
			case 'logins':
				?>
				<h2>Logins</h2>
				<ul>
					<?php
					// Fetch the successful login attempt count from the database
					$login_success = $rs_query->select('login_attempts', 'COUNT(*)', array('status' => 'success'));
					?>
					<li><a href="/admin/logins.php">Successful</a>: <strong class="value"><?php echo $login_success; ?></strong></li>
					<?php
					// Fetch the failed login attempt count from the database
					$login_failure = $rs_query->select('login_attempts', 'COUNT(*)', array('status' => 'failure'));
					?>
					<li><a href="/admin/logins.php">Failed</a>: <strong class="value"><?php echo $login_failure; ?></strong></li>
					<?php
					// Fetch the blacklisted login count from the database
					$blacklisted = $rs_query->select('login_blacklist', 'COUNT(*)');
					?>
					<li><a href="/admin/logins.php?page=blacklist">Blacklisted</a>: <strong class="value"><?php echo $blacklisted; ?></strong></li>
				</ul>
				<?php
				break;
		}
		?>
	</div>
	<?php
}

/*------------------------------------*\
    TABLES & FORMS
\*------------------------------------*/

/**
 * Construct a table header row.
 * @since 1.2.1[a]
 *
 * @param array $items
 * @return string
 */
function tableHeaderRow($items): string {
	// Create an empty row
	$row = '';
	
	// Loop through the column headings
	foreach($items as $item) $row .= '<th>'.$item.'</th>';
	
	// Return the row
	return '<tr>'.$row.'</tr>';
}

/**
 * Construct a table row.
 * @since 1.4.0[a]
 *
 * @param array $cells (optional; unlimited)
 * @return string
 */
function tableRow(...$cells): string {
	return '<tr>'.(!empty($cells) ? implode('', $cells) : '').'</tr>';
}

/**
 * Construct a table cell.
 * @since 1.2.1[a]
 *
 * @param string $data
 * @param string $class (optional; default: '')
 * @param int $colspan (optional; default: 1)
 * @return string
 */
function tableCell($data, $class = '', $colspan = 1): string {
	return '<td'.(!empty($class) ? ' class="column '.$class.'"' : '').($colspan > 1 ? ' colspan="'.$colspan.'"' : '').'>'.$data.'</td>';
}

/**
 * Construct a form HTML tag.
 * @since 1.2.0[a]
 *
 * @param string $tag_name
 * @param array $args (optional; default: null)
 * @return string
 */
function formTag($tag_name, $args = null): string {
	// Create an array of property names from the args array
	$props = !is_null($args) ? array_keys($args) : array();
	
	// Create an array of whitelisted tags with their properties
	$whitelisted_props = array(
		'a' => array('id', 'class', 'href', 'content'),
		'br' => array('id', 'class'),
		'div' => array('id', 'class', 'style', 'content'),
		'hr' => array('id', 'class'),
		'i' => array('id', 'class'),
		'img' => array('id', 'class', 'src', 'width'),
		'input' => array('type', 'id', 'class', 'name', 'maxlength', 'value', 'placeholder', '*'),
		'label' => array('id', 'class', 'for', 'content'),
		'select' => array('id', 'class', 'name', 'content'),
		'span' => array('id', 'class', 'style', 'title', 'content'),
		'textarea' => array('id', 'class', 'name', 'cols', 'rows', 'content')
	);
	
	// Create an array of whitelisted tags
	$whitelisted_tags = array_keys($whitelisted_props);
	
	// Check whether the specified tag has been whitelisted
	if(in_array($tag_name, $whitelisted_tags, true)) {
		// Start the opening portion of the tag
		$tag = '<'.$tag_name;
		
		// Check whether the tag is an input
		if($tag_name === 'input') {
			// Check whether the 'type' property has been provided and set it to 'text' if not
			if(!in_array('type', $props, true)) $tag .= ' type="text"';
		}
		
		// Check whether any args have been provided
		if(!is_null($args)) {
			// Loop through the args
			foreach($args as $key => $value) {
				// Check whether the property has been whitelisted and it does not equal 'content'
				if(in_array($key, $whitelisted_props[$tag_name], true) && $key !== 'content' || str_starts_with($key, 'data-')) {
					// Check whether the tag is an input and the property is valueless
					if($tag_name === 'input' && $key === '*') {
						// Add the property to the tag
						$tag .= ' '.$value;
					} else {
						// Add the property and its value to the tag
						$tag .= ' '.$key.'="'.$value.'"';
					}
				}
			}
		}
		
		// Finish the opening portion of the tag
		$tag .= '>';
		
		// Create an array of self-closing tags
		$self_closing = array('br', 'hr', 'img', 'input');
		
		// Check whether the tag should have a closing portion
		if(!in_array($tag_name, $self_closing, true)) {
			// Add any provided content
			$tag .= $args['content'] ?? '';
			
			// Create the closing portion of the tag
			$tag .= '</'.$tag_name.'>';
		}
	} else {
		// Create an empty tag
		$tag = '';
	}
	
	// Check whether a label argument has been provided
	if(!empty($args['label'])) {
		// Construct a label tag
		$label = '<label'.(!empty($args['label']['id']) ? ' id="'.$args['label']['id'].'"' : '').(!empty($args['label']['class']) ? ' class="'.$args['label']['class'].'"' : '').'>';
		
		// Construct the content of the label
		$content = (!empty($args['label']['content']) ? $args['label']['content'] : '').'</label>';
		
		// Put everything together
		$tag = $label.$tag.$content;
	}
	
	// Return the constructed tag
	return $tag;
}

/**
 * Alias for the formTag function.
 * @since 1.2.7[b]
 *
 * @param string $tag_name
 * @param array $args (optional; default: null)
 * @return string
 */
function tag($tag_name, $args = null): string {
	return formTag($tag_name, $args);
}

/**
 * Construct a form row.
 * @since 1.1.2[a]
 *
 * @param string|array $label (optional; default: '')
 * @param array $args (optional; unlimited)
 * @return string
 */
function formRow($label = '', ...$args): string {
	// Check whether the label is empty
	if(!empty($label)) {
		// Check whether the label is an array
		if(is_array($label)) {
			// Pop the second value from the array
			$required = array_pop($label);
			
			// Convert the label array to a string
			$label = implode('', $label);
		}
		
		// Loop through the args
		for($i = 0; $i < count($args); $i++) {
			// Break out of the loop if the 'name' key is found
			if(is_array($args[$i]) && array_key_exists('name', $args[$i])) break;
		}
		
		// Create the label for the form row
		$row = '<th><label'.(!empty($args[$i]['name']) ? ' for="'.$args[$i]['name'].'"' : '').'>'.$label.(!empty($required) && $required === true ? ' <span class="required">*</span>' : '').'</label></th>';
		
		// Open the table cell tag
		$row .= '<td>';
		
		// Check whether any args have been provided
		if(count($args) > 0) {
			// Check whether the args are a multidimensional array
			if(count($args) !== count($args, COUNT_RECURSIVE)) {
				// Loop through the args
				foreach($args as $arg) {
					// Fetch the arg's HTML tag and remove it from the args array
					$tag = array_shift($arg);
					
					// Construct the form tag and add it to the row
					$row .= formTag($tag, $arg);
				}
			} else {
				// Loop through the args and add any content to the row
				foreach($args as $arg) $row .= $arg;
			}
		}
		
		// Close the table cell tag
		$row .= '</td>';
	} else {
		// Open the table cell tag
		$row = '<td colspan="2">';
		
		// Check whether any args have been provided
		if(count($args) > 0) {
			// Check whether the args are a multidimensional array
			if(count($args) !== count($args, COUNT_RECURSIVE)) {
				// Loop through the args
				foreach($args as $arg) {
					// Fetch the arg's HTML tag and remove it from the args array
					$tag = array_shift($arg);
					
					// Construct the form tag and add it to the row
					$row .= formTag($tag, $arg);
				}
			} else {
				// Loop through the args and add any content to the row
				foreach($args as $arg) $row .= $arg;
			}
		}
		
		// Close the table cell tag
		$row .= '</td>';
	}
	
	// Return the form row
	return '<tr>'.$row.'</tr>';
}

/*------------------------------------*\
    MEDIA
\*------------------------------------*/

/**
 * Upload media to the media library.
 * @since 2.1.6[a]
 *
 * @param array $data
 * @return string
 */
function uploadMediaFile($data): string {
	// Extend the Query object
	global $rs_query;
	
	// Make sure a file has been selected
	if(empty($data['name']))
		return statusMessage('A file must be selected for upload!');
	
	// Create an array of accepted MIME types
	$accepted_mime = array(
		'image/jpeg',
		'image/png',
		'image/gif',
		'image/x-icon',
		'audio/mp3',
		'audio/ogg',
		'video/mp4',
		'text/plain'
	);
	
	// Check whether the uploaded file is among the accepted MIME types
	if(!in_array($data['type'], $accepted_mime, true))
		return statusMessage('The file could not be uploaded.');
	
	// File path for the uploads directory
	$file_path = PATH.UPLOADS;
	
	// Check whether the uploads directory exists and create it if not
	if(!file_exists($file_path)) mkdir($file_path);
	
	// Convert the filename to all lowercase, replace spaces with hyphens, and remove all special characters
	$filename = preg_replace('/[^\w.-]/i', '', str_replace(' ', '-', strtolower($data['name'])));
	
	// Get a unique filename
	$filename = getUniqueFilename($filename);
	
	// Strip off the filename's extension for the post's slug
	$slug = pathinfo($filename, PATHINFO_FILENAME);
	
	// Get a unique slug
	$slug = getUniquePostSlug($slug);
	
	// Move the uploaded file to the uploads directory
	move_uploaded_file($data['tmp_name'], trailingSlash(PATH.UPLOADS).$filename);
	
	// Create an array to hold the media's metadata
	$mediameta = array('filename' => $filename, 'mime_type' => $data['type'], 'alt_text' => '');
	
	// Set the media's title
	$title = ucwords(str_replace('-', ' ', $slug));
	
	// Fetch the user's data
	$session = getOnlineUser($_COOKIE['session']);
	
	// Insert the new media into the database
	$insert_id = $rs_query->insert('posts', array(
		'title' => $title,
		'author' => $session['id'],
		'date' => 'NOW()',
		'modified' => 'NOW()',
		'slug' => $slug,
		'type' => 'media'
	));
	
	// Insert the media's metadata into the database
	foreach($mediameta as $key => $value)
		$rs_query->insert('postmeta', array('post' => $insert_id, '_key' => $key, 'value' => $value));
	
	// Check whether the media is an image
	if(in_array($data['type'], array('image/jpeg', 'image/png', 'image/gif', 'image/x-icon'), true)) {
		// Fetch the media's dimensions
		list($width, $height) = getimagesize(trailingSlash(PATH.UPLOADS).$filename);
		
		// Construct hidden fields for the status message
		$status_msg = '<div class="hidden" data-field="id">'.$insert_id.'</div><div class="hidden" data-field="title">'.$title.'</div><div class="hidden" data-field="filename">'.trailingSlash(UPLOADS).$filename.'</div><div class="hidden" data-field="mime_type">'.$data['type'].'</div><dive class="hidden" data-field="width">'.$width.'</div>';
	}
	
	// Return a success message and a hidden field with the media's id
	return statusMessage('Upload successful!', true).($status_msg ?? '');
}

/**
 * Load the media library.
 * @since 2.1.2[a]
 *
 * @param bool $image_only (optional; default: false)
 */
function loadMedia($image_only = false): void {
	// Extend the Query object
	global $rs_query;
	
	// Fetch all media from the database
	$mediaa = $rs_query->select('posts', '*', array('type' => 'media'), 'date', 'DESC');
	
	// Loop through the media
	foreach($mediaa as $media) {
		// Fetch the media's metadata from the database
		$mediameta = $rs_query->select('postmeta', array('_key', 'value'), array('post' => $media['id']));
		
		// Create an empty array to hold the metadata
		$meta = array();
		
		// Loop through the metadata
		foreach($mediameta as $metadata) {
			// Get the meta values
			$values = array_values($metadata);
			
			// Loop through the individual metadata entries
			for($i = 0; $i < count($metadata); $i += 2) {
				// Assign the metadata to the meta array
				$meta[$values[$i]] = $values[$i + 1];
			}
		}
		
		// Check whether only images should be loaded
		if($image_only) {
			// Create an array of image MIME types
			$image_mime = array('image/jpeg', 'image/png', 'image/gif', 'image/x-icon');
			
			// Check whether the current media item is an image and skip to the next item if not
			if(!in_array($meta['mime_type'], $image_mime, true)) continue;
			
			// Fetch the image's dimensions
			list($width, $height) = getimagesize(trailingSlash(PATH.UPLOADS).$meta['filename']);
		}
		?>
		<div class="media-item-wrap">
			<div class="media-item">
				<div class="thumb-wrap">
					<?php echo getMedia($media['id'], array('class' => 'thumb')); ?>
				</div>
				<div>
					<div class="hidden" data-field="id"><?php echo $media['id']; ?></div>
					<div class="hidden" data-field="thumb"><?php echo getMedia($media['id']); ?></div>
					<div class="hidden" data-field="title"><?php echo $media['title']; ?></div>
					<div class="hidden" data-field="date"><?php echo formatDate($media['date'], 'd M Y @ g:i A'); ?></div>
					<div class="hidden" data-field="filename"><?php echo mediaLink($media['id'], array('link_text' => $meta['filename'], 'newtab' => 1)); ?></div>
					<div class="hidden" data-field="mime_type"><?php echo $meta['mime_type']; ?></div>
					<div class="hidden" data-field="alt_text"><?php echo $meta['alt_text']; ?></div>
					<div class="hidden" data-field="width"><?php echo $width ?? 150; ?></div>
				</div>
			</div>
		</div>
		<?php
	}
	
	// Display a notice if no media are found
	if(empty($mediaa)) {
		?>
		<p style="margin: 1em;">The media library is empty!</p>
		<?php
	}
}

/**
 * Construct a link to a media item.
 * @since 1.2.9[b]
 *
 * @param int $id
 * @param array $args (optional; default: array())
 * @return string
 */
function mediaLink($id, $args = array()): string {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the media's modified date from the database
	$modified = $rs_query->selectField('posts', 'modified', array('id' => $id));
	
	// Fetch the media's source
	$src = getMediaSrc($id).'?cached='.formatDate($modified, 'YmdHis');
	
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

/**
 * Construct a unique filename.
 * @since 2.1.0[a]
 *
 * @param string $filename
 * @return string
 */
function getUniqueFilename($filename): string {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the number of conflicting filenames in the database
	$count = $rs_query->select('postmeta', 'COUNT(*)', array('_key' => 'filename', 'value' => array('LIKE', $filename.'%')));
	
	// Check whether the count is greater than zero
	if($count > 0) {
		// Split the filename into separate parts
		$file_parts = pathinfo($filename);
		
		do {
			// Construct a unique filename
			$unique_filename = $file_parts['filename'].'-'.($count + 1).'.'.$file_parts['extension'];
			
			// Increment the count
			$count++;
		} while($rs_query->selectRow('postmeta', 'COUNT(*)', array('_key' => 'filename', 'value' => $unique_filename)) > 0);
		
		// Return the unique filename
		return $unique_filename;
	} else {
		// Return the original filename
		return $filename;
	}
}

/**
 * Convert a string value or file size to bytes.
 * @since 2.1.3[a]
 *
 * @param string $val
 * @return string
 */
function getSizeInBytes($val): string {
	// Get the unit's multiple value
	$multiple = substr($val, -1, 1);
	
	// Trim the last character off of the value
	$val = substr($val, 0, strlen($val) - 1);
	
	switch($multiple) {
		case 'T': case 't':
			$val *= 1024;
		case 'G': case 'g':
			$val *= 1024;
		case 'M': case 'm':
			$val *= 1024;
		case 'K': case 'k':
			$val *= 1024;
	}
	
	// Return the value in bytes
	return $val;
}

/**
 * Convert a file size in bytes to its equivalent in kilobytes, metabytes, etc.
 * @since 2.1.0[a]
 *
 * @param int $bytes
 * @param int $decimals (optional; default: 1)
 * @return string
 */
function getFileSize($bytes, $decimals = 1): string {
	// Multiples for the units of bytes
	$multiples = 'BKMGTP';
	
	// Calculate the factor for each unit
	$factor = floor((strlen($bytes) - 1) / 3);
	
	// Return the converted file size
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).' '.$multiples[(int)$factor].($factor > 0 ? 'B' : '');
}

/*------------------------------------*\
    MISCELLANEOUS
\*------------------------------------*/

/**
 * Populate the database tables.
 * @since 1.7.0[a]
 *
 * @param array $user_data
 * @param array $settings_data
 */
function populateTables($user_data, $settings_data): void {
	// Populate the `user_roles` table
	populateUserRoles();
	
	// Populate the `user_privileges` and `user_relationships` tables
	populateUserPrivileges();
	
	// Populate the `users` table
	$user = populateUsers($user_data);
	
	// Populate the `posts` table
	$post = populatePosts($user);
	
	// Add the home page to the settings data array
	$settings_data['home_page'] = $post['home_page'];
	
	// Populate the `settings` table
	populateSettings($settings_data);
	
	// Populate the `taxonomies` table
	populateTaxonomies();
	
	// Populate the `terms` table
	populateTerms($post['blog_post']);
}

/**
 * Construct a status message.
 * @since 1.2.0[a]
 *
 * @param string $text
 * @param bool $success (optional; default: false)
 * @return string
 */
function statusMessage($text, $success = false): string {
	// Determine whether the status is success or failure
	if($success === true) {
		// Set the status message's class to success
		$class = 'success';
	} else {
		// Set the status message's class to failure
		$class = 'failure';
		
		// Check whether the provided text value matches one of the predefined cases
		switch($text) {
			case 'E': case 'e':
				// Status message for unexpected errors out of the user's control
				$text = 'An unexpected error occurred. Please contact the system administrator.';
				break;
			case 'R': case 'r':
				// Status message for required form fields that are left empty
				$text = 'Required fields cannot be left blank!';
				break;
		}
	}
	
	// Return the status message
	return '<div class="status-message '.$class.'">'.$text.'</div>';
}

/**
 * Enable pagination.
 * @since 1.2.1[a]
 *
 * @param int $current (optional; default: 1)
 * @param int $per_page (optional; default: 20)
 * @return array
 */
function paginate($current = 1, $per_page = 20): array {
	// Set the current page
	$page['current'] = $current;
	
	// Set the number of results per page
	$page['per_page'] = $per_page;
	
	// Check whether the current page is '1'
	if($page['current'] === 1) {
		// Set the starting value to zero
		$page['start'] = 0;
	} else {
		// Set the starting value to offset based on the number of results per page
		$page['start'] = ($page['current'] * $page['per_page']) - $page['per_page'];
	}
	
	// Return the page data
	return $page;
}

/**
 * Construct pager navigation.
 * @since 1.2.1[a]
 *
 * @param int $page
 * @param int $page_count
 */
function pagerNav($page, $page_count): void {
	// Fetch the query string from the URL
	$query_string = $_SERVER['QUERY_STRING'];
	
	// Split the query string into an array
	$query_params = explode('&', $query_string);
	
	// Loop through the query parameters
	for($i = 0; $i < count($query_params); $i++) {
		// Remove the parameter if it contains 'paged'
		if(str_contains($query_params[$i], 'paged'))
			unset($query_params[$i]);
	}
	
	// Put the query string back together
	$query_string = implode('&', $query_params);
	?>
	<div class="pager">
		<?php
		// Display the 'first page'/'previous page' buttons if the first page of results is not showing
		if($page > 1) {
			?>
			<a class="pager-nav button" href="<?php echo '?'.(!empty($query_string) ? $query_string.'&' : '').'paged=1'; ?>" title="First Page">&laquo;</a><a class="pager-nav button" href="<?php echo '?'.(!empty($query_string) ? $query_string.'&' : '').'paged='.($page - 1); ?>" title="Previous Page">&lsaquo;</a>
			<?php
		}
		
		// Display the current page
		if($page_count > 0) echo ' Page '.$page.' of '.$page_count.' ';
		
		// Display the 'next page'/'last page' buttons if the last page of results is not showing
		if($page < $page_count) {
			?>
			<a class="pager-nav button" href="<?php echo '?'.(!empty($query_string) ? $query_string.'&' : '').'paged='.($page + 1); ?>" title="Next Page">&rsaquo;</a><a class="pager-nav button" href="<?php echo '?'.(!empty($query_string) ? $query_string.'&' : '').'paged='.$page_count; ?>" title="Last Page">&raquo;</a>
			<?php
		}
		?>
	</div>
	<?php
}

/**
 * Construct an action link.
 * @since 1.2.0[b]{ss-01}
 *
 * @param string $action
 * @param null|string|array $args (optional; default: null)
 * @return string
 */
function actionLink($action, $args = null): string {
	// Check whether the args value is null
	if(!is_null($args)) {
		// Check whether the args value is an array and turn it into one if not
		if(!is_array($args)) $args = (array)$args;
		
		// Set the CSS classes
		$classes = $args['classes'] ?? '';
		
		// Remove the CSS classes from the array
		unset($args['classes']);
		
		// Set the data item, if provided
		$data_item = $args['data_item'] ?? '';
		
		// Remove the data item from the array
		unset($args['data_item']);
		
		// Set the caption
		$caption = $args['caption'] ?? ($args[0] ?? 'Action Link');
		
		// Remove the caption from the array
		unset($args['caption'], $args[0]);
		
		// Create a variable to hold a query string
		$query_string = '';
		
		// Loop through the remaining args and assign them to a query string
		foreach($args as $key => $value)
			$query_string .= $key.'='.$value.'&';
		
		// Return the action link
		return '<a'.(!empty($classes) ? ' class="'.$classes.'"' : '').' href="'.ADMIN_URI.'?'.($query_string ?? '').'action='.$action.'"'.(!empty($data_item) ? ' data-item="'.$data_item.'"' : '').'>'.$caption.'</a>';
	}
}

/**
 * Display information about each admin page's function.
 * @since 1.2.0[b]
 */
function adminInfo(): void {
	?>
	<div class="admin-info">
		<span>
			<?php
			// Fetch the current page
			$page = basename($_SERVER['PHP_SELF'], '.php');
			
			switch($page) {
				case 'posts':
					$type = substr($_SERVER['QUERY_STRING'], strpos($_SERVER['QUERY_STRING'], '=') + 1);
					
					switch($type) {
						case 'page':
							echo 'Pages are the basic building blocks of your website. They hold content such as text and images.';
							break;
						default:
							if(empty($type))
								echo 'Posts typically function as blog entries for your website.';
							else
								echo 'Custom post types can be used for a variety of purposes on your website.';
					}
					break;
				case 'categories':
					echo 'Categories are used to organize your blog posts to make it easier for readers to find a specific topic.';
					break;
				case 'media':
					echo 'Media can be used in page or post content, as user avatars, and even as logos for your website.';
					break;
				case 'terms':
					echo 'Taxonomies are used to organize your blog posts to make it easier for readers to find a specific topic.';
					break;
				case 'comments':
					echo 'Comments appear below your blog posts. They allow readers to engage with your content.';
					break;
				case 'themes':
					echo 'Themes allow you to customize the look of your website.';
					break;
				case 'menus':
					echo 'Menus are used to present links to important pages on your website.';
					break;
				case 'widgets':
					echo 'Widgets are helpful content blocks that can spruce up your web pages.';
					break;
				case 'users':
					echo 'Users have specific permissions and can log in to the admin dashboard.';
					break;
				case 'logins':
					$pagee = substr($_SERVER['QUERY_STRING'], strpos($_SERVER['QUERY_STRING'], '=') + 1);
					
					switch($pagee) {
						case 'blacklist':
							echo 'Logins and IP addresses can be blacklisted from being able to log in to your website.';
							break;
						case 'rules':
							echo 'Login rules allow you to set thresholds for when a login or IP address should be blacklisted.';
							break;
						default:
							echo 'Login attempts display all successful and failed logins to your website.';
					}
					break;
				case 'settings':
					echo 'User roles give users site-wide permissions and restrictions, and custom roles can also be made.';
					break;
			}
			?>
		</span>
		<i class="fas fa-info-circle" title="Information"></i>
	</div>
	<?php
}

/**
 * Check whether a post exists in the database.
 * @since 1.0.5[b]
 *
 * @param int $id
 * @return bool
 */
function postExists($id): bool {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the number of times the id appears in the database and return true if it does
	return $rs_query->selectRow('posts', 'COUNT(id)', array('id' => $id)) > 0;
}

/**
 * Construct a unique slug.
 * @since 1.0.9[b]
 *
 * @param string $slug
 * @param string $table
 * @return string
 */
function getUniqueSlug($slug, $table): string {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the number of conflicting slugs in the database
	$count = $rs_query->selectRow($table, 'COUNT(slug)', array('slug' => $slug));
	
	// Check whether the count is greater than zero
	if($count > 0) {
		do {
			// Try to construct a unique slug
			$unique_slug = $slug.'-'.($count + 1);
			
			// Increment the count
			$count++;
		} while($rs_query->selectRow($table, 'COUNT(slug)', array('slug' => $unique_slug)) > 0);
		
		// Return the unique slug
		return $unique_slug;
	} else {
		// Return the original slug
		return $slug;
	}
}

/**
 * Construct a unique post slug.
 * @since 1.0.9[b]
 *
 * @param string $slug
 * @return string
 */
function getUniquePostSlug($slug): string {
	return getUniqueSlug($slug, 'posts');
}

/**
 * Construct a unique term slug.
 * @since 1.0.9[b]
 *
 * @param string $slug
 * @return string
 */
function getUniqueTermSlug($slug): string {
	return getUniqueSlug($slug, 'terms');
}