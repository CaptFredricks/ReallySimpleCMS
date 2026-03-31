<?php
/**
 * Core class used to implement the Register object. This handles registration of various components.
 * @since 1.3.15-beta
 *
 * @package ReallySimpleCMS
 * @subpackage Engine
 *
 * ## OBJECT VAR ##
 * - $rs_register
 *
 * ## CONSTANTS [5] ##
 * - public array DEFAULT_POST_TYPES
 * - public array DEFAULT_TAXONOMIES
 * - public array REQUIRED_MODULES
 * - public array DEFAULT_THEMES
 * - public array DEFAULT_ADMIN_THEMES
 *
 * ## METHODS [13] ##
 * { POST TYPES [4] }
 * - public registerPostType(string $name, array $args): ?array
 * - public unregisterPostType(string $name, bool $del_posts): bool
 * - private setPostTypeLabels(string $name, array $labels): array
 * - private setPostTypeActions(array $actions): array
 * { TAXONOMIES [3] }
 * - public registerTaxonomy(string $name, string $post_type, array $args): ?array
 * - public unregisterTaxonomy(string $name, bool $del_terms): bool
 * - private setTaxonomyLabels(string $name, array $labels): array
 * { MODULES [2] }
 * - public registerModule(string $name, array $args): ?array
 * - public unregisterModule(string $name, bool $del_data): bool
 * { THEMES [2] }
 * - public registerTheme(string $name, array $args): ?array
 * - public unregisterTheme(string $name, bool $del_data): bool
 * { ADMIN THEMES [2] }
 * - public registerAdminTheme(string $name, array $args): ?array
 * - public unregisterAdminTheme(string $name, bool $del_data): bool
 */
namespace Engine;

class Register {
	/**
	 * Default post types (included with fresh installs).
	 * These cannot be unregistered as they are required by core functionality.
	 * @since 1.3.15-beta
	 *
	 * @var array
	 */
	public const DEFAULT_POST_TYPES = array('page', 'media', 'post', 'nav_menu_item', 'widget');
	
	/**
	 * Default taxonomies (included with fresh installs).
	 * These cannot be unregistered as they are required by core functionality.
	 * @since 1.3.15-beta
	 *
	 * @var array
	 */
	public const DEFAULT_TAXONOMIES = array('category', 'nav_menu');
	
	/**
	 * Required modules (included with fresh installs).
	 * These cannot be unregistered as they are required by core functionality.
	 * @since 1.3.15-beta
	 *
	 * @var array
	 */
	public const REQUIRED_MODULES = array('domtags');
	
	/**
	 * Default themes (included with fresh installs).
	 * Carbon theme cannot be unregistered as it is considered the primary default.
	 * @since 1.3.15-beta
	 *
	 * @var array
	 */
	public const DEFAULT_THEMES = array('carbon');
	
	/**
	 * Default admin themes (included with fresh installs).
	 * Bedrock theme cannot be unregistered as it is considered the primary default.
	 * @since 1.3.15-beta
	 *
	 * @var array
	 */
	public const DEFAULT_ADMIN_THEMES = array('bedrock', 'sky', 'forest', 'ocean', 'sunset', 'harvest');
	
	/*------------------------------------*\
		POST TYPES
	\*------------------------------------*/
	
	/**
	 * Register a post type.
	 * @since 1.0.0-beta
	 *
	 * @access public
	 * @param string $name -- The post type's name.
	 * @param array $args (optional) -- The args.
	 * @return null|array
	 */
	public function registerPostType(string $name, array $args = array()): ?array {
		global $rs_query, $rs_post_types, $rs_taxonomies;
		
		if(!is_array($rs_post_types)) $rs_post_types = array();
		
		$name = sanitize($name);
		
		if(empty($name) || strlen($name) > 20)
			exit('A post type\'s name must be between 1 and 20 characters long.');
		
		// If the name is already registered, abort
		if(postTypeExists($name) || taxonomyExists($name)) return null;
		
		$defaults = array(
			'slug' => $name,
			'title' => null,
			'labels' => array(),
			'actions' => array(),
			'public' => true,
			'hierarchical' => false,
			'create_privileges' => true,
			# 'display_location' => array()
			'show_in_stats_graph' => null,
			'show_in_admin_menu' => null,
			'show_in_admin_bar' => null,
			'show_in_nav_menus' => null,
			'menu_item' => array(
				'link' => 'posts.php?type=' . $name,
				'icon' => null,
				'caption' => null,
				'submenu' => null,
				'index' => null,
				'privileges' => null
			),
			'menu_link' => 'posts.php?type=' . $name, # backward compat
			'menu_icon' => null, # backward compat
			'comments' => false,
			'taxonomies' => array()
		);
		
		// Merge all provided args with the defaults
		if(isset($args['menu_item']))
			$args['menu_item'] = array_merge($defaults['menu_item'], $args['menu_item']);
		
		$args = array_merge($defaults, $args);
		
		// Remove any unrecognized args
		foreach($args as $key => $value)
			if(!array_key_exists($key, $defaults)) unset($args[$key]);
		
		if(is_null($args['show_in_stats_graph'])) $args['show_in_stats_graph'] = $args['public'];
		if(is_null($args['show_in_admin_menu'])) $args['show_in_admin_menu'] = $args['public'];
		if(is_null($args['show_in_admin_bar'])) $args['show_in_admin_bar'] = $args['public'];
		if(is_null($args['show_in_nav_menus'])) $args['show_in_nav_menus'] = $args['public'];
		
		$args['is_default'] = in_array($name, self::DEFAULT_POST_TYPES, true) ? true : false;
		
		// Name, labels, actions, etc.
		$args['name'] = $name;
		$args['labels'] = $this->setPostTypeLabels($name, $args['labels']);
		$args['label'] = $args['labels']['name'];
		$args['actions'] = $this->setPostTypeActions($args['actions']);
		
		if(is_null($args['title']))
			$args['title'] = $args['labels']['name'];
		
		$caption = $args['menu_item']['caption'];
		$args['menu_item']['caption'] = $args['labels'][$caption] ?? ($caption ?? $args['title']);
		
		// Add the post type to the global array
		$rs_post_types[$name] = $args;
		
		if($args['create_privileges']) {
			$name_lowercase = str_replace(' ', '_', $args['labels']['name_lowercase']);
			
			$privileges = array(
				'can_view_' . $name_lowercase,
				'can_create_' . $name_lowercase,
				'can_edit_' . $name_lowercase,
				'can_delete_' . $name_lowercase
			);
			
			$db_privileges = $rs_query->select(getTable('up'), '*', array(
				'name' => array_merge(array('IN'), $privileges),
				'is_default' => ($args['is_default'] ? 1 : 0)
			));
			
			if(empty($db_privileges)) {
				$insert_ids = array();
				
				for($i = 0; $i < count($privileges); $i++) {
					$insert_ids[] = $rs_query->insert(getTable('up'), array(
						'name' => $privileges[$i]
					));
					
					if($privileges[$i] === 'can_view_' . $name_lowercase ||
						$privileges[$i] === 'can_create_' . $name_lowercase ||
						$privileges[$i] === 'can_edit_' . $name_lowercase
					) {
						// Editor
						$rs_query->insert(getTable('ue'), array(
							'role' => getUserRoleId('Editor'),
							'privilege' => $insert_ids[$i]
						));
						
						// Moderator
						$rs_query->insert(getTable('ue'), array(
							'role' => getUserRoleId('Moderator'),
							'privilege' => $insert_ids[$i]
						));
						
						// Administrator
						$rs_query->insert(getTable('ue'), array(
							'role' => getUserRoleId('Administrator'),
							'privilege' => $insert_ids[$i]
						));
					} elseif($privileges[$i] === 'can_delete_' . $name_lowercase) {
						// Moderator
						$rs_query->insert(getTable('ue'), array(
							'role' => getUserRoleId('Moderator'),
							'privilege' => $insert_ids[$i]
						));
						
						// Administrator
						$rs_query->insert(getTable('ue'), array(
							'role' => getUserRoleId('Administrator'),
							'privilege' => $insert_ids[$i]
						));
					}
				}
			}
		}
		
		return $rs_post_types[$name];
	}

	/**
	 * Unregister a post type.
	 * @since 1.0.5-beta
	 *
	 * @access public
	 * @param string $name -- The post type's name.
	 * @param bool $del_posts -- Whether to delete all post data from the database.
	 * @return bool
	 */
	public function unregisterPostType(string $name, bool $del_posts = false): bool {
		global $rs_query, $rs_post_types;
		
		$name = sanitize($name);
		
		if(postTypeExists($name) && !$rs_post_types[$name]['is_default']) {
			if($del_posts) {
				$posts = $rs_query->select(getTable('p'), 'id', array(
					'type' => $name
				));
				
				foreach($posts as $post) {
					$rs_query->delete(getTable('pm'), array(
						'post' => $post['id']
					));
				}
				
				$rs_query->delete(getTable('p'), array(
					'type' => $name
				));
			}
			
			$type = str_replace(' ', '_', $rs_post_types[$name]['labels']['name_lowercase']);
			
			$privileges = array(
				'can_view_' . $type,
				'can_create_' . $type,
				'can_edit_' . $type,
				'can_delete_' . $type
			);
			
			foreach($privileges as $privilege) {
				$rs_query->delete(getTable('ue'), array(
					'privilege' => getUserPrivilegeId($privilege)
				));
				
				$rs_query->delete(getTable('up'), array(
					'name' => $privilege
				));
			}
			
			unset($rs_post_types[$name]);
			return true;
		}
		
		return false;
	}
	
	/**
	 * Set all post type labels.
	 * @since 1.0.1-beta
	 *
	 * @access private
	 * @param string $name -- The post type's name.
	 * @param array $labels (optional) -- Any predefined labels.
	 * @return array
	 */
	private function setPostTypeLabels(string $name, array $labels = array()): array {
		$name_default = capitalize(($name === 'media' ? $name : $name . 's'));
		$name_lowercase = strtolower($name_default);
		$name_singular = capitalize($name);
		
		$defaults = array(
			// Name
			'name' => $name_default,
			'name_lowercase' => $name_lowercase,
			'name_singular' => $name_singular,
			// Headings/captions
			'list_items' => 'List ' . $name_default,
			'create_item' => 'Create ' . $name_singular,
			'create_button' => 'Create New',
			'publish_button' => 'Publish New',
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
	 * Set all post type actions.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @param array $actions (optional) -- Any predefined actions.
	 * @return array
	 */
	private function setPostTypeActions(array $actions = array()): array {
		$defaults = array(
			'create', 'edit', 'delete', 'view', 'preview',
			'upload', 'duplicate', 'replace', 'trash', 'restore'
		);
		
		$set_actions = array();
		
		foreach($defaults as $action) {
			if(in_array($action, $actions, true) || in_array('_defaults_', $actions, true))
				$set_actions[$action] = $action;
			else
				continue;
		}
		
		return $set_actions;
	}
	
	/*------------------------------------*\
		TAXONOMIES
	\*------------------------------------*/
	
	/**
	 * Register a taxonomy.
	 * @since 1.0.1-beta
	 *
	 * @param string $name -- The taxonomy's name.
	 * @param string $post_type -- The associated post type.
	 * @param array $args (optional) -- The args.
	 * @return null|array
	 */
	public function registerTaxonomy(string $name, string $post_type, array $args = array()): ?array {
		global $rs_query, $rs_taxonomies, $rs_post_types;
		
		if(!is_array($rs_taxonomies)) $rs_taxonomies = array();
		
		$name = sanitize($name);
		
		if(empty($name) || strlen($name) > 20)
			exit('A taxonomy\'s name must be between 1 and 20 characters long.');
		
		// If the name is already registered, abort
		if(taxonomyExists($name) || postTypeExists($name)) return null;
		
		$taxonomy = $rs_query->selectRow(getTable('ta'), '*', array(
			'name' => $name
		));
		
		if(empty($taxonomy)) {
			$rs_query->insert(getTable('ta'), array(
				'name' => $name
			));
		}
		
		$defaults = array(
			'slug' => $name,
			'title' => null,
			'labels' => array(),
			'actions' => array(),
			'public' => true,
			'hierarchical' => false,
			'create_privileges' => true,
			'show_in_stats_graph' => null,
			'show_in_admin_menu' => null,
			'show_in_admin_bar' => null,
			'show_in_nav_menus' => null,
			'menu_item' => array(
				'link' => 'terms.php?taxonomy=' . $name,
				'icon' => null,
				'caption' => null,
				'submenu' => null,
				'index' => null,
				'privileges' => null
			),
			'menu_link' => 'terms.php?taxonomy=' . $name, # backward compat
			'default_term' => array(
				'name' => '',
				'slug' => ''
			)
		);
		
		// Merge all provided args with the defaults
		if(isset($args['menu_item']))
			$args['menu_item'] = array_merge($defaults['menu_item'], $args['menu_item']);
		
		$args = array_merge($defaults, $args);
		
		// Remove any unrecognized args
		foreach($args as $key => $value)
			if(!array_key_exists($key, $defaults)) unset($args[$key]);
		
		if(is_null($args['show_in_stats_graph'])) $args['show_in_stats_graph'] = $args['public'];
		if(is_null($args['show_in_admin_menu'])) $args['show_in_admin_menu'] = $args['public'];
		if(is_null($args['show_in_admin_bar'])) $args['show_in_admin_bar'] = $args['public'];
		if(is_null($args['show_in_nav_menus'])) $args['show_in_nav_menus'] = $args['public'];
		
		$args['is_default'] = in_array($name, self::DEFAULT_TAXONOMIES, true) ? true : false;
		$args['post_type'] = $post_type;
		
		// Name, labels, actions, etc.
		$args['name'] = $name;
		$args['labels'] = $this->setTaxonomyLabels($name, $args['labels']);
		$args['label'] = $args['labels']['name'];
		$args['actions'] = $this->setTaxonomyActions($args['actions']);
		
		if(is_null($args['title']))
			$args['title'] = $args['labels']['name'];
		
		$caption = $args['menu_item']['caption'];
		$args['menu_item']['caption'] = $args['labels'][$caption] ?? ($caption ?? $args['title']);
		
		// Add the taxonomy to the global array
		$rs_taxonomies[$name] = $args;
		
		if($args['create_privileges']) {
			$name_lowercase = str_replace(' ', '_', $args['labels']['name_lowercase']);
			
			$privileges = array(
				'can_view_' . $name_lowercase,
				'can_create_' . $name_lowercase,
				'can_edit_' . $name_lowercase,
				'can_delete_' . $name_lowercase
			);
			
			$db_privileges = $rs_query->select(getTable('up'), '*', array(
				'name' => array_merge(array('IN'), $privileges),
				'is_default' => ($args['is_default'] ? 1 : 0)
			));
			
			if(empty($db_privileges)) {
				$insert_ids = array();
				
				for($i = 0; $i < count($privileges); $i++) {
					$insert_ids[] = $rs_query->insert(getTable('up'), array(
						'name' => $privileges[$i]
					));
					
					if($privileges[$i] === 'can_view_' . $name_lowercase ||
						$privileges[$i] === 'can_create_' . $name_lowercase ||
						$privileges[$i] === 'can_edit_' . $name_lowercase
					) {
						// Editor
						$rs_query->insert(getTable('ue'), array(
							'role' => getUserRoleId('Editor'),
							'privilege' => $insert_ids[$i]
						));
						
						// Moderator
						$rs_query->insert(getTable('ue'), array(
							'role' => getUserRoleId('Moderator'),
							'privilege' => $insert_ids[$i]
						));
						
						// Administrator
						$rs_query->insert(getTable('ue'), array(
							'role' => getUserRoleId('Administrator'),
							'privilege' => $insert_ids[$i]
						));
					} elseif($privileges[$i] === 'can_delete_' . $name_lowercase) {
						// Moderator
						$rs_query->insert(getTable('ue'), array(
							'role' => getUserRoleId('Moderator'),
							'privilege' => $insert_ids[$i]
						));
						
						// Administrator
						$rs_query->insert(getTable('ue'), array(
							'role' => getUserRoleId('Administrator'),
							'privilege' => $insert_ids[$i]
						));
					}
				}
			}
		}
		
		if(!empty($args['default_term']['name']) && !empty($args['default_term']['slug'])) {
			$term = $rs_query->selectRow(getTable('t'), 'COUNT(*)', array(
				'slug' => $args['default_term']['slug']
			)) > 0;
			
			if(!$term) {
				$rs_query->insert(getTable('t'), array(
					'name' => $args['default_term']['name'],
					'slug' => $args['default_term']['slug'],
					'taxonomy' => getTaxonomyId($name)
				));
			}
		}
		
		return $rs_taxonomies[$name];
	}

	/**
	 * Unregister a taxonomy.
	 * @since 1.0.5-beta
	 *
	 * @param string $name -- The taxonomy's name.
	 * @param bool $del_terms -- Whether to delete all term data from the database.
	 * @return bool
	 */
	function unregisterTaxonomy(string $name, bool $del_terms = false): bool {
		global $rs_query, $rs_taxonomies;
		
		$name = sanitize($name);
		
		if(taxonomyExists($name) && !$rs_taxonomies[$name]['is_default']) {
			if($del_terms) {
				$terms = $rs_query->select(getTable('t'), 'id', array(
					'taxonomy' => getTaxonomyId($name)
				));
				
				foreach($terms as $term) {
					$rs_query->delete(getTable('tr'), array(
						'term' => $term
					));
					
					$rs_query->delete(getTable('t'), array(
						'id' => $term
					));
				}
				
				$rs_query->delete(getTable('ta'), array(
					'name' => $name
				));
			}
			
			$taxonomy = str_replace(' ', '_', $rs_taxonomies[$name]['labels']['name_lowercase']);
			
			$privileges = array(
				'can_view_' . $taxonomy,
				'can_create_' . $taxonomy,
				'can_edit_' . $taxonomy,
				'can_delete_' . $taxonomy
			);
			
			foreach($privileges as $privilege) {
				$rs_query->delete(getTable('ue'), array(
					'privilege' => getUserPrivilegeId($privilege)
				));
				
				$rs_query->delete(getTable('up'), array(
					'name' => $privilege
				));
			}
			
			unset($rs_taxonomies[$name]);
			return true;
		}
		
		return false;
	}
	
	/**
	 * Set all taxonomy labels.
	 * @since 1.0.4-beta
	 *
	 * @access private
	 * @param string $name -- The taxonomy's name.
	 * @param array $labels (optional) -- Any predefined labels.
	 * @return array
	 */
	private function setTaxonomyLabels(string $name, array $labels = array()): array {
		$name_default = capitalize(($name === 'category' ? 'Categories' : $name . 's'));
		$name_lowercase = strtolower($name_default);
		$name_singular = capitalize($name);
		
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
	 * Set all taxonomy actions.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @param array $actions (optional) -- Any predefined actions.
	 * @return array
	 */
	private function setTaxonomyActions(array $actions = array()): array {
		$defaults = array(
			'create', 'edit', 'delete', 'view'
		);
		
		$set_actions = array();
		
		foreach($defaults as $action) {
			if(in_array($action, $actions, true) || in_array('_defaults_', $actions, true))
				$set_actions[$action] = $action;
			else
				continue;
		}
		
		return $set_actions;
	}
	
	/*------------------------------------*\
		MODULES
	\*------------------------------------*/
	
	/**
	 * Register a module.
	 * @since 1.3.15-beta
	 *
	 * @access public
	 * @param string $name -- The module's name.
	 * @param array $args (optional) -- The args.
	 * @return null|array
	 */
	public function registerModule(string $name, array $args = array()): ?array {
		global $rs_modules;
		
		if(!is_array($rs_modules)) $rs_modules = array();
		
		$name = sanitize($name);
		
		if(empty($name) || strlen($name) > 20)
			exit('A module\'s name must be between 1 and 20 characters long.');
		
		// If the name is already registered, abort
		if(moduleExists($name)) return null;
		
		$defaults = array(
			'label' => ucwords(str_replace(array('_', '-'), ' ', $name)),
			'author' => array(),
			'version' => null,
			'description' => ''
			#'slug' => $name,
			#'labels' => array(),
			#'public' => true,
		);
		
		$args = array_merge($defaults, $args);
		
		// Remove any unrecognized args
		foreach($args as $key => $value)
			if(!array_key_exists($key, $defaults)) unset($args[$key]);
		
		$args['is_required'] = in_array($name, self::REQUIRED_MODULES, true) ? true : false;
		$args['name'] = $name;
		
		// Add the module to the global array
		$rs_modules[$name] = $args;
		
		return $rs_modules[$name];
	}

	/**
	 * Unregister a module.
	 * @since 1.3.15-beta
	 *
	 * @access public
	 * @param string $name -- The module's name.
	 * @param bool $del_data (optional) -- Whether to delete all associated data.
	 * @return bool
	 */
	public function unregisterModule(string $name, bool $del_data = false): bool {
		global $rs_modules;
		
		$name = sanitize($name);
		
		if(moduleExists($name) && !$rs_modules[$name]['is_required']) {
			$file_path = slash(PATH . MODULES) . slash($name);
			
			if($del_data) removeDir($file_path);
			
			unset($rs_modules[$name]);
			return true;
		}
		
		return false;
	}
	
	/*------------------------------------*\
		THEMES
	\*------------------------------------*/
	
	/**
	 * Register a theme.
	 * @since 1.3.15-beta
	 *
	 * @access public
	 * @param string $name -- The theme's name.
	 * @param array $args (optional) -- The args.
	 * @return null|array
	 */
	public function registerTheme(string $name, array $args = array()): ?array {
		global $rs_themes;
		
		if(!is_array($rs_themes)) $rs_themes = array();
		
		$name = sanitize($name);
		
		if(empty($name) || strlen($name) > 20)
			exit('A theme\'s name must be between 1 and 20 characters long.');
		
		// If the name is already registered, abort
		if(themeExists($name)) return null;
		
		$defaults = array(
			'label' => ucwords(str_replace(array('_', '-'), ' ', $name)),
			'author' => array(),
			'version' => null,
			'stylesheet' => null,
			'description' => ''
			#'labels' => array()
		);
		
		$args = array_merge($defaults, $args);
		
		// Remove any unrecognized args
		foreach($args as $key => $value)
			if(!array_key_exists($key, $defaults)) unset($args[$key]);
		
		$args['stylesheet'] = slash(PATH . THEMES) . slash($name) . 'style.css';
		$args['is_default'] = in_array($name, self::DEFAULT_THEMES, true) ? true : false;
		$args['name'] = $name;
		
		// Add the admin theme to the global array
		$rs_themes[$name] = $args;
		
		return $rs_themes[$name];
	}
	
	/**
	 * Unregister a theme.
	 * @since 1.3.15-beta
	 *
	 * @access public
	 * @param string $name -- The theme's name.
	 * @param bool $del_data (optional) -- Whether to delete all associated data.
	 * @return bool
	 */
	public function unregisterTheme(string $name, bool $del_data = false): bool {
		global $rs_themes;
		
		$name = sanitize($name);
		
		if(themeExists($name) && $name !== 'carbon') {
			$file_path = slash(PATH . THEMES) . slash($name);
			
			if($del_data) removeDir($file_path);
			
			unset($rs_admin_themes[$name]);
			return true;
		}
		
		return false;
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
			'label' => ucwords(str_replace(array('_', '-'), ' ', $name)),
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