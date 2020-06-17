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
	 * Construct a list of all media in the database.
	 * @since 2.1.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listMedia() {
		// Extend the Query object
		global $rs_query;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Media</h1>
			<a class="button" href="?action=upload">Upload New</a>
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
					}
				}
			}
			
			// Fetch the media entry count from the database
			$count = $rs_query->select('posts', 'COUNT(*)', array('type'=>'media'));
			
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
				$table_header_cols = array('Thumbnail', 'File', 'Author', 'Upload Date', 'Size', 'Dimensions', 'MIME Type');
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all media from the database
				$mediaa = $rs_query->select('posts', '*', array('type'=>'media'), 'date', 'DESC', array($page['start'], $page['per_page']));
				
				// Loop through the media
				foreach($mediaa as $media) {
					// Fetch the media's metadata from the database
					$meta = $this->getPostMeta($media['id']);
					
					// Check whether the media exists
					if(file_exists(trailingSlash(PATH.UPLOADS).$meta['filename'])) {
						// Fetch the media's file size
						$size = getFileSize(filesize(trailingSlash(PATH.UPLOADS).$meta['filename']));
						
						// Check whether the media is an image
						if(strpos(mime_content_type(trailingSlash(PATH.UPLOADS).$meta['filename']), 'image') !== false) {
							// Fetch the image's dimensions
							list($width, $height) = getimagesize(trailingSlash(PATH.UPLOADS).$meta['filename']);
							
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
						tableCell('<img src="'.trailingSlash(UPLOADS).$meta['filename'].'" width="100">', 'thumbnail'),
						tableCell('<strong>'.$media['title'].'</strong><br><em>'.$meta['filename'].'</em><div class="actions"><a href="?id='.$media['id'].'&action=edit">Edit</a> &bull; <a class="modal-launch delete-item" href="?id='.$media['id'].'&action=delete" data-item="media">Delete</a> &bull; <a href="'.trailingSlash(UPLOADS).$meta['filename'].'" target="_blank" rel="noreferrer noopener">View</a></div>', 'file'),
						tableCell($this->getAuthor($media['author']), 'author'),
						tableCell(formatDate($media['date'], 'd M Y @ g:i A'), 'upload-date'),
						tableCell($size ?? '0 B', 'size'),
						tableCell($dimensions ?? '&mdash;', 'dimensions'),
						tableCell($meta['mime_type'], 'mime-type')
					);
				}
				
				// Display a notice if no media are found
				if(empty($mediaa))
					echo tableRow(tableCell('There are no media to display.', '', count($table_header_cols)));
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
		
		// Include the delete modal
        include_once PATH.ADMIN.INC.'/modal-delete.php';
	}
	
	/**
	 * Construct the 'Upload Media' form.
	 * @since 2.1.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function uploadMedia() {
		// Check whether the form has been submitted
		if(isset($_POST['submit'])) {
			// Merge the $_POST and $_FILES arrays
			$data = array_merge($_POST, $_FILES);
		
			// Validate the form data and return any messages
			$message = $this->validateData($data);
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
					echo formRow(array('Title', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'title', 'value'=>($_POST['title'] ?? '')));
					echo formRow(array('File', true), array('tag'=>'input', 'type'=>'file', 'id'=>'file-upl', 'class'=>'file-input required invalid init', 'name'=>'file'));
					echo formRow('Alt Text', array('tag'=>'input', 'class'=>'text-input', 'name'=>'alt_text', 'value'=>($_POST['alt_text'] ?? '')));
					echo formRow('Description', array('tag'=>'textarea', 'class'=>'textarea-input', 'name'=>'description', 'cols'=>30, 'rows'=>10, 'content'=>htmlspecialchars(($_POST['description'] ?? ''))));
					echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Upload Media'));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Construct the 'Edit Media' form.
	 * @since 2.1.0[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function editMedia($id) {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the media's id is valid
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Media' page
			redirect('media.php');
		} else {
			// Fetch the number of times the media appears in the database
			$count = $rs_query->selectRow('posts', 'COUNT(*)', array('id'=>$id, 'type'=>'media'));
			
			// Check whether the count is zero
			if($count === 0) {
				// Redirect to the 'List Media' page
				redirect('media.php');
			} else {
				// Validate the form data and return any messages
				$message = isset($_POST['submit']) ? $this->validateData($_POST, $id) : '';
				
				// Fetch the media from the database
				$media = $rs_query->selectRow('posts', '*', array('id'=>$id, 'type'=>'media'));
				
				// Fetch the media's metadata from the database
				$meta = $this->getPostMeta($id);
				?>
				<div class="heading-wrap">
					<h1>Edit Media</h1>
					<?php echo $message; ?>
				</div>
				<div class="data-form-wrap clear">
					<form class="data-form" action="" method="post" autocomplete="off">
						<table class="form-table">
							<?php
							echo formRow('Thumbnail', array('tag'=>'img', 'src'=>trailingSlash(UPLOADS).$meta['filename'], 'width'=>150));
							echo formRow(array('Title', true), array('tag'=>'input', 'class'=>'text-input required invalid init', 'name'=>'title', 'value'=>$media['title']));
							echo formRow('Alt Text', array('tag'=>'input', 'class'=>'text-input', 'name'=>'alt_text', 'value'=>$meta['alt_text']));
							echo formRow('Description', array('tag'=>'textarea', 'class'=>'textarea-input', 'name'=>'description', 'cols'=>30, 'rows'=>10, 'content'=>htmlspecialchars($media['content'])));
							echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
							echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Media'));
							?>
						</table>
					</form>
				</div>
				<?php
			}
		}
	}
	
	/**
	 * Delete media from the database.
	 * @since 2.1.6[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function deleteMedia($id) {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty array to hold conflicts
		$conflicts = array();
		
		// Fetch the number of times the media is used as an avatar from the database
		$count = $rs_query->select('usermeta', 'COUNT(*)', array('_key'=>'avatar', 'value'=>$id));
		
		// Check whether the count is greater than zero
		if($count > 0) $conflicts[] = 'users';
		
		// Fetch the number of times the media is used as a featured image from the database
		$count = $rs_query->select('postmeta', 'COUNT(*)', array('_key'=>'feat_image', 'value'=>$id));
		
		// Check whether the count is greater than zero
		if($count > 0) $conflicts[] = 'posts';
		
		// Check whether there are any conflicts
		if(!empty($conflicts))
			redirect('media.php?exit_status=failure&conflicts='.implode(':', $conflicts));
		
		// Fetch the filename from the database
		$filename = $rs_query->selectField('postmeta', 'value', array('post'=>$id, '_key'=>'filename'));
		
		// Check whether the filename exists in the database
		if($filename) {
			// File path for the file to be deleted
			$file_path = trailingSlash(PATH.UPLOADS).$filename;
			
			// Check whether the file exists
			if(file_exists($file_path)) {
				// Delete the file
				unlink($file_path);
				
				// Delete the media from the database
				$rs_query->delete('posts', array('id'=>$id));
				
				// Delete the media's metadata from the database
				$rs_query->delete('postmeta', array('post'=>$id));
				
				// Redirect to the 'List Media' page (with a success message)
				redirect('media.php?exit_status=success');
			}
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 2.1.0[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return null|string (null on $id == 0; string on $id != 0)
	 */
	private function validateData($data, $id = 0) {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Make sure no required fields are empty
		if(empty($data['title']))
			return statusMessage('R');
		
		if($id === 0) {
			// Make sure a file has been selected
			if(empty($data['file']['name']))
				return statusMessage('A file must be selected for upload!');
			
			// Create an array of accepted MIME types
			$accepted_mime = array('image/jpeg', 'image/png', 'image/gif', 'image/x-icon', 'audio/mp3', 'audio/ogg', 'video/mp4', 'text/plain');
			
			// Check whether the uploaded file is among the accepted MIME types
			if(!in_array($data['file']['type'], $accepted_mime, true))
				return statusMessage('The file could not be uploaded.');
			
			// File path for the uploads directory
			$file_path = PATH.UPLOADS;
			
			// Check whether the uploads directory exists, and create it if not
			if(!file_exists($file_path)) mkdir($file_path);
			
			// Convert the filename to all lowercase, replace spaces with hyphens, and remove all special characters
			$filename = preg_replace('/[^\w.\-]/i', '', str_replace(' ', '-', strtolower($data['file']['name'])));
			
			// Check whether the filename is already in the database and make it unique if so
			if(filenameExists($filename))
				$filename = getUniqueFilename($filename);
			
			// Strip off the filename's extension for the post's slug
			$slug = pathinfo($filename, PATHINFO_FILENAME);
			
			// Move the uploaded file to the uploads directory
			move_uploaded_file($data['file']['tmp_name'], trailingSlash(PATH.UPLOADS).$filename);
			
			// Create an array to hold the media's metadata
			$mediameta = array('filename'=>$filename, 'mime_type'=>$data['file']['type'], 'alt_text'=>$data['alt_text']);
			
			// Insert the new media into the database
			$insert_id = $rs_query->insert('posts', array('title'=>$data['title'], 'author'=>$session['id'], 'date'=>'NOW()', 'content'=>$data['description'], 'slug'=>$slug, 'type'=>'media'));
			
			// Insert the media's metadata into the database
			foreach($mediameta as $key=>$value)
				$rs_query->insert('postmeta', array('post'=>$insert_id, '_key'=>$key, 'value'=>$value));
			
			// Redirect to the 'Edit Media' page
			redirect('media.php?id='.$insert_id.'&action=edit');
		} else {
			// Create an array to hold the media's metadata
			$mediameta = array('alt_text'=>$data['alt_text']);
			
			// Update the media in the database
			$rs_query->update('posts', array('title'=>$data['title'], 'modified'=>'NOW()', 'content'=>$data['description']), array('id'=>$id));
			
			// Update the media's metadata in the database
			foreach($mediameta as $key=>$value)
				$rs_query->update('postmeta', array('value'=>$value), array('post'=>$id, '_key'=>$key));
			
			// Return a status message
			return statusMessage('Media updated! <a href="media.php">Return to list</a>?', true);
		}
	}
}