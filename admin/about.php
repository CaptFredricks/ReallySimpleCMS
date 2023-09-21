<?php
/**
 * Admin about page.
 * @since 1.3.2[b]
 */
require_once __DIR__ . '/header.php';
?>
<article class="content">
	<section class="heading-wrap">
		<h1>About <?php echo CMS_ENGINE; ?></h1>
	</section>
	<table class="data-table">
		<tbody>
			<?php
			// CREDITS
			echo tableRow(thCell('Credits', 'heading', 2));
			
			echo tableRow(
				thCell('Creator/Lead Developer'),
				tdCell(tag('a', array(
					'href' => 'https://jacefincham.com/',
					'target' => '_blank',
					'rel' => 'noreferrer noopener',
					'content' => 'Jace Fincham'
				)))
			);
			
			echo tableRow(
				thCell('Project Start'),
				tdCell('2019')
			);
			
			// SOFTWARE
			echo tableRow(thCell('Software', 'heading', 2));
			
			echo tableRow(
				thCell('CMS Version'),
				tdCell(CMS_VERSION . ' (Beta)')
			);
			
			echo tableRow(
				thCell('Server'),
				tdCell($_SERVER['SERVER_SOFTWARE'])
			);
			
			echo tableRow(
				thCell('Timezone'),
				tdCell(ini_get('date.timezone'))
			);
			
			echo tableRow(
				thCell('HTTPS Enabled?'),
				tdCell(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
					'Yes' : 'No')
			);
			
			echo tableRow(
				thCell('Minimum PHP Version'),
				tdCell(PHP_MINIMUM)
			);
			
			echo tableRow(
				thCell('Recommended PHP Version'),
				tdCell('&ge;' . PHP_RECOMMENDED)
			);
			
			echo tableRow(
				thCell('Server PHP Version'),
				tdCell(phpversion())
			);
			
			echo tableRow(
				thCell('PHP Max Input Variables'),
				tdCell(ini_get('max_input_vars'))
			);
			
			echo tableRow(
				thCell('PHP Max Execution Time'),
				tdCell(ini_get('max_execution_time'))
			);
			
			echo tableRow(
				thCell('PHP Memory Limit'),
				tdCell(ini_get('memory_limit'))
			);
			
			echo tableRow(
				thCell('PHP Post Max Size'),
				tdCell(ini_get('post_max_size'))
			);
			
			echo tableRow(
				thCell('PHP Upload Max Filesize'),
				tdCell(ini_get('upload_max_filesize'))
			);
			
			echo tableRow(
				thCell('jQuery Version'),
				tdCell(JQUERY_VERSION)
			);
			
			echo tableRow(
				thCell('Font Awesome Icons Version'),
				tdCell(ICONS_VERSION)
			);
			
			// DATABASE
			echo tableRow(thCell('Database', 'heading', 2));
			
			echo tableRow(
				thCell('Server Version'),
				tdCell($rs_query->server_version)
			);
			
			echo tableRow(
				thCell('Client Version'),
				tdCell($rs_query->client_version)
			);
			
			echo tableRow(
				thCell('Database Host'),
				tdCell(DB_HOST)
			);
			
			echo tableRow(
				thCell('Database Name'),
				tdCell(DB_NAME)
			);
			
			echo tableRow(
				thCell('Database Username'),
				tdCell(DB_USER)
			);
			
			echo tableRow(
				thCell('Database Charset'),
				tdCell(DB_CHARSET)
			);
			
			echo tableRow(
				thCell('Database Collation'),
				tdCell(DB_COLLATE)
			);
			
			// SITE STATUS
			echo tableRow(thCell('Site Status', 'heading', 2));
			
			echo tableRow(
				thCell('Maintenance Mode'),
				tdCell(defined('MAINT_MODE') ? (MAINT_MODE ? 'Enabled' : 'Disabled') : 'Undefined')
			);
			
			echo tableRow(
				thCell('Debug Mode'),
				tdCell(defined('DEBUG_MODE') ? (DEBUG_MODE ? 'Enabled' : 'Disabled') : 'Undefined')
			);
			?>
		</tbody>
	</table>
</article>
<?php require_once __DIR__ . '/footer.php'; ?>