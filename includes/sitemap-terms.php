<?php
/**
 * Generate a sitemap for all terms.
 * @since 1.1.2[b]
 */

// Tell the browser to parse this as an xml file
header('Content-type: text/xml');

// Tell the CMS that it should only initialize the base files and functions
if(!defined('BASE_INIT')) define('BASE_INIT', true);

// Include the initialization file
require_once dirname(__DIR__).'/init.php';
?>
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
	<?php
	// Fetch all terms (excluding menus) from the database
	$terms = $rs_query->select('terms', array('id', 'slug', 'taxonomy', 'parent'), array('taxonomy'=>array('<>', getTaxonomyId('nav_menu'))), 'slug');
	
	// Loop through the terms
	foreach($terms as $term) {
		// Fetch the term's taxonomy from the database
		$taxonomy = $rs_query->selectField('taxonomies', 'name', array('id'=>$term['taxonomy']));
		
		// Construct the permalink
		$permalink = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].getPermalink($taxonomy, $term['parent'], $term['slug']);
		?>
		<url>
			<loc><?php echo $permalink; ?></loc>
		</url>
		<?php
	}
	?>
</urlset>