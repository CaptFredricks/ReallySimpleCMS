<?php
/**
 * Admin class used to implement the Profile object. Inherits from the User class.
 * The user profile contains settings for individual users that can be changed at their will.
 * @since 2.0.0-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_profile
 *
 * ## VARIABLES [0] ##
 * See `User` class for a list of inherited vars
 *
 * ## METHODS [7] ##
 * See `User` class for a list of inherited methods
 * { LISTS, FORMS, & ACTIONS [2] }
 * - public editProfile(): void
 * - public resetPassword(): void
 * { VALIDATION [1] }
 * - private validateSubmission(array $data): string
 * { MISCELLANEOUS [4] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private getDisplayNames(): string
 * - private getAdminThemesList(string $current): string
 */
namespace Admin;

class Profile extends User {
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Edit your user profile.
	 * @since 2.0.0-alpha
	 *
	 * @access public
	 */
	public function editProfile(): void {
		$this->pageHeading();
		
		$meta = $this->getUserMeta($this->id);
		$avatar_filepath = PATH . getMediaSrc($meta['avatar']);
		
		if(!empty($meta['avatar']) && file_exists($avatar_filepath))
			list($width, $height) = getimagesize($avatar_filepath);
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Username
					echo formRow(array('Username', true), array(
						'tag' => 'input',
						'id' => 'username-field',
						'class' => 'text-input required invalid init',
						'name' => 'username',
						'value' => $this->username,
						'placeholder' => $this->admin_page['labels']['title_placeholder']
					));
					
					// Email
					echo formRow(array('Email', true), array(
						'tag' => 'input',
						'type' => 'email',
						'id' => 'email-field',
						'class' => 'text-input required invalid init',
						'name' => 'email',
						'value' => $this->email
					));
					
					// First name
					echo formRow('First Name', array(
						'tag' => 'input',
						'id' => 'first-name-field',
						'class' => 'text-input',
						'name' => 'first_name',
						'value' => $meta['first_name']
					));
					
					// Last name
					echo formRow('Last Name', array(
						'tag' => 'input',
						'id' => 'last-name-field',
						'class' => 'text-input',
						'name' => 'last_name',
						'value' => $meta['last_name']
					));
					
					// Display name
					echo formRow('Display Name', array(
						'tag' => 'select',
						'id' => 'display-name-field',
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
							'content' => domTag('i', array(
								'class' => 'fa-solid fa-xmark'
							))
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
						'id' => 'theme-field',
						'class' => 'select-input',
						'name' => 'theme',
						'content' => $this->getAdminThemesList($meta['theme'])
					));
					
					// Separator
					echo formRow('', array(
						'tag' => 'hr',
						'class' => 'separator'
					));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => $this->admin_page['labels']['update_button']
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
		includeFile(PATH . MODALS . '/modal-upload.php');
	}
	
	/**
	 * Reset your password.
	 * @since 2.0.7-alpha
	 *
	 * @access public
	 */
	public function resetPassword(): void {
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Current password
					echo formRow('Current Password', array(
						'tag' => 'input',
						'type' => 'password',
						'id' => 'current-pass-field',
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
						'content' => domTag('br', array(
							'class' => 'spacer'
						)) . domTag('input', array(
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
						'id' => 'confirm-pass-field',
						'class' => 'text-input required invalid init',
						'name' => 'confirm_pass'
					));
					
					// Separator
					echo formRow('', array(
						'tag' => 'hr',
						'class' => 'separator'
					));
					
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
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the form data.
	 * @since 2.0.6-alpha
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateSubmission(array $data): string {
		global $rs_query, $rs_session;
		
		switch($this->action) {
			case 'reset_password':
				if(empty($data['current_pass']) || empty($data['new_pass']) || empty($data['confirm_pass'])) {
					return exitNotice('REQ', -1);
					exit;
				}
				
				if(!$this->verifyPassword($data['current_pass'], $this->id)) {
					return exitNotice('Current password is incorrect.', -1);
					exit;
				}
				
				if($data['new_pass'] !== $data['confirm_pass']) {
					return exitNotice('New and confirm passwords do not match.', -1);
					exit;
				}
				
				if(strlen($data['new_pass']) < self::PW_LENGTH || strlen($data['confirm_pass']) < self::PW_LENGTH) {
					return exitNotice('New password must be at least ' . self::PW_LENGTH . ' characters long.', -1);
					exit;
				}
				
				if(!isset($data['pass_saved']) || $data['pass_saved'] != 1) {
					return exitNotice('Please confirm that you\'ve saved your password to a safe location.', -1);
					exit;
				}
				
				$hashed_password = password_hash($data['new_pass'], PASSWORD_BCRYPT, array(
					'cost' => 10
				));
				
				$rs_query->update(getTable('u'), array(
					'password' => $hashed_password,
					'session' => null
				), array(
					'id' => $this->id
				));
				
				// Delete the session cookie
				setcookie('session', '', 1, '/');
				
				redirect(ADMIN_URI . getQueryString(array(
					'action' => $this->action,
					'exit_status' => 'pw_success'
				)));
				break;
			default:
				if(empty($data['username']) || empty($data['email'])) {
					return exitNotice('REQ', -1);
					exit;
				}
				
				if(strlen($data['username']) < self::UN_LENGTH) {
					return exitNotice('Username must be at least ' . self::UN_LENGTH . ' characters long.', -1);
					exit;
				}
				
				if($this->usernameExists($data['username'], $rs_session['id'])) {
					return exitNotice('That username has already been taken. Please choose another one.', -1);
					exit;
				}
				
				if($this->emailExists($data['email'], $rs_session['id'])) {
					return exitNotice('That email is already taken by another user. Please choose another one.', -1);
					exit;
				}
				
				$username = $rs_query->selectField(getTable('u'), 'username', array(
					'id' => $rs_session['id']
				));
				
				$db_usermeta = $this->getUserMeta($rs_session['id']);
				
				$usermeta = array(
					'first_name' => $data['first_name'],
					'last_name' => $data['last_name'],
					'display_name' => $data['display_name'],
					'avatar' => $data['avatar'],
					'theme' => $data['theme']
				);
				
				$rs_query->update(getTable('u'), array(
					'username' => $data['username'],
					'email' => $data['email']
				), array(
					'id' => $rs_session['id']
				));
				
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
					
					$rs_query->update(getTable('um'), array(
						'value' => $value
					), array(
						'user' => $rs_session['id'],
						'key' => $key
					));
				}
				
				foreach($data as $key => $value) $this->$key = $value;
				
				redirect(ADMIN_URI . getQueryString(array(
					'exit_status' => 'edit_success'
				)));
		}
	}
	
	/*------------------------------------*\
		MISCELLANEOUS
	\*------------------------------------*/
	
	/**
	 * Construct the page heading.
	 * @since 1.3.14-beta
	 *
	 * @access public
	 */
	public function pageHeading(): void {
		switch($this->action) {
			case 'reset_password':
				$title = 'Reset Password';
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			default:
				$title = $this->admin_page['labels']['edit_item'];
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
		}
		?>
		<div class="heading-wrap">
			<?php
			// Page title
			domTagPr('h1', array(
				'content' => $title
			));
			
			// Status messages
			echo $message;
			
			// Exit notices
			if(isset($_GET['exit_status'])) {
				echo $this->exitNotice($_GET['exit_status']);
				
				if($this->action === 'reset_password') {
					// currently, the user is logged out immediately,
					// revisit in a later update
					domTagPr('meta', array(
						'http-equiv' => 'refresh',
						'content' => '2; url=\'/login.php' . getQueryString(array(
							'redirect' => urlencode($_SERVER['PHP_SELF'])
						)) . '\''
					));
				} else {
					domTagPr('meta', array(
						'http-equiv' => 'refresh',
						'content' => '2; url=\'' . ADMIN_URI . '\''
					));
				}
			}
			?>
		</div>
		<?php
	}
	
	/**
	 * Generate an exit notice.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @param string $exit_status -- The exit status.
	 * @param int $status_code (optional) -- The type of notice to display.
	 * @return string
	 */
	private function exitNotice(string $exit_status, int $status_code = 1): string {
		return exitNotice(match($exit_status) {
			'edit_success' => 'Profile updated! This page will automatically refresh for all changes to take effect.',
			'pw_success' => 'Password updated! You will be required to log back in.',
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Construct a list of possible display names.
	 * @since 1.3.8-beta
	 *
	 * @access private
	 * @return string
	 */
	private function getDisplayNames(): string {
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
		
		$list = array();
		
		foreach($display_names as $dname) {
			$list[] = domTag('option', array(
				'value' => $dname['name'],
				'selected' => $dname['name'] === $meta['display_name'],
				'content' => $dname['name'] . ' (' . $dname['extra'] . ')'
			));
		}
		
		return implode('', $list);
	}
	
	/**
	 * Construct a list of admin themes.
	 * @since 2.0.7-alpha
	 *
	 * @access private
	 * @param string $current -- The current theme.
	 * @return string
	 */
	private function getAdminThemesList(string $current): string {
		global $rs_admin_themes;
		
		$list = array();
		
		foreach($rs_admin_themes as $theme) {
			if(file_exists($theme['path'])) {
				$list[] = domTag('option', array(
					'value' => $theme['name'],
					'selected' => $theme['name'] === $current,
					'content' => $theme['label'] . ($theme['name'] === 'bedrock' ? ' (default)' : '')
				));
			}
		}
		
		return implode('', $list);
	}
}