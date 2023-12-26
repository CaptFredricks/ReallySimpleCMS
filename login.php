<?php
/**
 * Log in to the admin dashboard.
 * @since 1.3.3-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/init.php';
require_once FUNC;

ob_start();
session_start();

$rs_login = new \Engine\Login;
$action = $_GET['action'] ?? '';
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo empty($action) ? 'Log In' : ucwords(str_replace('_', ' ', $action)); ?> ▸ <?php putSetting('site_title'); ?></title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<meta name="theme-color" content="<?php putSetting('theme_color'); ?>">
		<link type="image/x-icon" href="<?php echo getMediaSrc(getSetting('site_icon')); ?>" rel="icon">
		<?php headerScripts(); ?>
	</head>
	<body class="login">
		<div class="wrapper">
			<h1>
				<a href="/">
					<?php
					// Title/logo
					if(!empty(getSetting('site_logo'))) {
						echo domTag('img', array(
							'src' => getMediaSrc(getSetting('site_logo')),
							'title' => getSetting('site_title')
						));
					} else {
						putSetting('site_title');
					}
					?>
				</a>
			</h1>
			<?php
			switch($action) {
				case 'logout':
					$login_slug = getSetting('login_slug');
					
					// Log the user out if the session cookie is set
					// Otherwise, redirect them to the login form
					isset($_COOKIE['session']) ? $rs_login->userLogout($_COOKIE['session']) :
						redirect('/login.php' . (!empty($login_slug) ? '?secure_login=' . $login_slug : ''));
					break;
				case 'forgot_password':
					$rs_login->forgotPasswordForm();
					break;
				case 'reset_password':
					$rs_login->resetPasswordForm();
					break;
				default:
					$rs_login->logInForm();
			}
			?>
		</div>
		<?php footerScripts(); ?>
	</body>
</html>
<?php
ob_end_flush();