<?php
/**
 * Try to load a custom page template. Default to the current theme's index.php file if none are found.
 * @since 2.3.3[a]
 */

if(!is_null($theme_path)) {
	if(file_exists($theme_path . '/index.php')) {
		if(isPost()) {
			if(getPostType() === 'page') {
				$template = getPostMeta('template');
				
				// Check whether the template is valid
				if(!empty($template) && templateExists($template, $theme_path . '/templates')) {
					require_once $theme_path . '/templates/' . $template;
				} else {
					// Load either the generic 'page' template file or the index.php file as a last resort
					if(file_exists($theme_path . '/page.php'))
						require_once $theme_path . '/page.php';
					else
						require_once $theme_path . '/index.php';
				}
			} else {
				// Check whether a specific post type template file exists
				if(file_exists($theme_path . '/posttype-' . getPostType() . '.php')) {
					require_once $theme_path . '/posttype-' . getPostType() . '.php';
				} // Load either the generic 'post' template file or the index.php file as a last resort
				elseif(file_exists($theme_path . '/post.php')) {
					require_once $theme_path . '/post.php';
				} else {
					require_once $theme_path . '/index.php';
				}
			}
		} elseif(isTerm()) {
			if(getTermTaxonomy() === 'category') {
				// Check whether a 'category' template file exists
				if(file_exists($theme_path . '/category.php')) {
					require_once $theme_path . '/category.php';
				} // Load either the generic 'taxonomy' template file or the index.php file as a last resort
				elseif(file_exists($theme_path . '/taxonomy.php')) {
					require_once $theme_path . '/taxonomy.php';
				} else {
					require_once $theme_path . '/index.php';
				}
			} else {
				// Check whether a specific taxonomy template file exists
				if(file_exists($theme_path . '/taxonomy-' . getTermTaxonomy() . '.php')) {
					require_once $theme_path . '/taxonomy-' . getTermTaxonomy() . '.php';
				} // Load either the generic 'taxonomy' template file or the index.php file as a last resort
				elseif(file_exists($theme_path . '/taxonomy.php')) {
					require_once $theme_path . '/taxonomy.php';
				} else {
					require_once $theme_path . '/index.php';
				}
			}
		} else {
			require_once PATH . INC . '/fallback-theme.php';
		}
	} else {
		require_once PATH . INC . '/fallback-theme.php';
	}
} else {
	require_once PATH . INC . '/fallback-theme.php';
}