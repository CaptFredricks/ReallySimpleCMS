<?php
/**
 * Admin class used to implement the Menu object.
 * @since 1.8.0[a]
 *
 * Menus are used for website navigation on the front end of the website.
 * Menus can be created, modified, and deleted. Menus are stored in the 'terms' table under the 'nav_menu' taxonomy. Menu items are stored in the 'posts' table as the 'nav_menu_item' post type.
 */
class Menu {
	/**
	 * Construct a list of all menus in the database.
	 * @since 1.8.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listMenus() {
		// Extend the Query class
		global $rs_query;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Menus</h1>
			<a class="button" href="?action=create">Create New</a>
			<?php
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The menu was successfully deleted.', true);
			
			// Fetch the menu entry count from the database
			$count = $rs_query->select('terms', 'COUNT(*)', array('taxonomy'=>getTaxonomyId('nav_menu')));
			
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
				$menus = $rs_query->select('terms', '*', array('taxonomy'=>getTaxonomyId('nav_menu')), 'name', 'ASC', array($page['start'], $page['per_page']));
				
				// Loop through the menus
				foreach($menus as $menu) {
					echo tableRow(
						tableCell('<strong>'.$menu['name'].'</strong><div class="actions"><a href="?id='.$menu['id'].'&action=edit">Edit</a> &bull; <a href="?id='.$menu['id'].'&action=delete">Delete</a></div>', 'name'),
						tableCell($menu['count'], 'count')
					);
				}
				
				// Display a notice if no menus are found
				if(empty($menus))
					echo tableRow(tableCell('There are no menus to display.', '', count($table_header_cols)));
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		pagerNav($page['current'], $page['count']);
	}
	
	/**
	 * Construct the 'Create Menu' form.
	 * @since 1.8.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function createMenu() {
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
					echo formTag('input', array('id'=>'name-field', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>($_POST['name'] ?? ''), 'placeholder'=>'Menu name'));
					
					// Construct a 'slug' form tag
					echo formTag('input', array('id'=>'slug-field', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>($_POST['slug'] ?? ''), 'placeholder'=>'Menu slug'));
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
							// Construct the 'menu items' lists
							$this->getMenuItemsLists();
							?>
						</div>
						<div id="submit" class="row">
							<?php
							// Construct the 'submit' button form tag
							echo formTag('input', array('type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create'));
							?>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Construct the 'Edit Menu' form.
	 * @since 1.8.0[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function editMenu($id) {
		// Extend the Query class
		global $rs_query;
		
		// Check whether or not the menu's id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Menus' page
			redirect('menus.php');
		} else {
			// Fetch the number of times the menu appears in the database
			$count = $rs_query->selectRow('terms', 'COUNT(*)', array('id'=>$id, 'taxonomy'=>getTaxonomyId('nav_menu')));
			
			// Check whether or not the count is zero
			if($count === 0) {
				// Redirect to the 'List Menus' page
				redirect('menus.php');
			} else {
				// Check whether the menu item's id is set
				if(isset($_GET['item_id'])) {
					// Fetch the menu item's id
					$item_id = (int)$_GET['item_id'];
					
					// Check whether or not the menu item's id is valid
					if(empty($item_id) || $item_id <= 0) {
						// Redirect to the 'Edit Menu' page
						redirect('menus.php?id='.$id.'&action=edit');
					} else {
						// Fetch the number of times the menu item appears in the database
						$count = $rs_query->selectRow('posts', 'COUNT(*)', array('id'=>$item_id, 'type'=>'nav_menu_item'));
						
						// Check whether or not the count is zero
						if($count === 0) {
							// Redirect to the 'Edit Menu' page
							redirect('menus.php?id='.$id.'&action=edit');
						}
					}
				}
				
				// Validate the form data and return any messages
				$message = isset($_POST['submit']) ? $this->validateMenuData($_POST, $id) : '';
				
				// Fetch the menu from the database
				$menu = $rs_query->selectRow('terms', '*', array('id'=>$id, 'taxonomy'=>getTaxonomyId('nav_menu')));
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
							echo formTag('input', array('id'=>'name-field', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>$menu['name'], 'placeholder'=>'Menu name'));
							
							// Construct a 'slug' form tag
							echo formTag('input', array('id'=>'slug-field', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>$menu['slug'], 'placeholder'=>'Menu slug'));
							?>
						</div>
						<div class="sidebar">
							<div class="block">
								<h2>Add Menu Items</h2>
								<div class="row">
									<?php
									// Construct the 'menu items' lists
									$this->getMenuItemsLists();
									?>
								</div>
								<div id="submit" class="row">
									<?php
									// Construct the 'submit' button form tag
									echo formTag('input', array('type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update'));
									?>
								</div>
							</div>
						</div>
					</form>
					<div class="item-list-wrap">
						<?php
						// Construct a list of the items on the menu
						echo $this->getMenuItems($menu['id']);
						?>
					</div>
				</div>
				<?php
			}
		}
	}
	
	/**
	 * Delete a menu from the database.
	 * @since 1.8.1[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function deleteMenu($id) {
		// Extend the Query class
		global $rs_query;
		
		// Check whether or not the menu's id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Menus' page
			redirect('menus.php');
		} else {
			// Delete the menu from the database
			$rs_query->delete('terms', array('id'=>$id, 'taxonomy'=>getTaxonomyId('nav_menu')));
			
			// Fetch all term relationships associated with the menu from the database
			$relationships = $rs_query->select('term_relationships', 'post', array('term'=>$id));
			
			// Delete all term relationships associated with the menu from the database
			$rs_query->delete('term_relationships', array('term'=>$id));
			
			// Loop through the relationships
			foreach($relationships as $relationship) {
				// Delete each menu item associated with the menu from the database
				$rs_query->delete('posts', array('id'=>$relationship['post']));
				
				// Delete each menu item's metadata from the database
				$rs_query->delete('postmeta', array('post'=>$relationship['post']));
			}
		}
		
		// Redirect to the 'List Menus' page (with a success status)
		redirect('menus.php?exit_status=success');
	}
	
	/**
	 * Validate the menu form data.
	 * @since 1.8.0[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return null|string (null on $id == 0; string on $id != 0)
	 */
	private function validateMenuData($data, $id = 0) {
		// Extend the Query class
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['name']) || empty($data['slug']))
			return statusMessage('R');
		
		// Make sure the slug is not already being used
		if($this->slugExists($data['slug'], $id))
			return statusMessage('That slug is already in use. Please choose another one.');
		
		if($id === 0) {
			// Insert the new menu into the database
			$menu_id = $rs_query->insert('terms', array('name'=>$data['name'], 'slug'=>$data['slug'], 'taxonomy'=>getTaxonomyId('nav_menu')));
			
			// Check whether any menu items have been selected
			if(!empty($data['menu_items'])) {
				// Assign the menu item data to a variable
				$menu_items = $data['menu_items'];
				
				// Loop through the menu items
				for($i = 0; $i < count($menu_items); $i++) {
					// Split the menu item into an array
					$menu_item = explode('-', $menu_items[$i]);
					
					// Check whether the menu item's type is 'post' or 'cat'
					if($menu_item[0] === 'post') {
						// Fetch the corresponding post from the database
						$post = $rs_query->selectRow('posts', array('id', 'title'), array('id'=>$menu_item[1]));
						
						// Insert the new menu item into the database
						$menu_item_id = $rs_query->insert('posts', array('title'=>$post['title'], 'date'=>'NOW()', 'type'=>'nav_menu_item'));
						
						// Update the menu item's slug in the database
						$rs_query->update('posts', array('slug'=>'menu-item-'.$menu_item_id), array('id'=>$menu_item_id));
						
						// Create an array to hold the menu item's metadata
						$itemmeta = array('post_link'=>$post['id'], 'menu_index'=>$i);
					} elseif($menu_item[0] === 'cat') {
						// Fetch the corresponding category from the database
						$category = $rs_query->selectRow('terms', array('id', 'name'), array('id'=>$menu_item[1], 'taxonomy'=>getTaxonomyId('category')));
						
						// Insert the new menu item into the database
						$menu_item_id = $rs_query->insert('posts', array('title'=>$category['name'], 'date'=>'NOW()', 'type'=>'nav_menu_item'));
						
						// Update the menu item's slug in the database
						$rs_query->update('posts', array('slug'=>'menu-item-'.$menu_item_id), array('id'=>$menu_item_id));
						
						// Create an array to hold the menu item's metadata
						$itemmeta = array('term_link'=>$category['id'], 'menu_index'=>$i);
					}
					
					// Insert the menu item's metadata into the database
					foreach($itemmeta as $key=>$value)
						$rs_query->insert('postmeta', array('post'=>$menu_item_id, '_key'=>$key, 'value'=>$value));
					
					// Insert a new term relationship into the database
					$rs_query->insert('term_relationships', array('term'=>$menu_id, 'post'=>$menu_item_id));
				}
				
				// Update the menu's count (nav items)
				$rs_query->update('terms', array('count'=>count($menu_items)), array('id'=>$menu_id));
			}
			
			// Check whether a custom menu item has been added
			if(!empty($data['custom_title']) && !empty($data['custom_link'])) {
				// Insert the new menu item into the database
				$menu_item_id = $rs_query->insert('posts', array('title'=>$data['custom_title'], 'date'=>'NOW()', 'type'=>'nav_menu_item'));
				
				// Update the menu item's slug in the database
				$rs_query->update('posts', array('slug'=>'menu-item-'.$menu_item_id), array('id'=>$menu_item_id));
				
				// Fetch the number of menu items associated with the menu
				$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term'=>$menu_id));
				
				// Create an array to hold the menu item's metadata
				$itemmeta = array('custom_link'=>$data['custom_link'], 'menu_index'=>$count);
				
				// Insert the menu item's metadata into the database
				foreach($itemmeta as $key=>$value)
					$rs_query->insert('postmeta', array('post'=>$menu_item_id, '_key'=>$key, 'value'=>$value));
				
				// Insert a new term relationship into the database
				$rs_query->insert('term_relationships', array('term'=>$menu_id, 'post'=>$menu_item_id));
				
				// Update the menu's count (nav items)
				$rs_query->update('terms', array('count'=>($count + 1)), array('id'=>$menu_id));
			}
			
			// Redirect to the 'Edit Menu' page
			redirect('menus.php?id='.$menu_id.'&action=edit');
		} else {
			// Update the menu in the database
			$rs_query->update('terms', array('name'=>$data['name'], 'slug'=>$data['slug']), array('id'=>$id));
			
			// Check whether any menu items have been selected
			if(!empty($data['menu_items'])) {
				// Assign the menu item data to a variable
				$menu_items = $data['menu_items'];
				
				// Loop through the menu items
				for($i = 0; $i < count($menu_items); $i++) {
					// Split the menu item into an array
					$menu_item = explode('-', $menu_items[$i]);
					
					// Fetch the number of menu items associated with the menu
					$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term'=>$id));
					
					// Check whether the menu item's type is 'post' or 'cat'
					if($menu_item[0] === 'post') {
						// Fetch the corresponding post from the database
						$post = $rs_query->selectRow('posts', array('id', 'title'), array('id'=>$menu_item[1]));
					
						// Insert the new menu item into the database
						$menu_item_id = $rs_query->insert('posts', array('title'=>$post['title'], 'date'=>'NOW()', 'type'=>'nav_menu_item'));
						
						// Update the menu item's slug in the database
						$rs_query->update('posts', array('slug'=>'menu-item-'.$menu_item_id), array('id'=>$menu_item_id));
						
						// Create an array to hold the menu item's metadata
						$itemmeta = array('post_link'=>$post['id'], 'menu_index'=>$count);
					} elseif($menu_item[0] === 'cat') {
						// Fetch the corresponding category from the database
						$category = $rs_query->selectRow('terms', array('id', 'name'), array('id'=>$menu_item[1], 'taxonomy'=>getTaxonomyId('category')));
						
						// Insert the new menu item into the database
						$menu_item_id = $rs_query->insert('posts', array('title'=>$category['name'], 'date'=>'NOW()', 'type'=>'nav_menu_item'));
						
						// Update the menu item's slug in the database
						$rs_query->update('posts', array('slug'=>'menu-item-'.$menu_item_id), array('id'=>$menu_item_id));
						
						// Create an array to hold the menu item's metadata
						$itemmeta = array('term_link'=>$category['id'], 'menu_index'=>$count);
					}
					
					// Insert the menu item's metadata into the database
					foreach($itemmeta as $key=>$value)
						$rs_query->insert('postmeta', array('post'=>$menu_item_id, '_key'=>$key, 'value'=>$value));
					
					// Insert a new term relationship into the database
					$rs_query->insert('term_relationships', array('term'=>$id, 'post'=>$menu_item_id));
				}
				
				// Fetch the number of menu items associated with the menu
				$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term'=>$id));
				
				// Update the menu's count (nav items)
				$rs_query->update('terms', array('count'=>$count), array('id'=>$id));
			}
			
			// Check whether a custom menu item has been added
			if(!empty($data['custom_title']) && !empty($data['custom_link'])) {
				// Insert the new menu item into the database
				$menu_item_id = $rs_query->insert('posts', array('title'=>$data['custom_title'], 'date'=>'NOW()', 'type'=>'nav_menu_item'));
				
				// Update the menu item's slug in the database
				$rs_query->update('posts', array('slug'=>'menu-item-'.$menu_item_id), array('id'=>$menu_item_id));
				
				// Fetch the number of menu items associated with the menu
				$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term'=>$id));
				
				// Create an array to hold the menu item's metadata
				$itemmeta = array('custom_link'=>$data['custom_link'], 'menu_index'=>$count);
				
				// Insert the menu item's metadata into the database
				foreach($itemmeta as $key=>$value)
					$rs_query->insert('postmeta', array('post'=>$menu_item_id, '_key'=>$key, 'value'=>$value));
				
				// Insert a new term relationship into the database
				$rs_query->insert('term_relationships', array('term'=>$id, 'post'=>$menu_item_id));
				
				// Update the menu's count (nav items)
				$rs_query->update('terms', array('count'=>($count + 1)), array('id'=>$id));
			}
			
			// Return a status message
			return statusMessage('Menu updated! <a href="menus.php">Return to list</a>?', true);
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
	private function slugExists($slug, $id) {
		// Extend the Query class
		global $rs_query;
		
		if($id === 0) {
			// Fetch the number of times the slug appears in the database
			$count = $rs_query->selectRow('terms', 'COUNT(slug)', array('slug'=>$slug));
		} else {
			// Fetch the number of times the slug appears in the database (minus the current category)
			$count = $rs_query->selectRow('terms', 'COUNT(slug)', array('slug'=>$slug, 'id'=>array('<>', $id)));
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
	 * @return null
	 */
	private function getMenuItems($id = 0) {
		// Extend the Query class
		global $rs_query;
		?>
		<ul class="item-list">
			<?php
			// Fetch the term relationships from the database
			$relationships = $rs_query->select('term_relationships', 'post', array('term'=>$id));
			
			// Create an empty array to hold the menu items' metadata
			$itemmeta = array();
			
			// Loop through the term relationships
			foreach($relationships as $relationship) {
				// Fetch the metadata associated with each menu item from the database
				$itemmeta[] = $rs_query->selectRow('postmeta', array('value', 'post'), array('post'=>$relationship['post'], '_key'=>'menu_index'));
			}
			
			// Sort the array in ascending index order
			asort($itemmeta);
			
			// Loop through the menu items' metadata
			foreach($itemmeta as $meta) {
				// Fetch the menu item from the database
				$menu_item = $rs_query->selectRow('posts', array('id', 'title'), array('id'=>$meta['post']));
				?>
				<li>
					<strong><?php echo $menu_item['title']; ?></strong>
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
									<meta http-equiv="refresh" content="0; url='?id=<?php echo $id; ?>&action=edit'">
									<?php
									break;
								case 'move_down':
									// Move the menu item down one position
									$this->moveDownMenuItem($menu_item['id'], $id);
									?>
									<meta http-equiv="refresh" content="0; url='?id=<?php echo $id; ?>&action=edit'">
									<?php
									break;
								case 'edit':
									?>
									<div class="actions"><a href="?id=<?php echo $id; ?>&action=edit">Cancel</a></div>
									<?php
									// Display the edit menu item form if the 'edit' action link has been clicked
									$this->editMenuItem($menu_item['id']);
									break;
								case 'delete':
									// Call the deleteMenuItem function if the 'delete' action link has been clicked
									$this->deleteMenuItem($menu_item['id']);
									?>
									<meta http-equiv="refresh" content="0; url='?id=<?php echo $id; ?>&action=edit'">
									<?php
									break;
							}
						}
					} else {
						?>
						<div class="actions">
							<a href="?id=<?php echo $id; ?>&action=edit&item_id=<?php echo $menu_item['id']; ?>&item_action=move_up">&uarr;</a> &bull; <a href="?id=<?php echo $id; ?>&action=edit&item_id=<?php echo $menu_item['id']; ?>&item_action=move_down">&darr;</a> &bull; <a href="?id=<?php echo $id; ?>&action=edit&item_id=<?php echo $menu_item['id']; ?>&item_action=edit">Edit</a> &bull; <a href="?id=<?php echo $id; ?>&action=edit&item_id=<?php echo $menu_item['id']; ?>&item_action=delete">Delete</a>
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
				<li>This menu is empty!</li>
				<?php
			}
			?>
		</ul>
		<?php
	}
	
	/**
	 * Construct a list of menu items.
	 * @since 1.8.0[a]
	 *
	 * @access private
	 * @return null
	 */
	private function getMenuItemsLists() {
		// Extend the Query class
		global $rs_query;
		?>
		<fieldset>
			<legend>Pages</legend>
			<ul class="checkbox-list">
				<?php
				// Fetch all published pages from the database
				$pages = $rs_query->select('posts', array('id', 'title'), array('status'=>'published', 'type'=>'page'));
				
				// Loop through the pages
				foreach($pages as $page) {
					?>
					<li><?php echo formTag('input', array('type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'menu_items[]', 'value'=>'post-'.$page['id'], 'label'=>array('content'=>'<span>'.$page['title'].'</span>'))); ?></li>
					<?php
				}
				?>
			</ul>
		</fieldset>
		<fieldset>
			<legend>Posts</legend>
			<ul class="checkbox-list">
				<?php
				// Fetch all published posts from the database
				$posts = $rs_query->select('posts', array('id', 'title'), array('status'=>'published', 'type'=>'post'));
			
				// Loop through the posts
				foreach($posts as $post) {
					?>
					<li><?php echo formTag('input', array('type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'menu_items[]', 'value'=>'post-'.$post['id'], 'label'=>array('content'=>'<span>'.$post['title'].'</span>'))); ?></li>
					<?php
				}
				?>
			</ul>
		</fieldset>
		<fieldset>
			<legend>Categories</legend>
			<ul class="checkbox-list">
				<?php
				// Fetch all categories from the database
				$categories = $rs_query->select('terms', array('id', 'name'), array('taxonomy'=>getTaxonomyId('category')));
				
				// Loop through the categories
				foreach($categories as $category) {
					?>
					<li><?php echo formTag('input', array('type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'menu_items[]', 'value'=>'cat-'.$category['id'], 'label'=>array('content'=>'<span>'.$category['name'].'</span>'))); ?></li>
					<?php
				}
				?>
			</ul>
		</fieldset>
		<fieldset>
			<legend>Custom</legend>
			<div class="row">
				<?php
				// Construct a 'custom menu item title' form tag
				echo formTag('label', array('for'=>'custom_title', 'content'=>'Title'));
				echo formTag('input', array('class'=>'text-input', 'name'=>'custom_title'));
				?>
			</div>
			<div class="row">
				<?php
				// Construct a 'custom menu item link' form tag
				echo formTag('label', array('for'=>'custom_link', 'content'=>'Link'));
				echo formTag('input', array('class'=>'text-input', 'name'=>'custom_link'));
				?>
			</div>
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
	 * @return null
	 */
	private function moveUpMenuItem($id, $menu) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the index of the menu item from the database
		$index = $rs_query->selectRow('postmeta', 'value', array('post'=>$id, '_key'=>'menu_index'));
		
		// Check whether the index is greater than zero (the first index)
		if((int)$index['value'] > 0) {
			// Fetch the shared relationships with the other menu items from the database
			$relationships = $rs_query->select('term_relationships', 'post', array('term'=>$menu));
			
			// Loop through the relationships
			foreach($relationships as $relationship) {
				// Fetch the metadata associated with the menu item from the database
				$itemmeta = $rs_query->selectRow('postmeta', array('id', 'value'), array('post'=>$relationship['post'], '_key'=>'menu_index'));
				
				// Check whether the previous menu item has been found
				if((int)$itemmeta['value'] === (int)$index['value'] - 1) {
					// Set the new index to one greater than the original index
					$rs_query->update('postmeta', array('value'=>((int)$itemmeta['value'] + 1)), array('post'=>$relationship['post'], '_key'=>'menu_index'));
					break;
				}
			}
			
			// Set the new index to one less than the original index
			$rs_query->update('postmeta', array('value'=>((int)$index['value'] - 1)), array('post'=>$id, '_key'=>'menu_index'));
		}
	}
	
	/**
	 * Move a menu item one position down.
	 * @since 1.8.3[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $menu
	 * @return null
	 */
	private function moveDownMenuItem($id, $menu) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the number of menu items attached to the current menu from the database
		$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term'=>$menu));
		
		// Fetch the index of the menu item from the database
		$index = $rs_query->selectRow('postmeta', 'value', array('post'=>$id, '_key'=>'menu_index'));
		
		// Check whether the index is greater than zero (the first index)
		if((int)$index['value'] < $count - 1) {
			// Fetch the shared relationships with the other menu items from the database
			$relationships = $rs_query->select('term_relationships', 'post', array('term'=>$menu));
			
			// Loop through the relationships
			foreach($relationships as $relationship) {
				// Fetch the metadata associated with the menu item from the database
				$itemmeta = $rs_query->selectRow('postmeta', array('id', 'value'), array('post'=>$relationship['post'], '_key'=>'menu_index'));
				
				// Check whether the next menu item has been found
				if((int)$itemmeta['value'] === (int)$index['value'] + 1) {
					// Set the new index to one less than the original index
					$rs_query->update('postmeta', array('value'=>((int)$itemmeta['value'] - 1)), array('post'=>$relationship['post'], '_key'=>'menu_index'));
					break;
				}
			}
			
			// Set the new index to one greater than the original index
			$rs_query->update('postmeta', array('value'=>((int)$index['value'] + 1)), array('post'=>$id, '_key'=>'menu_index'));
		}
	}
	
	/**
	 * Construct the 'Edit Menu Item' form.
	 * @since 1.8.1[a]
	 *
	 * @access private
	 * @param int $id
	 * @return null
	 */
	private function editMenuItem($id) {
		// Extend the Query class
		global $rs_query;
		
		// Validate the form data and return any messages
		$message = isset($_POST['item_submit']) ? $this->validateMenuItemData($_POST, $id) : '';
		
		// Fetch the menu item from the database
		$menu_item = $rs_query->selectRow('posts', '*', array('id'=>$id, 'type'=>'nav_menu_item'));
		
		// Fetch the menu item's metadata from the database
		$meta = $this->getMenuItemMeta($id);
		
		// Determine the menu item's type based on its metadata
		$type = isset($meta['post_link']) ? array('post', 'Page or Post') : (isset($meta['term_link']) ? array('term', 'Category') : (isset($meta['custom_link']) ? array('custom', 'Custom') : ''));
		?>
		<hr class="separator">
		<?php
		echo $message;
		
		// Set the page to refresh if the menu item data has been submitted
		if(isset($_POST['item_submit'])) {
			?>
			<meta http-equiv="refresh" content="3">
			<?php
		}
		?>
		<form class="data-form" action="" method="post" autocomplete="off">
			<table class="form-table">
				<?php
				echo formRow('Type', array('tag'=>'input', 'class'=>'text-input', 'name'=>'type', 'value'=>$type[1], '*'=>'disabled'));
				echo formRow(array('Title', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'title', 'value'=>$menu_item['title']));
				
				if($type[0] === 'post')
					echo formRow('Link', array('tag'=>'select', 'class'=>'select-input', 'name'=>'post_link', 'content'=>$this->getMenuItemsList((int)$meta['post_link'], $type[0])));
				elseif($type[0] === 'term')
					echo formRow('Link', array('tag'=>'select', 'class'=>'select-input', 'name'=>'term_link', 'content'=>$this->getMenuItemsList((int)$meta['term_link'], $type[0])));
				elseif($type[0] === 'custom')
					echo formRow('Link', array('tag'=>'input', 'class'=>'text-input', 'name'=>'custom_link', 'value'=>$meta['custom_link']));
				
				echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
				echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'item_submit', 'value'=>'Update'));
				?>
			</table>
		</form>
		<?php
	}
	
	/**
	 * Delete a menu item from the database.
	 * @since 1.8.1[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function deleteMenuItem($id) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the menu that the menu item is attached to
		$relationship = $rs_query->selectRow('term_relationships', 'term', array('post'=>$id));
		
		// Delete the menu item from the database
		$rs_query->delete('posts', array('id'=>$id, 'type'=>'nav_menu_item'));
		
		// Delete the menu item's metadata from the database
		$rs_query->delete('postmeta', array('post'=>$id));
		
		// Delete all term relationships associated with the menu item from the database
		$rs_query->delete('term_relationships', array('post'=>$id));
		
		// Fetch the number of menu items associated with the menu
		$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term'=>$relationship['term']));
		
		// Update the menu's count (nav items)
		$rs_query->update('terms', array('count'=>$count), array('id'=>$relationship['term']));
	}
	
	/**
	 * Validate the menu item form data.
	 * @since 1.8.1[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id
	 * @return null
	 */
	private function validateMenuItemData($data, $id) {
		// Extend the Query class
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['title']))
			return statusMessage('R');
		
		// Update the menu item in the database
		$rs_query->update('posts', array('title'=>$data['title'], 'modified'=>'NOW()'), array('id'=>$id));
		
		// Update the menu item's metadata in the database based on the menu type
		if(!empty($data['post_link']))
			$rs_query->update('postmeta', array('value'=>$data['post_link']), array('post'=>$id, '_key'=>'post_link'));
		elseif(!empty($data['term_link']))
			$rs_query->update('postmeta', array('value'=>$data['term_link']), array('post'=>$id, '_key'=>'term_link'));
		elseif(!empty($data['custom_link']))
			$rs_query->update('postmeta', array('value'=>$data['custom_link']), array('post'=>$id, '_key'=>'custom_link'));
		
		// Return a status message
		return statusMessage('Menu item updated! This page will automatically refresh for all changes to take effect.', true);
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
	private function getMenuItemsList($id, $type) {
		// Extend the Query class
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		if($type === 'post') {
			// Fetch all posts from the database (excluding widgets and menu items)
			$posts = $rs_query->select('posts', array('id', 'title'), array('type'=>array('<>', 'widget'), 'type'=>array('<>', 'nav_menu_item')));
			
			// Add each post to the list
			foreach($posts as $post)
				$list .= '<option value="'.$post['id'].'"'.($post['id'] === $id ? ' selected' : '').'>'.$post['title'].'</option>';
		} elseif($type === 'term') {
			// Fetch all categories from the database
			$categories = $rs_query->select('terms', array('id', 'name'), array('taxonomy'=>getTaxonomyId('category')));
			
			// Add each category to the list
			foreach($categories as $category)
				$list .= '<option value="'.$category['id'].'"'.($category['id'] === $id ? ' selected' : '').'>'.$category['name'].'</option>';
		}
		
		// Return the list
		return $list;
	}
	
	/**
	 * Fetch a menu item's metadata.
	 * @since 1.8.1[a]
	 *
	 * @access private
	 * @param int $id
	 * @return array
	 */
	private function getMenuItemMeta($id) {
		// Extend the Query class
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
}