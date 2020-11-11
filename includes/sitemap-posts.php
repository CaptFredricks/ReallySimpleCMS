<?php
/**
 * Generate a sitemap for all posts.
 * @since 1.1.2[b]
 */

// Tell the browser to parse this as an xml file
header('Content-type: text/xml');

// Tell the CMS that it should only initialize the base files and functions
if(!defined('BASE_INIT')) define('BASE_INIT', true);

// Include the initialization file
require_once dirname(__DIR__).'/init.php';

// Display the XML header
echo '<?xml version="1.0" encoding="UTF-8"?>'.chr(10);
?>
<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
	<?php
	// Fetch all published posts from the database
	$posts = $rs_query->select('posts', array('id', 'date', 'modified', 'slug', 'parent', 'type'), array('status'=>'published'), 'date', 'DESC');
	
	// Loop through the posts
	foreach($posts as $post) {
		// Construct the permalink
		$permalink = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].(isHomePage($post['id']) ? '/' : getPermalink($post['type'], $post['parent'], $post['slug']));
		?>
		<url>
			<loc><?php echo $permalink; ?></loc>
			<lastmod><?php echo formatDate((is_null($post['modified']) ? $post['date'] : $post['modified']), 'Y-m-d'); ?></lastmod>
		</url>
		<?php
	}
	?>
</urlset>