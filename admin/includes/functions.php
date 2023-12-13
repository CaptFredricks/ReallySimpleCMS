<?php
/**
 * Administrative functions.
 * @since 1.0.2[a]
 */

// Path to the admin stylesheets directory
if(!defined('ADMIN_STYLES')) define('ADMIN_STYLES', RES . '/css' . ADMIN);

// Path to the admin scripts directory
if(!defined('ADMIN_SCRIPTS')) define('ADMIN_SCRIPTS', RES . '/js' . ADMIN);

// Path to the admin themes directory
if(!defined('ADMIN_THEMES')) define('ADMIN_THEMES', CONT . '/admin-themes');

// Current admin page URI
if(!defined('ADMIN_URI')) define('ADMIN_URI', $_SERVER['PHP_SELF']);

/**
 * Autoload a class.
 * @since 1.0.2[a]
 *
 * @param string $class -- The name of the class.
 */
spl_autoload_register(function(string $class) {
	$file = PATH . ADMIN . INC . getClassFilename($class);
	
	if(file_exists($file)) require $file;
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
	global $rs_query, $post_types, $taxonomies;
	
	$current_page = basename($_SERVER['PHP_SELF'], '.php');
	
	if(!empty($_SERVER['QUERY_STRING'])) {
		$query_params = explode('&', $_SERVER['QUERY_STRING']);
		
		foreach($query_params as $query_param) {
			if(str_contains($query_param, 'type')) {
				$current_page = str_replace(' ', '_',
					$post_types[substr($query_param, strpos($query_param, '=') + 1)]['labels']['name_lowercase']
				);
			}
			
			if(str_contains($query_param, 'taxonomy')) {
				$current_page = str_replace(' ', '_',
					$taxonomies[substr($query_param, strpos($query_param, '=') + 1)]['labels']['name_lowercase']
				);
			}
			
			if(str_contains($query_param, 'action')) {
				// Fetch the current action
				$action = substr($query_param, strpos($query_param, '=') + 1);
				
				$exclude = array('themes', 'menus', 'widgets');
				
				foreach($taxonomies as $taxonomy)
					$exclude[] = str_replace(' ', '_', $taxonomy['labels']['name_lowercase']);
				
				switch($action) {
					case 'create':
					case 'upload':
						if(in_array($current_page, $exclude, true)) {
							break;
						} else {
							$current_page .= '-' . $action;
							break;
						}
				}
			}
			
			if(str_contains($query_param, 'page=')) {
				// Fetch the current page
				$page = substr($query_param, strpos($query_param, '=') + 1);
				
				$current_page = str_replace('_', '-', $page);
				break;
			}
		}
		
		if($current_page === 'posts' && isset($_GET['id'])) {
			$count = $rs_query->selectRow('posts', 'COUNT(*)', array('id' => $_GET['id']));
			
			if($count === 0) {
				redirect('posts.php');
			} else {
				$type = $rs_query->selectField('posts', 'type', array('id' => $_GET['id']));
				
				$current_page = str_replace(' ', '_', $post_types[$type]['labels']['name_lowercase']);
			}
		}
		elseif($current_page === 'terms' && isset($_GET['id'])) {
			$count = $rs_query->selectRow('terms', 'COUNT(*)', array('id' => $_GET['id']));
			
			if($count === 0) {
				redirect('categories.php');
			} else {
				$tax_id = $rs_query->selectField('terms', 'taxonomy', array('id' => $_GET['id']));
				$taxonomy = $rs_query->selectField('taxonomies', 'name', array('id' => $tax_id));
				
				$current_page = str_replace(' ', '_', $taxonomies[$taxonomy]['labels']['name_lowercase']);
			}
		}
	}
	
	return $current_page === 'index' ? 'dashboard' : $current_page;
}

/**
 * Fetch an admin page's title.
 * @since 2.1.11[a]
 *
 * @return string
 */
function getPageTitle(): string {
	global $rs_query, $post_types, $taxonomies;
	
	// Perform some checks based on what the current page is
	if(basename($_SERVER['PHP_SELF']) === 'index.php')
		$title = 'Dashboard';
	elseif(isset($_GET['type'])) {
		$title = $post_types[$_GET['type']]['label'] ?? 'Posts';
	} elseif(basename($_SERVER['PHP_SELF']) === 'posts.php' && isset($_GET['action']) &&
		$_GET['action'] === 'edit') {
			
		$type = $rs_query->selectField('posts', 'type', array('id' => $_GET['id']));
		
		$title = ucwords(str_replace(array('_', '-'), ' ', $type.'s'));
	} elseif(isset($_GET['taxonomy'])) {
		$title = $taxonomies[$_GET['taxonomy']]['label'] ?? 'Terms';
	} elseif(isset($_GET['page']) && $_GET['page'] === 'user_roles') {
		$title = ucwords(str_replace('_', ' ', $_GET['page']));
	} else {
		$title = ucfirst(basename($_SERVER['PHP_SELF'], '.php'));
	}
	
	return $title;
}

/**
 * Output an admin script file.
 * @since 1.2.0[a]
 *
 * @param string $script -- The script to load.
 * @param string $version (optional) -- The script's version.
 */
function adminScript(string $script, string $version = CMS_VERSION): void {
	echo '<script src="' . slash(ADMIN_SCRIPTS) . $script .
		(!empty($version) ? '?v=' . $version : '') . '"></script>';
}

/**
 * Output an admin stylesheet.
 * @since 1.2.0[a]
 *
 * @param string $stylesheet -- The stylesheet to load.
 * @param string $version (optional) -- The stylesheet's version.
 */
function adminStylesheet(string $stylesheet, string $version = CMS_VERSION): void {
	echo '<link href="' . slash(ADMIN_STYLES) . $stylesheet .
		(!empty($version) ? '?v=' . $version : '') . '" rel="stylesheet">';
}

/**
 * Output an admin theme's stylesheet.
 * @since 2.3.1[a]
 *
 * @param string $stylesheet -- The stylesheet to load.
 * @param string $version (optional) -- The stylesheet's version.
 */
function adminThemeStylesheet(string $stylesheet, string $version = CMS_VERSION): void {
	echo '<link href="' . slash(ADMIN_THEMES) . $stylesheet . (!empty($version) ? '?v=' .
		$version : '') . '" rel="stylesheet">';
}

/**
 * Load all admin header scripts and stylesheets.
 * @since 2.0.7[a]
 */
function adminHeaderScripts(): void {
	global $session;
	
	$debug = false;
	if(defined('DEBUG_MODE') && DEBUG_MODE) $debug = true;
	
	// Button stylesheet
	putStylesheet('button' . ($debug ? '' : '.min') . '.css');
	
	// Admin stylesheet
	adminStylesheet('style' . ($debug ? '' : '.min') . '.css');
	
	if($session['theme'] !== 'default') {
		$filename = $session['theme'] . '.css';
		
		// Admin theme stylesheet
		if(file_exists(slash(PATH . ADMIN_THEMES) . $filename))
			adminThemeStylesheet($filename);
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
	&copy; <?php echo date('Y'); ?> <a href="https://github.com/CaptFredricks/ReallySimpleCMS"><?php echo CMS_ENGINE; ?></a> &bull; Created by <a href="https://jacefincham.com/" target="_blank" rel="noreferrer noopener">Jace Fincham</a>
	<?php
}

/**
 * Display the CMS version on the admin dashboard.
 * @since 1.2.0[a]
 */
function RSVersion(): void {
	echo 'Version ' . CMS_VERSION . ' (&beta;)';
}

/**
 * Create a menu item for the admin navigation.
 * @since 1.2.5[a]
 *
 * @param array $item (optional) -- The menu item.
 * @param array $submenu (optional) -- The submenu, if applicable.
 * @param mixed $icon (optional) -- The menu icon.
 */
function adminNavMenuItem(array $item = array(), array $submenu = array(), mixed $icon = null): void {
	$current_page = getCurrentPage();
	
	if(!empty($item) && !is_array($item)) return;
	
	$item_id = $item['id'] ?? 'menu-item';
	$item_link = isset($item['link']) ? slash(ADMIN) . $item['link'] : 'javascript:void(0)';
	$item_caption = $item['caption'] ?? ucwords(str_replace(array('_', '-'), ' ', $item_id));
	
	if($item_id === $current_page) {
		$item_class = 'current-menu-item';
	} elseif(!empty($submenu)) {
		foreach($submenu as $sub_item) {
			if(!empty($sub_item['id']) && $sub_item['id'] === $current_page) {
				$item_class = 'child-is-current';
				break;
			}
		}
	}
	?>
	<li<?php echo !empty($item_class) ? ' class="' . $item_class . '"' : ''; ?>>
		<a href="<?php echo $item_link; ?>">
			<?php
			// Nav menu icon
			if(!empty($icon)) {
				if(is_array($icon)) {
					switch($icon[1]) {
						case 'regular':
							?>
							<i class="fa-regular fa-<?php echo $icon[0]; ?>"></i>
							<?php
							break;
						case 'solid':
						default:
							?>
							<i class="fa-solid fa-<?php echo $icon[0]; ?>"></i>
							<?php
					}
				} else {
					?>
					<i class="fa-solid fa-<?php echo $icon; ?>"></i>
					<?php
				}
			} else {
				?>
				<i class="fa-solid fa-code-branch"></i>
				<?php
			}
			?>
			<span><?php echo $item_caption; ?></span>
		</a>
		<?php
		if(!empty($submenu)) {
			if(!is_array($submenu)) return;
			?>
			<ul class="submenu">
				<?php
				foreach($submenu as $sub_item) {
					if(!empty($sub_item) && !is_array($sub_item)) break;
					
					if(!empty($sub_item)) {
						$sub_item_id = $sub_item['id'] ?? $item_id;
						$sub_item_link = isset($sub_item['link']) ? slash(ADMIN) .
							$sub_item['link'] : 'javascript:void(0)';
						$sub_item_caption = $sub_item['caption'] ?? ucwords(str_replace('-', ' ', $sub_item_id));
						?>
						<li<?php echo $sub_item_id === $current_page ? ' class="current-submenu-item"' : ''; ?>>
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
	global $post_types, $taxonomies;
	
	// Dashboard
	adminNavMenuItem(array('id' => 'dashboard', 'link' => 'index.php'), array(), 'gauge-high');
	
	// Pages/posts/media/CPTs
	foreach($post_types as $post_type) {
		if(!$post_type['show_in_admin_menu']) continue;
		
		$id = str_replace(' ', '_', $post_type['labels']['name_lowercase']);
		
		if(!empty($post_type['taxonomy']) && array_key_exists($post_type['taxonomy'], $taxonomies))
			$tax_id = str_replace(' ', '_', $taxonomies[$post_type['taxonomy']]['labels']['name_lowercase']);
		
		if(userHasPrivilege('can_view_' . $id)) {
			adminNavMenuItem(array('id' => $id), array( // Submenu
				array( // List <post_type>
					'link' => $post_type['menu_link'],
					'caption' => $post_type['labels']['list_items']
				),
				(userHasPrivilege(($post_type['name'] === 'media' ? 'can_upload_media' : 'can_create_' . $id)) ?
				array( // Create <post_type>
					'id' => $id === 'media' ? $id . '-upload' : $id . '-create',
					'link' => $post_type['menu_link'] . ($post_type['name'] === 'media' ? '?action=upload' :
						($post_type['name'] === 'post' ? '?action=create' : '&action=create')),
					'caption' => $post_type['labels']['create_item']
				) : null),
				(!empty($post_type['taxonomy']) && array_key_exists($post_type['taxonomy'], $taxonomies) &&
					userHasPrivilege('can_view_' . $tax_id) &&
					$taxonomies[$post_type['taxonomy']]['show_in_admin_menu'] ?
				array( // Taxonomy
					'id' => $tax_id,
					'link' => $taxonomies[$post_type['taxonomy']]['menu_link'],
					'caption' => $taxonomies[$post_type['taxonomy']]['labels']['list_items']
				) : null)
			), $post_type['menu_icon']);
		}
	}
	
	// Comments
	if(userHasPrivilege('can_view_comments')) {
		adminNavMenuItem(array(
			'id' => 'comments',
			'link' => 'comments.php'
		), array(), array('comments', 'regular'));
	}
	
	// Customization (themes/menus/widgets)
	if(userHasPrivileges(array('can_view_themes', 'can_view_menus', 'can_view_widgets'), 'OR')) {
		adminNavMenuItem(array('id' => 'customization'), array( // Submenu
			(userHasPrivilege('can_view_themes') ? array(
				'id' => 'themes',
				'link' => 'themes.php',
				'caption' => 'List Themes'
			) : null),
			(userHasPrivilege('can_view_menus') ? array(
				'id' => 'menus',
				'link' => 'menus.php',
				'caption' => 'List Menus'
			) : null),
			(userHasPrivilege('can_view_widgets') ? array(
				'id' => 'widgets',
				'link' => 'widgets.php',
				'caption' => 'List Widgets'
			) : null)
		), 'palette');
	}
	
	// Users/user profile
	adminNavMenuItem(array('id' => 'users'), array( // Submenu
		(userHasPrivilege('can_view_users') ? array(
			'link' => 'users.php',
			'caption' => 'List Users'
		) : null),
		(userHasPrivilege('can_create_users') ? array(
			'id' => 'users-create',
			'link' => 'users.php?action=create',
			'caption' => 'Create User'
		) : null),
		array('id' => 'profile', 'link' => 'profile.php', 'caption' => 'Your Profile')
	), 'users');
	
	// Logins (attempts/blacklist/rules)
	if(userHasPrivileges(array(
		'can_view_login_attempts',
		'can_view_login_blacklist',
		'can_view_login_rules'
	), 'OR')) {
		adminNavMenuItem(array('id' => 'logins'), array( // Submenu
			(userHasPrivilege('can_view_login_attempts') ? array(
				'link' => 'logins.php',
				'caption' => 'Attempts'
			) : null),
			(userHasPrivilege('can_view_login_blacklist') ? array(
				'id' => 'blacklist',
				'link' => 'logins.php?page=blacklist',
				'caption' => 'Blacklist'
			) : null),
			(userHasPrivilege('can_view_login_rules') ? array(
				'id' => 'rules',
				'link' => 'logins.php?page=rules',
				'caption' => 'Rules'
			) : null)
		), 'right-to-bracket');
	}
	
	// Settings (general/design/user roles)
	if(userHasPrivileges(array('can_edit_settings', 'can_view_user_roles'), 'OR')) {
		adminNavMenuItem(array('id' => 'settings'), array( // Submenu
			(userHasPrivilege('can_edit_settings') ? array(
				'link' => 'settings.php',
				'caption' => 'General'
			) : null),
			(userHasPrivilege('can_edit_settings') ? array(
				'id' => 'design',
				'link' => 'settings.php?page=design',
				'caption' => 'Design'
			) : null),
			(userHasPrivilege('can_view_user_roles') ? array(
				'id' => 'user-roles',
				'link' => 'settings.php?page=user_roles',
				'caption' => 'User Roles'
			) : null)
		), 'gears');
	}
	
	// About the CMS
	adminNavMenuItem(array('id' => 'about', 'link' => 'about.php'), array(), 'circle-info');
}

/*------------------------------------*\
    DASHBOARD
\*------------------------------------*/

/**
 * Get the count for posts of status `draft`.
 * @since 1.3.8[b]
 *
 * @param string $type (optional) -- The post's type.
 * @return int
 */
function ctDraft(string $type = 'post'): int {
	global $rs_query;
	
	return $rs_query->select('posts', 'COUNT(id)', array(
		'status' => 'draft',
		'type' => $type
	));
}

/**
 * Get statistics for a specific set of table entries.
 * @since 1.2.5[a]
 *
 * @param string $table -- The table name.
 * @param string $col (optional) -- The column to query.
 * @param string $value (optional) -- The column's value.
 * @return int
 */
function getStatistics(string $table, string $col = '', string $value = ''): int {
	global $rs_query;
	
	if(empty($col) || empty($value))
		return $rs_query->select($table, 'COUNT(*)');
	else
		return $rs_query->select($table, 'COUNT(*)', array($col => $value));
}

/**
 * Create and display a bar graph of site statistics.
 * @since 1.2.4[a]
 */
function statsBarGraph(): void {
	global $post_types, $taxonomies;
	
	$bars = $stats = array();
	
	foreach($post_types as $key => $value) {
		if(!$post_types[$key]['show_in_stats_graph']) continue;
		
		$bars[$key] = $value;
		$bars[$key]['stats'] = getStatistics('posts', 'type', $bars[$key]['name']);
		$stats[] = $bars[$key]['stats'];
	}
	
	foreach($taxonomies as $key => $value) {
		if(!$taxonomies[$key]['show_in_stats_graph']) continue;
		
		$bars[$key] = $value;
		$bars[$key]['stats'] = getStatistics('terms', 'taxonomy', getTaxonomyId($bars[$key]['name']));
		$stats[] = $bars[$key]['stats'];
	}
	
	// Max count value
	$max_count = max($stats);
	$num = ceil($max_count / 25);
	$num *= 5;
	
	echo domTag('input', array(
		'type' => 'hidden',
		'id' => 'max-ct',
		'value' => $num * 5
	));
	?>
	<div id="stats-graph">
		<ul class="graph-y">
			<?php
			// Y axis values
			for($i = 5; $i >= 0; $i--) {
				echo domTag('li', array(
					'content' => domTag('span', array(
						'class' => 'value',
						'content' => $i * $num
					))
				));
			}
			?>
		</ul>
		<ul class="graph-content">
			<?php
			// Bars
			foreach($bars as $bar) {
				echo domTag('li', array(
					'style' => 'width: ' . (1 / count($bars) * 100) . '%;',
					'content' => domTag('a', array(
						'class' => 'bar',
						'href' => $bar['menu_link'],
						'title' => $bar['label'] . ': ' . $bar['stats'] .
							($bar['stats'] === 1 ? ' entry' : ' entries'),
						'content' => $bar['stats']
					))
				));
			}
			?>
			<ul class="graph-overlay">
				<?php
				// Overlay items
				for($j = 5; $j >= 0; $j--)
					echo domTag('li');
				?>
			</ul>
		</ul>
		<ul class="graph-x">
			<?php
			// X axis values
			foreach($bars as $bar) {
				echo domTag('li', array(
					'style' => 'width: ' . (1 / count($bars) * 100) . '%;',
					'content' => domTag('a', array(
						'class' => 'value',
						'href' => $bar['menu_link'],
						'title' => $bar['label'] . ': ' . $bar['stats'] .
							($bar['stats'] === 1 ? ' entry' : ' entries'),
						'content' => $bar['label']
					))
				));
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
 * @param string $name -- The widget's name.
 */
function dashboardWidget(string $name): void {
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
					$comment_statuses = array('approved', 'pending');
					
					foreach($comment_statuses as $comment_status) {
						$count = $rs_query->select('comments', 'COUNT(*)', array(
							'status' => $comment_status
						));
						
						echo domTag('li', array(
							'content' => domTag('a', array(
								'href' => '/admin/comments.php?status=' . $comment_status,
								'content' => ucfirst($comment_status)
							)) . ': ' . domTag('strong', array(
								'class' => 'value',
								'content' => $count
							))
						));
					}
					?>
				</ul>
				<?php
				break;
			case 'users':
				?>
				<h2>Users</h2>
				<ul>
					<?php
					$user_statuses = array('online', 'offline');
					
					foreach($user_statuses as $user_status) {
						if($user_status === 'online')
							$session = array('IS NOT NULL');
						elseif($user_status === 'offline')
							$session = array('IS NULL');
						
						$count = $rs_query->select('users', 'COUNT(*)', array(
							'session' => $session
						));
						
						echo domTag('li', array(
							'content' => domTag('a', array(
								'href' => '/admin/users.php?status=' . $user_status,
								'content' => ucfirst($user_status)
							)) . ': ' . domTag('strong', array(
								'class' => 'value',
								'content' => $count
							))
						));
					}
					?>
				</ul>
				<?php
				break;
			case 'logins':
				?>
				<h2>Logins</h2>
				<ul>
					<?php
					$login_statuses = array('success', 'failure', 'blacklisted');
					
					foreach($login_statuses as $login_status) {
						if($login_status === 'blacklisted') {
							$count = $rs_query->select('login_blacklist', 'COUNT(*)');
							
							echo domTag('li', array(
								'content' => domTag('a', array(
									'href' => '/admin/logins.php?page=blacklist',
									'content' => ucfirst($login_status)
								)) . ': ' . domTag('strong', array(
									'class' => 'value',
									'content' => $count
								))
							));
						} else {
							$count = $rs_query->select('login_attempts', 'COUNT(*)', array(
								'status' => $login_status
							));
							
							echo domTag('li', array(
								'content' => domTag('a', array(
									'href' => '/admin/logins.php?status=' . $login_status,
									'content' => ($login_status === 'success' ? 'Successful' : 'Failed')
								)) . ': ' . domTag('strong', array(
									'class' => 'value',
									'content' => $count
								))
							));
						}
					}
					?>
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
 * Construct a table row. Also known as an HTML `tr` tag.
 * @since 1.4.0[a]
 *
 * @param string|array $cells (optional) -- The table cells.
 * @return string
 */
function tableRow(string|array ...$cells): string {
	return '<tr>' . (!empty($cells) ? implode('', $cells) : '') . '</tr>';
}

/**
 * Construct a table cell, either of the header or data variety.
 * @since 1.2.1[a]
 *
 * @param string $tag -- The HTML tag.
 * @param string $content -- The cell's content.
 * @param string $class (optional) -- The cell's CSS class(es).
 * @param int $colspan (optional) -- The cell's colspan.
 * @param int $rowspan (optional) -- The cell's rowspan.
 * @return string
 */
function tableCell(string $tag, string $content, string $class = '', int $colspan = 1, int $rowspan = 1): string {
	if($tag !== 'th' && $tag !== 'td') $tag = 'td';
	
	return '<' . $tag . ' class="column' . (!empty($class) ? ' ' . $class : '') . '"' .
		($colspan > 1 ? ' colspan="' . $colspan . '"' : '') .
		($rowspan > 1 ? ' rowspan="' . $rowspan . '"' : '') . '>' . $content . '</' . $tag . '>';
}

/**
 * Construct a table header cell. Also known as an HTML `th` tag.
 * @since 1.3.2[b]
 *
 * @param string $content -- The cell's content.
 * @param string $class (optional) -- The cell's CSS class(es).
 * @param int $colspan (optional) -- The cell's colspan.
 * @param int $rowspan (optional) -- The cell's rowspan.
 * @return string
 */
function thCell(string $content, string $class = '', int $colspan = 1, int $rowspan = 1): string {
	return tableCell('th', $content, $class, $colspan, $rowspan);
}

/**
 * Construct a table data cell. Also known as an HTML `td` tag.
 * @since 1.3.2[b]
 *
 * @param string $content -- The cell's content.
 * @param string $class (optional) -- The cell's CSS class(es).
 * @param int $colspan (optional) -- The cell's colspan.
 * @param int $rowspan (optional) -- The cell's rowspan.
 * @return string
 */
function tdCell(string $content, string $class = '', int $colspan = 1, int $rowspan = 1): string {
	return tableCell('td', $content, $class, $colspan, $rowspan);
}

/**
 * Construct a table header row.
 * @since 1.2.1[a]
 *
 * @param array $items -- The row items.
 * @return string
 */
function tableHeaderRow(array $items): string {
	if(count(array_filter(array_keys($items), 'is_string')) > 0) {
		foreach($items as $key => $value)
			$row[] = thCell($value, 'col-' . $key);
	} else {
		foreach($items as $item) $row[] = thCell($item);
	}
	
	return tableRow(implode('', $row));
}

/**
 * Construct a form HTML tag. Phased out in favor of DOMtags.
 * @since 1.2.0[a]
 * @deprecated since 1.3.11[b]
 *
 * @param string $tag_name -- The tag's name.
 * @param array|null $args (optional) -- The tag's args.
 * @return string
 */
function formTag(string $tag_name, ?array $args = null): string {
	deprecated();
	
	$props = !is_null($args) ? array_keys($args) : array();
	
	$always_whitelist = array('id', 'class', 'title');
	
	$whitelisted_props = array(
		'a' => array_merge($always_whitelist, array('href', 'target', 'rel')),
		'br' => $always_whitelist,
		'button' => $always_whitelist,
		'div' => array_merge($always_whitelist, array('style')),
		'fieldset' => array(),
		'hr' => $always_whitelist,
		'i' => $always_whitelist,
		'img' => array_merge($always_whitelist, array('src', 'width')),
		'input' => array_merge(
			array('type'),
			$always_whitelist,
			array('name', 'maxlength', 'value', 'placeholder', 'checked', 'disabled')
		),
		'label' => array_merge($always_whitelist, array('for')),
		'option' => array('value', 'selected'),
		'select' => array_merge($always_whitelist, array('name')),
		'span' => array_merge($always_whitelist, array('style')),
		'textarea' => array_merge($always_whitelist, array('name', 'cols', 'rows'))
	);
	
	$whitelisted_tags = array_keys($whitelisted_props);
	
	if(in_array($tag_name, $whitelisted_tags, true)) {
		$tag = '<' . $tag_name;
		
		if($tag_name === 'input')
			if(!in_array('type', $props, true)) $tag .= ' type="text"';
		
		if(!is_null($args)) {
			foreach($args as $key => $value) {
				if(in_array($key, $whitelisted_props[$tag_name], true) ||
					str_starts_with($key, 'data-')) {
						
					switch($key) {
						case 'checked':
						case 'disabled':
						case 'selected':
							$tag .= $value ? ' ' . $key : '';
							break;
						default:
							$tag .= ' ' . $key . '="' . $value . '"';
					}
				}
				if($tag_name === 'input' && $key === '*') {
					$tag .= ' ' . $value;
				}
			}
		}
		
		$tag .= '>';
		
		$self_closing = array('br', 'hr', 'img', 'input');
		
		if(!in_array($tag_name, $self_closing, true)) {
			$tag .= $args['content'] ?? '';
			$tag .= '</' . $tag_name . '>';
		}
	} else {
		$tag = '';
	}
	
	if(!empty($args['label'])) {
		$label = '<label' . (!empty($args['label']['id']) ? ' id="' . $args['label']['id'] .
			'"' : '') . (!empty($args['label']['class']) ? ' class="' . $args['label']['class'] .
			'"' : '') . '>';
		
		$content = (!empty($args['label']['content']) ? $args['label']['content'] : '') . '</label>';
		
		// Put everything together
		$tag = $label . $tag . $content;
	}
	
	return $tag;
}

/**
 * Alias for the formTag function.
 * @since 1.2.7[b]
 * @deprecated since 1.3.11[b]
 *
 * @param string $tag_name -- The tag's name.
 * @param array|null $args (optional) -- The tag's args.
 * @return string
 */
function tag(string $tag_name, ?array $args = null): string {
	deprecated();
	
	return formTag($tag_name, $args);
}

/**
 * Construct a form row.
 * @since 1.1.2[a]
 *
 * @param string|array $label (optional) -- The row's label.
 * @param string|array $args (optional) -- The row's args.
 * @return string
 */
function formRow(string|array $label = '', string|array ...$args): string {
	if(!empty($label)) {
		if(is_array($label)) {
			$required = array_pop($label);
			$label = implode('', $label);
		}
		
		for($i = 0; $i < count($args); $i++) {
			// Break out of the loop if the `name` key is found
			if(is_array($args[$i]) && array_key_exists('name', $args[$i])) break;
		}
		
		$row_label = domTag('label', array(
			'for' => (!empty($args[$i]['name']) ? $args[$i]['name'] : ''),
			'content' => $label . ' ' . (!empty($required) && $required === true ?
				domTag('span', array(
					'class' => 'required',
					'content' => '*'
				)) : ''
			)
		));
		
		if(count($args) > 0) {
			// Check whether the args are a multidimensional array
			if(count($args) !== count($args, COUNT_RECURSIVE)) {
				foreach($args as $arg) {
					$tag = array_shift($arg);
					
					$row_content[] = domTag($tag, $arg);
				}
			} else {
				foreach($args as $arg) $row_content[] = $arg;
			}
		}
		
		return tableRow(thCell($row_label), tdCell(implode('', $row_content)));
	} else {
		if(count($args) > 0) {
			// Check whether the args are a multidimensional array
			if(count($args) !== count($args, COUNT_RECURSIVE)) {
				foreach($args as $arg) {
					$tag = array_shift($arg);
					
					$row_content[] = domTag($tag, $arg);
				}
			} else {
				foreach($args as $arg) $row_content[] = $arg;
			}
		}
		
		return tableRow(tdCell(implode('', $row_content), '', 2));
	}
}

/**
 * Record search form.
 * @since 1.3.7[b]
 *
 * @param array $args (optional) -- The args.
 */
function recordSearch(array $args = array()): void {
	button(array(
		'id' => 'search-toggle',
		'title' => 'Record search',
		'label' => '<i class="fa-solid fa-magnifying-glass"></i>'
	));
	?>
	<form class="search-form" action="" method="get">
		<?php
		foreach($args as $key => $value) {
			if(!empty($args[$key])) {
				echo domTag('input', array(
					'type' => 'hidden',
					'name' => $key,
					'value' => $args[$key]
				));
			}
		}
		
		// Search field
		echo domTag('input', array(
			'id' => 'record-search',
			'name' => 'search'
		));
		
		// Submit button
		echo domTag('input', array(
			'type' => 'submit',
			'class' => 'submit-input button',
			'value' => 'Search'
		));
		?>
	</form>
	<?php
}

/*------------------------------------*\
    MEDIA
\*------------------------------------*/

/**
 * Upload media to the media library.
 * @since 2.1.6[a]
 *
 * @param array $data -- The submission data.
 * @return string
 */
function uploadMediaFile(array $data): string {
	global $rs_query;
	
	if(empty($data['name']))
		return exitNotice('A file must be selected for upload!', -1);
	
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
	
	if(!in_array($data['type'], $accepted_mime, true))
		return exitNotice('The file could not be uploaded.', -1);
	
	$basepath = PATH . UPLOADS;
	
	if(!file_exists($basepath)) mkdir($basepath);
	
	$year = date('Y');
	
	if(!file_exists(slash($basepath) . $year))
		mkdir(slash($basepath) . $year);
	
	$filename = str_replace(array('  ', ' ', '_'), '-', sanitize($data['name'], '/[^\w.-]/'));
	$filename = getUniqueFilename($filename);
	
	// Strip off the filename's extension for the post's slug
	$slug = pathinfo($filename, PATHINFO_FILENAME);
	
	// Get a unique slug
	$slug = getUniquePostSlug($slug);
	
	$filepath = slash($year) . $filename;
	
	// Move the file to the uploads directory
	move_uploaded_file(
		$data['tmp_name'],
		slash($basepath) . $filepath
	);
	
	$mediameta = array(
		'filepath' => $filepath,
		'mime_type' => $data['type'],
		'alt_text' => ''
	);
	
	$title = ucwords(str_replace('-', ' ', $slug));
	$session = getOnlineUser($_COOKIE['session']);
	
	$insert_id = $rs_query->insert('posts', array(
		'title' => $title,
		'author' => $session['id'],
		'date' => 'NOW()',
		'modified' => 'NOW()',
		'slug' => $slug,
		'type' => 'media'
	));
	
	foreach($mediameta as $key => $value) {
		$rs_query->insert('postmeta', array(
			'post' => $insert_id,
			'datakey' => $key,
			'value' => $value
		));
	}
	
	// Check whether the media is an image
	if(in_array($data['type'], array('image/jpeg', 'image/png', 'image/gif', 'image/x-icon'), true)) {
		list($width, $height) = getimagesize(slash($basepath) . $filepath);
		
		$status_msg = domTag('div', array(
			// ID
			'class' => 'hidden',
			'data-field' => 'id',
			'content' => $insert_id
		)) . domTag('div', array(
			// Title
			'class' => 'hidden',
			'data-field' => 'title',
			'content' => $title
		)) . domTag('div', array(
			// Filepath
			'class' => 'hidden',
			'data-field' => 'filepath',
			'content' => slash(UPLOADS) . $filepath
		)) . domTag('div', array(
			// MIME type
			'class' => 'hidden',
			'data-field' => 'mime_type',
			'content' => $data['type']
		)) . domTag('div', array(
			// Width
			'class' => 'hidden',
			'data-field' => 'width',
			'content' => $width
		));
	}
	
	return exitNotice('Upload successful!') . ($status_msg ?? '');
}

/**
 * Load the media library.
 * @since 2.1.2[a]
 *
 * @param bool $image_only (optional) -- Whether to display only images.
 */
function loadMedia(bool $image_only = false): void {
	global $rs_query;
	
	$mediaa = $rs_query->select('posts', '*', array('type' => 'media'), 'date', 'DESC');
	
	if(empty($mediaa)) {
		?>
		<p style="margin: 1em;">The media library is empty!</p>
		<?php
	} else {
		foreach($mediaa as $media) {
			$mediameta = $rs_query->select('postmeta',
				array('datakey', 'value'),
				array('post' => $media['id'])
			);
			
			$meta = array();
			
			foreach($mediameta as $metadata) {
				$values = array_values($metadata);
				
				for($i = 0; $i < count($metadata); $i += 2)
					$meta[$values[$i]] = $values[$i + 1];
			}
			
			if($image_only) {
				$image_mime = array('image/jpeg', 'image/png', 'image/gif', 'image/x-icon');
				
				if(!in_array($meta['mime_type'], $image_mime, true)) continue;
				
				list($width, $height) = getimagesize(
					slash(PATH . UPLOADS) . $meta['filepath']
				);
			}
			?>
			<div class="media-item-wrap">
				<div class="media-item">
					<div class="thumb-wrap">
						<?php echo getMedia($media['id'], array('class' => 'thumb')); ?>
					</div>
					<div>
						<?php
						$file = pathinfo($meta['filepath']);
						
						echo domTag('div', array(
							// ID
							'class' => 'hidden',
							'data-field' => 'id',
							'content' => $media['id']
						)) . domTag('div', array(
							// Thumb
							'class' => 'hidden',
							'data-field' => 'thumb',
							'content' => getMedia($media['id'])
						)) . domTag('div', array(
							// Title
							'class' => 'hidden',
							'data-field' => 'title',
							'content' => $media['title']
						)) . domTag('div', array(
							// Date
							'class' => 'hidden',
							'data-field' => 'date',
							'content' => formatDate($media['date'], 'd M Y @ g:i A')
						)) . domTag('div', array(
							// Filepath
							'class' => 'hidden',
							'data-field' => 'filepath',
							'content' => mediaLink($media['id'], array(
								'link_text' => $file['basename'],
								'newtab' => 1
							))
						)) . domTag('div', array(
							// MIME type
							'class' => 'hidden',
							'data-field' => 'mime_type',
							'content' => $meta['mime_type']
						)) . domTag('div', array(
							// Alt text
							'class' => 'hidden',
							'data-field' => 'alt_text',
							'content' => $meta['alt_text']
						)) . domTag('div', array(
							// Width
							'class' => 'hidden',
							'data-field' => 'width',
							'content' => ($width ?? 150)
						));
						?>
					</div>
				</div>
			</div>
			<?php
		}
	}
}

/**
 * Construct a link to a media item.
 * @since 1.2.9[b]
 *
 * @param int $id -- The media's id.
 * @param array $args (optional) -- The args.
 * @return string
 */
function mediaLink(int $id, array $args = array()): string {
	global $rs_query;
	
	$modified = $rs_query->selectField('posts', 'modified', array('id' => $id));
	
	$src = getMediaSrc($id) . '?cached=' . formatDate($modified, 'YmdHis');
	
	if(!empty($args['newtab']) && $args['newtab'] === 1)
		$newtab = 1;
	else
		$newtab = 0;
	
	if(empty($args['link_text']))
		$args['link_text'] = $rs_query->selectField('posts', 'title', array('id' => $id));
	
	return domTag('a', array(
		'class' => (!empty($args['class']) ? $args['class'] : ''),
		'href' => $src,
		'target' => ($newtab ? '_blank' : ''),
		'rel' => ($newtab ? 'noreferrer noopener' : ''),
		'content' => $args['link_text']
	));
}

/**
 * Construct a unique filename.
 * @since 2.1.0[a]
 *
 * @param string $filename -- The filename.
 * @return string
 */
function getUniqueFilename(string $filename): string {
	global $rs_query;
	
	$count = $rs_query->select('postmeta', 'COUNT(*)', array(
		'datakey' => 'filepath',
		'value' => array('LIKE', '%' . $filename . '%')
	));
	
	if($count > 0) {
		$file_parts = pathinfo($filename);
		
		do {
			$unique_filename = $file_parts['filename'] . '-' . ($count + 1) . '.' .
				$file_parts['extension'];
			$count++;
		} while($rs_query->selectRow('postmeta', 'COUNT(*)', array(
			'datakey' => 'filepath',
			'value' => array('LIKE', '%' . $unique_filename)
		)) > 0);
		
		return $unique_filename;
	} else {
		return $filename;
	}
}

/**
 * Convert a string value or file size to bytes.
 * @since 2.1.3[a]
 *
 * @param string $val -- The value as a string.
 * @return string
 */
function getSizeInBytes(string $val): string {
	$multiple = substr($val, -1, 1);
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
	
	return $val;
}

/**
 * Convert a file size in bytes to its equivalent in kilobytes, metabytes, etc.
 * @since 2.1.0[a]
 *
 * @param int $bytes -- The number of bytes.
 * @param int $decimals (optional) -- The number of decimal places.
 * @return string
 */
function getFileSize(int $bytes, int $decimals = 1): string {
	$multiples = 'BKMGTP';
	$factor = floor((strlen($bytes) - 1) / 3);
	
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $multiples[(int)$factor] .
		($factor > 0 ? 'B' : '');
}

/*------------------------------------*\
    MISCELLANEOUS
\*------------------------------------*/

/**
 * Populate the database tables.
 * @since 1.7.0[a]
 *
 * @param array $user_data -- The user data.
 * @param array $settings_data -- The settings data.
 */
function populateTables(array $user_data, array $settings_data): void {
	// Populate `user_roles`
	populateUserRoles();
	
	// Populate `user_privileges` and `user_relationships`
	populateUserPrivileges();
	
	// Populate `users`
	$user = populateUsers($user_data);
	
	// Populate `posts`
	$post = populatePosts($user);
	
	$settings_data['home_page'] = $post['home_page'];
	
	// Populate `settings`
	populateSettings($settings_data);
	
	// Populate `taxonomies`
	populateTaxonomies();
	
	// Populate `terms`
	populateTerms($post['blog_post']);
}

/**
 * Display a notice.
 * @since 1.2.0[a]
 *
 * @param string $text -- The notice's text.
 * @param int $status (optional) -- The notice's status.
 * @param bool $can_dismiss (optional) -- Whether the notice can be dismissed.
 * @param bool $is_exit (optional) -- Whether the notice is an exit status.
 * @return string
 */
function notice(string $text, int $status = 2, bool $can_dismiss = true, bool $is_exit = false): string {
	$rs_notice = new Notice;
	
	return $rs_notice->msg($text, $status, $can_dismiss, $is_exit);
}

/**
 * Display an exit status notice.
 * @since 1.3.8[b]
 *
 * @param string $text -- The notice's text.
 * @param int $status (optional) -- The notice's status.
 * @param bool $can_dismiss (optional) -- Whether the notice can be dismissed.
 * @return string
 */
function exitNotice(string $text, int $status = 1, bool $can_dismiss = true): string {
	return notice($text, $status, $can_dismiss, true);
}

/**
 * Check whether a notice has been dismissed.
 * @since 1.3.8[b]
 *
 * @param string $text -- The notice's text.
 * @param array|bool $dismissed -- All dismissed notices.
 * @return bool
 */
function isDismissedNotice(string $text, array|bool $dismissed): bool {
	if($dismissed === false) return false;
	
	$rs_notice = new Notice;
	
	return $rs_notice->isDismissed($text, $dismissed);
}

/**
 * Enable pagination.
 * @since 1.2.1[a]
 *
 * @param int $current_page (optional) -- The current page.
 * @param int $per_page (optional) -- The results per page.
 * @return array
 */
function paginate(int $current_page = 1, int $per_page = 20): array {
	$page['current'] = $current_page;
	$page['per_page'] = $per_page;
	
	if($page['current'] === 1)
		$page['start'] = 0;
	else
		$page['start'] = ($page['current'] * $page['per_page']) - $page['per_page'];
	
	return $page;
}

/**
 * Construct pager navigation.
 * @since 1.2.1[a]
 *
 * @param int $page -- The page.
 * @param int $page_count -- The total page count.
 */
function pagerNav(int $page, int $page_count): void {
	$query_string = $_SERVER['QUERY_STRING'];
	$query_params = explode('&', $query_string);
	
	for($i = 0; $i < count($query_params); $i++) {
		if(str_contains($query_params[$i], 'paged'))
			unset($query_params[$i]);
	}
	
	$query_string = implode('&', $query_params);
	?>
	<div class="pager">
		<?php
		if($page > 1) {
			echo domTag('a', array(
				'class' => 'pager-nav button',
				'href' => ADMIN_URI . '?' . (!empty($query_string) ? $query_string . '&' : '') .
					'paged=1',
				'title' => 'First Page',
				'content' => '&laquo;'
			)) . domTag('a', array(
				'class' => 'pager-nav button',
				'href' => ADMIN_URI . '?' . (!empty($query_string) ? $query_string . '&' : '') .
					'paged=' . ($page - 1),
				'title' => 'Previous Page',
				'content' => '&lsaquo;'
			));
		}
		
		if($page_count > 0) echo ' Page ' . $page . ' of ' . $page_count . ' ';
		
		if($page < $page_count) {
			echo domTag('a', array(
				'class' => 'pager-nav button',
				'href' => ADMIN_URI . '?' . (!empty($query_string) ? $query_string . '&' : '') .
					'paged=' . ($page + 1),
				'title' => 'Next Page',
				'content' => '&rsaquo;'
			)) . domTag('a', array(
				'class' => 'pager-nav button',
				'href' => ADMIN_URI . '?' . (!empty($query_string) ? $query_string . '&' : '') .
					'paged=' . $page_count,
				'title' => 'Last Page',
				'content' => '&raquo;'
			));
		}
		?>
	</div>
	<?php
}

/**
 * Construct an action link.
 * @since 1.2.0[b]{ss-01}
 *
 * @param string $action -- The action.
 * @param mixed $args (optional) -- The args.
 * @param mixed $more_args (optional) -- Any additional args.
 * @return string
 */
function actionLink(string $action, mixed $args = null, mixed $more_args = null): string {
	if(!is_null($args)) {
		if(!is_array($args)) $args = (array)$args;
		if(!is_array($more_args)) $more_args = (array)$more_args;
		
		$classes = $args['classes'] ?? '';
		unset($args['classes']);
		
		$data_item = $args['data_item'] ?? '';
		unset($args['data_item']);
		
		$caption = $args['caption'] ?? ($args[0] ?? 'Action Link');
		unset($args['caption'], $args[0]);
		
		$query_string = $more_string = '';
		
		foreach($args as $key => $value) {
			if(!is_null($value))
				$query_string .= $key . '=' . $value . '&';
		}
		
		foreach($more_args as $key => $value) {
			if(!is_null($value))
				$more_string .= '&' . $key . '=' . $value;
		}
		
		return '<a' . (!empty($classes) ? ' class="' . $classes . '"' : '') .
			' href="' . ADMIN_URI . '?' . ($query_string ?? '') . 'action=' . $action .
			($more_string ?? '') . '"' . (!empty($data_item) ? ' data-item="' . $data_item . '"' :
			'') . '>' . $caption . '</a>';
	}
	
	return '<span>Invalid action link</span>';
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
		<i class="fa-solid fa-circle-info" title="Information"></i>
	</div>
	<?php
}

/**
 * Check whether a post exists in the database.
 * @since 1.0.5[b]
 *
 * @param int $id -- The post's id.
 * @return bool
 */
function postExists(int $id): bool {
	global $rs_query;
	
	return $rs_query->selectRow('posts', 'COUNT(id)', array('id' => $id)) > 0;
}

/**
 * Check whether a term exists in the database.
 * @since 1.3.7[b]
 *
 * @param int $id -- The term's id.
 * @return bool
 */
function termExists(int $id): bool {
	global $rs_query;
	
	return $rs_query->selectRow('terms', 'COUNT(id)', array('id' => $id)) > 0;
}

/**
 * Construct a unique slug.
 * @since 1.0.9[b]
 *
 * @param string $slug -- The slug.
 * @param string $table -- The database table.
 * @return string
 */
function getUniqueSlug(string $slug, string $table): string {
	global $rs_query;
	
	$count = $rs_query->selectRow($table, 'COUNT(slug)', array('slug' => $slug));
	
	if($count > 0) {
		do {
			// Try to construct a unique slug
			$unique_slug = $slug . '-' . ($count + 1);
			
			$count++;
		} while($rs_query->selectRow($table, 'COUNT(slug)', array('slug' => $unique_slug)) > 0);
		
		return $unique_slug;
	} else {
		return $slug;
	}
}

/**
 * Construct a unique post slug.
 * @since 1.0.9[b]
 *
 * @param string $slug -- The post's slug.
 * @return string
 */
function getUniquePostSlug(string $slug): string {
	return getUniqueSlug($slug, 'posts');
}

/**
 * Construct a unique term slug.
 * @since 1.0.9[b]
 *
 * @param string $slug -- The term's slug.
 * @return string
 */
function getUniqueTermSlug(string $slug): string {
	return getUniqueSlug($slug, 'terms');
}