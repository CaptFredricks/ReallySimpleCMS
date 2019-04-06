<?php
/**
 * Admin class used to implement the Post object.
 * @since 1.4.0[a]
 *
 * Posts are the basis of the front end of the website. Currently, there are two post types: post (default, used for blog posts) and page (used for content pages).
 * Posts can be created, modified, and deleted.
 */
class Post {
	/**
	 * Construct a list of all posts in the database.
	 * @since 1.4.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listEntries() {
		// Extend the Query class
		global $rs_query;
		
		// Get the post type
		$type = $_GET['type'] ?? 'post';
		
		// Get the post status
		$status = $_GET['status'] ?? 'all';
		
		// Set up pagination
		$page = isset($_GET['page']) ? paginate($_GET['page']) : paginate();
		
		// Get the post count (by type)
		$count = array('all'=>$this->getPostCount($type), 'published'=>$this->getPostCount($type, 'published'), 'draft'=>$this->getPostCount($type, 'draft'), 'trash'=>$this->getPostCount($type, 'trash'));
		?>
		<h1><?php echo ucfirst($type).'s'; ?></h1>
		<a class="button" href="?<?php echo $type === 'post' ? '' : 'type='.$type.'&'; ?>action=create">Create <?php echo ucfirst($type); ?></a>
		<hr>
		<?php
		// Display any status messages
		echo isset($_GET['exit_status']) && $_GET['exit_status'] === 'success' ? statusMessage(ucfirst($type).' was successfully deleted.', true) : '';
		?>
		<ul class="post-status-nav">
			<?php
			// Loop through the post counts (by status)
			foreach($count as $key=>$value) {
				?>
				<li><a href="?type=<?php echo $type.($key === 'all' ? '' : '&status='.$key); ?>"><?php echo ucfirst($key); ?> <span class="count">(<?php echo $value; ?>)</span></a></li>
				<?php
				// Add bullets in between
				if($key !== array_key_last($count)) {
					?> &bull; <?php
				}
			}
			?>
		</ul>
		<?php
		// Set the page count
		$page['count'] = ceil($count[$status] / $page['per_page']);
		?>
		<div class="entry-count">
			<?php
			// Display entry count
			echo $count[$status].' '.($count[$status] === 1 ? 'entry' : 'entries');
			?>
		</div>
		<table class="data-table">
			<thead>
				<?php
				// Construct the table header
				echo tableHeaderRow(array('Title', 'Author', 'Publish Date', 'Status'));
				?>
			</thead>
			<tbody>
				<?php
				// Fetch posts from the database
				if($status === 'all')
					$posts = $rs_query->select('posts', '*', array('status'=>array('<>', 'trash'), 'type'=>$type), 'title', 'ASC', array($page['start'], $page['per_page']));
				else
					$posts = $rs_query->select('posts', '*', array('status'=>$status, 'type'=>$type), 'title', 'ASC', array($page['start'], $page['per_page']));
				
				// Loop through the posts
				foreach($posts as $post) {
					// Construct the current row
					echo tableRow(
						tableCell('<strong>'.$post['title'].'</strong><div class="actions">'.($status !== 'trash' ? '<a href="?id='.$post['id'].'&action=edit">Edit</a> &bull; <a href="?id='.$post['id'].'&action=trash">Trash</a> &bull; <a href="'.($post['status'] === 'published' ? ($this->isHomePage($post['id']) ? '/' : '' /* $this->getPermalink($post['parent'], $post['slug']) */).'">View' : ('/?id='.$post['id'].'&preview=true').'">Preview').'</a>' : '<a href="?id='.$post['id'].'&action=restore">Restore</a> &bull; <a href="" rel="">Delete</a>').'</div>', 'title'),
						tableCell($this->getAuthor($post['author']), 'author'),
						tableCell(formatDate($post['date'], 'd M Y @ g:i A'), 'publish-date'),
						tableCell(ucfirst($post['status']), 'status')
					);

// '<div class="actions">'.($status !== 'trash' ? '<a href="?id='.$post['id'].'&action=edit">Edit</a> &bull; <a href="?id='.$post['id'].'&action=trash">Trash</a> &bull; <a href="'.($post['status'] === 'published' ? ($post['slug'] !== 'home' ? $this->getPermalink($post['parent_id'], $post['slug']) : '/').'">View' : ('/?id='.$post['id'].'&preview=true').'">Preview').'</a>' : '<a href="?id='.$post['id'].'&action=restore">Restore</a> &bull; <a class="delete-item" href="javascript:void(0)" rel="'.($type === 'post' ? $post['id'] : $type.'-'.$post['id']).'">Delete</a>').'</div>';
//$content .= '<td class="parent">'.$this->getParent($post['parent_id']).'</td>';

				}
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
	}
	
	/**
	 * Check whether a post is the current home page.
	 * @since 1.4.0[a]
	 *
	 * @access private
	 * @param int $id
	 * @return bool
	 */
	private function isHomePage($id) {
		// Extend the Query class
		global $rs_query;
		
		// Return true if the post is the home page and false if not
		return $rs_query->selectRow('settings', 'COUNT(*)', array('name'=>'home_page', 'value'=>$id));
	}
	
	/**
	 * Fetch a post's author.
	 * @since 1.4.0[a]
	 *
	 * @access protected
	 * @param int $id
	 * @return string
	 */
	protected function getAuthor($id) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the author from the database
		$author = $rs_query->selectRow('users', 'username', array('id'=>$id));
		
		// Return the author's username
		return $author['username'];
	}
	
	/**
	 * Fetch the post count based on a specific status.
	 * @since 1.4.0[a]
	 *
	 * @access private
	 * @param string $type
	 * @param string $status (optional; default: '')
	 * @return int
	 */
	private function getPostCount($type, $status = '') {
		// Extend the Query class
		global $rs_query;
		
		if(empty($status)) {
			// Return the count of all posts (excluding ones that are in the trash)
			return $rs_query->select('posts', 'COUNT(*)', array('status'=>array('<>', 'trash'), 'type'=>$type));
		} else {
			// Return the count of all posts by the status
			return $rs_query->select('posts', 'COUNT(*)', array('status'=>$status, 'type'=>$type));
		}
	}
}