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
	public function listEntries() {
		// Extend the Query class
		global $rs_query;
		
		// Set up pagination
		$page = isset($_GET['page']) ? paginate($_GET['page']) : paginate();
		?>
		<div class="heading-wrap">
			<h1>Categories</h1>
			<a class="button" href="?action=create">Create New</a>
			<?php
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('Category was successfully deleted.', true);
			
			// Get the category count
			$count = $rs_query->select('terms', 'COUNT(*)', array('taxonomy'=>getTaxonomyId('category')));
			
			// Set the page count
			$page['count'] = ceil($count / $page['per_page']);
			?>
			<div class="entry-count">
				<?php
				// Display entry count
				echo $count.' '.($count === 1 ? 'entry' : 'entries');
				?>
			</div>
		</div>
		<table class="data-table">
			<thead>
				<?php
				// Construct the table header
				echo tableHeaderRow(array('Name', 'Slug', 'Parent', 'Count'));
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all categories from the database
				$categories = $rs_query->select('terms', '*', array('taxonomy'=>getTaxonomyId('category')), 'name', 'ASC', array($page['start'], $page['per_page']));
				
				// Loop through the categories
				foreach($categories as $category) {
					// Construct the current row
					echo tableRow(
						tableCell('<strong>'.$category['name'].'</strong><div class="actions"><a href="?id='.$category['id'].'&action=edit">Edit</a> &bull; <a href="?id='.$category['id'].'&action=delete">Delete</a></div>', 'name'),
						tableCell($category['slug'], 'slug'),
						tableCell($this->getParent($category['parent']), 'parent'),
						tableCell($category['count'], 'count')
					);
				}
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
	public function createEntry() {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1>Create Category</h1>
			<?php
			// Display status messages
			echo $message;
			?>
		</div>
		<form class="data-form" action="" method="post" autocomplete="off">
			<table class="form-table">
				<?php
				// Display form rows
				echo formRow(array('Name', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>($_POST['name'] ?? '')));
				echo formRow(array('Slug', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>($_POST['slug'] ?? '')));
				echo formRow('Parent', array('tag'=>'select', 'class'=>'select-input', 'name'=>'parent', 'content'=>$this->getParentList()));
				echo formRow('', array('tag'=>'hr', 'class'=>'divider'));
				echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create Category'));
				?>
			</table>
		</form>
		<?php
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
			$category = $rs_query->selectRow('terms', 'parent', array('id'=>$id));
			
			// Set the new id
			$id = (int)$category['parent'];
			
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
		$parent = $rs_query->selectRow('terms', 'name', array('id'=>$id));
		
		// Return the parent's name (if the category has a parent)
		return !empty($parent) ? $parent['name'] : '';
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
		$categories = $rs_query->select('terms', 'id', array('taxonomy'=>getTaxonomyId('category')));
		
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
			$list .= '<option value="'.$category['id'].'"'.($category['id'] === $parent ? ' selected' : '').'>'.$this->getParent($category['id']).'</option>';
		}
		
		// Return the list
		return $list;
	}
}