<?php
/**
 * Set up the basic CMS configuration.
 * @since 1.3.0[a]
 */

// Named constants
require_once dirname(__DIR__) . '/includes/constants.php';

// Critical functions
require_once CRIT_FUNC;

checkPHPVersion();

// Make sure a config file doesn't already exist (this will be created in a moment)
if(file_exists(DB_CONFIG)) exit('A configuration file already exists. If you wish to continue installation, please delete the existing file.');

// Path to the admin stylesheets directory
if(!defined('ADMIN_STYLES')) define('ADMIN_STYLES', ADMIN . INC . '/css');

// The current step of the setup process
$step = (int)($_GET['step'] ?? 0);
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo CMS_ENGINE; ?> Setup</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<link href="<?php echo STYLES . '/button.min.css'; ?>" rel="stylesheet">
		<link href="<?php echo ADMIN_STYLES . '/install.min.css'; ?>" rel="stylesheet">
	</head>
	<body>
		<h1><?php echo CMS_ENGINE; ?></h1>
		<div class="wrapper">
			<?php
			switch($step) {
				case 0:
					?>
					<p>Welcome to the <?php echo CMS_ENGINE; ?>! To install the CMS and start building your site, you will need to provide the following information. All of this can be obtained from your hosting provider.</p>
					<ol>
						<li>Database name</li>
						<li>Database username</li>
						<li>Database password</li>
						<li>Database host</li>
					</ol>
					<p>This data will be used to construct a configuration file so that <?php echo CMS_ENGINE; ?> can connect to your database. If you'd like to complete this task manually, copy the code in the <code>config-setup.php</code> (located in the <code>includes</code> directory) and create a new file called <code>config.php</code> (place it in the <code>root</code> directory). Then, replace all sample data with the appropriate information.</p>
					<div class="button-wrap"><a class="button" href="?step=1">Begin setup</a></div>
					<?php
					break;
				case 1:
					?>
					<p>Enter your database information in the form below.</p>
					<p>If <code>localhost</code> doesn't work, contact your hosting provider.</p>
					<form class="data-form" action="?step=2" method="post">
						<table class="form-table">
							<tr>
								<th><label for="db_name">Database Name</label></th>
								<td><input type="text" name="db_name" autofocus></td>
							</tr>
							<tr>
								<th><label for="db_user">Database Username</label></th>
								<td><input type="text" name="db_user"></td>
							</tr>
							<tr>
								<th><label for="db_pass">Database Password</label></th>
								<td><input type="text" name="db_pass" autocomplete="off"></td>
							</tr>
							<tr>
								<th><label for="db_host">Database Host</label></th>
								<td><input type="text" name="db_host" value="localhost"></td>
							</tr>
						</table>
						<input type="submit" class="button" name="submit">
					</form>
					<?php
					break;
				case 2:
					// Debugging functions
					require_once DEBUG_FUNC;
					
					$db_name = $db_user = $db_pass = $db_host = '';
					
					if(isset($_POST['submit'])) {
						$db_name = trim(strip_tags($_POST['db_name']));
						$db_user = trim(strip_tags($_POST['db_user']));
						$db_pass = trim(strip_tags($_POST['db_pass']));
						$db_host = trim(strip_tags($_POST['db_host']));
					}
					
					// Create database connection constants
					define('DB_NAME', $db_name);
					define('DB_USER', $db_user);
					define('DB_PASS', $db_pass);
					define('DB_HOST', $db_host);
					
					$rs_query = new Query;
					
					// Stop execution if the database connection can't be established
					if(!$rs_query->conn_status) {
						?>
						<p><strong>Error!</strong> <?php echo CMS_ENGINE; ?> could not connect to the database. Please return to the previous page and make sure all of the provided information is correct.</p>
						<div class="button-wrap"><a class="button" href="?step=1">Go Back</a></div>
						<?php
						exit;
					}
					
					$config_file = file(PATH . INC . '/config-setup.php');
					
					foreach($config_file as $line_num => $line) {
						// Skip over unmatched lines
						if(!preg_match('/^define\(\s*\'([A-Z_]+)\'/', $line, $match)) continue;
						
						$constant = $match[1];
						
						// Replace the sample text
						switch($constant) {
							case 'DB_NAME':
							case 'DB_USER':
							case 'DB_PASS':
							case 'DB_HOST':
								$config_file[$line_num] = "define('" . $constant . "', '" .
									addcslashes(constant($constant), "\\'") . "');" . chr(10);
								break;
							case 'DB_CHARSET':
								if($rs_query->charset === 'utf8mb4' || (!$rs_query->charset && $rs_query->hasCap('utf8mb4')))
									$config_file[$line_num] = "define('" . $constant . "', '" .
										"utf8mb4');" . chr(10);
								break;
						}
					}
					
					unset($line);
					
					// Make sure the home directory can be written to
					if(!is_writable(PATH)) {
						?>
						<p><strong>Error!</strong> The <code>config.php</code> file cannot be created. Write permissions may be disabled on your server.</p>
						<p>If that's the case, just copy the code below and create <code>config.php</code> in the <code>root</code> directory of <?php echo CMS_ENGINE; ?>.</p>
						<?php
						$text = '';
						
						foreach($config_file as $line)
							$text .= htmlentities($line, ENT_COMPAT, 'UTF-8');
						?>
						<textarea class="no-resize" rows="15" readonly><?php echo $text; ?></textarea>
						<p>Once you're done, you can run the installation.</p>
						<div class="button-wrap"><a class="button" href="install.php?step=1">Run installation</a></div>
						<?php
					} else {
						// Open the file stream
						$handle = fopen(DB_CONFIG, 'w');
						
						// Write to the file
						if($handle !== false) {
							foreach($config_file as $line) fwrite($handle, $line);
							
							fclose($handle);
						}
						
						// Set file permissions
						chmod(DB_CONFIG, 0666);
						
						if($handle !== false) {
							?>
							<p>The <code>config.php</code> file was successfully created! The database connection is all set up. You may now proceed with the installation.</p>
							<div class="button-wrap"><a class="button" href="install.php?step=1">Run installation</a></div>
							<?php
						}
					}
					break;
			}
			?>
		</div>
	</body>
</html>