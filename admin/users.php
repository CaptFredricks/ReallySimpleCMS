<?php
// Include the header
require_once __DIR__.'/header.php';

// Create a User object
$rs_user = new User;
?>
<div class="wrapper clear">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the user's id
	$id = (int)($_GET['id'] ?? 0);
	
	switch($action) {
		case 'create':
			// Create a new user
			userHasPrivilege($session['role'], 'can_create_users') ? $rs_user->createUser() : redirect('users.php');
			break;
		case 'edit':
			// Edit an existing user
			userHasPrivilege($session['role'], 'can_edit_users') ? $rs_user->editUser($id) : redirect('users.php');
			break;
		case 'delete':
			// Delete an existing user
			userHasPrivilege($session['role'], 'can_delete_users') ? $rs_user->deleteUser($id) : redirect('users.php');
			break;
		case 'reset_password':
			// Reset a user's password
			userHasPrivilege($session['role'], 'can_edit_users') ? $rs_user->resetPassword($id) : redirect('users.php');
			break;
		case 'reassign_content':
			// Reassign a user's content
			userHasPrivilege($session['role'], 'can_delete_users') ? $rs_user->reassignContent($id) : redirect('users.php');
			break;
		default:
			// List all users
			userHasPrivilege($session['role'], 'can_view_users') ? $rs_user->listUsers() : redirect('index.php');
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';