<?php
/**
 * Functions that must be loaded early in the initialization.
 * @since 1.3.9-beta
 *
 * @package ReallySimpleCMS
 */

require_once PATH . INC . '/polyfill-functions.php';

/**
 * Autoload a class.
 * @since 1.0.2-alpha
 *
 * @param string $class -- The name of the class.
 */
spl_autoload_register(function(string $class) {
	if(!isAdmin() || (isAdmin() && !file_exists(PATH . ADMIN . INC . getClassFilename($class))))
		$file = PATH . INC . getClassFilename($class);
	
	if(isset($file) && file_exists($file)) require $file;
});

/*------------------------------------*\
    MISCELLANEOUS
\*------------------------------------*/

/**
 * Make sure the server is running the required PHP version.
 * @since 1.3.9-beta
 */
function checkPHPVersion(): void {
	$notice = 'The minimum version of PHP that is supported by ' . CMS_ENGINE . ' is ' . PHP_MINIMUM .
		'; your server is running on ' . PHP_VERSION . '. ' .
		'Please upgrade to the minimum required version or higher to use this software.';
	
	if(version_compare(PHP_VERSION, PHP_MINIMUM, '<'))
		exit('<p>' . $notice . '</p>');
}

/**
 * Make sure the database can be connected to.
 * @since 1.3.13-beta
 */
function checkDBStatus(): void {
	global $rs_query;
	
	$notice = 'There is a problem with your database connection. Check your <code>config.php</code> file located in the <code>root</code> directory of your installation.';
	
	if(!$rs_query->conn_status)
		exit('<p>' . $notice . '</p>');
}

/**
 * Redirect to a specified URL.
 * @since 1.7.2-alpha
 *
 * @param string $url -- The URL to redirect to.
 * @param int $status (optional) -- The HTTP status code.
 */
function redirect(string $url, int $status = 302): void {
	header('Location: ' . $url, true, $status);
	exit;
}

/**
 * Construct a class' filename.
 * @since 1.3.9-beta
 *
 * @param string $name -- The name of the class.
 * @return string
 */
function getClassFilename(string $name): string {
	$is_interface = false;
	
	$name = str_replace('\\', '/', $name);
	$path = array();
	
	if(str_contains($name, '/')) {
		$raw_path = explode('/', $name);
		$name = array_pop($raw_path);
		
		foreach($raw_path as $p)
			$path[] = formatPathFragment($p);
	}
	
	if(str_ends_with($name, 'Interface')) {
		$is_interface = true;
		$name = substr($name, 0, strpos($name, 'Interface'));
	}
	
	$name = formatPathFragment($name);
	$path = slash(implode('/', $path));
	$path = !str_starts_with($path, '/') ? '/' . $path : $path;
	
	return $path . ($is_interface ? 'interface-' : 'class-') . $name . '.php';
}

/**
 * Format a fragment of a file path.
 * @since 1.3.9-beta
 *
 * @param string $frag -- The file path fragment.
 * @return string
 */
function formatPathFragment(string $frag): string {
	preg_match_all('/[A-Z][a-z]+/', $frag, $matches, PREG_SET_ORDER);
	
	if(count($matches) > 1) {
		$first_match = implode('', array_shift($matches));
		$m_string = '';
		
		foreach($matches as $match)
			$m_string .= '-' . implode('', $match);
		
		$frag = $first_match . $m_string;
	}
	
	return strtolower($frag);
}

/**
 * Remove a trailing slash from a string.
 * @since 1.3.6-beta
 *
 * @param string $text -- The text string.
 * @return string
 */
function unslash(string $text): string {
	return rtrim($text, '/\\');
}

/**
 * Add a trailing slash to a string.
 * @since 1.3.6-beta
 *
 * @param string $text -- The text string.
 * @return string
 */
function slash(string $text): string {
	return unslash($text) . '/';
}