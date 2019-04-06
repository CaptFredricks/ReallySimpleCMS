<?php
// Include header
require_once __DIR__.'/header.php';

// Create a Post object
$rs_post = new Post;
?>
<div class="wrapper">
	<?php
	// Get the current action
	$action = $_GET['action'] ?? '';
	
	// Get the post id
	$id = (int)($_GET['id'] ?? 0);
	
	switch($action) {
		case 'create':
			// Create a new post
			$rs_post->createEntry();
			break;
		case 'edit':
			// Edit an existing post
			$rs_post->editEntry($id);
			break;
		case 'trash':
			// Send an existing post to the trash
			$rs_post->trashEntry($id);
			break;
		case 'restore':
			// Restore a trashed post
			$rs_post->restoreEntry($id);
			break;
		case 'delete':
			// Delete an existing post
			$rs_post->deleteEntry($id);
			break;
		default:
			// List all posts
			$rs_post->listEntries();
	}
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';