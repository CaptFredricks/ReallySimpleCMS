<?php
// Include the header
require_once __DIR__.'/header.php';

// Create a Theme object
$rs_theme = new Theme;
?>
<div class="wrapper clear">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the theme's id
	$id = (int)($_GET['id'] ?? 0);
	
	switch($action) {
		case 'create':
			// Create a new theme
			userHasPrivilege($session['role'], 'can_create_themes') ? $rs_theme->createTheme() : redirect('themes.php');
			break;
		case 'edit':
			// Edit an existing theme
			userHasPrivilege($session['role'], 'can_edit_themes') ? $rs_theme->editTheme($id) : redirect('themes.php');
			break;
		case 'delete':
			// Delete an existing theme
			userHasPrivilege($session['role'], 'can_delete_themes') ? $rs_theme->deleteTheme($id) : redirect('themes.php');
			break;
		default:
			// List all themes
			userHasPrivilege($session['role'], 'can_view_themes') ? $rs_theme->listThemes() : redirect('index.php');
	}
	?>
</div>
<?php
// Include the footer
require_once __DIR__.'/footer.php';