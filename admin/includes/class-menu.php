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
	public function listEntries() {
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
				$table_header_cols = array('Name', 'Items');
				
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
						tableCell($this->getMenuItems($menu['id']), 'menu-items')
					);
				}
				
				// Display a notice if no menus are found
				if(count($menus) === 0)
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
	public function createEntry() {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1>Create Menu</h1>
			<?php echo $message; ?>
		</div>
		<form class="data-form" action="" method="post" autocomplete="off">
			<table class="form-table">
				<?php
				echo formRow(array('Name', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>($_POST['name'] ?? '')));
				echo formRow(array('Slug', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>($_POST['slug'] ?? '')));
				echo formRow('Menu Items', $this->getMenuItemsList());
				echo formRow('Custom Menu Items', array('tag'=>'textarea', 'class'=>'textarea-input'));
				echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
				echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create Menu'));
				?>
			</table>
		</form>
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
	public function editEntry($id) {
		// Extend the Query class
		global $rs_query;
		
		// Check whether or not the menu id is valid
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
				// Validate the form data and return any messages
				$message = isset($_POST['submit']) ? $this->validateData($_POST, $id) : '';
				
				// Fetch the menu from the database
				$menu = $rs_query->selectRow('terms', '*', array('id'=>$id, 'taxonomy'=>getTaxonomyId('nav_menu')));
				?>
				<div class="heading-wrap">
					<h1>Edit Menu</h1>
					<?php echo $message; ?>
				</div>
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						echo formRow(array('Name', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>$menu['name']));
						echo formRow(array('Slug', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>$menu['slug']));
						echo formRow('Menu Items', $this->getMenuItemsList($menu['id']));
						echo formRow('Custom Menu Items', array('tag'=>'textarea', 'class'=>'textarea-input'));
						echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
						echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Menu'));
						?>
					</table>
				</form>
				<?php
			}
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 1.8.0[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return null|string (null on $id == 0; string on $id != 0)
	 */
	private function validateData($data, $id = 0) {
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
					// Fetch the corresponding post from the database
					$post = $rs_query->selectRow('posts', '*', array('id'=>$menu_items[$i]));
					
					// Insert the new menu item into the database
					$menu_item_id = $rs_query->insert('posts', array('title'=>$post['title'], 'date'=>'NOW()', 'type'=>'nav_menu_item'));
					
					// Insert the new menu item's metadata into the database
					$rs_query->insert('postmeta', array('post'=>$menu_item_id, '_key'=>'menu_index', 'value'=>$i));
					
					// Insert a new term relationship into the database
					$rs_query->insert('term_relationships', array('term'=>$menu_id, 'post'=>$menu_item_id));
				}
			}
			
			// Redirect to the 'Edit Menu' page
			redirect('menus.php?id='.$menu_id.'&action=edit');
		} else {
			// Update the menu in the database
			$rs_query->update('terms', array('name'=>$data['name'], 'slug'=>$data['slug']), array('id'=>$id));
			
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
	 * @param int $id
	 * @return string
	 */
	private function getMenuItems($id) {
		// Extend the Query class
		global $rs_query;
		
		// Create an empty array to hold the menu items
		$menu_items = array();
		
		// Fetch the term relationships from the database
		$relationships = $rs_query->select('term_relationships', 'post', array('term'=>$id));
		
		// Loop through the term relationships
		foreach($relationships as $relationship) {
			// Fetch the menu item's title from the database
			$menu_item = $rs_query->selectRow('posts', 'title', array('id'=>$relationship['post']));
			
			// Assign the menu item to the array
			$menu_items[] = $menu_item['title'];
		}
		
		// Return the menu items
		return empty($menu_items) ? '&mdash;' : implode(', ', $menu_items);
	}
	
	/**
	 * Construct a list of menu items.
	 * @since 1.8.0[a]
	 *
	 * @access private
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function getMenuItemsList($id = 0) {
		// Extend the Query class
		global $rs_query;
		
		// Create a list with an opening unordered list tag
		$list = '<ul class="checkbox-list">';
		
		// Fetch all posts from the database
		$posts = $rs_query->select('posts', '*', array('type'=>array('<>', 'widget'), 'type'=>array('<>', 'nav_menu_item')));
		
		// Loop through the posts
		foreach($posts as $post) {
			// Fetch any existing term relationship from the database
			$relationship = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$id, 'post'=>$post['id']));
			
			// Construct the list
			$list .= '<li>'.formTag('input', array('type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'menu_items[]', 'value'=>$post['id'], '*'=>($relationship ? 'checked' : ''), 'label'=>array('content'=>'<span>'.$post['title'].'</span>'))).'</li>';
		}
		
		// Close the unordered list
		$list .= '</ul>';
		
		// Return the list
		return $list;
	}
}