<?php
/**
 * Functions for the default front end theme.
 * @since 2.2.6[a]
 */

/**
 * Fetch the most recent blog posts.
 * @since 2.2.6[a]
 *
 * @param int $count (optional; default: 3)
 * @return null
 */
function getRecentPosts($count = 3) {
	// Extend the Query object
	global $rs_query;
	?>
	<div class="recent-posts">
		<h3>Recent Posts</h3>
		<ul>
			<?php
			// Fetch the posts from the database
			$posts = $rs_query->select('posts', '*', array('status'=>'published', 'type'=>'post'), 'date', 'DESC');
			
			// Create a counter variable
			$i = 0;
			
			// Loop through the posts
			foreach($posts as $post) {
				// Check whether the post count has been reached and break out of the loop if so
				if($i >= (int)$count) break;
				
				// Fetch the post's metadata from the database
				$feat_image = $rs_query->selectField('postmeta', 'value', array('post'=>$post['id'], '_key'=>'feat_image'));
				?>
				<li class="post id-<?php echo $post['id']; ?> clear">
					<?php
					// Check whether the post has a featured image and display it if so
					if($feat_image) echo getMedia($feat_image, array('class'=>'feat-image', 'width'=>80));
					?>
					<h4><?php echo $post['title']; ?></h4>
					<p class="date"><?php echo formatDate($post['date'], 'M j, Y'); ?></p>
				</li>
				<?php
				// Increment the counter
				$i++;
			}
			?>
		</ul>
	</div>
	<?php
}