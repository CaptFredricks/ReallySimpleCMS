<?php
/**
 * Log in to the admin dashboard.
 * @since 1.3.3[a]
 */

// Include the initialization file
require_once __DIR__.'/init.php';

// Include functions
require_once PATH.INC.'/functions.php';

// Start output buffering
ob_start();

// Start the session
session_start();

// Create a Login object
$rs_login = new Login;

// Fetch the current action
$action = $_GET['action'] ?? '';
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo empty($action) ? 'Log In' : ucwords(str_replace('_', ' ', $action)); ?> &rtrif; <?php getSetting('site_title'); ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<meta name="theme-color" content="<?php getSetting('theme_color'); ?>">
		<link type="image/x-icon" href="<?php echo getMediaSrc(getSetting('site_icon', false)); ?>" rel="icon">
		<?php getStylesheet('button.min.css'); ?>
		<?php getStylesheet('style.css'); ?>
		<?php getStylesheet('font-awesome.min.css', '5.12.0'); ?>
		<?php getScript('jquery.min.js', '3.4.1'); ?>
	</head>
	<body class="login">
		<div class="wrapper">
			<h1>
				<a href="/">
					<?php
					// Check whether a site logo has been set
					if(!empty(getSetting('site_logo', false))) {
						// Display the site logo
						?>
						<img src="<?php echo getMediaSrc(getSetting('site_logo', false)); ?>" title="<?php getSetting('site_title'); ?>">
						<?php
					} else {
						// Display the site title
						getSetting('site_title');
					}
					?>
				</a>
			</h1>
			<?php
			switch($action) {
				case 'logout':
					// Log the user out if the session cookie is set, otherwise redirect them to the login form
					isset($_COOKIE['session']) ? $rs_login->userLogout($_COOKIE['session']) : redirect('login.php');
					break;
				case 'forgot_password':
					// Display the 'Forgot Password' form
					$rs_login->forgotPasswordForm();
					break;
				case 'reset_password':
					// Display the 'Reset Password' form
					$rs_login->resetPasswordForm();
					break;
				default:
					// Display the 'Log In' form
					$rs_login->logInForm();
			}
			?>
		</div>
		<?php getScript('script.js'); ?>
	</body>
</html>
<?php
// End output buffering
ob_end_flush();