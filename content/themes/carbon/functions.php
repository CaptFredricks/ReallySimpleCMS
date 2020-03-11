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
 * @param array $categories (optional; default: 0)
 * @param bool $display_title (optional; default: false)
 * @return null
 */
function getRecentPosts($count = 3, $categories = 0, $display_title = false) {
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
			// Check whether a category or categories have been specified
			if($categories === 0) {
				// Fetch the posts from the database
				$posts = $rs_query->select('posts', '*', array('status'=>'published', 'type'=>'post'), 'date', 'DESC', $count);
			} else {
				// Check whether the category value is null
				if(is_null($categories)) {
					// Fetch only the posts in the current category
					$posts = getPostsInCategory($categories, 'date', 'DESC', $count);
				} else {
					// Check whether the categories are in an array and convert them if not
					if(!is_array($categories)) $categories = (array)$categories;
					
					// Create an empty array to hold the posts
					$posts = array();
					
					// Loop through the categories
					foreach($categories as $category) {
						// Fetch only the posts in the specified category or categories from the database
						$posts[] = getPostsInCategory($category, 'date', 'DESC', $count);
					}
					
					// Merge all posts into one array
					$posts = array_merge(...$posts);
				}
			}
			
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