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
	 * The currently queried post's type data.
	 * @since 1.0.6[b]
	 *
	 * @access private
	 * @var array
	 */
	private $type_data = array();
	
	/**
	 * The currently queried post's taxonomy data.
	 * @since 1.0.6[b]
	 *
	 * @access private
	 * @var array
	 */
	private $taxonomy_data = array();
	
	/**
	 * Class constructor. Sets the default queried post slug.
	 * @since 2.2.3[a]
	 *
	 * @access public
	 * @param string $slug (optional; default: '')
	 */
	public function __construct($slug = '') {
		// Extend the Query object and the post types and taxonomies arrays
		global $rs_query, $post_types, $taxonomies;
		
		// Check whether a slug has been provided
		if(!empty($slug)) {
			// Fetch the slug value
			$this->slug = $slug;
			
			// Fetch the post's status from the database
			$status = $this->getPostStatus();
		} else {
			// Fetch the post's URI
			$raw_uri = $_SERVER['REQUEST_URI'];
			
			// Check whether the current page is the home page
			if($raw_uri === '/' || (strpos($raw_uri, '/?') === 0 && !isset($_GET['preview']))) {
				// Fetch the home page's id from the database
				$home_page = $rs_query->selectField('settings', 'value', array('name' => 'home_page'));
				
				// Fetch the slug from the database
				$this->slug = $this->getPostSlug($home_page);
				
				// Fetch the post's status from the database
				$status = $this->getPostStatus();
				
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
					$this->slug = $this->getPostSlug($_GET['id']);
					
					// Fetch the post's status from the database
					$status = $this->getPostStatus();
					
					// Check whether the post is a draft
					if($status !== 'draft') {
						// Check whether the post is published
						if($status === 'published') {
							// Redirect to the proper URL
							redirect($this->getPostPermalink($this->getPostType(), $this->getPostParent(), $this->slug));
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
					$id = $this->getPostId();
					
					// Fetch the post's status from the database
					$status = $this->getPostStatus();
					
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
							$permalink = $this->getPostPermalink($this->getPostType(), $this->getPostParent(), $this->getPostSlug($id));
							
							// Check whether the query string is set and concatenate it to the permalink if so
							if(isset($query_string)) $permalink .= $query_string;
							
							// Check whether the permalink is valid and redirect to the proper one if not
							if($raw_uri !== $permalink) redirect($permalink);
						}
					}
				}
			}
		}
		
		// Fetch the type data
		$this->type_data = $post_types[$this->getPostType()];
		
		// Check whether the current post type has a taxonomy associated with it and the taxonomy is valid
		if(!empty($this->type_data['taxonomy']) && array_key_exists($this->type_data['taxonomy'], $taxonomies)) {
			// Fetch the taxonomy data
			$this->taxonomy_data = $taxonomies[$this->type_data['taxonomy']];
		}
	}
	
	/**
	 * Fetch the post's id.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @return int
	 */
	public function getPostId(): int {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's id from the database and return it
		return (int)$rs_query->selectField('posts', 'id', array('slug' => $this->slug));
	}
	
	/**
	 * Fetch the post's title.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getPostTitle(): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's title from the database and return it
		return $rs_query->selectField('posts', 'title', array('slug' => $this->slug));
    }
	
	/**
	 * Fetch the post's author.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getPostAuthor(): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's author from the database
		$author = $rs_query->selectField('posts', 'author', array('slug' => $this->slug));
		
		// Fetch the author's username from the database and return it
		return $rs_query->selectField('users', 'username', array('id' => $author));
	}
	
	/**
	 * Fetch the post's publish date.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getPostDate(): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's date from the database
		$date = $rs_query->selectField('posts', 'date', array('slug' => $this->slug));
		
		// Check whether the post has been published and fetch the modified date if not
		if(empty($date))
			$date = $rs_query->selectField('posts', 'modified', array('slug' => $this->slug));
		
		// Return a formatted date string
		return formatDate($date, 'j M Y @ g:i A');
    }
	
	/**
	 * Fetch the post's modified date.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getPostModDate(): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's modified date from the database
		$modified = $rs_query->selectField('posts', 'modified', array('slug' => $this->slug));
		
		// Return a formatted date string
		return formatDate($modified, 'j M Y @ g:i A');
    }
	
	/**
	 * Fetch the post's content.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getPostContent(): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's content from the database and return it
		return $rs_query->selectField('posts', 'content', array('slug' => $this->slug));
    }
	
	/**
	 * Fetch the post's status.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getPostStatus(): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's status from the database and return it
		return $rs_query->selectField('posts', 'status', array('slug' => $this->slug));
    }
	
	/**
	 * Fetch the post's slug.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @param int $id
	 * @return string
	 */
    public function getPostSlug($id): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's slug from the database and return it
		return $rs_query->selectField('posts', 'slug', array('id' => $id));
    }
	
	/**
	 * Fetch the post's parent.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @return int
	 */
	public function getPostParent(): int {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's parent from the database and return it
		return (int)$rs_query->selectField('posts', 'parent', array('slug' => $this->slug));
    }
	
	/**
	 * Fetch the post's type.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getPostType(): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's type from the database and return it
		return $rs_query->selectField('posts', 'type', array('slug' => $this->slug));
	}
	
	/**
	 * Fetch the post's featured image.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getPostFeaturedImage(): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the featured image's id from the database
		$featured_image = (int)$rs_query->selectField('postmeta', 'value', array(
			'post' => $this->getPostId(),
			'_key' => 'feat_image'
		));
		
		// Return the featured image
		return getMedia($featured_image, array('class' => 'featured-image'));
    }
	
	/**
	 * Fetch the post's metadata.
	 * @since 2.2.3[a]
	 *
	 * @access public
	 * @param string $key
	 * @return string
	 */
	public function getPostMeta($key): string {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the post's metadata from the database
		$meta = $rs_query->selectField('postmeta', 'value', array(
			'post' => $this->getPostId(),
			'_key' => $key
		));
		
		// Return the metadata
		return $meta;
    }
	
	/**
	 * Fetch the post's terms.
	 * @since 2.4.1[a]
	 *
	 * @access public
	 * @param bool $linked (optional; default: true)
	 * @return array
	 */
	public function getPostTerms($linked = true): array {
		// Extend the Query object
		global $rs_query;
		
		// Create an empty array to hold the terms
		$terms = array();
		
		// Fetch the term relationships from the database
		$relationships = $rs_query->select('term_relationships', 'term', array('post' => $this->getPostId()));
		
		// Loop through the term relationships
		foreach($relationships as $relationship) {
			// Fetch the term's slug from the database
			$slug = $rs_query->selectField('terms', 'slug', array(
				'id' => $relationship['term'],
				'taxonomy' => getTaxonomyId($this->type_data['taxonomy'])
			));
			
			// Create a Term object
			$rs_term = getTerm($slug);
			
			// Fetch each term from the database and assign them to the terms array
			$terms[] = $linked ? '<a href="'.$rs_term->getTermUrl().'">'.$rs_term->getTermName().'</a>' : $rs_term->getTermName();
		}
		
		// Return the terms
		return $terms;
	}
	
	/**
	 * Fetch the post's comments.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param bool $feed_only (optional; default: false)
	 */
	public function getPostComments($feed_only = false): void {
		// Create a Comment object
		$rs_comment = new Comment($this->getPostId());
		
		// Check whether only the feed should be displayed
		if(!$feed_only) {
			// Display the comment reply box
			$rs_comment->getCommentReplyBox();
		}
		
		// Display the comment feed
		$rs_comment->getCommentFeed();
	}
	
	/**
	 * Fetch the post's permalink.
	 * @since 2.2.5[a]
	 *
	 * @access public
	 * @param string $type
	 * @param int $parent
	 * @param string $slug (optional; default: '')
	 * @return string
	 */
	public function getPostPermalink($type, $parent, $slug = ''): string {
		return getPermalink($type, $parent, $slug);
	}
	
	/**
	 * Fetch the post's full URL.
	 * @since 2.2.3[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getPostUrl(): string {
		// Check whether the current page is the home page
		if(isHomePage($this->getPostId()))
			return trailingSlash(getSetting('site_url'));
		else
			return getSetting('site_url').$this->getPostPermalink($this->getPostType(), $this->getPostParent(), $this->slug);
    }
	
	/**
	 * Check whether a post has a featured image.
	 * @since 2.2.4[a]
	 *
	 * @access public
	 * @return bool
	 */
	public function postHasFeaturedImage(): bool {
		// Extend the Query object
		global $rs_query;
		
		// Return true if the post has a featured image
		return (int)$rs_query->selectField('postmeta', 'value', array(
			'post' => $this->getPostId(),
			'_key' => 'feat_image'
		)) !== 0;
	}
}