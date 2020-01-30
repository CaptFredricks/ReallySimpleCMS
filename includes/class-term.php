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
	 * @return null
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
			
			// Fetch the slug from the URI array
			$this->slug = array_pop($uri);
			
			// Fetch the term's id from the database
			$id = $this->getTermId(false);
			
			// Construct the term's permalink
			$permalink = getPermalink($this->getTermTaxonomy(false), $this->getTermParent(false), $this->getTermSlug($id, false));
			
			// Check whether the slug is valid and redirect to the 404 (Not Found) page if not
			if(empty($id)) redirect('/404.php');
			
			// Check whether the permalink is valid and redirect to the proper one if not
			if($raw_uri !== $permalink) redirect($permalink);
		}
	}
	
	/**
	 * Fetch a term's id.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getTermId($echo = true) {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the term's id from the database
		$id = (int)$rs_query->selectField('terms', 'id', array('slug'=>$this->slug));
		
		if($echo)
			echo $id;
		else
			return $id;
	}
	
	/**
	 * Fetch a term's name.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getTermName($echo = true) {
		// Extend the Query object
        global $rs_query;
		
		// Fetch the term's name from the database
        $name = $rs_query->selectField('terms', 'name', array('slug'=>$this->slug));
		
        if($echo)
            echo $name;
        else
            return $name;
    }
	
	/**
	 * Fetch a term's slug.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param int $id
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
    public function getTermSlug($id, $echo = true) {
		// Extend the Query object
        global $rs_query;
		
		// Fetch the term's slug from the database
        $slug = $rs_query->selectField('terms', 'slug', array('id'=>$id));
		
        if($echo)
            echo $slug;
        else
            return $slug;
    }
	
	/**
	 * Fetch a term's taxonomy.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getTermTaxonomy($echo = true) {
		// Extend the Query object
        global $rs_query;
		
		// Fetch the term's taxonomy from the database
        $taxonomy = $rs_query->selectField('terms', 'taxonomy', array('slug'=>$this->slug));
		
		// Fetch the taxonomy's name from the database
		$tax_name = $rs_query->selectField('taxonomies', 'name', array('id'=>$taxonomy));
		
        if($echo)
            echo $tax_name;
        else
            return $tax_name;
	}
	
	/**
	 * Fetch a term's parent.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getTermParent($echo = true) {
		// Extend the Query object
        global $rs_query;
		
		// Fetch the term's parent from the database
        $parent = (int)$rs_query->selectField('terms', 'parent', array('slug'=>$this->slug));
		
        if($echo)
            echo $parent;
        else
            return $parent;
    }
	
	/**
	 * Fetch a term's full URL.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getTermUrl($echo = true) {
        if($echo)
            echo getSetting('site_url', false).getPermalink($this->getTermTaxonomy(false), $this->getTermParent(false), $this->slug);
        else
            return getSetting('site_url', false).getPermalink($this->getTermTaxonomy(false), $this->getTermParent(false), $this->slug);
    }
}