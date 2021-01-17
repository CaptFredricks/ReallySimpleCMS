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
	 * The currently queried post's id.
	 * @since 1.0.1[b]
	 *
	 * @access protected
	 * @var int
	 */
	protected $id;
	
	/**
	 * The currently queried post's title.
	 * @since 1.0.1[b]
	 *
	 * @access protected
	 * @var string
	 */
	protected $title;
	
	/**
	 * The currently queried post's author.
	 * @since 1.0.1[b]
	 *
	 * @access protected
	 * @var int
	 */
	protected $author;
	
	/**
	 * The currently queried post's date.
	 * @since 1.0.1[b]
	 *
	 * @access protected
	 * @var string
	 */
	protected $date;
	
	/**
	 * The currently queried post's content.
	 * @since 1.0.1[b]
	 *
	 * @access protected
	 * @var string
	 */
	protected $content;
	
	/**
	 * The currently queried post's status.
	 * @since 1.0.1[b]
	 *
	 * @access protected
	 * @var string
	 */
	protected $status;
	
	/**
	 * The currently queried post's slug.
	 * @since 1.0.1[b]
	 *
	 * @access protected
	 * @var string
	 */
	protected $slug;
	
	/**
	 * The currently queried post's parent.
	 * @since 1.0.1[b]
	 *
	 * @access protected
	 * @var int
	 */
	protected $parent;
	
	/**
	 * The currently queried post's type.
	 * @since 1.0.1[b]
	 *
	 * @access protected
	 * @var string
	 */
	protected $type;
	
	/**
	 * The currently queried post's type data.
	 * @since 1.0.1[b]
	 *
	 * @access private
	 * @var array
	 */
	private $type_data = array();
	
	/**
	 * The currently queried post's taxonomy data.
	 * @since 1.0.6[b]
	 *
	 * @access private
	 * @var array
	 */
	private $taxonomy_data = array();
	
	/**
	 * Class constructor.
	 * @since 1.0.1[b]
	 *
	 * @access public
	 * @param int $id (optional; default: 0)
	 * @param array $type_data (optional; default: array())
	 * @return null
	 */
	public function __construct($id = 0, $type_data = array()) {
		// Extend the Query object and the taxonomies array
		global $rs_query, $taxonomies;
		
		// Create an array of columns to fetch from the database
		$cols = array_keys(get_object_vars($this));
		
		// Exclude 'type_data' and 'taxonomy_data'
		$exclude = array('type_data', 'taxonomy_data');
		
		// Update the columns array
		$cols = array_diff($cols, $exclude);
		
		// Check whether the id is '0'
		if($id !== 0) {
			// Fetch the post from the database
			$post = $rs_query->selectRow('posts', $cols, array('id'=>$id));
			
			// Loop through the array and set the class variables
			foreach($post as $key=>$value) $this->$key = $post[$key];
		}
		
		// Fetch the type data
		$this->type_data = $type_data;
		
		// Check whether the current post type has a taxonomy associated with it and the taxonomy is valid
		if(!empty($this->type_data['taxonomy']) && array_key_exists($this->type_data['taxonomy'], $taxonomies)) {
			// Fetch the taxonomy data
			$this->taxonomy_data = $taxonomies[$this->type_data['taxonomy']];
		}
	}
	
	/**
	 * Construct a list of all posts in the database.
	 * @since 1.4.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listPosts() {
		// Extend the Query object and the user's session data
		global $rs_query, $session;
		
		// Fetch the post's type
		$type = $this->type_data['name'];
		
		// Fetch the status of the currently displayed posts
		$status = $_GET['status'] ?? 'all';
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1><?php echo $this->type_data['label']; ?></h1>
			<?php
			// Check whether the user has sufficient privileges to create posts of the current type and create an action link if so
			if(userHasPrivilege($session['role'], 'can_create_'.str_replace(' ', '_', $this->type_data['labels']['name_lowercase']))) {
				?>
				<a class="button" href="?<?php echo $type === 'post' ? '' : 'type='.$type.'&'; ?>action=create">Create New</a>
				<?php
			}
			
			// Display the page's info
			adminInfo();
			?>
			<hr>
			<?php
			// Check whether any status messages have been returned and display them if so
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The '.strtolower($this->type_data['labels']['name_singular']).' was successfully deleted.', true);
			?>
			<ul class="status-nav">
				<?php
				// Fetch the post entry count from the database (by status)
				$count = array('all'=>$this->getPostCount($type), 'published'=>$this->getPostCount($type, 'published'), 'draft'=>$this->getPostCount($type, 'draft'), 'trash'=>$this->getPostCount($type, 'trash'));
				
				// Loop through the post counts (by status)
				foreach($count as $key=>$value) {
					?>
					<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?type=<?php echo $type.($key === 'all' ? '' : '&status='.$key); ?>"><?php echo ucfirst($key); ?> <span class="count">(<?php echo $value; ?>)</span></a></li>
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
			<div class="entry-count status">
				<?php
				// Display the entry count for the current status
				echo $count[$status].' '.($count[$status] === 1 ? 'entry' : 'entries');
				?>
			</div>
		</div>
		<table class="data-table">
			<thead>
				<?php
				// Check whether the post type is hierarchical
				if($this->type_data['hierarchical']) {
					// Fill an array with the table header columns
					$table_header_cols = array('Title', 'Author', 'Publish Date', 'Parent', 'Meta Title', 'Meta Desc.');
					
					// Check whether comments are enabled sitewide and for the current post type
					if(getSetting('enable_comments', false) && $this->type_data['comments']) {
						// Insert the comments label into the array
						array_splice($table_header_cols, 4, 0, 'Comments');
					}
				} else {
					// Fill an array with the table header columns
					$table_header_cols = array('Title', 'Author', 'Publish Date', 'Meta Title', 'Meta Desc.');
					
					// Check whether comments are enabled sitewide and for the current post type
					if(getSetting('enable_comments', false) && $this->type_data['comments']) {
						// Insert the comments label into the array
						array_splice($table_header_cols, 3, 0, 'Comments');
					}
					
					// Check whether the post type has a taxonomy associated with it
					if(!empty($this->taxonomy_data)) {
						// Insert the taxonomy's label into the array
						array_splice($table_header_cols, 2, 0, $this->taxonomy_data['label']);
					}
				}
				
				// Construct the table header
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				// Set the default 'order by' argument
				$order_by = $type === 'page' ? 'title' : 'date';
				
				// Set the default 'order' argument
				$order = $type === 'page' ? 'ASC' : 'DESC';
				
				// Fetch all posts from the database (by status)
				if($status === 'all')
					$posts = $rs_query->select('posts', '*', array('status'=>array('<>', 'trash'), 'type'=>$type), $order_by, $order, array($page['start'], $page['per_page']));
				else
					$posts = $rs_query->select('posts', '*', array('status'=>$status, 'type'=>$type), $order_by, $order, array($page['start'], $page['per_page']));
				
				// Loop through the posts
				foreach($posts as $post) {
					// Fetch the post's metadata from the database
					$meta = $this->getPostMeta($post['id']);
					
					// Fetch the name of the post's type
					$type_name = str_replace(' ', '_', $this->type_data['labels']['name_lowercase']);
					
					// Set up the action links
					$actions = array(
						userHasPrivilege($session['role'], 'can_edit_'.$type_name) && $status !== 'trash' ? actionLink('edit', array('caption'=>'Edit', 'id'=>$post['id'])) : null,
						userHasPrivilege($session['role'], 'can_edit_'.$type_name) ? ($status === 'trash' ? actionLink('restore', array('caption'=>'Restore', 'id'=>$post['id'])) : actionLink('trash', array('caption'=>'Trash', 'id'=>$post['id']))) : null,
						$status === 'trash' ? (userHasPrivilege($session['role'], 'can_delete_'.$type_name) ? actionLink('delete', array('classes'=>'modal-launch delete-item', 'data_item'=>strtolower($this->type_data['labels']['name_singular']), 'caption'=>'Delete', 'id'=>$post['id'])) : null) : '<a href="'.($post['status'] === 'published' ? (isHomePage($post['id']) ? '/' : getPermalink($post['type'], $post['parent'], $post['slug'])).'">View' : ('/?id='.$post['id'].'&preview=true').'">Preview').'</a>'
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						tableCell((isHomePage($post['id']) ? '<i class="fas fa-home" style="cursor: help;" title="Home Page"></i> ' : '').'<strong>'.$post['title'].'</strong>'.($post['status'] !== 'published' && $status === 'all' ? ' &mdash; <em>'.$post['status'].'</em>' : '').'<div class="actions">'.implode(' &bull; ', $actions).'</div>', 'title'),
						tableCell($this->getAuthor($post['author']), 'author'),
						!$this->type_data['hierarchical'] && !empty($this->type_data['taxonomy']) ? tableCell($this->getTerms($post['id']), 'terms') : '',
						tableCell(is_null($post['date']) ? '&mdash;' : formatDate($post['date'], 'd M Y @ g:i A'), 'publish-date'),
						$this->type_data['hierarchical'] ? tableCell($this->getParent($post['parent']), 'parent') : '',
						getSetting('enable_comments', false) && $this->type_data['comments'] ? tableCell(($meta['comment_status'] ? $meta['comment_count'] : '&mdash;'), 'comments') : '',
						tableCell(!empty($meta['title']) ? 'Yes' : 'No', 'meta_title'),
						tableCell(!empty($meta['description']) ? 'Yes' : 'No', 'meta_description')
					);
				}
				
				// Display a notice if no posts are found
				if(empty($posts))
					echo tableRow(tableCell('There are no '.$this->type_data['labels']['name_lowercase'].' to display.', '', count($table_header_cols)));
				?>
			</tbody>
		</table>
		<?php
		// Set up page navigation
		echo pagerNav($page['current'], $page['count']);
		
		// Include the delete modal
        include_once PATH.ADMIN.INC.'/modal-delete.php';
	}
	
	/**
	 * Create a post.
	 * @since 1.4.1[a]
	 *
	 * @access public
	 * @return null
	 */
	public function createPost() {
		// Fetch the post's type
		$type = $this->type_data['name'];
		
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST) : '';
		?>
		<div class="heading-wrap">
			<h1><?php echo $this->type_data['labels']['create_item']; ?></h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<div class="content">
					<?php
					// Construct a hidden 'type' form tag
					echo formTag('input', array('type'=>'hidden', 'name'=>'type', 'value'=>$type));
					
					// Construct a 'title' form tag
					echo formTag('input', array('id'=>'title-field', 'class'=>'text-input required invalid init', 'name'=>'title', 'value'=>($_POST['title'] ?? ''), 'placeholder'=>$this->type_data['labels']['name_singular'].' title'));
					?>
					<div class="permalink">
						<?php
						// Construct a 'permalink' form tag
						echo formTag('label', array('for'=>'slug', 'content'=>'<strong>Permalink:</strong> '.getSetting('site_url', false).getPermalink($this->type_data['name'])));
						echo formTag('input', array('id'=>'slug-field', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>($_POST['slug'] ?? '')));
						echo '<span>/</span>';
						?>
					</div>
					<?php
					// Construct an 'insert media' button form tag
					echo formTag('input', array('type'=>'button', 'class'=>'button-input button modal-launch', 'value'=>'Insert Media', 'data-type'=>'all', 'data-insert'=>'true'));
					
					// Construct a 'content' form tag
					echo formTag('textarea', array('class'=>'textarea-input', 'name'=>'content', 'rows'=>25, 'content'=>htmlspecialchars(($_POST['content'] ?? ''))));
					?>
				</div>
				<div class="sidebar">
					<div class="block">
						<h2>Publish</h2>
						<div class="row">
							<?php
							// Construct a 'status' form tag
							echo formTag('label', array('for'=>'status', 'content'=>'Status'));
							echo formTag('select', array('class'=>'select-input', 'name'=>'status', 'content'=>'<option value="draft">Draft</option><option value="published">Published</option>'));
							?>
						</div>
						<div class="row">
							<?php
							// Construct an 'author' form tag
							echo formTag('label', array('for'=>'author', 'content'=>'Author'));
							echo formTag('select', array('class'=>'select-input', 'name'=>'author', 'content'=>$this->getAuthorList()));
							?>
						</div>
						<div class="row">
							<?php
							// Construct a 'publish date' form tag
							echo formTag('label', array('for'=>'date', 'content'=>'Publish on')).formTag('br');
							echo formTag('input', array('type'=>'date', 'class'=>'date-input', 'name'=>'date[]'));
							echo formTag('input', array('type'=>'time', 'class'=>'date-input', 'name'=>'date[]'));
							?>
						</div>
						<div id="submit" class="row">
							<?php
							// Construct a 'submit' button form tag
							echo formTag('input', array('type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Publish'));
							?>
						</div>
					</div>
					<?php
					// Check whether the post type is hierarchical
					if($this->type_data['hierarchical']) {
						?>
						<div class="block">
							<h2>Attributes</h2>
							<div class="row">
								<?php
								// Construct a 'parent' form tag
								echo formTag('label', array('for'=>'parent', 'content'=>'Parent'));
								echo formTag('select', array('class'=>'select-input', 'name'=>'parent', 'content'=>'<option value="0">(none)</option>'.$this->getParentList($type)));
								?>
							</div>
							<div class="row">
								<?php
								// Construct a 'template' form tag
								echo formTag('label', array('for'=>'template', 'content'=>'Template'));
								echo formTag('select', array('class'=>'select-input', 'name'=>'template', 'content'=>'<option value="default">Default</option>'.$this->getTemplateList()));
								?>
							</div>
						</div>
						<?php
					} else {
						// Check whether the post type has a valid taxonomy associated with it
						if(!empty($this->taxonomy_data)) {
							?>
							<div class="block">
								<h2><?php echo $this->taxonomy_data['label']; ?></h2>
								<div class="row">
									<?php
									// Construct a 'terms' form checklist
									echo $this->getTermsList();
									?>
								</div>
							</div>
							<?php
						}
					}
					
					// Check whether comments are enabled sitewide and for the current post type
					if(getSetting('enable_comments', false) && $this->type_data['comments']) {
						?>
						<div class="block">
							<h2>Comments</h2>
							<div class="row">
								<?php
								// Check whether comments are enabled for this post
								$comments = isset($_POST['comments']) || (!isset($_POST['comments']) && $this->type_data['comments']) ? 'checked' : '';
								
								// Construct a checkbox tag
								echo formTag('input', array('type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'comments', 'value'=>(!empty($comments) ? 1 : 0), '*'=>$comments, 'label'=>array('class'=>'checkbox-label', 'content'=>'<span>Enable comments</span>')));
								?>
							</div>
						</div>
						<?php
					}
					?>
					<div class="block">
						<h2>Featured Image</h2>
						<div class="row">
							<div class="image-wrap">
								<?php
								// Construct an image tag to display the featured image thumbnail
								echo formTag('img', array('src'=>'//:0', 'data-field'=>'thumb'));
								
								// Construct a span tag to display the 'remove image' button
								echo formTag('span', array('class'=>'image-remove', 'title'=>'Remove', 'content'=>formTag('i', array('class'=>'fas fa-times'))));
								?>
							</div>
							<?php
							// Construct a hidden 'featured image' form tag
							echo formTag('input', array('type'=>'hidden', 'name'=>'feat_image', 'value'=>($_POST['feat_image'] ?? 0), 'data-field'=>'id'));
							?>
							<a class="modal-launch" href="javascript:void(0)" data-type="image">Choose Image</a>
						</div>
					</div>
				</div>
				<div class="metadata">
					<div class="block">
						<h2>Metadata</h2>
						<div class="row">
							<?php
							// Construct a 'meta title' form tag
							echo formTag('label', array('for'=>'meta_title', 'content'=>'Title'));
							echo formTag('br');
							echo formTag('input', array('class'=>'text-input', 'name'=>'meta_title', 'value'=>($_POST['meta_title'] ?? '')));
							?>
						</div>
						<div class="row">
							<?php
							// Construct a 'meta description' form tag
							echo formTag('label', array('for'=>'meta_description', 'content'=>'Description'));
							echo formTag('br');
							echo formTag('textarea', array('class'=>'textarea-input', 'name'=>'meta_description', 'cols'=>30, 'rows'=>4, 'content'=>($_POST['meta_description'] ?? '')));
							?>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
		// Include the upload modal
		include_once PATH.ADMIN.INC.'/modal-upload.php';
	}
	
	/**
	 * Edit a post.
	 * @since 1.4.9[a]
	 *
	 * @access public
	 * @return null
	 */
	public function editPost() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the post's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Posts" page
			redirect(ADMIN_URI);
		} else {
			// Check whether the post's type is valid
			if(empty($this->type)) {
				// Redirect to the "List Posts" page
				redirect(ADMIN_URI);
			} elseif($this->type === 'media') {
				// Redirect to the appropriate "Edit Media" form
				redirect('media.php?id='.$this->id.'&action=edit');
			} elseif($this->type === 'widget') {
				// Redirect to the appropriate "Edit Widget" form
				redirect('widgets.php?id='.$this->id.'&action=edit');
			} else {
				// Check whether the post is in the trash
				if($this->isTrash($this->id)) {
					// Redirect to the "List Posts" trash page
					redirect(ADMIN_URI.($this->type !== 'post' ? '?type='.$this->type.'&' : '?').'status=trash');
				} else {
					// Validate the form data and return any messages
					$message = isset($_POST['submit']) ? $this->validateData($_POST, $this->id) : '';
					
					// Fetch the post's metadata from the database
					$meta = $this->getPostMeta($this->id);
					
					// Check whether the post has a featured image and fetch its dimensions if so
					if(!empty($meta['feat_image']))
						list($width, $height) = getimagesize(PATH.getMediaSrc($meta['feat_image']));
					?>
					<div class="heading-wrap">
						<h1><?php echo $this->type_data['labels']['edit_item']; ?></h1>
						<?php echo $message; ?>
					</div>
					<div class="data-form-wrap clear">
						<form class="data-form" action="" method="post" autocomplete="off">
							<div class="content">
								<?php
								// Construct a 'title' form tag
								echo formTag('input', array('id'=>'title-field', 'class'=>'text-input required invalid init', 'name'=>'title', 'value'=>$this->title, 'placeholder'=>$this->type_data['labels']['name_singular'].' title'));
								?>
								<div class="permalink">
									<?php
									// Construct a 'permalink' form tag
									echo formTag('label', array('for'=>'slug', 'content'=>'<strong>Permalink:</strong> '.getSetting('site_url', false).getPermalink($this->type, $this->parent)));
									echo formTag('input', array('id'=>'slug-field', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>$this->slug));
									echo '<span>/</span>';
									?>
								</div>
								<?php
								// Construct an 'insert media' button form tag
								echo formTag('input', array('type'=>'button', 'class'=>'button-input button modal-launch', 'value'=>'Insert Media', 'data-type'=>'all', 'data-insert'=>'true'));
								
								// Construct a 'content' form tag
								echo formTag('textarea', array('class'=>'textarea-input', 'name'=>'content', 'rows'=>25, 'content'=>htmlspecialchars($this->content)));
								?>
							</div>
							<div class="sidebar">
								<div class="block">
									<h2>Publish</h2>
									<div class="row">
										<?php
										// Construct a 'status' form tag
										echo formTag('label', array('for'=>'status', 'content'=>'Status'));
										echo formTag('select', array('class'=>'select-input', 'name'=>'status', 'content'=>'<option value="'.$this->status.'">'.ucfirst($this->status).'</option>'.($this->status === 'draft' ? '<option value="published">Published</option>' : '<option value="draft">Draft</option>')));
										?>
									</div>
									<div class="row">
										<?php
										// Construct an 'author' form tag
										echo formTag('label', array('for'=>'author', 'content'=>'Author'));
										echo formTag('select', array('class'=>'select-input', 'name'=>'author', 'content'=>$this->getAuthorList($this->author)));
										?>
									</div>
									<div class="row">
										<?php
										// Construct a 'publish date' form tag
										echo formTag('label', array('for'=>'date', 'content'=>'Published on')).formTag('br');
										echo formTag('input', array('type'=>'date', 'class'=>'date-input', 'name'=>'date[]', 'value'=>(!is_null($this->date) ? formatDate($this->date, 'Y-m-d') : '')));
										echo formTag('input', array('type'=>'time', 'class'=>'date-input', 'name'=>'date[]', 'value'=>(!is_null($this->date) ? formatDate($this->date, 'H:i') : '')));
										?>
									</div>
									<div id="submit" class="row">
										<?php
										// Construct a view/preview link
										echo $this->status === 'published' ? '<a href="'.(isHomePage($this->id) ? '/' : getPermalink($this->type, $this->parent, $this->slug)).'">View</a>' : '<a href="/?id='.$this->id.'&preview=true">Preview</a>';
										
										// Construct a 'submit' button form tag
										echo formTag('input', array('type'=>'submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update'));
										?>
									</div>
								</div>
								<?php
								// Check whether the post type is hierarchical
								if($this->type_data['hierarchical']) {
									?>
									<div class="block">
										<h2>Attributes</h2>
										<div class="row">
											<?php
											// Construct a 'parent' form tag
											echo formTag('label', array('for'=>'parent', 'content'=>'Parent'));
											echo formTag('select', array('class'=>'select-input', 'name'=>'parent', 'content'=>'<option value="0">(none)</option>'.$this->getParentList($this->type, $this->parent, $this->id)));
											?>
										</div>
										<div class="row">
											<?php
											// Construct a 'template' form tag
											echo formTag('label', array('for'=>'template', 'content'=>'Template'));
											echo formTag('select', array('class'=>'select-input', 'name'=>'template', 'content'=>'<option value="default">Default</option>'.$this->getTemplateList($this->id)));
											?>
										</div>
									</div>
									<?php
								} else {
									// Check whether the post type has a valid taxonomy associated with it
									if(!empty($this->taxonomy_data)) {
										?>
										<div class="block">
											<h2><?php echo $this->taxonomy_data['label']; ?></h2>
											<div class="row">
												<?php
												// Construct a 'terms' form checklist
												echo $this->getTermsList($this->id);
												?>
											</div>
										</div>
										<?php
									}
								}
								
								// Check whether comments are enabled sitewide and for the current post type
								if(getSetting('enable_comments', false) && $this->type_data['comments']) {
									?>
									<div class="block">
										<h2>Comments</h2>
										<div class="row">
											<?php
											// Construct a checkbox tag
											echo formTag('input', array('type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'comments', 'value'=>(int)$meta['comment_status'], '*'=>($meta['comment_status'] ? 'checked' : ''), 'label'=>array('class'=>'checkbox-label', 'content'=>'<span>Enable comments</span>')));
											?>
										</div>
									</div>
									<?php
								}
								?>
								<div class="block">
									<h2>Featured Image</h2>
									<div class="row">
										<div class="image-wrap<?php echo !empty($meta['feat_image']) ? ' visible' : ''; ?>" style="width: <?php echo $width ?? 0; ?>px;">
											<?php
											// Construct an image tag to display the featured image thumbnail
											echo formTag('img', array('src'=>getMediaSrc($meta['feat_image']), 'data-field'=>'thumb'));
											
											// Construct a span tag to display the 'remove image' button
											echo formTag('span', array('class'=>'image-remove', 'title'=>'Remove', 'content'=>formTag('i', array('class'=>'fas fa-times'))));
											?>
										</div>
										<?php
										// Construct a hidden 'featured image' form tag
										echo formTag('input', array('type'=>'hidden', 'name'=>'feat_image', 'value'=>$meta['feat_image'], 'data-field'=>'id'));
										?>
										<a class="modal-launch" href="javascript:void(0)" data-type="image">Choose Image</a>
									</div>
								</div>
							</div>
							<div class="metadata">
								<div class="block">
									<h2>Metadata</h2>
									<div class="row">
										<?php
										// Construct a 'meta title' form tag
										echo formTag('label', array('for'=>'meta_title', 'content'=>'Title'));
										echo formTag('br');
										echo formTag('input', array('class'=>'text-input', 'name'=>'meta_title', 'value'=>($meta['title'] ?? '')));
										?>
									</div>
									<div class="row">
										<?php
										// Construct a 'meta description' form tag
										echo formTag('label', array('for'=>'meta_description', 'content'=>'Description'));
										echo formTag('br');
										echo formTag('textarea', array('class'=>'textarea-input', 'name'=>'meta_description', 'cols'=>30, 'rows'=>4, 'content'=>($meta['description'] ?? '')));
										?>
									</div>
								</div>
							</div>
						</form>
					</div>
					<?php
					// Include the upload modal
					include_once PATH.ADMIN.INC.'/modal-upload.php';
				}
			}
		}
	}
	
	/**
	 * Send a post to the trash.
	 * @since 1.4.6[a]
	 *
	 * @access public
	 * @return null
	 */
	public function trashPost() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the post's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Posts" page
			redirect(ADMIN_URI);
		} else {
			// Set the post's status to 'trash'
			$rs_query->update('posts', array('status'=>'trash'), array('id'=>$this->id));
			
			// Redirect to the "List Posts" page
			redirect($this->type_data['menu_link']);
		}
	}
	
	/**
	 * Restore a post from the trash.
	 * @since 1.4.6[a]
	 *
	 * @access public
	 * @return null
	 */
	public function restorePost() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the post's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Posts" page
			redirect(ADMIN_URI);
		} else {
			// Set the post's status to 'draft'
			$rs_query->update('posts', array('status'=>'draft'), array('id'=>$this->id));
			
			// Redirect to the "List Posts" trash page
			redirect($this->type_data['menu_link'].($this->type !== 'post' ? '&' : '?').'status=trash');
		}
	}
	
	/**
	 * Delete a post.
	 * @since 1.4.7[a]
	 *
	 * @access public
	 * @return null
	 */
	public function deletePost() {
		// Extend the Query object
		global $rs_query;
		
		// Check whether the post's id is valid
		if(empty($this->id) || $this->id <= 0) {
			// Redirect to the "List Posts" page
			redirect(ADMIN_URI);
		} else {
			// Delete the post from the database
			$rs_query->delete('posts', array('id'=>$this->id));
			
			// Delete the post's metadata from the database
			$rs_query->delete('postmeta', array('post'=>$this->id));
			
			// Fetch all term relationships associated with the post from the database
			$relationships = $rs_query->select('term_relationships', '*', array('post'=>$this->id));
			
			// Loop through the relationships
			foreach($relationships as $relationship) {
				// Delete each unused relationship from the database
				$rs_query->delete('term_relationships', array('id'=>$relationship['id']));
				
				// Fetch the number of shared relationships between the category and a post in the database
				$count = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$relationship['term']));
				
				// Update the category's count (posts)
				$rs_query->update('terms', array('count'=>$count), array('id'=>$relationship['term']));
			}
			
			// Delete all comments associated with the post from the database
			$rs_query->delete('comments', array('post'=>$this->id));
			
			// Fetch any menu items associated with the post from the database
			$menu_items = $rs_query->select('postmeta', 'post', array('_key'=>'post_link', 'value'=>$this->id));
			
			// Loop through the menu items
			foreach($menu_items as $menu_item) {
				// Set the status of any menu items associated with the post to 'invalid' in the database
				$rs_query->update('posts', array('status'=>'invalid'), array('id'=>$menu_item['post']));
			}
			
			// Redirect to the "List Posts" page with an appropriate exit status
			redirect($this->type_data['menu_link'].($this->type !== 'post' ? '&' : '?').'status=trash&exit_status=success');
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 1.4.7[a]
	 *
	 * @access private
	 * @param array $data
	 * @param int $id (optional; default: 0)
	 * @return null|string (null on $id == 0; string on $id != 0)
	 */
	private function validateData($data, $id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['title']) || empty($data['slug']))
			return statusMessage('R');
		
		// Sanitize the slug (strip off HTML and/or PHP tags and replace any characters not specified in the filter)
		$slug = preg_replace('/[^a-zA-Z0-9\-]/i', '', strip_tags($data['slug']));
		
		// Make sure the slug is unique
		if($this->slugExists($slug, $id))
			$slug = getUniquePostSlug($slug);
		
		// Make sure the post has a valid status
		if($data['status'] !== 'draft' && $data['status'] !== 'published')
			$data['status'] = 'draft';
		
		// Create an array to hold the post's metadata
		$postmeta = array('title'=>$data['meta_title'], 'description'=>$data['meta_description'], 'feat_image'=>$data['feat_image']);
		
		// Check whether a page template has been submitted and add it to the postmeta array if so
		if(isset($data['template'])) $postmeta['template'] = $data['template'];
		
		// Check whether comments are enabled for the post type
		if($this->type_data['comments']) {
			// Check whether comments are enabled for the specified post and add the status to the postmeta array if so
			$postmeta['comment_status'] = isset($data['comments']) ? 1 : 0;
		}
		
		if($id === 0) {
			// Check whether a date has been provided and is valid
			if(!empty($data['date'][0]) && !empty($data['date'][1]) && $data['date'][0] >= '1000-01-01') {
				// Merge the date and time into a string
				$data['date'] = implode(' ', $data['date']);
			} else {
				// Fetch the current date and time
				$data['date'] = 'NOW()';
			}
			
			// Set the parent to zero if the post's type is 'post' (non-hierarchical)
			if(!$this->type_data['hierarchical']) $data['parent'] = 0;
			
			// Insert the new post into the database
			$insert_id = $rs_query->insert('posts', array('title'=>$data['title'], 'author'=>$data['author'], 'date'=>$data['date'], 'content'=>$data['content'], 'status'=>$data['status'], 'slug'=>$slug, 'parent'=>$data['parent'], 'type'=>$data['type']));
			
			// Check whether comments data has been submitted and set the comment count to zero if so
			if(isset($postmeta['comment_status'])) $postmeta['comment_count'] = 0;
			
			// Insert the post's metadata into the database
			foreach($postmeta as $key=>$value)
				$rs_query->insert('postmeta', array('post'=>$insert_id, '_key'=>$key, 'value'=>$value));
			
			// Check whether any terms have been selected
			if(!empty($data['terms'])) {
				// Loop through the terms
				foreach($data['terms'] as $term) {
					// Insert a new term relationship into the database
					$rs_query->insert('term_relationships', array('term'=>$term, 'post'=>$insert_id));
					
					// Fetch the number of shared relationships between the term and a post in the database
					$count = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$term));
					
					// Update the term's count (posts)
					$rs_query->update('terms', array('count'=>$count), array('id'=>$term));
				}
			}
			
			// Redirect to the appropriate "Edit Post" page
			redirect(ADMIN_URI.'?id='.$insert_id.'&action=edit');
		} else {
			// Check whether a date has been provided and is valid
			if(!empty($data['date'][0]) && !empty($data['date'][1]) && $data['date'][0] >= '1000-01-01') {
				// Merge the date and time into a string
				$data['date'] = implode(' ', $data['date']);
			} else {
				// Set the date and time to null
				$data['date'] = null;
			}
			
			// Set the parent to zero if the post is non-hierarchical
			if(!$this->type_data['hierarchical']) $data['parent'] = 0;
			
			// Update the post in the database
			$rs_query->update('posts', array('title'=>$data['title'], 'author'=>$data['author'], 'date'=>$data['date'], 'modified'=>'NOW()', 'content'=>$data['content'], 'status'=>$data['status'], 'slug'=>$slug, 'parent'=>$data['parent']), array('id'=>$id));
			
			// Update the post's metadata in the database
			foreach($postmeta as $key=>$value)
				$rs_query->update('postmeta', array('value'=>$value), array('post'=>$id, '_key'=>$key));
			
			// Fetch all term relationships associated with the post from the database
			$relationships = $rs_query->select('term_relationships', '*', array('post'=>$id));
			
			// Loop through the relationships
			foreach($relationships as $relationship) {
				// Check whether the relationship still exists
				if(empty($data['terms']) || !in_array($relationship['term'], $data['terms'])) {
					// Delete each unused relationship from the database
					$rs_query->delete('term_relationships', array('id'=>$relationship['id']));
					
					// Fetch the number of shared relationships between the term and a post in the database
					$count = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$relationship['term']));
					
					// Update the term's count (posts)
					$rs_query->update('terms', array('count'=>$count), array('id'=>$relationship['term']));
				}
			}
			
			// Check whether any terms have been selected
			if(!empty($data['terms'])) {
				// Loop through the terms
				foreach($data['terms'] as $term) {
					// Fetch any relationships between the current term and the post from the database
					$relationship = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$term, 'post'=>$id));
					
					// Check whether the relationship already exists
					if($relationship) {
						// Skip to the next term
						continue;
					} else {
						// Insert a new term relationship into the database
						$rs_query->insert('term_relationships', array('term'=>$term, 'post'=>$id));
						
						// Fetch the number of shared relationships between the term and a post in the database
						$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term'=>$term));
						
						// Update the term's count (posts)
						$rs_query->update('terms', array('count'=>$count), array('id'=>$term));
					}
				}
			}
			
			// Update the class variables
			foreach($data as $key=>$value) $this->$key = $value;
			
			// Return a status message
			return statusMessage($this->type_data['labels']['name_singular'].' updated! <a href="'.ADMIN_URI.($this->type === 'post' ? '' : '?type='.$this->type).'">Return to list</a>?', true);
		}
	}
	
	/**
	 * Check whether a slug exists in the database.
	 * @since 1.4.8[a]
	 *
	 * @access protected
	 * @param string $slug
	 * @param int $id (optional; default: 0)
	 * @return bool
	 */
	protected function slugExists($slug, $id = 0) {
		// Extend the Query object
		global $rs_query;
		
		if($id === 0) {
			// Fetch the number of times the slug appears in the database and return true if it does
			return $rs_query->selectRow('posts', 'COUNT(slug)', array('slug'=>$slug)) > 0;
		} else {
			// Fetch the number of times the slug appears in the database (minus the current post) and return true if it does
			return $rs_query->selectRow('posts', 'COUNT(slug)', array('slug'=>$slug, 'id'=>array('<>', $id))) > 0;
		}
	}
	
	/**
	 * Check whether a post is in the trash.
	 * @since 1.4.9[a]
	 *
	 * @access private
	 * @param int $id
	 * @return bool
	 */
	private function isTrash($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's status from the database and return true if it's in the trash
		return $rs_query->selectField('posts', 'status', array('id'=>$id)) === 'trash';
	}
	
	/**
	 * Check whether a post is a descendant of another post.
	 * @since 1.4.9[a]
	 *
	 * @access private
	 * @param int $id
	 * @param int $ancestor
	 * @return bool
	 */
	private function isDescendant($id, $ancestor) {
		// Extend the Query object
		global $rs_query;
		
		do {
			// Fetch the post's parent from the database
			$parent = $rs_query->selectField('posts', 'parent', array('id'=>$id));
			
			// Set the new id
			$id = (int)$parent;
			
			// Return true if the post's ancestor is found
			if($id === $ancestor) return true;
		} while($id !== 0);
		
		// Return false if no ancestor is found
		return false;
	}
	
	/**
	 * Fetch a post's metadata.
	 * @since 1.4.10[a]
	 *
	 * @access protected
	 * @param int $id
	 * @return array
	 */
	protected function getPostMeta($id) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's metadata from the database
		$postmeta = $rs_query->select('postmeta', array('_key', 'value'), array('post'=>$id));
		
		// Create an empty array to hold the metadata
		$meta = array();
		
		// Loop through the metadata
		foreach($postmeta as $metadata) {
			// Get the meta values
			$values = array_values($metadata);
			
			// Loop through the individual metadata entries
			for($i = 0; $i < count($metadata); $i += 2) {
				// Assign the metadata to the meta array
				$meta[$values[$i]] = $values[$i + 1];
			}
		}
		
		// Return the metadata
		return $meta;
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
		// Extend the Query object
		global $rs_query;
		
		// Fetch the author's username from the database and return it
		return $rs_query->selectField('users', 'username', array('id'=>$id));
	}
	
	/**
	 * Construct a list of authors.
	 * @since 1.4.4[a]
	 *
	 * @access private
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function getAuthorList($id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch all authors from the database
		$authors = $rs_query->select('users', array('id', 'username'), '', 'username');
		
		// Add each author to the list
		foreach($authors as $author)
			$list .= '<option value="'.$author['id'].'"'.($author['id'] === (int)$id ? ' selected' : '').'>'.$author['username'].'</option>';
		
		// Return the list
		return $list;
	}
	
	/**
	 * Fetch a post's terms.
	 * @since 1.5.0[a]
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getTerms($id) {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty array to hold the terms
		$terms = array();
		
		// Fetch the term relationships from the database
		$relationships = $rs_query->select('term_relationships', 'term', array('post'=>$id));
		
		// Loop through the term relationships
		foreach($relationships as $relationship) {
			// Fetch each term from the database and assign them to the terms array
			$terms[] = $rs_query->selectField('terms', 'name', array('id'=>$relationship['term'], 'taxonomy'=>getTaxonomyId($this->taxonomy_data['name'])));
		}
		
		// Return the terms
		return empty($terms) ? '&mdash;' : implode(', ', $terms);
	}
	
	/**
	 * Construct a list of terms.
	 * @since 1.5.2[a]
	 *
	 * @access private
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function getTermsList($id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Create a list with an opening unordered list tag
		$list = '<ul id="categories-list">';
		
		// Fetch all terms associated with the post type from the database
		$terms = $rs_query->select('terms', array('id', 'name'), array('taxonomy'=>getTaxonomyId($this->taxonomy_data['name'])), 'name');
		
		// Loop through the terms
		foreach($terms as $term) {
			// Fetch any existing term relationship from the database
			$relationship = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$term['id'], 'post'=>$id));
			
			// Construct the list
			$list .= '<li>'.formTag('input', array('type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'terms[]', 'value'=>$term['id'], '*'=>($relationship ? 'checked' : ''), 'label'=>array('content'=>'<span>'.$term['name'].'</span>'))).'</li>';
		}
		
		// Close the unordered list
		$list .= '</ul>';
		
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
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's parent from the database
		$parent = $rs_query->selectField('posts', 'title', array('id'=>$id));
		
		// Return the parent's title
		return empty($parent) ? '&mdash;' : $parent;
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
		// Extend the Query object
		global $rs_query;
		
		// Create an empty list
		$list = '';
		
		// Fetch all posts from the database (by type)
		$posts = $rs_query->select('posts', array('id', 'title'), array('status'=>array('<>', 'trash'), 'type'=>$type));
		
		// Loop through the posts
		foreach($posts as $post) {
			// Do some extra checks if an id is provided
			if($id !== 0) {
				// Skip the current post
				if($post['id'] === $id) continue;
				
				// Skip all descendant posts
				if($this->isDescendant($post['id'], $id)) continue;
			}
			
			// Construct the list
			$list .= '<option value="'.$post['id'].'"'.($post['id'] === $parent ? ' selected' : '').'>'.$post['title'].'</option>';
		}
		
		// Return the list
		return $list;
	}
	
	/**
	 * Construct a list of templates.
	 * @since 2.3.3[a]
	 *
	 * @access private
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function getTemplateList($id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Construct the file path for the current theme's page templates directory
		$templates_path = trailingSlash(PATH.THEMES).getSetting('theme', false).'/templates';
		
		// Check whether the templates directory exists within the current theme
		if(file_exists($templates_path)) {
			// Fetch all templates in the directory
			$templates = array_diff(scandir($templates_path), array('.', '..'));
			
			// Fetch the page's current template from the database
			$current = $rs_query->selectField('postmeta', 'value', array('post'=>$id, '_key'=>'template'));
			
			// Loop through the templates and add each one to an array
			foreach($templates as $template)
				$list[] = '<option value="'.$template.'"'.(isset($current) && $current === $template ? ' selected' : '').'>'.ucwords(substr(str_replace('-', ' ', $template), 0, strpos($template, '.'))).'</option>';
			
			// Convert the list array into a string
			$list = implode('', $list);
		}
		
		// Return the list
		return $list ?? '';
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
		// Extend the Query object
		global $rs_query;
		
		// Check whether a status has been provided
		if(empty($status)) {
			// Return the count of all posts (excluding ones that are in the trash)
			return $rs_query->select('posts', 'COUNT(*)', array('status'=>array('<>', 'trash'), 'type'=>$type));
		} else {
			// Return the count of all posts by the status
			return $rs_query->select('posts', 'COUNT(*)', array('status'=>$status, 'type'=>$type));
		}
	}
}