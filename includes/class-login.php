<?php
/**
 * Core class used to implement the Login object.
 * @since 2.0.0[a]
 *
 * Controls the login/logout process for the user.
 */
class Login {
	/**
	 * Validate the form data and log the user in.
	 * @since 2.0.0[a]
	 *
	 * @access public
	 * @param array $data
	 * @return 
	 */
	public function userLogin($data) {
		// Extend the Query class
		global $rs_query;
		
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
		
		// Make sure no required fields are empty
		if(empty($data['login']) || empty($data['password']) || empty($data['captcha']))
			return $this->errorMessage('F');
		
		// Check whether the login used was an email
		if(strpos($data['login'], '@') !== false && strpos($data['login'], '.') !== false) {
			// Sanitize the email
			$email = $this->sanitizeData($data['login'], '/[^a-zA-Z0-9@\.]/i');
		} else {
			// Sanitize the username
			$username = $this->sanitizeData($data['login'], '/[^a-zA-Z0-9]/i');
		}
		
		// Sanitize the password
		$password = $this->sanitizeData($data['password']);
		
		// Sanitize the captcha
		$captcha = $this->sanitizeData($data['captcha'], '/[^a-zA-Z0-9]/i');
		
		// Make sure the captcha value is valid
		if(!$this->isValidCaptcha($captcha))
			return $this->errorMessage('The captcha is not valid.');
		
		// Check whether the email or username variable is set
		if(isset($email)) {
			// Make sure the email and password are valid
			if(!$this->emailExists($email) || !$this->isValidPassword($email, $password))
				return $this->errorMessage('The email and/or password is not valid.');
			
			// Update the user in the database
			$rs_query->update('users', array('last_login'=>'NOW()', 'session'=>$session), array('email'=>$email));
		} elseif(isset($username)) {
			// Make sure the username and password are valid
			if(!$this->usernameExists($username) || !$this->isValidPassword($username, $password))
				return $this->errorMessage('The username and/or password is not valid.');
			
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
		if(strpos($login, '@') !== false && strpos($login, '.') !== false) {
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
	 * @param string $pattern (optional; default: null)
	 * @return string
	 */
	private function sanitizeData($data, $pattern = null) {
		// Check whether a pattern has been provided
		if($pattern === null) {
			// Trim off whitespace characters and return the data
			return trim($data);
		} else {
			// Replace any characters not specified in the patter, trim off whitespace characters, and return the data
			return trim(preg_replace($pattern, '', $data));
		}
	}
	
	/**
	 * Construct an error message.
	 * @since 2.0.0[a]
	 *
	 * @access private
	 * @param string $text
	 * @return string
	 */
	private function errorMessage($text) {
		switch($text) {
			case 'F': case 'f':
				$text = 'All fields must be filled in!';
		}
		
		// Return the error message
		return '<div class="error-message">'.$text.'</div>';
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
}