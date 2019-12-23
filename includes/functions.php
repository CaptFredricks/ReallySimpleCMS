<?php
/**
 * Front end functions.
 * @since 1.0.0[a]
 */

// Autoload classes
spl_autoload_register(function($class_name) {
	require_once PATH.INC.'/class-'.strtolower($class_name).'.php';
});

// Generate a cookie hash based on the site's URL
define('COOKIE_HASH', md5(getSetting('site_url', false)));

/**
 * Include the theme's header file.
 * @since 1.5.5[a]
 *
 * @return null
 */
function getHeader() {
	include_once PATH.CONT.'/header.php';
}

/**
 * Include the theme's footer file.
 * @since 1.5.5[a]
 *
 * @return null
 */
function getFooter() {
	include_once PATH.CONT.'/footer.php';
}

/**
 * Fetch a post's data.
 * @since 2.2.0[a]
 *
 * @param int|string $post
 * @param string $callback
 * @param string|array $data (optional; default: '')
 * @return object
 */
function getPost($callback, $data = array()) {
	// Create a Post object
	$rs_post = new Post;
	
	// Check whether the data is an array and turn it into one if not
	if(!is_array($data)) $data = array($data);
	
	// Return the post's data
	return call_user_func_array(array($rs_post, 'getPost'.$callback), $data);
}

/**
 * Fetch a nav menu.
 * @since 2.2.2[a]
 *
 * @param string $slug
 * @return null
 */
function getMenu($slug) {
	// Extend the Query string
	global $rs_query;
	
	// Fetch the menu's id from the database
	$id = $rs_query->selectField('terms', 'id', array('slug'=>$slug));
	?>
	<nav class="nav-menu menu-id-<?php echo $id; ?>">
		<ul>
			<?php
			// Fetch the term relationships from the database
			$relationships = $rs_query->select('term_relationships', 'post', array('term'=>$id));
			
			// Create an empty array to hold the menu items' metadata
			$itemmeta = array();
			
			// Create an index counter for the metadata array
			$i = 0;
			
			// Loop through the term relationships
			foreach($relationships as $relationship) {
				// Fetch the metadata associated with each menu item from the database
				$itemmeta[] = getMenuItemMeta($relationship['post']);
				
				// Reverse the array (to place the index first)
				$itemmeta[$i] = array_reverse($itemmeta[$i]);
				
				// Push the menu item's id onto the array
				$itemmeta[$i]['post'] = $relationship['post'];
				
				// Increment the index counter
				$i++;
			}
			
			// Sort the array in ascending index order
			asort($itemmeta);
			
			// Loop through the menu items' metadata
			foreach($itemmeta as $meta) {
				// Fetch the menu item from the database
				$menu_item = $rs_query->selectRow('posts', array('id', 'title', 'parent'), array('id'=>$meta['post']));
				
				// Check what type of link is being used
				if(isset($meta['post_link']))
					$link = isHomePage((int)$meta['post_link']) ? '/' : getPermalink('post', getMenuItemParent($meta['post_link']));
				elseif(isset($meta['term_link']))
					$link = getPermalink('category', getMenuItemParent($meta['term_link']));
				elseif(isset($meta['custom_link']))
					$link = $meta['custom_link'];
				
				// Check whether the menu item has a parent or is on the top level
				if(!menuItemHasParent($menu_item['id'])) {
					?>
					<li<?php echo menuItemHasChildren($menu_item['id']) ? ' class="menu-item-has-children"' : ''; ?>>
						<a href="<?php echo $link; ?>"><?php echo $menu_item['title']; ?></a>
						<?php
						if(menuItemHasChildren($menu_item['id']))
							getMenuItemDescendants($menu_item['id']);
						?>
					</li>
					<?php
				}
			}
			?>
		</ul>
	</nav>
	<?php
}

/**
 * Check whether a menu item has a parent.
 * @since 2.2.2[a]
 *
 * @param int $id
 * @return bool
 */
function menuItemHasParent($id) {
	// Extend the Query class
	global $rs_query;
	
	// Fetch the menu item's parent id from the database and return true if it's not equal to zero
	return (int)$rs_query->selectField('posts', 'parent', array('id'=>$id)) !== 0;
}

/**
 * Check whether a menu item has children.
 * @since 2.2.2[a]
 *
 * @param int $id
 * @return bool
 */
function menuItemHasChildren($id) {
	// Extend the Query class
	global $rs_query;
	
	// Fetch the number of children the menu item has from the database and return true if it's greater than zero
	return $rs_query->select('posts', 'COUNT(*)', array('parent'=>$id)) > 0;
}

/**
 * Fetch a menu item's metadata.
 * @since 2.2.2[a]
 *
 * @param int $id
 * @return array
 */
function getMenuItemMeta($id) {
	// Extend the Query class
	global $rs_query;
	
	// Fetch the menu item's metadata from the database
	$itemmeta = $rs_query->select('postmeta', array('_key', 'value'), array('post'=>$id));
	
	// Create an empty array to hold the metadata
	$meta = array();
	
	// Loop through the metadata
	foreach($itemmeta as $metadata) {
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
 * Fetch a menu item's parent.
 * @since 2.2.2[a]
 *
 * @param int $id
 * @return int
 */
function getMenuItemParent($id) {
	// Extend the Query class
	global $rs_query;
	
	// Fetch the menu item's parent id from the database and return it
	return $rs_query->selectField('posts', 'id', array('id'=>$id));
}

/**
 * Fetch all descendants of a menu item.
 * @since 2.2.2[a]
 *
 * @param int $id
 * @return null
 */
function getMenuItemDescendants($id) {
	// Extend the Query class
	global $rs_query;
	?>
	<ul class="sub-menu">
		<?php
		// Select any existing children from the database
		$children = $rs_query->select('posts', array('id', 'title'), array('parent'=>$id));
		
		// Loop through the children
		foreach($children as $child) {
			// Fetch the menu item's metadata
			$meta = getMenuItemMeta($child['id']);
			
			// Check what type of link is being used
			if(isset($meta['post_link']))
				$link = isHomePage((int)$meta['post_link']) ? '/' : getPermalink('post', getMenuItemParent($meta['post_link']));
			elseif(isset($meta['term_link']))
				$link = getPermalink('category', getMenuItemParent($meta['term_link']));
			elseif(isset($meta['custom_link']))
				$link = $meta['custom_link'];
			?>
			<li<?php echo menuItemHasChildren($child['id']) ? ' class="menu-item-has-children"' : ''; ?>>
				<a href="<?php echo $link; ?>"><?php echo $child['title']; ?></a>
				<?php
				// Check whether the menu item has descendants
				if(menuItemHasChildren($child['id'])) {
					// Fetch the descendants of the menu item
					getMenuItemDescendants($child['id']);
				}
				?>
			</li>
			<?php
		}
		?>
	</ul>
	<?php
}

/**
 * Fetch a widget.
 * @since 2.2.1[a]
 *
 * @param string $slug
 * @param bool $display_title (optional; default: false)
 * @return null
 */
function getWidget($slug, $display_title = false) {
	// Extend the Query class
	global $rs_query;
	
	// Fetch the widget from the database
	$widget = $rs_query->selectRow('posts', array('title', 'content', 'status'), array('type'=>'widget', 'slug'=>$slug));
	
	// Check whether the widget exists and is active
	if(empty($widget)) {
		?>
		<div class="widget">
			<h3>The specified widget does not exist.</h3>
		</div>
		<?php
	} elseif($widget['status'] === 'inactive') {
		?>
		<div class="widget">
			<h3>The specified widget could not be loaded.</h3>
		</div>
		<?php
	} else {
		?>
		<div class="widget <?php echo $slug; ?>">
			<?php
			// Check whether the title should be displayed
			if($display_title) {
				?>
				<h3 class="widget-title"><?php echo $widget['title']; ?></h3>
				<?php
			}
			?>
			<div>
				<?php
				// Display the widget's content
				echo $widget['content'];
				?>
			</div>
		</div>
		<?php
	}
}

/**
 * Generate a random hash.
 * @since 2.0.5[a]
 *
 * @param int $length (optional; default: 20)
 * @param bool $special_chars (optional; default: true)
 * @param string $salt (optional; default: '')
 * @return string
 */
function generateHash($length = 20, $special_chars = true, $salt = '') {
	// Regular characters
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	
	// If desired, add the special characters
	if($special_chars) $chars .= '!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
	
	// Create an empty variable to hold the hash
	$hash = '';
	
	// Construct a randomized hash
	for($i = 0; $i < (int)$length; $i++)
		$hash .= substr($chars, rand(0, strlen($chars) - 1), 1);
	
	// Add any salt that's been provided and hash it with md5
	if(!empty($salt)) $hash = substr(md5(md5($hash.$salt)), 0, (int)$length);
	
	// Return the hash
	return $hash;
}

/**
 * Format an email message with HTML and CSS.
 * @since 2.0.5[a]
 *
 * @param string $heading
 * @param array $fields
 * @return string
 */
function formatEmail($heading, $fields) {
	$content = '<div style="background-color: #ededed; padding: 3rem 0;">';
	$content .= '<div style="background-color: #fdfdfd; border: 1px solid #cdcdcd; border-top-color: #ededed; color: #101010 !important; margin: 0 auto; padding: 0.75rem 1.5rem; width: 60%;">';
	$content .= !empty($heading) ? '<h2 style="text-align: center;">'.$heading.'</h2>' : '';
	$content .= !empty($fields['name']) && !empty($fields['email']) ? '<p style="margin-bottom: 0;"><strong>Name:</strong> '.$fields['name'].'</p><p style="margin-top: 0;"><strong>Email:</strong> '.$fields['email'].'</p>' : '';
	$content .= '<p style="border-top: 1px dashed #adadad; padding-top: 1em;">'.str_replace("\r\n", '<br>', $fields['message']).'</p>';
	$content .= '</div></div>';
	
	// Return the content
	return $content;
}