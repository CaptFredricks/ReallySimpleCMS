<?php
// Include header
require_once __DIR__.'/header.php';

// Create a Settings object
$rs_settings = new Settings;
?>
<div class="wrapper clear">
	<?php
	// Fetch the current settings page
	$page = $_GET['page'] ?? '';
	
	switch($page) {
		case 'user_roles':
			// User roles settings
			$rs_settings->userRolesSettings();
			break;
		default:
			// General settings
			$rs_settings->generalSettings();
	}
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';