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
function getRecentPosts($count = 3, $display_title = false) {
	// Extend the Query object
	global $rs_query;
	?>
	<div class="recent-posts clear">
		<?php
		// Check whether the title should be displayed
		if($display_title) {
			?>
			<h3>Recent Posts</h3>
			<?php
		}
		?>
		<ul>
			<?php
			// Fetch the posts from the database
			$posts = $rs_query->select('posts', '*', array('status'=>'published', 'type'=>'post'), 'date', 'DESC', $count);
			
			// Loop through the posts
			foreach($posts as $post) {
				// Fetch the post's metadata from the database
				$feat_image = $rs_query->selectField('postmeta', 'value', array('post'=>$post['id'], '_key'=>'feat_image'));
				?>
				<li class="post id-<?php echo $post['id']; ?> clear">
					<?php
					// Check whether the post has a featured image and display it if so
					if($feat_image) echo getMedia($feat_image, array('class'=>'feat-image', 'width'=>80));
					?>
					<h4><a href="<?php echo getPost($post['slug'])->getPostPermalink($post['parent'], $post['slug']); ?>"><?php echo $post['title']; ?></a></h4>
					<p class="date"><?php echo formatDate($post['date'], 'M j, Y'); ?></p>
				</li>
				<?php
			}
			?>
		</ul>
	</div>
	<?php
}