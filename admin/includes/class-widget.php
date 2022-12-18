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
	 */
	public function __construct($id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Create an array of columns to fetch from the database
		$cols = array_keys(get_object_vars($this));
		
		if($id !== 0) {
			$widget = $rs_query->selectRow('posts', $cols, array('id' => $id, 'type' => 'widget'));
			
			// Set the class variable values
			foreach($widget as $key => $value) $this->$key = $widget[$key];
		}
	}
	
	/**
	 * Construct a list of all widgets in the database.
	 * @since 1.6.0[a]
	 *
	 * @access public
	 */
	public function listWidgets(): void {
		// Extend the Query object
		global $rs_query;
		
		// Query vars
		$search = $_GET['search'] ?? null;
		$paged = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Widgets</h1>
			<?php
			// Check whether the user has sufficient privileges to create widgets and create an action link if so
			if(userHasPrivilege('can_create_widgets'))
				echo actionLink('create', array('classes' => 'button', 'caption' => 'Create New'));
			
			recordSearch();
			adminInfo();
			?>
			<hr>
			<?php
			// Check whether any status messages have been returned and display them if so
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The widget was successfully deleted.', true);
			
			if(!is_null($search)) {
				$count = $rs_query->select('posts', 'COUNT(*)', array(
					'title' => array('LIKE', '%' . $search . '%'),
					'type' => 'widget'
				));
			} else {
				$count = $rs_query->select('posts', 'COUNT(*)', array('type' => 'widget'));
			}
			
			$paged['count'] = ceil($count / $paged['per_page']);
			?>
			<div class="entry-count">
				<?php echo $count . ' ' . ($count === 1 ? 'entry' : 'entries'); ?>
			</div>
		</div>
		<table class="data-table has-bulk-select">
			<thead>
				<?php
				$table_header_cols = array(
					tag('input', array(
						'type' => 'checkbox',
						'class' => 'checkbox bulk-selector'
					)),
					'Title',
					'Slug',
					'Status'
				);
				
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				if(!is_null($search)) {
					// Search results
					$widgets = $rs_query->select('posts', '*', array(
						'title' => array('LIKE', '%' . $search . '%'),
						'type' => 'widget'
					), 'title', 'ASC', array(
						$paged['start'],
						$paged['per_page']
					));
				} else {
					// All results
					$widgets = $rs_query->select('posts', '*', array('type' => 'widget'), 'title', 'ASC', array(
						$paged['start'],
						$paged['per_page']
					));
				}
				
				foreach($widgets as $widget) {
					$actions = array(
						// Edit
						userHasPrivilege('can_edit_widgets') ? actionLink('edit', array(
							'caption' => 'Edit',
							'id' => $widget['id']
						)) : null,
						// Delete
						userHasPrivilege('can_delete_widgets') ? actionLink('delete', array(
							'classes' => 'modal-launch delete-item',
							'data_item' => 'widget',
							'caption' => 'Delete',
							'id' => $widget['id']
						)) : null
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						// Bulk select
						tdCell(tag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox',
							'value' => $widget['id']
						)), 'bulk-select'),
						// Title
						tdCell('<strong>' . $widget['title'] . '</strong><div class="actions">' .
							implode(' &bull; ', $actions) . '</div>', 'title'),
						// Slug
						tdCell($widget['slug'], 'slug'),
						// Status
						tdCell(ucfirst($widget['status']), 'status')
					);
				}
				
				if(empty($widgets))
					echo tableRow(tdCell('There are no widgets to display.', '', count($table_header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($table_header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Bulk actions
		if(!empty($widgets)) $this->bulkActions();
		
		// Set up page navigation
		echo pagerNav($paged['current'], $paged['count']);
		
        include_once PATH . ADMIN . INC . '/modal-delete.php';
	}
	
	/**
	 * Create a widget.
	 * @since 1.6.0[a]
	 *
	 * @access public
	 */
	public function createWidget(): void {
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
					// Title
					echo formRow(array('Title', true), array(
						'tag' => 'input',
						'id' => 'title-field',
						'class' => 'text-input required invalid init',
						'name' => 'title',
						'value' => ($_POST['title'] ?? '')
					));
					
					// Slug
					echo formRow(array('Slug', true), array(
						'tag' => 'input',
						'id' => 'slug-field',
						'class' => 'text-input required invalid init',
						'name' => 'slug',
						'value' => ($_POST['slug'] ?? '')
					));
					
					// Content
					echo formRow('Content', array(
						'tag' => 'textarea',
						'class' => 'textarea-input',
						'name' => 'content',
						'cols' => 30,
						'rows' => 10,
						'content' => htmlspecialchars(($_POST['content'] ?? ''))
					));
					
					// Status
					echo formRow('Status', array(
						'tag' => 'select',
						'class' => 'select-input',
						'name' => 'status',
						'content' => tag('option', array(
							'value' => 'active',
							'content' => 'Active'
						)) . tag('option', array(
							'value' => 'inactive',
							'content' => 'Inactive'
						))
					));
					
					// Separator
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
	 */
	public function editWidget(): void {
		// Extend the Query object
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
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
						// Title
						echo formRow(array('Title', true), array(
							'tag' => 'input',
							'id' => 'title-field',
							'class' => 'text-input required invalid init',
							'name' => 'title',
							'value' => $this->title
						));
						
						// Slug
						echo formRow(array('Slug', true), array(
							'tag' => 'input',
							'id' => 'slug-field',
							'class' => 'text-input required invalid init',
							'name' => 'slug',
							'value' => $this->slug
						));
						
						// Content
						echo formRow('Content', array(
							'tag' => 'textarea',
							'class' => 'textarea-input',
							'name' => 'content',
							'cols' => 30,
							'rows' => 10,
							'content' => htmlspecialchars($this->content)
						));
						
						// Status
						echo formRow('Status', array(
							'tag' => 'select',
							'class' => 'select-input',
							'name' => 'status',
							'content' => tag('option', array(
								'value' => 'active',
								'selected' => ($this->status === 'active' ? 1 : 0),
								'content' => 'Active'
							)) . tag('option', array(
								'value' => 'inactive',
								'selected' => ($this->status === 'inactive' ? 1 : 0),
								'content' => 'Inactive'
							))
						));
						
						// Separator
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
	 * Update a widget's status.
	 * @since 1.2.9[b]
	 *
	 * @access public
	 * @param string $status
	 * @param int $id (optional; default: 0)
	 */
	public function updateWidgetStatus($status, $id = 0): void {
		// Extend the Query object
		global $rs_query;
		
		if($id !== 0) $this->id = $id;
		
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		else
			$rs_query->update('posts', array('status' => $status), array('id' => $this->id, 'type' => 'widget'));
	}
	
	/**
	 * Delete a widget.
	 * @since 1.6.1[a]
	 *
	 * @access public
	 */
	public function deleteWidget(): void {
		// Extend the Query object
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			$rs_query->delete('posts', array('id' => $this->id, 'type' => 'widget'));
			
			redirect(ADMIN_URI . '?exit_status=success');
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 1.6.2[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function validateData($data, $id = 0): string {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['title']) || empty($data['slug']))
			return statusMessage('R');
		
		// Sanitize the slug
		$slug = sanitize($data['slug']);
		
		// Make sure the slug is unique
		if($this->slugExists($slug, $id))
			$slug = getUniquePostSlug($slug);
		
		if($data['status'] !== 'active' && $data['status'] !== 'inactive')
			$data['status'] = 'active';
		
		if($id === 0) {
			// New widget
			$insert_id = $rs_query->insert('posts', array(
				'title' => $data['title'],
				'date' => 'NOW()',
				'modified' => 'NOW()',
				'content' => $data['content'],
				'status' => $data['status'],
				'slug' => $slug,
				'type' => 'widget'
			));
			
			redirect(ADMIN_URI . '?id=' . $insert_id . '&action=edit');
		} else {
			// Existing widget
			$rs_query->update('posts', array(
				'title' => $data['title'],
				'modified' => 'NOW()',
				'content' => $data['content'],
				'status' => $data['status'],
				'slug' => $slug
			), array('id' => $id));
			
			// Update the class variables
			foreach($data as $key => $value) $this->$key = $value;
			
			return statusMessage('Widget updated! <a href="' . ADMIN_URI . '">Return to list</a>?', true);
		}
	}
	
	/**
	 * Construct bulk actions.
	 * @since 1.2.9[b]
	 *
	 * @access private
	 */
	private function bulkActions(): void {
		?>
		<div class="bulk-actions">
			<?php
			if(userHasPrivilege('can_edit_widgets')) {
				echo formTag('select', array(
					'class' => 'actions',
					'content' => tag('option', array(
						'value' => 'active',
						'content' => 'Active'
					)) . tag('option', array(
						'value' => 'inactive',
						'content' => 'Inactive'
					))
				));
				
				// Update status
				button(array(
					'class' => 'bulk-update',
					'title' => 'Bulk status update',
					'label' => 'Update'
				));
			}
			
			if(userHasPrivilege('can_delete_widgets')) {
				// Delete
				button(array(
					'class' => 'bulk-delete',
					'title' => 'Bulk delete',
					'label' => 'Delete'
				));
			}
			?>
		</div>
		<?php
	}
}