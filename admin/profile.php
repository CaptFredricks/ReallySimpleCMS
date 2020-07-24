<?php
// Include the header
require_once __DIR__.'/header.php';

// Create a Profile object
$rs_profile = new Profile;
?>
<div class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the user's session id
	$id = $session['id'];
	
	switch($action) {
		case 'reset_password':
			// Reset password
			$rs_profile->resetPassword($id);
			break;
		default:
			// Edit profile
			$rs_profile->editProfile($id);
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';