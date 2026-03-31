<?php
/**
 * Core class used to implement the Menu object.
 * This class loads data from the `terms`, `term_relationships`, `posts`, and `postmeta` tables of the database
 *  for use on the front end.
 * @since 2.2.3-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Engine
 *
 * ## OBJECT VAR ##
 * - $rs_menu
 *
 * ## VARIABLES [1] ##
 * - private string $slug
 *
 * ## METHODS [10] ##
 * - public __construct(string $slug)
 * - public getMenu(): void
 * - public getSubmenu(int $id): string
 * { GETTER METHODS [3] }
 * - public getMenuId(): int
 * - private getMenuItemMeta(int $id): array
 * - private getMenuItemParent(int $id): int
 * { MISCELLANEOUS [4] }
 * - private getMenuItemTree(int $id, array $items, bool $is_top_level): array
 * - private isCurrentPage(string $uri): bool
 * - private menuItemHasParent(int $id): bool
 * - private menuItemHasChildren(int $id): bool
 */
namespace Engine;

class Menu {
	/**
	 * The currently queried menu's slug.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @var string
	 */
	private $slug;
	
	/**
	 * Class constructor. Sets the default queried menu slug.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access public
	 * @param string $slug (optional) -- The menu's slug.
	 */
	public function __construct(string $slug = '') {
		$this->slug = $slug;
	}
	
	/**
	 * Construct a nav menu.
	 * @since 2.2.2-alpha
	 *
	 * @access public
	 */
	public function getMenu(): void {
		global $rs_query;
		
		$id = $this->getMenuId();
		
		$relationships = $rs_query->select(getTable('tr'), 'post', array(
			'term' => $id
		));
		
		$relationships = array_map(function($relationship) {
			return array(
				'id' => $relationship['post']
			);
		}, $relationships);
		
		domTagPr('nav', array(
			'class' => 'nav-menu menu-id-' . $id,
			'content' => domTag('ul', array(
				'content' => implode('', $this->getMenuItemTree($id, $relationships, true))
			))
		));
	}
	
	/**
	 * Construct a submenu.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access public
	 * @param int $id -- The parent menu item's id.
	 * @return string
	 */
	public function getSubmenu(int $id): string {
		global $rs_query;
		
		$children = $rs_query->select(getTable('p'), 'id', array(
			'parent' => $id
		));
		
		return domTag('ul', array(
			'class' => 'sub-menu',
			'content' => implode('', $this->getMenuItemTree($id, $children, false))
		));
	}
	
	/*------------------------------------*\
		GETTER METHODS
	\*------------------------------------*/
	
	/**
	 * Fetch the menu's id.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access public
	 * @return int
	 */
	public function getMenuId(): int {
		global $rs_query;
		
		return (int)$rs_query->selectField(getTable('t'), 'id', array(
			'slug' => $this->slug
		));
	}
	
	/**
	 * Fetch a menu item's metadata.
	 * @since 2.2.2-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @return array
	 */
	private function getMenuItemMeta(int $id): array {
		global $rs_query;
		
		$itemmeta = $rs_query->select(getTable('pm'), array('key', 'value'), array(
			'post' => $id
		));
		
		$meta = array();
		
		foreach($itemmeta as $metadata) {
			$values = array_values($metadata);
			
			for($i = 0; $i < count($metadata); $i += 2)
				$meta[$values[$i]] = $values[$i + 1];
		}
		
		return $meta;
	}

	/**
	 * Fetch a menu item's parent.
	 * @since 2.2.2-alpha
	 *
	 * @access private
	 * @param int $id -- The child menu item's id.
	 * @return int
	 */
	private function getMenuItemParent(int $id): int {
		global $rs_query;
		
		return (int)$rs_query->selectField(getTable('p'), 'id', array(
			'id' => $id
		));
	}
	
	/*------------------------------------*\
		MISCELLANEOUS
	\*------------------------------------*/
	
	/**
	 * Fetch all descendants of a menu item.
	 * @since 2.2.2-alpha
	 *
	 * @access private
	 * @param int $id -- The parent menu item's id.
	 * @param array $items -- The menu items to loop through.
	 * @param bool $is_top_level -- Whether the menu item is on the top level (i.e., not a subitem).
	 * @return array
	 */
	private function getMenuItemTree(int $id, array $items, bool $is_top_level): array {
		global $rs_query, $rs_post_types, $rs_taxonomies;
		
		$submenu_content = array();
		$itemmeta = array();
		$i = 0;
		
		foreach($items as $item) {
			$itemmeta[] = $this->getMenuItemMeta($item['id']);
			$itemmeta[$i] = array_reverse($itemmeta[$i]);
			$itemmeta[$i]['post'] = $item['id'];
			$i++;
		}
		
		// Sort the array in ascending index order
		asort($itemmeta);
		
		foreach($itemmeta as $meta) {
			$menu_item = $rs_query->selectRow(getTable('p'), array('id', 'title', 'status'), array(
				'id' => $meta['post']
			));
			
			// Skip over invalid items
			if($menu_item['status'] === 'invalid' || ($this->menuItemHasParent($menu_item['id']) && $is_top_level === true))
				continue;
			
			$domain = $_SERVER['HTTP_HOST'];
			$permalink = '';
			$external = false;
			
			if(isset($meta['post_link'])) {
				$type = $rs_query->selectField(getTable('p'), 'type', array(
					'id' => $meta['post_link']
				));
				
				if(!empty($type) && $rs_post_types[$type]['show_in_nav_menus']) {
					$permalink = isHomePage((int)$meta['post_link']) ? '/' :
						getPermalink($type, $this->getMenuItemParent($meta['post_link']));
				}
			} elseif(isset($meta['term_link'])) {
				$tax_id = $rs_query->selectField(getTable('t'), 'taxonomy', array(
					'id' => $meta['term_link']
				));
				
				$taxonomy = $rs_query->selectField(getTable('ta'), 'name', array(
					'id' => $tax_id
				));
				
				if(!empty($taxonomy) && $rs_taxonomies[$taxonomy]['show_in_nav_menus'])
					$permalink = getPermalink($taxonomy, $this->getMenuItemParent($meta['term_link']));
			} elseif(isset($meta['custom_link'])) {
				$permalink = $meta['custom_link'];
				
				// Set up external links
				if(!str_contains($permalink, $domain)) $external = true;
			}
			
			if(!empty($permalink)) {
				$classes = array();
				
				if($this->isCurrentPage($permalink)) $classes[] = 'current-menu-item';
				if($this->menuItemHasChildren($menu_item['id'])) $classes[] = 'menu-item-has-children';
				
				// Sort the classes to make sure they're in alphabetical order
				asort($classes);
				
				$tag_args = array(
					'href' => $permalink
				);
				
				if($external === true) {
					$tag_args['target'] = '_blank';
					$tag_args['rel'] = 'noreferrer noopener';
				}
				
				$tag_args['content'] = $menu_item['title'];
				
				$submenu_content[] = domTag('li', array(
					'class' => !empty($classes) ? implode(' ', $classes) : '',
					'content' => domTag('a', $tag_args) . ($this->menuItemHasChildren($menu_item['id']) ?
						$this->getSubmenu($menu_item['id']) : ''
					)
				));
			}
		}
		
		return $submenu_content;
	}
	
	/**
	 * Check whether a menu item's URI matches the current page URI.
	 * @since 2.2.3-alpha
	 *
	 * @access private
	 * @param string $uri -- The page URI.
	 * @return bool
	 */
	private function isCurrentPage(string $uri): bool {
		return $uri === $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Check whether a menu item has a parent.
	 * @since 2.2.2-alpha
	 *
	 * @access private
	 * @param int $id -- The child menu item's id.
	 * @return bool
	 */
	private function menuItemHasParent(int $id): bool {
		global $rs_query;
		
		return (int)$rs_query->selectField(getTable('p'), 'parent', array(
			'id' => $id
		)) !== 0;
	}

	/**
	 * Check whether a menu item has children.
	 * @since 2.2.2-alpha
	 *
	 * @access private
	 * @param int $id -- The parent menu item's id.
	 * @return bool
	 */
	private function menuItemHasChildren(int $id): bool {
		global $rs_query;
		
		return $rs_query->select(getTable('p'), 'COUNT(*)', array(
			'parent' => $id
		)) > 0;
	}
}