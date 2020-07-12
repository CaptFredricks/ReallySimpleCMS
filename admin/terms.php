<?php
// Include the header
require_once __DIR__.'/header.php';

// Fetch the term's id
$id = (int)($_GET['id'] ?? 0);

// Check whether the term's taxonomy is in the query string
if(isset($_GET['taxonomy'])) {
	// Fetch the term's taxonomy from the query string
	$taxonomy = $_GET['taxonomy'];
} else {
	// Check whether the id is '0'
	if($id === 0) {
		// Set the taxonomy to 'category'
		$taxonomy = 'category';
	} else {
		// Fetch the term's taxonomy id from the database
		$db_tax = $rs_query->selectField('terms', 'taxonomy', array('id'=>$id));
		
		// Fetch the term's taxonomy from the database and set the taxonomy if it exists
		$taxonomy = $rs_query->selectField('taxonomies', 'name', array('id'=>$db_tax)) ?? 'category';
	}
}

// Create a Term object
$rs_term = new Term($id, $taxonomies[$taxonomy] ?? array());
?>
<div class="wrapper clear">
	<?php
	// Create an id from the taxonomy's label
	$tax_id = str_replace(' ', '_', $taxonomies[$taxonomy]['labels']['name_lowercase']);
	
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'create':
			// Create a new term
			userHasPrivilege($session['role'], 'can_create_'.$tax_id) ? $rs_term->createTerm() : redirect($taxonomies[$taxonomy]['menu_link']);
			break;
		case 'edit':
			// Edit an existing term
			userHasPrivilege($session['role'], 'can_edit_'.$tax_id) ? $rs_term->editTerm() : redirect($taxonomies[$taxonomy]['menu_link']);
			break;
		case 'delete':
			// Delete an existing term
			userHasPrivilege($session['role'], 'can_delete_'.$tax_id) ? $rs_term->deleteTerm() : redirect($taxonomies[$taxonomy]['menu_link']);
			break;
		default:
			// List all posts
			userHasPrivilege($session['role'], 'can_view_'.$tax_id) ? $rs_term->listTerms() : redirect('index.php');
	}
	
	// Redirect to the 'List Categories' page if the term's taxonomy is 'category'
	if($taxonomy === 'category') redirect('categories.php');
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';