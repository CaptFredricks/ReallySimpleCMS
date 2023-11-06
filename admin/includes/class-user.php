<?php
/**
 * Admin class used to implement the User object.
 * @since 1.1.0[a]
 *
 * Users have various privileges on the website not afforded to visitors, depending on their access level.
 * Users can be created, modified, and deleted.
 */
class User implements AdminInterface {
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
	 * The currently queried user's id.
	 * @since 1.1.1[b]
	 *
	 * @access protected
	 * @var int
	 */
	protected $id;
	
	/**
	 * The currently queried user's username.
	 * @since 1.1.1[b]
	 *
	 * @access protected
	 * @var string
	 */
	protected $username;
	
	/**
	 * The currently queried user's email.
	 * @since 1.1.1[b]
	 *
	 * @access protected
	 * @var string
	 */
	protected $email;
	
	/**
	 * The currently queried user's role.
	 * @since 1.1.1[b]
	 *
	 * @access protected
	 * @var int
	 */
	protected $role;
	
	/**
	 * Class constructor.
	 * @since 1.1.1[b]
	 *
	 * @access public
	 * @param int $id (optional) -- The user's id.
	 */
	public function __construct(int $id = 0) {
		global $rs_query;
		
		$cols = array_keys(get_object_vars($this));
		
		if($id !== 0) {
			$user = $rs_query->selectRow('users', $cols, array('id' => $id));
			
			// Set the class variable values
			foreach($user as $key => $value) $this->$key = $user[$key];
		}
	}
	
	/**
	 * Construct a list of all users in the database.
	 * @since 1.2.1[a]
	 *
	 * @access public
	 */
	public function listRecords(): void {
		global $rs_query, $session;
		
		// Query vars
		$status = $_GET['status'] ?? 'all';
		$search = $_GET['search'] ?? null;
		$paged = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Users</h1>
			<?php
			// Check whether the user has sufficient privileges to create users and create an action link if so
			if(userHasPrivilege('can_create_users'))
				echo actionLink('create', array('classes' => 'button', 'caption' => 'Create New'));
			
			recordSearch(array(
				'status' => $status
			));
			adminInfo();
			?>
			<hr>
			<?php
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo exitNotice('The user was successfully deleted.');
			?>
			<ul class="status-nav">
				<?php
				$keys = array('all', 'online', 'offline');
				$count = array();
				
				foreach($keys as $key) {
					if($key === 'all') {
						if(!is_null($search) && $key === $status)
							$count[$key] = $this->getUserCount('', $search);
						else
							$count[$key] = $this->getUserCount();
					} else {
						if(!is_null($search) && $key === $status)
							$count[$key] = $this->getUserCount($key, $search);
						else
							$count[$key] = $this->getUserCount($key);
					}
				}
				
				foreach($count as $key => $value) {
					?>
					<li>
						<a href="<?php echo ADMIN_URI . ($key === 'all' ? '' : '?status=' . $key);
						?>"><?php echo ucfirst($key); ?> <span class="count">(<?php echo $value; ?>)</span></a>
					</li>
					<?php
					if($key !== array_key_last($count)) {
						?> &bull; <?php
					}
				}
				?>
			</ul>
			<?php
			$paged['count'] = ceil($count[$status] / $paged['per_page']);
			?>
			<div class="entry-count">
				<?php echo $count[$status] . ' ' . ($count[$status] === 1 ? 'entry' : 'entries'); ?>
			</div>
		</div>
		<table class="data-table has-bulk-select">
			<thead>
				<?php
				$table_header_cols = array(
					tag('input', array(
						'type' => 'checkbox',
						'class' => 'checkbox bulk-selector'
					)),
					'Username',
					'Full Name',
					'Email',
					'Registered',
					'Role',
					'Status',
					'Last Login'
				);
				
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				switch($status) {
					case 'all':
						if(!is_null($search)) {
							$users = $rs_query->select('users', '*', array(
								'username' => array('LIKE', '%' . $search . '%')
							), 'username', 'ASC', array(
								$paged['start'],
								$paged['per_page']
							));
						} else {
							$users = $rs_query->select('users', '*',
								array(), 'username', 'ASC', array(
									$paged['start'],
									$paged['per_page']
								)
							);
						}
						break;
					case 'online':
						if(!is_null($search)) {
							$users = $rs_query->select('users', '*', array(
								'username' => array('LIKE', '%' . $search . '%'),
								'session' => array('IS NOT NULL')
							), 'username', 'ASC', array(
								$paged['start'],
								$paged['per_page']
							));
						} else {
							$users = $rs_query->select('users', '*', array(
								'session' => array('IS NOT NULL')
							), 'username', 'ASC', array(
								$paged['start'],
								$paged['per_page']
							));
						}
						break;
					case 'offline':
						if(!is_null($search)) {
							$users = $rs_query->select('users', '*', array(
								'username' => array('LIKE', '%' . $search . '%'),
								'session' => array('IS NULL')
							), 'username', 'ASC', array(
								$paged['start'],
								$paged['per_page']
							));
						} else {
							$users = $rs_query->select('users', '*', array(
								'session' => array('IS NULL')
							), 'username', 'ASC', array(
								$paged['start'],
								$paged['per_page']
							));
						}
						break;
				}
				
				foreach($users as $user) {
					$meta = $this->getUserMeta($user['id']);
					
					$actions = array(
						// Edit
						userHasPrivilege('can_edit_users'
							) || $user['id'] === $session['id'] ? ($user['id'] === $session['id'] ? '<a href="' .
								ADMIN . '/profile.php">Edit</a>' : actionLink('edit', array(
								'caption' => 'Edit',
								'id' => $user['id']
							))) : null,
						// Delete
						userHasPrivilege('can_delete_users'
							) && $user['id'] !== $session['id'] ? ($this->userHasContent($user['id']) ? actionLink('reassign_content', array(
								'caption' => 'Delete', 'id' => $user['id']
							)) : actionLink('delete', array(
								'classes' => 'modal-launch delete-item',
								'data_item' => 'user',
								'caption' => 'Delete',
								'id' => $user['id']
							))) : null
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						// Bulk select
						tdCell(tag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox',
							'value' => $user['id']
						)), 'bulk-select'),
						// Username
						tdCell(getMedia($meta['avatar'], array(
							'class' => 'avatar',
							'width' => 32,
							'height' => 32
						)) . '<strong>' . $user['username'] . '</strong><div class="actions">' .
							implode(' &bull; ', $actions) . '</div>', 'username'),
						// Full name
						tdCell(empty($meta['first_name']) && empty($meta['last_name']) ? '&mdash;' :
							$meta['first_name'] . ' ' . $meta['last_name'], 'full-name'),
						// Email
						tdCell($user['email'], 'email'),
						// Registered
						tdCell(formatDate($user['registered'], 'd M Y @ g:i A'), 'registered'),
						// Role
						tdCell($this->getRole($user['role']), 'role'),
						// Status
						tdCell(is_null($user['session']) ? 'Offline' : 'Online', 'status'),
						// Last login
						tdCell(is_null($user['last_login']) ? 'Never' : formatDate($user['last_login'], 'd M Y @ g:i A'), 'last-login')
					);
				}
				
				if(empty($users))
					echo tableRow(tdCell('There are no users to display.', '', count($table_header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($table_header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Bulk actions
		if(!empty($users)) $this->bulkActions();
		
		// Set up page navigation
		echo pagerNav($paged['current'], $paged['count']);
		
		include_once PATH . ADMIN . INC . '/modal-delete.php';
	}
	
	/**
	 * Create a new user.
	 * @since 1.1.2[a]
	 *
	 * @access public
	 */
	public function createRecord(): void {
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
					// Username
					echo formRow(array('Username', true), array(
						'tag' => 'input',
						'class' => 'text-input required invalid init',
						'name' => 'username',
						'value' => ($_POST['username'] ?? '')
					));
					
					// Email
					echo formRow(array('Email', true), array(
						'tag' => 'input',
						'type' => 'email',
						'class' => 'text-input required invalid init',
						'name' => 'email',
						'value' => ($_POST['email'] ?? '')
					));
					
					// First name
					echo formRow('First Name', array(
						'tag' => 'input',
						'class' => 'text-input',
						'name' => 'first_name',
						'value' => ($_POST['first_name'] ?? '')
					));
					
					// Last name
					echo formRow('Last Name', array(
						'tag' => 'input',
						'class' => 'text-input',
						'name' => 'last_name',
						'value' => ($_POST['last_name'] ?? '')
					));
					
					// Password
					echo formRow(array('Password', true), array(
						'tag' => 'input',
						'id' => 'password-field',
						'class' => 'text-input required invalid init',
						'name' => 'password'
					), array(
						'tag' => 'input',
						'type' => 'button',
						'id' => 'password-gen',
						'class' => 'button-input button',
						'value' => 'Generate Password'
					), array(
						'tag' => 'label',
						'class' => 'checkbox-label hidden required invalid init',
						'content' => tag('br', array('class' => 'spacer')) . tag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox-input',
							'name' => 'pass_saved',
							'value' => 1
						)) . tag('span', array('content' => 'I have copied the password to a safe place.'))
					));
					
					// Avatar
					echo formRow('Avatar', array(
						'tag' => 'div',
						'class' => 'image-wrap',
						'content' => tag('img', array('src' => '//:0', 'data-field' => 'thumb')) . tag('span', array(
							'class' => 'image-remove',
							'title' => 'Remove',
							'content' => tag('i', array('class' => 'fa-solid fa-xmark'))
						))
					), array(
						'tag' => 'input',
						'type' => 'hidden',
						'name' => 'avatar',
						'value' => ($_POST['avatar'] ?? 0),
						'data-field' => 'id'
					), array(
						'tag' => 'input',
						'type' => 'button',
						'class' => 'button-input button modal-launch',
						'value' => 'Choose Image',
						'data-type' => 'image'
					));
					
					// Role
					echo formRow('Role', array(
						'tag' => 'select',
						'class' => 'select-input',
						'name' => 'role',
						'content' => $this->getRoleList((int)getSetting('default_user_role'))
					));
					
					// Separator
					echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => 'Create User'
					));
					?>
				</table>
			</form>
		</div>
		<?php
		include_once PATH . ADMIN . INC . '/modal-upload.php';
	}
	
	/**
	 * Edit an existing user.
	 * @since 1.2.1[a]
	 *
	 * @access public
	 */
	public function editRecord(): void {
		global $rs_query, $session;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			// Check whether the user is viewing their own page
			if($this->id === $session['id']) {
				redirect('profile.php');
			} else {
				// Validate the form data and return any messages
				$message = isset($_POST['submit']) ? $this->validateData($_POST, $this->id) : '';
				
				$meta = $this->getUserMeta($this->id);
				
				if(!empty($meta['avatar']))
					list($width, $height) = getimagesize(PATH . getMediaSrc($meta['avatar']));
				?>
				<div class="heading-wrap">
					<h1>Edit User</h1>
					<?php echo $message; ?>
				</div>
				<div class="data-form-wrap clear">
					<form class="data-form" action="" method="post" autocomplete="off">
						<table class="form-table">
							<?php
							// Username
							echo formRow(array('Username', true), array(
								'tag' => 'input',
								'class' => 'text-input required invalid init',
								'name' => 'username',
								'value' => $this->username
							));
							
							// Email
							echo formRow(array('Email', true), array(
								'tag' => 'input',
								'type' => 'email',
								'class' => 'text-input required invalid init',
								'name' => 'email',
								'value' => $this->email
							));
							
							// First name
							echo formRow('First Name', array(
								'tag' => 'input',
								'class' => 'text-input',
								'name' => 'first_name',
								'value' => $meta['first_name']
							));
							
							// Last name
							echo formRow('Last Name', array(
								'tag' => 'input',
								'class' => 'text-input',
								'name' => 'last_name',
								'value' => $meta['last_name']
							));
							
							// Avatar
							echo formRow('Avatar', array(
								'tag' => 'div',
								'class' => 'image-wrap' . (!empty($meta['avatar']) ? ' visible' : ''),
								'style' => 'width: ' . ($width ?? 0) . 'px;',
								'content' => getMedia($meta['avatar'], array(
									'data-field' => 'thumb'
								)) . tag('span', array(
									'class' => 'image-remove',
									'title' => 'Remove',
									'content' => tag('i', array('class' => 'fa-solid fa-xmark'))
								))
							), array(
								'tag' => 'input',
								'type' => 'hidden',
								'name' => 'avatar',
								'value' => $meta['avatar'],
								'data-field' => 'id'
							), array(
								'tag' => 'input',
								'type' => 'button',
								'class' => 'button-input button modal-launch',
								'value' => 'Choose Image',
								'data-type' => 'image'
							));
							
							// Role
							echo formRow('Role', array(
								'tag' => 'select',
								'class' => 'select-input',
								'name' => 'role',
								'content' => $this->getRoleList($this->role)
							));
							
							// Separator
							echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
							
							// Submit button
							echo formRow('', array(
								'tag' => 'input',
								'type' => 'submit',
								'class' => 'submit-input button',
								'name' => 'submit',
								'value' => 'Update User'
							));
							?>
						</table>
					</form>
					<?php echo actionLink('reset_password', array(
						'classes' => 'reset-password button',
						'caption' => 'Reset Password',
						'id' => $this->id
					)); ?>
				</div>
				<?php
				include_once PATH . ADMIN . INC . '/modal-upload.php';
			}
		}
	}
	
	/**
	 * Update a user's role.
	 * @since 1.3.2[b]
	 *
	 * @access public
	 * @param int $role -- The user's role.
	 * @param int $id -- The user's id.
	 */
	public function updateUserRole(int $role, int $id): void {
		global $rs_query, $session;
		
		$this->id = $id;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			if($this->id !== $session['id'])
				$rs_query->update('users', array('role' => $role), array('id' => $this->id));
		}
	}
	
	/**
	 * Delete an existing user.
	 * @since 1.2.3[a]
	 *
	 * @access public
	 */
	public function deleteRecord(): void {
		global $rs_query, $session;
		
		if(empty($this->id) || $this->id <= 0 || $this->id === $session['id']) {
			redirect(ADMIN_URI);
		} else {
			$rs_query->delete('users', array('id' => $this->id));
			$rs_query->delete('usermeta', array('user' => $this->id));
			
			redirect(ADMIN_URI . '?exit_status=success');
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 1.2.0[a]
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @param int $id (optional) -- The user's id.
	 * @return string
	 */
	private function validateData(array $data, int $id = 0): string {
		global $rs_query;
		
		if(empty($data['username']) || empty($data['email']))
			return exitNotice('REQ', -1);
		
		if(strlen($data['username']) < self::UN_LENGTH)
			return exitNotice('Username must be at least ' . self::UN_LENGTH . ' characters long.', -1);
		
		$username = sanitize($data['username'], '/[^a-z0-9_\.]/i', false);
		
		if($this->usernameExists($username, $id))
			return exitNotice('That username has already been taken. Please choose another one.', -1);
		
		if($this->emailExists($data['email'], $id))
			return exitNotice('That email is already taken by another user. Please choose another one.', -1);
		
		$usermeta = array(
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'avatar' => $data['avatar']
		);
		
		if($id === 0) {
			// New user
			if(empty($data['password']))
				return exitNotice('REQ', -1);
			
			if(strlen($data['password']) < self::PW_LENGTH)
				return exitNotice('Password must be at least ' . self::PW_LENGTH . ' characters long.', -1);
			
			if(!isset($data['pass_saved']) || $data['pass_saved'] != 1)
				return exitNotice('Please confirm that you\'ve saved your password to a safe location.', -1);
			
			$hashed_password = password_hash($data['password'], PASSWORD_BCRYPT, array('cost' => 10));
			
			$insert_id = $rs_query->insert('users', array(
				'username' => $username,
				'password' => $hashed_password,
				'email' => $data['email'],
				'registered' => 'NOW()',
				'role' => $data['role']
			));
			
			$usermeta['theme'] = 'default';
			
			foreach($usermeta as $key => $value) {
				$rs_query->insert('usermeta', array(
					'user' => $insert_id,
					'_key' => $key,
					'value' => $value
				));
			}
			
			redirect(ADMIN_URI . '?id=' . $insert_id . '&action=edit');
		} else {
			// Existing user
			$rs_query->update('users', array(
				'username' => $username,
				'email' => $data['email'],
				'role' => $data['role']
			), array('id' => $id));
			
			foreach($usermeta as $key => $value)
				$rs_query->update('usermeta', array('value' => $value), array('user' => $id, '_key' => $key));
			
			// Update the class variables
			foreach($data as $key => $value) $this->$key = $value;
			
			return exitNotice('User updated! <a href="' . ADMIN_URI . '">Return to list</a>?');
		}
	}
	
	/**
	 * Check whether a username already exists in the database.
	 * @since 1.2.0[a]
	 *
	 * @access protected
	 * @param string $username -- The username.
	 * @param int $id -- The user's id.
	 * @return bool
	 */
	protected function usernameExists(string $username, int $id): bool {
		global $rs_query;
		
		if($id === 0) {
			return $rs_query->selectRow('users', 'COUNT(username)', array(
				'username' => $username
			)) > 0;
		} else {
			return $rs_query->selectRow('users', 'COUNT(username)', array(
				'username' => $username,
				'id' => array('<>', $id)
			)) > 0;
		}
	}
	
	/**
	 * Check whether an email already exists in the database.
	 * @since 2.0.6[a]
	 *
	 * @access protected
	 * @param string $email -- The user's email.
	 * @param int $id -- The user's id.
	 * @return bool
	 */
	protected function emailExists(string $email, int $id): bool {
		global $rs_query;
		
		if($id === 0) {
			return $rs_query->selectRow('users', 'COUNT(email)', array('email' => $email)) > 0;
		} else {
			return $rs_query->selectRow('users', 'COUNT(email)', array(
				'email' => $email,
				'id' => array('<>', $id)
			)) > 0;
		}
	}
	
	/**
	 * Check whether a user has content assigned to them.
	 * @since 2.4.3[a]
	 *
	 * @access private
	 * @param int $id -- The user's id.
	 * @return bool
	 */
	private function userHasContent(int $id): bool {
		global $rs_query;
		
		return $rs_query->selectRow('posts', 'COUNT(author)', array('author' => $id)) > 0;
	}
	
	/**
	 * Fetch a user's metadata.
	 * @since 1.2.2[a]
	 *
	 * @access protected
	 * @param int $id -- The user's id.
	 * @return array
	 */
	protected function getUserMeta(int $id): array {
		global $rs_query;
		
		$usermeta = $rs_query->select('usermeta', array('_key', 'value'), array('user' => $id));
		
		$meta = array();
		
		foreach($usermeta as $metadata) {
			$values = array_values($metadata);
			
			for($i = 0; $i < count($metadata); $i += 2)
				$meta[$values[$i]] = $values[$i + 1];
		}
		
		return $meta;
	}
	
	/**
	 * Fetch a user's role.
	 * @since 1.7.0[a]
	 *
	 * @access private
	 * @param int $id -- The user's id.
	 * @return string
	 */
	private function getRole(int $id): string {
		global $rs_query;
		
		return $rs_query->selectField('user_roles', 'name', array('id' => $id));
	}
	
	/**
	 * Construct a list of roles.
	 * @since 1.7.0[a]
	 *
	 * @access private
	 * @param int $id (optional) -- The user's id.
	 * @return string
	 */
	private function getRoleList(int $id = 0): string {
		global $rs_query;
		
		$list = '';
		
		$roles = $rs_query->select('user_roles', '*', array(), 'id');
		
		foreach($roles as $role) {
			$list .= tag('option', array(
				'value' => $role['id'],
				'selected' => ($role['id'] === $id),
				'content' => $role['name']
			));
		}
		
		return $list;
	}
	
	/**
	 * Construct the "Reset Password" form.
	 * @since 1.2.3[a]
	 *
	 * @access public
	 */
	public function resetPassword(): void {
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			// Validate the form data and return any messages
			$message = isset($_POST['submit']) ? $this->validatePasswordData($_POST, $this->id) : '';
			?>
			<div class="heading-wrap">
				<h1>Reset Password</h1>
				<?php echo $message; ?>
			</div>
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						// Admin password
						echo formRow('Admin Password', array(
							'tag' => 'input',
							'type' => 'password',
							'class' => 'text-input required invalid init',
							'name' => 'admin_pass'
						));
						
						// New user password
						echo formRow('New User Password', array(
							'tag' => 'input',
							'id' => 'password-field',
							'class' => 'text-input required invalid init',
							'name' => 'new_pass'
						), array(
							'tag' => 'input',
							'type' => 'button',
							'id' => 'password-gen',
							'class' => 'button-input button',
							'value' => 'Generate Password'
						), array(
							'tag' => 'label',
							'class' => 'checkbox-label hidden required invalid init',
							'content' => formTag('br', array('class' => 'spacer')).formTag('input', array(
								'type' => 'checkbox',
								'class' => 'checkbox-input',
								'name' => 'pass_saved',
								'value' => 1
							)).formTag('span', array('content' => 'I have copied the password to a safe place.'))
						));
						
						// Confirm new user password
						echo formRow('New User Password (confirm)', array(
							'tag' => 'input',
							'class' => 'text-input required invalid init',
							'name' => 'confirm_pass'
						));
						
						// Separator
						echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
						
						// Submit button
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'submit',
							'class' => 'submit-input button',
							'name' => 'submit',
							'value' => 'Update Password'
						));
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
	 * @param array $data -- The submission data.
	 * @param int $id -- The user's id.
	 * @return string
	 */
	private function validatePasswordData(array $data, int $id): string {
		global $rs_query, $session;
		
		if(empty($data['admin_pass']) || empty($data['new_pass']) || empty($data['confirm_pass']))
			return exitNotice('REQ', -1);
		
		if(!$this->verifyPassword($data['admin_pass'], $session['id']))
			return exitNotice('Admin password is incorrect.', -1);
		
		if($data['new_pass'] !== $data['confirm_pass'])
			return exitNotice('New and confirm passwords do not match.', -1);
		
		if(strlen($data['new_pass']) < self::PW_LENGTH || strlen($data['confirm_pass']) < self::PW_LENGTH)
			return exitNotice('New password must be at least ' . self::PW_LENGTH . ' characters long.', -1);
		
		if(!isset($data['pass_saved']) || $data['pass_saved'] != 1)
			return exitNotice('Please confirm that you\'ve saved your password to a safe location.', -1);
		
		// Hash the password (encrypts the password for security purposes)
		$hashed_password = password_hash($data['new_pass'], PASSWORD_BCRYPT, array('cost' => 10));
		
		$rs_query->update('users', array('password' => $hashed_password), array('id' => $id));
		
		$session = $rs_query->selectField('users', 'session', array('id' => $id));
		
		if(!is_null($session)) {
			$rs_query->update('users', array('session' => null), array('id' => $id, 'session' => $session));
			
			if($_COOKIE['session'] === $session)
				setcookie('session', '', 1, '/');
		}
		
		return exitNotice('Password updated! Return to <a href="' . ADMIN_URI . '">Return to list</a>?');
	}
	
	/**
	 * Verify that the current user's password matches what's in the database.
	 * @since 1.2.4[a]
	 *
	 * @access protected
	 * @param string $password -- The user's password.
	 * @param int $id -- The user's id.
	 * @return bool
	 */
	protected function verifyPassword(string $password, int $id): bool {
		global $rs_query;
		
		$db_password = $rs_query->selectField('users', 'password', array('id' => $id));
		
		return !empty($db_password) && password_verify($password, $db_password);
	}
	
	/**
	 * Reassign a user's content to another user.
	 * @since 2.4.3[a]
	 *
	 * @access public
	 */
	public function reassignContent(): void {
		global $session;
		
		if(empty($this->id) || $this->id <= 0 || $this->id === $session['id']) {
			redirect(ADMIN_URI);
		} else {
			// Validate the form data
			if(isset($_POST['submit'])) $this->validateReassignContentData($_POST, $this->id);
			?>
			<div class="heading-wrap">
				<h1>Reassign Content by <i><?php echo $this->getUsername($this->id); ?></i></h1>
			</div>
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						// Reassign to user
						echo formRow('Reassign to User', array(
							'tag' => 'select',
							'class' => 'select-input',
							'name' => 'reassign_to',
							'content' => $this->getUserList($this->id)
						));
						
						// Separator
						echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
						
						// Submit button
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'submit',
							'class' => 'submit-input button',
							'name' => 'submit',
							'value' => 'Submit'
						));
						?>
					</table>
				</form>
			</div>
			<?php
		}
	}
	
	/**
	 * Validate the "Reassign Content" form data.
	 * @since 2.4.3[a]
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @param int $id -- The user's id.
	 */
	private function validateReassignContentData(array $data, int $id): void {
		global $rs_query;
		
		// Reassign all posts to the new author
		$rs_query->update('posts', array('author' => $data['reassign_to']), array('author' => $id));
		
		$rs_query->delete('users', array('id' => $id));
		$rs_query->delete('usermeta', array('user' => $id));
		
		redirect(ADMIN_URI . '?exit_status=success');
	}
	
	/**
	 * Fetch a username by a user's id.
	 * @since 2.4.3[a]
	 *
	 * @access private
	 * @param int $id -- The user's id.
	 * @return string
	 */
	private function getUsername(int $id): string {
		global $rs_query;
		
		return $rs_query->selectField('users', 'username', array('id' => $id));
	}
	
	/**
	 * Construct a list of users.
	 * @since 2.4.3[a]
	 *
	 * @access private
	 * @param int $id -- The user's id.
	 * @return string
	 */
	private function getUserList(int $id): string {
		global $rs_query;
		
		$list = '';
		
		$users = $rs_query->select('users', array('id', 'username'), array(
			'id' => array('<>', $id)
		), 'username');
		
		foreach($users as $user) {
			$list .= tag('option', array(
				'value' => $user['id'],
				'content' => $user['username']
			));
		}
		
		return $list;
	}
	
	/**
	 * Fetch the user count based on a specific status.
	 * @since 1.3.2[b]
	 *
	 * @access private
	 * @param string $status (optional) -- The user's status.
	 * @param string $search (optional) -- The search query.
	 * @return int
	 */
	private function getUserCount(string $status = '', string $search = ''): int {
		global $rs_query;
		
		switch($status) {
			case 'online':
				if(!empty($search)) {
					return $rs_query->select('users', 'COUNT(*)', array(
						'username' => array('LIKE', '%' . $search . '%'),
						'session' => array('IS NOT NULL')
					));
				} else {
					return $rs_query->select('users', 'COUNT(*)', array(
						'session' => array('IS NOT NULL')
					));
				}
				break;
			case 'offline':
				if(!empty($search)) {
					return $rs_query->select('users', 'COUNT(*)', array(
						'username' => array('LIKE', '%' . $search . '%'),
						'session' => array('IS NULL')
					));
				} else {
					return $rs_query->select('users', 'COUNT(*)', array(
						'session' => array('IS NULL')
					));
				}
				break;
			default:
				if(!empty($search)) {
					return $rs_query->select('users', 'COUNT(*)', array(
						'username' => array('LIKE', '%' . $search . '%')
					));
				} else {
					return $rs_query->select('users', 'COUNT(*)');
				}
		}
	}
	
	/**
	 * Construct bulk actions.
	 * @since 1.3.2[b]
	 *
	 * @access private
	 */
	private function bulkActions(): void {
		global $rs_query;
		?>
		<div class="bulk-actions">
			<?php
			if(userHasPrivilege('can_edit_users')) {
				?>
				<select class="actions">
					<?php
					$roles = $rs_query->select('user_roles', array('id', 'name'), array(), 'id');
					
					foreach($roles as $role) {
						echo formTag('option', array(
							'value' => $role['id'],
							'content' => $role['name']
						));
					}
					?>
				</select>
				<?php
				// Update status
				button(array(
					'class' => 'bulk-update',
					'title' => 'Bulk status update',
					'label' => 'Update'
				));
			}
			
			if(userHasPrivilege('can_delete_users')) {
				// Delete
				button(array(
					'class' => 'bulk-delete',
					'title' => 'Bulk delete',
					'label' => 'Delete'
				));
			}
			?>
		</div>
		<?php
	}
}