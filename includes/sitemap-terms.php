<?php
/**
 * Generate sitemaps for all public terms.
 * @since 1.1.2[b]
 */

// Stop execution if the file is accessed directly
if(!defined('PATH')) exit('You do not have permission to access this directory.');

$public_taxonomies = array();

foreach($taxonomies as $taxonomy)
	if($taxonomy['public']) $public_taxonomies[] = $taxonomy['name'];

foreach($public_taxonomies as $tax) {
	// Make sure that the home directory can be written to
	if(is_writable(PATH)) {
		$sitemap_file_path = PATH . '/sitemap-' . str_replace('_', '-', $tax) . '.xml';
		
		$terms = $rs_query->select('terms', array('id', 'slug', 'taxonomy', 'parent'), array(
			'taxonomy' => getTaxonomyId($tax)
		), 'slug');
		
		if(file_exists($sitemap_file_path)) {
			$file = simplexml_load_file($sitemap_file_path);
			
			// Fetch the number of URLs in the sitemap
			$count = count($file->url);
		}
		
		// Check whether the sitemap already exists and whether the URL count matches the count in the database
		if(!file_exists($sitemap_file_path) || (file_exists($sitemap_file_path) && $count !== count($terms))) {
			// Open the file stream in write mode
			$handle = fopen($sitemap_file_path, 'w');
			
			fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>' .
				'<?xml-stylesheet href="/includes/sitemap.xsl" type="text/xsl"?>' . chr(10) .
				'<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . chr(10));
			
			foreach($terms as $term) {
				$permalink = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] .
					getPermalink($tax, $term['parent'], $term['slug']);
				
				fwrite($handle, '<url>' . chr(10) . '<loc>' . $permalink . '</loc>' . chr(10) . '</url>' . chr(10));
			}
			
			fwrite($handle, '</urlset>');
			fclose($handle);
			
			// Set file permissions
			chmod($sitemap_file_path, 0666);
		}
	}
}