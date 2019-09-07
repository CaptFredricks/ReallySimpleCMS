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
						// Check whether the current page is the 'Create Widget' page
						if($current === 'widgets') {
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
			
			// Check whether or not the count is zero
			if($count === 0) {
				// Redirect to the 'List Posts' page
				redirect('posts.php');
			} else {
				// Fetch the post from the database
				$post = $rs_query->selectRow('posts', 'type', array('id'=>$_GET['id']));
				
				// Check whether the current post is of type 'post', and set the current page accordingly
				if($post['type'] !== 'post') $current = $post['type'].'s';
			}
		}
	}
	
	// Return the current page
	return $current === 'index' ? 'dashboard' : $current;
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
		echo '<link rel="stylesheet" href="'.trailingSlash(ADMIN_STYLES).$stylesheet.(!empty($version) ? '?version='.$version : '').'">';
	else
		return '<link rel="stylesheet" href="'.trailingSlash(ADMIN_STYLES).$stylesheet.(!empty($version) ? '?version='.$version : '').'">';
}

/**
 * Fetch an admin script.
 * @since 1.2.0[a]
 *
 * @param string $script
 * @param string $version (optional; default: VERSION)
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getAdminScript($script, $version = VERSION, $echo = true) {
	if($echo)
		echo '<script src="'.trailingSlash(ADMIN_SCRIPTS).$script.(!empty($version) ? '?version='.$version : '').'"></script>';
	else
		return '<script src="'.trailingSlash(ADMIN_SCRIPTS).$script.(!empty($version) ? '?version='.$version : '').'"></script>';
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
	$usermeta = array('first_name'=>'', 'last_name'=>'', 'avatar'=>0);
	
	// Insert the user metadata into the database
	foreach($usermeta as $key=>$value)
		$rs_query->insert('usermeta', array('user'=>$user, '_key'=>$key, 'value'=>$value));
	
	// Create a sample page
	$post['home_page'] = $rs_query->insert('posts', array('title'=>'Sample Page', 'author'=>$user, 'date'=>'NOW()', 'content'=>'This is just a sample page to get you started.', 'status'=>'published', 'slug'=>'sample-page', 'type'=>'page'));
	
	// Create a sample blog post
	$post['blog_post'] = $rs_query->insert('posts', array('title'=>'Sample Blog Post', 'author'=>$user, 'date'=>'NOW()', 'content'=>'This is your first blog post. Feel free to remove this text and replace it with your own.', 'status'=>'published', 'slug'=>'sample-post'));
	
	// Post metadata
	$postmeta = array(
		'home_page'=>array('title'=>'Sample Page', 'description'=>'Just a simple meta description for your sample page.'),
		'blog_post'=>array('title'=>'Sample Blog Post', 'description'=>'Just a simple meta description for your first blog post.')
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
	$taxonomies = array('category');
	
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
 * @return null
 */
function adminNavMenuItem($item = array(), $submenu = array()) {
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
	} // Otherwise, check whether or not the submenu is empty
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
		<a href="<?php echo $item_link; ?>"><?php echo $item_caption; ?></a>
		<?php
		// Check whether or not the submenu parameters have been specified
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
	// Return if $bars is not countable
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
	
	$max_count = max($stats);
	$num = ceil($max_count / 25);
	$num *= 5;
	
	$content = '<input type="hidden" id="max-ct" value="'.($num * 5).'">';
	$content .= '<div id="stats-graph"><ul class="graph-y">';
	
	for($i = 5; $i >= 0; $i--)
		$content .= '<li><span class="value">'.($i * $num).'</span></li>';
	
	$content .= '</ul><ul class="graph-content">';
	$j = 0;
	
	foreach($bars as $bar) {
		$content .= '<li style="width:'.(1 / count($bars) * 100).'%;"><a class="bar" href="'.$links[$j].'" title="'.ucfirst(isset($bar[2]) ? $bar[2].'s' : $bar[0]).': '.$stats[$j].($stats[$j] === 1 ? ' entry' : ' entries').'">'.$stats[$j].'</a></li>';
		$j++;
	}
	
	$content .= '<ul class="graph-overlay">';
	
	for($k = 5; $k >= 0; $k--)
		$content .= '<li></li>';
	
	$content .= '</ul></ul><ul class="graph-x">';
	$l = 0;
	
	foreach($bars as $bar) {
		$content .= '<li style="width:'.(1 / count($bars) * 100).'%;"><a class="value" href="'.$links[$l].'" title="'.ucfirst(isset($bar[2]) ? $bar[2].'s' : $bar[0]).': '.$stats[$l].($stats[$l] === 1 ? ' entry' : ' entries').'">'.ucfirst(isset($bar[2]) ? $bar[2].'s' : $bar[0]).'</a></li>';
		$l++;
	}
	
	$content .= '</ul><span class="graph-y-label">Count</span><span class="graph-x-label">Category</span></div>';
	
	echo $content;
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
 * @param int $current
 * @param int $page_count
 * @return string
 */
function pagerNav($current, $page_count) {
	return '<div class="pager">'.($current > 1 ? '<a class="pager-nav button" href="?paged=1" title="First Page">&laquo;</a><a class="pager-nav button" href="?paged='.($current - 1).'" title="Previous Page">&lsaquo;</a>' : '').($page_count > 0 ? ' Page '.$current.' of '.$page_count.' ' : '').($current < $page_count ? '<a class="pager-nav button" href="?paged='.($current + 1).'" title="Next Page">&rsaquo;</a><a class="pager-nav button" href="?paged='.$page_count.'" title="Last Page">&raquo;</a>' : '').'</div>';
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
	foreach($items as $item)
		$row .= '<th>'.$item.'</th>';
	
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
 * @param string $tag
 * @param array $args (optional; default: null)
 * @return string
 */
function formTag($tag, $args = null) {
	switch($tag) {
		case 'input':
			// Construct an input tag
			$tag = '<input type="'.($args['type'] ?? 'text').'"'.(!empty($args['id']) ? ' id="'.$args['id'].'"' : '').(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').(!empty($args['name']) ? ' name="'.$args['name'].'"' : '').(!empty($args['maxlength']) ? ' maxlength="'.$args['maxlength'].'"' : '').(!empty($args['value']) || (isset($args['value']) && $args['value'] == 0) ? ' value="'.$args['value'].'"' : '').(!empty($args['placeholder']) ? ' placeholder="'.$args['placeholder'].'"' : '').(!empty($args['*']) ? $args['*'] : '').'>';
			break;
		case 'select':
			// Construct a select tag
			$tag = '<select'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').(!empty($args['name']) ? ' name="'.$args['name'].'"' : '').'>'.($args['content'] ?? '').'</select>';
			break;
		case 'textarea':
			// Construct a textarea tag
			$tag = '<textarea'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').(!empty($args['name']) ? ' name="'.$args['name'].'"' : '').(!empty($args['cols']) ? ' cols="'.$args['cols'].'"' : '').(!empty($args['rows']) ? ' rows="'.$args['rows'].'"' : '').'>'.($args['content'] ?? '').'</textarea>';
			break;
		case 'img':
			// Construct an img tag
			$tag = '<img'.(!empty($args['src']) ? ' src="'.$args['src'].'"' : '').(!empty($args['width']) ? ' width="'.$args['width'].'"' : '').'>';
			break;
		case 'hr':
			// Construct an hr tag
			$tag = '<hr'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').'>';
			break;
		case 'br':
			// Construct a br tag
			$tag = '<br'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').'>';
			break;
		case 'label':
			// Construct a label tag
			$tag = '<label'.(!empty($args['id']) ? ' id="'.$args['id'].'"' : '').(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').(!empty($args['for']) ? ' for="'.$args['for'].'"' : '').'>'.$args['content'].'</label>';
			break;
		default:
			// Don't construct a tag
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
 * Construct a form row.
 * @since 1.1.2[a]
 *
 * @param string|array $label (optional; default: '')
 * @param array $args (optional; unlimited)
 * @return string
 */
function formRow($label = '', ...$args) {
	// Check whether or not the label is empty
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
		
		// Check wether any args have been provided
		if(count($args) > 0) {
			// Check whether or not the args are a multidimensional array
			if(count($args) !== count($args, COUNT_RECURSIVE)) {
				// Loop through the args
				foreach($args as $arg) {
					// Fetch the arg's HTML tag
					$tag = $arg['tag'];
					
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
		
		// Check wether any args have been provided
		if(count($args) > 0) {
			// Check whether or not the args are a multidimensional array
			if(count($args) !== count($args, COUNT_RECURSIVE)) {
				// Loop through the args
				foreach($args as $arg) {
					// Fetch the arg's HTML tag
					$tag = $arg['tag'];
					
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
	
	// Fetch the taxonomy id from the database
	$taxonomy = $rs_query->selectRow('taxonomies', 'id', array('name'=>$name));
	
	// Return the taxonomy id
	return $taxonomy['id'] ?? 0;
}

/**
 * Generate a random password.
 * @since 1.3.0[a]
 *
 * @param int $length (optional; default: 15)
 * @param bool $special_chars (optional; default: true)
 * @param bool $extra_special_chars (optional; default: false)
 * @return string
 */
function generatePassword($length = 15, $special_chars = true, $extra_special_chars = false) {
	// Regular characters
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	
	// If desired, add the special characters
	if($special_chars) $chars .= '!@#$%^&*()';
	
	// If desired, add the extra special characters
	if($extra_special_chars) $chars .= '-_ []{}<>~`+=,.;:/?|';
	
	// Empty password
	$password = '';
	
	// Generate a random password
	for($i = 0; $i < $length; $i++)
		$password .= substr($chars, rand(0, strlen($chars) - 1), 1);
	
	// Return the password
	return $password;
}