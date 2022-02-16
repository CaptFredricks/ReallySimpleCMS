<?php require_once __DIR__.'/header.php'; ?>
<div class="content">
	<div class="heading-wrap">
		<h1>About <?php echo CMS_NAME; ?></h1>
	</div>
	<table class="data-table">
		<tbody>
			<?php
			echo tableRow(thCell('Credits', 'heading', 2));
			
			echo tableRow(
				thCell('Creator/Lead Developer'),
				tdCell('<a href="https://jacefincham.com/" target="_blank" rel="noreferrer noopener">Jace Fincham</a>')
			);
			
			echo tableRow(
				thCell('Project Start'),
				tdCell('2019')
			);
			
			echo tableRow(thCell('Software', 'heading', 2));
			
			echo tableRow(
				thCell('CMS Version'),
				tdCell(VERSION.' (Beta)')
			);
			
			echo tableRow(
				thCell('Minimum PHP Version'),
				tdCell(PHP_MINIMUM)
			);
			
			echo tableRow(
				thCell('Recommended PHP Version'),
				tdCell(PHP_RECOMMENDED)
			);
			
			echo tableRow(
				thCell('Server PHP Version'),
				tdCell(phpversion())
			);
			
			echo tableRow(
				thCell('jQuery Version'),
				tdCell(JQUERY_VERSION)
			);
			
			echo tableRow(
				thCell('Font Awesome Icons Version'),
				tdCell(ICONS_VERSION)
			);
			?>
		</tbody>
	</table>
</div>
<?php require_once __DIR__.'/footer.php'; ?>