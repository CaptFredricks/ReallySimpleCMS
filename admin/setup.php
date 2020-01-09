<?php
/**
 * Set up the ReallySimpleCMS.
 * @since 1.3.0[a]
 */

// Absolute path to the root directory
if(!defined('PATH')) define('PATH', dirname(__DIR__));

// Path to the includes directory
if(!defined('INC')) define('INC', '/includes');

// Path to the admin directory
if(!defined('ADMIN')) define('ADMIN', '/admin');

// Path to the stylesheets directory
if(!defined('STYLES')) define('STYLES', INC.'/css');

// Path to the admin stylesheets directory
if(!defined('ADMIN_STYLES')) define('ADMIN_STYLES', ADMIN.INC.'/css');

// Get data from the config setup file
$config_file = file(PATH.INC.'/config-setup.php');

// Make sure config file doesn't already exist (this will be created in a moment)
if(file_exists(PATH.INC.'/config.php')) exit('A configuration file already exists. If you wish to continue installation, please delete the existing file.');

// Set the current step of the setup process
$step = (int)($_GET['step'] ?? 0);
?>
<!DOCTYPE html>
<html>
	<head>
		<title>ReallySimpleCMS Setup</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<link href="<?php echo ADMIN_STYLES.'/install.min.css'; ?>" rel="stylesheet">
		<link href="<?php echo STYLES.'/button.min.css'; ?>" rel="stylesheet">
	</head>
	<body>
		<h1>ReallySimpleCMS</h1>
		<div class="wrapper">
			<?php
			switch($step) {
				case 0:
					?>
					<p>Welcome to the ReallySimpleCMS! To install the CMS and start building your site, you will need to provide the following information. All of this can be obtained from your hosting provider.</p>
					<ol>
						<li>Database name</li>
						<li>Database username</li>
						<li>Database password</li>
						<li>Database host</li>
					</ol>
					<p>This data will be used to construct a configuration file so that ReallySimpleCMS can connect to your database. If you'd like to complete this task manually, copy the code in the <code>config-setup.php</code> (located in the <code>includes</code> directory) and create a new file called <code>config.php</code> (place it in the same directory). Then, replace all sample data with the appropriate information.</p>
					<div><a class="button" href="?step=1">Begin installation</a></div>
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
								<td><input type="text" name="db_name" placeholder="" autofocus></td>
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
					// Create database connection constants
					define('DB_NAME', trim(strip_tags($_POST['db_name'])));
					define('DB_USER', trim(strip_tags($_POST['db_user'])));
					define('DB_PASS', trim(strip_tags($_POST['db_pass'])));
					define('DB_HOST', trim(strip_tags($_POST['db_host'])));
					define('DB_CHAR', 'utf8');
					
					// Include debugging functions
					require_once PATH.INC.'/debug.php';
					
					// Include query class
					require_once PATH.INC.'/class-query.php';
					
					// Create a Query object
					$rs_query = new Query;
					
					// Stop execution if the database connection can't be established
					if(!$rs_query->conn_status) {
						?>
						<p><strong>Error!</strong> ReallySimpleCMS could not connect to the database. Please return to the previous page and make sure all the provided information is correct.</p>
						<div><a class="button" href="?step=1">Go Back</a></div>
						<?php
						exit;
					}
					
					// Loop through the file
					foreach($config_file as $line_num=>$line) {
						// Skip over unmatched lines
						if(!preg_match('/^define\(\s*\'([A-Z_]+)\'/', $line, $match)) continue;
						
						// Matched constant names
						$constant = $match[1];
						
						// Replace the sample text with the provided values
						$config_file[$line_num] = "define('".$constant."', '".addcslashes(constant($constant), "\\'")."');".chr(10);
					}
					
					// Destroy the line variable
					unset($line);
					
					// Make sure the home directory can be written to
					if(!is_writable(PATH)) {
						?>
						<p><strong>Error!</strong> The <code>config.php</code> file cannot be created. Write permissions may be disabled on your server.</p>
						<p>If that's the case, just copy the code below and create <code>config.php</code> in the <code>includes</code> directory of ReallySimpleCMS.</p>
						<?php
						// Create an empty text
						$text = '';
						
						// Loop through the file
						foreach($config_file as $line)
							$text .= htmlentities($line, ENT_COMPAT, 'UTF-8');
						?>
						<textarea class="no-resize" rows="15" readonly><?php echo $text; ?></textarea>
						<p>Once you're done, you can run the installation.</p>
						<div><a class="button" href="install.php">Run installation</a></div>
						<?php
					} else {
						// File path for the config file
						$config_file_path = PATH.INC.'/config.php';
						
						// Open file stream
						$handle = fopen($config_file_path, 'w');
						
						// Write to the file
						foreach($config_file as $line)
							fwrite($handle, $line);
						
						// Close the file
						fclose($handle);
						
						// Set file permissions
						chmod($config_file_path, 0666);
						?>
						<p>The <code>config.php</code> file was successfully created! The database connection is all set up. You may now proceed with the installation.</p>
						<div><a class="button" href="install.php">Run installation</a></div>
						<?php
					}
					break;
			}
			?>
		</div>
	</body>
</html>