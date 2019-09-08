<?php
// Include header
require_once __DIR__.'/header.php';

// Create a Post object
$rs_post = new Post;
?>
<div class="wrapper clear">
	<?php
	// Fetch the current post type
	$type = $_GET['type'] ?? 'post';
	
	// Redirect to the widgets page if the post type is 'widget'
	if($type === 'widget') redirect('widgets.php');
	
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the post id
	$id = (int)($_GET['id'] ?? 0);
	
	// Fetch the post type from the database
	$post = $rs_query->selectRow('posts', 'type', array('id'=>$id));
	
	// Set the post type if the post exists
	if($post) $type = $post['type'];
	
	switch($action) {
		case 'create':
			// Create a new post
			userHasPrivilege($_SESSION['role'], 'can_create_'.$type.'s') ? $rs_post->createEntry() : redirect('posts.php'.($type !== 'post' ? '?type='.$type : ''));
			break;
		case 'edit':
			// Edit an existing post
			userHasPrivilege($_SESSION['role'], 'can_edit_'.$type.'s') ? $rs_post->editEntry($id) : redirect('posts.php'.($type !== 'post' ? '?type='.$type : ''));
			break;
		case 'trash':
			// Send an existing post to the trash
			userHasPrivilege($_SESSION['role'], 'can_edit_'.$type.'s') ? $rs_post->trashEntry($id) : redirect('posts.php'.($type !== 'post' ? '?type='.$type : ''));
			break;
		case 'restore':
			// Restore a trashed post
			userHasPrivilege($_SESSION['role'], 'can_edit_'.$type.'s') ? $rs_post->restoreEntry($id) : redirect('posts.php'.($type !== 'post' ? '?type='.$type : ''));
			break;
		case 'delete':
			// Delete an existing post
			userHasPrivilege($_SESSION['role'], 'can_delete_'.$type.'s') ? $rs_post->deleteEntry($id) : redirect('posts.php'.($type !== 'post' ? '?type='.$type : '?type='.$type));
			break;
		default:
			// List all posts
			userHasPrivilege($_SESSION['role'], 'can_view_'.$type.'s') ? $rs_post->listEntries() : redirect('index.php');
	}
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';