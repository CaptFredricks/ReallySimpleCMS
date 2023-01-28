<?php
/**
 * Admin class used to implement the Login object.
 * @since 1.2.0[b]{ss-01}
 *
 * Logins are attempts by registered users to gain access to the admin dashboard via the Log In page.
 * Users must enter their username, password, and a captcha properly to successfully log in.
 */
class Login {
	/**
	 * The currently queried login attempt, blacklisted login, or login rule's id.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access private
	 * @var int
	 */
	private $id;
	
	/**
	 * The currently queried login attempt's login (username or email).
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access private
	 * @var string
	 */
	private $login;
	
	/**
	 * The currently queried login attempt's IP address.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access private
	 * @var string
	 */
	private $ip_address;
	
	/**
	 * The currently queried blacklisted login's name.
	 * @since 1.2.0[b]{ss-02}
	 *
	 * @access private
	 * @var string
	 */
	private $name;
	
	/**
	 * The currently queried blacklisted login or login rule's duration.
	 * @since 1.2.0[b]{ss-02}
	 *
	 * @access private
	 * @var int
	 */
	private $duration;
	
	/**
	 * The currently queried blacklisted login's reason.
	 * @since 1.2.0[b]{ss-02}
	 *
	 * @access private
	 * @var string
	 */
	private $reason;
	
	/**
	 * The currently queried login rule's type.
	 * @since 1.2.0[b]{ss-05}
	 *
	 * @access private
	 * @var string
	 */
	private $type;
	
	/**
	 * The currently queried login rule's attempts.
	 * @since 1.2.0[b]{ss-05}
	 *
	 * @access private
	 * @var int
	 */
	private $attempts;
	
	/**
	 * Class constructor.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access public
	 * @param string $page
	 * @param int $id
	 */
	public function __construct($page, $id) {
		// Extend the Query object
		global $rs_query;
		
		if(getSetting('delete_old_login_attempts')) {
			$login_attempts = $rs_query->select('login_attempts', array('id', 'date'));
			
			foreach($login_attempts as $login_attempt) {
				$time = new DateTime();
				
				// Subtract 30 days from the current date
				$time->sub(new DateInterval('P30D'));
				
				$threshold = $time->format('Y-m-d H:i:s');
				
				// Delete the login attempt if it's expired
				if($threshold > $login_attempt['date'])
					$rs_query->delete('login_attempts', array('id' => $login_attempt['id']));
			}
		}
		
		if($id !== 0) {
			// Create an array of columns to fetch from the database
			$cols = array_keys(get_object_vars($this));
			
			if($page === 'blacklist') {
				// Exclude columns from the `login_attempts` and `login_rules` tables
				$exclude = array('login', 'ip_address', 'type', 'attempts');
				
				$cols = array_diff($cols, $exclude);
				$blacklisted_login = $rs_query->selectRow('login_blacklist', $cols, array('id' => $id));
				
				// Set the class variable values
				foreach($blacklisted_login as $key => $value) $this->$key = $blacklisted_login[$key];
			} elseif($page === 'rules') {
				// Exclude columns from the `login_attempts` and `login_blacklist` tables
				$exclude = array('login', 'ip_address', 'name', 'reason');
				
				$cols = array_diff($cols, $exclude);
				$login_rule = $rs_query->selectRow('login_rules', $cols, array('id' => $id));
				
				// Set the class variable values
				foreach($login_rule as $key => $value) $this->$key = $login_rule[$key];
			} else {
				// Exclude columns from the `login_blacklist` and `login_rules` tables
				$exclude = array('name', 'duration', 'reason', 'type', 'attempts');
				
				$cols = array_diff($cols, $exclude);
				$login_attempt = $rs_query->selectRow('login_attempts', $cols, array('id' => $id));
				
				// Set the class variable values
				foreach($login_attempt as $key => $value) $this->$key = $login_attempt[$key];
			}
		}
	}
	
	/**
	 * Construct a list of all login attempts in the database.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access public
	 */
	public function loginAttempts(): void {
		// Extend the Query object
		global $rs_query;
		
		// Query vars
		$status = $_GET['status'] ?? 'all';
		$search = $_GET['search'] ?? null;
		$paged = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Login Attempts</h1>
			<?php
			recordSearch(array(
				'status' => $status
			));
			adminInfo();
			?>
			<hr>
			<?php
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success' && isset($_GET['blacklist'])) {
				// Check whether a login or an IP address was blacklisted and display the appropriate message
				if($_GET['blacklist'] === 'login')
					echo exitNotice('The login was successfully blacklisted.');
				elseif($_GET['blacklist'] === 'ip_address')
					echo exitNotice('The IP address was successfully blacklisted.');
			}
			?>
			<ul class="status-nav">
				<?php
				$keys = array('all', 'success', 'failure');
				$count = array();
				
				foreach($keys as $key) {
					if($key === 'all') {
						if(!is_null($search) && $key === $status)
							$count[$key] = $this->getLoginCount('', $search);
						else
							$count[$key] = $this->getLoginCount();
					} else {
						if(!is_null($search) && $key === $status)
							$count[$key] = $this->getLoginCount($key, $search);
						else
							$count[$key] = $this->getLoginCount($key);
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
			<?php $paged['count'] = ceil($count[$status] / $paged['per_page']); ?>
			<div class="entry-count">
				<?php echo $count[$status] . ' ' . ($count[$status] === 1 ? 'entry' : 'entries'); ?>
			</div>
		</div>
		<table class="data-table">
			<thead>
				<?php
				$table_header_cols = array('Login', 'IP Address', 'Date', 'Status');
				
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				if($status === 'all') {
					if(!is_null($search)) {
						$login_attempts = $rs_query->select('login_attempts', '*', array(
							'login' => array('LIKE', '%' . $search . '%')
						), 'date', 'DESC', array(
							$paged['start'],
							$paged['per_page']
						));
					} else {
						$login_attempts = $rs_query->select('login_attempts', '*', '', 'date', 'DESC', array(
							$paged['start'],
							$paged['per_page']
						));
					}
				} else {
					if(!is_null($search)) {
						$login_attempts = $rs_query->select('login_attempts', '*', array(
							'login' => array('LIKE', '%' . $search . '%'),
							'status' => $status
						), 'date', 'DESC', array(
							$paged['start'],
							$paged['per_page']
						));
					} else {
						$login_attempts = $rs_query->select('login_attempts', '*', array(
							'status' => $status
						), 'date', 'DESC', array(
							$paged['start'],
							$paged['per_page']
						));
					}
				}
				
				foreach($login_attempts as $login_attempt) {
					// Check whether the login or IP address is blacklisted
					$blacklisted = $rs_query->select('login_blacklist', 'COUNT(name)', array(
						'name' => array('IN', $login_attempt['login'], $login_attempt['ip_address'])
					)) > 0;
					
					$actions = array(
						// Blacklist login
						userHasPrivilege('can_create_login_blacklist') ? actionLink('blacklist_login', array(
							'caption' => 'Blacklist Login',
							'id' => $login_attempt['id']
						)) : null,
						// Blacklist IP
						userHasPrivilege('can_create_login_blacklist') ? actionLink('blacklist_ip', array(
							'caption' => 'Blacklist IP',
							'id' => $login_attempt['id']
						)) : null
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						// Login
						tdCell('<strong>' . $login_attempt['login'] . '</strong>' . ($blacklisted ?
							' &mdash; <em>blacklisted</em>' : '') . '<div class="actions">' .
							implode(' &bull; ', $actions) . '</div>', 'login'),
						// IP address
						tdCell($login_attempt['ip_address'], 'ip-address'),
						// Date
						tdCell(formatDate($login_attempt['date'], 'd M Y @ g:i A'), 'date'),
						// Status
						tdCell(ucfirst($login_attempt['status']), 'status')
					);
				}
				
				if(empty($login_attempts))
					echo tableRow(tdCell('There are no login attempts to display.', '', count($table_header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($table_header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($paged['current'], $paged['count']);
	}
	
	/**
	 * Blacklist a user's login.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access public
	 */
	public function blacklistLogin(): void {
		// Extend the Query object
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			// Validate the form data and return any messages
			$message = isset($_POST['submit']) ? $this->validateBlacklistData($_POST, 'login') : '';
			?>
			<div class="heading-wrap">
				<h1>Blacklist Login</h1>
				<?php echo $message; ?>
			</div>
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						// Name (hidden)
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'hidden',
							'name' => 'name',
							'value' => $this->login
						));
						
						// Name
						echo formRow('Name', array('tag' => 'span', 'content' => $this->login));
						
						// Duration
						echo formRow(array('Duration (seconds)', true), array(
							'tag' => 'input',
							'class' => 'text-input required invalid init',
							'name' => 'duration',
							'maxlength' => 15,
							'value' => ($_POST['duration'] ?? '')
						));
						
						// Reason
						echo formRow(array('Reason', true), array(
							'tag' => 'textarea',
							'class' => 'textarea-input required invalid init',
							'name' => 'reason',
							'cols' => 30,
							'rows' => 5,
							'content' => htmlspecialchars(($_POST['reason'] ?? ''))
						));
						
						// Separator
						echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
						
						// Submit button
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'submit',
							'class' => 'submit-input button',
							'name' => 'submit',
							'value' => 'Create Blacklist'
						));
						?>
					</table>
				</form>
			</div>
			<?php
		}
	}
	
	/**
	 * Blacklist a user's IP address.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access public
	 */
	public function blacklistIPAddress(): void {
		// Extend the Query object
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			// Validate the form data and return any messages
			$message = isset($_POST['submit']) ? $this->validateBlacklistData($_POST, 'ip_address') : '';
			?>
			<div class="heading-wrap">
				<h1>Blacklist IP Address</h1>
				<?php echo $message; ?>
			</div>
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						// Name (hidden)
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'hidden',
							'name' => 'name',
							'value' => $this->ip_address
						));
						
						// Name
						echo formRow('Name', array('tag' => 'span', 'content' => $this->ip_address));
						
						// Duration
						echo formRow(array('Duration (seconds)', true), array(
							'tag' => 'input',
							'class' => 'text-input required invalid init',
							'name' => 'duration',
							'maxlength' => 15,
							'value' => ($_POST['duration'] ?? '')
						));
						
						// Reason
						echo formRow(array('Reason', true), array(
							'tag' => 'textarea',
							'class' => 'textarea-input required invalid init',
							'name' => 'reason',
							'cols' => 30,
							'rows' => 5,
							'content' => htmlspecialchars(($_POST['reason'] ?? ''))
						));
						
						// Separator
						echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
						
						// Submit button
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'submit',
							'class' => 'submit-input button',
							'name' => 'submit',
							'value' => 'Create Blacklist'
						));
						?>
					</table>
				</form>
			</div>
			<?php
		}
	}
	
	/**
	 * Construct a list of all blacklisted logins in the database.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access public
	 */
	public function loginBlacklist(): void {
		// Extend the Query object
		global $rs_query;
		
		// Query vars
		$page = $_GET['page'] ?? '';
		$search = $_GET['search'] ?? null;
		$paged = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Login Blacklist</h1>
			<?php
			// Check whether the user has sufficient privileges to create a login blacklist
			if(userHasPrivilege('can_create_login_blacklist')) {
				echo actionLink('create', array(
					'classes' => 'button',
					'caption' => 'Create New',
					'page' => 'blacklist'
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
				echo exitNotice('The login or IP address was successfully whitelisted.');
			
			if(!is_null($search)) {
				$count = $rs_query->select('login_blacklist', 'COUNT(*)', array(
					'name' => array('LIKE', '%' . $search . '%')
				));
			} else {
				$count = $rs_query->select('login_blacklist', 'COUNT(*)');
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
				$table_header_cols = array('Name', 'Attempts', 'Blacklisted', 'Expires', 'Reason');
				
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				if(!is_null($search)) {
					$blacklisted_logins = $rs_query->select('login_blacklist', '*', array(
						'name' => array('LIKE', '%' . $search . '%')
					), 'blacklisted', 'DESC', array(
						$paged['start'],
						$paged['per_page']
					));
				} else {
					$blacklisted_logins = $rs_query->select('login_blacklist', '*', '', 'blacklisted', 'DESC', array(
						$paged['start'],
						$paged['per_page']
					));
				}
				
				foreach($blacklisted_logins as $blacklisted_login) {
					$time = new DateTime($blacklisted_login['blacklisted']);
					$time->add(new DateInterval('PT' . $blacklisted_login['duration'] . 'S'));
					$expiration = $time->format('Y-m-d H:i:s');
					
					// Check whether the blacklist has expired
					if(date('Y-m-d H:i:s') >= $expiration && $blacklisted_login['duration'] !== 0) {
						$rs_query->delete('login_blacklist', array('name' => $blacklisted_login['name']));
						
						$bl_logins = $rs_query->select('login_blacklist', '*', '', 'blacklisted', 'DESC', array(
							$paged['start'],
							$paged['per_page']
						));
						
						if(empty($bl_logins)) {
							echo tableRow(tdCell('There are no blacklisted logins to display.', '',
								count($table_header_cols)
							));
							break;
						} else {
							// Continue to the next blacklisted login
							continue;
						}
					}
					
					$actions = array(
						// Edit
						userHasPrivilege('can_edit_login_blacklist') ? actionLink('edit', array(
							'caption' => 'Edit',
							'page' => 'blacklist',
							'id' => $blacklisted_login['id']
						)) : null,
						// Whitelist
						userHasPrivilege('can_delete_login_blacklist') ? actionLink('whitelist', array(
							'caption' => 'Whitelist',
							'page' => 'blacklist',
							'id' => $blacklisted_login['id']
						)) : null
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						// Name
						tdCell('<strong>' . $blacklisted_login['name'] . '</strong><div class="actions">' .
							implode(' &bull; ', $actions) . '</div>', 'name'),
						// Attempts
						tdCell($blacklisted_login['attempts'], 'attempts'),
						// Blacklisted
						tdCell(formatDate($blacklisted_login['blacklisted'], 'd M Y @ g:i A'), 'blacklisted'),
						// Expiration
						tdCell($blacklisted_login['duration'] === 0 ? 'Indefinite' :
							formatDate($expiration, 'd M Y @ g:i A'), 'expiration'),
						// Reason
						tdCell($blacklisted_login['reason'], 'reason')
					);
				}
				
				if(empty($blacklisted_logins)) {
					echo tableRow(tdCell('There are no blacklisted logins to display.', '',
						count($table_header_cols)
					));
				}
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($table_header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($paged['current'], $paged['count']);
	}
	
	/**
	 * Create a blacklisted login.
	 * @since 1.2.0[b]{ss-03}
	 *
	 * @access public
	 */
	public function createBlacklist(): void {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateBlacklistData($_POST, 'create') : '';
		?>
		<div class="heading-wrap">
			<h1>Create Login Blacklist</h1>
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
					
					// Duration
					echo formRow(array('Duration (seconds)', true), array(
						'tag' => 'input',
						'class' => 'text-input required invalid init',
						'name' => 'duration',
						'maxlength' => 15,
						'value' => ($_POST['duration'] ?? '')
					));
					
					// Reason
					echo formRow(array('Reason', true), array(
						'tag' => 'textarea',
						'class' => 'textarea-input required invalid init',
						'name' => 'reason',
						'cols' => 30,
						'rows' => 5,
						'content' => htmlspecialchars(($_POST['reason'] ?? ''))
					));
					
					// Separator
					echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => 'Create Blacklist'
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Edit a blacklisted login.
	 * @since 1.2.0[b]{ss-02}
	 *
	 * @access public
	 */
	public function editBlacklist(): void {
		// Extend the Query object
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI . '?page=blacklist');
		} else {
			// Validate the form data and return any messages
			$message = isset($_POST['submit']) ? $this->validateBlacklistData($_POST, 'edit') : '';
			?>
			<div class="heading-wrap">
				<h1>Edit Login Blacklist</h1>
				<?php echo $message; ?>
			</div>
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						// Name (hidden)
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'hidden',
							'name' => 'name',
							'value' => $this->name
						));
						
						// Name
						echo formRow('Name', array(
							'tag' => 'span',
							'content' => $this->name
						));
						
						// Duration
						echo formRow(array('Duration (seconds)', true), array(
							'tag' => 'input',
							'class' => 'text-input required invalid init',
							'name' => 'duration',
							'maxlength' => 15,
							'value' => $this->duration
						));
						
						// Reason
						echo formRow(array('Reason', true), array(
							'tag' => 'textarea',
							'class' => 'textarea-input required invalid init',
							'name' => 'reason',
							'cols' => 30,
							'rows' => 5,
							'content' => htmlspecialchars($this->reason)
						));
						
						// Separator
						echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
						
						// Submit button
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'submit',
							'class' => 'submit-input button',
							'name' => 'submit',
							'value' => 'Update Blacklist'
						));
						?>
					</table>
				</form>
			</div>
			<?php
		}
	}
	
	/**
	 * Validate the "Blacklist Login/Blacklist IP Address/Edit Blacklist" form data.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access private
	 * @param array $data
	 * @param string $action
	 * @return string
	 */
	private function validateBlacklistData($data, $action): string {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		if((empty($data['duration']) && $data['duration'] != 0) || empty($data['reason']))
			return exitNotice('REQ', -1);
		
		if($data['name'] === $session['username'] || $data['name'] === $_SERVER['REMOTE_ADDR'])
			return exitNotice('You cannot blacklist yourself!', -1);
		
		if($action !== 'edit' && $this->blacklistExits($data['name'])) {
			return exitNotice('This ' . ($action === 'login' ? 'login ' : 'IP address') .
				' is already blacklisted!', -1);
		}
		
		// Check which action has been submitted
		switch($action) {
			case 'login':
				$attempts = $rs_query->select('login_attempts', 'COUNT(*)', array('login' => $data['name']));
				
				$rs_query->insert('login_blacklist', array(
					'name' => $data['name'],
					'attempts' => $attempts,
					'blacklisted' => 'NOW()',
					'duration' => $data['duration'],
					'reason' => $data['reason']
				));
				
				$session = $rs_query->selectField('users', 'session', array(
					'logic' => 'OR',
					'username' => $data['name'],
					'email' => $data['name']
				));
				
				// Log the user out if they're logged in
				if(!is_null($session)) {
					$rs_query->update('users', array('session' => null), array('session' => $session));
					
					if($_COOKIE['session'] === $session)
						setcookie('session', '', 1, '/');
				}
				
				redirect(ADMIN_URI . '?exit_status=success&blacklist=login');
				break;
			case 'ip_address':
				$attempts = $rs_query->select('login_attempts', 'COUNT(*)', array('ip_address' => $data['name']));
				
				$rs_query->insert('login_blacklist', array(
					'name' => $data['name'],
					'attempts' => $attempts,
					'blacklisted' => 'NOW()',
					'duration' => $data['duration'],
					'reason' => $data['reason']
				));
				
				$logins = $rs_query->select('login_attempts', array('DISTINCT', 'login'), array(
					'ip_address' => $data['name']
				));
				
				foreach($logins as $login) {
					$session = $rs_query->selectRow('users', 'session', array(
						'logic' => 'OR',
						'username' => $login['login'],
						'email' => $login['login']
					));
					
					// Log the user out if they're logged in
					if(!is_null($session)) {
						$rs_query->update('users', array('session' => null), array('session' => $session));
						
						if($_COOKIE['session'] === $session)
							setcookie('session', '', 1, '/');
					}
				}
				
				redirect(ADMIN_URI . '?exit_status=success&blacklist=ip_address');
				break;
			case 'create':
				if(empty($data['name']))
					return exitNotice('REQ', -1);
				
				$attempts = $rs_query->select('login_attempts', 'COUNT(*)', array(
					'logic' => 'OR',
					'login' => $data['name'],
					'ip_address' => $data['name']
				));
				
				$rs_query->insert('login_blacklist', array(
					'name' => $data['name'],
					'attempts' => $attempts,
					'blacklisted' => 'NOW()',
					'duration' => $data['duration'],
					'reason' => $data['reason']
				));
				
				$session = $rs_query->selectField('users', 'session', array(
					'logic' => 'OR',
					'username' => $data['name'],
					'email' => $data['name']
				));
				
				// Log the user out if they're logged in
				if(!is_null($session)) {
					$rs_query->update('users', array('session' => null), array('session' => $session));
					
					if($_COOKIE['session'] === $session)
						setcookie('session', '', 1, '/');
				}
				
				redirect(ADMIN_URI . '?page=blacklist');
				break;
			case 'edit':
				$rs_query->update('login_blacklist', array(
					'duration' => $data['duration'],
					'reason' => $data['reason']
				), array('name' => $data['name']));
				
				// Update the class variables
				foreach($data as $key => $value) $this->$key = $value;
				
				return exitNotice('Blacklist updated! <a href="' . ADMIN_URI .
					'?page=blacklist">Return to list</a>?');
				break;
		}
	}
	
	/**
	 * Whitelist a blacklisted login or IP address.
	 * @since 1.2.0[b]{ss-02}
	 *
	 * @access public
	 */
	public function whitelistLoginIP(): void {
		// Extend the Query object
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI . '?page=blacklist');
		} else {
			$rs_query->delete('login_blacklist', array('id' => $this->id));
			
			redirect(ADMIN_URI . '?page=blacklist&exit_status=success');
		}
	}
	
	/**
	 * Check whether a blacklist already exists in the database.
	 * @since 1.2.0[b]{ss-02}
	 *
	 * @access private
	 * @param string $name
	 * @return bool
	 */
	private function blacklistExits($name): bool {
		// Extend the Query object
		global $rs_query;
		
		return $rs_query->selectRow('login_blacklist', 'COUNT(name)', array('name' => $name)) > 0;
	}
	
	/**
	 * Construct a list of all login rules in the database.
	 * @since 1.2.0[b]{ss-05}
	 *
	 * @access public
	 */
	public function loginRules(): void {
		// Extend the Query object
		global $rs_query;
		
		// Query vars
		$paged = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Login Rules</h1>
			<?php
			// Check whether the user has sufficient privileges to create login rules
			if(userHasPrivilege('can_create_login_rules')) {
				echo actionLink('create', array(
					'classes' => 'button',
					'caption' => 'Create New',
					'page' => 'rules'
				));
			}
			
			adminInfo();
			?>
			<hr>
			<?php
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo exitNotice('The rule was successfully deleted.');
			
			$count = $rs_query->select('login_rules', 'COUNT(*)');
			$paged['count'] = ceil($count / $paged['per_page']);
			?>
			<div class="entry-count">
				<?php echo $count . ' ' . ($count === 1 ? 'entry' : 'entries'); ?>
			</div>
		</div>
		<table class="data-table">
			<thead>
				<?php
				$table_header_cols = array('Rule');
				
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$login_rules = $rs_query->select('login_rules', '*', '', 'attempts', 'ASC', array(
					$paged['start'],
					$paged['per_page']
				));
				
				foreach($login_rules as $login_rule) {
					$actions = array(
						// Edit
						userHasPrivilege('can_edit_login_rules') ? actionLink('edit', array(
							'caption' => 'Edit',
							'page' => 'rules',
							'id' => $login_rule['id']
						)) : null,
						// Delete
						userHasPrivilege('can_delete_login_rules') ? actionLink('delete', array(
							'classes' => 'modal-launch delete-item',
							'data_item' => 'login rule',
							'caption' => 'Delete',
							'page' => 'rules',
							'id' => $login_rule['id']
						)) : null
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						tdCell('If failed login attempts exceed <strong>' . $login_rule['attempts'] .
							'</strong>, blacklist the <strong>' . ($login_rule['type'] === 'ip_address' ?
							'IP address' : $login_rule['type']) . '</strong> ' .
							($login_rule['duration'] !== 0 ? 'for ' : '') . '<strong>' .
							$this->formatDuration($login_rule['duration']) .
							'</strong>.<div class="actions">' . implode(' &bull; ', $actions) . '</div>')
					);
				}
				
				if(empty($login_rules))
					echo tableRow(tdCell('There are no login rules to display.', '', count($table_header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($table_header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($paged['current'], $paged['count']);
		
        include_once PATH . ADMIN . INC . '/modal-delete.php';
	}
	
	/**
	 * Create a login rule.
	 * @since 1.2.0[b]{ss-05}
	 *
	 * @access public
	 */
	public function createRule(): void {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateRuleData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1>Create Login Rule</h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Type
					echo formRow('Type', array(
						'tag' => 'select',
						'class' => 'select-input',
						'name' => 'type',
						'content' => '<option value="login">Login</option><option value="ip_address">IP Address</option>'
					));
					
					// Attempts
					echo formRow(array('Attempts', true), array(
						'tag' => 'input',
						'class' => 'text-input required invalid init',
						'name' => 'attempts',
						'maxlength' => 6,
						'value' => ($_POST['attempts'] ?? '')
					));
					
					// Duration
					echo formRow(array('Duration (seconds)', true), array(
						'tag' => 'input',
						'class' => 'text-input required invalid init',
						'name' => 'duration',
						'maxlength' => 15,
						'value' => ($_POST['duration'] ?? '')
					));
					
					// Separator
					echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => 'Create Rule'
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Edit a login rule.
	 * @since 1.2.0[b]{ss-05}
	 *
	 * @access public
	 */
	public function editRule(): void {
		// Extend the Query object
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI . '?page=rules');
		} else {
			// Validate the form data and return any messages
			$message = isset($_POST['submit']) ? $this->validateRuleData($_POST, $this->id) : '';
			?>
			<div class="heading-wrap">
				<h1>Edit Login Rule</h1>
				<?php echo $message; ?>
			</div>
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						// Type
						echo formRow('Type', array(
							'tag' => 'select',
							'class' => 'select-input',
							'name' => 'type',
							'content' => '<option value="' . $this->type . '">' . ($this->type === 'ip_address' ?
								'IP Address' : ucfirst($this->type)) . '</option>' . ($this->type === 'login' ?
								'<option value="ip_address">IP Address</option>' :
								'<option value="login">Login</option>')
						));
						
						
						echo formRow(array('Attempts', true), array(
							'tag' => 'input',
							'class' => 'text-input required invalid init',
							'name' => 'attempts',
							'maxlength' => 6,
							'value' => $this->attempts
						));
						
						
						echo formRow(array('Duration (seconds)', true), array(
							'tag' => 'input',
							'class' => 'text-input required invalid init',
							'name' => 'duration',
							'maxlength' => 15,
							'value' => $this->duration
						));
						
						// Separator
						echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
						
						// Submit button
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'submit',
							'class' => 'submit-input button',
							'name' => 'submit',
							'value' => 'Update Rule'
						));
						?>
					</table>
				</form>
			</div>
			<?php
		}
	}
	
	/**
	 * Delete a login rule.
	 * @since 1.2.0[b]{ss-05}
	 *
	 * @access public
	 */
	public function deleteRule(): void {
		// Extend the Query object
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI . '?page=rules');
		} else {
			$rs_query->delete('login_rules', array('id' => $this->id));
			
			redirect(ADMIN_URI . '?page=rules&exit_status=success');
		}
	}
	
	/**
	 * Validate the login rules form data.
	 * @since 1.2.0[b]{ss-05}
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function validateRuleData($data, $id = 0): string {
		// Extend the Query object
		global $rs_query;
		
		if(empty($data['attempts']) || (empty($data['duration']) && $data['duration'] != 0))
			return exitNotice('REQ', -1);
		
		if($data['type'] !== 'login' && $data['type'] !== 'ip_address')
			$data['type'] = 'login';
		
		if($id === 0) {
			// New rule
			$insert_id = $rs_query->insert('login_rules', array(
				'type' => $data['type'],
				'attempts' => $data['attempts'],
				'duration' => $data['duration']
			));
			
			redirect(ADMIN_URI . '?page=rules&id=' . $insert_id . '&action=edit');
		} else {
			// Existing rule
			$rs_query->update('login_rules', array(
				'type' => $data['type'],
				'attempts' => $data['attempts'],
				'duration' => $data['duration']
			), array('id' => $id));
			
			// Update the class variables
			foreach($data as $key => $value) $this->$key = $value;
			
			return exitNotice('Rule updated! <a href="' . ADMIN_URI . '?page=rules">Return to list</a>?');
		}
	}
	
	/**
	 * Format a duration in seconds to something more readable.
	 * @since 1.2.0[b]{ss-05}
	 *
	 * @access private
	 * @param int $seconds
	 * @return string
	 */
	private function formatDuration($seconds): string {
		if((int)$seconds !== 0) {
			$time_start = new DateTime('@0');
			$time_end = new DateTime('@' . $seconds);
			$duration = $time_start->diff($time_end);
			
			$date_strings = array(
				'y' => 'year',
				'm' => 'month',
				'd' => 'day',
				'h' => 'hour',
				'i' => 'minute',
				's' => 'second'
			);
			
			foreach($date_strings as $key => &$value) {
				if($duration->$key)
					$value = $duration->$key . ' ' . $value . ($duration->$key > 1 ? 's' : '');
				else
					unset($date_strings[$key]);
			}
			
			return implode(', ', $date_strings);
		} else {
			return 'indefinitely';
		}
	}
	
	/**
	 * Fetch the login attempt count based on a specific status.
	 * @since 1.3.2[b]
	 *
	 * @access private
	 * @param string $status (optional; default: '')
	 * @param string $search (optional; default: '')
	 * @return int
	 */
	private function getLoginCount($status = '', $search = ''): int {
		// Extend the Query class
		global $rs_query;
		
		if(empty($status)) {
			if(!empty($search)) {
				return $rs_query->select('login_attempts', 'COUNT(*)', array(
					'login' => array('LIKE', '%' . $search . '%')
				));
			} else {
				return $rs_query->select('login_attempts', 'COUNT(*)');
			}
		} else {
			if(!empty($search)) {
				return $rs_query->select('login_attempts', 'COUNT(*)', array(
					'login' => array('LIKE', '%' . $search . '%'),
					'status' => $status
				));
			} else {
				return $rs_query->select('login_attempts', 'COUNT(*)', array('status' => $status));
			}
		}
	}
}