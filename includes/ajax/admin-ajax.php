<?php
/**
 * Handle admin AJAX requests to the server.
 * @since 1.3.8-beta
 *
 * @package ReallySimpleCMS
 */

// Stop execution if the file is accessed directly
if(empty($_POST)) exit('You do not have permission to access this resource.');

// Only initialize the base files and functions
define('BASE_INIT', true);

require_once dirname(dirname(__DIR__)) . '/init.php';
requireFiles(array(RS_ADMIN_FUNC, RS_FRONT_FUNC));

if(isset($_POST)) {
	// Dismiss an admin notice
	if(isset($_POST['dismiss_notice']) && (bool)$_POST['dismiss_notice']) {
		if(isset($rs_session['dismissed_notices']))
			$rs_session['dismissed_notices'][] = $_POST['notice_id'];
		else
			$rs_session['dismissed_notices'] = array($_POST['notice_id']);
		
		$dismissed = serialize($rs_session['dismissed_notices']);
		
		$rs_query->update(getTable('um'), array(
			'value' => $dismissed
		), array(
			'user' => $rs_session['id'],
			'key' => 'dismissed_notices'
		));
	}
}