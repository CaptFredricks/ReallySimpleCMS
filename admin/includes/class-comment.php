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
	 * @return null
	 */
	public function __construct($id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Create an array of columns to fetch from the database
		$cols = array_keys(get_object_vars($this));
		
		// Check whether the id is '0'
		if($id !== 0) {
			// Fetch the comment from the database
			$comment = $rs_query->selectRow('comments', $cols, array('id'=>$id));
			
			// Loop through the array and set the class variables
			foreach($comment as $key=>$value) $this->$key = $comment[$key];
		}
	}
	
	/**
	 * Construct a list of all comments in the database.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access public
	 * @return null
	 */
	public function listComments() {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Fetch the status of the currently displayed comments
		$status = $_GET['status'] ?? 'all';
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1>Comments</h1>
			<hr>
			<?php
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The comment was successfully deleted.', true);
			?>
			<ul class="status-nav">
				<?php
				// Fetch the comment entry count from the database (by status)
				$count = array('all'=>$this->getCommentCount(), 'approved'=>$this->getCommentCount('approved'), 'unapproved'=>$this->getCommentCount('unapproved'));
				
				// Loop through the comment counts (by status)
				foreach($count as $key=>$value) {
					?>
					<li><a href="<?php echo $_SERVER['PHP_SELF'].($key === 'all' ? '' : '?status='.$key); ?>"><?php echo ucfirst($key); ?> <span class="count">(<?php echo $value; ?>)</span></a></li>
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
		<table class="data-table">
			<thead>
				<?php
				// Fill an array with the table header columns
				$table_header_cols = array('Comment', 'Post', 'Author', 'Date Posted');
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Fetch all comments from the database (by status)
				if($status === 'all')
					$comments = $rs_query->select('comments', '*', '', 'date', 'DESC', array($page['start'], $page['per_page']));
				else
					$comments = $rs_query->select('comments', '*', array('status'=>$status), 'date', 'DESC', array($page['start'], $page['per_page']));
				
				// Loop through the comments
				foreach($comments as $comment) {
					// Set up the action links
					$actions = array(
						userHasPrivilege($session['role'], 'can_edit_comments') ? ($comment['status'] === 'approved' ? '<a href="?id='.$comment['id'].'&action=unapprove">Unapprove</a>' : '<a href="?id='.$comment['id'].'&action=approve">Approve</a>') : '',
						userHasPrivilege($session['role'], 'can_edit_comments') ? '<a href="?id='.$comment['id'].'&action=edit">Edit</a>' : '',
						userHasPrivilege($session['role'], 'can_delete_comments') ? '<a class="modal-launch delete-item" href="?id='.$comment['id'].'&action=delete" data-item="comment">Delete</a>' : '',
						'<a href="'.$this->getPostPermalink($comment['post']).'#comment-'.$comment['id'].'">View</a>'
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						tableCell(trimWords($comment['content']).($comment['status'] === 'unapproved' && $status === 'all' ? ' &mdash; <em>pending approval</em>' : '').'<div class="actions">'.implode(' &bull; ', $actions).'</div>', 'content'),
						tableCell($this->getPost($comment['post']), 'post'),
						tableCell($this->getAuthor($comment['author']), 'author'),
						tableCell(formatDate($comment['date'], 'd M Y @ g:i A'), 'date')
					);
				}
				
				// Display a notice if no comments are found
				if(empty($comments)) {
					echo tableRow(tableCell('There are no comments to display.', '', count($table_header_cols)));
				}
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
	 * Construct the 'Edit Comment' form.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access public
	 * @return null
	 */
	public function editComment() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the comment's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the 'List Comments' page
			redirect('comments.php');
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
						echo formRow(array('Content', true), array('tag'=>'textarea', 'class'=>'textarea-input', 'name'=>'content', 'cols'=>30, 'rows'=>10, 'content'=>htmlspecialchars($this->content)));
						echo formRow('Status', array('tag'=>'select', 'class'=>'select-input', 'name'=>'status', 'content'=>'<option value="'.$this->status.'">'.ucfirst($this->status).'</option>'.($this->status === 'approved' ? '<option value="unapproved">Unapproved</option>' : '<option value="approved">Approved</option>')));
						echo formRow('', array('tag'=>'hr', 'class'=>'separator'));
						echo formRow('', array('tag'=>'input', 'type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update Comment'));
						?>
					</table>
				</form>
			</div>
			<?php
		}
	}
	
	/**
	 * Approve a comment.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access public
	 * @return null
	 */
	public function approveComment() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the comment's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the 'List Comments' page
			redirect('comments.php');
		} else {
			// Set the comment's status to 'approved'
			$rs_query->update('comments', array('status'=>'approved'), array('id'=>$this->id));
			
			// Fetch the number of approved comments attached to the current comment's post
			$count = $rs_query->select('comments', 'COUNT(*)', array('post'=>$this->post, 'status'=>'approved'));
			
			// Update the post's comment count in the database
			$rs_query->update('postmeta', array('value'=>$count), array('post'=>$this->post, '_key'=>'comment_count'));
			
			// Redirect to the 'List Comments' page
			redirect('comments.php');
		}
	}
	
	/**
	 * Unapprove a comment.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access public
	 * @return null
	 */
	public function unapproveComment() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the comment's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the 'List Comments' page
			redirect('comments.php');
		} else {
			// Set the comment's status to 'unapproved'
			$rs_query->update('comments', array('status'=>'unapproved'), array('id'=>$this->id));
			
			// Fetch the number of approved comments attached to the current comment's post
			$count = $rs_query->select('comments', 'COUNT(*)', array('post'=>$this->post, 'status'=>'approved'));
			
			// Update the post's comment count in the database
			$rs_query->update('postmeta', array('value'=>$count), array('post'=>$this->post, '_key'=>'comment_count'));
			
			// Redirect to the 'List Comments' page
			redirect('comments.php');
		}
	}
	
	/**
	 * Delete a comment from the database.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access public
	 * @return null
	 */
	public function deleteComment() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the comment's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the 'List Comments' page
			redirect('comments.php');
		} else {
			// Delete the comment from the database
			$rs_query->delete('comments', array('id'=>$this->id));
			
			// Fetch the number of approved comments attached to the current comment's post
			$count = $rs_query->select('comments', 'COUNT(*)', array('post'=>$this->post, 'status'=>'approved'));
			
			// Update the post's comment count in the database
			$rs_query->update('postmeta', array('value'=>$count), array('post'=>$this->post, '_key'=>'comment_count'));
			
			// Redirect to the 'List Comments' page (with a success status)
			redirect('comments.php?exit_status=success');
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
	private function validateData($data, $id) {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['content']))
			return statusMessage('R');
		
		// Make sure the comment has a valid status
		if($data['status'] !== 'approved' && $data['status'] !== 'unapproved')
			$data['status'] = 'unapproved';
		
		// Update the comment in the database
		$rs_query->update('comments', array('content'=>$data['content'], 'status'=>$data['status']), array('id'=>$id));
		
		// Update the class variables
		foreach($data as $key=>$value) $this->$key = $value;
		
		// Return a status message
		return statusMessage('Comment updated! <a href="comments.php">Return to list</a>?', true);
	}
	
	/**
	 * Fetch a comment's post.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getPost($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's title from the database
		$title = $rs_query->selectField('posts', array('title'), array('id'=>$id));
		
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
	private function getPostPermalink($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post from the database
		$post = $rs_query->selectRow('posts', array('slug', 'parent', 'type'), array('id'=>$id));
		
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
	private function getAuthor($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the author's username from the database
		$author = $rs_query->selectField('users', 'username', array('id'=>$id));
		
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
	private function getCommentCount($status = '') {
		// Extend the Query object
		global $rs_query;
		
		// Check whether a status has been provided
		if(empty($status)) {
			// Return the count of all comments
			return $rs_query->select('comments', 'COUNT(*)');
		} else {
			// Return the count of all comments by the status
			return $rs_query->select('comments', 'COUNT(*)', array('status'=>$status));
		}
	}
}