<?php
/**
 * A list of deprecated functions that may be used again later on (ordered by descending deprecated version).
 * @since 1.1.0[a]
 * @deprecated since 1.3.12[b]
 */

/**
 * Check whether a filename exists in the database.
 * @since 2.1.0[a]
 * @deprecated since 1.0.9[b]
 *
 * @param string $filename
 * @return bool
 */
function filenameExists($filename) {
	// Extend the Query object
	global $rs_query;
	
	// Return true if the filename appears in the database
	return $rs_query->select('postmeta', 'COUNT(*)', array('_key' => 'filename', 'value' => array('LIKE', $filename.'%'))) > 0;
}

/**
 * Check whether the current 'page' is a category archive.
 * @since 2.4.0[a]
 * @deprecated since 1.0.6[b]
 *
 * @param string $base (optional; default: 'category')
 * @return bool
 */
function isCategory($base = 'category') {
	return strpos($_SERVER['REQUEST_URI'], $base) !== false;
}

/**
 * Construct a post's permalink. (Admin Post class)
 * @since 1.4.9[a]
 * @deprecated since 1.0.0[b]
 *
 * @access private
 * @param int $parent
 * @param string $slug (optional; default: '')
 * @return string
 */
private function getPermalink($parent, $slug = '') {
	return getPermalink('post', $parent, $slug);
}

/**
 * Fetch the slug from the URL.
 * @since 2.2.3[a]
 * @deprecated since 2.2.5[a]
 *
 * @return string
 */
function getPageSlug() {
	// Check whether the current page is the home page
	if($_SERVER['REQUEST_URI'] === '/') {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the home page's id from the database
		$home_page = $rs_query->selectField('settings', 'value', array('name' => 'home_page'));
		
		// Create a Post object
		$rs_post = new Post;
		
		// Return the slug
		return $rs_post->getPostSlug($home_page, false);
	} else {
		// Create an array from the page's URI
		$uri = explode('/', $_SERVER['REQUEST_URI']);
		
		// Return the slug
		return array_pop($uri);
	}
}

/**
 * Fetch a post's data.
 * @since 2.2.0[a]
 * @deprecated since 2.2.3[a]
 *
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