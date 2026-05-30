<?php
/**
 * Admin class used to implement the Post object.
 * Posts are the basis of the front end of the website. Currently, there are two content post types: `post` (default, used for blog posts) and `page` (used for content pages).
 * Posts can be created, modified, and deleted.
 * @since 1.4.0-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_post
 *
 * ## VARIABLES [14] ##
 * - protected int $id
 * - protected string $title
 * - protected int $author
 * - protected string $created
 * - protected string $modified
 * - protected string $content
 * - protected string $status
 * - protected string $slug
 * - protected int $parent
 * - protected string $type
 * - private array $type_data
 * - private array $tax_data
 * - protected string $action
 * - protected array $paged
 *
 * ## METHODS [28] ##
 * - public __construct(int $id, string $action, array $type_data)
 * { LISTS, FORMS, & ACTIONS [8] }
 * - public listRecords(): void
 * - public createRecord(): void
 * - public editRecord(): void
 * - public duplicatePost(): void
 * - public updatePostStatus(string $status, int $id): void
 * - public trashPost(): void
 * - public restorePost(): void
 * - public deleteRecord(): void
 * { VALIDATION [1] }
 * - private validateSubmission(array $data): string
 * { MISCELLANEOUS [18] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private bulkActions(): void
 * - protected slugExists(string $slug): bool
 * - private isTrash(int $id): bool
 * - private isDescendant(int $id, int $ancestor): bool
 * - protected getPostMeta(int $id): array
 * - private getStatusList(): string
 * - protected getAuthor(int $id): string
 * - private getAuthorList(int $id): string
 * - private getTerms(string $taxonomy, int $id): string
 * - private getTermsList(string $taxonomy, int $id): string
 * - private getParent(int $id): string
 * - private getParentList(string $type, int $parent, int $id): string
 * - private getTemplateList(int $id): string
 * - private getResults(string $status, ?string $search, string $term, bool $all): array
 * - private getEntryCount(string $status, ?string $search, string $term): int
 * - private getActionLinks(array $post): string
 */
namespace Admin;

class Post implements AdminInterface {
	/**
	 * The currently queried post's id.
	 * @since 1.0.1-beta
	 *
	 * @access protected
	 * @var int
	 */
	protected $id;
	
	/**
	 * The currently queried post's title.
	 * @since 1.0.1-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $title;
	
	/**
	 * The currently queried post's author.
	 * @since 1.0.1-beta
	 *
	 * @access protected
	 * @var int
	 */
	protected $author;
	
	/**
	 * The currently queried post's creation date (usually the publish date).
	 * @since 1.0.1-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $created;
	
	/**
	 * The currently queried post's modified date.
	 * @since 1.2.9-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $modified;
	
	/**
	 * The currently queried post's content.
	 * @since 1.0.1-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $content;
	
	/**
	 * The currently queried post's status.
	 * @since 1.0.1-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $status;
	
	/**
	 * The currently queried post's slug.
	 * @since 1.0.1-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $slug;
	
	/**
	 * The currently queried post's parent.
	 * @since 1.0.1-beta
	 *
	 * @access protected
	 * @var int
	 */
	protected $parent;
	
	/**
	 * The currently queried post's type.
	 * @since 1.0.1-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $type;
	
	/**
	 * The currently queried post's type data.
	 * @since 1.0.1-beta
	 *
	 * @access private
	 * @var array
	 */
	private $type_data = array();
	
	/**
	 * The currently queried post's taxonomy data.
	 * @since 1.0.6-beta
	 *
	 * @access private
	 * @var array
	 */
	private $tax_data = array();
	
	/**
	 * The current action.
	 * @since 1.3.13-beta
	 *
	 * @access protected
	 * @var string
	 */
	protected $action;
	
	/**
	 * The pagination.
	 * @since 1.3.13-beta
	 *
	 * @access protected
	 * @var array
	 */
	protected $paged = array();
	
	/**
	 * Class constructor.
	 * @since 1.0.1-beta
	 *
	 * @access public
	 * @param int $id -- The post's id.
	 * @param string $action -- The current action.
	 * @param array $type_data (optional) -- The post type data.
	 */
	public function __construct(int $id, string $action, array $type_data = array()) {
		global $rs_query, $rs_taxonomies;
		
		$this->action = $action;
		
		if($id > 0) {
			$cols = array_keys(get_object_vars($this));
			$exclude = array('type_data', 'tax_data', 'action', 'paged');
			$cols = array_diff($cols, $exclude);
			
			$post = $rs_query->selectRow(getTable('p'), $cols, array(
				'id' => $id
			));
			
			foreach($post as $key => $value) $this->$key = $post[$key];
		} else {
			$this->id = 0;
		}
		
		$this->type_data = $type_data;
		
		// Fetch any associated taxonomy data
		if(!empty($this->type_data['taxonomies'])) {
			foreach($this->type_data['taxonomies'] as $tax) {
				if(array_key_exists($tax, $rs_taxonomies))
					$this->tax_data[] = $rs_taxonomies[$tax];
			}
		}
	}
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct a list of all posts in the database.
	 * @since 1.4.0-alpha
	 *
	 * @access public
	 */
	public function listRecords(): void {
		// Query vars
		$status = $_GET['status'] ?? 'all';
		$search = $_GET['search'] ?? null;
		$term = $_GET['term'] ?? '';
		$this->paged = paginate((int)($_GET['paged'] ?? 1));
		
		$this->pageHeading();
		?>
		<table class="data-table has-bulk-select">
			<thead>
				<?php
				if($this->type_data['hierarchical']) {
					$header_cols = array(
						'bulk-select' => domTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox bulk-selector'
						)),
						'title' => 'Title',
						'author' => 'Author',
						'publish-date' => 'Publish Date',
						'parent' => 'Parent',
						'meta-title' => 'Meta Title',
						'meta-description' => 'Meta Desc.'
					);
					
					// Add a label for comments if they're enabled
					if(getSetting('enable_comments') && $this->type_data['comments']) {
						$offset = 5;
						
						$header_cols = array_slice($header_cols, 0, $offset, true) + array(
							'comments' => 'Comments'
						) + array_slice($header_cols, $offset, null, true);
					}
				} else {
					$header_cols = array(
						'bulk-select' => domTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox bulk-selector'
						)),
						'title' => 'Title',
						'author' => 'Author',
						'publish-date' => 'Publish Date',
						'meta-title' => 'Meta Title',
						'meta-description' => 'Meta Desc.'
					);
					
					// Add a label for comments if they're enabled
					if(getSetting('enable_comments') && $this->type_data['comments']) {
						$offset = 4;
						
						$header_cols = array_slice($header_cols, 0, $offset, true) + array(
							'comments' => 'Comments'
						) + array_slice($header_cols, $offset, null, true);
					}
					
					// Add labels for any associated taxonomies
					if(!empty($this->tax_data)) {
						$offset = 3;
						
						foreach($this->tax_data as $tax) {
							$header_cols = array_slice($header_cols, 0, $offset, true) + array(
								'taxonomy' => $tax['label']
							) + array_slice($header_cols, $offset, null, true);
							$offset++;
						}
					}
				}
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$posts = $this->getResults($status, $search, $term);
				
				foreach($posts as $post) {
					foreach($post as $key => $value)
						${'p_' . $key} = $value;
					
					$meta = $this->getPostMeta($p_id);
					
					// Terms
					$terms = array();
					
					if(!$this->type_data['hierarchical'] && !empty($this->tax_data)) {
						foreach($this->tax_data as $tax)
							$terms[] = tdCell($this->getTerms($tax['name'], $p_id), 'terms');
					}
					
					$term_cols = implode('', $terms);
					
					echo tableRow(
						// Bulk select
						tdCell(domTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox',
							'value' => $p_id
						)), 'bulk-select'),
						// Title
						tdCell((isHomePage($p_id) ?
							domTag('i', array(
								'class' => 'fa-solid fa-house-chimney',
								'style' => 'cursor: help;',
								'title' => 'Home Page'
							)) . ' ' : '') .
							domTag('strong', array(
								'content' => $p_title
							)) . ($p_status !== 'published' && $status === 'all' ? ' &mdash; ' .
							domTag('em', array(
								'content' => $p_status
							)) : '') .
							domTag('div', array(
								'class' => 'actions',
								'content' => $this->getActionLinks($post)
							)), 'title'),
						// Author
						tdCell($this->getAuthor($post['author']), 'author'),
						// Terms (hierarchical post types only)
						$term_cols,
						// Publish date
						tdCell(is_null($p_created) ? '&mdash;' :
							formatDate($p_created, 'd M Y @ g:i A'), 'publish-date'),
						// Parent (hierarchical post types only)
						$this->type_data['hierarchical'] ?
							tdCell($this->getParent($post['parent']), 'parent') : '',
						// Comments
						getSetting('enable_comments') && $this->type_data['comments'] ?
							tdCell(($meta['comment_status'] ? $meta['comment_count'] : '&mdash;'), 'comments') : '',
						// Meta title
						tdCell(!empty($meta['title']) ? 'Yes' : 'No', 'meta-title'),
						// Meta description
						tdCell(!empty($meta['description']) ? 'Yes' : 'No', 'meta-description')
					);
				}
				
				if(empty($posts))
					echo tableRow(tdCell($this->type_data['labels']['no_items'], '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Bulk actions
		if(!empty($posts)) $this->bulkActions();
		
		// Set up page navigation
		echo pagerNav($this->paged['current'], $this->paged['count']);
		
		includeFile(PATH . MODALS . '/modal-delete.php');
	}
	
	/**
	 * Create a new post.
	 * @since 1.4.1-alpha
	 *
	 * @access public
	 */
	public function createRecord(): void {
		$type = $this->type_data['name'];
		
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<div class="content">
					<?php
					// Type (hidden)
					domTagPr('input', array(
						'type' => 'hidden',
						'name' => 'type',
						'value' => $type
					));
					
					// Title
					domTagPr('input', array(
						'id' => 'title-field',
						'class' => 'text-input required invalid init',
						'name' => 'title',
						'value' => ($_POST['title'] ?? ''),
						'placeholder' => $this->type_data['labels']['title_placeholder']
					));
					?>
					<div class="permalink">
						<?php
						// Permalink
						domTagPr('label', array(
							'for' => 'slug-field',
							'content' => domTag('strong', array(
								'content' => 'Permalink: '
							)) . getSetting('site_url') . getPermalink($type)
						));
						
						domTagPr('input', array(
							'id' => 'slug-field',
							'class' => 'text-input required invalid init',
							'name' => 'slug',
							'value' => ($_POST['slug'] ?? '')
						));
						
						domTagPr('span', array(
							'content' => '/'
						));
						?>
					</div>
					<?php
					// Insert media button
					domTagPr('input', array(
						'type' => 'button',
						'class' => 'button-input button modal-launch',
						'value' => 'Insert Media',
						'data-type' => 'all',
						'data-insert' => 'true'
					));
					
					// Content
					domTagPr('textarea', array(
						'id' => 'content-field',
						'class' => 'textarea-input',
						'name' => 'content',
						'rows' => 25,
						'content' => htmlspecialchars(($_POST['content'] ?? ''))
					));
					?>
				</div>
				<div class="sidebar">
					<div class="block">
						<?php
						domTagPr('h2', array(
							'content' => 'Publish'
						));
						?>
						<div class="row">
							<?php
							// Status
							domTagPr('label', array(
								'for' => 'status-field',
								'content' => 'Status'
							));
							
							domTagPr('select', array(
								'id' => 'status-field',
								'class' => 'select-input',
								'name' => 'status',
								'content' => $this->getStatusList()
							));
							?>
						</div>
						<div class="row">
							<?php
							// Author
							domTagPr('label', array(
								'for' => 'author-field',
								'content' => 'Author'
							));
							
							domTagPr('select', array(
								'id' => 'author-field',
								'class' => 'select-input',
								'name' => 'author',
								'content' => $this->getAuthorList()
							));
							?>
						</div>
						<div class="row">
							<?php
							// Publish date
							domTagPr('label', array(
								'for' => 'created-field',
								'content' => 'Publish on'
							));
							
							domTagPr('br');
							
							domTagPr('input', array(
								'type' => 'date',
								'id' => 'created-field',
								'class' => 'date-input',
								'name' => 'created[]'
							));
							
							domTagPr('input', array(
								'type' => 'time',
								'id' => 'created-field',
								'class' => 'date-input',
								'name' => 'created[]'
							));
							?>
						</div>
						<div id="submit" class="row">
							<?php
							// Submit button
							domTagPr('input', array(
								'type' => 'submit',
								'class' => 'submit-input button',
								'name' => 'submit',
								'value' => $this->type_data['labels']['publish_button']
							));
							?>
						</div>
					</div>
					<?php
					if($this->type_data['hierarchical']) {
						?>
						<div class="block">
							<?php
							domTagPr('h2', array(
								'content' => 'Attributes'
							));
							?>
							<div class="row">
								<?php
								// Parent
								domTagPr('label', array(
									'for' => 'parent-field',
									'content' => 'Parent'
								));
								
								domTagPr('select', array(
									'id' => 'parent-field',
									'class' => 'select-input',
									'name' => 'parent',
									'content' => domTag('option', array(
										'value' => 0,
										'content' => '(none)'
									)) . $this->getParentList($type)
								));
								?>
							</div>
							<div class="row">
								<?php
								// Template
								domTagPr('label', array(
									'for' => 'template-field',
									'content' => 'Template'
								));
								
								domTagPr('select', array(
									'id' => 'template-field',
									'class' => 'select-input',
									'name' => 'template',
									'content' => domTag('option', array(
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
							foreach($this->tax_data as $tax) {
								?>
								<div class="block">
									<?php
									domTagPr('h2', array(
										'content' => $tax['label']
									));
									?>
									<div class="row">
										<?php
										// Terms list
										echo $this->getTermsList($tax['name']);
										?>
									</div>
								</div>
								<?php
							}
						}
					}
					
					if(getSetting('enable_comments') && $this->type_data['comments']) {
						?>
						<div class="block">
							<?php
							domTagPr('h2', array(
								'content' => 'Comments'
							));
							?>
							<div class="row">
								<?php
								// Enable comments
								$comments = isset($_POST['comments']) ||
									(!isset($_POST['comments']) && $this->type_data['comments']);
								
								domTagPr('input', array(
									'type' => 'checkbox',
									'class' => 'checkbox-input',
									'name' => 'comments',
									'value' => (!empty($comments) ? 1 : 0),
									'checked' => $comments,
									'label' => array(
										'class' => 'checkbox-label',
										'content' => domTag('span', array(
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
						<?php
						domTagPr('h2', array(
							'content' => 'Featured Image'
						));
						?>
						<div class="row">
							<div class="image-wrap">
								<?php
								// Featured image thumbnail
								domTagPr('img', array(
									'src' => '//:0',
									'data-field' => 'thumb'
								));
								
								// Remove image button
								domTagPr('span', array(
									'class' => 'image-remove',
									'title' => 'Remove',
									'content' => domTag('i', array(
										'class' => 'fa-solid fa-xmark'
									))
								));
								?>
							</div>
							<?php
							// Featured image (hidden)
							domTagPr('input', array(
								'type' => 'hidden',
								'name' => 'feat_image',
								'value' => ($_POST['feat_image'] ?? 0),
								'data-field' => 'id'
							));
							
							// Choose image
							domTagPr('a', array(
								'class' => 'modal-launch',
								'href' => 'javascript:void(0)',
								'data-type' => 'image',
								'content' => 'Choose Image',
							));
							?>
						</div>
					</div>
				</div>
				<div class="metadata">
					<div class="block">
						<?php
						domTagPr('h2', array(
							'content' => 'Metadata'
						));
						?>
						<div class="row">
							<?php
							// Meta title
							domTagPr('label', array(
								'for' => 'meta-title-field',
								'content' => 'Title'
							));
							
							domTagPr('br');
							
							domTagPr('input', array(
								'id' => 'meta-title-field',
								'class' => 'text-input',
								'name' => 'meta_title',
								'value' => ($_POST['meta_title'] ?? '')
							));
							?>
						</div>
						<div class="row">
							<?php
							// Meta description
							domTagPr('label', array(
								'for' => 'meta-description-field',
								'content' => 'Description'
							));
							
							domTagPr('br');
							
							domTagPr('textarea', array(
								'id' => 'meta-description-field',
								'class' => 'textarea-input',
								'name' => 'meta_description',
								'cols' => 30,
								'rows' => 4,
								'content' => ($_POST['meta_description'] ?? '')
							));
							?>
						</div>
						<div class="row">
							<?php
							// Index post
							$index = isset($_POST['index_post']) ||
								(!isset($_POST['index_post']) && getSetting('do_robots'));
							
							domTagPr('input', array(
								'type' => 'checkbox',
								'id' => 'index-post-field',
								'class' => 'checkbox-input',
								'name' => 'index_post',
								'value' => $index ? 1 : 0,
								'checked' => $index,
								'label' => array(
									'class' => 'checkbox-label',
									'content' => domTag('span', array(
										'content' => 'Index ' . strtolower($this->type_data['labels']['name_singular'])
									))
								)
							));
							?>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
		includeFile(PATH . MODALS . '/modal-upload.php');
	}
	
	/**
	 * Edit an existing post.
	 * @since 1.4.9-alpha
	 *
	 * @access public
	 */
	public function editRecord(): void {
		if(empty($this->id) || $this->id <= 0 || empty($this->type))
			redirect(ADMIN_URI);
		
		$redir = match($this->type) {
			'media' => 'media.php',
			'widget' => 'widgets.php',
			default => ''
		};
		
		if(!empty($redir)) {
			redirect($redir . getQueryString(array(
				'id' => $this->id,
				'action' => 'edit'
			)));
		}
		
		if($this->isTrash($this->id))
			redirect(ADMIN_URI . ($this->type !== 'post' ? '?type=' . $this->type . '&' : '?') . 'status=trash');
		
		$this->pageHeading();
		
		$meta = $this->getPostMeta($this->id);
		$featimg_filepath = PATH . getMediaSrc($meta['feat_image']);
		
		if(!empty($meta['feat_image']) && file_exists($featimg_filepath))
			list($width, $height) = getimagesize($featimg_filepath);
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<div class="content">
					<?php
					// Title
					domTagPr('input', array(
						'id' => 'title-field',
						'class' => 'text-input required invalid init',
						'name' => 'title',
						'value' => $this->title,
						'placeholder' => $this->type_data['labels']['title_placeholder']
					));
					?>
					<div class="permalink">
						<?php
						// Permalink
						domTagPr('label', array(
							'for' => 'slug-field',
							'content' => domTag('strong', array(
								'content' => 'Permalink: '
							)) . getSetting('site_url') . getPermalink($this->type, $this->parent)
						));
						
						domTagPr('input', array(
							'id' => 'slug-field',
							'class' => 'text-input required invalid init',
							'name' => 'slug',
							'value' => $this->slug
						));
						
						domTagPr('span', array(
							'content' => '/'
						));
						?>
					</div>
					<?php
					// Insert media button
					domTagPr('input', array(
						'type' => 'button',
						'class' => 'button-input button modal-launch',
						'value' => 'Insert Media',
						'data-type' => 'all',
						'data-insert' => 'true'
					));
					
					// Content
					domTagPr('textarea', array(
						'id' => 'content-field',
						'class' => 'textarea-input',
						'name' => 'content',
						'rows' => 25,
						'content' => htmlspecialchars($this->content)
					));
					?>
				</div>
				<div class="sidebar">
					<div class="block">
						<?php
						domTagPr('h2', array(
							'content' => 'Publish'
						));
						?>
						<div class="row">
							<?php
							// Status
							domTagPr('label', array(
								'for' => 'status-field',
								'content' => 'Status'
							));
							
							domTagPr('select', array(
								'id' => 'status-field',
								'class' => 'select-input',
								'name' => 'status',
								'content' => $this->getStatusList()
							));
							?>
						</div>
						<div class="row">
							<?php
							// Author
							domTagPr('label', array(
								'for' => 'author-field',
								'content' => 'Author'
							));
							
							domTagPr('select', array(
								'id' => 'author-field',
								'class' => 'select-input',
								'name' => 'author',
								'content' => $this->getAuthorList($this->author)
							));
							?>
						</div>
						<div class="row">
							<?php
							// Publish date
							domTagPr('label', array(
								'for' => 'created-field',
								'content' => 'Published on'
							));
							
							domTagPr('br');
							
							domTagPr('input', array(
								'type' => 'date',
								'id' => 'created-field',
								'class' => 'date-input',
								'name' => 'created[]',
								'value' => (
									!is_null($this->created) ?
									formatDate($this->created, 'Y-m-d') :
									formatDate($this->modified, 'Y-m-d')
								)
							));
							
							domTagPr('input', array(
								'type' => 'time',
								'id' => 'created-field',
								'class' => 'date-input',
								'name' => 'created[]',
								'value' => (
									!is_null($this->created) ?
									formatDate($this->created, 'H:i') :
									formatDate($this->modified, 'H:i')
								)
							));
							?>
						</div>
						<div id="submit" class="row">
							<?php
							switch($this->status) {
								case 'draft':
									$is_published = false;
									break;
								case 'published':
								case 'private':
									$is_published = true;
									break;
							}
							
							// View/preview link
							echo $is_published ?
								domTag('a', array(
									'href' => (isHomePage($this->id) ? '/' : getPermalink(
										$this->type,
										$this->parent,
										$this->slug
									)),
									'target' => '_blank',
									'rel' => 'noreferrer noopener',
									'content' => 'View'
								)) :
								domTag('a', array(
									'href' => '/?id=' . $this->id . '&preview=true',
									'target' => '_blank',
									'rel' => 'noreferrer noopener',
									'content' => 'Preview'
								));
							
							// Submit button
							domTagPr('input', array(
								'type' => 'submit',
								'class' => 'submit-input button',
								'name' => 'submit',
								'value' => $this->type_data['labels']['update_button']
							));
							?>
						</div>
					</div>
					<?php
					if($this->type_data['hierarchical']) {
						?>
						<div class="block">
							<?php
							domTagPr('h2', array(
								'content' => 'Attributes'
							));
							?>
							<div class="row">
								<?php
								// Parent
								domTagPr('label', array(
									'for' => 'parent-field',
									'content' => 'Parent'
								));
								
								domTagPr('select', array(
									'id' => 'parent-field',
									'class' => 'select-input',
									'name' => 'parent',
									'content' => domTag('option', array(
										'value' => 0,
										'content' => '(none)'
									)) . $this->getParentList($this->type, $this->parent, $this->id)
								));
								?>
							</div>
							<div class="row">
								<?php
								// Template
								domTagPr('label', array(
									'for' => 'template-field',
									'content' => 'Template'
								));
								
								domTagPr('select', array(
									'id' => 'template-field',
									'class' => 'select-input',
									'name' => 'template',
									'content' => domTag('option', array(
										'value' => 'default',
										'content' => 'Default'
									)) . $this->getTemplateList($this->id)
								));
								?>
							</div>
						</div>
						<?php
					} else {
						if(!empty($this->tax_data)) {
							foreach($this->tax_data as $tax) {
								?>
								<div class="block">
									<?php
									domTagPr('h2', array(
										'content' => $tax['label']
									));
									?>
									<div class="row">
										<?php
										// Terms list
										echo $this->getTermsList($tax['name'], $this->id);
										?>
									</div>
								</div>
								<?php
							}
						}
					}
					
					if(getSetting('enable_comments') && $this->type_data['comments']) {
						?>
						<div class="block">
							<?php
							domTagPr('h2', array(
								'content' => 'Comments'
							));
							?>
							<div class="row">
								<?php
								// Enable comments
								domTagPr('input', array(
									'type' => 'checkbox',
									'class' => 'checkbox-input',
									'name' => 'comments',
									'value' => $meta['comment_status'],
									'checked' => $meta['comment_status'],
									'label' => array(
										'class' => 'checkbox-label',
										'content' => domTag('span', array(
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
						<?php
						domTagPr('h2', array(
							'content' => 'Featured Image'
						));
						?>
						<div class="row">
							<div class="image-wrap<?php echo !empty($meta['feat_image']) ?
								' visible' : ''; ?>" style="width: <?php echo $width ?? 0; ?>px;">
								<?php
								// Featured image thumbnail
								echo getMedia($meta['feat_image'], array(
									'data-field' => 'thumb'
								));
								
								// Remove image button
								domTagPr('span', array(
									'class' => 'image-remove',
									'title' => 'Remove',
									'content' => domTag('i', array(
										'class' => 'fa-solid fa-xmark'
									))
								));
								?>
							</div>
							<?php
							// Featured image (hidden)
							domTagPr('input', array(
								'type' => 'hidden',
								'name' => 'feat_image',
								'value' => $meta['feat_image'],
								'data-field' => 'id'
							));
							
							// Choose image
							domTagPr('a', array(
								'class' => 'modal-launch',
								'href' => 'javascript:void(0)',
								'data-type' => 'image',
								'content' => 'Choose Image',
							));
							?>
						</div>
					</div>
				</div>
				<div class="metadata">
					<div class="block">
						<?php
						domTagPr('h2', array(
							'content' => 'Metadata'
						));
						?>
						<div class="row">
							<?php
							// Meta title
							domTagPr('label', array(
								'for' => 'meta-title-field',
								'content' => 'Title'
							));
							
							domTagPr('br');
							
							domTagPr('input', array(
								'id' => 'meta-title-field',
								'class' => 'text-input',
								'name' => 'meta_title',
								'value' => ($meta['title'] ?? '')
							));
							?>
						</div>
						<div class="row">
							<?php
							// Meta description
							domTagPr('label', array(
								'for' => 'meta-description-field',
								'content' => 'Description'
							));
							
							domTagPr('br');
							
							domTagPr('textarea', array(
								'id' => 'meta-description-field',
								'class' => 'textarea-input',
								'name' => 'meta_description',
								'cols' => 30,
								'rows' => 4,
								'content' => ($meta['description'] ?? '')
							));
							?>
						</div>
						<div class="row">
							<?php
							// Index post
							domTagPr('input', array(
								'type' => 'checkbox',
								'id' => 'index-post-field',
								'class' => 'checkbox-input',
								'name' => 'index_post',
								'value' => $meta['index_post'],
								'checked' => $meta['index_post'],
								'label' => array(
									'class' => 'checkbox-label',
									'content' => domTag('span', array(
										'content' => 'Index ' . strtolower($this->type_data['labels']['name_singular'])
									))
								)
							));
							?>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
		includeFile(PATH . MODALS . '/modal-upload.php');
	}
	
	/**
	 * Duplicate a post.
	 * @since 1.3.7-beta
	 *
	 * @access public
	 */
	public function duplicatePost(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0 || empty($this->type))
			redirect(ADMIN_URI);
		
		$redir = match($this->type) {
			'media' => 'media.php',
			'widget' => 'widgets.php',
			default => ''
		};
		
		if(!empty($redir)) {
			redirect($redir . getQueryString(array(
				'id' => $this->id,
				'action' => 'edit'
			)));
		}
		
		if($this->isTrash($this->id))
			redirect(ADMIN_URI . ($this->type !== 'post' ? '?type=' . $this->type . '&' : '?') . 'status=trash');
		
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<table class="form-table">
					<?php
					// Original post
					echo formRow('Original ' . $this->type_data['labels']['name_singular'], array(
						'tag' => 'input',
						'id' => 'original-post-field',
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
					echo formRow('', array(
						'tag' => 'hr',
						'class' => 'separator'
					));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => $this->type_data['labels']['duplicate_item']
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Update a post's status.
	 * @since 1.2.9-beta
	 *
	 * @access public
	 * @param string $status -- The post's status.
	 * @param int $id (optional) -- The post's id.
	 */
	public function updatePostStatus(string $status, int $id = 0): void {
		global $rs_query;
		
		if($id !== 0) $this->id = $id;
		
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$type = $rs_query->selectField(getTable('p'), 'type', array(
			'id' => $this->id
		));
		
		if($type === $this->type_data['name']) {
			if($status === 'published' || $status === 'private') {
				$db_status = $rs_query->selectField(getTable('p'), 'status', array(
					'id' => $this->id
				));
				
				if($db_status !== $status) {
					$rs_query->update(getTable('p'), array(
						'created' => 'NOW()',
						'status' => $status
					), array(
						'id' => $this->id
					));
				} else {
					$rs_query->update(getTable('p'), array(
						'status' => $status
					), array(
						'id' => $this->id
					));
				}
			} else {
				$rs_query->update(getTable('p'), array(
					'created' => null,
					'status' => $status
				), array(
					'id' => $this->id
				));
			}
		}
	}
	
	/**
	 * Send a post to the trash.
	 * @since 1.4.6-alpha
	 *
	 * @access public
	 */
	public function trashPost(): void {
		$this->updatePostStatus('trash');
		
		redirect($this->type_data['menu_link']);
	}
	
	/**
	 * Restore a post from the trash.
	 * @since 1.4.6-alpha
	 *
	 * @access public
	 */
	public function restorePost(): void {
		$this->updatePostStatus('draft');
		
		redirect($this->type_data['menu_link'] . ($this->type !== 'post' ? '&' : '?') . 'status=trash');
	}
	
	/**
	 * Delete a post.
	 * @since 1.4.7-alpha
	 *
	 * @access public
	 */
	public function deleteRecord(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$rs_query->delete(getTable('p'), array(
			'id' => $this->id
		));
		
		$rs_query->delete(getTable('pm'), array(
			'post' => $this->id
		));
		
		$relationships = $rs_query->select(getTable('tr'), '*', array(
			'post' => $this->id
		));
		
		foreach($relationships as $relationship) {
			$rs_query->delete(getTable('tr'), array(
				'id' => $relationship['id']
			));
			
			$count = $rs_query->selectRow(getTable('tr'), 'COUNT(*)', array(
				'term' => $relationship['term']
			));
			
			$rs_query->update(getTable('t'), array(
				'count' => $count
			), array(
				'id' => $relationship['term']
			));
		}
		
		$rs_query->delete(getTable('c'), array(
			'post' => $this->id
		));
		
		$menu_items = $rs_query->select(getTable('pm'), 'post', array(
			'key' => 'post_link',
			'value' => $this->id
		));
		
		// Set any menu items associated with the post to invalid
		foreach($menu_items as $menu_item) {
			$rs_query->update(getTable('p'), array(
				'status' => 'invalid'
			), array(
				'id' => $menu_item['post']
			));
		}
		
		redirect($this->type_data['menu_link'] . ($this->type !== 'post' ? '&' : '?') .
			'status=trash&exit_status=del_success');
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the form data.
	 * @since 1.4.7-alpha
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateSubmission(array $data): string {
		global $rs_query;
		
		if(empty($data['title']) || empty($data['slug'])) {
			return exitNotice('REQ', -1);
			exit;
		}
		
		$slug = sanitize($data['slug']);
		
		if($this->slugExists($slug))
			$slug = getUniquePostSlug($slug);
		
		if($this->action === 'duplicate') {
			// Fetch the old post data for duplication
			$old_post = $rs_query->selectRow(getTable('p'), '*', array(
				'id' => $this->id
			));
			
			$old_postmeta = $rs_query->select(getTable('pm'), '*', array(
				'post' => $this->id
			));
			
			$old_term_relationships = $rs_query->select(getTable('tr'), '*', array(
				'post' => $this->id
			));
		} else {
			$valid_statuses = array('draft', 'published', 'private');
			
			if(!in_array($data['status'], $valid_statuses, true))
				$data['status'] = 'draft';
			
			switch($data['status']) {
				case 'draft':
					$is_published = false;
					break;
				case 'published':
				case 'private':
					$is_published = true;
					break;
			}
			
			$postmeta = array(
				'title' => $data['meta_title'],
				'description' => $data['meta_description'],
				'feat_image' => $data['feat_image'],
				'index_post' => (isset($data['index_post']) ? 1 : 0)
			);
			
			if(isset($data['template'])) $postmeta['template'] = $data['template'];
			
			if($this->type_data['comments'])
				$postmeta['comment_status'] = isset($data['comments']) ? 1 : 0;
		}
		
		switch($this->action) {
			case 'create':
				// Check whether a date has been provided and is valid
				if(!empty($data['created'][0]) && !empty($data['created'][1]) && $data['created'][0] >= '1000-01-01')
					$data['created'] = implode(' ', $data['created']);
				else
					$data['created'] = 'NOW()';
				
				if(!$this->type_data['hierarchical']) $data['parent'] = 0;
				
				$insert_id = $rs_query->insert(getTable('p'), array(
					'title' => $data['title'],
					'author' => $data['author'],
					'created' => ($is_published ? $data['created'] : null),
					'modified' => $data['created'],
					'content' => $data['content'],
					'status' => $data['status'],
					'slug' => $slug,
					'parent' => $data['parent'],
					'type' => $data['type']
				));
				
				if(isset($postmeta['comment_status'])) $postmeta['comment_count'] = 0;
				
				foreach($postmeta as $key => $value) {
					$rs_query->insert(getTable('pm'), array(
						'post' => $insert_id,
						'key' => $key,
						'value' => $value
					));
				}
				
				if(!empty($data['terms'])) {
					// Create new relationships
					foreach($data['terms'] as $term) {
						$rs_query->insert(getTable('tr'), array(
							'term' => $term,
							'post' => $insert_id
						));
						
						$count = $rs_query->selectRow(getTable('tr'), 'COUNT(*)', array(
							'term' => $term
						));
						
						$rs_query->update(getTable('t'), array(
							'count' => $count
						), array(
							'id' => $term
						));
					}
				}
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $insert_id,
					'action' => 'edit',
					'exit_status' => 'create_success'
				)));
				break;
			case 'edit':
				// Check whether a date has been provided and is valid
				if(!empty($data['created'][0]) && !empty($data['created'][1]) && $data['created'][0] >= '1000-01-01')
					$data['created'] = implode(' ', $data['created']);
				else
					$data['created'] = null;
				
				if(!$this->type_data['hierarchical']) $data['parent'] = 0;
				
				$rs_query->update(getTable('p'), array(
					'title' => $data['title'],
					'author' => $data['author'],
					'created' => ($is_published ? $data['created'] : null),
					'modified' => 'NOW()',
					'content' => $data['content'],
					'status' => $data['status'],
					'slug' => $slug,
					'parent' => $data['parent']
				), array(
					'id' => $this->id
				));
				
				foreach($postmeta as $key => $value) {
					$rs_query->update(getTable('pm'), array(
						'value' => $value
					), array(
						'post' => $this->id,
						'key' => $key
					));
				}
				
				$relationships = $rs_query->select(getTable('tr'), '*', array(
					'post' => $this->id
				));
				
				foreach($relationships as $relationship) {
					// Delete any unused relationships
					if(empty($data['terms']) || !in_array($relationship['term'], $data['terms'], true)) {
						$rs_query->delete(getTable('tr'), array(
							'id' => $relationship['id']
						));
						
						$count = $rs_query->selectRow(getTable('tr'), 'COUNT(*)', array(
							'term' => $relationship['term']
						));
						
						$rs_query->update(getTable('t'), array(
							'count' => $count
						), array(
							'id' => $relationship['term']
						));
					}
				}
				
				if(!empty($data['terms'])) {
					foreach($data['terms'] as $term) {
						$relationship = $rs_query->selectRow(getTable('tr'), 'COUNT(*)', array(
							'term' => $term,
							'post' => $this->id
						));
						
						// Skip existing relationships, otherwise create a new one
						if($relationship) {
							continue;
						} else {
							$rs_query->insert(getTable('tr'), array(
								'term' => $term,
								'post' => $this->id
							));
							
							$count = $rs_query->select(getTable('tr'), 'COUNT(*)', array(
								'term' => $term
							));
							
							$rs_query->update(getTable('t'), array(
								'count' => $count
							), array(
								'id' => $term
							));
						}
					}
				}
				
				foreach($data as $key => $value) $this->$key = $value;
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $this->id,
					'action' => $this->action,
					'exit_status' => 'edit_success'
				)));
				break;
			case 'duplicate':
				$insert_id = $rs_query->insert(getTable('p'), array(
					'title' => $data['title'],
					'author' => $old_post['author'],
					'created' => null,
					'modified' => $old_post['modified'],
					'content' => $old_post['content'],
					'status' => 'draft', // Set new post to a draft so the user has a chance to make changes before it goes live
					'slug' => $slug,
					'parent' => $old_post['parent'],
					'type' => $old_post['type']
				));
				
				foreach($old_postmeta as $meta) {
					// Reset comments to zero
					if($meta['key'] === 'comment_count') $meta['value'] = 0;
					
					$rs_query->insert(getTable('pm'), array(
						'post' => $insert_id,
						'key' => $meta['key'],
						'value' => $meta['value']
					));
				}
				
				if(!empty($old_term_relationships)) {
					foreach($old_term_relationships as $relationship) {
						$rs_query->insert(getTable('tr'), array(
							'term' => $relationship['term'],
							'post' => $insert_id
						));
						
						$count = $rs_query->selectRow(getTable('tr'), 'COUNT(*)', array(
							'term' => $relationship['term']
						));
						
						$rs_query->update(getTable('t'), array(
							'count' => $count
						), array(
							'id' => $relationship['term']
						));
					}
				}
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $insert_id,
					'action' => 'edit',
					'exit_status' => 'dup_success'
				)));
				break;
		}
	}
	
	/*------------------------------------*\
		MISCELLANEOUS
	\*------------------------------------*/
	
	/**
	 * Construct the page heading.
	 * @since 1.3.13-beta
	 *
	 * @access public
	 */
	public function pageHeading(): void {
		$labels = $this->type_data['labels'];
		
		switch($this->action) {
			case 'create':
				$title = $labels['create_item'];
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			case 'edit':
				$title = $labels['edit_item'];
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			case 'duplicate':
				$title = $labels['duplicate_item'];
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			default:
				$title = $labels['name'];
				$type = $this->type_data['name'];
				$status = $_GET['status'] ?? 'all';
				$search = $_GET['search'] ?? null;
				$term = $_GET['term'] ?? '';
		}
		?>
		<div class="heading-wrap">
			<?php
			// Page title
			domTagPr('h1', array(
				'content' => $title
			));
			
			if(!empty($this->action)) {
				// Status messages
				echo $message;
				
				// Exit notices
				if(isset($_GET['exit_status']))
					echo $this->exitNotice($_GET['exit_status']);
			} else {
				// Create button
				if(userHasPrivilege('can_create_' . str_replace(' ', '_', $this->type_data['labels']['name_lowercase']))) {
					echo actionLink($this->type_data['actions']['create'], array(
						'type' => ($type === 'post' ? null : $type),
						'classes' => 'button',
						'caption' => $labels['create_button']
					));
				}
				
				// Search
				recordSearch(array(
					'type' => $type,
					'status' => $status
				));
				
				// Info
				adminInfo();
				
				domTagPr('hr');
				
				// Exit notices
				if(isset($_GET['exit_status']))
					echo $this->exitNotice($_GET['exit_status']);
				?>
				<ul class="status-nav">
					<?php
					$keys = array('all', 'published', 'draft', 'private', 'trash');
					$count = array();
					
					foreach($keys as $key)
						$count[$key] = $this->getEntryCount($key, $search, $term);
					
					// Statuses
					foreach($count as $key => $value) {
						domTagPr('li', array(
							'content' => domTag('a', array(
								'href' => ADMIN_URI . '?type=' . $type . ($key === 'all' ? '' : '&status=' . $key),
								'content' => ucfirst($key) . ' ' . domTag('span', array(
									'class' => 'count',
									'content' => '(' . $value . ')'
								))
							))
						));
						
						if($key !== array_key_last($count)) echo ' &bull; ';
					}
					?>
				</ul>
				<?php
				// Record count
				if(!empty($term)) {
					$t = str_replace('-', '_', $term);
					$count[$t] = $this->getEntryCount($type, '', $term);
					
					$ct = $count[$t] . ' ' . ($count[$t] === 1 ? 'entry' : 'entries');
				} else {
					$ct = $count[$status] . ' ' . ($count[$status] === 1 ? 'entry' : 'entries');
				}
				
				domTagPr('div', array(
					'class' => 'entry-count status',
					'content' => $ct
				));
				
				$this->paged['count'] = ceil($count[$status] / $this->paged['per_page']);
			}
			?>
		</div>
		<?php
	}
	
	/**
	 * Generate an exit notice.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @param string $exit_status -- The exit status.
	 * @param int $status_code (optional) -- The type of notice to display.
	 * @return string
	 */
	private function exitNotice(string $exit_status, int $status_code = 1): string {
		$post_type = $this->type_data['labels']['name_singular'];
		
		return exitNotice(match($exit_status) {
			'create_success' => 'The ' . strtolower($post_type) . ' was successfully created. ' . domTag('a', array(
				'href' => $this->type_data['menu_link'],
				'content' => 'Return to list'
			)) . '?',
			'edit_success' => $post_type . ' updated! ' . domTag('a', array(
				'href' => $this->type_data['menu_link'],
				'content' => 'Return to list'
			)) . '?',
			'dup_success' => 'The ' . strtolower($post_type) . ' was successfully duplicated.',
			'del_success' => 'The ' . strtolower($post_type) . ' was successfully deleted.',
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Construct bulk actions.
	 * @since 1.2.9-beta
	 *
	 * @access private
	 */
	private function bulkActions(): void {
		$labels = $this->type_data['labels'];
		?>
		<div class="bulk-actions">
			<?php
			$type_name = str_replace(' ', '_', $labels['name_lowercase']);
			
			if(userHasPrivilege('can_edit_' . $type_name)) {
				$statuses = array('published', 'draft', 'private', 'trash');
				$list = array();
				
				foreach($statuses as $status) {
					$list[] = domTag('option', array(
						'value' => $status,
						'content' => ucfirst($status)
					));
				}
				
				domTagPr('select', array(
					'class' => 'actions',
					'name' => 'bulk_actions',
					'content' => implode('', $list)
				));
				
				// Update status
				button(array(
					'class' => 'bulk-update',
					'title' => 'Bulk status update',
					'label' => $labels['bulk_update']
				));
			}
			
			if(userHasPrivilege('can_delete_' . $type_name)) {
				// Delete
				button(array(
					'class' => 'bulk-delete',
					'title' => 'Bulk delete',
					'label' => $labels['bulk_delete']
				));
			}
			?>
		</div>
		<?php
	}
	
	/**
	 * Check whether a slug exists in the database.
	 * @since 1.4.8-alpha
	 *
	 * @access protected
	 * @param string $slug -- The slug.
	 * @return bool
	 */
	protected function slugExists(string $slug): bool {
		global $rs_query;
		
		if($this->id === 0) {
			return $rs_query->selectRow(getTable('p'), 'COUNT(slug)', array(
				'slug' => $slug
			)) > 0;
		} else {
			return $rs_query->selectRow(getTable('p'), 'COUNT(slug)', array(
				'slug' => $slug,
				'id' => array('<>', $this->id)
			)) > 0;
		}
	}
	
	/**
	 * Check whether a post is in the trash.
	 * @since 1.4.9-alpha
	 *
	 * @access private
	 * @param int $id -- The post's id.
	 * @return bool
	 */
	private function isTrash(int $id): bool {
		global $rs_query;
		
		$status = $_GET['status'] ?? 'all';
		
		return $rs_query->selectField(getTable('p'), 'status', array(
			'id' => $id
		)) === 'trash' || $status === 'trash';
	}
	
	/**
	 * Check whether a post is a descendant of another post.
	 * @since 1.4.9-alpha
	 *
	 * @access private
	 * @param int $id -- The post's id.
	 * @param int $ancestor -- The post's ancestor.
	 * @return bool
	 */
	private function isDescendant(int $id, int $ancestor): bool {
		global $rs_query;
		
		do {
			$parent = $rs_query->selectField(getTable('p'), 'parent', array(
				'id' => $id
			));
			
			$id = (int)$parent;
			
			if($id === $ancestor) return true;
		} while($id !== 0);
		
		return false;
	}
	
	/**
	 * Fetch a post's metadata.
	 * @since 1.4.10-alpha
	 *
	 * @access protected
	 * @param int $id -- The post's id.
	 * @return array
	 */
	protected function getPostMeta(int $id): array {
		global $rs_query;
		
		$postmeta = $rs_query->select(getTable('pm'), array('key', 'value'), array(
			'post' => $id
		));
		
		$meta = array();
		
		foreach($postmeta as $metadata) {
			$values = array_values($metadata);
			
			for($i = 0; $i < count($metadata); $i += 2)
				$meta[$values[$i]] = $values[$i + 1];
		}
		
		return $meta;
	}
	
	/**
	 * Construct a list of statuses.
	 * @since 1.3.11-beta
	 *
	 * @access private
	 * @return string
	 */
	private function getStatusList(): string {
		$statuses = array('draft', 'published', 'private');
		$list = array();
		
		foreach($statuses as $status) {
			$list[] = domTag('option', array(
				'value' => $status,
				'selected' => $status === $this->status,
				'content' => ucfirst($status)
			));
		}
		
		return implode('', $list);
	}
	
	/**
	 * Fetch a post's author.
	 * @since 1.4.0-alpha
	 *
	 * @access protected
	 * @param int $id -- The post's id.
	 * @return string
	 */
	protected function getAuthor(int $id): string {
		global $rs_query;
		
		return $rs_query->selectField(getTable('um'), 'value', array(
			'user' => $id,
			'key' => 'display_name'
		));
	}
	
	/**
	 * Construct a list of authors.
	 * @since 1.4.4-alpha
	 *
	 * @access private
	 * @param int $id (optional) -- The post's id.
	 * @return string
	 */
	private function getAuthorList(int $id = 0): string {
		global $rs_query;
		
		$list = array();
		
		$authors = $rs_query->select(getTable('u'), array('id', 'username'), array(), array(
			'order_by' => 'username'
		));
		
		foreach($authors as $author) {
			$display_name = $rs_query->selectField(getTable('um'), 'value', array(
				'user' => $author['id'],
				'key' => 'display_name'
			));
			
			$list[] = domTag('option', array(
				'value' => $author['id'],
				'selected' => $author['id'] === $id,
				'content' => ($display_name === $author['username'] ? $display_name :
					$display_name . ' (' . $author['username'] . ')')
			));
		}
		
		return implode('', $list);
	}
	
	/**
	 * Fetch a post's terms.
	 * @since 1.5.0-alpha
	 *
	 * @access private
	 * @param string $taxonomy -- The term's taxonomy.
	 * @param int $id -- The post's id.
	 * @return string
	 */
	private function getTerms(string $taxonomy, int $id): string {
		global $rs_query;
		
		$terms = array();
		
		$relationships = $rs_query->select(getTable('tr'), 'term', array(
			'post' => $id
		));
		
		foreach($relationships as $relationship) {
			$term = $rs_query->selectRow(getTable('t'), '*', array(
				'id' => $relationship['term'],
				'taxonomy' => getTaxonomyId($taxonomy)
			));
			
			if($term) {
				$terms[] = domTag('a', array(
					'href' => getPermalink($taxonomy, $term['parent'], $term['slug']),
					'content' => $term['name']
				));
			}
		}
		
		return empty($terms) ? '&mdash;' : implode(', ', $terms);
	}
	
	/**
	 * Construct a list of terms.
	 * @since 1.5.2-alpha
	 *
	 * @access private
	 * @param string $taxonomy -- The term's taxonomy.
	 * @param int $id (optional) -- The post's id.
	 * @return string
	 */
	private function getTermsList(string $taxonomy, int $id = 0): string {
		global $rs_query, $rs_taxonomies;
		
		$list = array();
		
		$terms = $rs_query->select(getTable('t'), array('id', 'name', 'slug'), array(
			'taxonomy' => getTaxonomyId($taxonomy)
		), array(
			'order_by' => 'name'
		));
		
		foreach($terms as $term) {
			$relationship = $rs_query->selectRow(getTable('tr'), 'COUNT(*)', array(
				'term' => $term['id'],
				'post' => $id
			));
			
			$list[] = domTag('li', array(
				'content' => domTag('input', array(
					'type' => 'checkbox',
					'class' => 'checkbox-input',
					'name' => 'terms[]',
					'value' => $term['id'],
					'checked' => ($relationship || ($id === 0 &&
						$term['slug'] === $rs_taxonomies[$taxonomy]['default_term']['slug'])
					),
					'label' => array(
						'class' => 'checkbox-label',
						'content' => domTag('span', array(
							'content' => $term['name']
						))
					)
				))
			));
		}
		
		return domTag('ul', array(
			'id' => 'terms-list',
			'content' => implode('', $list)
		));
	}
	
	/**
	 * Fetch a post's parent.
	 * @since 1.4.4-alpha
	 *
	 * @access private
	 * @param int $id -- The post's id.
	 * @return string
	 */
	private function getParent(int $id): string {
		global $rs_query;
		
		$parent = $rs_query->selectField(getTable('p'), 'title', array(
			'id' => $id
		));
		
		return empty($parent) ? '&mdash;' : $parent;
	}
	
	/**
	 * Construct a list of parents.
	 * @since 1.4.4-alpha
	 *
	 * @access private
	 * @param string $type -- The post's type.
	 * @param int $parent (optional) -- The post's parent id.
	 * @param int $id (optional) -- The post's id.
	 * @return string
	 */
	private function getParentList(string $type, int $parent = 0, int $id = 0): string {
		global $rs_query;
		
		$list = array();
		
		$posts = $rs_query->select(getTable('p'), array('id', 'title'), array(
			'status' => array('<>', 'trash'),
			'type' => $type
		));
		
		foreach($posts as $post) {
			$p_id = $post['id'];
			
			if($id !== 0) {
				// Skip the current post
				if($p_id === $id) continue;
				
				// Skip all descendant posts
				if($this->isDescendant($p_id, $id)) continue;
			}
			
			$list[] = domTag('option', array(
				'value' => $p_id,
				'selected' => $p_id === $parent,
				'content' => $post['title']
			));
		}
		
		return implode('', $list);
	}
	
	/**
	 * Construct a list of templates.
	 * @since 2.3.3-alpha
	 *
	 * @access private
	 * @param int $id (optional) -- The post's id.
	 * @return string
	 */
	private function getTemplateList(int $id = 0): string {
		global $rs_query;
		
		$templates_path = slash(PATH . THEMES) . getSetting('active_theme') . '/templates';
		
		if(file_exists($templates_path)) {
			// Fetch all templates in the directory
			$templates = array_diff(scandir($templates_path), array('.', '..'));
			
			$list = array();
			
			$current = $rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $id,
				'key' => 'template'
			));
			
			foreach($templates as $template) {
				$list[] = domTag('option', array(
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
	 * Fetch all posts based on a specific status or term.
	 * @since 1.3.15-beta
	 *
	 * @access private
	 * @param string $status -- The post's status.
	 * @param null|string $search -- The search query.
	 * @param string $term -- The term the post is linked to.
	 * @param bool $all (optional) -- Whether to return all or set a limit (for pagination).
	 * @return array
	 */
	private function getResults(string $status, ?string $search, string $term, bool $all = false): array {
		global $rs_query;
		
		$type = $this->type_data['name'];
		$order_by = $type === 'page' ? 'title' : 'created';
		$order = $type === 'page' ? 'ASC' : 'DESC';
		$limit = $all === false ? array($this->paged['start'], $this->paged['per_page']) : 0;
		
		if($status === 'all')
			$db_status = array('<>', 'trash');
		else
			$db_status = $status;
		
		if(!empty($term)) {
			$term_id = (int)$rs_query->selectField(getTable('t'), 'id', array(
				'slug' => $term
			));
			
			$relationships = $rs_query->select(getTable('tr'), 'post', array(
				'term' => $term_id
			));
			
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
			return $rs_query->select(getTable('p'), '*', array(
				'id' => $post_ids,
				'type' => $type
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		} elseif(!is_null($search)) {
			// Search results
			return $rs_query->select(getTable('p'), '*', array(
				'title' => array('LIKE', '%' . $search . '%'),
				'status' => $db_status,
				'type' => $type
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		} else {
			// All results
			return $rs_query->select(getTable('p'), '*', array(
				'status' => $db_status,
				'type' => $type
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		}
	}
	
	/**
	 * Fetch the post count based on a specific status or term.
	 * @since 1.4.0-alpha
	 *
	 * @access private
	 * @param string $status -- The post's status.
	 * @param null|string $search -- The search query.
	 * @param string $term -- The term the post is linked to.
	 * @return int
	 */
	private function getEntryCount(string $status, ?string $search, string $term): int {
		return count($this->getResults($status, $search, $term, true));
	}
	
	/**
	 * Fetch all associated action links.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @param array $post -- The post's data.
	 * @return string
	 */
	private function getActionLinks(array $post): string {
		$status = $_GET['status'] ?? 'all';
		$type_name = str_replace(' ', '_', $this->type_data['labels']['name_lowercase']);
		$actions = $this->type_data['actions'];
		$action_list = array();
		
		switch($post['status']) {
			case 'draft':
				$is_published = false;
				break;
			case 'published':
			case 'private':
				$is_published = true;
				break;
		}
		
		foreach($actions as $key => $value) {
			if($value === 'create' || $value === 'replace') continue;
			
			$privileged = match($value) {
				'edit', 'trash', 'restore' => userHasPrivilege('can_edit_' . $type_name),
				'duplicate' => userHasPrivilege('can_create_' . $type_name),
				'delete' => userHasPrivilege('can_delete_' . $type_name),
				default => null
			};
			
			$action_list[] = array(
				'privileged' => $privileged,
				'link' => $value,
				'caption' => ucfirst($value)
			);
		}
		
		list($edit, $delete, $view, $preview, $duplicate, $trash, $restore) = $action_list;
		
		$action_links = array(
			// Edit
			$edit['privileged'] && !$this->isTrash($post['id']) ?
				actionLink($edit['link'], array(
					'caption' => $edit['caption'],
					'id' => $post['id']
				)) : null,
			// Duplicate
			$duplicate['privileged'] && !$this->isTrash($post['id']) ?
				actionLink($duplicate['link'], array(
					'caption' => $duplicate['caption'],
					'id' => $post['id']
				)) : null,
			// Trash/restore
			$trash['privileged'] && $restore['privileged'] ?
				($this->isTrash($post['id']) ?
					actionLink($restore['link'], array(
						'caption' => $restore['caption'],
						'id' => $post['id']
					)) : actionLink($trash['link'], array(
						'caption' => $trash['caption'],
						'id' => $post['id']
					))
				) : null,
			// Delete
			$this->isTrash($post['id']) ? ($delete['privileged'] ?
				actionLink($delete['link'], array(
					'classes' => 'modal-launch delete-item',
					'data_item' => strtolower($this->type_data['labels']['name_singular']),
					'caption' => $delete['caption'],
					'id' => $post['id']
				)) : null) : (
			// View/preview
			domTag('a', array(
				'href' => ($is_published ? (isHomePage($post['id']) ? '/' :
					getPermalink($post['type'], $post['parent'], $post['slug'])) :
					('/?id=' . $post['id'] . '&preview=true')),
				'content' => ($is_published ? $view['caption'] : $preview['caption'])
			)))
		);
		
		// Filter out any empty actions
		$action_links = array_filter($action_links);
		
		return implode(' &bull; ', $action_links);
	}
}