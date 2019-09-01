<?php
// Include header
require_once __DIR__.'/header.php';

// Create a Widget object
$rs_widget = new Widget;
?>
<div class="wrapper clear">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the widget id
	$id = (int)($_GET['id'] ?? 0);
	
	switch($action) {
		case 'create':
			// Create a new widget
			$rs_widget->createEntry();
			break;
		case 'edit':
			// Edit an existing widget
			$rs_widget->editEntry($id);
			break;
		case 'delete':
			// Delete an existing widget
			$rs_widget->deleteEntry($id);
			break;
		default:
			// List all widgets
			$rs_widget->listEntries();
	}
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';