<?php
/**
 * Admin menus page. Makes use of the Menu object.
 * @since 1.8.0-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

$rs_ad_menu = new \Admin\Menu($id, $action);
?>
<article class="content">
	<?php
	switch($action) {
		case 'create':
			// Action: Create Menu
			userHasPrivilege('can_create_menus') ?
				$rs_ad_menu->createRecord() :
					redirect(ADMIN_URI);
			break;
		case 'edit':
			// Action: Edit Menu
			userHasPrivilege('can_edit_menus') ?
				$rs_ad_menu->editRecord() :
					redirect(ADMIN_URI);
			break;
		case 'delete':
			// Action: Delete Menu
			userHasPrivilege('can_delete_menus') ?
				$rs_ad_menu->deleteRecord() :
					redirect(ADMIN_URI);
			break;
		default:
			// Action: List Menus
			userHasPrivilege('can_view_menus') ?
				$rs_ad_menu->listRecords() :
					redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';