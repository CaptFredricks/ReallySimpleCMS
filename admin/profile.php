<?php
/**
 * Admin profile page. Makes use of the Profile object.
 * @since 2.0.0-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$action = $_GET['action'] ?? '';

$rs_ad_profile = new \Admin\Profile($rs_session['id'], $action);
?>
<article class="content">
	<?php
	switch($action) {
		case 'reset_password':
			// Action: Reset Password
			$rs_ad_profile->resetPassword();
			break;
		default:
			// Action: Edit Profile
			$rs_ad_profile->editProfile();
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';