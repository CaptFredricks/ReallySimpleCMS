<?php
/**
 * Sitemap generators. Sitemaps are used to help search engines better index your website.
 * @since 1.1.2-beta
 *
 * @package ReallySimpleCMS
 *
 * ## FUNCTIONS [3] ##
 * - genSitemaps(): void
 * - genPostSitemaps(): array
 * - genTermSitemaps(): array
 */

// Stop execution if the file is accessed directly
if(!defined('PATH')) exit('You do not have permission to access this resource.');

genSitemaps();

/**
 * Generate all sitemaps and the sitemap index.
 * @since 1.4.0-beta_snap-04
 */
function genSitemaps(): void {
	$sitemaps = array();
	$sitemap_file_path = PATH . '/sitemap.xml';
	$robots_file_path = PATH . '/robots.txt';
	
	if(is_writable(PATH)) {	
		// Generate sitemaps
		$public_post_types = genPostSitemaps();
		$public_taxonomies = genTermSitemaps();
		
		$handle = opendir(PATH);
		
		while(($entry = readdir($handle)) !== false)
			if(str_starts_with($entry, 'sitemap-')) $sitemaps[] = $entry;
		
		foreach($sitemaps as $sitemap) {
			// Fetch the sitemap's name from the filename
			$name = substr($sitemap, strpos($sitemap, '-') + 1, strpos($sitemap, '.') - strpos($sitemap, '-') - 1);
			
			if(!in_array($name, $public_post_types, true) && !in_array($name, $public_taxonomies, true))
				unlink(slash(PATH) . $sitemap);
		}
		
		if(file_exists($sitemap_file_path)) {
			$file = simplexml_load_file($sitemap_file_path);
			$count = count($file->sitemap);
		}
		
		// Generate the sitemap index
		if(!file_exists($sitemap_file_path) || file_exists($sitemap_file_path) && $count !== count($sitemaps)) {
			$handle = fopen($sitemap_file_path, 'w');
			
			fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>' .
				'<?xml-stylesheet href="' . UTILS . '/sitemap.xsl" type="text/xsl"?>' . chr(10) .
				'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . chr(10));
			
			foreach($sitemaps as $sitemap) {
				fwrite($handle, '<sitemap>' . chr(10) . '<loc>' . (isSecureConnection() ? 'https://' : 'http://') .
					slash($_SERVER['HTTP_HOST']) . $sitemap . '</loc>' . chr(10) . '</sitemap>');
			}
			
			fwrite($handle, '</sitemapindex>');
			fclose($handle);
			
			// Set file permissions
			chmod($sitemap_file_path, 0666);
			
			if(file_exists($robots_file_path)) {
				$handle = fopen($robots_file_path, 'r');
				$contents = fread($handle, filesize($robots_file_path));
				
				fclose($handle);
				
				// Check whether a sitemap is defined in `robots.txt`
				if(!str_contains($contents, 'Sitemap:')) {
					$handle = fopen($robots_file_path, 'a');
					
					fwrite($handle, chr(10) . chr(10) . 'Sitemap: ' . (isSecureConnection() ? 'https://' : 'http://') .
						$_SERVER['HTTP_HOST'] . '/sitemap.xml');
					
					fclose($handle);
				}
			} else {
				$handle = fopen($robots_file_path, 'w');
				
				fwrite($handle, 'Sitemap: ' . (isSecureConnection() ? 'https://' : 'http://') .
					$_SERVER['HTTP_HOST'] . '/sitemap.xml');
				
				fclose($handle);
				
				// Set file permissions
				chmod($robots_file_path, 0666);
			}
		}
	}
}

/**
 * Generate all Post sitemaps.
 * @since 1.4.0-beta_snap-04
 *
 * @return array
 */
function genPostSitemaps(): array {
	global $rs_query, $rs_post_types;
	
	$public_post_types = array();
	
	foreach($rs_post_types as $post_type) {
		// Skip the `media` post type
		if($post_type['name'] === 'media') continue;
		
		if($post_type['public'] === true) $public_post_types[] = $post_type['name'];
	}
	
	if(is_writable(PATH)) {
		foreach($public_post_types as $type) {
			$sitemap_file_path = PATH . '/sitemap-' . str_replace('_', '-', $type) . '.xml';
			
			$posts = $rs_query->select(getTable('p'), array('id', 'created', 'modified', 'slug', 'parent', 'type'), array(
				'status' => 'published',
				'type' => $type
			), array(
				'order_by' => 'created',
				'order' => 'DESC'
			));
			
			if(file_exists($sitemap_file_path)) {
				$file = simplexml_load_file($sitemap_file_path);
				$count = count($file->url);
			}
			
			if(!file_exists($sitemap_file_path) || (file_exists($sitemap_file_path) && $count !== count($posts))) {
				$handle = fopen($sitemap_file_path, 'w');
				
				fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>' .
					'<?xml-stylesheet href="' . UTILS . '/sitemap.xsl" type="text/xsl"?>' . chr(10) .
					'<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . chr(10));
				
				foreach($posts as $post) {
					$permalink = (isSecureConnection() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] .
						(isHomePage($post['p_id']) ? '/' : getPermalink($type, $post['p_parent'], $post['p_slug']));
					
					fwrite($handle, '<url>' . chr(10) . '<loc>' . $permalink . '</loc>' . chr(10) .
						'<lastmod>' . (formatDate((is_null($post['p_modified']) ? $post['p_created'] :
							$post['p_modified']), 'Y-m-d\TH:i:s')) . '</lastmod>' . chr(10) . '</url>' . chr(10));
				}
				
				fwrite($handle, '</urlset>');
				fclose($handle);
				
				// Set file permissions
				chmod($sitemap_file_path, 0666);
			}
		}
	}
	
	return $public_post_types;
}

/**
 * Generate all Term sitemaps.
 * @since 1.4.0-beta_snap-04
 *
 * @return array
 */
function genTermSitemaps(): array {
	global $rs_query, $rs_taxonomies;
	
	$public_taxonomies = array();
	
	foreach($rs_taxonomies as $taxonomy)
		if($taxonomy['public'] === true) $public_taxonomies[] = $taxonomy['name'];
	
	if(is_writable(PATH)) {
		foreach($public_taxonomies as $tax) {
			$sitemap_file_path = PATH . '/sitemap-' . str_replace('_', '-', $tax) . '.xml';
			
			$terms = $rs_query->select(getTable('t'), array('id', 'slug', 'taxonomy', 'parent'), array(
				'taxonomy' => getTaxonomyId($tax)
			), array(
				'order_by' => 'slug'
			));
			
			if(file_exists($sitemap_file_path)) {
				$file = simplexml_load_file($sitemap_file_path);
				$count = count($file->url);
			}
			
			if(!file_exists($sitemap_file_path) || (file_exists($sitemap_file_path) && $count !== count($terms))) {
				$handle = fopen($sitemap_file_path, 'w');
				
				fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>' .
					'<?xml-stylesheet href="' . UTILS . '/sitemap.xsl" type="text/xsl"?>' . chr(10) .
					'<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . chr(10));
				
				foreach($terms as $term) {
					$permalink = (isSecureConnection() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] .
						getPermalink($tax, $term['t_parent'], $term['t_slug']);
					
					fwrite($handle, '<url>' . chr(10) . '<loc>' . $permalink . '</loc>' . chr(10) . '</url>' . chr(10));
				}
				
				fwrite($handle, '</urlset>');
				fclose($handle);
				
				// Set file permissions
				chmod($sitemap_file_path, 0666);
			}
		}
	}
	
	return $public_taxonomies;
}