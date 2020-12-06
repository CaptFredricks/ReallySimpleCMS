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
	 * The currently queried login attempt or blacklisted login's id.
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
	 * Class constructor.
	 * @since 1.2.0[b]{ss-01}
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
			// Fetch the login attempt from the database
			$login_attempt = $rs_query->selectRow('login_attempts', $cols, array('id'=>$id));
			
			// Loop through the array and set the class variables
			foreach($login_attempt as $key=>$value) $this->$key = $login_attempt[$key];
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
		// Extend the Query object
		global $rs_query;
		
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
						actionLink('blacklist_login', array('caption'=>'Blacklist Login', 'id'=>$login_attempt['id'])),
						actionLink('blacklist_ip', array('caption'=>'Blacklist IP', 'id'=>$login_attempt['id']))
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
			redirect('logins.php');
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
			redirect('logins.php');
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
	 * Validate the "Blacklist Login/Blacklist IP Address" form data.
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
		
		// make sure no duplicates
		
		// Check which action has been submitted
		if($action === 'login') {
			// Fetch the number of login attempts associated with the blacklisted login in the database
			$attempts = $rs_query->select('login_attempts', 'COUNT(*)', array('login'=>$data['name']));
			
			// Insert the new blacklisted login into the database
			$rs_query->insert('login_blacklist', array('name'=>$data['name'], 'attempts'=>$attempts, 'blacklisted'=>'NOW()', 'duration'=>$data['duration'], 'reason'=>$data['reason']));
			
			// Redirect to the "Login Attempts" page with an appropriate exit status
			redirect(ADMIN_URI.'?exit_status=success&blacklist=login');
		} elseif($action === 'ip_address') {
			// Fetch the number of login attempts associated with the blacklisted IP address in the database
			$attempts = $rs_query->select('login_attempts', 'COUNT(*)', array('ip_address'=>$data['name']));
			
			// Insert the new blacklisted login into the database
			$rs_query->insert('login_blacklist', array('name'=>$data['name'], 'attempts'=>$attempts, 'blacklisted'=>'NOW()', 'duration'=>$data['duration'], 'reason'=>$data['reason']));
			
			// Redirect to the "Login Attempts" page with an appropriate exit status
			redirect(ADMIN_URI.'?exit_status=success&blacklist=ip_address');
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
		// Extend the Query object
		global $rs_query;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Login Blacklist</h1>
			<?php
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
					$time->add(new DateInterval('PT'.($blacklisted_login['duration'] ?? 0).'S'));
					
					// Format the expiration date
					$expiration = $time->format('Y-m-d H:i:s');
					
					// Set up the action links
					$actions = array(
						actionLink('edit', array('caption'=>'Edit', 'id'=>$blacklisted_login['id'])),
						actionLink('whitelist', array('caption'=>'Whitelist', 'id'=>$blacklisted_login['id']))
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
}