<?php
/**
 * Admin posts page.
 * @since 1.4.0[a]
 */
require_once __DIR__ . '/header.php';

// Fetch the post's id
$id = (int)($_GET['id'] ?? 0);

if(isset($_GET['type'])) {
	$type = $_GET['type'];
} else {
	if($id === 0) {
		// Default to 'post'
		$type = 'post';
	} else {
		if(!postExists($id)) {
			redirect('posts.php');
		} else {
			// Fetch the post's type from the database and set the type if it exists
			$type = $rs_query->selectField('posts', 'type', array('id' => $id)) ?? 'post';
		}
	}
}

// Redirect non-content post types
if($type === 'media') redirect('media.php');
if($type === 'nav_menu_item') redirect('menus.php');
if($type === 'widget') redirect('widgets.php');

// Create a Post object
$rs_post = new Post($id, $post_types[$type] ?? array());
?>
<article class="content">
	<?php
	// Create an id from the post type's label
	$type_id = str_replace(' ', '_', $post_types[$type]['labels']['name_lowercase']);
	
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'create':
			// Create a new post
			userHasPrivilege('can_create_' . $type_id) ? $rs_post->createPost() :
				redirect($post_types[$type]['menu_link']);
			break;
		case 'edit':
			// Edit an existing post
			userHasPrivilege('can_edit_' . $type_id) ? $rs_post->editPost() :
				redirect($post_types[$type]['menu_link']);
			break;
		case 'duplicate':
			// Duplicate an existing post
			userHasPrivilege('can_create_' . $type_id) ? $rs_post->duplicatePost() :
				redirect($post_types[$type]['menu_link']);
			break;
		case 'trash':
			// Send an existing post to the trash
			userHasPrivilege('can_edit_' . $type_id) ? $rs_post->trashPost() :
				redirect($post_types[$type]['menu_link']);
			break;
		case 'restore':
			// Restore a trashed post
			userHasPrivilege('can_edit_' . $type_id) ? $rs_post->restorePost() :
				redirect($post_types[$type]['menu_link']);
			break;
		case 'delete':
			// Delete an existing post
			userHasPrivilege('can_delete_' . $type_id) ? $rs_post->deletePost() :
				redirect($post_types[$type]['menu_link']);
			break;
		default:
			// List all posts
			userHasPrivilege('can_view_' . $type_id) ? $rs_post->listPosts() :
				redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';