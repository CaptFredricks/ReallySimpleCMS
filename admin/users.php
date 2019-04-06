<?php
// Include header
require_once __DIR__.'/header.php';

// Create a User object
$rs_user = new User;
?>
<div class="wrapper">
	<?php
	// Get the current action
	$action = $_GET['action'] ?? '';
	
	// Get the user id
	$id = (int)($_GET['id'] ?? 0);
	
	switch($action) {
		case 'create':
			// Create a new user
			$rs_user->createEntry();
			break;
		case 'edit':
			// Edit an existing user
			$rs_user->editEntry($id);
			break;
		case 'delete':
			// Delete an existing user
			$rs_user->deleteEntry($id);
			break;
		case 'reset_password':
			// Reset a user's password
			$rs_user->resetPassword($id);
			break;
		default:
			// List all users
			$rs_user->listEntries();
	}
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';