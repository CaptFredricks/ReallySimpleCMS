<?php
/**
 * Administrative functions.
 * @since 1.0.2-alpha
 *
 * @package ReallySimpleCMS
 *
 * ## CONSTANTS [3] ##
 * - string ADMIN_STYLES [DEPRECATED]
 * - string ADMIN_SCRIPTS [DEPRECATED]
 * - string ADMIN_URI
 *
 * ## FUNCTIONS [41] ##
 * { HEADER, FOOTER, & NAV MENU [8] }
 * - getCurrentPage(): string
 * - getPageTitle(): string
 * - adminScript(string $script, string $version): void [DEPRECATED]
 * - adminStylesheet(string $stylesheet, string $version): void [DEPRECATED]
 * - adminHeaderScripts(): void
 * - adminFooterScripts(): void
 * - RSCopyright(): void
 * - RSVersion(): void
 * { DASHBOARD [4] }
 * - ctDraft(string $type): int
 * - getStatistics(string|array $table, string $col, string $value): int
 * - statsBarGraph(): void
 * - dashboardWidget(string $name): void
 * { ABOUT & INFO [5] }
 * - aboutTabStats(): void
 * - aboutTabSoftware(): void
 * - aboutTabCredits(): void
 * - userStats(): void
 * - adminInfo(): void
 * { TABLES & FORMS [7] }
 * - tableRow(string|array ...$cells): string
 * - tableCell(string $tag, string $content, string $class, int $colspan, int $rowspan): string
 * - thCell(string $content, string $class, int $colspan, int $rowspan): string
 * - tdCell(string $content, string $class, int $colspan, int $rowspan): string
 * - tableHeaderRow(array $items): string
 * - formRow(string|array $label, string|array ...$args): string
 * - recordSearch(array $args): void
 * { MEDIA [6] }
 * - uploadMediaFile(array $data): string
 * - loadMedia(bool $image_only): void
 * - mediaLink(int $id, array $args): string
 * - getUniqueFilename(string $filename): string
 * - getSizeInBytes(string $val): string
 * - getFileSize(int $bytes, int $decimals): string
 * { NOTICES [3] }
 * - notice(string $text, int $status, bool $can_dismiss, bool $is_exit): string
 * - exitNotice(string $text, int $status, bool $can_dismiss): string
 * - isDismissedNotice(string $text, array|bool $dismissed): bool
 * { MISCELLANEOUS [8] }
 * - paginate(int $current_page, int $per_page): array
 * - pagerNav(int $page, int $page_count): void
 * - actionLink(string $action, mixed $args, mixed $more_args): string
 * - postExists(int $id): bool
 * - termExists(int $id): bool
 * - getUniqueSlug(string $slug, string|array $table): string
 * - getUniquePostSlug(string $slug): string
 * - getUniqueTermSlug(string $slug): string
 */

// Path to the admin stylesheets directory [DEPRECATED]
if(!defined('ADMIN_STYLES')) define('ADMIN_STYLES', RES . '/css' . ADMIN);

// Path to the admin scripts directory [DEPRECATED]
if(!defined('ADMIN_SCRIPTS')) define('ADMIN_SCRIPTS', RES . '/js' . ADMIN);

// Current admin page URI
if(!defined('ADMIN_URI')) define('ADMIN_URI', $_SERVER['PHP_SELF']);

/*------------------------------------*\
    HEADER, FOOTER, & NAV MENU
\*------------------------------------*/

/**
 * Fetch the current admin page.
 * @since 1.5.4-alpha
 *
 * @return string
 */
function getCurrentPage(): string {
	global $rs_query, $rs_admin_pages, $rs_post_types, $rs_taxonomies;
	
	$current_page = basename($_SERVER['PHP_SELF'], '.php');
	
	if(!empty($_SERVER['QUERY_STRING'])) {
		$query_params = explode('&', $_SERVER['QUERY_STRING']);
		
		foreach($query_params as $query_param) {
			// Post types
			if(str_contains($query_param, 'type') && $current_page === 'posts') {
				$current_page = str_replace(' ', '_',
					$rs_post_types[substr($query_param, strpos($query_param, '=') + 1)]['labels']['name_lowercase']
				);
			}
			
			// Taxonomies
			if(str_contains($query_param, 'taxonomy')) {
				$current_page = str_replace(' ', '_',
					$rs_taxonomies[substr($query_param, strpos($query_param, '=') + 1)]['labels']['name_lowercase']
				);
			}
			
			if(str_contains($query_param, 'action')) {
				// Fetch the current action
				$action = substr($query_param, strpos($query_param, '=') + 1);
				
				$exclude = array('themes', 'menus', 'widgets');
				
				foreach($rs_taxonomies as $taxonomy)
					$exclude[] = str_replace(' ', '_', $taxonomy['labels']['name_lowercase']);
				
				switch($action) {
					case 'create':
					case 'upload':
					case 'install':
						if(in_array($current_page, $exclude, true)) {
							break;
						} else {
							$current_page .= '-' . $action;
							break;
						}
				}
			}
			
			// Logins, settings
			if(str_contains($query_param, 'page=')) {
				// Fetch the current page
				if(array_key_exists($current_page, $rs_admin_pages)) {
					$page = $rs_admin_pages[$current_page]['labels']['name_singular'] . '-' .
						substr($query_param, strpos($query_param, '=') + 1);
				}
				
				$current_page = str_replace('_', '-', strtolower($page));
				break;
			}
		}
		
		if($current_page === 'posts' && isset($_GET['id'])) {
			$count = $rs_query->selectRow(getTable('p'), 'COUNT(*)', array(
				'id' => $_GET['id']
			));
			
			if($count === 0) {
				redirect('posts.php');
			} else {
				$type = $rs_query->selectField(getTable('p'), 'type', array(
					'id' => $_GET['id']
				));
				
				$current_page = str_replace(' ', '_', $rs_post_types[$type]['labels']['name_lowercase']);
			}
		}
		elseif($current_page === 'terms' && isset($_GET['id'])) {
			$count = $rs_query->selectRow(getTable('t'), 'COUNT(*)', array(
				'id' => $_GET['id']
			));
			
			if($count === 0) {
				redirect('categories.php');
			} else {
				$tax_id = $rs_query->selectField(getTable('t'), 'taxonomy', array(
					'id' => $_GET['id']
				));
				
				$taxonomy = $rs_query->selectField(getTable('ta'), 'name', array(
					'id' => $tax_id
				));
				
				$current_page = str_replace(' ', '_', $rs_taxonomies[$taxonomy]['labels']['name_lowercase']);
			}
		}
	}
	
	return $current_page === 'index' ? 'dashboard' : $current_page;
}

/**
 * Fetch an admin page's title.
 * @since 2.1.11-alpha
 *
 * @return string
 */
function getPageTitle(): string {
	global $rs_query, $rs_admin_pages, $rs_post_types, $rs_taxonomies;
	
	if(basename($_SERVER['PHP_SELF']) === 'index.php') {
		// Dashboard
		$title = $rs_admin_pages['dashboard']['title'];
	} elseif(isset($_GET['type'])) {
		// Post types
		$title = $rs_post_types[$_GET['type']]['title'] ?? 'Posts';
	} elseif(basename($_SERVER['PHP_SELF']) === 'posts.php' && isset($_GET['action']) &&
		($_GET['action'] === 'edit' || $_GET['action'] === 'duplicate')
	) {
		// Post type: `post`
		$type = $rs_query->selectField(getTable('p'), 'type', array(
			'id' => $_GET['id']
		));
		
		$title = $rs_post_types[$type]['title'];
	} elseif(isset($_GET['taxonomy'])) {
		// Taxonomies
		$title = $rs_taxonomies[$_GET['taxonomy']]['title'] ?? 'Terms';
	} elseif(isset($_GET['page']) && $_GET['page'] === 'user_roles') {
		// User roles
		$title = capitalize($_GET['page']);
	} else {
		// All other pages
		$key = basename($_SERVER['PHP_SELF'], '.php');
		
		if(array_key_exists($key, $rs_admin_pages))
			$title = $rs_admin_pages[$key]['title'];
		else
			$title = 'Unregistered Page';
	}
	
	return $title;
}

/**
 * Output an admin script file.
 * @since 1.2.0-alpha
 * @deprecated since 1.3.15-beta
 *
 * @param string $script -- The script to load.
 * @param string $version (optional) -- The script's version.
 */
function adminScript(string $script, string $version = RS_VERSION): void {
	deprecated();
	
	echo '<script src="' . slash(ADMIN_SCRIPTS) . $script .
		(!empty($version) ? '?v=' . $version : '') . '"></script>';
}

/**
 * Output an admin stylesheet.
 * @since 1.2.0-alpha
 * @deprecated since 1.3.15-beta
 *
 * @param string $stylesheet -- The stylesheet to load.
 * @param string $version (optional) -- The stylesheet's version.
 */
function adminStylesheet(string $stylesheet, string $version = RS_VERSION): void {
	deprecated();
	
	echo '<link href="' . slash(ADMIN_STYLES) . $stylesheet .
		(!empty($version) ? '?v=' . $version : '') . '" rel="stylesheet">';
}

/**
 * Load all admin header scripts and stylesheets.
 * @since 2.0.7-alpha
 */
function adminHeaderScripts(): void {
	global $rs_session, $rs_admin_themes;
	
	$debug = false;
	
	if(isDebugMode()) $debug = true;
	
	// Global stylesheet
	putStylesheet('global' . ($debug ? '' : '.min') . '.css');
	
	// Button stylesheet
	putStylesheet('button' . ($debug ? '' : '.min') . '.css');
	
	// Admin stylesheet
	putStylesheet('admin' . ($debug ? '' : '.min') . '.css');
	
	// Admin theme stylesheet
	$admin_theme = $rs_session['theme'];
	$stylesheet = $admin_theme . '.css';
	$path = slash(ADMIN_THEMES) . slash($admin_theme) . $stylesheet;
	
	if(file_exists(PATH . $path) && array_key_exists($admin_theme, $rs_admin_themes))
		loadAdminTheme($path, $rs_admin_themes[$admin_theme]['version']);
	
	// Font Awesome icons stylesheet
	putStylesheet('font-awesome.min.css', ICONS_VERSION);
	
	// Font Awesome font-face rules stylesheet
	putStylesheet('font-awesome-rules.min.css');
	
	// JQuery library
	putScript('jquery.min.js', JQUERY_VERSION);
}

/**
 * Load all admin footer scripts and stylesheets.
 * @since 2.0.7-alpha
 */
function adminFooterScripts(): void {
	$debug = false;
	
	if(isDebugMode()) $debug = true;
	
	// Admin script file
	putScript('admin' . ($debug ? '' : '.min') . '.js');
}

/**
 * Display the copyright information on the admin dashboard.
 * @since 1.2.0-alpha
 */
function RSCopyright(): void {
	echo domTag('span', array(
		'content' => '&copy; ' . date('Y') . ' ' . domTag('a', array(
			'href' => 'https://github.com/ReallySimpleSystems/ReallySimpleCMS',
			'target' => '_blank',
			'rel' => 'noreferrer noopener',
			'content' => RS_ENGINE
		)) . ' &ndash; ' . domTag('em', array(
			'content' => 'powered by ' . RS_DEVELOPER
		)) . ' &bull; All Rights Reserved.'
	));
}

/**
 * Display the CMS version on the admin dashboard.
 * @since 1.2.0-alpha
 */
function RSVersion(): void {
	echo domTag('a', array(
		'href' => ADMIN . '/update.php',
		'content' => 'Version ' . RS_VERSION
	));
}

/*------------------------------------*\
    DASHBOARD
\*------------------------------------*/

/**
 * Get the count for posts of status `draft`.
 * @since 1.3.8-beta
 *
 * @param string $type (optional) -- The post's type.
 * @return int
 */
function ctDraft(string $type = 'post'): int {
	global $rs_query;
	
	return $rs_query->select(getTable('p'), 'COUNT(id)', array(
		'status' => 'draft',
		'type' => $type
	));
}

/**
 * Get statistics for a specific set of table entries.
 * @since 1.2.5-alpha
 *
 * @param string|array $table -- The database table.
 * @param string $col (optional) -- The column to query.
 * @param string $value (optional) -- The column's value.
 * @return int
 */
function getStatistics(string|array $table, string $col = '', string $value = ''): int {
	global $rs_query;
	
	if(empty($col) || empty($value)) {
		return $rs_query->select($table, 'COUNT(*)');
	} else {
		return $rs_query->select($table, 'COUNT(*)', array(
			$col => $value
		));
	}
}

/**
 * Create and display a bar graph of site statistics.
 * @since 1.2.4-alpha
 */
function statsBarGraph(): void {
	global $rs_post_types, $rs_taxonomies;
	
	$bars = $stats = array();
	
	foreach($rs_post_types as $key => $value) {
		if(!$rs_post_types[$key]['show_in_stats_graph']) continue;
		
		$bars[$key] = $value;
		$bars[$key]['stats'] = getStatistics(getTable('p'), 'type', $bars[$key]['name']);
		$stats[] = $bars[$key]['stats'];
	}
	
	foreach($rs_taxonomies as $key => $value) {
		if(!$rs_taxonomies[$key]['show_in_stats_graph']) continue;
		
		$bars[$key] = $value;
		$bars[$key]['stats'] = getStatistics(getTable('t'), 'taxonomy', getTaxonomyId($bars[$key]['name']));
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
						'title' => $bar['label'] . ': ' . $bar['stats'] . ($bar['stats'] === 1 ? ' entry' : ' entries'),
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
						'title' => $bar['label'] . ': ' . $bar['stats'] . ($bar['stats'] === 1 ? ' entry' : ' entries'),
						'content' => $bar['label']
					))
				));
			}
			?>
		</ul>
		<?php
		echo domTag('span', array(
			'class' => 'graph-y-label',
			'content' => 'Count'
		));
		
		echo domTag('span', array(
			'class' => 'graph-x-label',
			'content' => 'Category'
		));
		?>
	</div>
	<?php
}

/**
 * Construct a widget for the admin dashboard.
 * @since 1.2.1-beta
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
				echo domTag('h2', array(
					'content' => 'Comments'
				));
				?>
				<ul>
					<?php
					$comment_statuses = array('approved', 'pending');
					
					foreach($comment_statuses as $comment_status) {
						$count = $rs_query->select(getTable('c'), 'COUNT(*)', array(
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
				echo domTag('h2', array(
					'content' => 'Users'
				));
				?>
				<ul>
					<?php
					$user_statuses = array('online', 'offline');
					
					foreach($user_statuses as $user_status) {
						if($user_status === 'online')
							$session = array('IS NOT NULL');
						elseif($user_status === 'offline')
							$session = array('IS NULL');
						
						$count = $rs_query->select(getTable('u'), 'COUNT(*)', array(
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
				echo domTag('h2', array(
					'content' => 'Logins'
				));
				?>
				<ul>
					<?php
					$login_statuses = array('success', 'failure', 'blacklisted');
					
					foreach($login_statuses as $login_status) {
						if($login_status === 'blacklisted') {
							$count = $rs_query->select(getTable('lb'), 'COUNT(*)');
							
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
							$count = $rs_query->select(getTable('la'), 'COUNT(*)', array(
								'status' => $login_status
							));
							
							echo domTag('li', array(
								'content' => domTag('a', array(
									'href' => ADMIN . '/logins.php?status=' . $login_status,
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
    ABOUT
\*------------------------------------*/

/**
 * Display the 'stats' tab.
 * @since 1.3.15-beta
 */
function aboutTabStats(): void {
	global $rs_query, $rs_modules, $rs_themes, $rs_admin_themes, $rs_post_types, $rs_taxonomies;
	
	// POST TYPES
	echo tableRow(thCell('Post Types' . domTag('br') . domTag('code', array(
		'content' => '&lt;$rs_post_types&gt;'
	)), 'heading', 2));
	
	$pt_default = array();
	$pt_custom = array();
	
	foreach($rs_post_types as $post_type) {
		$pt_count = $rs_query->select(getTable('p'), 'COUNT(*)', array(
			'type' => $post_type['name']
		));
		
		if($post_type['is_default'] === true)
			$pt_default[] = $post_type['name'] . ' [' . $pt_count . ']';
		else
			$pt_custom[] = $post_type['name'] . ' [' . $pt_count . ']';
	}
	
	echo tableRow(
		thCell('Default [' . count($pt_default) . ']'),
		tdCell(implode(', ', $pt_default))
	);
	
	echo tableRow(
		thCell('Custom [' . count($pt_custom) . ']'),
		tdCell(empty($pt_custom) ? '&mdash;' : implode(', ', $pt_custom))
	);
	
	// TAXONOMIES
	echo tableRow(thCell('Taxonomies' . domTag('br') . domTag('code', array(
		'content' => '&lt;$rs_taxonomies&gt;'
	)), 'heading', 2));
	
	$tax_default = array();
	$tax_custom = array();
	
	foreach($rs_taxonomies as $taxonomy) {
		$term_count = $rs_query->select(getTable('t'), 'COUNT(*)', array(
			'taxonomy' => getTaxonomyId($taxonomy['name'])
		));
		
		if($taxonomy['is_default'] === true)
			$tax_default[] = $taxonomy['name'] . ' [' . $term_count . ']';
		else
			$tax_custom[] = $taxonomy['name'] . ' [' . $term_count . ']';
	}
	
	echo tableRow(
		thCell('Default [' . count($tax_default) . ']'),
		tdCell(implode(', ', $tax_default))
	);
	
	echo tableRow(
		thCell('Custom [' . count($tax_custom) . ']'),
		tdCell(empty($tax_custom) ? '&mdash;' : implode(', ', $tax_custom))
	);
	
	// MEDIA
	echo tableRow(thCell('Media' . domTag('br') . domTag('code', array(
		'content' => '%' . slash(UPLOADS) . '%'
	)), 'heading', 2));
	
	$media_library = $rs_query->select(getTable('p'), 'COUNT(*)', array(
		'type' => 'media'
	));
	
	$media_dir = array_diff(scandir(PATH . UPLOADS), array('.', '..'));
	$media_uploaded = 0;
	
	foreach($media_dir as $file) {
		if(preg_match('/^\d{4}$/', $file))
			$media_uploaded += count(array_diff(scandir(slash(PATH . UPLOADS) . $file), array('.', '..')));
		else
			$media_uploaded++;
	}
	
	echo tableRow(
		thCell('Library/Uploaded'),
		tdCell($media_library . '&ensp;/&ensp;' . $media_uploaded)
	);
	
	// COMMENTS
	echo tableRow(thCell('Comments', 'heading', 2));
	
	echo tableRow(
		thCell('Total'),
		tdCell(getSetting('enable_comments') ? $rs_query->select(getTable('c'), 'COUNT(*)') :
			'Comments are disabled'
		)
	);
	
	$statuses = array('approved', 'pending', 'spam');
	$by_status = array();
	
	foreach($statuses as $status) {
		$comments = $rs_query->select(getTable('c'), 'COUNT(*)', array(
			'status' => $status
		));
		
		$by_status[] = $status . ' [' . $comments . ']';
	}
	
	echo tableRow(
		thCell('By Status'),
		tdCell(getSetting('enable_comments') ? implode(', ', $by_status) : 'Comments are disabled')
	);
	
	// USERS
	echo tableRow(thCell('Users', 'heading', 2));
	
	echo tableRow(
		thCell('Registered'),
		tdCell($rs_query->select(getTable('u'), 'COUNT(*)'))
	);
	
	$statuses = array('online', 'offline');
	$by_status = array();
	
	foreach($statuses as $status) {
		$session = match($status) {
			'online' => array('IS NOT NULL'),
			'offline' => array('IS NULL')
		};
		
		$users = $rs_query->select(getTable('u'), 'COUNT(*)', array(
			'session' => $session
		));
		
		$by_status[] = $status . ' [' . $users . ']';
	}
	
	echo tableRow(
		thCell('By Status'),
		tdCell(implode(', ', $by_status))
	);
	
	$roles = $rs_query->select(getTable('ur'), array('id', 'name'), array(), array(
		'order_by' => 'id'
	));
	
	$by_role = array();
	
	foreach($roles as $role) {
		$by_role[] = strtolower($role['name']) . ' [' . $rs_query->select(getTable('u'), 'COUNT(*)', array(
			'role' => $role['id']
		)) . ']';
	}
	
	echo tableRow(
		thCell('By Role'),
		tdCell(implode(', ', $by_role))
	);
	
	// LOGINS
	echo tableRow(thCell('Logins', 'heading', 2));
	
	$types = array(
		'la' => 'attempts',
		'lb' => 'blacklist',
		'lr' => 'rules'
	);
	
	$by_type = array();
	
	foreach($types as $key => $val) {
		$logins = $rs_query->select(getTable($key), 'COUNT(*)');
		
		$by_type[] = $val . ' [' . $logins . ']';
	}
	
	echo tableRow(
		thCell('By Type'),
		tdCell(getSetting('track_login_attempts') ? implode(', ', $by_type) : 'Login tracking is disabled')
	);
	
	// MODULES
	echo tableRow(thCell('Modules' . domTag('br') . domTag('code', array(
		'content' => '&lt;$rs_modules&gt;' . '&emsp;&emsp;' . '%' . slash(MODULES) . '%'
	)), 'heading', 2));
	
	$mods_dir = array_diff(scandir(PATH . MODULES), array('.', '..', 'backups'));
	
	$mods_registered = is_countable($rs_modules) ? count($rs_modules) : 0;
	$mods_installed = is_countable($mods_dir) ? count($mods_dir) : 0;
	
	echo tableRow(
		thCell('Registered/Installed'),
		tdCell($mods_registered . '&ensp;/&ensp;' . $mods_installed)
	);
	
	$mods_required = array();
	
	foreach($rs_modules as $module) {
		if($module['is_required'] === true)
			$mods_required[] = $module['label'];
	}
	
	echo tableRow(
		thCell('Required'),
		tdCell(implode(', ', $mods_required))
	);
	
	// THEMES
	echo tableRow(thCell('Themes' . domTag('br') . domTag('code', array(
		'content' => '&lt;$rs_themes&gt;' . '&emsp;&emsp;' . '%' . slash(THEMES) . '%'
	)), 'heading', 2));
	
	$themes_dir = array_diff(scandir(PATH . THEMES), array('.', '..', 'backups'));
	
	$themes_registered = is_countable($rs_themes) ? count($rs_themes) : 0;
	$themes_installed = is_countable($themes_dir) ? count($themes_dir) : 0;
	
	echo tableRow(
		thCell('Registered/Installed'),
		tdCell($themes_registered . '&ensp;/&ensp;' . $themes_installed)
	);
	
	echo tableRow(
		thCell('Active'),
		tdCell($rs_themes[getSetting('active_theme')]['label'])
	);
	
	// ADMIN THEMES
	echo tableRow(thCell('Admin Themes' . domTag('br') . domTag('code', array(
		'content' => '&lt;$rs_admin_themes&gt;' . '&emsp;&emsp;' . '%' . slash(ADMIN_THEMES) . '%'
	)), 'heading', 2));
	
	$adthemes_dir = array_diff(scandir(PATH . ADMIN_THEMES), array('.', '..', 'backups'));
	
	$adthemes_registered = is_countable($rs_admin_themes) ? count($rs_admin_themes) : 0;
	$adthemes_installed = is_countable($adthemes_dir) ? count($adthemes_dir) : 0;
	
	echo tableRow(
		thCell('Registered/Installed'),
		tdCell($adthemes_registered . '&ensp;/&ensp;' . $adthemes_installed)
	);
	
	// SETTINGS
	echo tableRow(thCell('Settings', 'heading', 2));
	
	$settings = $rs_query->select(getTable('s'), 'name', array(), array(
		'order_by' => 'id'
	));
	
	$settings_list = array();
	
	foreach($settings as $setting)
		$settings_list[] = $setting['name'];
	
	echo tableRow(
		thCell('Available Settings'),
		tdCell(implode(', ', $settings_list))
	);
	
	echo tableRow(
		thCell('User Roles'),
		tdCell($rs_query->select(getTable('ur'), 'COUNT(*)'))
	);
	
	echo tableRow(
		thCell('User Privileges'),
		tdCell($rs_query->select(getTable('up'), 'COUNT(*)'))
	);
}

/**
 * Display the 'software' tab.
 * @since 1.3.15-beta
 */
function aboutTabSoftware(): void {
	global $rs_query;
	
	if(userHasPrivilege('can_edit_settings')) {
		// CORE & SERVER
		echo tableRow(thCell('Core & Server', 'heading', 2));
		
		echo tableRow(
			thCell('Core Version'),
			tdCell(RS_VERSION . ' (Changelog: ' . domTag('a', array(
				'href' => '/logs/changelog-beta.md',
				'target' => '_blank',
				'rel' => 'noreferrer noopener',
				'content' => 'Beta'
			)) . ' &bull; ' . domTag('a', array(
				'href' => '/logs/changelog-beta-snapshots.md',
				'target' => '_blank',
				'rel' => 'noreferrer noopener',
				'content' => 'Snapshots'
			)) . ')')
		);
		
		echo tableRow(
			thCell('jQuery Version'),
			tdCell(JQUERY_VERSION . ' (' . domTag('a', array(
				'href' => 'https://code.jquery.com/jquery-3.7.1.min.js',
				'target' => '_blank',
				'rel' => 'noreferrer noopener',
				'content' => 'Source Code'
			)) . ')')
		);
		
		echo tableRow(
			thCell('Font Awesome Version'),
			tdCell(ICONS_VERSION . ' (' . domTag('a', array(
				'href' => 'https://fontawesome.com/docs',
				'target' => '_blank',
				'rel' => 'noreferrer noopener',
				'content' => 'Documentation'
			)) . ')')
		);
		
		echo tableRow(
			thCell('Server'),
			tdCell($_SERVER['SERVER_SOFTWARE'])
		);
		
		echo tableRow(
			thCell('Timezone'),
			tdCell(ini_get('date.timezone'))
		);
		
		echo tableRow(
			thCell('HTTPS Enabled?'),
			tdCell(isSecureConnection() ? 'Yes' : 'No')
		);
		
		echo tableRow(
			thCell('Minimum PHP Version'),
			tdCell(PHP_MINIMUM)
		);
		
		echo tableRow(
			thCell('Recommended PHP Version'),
			tdCell('&ge;' . PHP_RECOMMENDED)
		);
		
		echo tableRow(
			thCell('Server PHP Version'),
			tdCell(phpversion())
		);
		
		echo tableRow(
			thCell('PHP Max Input Variables'),
			tdCell(ini_get('max_input_vars'))
		);
		
		echo tableRow(
			thCell('PHP Max Execution Time'),
			tdCell(ini_get('max_execution_time'))
		);
		
		echo tableRow(
			thCell('PHP Memory Limit'),
			tdCell(ini_get('memory_limit'))
		);
		
		echo tableRow(
			thCell('PHP Post Max Size'),
			tdCell(ini_get('post_max_size'))
		);
		
		echo tableRow(
			thCell('PHP Upload Max Filesize'),
			tdCell(ini_get('upload_max_filesize'))
		);
		
		// DATABASE
		echo tableRow(thCell('Database', 'heading', 2));
		
		echo tableRow(
			thCell('Server Version'),
			tdCell($rs_query->server_version)
		);
		
		echo tableRow(
			thCell('Client Version'),
			tdCell($rs_query->client_version)
		);
		
		echo tableRow(
			thCell('Database Host'),
			tdCell(DB_HOST)
		);
		
		echo tableRow(
			thCell('Database Name'),
			tdCell(DB_NAME)
		);
		
		echo tableRow(
			thCell('Database Username'),
			tdCell(DB_USER)
		);
		
		echo tableRow(
			thCell('Database Charset'),
			tdCell(DB_CHARSET)
		);
		
		echo tableRow(
			thCell('Database Collation'),
			tdCell(DB_COLLATE)
		);
	}
	
	// SITE STATUS
	echo tableRow(thCell('Site Status', 'heading', 2));
	
	echo tableRow(
		thCell('Maintenance Mode'),
		tdCell(defined('MAINT_MODE') ? (MAINT_MODE ? 'Enabled' : 'Disabled') : 'Undefined')
	);
	
	echo tableRow(
		thCell('Debug Mode'),
		tdCell(defined('DEBUG_MODE') ? (DEBUG_MODE ? 'Enabled' : 'Disabled') : 'Undefined')
	);
}

/**
 * Display the 'credits' tab.
 * @since 1.3.15-beta
 */
function aboutTabCredits(): void {
	// CREDITS
	echo tableRow(thCell('Credits', 'heading', 2));
	
	echo tableRow(
		thCell('Developed by'),
		tdCell(RS_DEVELOPER)
	);
	
	echo tableRow(
		thCell('Creator/Lead Developer'),
		tdCell(domTag('a', array(
			'href' => RS_LEAD_DEV['url'],
			'target' => '_blank',
			'rel' => 'noreferrer noopener',
			'content' => RS_LEAD_DEV['name']
		)))
	);
	
	echo tableRow(
		thCell('Project Start'),
		tdCell(RS_PROJ_START)
	);
}

/**
 * Display user stats.
 * @since 1.3.15-beta
 */
function userStats(): void {
	global $rs_query, $rs_session, $rs_admin_themes, $rs_post_types;
	
	// BASIC STATS
	echo tableRow(thCell('Basic Stats', 'heading', 2));
	
	$user = $rs_query->selectRow(getTable('u'), '*', array(
		'id' => $rs_session['id']
	));
	
	$role = $rs_query->selectField(getTable('ur'), 'name', array(
		'id' => $user['role']
	));
	
	echo tableRow(
		thCell('Role'),
		tdCell($role)
	);
	
	echo tableRow(
		thCell('Email'),
		tdCell($user['email'])
	);
	
	echo tableRow(
		thCell('Registered'),
		tdCell(formatDate($user['registered'], 'j M Y @ g:i A'))
	);
	
	$admin_theme = $rs_admin_themes[$rs_session['theme']];
	
	echo tableRow(
		thCell('Admin Theme'),
		tdCell($admin_theme['label'] . ' (v' . $admin_theme['version'] . ')')
	);
	
	$meta = $rs_query->selectField(getTable('um'), 'value', array(
		'user' => $rs_session['id'],
		'datakey' => 'dismissed_notices'
	));
	
	$dn = !empty($meta) ? unserialize($meta) : array();
	
	echo tableRow(
		thCell('Dismissed Notices'),
		tdCell(count($dn))
	);
	
	// CONTENT
	echo tableRow(thCell('Content', 'heading', 2));
	
	$posts = $rs_query->select(getTable('p'), 'type', array(
		'author' => $rs_session['id'],
		'status' => 'published'
	));
	
	$by_type = array();
	
	foreach($rs_post_types as $post_type) {
		if(!$post_type['public'] || !$post_type['show_in_nav_menus']) continue;
		
		$by_type[$post_type['name']] = 0;
	}
	
	$by_type['unregistered'] = 0;
	
	foreach($posts as $post) {
		if(array_key_exists($post['type'], $by_type))
			$by_type[$post['type']] += 1;
		else
			$by_type['unregistered'] += 1;
	}
	
	foreach($by_type as $key => $value) {
		$by_type[] = $key . ' [' . $value . ']';
		unset($by_type[$key]);
	}
	
	echo tableRow(
		thCell('Published Posts (by type)'),
		tdCell(implode(', ', $by_type))
	);
	
	$media = $rs_query->select(getTable('p'), 'COUNT(*)', array(
		'author' => $rs_session['id'],
		'type' => 'media'
	));
	
	echo tableRow(
		thCell('Uploaded Media'),
		tdCell($media)
	);
	
	$comments = $rs_query->select(getTable('c'), 'COUNT(*)', array(
		'author' => $rs_session['id'],
		'status' => 'approved'
	));
	
	echo tableRow(
		thCell('Approved Comments'),
		tdCell($comments)
	);
}

/**
 * Display information about each admin page's function.
 * @since 1.2.0-beta
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
		<?php
		echo domTag('i', array(
			'class' => 'fa-solid fa-circle-info',
			'title' => 'Information'
		));
		?>
	</div>
	<?php
}

/*------------------------------------*\
    TABLES & FORMS
\*------------------------------------*/

/**
 * Construct a table row. Also known as an HTML `tr` tag.
 * @since 1.4.0-alpha
 *
 * @param string|array $cells (optional) -- The table cells.
 * @return string
 */
function tableRow(string|array ...$cells): string {
	return domTag('tr', array(
		'content' => (!empty($cells) ? implode('', $cells) : '')
	));
}

/**
 * Construct a table cell, either of the header or data variety.
 * @since 1.2.1-alpha
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
	
	$args = array(
		'class' => 'column' . (!empty($class) ? ' col-' . $class : ''),
		'content' => $content
	);
	
	if($colspan > 1) $args['colspan'] = $colspan;
	if($rowspan > 1) $args['rowspan'] = $rowspan;
	
	return domTag($tag, $args);
}

/**
 * Construct a table header cell. Also known as an HTML `th` tag.
 * @since 1.3.2-beta
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
 * @since 1.3.2-beta
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
 * @since 1.2.1-alpha
 *
 * @param array $items -- The row items.
 * @return string
 */
function tableHeaderRow(array $items): string {
	if(count(array_filter(array_keys($items), 'is_string')) > 0) {
		foreach($items as $key => $value)
			$row[] = thCell($value, $key);
	} else {
		foreach($items as $item) $row[] = thCell($item);
	}
	
	return tableRow(implode('', $row));
}

/**
 * Construct a form row.
 * @since 1.1.2-alpha
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
			'for' => (!empty($args[$i]['id']) ? $args[$i]['id'] : ''),
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
 * @since 1.3.7-beta
 *
 * @param array $args (optional) -- The args.
 */
function recordSearch(array $args = array()): void {
	button(array(
		'id' => 'search-toggle',
		'title' => 'Record search',
		'label' => domTag('i', array(
			'class' => 'fa-solid fa-magnifying-glass'
		))
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
 * @since 2.1.6-alpha
 *
 * @param array $data -- The submission data.
 * @return string
 */
function uploadMediaFile(array $data): string {
	global $rs_query, $rs_session;
	
	if(empty($data['name'])) {
		return exitNotice('A file must be selected for upload!', -1);
		exit;
	}
	
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
	
	if(!in_array($data['type'], $accepted_mime, true)) {
		return exitNotice('The file could not be uploaded.', -1);
		exit;
	}
	
	$basepath = PATH . UPLOADS;
	
	if(!file_exists($basepath)) mkdir($basepath);
	
	$year = date('Y');
	
	if(!file_exists(slash($basepath) . $year))
		mkdir(slash($basepath) . $year);
	
	$filename = sanitize(str_replace(array('  ', ' ', '_'), '-', $data['name']), '/[^\w.-]/');
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
	
	$title = capitalize($slug);
	
	$insert_id = $rs_query->insert(getTable('p'), array(
		'title' => $title,
		'author' => $rs_session['id'],
		'created' => 'NOW()',
		'modified' => 'NOW()',
		'content' => '',
		'slug' => $slug,
		'type' => 'media'
	));
	
	foreach($mediameta as $key => $value) {
		$rs_query->insert(getTable('pm'), array(
			'post' => $insert_id,
			'key' => $key,
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
 * @since 2.1.2-alpha
 *
 * @param bool $image_only (optional) -- Whether to display only images.
 */
function loadMedia(bool $image_only = false): void {
	global $rs_query;
	
	$mediaa = $rs_query->select(getTable('p'), '*', array(
		'type' => 'media'
	), array(
		'order_by' => 'created',
		'order' => 'DESC'
	));
	
	if(empty($mediaa)) {
		echo domTag('p', array(
			'style' => 'margin: 1em;',
			'content' => 'The media library is empty!'
		));
	} else {
		foreach($mediaa as $media) {
			$mediameta = $rs_query->select(getTable('pm'), array('key', 'value'), array(
				'post' => $media['p_id']
			));
			
			$meta = array();
			
			foreach($mediameta as $metadata) {
				$values = array_values($metadata);
				
				for($i = 0; $i < count($metadata); $i += 2)
					$meta[$values[$i]] = $values[$i + 1];
			}
			
			if($image_only) {
				$image_mime = array('image/jpeg', 'image/png', 'image/gif', 'image/x-icon');
				
				if(!in_array($meta['mime_type'], $image_mime, true)) continue;
				
				$image_filepath = slash(PATH . UPLOADS) . $meta['filepath'];
				
				if(file_exists($image_filepath))
					list($width, $height) = getimagesize($image_filepath);
			}
			?>
			<div class="media-item-wrap">
				<div class="media-item">
					<div class="thumb-wrap">
						<?php
						echo getMedia($media['p_id'], array(
							'class' => 'thumb'
						));
						?>
					</div>
					<div>
						<?php
						$file = pathinfo($meta['filepath']);
						
						echo domTag('div', array(
							// ID
							'class' => 'hidden',
							'data-field' => 'id',
							'content' => $media['p_id']
						)) . domTag('div', array(
							// Thumb
							'class' => 'hidden',
							'data-field' => 'thumb',
							'content' => getMedia($media['p_id'])
						)) . domTag('div', array(
							// Title
							'class' => 'hidden',
							'data-field' => 'title',
							'content' => $media['p_title']
						)) . domTag('div', array(
							// Date
							'class' => 'hidden',
							'data-field' => 'date',
							'content' => formatDate($media['p_created'], 'd M Y @ g:i A')
						)) . domTag('div', array(
							// Filepath
							'class' => 'hidden',
							'data-field' => 'filepath',
							'content' => mediaLink($media['p_id'], array(
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
 * @since 1.2.9-beta
 *
 * @param int $id -- The media's id.
 * @param array $args (optional) -- The args.
 * @return string
 */
function mediaLink(int $id, array $args = array()): string {
	global $rs_query;
	
	$modified = $rs_query->selectField(getTable('p'), 'modified', array(
		'id' => $id
	));
	
	$src = getMediaSrc($id) . '?cached=' . formatDate($modified, 'YmdHis');
	
	if(!empty($args['newtab']) && $args['newtab'] === 1)
		$newtab = 1;
	else
		$newtab = 0;
	
	if(empty($args['link_text'])) {
		$args['link_text'] = $rs_query->selectField(getTable('p'), 'title', array(
			'id' => $id
		));
	}
	
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
 * @since 2.1.0-alpha
 *
 * @param string $filename -- The filename.
 * @return string
 */
function getUniqueFilename(string $filename): string {
	global $rs_query;
	
	$count = $rs_query->select(getTable('pm'), 'COUNT(*)', array(
		'key' => 'filepath',
		'value' => array('LIKE', '%' . $filename . '%')
	));
	
	if($count > 0) {
		$file_parts = pathinfo($filename);
		
		do {
			$unique_filename = $file_parts['filename'] . '-' . ($count + 1) . '.' . $file_parts['extension'];
			$count++;
		} while($rs_query->selectRow(getTable('pm'), 'COUNT(*)', array(
			'key' => 'filepath',
			'value' => array('LIKE', '%' . $unique_filename)
		)) > 0);
		
		return $unique_filename;
	} else {
		return $filename;
	}
}

/**
 * Convert a string value or file size to bytes.
 * @since 2.1.3-alpha
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
 * @since 2.1.0-alpha
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
    NOTICES
\*------------------------------------*/

/**
 * Display a notice.
 * @since 1.2.0-alpha
 *
 * @param string $text -- The notice's text.
 * @param int $status (optional) -- The notice's status.
 * @param bool $can_dismiss (optional) -- Whether the notice can be dismissed.
 * @param bool $is_exit (optional) -- Whether the notice is an exit status.
 * @return string
 */
function notice(string $text, int $status = 2, bool $can_dismiss = true, bool $is_exit = false): string {
	$rs_ad_notice = new \Admin\Notice;
	
	return $rs_ad_notice->msg($text, $status, $can_dismiss, $is_exit);
}

/**
 * Display an exit status notice.
 * @since 1.3.8-beta
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
 * @since 1.3.8-beta
 *
 * @param string $text -- The notice's text.
 * @param array|bool $dismissed -- All dismissed notices.
 * @return bool
 */
function isDismissedNotice(string $text, array|bool $dismissed): bool {
	if($dismissed === false) return false;
	
	$rs_ad_notice = new \Admin\Notice;
	
	return $rs_ad_notice->isDismissed($text, $dismissed);
}

/*------------------------------------*\
    MISCELLANEOUS
\*------------------------------------*/

/**
 * Enable pagination.
 * @since 1.2.1-alpha
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
 * @since 1.2.1-alpha
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
				'href' => ADMIN_URI . '?' . (!empty($query_string) ? $query_string . '&' : '') . 'paged=1',
				'title' => 'First Page',
				'content' => '&laquo;'
			)) . domTag('a', array(
				'class' => 'pager-nav button',
				'href' => ADMIN_URI . '?' . (!empty($query_string) ? $query_string . '&' : '') . 'paged=' . $page - 1,
				'title' => 'Previous Page',
				'content' => '&lsaquo;'
			));
		}
		
		if($page_count > 0) echo ' Page ' . $page . ' of ' . $page_count . ' ';
		
		if($page < $page_count) {
			echo domTag('a', array(
				'class' => 'pager-nav button',
				'href' => ADMIN_URI . '?' . (!empty($query_string) ? $query_string . '&' : '') . 'paged=' . $page + 1,
				'title' => 'Next Page',
				'content' => '&rsaquo;'
			)) . domTag('a', array(
				'class' => 'pager-nav button',
				'href' => ADMIN_URI . '?' . (!empty($query_string) ? $query_string . '&' : '') . 'paged=' . $page_count,
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
 * @since 1.2.0-beta_snap-01
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
		
		return domTag('a', array(
			'class' => (!empty($classes) ? $classes : ''),
			'href' => ADMIN_URI . '?' . ($query_string ?? '') . 'action=' . $action . ($more_string ?? ''),
			'data-item' => (!empty($data_item) ? $data_item : ''),
			'content' => $caption
		));
	}
	
	return domTag('span', array(
		'content' => 'Invalid action link'
	));
}

/**
 * Check whether a post exists in the database.
 * @since 1.0.5-beta
 *
 * @param int $id -- The post's id.
 * @return bool
 */
function postExists(int $id): bool {
	global $rs_query;
	
	return $rs_query->selectRow(getTable('p'), 'COUNT(id)', array(
		'id' => $id
	)) > 0;
}

/**
 * Check whether a term exists in the database.
 * @since 1.3.7-beta
 *
 * @param int $id -- The term's id.
 * @return bool
 */
function termExists(int $id): bool {
	global $rs_query;
	
	return $rs_query->selectRow(getTable('t'), 'COUNT(id)', array(
		'id' => $id
	)) > 0;
}

/**
 * Construct a unique slug.
 * @since 1.0.9-beta
 *
 * @param string $slug -- The slug.
 * @param string|array $table -- The database table.
 * @return string
 */
function getUniqueSlug(string $slug, string|array $table): string {
	global $rs_query;
	
	$count = $rs_query->selectRow($table, 'COUNT(slug)', array(
		'slug' => $slug
	));
	
	if($count > 0) {
		do {
			// Try to construct a unique slug
			$unique_slug = $slug . '-' . $count + 1;
			
			$count++;
		} while($rs_query->selectRow($table, 'COUNT(slug)', array(
			'slug' => $unique_slug
		)) > 0);
		
		return $unique_slug;
	} else {
		return $slug;
	}
}

/**
 * Construct a unique post slug.
 * @since 1.0.9-beta
 *
 * @param string $slug -- The post's slug.
 * @return string
 */
function getUniquePostSlug(string $slug): string {
	return getUniqueSlug($slug, getTable('p'));
}

/**
 * Construct a unique term slug.
 * @since 1.0.9-beta
 *
 * @param string $slug -- The term's slug.
 * @return string
 */
function getUniqueTermSlug(string $slug): string {
	return getUniqueSlug($slug, getTable('t'));
}