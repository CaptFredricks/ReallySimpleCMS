<?php
// Include the header
require_once __DIR__.'/header.php';

// Fetch the menu's id
$id = (int)($_GET['id'] ?? 0);

// Create a Menu object
$rs_menu = new Menu($id);
?>
<div class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'create':
			// Create a new menu
			userHasPrivilege($session['role'], 'can_create_menus') ? $rs_menu->createMenu() : redirect('menus.php');
			break;
		case 'edit':
			// Edit an existing menu
			userHasPrivilege($session['role'], 'can_edit_menus') ? $rs_menu->editMenu() : redirect('menus.php');
			break;
		case 'delete':
			// Delete an existing menu
			userHasPrivilege($session['role'], 'can_delete_menus') ? $rs_menu->deleteMenu() : redirect('menus.php');
			break;
		default:
			// List all menus
			userHasPrivilege($session['role'], 'can_view_menus') ? $rs_menu->listMenus() : redirect('index.php');
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';