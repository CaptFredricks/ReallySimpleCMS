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
	public function loginForm() {
		// Display a confirmation if the 'Forgot Password' form has just been submitted
		echo isset($_GET['pw_forgot']) && $_GET['pw_forgot'] === 'confirm' ? $this->statusMessage('Check your email for a confirmation to reset your password.', true) : '';
		
		// Display a confirmation if the 'Reset Password' form has just been submitted
		echo isset($_GET['pw_reset']) && $_GET['pw_reset'] === 'confirm' ? $this->statusMessage('Your password has been successfully reset.', true) : '';
		
		// Validate the form data and display any error messages
		echo isset($_POST['submit']) ? $this->validateLoginData($_POST) : '';
		?>
		<form class="data-form" action="login.php" method="post">
			<p><label for="login">Username or Email<br><input type="text" name="login" autofocus></label></p>
			<p><label for="password">Password<br><input type="password" name="password"></label></p>
			<p><label for="captcha">Captcha<br><input type="text" name="captcha" autocomplete="off"><img id="captcha" src="<?php echo INC.'/captcha.php'; ?>"></label></p>
			<p><label class="checkbox-label"><input type="checkbox" name="remember_login" value="checked"> <span>Keep me logged in</span></label></p>
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
		// Extend the Query class
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
			$username = $this->sanitizeData($data['login'], '/[^a-zA-Z0-9_\.]/i');
		}
		
		// Sanitize the password
		$password = $this->sanitizeData($data['password']);
		
		// Sanitize the captcha
		$captcha = $this->sanitizeData($data['captcha'], '/[^a-zA-Z0-9]/i');
		
		// Make sure the captcha value is valid
		if(!$this->isValidCaptcha($captcha))
			return $this->statusMessage('The captcha is not valid.');
		
		// Create a list of characters to randomly choose from
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
		
		// Create an empty variable to hold the session value
		$session = '';
		
		do {
			// Grab a set of random characters and put them in a new string
			for($i = 0; $i < 12; $i++)
				$session .= substr($chars, rand(0, strlen($chars) - 1), 1);
			
			// Hash the session variable
			$session = md5(md5($session));
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
			// Create a cookie with the session value that expires in 30 minutes
			setcookie('session', $session, time() + 60 * 30, '/');
		}
		
		// Unset the secure login code
		unset($_SESSION['secure_login']);
		
		// Check whether the page needs to redirect to a specific URL after login
		if(isset($_GET['redirect']))
			redirect($_GET['redirect']);
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
		// Extend the Query class
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
		// Extend the Query class
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
		// Extend the Query class
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
		// Extend the Query class
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
		// Extend the Query class
		global $rs_query;
		
		// Update the user's session in the database
		$rs_query->update('users', array('session'=>null), array('session'=>$session));
		
		// Delete the cookie
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
		// Extend the Query class
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['login']))
			return $this->statusMessage('F');
		
		// Generate a hashed key
		$key = generateHash(20, false, time());
		
		// Check whether the login used was an email
		if(strpos($data['login'], '@') !== false) {
			// Sanitize the email
			$email = $this->sanitizeData($data['login'], FILTER_SANITIZE_EMAIL);
			
			// Make sure the email exists in the database
			if(!$this->emailExists($email))
				return $this->statusMessage('The email you provided is not registered on this website.');
			
			// Fetch the user's username from the database
			$username = $rs_query->selectField('users', 'username', array('email'=>$email));
			
			// Construct the subject line
			$subject = getSetting('site_title', false).' – Password Reset';
			
			// Fetch the site's url
			$site_url = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
			
			// Construct the 'Reset Password' link
			$pw_reset_link = $site_url.'/login.php?login='.$username.'&key='.$key.'&action=reset_password';
			
			// Construct the message text
			$message = 'A request has been made to reset the password for the user <strong>'.$username.'</strong> on "'.getSetting('site_title', false).'".<br><br>If this was you, please click the link below to reset your password. If not, you may disregard this email.<br><br><a href="'.$pw_reset_link.'">Reset your password</a>';
			
			// Format the message content
			$content = formatEmail('Reset Password', array('message'=>$message));
			
			// Set the content headers (to allow for HTML-formatted emails)
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=iso-8859-1";
			$headers[] = "From: ".getSetting('site_title', false)." <".getSetting('admin_email', false).">";
			
			// Make sure the email can be sent
			if(mail($email, $subject, $content, implode("\r\n", $headers))) {
				// Create a cookie with the session value that expires when the browser is closed
				setcookie('pw-reset-'.generateHash(30, false, time()), $username.':'.$key, 0, '/login.php');
				
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
			
			// Fetch the user's email from the database
			$email = $rs_query->selectField('users', 'email', array('username'=>$username));
			
			// Construct the subject line
			$subject = getSetting('site_title', false).' – Password Reset';
			
			// Fetch the site's url
			$site_url = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
			
			// Construct the 'Reset Password' link
			$pw_reset_link = $site_url.'/login.php?login='.$username.'&key='.$key.'&action=reset_password';
			
			// Construct the message text
			$message = 'A request has been made to reset the password for the user <strong>'.$username.'</strong> on "'.getSetting('site_title', false).'".<br><br>If this was you, please click the link below to reset your password. If not, you may disregard this email.<br><br><a href="'.$pw_reset_link.'">Reset your password</a>';
			
			// Format the message content
			$content = formatEmail('Reset Password', array('message'=>$message));
			
			// Set the content headers (to allow for HTML-formatted emails)
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=iso-8859-1";
			$headers[] = "From: ".getSetting('site_title', false)." <".getSetting('admin_email', false).">";
			
			// Make sure the email can be sent
			if(mail($email, $subject, $content, implode("\r\n", $headers))) {
				// Create a cookie with the session value that expires when the browser is closed
				setcookie('pw-reset-'.generateHash(30, false, time()), $username.':'.$key, 0, '/login.php');
				
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
	 * @param array $cookie_data
	 * @return null
	 */
	public function resetPasswordForm($cookie_data) {
		// Check whether the data in the query string matches the data in the cookie
		if((isset($_GET['login']) && $_GET['login'] === $cookie_data[0]) && (isset($_GET['key']) && $_GET['key'] === $cookie_data[1]))
			// Redirect to remove the 'login' and 'key' values from the query string
			redirect('login.php?action=reset_password');
		
		// Validate the form data and display any error messages
		echo isset($_POST['submit']) ? $this->validateResetPasswordData($_POST) : '';
		?>
		<form class="data-form" action="" method="post">
			<p><label for="password">New Password<br><input type="text" name="password" value="<?php echo generatePassword(); ?>" autofocus></label></p>
			<input type="hidden" name="login" value="<?php echo $cookie_data[0]; ?>">
			<input type="hidden" name="key" value="<?php echo $cookie_data[1]; ?>">
			<input type="submit" class="button" name="submit" value="Reset Password">
		</form>
		<?php
	}
	
	/**
	 * Validate the reset password data.
	 * @since 2.0.5[a]
	 *
	 * @access private
	 * @param array $data
	 * @return null|string (null on no errors; string on error)
	 */
	private function validateResetPasswordData($data) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch all cookie keynames
		$cookies = array_keys($_COOKIE);
		
		// Loop through the cookies
		foreach($cookies as $cookie) {
			// Check whether the reset password cookie is set
			if(strpos($cookie, 'pw-reset') !== false) {
				// Fetch the cookie's name
				$cookie_name = $cookie;
				break;
			}
		}
		
		// Fetch the cookie's data
		$cookie_data = explode(':', $_COOKIE[$cookie_name]);
		
		// Grab just the key value
		$key = array_pop($cookie_data);
		
		// Check whether the submitted key is still valid
		if($data['key'] === $key) {
			// Hash the password (encrypts the password for security purposes)
			$hashed_password = password_hash($data['password'], PASSWORD_BCRYPT, array('cost'=>10));
			
			// Update the user's password in the database
			$rs_query->update('users', array('password'=>$hashed_password), array('username'=>$data['login']));
			
			// Delete the cookie
			setcookie($cookie_name, '', 1, '/login.php');
			
			// Redirect to the 'Log In' form
			redirect('login.php?pw_reset=confirm');
		}
	}
}