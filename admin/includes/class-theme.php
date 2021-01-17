<?php
/**
 * Admin class used to implement the Theme object.
 * @since 2.3.0[a]
 *
 * Themes are used on the front end of the CMS to allow complete customization for the user's website.
 * Themes can be created, modified, and deleted.
 */
class Theme {
	/**
	 * Construct a list of all installed themes.
	 * @since 2.3.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listThemes() {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		?>
		<div class="heading-wrap">
			<h1>Themes</h1>
			<?php
			// Check whether the user has sufficient privileges to create themes and create an action link if so
			if(userHasPrivilege($session['role'], 'can_create_themes'))
				echo actionLink('create', array('classes'=>'button', 'caption'=>'Create New'));
			
			// Display the page's info
			adminInfo();
			?>
			<hr>
			<?php
			// Check whether any status messages have been returned and display them if so
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The theme was successfully deleted.', true);
			?>
		</div>
		<ul class="data-list clear">
			<?php
			// Check whether the themes directory exists and extract any existing theme directories if so
			if(file_exists(PATH.THEMES))
				$themes = array_diff(scandir(PATH.THEMES), array('.', '..'));
			else
				$themes = array();
			
			// Loop through the themes
			foreach($themes as $theme) {
				// Construct the file path for the current theme
				$theme_path = trailingSlash(PATH.THEMES).$theme;
				
				// Check whether the theme has an index.php file and skip it if not
				if(!file_exists($theme_path.'/index.php')) continue;
				
				// Set up the action links
				$actions = array(
					userHasPrivilege($session['role'], 'can_edit_themes') ? actionLink('activate', array('caption'=>'Activate', 'name'=>$theme)) : null,
					userHasPrivilege($session['role'], 'can_delete_themes') ? actionLink('delete', array('classes'=>'modal-launch delete-item', 'data_item'=>'theme', 'caption'=>'Delete', 'name'=>$theme)) : null
				);
				
				// Filter out any empty actions
				$actions = array_filter($actions);
				?>
				<li>
					<div class="theme-preview">
						<?php if(file_exists($theme_path.'/preview.png')): ?>
							<img src="<?php echo trailingSlash(THEMES).$theme.'/preview.png'; ?>" alt="<?php echo ucwords(str_replace('-', ' ', $theme)); ?> preview">
						<?php endif; ?>
					</div>
					<h2 class="theme-name">
						<?php echo ucwords(str_replace('-', ' ', $theme)).($this->isActiveTheme($theme) ? ' &mdash; <small><em>active</em></small>' : ''); ?>
						<span class="actions">
							<?php
							// Check whether the theme is active and display the action links if not
							if(!$this->isActiveTheme($theme))
								echo implode(' &bull; ', $actions);
							?>
						</span>
					</h2>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
		// Include the delete modal
        include_once PATH.ADMIN.INC.'/modal-delete.php';
	}
	
	/**
	 * Create a theme.
	 * @since 2.3.1[a]
	 *
	 * @access public
	 * @return null
	 */
	public function createTheme() {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1>Create Theme</h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					echo formRow(array('Name', true), array('tag'=>'input', 'id'=>'slug-field', 'class'=>'text-input required invalid init', 'name'=>'name', 'value'=>($_POST['name'] ?? '')));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create Theme'));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Activate an inactive theme.
	 * @since 2.3.1[a]
	 *
	 * @access public
	 * @param string $name
	 * @return null
	 */
	public function activateTheme($name) {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the theme's name is valid
		if(!empty($name) && $this->themeExists($name) && !$this->isActiveTheme($name)) {
			// Update the theme setting in the database
			$rs_query->update('settings', array('value'=>$name), array('name'=>'theme'));
		}
		
		// Redirect to the "List Themes" page
		redirect(ADMIN_URI);
	}
	
	/**
	 * Delete a theme.
	 * @since 2.3.1[a]
	 *
	 * @access public
	 * @param string $name
	 * @return null
	 */
	public function deleteTheme($name) {
		// Check whether the theme's name is valid
		if(!empty($name) && $this->themeExists($name) && !$this->isActiveTheme($name)) {
			// Delete the theme's directory and all its contents
			$this->recursiveDelete(trailingSlash(PATH.THEMES).$name);
			
			// Redirect to the "List Themes" page with an appropriate exit status
			redirect(ADMIN_URI.'?exit_status=success');
		}
		
		// Redirect to the "List Themes" page
		redirect(ADMIN_URI);
	}
	
	/**
	 * Validate the form data.
	 * @since 2.3.1[a]
	 *
	 * @access private
	 * @param array $data
	 * @return null
	 */
	private function validateData($data) {
		// Make sure no required fields are empty
		if(empty($data['name']))
			return statusMessage('R');
		
		// Sanitize the name (strip off HTML and/or PHP tags and replace any characters not specified in the filter)
		$name = preg_replace('/[^a-z0-9\-]/', '', strip_tags(strtolower($data['name'])));
		
		// Make sure the theme doesn't already exist
		if($this->themeExists($name))
			return statusMessage('That theme already exists. Please choose a different name.');
		
		// Construct the file path for the new theme
		$theme_path = trailingSlash(PATH.THEMES).$name;
		
		// Create a directory with the chosen name
		mkdir($theme_path);
		
		// Create an index.php file
		file_put_contents($theme_path.'/index.php', array("<?php\r\n", '// Start building your new theme!'));
		
		// Redirect to the "List Themes" page
		redirect(ADMIN_URI);
	}
	
	/**
	 * Check whether a specified theme exists.
	 * @since 2.3.1[a]
	 *
	 * @access private
	 * @param string $name
	 * @return bool
	 */
	private function themeExists($name) {
		// Fetch all installed themes
		$themes = array_diff(scandir(PATH.THEMES), array('.', '..'));
		
		// Loop through the themes
		foreach($themes as $theme) {
			// Return true if the theme is found
			if($theme === $name) return true;
		}
		
		// Return false otherwise
		return false;
	}
	
	/**
	 * Check whether a specified theme is the active theme.
	 * @since 2.3.1[a]
	 *
	 * @access private
	 * @param string $name
	 * @return bool
	 */
	private function isActiveTheme($name) {
		return $name === getSetting('theme', false);
	}
	
	/**
	 * Recursively delete files and directories.
	 * @since 2.3.1[a]
	 *
	 * @access private
	 * @param string $dir
	 * @return null
	 */
	private function recursiveDelete($dir) {
		// Fetch the directory's contents
		$contents = array_diff(scandir($dir), array('.', '..'));
		
		// Loop through the directory
		foreach($contents as $content) {
			// If the content is a directory, recursively delete its contents, otherwise delete the file
			is_dir($dir.'/'.$content) ? recursiveDelete($dir.'/'.$content) : unlink($dir.'/'.$content);
		}
		
		// Delete the directory
		rmdir($dir);
	}
}