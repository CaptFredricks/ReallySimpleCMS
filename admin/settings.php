<?php
// Include the header
require_once __DIR__.'/header.php';

// Create a Settings object
$rs_settings = new Settings;
?>
<div class="content">
	<?php
	// Fetch the current settings page
	$page = $_GET['page'] ?? '';
	
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the current id
	$id = (int)($_GET['id'] ?? 0);
	
	switch($page) {
		case 'design':
			// Design settings
			userHasPrivilege('can_edit_settings') ? $rs_settings->designSettings() : redirect('index.php');
			break;
		case 'user_roles':
			// Create a UserRole object
			$rs_user_role = new UserRole($id);
			
			switch($action) {
				case 'create':
					// Create a new user role
					userHasPrivilege('can_create_user_roles') ? $rs_user_role->createUserRole() : redirect('settings.php?page=user_roles');
					break;
				case 'edit':
					// Edit an existing user role
					userHasPrivilege('can_edit_user_roles') ? $rs_user_role->editUserRole() : redirect('settings.php?page=user_roles');
					break;
				case 'delete':
					// Delete an existing user role
					userHasPrivilege('can_delete_user_roles') ? $rs_user_role->deleteUserRole() : redirect('settings.php?page=user_roles');
					break;
				default:
					// List all user roles
					userHasPrivilege('can_view_user_roles') ? $rs_user_role->listUserRoles() : redirect('index.php');
			}
			break;
		default:
			// General settings
			userHasPrivilege('can_edit_settings') ? $rs_settings->generalSettings() : redirect('index.php');
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';