<?php
/**
 * Admin themes page. Makes use of the Theme object.
 * @since 2.3.0-alpha
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$name = $_GET['name'] ?? '';
$action = $_GET['action'] ?? '';

$rs_ad_theme = new \Admin\Theme($name, $action);
?>
<article class="content">
	<?php
	switch($action) {
		case 'create':
			// Action: Create Theme
			userHasPrivilege('can_create_themes') ?
				$rs_ad_theme->createTheme() :
					redirect(ADMIN_URI);
			break;
		case 'activate':
			// Action: Activate Theme
			userHasPrivilege('can_edit_themes') ?
				$rs_ad_theme->activateTheme() :
					redirect(ADMIN_URI);
			break;
		case 'delete':
			// Action: Delete Theme
			userHasPrivilege('can_delete_themes') ?
				$rs_ad_theme->deleteTheme() :
					redirect(ADMIN_URI);
			break;
		default:
			// Action: List Themes
			userHasPrivilege('can_view_themes') ?
				$rs_ad_theme->listThemes() :
					redirect('index.php');
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';