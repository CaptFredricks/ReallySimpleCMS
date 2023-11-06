<?php
/**
 * Admin class used to implement the Comment object.
 * @since 1.1.0[b]{ss-01}
 *
 * Comments are left by users as feedback for a post on the front end of the site.
 * Comments can be created (front end only), moderated, and deleted.
 */
class Comment implements AdminInterface {
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
	 * @param int $id (optional) -- The comment's id.
	 */
	public function __construct(int $id = 0) {
		global $rs_query;
		
		$cols = array_keys(get_object_vars($this));
		
		if($id !== 0) {
			$comment = $rs_query->selectRow('comments', $cols, array(
				'id' => $id
			));
			
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
	public function listRecords(): void {
		global $rs_query;
		
		// Query vars
		$status = $_GET['status'] ?? 'all';
		$search = $_GET['search'] ?? null;
		$paged = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<?php
			// Page title
			echo domTag('h1', array(
				'content' => 'Comments'
			));
			
			// Search
			recordSearch(array(
				'status' => $status
			));
			
			// Info
			adminInfo();
			
			echo domTag('hr');
			
			// Notices
			if(!getSetting('enable_comments'))
				echo notice('Comments are currently disabled. You can enable them on the <a href="' . ADMIN . '/settings.php">settings page</a>.', 2, false, true);
			
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo exitNotice('The comment was successfully deleted.');
			?>
			<ul class="status-nav">
				<?php
				$keys = array('all', 'approved', 'unapproved', 'spam');
				$count = array();
				
				foreach($keys as $key) {
					if($key === 'all') {
						if(!is_null($search) && $key === $status)
							$count[$key] = $this->getCommentCount('', $search);
						else
							$count[$key] = $this->getCommentCount();
					} else {
						if(!is_null($search) && $key === $status)
							$count[$key] = $this->getCommentCount($key, $search);
						else
							$count[$key] = $this->getCommentCount($key);
					}
				}
				
				// Statuses
				foreach($count as $key => $value) {
					echo domTag('li', array(
						'content' => domTag('a', array(
							'href' => ADMIN_URI . ($key === 'all' ? '' : '?status=' . $key),
							'content' => ucfirst($key) . ' ' . domTag('span', array(
								'class' => 'count',
								'content' => '(' . $value . ')'
							))
						))
					));
					
					if($key !== array_key_last($count)) echo ' &bull; ';
				}
				?>
			</ul>
			<?php
			// Record count
			echo domTag('div', array(
				'class' => 'entry-count status',
				'content' => $count[$status] . ' ' . ($count[$status] === 1 ? 'entry' : 'entries')
			));
			?>
		</div>
		<table class="data-table has-bulk-select">
			<thead>
				<?php
				$table_header_cols = array(
					domTag('input', array(
						'type' => 'checkbox',
						'class' => 'checkbox bulk-selector'
					)),
					'Comment',
					'Post',
					'Author',
					'Date Posted'
				);
				
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				if($status === 'all')
					$db_status = array('<>', 'spam');
				else
					$db_status = $status;
				
				if(!is_null($search)) {
					// Search results
					$comments = $rs_query->select('comments', '*', array(
						'content' => array('LIKE', '%' . $search . '%'),
						'status' => $db_status
					), 'date', 'DESC', array(
						$paged['start'],
						$paged['per_page']
					));
				} else {
					// All results
					$comments = $rs_query->select('comments', '*', array(
						'status' => $db_status
					), 'date', 'DESC', array(
						$paged['start'],
						$paged['per_page']
					));
				}
				
				foreach($comments as $comment) {
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
						// Spam
						userHasPrivilege('can_edit_comments') ? actionLink('spam', array(
							'caption' => 'Spam',
							'id' => $comment['id']
						)) : null,
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
						domTag('a', array(
							'href' => $this->getPostPermalink($comment['post']) . '#comment-' . $comment['id'],
							'content' => 'View'
						))
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						// Bulk select
						tdCell(domTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox',
							'value' => $comment['id']
						)), 'bulk-select'),
						// Comment
						tdCell(trimWords($comment['content']) . ($comment['status'] === 'unapproved' &&
							$status === 'all' ? ' &mdash; ' .
							domTag('em', array(
								'content' => 'pending approval'
							)) : '') .
							domTag('div', array(
								'class' => 'actions',
								'content' => implode(' &bull; ', $actions)
							)), 'content'
						),
						// Post
						tdCell($this->getPost($comment['post']), 'post'),
						// Author
						tdCell($this->getAuthor($comment['author']), 'author'),
						// Date posted
						tdCell(formatDate($comment['date'], 'd M Y @ g:i A'), 'date')
					);
				}
				
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
		
		// Page navigation
		$paged['count'] = ceil($count[$status] / $paged['per_page']);
		echo pagerNav($paged['current'], $paged['count']);
		
        include_once PATH . ADMIN . INC . '/modal-delete.php';
	}
	
	/**
	 * Create a new comment.
	 * @since 1.3.10[b]
	 *
	 * @access public
	 */
	public function createRecord(): void {
		// Unused because comments are only created on the front end
	}
	
	/**
	 * Edit an existing comment.
	 * @since 1.1.0[b]{ss-02}
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
				<?php
				echo domTag('h1', array(
					'content' => 'Edit Comment'
				));
				
				echo $message;
				?>
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
							'content' => domTag('option', array(
								'value' => 'approved',
								'selected' => ($this->status === 'approved' ? 1 : 0),
								'content' => 'Approved'
							)) . domTag('option', array(
								'value' => 'unapproved',
								'selected' => ($this->status === 'unapproved' ? 1 : 0),
								'content' => 'Unapproved'
							)) . domTag('option', array(
								'value' => 'spam',
								'selected' => ($this->status === 'spam' ? 1 : 0),
								'content' => 'Spam'
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
	 * @param string $status -- The comment's status.
	 * @param int $id (optional) -- The comment's id.
	 */
	public function updateCommentStatus($status, $id = 0): void {
		global $rs_query;
		
		if($id !== 0) $this->id = $id;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			$rs_query->update('comments', array('status' => $status), array('id' => $this->id));
			
			if(is_null($this->post))
				$this->post = $rs_query->selectField('comments', 'post', array('id' => $this->id));
			
			// Update the approved comment count for the attached post
			$count = $rs_query->select('comments', 'COUNT(*)', array(
				'post' => $this->post,
				'status' => 'approved'
			));
			
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
		$this->updateCommentStatus('approved');
		
		redirect(ADMIN_URI);
	}
	
	/**
	 * Unapprove a comment.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access public
	 */
	public function unapproveComment(): void {
		$this->updateCommentStatus('unapproved');
		
		redirect(ADMIN_URI);
	}
	
	/**
	 * Send a comment to spam.
	 * @since 1.3.7[b]
	 *
	 * @access public
	 */
	public function spamComment(): void {
		$this->updateCommentStatus('spam');
		
		redirect(ADMIN_URI);
	}
	
	/**
	 * Delete an existing comment.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access public
	 */
	public function deleteRecord(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			$rs_query->delete('comments', array('id' => $this->id));
			
			// Update the approved comment count for the attached post
			$count = $rs_query->select('comments', 'COUNT(*)', array(
				'post' => $this->post,
				'status' => 'approved'
			));
			
			$rs_query->update('postmeta', array('value' => $count), array(
				'post' => $this->post,
				'_key' => 'comment_count'
			));
			
			redirect(ADMIN_URI . '?exit_status=success');
		}
	}
	
	/**
	 * Delete all spam comments.
	 * @since 1.3.7[b]
	 *
	 * @access public
	 */
	public function deleteSpamComments() {
		global $rs_query;
		
		$rs_query->delete('comments', array('status' => 'spam'));
	}
	
	/**
	 * Validate the form data.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @param int $id -- The comment's id.
	 * @return string
	 */
	private function validateData(array $data, int $id): string {
		global $rs_query;
		
		if(empty($data['content']))
			return exitNotice('REQ', -1);
		
		if($data['status'] !== 'approved' && $data['status'] !== 'unapproved')
			$data['status'] = 'unapproved';
		
		$rs_query->update('comments', array(
			'content' => $data['content'],
			'status' => $data['status']
		), array('id' => $id));
		
		// Update the class variables
		foreach($data as $key => $value) $this->$key = $value;
		
		return exitNotice('Comment updated! <a href="' . ADMIN_URI . '">Return to list</a>?');
	}
	
	/**
	 * Fetch a comment's post.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access private
	 * @param int $id -- The post's id.
	 * @return string
	 */
	private function getPost(int $id): string {
		global $rs_query;
		
		$title = $rs_query->selectField('posts', 'title', array(
			'id' => $id
		));
		
		return domTag('a', array(
			'href' => $this->getPostPermalink($id),
			'content' => $title
		));
	}
	
	/**
	 * Fetch a post's permalink.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access private
	 * @param int $id -- The post's id.
	 * @return string
	 */
	private function getPostPermalink(int $id): string {
		global $rs_query;
		
		$post = $rs_query->selectRow('posts', array(
			'slug',
			'parent',
			'type'
		), array(
			'id' => $id
		));
		
		return getPermalink($post['type'], $post['parent'], $post['slug']);
	}
	 
	/**
	 * Fetch a comment's author.
	 * @since 1.1.0[b]{ss-02}
	 *
	 * @access private
	 * @param int $id -- The author's id.
	 * @return string
	 */
	private function getAuthor(int $id): string {
		global $rs_query;
		
		$author = $rs_query->selectField('usermeta', 'value', array(
			'user' => $id,
			'_key' => 'display_name'
		));
		
		return empty($author) ? '&mdash;' : $author;
	}
	
	/**
	 * Fetch the comment count based on a specific status.
	 * @since 1.1.7[b]
	 *
	 * @access private
	 * @param string $status (optional) -- The comment's status.
	 * @param string $search (optional) -- The search query.
	 * @return int
	 */
	private function getCommentCount(string $status = '', string $search = ''): int {
		global $rs_query;
		
		if(empty($status))
			$db_status = array('<>', 'spam');
		else
			$db_status = $status;
		
		if(!empty($search)) {
			return $rs_query->select('comments', 'COUNT(*)', array(
				'content' => array('LIKE', '%' . $search . '%'),
				'status' => $db_status
			));
		} else {
			return $rs_query->select('comments', 'COUNT(*)', array('status' => $db_status));
		}
	}
	
	/**
	 * Construct bulk actions.
	 * @since 1.2.7[b]
	 *
	 * @access private
	 */
	private function bulkActions(): void {
		$status = $_GET['status'] ?? '';
		?>
		<div class="bulk-actions">
			<?php
			if(userHasPrivilege('can_edit_comments')) {
				echo formTag('select', array(
					'class' => 'actions',
					'content' => tag('option', array(
						'value' => 'approved',
						'content' => 'Approve'
					)) . tag('option', array(
						'value' => 'unapproved',
						'content' => 'Unapprove'
					)) . tag('option', array(
						'value' => 'spam',
						'content' => 'Spam'
					))
				));
				
				// Update status
				button(array(
					'class' => 'bulk-update',
					'title' => 'Bulk status update',
					'label' => 'Update'
				));
			}
			
			if(userHasPrivilege('can_delete_comments')) {
				// Delete
				button(array(
					'class' => 'bulk-delete',
					'title' => 'Bulk delete',
					'label' => 'Delete'
				));
				
				if($status === 'spam') {
					// Clear spam
					button(array(
						'class' => 'bulk-delete-spam',
						'title' => 'Delete all spam',
						'label' => 'Clear spam'
					));
				}
			}
			?>
		</div>
		<?php
	}
}