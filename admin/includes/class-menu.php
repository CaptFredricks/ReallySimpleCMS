<?php
/**
 * Admin class used to implement the Menu object. Inherits from the Term class.
 * @since 1.8.0[a]
 *
 * Menus are used for website navigation on the front end of the website.
 * Menus can be created, modified, and deleted. Menus are stored in the 'terms' table under the 'nav_menu' taxonomy. Menu items are stored in the 'posts' table as the 'nav_menu_item' post type.
 */
class Menu extends Term {
	/**
	 * The number of members in a menu item's family tree.
	 * @since 1.8.7[a]
	 *
	 * @access private
	 * @var int
	 */
	private $members = 0;
	
	/**
	 * Class constructor.
	 * @since 1.1.1[b]
	 *
	 * @access public
	 * @param int $id (optional; default: 0)
	 */
	public function __construct($id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Create an array of columns to fetch from the database
		$cols = array_keys(get_object_vars($this));
		
		// Exclude 'members'
		$exclude = array('members');
		
		// Update the columns array
		$cols = array_diff($cols, $exclude);
		
		// Check whether the id is '0'
		if($id !== 0) {
			// Fetch the menu from the database
			$menu = $rs_query->selectRow('terms', $cols, array(
				'id' => $id,
				'taxonomy' => getTaxonomyId('nav_menu')
			));
			
			// Set the class variable values
			foreach($menu as $key => $value) $this->$key = $menu[$key];
		}
	}
	
	/**
	 * Construct a list of all menus in the database.
	 * @since 1.8.0[a]
	 *
	 * @access public
	 */
	public function listMenus(): void {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Menus</h1>
			<?php
			// Check whether the user has sufficient privileges to create menus and create an action link if so
			if(userHasPrivilege($session['role'], 'can_create_menus'))
				echo actionLink('create', array('classes' => 'button', 'caption' => 'Create New'));
			
			// Display the page's info
			adminInfo();
			?>
			<hr>
			<?php
			// Check whether any status messages have been returned and display them if so
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The menu was successfully deleted.', true);
			
			// Fetch the menu entry count from the database
			$count = $rs_query->select('terms', 'COUNT(*)', array('taxonomy' => getTaxonomyId('nav_menu')));
			
			// Set the page count
			$page['count'] = ceil($count / $page['per_page']);
			?>
			<div class="entry-count">
				<?php
				// Display the entry count
				echo $count.' '.($count === 1 ? 'entry' : 'entries');
				?>
			</div>
		</div>
		<table class="data-table">
			<thead>
				<?php
				// Fill an array with the table header columns
				$table_header_cols = array('Name', 'Item Count');
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all menus from the database
				$menus = $rs_query->select('terms', '*', array(
					'taxonomy' => getTaxonomyId('nav_menu')
				), 'name', 'ASC', array($page['start'], $page['per_page']));
				
				foreach($menus as $menu) {
					// Set up the action links
					$actions = array(
						// Edit
						userHasPrivilege($session['role'], 'can_edit_menus') ? actionLink('edit', array(
							'caption' => 'Edit',
							'id' => $menu['id']
						)) : null,
						// Delete
						userHasPrivilege($session['role'], 'can_delete_menus') ? actionLink('delete', array(
							'classes' => 'modal-launch delete-item',
							'data_item' => 'menu',
							'caption' => 'Delete',
							'id' => $menu['id']
						)) : null
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						// Name
						tdCell('<strong>'.$menu['name'].'</strong><div class="actions">'.implode(' &bull; ', $actions).'</div>', 'name'),
						// Item count
						tdCell($menu['count'], 'count')
					);
				}
				
				// Display a notice if no menus are found
				if(empty($menus))
					echo tableRow(tdCell('There are no menus to display.', '', count($table_header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($table_header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		pagerNav($page['current'], $page['count']);
		
		// Include the delete modal
        include_once PATH.ADMIN.INC.'/modal-delete.php';
	}
	
	/**
	 * Create a menu.
	 * @since 1.8.0[a]
	 *
	 * @access public
	 */
	public function createMenu(): void {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateMenuData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1>Create Menu</h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<div class="content">
					<?php
					// Construct a 'name' form tag
					echo formTag('input', array(
						'id' => 'name-field',
						'class' => 'text-input required invalid init',
						'name' => 'name',
						'value' => ($_POST['name'] ?? ''),
						'placeholder' => 'Menu name'
					));
					
					// Construct a 'slug' form tag
					echo formTag('input', array(
						'id' => 'slug-field',
						'class' => 'text-input required invalid init',
						'name' => 'slug',
						'value' => ($_POST['slug'] ?? ''),
						'placeholder' => 'Menu slug'
					));
					?>
					<div class="block">
						<?php
						// Construct a list of the items on the menu
						echo $this->getMenuItems();
						?>
					</div>
				</div>
				<div class="sidebar">
					<div class="block">
						<h2>Add Menu Items</h2>
						<div class="row">
							<?php
							// Construct the 'menu items' sidebar
							$this->getMenuItemsSidebar();
							?>
						</div>
						<div id="submit" class="row">
							<?php
							// Construct the 'submit' button form tag
							echo formTag('input', array(
								'type' => 'submit',
								'class' => 'submit-input button',
								'name' => 'submit',
								'value' => 'Create'
							));
							?>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Edit a menu.
	 * @since 1.8.0[a]
	 *
	 * @access public
	 */
	public function editMenu(): void {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the menu's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Menus" page
			redirect(ADMIN_URI);
		} else {
			// Check whether the menu item's id is set
			if(isset($_GET['item_id'])) {
				// Fetch the menu item's id
				$item_id = (int)$_GET['item_id'];
				
				// Check whether the menu item's id is valid
				if(empty($item_id) || $item_id <= 0) {
					// Redirect to the "Edit Menu" page
					redirect(ADMIN_URI.'?id='.$this->id.'&action=edit');
				} else {
					// Fetch the number of times the menu item appears in the database
					$count = $rs_query->selectRow('posts', 'COUNT(*)', array(
						'id' => $item_id,
						'type' => 'nav_menu_item'
					));
					
					// Check whether the count is zero and redirect to the "Edit Menu" page if so
					if($count === 0)
						redirect(ADMIN_URI.'?id='.$this->id.'&action=edit');
				}
			}
			
			// Validate the form data and return any messages
			$message = isset($_POST['submit']) ? $this->validateMenuData($_POST, $this->id) : '';
			?>
			<div class="heading-wrap">
				<h1>Edit Menu</h1>
				<?php echo $message; ?>
			</div>
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off">
					<div class="content">
						<?php
						// Construct a 'name' form tag
						echo formTag('input', array(
							'id' => 'name-field',
							'class' => 'text-input required invalid init',
							'name' => 'name',
							'value' => $this->name,
							'placeholder' => 'Menu name'
						));
						
						// Construct a 'slug' form tag
						echo formTag('input', array(
							'id' => 'slug-field',
							'class' => 'text-input required invalid init',
							'name' => 'slug',
							'value' => $this->slug,
							'placeholder' => 'Menu slug'
						));
						?>
					</div>
					<div class="sidebar">
						<div class="block">
							<h2>Add Menu Items</h2>
							<div class="row">
								<?php
								// Construct the 'menu items' sidebar
								$this->getMenuItemsSidebar();
								?>
							</div>
							<div id="submit" class="row">
								<?php
								// Construct the 'submit' button form tag
								echo formTag('input', array(
									'type' => 'submit',
									'class' => 'submit-input button',
									'name' => 'submit',
									'value' => 'Update'
								));
								?>
							</div>
						</div>
					</div>
				</form>
				<div class="item-list-wrap">
					<?php
					// Construct a list of the items on the menu
					echo $this->getMenuItems($this->id);
					?>
				</div>
			</div>
			<?php
		}
	}
	
	/**
	 * Delete a menu.
	 * @since 1.8.1[a]
	 *
	 * @access public
	 */
	public function deleteMenu(): void {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the menu's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Menus" page
			redirect(ADMIN_URI);
		} else {
			// Delete the menu from the database
			$rs_query->delete('terms', array('id' => $this->id, 'taxonomy' => getTaxonomyId('nav_menu')));
			
			// Fetch all term relationships associated with the menu from the database
			$relationships = $rs_query->select('term_relationships', 'post', array('term' => $this->id));
			
			// Delete all term relationships associated with the menu from the database
			$rs_query->delete('term_relationships', array('term' => $this->id));
			
			foreach($relationships as $relationship) {
				// Delete each menu item associated with the menu from the database
				$rs_query->delete('posts', array('id' => $relationship['post']));
				
				// Delete each menu item's metadata from the database
				$rs_query->delete('postmeta', array('post' => $relationship['post']));
			}
		}
		
		// Redirect to the "List Menus" page with an appropriate exit status
		redirect(ADMIN_URI.'?exit_status=success');
	}
	
	/**
	 * Validate the menu form data.
	 * @since 1.8.0[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function validateMenuData($data, $id = 0): string {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['name']) || empty($data['slug']))
			return statusMessage('R');
		
		// Sanitize the slug
		$slug = sanitize($data['slug']);
		
		// Make sure the slug is unique
		if($this->slugExists($slug, $id))
			$slug = getUniqueTermSlug($slug);
		
		if($id === 0) {
			// Insert the new menu into the database
			$menu_id = $rs_query->insert('terms', array(
				'name' => $data['name'],
				'slug' => $slug,
				'taxonomy' => getTaxonomyId('nav_menu')
			));
			
			// Check whether any menu items have been selected
			if(!empty($data['menu_items'])) {
				// Assign the menu item data to a variable
				$menu_items = $data['menu_items'];
				
				for($i = 0; $i < count($menu_items); $i++) {
					// Split the menu item data into separate variables
					list($item_type, $item_id) = explode('-', $menu_items[$i]);
					
					// Create a new menu item
					$itemmeta = $this->createMenuItem($item_type, $item_id, $i);
					
					// Retrieve the first element from the itemmeta array
					$menu_item_id = array_shift($itemmeta);
					
					// Insert the menu item's metadata into the database
					foreach($itemmeta as $key => $value) {
						$rs_query->insert('postmeta', array(
							'post' => $menu_item_id,
							'_key' => $key,
							'value' => $value
						));
					}
					
					// Insert a new term relationship into the database
					$rs_query->insert('term_relationships', array('term' => $menu_id, 'post' => $menu_item_id));
				}
				
				// Update the menu's count (nav items)
				$rs_query->update('terms', array('count' => count($menu_items)), array('id' => $menu_id));
			}
			
			// Check whether a custom menu item has been added
			if(!empty($data['custom_title']) && !empty($data['custom_link'])) {
				// Insert the new menu item into the database
				$menu_item_id = $rs_query->insert('posts', array(
					'title' => $data['custom_title'],
					'date' => 'NOW()',
					'modified' => 'NOW()',
					'slug' => '',
					'type' => 'nav_menu_item'
				));
				
				// Update the menu item's slug in the database
				$rs_query->update('posts', array('slug' => 'menu-item-'.$menu_item_id), array('id' => $menu_item_id));
				
				// Fetch the number of menu items associated with the menu
				$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $menu_id));
				
				// Create an array to hold the menu item's metadata
				$itemmeta = array('custom_link' => $data['custom_link'], 'menu_index' => $count);
				
				// Insert the menu item's metadata into the database
				foreach($itemmeta as $key => $value) {
					$rs_query->insert('postmeta', array(
						'post' => $menu_item_id,
						'_key' => $key,
						'value' => $value
					));
				}
				
				// Insert a new term relationship into the database
				$rs_query->insert('term_relationships', array('term' => $menu_id, 'post' => $menu_item_id));
				
				// Update the menu's count (nav items)
				$rs_query->update('terms', array('count' => ($count + 1)), array('id' => $menu_id));
			}
			
			// Redirect to the appropriate "Edit Menu" page
			redirect(ADMIN_URI.'?id='.$menu_id.'&action=edit');
		} else {
			// Update the menu in the database
			$rs_query->update('terms', array(
				'name' => $data['name'],
				'slug' => $data['slug']
			), array('id' => $id));
			
			// Check whether any menu items have been selected
			if(!empty($data['menu_items'])) {
				// Assign the menu item data to a variable
				$menu_items = $data['menu_items'];
				
				for($i = 0; $i < count($menu_items); $i++) {
					// Split the menu item data into separate variables
					list($item_type, $item_id) = explode('-', $menu_items[$i]);
					
					// Fetch the number of menu items associated with the menu
					$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $id));
					
					// Create a new menu item
					$itemmeta = $this->createMenuItem($item_type, $item_id, $count);
					
					// Retrieve the first element from the itemmeta array
					$menu_item_id = array_shift($itemmeta);
					
					// Insert the menu item's metadata into the database
					foreach($itemmeta as $key => $value) {
						$rs_query->insert('postmeta', array(
							'post' => $menu_item_id,
							'_key' => $key,
							'value' => $value
						));
					}
					
					// Insert a new term relationship into the database
					$rs_query->insert('term_relationships', array('term' => $id, 'post' => $menu_item_id));
				}
				
				// Fetch the number of menu items associated with the menu
				$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $id));
				
				// Update the menu's count (nav items)
				$rs_query->update('terms', array('count' => $count), array('id' => $id));
			}
			
			// Check whether a custom menu item has been added
			if(!empty($data['custom_title']) && !empty($data['custom_link'])) {
				// Insert the new menu item into the database
				$menu_item_id = $rs_query->insert('posts', array(
					'title' => $data['custom_title'],
					'date' => 'NOW()',
					'modified' => 'NOW()',
					'slug' => '',
					'type' => 'nav_menu_item'
				));
				
				// Update the menu item's slug in the database
				$rs_query->update('posts', array('slug' => 'menu-item-'.$menu_item_id), array('id' => $menu_item_id));
				
				// Fetch the number of menu items associated with the menu
				$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $id));
				
				// Create an array to hold the menu item's metadata
				$itemmeta = array('custom_link' => $data['custom_link'], 'menu_index' => $count);
				
				// Insert the menu item's metadata into the database
				foreach($itemmeta as $key => $value) {
					$rs_query->insert('postmeta', array(
						'post' => $menu_item_id,
						'_key' => $key,
						'value' => $value
					));
				}
				
				// Insert a new term relationship into the database
				$rs_query->insert('term_relationships', array('term' => $id, 'post' => $menu_item_id));
				
				// Update the menu's count (nav items)
				$rs_query->update('terms', array('count' => ($count + 1)), array('id' => $id));
			}
			
			// Update the class variables
			foreach($data as $key => $value) $this->$key = $value;
			
			// Return a status message
			return statusMessage('Menu updated! <a href="'.ADMIN_URI.'">Return to list</a>?', true);
		}
	}
	
	/**
	 * Check whether a slug exists in the database.
	 * @since 1.8.0[a]
	 *
	 * @access private
	 * @param string $slug
	 * @param int $id
	 * @return bool
	 */
	private function slugExists($slug, $id): bool {
		// Extend the Query object
		global $rs_query;
		
		if($id === 0) {
			// Fetch the number of times the slug appears in the database
			$count = $rs_query->selectRow('terms', 'COUNT(slug)', array('slug' => $slug));
		} else {
			// Fetch the number of times the slug appears in the database (minus the current category)
			$count = $rs_query->selectRow('terms', 'COUNT(slug)', array(
				'slug' => $slug,
				'id' => array('<>', $id)
			));
		}
		
		// Return true if the count is greater than zero
		return $count > 0;
	}
	
	/**
	 * Fetch a menu's menu items.
	 * @since 1.8.0[a]
	 *
	 * @access private
	 * @param int $id (optional; default: 0)
	 */
	private function getMenuItems($id = 0): void {
		// Extend the Query object
		global $rs_query;
		?>
		<ul class="item-list">
			<?php
			// Fetch the term relationships from the database
			$relationships = $rs_query->select('term_relationships', 'post', array('term' => $id));
			
			// Create an empty array to hold the menu items' metadata
			$itemmeta = array();
			
			// Create an index counter for the metadata array
			$i = 0;
			
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
			
			foreach($itemmeta as $meta) {
				// Fetch the menu item from the database
				$menu_item = $rs_query->selectRow('posts', array(
					'id',
					'title',
					'status',
					'parent'
				), array('id' => $meta['post']));
				
				// Check what type of link is being used
				if(isset($meta['post_link']))
					$type = $rs_query->selectField('posts', 'type', array('id' => $meta['post_link']));
				elseif(isset($meta['term_link']))
					$type = 'term';
				elseif(isset($meta['custom_link']))
					$type = 'custom';
				?>
				<li class="menu-item depth-<?php echo $this->getMenuItemDepth($menu_item['id']).($menu_item['status'] === 'invalid' ? ' invalid' : ''); ?>">
					<strong><?php echo $menu_item['title']; ?></strong> &mdash; <small><em><?php echo empty($type) ? $menu_item['status'] : $type; ?></em></small>
					<?php
					// Check whether the menu item's id is set
					if(isset($_GET['item_id']) && (int)$_GET['item_id'] === $menu_item['id']) {
						// Check whether the item action is set
						if(isset($_GET['item_action'])) {
							switch($_GET['item_action']) {
								case 'move_up':
									// Move the menu item up one position
									$this->moveUpMenuItem($menu_item['id'], $id);
									?>
									<meta http-equiv="refresh" content="0; url='<?php echo ADMIN_URI; ?>?id=<?php echo $id; ?>&action=edit'">
									<?php
									break;
								case 'move_down':
									// Move the menu item down one position
									$this->moveDownMenuItem($menu_item['id'], $id);
									?>
									<meta http-equiv="refresh" content="0; url='<?php echo ADMIN_URI; ?>?id=<?php echo $id; ?>&action=edit'">
									<?php
									break;
								case 'edit':
									?>
									<div class="actions"><a href="<?php echo ADMIN_URI; ?>?id=<?php echo $id; ?>&action=edit">Cancel</a></div>
									<?php
									// Display the edit menu item form if the 'edit' action link has been clicked
									$this->editMenuItem($menu_item['id']);
									break;
								case 'delete':
									// Call the deleteMenuItem function if the 'delete' action link has been clicked
									$this->deleteMenuItem($menu_item['id'], $id);
									?>
									<meta http-equiv="refresh" content="0; url='<?php echo ADMIN_URI; ?>?id=<?php echo $id; ?>&action=edit'">
									<?php
									break;
							}
						}
					} else {
						?>
						<div class="actions">
							<a href="<?php echo ADMIN_URI; ?>?id=<?php echo $id; ?>&action=edit&item_id=<?php echo $menu_item['id']; ?>&item_action=move_up">&uarr;</a> &bull; <a href="<?php echo ADMIN_URI; ?>?id=<?php echo $id; ?>&action=edit&item_id=<?php echo $menu_item['id']; ?>&item_action=move_down">&darr;</a> &bull; <a href="<?php echo ADMIN_URI; ?>?id=<?php echo $id; ?>&action=edit&item_id=<?php echo $menu_item['id']; ?>&item_action=edit">Edit</a>
						</div>
						<?php
					}
					?>
				</li>
				<?php
			}
			
			// Display a notice if no relationships are found
			if(empty($relationships)) {
				?>
				<li class="menu-item">This menu is empty!</li>
				<?php
			}
			?>
		</ul>
		<?php
	}
	
	/**
	 * Construct a sidebar of menu items.
	 * @since 1.8.0[a]
	 *
	 * @access private
	 */
	private function getMenuItemsSidebar(): void {
		// Extend the Query object and the post types and taxonomies arrays
		global $rs_query, $post_types, $taxonomies;
		
		foreach($post_types as $post_type) {
			// Skip any post type that has 'show_in_nav_menus' set to false
			if(!$post_type['show_in_nav_menus']) continue;
			?>
			<fieldset>
				<legend><?php echo $post_type['label']; ?></legend>
				<ul class="checkbox-list">
					<?php
					// Fetch all published posts from the database
					$posts = $rs_query->select('posts', array('id', 'title'), array(
						'status' => 'published',
						'type' => $post_type['name']
					), 'id', 'DESC');
					
					foreach($posts as $post) {
						?>
						<li><?php echo formTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox-input',
							'name' => 'menu_items[]',
							'value' => 'post-'.$post['id'],
							'label' => array('content' => '<span title="'.$post['title'].'">'.trimWords($post['title'], 5).'</span>')
						)); ?></li>
						<?php
					}
					?>
				</ul>
			</fieldset>
			<?php
		}
		
		foreach($taxonomies as $taxonomy) {
			// Skip any taxonomy that has 'show_in_nav_menus' set to false
			if(!$taxonomy['show_in_nav_menus']) continue;
			?>
			<fieldset>
				<legend><?php echo $taxonomy['label']; ?></legend>
				<ul class="checkbox-list">
					<?php
					// Fetch all of the taxonomy's terms from the database
					$terms = $rs_query->select('terms', array('id', 'name'), array(
						'taxonomy' => getTaxonomyId($taxonomy['name'])
					), 'id', 'DESC');
					
					foreach($terms as $term) {
						?>
						<li><?php echo formTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox-input',
							'name' => 'menu_items[]',
							'value' => 'term-'.$term['id'],
							'label' => array('content' => '<span title="'.$term['name'].'">'.trimWords($term['name'], 5).'</span>')
						)); ?></li>
						<?php
					}
					?>
				</ul>
			</fieldset>
			<?php
		}
		?>
		<fieldset>
			<legend>Custom</legend>
			<?php
			// Construct a 'custom menu item title' form tag
			echo formTag('label', array('for' => 'custom_title', 'content' => 'Title'));
			echo formTag('input', array('class' => 'text-input', 'name' => 'custom_title'));
			?>
			<div class="clear" style="height: 2px;"></div>
			<?php
			// Construct a 'custom menu item link' form tag
			echo formTag('label', array('for' => 'custom_link', 'content' => 'Link'));
			echo formTag('input', array('class' => 'text-input', 'name' => 'custom_link'));
			?>
		</fieldset>
		<?php
	}
	
	/**
	 * Move a menu item one position up.
	 * @since 1.8.3[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $menu
	 */
	private function moveUpMenuItem($id, $menu): void {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the menu item has siblings and is not the first sibling
		if($this->hasSiblings($id, $menu) && !$this->isFirstSibling($id, $menu)) {
			// Fetch the index of the current menu item from the database
			$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			// Fetch the index of the previous sibling from the database
			$previous_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $this->getPreviousSibling($id, $menu),
				'_key' => 'menu_index'
			));
			
			// Update the current menu item's index in the database
			$rs_query->update('postmeta', array('value' => $previous_index), array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			// Fetch all relationships associated with the menu from the database
			$relationships = $rs_query->select('term_relationships', 'post', array('term' => $menu));
			
			foreach($relationships as $relationship) {
				// Fetch the index of the menu item associated with the relationship from the database
				$indexes[] = $rs_query->selectRow('postmeta', array('value', 'post'), array(
					'post' => $relationship['post'],
					'_key' => 'menu_index'
				));
			}
			
			// Sort the array in ascending index order
			asort($indexes);
			
			// Set a counter
			$i = 1;
			
			foreach($indexes as $index) {
				// Skip over any indexes that come after the current index (and its children) or before the previous index
				if($index['post'] === $id || (int)$index['value'] >= ($current_index + $this->getFamilyTree($id)) || (int)$index['value'] < $previous_index) continue;
				
				// Check whether any menu items are children of the current menu item
				if($this->isDescendant($index['post'], $id)) {
					// Update each menu item's index
					$rs_query->update('postmeta', array('value' => ($previous_index + $i)), array(
						'post' => $index['post'],
						'_key' => 'menu_index'
					));
					$i++;
				} else {
					// Update each menu item's index
					$rs_query->update('postmeta', array(
						'value' => ((int)$index['value'] + $this->getFamilyTree($id))
					), array(
						'post' => $index['post'],
						'_key' => 'menu_index'
					));
				}
			}
		}
	}
	
	/**
	 * Move a menu item one position down.
	 * @since 1.8.3[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $menu
	 */
	private function moveDownMenuItem($id, $menu): void {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the menu item has siblings and is not the last sibling
		if($this->hasSiblings($id, $menu) && !$this->isLastSibling($id, $menu)) {
			// Fetch the id of the next sibling
			$next_sibling = $this->getNextSibling($id, $menu);
			
			// Fetch the index of the current menu item from the database
			$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			// Fetch the index of the next sibling from the database
			$next_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $next_sibling,
				'_key' => 'menu_index'
			));
			
			// Update the current menu item's index in the database
			$rs_query->update('postmeta', array(
				'value' => ($current_index + $this->getFamilyTree($next_sibling))
			), array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			// Fetch all relationships associated with the menu from the database
			$relationships = $rs_query->select('term_relationships', 'post', array('term' => $menu));
			
			foreach($relationships as $relationship) {
				// Fetch the index of the menu item associated with the relationship from the database
				$indexes[] = $rs_query->selectRow('postmeta', array('value', 'post'), array(
					'post' => $relationship['post'],
					'_key' => 'menu_index'
				));
			}
			
			// Sort the array in ascending index order
			asort($indexes);
			
			// Set a counter
			$i = 1;
			
			foreach($indexes as $index) {
				// Skip over any indexes that come before the current index or after the next index
				if($index['post'] === $id || (int)$index['value'] < $current_index || (int)$index['value'] >= $next_index + $this->getFamilyTree($next_sibling)) continue;
				
				// Check whether any menu items are children of the current menu item
				if($this->isDescendant($index['post'], $id)) {
					// Update each menu item's index
					$rs_query->update('postmeta', array(
						'value' => ($current_index + $this->getFamilyTree($this->getNextSibling($id, $menu)) + $i)
					), array(
						'post' => $index['post'],
						'_key' => 'menu_index'
					));
					$i++;
				} else {
					// Update each menu item's index
					$rs_query->update('postmeta', array(
						'value' => ((int)$index['value'] - $this->getFamilyTree($id))
					), array(
						'post' => $index['post'],
						'_key' => 'menu_index'
					));
				}
			}
		}
	}
	
	/**
	 * Create a menu item.
	 * @since 2.3.2[a]
	 *
	 * @access private
	 * @param string $type
	 * @param int $id
	 * @param int $index
	 * @return array
	 */
	private function createMenuItem($type, $id, $index): array {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the type is 'post' or 'term'
		if($type === 'post') {
			// Fetch the corresponding post from the database
			$post = $rs_query->selectRow('posts', array('id', 'title'), array('id' => $id));
			
			// Insert the new menu item into the database
			$menu_item_id = $rs_query->insert('posts', array(
				'title' => $post['title'],
				'date' => 'NOW()',
				'modified' => 'NOW()',
				'slug' => '',
				'type' => 'nav_menu_item'
			));
			
			// Update the menu item's slug in the database
			$rs_query->update('posts', array('slug' => 'menu-item-'.$menu_item_id), array('id' => $menu_item_id));
			
			// Return the menu item's metadata
			return array('id' => $menu_item_id, 'post_link' => $post['id'], 'menu_index' => $index);
		} elseif($type === 'term') {
			// Fetch the corresponding term from the database
			$term = $rs_query->selectRow('terms', array('id', 'name'), array('id' => $id));
			
			// Insert the new menu item into the database
			$menu_item_id = $rs_query->insert('posts', array(
				'title' => $term['name'],
				'date' => 'NOW()',
				'modified' => 'NOW()',
				'slug' => '',
				'type' => 'nav_menu_item'
			));
			
			// Update the menu item's slug in the database
			$rs_query->update('posts', array('slug' => 'menu-item-'.$menu_item_id), array('id' => $menu_item_id));
			
			// Return the menu item's metadata
			return array('id' => $menu_item_id, 'term_link' => $term['id'], 'menu_index' => $index);
		}
	}
	
	/**
	 * Edit a menu item.
	 * @since 1.8.1[a]
	 *
	 * @access private
	 * @param int $id
	 */
	private function editMenuItem($id): void {
		// Extend the Query object
		global $rs_query;
		
		// Validate the form data and return any messages
		$message = isset($_POST['item_submit']) ? $this->validateMenuItemData($_POST, $id) : '';
		
		// Fetch the menu item from the database
		$menu_item = $rs_query->selectRow('posts', '*', array('id' => $id, 'type' => 'nav_menu_item'));
		
		// Fetch the menu item's metadata from the database
		$meta = $this->getMenuItemMeta($id);
		
		// Determine the menu item's type based on its metadata
		$type = isset($meta['post_link']) ? 'post' : (isset($meta['term_link']) ? 'term' : (isset($meta['custom_link']) ? 'custom' : ''));
		?>
		<hr class="separator">
		<?php
		echo $message;
		
		// Set the page to refresh if the menu item data has been submitted
		if(isset($_POST['item_submit'])) {
			?>
			<meta http-equiv="refresh" content="0">
			<?php
		}
		?>
		<form class="data-form" action="" method="post" autocomplete="off">
			<table class="form-table">
				<?php
				// Title
				echo formRow(array('Title', true), array(
					'tag' => 'input',
					'class' => 'text-input required invalid init',
					'name' => 'title',
					'value' => $menu_item['title']
				));
				
				// Link
				if($type === 'post') {
					echo formRow('Link to', array(
						'tag' => 'select',
						'class' => 'select-input',
						'name' => 'post_link',
						'content' => $this->getMenuItemsList((int)$meta['post_link'], $type)
					));
				} elseif($type === 'term') {
					echo formRow('Link to', array(
						'tag' => 'select',
						'class' => 'select-input',
						'name' => 'term_link',
						'content' => $this->getMenuItemsList((int)$meta['term_link'], $type)
					));
				} elseif($type === 'custom') {
					echo formRow('Link to', array(
						'tag' => 'input',
						'class' => 'text-input',
						'name' => 'custom_link',
						'value' => $meta['custom_link']
					));
				}
				
				// Parent
				echo formRow('Parent', array(
					'tag' => 'select',
					'class' => 'select-input',
					'name' => 'parent',
					'content' => '<option value="0">(none)</option>'.$this->getParentList($menu_item['parent'], $menu_item['id'])
				));
				
				// Separator
				echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
				
				// Update and delete buttons
				echo formRow('', array(
					'tag' => 'input',
					'type' => 'submit',
					'class' => 'submit-input button',
					'name' => 'item_submit',
					'value' => 'Update'
				), array(
					'tag' => 'div',
					'class' => 'actions',
					'content' => formTag('a', array(
						'class' => 'button',
						'href' => ADMIN_URI.'?id='.$_GET['id'].'&action=edit&item_id='.$menu_item['id'].'&item_action=delete',
						'content' => 'Delete'
					))
				));
				?>
			</table>
		</form>
		<?php
	}
	
	/**
	 * Delete a menu item.
	 * @since 1.8.1[a]
	 *
	 * @access public
	 * @param int $id
	 * @param int $menu
	 */
	public function deleteMenuItem($id, $menu): void {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the parent of the current menu item from the database
		$parent = (int)$rs_query->selectField('posts', 'parent', array('id' => $id));
		
		// Update the parent of each of the menu item's children in the database
		$rs_query->update('posts', array('parent' => $parent), array('parent' => $id));
		
		// Fetch the number of menu items attached to the current menu from the database
		$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $menu));
		
		// Fetch the index of the current menu item from the database
		$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
			'post' => $id,
			'_key' => 'menu_index'
		));
		
		// Delete the menu item from the database
		$rs_query->delete('posts', array('id' => $id, 'type' => 'nav_menu_item'));
		
		// Delete the menu item's metadata from the database
		$rs_query->delete('postmeta', array('post' => $id));
		
		// Delete all term relationships associated with the menu item from the database
		$rs_query->delete('term_relationships', array('post' => $id));
		
		// Check whether the index is less than the count minus one (the last index)
		if($current_index < $count - 1) {
			// Fetch the shared relationships with the other menu items from the database
			$relationships = $rs_query->select('term_relationships', 'post', array('term' => $menu));
			
			foreach($relationships as $relationship) {
				// Fetch the index of the menu item from the database
				$index = (int)$rs_query->selectField('postmeta', 'value', array(
					'post' => $relationship['post'],
					'_key' => 'menu_index'
				));
				
				// Check whether the menu item's index is less than the deleted menu item
				if($index < $current_index) {
					// Skip to the next menu item
					continue;
				} else {
					// Set the new index to one less than the original index
					$rs_query->update('postmeta', array('value' => ($index - 1)), array(
						'post' => $relationship['post'],
						'_key' => 'menu_index'
					));
				}
			}
		}
		
		// Fetch the number of menu items attached to the current menu from the database
		$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $menu));
		
		// Update the menu's count (nav items)
		$rs_query->update('terms', array('count' => $count), array('id' => $menu));
	}
	
	/**
	 * Validate the menu item form data.
	 * @since 1.8.1[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id
	 * @return nullable (null|string)
	 */
	private function validateMenuItemData($data, $id): ?string {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['title']))
			return statusMessage('R');
		
		// Fetch the menu item's current parent from the database
		$parent = (int)$rs_query->selectField('posts', 'parent', array('id' => $id));
		
		// Update the menu item in the database
		$rs_query->update('posts', array(
			'title' => $data['title'],
			'modified' => 'NOW()',
			'parent' => $data['parent']
		), array('id' => $id));
		
		// Update the menu item's metadata in the database based on the menu type
		if(!empty($data['post_link'])) {
			$rs_query->update('postmeta', array('value' => $data['post_link']), array(
				'post' => $id,
				'_key' => 'post_link'
			));
		} elseif(!empty($data['term_link'])) {
			$rs_query->update('postmeta', array('value' => $data['term_link']), array(
				'post' => $id,
				'_key' => 'term_link'
			));
		} elseif(!empty($data['custom_link'])) {
			$rs_query->update('postmeta', array('value' => $data['custom_link']), array(
				'post' => $id,
				'_key' => 'custom_link'
			));
		}
		
		// Fetch the menu associated with the current menu item
		$menu = $rs_query->selectField('term_relationships', 'term', array('post' => $id));
		
		// Fetch all relationships associated with the menu from the database
		$relationships = $rs_query->select('term_relationships', 'post', array('term' => $menu));
		
		// Create an empty array to hold the indexes
		$indexes = array();
		
		foreach($relationships as $relationship) {
			// Fetch the index of the menu item associated with the relationship from the database
			$indexes[] = $rs_query->selectRow('postmeta', array('post', 'value'), array(
				'post' => $relationship['post'],
				'_key' => 'menu_index'
			));
		}
		
		// Fetch the current menu item's index from the database
		$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
			'post' => $id,
			'_key' => 'menu_index'
		));
		
		// Check whether a parent has been set
		if((int)$data['parent'] === 0 && $parent !== 0) {
			// Fetch the number of menu items associated with the menu
			$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $menu));
			
			// Update the current menu item's index in the database
			$rs_query->update('postmeta', array(
				'value' => ($count - $this->getFamilyTree($id))
			), array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			// Set a counter
			$i = 1;
			
			foreach($indexes as $index) {
				// Skip over any indexes that come before the current index
				if((int)$index['value'] <= $current_index) continue;
				
				// Check whether any menu items are children of the current menu item
				if($this->isDescendant($index['post'], $id)) {
					// Update each menu item's index
					$rs_query->update('postmeta', array(
						'value' => ($count - $this->getFamilyTree($id) + $i)
					), array(
						'post' => $index['post'],
						'_key' => 'menu_index'
					));
					$i++;
				} else {
					// Update each menu item's index
					$rs_query->update('postmeta', array(
						'value' => ((int)$index['value'] - $this->getFamilyTree($id))
					), array(
						'post' => $index['post'],
						'_key' => 'menu_index'
					));
				}
			}
		} elseif((int)$data['parent'] !== 0) {
			// Fetch the parent menu item's index
			$parent_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $data['parent'],
				'_key' => 'menu_index'
			));
			
			// Check whether the current menu item's index is higher or lower than the parent's index
			if($current_index > $parent_index) {
				// Update the current menu item's index in the database
				$rs_query->update('postmeta', array('value' => ($parent_index + 1)), array(
					'post' => $id,
					'_key' => 'menu_index'
				));
				
				// Set a counter
				$i = 1;
				
				foreach($indexes as $index) {
					// Skip over any indexes that come after the current index (and its children) or before the parent index
					if((int)$index['value'] === $current_index || (int)$index['value'] >= ($current_index + $this->getFamilyTree($id)) || (int)$index['value'] <= $parent_index) continue;
					
					// Check whether any menu items are children of the current menu item
					if($this->isDescendant($index['post'], $id)) {
						// Update each menu item's index
						$rs_query->update('postmeta', array('value' => ($parent_index + 1 + $i)), array(
							'post' => $index['post'],
							'_key' => 'menu_index'
						));
						$i++;
					} else {
						// Update each menu item's index
						$rs_query->update('postmeta', array(
							'value' => ((int)$index['value'] + $this->getFamilyTree($id))
						), array(
							'post' => $index['post'],
							'_key' => 'menu_index'
						));
					}
				}
			} elseif($current_index < $parent_index) {
				// Determine the new index of the current menu item
				$new_index = $parent_index - $this->getFamilyTree($id) + $this->getFamilyTree((int)$data['parent']) - $this->getFamilyTree($id);
				
				// Update the current menu item's index in the database
				$rs_query->update('postmeta', array('value' => $new_index), array(
					'post' => $id,
					'_key' => 'menu_index'
				));
				
				// Set a counter
				$i = 1;
				
				foreach($indexes as $index) {
					// Skip over any indexes that come before the current index or after the parent index
					if((int)$index['value'] <= $current_index || (int)$index['value'] >= $parent_index + $this->getFamilyTree((int)$data['parent']) - $this->getFamilyTree($id)) continue;
					
					// Check whether any menu items are children of the current menu item
					if($this->isDescendant($index['post'], $id)) {
						// Update each menu item's index
						$rs_query->update('postmeta', array('value' => ($new_index + $i)), array(
							'post' => $index['post'],
							'_key' => 'menu_index'
						));
						$i++;
					} else {
						// Update each menu item's index
						$rs_query->update('postmeta', array(
							'value' => ((int)$index['value'] - $this->getFamilyTree($id))
						), array(
							'post' => $index['post'],
							'_key' => 'menu_index'
						));
					}
				}
			}
		}
		
		// Return a null value
		return null;
	}
	
	/**
	 * Check whether a menu item is the first of its siblings.
	 * @since 1.8.12[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $menu
	 * @return bool
	 */
	private function isFirstSibling($id, $menu): bool {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the current menu item's index from the database
		$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
			'post' => $id,
			'_key' => 'menu_index'
		));
		
		// Fetch all of the menu item's siblings
		$siblings = $this->getSiblings($id, $menu);
		
		foreach($siblings as $sibling) {
			// Fetch the sibling's index from the database
			$index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $sibling,
				'_key' => 'menu_index'
			));
			
			// Check whether the sibling's index is lower than the current index and return if it is
			if($index < $current_index) return false;
		}
		
		// Return true if the current index is the first among the siblings
		return true;
	}
	
	/**
	 * Check whether a menu item is the last of its siblings.
	 * @since 1.8.12[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $menu
	 * @return bool
	 */
	private function isLastSibling($id, $menu): bool {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the current menu item's index from the database
		$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
			'post' => $id,
			'_key' => 'menu_index'
		));
		
		// Fetch all of the menu item's siblings
		$siblings = $this->getSiblings($id, $menu);
		
		foreach($siblings as $sibling) {
			// Fetch the sibling's index from the database
			$index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $sibling,
				'_key' => 'menu_index'
			));
			
			// Check whether the sibling's index is higher than the current index and return if it is
			if($index > $current_index) return false;
		}
		
		// Return true if the current index is the last among the siblings
		return true;
	}
	
	/**
	 * Check whether a menu item is the previous sibling of another menu item.
	 * @since 1.8.12[a]
	 *
	 * @access private
	 * @param int $previous
	 * @param int $id
	 * @param int $menu
	 * @return bool
	 */
	private function isPreviousSibling($previous, $id, $menu): bool {
		// Extend the Query object
		global $rs_query;
		
		// Fetch all of the menu item's siblings
		$siblings = $this->getSiblings($id, $menu);
		
		// Check whether the previous menu item is a sibling of the current menu item
		if(in_array($previous, $siblings, true)) {
			// Fetch the previous sibling's index from the database
			$previous_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $previous,
				'_key' => 'menu_index'
			));
			
			// Fetch the current menu item's index from the database
			$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			// Check whether the previous index is less than the current index
			if($previous_index < $current_index) {
				foreach($siblings as $sibling) {
					// Fetch the sibling's index from the database
					$index = (int)$rs_query->selectField('postmeta', 'value', array(
						'post' => $sibling,
						'_key' => 'menu_index'
					));
					
					// Check whether the sibling's index falls in between the previous index and the current index
					if($index > $previous_index && $index < $current_index) return false;
				}
				
				// Return true if the current index is the next sibling
				return true;
			}
		}
		
		// Return false otherwise
		return false;
	}
	
	/**
	 * Check whether a menu item is the next sibling of another menu item.
	 * @since 1.8.12[a]
	 *
	 * @access private
	 * @param int $next
	 * @param int $id
	 * @param int $menu
	 * @return bool
	 */
	private function isNextSibling($next, $id, $menu): bool {
		// Extend the Query object
		global $rs_query;
		
		// Fetch all of the menu item's siblings
		$siblings = $this->getSiblings($id, $menu);
		
		// Check whether the next menu item is a sibling of the current menu item
		if(in_array($next, $siblings, true)) {
			// Fetch the next sibling's index from the database
			$next_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $next,
				'_key' => 'menu_index'
			));
			
			// Fetch the current menu item's index from the database
			$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			// Check whether the next index is greater than the current index
			if($next_index > $current_index) {
				foreach($siblings as $sibling) {
					// Fetch the sibling's index from the database
					$index = (int)$rs_query->selectField('postmeta', 'value', array(
						'post' => $sibling,
						'_key' => 'menu_index'
					));
					
					// Check whether the sibling's index falls in between the current index and the next index
					if($index > $current_index && $index < $next_index) return false;
				}
				
				// Return true if the current index is the next sibling
				return true;
			}
		}
		
		// Return false otherwise
		return false;
	}
	
	/**
	 * Check whether a menu item is a descendant of another menu item.
	 * @since 1.8.6[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $ancestor
	 * @return bool
	 */
	private function isDescendant($id, $ancestor): bool {
		// Extend the Query object
		global $rs_query;
		
		do {
			// Fetch the parent menu item from the database
			$parent = $rs_query->selectField('posts', 'parent', array('id' => $id));
			
			// Set the new id
			$id = (int)$parent;
			
			// Return true if the menu item's ancestor is found
			if($id === $ancestor) return true;
		} while($id !== 0);
		
		// Return false if no ancestor is found
		return false;
	}
	
	/**
	 * Check whether a menu item has siblings.
	 * @since 1.8.12[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $menu
	 * @return bool
	 */
	private function hasSiblings($id, $menu): bool {
		return count($this->getSiblings($id, $menu)) > 0;
	}
	
	/**
	 * Fetch a list of menu items related to a menu.
	 * @since 2.3.2[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $exclude
	 * @return array
	 */
	private function getMenuRelationships($id, $exclude): array {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the term relationships from the database
		$relationships = $rs_query->select('term_relationships', 'post', array('term' => $id));
		
		// Create an empty array to hold the menu items' metadata
		$itemmeta = array();
		
		// Create an index counter for the metadata array
		$i = 0;
		
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
		
		foreach($itemmeta as $meta) {
			// Check whether the menu item should be excluded
			if($meta['post'] === $exclude) continue;
			
			// Assign each menu item's id to an array
			$items[] = $meta['post'];
		}
		
		// Return the menu item ids
		return $items ?? array();
	}
	
	/**
	 * Construct a list of menu items.
	 * @since 1.8.1[a]
	 *
	 * @access private
	 * @param int $id
	 * @param string $type
	 * @return string
	 */
	private function getMenuItemsList($id, $type): string {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		if($type === 'post') {
			// Fetch the post's type
			$post_type = $rs_query->selectField('posts', 'type', array('id' => $id));
			
			// Fetch all posts of the same type from the database
			$posts = $rs_query->select('posts', array('id', 'title'), array(
				'status' => 'published',
				'type' => $post_type
			));
			
			// Add each post to the list
			foreach($posts as $post)
				$list .= '<option value="'.$post['id'].'"'.($post['id'] === $id ? ' selected' : '').'>'.$post['title'].'</option>';
		} elseif($type === 'term') {
			// Fetch the term's taxonomy
			$taxonomy = $rs_query->selectField('terms', 'taxonomy', array('id' => $id));
			
			// Fetch all terms of the same taxonomy from the database
			$terms = $rs_query->select('terms', array('id', 'name'), array('taxonomy' => $taxonomy));
			
			// Add each term to the list
			foreach($terms as $term)
				$list .= '<option value="'.$term['id'].'"'.($term['id'] === $id ? ' selected' : '').'>'.$term['name'].'</option>';
		}
		
		// Return the list
		return $list;
	}
	
	/**
	 * Determine a menu item's nested depth.
	 * @since 1.8.6[a]
	 *
	 * @access private
	 * @param int $id
	 * @return int
	 */
	private function getMenuItemDepth($id): int {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty variable to hold the nested depth
		$depth = -1;
		
		do {
			// Fetch the parent menu item from the database
			$parent = $rs_query->selectField('posts', 'parent', array('id' => $id));
			
			// Set the new id
			$id = (int)$parent;
			
			// Increment the count variable
			$depth++;
		} while($id !== 0);
		
		// Return the menu item's nested depth
		return $depth;
	}
	
	/**
	 * Fetch a menu item's metadata.
	 * @since 1.8.1[a]
	 *
	 * @access private
	 * @param int $id
	 * @return array
	 */
	private function getMenuItemMeta($id): array {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the menu item's metadata from the database
		$itemmeta = $rs_query->select('postmeta', array('_key', 'value'), array('post' => $id));
		
		// Create an empty array to hold the metadata
		$meta = array();
		
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
	 * @since 1.8.12[a]
	 *
	 * @access private
	 * @param int $id
	 * @return int
	 */
	private function getParent($id): int {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the parent menu item from the database and return it
		return (int)$rs_query->selectField('posts', 'parent', array('id' => $id));
	}
	
	/**
	 * Construct a list of parents.
	 * @since 1.8.6[a]
	 *
	 * @access private
	 * @param int $parent
	 * @param int $id
	 * @return string
	 */
	private function getParentList($parent, $id): string {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Create an empty array to hold the menu items
		$menu_items = array();
		
		// Create an index counter for the menu items array
		$i = 0;
		
		// Fetch the menu that the menu item id is associated with from the database
		$menu = $rs_query->selectField('term_relationships', 'term', array('post' => $id));
		
		// Fetch all term relationships associated with the menu from the database
		$relationships = $rs_query->select('term_relationships', 'post', array('term' => $menu));
		
		foreach($relationships as $relationship) {
			// Fetch all menu items associated with the menu from the database
			$menu_items[] = $rs_query->selectRow('posts', array('title', 'id'), array(
				'id' => $relationship['post'],
				'type' => 'nav_menu_item'
			));
			
			// Push the menu item's index onto the array
			$menu_items[$i]['menu_index'] = $rs_query->selectField('postmeta', 'value', array(
				'post' => $relationship['post'],
				'_key' => 'menu_index'
			));
			
			// Reverse the array (to place the index first)
			$menu_items[$i] = array_reverse($menu_items[$i]);
			
			// Increment the menu item counter
			$i++;
		}
		
		// Sort the array in ascending index order
		asort($menu_items);
		
		foreach($menu_items as $menu_item) {
			// Skip the current menu item
			if($menu_item['id'] === $id) continue;
			
			// Skip all descendant menu items
			if($this->isDescendant($menu_item['id'], $id)) continue;
			
			// Construct the list
			$list .= '<option value="'.$menu_item['id'].'"'.($menu_item['id'] === $parent ? ' selected' : '').'>'.$menu_item['title'].'</option>';
		}
		
		// Return the list
		return $list;
	}
	
	/**
	 * Fetch all siblings of a menu item.
	 * @since 2.3.3[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $menu
	 * @return array
	 */
	private function getSiblings($id, $menu): array {
		// Extend the Query object
		global $rs_query;
		
		// Fetch all menu items related to the menu from the database
		$menu_items = $this->getMenuRelationships($menu, $id);
		
		// Fetch any posts that share the same parent from the database
		$posts = $rs_query->select('posts', 'id', array(
			'parent' => $this->getParent($id),
			'id' => array('<>', $id)
		));
		
		foreach($posts as $post) {
			// Assign the posts to an array
			$same_parent[] = $post['id'];
		}
		
		// Return all siblings of the menu item (if it has any)
		return isset($same_parent) ? array_intersect($menu_items, $same_parent) : array();
	}
	
	/**
	 * Fetch the previous sibling of a menu item.
	 * @since 1.8.12[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $menu
	 * @return int
	 */
	private function getPreviousSibling($id, $menu): int {
		// Extend the Query object
		global $rs_query;
		
		// Fetch all of the menu item's siblings
		$siblings = $this->getSiblings($id, $menu);
		
		foreach($siblings as $sibling) {
			// Check whether the sibling is the previous sibling of the current menu item and return if so
			if($this->isPreviousSibling($sibling, $id, $menu)) return $sibling;
		}
	}
	
	/**
	 * Fetch the next sibling of a menu item.
	 * @since 1.8.12[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $menu
	 * @return int
	 */
	private function getNextSibling($id, $menu): int {
		// Extend the Query object
		global $rs_query;
		
		// Fetch all of the menu item's siblings
		$siblings = $this->getSiblings($id, $menu);
		
		foreach($siblings as $sibling) {
			// Check whether the sibling is the next sibling of the current menu item and return if so
			if($this->isNextSibling($sibling, $id, $menu)) return $sibling;
		}
	}
	
	/**
	 * Fetch the "family tree" of a menu item. Returns the number of members.
	 * @since 1.8.7[a]
	 *
	 * @access private
	 * @param int $id
	 * @return int
	 */
	private function getFamilyTree($id): int {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the menu item's id from the database
		$menu_item_id = $rs_query->selectField('posts', 'id', array('id' => $id));
		
		// Check whether the menu item's id is valid
		if($menu_item_id) {
			// Fetch the descendants of the menu item
			$this->getDescendants($menu_item_id);
			
			// Increment the member count
			$this->members++;
		}
		
		// Assign the global value to a local variable
		$members = $this->members;
		
		// Reset the global variable
		$this->members = 0;
		
		// Return the family member count
		return $members;
	}
	
	/**
	 * Fetch all descendants of a menu item.
	 * @since 1.8.7[a]
	 *
	 * @access private
	 * @param int $id
	 */
	private function getDescendants($id): void {
		// Extend the Query object
		global $rs_query;
		
		// Select any existing children from the database
		$children = $rs_query->select('posts', 'id', array('parent' => $id));
		
		foreach($children as $child) {
			// Fetch the descendants of the menu item
			$this->getDescendants($child['id']);
			
			// Increment the member count
			$this->members++;
		}
	}
}