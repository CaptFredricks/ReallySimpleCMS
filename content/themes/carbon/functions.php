<?php
/**
 * Functions for the default front end theme.
 * @since 2.2.6[a]
 */

/**
 * Register custom post types.
 * @since 1.0.0[b]
 */
// registerPostType($slug, $args);

/**
 * Register custom taxonomies.
 * @since 1.0.1[b]
 */
// registerTaxonomy($name);

/**
 * Register theme menus.
 * @since 1.0.0[b]
 */
registerMenu('Main Menu', 'main-menu');
registerMenu('Footer Menu', 'footer-menu');

/**
 * Register theme widgets.
 * @since 1.0.0[b]
 */
registerWidget('Social Media', 'social-media');
registerWidget('Get in contact with us!', 'business-info');
registerWidget('Copyright', 'copyright');

/**
 * Fetch the most recent posts in a taxonomy.
 * @since 2.2.6[a]
 *
 * @param int $count (optional; default: 3)
 * @param array $terms (optional; default: 0)
 * @param bool $display_title (optional; default: false)
 */
function getRecentPosts($count = 3, $terms = 0, $display_title = false): void {
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
		
		// Check whether a term or terms have been specified
		if($terms === 0) {
			// Fetch the posts from the database
			$posts = $rs_query->select('posts', '*', array(
				'status' => 'published',
				'type' => 'post'
			), 'date', 'DESC', $count);
		} else {
			// Check whether the term value is null
			if(is_null($terms)) {
				// Fetch only the posts associated with the current term
				$posts = getTermPosts($terms, 'date', 'DESC', $count);
			} else {
				// Check whether the terms are in an array and convert them if not
				if(!is_array($terms)) $terms = (array)$terms;
				
				// Create an empty array to hold the posts
				$posts = array();
				
				// Loop through the terms
				foreach($terms as $term) {
					// Fetch only the posts associated with the specified term or terms from the database
					$posts[] = getTermPosts($term, 'date', 'DESC', $count);
				}
				
				// Merge all posts into one array
				$posts = array_merge(...$posts);
			}
		}
		
		// Check whether there are any posts
		if(empty($posts)) {
			?>
			<h4>Sorry, there are no posts to display.</h4>
			<?php
		} else {
			?>
			<ul>
				<?php
				// Loop through the posts
				foreach($posts as $post) {
					// Fetch the post's metadata from the database
					$feat_image = $rs_query->selectField('postmeta', 'value', array(
						'post' => $post['id'],
						'_key' => 'feat_image'
					));
					?>
					<li class="post id-<?php echo $post['id']; ?> clear">
						<?php
						// Check whether the post has a featured image and display it if so
						if($feat_image) echo getMedia($feat_image, array('class' => 'feat-image', 'width' => 80));
						?>
						<h4><a href="<?php echo getPost($post['slug'])->getPostPermalink($post['type'], $post['parent'], $post['slug']); ?>"><?php echo $post['title']; ?></a></h4>
						<p class="date"><?php echo formatDate($post['date'], 'j M Y'); ?></p>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
		}
		?>
	</div>
	<?php
}