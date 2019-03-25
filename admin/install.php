<?php
/**
 * Install the Really Simple CMS.
 * @since Alpha 1.3.0
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

// Include database configuration and functions
require_once PATH.ADMIN.INC.'/functions.php';

// Make sure Really Simple CMS isn't already installed
if($rs_query->conn_status) {
	$data = $rs_query->showTables();
	
	if(!empty($data)) exit('Really Simple CMS is already installed!');
}

// Set the current step of the installation process
$step = intval($_GET['step'] ?? 1);

/**
 * Run the installation.
 * @since Alpha 1.3.0
 *
 * @param array $data
 */
function runInstall($data) {
	global $rs_query;
	
	// Include the database schema
	require_once PATH.INC.'/schema.php';
	
	// Get database tables
	$tables = dbSchema();
	
	// Create the tables
	foreach($tables as $table)
		$rs_query->doQuery($table);
	
	// Get the settings data
	$settings = array('site_title'=>$data['site_title'], 'admin_email'=>$data['admin_email'], 'do_robots'=>$data['do_robots']);
	
	// Populate the settings table
	populateSettings($settings);
	
	// Get the user data
	$user = array('username'=>$data['username'], 'password'=>$data['password'], 'email'=>$data['admin_email']);
	
	// Populate the users table
	populateUsers($user);
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Really Simple CMS Installation</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<link rel="stylesheet" href="<?php echo ADMIN_STYLES.'/install.css'; ?>">
		<link rel="stylesheet" href="<?php echo STYLES.'/buttons.css'; ?>">
	</head>
	<body>
		<h1>Really Simple CMS</h1>
		<div class="wrapper">
<?php
/**
 * Display the installation form.
 * @since Alpha 1.3.0
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
	$do_robots = isset($_POST['do_robots']) ? intval($_POST['do_robots']) : 1;
	
	// Display any validation errors
	if(!is_null($error))
		echo '<p class="status-message failure">'.$error.'</p>';
	?>
	<form action="?step=2" method="post">
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
				<td><label for="do_robots"><input type="checkbox" name="do_robots" value="0"> <small>Discourage search engines from indexing this site</small></label></td>
			</tr>
		</table>
		<input type="submit" class="button" name="submit" value="Install">
	</form>
	<?php
}

switch($step) {
	case 1:
		?>
		<p>Congrats! You're almost ready to begin using the Really Simple CMS. Fill in the form below to proceed with the installation.</p>
		<p>All of the settings below can be changed at a later date. They're required in order to set up the CMS, though.</p>
		<?php
		displayInstallForm();
		break;
	case 2:
		// Get site title
		$data['site_title'] = isset($_POST['site_title']) ? (!empty($_POST['site_title']) ? trim(strip_tags($_POST['site_title'])): 'My Website') : '';

		// Get username
		$data['username'] = isset($_POST['username']) ? trim(strip_tags($_POST['username'])) : '';
		
		// Get password
		$data['password'] = isset($_POST['password']) ? strip_tags($_POST['password']) : '';

		// Get admin email
		$data['admin_email'] = isset($_POST['admin_email']) ? trim(strip_tags($_POST['admin_email'])) : '';

		// Get search engine visibility (visible by default)
		$data['do_robots'] = isset($_POST['do_robots']) ? intval($_POST['do_robots']) : 1;
		
		// Error flag
		$error = false;
		
		// Validate input data
		if(empty($data['username'])) {
			displayInstallForm('You must provide a username.');
			$error = true;
		} elseif(strlen($data['username']) < UN_LENTH) {
			displayInstallForm('Username must be at least '.UN_LENTH.' characters long.');
			$error = true;
		} elseif(empty($data['password'])) {
			displayInstallForm('You must provide a password.');
			$error = true;
		} elseif(strlen($data['password']) < PW_LENGTH) {
			displayInstallForm('Password must be at least '.PW_LENGTH.' characters long.');
			$error = true;
		} elseif(empty($data['admin_email'])) {
			displayInstallForm('You must provide an email.');
			$error = true;
		}
		
		// If no errors are present, install the CMS
		if($error === false) {
			runInstall($data);
			?>
			<p>The Really Simple CMS has successfully been installed! You are now ready to start using your website.</p>
			<div><a class="button" href="/login.php">Log In</a></div>
			<?php
		}
		break;
}
?>
		</div>
	</body>
</html>