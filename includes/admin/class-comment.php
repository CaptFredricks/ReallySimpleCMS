<?php
/**
 * Admin class used to implement the Comment object.
 * Comments are left by users as feedback for a post on the front end of the site.
 * Comments can be created (front end only), moderated, and deleted.
 * @since 1.1.0-beta_snap-01
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_comment
 *
 * ## VARIABLES [12] ##
 * - private int $id
 * - private int $post
 * - private int $author
 * - private string $created
 * - private string $content
 * - private int $upvotes
 * - private int $downvotes
 * - private string $status
 * - private int $parent
 * - private array $admin_page
 * - private string $action
 * - private array $paged
 *
 * ## METHODS [20] ##
 * - public __construct(int $id, string $action)
 * { LISTS, FORMS, & ACTIONS [9] }
 * - public listRecords(): void
 * - public createRecord(): void
 * - public editRecord(): void
 * - public updateCommentStatus(string $status, int $id): void
 * - public approveComment(): void
 * - public unapproveComment(): void
 * - public spamComment(): void
 * - public deleteRecord(): void
 * - public deleteSpamComments(): void
 * { VALIDATION [1] }
 * - private validateSubmission(array $data): string
 * { MISCELLANEOUS [9] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private bulkActions(): void
 * - private getPost(int $id): string
 * - private getPostPermalink(int $id): string
 * - private getAuthor(int $id): string
 * - private getResults(string $status, ?string $search, bool $all): array
 * - private getEntryCount(string $status, ?string $search): int
 * - private getActionLinks(array $comment): string
 */
namespace Admin;

class Comment implements AdminInterface {
	/**
	 * The currently queried comment's id.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access private
	 * @var int
	 */
	private $id;
	
	/**
	 * The post the currently queried comment is attached to.
	 * @since 1.1.7-beta
	 *
	 * @access private
	 * @var int
	 */
	private $post;
	
	/**
	 * The currently queried comment's author.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var int
	 */
	private $author;
	
	/**
	 * The currently queried comment's creation date.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $created;
	
	/**
	 * The currently queried comment's content.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access private
	 * @var string
	 */
	private $content;
	
	/**
	 * The currently queried comment's upvotes.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var int
	 */
	private $upvotes;
	 
	/**
	 * The currently queried comment's downvotes.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var int
	 */
	private $downvotes;
	
	/**
	 * The currently queried comment's status.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access private
	 * @var string
	 */
	private $status;
	
	/**
	 * The currently queried comment's parent.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var int
	 */
	private $parent;
	
	/**
	 * The admin page's data.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @var array
	 */
	private $admin_page = array();
	
	/**
	 * The current action.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $action;
	
	/**
	 * The pagination.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var array
	 */
	private $paged = array();
	
	/**
	 * Class constructor.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 * @param string $action -- The current action.
	 */
	public function __construct(int $id, string $action) {
		global $rs_query, $rs_admin_pages;
		
		$this->action = $action;
		$this->admin_page = $rs_admin_pages[basename($_SERVER['PHP_SELF'], '.php')];
		
		if($id > 0) {
			$cols = array_keys(get_object_vars($this));
			$exclude = array('admin_page', 'action', 'paged');
			$cols = array_diff($cols, $exclude);
			
			$comment = $rs_query->selectRow(getTable('c'), $cols, array(
				'id' => $id
			));
			
			foreach($comment as $key => $value) $this->$key = $comment[$key];
		} else {
			$this->id = 0;
		}
	}
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct a list of all comments in the database.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access public
	 */
	public function listRecords(): void {
		// Query vars
		$status = $_GET['status'] ?? 'all';
		$search = $_GET['search'] ?? null;
		$this->paged = paginate((int)($_GET['paged'] ?? 1));
		
		$this->pageHeading();
		?>
		<table class="data-table has-bulk-select">
			<thead>
				<?php
				$header_cols = array(
					'bulk-select' => domTag('input', array(
						'type' => 'checkbox',
						'class' => 'checkbox bulk-selector'
					)),
					'content' => 'Comment',
					'post' => 'Post',
					'author' => 'Author',
					'posted-date' => 'Posted Date',
					'upvotes' => domTag('i', array(
						'class' => 'fa-solid fa-thumbs-up',
						'title' => 'Upvotes'
					)),
					'downvotes' => domTag('i', array(
						'class' => 'fa-solid fa-thumbs-down',
						'title' => 'Downvotes'
					))
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$comments = $this->getResults($status, $search);
				
				foreach($comments as $comment) {
					echo tableRow(
						// Bulk select
						tdCell(domTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox',
							'value' => $comment['id']
						)), 'bulk-select'),
						// Comment
						tdCell(trimWords($comment['content']) . ($comment['status'] === 'pending' && $status === 'all' ?
							' &mdash; ' . domTag('em', array(
								'content' => 'pending approval'
							)) : '') .
							domTag('div', array(
								'class' => 'actions',
								'content' => $this->getActionLinks($comment)
							)), 'content'
						),
						// Post
						tdCell($this->getPost($comment['post']), 'post'),
						// Author
						tdCell($this->getAuthor($comment['author']), 'author'),
						// Date posted
						tdCell(formatDate($comment['created'], 'd M Y @ g:i A'), 'posted-date'),
						// Upvotes
						tdCell($comment['upvotes'], 'upvotes'),
						// Downvotes
						tdCell($comment['downvotes'], 'downvotes')
					);
				}
				
				if(empty($comments))
					echo tableRow(tdCell($this->admin_page['labels']['no_items'], '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Bulk actions
		if(!empty($comments)) $this->bulkActions();
		
		// Set up page navigation
		echo pagerNav($this->paged['current'], $this->paged['count']);
		
		includeFile(PATH . MODALS . '/modal-delete.php');
	}
	
	/**
	 * Create a new comment.
	 * @since 1.3.10-beta
	 *
	 * @access public
	 */
	public function createRecord(): void {
		// Unused because comments are only created on the front end
	}
	
	/**
	 * Edit an existing comment.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access public
	 */
	public function editRecord(): void {
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Content
					echo formRow(array('Content', true), array(
						'tag' => 'textarea',
						'id' => 'content-field',
						'class' => 'textarea-input',
						'name' => 'content',
						'cols' => 30,
						'rows' => 10,
						'content' => htmlspecialchars($this->content)
					));
					
					// Status
					echo formRow('Status', array(
						'tag' => 'select',
						'id' => 'status-field',
						'class' => 'select-input',
						'name' => 'status',
						'content' => domTag('option', array(
							'value' => 'approved',
							'selected' => ($this->status === 'approved' ? 1 : 0),
							'content' => 'Approved'
						)) . domTag('option', array(
							'value' => 'pending',
							'selected' => ($this->status === 'pending' ? 1 : 0),
							'content' => 'Pending'
						)) . domTag('option', array(
							'value' => 'spam',
							'selected' => ($this->status === 'spam' ? 1 : 0),
							'content' => 'Spam'
						))
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
		</div>
		<?php
	}
	
	/**
	 * Update a comment's status.
	 * @since 1.2.9-beta
	 *
	 * @access public
	 * @param string $status -- The comment's status.
	 * @param int $id (optional) -- The comment's id.
	 */
	public function updateCommentStatus(string $status, int $id = 0): void {
		global $rs_query;
		
		if($id !== 0) $this->id = $id;
		
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$rs_query->update(getTable('c'), array(
			'status' => $status
		), array(
			'id' => $this->id
		));
		
		if(is_null($this->post)) {
			$this->post = $rs_query->selectField(getTable('c'), 'post', array(
				'id' => $this->id
			));
		}
		
		// Update the approved comment count for the attached post
		$count = $rs_query->select(getTable('c'), 'COUNT(*)', array(
			'post' => $this->post,
			'status' => 'approved'
		));
		
		$rs_query->update(getTable('pm'), array(
			'value' => $count
		), array(
			'post' => $this->post,
			'key' => 'comment_count'
		));
	}
	
	/**
	 * Approve a comment.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access public
	 */
	public function approveComment(): void {
		$this->updateCommentStatus('approved');
		
		redirect(ADMIN_URI);
	}
	
	/**
	 * Unapprove a comment.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access public
	 */
	public function unapproveComment(): void {
		$this->updateCommentStatus('pending');
		
		redirect(ADMIN_URI);
	}
	
	/**
	 * Send a comment to spam.
	 * @since 1.3.7-beta
	 *
	 * @access public
	 */
	public function spamComment(): void {
		$this->updateCommentStatus('spam');
		
		redirect(ADMIN_URI);
	}
	
	/**
	 * Delete an existing comment.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access public
	 */
	public function deleteRecord(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$rs_query->delete(getTable('c'), array(
			'id' => $this->id
		));
		
		// Update the approved comment count for the attached post
		$count = $rs_query->select(getTable('c'), 'COUNT(*)', array(
			'post' => $this->post,
			'status' => 'approved'
		));
		
		$rs_query->update(getTable('pm'), array(
			'value' => $count
		), array(
			'post' => $this->post,
			'key' => 'comment_count'
		));
		
		redirect(ADMIN_URI . getQueryString(array(
			'exit_status' => 'del_success'
		)));
	}
	
	/**
	 * Delete all spam comments.
	 * @since 1.3.7-beta
	 *
	 * @access public
	 */
	public function deleteSpamComments(): void {
		global $rs_query;
		
		$rs_query->delete(getTable('c'), array(
			'status' => 'spam'
		));
		
		redirect(ADMIN_URI . getQueryString(array(
			'exit_status' => 'del_spam_success'
		)));
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the form data.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateSubmission(array $data): string {
		global $rs_query;
		
		if(empty($data['content'])) {
			return exitNotice('REQ', -1);
			exit;
		}
		
		if($data['status'] !== 'approved' && $data['status'] !== 'pending')
			$data['status'] = 'pending';
		
		$rs_query->update(getTable('c'), array(
			'content' => $data['content'],
			'status' => $data['status']
		), array(
			'id' => $this->id
		));
		
		foreach($data as $key => $value) $this->$key = $value;
		
		redirect(ADMIN_URI . getQueryString(array(
			'id' => $this->id,
			'action' => $this->action,
			'exit_status' => 'edit_success'
		)));
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
		
		switch($this->action) {
			case 'create':
				// unused
				break;
			case 'edit':
				$title = $labels['edit_item'] . ': { by ' . domTag('em', array(
					'content' => $this->getAuthor($this->author)
				)) . ' }';
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			default:
				$title = $labels['name'];
				$status = $_GET['status'] ?? 'all';
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
				// Search
				recordSearch(array(
					'status' => $status
				));
				
				// Info
				adminInfo();
				
				domTagPr('hr');
				
				// Notices
				if(!getSetting('enable_comments')) {
					echo notice('Comments are currently disabled. You can enable them on the ' . domTag('a', array(
						'href' => ADMIN . '/settings.php',
						'content' => 'settings page'
					)) . '.', 2, false, true);
				}
				
				// Exit notices
				if(isset($_GET['exit_status']))
					echo $this->exitNotice($_GET['exit_status']);
				?>
				<ul class="status-nav">
					<?php
					$keys = array('all', 'approved', 'pending', 'spam');
					$count = array();
					
					foreach($keys as $key)
						$count[$key] = $this->getEntryCount($key, $search);
					
					// Statuses
					foreach($count as $key => $value) {
						domTagPr('li', array(
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
				domTagPr('div', array(
					'class' => 'entry-count status',
					'content' => $count[$status] . ' ' . ($count[$status] === 1 ? 'entry' : 'entries')
				));
				
				$this->paged['count'] = ceil($count[$status] / $this->paged['per_page']);
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
			'edit_success' => 'Comment updated! ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'del_success' => 'The comment was successfully deleted.',
			'del_spam_success' => 'All spam comments were successfully deleted.',
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Construct bulk actions.
	 * @since 1.2.7-beta
	 *
	 * @access private
	 */
	private function bulkActions(): void {
		// Query vars
		$status = $_GET['status'] ?? '';
		?>
		<div class="bulk-actions">
			<?php
			if(userHasPrivilege('can_edit_comments')) {
				domTagPr('select', array(
					'class' => 'actions',
					'name' => 'bulk_actions',
					'content' => domTag('option', array(
						'value' => 'approved',
						'content' => 'Approve'
					)) . domTag('option', array(
						'value' => 'pending',
						'content' => 'Unapprove'
					)) . domTag('option', array(
						'value' => 'spam',
						'content' => 'Spam'
					))
				));
				
				// Update status
				button(array(
					'class' => 'bulk-update',
					'title' => 'Bulk status update',
					'label' => $this->admin_page['labels']['bulk_update']
				));
			}
			
			if(userHasPrivilege('can_delete_comments')) {
				// Delete
				button(array(
					'class' => 'bulk-delete',
					'title' => 'Bulk delete',
					'label' => $this->admin_page['labels']['bulk_delete']
				));
				
				if($status === 'spam') {
					// Clear spam
					button(array(
						'class' => 'bulk-delete-spam',
						'title' => 'Delete all spam',
						'label' => 'Clear Spam'
					));
				}
			}
			?>
		</div>
		<?php
	}
	
	/**
	 * Fetch a comment's post.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access private
	 * @param int $id -- The post's id.
	 * @return string
	 */
	private function getPost(int $id): string {
		global $rs_query;
		
		$title = $rs_query->selectField(getTable('p'), 'title', array(
			'id' => $id
		));
		
		return domTag('a', array(
			'href' => $this->getPostPermalink($id),
			'content' => $title
		));
	}
	
	/**
	 * Fetch a post's permalink.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access private
	 * @param int $id -- The post's id.
	 * @return string
	 */
	private function getPostPermalink(int $id): string {
		global $rs_query;
		
		$post = $rs_query->selectRow(getTable('p'), array('slug', 'parent', 'type'), array(
			'id' => $id
		));
		
		return getPermalink($post['type'], $post['parent'], $post['slug']);
	}
	 
	/**
	 * Fetch a comment's author.
	 * @since 1.1.0-beta_snap-02
	 *
	 * @access private
	 * @param int $id -- The author's id.
	 * @return string
	 */
	private function getAuthor(int $id): string {
		global $rs_query;
		
		$author = $rs_query->selectField(getTable('um'), 'value', array(
			'user' => $id,
			'key' => 'display_name'
		));
		
		return empty($author) ? 'Anonymous' : $author;
	}
	
	/**
	 * Fetch all comments based on a specific status.
	 * @since 1.3.15-beta
	 *
	 * @access private
	 * @param string $status -- The comment's status.
	 * @param null|string $search -- The search query.
	 * @param bool $all (optional) -- Whether to return all or set a limit (for pagination).
	 * @return array
	 */
	private function getResults(string $status, ?string $search, bool $all = false): array {
		global $rs_query;
		
		$order_by = 'created';
		$order = 'DESC';
		$limit = $all === false ? array($this->paged['start'], $this->paged['per_page']) : 0;
		
		if($status === 'all')
			$db_status = array('<>', 'spam');
		else
			$db_status = $status;
		
		if(!is_null($search)) {
			// Search results
			return $rs_query->select(getTable('c'), '*', array(
				'content' => array('LIKE', '%' . $search . '%'),
				'status' => $db_status
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		} else {
			// All results
			return $rs_query->select(getTable('c'), '*', array(
				'status' => $db_status
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		}
	}
	
	/**
	 * Fetch the comment count based on a specific status.
	 * @since 1.1.7-beta
	 *
	 * @access private
	 * @param string $status -- The comment's status.
	 * @param null|string $search -- The search query.
	 * @return int
	 */
	private function getEntryCount(string $status, ?string $search): int {
		return count($this->getResults($status, $search, true));
	}
	
	/**
	 * Fetch all associated action links.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @param array $comment -- The comment's data.
	 * @return string
	 */
	private function getActionLinks(array $comment): string {
		$actions = $this->admin_page['actions'];
		$action_list = array();
		
		foreach($actions as $key => $value) {
			$privileged = match($value) {
				'edit', 'approve', 'unapprove', 'spam' => userHasPrivilege('can_edit_comments'),
				'delete' => userHasPrivilege('can_delete_comments'),
				default => null
			};
			
			$action_list[] = array(
				'privileged' => $privileged,
				'link' => $value,
				'caption' => ucfirst($value)
			);
		}
		
		list($edit, $delete, $view, $approve, $unapprove, $spam) = $action_list;
		
		$action_links = array(
			// Approve/unapprove
			$approve['privileged'] && $unapprove['privileged'] ? ($comment['status'] === 'approved' ?
				actionLink($unapprove['link'], array(
					'caption' => $unapprove['caption'],
					'id' => $comment['id']
				)) : actionLink($approve['link'], array(
					'caption' => $approve['caption'],
					'id' => $comment['id']
				))
			) : null,
			// Spam
			$spam['privileged'] ? ($comment['status'] !== 'spam' ?
				actionLink($spam['link'], array(
					'caption' => $spam['caption'],
					'id' => $comment['id']
				)) : null
			) : null,
			// Edit
			$edit['privileged'] ? actionLink($edit['link'], array(
				'caption' => $edit['caption'],
				'id' => $comment['id']
			)) : null,
			// Delete
			$delete['privileged'] ? actionLink($delete['link'], array(
				'classes' => 'modal-launch delete-item',
				'data_item' => 'comment',
				'caption' => $delete['caption'],
				'id' => $comment['id']
			)) : null,
			// View
			domTag('a', array(
				'href' => $this->getPostPermalink($comment['post']) . '#comment-' . $comment['id'],
				'content' => $view['caption']
			))
		);
		
		// Filter out any empty actions
		$action_links = array_filter($action_links);
		
		return implode(' &bull; ', $action_links);
	}
}