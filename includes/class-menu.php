<?php
/**
 * Core class used to implement the Menu object.
 * @since 2.2.3[a]
 *
 * This class loads data from the terms, term relationships, posts, and postmeta tables of the database for use on the front end of the CMS.
 */
class Menu {
	/**
	 * Construct a nav menu.
	 * @since 2.2.2[a]
	 *
	 * @access public
	 * @param string $slug -- The menu's slug.
	 */
	public function getMenu($slug): void {
		global $rs_query, $post_types, $taxonomies;
		
		$id = $rs_query->selectField('terms', 'id', array('slug' => $slug));
		?>
		<nav class="nav-menu menu-id-<?php echo $id; ?>">
			<ul>
				<?php
				$relationships = $rs_query->select('term_relationships', 'post', array(
					'term' => $id
				));
				$itemmeta = array();
				$i = 0;
				
				foreach($relationships as $relationship) {
					$itemmeta[] = $this->getMenuItemMeta($relationship['post']);
					$itemmeta[$i] = array_reverse($itemmeta[$i]);
					$itemmeta[$i]['post'] = $relationship['post'];
					$i++;
				}
				
				// Sort the array in ascending index order
				asort($itemmeta);
				
				foreach($itemmeta as $meta) {
					$menu_item = $rs_query->selectRow('posts', array('id', 'title', 'status'), array(
						'id' => $meta['post']
					));
					
					// Skip over invalid items
					if($menu_item['status'] === 'invalid') continue;
					
					if(!$this->menuItemHasParent($menu_item['id'])) {
						$domain = $_SERVER['HTTP_HOST'];
						$permalink = '';
						$external = false;
						
						if(isset($meta['post_link'])) {
							$type = $rs_query->selectField('posts', 'type', array('id' => $meta['post_link']));
							
							if(!empty($type) && $post_types[$type]['show_in_nav_menus']) {
								$permalink = isHomePage((int)$meta['post_link']) ? '/' :
									getPermalink($type, $this->getMenuItemParent($meta['post_link']));
							}
						} elseif(isset($meta['term_link'])) {
							$tax_id = $rs_query->selectField('terms', 'taxonomy', array(
								'id' => $meta['term_link']
							));
							$taxonomy = $rs_query->selectField('taxonomies', 'name', array('id' => $tax_id));
							
							if(!empty($taxonomy) && $taxonomies[$taxonomy]['show_in_nav_menus']) {
								$permalink = getPermalink($taxonomy, $this->getMenuItemParent($meta['term_link']));
							}
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
							?>
							<li<?php echo !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : ''; ?>>
								<a href="<?php echo $permalink; ?>"<?php echo $external === true ? ' target="_blank" rel="noreferrer noopener"' : ''; ?>><?php echo $menu_item['title']; ?></a>
								<?php
								if($this->menuItemHasChildren($menu_item['id']))
									$this->getMenuItemDescendants($menu_item['id']);
								?>
							</li>
							<?php
						}
					}
				}
				?>
			</ul>
		</nav>
		<?php
	}
	
	/**
	 * Check whether a menu item's URI matches the current page URI.
	 * @since 2.2.3[a]
	 *
	 * @access private
	 * @param string $uri -- The page URI.
	 * @return bool
	 */
	private function isCurrentPage($uri): bool {
		global $rs_query;
		
		return $uri === $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Check whether a menu item has a parent.
	 * @since 2.2.2[a]
	 *
	 * @access private
	 * @param int $id -- The child menu item's id.
	 * @return bool
	 */
	private function menuItemHasParent($id): bool {
		global $rs_query;
		
		return (int)$rs_query->selectField('posts', 'parent', array('id' => $id)) !== 0;
	}

	/**
	 * Check whether a menu item has children.
	 * @since 2.2.2[a]
	 *
	 * @access private
	 * @param int $id -- The parent menu item's id.
	 * @return bool
	 */
	private function menuItemHasChildren($id): bool {
		global $rs_query;
		
		return $rs_query->select('posts', 'COUNT(*)', array('parent' => $id)) > 0;
	}

	/**
	 * Fetch a menu item's metadata.
	 * @since 2.2.2[a]
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @return array
	 */
	private function getMenuItemMeta($id): array {
		global $rs_query;
		
		$itemmeta = $rs_query->select('postmeta', array('_key', 'value'), array('post' => $id));
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
	 * @since 2.2.2[a]
	 *
	 * @access private
	 * @param int $id
	 * @return int
	 */
	private function getMenuItemParent($id): int {
		global $rs_query;
		
		return $rs_query->selectField('posts', 'id', array('id' => $id));
	}

	/**
	 * Fetch all descendants of a menu item.
	 * @since 2.2.2[a]
	 *
	 * @access private
	 * @param int $id
	 */
	private function getMenuItemDescendants($id): void {
		// Extend the Query object and the post types array
		global $rs_query, $post_types;
		?>
		<ul class="sub-menu">
			<?php
			$children = $rs_query->select('posts', 'id', array('parent' => $id));
			$itemmeta = array();
			$i = 0;
			
			foreach($children as $child) {
				$itemmeta[] = $this->getMenuItemMeta($child['id']);
				$itemmeta[$i] = array_reverse($itemmeta[$i]);
				$itemmeta[$i]['post'] = $child['id'];
				$i++;
			}
			
			// Sort the array in ascending index order
			asort($itemmeta);
			
			foreach($itemmeta as $meta) {
				$menu_item = $rs_query->selectRow('posts', array('id', 'title'), array('id' => $meta['post']));
				$domain = $_SERVER['HTTP_HOST'];
				$permalink = '';
				$external = false;
				
				if(isset($meta['post_link'])) {
					$type = $rs_query->selectField('posts', 'type', array('id' => $meta['post_link']));
					
					if($post_types[$type]['show_in_nav_menus']) {
						$permalink = isHomePage((int)$meta['post_link']) ? '/' :
							getPermalink($type, $this->getMenuItemParent($meta['post_link']));
					}
				} elseif(isset($meta['term_link'])) {
					$permalink = getPermalink('category', $this->getMenuItemParent($meta['term_link']));
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
					?>
					<li<?php echo !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : ''; ?>>
						<a href="<?php echo $permalink; ?>"<?php echo $external === true ? ' target="_blank" rel="noreferrer noopener"' : ''; ?>><?php echo $menu_item['title']; ?></a>
						<?php
						if($this->menuItemHasChildren($menu_item['id']))
							$this->getMenuItemDescendants($menu_item['id']);
						?>
					</li>
					<?php
				}
			}
			?>
		</ul>
		<?php
	}
}