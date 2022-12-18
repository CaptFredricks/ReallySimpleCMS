<?php
require_once __DIR__ . '/header.php';
?>
<div class="content">
	<div class="heading-wrap">
		<h1>Admin Dashboard</h1>
	</div>
	<?php statsBarGraph(); ?>
	<div>
		<?php getSetting('enable_comments') ? dashboardWidget('comments') : null; ?>
		<?php dashboardWidget('users'); ?>
		<?php getSetting('track_login_attempts') ? dashboardWidget('logins') : null; ?>
	</div>
</div>
<?php
require_once __DIR__ . '/footer.php';