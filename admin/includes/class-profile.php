<?php
/**
 * Admin class used to implement the Profile object. Inherits from the User class.
 * @since 2.0.0[a]
 *
 * The user profile contains settings for individual users that can be changed at their will.
 */
class Profile extends User {
	/**
	 * Construct the 'Edit Profile' form.
	 * @since 2.0.0[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function editProfile($id) {
		// Extend the Query class
		global $rs_query;
		
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		
		// Fetch the user from the database
		$user = $rs_query->selectRow('users', '*', array('id'=>$id));
		
		// Fetch the user's metadata from the database
		$meta = $this->getUserMeta($id);
		?>
		<div class="heading-wrap">
			<h1>Edit Profile</h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					echo formRow(array('Username', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'username', 'value'=>$user['username']));
					echo formRow(array('Email', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'email', 'value'=>$user['email']));
					echo formRow('First Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'first_name', 'value'=>$meta['first_name']));
					echo formRow('Last Name', array('tag'=>'input', 'class'=>'text-input', 'name'=>'last_name', 'value'=>$meta['last_name']));
					echo formRow('Avatar');
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Profile'));
					?>
				</table>
			</form>
			<a class="reset-password button" href="?action=reset_password">Reset Password</a>
		</div>
		<?php
	}
}