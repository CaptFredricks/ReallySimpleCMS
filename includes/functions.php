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
 * Include the theme's header template.
 * @since 1.5.5[a]
 *
 * @return null
 */
function getHeader() {
	// Extend the Post object and the user's session data
	global $rs_post, $session;
	
	// Include the header template
	require_once PATH.CONT.'/header.php';
}

/**
 * Include the theme's footer template.
 * @since 1.5.5[a]
 *
 * @return null
 */
function getFooter() {
	// Extend the Post object and the user's session data
	global $rs_post, $session;
	
	// Include the footer template
	require_once PATH.CONT.'/footer.php';
}

/**
 * Create a Post object based on a provided slug.
 * @since 2.2.3[a]
 *
 * @param string $slug
 * @return object
 */
function getPost($slug) {
	return new Post($slug);
}

/**
 * Fetch a nav menu.
 * @since 2.2.3[a]
 *
 * @param string $slug
 * @return null
 */
function getMenu($slug) {
	// Create a Menu object
	$rs_menu = new Menu;
	
	// Display the menu
	$rs_menu->getMenu($slug);
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
	// Extend the Query object
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
			<div class="widget-content">
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
 * Construct a list of CSS classes for the body tag.
 * @since 2.2.3[a]
 *
 * @param array $addtl_classes (optional; default: array())
 * @return string
 */
function bodyClasses($addtl_classes = array()) {
	// Extend the Post object and the user's session data
	global $rs_post, $session;
	
	// Fetch the post's id from the database
	$id = $rs_post->getPostId(false);
	
	// Fetch the post's parent from the database
	$parent = $rs_post->getPostParent(false);
	
	// Fetch the post's slug from the database and add an appropriate class
	$classes[] = $rs_post->getPostSlug($id, false);
	
	// Fetch the post's type from the database and add an appropriate class (along with the id)
	$classes[] = $rs_post->getPostType(false).'-id-'.$id;
	
	// Check whether the current page is a child of another page and add an appropriate class if so
	if($parent !== 0) $classes[] = $rs_post->getPostSlug($parent, false).'-child';
	
	// Check whether the current page is the home page and add an appropriate class if so
	if(isHomePage($id)) $classes[] = 'home-page';

	// Check whether the user is logged in and add an appropriate class if so
	if($session) $classes[] = 'logged-in';
	
	// Merge any additional classes with the classes array
	$classes = array_merge($classes, (array)$addtl_classes);
	
	// Return the classes as a string
	return implode(' ', $classes);
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