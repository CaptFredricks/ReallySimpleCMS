<?php
/**
 * Admin class used to implement the Widget object. Inherits from the Post class.
 * Widgets are used to add small blocks of content to the front end of the website outside of the content area.
 * Widgets can be created, modified, and deleted. They are stored in the `posts` table as their own post type.
 * @since 1.6.0-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_widget
 *
 * ## VARIABLES [2] ##
 * See `Post` class for a list of inherited vars
 * - private string $post_type
 * - private array $admin_page
 *
 * ## METHODS [13] ##
 * See `Post` class for a list of inherited methods
 * - public __construct(int $id, string $action)
 * { LISTS, FORMS, & ACTIONS [5] }
 * - public listRecords(): void
 * - public createRecord(): void
 * - public editRecord(): void
 * - public updateWidgetStatus(string $status, int $id): void
 * - public deleteRecord(): void
 * { VALIDATION [1] }
 * - private validateSubmission(array $data): string
 * { MISCELLANEOUS [6] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private bulkActions(): void
 * - private getResults(?string $search, bool $all): array
 * - private getEntryCount(?string $search): int
 * - private getActionLinks(array $widget): string
 */
namespace Admin;

class Widget extends Post implements AdminInterface {
	/**
	 * The currently queried widget's post type.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $post_type = 'widget';
	
	/**
	 * The admin page's data.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @var array
	 */
	private $admin_page = array();
	
	/**
	 * Class constructor.
	 * @since 1.1.1-beta
	 *
	 * @access public
	 * @param int $id -- The widget's id.
	 * @param string $action -- The current action.
	 */
	public function __construct(int $id, string $action) {
		global $rs_query, $rs_admin_pages;
		
		$this->action = $action;
		$this->admin_page = $rs_admin_pages[basename($_SERVER['PHP_SELF'], '.php')];
		
		if($id > 0) {
			$cols = array_keys(get_object_vars($this));
			$exclude = array('action', 'paged', 'post_type', 'admin_page');
			$cols = array_diff($cols, $exclude);
			
			$widget = $rs_query->selectRow(getTable('p'), $cols, array(
				'id' => $id,
				'type' => $this->post_type
			));
			
			foreach($widget as $key => $value) $this->$key = $widget[$key];
		} else {
			$this->id = 0;
		}
	}
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct a list of all widgets in the database.
	 * @since 1.6.0-alpha
	 *
	 * @access public
	 */
	public function listRecords(): void {
		// Query vars
		$search = $_GET['search'] ?? null;
		$this->paged = paginate((int)($_GET['paged'] ?? 1));
		
		$this->pageHeading();
		?>
		<table class="data-table has-bulk-select">
			<thead>
				<?php
				$header_cols = array(
					'bulk-select' => domTag('input', array(
						'type' => 'checkbox',
						'class' => 'checkbox bulk-selector'
					)),
					'title' => 'Title',
					'slug' => 'Slug',
					'status' => 'Status'
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$widgets = $this->getResults($search);
				
				foreach($widgets as $widget) {
					echo tableRow(
						// Bulk select
						tdCell(domTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox',
							'value' => $widget['id']
						)), 'bulk-select'),
						// Title
						tdCell(domTag('strong', array(
							'content' => $widget['title']
						)) . domTag('div', array(
							'class' => 'actions',
							'content' => $this->getActionLinks($widget)
						)), 'title'),
						// Slug
						tdCell($widget['slug'], 'slug'),
						// Status
						tdCell(ucfirst($widget['status']), 'status')
					);
				}
				
				if(empty($widgets))
					echo tableRow(tdCell($this->admin_page['labels']['no_items'], '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Bulk actions
		if(!empty($widgets)) $this->bulkActions();
		
		// Set up page navigation
		echo pagerNav($this->paged['current'], $this->paged['count']);
		
        includeFile(PATH . MODALS . '/modal-delete.php');
	}
	
	/**
	 * Create a new widget.
	 * @since 1.6.0-alpha
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
					// Title
					echo formRow(array('Title', true), array(
						'tag' => 'input',
						'id' => 'title-field',
						'class' => 'text-input required invalid init',
						'name' => 'title',
						'value' => ($_POST['title'] ?? ''),
						'placeholder' => $this->admin_page['labels']['title_placeholder']
					));
					
					// Slug
					echo formRow(array('Slug', true), array(
						'tag' => 'input',
						'id' => 'slug-field',
						'class' => 'text-input required invalid init',
						'name' => 'slug',
						'value' => ($_POST['slug'] ?? '')
					));
					
					// Content
					echo formRow('Content', array(
						'tag' => 'textarea',
						'id' => 'content-field',
						'class' => 'textarea-input',
						'name' => 'content',
						'cols' => 30,
						'rows' => 10,
						'content' => htmlspecialchars(($_POST['content'] ?? ''))
					));
					
					// Status
					echo formRow('Status', array(
						'tag' => 'select',
						'id' => 'status-field',
						'class' => 'select-input',
						'name' => 'status',
						'content' => domTag('option', array(
							'value' => 'active',
							'content' => 'Active'
						)) . domTag('option', array(
							'value' => 'inactive',
							'content' => 'Inactive'
						))
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
						'value' => $this->admin_page['labels']['create_button']
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Edit an existing widget.
	 * @since 1.6.1-alpha
	 *
	 * @access public
	 */
	public function editRecord(): void {
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Title
					echo formRow(array('Title', true), array(
						'tag' => 'input',
						'id' => 'title-field',
						'class' => 'text-input required invalid init',
						'name' => 'title',
						'value' => $this->title,
						'placeholder' => $this->admin_page['labels']['title_placeholder']
					));
					
					// Slug
					echo formRow(array('Slug', true), array(
						'tag' => 'input',
						'id' => 'slug-field',
						'class' => 'text-input required invalid init',
						'name' => 'slug',
						'value' => $this->slug
					));
					
					// Content
					echo formRow('Content', array(
						'tag' => 'textarea',
						'id' => 'content-field',
						'class' => 'textarea-input',
						'name' => 'content',
						'cols' => 30,
						'rows' => 10,
						'content' => htmlspecialchars($this->content)
					));
					
					// Status
					echo formRow('Status', array(
						'tag' => 'select',
						'id' => 'status-field',
						'class' => 'select-input',
						'name' => 'status',
						'content' => domTag('option', array(
							'value' => 'active',
							'selected' => ($this->status === 'active' ? 1 : 0),
							'content' => 'Active'
						)) . domTag('option', array(
							'value' => 'inactive',
							'selected' => ($this->status === 'inactive' ? 1 : 0),
							'content' => 'Inactive'
						))
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
						'value' => $this->admin_page['labels']['update_button']
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Update a widget's status.
	 * @since 1.2.9-beta
	 *
	 * @access public
	 * @param string $status -- The widget's status.
	 * @param int $id -- The widget's id.
	 */
	public function updateWidgetStatus(string $status, int $id): void {
		global $rs_query;
		
		$this->id = $id;
		
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$rs_query->update(getTable('p'), array(
			'status' => $status
		), array(
			'id' => $this->id,
			'type' => $this->post_type
		));
	}
	
	/**
	 * Delete an existing widget.
	 * @since 1.6.1-alpha
	 *
	 * @access public
	 */
	public function deleteRecord(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$rs_query->delete(getTable('p'), array(
			'id' => $this->id,
			'type' => $this->post_type
		));
		
		redirect(ADMIN_URI . getQueryString(array(
			'exit_status' => 'del_success'
		)));
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the form data.
	 * @since 1.6.2-alpha
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateSubmission(array $data): string {
		global $rs_query;
		
		if(empty($data['title']) || empty($data['slug'])) {
			return exitNotice('REQ', -1);
			exit;
		}
		
		$slug = sanitize($data['slug']);
		
		// Make sure the slug is unique
		if($this->slugExists($slug))
			$slug = getUniquePostSlug($slug);
		
		if($data['status'] !== 'active' && $data['status'] !== 'inactive')
			$data['status'] = 'active';
		
		switch($this->action) {
			case 'create':
				$insert_id = $rs_query->insert(getTable('p'), array(
					'title' => $data['title'],
					'created' => 'NOW()',
					'modified' => 'NOW()',
					'content' => $data['content'],
					'status' => $data['status'],
					'slug' => $slug,
					'type' => $this->post_type
				));
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $insert_id,
					'action' => 'edit',
					'exit_status' => 'create_success'
				)));
				break;
			case 'edit':
				$rs_query->update(getTable('p'), array(
					'title' => $data['title'],
					'modified' => 'NOW()',
					'content' => $data['content'],
					'status' => $data['status'],
					'slug' => $slug
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
		$labels = $this->admin_page['labels'];
		
		switch($this->action) {
			case 'create':
				$title = $labels['create_item'];
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			case 'edit':
				$title = $labels['edit_item'] . ': { ' . domTag('em', array(
					'content' => $this->title
				)) . ' }';
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			default:
				$title = $labels['name'];
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
				if(userHasPrivilege('can_create_widgets')) {
					echo actionLink($this->admin_page['actions']['create'], array(
						'classes' => 'button',
						'caption' => $labels['create_button']
					));
				}
				
				// Search
				recordSearch();
				
				//Info
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
		return exitNotice(match($exit_status) {
			'create_success' => 'The widget was successfully created. ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'edit_success' => 'Widget updated! ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'del_success' => 'The widget was successfully deleted.',
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Construct bulk actions.
	 * @since 1.2.9-beta
	 *
	 * @access private
	 */
	private function bulkActions(): void {
		$labels = $this->admin_page['labels'];
		?>
		<div class="bulk-actions">
			<?php
			if(userHasPrivilege('can_edit_widgets')) {
				domTagPr('select', array(
					'class' => 'actions',
					'content' => domTag('option', array(
						'value' => 'active',
						'content' => 'Active'
					)) . domTag('option', array(
						'value' => 'inactive',
						'content' => 'Inactive'
					))
				));
				
				// Update status
				button(array(
					'class' => 'bulk-update',
					'title' => 'Bulk status update',
					'label' => $labels['bulk_update']
				));
			}
			
			if(userHasPrivilege('can_delete_widgets')) {
				// Delete
				button(array(
					'class' => 'bulk-delete',
					'title' => 'Bulk delete',
					'label' => $labels['bulk_delete']
				));
			}
			?>
		</div>
		<?php
	}
	
	/**
 	 * Fetch a list of widgets based on a specific status.
 	 * @since 1.3.15-beta
 	 *
 	 * @access private
 	 * @param null|string $search -- The search query.
	 * @param bool $all (optional) -- Whether to return all or set a limit (for pagination).
 	 * @return array
 	 */
 	private function getResults(?string $search, bool $all = false): array {
		global $rs_query;
		
		$order_by = 'title';
		$order = 'ASC';
		$limit = $all === false ? array($this->paged['start'], $this->paged['per_page']) : 0;
		
		if(!is_null($search)) {
			// Search results
			return $rs_query->select(getTable('p'), '*', array(
				'title' => array('LIKE', '%' . $search . '%'),
				'type' => $this->post_type
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		} else {
			// All results
			return $rs_query->select(getTable('p'), '*', array(
				'type' => $this->post_type
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		}
	}
	
	/**
	 * Fetch the total widget count.
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
	 * @param array $widget -- The widget's data.
	 * @return string
	 */
	private function getActionLinks(array $widget): string {
		$actions = $this->admin_page['actions'];
		$action_list = array();
		
		foreach($actions as $key => $value) {
			$continue = match($value) {
				'create', 'view', 'preview',
				'duplicate', 'replace', 'trash', 'restore' => true,
				default => false
			};
			
			if($continue) continue;
			
			$privileged = match($value) {
				'edit' => userHasPrivilege('can_edit_widgets'),
				'delete' => userHasPrivilege('can_delete_widgets'),
				default => null
			};
			
			$action_list[] = array(
				'privileged' => $privileged,
				'link' => $value,
				'caption' => ucfirst($value)
			);
		}
		
		list($edit, $delete) = $action_list;
		
		$action_links = array(
			// Edit
			$edit['privileged'] ? actionLink($edit['link'], array(
				'caption' => $edit['caption'],
				'id' => $widget['id']
			)) : null,
			// Delete
			$delete['privileged'] ? actionLink($delete['link'], array(
				'classes' => 'modal-launch delete-item',
				'data_item' => 'widget',
				'caption' => $delete['caption'],
				'id' => $widget['id']
			)) : null
		);
		
		// Filter out any empty actions
		$action_links = array_filter($action_links);
		
		return implode(' &bull; ', $action_links);
	}
}