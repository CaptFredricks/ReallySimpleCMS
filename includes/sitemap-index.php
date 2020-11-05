<?php
/**
 * Generate a sitemap index.
 * @since 1.1.2[b]
 */

// File path for the sitemap index
$sitemap_file_path = PATH.'/sitemap.xml';

// File path for the robots.txt file
$robots_file_path = PATH.'/robots.txt';

// Make sure that the home directory can be written to
if(is_writable(PATH) && !file_exists($sitemap_file_path)) {
	// The existing sitemaps to point to
	$sitemaps = array('sitemap-posts.php', 'sitemap-terms.php');
	
	// Open the file stream
	$handle = fopen($sitemap_file_path, 'w');
	
	// Begin writing to the file
	fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>'.chr(10).'<sitemapindex xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">'.chr(10));
	
	// Loop through the sitemaps and write them to the file
	foreach($sitemaps as $sitemap)
		fwrite($handle, '<sitemap>'.chr(10).'<loc>'.(!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').trailingSlash($_SERVER['HTTP_HOST']).$sitemap.'</loc>'.chr(10).'</sitemap>');
	
	// Finish writing to the file
	fwrite($handle, '</sitemapindex>');
	
	// Close the file
	fclose($handle);
	
	// Set file permissions
	chmod($sitemap_file_path, 0666);
	
	// Check whether a robots.txt file exists
	if(file_exists($robots_file_path)) {
		// Open the file stream
		$handle = fopen($robots_file_path, 'a');
		
		// Write to the file
		fwrite($handle, chr(10).chr(10).'Sitemap: '.(!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].'/sitemap.xml');
		
		// Close the file
		fclose($handle);
	} else {
		// Open the file stream
		$handle = fopen($robots_file_path, 'w');
		
		// Write to the file
		fwrite($handle, 'Sitemap: '.(!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].'/sitemap.xml');
		
		// Close the file
		fclose($handle);
		
		// Set file permissions
		chmod($robots_file_path, 0666);
	}
}