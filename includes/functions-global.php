<?php
/**
 * Global variables and functions (used system-wide).
 * @since 1.2.0-alpha
 *
 * @package ReallySimpleCMS
 *
 * ## GLOBAL VARS [6] ##
 * - array $rs_admin_pages
 * - array $rs_post_types
 * - array $rs_taxonomies
 * - array $rs_modules
 * - array $rs_themes
 * - array $rs_admin_themes
 *
 * ## AUTOLOADERS [1] ##
 * - spl_autoload_register(string $class) [module class autoloader]
 *
 * ## FUNCTIONS [29] ##
 * { REGISTRIES [6 files] }
 * - /register/admin-pages.php [3]
 * - /register/post-types.php [4]
 * - /register/taxonomies.php [5]
 * - /register/modules.php [6]
 * - /register/themes.php [9]
 * - /register/admin-themes.php [6]
 * { HEADER & FOOTER [4] }
 * - getScript(string $script, string $version): string
 * - putScript(string $script, string $version): void
 * - getStylesheet(string $stylesheet, string $version): string
 * - putStylesheet(string $stylesheet, string $version): void
 * { USER PRIVILEGES [4] }
 * - userHasPrivilege(string $privilege, ?int $role): bool
 * - userHasPrivileges(array $privileges, string $logic, ?int $role): bool
 * - getUserRoleId(string $name): int
 * - getUserPrivilegeId(string $name): int
 * { HASHING/RANDOMIZATION [2] }
 * - generateHash(int $length, bool $special_chars, string $salt): string
 * - generatePassword(int $length, bool $special_chars): string
 * { TEXT FORMATTING [4] }
 * - trimWords(string $text, int $num_words, string $more): string
 * - sanitize(string $text, string $regex, bool $lc): string
 * - capitalize(string $text): string
 * - formatDate(string $date, string $format): string
 * { MISCELLANEOUS [15] }
 * - isHomePage(int $id): bool
 * - isLogin(): bool
 * - is404(): bool
 * - isEmptyDir(string $dir): ?bool
 * - removeDir(string $dir): bool
 * - getTable(string $key): string|array
 * - getSetting(string $name): string|bool
 * - putSetting(string $name): void
 * - getPermalink(string $type_tax, int $parent, string $slug): string|bool
 * - getQueryString(array $args): string
 * - isValidSession(string $session): bool
 * - getOnlineUser(string $session): array
 * - getMediaSrc(int $id): string
 * - getMedia(int $id, array $args): string
 * - button(array $args, bool $link): void
 */

// Set the server timezone
ini_set('date.timezone', date_default_timezone_get());

/*------------------------------------*\
	GLOBAL VARIABLES
\*------------------------------------*/

/**
 * All registered admin pages.
 * @since 1.3.16-beta
 *
 * @var array
 */
$rs_admin_pages = array();

/**
 * All registered post types.
 * @since 1.0.0-beta
 *
 * @var array
 */
$rs_post_types = array();

/**
 * All registered taxonomies.
 * @since 1.0.4-beta
 *
 * @var array
 */
$rs_taxonomies = array();

/**
 * All registered modules.
 * @since 1.3.16-beta
 *
 * @var array
 */
$rs_modules = array();

/**
 * All registered themes.
 * @since 1.3.15-beta
 *
 * @var array
 */
$rs_themes = array();

/**
 * All registered admin themes.
 * @since 1.3.15-beta
 *
 * @var array
 */
$rs_admin_themes = array();

/*------------------------------------*\
    AUTOLOADERS
	 (must be placed before any
	 class declarations)
\*------------------------------------*/

/**
 * Autoload a module class.
 * @since 1.3.16-beta
 *
 * @param string $class -- The name of the class.
 */
spl_autoload_register(function(string $class) {
	global $rs_modules;
	
	$mod_name = strtolower(strtok($class, '\\'));
	$file_path = $mod_name . getClassFilename($class);
	
	if(array_key_exists($mod_name, $rs_modules) || file_exists(slash(PATH . MODULES) . $file_path))
		$file = slash(PATH . MODULES) . $file_path;
	
	if(isset($file) && file_exists($file)) requireFile($file);
});

/*------------------------------------*\
	REGISTRIES
\*------------------------------------*/

// Admin pages
requireFile(PATH . REGISTER . '/admin-pages.php');

// Post types
requireFile(PATH . REGISTER . '/post-types.php');

// Taxonomies
requireFile(PATH . REGISTER . '/taxonomies.php');

// Modules
requireFile(PATH . REGISTER . '/modules.php');

// Themes
requireFile(PATH . REGISTER . '/themes.php');

// Admin themes
requireFile(PATH . REGISTER . '/admin-themes.php');

/*------------------------------------*\
	HEADER & FOOTER
\*------------------------------------*/

/**
 * Fetch a script file.
 * @since 1.3.3-alpha
 *
 * @param string $script -- The script to load.
 * @param string $version (optional) -- The script's version.
 * @return string
 */
function getScript(string $script, string $version = RS_VERSION): string {
	return domTag('script', array(
		'src' => slash(SCRIPTS) . $script . (!empty($version) ? '?v=' . $version : '')
	));
}

/**
 * Output a script file.
 * @since 1.3.0-beta
 *
 * @param string $script -- The script to load.
 * @param string $version (optional) -- The script's version.
 */
function putScript(string $script, string $version = RS_VERSION): void {
	echo getScript($script, $version);
}

/**
 * Fetch a stylesheet.
 * @since 1.3.3-alpha
 *
 * @param string $stylesheet -- The stylesheet to load.
 * @param string $version (optional) -- The stylesheet's version.
 * @return string
 */
function getStylesheet(string $stylesheet, string $version = RS_VERSION): string {
	return domTag('link', array(
		'href' => slash(STYLES) . $stylesheet . (!empty($version) ? '?v=' . $version : ''),
		'rel' => 'stylesheet'
	));
}

/**
 * Output a stylesheet.
 * @since 1.3.0-beta
 *
 * @param string $stylesheet -- The stylesheet to load.
 * @param string $version (optional) -- The stylesheet's version.
 */
function putStylesheet(string $stylesheet, string $version = RS_VERSION): void {
	echo getStylesheet($stylesheet, $version);
}

/*------------------------------------*\
	USER PRIVILEGES
\*------------------------------------*/

/**
 * Check whether a user has a specified privilege.
 * @since 1.7.2-alpha
 *
 * @param string $privilege -- The privilege's name.
 * @param null|int $role (optional) -- The role's id.
 * @return bool
 */
function userHasPrivilege(string $privilege, ?int $role = null): bool {
	global $rs_query, $rs_session;
	
	if(is_null($role)) $role = $rs_session['role'];
	
	$id = $rs_query->selectField(getTable('up'), 'id', array(
		'name' => $privilege
	));
	
	return $rs_query->selectRow(getTable('ue'), 'COUNT(*)', array(
		'role' => $role,
		'privilege' => $id
	)) > 0;
}

/**
 * Check whether a user has a specified group of privileges.
 * @since 1.2.0-beta_snap-02
 *
 * @param array $privileges (optional) -- A list of the privileges' names.
 * @param string $logic (optional) -- Query logic operator.
 * @param null|int $role (optional) -- The role's id.
 * @return bool
 */
function userHasPrivileges(array $privileges = array(), string $logic = 'AND', ?int $role = null): bool {
	if(!is_array($privileges)) $privileges = (array)$privileges;
	
	foreach($privileges as $privilege) {
		if(strtoupper($logic) === 'AND') {
			if(userHasPrivilege($privilege, $role) === false) return false;
		} elseif(strtoupper($logic) === 'OR') {
			if(userHasPrivilege($privilege, $role) === true) return true;
		}
	}
	
	if(strtoupper($logic) === 'AND')
		return true;
	elseif(strtoupper($logic) === 'OR')
		return false;
}

/**
 * Fetch a user role's id.
 * @since 1.0.5-beta
 *
 * @param string $name -- The role's name.
 * @return int
 */
function getUserRoleId(string $name): int {
	global $rs_query;
	
	$name = sanitize($name, '/[^A-Za-z0-9_-]/', false);
	
	return (int)$rs_query->selectField(getTable('ur'), 'id', array(
		'name' => $name
	)) ?? 0;
}

/**
 * Fetch a user privilege's id.
 * @since 1.0.5-beta
 *
 * @param string $name -- The privilege's name.
 * @return int
 */
function getUserPrivilegeId(string $name): int {
	global $rs_query;
	
	$name = sanitize($name);
	
	return (int)$rs_query->selectField(getTable('up'), 'id', array(
		'name' => $name
	)) ?? 0;
}

/*------------------------------------*\
	HASHING/RANDOMIZATION
\*------------------------------------*/

/**
 * Generate a random hash.
 * @since 2.0.5-alpha
 *
 * @param int $length (optional) -- The length of the hash.
 * @param bool $special_chars (optional) -- Whether to include special characters.
 * @param string $salt (optional) -- The hash salt.
 * @return string
 */
function generateHash(int $length = 20, bool $special_chars = true, string $salt = ''): string {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	
	if($special_chars) $chars .= '!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
	
	$hash = '';
	
	for($i = 0; $i < (int)$length; $i++)
		$hash .= substr($chars, rand(0, strlen($chars) - 1), 1);
	
	if(!empty($salt)) $hash = substr(md5(md5($hash . $salt)), 0, (int)$length);
	
	return $hash;
}

/**
 * Generate a random password.
 * @since 1.3.0-alpha
 *
 * @param int $length (optional) -- The length of the password.
 * @param bool $special_chars (optional) -- Whether to include special characters.
 * @return string
 */
function generatePassword(int $length = 16, bool $special_chars = true): string {
	return generateHash($length, $special_chars);
}

/*------------------------------------*\
	TEXT FORMATTING
\*------------------------------------*/

/**
 * Trim text down to a specified number of words.
 * @since 1.2.5-alpha
 *
 * @param string $text -- The text to trim.
 * @param int $num_words (optional) -- The number of words to include before trimming.
 * @param string $more (optional) -- The 'more' text.
 * @return string
 */
function trimWords(string $text, int $num_words = 50, string $more = '&hellip;'): string {
	$words = explode(' ', $text);
	
	if(count($words) > $num_words) {
		$words = array_slice($words, 0, $num_words);
		
		return implode(' ', $words) . $more;
	} else {
		return $text;
	}
}

/**
 * Sanitize a string of text.
 * @since 1.0.0-beta
 *
 * @param string $text -- The text to sanitize.
 * @param string $regex (optional) -- The regex pattern.
 * @param bool $lc (optional) -- Whether to format in lowercase.
 * @return string
 */
function sanitize(string $text, string $regex = '/[^A-Za-z0-9_-]/', bool $lc = true): string {
	$text = strip_tags($text);
	
	if($lc) $text = strtolower($text);
	
	return preg_replace($regex, '', $text);
}

/**
 * Capitalize a string of text, replacing underscores and dashes with spaces.
 * @since 1.3.15-beta
 *
 * @param string $text -- The text to capitalize.
 * @return string
 */
function capitalize(string $text): string {
	return ucwords(str_replace(array('_', '-'), ' ', $text));
}

/**
 * Format a date string.
 * @since 1.2.1-alpha
 *
 * @param string $date -- The raw date.
 * @param string $format (optional) -- The date format.
 * @return string
 */
function formatDate(string $date, string $format = 'Y-m-d H:i:s'): string {
	return date_format(date_create($date), $format);
}

/*------------------------------------*\
	MISCELLANEOUS
\*------------------------------------*/

/**
 * Check whether a post is the website's home page.
 * @since 1.4.0-alpha
 *
 * @param int $id -- The post's id.
 * @return bool
 */
function isHomePage(int $id): bool {
	global $rs_query;
	
	return (int)$rs_query->selectField(getTable('s'), 'value', array(
		'name' => 'home_page'
	)) === $id;
}

/**
 * Check whether the user is viewing the log in page.
 * @since 1.0.6-beta
 *
 * @return bool
 */
function isLogin(): bool {
	$login_slug = getSetting('login_slug');
	
	return str_starts_with($_SERVER['REQUEST_URI'], '/login.php') ||
		(!empty($login_slug) && str_contains($_SERVER['REQUEST_URI'], $login_slug));
}

/**
 * Check whether the user is viewing the 404 not found page.
 * @since 1.0.6-beta
 *
 * @return bool
 */
function is404(): bool {
	return str_starts_with($_SERVER['REQUEST_URI'], '/404.php');
}

/**
 * Check whether a directory is empty.
 * @since 2.3.0-alpha
 *
 * @param string $dir -- The directory to open.
 * @return null|bool
 */
function isEmptyDir(string $dir): ?bool {
	if(!is_readable($dir)) return null;
	
	$handle = opendir($dir);
	
	while(($entry = readdir($handle)) !== false)
		if($entry !== '.' && $entry !== '..') return false;
	
	return true;
}

/**
 * Recursively delete a directory and its contents.
 * @since 1.3.15-beta
 *
 * @param string $dir -- The directory to delete.
 * @return bool
 */
function removeDir(string $dir): bool {
	$files = array_diff(scandir($dir), array('.', '..'));
	
    foreach($files as $file)
		is_dir(slash($dir) . $file) ? removeDir(slash($dir) . $file) : unlink(slash($dir) . $file);
	
    return rmdir($dir);
}

/**
 * Fetch a database table based on its key.
 * @since 1.3.15-beta
 *
 * @see \Enums\Table::getTable()
 * @param string $key -- The table key.
 * @return string|array
 */
function getTable(string $key): string|array {
	return \Enums\Table::getTable($key);
}

/**
 * Retrieve a setting from the database.
 * @since 1.2.5-alpha
 *
 * @param string $name -- The setting's name.
 * @return string|bool
 */
function getSetting(string $name): string|bool {
	global $rs_query;
	
	$setting = (int)$rs_query->selectField(getTable('s'), 'COUNT(value)', array(
		'name' => $name
	));
	
	return $setting === 0 ? false : $rs_query->selectField(getTable('s'), 'value', array(
		'name' => $name
	));
}

/**
 * Output a setting from the database.
 * @since 1.3.0-beta
 *
 * @param string $name -- The setting's name.
 */
function putSetting(string $name): void { echo getSetting($name); }

/**
 * Construct a permalink.
 * @since 2.2.2-alpha
 *
 * @param string $type_tax -- The post type or taxonomy.
 * @param int $parent (optional) -- The post's parent.
 * @param string $slug (optional) -- The post's slug.
 * @return string|bool
 */
function getPermalink(string $type_tax, int $parent = 0, string $slug = ''): string|bool {
	global $rs_query, $rs_post_types, $rs_taxonomies;
	
	if(array_key_exists($type_tax, $rs_post_types)) {
		$table = getTable('p');
		
		if($type_tax !== 'post' && $type_tax !== 'page') {
			if($rs_post_types[$type_tax]['slug'] !== $type_tax)
				$base = str_replace('_', '-', $rs_post_types[$type_tax]['slug']);
			else
				$base = str_replace('_', '-', $type_tax);
		}
	} elseif(array_key_exists($type_tax, $rs_taxonomies)) {
		$table = getTable('t');
		
		if($rs_taxonomies[$type_tax]['slug'] !== $type_tax)
			$base = str_replace('_', '-', $rs_taxonomies[$type_tax]['slug']);
		else
			$base = str_replace('_', '-', $type_tax);
	} else {
		return false;
	}
	
	$permalink = array();
	
	while((int)$parent !== 0) {
		$item = $rs_query->selectRow($table, array('slug', 'parent'), array(
			'id' => $parent
		));
		
		$parent = (int)$item['parent'];
		$permalink[] = $item['slug'];
	}
	
	$permalink = implode('/', array_reverse($permalink));
	
	// Construct the full permalink and return it
	return '/' . (isset($base) ? slash($base) : '') .
		(!empty($permalink) ? slash($permalink) : '') .
		(!empty($slug) ? slash($slug) : '');
}

/**
 * Generate an HTTP-encoded query string.
 * @since 1.3.15-beta
 *
 * @param array $args -- The args.
 * @return string
 */
function getQueryString(array $args): string {
	return '?' . http_build_query($args);
}

/**
 * Check whether a user's session is valid.
 * @since 2.0.1-alpha
 *
 * @param string $session -- The session data.
 * @return bool
 */
function isValidSession(string $session): bool {
	global $rs_query;
	
	return $rs_query->selectRow(getTable('u'), 'COUNT(*)', array(
		'session' => $session
	)) > 0;
}

/**
 * Fetch an online user's data.
 * @since 2.0.1-alpha
 *
 * @param string $session -- The session data.
 * @return array
 */
function getOnlineUser(string $session): array {
	global $rs_query;
	
	$user = $rs_query->selectRow(getTable('u'), array('id', 'username', 'role'), array(
		'session' => $session
	));
	
	if(!empty($user)) {
		$usermeta = array('display_name', 'avatar', 'theme', 'dismissed_notices');
		
		foreach($usermeta as $meta) {
			$user[$meta] = $rs_query->selectField(getTable('um'), 'value', array(
				'user' => $user['id'],
				'key' => $meta
			));
		}
		
		$user['dismissed_notices'] = unserialize($user['dismissed_notices']);
	}
	
	return $user;
}

/**
 * Fetch the source of a specified media item.
 * @since 2.1.5-alpha
 *
 * @param int $id -- The media's id.
 * @return string
 */
function getMediaSrc(int $id): string {
	global $rs_query;
	
	$media = $rs_query->selectField(getTable('pm'), 'value', array(
		'post' => $id,
		'key' => 'filepath'
	));
	
	if(!empty($media))
		return slash(UPLOADS) . $media;
	else
		return '//:0';
}

/**
 * Fetch a specified media item.
 * @since 2.2.0-alpha
 *
 * @param int $id -- The media's id.
 * @param array $args (optional) -- Additional args.
 * @return string
 */
function getMedia(int $id, array $args = array()): string {
	global $rs_query;
	
	$src = getMediaSrc($id);
	
	if(empty($args['cached'])) $args['cached'] = true;
	
	if($args['cached'] === true && $src !== '//:0') {
		$modified = $rs_query->selectField(getTable('p'), 'modified', array(
			'id' => $id
		));
		
		$src .= getQueryString(array(
			'cached' => formatDate($modified, 'YmdHis')
		));
	}
	
	$mime_type = $rs_query->selectField(getTable('pm'), 'value', array(
		'post' => $id,
		'key' => 'mime_type'
	));
	
	// Determine what kind of HTML tag to construct based on the media's MIME type
	if(str_starts_with($mime_type, 'image') || $src === '//:0') {
		// Image tag
		$alt_text = $rs_query->selectField(getTable('pm'), 'value', array(
			'post' => $id,
			'key' => 'alt_text'
		));
		
		$props = array_merge(array(
			'src' => $src,
			'alt' => $alt_text
		), $args);
		
		$tag_args = array();
		
		foreach($props as $key => $value) {
			if($key === 'cached') continue;
			
			$tag_args[$key] = $value;
		}
		
		return domTag('img', $tag_args);
	} elseif(str_starts_with($mime_type, 'audio')) {
		// Audio tag
		return domTag('audio', array(
			'class' => (!empty($args['class']) ? $args['class'] : ''),
			'src' => $src
		));
	} elseif(str_starts_with($mime_type, 'video')) {
		// Video tag
		return domTag('video', array(
			'class' => (!empty($args['class']) ? $args['class'] : ''),
			'src' => $src
		));
	} else {
		// Anchor tag
		if(empty($args['link_text'])) {
			$args['link_text'] = $rs_query->selectField(getTable('p'), 'title', array(
				'id' => $id
			));
		}
		
		$tag_args = array();
		
		if(!empty($args['class'])) $tag_args['class'] = $args['class'];
		
		$tag_args['href'] = $src;
		
		if(!empty($args['newtab']) && $args['newtab'] === 1) {
			$tag_args['target'] = '_blank';
			$tag_args['rel'] = 'noreferrer noopener';
		}
		
		$tag_args['content'] = $args['link_text'];
		
		return domTag('a', $tag_args);
	}
}

/**
 * Create a button.
 * @since 1.2.7-beta
 *
 * @param array $args (optional) -- The args.
 * @param bool $link (optional) -- Whether to link the button.
 */
function button(array $args = array(), bool $link = false): void {
	if($link) {
		domTagPr('a', array(
			'id' => (!empty($args['id']) ? $args['id'] : ''),
			'class' => (!empty($args['class']) ? $args['class'] . ' ' : '') . 'button',
			'href' => ($args['link'] ?? '#'),
			'title' => (!empty($args['title']) ? $args['title'] : ''),
			'content' => ($args['label'] ?? 'Button')
		));
	} else {
		domTagPr('button', array(
			'id' => (!empty($args['id']) ? $args['id'] : ''),
			'class' => (!empty($args['class']) ? $args['class'] . ' ' : '') . 'button',
			'title' => (!empty($args['title']) ? $args['title'] : ''),
			'content' => ($args['label'] ?? 'Button')
		));
	}
}