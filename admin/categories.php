<?php
// Include header
require_once __DIR__.'/header.php';

// Create a Category object
$rs_category = new Category;
?>
<div class="wrapper clear">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the category id
	$id = (int)($_GET['id'] ?? 0);
	
	switch($action) {
		case 'create':
			// Create a new category
			userHasPrivilege($_SESSION['role'], 'can_create_categories') ? $rs_category->createEntry() : redirect('categories.php');
			break;
		case 'edit':
			// Edit an existing category
			userHasPrivilege($_SESSION['role'], 'can_edit_categories') ? $rs_category->editEntry($id) : redirect('categories.php');
			break;
		case 'delete':
			// Delete an existing category
			userHasPrivilege($_SESSION['role'], 'can_delete_categories') ? $rs_category->deleteEntry($id) : redirect('categories.php');
			break;
		default:
			// List all categories
			userHasPrivilege($_SESSION['role'], 'can_view_categories') ? $rs_category->listEntries() : redirect('index.php');
	}
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';