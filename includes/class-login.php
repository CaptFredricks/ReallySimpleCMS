<?php
/**
 * Core class used to implement the Login object.
 * @since 2.0.0[a]
 *
 * Controls the login/logout process for the user.
 */
class Login {
	/**
	 * Construct the 'Log In' form.
	 * @since 2.0.3[a]
	 *
	 * @access public
	 * @return null
	 */
	public function logInForm() {
		// Display a confirmation if the 'Forgot Password' form has just been submitted
		echo isset($_GET['pw_forgot']) && $_GET['pw_forgot'] === 'confirm' ? $this->statusMessage('Check your email for a confirmation to reset your password.', true) : '';
		
		// Display a confirmation if the 'Reset Password' form has just been submitted
		echo isset($_GET['pw_reset']) && $_GET['pw_reset'] === 'confirm' ? $this->statusMessage('Your password has been successfully reset.', true) : '';
		
		// Validate the form data and display any error messages
		echo isset($_POST['submit']) ? $this->validateLoginData($_POST) : '';
		?>
		<form class="data-form" action="login.php" method="post">
			<p class="login-field">
				<label for="login">Username or Email<br><input type="text" name="login" autofocus></label>
			</p>
			<p class="password-field">
				<label for="password">Password<br><input type="password" name="password"></label><button type="button" id="password-toggle" class="button" title="Show Password" data-visibility="hidden"><i class="far fa-eye"></i></button>
			</p>
			<p class="captcha-field">
				<label for="captcha">Captcha<br><input type="text" name="captcha" autocomplete="off"><img id="captcha" src="<?php echo INC.'/captcha.php'; ?>"></label>
			</p>
			<p class="remember-field">
				<label class="checkbox-label"><input type="checkbox" name="remember_login" value="checked"> <span>Keep me logged in</span></label>
			</p>
			<?php
			// Check whether a redirect URL has been specified
			if(isset($_GET['redirect'])) {
				?>
				<input type="hidden" name="redirect" value="<?php echo $_GET['redirect']; ?>">
				<?php
			}
			?>
			<input type="submit" class="button" name="submit" value="Log In">
		</form>
		<?php
		// Check whether the user has just submitted the 'Forgot Password' form and hide the 'forgot your password' link if so
		if(!isset($_GET['pw_forgot'])) {
			?>
			<a href="?action=forgot_password">Forgot your password?</a>
			<?php
		}
	}
	
	/**
	 * Validate the 'Log In' form data and log the user in.
	 * @since 2.0.0[a]
	 *
	 * @access private
	 * @param array $data
	 * @return null|string (null on no errors; string on error)
	 */
	private function validateLoginData($data) {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['login']) || empty($data['password']) || empty($data['captcha']))
			return $this->statusMessage('F');
		
		// Check whether the login used was an email
		if(strpos($data['login'], '@') !== false) {
			// Sanitize the email
			$email = $this->sanitizeData($data['login'], FILTER_SANITIZE_EMAIL);
		} else {
			// Sanitize the username
			$username = $this->sanitizeData($data['login'], '/[^\w.]/i');
		}
		
		// Sanitize the password
		$password = $this->sanitizeData($data['password']);
		
		// Sanitize the captcha
		$captcha = $this->sanitizeData($data['captcha'], '/[^a-zA-Z0-9]/i');
		
		// Make sure the captcha value is valid
		if(!$this->isValidCaptcha($captcha))
			return $this->statusMessage('The captcha is not valid.');
		
		do {			
			// Generate a random hash for the session value
			$session = generateHash(12);
		} while($this->sessionExists($session));
		
		// Check whether the email or username variable is set
		if(isset($email)) {
			// Make sure the email and password are valid
			if(!$this->emailExists($email) || !$this->isValidPassword($email, $password))
				return $this->statusMessage('The email and/or password is not valid.');
			
			// Update the user in the database
			$rs_query->update('users', array('last_login'=>'NOW()', 'session'=>$session), array('email'=>$email));
		} elseif(isset($username)) {
			// Make sure the username and password are valid
			if(!$this->usernameExists($username) || !$this->isValidPassword($username, $password))
				return $this->statusMessage('The username and/or password is not valid.');
			
			// Update the user in the database
			$rs_query->update('users', array('last_login'=>'NOW()', 'session'=>$session), array('username'=>$username));
		}
		
		// Check whether the 'keep me logged in' checkbox has been checked
		if(isset($data['remember_login']) && $data['remember_login'] === 'checked') {
			// Create a cookie with the session value that expires in 30 days
			setcookie('session', $session, time() + 60 * 60 * 24 * 30, '/');
		} else {
			// Create a cookie with the session value that expires when the browser is closed
			setcookie('session', $session, 0, '/');
		}
		
		// Unset the secure login code
		unset($_SESSION['secure_login']);
		
		// Check whether the page needs to redirect to a specific URL after login
		if(isset($data['redirect']))
			redirect($data['redirect']);
		else
			redirect(trailingSlash(ADMIN));
	}
	
	/**
	 * Check whether a password is valid.
	 * @since 2.0.0[a]
	 *
	 * @access private
	 * @param string $login
	 * @param string $password
	 * @return bool
	 */
	private function isValidPassword($login, $password) {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the login used was an email
		if(strpos($login, '@') !== false) {
			// Fetch the user's email from the database
			$db_password = $rs_query->selectField('users', 'password', array('email'=>$login));
		} else {
			// Fetch the user's password from the database
			$db_password = $rs_query->selectField('users', 'password', array('username'=>$login));
		}
		
		// Return true if the password is valid
		return !empty($db_password) && password_verify($password, $db_password);
	}
	
	/**
	 * Check whether a captcha value is valid.
	 * @since 2.0.0[a]
	 *
	 * @access private
	 * @param string $captcha
	 * @return bool
	 */
	private function isValidCaptcha($captcha) {
		return !empty($_SESSION['secure_login']) && $captcha === $_SESSION['secure_login'];
	}
	
	/**
	 * Check whether a session already exists in the database.
	 * @since 2.0.2[a]
	 *
	 * @access private
	 * @param string $session
	 * @return bool
	 */
	private function sessionExists($session) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the number of times the session appears in the database
		$count = $rs_query->selectRow('users', 'COUNT(session)', array('session'=>$session));
		
		// Return true if the count is greater than zero
		return $count > 0;
	}
	
	/**
	 * Check whether an email already exists in the database.
	 * @since 2.0.2[a]
	 *
	 * @access private
	 * @param string $email
	 * @return bool
	 */
	private function emailExists($email) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the number of times the email appears in the database
		$count = $rs_query->selectRow('users', 'COUNT(email)', array('email'=>$email));
		
		// Return true if the count is greater than zero
		return $count > 0;
	}
	
	/**
	 * Check whether a username already exists in the database.
	 * @since 2.0.0[a]
	 *
	 * @access private
	 * @param string $username
	 * @return bool
	 */
	private function usernameExists($username) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the number of times the username appears in the database
		$count = $rs_query->selectRow('users', 'COUNT(username)', array('username'=>$username));
		
		// Return true if the count is greater than zero
		return $count > 0;
	}
	
	/**
	 * Sanitize user input data.
	 * @since 2.0.1[a]
	 *
	 * @access private
	 * @param string $data
	 * @param string $filter (optional; default: null)
	 * @return string
	 */
	private function sanitizeData($data, $filter = null) {
		// Check whether a filter has been provided
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
	 * @param string $text
	 * @param bool $success (optional; default: false)
	 * @return string
	 */
	private function statusMessage($text, $success = false) {
		// Determine whether the status is success or failure
		if($success === true) {
			// Set the status message's class to 'success'
			$class = 'success';
		} else {
			// Set the status message's class to 'failure'
			$class = 'failure';
			
			// Check whether the provided text value matches one of the predefined cases
			switch($text) {
				case 'F': case 'f':
					// Status message for form fields that are left empty
					$text = 'All fields must be filled in!';
					break;
			}
		}
		
		// Return the status message
		return '<div class="status-message '.$class.'">'.$text.'</div>';
	}
	
	/**
	 * Log the user out.
	 * @since 2.0.1[a]
	 *
	 * @access public
	 * @param string $session
	 * @return null
	 */
	public function userLogout($session) {
		// Extend the Query object
		global $rs_query;
		
		// Update the user's session in the database
		$rs_query->update('users', array('session'=>null), array('session'=>$session));
		
		// Delete the session cookie
		setcookie('session', '', 1, '/');
		
		// Redirect to the login page
		redirect('../login.php');
	}
	
	/**
	 * Construct the 'Forgot Password' form.
	 * @since 2.0.3[a]
	 *
	 * @access public
	 * @return null
	 */
	public function forgotPasswordForm() {
		// Display an error if the password reset security key is invalid
		echo isset($_GET['error']) && $_GET['error'] === 'invalid_key' ? $this->statusMessage('Your security key is invalid. Submit this form to get a new password reset link.') : '';
		
		// Display an error if the password reset security key has expired
		echo isset($_GET['error']) && $_GET['error'] === 'expired_key' ? $this->statusMessage('Your security key has expired. Submit this form to get a new password reset link.') : '';
		
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
	 * @param array $data
	 * @return null|string (null on no errors; string on error)
	 */
	private function validateForgotPasswordData($data) {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['login']))
			return $this->statusMessage('F');
		
		// Generate a hashed key
		$key = generateHash(20, false, time());
		
		// Fetch the site's URL
		$site_url = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
		
		// Check whether the login used was an email
		if(strpos($data['login'], '@') !== false) {
			// Sanitize the email
			$email = $this->sanitizeData($data['login'], FILTER_SANITIZE_EMAIL);
			
			// Make sure the email exists in the database
			if(!$this->emailExists($email))
				return $this->statusMessage('The email you provided is not registered on this website.');
			
			// Fetch the user's id and username from the database
			list($id, $username) = array_values($rs_query->selectRow('users', array('id', 'username'), array('email'=>$email)));
			
			// Construct the email's subject line
			$subject = getSetting('site_title', false).' – Password Reset';
			
			// Construct the 'Reset Password' link
			$pw_reset_link = $site_url.'/login.php?login='.$username.'&key='.$key.'&action=reset_password';
			
			// Construct the email's text
			$message = 'A request has been made to reset the password for the user <strong>'.$username.'</strong> on "'.getSetting('site_title', false).'".<br><br>If this was you, please click the link below to reset your password. If not, you may disregard this email.<br><br><a href="'.$pw_reset_link.'">Reset your password</a>';
			
			// Format the email's content
			$content = formatEmail('Reset Password', array('message'=>$message));
			
			// Set the content headers (to allow for HTML-formatted emails)
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=iso-8859-1";
			$headers[] = "From: ReallySimpleCMS <rscms@".$_SERVER['HTTP_HOST'].">";
			
			// Make sure the email can be sent
			if(mail($email, $subject, $content, implode("\r\n", $headers))) {
				// Update the user's security key in the database
				$rs_query->update('users', array('security_key'=>$key), array('id'=>$id));
				
				// Redirect to the 'Log In' form
				redirect('login.php?pw_forgot=confirm');
			} else {
				// Return a failure status message
				return $this->statusMessage('ReallySimpleCMS encountered an error and could not send an email. Please contact this website\'s administrator or web host.');
			}
		} else {
			// Sanitize the username
			$username = $this->sanitizeData($data['login'], '/[^a-zA-Z0-9_\.]/i');
			
			// Make sure the username exists in the database
			if(!$this->usernameExists($username))
				return $this->statusMessage('The username you provided is not registered on this website.');
			
			// Fetch the user's id and email from the database
			list($id, $email) = array_values($rs_query->selectRow('users', array('id', 'email'), array('username'=>$username)));
			
			// Construct the email's subject line
			$subject = getSetting('site_title', false).' – Password Reset';
			
			// Construct the 'Reset Password' link
			$pw_reset_link = $site_url.'/login.php?login='.$username.'&key='.$key.'&action=reset_password';
			
			// Construct the email's text
			$message = 'A request has been made to reset the password for the user <strong>'.$username.'</strong> on "'.getSetting('site_title', false).'".<br><br>If this was you, please click the link below to reset your password. If not, you may disregard this email.<br><br><a href="'.$pw_reset_link.'">Reset your password</a>';
			
			// Format the email's content
			$content = formatEmail('Reset Password', array('message'=>$message));
			
			// Set the content headers (to allow for HTML-formatted emails)
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=iso-8859-1";
			$headers[] = "From: ReallySimpleCMS <rscms@".$_SERVER['HTTP_HOST'].">";
			
			// Make sure the email can be sent
			if(mail($email, $subject, $content, implode("\r\n", $headers))) {
				// Update the user's security key in the database
				$rs_query->update('users', array('security_key'=>$key), array('id'=>$id));
				
				// Redirect to the 'Log In' form
				redirect('login.php?pw_forgot=confirm');
			} else {
				// Return a failure status message
				return $this->statusMessage('ReallySimpleCMS encountered an error and could not send an email. Please contact this website\'s administrator or web host.');
			}
		}
	}
	
	/**
	 * Construct the 'Reset Password' form.
	 * @since 2.0.5[a]
	 *
	 * @access public
	 * @return null
	 */
	public function resetPasswordForm() {
		// Set a name for the password reset cookie
		$cookie_name = 'pw-reset-'.COOKIE_HASH;
		
		// Check whether the login and key are in the query string
		if(isset($_GET['login']) && isset($_GET['key'])) {
			// Create a cookie that expires when the browser is closed
			setcookie($cookie_name, $_GET['login'].':'.$_GET['key'], 0, '/login.php');
			
			// Redirect to remove the 'login' and 'key' values from the query string
			redirect('login.php?action=reset_password');
		}
		
		// Check whether the reset password cookie is set
		if(isset($_COOKIE[$cookie_name])) {
			// Fetch the cookie's data
			list($login, $key) = explode(':', $_COOKIE[$cookie_name]);
			
			// Check whether the reset password cookie is valid
			if(!$this->isValidCookie($login, $key)) {
				// Delete the cookie
				setcookie($cookie_name, '', 1, '/login.php');
				
				// Redirect to the 'Forgot Password' form and display an error
				redirect('login.php?action=forgot_password&error=invalid_key');
			}
		} else {
			// Redirect to the 'Forgot Password' form and display an error
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
	 * Set the minimum password length.
	 * @since 2.0.7[a]
	 *
	 * @access private
	 * @var int
	 */
	private const PW_LENGTH = 8;
	
	/**
	 * Validate the reset password data.
	 * @since 2.0.5[a]
	 *
	 * @access private
	 * @param array $data
	 * @return null|string (null on no errors; string on error)
	 */
	private function validateResetPasswordData($data) {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['password']))
			return $this->statusMessage('F');
		
		// Make sure the password is long enough
		if(strlen($data['password']) < self::PW_LENGTH)
			return statusMessage('Password must be at least '.self::PW_LENGTH.' characters long.');
		
		// Check whether the reset password cookie is valid
		if($this->isValidCookie($data['login'], $data['key'])) {
			// Hash the password (encrypts the password for security purposes)
			$hashed_password = password_hash($data['password'], PASSWORD_BCRYPT, array('cost'=>10));
			
			// Update the user's password and security key in the database
			$rs_query->update('users', array('password'=>$hashed_password, 'security_key'=>null), array('username'=>$data['login']));
			
			// Delete the cookie
			setcookie('pw-reset-'.COOKIE_HASH, '', 1, '/login.php');
			
			// Redirect to the 'Log In' form
			redirect('login.php?pw_reset=confirm');
		} else {
			// Redirect to the 'Forgot Password' form and display an error
			redirect('login.php?action=forgot_password&error=invalid_key');
		}
	}
	
	/**
	 * Check whether a reset password cookie is valid.
	 * @since 2.0.6[a]
	 *
	 * @access private
	 * @param string $login
	 * @param string $key
	 * @return bool
	 */
	private function isValidCookie($login, $key) {
		// Extend the Query object
		global $rs_query;
		
		// Return true if the key is found for the user in the database
		return $rs_query->selectRow('users', 'COUNT(*)', array('username'=>$login, 'security_key'=>$key)) > 0;
	}
}