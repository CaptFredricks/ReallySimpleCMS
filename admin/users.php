<?php
// Include header
require_once __DIR__.'/header.php';

// Create a User object
$rs_user = new User;
?>
<div class="wrapper clear">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the user id
	$id = (int)($_GET['id'] ?? 0);
	
	switch($action) {
		case 'create':
			// Create a new user
			userHasPrivilege($_SESSION['role'], 'can_create_users') ? $rs_user->createEntry() : redirect('users.php');
			break;
		case 'edit':
			// Edit an existing user
			userHasPrivilege($_SESSION['role'], 'can_edit_users') ? $rs_user->editEntry($id) : redirect('users.php');
			break;
		case 'delete':
			// Delete an existing user
			userHasPrivilege($_SESSION['role'], 'can_delete_users') ? $rs_user->deleteEntry($id) : redirect('users.php');
			break;
		case 'reset_password':
			// Reset a user's password
			userHasPrivilege($_SESSION['role'], 'can_edit_users') ? $rs_user->resetPassword($id) : redirect('users.php');
			break;
		default:
			// List all users
			userHasPrivilege($_SESSION['role'], 'can_view_users') ? $rs_user->listEntries() : redirect('index.php');
	}
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';