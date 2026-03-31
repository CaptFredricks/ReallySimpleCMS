<?php
/**
 * Admin logins page. Makes use of the Login object.
 * @since 1.2.0-beta_snap-01
 *
 * @package ReallySimpleCMS
 */

require_once __DIR__ . '/header.php';

// Query vars
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
$page = $_GET['page'] ?? 'attempts';

$rs_ad_login = new \Admin\Login($id, $action, $page);
?>
<article class="content">
	<?php
	switch($page) {
		case 'blacklist':
			switch($action) {
				case 'create':
					// Action: Create Login Blacklist
					userHasPrivilege('can_create_login_blacklist') ?
						$rs_ad_login->createBlacklist() :
							redirect(ADMIN_URI . getQueryString(array(
								'page' => 'blacklist'
							)));
					break;
				case 'edit':
					// Action: Edit Login Blacklist
					userHasPrivilege('can_edit_login_blacklist') ?
						$rs_ad_login->editBlacklist() :
							redirect(ADMIN_URI . getQueryString(array(
								'page' => 'blacklist'
							)));
					break;
				case 'whitelist':
					// Action: Whitelist Login Blacklist
					userHasPrivilege('can_delete_login_blacklist') ?
						$rs_ad_login->whitelistLoginIP() :
							redirect(ADMIN_URI . getQueryString(array(
								'page' => 'blacklist'
							)));
					break;
				default:
					// Action: List Login Blacklist
					userHasPrivilege('can_view_login_blacklist') ?
						$rs_ad_login->loginBlacklist() :
							redirect(ADMIN_URI);
			}
			break;
		case 'rules':
			switch($action) {
				case 'create':
					// Action: Create Login Rule
					userHasPrivilege('can_create_login_rules') ?
						$rs_ad_login->createRule() :
							redirect(ADMIN_URI . getQueryString(array(
								'page' => 'rules'
							)));
					break;
				case 'edit':
					// Action: Edit Login Rule
					userHasPrivilege('can_edit_login_rules') ?
						$rs_ad_login->editRule() :
							redirect(ADMIN_URI . getQueryString(array(
								'page' => 'rules'
							)));
					break;
				case 'delete':
					// Action: Delete Login Rule
					userHasPrivilege('can_delete_login_rules') ?
						$rs_ad_login->deleteRule() :
							redirect(ADMIN_URI . getQueryString(array(
								'page' => 'rules'
							)));
					break;
				default:
					// Action: List Login Rules
					userHasPrivilege('can_view_login_rules') ?
						$rs_ad_login->loginRules() :
							redirect(ADMIN_URI);
			}
			break;
		default:
			switch($action) {
				case 'blacklist_login':
					// Action: Blacklist Login
					userHasPrivilege('can_create_login_blacklist') ?
						$rs_ad_login->blacklistLogin() :
							redirect(ADMIN_URI);
					break;
				case 'blacklist_ip':
					// Action: Blacklist IP Address
					userHasPrivilege('can_create_login_blacklist') ?
						$rs_ad_login->blacklistIPAddress() :
							redirect(ADMIN_URI);
					break;
				default:
					// Action: List Login Attempts
					userHasPrivilege('can_view_login_attempts') ?
						$rs_ad_login->loginAttempts() :
							redirect('index.php');
			}
	}
	?>
</article>
<?php
require_once __DIR__ . '/footer.php';