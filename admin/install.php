<?php
/**
 * Run the database installer.
 * @since 1.3.0[a]
 */

// Named constants
require_once dirname(__DIR__) . '/includes/constants.php';

// Critical functions
require_once CRIT_FUNC;

checkPHPVersion();

// Make sure config file has been created already
if(!file_exists(DB_CONFIG)) redirect(ADMIN . '/setup.php');

// Database configuration
require_once DB_CONFIG;

// Debugging functions
require_once DEBUG_FUNC;

// Global functions
require_once GLOBAL_FUNC;

$rs_query = new Query;

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
		
		exit(CMS_ENGINE . ' is already installed!');
	}
}

// Installation engine
require_once PATH . ADMIN . INC . '/run-install.php';

// The current step of the installation process
$step = (int)($_GET['step'] ?? 1);
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo CMS_ENGINE; ?> Installation</title>
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
		<h1><?php echo CMS_ENGINE; ?></h1>
		<div class="wrapper">
			<?php
			/**
			 * Display the installation form.
			 * @since 1.3.0[a]
			 *
			 * @param string|null $error (optional) -- Any errors to display.
			 */
			function displayInstallForm(?string $error = null): void {
				?>
				<p>You're almost ready to begin using the <?php echo CMS_ENGINE; ?>. Fill in the form below to proceed with the installation.</p>
				<p>All of the settings below can be changed at a later date. They're required in order to set up the CMS, though.</p>
				<?php
				$site_title = isset($_POST['site_title']) ?
					trim(strip_tags($_POST['site_title'])) : '';
				
				$username = isset($_POST['username']) ?
					trim(strip_tags($_POST['username'])) : '';
				
				$admin_email = isset($_POST['admin_email']) ?
					trim(strip_tags($_POST['admin_email'])) : '';
				
				$do_robots = isset($_POST['do_robots']) ? (int)$_POST['do_robots'] : 1;
				
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
					displayInstallForm();
					break;
				case 2:
					list($error, $message) = runInstall($_POST);
					
					if($error)
						displayInstallForm($message);
					else
						echo $message;
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