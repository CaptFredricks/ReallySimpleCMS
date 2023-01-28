<?php
/**
 * Admin menus page.
 * @since 1.8.0[a]
 */
require_once __DIR__ . '/header.php';

// Fetch the menu's id
$id = (int)($_GET['id'] ?? 0);

// Create a Menu object
$rs_menu = new Menu($id);
?>
<article class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'create':
			// Create a new menu
			userHasPrivilege('can_create_menus') ? $rs_menu->createMenu() :
				redirect(ADMIN_URI);
			break;
		case 'edit':
			// Edit an existing menu
			userHasPrivilege('can_edit_menus') ? $rs_menu->editMenu() :
				redirect(ADMIN_URI);
			break;
		case 'delete':
			// Delete an existing menu
			userHasPrivilege('can_delete_menus') ? $rs_menu->deleteMenu() :
				redirect(ADMIN_URI);
			break;
		default:
			// List all menus
			userHasPrivilege('can_view_menus') ? $rs_menu->listMenus() :
				redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';