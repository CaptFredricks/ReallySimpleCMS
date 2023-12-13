<?php
/**
 * Admin class used to implement the Widget object. Inherits from the Post class.
 * @since 1.6.0[a]
 *
 * Widgets are used to add small blocks of content to the front end of the website outside of the content area.
 * Widgets can be created, modified, and deleted. They are stored in the 'posts' table as their own post type.
 */
class Widget extends Post implements AdminInterface {
	/**
	 * Class constructor.
	 * @since 1.1.1[b]
	 *
	 * @access public
	 * @param int $id (optional) -- The widget's id.
	 */
	public function __construct(int $id = 0) {
		global $rs_query;
		
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
	public function listRecords(): void {
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
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo exitNotice('The widget was successfully deleted.');
			
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
					domTag('input', array(
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
						tdCell(domTag('input', array(
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
	 * Create a new widget.
	 * @since 1.6.0[a]
	 *
	 * @access public
	 */
	public function createRecord(): void {
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
						'content' => domTag('option', array(
							'value' => 'active',
							'content' => 'Active'
						)) . domTag('option', array(
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
	 * Edit an existing widget.
	 * @since 1.6.1[a]
	 *
	 * @access public
	 */
	public function editRecord(): void {
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
							'content' => domTag('option', array(
								'value' => 'active',
								'selected' => ($this->status === 'active' ? 1 : 0),
								'content' => 'Active'
							)) . domTag('option', array(
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
	 * @param string $status -- The widget's status.
	 * @param int $id (optional) -- The widget's id.
	 */
	public function updateWidgetStatus(string $status, int $id = 0): void {
		global $rs_query;
		
		if($id !== 0) $this->id = $id;
		
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		else {
			$rs_query->update('posts', array('status' => $status), array(
				'id' => $this->id,
				'type' => 'widget'
			));
		}
	}
	
	/**
	 * Delete an existing widget.
	 * @since 1.6.1[a]
	 *
	 * @access public
	 */
	public function deleteRecord(): void {
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
	 * @param array $data -- The submission data.
	 * @param int $id (optional) -- The widget's id.
	 * @return string
	 */
	private function validateData(array $data, int $id = 0): string {
		global $rs_query;
		
		if(empty($data['title']) || empty($data['slug']))
			return exitNotice('REQ', -1);
		
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
			
			return exitNotice('Widget updated! <a href="' . ADMIN_URI . '">Return to list</a>?');
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
				echo domTag('select', array(
					'class' => 'actions',
					'content' => domTag('option', array(
						'value' => 'active',
						'content' => 'Active'
					)) . domTag('option', array(
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