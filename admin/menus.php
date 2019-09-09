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
			userHasPrivilege($_SESSION['role'], 'can_create_menus') ? $rs_menu->createEntry() : redirect('menus.php');
			break;
		case 'edit':
			// Edit an existing menu
			userHasPrivilege($_SESSION['role'], 'can_edit_menus') ? $rs_menu->editEntry($id) : redirect('menus.php');
			break;
		case 'delete':
			break;
		default:
			// List all menus
			userHasPrivilege($_SESSION['role'], 'can_view_menus') ? $rs_menu->listEntries() : redirect('index.php');
	}
	?>
</div>
<?php