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
	 * @return null
	 */
	public function generalSettings() {
		// Extend the Query object
		global $rs_query;
		
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateSettingsData($_POST) : '';
		
		// Fetch all settings from the database
		$db_settings = $rs_query->select('settings', '*');
		
		// Loop through the settings
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
					echo formRow(array('Site Title', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'site_title', 'value'=>$setting['site_title']));
					echo formRow('Description', array('tag'=>'input', 'class'=>'text-input', 'name'=>'description', 'maxlength'=>155, 'value'=>$setting['description']));
					echo formRow(array('Site URL', true), array('tag'=>'input', 'type'=>'url', 'class'=>'text-input required invalid init', 'name'=>'site_url', 'value'=>$setting['site_url']));
					echo formRow(array('Admin Email', true), array('tag'=>'input', 'type'=>'email', 'class'=>'text-input required invalid init', 'name'=>'admin_email', 'value'=>$setting['admin_email']));
					echo formRow('Default User Role', array('tag'=>'select', 'class'=>'select-input', 'name'=>'default_user_role', 'content'=>$this->getUserRoles((int)$setting['default_user_role'])));
					echo formRow('Home Page', array('tag'=>'select', 'class'=>'select-input', 'name'=>'home_page', 'content'=>$this->getPageList((int)$setting['home_page'])));
					echo formRow('Search Engine Visibility', array('tag'=>'input', 'type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'do_robots', 'value'=>(int)$setting['do_robots'], '*'=>(!$setting['do_robots'] ? 'checked' : ''), 'label'=>array('class'=>'checkbox-label', 'content'=>'<span>Discourage search engines from indexing this site</span>')));
					echo formRow('Comments', array('tag'=>'input', 'type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'comment_status', 'value'=>(int)$setting['comment_status'], '*'=>($setting['comment_status'] ? 'checked' : ''), 'label'=>array('class'=>'checkbox-label', 'content'=>'<span>Enable comments</span>')), array('tag'=>'br'), array('tag'=>'input', 'type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'comment_approval', 'value'=>(int)$setting['comment_approval'], '*'=>($setting['comment_approval'] ? 'checked' : ''), 'label'=>array('class'=>'checkbox-label', 'content'=>'<span>Approve comments automatically</span>')));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Settings'));
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
	 * @return null
	 */
	public function designSettings() {
		// Extend the Query object
		global $rs_query;
		
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateSettingsData($_POST) : '';
		
		// Fetch all settings from the database
		$db_settings = $rs_query->select('settings', '*');
		
		// Loop through the settings
		foreach($db_settings as $db_setting)
			$setting[$db_setting['name']] = $db_setting['value'];
		
		// Check whether the site logo has been set
		if(!empty($setting['site_logo'])) {
			// Fetch the logo's dimensions
			list($logo_width, $logo_height) = getimagesize(PATH.getMediaSrc($setting['site_logo']));
		}
		
		// Check whether the site icon has been set
		if(!empty($setting['site_icon'])) {
			// Fetch the icon's dimensions
			list($icon_width, $icon_height) = getimagesize(PATH.getMediaSrc($setting['site_icon']));
		}
		?>
		<div class="heading-wrap">
			<h1>Design Settings</h1>
			<?php
			// Display any returned messages
			echo $message;
			
			// Refresh the page after 2 seconds
			echo isset($_POST['submit']) ? '<meta http-equiv="refresh" content="2">' : '';
			?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<?php
				// Construct a hidden 'page' form tag
				echo formTag('input', array('type'=>'hidden', 'name'=>'page', 'value'=>'design'));
				?>
				<table class="form-table">
					<?php
					echo formRow('Site Logo', array('tag'=>'div', 'class'=>'image-wrap'.(!empty($setting['site_logo']) ? ' visible' : ''), 'style'=>'width: '.($logo_width ?? 0).'px;', 'content'=>formTag('img', array('src'=>getMediaSrc($setting['site_logo']), 'data-field'=>'thumb')).formTag('span', array('class'=>'image-remove', 'title'=>'Remove', 'content'=>formTag('i', array('class'=>'fas fa-times'))))), array('tag'=>'input', 'type'=>'hidden', 'name'=>'site_logo', 'value'=>(int)$setting['site_logo'], 'data-field'=>'id'), array('tag'=>'input', 'type'=>'button', 'class'=>'button-input button modal-launch', 'value'=>'Choose Image', 'data-type'=>'image'));
					echo formRow('Site Icon', array('tag'=>'div', 'class'=>'image-wrap'.(!empty($setting['site_icon']) ? ' visible' : ''), 'style'=>'width: '.($icon_width ?? 0).'px;', 'content'=>formTag('img', array('src'=>getMediaSrc($setting['site_icon']), 'data-field'=>'thumb')).formTag('span', array('class'=>'image-remove', 'title'=>'Remove', 'content'=>formTag('i', array('class'=>'fas fa-times'))))), array('tag'=>'input', 'type'=>'hidden', 'name'=>'site_icon', 'value'=>(int)$setting['site_icon'], 'data-field'=>'id'), array('tag'=>'input', 'type'=>'button', 'class'=>'button-input button modal-launch', 'value'=>'Choose Image', 'data-type'=>'image'));
					echo formRow('Theme Color', array('tag'=>'input', 'type'=>'color', 'class'=>'color-input', 'name'=>'theme_color', 'value'=>$setting['theme_color']));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Settings'));
					?>
				</table>
			</form>
		</div>
		<?php
		// Include the upload modal
		include_once PATH.ADMIN.INC.'/modal-upload.php';
	}
	
	/**
	 * Validate the settings form data.
	 * @since 1.3.7[a]
	 *
	 * @access private
	 * @param array $data
	 * @return string
	 */
	private function validateSettingsData($data) {
		// Extend the Query object
		global $rs_query;
		
		// Remove the 'submit' value from the data array
		array_pop($data);
		
		// Check whether a settings page has been specified
		if(isset($data['page'])) {
			// Remove the 'page' value from the data array
			array_shift($data);
			
			// Update the settings in the database
			foreach($data as $name=>$value)
				$rs_query->update('settings', array('value'=>$value), array('name'=>$name));
		} else {
			// Make sure no required fields are empty
			if(empty($data['site_title']) || empty($data['site_url']) || empty($data['admin_email']))
				return statusMessage('R');
			
			// Set the value of 'do_robots'
			$data['do_robots'] = isset($data['do_robots']) ? 0 : 1;
			
			// Set the value of 'comment_status'
			$data['comment_status'] = isset($data['comment_status']) ? 1 : 0;
			
			// Set the value of 'comment_approval'
			$data['comment_approval'] = isset($data['comment_approval']) ? 1 : 0;
			
			// Update the settings in the database
			foreach($data as $name=>$value)
				$rs_query->update('settings', array('value'=>$value), array('name'=>$name));
			
			// Fetch current value of 'do_robots' in the database
			$do_robots = $rs_query->selectField('settings', 'value', array('name'=>'do_robots'));
			
			// File path for robots.txt
			$file_path = PATH.'/robots.txt';
			
			// Fetch the robots.txt file
			$file = file($file_path, FILE_IGNORE_NEW_LINES);
			
			// Check whether 'do_robots' has changed
			if($data['do_robots'] !== (int)$do_robots) {
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
		
		// Return a success message
		return statusMessage('Settings updated!', true);
	}
	
	/**
	 * Construct a list of user roles.
	 * @since 1.6.4[a]
	 *
	 * @access private
	 * @param int $default
	 * @return string
	 */
	private function getUserRoles($default) {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch all user roles from the database
		$roles = $rs_query->select('user_roles', '*', '', 'id');
		
		// Add each role to the list
		foreach($roles as $role)
			$list .= '<option value="'.$role['id'].'"'.($role['id'] === $default ? ' selected' : '').'>'.$role['name'].'</option>';
		
		// Return the list
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
	private function getPageList($home_page) {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch all pages from the database
		$pages = $rs_query->select('posts', array('id', 'title'), array('status'=>'published', 'type'=>'page'), 'title');
		
		// Add each page to the list
		foreach($pages as $page)
			$list .= '<option value="'.$page['id'].'"'.($page['id'] === $home_page ? ' selected' : '').'>'.$page['title'].'</option>';
		
		// Return the list
		return $list;
	}
}