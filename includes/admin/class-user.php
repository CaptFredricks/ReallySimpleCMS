<?php
/**
 * Admin class used to implement the User object.
 * Users have various privileges on the website not afforded to visitors, depending on their access level.
 * Users can be created, modified, and deleted.
 * @since 1.1.0-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_user
 *
 * ## CONSTANTS [2] ##
 * - protected int UN_LENGTH
 * - protected int PW_LENGTH
 *
 * ## VARIABLES [9] ##
 * - protected int $id
 * - protected string $username
 * - protected string $email
 * - protected string $registered
 * - protected string $last_login
 * - protected int $role
 * - protected array $admin_page
 * - protected string $action
 * - protected array $paged
 *
 * ## METHODS [24] ##
 * - public __construct(int $id, string $action)
 * { LISTS, FORMS, & ACTIONS [7] }
 * - public listRecords(): void
 * - public createRecord(): void
 * - public editRecord(): void
 * - public updateUserRole(int $role, int $id): void
 * - public deleteRecord(): void
 * - public resetPassword(): void
 * - public reassignContent(): void
 * { VALIDATION [1] }
 * - private validateSubmission(array $data): string
 * { MISCELLANEOUS [15] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private bulkActions(): void
 * - protected usernameExists(string $username, int $id): bool
 * - protected emailExists(string $email, int $id): bool
 * - private userHasContent(int $id): bool
 * - private getUsername(int $id): string
 * - protected getUserMeta(int $id): array
 * - private getRole(int $id): string
 * - private getRoleList(int $id): string
 * - protected verifyPassword(string $password, int $id): bool
 * - private getUserList(int $id): string
 * - private getResults(string $status, ?string $search, bool $all): array
 * - private getEntryCount(string $status, ?string $search): int
 * - private getActionLinks(array $user): string
 */
namespace Admin;

class User implements AdminInterface {
	/**
	 * Set the minimum username length.
	 * @since 1.1.0-alpha
	 *
	 * @access protected
	 * @var int
	 */
	protected const UN_LENGTH = 4;
	
	/**
	 * Set the minimum password length.
	 * @since 1.1.0-alpha
	 *
	 * @access protected
	 * @var int
	 */
	protected const PW_LENGTH = 8;
	
	/**
	 * The currently queried user's id.
	 * @since 1.1.1-beta
	 *
	 * @access protected
	 * @var int
	 */
	protected $id;
	
	/**
	 * The currently queried user's username.
	 * @since 1.1.1-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $username;
	
	/**
	 * The currently queried user's email.
	 * @since 1.1.1-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $email;
	
	/**
	 * The currently queried user's register date.
	 * @since 1.3.14-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $registered;
	
	/**
	 * The currently queried user's last login date.
	 * @since 1.3.14-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $last_login;
	
	/**
	 * The currently queried user's role.
	 * @since 1.1.1-beta
	 *
	 * @access protected
	 * @var int
	 */
	protected $role;
	
	/**
	 * The admin page's data.
	 * @since 1.3.16-beta
	 *
	 * @access protected
	 * @var array
	 */
	protected $admin_page = array();
	
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
	 * @since 1.1.1-beta
	 *
	 * @access public
	 * @param int $id -- The user's id.
	 * @param string $action -- The current action.
	 */
	public function __construct(int $id, string $action) {
		global $rs_query, $rs_admin_pages;
		
		$this->action = $action;
		$this->admin_page = $rs_admin_pages[basename($_SERVER['PHP_SELF'], '.php')];
		
		if($id > 0) {
			$cols = array_keys(get_object_vars($this));
			$exclude = array('admin_page', 'action', 'paged');
			$cols = array_diff($cols, $exclude);
			
			$user = $rs_query->selectRow(getTable('u'), $cols, array(
				'id' => $id
			));
			
			foreach($user as $key => $value) $this->$key = $user[$key];
		} else {
			$this->id = 0;
		}
	}
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct a list of all users in the database.
	 * @since 1.2.1-alpha
	 *
	 * @access public
	 */
	public function listRecords(): void {
		global $rs_session;
		
		// Query vars
		$status = $_GET['status'] ?? 'all';
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
					'username' => 'Username',
					'display-name' => 'Display Name',
					'email' => 'Email',
					'registered' => 'Registered',
					'role' => 'Role',
					'status' => 'Status',
					'last-login' => 'Last Login'
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$users = $this->getResults($status, $search);
				
				foreach($users as $user) {
					$meta = $this->getUserMeta($user['id']);
					
					echo tableRow(
						// Bulk select
						tdCell(domTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox',
							'value' => $user['id']
						)), 'bulk-select'),
						// Username
						tdCell(getMedia($meta['avatar'], array(
							'class' => 'avatar',
							'width' => 32,
							'height' => 32
						)) . domTag('strong', array(
							'content' => $user['username']
						)) . domTag('div', array(
							'class' => 'actions',
							'content' => $this->getActionLinks($user)
						)), 'username'),
						// Display name
						tdCell($meta['display_name'], 'display-name'),
						// Email
						tdCell($user['email'], 'email'),
						// Registered
						tdCell(formatDate($user['registered'], 'd M Y @ g:i A'), 'registered'),
						// Role
						tdCell($this->getRole($user['role']), 'role'),
						// Status
						tdCell(is_null($user['session']) ? 'Offline' : 'Online', 'status'),
						// Last login
						tdCell(is_null($user['last_login']) ? 'Never' :
							formatDate($user['last_login'], 'd M Y @ g:i A'), 'last-login')
					);
				}
				
				if(empty($users))
					echo tableRow(tdCell($this->admin_page['labels']['no_items'], '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Bulk actions
		if(!empty($users)) $this->bulkActions();
		
		// Set up page navigation
		echo pagerNav($this->paged['current'], $this->paged['count']);
		
		includeFile(PATH . MODALS . '/modal-delete.php');
	}
	
	/**
	 * Create a new user.
	 * @since 1.1.2-alpha
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
					// Username
					echo formRow(array('Username', true), array(
						'tag' => 'input',
						'id' => 'username-field',
						'class' => 'text-input required invalid init',
						'name' => 'username',
						'value' => ($_POST['username'] ?? ''),
						'placeholder' => $this->admin_page['labels']['title_placeholder']
					));
					
					// Email
					echo formRow(array('Email', true), array(
						'tag' => 'input',
						'type' => 'email',
						'id' => 'email-field',
						'class' => 'text-input required invalid init',
						'name' => 'email',
						'value' => ($_POST['email'] ?? '')
					));
					
					// First name
					echo formRow('First Name', array(
						'tag' => 'input',
						'id' => 'first-name-field',
						'class' => 'text-input',
						'name' => 'first_name',
						'value' => ($_POST['first_name'] ?? '')
					));
					
					// Last name
					echo formRow('Last Name', array(
						'tag' => 'input',
						'id' => 'last-name-field',
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
						'content' => domTag('br', array(
							'class' => 'spacer'
						)) . domTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox-input',
							'name' => 'pass_saved',
							'value' => 1
						)) . domTag('span', array(
							'content' => 'I have copied the password to a safe place.'
						))
					));
					
					// Avatar
					echo formRow('Avatar', array(
						'tag' => 'div',
						'class' => 'image-wrap',
						'content' => domTag('img', array(
							'src' => '//:0',
							'data-field' => 'thumb'
						)) . domTag('span', array(
							'class' => 'image-remove',
							'title' => 'Remove',
							'content' => domTag('i', array(
								'class' => 'fa-solid fa-xmark'
							))
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
						'id' => 'role-field',
						'class' => 'select-input',
						'name' => 'role',
						'content' => $this->getRoleList((int)getSetting('default_user_role'))
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
		includeFile(PATH . MODALS . '/modal-upload.php');
	}
	
	/**
	 * Edit an existing user.
	 * @since 1.2.1-alpha
	 *
	 * @access public
	 */
	public function editRecord(): void {
		global $rs_session;
		
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		if($this->id === $rs_session['id'])
			redirect('profile.php'); // The user is viewing their own page
		
		$this->pageHeading();
		
		$meta = $this->getUserMeta($this->id);
		$avatar_filepath = PATH . getMediaSrc($meta['avatar']);
		
		if(!empty($meta['avatar']) && file_exists($avatar_filepath))
			list($width, $height) = getimagesize($avatar_filepath);
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Username
					echo formRow(array('Username', true), array(
						'tag' => 'input',
						'id' => 'username-field',
						'class' => 'text-input required invalid init',
						'name' => 'username',
						'value' => $this->username,
						'placeholder' => $this->admin_page['labels']['title_placeholder']
					));
					
					// Email
					echo formRow(array('Email', true), array(
						'tag' => 'input',
						'type' => 'email',
						'id' => 'email-field',
						'class' => 'text-input required invalid init',
						'name' => 'email',
						'value' => $this->email
					));
					
					// First name
					echo formRow('First Name', array(
						'tag' => 'input',
						'id' => 'first-name-field',
						'class' => 'text-input',
						'name' => 'first_name',
						'value' => $meta['first_name']
					));
					
					// Last name
					echo formRow('Last Name', array(
						'tag' => 'input',
						'id' => 'last-name-field',
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
						)) . domTag('span', array(
							'class' => 'image-remove',
							'title' => 'Remove',
							'content' => domTag('i', array(
								'class' => 'fa-solid fa-xmark'
							))
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
						'id' => 'role-field',
						'class' => 'select-input',
						'name' => 'role',
						'content' => $this->getRoleList($this->role)
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
			<?php echo actionLink('reset_password', array(
				'classes' => 'reset-password button',
				'caption' => 'Reset Password',
				'id' => $this->id
			)); ?>
		</div>
		<?php
		includeFile(PATH . MODALS . '/modal-upload.php');
	}
	
	/**
	 * Update a user's role.
	 * @since 1.3.2-beta
	 *
	 * @access public
	 * @param int $role -- The user's role.
	 * @param int $id -- The user's id.
	 */
	public function updateUserRole(int $role, int $id): void {
		global $rs_query, $rs_session;
		
		$this->id = $id;
		
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		if($this->id !== $rs_session['id']) {
			$rs_query->update(getTable('u'), array(
				'role' => $role
			), array(
				'id' => $this->id
			));
		}
	}
	
	/**
	 * Delete an existing user.
	 * @since 1.2.3-alpha
	 *
	 * @access public
	 */
	public function deleteRecord(): void {
		global $rs_query, $rs_session;
		
		if(empty($this->id) || $this->id <= 0 || $this->id === $rs_session['id'])
			redirect(ADMIN_URI);
		
		$rs_query->delete(getTable('u'), array(
			'id' => $this->id
		));
		
		$rs_query->delete(getTable('um'), array(
			'user' => $this->id
		));
		
		redirect(ADMIN_URI . getQueryString(array(
			'exit_status' => 'del_success'
		)));
	}
	
	/**
	 * Construct the "Reset Password" form.
	 * @since 1.2.3-alpha
	 *
	 * @access public
	 */
	public function resetPassword(): void {
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Admin password
					echo formRow('Admin Password', array(
						'tag' => 'input',
						'type' => 'password',
						'id' => 'admin-pass-field',
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
						'content' => domTag('br', array(
							'class' => 'spacer'
						)) . domTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox-input',
							'name' => 'pass_saved',
							'value' => 1
						)) . domTag('span', array(
							'content' => 'I have copied the password to a safe place.'
						))
					));
					
					// Confirm new user password
					echo formRow('New User Password (confirm)', array(
						'tag' => 'input',
						'id' => 'confirm-pass-field',
						'class' => 'text-input required invalid init',
						'name' => 'confirm_pass'
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
						'value' => 'Update Password'
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Reassign a user's content to another user.
	 * @since 2.4.3-alpha
	 *
	 * @access public
	 */
	public function reassignContent(): void {
		global $rs_session;
		
		if(empty($this->id) || $this->id <= 0 || $this->id === $rs_session['id'])
			redirect(ADMIN_URI);
		
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Reassign to user
					echo formRow('Reassign to User', array(
						'tag' => 'select',
						'id' => 'reassign-to-field',
						'class' => 'select-input',
						'name' => 'reassign_to',
						'content' => $this->getUserList($this->id)
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
						'value' => 'Submit'
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the form data.
	 * @since 1.2.0-alpha
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateSubmission(array $data): string {
		global $rs_query, $rs_session;
		
		if($this->action === 'create' || $this->action === 'edit') {
			if(empty($data['username']) || empty($data['email'])) {
				return exitNotice('REQ', -1);
				exit;
			}
			
			if(strlen($data['username']) < self::UN_LENGTH) {
				return exitNotice('Username must be at least ' . self::UN_LENGTH . ' characters long.', -1);
				exit;
			}
			
			$username = sanitize($data['username'], '/[^a-z0-9_\.]/i', false);
			
			if($this->usernameExists($username, $this->id)) {
				return exitNotice('That username has already been taken. Please choose another one.', -1);
				exit;
			}
			
			if($this->emailExists($data['email'], $this->id)) {
				return exitNotice('That email is already taken by another user. Please choose another one.', -1);
				exit;
			}
			
			$usermeta = array(
				'first_name' => $data['first_name'],
				'last_name' => $data['last_name'],
				'display_name' => $data['username'],
				'avatar' => $data['avatar'],
				'theme' => 'bedrock',
				'dismissed_notices' => ''
			);
		}
		
		switch($this->action) {
			case 'create':
				if(empty($data['password'])) {
					return exitNotice('REQ', -1);
					exit;
				}
				
				if(strlen($data['password']) < self::PW_LENGTH) {
					return exitNotice('Password must be at least ' . self::PW_LENGTH . ' characters long.', -1);
					exit;
				}
				
				if(!isset($data['pass_saved']) || $data['pass_saved'] != 1) {
					return exitNotice('Please confirm that you\'ve saved your password to a safe location.', -1);
					exit;
				}
				
				$hashed_password = password_hash($data['password'], PASSWORD_BCRYPT, array(
					'cost' => 10
				));
				
				$insert_id = $rs_query->insert(getTable('u'), array(
					'username' => $username,
					'password' => $hashed_password,
					'email' => $data['email'],
					'registered' => 'NOW()',
					'role' => $data['role']
				));
				
				foreach($usermeta as $key => $value) {
					$rs_query->insert(getTable('um'), array(
						'user' => $insert_id,
						'key' => $key,
						'value' => $value
					));
				}
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $insert_id,
					'action' => 'edit',
					'exit_status' => 'create_success'
				)));
				break;
			case 'edit':
				$rs_query->update(getTable('u'), array(
					'username' => $username,
					'email' => $data['email'],
					'role' => $data['role']
				), array(
					'id' => $this->id
				));
				
				foreach($usermeta as $key => $value) {
					$rs_query->update(getTable('um'), array(
						'value' => $value
					), array(
						'user' => $this->id,
						'key' => $key
					));
				}
				
				foreach($data as $key => $value) $this->$key = $value;
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $this->id,
					'action' => $this->action,
					'exit_status' => 'edit_success'
				)));
				break;
			case 'reset_password':
				if(empty($data['admin_pass']) || empty($data['new_pass']) || empty($data['confirm_pass'])) {
					return exitNotice('REQ', -1);
					exit;
				}
				
				if(!$this->verifyPassword($data['admin_pass'], $rs_session['id'])) {
					return exitNotice('Admin password is incorrect.', -1);
					exit;
				}
				
				if($data['new_pass'] !== $data['confirm_pass']) {
					return exitNotice('New and confirm passwords do not match.', -1);
					exit;
				}
				
				if(strlen($data['new_pass']) < self::PW_LENGTH || strlen($data['confirm_pass']) < self::PW_LENGTH) {
					return exitNotice('New password must be at least ' . self::PW_LENGTH . ' characters long.', -1);
					exit;
				}
				
				if(!isset($data['pass_saved']) || $data['pass_saved'] != 1) {
					return exitNotice('Please confirm that you\'ve saved your password to a safe location.', -1);
					exit;
				}
				
				$hashed_password = password_hash($data['new_pass'], PASSWORD_BCRYPT, array(
					'cost' => 10
				));
				
				$rs_query->update(getTable('u'), array(
					'password' => $hashed_password
				), array(
					'id' => $this->id
				));
				
				$session = $rs_query->selectField(getTable('u'), 'session', array(
					'id' => $this->id
				));
				
				if(!is_null($session)) {
					$rs_query->update(getTable('u'), array(
						'session' => null
					), array(
						'id' => $this->id,
						'session' => $session
					));
					
					if($_COOKIE['session'] === $session)
						setcookie('session', '', 1, '/');
				}
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $this->id,
					'action' => $this->action,
					'exit_status' => 'pw_success'
				)));
				break;
			case 'reassign_content':
				// Reassign all posts to the new author
				$rs_query->update(getTable('p'), array(
					'author' => $data['reassign_to']
				), array(
					'author' => $this->id
				));
				
				$rs_query->delete(getTable('u'), array(
					'id' => $this->id
				));
				
				$rs_query->delete(getTable('um'), array(
					'user' => $this->id
				));
				
				redirect(ADMIN_URI . getQueryString(array(
					'exit_status' => 'reassign_success',
					'reassign_to' => $data['reassign_to']
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
					'content' => $this->username
				)) . ' }';
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			case 'reset_password':
				$title = 'Reset Password: { ' . domTag('em', array(
					'content' => $this->username
				)) . ' }';
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			case 'reassign_content':
				$title = 'Reassign Content: { ' . domTag('em', array(
					'content' => $this->username
				)) . ' }';
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			default:
				$title = $labels['name'];
				$status = $_GET['status'] ?? 'all';
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
				if(userHasPrivilege('can_create_users')) {
					echo actionLink($this->admin_page['actions']['create'], array(
						'classes' => 'button',
						'caption' => $labels['create_button']
					));
				}
				
				// Search
				recordSearch(array(
					'status' => $status
				));
				
				// Info
				adminInfo();
				
				domTagPr('hr');
				
				// Exit notices
				if(isset($_GET['exit_status']))
					echo $this->exitNotice($_GET['exit_status']);
				?>
				<ul class="status-nav">
					<?php
					$keys = array('all', 'online', 'offline');
					$count = array();
					
					foreach($keys as $key)
						$count[$key] = $this->getEntryCount($key, $search);
					
					// Statuses
					foreach($count as $key => $value) {
						domTagPr('li', array(
							'content' => domTag('a', array(
								'href' => ADMIN_URI . ($key === 'all' ? '' : '?status=' . $key),
								'content' => ucfirst($key) . ' ' . domTag('span', array(
									'class' => 'count',
									'content' => '(' . $value . ')'
								))
							))
						));
						
						if($key !== array_key_last($count)) echo ' &bull; ';
					}
					?>
				</ul>
				<?php
				// Record count
				domTagPr('div', array(
					'class' => 'entry-count status',
					'content' => $count[$status] . ' ' . ($count[$status] === 1 ? 'entry' : 'entries')
				));
				
				$this->paged['count'] = ceil($count[$status] / $this->paged['per_page']);
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
			'create_success' => 'The user was successfully created. ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'edit_success' => 'User updated! ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'pw_success' => 'Password updated! ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'del_success' => 'The user was successfully deleted.',
			'reassign_success' => 'The user\'s content was successfully reassigned to ' . domTag('strong', array(
				'content' => $this->getUsername($_GET['reassign_to'])
			)),
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Construct bulk actions.
	 * @since 1.3.2-beta
	 *
	 * @access private
	 */
	private function bulkActions(): void {
		global $rs_query;
		
		$labels = $this->admin_page['labels'];
		?>
		<div class="bulk-actions">
			<?php
			if(userHasPrivilege('can_edit_users')) {
				$roles = $rs_query->select(getTable('ur'), array('id', 'name'), array(), array(
					'order_by' => 'id'
				));
				
				$list = array();
				
				foreach($roles as $role) {
					$list[] = domTag('option', array(
						'value' => $role['id'],
						'content' => $role['name']
					));
				}
				
				domTagPr('select', array(
					'class' => 'actions',
					'name' => 'bulk_actions',
					'content' => implode('', $list)
				));
				
				// Update role
				button(array(
					'class' => 'bulk-update',
					'title' => 'Bulk role update',
					'label' => $labels['bulk_update']
				));
			}
			
			if(userHasPrivilege('can_delete_users')) {
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
	 * Check whether a username already exists in the database.
	 * @since 1.2.0-alpha
	 *
	 * @access protected
	 * @param string $username -- The username.
	 * @param int $id -- The user's id.
	 * @return bool
	 */
	protected function usernameExists(string $username, int $id): bool {
		global $rs_query;
		
		if($id === 0) {
			return $rs_query->selectRow(getTable('u'), 'COUNT(username)', array(
				'username' => $username
			)) > 0;
		} else {
			return $rs_query->selectRow(getTable('u'), 'COUNT(username)', array(
				'username' => $username,
				'id' => array('<>', $id)
			)) > 0;
		}
	}
	
	/**
	 * Check whether an email already exists in the database.
	 * @since 2.0.6-alpha
	 *
	 * @access protected
	 * @param string $email -- The user's email.
	 * @param int $id -- The user's id.
	 * @return bool
	 */
	protected function emailExists(string $email, int $id): bool {
		global $rs_query;
		
		if($id === 0) {
			return $rs_query->selectRow(getTable('u'), 'COUNT(email)', array(
				'email' => $email
			)) > 0;
		} else {
			return $rs_query->selectRow(getTable('u'), 'COUNT(email)', array(
				'email' => $email,
				'id' => array('<>', $id)
			)) > 0;
		}
	}
	
	/**
	 * Check whether a user has content assigned to them.
	 * @since 2.4.3-alpha
	 *
	 * @access private
	 * @param int $id -- The user's id.
	 * @return bool
	 */
	private function userHasContent(int $id): bool {
		global $rs_query;
		
		return $rs_query->selectRow(getTable('p'), 'COUNT(author)', array(
			'author' => $id
		)) > 0;
	}
	
	/**
	 * Fetch a username by a user's id.
	 * @since 2.4.3-alpha
	 *
	 * @access private
	 * @param int $id -- The user's id.
	 * @return string
	 */
	private function getUsername(int $id): string {
		global $rs_query;
		
		return $rs_query->selectField(getTable('u'), 'username', array(
			'id' => $id
		));
	}
	
	/**
	 * Fetch a user's metadata.
	 * @since 1.2.2-alpha
	 *
	 * @access protected
	 * @param int $id -- The user's id.
	 * @return array
	 */
	protected function getUserMeta(int $id): array {
		global $rs_query;
		
		$usermeta = $rs_query->select(getTable('um'), array('key', 'value'), array(
			'user' => $id
		));
		
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
	 * @since 1.7.0-alpha
	 *
	 * @access private
	 * @param int $id -- The user's id.
	 * @return string
	 */
	private function getRole(int $id): string {
		global $rs_query;
		
		return $rs_query->selectField(getTable('ur'), 'name', array(
			'id' => $id
		));
	}
	
	/**
	 * Construct a list of roles.
	 * @since 1.7.0-alpha
	 *
	 * @access private
	 * @param int $id (optional) -- The user's id.
	 * @return string
	 */
	private function getRoleList(int $id = 0): string {
		global $rs_query;
		
		$list = array();
		
		$roles = $rs_query->select(getTable('ur'), '*', array(), array(
			'order_by' => 'id'
		));
		
		foreach($roles as $role) {
			$list[] = domTag('option', array(
				'value' => $role['id'],
				'selected' => $role['id'] === $id,
				'content' => $role['name']
			));
		}
		
		return implode('', $list);
	}
	
	/**
	 * Verify that the current user's password matches what's in the database.
	 * @since 1.2.4-alpha
	 *
	 * @access protected
	 * @param string $password -- The user's password.
	 * @param int $id -- The user's id.
	 * @return bool
	 */
	protected function verifyPassword(string $password, int $id): bool {
		global $rs_query;
		
		$db_password = $rs_query->selectField(getTable('u'), 'password', array(
			'id' => $id
		));
		
		return !empty($db_password) && password_verify($password, $db_password);
	}
	
	/**
	 * Construct a list of users.
	 * @since 2.4.3-alpha
	 *
	 * @access private
	 * @param int $id -- The user's id.
	 * @return string
	 */
	private function getUserList(int $id): string {
		global $rs_query;
		
		$list = array();
		
		$users = $rs_query->select(getTable('u'), array('id', 'username'), array(
			'id' => array('<>', $id)
		), array(
			'order_by' => 'username'
		));
		
		foreach($users as $user) {
			$list[] = domTag('option', array(
				'value' => $user['id'],
				'content' => $user['username']
			));
		}
		
		return implode('', $list);
	}
	
	/**
	 * Fetch all users based on a specific status.
	 * @since 1.3.15-beta
	 *
	 * @access private
	 * @param string $status -- The user's status.
	 * @param null|string $search -- The search query.
	 * @param bool $all (optional) -- Whether to return all or set a limit (for pagination).
	 * @return array
	 */
	private function getResults(string $status, ?string $search, bool $all = false): array {
		global $rs_query;
		
		$order_by = 'username';
		$order = 'ASC';
		$limit = $all === false ? array($this->paged['start'], $this->paged['per_page']) : 0;
		
		if(!is_null($search)) {
			// Search results
			switch($status) {
				case 'online':
					return $rs_query->select(getTable('u'), '*', array(
						'username' => array('LIKE', '%' . $search . '%'),
						'session' => array('IS NOT NULL')
					), array(
						'order_by' => $order_by,
						'order' => $order,
						'limit' => $limit
					));
					break;
				case 'offline':
					return $rs_query->select(getTable('u'), '*', array(
						'username' => array('LIKE', '%' . $search . '%'),
						'session' => array('IS NULL')
					), array(
						'order_by' => $order_by,
						'order' => $order,
						'limit' => $limit
					));
					break;
				default:
					return $rs_query->select(getTable('u'), '*', array(
						'username' => array('LIKE', '%' . $search . '%')
					), array(
						'order_by' => $order_by,
						'order' => $order,
						'limit' => $limit
					));
			}
		} else {
			// All results
			switch($status) {
				case 'online':
					return $rs_query->select(getTable('u'), '*', array(
						'session' => array('IS NOT NULL')
					), array(
						'order_by' => $order_by,
						'order' => $order,
						'limit' => $limit
					));
					break;
				case 'offline':
					return $rs_query->select(getTable('u'), '*', array(
						'session' => array('IS NULL')
					), array(
						'order_by' => $order_by,
						'order' => $order,
						'limit' => $limit
					));
					break;
				default:
					return $rs_query->select(getTable('u'), '*', array(), array(
						'order_by' => $order_by,
						'order' => $order,
						'limit' => $limit
					));
			}
		}
	}
	
	/**
	 * Fetch the user count based on a specific status.
	 * @since 1.3.2-beta
	 *
	 * @access private
	 * @param string $status -- The user's status.
	 * @param null|string $search -- The search query.
	 * @return int
	 */
	private function getEntryCount(string $status, ?string $search): int {
		return count($this->getResults($status, $search, true));
	}
	
	/**
	 * Fetch all associated action links.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @param array $user -- The user's data.
	 * @return string
	 */
	private function getActionLinks(array $user): string {
		global $rs_session;
		
		$actions = $this->admin_page['actions'];
		$action_list = array();
		
		foreach($actions as $key => $value) {
			if($value === 'create') continue;
			
			$privileged = match($value) {
				'edit' => userHasPrivilege('can_edit_users'),
				'delete' => userHasPrivilege('can_delete_users'),
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
			$edit['privileged'] ? ($user['id'] === $rs_session['id'] ? domTag('a', array(
					'href' => ADMIN . '/profile.php',
					'content' => $edit['caption']
				)) : actionLink($edit['link'], array(
					'caption' => $edit['caption'],
					'id' => $user['id']
				))) : null,
			// Delete
			$delete['privileged'] && $user['id'] !== $rs_session['id'] ?
				($this->userHasContent($user['id']) ? actionLink('reassign_content', array(
					'caption' => $delete['caption'],
					'id' => $user['id']
				)) : actionLink($delete['link'], array(
					'classes' => 'modal-launch delete-item',
					'data_item' => 'user',
					'caption' => $delete['caption'],
					'id' => $user['id']
				))) : null
		);
		
		// Filter out any empty actions
		$action_links = array_filter($action_links);
		
		return implode(' &bull; ', $action_links);
	}
}