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
		// Extend the Query object
		global $rs_query;
		?>
		<div class="heading-wrap">
			<h1>Themes</h1>
			<a class="button" href="?action=create">Create New</a>
			<?php
			// Display any status messages
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
				// Check whether the theme has an index.php file and skip it if not
				if(!file_exists(trailingSlash(PATH.THEMES).$theme.'/index.php')) continue;
				?>
				<li>
					<?php echo ucwords(str_replace('-', ' ', $theme)); ?>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
		// Include the delete modal
        include_once PATH.ADMIN.INC.'/modal-delete.php';
	}
}