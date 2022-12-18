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
	 */
	public function listThemes(): void {
		// Extend the Query object
		global $rs_query;
		
		// Query vars
		$search = $_GET['search'] ?? null;
		?>
		<div class="heading-wrap">
			<h1>Themes</h1>
			<?php
			// Check whether the user has sufficient privileges to create themes
			if(userHasPrivilege('can_create_themes'))
				echo actionLink('create', array('classes' => 'button', 'caption' => 'Create New'));
			
			recordSearch();
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
			// Extract any existing theme directories
			if(file_exists(PATH . THEMES))
				$themes = array_diff(scandir(PATH . THEMES), array('.', '..'));
			else
				$themes = array();
			
			$active = array_search(getSetting('theme'), $themes, true);
			
			// Remove the active theme from the array
			unset($themes[$active]);
			
			// Place the active theme at the begining of the array
			array_unshift($themes, getSetting('theme'));
			
			foreach($themes as $theme) {
				if(!is_null($search) && !str_contains($theme, $search)) continue;
				
				$theme_path = trailingSlash(PATH . THEMES) . $theme;
				$is_broken = false;
				
				if(!file_exists($theme_path . '/index.php')) $is_broken = true;
				
				$actions = array(
					// Activate
					userHasPrivilege('can_edit_themes') ? actionLink('activate', array(
						'caption' => 'Activate',
						'name' => $theme
					)) : null,
					// Delete
					userHasPrivilege('can_delete_themes') ? actionLink('delete', array(
						'classes' => 'modal-launch delete-item',
						'data_item' => 'theme',
						'caption' => 'Delete',
						'name' => $theme
					)) : null
				);
				
				// Filter out any empty actions
				$actions = array_filter($actions);
				?>
				<li>
					<div class="theme-preview">
						<?php if($is_broken): ?>
							<span class="error">Warning:<br>missing index.php file</span>
						<?php elseif(file_exists($theme_path . '/preview.png')): ?>
							<img src="<?php echo slash(THEMES) . $theme .
								'/preview.png'; ?>" alt="<?php echo ucwords(str_replace('-', ' ', $theme)); ?> theme preview">
						<?php else: ?>
							<span>No theme preview</span>
						<?php endif; ?>
					</div>
					<h2 class="theme-name">
						<?php echo ucwords(str_replace('-', ' ', $theme)) . ($this->isActiveTheme($theme) ?
							' &mdash; <small><em>active</em></small>' : ''); ?>
						<span class="actions">
							<?php
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
        include_once PATH . ADMIN . INC . '/modal-delete.php';
	}
	
	/**
	 * Create a theme.
	 * @since 2.3.1[a]
	 *
	 * @access public
	 */
	public function createTheme(): void {
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
					// Name
					echo formRow(array('Name', true), array(
						'tag' => 'input',
						'id' => 'slug-field',
						'class' => 'text-input required invalid init',
						'name' => 'name',
						'value' => ($_POST['name'] ?? '')
					));
					
					// Separator
					echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => 'Create Theme'
					));
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
	 */
	public function activateTheme($name): void {
		// Extend the Query object
		global $rs_query;
		
		if(!empty($name) && $this->themeExists($name) && !$this->isActiveTheme($name))
			$rs_query->update('settings', array('value' => $name), array('name' => 'theme'));
		
		redirect(ADMIN_URI);
	}
	
	/**
	 * Delete a theme.
	 * @since 2.3.1[a]
	 *
	 * @access public
	 * @param string $name
	 */
	public function deleteTheme($name): void {
		if(!empty($name) && $this->themeExists($name) && !$this->isActiveTheme($name)) {
			$this->recursiveDelete(trailingSlash(PATH . THEMES) . $name);
			
			redirect(ADMIN_URI . '?exit_status=success');
		}
		
		redirect(ADMIN_URI);
	}
	
	/**
	 * Validate the form data.
	 * @since 2.3.1[a]
	 *
	 * @access private
	 * @param array $data
	 * @return string
	 */
	private function validateData($data): string {
		// Make sure no required fields are empty
		if(empty($data['name']))
			return statusMessage('R');
		
		$name = sanitize($data['name'], '/[^a-z0-9\-]/');
		
		if($this->themeExists($name))
			return statusMessage('That theme already exists. Please choose a different name.');
		
		$theme_path = trailingSlash(PATH . THEMES) . $name;
		
		// Create the theme directory and index.php
		mkdir($theme_path);
		file_put_contents($theme_path . '/index.php', array("<?php\r\n", '// Start building your new theme!'));
		
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
	private function themeExists($name): bool {
		// Fetch all installed themes
		$themes = array_diff(scandir(PATH . THEMES), array('.', '..'));
		
		foreach($themes as $theme)
			if($theme === $name) return true;
		
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
	private function isActiveTheme($name): bool {
		return $name === getSetting('theme');
	}
	
	/**
	 * Recursively delete files and directories.
	 * @since 2.3.1[a]
	 *
	 * @access private
	 * @param string $dir
	 */
	private function recursiveDelete($dir): void {
		// Fetch the directory's contents
		$contents = array_diff(scandir($dir), array('.', '..'));
		
		foreach($contents as $content) {
			// If the content is a directory, recursively delete its contents, otherwise delete the file
			is_dir($dir . '/' . $content) ? recursiveDelete($dir . '/' . $content) : unlink($dir . '/' . $content);
		}
		
		// Delete the directory
		rmdir($dir);
	}
}