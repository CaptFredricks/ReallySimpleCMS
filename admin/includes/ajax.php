<?php
/**
 * Handle admin AJAX requests to the server.
 * @since 1.3.8[b]
 */

// Only initialize the base files and functions
define('BASE_INIT', true);

// Initialization file
require_once dirname(dirname(__DIR__)) . '/init.php';

// Admin functions
require_once ADMIN_FUNC;

// Functions
require_once FUNC;

// Fetch the user's session data if they're logged in
if(isset($_COOKIE['session']) && isValidSession($_COOKIE['session']))
	$session = getOnlineUser($_COOKIE['session']);

if(isset($_POST)) {
	// Dismiss an admin notice
	if(isset($_POST['dismiss_notice']) && (bool)$_POST['dismiss_notice']) {
		if(isset($session['dismissed_notices']))
			$session['dismissed_notices'][] = $_POST['notice_id'];
		else
			$session['dismissed_notices'] = array($_POST['notice_id']);
		
		$dismissed = serialize($session['dismissed_notices']);
		
		$rs_query->update('usermeta', array('value' => $dismissed), array(
			'user' => $session['id'],
			'_key' => 'dismissed_notices'
		));
	}
}