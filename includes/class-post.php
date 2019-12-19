<?php
/**
 * Core class used to implement the Post object.
 * @since 1.0.2[a]
 *
 * This class loads data from the posts table of the database for use on the front end of the CMS.
 */
class Post {
	/**
	 * Fetch a post's id.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param string $slug
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getPostId($slug, $echo = true) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the post's id from the database
		$id = (int)$rs_query->selectField('posts', 'id', array('slug'=>$slug));
		
		if($echo)
			echo $id;
		else
			return $id;
	}
	
	/**
	 * Fetch a post's title.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param string $slug
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostTitle($slug, $echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's title from the database
        $title = $rs_query->selectField('posts', 'title', array('slug'=>$slug));
		
        if($echo)
            echo $title;
        else
            return $title;
    }
	
	/**
	 * Fetch a post's author.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param string $slug
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostAuthor($slug, $echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's author from the database
        $author = $rs_query->selectField('posts', 'author', array('slug'=>$slug));
		
		// Fetch the author's username from the database
		$username = $rs_query->selectField('users', 'username', array('id'=>$author));
		
        if($echo)
            echo $username;
        else
            return $username;
	}
	
	/**
	 * Fetch a post's publish date.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param string $slug
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostDate($slug, $echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's title from the database
        $date = $rs_query->selectField('posts', 'date', array('slug'=>$slug));
		
        if($echo)
            echo formatDate($date, 'd M Y @ g:i A');
        else
            return formatDate($date, 'd M Y @ g:i A');
    }
	
	/**
	 * Fetch a post's modified date.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param string $slug
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostModDate($slug, $echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's title from the database
        $modified = $rs_query->selectField('posts', 'modified', array('slug'=>$slug));
		
        if($echo)
            echo formatDate($modified, 'd M Y @ g:i A');
        else
            return formatDate($modified, 'd M Y @ g:i A');
    }
	
	/**
	 * Fetch a post's content.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param string $slug
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostContent($slug, $echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's content from the database
        $content = $rs_query->selectField('posts', 'content', array('slug'=>$slug));
		
		// Filter out any HTML or JavaScript comments
        //$filtered_content = $this->removeComments($post['content']);
		
        if($echo)
            echo $content;
        else
            return $content;
    }
	
	/**
	 * Fetch a post's status.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param string $slug
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostStatus($slug, $echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's status from the database
        $status = $rs_query->selectField('posts', 'status', array('slug'=>$slug));
		
        if($echo)
            echo $status;
        else
            return $status;
    }
	
	/**
	 * Fetch a post's slug.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param string $slug
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
    public function getPostSlug($id, $echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's slug from the database
        $slug = $rs_query->selectField('posts', 'slug', array('id'=>$id));
		
        if($echo)
            echo $slug;
        else
            return $slug;
    }
	
	/**
	 * Fetch a post's parent.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param string $slug
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostParent($slug, $echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's parent from the database
        $parent = $rs_query->selectField('posts', 'parent', array('slug'=>$slug));
		
        if($echo)
            echo $parent;
        else
            return $parent;
    }
	
	/**
	 * Fetch a post's type.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param string $slug
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostType($slug, $echo = true) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the post's type from the database
		$type = $rs_query->selectField('posts', 'type', array('slug'=>$slug));
		
		if($echo)
			echo $type;
		else
			return $type;
	}
	
	/**
	 * Fetch a post's featured image.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param string $slug
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostFeatImage($slug, $echo = true) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the featured image's id from the database
		$feat_image = (int)$rs_query->selectField('postmeta', 'value', array('post'=>$this->getPostId($slug, false), '_key'=>'feat_image'));
		
        if($echo)
            echo getMedia($feat_image, 'featured-image');
        else
            return getMedia($feat_image, 'featured-image');
    }
}