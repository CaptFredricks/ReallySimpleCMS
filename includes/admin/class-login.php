<?php
/**
 * Admin class used to implement the Login object.
 * Logins are attempts by registered users to gain access to the admin dashboard via the Log In page.
 * Users must enter their username, password, and a captcha properly to successfully log in.
 * @since 1.2.0-beta_snap-01
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_login
 *
 * ## VARIABLES [12] ##
 * - private int $id
 * - private string $login
 * - private string $ip_address
 * - private string $name
 * - private int $duration
 * - private string $reason
 * - private string $type
 * - private int $attempts
 * - private array $admin_page
 * - private string $action
 * - private array $paged
 * - private string $page
 *
 * ## METHODS [23] ##
 * - public __construct(int $id, string $action, string $page)
 * { LISTS, FORMS, & ACTIONS [11] }
 * - public loginAttempts(): void
 * - public blacklistLogin(): void
 * - public blacklistIPAddress(): void
 * - public loginBlacklist(): void
 * - public createBlacklist(): void
 * - public editBlacklist(): void
 * - public whitelistLoginIP(): void
 * - public loginRules(): void
 * - public createRule(): void
 * - public editRule(): void
 * - public deleteRule(): void
 * { VALIDATION [2] }
 * - private validateBlacklistSubmission(array $data): string
 * - private validateRuleSubmission(array $data): string
 * { MISCELLANEOUS [9] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private blacklistExists(string $name): bool
 * - private formatDuration(int $seconds): string
 * - private getResultsAttempts(string $status, ?string $search, bool $all): array
 * - private getResultsBlacklist(?string $search, bool $all): array
 * - private getResultsRules(bool $all): array
 * - private getEntryCount(string $status, ?string $search): int
 * - private getActionLinks(array $login): string
 */
namespace Admin;

class Login {
	/**
	 * The currently queried login attempt, blacklisted login, or login rule's id.
	 * @since 1.2.0-beta_snap-01
	 *
	 * @access private
	 * @var int
	 */
	private $id;
	
	/**
	 * The currently queried login attempt's login (username or email).
	 * @since 1.2.0-beta_snap-01
	 *
	 * @access private
	 * @var string
	 */
	private $login;
	
	/**
	 * The currently queried login attempt's IP address.
	 * @since 1.2.0-beta_snap-01
	 *
	 * @access private
	 * @var string
	 */
	private $ip_address;
	
	/**
	 * The currently queried blacklisted login's name.
	 * @since 1.2.0-beta_snap-02
	 *
	 * @access private
	 * @var string
	 */
	private $name;
	
	/**
	 * The currently queried blacklisted login or login rule's duration.
	 * @since 1.2.0-beta_snap-02
	 *
	 * @access private
	 * @var int
	 */
	private $duration;
	
	/**
	 * The currently queried blacklisted login's reason.
	 * @since 1.2.0-beta_snap-02
	 *
	 * @access private
	 * @var string
	 */
	private $reason;
	
	/**
	 * The currently queried login rule's type.
	 * @since 1.2.0-beta_snap-05
	 *
	 * @access private
	 * @var string
	 */
	private $type;
	
	/**
	 * The currently queried login rule's attempts.
	 * @since 1.2.0-beta_snap-05
	 *
	 * @access private
	 * @var int
	 */
	private $attempts;
	
	/**
	 * The admin page's data.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @var array
	 */
	private $admin_page = array();
	
	/**
	 * The current action.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $action;
	
	/**
	 * The pagination.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var array
	 */
	private $paged = array();
	
	/**
	 * The current login settings page.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $page;
	
	/**
	 * Class constructor.
	 * @since 1.2.0-beta_snap-01
	 *
	 * @access public
	 * @param int $id -- The login's id.
	 * @param string $action -- The current action.
	 * @param string $page -- The current login settings page.
	 */
	public function __construct(int $id, string $action, string $page) {
		global $rs_query, $rs_admin_pages;
		
		$this->action = $action;
		$this->page = $page;
		$this->admin_page = $rs_admin_pages[basename($_SERVER['PHP_SELF'], '.php')];
		var_dump($this->admin_page);
		
		if(getSetting('delete_old_login_attempts')) {
			$login_attempts = $rs_query->select(getTable('la'), array('id', 'date'));
			
			foreach($login_attempts as $login_attempt) {
				$time = new \DateTime();
				
				// Subtract 30 days from the current date
				$time->sub(new \DateInterval('P30D'));
				
				$threshold = $time->format('Y-m-d H:i:s');
				
				// Delete the login attempt if it's expired
				if($threshold > $login_attempt['date']) {
					$rs_query->delete(getTable('la'), array(
						'id' => $login_attempt['id']
					));
				}
			}
		}
		
		if($id > 0) {
			$cols = array_keys(get_object_vars($this));
			$exclude_all = array('admin_page', 'action', 'paged', 'page');
			$cols = array_diff($cols, $exclude_all);
			
			switch($this->page) {
				case 'blacklist':
					$exclude = array('login', 'ip_address', 'type', 'attempts');
					$cols = array_diff($cols, $exclude);
					
					$blacklisted_login = $rs_query->selectRow(getTable('lb'), $cols, array(
						'id' => $id
					));
					
					foreach($blacklisted_login as $key => $value) $this->$key = $blacklisted_login[$key];
					break;
				case 'rules':
					$exclude = array('login', 'ip_address', 'name', 'reason');
					$cols = array_diff($cols, $exclude);
					
					$login_rule = $rs_query->selectRow(getTable('lr'), $cols, array(
						'id' => $id
					));
					
					foreach($login_rule as $key => $value) $this->$key = $login_rule[$key];
					break;
				default:
					$exclude = array('name', 'duration', 'reason', 'type', 'attempts');
					$cols = array_diff($cols, $exclude);
					
					$login_attempt = $rs_query->selectRow(getTable('la'), $cols, array(
						'id' => $id
					));
					
					foreach($login_attempt as $key => $value) $this->$key = $login_attempt[$key];
			}
		} else {
			$this->id = 0;
		}
	}
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct a list of all login attempts in the database.
	 * @since 1.2.0-beta_snap-01
	 *
	 * @access public
	 */
	public function loginAttempts(): void {
		global $rs_query;
		
		// Query vars
		$status = $_GET['status'] ?? 'all';
		$search = $_GET['search'] ?? null;
		$this->paged = paginate((int)($_GET['paged'] ?? 1));
		
		$this->pageHeading();
		?>
		<table class="data-table">
			<thead>
				<?php
				$header_cols = array(
					'login' => 'Login',
					'ip-address' => 'IP Address',
					'date' => 'Date',
					'status' => 'Status'
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$login_attempts = $this->getResultsAttempts($status, $search);
				
				foreach($login_attempts as $login_attempt) {
					list($la_login, $la_ip_address) = array(
						$login_attempt['login'],
						$login_attempt['ip_address']
					);
					
					// Check whether the login or IP address is blacklisted
					$blacklisted = $rs_query->select(getTable('lb'), 'COUNT(name)', array(
						'name' => array('IN', $la_login, $la_ip_address)
					)) > 0;
					
					echo tableRow(
						// Login
						tdCell(domTag('strong', array(
							'content' => $la_login
						)) . ($blacklisted ? ' &mdash; ' . domTag('em', array(
							'content' => 'blacklisted'
						)) : '') . domTag('div', array(
							'class' => 'actions',
							'content' => $this->getActionLinks($login_attempt)
						)), 'login'),
						// IP address
						tdCell($la_ip_address, 'ip-address'),
						// Date
						tdCell(formatDate($login_attempt['date'], 'd M Y @ g:i A'), 'date'),
						// Status
						tdCell(ucfirst($login_attempt['status']), 'status')
					);
				}
				
				if(empty($login_attempts))
					echo tableRow(tdCell('There are no login attempts to display.', '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($this->paged['current'], $this->paged['count']);
	}
	
	/**
	 * Blacklist a user's login.
	 * @since 1.2.0-beta_snap-01
	 *
	 * @access public
	 */
	public function blacklistLogin(): void {
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$this->pageHeading();
		?>
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
					
					// Duration
					echo formRow(array('Duration (seconds)', true), array(
						'tag' => 'input',
						'id' => 'duration-field',
						'class' => 'text-input required invalid init',
						'name' => 'duration',
						'maxlength' => 15,
						'value' => ($_POST['duration'] ?? '')
					));
					
					// Reason
					echo formRow(array('Reason', true), array(
						'tag' => 'textarea',
						'id' => 'reason-field',
						'class' => 'textarea-input required invalid init',
						'name' => 'reason',
						'cols' => 30,
						'rows' => 5,
						'content' => htmlspecialchars(($_POST['reason'] ?? ''))
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
	}
	
	/**
	 * Blacklist a user's IP address.
	 * @since 1.2.0-beta_snap-01
	 *
	 * @access public
	 */
	public function blacklistIPAddress(): void {
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$this->pageHeading();
		?>
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
					
					// Duration
					echo formRow(array('Duration (seconds)', true), array(
						'tag' => 'input',
						'id' => 'duration-field',
						'class' => 'text-input required invalid init',
						'name' => 'duration',
						'maxlength' => 15,
						'value' => ($_POST['duration'] ?? '')
					));
					
					// Reason
					echo formRow(array('Reason', true), array(
						'tag' => 'textarea',
						'id' => 'reason-field',
						'class' => 'textarea-input required invalid init',
						'name' => 'reason',
						'cols' => 30,
						'rows' => 5,
						'content' => htmlspecialchars(($_POST['reason'] ?? ''))
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
	}
	
	/**
	 * Construct a list of all blacklisted logins in the database.
	 * @since 1.2.0-beta_snap-01
	 *
	 * @access public
	 */
	public function loginBlacklist(): void {
		global $rs_query;
		
		// Query vars
		$page = $_GET['page'] ?? '';
		$search = $_GET['search'] ?? null;
		$this->paged = paginate((int)($_GET['paged'] ?? 1));
		
		$this->pageHeading();
		?>
		<table class="data-table">
			<thead>
				<?php
				$header_cols = array(
					'name' => 'Name',
					'attempts' => 'Attempts',
					'blacklisted' => 'Blacklisted',
					'expiration' => 'Expires',
					'reason' => 'Reason'
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$blacklisted_logins = $this->getResultsBlacklist($search);
				
				foreach($blacklisted_logins as $blacklisted_login) {
					list($lb_name, $lb_blacklisted, $lb_duration) = array(
						$blacklisted_login['name'],
						$blacklisted_login['blacklisted'],
						$blacklisted_login['duration']
					);
					
					$time = new \DateTime($lb_blacklisted);
					$time->add(new \DateInterval('PT' . $lb_duration . 'S'));
					$expiration = $time->format('Y-m-d H:i:s');
					
					// Check whether the blacklist has expired
					if(date('Y-m-d H:i:s') >= $expiration && $lb_duration !== 0) {
						$rs_query->delete(getTable('lb'), array(
							'name' => $lb_name
						));
						
						$bl_logins = $rs_query->select(getTable('lb'), '*', array(), array(
							'order_by' => 'blacklisted',
							'order' => 'DESC',
							'limit' => array($this->paged['start'], $this->paged['per_page'])
						));
						
						if(empty($bl_logins)) {
							echo tableRow(tdCell('There are no blacklisted logins to display.', '',
								count($header_cols)
							));
							break;
						} else {
							// Continue to the next blacklisted login
							continue;
						}
					}
					
					echo tableRow(
						// Name
						tdCell(domTag('strong', array(
							'content' => $lb_name
						)) . domTag('div', array(
							'class' => 'actions',
							'content' => $this->getActionLinks($blacklisted_login)
						)), 'name'),
						// Attempts
						tdCell($blacklisted_login['attempts'], 'attempts'),
						// Blacklisted
						tdCell(formatDate($lb_blacklisted, 'd M Y @ g:i A'), 'blacklisted'),
						// Expiration
						tdCell($lb_duration === 0 ? 'Indefinite' : formatDate($expiration, 'd M Y @ g:i A'), 'expiration'),
						// Reason
						tdCell($blacklisted_login['reason'], 'reason')
					);
				}
				
				if(empty($blacklisted_logins)) {
					echo tableRow(tdCell('There are no blacklisted logins to display.', '',
						count($header_cols)
					));
				}
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($this->paged['current'], $this->paged['count']);
	}
	
	/**
	 * Create a blacklisted login.
	 * @since 1.2.0-beta_snap-03
	 *
	 * @access public
	 */
	public function createBlacklist(): void {
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Name
					echo formRow(array('Name', true), array(
						'tag' => 'input',
						'id' => 'name-field',
						'class' => 'text-input required invalid init',
						'name' => 'name',
						'value' => ($_POST['name'] ?? ''),
						'autocomplete' => 'off'
					));
					
					// Duration
					echo formRow(array('Duration (seconds)', true), array(
						'tag' => 'input',
						'id' => 'duration-field',
						'class' => 'text-input required invalid init',
						'name' => 'duration',
						'maxlength' => 15,
						'value' => ($_POST['duration'] ?? '')
					));
					
					// Reason
					echo formRow(array('Reason', true), array(
						'tag' => 'textarea',
						'id' => 'reason-field',
						'class' => 'textarea-input required invalid init',
						'name' => 'reason',
						'cols' => 30,
						'rows' => 5,
						'content' => htmlspecialchars(($_POST['reason'] ?? ''))
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
	}
	
	/**
	 * Edit a blacklisted login.
	 * @since 1.2.0-beta_snap-02
	 *
	 * @access public
	 */
	public function editBlacklist(): void {
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI . getQueryString(array(
				'page' => $this->page
			)));
		}
		
		$this->pageHeading();
		?>
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
					
					// Duration
					echo formRow(array('Duration (seconds)', true), array(
						'tag' => 'input',
						'id' => 'duration-field',
						'class' => 'text-input required invalid init',
						'name' => 'duration',
						'maxlength' => 15,
						'value' => $this->duration
					));
					
					// Reason
					echo formRow(array('Reason', true), array(
						'tag' => 'textarea',
						'id' => 'reason-field',
						'class' => 'textarea-input required invalid init',
						'name' => 'reason',
						'cols' => 30,
						'rows' => 5,
						'content' => htmlspecialchars($this->reason)
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
						'value' => 'Update ' . ucfirst($this->page)
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Whitelist a blacklisted login or IP address.
	 * @since 1.2.0-beta_snap-02
	 *
	 * @access public
	 */
	public function whitelistLoginIP(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI . getQueryString(array(
				'page' => $this->page
			)));
		}
		
		$rs_query->delete(getTable('lb'), array(
			'id' => $this->id
		));
		
		redirect(ADMIN_URI . getQueryString(array(
			'page' => $this->page,
			'exit_status' => 'wl_success'
		)));
	}
	
	/**
	 * Construct a list of all login rules in the database.
	 * @since 1.2.0-beta_snap-05
	 *
	 * @access public
	 */
	public function loginRules(): void {
		// Query vars
		$this->paged = paginate((int)($_GET['paged'] ?? 1));
		
		$this->pageHeading();
		?>
		<table class="data-table">
			<thead>
				<?php
				$header_cols = array(
					'rule' => 'Rule'
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$login_rules = $this->getResultsRules();
				
				foreach($login_rules as $login_rule) {
					list($lr_id, $lr_type, $lr_attempts, $lr_duration) = array(
						$login_rule['id'],
						$login_rule['type'],
						$login_rule['attempts'],
						$login_rule['duration']
					);
					
					echo tableRow(
						tdCell('If failed login attempts exceed ' . domTag('strong', array(
							'content' => $lr_attempts
						)) . ', blacklist the ' . domTag('strong', array(
							'content' => ($lr_type === 'ip_address' ? 'IP address' : $lr_type)
						)) . ' ' . ($lr_duration !== 0 ? 'for ' : '') . domTag('strong', array(
							'content' => $this->formatDuration($lr_duration)
						)) . '.' . domTag('div', array(
							'class' => 'actions',
							'content' => $this->getActionLinks($login_rule)
						)), 'rule')
					);
				}
				
				if(empty($login_rules))
					echo tableRow(tdCell('There are no login rules to display.', '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($this->paged['current'], $this->paged['count']);
		
		includeFile(PATH . MODALS . '/modal-delete.php');
	}
	
	/**
	 * Create a login rule.
	 * @since 1.2.0-beta_snap-05
	 *
	 * @access public
	 */
	public function createRule(): void {
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Type
					echo formRow('Type', array(
						'tag' => 'select',
						'id' => 'type-field',
						'class' => 'select-input',
						'name' => 'type',
						'content' => domTag('option', array(
							'value' => 'login',
							'content' => 'Login'
						)) . domTag('option', array(
							'value' => 'ip_address',
							'content' => 'IP Address'
						))
					));
					
					// Attempts
					echo formRow(array('Attempts', true), array(
						'tag' => 'input',
						'id' => 'attempts-field',
						'class' => 'text-input required invalid init',
						'name' => 'attempts',
						'maxlength' => 6,
						'value' => ($_POST['attempts'] ?? '')
					));
					
					// Duration
					echo formRow(array('Duration (seconds)', true), array(
						'tag' => 'input',
						'id' => 'duration-field',
						'class' => 'text-input required invalid init',
						'name' => 'duration',
						'maxlength' => 15,
						'value' => ($_POST['duration'] ?? '')
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
	}
	
	/**
	 * Edit a login rule.
	 * @since 1.2.0-beta_snap-05
	 *
	 * @access public
	 */
	public function editRule(): void {
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI . getQueryString(array(
				'page' => $this->page
			)));
		}
		
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Type
					echo formRow('Type', array(
						'tag' => 'select',
						'id' => 'type-field',
						'class' => 'select-input',
						'name' => 'type',
						'content' => domTag('option', array(
							'value' => $this->type,
							'content' => ($this->type === 'ip_address' ? 'IP Address' : ucfirst($this->type))
						)) . ($this->type === 'login' ?
							domTag('option', array(
								'value' => 'ip_address',
								'content' => 'IP Address'
							)) :
							domTag('option', array(
								'value' => 'login',
								'content' => 'Login'
							))
						)
					));
					
					// Attempts
					echo formRow(array('Attempts', true), array(
						'tag' => 'input',
						'id' => 'attempts-field',
						'class' => 'text-input required invalid init',
						'name' => 'attempts',
						'maxlength' => 6,
						'value' => $this->attempts
					));
					
					// Duration
					echo formRow(array('Duration (seconds)', true), array(
						'tag' => 'input',
						'id' => 'duration-field',
						'class' => 'text-input required invalid init',
						'name' => 'duration',
						'maxlength' => 15,
						'value' => $this->duration
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
						'value' => 'Update ' . ucfirst(substr($this->page, 0, strpos($this->page, 's')))
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Delete a login rule.
	 * @since 1.2.0-beta_snap-05
	 *
	 * @access public
	 */
	public function deleteRule(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI . getQueryString(array(
				'page' => $this->page
			)));
		}
		
		$rs_query->delete(getTable('lr'), array(
			'id' => $this->id
		));
		
		redirect(ADMIN_URI . getQueryString(array(
			'page' => $this->page,
			'exit_status' => 'rule_del_success'
		)));
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the "Blacklist Login/Blacklist IP Address/Edit Blacklist" form data.
	 * @since 1.2.0-beta_snap-01
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateBlacklistSubmission(array $data): string {
		global $rs_query, $rs_session;
		
		if((empty($data['duration']) && $data['duration'] != 0) || empty($data['reason'])) {
			return exitNotice('REQ', -1);
			exit;
		}
		
		if($data['name'] === $rs_session['username'] || $data['name'] === $_SERVER['REMOTE_ADDR']) {
			return exitNotice('You cannot blacklist yourself!', -1);
			exit;
		}
		
		if($this->action !== 'edit' && $this->blacklistExists($data['name'])) {
			return exitNotice('This ' . ($this->action === 'login' ? 'login ' : 'IP address') .
				' is already blacklisted!', -1);
			exit;
		}
		
		switch($this->action) {
			case 'blacklist_login':
				$attempts = $rs_query->select(getTable('la'), 'COUNT(*)', array(
					'login' => $data['name']
				));
				
				$rs_query->insert(getTable('lb'), array(
					'name' => $data['name'],
					'attempts' => $attempts,
					'blacklisted' => 'NOW()',
					'duration' => $data['duration'],
					'reason' => $data['reason']
				));
				
				$session = $rs_query->selectField(getTable('u'), 'session', array(
					'logic' => 'OR',
					'username' => $data['name'],
					'email' => $data['name']
				));
				
				// Log the user out if they're logged in
				if(!is_null($session)) {
					$rs_query->update(getTable('u'), array(
						'session' => null
					), array(
						'session' => $session
					));
					
					if($_COOKIE['session'] === $session)
						setcookie('session', '', 1, '/');
				}
				
				redirect(ADMIN_URI . getQueryString(array(
					'exit_status' => 'bl_success',
					'blacklist' => 'login'
				)));
				break;
			case 'blacklist_ip':
				$attempts = $rs_query->select(getTable('la'), 'COUNT(*)', array(
					'ip_address' => $data['name']
				));
				
				$rs_query->insert(getTable('lb'), array(
					'name' => $data['name'],
					'attempts' => $attempts,
					'blacklisted' => 'NOW()',
					'duration' => $data['duration'],
					'reason' => $data['reason']
				));
				
				$logins = $rs_query->select(getTable('la'), array('DISTINCT', 'login'), array(
					'ip_address' => $data['name']
				));
				
				foreach($logins as $login) {
					$session = $rs_query->selectRow(getTable('u'), 'session', array(
						'logic' => 'OR',
						'username' => $login['login'],
						'email' => $login['login']
					));
					
					// Log the user out if they're logged in
					if(!is_null($session)) {
						$rs_query->update(getTable('u'), array(
							'session' => null
						), array(
							'session' => $session
						));
						
						if($_COOKIE['session'] === $session)
							setcookie('session', '', 1, '/');
					}
				}
				
				redirect(ADMIN_URI . getQueryString(array(
					'exit_status' => 'bl_success',
					'blacklist' => 'ip_address'
				)));
				break;
			case 'create':
				if(empty($data['name'])) {
					return exitNotice('REQ', -1);
					exit;
				}
				
				$attempts = $rs_query->select(getTable('la'), 'COUNT(*)', array(
					'logic' => 'OR',
					'login' => $data['name'],
					'ip_address' => $data['name']
				));
				
				$insert_id = $rs_query->insert(getTable('lb'), array(
					'name' => $data['name'],
					'attempts' => $attempts,
					'blacklisted' => 'NOW()',
					'duration' => $data['duration'],
					'reason' => $data['reason']
				));
				
				$session = $rs_query->selectField(getTable('u'), 'session', array(
					'logic' => 'OR',
					'username' => $data['name'],
					'email' => $data['name']
				));
				
				// Log the user out if they're logged in
				if(!is_null($session)) {
					$rs_query->update(getTable('u'), array(
						'session' => null
					), array(
						'session' => $session
					));
					
					if($_COOKIE['session'] === $session)
						setcookie('session', '', 1, '/');
				}
				
				redirect(ADMIN_URI . getQueryString(array(
					'page' => $this->page,
					'id' => $insert_id,
					'action' => 'edit',
					'exit_status' => 'bl_create_success'
				)));
				break;
			case 'edit':
				$rs_query->update(getTable('lb'), array(
					'duration' => $data['duration'],
					'reason' => $data['reason']
				), array(
					'name' => $data['name']
				));
				
				foreach($data as $key => $value) $this->$key = $value;
				
				redirect(ADMIN_URI . getQueryString(array(
					'page' => $this->page,
					'id' => $this->id,
					'action' => $this->action,
					'exit_status' => 'bl_edit_success'
				)));
				break;
		}
	}
	
	/**
	 * Validate the login rules form data.
	 * @since 1.2.0-beta_snap-05
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateRuleSubmission(array $data): string {
		global $rs_query;
		
		if(empty($data['attempts']) || (empty($data['duration']) && $data['duration'] != 0)) {
			return exitNotice('REQ', -1);
			exit;
		}
		
		if($data['type'] !== 'login' && $data['type'] !== 'ip_address')
			$data['type'] = 'login';
		
		switch($this->action) {
			case 'create':
				$insert_id = $rs_query->insert(getTable('lr'), array(
					'type' => $data['type'],
					'attempts' => $data['attempts'],
					'duration' => $data['duration']
				));
				
				redirect(ADMIN_URI . getQueryString(array(
					'page' => $this->page,
					'id' => $insert_id,
					'action' => 'edit',
					'exit_status' => 'rule_create_success'
				)));
				break;
			case 'edit':
				$rs_query->update(getTable('lr'), array(
					'type' => $data['type'],
					'attempts' => $data['attempts'],
					'duration' => $data['duration']
				), array(
					'id' => $this->id
				));
				
				foreach($data as $key => $value) $this->$key = $value;
				
				redirect(ADMIN_URI . getQueryString(array(
					'page' => $this->page,
					'id' => $this->id,
					'action' => $this->action,
					'exit_status' => 'rule_edit_success'
				)));
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
		
		switch($this->page) {
			case 'blacklist':
				switch($this->action) {
					case 'create':
						$title = $labels['create_item'] . ' ' . ucfirst($this->page);
						$message = isset($_POST['submit']) ? $this->validateBlacklistSubmission($_POST) : '';
						break;
					case 'edit':
						$title = $labels['edit_item'] . ' ' . ucfirst($this->page) . ': { ' . domTag('em', array(
							'content' => $this->name
						)) . ' }';
						$message = isset($_POST['submit']) ? $this->validateBlacklistSubmission($_POST) : '';
						break;
					default:
						$title = $labels['name_singular'] . ' ' . ucfirst($this->page);
						$search = $_GET['search'] ?? null;
				}
				break;
			case 'rules':
				switch($this->action) {
					case 'create':
						$title = $labels['create_item'] . ' ' . ucfirst(substr($this->page, 0, strpos($this->page, 's')));
						$message = isset($_POST['submit']) ? $this->validateRuleSubmission($_POST) : '';
						break;
					case 'edit':
						$title = $labels['edit_item'] . ' ' . ucfirst(substr($this->page, 0, strpos($this->page, 's'))) .
							': { ' . domTag('em', array(
								'content' => $this->type
							)) . ' }';
						$message = isset($_POST['submit']) ? $this->validateRuleSubmission($_POST) : '';
						break;
					default:
						$title = $labels['name_singular'] . ' ' . ucfirst($this->page);
				}
				break;
			default:
				switch($this->action) {
					case 'blacklist_login':
						$title = capitalize($this->action) . ': { ' . domTag('em', array(
							'content' => $this->login
						)) . ' }';
						$message = isset($_POST['submit']) ? $this->validateBlacklistSubmission($_POST) : '';
						break;
					case 'blacklist_ip':
						$title = capitalize(substr($this->action, 0, strpos($this->action, 'ip')) .
							strtoupper(substr($this->action, strpos($this->action, 'ip')))) .
							' Address: { ' . domTag('em', array(
								'content' => $this->ip_address
							)) . ' }';
						$message = isset($_POST['submit']) ? $this->validateBlacklistSubmission($_POST) : '';
						break;
					default:
						$title = $labels['name_singular'] . ' ' . ucfirst($this->admin_page['pages']['default']);
						$status = $_GET['status'] ?? 'all';
						$search = $_GET['search'] ?? null;
				}
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
				if($this->page === 'blacklist' && userHasPrivilege('can_create_login_blacklist') ||
					$this->page === 'rules' && userHasPrivilege('can_create_login_rules')
				) {
					echo actionLink($this->admin_page['actions']['create'], array(
						'classes' => 'button',
						'caption' => $labels['create_button'],
						'page' => $this->page
					));
				}
				
				// Search
				switch($this->page) {
					case 'blacklist':
						recordSearch(array(
							'page' => $this->page
						));
						break;
					case 'rules':
						// No search
						break;
					default:
						recordSearch(array(
							'status' => $status
						));
				}
				
				// Info
				adminInfo();
				
				domTagPr('hr');
				
				// Notices
				if(!getSetting('track_login_attempts')) {
					echo notice('Login tracking is currently disabled. You can enable it on the ' . domTag('a', array(
						'href' => ADMIN . '/settings.php',
						'content' => 'settings page'
					)) . '.', 2, false, true);
				}
				
				// Exit notices
				if(isset($_GET['exit_status'])) {
					if(isset($_GET['blacklist']))
						echo $this->exitNotice('bl_' . $_GET['blacklist'] . '_success');
					else
						echo $this->exitNotice($_GET['exit_status']);
				}
				
				switch($this->page) {
					case 'blacklist':
						$count = $this->getEntryCount('', $search);
						
						// Record count
						domTagPr('div', array(
							'class' => 'entry-count',
							'content' => $count . ' ' . ($count === 1 ? 'entry' : 'entries')
						));
						
						$this->paged['count'] = ceil($count / $this->paged['per_page']);
						break;
					case 'rules':
						$count = $this->getEntryCount('', null);
						
						// Record count
						domTagPr('div', array(
							'class' => 'entry-count status',
							'content' => $count . ' ' . ($count === 1 ? 'entry' : 'entries')
						));
						
						$this->paged['count'] = ceil($count / $this->paged['per_page']);
						break;
					default:
						?>
						<ul class="status-nav">
							<?php
							$keys = array('all', 'success', 'failure');
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
			'bl_login_success' => 'The login was successfully blacklisted.',
			'bl_ip_address_success' => 'The IP address was successfully blacklisted.',
			'bl_create_success' => 'The blacklist was successfully created. ' . domTag('a', array(
				'href' => ADMIN_URI . getQueryString(array(
					'page' => $this->page
				)),
				'content' => 'Return to list'
			)) . '?',
			'bl_edit_success' => 'Blacklist updated! ' . domTag('a', array(
				'href' => ADMIN_URI . getQueryString(array(
					'page' => $this->page
				)),
				'content' => 'Return to list'
			)) . '?',
			'wl_success' => 'The login or IP address was successfully whitelisted.',
			'rule_create_success' => 'The login rule was successfully created. ' . domTag('a', array(
				'href' => ADMIN_URI . getQueryString(array(
					'page' => $this->page
				)),
				'content' => 'Return to list'
			)) . '?',
			'rule_edit_success' => 'Rule updated! ' . domTag('a', array(
				'href' => ADMIN_URI . getQueryString(array(
					'page' => $this->page
				)),
				'content' => 'Return to list'
			)) . '?',
			'rule_del_success' => 'The rule was successfully deleted.',
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Check whether a blacklist already exists in the database.
	 * @since 1.2.0-beta_snap-02
	 *
	 * @access private
	 * @param string $name -- The blacklist's name.
	 * @return bool
	 */
	private function blacklistExists(string $name): bool {
		global $rs_query;
		
		return $rs_query->selectRow(getTable('lb'), 'COUNT(name)', array(
			'name' => $name
		)) > 0;
	}
	
	/**
	 * Format a duration in seconds to something more readable.
	 * @since 1.2.0-beta_snap-05
	 *
	 * @access private
	 * @param int $seconds -- The number of seconds.
	 * @return string
	 */
	private function formatDuration(int $seconds): string {
		if($seconds !== 0) {
			$time_start = new \DateTime('@0');
			$time_end = new \DateTime('@' . $seconds);
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
 	 * Fetch all login attempts based on a specific status.
 	 * @since 1.3.15-beta
 	 *
 	 * @access private
 	 * @param string $status -- The login attempt's status.
 	 * @param null|string $search -- The search query.
	 * @param bool $all (optional) -- Whether to return all or set a limit (for pagination).
 	 * @return array
 	 */
	private function getResultsAttempts(string $status, ?string $search, bool $all = false): array {
		global $rs_query;
		
		$order_by = 'date';
		$order = 'DESC';
		$limit = $all === false ? array($this->paged['start'], $this->paged['per_page']) : 0;
		
		if(!is_null($search)) {
			// Search results
			if($status === 'all') {
				return $rs_query->select(getTable('la'), '*', array(
					'login' => array('LIKE', '%' . $search . '%')
				), array(
					'order_by' => $order_by,
					'order' => $order,
					'limit' => $limit
				));
			} else {
				return $rs_query->select(getTable('la'), '*', array(
					'login' => array('LIKE', '%' . $search . '%'),
					'status' => $status
				), array(
					'order_by' => $order_by,
					'order' => $order,
					'limit' => $limit
				));
			}
		} else {
			// All results
			if($status === 'all') {
				return $rs_query->select(getTable('la'), '*', array(), array(
					'order_by' => $order_by,
					'order' => $order,
					'limit' => $limit
				));
			} else {
				return $rs_query->select(getTable('la'), '*', array(
					'status' => $status
				), array(
					'order_by' => $order_by,
					'order' => $order,
					'limit' => $limit
				));
			}
		}
	}
	
	/**
 	 * Fetch all login blacklists based on a specific status.
 	 * @since 1.3.15-beta
 	 *
 	 * @access private
 	 * @param null|string $search -- The search query.
	 * @param bool $all (optional) -- Whether to return all or set a limit (for pagination).
 	 * @return array
 	 */
	private function getResultsBlacklist(?string $search, bool $all = false): array {
		global $rs_query;
		
		$order_by = 'blacklisted';
		$order = 'DESC';
		$limit = $all === false ? array($this->paged['start'], $this->paged['per_page']) : 0;
		
		if(!is_null($search)) {
			// Search results
			return $rs_query->select(getTable('lb'), '*', array(
				'name' => array('LIKE', '%' . $search . '%')
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		} else {
			// All results
			return $rs_query->select(getTable('lb'), '*', array(), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		}
	}
	
	/**
 	 * Fetch all login rules based on a specific status.
 	 * @since 1.3.15-beta
 	 *
 	 * @access private
	 * @param bool $all (optional) -- Whether to return all or set a limit (for pagination).
 	 * @return array
 	 */
	private function getResultsRules(bool $all = false): array {
		global $rs_query;
		
		$order_by = 'attempts';
		$order = 'ASC';
		$limit = $all === false ? array($this->paged['start'], $this->paged['per_page']) : 0;
		
		return $rs_query->select(getTable('lr'), '*', array(), array(
			'order_by' => $order_by,
			'order' => $order,
			'limit' => $limit
		));
	}
	
	/**
	 * Fetch the login attempt count based on a specific status.
	 * @since 1.3.2-beta
	 *
	 * @access private
	 * @param string $status -- The login attempt's status.
	 * @param null|string $search -- The search query.
	 * @return int
	 */
	private function getEntryCount(string $status, ?string $search): int {
		return match($this->page) {
			'blacklist' => count($this->getResultsBlacklist($search, true)),
			'rules' => count($this->getResultsRules(true)),
			default => count($this->getResultsAttempts($status, $search, true))
		};
	}
	
	/**
	 * Fetch all associated action links.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @param array $login -- The login's data.
	 * @return string
	 */
	private function getActionLinks(array $login): string {
		global $rs_session;
		
		$actions = $this->admin_page['actions'];
		$action_list = array();
		
		foreach($actions as $key => $value) {
			switch($this->page) {
				// Login Attempts
				case $this->admin_page['pages']['default']:
					$continue = match($value) {
						'create', 'edit', 'whitelist', 'delete' => true,
						default => false
					};
					
					if($continue) continue 2;
					
					$privileged = match($value) {
						'blacklist_login' => userHasPrivilege('can_create_login_blacklist'),
						'blacklist_ip' => userHasPrivilege('can_create_login_blacklist'),
						default => null
					};
					break;
				// Login Blacklist
				case $this->admin_page['pages']['subpages'][0]:
					$continue = match($value) {
						'create', 'blacklist_login', 'blacklist_ip', 'delete' => true,
						default => false
					};
					
					if($continue) continue 2;
					
					$privileged = match($value) {
						'edit' => userHasPrivilege('can_edit_login_blacklist'),
						'whitelist' => userHasPrivilege('can_delete_login_blacklist'),
						default => null
					};
					break;
				// Login Rules
				case $this->admin_page['pages']['subpages'][1]:
					$continue = match($value) {
						'create', 'blacklist_login', 'blacklist_ip', 'whitelist' => true,
						default => false
					};
					
					if($continue) continue 2;
					
					$privileged = match($value) {
						'edit' => userHasPrivilege('can_edit_login_rules'),
						'delete' => userHasPrivilege('can_delete_login_rules'),
						default => null
					};
					break;
			}
			
			$caption = capitalize($value);
			
			$action_list[] = array(
				'privileged' => $privileged,
				'link' => $value,
				'caption' => str_contains($caption, 'Ip') ?
					substr($caption, 0, strpos($caption, 'Ip')) . 
					strtoupper(substr($caption, strpos($caption, 'Ip'))) : $caption
			);
		}
		
		switch($this->page) {
			case $this->admin_page['pages']['default']:
				list($blacklist_login, $blacklist_ip) = $action_list;
				
				$action_links = array(
					// Blacklist Login
					$blacklist_login['privileged'] ? actionLink($blacklist_login['link'], array(
						'caption' => $blacklist_login['caption'],
						'id' => $login['id']
					)) : null,
					// Blacklist IP
					$blacklist_ip['privileged'] ? actionLink($blacklist_ip['link'], array(
						'caption' => $blacklist_ip['caption'],
						'id' => $login['id']
					)) : null
				);
				break;
			case $this->admin_page['pages']['subpages'][0]:
				list($edit, $whitelist) = $action_list;
				
				$action_links = array(
					// Edit
					$edit['privileged'] ? actionLink($edit['link'], array(
						'caption' => $edit['caption'],
						'page' => 'blacklist',
						'id' => $login['id']
					)) : null,
					// Whitelist
					$whitelist['privileged'] ? actionLink($whitelist['link'], array(
						'caption' => $whitelist['caption'],
						'page' => 'blacklist',
						'id' => $login['id']
					)) : null
				);
				break;
			case $this->admin_page['pages']['subpages'][1]:
				list($edit, $delete) = $action_list;
				
				$action_links = array(
					// Edit
					$edit['privileged'] ? actionLink($edit['link'], array(
						'caption' => $edit['caption'],
						'page' => 'rules',
						'id' => $login['id']
					)) : null,
					// Delete
					$delete['privileged'] ? actionLink($delete['link'], array(
						'classes' => 'modal-launch delete-item',
						'data_item' => 'login rule',
						'caption' => $delete['caption'],
						'page' => 'rules',
						'id' => $login['id']
					)) : null
				);
				break;
		}
		
		// Filter out any empty actions
		$action_links = array_filter($action_links);
		
		return implode(' &bull; ', $action_links);
	}
}