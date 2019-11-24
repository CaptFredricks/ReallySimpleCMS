<?php
/**
 * Admin class used to implement the Category object.
 * @since 1.5.0[a]
 *
 * Categories are used to group posts by similarity. Categories are only used on the default post type: 'post'.
 * Categories can be created, modified, and deleted. They are stored in the 'terms' database table.
 */
class Category {
	/**
	 * Construct a list of all categories in the database.
	 * @since 1.5.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listCategories() {
		// Extend the Query class
		global $rs_query;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Categories</h1>
			<a class="button" href="?action=create">Create New</a>
			<?php
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The category was successfully deleted.', true);
			
			// Fetch the category entry count from the database
			$count = $rs_query->select('terms', 'COUNT(*)', array('taxonomy'=>getTaxonomyId('category')));
			
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
				$table_header_cols = array('Name', 'Slug', 'Parent', 'Count');
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all categories from the database
				$categories = $rs_query->select('terms', '*', array('taxonomy'=>getTaxonomyId('category')), 'name', 'ASC', array($page['start'], $page['per_page']));
				
				// Loop through the categories
				foreach($categories as $category) {
					echo tableRow(
						tableCell('<strong>'.$category['name'].'</strong><div class="actions"><a href="?id='.$category['id'].'&action=edit">Edit</a> &bull; <a href="?id='.$category['id'].'&action=delete">Delete</a></div>', 'name'),
						tableCell($category['slug'], 'slug'),
						tableCell($this->getParent($category['parent']), 'parent'),
						tableCell($category['count'], 'count')
					);
				}
				
				// Display a notice if no categories are found
				if(empty($categories))
					echo tableRow(tableCell('There are no categories to display.', '', count($table_header_cols)));
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
	}
	
	/**
	 * Construct the 'Create Category' form.
	 * @since 1.5.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function createCategory() {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1>Create Category</h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					echo formRow(array('Name', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>($_POST['name'] ?? '')));
					echo formRow(array('Slug', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>($_POST['slug'] ?? '')));
					echo formRow('Parent', array('tag'=>'select', 'class'=>'select-input', 'name'=>'parent', 'content'=>'<option value="0">(none)</option>'.$this->getParentList()));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create Category'));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Construct the 'Edit Category' form.
	 * @since 1.5.1[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function editCategory($id) {
		// Extend the Query class
		global $rs_query;
		
		// Check whether the category's id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Categories' page
			redirect('categories.php');
		} else {
			// Fetch the number of times the category appears in the database
			$count = $rs_query->selectRow('terms', 'COUNT(*)', array('id'=>$id, 'taxonomy'=>getTaxonomyId('category')));
			
			// Check whether the count is zero
			if($count === 0) {
				// Redirect to the 'List Categories' page
				redirect('categories.php');
			} else {
				// Validate the form data and return any messages
				$message = isset($_POST['submit']) ? $this->validateData($_POST, $id) : '';
				
				// Fetch the category from the database
				$category = $rs_query->selectRow('terms', '*', array('id'=>$id, 'taxonomy'=>getTaxonomyId('category')));
				?>
				<div class="heading-wrap">
					<h1>Edit Category</h1>
					<?php echo $message; ?>
				</div>
				<div class="data-form-wrap clear">
					<form class="data-form" action="" method="post" autocomplete="off">
						<table class="form-table">
							<?php
							echo formRow(array('Name', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>$category['name']));
							echo formRow(array('Slug', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>$category['slug']));
							echo formRow('Parent', array('tag'=>'select', 'class'=>'select-input', 'name'=>'parent', 'content'=>'<option value="0">(none)</option>'.$this->getParentList($category['parent'], $category['id'])));
							echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
							echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Category'));
							?>
						</table>
					</form>
				</div>
				<?php
			}
		}
	}
	
	/**
	 * Delete a category from the database.
	 * @since 1.5.1[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function deleteCategory($id) {
		// Extend the Query class
		global $rs_query;
		
		// Check whether the category's id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Categories' page
			redirect('categories.php');
		} else {
			// Delete the category from the database
			$rs_query->delete('terms', array('id'=>$id, 'taxonomy'=>getTaxonomyId('category')));
			
			// Delete the term relationship(s) from the database
			$rs_query->delete('term_relationships', array('term'=>$id));
			
			// Redirect to the 'List Categories' page (with a success message)
			redirect('categories.php?exit_status=success');
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 1.5.2[a]
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
			// Insert the new category into the database
			$insert_id = $rs_query->insert('terms', array('name'=>$data['name'], 'slug'=>$data['slug'], 'taxonomy'=>getTaxonomyId('category'), 'parent'=>$data['parent']));
			
			// Redirect to the 'Edit Category' page
			redirect('categories.php?id='.$insert_id.'&action=edit');
		} else {
			// Update the category in the database
			$rs_query->update('terms', array('name'=>$data['name'], 'slug'=>$data['slug'], 'parent'=>$data['parent']), array('id'=>$id));
			
			// Return a status message
			return statusMessage('Category updated! <a href="categories.php">Return to list</a>?', true);
		}
	}
	
	/**
	 * Check whether a slug exists in the database.
	 * @since 1.5.2[a]
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
	 * Check whether a category is a descendant of another category.
	 * @since 1.5.0[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $ancestor
	 * @return bool
	 */
	private function isDescendant($id, $ancestor) {
		// Extend the Query class
		global $rs_query;
		
		do {
			// Fetch the parent category from the database
			$parent = $rs_query->selectField('terms', 'parent', array('id'=>$id));
			
			// Set the new id
			$id = (int)$parent;
			
			// Return true if the category's ancestor is found
			if($id === $ancestor) return true;
		} while($id !== 0);
		
		// Return false if no ancestor is found
		return false;
	}
	
	/**
	 * Fetch a category's parent.
	 * @since 1.5.0[a]
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getParent($id) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the parent category from the database
		$parent = $rs_query->selectField('terms', 'name', array('id'=>$id));
		
		// Return the parent's name
		return empty($parent) ? '&mdash;' : $parent;
	}
	
	/**
	 * Construct a list of parents.
	 * @since 1.5.0[a]
	 *
	 * @access private
	 * @param int $parent (optional; default: 0)
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function getParentList($parent = 0, $id = 0) {
		// Extend the Query class
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch all categories from the database
		$categories = $rs_query->select('terms', array('id', 'name'), array('taxonomy'=>getTaxonomyId('category')));
		
		// Loop through the categories
		foreach($categories as $category) {
			// Do some extra checks if an id is provided
			if($id !== 0) {
				// Skip the current category
				if($category['id'] === $id) continue;
				
				// Skip all descendant categories
				if($this->isDescendant($category['id'], $id)) continue;
			}
			
			// Construct the list
			$list .= '<option value="'.$category['id'].'"'.($category['id'] === $parent ? ' selected' : '').'>'.$category['name'].'</option>';
		}
		
		// Return the list
		return $list;
	}
}