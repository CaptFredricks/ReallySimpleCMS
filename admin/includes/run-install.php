<?php
/**
 * Run the database installation.
 * @since 1.2.6[b]
 */

// Minimum username length
const UN_LENGTH = 4;

// Minimum password length
const PW_LENGTH = 8;

// Check whether the form is being submitted via AJAX
if(isset($_POST['submit_ajax']) && $_POST['submit_ajax']) {
	// Include named constants
	require_once dirname(dirname(__DIR__)).'/includes/constants.php';
	
	// Include debugging functions
	require_once DEBUG_FUNC;
	
	// Include the database configuration
	require_once DB_CONFIG;
	
	// Include the Query class
	require_once QUERY_CLASS;
	
	// Create a Query object
	$rs_query = new Query;
	
	// Include global functions
	require_once GLOBAL_FUNC;
	
	// Include admin functions
	require_once ADMIN_FUNC;
	
	// Include the database schema
	require_once DB_SCHEMA;
	
	// Run the installation
	$result = runInstall($_POST);
	
	// Display the result
	echo implode(';', $result);
}

/**
 * Run the installation.
 * @since 1.2.6[b]
 *
 * @param array $data
 * @return array
 */
function runInstall($data): array {
	// Extend the Query object
	global $rs_query;
	
	// Get site title
	$data['site_title'] = !empty($data['site_title']) ? trim(strip_tags($data['site_title'])) : 'My Website';
	
	// Get username
	$data['username'] = isset($data['username']) ? trim(strip_tags($data['username'])) : '';
	
	// Get password
	$data['password'] = isset($data['password']) ? strip_tags($data['password']) : '';
	
	// Get admin email
	$data['admin_email'] = isset($data['admin_email']) ? trim(strip_tags($data['admin_email'])) : '';
	
	// Get search engine visibility (visible by default)
	$data['do_robots'] = isset($data['do_robots']) ? (int)$data['do_robots'] : 1;
	
	// Validate input data
	if(empty($data['username'])) {
		// Return an error (username field is empty)
		return array(true, 'You must provide a username.');
	} elseif(strlen($data['username']) < UN_LENGTH) {
		// Return an error (username is too short)
		return array(true, 'Username must be at least '.UN_LENGTH.' characters long.');
	} elseif(empty($data['password'])) {
		// Return an error (password field is empty)
		return array(true, 'You must provide a password.');
	} elseif(strlen($data['password']) < PW_LENGTH) {
		// Return an error (password is too short)
		return array(true, 'Password must be at least '.PW_LENGTH.' characters long.');
	} elseif(empty($data['admin_email'])) {
		// Return an error (email field is empty)
		return array(true, 'You must provide an email.');
	}
	
	// Fetch the database schema
	$schema = dbSchema();
	
	// Create the tables
	foreach($schema as $table) $rs_query->doQuery($table);
	
	// Fetch the user data
	$user_data = array(
		'username' => $data['username'],
		'password' => $data['password'],
		'email' => $data['admin_email']
	);
	
	// Fetch the site's url
	$site_url = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
	
	// Fetch the settings data
	$settings_data = array(
		'site_title' => $data['site_title'],
		'site_url' => $site_url,
		'admin_email' => $data['admin_email'],
		'do_robots' => $data['do_robots']
	);
	
	// Populate the database tables
	populateTables($user_data, $settings_data);
	
	// Make sure that the home directory can be written to
	if(is_writable(PATH)) {
		// File path for robots.txt
		$file_path = PATH.'/robots.txt';
		
		// Open the file stream
		$handle = fopen($file_path, 'w');
		
		// Address all user-agents (robots)
		fwrite($handle, 'User-agent: *'.chr(10));
		
		// Check whether robots are being blocked
		if((int)$data['do_robots'] === 0) {
			// Block robots from crawling the site
			fwrite($handle, 'Disallow: /');
		} else {
			// Allow crawling to all directories except for /admin/
			fwrite($handle, 'Disallow: /admin/');
		}
		
		// Close the file
		fclose($handle);
		
		// Set file permissions
		chmod($file_path, 0666);
	}
	
	// Return a success message
	return array(false, '<p>The database has successfully been installed! You are now ready to start using your website.</p><div class="button-wrap centered"><a class="button" href="/login.php">Log In</a></div>');
}