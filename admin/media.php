<?php
require_once __DIR__ . '/header.php';

// Fetch the media's id
$id = (int)($_GET['id'] ?? 0);

// Create a Media object
$rs_media = new Media($id);
?>
<div class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'upload':
			// Upload new media
			userHasPrivilege('can_upload_media') ? $rs_media->uploadMedia() :
				redirect(ADMIN_URI);
			break;
		case 'edit':
			// Edit existing media
			userHasPrivilege('can_edit_media') ? $rs_media->editMedia() :
				redirect(ADMIN_URI);
			break;
		case 'replace':
			// Replace existing media
			userHasPrivilege('can_edit_media') ? $rs_media->replaceMedia() :
				redirect(ADMIN_URI);
			break;
		case 'delete':
			// Delete existing media
			userHasPrivilege('can_delete_media') ? $rs_media->deleteMedia() :
				redirect(ADMIN_URI);
			break;
		default:
			// List all media
			userHasPrivilege('can_view_media') ? $rs_media->listMedia() :
				redirect('index.php');
	}
	?>
</div>
<?php
require_once __DIR__ . '/footer.php';