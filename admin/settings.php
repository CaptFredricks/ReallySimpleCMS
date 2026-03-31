<?php
/**
 * Admin settings page. Makes use of the Settings object.
 * @since 1.2.6-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
$page = $_GET['page'] ?? 'general';

$rs_ad_settings = new \Admin\Settings($page);
?>
<article class="content">
	<?php
	switch($page) {
		case 'design':
			// Page: Design Settings
			userHasPrivilege('can_edit_settings') ?
				$rs_ad_settings->designSettings() :
					redirect('index.php');
			break;
		case 'user_roles':
			// Page: User Roles
			$rs_ad_user_role = new \Admin\UserRole($id, $action, $page);
			
			switch($action) {
				case 'create':
					// Action: Create User Role
					userHasPrivilege('can_create_user_roles') ?
						$rs_ad_user_role->createRecord() :
							redirect(ADMIN_URI . getQueryString(array(
								'page' => 'user_roles'
							)));
					break;
				case 'edit':
					// Action: Edit User Role
					userHasPrivilege('can_edit_user_roles') ?
						$rs_ad_user_role->editRecord() :
							redirect(ADMIN_URI . getQueryString(array(
								'page' => 'user_roles'
							)));
					break;
				case 'delete':
					// Action: Delete User Role
					userHasPrivilege('can_delete_user_roles') ?
						$rs_ad_user_role->deleteRecord() :
							redirect(ADMIN_URI . getQueryString(array(
								'page' => 'user_roles'
							)));
					break;
				default:
					// Action: List User Roles
					userHasPrivilege('can_view_user_roles') ?
						$rs_ad_user_role->listRecords() :
							redirect('index.php');
			}
			break;
		default:
			// Page: General Settings
			userHasPrivilege('can_edit_settings') ?
				$rs_ad_settings->generalSettings() :
					redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';