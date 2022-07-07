<?php
/**
 * Admin class used to implement the Settings object.
 * @since 1.3.7[a]
 *
 * Settings allow some extra customization for the site via the admin dashboard.
 * Settings can be modified, but not created or deleted.
 */
class Settings {
	/**
	 * Construct a list of general settings.
	 * @since 1.3.7[a]
	 *
	 * @access public
	 */
	public function generalSettings(): void {
		// Extend the Query object
		global $rs_query;
		
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateSettingsData($_POST) : '';
		
		$db_settings = $rs_query->select('settings', '*');
		
		foreach($db_settings as $db_setting)
			$setting[$db_setting['name']] = $db_setting['value'];
		?>
		<div class="heading-wrap">
			<h1>General Settings</h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Site title
					echo formRow(array('Site Title', true), array(
						'tag' => 'input',
						'class' => 'text-input required invalid init',
						'name' => 'site_title',
						'value' => $setting['site_title']
					));
					
					// Description
					echo formRow('Description', array(
						'tag' => 'input',
						'class' => 'text-input',
						'name' => 'description',
						'maxlength' => 155,
						'value' => $setting['description']
					));
					
					// Site URL
					echo formRow(array('Site URL', true), array(
						'tag' => 'input',
						'type' => 'url',
						'class' => 'text-input required invalid init',
						'name' => 'site_url',
						'value' => $setting['site_url']
					));
					
					// Admin email
					echo formRow(array('Admin Email', true), array(
						'tag' => 'input',
						'type' => 'email',
						'class' => 'text-input required invalid init',
						'name' => 'admin_email',
						'value' => $setting['admin_email']
					));
					
					// Default user role
					echo formRow('Default User Role', array(
						'tag' => 'select',
						'class' => 'select-input',
						'name' => 'default_user_role',
						'content' => $this->getUserRoles((int)$setting['default_user_role'])
					));
					
					// Home page
					echo formRow('Home Page', array(
						'tag' => 'select',
						'class' => 'select-input',
						'name' => 'home_page',
						'content' => $this->getPageList((int)$setting['home_page'])
					));
					
					// Search engine visibility
					echo formRow('Search Engine Visibility', array(
						'tag' => 'input',
						'type' => 'checkbox',
						'class' => 'checkbox-input',
						'name' => 'do_robots',
						'value' => $setting['do_robots'],
						'checked' => !$setting['do_robots'],
						'label' => array(
							'class' => 'checkbox-label',
							'content' => tag('span', array(
								'content' => 'Discourage search engines from indexing this site'
							))
						)
					));
					
					// Comments
					echo formRow('Comments', array(
						'tag' => 'input',
						'type' => 'checkbox',
						'class' => 'checkbox-input',
						'name' => 'enable_comments',
						'value' => $setting['enable_comments'],
						'checked' => $setting['enable_comments'],
						'label' => array(
							'class' => 'checkbox-label conditional-toggle',
							'content' => tag('span', array(
								'content' => 'Enable comments'
							))
						)
					), array('tag' => 'br'), array(
						'tag' => 'input',
						'type' => 'checkbox',
						'class' => 'checkbox-input',
						'name' => 'auto_approve_comments',
						'value' => $setting['auto_approve_comments'],
						'checked' => $setting['auto_approve_comments'],
						'label' => array(
							'class' => 'checkbox-label conditional-field',
							'content' => tag('span', array(
								'content' => 'Approve comments automatically'
							))
						)
					), array('tag' => 'br', 'class' => 'conditional-field'), array(
						'tag' => 'input',
						'type' => 'checkbox',
						'class' => 'checkbox-input',
						'name' => 'allow_anon_comments',
						'value' => $setting['allow_anon_comments'],
						'checked' => $setting['allow_anon_comments'],
						'label' => array(
							'class' => 'checkbox-label conditional-field',
							'content' => tag('span', array(
								'content' => 'Allow comments from anonymous (logged out) users'
							))
						)
					));
					
					// Logins
					echo formRow('Logins', array(
						'tag' => 'input',
						'type' => 'checkbox',
						'class' => 'checkbox-input',
						'name' => 'track_login_attempts',
						'value' => $setting['track_login_attempts'],
						'checked' => $setting['track_login_attempts'],
						'label' => array(
							'class' => 'checkbox-label conditional-toggle',
							'content' => tag('span', array(
								'content' => 'Keep track of login attempts'
							))
						)
					), array('tag' => 'br'), array(
						'tag' => 'input',
						'type' => 'checkbox',
						'class' => 'checkbox-input',
						'name' => 'delete_old_login_attempts',
						'value' => $setting['delete_old_login_attempts'],
						'checked' => $setting['delete_old_login_attempts'],
						'label' => array(
							'class' => 'checkbox-label conditional-field',
							'content' => tag('span', array(
								'content' => 'Delete login attempts from more than 30 days ago'
							))
						)
					));
					
					// Separator
					echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => 'Update Settings'
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Construct a list of design settings.
	 * @since 2.1.11[a]
	 *
	 * @access public
	 */
	public function designSettings(): void {
		// Extend the Query object
		global $rs_query;
		
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateSettingsData($_POST) : '';
		
		$db_settings = $rs_query->select('settings', '*');
		
		foreach($db_settings as $db_setting)
			$setting[$db_setting['name']] = $db_setting['value'];
		
		// Check whether the site logo has been set and fetch its dimensions if so
		if(!empty($setting['site_logo']))
			list($logo_width, $logo_height) = getimagesize(PATH.getMediaSrc($setting['site_logo']));
		
		// Check whether the site icon has been set and fetch its dimensions if so
		if(!empty($setting['site_icon']))
			list($icon_width, $icon_height) = getimagesize(PATH.getMediaSrc($setting['site_icon']));
		?>
		<div class="heading-wrap">
			<h1>Design Settings</h1>
			<?php
			echo $message;
			
			// Refresh the page after 2 seconds
			echo isset($_POST['submit']) ? '<meta http-equiv="refresh" content="2">' : '';
			?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<?php
				// Page ID (hidden)
				echo formTag('input', array(
					'type' => 'hidden',
					'name' => 'page',
					'value' => 'design'
				));
				?>
				<table class="form-table">
					<?php
					// Site logo
					echo formRow('Site Logo', array(
						'tag' => 'div',
						'class' => 'image-wrap' . (!empty($setting['site_logo']) ? ' visible' : ''),
						'style' => 'width: ' . ($logo_width ?? 0) . 'px;',
						'content' => getMedia($setting['site_logo'], array(
							'data-field' => 'thumb'
						)) . tag('span', array(
							'class' => 'image-remove',
							'title' => 'Remove',
							'content' => tag('i', array('class' => 'fa-solid fa-xmark'))
						))
					), array(
						'tag' => 'input',
						'type' => 'hidden',
						'name' => 'site_logo',
						'value' => (int)$setting['site_logo'],
						'data-field' => 'id'
					), array(
						'tag' => 'input',
						'type' => 'button',
						'class' => 'button-input button modal-launch',
						'value' => 'Choose Image',
						'data-type' => 'image'
					));
					
					// Site icon
					echo formRow('Site Icon', array(
						'tag' => 'div',
						'class' => 'image-wrap' . (!empty($setting['site_icon']) ? ' visible' : ''),
						'style' => 'width: ' . ($icon_width ?? 0) . 'px;',
						'content' => getMedia($setting['site_icon'], array(
							'data-field' => 'thumb'
						)) . tag('span', array(
							'class' => 'image-remove',
							'title' => 'Remove',
							'content' => tag('i', array('class' => 'fa-solid fa-xmark'))
						))
					), array(
						'tag' => 'input',
						'type' => 'hidden',
						'name' => 'site_icon',
						'value' => (int)$setting['site_icon'],
						'data-field' => 'id'
					), array(
						'tag' => 'input',
						'type' => 'button',
						'class' => 'button-input button modal-launch',
						'value' => 'Choose Image',
						'data-type' => 'image'
					));
					
					// Theme color
					echo formRow('Theme Color', array(
						'tag' => 'input',
						'type' => 'color',
						'class' => 'color-input',
						'name' => 'theme_color',
						'value' => $setting['theme_color']
					));
					
					// Separator
					echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => 'Update Settings'
					));
					?>
				</table>
			</form>
		</div>
		<?php
		include_once PATH . ADMIN . INC . '/modal-upload.php';
	}
	
	/**
	 * Validate the settings form data.
	 * @since 1.3.7[a]
	 *
	 * @access private
	 * @param array $data
	 * @return string
	 */
	private function validateSettingsData($data): string {
		// Extend the Query object
		global $rs_query;
		
		// Remove the 'submit' value from the data array
		array_pop($data);
		
		// Check whether a settings page has been specified
		if(isset($data['page'])) {
			// Remove the 'page' value from the data array
			array_shift($data);
			
			// Update the settings in the database
			foreach($data as $name => $value)
				$rs_query->update('settings', array('value' => $value), array('name' => $name));
		} else {
			// Make sure no required fields are empty
			if(empty($data['site_title']) || empty($data['site_url']) || empty($data['admin_email']))
				return statusMessage('R');
			
			// Set the value of 'do_robots'
			$data['do_robots'] = isset($data['do_robots']) ? 0 : 1;
			
			// Create an array of togglable settings
			$settings = array(
				'enable_comments',
				'auto_approve_comments',
				'allow_anon_comments',
				'track_login_attempts',
				'delete_old_login_attempts'
			);
			
			// Loop through the settings and assign them values
			foreach($settings as $setting)
				$data[$setting] = isset($data[$setting]) ? 1 : 0;
			
			// Fetch current value of 'do_robots' in the database
			$do_robots = $rs_query->selectField('settings', 'value', array('name' => 'do_robots'));
			
			// Update the settings in the database
			foreach($data as $name => $value)
				$rs_query->update('settings', array('value' => $value), array('name' => $name));
			
			// File path for the robots.txt file
			$file_path = PATH.'/robots.txt';
			
			// Fetch the robots.txt file
			$file = file($file_path, FILE_IGNORE_NEW_LINES);
			
			// Check whether 'do_robots' has changed
			if($data['do_robots'] !== (int)$do_robots) {
				// Check whether the second line is what we want to change
				if(str_starts_with($file[1], 'Disallow:')) {
					// Check whether 'do_robots' is set
					if($data['do_robots'] === 0) {
						// Block robots from crawling the site
						$file[1] = 'Disallow: /';
					} else {
						// Allow crawling to all directories except for /admin/
						$file[1] = 'Disallow: /admin/';
					}
					
					// Output changes to the file
					file_put_contents($file_path, implode(chr(10), $file));
				}
			}
		}
		
		return statusMessage('Settings updated!', true);
	}
	
	/**
	 * Construct a list of user roles.
	 * @since 1.7.0[a]
	 *
	 * @access private
	 * @param int $default
	 * @return string
	 */
	private function getUserRoles($default): string {
		// Extend the Query object
		global $rs_query;
		
		$list = '';
		
		$roles = $rs_query->select('user_roles', '*', '', 'id');
		
		foreach($roles as $role) {
			$list .= tag('option', array(
				'value' => $role['id'],
				'selected' => ($role['id'] === $default),
				'content' => $role['name']
			));
		}
		
		return $list;
	}
	
	/**
	 * Construct a list of existing pages.
	 * @since 1.3.7[a]
	 *
	 * @access private
	 * @param int $home_page
	 * @return string
	 */
	private function getPageList($home_page): string {
		// Extend the Query object
		global $rs_query;
		
		$list = '';
		
		$pages = $rs_query->select('posts', array('id', 'title'), array(
			'status' => 'published',
			'type' => 'page'
		), 'title');
		
		// Check whether the home page exists and add a blank option if not
		if(array_search($home_page, array_column($pages, 'id'), true) === false) {
			$list .= tag('option', array(
				'value' => 0,
				'selected' => 1,
				'content' => '(none)'
			));
		}
		
		foreach($pages as $page) {
			$list .= tag('option', array(
				'value' => $page['id'],
				'selected' => ($page['id'] === $home_page),
				'content' => $page['title']
			));
		}
		
		return $list;
	}
}