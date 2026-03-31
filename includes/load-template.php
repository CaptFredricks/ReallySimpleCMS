<?php
/**
 * Try to load a custom page template. Default to the current theme's `index.php` file if none are found.
 * @since 2.3.3-alpha
 *
 * @package ReallySimpleCMS
 */

$is_broken_theme = false;

if(is_null($rs_theme_path) || !file_exists($rs_theme_path . '/index.php'))
	$is_broken_theme = true;

if($is_broken_theme === true) {
	// Theme is broken, use fallback theme
	requireFile(PATH . UTILS . '/fallback-theme.php');
} else {
	if(isPost()) {
		if(getPostType() === 'page') {
			$template = getPostMeta('template');
			
			// Check whether the template is valid
			if(!empty($template) && templateExists($template, $rs_theme_path . '/templates')) {
				requireFile($rs_theme_path . '/templates/' . $template);
			} else {
				// Load either the generic 'page' template file or the `index.php` file as a last resort
				if(file_exists($rs_theme_path . '/homepage.php') && $_SERVER['REQUEST_URI'] === '/')
					requireFile($rs_theme_path . '/homepage.php');
				elseif(file_exists($rs_theme_path . '/page.php'))
					requireFile($rs_theme_path . '/page.php');
				else
					requireFile($rs_theme_path . '/index.php');
			}
		} else {
			// Check whether a specific post type template file exists
			if(file_exists($rs_theme_path . '/posttype-' . getPostType() . '.php')) {
				requireFile($rs_theme_path . '/posttype-' . getPostType() . '.php');
			} // Load either the generic 'post' template file or the `index.php` file as a last resort
			elseif(file_exists($rs_theme_path . '/post.php')) {
				requireFile($rs_theme_path . '/post.php');
			} else {
				requireFile($rs_theme_path . '/index.php');
			}
		}
	} elseif(isTerm()) {
		if(getTermTaxonomy() === 'category') {
			// Check whether a 'category' template file exists
			if(file_exists($rs_theme_path . '/category.php')) {
				requireFile($rs_theme_path . '/category.php');
			} // Load either the generic 'taxonomy' template file or the `index.php` file as a last resort
			elseif(file_exists($rs_theme_path . '/taxonomy.php')) {
				requireFile($rs_theme_path . '/taxonomy.php');
			} else {
				requireFile($rs_theme_path . '/index.php');
			}
		} else {
			// Check whether a specific taxonomy template file exists
			if(file_exists($rs_theme_path . '/taxonomy-' . getTermTaxonomy() . '.php')) {
				requireFile($rs_theme_path . '/taxonomy-' . getTermTaxonomy() . '.php');
			} // Load either the generic 'taxonomy' template file or the `index.php` file as a last resort
			elseif(file_exists($rs_theme_path . '/taxonomy.php')) {
				requireFile($rs_theme_path . '/taxonomy.php');
			} else {
				requireFile($rs_theme_path . '/index.php');
			}
		}
	} else {
		// Unrecognized page type
		requireFile(PATH . UTILS . '/fallback-theme.php');
	}
}