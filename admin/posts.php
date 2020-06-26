<?php
// Include the header
require_once __DIR__.'/header.php';

// Fetch the post's id
$id = (int)($_GET['id'] ?? 0);

// Check whether the post's type is in the query string
if(isset($_GET['type'])) {
	// Fetch the post's type from the query string
	$type = $_GET['type'];
} else {
	// Check whether the id is '0'
	if($id === 0) {
		// Set the type to 'post'
		$type = 'post';
	} else {
		// Fetch the post's type from the database
		$post = $rs_query->selectRow('posts', 'type', array('id'=>$id));
		
		// Set the post's type if the post exists
		if($post) $type = $post['type'];
	}
}

// Create a Post object
$rs_post = new Post($id, $post_types[$type] ?? array());
?>
<div class="wrapper clear">
	<?php
	// Create an id from the post type's label
	$type_id = strtolower($post_types[$type]['label']);
	
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'create':
			// Create a new post
			userHasPrivilege($session['role'], 'can_create_'.$type_id) ? $rs_post->createPost() : redirect($post_types[$type]['menu_link']);
			break;
		case 'edit':
			// Edit an existing post
			userHasPrivilege($session['role'], 'can_edit_'.$type_id) ? $rs_post->editPost() : redirect($post_types[$type]['menu_link']);
			break;
		case 'trash':
			// Send an existing post to the trash
			userHasPrivilege($session['role'], 'can_edit_'.$type_id) ? $rs_post->trashPost() : redirect($post_types[$type]['menu_link']);
			break;
		case 'restore':
			// Restore a trashed post
			userHasPrivilege($session['role'], 'can_edit_'.$type_id) ? $rs_post->restorePost() : redirect($post_types[$type]['menu_link']);
			break;
		case 'delete':
			// Delete an existing post
			userHasPrivilege($session['role'], 'can_delete_'.$type_id) ? $rs_post->deletePost() : redirect($post_types[$type]['menu_link']);
			break;
		default:
			// List all posts
			userHasPrivilege($session['role'], 'can_view_'.$type_id) ? $rs_post->listPosts() : redirect('index.php');
	}
	
	// Redirect to the 'List Media' page if the post's type is 'media'
	if($type === 'media') redirect('media.php');
	
	// Redirect to the 'List Menus' page if the post's type is 'nav_menu_item'
	if($type === 'nav_menu_item') redirect('menus.php');
	
	// Redirect to the 'List Widgets' page if the post's type is 'widget'
	if($type === 'widget') redirect('widgets.php');
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';