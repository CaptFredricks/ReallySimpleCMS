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
	 * @return null
	 */
	public function __construct($page, $id) {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the 'delete_old_login_attempts' setting is turned on
		if(getSetting('delete_old_login_attempts', false)) {
			// Fetch all login attempts from the database
			$login_attempts = $rs_query->select('login_attempts', array('id', 'date'));
			
			// Loop through the login attempts
			foreach($login_attempts as $login_attempt) {
				// Create a DateTime object
				$time = new DateTime();
				
				// Subtract 30 days from the current date
				$time->sub(new DateInterval('P30D'));
				
				// Format the threshold date
				$threshold = $time->format('Y-m-d H:i:s');
				
				// Check whether the login attempt has expired
				if($threshold > $login_attempt['date']) {
					// Delete the login attempt from the database
					$rs_query->delete('login_attempts', array('id'=>$login_attempt['id']));
				}
			}
		}
		
		// Check whether the id is '0'
		if($id !== 0) {
			// Create an array of columns to fetch from the database
			$cols = array_keys(get_object_vars($this));
			
			// Check whether the user is on the "List Blacklist" page
			if($page === 'blacklist') {
				// Exclude columns from the 'login_attempts' and 'login_rules' tables
				$exclude = array('login', 'ip_address', 'type', 'attempts');
				
				// Update the columns array
				$cols = array_diff($cols, $exclude);
				
				// Fetch the blacklisted login from the database
				$blacklisted_login = $rs_query->selectRow('login_blacklist', $cols, array('id'=>$id));
				
				// Loop through the array and set the class variables
				foreach($blacklisted_login as $key=>$value) $this->$key = $blacklisted_login[$key];
			} // Check whether the user is on the "List Rules" page
			elseif($page === 'rules') {
				// Exclude columns from the 'login_attempts' and 'login_blacklist' tables
				$exclude = array('login', 'ip_address', 'name', 'reason');
				
				// Update the columns array
				$cols = array_diff($cols, $exclude);
				
				// Fetch the login rule from the database
				$login_rule = $rs_query->selectRow('login_rules', $cols, array('id'=>$id));
				
				// Loop through the array and set the class variables
				foreach($login_rule as $key=>$value) $this->$key = $login_rule[$key];
			} else {
				// Exclude columns from the 'login_blacklist' and 'login_rules' tables
				$exclude = array('name', 'duration', 'reason', 'type', 'attempts');
				
				// Update the columns array
				$cols = array_diff($cols, $exclude);
				
				// Fetch the login attempt from the database
				$login_attempt = $rs_query->selectRow('login_attempts', $cols, array('id'=>$id));
				
				// Loop through the array and set the class variables
				foreach($login_attempt as $key=>$value) $this->$key = $login_attempt[$key];
			}
		}
	}
	
	/**
	 * Construct a list of all login attempts in the database.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access public
	 * @return null
	 */
	public function loginAttempts() {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Login Attempts</h1>
			<?php
			// Check whether any status messages have been returned
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success' && isset($_GET['blacklist'])) {
				// Check whether a login or an IP address was blacklisted and display the appropriate message
				if($_GET['blacklist'] === 'login')
					echo statusMessage('The login was successfully blacklisted.', true);
				elseif($_GET['blacklist'] === 'ip_address')
					echo statusMessage('The IP address was successfully blacklisted.', true);
			}
			
			// Fetch the login attempt entry count from the database
			$count = $rs_query->select('login_attempts', 'COUNT(*)');
			
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
				$table_header_cols = array('Login', 'IP Address', 'Date', 'Status');
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all login attempts from the database
				$login_attempts = $rs_query->select('login_attempts', '*', '', 'date', 'DESC', array($page['start'], $page['per_page']));
				
				// Loop through the login attempts
				foreach($login_attempts as $login_attempt) {
					// Check whether the login or IP address is blacklisted
					$blacklisted = $rs_query->select('login_blacklist', 'COUNT(name)', array('name'=>array('IN', $login_attempt['login'], $login_attempt['ip_address']))) > 0;
					
					// Set up the action links
					$actions = array(
						userHasPrivilege($session['role'], 'can_create_login_blacklist') ? actionLink('blacklist_login', array('caption'=>'Blacklist Login', 'id'=>$login_attempt['id'])) : null,
						userHasPrivilege($session['role'], 'can_create_login_blacklist') ? actionLink('blacklist_ip', array('caption'=>'Blacklist IP', 'id'=>$login_attempt['id'])) : null
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						tableCell('<strong>'.$login_attempt['login'].'</strong>'.($blacklisted ? ' &mdash; <em>blacklisted</em>' : '').'<div class="actions">'.implode(' &bull; ', $actions).'</div>', 'login'),
						tableCell($login_attempt['ip_address'], 'ip-address'),
						tableCell(formatDate($login_attempt['date'], 'd M Y @ g:i A'), 'date'),
						tableCell(ucfirst($login_attempt['status']), 'status')
					);
				}
				
				// Display a notice if no login attempts are found
				if(empty($login_attempts))
					echo tableRow(tableCell('There are no login attempts to display.', '', count($table_header_cols)));
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
	}
	
	/**
	 * Blacklist a user's login.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access public
	 * @return
	 */
	public function blacklistLogin() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the login attempt's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "Login Attempts" page
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
						echo formRow('', array('tag'=>'input', 'type'=>'hidden', 'name'=>'name', 'value'=>$this->login));
						echo formRow('Name', array('tag'=>'span', 'content'=>$this->login));
						echo formRow(array('Duration (seconds)', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'duration', 'maxlength'=>15, 'value'=>($_POST['duration'] ?? '')));
						echo formRow(array('Reason', true), array('tag'=>'textarea', 'class'=>'textarea-input required invalid init', 'name'=>'reason', 'cols'=>30, 'rows'=>5, 'content'=>htmlspecialchars(($_POST['reason'] ?? ''))));
						echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
						echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create Blacklist'));
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
	 * @return
	 */
	public function blacklistIPAddress() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the login attempt's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "Login Attempts" page
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
						echo formRow('', array('tag'=>'input', 'type'=>'hidden', 'name'=>'name', 'value'=>$this->ip_address));
						echo formRow('Name', array('tag'=>'span', 'content'=>$this->ip_address));
						echo formRow(array('Duration (seconds)', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'duration', 'maxlength'=>15, 'value'=>($_POST['duration'] ?? '')));
						echo formRow(array('Reason', true), array('tag'=>'textarea', 'class'=>'textarea-input required invalid init', 'name'=>'reason', 'cols'=>30, 'rows'=>5, 'content'=>htmlspecialchars(($_POST['reason'] ?? ''))));
						echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
						echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create Blacklist'));
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
	 * @return null
	 */
	public function loginBlacklist() {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Login Blacklist</h1>
			<?php
			// Check whether the user has sufficient privileges to create a login blacklist and create an action link if so
			if(userHasPrivilege($session['role'], 'can_create_login_blacklist'))
				echo actionLink('create', array('classes'=>'button', 'caption'=>'Create New', 'page'=>'blacklist'));
			
			// Check whether any status messages have been returned and display them if so
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The login or IP address was successfully whitelisted.', true);
			
			// Fetch the login blacklist entry count from the database
			$count = $rs_query->select('login_blacklist', 'COUNT(*)');
			
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
				$table_header_cols = array('Name', 'Attempts', 'Blacklisted', 'Expires', 'Reason');
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all blacklisted logins from the database
				$blacklisted_logins = $rs_query->select('login_blacklist', '*', '', 'blacklisted', 'DESC', array($page['start'], $page['per_page']));
				
				// Loop through the blacklisted logins
				foreach($blacklisted_logins as $blacklisted_login) {
					// Create a DateTime object
					$time = new DateTime($blacklisted_login['blacklisted']);
					
					// Add the blacklist duration to the time to find the expiration
					$time->add(new DateInterval('PT'.$blacklisted_login['duration'].'S'));
					
					// Format the expiration date
					$expiration = $time->format('Y-m-d H:i:s');
					
					// Check whether the blacklist has expired
					if(date('Y-m-d H:i:s') >= $expiration && $blacklisted_login['duration'] !== 0) {
						// Delete the blacklisted login from the database
						$rs_query->delete('login_blacklist', array('name'=>$blacklisted_login['name']));
						
						// Continue to the next blacklisted login
						continue;
					}
					
					// Set up the action links
					$actions = array(
						userHasPrivilege($session['role'], 'can_edit_login_blacklist') ? actionLink('edit', array('caption'=>'Edit', 'page'=>'blacklist', 'id'=>$blacklisted_login['id'])) : null,
						userHasPrivilege($session['role'], 'can_delete_login_blacklist') ? actionLink('whitelist', array('caption'=>'Whitelist', 'page'=>'blacklist', 'id'=>$blacklisted_login['id'])) : null
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						tableCell('<strong>'.$blacklisted_login['name'].'</strong><div class="actions">'.implode(' &bull; ', $actions).'</div>', 'name'),
						tableCell($blacklisted_login['attempts'], 'attempts'),
						tableCell(formatDate($blacklisted_login['blacklisted'], 'd M Y @ g:i A'), 'blacklisted'),
						tableCell($blacklisted_login['duration'] === 0 ? 'Indefinite' : formatDate($expiration, 'd M Y @ g:i A'), 'expiration'),
						tableCell($blacklisted_login['reason'], 'reason')
					);
				}
				
				// Display a notice if no blacklisted logins are found
				if(empty($blacklisted_logins))
					echo tableRow(tableCell('There are no blacklisted logins to display.', '', count($table_header_cols)));
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
	}
	
	/**
	 * Create a blacklisted login.
	 * @since 1.2.0[b]{ss-03}
	 *
	 * @access public
	 * @return null
	 */
	public function createBlacklist() {
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
					echo formRow(array('Name', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>($_POST['name'] ?? '')));
					echo formRow(array('Duration (seconds)', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'duration', 'maxlength'=>15, 'value'=>($_POST['duration'] ?? '')));
					echo formRow(array('Reason', true), array('tag'=>'textarea', 'class'=>'textarea-input required invalid init', 'name'=>'reason', 'cols'=>30, 'rows'=>5, 'content'=>htmlspecialchars(($_POST['reason'] ?? ''))));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create Blacklist'));
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
	 * @return null
	 */
	public function editBlacklist() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the blacklisted login's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "Login Blacklist" page
			redirect(ADMIN_URI.'?page=blacklist');
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
						echo formRow('', array('tag'=>'input', 'type'=>'hidden', 'name'=>'name', 'value'=>$this->name));
						echo formRow('Name', array('tag'=>'span', 'content'=>$this->name));
						echo formRow(array('Duration (seconds)', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'duration', 'maxlength'=>15, 'value'=>$this->duration));
						echo formRow(array('Reason', true), array('tag'=>'textarea', 'class'=>'textarea-input required invalid init', 'name'=>'reason', 'cols'=>30, 'rows'=>5, 'content'=>htmlspecialchars($this->reason)));
						echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
						echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Blacklist'));
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
	 * @return null
	 */
	private function validateBlacklistData($data, $action) {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if((empty($data['duration']) && $data['duration'] != 0) || empty($data['reason']))
			return statusMessage('R');
		
		// Make sure the login or IP address is not already blacklisted
		if($action !== 'edit' && $this->blacklistExits($data['name']))
			return statusMessage('This '.($action === 'login' ? 'login ' : 'IP address').' is already blacklisted!');
		
		// Check which action has been submitted
		switch($action) {
			case 'login':
				// Fetch the number of login attempts associated with the blacklisted login in the database
				$attempts = $rs_query->select('login_attempts', 'COUNT(*)', array('login'=>$data['name']));
				
				// Insert the new blacklisted login into the database
				$rs_query->insert('login_blacklist', array('name'=>$data['name'], 'attempts'=>$attempts, 'blacklisted'=>'NOW()', 'duration'=>$data['duration'], 'reason'=>$data['reason']));
				
				// Fetch the blacklisted user from the database
				$session = $rs_query->selectField('users', 'session', array('logic'=>'OR', 'username'=>$data['name'], 'email'=>$data['name']));
				
				// Check whether the user's session is null
				if(!is_null($session)) {
					// Set the user's session to null in the database
					$rs_query->update('users', array('session'=>null), array('session'=>$session));
					
					// Check whether the cookie's value matches the session value and delete it if so
					if($_COOKIE['session'] === $session)
						setcookie('session', '', 1, '/');
				}
				
				// Redirect to the "Login Attempts" page with an appropriate exit status
				redirect(ADMIN_URI.'?exit_status=success&blacklist=login');
				break;
			case 'ip_address':
				// Fetch the number of login attempts associated with the blacklisted IP address in the database
				$attempts = $rs_query->select('login_attempts', 'COUNT(*)', array('ip_address'=>$data['name']));
				
				// Insert the new blacklisted login into the database
				$rs_query->insert('login_blacklist', array('name'=>$data['name'], 'attempts'=>$attempts, 'blacklisted'=>'NOW()', 'duration'=>$data['duration'], 'reason'=>$data['reason']));
				
				// Fetch all logins associated with the IP address from the database
				$logins = $rs_query->select('login_attempts', array('DISTINCT', 'login'), array('ip_address'=>$data['name']));
				
				// Loop through the logins
				foreach($logins as $login) {
					// Fetch the blacklisted user from the database
					$session = $rs_query->selectRow('users', 'session', array('logic'=>'OR', 'username'=>$login['login'], 'email'=>$login['login']));
				
					// Check whether the user's session is null
					if(!is_null($session)) {
						// Set the user's session to null in the database
						$rs_query->update('users', array('session'=>null), array('session'=>$session));
						
						// Check whether the cookie's value matches the session value and delete it if so
						if($_COOKIE['session'] === $session)
							setcookie('session', '', 1, '/');
					}
				}
				
				// Redirect to the "Login Attempts" page with an appropriate exit status
				redirect(ADMIN_URI.'?exit_status=success&blacklist=ip_address');
				break;
			case 'create':
				// Fetch the number of login attempts associated with the blacklisted login or IP address in the database
				$attempts = $rs_query->select('login_attempts', 'COUNT(*)', array('logic'=>'OR', 'login'=>$data['name'], 'ip_address'=>$data['name']));
				
				// Insert the new blacklisted login into the database
				$rs_query->insert('login_blacklist', array('name'=>$data['name'], 'attempts'=>$attempts, 'blacklisted'=>'NOW()', 'duration'=>$data['duration'], 'reason'=>$data['reason']));
				
				// Redirect to the "Login Blacklist" page
				redirect(ADMIN_URI.'?page=blacklist');
				break;
			case 'edit':
				// Update the blacklisted login in the database
				$rs_query->update('login_blacklist', array('duration'=>$data['duration'], 'reason'=>$data['reason']), array('name'=>$data['name']));
				
				// Update the class variables
				foreach($data as $key=>$value) $this->$key = $value;
				
				// Return a status message
				return statusMessage('Blacklist updated! <a href="'.ADMIN_URI.'?page=blacklist">Return to list</a>?', true);
				break;
		}
	}
	
	/**
	 * Whitelist a blacklisted login or IP address.
	 * @since 1.2.0[b]{ss-02}
	 *
	 * @access public
	 * @return null
	 */
	public function whitelistLoginIP() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the blacklisted login's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "Login Blacklist" page
			redirect(ADMIN_URI.'?page=blacklist');
		} else {
			// Delete the blacklisted login from the database
			$rs_query->delete('login_blacklist', array('id'=>$this->id));
			
			// Redirect to the "Login Blacklist" page with an appropriate exit status
			redirect(ADMIN_URI.'?page=blacklist&exit_status=success');
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
	private function blacklistExits($name) {
		// Extend the Query object
		global $rs_query;
		
		// Return true if the blacklist appears in the database
		return $rs_query->selectRow('login_blacklist', 'COUNT(name)', array('name'=>$name)) > 0;
	}
	
	/**
	 * Construct a list of all login rules in the database.
	 * @since 1.2.0[b]{ss-05}
	 *
	 * @access public
	 * @return null
	 */
	public function loginRules() {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Login Rules</h1>
			<?php
			// Check whether the user has sufficient privileges to create a login rule and create an action link if so
			if(userHasPrivilege($session['role'], 'can_create_login_rules'))
				echo actionLink('create', array('classes'=>'button', 'caption'=>'Create New', 'page'=>'rules'));
			
			// Check whether any status messages have been returned and display them if so
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The rule was successfully deleted.', true);
			
			// Fetch the login rules entry count from the database
			$count = $rs_query->select('login_rules', 'COUNT(*)');
			
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
				$table_header_cols = array('Rule');
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all login rules from the database
				$login_rules = $rs_query->select('login_rules', '*', '', 'attempts', 'ASC', array($page['start'], $page['per_page']));
				
				// Loop through the login rules
				foreach($login_rules as $login_rule) {
					// Check whether the login or IP address is blacklisted
					$blacklisted = $rs_query->select('login_blacklist', 'COUNT(name)', array('name'=>array('IN', $login_rule['login'], $login_rule['ip_address']))) > 0;
					
					// Set up the action links
					$actions = array(
						userHasPrivilege($session['role'], 'can_edit_login_rules') ? actionLink('edit', array('caption'=>'Edit', 'page'=>'rules', 'id'=>$login_rule['id'])) : null,
						userHasPrivilege($session['role'], 'can_delete_login_rules') ? actionLink('delete', array('classes'=>'modal-launch delete-item', 'data_item'=>'login rule', 'caption'=>'Delete', 'page'=>'rules', 'id'=>$login_rule['id'])) : null
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						tableCell('If failed login attempts exceed <strong>'.$login_rule['attempts'].'</strong>, blacklist the <strong>'.($login_rule['type'] === 'ip_address' ? 'IP address' : $login_rule['type']).'</strong> '.($login_rule['duration'] !== 0 ? 'for ' : '').'<strong>'.$this->formatDuration($login_rule['duration']).'</strong>.<div class="actions">'.implode(' &bull; ', $actions).'</div>')
					);
				}
				
				// Display a notice if no login rules are found
				if(empty($login_rules))
					echo tableRow(tableCell('There are no login rules to display.', '', count($table_header_cols)));
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
	 * Create a login rule.
	 * @since 1.2.0[b]{ss-05}
	 *
	 * @access public
	 * @return null
	 */
	public function createRule() {
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
					echo formRow('Type', array('tag'=>'select', 'class'=>'select-input', 'name'=>'type', 'content'=>'<option value="login">Login</option><option value="ip_address">IP Address</option>'));
					echo formRow(array('Attempts', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'attempts', 'maxlength'=>6, 'value'=>($_POST['attempts'] ?? '')));
					echo formRow(array('Duration (seconds)', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'duration', 'maxlength'=>15, 'value'=>($_POST['duration'] ?? '')));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create Rule'));
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
	 * @return null
	 */
	public function editRule() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the login rule's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "Login Rules" page
			redirect(ADMIN_URI.'?page=rules');
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
						echo formRow('Type', array('tag'=>'select', 'class'=>'select-input', 'name'=>'type', 'content'=>'<option value="'.$this->type.'">'.($this->type === 'ip_address' ? 'IP Address' : ucfirst($this->type)).'</option>'.($this->type === 'login' ? '<option value="ip_address">IP Address</option>' : '<option value="login">Login</option>')));
						echo formRow(array('Attempts', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'attempts', 'maxlength'=>6, 'value'=>$this->attempts));
						echo formRow(array('Duration (seconds)', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'duration', 'maxlength'=>15, 'value'=>$this->duration));
						echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
						echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Rule'));
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
	 * @return null
	 */
	public function deleteRule() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the login rule's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "Login Rules" page
			redirect(ADMIN_URI.'?page=rules');
		} else {
			// Delete the login rule from the database
			$rs_query->delete('login_rules', array('id'=>$this->id));
			
			// Redirect to the "Login Rules" page with an appropriate exit status
			redirect(ADMIN_URI.'?page=rules&exit_status=success');
		}
	}
	
	/**
	 * Validate the login rules form data.
	 * @since 1.2.0[b]{ss-05}
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return null|string (null on $id == 0; string on $id != 0)
	 */
	private function validateRuleData($data, $id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['attempts']) || (empty($data['duration']) && $data['duration'] != 0))
			return statusMessage('R');
		
		// Make sure the rule has a valid type
		if($data['type'] !== 'login' && $data['type'] !== 'ip_address')
			$data['type'] = 'login';
		
		if($id === 0) {
			// Insert the new login rule into the database
			$insert_id = $rs_query->insert('login_rules', array('type'=>$data['type'], 'attempts'=>$data['attempts'], 'duration'=>$data['duration']));
			
			// Redirect to the appropriate "Edit Login Rule" page
			redirect(ADMIN_URI.'?page=rules&id='.$insert_id.'&action=edit');
		} else {
			// Update the login rule in the database
			$rs_query->update('login_rules', array('type'=>$data['type'], 'attempts'=>$data['attempts'], 'duration'=>$data['duration']), array('id'=>$id));
			
			// Update the class variables
			foreach($data as $key=>$value) $this->$key = $value;
			
			// Return a status message
			return statusMessage('Rule updated! <a href="'.ADMIN_URI.'?page=rules">Return to list</a>?', true);
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
	private function formatDuration($seconds) {
		// Check whether the seconds are equal to '0'
		if((int)$seconds !== 0) {
			// Create a DateTime object for the starting time
			$time_start = new DateTime('@0');
			
			// Create a DateTime object for the ending time
			$time_end = new DateTime('@'.$seconds);
			
			// Determine the duration
			$duration = $time_start->diff($time_end);
			
			// Create an array of date format strings
			$date_strings = array('y'=>'year', 'm'=>'month', 'd'=>'day', 'h'=>'hour', 'i'=>'minute', 's'=>'second');
			
			// Loop through the strings
			foreach($date_strings as $key=>&$value) {
				// Check whether the key is set in the duration
				if($duration->$key) {
					// Format the value
					$value = $duration->$key.' '.$value.($duration->$key > 1 ? 's' : '');
				} else {
					// Remove the key and its value from the array
					unset($date_strings[$key]);
				}
			}
			
			// Return the formatted duration
			return implode(', ', $date_strings);
		} else {
			// Return 'indefinitely' as the duration
			return 'indefinitely';
		}
	}
}