<?php
/**
 * Admin posts page. Makes use of the Post object.
 * @since 1.4.0-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if(isset($_GET['type'])) {
	$type = $_GET['type'];
} else {
	if($id === 0) {
		$type = 'post';
	} else {
		if(!postExists($id)) {
			redirect('posts.php');
		} else {
			// Set the type if it exists in the database
			$type = $rs_query->selectField(getTable('p'), 'type', array(
				'id' => $id
			)) ?? 'post';
		}
	}
}

// Redirect non-content post types
if($type === 'media') redirect('media.php');
if($type === 'nav_menu_item') redirect('menus.php');
if($type === 'widget') redirect('widgets.php');

$rs_ad_post = new \Admin\Post($id, $action, $rs_post_types[$type] ?? array());
?>
<article class="content">
	<?php
	$type_name = str_replace(' ', '_', $rs_post_types[$type]['labels']['name_lowercase']);
	$menu_link = $rs_post_types[$type]['menu_link'];
	
	switch($action) {
		case 'create':
			// Action: Create Post
			userHasPrivilege('can_create_' . $type_name) ?
				$rs_ad_post->createRecord() :
					redirect($menu_link);
			break;
		case 'edit':
			// Action: Edit Post
			userHasPrivilege('can_edit_' . $type_name) ?
				$rs_ad_post->editRecord() :
					redirect($menu_link);
			break;
		case 'duplicate':
			// Action: Duplicate Post
			userHasPrivilege('can_create_' . $type_name) ?
				$rs_ad_post->duplicatePost() :
					redirect($menu_link);
			break;
		case 'trash':
			// Action: Trash Post
			userHasPrivilege('can_edit_' . $type_name) ?
				$rs_ad_post->trashPost() :
					redirect($menu_link);
			break;
		case 'restore':
			// Action: Restore Post
			userHasPrivilege('can_edit_' . $type_name) ?
				$rs_ad_post->restorePost() :
					redirect($menu_link);
			break;
		case 'delete':
			// Action: Delete Post
			userHasPrivilege('can_delete_' . $type_name) ?
				$rs_ad_post->deleteRecord() :
					redirect($menu_link);
			break;
		default:
			// Action: List Posts
			userHasPrivilege('can_view_' . $type_name) ?
				$rs_ad_post->listRecords() :
					redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';