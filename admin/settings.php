<?php
// Include header
require_once __DIR__.'/header.php';

// Create a Settings object
$rs_settings = new Settings;
?>
<div class="wrapper">
	<?php
	// List all settings
	$rs_settings->listSettings();
	?>
</div>
<?php
// Include footer
require_once __DIR__.'/footer.php';
?>