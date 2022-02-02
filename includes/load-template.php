<?php
/**
 * Try to load a custom page template. Default to the current theme's index.php file if none are found.
 * @since 2.3.3[a]
 */

// Construct the file path for the current theme
$theme_path = trailingSlash(PATH.THEMES).getSetting('theme', false);

// Check whether the theme has an index.php file
if(file_exists($theme_path.'/index.php')) {
	// Check whether the current page is a post
	if(isPost()) {
		// Check whether the post is of type 'page'
		if(getPostType() === 'page') {
			// Fetch the page's template
			$template = getPostMeta('template');
			
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
			// Check whether a specific post type template file exists
			if(file_exists($theme_path.'/posttype-'.getPostType().'.php')) {
				// Include the template file
				require_once $theme_path.'/posttype-'.getPostType().'.php';
			} // Check whether a generic 'post' template file exists
			elseif(file_exists($theme_path.'/post.php')) {
				// Include the template file
				require_once $theme_path.'/post.php';
			} else {
				// Include the theme's index file
				require_once $theme_path.'/index.php';
			}
		}
	} // Check whether the Term object is set
	elseif(isTerm()) {
		// Check whether the term is in the 'category' taxonomy
		if(getTermTaxonomy() === 'category') {
			// Check whether a 'category' template file exists
			if(file_exists($theme_path.'/category.php')) {
				// Include the template file
				require_once $theme_path.'/category.php';
			} // Check whether a generic 'taxonomy' template file exists
			elseif(file_exists($theme_path.'/taxonomy.php')) {
				// Include the template file
				require_once $theme_path.'/taxonomy.php';
			} else {
				// Include the theme's index file
				require_once $theme_path.'/index.php';
			}
		} else {
			// Check whether a specific taxonomy template file exists
			if(file_exists($theme_path.'/taxonomy-'.getTermTaxonomy().'.php')) {
				// Include the template file
				require_once $theme_path.'/taxonomy-'.getTermTaxonomy().'.php';
			} // Check whether a generic 'taxonomy' template file exists
			elseif(file_exists($theme_path.'/taxonomy.php')) {
				// Include the template file
				require_once $theme_path.'/taxonomy.php';
			} else {
				// Include the theme's index file
				require_once $theme_path.'/index.php';
			}
		}
	} else {
		// Load the fallback theme
		require_once PATH.INC.'/fallback.php';
	}
} else {
	// Load the fallback theme
	require_once PATH.INC.'/fallback.php';
}