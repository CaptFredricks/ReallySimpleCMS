<?php
// Include the header
require_once __DIR__.'/header.php';

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
			userHasPrivilege($session['role'], 'can_upload_media') ? $rs_media->uploadMedia() : redirect('media.php');
			break;
		case 'edit':
			// Edit existing media
			userHasPrivilege($session['role'], 'can_edit_media') ? $rs_media->editMedia() : redirect('media.php');
			break;
		case 'delete':
			// Delete existing media
			userHasPrivilege($session['role'], 'can_delete_media') ? $rs_media->deleteMedia() : redirect('media.php');
			break;
		default:
			// List all media
			userHasPrivilege($session['role'], 'can_view_media') ? $rs_media->listMedia() : redirect('index.php');
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';