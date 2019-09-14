<?php
// Include the header
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
			userHasPrivilege($_SESSION['role'], 'can_create_widgets') ? $rs_widget->createWidget() : redirect('widgets.php');
			break;
		case 'edit':
			// Edit an existing widget
			userHasPrivilege($_SESSION['role'], 'can_edit_widgets') ? $rs_widget->editWidget($id) : redirect('widgets.php');
			break;
		case 'delete':
			// Delete an existing widget
			userHasPrivilege($_SESSION['role'], 'can_delete_widgets') ? $rs_widget->deleteWidget($id) : redirect('widgets.php');
			break;
		default:
			// List all widgets
			userHasPrivilege($_SESSION['role'], 'can_view_widgets') ? $rs_widget->listWidgets() : redirect('index.php');
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';