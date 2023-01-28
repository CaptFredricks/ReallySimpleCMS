<?php
/**
 * Run the database installer.
 * @since 1.3.0[a]
 */

// Named constants
require_once dirname(__DIR__) . '/includes/constants.php';

// Make sure config file has been created already
if(!file_exists(DB_CONFIG)) header('Location: ' . ADMIN . '/setup.php');

// Debugging functions
require_once DEBUG_FUNC;

// Database configuration
require_once DB_CONFIG;

// Query class
require_once QUERY_CLASS;

// Create a Query object
$rs_query = new Query;

// Global functions
require_once GLOBAL_FUNC;

// Admin functions
require_once ADMIN_FUNC;

// Make sure the CMS isn't already installed
if($rs_query->conn_status) {
	// Database schema
	require_once DB_SCHEMA;
	
	$schema = dbSchema();
	$tables = $rs_query->showTables();
	
	if(!empty($tables)) {
		$installed = true;
		
		foreach($schema as $key => $value) {
			// Check whether the table exists in the database and create it if not
			if(!$rs_query->tableExists($key)) $rs_query->doQuery($schema[$key]);
		}
		
		exit(CMS_NAME . ' is already installed!');
	}
}

// AJAX installation file
require_once PATH . ADMIN . INC . '/run-install.php';

// Set the current step of the installation process
$step = (int)($_GET['step'] ?? 1);
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo CMS_NAME; ?> Installation</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<?php
		putStylesheet('button.min.css');
		adminStylesheet('install.min.css');
		putStylesheet('font-awesome.min.css', ICONS_VERSION);
		putStylesheet('font-awesome-rules.min.css');
		?>
	</head>
	<body>
		<h1><?php echo CMS_NAME; ?></h1>
		<div class="wrapper">
			<?php
			/**
			 * Display the installation form.
			 * @since 1.3.0[a]
			 *
			 * @param string $error (optional; default: null)
			 */
			function displayInstallForm($error = null): void {
				?>
				<p>You're almost ready to begin using the <?php echo CMS_NAME; ?>. Fill in the form below to proceed with the installation.</p>
				<p>All of the settings below can be changed at a later date. They're required in order to set up the CMS, though.</p>
				<?php
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
					echo '<p class="status-message failure">' . $error . '</p>';
				?>
				<form class="data-form" action="?step=2" method="post">
					<table class="form-table">
						<tr>
							<th><label for="site_title">Site Title</label></th>
							<td><input type="text" id="site-title" name="site_title" value="<?php echo $site_title; ?>" autofocus></td>
						</tr>
						<tr>
							<th><label for="username">Username</label></th>
							<td><input type="text" id="username" name="username" value="<?php echo $username; ?>"></td>
						</tr>
						<tr>
							<th><label for="password">Password</label></th>
							<td><input type="text" id="password" name="password" value="<?php echo generatePassword(); ?>" autocomplete="off"></td>
						</tr>
						<tr>
							<th><label for="admin_email">Email</label></th>
							<td><input type="email" id="admin-email" name="admin_email" value="<?php echo $admin_email; ?>"></td>
						</tr>
						<tr>
							<th><label for="do_robots">Search Engine Visibility</label></th>
							<td><label class="checkbox-label"><input type="checkbox" id="do-robots" name="do_robots" value="0"> <span>Discourage search engines from indexing this site</span></label></td>
						</tr>
					</table>
					<input type="hidden" id="submit-ajax" name="submit_ajax" value="0">
					<div class="button-wrap">
						<input type="submit" class="button" name="submit" value="Install">
					</div>
				</form>
				<?php
			}
			
			switch($step) {
				case 1:
					// Show the installation form
					displayInstallForm();
					break;
				case 2:
					// Run the installation
					list($error, $message) = runInstall($_POST);
					
					// Check whether an error was returned
					if($error) {
						// Display the form with the appropriate error message
						displayInstallForm($message);
					} else {
						// Display the success message
						echo $message;
					}
					break;
			}
			?>
		</div>
		<?php
		putScript('jquery.min.js', JQUERY_VERSION);
		adminScript('install.min.js');
		?>
	</body>
</html>