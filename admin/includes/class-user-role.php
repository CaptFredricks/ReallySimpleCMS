<?php
/**
 * Admin class used to implement the UserRole object. Inherits from the Settings class.
 * @since 1.1.1[b]
 *
 * User roles allow privileged users to perform actions throughout the CMS.
 * User roles can be created, modified, and deleted.
 */
class UserRole {
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
	 * @param int $id (optional; default: 0)
	 * @return null
	 */
	public function __construct($id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Create an array of columns to fetch from the database
		$cols = array_keys(get_object_vars($this));
		
		// Check whether the id is '0'
		if($id !== 0) {
			// Fetch the user role from the database
			$role = $rs_query->selectRow('user_roles', $cols, array('id'=>$id));
			
			// Loop through the array and set the class variables
			foreach($role as $key=>$value) $this->$key = $role[$key];
		}
	}
	
	/**
	 * Construct a list of user roles.
	 * @since 1.7.1[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listUserRoles() {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>User Roles</h1>
			<?php
			// Check whether the user has sufficient privileges to create user roles
			if(userHasPrivilege($session['role'], 'can_create_user_roles')) {
				?>
				<a class="button" href="?page=user_roles&action=create">Create New</a>
				<?php
			}
			
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The user role was successfully deleted.', true);
			
			// Fetch the user role entry count from the database
			$count = $rs_query->select('user_roles', 'COUNT(*)', array('_default'=>'no'));
			
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
				$table_header_cols = array('Name', 'Privileges');
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all user roles from the database
				$roles = $rs_query->select('user_roles', '*', array('_default'=>'no'), 'id', 'ASC', array($page['start'], $page['per_page']));
				
				// Loop through the user roles
				foreach($roles as $role) {
					// Set up the action links
					$actions = array(
						userHasPrivilege($session['role'], 'can_edit_user_roles') ? '<a href="?page=user_roles&id='.$role['id'].'&action=edit">Edit</a>' : '',
						userHasPrivilege($session['role'], 'can_delete_user_roles') ? '<a class="modal-launch delete-item" href="?page=user_roles&id='.$role['id'].'&action=delete" data-item="user role">Delete</a>' : ''
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						tableCell('<strong>'.$role['name'].'</strong><div class="actions">'.implode(' &bull; ', $actions).'</div>', 'name'),
						tableCell($this->getPrivileges($role['id']), 'privileges')
					);
				}
				
				// Display a notice if no user roles are found
				if(empty($roles))
					echo tableRow(tableCell('There are no user roles to display.', '', count($table_header_cols)));
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
		?>
		<h2 class="subheading">Default User Roles</h2>
		<table class="data-table">
			<thead>
				<?php
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all user roles from the database
				$roles = $rs_query->select('user_roles', '*', array('_default'=>'yes'), 'id');
				
				// Loop through the user roles
				foreach($roles as $role) {
					echo tableRow(
						tableCell('<strong>'.$role['name'].'</strong><div class="actions"><em>default roles cannot be modified</em></div>', 'name'),
						tableCell($this->getPrivileges($role['id']), 'privileges')
					);
				}
				
				// Display a notice if no user roles are found
				if(empty($roles))
					echo tableRow(tableCell('There are no user roles to display.', '', count($table_header_cols)));
				?>
			</tbody>
		</table>
		<?php
		// Include the delete modal					 
		include_once PATH.ADMIN.INC.'/modal-delete.php';												
	}
	
	/**
	 * Construct the 'Create User Role' form.
	 * @since 1.7.2[a]
	 *
	 * @access public
	 * @return null
	 */
	public function createUserRole() {
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
					echo formRow(array('Name', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>($_POST['name'] ?? '')));
					echo formRow('Privileges', $this->getPrivilegesList());
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create User Role'));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Construct the 'Edit User Role' form.
	 * @since 1.7.2[a]
	 *
	 * @access public
	 * @return null
	 */
	public function editUserRole() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the user role's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the 'List User Roles' page
			redirect('settings.php?page=user_roles');
		} else {
			// Check whether the role is a default user role
			if($this->_default === 'yes') {
				// Redirect to the 'List User Roles' page
				redirect('settings.php?page=user_roles');
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
							echo formRow(array('Name', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>$this->name));
							echo formRow('Privileges', $this->getPrivilegesList($this->id));
							echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
							echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update User Role'));
							?>
						</table>
					</form>
				</div>
				<?php
			}
		}
	}
	
	/**
	 * Delete a user role from the database.
	 * @since 1.7.2[a]
	 *
	 * @access public
	 * @return null
	 */
	public function deleteUserRole() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the user role's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the 'List User Roles' page
			redirect('settings.php?page=user_roles');
		} else {
			// Check whether the role is a default user role
			if($this->_default === 'yes') {
				// Redirect to the 'List User Roles' page
				redirect('settings.php?page=user_roles');
			} else {
				// Delete the user role from the database
				$rs_query->delete('user_roles', array('id'=>$this->id));
				
				// Delete the user relationship(s) from the database
				$rs_query->delete('user_relationships', array('role'=>$this->id));
				
				// Redirect to the 'List User Roles' page (with a success message)
				redirect('settings.php?page=user_roles&exit_status=success');
			}
		}
	}
	
	/**
	 * Validate the user role form data.
	 * @since 1.7.2[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return null|string (null on $id == 0; string on $id != 0)
	 */
	private function validateUserRoleData($data, $id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['name']))
			return statusMessage('R');
		
		// Make sure the name is not already being used
		if($this->roleNameExists($data['name'], $id))
			return statusMessage('That name is already in use. Please choose another one.');
		
		if($id === 0) {
			// Insert the new user role into the database
			$insert_id = $rs_query->insert('user_roles', array('name'=>$data['name']));
			
			// Check whether any privileges have been selected
			if(!empty($data['privileges'])) {
				// Loop through the privileges
				foreach($data['privileges'] as $privilege) {
					// Insert a new user relationship into the database
					$rs_query->insert('user_relationships', array('role'=>$insert_id, 'privilege'=>$privilege));
				}
			}
			
			// Redirect to the 'Edit User Role' page
			redirect('settings.php?page=user_roles&id='.$insert_id.'&action=edit');
		} else {
			// Update the user role in the database
			$rs_query->update('user_roles', array('name'=>$data['name']), array('id'=>$id));
			
			// Fetch all user relationships associated with the user role from the database
			$relationships = $rs_query->select('user_relationships', '*', array('role'=>$id));
			
			// Loop through the relationships
			foreach($relationships as $relationship) {
				// Check whether the relationship still exists
				if(empty($data['privileges']) || !in_array($relationship['privilege'], $data['privileges'])) {
					// Delete the unused relationship from the database
					$rs_query->delete('user_relationships', array('id'=>$relationship['id']));
				}
			}
			
			// Check whether any privileges have been selected
			if(!empty($data['privileges'])) {
				// Loop through the privileges
				foreach($data['privileges'] as $privilege) {
					// Fetch any relationships between the current privilege and the role from the database
					$relationship = $rs_query->selectRow('user_relationships', 'COUNT(*)', array('role'=>$id, 'privilege'=>$privilege));
					
					// Check whether the relationship already exists
					if($relationship) {
						// Skip to the next privilege
						continue;
					} else {
						// Insert a new user relationship into the database
						$rs_query->insert('user_relationships', array('role'=>$id, 'privilege'=>$privilege));
					}
				}
			}
			
			// Update the class variables
			foreach($data as $key=>$value) $this->$key = $value;
			
			// Return a status message
			return statusMessage('User role updated! <a href="settings.php?page=user_roles">Return to list</a>?', true);
		}
	}
	
	/**
	 * Check whether a user role name exists in the database.
	 * @since 1.7.3[a]
	 *
	 * @access private
	 * @param string $name
	 * @param int $id
	 * @return bool
	 */
	private function roleNameExists($name, $id) {
		// Extend the Query object
		global $rs_query;
		
		if($id === 0) {
			// Fetch the number of times the name appears in the database
			$count = $rs_query->selectRow('user_roles', 'COUNT(name)', array('name'=>$name));
		} else {
			// Fetch the number of times the name appears in the database (minus the current role)
			$count = $rs_query->selectRow('user_roles', 'COUNT(name)', array('name'=>$name, 'id'=>array('<>', $id)));
		}
		
		// Return true if the count is greater than zero
		return $count > 0;
	}
	
	/**
	 * Fetch a user role's privileges.
	 * @since 1.7.2[a]
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getPrivileges($id) {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty array to hold the privileges
		$privileges = array();
		
		// Fetch the user relationships from the database
		$relationships = $rs_query->select('user_relationships', 'privilege', array('role'=>$id), 'privilege');
		
		// Loop through the user relationships
		foreach($relationships as $relationship) {
			// Fetch the privilege's name from the database
			$privilege = $rs_query->selectField('user_privileges', 'name', array('id'=>$relationship['privilege']));
			
			// Assign the privilege to the array
			$privileges[] = $privilege;
		}
		
		// Return the privileges
		return empty($privileges) ? '&mdash;' : implode(', ', $privileges);
	}
	
	/**
	 * Construct a list of user privileges.
	 * @since 1.7.2[a]
	 *
	 * @access private
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function getPrivilegesList($id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Create a list with an opening unordered list tag
		$list = '<ul class="checkbox-list">';
		
		// Fetch all privileges from the database
		$privileges = $rs_query->select('user_privileges', '*', '', 'id');
		
		// Loop through the privileges
		foreach($privileges as $privilege) {
			// Fetch any existing user relationship from the database
			$relationship = $rs_query->selectRow('user_relationships', 'COUNT(*)', array('role'=>$id, 'privilege'=>$privilege['id']));
			
			// Construct the list
			$list .= '<li>'.formTag('input', array('type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'privileges[]', 'value'=>$privilege['id'], '*'=>($relationship ? 'checked' : ''), 'label'=>array('content'=>'<span>'.$privilege['name'].'</span>'))).'</li>';
		}
		
		// Close the unordered list
		$list .= '</ul>';
		
		// Return the list
		return $list;
	}
}