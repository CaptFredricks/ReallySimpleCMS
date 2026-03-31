<?php
/**
 * Admin class used to implement the Media object. Inherits from the Post class.
 * Media includes images, videos, and documents. These can be used anywhere on the front end of the site.
 * Media can be uploaded, modified, and deleted. Media are stored in the `posts` table as the `media` post type.
 * @since 2.1.0-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_media
 *
 * ## VARIABLES [2] ##
 * See `Post` class for a list of inherited vars
 * - private string $post_type
 * - private array $admin_page
 *
 * ## METHODS [12] ##
 * See `Post` class for a list of inherited methods
 * - public __construct(int $id, string $action)
 * { LISTS, FORMS, & ACTIONS [5] }
 * - public listRecordsMedia(): void
 * - public uploadRecordMedia(): void
 * - public editRecordMedia(): void
 * - public replaceRecordMedia(): void
 * - public deleteRecordMedia(): void
 * { VALIDATION [1] }
 * - private validateSubmission(array $data): string
 * { MISCELLANEOUS [5] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private getResults(?string $search, bool $all): array
 * - private getEntryCount(?string $search): int
 * - private getActionLinks(array $media): string
 */
namespace Admin;

class Media extends Post {
	/**
	 * The currently queried media's post type.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $post_type = 'media';
	
	/**
	 * The admin page's data.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @var array
	 */
	private $admin_page = array();
	
	/**
	 * Class constructor.
	 * @since 1.1.1-beta
	 *
	 * @access public
	 * @param int $id -- The media's id.
	 * @param string $action -- The current action.
	 */
	public function __construct(int $id, string $action) {
		global $rs_query, $rs_admin_pages;
		
		$this->action = $action;
		$this->admin_page = $rs_admin_pages[basename($_SERVER['PHP_SELF'], '.php')];
		
		if($id > 0) {
			$cols = array_keys(get_object_vars($this));
			$exclude = array('action', 'paged', 'post_type', 'admin_page');
			$cols = array_diff($cols, $exclude);
			
			$media = $rs_query->selectRow(getTable('p'), $cols, array(
				'id' => $id,
				'type' => $this->post_type
			));
			
			foreach($media as $key => $value) $this->$key = $media[$key];
		} else {
			$this->id = 0;
		}
	}
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct a list of all media in the database.
	 * @since 2.1.0-alpha
	 *
	 * @access public
	 */
	public function listRecordsMedia(): void {
		// Query vars
		$search = $_GET['search'] ?? null;
		$this->paged = paginate((int)($_GET['paged'] ?? 1));
		
		$this->pageHeading();
		?>
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
				$mediaa = $this->getResults($search);
				
				foreach($mediaa as $media) {
					$meta = $this->getPostMeta($media['id']);
					
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
						$path = array();
						$size = null;
					}
					
					echo tableRow(
						// Thumbnail
						tdCell(getMedia($media['id']), 'thumbnail'),
						// File
						tdCell(domTag('strong', array(
							'content' => $media['title']
						)) . domTag('br') . domTag('em', array(
							'content' => !empty($path) ? $path['basename'] : 'file does not exist'
						)) . domTag('div', array(
							'class' => 'actions',
							'content' => $this->getActionLinks($media)
						)), 'file'),
						// Author
						tdCell($this->getAuthor($media['author']), 'author'),
						// Upload date
						tdCell(formatDate($media['created'], 'd M Y @ g:i A'), 'upload-date'),
						// Size
						tdCell($size ?? '0 B', 'size'),
						// Dimensions
						tdCell($dimensions ?? '&mdash;', 'dimensions'),
						// MIME type
						tdCell($meta['mime_type'], 'mime-type')
					);
				}
				
				if(empty($mediaa))
					echo tableRow(tdCell($this->admin_page['labels']['no_items'], '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($this->paged['current'], $this->paged['count']);
		
        includeFile(PATH . MODALS . '/modal-delete.php');
	}
	
	/**
	 * Upload some media.
	 * @since 2.1.0-alpha
	 *
	 * @access public
	 */
	public function uploadRecordMedia(): void {
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off" enctype="multipart/form-data">
				<table class="form-table">
					<?php
					// Title
					echo formRow(array('Title', true), array(
						'tag' => 'input',
						'id' => 'title-field',
						'class' => 'text-input required invalid init',
						'name' => 'title',
						'value' => ($_POST['title'] ?? ''),
						'placeholder' => $this->admin_page['labels']['title_placeholder']
					));
					
					// File
					echo formRow(array('File', true), array(
						'tag' => 'input',
						'type' => 'file',
						'id' => 'file-upload-field',
						'class' => 'file-input required invalid init',
						'name' => 'file'
					));
					
					// Alt text
					echo formRow('Alt Text', array(
						'tag' => 'input',
						'id' => 'alt-text-field',
						'class' => 'text-input',
						'name' => 'alt_text',
						'value' => ($_POST['alt_text'] ?? '')
					));
					
					// Description
					echo formRow('Description', array(
						'tag' => 'textarea',
						'id' => 'description-field',
						'class' => 'textarea-input',
						'name' => 'description',
						'cols' => 30,
						'rows' => 10,
						'content' => htmlspecialchars(($_POST['description'] ?? ''))
					));
					
					// Separator
					echo formRow('', array(
						'tag' => 'hr',
						'class' => 'separator'
					));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => $this->admin_page['labels']['create_button']
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Edit some media.
	 * @since 2.1.0-alpha
	 *
	 * @access public
	 */
	public function editRecordMedia(): void {
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$this->pageHeading();
		
		$meta = $this->getPostMeta($this->id);
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Thumbnail
					echo formRow('Thumbnail', array(
						'tag' => 'div',
						'class' => 'thumb-wrap',
						'content' => getMedia($this->id, array(
							'class' => 'media-thumb'
						))
					));
					
					// Title
					echo formRow(array('Title', true), array(
						'tag' => 'input',
						'id' => 'title-field',
						'class' => 'text-input required invalid init',
						'name' => 'title',
						'value' => $this->title,
						'placeholder' => $this->admin_page['labels']['title_placeholder']
					));
					
					// Alt text
					echo formRow('Alt Text', array(
						'tag' => 'input',
						'id' => 'alt-text-field',
						'class' => 'text-input',
						'name' => 'alt_text',
						'value' => $meta['alt_text']
					));
					
					// Description
					echo formRow('Description', array(
						'tag' => 'textarea',
						'id' => 'description-field',
						'class' => 'textarea-input',
						'name' => 'description',
						'cols' => 30,
						'rows' => 10,
						'content' => htmlspecialchars($this->content)
					));
					
					// Separator
					echo formRow('', array(
						'tag' => 'hr',
						'class' => 'separator'
					));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => $this->admin_page['labels']['update_button']
					));
					?>
				</table>
			</form>
			<?php echo actionLink($this->admin_page['actions']['replace'], array(
				'classes' => 'replace-media button',
				'caption' => $this->admin_page['labels']['duplicate_item'],
				'id' => $this->id
			)); ?>
		</div>
		<?php
	}
	
	/**
	 * Replace some media.
	 * @since 1.2.3-beta
	 *
	 * @access public
	 */
	public function replaceRecordMedia(): void {
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$this->pageHeading();
		
		$meta = $this->getPostMeta($this->id);
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off" enctype="multipart/form-data">
				<table class="form-table">
					<?php
					// Thumbnail
					echo formRow('Thumbnail', array(
						'tag' => 'div',
						'class' => 'thumb-wrap',
						'content' => getMedia($this->id, array(
							'class' => 'media-thumb'
						))
					));
					
					// Title
					echo formRow(array('Title', true), array(
						'tag' => 'input',
						'id' => 'title-field',
						'class' => 'text-input required invalid init',
						'name' => 'title',
						'value' => $this->title,
						'placeholder' => $this->admin_page['labels']['title_placeholder']
					));
					
					// File
					echo formRow(array('File', true), array(
						'tag' => 'input',
						'type' => 'file',
						'id' => 'file-upload-field',
						'class' => 'file-input required invalid init',
						'name' => 'file'
					));
					
					// Metadata
					echo formRow('Metadata', array(
						'tag' => 'input',
						'type' => 'checkbox',
						'id' => 'update-filename-date-field',
						'class' => 'checkbox-input',
						'name' => 'update_filename_date',
						'value' => 1,
						'checked' => ($_POST['update_filename_date'] ?? 0),
						'label' => array(
							'class' => 'checkbox-label',
							'content' => domTag('span', array(
								'content' => 'Update filename and date'
							))
						)
					));
					
					// Separator
					echo formRow('', array(
						'tag' => 'hr',
						'class' => 'separator'
					));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => $this->admin_page['labels']['duplicate_item']
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Delete some media.
	 * @since 2.1.6-alpha
	 *
	 * @access public
	 */
	public function deleteRecordMedia(): void {
		global $rs_query;
		
		$conflicts = array();
		
		// Check if the media is used as an avatar
		$count = $rs_query->select(getTable('um'), 'COUNT(*)', array(
			'key' => 'avatar',
			'value' => $this->id
		));
		
		if($count > 0) $conflicts[] = 'users';
		
		// Check if the media is used as a post featured image
		$count = $rs_query->select(getTable('pm'), 'COUNT(*)', array(
			'key' => 'feat_image',
			'value' => $this->id
		));
		
		if($count > 0) $conflicts[] = 'posts';
		
		// Check whether there are any conflicts and redirect to the "List Media" page with an appropriate exit status if so
		if(!empty($conflicts)) {
			redirect(ADMIN_URI . getQueryString(array(
				'exit_status' => 'del_failure',
				'conflicts' => implode(':', $conflicts)
			)));
		}
		
		$filename = $rs_query->selectField(getTable('pm'), 'value', array(
			'post' => $this->id,
			'key' => 'filepath'
		));
		
		// If the file exists, delete it
		if($filename) {
			$file_path = slash(PATH . UPLOADS) . $filename;
			
			if(file_exists($file_path)) unlink($file_path);
			
			$rs_query->delete(getTable('p'), array(
				'id' => $this->id
			));
			
			$rs_query->delete(getTable('pm'), array(
				'post' => $this->id
			));
			
			redirect(ADMIN_URI . getQueryString(array(
				'exit_status' => 'del_success'
			)));
		}
		
		redirect(ADMIN_URI . getQueryString(array(
			'exit_status' => 'del_failure'
		)));
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the form data.
	 * @since 2.1.0-alpha
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateSubmission(array $data): string {
		global $rs_query, $rs_session;
		
		if(empty($data['title'])) {
			return exitNotice('REQ', -1);
			exit;
		}
		
		$basepath = PATH . UPLOADS;
		
		switch($this->action) {
			case 'upload':
				if(empty($data['file']['name'])) {
					return exitNotice('A file must be selected for upload!', -1);
					exit;
				}
				
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
				
				if(!in_array($data['file']['type'], $accepted_mime, true)) {
					return exitNotice('The file could not be uploaded.', -1);
					exit;
				}
				
				if(!file_exists($basepath)) mkdir($basepath);
				
				$year = date('Y');
				
				if(!file_exists(slash($basepath) . $year))
					mkdir(slash($basepath) . $year);
				
				$file = pathinfo($data['file']['name']);
				$filename = sanitize(str_replace(array('  ', ' ', '_'), '-', $file['filename']), '/[^\w-]/');
				
				$slug = getUniquePostSlug($filename);
				$filename = getUniqueFilename($filename . '.' . $file['extension']);
				$filepath = slash($year) . $filename;
				
				// Move the uploaded file to the uploads directory
				move_uploaded_file(
					$data['file']['tmp_name'],
					slash($basepath) . $filepath
				);
				
				$insert_id = $rs_query->insert(getTable('p'), array(
					'title' => $data['title'],
					'author' => $rs_session['id'],
					'created' => 'NOW()',
					'modified' => 'NOW()',
					'content' => $data['description'],
					'slug' => $slug,
					'type' => $this->post_type
				));
				
				$mediameta = array(
					'filepath' => $filepath,
					'mime_type' => $data['file']['type'],
					'alt_text' => $data['alt_text']
				);
				
				foreach($mediameta as $key => $value) {
					$rs_query->insert(getTable('pm'), array(
						'post' => $insert_id,
						'key' => $key,
						'value' => $value
					));
				}
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $insert_id,
					'action' => 'edit',
					'exit_status' => 'upl_success'
				)));
				break;
			case 'edit':
				$rs_query->update(getTable('p'), array(
					'title' => $data['title'],
					'modified' => 'NOW()',
					'content' => $data['description']
				), array(
					'id' => $this->id
				));
				
				$mediameta = array(
					'alt_text' => $data['alt_text']
				);
				
				foreach($mediameta as $key => $value) {
					$rs_query->update(getTable('pm'), array(
						'value' => $value
					), array(
						'post' => $this->id,
						'key' => $key
					));
				}
				
				foreach($data as $key => $value) $this->$key = $value;
				
				$this->content = $data['description'];
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $this->id,
					'action' => $this->action,
					'exit_status' => 'edit_success'
				)));
				break;
			case 'replace':
				if(empty($data['file']['name'])) {
					return exitNotice('A file must be selected for upload!', -1);
					exit;
				}
				
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
				
				if(!in_array($data['file']['type'], $accepted_mime, true)) {
					return exitNotice('The file could not be uploaded.', -1);
					exit;
				}
				
				$meta = $this->getPostMeta($this->id);
				
				// Delete the old file
				unlink(slash($basepath) . $meta['filepath']);
				
				// Check whether the filename and upload date should be updated
				if(isset($data['update_filename_date']) && $data['update_filename_date'] == 1) {
					$year = date('Y');
					
					if(!file_exists(slash($basepath) . $year))
						mkdir(slash($basepath) . $year);
					
					$file = pathinfo($data['file']['name']);
					$filename = sanitize(str_replace(array('  ', ' ', '_'), '-', $file['filename']), '/[^\w-]/');
					
					// Check whether the new filename is the same as the old one
					if(slash($year) . $filename . '.' . $file['extension'] === $meta['filepath']) {
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
					
					$rs_query->update(getTable('p'), array(
						'title' => $data['title'],
						'created' => 'NOW()',
						'modified' => 'NOW()',
						'slug' => $slug
					), array(
						'id' => $this->id
					));
				} else {
					$year = formatDate($rs_query->selectField(getTable('p'), 'created', array(
						'id' => $this->id
					)), 'Y');
					
					// Split the filename into separate parts
					$file = pathinfo($data['file']['name']);
					
					// Check whether the extension of the new file matches the existing one
					if(str_contains($meta['filepath'], $file['extension'])) {
						$filepath = $meta['filepath'];
					} else {
						$old_filename = pathinfo($meta['filepath']);
						$filepath = slash($year) . $old_filename['filename'] . '.' . $file['extension'];
					}
					
					// Move the uploaded file to the uploads directory
					move_uploaded_file(
						$data['file']['tmp_name'],
						slash($basepath) . $filepath
					);
					
					$rs_query->update(getTable('p'), array(
						'title' => $data['title'],
						'modified' => 'NOW()'
					), array(
						'id' => $this->id
					));
				}
				
				$mediameta = array(
					'filepath' => $filepath,
					'mime_type' => $data['file']['type']
				);
				
				foreach($mediameta as $key => $value) {
					$rs_query->update(getTable('pm'), array(
						'value' => $value
					), array(
						'post' => $this->id,
						'key' => $key
					));
				}
				
				foreach($data as $key => $value) $this->$key = $value;
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $this->id,
					'action' => 'edit',
					'exit_status' => 'repl_success'
				)));
				break;
		}
	}
	
	/*------------------------------------*\
		MISCELLANEOUS
	\*------------------------------------*/
	
	/**
	 * Construct the page heading.
	 * @since 1.3.14-beta
	 *
	 * @access public
	 */
	public function pageHeading(): void {
		$labels = $this->admin_page['labels'];
		$meta = $this->getPostMeta($this->id);
		
		switch($this->action) {
			case 'upload':
				$title = $labels['create_item'];
				
				if(isset($_POST['submit'])) {
					$data = array_merge($_POST, $_FILES);
					$message = $this->validateSubmission($data);
				} else {
					$message = '';
				}
				break;
			case 'edit':
				$title = $labels['edit_item'] . ': { ' . domTag('em', array(
					'content' => substr($meta['filepath'], strpos($meta['filepath'], '/') + 1)
				)) . ' }';
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			case 'replace':
				$title = $labels['duplicate_item'] . ': { ' . domTag('em', array(
					'content' => substr($meta['filepath'], strpos($meta['filepath'], '/') + 1)
				)) . ' }';
				
				if(isset($_POST['submit'])) {
					$data = array_merge($_POST, $_FILES);
					$message = $this->validateSubmission($data);
				} else {
					$message = '';
				}
				break;
			default:
				$title = $labels['name'];
				$search = $_GET['search'] ?? null;
		}
		?>
		<div class="heading-wrap">
			<?php
			// Page title
			domTagPr('h1', array(
				'content' => $title
			));
			
			if(!empty($this->action)) {
				// Status messages
				echo $message;
				
				// Exit notices
				if(isset($_GET['exit_status']))
					echo $this->exitNotice($_GET['exit_status']);
			} else {
				// Upload button
				if(userHasPrivilege('can_upload_media')) {
					echo actionLink('upload', array(
						'classes' => 'button',
						'caption' => $labels['create_button']
					));
				}
				
				// Search
				recordSearch();
				
				// Info
				adminInfo();
				
				domTagPr('hr');
				
				// Exit notices
				if(isset($_GET['exit_status'])) {
					$exit = $_GET['exit_status'];
					
					if($exit === 'del_failure')
						$status_code = -1;
					
					if(isset($status_code)) {
						if(isset($_GET['conflicts'])) {
							$conflicts = explode(':', $_GET['conflicts']);
							$conf = array();
							
							if(in_array('users', $conflicts, true))
								$conf[] = '_avatar';
							
							if(in_array('posts', $conflicts, true))
								$conf[] = '_featimg';
							
							$conf = implode('', $conf);
						}
						
						echo $this->exitNotice($exit . ($conf ?? ''), $status_code);
					} else {
						echo $this->exitNotice($exit);
					}
				}
				
				// Record count
				$count = $this->getEntryCount($search);
				
				domTagPr('div', array(
					'class' => 'entry-count',
					'content' => $count . ' ' . ($count === 1 ? 'entry' : 'entries')
				));
				
				$this->paged['count'] = ceil($count / $this->paged['per_page']);
			}
			?>
		</div>
		<?php
	}
	
	/**
	 * Generate an exit notice.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @param string $exit_status -- The exit status.
	 * @param int $status_code (optional) -- The type of notice to display.
	 * @return string
	 */
	private function exitNotice(string $exit_status, int $status_code = 1): string {
		return exitNotice(match($exit_status) {
			'upl_success' => 'The media was successfully uploaded. ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'edit_success' => 'Media updated! ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'repl_success' => 'Media replaced! ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'del_success' => 'The media was successfully deleted.',
			'del_failure' => 'The media could not be deleted!',
				'del_failure_avatar' => 'That media is currently a ' . domTag('strong', array(
					'content' => domTag('em', array(
						'content' => 'user\'s avatar'
					))
				)) . '. If you wish to delete it, unlink it from the user first.',
				'del_failure_featimg' => 'That media is currently a ' . domTag('strong', array(
					'content' => domTag('em', array(
						'content' => 'post\'s featured image'
					))
				)) . '. If you wish to delete it, unlink it from the post first.',
				'del_failure_avatar_featimg' => 'That media is currently in use in ' . domTag('strong', array(
					'content' => domTag('em', array(
						'content' => 'multiple places'
					))
				)) . '. If you wish to delete it, unlink it from the user and/or post first.',
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Fetch all media based on a specific status.
	 * @since 1.3.15-beta
	 *
	 * @access private
	 * @param null|string $search -- The search query.
	 * @param bool $all (optional) -- Whether to return all or set a limit (for pagination).
	 * @return array
	 */
	private function getResults(?string $search, bool $all = false): array {
		global $rs_query;
		
		$order_by = 'created';
		$order = 'DESC';
		$limit = $all === false ? array($this->paged['start'], $this->paged['per_page']) : 0;
		
		if(!is_null($search)) {
			// Search results
			return $rs_query->select(getTable('p'), '*', array(
				'title' => array('LIKE', '%' . $search . '%'),
				'type' => $this->post_type
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		} else {
			// All results
			return $rs_query->select(getTable('p'), '*', array(
				'type' => $this->post_type
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		}
	}
	
	/**
	 * Fetch the media count based on a specific status.
	 * @since 1.3.15-beta
	 *
	 * @access private
	 * @param null|string $search -- The search query.
	 * @return int
	 */
	private function getEntryCount(?string $search): int {
		return count($this->getResults($search, true));
	}
	
	/**
	 * Fetch all associated action links.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @param array $media -- The media's data.
	 * @return string
	 */
	private function getActionLinks(array $media): string {
		$actions = $this->admin_page['actions'];
		$action_list = array();
		
		foreach($actions as $key => $value) {
			if($value === 'upload') continue;
			
			$privileged = match($value) {
				'edit', 'replace' => userHasPrivilege('can_edit_media'),
				'delete' => userHasPrivilege('can_delete_media'),
				default => null
			};
			
			$action_list[] = array(
				'privileged' => $privileged,
				'link' => $value,
				'caption' => ucfirst($value)
			);
		}
		
		list($edit, $delete, $view, $replace) = $action_list;
		
		$action_links = array(
			// Edit
			$edit['privileged'] ? actionLink($edit['link'], array(
				'caption' => $edit['caption'],
				'id' => $media['id']
			)) : null,
			// Replace
			$replace['privileged'] ? actionLink($replace['link'], array(
				'caption' => $replace['caption'],
				'id' => $media['id']
			)) : null,
			// Delete
			$delete['privileged'] ? actionLink($delete['link'], array(
				'classes' => 'modal-launch delete-item',
				'data_item' => 'media',
				'caption' => $delete['caption'],
				'id' => $media['id']
			)) : null,
			// View
			mediaLink($media['id'], array(
				'link_text' => $view['caption'],
				'newtab' => 1
			))
		);
		
		// Filter out any empty actions
		$action_links = array_filter($action_links);
		
		return implode(' &bull; ', $action_links);
	}
}