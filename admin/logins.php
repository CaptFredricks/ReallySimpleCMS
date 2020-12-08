<?php
// Include the header
require_once __DIR__.'/header.php';

// Fetch the current logins page
$page = $_GET['page'] ?? '';

// Fetch the current id
$id = (int)($_GET['id'] ?? 0);

// Create a Login object
$rs_login = new Login($page, $id);
?>
<div class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($page) {
		case 'blacklist':
			switch($action) {
				case 'edit':
					// Edit a blacklisted login
					userHasPrivilege($session['role'], 'can_edit_login_blacklist') ? $rs_login->editBlacklist() : redirect(ADMIN_URI.'?page=blacklist');
					break;
				case 'whitelist':
					// Whitelist a blacklisted login or IP address
					userHasPrivilege($session['role'], 'can_delete_login_blacklist') ? $rs_login->whitelistLoginIP() : redirect(ADMIN_URI.'?page=blacklist');
					break;
				default:
					// List the login blacklist
					userHasPrivilege($session['role'], 'can_view_login_blacklist') ? $rs_login->loginBlacklist() : redirect(ADMIN_URI);
			}
			break;
		case 'rules':
			break;
		default:
			switch($action) {
				case 'blacklist_login':
					// Blacklist a user's login
					userHasPrivilege($session['role'], 'can_create_login_blacklist') ? $rs_login->blacklistLogin() : redirect(ADMIN_URI);
					break;
				case 'blacklist_ip':
					// Blacklist a user's IP address
					userHasPrivilege($session['role'], 'can_create_login_blacklist') ? $rs_login->blacklistIPAddress() : redirect(ADMIN_URI);
					break;
				default:
					// List all login attempts
					userHasPrivilege($session['role'], 'can_view_login_attempts') ? $rs_login->loginAttempts() : redirect('index.php');
			}
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';