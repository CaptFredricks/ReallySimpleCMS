<?php
/**
 * Log in to the admin dashboard.
 * @since 1.3.3[a]
 */

// Include the initialization file
require_once __DIR__.'/init.php';

// Include functions
require_once PATH.INC.'/functions.php';

// Start the session
session_start();

// Create a Login object
$rs_login = new Login;

// Check whether the user is logging out and log them out if the session cookie is set
if(isset($_GET['action']) && $_GET['action'] === 'logout')
	isset($_COOKIE['session']) ? $rs_login->userLogout($_COOKIE['session']) : redirect('login.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php getSetting('site_title'); ?> &rtrif; Log In</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<?php getStylesheet('style.css'); ?>
		<?php getStylesheet('buttons.css'); ?>
	</head>
	<body class="login">
		<div class="wrapper">
			<h1><a href="/"><?php getSetting('site_title'); ?></a></h1>
			<?php $error = isset($_POST['submit']) ? $rs_login->userLogin($_POST) : ''; echo $error; ?>
			<form class="data-form" action="" method="post">
				<p><label for="username_email">Username or Email<br><input type="text" name="username_email" autofocus></label></p>
				<p><label for="password">Password<br><input type="password" name="password"></label></p>
				<p><label for="captcha">Captcha<br><input type="text" name="captcha"><img id="captcha" src="<?php echo INC.'/captcha.php'; ?>"></label></p>
				<p><label class="checkbox-label"><input type="checkbox" name="remember_login" value="0"> <span>Keep me logged in</span></label></p>
				<input type="submit" class="button" name="submit" value="Log In">
			</form>
		</div>
	</body>
</html>