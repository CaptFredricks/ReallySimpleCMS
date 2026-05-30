<?php
/**
 * Core class used to implement the Install object.
 * This class is responsible for setting up the database and core functions.
 * @since 1.3.16-beta
 *
 * @package ReallySimpleCMS
 * @subpackage Engine
 *
 * ## OBJECT VAR ##
 * - $rs_install
 *
 * ## CONSTANTS [2] ##
 * - private int UN_LENGTH
 * - private int PW_LENGTH
 *
 * ## VARIABLES [] ##
 *
 * ## METHODS [12] ##
 * { INSTALLATION [2] }
 * - public installForm(?string $error): void
 * - public runInstall(array $data): array
 * { TABLE POPULATORS [10] }
 * - private populateTables(array $user_data, array $settings_data): void
 * - private populateTable(string $table): void
 * - private repopulateTable(string $table): void
 * - private populatePosts(int $author): array
 * - private populateTaxonomies(): void
 * - private populateTerms(int $post): void
 * - private populateUsers(array $args): int
 * - private populateSettings(array $args): void
 * - private populateUserRoles(): void
 * - private populateUserPrivileges(): void
 */
namespace Engine;

class Install {
	/**
	 * Set the minimum username length.
	 * @since 1.2.6-beta
	 *
	 * @access private
	 * @var int
	 */
	private const UN_LENGTH = 4;
	
	/**
	 * Set the minimum password length.
	 * @since 1.2.6-beta
	 *
	 * @access private
	 * @var int
	 */
	private const PW_LENGTH = 8;
	
	/*------------------------------------*\
		INSTALLATION
	\*------------------------------------*/
	
	/**
	 * Display the installation form.
	 * @since 1.3.0-alpha
	 *
	 * @access public
	 * @param null|string $error (optional) -- The error to display.
	 */
	public function installForm(?string $error = null): void {
		domTagPr('p', array(
			'content' => 'You\'re almost ready to begin using the ' . RS_ENGINE . '. Fill in the form below to proceed with the installation. All of the settings below can be changed at a later date. They\'re required in order to set up the CMS, though.'
		));
		
		// Site title
		$site_title = isset($_POST['site_title']) ? trim(strip_tags($_POST['site_title'])) : '';
		
		// Username
		$username = isset($_POST['username']) ? trim(strip_tags($_POST['username'])) : '';
		
		// Admin email
		$admin_email = isset($_POST['admin_email']) ? trim(strip_tags($_POST['admin_email'])) : '';
		
		// Search engine visibility
		$do_robots = isset($_POST['do_robots']) ? (int)$_POST['do_robots'] : 1;
		
		if(!is_null($error)) {
			domTagPr('p', array(
				'class' => 'status-message failure',
				'content' => $error
			));
		}
		?>
		<form class="data-form" action="?step=2" method="post">
			<table class="form-table">
				<tr>
					<th><label for="site-title">Site Title</label></th>
					<td><input type="text" id="site-title" name="site_title" value="<?php echo $site_title; ?>" autofocus></td>
				</tr>
				<tr>
					<th><label for="username">Username</label></th>
					<td><input type="text" id="username" name="username" value="<?php echo $username; ?>" autocomplete="on"></td>
				</tr>
				<tr>
					<th><label for="password">Password</label></th>
					<td><input type="text" id="password" name="password" value="<?php echo generatePassword(); ?>" autocomplete="off"></td>
				</tr>
				<tr>
					<th><label for="admin-email">Email</label></th>
					<td><input type="email" id="admin-email" name="admin_email" value="<?php echo $admin_email; ?>"></td>
				</tr>
				<tr>
					<th><label for="do-robots">Search Engine Visibility</label></th>
					<td><label class="checkbox-label"><input type="checkbox" id="do-robots" name="do_robots" value="0"> <span>Discourage search engines from indexing this site</span></label></td>
				</tr>
			</table>
			<?php
			domTagPr('input', array(
				'type' => 'hidden',
				'id' => 'submit-ajax',
				'name' => 'submit_ajax',
				'value' => 0
			));
			
			domTagPr('div', array(
				'class' => 'button-wrap',
				'content' => domTag('input', array(
					'type' => 'submit',
					'class' => 'button',
					'name' => 'submit',
					'value' => 'Install'
				))
			));
			?>
		</form>
		<?php
	}
	
	/**
	 * Run the installation.
	 * @since 1.2.6-beta
	 *
	 * @access public
	 * @param array $data -- The submitted data.
	 * @return array
	 */
	public function runInstall(array $data): array {
		global $rs_query;
		
		// Site title
		$data['site_title'] = !empty($data['site_title']) ? trim(strip_tags($data['site_title'])) : 'My Website';
		
		// Username
		$data['username'] = isset($data['username']) ? trim(strip_tags($data['username'])) : '';
		
		// Password
		$data['password'] = isset($data['password']) ? strip_tags($data['password']) : '';
		
		// Admin email
		$data['admin_email'] = isset($data['admin_email']) ? trim(strip_tags($data['admin_email'])) : '';
		
		// Search engine visibility (visible by default)
		$data['do_robots'] = isset($data['do_robots']) ? (int)$data['do_robots'] : 1;
		
		// Validate input data
		if(empty($data['username']))
			return array(true, 'You must provide a username.');
		elseif(strlen($data['username']) < self::UN_LENGTH)
			return array(true, 'Username must be at least ' . self::UN_LENGTH . ' characters long.');
		elseif(empty($data['password']))
			return array(true, 'You must provide a password.');
		elseif(strlen($data['password']) < self::PW_LENGTH)
			return array(true, 'Password must be at least ' . self::PW_LENGTH . ' characters long.');
		elseif(empty($data['admin_email']))
			return array(true, 'You must provide an email.');
		
		requireFile(RS_SCHEMA);
		
		$schema = dbSchema();
		
		// Create the tables
		foreach($schema as $key => $value) $rs_query->createTable($key, $value);
		
		$user_data = array(
			'username' => $data['username'],
			'password' => $data['password'],
			'email' => $data['admin_email']
		);
		
		$site_url = (isSecureConnection() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
		
		$settings_data = array(
			'site_title' => $data['site_title'],
			'site_url' => $site_url,
			'admin_email' => $data['admin_email'],
			'do_robots' => $data['do_robots']
		);
		
		$this->populateTables($user_data, $settings_data);
		
		if(is_writable(PATH)) {
			$file_path = PATH . '/robots.txt';
			$handle = fopen($file_path, 'w');
			
			if($handle !== false) {
				fwrite($handle, 'User-agent: *' . chr(10));
				
				if((int)$data['do_robots'] === 0)
					fwrite($handle, 'Disallow: /');
				else
					fwrite($handle, 'Disallow: /admin/');
				
				fclose($handle);
			}
			
			// Set file permissions
			chmod($file_path, 0666);
		}
		
		return array(false, domTag('p', array(
			'content' => 'The database has successfully been installed! You are now ready to start using your website.'
		)) . domTag('div', array(
			'class' => 'button-wrap centered',
			'content' => domTag('a', array(
				'class' => 'button',
				'href' => '/login.php',
				'content' => 'Log In'
			))
		)));
	}
	
	/*------------------------------------*\
		TABLE POPULATORS
	\*------------------------------------*/
	
	/**
	 * Populate the database tables.
	 * @since 1.7.0-alpha
	 *
	 * @access private
	 * @param array $user_data -- The user data.
	 * @param array $settings_data -- The settings data.
	 */
	private function populateTables(array $user_data, array $settings_data): void {
		global $rs_query;
		
		$this->populateUserRoles();
		$this->populateUserPrivileges();
		
		$user = $this->populateUsers($user_data);
		$post = $this->populatePosts($user);
		
		$settings_data['home_page'] = $post['home_page'];
		$this->populateSettings($settings_data);
		
		$this->populateTaxonomies();
		$this->populateTerms($post['blog_post']);
		
		// Set post indexing based on the global setting
		$posts = $rs_query->select(getTable('p'), 'id');
		
		foreach($posts as $post) {
			$meta = $rs_query->update(getTable('pm'), array(
				'value' => getSetting('do_robots')
			), array(
				'post' => $post['id'],
				'datakey' => 'index_post'
			));
		}
	}
	
	/**
	 * Populate a database table.
	 * @since 1.0.8-beta
	 *
	 * @access private
	 * @param string $table -- The table.
	 */
	private function populateTable(string $table): void {
		global $rs_query;
		
		$schema = dbSchema();
		
		switch($table) {
			case 'postmeta':
			case 'posts':
				$names = array('postmeta', 'posts');
				
				foreach($names as $name) {
					if($rs_query->tableExists($name)) {
						$rs_query->dropTable($name);
						$rs_query->doQuery($schema[$name]);
					}
				}
				
				$admin_user_role = getUserRoleId('Administrator');
				
				$admin = $rs_query->selectField(getTable('u'), 'id', array(
					'role' => $admin_user_role
				), array(
					'order_by' => 'id',
					'order' => 'ASC',
					'limit' => 1
				));
				
				$this->populatePosts($admin);
				break;
			case 'settings':
				$this->populateSettings();
				break;
			case 'taxonomies':
				$this->populateTaxonomies();
				break;
			case 'terms':
			case 'term_relationships':
				$names = array('terms', 'term_relationships');
				
				foreach($names as $name) {
					if($rs_query->tableExists($name)) {
						$rs_query->dropTable($name);
						$rs_query->doQuery($schema[$name]);
					}
				}
				
				$post = $rs_query->selectField(getTable('p'), 'id', array(
					'status' => 'published',
					'type' => 'post'
				), array(
					'order_by' => 'id',
					'order' => 'ASC',
					'limit' => 1
				));
				
				$this->populateTerms($post);
				break;
			case 'usermeta':
			case 'users':
				$names = array('usermeta', 'users');
				
				foreach($names as $name) {
					if($rs_query->tableExists($name)) {
						$rs_query->dropTable($name);
						$rs_query->doQuery($schema[$name]);
					}
				}
				
				$this->populateUsers();
				break;
			case 'user_privileges': // Also populates `user_relationships`
				$this->populateUserPrivileges();
				break;
			case 'user_roles':
				$this->populateUserRoles();
				break;
		}
	}
	
	/**
	 * Populate the `posts` database table.
	 * @since 1.3.7-alpha
	 * @deprecated from 1.7.0-alpha to 1.0.8-beta
	 *
	 * @access private
	 * @param int $author -- The author's id.
	 * @return array
	 */
	private function populatePosts(int $author): array {
		global $rs_query;
		
		// Create a sample page
		$post['home_page'] = $rs_query->insert(getTable('p'), array(
			'title' => 'Sample Page',
			'author' => $author,
			'created' => 'NOW()',
			'content' => '<p>This is just a sample page to get you started.</p>',
			'status' => 'published',
			'slug' => 'sample-page',
			'type' => 'page'
		));
		
		// Create a sample blog post
		$post['blog_post'] = $rs_query->insert(getTable('p'), array(
			'title' => 'Sample Blog Post',
			'author' => $author,
			'created' => 'NOW()',
			'content' => '<p>This is your first blog post. Feel free to remove this text and replace it with your own.</p>',
			'status' => 'published',
			'slug' => 'sample-post'
		));
		
		$postmeta = array(
			'home_page' => array(
				'title' => 'Sample Page',
				'description' => 'Just a simple meta description for your sample page.',
				'index_post' => 0,
				'feat_image' => 0,
				'template' => 'default'
			),
			'blog_post' => array(
				'title' => 'Sample Blog Post',
				'description' => 'Just a simple meta description for your first blog post.',
				'index_post' => 0,
				'feat_image' => 0,
				'comment_status' => 1,
				'comment_count' => 0
			)
		);
		
		foreach($postmeta as $metadata) {
			foreach($metadata as $key => $value) {
				$rs_query->insert(getTable('pm'), array(
					'post' => $post[key($postmeta)],
					'key' => $key,
					'value' => $value
				));
			}
			
			next($postmeta);
		}
		
		return $post;
	}
	
	/**
	 * Populate the `taxonomies` database table.
	 * @since 1.5.0-alpha
	 * @deprecated from 1.7.0-alpha to 1.0.8-beta
	 *
	 * @access private
	 */
	private function populateTaxonomies(): void {
		global $rs_query;
		
		$taxonomies = array('category', 'nav_menu');
		
		foreach($taxonomies as $taxonomy) {
			$rs_query->insert(getTable('ta'), array(
				'name' => $taxonomy
			));
		}
	}
	
	/**
	 * Populate the `terms` database table.
	 * @since 1.5.0-alpha
	 * @deprecated from 1.7.0-alpha to 1.0.8-beta
	 *
	 * @access private
	 * @param int $post -- The post's id.
	 */
	private function populateTerms(int $post): void {
		global $rs_query;
		
		$term = $rs_query->insert(getTable('t'), array(
			'name' => 'Uncategorized',
			'slug' => 'uncategorized',
			'taxonomy' => getTaxonomyId('category'),
			'count' => 1
		));
		
		$rs_query->insert(getTable('tr'), array(
			'term' => $term,
			'post' => $post
		));
	}
	
	/**
	 * Populate the `users` database table.
	 * @since 1.3.1-alpha
	 * @deprecated from 1.7.0-alpha to 1.0.8-beta
	 *
	 * @access private
	 * @param array $args (optional) -- User-supplied arguments.
	 * @return int
	 */
	private function populateUsers(array $args = array()): int {
		global $rs_query;
		
		$defaults = array(
			'username' => 'admin',
			'password' => '12345678',
			'email' => 'admin@rscms.com',
			'role' => getUserRoleId('Administrator')
		);
		
		$args = array_merge($defaults, $args);
		
		$hashed_password = password_hash($args['password'], PASSWORD_BCRYPT, array(
			'cost' => 10
		));
		
		$user = $rs_query->insert(getTable('u'), array(
			'username' => $args['username'],
			'password' => $hashed_password,
			'email' => $args['email'],
			'registered' => 'NOW()',
			'role' => $args['role']
		));
		
		$usermeta = array(
			'first_name' => '',
			'last_name' => '',
			'display_name' => $args['username'],
			'avatar' => 0,
			'theme' => 'bedrock',
			'dismissed_notices' => ''
		);
		
		foreach($usermeta as $key => $value) {
			$rs_query->insert(getTable('um'), array(
				'user' => $user,
				'key' => $key,
				'value' => $value
			));
		}
		
		return $user;
	}
	
	/**
	 * Populate the `settings` database table.
	 * @since 1.3.0-alpha
	 * @deprecated from 1.7.0-alpha to 1.0.8-beta
	 *
	 * @access private
	 * @param array $args (optional) -- User-supplied arguments.
	 */
	private function populateSettings(array $args = array()): void {
		global $rs_query;
		
		$defaults = array(
			'site_title' => 'My Website',
			'description' => 'A new ' . RS_ENGINE . ' website!',
			'site_url' => (isSecureConnection() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'],
			'admin_email' => 'admin@rscms.com',
			'default_user_role' => getUserRoleId('User'),
			'home_page' => $rs_query->selectField(getTable('p'), 'id', array(
				'status' => 'published',
				'type' => 'page'
			), array(
				'order_by' => 'id',
				'order' => 'ASC',
				'limit' => 1
			)),
			'do_robots' => 1,
			'enable_comments' => 1,
			'auto_approve_comments' => 0,
			'allow_anon_comments' => 0,
			'comments_per_page' => 10,
			'track_login_attempts' => 0,
			'delete_old_login_attempts' => 0,
			'login_slug' => '',
			'site_logo' => 0,
			'site_icon' => 0,
			'theme_color' => '#ededed',
			'active_modules' => '',
			'active_theme' => 'carbon'
		);
		
		$args = array_merge($defaults, $args);
		
		foreach($args as $name => $value) {
			$rs_query->insert(getTable('s'), array(
				'name' => $name,
				'value' => $value
			));
		}
	}
	
	/**
	 * Populate the `user_roles` database table.
	 * @since 1.0.8-beta
	 *
	 * @access private
	 */
	private function populateUserRoles(): void {
		global $rs_query;
		
		$roles = array('User', 'Editor', 'Moderator', 'Administrator');
		
		foreach($roles as $key => $value) {
			$rs_query->insert(getTable('ur'), array(
				'name' => $value,
				'level' => $key,
				'is_default' => 1
			));
		}
	}
	
	/**
	 * Populate the `user_privileges` and `user_relationships` database tables.
	 * @since 1.0.8-beta
	 *
	 * @access private
	 */
	private function populateUserPrivileges(): void {
		global $rs_query;
		
		$admin_pages = array(
			'pages',
			'posts',
			'categories',
			'media',
			'comments',
			'themes',
			'menus',
			'widgets',
			'users',
			'login_attempts',
			'login_blacklist',
			'login_rules',
			'settings',
			'user_roles'
		);
		
		$privileges = array(
			'can_view_',
			'can_create_',
			'can_edit_',
			'can_delete_'
		);
		
		foreach($admin_pages as $admin_page) {
			foreach($privileges as $privilege) {
				switch($admin_page) {
					case 'media':
						if($privilege === 'can_create_') $privilege = 'can_upload_';
						break;
					case 'comments':
						// Skip 'can_create_' for comments
						if($privilege === 'can_create_') continue 2;
						break;
					case 'login_attempts':
						// Skip 'can_create_', 'can_edit_', and 'can_delete_' for settings
						if($privilege !== 'can_view_')
							continue 2;
						break;
					case 'settings':
						// Skip 'can_view_', 'can_create_', and 'can_delete_' for settings
						if($privilege !== 'can_edit_')
							continue 2;
						break;
				}
				
				$rs_query->insert(getTable('up'), array(
					'name' => $privilege . $admin_page,
					'is_default' => 1
				));
			}
		}
		
		/**
		 * List of privileges:
		 * 1 => 'can_view_pages', 2 => 'can_create_pages', 3 => 'can_edit_pages', 4 => 'can_delete_pages',
		 * 5 => 'can_view_posts', 6 => 'can_create_posts', 7 => 'can_edit_posts', 8 => 'can_delete_posts',
		 * 9 => 'can_view_categories', 10 => 'can_create_categories', 11 => 'can_edit_categories', 12 => 'can_delete_categories',
		 * 13 => 'can_view_media', 14 => 'can_upload_media', 15 => 'can_edit_media', 16 => 'can_delete_media',
		 * 17 => 'can_view_comments', 18 => 'can_edit_comments', 19 => 'can_delete_comments',
		 * 20 => 'can_view_themes', 21 => 'can_create_themes', 22 => 'can_edit_themes', 23 => 'can_delete_themes',
		 * 24 => 'can_view_menus', 25 => 'can_create_menus', 26 => 'can_edit_menus', 27 => 'can_delete_menus',
		 * 28 => 'can_view_widgets', 29 => 'can_create_widgets', 30 => 'can_edit_widgets', 31 => 'can_delete_widgets',
		 * 32 => 'can_view_users', 33 => 'can_create_users', 34 => 'can_edit_users', 35 => 'can_delete_users',
		 * 36 => 'can_view_login_attempts',
		 * 37 => 'can_view_login_blacklist', 38 => 'can_create_login_blacklist', 39 => 'can_edit_login_blacklist', 40 => 'can_delete_login_blacklist',
		 * 41 => 'can_view_login_rules', 42 => 'can_create_login_rules', 43 => 'can_edit_login_rules', 44 => 'can_delete_login_rules',
		 * 45 => 'can_edit_settings',
		 * 46 => 'can_view_user_roles', 47 => 'can_create_user_roles', 48 => 'can_edit_user_roles', 49 => 'can_delete_user_roles'
		 */
		
		$roles = $rs_query->select(getTable('ur'), 'id', array(
			'id' => array('IN', 1, 2, 3, 4)
		), array(
			'order_by' => 'id'
		));
		
		foreach($roles as $role) {
			switch($role['id']) {
				case 1:
					// User
					$privileges = array();
					break;
				case 2:
					// Editor
					$privileges = array(
						1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15, 32, 46
					);
					break;
				case 3:
					// Moderator
					$privileges = array(
						1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14,
						15, 16, 17, 18, 19, 20, 24, 25, 26, 28, 29, 30,
						32, 33, 34, 36, 37, 38, 39, 40, 41, 42, 43, 46
					);
					break;
				case 4:
					// Administrator
					$privileges = array(
						1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14,
						15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26,
						27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38,
						39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49
					);
					break;
			}
			
			foreach($privileges as $privilege) {
				$rs_query->insert(getTable('ue'), array(
					'role' => $role['id'],
					'privilege' => $privilege
				));
			}
		}
	}
}