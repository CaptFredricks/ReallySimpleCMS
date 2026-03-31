<?php
/**
 * Admin modules page. Makes use of the Module object.
 * @since 1.4.0-beta_snap-03
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$name = $_GET['name'] ?? '';
$action = $_GET['action'] ?? '';

$rs_ad_module = new \Admin\Module($name, $action, $rs_modules[$name] ?? array());
?>
<article class="content">
	<?php
	switch($action) {
		case 'install':
			// Action: Install Module
			/* userHasPrivilege('can_install_modules') ? $rs_media->uploadRecordMedia() :
				redirect(ADMIN_URI); */
			$rs_ad_module->installModule();
			break;
		case 'activate':
			// Action: Activate Module
			$rs_ad_module->activateModule();
			break;
		case 'deactivate':
			// Action: Deactivate Module
			$rs_ad_module->deactivateModule();
			break;
		case 'update':
			// Action: Update Module
			/* userHasPrivilege('can_update_modules') ? $rs_media->editRecordMedia() :
				redirect(ADMIN_URI); */
			$rs_ad_module->updateModule();
			break;
		case 'uninstall':
			// Action: Delete Module
			/* userHasPrivilege('can_delete_modules') ? $rs_media->deleteRecordMedia() :
				redirect(ADMIN_URI); */
			#$rs_ad_module->uninstallModule();
			break;
		default:
			// Action: List Modules
			/* userHasPrivilege('can_view_modules') ? $rs_ad_module->listRecords() :
				redirect('index.php'); */
			$rs_ad_module->listRecords();
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';

/*
if(defined('DOMTAGS_VERSION')) {
					echo tableRow(
						thCell('DOMtags Version'),
						tdCell(DOMTAGS_VERSION . ' (' . domTag('a', array(
							'href' => 'https://github.com/CaptFredricks/DOMtags',
							'target' => '_blank',
							'rel' => 'noreferrer noopener',
							'content' => 'GitHub Repo'
						)) . ')')
					);
				} */