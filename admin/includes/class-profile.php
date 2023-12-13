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
					
					// Display name
					echo formRow('Display Name', array(
						'tag' => 'select',
						'class' => 'select-input',
						'name' => 'display_name',
						'content' => $this->getDisplayNames()
					));
					
					// Avatar
					echo formRow('Avatar', array(
						'tag' => 'div',
						'class' => 'image-wrap' . (!empty($meta['avatar']) ? ' visible' : ''),
						'style' => 'width: ' . ($width ?? 0) . 'px;',
						'content' => getMedia($meta['avatar'], array(
							'data-field' => 'thumb'
						)) . domTag('span', array(
							'class' => 'image-remove',
							'title' => 'Remove',
							'content' => domTag('i', array('class' => 'fa-solid fa-xmark'))
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
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateData(array $data): string {
		global $rs_query, $session;
		
		if(empty($data['username']) || empty($data['email']))
			return exitNotice('REQ', -1);
		
		if(strlen($data['username']) < self::UN_LENGTH)
			return exitNotice('Username must be at least ' . self::UN_LENGTH . ' characters long.', -1);
		
		if($this->usernameExists($data['username'], $session['id']))
			return exitNotice('That username has already been taken. Please choose another one.', -1);
		
		if($this->emailExists($data['email'], $session['id']))
			return exitNotice('That email is already taken by another user. Please choose another one.', -1);
		
		$username = $rs_query->selectField('users', 'username', array('id' => $session['id']));
		$db_usermeta = $this->getUserMeta($session['id']);
		
		$usermeta = array(
			'first_name' => $data['first_name'],
			'last_name' => $data['last_name'],
			'display_name' => $data['display_name'],
			'avatar' => $data['avatar'],
			'theme' => $data['theme']
		);
		
		$rs_query->update('users', array(
			'username' => $data['username'],
			'email' => $data['email']
		), array('id' => $session['id']));
		
		foreach($usermeta as $key => $value) {
			if($key === 'display_name') {
				// Update the display name
				switch($data['display_name']) {
					case $username:
						$value = $data['username'];
						break;
					case $db_usermeta['first_name']:
						$value = $data['first_name'];
						break;
					case $db_usermeta['first_name'] . ' ' . $db_usermeta['last_name']:
						$value = $data['first_name'] . ' ' . $data['last_name'];
						break;
					case $db_usermeta['last_name'] . ' ' . $db_usermeta['first_name']:
						$value = $data['last_name'] . ' ' . $data['first_name'];
						break;
				}
			}
			
			$rs_query->update('usermeta', array('value' => $value), array(
				'user' => $session['id'],
				'datakey' => $key
			));
		}
		
		// Update the class variables
		foreach($data as $key => $value) $this->$key = $value;
		
		return exitNotice('Profile updated! This page will automatically refresh for all changes to take effect.');
	}
	
	/**
	 * Construct a list of possible display names.
	 * @since 1.3.8[b]
	 *
	 * @access private
	 * @return string
	 */
	private function getDisplayNames(): string {
		global $rs_query;
		
		$list = '';
		$meta = $this->getUserMeta($this->id);
		
		$display_names = array(array(
			'name' => $this->username,
			'extra' => 'username'
		));
		
		if(!empty($meta['first_name'])) {
			$display_names[] = array(
				'name' => $meta['first_name'],
				'extra' => 'first name only'
			);
			
			if(!empty($meta['last_name'])) {
				$display_names[] = array(
					'name' => $meta['first_name'] . ' ' . $meta['last_name'],
					'extra' => 'Western style'
				);
				
				$display_names[] = array(
					'name' => $meta['last_name'] . ' ' . $meta['first_name'],
					'extra' => 'Eastern style'
				);
			}
		}
		
		foreach($display_names as $dname) {
			$list .= domTag('option', array(
				'value' => $dname['name'],
				'selected' => ($dname['name'] === $meta['display_name']),
				'content' => $dname['name'] . ' (' . $dname['extra'] . ')'
			));
		}
		
		return $list;
	}
	
	/**
	 * Construct a list of admin themes.
	 * @since 2.0.7[a]
	 *
	 * @access private
	 * @param string $current -- The current theme.
	 * @return string
	 */
	private function getThemesList(string $current): string {
		global $rs_query;
		
		$list = domTag('option', array(
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
				$list .= domTag('option', array(
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
						'content' => domTag('br', array('class' => 'spacer')) . domTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox-input',
							'name' => 'pass_saved',
							'value' => 1
						)) . domTag('span', array(
							'content' => 'I have copied the password to a safe place.'
						))
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
	 * @param array $data -- The submission data.
	 * @param int $id -- The user's id.
	 * @return string
	 */
	private function validatePasswordData(array $data, int $id): string {
		global $rs_query;
		
		if(empty($data['current_pass']) || empty($data['new_pass']) || empty($data['confirm_pass']))
			return exitNotice('REQ', -1);
		
		if(!$this->verifyPassword($data['current_pass'], $id))
			return exitNotice('Current password is incorrect.', -1);
		
		if($data['new_pass'] !== $data['confirm_pass'])
			return exitNotice('New and confirm passwords do not match.', -1);
		
		if(strlen($data['new_pass']) < self::PW_LENGTH || strlen($data['confirm_pass']) < self::PW_LENGTH)
			return exitNotice('New password must be at least ' . self::PW_LENGTH . ' characters long.', -1);
		
		if(!isset($data['pass_saved']) || $data['pass_saved'] != 1)
			return exitNotice('Please confirm that you\'ve saved your password to a safe location.', -1);
		
		$hashed_password = password_hash($data['new_pass'], PASSWORD_BCRYPT, array('cost' => 10));
		
		$rs_query->update('users', array(
			'password' => $hashed_password,
			'session' => null
		), array('id' => $id));
		
		// Delete the session cookie
		setcookie('session', '', 1, '/');
		
		return exitNotice('Password updated! You will be required to log back in.');
	}
}