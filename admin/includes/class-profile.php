<?php
/**
 * Admin class used to implement the Profile object. Inherits from the User class.
 * @since 2.0.0[a]
 *
 * The user profile contains settings for individual users that can be changed at their will.
 */
class Profile extends User {
	/**
	 * Edit your user profile.
	 * @since 2.0.0[a]
	 *
	 * @access public
	 */
	public function editProfile(): void {
		// Extend the Query object
		global $rs_query;
		
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		
		$meta = $this->getUserMeta($this->id);
		
		// Check whether the user has an avatar and fetch its dimensions if so
		if(!empty($meta['avatar']))
			list($width, $height) = getimagesize(PATH . getMediaSrc($meta['avatar']));
		?>
		<div class="heading-wrap">
			<h1>Edit Profile</h1>
			<?php
			echo $message;

			// Refresh the page after 2 seconds
			echo isset($_POST['submit']) ? '<meta http-equiv="refresh" content="2">' : '';
			?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Username
					echo formRow(array('Username', true), array(
						'tag' => 'input',
						'class' => 'text-input required invalid init',
						'name' => 'username',
						'value' => $this->username
					));
					
					// Email
					echo formRow(array('Email', true), array(
						'tag' => 'input',
						'type' => 'email',
						'class' => 'text-input required invalid init',
						'name' => 'email',
						'value' => $this->email
					));
					
					// First name
					echo formRow('First Name', array(
						'tag' => 'input',
						'class' => 'text-input',
						'name' => 'first_name',
						'value' => $meta['first_name']
					));
					
					// Last name
					echo formRow('Last Name', array(
						'tag' => 'input',
						'class' => 'text-input',
						'name' => 'last_name',
						'value' => $meta['last_name']
					));
					
					// Avatar
					echo formRow('Avatar', array(
						'tag' => 'div',
						'class' => 'image-wrap' . (!empty($meta['avatar']) ? ' visible' : ''),
						'style' => 'width: ' . ($width ?? 0) . 'px;',
						'content' => getMedia($meta['avatar'], array(
							'data-field' => 'thumb'
						)) . tag('span', array(
							'class' => 'image-remove',
							'title' => 'Remove',
							'content' => formTag('i', array('class' => 'fa-solid fa-xmark'))
						))
					), array(
						'tag' => 'input',
						'type' => 'hidden',
						'name' => 'avatar',
						'value' => $meta['avatar'],
						'data-field' => 'id'
					), array(
						'tag' => 'input',
						'type' => 'button',
						'class' => 'button-input button modal-launch',
						'value' => 'Choose Image',
						'data-type' => 'image'
					));
					
					// Theme
					echo formRow('Theme', array(
						'tag' => 'select',
						'class' => 'select-input',
						'name' => 'theme',
						'content' => $this->getThemesList($meta['theme'])
					));
					
					// Separator
					echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => 'Update Profile'
					));
					?>
				</table>
			</form>
			<?php echo actionLink('reset_password', array(
				'classes' => 'reset-password button',
				'caption' => 'Reset Password'
			)); ?>
		</div>
		<?php
		include_once PATH . ADMIN . INC . '/modal-upload.php';
	}
	
	/**
	 * Validate the form data.
	 * @since 2.0.6[a]
	 *
	 * @access private
	 * @param array $data
	 * @return string
	 */
	private function validateData($data): string {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Make sure no required fields are empty
		if(empty($data['username']) || empty($data['email']))
			return statusMessage('R');
		
		// Make sure the username is long enough
		if(strlen($data['username']) < self::UN_LENGTH)
			return statusMessage('Username must be at least ' . self::UN_LENGTH . ' characters long.');
		
		// Make sure the username is not already being used
		if($this->usernameExists($data['username'], $session['id']))
			return statusMessage('That username has already been taken. Please choose another one.');
		
		// Make sure the email is not already being used
		if($this->emailExists($data['email'], $session['id']))
			return statusMessage('That email is already taken by another user. Please choose another one.');
		
		$usermeta = array(
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'avatar' => $data['avatar'],
			'theme' => $data['theme']
		);
		
		$rs_query->update('users', array(
			'username' => $data['username'],
			'email' => $data['email']
		), array('id' => $session['id']));
		
		foreach($usermeta as $key => $value) {
			$rs_query->update('usermeta', array('value' => $value), array(
				'user' => $session['id'],
				'_key' => $key
			));
		}
		
		// Update the class variables
		foreach($data as $key => $value) $this->$key = $value;
		
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
	private function getThemesList($current): string {
		// Extend the Query object
		global $rs_query;
		
		$list = tag('option', array(
			'value' => 'default',
			'content' => 'Default'
		));
		
		$themes_filepath = PATH . CONT . '/admin-themes';
		
		// Check whether the directory exists and extract any existing theme filenames if so
		if(file_exists($themes_filepath))
			$themes = array_diff(scandir($themes_filepath), array('.', '..'));
		else
			$themes = array();
		
		foreach($themes as $theme) {
			$theme = pathinfo($theme);
			
			if($theme['extension'] === 'css') {
				$list .= tag('option', array(
					'value' => $theme['filename'],
					'selected' => ($theme['filename'] === $current),
					'content' => ucwords(str_replace('-', ' ', $theme['filename']))
				));
			}
		}
		
		return $list;
	}
	
	/**
	 * Reset your password.
	 * @since 2.0.7[a]
	 *
	 * @access public
	 */
	public function resetPassword(): void {
		// Check whether the form has been submitted
		if(isset($_POST['submit'])) {
			// Validate the form data and return any messages
			$message = $this->validatePasswordData($_POST, $this->id);
			
			if(str_contains($message, 'success')) {
				// Refresh the page after 2 seconds
				?>
				<meta http-equiv="refresh" content="2; url='/login.php?redirect=<?php
					echo urlencode($_SERVER['PHP_SELF']);
				?>'"><?php
			}
		}
		?>
		<div class="heading-wrap">
			<h1>Reset Password</h1>
			<?php echo $message ?? ''; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Current password
					echo formRow('Current Password', array(
						'tag' => 'input',
						'type' => 'password',
						'class' => 'text-input required invalid init',
						'name' => 'current_pass'
					));
					
					// New password
					echo formRow('New Password', array(
						'tag' => 'input',
						'id' => 'password-field',
						'class' => 'text-input required invalid init',
						'name' => 'new_pass'
					), array(
						'tag' => 'input',
						'type' => 'button',
						'id' => 'password-gen',
						'class' => 'button-input button',
						'value' => 'Generate Password'
					), array(
						'tag' => 'label',
						'class' => 'checkbox-label hidden required invalid init',
						'content' => tag('br', array('class' => 'spacer')) . tag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox-input',
							'name' => 'pass_saved',
							'value' => 1
						)) . tag('span', array('content' => 'I have copied the password to a safe place.'))
					));
					
					// New password (confirmation)
					echo formRow('New Password (confirm)', array(
						'tag' => 'input',
						'class' => 'text-input required invalid init',
						'name' => 'confirm_pass'
					));
					
					// Separator
					echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => 'Update Password'
					));
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
	private function validatePasswordData($data, $id): string {
		// Extend the Query object
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
			return statusMessage('New password must be at least ' . self::PW_LENGTH . ' characters long.');
		
		// Make sure the password saved checkbox has been checked
		if(!isset($data['pass_saved']) || $data['pass_saved'] != 1)
			return statusMessage('Please confirm that you\'ve saved your password to a safe location.');
		
		// Hash the password (encrypts the password for security purposes)
		$hashed_password = password_hash($data['new_pass'], PASSWORD_BCRYPT, array('cost' => 10));
		
		// Update the current user's password and session in the database
		$rs_query->update('users', array(
			'password' => $hashed_password,
			'session' => null
		), array('id' => $id));
		
		// Delete the session cookie
		setcookie('session', '', 1, '/');
		
		return statusMessage('Password updated! You will be required to log back in.', true);
	}
}