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
	 * @param string $slug (optional) -- The post's slug.
	 */
	public function __construct(string $slug = '') {
		global $rs_query, $session, $post_types, $taxonomies;
		
		if(!empty($slug)) {
			$this->slug = $slug;
			$status = $this->getPostStatus(); // This line does nothing ??
		} else {
			$raw_uri = $_SERVER['REQUEST_URI'];
			
			// Home page
			if($raw_uri === '/' || (str_starts_with($raw_uri, '/?') && !isset($_GET['preview']))) {
				$home_page = $rs_query->selectField('settings', 'value', array('name' => 'home_page'));
				$this->slug = $this->getPostSlug($home_page);
				$status = $this->getPostStatus();
				
				switch($status) {
					case 'draft': case 'trash':
						$is_published = false;
						break;
					case 'published': case 'private':
						$is_published = true;
						break;
				}
				
				if(!$is_published) {
					if($status === 'draft' && isset($session))
						redirect('/?id=' . $home_page . '&preview=true');
					else
						redirect('/404.php');
				} else {
					if($status === 'private' && !isset($session))
						redirect('/404.php');
				}
			} // All other pages
			 else {
				// Check whether the current post is a preview and the id is valid
				if(isset($_GET['preview']) && $_GET['preview'] === 'true' && isset($_GET['id']) && $_GET['id'] > 0) {
					$this->slug = $this->getPostSlug($_GET['id']);
					$status = $this->getPostStatus();
					
					switch($status) {
						case 'draft': case 'trash':
							$is_published = false;
							break;
						case 'published': case 'private':
							$is_published = true;
							break;
					}
					
					if($status !== 'draft') {
						if($is_published) {
							// Redirect to the proper URL
							redirect($this->getPostPermalink(
								$this->getPostType(),
								$this->getPostParent(),
								$this->slug
							));
						} else {
							redirect('/404.php');
						}
					}
					
					if(!isset($session)) redirect('/404.php');
				} else {
					$uri = explode('/', $raw_uri);
					
					// Filter out any empty array values
					$uri = array_filter($uri);
					
					// Check whether the last element of the array is the slug
					if(str_starts_with(end($uri), '?')) {
						// Fetch the query string at the end of the array
						$query_string = array_pop($uri);
					}
					
					$this->slug = array_pop($uri);
					$id = $this->getPostId();
					$status = $this->getPostStatus();
					
					switch($status) {
						case 'draft':
							$is_published = false;
							break;
						case 'published': case 'private':
							$is_published = true;
							break;
					}
					
					if(!$is_published) {
						if($status === 'draft' && isset($session) && !empty($id))
							redirect('/?id=' . $id . '&preview=true');
						else
							redirect('/404.php');
					} else {
						if($status === 'private' && !isset($session))
							redirect('/404.php');
						
						if(isHomePage($id)) {
							redirect('/');
						} else {
							$permalink = $this->getPostPermalink(
								$this->getPostType(),
								$this->getPostParent(),
								$this->getPostSlug($id)
							);
							
							if(isset($query_string)) $permalink .= $query_string;
							if($raw_uri !== $permalink) redirect($permalink);
						}
					}
				}
			}
		}
		
		if(array_key_exists($this->getPostType(), $post_types)) {
			$this->type_data = $post_types[$this->getPostType()];
			
			// Check whether the current post type has a taxonomy associated with it and the taxonomy is valid
			if(!empty($this->type_data['taxonomy']) && array_key_exists($this->type_data['taxonomy'], $taxonomies))
				$this->taxonomy_data = $taxonomies[$this->type_data['taxonomy']];
		} else {
			// Unrecognized post type, abort
			redirect('/404.php');
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
		global $rs_query;
		
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
		global $rs_query;
		
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
		global $rs_query;
		
		$author = $rs_query->selectField('posts', 'author', array('slug' => $this->slug));
		
		return $rs_query->selectField('usermeta', 'value', array(
			'user' => $author,
			'_key' => 'display_name'
		));
	}
	
	/**
	 * Fetch the post's publish date.
	 * @since 2.2.0[a]
	 *
	 * @access public
	 * @return string
	 */
	public function getPostDate(): string {
		global $rs_query;
		
		$date = $rs_query->selectField('posts', 'date', array('slug' => $this->slug));
		
		if(empty($date))
			$date = $rs_query->selectField('posts', 'modified', array('slug' => $this->slug));
		
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
		global $rs_query;
		
		$modified = $rs_query->selectField('posts', 'modified', array('slug' => $this->slug));
		
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
		global $rs_query;
		
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
		global $rs_query;
		
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
		global $rs_query;
		
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
		global $rs_query;
		
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
		global $rs_query;
		
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
		global $rs_query;
		
		$featured_image = (int)$rs_query->selectField('postmeta', 'value', array(
			'post' => $this->getPostId(),
			'_key' => 'feat_image'
		));
		
		return getMedia($featured_image, array('class' => 'featured-image'));
    }
	
	/**
	 * Fetch the post's metadata.
	 * @since 2.2.3[a]
	 *
	 * @access public
	 * @param string $key -- The meta database key.
	 * @return string
	 */
	public function getPostMeta(string $key): string {
		global $rs_query;
		
		$field = $rs_query->selectField('postmeta', 'value', array(
			'post' => $this->getPostId(),
			'_key' => $key
		));
		
		// Escape double quotes in meta descriptions
		if($key === 'description')
			$field = str_replace('"', '&quot;', $field);
		
		return $field;
    }
	
	/**
	 * Fetch the post's terms.
	 * @since 2.4.1[a]
	 *
	 * @access public
	 * @param bool $linked (optional) -- Whether to link the terms.
	 * @return array
	 */
	public function getPostTerms(bool $linked = true): array {
		global $rs_query;
		
		$terms = array();
		$relationships = $rs_query->select('term_relationships', 'term', array(
			'post' => $this->getPostId()
		));
		
		foreach($relationships as $relationship) {
			$slug = $rs_query->selectField('terms', 'slug', array(
				'id' => $relationship['term'],
				'taxonomy' => getTaxonomyId($this->type_data['taxonomy'])
			));
			
			$rs_term = getTerm($slug);
			
			$terms[] = $linked ? domTag('a', array(
					'href' => $rs_term->getTermUrl(),
					'content' => $rs_term->getTermName()
				)) : $rs_term->getTermName();
		}
		
		return $terms;
	}
	
	/**
	 * Fetch the post's comments.
	 * @since 1.1.0[b]{ss-03}
	 *
	 * @access public
	 * @param bool $feed_only (optional) -- Whether to only display the comment feed.
	 */
	public function getPostComments(bool $feed_only = false): void {
		$rs_comment = new Comment($this->getPostId());
		
		if(!$feed_only) $rs_comment->getCommentReplyBox();
		
		$rs_comment->getCommentFeed();
	}
	
	/**
	 * Fetch the post's permalink.
	 * @since 2.2.5[a]
	 *
	 * @access public
	 * @param string $type -- The post's type.
	 * @param int $parent -- The post's parent.
	 * @param string $slug (optional) -- The post's slug.
	 * @return string
	 */
	public function getPostPermalink(string $type, int $parent, string $slug = ''): string {
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
		if(isHomePage($this->getPostId())) {
			return trailingSlash(getSetting('site_url'));
		} else {
			return getSetting('site_url') . $this->getPostPermalink(
				$this->getPostType(),
				$this->getPostParent(),
				$this->slug
			);
		}
    }
	
	/**
	 * Check whether a post has a featured image.
	 * @since 2.2.4[a]
	 *
	 * @access public
	 * @return bool
	 */
	public function postHasFeaturedImage(): bool {
		global $rs_query;
		
		return (int)$rs_query->selectField('postmeta', 'value', array(
			'post' => $this->getPostId(),
			'_key' => 'feat_image'
		)) !== 0;
	}
}