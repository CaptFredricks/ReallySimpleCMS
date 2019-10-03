<?php
/**
 * Install the ReallySimpleCMS.
 * @since 1.3.0[a]
 */

// Absolute path to the root directory
if(!defined('PATH')) define('PATH', dirname(__DIR__));

// Path to the includes directory
if(!defined('INC')) define('INC', '/includes');

// Path to the admin directory
if(!defined('ADMIN')) define('ADMIN', '/admin');

// Make sure config file has been created already (if not, redirect to the setup page)
if(!file_exists(PATH.INC.'/config.php')) header('Location: '.ADMIN.'/setup.php');

// Path to the stylesheets directory
if(!defined('STYLES')) define('STYLES', INC.'/css');

// Path to the admin stylesheets directory
if(!defined('ADMIN_STYLES')) define('ADMIN_STYLES', ADMIN.INC.'/css');

// Minimum username length
const UN_LENTH = 4;

// Minimum password length
const PW_LENGTH = 8;

// Include debugging functions
require_once PATH.INC.'/debug.php';

// Include database configuration
require_once PATH.INC.'/config.php';

// Include Query class
require_once PATH.INC.'/class-query.php';

// Create a Query object
$rs_query = new Query;

// Include database configuration and functions
require_once PATH.ADMIN.INC.'/functions.php';

// Make sure ReallySimpleCMS isn't already installed
if($rs_query->conn_status) {
	// Include the database schema
	require_once PATH.INC.'/schema.php';
	
	// Fetch the database schema tables
	$tables = dbSchema();
	
	// Get a list of tables in the database
	$data = $rs_query->showTables();
	
	// Check whether or not there are tables in the database
	if(!empty($data) && count($data) >= count($tables)) {
		// Warn the user that the database is already installed
		exit('ReallySimpleCMS is already installed!');
	} else {
		// Loop through the schema tables
		for($i = 0; $i < count($tables); $i++) {
			// Drop each existing table from the database
			$rs_query->doQuery("DROP TABLE `".key($tables)."`");
			
			// Move the array pointer to the next element
			next($tables);
		}
	}
}

// Set the current step of the installation process
$step = (int)($_GET['step'] ?? 1);

/**
 * Run the installation.
 * @since 1.3.0[a]
 *
 * @param array $data
 * @return null
 */
function runInstall($data) {
	// Extend the Query class
	global $rs_query;
	
	// Fetch the database schema tables
	$tables = dbSchema();
	
	// Create the tables
	foreach($tables as $table) $rs_query->doQuery($table);
	
	// Fetch the user data
	$user_data = array('username'=>$data['username'], 'password'=>$data['password'], 'email'=>$data['admin_email']);
	
	// Fetch the site's url
	$site_url = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
	
	// Fetch the settings data
	$settings_data = array('site_title'=>$data['site_title'], 'site_url'=>$site_url, 'admin_email'=>$data['admin_email'], 'do_robots'=>$data['do_robots']);
	
	// Populate the database tables
	populateTables($user_data, $settings_data);
	
	// Make sure that the home directory can be written to
	if(is_writable(PATH)) {
		// File path for robots.txt
		$file_path = PATH.'/robots.txt';
		
		// Open file stream
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
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>ReallySimpleCMS Installation</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<link rel="stylesheet" href="<?php echo ADMIN_STYLES; ?>/install.css">
		<link rel="stylesheet" href="<?php echo STYLES; ?>/buttons.css">
	</head>
	<body>
		<h1>ReallySimpleCMS</h1>
		<div class="wrapper">
			<?php
			/**
			 * Display the installation form.
			 * @since 1.3.0[a]
			 *
			 * @param string $error (optional; default: null)
			 * @return null
			 */
			function displayInstallForm($error = null) {
				// Validate site title
				$site_title = isset($_POST['site_title']) ? trim(strip_tags($_POST['site_title'])) : '';

				// Validate username
				$username = isset($_POST['username']) ? trim(strip_tags($_POST['username'])) : '';

				// Validate admin email
				$admin_email = isset($_POST['admin_email']) ? trim(strip_tags($_POST['admin_email'])) : '';

				// Validate search engine visibility (visible by default)
				$do_robots = isset($_POST['do_robots']) ? (int)$_POST['do_robots'] : 1;
				
				// Display any validation errors
				if(!is_null($error))
					echo '<p class="status-message failure">'.$error.'</p>';
				?>
				<form class="data-form" action="?step=2" method="post">
					<table class="form-table">
						<tr>
							<th><label for="site_title">Site Title</label></th>
							<td><input type="text" name="site_title" value="<?php echo $site_title; ?>" autofocus></td>
						</tr>
						<tr>
							<th><label for="username">Username</label></th>
							<td><input type="text" name="username" value="<?php echo $username; ?>"></td>
						</tr>
						<tr>
							<th><label for="password">Password</label></th>
							<td><input type="text" name="password" value="<?php echo generatePassword(); ?>" autocomplete="off"></td>
						</tr>
						<tr>
							<th><label for="admin_email">Email</label></th>
							<td><input type="email" name="admin_email" value="<?php echo $admin_email; ?>"></td>
						</tr>
						<tr>
							<th><label for="do_robots">Search Engine Visibility</label></th>
							<td><label class="checkbox-label"><input type="checkbox" name="do_robots" value="0"> <span>Discourage search engines from indexing this site</span></label></td>
						</tr>
					</table>
					<input type="submit" class="button" name="submit" value="Install">
				</form>
				<?php
			}

			switch($step) {
				case 1:
					?>
					<p>You're almost ready to begin using the ReallySimpleCMS. Fill in the form below to proceed with the installation.</p>
					<p>All of the settings below can be changed at a later date. They're required in order to set up the CMS, though.</p>
					<?php
					// Show the installation form
					displayInstallForm();
					break;
				case 2:
					// Get site title
					$data['site_title'] = isset($_POST['site_title']) ? (!empty($_POST['site_title']) ? trim(strip_tags($_POST['site_title'])) : 'My Website') : '';

					// Get username
					$data['username'] = isset($_POST['username']) ? trim(strip_tags($_POST['username'])) : '';
					
					// Get password
					$data['password'] = isset($_POST['password']) ? strip_tags($_POST['password']) : '';

					// Get admin email
					$data['admin_email'] = isset($_POST['admin_email']) ? trim(strip_tags($_POST['admin_email'])) : '';

					// Get search engine visibility (visible by default)
					$data['do_robots'] = isset($_POST['do_robots']) ? (int)$_POST['do_robots'] : 1;
					
					// Set the error flag to false
					$error = false;
					
					// Validate input data
					if(empty($data['username'])) {
						// Show the installation form with the appropriate error
						displayInstallForm('You must provide a username.');
						
						// Set the error flag to true
						$error = true;
					} elseif(strlen($data['username']) < UN_LENTH) {
						// Show the installation form with the appropriate error
						displayInstallForm('Username must be at least '.UN_LENTH.' characters long.');
						
						// Set the error flag to true
						$error = true;
					} elseif(empty($data['password'])) {
						// Show the installation form with the appropriate error
						displayInstallForm('You must provide a password.');
						
						// Set the error flag to true
						$error = true;
					} elseif(strlen($data['password']) < PW_LENGTH) {
						// Show the installation form with the appropriate error
						displayInstallForm('Password must be at least '.PW_LENGTH.' characters long.');
						
						// Set the error flag to true
						$error = true;
					} elseif(empty($data['admin_email'])) {
						// Show the installation form with the appropriate error
						displayInstallForm('You must provide an email.');
						
						// Set the error flag to true
						$error = true;
					}
					
					// If no errors are present, install the CMS
					if($error === false) {
						runInstall($data);
						?>
						<p>The ReallySimpleCMS has successfully been installed! You are now ready to start using your website.</p>
						<div><a class="button" href="/login.php">Log In</a></div>
						<?php
					}
					break;
			}
			?>
		</div>
	</body>
</html>