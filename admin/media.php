<?php
/**
 * Admin media page. Makes use of the Media object.
 * @since 2.1.0-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

$rs_ad_media = new \Admin\Media($id, $action);
?>
<article class="content">
	<?php
	switch($action) {
		case 'upload':
			// Action: Upload Media
			userHasPrivilege('can_upload_media') ?
				$rs_ad_media->uploadRecordMedia() :
					redirect(ADMIN_URI);
			break;
		case 'edit':
			// Action: Edit Media
			userHasPrivilege('can_edit_media') ?
				$rs_ad_media->editRecordMedia() :
					redirect(ADMIN_URI);
			break;
		case 'replace':
			// Action: Replace Media
			userHasPrivilege('can_edit_media') ?
				$rs_ad_media->replaceRecordMedia() :
					redirect(ADMIN_URI);
			break;
		case 'delete':
			// Action: Delete Media
			userHasPrivilege('can_delete_media') ?
				$rs_ad_media->deleteRecordMedia() :
					redirect(ADMIN_URI);
			break;
		default:
			// Action: List Media
			userHasPrivilege('can_view_media') ?
				$rs_ad_media->listRecordsMedia() :
					redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';