<?php
// Include header
require_once __DIR__.'/header.php';

// Create a Settings object
$rs_settings = new Settings;
?>
<div class="wrapper clear">
	<?php
	// Fetch the current settings page
	$page = $_GET['page'] ?? '';
	
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the current id
	$id = (int)($_GET['id'] ?? 0);
	
	switch($page) {
		case 'user_roles':
			switch($action) {
				case 'create':
					// Create a new user role
					userHasPrivilege($_SESSION['role'], 'can_create_user_roles') ? $rs_settings->createUserRole() : redirect('settings.php?page=user_roles');
					break;
				case 'edit':
					// Edit an existing user role
					userHasPrivilege($_SESSION['role'], 'can_edit_user_roles') ? $rs_settings->editUserRole($id) : redirect('settings.php?page=user_roles');
					break;
				case 'delete':
					// Delete an existing user role
					userHasPrivilege($_SESSION['role'], 'can_delete_user_roles') ? $rs_settings->deleteUserRole($id) : redirect('settings.php?page=user_roles');
					break;
				default:
					// List all user roles
					userHasPrivilege($_SESSION['role'], 'can_view_user_roles') ? $rs_settings->listUserRoles() : redirect('index.php');
			}
			break;
		default:
			// General settings
			userHasPrivilege($_SESSION['role'], 'can_edit_settings') ? $rs_settings->generalSettings() : redirect('index.php');
	}
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';