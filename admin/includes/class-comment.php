<?php
/**
 * Admin class used to implement the Comment object.
 * @since 1.1.0[b]{ss-01}
 *
 * Comments are left by users as feedback for a post on the front end of the site.
 * Comments can be created (front end only), moderated, and deleted.
 */
class Comment {
	/**
	 * The currently queried comment's id.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access private
	 * @var int
	 */
	private $id;
	
	/**
	 * The post the currently queried comment is attached to.
	 * @since 1.1.7[b]
	 *
	 * @access private
	 * @var int
	 */
	private $post;
	
	/**
	 * The currently queried comment's content.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access private
	 * @var string
	 */
	private $content;
	
	/**
	 * The currently queried comment's status.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access private
	 * @var string
	 */
	private $status;
	
	/**
	 * Class constructor.
	 * @since 1.1.0[b]{ss-02}
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
			// Fetch the comment from the database
			$comment = $rs_query->selectRow('comments', $cols, array('id' => $id));
			
			// Set the class variable values
			foreach($comment as $key => $value) $this->$key = $comment[$key];
		}
	}
	
	/**
	 * Construct a list of all comments in the database.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access public
	 */
	public function listComments(): void {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the status of the currently displayed comments
		$status = $_GET['status'] ?? 'all';
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Comments</h1>
			<?php adminInfo(); ?>
			<hr>
			<?php
			// Check whether any status messages have been returned and display them if so
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The comment was successfully deleted.', true);
			?>
			<ul class="status-nav">
				<?php
				// Create keys for each of the possible statuses
				$keys = array('all', 'approved', 'unapproved');
				$count = array();
				
				// Fetch the comment entry count from the database (by status)
				foreach($keys as $key) {
					if($key === 'all')
						$count[$key] = $this->getCommentCount();
					else
						$count[$key] = $this->getCommentCount($key);
				}
				
				foreach($count as $key => $value) {
					?>
					<li><a href="<?php echo ADMIN_URI.($key === 'all' ? '' : '?status='.$key); ?>"><?php echo ucfirst($key); ?> <span class="count">(<?php echo $value; ?>)</span></a></li>
					<?php
					// Add bullets in between
					if($key !== array_key_last($count)) {
						?> &bull; <?php
					}
				}
				?>
			</ul>
			<?php
			// Set the page count
			$page['count'] = ceil($count[$status] / $page['per_page']);
			?>
			<div class="entry-count status">
				<?php
				// Display the entry count for the current status
				echo $count[$status].' '.($count[$status] === 1 ? 'entry' : 'entries');
				?>
			</div>
		</div>
		<table class="data-table has-bulk-select">
			<thead>
				<?php
				// Fill an array with the table header columns
				$table_header_cols = array(
					tag('input', array(
						'type' => 'checkbox',
						'class' => 'checkbox bulk-selector'
					)),
					'Comment',
					'Post',
					'Author',
					'Date Posted'
				);
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all comments from the database (by status)
				if($status === 'all') {
					$comments = $rs_query->select('comments', '*', '', 'date', 'DESC', array(
						$page['start'],
						$page['per_page']
					));
				} else {
					$comments = $rs_query->select('comments', '*', array(
						'status' => $status
					), 'date', 'DESC', array(
						$page['start'],
						$page['per_page']
					));
				}
				
				foreach($comments as $comment) {
					// Set up the action links
					$actions = array(
						// Approve/unapprove
						userHasPrivilege('can_edit_comments'
							) ? ($comment['status'] === 'approved' ? actionLink('unapprove', array(
								'caption' => 'Unapprove',
								'id' => $comment['id']
							)) : actionLink('approve', array(
								'caption' => 'Approve',
								'id' => $comment['id']
							))) : null,
						// Edit
						userHasPrivilege('can_edit_comments') ? actionLink('edit', array(
								'caption' => 'Edit',
								'id' => $comment['id']
							)) : null,
						// Delete
						userHasPrivilege('can_delete_comments') ? actionLink('delete', array(
								'classes' => 'modal-launch delete-item',
								'data_item' => 'comment',
								'caption' => 'Delete',
								'id' => $comment['id']
							)) : null,
						// View
						'<a href="'.$this->getPostPermalink($comment['post']).'#comment-'.$comment['id'].'">View</a>'
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						// Bulk select
						tdCell(tag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox',
							'value' => $comment['id']
						)), 'bulk-select'),
						// Comment
						tdCell(trimWords($comment['content']).($comment['status'] === 'unapproved' && $status === 'all' ? ' &mdash; <em>pending approval</em>' : '').'<div class="actions">'.implode(' &bull; ', $actions).'</div>', 'content'),
						// Post
						tdCell($this->getPost($comment['post']), 'post'),
						// Author
						tdCell($this->getAuthor($comment['author']), 'author'),
						// Date posted
						tdCell(formatDate($comment['date'], 'd M Y @ g:i A'), 'date')
					);
				}
				
				// Display a notice if no comments are found
				if(empty($comments))
					echo tableRow(tdCell('There are no comments to display.', '', count($table_header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($table_header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Bulk actions
		if(!empty($comments)) $this->bulkActions();
		
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
		
		// Include the delete modal
        include_once PATH.ADMIN.INC.'/modal-delete.php';
	}
	
	/**
	 * Edit a comment.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access public
	 */
	public function editComment(): void {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the comment's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Comments" page
			redirect(ADMIN_URI);
		} else {
			// Validate the form data and return any messages
			$message = isset($_POST['submit']) ? $this->validateData($_POST, $this->id) : '';
			?>
			<div class="heading-wrap">
				<h1>Edit Comment</h1>
				<?php echo $message; ?>
			</div>
			<div class="data-form-wrap clear">
				<form class="data-form" action="" method="post" autocomplete="off">
					<table class="form-table">
						<?php
						// Content
						echo formRow(array('Content', true), array(
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
							'content' => '<option value="'.$this->status.'">'.ucfirst($this->status).'</option>'.($this->status === 'approved' ? '<option value="unapproved">Unapproved</option>' : '<option value="approved">Approved</option>')
						));
						
						// Separator
						echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
						
						// Submit button
						echo formRow('', array(
							'tag' => 'input',
							'type' => 'submit',
							'class' => 'submit-input button',
							'name' => 'submit',
							'value' => 'Update Comment'
						));
						?>
					</table>
				</form>
			</div>
			<?php
		}
	}
	
	/**
	 * Update a comment's status.
	 * @since 1.2.9[b]
	 *
	 * @access public
	 * @param string $status
	 * @param int $id (optional; default: 0)
	 */
	public function updateCommentStatus($status, $id = 0): void {
		// Extend the Query object
		global $rs_query;
		
		// If the provided id is not zero, update the class id to match it
		if($id !== 0) $this->id = $id;
		
		// Check whether the comment's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Comments" page
			redirect(ADMIN_URI);
		} else {
			// Update the comment's status
			$rs_query->update('comments', array('status' => $status), array('id' => $this->id));
			
			// Fetch the number of approved comments attached to the current comment's post
			$count = $rs_query->select('comments', 'COUNT(*)', array(
				'post' => $this->post,
				'status' => 'approved'
			));
			
			// Update the post's comment count in the database
			$rs_query->update('postmeta', array('value' => $count), array(
				'post' => $this->post,
				'_key' => 'comment_count'
			));
		}
	}
	
	/**
	 * Approve a comment.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access public
	 */
	public function approveComment(): void {
		// Set the comment's status to 'approved'
		$this->updateCommentStatus('approved');
		
		// Redirect to the "List Comments" page
		redirect(ADMIN_URI);
	}
	
	/**
	 * Unapprove a comment.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access public
	 */
	public function unapproveComment(): void {
		// Set the comment's status to 'unapproved'
		$this->updateCommentStatus('unapproved');
		
		// Redirect to the "List Comments" page
		redirect(ADMIN_URI);
	}
	
	/**
	 * Delete a comment.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access public
	 */
	public function deleteComment(): void {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the comment's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Comments" page
			redirect(ADMIN_URI);
		} else {
			// Delete the comment from the database
			$rs_query->delete('comments', array('id' => $this->id));
			
			// Fetch the number of approved comments attached to the current comment's post
			$count = $rs_query->select('comments', 'COUNT(*)', array(
				'post' => $this->post,
				'status' => 'approved'
			));
			
			// Update the post's comment count in the database
			$rs_query->update('postmeta', array('value' => $count), array(
				'post' => $this->post,
				'_key' => 'comment_count'
			));
			
			// Redirect to the "List Comments" page with an appropriate exit status
			redirect(ADMIN_URI.'?exit_status=success');
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access private
	 * @param array $data
	 * @param int $id
	 * @return string
	 */
	private function validateData($data, $id): string {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['content']))
			return statusMessage('R');
		
		// Make sure the comment has a valid status
		if($data['status'] !== 'approved' && $data['status'] !== 'unapproved')
			$data['status'] = 'unapproved';
		
		// Update the comment in the database
		$rs_query->update('comments', array(
			'content' => $data['content'],
			'status' => $data['status']
		), array('id' => $id));
		
		// Update the class variables
		foreach($data as $key => $value) $this->$key = $value;
		
		// Return a status message
		return statusMessage('Comment updated! <a href="'.ADMIN_URI.'">Return to list</a>?', true);
	}
	
	/**
	 * Fetch a comment's post.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getPost($id): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's title from the database
		$title = $rs_query->selectField('posts', array('title'), array('id' => $id));
		
		// Return the post's title
		return '<a href="'.$this->getPostPermalink($id).'">'.$title.'</a>';
	}
	
	/**
	 * Fetch a post's permalink.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getPostPermalink($id): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post from the database
		$post = $rs_query->selectRow('posts', array(
			'slug',
			'parent',
			'type'
		), array('id' => $id));
		
		// Return the permalink
		return getPermalink($post['type'], $post['parent'], $post['slug']);
	}
	 
	/**
	 * Fetch a comment's author.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getAuthor($id): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the author's username from the database
		$author = $rs_query->selectField('users', 'username', array('id' => $id));
		
		// Return the username
		return empty($author) ? '&mdash;' : $author;
	}
	
	/**
	 * Fetch the comment count based on a specific status.
	 * @since 1.1.7[b]
	 *
	 * @access private
	 * @param string $status (optional; default: '')
	 * @return int
	 */
	private function getCommentCount($status = ''): int {
		// Extend the Query object
		global $rs_query;
		
		// Check whether a status has been provided
		if(empty($status)) {
			// Return the count of all comments
			return $rs_query->select('comments', 'COUNT(*)');
		} else {
			// Return the count of all comments by the status
			return $rs_query->select('comments', 'COUNT(*)', array('status' => $status));
		}
	}
	
	/**
	 * Construct bulk actions.
	 * @since 1.2.7[b]
	 *
	 * @access private
	 */
	private function bulkActions(): void {
		?>
		<div class="bulk-actions">
			<?php
			// Make sure the user has the required permissions
			if(userHasPrivilege('can_edit_comments')) {
				?>
				<select class="actions">
					<option value="approved">Approve</option>
					<option value="unapproved">Unapprove</option>
				</select>
				<?php
				// Update status
				button(array(
					'class' => 'bulk-update',
					'title' => 'Bulk approve/unapprove',
					'label' => 'Update'
				));
			}
			
			// Make sure the user has the required permissions
			if(userHasPrivilege('can_delete_comments')) {
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