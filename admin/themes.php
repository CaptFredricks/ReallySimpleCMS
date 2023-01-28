<?php
/**
 * Admin themes page.
 * @since 2.3.0[a]
 */
require_once __DIR__ . '/header.php';

// Create a Theme object
$rs_theme = new Theme;
?>
<article class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	// Fetch the theme's name
	$name = $_GET['name'] ?? '';
	
	switch($action) {
		case 'create':
			// Create a new theme
			userHasPrivilege('can_create_themes') ? $rs_theme->createTheme() :
				redirect(ADMIN_URI);
			break;
		case 'activate':
			// Activate an inactive theme
			userHasPrivilege('can_edit_themes') ? $rs_theme->activateTheme($name) :
				redirect(ADMIN_URI);
			break;
		case 'delete':
			// Delete an existing theme
			userHasPrivilege('can_delete_themes') ? $rs_theme->deleteTheme($name) :
				redirect(ADMIN_URI);
			break;
		default:
			// List all themes
			userHasPrivilege('can_view_themes') ? $rs_theme->listThemes() :
				redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';