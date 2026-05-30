<?php
/**
 * Core class used to implement the Login object.
 * This class controls the login/logout process for the user.
 * @since 2.0.0-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Engine
 *
 * ## OBJECT VAR ##
 * - $rs_login
 *
 * ## CONSTANTS [1] ##
 * - private int PW_LENGTH
 *
 * ## VARIABLES [3] ##
 * - private bool $https
 * - private string $ip_address
 * - private string $login_uri
 *
 * ## METHODS [19] ##
 * - public __construct()
 * { FORMS & ACTIONS [4] }
 * - public logInForm(): void
 * - public forgotPasswordForm(): void
 * - public resetPasswordForm(): void
 * - public userLogout(string $session): void
 * { VALIDATION [3] }
 * - private validateLoginSubmission(array $data): string
 * - private validateForgotPasswordSubmission(array $data): string
 * - private validateResetPasswordSubmission(array $data): string
 * { MISCELLANEOUS [11] }
 * - private sessionExists(string $session): bool
 * - private emailExists(string $email): bool
 * - private usernameExists(string $username): bool
 * - private isValidPassword(string $login, string $password): bool
 * - private isValidCaptcha(string $captcha): bool
 * - private isValidCookie(string $login, string $key): bool
 * - private isBlacklisted(string $login): bool
 * - private shouldBlacklist(string $ip_address, string $login): void
 * - private getBlacklistDuration(string $name): int
 * - private sanitizeData(string $data, ?string $filter): string
 * - private statusMsg(string $text, bool $success): string
 */
namespace Engine;

class Login {
	/**
	 * The minimum password length.
	 * @since 2.0.7-alpha
	 *
	 * @access private
	 * @var int
	 */
	private const PW_LENGTH = 8;
	
	/**
	 * Whether HTTPS is enabled on the server.
	 * @since 1.1.4-beta
	 *
	 * @access private
	 * @var bool
	 */
	private $https;
	
	/**
	 * The current user's IP address.
	 * @since 1.2.0-beta_snap-01
	 *
	 * @access private
	 * @var string
	 */
	private $ip_address;
	
	/**
	 * The login URI.
	 * @since 1.3.12-beta
	 *
	 * @access private
	 * @var string
	 */
	private $login_uri;
	
	/**
	 * Class constructor.
	 * @since 1.1.4-beta
	 *
	 * @access public
	 */
	public function __construct() {
		$this->https = isSecureConnection() ? true : false;
		$this->ip_address = $_SERVER['REMOTE_ADDR'];
		$this->login_uri = '/login.php';
		
		$login_slug = getSetting('login_slug');
		
		if(!empty($login_slug)) {
			$this->login_uri .= getQueryString(array(
				'secure_login' => $login_slug
			));
		}
	}
	
	/*------------------------------------*\
		FORMS & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct the "Log In" form.
	 * @since 2.0.3-alpha
	 *
	 * @access public
	 */
	public function logInForm(): void {
		// "Forgot Password" form confirmation
		if(isset($_GET['pw_forgot']) && $_GET['pw_forgot'] === 'confirm')
			echo $this->statusMsg('Check your email for a confirmation to reset your password.', true);
		
		// "Reset Password" form confirmation
		if(isset($_GET['pw_reset']) && $_GET['pw_reset'] === 'confirm')
			echo $this->statusMsg('Your password has been successfully reset.', true);
		
		// Validate the form data
		echo isset($_POST['submit']) ? $this->validateLoginSubmission($_POST) : '';
		?>
		<form class="data-form" action="" method="post">
			<?php
			// Login
			domTagPr('p', array(
				'class' => 'login-field',
				'content' => domTag('label', array(
					'for' => 'login',
					'content' => 'Username or Email' . domTag('br') . domTag('input', array(
						'id' => 'login',
						'name' => 'login',
						'autofocus' => 1
					))
				))
			));
			
			// Password
			domTagPr('p', array(
				'class' => 'password-field',
				'content' => domTag('label', array(
					'for' => 'password',
					'content' => 'Password' . domTag('br') . domTag('input', array(
						'type' => 'password',
						'id' => 'password',
						'name' => 'password'
					))
				)) . domTag('button', array(
					'id' => 'password-toggle',
					'class' => 'button',
					'title' => 'Show Password',
					'data-visibility' => 'hidden',
					'content' => domTag('i', array(
						'class' => 'fa-regular fa-eye'
					))
				))
			));
			
			// Captcha
			domTagPr('p', array(
				'class' => 'captcha-field',
				'content' => domTag('label', array(
					'for' => 'captcha',
					'content' => 'Captcha' . domTag('br') . domTag('input', array(
						'id' => 'captcha',
						'name' => 'captcha',
						'autocomplete' => 'off'
					)) . domTag('img', array(
						'id' => 'captcha-image',
						'src' => UTILS . '/captcha.php'
					))
				))
			));
			
			// Remember login
			domTagPr('p', array(
				'class' => 'remember-field',
				'content' => domTag('label', array(
					'class' => 'checkbox-label',
					'for' => 'remember-login',
					'content' => domTag('input', array(
						'type' => 'checkbox',
						'id' => 'remember-login',
						'name' => 'remember_login',
						'value' => 'checked'
					)) . ' ' . domTag('span', array(
						'content' => 'Keep me logged in'
					))
				))
			));
			
			// Redirect
			if(isset($_GET['redirect'])) {
				domTagPr('input', array(
					'type' => 'hidden',
					'name' => 'redirect',
					'value' => $_GET['redirect']
				));
			}
			
			// Submit button
			domTagPr('input', array(
				'type' => 'submit',
				'class' => 'button',
				'name' => 'submit',
				'value' => 'Log In'
			));
			?>
		</form>
		<?php
		if(!isset($_GET['pw_forgot'])) {
			domTagPr('a', array(
				'href' => getQueryString(array(
					'action' => 'forgot_password'
				)),
				'content' => 'Forgot your password?'
			));
		}
	}
	
	/**
	 * Construct the "Forgot Password" form.
	 * @since 2.0.3-alpha
	 *
	 * @access public
	 */
	public function forgotPasswordForm(): void {
		if(isset($_GET['error'])) {
			$error = $_GET['error'];
			
			if($error === 'invalid_key')
				echo $this->statusMsg('Your security key is invalid. Submit this form to get a new password reset link.');
			elseif($error === 'expired_key')
				echo $this->statusMsg('Your security key has expired. Submit this form to get a new password reset link.');
		}
		
		// Validate the form data
		echo isset($_POST['submit']) ? $this->validateForgotPasswordSubmission($_POST) : '';
		?>
		<form class="data-form" action="" method="post">
			<?php
			domTagPr('p', array(
				'content' => 'Enter your username or email below and you will receive a link to reset your password in an email.'
			));
			
			domTagPr('p', array(
				'content' => 'Remembered your password? ' . domTag('a', array(
					'href' => $this->login_uri,
					'content' => 'Log in'
				)) . ' instead.'
			));
			
			// Username or email
			domTagPr('p', array(
				'content' => domTag('label', array(
					'for' => 'login',
					'content' => 'Username or Email' . domTag('br') . domTag('input', array(
						'id' => 'login',
						'name' => 'login',
						'autofocus' => 1
					))
				))
			));
			
			// Submit button
			domTag('input', array(
				'type' => 'submit',
				'class' => 'button',
				'name' => 'submit',
				'value' => 'Get New Password'
			));
			?>
		</form>
		<?php
	}
	
	/**
	 * Construct the "Reset Password" form.
	 * @since 2.0.5-alpha
	 *
	 * @access public
	 */
	public function resetPasswordForm(): void {
		$cookie_name = 'pw-reset-' . COOKIE_HASH;
		
		if(isset($_GET['login']) && isset($_GET['key'])) {
			// Create a cookie that expires when the browser is closed
			setcookie($cookie_name, $_GET['login'] . ':' . $_GET['key'], array(
				'expires' => 0,
				'path' => '/login.php',
				'secure' => $this->https,
				'httponly' => true,
				'samesite' => 'Strict'
			));
			
			redirect('/login.php' . getQueryString(array(
				'action' => 'reset_password'
			)));
		}
		
		if(isset($_COOKIE[$cookie_name])) {
			list($login, $key) = explode(':', $_COOKIE[$cookie_name]);
			
			if(!$this->isValidCookie($login, $key)) {
				// Delete the cookie
				setcookie($cookie_name, '', 1, '/login.php');
				
				redirect('/login.php' . getQueryString(array(
					'action' => 'forgot_password',
					'error' => 'invalid_key'
				)));
			}
		} else {
			redirect('/login.php' . getQueryString(array(
				'action' => 'forgot_password',
				'error' => 'expired_key'
			)));
		}
		
		// Validate the form data
		echo isset($_POST['submit']) ? $this->validateResetPasswordSubmission($_POST) : '';
		?>
		<form class="data-form" action="" method="post">
			<?php
			// Password
			domTagPr('p', array(
				'content' => domTag('label', array(
					'for' => 'password',
					'content' => 'New Password' . domTag('br') . domTag('input', array(
						'id' => 'password',
						'name' => 'password',
						'value' => generatePassword(),
						'autofocus' => 1
					))
				))
			));
			
			// Login
			domTagPr('input', array(
				'type' => 'hidden',
				'name' => 'login',
				'value' => $login
			));
			
			// Key
			domTagPr('input', array(
				'type' => 'hidden',
				'name' => 'key',
				'value' => $key
			));
			
			// Submit button
			domTagPr('input', array(
				'type' => 'submit',
				'class' => 'button',
				'name' => 'submit',
				'value' => 'Reset Password'
			));
			?>
		</form>
		<?php
	}
	
	/**
	 * Log the user out.
	 * @since 2.0.1-alpha
	 *
	 * @access public
	 * @param string $session -- The session value.
	 */
	public function userLogout(string $session): void {
		global $rs_query;
		
		$rs_query->update(getTable('u'), array(
			'session' => null
		), array(
			'session' => $session
		));
		
		// Delete the session cookie
		setcookie('session', '', 1, '/');
		
		redirect($this->login_uri);
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the "Log In" form data and log the user in.
	 * @since 2.0.0-alpha
	 *
	 * @access private
	 * @param array $data -- The form data.
	 * @return string
	 */
	private function validateLoginSubmission(array $data): string {
		global $rs_query;
		
		$offsite_redirect = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'; // ;)
		
		if(empty($data['login']) || empty($data['password']) || empty($data['captcha'])) {
			return $this->statusMsg('F');
			exit;
		}
		
		// Check whether the login used was an email
		if(str_contains($data['login'], '@'))
			$email = $this->sanitizeData($data['login'], FILTER_SANITIZE_EMAIL);
		else
			$username = $this->sanitizeData($data['login'], '/[^\w.]/i');
		
		$this->shouldBlacklist($this->ip_address, ($email ?? $username));
		
		if($this->isBlacklisted($this->ip_address)) {
			// Check whether the blacklist duration is indefinite and redirect off-site if so
			if($this->getBlacklistDuration($this->ip_address) === 0) {
				redirect($offsite_redirect);
			} // Otherwise, return an error
			elseif($this->getBlacklistDuration($this->ip_address) > 0) {
				return $this->statusMsg('You\'re attempting to log in too fast! Try again later.');
				exit;
			}
		}
		
		if($this->isBlacklisted($email ?? $username)) {
			// Check whether the blacklist duration is indefinite and redirect off-site if so
			if($this->getBlacklistDuration($email ?? $username) === 0) {
				redirect($offsite_redirect);
			} // Otherwise, return an error
			elseif($this->getBlacklistDuration($email ?? $username) > 0) {
				return $this->statusMsg('You\'re attempting to log in too fast! Try again later.');
				exit;
			}
		}
		
		$password = $this->sanitizeData($data['password']);
		$captcha = $this->sanitizeData($data['captcha'], '/[^A-Za-z0-9]/i');
		
		if(getSetting('track_login_attempts')) {
			$login_attempt = $rs_query->insert(getTable('la'), array(
				'login' => ($email ?? $username),
				'ip_address' => $this->ip_address,
				'date' => 'NOW()'
			));
		}
		
		if(!$this->isValidCaptcha($captcha)) {
			return $this->statusMsg('The captcha is not valid.');
			exit;
		}
		
		do {
			// Generate a random hash for the session value
			$session = generateHash(12);
		} while($this->sessionExists($session));
		
		if(isset($email)) {
			if(!$this->emailExists($email) || !$this->isValidPassword($email, $password)) {
				return $this->statusMsg('The email and/or password is not valid.');
				exit;
			}
			
			$rs_query->update(getTable('u'), array(
				'last_login' => 'NOW()',
				'session' => $session
			), array(
				'email' => $email
			));
		} elseif(isset($username)) {
			if(!$this->usernameExists($username) || !$this->isValidPassword($username, $password)) {
				return $this->statusMsg('The username and/or password is not valid.');
				exit;
			}
			
			$rs_query->update(getTable('u'), array(
				'last_login' => 'NOW()',
				'session' => $session
			), array(
				'username' => $username
			));
		}
		
		// Check whether the login attempt was tracked
		if(isset($login_attempt)) {
			$rs_query->update(getTable('la'), array(
				'status' => 'success'
			), array(
				'id' => $login_attempt
			));
		}
		
		if(isset($data['remember_login']) && $data['remember_login'] === 'checked') {
			// Create a cookie with the session value that expires in 30 days
			setcookie('session', $session, array(
				'expires' => time() + 60 * 60 * 24 * 30,
				'path' => '/',
				'secure' => $this->https,
				'httponly' => true,
				'samesite' => 'Strict'
			));
		} else {
			// Create a cookie with the session value that expires when the browser is closed
			setcookie('session', $session, array(
				'expires' => 0,
				'path' => '/',
				'secure' => $this->https,
				'httponly' => true,
				'samesite' => 'Strict'
			));
		}
		
		unset($_SESSION['secure_login']);
		
		if(isset($data['redirect']))
			redirect($data['redirect']);
		else
			redirect(slash(ADMIN));
	}
	
	/**
	 * Validate the forgotten password data.
	 * @since 2.0.5-alpha
	 *
	 * @access private
	 * @param array $data -- The form data.
	 * @return string
	 */
	private function validateForgotPasswordSubmission(array $data): string {
		global $rs_query;
		
		if(empty($data['login'])) {
			return $this->statusMsg('F');
			exit;
		}
		
		$key = generateHash(20, false, time());
		
		$site_url = (isSecureConnection() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
		
		// Check whether the login used was an email
		if(str_contains($data['login'], '@')) {
			$email = $this->sanitizeData($data['login'], FILTER_SANITIZE_EMAIL);
			
			if(!$this->emailExists($email)) {
				return $this->statusMsg('The email you provided is not registered on this website.');
				exit;
			}
			
			list($id, $username) = array_values($rs_query->selectRow(getTable('u'), array('id', 'username'), array(
				'email' => $email
			)));
		} else {
			$username = $this->sanitizeData($data['login'], '/[^A-Za-z0-9_.]/i');
			
			if(!$this->usernameExists($username)) {
				return $this->statusMsg('The username you provided is not registered on this website.');
				exit;
			}
			
			list($id, $email) = array_values($rs_query->selectRow(getTable('u'), array('id', 'email'), array(
				'username' => $username
			)));
		}
		
		$subject = getSetting('site_title') . ' – Password Reset';
		
		$pw_reset_link = $site_url . '/login.php' . getQueryString(array(
			'login' => $username,
			'key' => $key,
			'action' => 'reset_password'
		));
		
		$message = 'A request has been made to reset the password for the user ' .
			domTag('strong', array(
				'content' => $username
			)) . ' on "' . getSetting('site_title') . '".' . domTag('br') . domTag('br') .
			'If this was you, please click the link below to reset your password. If not, you may disregard this email.' .
			domTag('br') . domTag('br') . domTag('a', array(
				'href' => $pw_reset_link,
				'content' => 'Reset your password'
			));
		
		$content = formatEmail('Reset Password', array(
			'message' => $message
		));
		
		// Set the content headers (to allow for HTML-formatted emails)
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/html; charset=iso-8859-1";
		$headers[] = "From: " . RS_ENGINE . " <" . (str_contains(getSetting('admin_email'), $_SERVER['HTTP_HOST']) ?
			getSetting('admin_email') : "rscms@" . $_SERVER['HTTP_HOST']) . ">";
		
		// Make sure the email can be sent
		if(mail($email, $subject, $content, implode("\r\n", $headers))) {
			$rs_query->update(getTable('u'), array(
				'token' => $key
			), array(
				'id' => $id
			));
			
			redirect('/login.php' . getQueryString(array(
				'pw_forgot' => 'confirm'
			)));
		} else {
			return $this->statusMsg(RS_ENGINE . ' encountered an error and could not send an email. Please contact this website\'s administrator or web host.');
			exit;
		}
	}
	
	/**
	 * Validate the reset password data.
	 * @since 2.0.5-alpha
	 *
	 * @access private
	 * @param array $data -- The form data.
	 * @return string
	 */
	private function validateResetPasswordSubmission(array $data): string {
		global $rs_query;
		
		if(empty($data['password'])) {
			return $this->statusMsg('F');
			exit;
		}
		
		if(strlen($data['password']) < self::PW_LENGTH) {
			return $this->statusMsg('Password must be at least ' . self::PW_LENGTH . ' characters long.');
			exit;
		}
		
		if($this->isValidCookie($data['login'], $data['key'])) {
			$hashed_password = password_hash($data['password'], PASSWORD_BCRYPT, array(
				'cost' => 10
			));
			
			$rs_query->update(getTable('u'), array(
				'password' => $hashed_password,
				'token' => null
			), array(
				'username' => $data['login']
			));
			
			// Delete the cookie
			setcookie('pw-reset-' . COOKIE_HASH, '', 1, '/login.php');
			
			redirect('/login.php' . getQueryString(array(
				'pw_reset' => 'confirm'
			)));
		} else {
			redirect('/login.php' . getQueryString(array(
				'action' => 'forgot_password',
				'error' => 'invalid_key'
			)));
		}
	}
	
	/*------------------------------------*\
		MISCELLANEOUS
	\*------------------------------------*/
	
	/**
	 * Check whether a session already exists in the database.
	 * @since 2.0.2-alpha
	 *
	 * @access private
	 * @param string $session -- The session value.
	 * @return bool
	 */
	private function sessionExists(string $session): bool {
		global $rs_query;
		
		return $rs_query->selectRow(getTable('u'), 'COUNT(session)', array(
			'session' => $session
		)) > 0;
	}
	
	/**
	 * Check whether an email already exists in the database.
	 * @since 2.0.2-alpha
	 *
	 * @access private
	 * @param string $email -- The email.
	 * @return bool
	 */
	private function emailExists(string $email): bool {
		global $rs_query;
		
		return $rs_query->selectRow(getTable('u'), 'COUNT(email)', array(
			'email' => $email
		)) > 0;
	}
	
	/**
	 * Check whether a username already exists in the database.
	 * @since 2.0.0-alpha
	 *
	 * @access private
	 * @param string $username -- The username.
	 * @return bool
	 */
	private function usernameExists(string $username): bool {
		global $rs_query;
		
		return $rs_query->selectRow(getTable('u'), 'COUNT(username)', array(
			'username' => $username
		)) > 0;
	}
	
	/**
	 * Check whether a password is valid.
	 * @since 2.0.0-alpha
	 *
	 * @access private
	 * @param string $login -- The username or email.
	 * @param string $password -- The password.
	 * @return bool
	 */
	private function isValidPassword(string $login, string $password): bool {
		global $rs_query;
		
		if(str_contains($login, '@'))
			$col = 'email';
		else
			$col = 'username';
		
		$db_password = $rs_query->selectField(getTable('u'), 'password', array(
			$col => $login
		));
		
		return !empty($db_password) && password_verify($password, $db_password);
	}
	
	/**
	 * Check whether a captcha value is valid.
	 * @since 2.0.0-alpha
	 *
	 * @access private
	 * @param string $captcha -- The captcha value.
	 * @return bool
	 */
	private function isValidCaptcha(string $captcha): bool {
		return !empty($_SESSION['secure_login']) && $captcha === $_SESSION['secure_login'];
	}
	
	/**
	 * Check whether a reset password cookie is valid.
	 * @since 2.0.6-alpha
	 *
	 * @access private
	 * @param string $login -- The user's login.
	 * @param string $key -- The cookie hash key.
	 * @return bool
	 */
	private function isValidCookie(string $login, string $key): bool {
		global $rs_query;
		
		return $rs_query->selectRow(getTable('u'), 'COUNT(*)', array(
			'username' => $login,
			'token' => $key
		)) > 0;
	}
	
	/**
	 * Check whether the login or IP address is blacklisted.
	 * @since 1.2.0-beta_snap-01
	 *
	 * @access private
	 * @param string $login -- The login or IP address.
	 * @return bool
	 */
	private function isBlacklisted(string $login): bool {
		global $rs_query;
		
		return $rs_query->select(getTable('lb'), 'COUNT(name)', array(
			'name' => $login
		)) > 0;
	}
	
	/**
	 * Check whether the login or IP address should be blacklisted.
	 * @since 1.2.0-beta_snap-05
	 *
	 * @access private
	 * @param string $ip_address -- The IP address.
	 * @param string $login -- The login.
	 */
	private function shouldBlacklist(string $ip_address, string $login): void {
		global $rs_query;
		
		$last_blacklisted_ip = $rs_query->selectField(getTable('la'), 'last_blacklisted_ip', array(
			'ip_address' => $ip_address
		), array(
			'order_by' => 'id',
			'order' => 'ASC',
			'limit' => 1
		));
		
		$failed_logins = $rs_query->select(getTable('la'), 'COUNT(*)', array(
			'ip_address' => $ip_address,
			'date' => array('>', $last_blacklisted_ip),
			'status' => 'failure'
		));
		
		$login_rules = $rs_query->select(getTable('lr'), '*', array(
			'type' => 'ip_address'
		), array(
			'order_by' => 'attempts',
			'order' => 'DESC'
		));
		
		foreach($login_rules as $login_rule) {
			// Check whether the failed logins exceed the rule's threshold
			if($failed_logins >= $login_rule['attempts']) {
				if(!$this->isBlacklisted($ip_address)) {
					$failed_attempts = $rs_query->select(getTable('la'), 'COUNT(*)', array(
						'ip_address' => $ip_address,
						'status' => 'failure'
					));
					
					// Create a blacklist for the IP address
					$rs_query->insert(getTable('lb'), array(
						'name' => $ip_address,
						'attempts' => $failed_attempts,
						'blacklisted' => 'NOW()',
						'duration' => $login_rule['duration'],
						'reason' => 'too many failed login attempts'
					));
					
					$rs_query->update(getTable('la'), array(
						'last_blacklisted_ip' => 'NOW()'
					), array(
						'ip_address' => $ip_address
					));
					
					$logins = $rs_query->select(getTable('la'), array('DISTINCT', 'login'), array(
						'ip_address' => $ip_address
					));
					
					foreach($logins as $login) {
						$session = $rs_query->selectField(getTable('u'), 'session', array(
							'logic' => 'OR',
							'username' => $login['login'],
							'email' => $login['login']
						));
						
						if(!is_null($session)) {
							$rs_query->update(getTable('u'), array(
								'session' => null
							), array(
								'session' => $session
							));
							
							if(isset($_COOKIE['session']) && $_COOKIE['session'] === $session)
								setcookie('session', '', 1, '/');
						}
					}
				}
				
				return;
			}
		}
		
		$last_blacklisted_login = $rs_query->selectField(getTable('la'), 'last_blacklisted_login', array(
			'login' => $login
		), array(
			'order_by' => 'id',
			'order' => 'ASC',
			'limit' => 1
		));
		
		$failed_logins = $rs_query->select(getTable('la'), 'COUNT(*)', array(
			'login' => $login,
			'date' => array('>', $last_blacklisted_login),
			'status' => 'failure'
		));
		
		$login_rules = $rs_query->select(getTable('lr'), '*', array(
			'type' => 'login'
		), array(
			'order_by' => 'attempts',
			'order' => 'DESC'
		));
		
		foreach($login_rules as $login_rule) {
			// Check whether the failed logins exceed the rule's threshold
			if($failed_logins >= $login_rule['attempts']) {
				if(!$this->isBlacklisted($login)) {
					$failed_attempts = $rs_query->select(getTable('la'), 'COUNT(*)', array(
						'login' => $login,
						'status' => 'failure'
					));
					
					// Create a blacklist for the login
					$rs_query->insert(getTable('lb'), array(
						'name' => $login,
						'attempts' => $failed_attempts,
						'blacklisted' => 'NOW()',
						'duration' => $login_rule['duration'],
						'reason' => 'too many failed login attempts'
					));
					
					$rs_query->update(getTable('la'), array(
						'last_blacklisted_login' => 'NOW()'
					), array(
						'login' => $login
					));
					
					$session = $rs_query->selectField(getTable('u'), 'session', array(
						'logic' => 'OR',
						'username' => $login,
						'email' => $login
					));
					
					if(!is_null($session)) {
						$rs_query->update(getTable('u'), array(
							'session' => null
						), array(
							'session' => $session
						));
						
						if(isset($_COOKIE['session']) && $_COOKIE['session'] === $session)
							setcookie('session', '', 1, '/');
					}
				}
				
				return;
			}
		}
	}
	
	/**
	 * Fetch a blacklist's duration.
	 * @since 1.2.0-beta_snap-01
	 *
	 * @access private
	 * @param string $name -- The blacklist's name.
	 * @return int
	 */
	private function getBlacklistDuration(string $name): int {
		global $rs_query;
		
		$blacklist = $rs_query->selectRow(getTable('lb'), array('blacklisted', 'duration'), array(
			'name' => $name
		));
		
		if(empty($blacklist)) {
			$duration = -1;
		} else {
			// Calculate the expiration date
			$time = new \DateTime($blacklist['blacklisted']);
			$time->add(new \DateInterval('PT' . $blacklist['duration'] . 'S'));
			$expiration = $time->format('Y-m-d H:i:s');
			
			// Check whether the blacklist has expired
			if(date('Y-m-d H:i:s') >= $expiration && $blacklist['duration'] !== 0) {
				$duration = -1;
				
				$rs_query->delete(getTable('lb'), array(
					'name' => $name
				));
			} else {
				// Set the duration
				$duration = (int)$blacklist['duration'];
			}
		}
		
		return $duration;
	}
	
	/**
	 * Sanitize user input data.
	 * @since 2.0.1-alpha
	 *
	 * @access private
	 * @param string $data -- The data to sanitize.
	 * @param null|string $filter (optional) -- The filter to use.
	 * @return string
	 */
	private function sanitizeData(string $data, ?string $filter = null): string {
		if(is_null($filter))
			return strip_tags(trim($data));
		elseif(is_int($filter))
			return filter_var(strip_tags($data), $filter);
		else
			return preg_replace($filter, '', strip_tags($data));
	}
	
	/**
	 * Construct a status message.
	 * @since 2.0.0-alpha
	 *
	 * @access private
	 * @param string $text -- The message's text.
	 * @param bool $success (optional) -- Whether the submission was successful.
	 * @return string
	 */
	private function statusMsg(string $text, bool $success = false): string {
		if($success === true) {
			$class = 'success';
		} else {
			$class = 'failure';
			
			switch(strtoupper($text)) {
				case 'F':
					$text = 'All fields must be filled in!';
					break;
			}
		}
		
		return domTag('div', array(
			'class' => 'status-message ' . $class,
			'content' => $text
		));
	}
}