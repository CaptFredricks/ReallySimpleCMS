<?php
require_once __DIR__ . '/header.php';

// Fetch the user's id
$id = (int)($_GET['id'] ?? 0);

// Create a User object
$rs_user = new User($id);
?>
<div class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'create':
			// Create a new user
			userHasPrivilege('can_create_users') ? $rs_user->createUser() :
				redirect(ADMIN_URI);
			break;
		case 'edit':
			// Edit an existing user
			userHasPrivilege('can_edit_users') ? $rs_user->editUser() :
				redirect(ADMIN_URI);
			break;
		case 'delete':
			// Delete an existing user
			userHasPrivilege('can_delete_users') ? $rs_user->deleteUser() :
				redirect(ADMIN_URI);
			break;
		case 'reset_password':
			// Reset a user's password
			userHasPrivilege('can_edit_users') ? $rs_user->resetPassword() :
				redirect(ADMIN_URI);
			break;
		case 'reassign_content':
			// Reassign a user's content
			userHasPrivilege('can_delete_users') ? $rs_user->reassignContent() :
				redirect(ADMIN_URI);
			break;
		default:
			// List all users
			userHasPrivilege('can_view_users') ? $rs_user->listUsers() :
				redirect('index.php');
	}
	?>
</div>
<?php
require_once __DIR__ . '/footer.php';