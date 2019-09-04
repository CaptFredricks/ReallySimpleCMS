<?php
/**
 * Admin class used to implement the User object.
 * @since 1.1.0[a]
 *
 * Users have various privileges on the website not afforded to visitors, depending on their access level.
 * Users can be created, modified, and deleted.
 */
class User {
	/**
	 * Set the minimum username length.
	 * @since 1.1.0[a]
	 * @var int
	 */
	const UN_LENGTH = 4;
	
	/**
	 * Set the minimum password length.
	 * @since 1.1.0[a]
	 * @var int
	 */
	const PW_LENGTH = 8;
	
	/**
	 * Construct a list of all users in the database.
	 * @since 1.2.1[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listEntries() {
		// Extend the Query class
		global $rs_query;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Users</h1>
			<a class="button" href="?action=create">Create New</a>
			<?php
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The user was successfully deleted.', true);
			
			// Get the user count
			$count = $rs_query->select('users', 'COUNT(*)');
			
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
				// Fill an array with the table header columns
				$table_header_cols = array('Username', 'Full Name', 'Email', 'Registered', 'Role', 'Status', 'Last Login');
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all users from the database
				$users = $rs_query->select('users', '*', '', 'username', 'ASC', array($page['start'], $page['per_page']));
		
				// Loop through the users
				foreach($users as $user) {
					// Fetch the user metadata from the database
					$meta = $this->getUserMeta($user['id']);
					
					echo tableRow(
						tableCell('<img class="avatar" src="'.(!empty($meta['avatar']) ? '' : '').'" width="32" height="32"><strong>'.$user['username'].'</strong><div class="actions"><a href="?id='.$user['id'].'&action=edit">Edit</a> &bull; <a class="delete-item" href="javascript:void(0)" rel="'.$user['id'].'">Delete</a></div>', 'username'),
						tableCell(empty($meta['first_name']) && empty($meta['last_name']) ? '&mdash;' : $meta['first_name'].' '.$meta['last_name'], 'full-name'),
						tableCell($user['email'], 'email'),
						tableCell(formatDate($user['registered'], 'd M Y @ g:i A'), 'registered'),
						tableCell($this->getRole($user['role']), 'role'),
						tableCell(is_null($user['session']) ? 'Offline' : 'Online', 'status'),
						tableCell(is_null($user['last_login']) ? 'Never' : formatDate($user['last_login'], 'd M Y @ g:i A'), 'last-login')
					);
				}
				
				// Display a notice if no users are found
				if(count($users) === 0)
					echo tableRow(tableCell('There are no users to display.', '', count($table_header_cols)));
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
	}
	
	/**
	 * Construct the 'Create User' form.
	 * @since 1.1.2[a]
	 *
	 * @access public
	 * @return null
	 */
	public function createEntry() {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1>Create User</h1>
			<?php echo $message; ?>
		</div>
		<form class="data-form" action="" method="post" autocomplete="off">
			<table class="form-table">
				<?php
				echo formRow(array('Username', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'username', 'value'=>($_POST['username'] ?? '')));
				echo formRow(array('Email', true), array('tag'=>'input', 'type'=>'email', 'class'=>'text-input required invalid init', 'name'=>'email', 'value'=>($_POST['email'] ?? '')));
				echo formRow('First Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'first_name', 'value'=>($_POST['first_name'] ?? '')));
				echo formRow('Last Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'last_name', 'value'=>($_POST['last_name'] ?? '')));
				echo formRow(array('Password', true), array('tag'=>'input', 'id'=>'pw-input', 'class'=>'text-input required invalid init', 'name'=>'password'), array('tag'=>'input', 'type'=>'button', 'id'=>'pw-btn', 'class'=>'button-input button', 'value'=>'Generate Password'), array('tag'=>'br'), array('tag'=>'input', 'type'=>'checkbox', 'id'=>'pw-chk', 'class'=>'checkbox-input', 'name'=>'pass_saved', 'value'=>'checked', 'label'=>array('id'=>'chk-label', 'class'=>'checkbox-label required invalid init', 'content'=>' <span>I have copied the password to a safe place.</span>')));
				echo formRow('Avatar', array('tag'=>'input', 'type'=>'hidden', 'id'=>'img-input', 'name'=>'avatar', 'value'=>($_POST['avatar'] ?? '')), array('tag'=>'input', 'type'=>'button', 'id'=>'img-choose', 'class'=>'button-input button', 'value'=>'Choose Image'));
				echo formRow('Role', array('tag'=>'select', 'class'=>'select-input', 'name'=>'role', 'content'=>$this->getRoleList()));
				echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
				echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create User'));
				?>
			</table>
		</form>
		<?php
	}
	
	/**
	 * Construct the 'Edit User' form.
	 * @since 1.2.1[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function editEntry($id) {
		// Extend the Query class
		global $rs_query;
		
		// Check whether or not the user id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Users' page
			redirect('users.php');
		} else {
			// Fetch the number of times the user appears in the database
			$count = $rs_query->selectRow('users', 'COUNT(*)', array('id'=>$id));
			
			// Check whether or not the count is zero
			if($count === 0) {
				// Redirect to the 'List Users' page
				redirect('users.php');
			} else {
				// Validate the form data and return any messages
				$message = isset($_POST['submit']) ? $this->validateData($_POST, $id) : '';
				
				// Fetch the user from the database
				$user = $rs_query->selectRow('users', '*', array('id'=>$id));
				
				// Fetch the user metadata from the database
				$meta = $this->getUserMeta($id);
				?>
				<div class="heading-wrap">
					<h1>Edit User</h1>
					<?php echo $message; ?>
				</div>
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						echo formRow(array('Username', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'username', 'value'=>$user['username']));
						echo formRow(array('Email', true), array('tag'=>'input', 'type'=>'email', 'class'=>'text-input required invalid init', 'name'=>'email', 'value'=>$user['email']));
						echo formRow('First Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'first_name', 'value'=>$meta['first_name']));
						echo formRow('Last Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'last_name', 'value'=>$meta['last_name']));
						echo formRow('Avatar', array('tag'=>'img', 'src'=>$this->getAvatar($meta['avatar']), 'width'=>150), array('tag'=>'br'), array('tag'=>'input', 'type'=>'hidden', 'id'=>'img-input', 'name'=>'avatar', 'value'=>$meta['avatar']), array('tag'=>'input', 'type'=>'button', 'id'=>'img-choose', 'class'=>'button-input button', 'value'=>'Choose Image'));
						echo formRow('Role', array('tag'=>'select', 'class'=>'select-input', 'name'=>'role', 'content'=>$this->getRoleList($user['role'])));
						echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
						echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update User'));
						?>
					</table>
				</form>
				<a href="?id=<?php echo $id; ?>&action=reset_password">Reset Password</a>
				<?php
			}
		}
	}
	
	/**
	 * Delete a user from the database.
	 * @since 1.2.3[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function deleteEntry($id) {
		// Extend the Query class
		global $rs_query;
		
		// Check whether or not the user id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Users' page
			redirect('users.php');
		} else {
			// Delete the user from the database
			$rs_query->delete('users', array('id'=>$id));
			
			// Delete the user metadata from the database
			$rs_query->delete('usermeta', array('user'=>$id));
			
			// Redirect to the 'List Users' page (with a success message)
			redirect('users.php?exit_status=success');
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 1.2.0[a]
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
		if(empty($data['username']) || empty($data['email']))
			return statusMessage('R');
		
		// Make sure the username is long enough
		if(strlen($data['username']) < self::UN_LENGTH)
			return statusMessage('Username must be at least '.self::UN_LENGTH.' characters long.');
		
		// Make sure the username is not already being used
		if($this->usernameExists($data['username'], $id))
			return statusMessage('That username has already been taken. Please choose another one.');
		
		// Create an array to hold the user metadata
		$usermeta = array('first_name'=>$data['first_name'], 'last_name'=>$data['last_name'], 'avatar'=>$data['avatar']);
		
		if($id === 0) {
			// Make sure the password field is not empty
			if(empty($data['password']))
				return statusMessage('R');
			
			// Make sure the password is long enough
			if(strlen($data['password']) < self::PW_LENGTH)
				return statusMessage('Password must be at least '.self::PW_LENGTH.' characters long.');
			
			// Make sure the password saved checkbox has been checked
			if(!isset($data['pass_saved']) || $data['pass_saved'] !== 'checked')
				return statusMessage('Please confirm that you\'ve saved your password to a safe location.');
			
			// Hash the password (encrypts the password for security purposes)
			$hashed_password = password_hash($data['password'], PASSWORD_BCRYPT, array('cost'=>10));
			
			// Insert the new user into the database
			$insert_id = $rs_query->insert('users', array('username'=>$data['username'], 'password'=>$hashed_password, 'email'=>$data['email'], 'registered'=>'NOW()', 'role'=>$data['role']));
			
			// Insert the user metadata into the database
			foreach($usermeta as $key=>$value)
				$rs_query->insert('usermeta', array('user'=>$insert_id, '_key'=>$key, 'value'=>$value));
			
			// Redirect to the 'Edit User' page
			redirect('users.php?id='.$insert_id.'&action=edit');
		} else {
			// Update the user in the database
			$rs_query->update('users', array('username'=>$data['username'], 'email'=>$data['email'], 'role'=>$data['role']), array('id'=>$id));
			
			// Update the user metadata in the database
			foreach($usermeta as $key=>$value)
				$rs_query->update('usermeta', array('value'=>$value), array('user'=>$id, '_key'=>$key));
			
			// Return a status message
			return statusMessage('User updated! <a href="users.php">Return to list</a>?', true);
		}
	}
	
	/**
	 * Check if the username already exists in the database.
	 * @since 1.2.0[a]
	 *
	 * @access private
	 * @param string $username
	 * @param int $id
	 * @return bool
	 */
	private function usernameExists($username, $id) {
		// Extend the Query class
		global $rs_query;
		
		if($id === 0) {
			// Fetch the number of times the username appears in the database
			$count = $rs_query->selectRow('users', 'COUNT(username)', array('username'=>$username));
		} else {
			// Fetch the number of times the username appears in the database (minus the current user)
			$count = $rs_query->selectRow('users', 'COUNT(username)', array('username'=>$username, 'id'=>array('<>', $id)));
		}
		
		// Return true if the count is greater than zero
		return $count > 0;
	}
	
	/**
	 * Fetch the user metadata.
	 * @since 1.2.2[a]
	 *
	 * @access private
	 * @param int $id
	 * @return array
	 */
	private function getUserMeta($id) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the user metadata from the database
		$usermeta = $rs_query->select('usermeta', array('_key', 'value'), array('user'=>$id));
		
		// Create an empty array to hold the metadata
		$meta = array();
		
		// Loop through the metadata
		foreach($usermeta as $metadata) {
			// Get the meta values
			$values = array_values($metadata);
			
			// Loop through the individual metadata entries
			for($i = 0; $i < count($metadata); $i += 2) {
				// Assign the metadata to the meta array
				$meta[$values[$i]] = $values[$i + 1];
			}
		}
		
		// Return the metadata
		return $meta;
	}
	
	/**
	 * Fetch the URL of a user's avatar.
	 * @since 1.2.4[a]
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getAvatar($id) {
		// Extend the Query class
		global $rs_query;
		
		$meta = $rs_query->selectRow('usermeta', 'value', array('id'=>$id));
		
		if(!empty($meta['value']))
			return trailingSlash(UPLOADS).$meta['value'];
		else
			return '//:0';
	}
	
	/**
	 * Fetch a user's role.
	 * @since 1.6.4[a]
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getRole($id) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the role from the database
		$role = $rs_query->selectRow('user_roles', 'name', array('id'=>$id));
		
		// Return the role's name
		return $role['name'];
	}
	
	/**
	 * Construct a list of roles.
	 * @since 1.6.4[a]
	 *
	 * @access private
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function getRoleList($id = 0) {
		// Extend the Query class
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch all roles from the database
		$roles = $rs_query->select('user_roles', '*', '', 'id');
		
		// Add each role to the list
		foreach($roles as $role)
			$list .= '<option value="'.$role['id'].'"'.($role['id'] === $id ? ' selected' : '').'>'.$role['name'].'</option>';
		
		// Return the list
		return $list;
	}
	
	/**
	 * Construct the 'Reset Password' form.
	 * @since 1.2.3[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function resetPassword($id) {
		// Check whether or not the user id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Users' page
			redirect('users.php');
		} else {
			// Validate the form data and return any messages
			$message = isset($_POST['submit']) ? $this->validatePasswordData($_POST, $id) : '';
			?>
			<div class="heading-wrap">
				<h1>Reset Password</h1>
				<?php echo $message; ?>
			</div>
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					echo formRow('Admin Password', array('tag'=>'input', 'type'=>'password', 'class'=>'text-input required invalid init', 'name'=>'admin_pass'));
					echo formRow('New User Password', array('tag'=>'input', 'id'=>'pw-input', 'class'=>'text-input required invalid init', 'name'=>'new_pass'), array('tag'=>'input', 'type'=>'button', 'id'=>'pw-btn', 'class'=>'button-input button', 'value'=>'Generate Password'), array('tag'=>'br'), array('tag'=>'input', 'type'=>'checkbox', 'id'=>'pw-chk', 'class'=>'checkbox-input', 'name'=>'pass_saved', 'value'=>'checked', 'label'=>array('id'=>'chk-label', 'class'=>'checkbox-label required invalid init', 'content'=>' <span>I have copied the password to a safe place.</span>')));
					echo formRow('New User Password (confirm)', array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'confirm_pass'));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Password'));
					?>
				</table>
			</form>
			<?php
		}
	}
	
	/**
	 * Validate the password form data.
	 * @since 1.2.3[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return null|string (null on $id == 0; string on $id != 0)
	 */
	private function validatePasswordData($data, $id = 0) {
		// Extend the Query class
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['new_pass']) || empty($data['confirm_pass']))
			return statusMessage('R');
		
		// Make sure the new and confirm password fields match
		if($data['new_pass'] !== $data['confirm_pass'])
			return statusMessage('New and confirm passwords do not match.');
		
		// Make sure the new and confirm passwords are long enough
		if(strlen($data['new_pass']) < self::PW_LENGTH || strlen($data['confirm_pass']) < self::PW_LENGTH)
			return statusMessage('New password must be at least '.self::PW_LENGTH.' characters long.');
		
		// Make sure the password saved checkbox has been checked
		if(!isset($data['pass_saved']) || $data['pass_saved'] !== 'checked')
			return statusMessage('Please confirm that you\'ve saved your password to a safe location.');
		
		if($id === 0) {
			// Make sure the current password field is not empty
			if(empty($data['current_pass']))
				return statusMessage('R');
		} else {
			// Make sure the admin password field is not empty
			if(empty($data['admin_pass']))
				return statusMessage('R');
			
			// Make sure the admin password is correctly entered
			if(!$this->verifyPassword($session_data, $data['admin_pass']))
				return statusMessage('Admin password is incorrect.');
			
			// Hash the password (encrypts the password for security purposes)
			$hashed_password = password_hash($data['new_pass'], PASSWORD_BCRYPT, array('cost'=>10));
			
			// Update the user's password in the database
			$rs_query->update('users', array('password'=>$hashed_password), array('id'=>$id));
			
			// Fetch the user's session from the database
			$user = $rs_query->selectRow('users', 'session', array('id'=>$id));
			
			// Check whether the user's session is null
			if(!is_null($user['session'])) {
				// Set the user's session to null in the database
				$rs_query->update('users', array('session'=>null), array('id'=>$id, 'session'=>$user['session']));
				
				// Fetch the session id
				session_id($user['session']);
				
				// Unset all of the session variables
				unset($_SESSION['id'], $_SESSION['username'], $_SESSION['avatar'], $_SESSION['role'], $_SESSION['session']);
				
				// Destroy the session
				session_destroy();
			}
			
			// Return a status message
			return statusMessage('Password updated! Return to <a href="users.php">Return to list</a>?', true);
		}
	}
	
	/**
	 * Verify that the current user's password matches what's in the database.
	 * @since 1.2.4[a]
	 *
	 * @access private
	 * @param array $session_data
	 * @param string $password
	 * @return bool
	 */
	private function verifyPassword($session_data, $password) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the password from the database
		$user = $rs_query->selectRow('users', 'password', array('id'=>$session_data['id'], 'session'=>$session_data['session']));
		
		// Return true if the password is valid
		return !empty($user['password']) && password_verify($password, $user['password']);
	}
}