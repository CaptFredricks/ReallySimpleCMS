<?php
/**
 * Admin class used to implement the Widget object. Inherits from the Post class.
 * @since 1.6.0[a]
 *
 * Widgets are used to add small blocks of content to the front end of the website outside of the content area.
 * Widgets can be created, modified, and deleted. They are stored in the 'posts' table as their own post type.
 */
class Widget extends Post {
	/**
	 * Class constructor.
	 * @since 1.1.1[b]
	 *
	 * @access public
	 * @param int $id (optional; default: 0)
	 * @return null
	 */
	public function __construct($id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Create an array of columns to fetch from the database
		$cols = array_keys(get_object_vars($this));
		
		// Check whether the id is '0'
		if($id !== 0) {
			// Fetch the widget from the database
			$widget = $rs_query->selectRow('posts', $cols, array('id' => $id, 'type' => 'widget'));
			
			// Loop through the array and set the class variables
			foreach($widget as $key => $value) $this->$key = $widget[$key];
		}
	}
	
	/**
	 * Construct a list of all widgets in the database.
	 * @since 1.6.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listWidgets() {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Widgets</h1>
			<?php
			// Check whether the user has sufficient privileges to create widgets and create an action link if so
			if(userHasPrivilege($session['role'], 'can_create_widgets'))
				echo actionLink('create', array('classes' => 'button', 'caption' => 'Create New'));
			
			// Display the page's info
			adminInfo();
			?>
			<hr>
			<?php
			// Check whether any status messages have been returned and display them if so
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The widget was successfully deleted.', true);
			
			// Fetch the widget entry count from the database
			$count = $rs_query->select('posts', 'COUNT(*)', array('type' => 'widget'));
			
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
				$widgets = $rs_query->select('posts', '*', array('type' => 'widget'), 'title', 'ASC', array(
					$page['start'],
					$page['per_page']
				));
				
				// Loop through the widgets
				foreach($widgets as $widget) {
					// Set up the action links
					$actions = array(
						// Edit
						userHasPrivilege($session['role'], 'can_edit_widgets') ? actionLink('edit', array(
							'caption' => 'Edit',
							'id' => $widget['id']
						)) : null,
						// Delete
						userHasPrivilege($session['role'], 'can_delete_widgets') ? actionLink('delete', array(
							'classes' => 'modal-launch delete-item',
							'data_item' => 'widget',
							'caption' => 'Delete',
							'id' => $widget['id']
						)) : null
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						// Title
						tableCell('<strong>'.$widget['title'].'</strong><div class="actions">'.implode(' &bull; ', $actions).'</div>', 'title'),
						// Slug
						tableCell($widget['slug'], 'slug'),
						// Status
						tableCell(ucfirst($widget['status']), 'status')
					);
				}
				
				// Display a notice if no widgets are found
				if(empty($widgets))
					echo tableRow(tableCell('There are no widgets to display.', '', count($table_header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($table_header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
		
		// Include the delete modal
        include_once PATH.ADMIN.INC.'/modal-delete.php';
	}
	
	/**
	 * Create a widget.
	 * @since 1.6.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function createWidget() {
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
					// Title field
					echo formRow(array('Title', true), array(
						'tag' => 'input',
						'id' => 'title-field',
						'class' => 'text-input required invalid init',
						'name' => 'title',
						'value' => ($_POST['title'] ?? '')
					));
					
					// Slug field
					echo formRow(array('Slug', true), array(
						'tag' => 'input',
						'id' => 'slug-field',
						'class' => 'text-input required invalid init',
						'name' => 'slug',
						'value' => ($_POST['slug'] ?? '')
					));
					
					// Content field
					echo formRow('Content', array(
						'tag' => 'textarea',
						'class' => 'textarea-input',
						'name' => 'content',
						'cols' => 30,
						'rows' => 10,
						'content' => htmlspecialchars(($_POST['content'] ?? ''))
					));
					
					// Status dropdown
					echo formRow('Status', array(
						'tag' => 'select',
						'class' => 'select-input',
						'name' => 'status',
						'content' => '<option value="active">Active</option><option value="inactive">Inactive</option>'
					));
					
					echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => 'Create Widget'
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Edit a widget.
	 * @since 1.6.1[a]
	 *
	 * @access public
	 * @return null
	 */
	public function editWidget() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the widget's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Widgets" page
			redirect(ADMIN_URI);
		} else {
			// Validate the form data and return any messages
			$message = isset($_POST['submit']) ? $this->validateData($_POST, $this->id) : '';
			?>
			<div class="heading-wrap">
				<h1>Edit Widget</h1>
				<?php echo $message; ?>
			</div>
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						// Title field
						echo formRow(array('Title', true), array(
							'tag' => 'input',
							'id' => 'title-field',
							'class' => 'text-input required invalid init',
							'name' => 'title',
							'value' => $this->title
						));
						
						// Slug field
						echo formRow(array('Slug', true), array(
							'tag' => 'input',
							'id' => 'slug-field',
							'class' => 'text-input required invalid init',
							'name' => 'slug',
							'value' => $this->slug
						));
						
						// Content field
						echo formRow('Content', array(
							'tag' => 'textarea',
							'class' => 'textarea-input',
							'name' => 'content',
							'cols' => 30,
							'rows' => 10,
							'content' => htmlspecialchars($this->content)
						));
						
						// Status dropdown
						echo formRow('Status', array(
							'tag' => 'select',
							'class' => 'select-input',
							'name' => 'status',
							'content' => '<option value="'.$this->status.'">'.ucfirst($this->status).'</option>'.($this->status === 'active' ? '<option value="inactive">Inactive</option>' : '<option value="active">Active</option>')
						));
						
						echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
						
						// Submit button
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'submit',
							'class' => 'submit-input button',
							'name' => 'submit',
							'value' => 'Update Widget'
						));
						?>
					</table>
				</form>
			</div>
			<?php
		}
	}
	
	/**
	 * Delete a widget.
	 * @since 1.6.1[a]
	 *
	 * @access public
	 * @return null
	 */
	public function deleteWidget() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the widget's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Widgets" page
			redirect(ADMIN_URI);
		} else {
			// Delete the widget from the database
			$rs_query->delete('posts', array('id' => $this->id, 'type' => 'widget'));
			
			// Redirect to the "List Widgets" page with an appropriate exit status
			redirect(ADMIN_URI.'?exit_status=success');
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
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['title']) || empty($data['slug']))
			return statusMessage('R');
		
		// Sanitize the slug (strip off HTML and/or PHP tags and replace any characters not specified in the filter)
		$slug = preg_replace('/[^a-z0-9\-]/', '', strip_tags(strtolower($data['slug'])));
		
		// Make sure the slug is unique
		if($this->slugExists($slug, $id))
			$slug = getUniquePostSlug($slug);
		
		// Make sure the widget has a valid status
		if($data['status'] !== 'active' && $data['status'] !== 'inactive')
			$data['status'] = 'active';
		
		if($id === 0) {
			// Insert the new widget into the database
			$insert_id = $rs_query->insert('posts', array(
				'title' => $data['title'],
				'date' => 'NOW()',
				'content' => $data['content'],
				'status' => $data['status'],
				'slug' => $slug,
				'type' => 'widget'
			));
			
			// Redirect to the appropriate "Edit Widget" page
			redirect(ADMIN_URI.'?id='.$insert_id.'&action=edit');
		} else {
			// Update the widget in the database
			$rs_query->update('posts', array(
				'title' => $data['title'],
				'modified' => 'NOW()',
				'content' => $data['content'],
				'status' => $data['status'],
				'slug' => $slug
			), array('id' => $id));
			
			// Update the class variables
			foreach($data as $key => $value) $this->$key = $value;
			
			// Return a status message
			return statusMessage('Widget updated! <a href="'.ADMIN_URI.'">Return to list</a>?', true);
		}
	}
}