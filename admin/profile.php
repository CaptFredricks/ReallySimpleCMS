<?php
// Include the header
require_once __DIR__.'/header.php';

// Fetch the user's session id
$id = $session['id'];

// Create a Profile object
$rs_profile = new Profile($id);
?>
<div class="content">
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
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';