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

			// Refresh the page after 3 seconds
			echo isset($_POST['submit']) ? '<meta http-equiv="refresh" content="3">' : '';
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
					echo formRow('Avatar', array('tag'=>'div', 'class'=>'feat-image-wrap'.(!empty($meta['avatar']) ? ' visible' : ''), 'content'=>implode('', array(formTag('img', array('id'=>'media-thumb', 'src'=>getMedia($meta['avatar']), 'width'=>150)), formTag('span', array('id'=>'image-remove', 'title'=>'Remove', 'content'=>formTag('i', array('class'=>'fas fa-times'))))))), array('tag'=>'input', 'type'=>'hidden', 'id'=>'media-type', 'value'=>'image'), array('tag'=>'input', 'type'=>'hidden', 'id'=>'media-id', 'name'=>'avatar', 'value'=>$meta['avatar']), array('tag'=>'input', 'type'=>'button', 'id'=>'modal-launch', 'class'=>'button-input button', 'value'=>'Choose Image'));
					echo formRow('Theme', array('tag'=>'select', 'class'=>'select-input', 'name'=>'theme', 'content'=>$this->getThemesList($meta['theme'])));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Profile'));
					?>
				</table>
			</form>
			<a class="reset-password button" href="?action=reset_password">Reset Password</a>
		</div>
		<?php
		// Include the upload modal
		include_once PATH.ADMIN.INC.'/modal-upload.php';
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
		// Extend the Query class and the user's session data
		global $rs_query, $session;
		
		// Make sure no required fields are empty
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
		$usermeta = array('first_name'=>$data['first_name'], 'last_name'=>$data['last_name'], 'avatar'=>$data['avatar'], 'theme'=>$data['theme']);
		
		// Update the user in the database
		$rs_query->update('users', array('username'=>$data['username'], 'email'=>$data['email']), array('id'=>$session['id']));
		
		// Update the user's metadata in the database
		foreach($usermeta as $key=>$value)
			$rs_query->update('usermeta', array('value'=>$value), array('user'=>$session['id'], '_key'=>$key));
		
		// Return a status message
		return statusMessage('Profile updated! This page will automatically refresh for all changes to take effect.', true);
	}
	
	/**
	 * Construct a list of admin themes.
	 * @since 2.0.7[a]
	 *
	 * @access private
	 * @param string $current
	 * @return string
	 */
	private function getThemesList($current) {
		// Extend the Query class
		global $rs_query;
		
		// Create a list with just the default theme
		$list = '<option value="default">Default</option>';
		
		// File path for the admin themes directory
		$file_path = PATH.CONT.'/admin-themes';
		
		// Check whether the directory exists and extract any existing theme filenames if so
		if(file_exists($file_path))
			$themes = array_diff(scandir($file_path), array('.', '..'));
		else
			$themes = array();
		
		// Loop through the themes
		foreach($themes as $theme) {
			// Create an array to hold the parts of the theme's filename
			$theme = pathinfo($theme);
			
			// Check whether the file's extension is 'css'
			if($theme['extension'] === 'css') {
				// Add each theme to the list
				$list .= '<option value="'.$theme['filename'].'"'.($theme['filename'] === $current ? ' selected' : '').'>'.ucwords(str_replace('-', ' ', $theme['filename'])).'</option>';
			}
		}
		
		// Return the list
		return $list;
	}
	
	/**
	 * Construct the 'Reset Password' form.
	 * @since 2.0.7[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function resetPassword($id) {
		// Check whether the form has been submitted
		if(isset($_POST['submit'])) {
			// Validate the form data and return any messages
			$message = $this->validatePasswordData($_POST, $id);
			
			// Check whether the message contains 'success'
			if(strpos($message, 'success') !== false) {
				?>
				<meta http-equiv="refresh" content="3; url='/login.php?redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>'">
				<?php
			}
		} else {
			// Set the message as an empty string
			$message = '';
		}
		?>
		<div class="heading-wrap">
			<h1>Reset Password</h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					echo formRow('Current Password', array('tag'=>'input', 'type'=>'password', 'class'=>'text-input required invalid init', 'name'=>'current_pass'));
					echo formRow('New Password', array('tag'=>'input', 'id'=>'pw-input', 'class'=>'text-input required invalid init', 'name'=>'new_pass'), array('tag'=>'input', 'type'=>'button', 'id'=>'pw-btn', 'class'=>'button-input button', 'value'=>'Generate Password'), array('tag'=>'br'), array('tag'=>'input', 'type'=>'checkbox', 'id'=>'pw-chk', 'class'=>'checkbox-input', 'name'=>'pass_saved', 'value'=>'checked', 'label'=>array('id'=>'chk-label', 'class'=>'checkbox-label required invalid init', 'content'=>'<span>I have copied the password to a safe place.</span>')));
					echo formRow('New Password (confirm)', array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'confirm_pass'));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Password'));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Validate the password form data.
	 * @since 2.0.7[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id
	 * @return string
	 */
	private function validatePasswordData($data, $id) {
		// Extend the Query class
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['current_pass']) || empty($data['new_pass']) || empty($data['confirm_pass']))
			return statusMessage('R');
		
		// Make sure the current password is correctly entered
		if(!$this->verifyPassword($data['current_pass'], $id))
			return statusMessage('Current password is incorrect.');
		
		// Make sure the new and confirm password fields match
		if($data['new_pass'] !== $data['confirm_pass'])
			return statusMessage('New and confirm passwords do not match.');
		
		// Make sure the new and confirm passwords are long enough
		if(strlen($data['new_pass']) < self::PW_LENGTH || strlen($data['confirm_pass']) < self::PW_LENGTH)
			return statusMessage('New password must be at least '.self::PW_LENGTH.' characters long.');
		
		// Make sure the password saved checkbox has been checked
		if(!isset($data['pass_saved']) || $data['pass_saved'] !== 'checked')
			return statusMessage('Please confirm that you\'ve saved your password to a safe location.');
		
		// Hash the password (encrypts the password for security purposes)
		$hashed_password = password_hash($data['new_pass'], PASSWORD_BCRYPT, array('cost'=>10));
		
		// Update the current user's password and session in the database
		$rs_query->update('users', array('password'=>$hashed_password, 'session'=>null), array('id'=>$id));
		
		// Delete the session cookie
		setcookie('session', '', 1, '/');
		
		// Return a status message
		return statusMessage('Password updated! You will be required to log back in.', true);
	}
}