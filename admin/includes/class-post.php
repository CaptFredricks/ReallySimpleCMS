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
		$page = isset($_GET['page']) ? paginate($_GET['page'], 4) : paginate(1, 4);
		
		// Get the post count (by type)
		$count = array('all'=>$this->getPostCount($type), 'published'=>$this->getPostCount($type, 'published'), 'draft'=>$this->getPostCount($type, 'draft'), 'trash'=>$this->getPostCount($type, 'trash'));
		?>
		<div class="heading-wrap">
			<h1><?php echo ucfirst($type).'s'; ?></h1>
			<a class="button" href="?<?php echo $type === 'post' ? '' : 'type='.$type.'&'; ?>action=create">Create New</a>
			<hr>
			<?php
			// Display any status messages
			if(isset($_GET['exit_status']) && $_GET['exit_status'] === 'success')
				echo statusMessage(ucfirst($type).' was successfully deleted.', true);
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
				// Display entry count
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
				// Fetch all posts from the database
				if($status === 'all')
					$posts = $rs_query->select('posts', '*', array('status'=>array('<>', 'trash'), 'type'=>$type), 'title', 'ASC', array($page['start'], $page['per_page']));
				else
					$posts = $rs_query->select('posts', '*', array('status'=>$status, 'type'=>$type), 'title', 'ASC', array($page['start'], $page['per_page']));
				
				// Loop through the posts
				foreach($posts as $post) {
					// Fetch the post metadata from the database
					$meta = $this->getPostMeta($post['id']);
					
					// Construct the current row
					echo tableRow(
						tableCell('<strong>'.$post['title'].'</strong>'.($post['status'] !== 'published' && $status === 'all' ? ' &ndash; <em>'.$post['status'].'</em>' : '').'<div class="actions">'.($status !== 'trash' ? '<a href="?id='.$post['id'].'&action=edit">Edit</a> &bull; <a href="?id='.$post['id'].'&action=trash">Trash</a> &bull; <a href="'.($post['status'] === 'published' ? ($this->isHomePage($post['id']) ? '/' : $this->getPermalink($post['parent'], $post['slug'])).'">View' : ('/?id='.$post['id'].'&preview=true').'">Preview').'</a>' : '<a href="?id='.$post['id'].'&action=restore">Restore</a> &bull; <a href="?id='.$post['id'].'&action=delete" rel="">Delete</a>').'</div>', 'title'),
						tableCell($this->getAuthor($post['author']), 'author'),
						$type === 'post' ? tableCell($this->getCategories($post['id']), 'categories') : '',
						tableCell(formatDate($post['date'], 'd M Y @ g:i A'), 'publish-date'),
						$type !== 'post' ? tableCell($this->getParent($post['parent']), 'parent') : '',
						tableCell(!empty($meta['title']) ? 'Yes' : 'No', 'meta_title'),
						tableCell(!empty($meta['description']) ? 'Yes' : 'No', 'meta_description')
					);

// ($status !== 'trash' ? : <a class="delete-item" href="javascript:void(0)" rel="'.($type === 'post' ? $post['id'] : $type.'-'.$post['id']).'">Delete</a>');

				}
				
				if(count($posts) === 0) {
					// Display notice if no posts are found
					echo tableRow(tableCell('There are no '.$type.'s to display.', '', count($table_header_cols)));
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
		<form class="data-form clear" action="" method="post" autocomplete="off">
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
						echo formTag('select', array('class'=>'select-input', 'name'=>'status', 'content'=>'<option value="draft">Draft</option><option value="published">Published</option>'));
						?>
					</div>
					<div class="row">
						<?php
						// Construct 'author' form tag
						echo formTag('label', array('for'=>'author', 'content'=>'Author'));
						echo formTag('select', array('class'=>'select-input', 'name'=>'author', 'content'=>$this->getAuthorList()));
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
						if($type === 'post') {
							// Construct 'categories' form checklist
							echo $this->getCategoriesList();
						} else {
							// Construct 'parent' form tag
							echo formTag('label', array('for'=>'parent', 'content'=>'Parent'));
							echo formTag('select', array('class'=>'select-input', 'name'=>'parent', 'content'=>'<option value="0">(none)</option>'.$this->getParentList($type)));
						}
						?>
					</div>
				</div>
				<div class="block">
					<h2>Featured Image</h2>
					<div class="row">
						<?php
						// Display the featured image if it's been selected
						isset($_POST['feat_image']) && strlen($_POST['feat_image']) > 0 ? '<img src=""><span></span>' : '';
						
						// Construct hidden 'featured' form tag
						echo formTag('input', array('type'=>'hidden', 'name'=>'feat_image'));
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
						echo formTag('textarea', array('class'=>'textarea-input', 'name'=>'meta_description', 'cols'=>30, 'rows'=>4, 'content'=>$_POST['meta_description'] ?? ''));
						?>
					</div>
				</div>
			</div>
		</form>
		<?php
	}
	
	/**
	 * Construct the 'Edit Post' form.
	 * @since 1.4.9[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function editEntry($id) {
		// Extend the Query class
		global $rs_query;
		
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Posts' page if the post id is invalid
			header('Location: posts.php');
		} else {
			// Fetch the post from the database
			$post = $rs_query->selectRow('posts', '*', array('id'=>$id));
			
			if(empty($post)) {
				// Redirect to the 'List Posts' page if the post doesn't exist
				header('Location: posts.php');
			} else {
				if($this->isTrash($id)) {
					// Redirect to the 'List Posts' trash page if the post is in the trash
					header('Location: posts.php'.($post['type'] !== 'post' ? '?type='.$post['type'].'&' : '?').'status=trash');
				} else {
					// Validate the form data and return any messages
					$message = isset($_POST['submit']) ? $this->validateData($_POST, $id) : '';
					
					// Fetch the post metadata from the database
					$meta = $this->getPostMeta($id);
					?>
					<div class="heading-wrap">
						<h1>Edit <?php echo ucwords($post['type']); ?></h1>
						<?php
						// Display status messages
						echo $message;
						?>
					</div>
					<form class="data-form clear" action="" method="post" autocomplete="off">
						<div class="content">
							<?php
							// Construct 'title' form tag
							echo formTag('input', array('type'=>'text', 'id'=>'title-field', 'class'=>'text-input required invalid init', 'name'=>'title', 'value'=>$post['title'], 'placeholder'=>ucfirst($post['type']).' title'));
							?>
							<div class="permalink">
								<?php
								// Construct 'permalink' form tag
								echo formTag('label', array('for'=>'slug', 'content'=>'<strong>Permalink:</strong> '.getSetting('site_url', false).($post['parent'] !== 0 ? $this->getPermalink($post['parent']) : '/')));
								echo formTag('input', array('type'=>'text', 'id'=>'slug-field', 'class'=>'text-input required invalid init', 'name'=>'slug', 'value'=>$post['slug']));
								echo '/';
								?>
							</div>
							<?php
							// Construct 'insert image' button form tag
							echo formTag('input', array('type'=>'button', 'class'=>'button-input button', 'value'=>'Insert Image'));
							
							// Construct 'content' form tag
							echo formTag('textarea', array('class'=>'textarea-input', 'name'=>'content', 'cols'=>30, 'rows'=>20, 'content'=>htmlspecialchars($post['content'])));
							?>
						</div>
						<div class="sidebar">
							<div class="block">
								<h2>Publish</h2>
								<div class="row">
									<?php
									// Construct 'status' form tag
									echo formTag('label', array('for'=>'status', 'content'=>'Status'));
									echo formTag('select', array('class'=>'select-input', 'name'=>'status', 'content'=>'<option value="'.$post['status'].'">'.ucfirst($post['status']).'</option>'.($post['status'] === 'draft' ? '<option value="published">Published</option>' : '<option value="draft">Draft</option>')));
									?>
								</div>
								<div class="row">
									<?php
									// Construct 'author' form tag
									echo formTag('label', array('for'=>'author', 'content'=>'Author'));
									echo formTag('select', array('class'=>'select-input', 'name'=>'author', 'content'=>$this->getAuthorList($post['author'])));
									?>
								</div>
								<div class="row">
									<?php
									// Construct 'publish date' form tag label
									echo formTag('label', array('for'=>'date', 'content'=>'Published on'));
									echo '<span id="date">'.formatDate($post['date'], 'M d Y @ h:i A').'</span>';
									?>
								</div>
								<div id="submit" class="row">
									<?php
									// Construct view/preview link
									echo $post['status'] === 'published' ? '<a href="'.($this->isHomePage($post['id']) ? '/' : $this->getPermalink($post['parent'], $post['slug'])).'">View</a>' : '<a href="/?id='.$post['id'].'&preview=true">Preview</a>';
									
									// Construct 'submit' button form tag
									echo formTag('input', array('type'=>'submit', 'id'=>'frm-submit', 'class'=>'submit-input button', 'name'=>'submit', 'value'=>'Update'));
									?>
								</div>
							</div>
							<div class="block">
								<h2>Attributes</h2>
								<div class="row">
									<?php
									if($post['type'] === 'post') {
										// Construct 'categories' form checklist
										echo $this->getCategoriesList($id);
									} else {
										// Construct 'parent' form tag
										echo formTag('label', array('for'=>'parent', 'content'=>'Parent'));
										echo formTag('select', array('class'=>'select-input', 'name'=>'parent', 'content'=>'<option value="0">(none)</option>'.$this->getParentList($post['type'], $post['parent'], $post['id'])));
									}
									?>
								</div>
							</div>
							<div class="block">
								<h2>Featured Image</h2>
								<div class="row">
									<?php
									// Display the featured image if it's been selected
									isset($_POST['feat_image']) && strlen($_POST['feat_image']) > 0 ? '<img src=""><span></span>' : '';
									
									// Construct hidden 'featured' form tag
									echo formTag('input', array('type'=>'hidden', 'name'=>'feat_image'));
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
									echo formTag('input', array('type'=>'text', 'class'=>'text-input', 'name'=>'meta_title', 'value'=>$meta['title'] ?? ''));
									?>
								</div>
								<div class="row">
									<?php
									// Construct 'meta description' form tag
									echo formTag('label', array('for'=>'meta_description', 'content'=>'Description'));
									echo formTag('br');
									echo formTag('textarea', array('class'=>'textarea-input', 'name'=>'meta_description', 'cols'=>30, 'rows'=>4, 'content'=>$meta['description'] ?? ''));
									?>
								</div>
							</div>
						</div>
					</form>
					<?php
				}
			}
		}
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
		
		if(!empty($id)) {
			// Fetch the post from the database
			$post = $rs_query->selectRow('posts', 'type', array('id'=>$id));
			
			// Set the post's status to 'trash'
			$rs_query->update('posts', array('status'=>'trash'), array('id'=>$id));
			
			// Redirect to the 'List Posts' page
			header('Location: posts.php'.($post['type'] !== 'post' ? '?type='.$post['type'] : ''));
		} else {
			// Redirect to the posts page
			header('Location: posts.php');
		}
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
		
		if(!empty($id)) {
			// Fetch the post from the database
			$post = $rs_query->selectRow('posts', 'type', array('id'=>$id));
			
			// Set the post's status to 'draft'
			$rs_query->update('posts', array('status'=>'draft'), array('id'=>$id));
			
			// Redirect to the 'List Posts' trash page
			header('Location: posts.php'.($post['type'] !== 'post' ? '?type='.$post['type'].'&' : '?').'status=trash');
		} else {
			// Redirect to the posts page
			header('Location: posts.php');
		}
	}
	
	/**
	 * Delete a post from the database.
	 * @since 1.4.7[a]
	 *
	 * @access public
	 * @param int $id
	 * @return null
	 */
	public function deleteEntry($id) {
		// Extend the Query class
		global $rs_query;
		
		if(empty($id) || $id <= 0) {
			// Redirect to the 'List Posts' page if the post id is invalid
			header('Location: posts.php');
		} else {
			// Fetch the post from the database
			$post = $rs_query->selectRow('posts', 'type', array('id'=>$id));
			
			// Delete the post from the database
			$rs_query->delete('posts', array('id'=>$id));
			
			// Delete the post metadata from the database
			$rs_query->delete('postmeta', array('post'=>$id));
			
			// Fetch all term relationships associated with the post from the database
			$relationships = $rs_query->select('term_relationships', '*', array('post'=>$id));
			
			// Loop through the relationships
			foreach($relationships as $relationship) {
				// Delete the unused relationship from the database
				$rs_query->delete('term_relationships', array('id'=>$relationship['id']));
				
				// Fetch the number of times a category shares a relationship with the post in the database
				$count = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$relationship['term']));
				
				// Update the category's post count
				$rs_query->update('terms', array('count'=>$count), array('id'=>$relationship['term']));
			}
			
			// Redirect to the 'List Posts' page (with a success message)
			header('Location: posts.php'.($post['type'] !== 'post' ? '?type='.$post['type'].'&' : '?').'status=trash&exit_status=success');
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
		// Extend the Query class
		global $rs_query;
		
		// Make sure no required fields are empty
		if(empty($data['title']) || empty($data['slug']))
			return statusMessage('R');
		
		// Make sure the slug is not already being used
		if($this->slugExists($data['slug'], $id))
			return statusMessage('That slug is already in use. Please choose another one.');
		
		// Prevent post from being published with an invalid status
		if($data['status'] !== 'draft' && $data['status'] !== 'published')
			$data['status'] = 'draft';
		
		// Create an array to hold the post metadata
		$postmeta = array('feat_image'=>$data['feat_image'], 'title'=>$data['meta_title'], 'description'=>$data['meta_description']);
		
		if($id === 0) {
			// Set the parent to zero if the post type is 'post' (non-hierarchical)
			if($data['type'] === 'post') $data['parent'] = 0;
			
			// Insert the new post into the database
			$insert_id = $rs_query->insert('posts', array('title'=>$data['title'], 'author'=>$data['author'], 'date'=>'NOW()', 'content'=>$data['content'], 'status'=>$data['status'], 'slug'=>$data['slug'], 'parent'=>$data['parent'], 'type'=>$data['type']));
			
			// Insert the post metadata into the database
			foreach($postmeta as $key=>$value)
				$rs_query->insert('postmeta', array('post'=>$insert_id, '_key'=>$key, 'value'=>$value));
			
			// Check whether any categories have been selected
			if(!empty($data['categories'])) {
				// Loop through the categories
				foreach($data['categories'] as $category) {
					// Insert a new term relationship into the database
					$rs_query->insert('term_relationships', array('term'=>$category, 'post'=>$insert_id));
					
					// Fetch the current category's post count from the database
					$count = $rs_query->selectRow('terms', 'count', array('id'=>$category));
					
					// Increment the category's post count
					$rs_query->update('terms', array('count'=>(++$count)), array('id'=>$category));
				}
			}
			
			// Redirect to the 'Edit Post' page
			header('Location: posts.php?id='.$insert_id.'&action=edit');
		} else {
			// Fetch the post type from the database
			$post = $rs_query->selectRow('posts', 'type', array('id'=>$id));
			
			// Set the parent to zero if the post type is 'post' (non-hierarchical)
			if($post['type'] === 'post') $data['parent'] = 0;
			
			// Update the post in the database
			$rs_query->update('posts', array('title'=>$data['title'], 'author'=>$data['author'], 'content'=>$data['content'], 'status'=>$data['status'], 'slug'=>$data['slug'], 'parent'=>$data['parent']), array('id'=>$id));
			
			// Update the post metadata in the database
			foreach($postmeta as $key=>$value)
				$rs_query->update('postmeta', array('value'=>$value), array('post'=>$id, '_key'=>$key));
			
			// Fetch all term relationships associated with the post from the database
			$relationships = $rs_query->select('term_relationships', '*', array('post'=>$id));
			
			// Loop through the relationships
			foreach($relationships as $relationship) {
				// Check whether the relationship still exists
				if(empty($data['categories']) || !in_array($relationship['term'], $data['categories'], true)) {
					// Delete the unused relationship from the database
					$rs_query->delete('term_relationships', array('id'=>$relationship['id']));
					
					// Fetch the number of times the category shares a relationship with a post in the database
					$count = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$relationship['term']));
					
					// Update the category's post count
					$rs_query->update('terms', array('count'=>$count), array('id'=>$relationship['term']));
				}
			}
			
			// Check whether any categories have been selected
			if(!empty($data['categories'])) {
				// Loop through the categories
				foreach($data['categories'] as $category) {
					// Fetch any relationships between the current category and the post from the database
					$relationship = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$category, 'post'=>$id));
					
					if($relationship) {
						// Skip to the next category if the relationship already exists
						continue;
					} else {
						// Insert a new term relationship into the database
						$rs_query->insert('term_relationships', array('term'=>$category, 'post'=>$id));
						
						// Fetch the number of times the category shares a relationship with a post in the database
						$count = $rs_query->selectRow('term_relationships', 'COUNT(*)', array('term'=>$category));
						
						// Update the category's post count
						$rs_query->update('terms', array('count'=>$count), array('id'=>$category));
					}
				}
			}
			
			// Return a status message
			return statusMessage(ucfirst($post['type']).' updated! <a href="posts.php'.($post['type'] === 'post' ? '' : '?type='.$post['type']).'">Return to list</a>?', true);
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
		// Extend the Query class
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
		
		// Return true if the post is the home page
		return $rs_query->selectRow('settings', 'COUNT(*)', array('name'=>'home_page', 'value'=>$id));
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
		// Extend the Query class
		global $rs_query;
		
		// Fetch the post from the database
		$post = $rs_query->selectRow('posts', 'status', array('id'=>$id));
		
		// Return true if the post is in the trash
		return $post['status'] === 'trash';
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
		// Extend the Query class
		global $rs_query;
		
		do {
			// Fetch the parent post from the database
			$post = $rs_query->selectRow('posts', 'parent', array('id'=>$id));
			
			// Set the new id
			$id = (int)$post['parent'];
			
			// Return true if the post's ancestor is found
			if($id === $ancestor) return true;
		} while($id !== 0);
		
		// Return false if no ancestor is found
		return false;
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
	 * Fetch a post's categories.
	 * @since 1.5.0[a]
	 *
	 * @access private
	 * @param int $id
	 * @return string
	 */
	private function getCategories($id) {
		// Extend the Query class
		global $rs_query;
		
		// Create an empty array to hold the categories
		$categories = array();
		
		// Fetch the term relationships from the database
		$relationships = $rs_query->select('term_relationships', 'term', array('post'=>$id));
		
		// Loop through the term relationships
		foreach($relationships as $relationship) {
			// Fetch the terms from the database
			$terms = $rs_query->selectRow('terms', 'name', array('id'=>$relationship['term'], 'taxonomy'=>getTaxonomyId('category')));
			
			// Assign the term name to the categories array
			$categories[] = $terms['name'];
		}
		
		// Return the categories
		return implode(', ', $categories);
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
		// Extend the Query class
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
		// Extend the Query class
		global $rs_query;
		
		// Fetch the parent post from the database
		$parent = $rs_query->selectRow('posts', 'title', array('id'=>$id));
		
		// Return the parent's title (if post has a parent)
		return !empty($parent) ? $parent['title'] : '';
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
		$posts = $rs_query->select('posts', 'id', array('status'=>array('<>', 'trash'), 'type'=>$type));
		
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
			$list .= '<option value="'.$post['id'].'"'.($post['id'] === $parent ? ' selected' : '').'>'.$this->getParent($post['id']).'</option>';
		}
		
		// Return the list
		return $list;
	}
	
	/**
	 * Fetch the post metadata.
	 * @since 1.4.10[a]
	 *
	 * @access private
	 * @param int $id
	 * @return array
	 */
	private function getPostMeta($id) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the post metadata from the database
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
	 * Construct the post permalink.
	 * @since 1.4.9[a]
	 *
	 * @access private
	 * @param int $parent
	 * @param string $slug (optional; default: '')
	 * @return string
	 */
	private function getPermalink($parent, $slug = '') {
		// Extend the Query class
		global $rs_query;
		
		// Create an empty permalink array
		$permalink = array();
		
		while($parent !== 0) {
			// Fetch the parent post from the database
			$post = $rs_query->selectRow('posts', array('slug', 'parent'), array('id'=>$parent));
			
			// Set the new parent id
			$parent = (int)$post['parent'];
			
			// Add to the permalink array
			$permalink[] = $post['slug'];
		};
		
		// Reverse and merge the permalink array
		$permalink = implode('/', array_reverse($permalink));
		
		// Construct the full permalink
		$permalink = (!empty($permalink) ? '/'.$permalink : '').(!empty($slug) ? '/'.$slug : '').'/';
		
		// Return the permalink
		return $permalink;
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