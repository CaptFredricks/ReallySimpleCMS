<?php
/**
 * Admin about page.
 * @since 1.3.2-beta
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$active_tab = $_GET['tab'] ?? '';
?>
<article class="content">
	<?php
	domTagPr('section', array(
		'class' => 'heading-wrap',
		'content' => domTag('h1', array(
			'content' => $rs_admin_pages['about']['title']
		))
	));
	
	$tabs = array('stats', 'software', 'credits');
	$tabber_content = array();
	$is_active = 'stats';
	
	foreach($tabs as $tab) {
		$tabber_content[] = domTag('li', array(
			'id' => $tab,
			'class' => 'tab' . ($tab === $is_active ? ' active' : ''),
			'content' => ucfirst($tab)
		));
	}
	
	domTagPr('div', array(
		'class' => 'tabber-nav',
		'content' => domTag('ul', array(
			'class' => 'tabber',
			'content' => implode('', $tabber_content)
		))
	));
	?>
	<table class="data-table has-tabber active" data-tab="stats">
		<tbody>
			<?php aboutTabStats(); ?>
		</tbody>
	</table>
	<table class="data-table has-tabber" data-tab="software">
		<tbody>
			<?php aboutTabSoftware(); ?>
		</tbody>
	</table>
	<table class="data-table has-tabber" data-tab="credits">
		<tbody>
			<?php aboutTabCredits(); ?>
		</tbody>
	</table>
</article>
<?php
require_once __DIR__ . '/footer.php';