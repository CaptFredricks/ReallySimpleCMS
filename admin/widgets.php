<?php
// Include header
require_once __DIR__.'/header.php';

// Create a Widget object
$rs_widget = new Widget;
?>
<div class="wrapper">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the widget id
	$id = (int)($_GET['id'] ?? 0);
	
	// Choose the appropriate function based on the current action
	switch($action) {
		case 'create':
			// Create a new widget
			$rs_widget->createEntry();
			break;
		case 'edit':
		case 'delete':
		default:
			// List all widgets
			$rs_widget->listEntries();
	}
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';