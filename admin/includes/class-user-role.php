<?php
/**
 * Admin class used to implement the UserRole object. Inherits from the Settings class.
 * @since 1.1.1[b]
 *
 * User roles allow privileged users to perform actions throughout the CMS.
 * User roles can be created, modified, and deleted.
 */
class UserRole implements AdminInterface {
	/**
	 * The currently queried user role's id.
	 * @since 1.1.1[b]
	 *
	 * @access private
	 * @var int
	 */
	private $id;
	
	/**
	 * The currently queried user role's name.
	 * @since 1.1.1[b]
	 *
	 * @access private
	 * @var string
	 */
	private $name;
	
	/**
	 * The currently queried user role's status (default or not).
	 * @since 1.1.1[b]
	 *
	 * @access private
	 * @var string
	 */
	private $_default;
	
	/**
	 * Class constructor.
	 * @since 1.1.1[b]
	 *
	 * @access public
	 * @param int $id (optional) -- The role's id.
	 */
	public function __construct(int $id = 0) {
		global $rs_query;
		
		$cols = array_keys(get_object_vars($this));
		
		if($id !== 0) {
			$role = $rs_query->selectRow('user_roles', $cols, array('id' => $id));
			
			// Set the class variable values
			foreach($role as $key => $value) $this->$key = $role[$key];
		}
	}
	
	/**
	 * Construct a list of all user roles in the database.
	 * @since 1.7.1[a]
	 *
	 * @access public
	 */
	public function listRecords(): void {
		global $rs_query;
		
		// Query vars
		$page = $_GET['page'] ?? '';
		$search = $_GET['search'] ?? null;
		$paged = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>User Roles</h1>
			<?php
			// Check whether the user has sufficient privileges to create user roles
			if(userHasPrivilege('can_create_user_roles')) {
				echo actionLink('create', array(
					'classes' => 'button',
					'caption' => 'Create New',
					'page' => 'user_roles'
				));
			}
			
			recordSearch(array(
				'page' => $page
			));
			adminInfo();
			?>
			<hr>
			<?php
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo exitNotice('The user role was successfully deleted.');
			
			if(!is_null($search)) {
				$count = $rs_query->select('user_roles', 'COUNT(*)', array(
					'name' => array('LIKE', '%' . $search . '%'),
					'_default' => 'no'
				));
			} else {
				$count = $rs_query->select('user_roles', 'COUNT(*)', array(
					'_default' => 'no'
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
				$header_cols = array('Name', 'Privileges');
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				if(!is_null($search)) {
					$roles = $rs_query->select('user_roles', '*', array(
						'name' => array('LIKE', '%' . $search . '%'),
						'_default' => 'no'
					), 'id', 'ASC', array(
						$paged['start'],
						$paged['per_page']
					));
				} else {
					$roles = $rs_query->select('user_roles', '*',
						array('_default' => 'no'), 'id', 'ASC',
						array(
							$paged['start'],
							$paged['per_page']
						)
					);
				}
				
				foreach($roles as $role) {
					$actions = array(
						// Edit
						userHasPrivilege('can_edit_user_roles') ? actionLink('edit', array(
							'caption' => 'Edit',
							'page' => 'user_roles',
							'id' => $role['id']
						)) : null,
						// Delete
						userHasPrivilege('can_delete_user_roles') ? actionLink('delete', array(
							'classes' => 'modal-launch delete-item',
							'data_item' => 'user role',
							'caption' => 'Delete',
							'page' => 'user_roles',
							'id' => $role['id']
						)) : null
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						// Name
						tdCell('<strong>' . $role['name'] . '</strong><div class="actions">' .
							implode(' &bull; ', $actions) . '</div>', 'name'),
						// Privileges
						tdCell($this->getPrivileges($role['id']), 'privileges')
					);
				}
				
				if(empty($roles)) {
					echo tableRow(tdCell('There are no user roles to display.', '',
						count($header_cols)
					));
				}
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($paged['current'], $paged['count']);
		?>
		<h2 class="subheading">Default User Roles</h2>
		<table class="data-table">
			<thead>
				<?php echo tableHeaderRow($header_cols); ?>
			</thead>
			<tbody>
				<?php
				$roles = $rs_query->select('user_roles', '*', array('_default' => 'yes'), 'id');
				
				foreach($roles as $role) {
					echo tableRow(
						// Name
						tdCell('<strong>' . $role['name'] . '</strong><div class="actions"><em>default roles cannot be modified</em></div>', 'name'),
						// Privileges
						tdCell($this->getPrivileges($role['id']), 'privileges')
					);
				}
				
				if(empty($roles)) {
					echo tableRow(tdCell('There are no user roles to display.', '',
						count($header_cols)
					));
				}
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		include_once PATH . ADMIN . INC . '/modal-delete.php';
	}
	
	/**
	 * Create a new user role.
	 * @since 1.7.2[a]
	 *
	 * @access public
	 */
	public function createRecord(): void {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateUserRoleData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1>Create User Role</h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Name
					echo formRow(array('Name', true), array(
						'tag' => 'input',
						'class' => 'text-input required invalid init',
						'name' => 'name',
						'value' => ($_POST['name'] ?? '')
					));
					
					// Privileges
					echo formRow('Privileges', $this->getPrivilegesList());
					
					// Separator
					echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => 'Create User Role'
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Edit an existing user role.
	 * @since 1.7.2[a]
	 *
	 * @access public
	 */
	public function editRecord(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI . '?page=user_roles');
		} else {
			if($this->_default === 'yes') {
				redirect(ADMIN_URI . '?page=user_roles');
			} else {
				// Validate the form data and return any messages
				$message = isset($_POST['submit']) ? $this->validateUserRoleData($_POST, $this->id) : '';
				?>
				<div class="heading-wrap">
					<h1>Edit User Role</h1>
					<?php echo $message; ?>
				</div>
				<div class="data-form-wrap clear">
					<form class="data-form" action="" method="post" autocomplete="off">
						<table class="form-table">
							<?php
							// Name
							echo formRow(array('Name', true), array(
								'tag' => 'input',
								'class' => 'text-input required invalid init',
								'name' => 'name',
								'value' => $this->name
							));
							
							// Privileges
							echo formRow('Privileges', $this->getPrivilegesList($this->id));
							
							// Separator
							echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
							
							// Submit button
							echo formRow('', array(
								'tag' => 'input',
								'type' => 'submit',
								'class' => 'submit-input button',
								'name' => 'submit',
								'value' => 'Update User Role'
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
	 * Delete an existing user role.
	 * @since 1.7.2[a]
	 *
	 * @access public
	 */
	public function deleteRecord(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI . '?page=user_roles');
		} else {
			if($this->_default === 'yes') {
				redirect(ADMIN_URI . '?page=user_roles');
			} else {
				$rs_query->delete('user_roles', array('id' => $this->id));
				$rs_query->delete('user_relationships', array('role' => $this->id));
				
				redirect(ADMIN_URI . '?page=user_roles&exit_status=success');
			}
		}
	}
	
	/**
	 * Validate the user role form data.
	 * @since 1.7.2[a]
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @param int $id (optional) -- The role's id.
	 * @return string
	 */
	private function validateUserRoleData(array $data, int $id = 0): string {
		global $rs_query;
		
		if(empty($data['name']))
			return exitNotice('REQ', -1);
		
		if($this->roleNameExists($data['name'], $id))
			return exitNotice('That name is already in use. Please choose another one.', -1);
		
		if($id === 0) {
			// New user role
			$insert_id = $rs_query->insert('user_roles', array('name' => $data['name']));
			
			if(!empty($data['privileges'])) {
				foreach($data['privileges'] as $privilege) {
					$rs_query->insert('user_relationships', array(
						'role' => $insert_id,
						'privilege' => $privilege
					));
				}
			}
			
			redirect(ADMIN_URI . '?page=user_roles&id=' . $insert_id . '&action=edit');
		} else {
			// Existing user role
			$rs_query->update('user_roles', array('name' => $data['name']), array('id' => $id));
			
			$relationships = $rs_query->select('user_relationships', '*', array('role' => $id));
			
			foreach($relationships as $relationship) {
				// Check whether the relationship still exists
				if(empty($data['privileges']) || !in_array($relationship['privilege'], $data['privileges'], true)) {
					// Delete the unused relationship from the database
					$rs_query->delete('user_relationships', array('id' => $relationship['id']));
				}
			}
			
			if(!empty($data['privileges'])) {
				foreach($data['privileges'] as $privilege) {
					$relationship = $rs_query->selectRow('user_relationships', 'COUNT(*)', array(
						'role' => $id,
						'privilege' => $privilege
					));
					
					if($relationship) {
						continue;
					} else {
						$rs_query->insert('user_relationships', array(
							'role' => $id,
							'privilege' => $privilege
						));
					}
				}
			}
			
			// Update the class variables
			foreach($data as $key => $value) $this->$key = $value;
			
			return exitNotice('User role updated! <a href="' . ADMIN_URI . '?page=user_roles">Return to list</a>?');
		}
	}
	
	/**
	 * Check whether a user role name exists in the database.
	 * @since 1.7.3[a]
	 *
	 * @access private
	 * @param string $name -- The role's name.
	 * @param int $id -- The role's id.
	 * @return bool
	 */
	private function roleNameExists(string $name, int $id): bool {
		global $rs_query;
		
		if($id === 0) {
			return $rs_query->selectRow('user_roles', 'COUNT(name)', array('name' => $name)) > 0;
		} else {
			return $rs_query->selectRow('user_roles', 'COUNT(name)', array(
				'name' => $name,
				'id' => array('<>', $id)
			)) > 0;
		}
	}
	
	/**
	 * Fetch a user role's privileges.
	 * @since 1.7.2[a]
	 *
	 * @access private
	 * @param int $id -- The role's id.
	 * @return string
	 */
	private function getPrivileges(int $id): string {
		global $rs_query;
		
		$privileges = array();
		
		$relationships = $rs_query->select('user_relationships', 'privilege', array(
			'role' => $id
		), 'privilege');
		
		foreach($relationships as $relationship) {
			$privileges[] = $rs_query->selectField('user_privileges', 'name', array(
				'id' => $relationship['privilege']
			));
		}
		
		return empty($privileges) ? '&mdash;' : implode(', ', $privileges);
	}
	
	/**
	 * Construct a list of user privileges.
	 * @since 1.7.2[a]
	 *
	 * @access private
	 * @param int $id (optional) -- The role's id.
	 * @return string
	 */
	private function getPrivilegesList(int $id = 0): string {
		global $rs_query;
		
		$list = '<ul class="checkbox-list">';
		
		$privileges = $rs_query->select('user_privileges', '*', array(), 'id');
		
		$list .= '<li>' . tag('input', array(
			'type' => 'checkbox',
			'id' => 'select-all',
			'class' => 'checkbox-input',
			'label' => array(
				'content' => tag('span', array(
					'content' => 'SELECT ALL'
				))
			)
		)) . '</li>';
		
		foreach($privileges as $privilege) {
			$relationship = $rs_query->selectRow('user_relationships', 'COUNT(*)', array(
				'role' => $id,
				'privilege' => $privilege['id']
			));
			
			$list .= '<li>' . tag('input', array(
				'type' => 'checkbox',
				'class' => 'checkbox-input',
				'name' => 'privileges[]',
				'value' => $privilege['id'],
				'checked' => $relationship,
				'label' => array(
					'content' => tag('span', array(
						'content' => $privilege['name']
					))
				)
			)) . '</li>';
		}
		
		$list .= '</ul>';
		
		return $list;
	}
}