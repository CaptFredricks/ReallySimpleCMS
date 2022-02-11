<?php
/**
 * Site-wide functions.
 * @since 1.0.0[a]
 */

// Autoload classes
spl_autoload_register(function($class_name) {
	// Find all uppercase characters in the class name
	preg_match_all('/[A-Z]/', $class_name, $matches, PREG_SET_ORDER);
	
	// Check whether the class name contains multiple uppercase characters
	if(count($matches) > 1) {
		// Remove the first match
		array_shift($matches);
		
		// Loop through the matches
		foreach($matches as $match) {
			// Flatten the array
			$match = implode($match);
			
			// Insert hyphens before every match
			$class_name = substr_replace($class_name, '-', strpos($class_name, $match), 0);
		}
	}
	
	// Include the class
	require_once PATH.INC.'/class-'.strtolower($class_name).'.php';
});

// Generate a cookie hash based on the site's URL
define('COOKIE_HASH', md5(getSetting('site_url')));

/*------------------------------------*\
    HEADER & FOOTER
\*------------------------------------*/

/**
 * Fetch a theme-specific script file.
 * @since 2.0.7[a]
 *
 * @param string $script
 * @param string $version (optional; default: VERSION)
 * @return string
 */
function getThemeScript($script, $version = VERSION): string {
	// Construct the file path for the current theme
	$theme_path = trailingSlash(THEMES).getSetting('theme');
	
	return '<script src="'.trailingSlash($theme_path).$script.(!empty($version) ? '?v='.$version : '').'"></script>';
}

/**
 * Output a theme-specific script file.
 * @since 1.3.0[b]
 *
 * @param string $script
 * @param string $version (optional; default: VERSION)
 */
function putThemeScript($script, $version = VERSION): void {
	echo getThemeScript($script, $version);
}

/**
 * Fetch a theme-specific stylesheet.
 * @since 2.0.7[a]
 *
 * @param string $stylesheet
 * @param string $version (optional; default: VERSION)
 * @return string
 */
function getThemeStylesheet($stylesheet, $version = VERSION, $echo = true): string {
	// Construct the file path for the current theme
	$theme_path = trailingSlash(THEMES).getSetting('theme');
	
	return '<link href="'.trailingSlash($theme_path).$stylesheet.(!empty($version) ? '?v='.$version : '').'" rel="stylesheet">';
}

/**
 * Output a theme-specific stylesheet.
 * @since 1.3.0[b]
 *
 * @param string $stylesheet
 * @param string $version (optional; default: VERSION)
 */
function putThemeStylesheet($stylesheet, $version = VERSION): void {
	echo getThemeStylesheet($stylesheet, $version);
}

/**
 * Load all header scripts and stylesheets.
 * @since 2.4.2[a]
 *
 * @param string|array $exclude (optional; default: '')
 * @param string|array $include_styles (optional; default: array())
 * @param string|array $include_scripts (optional; default: array())
 */
function headerScripts($exclude = '', $include_styles = array(), $include_scripts = array()): void {
	// Convert $exclude to an array if it isn't already one
	if(!is_array($exclude)) $exclude = explode(' ', $exclude);
	
	// Button stylesheet
	if(!in_array('button', $exclude, true)) putStylesheet('button.min.css');
	
	// Default stylesheet
	if(!in_array('style', $exclude, true)) putStylesheet('style.min.css');
	
	if(!in_array('fa', $exclude, true)) {
		// Font Awesome icons stylesheet
		putStylesheet('font-awesome.min.css', ICONS_VERSION);
	
		// Font Awesome font-face rules stylesheet
		putStylesheet('font-awesome-rules.min.css');
	}
	
	// Check whether any custom stylesheets have been included
	if(!empty($include_styles)) {
		// Check whether the included stylesheets are in an array
		if(is_array($include_styles)) {
			// Loop through the array and include the stylesheets
			foreach($include_styles as $style) putThemeStylesheet($style[0].'.css', $style[1] ?? VERSION);
		}
	}
	
	// jQuery library
	if(!in_array('jquery', $exclude, true)) putScript('jquery.min.js', JQUERY_VERSION);
	
	// Check whether any custom scripts have been included
	if(!empty($include_scripts)) {
		// Check whether the included scripts are in an array
		if(is_array($include_scripts)) {
			// Loop through the array and include the scripts
			foreach($include_scripts as $script) putThemeScript($script[0].'.js', $script[1] ?? VERSION);
		}
	}
}

/**
 * Load all footer scripts and stylesheets.
 * @since 2.4.2[a]
 *
 * @param string|array $exclude (optional; default: '')
 * @param string|array $include_styles (optional; default: array())
 * @param string|array $include_scripts (optional; default: array())
 */
function footerScripts($exclude = '', $include_styles = array(), $include_scripts = array()): void {
	// Convert $exclude to an array if it isn't already one
	if(!is_array($exclude)) $exclude = explode(' ', $exclude);
	
	// Check whether any custom stylesheets have been included
	if(!empty($include_styles)) {
		// Check whether the included stylesheets are in an array
		if(is_array($include_styles)) {
			// Loop through the array and include the stylesheets
			foreach($include_styles as $style) putThemeStylesheet($style[0].'.css', $style[1] ?? VERSION);
		}
	}
	
	// Default scripts
	if(!in_array('script', $exclude, true)) putScript('script.js');
	
	// Check whether any custom scripts have been included
	if(!empty($include_scripts)) {
		// Check whether the included scripts are in an array
		if(is_array($include_scripts)) {
			// Loop through the array and include the scripts
			foreach($include_scripts as $script) putThemeScript($script[0].'.js', $script[1] ?? VERSION);
		}
	}
}

/**
 * Construct a list of CSS classes for the body tag.
 * @since 2.2.3[a]
 *
 * @param array $addtl_classes (optional; default: array())
 * @return string
 */
function bodyClasses($addtl_classes = array()): string {
	// Extend the Post and Term objects and the user's session data
	global $rs_post, $rs_term, $session;
	
	// Create an empty array to hold the classes
	$classes = array();
	
	// Check whether the Post object has data
	if($rs_post) {
		// Fetch the post's id from the database
		$id = $rs_post->getPostId();
		
		// Fetch the post's parent from the database
		$parent = $rs_post->getPostParent();
		
		// Fetch the post's type from the database
		$type = $rs_post->getPostType();
		
		// Fetch the current theme from the database and add an appropriate class
		$classes[] = getSetting('theme').'-theme';
		
		// Fetch the post's slug from the database and add an appropriate class
		$classes[] = $rs_post->getPostSlug($id);
		
		// Add an appropriate class with the post's type
		$classes[] = $type;
		
		// Add an appropriate class with the post's type and id
		$classes[] = $type.'-id-'.$id;
		
		// Check whether the current page is a child of another page and add an appropriate class if so
		if($parent !== 0) $classes[] = $rs_post->getPostSlug($parent).'-child';
		
		// Check whether the current page is the home page and add an appropriate class if so
		if(isHomePage($id)) $classes[] = 'home-page';
	} // Check whether the Term object has data
	elseif($rs_term) {
		// Fetch the term's id from the database
		$id = $rs_term->getTermId();
		
		// Fetch the term's taxonomy from the database
		$taxonomy = $rs_term->getTermTaxonomy();
		
		// Fetch the current theme from the database and add an appropriate class
		$classes[] = getSetting('theme').'-theme';
		
		// Fetch the term's slug from the database and add an appropriate class
		$classes[] = $rs_term->getTermSlug($id);
		
		// Add an appropriate class with the term's taxonomy
		$classes[] = $taxonomy;
		
		// Add an appropriate class with the term's taxonomy and id
		$classes[] = $taxonomy.'-id-'.$id;
	}
	
	// Merge any additional classes with the classes array
	$classes = array_merge($classes, (array)$addtl_classes);
	
	// Check whether the user is logged in and add an appropriate class if so
	if($session) $classes[] = 'logged-in';
	
	// Return the classes as a string
	return implode(' ', $classes);
}

/**
 * Construct an admin bar for logged in users.
 * @since 2.2.7[a]
 */
function adminBar(): void {
	// Extend the Post object, the user's session data, and the post types and taxonomies arrays
	global $rs_post, $session, $post_types, $taxonomies;
	?>
	<div id="admin-bar">
		<ul class="menu">
			<li>
				<a href="javascript:void(0)"><i class="fas fa-tachometer-alt"></i> <span>Admin</span></a>
				<ul class="sub-menu">
					<li><a href="/admin/">Dashboard</a></li>
					<?php
					// Loop through the post types
					foreach($post_types as $post_type) {
						// Skip any post type that the user doesn't have sufficient privileges to view or that has 'show_in_admin_bar' set to false
						if(!userHasPrivilege($session['role'], 'can_view_'.str_replace(' ', '_', $post_type['labels']['name_lowercase'])) || !$post_type['show_in_admin_bar']) continue;
						?>
						<li>
							<a href="/admin/<?php echo $post_type['menu_link']; ?>"><?php echo $post_type['label']; ?></a>
							<?php
							// Check whether the post type has a valid taxonomy associated with it and has 'show_in_admin_bar' set to true
							if(!empty($post_type['taxonomy']) && array_key_exists($post_type['taxonomy'], $taxonomies) && userHasPrivilege($session['role'], 'can_view_'.str_replace(' ', '_', $taxonomies[$post_type['taxonomy']]['labels']['name_lowercase'])) && $taxonomies[$post_type['taxonomy']]['show_in_admin_bar']) {
								?>
								<ul class="sub-menu">
									<li>
										<a href="/admin/<?php echo $taxonomies[$post_type['taxonomy']]['menu_link']; ?>"><?php echo $taxonomies[$post_type['taxonomy']]['label']; ?></a>
									</li>
								</ul>
								<?php
							}
							?>
						</li>
						<?php
					}
					
					// Check whether the user has sufficient privileges to view comments
					if(userHasPrivilege($session['role'], 'can_view_comments')) {
						?>
						<li><a href="/admin/comments.php">Comments</a></li>
						<?php
					}
					
					// Check whether the user has sufficient privileges to view customization options
					if(userHasPrivileges($session['role'], array(
						'can_view_themes',
						'can_view_menus',
						'can_view_widgets'
					), 'OR')): ?>
						<li>
							<a href="javascript:void(0)">Customization</a>
							<ul class="sub-menu">
								<?php if(userHasPrivilege($session['role'], 'can_view_themes')): ?>
									<li><a href="/admin/themes.php">Themes</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege($session['role'], 'can_view_menus')): ?>
									<li><a href="/admin/menus.php">Menus</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege($session['role'], 'can_view_widgets')): ?>
									<li><a href="/admin/widgets.php">Widgets</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					<?php if(userHasPrivilege($session['role'], 'can_view_users')): ?>
						<li><a href="/admin/users.php">Users</a></li>
					<?php endif; ?>
					<?php if(userHasPrivileges($session['role'], array(
						'can_view_login_attempts',
						'can_view_login_blacklist',
						'can_view_login_rules'
					), 'OR')): ?>
						<li>
							<a href="javascript:void(0)">Logins</a>
							<ul class="sub-menu">
								<?php if(userHasPrivilege($session['role'], 'can_view_login_attempts')): ?>
									<li><a href="/admin/logins.php">Attempts</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege($session['role'], 'can_view_login_blacklist')): ?>
									<li><a href="/admin/logins.php?page=blacklist">Blacklist</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege($session['role'], 'can_view_login_rules')): ?>
									<li><a href="/admin/logins.php?page=rules">Rules</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					<?php if(userHasPrivileges($session['role'], array(
						'can_edit_settings',
						'can_view_user_roles'
					), 'OR')): ?>
						<li>
							<a href="javascript:void(0)">Settings</a>
							<ul class="sub-menu">
								<?php if(userHasPrivilege($session['role'], 'can_edit_settings')): ?>
									<li><a href="/admin/settings.php">General</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege($session['role'], 'can_edit_settings')): ?>
									<li><a href="/admin/settings.php?page=design">Design</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege($session['role'], 'can_view_user_roles')): ?>
									<li><a href="/admin/settings.php?page=user_roles">User Roles</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
				</ul>
			</li>
			<li>
				<a href="javascript:void(0)"><i class="fas fa-plus"></i> <span>New</span></a>
				<ul class="sub-menu">
					<?php
					// Loop through the post types
					foreach($post_types as $post_type) {
						// Skip any post type that the user doesn't have sufficient privileges to create or that has 'show_in_admin_bar' set to false
						if(!userHasPrivilege($session['role'], ($post_type['name'] === 'media' ? 'can_upload_media' : 'can_create_'.str_replace(' ', '_', $post_type['labels']['name_lowercase']))) || !$post_type['show_in_admin_bar']) continue;
						?>
						<li>
							<a href="/admin/<?php echo $post_type['menu_link'].($post_type['name'] === 'media' ? '?action=upload' : ($post_type['name'] === 'post' ? '?action=create' : '&action=create')); ?>"><?php echo $post_type['labels']['name_singular']; ?></a>
							<?php
							// Check whether the post type has a valid taxonomy associated with it and has 'show_in_admin_bar' set to true
							if(!empty($post_type['taxonomy']) && array_key_exists($post_type['taxonomy'], $taxonomies) && userHasPrivilege($session['role'], 'can_create_'.str_replace(' ', '_', $taxonomies[$post_type['taxonomy']]['labels']['name_lowercase'])) && $taxonomies[$post_type['taxonomy']]['show_in_admin_bar']) {
								?>
								<ul class="sub-menu">
									<li>
										<a href="/admin/<?php echo $taxonomies[$post_type['taxonomy']]['menu_link'].($post_type['taxonomy'] === 'category' ? '?action=create' : '&action=create'); ?>"><?php echo $taxonomies[$post_type['taxonomy']]['labels']['name_singular']; ?></a>
									</li>
								</ul>
								<?php
							}
							?>
						</li>
						<?php
					}
					
					// Check whether the user has sufficient privileges to view customization options
					if(userHasPrivileges($session['role'], array(
						'can_create_themes',
						'can_create_menus',
						'can_create_widgets'
					), 'OR')): ?>
						<li>
							<a href="javascript:void(0)">Customization</a>
							<ul class="sub-menu">
								<?php if(userHasPrivilege($session['role'], 'can_create_themes')): ?>
									<li><a href="/admin/themes.php?action=create">Theme</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege($session['role'], 'can_create_menus')): ?>
									<li><a href="/admin/menus.php?action=create">Menu</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege($session['role'], 'can_create_widgets')): ?>
									<li><a href="/admin/widgets.php?action=create">Widget</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					<?php if(userHasPrivilege($session['role'], 'can_create_users')): ?>
						<li><a href="/admin/users.php?action=create">User</a></li>
					<?php endif; ?>
					<?php if(userHasPrivilege($session['role'], 'can_create_login_rules')): ?>
						<li>
							<a href="javascript:void(0)">Login</a>
							<ul class="sub-menu">
								<li><a href="/admin/logins.php?page=rules&action=create">Rule</a></li>
							</ul>
						</li>
					<?php endif; ?>
					<?php if(userHasPrivilege($session['role'], 'can_create_user_roles')): ?>
						<li>
							<a href="javascript:void(0)">Settings</a>
							<ul class="sub-menu">
								<li><a href="/admin/settings.php?page=user_roles&action=create">User Roles</a></li>
							</ul>
						</li>
					<?php endif; ?>
				</ul>
			</li>
			<?php if(!is_null($rs_post)): ?>
				<li>
					<a href="/admin/posts.php?id=<?php echo $rs_post->getPostId(); ?>&action=edit"><i class="fas fa-feather-alt"></i> <span>Edit</span></a>
				</li>
			<?php endif; ?>
		</ul>
		<div class="user-dropdown">
			<span>Welcome, <?php echo $session['username']; ?></span>
			<?php echo getMedia($session['avatar'], array(
				'class' => 'avatar',
				'width' => 20,
				'height' => 20
			)); ?>
			<ul class="user-dropdown-menu">
				<?php echo getMedia($session['avatar'], array(
					'class' => 'avatar-large',
					'width' => 100,
					'height' => 100
				)); ?>
				<li><a href="/admin/profile.php">My Profile</a></li>
				<li><a href="/login.php?action=logout">Log Out</a></li>
			</ul>
		</div>
	</div>
	<?php
}

/*------------------------------------*\
    MISCELLANEOUS
\*------------------------------------*/

/**
 * Check whether a post type exists in the database.
 * @since 1.0.5[b]
 *
 * @param string $type
 * @return bool
 */
function postTypeExists($type): bool {
	// Extend the Query object
	global $rs_query;
	
	// Sanitize the post type's name
	$type = sanitize($type);
	
	// Fetch the number of times the type appears in the database and return true if it does
	return $rs_query->selectRow('posts', 'COUNT(type)', array('type' => $type)) > 0;
}

/**
 * Check whether a taxonomy exists in the database.
 * @since 1.0.5[b]
 *
 * @param string $taxonomy
 * @return bool
 */
function taxonomyExists($taxonomy): bool {
	// Extend the Query object
	global $rs_query;
	
	// Sanitize the taxonomy's name
	$taxonomy = sanitize($taxonomy);
	
	// Fetch the number of times the taxonomy appears in the database and return true if it does
	return $rs_query->selectRow('taxonomies', 'COUNT(name)', array('name' => $taxonomy)) > 0;
}

/**
 * Create a Post object based on a provided slug.
 * @since 2.2.3[a]
 *
 * @param string $slug
 * @return object
 */
function getPost($slug): object {
	return new Post($slug);
}

/**
 * Create a Term object based on a provided slug.
 * @since 1.0.6[b]
 *
 * @param string $slug
 * @return object
 */
function getTerm($slug): object {
	return new Term($slug);
}

/**
 * Alias for the getTerm function.
 * @since 2.4.1[a]
 *
 * @see getTerm()
 * @param string $slug
 * @return object
 */
function getCategory($slug): object {
	return getTerm($slug);
}

/**
 * Fetch a nav menu.
 * @since 2.2.3[a]
 *
 * @param string $slug
 */
function getMenu($slug): void {
	// Create a Menu object
	$rs_menu = new Menu;
	
	// Display the menu
	$rs_menu->getMenu($slug);
}

/**
 * Fetch a widget.
 * @since 2.2.1[a]
 *
 * @param string $slug
 * @param bool $display_title (optional; default: false)
 */
function getWidget($slug, $display_title = false): void {
	// Extend the Query object
	global $rs_query;
	
	// Fetch the widget from the database
	$widget = $rs_query->selectRow('posts', array('title', 'content', 'status'), array(
		'type' => 'widget',
		'slug' => $slug
	));
	
	// Check whether the widget exists and is active
	if(empty($widget)) {
		?>
		<div class="widget">
			<h3>The specified widget does not exist.</h3>
		</div>
		<?php
	} elseif($widget['status'] === 'inactive') {
		?>
		<div class="widget">
			<h3>The specified widget could not be loaded.</h3>
		</div>
		<?php
	} else {
		?>
		<div class="widget <?php echo $slug; ?>">
			<?php
			// Check whether the title should be displayed
			if($display_title) {
				?>
				<h3 class="widget-title"><?php echo $widget['title']; ?></h3>
				<?php
			}
			?>
			<div class="widget-content">
				<?php
				// Display the widget's content
				echo $widget['content'];
				?>
			</div>
		</div>
		<?php
	}
}

/**
 * Register a menu.
 * @since 1.0.0[b]
 *
 * @param string $name
 * @param string $slug
 */
function registerMenu($name, $slug): void {
	// Extend the Query object
	global $rs_query;
	
	// Sanitize the slug
	$slug = sanitize($slug);
	
	// Fetch any menus that have the same slug as the newly registered one
	$menu = $rs_query->selectRow('terms', '*', array('slug' => $slug, 'taxonomy' => getTaxonomyId('nav_menu')));
	
	// Check whether the menu already exists
	if(empty($menu)) {
		// Insert the new menu into the database
		$rs_query->insert('terms', array(
			'name' => $name,
			'slug' => $slug,
			'taxonomy' => getTaxonomyId('nav_menu')
		));
	}
}

/**
 * Register a widget.
 * @since 1.0.0[b]
 *
 * @param string $title
 * @param string $slug
 */
function registerWidget($title, $slug): void {
	// Extend the Query object
	global $rs_query;
	
	// Sanitize the slug
	$slug = sanitize($slug);
	
	// Fetch any widgets that have the same slug as the newly registered one
	$widget = $rs_query->selectRow('posts', '*', array('slug' => $slug, 'type' => 'widget'));
	
	// Check whether the widget already exists
	if(empty($widget)) {
		// Insert the new widget into the database
		$rs_query->insert('posts', array(
			'title' => $title,
			'date' => 'NOW()',
			'content' => '',
			'status' => 'active',
			'slug' => $slug,
			'type' => 'widget'
		));
	}
}

/**
 * Generate a random hash.
 * @since 2.0.5[a]
 *
 * @param int $length (optional; default: 20)
 * @param bool $special_chars (optional; default: true)
 * @param string $salt (optional; default: '')
 * @return string
 */
function generateHash($length = 20, $special_chars = true, $salt = ''): string {
	// Regular characters
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	
	// If desired, add the special characters
	if($special_chars) $chars .= '!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
	
	// Create an empty variable to hold the hash
	$hash = '';
	
	// Construct a randomized hash
	for($i = 0; $i < (int)$length; $i++)
		$hash .= substr($chars, rand(0, strlen($chars) - 1), 1);
	
	// Add any salt that's been provided and hash it with md5
	if(!empty($salt)) $hash = substr(md5(md5($hash.$salt)), 0, (int)$length);
	
	// Return the hash
	return $hash;
}

/**
 * Format an email message with HTML and CSS.
 * @since 2.0.5[a]
 *
 * @param string $heading
 * @param array $fields
 * @return string
 */
function formatEmail($heading, $fields): string {
	$content = '<div style="background-color: #ededed; padding: 3rem 0;">';
	$content .= '<div style="background-color: #fdfdfd; border: 1px solid #cdcdcd; border-top-color: #ededed; color: #101010 !important; margin: 0 auto; padding: 0.75rem 1.5rem; width: 60%;">';
	$content .= !empty($heading) ? '<h2 style="text-align: center;">'.$heading.'</h2>' : '';
	$content .= !empty($fields['name']) && !empty($fields['email']) ? '<p style="margin-bottom: 0;"><strong>Name:</strong> '.$fields['name'].'</p><p style="margin-top: 0;"><strong>Email:</strong> '.$fields['email'].'</p>' : '';
	$content .= '<p style="border-top: 1px dashed #adadad; padding-top: 1em;">'.str_replace("\r\n", '<br>', $fields['message']).'</p>';
	$content .= '</div></div>';
	
	// Return the content
	return $content;
}