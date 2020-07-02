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
	 * @access private
	 * @var int
	 */
	private $id;
	
	/**
	 * The currently queried post's title.
	 * @since 1.0.1[b]
	 *
	 * @access private
	 * @var string
	 */
	private $title;
	
	/**
	 * The currently queried post's author.
	 * @since 1.0.1[b]
	 *
	 * @access private
	 * @var int
	 */
	private $author;
	
	/**
	 * The currently queried post's date.
	 * @since 1.0.1[b]
	 *
	 * @access private
	 * @var string
	 */
	private $date;
	
	/**
	 * The currently queried post's modified date.
	 * @since 1.0.1[b]
	 *
	 * @access private
	 * @var string
	 */
	private $modified;
	
	/**
	 * The currently queried post's content.
	 * @since 1.0.1[b]
	 *
	 * @access private
	 * @var string
	 */
	private $content;
	
	/**
	 * The currently queried post's status.
	 * @since 1.0.1[b]
	 *
	 * @access private
	 * @var string
	 */
	private $status;
	
	/**
	 * The currently queried post's slug.
	 * @since 1.0.1[b]
	 *
	 * @access private
	 * @var string
	 */
	private $slug;
	
	/**
	 * The currently queried post's parent.
	 * @since 1.0.1[b]
	 *
	 * @access private
	 * @var int
	 */
	private $parent;
	
	/**
	 * The currently queried post's type.
	 * @since 1.0.1[b]
	 *
	 * @access private
	 * @var string
	 */
	private $type;
	
	/**
	 * The currently queried post's type data.
	 * @since 1.0.1[b]
	 *
	 * @access private
	 * @var array
	 */
	private $type_data = array();
	
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
		// Extend the Query object
		global $rs_query;
		
		// Check whether the id is '0'
		if($id !== 0) {
			// Fetch the post from the database
			$post = $rs_query->selectRow('posts', '*', array('id'=>$id));
			
			// Loop through the post array and set the class variables
			foreach($post as $key=>$value) $this->$key = $post[$key];
		}
		
		// Set the $type_data class variable
		$this->type_data = $type_data;
	}
	
	/**
	 * Construct a list of all posts in the database.
	 * @since 1.4.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listPosts() {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's type
		$type = $_GET['type'] ?? 'post';
		
		// Fetch the post's status
		$status = $_GET['status'] ?? 'all';
		
		// Set up pagination
		$page = paginate((int)($_GET['paged'] ?? 1));
		
		// Fetch the post entry count from the database (by type)
		$count = array('all'=>$this->getPostCount($type), 'published'=>$this->getPostCount($type, 'published'), 'draft'=>$this->getPostCount($type, 'draft'), 'trash'=>$this->getPostCount($type, 'trash'));
		?>
		<div class="heading-wrap">
			<h1><?php echo $this->type_data['label']; ?></h1>
			<a class="button" href="?<?php echo $type === 'post' ? '' : 'type='.$type.'&'; ?>action=create">Create New</a>
			<hr>
			<?php
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage('The '.$type.' was successfully deleted.', true);
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
			<div class="entry-count post">
				<?php
				// Display the entry count
				echo $count[$status].' '.($count[$status] === 1 ? 'entry' : 'entries');
				?>
			</div>
		</div>
		<table class="data-table">
			<thead>
				<?php
				// Fill an array with the table header columns
				if($type === 'post')
					$table_header_cols = array('Title', 'Author', 'Categories', 'Publish Date', 'Meta Title', 'Meta Desc.');
				else
					$table_header_cols = array('Title', 'Author', 'Publish Date', 'Parent', 'Meta Title', 'Meta Desc.');
				
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
				
				// Fetch all posts from the database
				if($status === 'all')
					$posts = $rs_query->select('posts', '*', array('status'=>array('<>', 'trash'), 'type'=>$type), $order_by, $order, array($page['start'], $page['per_page']));
				else
					$posts = $rs_query->select('posts', '*', array('status'=>$status, 'type'=>$type), $order_by, $order, array($page['start'], $page['per_page']));
				
				// Loop through the posts
				foreach($posts as $post) {
					// Fetch the post's metadata from the database
					$meta = $this->getPostMeta($post['id']);
					
					echo tableRow(
						tableCell((isHomePage($post['id']) ? '<i class="fas fa-home" style="cursor: help;" title="Home Page"></i> ' : '').'<strong>'.$post['title'].'</strong>'.($post['status'] !== 'published' && $status === 'all' ? ' &ndash; <em>'.$post['status'].'</em>' : '').'<div class="actions">'.($status !== 'trash' ? '<a href="?id='.$post['id'].'&action=edit">Edit</a> &bull; <a href="?id='.$post['id'].'&action=trash">Trash</a> &bull; <a href="'.($post['status'] === 'published' ? (isHomePage($post['id']) ? '/' : getPermalink($post['type'], $post['parent'], $post['slug'])).'">View' : ('/?id='.$post['id'].'&preview=true').'">Preview').'</a>' : '<a href="?id='.$post['id'].'&action=restore">Restore</a> &bull; <a class="modal-launch delete-item" href="?id='.$post['id'].'&action=delete" data-item="'.$post['type'].'">Delete</a>').'</div>', 'title'),
						tableCell($this->getAuthor($post['author']), 'author'),
						$type === 'post' ? tableCell($this->getCategories($post['id']), 'categories') : '',
						tableCell(is_null($post['date']) ? '&mdash;' : formatDate($post['date'], 'd M Y @ g:i A'), 'publish-date'),
						$type !== 'post' ? tableCell($this->getParent($post['parent']), 'parent') : '',
						tableCell(!empty($meta['title']) ? 'Yes' : 'No', 'meta_title'),
						tableCell(!empty($meta['description']) ? 'Yes' : 'No', 'meta_description')
					);
				}
				
				// Display a notice if no posts are found
				if(empty($posts))
					echo tableRow(tableCell('There are no '.$type.'s to display.', '', count($table_header_cols)));
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
	 * Construct the 'Create Post' form.
	 * @since 1.4.1[a]
	 *
	 * @access public
	 * @return null
	 */
	public function createPost() {
		// Fetch the post's type
		$type = $_GET['type'] ?? 'post';
		
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
					echo formTag('input', array('id'=>'title-field', 'class'=>'text-input required invalid init', 'name'=>'title', 'value'=>($_POST['title'] ?? ''), 'placeholder'=>ucfirst($type).' title'));
					?>
					<div class="permalink">
						<?php
						// Construct a 'permalink' form tag
						echo formTag('label', array('for'=>'slug', 'content'=>'<strong>Permalink:</strong> '.getSetting('site_url', false).'/'));
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
					<div class="block">
						<?php
						if(!$this->type_data['hierarchical']) {
							?>
							<h2>Categories</h2>
							<div class="row">
								<?php
								// Construct a 'categories' form checklist
								echo $this->getCategoriesList();
								?>
							</div>
							<?php
						} else {
							?>
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
							<?php
						}
						?>
					</div>
					<div class="block">
						<h2>Featured Image</h2>
						<div class="row">
							<div class="image-wrap">
								<?php
								// Construct an image tag to display the featured image thumbnail
								echo formTag('img', array('src'=>'//:0', 'width'=>'100%', 'data-field'=>'thumb'));
								
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
	 * Construct the 'Edit Post' form.
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
			// Redirect to the 'List Posts' page
			redirect('posts.php');
		} else {
			// Check whether the post's type is valid
			if(empty($this->type)) {
				// Redirect to the 'List Posts' page
				redirect('posts.php');
			} elseif($this->type === 'media') {
				// Redirect to the appropriate 'Edit Media' form
				redirect('media.php?id='.$this->id.'&action=edit');
			} elseif($this->type === 'widget') {
				// Redirect to the appropriate 'Edit Widget' form
				redirect('widgets.php?id='.$this->id.'&action=edit');
			} else {
				// Check whether the post is in the trash
				if($this->isTrash($this->id)) {
					// Redirect to the 'List Posts' trash page
					redirect('posts.php'.($this->type !== 'post' ? '?type='.$this->type.'&' : '?').'status=trash');
				} else {
					// Validate the form data and return any messages
					$message = isset($_POST['submit']) ? $this->validateData($_POST, $this->id) : '';
					
					// Fetch the post's metadata from the database
					$meta = $this->getPostMeta($this->id);
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
								echo formTag('input', array('id'=>'title-field', 'class'=>'text-input required invalid init', 'name'=>'title', 'value'=>$this->title, 'placeholder'=>ucfirst($this->type).' title'));
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
								<div class="block">
									<?php
									if(!$this->type_data['hierarchical']) {
										?>
										<h2>Categories</h2>
										<div class="row">
											<?php
											// Construct a 'categories' form checklist
											echo $this->getCategoriesList($this->id);
											?>
										</div>
										<?php
									} else {
										?>
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
										<?php
									}
									?>
								</div>
								<div class="block">
									<h2>Featured Image</h2>
									<div class="row">
										<div class="image-wrap<?php echo !empty($meta['feat_image']) ? ' visible' : ''; ?>">
											<?php
											// Construct an image tag to display the featured image thumbnail
											echo formTag('img', array('src'=>getMediaSrc($meta['feat_image']), 'width'=>'100%', 'data-field'=>'thumb'));
											
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
			// Redirect to the 'List Posts' page
			redirect('posts.php');
		} else {
			// Set the post's status to 'trash'
			$rs_query->update('posts', array('status'=>'trash'), array('id'=>$this->id));
			
			// Redirect to the 'List Posts' page
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
			// Redirect to the 'List Posts' page
			redirect('posts.php');
		} else {
			// Set the post's status to 'draft'
			$rs_query->update('posts', array('status'=>'draft'), array('id'=>$this->id));
			
			// Redirect to the 'List Posts' trash page
			redirect($this->type_data['menu_link'].($this->type !== 'post' ? '&' : '?').'status=trash');
		}
	}
	
	/**
	 * Delete a post from the database.
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
			// Redirect to the 'List Posts' page
			redirect('posts.php');
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
			
			// Fetch any menu items associated with the post from the database
			$menu_items = $rs_query->select('postmeta', 'post', array('_key'=>'post_link', 'value'=>$this->id));
			
			// Loop through the menu items
			foreach($menu_items as $menu_item) {
				// Set the status of any menu items associated with the post to 'invalid' in the database
				$rs_query->update('posts', array('status'=>'invalid'), array('id'=>$menu_item['post']));
			}
			
			// Redirect to the 'List Posts' page (with a success status)
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
		
		// Make sure the slug is not already being used
		if($this->slugExists($slug, $id))
			return statusMessage('That slug is already in use. Please choose another one.');
		
		// Make sure the post has a valid status
		if($data['status'] !== 'draft' && $data['status'] !== 'published')
			$data['status'] = 'draft';
		
		// Create an array to hold the post's metadata
		$postmeta = array('title'=>$data['meta_title'], 'description'=>$data['meta_description'], 'feat_image'=>$data['feat_image']);
		
		// Check whether a page template has been submitted and add it to the postmeta array if so
		if(isset($data['template'])) $postmeta['template'] = $data['template'];
		
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
			if($data['type'] === 'post') $data['parent'] = 0;
			
			// Insert the new post into the database
			$insert_id = $rs_query->insert('posts', array('title'=>$data['title'], 'author'=>$data['author'], 'date'=>$data['date'], 'content'=>$data['content'], 'status'=>$data['status'], 'slug'=>$slug, 'parent'=>$data['parent'], 'type'=>$data['type']));
			
			// Insert the post's metadata into the database
			foreach($postmeta as $key=>$value)
				$rs_query->insert('postmeta', array('post'=>$insert_id, '_key'=>$key, 'value'=>$value));
			
			// Check whether any categories have been selected
			if(!empty($data['categories'])) {
				// Loop through the categories
				foreach($data['categories'] as $category) {
					// Insert a new term relationship into the database
					$rs_query->insert('term_relationships', array('term'=>$category, 'post'=>$insert_id));
					
					// Fetch the number of shared relationships between the category and a post in the database
					$count = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$category));
					
					// Update the category's count (posts)
					$rs_query->update('terms', array('count'=>$count), array('id'=>$category));
				}
			}
			
			// Redirect to the 'Edit Post' page
			redirect('posts.php?id='.$insert_id.'&action=edit');
		} else {
			// Check whether a date has been provided and is valid
			if(!empty($data['date'][0]) && !empty($data['date'][1]) && $data['date'][0] >= '1000-01-01') {
				// Merge the date and time into a string
				$data['date'] = implode(' ', $data['date']);
			} else {
				// Set the date and time to null
				$data['date'] = null;
			}
			
			// Fetch the post's type from the database
			$type = $rs_query->selectField('posts', 'type', array('id'=>$id));
			
			// Set the parent to zero if the post's type is 'post' (non-hierarchical)
			if($type === 'post') $data['parent'] = 0;
			
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
				if(empty($data['categories']) || !in_array($relationship['term'], $data['categories'])) {
					// Delete each unused relationship from the database
					$rs_query->delete('term_relationships', array('id'=>$relationship['id']));
					
					// Fetch the number of shared relationships between the category and a post in the database
					$count = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$relationship['term']));
					
					// Update the category's count (posts)
					$rs_query->update('terms', array('count'=>$count), array('id'=>$relationship['term']));
				}
			}
			
			// Check whether any categories have been selected
			if(!empty($data['categories'])) {
				// Loop through the categories
				foreach($data['categories'] as $category) {
					// Fetch any relationships between the current category and the post from the database
					$relationship = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$category, 'post'=>$id));
					
					// Check whether the relationship already exists
					if($relationship) {
						// Skip to the next category
						continue;
					} else {
						// Insert a new term relationship into the database
						$rs_query->insert('term_relationships', array('term'=>$category, 'post'=>$id));
						
						// Fetch the number of shared relationships between the category and a post in the database
						$count = $rs_query->select('term_relationships', 'COUNT(*)', array('term'=>$category));
						
						// Update the category's count (posts)
						$rs_query->update('terms', array('count'=>$count), array('id'=>$category));
					}
				}
			}
			
			// Update the class variables
			foreach($data as $key=>$value) $this->$key = $value;
			
			// Return a status message
			return statusMessage(ucfirst($type).' updated! <a href="posts.php'.($type === 'post' ? '' : '?type='.$type).'">Return to list</a>?', true);
		}
	}
	
	/**
	 * Check whether a slug exists in the database.
	 * @since 1.4.8[a]
	 *
	 * @access protected
	 * @param string $slug
	 * @param int $id
	 * @return bool
	 */
	protected function slugExists($slug, $id) {
		// Extend the Query object
		global $rs_query;
		
		if($id === 0) {
			// Fetch the number of times the slug appears in the database
			$count = $rs_query->selectRow('posts', 'COUNT(slug)', array('slug'=>$slug));
		} else {
			// Fetch the number of times the slug appears in the database (minus the current post)
			$count = $rs_query->selectRow('posts', 'COUNT(slug)', array('slug'=>$slug, 'id'=>array('<>', $id)));
		}
		
		// Return true if the count is greater than zero
		return $count > 0;
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
		
		// Fetch the post's status from the database
		$status = $rs_query->selectField('posts', 'status', array('id'=>$id));
		
		// Return true if the post is in the trash
		return $status === 'trash';
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
			$list .= '<option value="'.$author['id'].'"'.($author['id'] === $id ? ' selected' : '').'>'.$author['username'].'</option>';
		
		// Return the list
		return $list;
	}
	
	/**
	 * Fetch a post's categories.
	 * @since 1.5.0[a]
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getCategories($id) {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty array to hold the categories
		$categories = array();
		
		// Fetch the term relationships from the database
		$relationships = $rs_query->select('term_relationships', 'term', array('post'=>$id));
		
		// Loop through the term relationships
		foreach($relationships as $relationship) {
			// Fetch each term from the database and assign them to the categories array
			$categories[] = $rs_query->selectField('terms', 'name', array('id'=>$relationship['term'], 'taxonomy'=>getTaxonomyId('category')));
		}
		
		// Return the categories
		return empty($categories) ? '&mdash;' : implode(', ', $categories);
	}
	
	/**
	 * Construct a list of categories.
	 * @since 1.5.2[a]
	 *
	 * @access private
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function getCategoriesList($id = 0) {
		// Extend the Query object
		global $rs_query;
		
		// Create a list with an opening unordered list tag
		$list = '<ul id="categories-list">';
		
		// Fetch all categories from the database
		$categories = $rs_query->select('terms', array('id', 'name'), array('taxonomy'=>getTaxonomyId('category')), 'name');
		
		// Loop through the categories
		foreach($categories as $category) {
			// Fetch any existing term relationship from the database
			$relationship = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$category['id'], 'post'=>$id));
			
			// Construct the list
			$list .= '<li>'.formTag('input', array('type'=>'checkbox', 'class'=>'checkbox-input', 'name'=>'categories[]', 'value'=>$category['id'], '*'=>($relationship ? 'checked' : ''), 'label'=>array('content'=>'<span>'.$category['name'].'</span>'))).'</li>';
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
		
		if(empty($status)) {
			// Return the count of all posts (excluding ones that are in the trash)
			return $rs_query->select('posts', 'COUNT(*)', array('status'=>array('<>', 'trash'), 'type'=>$type));
		} else {
			// Return the count of all posts by the status
			return $rs_query->select('posts', 'COUNT(*)', array('status'=>$status, 'type'=>$type));
		}
	}
}