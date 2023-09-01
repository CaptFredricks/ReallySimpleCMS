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
		
		if($id !== 0) {
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
		// Extend the Query object
		global $rs_query;
		
		// Query vars
		$search = $_GET['search'] ?? null;
		$paged = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Media</h1>
			<?php
			// Check whether the user has sufficient privileges to upload media
			if(userHasPrivilege('can_upload_media'))
				echo actionLink('upload', array('classes' => 'button', 'caption' => 'Upload New'));
			
			recordSearch();
			adminInfo();
			?>
			<hr>
			<?php
			if(isset($_GET['exit_status'])) {
				if($_GET['exit_status'] === 'success') {
					echo exitNotice('The media was successfully deleted.');
				} elseif($_GET['exit_status'] === 'failure') {
					if(isset($_GET['conflicts'])) {
						// Create an array with the conflicts
						$conflicts = explode(':', $_GET['conflicts']);
						
						// Check whether the conflict is with the users table
						if(in_array('users', $conflicts, true))
							$message[] = 'That media is currently a <strong><em>user\'s avatar</em></strong>. If you wish to delete it, unlink it from the user first.';
						
						// Check whether the conflict is with the posts table
						if(in_array('posts', $conflicts, true))
							$message[] = 'That media is currently a <strong><em>post\'s featured image</em></strong>. If you wish to delete it, unlink it from the post first.';
						
						echo exitNotice(implode('<br>', $message), -1);
					} else {
						echo exitNotice('The media could not be deleted!', -1);
					}
				}
			}
			
			if(!is_null($search)) {
				$count = $rs_query->select('posts', 'COUNT(*)', array(
					'title' => array('LIKE', '%' . $search . '%'),
					'type' => 'media'
				));
			} else {
				$count = $rs_query->select('posts', 'COUNT(*)', array('type' => 'media'));
			}
			
			$paged['count'] = ceil($count / $paged['per_page']);
			?>
			<div class="entry-count">
				<?php echo $count . ' ' . ($count === 1 ? 'entry' : 'entries'); ?>
			</div>
		</div>
		<table class="data-table">
			<thead>
				<?php
				$header_cols = array(
					'thumbnail' => 'Thumbnail',
					'file' => 'File',
					'uploader' => 'Uploader',
					'upload-date' => 'Upload Date',
					'size' => 'Size',
					'dimensions' => 'Dimensions',
					'mime-type' => 'MIME Type'
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				if(!is_null($search)) {
					// Search results
					$mediaa = $rs_query->select('posts', '*', array(
						'title' => array('LIKE', '%' . $search . '%'),
						'type' => 'media'
					), 'date', 'DESC', array($paged['start'], $paged['per_page']));
				} else {
					// All results
					$mediaa = $rs_query->select('posts', '*',
						array('type' => 'media'), 'date', 'DESC',
						array($paged['start'], $paged['per_page'])
					);
				}
				
				foreach($mediaa as $media) {
					$meta = $this->getPostMeta($media['id']);
					
					$actions = array(
						// Edit
						userHasPrivilege('can_edit_media') ? actionLink('edit', array(
							'caption' => 'Edit',
							'id' => $media['id']
						)) : null,
						// Delete
						userHasPrivilege('can_delete_media') ? actionLink('delete', array(
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
					$file_path = slash(PATH . UPLOADS) . $meta['filepath'];
					
					if(file_exists($file_path)) {
						$path = pathinfo($file_path);
						$size = getFileSize(filesize($file_path));
						
						// Check whether the media is an image
						if(str_starts_with(mime_content_type($file_path), 'image')) {
							list($width, $height) = getimagesize($file_path);
							
							$dimensions = $width . ' x ' . $height;
						} else {
							$dimensions = null;
						}
					} else {
						$size = null;
					}
					
					echo tableRow(
						// Thumbnail
						tdCell(getMedia($media['id']), 'thumbnail'),
						// File
						tdCell('<strong>' . $media['title'] . '</strong><br><em>' . $path['basename'] .
							'</em><div class="actions">' . implode(' &bull; ', $actions) . '</div>',
						'file'),
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
				
				if(empty($mediaa))
					echo tableRow(tdCell('There are no media to display.', '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($paged['current'], $paged['count']);
		
        include_once PATH . ADMIN . INC . '/modal-delete.php';
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
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			// Validate the form data and return any messages
			$message = isset($_POST['submit']) ? $this->validateData(
				$_POST,
				$_GET['action'],
				$this->id
			) : '';
			
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
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			if(isset($_POST['submit'])) {
				$data = array_merge($_POST, $_FILES);
				
				// Validate the form data and return any messages
				$message = $this->validateData($data, $_GET['action'], $this->id);
			}
			
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
							'checked' => ($_POST['update_filename_date'] ?? 0),
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
		$count = $rs_query->select('usermeta', 'COUNT(*)', array(
			'_key' => 'avatar',
			'value' => $this->id
		));
		
		// Check whether the count is greater than zero
		if($count > 0) $conflicts[] = 'users';
		
		// Fetch the number of times the media is used as a featured image from the database
		$count = $rs_query->select('postmeta', 'COUNT(*)', array(
			'_key' => 'feat_image',
			'value' => $this->id
		));
		
		// Check whether the count is greater than zero
		if($count > 0) $conflicts[] = 'posts';
		
		// Check whether there are any conflicts and redirect to the "List Media" page with an appropriate exit status if so
		if(!empty($conflicts))
			redirect(ADMIN_URI . '?exit_status=failure&conflicts=' . implode(':', $conflicts));
		
		$filename = $rs_query->selectField('postmeta', 'value', array(
			'post' => $this->id,
			'_key' => 'filepath'
		));
		
		// If the file exists, delete it
		if($filename) {
			$file_path = slash(PATH . UPLOADS) . $filename;
			
			if(file_exists($file_path)) unlink($file_path);
			
			$rs_query->delete('posts', array('id' => $this->id));
			$rs_query->delete('postmeta', array('post' => $this->id));
			
			redirect(ADMIN_URI . '?exit_status=success');
		} else {
			redirect(ADMIN_URI . '?exit_status=failure');
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
		
		if(empty($data['title']))
			return exitNotice('REQ', -1);
		
		$basepath = PATH . UPLOADS;
		
		switch($action) {
			case 'upload':
				if(empty($data['file']['name']))
					return exitNotice('A file must be selected for upload!', -1);
				
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
				
				if(!in_array($data['file']['type'], $accepted_mime, true))
					return exitNotice('The file could not be uploaded.', -1);
				
				if(!file_exists($basepath)) mkdir($basepath);
				
				$year = date('Y');
				
				if(!file_exists(slash($basepath) . $year))
					mkdir(slash($basepath) . $year);
				
				$file = pathinfo($data['file']['name']);
				$filename = str_replace(array('  ', ' ', '_'), '-',
					sanitize($file['filename'], '/[^\w-]/')
				);
				
				$slug = getUniquePostSlug($filename);
				$filename = getUniqueFilename($filename . '.' . $file['extension']);
				$filepath = slash($year) . $filename;
				
				// Move the uploaded file to the uploads directory
				move_uploaded_file(
					$data['file']['tmp_name'],
					slash($basepath) . $filepath
				);
				
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
				
				$mediameta = array(
					'filepath' => $filepath,
					'mime_type' => $data['file']['type'],
					'alt_text' => $data['alt_text']
				);
				
				foreach($mediameta as $key => $value) {
					$rs_query->insert('postmeta', array(
						'post' => $insert_id,
						'_key' => $key,
						'value' => $value
					));
				}
				
				redirect(ADMIN_URI . '?id=' . $insert_id . '&action=edit');
				break;
			case 'edit':
				$rs_query->update('posts', array(
					'title' => $data['title'],
					'modified' => 'NOW()',
					'content' => $data['description']
				), array('id' => $id));
				
				$mediameta = array('alt_text' => $data['alt_text']);
				
				foreach($mediameta as $key => $value)
					$rs_query->update('postmeta', array('value' => $value), array(
						'post' => $id,
						'_key' => $key
					));
				
				// Update the class variables
				foreach($data as $key => $value) $this->$key = $value;
				
				// Update the content class variable
				$this->content = $data['description'];
				
				return exitNotice('Media updated! <a href="' . ADMIN_URI . '">Return to list</a>?');
				break;
			case 'replace':
				if(empty($data['file']['name']))
					return exitNotice('A file must be selected for upload!', -1);
				
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
				
				if(!in_array($data['file']['type'], $accepted_mime, true))
					return exitNotice('The file could not be uploaded.', -1);
				
				$meta = $this->getPostMeta($id);
				
				// Delete the old file
				unlink(slash($basepath) . $meta['filepath']);
				
				// Check whether the filename and upload date should be updated
				if(isset($data['update_filename_date']) && $data['update_filename_date'] == 1) {
					$year = date('Y');
					
					if(!file_exists(slash($basepath) . $year))
						mkdir(slash($basepath) . $year);
					
					$file = pathinfo($data['file']['name']);
					$filename = str_replace(array('  ', ' ', '_'), '-',
						sanitize($file['filename'], '/[^\w-]/')
					);
					
					// Check whether the new filename is the same as the old one
					if(slash($year) . $filename . '.' .
						$file['extension'] === $meta['filepath']) {
							
						$slug = $filename;
						$filepath = slash($year) . $filename . '.' . $file['extension'];
					} else {
						$slug = getUniquePostSlug($filename);
						$filename = getUniqueFilename($filename . '.' . $file['extension']);
						$filepath = slash($year) . $filename;
					}
					
					// Move the uploaded file to the uploads directory
					move_uploaded_file(
						$data['file']['tmp_name'],
						slash($basepath) . $filepath
					);
					
					// Update the media in the database
					$rs_query->update('posts', array(
						'title' => $data['title'],
						'date' => 'NOW()',
						'modified' => 'NOW()',
						'slug' => $slug
					), array('id' => $id));
				} else {
					$year = formatDate($rs_query->selectField('posts', 'date', array('id' => $id)), 'Y');
					
					// Split the filename into separate parts
					$file = pathinfo($data['file']['name']);
					
					// Check whether the extension of the new file matches the existing one
					if(str_contains($meta['filepath'], $file['extension'])) {
						// If so, keep the filename and extension the same
						$filepath = $meta['filepath'];
					} else {
						// Otherwise,
						// Split the old filename into separate parts
						$old_filename = pathinfo($meta['filepath']);
						
						// Update the extension
						$filepath = slash($year) . $old_filename['filename'] . '.' .
							$file['extension'];
					}
					
					// Move the uploaded file to the uploads directory
					move_uploaded_file(
						$data['file']['tmp_name'],
						slash($basepath) . $filepath
					);
					
					// Update the media in the database
					$rs_query->update('posts', array(
						'title' => $data['title'],
						'modified' => 'NOW()'
					), array('id' => $id));
				}
				
				$mediameta = array(
					'filepath' => $filepath,
					'mime_type' => $data['file']['type']
				);
				
				foreach($mediameta as $key => $value) {
					$rs_query->update('postmeta', array('value' => $value), array(
						'post' => $id,
						'_key' => $key
					));
				}
				
				// Update the class variables
				foreach($data as $key => $value) $this->$key = $value;
				
				return exitNotice('Media replaced! <a href="' . ADMIN_URI . '">Return to list</a>?');
				break;
		}
	}
}