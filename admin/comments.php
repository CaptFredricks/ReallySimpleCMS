<?php
/**
 * Admin comments page. Makes use of the Comment object.
 * @since 1.1.0-beta_snap-01
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

$rs_ad_comment = new \Admin\Comment($id, $action);
?>
<article class="content">
	<?php
	switch($action) {
		case 'edit':
			// Action: Edit Comment
			userHasPrivilege('can_edit_comments') ?
				$rs_ad_comment->editRecord() :
					redirect(ADMIN_URI);
			break;
		case 'approve':
			// Action: Approve Comment
			userHasPrivilege('can_edit_comments') ?
				$rs_ad_comment->approveComment() :
					redirect(ADMIN_URI);
			break;
		case 'unapprove':
			// Action: Unapprove Comment
			userHasPrivilege('can_edit_comments') ?
				$rs_ad_comment->unapproveComment() :
					redirect(ADMIN_URI);
			break;
		case 'spam':
			// Action: Spam Comment
			userHasPrivilege('can_edit_comments') ?
				$rs_ad_comment->spamComment() :
					redirect(ADMIN_URI);
			break;
		case 'delete':
			// Action: Delete Comment
			userHasPrivilege('can_delete_comments') ?
				$rs_ad_comment->deleteRecord() :
					redirect(ADMIN_URI);
			break;
		default:
			// Action: List Comments
			userHasPrivilege('can_view_comments') ?
				$rs_ad_comment->listRecords() :
					redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';