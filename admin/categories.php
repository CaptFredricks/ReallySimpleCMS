<?php
/**
 * Admin categories page. Makes use of the Term object.
 * @since 1.5.0-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

$rs_ad_category = new \Admin\Term($id, $action, $rs_taxonomies['category']);
?>
<article class="content">
	<?php
	switch($action) {
		case 'create':
			// Action: Create Category
			userHasPrivilege('can_create_categories') ?
				$rs_ad_category->createRecord() :
					redirect(ADMIN_URI);
			break;
		case 'edit':
			// Action: Edit Category
			userHasPrivilege('can_edit_categories') ?
				$rs_ad_category->editRecord() :
					redirect(ADMIN_URI);
			break;
		case 'delete':
			// Action: Delete Category
			userHasPrivilege('can_delete_categories') ?
				$rs_ad_category->deleteRecord() :
					redirect(ADMIN_URI);
			break;
		default:
			// Action: List Categories
			userHasPrivilege('can_view_categories') ?
				$rs_ad_category->listRecords() :
					redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';