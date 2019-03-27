<?php
require_once 'header.php';

$rs_user = new User;
?>

	<div class="wrapper">
		<?php
		$action = $_GET['action'] ?? '';
		$id = intval($_GET['id'] ?? 0);
		
		switch($action) {
			case 'create':
				$rs_user->createEntry();
				break;
			case 'edit':
				$rs_user->editEntry($id);
				break;
			case 'delete':
				$rs_user->deleteEntry($id);
				break;
			case 'reset_password':
				$rs_user->resetPassword($id);
				break;
			default:
				$rs_user->listEntries();
		}
		?>
	</div>

<?php require_once 'footer.php'; ?>