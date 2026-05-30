<?php
/**
 * Submit admin bulk actions via AJAX.
 * @since 1.2.7-beta
 *
 * @package ReallySimpleCMS
 */

// Stop execution if the file is accessed directly
if(empty($_POST)) exit('You do not have permission to access this resource.');

// Only initialize the base files and functions
define('BASE_INIT', true);
define('ADMIN_URI', $_POST['uri']);

require_once dirname(dirname(__DIR__)) . '/init.php';
requireFiles(array(RS_ADMIN_FUNC, RS_FRONT_FUNC));

$rs_theme_path = slash(PATH . THEMES) . getSetting('active_theme');

if(file_exists($rs_theme_path . '/functions.php')) requireFile($rs_theme_path . '/functions.php');

$post_type = $type = '';
$taxonomy = $tax = '';

foreach($rs_post_types as $key => $value) {
	if($key === 'widget') continue;
	
	if($value['labels']['name_lowercase'] === $_POST['page']) {
		$post_type = $_POST['page'];
		$type = $key;
	}
}

foreach($rs_taxonomies as $key => $value) {
	if($value['labels']['name_lowercase'] === $_POST['page']) {
		$taxonomy = $_POST['page'];
		$tax = $key;
	}
}

switch($_POST['page']) {
	case $post_type:
		$rs_ad_post = new \Admin\Post(0, '', $rs_post_types[$type]);
		
		// Update all selected posts
		if(!empty($_POST['selected'])) {
			foreach($_POST['selected'] as $id) {
				$id = (int)$id;
				
				if($id === 0) continue;
				
				$rs_ad_post->updatePostStatus($_POST['action'], $id);
			}
		}
		
		echo $rs_ad_post->listRecords();
		break;
	case 'comments':
		$rs_ad_comment = new \Admin\Comment(0, '');
		
		switch($_POST['action']) {
			case 'delete_spam':
				// Delete all spam comments
				$rs_ad_comment->deleteSpamComments();
				break;
			default:
				// Update all selected comments
				if(!empty($_POST['selected'])) {
					foreach($_POST['selected'] as $id) {
						$id = (int)$id;
						
						if($id === 0) continue;
						
						$rs_ad_comment->updateCommentStatus($_POST['action'], $id);
					}
				}
		}
		
		echo $rs_ad_comment->listRecords();
		break;
	case 'widgets':
		$rs_ad_widget = new \Admin\Widget(0, '');
		
		// Update all selected widgets
		if(!empty($_POST['selected'])) {
			foreach($_POST['selected'] as $id) {
				$id = (int)$id;
				
				if($id === 0) continue;
				
				$rs_ad_widget->updateWidgetStatus($_POST['action'], $id);
			}
		}
		
		echo $rs_ad_widget->listRecords();
		break;
	case 'users':
		$rs_ad_user = new \Admin\User(0, '');
		
		// Update all selected users
		if(!empty($_POST['selected'])) {
			foreach($_POST['selected'] as $id) {
				$id = (int)$id;
				
				if($id === 0) continue;
				
				$rs_ad_user->updateUserRole($_POST['action'], $id);
			}
		}
		
		echo $rs_ad_user->listRecords();
		break;
}