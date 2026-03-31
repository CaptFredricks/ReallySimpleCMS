<?php
/**
 * Admin users page. Makes use of the User object.
 * @since 1.1.0-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

$rs_ad_user = new \Admin\User($id, $action);
?>
<article class="content">
	<?php
	switch($action) {
		case 'create':
			// Action: Create User
			userHasPrivilege('can_create_users') ?
				$rs_ad_user->createRecord() :
					redirect(ADMIN_URI);
			break;
		case 'edit':
			// Action: Edit User
			userHasPrivilege('can_edit_users') ?
				$rs_ad_user->editRecord() :
					redirect(ADMIN_URI);
			break;
		case 'delete':
			// Action: Delete User
			userHasPrivilege('can_delete_users') ?
				$rs_ad_user->deleteRecord() :
					redirect(ADMIN_URI);
			break;
		case 'reset_password':
			// Action: Reset Password
			userHasPrivilege('can_edit_users') ?
				$rs_ad_user->resetPassword() :
					redirect(ADMIN_URI);
			break;
		case 'reassign_content':
			// Action: Reassign Content
			userHasPrivilege('can_delete_users') ?
				$rs_ad_user->reassignContent() :
					redirect(ADMIN_URI);
			break;
		default:
			// Action: List Users
			userHasPrivilege('can_view_users') ?
				$rs_ad_user->listRecords() :
					redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';