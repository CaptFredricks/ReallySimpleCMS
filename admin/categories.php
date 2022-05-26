<?php
// Include the header
require_once __DIR__.'/header.php';

// Fetch the category's id
$id = (int)($_GET['id'] ?? 0);

// Create a Category object
$rs_category = new Category($id, $taxonomies['category']);
?>
<div class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'create':
			// Create a new category
			userHasPrivilege('can_create_categories') ? $rs_category->createCategory() : redirect('categories.php');
			break;
		case 'edit':
			// Edit an existing category
			userHasPrivilege('can_edit_categories') ? $rs_category->editCategory() : redirect('categories.php');
			break;
		case 'delete':
			// Delete an existing category
			userHasPrivilege('can_delete_categories') ? $rs_category->deleteCategory() : redirect('categories.php');
			break;
		default:
			// List all categories
			userHasPrivilege('can_view_categories') ? $rs_category->listCategories() : redirect('index.php');
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';