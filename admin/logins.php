<?php
// Include the header
require_once __DIR__.'/header.php';

// Fetch the current id
$id = (int)($_GET['id'] ?? 0);

// Create a Login object
$rs_login = new Login($id);
?>
<div class="content">
	<?php
	// Fetch the current logins page
	$page = $_GET['page'] ?? '';
	
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($page) {
		case 'blacklist':
			switch($action) {
				case 'edit':
					break;
				case 'whitelist':
					break;
				default:
					// Login blacklist
					$rs_login->loginBlacklist();
			}
			break;
		case 'rules':
			break;
		default:
			switch($action) {
				case 'blacklist_login':
					// Blacklist a user's login
					$rs_login->blacklistLogin();
					break;
				case 'blacklist_ip':
					// Blacklist a user's IP address
					$rs_login->blacklistIPAddress();
					break;
				default:
					// List all login attempts
					$rs_login->loginAttempts();
			}
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';