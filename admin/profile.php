<?php
// Include the header
require_once __DIR__.'/header.php';

// Create a Profile object
$rs_profile = new Profile;
?>
<div class="wrapper clear">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the user's session id
	$id = $_SESSION['id'];
	
	switch($action) {
		default:
			// Edit profile
			$rs_profile->editProfile($id);
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';