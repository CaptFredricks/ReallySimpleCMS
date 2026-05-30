<?php
/**
 * Generate a database configuration file.
 * @since 1.3.0-alpha
 *
 * @package ReallySimpleCMS
 */

require_once dirname(__DIR__) . '/includes/constants.php';
require_once RS_CRIT_FUNC;

checkPHPSetup();
checkDBConfig(); // Hiding this will bypass config checks, allowing for debugging

$step = (int)($_GET['step'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo RS_ENGINE; ?> Config Setup</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<link href="<?php echo STYLES . '/global.min.css'; ?>" rel="stylesheet">
		<link href="<?php echo STYLES . '/button.min.css'; ?>" rel="stylesheet">
		<link href="<?php echo STYLES . '/setup.min.css'; ?>" rel="stylesheet">
	</head>
	<body class="rscms-config">
		<div class="wrapper">
			<h1><?php echo RS_ENGINE; ?></h1>
			<main class="content" role="main">
				<?php
				switch($step) {
					case 0:
						?>
						<p>Welcome to the <?php echo RS_ENGINE; ?>! To complete the installation and start building your site, you will need to provide the following information. All of this can be obtained from your hosting provider.</p>
						<ol>
							<li>Database name</li>
							<li>Database username</li>
							<li>Database password</li>
							<li>Database host</li>
						</ol>
						<p>This data will be used to construct a configuration file so that <?php echo RS_ENGINE; ?> can connect to your database. If you'd like to complete this task manually, copy the code in the <code>default-config.php</code> (located in the <code>setup</code> directory) and create a new file called <code>config.php</code> (place it in the <code>root</code> directory). Then, replace all sample data with the appropriate information.</p>
						<div class="button-wrap"><a class="button" href="?step=1">Begin setup</a></div>
						<?php
						break;
					case 1:
						?>
						<p>Enter your database information in the form below. If <code>localhost</code> doesn't work, contact your hosting provider.</p>
						<form class="data-form" action="?step=2" method="post">
							<table class="form-table">
								<tr>
									<th><label for="db-name">Database Name</label></th>
									<td><input type="text" id="db-name" name="db_name" autofocus></td>
								</tr>
								<tr>
									<th><label for="db-user">Database Username</label></th>
									<td><input type="text" id="db-user" name="db_user"></td>
								</tr>
								<tr>
									<th><label for="db-pass">Database Password</label></th>
									<td><input type="text" id="db-pass" name="db_pass" autocomplete="off"></td>
								</tr>
								<tr>
									<th><label for="db-host">Database Host</label></th>
									<td><input type="text" id="db-host" name="db_host" value="localhost"></td>
								</tr>
							</table>
							<input type="submit" class="button" name="submit">
						</form>
						<?php
						break;
					case 2:
						requireFile(RS_DEBUG_FUNC);
						
						if(isset($_POST['submit'])) {
							define('DB_NAME', trim(strip_tags($_POST['db_name'])));
							define('DB_USER', trim(strip_tags($_POST['db_user'])));
							define('DB_PASS', trim(strip_tags($_POST['db_pass'])));
							define('DB_HOST', trim(strip_tags($_POST['db_host'])));
							define('DB_CHARSET', 'utf8');
							define('DB_COLLATE', '');
							
							$rs_query = new \Engine\Query;
							
							// Stop execution if the database connection can't be established
							if(!$rs_query->conn_status) {
								?>
								<p><strong>Error!</strong> <?php echo RS_ENGINE; ?> could not connect to the database. Please return to the previous page and make sure all of the provided information is correct.</p>
								<div class="button-wrap"><a class="button" href="?step=1">Go Back</a></div>
								<?php
								exit;
							}
							
							$config_file = file(PATH . SETUP . '/default-config.php');
							
							if($config_file) {
								foreach($config_file as $line_num => $line) {
									// Skip over unmatched lines
									if(!preg_match('/^define\(\s*\'(DB_[A-Z_]+)\'/', $line, $match)) continue;
									
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
											if($rs_query->charset === 'utf8mb4' || (!$rs_query->charset &&
												$rs_query->hasCap('utf8mb4'))
											) {
												$config_file[$line_num] = "define('" . $constant . "', '" .
													"utf8mb4');" . chr(10);
											}
											break;
									}
								}
								
								unset($line);
								
								if(!is_writable(PATH)) {
									?>
									<p><strong>Error!</strong> The <code>config.php</code> file cannot be created. Write permissions may be disabled on your server.</p>
									<p>If that's the case, just copy the code below and create <code>config.php</code> in the <code>root</code> directory of <?php echo RS_ENGINE; ?>.</p>
									<?php
									$text = '';
									
									foreach($config_file as $line)
										$text .= htmlentities($line, ENT_COMPAT, 'UTF-8');
									?>
									<textarea class="no-resize" rows="15" readonly><?php echo $text; ?></textarea>
									<p>Once you're done, you can run the installation.</p>
									<div class="button-wrap"><a class="button" href="rsdb-install.php">Run installation</a></div>
									<?php
								} else {
									$handle = fopen(RS_CONFIG, 'w');
									
									foreach($config_file as $line) fwrite($handle, $line);
									
									fclose($handle);
									
									// Set file permissions
									chmod(RS_CONFIG, 0666);
									?>
									<p>The <code>config.php</code> file was successfully created! The database connection is all set up. You may now proceed with the installation.</p>
									<div class="button-wrap"><a class="button" href="rsdb-install.php">Run installation</a></div>
									<?php
								}
							} else {
								?>
								<p>The default config file could not be found. Your copy of <?php echo RS_ENGINE; ?> may be corrupted. Please download the appropriate package and try again.</p>
								<div class="button-wrap"><a class="button" href="?step=1">Go Back</a></div>
								<?php
							}
						}
						break;
				}
				?>
			</main>
			<p class="powered-by">Powered by <?php echo RS_DEVELOPER; ?></p>
		</div>
	</body>
</html>