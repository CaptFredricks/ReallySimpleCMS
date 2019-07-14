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
		<div class="heading-wrap">
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
		</div>
		<table class="data-table">
			<thead>
				<?php
				// Construct the table header
				echo tableHeaderRow(array('Title', 'Author', 'Publish Date', 'Status', 'Meta Title', 'Meta Desc.'));
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
					// Fetch postmeta from the database
					$postmeta = $rs_query->select('postmeta', '*', array('post'=>$post['id']));
					
					// Assign metadata to its own array
					foreach($postmeta as $metadata)
						$meta[$metadata['_key']] = $metadata['value'];
					
					// Construct the current row
					echo tableRow(
						tableCell('<strong>'.$post['title'].'</strong><div class="actions">'.($status !== 'trash' ? '<a href="?id='.$post['id'].'&action=edit">Edit</a> &bull; <a href="?id='.$post['id'].'&action=trash">Trash</a> &bull; <a href="'.($post['status'] === 'published' ? ($this->isHomePage($post['id']) ? '/' : '' /* $this->getPermalink($post['parent'], $post['slug']) */).'">View' : ('/?id='.$post['id'].'&preview=true').'">Preview').'</a>' : '<a href="?id='.$post['id'].'&action=restore">Restore</a> &bull; <a href="" rel="">Delete</a>').'</div>', 'title'),
						tableCell($this->getAuthor($post['author']), 'author'),
						tableCell(formatDate($post['date'], 'd M Y @ g:i A'), 'publish-date'),
						tableCell(ucfirst($post['status']), 'status'),
						tableCell(!empty($meta['title']) ? 'Yes' : 'No', 'meta_title'),
						tableCell(!empty($meta['description']) ? 'Yes' : 'No', 'meta_description')
					);

// '<div class="actions">'.($status !== 'trash' ? '<a href="?id='.$post['id'].'&action=edit">Edit</a> &bull; <a href="?id='.$post['id'].'&action=trash">Trash</a> &bull; <a href="'.($post['status'] === 'published' ? ($post['slug'] !== 'home' ? $this->getPermalink($post['parent_id'], $post['slug']) : '/').'">View' : ('/?id='.$post['id'].'&preview=true').'">Preview').'</a>' : '<a href="?id='.$post['id'].'&action=restore">Restore</a> &bull; <a class="delete-item" href="javascript:void(0)" rel="'.($type === 'post' ? $post['id'] : $type.'-'.$post['id']).'">Delete</a>').'</div>';
//$content .= '<td class="parent">'.$this->getParent($post['parent_id']).'</td>';

				}
				
				if(count($posts) === 0) {
					// Display notice if no posts are found
					echo tableRow(tableCell('There are no '.$type.'s to display.', '', 6));
				}
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
	}
	
	/**
	 * Construct the 'Create Post' form.
	 * @since 1.4.1[a]
	 *
	 * @access public
	 * @return null
	 */
	public function createEntry() {
		// Get the post type
		$type = $_GET['type'] ?? 'post';
		
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1>Create <?php echo ucwords($type); ?></h1>
			<?php
			// Display status messages
			echo $message;
			?>
		</div>
		<form action="" method="post" autocomplete="off">
			<div class="form-container">
				<div class="content">
					<?php
					// Construct hidden 'type' form tag
					echo formTag('input', array('type'=>'hidden', 'name'=>'type', 'value'=>$type));
					
					// Construct 'title' form tag
					echo formTag('input', array('type'=>'text', 'id'=>'title-field', 'class'=>'text-input required invalid init', 'name'=>'title', 'value'=>$_POST['title'] ?? '', 'placeholder'=>ucfirst($type).' title'));
					?>
					<div class="permalink">
						<?php
						// Construct 'permalink' form tag
						echo formTag('label', array('for'=>'slug', 'content'=>'<strong>Permalink:</strong> '.getSetting('site_url', false).'/'));
						echo formTag('input', array('type'=>'text', 'id'=>'slug-field', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>$_POST['slug'] ?? ''));
						echo '/';
						?>
					</div>
					<?php
					// Construct 'insert image' button form tag
					echo formTag('input', array('type'=>'button', 'class'=>'button-input button', 'value'=>'Insert Image'));
					
					// Construct 'content' form tag
					echo formTag('textarea', array('class'=>'textarea-input', 'name'=>'content', 'cols'=>30, 'rows'=>20, 'content'=>isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''));
					?>
				</div>
				<div class="sidebar">
					<div class="block">
						<h2>Publish</h2>
						<div class="row">
							<?php
							// Construct 'status' form tag
							echo formTag('label', array('for'=>'status', 'content'=>'Status'));
							echo formTag('select', array('name'=>'status', 'content'=>'<option value="draft">Draft</option><option value="published">Published</option>'));
							?>
						</div>
						<div class="row">
							<?php
							// Construct 'author' form tag
							echo formTag('label', array('for'=>'author', 'content'=>'Author'));
							echo formTag('select', array('name'=>'author', 'content'=>$this->getAuthorList()));
							?>
						</div>
						<div id="submit" class="row">
							<?php
							// Construct 'submit' button form tag
							echo formTag('input', array('type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Publish'));
							?>
						</div>
					</div>
					<div class="block">
						<h2>Attributes</h2>
						<div class="row">
							<?php
							// Construct 'parent' form tag
							echo formTag('label', array('for'=>'parent', 'content'=>'Parent'));
							echo formTag('select', array('name'=>'parent', 'content'=>'<option value="0">(none)</option>'.$this->getParentList($type)));
							?>
						</div>
					</div>
					<div class="block">
						<h2>Featured Image</h2>
						<div class="row">
							<?php
							// Display the featured image if it's been selected
							isset($_POST['featured']) && strlen($_POST['featured']) > 0 ? '<img src=""><span></span>' : '';
							
							// Construct hidden 'featured' form tag
							echo formTag('input', array('type'=>'hidden', 'name'=>'featured'));
							?>
							<a href="#">Choose Image</a>
						</div>
					</div>
				</div>
				<div class="metadata">
					<div class="block">
						<h2>Metadata</h2>
						<div class="row">
							<?php
							// Construct 'meta title' form tag
							echo formTag('label', array('for'=>'meta_title', 'content'=>'Title'));
							echo formTag('br');
							echo formTag('input', array('type'=>'text', 'class'=>'text-input', 'name'=>'meta_title', 'value'=>$_POST['meta_title'] ?? ''));
							?>
						</div>
						<div class="row">
							<?php
							// Construct 'meta description' form tag
							echo formTag('label', array('for'=>'meta_description', 'content'=>'Description'));
							echo formTag('br');
							echo formTag('textarea', array('class'=>'textarea-input', 'name'=>'meta_description', 'cols'=>30, 'rows'=>3, 'content'=>$_POST['meta_description'] ?? ''));
							?>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php
	}
	
	/**
	 * Send a post to the trash.
	 * @since 1.4.6[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function trashEntry($id) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the post from the database
		$post = $rs_query->selectRow('posts', 'type', array('id'=>$id));
		
		// Set the post's status to 'trash'
		$rs_query->update('posts', array('status'=>'trash'), array('id'=>$id));
		
		// Redirect to the 'All Posts' page
		header('Location: posts.php'.($post['type'] !== 'post' ? '?type='.$post['type'] : ''));
	}
	
	/**
	 * Restore a post from the trash.
	 * @since 1.4.6[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function restoreEntry($id) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the post from the database
		$post = $rs_query->selectRow('posts', 'type', array('id'=>$id));
		
		// Set the post's status to 'draft'
		$rs_query->update('posts', array('status'=>'draft'), array('id'=>$id));
		
		// Redirect to the 'All Posts' trash page
		header('Location: posts.php'.($post['type'] !== 'post' ? '?type='.$post['type'].'&' : '?').'status=trash');
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
	 * Construct a list of authors.
	 * @since 1.4.4[a]
	 *
	 * @access protected
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	protected function getAuthorList($id = 0) {
		// Extend the Query class
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch all authors from the database
		$authors = $rs_query->select('users', 'id', '', 'username');
		
		// Loop through the authors
		foreach($authors as $author) {
			// Construct the list
			$list .= '<option value="'.$author['id'].'"'.($author['id'] === $id ? ' selected' : '').'>'.$this->getAuthor($author['id']).'</option>';
		}
		
		// Return the list
		return $list;
	}
	
	/**
	 * Fetch a post's parent.
	 * @since 1.4.4[a]
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getParent($id) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the parent from the database
		$parent = $rs_query->selectRow('posts', 'title', array('id'=>$id));
		
		// Return the parent's title
		return $parent['title'];
	}
	
	/**
	 * Construct a list of parents.
	 * @since 1.4.4[a]
	 *
	 * @access private
	 * @param string $type
	 * @param int $parent (optional; default: 0)
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function getParentList($type, $parent = 0, $id = 0) {
		// Extend the Query class
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch all posts from the database (by type)
		$posts = $rs_query->select('posts', 'id', array('type'=>$type));
		
		// Loop through the posts
		foreach($posts as $post) {
			// Do some extra checks if an id is provided
			if($id !== 0) {
				// Skip the current post
				if($post['id'] === $id) continue;
				
				// Skip all ancestor posts
				if($this->isDescendant($post['id'], $id)) continue;
			}
			
			// Construct the list
			$list .= '<option value="'.$post['id'].'"'.($post['id'] === $parent ? ' selected' : '').'>'.$this->getParent($post['id']).'</option>';
		}
		
		// Return the list
		return $list;
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