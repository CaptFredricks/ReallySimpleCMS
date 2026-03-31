<?php
/**
 * Admin class used to implement the Term object.
 * Terms are data that interact with posts, such as categories. They can also interact with custom post types.
 * Terms can be created, modified, and deleted. They are stored in the `terms` database table.
 * @since 1.0.4-beta
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_term
 * - $rs_ad_category (`Category` taxonomy only)
 *
 * ## VARIABLES [10] ##
 * - protected int $id
 * - protected string $name
 * - protected string $slug
 * - protected int $taxonomy
 * - private int $parent
 * - private int $count
 * - private array $tax_data
 * - private array $type_data
 * - protected string $action
 * - protected array $paged
 *
 * ## METHODS [16] ##
 * - public __construct(int $id, string $action, array $tax_data)
 * { LISTS, FORMS, & ACTIONS [4] }
 * - public listRecords(): void
 * - public createRecord(): void
 * - public editRecord(): void
 * - public deleteRecord(): void
 * { VALIDATION [1] }
 * - private validateSubmission(array $data): string
 * { MISCELLANEOUS [10] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private slugExists(string $slug): bool
 * - private isDescendant(int $id, int $ancestor): bool
 * - private getTaxonomy(int $id): string
 * - private getParent(int $id): string
 * - private getParentList(int $parent, int $id): string
 * - private getResults(?string $search, bool $all): array
 * - private getEntryCount(?string $search): int
 * - private getActionLinks(array $term): string
 */
namespace Admin;

class Term implements AdminInterface {
	/**
	 * The currently queried term's id.
	 * @since 1.0.5-beta
	 *
	 * @access protected
	 * @var int
	 */
	protected $id;
	
	/**
	 * The currently queried term's name.
	 * @since 1.0.5-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $name;
	
	/**
	 * The currently queried term's slug.
	 * @since 1.0.5-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $slug;
	
	/**
	 * The currently queried term's taxonomy id.
	 * @since 1.0.5-beta
	 *
	 * @access protected
	 * @var int
	 */
	protected $taxonomy;
	
	/**
	 * The currently queried term's parent.
	 * @since 1.0.5-beta
	 *
	 * @access private
	 * @var int
	 */
	private $parent;
	
	/**
	 * The currently queried term's post count.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var int
	 */
	private $count;
	
	/**
	 * The currently queried term's taxonomy data.
	 * @since 1.0.5-beta
	 *
	 * @access private
	 * @var array
	 */
	private $tax_data = array();
	
	/**
	 * The currently queried term's post type data.
	 * @since 1.3.7-beta
	 *
	 * @access private
	 * @var array
	 */
	private $type_data = array();
	
	/**
	 * The current action.
	 * @since 1.3.14-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $action;
	
	/**
	 * The pagination.
	 * @since 1.3.14-beta
	 *
	 * @access protected
	 * @var array
	 */
	protected $paged = array();
	
	/**
	 * Class constructor.
	 * @since 1.0.5-beta
	 *
	 * @access public
	 * @param int $id -- The term's id.
	 * @param string $action -- The current action.
	 * @param array $tax_data (optional) -- The taxonomy data.
	 */
	public function __construct(int $id, string $action, array $tax_data = array()) {
		global $rs_query, $rs_post_types;
		
		$this->action = $action;
		
		if($id > 0) {
			$cols = array_keys(get_object_vars($this));
			$exclude = array('tax_data', 'type_data', 'action', 'paged');
			$cols = array_diff($cols, $exclude);
			
			$term = $rs_query->selectRow(getTable('t'), $cols, array(
				'id' => $id
			));
			
			foreach($term as $key => $value) $this->$key = $term[$key];
		} else {
			$this->id = 0;
		}
		
		$this->tax_data = $tax_data;
		
		// Fetch any associated post type data
		if(!empty($this->tax_data['post_type']) &&
			array_key_exists($this->tax_data['post_type'], $rs_post_types)) {
				$this->type_data = $rs_post_types[$this->tax_data['post_type']];
		}
	}
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct a list of all terms in the database.
	 * @since 1.0.5-beta
	 *
	 * @access public
	 */
	public function listRecords(): void {
		// Query vars
		$search = $_GET['search'] ?? null;
		$this->paged = paginate((int)($_GET['paged'] ?? 1));
		
		$this->pageHeading();
		?>
		<table class="data-table">
			<thead>
				<?php
				$header_cols = array(
					'name' => 'Name',
					'slug' => 'Slug',
					'parent' => 'Parent',
					'count' => 'Count'
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$terms = $this->getResults($search);
				
				foreach($terms as $term) {
					list($t_id, $t_name, $t_slug, $t_parent, $t_count) = array(
						$term['id'],
						$term['name'],
						$term['slug'],
						$term['parent'],
						$term['count']
					);
					
					echo tableRow(
						// Name
						tdCell(domTag('strong', array(
							'content' => $t_name
						)) . domTag('div', array(
							'class' => 'actions',
							'content' => $this->getActionLinks($term)
						)), 'name'),
						// Slug
						tdCell($t_slug, 'slug'),
						// Parent
						tdCell($this->getParent($t_parent), 'parent'),
						// Count
						tdCell((empty($this->tax_data['post_type']) ||
							$this->tax_data['post_type'] !== $this->type_data['name'] ? $t_count :
							domTag('a', array(
								'href' => ADMIN . '/posts.php?' . ($this->tax_data['post_type'] !== 'post' ?
									'type=' . $this->tax_data['post_type'] . '&' : '') . 'term=' . $t_slug,
								'content' => $t_count
							))
						), 'count')
					);
				}
				
				if(empty($terms))
					echo tableRow(tdCell($this->tax_data['labels']['no_items'], '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($this->paged['current'], $this->paged['count']);
		
        includeFile(PATH . MODALS . '/modal-delete.php');
	}
	
	/**
	 * Create a new term.
	 * @since 1.0.5-beta
	 *
	 * @access public
	 */
	public function createRecord(): void {
		$this->pageHeading();
		?>
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
						'value' => ($_POST['name'] ?? ''),
						'placeholder' => $this->tax_data['labels']['title_placeholder'],
						'autocomplete' => 'off'
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
						'id' => 'parent-field',
						'class' => 'select-input',
						'name' => 'parent',
						'content' => domTag('option', array(
							'value' => 0,
							'content' => '(none)'
						)) . $this->getParentList()
					));
					
					// Separator
					echo formRow('', array(
						'tag' => 'hr',
						'class' => 'separator'
					));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => $this->tax_data['labels']['create_button']
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Edit an existing term.
	 * @since 1.0.5-beta
	 *
	 * @access public
	 */
	public function editRecord(): void {
		if(empty($this->id) || $this->id <= 0 || empty($this->taxonomy))
			redirect('categories.php');
		
		if($this->getTaxonomy($this->taxonomy) === 'category' && $this->tax_data['menu_link'] !== 'categories.php') {
			redirect('categories.php' . getQueryString(array(
				'id' => $this->id,
				'action' => 'edit'
			)));
		}
		
		if($this->getTaxonomy($this->taxonomy) === 'nav_menu') {
			redirect('menus.php' . getQueryString(array(
				'id' => $this->id,
				'action' => 'edit'
			)));
		}
		
		$this->pageHeading();
		?>
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
						'value' => $this->name,
						'placeholder' => $this->tax_data['labels']['title_placeholder'],
						'autocomplete' => 'off'
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
						'id' => 'parent-field',
						'class' => 'select-input',
						'name' => 'parent',
						'content' => domTag('option', array(
							'value' => 0,
							'content' => '(none)'
						)) . $this->getParentList($this->parent, $this->id)
					));
					
					// Separator
					echo formRow('', array(
						'tag' => 'hr',
						'class' => 'separator'
					));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => $this->tax_data['labels']['update_button']
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Delete an existing term.
	 * @since 1.0.5-beta
	 *
	 * @access public
	 */
	public function deleteRecord(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0)
			redirect('categories.php');
		
		$rs_query->delete(getTable('t'), array(
			'id' => $this->id,
			'taxonomy' => $this->taxonomy
		));
		
		$rs_query->delete(getTable('tr'), array(
			'term' => $this->id
		));
		
		redirect($this->tax_data['menu_link'] . ($this->getTaxonomy($this->taxonomy) !== 'category' ? '&' : '?') .
			'exit_status=del_success');
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the form data.
	 * @since 1.5.2-alpha
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateSubmission(array $data): string {
		global $rs_query;
		
		if(empty($data['name']) || empty($data['slug'])) {
			return exitNotice('REQ', -1);
			exit;
		}
		
		$slug = sanitize($data['slug']);
		
		if($this->slugExists($slug))
			$slug = getUniqueTermSlug($slug);
		
		switch($this->action) {
			case 'create':
				$insert_id = $rs_query->insert(getTable('t'), array(
					'name' => $data['name'],
					'slug' => $slug,
					'taxonomy' => getTaxonomyId($this->tax_data['name']),
					'parent' => $data['parent']
				));
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $insert_id,
					'action' => 'edit',
					'exit_status' => 'create_success'
				)));
				break;
			case 'edit':
				$rs_query->update(getTable('t'), array(
					'name' => $data['name'],
					'slug' => $slug,
					'parent' => $data['parent']
				), array(
					'id' => $this->id
				));
				
				foreach($data as $key => $value) $this->$key = $value;
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $this->id,
					'action' => $this->action,
					'exit_status' => 'edit_success'
				)));
				break;
		}
	}
	
	/*------------------------------------*\
		MISCELLANEOUS
	\*------------------------------------*/
	
	/**
	 * Construct the page heading.
	 * @since 1.3.14-beta
	 *
	 * @access public
	 */
	public function pageHeading(): void {
		$labels = $this->tax_data['labels'];
		
		switch($this->action) {
			case 'create':
				$title = $labels['create_item'];
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			case 'edit':
				$title = $labels['edit_item'] . ': { ' . domTag('em', array(
					'content' => $this->name
				)) . ' }';
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			default:
				$title = $labels['name'];
				$tax = $this->tax_data['name'];
				$search = $_GET['search'] ?? null;
		}
		?>
		<div class="heading-wrap">
			<?php
			// Page title
			domTagPr('h1', array(
				'content' => $title
			));
			
			if(!empty($this->action)) {
				// Status messages
				echo $message;
				
				// Exit notices
				if(isset($_GET['exit_status']))
					echo $this->exitNotice($_GET['exit_status']);
			} else {
				// Create button
				if(userHasPrivilege('can_create_' . str_replace(' ', '_', $this->tax_data['labels']['name_lowercase']))) {
					echo actionLink($this->tax_data['actions']['create'], array(
						'taxonomy' => ($tax === 'category' ? null : $tax),
						'classes' => 'button',
						'caption' => $labels['create_button']
					));
				}
				
				// Search
				recordSearch(array(
					'taxonomy' => ($tax === 'category' ? null : $tax)
				));
				
				// Info
				adminInfo();
				
				domTagPr('hr');
				
				// Exit notices
				if(isset($_GET['exit_status']))
					echo $this->exitNotice($_GET['exit_status']);
				
				// Record count
				$count = $this->getEntryCount($search);
				
				domTagPr('div', array(
					'class' => 'entry-count',
					'content' => $count . ' ' . ($count === 1 ? 'entry' : 'entries')
				));
				
				$this->paged['count'] = ceil($count / $this->paged['per_page']);
			}
			?>
		</div>
		<?php
	}
	
	/**
	 * Generate an exit notice.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @param string $exit_status -- The exit status.
	 * @param int $status_code (optional) -- The type of notice to display.
	 * @return string
	 */
	private function exitNotice(string $exit_status, int $status_code = 1): string {
		$taxonomy = $this->tax_data['labels']['name_singular'];
		
		return exitNotice(match($exit_status) {
			'create_success' => 'The ' . strtolower($taxonomy) . ' was successfully created. ' . domTag('a', array(
				'href' => $this->tax_data['menu_link'],
				'content' => 'Return to list'
			)) . '?',
			'edit_success' => $taxonomy . ' updated! ' . domTag('a', array(
				'href' => $this->tax_data['menu_link'],
				'content' => 'Return to list'
			)) . '?',
			'del_success' => 'The ' . strtolower($taxonomy) . ' was successfully deleted.',
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Check whether a slug exists in the database.
	 * @since 1.5.2-alpha
	 *
	 * @access private
	 * @param string $slug -- The term's slug.
	 * @return bool
	 */
	private function slugExists(string $slug): bool {
		global $rs_query;
		
		if($this->id === 0) {
			return $rs_query->selectRow(getTable('t'), 'COUNT(slug)', array(
				'slug' => $slug
			)) > 0;
		} else {
			return $rs_query->selectRow(getTable('t'), 'COUNT(slug)', array(
				'slug' => $slug,
				'id' => array('<>', $this->id)
			)) > 0;
		}
	}
	
	/**
	 * Check whether a term is a descendant of another term.
	 * @since 1.5.0-alpha
	 *
	 * @access private
	 * @param int $id -- The term's id.
	 * @param int $ancestor -- The term's ancestor.
	 * @return bool
	 */
	private function isDescendant(int $id, int $ancestor): bool {
		global $rs_query;
		
		do {
			$parent = $rs_query->selectField(getTable('t'), 'parent', array(
				'id' => $id
			));
			
			$id = (int)$parent;
			
			if($id === $ancestor) return true;
		} while($id !== 0);
		
		return false;
	}
	
	/**
	 * Fetch a term's taxonomy name.
	 * @since 1.0.5-beta
	 *
	 * @access private
	 * @param int $id -- The term's id.
	 * @return string
	 */
	private function getTaxonomy(int $id): string {
		global $rs_query;
		
		return $rs_query->selectField(getTable('ta'), 'name', array(
			'id' => $id
		));
	}
	
	/**
	 * Fetch a term's parent.
	 * @since 1.5.0-alpha
	 *
	 * @access private
	 * @param int $id -- The term's id.
	 * @return string
	 */
	private function getParent(int $id): string {
		global $rs_query;
		
		$parent = $rs_query->selectField(getTable('t'), 'name', array(
			'id' => $id
		));
		
		return empty($parent) ? '&mdash;' : $parent;
	}
	
	/**
	 * Construct a list of parents.
	 * @since 1.5.0-alpha
	 *
	 * @access private
	 * @param int $parent (optional) -- The term's parent.
	 * @param int $id (optional) -- The term's id.
	 * @return string
	 */
	private function getParentList(int $parent = 0, int $id = 0): string {
		global $rs_query;
		
		$list = array();
		
		$terms = $rs_query->select(getTable('t'), array('id', 'name'), array(
			'taxonomy' => getTaxonomyId($this->tax_data['name'])
		));
		
		foreach($terms as $term) {
			list($t_id, $t_name) = array(
				$term['id'],
				$term['name']
			);
			
			if($id !== 0) {
				// Skip the current term
				if($t_id === $id) continue;
				
				// Skip all descendant terms
				if($this->isDescendant($t_id, $id)) continue;
			}
			
			$list[] = domTag('option', array(
				'value' => $t_id,
				'selected' => $t_id === $parent,
				'content' => $t_name
			));
		}
		
		return implode('', $list);
	}
	
	/**
 	 * Fetch a list of terms based on a specific status.
 	 * @since 1.3.15-beta
 	 *
 	 * @access private
 	 * @param null|string $search -- The search query.
	 * @param bool $all (optional) -- Whether to return all or set a limit (for pagination).
 	 * @return array
 	 */
 	private function getResults(?string $search, bool $all = false): array {
		global $rs_query;
		
		$order_by = 'name';
		$order = 'ASC';
		$limit = $all === false ? array($this->paged['start'], $this->paged['per_page']) : 0;
		
		if(!is_null($search)) {
			// Search results
			return $rs_query->select(getTable('t'), '*', array(
				'name' => array('LIKE', '%' . $search . '%'),
				'taxonomy' => getTaxonomyId($this->tax_data['name'])
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		} else {
			// All results
			return $rs_query->select(getTable('t'), '*', array(
				'taxonomy' => getTaxonomyId($this->tax_data['name'])
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		}
	}
	
	/**
	 * Fetch the term count.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @param null|string $search -- The search query.
	 * @return int
	 */
	private function getEntryCount(?string $search): int {
		return count($this->getResults($search, true));
	}
	
	/**
	 * Fetch all associated action links.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @param array $term -- The term's data.
	 * @return string
	 */
	private function getActionLinks(array $term): string {
		$tax_name = str_replace(' ', '_', $this->tax_data['labels']['name_lowercase']);
		$actions = $this->tax_data['actions'];
		$action_list = array();
		
		foreach($actions as $key => $value) {
			if($value === 'create') continue;
			
			$privileged = match($value) {
				'edit' => userHasPrivilege('can_edit_' . $tax_name),
				'delete' => userHasPrivilege('can_delete_' . $tax_name),
				default => null
			};
			
			$action_list[] = array(
				'privileged' => $privileged,
				'link' => $value,
				'caption' => ucfirst($value)
			);
		}
		
		list($edit, $delete, $view) = $action_list;
		
		$action_links = array(
			// Edit
			$edit['privileged'] ? actionLink($edit['link'], array(
				'caption' => $edit['caption'],
				'id' => $term['id']
			)) : null,
			// Delete
			$delete['privileged'] ? actionLink($delete['link'], array(
				'classes' => 'modal-launch delete-item',
				'data_item' => strtolower($this->tax_data['labels']['name_singular']),
				'caption' => $delete['caption'],
				'id' => $term['id']
			)) : null,
			// View
			domTag('a', array(
				'href' => getPermalink($this->tax_data['name'], $term['parent'], $term['slug']),
				'content' => $view['caption']
			))
		);
		
		// Filter out any empty actions
		$action_links = array_filter($action_links);
		
		return implode(' &bull; ', $action_links);
	}
}