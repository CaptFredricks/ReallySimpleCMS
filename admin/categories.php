<?php
// Include header
require_once __DIR__.'/header.php';

// Create a Category object
$rs_category = new Category;
?>
<div class="wrapper">
	<?php
	// Get the current action
	$action = $_GET['action'] ?? '';
	
	// Get the category id
	$id = (int)($_GET['id'] ?? 0);
	
	switch($action) {
		case 'create':
			// Create a new category
			$rs_category->createEntry();
			break;
		case 'edit':
			// Edit an existing category
			//$rs_category->editEntry($id);
			break;
		case 'delete':
			// Delete an existing category
			//$rs_category->deleteEntry($id);
			break;
		default:
			// List all categories
			$rs_category->listEntries();
	}
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';