<?php
/**
 * Admin class used to implement the Theme object.
 * Themes are used on the front end to allow complete customization for the user's website.
 * Themes can be created, modified, and deleted.
 * @since 2.3.0-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_theme
 *
 * ## VARIABLES [6] ##
 * - private string $name
 * - private array $theme_data
 * - private array $admin_page
 * - private string $action
 * - private array $paged
 * - private int $count
 *
 * ## METHODS [12] ##
 * - public __construct(string $name, string $action)
 * { LISTS, FORMS, & ACTIONS [4] }
 * - public listThemes(): void
 * - public createTheme(): void
 * - public activateTheme(): void
 * - public deleteTheme(): void
 * { VALIDATION [1] }
 * - private validateSubmission(array $data): string
 * { MISCELLANEOUS [6] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private recursiveDelete(string $dir): void
 * - private getResults(?string $search, bool $all): array
 * - private getEntryCount(?string $search): int
 * - private getActionLinks(array $theme): string
 */
namespace Admin;

class Theme {
	/**
	 * The theme's name.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $name;
	
	/**
	 * The currently queried theme's data.
	 * @since 1.3.15-beta
	 *
	 * @access private
	 * @var array
	 */
	private $theme_data = array();
	
	/**
	 * The admin page's data.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @var array
	 */
	private $admin_page = array();
	
	/**
	 * The current action.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $action;
	
	/**
	 * The pagination.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var array
	 */
	private $paged = array();
	
	/**
	 * The number of available themes.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var int
	 */
	private $count;
	
	/**
	 * Class constructor.
	 * @since 1.3.14-beta
	 *
	 * @access public
	 * @param string $name -- The theme's name.
	 * @param string $action -- The current action.
	 */
	public function __construct(string $name, string $action) {
		global $rs_admin_pages, $rs_themes;
		
		$this->name = $name;
		$this->action = $action;
		$this->theme_data = $rs_themes[$name] ?? array();
		$this->admin_page = $rs_admin_pages[basename($_SERVER['PHP_SELF'], '.php')];
	}
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct a list of all installed themes.
	 * @since 2.3.0-alpha
	 *
	 * @access public
	 */
	public function listThemes(): void {
		$per_page = 6;
		
		// Query vars
		$search = $_GET['search'] ?? null;
		$this->paged = paginate((int)($_GET['paged'] ?? 1), $per_page);
		
		$this->pageHeading();
		?>
		<ul class="data-list clear">
			<?php
			$themes = $this->getResults($search);
			
			foreach($themes as $theme) {
				$theme_path = slash(PATH . THEMES) . $theme['name'];
				$is_broken = isBrokenTheme($theme['name'], $theme_path);
				?>
				<li>
					<div class="theme-preview">
						<?php
						if($is_broken) {
							domTagPr('span', array(
								'class' => 'error',
								'content' => 'Warning:' . domTag('br') . 'missing registration file' . domTag('br') .
									'or index template'
							));
						} elseif(file_exists($theme_path . '/preview.png')) {
							domTagPr('img', array(
								'src' => slash(THEMES) . $theme['name'] . '/preview.png',
								'alt' => $theme['label'] . ' theme preview'
							));
						} else {
							domTagPr('span', array(
								'content' => 'No theme preview'
							));
						}
						?>
					</div>
					<div class="theme-info">
						<h2 class="theme-name">
							<?php
							echo $theme['label'] . (isActiveTheme($theme['name']) ?
								' &mdash; ' . domTag('small', array(
									'content' => domTag('em', array(
										'content' => 'active'
									))
								)) : ''
							);
							
							domTagPr('span', array(
								'class' => 'actions',
								'content' => (!isActiveTheme($theme['name']) ? $this->getActionLinks($theme) : '')
							));
							?>
						</h2>
						<div class="theme-meta">
							<?php
							if(!$is_broken) {
								domTagPr('span', array(
									'content' => domTag('em', array(
										'content' => 'Author: '
									)) . (!empty($theme['author']['url']) ? domTag('a', array(
										'href' => $theme['author']['url'],
										'target' => '_blank',
										'rel' => 'noreferrer noopener',
										'content' => $theme['author']['name']
									)) : $theme['author']['name']) . ' &bull; ' . domTag('em', array(
										'content' => 'Version: '
									)) . $theme['version']
								));
							} else {
								domTagPr('span', array(
									'class' => 'error',
									'content' => 'Theme is broken!'
								));
							}
							?>
						</div>
					</div>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
		// Set up page navigation
		echo pagerNav($this->paged['current'], $this->paged['count']);
		
		includeFile(PATH . MODALS . '/modal-delete.php');
	}
	
	/**
	 * Create a new theme.
	 * @since 2.3.1-alpha
	 *
	 * @access public
	 */
	public function createTheme(): void {
		$this->pageHeading();
		?>
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
						'value' => ($_POST['name'] ?? ''),
						'autocomplete' => 'off',
						'placeholder' => $this->admin_page['labels']['title_placeholder']
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
						'value' => $this->admin_page['labels']['create_button']
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Activate a registered theme.
	 * @since 2.3.1-alpha
	 *
	 * @access public
	 */
	public function activateTheme(): void {
		global $rs_query;
		
		$theme_path = slash(PATH . THEMES) . $this->name;
		
		if(!empty($this->name) && themeExists($this->name) && !isBrokenTheme($theme_path) &&
			!isActiveTheme($this->name)
		) {
			$rs_query->update(getTable('s'), array(
				'value' => $this->name
			), array(
				'name' => 'active_theme'
			));
			
			redirect(ADMIN_URI . getQueryString(array(
				'exit_status' => 'activate_success'
			)));
		}
		
		redirect(ADMIN_URI . getQueryString(array(
			'exit_status' => 'activate_failure'
		)));
	}
	
	/**
	 * Delete an existing theme.
	 * @since 2.3.1-alpha
	 *
	 * @access public
	 */
	public function deleteTheme(): void {
		if(!empty($this->name) && themeExists($this->name) && !isActiveTheme($this->name)) {
			$this->recursiveDelete(slash(PATH . THEMES) . $this->name);
			
			redirect(ADMIN_URI . getQueryString(array(
				'exit_status' => 'del_success'
			)));
		}
		
		redirect(ADMIN_URI . getQueryString(array(
			'exit_status' => 'del_failure'
		)));
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the form data.
	 * @since 2.3.1-alpha
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateSubmission(array $data): string {
		global $rs_session;
		
		if(empty($data['name'])) {
			return exitNotice('REQ', -1);
			exit;
		}
		
		$name = sanitize($data['name'], '/[^a-z0-9\-]/');
		
		if(themeExists($name)) {
			return exitNotice('That theme already exists. Please choose a different name.', -1);
			exit;
		}
		
		// Create the theme directory
		$theme_path = slash(PATH . THEMES) . $name;
		mkdir($theme_path);
		
		// Create the registry file and `index.php`
		file_put_contents(slash($theme_path) . $name . '.php', array(
			"<?php\r\n",
			"/**\r\n",
			"* Registry for the " . capitalize($name) . " theme.\r\n",
			"* @since 1.0.0\r\n",
			"*\r\n",
			"* @package ReallySimpleCMS\r\n",
			"* @subpackage " . capitalize($name) . "\r\n",
			"*/\r\n",
			"\r\n",
			"\$theme = '" . $name . "';\r\n",
			"\r\n",
			"if(function_exists('registerTheme')) {\r\n",
			"	registerTheme(\$theme, array(\r\n",
			"		'author' => array(\r\n",
			"			'name' => '" . $rs_session['username'] . "',\r\n",
			"			'url' => ''\r\n",
			"		),\r\n",
			"		'version' => '1.0.0'\r\n",
			"	));\r\n",
			"}"
		));
		
		file_put_contents($theme_path . '/index.php', array(
			"<?php\r\n",
			"// Start building your new theme!"
		));
		
		redirect(ADMIN_URI . getQueryString(array(
			'exit_status' => 'create_success'
		)));
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
		$labels = $this->admin_page['labels'];
		
		switch($this->action) {
			case 'create':
				$title = $labels['create_item'];
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			default:
				$title = $labels['name'];
				$search = $_GET['search'] ?? null;
		}
		?>
		<div class="heading-wrap clear">
			<?php
			// Page title
			domTagPr('h1', array(
				'content' => $title
			));
			
			if(!empty($this->action)) {
				// Status messages
				echo $message;
				
				// Exit notices
				if(isset($_GET['exit_status']))
					echo $this->exitNotice($_GET['exit_status']);
			} else {
				// Create button
				if(userHasPrivilege('can_create_themes')) {
					echo actionLink($this->admin_page['actions']['create'], array(
						'classes' => 'button',
						'caption' => $labels['create_button']
					));
				}
				
				// Search
				recordSearch();
				
				//Info
				adminInfo();
				
				domTagPr('hr');
				
				// Exit notices
				if(isset($_GET['exit_status'])) {
					$exit = $_GET['exit_status'];
					
					if($exit === 'activate_failure' || $exit === 'del_failure')
						$status_code = -1;
					
					if(isset($status_code))
						echo $this->exitNotice($exit, $status_code);
					else
						echo $this->exitNotice($exit);
				}
				
				// Record count
				$count = $this->getEntryCount($search);
				
				domTagPr('div', array(
					'class' => 'entry-count',
					'content' => $count . ' ' . ($count === 1 ? 'entry' : 'entries')
				));
				
				$this->paged['count'] = ceil($count / $this->paged['per_page']);
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
			'create_success' => 'The theme was successfully created.',
			'activate_success' => 'The theme was successfully activated.',
			'activate_failure' => 'The theme could not be activated.',
			'del_success' => 'The theme was successfully deleted.',
			'del_failure' => 'The theme could not be deleted.',
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Recursively delete files and directories.
	 * @since 2.3.1-alpha
	 *
	 * @access private
	 * @param string $dir -- The directory to delete.
	 */
	private function recursiveDelete(string $dir): void {
		$contents = array_diff(scandir($dir), array('.', '..'));
		
		foreach($contents as $content) {
			// If the content is a directory, recursively delete its contents, otherwise delete the file
			is_dir($dir . '/' . $content) ? recursiveDelete($dir . '/' . $content) : unlink($dir . '/' . $content);
		}
		
		rmdir($dir);
	}
	
	/**
 	 * Fetch all themes based on a specific status.
 	 * @since 1.3.15-beta
 	 *
 	 * @access private
 	 * @param null|string $search -- The search query.
	 * @param bool $all (optional) -- Whether to return all or set a limit (for pagination).
 	 * @return array
 	 */
	private function getResults(?string $search, bool $all = false): array {
		global $rs_themes;
		
		// Extract any existing theme directories
		$themes_dir = array_diff(scandir(PATH . THEMES), array('.', '..', 'backups'));
		$themes = array();
		
		foreach($themes_dir as $installed) {
			if(array_key_exists($installed, $rs_themes)) {
				// Working theme
				$themes[$installed] = $rs_themes[$installed];
			} else {
				// Broken/unregistered theme
				$themes[$installed] = array(
					'label' => capitalize($installed),
					'name' => $installed,
					'is_broken' => true
				);
			}
		}
		
		foreach($themes as $theme) {
			if(!is_null($search) && !str_contains($theme['name'], $search)) {
				// Search results
				unset($themes[$theme['name']]);
			} elseif(isActiveTheme($theme['name'])) {
				// Remove the active theme from the array
				$active = $theme;
				unset($themes[$active['name']]);
			}
		}
		
		// Place the active theme at the begining of the array
		if(isset($active))
			array_unshift($themes, $active);
		
		$limit = $all === false ? $this->paged['per_page'] : count($themes);
		
		return array_slice($themes, $this->paged['start'], $limit);
	}
	
	/**
	 * Fetch the theme count.
	 * @since 1.3.15-beta
	 *
	 * @access private
	 * @param null|string $search -- The search query.
	 * @return int
	 */
	private function getEntryCount(?string $search): int {
		return count($this->getResults($search, true));
	}
	
	/**
	 * Fetch all associated action links.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @param array $theme -- The theme's data.
	 * @return string
	 */
	private function getActionLinks(array $theme): string {
		$theme_path = slash(PATH . THEMES) . $theme['name'];
		$is_broken = isBrokenTheme($theme['name'], $theme_path);
		$actions = $this->admin_page['actions'];
		$action_list = array();
		
		foreach($actions as $key => $value) {
			if($value === 'create') continue; // backward compat
			if($value === 'install') continue;
			if($value === 'uninstall') continue;
			
			$privileged = match($value) {
				'activate' => userHasPrivilege('can_edit_themes'),
				'delete' => userHasPrivilege('can_delete_themes'), // change to uninstall
				default => null
			};
			
			$action_list[] = array(
				'privileged' => $privileged,
				'link' => $value,
				'caption' => ucfirst($value)
			);
		}
		
		list($delete, $activate) = $action_list;
		
		$action_links = array(
			// Activate
			$activate['privileged'] && !$is_broken ? actionLink($activate['link'], array(
				'caption' => $activate['caption'],
				'name' => $theme['name']
			)) : null,
			// Delete
			$delete['privileged'] ? actionLink($delete['link'], array(
				'classes' => 'modal-launch delete-item',
				'data_item' => 'theme',
				'caption' => $delete['caption'],
				'name' => $theme['name']
			)) : null
		);
		
		// Filter out any empty actions
		$action_links = array_filter($action_links);
		
		return implode(' &bull; ', $action_links);
	}
}