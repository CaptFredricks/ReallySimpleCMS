<?php
/**
 * Core class used to implement the Post object.
 * @since 1.0.2[a]
 *
 * This class loads data from the posts table of the database for use on the front end of the CMS.
 */
class Post {
	/**
	 * The currently queried post's slug.
	 * @since 2.2.3[a]
	 *
	 * @access private
	 * @var string
	 */
	private $slug;
	
	/**
	 * Class constructor. Sets the default queried post slug.
	 * @since 2.2.3[a]
	 *
	 * @access public
	 * @param string $slug (optional; default: '')
	 * @return null
	 */
	public function __construct($slug = '') {
		$this->slug = $slug;
	}
	
	/**
	 * Fetch a post's id.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getPostId($echo = true) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the post's id from the database
		$id = (int)$rs_query->selectField('posts', 'id', array('slug'=>$this->slug));
		
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
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostTitle($echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's title from the database
        $title = $rs_query->selectField('posts', 'title', array('slug'=>$this->slug));
		
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
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostAuthor($echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's author from the database
        $author = $rs_query->selectField('posts', 'author', array('slug'=>$this->slug));
		
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
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostDate($echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's title from the database
        $date = $rs_query->selectField('posts', 'date', array('slug'=>$this->slug));
		
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
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostModDate($echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's title from the database
        $modified = $rs_query->selectField('posts', 'modified', array('slug'=>$this->slug));
		
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
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostContent($echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's content from the database
        $content = $rs_query->selectField('posts', 'content', array('slug'=>$this->slug));
		
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
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostStatus($echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's status from the database
        $status = $rs_query->selectField('posts', 'status', array('slug'=>$this->slug));
		
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
	 * @param int $id
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
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostParent($echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's parent from the database
        $parent = $rs_query->selectField('posts', 'parent', array('slug'=>$this->slug));
		
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
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostType($echo = true) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the post's type from the database
		$type = $rs_query->selectField('posts', 'type', array('slug'=>$this->slug));
		
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
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostFeatImage($echo = true) {
		// Extend the Query class
		global $rs_query;
		
		// Fetch the featured image's id from the database
		$feat_image = (int)$rs_query->selectField('postmeta', 'value', array('post'=>$this->getPostId(false), '_key'=>'feat_image'));
		
        if($echo)
            echo getMedia($feat_image, 'featured-image');
        else
            return getMedia($feat_image, 'featured-image');
    }
	
	/**
	 * Fetch a post's metadata.
	 * @since 2.2.3[a]
	 *
	 * @access public
	 * @param string $key
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostMeta($key, $echo = true) {
		// Extend the Query class
        global $rs_query;
		
		// Fetch the post's metadata from the database
        $meta = $rs_query->selectField('postmeta', 'value', array('post'=>$this->getPostId(false), '_key'=>$key));

        if($echo)
            echo $meta;
        else
            return $meta;
    }
	
	/**
	 * Fetch a post's full URL.
	 * @since 2.2.3[a]
	 *
	 * @access public
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostUrl($echo = true) {
        if($echo)
            echo getSetting('site_url', false).getPermalink($this->getPostParent(false), $this->slug);
        else
            return getSetting('site_url', false).getPermalink($this->getPostParent(false), $this->slug);
    }
	
	/**
	 * Check whether a post has a featured image.
	 * @since 2.2.4[a]
	 *
	 * @access public
	 * @return bool
	 */
	public function postHasFeatImage() {
		// Extend the Query class
		global $rs_query;
		
		// Return true if the post has a featured image
		return (int)$rs_query->selectField('postmeta', 'value', array('post'=>$this->getPostId(false), '_key'=>'feat_image')) !== 0;
	}
}