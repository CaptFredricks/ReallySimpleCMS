<?php
/**
 * Core class used to implement the Comment object.
 * This class loads data from the comments table of the database for use on the front end of the CMS.
 * @since 1.1.0-beta_snap-03
 *
 * @package ReallySimpleCMS
 * @subpackage Engine
 */
namespace Engine;

class Comment {
	/**
	 * The post the current comment belongs to.
	 * @since 1.1.0-beta_snap-03
	 *
	 * @access private
	 * @var int
	 */
	private $post;
	
	/**
	 * Class constructor.
	 * @since 1.1.0-beta_snap-03
	 *
	 * @access public
	 * @param int $post (optional) -- The post the comment belongs to.
	 */
	public function __construct(int $post = 0) {
		$this->post = $post;
	}
	
	/**
	 * Fetch a comment's author.
	 * @since 1.1.0-beta_snap-03
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 * @return string
	 */
	public function getCommentAuthor(int $id): string {
		global $rs_query;
		
		$author_id = $this->getCommentAuthorId($id);
		
		if($author_id === 0) {
			$author = 'Anonymous';
		} else {
			$author = $rs_query->selectField('usermeta', 'value', array(
				'user' => $author_id,
				'datakey' => 'display_name'
			));
		}
		
		return $author;
	}
	
	/**
	 * Fetch a comment's author id.
	 * @since 1.1.0-beta_snap-04
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 * @return int
	 */
	public function getCommentAuthorId(int $id): int {
		global $rs_query;
		
		return (int)$rs_query->selectField('comments', 'author', array('id' => $id));
	}
	
	/**
	 * Fetch a comment's submission date.
	 * @since 1.1.0-beta_snap-03
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 * @return string
	 */
	public function getCommentDate(int $id): string {
		global $rs_query;
		
		$date = $rs_query->selectField('comments', 'date', array('id' => $id));
		
		return formatDate($date, 'j M Y @ g:i A');
	}
	
	/**
	 * Fetch a comment's content.
	 * @since 1.1.0-beta_snap-03
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 * @return string
	 */
	public function getCommentContent(int $id): string {
		global $rs_query;
		
		return $rs_query->selectField('comments', 'content', array('id' => $id));
	}
	
	/**
	 * Fetch a comment's number of upvotes.
	 * @since 1.1.0-beta_snap-03
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 * @return int
	 */
	public function getCommentUpvotes(int $id): int {
		global $rs_query;
		
		return (int)$rs_query->selectField('comments', 'upvotes', array('id' => $id));
	}
	
	/**
	 * Fetch a comment's number of downvotes.
	 * @since 1.1.0-beta_snap-03
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 * @return int
	 */
	public function getCommentDownvotes(int $id): int {
		global $rs_query;
		
		return (int)$rs_query->selectField('comments', 'downvotes', array('id' => $id));
	}
	
	/**
	 * Fetch a comment's status.
	 * @since 1.1.0-beta_snap-03
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 * @return string
	 */
	public function getCommentStatus(int $id): string {
		global $rs_query;
		
		return $rs_query->selectField('comments', 'status', array('id' => $id));
	}
	
	/**
	 * Fetch a comment's parent.
	 * @since 1.1.0-beta_snap-04
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 * @return int
	 */
	public function getCommentParent(int $id): int {
		global $rs_query;
		
		return (int)$rs_query->selectField('comments', 'parent', array('id' => $id));
	}
	
	/**
	 * Fetch a comment's permalink.
	 * @since 1.1.0-beta_snap-03
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 * @return string
	 */
	public function getCommentPermalink(int $id): string {
		global $rs_query;
		
		$post = $rs_query->selectRow('posts', array('slug', 'parent', 'type'), array(
			'id' => $this->post
		));
		
		return getPermalink($post['type'], $post['parent'], $post['slug']) . '#comment-' . $id;
	}
	
	/**
	 * Fetch the number of comments assigned to the current post.
	 * @since 1.1.0-beta_snap-04
	 *
	 * @access public
	 * @param int $post -- The post's id.
	 * @return int
	 */
	public function getCommentCount(int $post): int {
		global $rs_query;
		
		return $rs_query->select('comments', 'COUNT(*)', array('post' => $post));
	}
	
	/**
	 * Construct the reply box for a comment feed.
	 * @since 1.1.0-beta_snap-04
	 *
	 * @access public
	 */
	public function getCommentReplyBox(): void {
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
	 * Construct a comment feed.
	 * @since 1.1.0-beta_snap-03
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
	 * @since 1.2.2-beta
	 *
	 * @access public
	 * @param int $offset (optional) -- The offset (starting point).
	 * @param int $count (optional) -- The number of comments to load.
	 */
	public function loadComments(int $offset = 0, int $count = 10): void {
		global $rs_query, $rs_post, $session, $post_types;
		
		$per_page = 10;
		
		$comments = $rs_query->select('comments', 'id', array(
			'post' => $this->post,
			'status' => 'approved'
		), 'date', 'DESC', array($offset, $count));
		
		$approved = $rs_query->select('comments', 'COUNT(*)', array(
			'post' => $this->post,
			'status' => 'approved'
		));
		
		if(empty($comments)) {
			?>
			<p>No comments to display.</p>
			<?php
		} else {
			?>
			<span class="count hidden" data-comments="<?php echo $offset + $count; ?>"></span>
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
			
			if($approved > $per_page && $approved > $offset + $count) {
				?>
				<button type="button" class="load button">Load more</button>
				<?php
			}
		}
	}
	
	/**
	 * Create a new comment.
	 * @since 1.1.0-beta_snap-04
	 *
	 * @access public
	 * @param array $data -- The comment data.
	 * @return string
	 */
	public function createComment(array $data): string {
		global $rs_query, $session;
		
		if(!empty($data['content'])) {
			$status = getSetting('auto_approve_comments') ? 'approved' : 'pending';
			
			$rs_query->insert('comments', array(
				'post' => $data['post'],
				'author' => ($session['id'] ?? 0),
				'date' => 'NOW()',
				'content' => htmlspecialchars($data['content']),
				'status' => $status,
				'parent' => $data['replyto']
			));
			
			$approved = $rs_query->select('comments', 'COUNT(*)', array(
				'post' => $data['post'],
				'status' => 'approved'
			));
			
			$rs_query->update('postmeta', array('value' => $approved), array(
				'post' => $data['post'],
				'datakey' => 'comment_count'
			));
			
			return '<p style="margin-top: 0;">Your comment was submitted' .
				(!getSetting('auto_approve_comments') ? ' for review' : '') . '!</p>';
		}
	}
	
	/**
	 * Update an existing comment.
	 * @since 1.1.0-beta_snap-05
	 *
	 * @access public
	 * @param array $data -- The comment data.
	 */
	public function updateComment(array $data): void {
		global $rs_query;
		
		$rs_query->update('comments', array(
			'content' => $data['content']
		), array(
			'id' => $data['id']
		));
	}
	
	/**
	 * Delete a comment.
	 * @since 1.1.0-beta_snap-04
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 */
	public function deleteComment(int $id): void {
		global $rs_query;
		
		$post = $rs_query->selectField('comments', 'post', array('id' => $id));
		$rs_query->delete('comments', array('id' => $id));
		
		$count = $rs_query->select('comments', 'COUNT(*)', array(
			'post' => $post,
			'status' => 'approved'
		));
		
		$rs_query->update('postmeta', array('value' => $count), array(
			'post' => $post,
			'datakey' => 'comment_count'
		));
	}
	
	/**
	 * Increment (increase) the vote count.
	 * @since 1.1.0-beta_snap-03
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 * @param string $type -- The vote type (upvotes).
	 * @return int
	 */
	public function incrementVotes(int $id, string $type): int {
		global $rs_query;
		
		$votes = $rs_query->selectField('comments', $type, array('id' => $id));
		$rs_query->update('comments', array($type => ++$votes), array('id' => $id));
		
		return $votes;
	}
	
	/**
	 * Decrement (decrease) the vote count.
	 * @since 1.1.0-beta_snap-03
	 *
	 * @access public
	 * @param int $id -- The comment's id.
	 * @param string $type -- The vote type (downvotes).
	 * @return int
	 */
	public function decrementVotes(int $id, string $type): int {
		global $rs_query;
		
		$votes = $rs_query->selectField('comments', $type, array('id' => $id));
		$rs_query->update('comments', array($type => --$votes), array('id' => $id));
		
		return $votes;
	}
}