<?php
/**
 * Try to load a custom page template. Default to the current theme's index.php file if none are found.
 * @since 2.3.3[a]
 */

// Construct the file path for the current theme
$theme_path = trailingSlash(PATH.THEMES).getSetting('theme', false);

// Check whether the Post object is set
if(isset($rs_post)) {
	// Check whether the post is of type 'page'
	if($rs_post->getPostType(false) === 'page') {
		// Fetch the page's template
		$template = $rs_post->getPostMeta('template', false);
		
		// Check whether the template is valid
		if(!empty($template) && templateExists($template, $theme_path.'/templates')) {
			// Include the template file
			require_once $theme_path.'/templates/'.$template;
		} else {
			// Check whether a generic 'page' template file exists
			if(file_exists($theme_path.'/page.php')) {
				// Include the template file
				require_once $theme_path.'/page.php';
			} else {
				// Include the theme's index file
				require_once $theme_path.'/index.php';
			}
		}
	} else {
		// Check whether a generic 'post' template file exists
		if(file_exists($theme_path.'/post.php')) {
			// Include the template file
			require_once $theme_path.'/post.php';
		} else {
			// Include the theme's index file
			require_once $theme_path.'/index.php';
		}
	}
} else {
	// Check whether a generic 'category' template file exists
	if(file_exists($theme_path.'/category.php')) {
		// Include the template file
		require_once $theme_path.'/category.php';
	} else {
		// Include the theme's index file
		require_once $theme_path.'/index.php';
	}
}