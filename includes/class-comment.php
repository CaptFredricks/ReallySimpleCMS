<?php
/**
 * Core class used to implement the Comment object.
 * @since 1.1.0[b]{ss-03}
 *
 * This class loads data from the comments table of the database for use on the front end of the CMS.
 */
class Comment {
	/**
	 * The currently queried comment's post.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access private
	 * @var int
	 */
	private $post;
	
	/**
	 * Class constructor.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $post (optional; default: 0)
	 */
	public function __construct($post = 0) {
		$this->post = $post;
	}
	
	/**
	 * Fetch a comment's author.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @return string
	 */
	public function getCommentAuthor($id): string {
		// Extend the Query object
		global $rs_query;
		
		$author_id = (int)$rs_query->selectField('comments', 'author', array('id' => $id));
		
		if($author_id === 0) {
			$author = 'Anonymous';
		} else {
			$author = $rs_query->selectField('usermeta', 'value', array(
				'user' => $author_id,
				'_key' => 'display_name'
			));
		}
		
		return $author;
	}
	
	/**
	 * Fetch a comment's author id.
	 * @since 1.1.0[b]{ss-04}
	 *
	 * @access public
	 * @param int $id
	 * @return int
	 */
	public function getCommentAuthorId($id): int {
		// Extend the Query object
		global $rs_query;
		
		return (int)$rs_query->selectField('comments', 'author', array('id' => $id));
	}
	
	/**
	 * Fetch a comment's date.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @return string
	 */
	public function getCommentDate($id): string {
		// Extend the Query object
		global $rs_query;
		
		$date = $rs_query->selectField('comments', 'date', array('id' => $id));
		
		return formatDate($date, 'j M Y @ g:i A');
	}
	
	/**
	 * Fetch a comment's content.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @return string
	 */
	public function getCommentContent($id): string {
		// Extend the Query object
		global $rs_query;
		
		return $rs_query->selectField('comments', 'content', array('id' => $id));
	}
	
	/**
	 * Fetch a comment's upvotes.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @return int
	 */
	public function getCommentUpvotes($id): int {
		// Extend the Query object
		global $rs_query;
		
		return (int)$rs_query->selectField('comments', 'upvotes', array('id' => $id));
	}
	
	/**
	 * Fetch a comment's downvotes.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @return int
	 */
	public function getCommentDownvotes($id): int {
		// Extend the Query object
		global $rs_query;
		
		return (int)$rs_query->selectField('comments', 'downvotes', array('id' => $id));
	}
	
	/**
	 * Fetch a comment's status.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @return string
	 */
	public function getCommentStatus($id): string {
		// Extend the Query object
		global $rs_query;
		
		return $rs_query->selectField('comments', 'status', array('id' => $id));
	}
	
	/**
	 * Fetch a comment's parent.
	 * @since 1.1.0[b]{ss-04}
	 *
	 * @access public
	 * @param int $id
	 * @return int
	 */
	public function getCommentParent($id): int {
		// Extend the Query object
		global $rs_query;
		
		return (int)$rs_query->selectField('comments', 'parent', array('id' => $id));
	}
	
	/**
	 * Fetch a comment's permalink.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @return string
	 */
	public function getCommentPermalink($id): string {
		// Extend the Query object
		global $rs_query;
		
		$post = $rs_query->selectRow('posts', array('slug', 'parent', 'type'), array('id' => $this->post));
		
		return getPermalink($post['type'], $post['parent'], $post['slug']) . '#comment-' . $id;
	}
	
	/**
	 * Fetch the number of comments assigned to the current post.
	 * @since 1.1.0[b]{ss-04}
	 *
	 * @access public
	 * @param int $post
	 * @return int
	 */
	public function getCommentCount($post): int {
		// Extend the Query object
		global $rs_query;
		
		return $rs_query->select('comments', 'COUNT(*)', array('post' => $post));
	}
	
	/**
	 * Construct a comment reply box.
	 * @since 1.1.0[b]{ss-04}
	 *
	 * @access public
	 */
	public function getCommentReplyBox(): void {
		// Extend the Post object, the user's session data, and the post types array
		global $rs_post, $session, $post_types;
		
		// Check whether comments are enabled
		if(getSetting('enable_comments') && $post_types[$rs_post->getPostType()]['comments'] &&
			$rs_post->getPostMeta('comment_status')) {
				
			// Check whether the user is logged in, and if not, check whether anonymous users can comment
			if(!is_null($session) || (is_null($session) && getSetting('allow_anon_comments'))) {
				?>
				<div id="comments-reply" class="textarea-wrap">
					<div id="reply-to"></div>
					<input type="hidden" name="post" value="<?php echo $rs_post->getPostId(); ?>">
					<input type="hidden" name="replyto" value="0">
					<textarea class="textarea-input" cols="60" rows="8" placeholder="Leave a comment"></textarea>
					<button type="submit" class="submit-comment button" disabled>Submit</button>
				</div>
				<?php
			}
		}
	}
	
	/**
	 * Construct a feed of comments.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 */
	public function getCommentFeed(): void {
		?>
		<div class="comments-wrap">
			<?php $this->loadComments(); ?>
		</div>
		<?php
	}
	
	/**
	 * Load a specified number of comments.
	 * @since 1.2.2[b]
	 *
	 * @access public
	 * @param int $start (optional; default: 0)
	 * @param int $count (optional; default: 10)
	 */
	public function loadComments($start = 0, $count = 10): void {
		// Extend the Query and Post objects, the user's session data, and the post types array
		global $rs_query, $rs_post, $session, $post_types;
		
		// Fetch the specified number of comments from the database
		$comments = $rs_query->select('comments', 'id', array(
			'post' => $this->post,
			'status' => 'approved'
		), 'date', 'DESC', array($start, $count));
		
		// Fetch the total number of comments attached to the post
		$db_count = $rs_query->select('comments', 'COUNT(*)', array(
			'post' => $this->post,
			'status' => 'approved'
		));
		
		if(empty($comments)) {
			?>
			<p>No comments to display.</p>
			<?php
		} else {
			?>
			<span class="count hidden" data-comments="<?php echo $start + $count; ?>"></span>
			<?php
			foreach($comments as $comment) {
				$id = $comment['id'];
				$parent = $this->getCommentParent($id);
				?>
				<div id="comment-<?php echo $id; ?>" class="comment">
					<p class="meta">
						<span class="permalink"><a href="<?php echo $this->getCommentPermalink($id); ?>">#<?php echo $id; ?></a></span>&ensp;<span class="author"><?php echo $this->getCommentAuthor($id); ?></span> <span class="date"><?php echo $this->getCommentDate($id); ?></span>
						<?php
						if($parent !== 0) {
							?>
							<span class="replyto">replying to <a href="<?php echo $this->getCommentPermalink($parent); ?>">#<?php echo $parent; ?></a></span>
							<?php
						}
						?>
					</p>
					<div class="content">
						<?php echo nl2br($this->getCommentContent($id)); ?>
					</div>
					<p class="actions">
						<span class="upvote"><span><?php echo $this->getCommentUpvotes($id); ?></span> <a href="#" data-id="<?php echo $id; ?>" data-vote="0" title="Upvote"><i class="fa-solid fa-thumbs-up"></i></a></span>
						&bull; <span class="downvote"><span><?php echo $this->getCommentDownvotes($id); ?></span> <a href="#" data-id="<?php echo $id; ?>" data-vote="0" title="Downvote"><i class="fa-solid fa-thumbs-down"></i></a></span>
						<?php
						// Check whether comments are enabled
						if(getSetting('enable_comments') && $post_types[$rs_post->getPostType()]['comments'] &&
							$rs_post->getPostMeta('comment_status')) {
								
							// Check whether the user is logged in, and if not, check whether anonymous users can comment
							if(!is_null($session) || (is_null($session) && getSetting('allow_anon_comments'))) {
								?>
								&bull; <span class="reply"><a href="#" data-replyto="<?php echo $id; ?>">Reply</a></span>
								<?php
							}
						}
						
						// Check whether the user has permission to edit the comment
						if(!is_null($session) && ($session['id'] === $this->getCommentAuthorId($id) ||
							userHasPrivilege('can_edit_comments'))) {
								
							?>
							&bull; <span class="edit"><a href="#" data-id="<?php echo $id; ?>">Edit</a></span>
							<?php
						}
						
						// Check whether the user has permission to delete the comment
						if(!is_null($session) && ($session['id'] === $this->getCommentAuthorId($id) ||
							userHasPrivilege('can_delete_comments'))) {
								
							?>
							&bull; <span class="delete"><a href="#" data-id="<?php echo $id; ?>">Delete</a></span>
							<?php
						}
						?>
					</p>
				</div>
				<?php
			}
			
			// Check whether the total comment count is greater than 10 and greater than the current number of loaded comments
			if($db_count > 10 && $db_count > $start + $count) {
				?>
				<button type="button" class="load button">Load more</button>
				<?php
			}
		}
	}
	
	/**
	 * Submit a new comment.
	 * @since 1.1.0[b]{ss-04}
	 *
	 * @access public
	 * @param array $data
	 * @return string
	 */
	public function createComment($data): string {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		if(!empty($data['content'])) {
			$status = getSetting('auto_approve_comments') ? 'approved' : 'unapproved';
			
			// Insert a new comment into the database
			$rs_query->insert('comments', array(
				'post' => $data['post'],
				'author' => ($session['id'] ?? 0),
				'date' => 'NOW()',
				'content' => htmlspecialchars($data['content']),
				'status' => $status,
				'parent' => $data['replyto']
			));
			
			// Fetch the number of approved comments attached to the current post
			$count = $rs_query->select('comments', 'COUNT(*)', array(
				'post' => $data['post'],
				'status' => 'approved'
			));
			
			// Update the post's comment count in the database
			$rs_query->update('postmeta', array('value' => $count), array(
				'post' => $data['post'],
				'_key' => 'comment_count'
			));
			
			return '<p style="margin-top: 0;">Your comment was submitted' .
				(!getSetting('auto_approve_comments') ? ' for review' : '') . '!</p>';
		}
	}
	
	/**
	 * Update an existing comment.
	 * @since 1.1.0[b]{ss-05}
	 *
	 * @access public
	 * @param array $data
	 */
	public function updateComment($data): void {
		// Extend the Query object
		global $rs_query;
		
		$rs_query->update('comments', array('content' => $data['content']), array('id' => $data['id']));
	}
	
	/**
	 * Delete a comment.
	 * @since 1.1.0[b]{ss-04}
	 *
	 * @access public
	 * @param int $id
	 */
	public function deleteComment($id): void {
		// Extend the Query object
		global $rs_query;
		
		$post = $rs_query->selectField('comments', 'post', array('id' => $id));
		$rs_query->delete('comments', array('id' => $id));
		
		$count = $rs_query->select('comments', 'COUNT(*)', array('post' => $post, 'status' => 'approved'));
		$rs_query->update('postmeta', array('value' => $count), array('post' => $post, '_key' => 'comment_count'));
	}
	
	/**
	 * Increment the vote count.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @param string $type
	 * @return int
	 */
	public function incrementVotes($id, $type): int {
		// Extend the Query object
		global $rs_query;
		
		$votes = $rs_query->selectField('comments', $type, array('id' => $id));
		$rs_query->update('comments', array($type => ++$votes), array('id' => $id));
		
		return $votes;
	}
	
	/**
	 * Decrement the vote count.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @param string $type
	 * @return int
	 */
	public function decrementVotes($id, $type): int {
		// Extend the Query object
		global $rs_query;
		
		$votes = $rs_query->selectField('comments', $type, array('id' => $id));
		$rs_query->update('comments', array($type => --$votes), array('id' => $id));
		
		return $votes;
	}
}