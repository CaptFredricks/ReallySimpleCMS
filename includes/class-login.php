<?php
/**
 * Core class used to implement the Login object.
 * @since 2.0.0[a]
 *
 * Controls the login/logout process for the user.
 */
class Login {
	/**
	 * The minimum password length.
	 * @since 2.0.7[a]
	 *
	 * @access private
	 * @var int
	 */
	private const PW_LENGTH = 8;
	
	/**
	 * Whether HTTPS is enabled on the server.
	 * @since 1.1.4[b]
	 *
	 * @access private
	 * @var bool
	 */
	private $https;
	
	/**
	 * The current user's IP address.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access private
	 * @var string
	 */
	private $ip_address;
	
	/**
	 * Class constructor.
	 * @since 1.1.4[b]
	 *
	 * @access public
	 */
	public function __construct() {
		$this->https = !empty($_SERVER['HTTPS']) ? true : false;
		$this->ip_address = $_SERVER['REMOTE_ADDR'];
	}
	
	/**
	 * Construct the "Log In" form.
	 * @since 2.0.3[a]
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
		
		// Validate the form data and display any error messages
		echo isset($_POST['submit']) ? $this->validateLoginData($_POST) : '';
		?>
		<form class="data-form" action="login.php" method="post">
			<p class="login-field">
				<label for="login">Username or Email<br><input type="text" name="login" autofocus></label>
			</p>
			<p class="password-field">
				<label for="password">Password<br><input type="password" name="password"></label><button type="button" id="password-toggle" class="button" title="Show Password" data-visibility="hidden"><i class="fa-regular fa-eye"></i></button>
			</p>
			<p class="captcha-field">
				<label for="captcha">Captcha<br><input type="text" name="captcha" autocomplete="off"><img id="captcha" src="<?php echo INC . '/captcha.php'; ?>"></label>
			</p>
			<p class="remember-field">
				<label class="checkbox-label"><input type="checkbox" name="remember_login" value="checked"> <span>Keep me logged in</span></label>
			</p>
			<?php
			if(isset($_GET['redirect'])) {
				?>
				<input type="hidden" name="redirect" value="<?php echo $_GET['redirect']; ?>">
				<?php
			}
			?>
			<input type="submit" class="button" name="submit" value="Log In">
		</form>
		<?php
		if(!isset($_GET['pw_forgot'])) {
			?>
			<a href="?action=forgot_password">Forgot your password?</a>
			<?php
		}
	}
	
	/**
	 * Validate the "Log In" form data and log the user in.
	 * @since 2.0.0[a]
	 *
	 * @access private
	 * @param array $data -- The form data.
	 * @return string
	 */
	private function validateLoginData($data): string {
		global $rs_query;
		
		$offsite_redirect = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
		
		if(empty($data['login']) || empty($data['password']) || empty($data['captcha']))
			return $this->statusMsg('F');
		
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
			}
		}
		
		if($this->isBlacklisted($email ?? $username)) {
			// Check whether the blacklist duration is indefinite and redirect off-site if so
			if($this->getBlacklistDuration($email ?? $username) === 0) {
				redirect($offsite_redirect);
			} // Otherwise, return an error
			elseif($this->getBlacklistDuration($email ?? $username) > 0) {
				return $this->statusMsg('You\'re attempting to log in too fast! Try again later.');
			}
		}
		
		$password = $this->sanitizeData($data['password']);
		$captcha = $this->sanitizeData($data['captcha'], '/[^a-zA-Z0-9]/i');
		
		if(getSetting('track_login_attempts')) {
			$login_attempt = $rs_query->insert('login_attempts', array(
				'login' => ($email ?? $username),
				'ip_address' => $this->ip_address,
				'date' => 'NOW()'
			));
		}
		
		if(!$this->isValidCaptcha($captcha))
			return $this->statusMsg('The captcha is not valid.');
		
		do {
			// Generate a random hash for the session value
			$session = generateHash(12);
		} while($this->sessionExists($session));
		
		if(isset($email)) {
			if(!$this->emailExists($email) || !$this->isValidPassword($email, $password))
				return $this->statusMsg('The email and/or password is not valid.');
			
			$rs_query->update('users', array(
				'last_login' => 'NOW()',
				'session' => $session
			), array('email' => $email));
		} elseif(isset($username)) {
			if(!$this->usernameExists($username) || !$this->isValidPassword($username, $password))
				return $this->statusMsg('The username and/or password is not valid.');
			
			$rs_query->update('users', array(
				'last_login' => 'NOW()',
				'session' => $session
			), array('username' => $username));
		}
		
		// Check whether the login attempt was tracked
		if(isset($login_attempt))
			$rs_query->update('login_attempts', array('status' => 'success'), array('id' => $login_attempt));
		
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
	 * Check whether the login or IP address is blacklisted.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access private
	 * @param string $login -- The login or IP address.
	 * @return bool
	 */
	private function isBlacklisted($login): bool {
		global $rs_query;
		
		return $rs_query->select('login_blacklist', 'COUNT(name)', array('name' => $login)) > 0;
	}
	
	/**
	 * Check whether a password is valid.
	 * @since 2.0.0[a]
	 *
	 * @access private
	 * @param string $login -- The username or email.
	 * @param string $password -- The password.
	 * @return bool
	 */
	private function isValidPassword($login, $password): bool {
		global $rs_query;
		
		// Check whether the login used was an email
		if(str_contains($login, '@'))
			$db_password = $rs_query->selectField('users', 'password', array('email' => $login));
		else
			$db_password = $rs_query->selectField('users', 'password', array('username' => $login));
		
		return !empty($db_password) && password_verify($password, $db_password);
	}
	
	/**
	 * Check whether a captcha value is valid.
	 * @since 2.0.0[a]
	 *
	 * @access private
	 * @param string $captcha -- The captcha value.
	 * @return bool
	 */
	private function isValidCaptcha($captcha): bool {
		return !empty($_SESSION['secure_login']) && $captcha === $_SESSION['secure_login'];
	}
	
	/**
	 * Check whether the login or IP address should be blacklisted.
	 * @since 1.2.0[b]{ss-05}
	 *
	 * @access private
	 * @param string $ip_address -- The IP address.
	 * @param string $login -- The login.
	 */
	private function shouldBlacklist($ip_address, $login): void {
		global $rs_query;
		
		$last_blacklisted_ip = $rs_query->selectField('login_attempts', 'last_blacklisted_ip', array(
			'ip_address' => $ip_address
		), 'id', 'ASC', '1');
		
		$failed_logins = $rs_query->select('login_attempts', 'COUNT(*)', array(
			'ip_address' => $ip_address,
			'date' => array('>', $last_blacklisted_ip),
			'status' => 'failure'
		));
		
		$login_rules = $rs_query->select('login_rules', '*', array('type' => 'ip_address'), 'attempts', 'DESC');
		
		foreach($login_rules as $login_rule) {
			// Check whether the failed logins exceed the rule's threshold
			if($failed_logins >= $login_rule['attempts']) {
				if(!$this->isBlacklisted($ip_address)) {
					$attempts = $rs_query->select('login_attempts', 'COUNT(*)', array(
						'ip_address' => $ip_address,
						'status' => 'failure'
					));
					
					// Create a blacklist for the IP address
					$rs_query->insert('login_blacklist', array(
						'name' => $ip_address,
						'attempts' => $attempts,
						'blacklisted' => 'NOW()',
						'duration' => $login_rule['duration'],
						'reason' => 'too many failed login attempts'
					));
					
					// Update the last blacklisted date of the IP address and return
					$rs_query->update('login_attempts', array('last_blacklisted_ip' => 'NOW()'), array(
						'ip_address' => $ip_address
					));
					
					// Fetch all logins associated with the IP address from the database
					$logins = $rs_query->select('login_attempts', array('DISTINCT', 'login'), array(
						'ip_address' => $ip_address
					));
					
					foreach($logins as $login) {
						$session = $rs_query->selectField('users', 'session', array(
							'logic' => 'OR',
							'username' => $login['login'],
							'email' => $login['login']
						));
						
						if(!is_null($session)) {
							$rs_query->update('users', array('session' => null), array('session' => $session));
							
							// Check whether the cookie's value matches the session value and delete it if so
							if(isset($_COOKIE['session']) && $_COOKIE['session'] === $session)
								setcookie('session', '', 1, '/');
						}
					}
				}
				
				return;
			}
		}
		
		$last_blacklisted_login = $rs_query->selectField('login_attempts', 'last_blacklisted_login', array(
			'login' => $login
		), 'id', 'ASC', '1');
		
		$failed_logins = $rs_query->select('login_attempts', 'COUNT(*)', array(
			'login' => $login,
			'date' => array('>', $last_blacklisted_login),
			'status' => 'failure'
		));
		
		$login_rules = $rs_query->select('login_rules', '*', array('type' => 'login'), 'attempts', 'DESC');
		
		foreach($login_rules as $login_rule) {
			// Check whether the failed logins exceed the rule's threshold
			if($failed_logins >= $login_rule['attempts']) {
				if(!$this->isBlacklisted($login)) {
					// Fetch the total number of failed login attempts from the database
					$attempts = $rs_query->select('login_attempts', 'COUNT(*)', array(
						'login' => $login,
						'status' => 'failure'
					));
					
					// Create a blacklist for the login
					$rs_query->insert('login_blacklist', array(
						'name' => $login,
						'attempts' => $attempts,
						'blacklisted' => 'NOW()',
						'duration' => $login_rule['duration'],
						'reason' => 'too many failed login attempts'
					));
					
					// Update the last blacklisted date of the login
					$rs_query->update('login_attempts', array('last_blacklisted_login' => 'NOW()'), array(
						'login' => $login
					));
					
					$session = $rs_query->selectField('users', 'session', array(
						'logic' => 'OR',
						'username' => $login,
						'email' => $login
					));
					
					if(!is_null($session)) {
						$rs_query->update('users', array('session' => null), array('session' => $session));
						
						// Check whether the cookie's value matches the session value and delete it if so
						if(isset($_COOKIE['session']) && $_COOKIE['session'] === $session)
							setcookie('session', '', 1, '/');
					}
				}
				
				return;
			}
		}
	}
	
	/**
	 * Check whether a session already exists in the database.
	 * @since 2.0.2[a]
	 *
	 * @access private
	 * @param string $session -- The session value.
	 * @return bool
	 */
	private function sessionExists($session): bool {
		global $rs_query;
		
		return $rs_query->selectRow('users', 'COUNT(session)', array('session' => $session)) > 0;
	}
	
	/**
	 * Check whether an email already exists in the database.
	 * @since 2.0.2[a]
	 *
	 * @access private
	 * @param string $email -- The email.
	 * @return bool
	 */
	private function emailExists($email): bool {
		global $rs_query;
		
		return $rs_query->selectRow('users', 'COUNT(email)', array('email' => $email)) > 0;
	}
	
	/**
	 * Check whether a username already exists in the database.
	 * @since 2.0.0[a]
	 *
	 * @access private
	 * @param string $username -- The username.
	 * @return bool
	 */
	private function usernameExists($username): bool {
		global $rs_query;
		
		return $rs_query->selectRow('users', 'COUNT(username)', array('username' => $username)) > 0;
	}
	
	/**
	 * Fetch a blacklist's duration.
	 * @since 1.2.0[b]{ss-01}
	 *
	 * @access private
	 * @param string $name -- The blacklist's name.
	 * @return int
	 */
	private function getBlacklistDuration($name): int {
		global $rs_query;
		
		// Fetch the blacklist's duration from the database
		$blacklist = $rs_query->selectRow('login_blacklist', array('blacklisted', 'duration'), array(
			'name' => $name
		));
		
		if(empty($blacklist)) {
			// Set the duration to expired
			$duration = -1;
		} else {
			// Calculate the expiration date
			$time = new DateTime($blacklist['blacklisted']);
			$time->add(new DateInterval('PT' . $blacklist['duration'] . 'S'));
			$expiration = $time->format('Y-m-d H:i:s');
			
			// Check whether the blacklist has expired
			if(date('Y-m-d H:i:s') >= $expiration && $blacklist['duration'] !== 0) {
				// Set the duration to expired
				$duration = -1;
				
				$rs_query->delete('login_blacklist', array('name' => $name));
			} else {
				// Set the duration
				$duration = (int)$blacklist['duration'];
			}
		}
		
		return $duration;
	}
	
	/**
	 * Sanitize user input data.
	 * @since 2.0.1[a]
	 *
	 * @access private
	 * @param string $data -- The data to sanitize.
	 * @param string $filter (optional) -- The filter to use.
	 * @return string
	 */
	private function sanitizeData($data, $filter = null): string {
		if(is_null($filter)) {
			// Trim off whitespace characters, strip off HTML and/or PHP tags and return the data
			return strip_tags(trim($data));
		} elseif(is_int($filter)) {
			// Strip off HTML and/or PHP tags, run the data through a filter, and return the data
			return filter_var(strip_tags($data), $filter);
		} else {
			// Strip off HTML and/or PHP tags, replace any characters not specified in the filter, and return the data
			return preg_replace($filter, '', strip_tags($data));
		}
	}
	
	/**
	 * Construct a status message.
	 * @since 2.0.0[a]
	 *
	 * @access private
	 * @param string $text -- The message's text.
	 * @param bool $success (optional) -- Whether the submission was successful.
	 * @return string
	 */
	private function statusMsg($text, $success = false): string {
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
		
		return '<div class="status-message ' . $class . '">' . $text . '</div>';
	}
	
	/**
	 * Log the user out.
	 * @since 2.0.1[a]
	 *
	 * @access public
	 * @param string $session -- The session value.
	 */
	public function userLogout($session): void {
		global $rs_query;
		
		$rs_query->update('users', array('session' => null), array('session' => $session));
		
		// Delete the session cookie
		setcookie('session', '', 1, '/');
		
		redirect('../login.php');
	}
	
	/**
	 * Construct the "Forgot Password" form.
	 * @since 2.0.3[a]
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
		
		// Validate the form data and display any error messages
		echo isset($_POST['submit']) ? $this->validateForgotPasswordData($_POST) : '';
		?>
		<form class="data-form" action="" method="post">
			<p>Enter your username or email below and you will receive a link to reset your password in an email.</p>
			<p>Remembered your password? <a href="login.php">Log in</a> instead.</p>
			<p><label for="login">Username or Email<br><input type="text" name="login" autofocus></label></p>
			<input type="submit" class="button" name="submit" value="Get New Password">
		</form>
		<?php
	}
	
	/**
	 * Validate the forgotten password data.
	 * @since 2.0.5[a]
	 *
	 * @access private
	 * @param array $data -- The form data.
	 * @return string
	 */
	private function validateForgotPasswordData($data): string {
		global $rs_query;
		
		if(empty($data['login']))
			return $this->statusMsg('F');
		
		$key = generateHash(20, false, time());
		
		$site_url = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
		
		// Check whether the login used was an email
		if(str_contains($data['login'], '@')) {
			$email = $this->sanitizeData($data['login'], FILTER_SANITIZE_EMAIL);
			
			if(!$this->emailExists($email))
				return $this->statusMsg('The email you provided is not registered on this website.');
			
			list($id, $username) = array_values($rs_query->selectRow('users', array('id', 'username'), array(
				'email' => $email
			)));
		} else {
			$username = $this->sanitizeData($data['login'], '/[^a-zA-Z0-9_\.]/i');
			
			if(!$this->usernameExists($username))
				return $this->statusMsg('The username you provided is not registered on this website.');
			
			list($id, $email) = array_values($rs_query->selectRow('users', array('id', 'email'), array(
				'username' => $username
			)));
		}
		
		$subject = getSetting('site_title') . ' – Password Reset';
		
		$pw_reset_link = $site_url . '/login.php?login=' . $username . '&key=' . $key .
			'&action=reset_password';
		
		$message = 'A request has been made to reset the password for the user <strong>' . $username .
			'</strong> on "' . getSetting('site_title') . '".<br><br>If this was you, please click the link below to reset your password. If not, you may disregard this email.<br><br><a href="' .
			$pw_reset_link . '">Reset your password</a>';
		
		$content = formatEmail('Reset Password', array('message' => $message));
		
		// Set the content headers (to allow for HTML-formatted emails)
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/html; charset=iso-8859-1";
		$headers[] = "From: " . CMS_NAME . " <rscms@" . $_SERVER['HTTP_HOST'] . ">";
		
		// Make sure the email can be sent
		if(mail($email, $subject, $content, implode("\r\n", $headers))) {
			$rs_query->update('users', array('security_key' => $key), array('id' => $id));
			redirect('login.php?pw_forgot=confirm');
		} else {
			return $this->statusMsg(CMS_NAME . ' encountered an error and could not send an email. Please contact this website\'s administrator or web host.');
		}
	}
	
	/**
	 * Construct the "Reset Password" form.
	 * @since 2.0.5[a]
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
			
			// Redirect to remove the 'login' and 'key' values from the query string
			redirect('login.php?action=reset_password');
		}
		
		if(isset($_COOKIE[$cookie_name])) {
			// Fetch the cookie's data
			list($login, $key) = explode(':', $_COOKIE[$cookie_name]);
			
			if(!$this->isValidCookie($login, $key)) {
				// Delete the cookie
				setcookie($cookie_name, '', 1, '/login.php');
				
				redirect('login.php?action=forgot_password&error=invalid_key');
			}
		} else {
			redirect('login.php?action=forgot_password&error=expired_key');
		}
		
		// Validate the form data and display any error messages
		echo isset($_POST['submit']) ? $this->validateResetPasswordData($_POST) : '';
		?>
		<form class="data-form" action="" method="post">
			<p><label for="password">New Password<br><input type="text" name="password" value="<?php echo generatePassword(); ?>" autofocus></label></p>
			<input type="hidden" name="login" value="<?php echo $login; ?>">
			<input type="hidden" name="key" value="<?php echo $key; ?>">
			<input type="submit" class="button" name="submit" value="Reset Password">
		</form>
		<?php
	}
	
	/**
	 * Validate the reset password data.
	 * @since 2.0.5[a]
	 *
	 * @access private
	 * @param array $data -- The form data.
	 * @return string
	 */
	private function validateResetPasswordData($data): string {
		global $rs_query;
		
		if(empty($data['password']))
			return $this->statusMsg('F');
		
		if(strlen($data['password']) < self::PW_LENGTH)
			return $this->statusMsg('Password must be at least ' . self::PW_LENGTH . ' characters long.');
		
		if($this->isValidCookie($data['login'], $data['key'])) {
			// Hash the password (encrypts the password for security purposes)
			$hashed_password = password_hash($data['password'], PASSWORD_BCRYPT, array('cost' => 10));
			
			$rs_query->update('users', array(
				'password' => $hashed_password,
				'security_key' => null
			), array('username' => $data['login']));
			
			// Delete the cookie
			setcookie('pw-reset-' . COOKIE_HASH, '', 1, '/login.php');
			
			redirect('login.php?pw_reset=confirm');
		} else {
			redirect('login.php?action=forgot_password&error=invalid_key');
		}
	}
	
	/**
	 * Check whether a reset password cookie is valid.
	 * @since 2.0.6[a]
	 *
	 * @access private
	 * @param string $login -- The user's login.
	 * @param string $key -- The cookie hash key.
	 * @return bool
	 */
	private function isValidCookie($login, $key): bool {
		global $rs_query;
		
		return $rs_query->selectRow('users', 'COUNT(*)', array(
			'username' => $login,
			'security_key' => $key
		)) > 0;
	}
}