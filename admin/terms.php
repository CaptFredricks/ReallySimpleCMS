<?php
/**
 * Admin terms page. Makes use of the Term object.
 * @since 1.0.4-beta
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if(isset($_GET['taxonomy'])) {
	$taxonomy = $_GET['taxonomy'];
} else {
	if($id === 0) {
		$taxonomy = 'category';
	} else {
		if(!termExists($id)) {
			redirect('categories.php');
		} else {
			// Set the taxonomy if it exists in the database
			$db_tax = $rs_query->selectField(getTable('t'), 'taxonomy', array(
				'id' => $id
			));
			
			$taxonomy = $rs_query->selectField(getTable('ta'), 'name', array(
				'id' => $db_tax
			)) ?? 'category';
		}
	}
}

// Redirect 'category' taxonomy
if($taxonomy === 'category') redirect('categories.php');

$rs_ad_term = new \Admin\Term($id, $action, $rs_taxonomies[$taxonomy] ?? array());
?>
<article class="content">
	<?php
	$tax_name = str_replace(' ', '_', $rs_taxonomies[$taxonomy]['labels']['name_lowercase']);
	$menu_link = $rs_taxonomies[$taxonomy]['menu_link'];
	
	switch($action) {
		case 'create':
			// Action: Create Term
			userHasPrivilege('can_create_' . $tax_name) ?
				$rs_ad_term->createRecord() :
					redirect($menu_link);
			break;
		case 'edit':
			// Action: Edit Term
			userHasPrivilege('can_edit_' . $tax_name) ?
				$rs_ad_term->editRecord() :
					redirect($menu_link);
			break;
		case 'delete':
			// Action: Delete Term
			userHasPrivilege('can_delete_' . $tax_name) ?
				$rs_ad_term->deleteRecord() :
					redirect($menu_link);
			break;
		default:
			// Action: List Terms
			userHasPrivilege('can_view_' . $tax_name) ?
				$rs_ad_term->listRecords() :
					redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';