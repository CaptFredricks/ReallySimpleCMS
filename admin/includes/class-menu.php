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
		
		$exclude = array('members');
		$cols = array_diff($cols, $exclude);
		
		if($id !== 0) {
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
		// Extend the Query object
		global $rs_query;
		
		// Query vars
		$search = $_GET['search'] ?? null;
		$paged = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Menus</h1>
			<?php
			// Check whether the user has sufficient privileges to create menus
			if(userHasPrivilege('can_create_menus'))
				echo actionLink('create', array('classes' => 'button', 'caption' => 'Create New'));
			
			recordSearch();
			adminInfo();
			?>
			<hr>
			<?php
			// Check whether any status messages have been returned and display them if so
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The menu was successfully deleted.', true);
			
			if(!is_null($search)) {
				$count = $rs_query->select('terms', 'COUNT(*)', array(
					'name' => array('LIKE', '%' . $search . '%'),
					'taxonomy' => getTaxonomyId('nav_menu')
				));
			} else {
				$count = $rs_query->select('terms', 'COUNT(*)', array('taxonomy' => getTaxonomyId('nav_menu')));
			}
			
			$paged['count'] = ceil($count / $paged['per_page']);
			?>
			<div class="entry-count">
				<?php echo $count . ' ' . ($count === 1 ? 'entry' : 'entries'); ?>
			</div>
		</div>
		<table class="data-table">
			<thead>
				<?php
				$table_header_cols = array('Name', 'Item Count');
				
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				if(!is_null($search)) {
					// Search results
					$menus = $rs_query->select('terms', '*', array(
						'name' => array('LIKE', '%' . $search . '%'),
						'taxonomy' => getTaxonomyId('nav_menu')
					), 'name', 'ASC', array($paged['start'], $paged['per_page']));
				} else {
					// All results
					$menus = $rs_query->select('terms', '*', array(
						'taxonomy' => getTaxonomyId('nav_menu')
					), 'name', 'ASC', array($paged['start'], $paged['per_page']));
				}
				
				foreach($menus as $menu) {
					$actions = array(
						// Edit
						userHasPrivilege('can_edit_menus') ? actionLink('edit', array(
							'caption' => 'Edit',
							'id' => $menu['id']
						)) : null,
						// Delete
						userHasPrivilege('can_delete_menus') ? actionLink('delete', array(
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
						tdCell('<strong>' . $menu['name'] . '</strong><div class="actions">' .
							implode(' &bull; ', $actions) . '</div>', 'name'),
						// Item count
						tdCell($menu['count'], 'count')
					);
				}
				
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
		pagerNav($paged['current'], $paged['count']);
		
        include_once PATH . ADMIN . INC . '/modal-delete.php';
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
					// Name
					echo formTag('input', array(
						'id' => 'name-field',
						'class' => 'text-input required invalid init',
						'name' => 'name',
						'value' => ($_POST['name'] ?? ''),
						'placeholder' => 'Menu name'
					));
					
					// Slug
					echo formTag('input', array(
						'id' => 'slug-field',
						'class' => 'text-input required invalid init',
						'name' => 'slug',
						'value' => ($_POST['slug'] ?? ''),
						'placeholder' => 'Menu slug'
					));
					?>
				</div>
				<div class="sidebar">
					<div class="block">
						<h2>Add Menu Items</h2>
						<div class="row">
							<?php
							// Menu items sidebar
							$this->getMenuItemsSidebar();
							?>
						</div>
						<div id="submit" class="row">
							<?php
							// Submit button
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
			<div class="item-list-wrap">
				<?php
				// Menu items list
				echo $this->getMenuItems();
				?>
			</div>
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
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			if(isset($_GET['item_id'])) {
				$item_id = (int)$_GET['item_id'];
				
				if(empty($item_id) || $item_id <= 0) {
					redirect(ADMIN_URI . '?id=' . $this->id . '&action=edit');
				} else {
					$count = $rs_query->selectRow('posts', 'COUNT(*)', array(
						'id' => $item_id,
						'type' => 'nav_menu_item'
					));
					
					if($count === 0)
						redirect(ADMIN_URI . '?id=' . $this->id . '&action=edit');
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
						// Name
						echo formTag('input', array(
							'id' => 'name-field',
							'class' => 'text-input required invalid init',
							'name' => 'name',
							'value' => $this->name,
							'placeholder' => 'Menu name'
						));
						
						// Slug
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
								// Menu items sidebar
								$this->getMenuItemsSidebar();
								?>
							</div>
							<div id="submit" class="row">
								<?php
								// Submit button
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
					// Menu items list
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
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			$rs_query->delete('terms', array('id' => $this->id, 'taxonomy' => getTaxonomyId('nav_menu')));
			$relationships = $rs_query->select('term_relationships', 'post', array('term' => $this->id));
			$rs_query->delete('term_relationships', array('term' => $this->id));
			
			// Delete all menu items associated with the menu
			foreach($relationships as $relationship) {
				$rs_query->delete('posts', array('id' => $relationship['post']));
				$rs_query->delete('postmeta', array('post' => $relationship['post']));
			}
		}
		
		redirect(ADMIN_URI . '?exit_status=success');
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
		
		$slug = sanitize($data['slug']);
		
		if($this->slugExists($slug, $id))
			$slug = getUniqueTermSlug($slug);
		
		if($id === 0) {
			// New menu
			$menu_id = $rs_query->insert('terms', array(
				'name' => $data['name'],
				'slug' => $slug,
				'taxonomy' => getTaxonomyId('nav_menu')
			));
			
			if(!empty($data['menu_items'])) {
				$menu_items = $data['menu_items'];
				
				for($i = 0; $i < count($menu_items); $i++) {
					list($item_type, $item_id) = explode('-', $menu_items[$i]);
					
					$itemmeta = $this->createMenuItem($item_type, $item_id, $i);
					$menu_item_id = array_shift($itemmeta);
					
					foreach($itemmeta as $key => $value) {
						$rs_query->insert('postmeta', array(
							'post' => $menu_item_id,
							'_key' => $key,
							'value' => $value
						));
					}
					
					$rs_query->insert('term_relationships', array('term' => $menu_id, 'post' => $menu_item_id));
				}
				
				$rs_query->update('terms', array('count' => count($menu_items)), array('id' => $menu_id));
			}
			
			if(!empty($data['custom_title']) && !empty($data['custom_link'])) {
				$menu_item_id = $rs_query->insert('posts', array(
					'title' => $data['custom_title'],
					'date' => 'NOW()',
					'modified' => 'NOW()',
					'slug' => '',
					'type' => 'nav_menu_item'
				));
				
				$rs_query->update('posts', array(
					'slug' => 'menu-item-' . $menu_item_id
				), array('id' => $menu_item_id));
				
				$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $menu_id));
				$itemmeta = array('custom_link' => $data['custom_link'], 'menu_index' => $count);
				
				foreach($itemmeta as $key => $value) {
					$rs_query->insert('postmeta', array(
						'post' => $menu_item_id,
						'_key' => $key,
						'value' => $value
					));
				}
				
				$rs_query->insert('term_relationships', array('term' => $menu_id, 'post' => $menu_item_id));
				$rs_query->update('terms', array('count' => ($count + 1)), array('id' => $menu_id));
			}
			
			redirect(ADMIN_URI . '?id=' . $menu_id . '&action=edit');
		} else {
			// Existing menu
			$rs_query->update('terms', array(
				'name' => $data['name'],
				'slug' => $data['slug']
			), array('id' => $id));
			
			if(!empty($data['menu_items'])) {
				$menu_items = $data['menu_items'];
				
				for($i = 0; $i < count($menu_items); $i++) {
					list($item_type, $item_id) = explode('-', $menu_items[$i]);
					
					$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $id));
					$itemmeta = $this->createMenuItem($item_type, $item_id, $count);
					$menu_item_id = array_shift($itemmeta);
					
					foreach($itemmeta as $key => $value) {
						$rs_query->insert('postmeta', array(
							'post' => $menu_item_id,
							'_key' => $key,
							'value' => $value
						));
					}
					
					$rs_query->insert('term_relationships', array('term' => $id, 'post' => $menu_item_id));
				}
				
				$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $id));
				$rs_query->update('terms', array('count' => $count), array('id' => $id));
			}
			
			if(!empty($data['custom_title']) && !empty($data['custom_link'])) {
				$menu_item_id = $rs_query->insert('posts', array(
					'title' => $data['custom_title'],
					'date' => 'NOW()',
					'modified' => 'NOW()',
					'slug' => '',
					'type' => 'nav_menu_item'
				));
				
				$rs_query->update('posts', array(
					'slug' => 'menu-item-' . $menu_item_id
				), array('id' => $menu_item_id));
				
				$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $id));
				$itemmeta = array('custom_link' => $data['custom_link'], 'menu_index' => $count);
				
				foreach($itemmeta as $key => $value) {
					$rs_query->insert('postmeta', array(
						'post' => $menu_item_id,
						'_key' => $key,
						'value' => $value
					));
				}
				
				$rs_query->insert('term_relationships', array('term' => $id, 'post' => $menu_item_id));
				$rs_query->update('terms', array('count' => ($count + 1)), array('id' => $id));
			}
			
			// Update the class variables
			foreach($data as $key => $value) $this->$key = $value;
			
			return statusMessage('Menu updated! <a href="' . ADMIN_URI . '">Return to list</a>?', true);
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
			return $rs_query->selectRow('terms', 'COUNT(slug)', array('slug' => $slug)) > 0;
		} else {
			return $rs_query->selectRow('terms', 'COUNT(slug)', array(
				'slug' => $slug,
				'id' => array('<>', $id)
			)) > 0;
		}
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
			$relationships = $rs_query->select('term_relationships', 'post', array('term' => $id));
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
				$menu_item = $rs_query->selectRow('posts', array(
					'id',
					'title',
					'status',
					'parent'
				), array('id' => $meta['post']));
				
				if(isset($meta['post_link']))
					$type = $rs_query->selectField('posts', 'type', array('id' => $meta['post_link']));
				elseif(isset($meta['term_link']))
					$type = 'term';
				elseif(isset($meta['custom_link']))
					$type = 'custom';
				?>
				<li class="menu-item depth-<?php echo $this->getMenuItemDepth($menu_item['id']) .
					($menu_item['status'] === 'invalid' ? ' invalid' : ''); ?>">
					
					<strong><?php echo $menu_item['title']; ?></strong> &mdash; <small><em><?php echo empty($type) ? $menu_item['status'] : $type; ?></em></small>
					<?php
					// Check whether the menu item's id is set
					if(isset($_GET['item_id']) && (int)$_GET['item_id'] === $menu_item['id']) {
						if(isset($_GET['item_action'])) {
							switch($_GET['item_action']) {
								case 'move_up':
									$this->moveUpMenuItem($menu_item['id'], $id);
									?>
									<meta http-equiv="refresh" content="0; url='<?php echo ADMIN_URI; ?>?id=<?php echo $id; ?>&action=edit'">
									<?php
									break;
								case 'move_down':
									$this->moveDownMenuItem($menu_item['id'], $id);
									?>
									<meta http-equiv="refresh" content="0; url='<?php echo ADMIN_URI; ?>?id=<?php echo $id; ?>&action=edit'">
									<?php
									break;
								case 'edit':
									?>
									<div class="actions">
										<?php
										// Cancel button
										echo actionLink('edit', array(
											'caption' => 'Cancel',
											'id' => $id
										));
										?>
									</div>
									<?php
									$this->editMenuItem($menu_item['id']);
									break;
								case 'delete':
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
							<?php
							// Move up
							echo actionLink('edit', array(
								'caption' => '&uarr;',
								'id' => $id
							), array(
								'item_id' => $menu_item['id'],
								'item_action' => 'move_up'
							)) . ' &bull; ';
							// Move down
							echo actionLink('edit', array(
								'caption' => '&darr;',
								'id' => $id
							), array(
								'item_id' => $menu_item['id'],
								'item_action' => 'move_down'
							)) . ' &bull; ';
							// Edit
							echo actionLink('edit', array(
								'caption' => 'Edit',
								'id' => $id
							), array(
								'item_id' => $menu_item['id'],
								'item_action' => 'edit'
							));
							?>
						</div>
						<?php
					}
					?>
				</li>
				<?php
			}
			
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
			if(!$post_type['show_in_nav_menus']) continue;
			?>
			<fieldset>
				<legend><?php echo $post_type['label']; ?></legend>
				<ul class="checkbox-list">
					<?php
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
							'value' => 'post-' . $post['id'],
							'label' => array(
								'class' => 'checkbox-label',
								'content' => '<span title="' . $post['title'] . '">' .
									trimWords($post['title'], 5) . '</span>'
							)
						)); ?></li>
						<?php
					}
					?>
				</ul>
			</fieldset>
			<?php
		}
		
		foreach($taxonomies as $taxonomy) {
			if(!$taxonomy['show_in_nav_menus']) continue;
			?>
			<fieldset>
				<legend><?php echo $taxonomy['label']; ?></legend>
				<ul class="checkbox-list">
					<?php
					$terms = $rs_query->select('terms', array('id', 'name'), array(
						'taxonomy' => getTaxonomyId($taxonomy['name'])
					), 'id', 'DESC');
					
					foreach($terms as $term) {
						?>
						<li><?php echo formTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox-input',
							'name' => 'menu_items[]',
							'value' => 'term-' . $term['id'],
							'label' => array(
								'class' => 'checkbox-label',
								'content' => '<span title="' . $term['name'] . '">' .
									trimWords($term['name'], 5) . '</span>')
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
			// Custom menu item title
			echo formTag('label', array('for' => 'custom_title', 'content' => 'Title'));
			echo formTag('input', array('class' => 'text-input', 'name' => 'custom_title'));
			?>
			<div class="clear" style="height: 2px;"></div>
			<?php
			// Custom menu item link
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
		
		if($this->hasSiblings($id, $menu) && !$this->isFirstSibling($id, $menu)) {
			$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			$previous_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $this->getPreviousSibling($id, $menu),
				'_key' => 'menu_index'
			));
			
			$rs_query->update('postmeta', array('value' => $previous_index), array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			$relationships = $rs_query->select('term_relationships', 'post', array('term' => $menu));
			
			foreach($relationships as $relationship) {
				$indexes[] = $rs_query->selectRow('postmeta', array('value', 'post'), array(
					'post' => $relationship['post'],
					'_key' => 'menu_index'
				));
			}
			
			// Sort the array in ascending index order
			asort($indexes);
			
			$i = 1;
			
			foreach($indexes as $index) {
				// Skip over any indexes that come after the current index (and its children) or before the previous index
				if($index['post'] === $id ||
					(int)$index['value'] >= ($current_index + $this->getFamilyTree($id)) ||
					(int)$index['value'] < $previous_index) continue;
				
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
		
		if($this->hasSiblings($id, $menu) && !$this->isLastSibling($id, $menu)) {
			$next_sibling = $this->getNextSibling($id, $menu);
			
			$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			$next_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $next_sibling,
				'_key' => 'menu_index'
			));
			
			$rs_query->update('postmeta', array(
				'value' => ($current_index + $this->getFamilyTree($next_sibling))
			), array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			$relationships = $rs_query->select('term_relationships', 'post', array('term' => $menu));
			
			foreach($relationships as $relationship) {
				$indexes[] = $rs_query->selectRow('postmeta', array('value', 'post'), array(
					'post' => $relationship['post'],
					'_key' => 'menu_index'
				));
			}
			
			// Sort the array in ascending index order
			asort($indexes);
			
			$i = 1;
			
			foreach($indexes as $index) {
				// Skip over any indexes that come before the current index or after the next index
				if($index['post'] === $id ||
					(int)$index['value'] < $current_index ||
					(int)$index['value'] >= $next_index + $this->getFamilyTree($next_sibling)) continue;
				
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
		
		if($type === 'post') {
			$post = $rs_query->selectRow('posts', array('id', 'title'), array('id' => $id));
			
			$menu_item_id = $rs_query->insert('posts', array(
				'title' => $post['title'],
				'date' => 'NOW()',
				'modified' => 'NOW()',
				'slug' => '',
				'type' => 'nav_menu_item'
			));
			
			$rs_query->update('posts', array(
				'slug' => 'menu-item-' . $menu_item_id
			), array('id' => $menu_item_id));
			
			return array('id' => $menu_item_id, 'post_link' => $post['id'], 'menu_index' => $index);
		} elseif($type === 'term') {
			$term = $rs_query->selectRow('terms', array('id', 'name'), array('id' => $id));
			
			$menu_item_id = $rs_query->insert('posts', array(
				'title' => $term['name'],
				'date' => 'NOW()',
				'modified' => 'NOW()',
				'slug' => '',
				'type' => 'nav_menu_item'
			));
			
			$rs_query->update('posts', array(
				'slug' => 'menu-item-' . $menu_item_id
			), array('id' => $menu_item_id));
			
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
		
		$menu_item = $rs_query->selectRow('posts', '*', array('id' => $id, 'type' => 'nav_menu_item'));
		$meta = $this->getMenuItemMeta($id);
		$type = isset($meta['post_link']) ? 'post' : (isset($meta['term_link']) ? 'term' :
			(isset($meta['custom_link']) ? 'custom' : ''));
		?>
		<hr class="separator">
		<?php
		echo $message;
		
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
					'content' => '<option value="0">(none)</option>' .
						$this->getParentList($menu_item['parent'], $menu_item['id'])
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
						'href' => ADMIN_URI . '?id=' . $_GET['id'] . '&action=edit&item_id=' .
							$menu_item['id'] . '&item_action=delete',
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
		
		$parent = (int)$rs_query->selectField('posts', 'parent', array('id' => $id));
		$rs_query->update('posts', array('parent' => $parent), array('parent' => $id));
		$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $menu));
		
		$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
			'post' => $id,
			'_key' => 'menu_index'
		));
		
		$rs_query->delete('posts', array('id' => $id, 'type' => 'nav_menu_item'));
		$rs_query->delete('postmeta', array('post' => $id));
		$rs_query->delete('term_relationships', array('post' => $id));
		
		// Check whether the index is less than the last index
		if($current_index < $count - 1) {
			$relationships = $rs_query->select('term_relationships', 'post', array('term' => $menu));
			
			foreach($relationships as $relationship) {
				$index = (int)$rs_query->selectField('postmeta', 'value', array(
					'post' => $relationship['post'],
					'_key' => 'menu_index'
				));
				
				if($index < $current_index) {
					continue;
				} else {
					$rs_query->update('postmeta', array('value' => ($index - 1)), array(
						'post' => $relationship['post'],
						'_key' => 'menu_index'
					));
				}
			}
		}
		
		$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $menu));
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
		
		$parent = (int)$rs_query->selectField('posts', 'parent', array('id' => $id));
		
		$rs_query->update('posts', array(
			'title' => $data['title'],
			'modified' => 'NOW()',
			'parent' => $data['parent']
		), array('id' => $id));
		
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
		
		$menu = $rs_query->selectField('term_relationships', 'term', array('post' => $id));
		$relationships = $rs_query->select('term_relationships', 'post', array('term' => $menu));
		$indexes = array();
		
		foreach($relationships as $relationship) {
			$indexes[] = $rs_query->selectRow('postmeta', array('post', 'value'), array(
				'post' => $relationship['post'],
				'_key' => 'menu_index'
			));
		}
		
		$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
			'post' => $id,
			'_key' => 'menu_index'
		));
		
		if((int)$data['parent'] === 0 && $parent !== 0) {
			$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term' => $menu));
			
			$rs_query->update('postmeta', array(
				'value' => ($count - $this->getFamilyTree($id))
			), array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			$i = 1;
			
			foreach($indexes as $index) {
				// Skip over any indexes that come before the current index
				if((int)$index['value'] <= $current_index) continue;
				
				// Check whether any menu items are children of the current menu item
				if($this->isDescendant($index['post'], $id)) {
					$rs_query->update('postmeta', array(
						'value' => ($count - $this->getFamilyTree($id) + $i)
					), array(
						'post' => $index['post'],
						'_key' => 'menu_index'
					));
					$i++;
				} else {
					$rs_query->update('postmeta', array(
						'value' => ((int)$index['value'] - $this->getFamilyTree($id))
					), array(
						'post' => $index['post'],
						'_key' => 'menu_index'
					));
				}
			}
		} elseif((int)$data['parent'] !== 0) {
			$parent_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $data['parent'],
				'_key' => 'menu_index'
			));
			
			if($current_index > $parent_index) {
				$rs_query->update('postmeta', array('value' => ($parent_index + 1)), array(
					'post' => $id,
					'_key' => 'menu_index'
				));
				
				$i = 1;
				
				foreach($indexes as $index) {
					// Skip over any indexes that come after the current index (and its children) or before the parent index
					if((int)$index['value'] === $current_index ||
						(int)$index['value'] >= ($current_index + $this->getFamilyTree($id)) ||
						(int)$index['value'] <= $parent_index) continue;
					
					// Check whether any menu items are children of the current menu item
					if($this->isDescendant($index['post'], $id)) {
						$rs_query->update('postmeta', array('value' => ($parent_index + 1 + $i)), array(
							'post' => $index['post'],
							'_key' => 'menu_index'
						));
						$i++;
					} else {
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
				$new_index = $parent_index - $this->getFamilyTree($id) +
					$this->getFamilyTree((int)$data['parent']) - $this->getFamilyTree($id);
				
				$rs_query->update('postmeta', array('value' => $new_index), array(
					'post' => $id,
					'_key' => 'menu_index'
				));
				
				$i = 1;
				
				foreach($indexes as $index) {
					// Skip over any indexes that come before the current index or after the parent index
					if((int)$index['value'] <= $current_index ||
						(int)$index['value'] >= $parent_index + $this->getFamilyTree((int)$data['parent']) -
						$this->getFamilyTree($id)) continue;
					
					// Check whether any menu items are children of the current menu item
					if($this->isDescendant($index['post'], $id)) {
						$rs_query->update('postmeta', array('value' => ($new_index + $i)), array(
							'post' => $index['post'],
							'_key' => 'menu_index'
						));
						$i++;
					} else {
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
		
		$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
			'post' => $id,
			'_key' => 'menu_index'
		));
		
		$siblings = $this->getSiblings($id, $menu);
		
		foreach($siblings as $sibling) {
			$index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $sibling,
				'_key' => 'menu_index'
			));
			
			if($index < $current_index) return false;
		}
		
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
		
		$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
			'post' => $id,
			'_key' => 'menu_index'
		));
		
		$siblings = $this->getSiblings($id, $menu);
		
		foreach($siblings as $sibling) {
			$index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $sibling,
				'_key' => 'menu_index'
			));
			
			if($index > $current_index) return false;
		}
		
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
		
		$siblings = $this->getSiblings($id, $menu);
		
		if(in_array($previous, $siblings, true)) {
			$previous_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $previous,
				'_key' => 'menu_index'
			));
			$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			if($previous_index < $current_index) {
				foreach($siblings as $sibling) {
					$index = (int)$rs_query->selectField('postmeta', 'value', array(
						'post' => $sibling,
						'_key' => 'menu_index'
					));
					
					// Check whether the sibling's index falls in between the previous index and the current index
					if($index > $previous_index && $index < $current_index) return false;
				}
				
				return true;
			}
		}
		
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
		
		$siblings = $this->getSiblings($id, $menu);
		
		if(in_array($next, $siblings, true)) {
			$next_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $next,
				'_key' => 'menu_index'
			));
			$current_index = (int)$rs_query->selectField('postmeta', 'value', array(
				'post' => $id,
				'_key' => 'menu_index'
			));
			
			if($next_index > $current_index) {
				foreach($siblings as $sibling) {
					$index = (int)$rs_query->selectField('postmeta', 'value', array(
						'post' => $sibling,
						'_key' => 'menu_index'
					));
					
					// Check whether the sibling's index falls in between the current index and the next index
					if($index > $current_index && $index < $next_index) return false;
				}
				
				return true;
			}
		}
		
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
			$parent = $rs_query->selectField('posts', 'parent', array('id' => $id));
			$id = (int)$parent;
			
			if($id === $ancestor) return true;
		} while($id !== 0);
		
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
		
		$relationships = $rs_query->select('term_relationships', 'post', array('term' => $id));
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
			if($meta['post'] === $exclude) continue;
			
			$items[] = $meta['post'];
		}
		
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
		
		$list = '';
		
		if($type === 'post') {
			$post_type = $rs_query->selectField('posts', 'type', array('id' => $id));
			$posts = $rs_query->select('posts', array('id', 'title'), array(
				'status' => 'published',
				'type' => $post_type
			));
			
			foreach($posts as $post) {
				$list .= '<option value="' . $post['id'] . '"' . ($post['id'] === $id ? ' selected' : '') . '>' .
					$post['title'] . '</option>';
			}
		} elseif($type === 'term') {
			$taxonomy = $rs_query->selectField('terms', 'taxonomy', array('id' => $id));
			$terms = $rs_query->select('terms', array('id', 'name'), array('taxonomy' => $taxonomy));
			
			foreach($terms as $term) {
				$list .= '<option value="' . $term['id'] . '"' . ($term['id'] === $id ? ' selected' : '') . '>' .
					$term['name'] . '</option>';
			}
		}
		
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
		
		$depth = -1;
		
		do {
			$parent = $rs_query->selectField('posts', 'parent', array('id' => $id));
			$id = (int)$parent;
			$depth++;
		} while($id !== 0);
		
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
		
		$itemmeta = $rs_query->select('postmeta', array('_key', 'value'), array('post' => $id));
		$meta = array();
		
		foreach($itemmeta as $metadata) {
			$values = array_values($metadata);
			
			// Assign the metadata to the meta array
			for($i = 0; $i < count($metadata); $i += 2)
				$meta[$values[$i]] = $values[$i + 1];
		}
		
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
		
		$list = '';
		$menu_items = array();
		$i = 0;
		
		$menu = $rs_query->selectField('term_relationships', 'term', array('post' => $id));
		$relationships = $rs_query->select('term_relationships', 'post', array('term' => $menu));
		
		foreach($relationships as $relationship) {
			$menu_items[] = $rs_query->selectRow('posts', array('title', 'id'), array(
				'id' => $relationship['post'],
				'type' => 'nav_menu_item'
			));
			$menu_items[$i]['menu_index'] = $rs_query->selectField('postmeta', 'value', array(
				'post' => $relationship['post'],
				'_key' => 'menu_index'
			));
			$menu_items[$i] = array_reverse($menu_items[$i]);
			$i++;
		}
		
		// Sort the array in ascending index order
		asort($menu_items);
		
		foreach($menu_items as $menu_item) {
			// Skip the current menu item
			if($menu_item['id'] === $id) continue;
			
			// Skip all descendant menu items
			if($this->isDescendant($menu_item['id'], $id)) continue;
			
			$list .= '<option value="' . $menu_item['id'] . '"' . ($menu_item['id'] === $parent ? ' selected' :
				'') . '>' . $menu_item['title'] . '</option>';
		}
		
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
		
		$menu_items = $this->getMenuRelationships($menu, $id);
		
		// Fetch any posts that share the same parent
		$posts = $rs_query->select('posts', 'id', array(
			'parent' => $this->getParent($id),
			'id' => array('<>', $id)
		));
		
		foreach($posts as $post)
			$same_parent[] = $post['id'];
		
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
		
		$siblings = $this->getSiblings($id, $menu);
		
		foreach($siblings as $sibling)
			if($this->isPreviousSibling($sibling, $id, $menu)) return $sibling;
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
		
		$siblings = $this->getSiblings($id, $menu);
		
		foreach($siblings as $sibling)
			if($this->isNextSibling($sibling, $id, $menu)) return $sibling;
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
		
		$menu_item_id = $rs_query->selectField('posts', 'id', array('id' => $id));
		
		if($menu_item_id) {
			$this->getDescendants($menu_item_id);
			$this->members++;
		}
		
		$members = $this->members;
		$this->members = 0;
		
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
		
		$children = $rs_query->select('posts', 'id', array('parent' => $id));
		
		foreach($children as $child) {
			$this->getDescendants($child['id']);
			$this->members++;
		}
	}
}