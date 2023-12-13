<?php
/**
 * Site-wide functions.
 * @since 1.0.0[a]
 */

// Generate a cookie hash based on the site's URL
define('COOKIE_HASH', md5(getSetting('site_url')));

/*------------------------------------*\
    HEADER & FOOTER
\*------------------------------------*/

/**
 * Fetch a theme-specific script file.
 * @since 2.0.7[a]
 *
 * @param string $script -- The script to load.
 * @param string $version (optional) -- The script's version.
 * @return string
 */
function getThemeScript(string $script, string $version = CMS_VERSION): string {
	$theme_path = slash(THEMES) . getSetting('theme');
	
	return '<script src="' . slash($theme_path) . $script .
		(!empty($version) ? '?v=' . $version : '') . '"></script>';
}

/**
 * Output a theme-specific script file.
 * @since 1.3.0[b]
 *
 * @param string $script -- The script to load.
 * @param string $version (optional) -- The script's version.
 */
function putThemeScript(string $script, string $version = CMS_VERSION): void {
	echo getThemeScript($script, $version);
}

/**
 * Fetch a theme-specific stylesheet.
 * @since 2.0.7[a]
 *
 * @param string $stylesheet -- The stylesheet to load.
 * @param string $version (optional) -- The stylesheet's version.
 * @return string
 */
function getThemeStylesheet(string $stylesheet, string $version = CMS_VERSION): string {
	$theme_path = slash(THEMES) . getSetting('theme');
	
	return '<link href="' . slash($theme_path) . $stylesheet .
		(!empty($version) ? '?v=' . $version : '') . '" rel="stylesheet">';
}

/**
 * Output a theme-specific stylesheet.
 * @since 1.3.0[b]
 *
 * @param string $stylesheet -- The stylesheet to load.
 * @param string $version (optional) -- The stylesheet's version.
 */
function putThemeStylesheet(string $stylesheet, string $version = CMS_VERSION): void {
	echo getThemeStylesheet($stylesheet, $version);
}

/**
 * Load all header scripts and stylesheets.
 * @since 2.4.2[a]
 *
 * @param string|array $exclude (optional) -- The script(s) to exclude.
 * @param array $include_styles (optional) -- Any additional stylesheets to include.
 * @param array $include_scripts (optional) -- Any additional scripts to include.
 */
function headerScripts(
	string|array $exclude = '',
	array $include_styles = array(),
	array $include_scripts = array()
): void {
	if(!is_array($exclude)) $exclude = explode(' ', $exclude);
	
	$debug = false;
	if(defined('DEBUG_MODE') && DEBUG_MODE) $debug = true;
	
	// Button stylesheet
	if(!in_array('button', $exclude, true))
		putStylesheet('button' . ($debug ? '' : '.min') . '.css');
	
	// Default stylesheet
	if(!in_array('style', $exclude, true))
		putStylesheet('style' . ($debug ? '' : '.min') . '.css');
	
	if(!in_array('fa', $exclude, true)) {
		// Font Awesome icons stylesheet
		putStylesheet('font-awesome.min.css', ICONS_VERSION);
	
		// Font Awesome font-face rules stylesheet
		putStylesheet('font-awesome-rules.min.css');
	}
	
	// Additional custom stylesheets
	if(!empty($include_styles) && is_array($include_styles)) {
		foreach($include_styles as $style)
			putThemeStylesheet($style[0] . '.css', $style[1] ?? THEME_VERSION);
	}
	
	// jQuery library
	if(!in_array('jquery', $exclude, true)) putScript('jquery.min.js', JQUERY_VERSION);
	
	// Additional custom scripts
	if(!empty($include_scripts) && is_array($include_scripts)) {
		foreach($include_scripts as $script)
			putThemeScript($script[0] . '.js', $script[1] ?? THEME_VERSION);
	}
}

/**
 * Load all footer scripts and stylesheets.
 * @since 2.4.2[a]
 *
 * @param string|array $exclude (optional) -- The script(s) to exclude.
 * @param array $include_styles (optional) -- Any additional stylesheets to include.
 * @param array $include_scripts (optional) -- Any additional scripts to include.
 */
function footerScripts(
	string|array $exclude = '',
	array $include_styles = array(),
	array $include_scripts = array()
): void {
	if(!is_array($exclude)) $exclude = explode(' ', $exclude);
	
	$debug = false;
	if(defined('DEBUG_MODE') && DEBUG_MODE) $debug = true;
	
	// Additional custom stylesheets
	if(!empty($include_styles) && is_array($include_styles)) {
		foreach($include_styles as $style)
			putThemeStylesheet($style[0] . '.css', $style[1] ?? THEME_VERSION);
	}
	
	// Default scripts
	if(!in_array('script', $exclude, true)) putScript('script.js');
	
	// Additional custom scripts
	if(!empty($include_scripts) && is_array($include_scripts)) {
		foreach($include_scripts as $script)
			putThemeScript($script[0] . '.js', $script[1] ?? THEME_VERSION);
	}
}

/**
 * Construct a list of CSS classes for the body tag.
 * @since 2.2.3[a]
 *
 * @param string|array $addtl_classes (optional) -- Additional classes to include.
 * @return string
 */
function bodyClasses(string|array $addtl_classes = array()): string {
	global $rs_post, $rs_term, $session;
	
	$classes = array();
	
	if($rs_post) {
		$id = $rs_post->getPostId();
		$parent = $rs_post->getPostParent();
		$type = $rs_post->getPostType();
		
		$classes[] = getSetting('theme') . '-theme';
		$classes[] = $rs_post->getPostSlug($id);
		$classes[] = $type;
		$classes[] = $type . '-id-' . $id;
		
		if($parent !== 0) $classes[] = $rs_post->getPostSlug($parent) . '-child';
		if(isHomePage($id)) $classes[] = 'home-page';
	} elseif($rs_term) {
		$id = $rs_term->getTermId();
		$taxonomy = $rs_term->getTermTaxonomy();
		
		$classes[] = getSetting('theme') . '-theme';
		$classes[] = $rs_term->getTermSlug($id);
		$classes[] = $taxonomy;
		$classes[] = $taxonomy . '-id-' . $id;
	}
	
	$classes = array_merge($classes, (array)$addtl_classes);
	
	if($session) $classes[] = 'logged-in';
	
	return implode(' ', $classes);
}

/**
 * Construct an admin bar for logged in users.
 * @since 2.2.7[a]
 */
function adminBar(): void {
	global $rs_post, $rs_term, $session, $post_types, $taxonomies;
	?>
	<div id="admin-bar">
		<ul class="menu">
			<li>
				<a href="javascript:void(0)"><i class="fa-solid fa-gauge-high"></i> <span>Admin</span></a>
				<ul class="sub-menu">
					<li><a href="/admin/">Dashboard</a></li>
					<?php
					foreach($post_types as $post_type) {
						// Skip any post type that the user doesn't have sufficient privileges to view or that has 'show_in_admin_bar' set to false
						if(!userHasPrivilege('can_view_' . str_replace(' ', '_',
							$post_type['labels']['name_lowercase'])) || !$post_type['show_in_admin_bar']) continue;
						?>
						<li>
							<a href="/admin/<?php echo $post_type['menu_link']; ?>"><?php echo $post_type['label']; ?></a>
							<?php
							// Check whether the post type has a valid taxonomy associated with it and has 'show_in_admin_bar' set to true
							if(!empty($post_type['taxonomy']) &&
								array_key_exists($post_type['taxonomy'], $taxonomies) &&
								userHasPrivilege('can_view_' . str_replace(' ', '_',
								$taxonomies[$post_type['taxonomy']]['labels']['name_lowercase'])) &&
								$taxonomies[$post_type['taxonomy']]['show_in_admin_bar']) {
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
					
					if(userHasPrivilege('can_view_comments')) {
						?>
						<li><a href="/admin/comments.php">Comments</a></li>
						<?php
					}
					
					if(userHasPrivileges(array(
						'can_view_themes',
						'can_view_menus',
						'can_view_widgets'
					), 'OR')): ?>
						<li>
							<a href="javascript:void(0)">Customization</a>
							<ul class="sub-menu">
								<?php if(userHasPrivilege('can_view_themes')): ?>
									<li><a href="/admin/themes.php">Themes</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege('can_view_menus')): ?>
									<li><a href="/admin/menus.php">Menus</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege('can_view_widgets')): ?>
									<li><a href="/admin/widgets.php">Widgets</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					<?php if(userHasPrivilege('can_view_users')): ?>
						<li><a href="/admin/users.php">Users</a></li>
					<?php endif; ?>
					<?php if(userHasPrivileges(array(
						'can_view_login_attempts',
						'can_view_login_blacklist',
						'can_view_login_rules'
					), 'OR')): ?>
						<li>
							<a href="javascript:void(0)">Logins</a>
							<ul class="sub-menu">
								<?php if(userHasPrivilege('can_view_login_attempts')): ?>
									<li><a href="/admin/logins.php">Attempts</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege('can_view_login_blacklist')): ?>
									<li><a href="/admin/logins.php?page=blacklist">Blacklist</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege('can_view_login_rules')): ?>
									<li><a href="/admin/logins.php?page=rules">Rules</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					<?php if(userHasPrivileges(array(
						'can_edit_settings',
						'can_view_user_roles'
					), 'OR')): ?>
						<li>
							<a href="javascript:void(0)">Settings</a>
							<ul class="sub-menu">
								<?php if(userHasPrivilege('can_edit_settings')): ?>
									<li><a href="/admin/settings.php">General</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege('can_edit_settings')): ?>
									<li><a href="/admin/settings.php?page=design">Design</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege('can_view_user_roles')): ?>
									<li><a href="/admin/settings.php?page=user_roles">User Roles</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					<li><a href="/admin/about.php">About</a></li>
				</ul>
			</li>
			<li>
				<a href="javascript:void(0)"><i class="fa-solid fa-plus"></i> <span>New</span></a>
				<ul class="sub-menu">
					<?php
					foreach($post_types as $post_type) {
						// Skip any post type that the user doesn't have sufficient privileges to create or that has 'show_in_admin_bar' set to false
						if(!userHasPrivilege(($post_type['name'] === 'media' ? 'can_upload_media' :
							'can_create_' . str_replace(' ', '_', $post_type['labels']['name_lowercase']))) ||
							!$post_type['show_in_admin_bar']) continue;
						?>
						<li>
							<a href="/admin/<?php echo $post_type['menu_link'].($post_type['name'] === 'media' ? '?action=upload' : ($post_type['name'] === 'post' ? '?action=create' : '&action=create')); ?>"><?php echo $post_type['labels']['name_singular']; ?></a>
							<?php
							// Check whether the post type has a valid taxonomy associated with it and has 'show_in_admin_bar' set to true
							if(!empty($post_type['taxonomy']) &&
								array_key_exists($post_type['taxonomy'], $taxonomies) &&
								userHasPrivilege('can_create_' . str_replace(' ', '_',
								$taxonomies[$post_type['taxonomy']]['labels']['name_lowercase'])) &&
								$taxonomies[$post_type['taxonomy']]['show_in_admin_bar']) {
								?>
								<ul class="sub-menu">
									<li>
										<a href="/admin/<?php echo $taxonomies[$post_type['taxonomy']]['menu_link'] . ($post_type['taxonomy'] === 'category' ? '?action=create' : '&action=create'); ?>"><?php echo $taxonomies[$post_type['taxonomy']]['labels']['name_singular']; ?></a>
									</li>
								</ul>
								<?php
							}
							?>
						</li>
						<?php
					}
					
					if(userHasPrivileges(array(
						'can_create_themes',
						'can_create_menus',
						'can_create_widgets'
					), 'OR')): ?>
						<li>
							<a href="javascript:void(0)">Customization</a>
							<ul class="sub-menu">
								<?php if(userHasPrivilege('can_create_themes')): ?>
									<li><a href="/admin/themes.php?action=create">Theme</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege('can_create_menus')): ?>
									<li><a href="/admin/menus.php?action=create">Menu</a></li>
								<?php endif; ?>
								<?php if(userHasPrivilege('can_create_widgets')): ?>
									<li><a href="/admin/widgets.php?action=create">Widget</a></li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
					<?php if(userHasPrivilege('can_create_users')): ?>
						<li><a href="/admin/users.php?action=create">User</a></li>
					<?php endif; ?>
					<?php if(userHasPrivilege('can_create_login_rules')): ?>
						<li>
							<a href="javascript:void(0)">Login</a>
							<ul class="sub-menu">
								<li><a href="/admin/logins.php?page=rules&action=create">Rule</a></li>
							</ul>
						</li>
					<?php endif; ?>
					<?php if(userHasPrivilege('can_create_user_roles')): ?>
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
					<a href="/admin/posts.php?id=<?php
						// Create an edit link for pages and other post types
						echo $rs_post->getPostId();
					?>&action=edit"><i class="fa-solid fa-feather-pointed"></i> <span>Edit</span></a>
				</li>
			<?php elseif(!is_null($rs_term)): ?>
				<li>
					<a href="/admin/<?php
						// Create an edit link for categories and other terms
						echo ($rs_term->getTermTaxonomy() === 'category' ? 'categories.php' : 'terms.php') .
							'?id=' . $rs_term->getTermId();
					?>&action=edit"><i class="fa-solid fa-feather-pointed"></i> <span>Edit</span></a>
				</li>
			<?php endif; ?>
		</ul>
		<div class="user-dropdown">
			<span>Welcome, <?php echo $session['display_name']; ?></span>
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
 * Prevent direct access to `/login.php` if a login slug has been set.
 * @since 1.3.12[b]
 */
function handleSecureLogin(): void {
	$login_slug = getSetting('login_slug');
	
	// Check for secure login
	if(!empty($login_slug)) {
		if(!isset($_GET['secure_login'])) {
			if(str_starts_with($_SERVER['REQUEST_URI'], '/login.php')) {
				if(!isset($_GET['action']) && !isset($_GET['pw_reset']) && !isset($_GET['pw_forgot']))
					redirect('/404.php');
			} elseif(str_contains($_SERVER['REQUEST_URI'], $login_slug))
				redirect('/login.php?secure_login=' . $login_slug);
		} else {
			if($_GET['secure_login'] !== $login_slug)
				redirect('/404.php');
		}
	}
}

/**
 * Determine the type of page being viewed (e.g., post, term, etc.).
 * @since 1.3.11[b]
 */
function guessPageType(): void {
	global $rs_query, $rs_post, $rs_term;
	
	if(isset($_GET['preview']) && $_GET['preview'] === 'true' && isset($_GET['id']) && $_GET['id'] > 0) {
		$rs_post = new Post;
	} else {
		$raw_uri = $_SERVER['REQUEST_URI'];
		
		// Check whether the current page is the home page
		if($raw_uri === '/' || str_starts_with($raw_uri, '/?')) {
			$home_page = $rs_query->selectField('settings', 'value', array('name' => 'home_page'));
			$slug = $rs_query->selectField('posts', 'slug', array('id' => $home_page));
		} else {
			$uri = explode('/', $raw_uri);
			$uri = array_filter($uri);
			
			// Remove the query string
			if(str_starts_with(end($uri), '?'))
				array_pop($uri);
			
			$slug = array_pop($uri);
		}
		
		// Check whether the current page is a post or a term
		if($rs_query->selectRow('posts', 'COUNT(slug)', array('slug' => $slug)) > 0) {
			$rs_post = new Post;
		} elseif($rs_query->selectRow('terms', 'COUNT(slug)', array('slug' => $slug)) > 0) {
			$rs_term = new Term;
		} else {
			// Catastrophic failure, abort
			redirect('/404.php');
		}
	}
}

/**
 * Check whether a post type exists in the database.
 * @since 1.0.5[b]
 *
 * @param string $type -- The post's type.
 * @return bool
 */
function postTypeExists(string $type): bool {
	global $rs_query;
	
	$type = sanitize($type);
	
	return $rs_query->selectRow('posts', 'COUNT(type)', array('type' => $type)) > 0;
}

/**
 * Check whether a taxonomy exists in the database.
 * @since 1.0.5[b]
 *
 * @param string $taxonomy -- The taxonomy's name.
 * @return bool
 */
function taxonomyExists(string $taxonomy): bool {
	global $rs_query;
	
	$taxonomy = sanitize($taxonomy);
	
	return $rs_query->selectRow('taxonomies', 'COUNT(name)', array('name' => $taxonomy)) > 0;
}

/**
 * Create a Post object based on a provided slug.
 * @since 2.2.3[a]
 *
 * @param string $slug -- The post's slug.
 * @return object
 */
function getPost(string $slug): object {
	return new Post($slug);
}

/**
 * Create a Term object based on a provided slug.
 * @since 1.0.6[b]
 *
 * @param string $slug -- The term's slug.
 * @return object
 */
function getTerm(string $slug): object {
	return new Term($slug);
}

/**
 * Alias for the getTerm function.
 * @since 2.4.1[a]
 *
 * @see getTerm()
 * @param string $slug -- The category's slug.
 * @return object
 */
function getCategory(string $slug): object {
	return getTerm($slug);
}

/**
 * Fetch a nav menu.
 * @since 2.2.3[a]
 *
 * @param string $slug -- The menu's slug.
 */
function getMenu(string $slug): void {
	$rs_menu = new Menu;
	$rs_menu->getMenu($slug);
}

/**
 * Fetch a widget.
 * @since 2.2.1[a]
 *
 * @param string $slug -- The widget's slug.
 * @param bool $display_title (optional) -- Whether to display the widget's title.
 */
function getWidget(string $slug, bool $display_title = false): void {
	global $rs_query;
	
	$widget = $rs_query->selectRow('posts', array('title', 'content', 'status'), array(
		'type' => 'widget',
		'slug' => $slug
	));
	
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
			if($display_title) {
				?>
				<h3 class="widget-title"><?php echo $widget['title']; ?></h3>
				<?php
			}
			?>
			<div class="widget-content">
				<?php echo $widget['content']; ?>
			</div>
		</div>
		<?php
	}
}

/**
 * Register a menu.
 * @since 1.0.0[b]
 *
 * @param string $name -- The menu's name.
 * @param string $slug -- The menu's slug.
 */
function registerMenu(string $name, string $slug): void {
	global $rs_query;
	
	$slug = sanitize($slug);
	$menu = $rs_query->selectRow('terms', '*', array('slug' => $slug, 'taxonomy' => getTaxonomyId('nav_menu')));
	
	if(empty($menu)) {
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
 * @param string $title -- The widget's title.
 * @param string $slug -- The widget's slug.
 */
function registerWidget(string $title, string $slug): void {
	global $rs_query;
	
	$slug = sanitize($slug);
	$widget = $rs_query->selectRow('posts', '*', array('slug' => $slug, 'type' => 'widget'));
	
	if(empty($widget)) {
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
 * Format an email message with HTML and CSS.
 * @since 2.0.5[a]
 *
 * @param string $heading -- The email heading.
 * @param array $fields -- The email fields.
 * @return string
 */
function formatEmail(string $heading, array $fields): string {
	$content = '<div style="background-color: #ededed; padding: 3rem 0;">';
	$content .= '<div style="background-color: #fdfdfd; border: 1px solid #cdcdcd; border-top-color: #ededed; color: #101010 !important; margin: 0 auto; padding: 0.75rem 1.5rem; width: 60%;">';
	$content .= !empty($heading) ? '<h2 style="text-align: center;">' . $heading . '</h2>' : '';
	$content .= !empty($fields['name']) && !empty($fields['email']) ? '<p style="margin-bottom: 0;"><strong>Name:</strong> ' . $fields['name'] . '</p><p style="margin-top: 0;"><strong>Email:</strong> ' . $fields['email'] . '</p>' : '';
	$content .= '<p style="border-top: 1px dashed #adadad; padding-top: 1em;">' . str_replace("\r\n", '<br>', $fields['message']) . '</p>';
	$content .= '</div></div>';
	
	return $content;
}