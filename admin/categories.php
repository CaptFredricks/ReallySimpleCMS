<?php
// Include the header
require_once __DIR__.'/header.php';

// Create a Category object
$rs_category = new Category;
?>
<div class="wrapper clear">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the category's id
	$id = (int)($_GET['id'] ?? 0);
	
	switch($action) {
		case 'create':
			// Create a new category
			userHasPrivilege($session['role'], 'can_create_categories') ? $rs_category->createCategory() : redirect('categories.php');
			break;
		case 'edit':
			// Edit an existing category
			userHasPrivilege($session['role'], 'can_edit_categories') ? $rs_category->editCategory($id) : redirect('categories.php');
			break;
		case 'delete':
			// Delete an existing category
			userHasPrivilege($session['role'], 'can_delete_categories') ? $rs_category->deleteCategory($id) : redirect('categories.php');
			break;
		default:
			// List all categories
			userHasPrivilege($session['role'], 'can_view_categories') ? $rs_category->listCategories() : redirect('index.php');
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';