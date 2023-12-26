<?php
/**
 * Generate a sitemap index.
 * @since 1.1.2-beta
 *
 * @package ReallySimpleCMS
 */

// Stop execution if the file is accessed directly
if(!defined('PATH')) exit('You do not have permission to access this directory.');

include_once PATH . INC . '/sitemap-posts.php';
include_once PATH . INC . '/sitemap-terms.php';

$sitemaps = array();

// Make sure that the home directory can be written to
if(is_writable(PATH)) {
	$sitemap_file_path = PATH . '/sitemap.xml';
	$robots_file_path = PATH . '/robots.txt';
	$handle = opendir(PATH);
	
	while(($entry = readdir($handle)) !== false) {
		// Check whether the current entry is a sitemap and assign it to the sitemaps array if so
		if(str_starts_with($entry, 'sitemap-')) $sitemaps[] = $entry;
	}
	
	foreach($sitemaps as $sitemap) {
		// Fetch the sitemap's name from the filename
		$name = substr($sitemap, strpos($sitemap, '-') + 1, strpos($sitemap, '.') - strpos($sitemap, '-') - 1);
		
		// Check whether the current sitemap is of a registered post type or taxonomy and delete it if not
		if(!in_array($name, $public_post_types, true) && !in_array($name, $public_taxonomies, true))
			unlink(trailingSlash(PATH) . $sitemap);
	}
	
	if(file_exists($sitemap_file_path)) {
		$file = simplexml_load_file($sitemap_file_path);
		
		// Fetch the number of sitemaps in the index
		$count = count($file->sitemap);
	}
	
	// Check whether the sitemap index already exists and whether the sitemap count matches the count in the root directory
	if(!file_exists($sitemap_file_path) || file_exists($sitemap_file_path) && $count !== count($sitemaps)) {
		$handle = fopen($sitemap_file_path, 'w');
		
		fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>' .
			'<?xml-stylesheet href="/includes/sitemap.xsl" type="text/xsl"?>' . chr(10) .
			'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . chr(10));
		
		foreach($sitemaps as $sitemap) {
			fwrite($handle, '<sitemap>' . chr(10) . '<loc>' .
				(!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . trailingSlash($_SERVER['HTTP_HOST']) .
				$sitemap . '</loc>' . chr(10) . '</sitemap>');
		}
		
		fwrite($handle, '</sitemapindex>');
		fclose($handle);
		
		// Set file permissions
		chmod($sitemap_file_path, 0666);
		
		if(file_exists($robots_file_path)) {
			$handle = fopen($robots_file_path, 'r');
			$contents = fread($handle, filesize($robots_file_path));
			fclose($handle);
			
			// Check whether a sitemap is defined in robots.txt
			if(!str_contains($contents, 'Sitemap:')) {
				$handle = fopen($robots_file_path, 'a');
				
				fwrite($handle, chr(10) . chr(10) . 'Sitemap: ' .
					(!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/sitemap.xml');
				fclose($handle);
			}
		} else {
			$handle = fopen($robots_file_path, 'w');
			
			fwrite($handle, 'Sitemap: ' . (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') .
				$_SERVER['HTTP_HOST'] . '/sitemap.xml');
			fclose($handle);
			
			// Set file permissions
			chmod($robots_file_path, 0666);
		}
	}
}