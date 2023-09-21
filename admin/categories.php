<?php
/**
 * Admin categories page.
 * @since 1.5.0[a]
 */
require_once __DIR__ . '/header.php';

// Fetch the category's id
$id = (int)($_GET['id'] ?? 0);

// Create a Term object
$rs_category = new Term($id, $taxonomies['category']);
?>
<article class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'create':
			// Create a new category
			userHasPrivilege('can_create_categories') ? $rs_category->createRecord() :
				redirect(ADMIN_URI);
			break;
		case 'edit':
			// Edit an existing category
			userHasPrivilege('can_edit_categories') ? $rs_category->editRecord() :
				redirect(ADMIN_URI);
			break;
		case 'delete':
			// Delete an existing category
			userHasPrivilege('can_delete_categories') ? $rs_category->deleteRecord() :
				redirect(ADMIN_URI);
			break;
		default:
			// List all categories
			userHasPrivilege('can_view_categories') ? $rs_category->listRecords() :
				redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';