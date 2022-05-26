<?php
// Include the header
require_once __DIR__.'/header.php';

// Fetch the widget's id
$id = (int)($_GET['id'] ?? 0);

// Create a Widget object
$rs_widget = new Widget($id);
?>
<div class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'create':
			// Create a new widget
			userHasPrivilege('can_create_widgets') ? $rs_widget->createWidget() : redirect('widgets.php');
			break;
		case 'edit':
			// Edit an existing widget
			userHasPrivilege('can_edit_widgets') ? $rs_widget->editWidget() : redirect('widgets.php');
			break;
		case 'delete':
			// Delete an existing widget
			userHasPrivilege('can_delete_widgets') ? $rs_widget->deleteWidget() : redirect('widgets.php');
			break;
		default:
			// List all widgets
			userHasPrivilege('can_view_widgets') ? $rs_widget->listWidgets() : redirect('index.php');
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';