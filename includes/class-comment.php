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
	 * @return null
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
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getCommentAuthor($id, $echo = true) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the comment's author from the database
		$author = (int)$rs_query->selectField('comments', 'author', array('id' => $id));
		
		// Check whether the author's id is zero
		if($author === 0) {
			// Set the username to 'anonymous'
			$username = 'Anonymous';
		} else {
			// Fetch the author's username from the database
			$username = $rs_query->selectField('users', 'username', array('id' => $author));
		}
		
		if($echo)
			echo $username;
		else
			return $username;
	}
	
	/**
	 * Fetch a comment's author id.
	 * @since 1.1.0[b]{ss-04}
	 *
	 * @access public
	 * @param int $id
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getCommentAuthorId($id, $echo = true) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the comment's author from the database
		$author = (int)$rs_query->selectField('comments', 'author', array('id' => $id));
		
		if($echo)
			echo $author;
		else
			return $author;
	}
	
	/**
	 * Fetch a comment's date.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getCommentDate($id, $echo = true) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the comment's date from the database
        $date = $rs_query->selectField('comments', 'date', array('id' => $id));
		
        if($echo)
            echo formatDate($date, 'j M Y @ g:i A');
        else
            return formatDate($date, 'j M Y @ g:i A');
	}
	
	/**
	 * Fetch a comment's content.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getCommentContent($id, $echo = true) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the comment's content from the database
		$content = $rs_query->selectField('comments', 'content', array('id' => $id));
		
		if($echo)
			echo $content;
		else
			return $content;
	}
	
	/**
	 * Fetch a comment's upvotes.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getCommentUpvotes($id, $echo = true) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the comment's upvotes from the database
		$upvotes = $rs_query->selectField('comments', 'upvotes', array('id' => $id));
		
		if($echo)
			echo $upvotes;
		else
			return $upvotes;
	}
	
	/**
	 * Fetch a comment's downvotes.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getCommentDownvotes($id, $echo = true) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the comment's downvotes from the database
		$downvotes = $rs_query->selectField('comments', 'downvotes', array('id' => $id));
		
		if($echo)
			echo $downvotes;
		else
			return $downvotes;
	}
	
	/**
	 * Fetch a comment's status.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getCommentStatus($echo = true) {
		// Extend the Query object
		global $rs_query;
	}
	
	/**
	 * Fetch a comment's parent.
	 * @since 1.1.0[b]{ss-04}
	 *
	 * @access public
	 * @param int $id
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getCommentParent($id, $echo = true) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the comment's parent from the database
		$parent = (int)$rs_query->selectField('comments', 'parent', array('id' => $id));
		
		if($echo)
			echo $parent;
		else
			return $parent;
	}
	
	/**
	 * Fetch a comment's permalink.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param int $id
	 * @return string
	 */
	public function getCommentPermalink($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post from the database
		$post = $rs_query->selectRow('posts', array('slug', 'parent', 'type'), array('id' => $this->post));
		
		// Return the permalink
		return getPermalink($post['type'], $post['parent'], $post['slug']).'#comment-'.$id;
	}
	
	/**
	 * Fetch the number of comments assigned to the current post.
	 * @since 1.1.0[b]{ss-04}
	 *
	 * @access public
	 * @param int $post
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getCommentCount($post, $echo = true) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the comment count from the database
		$count = $rs_query->select('comments', 'COUNT(*)', array('post' => $post));
		
		if($echo)
			echo $count;
		else
			return $count;
	}
	
	/**
	 * Construct a comment reply box.
	 * @since 1.1.0[b]{ss-04}
	 *
	 * @access public
	 * @return null
	 */
	public function getCommentReplyBox() {
		// Extend the Post object, the user's session data, and the post types array
		global $rs_post, $session, $post_types;
		
		// Check whether comments are enabled
		if(getSetting('enable_comments', false) && $post_types[$rs_post->getPostType()]['comments'] && $rs_post->getPostMeta('comment_status')) {
			// Check whether the user is logged in, and if not, check whether anonymous users can comment
			if(!is_null($session) || (is_null($session) && getSetting('allow_anon_comments', false))) {
				?>
				<div id="comments-reply" class="textarea-wrap">
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
	 * @return null
	 */
	public function getCommentFeed() {
		?>
		<div class="comments-wrap">
			<?php
			// Load the comments
			$this->loadComments();
			?>
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
	 * @return array
	 */
	public function loadComments($start = 0, $count = 10) {
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
		
		// Check whether there are any comments
		if(empty($comments)) {
			?>
			<p>No comments to display.</p>
			<?php
		} else {
			?>
			<span class="count hidden" data-comments="<?php echo $start + $count; ?>"></span>
			<?php
			// Loop through the comments
			foreach($comments as $comment) {
				// Fetch the comment's id
				$id = $comment['id'];
				
				// Fetch the comment's parent
				$parent = $this->getCommentParent($id, false);
				?>
				<div id="comment-<?php echo $id; ?>" class="comment">
					<p class="meta">
						<span class="permalink"><a href="<?php echo $this->getCommentPermalink($id); ?>">#<?php echo $id; ?></a></span>&ensp;<span class="author"><?php $this->getCommentAuthor($id); ?></span> <span class="date"><?php $this->getCommentDate($id); ?></span>
						<?php
						// Check whether the comment has a parent
						if($parent !== 0) {
							?>
							<span class="replyto">replying to <a href="<?php echo $this->getCommentPermalink($parent); ?>">#<?php echo $parent; ?></a></span>
							<?php
						}
						?>
					</p>
					<div class="content">
						<?php nl2br($this->getCommentContent($id)); ?>
					</div>
					<p class="actions">
						<span class="upvote"><span><?php $this->getCommentUpvotes($id); ?></span> <a href="#" data-id="<?php echo $id; ?>" data-vote="0" title="Upvote"><i class="fas fa-thumbs-up"></i></a></span>
						&bull; <span class="downvote"><span><?php $this->getCommentDownvotes($id); ?></span> <a href="#" data-id="<?php echo $id; ?>" data-vote="0" title="Downvote"><i class="fas fa-thumbs-down"></i></a></span>
						<?php
						// Check whether comments are enabled
						if(getSetting('enable_comments', false) && $post_types[$rs_post->getPostType()]['comments'] && $rs_post->getPostMeta('comment_status')) {
							// Check whether the user is logged in, and if not, check whether anonymous users can comment
							if(!is_null($session) || (is_null($session) && getSetting('allow_anon_comments', false))) {
								?>
								&bull; <span class="reply"><a href="#" data-replyto="<?php echo $id; ?>">Reply</a></span>
								<?php
							}
						}
						
						// Check whether the user has permission to edit the comment
						if(!is_null($session) && ($session['id'] === $this->getCommentAuthorId($id, false) || userHasPrivilege($session['role'], 'can_edit_comments'))) {
							?>
							&bull; <span class="edit"><a href="#" data-id="<?php echo $id; ?>">Edit</a></span>
							<?php
						}
						
						// Check whether the user has permission to delete the comment
						if(!is_null($session) && ($session['id'] === $this->getCommentAuthorId($id, false) || userHasPrivilege($session['role'], 'can_delete_comments'))) {
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
	 * @return string|null
	 */
	public function createComment($data) {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Check whether the comment's content is empty
		if(!empty($data['content'])) {
			// Set the comment status based on whether comments are set to auto approve
			$status = getSetting('auto_approve_comments', false) ? 'approved' : 'unapproved';
			
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
			
			// Return a status message
			return '<p style="margin-top: 0;">Your comment was submitted'.(!getSetting('auto_approve_comments', false) ? ' for review' : '').'!</p>';
		}
	}
	
	/**
	 * Update an existing comment.
	 * @since 1.1.0[b]{ss-05}
	 *
	 * @access public
	 * @param array $data
	 * @return null
	 */
	public function updateComment($data) {
		// Extend the Query object
		global $rs_query;
		
		// Update the comment in the database
		$rs_query->update('comments', array('content' => $data['content']), array('id' => $data['id']));
	}
	
	/**
	 * Delete a comment.
	 * @since 1.1.0[b]{ss-04}
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function deleteComment($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post the comment is attached to
		$post = $rs_query->selectField('comments', 'post', array('id' => $id));
		
		// Delete the comment from the database
		$rs_query->delete('comments', array('id' => $id));
		
		// Fetch the number of approved comments attached to the current post
		$count = $rs_query->select('comments', 'COUNT(*)', array('post' => $post, 'status' => 'approved'));
		
		// Update the post's comment count in the database
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
	public function incrementVotes($id, $type) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the current vote count from the database
		$votes = $rs_query->selectField('comments', $type, array('id' => $id));
		
		// Update the vote count in the database
		$rs_query->update('comments', array($type => ++$votes), array('id' => $id));
		
		// Return the new vote count
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
	public function decrementVotes($id, $type) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the current vote count from the database
		$votes = $rs_query->selectField('comments', $type, array('id' => $id));
		
		// Update the vote count in the database
		$rs_query->update('comments', array($type => --$votes), array('id' => $id));
		
		// Return the new vote count
		return $votes;
	}
}