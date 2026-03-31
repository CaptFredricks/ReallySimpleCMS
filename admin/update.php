<?php
/**
 * Admin update page. Makes use of the Update object.
 * @since 1.4.0-beta_snap-01
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$type = $_GET['type'] ?? '';
$name = $_GET['name'] ?? '';
$action = $_GET['action'] ?? '';

$rs_ad_update = new \Admin\Update($type, $name, $action);
?>
<article class="content">
	<section class="heading-wrap">
		<?php
		domTagPr('h1', array(
			'content' => 'Update ' . RS_ENGINE
		));
		?>
	</section>
	<section>
		<?php
		domTagPr('p', array(
			'content' => 'Updating software is critical in order to patch vulnerabilities in code and access the latest features. Updates can take up to several minutes to complete, so please be patient.'
		));
		
		domTagPr('hr', array(
			'class' => 'separator'
		));
		
		// Core software
		$rs_ad_update->listCoreUpdates();
		
		domTagPr('hr', array(
			'class' => 'separator'
		));
		
		// Modules
		$rs_ad_update->listModuleUpdates();
		
		domTagPr('hr', array(
			'class' => 'separator'
		));
		
		// Themes
		$rs_ad_update->listThemeUpdates();
		
		domTagPr('hr', array(
			'class' => 'separator'
		));
		
		// Admin Themes
		$rs_ad_update->listAdminThemeUpdates();
		?>
	</section>
</article>
<?php
require_once __DIR__ . '/footer.php';