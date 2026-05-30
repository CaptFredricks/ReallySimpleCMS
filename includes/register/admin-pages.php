<?php
/**
 * Admin page registry functions.
 * @since 1.3.16-beta
 *
 * @package ReallySimpleCMS
 *
 * ## FUNCTIONS [5] ##
 * - registerAdminPage(string $name, array $args): ?array
 * - runAdminPagesRegister(): void
 * - adminPageExists(string $name): bool
 * - registerAdminMenuItem(array $item, array $submenu, mixed $icon): void
 * - registerAdminMenu(): void
 */

/**
 * Register an admin page.
 * @since 1.3.16-beta
 *
 * @param string $name -- The admin page's name.
 * @param array $args (optional) -- The args.
 * @return null|array
 */
function registerAdminPage(string $name, array $args = array()): ?array {
	global $rs_ad_register;
	
	return $rs_ad_register->registerAdminPage($name, $args);
}

/**
 * Register all available admin pages.
 * @since 1.3.16-beta
 */
function runAdminPagesRegister(): void {
	
	// ADMIN MENU TOP-LEVEL
	
	// Dashboard
	registerAdminPage('dashboard', array(
		'title' => 'Admin Dashboard',
		'labels' => array(
			'exclude' => array(
				'list_items', 'create_item', 'edit_item', 'update_button',
				'bulk_update', 'bulk_delete', 'duplicate_item',
				'no_items', 'title_placeholder'
			)
		),
		'menu_item' => array(
			'link' => 'index.php',
			'icon' => 'gauge-high',
			'caption' => 'Dashboard',
			'index' => 5
		)
	));
	
	// Customize
	registerAdminPage('customize', array(
		'labels' => array(
			'exclude' => array(
				'list_items', 'create_item', 'edit_item', 'update_button',
				'bulk_update', 'bulk_delete', 'duplicate_item',
				'no_items', 'title_placeholder'
			)
		),
		'menu_item' => array(
			'link' => null,
			'icon' => 'palette',
			'submenu' => array('themes', 'menus', 'widgets'),
			'index' => 10
		)
	));
	
	// Comments
	registerAdminPage('comments', array(
		'labels' => array(
			'exclude' => array('create_item', 'create_button', 'duplicate_item', 'title_placeholder')
		),
		'actions' => array('edit', 'delete', 'view', 'approve', 'unapprove', 'spam'),
		'menu_item' => array(
			'icon' => array('comments', 'regular'),
			'index' => 25
		)
	));
	
	// Users
	registerAdminPage('users', array(
		'labels' => array(
			'title_placeholder' => 'jon.doe123',
			'exclude' => array('duplicate_item')
		),
		'actions' => array('create', 'edit', 'delete'),
		'menu_item' => array(
			'icon' => 'users',
			'submenu' => array('list_items', 'create_item', 'profile', 'stats'),
			'index' => 35
		)
	));
	
	// Logins
	registerAdminPage('logins', array(
		'labels' => array(
			'exclude' => array('list_items', 'duplicate_item', 'bulk_update', 'bulk_delete', 'no_items', 'title_placeholder')
		),
		'actions' => array('create', 'edit', 'delete', 'blacklist_login', 'blacklist_ip', 'whitelist'),
		'pages' => array(
			'default' => 'attempts',
			'subpages' => array('blacklist', 'rules'),
		),
		'menu_item' => array(
			'icon' => 'right-to-bracket',
			'submenu' => array('attempts', 'blacklist', 'rules'),
			'index' => 40
		)
	));
	
	// Settings
	registerAdminPage('settings', array(
		'labels' => array(
			'exclude' => array(
				'list_items', 'create_item', 'edit_item',
				'duplicate_item', 'bulk_update', 'bulk_delete',
				'no_items', 'title_placeholder'
			)
		),
		'actions' => array('create', 'edit', 'delete'),
		'pages' => array(
			'default' => 'general',
			'subpages' => array('design', 'user_roles')
		),
		'menu_item' => array(
			'icon' => 'gears',
			'submenu' => array('general', 'design', 'user_roles'),
			'index' => 45
		)
	));
	
	// About
	registerAdminPage('about', array(
		'title' => 'About ' . RS_ENGINE,
		'labels' => array(
			'exclude' => 'all'
		),
		'menu_item' => array(
			'icon' => 'circle-info',
			'index' => 50
		)
	));
	
	// ADMIN MENU SUB-ITEMS, OR NOT IN MENU
	
	// Posts
	registerAdminPage('posts', array(
		'show_in_admin_menu' => false,
		'show_in_admin_bar' => false
	));
	
	// Categories
	registerAdminPage('categories', array(
		'show_in_admin_menu' => false,
		'show_in_admin_bar' => false,
		'menu_item' => array(
			'parent' => 'posts',
			'index' => 2
		)
	));
	
	// Terms
	registerAdminPage('terms', array(
		'show_in_admin_menu' => false,
		'show_in_admin_bar' => false
	));
	
	// Media
	registerAdminPage('media', array(
		'labels' => array(
			'create_item' => 'Upload Media',
			'create_button' => 'Upload New',
			'duplicate_item' => 'Replace Media',
			'exclude' => array('bulk_update', 'bulk_delete')
		),
		'actions' => array('upload', 'edit', 'delete', 'view', 'replace'),
		'show_in_admin_menu' => false,
		'show_in_admin_bar' => false
	));
	
	// Themes
	registerAdminPage('themes', array(
		'labels' => array(
			'exclude' => array(
				'edit_item', 'update_button', 'duplicate_item',
				'bulk_update', 'bulk_delete', 'no_items'
			)
		),
		'actions' => array('install', 'uninstall', 'activate',
			'create', 'delete' # backward compat
		),
		'show_in_admin_menu' => false,
		'show_in_admin_bar' => false,
		'menu_item' => array(
			'caption' => 'list_items',
			'index' => 0
		)
	));
	
	// Menus
	registerAdminPage('menus', array(
		'labels' => array(
			'exclude' => array('duplicate_item', 'bulk_update', 'bulk_delete')
		),
		'actions' => array('create', 'edit', 'delete'),
		'show_in_admin_menu' => false,
		'show_in_admin_bar' => false,
		'menu_item' => array(
			'caption' => 'list_items',
			'index' => 1
		)
	));
	
	// Widgets
	registerAdminPage('widgets', array(
		'labels' => array(
			'exclude' => array('duplicate_item')
		),
		'actions' => array('create', 'edit', 'delete'),
		'show_in_admin_menu' => false,
		'show_in_admin_bar' => false,
		'menu_item' => array(
			'caption' => 'list_items',
			'index' => 2
		)
	));
	
	// Profile
	registerAdminPage('profile', array(
		'title' => 'My Profile',
		'labels' => array(
			'title_placeholder' => 'jon.doe123',
			'exclude' => array(
				'list_items', 'create_item', 'create_button',
				'duplicate_item', 'bulk_update', 'bulk_delete', 'no_items'
			)
		),
		'show_in_admin_menu' => false,
		'show_in_admin_bar' => false,
		'menu_item' => array(
			'index' => 2
		)
	));
	
	// Stats
	registerAdminPage('stats', array(
		'title' => 'My Stats',
		'labels' => array(
			'list_items' => 'User Stats',
			'exclude' => array(
				'create_item', 'create_button',
				'duplicate_item', 'bulk_update', 'bulk_delete',
				'no_items', 'title_placeholder'
			)
		),
		'show_in_admin_menu' => false,
		'show_in_admin_bar' => false,
		'menu_item' => array(
			'index' => 3
		)
	));
}

/**
 * Check whether an admin page exists.
 * @since 1.3.16-beta
 *
 * @param string $name -- The admin page's name.
 * @return bool
 */
function adminPageExists(string $name): bool {
	global $rs_admin_pages;
	
	$name .= '.php';
	$installed = array_diff(scandir(PATH . ADMIN), array('.', '..'));
	
	return !empty($rs_admin_pages) && array_key_exists($name, $rs_admin_pages) && in_array($name, $installed, true);
}

/**
 * Create a menu item for the admin navigation.
 * @since 1.2.5-alpha
 *
 * @param array $item (optional) -- The menu item.
 * @param array $submenu (optional) -- The submenu, if applicable.
 * @param mixed $icon (optional) -- The menu icon.
 */
function registerAdminMenuItem(array $item = array(), array $submenu = array(), mixed $icon = null): void {
	$current_page = getCurrentPage();
	
	if(!empty($item) && !is_array($item)) $item = (array)$item;
	
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
	
	if(!empty($submenu)) {
		if(!is_array($submenu)) $submenu = (array)$submenu;
		
		$submenu_items = array();
		
		foreach($submenu as $sub_item) {
			if(!empty($sub_item) && !is_array($sub_item)) break;
			
			if(!empty($sub_item)) {
				$sub_item_id = $sub_item['id'] ?? $item_id;
				$sub_item_link = isset($sub_item['link']) ? slash(ADMIN) .
					$sub_item['link'] : 'javascript:void(0)';
				$sub_item_caption = $sub_item['caption'] ?? ucwords(str_replace('-', ' ', $sub_item_id));
				
				$submenu_items[] = domTag('li', array(
					'class' => ($sub_item_id === $current_page ? 'current-submenu-item' : ''),
					'content' => domTag('a', array(
						'href' => $sub_item_link,
						'content' => $sub_item_caption
					))
				));
			}
		}
		
		$submenu = domTag('ul', array(
			'class' => 'submenu',
			'content' => implode('', $submenu_items)
		));
	}
	
	// Menu item wrapper
	?>
	<li<?php echo !empty($item_class) ? ' class="' . $item_class . '"' : ''; ?>>
		<?php
		$item_content = '';
		
		// Item icon
		if(!empty($icon)) {
			if(is_array($icon)) {
				switch($icon[1]) {
					case 'regular':
						$item_content = domTag('i', array(
							'class' => 'fa-regular fa-' . $icon[0]
						));
						break;
					case 'solid':
					default:
						$item_content = domTag('i', array(
							'class' => 'fa-solid fa-' . $icon[0]
						));
				}
			} else {
				$item_content = domTag('i', array(
					'class' => 'fa-solid fa-' . $icon
				));
			}
		} else {
			$item_content = domTag('i', array(
				'class' => 'fa-solid fa-code-branch'
			));
		}
		
		// Item caption
		$item_content .= domTag('span', array(
			'content' => $item_caption
		));
		
		// Item link
		domTagPr('a', array(
			'href' => $item_link,
			'content' => $item_content
		));
		
		// Submenu, if exists
		if(!empty($submenu)) echo $submenu;
		?>
	</li>
	<?php
}

/**
 * Construct the admin nav menu.
 * @since 1.0.0-beta
 */
function registerAdminMenu(): void {
	global $rs_admin_pages, $rs_post_types, $rs_taxonomies;
	
	// Dashboard
	registerAdminMenuItem(array(
		'id' => 'dashboard',
		'link' => 'index.php'
	), array(), 'gauge-high');
	
	// Customize (themes/menus/widgets)
	if(userHasPrivileges(array('can_view_themes', 'can_view_menus', 'can_view_widgets'), 'OR')) {
		registerAdminMenuItem(array(
			'id' => 'customize'
		), array( // Submenu
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
	
	// Post types
	foreach($rs_post_types as $post_type) {
		if(!$post_type['show_in_admin_menu']) continue;
		
		$id = str_replace(' ', '_', $post_type['labels']['name_lowercase']);
		
		if(userHasPrivilege('can_view_' . $id)) {
			// Taxonomies
			$taxes = array();
			
			if(!empty($post_type['taxonomies'])) {
				foreach($post_type['taxonomies'] as $tax) {
					if(array_key_exists($tax, $rs_taxonomies)) {
						$tax_id = str_replace(' ', '_', $rs_taxonomies[$tax]['labels']['name_lowercase']);
						
						if(userHasPrivilege('can_view_' . $tax_id) && $rs_taxonomies[$tax]['show_in_admin_menu']) {
							$taxes[] = array(
								'id' => $tax_id,
								'link' => $rs_taxonomies[$tax]['menu_link'],
								'caption' => $rs_taxonomies[$tax]['labels']['list_items']
							);
						}
					}
				}
			}
			
			$submenu = array(
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
				) : null)
			);
			
			$submenu = array_merge($submenu, $taxes);
			
			registerAdminMenuItem(array(
				'id' => $id
			), $submenu, $post_type['menu_icon']);
		}
	}
	
	// Comments
	if(userHasPrivilege('can_view_comments')) {
		registerAdminMenuItem(array(
			'id' => 'comments',
			'link' => 'comments.php'
		), array(), array('comments', 'regular'));
	}
	
	// Users/user profile
	registerAdminMenuItem(array(
		'id' => 'users'
	), array( // Submenu
		(userHasPrivilege('can_view_users') ? array(
			'link' => 'users.php',
			'caption' => 'List Users'
		) : null),
		(userHasPrivilege('can_create_users') ? array(
			'id' => 'users-create',
			'link' => 'users.php?action=create',
			'caption' => 'Create User'
		) : null), array(
			'id' => 'profile',
			'link' => 'profile.php',
			'caption' => 'My Profile'
		), array(
			'id' => 'stats',
			'link' => 'stats.php',
			'caption' => 'My Stats'
		)
	), 'users');
	
	// Logins (attempts/blacklist/rules)
	if(userHasPrivileges(array(
		'can_view_login_attempts',
		'can_view_login_blacklist',
		'can_view_login_rules'
	), 'OR')) {
		registerAdminMenuItem(array(
			'id' => 'logins'
		), array( // Submenu
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
		registerAdminMenuItem(array(
			'id' => 'settings'
		), array( // Submenu
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
	registerAdminMenuItem(array(
		'id' => 'about',
		'link' => 'about.php'
	), array(), 'circle-info');
}