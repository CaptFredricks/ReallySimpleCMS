<?php
/**
 * Admin class used to implement the Term object.
 * @since 1.0.4[b]
 *
 * Terms are data that interact with posts, such as categories. They can also interact with custom post types.
 * Terms can be created, modified, and deleted. They are stored in the 'terms' database table.
 */
class Term implements AdminInterface {
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
	private $tax_data = array();
	
	/**
	 * The currently queried term's post type data.
	 * @since 1.3.7[b]
	 *
	 * @access private
	 * @var array
	 */
	private $type_data = array();
	
	/**
	 * Class constructor.
	 * @since 1.0.5[b]
	 *
	 * @access public
	 * @param int $id (optional) -- The term's id.
	 * @param array $tax_data (optional) -- The taxonomy data.
	 */
	public function __construct(int $id = 0, array $tax_data = array()) {
		global $rs_query, $post_types;
		
		$cols = array_keys(get_object_vars($this));
		$exclude = array('tax_data', 'type_data');
		$cols = array_diff($cols, $exclude);
		
		if($id !== 0) {
			$term = $rs_query->selectRow('terms', $cols, array('id' => $id));
			
			// Set the class variable values
			foreach($term as $key => $value) $this->$key = $term[$key];
		}
		
		$this->tax_data = $tax_data;
		
		// Fetch any associated post type data
		if(!empty($this->tax_data['post_type']) &&
			array_key_exists($this->tax_data['post_type'], $post_types)) {
				$this->type_data = $post_types[$this->tax_data['post_type']];
		}
	}
	
	/**
	 * Construct a list of all terms in the database.
	 * @since 1.0.5[b]
	 *
	 * @access public
	 */
	public function listRecords(): void {
		global $rs_query;
		
		// Query vars
		$tax = $this->tax_data['name'];
		$search = $_GET['search'] ?? null;
		$paged = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1><?php echo $this->tax_data['label']; ?></h1>
			<?php
			// Check whether the user has sufficient privileges to create terms of the current taxonomy
			if(userHasPrivilege('can_create_' . str_replace(' ', '_',
				$this->tax_data['labels']['name_lowercase']))) {
					
				echo actionLink('create', array(
					'taxonomy' => ($tax === 'category' ? null : $tax),
					'classes' => 'button',
					'caption' => 'Create New'
				));
			}
			
			recordSearch(array(
				'taxonomy' => ($tax === 'category' ? null : $tax)
			));
			adminInfo();
			?>
			<hr>
			<?php
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success') {
				echo exitNotice('The ' . strtolower($this->tax_data['labels']['name_singular']) .
					' was successfully deleted.');
			}
			
			if(!is_null($search)) {
				$count = $rs_query->select('terms', 'COUNT(*)', array(
					'name' => array('LIKE', '%' . $search . '%'),
					'taxonomy' => getTaxonomyId($this->tax_data['name'])
				));
			} else {
				$count = $rs_query->select('terms', 'COUNT(*)', array(
					'taxonomy' => getTaxonomyId($this->tax_data['name'])
				));
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
				$table_header_cols = array('Name', 'Slug', 'Parent', 'Count');
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				if(!is_null($search)) {
					// Search results
					$terms = $rs_query->select('terms', '*', array(
						'name' => array('LIKE', '%' . $search . '%'),
						'taxonomy' => getTaxonomyId($this->tax_data['name'])
					), 'name', 'ASC', array($paged['start'], $paged['per_page']));
				} else {
					// All results
					$terms = $rs_query->select('terms', '*', array(
						'taxonomy' => getTaxonomyId($this->tax_data['name'])
					), 'name', 'ASC', array($paged['start'], $paged['per_page']));
				}
				
				foreach($terms as $term) {
					$tax_name = str_replace(' ', '_', $this->tax_data['labels']['name_lowercase']);
					
					$actions = array(
						// Edit
						userHasPrivilege('can_edit_' . $tax_name
						) ? actionLink('edit', array(
							'caption' => 'Edit',
							'id' => $term['id']
						)) : null,
						// Delete
						userHasPrivilege('can_delete_' . $tax_name
						) ? actionLink('delete', array(
							'classes' => 'modal-launch delete-item',
							'data_item' => strtolower($this->tax_data['labels']['name_singular']),
							'caption' => 'Delete',
							'id' => $term['id']
						)) : null,
						// View
						'<a href="' . getPermalink($this->tax_data['name'], $term['parent'], $term['slug']) .
							'">View</a>'
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						// Name
						tdCell('<strong>' . $term['name'] . '</strong><div class="actions">' .
							implode(' &bull; ', $actions) . '</div>', 'name'),
						// Slug
						tdCell($term['slug'], 'slug'),
						// Parent
						tdCell($this->getParent($term['parent']), 'parent'),
						// Count
						tdCell((empty($this->tax_data['post_type']) ||
							$this->tax_data['post_type'] !== $this->type_data['name'] ? $term['count'] :
							'<a href="' . ADMIN . '/posts.php?' . ($this->tax_data['post_type'] !== 'post' ?
							'type=' . $this->tax_data['post_type'] . '&' : '') . 'term=' . $term['slug'] .
							'">' . $term['count'] . '</a>'), 'count')
					);
				}
				
				if(empty($terms)) {
					echo tableRow(
						tdCell('There are no ' . $this->tax_data['labels']['name_lowercase'] .
							' to display.', '', count($table_header_cols))
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
		echo pagerNav($paged['current'], $paged['count']);
		
        include_once PATH . ADMIN . INC . '/modal-delete.php';
	}
	
	/**
	 * Create a new term.
	 * @since 1.0.5[b]
	 *
	 * @access public
	 */
	public function createRecord(): void {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1><?php echo $this->tax_data['labels']['create_item']; ?></h1>
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
						'content' => '<option value="0">(none)</option>' . $this->getParentList()
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
	 * Edit an existing term.
	 * @since 1.0.5[b]
	 *
	 * @access public
	 */
	public function editRecord(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect('categories.php');
		} else {
			if(empty($this->taxonomy)) {
				redirect('categories.php');
			} elseif($this->getTaxonomy($this->taxonomy) === 'category' &&
				$this->tax_data['menu_link'] !== 'categories.php') {
					
				redirect('categories.php?id=' . $this->id . '&action=edit');
			} elseif($this->getTaxonomy($this->taxonomy) === 'nav_menu') {
				redirect('menus.php?id=' . $this->id . '&action=edit');
			} else {
				// Validate the form data and return any messages
				$message = isset($_POST['submit']) ? $this->validateData($_POST, $this->id) : '';
				?>
				<div class="heading-wrap">
					<h1><?php echo $this->tax_data['labels']['edit_item']; ?></h1>
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
								'content' => '<option value="0">(none)</option>' .
									$this->getParentList($this->parent, $this->id)
							));
							
							// Separator
							echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
							
							// Submit button
							echo formRow('', array(
								'tag' => 'input',
								'type' => 'submit',
								'class' => 'submit-input button',
								'name' => 'submit',
								'value' => 'Update ' . $this->tax_data['labels']['name_singular']
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
	 * Delete an existing term.
	 * @since 1.0.5[b]
	 *
	 * @access public
	 */
	public function deleteRecord(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect('categories.php');
		} else {
			$rs_query->delete('terms', array('id' => $this->id, 'taxonomy' => $this->taxonomy));
			$rs_query->delete('term_relationships', array('term' => $this->id));
			
			redirect($this->tax_data['menu_link'] . '?exit_status=success');
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 1.5.2[a]
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @param int $id (optional) -- The term's id.
	 * @return string
	 */
	private function validateData(array $data, int $id = 0): string {
		global $rs_query;
		
		if(empty($data['name']) || empty($data['slug']))
			return exitNotice('REQ', -1);
		
		$slug = sanitize($data['slug']);
		
		if($this->slugExists($slug, $id))
			$slug = getUniqueTermSlug($slug);
		
		if($id === 0) {
			// New term
			$insert_id = $rs_query->insert('terms', array(
				'name' => $data['name'],
				'slug' => $slug,
				'taxonomy' => getTaxonomyId($this->tax_data['name']),
				'parent' => $data['parent']
			));
			
			redirect(ADMIN_URI . '?id=' . $insert_id . '&action=edit');
		} else {
			// Existing term
			$rs_query->update('terms', array(
				'name' => $data['name'],
				'slug' => $slug,
				'parent' => $data['parent']
			), array('id' => $id));
			
			// Update the class variables
			foreach($data as $key => $value) $this->$key = $value;
			
			return exitNotice($this->tax_data['labels']['name_singular'] . ' updated! <a href="' .
				$this->tax_data['menu_link'] . '">Return to list</a>?');
		}
	}
	
	/**
	 * Check whether a slug exists in the database.
	 * @since 1.5.2[a]
	 *
	 * @access private
	 * @param string $slug -- The term's slug.
	 * @param int $id -- The term's id.
	 * @return bool
	 */
	private function slugExists(string $slug, int $id): bool {
		global $rs_query;
		
		if($id === 0) {
			return $rs_query->selectRow('terms', 'COUNT(slug)', array(
				'slug' => $slug
			)) > 0;
		} else {
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
	 * @param int $id -- The term's id.
	 * @param int $ancestor -- The term's ancestor.
	 * @return bool
	 */
	private function isDescendant(int $id, int $ancestor): bool {
		global $rs_query;
		
		do {
			$parent = $rs_query->selectField('terms', 'parent', array('id' => $id));
			$id = (int)$parent;
			
			if($id === $ancestor) return true;
		} while($id !== 0);
		
		return false;
	}
	
	/**
	 * Fetch a term's taxonomy.
	 * @since 1.0.5[b]
	 *
	 * @access private
	 * @param int $id -- The term's id.
	 * @return string
	 */
	private function getTaxonomy(int $id): string {
		global $rs_query;
		
		return $rs_query->selectField('taxonomies', 'name', array('id' => $id));
	}
	
	/**
	 * Fetch a term's parent.
	 * @since 1.5.0[a]
	 *
	 * @access private
	 * @param int $id -- The term's id.
	 * @return string
	 */
	private function getParent(int $id): string {
		global $rs_query;
		
		$parent = $rs_query->selectField('terms', 'name', array('id' => $id));
		
		return empty($parent) ? '&mdash;' : $parent;
	}
	
	/**
	 * Construct a list of parents.
	 * @since 1.5.0[a]
	 *
	 * @access private
	 * @param int $parent (optional) -- The term's parent.
	 * @param int $id (optional) -- The term's id.
	 * @return string
	 */
	private function getParentList(int $parent = 0, int $id = 0): string {
		global $rs_query;
		
		$list = '';
		$terms = $rs_query->select('terms', array('id', 'name'), array(
			'taxonomy' => getTaxonomyId($this->tax_data['name'])
		));
		
		foreach($terms as $term) {
			if($id !== 0) {
				// Skip the current term
				if($term['id'] === $id) continue;
				
				// Skip all descendant terms
				if($this->isDescendant($term['id'], $id)) continue;
			}
			
			$list .= tag('option', array(
				'value' => $term['id'],
				'selected' => ($term['id'] === $parent),
				'content' => $term['name']
			));
		}
		
		return $list;
	}
}