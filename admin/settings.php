<?php
// Include the header
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
					userHasPrivilege($session['role'], 'can_create_user_roles') ? $rs_settings->createUserRole() : redirect('settings.php?page=user_roles');
					break;
				case 'edit':
					// Edit an existing user role
					userHasPrivilege($session['role'], 'can_edit_user_roles') ? $rs_settings->editUserRole($id) : redirect('settings.php?page=user_roles');
					break;
				case 'delete':
					// Delete an existing user role
					userHasPrivilege($session['role'], 'can_delete_user_roles') ? $rs_settings->deleteUserRole($id) : redirect('settings.php?page=user_roles');
					break;
				default:
					// List all user roles
					userHasPrivilege($session['role'], 'can_view_user_roles') ? $rs_settings->listUserRoles() : redirect('index.php');
			}
			break;
		default:
			// General settings
			userHasPrivilege($session['role'], 'can_edit_settings') ? $rs_settings->generalSettings() : redirect('index.php');
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';