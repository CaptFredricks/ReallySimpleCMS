<?php
// Include the header
require_once __DIR__.'/header.php';

// Fetch the comment's id
$id = (int)($_GET['id'] ?? 0);

// Create a Comment object
$rs_comment = new Comment($id);
?>
<div class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'edit':
			// Edit an existing comment
			userHasPrivilege($session['role'], 'can_edit_comments') ? $rs_comment->editComment() : redirect('comments.php');
			break;
		case 'approve':
			// Approve an existing comment
			userHasPrivilege($session['role'], 'can_edit_comments') ? $rs_comment->approveComment() : redirect('comments.php');
			break;
		case 'unapprove':
			// Unapprove an existing comment
			userHasPrivilege($session['role'], 'can_edit_comments') ? $rs_comment->unapproveComment() : redirect('comments.php');
			break;
		case 'delete':
			// Delete an existing comment
			userHasPrivilege($session['role'], 'can_delete_comments') ? $rs_comment->deleteComment() : redirect('comments.php');
			break;
		default:
			// List all comments
			userHasPrivilege($session['role'], 'can_view_comments') ? $rs_comment->listComments() : redirect('index.php');
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';