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
	 * The currently queried post's publish date.
	 * @since 1.0.1[b]
	 *
	 * @access protected
	 * @var string
	 */
	protected $date;
	
	/**
	 * The currently queried post's modified date.
	 * @since 1.2.9[b]
	 *
	 * @access protected
	 * @var string
	 */
	protected $modified;
	
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
	private $tax_data = array();
	
	/**
	 * Class constructor.
	 * @since 1.0.1[b]
	 *
	 * @access public
	 * @param int $id (optional; default: 0)
	 * @param array $type_data (optional; default: array())
	 */
	public function __construct($id = 0, $type_data = array()) {
		// Extend the Query object and the taxonomies array
		global $rs_query, $taxonomies;
		
		// Create an array of columns to fetch from the database
		$cols = array_keys(get_object_vars($this));
		
		$exclude = array('type_data', 'tax_data');
		$cols = array_diff($cols, $exclude);
		
		if($id !== 0) {
			$post = $rs_query->selectRow('posts', $cols, array('id' => $id));
			
			// Set the class variable values
			foreach($post as $key => $value) $this->$key = $post[$key];
		}
		
		$this->type_data = $type_data;
		
		// Fetch any associated taxonomy data
		if(!empty($this->type_data['taxonomy']) &&
			array_key_exists($this->type_data['taxonomy'], $taxonomies)) {
				$this->tax_data = $taxonomies[$this->type_data['taxonomy']];
		}
	}
	
	/**
	 * Construct a list of all posts in the database.
	 * @since 1.4.0[a]
	 *
	 * @access public
	 */
	public function listPosts(): void {
		// Extend the Query object
		global $rs_query;
		
		// Query vars
		$type = $this->type_data['name'];
		$status = $_GET['status'] ?? 'all';
		$search = $_GET['search'] ?? null;
		$term = $_GET['term'] ?? '';
		$paged = paginate((int)($_GET['paged'] ?? 1));
		?>
		<div class="heading-wrap">
			<h1><?php echo $this->type_data['label']; ?></h1>
			<?php
			// Check whether the user has sufficient privileges to create posts of the current type
			if(userHasPrivilege('can_create_' . str_replace(' ', '_',
				$this->type_data['labels']['name_lowercase']))) {
					
				echo actionLink('create', array(
					'type' => ($type === 'post' ? null : $type),
					'classes' => 'button',
					'caption' => 'Create New'
				));
			}
			
			recordSearch(array(
				'type' => $type,
				'status' => $status
			));
			adminInfo();
			?>
			<hr>
			<?php
			// Check whether any status messages have been returned and display them if so
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success') {
				echo statusMessage('The ' . strtolower($this->type_data['labels']['name_singular']) .
					' was successfully deleted.', true);
			}
			?>
			<ul class="status-nav">
				<?php
				$keys = array('all', 'published', 'draft', 'trash');
				$count = array();
				
				foreach($keys as $key) {
					if($key === 'all') {
						if(!is_null($search) && $key === $status)
							$count[$key] = $this->getPostCount($type, '', $search);
						else
							$count[$key] = $this->getPostCount($type);
					} else {
						if(!is_null($search) && $key === $status)
							$count[$key] = $this->getPostCount($type, $key, $search);
						else
							$count[$key] = $this->getPostCount($type, $key);
					}
				}
				
				foreach($count as $key => $value) {
					?>
					<li>
						<a href="<?php
							echo ADMIN_URI . '?type=' . $type . ($key === 'all' ? '' : '&status=' . $key);
							?>"><?php echo ucfirst($key); ?> <span class="count">(<?php echo $value; ?>)</span></a>
					</li>
					<?php
					if($key !== array_key_last($count)) {
						?> &bull; <?php
					}
				}
				?>
			</ul>
			<?php $paged['count'] = ceil($count[$status] / $paged['per_page']); ?>
			<div class="entry-count status">
				<?php
				if(!empty($term)) {
					$t = str_replace('-', '_', $term);
					$count[$t] = $this->getPostCount($type, '', '', $term);
					
					echo $count[$t] . ' ' . ($count[$t] === 1 ? 'entry' : 'entries');
				} else {
					echo $count[$status] . ' ' . ($count[$status] === 1 ? 'entry' : 'entries');
				}
				?>
			</div>
		</div>
		<table class="data-table has-bulk-select">
			<thead>
				<?php
				if($this->type_data['hierarchical']) {
					$table_header_cols = array(
						tag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox bulk-selector'
						)),
						'Title',
						'Author',
						'Publish Date',
						'Parent',
						'Meta Title',
						'Meta Desc.'
					);
					
					// Insert the comments label into the array if comments are enabled
					if(getSetting('enable_comments') && $this->type_data['comments'])
						array_splice($table_header_cols, 5, 0, 'Comments');
				} else {
					$table_header_cols = array(
						tag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox bulk-selector'
						)),
						'Title',
						'Author',
						'Publish Date',
						'Meta Title',
						'Meta Desc.'
					);
					
					// Insert the comments label into the array if comments are enabled
					if(getSetting('enable_comments') && $this->type_data['comments'])
						array_splice($table_header_cols, 4, 0, 'Comments');
					
					// Insert the taxonomy label into the array if the post type has an associated taxonomy
					if(!empty($this->tax_data))
						array_splice($table_header_cols, 3, 0, $this->tax_data['label']);
				}
				
				echo tableHeaderRow($table_header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$order_by = $type === 'page' ? 'title' : 'date';
				$order = $type === 'page' ? 'ASC' : 'DESC';
				
				if($status === 'all')
					$db_status = array('<>', 'trash');
				else
					$db_status = $status;
					
				if(!empty($term)) {
					$term_id = (int)$rs_query->selectField('terms', 'id', array('slug' => $term));
					$relationships = $rs_query->select('term_relationships', 'post', array('term' => $term_id));
					
					if(count($relationships) > 1) {
						$post_ids = array('IN');
						
						foreach($relationships as $rel)
							$post_ids[] = $rel['post'];
					} elseif(count($relationships) > 0) {
						$post_ids = $relationships[0]['post'];
					} else {
						$post_ids = 0;
					}
					
					// Term results
					$posts = $rs_query->select('posts', '*', array(
						'id' => $post_ids,
						'status' => $db_status,
						'type' => $type
					), $order_by, $order, array($paged['start'], $paged['per_page']));
				} elseif(!is_null($search)) {
					// Search results
					$posts = $rs_query->select('posts', '*', array(
						'title' => array('LIKE', '%' . $search . '%'),
						'status' => $db_status,
						'type' => $type
					), $order_by, $order, array($paged['start'], $paged['per_page']));
				} else {
					// All results
					$posts = $rs_query->select('posts', '*', array(
						'status' => $db_status,
						'type' => $type
					), $order_by, $order, array($paged['start'], $paged['per_page']));
				}
				
				foreach($posts as $post) {
					$meta = $this->getPostMeta($post['id']);
					
					$type_name = str_replace(' ', '_', $this->type_data['labels']['name_lowercase']);
					
					$actions = array(
						// Edit
						userHasPrivilege('can_edit_' . $type_name
						) && $status !== 'trash' ? actionLink('edit', array(
							'caption' => 'Edit',
							'id' => $post['id']
						)) : null,
						// Duplicate
						userHasPrivilege('can_create_' . $type_name
						) && $status !== 'trash' ? actionLink('duplicate', array(
							'caption' => 'Duplicate',
							'id' => $post['id']
						)) : null,
						// Trash/restore
						userHasPrivilege('can_edit_' . $type_name
						) ? ($status === 'trash' ? actionLink('restore', array(
							'caption' => 'Restore',
							'id' => $post['id']
						)) : actionLink('trash', array(
							'caption' => 'Trash',
							'id' => $post['id']
						))) : null,
						// Delete
						$status === 'trash' ? (userHasPrivilege('can_delete_' . $type_name
						) ? actionLink('delete', array(
							'classes' => 'modal-launch delete-item',
							'data_item' => strtolower($this->type_data['labels']['name_singular']),
							'caption' => 'Delete',
							'id' => $post['id']
						)) : null) : (
						// View/preview
						'<a href="' . ($post['status'] === 'published' ? (isHomePage($post['id']) ? '/' :
							getPermalink($post['type'], $post['parent'], $post['slug'])) . '">View' :
							('/?id=' . $post['id'] . '&preview=true') . '">Preview') . '</a>')
					);
					
					// Filter out any empty actions
					$actions = array_filter($actions);
					
					echo tableRow(
						// Bulk select
						tdCell(tag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox',
							'value' => $post['id']
						)), 'bulk-select'),
						// Title
						tdCell((isHomePage($post['id']) ?
							'<i class="fa-solid fa-house-chimney" style="cursor: help;" title="Home Page"></i> ' :
							'') . '<strong>' . $post['title'] . '</strong>' . ($post['status'] !== 'published' &&
							$status === 'all' ? ' &mdash; <em>' . $post['status'] . '</em>' : '') .
							'<div class="actions">' . implode(' &bull; ', $actions) . '</div>', 'title'),
						// Author
						tdCell($this->getAuthor($post['author']), 'author'),
						// Terms (hierarchical post types only)
						!$this->type_data['hierarchical'] && !empty($this->type_data['taxonomy']) ?
							tdCell($this->getTerms($post['id']), 'terms') : '',
						// Publish date
						tdCell(is_null($post['date']) ? '&mdash;' :
							formatDate($post['date'], 'd M Y @ g:i A'), 'publish-date'),
						// Parent (hierarchical post types only)
						$this->type_data['hierarchical'] ? tdCell($this->getParent($post['parent']), 'parent') : '',
						// Comments
						getSetting('enable_comments') && $this->type_data['comments'] ?
							tdCell(($meta['comment_status'] ? $meta['comment_count'] : '&mdash;'), 'comments') : '',
						// Meta title
						tdCell(!empty($meta['title']) ? 'Yes' : 'No', 'meta-title'),
						// Meta description
						tdCell(!empty($meta['description']) ? 'Yes' : 'No', 'meta-description')
					);
				}
				
				if(empty($posts)) {
					echo tableRow(tdCell('There are no ' . $this->type_data['labels']['name_lowercase'] .
						' to display.', '', count($table_header_cols)));
				}
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($table_header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Bulk actions
		if(!empty($posts)) $this->bulkActions();
		
		// Set up page navigation
		echo pagerNav($paged['current'], $paged['count']);
		
        include_once PATH . ADMIN . INC . '/modal-delete.php';
	}
	
	/**
	 * Create a post.
	 * @since 1.4.1[a]
	 *
	 * @access public
	 */
	public function createPost(): void {
		$type = $this->type_data['name'];
		
		// Validate the form data and return any messages
		$message = isset($_POST['submit']) ? $this->validateData($_POST, $_GET['action']) : '';
		?>
		<div class="heading-wrap">
			<h1><?php echo $this->type_data['labels']['create_item']; ?></h1>
			<?php echo $message; ?>
		</div>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<div class="content">
					<?php
					// Type (hidden)
					echo formTag('input', array(
						'type' => 'hidden',
						'name' => 'type',
						'value' => $type
					));
					
					// Title
					echo formTag('input', array(
						'id' => 'title-field',
						'class' => 'text-input required invalid init',
						'name' => 'title',
						'value' => ($_POST['title'] ?? ''),
						'placeholder' => $this->type_data['labels']['name_singular'] . ' title'
					));
					?>
					<div class="permalink">
						<?php
						// Permalink
						echo formTag('label', array(
							'for' => 'slug',
							'content' => '<strong>Permalink:</strong> ' . getSetting('site_url') .
								getPermalink($this->type_data['name'])
						));
						echo formTag('input', array(
							'id' => 'slug-field',
							'class' => 'text-input required invalid init',
							'name' => 'slug',
							'value' => ($_POST['slug'] ?? '')
						));
						echo formTag('span', array(
							'content' => '/'
						));
						?>
					</div>
					<?php
					// Insert media button
					echo formTag('input', array(
						'type' => 'button',
						'class' => 'button-input button modal-launch',
						'value' => 'Insert Media',
						'data-type' => 'all',
						'data-insert' => 'true'
					));
					
					// Content
					echo formTag('textarea', array(
						'class' => 'textarea-input',
						'name' => 'content',
						'rows' => 25,
						'content' => htmlspecialchars(($_POST['content'] ?? ''))
					));
					?>
				</div>
				<div class="sidebar">
					<div class="block">
						<h2>Publish</h2>
						<div class="row">
							<?php
							// Status
							echo formTag('label', array('for' => 'status', 'content' => 'Status'));
							echo formTag('select', array(
								'class' => 'select-input',
								'name' => 'status',
								'content' => tag('option', array(
									'value' => 'draft',
									'content' => 'Draft'
								)) . tag('option', array(
									'value' => 'published',
									'content' => 'Published'
								))
							));
							?>
						</div>
						<div class="row">
							<?php
							// Author
							echo formTag('label', array('for' => 'author', 'content' => 'Author'));
							echo formTag('select', array(
								'class' => 'select-input',
								'name' => 'author',
								'content' => $this->getAuthorList()
							));
							?>
						</div>
						<div class="row">
							<?php
							// Publish date
							echo formTag('label', array('for' => 'date', 'content' => 'Publish on'));
							echo formTag('br');
							echo formTag('input', array(
								'type' => 'date',
								'class' => 'date-input',
								'name' => 'date[]'
							));
							echo formTag('input', array(
								'type' => 'time',
								'class' => 'date-input',
								'name' => 'date[]'
							));
							?>
						</div>
						<div id="submit" class="row">
							<?php
							// Submit button
							echo formTag('input', array(
								'type' => 'submit',
								'class' => 'submit-input button',
								'name' => 'submit',
								'value' => 'Publish'
							));
							?>
						</div>
					</div>
					<?php
					if($this->type_data['hierarchical']) {
						?>
						<div class="block">
							<h2>Attributes</h2>
							<div class="row">
								<?php
								// Parent
								echo formTag('label', array('for' => 'parent', 'content' => 'Parent'));
								echo formTag('select', array(
									'class' => 'select-input',
									'name' => 'parent',
									'content' => tag('option', array(
										'value' => 0,
										'content' => '(none)'
									)) . $this->getParentList($type)
								));
								?>
							</div>
							<div class="row">
								<?php
								// Template
								echo formTag('label', array(
									'for' => 'template',
									'content' => 'Template'
								));
								echo formTag('select', array(
									'class' => 'select-input',
									'name' => 'template',
									'content' => tag('option', array(
										'value' => 'default',
										'content' => 'Default'
									)) . $this->getTemplateList()
								));
								?>
							</div>
						</div>
						<?php
					} else {
						if(!empty($this->tax_data)) {
							?>
							<div class="block">
								<h2><?php echo $this->tax_data['label']; ?></h2>
								<div class="row">
									<?php
									// Terms list
									echo $this->getTermsList();
									?>
								</div>
							</div>
							<?php
						}
					}
					
					if(getSetting('enable_comments') && $this->type_data['comments']) {
						?>
						<div class="block">
							<h2>Comments</h2>
							<div class="row">
								<?php
								// Check whether comments are enabled for this post
								$comments = isset($_POST['comments']) || (!isset($_POST['comments']) &&
									$this->type_data['comments']) ? 'checked' : '';
								
								// Enable comments
								echo formTag('input', array(
									'type' => 'checkbox',
									'class' => 'checkbox-input',
									'name' => 'comments',
									'value' => (!empty($comments) ? 1 : 0),
									'checked' => $comments,
									'label' => array(
										'class' => 'checkbox-label',
										'content' => tag('span', array(
											'content' => 'Enable comments'
										))
									)
								));
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
								// Featured image thumbnail
								echo formTag('img', array('src' => '//:0', 'data-field' => 'thumb'));
								
								// Remove image button
								echo formTag('span', array(
									'class' => 'image-remove',
									'title' => 'Remove',
									'content' => tag('i', array('class' => 'fa-solid fa-xmark'))
								));
								?>
							</div>
							<?php
							// Featured image (hidden)
							echo formTag('input', array(
								'type' => 'hidden',
								'name' => 'feat_image',
								'value' => ($_POST['feat_image'] ?? 0),
								'data-field' => 'id'
							));
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
							// Meta title
							echo formTag('label', array('for' => 'meta_title', 'content' => 'Title'));
							echo formTag('br');
							echo formTag('input', array(
								'class' => 'text-input',
								'name' => 'meta_title',
								'value' => ($_POST['meta_title'] ?? '')
							));
							?>
						</div>
						<div class="row">
							<?php
							// Meta description
							echo formTag('label', array(
								'for' => 'meta_description',
								'content' => 'Description'
							));
							echo formTag('br');
							echo formTag('textarea', array(
								'class' => 'textarea-input',
								'name' => 'meta_description',
								'cols' => 30,
								'rows' => 4,
								'content' => ($_POST['meta_description'] ?? '')
							));
							?>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
		include_once PATH . ADMIN . INC . '/modal-upload.php';
	}
	
	/**
	 * Edit a post.
	 * @since 1.4.9[a]
	 *
	 * @access public
	 */
	public function editPost(): void {
		// Extend the Query object
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			if(empty($this->type)) {
				redirect(ADMIN_URI);
			} elseif($this->type === 'media') {
				redirect('media.php?id=' . $this->id . '&action=edit');
			} elseif($this->type === 'widget') {
				redirect('widgets.php?id=' . $this->id . '&action=edit');
			} else {
				if($this->isTrash($this->id)) {
					redirect(ADMIN_URI . ($this->type !== 'post' ? '?type=' . $this->type . '&' : '?') .
						'status=trash');
				} else {
					// Validate the form data and return any messages
					$message = isset($_POST['submit']) ? $this->validateData($_POST, $_GET['action'], $this->id) : '';
					
					$meta = $this->getPostMeta($this->id);
					
					if(!empty($meta['feat_image']))
						list($width, $height) = getimagesize(PATH . getMediaSrc($meta['feat_image']));
					?>
					<div class="heading-wrap">
						<h1><?php echo $this->type_data['labels']['edit_item']; ?></h1>
						<?php echo $message; ?>
					</div>
					<div class="data-form-wrap clear">
						<form class="data-form" action="" method="post" autocomplete="off">
							<div class="content">
								<?php
								// Title
								echo formTag('input', array(
									'id' => 'title-field',
									'class' => 'text-input required invalid init',
									'name' => 'title',
									'value' => $this->title,
									'placeholder' => $this->type_data['labels']['name_singular'] .
										' title'
								));
								?>
								<div class="permalink">
									<?php
									// Permalink
									echo formTag('label', array(
										'for' => 'slug',
										'content' => '<strong>Permalink:</strong> ' .
											getSetting('site_url') .
											getPermalink($this->type, $this->parent)
									));
									echo formTag('input', array(
										'id' => 'slug-field',
										'class' => 'text-input required invalid init',
										'name' => 'slug',
										'value' => $this->slug
									));
									echo formTag('span', array(
										'content' => '/'
									));
									?>
								</div>
								<?php
								// Insert media button
								echo formTag('input', array(
									'type' => 'button',
									'class' => 'button-input button modal-launch',
									'value' => 'Insert Media',
									'data-type' => 'all',
									'data-insert' => 'true'
								));
								
								// Content
								echo formTag('textarea', array(
									'class' => 'textarea-input',
									'name' => 'content',
									'rows' => 25,
									'content' => htmlspecialchars($this->content)
								));
								?>
							</div>
							<div class="sidebar">
								<div class="block">
									<h2>Publish</h2>
									<div class="row">
										<?php
										// Status
										echo formTag('label', array(
											'for' => 'status',
											'content' => 'Status'
										));
										echo formTag('select', array(
											'class' => 'select-input',
											'name' => 'status',
											'content' => tag('option', array(
												'value' => 'draft',
												'selected' => ($this->status === 'draft' ? 1 : 0),
												'content' => 'Draft'
											)) . tag('option', array(
												'value' => 'published',
												'selected' => ($this->status === 'published' ? 1 : 0),
												'content' => 'Published'
											))
										));
										?>
									</div>
									<div class="row">
										<?php
										// Author
										echo formTag('label', array(
											'for' => 'author',
											'content' => 'Author'
										));
										echo formTag('select', array(
											'class' => 'select-input',
											'name' => 'author',
											'content' => $this->getAuthorList($this->author)
										));
										?>
									</div>
									<div class="row">
										<?php
										// Publish date
										echo formTag('label', array(
											'for' => 'date',
											'content' => 'Published on'
										));
										echo formTag('br');
										echo formTag('input', array(
											'type' => 'date',
											'class' => 'date-input',
											'name' => 'date[]',
											'value' => (
												!is_null($this->date) ?
												formatDate($this->date, 'Y-m-d') :
												formatDate($this->modified, 'Y-m-d')
											)
										));
										echo formTag('input', array(
											'type' => 'time',
											'class' => 'date-input',
											'name' => 'date[]',
											'value' => (
												!is_null($this->date) ?
												formatDate($this->date, 'H:i') :
												formatDate($this->modified, 'H:i'))
										));
										?>
									</div>
									<div id="submit" class="row">
										<?php
										// View/preview link
										echo $this->status === 'published' ? '<a href="' .
											(isHomePage($this->id) ? '/' : getPermalink(
												$this->type,
												$this->parent,
												$this->slug
											)) . '" target="_blank" rel="noreferrer noopener">View</a>' :
											'<a href="/?id=' . $this->id .
											'&preview=true" target="_blank" rel="noreferrer noopener">Preview</a>';
										
										// Submit button
										echo formTag('input', array(
											'type' => 'submit',
											'class' => 'submit-input button',
											'name' => 'submit',
											'value' => 'Update'
										));
										?>
									</div>
								</div>
								<?php
								if($this->type_data['hierarchical']) {
									?>
									<div class="block">
										<h2>Attributes</h2>
										<div class="row">
											<?php
											// Parent
											echo formTag('label', array(
												'for' => 'parent',
												'content' => 'Parent'
											));
											echo formTag('select', array(
												'class' => 'select-input',
												'name' => 'parent',
												'content' => tag('option', array(
													'value' => 0,
													'content' => '(none)'
												)) .
												$this->getParentList(
													$this->type,
													$this->parent,
													$this->id
												)
											));
											?>
										</div>
										<div class="row">
											<?php
											// Template
											echo formTag('label', array(
												'for' => 'template',
												'content' => 'Template'
											));
											echo formTag('select', array(
												'class' => 'select-input',
												'name' => 'template',
												'content' => tag('option', array(
													'value' => 'default',
													'content' => 'Default'
												)). $this->getTemplateList($this->id)
											));
											?>
										</div>
									</div>
									<?php
								} else {
									if(!empty($this->tax_data)) {
										?>
										<div class="block">
											<h2><?php echo $this->tax_data['label']; ?></h2>
											<div class="row">
												<?php
												// Terms list
												echo $this->getTermsList($this->id);
												?>
											</div>
										</div>
										<?php
									}
								}
								
								if(getSetting('enable_comments') && $this->type_data['comments']) {
									?>
									<div class="block">
										<h2>Comments</h2>
										<div class="row">
											<?php
											// Enable comments
											echo formTag('input', array(
												'type' => 'checkbox',
												'class' => 'checkbox-input',
												'name' => 'comments',
												'value' => $meta['comment_status'],
												'checked' => $meta['comment_status'],
												'label' => array(
													'class' => 'checkbox-label',
													'content' => '<span>Enable comments</span>'
												)
											));
											?>
										</div>
									</div>
									<?php
								}
								?>
								<div class="block">
									<h2>Featured Image</h2>
									<div class="row">
										<div class="image-wrap<?php echo !empty($meta['feat_image']) ?
											' visible' : ''; ?>" style="width: <?php echo $width ?? 0; ?>px;">
											<?php
											// Featured image thumbnail
											echo getMedia($meta['feat_image'], array(
												'data-field' => 'thumb'
											));
											
											// Remove image button
											echo formTag('span', array(
												'class' => 'image-remove',
												'title' => 'Remove',
												'content' => tag('i', array(
													'class' => 'fa-solid fa-xmark'
												))
											));
											?>
										</div>
										<?php
										// Featured image (hidden)
										echo formTag('input', array(
											'type' => 'hidden',
											'name' => 'feat_image',
											'value' => $meta['feat_image'],
											'data-field' => 'id'
										));
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
										// Meta title
										echo formTag('label', array(
											'for' => 'meta_title',
											'content' => 'Title'
										));
										echo formTag('br');
										echo formTag('input', array(
											'class' => 'text-input',
											'name' => 'meta_title',
											'value' => ($meta['title'] ?? '')
										));
										?>
									</div>
									<div class="row">
										<?php
										// Meta description
										echo formTag('label', array(
											'for' => 'meta_description',
											'content' => 'Description'
										));
										echo formTag('br');
										echo formTag('textarea', array(
											'class' => 'textarea-input',
											'name' => 'meta_description',
											'cols' => 30,
											'rows' => 4,
											'content' => ($meta['description'] ?? '')
										));
										?>
									</div>
								</div>
							</div>
						</form>
					</div>
					<?php
					include_once PATH . ADMIN . INC . '/modal-upload.php';
				}
			}
		}
	}
	
	/**
	 * Duplicate a post.
	 * @since 1.3.7[b]
	 *
	 * @access public
	 */
	public function duplicatePost(): void {
		// Extend the Query object
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			if(empty($this->type)) {
				redirect(ADMIN_URI);
			} elseif($this->type === 'media') {
				redirect('media.php?id=' . $this->id . '&action=edit');
			} elseif($this->type === 'widget') {
				redirect('widgets.php?id=' . $this->id . '&action=edit');
			} else {
				if($this->isTrash($this->id)) {
					redirect(ADMIN_URI . ($this->type !== 'post' ? '?type=' . $this->type . '&' : '?') .
						'status=trash');
				} else {
					// Validate the form data and return any messages
					$message = isset($_POST['submit']) ? $this->validateData($_POST, $_GET['action'], $this->id) : '';
					?>
					<div class="heading-wrap">
						<h1><?php echo $this->type_data['labels']['duplicate_item']; ?></h1>
						<?php echo $message; ?>
					</div>
					<div class="data-form-wrap clear">
						<form class="data-form" action="" method="post" autocomplete="off">
							<table class="form-table">
								<?php
								// Original post
								echo formRow('Original Post', array(
									'tag' => 'input',
									'class' => 'text-input disabled',
									'name' => 'original_post',
									'value' => $this->title,
									'disabled' => 1
								));
								
								$new_title = 'Copy of ' . $this->title;
								
								// New post title
								echo formRow(array('New Title', true), array(
									'tag' => 'input',
									'id' => 'title-field',
									'class' => 'text-input required invalid init',
									'name' => 'title',
									'value' => $new_title
								));
								
								// New post slug
								echo formRow(array('New Slug', true), array(
									'tag' => 'input',
									'id' => 'slug-field',
									'class' => 'text-input required invalid init',
									'name' => 'slug',
									'value' => sanitize(str_replace(' ', '-', $new_title))
								));
								
								// Separator
								echo formRow('', array('tag' => 'hr', 'class' => 'separator'));
								
								// Submit button
								echo formRow('', array(
									'tag' => 'input',
									'type' => 'submit',
									'class' => 'submit-input button',
									'name' => 'submit',
									'value' => 'Duplicate ' . $this->type_data['labels']['name_singular']
								));
								?>
							</table>
						</form>
					</div>
					<?php
				}
			}
		}
	}
	
	/**
	 * Update a post's status.
	 * @since 1.2.9[b]
	 *
	 * @access public
	 * @param string $status
	 * @param int $id (optional; default: 0)
	 */
	public function updatePostStatus($status, $id = 0): void {
		// Extend the Query object
		global $rs_query;
		
		if($id !== 0) $this->id = $id;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			$type = $rs_query->selectField('posts', 'type', array('id' => $this->id));
			
			if($type === $this->type_data['name']) {
				if($status === 'published') {
					$db_status = $rs_query->selectField('posts', 'status', array('id' => $this->id));
					
					if($db_status !== $status) {
						$rs_query->update('posts', array(
							'date' => 'NOW()',
							'status' => $status
						), array('id' => $this->id));
					} else {
						$rs_query->update('posts', array('status' => $status), array('id' => $this->id));
					}
				} else {
					$rs_query->update('posts', array(
						'date' => null,
						'status' => $status
					), array('id' => $this->id));
				}
			}
		}
	}
	
	/**
	 * Send a post to the trash.
	 * @since 1.4.6[a]
	 *
	 * @access public
	 */
	public function trashPost(): void {
		$this->updatePostStatus('trash');
		
		redirect($this->type_data['menu_link']);
	}
	
	/**
	 * Restore a post from the trash.
	 * @since 1.4.6[a]
	 *
	 * @access public
	 */
	public function restorePost(): void {
		$this->updatePostStatus('draft');
		
		redirect($this->type_data['menu_link'] . ($this->type !== 'post' ? '&' : '?') . 'status=trash');
	}
	
	/**
	 * Delete a post.
	 * @since 1.4.7[a]
	 *
	 * @access public
	 */
	public function deletePost(): void {
		// Extend the Query object
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0) {
			redirect(ADMIN_URI);
		} else {
			$rs_query->delete('posts', array('id' => $this->id));
			$rs_query->delete('postmeta', array('post' => $this->id));
			$relationships = $rs_query->select('term_relationships', '*', array('post' => $this->id));
			
			foreach($relationships as $relationship) {
				$rs_query->delete('term_relationships', array('id' => $relationship['id']));
				$count = $rs_query->selectRow('term_relationships', 'COUNT(*)', array(
					'term' => $relationship['term']
				));
				$rs_query->update('terms',
					array('count' => $count),
					array('id' => $relationship['term'])
				);
			}
			
			$rs_query->delete('comments', array('post' => $this->id));
			
			$menu_items = $rs_query->select('postmeta', 'post', array(
				'_key' => 'post_link',
				'value' => $this->id
			));
			
			// Set any menu items associated with the post to invalid
			foreach($menu_items as $menu_item) {
				$rs_query->update('posts',
					array('status' => 'invalid'),
					array('id' => $menu_item['post'])
				);
			}
			
			redirect($this->type_data['menu_link'] . ($this->type !== 'post' ? '&' : '?') .
				'status=trash&exit_status=success');
		}
	}
	
	/**
	 * Validate the form data.
	 * @since 1.4.7[a]
	 *
	 * @access private
	 * @param array $data
	 * @param string $action
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function validateData($data, $action, $id = 0): string {
		// Extend the Query object
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['title']) || empty($data['slug']))
			return statusMessage('R');
		
		$slug = sanitize($data['slug']);
		
		if($this->slugExists($slug, $id))
			$slug = getUniquePostSlug($slug);
		
		if($action === 'duplicate') {
			// Fetch the old post data for duplication
			$old_post = $rs_query->selectRow('posts', '*', array('id' => $id));
			$old_postmeta = $rs_query->select('postmeta', '*', array('post' => $id));
			$old_term_relationships = $rs_query->select('term_relationships', '*', array('post' => $id));
		} else {
			if($data['status'] !== 'draft' && $data['status'] !== 'published')
				$data['status'] = 'draft';
			
			$postmeta = array(
				'title' => $data['meta_title'],
				'description' => $data['meta_description'],
				'feat_image' => $data['feat_image']
			);
			
			if(isset($data['template'])) $postmeta['template'] = $data['template'];
			
			// Check whether comments are enabled for the post type
			if($this->type_data['comments']) {
				// Check whether comments are enabled for the specified post
				$postmeta['comment_status'] = isset($data['comments']) ? 1 : 0;
			}
		}
		
		switch($action) {
			case 'create':
				// Check whether a date has been provided and is valid
				if(!empty($data['date'][0]) && !empty($data['date'][1]) && $data['date'][0] >= '1000-01-01')
					$data['date'] = implode(' ', $data['date']);
				else
					$data['date'] = 'NOW()';
				
				if(!$this->type_data['hierarchical']) $data['parent'] = 0;
				
				$insert_id = $rs_query->insert('posts', array(
					'title' => $data['title'],
					'author' => $data['author'],
					'date' => ($data['status'] === 'published' ? $data['date'] : null),
					'modified' => $data['date'],
					'content' => $data['content'],
					'status' => $data['status'],
					'slug' => $slug,
					'parent' => $data['parent'],
					'type' => $data['type']
				));
				
				if(isset($postmeta['comment_status'])) $postmeta['comment_count'] = 0;
				
				foreach($postmeta as $key => $value) {
					$rs_query->insert('postmeta', array(
						'post' => $insert_id,
						'_key' => $key,
						'value' => $value
					));
				}
				
				if(!empty($data['terms'])) {
					// Create new relationships
					foreach($data['terms'] as $term) {
						$rs_query->insert('term_relationships', array(
							'term' => $term,
							'post' => $insert_id
						));
						$count = $rs_query->selectRow('term_relationships', 'COUNT(*)', array(
							'term' => $term
						));
						$rs_query->update('terms', array('count' => $count), array('id' => $term));
					}
				}
				
				redirect(ADMIN_URI . '?id=' . $insert_id . '&action=edit');
				break;
			case 'edit':
				// Check whether a date has been provided and is valid
				if(!empty($data['date'][0]) && !empty($data['date'][1]) && $data['date'][0] >= '1000-01-01')
					$data['date'] = implode(' ', $data['date']);
				else
					$data['date'] = null;
				
				if(!$this->type_data['hierarchical']) $data['parent'] = 0;
				
				$rs_query->update('posts', array(
					'title' => $data['title'],
					'author' => $data['author'],
					'date' => ($data['status'] === 'published' ? $data['date'] : null),
					'modified' => 'NOW()',
					'content' => $data['content'],
					'status' => $data['status'],
					'slug' => $slug,
					'parent' => $data['parent']
				), array('id' => $id));
				
				foreach($postmeta as $key => $value) {
					$rs_query->update('postmeta', array('value' => $value), array(
						'post' => $id,
						'_key' => $key
					));
				}
				
				$relationships = $rs_query->select('term_relationships', '*', array('post' => $id));
				
				foreach($relationships as $relationship) {
					// Delete any unused relationships
					if(empty($data['terms']) || !in_array($relationship['term'], $data['terms'], true)) {
						$rs_query->delete('term_relationships', array('id' => $relationship['id']));
						$count = $rs_query->selectRow('term_relationships', 'COUNT(*)', array(
							'term' => $relationship['term']
						));
						$rs_query->update('terms',
							array('count' => $count),
							array('id' => $relationship['term'])
						);
					}
				}
				
				if(!empty($data['terms'])) {
					foreach($data['terms'] as $term) {
						$relationship = $rs_query->selectRow('term_relationships', 'COUNT(*)', array(
							'term' => $term,
							'post' => $id
						));
						
						// Skip existing relationships, otherwise create a new one
						if($relationship) {
							continue;
						} else {
							$rs_query->insert('term_relationships', array('term' => $term, 'post' => $id));
							$count = $rs_query->select('term_relationships', 'COUNT(*)', array(
								'term' => $term
							));
							$rs_query->update('terms', array('count' => $count), array('id' => $term));
						}
					}
				}
				
				// Update the class variables
				foreach($data as $key => $value) $this->$key = $value;
				
				return statusMessage($this->type_data['labels']['name_singular'] . ' updated! <a href="' . ADMIN_URI .
					($this->type === 'post' ? '' : '?type=' . $this->type) . '">Return to list</a>?', true);
				break;
			case 'duplicate':
				$insert_id = $rs_query->insert('posts', array(
					'title' => $data['title'],
					'author' => $old_post['author'],
					'date' => null,
					'modified' => $old_post['modified'],
					'content' => $old_post['content'],
					'status' => 'draft', // set new post to a draft so the user has a chance to make changes before it goes live
					'slug' => $slug,
					'parent' => $old_post['parent'],
					'type' => $old_post['type']
				));
				
				foreach($old_postmeta as $meta) {
					$rs_query->insert('postmeta', array(
						'post' => $insert_id,
						'_key' => $meta['_key'],
						'value' => $meta['value']
					));
				}
				
				if(!empty($old_term_relationships)) {
					foreach($old_term_relationships as $relationship) {
						$rs_query->insert('term_relationships', array(
							'term' => $relationship['term'],
							'post' => $insert_id
						));
						$count = $rs_query->selectRow('term_relationships', 'COUNT(*)', array(
							'term' => $relationship['term']
						));
						$rs_query->update('terms', array('count' => $count), array('id' => $relationship['term']));
					}
				}
				
				redirect(ADMIN_URI . '?id=' . $insert_id . '&action=edit&exit_status=dup_success');
				break;
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
	protected function slugExists($slug, $id = 0): bool {
		// Extend the Query object
		global $rs_query;
		
		if($id === 0) {
			return $rs_query->selectRow('posts', 'COUNT(slug)', array('slug' => $slug)) > 0;
		} else {
			return $rs_query->selectRow('posts', 'COUNT(slug)', array(
				'slug' => $slug,
				'id' => array('<>', $id)
			)) > 0;
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
	private function isTrash($id): bool {
		// Extend the Query object
		global $rs_query;
		
		return $rs_query->selectField('posts', 'status', array('id' => $id)) === 'trash';
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
	private function isDescendant($id, $ancestor): bool {
		// Extend the Query object
		global $rs_query;
		
		do {
			$parent = $rs_query->selectField('posts', 'parent', array('id' => $id));
			$id = (int)$parent;
			
			if($id === $ancestor) return true;
		} while($id !== 0);
		
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
	protected function getPostMeta($id): array {
		// Extend the Query object
		global $rs_query;
		
		$postmeta = $rs_query->select('postmeta', array('_key', 'value'), array('post' => $id));
		$meta = array();
		
		foreach($postmeta as $metadata) {
			$values = array_values($metadata);
			
			// Assign the metadata to the meta array
			for($i = 0; $i < count($metadata); $i += 2)
				$meta[$values[$i]] = $values[$i + 1];
		}
		
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
	protected function getAuthor($id): string {
		// Extend the Query object
		global $rs_query;
		
		return $rs_query->selectField('users', 'username', array('id' => $id));
	}
	
	/**
	 * Construct a list of authors.
	 * @since 1.4.4[a]
	 *
	 * @access private
	 * @param int $id (optional; default: 0)
	 * @return string
	 */
	private function getAuthorList($id = 0): string {
		// Extend the Query object
		global $rs_query;
		
		$list = '';
		$authors = $rs_query->select('users', array('id', 'username'), '', 'username');
		
		foreach($authors as $author) {
			$list .= '<option value="' . $author['id'] . '"' . ($author['id'] === (int)$id ? ' selected' : '') .
				'>' . $author['username'] . '</option>';
		}
		
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
	private function getTerms($id): string {
		// Extend the Query object
		global $rs_query;
		
		$terms = array();
		$relationships = $rs_query->select('term_relationships', 'term', array('post' => $id));
		
		foreach($relationships as $relationship) {
			$term = $rs_query->selectRow('terms', '*', array(
				'id' => $relationship['term'],
				'taxonomy' => getTaxonomyId($this->tax_data['name'])
			));
			
			$terms[] = '<a href="' . getPermalink($this->tax_data['name'], $term['parent'], $term['slug']) .
				'">' . $term['name'] . '</a>';
		}
		
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
	private function getTermsList($id = 0): string {
		// Extend the Query object
		global $rs_query;
		
		$list = '<ul id="terms-list">';
		$terms = $rs_query->select('terms', array('id', 'name', 'slug'), array(
			'taxonomy' => getTaxonomyId($this->tax_data['name'])
		), 'name');
		
		foreach($terms as $term) {
			$relationship = $rs_query->selectRow('term_relationships', 'COUNT(*)', array(
				'term' => $term['id'],
				'post' => $id
			));
			
			$list .= '<li>' . tag('input', array(
				'type' => 'checkbox',
				'class' => 'checkbox-input',
				'name' => 'terms[]',
				'value' => $term['id'],
				'checked' => ($relationship || ($id === 0 &&
					$term['slug'] === $this->tax_data['default_term']['slug'])
				),
				'label' => array(
					'class' => 'checkbox-label',
					'content' => tag('span', array(
						'content' => $term['name']
					))
				)
			)) . '</li>';
			//echo $relationship;
		}
		
		$list .= '</ul>';
		
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
	private function getParent($id): string {
		// Extend the Query object
		global $rs_query;
		
		$parent = $rs_query->selectField('posts', 'title', array('id' => $id));
		
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
	private function getParentList($type, $parent = 0, $id = 0): string {
		// Extend the Query object
		global $rs_query;
		
		$list = '';
		$posts = $rs_query->select('posts', array('id', 'title'), array(
			'status' => array('<>', 'trash'),
			'type' => $type
		));
		
		foreach($posts as $post) {
			if($id !== 0) {
				// Skip the current post
				if($post['id'] === $id) continue;
				
				// Skip all descendant posts
				if($this->isDescendant($post['id'], $id)) continue;
			}
			
			$list .= tag('option', array(
				'value' => $post['id'],
				'selected' => ($post['id'] === $parent),
				'content' => $post['title']
			));
		}
		
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
	private function getTemplateList($id = 0): string {
		// Extend the Query object
		global $rs_query;
		
		$templates_path = trailingSlash(PATH . THEMES) . getSetting('theme') . '/templates';
		
		if(file_exists($templates_path)) {
			// Fetch all templates in the directory
			$templates = array_diff(scandir($templates_path), array('.', '..'));
			
			$current = $rs_query->selectField('postmeta', 'value', array('post' => $id, '_key' => 'template'));
			
			foreach($templates as $template) {
				$list[] = tag('option', array(
					'value' => $template,
					'selected' => (isset($current) && $current === $template),
					'content' => ucwords(substr(
						str_replace('-', ' ', $template), 0,
						strpos($template, '.')
					))
				));
			}
			
			$list = implode('', $list);
		}
		
		return $list ?? '';
	}
	
	/**
	 * Fetch the post count based on a specific status or term.
	 * @since 1.4.0[a]
	 *
	 * @access private
	 * @param string $type
	 * @param string $status (optional; default: '')
	 * @param string $search (optional; default: '')
	 * @param string $term (optional; default: '')
	 * @return int
	 */
	private function getPostCount($type, $status = '', $search = '', $term = ''): int {
		// Extend the Query object
		global $rs_query;
		
		if(empty($status))
			$db_status = array('<>', 'trash');
		else
			$db_status = $status;
		
		if(!empty($term)) {
			$term_id = (int)$rs_query->selectField('terms', 'id', array('slug' => $term));
			$relationships = $rs_query->select('term_relationships', 'post', array('term' => $term_id));
			
			if(count($relationships) > 1) {
				$post_ids = array('IN');
				
				foreach($relationships as $rel)
					$post_ids[] = $rel['post'];
			} elseif(count($relationships) > 0) {
				$post_ids = $relationships[0]['post'];
			} else {
				$post_ids = 0;
			}
			
			return $rs_query->select('posts', 'COUNT(*)', array(
				'id' => $post_ids,
				'status' => $db_status,
				'type' => $type
			));
		} elseif(!empty($search)) {
			return $rs_query->select('posts', 'COUNT(*)', array(
				'title' => array('LIKE', '%' . $search . '%'),
				'status' => $db_status,
				'type' => $type
			));
		} else {
			return $rs_query->select('posts', 'COUNT(*)', array(
				'status' => $db_status,
				'type' => $type
			));
		}
	}
	
	/**
	 * Construct bulk actions.
	 * @since 1.2.9[b]
	 *
	 * @access private
	 */
	private function bulkActions(): void {
		?>
		<div class="bulk-actions">
			<?php
			$type_name = str_replace(' ', '_', $this->type_data['labels']['name_lowercase']);
			
			if(userHasPrivilege('can_edit_' . $type_name)) {
				echo formTag('select', array(
					'class' => 'actions',
					'content' => tag('option', array(
						'value' => 'published',
						'content' => 'Publish'
					)) . tag('option', array(
						'value' => 'draft',
						'content' => 'Draft'
					)) . tag('option', array(
						'value' => 'trash',
						'content' => 'Trash'
					))
				));
				
				// Update status
				button(array(
					'class' => 'bulk-update',
					'title' => 'Bulk status update',
					'label' => 'Update'
				));
			}
			
			if(userHasPrivilege('can_delete_' . $type_name)) {
				// Delete
				button(array(
					'class' => 'bulk-delete',
					'title' => 'Bulk delete',
					'label' => 'Delete'
				));
			}
			?>
		</div>
		<?php
	}
}