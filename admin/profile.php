<?php
/**
 * Admin profile page.
 * @since 2.0.0[a]
 */
require_once __DIR__ . '/header.php';

// Create a Profile object
$rs_profile = new Profile($session['id']);
?>
<article class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'reset_password':
			// Reset password
			$rs_profile->resetPassword();
			break;
		default:
			// Edit profile
			$rs_profile->editProfile();
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';