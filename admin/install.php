<?php
/**
 * Install the ReallySimpleCMS.
 * @since 1.3.0[a]
 */

// Include named constants
require_once dirname(__DIR__).'/includes/constants.php';

// Make sure config file has been created already (if not, redirect to the setup page)
if(!file_exists(PATH.'/config.php')) header('Location: '.ADMIN.'/setup.php');

// Include debugging functions
require_once PATH.INC.'/debug.php';

// Include database configuration
require_once PATH.'/config.php';

// Include Query class
require_once PATH.INC.'/class-query.php';

// Create a Query object
$rs_query = new Query;

// Include global functions
require_once PATH.INC.'/globals.php';

// Include database configuration and functions
require_once PATH.ADMIN.INC.'/functions.php';

// Make sure ReallySimpleCMS isn't already installed
if($rs_query->conn_status) {
	// Include the database schema
	require_once PATH.INC.'/schema.php';
	
	// Fetch the database schema
	$schema = dbSchema();
	
	// Get a list of tables in the database
	$tables = $rs_query->showTables();
	
	// Check whether the database is installed
	if(!empty($tables)) {
		// Create a flag for whether the database is installed
		$installed = true;
		
		// Loop through the schema
		foreach($schema as $key => $value) {
			// Check whether the table exists in the database and create it if not
			if(!$rs_query->tableExists($key)) $rs_query->doQuery($schema[$key]);
		}
		
		// Warn the user that the database is already installed
		exit('ReallySimpleCMS is already installed!');
	}
}

// Include the run install file
require_once PATH.ADMIN.INC.'/run-install.php';

// Set the current step of the installation process
$step = (int)($_GET['step'] ?? 1);
?>
<!DOCTYPE html>
<html>
	<head>
		<title>ReallySimpleCMS Installation</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<?php
		// Include stylesheets
		getStylesheet('button.min.css');
		getAdminStylesheet('install.min.css');
		getStylesheet('font-awesome.min.css', ICONS_VERSION);
		getStylesheet('font-awesome-rules.min.css');
		?>
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
				?>
				<p>You're almost ready to begin using the ReallySimpleCMS. Fill in the form below to proceed with the installation.</p>
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
					echo '<p class="status-message failure">'.$error.'</p>';
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
		// Include scripts
		getScript('jquery.min.js', JQUERY_VERSION);
		getAdminScript('install.min.js');
		?>
	</body>
</html>