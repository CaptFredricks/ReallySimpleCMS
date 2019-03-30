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
	 * Construct a list of all settings.
	 * @since 1.3.7[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listSettings() {
		// Extend the Query class
		global $rs_query;
		
		// Validate the form data
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		
		// Fetch all settings from the database
		$db_settings = $rs_query->select('settings', '*');
		
		// Loop through the settings
		foreach($db_settings as $db_setting)
			$setting[$db_setting['name']] = $db_setting['value'];
		?>
		<h1 id="admin-heading">General Settings</h1>
		<?php
		// Display status messages
		echo $message;
		?>
		<form action="" method="post" autocomplete="off">
			<table class="form-table">
				<?php
				// Check if 'do_robots' has been set
				$do_robots = !$setting['do_robots'] ? 'checked' : '';
				
				// Display form rows
				echo formRow(array('Site Title', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'site_title', 'value'=>$setting['site_title']));
				echo formRow('Description', array('tag'=>'input', 'class'=>'text-input', 'name'=>'description', 'maxlength'=>155, 'value'=>$setting['description']));
				echo formRow(array('Site URL', true), array('tag'=>'input', 'type'=>'url', 'class'=>'text-input required invalid init', 'name'=>'site_url', 'value'=>$setting['site_url']));
				echo formRow(array('Admin Email', true), array('tag'=>'input', 'type'=>'email', 'class'=>'text-input required invalid init', 'name'=>'admin_email', 'value'=>$setting['admin_email']));
				echo formRow('Default User Role', array('tag'=>'select', 'class'=>'select-input', 'name'=>'default_user_role', 'content'=>''));
				echo formRow('Home Page', array('tag'=>'select', 'class'=>'select-input', 'name'=>'home_page', 'content'=>$this->getPageList($setting['home_page'])));
				echo formRow('Search Engine Visibility', array('tag'=>'input', 'type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'do_robots', 'value'=>intval($setting['do_robots']), '*'=>$do_robots, 'label'=>array('class'=>'checkbox-label', 'content'=>'Discourage search engines from indexing this site')));
				echo formRow('', array('tag'=>'hr', 'class'=>'divider'));
				echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input', 'name'=>'submit', 'value'=>'Update Settings'));
				?>
			</table>
		</form>
		<?php
	}
	
	/**
	 * Validate the form data.
	 * @since 1.3.7[a]
	 *
	 * @access private
	 * @param array $data
	 * @return string
	 */
	private function validateData($data) {
		// Extend the Query class
		global $rs_query;
		
		// Remove 'submit' from data
		array_pop($data);
		
		// Return if required data is not provided
		if(empty($data['site_title']) || empty($data['site_url']) || empty($data['admin_email']))
			return statusMessage('R');
		
		// Set the value of 'do_robots'
		$data['do_robots'] = isset($data['do_robots']) ? 0 : 1;
		
		// Fetch current value of 'do_robots' in the database
		$db_data = $rs_query->selectRow('settings', 'value', array('name'=>'do_robots'));
		
		// Update the settings in the database
		foreach($data as $name=>$value)
			$rs_query->update('settings', array('value'=>$value), array('name'=>$name));
		
		// File path for robots.txt
		$file_path = PATH.'/robots.txt';
		
		// Fetch the robots.txt file
		$file = file($file_path, FILE_IGNORE_NEW_LINES);
		
		// Check whether 'do_robots' has changed
		if($data['do_robots'] !== (int)$db_data['value']) {
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
		
		// Return a success message
		return statusMessage('Settings updated!', 1);
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
		// Extend the Query class
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch the pages from the database
		$pages = $rs_query->select('posts', array('id', 'title'), array('type'=>'page'));
		
		// Add the pages to the list
		foreach($pages as $page)
			$list .= '<option value="'.$page['id'].'" '.($page['id'] === intval($home_page) ? 'selected' : '').'>'.$page['title'].'</option>';
		
		// Return the list
		return $list;
	}
}