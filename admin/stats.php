<?php
/**
 * Admin user stats page.
 * @since 1.3.15-beta
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';
?>
<article class="content">
	<section class="heading-wrap">
		<?php
 		domTagPr('h1', array(
 			'content' => $rs_admin_pages['stats']['labels']['list_items'] . ': { ' . domTag('em', array(
				'content' => $rs_session['username']
			)) . ' }'
 		));
 		?>
	</section>
	<table class="data-table">
 		<tbody>
 			<?php userStats(); ?>
 		</tbody>
 	</table>
</article>
<?php
require_once __DIR__ . '/footer.php';