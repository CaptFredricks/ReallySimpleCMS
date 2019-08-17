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
		$page = paginate((int)($_GET['page'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Widgets</h1>
			<a class="button" href="?action=create">Create New</a>
			<?php
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('Widget was successfully deleted.', true);
			
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
				$table_header_cols = array('Title', 'Slug');
				
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
					// Construct the current row
					echo tableRow(
						tableCell('<strong>'.$widget['title'].'</strong><div class="actions"><a href="?id='.$widget['id'].'&action=edit">Edit</a> &bull; <a href="?id='.$widget['id'].'&action=delete">Delete</a></div>', 'title'),
						tableCell($widget['slug'], 'slug')
					);
				}
				
				// Display a notice if no widgets are found
				if(count($widgets) === 0)
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
			<?php
			// Display status messages
			echo $message;
			?>
		</div>
		<form class="data-form" action="" method="post" autocomplete="off">
			<table class="form-table">
				<?php
				// Display form rows
				echo formRow(array('Title', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'title', 'value'=>($_POST['title'] ?? '')));
				echo formRow(array('Slug', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>($_POST['slug'] ?? '')));
				echo formRow('Content', array('tag'=>'textarea', 'class'=>'textarea-input', 'name'=>'content', 'cols'=>30, 'rows'=>10, 'content'=>(isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '')));
				echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
				echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Create Widget'));
				?>
			</table>
		</form>
		<?php
	}
}