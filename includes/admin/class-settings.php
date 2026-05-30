<?php
/**
 * Admin class used to implement the Settings object.
 * Settings allow some extra customization for the site via the admin dashboard.
 * Settings can be modified, but not created nor deleted.
 * @since 1.3.7-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_settings
 *
 * ## VARIABLES [2] ##
 * - private string $page
 * - private array $admin_page
 *
 * ## METHODS [8] ##
 * - public __construct(string $page)
 * { LISTS, FORMS, & ACTIONS [2] }
 * - public generalSettings(): void
 * - public designSettings(): void
 * { VALIDATION [1] }
 * - private validateSubmission(array $data): string
 * { MISCELLANEOUS [4] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private getUserRoles(int $default): string
 * - private getPageList(int $home_page): string
 */
namespace Admin;

class Settings {
	/**
	 * The current settings page.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $page;
	
	/**
	 * The admin page's data.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @var array
	 */
	private $admin_page = array();
	
	/**
	 * Class constructor.
	 * @since 1.3.14-beta
	 *
	 * @access public
	 * @param string $page -- The current settings page.
	 */
	public function __construct(string $page) {
		global $rs_admin_pages;
		
		$this->page = $page;
		$this->admin_page = $rs_admin_pages[basename($_SERVER['PHP_SELF'], '.php')];
	}
	
	/*------------------------------------*\
		LISTS & FORMS
	\*------------------------------------*/
	
	/**
	 * Construct a list of general settings.
	 * @since 1.3.7-alpha
	 *
	 * @access public
	 */
	public function generalSettings(): void {
		global $rs_query;
		
		$db_settings = $rs_query->select(getTable('s'));
		
		foreach($db_settings as $db_setting)
			$setting[$db_setting['name']] = $db_setting['value'];
		
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Site title
					echo formRow(array('Site Title', true), array(
						'tag' => 'input',
						'id' => 'site-title-field',
						'class' => 'text-input required invalid init',
						'name' => 'site_title',
						'value' => $setting['site_title']
					));
					
					// Description
					echo formRow('Description', array(
						'tag' => 'input',
						'id' => 'description-field',
						'class' => 'text-input',
						'name' => 'description',
						'maxlength' => 155,
						'value' => $setting['description']
					));
					
					// Site URL
					echo formRow(array('Site URL', true), array(
						'tag' => 'input',
						'type' => 'url',
						'id' => 'site-url-field',
						'class' => 'text-input required invalid init',
						'name' => 'site_url',
						'value' => $setting['site_url']
					));
					
					// Admin email
					echo formRow(array('Admin Email', true), array(
						'tag' => 'input',
						'type' => 'email',
						'id' => 'admin-email-field',
						'class' => 'text-input required invalid init',
						'name' => 'admin_email',
						'value' => $setting['admin_email']
					));
					
					// Default user role
					echo formRow('Default User Role', array(
						'tag' => 'select',
						'id' => 'default-user-role-field',
						'class' => 'select-input',
						'name' => 'default_user_role',
						'content' => $this->getUserRoles((int)$setting['default_user_role'])
					));
					
					// Home page
					echo formRow('Home Page', array(
						'tag' => 'select',
						'id' => 'home-page-field',
						'class' => 'select-input',
						'name' => 'home_page',
						'content' => $this->getPageList((int)$setting['home_page'])
					));
					
					// Search engine visibility
					echo formRow('Search Engine Visibility', array(
						'tag' => 'input',
						'type' => 'checkbox',
						'id' => 'do-robots-field',
						'class' => 'checkbox-input',
						'name' => 'do_robots',
						'value' => $setting['do_robots'],
						'checked' => !$setting['do_robots'],
						'label' => array(
							'class' => 'checkbox-label',
							'content' => domTag('span', array(
								'content' => 'Discourage search engines from indexing this site'
							))
						)
					));
					
					// Comments
					echo formRow('Comments', array(
						'tag' => 'input',
						'type' => 'checkbox',
						'id' => 'comments-field',
						'class' => 'checkbox-input',
						'name' => 'enable_comments',
						'value' => $setting['enable_comments'],
						'checked' => $setting['enable_comments'],
						'label' => array(
							'class' => 'checkbox-label conditional-toggle',
							'content' => domTag('span', array(
								'content' => 'Enable comments'
							))
						)
					), array(
						'tag' => 'br'
					), array(
						'tag' => 'input',
						'type' => 'checkbox',
						'class' => 'checkbox-input',
						'name' => 'auto_approve_comments',
						'value' => $setting['auto_approve_comments'],
						'checked' => $setting['auto_approve_comments'],
						'label' => array(
							'class' => 'checkbox-label conditional-field',
							'content' => domTag('span', array(
								'content' => 'Approve comments automatically'
							))
						)
					), array(
						'tag' => 'br',
						'class' => 'conditional-field'
					), array(
						'tag' => 'input',
						'type' => 'checkbox',
						'class' => 'checkbox-input',
						'name' => 'allow_anon_comments',
						'value' => $setting['allow_anon_comments'],
						'checked' => $setting['allow_anon_comments'],
						'label' => array(
							'class' => 'checkbox-label conditional-field',
							'content' => domTag('span', array(
								'content' => 'Allow comments from anonymous (logged out) users'
							))
						)
					));
					
					// Comments per page
					if($setting['enable_comments']) {
						echo formRow('Comments Per Page', array(
							'tag' => 'input',
							'type' => 'number',
							'id' => 'comments-per-page-field',
							'class' => 'text-input',
							'name' => 'comments_per_page',
							'value' => $setting['comments_per_page']
						));
					}
					
					// Logins
					echo formRow('Logins', array(
						'tag' => 'input',
						'type' => 'checkbox',
						'id' => 'logins-field',
						'class' => 'checkbox-input',
						'name' => 'track_login_attempts',
						'value' => $setting['track_login_attempts'],
						'checked' => $setting['track_login_attempts'],
						'label' => array(
							'class' => 'checkbox-label conditional-toggle',
							'content' => domTag('span', array(
								'content' => 'Keep track of login attempts'
							))
						)
					), array(
						'tag' => 'br'
					), array(
						'tag' => 'input',
						'type' => 'checkbox',
						'class' => 'checkbox-input',
						'name' => 'delete_old_login_attempts',
						'value' => $setting['delete_old_login_attempts'],
						'checked' => $setting['delete_old_login_attempts'],
						'label' => array(
							'class' => 'checkbox-label conditional-field',
							'content' => domTag('span', array(
								'content' => 'Delete login attempts from more than 30 days ago'
							))
						)
					));
					
					// Login slug
					echo formRow('Login Slug', array(
						'tag' => 'input',
						'id' => 'login-slug-field',
						'class' => 'text-input',
						'name' => 'login_slug',
						'value' => $setting['login_slug']
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
		</div>
		<?php
	}
	
	/**
	 * Construct a list of design settings.
	 * @since 2.1.11-alpha
	 *
	 * @access public
	 */
	public function designSettings(): void {
		global $rs_query;
		
		$db_settings = $rs_query->select(getTable('s'));
		
		foreach($db_settings as $db_setting)
			$setting[$db_setting['name']] = $db_setting['value'];
		
		$logo_filepath = PATH . getMediaSrc($setting['site_logo']);
		$icon_filepath = PATH . getMediaSrc($setting['site_icon']);
		
		if(!empty($setting['site_logo']) && file_exists($logo_filepath))
			list($logo_width, $logo_height) = getimagesize($logo_filepath);
		
		if(!empty($setting['site_icon']) && file_exists($icon_filepath))
			list($icon_width, $icon_height) = getimagesize($icon_filepath);
		
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Site logo
					echo formRow('Site Logo', array(
						'tag' => 'div',
						'class' => 'image-wrap' . (!empty($setting['site_logo']) ? ' visible' : ''),
						'style' => 'width: ' . ($logo_width ?? 0) . 'px;',
						'content' => getMedia($setting['site_logo'], array(
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
						'id' => 'site-logo-field',
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
						'id' => 'site-icon-field',
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
						'id' => 'theme-color-field',
						'class' => 'color-input',
						'name' => 'theme_color',
						'value' => $setting['theme_color']
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
		</div>
		<?php
		includeFile(PATH . MODALS . '/modal-upload.php');
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the settings form data.
	 * @since 1.3.7-alpha
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateSubmission(array $data): string {
		global $rs_query;
		
		// Remove the `submit` value from the data array
		if(isset($data['submit']))
			unset($data['submit']);
		
		if($this->page === 'general') {
			if(empty($data['site_title']) || empty($data['site_url']) || empty($data['admin_email'])) {
				return exitNotice('REQ', -1);
				exit;
			}
			
			$data['do_robots'] = isset($data['do_robots']) ? 0 : 1;
			
			$settings = array(
				'enable_comments',
				'auto_approve_comments',
				'allow_anon_comments',
				'track_login_attempts',
				'delete_old_login_attempts'
			);
			
			foreach($settings as $setting)
				$data[$setting] = isset($data[$setting]) ? 1 : 0;
			
			$do_robots = $rs_query->selectField(getTable('s'), 'value', array(
				'name' => 'do_robots'
			));
			
			foreach($data as $name => $value) {
				$rs_query->update(getTable('s'), array(
					'value' => $value
				), array(
					'name' => $name
				));
			}
			
			$file_path = PATH . '/robots.txt';
			$file = file($file_path, FILE_IGNORE_NEW_LINES);
			
			// Check whether `do_robots` has changed
			if($data['do_robots'] !== (int)$do_robots) {
				if(str_starts_with($file[1], 'Disallow:')) {
					if($data['do_robots'] === 0) {
						// Block robots from crawling the site
						$file[1] = 'Disallow: /';
					} else {
						// Allow crawling to all directories except for /admin/
						$file[1] = 'Disallow: /admin/';
					}
					
					file_put_contents($file_path, implode(chr(10), $file));
				}
			}
		} else {
			foreach($data as $name => $value) {
				$rs_query->update(getTable('s'), array(
					'value' => $value
				), array(
					'name' => $name
				));
			}
		}
		
		redirect(ADMIN_URI . '?' . ($this->page !== 'general' ? 'page=' . $this->page . '&' : '') .
			'exit_status=edit_success');
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
		$pages = $this->admin_page['pages'];
		$labels = $this->admin_page['labels'];
		$page = array_search($this->page, $pages['subpages'], true);
		
		switch($this->page) {
			case $pages['default']:
				$title = capitalize($pages['default']) . ' ' . $labels['name'];
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			case $pages['subpages'][$page]:
				$title = capitalize($pages['subpages'][$page]) . ' ' . $labels['name'];
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			default:
				$title = 'Unregistered Settings Page';
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
				
				domTagPr('meta', array(
					'http-equiv' => 'refresh',
					'content' => '2; url=\'' . ADMIN_URI . ($this->page !== $pages['default'] ? '?page=' . $this->page : '') . '\''
				));
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
			'edit_success' => ucfirst($this->page) . ' settings updated!',
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Construct a list of user roles.
	 * @since 1.7.0-alpha
	 *
	 * @access private
	 * @param int $default -- The default user role.
	 * @return string
	 */
	private function getUserRoles(int $default): string {
		global $rs_query;
		
		$roles = $rs_query->select(getTable('ur'), '*', array(), array(
			'order_by' => 'id'
		));
		
		$list = array();
		
		foreach($roles as $role) {
			$list[] = domTag('option', array(
				'value' => $role['id'],
				'selected' => $role['id'] === $default,
				'content' => $role['name']
			));
		}
		
		return implode('', $list);
	}
	
	/**
	 * Construct a list of existing pages.
	 * @since 1.3.7-alpha
	 *
	 * @access private
	 * @param int $home_page -- The home page's id.
	 * @return string
	 */
	private function getPageList(int $home_page): string {
		global $rs_query;
		
		$pages = $rs_query->select(getTable('p'), array('id', 'title'), array(
			'status' => 'published',
			'type' => 'page'
		), array(
			'order_by' => 'title'
		));
		
		$list = array();
		
		// Check whether the home page exists and add a blank option if not
		if(array_search($home_page, array_column($pages, 'id'), true) === false) {
			$list[] = domTag('option', array(
				'value' => 0,
				'selected' => 1,
				'content' => '(none)'
			));
		}
		
		foreach($pages as $page) {
			$list[] = domTag('option', array(
				'value' => $page['id'],
				'selected' => $page['id'] === $home_page,
				'content' => $page['title']
			));
		}
		
		return implode('', $list);
	}
}