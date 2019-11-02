<?php
/**
 * Admin class used to implement the Profile object. Inherits from the User class.
 * @since 2.0.0[a]
 *
 * The user profile contains settings for individual users that can be changed at their will.
 */
class Profile extends User {
	/**
	 * Construct the 'Edit Profile' form.
	 * @since 2.0.0[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function editProfile($id) {
		// Extend the Query class
		global $rs_query;
		
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		
		// Fetch the user from the database
		$user = $rs_query->selectRow('users', '*', array('id'=>$id));
		
		// Fetch the user's metadata from the database
		$meta = $this->getUserMeta($id);
		?>
		<div class="heading-wrap">
			<h1>Edit Profile</h1>
			<?php
			// Display any returned messages
			echo $message;

			// Refresh the page after 4 seconds
			echo isset($_POST['submit']) ? '<meta http-equiv="refresh" content="4">' : '';
			?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					echo formRow(array('Username', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'username', 'value'=>$user['username']));
					echo formRow(array('Email', true), array('tag'=>'input', 'type'=>'email', 'class'=>'text-input required invalid init', 'name'=>'email', 'value'=>$user['email']));
					echo formRow('First Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'first_name', 'value'=>$meta['first_name']));
					echo formRow('Last Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'last_name', 'value'=>$meta['last_name']));
					echo formRow('Avatar', array('tag'=>'input', 'name'=>'avatar', 'value'=>$meta['avatar']));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Profile'));
					?>
				</table>
			</form>
			<a class="reset-password button" href="?action=reset_password">Reset Password</a>
		</div>
		<?php
	}
	
	/**
	 * Validate the form data.
	 * @since 2.0.6[a]
	 *
	 * @access private
	 * @param array $data
	 * @return 
	 */
	private function validateData($data) {
		// Extend the Query class and the user session data
		global $rs_query, $session;
		
		// Make sure no require fields are empty
		if(empty($data['username']) || empty($data['email']))
			return statusMessage('R');
		
		// Make sure the username is long enough
		if(strlen($data['username']) < self::UN_LENGTH)
			return statusMessage('Username must be at least '.self::UN_LENGTH.' characters long.');
		
		// Make sure the username is not already being used
		if($this->usernameExists($data['username'], $session['id']))
			return statusMessage('That username has already been taken. Please choose another one.');
		
		// Make sure the email is not already being used
		if($this->emailExists($data['email'], $session['id']))
			return statusMessage('That email is already taken by another user. Please choose another one.');
		
		// Create an array to hold the user's metadata
		$usermeta = array('first_name'=>$data['first_name'], 'last_name'=>$data['last_name'], 'avatar'=>$data['avatar']);
		
		// Update the user in the database
		$rs_query->update('users', array('username'=>$data['username'], 'email'=>$data['email']), array('id'=>$session['id']));
		
		// Update the user's metadata in the database
		foreach($usermeta as $key=>$value)
			$rs_query->update('usermeta', array('value'=>$value), array('user'=>$session['id'], '_key'=>$key));
		
		// Return a status message
		return statusMessage('Profile updated! This page will automatically refresh for all changes to take effect.', true);
	}
}