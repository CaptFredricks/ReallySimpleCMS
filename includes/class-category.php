<?php
/**
 * Core class used to implement the Category object. Inherits from the Term class.
 * @since 2.4.0[a]
 *
 * This class contains alias functions for the equivalent Term functions.
 */
class Category extends Term {
	/**
	 * Fetch a category's id.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getCategoryId($echo = true) {
		if($echo)
			echo $this->getTermId(false);
		else
			return $this->getTermId(false);
	}
	
	/**
	 * Fetch a category's name.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getCategoryName($echo = true) {
        if($echo)
            echo $this->getTermName(false);
        else
            return $this->getTermName(false);
    }
	
	/**
	 * Fetch a category's slug.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param int $id
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
    public function getCategorySlug($id, $echo = true) {
        if($echo)
            echo $this->getTermSlug($id, false);
        else
            return $this->getTermSlug($id, false);
    }
	
	/**
	 * Fetch a category's parent.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getCategoryParent($echo = true) {
        if($echo)
            echo $this->getTermParent(false);
        else
            return $this->getTermParent(false);
    }
	
	/**
	 * Fetch a category's full URL.
	 * @since 2.4.0[a]
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getCategoryUrl($echo = true) {
        if($echo)
            echo $this->getTermUrl(false);
        else
            return $this->getTermUrl(false);
    }
}