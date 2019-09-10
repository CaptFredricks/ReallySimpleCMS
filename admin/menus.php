<?php
// Include header
require_once __DIR__.'/header.php';

// Create a Menu object
$rs_menu = new Menu;
?>
<div class="wrapper clear">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the menu id
	$id = (int)($_GET['id'] ?? 0);
	
	switch($action) {
		case 'create':
			// Create a new menu
			userHasPrivilege($_SESSION['role'], 'can_create_menus') ? $rs_menu->createMenu() : redirect('menus.php');
			break;
		case 'edit':
			// Edit an existing menu
			userHasPrivilege($_SESSION['role'], 'can_edit_menus') ? $rs_menu->editMenu($id) : redirect('menus.php');
			break;
		case 'delete':
			// Delete an existing menu
			userHasPrivilege($_SESSION['role'], 'can_delete_menus') ? $rs_menu->deleteMenu($id) : redirect('menus.php');
			break;
		default:
			// List all menus
			userHasPrivilege($_SESSION['role'], 'can_view_menus') ? $rs_menu->listMenus() : redirect('index.php');
	}
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';