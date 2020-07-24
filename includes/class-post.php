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
		// Extend the Query object
		global $rs_query;
		
		// Check whether a slug has been provided
		if(!empty($slug)) {
			// Fetch the slug value
			$this->slug = $slug;
			
			// Fetch the post's status from the database
			$status = $this->getPostStatus(false);
			
			// Check whether the post is published and redirect to the 404 (Not Found) page if not
			if($status !== 'published') redirect('/404.php');
		} else {
			// Fetch the post's URI
			$raw_uri = $_SERVER['REQUEST_URI'];
			
			// Check whether the current page is the home page
			if($raw_uri === '/' || (strpos($raw_uri, '/?') === 0 && !isset($_GET['preview']))) {
				// Fetch the home page's id from the database
				$home_page = $rs_query->selectField('settings', 'value', array('name'=>'home_page'));
				
				// Fetch the slug from the database
				$this->slug = $this->getPostSlug($home_page, false);
				
				// Fetch the post's status from the database
				$status = $this->getPostStatus(false);
				
				// Check whether the post is published
				if($status !== 'published') {
					// Check whether the post is a draft
					if($status === 'draft') {
						// Redirect to the post preview
						redirect('/?id='.$home_page.'&preview=true');
					} else {
						// Redirect to the 404 (Not Found) page
						redirect('/404.php');
					}
				}
			} else {
				// Check whether the current post is a preview and the id is valid
				if(isset($_GET['preview']) && $_GET['preview'] === 'true' && isset($_GET['id']) && $_GET['id'] > 0) {
					// Fetch the slug from the database
					$this->slug = $this->getPostSlug($_GET['id'], false);
					
					// Fetch the post's status from the database
					$status = $this->getPostStatus(false);
					
					// Check whether the post is a draft
					if($status !== 'draft') {
						// Check whether the post is published
						if($status === 'published') {
							// Redirect to the proper URL
							redirect($this->getPostPermalink($this->getPostType(false), $this->getPostParent(false), $this->slug));
						} else {
							// Redirect to the 404 (Not Found) page
							redirect('/404.php');
						}
					}
					
					// Check whether the user is logged in and redirect to the 404 (Not Found) page if not
					if(!isset($_COOKIE['session'])) redirect('/404.php');
				} else {
					// Create an array from the post's URI
					$uri = explode('/', $raw_uri);
					
					// Filter out any empty array values
					$uri = array_filter($uri);
					
					// Check whether the last element of the array is the slug
					if(strpos(end($uri), '?') !== false) {
						// Fetch the query string at the end of the array
						$query_string = array_pop($uri);
					}
					
					// Fetch the slug from the URI array
					$this->slug = array_pop($uri);
					
					// Fetch the post's id from the database
					$id = $this->getPostId(false);
					
					// Fetch the post's status from the database
					$status = $this->getPostStatus(false);
					
					// Check whether the post is published
					if($status !== 'published') {
						// Check whether the id is valid and whether the post is a draft
						if(!empty($id) && $status === 'draft') {
							// Redirect to the post preview
							redirect('/?id='.$id.'&preview=true');
						} else {
							// Redirect to the 404 (Not Found) page
							redirect('/404.php');
						}
					} else {
						// Check whether the post is actually the home page
						if(isHomePage($id)) {
							// Redirect to the home URL
							redirect('/');
						} else {
							// Construct the post's permalink
							$permalink = $this->getPostPermalink($this->getPostType(false), $this->getPostParent(false), $this->getPostSlug($id, false));
							
							// Check whether the query string is set and concatenate it to the permalink if so
							if(isset($query_string)) $permalink .= $query_string;
							
							// Check whether the permalink is valid and redirect to the proper one if not
							if($raw_uri !== $permalink) redirect($permalink);
						}
					}
				}
			}
		}
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
		// Extend the Query object
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
		// Extend the Query object
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
		// Extend the Query object
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
		// Extend the Query object
        global $rs_query;
		
		// Fetch the post's title from the database
        $date = $rs_query->selectField('posts', 'date', array('slug'=>$this->slug));
		
        if($echo)
            echo formatDate($date, 'j M Y @ g:i A');
        else
            return formatDate($date, 'j M Y @ g:i A');
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
		// Extend the Query object
        global $rs_query;
		
		// Fetch the post's title from the database
        $modified = $rs_query->selectField('posts', 'modified', array('slug'=>$this->slug));
		
        if($echo)
            echo formatDate($modified, 'j M Y @ g:i A');
        else
            return formatDate($modified, 'j M Y @ g:i A');
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
		// Extend the Query object
        global $rs_query;
		
		// Fetch the post's content from the database
        $content = $rs_query->selectField('posts', 'content', array('slug'=>$this->slug));
		
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
		// Extend the Query object
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
		// Extend the Query object
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
	 * @return null|int (null on $echo == true; int on $echo == false)
	 */
	public function getPostParent($echo = true) {
		// Extend the Query object
        global $rs_query;
		
		// Fetch the post's parent from the database
        $parent = (int)$rs_query->selectField('posts', 'parent', array('slug'=>$this->slug));
		
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
		// Extend the Query object
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
		// Extend the Query object
		global $rs_query;
		
		// Fetch the featured image's id from the database
		$feat_image = (int)$rs_query->selectField('postmeta', 'value', array('post'=>$this->getPostId(false), '_key'=>'feat_image'));
		
        if($echo)
            echo getMedia($feat_image, array('class'=>'featured-image'));
        else
            return getMedia($feat_image, array('class'=>'featured-image'));
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
		// Extend the Query object
        global $rs_query;
		
		// Fetch the post's metadata from the database
        $meta = $rs_query->selectField('postmeta', 'value', array('post'=>$this->getPostId(false), '_key'=>$key));

        if($echo)
            echo $meta;
        else
            return $meta;
    }
	
	/**
	 * Fetch a post's categories.
	 * @since 2.4.1[a]
	 *
	 * @param bool $echo (optional; default: true)
	 * @return null|string (null on $echo == true; string on $echo == false)
	 */
	public function getPostCategories($echo = true) {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty array to hold the categories
		$categories = array();
		
		// Fetch the term relationships from the database
		$relationships = $rs_query->select('term_relationships', 'term', array('post'=>$this->getPostId(false)));
		
		// Loop through the term relationships
		foreach($relationships as $relationship) {
			// Fetch the category's slug from the database
			$slug = $rs_query->selectField('terms', 'slug', array('id'=>$relationship['term'], 'taxonomy'=>getTaxonomyId('category')));
			
			// Create a Category object
			$rs_category = getCategory($slug);
			
			// Fetch each term from the database and assign them to the categories array
			$categories[] = $echo ? '<a href="'.$rs_category->getCategoryUrl(false).'">'.$rs_category->getCategoryName(false).'</a>' : $rs_category->getCategoryName(false);
		}
		
		if($echo)
			echo empty($categories) ? 'None' : implode(', ', $categories);
		else
			return $categories;
	}
	
	/**
	 * Fetch a post's permalink.
	 * @since 2.2.5[a]
	 *
	 * @access public
	 * @param string $type
	 * @param int $parent
	 * @param string $slug (optional; default: '')
	 * @return string
	 */
	public function getPostPermalink($type, $parent, $slug = '') {
		return getPermalink($type, $parent, $slug);
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
            echo getSetting('site_url', false).$this->getPostPermalink($this->getPostType(false), $this->getPostParent(false), $this->slug);
        else
            return getSetting('site_url', false).$this->getPostPermalink($this->getPostType(false), $this->getPostParent(false), $this->slug);
    }
	
	/**
	 * Check whether a post has a featured image.
	 * @since 2.2.4[a]
	 *
	 * @access public
	 * @return bool
	 */
	public function postHasFeatImage() {
		// Extend the Query object
		global $rs_query;
		
		// Return true if the post has a featured image
		return (int)$rs_query->selectField('postmeta', 'value', array('post'=>$this->getPostId(false), '_key'=>'feat_image')) !== 0;
	}
}