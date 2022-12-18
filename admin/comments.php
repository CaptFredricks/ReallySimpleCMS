<?php
require_once __DIR__ . '/header.php';

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
			userHasPrivilege('can_edit_comments') ? $rs_comment->editComment() :
				redirect(ADMIN_URI);
			break;
		case 'approve':
			// Approve an existing comment
			userHasPrivilege('can_edit_comments') ? $rs_comment->approveComment() :
				redirect(ADMIN_URI);
			break;
		case 'unapprove':
			// Unapprove an existing comment
			userHasPrivilege('can_edit_comments') ? $rs_comment->unapproveComment() :
				redirect(ADMIN_URI);
			break;
		case 'spam':
			// Send an existing comment to spam
			userHasPrivilege('can_edit_comments') ? $rs_comment->spamComment() :
				redirect(ADMIN_URI);
			break;
		case 'delete':
			// Delete an existing comment
			userHasPrivilege('can_delete_comments') ? $rs_comment->deleteComment() :
				redirect(ADMIN_URI);
			break;
		default:
			// List all comments
			userHasPrivilege('can_view_comments') ? $rs_comment->listComments() :
				redirect('index.php');
	}
	?>
</div>
<?php
require_once __DIR__ . '/footer.php';