<?php
/**
 * Admin update page.
 * @since 1.4.0-beta_snap-01
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';
?>
<article class="content">
	<section class="heading-wrap">
		<h1>Update <?php echo CMS_ENGINE; ?></h1>
	</section>
	<section>
		<?php
		if(version_compare(CMS_VERSION, $rs_api_fetch->getVersion(), '<')) {
			echo domTag('p', array(
				'content' => 'An update is available for ' . CMS_ENGINE . '. You\'re running ' . domTag('strong', array(
					'content' => CMS_VERSION
				)) . ', and the latest version is ' . domTag('strong', array(
					'content' => $rs_api_fetch->getVersion()
				)) . '.'
			));
			
			echo domTag('form', array(
				'method' => 'post',
				'content' => domTag('input', array(
					'type' => 'hidden',
					'name' => 'do_update',
					'value' => 1 // check should come from here
				)) . domTag('input', array(
					'type' => 'submit',
					'class' => 'submit-input button',
					'name' => 'submit',
					'value' => 'Update Core'
				))
			));
		} else {
			echo domTag('p', array(
				'content' => CMS_ENGINE . ' is up to date.'
			));
		}
		?>
	</section>
</article>
<?php
require_once __DIR__ . '/footer.php';