<?php
// Include the header
require_once __DIR__.'/header.php';

// Create a Post object
$rs_post = new Post;
?>
<div class="wrapper clear">
	<?php
	// Fetch the post's type
	$type = $_GET['type'] ?? 'post';
	
	// Redirect to the 'List Widgets' page if the post's type is 'widget'
	if($type === 'widget') redirect('widgets.php');
	
	// Redirect to the 'List Menus' page if the post's type is 'nav_menu_item'
	if($type === 'nav_menu_item') redirect('menus.php');
	
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the post's id
	$id = (int)($_GET['id'] ?? 0);
	
	// Fetch the post's type from the database
	$post = $rs_query->selectRow('posts', 'type', array('id'=>$id));
	
	// Set the post's type if the post exists
	if($post) $type = $post['type'];
	
	switch($action) {
		case 'create':
			// Create a new post
			userHasPrivilege($_SESSION['role'], 'can_create_'.$type.'s') ? $rs_post->createPost() : redirect('posts.php'.($type !== 'post' ? '?type='.$type : ''));
			break;
		case 'edit':
			// Edit an existing post
			userHasPrivilege($_SESSION['role'], 'can_edit_'.$type.'s') ? $rs_post->editPost($id) : redirect('posts.php'.($type !== 'post' ? '?type='.$type : ''));
			break;
		case 'trash':
			// Send an existing post to the trash
			userHasPrivilege($_SESSION['role'], 'can_edit_'.$type.'s') ? $rs_post->trashPost($id) : redirect('posts.php'.($type !== 'post' ? '?type='.$type : ''));
			break;
		case 'restore':
			// Restore a trashed post
			userHasPrivilege($_SESSION['role'], 'can_edit_'.$type.'s') ? $rs_post->restorePost($id) : redirect('posts.php'.($type !== 'post' ? '?type='.$type : ''));
			break;
		case 'delete':
			// Delete an existing post
			userHasPrivilege($_SESSION['role'], 'can_delete_'.$type.'s') ? $rs_post->deletePost($id) : redirect('posts.php'.($type !== 'post' ? '?type='.$type : '?type='.$type));
			break;
		default:
			// List all posts
			userHasPrivilege($_SESSION['role'], 'can_view_'.$type.'s') ? $rs_post->listPosts() : redirect('index.php');
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';