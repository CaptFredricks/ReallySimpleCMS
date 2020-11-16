<?php
/**
 * Generate a sitemap index.
 * @since 1.1.2[b]
 */

// Stop execution if the file is accessed directly
if(!defined('PATH')) exit('You do not have permission to access this directory.');

// Include the posts sitemap generator
include_once PATH.INC.'/sitemap-posts.php';

// Include the terms sitemap generator
include_once PATH.INC.'/sitemap-terms.php';

// Create an array to hold the sitemaps
$sitemaps = array();

// Make sure that the home directory can be written to
if(is_writable(PATH)) {
	// File path for the sitemap index
	$sitemap_file_path = PATH.'/sitemap.xml';
	
	// File path for the robots.txt file
	$robots_file_path = PATH.'/robots.txt';
	
	// Open the directory handle
	$handle = opendir(PATH);
	
	// Loop through the directory's contents
	while(($entry = readdir($handle)) !== false) {
		// Check whether the current entry is a sitemap and assign it to the sitemaps array if so
		if(strpos($entry, 'sitemap-') !== false) $sitemaps[] = $entry;
	}
	
	// Loop through the sitemaps
	foreach($sitemaps as $sitemap) {
		// Fetch the sitemap's name from the filename
		$name = substr($sitemap, strpos($sitemap, '-') + 1, strpos($sitemap, '.') - strpos($sitemap, '-') - 1);
		
		// Check whether the current sitemap is of a registered post type or taxonomy and delete it if not
		if(!in_array($name, $public_post_types, true) && !in_array($name, $public_taxonomies, true)) unlink(trailingSlash(PATH).$sitemap);
	}
	
	// Check whether the sitemap index already exists
	if(file_exists($sitemap_file_path)) {
		// Load the sitemap index
		$file = simplexml_load_file($sitemap_file_path);
		
		// Fetch the number of sitemaps in the index
		$count = count($file->sitemap);
	}
	
	// Check whether the sitemap index already exists and whether the sitemap count matches the count in the root directory
	if(!file_exists($sitemap_file_path) || file_exists($sitemap_file_path) && $count !== count($sitemaps)) {
		// Open the file stream in write mode
		$handle = fopen($sitemap_file_path, 'w');
		
		// Begin writing to the file
		fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet href="/includes/sitemap.xsl" type="text/xsl"?>'.chr(10).'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.chr(10));
		
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
			// Open the file stream in read mode
			$handle = fopen($robots_file_path, 'r');
			
			// Fetch the contents of the file
			$contents = fread($handle, filesize($robots_file_path));
			
			// Close the file
			fclose($handle);
			
			// Check whether a sitemap is defined in robots.txt
			if(strpos($contents, 'Sitemap:') === false) {
				// Open the file stream in append mode
				$handle = fopen($robots_file_path, 'a');
				
				// Write to the file
				fwrite($handle, chr(10).chr(10).'Sitemap: '.(!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].'/sitemap.xml');
				
				// Close the file
				fclose($handle);
			}
		} else {
			// Open the file stream in write mode
			$handle = fopen($robots_file_path, 'w');
			
			// Write to the file
			fwrite($handle, 'Sitemap: '.(!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].'/sitemap.xml');
			
			// Close the file
			fclose($handle);
			
			// Set file permissions
			chmod($robots_file_path, 0666);
		}
	}
}