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
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getCommentAuthor($id, $echo = true) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the comment's author from the database
		$author = (int)$rs_query->selectField('comments', 'author', array('id'=>$id));
		
		// Check whether the author's id is zero
		if($author === 0) {
			// Set the username to 'anonymous'
			$username = 'Anonymous';
		} else {
			// Fetch the author's username from the database
			$username = $rs_query->selectField('users', 'username', array('id'=>$author));
		}
		
		if($echo)
			echo $username;
		else
			return $username;
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
        $date = $rs_query->selectField('comments', 'date', array('id'=>$id));
		
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
		$content = $rs_query->selectField('comments', 'content', array('id'=>$id));
		
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
		$upvotes = $rs_query->selectField('comments', 'upvotes', array('id'=>$id));
		
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
		$downvotes = $rs_query->selectField('comments', 'downvotes', array('id'=>$id));
		
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
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getCommentParent($echo = true) {
		// Extend the Query object
		global $rs_query;
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
		$post = $rs_query->selectRow('posts', array('slug', 'parent', 'type'), array('id'=>$this->post));
		
		// Return the permalink
		return getPermalink($post['type'], $post['parent'], $post['slug']).'#comment-'.$id;
	}
	
	/**
	 * Construct a thread of comments.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @return null
	 */
	public function getCommentThread() {
		// Extend the Query object
		global $rs_query;
		
		// Fetch all comments attached to the post from the database
		$comments = $rs_query->select('comments', 'id', array('post'=>$this->post, 'status'=>'approved'));
		
		// Check whether there are any comments
		if(empty($comments)) {
			?>
			<p>No comments to display.</p>
			<?php
		} else {
			// Loop through the comments
			foreach($comments as $comment) {
				// Fetch the comment's id
				$id = $comment['id'];
				?>
				<div id="comment-<?php echo $id; ?>" class="comment">
					<p class="meta">
						<span class="permalink"><a href="<?php echo $this->getCommentPermalink($id); ?>">#<?php echo $id; ?></a></span>&ensp;<span class="author"><?php $this->getCommentAuthor($id); ?></span> <span class="date"><?php $this->getCommentDate($id); ?></span>
					</p>
					<div class="content">
						<?php nl2br($this->getCommentContent($id)); ?>
					</div>
					<p class="actions">
						<span class="upvote"><span><?php $this->getCommentUpvotes($id); ?></span> <a href="#" data-id="<?php echo $id; ?>" data-vote="0" title="Upvote"><i class="fas fa-thumbs-up"></i></a></span> &bull; <span class="downvote"><span><?php $this->getCommentDownvotes($id); ?></span> <a href="#" data-id="<?php echo $id; ?>" data-vote="0" title="Downvote"><i class="fas fa-thumbs-down"></i></a></span> &bull; <span class="reply"><a href="#" data-replyto="<?php echo $id; ?>">Reply</a></span>
					</p>
				</div>
				<?php
			}
		}
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
		$votes = $rs_query->selectField('comments', $type, array('id'=>$id));
		
		// Update the vote count in the database
		$rs_query->update('comments', array($type=>++$votes), array('id'=>$id));
		
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
		$votes = $rs_query->selectField('comments', $type, array('id'=>$id));
		
		// Update the vote count in the database
		$rs_query->update('comments', array($type=>--$votes), array('id'=>$id));
		
		// Return the new vote count
		return $votes;
	}
}