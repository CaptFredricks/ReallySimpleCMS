<?php
/**
 * Admin class used to implement the Widget object. Child class of the Post class.
 * @since 1.6.0[a]
 *
 * Widgets are used to add small blocks of content to the front end of the website outside of the content area.
 * Widgets can be created, modified, and deleted. They are stored in the 'posts' table as their own post type.
 */
class Widget extends Post {
	/**
	 * Construct a list of all widgets in the database.
	 * @since 1.6.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listEntries() {
		// Extend the Query class
		global $rs_query;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Widgets</h1>
			<a class="button" href="?action=create">Create New</a>
			<?php
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The widget was successfully deleted.', true);
			
			// Fetch the widget entry count from the database
			$count = $rs_query->select('posts', 'COUNT(*)', array('type'=>'widget'));
			
			// Set the page count
			$page['count'] = ceil($count / $page['per_page']);
			?>
			<div class="entry-count">
				<?php
				// Display the entry count
				echo $count.' '.($count === 1 ? 'entry' : 'entries');
				?>
			</div>
		</div>
		<table class="data-table">
			<thead>
				<?php
				// Fill an array with the table header columns
				$table_header_cols = array('Title', 'Slug', 'Status');
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all widgets from the database
				$widgets = $rs_query->select('posts', '*', array('type'=>'widget'), 'title', 'ASC', array($page['start'], $page['per_page']));
				
				// Loop through the widgets
				foreach($widgets as $widget) {
					echo tableRow(
						tableCell('<strong>'.$widget['title'].'</strong><div class="actions"><a href="?id='.$widget['id'].'&action=edit">Edit</a> &bull; <a href="?id='.$widget['id'].'&action=delete">Delete</a></div>', 'title'),
						tableCell($widget['slug'], 'slug'),
						tableCell(ucfirst($widget['status']), 'status')
					);
				}
				
				// Display a notice if no widgets are found
				if(empty($widgets))
					echo tableRow(tableCell('There are no widgets to display.', '', count($table_header_cols)));
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
	}
	
	/**
	 * Construct the 'Create Widget' form.
	 * @since 1.6.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function createEntry() {
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1>Create Widget</h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					echo formRow(array('Title', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'title', 'value'=>($_POST['title'] ?? '')));
					echo formRow(array('Slug', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>($_POST['slug'] ?? '')));
					echo formRow('Content', array('tag'=>'textarea', 'class'=>'textarea-input', 'name'=>'content', 'cols'=>30, 'rows'=>10, 'content'=>(isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '')));
					echo formRow('Status', array('tag'=>'select', 'class'=>'select-input', 'name'=>'status', 'content'=>'<option value="active">Active</option><option value="inactive">Inactive</option>'));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create Widget'));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Construct the 'Edit Widget' form.
	 * @since 1.6.1[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function editEntry($id) {
		// Extend the Query class
		global $rs_query;
		
		// Check whether or not the widget id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Widgets' page
			redirect('widgets.php');
		} else {
			// Fetch the number of times the widget appears in the database
			$count = $rs_query->selectRow('posts', 'COUNT(*)', array('id'=>$id, 'type'=>'widget'));
			
			// Check whether or not the count is zero
			if($count === 0) {
				// Redirect to the 'List Widgets' page
				redirect('widgets.php');
			} else {
				// Validate the form data and return any messages
				$message = isset($_POST['submit']) ? $this->validateData($_POST, $id) : '';
				
				// Fetch the widget from the database
				$widget = $rs_query->selectRow('posts', '*', array('id'=>$id, 'type'=>'widget'));
				?>
				<div class="heading-wrap">
					<h1>Edit Widget</h1>
					<?php echo $message; ?>
				</div>
				<div class="data-form-wrap clear">
					<form class="data-form" action="" method="post" autocomplete="off">
						<table class="form-table">
							<?php
							echo formRow(array('Title', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'title', 'value'=>$widget['title']));
							echo formRow(array('Slug', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>$widget['slug']));
							echo formRow('Content', array('tag'=>'textarea', 'class'=>'textarea-input', 'name'=>'content', 'cols'=>30, 'rows'=>10, 'content'=>htmlspecialchars($widget['content'])));
							echo formRow('Status', array('tag'=>'select', 'class'=>'select-input', 'name'=>'status', 'content'=>'<option value="'.$widget['status'].'">'.ucfirst($widget['status']).'</option>'.($widget['status'] === 'active' ? '<option value="inactive">Inactive</option>' : '<option value="active">Active</option>')));
							echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
							echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Widget'));
							?>
						</table>
					</form>
				</div>
				<?php
			}
		}
	}
	
	/**
	 * Delete a widget from the database.
	 * @since 1.6.1[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function deleteEntry($id) {
		// Extend the Query class
		global $rs_query;
		
		// Check whether or not the widget id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Widgets' page
			redirect('widgets.php');
		} else {
			// Delete the widget from the database
			$rs_query->delete('posts', array('id'=>$id, 'type'=>'widget'));
			
			// Redirect to the 'List Posts' page (with a success message)
			redirect('widgets.php?exit_status=success');
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 1.6.2[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return null|string (null on $id == 0; string on $id != 0)
	 */
	private function validateData($data, $id = 0) {
		// Extend the Query class
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['title']) || empty($data['slug']))
			return statusMessage('R');
		
		// Make sure the slug is not already being used
		if($this->slugExists($data['slug'], $id))
			return statusMessage('That slug is already in use. Please choose another one.');
		
		// Make sure the widget has a valid status
		if($data['status'] !== 'active' && $data['status'] !== 'inactive')
			$data['status'] = 'active';
		
		if($id === 0) {
			// Insert the new widget into the database
			$insert_id = $rs_query->insert('posts', array('title'=>$data['title'], 'date'=>'NOW()', 'content'=>$data['content'], 'status'=>$data['status'], 'slug'=>$data['slug'], 'type'=>'widget'));
			
			// Redirect to the 'Edit Widget' page
			redirect('widgets.php?id='.$insert_id.'&action=edit');
		} else {
			// Update the widget in the database
			$rs_query->update('posts', array('title'=>$data['title'], 'modified'=>'NOW()', 'content'=>$data['content'], 'status'=>$data['status'], 'slug'=>$data['slug']), array('id'=>$id));
			
			// Return a status message
			return statusMessage('Widget updated! <a href="widgets.php">Return to list</a>?', true);
		}
	}
}