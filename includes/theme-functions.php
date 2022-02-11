<?php
/**
 * Theme-specific functions.
 * @since 1.2.8[b]
 */

/*------------------------------------*\
    POSTS
\*------------------------------------*/

/**
 * Check whether the currently viewed page is a post.
 * @since 1.2.8[b]
 *
 * @return bool
 */
function isPost(): bool {
	// Extend the Post object
	global $rs_post;
	
	return isset($rs_post);
}

/**
 * Alias for the Post class' getPostId function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostId()
 * @return int
 */
function getPostId(): int {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostId();
}

/**
 * Display the post's id.
 * @since 1.2.8[b]
 */
function putPostId(): void { echo getPostId(); }

/**
 * Alias for the Post class' getPostTitle function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostTitle()
 * @return string
 */
function getPostTitle(): string {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostTitle();
}

/**
 * Display the post's title.
 * @since 1.2.8[b]
 */
function putPostTitle(): void { echo getPostTitle(); }

/**
 * Alias for the Post class' getPostAuthor function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostAuthor()
 * @return string
 */
function getPostAuthor(): string {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostAuthor();
}

/**
 * Display the post's author.
 * @since 1.2.8[b]
 */
function putPostAuthor(): void { echo getPostAuthor(); }

/**
 * Alias for the Post class' getPostDate function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostDate()
 * @return string
 */
function getPostDate(): string {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostDate();
}

/**
 * Display the post's publish date.
 * @since 1.2.8[b]
 */
function putPostDate(): void { echo getPostDate(); }

/**
 * Alias for the Post class' getPostModDate function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostModDate()
 * @return string
 */
function getPostModDate(): string {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostModDate();
}

/**
 * Display the post's modified date.
 * @since 1.2.8[b]
 */
function putPostModDate(): void { echo getPostModDate(); }

/**
 * Alias for the Post class' getPostContent function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostContent()
 * @return string
 */
function getPostContent(): string {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostContent();
}

/**
 * Display the post's content.
 * @since 1.2.8[b]
 */
function putPostContent(): void { echo getPostContent(); }

/**
 * Alias for the Post class' getPostStatus function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostStatus()
 * @return string
 */
function getPostStatus(): string {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostStatus();
}

/**
 * Display the post's status.
 * @since 1.2.8[b]
 */
function putPostStatus(): void { echo getPostStatus(); }

/**
 * Alias for the Post class' getPostSlug function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostSlug()
 * @param int $id
 * @return string
 */
function getPostSlug($id): string {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostSlug($id);
}

/**
 * Display the post's slug.
 * @since 1.2.8[b]
 *
 * @param int $id
 */
function putPostSlug($id): void { echo getPostSlug($id); }

/**
 * Alias for the Post class' getPostParent function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostParent()
 * @return int
 */
function getPostParent(): int {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostParent();
}

/**
 * Display the post's parent.
 * @since 1.2.8[b]
 */
function putPostParent(): void { echo getPostParent(); }

/**
 * Alias for the Post class' getPostType function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostType()
 * @return string
 */
function getPostType(): string {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostType();
}

/**
 * Display the post's type.
 * @since 1.2.8[b]
 */
function putPostType(): void { echo getPostType(); }

/**
 * Alias for the Post class' getPostFeaturedImage function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostFeaturedImage()
 * @return string
 */
function getPostFeaturedImage(): string {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostFeaturedImage();
}

/**
 * Display the post's featured image.
 * @since 1.2.8[b]
 */
function putPostFeaturedImage(): void { echo getPostFeaturedImage(); }

/**
 * Alias for the Post class' getPostMeta function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostMeta()
 * @param string $key
 * @return string
 */
function getPostMeta($key): string {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostMeta($key);
}

/**
 * Display the post's metadata.
 * @since 1.2.8[b]
 *
 * @param string $key
 */
function putPostMeta($key): void { echo getPostMeta($key); }

/**
 * Alias for the Post class' getPostTerms function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostTerms()
 * @param bool $linked (optional; default: true)
 * @return array
 */
function getPostTerms($linked = true): array {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostTerms($linked);
}

/**
 * Display the post's terms.
 * @since 1.2.8[b]
 *
 * @param bool $linked (optional; default: true)
 */
function putPostTerms($linked = true): void {
	echo empty(getPostTerms()) ? 'None' : implode(', ', getPostTerms($linked));
}

/**
 * Alias for the Post class' getPostComments function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostComments()
 * @param bool $feed_only (optional; default: false)
 */
function getPostComments($feed_only = false): void {
	// Extend the Post object
	global $rs_post;
	
	$rs_post->getPostComments($feed_only);
}

/**
 * Alias for the Post class' getPostUrl function.
 * @since 1.2.8[b]
 *
 * @see Post::getPostUrl()
 * @return string
 */
function getPostUrl(): string {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->getPostUrl();
}

/**
 * Display the post's full URL.
 * @since 1.2.8[b]
 */
function putPostUrl(): void { echo getPostUrl(); }

/**
 * Alias for the Post class' postHasFeaturedImage function.
 * @since 1.2.8[b]
 *
 * @see Post::postHasFeaturedImage()
 * @return bool
 */
function postHasFeaturedImage(): bool {
	// Extend the Post object
	global $rs_post;
	
	return $rs_post->postHasFeaturedImage();
}

/**
 * Construct the post's excerpt text.
 * @since 1.2.9[b]
 *
 * @param int $num_words (optional; default: 25)
 * @return string
 */
function getPostExcerpt($num_words = 25): string {
	return trimWords(str_replace(array("\n", "\r", "  "), ' ', strip_tags(getPostContent())), $num_words, '...');
}

/**
 * Display the post's excerpt text.
 * @since 1.2.9[b]
 *
 * @param int $num_words (optional; default: 25)
 */
function putPostExcerpt($num_words = 25): void { echo getPostExcerpt($num_words); }

/*------------------------------------*\
    TERMS
\*------------------------------------*/

/**
 * Check whether the currently viewed page is a term.
 * @since 1.2.8[b]
 *
 * @return bool
 */
function isTerm(): bool {
	// Extend the Term object
	global $rs_term;
	
	return isset($rs_term);
}

/**
 * Alias for the Term class' getTermId function.
 * @since 1.2.8[b]
 *
 * @see Term::getTermId()
 * @return int
 */
function getTermId(): int {
	// Extend the Term object
	global $rs_term;
	
	return $rs_term->getTermId();
}

/**
 * Display the term's id.
 * @since 1.2.8[b]
 */
function putTermId(): void { echo getTermId(); }

/**
 * Alias for the Term class' getTermName function.
 * @since 1.2.8[b]
 *
 * @see Term::getTermName()
 * @return string
 */
function getTermName(): string {
	// Extend the Term object
	global $rs_term;
	
	return $rs_term->getTermName();
}

/**
 * Display the term's name.
 * @since 1.2.8[b]
 */
function putTermName(): void { echo getTermName(); }

/**
 * Alias for the Term class' getTermSlug function.
 * @since 1.2.8[b]
 *
 * @see Term::getTermSlug()
 * @param int $id
 * @return string
 */
function getTermSlug($id): string {
	// Extend the Term object
	global $rs_term;
	
	return $rs_term->getTermSlug($id);
}

/**
 * Display the term's slug.
 * @since 1.2.8[b]
 *
 * @param int $id
 */
function putTermSlug($id): void { echo getTermSlug($id); }

/**
 * Alias for the Term class' getTermTaxonomy function.
 * @since 1.2.8[b]
 *
 * @see Term::getTermTaxonomy()
 * @return string
 */
function getTermTaxonomy(): string {
	// Extend the Term object
	global $rs_term;
	
	return $rs_term->getTermTaxonomy();
}

/**
 * Display the term's taxonomy.
 * @since 1.2.8[b]
 */
function putTermTaxonomy(): void { echo getTermTaxonomy(); }

/**
 * Alias for the Term class' getTermParent function.
 * @since 1.2.8[b]
 *
 * @see Term::getTermParent()
 * @return int
 */
function getTermParent(): int {
	// Extend the Term object
	global $rs_term;
	
	return $rs_term->getTermParent();
}

/**
 * Display the term's parent.
 * @since 1.2.8[b]
 */
function putTermParent(): void { echo getTermParent(); }

/**
 * Alias for the Term class' getTermUrl function.
 * @since 1.2.8[b]
 *
 * @see Term::getTermUrl()
 * @return string
 */
function getTermUrl(): string {
	// Extend the Term object
	global $rs_term;
	
	return $rs_term->getTermUrl();
}

/**
 * Display the term's full URL.
 * @since 1.2.8[b]
 */
function putTermUrl(): void { echo getTermUrl(); }

/**
 * Alias for the getTermId function.
 * @since 1.2.8[b]
 *
 * @see getTermId()
 * @return int
 */
function getCategoryId(): int { return getTermId(); }

/**
 * Alias for the putTermId function.
 * @since 1.2.8[b]
 *
 * @see putTermId()
 */
function putCategoryId(): void { putTermId(); }

/**
 * Alias for the getTermName function.
 * @since 1.2.8[b]
 *
 * @see getTermName()
 * @return string
 */
function getCategoryName(): string { return getTermName(); }

/**
 * Alias for the putTermName function.
 * @since 1.2.8[b]
 *
 * @see putTermName()
 */
function putCategoryName(): void { putTermName(); }

/**
 * Alias for the getTermSlug function.
 * @since 1.2.8[b]
 *
 * @see getTermSlug()
 * @param int $id
 * @return string
 */
function getCategorySlug($id): string { return getTermSlug($id); }

/**
 * Alias for the putTermSlug function.
 * @since 1.2.8[b]
 *
 * @see putTermSlug()
 * @param int $id
 */
function putCategorySlug($id): void { putTermSlug($id); }

/**
 * Alias for the getTermParent function.
 * @since 1.2.8[b]
 *
 * @see getTermParent()
 * @return int
 */
function getCategoryParent(): int { return getTermParent(); }

/**
 * Alias for the putTermParent function.
 * @since 1.2.8[b]
 *
 * @see putTermParent()
 */
function putCategoryParent(): void { putTermParent(); }

/**
 * Alias for the getTermUrl function.
 * @since 1.2.8[b]
 *
 * @see getTermUrl()
 * @return string
 */
function getCategoryUrl(): string { return getTermUrl(); }

/**
 * Alias for the putTermUrl function.
 * @since 1.2.8[b]
 *
 * @see putTermUrl()
 */
function putCategoryUrl(): void { putTermUrl(); }

/**
 * Fetch a user-friendly version of the term's taxonomy name.
 * @since 1.3.0[b]
 *
 * @return string
 */
function getTermTaxName(): string {
	// Extend the taxonomies array
	global $taxonomies;
	
	return $taxonomies[getTermTaxonomy()]['labels']['name_singular'];
}

/**
 * Display a user-friendly version of the term's taxonomy name.
 * @since 1.3.0[b]
 */
function putTermTaxName(): void { echo getTermTaxName(); }

/**
 * Fetch all posts associated with the current term.
 * @since 2.4.1[a]
 *
 * @param null|int|string $_term (optional; default: null)
 * @param string $order_by (optional; default: 'date')
 * @param string $order (optional; default: 'DESC')
 * @param int $limit (optional; default: 0)
 * @return array
 */
function getTermPosts($_term = null, $order_by = 'date', $order = 'DESC', $limit = 0): array {
	// Extend the Query object
	global $rs_query;
	
	// Create an empty array to hold the posts
	$posts = array();
	
	// Check whether the term value is null
	if(!is_null($_term)) {
		// Check whether the term value is an integer
		if(is_int($_term)) {
			// Fetch the term
			$term = $_term;
		} else {
			// Fetch the term's id
			$term = getTerm($_term)->getTermId();
		}
	} else {
		// Fetch the term's id
		$term = getTermId();
	}
	
	// Fetch the term relationships from the database
	$relationships = $rs_query->select('term_relationships', 'post', array('term' => $term));
	
	// Loop through the term relationships
	foreach($relationships as $relationship) {
		// Skip the post if it isn't published
		if(!$rs_query->selectRow('posts', 'id', array('id' => $relationship['post'], 'status' => 'published')))
			continue;
		
		// Fetch each post from the database and assign them to the posts array
		$posts[] = $rs_query->selectRow('posts', '*', array('id' => $relationship['post']), $order_by, $order, $limit);
	}
	
	// Return the posts
	return $posts;
}

/**
 * Display all posts associated with the current term.
 * @since 1.3.0[b]
 *
 * @param null|int|string $_term (optional; default: null)
 * @param string $order_by (optional; default: 'date')
 * @param string $order (optional; default: 'DESC')
 * @param int $limit (optional; default: 0)
 */
function putTermPosts($_term = null, $order_by = 'date', $order = 'DESC', $limit = 0): void {
	// Fetch the posts
	$posts = getTermPosts($_term, $order_by, $order, $limit);
	
	// Check whether there are any posts
	if(empty($posts)) {
		echo '<p>There are no posts to display!</p>';
	} else {
		$content = '<ul>';
		
		// Loop through the posts
		foreach($posts as $post) {
			// Fetch the post's permalink
			$permalink = getPost($post['slug'])->getPostPermalink($post['type'], $post['parent'], $post['slug']);
			
			// Add the post to the list
			$content .= '<li><a href="'.$permalink.'">'.$post['title'].'</a></li>';
		}
		
		echo $content.'</ul>';
	}
}

/*------------------------------------*\
    MISCELLANEOUS
\*------------------------------------*/

/**
 * Check whether a page template exists.
 * @since 2.3.3[a]
 *
 * @param string $template
 * @param string $dir
 * @return bool
 */
function templateExists($template, $dir): bool {
    return file_exists(trailingSlash($dir).$template);
}

/**
 * Fetch the theme's header template.
 * @since 1.5.5[a]
 *
 * @param string $template (optional; default: '')
 * @return null (when no template exists)
 */
function getHeader($template = '') {
	// Extend the user's session data
	global $session;
	
	// Construct the file path for the current theme
	$theme_path = trailingSlash(PATH.THEMES).getSetting('theme');
	
	// Check whether the template file exists
	if(!file_exists($theme_path.'/header.php') && !file_exists(trailingSlash($theme_path).$template.'.php')) {
		// Don't load anything
		return null;
	} else {
		// Include the header template
		require_once trailingSlash($theme_path).(!empty($template) ? $template : 'header').'.php';
	}
}

/**
 * Fetch the theme's footer template.
 * @since 1.5.5[a]
 *
 * @param string $template (optional; default: '')
 * @return null (when no template exists)
 */
function getFooter($template = '') {
	// Extend the user's session data
	global $session;
	
	// Construct the file path for the current theme
	$theme_path = trailingSlash(PATH.THEMES).getSetting('theme');
	
	// Check whether the template file exists
	if(!file_exists($theme_path.'/footer.php') && !file_exists(trailingSlash($theme_path).$template.'.php')) {
		// Don't load anything
		return null;
	} else {
		// Include the footer template
		require_once trailingSlash($theme_path).(!empty($template) ? $template : 'footer').'.php';
	}
}

/**
 * Construct and display the page title.
 * @since 1.1.3[b]
 */
function pageTitle(): void {
	if(isPost())
		!empty(getPostMeta('title')) ? putPostMeta('title') : putPostTitle();
	else
		putTermName();
	?> &rtrif; <?php putSetting('site_title');
}

/**
 * Set up all of the meta tags for the <head> section.
 * @since 1.1.3[b]
 */
function metaTags(): void {
	?>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="<?php putSetting('theme_color'); ?>">
	<meta name="description" content="<?php
		if(isPost())
			!empty(getPostMeta('description')) ? putPostMeta('description') : putPostExcerpt();
		?>">
	<meta property="og:title" content="<?php
		if(isPost())
			!empty(getPostMeta('title')) ? putPostMeta('title') : putPostTitle();
		else
			putTermName();
		?>">
	<meta property="og:type" content="website">
	<meta property="og:url" content="<?php isPost() ? putPostUrl() : putTermUrl(); ?>">
	<meta property="og:image" content="<?php echo getMediaSrc(getSetting('site_logo')); ?>">
	<meta property="og:description" content="<?php
		if(isPost())
			!empty(getPostMeta('description')) ? putPostMeta('description') : putPostExcerpt();
		?>">
	<link href="<?php isPost() ? putPostUrl() : putTermUrl(); ?>" rel="canonical">
	<link type="image/x-icon" href="<?php echo getMediaSrc(getSetting('site_icon')); ?>" rel="icon">
	<?php
}