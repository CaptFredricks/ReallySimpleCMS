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
	 * @param string $slug
	 * @return null
	 */
	public function getMenu($slug) {
		// Extend the Query object and the post types and taxonomies arrays
		global $rs_query, $post_types, $taxonomies;
		
		// Fetch the menu's id from the database
		$id = $rs_query->selectField('terms', 'id', array('slug'=>$slug));
		?>
		<nav class="nav-menu menu-id-<?php echo $id; ?>">
			<ul>
				<?php
				// Fetch the term relationships from the database
				$relationships = $rs_query->select('term_relationships', 'post', array('term'=>$id));
				
				// Create an empty array to hold the menu items' metadata
				$itemmeta = array();
				
				// Create an index counter for the metadata array
				$i = 0;
				
				// Loop through the term relationships
				foreach($relationships as $relationship) {
					// Fetch the metadata associated with each menu item from the database
					$itemmeta[] = $this->getMenuItemMeta($relationship['post']);
					
					// Reverse the array (to place the index first)
					$itemmeta[$i] = array_reverse($itemmeta[$i]);
					
					// Push the menu item's id onto the array
					$itemmeta[$i]['post'] = $relationship['post'];
					
					// Increment the index counter
					$i++;
				}
				
				// Sort the array in ascending index order
				asort($itemmeta);
				
				// Loop through the menu items' metadata
				foreach($itemmeta as $meta) {
					// Fetch the menu item from the database
					$menu_item = $rs_query->selectRow('posts', array('id', 'title', 'status'), array('id'=>$meta['post']));
					
					// Check whether the menu item is invalid
					if($menu_item['status'] === 'invalid') continue;
					
					// Check whether the menu item has a parent or is on the top level
					if(!$this->menuItemHasParent($menu_item['id'])) {
						// Fetch the site's domain
						$domain = $_SERVER['HTTP_HOST'];
						
						// Create an empty variable to hold the menu item's permalink
						$permalink = '';
						
						// Create an external link flag
						$external = false;
						
						// Check what type of link is being used
						if(isset($meta['post_link'])) {
							// Fetch the type of the post the menu item points to
							$type = $rs_query->selectField('posts', 'type', array('id'=>$meta['post_link']));
							
							// Check whether the post type is set to display in nav menus
							if(!empty($type) && $post_types[$type]['show_in_nav_menus']) {
								// Construct the permalink
								$permalink = isHomePage((int)$meta['post_link']) ? '/' : getPermalink($type, $this->getMenuItemParent($meta['post_link']));
							}
						} elseif(isset($meta['term_link'])) {
							// Fetch the taxonomy's id of the term the menu item points to
							$tax_id = $rs_query->selectField('terms', 'taxonomy', array('id'=>$meta['term_link']));
							
							// Fetch the taxonomy of the term the menu item points to
							$taxonomy = $rs_query->selectField('taxonomies', 'name', array('id'=>$tax_id));
							
							// Check whether the taxonomy is set to display in nav menus
							if(!empty($taxonomy) && $taxonomies[$taxonomy]['show_in_nav_menus']) {
								// Construct the permalink
								$permalink = getPermalink($taxonomy, $this->getMenuItemParent($meta['term_link']));
							}
						} elseif(isset($meta['custom_link'])) {
							// Fetch the permalink
							$permalink = $meta['custom_link'];
							
							// Check whether the menu item links to an external site and set the flag to true if so
							if(strpos($permalink, 'http') !== false && strpos($permalink, $domain) === false) $external = true;
						}
						
						// Check whether a permalink has been constructed
						if(!empty($permalink)) {
							// Create an empty array to hold classes for the menu items
							$classes = array();
							
							// Check whether the permalink matches the current page's URI and assign it the appropriate class if so
							if($this->isCurrentPage($permalink)) $classes[] = 'current-menu-item';
							
							// Check whether the menu item has children and assign it the appropriate class if so
							if($this->menuItemHasChildren($menu_item['id'])) $classes[] = 'menu-item-has-children';
							
							// Sort the classes to make sure they're in alphabetical order
							asort($classes);
							?>
							<li<?php echo !empty($classes) ? ' class="'.implode(' ', $classes).'"' : ''; ?>>
								<a href="<?php echo $permalink; ?>"<?php echo $external === true ? ' target="_blank" rel="noreferrer noopener"' : ''; ?>><?php echo $menu_item['title']; ?></a>
								<?php
								// Check whether the menu item has descendants and fetch any that exist
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
	 * @param string $uri
	 * @return bool
	 */
	private function isCurrentPage($uri) {
		// Extend the Query object
		global $rs_query;
		
		// Return true if the provided URI matches the current page's URI
		return $uri === $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Check whether a menu item has a parent.
	 * @since 2.2.2[a]
	 *
	 * @access private
	 * @param int $id
	 * @return bool
	 */
	private function menuItemHasParent($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the menu item's parent id from the database and return true if it's not equal to zero
		return (int)$rs_query->selectField('posts', 'parent', array('id'=>$id)) !== 0;
	}

	/**
	 * Check whether a menu item has children.
	 * @since 2.2.2[a]
	 *
	 * @access private
	 * @param int $id
	 * @return bool
	 */
	private function menuItemHasChildren($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the number of children the menu item has from the database and return true if it's greater than zero
		return $rs_query->select('posts', 'COUNT(*)', array('parent'=>$id)) > 0;
	}

	/**
	 * Fetch a menu item's metadata.
	 * @since 2.2.2[a]
	 *
	 * @access private
	 * @param int $id
	 * @return array
	 */
	private function getMenuItemMeta($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the menu item's metadata from the database
		$itemmeta = $rs_query->select('postmeta', array('_key', 'value'), array('post'=>$id));
		
		// Create an empty array to hold the metadata
		$meta = array();
		
		// Loop through the metadata
		foreach($itemmeta as $metadata) {
			// Get the meta values
			$values = array_values($metadata);
			
			// Loop through the individual metadata entries
			for($i = 0; $i < count($metadata); $i += 2) {
				// Assign the metadata to the meta array
				$meta[$values[$i]] = $values[$i + 1];
			}
		}
		
		// Return the metadata
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
	private function getMenuItemParent($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the menu item's parent id from the database and return it
		return $rs_query->selectField('posts', 'id', array('id'=>$id));
	}

	/**
	 * Fetch all descendants of a menu item.
	 * @since 2.2.2[a]
	 *
	 * @access private
	 * @param int $id
	 * @return null
	 */
	private function getMenuItemDescendants($id) {
		// Extend the Query object and the post types array
		global $rs_query, $post_types;
		?>
		<ul class="sub-menu">
			<?php
			// Select any existing children from the database
			$children = $rs_query->select('posts', 'id', array('parent'=>$id));
			
			// Create an empty array to hold the menu items' metadata
			$itemmeta = array();
			
			// Create an index counter for the metadata array
			$i = 0;
			
			// Loop through the children
			foreach($children as $child) {
				// Fetch the metadata associated with each menu item from the database
				$itemmeta[] = $this->getMenuItemMeta($child['id']);
				
				// Reverse the array (to place the index first)
				$itemmeta[$i] = array_reverse($itemmeta[$i]);
				
				// Push the menu item's id onto the array
				$itemmeta[$i]['post'] = $child['id'];
				
				// Increment the index counter
				$i++;
			}
			
			// Sort the array in ascending index order
			asort($itemmeta);
			
			// Loop through the menu items' metadata
			foreach($itemmeta as $meta) {
				// Fetch the menu item from the database
				$menu_item = $rs_query->selectRow('posts', array('id', 'title'), array('id'=>$meta['post']));
				
				// Fetch the site's domain
				$domain = $_SERVER['HTTP_HOST'];
				
				// Create an empty variable to hold the menu item's permalink
				$permalink = '';
				
				// Create an external link flag
				$external = false;
				
				// Check what type of link is being used
				if(isset($meta['post_link'])) {
					// Fetch the type of the post the menu item points to
					$type = $rs_query->selectField('posts', 'type', array('id'=>$meta['post_link']));
					
					// Check whether the post type is set to display in nav menus
					if($post_types[$type]['show_in_nav_menus']) {
						// Construct the permalink
						$permalink = isHomePage((int)$meta['post_link']) ? '/' : getPermalink($type, $this->getMenuItemParent($meta['post_link']));
					}
				} elseif(isset($meta['term_link'])) {
					// Construct the permalink
					$permalink = getPermalink('category', $this->getMenuItemParent($meta['term_link']));
				} elseif(isset($meta['custom_link'])) {
					// Fetch the permalink
					$permalink = $meta['custom_link'];
					
					// Check whether the menu item links to an external site and set the flag to true if so
					if(strpos($permalink, 'http') !== false && strpos($permalink, $domain) === false) $external = true;
				}
				
				// Check whether a permalink has been constructed
				if(!empty($permalink)) {
					// Create an empty array to hold classes for the menu items
					$classes = array();
					
					// Check whether the permalink matches the current page's URI and assign it the appropriate class if so
					if($this->isCurrentPage($permalink)) $classes[] = 'current-menu-item';
					
					// Check whether the menu item has children and assign it the appropriate class if so
					if($this->menuItemHasChildren($menu_item['id'])) $classes[] = 'menu-item-has-children';
					
					// Sort the classes to make sure they're in alphabetical order
					asort($classes);
					?>
					<li<?php echo !empty($classes) ? ' class="'.implode(' ', $classes).'"' : ''; ?>>
						<a href="<?php echo $permalink; ?>"<?php echo $external === true ? ' target="_blank" rel="noreferrer noopener"' : ''; ?>><?php echo $menu_item['title']; ?></a>
						<?php
						// Check whether the menu item has descendants and fetch any that exist
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