<?php
/**
 * Core class used to implement the Term object.
 * @since 2.4.0[a]
 *
 * This class loads data from the terms table of the database for use on the front end of the CMS.
 */
class Term {
	/**
	 * The currently queried term's slug.
	 * @since 2.4.0[a]
	 *
	 * @access private
	 * @var string
	 */
	private $slug;
	
	/**
	 * Class constructor. Sets the default queried term slug.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param string $slug (optional; default: '')
	 */
	public function __construct($slug = '') {
		// Extend the Query object
		global $rs_query;
		
		// Check whether a slug has been provided
		if(!empty($slug)) {
			// Fetch the slug value
			$this->slug = $slug;
		} else {
			// Fetch the term's URI
			$raw_uri = $_SERVER['REQUEST_URI'];
			
			// Create an array from the term's URI
			$uri = explode('/', $raw_uri);
			
			// Filter out any empty array values
			$uri = array_filter($uri);
			
			// Check whether the last element of the array is the slug
			if(str_starts_with(end($uri), '?')) {
				// Fetch the query string at the end of the array
				$query_string = array_pop($uri);
			}
			
			// Fetch the slug from the URI array
			$this->slug = array_pop($uri);
			
			// Fetch the term's id from the database
			$id = $this->getTermId();
			
			// Construct the term's permalink
			$permalink = getPermalink($this->getTermTaxonomy(), $this->getTermParent(), $this->getTermSlug($id));
			
			// Check whether the slug is valid and redirect to the 404 (Not Found) page if not
			if(empty($id)) redirect('/404.php');
			
			// Check whether the query string is set and concatenate it to the permalink if so
			if(isset($query_string)) $permalink .= $query_string;
			
			// Check whether the permalink is valid and redirect to the proper one if not
			if($raw_uri !== $permalink) redirect($permalink);
		}
	}
	
	/**
	 * Fetch the term's id.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @return int
	 */
	public function getTermId(): int {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the term's id from the database and return it
		return (int)$rs_query->selectField('terms', 'id', array('slug' => $this->slug));
	}
	
	/**
	 * Fetch the term's name.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getTermName(): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the term's name from the database and return it
		return $rs_query->selectField('terms', 'name', array('slug' => $this->slug));
    }
	
	/**
	 * Fetch the term's slug.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param int $id
	 * @return string
	 */
    public function getTermSlug($id): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the term's slug from the database and return it
		return $rs_query->selectField('terms', 'slug', array('id' => $id));
    }
	
	/**
	 * Fetch the term's taxonomy.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getTermTaxonomy(): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the term's taxonomy from the database
		$taxonomy = $rs_query->selectField('terms', 'taxonomy', array('slug' => $this->slug));
		
		// Fetch the taxonomy's name from the database and return it
		return $rs_query->selectField('taxonomies', 'name', array('id' => $taxonomy));
	}
	
	/**
	 * Fetch the term's parent.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @return int
	 */
	public function getTermParent(): int {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the term's parent from the database and return it
		return (int)$rs_query->selectField('terms', 'parent', array('slug' => $this->slug));
    }
	
	/**
	 * Fetch the term's full URL.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getTermUrl(): string {
		return getSetting('site_url').getPermalink($this->getTermTaxonomy(), $this->getTermParent(), $this->slug);
    }
}