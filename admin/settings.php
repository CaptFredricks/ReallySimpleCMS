<?php
/**
 * Admin settings page.
 * @since 1.2.6[a]
 */
require_once __DIR__ . '/header.php';

// Create a Settings object
$rs_settings = new Settings;
?>
<article class="content">
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
			userHasPrivilege('can_edit_settings') ? $rs_settings->designSettings() :
				redirect('index.php');
			break;
		case 'user_roles':
			// Create a UserRole object
			$rs_user_role = new UserRole($id);
			
			switch($action) {
				case 'create':
					// Create a new user role
					userHasPrivilege('can_create_user_roles') ? $rs_user_role->createRecord() :
						redirect(ADMIN_URI . '?page=user_roles');
					break;
				case 'edit':
					// Edit an existing user role
					userHasPrivilege('can_edit_user_roles') ? $rs_user_role->editRecord() :
						redirect(ADMIN_URI . '?page=user_roles');
					break;
				case 'delete':
					// Delete an existing user role
					userHasPrivilege('can_delete_user_roles') ? $rs_user_role->deleteRecord() :
						redirect(ADMIN_URI . '?page=user_roles');
					break;
				default:
					// List all user roles
					userHasPrivilege('can_view_user_roles') ? $rs_user_role->listRecords() :
						redirect('index.php');
			}
			break;
		default:
			// General settings
			userHasPrivilege('can_edit_settings') ? $rs_settings->generalSettings() :
				redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';