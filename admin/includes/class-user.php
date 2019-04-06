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
		$page = isset($_GET['page']) ? paginate($_GET['page']) : paginate();
		?>
		<h1>Users</h1>
		<a class="button" href="?action=create">Create User</a>
		<?php
		// Display any status messages
		echo isset($_GET['exit_status']) && $_GET['exit_status'] === 'success' ? statusMessage('User was successfully deleted.', true) : '';
		
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
		<table class="data-table">
			<thead>
				<?php
				// Construct the table header
				echo tableHeaderRow(array('Username', 'Full Name', 'Email', 'Registered', 'Role', 'Status', 'Last Login'));
				?>
			</thead>
			<tbody>
				<?php
				// Fetch users from the database
				$users = $rs_query->select('users', '*', '', 'username', 'ASC', array($page['start'], $page['per_page']));
		
				// Loop through the users
				foreach($users as $user) {
					// Fetch the user metadata
					$meta = $this->getUserMeta($user['id']);
					
					// Construct the current row
					echo tableRow(
						tableCell('<img class="avatar" src="'.(!empty($meta['avatar']) ? '' : '').'" width="32" height="32"><strong>'.$user['username'].'</strong><div class="actions"><a href="?id='.$user['id'].'&action=edit">Edit</a> &bull; <a class="delete-item" href="javascript:void(0)" rel="'.$user['id'].'">Delete</a></div>', 'username'),
						tableCell($meta['first_name'].' '.$meta['last_name'], 'full-name'),
						tableCell($user['email'], 'email'),
						tableCell(formatDate($user['registered'], 'd M Y @ g:i A'), 'registered'),
						tableCell('', 'role'),
						tableCell((!empty($user['session']) ? 'Online' : 'Offline'), 'status'),
						tableCell(($user['last_login'] === null ? 'Never' : formatDate($user['last_login'], 'd M Y @ g:i A')), 'last-login')
					);
				}
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
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		
		$content = '<h1 id="admin-heading">Create User</h1>';
		$content .= $message;
		$content .= '<form action="" method="post" autocomplete="off"><table class="form-table">';
		$content .= formRow(array('Username', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'username', 'value'=>($_POST['username'] ?? '')));
		$content .= formRow(array('Email', true), array('tag'=>'input', 'type'=>'email', 'class'=>'text-input required invalid init', 'name'=>'email', 'value'=>($_POST['email'] ?? '')));
		$content .= formRow('First Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'first_name', 'value'=>($_POST['first_name'] ?? '')));
		$content .= formRow('Last Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'last_name', 'value'=>($_POST['last_name'] ?? '')));
		$content .= formRow(array('Password', true), array('tag'=>'input', 'id'=>'pw-input', 'class'=>'text-input required invalid init', 'name'=>'password'), array('tag'=>'input', 'type'=>'button', 'id'=>'pw-btn', 'class'=>'button-input', 'value'=>'Generate Password'), array('tag'=>'br'), array('tag'=>'input', 'type'=>'checkbox', 'id'=>'pw-chk', 'class'=>'checkbox-input', 'name'=>'pass_saved', 'value'=>'checked', 'label'=>array('id'=>'chk-label', 'class'=>'checkbox-label required invalid init', 'content'=>'I have copied the password to a safe place.')));
		$content .= formRow('Avatar', array('tag'=>'input', 'type'=>'hidden', 'id'=>'img-input', 'name'=>'avatar', 'value'=>($_POST['avatar'] ?? '')), array('tag'=>'input', 'type'=>'button', 'id'=>'img-choose', 'class'=>'button-input', 'value'=>'Choose Image'));
		$content .= formRow('Role', array('tag'=>'select', 'class'=>'select-input', 'name'=>'role', 'content'=>'<option></option>'));
		$content .= formRow('', array('tag'=>'hr', 'class'=>'divider'));
		$content .= formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input', 'name'=>'submit', 'value'=>'Create User'));
		$content .= '</table></form>';
		
		echo $content;
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
		
		if(empty($id) || $id <= 0) {
			header('Location: users.php');
		} else {
			$message = isset($_POST['submit']) ? $this->validateData($_POST, $id) : '';
			
			$user = $rs_query->selectRow('users', '*', array('id'=>$id));

			$meta = $this->getUserMeta($id);
			
			$content = '<h1 id="admin-heading">Edit User</h1>';
			$content .= $message;
			$content .= '<form action="" method="post" autocomplete="off"><table class="form-table">';
			$content .= formRow(array('Username', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'username', 'value'=>$user['username']));
			$content .= formRow(array('Email', true), array('tag'=>'input', 'type'=>'email', 'class'=>'text-input required invalid init', 'name'=>'email', 'value'=>$user['email']));
			$content .= formRow('First Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'first_name', 'value'=>$meta['first_name']));
			$content .= formRow('Last Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'last_name', 'value'=>$meta['last_name']));
			$content .= formRow('Avatar', array('tag'=>'img', 'src'=>$this->getAvatar($meta['avatar']), 'width'=>150), array('tag'=>'br'), array('tag'=>'input', 'type'=>'hidden', 'id'=>'img-input', 'name'=>'avatar', 'value'=>$meta['avatar']), array('tag'=>'input', 'type'=>'button', 'id'=>'img-choose', 'class'=>'button-input', 'value'=>'Choose Image'));
			$content .= formRow('Role', array('tag'=>'select', 'class'=>'select-input', 'name'=>'role', 'content'=>'<option></option>'));
			$content .= formRow('', array('tag'=>'hr', 'class'=>'divider'));
			$content .= formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input', 'name'=>'submit', 'value'=>'Update User'));
			$content .= '</table></form>';
			$content .= '<a href="?id='.$id.'&action=reset_password">Reset Password</a>';
			
			echo $content;
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
		
		if(empty($id) || $id <= 0) {
			header('Location: users.php');
		} else {
			$rs_query->delete('users', array('id'=>$id));
			$rs_query->delete('usermeta', array('user'=>$id));
			
			header('Location: users.php?exit_status=success');
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
		
		if(empty($data['username']) || empty($data['email']))
			return statusMessage('R');
		elseif(strlen($data['username']) < self::UN_LENGTH)
			return statusMessage('Username must be at least '.self::UN_LENGTH.' characters long.');
		
		if($id === 0) {
			if(empty($data['password']))
				return statusMessage('R');
			elseif($this->usernameExists($data['username']))
				return statusMessage('That username has already been taken. Please choose another one.');
			elseif(strlen($data['password']) < self::PW_LENGTH)
				return statusMessage('Password must be at least '.self::PW_LENGTH.' characters long.');
			elseif(!isset($data['pass_saved']) || $data['pass_saved'] !== 'checked')
				return statusMessage('Please confirm that you\'ve saved your password to a safe location.');
			
			$hashed_password = password_hash($data['password'], PASSWORD_BCRYPT, array('cost'=>10));
			
			$insert_id = $rs_query->insert('users', array('username'=>$data['username'], 'password'=>$hashed_password, 'email'=>$data['email'], 'registered'=>'NOW()')); //role
			
			$meta = array('first_name'=>$data['first_name'], 'last_name'=>$data['last_name'], 'avatar'=>$data['avatar']);
			
			foreach($meta as $key => $value)
				$rs_query->insert('usermeta', array('_key'=>$key, 'value'=>$value, 'user'=>$insert_id));
			
			header('Location: users.php?id='.$insert_id.'&action=edit');
		} else {
			if($this->usernameExists($data['username'], $id))
				return statusMessage('That username has already been taken. Please choose another one.');
			
			$rs_query->update('users', array('username'=>$data['username'], 'email'=>$data['email']), array('id'=>$id)); //role
			
			$meta = array('first_name'=>$data['first_name'], 'last_name'=>$data['last_name'], 'avatar'=>$data['avatar']);
			
			foreach($meta as $key => $value)
				$rs_query->update('usermeta', array('value'=>$value), array('_key'=>$key, 'user'=>$id));
			
			return statusMessage('User updated! <a href="users.php">Return to list</a>?', true);
		}
	}
	
	/**
	 * Check if the username already exists in the database.
	 * @since 1.2.0[a]
	 *
	 * @access private
	 * @param string $username
	 * @param int $id (optional; default: 0)
	 * @return bool
	 */
	private function usernameExists($username, $id = 0) {
		// Extend the Query class
		global $rs_query;
		
		if($id === 0)
			$count = $rs_query->selectRow('users', 'COUNT(username)', array('username'=>$username));
		else
			$count = $rs_query->selectRow('users', 'COUNT(username)', array('username'=>$username, 'id'=>array('<>', $id)));
		
		return ($count > 0);
	}
	
	/**
	 * Retrieve user metadata.
	 * @since 1.2.2[a]
	 *
	 * @access private
	 * @param int $id
	 * @return array
	 */
	private function getUserMeta($id) {
		// Extend the Query class
		global $rs_query;
		
		$usermeta = $rs_query->select('usermeta', array('_key', 'value'), array('user'=>$id));
		$meta = array();
		
		foreach($usermeta as $metadata) {
			$values = array_values($metadata);
			
			for($i = 0; $i < count($metadata); $i += 2)
				$meta[$values[$i]] = $values[$i + 1];
		}
		
		return $meta;
	}
	
	/**
	 * Retrieve URL of a user's avatar.
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
			return UPL_DIR.$meta['value'];
		else
			return '//:0';
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
		if(empty($id) || $id <= 0) {
			header('Location: users.php');
		} else {
			$message = isset($_POST['submit']) ? $this->validatePasswordData($_POST, $id) : '';
			
			$content = '<h1 id="admin-heading">Reset Password</h1>';
			$content .= $message;
			$content .= '<form action="" method="post" autocomplete="off"><table class="form-table">';
			$content .= formRow('Admin Password', array('tag'=>'input', 'type'=>'password', 'class'=>'text-input required invalid init', 'name'=>'admin_pass'));
			$content .= formRow('New User Password', array('tag'=>'input', 'id'=>'pw-input', 'class'=>'text-input required invalid init', 'name'=>'new_pass'), array('tag'=>'input', 'type'=>'button', 'id'=>'pw-btn', 'class'=>'button-input', 'value'=>'Generate Password'), array('tag'=>'br'), array('tag'=>'input', 'type'=>'checkbox', 'id'=>'pw-chk', 'class'=>'checkbox-input', 'name'=>'pass_saved', 'value'=>'checked', 'label'=>array('id'=>'chk-label', 'class'=>'checkbox-label required invalid init', 'content'=>'I have copied the password to a safe place.')));
			$content .= formRow('New User Password (confirm)', array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'confirm_pass'));
			$content .= formRow('', array('tag'=>'hr', 'class'=>'divider'));
			$content .= formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input', 'name'=>'submit', 'value'=>'Update Password'));
			$content .= '</table></form>';
			
			echo $content;
		}
	}
	
	/**
	 * Validate the password form data.
	 * @since 1.2.3[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return
	 */
	private function validatePasswordData($data, $id = 0) {
		// Extend the Query class
		global $rs_query;
		
		if(empty($data['new_pass']) || empty($data['confirm_pass']))
			return statusMessage('R');
		elseif($data['new_pass'] !== $data['confirm_pass'])
			return statusMessage('New and confirm passwords do not match.');
		elseif(strlen($data['new_pass']) < self::PW_LENGTH || strlen($data['confirm_pass']) < self::PW_LENGTH)
			return statusMessage('New password must be at least '.self::PW_LENGTH.' characters long.');
		elseif(!isset($data['pass_saved']) || $data['pass_saved'] !== 'checked')
			return statusMessage('Please confirm that you\'ve saved your password to a safe location.');
		
		if($id === 0) {
			if(empty($data['current_pass']))
				return statusMessage('R');
		} else {
			if(empty($data['admin_pass']))
				return statusMessage('R');
			elseif(!$this->verifyPassword($session_data, $data['admin_pass']))
				return statusMessage('Admin password is incorrect.');
			
			$hashed_password = password_hash($data['new_pass'], PASSWORD_BCRYPT, array('cost'=>10));
			
			$rs_query->update('users', array('password'=>$hashed_password), array('id'=>$id));
			
			$user = $rs_query->selectRow('users', 'session', array('id'=>$id));
			
			$rs_query->update('users', array('session'=>null), array('id'=>$id, 'session'=>$user['session']));
			
			if($user['session'] != null) {
				session_id($user['session']);
				unset($_SESSION['id'], $_SESSION['username'], $_SESSION['avatar'], $_SESSION['role'], $_SESSION['session']);
				session_destroy();
			}
			
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
		
		$user = $rs_query->selectRow('users', 'password', array('id'=>$session_data['id'], 'session'=>$session_data['session']));
		
		return !empty($user['password']) && password_verify($password, $user['password']);
	}
}