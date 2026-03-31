<?php
/**
 * Admin class used to implement the UserRole object. Inherits from the Settings class.
 * User roles allow privileged users to perform actions throughout the CMS.
 * User roles can be created, modified, and deleted.
 * @since 1.1.1-beta
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_user_role
 *
 * ## VARIABLES [7] ##
 * - private int $id
 * - private string $name
 * - private int $is_default
 * - private string $action
 * - private array $paged
 * - private string $page
 * - private array $admin_page
 *
 * ## METHODS [14] ##
 * - public __construct(int $id, string $action, string $page)
 * { LISTS, FORMS, & ACTIONS [4] }
 * - public listRecords(): void
 * - public createRecord(): void
 * - public editRecord(): void
 * - public deleteRecord(): void
 * { VALIDATION [1] }
 * - private validateSubmission(array $data): string
 * { MISCELLANEOUS [8] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private roleNameExists(string $name): bool
 * - private getPrivileges(int $id, int $is_default): string
 * - private getPrivilegesList(): string
 * - private getResults(?string $search, bool $all): array
 * - private getEntryCount(?string $search): int
 * - private getActionLinks(array $role): string
 */
namespace Admin;

class UserRole implements AdminInterface {
	/**
	 * The currently queried user role's id.
	 * @since 1.1.1-beta
	 *
	 * @access private
	 * @var int
	 */
	private $id;
	
	/**
	 * The currently queried user role's name.
	 * @since 1.1.1-beta
	 *
	 * @access private
	 * @var string
	 */
	private $name;
	
	/**
	 * The currently queried user role's status (default or not).
	 * @since 1.1.1-beta
	 *
	 * @access private
	 * @var int
	 */
	private $is_default;
	
	/**
	 * The current action.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $action;
	
	/**
	 * The pagination.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var array
	 */
	private $paged = array();
	
	/**
	 * The current settings page.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $page;
	
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
	 * @param int $id -- The role's id.
	 * @param string $action -- The current action.
	 * @param string $page -- The current settings page.
	 */
	public function __construct(int $id, string $action, string $page) {
		global $rs_query, $rs_admin_pages;
		
		$this->action = $action;
		$this->page = $page;
		$this->admin_page = $rs_admin_pages[basename($_SERVER['PHP_SELF'], '.php')];
		
		if($id > 0) {
			$cols = array_keys(get_object_vars($this));
			$exclude = array('action', 'paged', 'page', 'admin_page');
			$cols = array_diff($cols, $exclude);
			
			$role = $rs_query->selectRow(getTable('ur'), $cols, array(
				'id' => $id
			));
			
			foreach($role as $key => $value) $this->$key = $role[$key];
		} else {
			$this->id = 0;
		}
	}
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct a list of all records in the database.
	 * @since 1.7.1-alpha
	 *
	 * @access public
	 */
	public function listRecords(): void {
		global $rs_query;
		
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
					'default-privileges' => 'Default Privileges',
					'custom-privileges' => 'Custom Privileges'
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$roles = $this->getResults($search);
				
				foreach($roles as $role) {
					list($ur_id, $ur_name) = array(
						$role['id'],
						$role['name']
					);
					
					echo tableRow(
						// Name
						tdCell(domTag('strong', array(
							'content' => $ur_name
						)) . domTag('div', array(
							'class' => 'actions',
							'content' => $this->getActionLinks($role)
						)), 'name'),
						// Default Privileges
						tdCell($this->getPrivileges($ur_id, 1), 'default-privileges'),
						// Custom Privileges
						tdCell($this->getPrivileges($ur_id, 0), 'custom-privileges')
					);
				}
				
				if(empty($roles))
					echo tableRow(tdCell('There are no user roles to display.', '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($this->paged['current'], $this->paged['count']);
		
		domTagPr('h2', array(
			'class' => 'subheading',
			'content' => 'Default User Roles'
		));
		?>
		<table class="data-table">
			<thead>
				<?php echo tableHeaderRow($header_cols); ?>
			</thead>
			<tbody>
				<?php
				$roles = $rs_query->select(getTable('ur'), '*', array(
					'is_default' => 1
				), array(
					'order_by' => 'id'
				));
				
				foreach($roles as $role) {
					echo tableRow(
						// Name
						tdCell(domTag('strong', array(
							'content' => $role['name']
						)) . domTag('div', array(
							'class' => 'actions',
							'content' => domTag('em', array(
								'content' => 'default roles cannot be modified'
							))
						)), 'name'),
						// Default Privileges
						tdCell($this->getPrivileges($role['id'], 1), 'default-privileges'),
						// Custom Privileges
						tdCell($this->getPrivileges($role['id'], 0), 'custom-privileges')
					);
				}
				
				if(empty($roles))
					echo tableRow(tdCell('There are no user roles to display.', '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		includeFile(PATH . MODALS . '/modal-delete.php');
	}
	
	/**
	 * Create a new user role.
	 * @since 1.7.2-alpha
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
						'autocomplete' => 'off'
					));
					
					// Privileges
					echo formRow('Privileges', $this->getPrivilegesList());
					
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
	 * Edit an existing user role.
	 * @since 1.7.2-alpha
	 *
	 * @access public
	 */
	public function editRecord(): void {
		if(empty($this->id) || $this->id <= 0 || $this->is_default === 'yes') {
			redirect(ADMIN_URI . getQueryString(array(
				'page' => $this->page
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
						'value' => $this->name
					));
					
					// Privileges
					echo formRow('Privileges', $this->getPrivilegesList());
					
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
						'value' => 'Update ' . capitalize((str_ends_with($this->page, 's') ?
							substr($this->page, 0, -1) : $this->page))
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Delete an existing user role.
	 * @since 1.7.2-alpha
	 *
	 * @access public
	 */
	public function deleteRecord(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0 || $this->is_default === 'yes') {
			redirect(ADMIN_URI . getQueryString(array(
				'page' => $this->page
			)));
		}
		
		$rs_query->delete(getTable('ur'), array(
			'id' => $this->id
		));
		
		$rs_query->delete(getTable('ue'), array(
			'role' => $this->id
		));
		
		redirect(ADMIN_URI . getQueryString(array(
			'page' => $this->page,
			'exit_status' => 'del_success'
		)));
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the form data.
	 * @since 1.7.2-alpha
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateSubmission(array $data): string {
		global $rs_query;
		
		if(empty($data['name'])) {
			return exitNotice('REQ', -1);
			exit;
		}
		
		if($this->roleNameExists($data['name'])) {
			return exitNotice('That name is already in use. Please choose another one.', -1);
			exit;
		}
		
		switch($this->action) {
			case 'create':
				$insert_id = $rs_query->insert(getTable('ur'), array(
					'name' => $data['name']
				));
				
				if(!empty($data['privileges'])) {
					foreach($data['privileges'] as $privilege) {
						$rs_query->insert(getTable('ue'), array(
							'role' => $insert_id,
							'privilege' => $privilege
						));
					}
				}
				
				redirect(ADMIN_URI . getQueryString(array(
					'page' => $this->page,
					'id' => $insert_id,
					'action' => 'edit',
					'exit_status' => 'create_success'
				)));
				break;
			case 'edit':
				$rs_query->update(getTable('ur'), array(
					'name' => $data['name']
				), array(
					'id' => $this->id
				));
				
				$relationships = $rs_query->select(getTable('ue'), '*', array(
					'role' => $this->id
				));
				
				foreach($relationships as $relationship) {
					// Delete any unused relationships
					if(empty($data['privileges']) || !in_array($relationship['privilege'], $data['privileges'], true)) {
						$rs_query->delete(getTable('ue'), array(
							'id' => $relationship['id']
						));
					}
				}
				
				if(!empty($data['privileges'])) {
					foreach($data['privileges'] as $privilege) {
						$relationship = $rs_query->selectRow(getTable('ue'), 'COUNT(*)', array(
							'role' => $this->id,
							'privilege' => $privilege
						));
						
						if($relationship) {
							continue;
						} else {
							$rs_query->insert(getTable('ue'), array(
								'role' => $this->id,
								'privilege' => $privilege
							));
						}
					}
				}
				
				foreach($data as $key => $value) $this->$key = $value;
				
				redirect(ADMIN_URI . getQueryString(array(
					'page' => $this->page,
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
		switch($this->action) {
			case 'create':
				$title = capitalize($this->action . ' ' .
					(str_ends_with($this->page, 's') ? substr($this->page, 0, -1) : $this->page)
				);
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			case 'edit':
				$title = capitalize($this->action . ' ' .
					(str_ends_with($this->page, 's') ? substr($this->page, 0, -1) : $this->page)
				) . ': { ' . domTag('em', array(
					'content' => $this->name
				)) . ' }';
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			default:
				$title = capitalize($this->page);
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
				if(userHasPrivilege('can_create_user_roles')) {
					echo actionLink($this->admin_page['actions']['create'], array(
						'classes' => 'button',
						'caption' => $this->admin_page['labels']['create_button'],
						'page' => $this->page
					));
				}
				
				// Search
				recordSearch(array(
					'page' => $this->page
				));
				
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
			'create_success' => 'The user role was successfully created. ' . domTag('a', array(
				'href' => ADMIN_URI . getQueryString(array(
					'page' => $this->page
				)),
				'content' => 'Return to list'
			)) . '?',
			'edit_success' => 'User role updated! ' . domTag('a', array(
				'href' => ADMIN_URI . getQueryString(array(
					'page' => $this->page
				)),
				'content' => 'Return to list'
			)) . '?',
			'del_success' => 'The user role was successfully deleted.',
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Check whether a user role name exists in the database.
	 * @since 1.7.3-alpha
	 *
	 * @access private
	 * @param string $name -- The role's name.
	 * @return bool
	 */
	private function roleNameExists(string $name): bool {
		global $rs_query;
		
		if($this->id === 0) {
			return $rs_query->selectRow(getTable('ur'), 'COUNT(name)', array(
				'name' => $name
			)) > 0;
		} else {
			return $rs_query->selectRow(getTable('ur'), 'COUNT(name)', array(
				'name' => $name,
				'id' => array('<>', $this->id)
			)) > 0;
		}
	}
	
	/**
	 * Fetch a user role's privileges.
	 * @since 1.7.2-alpha
	 *
	 * @access private
	 * @param int $id -- The role's id.
	 * @param int $is_default -- Whether the privilege is a default one.
	 * @return string
	 */
	private function getPrivileges(int $id, int $is_default): string {
		global $rs_query;
		
		$privileges = array();
		
		$relationships = $rs_query->select(getTable('ue'), 'privilege', array(
			'role' => $id
		), array(
			'order_by' => 'privilege'
		));
		
		foreach($relationships as $relationship) {
			$privileges[] = $rs_query->selectField(getTable('up'), 'name', array(
				'id' => $relationship['privilege'],
				'is_default' => $is_default
			));
		}
		
		$privileges = array_filter($privileges);
		
		return empty($privileges) ? '&mdash;' : implode(', ', $privileges);
	}
	
	/**
	 * Construct a list of user privileges.
	 * @since 1.7.2-alpha
	 *
	 * @access private
	 * @return string
	 */
	private function getPrivilegesList(): string {
		global $rs_query;
		
		$privileges = $rs_query->select(getTable('up'), '*', array(), array(
			'order_by' => 'id'
		));
		
		$list = array();
		
		$list[] = domTag('li', array(
			'content' => domTag('input', array(
				'type' => 'checkbox',
				'id' => 'select-all',
				'class' => 'checkbox-input',
				'label' => array(
					'content' => domTag('span', array(
						'content' => 'SELECT ALL'
					))
				)
			))
		));
		
		foreach($privileges as $privilege) {
			$relationship = $rs_query->selectRow(getTable('ue'), 'COUNT(*)', array(
				'role' => $this->id,
				'privilege' => $privilege['id']
			));
			
			$list[] = domTag('li', array(
				'content' => domTag('input', array(
					'type' => 'checkbox',
					'class' => 'checkbox-input',
					'name' => 'privileges[]',
					'value' => $privilege['id'],
					'checked' => $relationship,
					'label' => array(
						'content' => domTag('span', array(
							'content' => $privilege['name']
						))
					)
				))
			));
		}
		
		return domTag('ul', array(
			'class' => 'checkbox-list',
			'content' => implode('', $list)
		));
	}
	
	/**
 	 * Fetch a list of user roles based on a specific status.
 	 * @since 1.3.15-beta
 	 *
 	 * @access private
 	 * @param null|string $search -- The search query.
	 * @param bool $all (optional) -- Whether to return all or set a limit (for pagination).
 	 * @return array
 	 */
 	private function getResults(?string $search, bool $all = false): array {
		global $rs_query;
		
		$order_by = 'id';
		$order = 'ASC';
		$limit = $all === false ? array($this->paged['start'], $this->paged['per_page']) : 0;
		
		if(!is_null($search)) {
			// Search results
			return $rs_query->select(getTable('ur'), '*', array(
				'name' => array('LIKE', '%' . $search . '%'),
				'is_default' => 0
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		} else {
			// All results
			return $rs_query->select(getTable('ur'), '*', array(
				'is_default' => 0
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		}
	}
	
	/**
	 * Fetch the total user role count.
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
	 * @param array $role -- The user role's data.
	 * @return string
	 */
	private function getActionLinks(array $role): string {
		$actions = $this->admin_page['actions'];
		$action_list = array();
		
		foreach($actions as $key => $value) {
			if($value === 'create') continue;
			
			$privileged = match($value) {
				'edit' => userHasPrivilege('can_edit_user_roles'),
				'delete' => userHasPrivilege('can_delete_user_roles'),
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
				'page' => $this->page,
				'id' => $role['id']
			)) : null,
			// Delete
			$delete['privileged'] ? actionLink($delete['link'], array(
				'classes' => 'modal-launch delete-item',
				'data_item' => 'user role',
				'caption' => $delete['caption'],
				'page' => $this->page,
				'id' => $role['id']
			)) : null
		);
		
		// Filter out any empty actions
		$action_links = array_filter($action_links);
		
		return implode(' &bull; ', $action_links);
	}
}