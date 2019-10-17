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
		
		// Grab a set of random characters and put them in a new string
		for($i = 0; $i < 12; $i++)
			$session .= substr($chars, rand(0, strlen($chars) - 1), 1);
		
		// Hash the session variable
		$session = md5(md5($session));
		
		// Make sure no required fields are empty
		if(empty($data['username_email']) || empty($data['password']) || empty($data['captcha']))
			return $this->errorMessage('F');
		
		// Make sure the username and password are valid
		if(!$this->usernameExists($data['username_email']) || !$this->isValidPassword($data['username_email'], $data['password']))
			return $this->errorMessage('The username and/or password do not match.');
		
		// Make sure the captcha value is valid
		if(!$this->isValidCaptcha($data['captcha']))
			return $this->errorMessage('The captcha is not valid.');
		
		// Update the user in the database
		$rs_query->update('users', array('last_login'=>'NOW()', 'session'=>$session), array('username'=>$data['username_email']));
		
		// Fetch the user from the database
		$user = $rs_query->selectRow('users', array('id', 'username', 'session', 'role'), array('username'=>$data['username_email']));
		
		// Set the session values
		$_SESSION['id'] = $user['id'];
		$_SESSION['username'] = $user['username'];
		$_SESSION['session'] = $user['session'];
		$_SESSION['role'] = $user['role'];
		
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
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	private function isValidPassword($username, $password) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the user's password from the database
		$db_password = $rs_query->selectField('users', 'password', array('username'=>$username));
		
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
}