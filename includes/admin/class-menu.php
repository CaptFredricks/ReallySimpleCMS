<?php
/**
 * Admin class used to implement the Menu object. Inherits from the Term class.
 * Menus are used for website navigation on the front end of the website.
 * Menus can be created, modified, and deleted. Menus are stored in the `terms` table under the `nav_menu` taxonomy. Menu items are stored in the `posts` table as the `nav_menu_item` post type.
 * @since 1.8.0-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_menu
 *
 * ## VARIABLES [4] ##
 * See `Term` class for a list of inherited vars
 * - private string $tax_name
 * - private string $item_post_type
 * - private int $members
 * - private array $admin_page
 *
 * ## METHODS [37] ##
 * See `Term` class for a list of inherited methods
 * - public __construct(int $id, string $action)
 * { LISTS, FORMS, & ACTIONS [9] }
 * - public listRecords(): void
 * - public createRecord(): void
 * - public editRecord(): void
 * - public deleteRecord(): void
 * - private createMenuItem(string $type, int $id, int $index): array
 * - private editMenuItem(int $id): void
 * - private moveUpMenuItem(int $id): void
 * - private moveDownMenuItem(int $id): void
 * - private deleteMenuItem(int $id): void
 * { VALIDATION [2] }
 * - private validateMenuSubmission(array $data): string
 * - private validateMenuItemSubmission(array $data, int $id): string
 * { MISCELLANEOUS [25] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private slugExists(string $slug): bool
 * - private isFirstSibling(int $id): bool
 * - private isLastSibling(int $id): bool
 * - private isPreviousSibling(int $previous, int $id): bool
 * - private isNextSibling(int $next, int $id): bool
 * - private isDescendant(int $id, int $ancestor): bool
 * - private hasSiblings(int $id): bool
 * - private getMenuItems(): void
 * - private getMenuItemsSidebar(): void
 * - private getMenuItemsList(int $id, string $type): string
 * - private getMenuItemDepth(int $id): int
 * - private getMenuItemMeta(int $id): array
 * - private getMenuRelationships(int $exclude): array
 * - private getParent(int $id): int
 * - private getParentList(int $parent, int $id): string
 * - private getSiblings(int $id): array
 * - private getPreviousSibling(int $id): int
 * - private getNextSibling(int $id): int
 * - private getFamilyTree(int $id): int
 * - private getDescendants(int $id): void
 * - private getResults(?string $search, bool $all): array
 * - private getEntryCount(?string $search): int
 * - private getActionLinks(array $menu): string
 */
namespace Admin;

class Menu extends Term implements AdminInterface {
	/**
	 * The associated taxonomy.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $tax_name = 'nav_menu';
	
	/**
	 * The associated post type.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var string
	 */
	private $item_post_type = 'nav_menu_item';
	
	/**
	 * The number of members in a menu item's family tree.
	 * @since 1.8.7-alpha
	 *
	 * @access private
	 * @var int
	 */
	private $members = 0;
	
	/**
	 * The admin page's data.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @var array
	 */
	private $admin_page = array();
	
	/**
	 * Class constructor.
	 * @since 1.1.1-beta
	 *
	 * @access public
	 * @param int $id -- The menu's id.
	 * @param string $action -- The current action.
	 */
	public function __construct(int $id, string $action) {
		global $rs_query, $rs_admin_pages;
		
		$this->action = $action;
		$this->admin_page = $rs_admin_pages[basename($_SERVER['PHP_SELF'], '.php')];
		
		if($id > 0) {
			$cols = array_keys(get_object_vars($this));
			$exclude = array('action', 'paged', 'tax_name', 'item_post_type', 'members', 'admin_page');
			$cols = array_diff($cols, $exclude);
			
			$menu = $rs_query->selectRow(getTable('t'), $cols, array(
				'id' => $id,
				'taxonomy' => getTaxonomyId($this->tax_name)
			));
			
			foreach($menu as $key => $value) $this->$key = $menu[$key];
		} else {
			$this->id = 0;
		}
	}
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct a list of all menus in the database.
	 * @since 1.8.0-alpha
	 *
	 * @access public
	 */
	public function listRecords(): void {
		// Query vars
		$search = $_GET['search'] ?? null;
		$this->paged = paginate((int)($_GET['paged'] ?? 1));
		
		$this->pageHeading();
		?>
		<table class="data-table">
			<thead>
				<?php
				$header_cols = array(
					'name' => 'Name',
					'slug' => 'Slug',
					'count' => 'Item Count'
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$menus = $this->getResults($search);
				
				foreach($menus as $menu) {
					echo tableRow(
						// Name
						tdCell(domTag('strong', array(
							'content' => $menu['name']
						)) . domTag('div', array(
							'class' => 'actions',
							'content' => $this->getActionLinks($menu)
						)), 'name'),
						// Slug
						tdCell($menu['slug'], 'slug'),
						// Item count
						tdCell($menu['count'], 'count')
					);
				}
				
				if(empty($menus))
					echo tableRow(tdCell($this->admin_page['labels']['no_items'], '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Set up page navigation
		pagerNav($this->paged['current'], $this->paged['count']);
		
		includeFile(PATH . MODALS . '/modal-delete.php');
	}
	
	/**
	 * Create a new menu.
	 * @since 1.8.0-alpha
	 *
	 * @access public
	 */
	public function createRecord(): void {
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<div class="content">
					<?php
					// Name
					domTagPr('input', array(
						'id' => 'name-field',
						'class' => 'text-input required invalid init',
						'name' => 'name',
						'value' => ($_POST['name'] ?? ''),
						'placeholder' => $this->admin_page['labels']['title_placeholder'],
						'autocomplete' => 'off'
					));
					
					// Slug
					domTagPr('input', array(
						'id' => 'slug-field',
						'class' => 'text-input required invalid init',
						'name' => 'slug',
						'value' => ($_POST['slug'] ?? ''),
						'placeholder' => 'Menu slug'
					));
					?>
				</div>
				<div class="sidebar">
					<div class="block">
						<?php
						domTagPr('h2', array(
							'content' => 'Add Menu Items'
						));
						?>
						<div class="row">
							<?php
							// Menu items sidebar
							$this->getMenuItemsSidebar();
							?>
						</div>
						<div id="submit" class="row">
							<?php
							// Submit button
							domTagPr('input', array(
								'type' => 'submit',
								'class' => 'submit-input button',
								'name' => 'submit',
								'value' => $this->admin_page['labels']['create_button']
							));
							?>
						</div>
					</div>
				</div>
			</form>
			<div class="item-list-wrap">
				<?php
				// Menu items list
				echo $this->getMenuItems();
				?>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Edit an existing menu.
	 * @since 1.8.0-alpha
	 *
	 * @access public
	 */
	public function editRecord(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		if(isset($_GET['item_id'])) {
			$item_id = (int)$_GET['item_id'];
			
			if(empty($item_id) || $item_id <= 0) {
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $this->id,
					'action' => 'edit'
				)));
			}
			
			$count = $rs_query->selectRow(getTable('p'), 'COUNT(*)', array(
				'id' => $item_id,
				'type' => $this->item_post_type
			));
			
			if($count === 0) {
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $this->id,
					'action' => 'edit'
				)));
			}
		}
		
		$this->pageHeading();
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off">
				<div class="content">
					<?php
					// Name
					domTagPr('input', array(
						'id' => 'name-field',
						'class' => 'text-input required invalid init',
						'name' => 'name',
						'value' => $this->name,
						'placeholder' => $this->admin_page['labels']['title_placeholder'],
						'autocomplete' => 'off'
					));
					
					// Slug
					domTagPr('input', array(
						'id' => 'slug-field',
						'class' => 'text-input required invalid init',
						'name' => 'slug',
						'value' => $this->slug,
						'placeholder' => 'Menu slug'
					));
					?>
				</div>
				<div class="sidebar">
					<div class="block">
						<?php
						domTagPr('h2', array(
							'content' => 'Add Menu Items'
						));
						?>
						<div class="row">
							<?php
							// Menu items sidebar
							$this->getMenuItemsSidebar();
							?>
						</div>
						<div id="submit" class="row">
							<?php
							// Submit button
							domTagPr('input', array(
								'type' => 'submit',
								'class' => 'submit-input button',
								'name' => 'submit',
								'value' => $this->admin_page['labels']['update_button']
							));
							?>
						</div>
					</div>
				</div>
			</form>
			<div class="item-list-wrap">
				<?php
				// Menu items list
				echo $this->getMenuItems();
				?>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Delete an existing menu.
	 * @since 1.8.1-alpha
	 *
	 * @access public
	 */
	public function deleteRecord(): void {
		global $rs_query;
		
		if(empty($this->id) || $this->id <= 0)
			redirect(ADMIN_URI);
		
		$rs_query->delete(getTable('t'), array(
			'id' => $this->id,
			'taxonomy' => $this->taxonomy
		));
		
		$relationships = $rs_query->select(getTable('tr'), 'post', array(
			'term' => $this->id
		));
		
		$rs_query->delete(getTable('tr'), array(
			'term' => $this->id
		));
		
		// Delete all menu items associated with the menu
		foreach($relationships as $relationship) {
			$rs_query->delete(getTable('p'), array(
				'id' => $relationship['post']
			));
			
			$rs_query->delete(getTable('pm'), array(
				'post' => $relationship['post']
			));
		}
		
		redirect(ADMIN_URI . getQueryString(array(
			'exit_status' => 'del_success'
		)));
	}
	
	/**
	 * Create a menu item.
	 * @since 2.3.2-alpha
	 *
	 * @access private
	 * @param string $type -- The menu item's type.
	 * @param int $id -- The menu item's id.
	 * @param int $index -- The menu item's index.
	 * @return array
	 */
	private function createMenuItem(string $type, int $id, int $index): array {
		global $rs_query;
		
		if($type === 'post') {
			$post = $rs_query->selectRow(getTable('p'), array('id', 'title'), array(
				'id' => $id
			));
			
			$menu_item_id = $rs_query->insert(getTable('p'), array(
				'title' => $post['title'],
				'created' => 'NOW()',
				'modified' => 'NOW()',
				'content' => '',
				'slug' => '',
				'type' => $this->item_post_type
			));
			
			$rs_query->update(getTable('p'), array(
				'slug' => 'menu-item-' . $menu_item_id
			), array(
				'id' => $menu_item_id
			));
			
			return array(
				'id' => $menu_item_id,
				'post_link' => $post['id'],
				'menu_index' => $index
			);
		} elseif($type === 'term') {
			$term = $rs_query->selectRow(getTable('t'), array('id', 'name'), array(
				'id' => $id
			));
			
			$menu_item_id = $rs_query->insert(getTable('p'), array(
				'title' => $term['name'],
				'created' => 'NOW()',
				'modified' => 'NOW()',
				'content' => '',
				'slug' => '',
				'type' => $this->item_post_type
			));
			
			$rs_query->update(getTable('p'), array(
				'slug' => 'menu-item-' . $menu_item_id
			), array(
				'id' => $menu_item_id
			));
			
			return array(
				'id' => $menu_item_id,
				'term_link' => $term['id'],
				'menu_index' => $index
			);
		}
	}
	
	/**
	 * Edit a menu item.
	 * @since 1.8.1-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 */
	private function editMenuItem(int $id): void {
		global $rs_query;
		
		$message = isset($_POST['item_submit']) ? $this->validateMenuItemSubmission($_POST, $id) : '';
		
		$menu_item = $rs_query->selectRow(getTable('p'), '*', array(
			'id' => $id,
			'type' => $this->item_post_type
		));
		
		$meta = $this->getMenuItemMeta($id);
		
		$type = isset($meta['post_link']) ? 'post' : (isset($meta['term_link']) ? 'term' :
			(isset($meta['custom_link']) ? 'custom' : ''));
		
		domTagPr('hr', array(
			'class' => 'separator'
		));
		
		// Status messages
		echo $message;
		
		if(isset($_POST['item_submit'])) {
			domTagPr('meta', array(
				'http-equiv' => 'refresh',
				'content' => '0'
			));
		}
		?>
		<form class="data-form" action="" method="post" autocomplete="off">
			<table class="form-table">
				<?php
				// Caption
				echo formRow(array('Caption', true), array(
					'tag' => 'input',
					'id' => 'caption-field',
					'class' => 'text-input required invalid init',
					'name' => 'caption',
					'value' => $menu_item['title']
				));
				
				// Link
				switch($type) {
					case 'post':
						echo formRow('Link to', array(
							'tag' => 'select',
							'id' => 'post-link-field',
							'class' => 'select-input',
							'name' => 'post_link',
							'content' => $this->getMenuItemsList((int)$meta['post_link'], $type)
						));
						break;
					case 'term':
						echo formRow('Link to', array(
							'tag' => 'select',
							'id' => 'term-link-field',
							'class' => 'select-input',
							'name' => 'term_link',
							'content' => $this->getMenuItemsList((int)$meta['term_link'], $type)
						));
						break;
					case 'custom':
						echo formRow('Link to', array(
							'tag' => 'input',
							'id' => 'custom-link-field',
							'class' => 'text-input',
							'name' => 'custom_link',
							'value' => $meta['custom_link']
						));
						break;
				}
				
				// Parent
				echo formRow('Parent', array(
					'tag' => 'select',
					'id' => 'parent-field',
					'class' => 'select-input',
					'name' => 'parent',
					'content' => domTag('option', array(
						'value' => '0',
						'content' => '(none)'
					)) . $this->getParentList($menu_item['parent'], $menu_item['id'])
				));
				
				// Separator
				echo formRow('', array(
					'tag' => 'hr',
					'class' => 'separator'
				));
				
				// Update and delete buttons
				echo formRow('', array(
					'tag' => 'input',
					'type' => 'submit',
					'class' => 'submit-input button',
					'name' => 'item_submit',
					'value' => 'Update'
				), array(
					'tag' => 'div',
					'class' => 'actions',
					'content' => domTag('a', array(
						'class' => 'button',
						'href' => ADMIN_URI . getQueryString(array(
							'id' => $this->id,
							'action' => 'edit',
							'item_id' => $menu_item['id'],
							'item_action' => 'delete'
						)),
						'content' => 'Delete'
					))
				));
				?>
			</table>
		</form>
		<?php
	}
	
	/**
	 * Move a menu item one position up.
	 * @since 1.8.3-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 */
	private function moveUpMenuItem(int $id): void {
		global $rs_query;
		
		if($this->hasSiblings($id) && !$this->isFirstSibling($id)) {
			$current_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $id,
				'key' => 'menu_index'
			));
			
			$previous_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $this->getPreviousSibling($id),
				'key' => 'menu_index'
			));
			
			$rs_query->update(getTable('pm'), array(
				'value' => $previous_index
			), array(
				'post' => $id,
				'key' => 'menu_index'
			));
			
			$relationships = $rs_query->select(getTable('tr'), 'post', array(
				'term' => $this->id
			));
			
			foreach($relationships as $relationship) {
				$indexes[] = $rs_query->selectRow(getTable('pm'), array('value', 'post'), array(
					'post' => $relationship['post'],
					'key' => 'menu_index'
				));
			}
			
			// Sort the array in ascending index order
			asort($indexes);
			
			$i = 1;
			
			foreach($indexes as $index) {
				// Skip over any indexes that come after the current index (and its children) or before the previous index
				if($index['post'] === $id ||
					(int)$index['value'] >= ($current_index + $this->getFamilyTree($id)) ||
					(int)$index['value'] < $previous_index) continue;
				
				// Update each menu item's index
				if($this->isDescendant($index['post'], $id)) {
					$rs_query->update(getTable('pm'), array(
						'value' => $previous_index + $i
					), array(
						'post' => $index['post'],
						'key' => 'menu_index'
					));
					
					$i++;
				} else {
					// Update each menu item's index
					$rs_query->update(getTable('pm'), array(
						'value' => (int)$index['value'] + $this->getFamilyTree($id)
					), array(
						'post' => $index['post'],
						'key' => 'menu_index'
					));
				}
			}
			
			redirect(ADMIN_URI . getQueryString(array(
				'id' => $this->id,
				'action' => 'edit',
				'exit_status' => 'move_success_item'
			)));
		}
		
		redirect(ADMIN_URI . getQueryString(array(
			'id' => $this->id,
			'action' => 'edit',
			'exit_status' => 'move_failure_item'
		)));
	}
	
	/**
	 * Move a menu item one position down.
	 * @since 1.8.3-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 */
	private function moveDownMenuItem(int $id): void {
		global $rs_query;
		
		if($this->hasSiblings($id) && !$this->isLastSibling($id)) {
			$next_sibling = $this->getNextSibling($id);
			
			$current_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $id,
				'key' => 'menu_index'
			));
			
			$next_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $next_sibling,
				'key' => 'menu_index'
			));
			
			$rs_query->update(getTable('pm'), array(
				'value' => $current_index + $this->getFamilyTree($next_sibling)
			), array(
				'post' => $id,
				'key' => 'menu_index'
			));
			
			$relationships = $rs_query->select(getTable('tr'), 'post', array(
				'term' => $this->id
			));
			
			foreach($relationships as $relationship) {
				$indexes[] = $rs_query->selectRow(getTable('pm'), array('value', 'post'), array(
					'post' => $relationship['post'],
					'key' => 'menu_index'
				));
			}
			
			// Sort the array in ascending index order
			asort($indexes);
			
			$i = 1;
			
			foreach($indexes as $index) {
				// Skip over any indexes that come before the current index or after the next index
				if($index['post'] === $id ||
					(int)$index['value'] < $current_index ||
					(int)$index['value'] >= $next_index + $this->getFamilyTree($next_sibling)) continue;
				
				// Update each menu item's index
				if($this->isDescendant($index['post'], $id)) {
					$rs_query->update(getTable('pm'), array(
						'value' => $current_index + $this->getFamilyTree($this->getNextSibling($id)) + $i
					), array(
						'post' => $index['post'],
						'key' => 'menu_index'
					));
					
					$i++;
				} else {
					$rs_query->update(getTable('pm'), array(
						'value' => (int)$index['value'] - $this->getFamilyTree($id)
					), array(
						'post' => $index['post'],
						'key' => 'menu_index'
					));
				}
			}
			
			redirect(ADMIN_URI . getQueryString(array(
				'id' => $this->id,
				'action' => 'edit',
				'exit_status' => 'move_success_item'
			)));
		}
		
		redirect(ADMIN_URI . getQueryString(array(
			'id' => $this->id,
			'action' => 'edit',
			'exit_status' => 'move_failure_item'
		)));
	}
	
	/**
	 * Delete a menu item.
	 * @since 1.8.1-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 */
	private function deleteMenuItem(int $id): void {
		global $rs_query;
		
		$parent = (int)$rs_query->selectField(getTable('p'), 'parent', array(
			'id' => $id
		));
		
		$rs_query->update(getTable('p'), array(
			'parent' => $parent
		), array(
			'parent' => $id
		));
		
		$count = $rs_query->select(getTable('tr'), 'COUNT(*)', array(
			'term' => $this->id
		));
		
		$current_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
			'post' => $id,
			'key' => 'menu_index'
		));
		
		$rs_query->delete(getTable('p'), array(
			'id' => $id,
			'type' => $this->item_post_type
		));
		
		$rs_query->delete(getTable('pm'), array(
			'post' => $id
		));
		
		$rs_query->delete(getTable('tr'), array(
			'post' => $id
		));
		
		// Check whether the index is less than the last index
		if($current_index < $count - 1) {
			$relationships = $rs_query->select(getTable('tr'), 'post', array(
				'term' => $this->id
			));
			
			foreach($relationships as $relationship) {
				$index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
					'post' => $relationship['post'],
					'key' => 'menu_index'
				));
				
				if($index < $current_index) {
					continue;
				} else {
					$rs_query->update(getTable('pm'), array(
						'value' => $index - 1
					), array(
						'post' => $relationship['post'],
						'key' => 'menu_index'
					));
				}
			}
		}
		
		$count = $rs_query->select(getTable('tr'), 'COUNT(*)', array(
			'term' => $this->id
		));
		
		$rs_query->update(getTable('t'), array(
			'count' => $count
		), array(
			'id' => $this->id
		));
		
		redirect(ADMIN_URI . getQueryString(array(
			'id' => $this->id,
			'action' => 'edit',
			'exit_status' => 'del_success_item'
		)));
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the menu form data.
	 * @since 1.8.0-alpha
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateMenuSubmission(array $data): string {
		global $rs_query;
		
		if(empty($data['name']) || empty($data['slug'])) {
			return exitNotice('REQ', -1);
			exit;
		}
		
		$slug = sanitize($data['slug']);
		
		if($this->slugExists($slug))
			$slug = getUniqueTermSlug($slug);
		
		switch($this->action) {
			case 'create':
				$menu_id = $rs_query->insert(getTable('t'), array(
					'name' => $data['name'],
					'slug' => $slug,
					'taxonomy' => getTaxonomyId($this->tax_name)
				));
				
				// Menu items created from posts or terms
				if(!empty($data['menu_items'])) {
					$menu_items = $data['menu_items'];
					
					for($i = 0; $i < count($menu_items); $i++) {
						list($item_type, $item_id) = explode('-', $menu_items[$i]);
						
						$itemmeta = $this->createMenuItem($item_type, (int)$item_id, $i);
						$menu_item_id = array_shift($itemmeta);
						
						foreach($itemmeta as $key => $value) {
							$rs_query->insert(getTable('pm'), array(
								'post' => $menu_item_id,
								'key' => $key,
								'value' => $value
							));
						}
						
						$rs_query->insert(getTable('tr'), array(
							'term' => $menu_id,
							'post' => $menu_item_id
						));
					}
					
					$rs_query->update(getTable('t'), array(
						'count' => count($menu_items)
					), array(
						'id' => $menu_id
					));
				}
				
				// Menu items created from custom links
				if(!empty($data['custom_caption']) && !empty($data['custom_link'])) {
					$menu_item_id = $rs_query->insert(getTable('p'), array(
						'title' => $data['custom_caption'],
						'created' => 'NOW()',
						'modified' => 'NOW()',
						'content' => '',
						'slug' => '',
						'type' => $this->item_post_type
					));
					
					$rs_query->update(getTable('p'), array(
						'slug' => 'menu-item-' . $menu_item_id
					), array(
						'id' => $menu_item_id
					));
					
					$count = $rs_query->select(getTable('tr'), 'COUNT(*)', array(
						'term' => $menu_id
					));
					
					$itemmeta = array(
						'custom_link' => $data['custom_link'],
						'menu_index' => $count
					);
					
					foreach($itemmeta as $key => $value) {
						$rs_query->insert(getTable('pm'), array(
							'post' => $menu_item_id,
							'key' => $key,
							'value' => $value
						));
					}
					
					$rs_query->insert(getTable('tr'), array(
						'term' => $menu_id,
						'post' => $menu_item_id
					));
					
					$rs_query->update(getTable('t'), array(
						'count' => $count + 1
					), array(
						'id' => $menu_id
					));
				}
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $menu_id,
					'action' => 'edit',
					'exit_status' => 'create_success'
				)));
				break;
			case 'edit':
				$rs_query->update(getTable('t'), array(
					'name' => $data['name'],
					'slug' => $data['slug']
				), array(
					'id' => $this->id
				));
				
				// Menu items created from posts or terms
				if(!empty($data['menu_items'])) {
					$menu_items = $data['menu_items'];
					
					for($i = 0; $i < count($menu_items); $i++) {
						list($item_type, $item_id) = explode('-', $menu_items[$i]);
						
						$count = $rs_query->select(getTable('tr'), 'COUNT(*)', array(
							'term' => $this->id
						));
						
						$itemmeta = $this->createMenuItem($item_type, (int)$item_id, $count);
						$menu_item_id = array_shift($itemmeta);
						
						foreach($itemmeta as $key => $value) {
							$rs_query->insert(getTable('pm'), array(
								'post' => $menu_item_id,
								'key' => $key,
								'value' => $value
							));
						}
						
						$rs_query->insert(getTable('tr'), array(
							'term' => $this->id,
							'post' => $menu_item_id
						));
					}
					
					$count = $rs_query->select(getTable('tr'), 'COUNT(*)', array(
						'term' => $this->id
					));
					
					$rs_query->update(getTable('t'), array(
						'count' => $count
					), array(
						'id' => $this->id
					));
				}
				
				// Menu items created from custom links
				if(!empty($data['custom_caption']) && !empty($data['custom_link'])) {
					$menu_item_id = $rs_query->insert(getTable('p'), array(
						'title' => $data['custom_caption'],
						'created' => 'NOW()',
						'modified' => 'NOW()',
						'content' => '',
						'slug' => '',
						'type' => $this->item_post_type
					));
					
					$rs_query->update(getTable('p'), array(
						'slug' => 'menu-item-' . $menu_item_id
					), array(
						'id' => $menu_item_id
					));
					
					$count = $rs_query->select(getTable('tr'), 'COUNT(*)', array(
						'term' => $this->id
					));
					
					$itemmeta = array(
						'custom_link' => $data['custom_link'],
						'menu_index' => $count
					);
					
					foreach($itemmeta as $key => $value) {
						$rs_query->insert(getTable('pm'), array(
							'post' => $menu_item_id,
							'key' => $key,
							'value' => $value
						));
					}
					
					$rs_query->insert(getTable('tr'), array(
						'term' => $this->id,
						'post' => $menu_item_id
					));
					
					$rs_query->update(getTable('t'), array(
						'count' => $count + 1
					), array(
						'id' => $this->id
					));
				}
				
				foreach($data as $key => $value) $this->$key = $value;
				
				redirect(ADMIN_URI . getQueryString(array(
					'id' => $this->id,
					'action' => $this->action,
					'exit_status' => 'edit_success'
				)));
				break;
		}
	}
	
	/**
	 * Validate the menu item form data.
	 * @since 1.8.1-alpha
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @param int $id -- The menu item's id.
	 * @return string
	 */
	private function validateMenuItemSubmission(array $data, int $id): string {
		global $rs_query;
		
		if(empty($data['caption'])) {
			return exitNotice('REQ', -1);
			exit;
		}
		
		$parent = (int)$rs_query->selectField(getTable('p'), 'parent', array(
			'id' => $id
		));
		
		$rs_query->update(getTable('p'), array(
			'title' => $data['caption'],
			'modified' => 'NOW()',
			'parent' => $data['parent']
		), array(
			'id' => $id
		));
		
		if(!empty($data['post_link'])) {
			$rs_query->update(getTable('pm'), array(
				'value' => $data['post_link']
			), array(
				'post' => $id,
				'key' => 'post_link'
			));
		} elseif(!empty($data['term_link'])) {
			$rs_query->update(getTable('pm'), array(
				'value' => $data['term_link']
			), array(
				'post' => $id,
				'key' => 'term_link'
			));
		} elseif(!empty($data['custom_link'])) {
			$rs_query->update(getTable('pm'), array(
				'value' => $data['custom_link']
			), array(
				'post' => $id,
				'key' => 'custom_link'
			));
		}
		
		$menu = $rs_query->selectField(getTable('tr'), 'term', array(
			'post' => $id
		));
		
		$relationships = $rs_query->select(getTable('tr'), 'post', array(
			'term' => $menu
		));
		
		$indexes = array();
		
		foreach($relationships as $relationship) {
			$indexes[] = $rs_query->selectRow(getTable('pm'), array('post', 'value'), array(
				'post' => $relationship['post'],
				'key' => 'menu_index'
			));
		}
		
		$current_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
			'post' => $id,
			'key' => 'menu_index'
		));
		
		if((int)$data['parent'] === 0 && $parent !== 0) {
			$count = $rs_query->select(getTable('tr'), 'COUNT(*)', array(
				'term' => $menu
			));
			
			$rs_query->update(getTable('pm'), array(
				'value' => $count - $this->getFamilyTree($id)
			), array(
				'post' => $id,
				'key' => 'menu_index'
			));
			
			$i = 1;
			
			foreach($indexes as $index) {
				// Skip over any indexes that come before the current index
				if((int)$index['value'] <= $current_index) continue;
				
				// Check whether any menu items are children of the current menu item
				if($this->isDescendant($index['post'], $id)) {
					$rs_query->update(getTable('pm'), array(
						'value' => $count - $this->getFamilyTree($id) + $i
					), array(
						'post' => $index['post'],
						'key' => 'menu_index'
					));
					
					$i++;
				} else {
					$rs_query->update(getTable('pm'), array(
						'value' => (int)$index['value'] - $this->getFamilyTree($id)
					), array(
						'post' => $index['post'],
						'key' => 'menu_index'
					));
				}
			}
		} elseif((int)$data['parent'] !== 0) {
			$parent_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $data['parent'],
				'key' => 'menu_index'
			));
			
			if($current_index > $parent_index) {
				$rs_query->update(getTable('pm'), array(
					'value' => $parent_index + 1
				), array(
					'post' => $id,
					'key' => 'menu_index'
				));
				
				$i = 1;
				
				foreach($indexes as $index) {
					// Skip over any indexes that come after the current index (and its children) or before the parent index
					if((int)$index['value'] === $current_index ||
						(int)$index['value'] >= ($current_index + $this->getFamilyTree($id)) ||
						(int)$index['value'] <= $parent_index) continue;
					
					// Check whether any menu items are children of the current menu item
					if($this->isDescendant($index['post'], $id)) {
						$rs_query->update(getTable('pm'), array(
							'value' => $parent_index + 1 + $i
						), array(
							'post' => $index['post'],
							'key' => 'menu_index'
						));
						
						$i++;
					} else {
						$rs_query->update(getTable('pm'), array(
							'value' => (int)$index['value'] + $this->getFamilyTree($id)
						), array(
							'post' => $index['post'],
							'key' => 'menu_index'
						));
					}
				}
			} elseif($current_index < $parent_index) {
				// Determine the new index of the current menu item
				$new_index = $parent_index - $this->getFamilyTree($id) +
					$this->getFamilyTree((int)$data['parent']) - $this->getFamilyTree($id);
				
				$rs_query->update(getTable('pm'), array(
					'value' => $new_index
				), array(
					'post' => $id,
					'key' => 'menu_index'
				));
				
				$i = 1;
				
				foreach($indexes as $index) {
					// Skip over any indexes that come before the current index or after the parent index
					if((int)$index['value'] <= $current_index ||
						(int)$index['value'] >= $parent_index + $this->getFamilyTree((int)$data['parent']) -
						$this->getFamilyTree($id)) continue;
					
					// Check whether any menu items are children of the current menu item
					if($this->isDescendant($index['post'], $id)) {
						$rs_query->update(getTable('pm'), array(
							'value' => $new_index + $i
						), array(
							'post' => $index['post'],
							'key' => 'menu_index'
						));
						
						$i++;
					} else {
						$rs_query->update(getTable('pm'), array(
							'value' => (int)$index['value'] - $this->getFamilyTree($id)
						), array(
							'post' => $index['post'],
							'key' => 'menu_index'
						));
					}
				}
			}
		}
		
		redirect(ADMIN_URI . getQueryString(array(
			'id' => $this->id,
			'action' => 'edit',
			'item_id' => $id,
			'item_action' => 'edit',
			'exit_status' => 'edit_success_item'
		)));
	}
	
	/*------------------------------------*\
		MISCELLANEOUS
	\*------------------------------------*/
	
	/**
	 * Construct the page heading.
	 * @since 1.3.14-beta
	 *
	 * @access public
	 */
	public function pageHeading(): void {
		$labels = $this->admin_page['labels'];
		
		switch($this->action) {
			case 'create':
				$title = $labels['create_item'];
				$message = isset($_POST['submit']) ? $this->validateMenuSubmission($_POST) : '';
				break;
			case 'edit':
				$title = $labels['edit_item'] . ': { ' . domTag('em', array(
					'content' => $this->name
				)) . ' }';
				$message = isset($_POST['submit']) ? $this->validateMenuSubmission($_POST) : '';
				break;
			default:
				$title = $labels['name'];
				$search = $_GET['search'] ?? null;
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
				if(isset($_GET['exit_status'])) {
					$exit = $_GET['exit_status'];
					
					if($exit === 'move_failure_item')
						$status_code = -1;
					
					if(isset($status_code))
						echo $this->exitNotice($exit, $status_code);
					else
						echo $this->exitNotice($exit);
				}
			} else {
				// Create button
				if(userHasPrivilege('can_create_menus')) {
					echo actionLink('create', array(
						'classes' => 'button',
						'caption' => $labels['create_button']
					));
				}
				
				// Search
				recordSearch();
				
				//Info
				adminInfo();
				
				domTagPr('hr');
				
				// Exit notices
				if(isset($_GET['exit_status']))
					echo $this->exitNotice($_GET['exit_status']);
				
				// Record count
				$count = $this->getEntryCount($search);
				
				domTagPr('div', array(
					'class' => 'entry-count',
					'content' => $count . ' ' . ($count === 1 ? 'entry' : 'entries')
				));
				
				$this->paged['count'] = ceil($count / $this->paged['per_page']);
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
		return exitNotice(match($exit_status) {
			// Menus
			'create_success' => 'The menu was successfully created. ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'edit_success' => 'Menu updated! ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'del_success' => 'The menu was successfully deleted.',
			// Menu items
			'edit_success_item' => 'Menu item updated! ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'move_success_item' => 'Menu item moved successfully. ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			'move_failure_item' => 'The menu item could not be moved.',
			'del_success_item' => 'The menu item was successfully deleted. ' . domTag('a', array(
				'href' => ADMIN_URI,
				'content' => 'Return to list'
			)) . '?',
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Check whether a slug exists in the database.
	 * @since 1.8.0-alpha
	 *
	 * @access private
	 * @param string $slug -- The menu's slug.
	 * @return bool
	 */
	private function slugExists(string $slug): bool {
		global $rs_query;
		
		if($this->id === 0) {
			return $rs_query->selectRow(getTable('t'), 'COUNT(slug)', array(
				'slug' => $slug
			)) > 0;
		} else {
			return $rs_query->selectRow(getTable('t'), 'COUNT(slug)', array(
				'slug' => $slug,
				'id' => array('<>', $this->id)
			)) > 0;
		}
	}
	
	/**
	 * Check whether a menu item is the first of its siblings.
	 * @since 1.8.12-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @return bool
	 */
	private function isFirstSibling(int $id): bool {
		global $rs_query;
		
		$current_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
			'post' => $id,
			'key' => 'menu_index'
		));
		
		$siblings = $this->getSiblings($id);
		
		foreach($siblings as $sibling) {
			$index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $sibling,
				'key' => 'menu_index'
			));
			
			if($index < $current_index) return false;
		}
		
		return true;
	}
	
	/**
	 * Check whether a menu item is the last of its siblings.
	 * @since 1.8.12-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @return bool
	 */
	private function isLastSibling(int $id): bool {
		global $rs_query;
		
		$current_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
			'post' => $id,
			'key' => 'menu_index'
		));
		
		$siblings = $this->getSiblings($id);
		
		foreach($siblings as $sibling) {
			$index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $sibling,
				'key' => 'menu_index'
			));
			
			if($index > $current_index) return false;
		}
		
		return true;
	}
	
	/**
	 * Check whether a menu item is the previous sibling of another menu item.
	 * @since 1.8.12-alpha
	 *
	 * @access private
	 * @param int $previous -- The previous menu item's id.
	 * @param int $id -- The menu item's id.
	 * @return bool
	 */
	private function isPreviousSibling(int $previous, int $id): bool {
		global $rs_query;
		
		$siblings = $this->getSiblings($id);
		
		if(in_array($previous, $siblings, true)) {
			$previous_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $previous,
				'key' => 'menu_index'
			));
			
			$current_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $id,
				'key' => 'menu_index'
			));
			
			if($previous_index < $current_index) {
				foreach($siblings as $sibling) {
					$index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
						'post' => $sibling,
						'key' => 'menu_index'
					));
					
					// Check whether the sibling's index falls in between the previous index and the current index
					if($index > $previous_index && $index < $current_index) return false;
				}
				
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Check whether a menu item is the next sibling of another menu item.
	 * @since 1.8.12-alpha
	 *
	 * @access private
	 * @param int $next -- The next menu item's id.
	 * @param int $id -- The menu item's id.
	 * @return bool
	 */
	private function isNextSibling(int $next, int $id): bool {
		global $rs_query;
		
		$siblings = $this->getSiblings($id);
		
		if(in_array($next, $siblings, true)) {
			$next_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $next,
				'key' => 'menu_index'
			));
			
			$current_index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $id,
				'key' => 'menu_index'
			));
			
			if($next_index > $current_index) {
				foreach($siblings as $sibling) {
					$index = (int)$rs_query->selectField(getTable('pm'), 'value', array(
						'post' => $sibling,
						'key' => 'menu_index'
					));
					
					// Check whether the sibling's index falls in between the current index and the next index
					if($index > $current_index && $index < $next_index) return false;
				}
				
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Check whether a menu item is a descendant of another menu item.
	 * @since 1.8.6-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @param int $ancestor -- The menu item's ancestor.
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
	 * Check whether a menu item has siblings.
	 * @since 1.8.12-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @return bool
	 */
	private function hasSiblings(int $id): bool {
		return count($this->getSiblings($id)) > 0;
	}
	
	/**
	 * Fetch a menu's items.
	 * @since 1.8.0-alpha
	 *
	 * @access private
	 */
	private function getMenuItems(): void {
		global $rs_query;
		?>
		<ul class="item-list">
			<?php
			$relationships = $rs_query->select(getTable('tr'), 'post', array(
				'term' => $this->id
			));
			
			$itemmeta = array();
			$i = 0;
			
			foreach($relationships as $relationship) {
				$itemmeta[] = $this->getMenuItemMeta($relationship['post']);
				$itemmeta[$i] = array_reverse($itemmeta[$i]);
				$itemmeta[$i]['post'] = $relationship['post'];
				$i++;
			}
			
			// Sort the array in ascending index order
			uasort($itemmeta, function($a, $b) {
				return $a['menu_index'] > $b['menu_index'] ? 1 : -1;
			});
			
			foreach($itemmeta as $meta) {
				$menu_item = $rs_query->selectRow(getTable('p'), array('id', 'title', 'status', 'parent'), array(
					'id' => $meta['post']
				));
				
				if(isset($meta['post_link'])) {
					$type = $rs_query->selectField(getTable('p'), 'type', array(
						'id' => $meta['post_link']
					));
				} elseif(isset($meta['term_link']))
					$type = 'term';
				elseif(isset($meta['custom_link']))
					$type = 'custom';
				?>
				<li class="menu-item depth-<?php echo $this->getMenuItemDepth($menu_item['id']) .
					($menu_item['status'] === 'invalid' ? ' invalid' : '');
				?>">
					<?php
					// Title
					domTagPr('strong', array(
						'content' => $menu_item['title']
					)) . ' &mdash; ' . domTag('small', array(
						'content' => domTag('em', array(
							'content' => empty($type) ? $menu_item['status'] : $type
						))
					));
					
					// Check whether the menu item's id is set
					if(isset($_GET['item_id']) && (int)$_GET['item_id'] === $menu_item['id']) {
						if(isset($_GET['item_action'])) {
							switch($_GET['item_action']) {
								case 'move_up':
									$this->moveUpMenuItem($menu_item['id']);
									break;
								case 'move_down':
									$this->moveDownMenuItem($menu_item['id']);
									break;
								case 'edit':
									// Cancel button
									domTagPr('div', array(
										'class' => 'actions',
										'content' => actionLink('edit', array(
											'caption' => 'Cancel',
											'id' => $this->id
										))
									));
									
									$this->editMenuItem($menu_item['id']);
									break;
								case 'delete':
									$this->deleteMenuItem($menu_item['id']);
									break;
							}
						}
					} else {
						// Action links
						domTagPr('div', array(
							'class' => 'actions',
							'content' => // Move up
								actionLink('edit', array(
									'caption' => '&uarr;',
									'id' => $this->id
								), array(
									'item_id' => $menu_item['id'],
									'item_action' => 'move_up'
								)) . ' &bull; ' .
								// Move down
								actionLink('edit', array(
									'caption' => '&darr;',
									'id' => $this->id
								), array(
									'item_id' => $menu_item['id'],
									'item_action' => 'move_down'
								)) . ' &bull; ' .
								// Edit
								actionLink('edit', array(
									'caption' => 'Edit',
									'id' => $this->id
								), array(
									'item_id' => $menu_item['id'],
									'item_action' => 'edit'
								))
						));
					}
					?>
				</li>
				<?php
			}
			
			if(empty($relationships)) {
				domTagPr('li', array(
					'class' => 'menu-item',
					'content' => 'This menu is empty!'
				));
			}
			?>
		</ul>
		<?php
	}
	
	/**
	 * Construct a sidebar of menu items.
	 * @since 1.8.0-alpha
	 *
	 * @access private
	 */
	private function getMenuItemsSidebar(): void {
		global $rs_query, $rs_post_types, $rs_taxonomies;
		
		$order_by = 'id';
		$order = 'DESC';
		
		// Post types
		foreach($rs_post_types as $post_type) {
			if(!$post_type['show_in_nav_menus']) continue;
			
			$post_list_content = array();
			
			$posts = $rs_query->select(getTable('p'), array('id', 'title'), array(
				'status' => 'published',
				'type' => $post_type['name']
			), array(
				'order_by' => $order_by,
				'order' => $order
			));
			
			foreach($posts as $post) {
				$post_list_content[] = domTag('li', array(
					'content' => domTag('input', array(
						'type' => 'checkbox',
						'class' => 'checkbox-input',
						'name' => 'menu_items[]',
						'value' => 'post-' . $post['id'],
						'label' => array(
							'class' => 'checkbox-label',
							'content' => domTag('span', array(
								'title' => $post['title'],
								'content' => trimWords($post['title'], 5)
							))
						)
					))
				));
			}
			
			domTagPr('fieldset', array(
				'name' => 'post-type__' . $post_type['name'],
				'content' => domTag('legend', array(
					'content' => $post_type['label']
				)) . domTag('ul', array(
					'class' => 'checkbox-list',
					'content' => implode('', $post_list_content)
				))
			));
		}
		
		// Taxonomies
		foreach($rs_taxonomies as $taxonomy) {
			if(!$taxonomy['show_in_nav_menus']) continue;
			
			$term_list_content = array();
			
			$terms = $rs_query->select(getTable('t'), array('id', 'name'), array(
				'taxonomy' => getTaxonomyId($taxonomy['name'])
			), array(
				'order_by' => $order_by,
				'order' => $order
			));
			
			foreach($terms as $term) {
				$term_list_content[] = domTag('li', array(
					'content' => domTag('input', array(
						'type' => 'checkbox',
						'class' => 'checkbox-input',
						'name' => 'menu_items[]',
						'value' => 'term-' . $term['id'],
						'label' => array(
							'class' => 'checkbox-label',
							'content' => domTag('span', array(
								'title' => $term['name'],
								'content' => trimWords($term['name'], 5)
							))
						)
					))
				));
			}
			
			domTagPr('fieldset', array(
				'name' => 'taxonomy__' . $taxonomy['name'],
				'content' => domTag('legend', array(
					'content' => $taxonomy['label']
				)) . domTag('ul', array(
					'class' => 'checkbox-list',
					'content' => implode('', $term_list_content)
				))
			));
		}
		
		// Custom link
		domTagPr('fieldset', array(
			'name' => 'custom-link',
			'content' => domTag('legend', array(
				'content' => 'Custom Link'
			)) . // Caption
			domTag('label', array(
				'for' => 'custom-caption-field',
				'content' => 'Caption'
			)) . domTag('input', array(
				'id' => 'custom-caption-field',
				'class' => 'text-input',
				'name' => 'custom_caption'
			)) . // Separator
			domTag('div', array(
				'class' => 'clear',
				'style' => 'height: 2px;'
			)) . // Link
			domTag('label', array(
				'for' => 'custom-link-field',
				'content' => 'Link'
			)) . domTag('input', array(
				'id' => 'custom-link-field',
				'class' => 'text-input',
				'name' => 'custom_link'
			))
		));
	}
	
	/**
	 * Construct a list of menu items.
	 * @since 1.8.1-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @param string $type -- The menu item's type.
	 * @return string
	 */
	private function getMenuItemsList(int $id, string $type): string {
		global $rs_query;
		
		$list = array();
		
		if($type === 'post') {
			$post_type = $rs_query->selectField(getTable('p'), 'type', array(
				'id' => $id
			));
			
			$posts = $rs_query->select(getTable('p'), array('id', 'title'), array(
				'status' => 'published',
				'type' => $post_type
			));
			
			foreach($posts as $post) {
				$list[] = domTag('option', array(
					'value' => $post['id'],
					'selected' => $post['id'] === $id,
					'content' => $post['title']
				));
			}
		} elseif($type === 'term') {
			$taxonomy = $rs_query->selectField(getTable('t'), 'taxonomy', array(
				'id' => $id
			));
			
			$terms = $rs_query->select(getTable('t'), array('id', 'name'), array(
				'taxonomy' => $taxonomy
			));
			
			foreach($terms as $term) {
				$list[] = domTag('option', array(
					'value' => $term['id'],
					'selected' => $term['id'] === $id,
					'content' => $term['name']
				));
			}
		}
		
		return implode('', $list);
	}
	
	/**
	 * Determine a menu item's nested depth.
	 * @since 1.8.6-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @return int
	 */
	private function getMenuItemDepth(int $id): int {
		global $rs_query;
		
		$depth = -1;
		
		do {
			$parent = $rs_query->selectField(getTable('p'), 'parent', array(
				'id' => $id
			));
			
			$id = (int)$parent;
			$depth++;
		} while($id !== 0);
		
		return $depth;
	}
	
	/**
	 * Fetch a menu item's metadata.
	 * @since 1.8.1-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @return array
	 */
	private function getMenuItemMeta(int $id): array {
		global $rs_query;
		
		$itemmeta = $rs_query->select(getTable('pm'), array('key', 'value'), array(
			'post' => $id
		));
		
		$meta = array();
		
		foreach($itemmeta as $metadata) {
			$values = array_values($metadata);
			
			for($i = 0; $i < count($metadata); $i += 2)
				$meta[$values[$i]] = $values[$i + 1];
		}
		
		return $meta;
	}
	
	/**
	 * Fetch a list of menu items related to a menu.
	 * @since 2.3.2-alpha
	 *
	 * @access private
	 * @param int $exclude -- The menu item to exclude.
	 * @return array
	 */
	private function getMenuRelationships(int $exclude): array {
		global $rs_query;
		
		$relationships = $rs_query->select(getTable('tr'), 'post', array(
			'term' => $this->id
		));
		
		$itemmeta = array();
		$i = 0;
		
		foreach($relationships as $relationship) {
			$itemmeta[] = $this->getMenuItemMeta($relationship['post']);
			$itemmeta[$i] = array_reverse($itemmeta[$i]);
			$itemmeta[$i]['post'] = $relationship['post'];
			$i++;
		}
		
		// Sort the array in ascending index order
		asort($itemmeta);
		
		foreach($itemmeta as $meta) {
			if($meta['post'] === $exclude) continue;
			
			$items[] = $meta['post'];
		}
		
		return $items ?? array();
	}
	
	/**
	 * Fetch a menu item's parent.
	 * @since 1.8.12-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @return int
	 */
	private function getParent(int $id): int {
		global $rs_query;
		
		return (int)$rs_query->selectField(getTable('p'), 'parent', array(
			'id' => $id
		));
	}
	
	/**
	 * Construct a list of parents.
	 * @since 1.8.6-alpha
	 *
	 * @access private
	 * @param int $parent -- The menu item's parent.
	 * @param int $id -- The menu item's id.
	 * @return string
	 */
	private function getParentList(int $parent, int $id): string {
		global $rs_query;
		
		$list = $menu_items = array();
		$i = 0;
		
		$menu = $rs_query->selectField(getTable('tr'), 'term', array(
			'post' => $id
		));
		
		$relationships = $rs_query->select(getTable('tr'), 'post', array(
			'term' => $menu
		));
		
		foreach($relationships as $relationship) {
			$menu_items[] = $rs_query->selectRow(getTable('p'), array('title', 'id'), array(
				'id' => $relationship['post'],
				'type' => $this->item_post_type
			));
			
			$menu_items[$i]['menu_index'] = $rs_query->selectField(getTable('pm'), 'value', array(
				'post' => $relationship['post'],
				'key' => 'menu_index'
			));
			
			$menu_items[$i] = array_reverse($menu_items[$i]);
			$i++;
		}
		
		// Sort the array in ascending index order
		asort($menu_items);
		
		foreach($menu_items as $menu_item) {
			// Skip the current menu item
			if($menu_item['id'] === $id) continue;
			
			// Skip all descendant menu items
			if($this->isDescendant($menu_item['id'], $id)) continue;
			
			$list[] = domTag('option', array(
				'value' => $menu_item['id'],
				'selected' => $menu_item['id'] === $parent,
				'content' => $menu_item['title']
			));
		}
		
		return implode('', $list);
	}
	
	/**
	 * Fetch all siblings of a menu item.
	 * @since 2.3.3-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @return array
	 */
	private function getSiblings(int $id): array {
		global $rs_query;
		
		$menu_items = $this->getMenuRelationships($id);
		
		// Fetch any posts that share the same parent
		$posts = $rs_query->select(getTable('p'), 'id', array(
			'parent' => $this->getParent($id),
			'id' => array('<>', $id)
		));
		
		foreach($posts as $post)
			$same_parent[] = $post['id'];
		
		return isset($same_parent) ? array_intersect($menu_items, $same_parent) : array();
	}
	
	/**
	 * Fetch the previous sibling of a menu item.
	 * @since 1.8.12-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @return int
	 */
	private function getPreviousSibling(int $id): int {
		$siblings = $this->getSiblings($id);
		
		foreach($siblings as $sibling)
			if($this->isPreviousSibling($sibling, $id)) return $sibling;
	}
	
	/**
	 * Fetch the next sibling of a menu item.
	 * @since 1.8.12-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @return int
	 */
	private function getNextSibling(int $id): int {
		$siblings = $this->getSiblings($id);
		
		foreach($siblings as $sibling)
			if($this->isNextSibling($sibling, $id)) return $sibling;
	}
	
	/**
	 * Fetch the "family tree" of a menu item. Returns the number of members.
	 * @since 1.8.7-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 * @return int
	 */
	private function getFamilyTree(int $id): int {
		global $rs_query;
		
		$menu_item_id = $rs_query->selectField(getTable('p'), 'id', array(
			'id' => $id
		));
		
		if($menu_item_id) {
			$this->getDescendants($menu_item_id);
			$this->members++;
		}
		
		$members = $this->members;
		$this->members = 0;
		
		return $members;
	}
	
	/**
	 * Fetch all descendants of a menu item.
	 * @since 1.8.7-alpha
	 *
	 * @access private
	 * @param int $id -- The menu item's id.
	 */
	private function getDescendants(int $id): void {
		global $rs_query;
		
		$children = $rs_query->select(getTable('p'), 'id', array(
			'parent' => $id
		));
		
		foreach($children as $child) {
			$this->getDescendants($child['id']);
			$this->members++;
		}
	}
	
	/**
	 * Fetch all menus based on a specific status.
	 * @since 1.3.15-beta
	 *
	 * @access private
	 * @param null|string $search -- The search query.
	 * @param bool $all (optional) -- Whether to return all or set a limit (for pagination).
	 * @return array
	 */
	private function getResults(?string $search, bool $all = false): array {
		global $rs_query;
		
		$order_by = 'name';
		$order = 'ASC';
		$limit = $all === false ? array($this->paged['start'], $this->paged['per_page']) : 0;
		
		if(!is_null($search)) {
			// Search results
			return $rs_query->select(getTable('t'), '*', array(
				'name' => array('LIKE', '%' . $search . '%'),
				'taxonomy' => getTaxonomyId($this->tax_name)
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		} else {
			// All results
			return $rs_query->select(getTable('t'), '*', array(
				'taxonomy' => getTaxonomyId($this->tax_name)
			), array(
				'order_by' => $order_by,
				'order' => $order,
				'limit' => $limit
			));
		}
	}
	
	/**
	 * Fetch the menu count based on a specific status.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @param null|string $search -- The search query.
	 * @return int
	 */
	private function getEntryCount(?string $search): int {
		return count($this->getResults($search, true));
	}
	
	/**
	 * Fetch all associated action links.
	 * @since 1.3.16-beta
	 *
	 * @access private
	 * @param array $menu -- The menu's data.
	 * @return string
	 */
	private function getActionLinks(array $menu): string {
		$actions = $this->admin_page['actions'];
		$action_list = array();
		
		foreach($actions as $key => $value) {
			if($value === 'create') continue;
			
			$privileged = match($value) {
				'edit' => userHasPrivilege('can_edit_menus'),
				'delete' => userHasPrivilege('can_delete_menus'),
				default => null
			};
			
			$action_list[] = array(
				'privileged' => $privileged,
				'link' => $value,
				'caption' => ucfirst($value)
			);
		}
		
		list($edit, $delete) = $action_list;
		
		$action_links = array(
			// Edit
			$edit['privileged'] ? actionLink($edit['link'], array(
				'caption' => $edit['caption'],
				'id' => $menu['id']
			)) : null,
			// Delete
			$delete['privileged'] ? actionLink($delete['link'], array(
				'classes' => 'modal-launch delete-item',
				'data_item' => 'menu',
				'caption' => $delete['caption'],
				'id' => $menu['id']
			)) : null
		);
		
		// Filter out any empty actions
		$action_links = array_filter($action_links);
		
		return implode(' &bull; ', $action_links);
	}
}