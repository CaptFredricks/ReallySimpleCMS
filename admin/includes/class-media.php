<?php
/**
 * Admin class used to implement the Media object. Inherits from the Post class.
 * @since 2.1.0[a]
 *
 * Media includes images, videos, and documents. These can be used anywhere on the front end of the site.
 * Media can be uploaded, modified, and deleted. Media are stored in the 'posts' table as the 'media' post type.
 */
class Media extends Post {
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
		
		// Check whether the id is '0'
		if($id !== 0) {
			// Fetch the media from the database
			$media = $rs_query->selectRow('posts', $cols, array('id' => $id, 'type' => 'media'));
			
			// Set the class variable values
			foreach($media as $key => $value) $this->$key = $media[$key];
		}
	}
	
	/**
	 * Construct a list of all media in the database.
	 * @since 2.1.0[a]
	 *
	 * @access public
	 */
	public function listMedia(): void {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Media</h1>
			<?php
			// Check whether the user has sufficient privileges to upload media and create an action link if so
			if(userHasPrivilege($session['role'], 'can_upload_media'))
				echo actionLink('upload', array('classes' => 'button', 'caption' => 'Upload New'));
			
			// Display the page's info
			adminInfo();
			?>
			<hr>
			<?php
			// Check whether any status messages have been returned
			if(isset($_GET['exit_status'])) {
				// Choose an appropriate status message based upon the exit status
				if($_GET['exit_status'] === 'success') {
					// Display a success status message
					echo statusMessage('The media was successfully deleted.', true);
				} elseif($_GET['exit_status'] === 'failure') {
					// Check whether there are conflicts
					if(isset($_GET['conflicts'])) {
						// Create an array with the conflicts
						$conflicts = explode(':', $_GET['conflicts']);
						
						// Check whether the conflict is with the users table
						if(in_array('users', $conflicts, true))
							$message[] = 'That media is currently a <strong><em>user\'s avatar</em></strong>. If you wish to delete it, unlink it from the user first.';
						
						// Check whether the conflict is with the posts table
						if(in_array('posts', $conflicts, true))
							$message[] = 'That media is currently a <strong><em>post\'s featured image</em></strong>. If you wish to delete it, unlink it from the post first.';
						
						// Display the failure status message
						echo statusMessage(implode('<br>', $message));
					} else {
						// Display a success status message
						echo statusMessage('The media could not be deleted!');
					}
				}
			}
			
			// Fetch the media entry count from the database
			$count = $rs_query->select('posts', 'COUNT(*)', array('type' => 'media'));
			
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
				$table_header_cols = array(
					'Thumbnail',
					'File',
					'Author',
					'Upload Date',
					'Size',
					'Dimensions',
					'MIME Type'
				);
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all media from the database
				$mediaa = $rs_query->select('posts', '*', array('type' => 'media'), 'date', 'DESC', array(
					$page['start'],
					$page['per_page']
				));
				
				foreach($mediaa as $media) {
					// Fetch the media's metadata from the database
					$meta = $this->getPostMeta($media['id']);
					
					// Set up the action links
					$actions = array(
						// Edit
						userHasPrivilege($session['role'], 'can_edit_media') ? actionLink('edit', array(
							'caption' => 'Edit',
							'id' => $media['id']
						)) : null,
						// Delete
						userHasPrivilege($session['role'], 'can_delete_media') ? actionLink('delete', array(
							'classes' => 'modal-launch delete-item',
							'data_item' => 'media',
							'caption' => 'Delete',
							'id' => $media['id']
						)) : null,
						// View
						mediaLink($media['id'], array('link_text' => 'View', 'newtab' => 1))
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					// Get the media's filepath
					$file_path = trailingSlash(PATH.UPLOADS).$meta['filename'];
					
					// Check whether the media exists
					if(file_exists($file_path)) {
						// Fetch the media's file size
						$size = getFileSize(filesize($file_path));
						
						// Check whether the media is an image
						if(str_starts_with(mime_content_type($file_path), 'image')) {
							// Fetch the image's dimensions
							list($width, $height) = getimagesize($file_path);
							
							// Set the dimensions
							$dimensions = $width.' x '.$height;
						} else {
							// Set the dimensions to null
							$dimensions = null;
						}
					} else {
						// Set the file size to null
						$size = null;
					}
					
					echo tableRow(
						// Thumbnail
						tdCell(getMedia($media['id']), 'thumbnail'),
						// File
						tdCell('<strong>'.$media['title'].'</strong><br><em>'.$meta['filename'].'</em><div class="actions">'.implode(' &bull; ', $actions).'</div>', 'file'),
						// Author
						tdCell($this->getAuthor($media['author']), 'author'),
						// Upload date
						tdCell(formatDate($media['date'], 'd M Y @ g:i A'), 'upload-date'),
						// Size
						tdCell($size ?? '0 B', 'size'),
						// Dimensions
						tdCell($dimensions ?? '&mdash;', 'dimensions'),
						// MIME type
						tdCell($meta['mime_type'], 'mime-type')
					);
				}
				
				// Display a notice if no media are found
				if(empty($mediaa))
					echo tableRow(tdCell('There are no media to display.', '', count($table_header_cols)));
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
	 * Upload some media.
	 * @since 2.1.0[a]
	 *
	 * @access public
	 */
	public function uploadMedia(): void {
		// Check whether the form has been submitted
		if(isset($_POST['submit'])) {
			// Merge the $_POST and $_FILES arrays
			$data = array_merge($_POST, $_FILES);
		
			// Validate the form data and return any messages
			$message = $this->validateData($data, $_GET['action']);
		}
		?>
		<div class="heading-wrap">
			<h1>Upload Media</h1>
			<?php echo $message ?? ''; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off" enctype="multipart/form-data">
				<table class="form-table">
					<?php
					// Title
					echo formRow(array('Title', true), array(
						'tag' => 'input',
						'class' => 'text-input required invalid init',
						'name' => 'title',
						'value' => ($_POST['title'] ?? '')
					));
					
					// File
					echo formRow(array('File', true), array(
						'tag' => 'input',
						'type' => 'file',
						'id' => 'file-upl',
						'class' => 'file-input required invalid init',
						'name' => 'file'
					));
					
					// Alt text
					echo formRow('Alt Text', array(
						'tag' => 'input',
						'class' => 'text-input',
						'name' => 'alt_text',
						'value' => ($_POST['alt_text'] ?? '')
					));
					
					// Description
					echo formRow('Description', array(
						'tag' => 'textarea',
						'class' => 'textarea-input',
						'name' => 'description',
						'cols' => 30,
						'rows' => 10,
						'content' => htmlspecialchars(($_POST['description'] ?? ''))
					));
					
					// Separator
					echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => 'Upload Media'
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Edit some media.
	 * @since 2.1.0[a]
	 *
	 * @access public
	 */
	public function editMedia(): void {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the media's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Media" page
			redirect(ADMIN_URI);
		} else {
			// Validate the form data and return any messages
			$message = isset($_POST['submit']) ? $this->validateData($_POST, $_GET['action'], $this->id) : '';
			
			// Fetch the media's metadata from the database
			$meta = $this->getPostMeta($this->id);
			?>
			<div class="heading-wrap">
				<h1>Edit Media</h1>
				<?php echo $message; ?>
			</div>
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						// Thumbnail
						echo formRow('Thumbnail', array(
							'tag' => 'div',
							'class' => 'thumb-wrap',
							'content' => getMedia($this->id, array('class' => 'media-thumb'))
						));
						
						// Title
						echo formRow(array('Title', true), array(
							'tag' => 'input',
							'class' => 'text-input required invalid init',
							'name' => 'title',
							'value' => $this->title
						));
						
						// Alt text
						echo formRow('Alt Text', array(
							'tag' => 'input',
							'class' => 'text-input',
							'name' => 'alt_text',
							'value' => $meta['alt_text']
						));
						
						// Description
						echo formRow('Description', array(
							'tag' => 'textarea',
							'class' => 'textarea-input',
							'name' => 'description',
							'cols' => 30,
							'rows' => 10,
							'content' => htmlspecialchars($this->content)
						));
						
						// Separator
						echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
						
						// Submit button
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'submit',
							'class' => 'submit-input button',
							'name' => 'submit',
							'value' => 'Update Media'
						));
						?>
					</table>
				</form>
				<?php echo actionLink('replace', array(
					'classes' => 'replace-media button',
					'caption' => 'Replace Media',
					'id' => $this->id
				)); ?>
			</div>
			<?php
		}
	}
	
	/**
	 * Replace some media.
	 * @since 1.2.3[b]
	 *
	 * @access public
	 */
	public function replaceMedia(): void {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the media's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Media" page
			redirect(ADMIN_URI);
		} else {
			// Check whether the form has been submitted
			if(isset($_POST['submit'])) {
				// Merge the $_POST and $_FILES arrays
				$data = array_merge($_POST, $_FILES);
				
				// Validate the form data and return any messages
				$message = $this->validateData($data, $_GET['action'], $this->id);
			}
			
			// Fetch the media's metadata from the database
			$meta = $this->getPostMeta($this->id);
			?>
			<div class="heading-wrap">
				<h1>Replace Media</h1>
				<?php echo $message ?? ''; ?>
			</div>
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off" enctype="multipart/form-data">
					<table class="form-table">
						<?php
						// Thumbnail
						echo formRow('Thumbnail', array(
							'tag' => 'div',
							'class' => 'thumb-wrap',
							'content' => getMedia($this->id, array('class' => 'media-thumb'))
						));
						
						// Title
						echo formRow(array('Title', true), array(
							'tag' => 'input',
							'class' => 'text-input required invalid init',
							'name' => 'title',
							'value' => $this->title
						));
						
						// File
						echo formRow(array('File', true), array(
							'tag' => 'input',
							'type' => 'file',
							'id' => 'file-upl',
							'class' => 'file-input required invalid init',
							'name' => 'file'
						));
						
						// Metadata
						echo formRow('Metadata', array(
							'tag' => 'input',
							'type' => 'checkbox',
							'class' => 'checkbox-input',
							'name' => 'update_filename_date',
							'value' => 1,
							'*' => (isset($_POST['update_filename_date']) && $_POST['update_filename_date'] ? 'checked' : ''),
							'label' => array(
								'class' => 'checkbox-label',
								'content' => '<span>Update filename and date</span>'
							)
						));
						
						// Separator
						echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
						
						// Submit button
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'submit',
							'class' => 'submit-input button',
							'name' => 'submit',
							'value' => 'Replace Media'
						));
						?>
					</table>
				</form>
			</div>
			<?php
		}
	}
	
	/**
	 * Delete some media.
	 * @since 2.1.6[a]
	 *
	 * @access public
	 */
	public function deleteMedia(): void {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty array to hold conflicts
		$conflicts = array();
		
		// Fetch the number of times the media is used as an avatar from the database
		$count = $rs_query->select('usermeta', 'COUNT(*)', array('_key' => 'avatar', 'value' => $this->id));
		
		// Check whether the count is greater than zero
		if($count > 0) $conflicts[] = 'users';
		
		// Fetch the number of times the media is used as a featured image from the database
		$count = $rs_query->select('postmeta', 'COUNT(*)', array('_key' => 'feat_image', 'value' => $this->id));
		
		// Check whether the count is greater than zero
		if($count > 0) $conflicts[] = 'posts';
		
		// Check whether there are any conflicts and redirect to the "List Media" page with an appropriate exit status if so
		if(!empty($conflicts))
			redirect(ADMIN_URI.'?exit_status=failure&conflicts='.implode(':', $conflicts));
		
		// Fetch the filename from the database
		$filename = $rs_query->selectField('postmeta', 'value', array('post' => $this->id, '_key' => 'filename'));
		
		// Check whether the filename exists in the database
		if($filename) {
			// File path for the file to be deleted
			$file_path = trailingSlash(PATH.UPLOADS).$filename;
			
			// Check whether the file exists and delete it if so
			if(file_exists($file_path)) unlink($file_path);
			
			// Delete the media from the database
			$rs_query->delete('posts', array('id' => $this->id));
			
			// Delete the media's metadata from the database
			$rs_query->delete('postmeta', array('post' => $this->id));
			
			// Redirect to the "List Media" page with an appropriate exit status
			redirect(ADMIN_URI.'?exit_status=success');
		} else {
			// Redirect to the "List Media" page with an appropriate exit status
			redirect(ADMIN_URI.'?exit_status=failure');
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 2.1.0[a]
	 *
	 * @access private
	 * @param array $data
	 * @param string $action
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function validateData($data, $action, $id = 0): string {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Make sure no required fields are empty
		if(empty($data['title']))
			return statusMessage('R');
		
		// Check which action has been submitted
		switch($action) {
			case 'upload':
				// Make sure a file has been selected
				if(empty($data['file']['name']))
					return statusMessage('A file must be selected for upload!');
				
				// Create an array of accepted MIME types
				$accepted_mime = array(
					'image/jpeg',
					'image/png',
					'image/gif',
					'image/x-icon',
					'audio/mp3',
					'audio/ogg',
					'video/mp4',
					'text/plain'
				);
				
				// Check whether the uploaded file is among the accepted MIME types
				if(!in_array($data['file']['type'], $accepted_mime, true))
					return statusMessage('The file could not be uploaded.');
				
				// File path for the uploads directory
				$file_path = PATH.UPLOADS;
				
				// Check whether the uploads directory exists, and create it if not
				if(!file_exists($file_path)) mkdir($file_path);
				
				// Split the filename into separate parts
				$file = pathinfo($data['file']['name']);
				
				// Sanitize the filename
				$filename = str_replace(array('  ', ' '), '-', sanitize($file['filename'], '/[^\w\s\-]/'));
				
				// Get a unique slug
				$slug = getUniquePostSlug($filename);
				
				// Get a unique filename
				$filename = getUniqueFilename($filename.'.'.$file['extension']);
				
				// Move the uploaded file to the uploads directory
				move_uploaded_file($data['file']['tmp_name'], trailingSlash(PATH.UPLOADS).$filename);
				
				// Insert the new media into the database
				$insert_id = $rs_query->insert('posts', array(
					'title' => $data['title'],
					'author' => $session['id'],
					'date' => 'NOW()',
					'modified' => 'NOW()',
					'content' => $data['description'],
					'slug' => $slug,
					'type' => 'media'
				));
				
				// Create an array to hold the media's metadata
				$mediameta = array(
					'filename' => $filename,
					'mime_type' => $data['file']['type'],
					'alt_text' => $data['alt_text']
				);
				
				// Insert the media's metadata into the database
				foreach($mediameta as $key => $value) {
					$rs_query->insert('postmeta', array(
						'post' => $insert_id,
						'_key' => $key,
						'value' => $value
					));
				}
				
				// Redirect to the appropriate "Edit Media" page
				redirect(ADMIN_URI.'?id='.$insert_id.'&action=edit');
				break;
			case 'edit':
				// Update the media in the database
				$rs_query->update('posts', array(
					'title' => $data['title'],
					'modified' => 'NOW()',
					'content' => $data['description']
				), array('id' => $id));
				
				// Create an array to hold the media's metadata
				$mediameta = array('alt_text' => $data['alt_text']);
				
				// Update the media's metadata in the database
				foreach($mediameta as $key => $value)
					$rs_query->update('postmeta', array('value' => $value), array('post' => $id, '_key' => $key));
				
				// Update the class variables
				foreach($data as $key => $value) $this->$key = $value;
				
				// Update the content class variable
				$this->content = $data['description'];
				
				// Return a status message
				return statusMessage('Media updated! <a href="'.ADMIN_URI.'">Return to list</a>?', true);
				break;
			case 'replace':
				// Make sure a file has been selected
				if(empty($data['file']['name']))
					return statusMessage('A file must be selected for upload!');
				
				// Create an array of accepted MIME types
				$accepted_mime = array(
					'image/jpeg',
					'image/png',
					'image/gif',
					'image/x-icon',
					'audio/mp3',
					'audio/ogg',
					'video/mp4',
					'text/plain'
				);
				
				// Check whether the uploaded file is among the accepted MIME types
				if(!in_array($data['file']['type'], $accepted_mime, true))
					return statusMessage('The file could not be uploaded.');
				
				// Fetch the media's metadata from the database
				$meta = $this->getPostMeta($id);
				
				// Delete the old file from the uploads directory
				unlink(trailingSlash(PATH.UPLOADS).$meta['filename']);
				
				// Check whether the filename and upload date should be updated
				if(isset($data['update_filename_date']) && $data['update_filename_date'] == 1) {
					// Split the filename into separate parts
					$file = pathinfo($data['file']['name']);
					
					// Sanitize the filename
					$filename = str_replace(array('  ', ' '), '-', sanitize($file['filename'], '/[^\w\s\-]/'));
					
					// Check whether the new filename is the same as the old one
					if($filename.'.'.$file['extension'] === $meta['filename']) {
						// Set the slug to match the filename
						$slug = $filename;
						
						// Add the extension to the filename
						$filename .= '.'.$file['extension'];
					} else {
						// Get a unique slug
						$slug = getUniquePostSlug($filename);
						
						// Get a unique filename
						$filename = getUniqueFilename($filename.'.'.$file['extension']);
					}
					
					// Move the uploaded file to the uploads directory
					move_uploaded_file($data['file']['tmp_name'], trailingSlash(PATH.UPLOADS).$filename);
					
					// Update the media in the database
					$rs_query->update('posts', array(
						'title' => $data['title'],
						'date' => 'NOW()',
						'modified' => 'NOW()',
						'slug' => $slug
					), array('id' => $id));
				} else {
					// Split the filename into separate parts
					$file = pathinfo($data['file']['name']);
					
					// Check whether the extension of the new file matches the existing one
					if(str_contains($meta['filename'], $file['extension'])) {
						// If so, keep the filename and extension the same
						$filename = $meta['filename'];
					} else {
						// Otherwise,
						// Split the old filename into separate parts
						$old_filename = pathinfo($meta['filename']);
						
						// Update the extension
						$filename = $old_filename['filename'].'.'.$file['extension'];
					}
					
					// Move the uploaded file to the uploads directory
					move_uploaded_file($data['file']['tmp_name'], trailingSlash(PATH.UPLOADS).$filename);
					
					// Update the media in the database
					$rs_query->update('posts', array(
						'title' => $data['title'],
						'modified' => 'NOW()'
					), array('id' => $id));
				}
				
				// Create an array to hold the media's metadata
				$mediameta = array('filename' => $filename, 'mime_type' => $data['file']['type']);
				
				// Update the media's metadata in the database
				foreach($mediameta as $key => $value)
					$rs_query->update('postmeta', array('value' => $value), array('post' => $id, '_key' => $key));
				
				// Update the class variables
				foreach($data as $key => $value) $this->$key = $value;
				
				// Return a status message
				return statusMessage('Media replaced! <a href="'.ADMIN_URI.'">Return to list</a>?', true);
				break;
		}
	}
}