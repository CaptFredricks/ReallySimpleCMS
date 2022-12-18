<?php
require_once __DIR__ . '/header.php';

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
			userHasPrivilege('can_create_categories') ? $rs_category->createCategory() :
				redirect(ADMIN_URI);
			break;
		case 'edit':
			// Edit an existing category
			userHasPrivilege('can_edit_categories') ? $rs_category->editCategory() :
				redirect(ADMIN_URI);
			break;
		case 'delete':
			// Delete an existing category
			userHasPrivilege('can_delete_categories') ? $rs_category->deleteCategory() :
				redirect(ADMIN_URI);
			break;
		default:
			// List all categories
			userHasPrivilege('can_view_categories') ? $rs_category->listCategories() :
				redirect('index.php');
	}
	?>
</div>
<?php
require_once __DIR__ . '/footer.php';