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
		// Extend the Query class
		global $rs_query;
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Media</h1>
			<a class="button" href="?action=upload">Upload New</a>
			<?php
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The media was successfully deleted.', true);
			
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
				$table_header_cols = array('Thumbnail', 'File', 'Alt Text', 'Size', 'Dimensions', 'MIME Type', 'Upload Date');
				
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
					
					// Check whether the media is an image
					if(strpos(mime_content_type(trailingSlash(PATH.UPLOADS).$meta['filename']), 'image') !== false) {
						// Fetch the image's dimensions
						list($width, $height) = getimagesize(trailingSlash(PATH.UPLOADS).$meta['filename']);
						
						// Set the dimensions
						$dimensions = $width.' x '.$height;
					}
					
					echo tableRow(
						tableCell('<img src="'.trailingSlash(UPLOADS).$meta['filename'].'" width="100">', 'thumbnail'),
						tableCell('<strong>'.$media['title'].'</strong><br><em>'.$meta['filename'].'</em><div class="actions"><a href="?id='.$media['id'].'&action=edit">Edit</a> &bull; <a href="?id='.$media['id'].'&action=delete">Delete</a> &bull; <a href="'.trailingSlash(UPLOADS).$meta['filename'].'" target="_blank">View</a></div>', 'file'),
						tableCell($meta['alt_text'], 'alt-text'),
						tableCell(getFileSize(filesize(trailingSlash(PATH.UPLOADS).$meta['filename'])), 'size'),
						tableCell($dimensions ?? '', 'dimensions'),
						tableCell($meta['mime_type'], 'mime-type'),
						tableCell(formatDate($media['date'], 'd M Y @ g:i A'), 'upload-date')
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
					echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Upload Media'));
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
		// Extend the Query class
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
							echo formRow('', array('tag'=>'input', 'type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Media'));
							?>
						</table>
					</form>
				</div>
				<?php
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
		// Extend the Query class and the user's session data
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
			if($this->filenameExists($filename))
				$filename = $this->getUniqueFilename($filename);
			
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
	
	/**
	 * Check whether a filename already exists in the database.
	 * @since 2.1.0[a]
	 *
	 * @access private
	 * @param string $filename
	 * @return bool
	 */
	private function filenameExists($filename) {
		// Extend the Query class
		global $rs_query;
		
		// Return true if the filename appears in the database
		return $rs_query->select('postmeta', 'COUNT(*)', array('_key'=>'filename', 'value'=>array('LIKE', $filename.'%'))) > 0;
	}
	
	/**
	 * Make a filename unique by adding a number to the end of it.
	 * @since 2.1.0[a]
	 *
	 * @access private
	 * @param string $filename
	 * @return string
	 */
	private function getUniqueFilename($filename) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the number of conflicting filenames in the database
		$count = $rs_query->select('postmeta', 'COUNT(*)', array('_key'=>'filename', 'value'=>array('LIKE', $filename.'%')));
		
		// Split the filename into separate parts
		$file_parts = pathinfo($filename);
		
		do {
			// Construct a unique filename
			$unique_filename = $file_parts['filename'].'-'.($count + 1).'.'.$file_parts['extension'];
			
			// Increment the count
			$count++;
		} while($rs_query->selectRow('postmeta', 'COUNT(*)', array('_key'=>'filename', 'value'=>$unique_filename)) > 0);
		
		// Return the unique filename
		return $unique_filename;
	}
}