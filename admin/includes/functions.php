<?php
/**
 * Administrative functions.
 * @since 1.0.2[a]
 */

// Path to the admin stylesheets directory
if(!defined('ADMIN_STYLES')) define('ADMIN_STYLES', ADMIN.INC.'/css');

// Path to the admin scripts directory
if(!defined('ADMIN_SCRIPTS')) define('ADMIN_SCRIPTS', ADMIN.INC.'/js');

// Autoload classes
spl_autoload_register(function($class_name) {
	require_once trailingSlash(PATH.ADMIN.INC).'class-'.strtolower($class_name).'.php';
});

/**
 * Fetch the current admin page.
 * @since 1.5.4[a]
 *
 * @return string
 */
function getCurrentPage() {
	// Extend the Query class
	global $rs_query;
	
	// Extract the current page from the PHP filename
	$current = basename($_SERVER['PHP_SELF'], '.php');
	
	// Check whether the server request contains a query string
	if(!empty($_SERVER['QUERY_STRING'])) {
		// Fetch the query string and separate it by its parameters
		$query_params = explode('&', $_SERVER['QUERY_STRING']);
		
		// Loop through the query parameters
		foreach($query_params as $query_param) {
			// Check whether the query parameter contains 'type'
			if(strpos($query_param, 'type') !== false) {
				// Set the current page 
				$current = substr($query_param, strpos($query_param, '=') + 1).'s';
			}
			
			// Check whether the query parameter contains 'action'
			if(strpos($query_param, 'action') !== false) {
				// Fetch the current action
				$action = substr($query_param, strpos($query_param, '=') + 1);
				
				switch($action) {
					case 'create':
					case 'upload':
						// Check whether the current page is the 'Create Menu' page or the 'Create Widget' page
						if($current === 'menus' || $current === 'widgets') {
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
			if(strpos($query_param, 'page=') !== false) {
				// Fetch the current page
				$page = substr($query_param, strpos($query_param, '=') + 1);
				
				// Replace any underscores with dashes
				$current = str_replace('_', '-', $page);
				break;
			}
		}
		
		// Check whether the current page is the 'Edit Post' or 'Edit Page' page
		if($current === 'posts' && isset($_GET['id'])) {
			// Fetch the number of times the post appears in the database
			$count = $rs_query->selectRow('posts', 'COUNT(*)', array('id'=>$_GET['id']));
			
			// Check whether the count is zero
			if($count === 0) {
				// Redirect to the 'List Posts' page
				redirect('posts.php');
			} else {
				// Fetch the post's type from the database
				$type = $rs_query->selectField('posts', 'type', array('id'=>$_GET['id']));
				
				// Check whether the current post is of type 'post' and set the current page accordingly
				if($type !== 'post') $current = $type.'s';
			}
		}
	}
	
	// Return the current page
	return $current === 'index' ? 'dashboard' : $current;
}

/**
 * Fetch an admin script file.
 * @since 1.2.0[a]
 *
 * @param string $script
 * @param string $version (optional; default: VERSION)
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getAdminScript($script, $version = VERSION, $echo = true) {
	if($echo)
		echo '<script src="'.trailingSlash(ADMIN_SCRIPTS).$script.(!empty($version) ? '?v='.$version : '').'"></script>';
	else
		return '<script src="'.trailingSlash(ADMIN_SCRIPTS).$script.(!empty($version) ? '?v='.$version : '').'"></script>';
}

/**
 * Fetch an admin stylesheet.
 * @since 1.2.0[a]
 *
 * @param string $stylesheet
 * @param string $version (optional; default: VERSION)
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getAdminStylesheet($stylesheet, $version = VERSION, $echo = true) {
	if($echo)
		echo '<link href="'.trailingSlash(ADMIN_STYLES).$stylesheet.(!empty($version) ? '?v='.$version : '').'" rel="stylesheet">';
	else
		return '<link href="'.trailingSlash(ADMIN_STYLES).$stylesheet.(!empty($version) ? '?v='.$version : '').'" rel="stylesheet">';
}

/**
 * Load all admin header scripts and stylesheets.
 * @since 2.0.7[a]
 *
 * @return null
 */
function adminHeaderScripts() {
	// Extend the user's session data
	global $session;
	
	// Buttons stylesheet
	getStylesheet('buttons.css');
	
	// Admin stylesheet
	getAdminStylesheet('style.css');
	
	// Check whether the user has a custom admin theme selected
	if($session['theme'] !== 'default') {
		// File path for the admin theme stylesheet
		$file_path = 'admin-themes/'.$session['theme'].'.css';
		
		// Check whether the stylesheet exists
		if(file_exists(trailingSlash(PATH.CONT).$file_path)) {
			// Admin theme stylesheet
			getThemeStylesheet($file_path);
		}
	}
	
	// Font Awesome icons stylesheet
	getStylesheet('fa-icons.css', '5.11.2');
	
	// JQuery library
	getScript('jquery.min.js', '3.4.1');
}

/**
 * Load all admin footer scripts and stylesheets.
 * @since 2.0.7[a]
 *
 * @return null
 */
function adminFooterScripts() {
	// Admin script file
	getAdminScript('script.js');
}

/**
 * Construct a status message.
 * @since 1.2.0[a]
 *
 * @param string $text
 * @param bool $success (optional; default: false)
 * @return string
 */
function statusMessage($text, $success = false) {
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
 * Populate the database tables.
 * @since 1.6.4[a]
 *
 * @param array $user_data
 * @param array $settings_data
 * @return null
 */
function populateTables($user_data, $settings_data) {
	// Extend the Query class
	global $rs_query;
	
	// Create an array of user roles
	$roles = array('User', 'Editor', 'Moderator', 'Administrator');
	
	// Insert the user roles into the database
	foreach($roles as $role) {
		if($role === 'User')
			$default_user_role = $rs_query->insert('user_roles', array('name'=>$role, '_default'=>'yes'));
		elseif($role === 'Administrator')
			$admin_user_role = $rs_query->insert('user_roles', array('name'=>$role, '_default'=>'yes'));
		else
			$rs_query->insert('user_roles', array('name'=>$role, '_default'=>'yes'));
	}
	
	// Create an array of admin pages (for privileges)
	$admin_pages = array('pages', 'posts', 'categories', 'comments', 'media', 'menus', 'widgets', 'users', 'settings', 'user_roles');
	
	// Create an array of user privileges
	$privileges = array('can_view_', 'can_create_', 'can_edit_', 'can_delete_');
	
	// Loop through the admin pages
	foreach($admin_pages as $admin_page) {
		// Loop through the user privileges
		foreach($privileges as $privilege) {
			switch($admin_page) {
				case 'comments':
					// Skip comments for now (will be added later)
					continue 2;
				case 'media':
					// Change the 'can_create_' privilege to 'can_upload_'
					if($privilege === 'can_create_') $privilege = 'can_upload_';
					
					// Insert the user privilege into the database
					$rs_query->insert('user_privileges', array('name'=>$privilege.$admin_page));
					break;
				case 'settings':
					// Skip 'can_view_', 'can_create_', and 'can_delete_' for settings
					if($privilege === 'can_view_' || $privilege === 'can_create_' || $privilege === 'can_delete_') continue 2;
				default:
					// Insert the user privilege into the database
					$rs_query->insert('user_privileges', array('name'=>$privilege.$admin_page));
			}
		}
	}
	
	/**
	 * List of privileges:
	 * 1=>'can_view_pages', 2=>'can_create_pages', 3=>'can_edit_pages', 4=>'can_delete_pages',
	 * 5=>'can_view_posts', 6=>'can_create_posts', 7=>'can_edit_posts', 8=>'can_delete_posts',
	 * 9=>'can_view_categories', 10=>'can_create_categories', 11=>'can_edit_categories', 12=>'can_delete_categories',
	 * 13=>'can_view_media', 14=>'can_upload_media', 15=>'can_edit_media', 16=>'can_delete_media',
	 * 17=>'can_view_menus', 18=>'can_create_menus', 19=>'can_edit_menus', 20=>'can_delete_menus',
	 * 21=>'can_view_widgets', 22=>'can_create_widgets', 23=>'can_edit_widgets', 24=>'can_delete_widgets',
	 * 25=>'can_view_users', 26=>'can_create_users', 27=>'can_edit_users', 28=>'can_delete_users',
	 * 29=>'can_edit_settings',
	 * 30=>'can_view_user_roles', 31=>'can_create_user_roles', 32=>'can_edit_user_roles', 33=>'can_delete_user_roles'
	 */
	
	// Fetch all user roles from the database
	$roles = $rs_query->select('user_roles', 'id', '', 'id');
	
	// Loop through the user roles
	foreach($roles as $role) {
		switch($role['id']) {
			case 1:
				// Set the privileges for the 'user' role
				$privileges = array();
				break;
			case 2:
				// Set the privileges for the 'editor' role
				$privileges = array(1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15, 17, 18, 19, 21, 22, 23, 25);
				break;
			case 3:
				// Set the privileges for the 'moderator' role
				$privileges = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28);
				break;
			case 4:
				// Set the privileges for the 'administrator' role
				$privileges = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33);
				break;
		}
		
		// Insert the user relationships into the database
		foreach($privileges as $privilege)
			$rs_query->insert('user_relationships', array('role'=>$role['id'], 'privilege'=>$privilege));
	}
	
	// Encrypt password
	$hashed_password = password_hash($user_data['password'], PASSWORD_BCRYPT, array('cost'=>10));
	
	// Create an admin user
	$user = $rs_query->insert('users', array('username'=>$user_data['username'], 'password'=>$hashed_password, 'email'=>$user_data['email'], 'registered'=>'NOW()', 'role'=>$admin_user_role));
	
	// User metadata
	$usermeta = array('first_name'=>'', 'last_name'=>'', 'avatar'=>0, 'theme'=>'default');
	
	// Insert the user metadata into the database
	foreach($usermeta as $key=>$value)
		$rs_query->insert('usermeta', array('user'=>$user, '_key'=>$key, 'value'=>$value));
	
	// Create a sample page
	$post['home_page'] = $rs_query->insert('posts', array('title'=>'Sample Page', 'author'=>$user, 'date'=>'NOW()', 'content'=>'This is just a sample page to get you started.', 'status'=>'published', 'slug'=>'sample-page', 'type'=>'page'));
	
	// Create a sample blog post
	$post['blog_post'] = $rs_query->insert('posts', array('title'=>'Sample Blog Post', 'author'=>$user, 'date'=>'NOW()', 'content'=>'This is your first blog post. Feel free to remove this text and replace it with your own.', 'status'=>'published', 'slug'=>'sample-post'));
	
	// Post metadata
	$postmeta = array(
		'home_page'=>array('title'=>'Sample Page', 'description'=>'Just a simple meta description for your sample page.', 'feat_image'=>0),
		'blog_post'=>array('title'=>'Sample Blog Post', 'description'=>'Just a simple meta description for your first blog post.', 'feat_image'=>0)
	);
	
	// Loop through the post metadata
	foreach($postmeta as $metadata) {
		// Insert the post metadata into the database
		foreach($metadata as $key=>$value)
			$rs_query->insert('postmeta', array('post'=>$post[key($postmeta)], '_key'=>$key, 'value'=>$value));
		
		// Move the array pointer to the next element
		next($postmeta);
	}
	
	// Settings
	$settings = array('site_title'=>$settings_data['site_title'], 'description'=>'A new ReallySimpleCMS website!', 'site_url'=>$settings_data['site_url'], 'admin_email'=>$settings_data['admin_email'], 'default_user_role'=>$default_user_role, 'home_page'=>$post['home_page'], 'do_robots'=>$settings_data['do_robots']);
	
	// Insert the settings into the database
	foreach($settings as $name=>$value)
		$rs_query->insert('settings', array('name'=>$name, 'value'=>$value));
	
	// Create an array of taxonomies
	$taxonomies = array('category', 'nav_menu');
	
	// Insert the taxonomies into the database
	foreach($taxonomies as $taxonomy)
		$rs_query->insert('taxonomies', array('name'=>$taxonomy));
	
	// Insert the terms into the database
	$term = $rs_query->insert('terms', array('name'=>'Uncategorized', 'slug'=>'uncategorized', 'taxonomy'=>getTaxonomyId('category'), 'count'=>1));
	
	// Insert the term relationships into the database
	$rs_query->insert('term_relationships', array('term'=>$term, 'post'=>$post['blog_post']));
}

/**
 * Create a nav menu item for the admin navigation.
 * @since 1.2.5[a]
 *
 * @param array $item (optional; default: array())
 * @param array $submenu (optional; default: array())
 * @param string|array $icon (optional; default: null)
 * @return null
 */
function adminNavMenuItem($item = array(), $submenu = array(), $icon = null) {
	// Fetch the current page
	$current = getCurrentPage();
	
	// Return if the menu item is not an array
	if(!empty($item) && !is_array($item)) return;
	
	// Fetch the menu item id
	$item_id = $item['id'] ?? 'menu-item';
	
	// Fetch the menu item link
	$item_link = isset($item['link']) ? trailingSlash(ADMIN).$item['link'] : 'javascript:void(0)';
	
	// Fetch the menu item caption
	$item_caption = $item['caption'] ?? ucwords(str_replace('-', ' ', $item_id));
	
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
				?>
			</ul>
			<?php
		}
		?>
	</li>
	<?php
}

/**
 * Get statistics for a specific set of table entries.
 * @since 1.2.5[a]
 *
 * @param string $table
 * @param string $field (optional; default: '')
 * @param string $value (optional; default: '')
 * @return int
 */
function getStatistics($table, $field = '', $value = '') {
	// Extend the Query class
	global $rs_query;
	
	// Fetch the entry counts for the specified tables
	if(empty($field) || empty($value))
		return $rs_query->select($table, 'COUNT(*)');
	else
		return $rs_query->select($table, 'COUNT(*)', array($field=>$value));
}

/**
 * Create and display a bar graph of site statistics.
 * @since 1.2.4[a]
 *
 * @param array $bars
 * @return null
 */
function statsBarGraph($bars) {
	// Return if the bars array is not countable
	if(!is_countable($bars)) return;
	
	// Create empty arrays for the stats and links
	$stats = $links = array();
	
	// Loop through the bars
	foreach($bars as $bar) {
		// Return if the bar is not an array
		if(!is_array($bar)) return;
		
		// Check whether multiple arguments have been supplied for the bar
		if(count($bar) === 3) {
			$stats[] = getStatistics($bar[0], $bar[1], $bar[2]);
			$links[] = $bar[0].'.php?'.$bar[1].'='.$bar[2];
		} else {
			$stats[] = getStatistics($bar[0]);
			$links[] = $bar[0].'.php';
		}
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
			// Set a counter
			$j = 0;
			
			// Loop through the bars
			foreach($bars as $bar) {
				?>
				<li style="width: <?php echo 1 / count($bars) * 100; ?>%;">
					<a class="bar" href="<?php echo $links[$j]; ?>" title="<?php echo ucfirst(isset($bar[2]) ? $bar[2].'s' : $bar[0]); ?>: <?php echo $stats[$j].($stats[$j] === 1 ? ' entry' : ' entries'); ?>"><?php echo $stats[$j]; ?></a>
				</li>
				<?php
				// Increment the counter
				$j++;
			}
			?>
			<ul class="graph-overlay">
				<?php
				// Loop through the overlay items
				for($k = 5; $k >= 0; $k--) {
					?>
					<li></li>
					<?php
				}
				?>
			</ul>
		</ul>
		<ul class="graph-x">
			<?php
			// Set a counter
			$l = 0;
			
			// Loop through the bars
			foreach($bars as $bar) {
				?>
				<li style="width: <?php echo 1 / count($bars) * 100; ?>%;">
					<a class="value" href="<?php echo $links[$l]; ?>" title="<?php echo ucfirst(isset($bar[2]) ? $bar[2].'s' : $bar[0]); ?>: <?php echo $stats[$l].($stats[$l] === 1 ? ' entry' : ' entries'); ?>"><?php echo ucfirst(isset($bar[2]) ? $bar[2].'s' : $bar[0]); ?></a>
				</li>
				<?php
				// Increment the counter
				$l++;
			}
			?>
		</ul>
		<span class="graph-y-label">Count</span>
		<span class="graph-x-label">Category</span>
	</div>
	<?php
}

/**
 * Enable pagination.
 * @since 1.2.1[a]
 *
 * @param int $current (optional; default: 1)
 * @param int $per_page (optional; default: 20)
 * @return array
 */
function paginate($current = 1, $per_page = 20) {
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
 * @return null
 */
function pagerNav($page, $page_count) {
	// Fetch the query string from the URL
	$query_string = $_SERVER['QUERY_STRING'];
	
	// Split the query string into an array
	$query_params = explode('&', $query_string);
	
	// Loop through the query parameters
	for($i = 0; $i < count($query_params); $i++) {
		// Remove the parameter if it contains 'paged'
		if(strpos($query_params[$i], 'paged') !== false)
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
 * Construct a table header row.
 * @since 1.2.1[a]
 *
 * @param array $items
 * @return string
 */
function tableHeaderRow($items) {
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
function tableRow(...$cells) {
	if(!empty($cells)) return '<tr>'.implode('', $cells).'</tr>';
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
function tableCell($data, $class = '', $colspan = 1) {
	return '<td'.(!empty($class) ? ' class="'.$class.'"' : '').($colspan > 1 ? ' colspan="'.$colspan.'"' : '').'>'.$data.'</td>';
}

/**
 * Construct a form HTML tag.
 * @since 1.2.0[a]
 *
 * @param string $tag_name
 * @param array $args (optional; default: null)
 * @return string
 */
function formTag($tag_name, $args = null) {
	// Create an array of property names from the args array
	$props = !is_null($args) ? array_keys($args) : array();
	
	// Create an array of whitelisted tags with their properties
	$whitelisted_props = array(
		'br'=>array('class'),
		'div'=>array('id', 'class', 'content'),
		'hr'=>array('class'),
		'i'=>array('class'),
		'img'=>array('id', 'src', 'width'),
		'input'=>array('type', 'id', 'class', 'name', 'maxlength', 'value', 'placeholder', '*'),
		'label'=>array('id', 'class', 'for', 'content'),
		'select'=>array('class', 'name', 'content'),
		'span'=>array('id', 'class', 'title', 'content'),
		'textarea'=>array('class', 'name', 'cols', 'rows', 'content')
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
		
		// (!empty($args['value']) || (isset($args['value']) && $args['value'] == 0) ? ' value="'.$args['value'].'"' : '')
		
		// Check whether any args have been provided
		if(!is_null($args)) {
			// Loop through the args
			foreach($args as $key=>$value) {
				// Check whether the property has been whitelisted and it does not equal 'content'
				if(in_array($key, $whitelisted_props[$tag_name], true) && $key !== 'content') {
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
 * Construct a form row.
 * @since 1.1.2[a]
 *
 * @param string|array $label (optional; default: '')
 * @param array $args (optional; unlimited)
 * @return string
 */
function formRow($label = '', ...$args) {
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

/**
 * Upload media to the media library.
 * @since 2.1.6[a]
 *
 * @param array $data
 * @return string
 */
function uploadMediaFile($data) {
	// Extend the Query class
	global $rs_query;
	
	// Make sure a file has been selected
	if(empty($data['name']))
		return statusMessage('A file must be selected for upload!');
	
	// Create an array of accepted MIME types
	$accepted_mime = array('image/jpeg', 'image/png', 'image/gif', 'image/x-icon', 'audio/mp3', 'audio/ogg', 'video/mp4', 'text/plain');
	
	// Check whether the uploaded file is among the accepted MIME types
	if(!in_array($data['type'], $accepted_mime, true))
		return statusMessage('The file could not be uploaded.');
	
	// File path for the uploads directory
	$file_path = PATH.UPLOADS;
	
	// Check whether the uploads directory exists, and create it if not
	if(!file_exists($file_path)) mkdir($file_path);
	
	// Convert the filename to all lowercase, replace spaces with hyphens, and remove all special characters
	$filename = preg_replace('/[^\w.\-]/i', '', str_replace(' ', '-', strtolower($data['name'])));
	
	// Check whether the filename is already in the database and make it unique if so
	if(filenameExists($filename))
		$filename = getUniqueFilename($filename);
	
	// Strip off the filename's extension for the post's slug
	$slug = pathinfo($filename, PATHINFO_FILENAME);
	
	// Move the uploaded file to the uploads directory
	move_uploaded_file($data['tmp_name'], trailingSlash(PATH.UPLOADS).$filename);
	
	// Create an array to hold the media's metadata
	$mediameta = array('filename'=>$filename, 'mime_type'=>$data['type'], 'alt_text'=>'');
	
	// Fetch the user's data
	$session = getOnlineUser($_COOKIE['session']);
	
	// Insert the new media into the database
	$insert_id = $rs_query->insert('posts', array('title'=>'New media', 'author'=>$session['id'], 'date'=>'NOW()', 'content'=>'', 'slug'=>$slug, 'type'=>'media'));
	
	// Insert the media's metadata into the database
	foreach($mediameta as $key=>$value)
		$rs_query->insert('postmeta', array('post'=>$insert_id, '_key'=>$key, 'value'=>$value));
	
	// Return a success message and a hidden field with the media's id
	return statusMessage('Upload successful!', true).(in_array($data['type'], array('image/jpeg', 'image/png', 'image/gif'), true) ? '<div class="hidden" data-field="id">'.$insert_id.'</div><div class="hidden" data-field="filename">'.trailingSlash(UPLOADS).$filename.'</div>' : '');
}

/**
 * Load the media library.
 * @since 2.1.2[a]
 *
 * @param bool $image_only (optional; default: false)
 * @return null
 */
function loadMedia($image_only = false) {
	// Extend the Query class
	global $rs_query;
	
	// Fetch all media from the database
	$mediaa = $rs_query->select('posts', '*', array('type'=>'media'), 'date', 'DESC');
	
	// Loop through the media
	foreach($mediaa as $media) {
		// Fetch the media's metadata from the database
		$mediameta = $rs_query->select('postmeta', array('_key', 'value'), array('post'=>$media['id']));
		
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
			$image_mime = array('image/jpeg', 'image/png', 'image/gif');
			
			// Check whether the current media item is an image and skip to the next item if not
			if(!in_array($meta['mime_type'], $image_mime, true)) continue;
		}
		?>
		<div class="media-item-wrap">
			<div class="media-item">
				<div class="thumb-wrap">
					<img class="thumb" src="<?php echo trailingSlash(UPLOADS).$meta['filename']; ?>">
				</div>
				<div>
					<div class="hidden" data-field="id"><?php echo $media['id']; ?></div>
					<div class="hidden" data-field="thumb"><img src="<?php echo trailingSlash(UPLOADS).$meta['filename']; ?>" width="150"></div>
					<div class="hidden" data-field="title"><?php echo $media['title']; ?></div>
					<div class="hidden" data-field="date"><?php echo formatDate($media['date'], 'd M Y @ g:i A'); ?></div>
					<div class="hidden" data-field="filename"><a href="<?php echo trailingSlash(UPLOADS).$meta['filename']; ?>" target="_blank"><?php echo $meta['filename']; ?></a></div>
				</div>
			</div>
		</div>
		<?php
	}
}

/**
 * Check whether a filename already exists in the database.
 * @since 2.1.0[a]
 *
 * @param string $filename
 * @return bool
 */
function filenameExists($filename) {
	// Extend the Query class
	global $rs_query;
	
	// Return true if the filename appears in the database
	return $rs_query->select('postmeta', 'COUNT(*)', array('_key'=>'filename', 'value'=>array('LIKE', $filename.'%'))) > 0;
}

/**
 * Make a filename unique by adding a number to the end of it.
 * @since 2.1.0[a]
 *
 * @param string $filename
 * @return string
 */
function getUniqueFilename($filename) {
	// Extend the Query class
	global $rs_query;
	
	// Fetch the number of conflicting filenames in the database
	$count = $rs_query->select('postmeta', 'COUNT(*)', array('_key'=>'filename', 'value'=>array('LIKE', $filename.'%')));
	
	// Split the filename into separate parts
	$file_parts = pathinfo($filename);
	
	do {
		// Construct a unique filename
		$unique_filename = $file_parts['filename'].'-'.($count + 1).'.'.$file_parts['extension'];
		
		// Increment the count
		$count++;
	} while($rs_query->selectRow('postmeta', 'COUNT(*)', array('_key'=>'filename', 'value'=>$unique_filename)) > 0);
	
	// Return the unique filename
	return $unique_filename;
}

/**
 * Convert a string value or file size to bytes.
 * @since 2.1.3[a]
 *
 * @param string $val
 * @return string
 */
function getSizeInBytes($val) {
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
function getFileSize($bytes, $decimals = 1) {
	// Multiples for the units of bytes
	$multiples = 'BKMGTP';
	
	// Calculate the factor for each unit
	$factor = floor((strlen($bytes) - 1) / 3);
	
	// Return the converted file size
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).' '.$multiples[(int)$factor].($factor > 0 ? 'B' : '');
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
 * Fetch a taxonomy's id.
 * @since 1.5.0[a]
 *
 * @param string $name
 * @return int
 */
function getTaxonomyId($name) {
	// Extend the Query class
	global $rs_query;
	
	// Fetch the taxonomy's id from the database
	$id = (int)$rs_query->selectField('taxonomies', 'id', array('name'=>$name));
	
	// Return the taxonomy's id
	return $id ?? 0;
}

/**
 * Fetch the URL of a specified media item.
 * @since 2.1.5[a]
 *
 * @param int $id
 * @return string
 */
function getMedia($id) {
	// Extend the Query class
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