<?php
/**
 * Admin dashboard page.
 * @since 1.0.2[a]
 */
require_once __DIR__ . '/header.php';
?>
<article class="content">
	<?php
	// Fetch the current action
	$action = $_GET['action'] ?? '';
	
	switch($action) {
		case 'unhide_notices':
			$rs_notice = new Notice;
			
			$rs_notice->unhide($session['id']);
			
			redirect(ADMIN_URI);
			break;
		default:
			?>
			<section class="heading-wrap">
				<h1>Admin Dashboard</h1>
			</section>
			<section>
				<p>Welcome to the administrative dashboard for <?php putSetting('site_title'); ?>.</p>
				<?php
				// Notices
				if($session['dismissed_notices'] !== false) {
					$hidden = count($session['dismissed_notices']);
					
					$message = 'You have ' . $hidden . ' hidden ' . ($hidden === 1 ? 'notice' : 'notices') .
						'. <a href="' . ADMIN_URI . '?action=unhide_notices">Click here</a> to unhide ' . ($hidden === 1 ? 'it' : 'them') . '.';
					
					echo notice($message, 2, false, true);
				}
				
				if(ctDraft() > 0 || ctDraft('page') > 0) {
					$message = 'You have ' . ctDraft('page') . ' unpublished <a href="/admin/posts.php?type=page&status=draft">pages</a> and ' . ctDraft() . ' unpublished <a href="/admin/posts.php?status=draft">posts</a>.';
					
					if(!isDismissedNotice($message, $session['dismissed_notices']))
						echo notice($message, 0);
				}
				?>
			</section>
			<section>
				<?php statsBarGraph(); ?>
			</section>
			<section class="clear">
				<?php getSetting('enable_comments') ? dashboardWidget('comments') : null; ?>
				<?php dashboardWidget('users'); ?>
				<?php getSetting('track_login_attempts') ? dashboardWidget('logins') : null; ?>
			</section>
			<?php
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';