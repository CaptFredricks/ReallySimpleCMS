<?php
/**
 * Admin class used to implement the Term object.
 * @since 1.0.4[b]
 *
 * Terms are data that interact with posts, such as categories. They can also interact with custom post types.
 * Terms can be created, modified, and deleted. They are stored in the 'terms' database table.
 */
class Term {
	/**
	 * The currently queried term's id.
	 * @since 1.0.5[b]
	 *
	 * @access protected
	 * @var int
	 */
	protected $id;
	
	/**
	 * The currently queried term's name.
	 * @since 1.0.5[b]
	 *
	 * @access protected
	 * @var string
	 */
	protected $name;
	
	/**
	 * The currently queried term's slug.
	 * @since 1.0.5[b]
	 *
	 * @access protected
	 * @var string
	 */
	protected $slug;
	
	/**
	 * The currently queried term's taxonomy.
	 * @since 1.0.5[b]
	 *
	 * @access private
	 * @var int
	 */
	private $taxonomy;
	
	/**
	 * The currently queried term's parent.
	 * @since 1.0.5[b]
	 *
	 * @access private
	 * @var int
	 */
	private $parent;
	
	/**
	 * The currently queried term's taxonomy data.
	 * @since 1.0.5[b]
	 *
	 * @access private
	 * @var array
	 */
	private $taxonomy_data = array();
	
	/**
	 * Class constructor.
	 * @since 1.0.5[b]
	 *
	 * @access public
	 * @param int $id (optional; default: 0)
	 * @param array $taxonomy_data (optional; default: array())
	 */
	public function __construct($id = 0, $taxonomy_data = array()) {
		// Extend the Query object
		global $rs_query;
		
		// Create an array of columns to fetch from the database
		$cols = array_keys(get_object_vars($this));
		
		// Exclude 'taxonomy_data'
		$exclude = array('taxonomy_data');
		
		// Update the columns array
		$cols = array_diff($cols, $exclude);
		
		// Check whether the id is '0'
		if($id !== 0) {
			// Fetch the term from the database
			$term = $rs_query->selectRow('terms', $cols, array('id' => $id));
			
			// Loop through the array and set the class variables
			foreach($term as $key => $value) $this->$key = $term[$key];
		}
		
		// Set the $taxonomy_data class variable
		$this->taxonomy_data = $taxonomy_data;
	}
	
	/**
	 * Construct a list of all terms in the database.
	 * @since 1.0.5[b]
	 *
	 * @access public
	 */
	public function listTerms(): void {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1><?php echo $this->taxonomy_data['label']; ?></h1>
			<?php
			// Check whether the user has sufficient privileges to create terms of the current taxonomy and create an action link if so
			if(userHasPrivilege($session['role'], 'can_create_'.str_replace(' ', '_', $this->taxonomy_data['labels']['name_lowercase']))) {
				?>
				<a class="button" href="<?php echo $this->taxonomy_data['menu_link'].($this->taxonomy_data['name'] === 'category' ? '?' : '&'); ?>action=create">Create New</a>
				<?php
			}
			
			// Display the page's info
			adminInfo();
			?>
			<hr>
			<?php
			// Check whether any status messages have been returned and display them if so
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The '.strtolower($this->taxonomy_data['labels']['name_singular']).' was successfully deleted.', true);
			
			// Fetch the term entry count from the database (by taxonomy)
			$count = $rs_query->select('terms', 'COUNT(*)', array(
				'taxonomy' => getTaxonomyId($this->taxonomy_data['name'])
			));
			
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
				// Fetch all terms from the database (by taxonomy)
				$terms = $rs_query->select('terms', '*', array(
					'taxonomy' => getTaxonomyId($this->taxonomy_data['name'])
				), 'name', 'ASC', array($page['start'], $page['per_page']));
				
				// Loop through the terms
				foreach($terms as $term) {
					// Fetch the name of the term's taxonomy
					$tax_name = str_replace(' ', '_', $this->taxonomy_data['labels']['name_lowercase']);
					
					// Set up the action links
					$actions = array(
						// Edit
						userHasPrivilege($session['role'], 'can_edit_'.$tax_name
						) ? actionLink('edit', array(
							'caption' => 'Edit',
							'id' => $term['id']
						)) : null,
						// Delete
						userHasPrivilege($session['role'], 'can_delete_'.$tax_name
						) ? actionLink('delete', array(
							'classes' => 'modal-launch delete-item',
							'data_item' => strtolower($this->taxonomy_data['labels']['name_singular']),
							'caption' => 'Delete',
							'id' => $term['id']
						)) : null,
						// View
						'<a href="'.getPermalink($this->taxonomy_data['name'], $term['parent'], $term['slug']).'">View</a>'
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						// Name
						tableCell('<strong>'.$term['name'].'</strong><div class="actions">'.implode(' &bull; ', $actions).'</div>', 'name'),
						// Slug
						tableCell($term['slug'], 'slug'),
						// Parent
						tableCell($this->getParent($term['parent']), 'parent'),
						// Count
						tableCell($term['count'], 'count')
					);
				}
				
				// Display a notice if no terms are found
				if(empty($terms)) {
					echo tableRow(
						tableCell('There are no '.$this->taxonomy_data['labels']['name_lowercase'].' to display.', '', count($table_header_cols))
					);
				}
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($table_header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
		
		// Include the delete modal
        include_once PATH.ADMIN.INC.'/modal-delete.php';
	}
	
	/**
	 * Create a term.
	 * @since 1.0.5[b]
	 *
	 * @access public
	 */
	public function createTerm(): void {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1><?php echo $this->taxonomy_data['labels']['create_item']; ?></h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Name
					echo formRow(array('Name', true), array(
						'tag' => 'input',
						'id' => 'name-field',
						'class' => 'text-input required invalid init',
						'name' => 'name',
						'value' => ($_POST['name'] ?? '')
					));
					
					// Slug
					echo formRow(array('Slug', true), array(
						'tag' => 'input',
						'id' => 'slug-field',
						'class' => 'text-input required invalid init',
						'name' => 'slug',
						'value' => ($_POST['slug'] ?? '')
					));
					
					// Parent
					echo formRow('Parent', array(
						'tag' => 'select',
						'class' => 'select-input',
						'name' => 'parent',
						'content' => '<option value="0">(none)</option>'.$this->getParentList()
					));
					
					// Separator
					echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => 'Create Category'
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Edit a term.
	 * @since 1.0.5[b]
	 *
	 * @access public
	 */
	public function editTerm(): void {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the term's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Categories" page
			redirect('categories.php');
		} else {
			// Check whether the term's taxonomy is valid
			if(empty($this->taxonomy)) {
				// Redirect to the "List Categories" page
				redirect('categories.php');
			} elseif($this->getTaxonomy($this->taxonomy) === 'category' && $this->taxonomy_data['menu_link'] !== 'categories.php') {
				// Redirect to the appropriate "Edit Category" form
				redirect('categories.php?id='.$this->id.'&action=edit');
			} elseif($this->getTaxonomy($this->taxonomy) === 'nav_menu') {
				// Redirect to the appropriate "Edit Menu" form
				redirect('menus.php?id='.$this->id.'&action=edit');
			} else {
				// Validate the form data and return any messages
				$message = isset($_POST['submit']) ? $this->validateData($_POST, $this->id) : '';
				?>
				<div class="heading-wrap">
					<h1><?php echo $this->taxonomy_data['labels']['edit_item']; ?></h1>
					<?php echo $message; ?>
				</div>
				<div class="data-form-wrap clear">
					<form class="data-form" action="" method="post" autocomplete="off">
						<table class="form-table">
							<?php
							// Name
							echo formRow(array('Name', true), array(
								'tag' => 'input',
								'id' => 'name-field',
								'class' => 'text-input required invalid init',
								'name' => 'name',
								'value' => $this->name
							));
							
							// Slug
							echo formRow(array('Slug', true), array(
								'tag' => 'input',
								'id' => 'slug-field',
								'class' => 'text-input required invalid init',
								'name' => 'slug',
								'value' => $this->slug
							));
							
							// Parent
							echo formRow('Parent', array(
								'tag' => 'select',
								'class' => 'select-input',
								'name' => 'parent',
								'content' => '<option value="0">(none)</option>'
									.$this->getParentList($this->parent, $this->id)
							));
							
							// Separator
							echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
							
							// Submit button
							echo formRow('', array(
								'tag' => 'input',
								'type' => 'submit',
								'class' => 'submit-input button',
								'name' => 'submit',
								'value' => 'Update '.$this->taxonomy_data['labels']['name_singular']
							));
							?>
						</table>
					</form>
				</div>
				<?php
			}
		}
	}
	
	/**
	 * Delete a term.
	 * @since 1.0.5[b]
	 *
	 * @access public
	 */
	public function deleteTerm(): void {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the term's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Categories" page
			redirect('categories.php');
		} else {
			// Delete the category from the database
			$rs_query->delete('terms', array('id' => $this->id, 'taxonomy' => $this->taxonomy));
			
			// Delete the term relationship(s) from the database
			$rs_query->delete('term_relationships', array('term' => $this->id));
			
			// Redirect to the "List Terms" page with an appropriate exit status
			redirect($this->taxonomy_data['menu_link'].'?exit_status=success');
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 1.5.2[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function validateData($data, $id = 0): string {
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
			// Insert the new term into the database
			$insert_id = $rs_query->insert('terms', array(
				'name' => $data['name'],
				'slug' => $slug,
				'taxonomy' => getTaxonomyId($this->taxonomy_data['name']),
				'parent' => $data['parent']
			));
			
			// Redirect to the appropriate "Edit Term" page
			redirect(ADMIN_URI.'?id='.$insert_id.'&action=edit');
		} else {
			// Update the category in the database
			$rs_query->update('terms', array(
				'name' => $data['name'],
				'slug' => $slug,
				'parent' => $data['parent']
			), array('id' => $id));
			
			// Update the class variables
			foreach($data as $key => $value) $this->$key = $value;
			
			// Return a status message
			return statusMessage($this->taxonomy_data['labels']['name_singular'].' updated! <a href="'.$this->taxonomy_data['menu_link'].'">Return to list</a>?', true);
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
	private function slugExists($slug, $id): bool {
		// Extend the Query object
		global $rs_query;
		
		if($id === 0) {
			// Fetch the number of times the slug appears in the database and return true if it does
			return $rs_query->selectRow('terms', 'COUNT(slug)', array('slug' => $slug)) > 0;
		} else {
			// Fetch the number of times the slug appears in the database (minus the current term) and return true if it does
			return $rs_query->selectRow('terms', 'COUNT(slug)', array(
				'slug' => $slug,
				'id' => array('<>', $id)
			)) > 0;
		}
	}
	
	/**
	 * Check whether a term is a descendant of another term.
	 * @since 1.5.0[a]
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
			// Fetch the parent term from the database
			$parent = $rs_query->selectField('terms', 'parent', array('id' => $id));
			
			// Set the new id
			$id = (int)$parent;
			
			// Return true if the term's ancestor is found
			if($id === $ancestor) return true;
		} while($id !== 0);
		
		// Return false if no ancestor is found
		return false;
	}
	
	/**
	 * Fetch a term's taxonomy.
	 * @since 1.0.5[b]
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getTaxonomy($id): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the taxonomy's name from the database and return it
		return $rs_query->selectField('taxonomies', 'name', array('id' => $id));
	}
	
	/**
	 * Fetch a term's parent.
	 * @since 1.5.0[a]
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getParent($id): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the parent term from the database
		$parent = $rs_query->selectField('terms', 'name', array('id' => $id));
		
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
	private function getParentList($parent = 0, $id = 0): string {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch all terms of the specified taxonomy from the database
		$terms = $rs_query->select('terms', array('id', 'name'), array(
			'taxonomy' => getTaxonomyId($this->taxonomy_data['name'])
		));
		
		// Loop through the terms
		foreach($terms as $term) {
			// Do some extra checks if an id is provided
			if($id !== 0) {
				// Skip the current term
				if($term['id'] === $id) continue;
				
				// Skip all descendant terms
				if($this->isDescendant($term['id'], $id)) continue;
			}
			
			// Construct the list
			$list .= '<option value="'.$term['id'].'"'.($term['id'] === $parent ? ' selected' : '').'>'.$term['name'].'</option>';
		}
		
		// Return the list
		return $list;
	}
}