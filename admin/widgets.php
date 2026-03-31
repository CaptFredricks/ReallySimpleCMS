<?php
/**
 * Admin widgets page. Makes use of the Widget object.
 * @since 1.6.0-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

$rs_ad_widget = new \Admin\Widget($id, $action);
?>
<article class="content">
	<?php
	switch($action) {
		case 'create':
			// Action: Create Widget
			userHasPrivilege('can_create_widgets') ?
				$rs_ad_widget->createRecord() :
					redirect(ADMIN_URI);
			break;
		case 'edit':
			// Action: Edit Widget
			userHasPrivilege('can_edit_widgets') ?
				$rs_ad_widget->editRecord() :
					redirect(ADMIN_URI);
			break;
		case 'delete':
			// Action: Delete Widget
			userHasPrivilege('can_delete_widgets') ?
				$rs_ad_widget->deleteRecord() :
					redirect(ADMIN_URI);
			break;
		default:
			// Action: List Widgets
			userHasPrivilege('can_view_widgets') ?
				$rs_ad_widget->listRecords() :
					redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';