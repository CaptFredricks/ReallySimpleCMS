<?php
/**
 * Admin class used to implement the Register object. This handles registration of various components.
 * @since 1.3.16-beta
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_register
 *
 * ## CONSTANTS [2] ##
 * - public array DEFAULT_ADMIN_PAGES
 * - public array DEFAULT_ADMIN_THEMES
 *
 * ## METHODS [5] ##
 * { ADMIN PAGES [3] }
 * - public registerAdminPage(string $name, array $args): ?array
 * - private setAdminPageLabels(string $name, array $labels): array
 * - private setAdminPageActions(array $actions): array
 * { ADMIN THEMES [2] }
 * - public registerAdminTheme(string $name, array $args): ?array
 * - public unregisterAdminTheme(string $name, bool $del_data): bool
 */
namespace Admin;

class Register {
	/**
	 * Default admin pages.
	 * @since 1.3.16-beta
	 */
	public const DEFAULT_ADMIN_PAGES = array(
		'dashboard', 'about',
		'posts', 'categories', 'terms', 'media',
		'themes', 'menus', 'widgets', 'comments',
		'users', 'profile', 'stats',
		'logins', 'settings'
	);
	
	/**
	 * Default admin themes (included with fresh installs).
	 * Bedrock theme cannot be unregistered as it is considered the primary default.
	 * @since 1.3.15-beta
	 *
	 * @var array
	 */
	public const DEFAULT_ADMIN_THEMES = array('bedrock', 'sky', 'forest', 'ocean', 'sunset', 'harvest');
	
	/*------------------------------------*\
		ADMIN PAGES
	\*------------------------------------*/
	
	/**
	 * Register an admin page.
	 * @since 1.3.16-beta
	 *
	 * @access public
	 * @param string $name -- The admin page's name.
	 * @param array $args (optional) -- The args.
	 * @return null|array
	 */
	public function registerAdminPage(string $name, array $args): ?array {
		global $rs_admin_pages;
		
		if(!is_array($rs_admin_pages)) $rs_admin_pages = array();
		
		$name = sanitize($name);
		
		if(empty($name) || strlen($name) > 20)
			exit('An admin page\'s name must be between 1 and 20 characters long.');
		
		// If the name is already registered, abort
		if(adminPageExists($name)) return null;
		
		$defaults = array(
			'title' => null,
			'labels' => array(),
			'actions' => array(),
			'pages' => array(
				'default' => null,
				'subpages' => null
			),
			'show_in_admin_menu' => true,
			'show_in_admin_bar' => true,
			'menu_item' => array(
				'link' => $name . '.php',
				'icon' => null,
				'caption' => null,
				'submenu' => null,
				'index' => null,
				'privileges' => null
			)
		);
		
		// Merge all provided args with the defaults
		if(isset($args['pages']))
			$args['pages'] = array_merge($defaults['pages'], $args['pages']);
		
		if(isset($args['menu_item']))
			$args['menu_item'] = array_merge($defaults['menu_item'], $args['menu_item']);
		
		$args = array_merge($defaults, $args);
		
		// Remove any unrecognized args
		foreach($args as $key => $value)
			if(!array_key_exists($key, $defaults)) unset($args[$key]);
		
		$args['is_default'] = in_array($name, self::DEFAULT_ADMIN_PAGES, true) ? true : false;
		
		// Name, labels, actions, etc.
		$args['name'] = $name;
		$args['labels'] = $this->setAdminPageLabels($name, $args['labels']);
		$args['actions'] = $this->setAdminPageActions($args['actions']);
		
		if(is_null($args['title']))
			$args['title'] = $args['labels']['name'];
		
		$caption = $args['menu_item']['caption'];
		$args['menu_item']['caption'] = $args['labels'][$caption] ?? ($caption ?? $args['title']);
		
		// Add the admin page to the global array
		$rs_admin_pages[$name] = $args;
		
		return $rs_admin_pages[$name];
	}
	
	/**
	 * Set all admin page labels.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @param string $name -- The admin page's name.
	 * @param array $labels (optional) -- Any predefined labels.
	 * @return array
	 */
	private function setAdminPageLabels(string $name, array $labels = array()): array {
		$name_default = capitalize($name);
		$name_lowercase = strtolower($name_default);
		$name_singular = capitalize((str_ends_with($name, 's') && $name !== 'settings' ? substr($name, 0, -1) : $name));
		
		$defaults = array(
			// Name
			'name' => $name_default,
			'name_lowercase' => $name_lowercase,
			'name_singular' => $name_singular,
			// Headings/captions
			'list_items' => 'List ' . $name_default,
			'create_item' => 'Create ' . $name_singular,
			'create_button' => 'Create New',
			'edit_item' => 'Edit ' . $name_singular,
			'update_button' => 'Update ' . $name_singular,
			'duplicate_item' => 'Duplicate ' . $name_singular,
			'bulk_update' => 'Bulk Update',
			'bulk_delete' => 'Bulk Delete',
			// Notices/placeholders
			'no_items' => 'There are no ' . $name_lowercase . ' to display.',
			'title_placeholder' => 'My New ' . $name_singular,
			// Exclude from final array
			'exclude' => array()
		);
		
		$labels = array_merge($defaults, $labels);
		
		$required = array('name', 'name_lowercase', 'name_singular', 'exclude');
		
		foreach($labels as $key => $val) {
			if(($labels['exclude'] === 'all' || in_array($key, $labels['exclude'], true)) && !in_array($key, $required, true))
				unset($labels[$key]);
		}
		
		return $labels;
	}
	
	/**
	 * Set all admin page actions.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @param array $actions (optional) -- Any predefined actions.
	 * @return array
	 */
	private function setAdminPageActions(array $actions = array()): array {
		$defaults = array(
			// Global
			'create', 'edit', 'delete', 'view', 'preview',
			// Post types
			'upload', 'duplicate', 'replace', 'trash', 'restore',
			// Modules, themes
			'install', 'uninstall', 'activate', 'deactivate',
			// Comments
			'approve', 'unapprove', 'spam',
			// Logins
			'blacklist_login', 'blacklist_ip', 'whitelist'
		);
		
		$set_actions = array();
		
		foreach($defaults as $action) {
			if(!in_array($action, $actions, true)) continue;
			
			$set_actions[$action] = $action;
		}
		
		return $set_actions;
	}
	
	/*------------------------------------*\
		ADMIN THEMES
	\*------------------------------------*/
	
	/**
	 * Register an admin theme.
	 * @since 1.3.15-beta
	 *
	 * @access public
	 * @param string $name -- The theme's name.
	 * @param array $args (optional) -- The args.
	 * @return null|array
	 */
	public function registerAdminTheme(string $name, array $args = array()): ?array {
		global $rs_admin_themes;
		
		if(!is_array($rs_admin_themes)) $rs_admin_themes = array();
		
		$name = sanitize($name);
		
		if(empty($name) || strlen($name) > 20)
			exit('An admin theme\'s name must be between 1 and 20 characters long.');
		
		// If the name is already registered, abort
		if(adminThemeExists($name)) return null;
		
		$defaults = array(
			'label' => capitalize($name),
			'author' => array(),
			'version' => null,
			'path' => null,
			'description' => ''
			#'labels' => array()
		);
		
		$args = array_merge($defaults, $args);
		
		// Remove any unrecognized args
		foreach($args as $key => $value)
			if(!array_key_exists($key, $defaults)) unset($args[$key]);
		
		$args['path'] = slash(PATH . ADMIN_THEMES) . slash($name) . $name . '.css';
		$args['is_default'] = in_array($name, self::DEFAULT_ADMIN_THEMES, true) ? true : false;
		$args['name'] = $name;
		
		// Add the admin theme to the global array
		$rs_admin_themes[$name] = $args;
		
		return $rs_admin_themes[$name];
	}
	
	/**
	 * Unregister an admin theme.
	 * @since 1.3.15-beta
	 *
	 * @access public
	 * @param string $name -- The theme's name.
	 * @param bool $del_data (optional) -- Whether to delete all associated data.
	 * @return bool
	 */
	public function unregisterAdminTheme(string $name, bool $del_data = false): bool {
		global $rs_admin_themes;
		
		$name = sanitize($name);
		
		if(adminThemeExists($name) && $name !== 'bedrock') {
			$file_path = slash(PATH . ADMIN_THEMES) . slash($name);
			
			if($del_data) removeDir($file_path);
			
			unset($rs_admin_themes[$name]);
			return true;
		}
		
		return false;
	}
}