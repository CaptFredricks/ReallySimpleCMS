<?php
/**
 * Carbon theme - functions.
 * @since 1.0.0 [2.2.6-alpha]
 *
 * @package ReallySimpleCMS
 * @subpackage Carbon
 */

/**
 * Fetch the most recent posts in a taxonomy.
 * @since 2.2.6-alpha
 *
 * @param int $count (optional) -- The post count.
 * @param mixed $terms (optional) -- The terms to query.
 * @param bool $display_title (optional) -- Whether to display the widget title.
 */
function getRecentPosts(int $count = 3, mixed $terms = null, bool $display_title = false): void {
	global $rs_query;
	?>
	<div class="recent-posts clear">
		<?php
		if($display_title) {
			domTagPr('h3', array(
				'content' => 'Recent Posts'
			));
		}
		
		if(is_null($terms)) {
			// Fetch all published posts regardless of taxonomy
			$posts = querySelect(getTable('p'), '*', array(
				'status' => 'published',
				'type' => 'post'
			), array(
				'order_by' => 'created',
				'order' => 'DESC',
				'limit' => $count
			));
		} else {
			if($terms === 0) {
				// Fetch only the posts associated with the current term
				$posts = getTermPosts($terms, 'created', 'DESC', $count);
			} else {
				if(!is_array($terms)) $terms = (array)$terms;
				
				$posts = array();
				
				foreach($terms as $term)
					$posts[] = getTermPosts($term, 'created', 'DESC', $count);
				
				$posts = array_merge(...$posts);
			}
		}
		
		if(empty($posts)) {
			domTagPr('h4', array(
				'content' => 'Sorry, there are no posts to display.'
			));
		} else {
			$recent_posts = array();
			
			foreach($posts as $post) {
				$has_feat_image = false;
				
				$feat_image = querySelectField(getTable('pm'), 'value', array(
					'post' => $post['id'],
					'key' => 'feat_image'
				));
				
				if(!empty($feat_image)) {
					$has_feat_image = true;
					
					$feat_image = getMedia($feat_image, array(
						'class' => 'feat-image',
						'width' => 80
					));
				}
				
				$recent_posts[] = domTag('li', array(
					'class' => 'post id-' . $post['id'] . ' clear',
					'content' => ($has_feat_image === true ? $feat_image : '') . domTag('h4', array(
						'content' => domTag('a', array(
							'href' => getPost($post['slug'])->getPostPermalink(
								$post['type'],
								$post['parent'],
								$post['slug']
							),
							'content' => $post['title']
						))
					)) . domTag('p', array(
						'class' => 'date',
						'content' => formatDate($post['created'], 'j M Y')
					))
				));
			}
			
			domTagPr('ul', array(
				'content' => implode('', $recent_posts)
			));
		}
		?>
	</div>
	<?php
}