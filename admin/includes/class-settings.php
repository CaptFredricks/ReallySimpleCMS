<?php
/**
 * Admin class used to implement the Settings object.
 * @since 1.3.7[a]
 *
 * Settings allow some extra customization for the site via the admin dashboard.
 * Settings can be modified, but not created or deleted.
 */
class Settings {
	/**
	 * Construct a list of general settings.
	 * @since 1.3.7[a]
	 *
	 * @access public
	 * @return null
	 */
	public function generalSettings() {
		// Extend the Query class
		global $rs_query;
		
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateSettingsData($_POST) : '';
		
		// Fetch all settings from the database
		$db_settings = $rs_query->select('settings', '*');
		
		// Loop through the settings
		foreach($db_settings as $db_setting)
			$setting[$db_setting['name']] = $db_setting['value'];
		?>
		<div class="heading-wrap">
			<h1>General Settings</h1>
			<?php echo $message; ?>
		</div>
		<form class="data-form" action="" method="post" autocomplete="off">
			<table class="form-table">
				<?php
				// Check whether 'do_robots' has been set
				$do_robots = !$setting['do_robots'] ? 'checked' : '';
				
				echo formRow(array('Site Title', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'site_title', 'value'=>$setting['site_title']));
				echo formRow('Description', array('tag'=>'input', 'class'=>'text-input', 'name'=>'description', 'maxlength'=>155, 'value'=>$setting['description']));
				echo formRow(array('Site URL', true), array('tag'=>'input', 'type'=>'url', 'class'=>'text-input required invalid init', 'name'=>'site_url', 'value'=>$setting['site_url']));
				echo formRow(array('Admin Email', true), array('tag'=>'input', 'type'=>'email', 'class'=>'text-input required invalid init', 'name'=>'admin_email', 'value'=>$setting['admin_email']));
				echo formRow('Default User Role', array('tag'=>'select', 'class'=>'select-input', 'name'=>'default_user_role', 'content'=>$this->getUserRoles((int)$setting['default_user_role'])));
				echo formRow('Home Page', array('tag'=>'select', 'class'=>'select-input', 'name'=>'home_page', 'content'=>$this->getPageList((int)$setting['home_page'])));
				echo formRow('Search Engine Visibility', array('tag'=>'input', 'type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'do_robots', 'value'=>(int)$setting['do_robots'], '*'=>$do_robots, 'label'=>array('class'=>'checkbox-label', 'content'=>' <span>Discourage search engines from indexing this site</span>')));
				echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
				echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Settings'));
				?>
			</table>
		</form>
		<?php
	}
	
	/**
	 * Validate the settings form data.
	 * @since 1.3.7[a]
	 *
	 * @access private
	 * @param array $data
	 * @return string
	 */
	private function validateSettingsData($data) {
		// Extend the Query class
		global $rs_query;
		
		// Remove 'submit' from data
		array_pop($data);
		
		// Make sure no required fields are empty
		if(empty($data['site_title']) || empty($data['site_url']) || empty($data['admin_email']))
			return statusMessage('R');
		
		// Set the value of 'do_robots'
		$data['do_robots'] = isset($data['do_robots']) ? 0 : 1;
		
		// Fetch current value of 'do_robots' in the database
		$db_data = $rs_query->selectRow('settings', 'value', array('name'=>'do_robots'));
		
		// Update the settings in the database
		foreach($data as $name=>$value)
			$rs_query->update('settings', array('value'=>$value), array('name'=>$name));
		
		// File path for robots.txt
		$file_path = PATH.'/robots.txt';
		
		// Fetch the robots.txt file
		$file = file($file_path, FILE_IGNORE_NEW_LINES);
		
		// Check whether 'do_robots' has changed
		if($data['do_robots'] !== (int)$db_data['value']) {
			// Check whether 'do_robots' is set or not
			if($data['do_robots'] === 0) {
				// Block robots from crawling the site
				$file[1] = 'Disallow: /';
			} else {
				// Allow crawling to all directories except for /admin/
				$file[1] = 'Disallow: /admin/';
			}
			
			// Output changes to the file
			file_put_contents($file_path, implode(chr(10), $file));
		}
		
		// Return a success message
		return statusMessage('Settings updated!', true);
	}
	
	/**
	 * Construct a list of user roles.
	 * @since 1.6.4[a]
	 *
	 * @access private
	 * @param int $default
	 * @return string
	 */
	private function getUserRoles($default) {
		// Extend the Query class
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch all user roles from the database
		$roles = $rs_query->select('user_roles', '*', '', 'id');
		
		// Add each role to the list
		foreach($roles as $role)
			$list .= '<option value="'.$role['id'].'"'.($role['id'] === $default ? ' selected' : '').'>'.$role['name'].'</option>';
		
		// Return the list
		return $list;
	}
	
	/**
	 * Construct a list of existing pages.
	 * @since 1.3.7[a]
	 *
	 * @access private
	 * @param int $home_page
	 * @return string
	 */
	private function getPageList($home_page) {
		// Extend the Query class
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch all pages from the database
		$pages = $rs_query->select('posts', array('id', 'title'), array('status'=>'published', 'type'=>'page'), 'title');
		
		// Add each page to the list
		foreach($pages as $page)
			$list .= '<option value="'.$page['id'].'"'.($page['id'] === $home_page ? ' selected' : '').'>'.$page['title'].'</option>';
		
		// Return the list
		return $list;
	}
	
	/**
	 * Construct a list of user roles.
	 * @since 1.7.1[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listUserRoles() {
		// Extend the Query class
		global $rs_query;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>User Roles</h1>
			<a class="button" href="?page=user_roles&action=create">Create New</a>
			<?php
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The user role was successfully deleted.', true);
			
			// Fetch the user role entry count from the database
			$count = $rs_query->select('user_roles', 'COUNT(*)');
			
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
				$roles = $rs_query->select('user_roles', '*', '', 'id', 'ASC', array($page['start'], $page['per_page']));
				
				// Loop through the user roles
				foreach($roles as $role) {
					echo tableRow(
						tableCell('<strong>'.$role['name'].'</strong><div class="actions"><a href="?page=user_roles&id='.$role['id'].'&action=edit">Edit</a> &bull; <a id="delete" href="?page=user_roles&id='.$role['id'].'&action=delete">Delete</a></div>', 'name'),
						tableCell($this->getPrivileges($role['id']), 'privileges')
					);
				}
				
				// Display a notice if no user roles are found
				if(count($roles) === 0)
					echo tableRow(tableCell('There are no user roles to display.', '', count($table_header_cols)));
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
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
		<form class="data-form" action="" method="post" autocomplete="off">
			<table class="form-table">
				<?php
				echo formRow(array('Name', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>($_POST['name'] ?? '')));
				echo formRow('Privileges', $this->getPrivilegesList());
				echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
				echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create User Role'));
				?>
			</table>
		</form>
		<?php
	}
	
	/**
	 * Construct the 'Edit User Role' form.
	 * @since 1.7.2[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function editUserRole($id) {
		// Extend the Query class
		global $rs_query;
		
		// Check whether or not the user role id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List User Roles' page
			redirect('settings.php?page=user_roles');
		} else {
			// Fetch the number of times the user role appears in the database
			$count = $rs_query->selectRow('user_roles', 'COUNT(*)', array('id'=>$id));
			
			// Check whether or not the count is zero
			if($count === 0) {
				// Redirect to the 'List User Roles' page
				redirect('settings.php?page=user_roles');
			} else {
				// Validate the form data and return any messages
				$message = isset($_POST['submit']) ? $this->validateUserRoleData($_POST, $id) : '';
				
				// Fetch the user role from the database
				$role = $rs_query->selectRow('user_roles', '*', array('id'=>$id));
				?>
				<div class="heading-wrap">
					<h1>Edit User Role</h1>
					<?php echo $message; ?>
				</div>
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						echo formRow(array('Name', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>$role['name']));
						echo formRow('Privileges', $this->getPrivilegesList($role['id']));
						echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
						echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update User Role'));
						?>
					</table>
				</form>
				<?php
			}
		}
	}
	
	/**
	 * Delete a user role from the database.
	 * @since 1.7.2[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function deleteUserRole($id) {
		// Extend the Query class
		global $rs_query;
		
		// Check whether or not the user role id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List User Roles' page
			redirect('settings.php?page=user_roles');
		} else {
			// Delete the user role from the database
			$rs_query->delete('user_roles', array('id'=>$id));
			
			// Delete the user relationship(s) from the database
			$rs_query->delete('user_relationships', array('role'=>$id));
			
			// Redirect to the 'List User Roles' page (with a success message)
			redirect('settings.php?page=user_roles&exit_status=success');
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
		// Extend the Query class
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['name']))
			return statusMessage('R');
		
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
			
			// Return a status message
			return statusMessage('User role updated! <a href="settings.php?page=user_roles">Return to list</a>?', true);
		}
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
		// Extend the Query class
		global $rs_query;
		
		// Create an empty array to hold the privileges
		$privileges = array();
		
		// Fetch the user relationships from the database
		$relationships = $rs_query->select('user_relationships', 'privilege', array('role'=>$id));
		
		// Loop through the user relationships
		foreach($relationships as $relationship) {
			// Fetch the privilege's name from the database
			$privilege = $rs_query->selectRow('user_privileges', 'name', array('id'=>$relationship['privilege']));
			
			// Assign the privilege to the array
			$privileges[] = $privilege['name'];
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
		// Extend the Query class
		global $rs_query;
		
		// Create a list with an opening unordered list tag
		$list = '<ul id="privileges-list">';
		
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