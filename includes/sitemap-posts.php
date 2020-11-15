<?php
/**
 * Generate sitemaps for all public posts.
 * @since 1.1.2[b]
 */

// Stop execution if the file is accessed directly
if(!defined('PATH')) exit('You do not have permission to access this directory.');

// Create an array to hold the public post types
$public_post_types = array();

// Loop through the registered post types
foreach($post_types as $post_type) {
	// Skip the post type if it's 'media'
	if($post_type['name'] === 'media') continue;
	
	// Check whether the post type is public an assign its name to the public array if so
	if($post_type['public']) $public_post_types[] = $post_type['name'];
}

// Loop through the public post types
foreach($public_post_types as $type) {
	// Make sure that the home directory can be written to
	if(is_writable(PATH)) {
		// File path for the sitemap
		$sitemap_file_path = PATH.'/sitemap-'.str_replace('_', '-', $type).'.xml';
		
		// Fetch the posts from the database
		$posts = $rs_query->select('posts', array('id', 'date', 'modified', 'slug', 'parent', 'type'), array('status'=>'published', 'type'=>$type), 'date', 'DESC');
		
		// Check whether the sitemap already exists
		if(file_exists($sitemap_file_path)) {
			// Load the sitemap
			$file = simplexml_load_file($sitemap_file_path);
			
			// Fetch the number of URLs in the sitemap
			$count = count($file->url);
		}
		
		// Check whether the sitemap already exists and whether the URL count matches the count in the database
		if(!file_exists($sitemap_file_path) || (file_exists($sitemap_file_path) && $count !== count($posts))) {
			// Open the file stream in write mode
			$handle = fopen($sitemap_file_path, 'w');
			
			// Begin writing to the file
			fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet href="/includes/sitemap.xsl" type="text/xsl"?>'.chr(10).'<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.chr(10));
			
			// Loop through the posts
			foreach($posts as $post) {
				// Construct the permalink
				$permalink = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].(isHomePage($post['id']) ? '/' : getPermalink($type, $post['parent'], $post['slug']));
				
				// Write the post to the file
				fwrite($handle, '<url>'.chr(10).'<loc>'.$permalink.'</loc>'.chr(10).'<lastmod>'.(formatDate((is_null($post['modified']) ? $post['date'] : $post['modified']), 'Y-m-d\TH:i:s')).'</lastmod>'.chr(10).'</url>'.chr(10));
			}
			
			// Finish writing to the file
			fwrite($handle, '</urlset>');
			
			// Close the file
			fclose($handle);
			
			// Set file permissions
			chmod($sitemap_file_path, 0666);
		}
	}
}