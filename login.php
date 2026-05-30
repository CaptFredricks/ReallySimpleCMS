<?php
/**
 * Log in to the admin dashboard.
 * @since 1.3.3-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/init.php';
requireFile(RS_FRONT_FUNC);

ob_start();
session_start();

$rs_login = new \Engine\Login;
$action = $_GET['action'] ?? '';
?>
<!DOCTYPE html>
<html>
	<head>
		<?php
		domTagPr('title', array(
			'content' => (empty($action) ? 'Log In' : capitalize($action)) . ' ▸ ' . getSetting('site_title')
		));
		
		domTagPr('meta', array(
			'charset' => 'UTF-8'
		));
		
		domTagPr('meta', array(
			'name' => 'viewport',
			'content' => 'width=device-width, initial-scale=1.0'
		));
		
		domTagPr('meta', array(
			'name' => 'robots',
			'content' => 'noindex, nofollow'
		));
		
		domTagPr('meta', array(
			'name' => 'theme-color',
			'content' => getSetting('theme_color')
		));
		
		domTagPr('link', array(
			'type'=> 'image/x-icon',
			'href' => getMediaSrc(getSetting('site_icon')),
			'rel' => 'icon'
		));
		
		headerScripts();
		?>
	</head>
	<body class="login">
		<div class="wrapper">
			<?php
			// Title/logo
			domTagPr('h1', array(
				'content' => domTag('a', array(
					'href' => '/',
					'content' => (!empty(getSetting('site_logo')) ?
						domTag('img', array(
							'src' => getMediaSrc(getSetting('site_logo')),
							'title' => getSetting('site_title')
						)) : getSetting('site_title')
					)
				))
			));
			
			switch($action) {
				case 'logout':
					// Action: Log Out
					$login_slug = getSetting('login_slug');
					
					// Log the user out if the session cookie is set
					// Otherwise, redirect them to the login form
					isset($_COOKIE['session']) ? $rs_login->userLogout($_COOKIE['session']) :
						redirect('/login.php' . (!empty($login_slug) ? '?secure_login=' . $login_slug : ''));
					break;
				case 'forgot_password':
					// Action: Forgot Password
					$rs_login->forgotPasswordForm();
					break;
				case 'reset_password':
					// Action: Reset Password
					$rs_login->resetPasswordForm();
					break;
				default:
					// Action: Log In
					$rs_login->logInForm();
			}
			?>
		</div>
		<?php footerScripts(); ?>
	</body>
</html>
<?php
ob_end_flush();