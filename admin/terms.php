<?php
/**
 * Admin terms page.
 * @since 1.0.4[b]
 */
require_once __DIR__ . '/header.php';

// Fetch the term's id
$id = (int)($_GET['id'] ?? 0);

if(isset($_GET['taxonomy'])) {
	$taxonomy = $_GET['taxonomy'];
} else {
	if($id === 0) {
		// Default to 'category'
		$taxonomy = 'category';
	} else {
		if(!termExists($id)) {
			redirect('categories.php');
		} else {
			// Fetch the term's taxonomy id from the database
			$db_tax = $rs_query->selectField('terms', 'taxonomy', array('id' => $id));
			
			// Fetch the term's taxonomy from the database and set the taxonomy if it exists
			$taxonomy = $rs_query->selectField('taxonomies', 'name', array('id' => $db_tax)) ?? 'category';
		}
	}
}

// Redirect 'category' taxonomy
if($taxonomy === 'category') redirect('categories.php');

// Create a Term object
$rs_term = new Term($id, $taxonomies[$taxonomy] ?? array());
?>
<article class="content">
	<?php
	// Create an id from the taxonomy's label
	$tax_id = str_replace(' ', '_', $taxonomies[$taxonomy]['labels']['name_lowercase']);
	
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'create':
			// Create a new term
			userHasPrivilege('can_create_' . $tax_id) ? $rs_term->createRecord() :
				redirect($taxonomies[$taxonomy]['menu_link']);
			break;
		case 'edit':
			// Edit an existing term
			userHasPrivilege('can_edit_' . $tax_id) ? $rs_term->editRecord() :
				redirect($taxonomies[$taxonomy]['menu_link']);
			break;
		case 'delete':
			// Delete an existing term
			userHasPrivilege('can_delete_' . $tax_id) ? $rs_term->deleteRecord() :
				redirect($taxonomies[$taxonomy]['menu_link']);
			break;
		default:
			// List all terms
			userHasPrivilege('can_view_' . $tax_id) ? $rs_term->listRecords() :
				redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';