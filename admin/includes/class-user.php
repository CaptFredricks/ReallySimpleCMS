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
	 *
	 * @access protected
	 * @var int
	 */
	protected const UN_LENGTH = 4;
	
	/**
	 * Set the minimum password length.
	 * @since 1.1.0[a]
	 *
	 * @access protected
	 * @var int
	 */
	protected const PW_LENGTH = 8;
	
	/**
	 * Construct a list of all users in the database.
	 * @since 1.2.1[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listUsers() {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Users</h1>
			<?php
			// Check whether the user has sufficient privileges to create users
			if(userHasPrivilege($session['role'], 'can_create_users')) {
				?>
				<a class="button" href="?action=create">Create New</a>
				<?php
			}
			
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The user was successfully deleted.', true);
			
			// Fetch the user entry count from the database
			$count = $rs_query->select('users', 'COUNT(*)');
			
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
					// Fetch the user's metadata from the database
					$meta = $this->getUserMeta($user['id']);
					
					echo tableRow(
						tableCell('<img class="avatar" src="'.getMediaSrc($meta['avatar']).'" width="32" height="32"><strong>'.$user['username'].'</strong><div class="actions"><a href="?id='.$user['id'].'&action=edit">Edit</a>'.($user['id'] !== $session['id'] ? ' &bull; '.($this->userHasContent($user['id']) ? '<a href="?id='.$user['id'].'&action=reassign_content">Delete</a>' : '<a class="modal-launch delete-item" href="?id='.$user['id'].'&action=delete" data-item="user">Delete</a>') : '').'</div>', 'username'),
						tableCell(empty($meta['first_name']) && empty($meta['last_name']) ? '&mdash;' : $meta['first_name'].' '.$meta['last_name'], 'full-name'),
						tableCell($user['email'], 'email'),
						tableCell(formatDate($user['registered'], 'd M Y @ g:i A'), 'registered'),
						tableCell($this->getRole($user['role']), 'role'),
						tableCell(is_null($user['session']) ? 'Offline' : 'Online', 'status'),
						tableCell(is_null($user['last_login']) ? 'Never' : formatDate($user['last_login'], 'd M Y @ g:i A'), 'last-login')
					);
				}
				
				// Display a notice if no users are found
				if(empty($users))
					echo tableRow(tableCell('There are no users to display.', '', count($table_header_cols)));
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
		
		// Include the delete modal
		include_once PATH.ADMIN.INC.'/modal-delete.php';
	}
	
	/**
	 * Construct the 'Create User' form.
	 * @since 1.1.2[a]
	 *
	 * @access public
	 * @return null
	 */
	public function createUser() {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1>Create User</h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					echo formRow(array('Username', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'username', 'value'=>($_POST['username'] ?? '')));
					echo formRow(array('Email', true), array('tag'=>'input', 'type'=>'email', 'class'=>'text-input required invalid init', 'name'=>'email', 'value'=>($_POST['email'] ?? '')));
					echo formRow('First Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'first_name', 'value'=>($_POST['first_name'] ?? '')));
					echo formRow('Last Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'last_name', 'value'=>($_POST['last_name'] ?? '')));
					echo formRow(array('Password', true), array('tag'=>'input', 'id'=>'password-field', 'class'=>'text-input required invalid init', 'name'=>'password'), array('tag'=>'input', 'type'=>'button', 'id'=>'password-gen', 'class'=>'button-input button', 'value'=>'Generate Password'), array('tag'=>'label', 'class'=>'checkbox-label hidden required invalid init', 'content'=>formTag('br', array('class'=>'spacer')).formTag('input', array('type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'pass_saved', 'value'=>'checked')).formTag('span', array('content'=>'I have copied the password to a safe place.'))));
					echo formRow('Avatar', array('tag'=>'div', 'class'=>'image-wrap', 'content'=>formTag('img', array('src'=>'//:0', 'data-field'=>'thumb')).formTag('span', array('class'=>'image-remove', 'title'=>'Remove', 'content'=>formTag('i', array('class'=>'fas fa-times'))))), array('tag'=>'input', 'type'=>'hidden', 'name'=>'avatar', 'value'=>($_POST['avatar'] ?? 0), 'data-field'=>'id'), array('tag'=>'input', 'type'=>'button', 'class'=>'button-input button modal-launch', 'value'=>'Choose Image', 'data-type'=>'image'));
					echo formRow('Role', array('tag'=>'select', 'class'=>'select-input', 'name'=>'role', 'content'=>$this->getRoleList((int)getSetting('default_user_role', false))));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create User'));
					?>
				</table>
			</form>
		</div>
		<?php
		// Include the upload modal
		include_once PATH.ADMIN.INC.'/modal-upload.php';
	}
	
	/**
	 * Construct the 'Edit User' form.
	 * @since 1.2.1[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function editUser($id) {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Check whether the user's id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Users' page
			redirect('users.php');
		} else {
			// Fetch the number of times the user appears in the database
			$count = $rs_query->selectRow('users', 'COUNT(*)', array('id'=>$id));
			
			// Check whether the count is zero
			if($count === 0) {
				// Redirect to the 'List Users' page
				redirect('users.php');
			} else {
				// Check whether the user is viewing their own page
				if($id === $session['id']) {
					// Redirect to the user's profile page
					redirect('profile.php');
				} else {
					// Validate the form data and return any messages
					$message = isset($_POST['submit']) ? $this->validateData($_POST, $id) : '';
					
					// Fetch the user from the database
					$user = $rs_query->selectRow('users', '*', array('id'=>$id));
					
					// Fetch the user's metadata from the database
					$meta = $this->getUserMeta($id);
					
					// Check whether the user has an avatar
					if(!empty($meta['avatar'])) {
						// Fetch the avatar's dimensions
						list($width, $height) = getimagesize(PATH.getMediaSrc($meta['avatar']));
					}
					?>
					<div class="heading-wrap">
						<h1>Edit User</h1>
						<?php echo $message; ?>
					</div>
					<div class="data-form-wrap clear">
						<form class="data-form" action="" method="post" autocomplete="off">
							<table class="form-table">
								<?php
								echo formRow(array('Username', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'username', 'value'=>$user['username']));
								echo formRow(array('Email', true), array('tag'=>'input', 'type'=>'email', 'class'=>'text-input required invalid init', 'name'=>'email', 'value'=>$user['email']));
								echo formRow('First Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'first_name', 'value'=>$meta['first_name']));
								echo formRow('Last Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'last_name', 'value'=>$meta['last_name']));
								echo formRow('Avatar', array('tag'=>'div', 'class'=>'image-wrap'.(!empty($meta['avatar']) ? ' visible' : ''), 'style'=>'width: '.($width ?? 0).'px;', 'content'=>formTag('img', array('src'=>getMediaSrc($meta['avatar']), 'data-field'=>'thumb')).formTag('span', array('class'=>'image-remove', 'title'=>'Remove', 'content'=>formTag('i', array('class'=>'fas fa-times'))))), array('tag'=>'input', 'type'=>'hidden', 'name'=>'avatar', 'value'=>$meta['avatar'], 'data-field'=>'id'), array('tag'=>'input', 'type'=>'button', 'class'=>'button-input button modal-launch', 'value'=>'Choose Image', 'data-type'=>'image'));
								echo formRow('Role', array('tag'=>'select', 'class'=>'select-input', 'name'=>'role', 'content'=>$this->getRoleList($user['role'])));
								echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
								echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update User'));
								?>
							</table>
						</form>
						<a class="reset-password button" href="?id=<?php echo $id; ?>&action=reset_password">Reset Password</a>
					</div>
					<?php
					// Include the upload modal
			        include_once PATH.ADMIN.INC.'/modal-upload.php';
				}
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
	public function deleteUser($id) {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Check whether the user's id is valid
		if(empty($id) || $id <= 0 || $id === $session['id']) {
			// Redirect to the 'List Users' page
			redirect('users.php');
		} else {
			// Delete the user from the database
			$rs_query->delete('users', array('id'=>$id));
			
			// Delete the user's metadata from the database
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
		// Extend the Query object
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
		
		// Make sure the email is not already being used
		if($this->emailExists($data['email'], $id))
			return statusMessage('That email is already taken by another user. Please choose another one.');
		
		// Create an array to hold the user's metadata
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
			
			// Add a metadata entry for the user's admin theme to the usermeta array
			$usermeta['theme'] = 'default';
			
			// Insert the user's metadata into the database
			foreach($usermeta as $key=>$value)
				$rs_query->insert('usermeta', array('user'=>$insert_id, '_key'=>$key, 'value'=>$value));
			
			// Redirect to the 'Edit User' page
			redirect('users.php?id='.$insert_id.'&action=edit');
		} else {
			// Update the user in the database
			$rs_query->update('users', array('username'=>$data['username'], 'email'=>$data['email'], 'role'=>$data['role']), array('id'=>$id));
			
			// Update the user's metadata in the database
			foreach($usermeta as $key=>$value)
				$rs_query->update('usermeta', array('value'=>$value), array('user'=>$id, '_key'=>$key));
			
			// Return a status message
			return statusMessage('User updated! <a href="users.php">Return to list</a>?', true);
		}
	}
	
	/**
	 * Check whether a username already exists in the database.
	 * @since 1.2.0[a]
	 *
	 * @access protected
	 * @param string $username
	 * @param int $id
	 * @return bool
	 */
	protected function usernameExists($username, $id) {
		// Extend the Query object
		global $rs_query;
		
		if($id === 0) {
			// Return true if the username appears in the database
			return $rs_query->selectRow('users', 'COUNT(username)', array('username'=>$username)) > 0;
		} else {
			// Return true if the username appears in the database (not counting the current user)
			return $rs_query->selectRow('users', 'COUNT(username)', array('username'=>$username, 'id'=>array('<>', $id))) > 0;
		}
	}
	
	/**
	 * Check whether an email already exists in the database.
	 * @since 2.0.6[a]
	 *
	 * @access protected
	 * @param string $email
	 * @param int $id
	 * @return bool
	 */
	protected function emailExists($email, $id) {
		// Extend the Query object
		global $rs_query;
		
		if($id === 0) {
			// Return true if the email appears in the database
			return $rs_query->selectRow('users', 'COUNT(email)', array('email'=>$email)) > 0;
		} else {
			// Return true if the email appears in the database (not counting the current user)
			return $rs_query->selectRow('users', 'COUNT(email)', array('email'=>$email, 'id'=>array('<>', $id))) > 0;
		}
	}
	
	/**
	 * Check whether a user has content assigned to them.
	 * @since 2.4.3[a]
	 *
	 * @access private
	 * @param int $id
	 * @return bool
	 */
	private function userHasContent($id) {
		// Extend the Query object
		global $rs_query;
		
		// Return true if the user has any content assigned to them
		return $rs_query->selectRow('posts', 'COUNT(author)', array('author'=>$id)) > 0;
	}
	
	/**
	 * Fetch a user's metadata.
	 * @since 1.2.2[a]
	 *
	 * @access protected
	 * @param int $id
	 * @return array
	 */
	protected function getUserMeta($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the user's metadata from the database
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
	 * Fetch a user's role.
	 * @since 1.6.4[a]
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getRole($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the user's role from the database and return it
		return $rs_query->selectField('user_roles', 'name', array('id'=>$id));
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
		// Extend the Query object
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
		// Check whether the user's id is valid
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
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						echo formRow('Admin Password', array('tag'=>'input', 'type'=>'password', 'class'=>'text-input required invalid init', 'name'=>'admin_pass'));
						echo formRow('New User Password', array('tag'=>'input', 'id'=>'password-field', 'class'=>'text-input required invalid init', 'name'=>'new_pass'), array('tag'=>'input', 'type'=>'button', 'id'=>'password-gen', 'class'=>'button-input button', 'value'=>'Generate Password'), array('tag'=>'label', 'class'=>'checkbox-label hidden required invalid init', 'content'=>formTag('br', array('class'=>'spacer')).formTag('input', array('type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'pass_saved', 'value'=>'checked')).formTag('span', array('content'=>'I have copied the password to a safe place.'))));
						echo formRow('New User Password (confirm)', array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'confirm_pass'));
						echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
						echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Password'));
						?>
					</table>
				</form>
			</div>
			<?php
		}
	}
	
	/**
	 * Validate the password form data.
	 * @since 1.2.3[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id
	 * @return string
	 */
	private function validatePasswordData($data, $id) {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Make sure no required fields are empty
		if(empty($data['admin_pass']) || empty($data['new_pass']) || empty($data['confirm_pass']))
			return statusMessage('R');
		
		// Make sure the admin password is correctly entered
		if(!$this->verifyPassword($data['admin_pass'], $session['id']))
			return statusMessage('Admin password is incorrect.');
		
		// Make sure the new and confirm password fields match
		if($data['new_pass'] !== $data['confirm_pass'])
			return statusMessage('New and confirm passwords do not match.');
		
		// Make sure the new and confirm passwords are long enough
		if(strlen($data['new_pass']) < self::PW_LENGTH || strlen($data['confirm_pass']) < self::PW_LENGTH)
			return statusMessage('New password must be at least '.self::PW_LENGTH.' characters long.');
		
		// Make sure the password saved checkbox has been checked
		if(!isset($data['pass_saved']) || $data['pass_saved'] !== 'checked')
			return statusMessage('Please confirm that you\'ve saved your password to a safe location.');
		
		// Hash the password (encrypts the password for security purposes)
		$hashed_password = password_hash($data['new_pass'], PASSWORD_BCRYPT, array('cost'=>10));
		
		// Update the user's password in the database
		$rs_query->update('users', array('password'=>$hashed_password), array('id'=>$id));
		
		// Fetch the user's session from the database
		$session = $rs_query->selectField('users', 'session', array('id'=>$id));
		
		// Check whether the user's session is null
		if(!is_null($session)) {
			// Set the user's session to null in the database
			$rs_query->update('users', array('session'=>null), array('id'=>$id, 'session'=>$session));
			
			// Check whether the cookie's value matches the session value and delete it if so
			if($_COOKIE['session'] === $session)
				setcookie('session', '', 1, '/');
		}
		
		// Return a status message
		return statusMessage('Password updated! Return to <a href="users.php">Return to list</a>?', true);
	}
	
	/**
	 * Verify that the current user's password matches what's in the database.
	 * @since 1.2.4[a]
	 *
	 * @access protected
	 * @param string $password
	 * @param int $id
	 * @return bool
	 */
	protected function verifyPassword($password, $id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the user's password from the database
		$db_password = $rs_query->selectField('users', 'password', array('id'=>$id));
		
		// Return true if the password is valid
		return !empty($db_password) && password_verify($password, $db_password);
	}
	
	/**
	 * Reassign a user's content to another user.
	 * @since 2.4.3[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function reassignContent($id) {
		// Extend the user's session data
		global $session;
		
		// Check whether the user's id is valid
		if(empty($id) || $id <= 0 || $id === $session['id']) {
			// Redirect to the 'List Users' page
			redirect('users.php');
		} else {
			// Validate the form data
			if(isset($_POST['submit'])) $this->validateReassignContentData($_POST, $id);
			?>
			<div class="heading-wrap">
				<h1>Reassign Content by <i><?php echo $this->getUsername($id); ?></i></h1>
			</div>
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						echo formRow('Reassign to User', array('tag'=>'select', 'class'=>'select-input', 'name'=>'reassign_to', 'content'=>$this->getUserList($id)));
						echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
						echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Submit'));
						?>
					</table>
				</form>
			</div>
			<?php
		}
	}
	
	/**
	 * Validate the 'Reassign Content' form data.
	 * @since 2.4.3[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id
	 * @return null
	 */
	private function validateReassignContentData($data, $id) {
		// Extend the Query object and the user's session data
		global $rs_query;
		
		// Reassign all posts to the new author
		$rs_query->update('posts', array('author'=>$data['reassign_to']), array('author'=>$id));
		
		// Delete the user from the database
		$rs_query->delete('users', array('id'=>$id));
		
		// Delete the user's metadata from the database
		$rs_query->delete('usermeta', array('user'=>$id));
		
		// Redirect to the 'List Users' page (with a success message)
		redirect('users.php?exit_status=success');
	}
	
	/**
	 * Fetch a username by a user's id.
	 * @since 2.4.3[a]
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getUsername($id) {
		// Extend the Query object
		global $rs_query;
		
		// Return the username
		return $rs_query->selectField('users', 'username', array('id'=>$id));
	}
	
	/**
	 * Construct a list of users.
	 * @since 2.4.3[a]
	 *
	 * @access private
	 * @param int $exclude
	 * @return string
	 */
	private function getUserList($id) {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch all users from the database
		$users = $rs_query->select('users', array('id', 'username'), array('id'=>array('<>', $id)), 'username');
		
		// Add each user to the list
		foreach($users as $user)
			$list .= '<option value="'.$user['id'].'">'.$user['username'].'</option>';
		
		// Return the list
		return $list;
	}
}