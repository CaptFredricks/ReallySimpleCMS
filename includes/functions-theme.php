<?php
/**
 * Theme-specific functions. Most functions in here are aliases for internal class methods.
 * @since 1.2.8-beta
 *
 * @package ReallySimpleCMS
 *
 * ## FUNCTIONS [71] ##
 * { POSTS [33] }
 * - isPost(): bool
 * - getPostId(): int
 * - putPostId(): void
 * - getPostTitle(): string
 * - putPostTitle(): void
 * - getPostAuthor(): string
 * - putPostAuthor(): void
 * - getPostDate(): string
 * - putPostDate(): void
 * - getPostModDate(): string
 * - putPostModDate(): void
 * - getPostContent(): string
 * - putPostContent(): void
 * - getPostStatus(): string
 * - putPostStatus(): void
 * - getPostSlug(int $id): string
 * - putPostSlug(int $id): void
 * - getPostParent(): int
 * - putPostParent(): void
 * - getPostType(): string
 * - putPostType(): void
 * - getPostFeaturedImage(): string
 * - putPostFeaturedImage(): void
 * - getPostMeta(string $key): string
 * - putPostMeta(string $key): void
 * - getPostTerms(string $taxonomy, bool $linked): array
 * - putPostTerms(string $taxonomy, bool $linked): void
 * - getPostComments(bool $feed_only): void
 * - getPostUrl(): string
 * - putPostUrl(): void
 * - postHasFeaturedImage(): bool
 * - getPostExcerpt(int $num_words): string
 * - putPostExcerpt(int $num_words): void
 * { TERMS [27] }
 * - isTerm(): bool
 * - getTermId(): int
 * - putTermId(): void
 * - getTermName(): string
 * - putTermName(): void
 * - getTermSlug(int $id): string
 * - putTermSlug(int $id): void
 * - getTermTaxonomy(): string
 * - putTermTaxonomy(): void
 * - getTermParent(): int
 * - putTermParent(): void
 * - getTermUrl(): string
 * - putTermUrl(): void
 * - getCategoryId(): int
 * - putCategoryId(): void
 * - getCategoryName(): string
 * - putCategoryName(): void
 * - getCategorySlug(int $id): string
 * - putCategorySlug(int $id): void
 * - getCategoryParent(): int
 * - putCategoryParent(): void
 * - getCategoryUrl(): string
 * - putCategoryUrl(): void
 * - getTermTaxName(): string
 * - putTermTaxName(): void
 * - getTermPosts(mixed $_term, string $order_by, string $order, int $limit): array
 * - putTermPosts(mixed $_term, string $order_by, string $order, int $limit): void
 * { QUERIES [6] }
 * - querySelect(string $table, string|array $cols, array $where, array $args): int|array
 * - querySelectRow(string $table, string|array $cols, array $where, array $args): int|array
 * - querySelectField(string $table, string $col, array $where, array $args): string
 * - queryInsert(string $table, array $data, array $args): int
 * - queryUpdate(string $table, array $data, array $where, array $args): void
 * - queryDelete(string $table, array $where, array $args): void
 * { MISCELLANEOUS [5] }
 * - templateExists(string $template, string $dir): bool
 * - getHeader(string $template): bool
 * - getFooter(string $template): bool
 * - pageTitle(): void
 * - metaTags(): void
 */

/*------------------------------------*\
    POSTS
\*------------------------------------*/

/**
 * Check whether the currently viewed page is a post.
 * @since 1.2.8-beta
 *
 * @return bool
 */
function isPost(): bool {
	global $rs_post;
	
	return isset($rs_post);
}

/**
 * Alias for the Post class' getPostId function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostId()
 * @return int
 */
function getPostId(): int {
	global $rs_post;
	
	return $rs_post->getPostId();
}

/**
 * Display the post's id.
 * @since 1.2.8-beta
 */
function putPostId(): void { echo getPostId(); }

/**
 * Alias for the Post class' getPostTitle function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostTitle()
 * @return string
 */
function getPostTitle(): string {
	global $rs_post;
	
	return $rs_post->getPostTitle();
}

/**
 * Display the post's title.
 * @since 1.2.8-beta
 */
function putPostTitle(): void { echo getPostTitle(); }

/**
 * Alias for the Post class' getPostAuthor function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostAuthor()
 * @return string
 */
function getPostAuthor(): string {
	global $rs_post;
	
	return $rs_post->getPostAuthor();
}

/**
 * Display the post's author.
 * @since 1.2.8-beta
 */
function putPostAuthor(): void { echo getPostAuthor(); }

/**
 * Alias for the Post class' getPostDate function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostDate()
 * @return string
 */
function getPostDate(): string {
	global $rs_post;
	
	return $rs_post->getPostDate();
}

/**
 * Display the post's creation (publish) date.
 * @since 1.2.8-beta
 */
function putPostDate(): void { echo getPostDate(); }

/**
 * Alias for the Post class' getPostModDate function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostModDate()
 * @return string
 */
function getPostModDate(): string {
	global $rs_post;
	
	return $rs_post->getPostModDate();
}

/**
 * Display the post's modified date.
 * @since 1.2.8-beta
 */
function putPostModDate(): void { echo getPostModDate(); }

/**
 * Alias for the Post class' getPostContent function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostContent()
 * @return string
 */
function getPostContent(): string {
	global $rs_post;
	
	return $rs_post->getPostContent();
}

/**
 * Display the post's content.
 * @since 1.2.8-beta
 */
function putPostContent(): void { echo getPostContent(); }

/**
 * Alias for the Post class' getPostStatus function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostStatus()
 * @return string
 */
function getPostStatus(): string {
	global $rs_post;
	
	return $rs_post->getPostStatus();
}

/**
 * Display the post's status.
 * @since 1.2.8-beta
 */
function putPostStatus(): void { echo getPostStatus(); }

/**
 * Alias for the Post class' getPostSlug function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostSlug()
 * @param int $id -- The post's id.
 * @return string
 */
function getPostSlug(int $id): string {
	global $rs_post;
	
	return $rs_post->getPostSlug($id);
}

/**
 * Display the post's slug.
 * @since 1.2.8-beta
 *
 * @param int $id -- The post's id.
 */
function putPostSlug(int $id): void { echo getPostSlug($id); }

/**
 * Alias for the Post class' getPostParent function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostParent()
 * @return int
 */
function getPostParent(): int {
	global $rs_post;
	
	return $rs_post->getPostParent();
}

/**
 * Display the post's parent.
 * @since 1.2.8-beta
 */
function putPostParent(): void { echo getPostParent(); }

/**
 * Alias for the Post class' getPostType function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostType()
 * @return string
 */
function getPostType(): string {
	global $rs_post;
	
	return $rs_post->getPostType();
}

/**
 * Display the post's type.
 * @since 1.2.8-beta
 */
function putPostType(): void { echo getPostType(); }

/**
 * Alias for the Post class' getPostFeaturedImage function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostFeaturedImage()
 * @return string
 */
function getPostFeaturedImage(): string {
	global $rs_post;
	
	return $rs_post->getPostFeaturedImage();
}

/**
 * Display the post's featured image.
 * @since 1.2.8-beta
 */
function putPostFeaturedImage(): void { echo getPostFeaturedImage(); }

/**
 * Alias for the Post class' getPostMeta function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostMeta()
 * @param string $key -- The metadata key.
 * @return string
 */
function getPostMeta(string $key): string {
	global $rs_post;
	
	return $rs_post->getPostMeta($key);
}

/**
 * Display the post's metadata.
 * @since 1.2.8-beta
 *
 * @param string $key -- The metadata key.
 */
function putPostMeta(string $key): void { echo getPostMeta($key); }

/**
 * Alias for the Post class' getPostTerms function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostTerms()
 * @param string $taxonomy -- The term's taxonomy.
 * @param bool $linked (optional) -- Whether to link the terms.
 * @return array
 */
function getPostTerms(string $taxonomy = 'category', bool $linked = true): array {
	global $rs_post;
	
	return $rs_post->getPostTerms($taxonomy, $linked);
}

/**
 * Display the post's terms.
 * @since 1.2.8-beta
 *
 * @param string $taxonomy (optional) -- The term's taxonomy.
 * @param bool $linked (optional) -- Whether to link the terms.
 */
function putPostTerms(string $taxonomy = 'category', bool $linked = true): void {
	echo empty(getPostTerms($taxonomy)) ? 'None' : implode(', ', getPostTerms($taxonomy, $linked));
}

/**
 * Alias for the Post class' getPostComments function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostComments()
 * @param bool $feed_only (optional) -- Whether to only display the comment feed.
 */
function getPostComments(bool $feed_only = false): void {
	global $rs_post;
	
	$rs_post->getPostComments($feed_only);
}

/**
 * Alias for the Post class' getPostUrl function.
 * @since 1.2.8-beta
 *
 * @see Post::getPostUrl()
 * @return string
 */
function getPostUrl(): string {
	global $rs_post;
	
	return $rs_post->getPostUrl();
}

/**
 * Display the post's full URL.
 * @since 1.2.8-beta
 */
function putPostUrl(): void { echo getPostUrl(); }

/**
 * Alias for the Post class' postHasFeaturedImage function.
 * @since 1.2.8-beta
 *
 * @see Post::postHasFeaturedImage()
 * @return bool
 */
function postHasFeaturedImage(): bool {
	global $rs_post;
	
	return $rs_post->postHasFeaturedImage();
}

/**
 * Construct the post's excerpt text.
 * @since 1.2.9-beta
 *
 * @param int $num_words (optional) -- The number of words to include before trimming.
 * @return string
 */
function getPostExcerpt(int $num_words = 25): string {
	return trimWords(str_replace(array("\n", "\r", "  "), ' ', strip_tags(getPostContent())), $num_words, '...');
}

/**
 * Display the post's excerpt text.
 * @since 1.2.9-beta
 *
 * @param int $num_words (optional) -- The number of words to include before trimming.
 */
function putPostExcerpt(int $num_words = 25): void { echo getPostExcerpt($num_words); }

/*------------------------------------*\
    TERMS
\*------------------------------------*/

/**
 * Check whether the currently viewed page is a term.
 * @since 1.2.8-beta
 *
 * @return bool
 */
function isTerm(): bool {
	global $rs_term;
	
	return isset($rs_term);
}

/**
 * Alias for the Term class' getTermId function.
 * @since 1.2.8-beta
 *
 * @see Term::getTermId()
 * @return int
 */
function getTermId(): int {
	global $rs_term;
	
	return $rs_term->getTermId();
}

/**
 * Display the term's id.
 * @since 1.2.8-beta
 */
function putTermId(): void { echo getTermId(); }

/**
 * Alias for the Term class' getTermName function.
 * @since 1.2.8-beta
 *
 * @see Term::getTermName()
 * @return string
 */
function getTermName(): string {
	global $rs_term;
	
	return $rs_term->getTermName();
}

/**
 * Display the term's name.
 * @since 1.2.8-beta
 */
function putTermName(): void { echo getTermName(); }

/**
 * Alias for the Term class' getTermSlug function.
 * @since 1.2.8-beta
 *
 * @see Term::getTermSlug()
 * @param int $id -- The term's id.
 * @return string
 */
function getTermSlug(int $id): string {
	global $rs_term;
	
	return $rs_term->getTermSlug($id);
}

/**
 * Display the term's slug.
 * @since 1.2.8-beta
 *
 * @param int $id -- The term's id.
 */
function putTermSlug(int $id): void { echo getTermSlug($id); }

/**
 * Alias for the Term class' getTermTaxonomy function.
 * @since 1.2.8-beta
 *
 * @see Term::getTermTaxonomy()
 * @return string
 */
function getTermTaxonomy(): string {
	global $rs_term;
	
	return $rs_term->getTermTaxonomy();
}

/**
 * Display the term's taxonomy.
 * @since 1.2.8-beta
 */
function putTermTaxonomy(): void { echo getTermTaxonomy(); }

/**
 * Alias for the Term class' getTermParent function.
 * @since 1.2.8-beta
 *
 * @see Term::getTermParent()
 * @return int
 */
function getTermParent(): int {
	global $rs_term;
	
	return $rs_term->getTermParent();
}

/**
 * Display the term's parent.
 * @since 1.2.8-beta
 */
function putTermParent(): void { echo getTermParent(); }

/**
 * Alias for the Term class' getTermUrl function.
 * @since 1.2.8-beta
 *
 * @see Term::getTermUrl()
 * @return string
 */
function getTermUrl(): string {
	global $rs_term;
	
	return $rs_term->getTermUrl();
}

/**
 * Display the term's full URL.
 * @since 1.2.8-beta
 */
function putTermUrl(): void { echo getTermUrl(); }

/**
 * Alias for the getTermId function.
 * @since 1.2.8-beta
 *
 * @see getTermId()
 * @return int
 */
function getCategoryId(): int { return getTermId(); }

/**
 * Alias for the putTermId function.
 * @since 1.2.8-beta
 *
 * @see putTermId()
 */
function putCategoryId(): void { putTermId(); }

/**
 * Alias for the getTermName function.
 * @since 1.2.8-beta
 *
 * @see getTermName()
 * @return string
 */
function getCategoryName(): string { return getTermName(); }

/**
 * Alias for the putTermName function.
 * @since 1.2.8-beta
 *
 * @see putTermName()
 */
function putCategoryName(): void { putTermName(); }

/**
 * Alias for the getTermSlug function.
 * @since 1.2.8-beta
 *
 * @see getTermSlug()
 * @param int $id -- The term's id.
 * @return string
 */
function getCategorySlug(int $id): string { return getTermSlug($id); }

/**
 * Alias for the putTermSlug function.
 * @since 1.2.8-beta
 *
 * @see putTermSlug()
 * @param int $id -- The term's id.
 */
function putCategorySlug(int $id): void { putTermSlug($id); }

/**
 * Alias for the getTermParent function.
 * @since 1.2.8-beta
 *
 * @see getTermParent()
 * @return int
 */
function getCategoryParent(): int { return getTermParent(); }

/**
 * Alias for the putTermParent function.
 * @since 1.2.8-beta
 *
 * @see putTermParent()
 */
function putCategoryParent(): void { putTermParent(); }

/**
 * Alias for the getTermUrl function.
 * @since 1.2.8-beta
 *
 * @see getTermUrl()
 * @return string
 */
function getCategoryUrl(): string { return getTermUrl(); }

/**
 * Alias for the putTermUrl function.
 * @since 1.2.8-beta
 *
 * @see putTermUrl()
 */
function putCategoryUrl(): void { putTermUrl(); }

/**
 * Fetch a user-friendly version of the term's taxonomy name.
 * @since 1.3.0-beta
 *
 * @return string
 */
function getTermTaxName(): string {
	global $rs_taxonomies;
	
	return $rs_taxonomies[getTermTaxonomy()]['labels']['name_singular'];
}

/**
 * Display a user-friendly version of the term's taxonomy name.
 * @since 1.3.0-beta
 */
function putTermTaxName(): void { echo getTermTaxName(); }

/**
 * Fetch all posts associated with the current term.
 * @since 2.4.1-alpha
 *
 * @param mixed $_term (optional) -- The term.
 * @param string $order_by (optional) -- The column to order by.
 * @param string $order (optional) -- The sort order.
 * @param int $limit (optional) -- The post limit.
 * @return array
 */
function getTermPosts(mixed $_term = null, string $order_by = 'created', string $order = 'DESC', int $limit = 0): array {
	global $rs_query;
	
	$posts = array();
	
	if(!is_null($_term) && $_term !== 0) {
		if(is_int($_term))
			$term = $_term;
		else
			$term = getTerm($_term)->getTermId();
	} else {
		$term = getTermId();
	}
	
	$relationships = $rs_query->select(getTable('tr'), 'post', array(
		'term' => $term
	));
	
	foreach($relationships as $relationship) {
		// Skip the post if it isn't published
		if(!$rs_query->selectRow(getTable('p'), 'id', array(
			'id' => $relationship['tr_post'],
			'status' => 'published'
		))) {
			continue;
		}
		
		$posts[] = $rs_query->selectRow(getTable('p'), '*', array(
			'id' => $relationship['tr_post']
		), array(
			'order_by' => $order_by,
			'order' => $order,
			'limit' => $limit
		));
	}
	
	return $posts;
}

/**
 * Display all posts associated with the current term.
 * @since 1.3.0-beta
 *
 * @param mixed $_term (optional) -- The term.
 * @param string $order_by (optional) -- The column to order by.
 * @param string $order (optional) -- The sort order.
 * @param int $limit (optional) -- The post limit.
 */
function putTermPosts(mixed $_term = null, string $order_by = 'created', string $order = 'DESC', int $limit = 0): void {
	$posts = getTermPosts($_term, $order_by, $order, $limit);
	
	if(empty($posts)) {
		domTagPr('p', array(
			'content' => 'There are no posts to display!'
		));
	} else {
		$content = array();
		
		foreach($posts as $post) {
			$permalink = getPost($post['p_slug'])->getPostPermalink($post['p_type'], $post['p_parent'], $post['p_slug']);
			
			$content[] = domTag('li', array(
				'content' => domTag('a', array(
					'href' => $permalink,
					'content' => $post['p_title']
				))
			));
		}
		
		domTagPr('ul', array(
			'content' => implode('', $content)
		));
	}
}

/*------------------------------------*\
    QUERIES
\*------------------------------------*/

/**
 * Alias for the Query class' select method.
 * @since 1.3.8-beta
 *
 * @see Query::select()
 * @param string|array $table -- The table name and optionally, table prefix.
 * @param string|array $cols (optional) -- The column(s) to query.
 * @param array $where (optional) -- The where clause.
 * @param array $args (optional) -- Additional args (e.g., `order_by`, `order`, `limit`).
 * @return int|array
 */
function querySelect(string|array $table, string|array $cols = '*', array $where = array(), array $args = array()): int|array {
	global $rs_query;
	
	return $rs_query->select($table, $cols, $where, $args);
}

/**
 * Alias for the Query class' selectRow method.
 * @since 1.3.8-beta
 *
 * @see Query::selectRow()
 * @param string|array $table -- The table name and optionally, table prefix.
 * @param string|array $cols (optional) -- The column(s) to query.
 * @param array $where (optional) -- The where clause.
 * @param array $args (optional) -- Additional args (e.g., `order_by`, `order`, `limit`).
 * @return int|array
 */
function querySelectRow(string|array $table, string|array $cols = '*', array $where = array(), array $args = array()): int|array {
	global $rs_query;
	
	return $rs_query->selectRow($table, $cols, $where, $args);
}

/**
 * Alias for the Query class' selectField method.
 * @since 1.3.8-beta
 *
 * @see Query::selectField()
 * @param string|array $table -- The table name and optionally, table prefix.
 * @param string $col -- The column to query.
 * @param array $where (optional) -- The where clause.
 * @param array $args (optional) -- Additional args (e.g., `order_by`, `order`, `limit`).
 * @return string
 */
function querySelectField(string|array $table, string $col, array $where = array(), array $args = array()): string {
	global $rs_query;
	
	return $rs_query->selectField($table, $col, $where, $args);
}

/**
 * Alias for the Query class' insert method.
 * @since 1.3.8-beta
 *
 * @see Query::insert()
 * @param string|array $table -- The table name and optionally, table prefix.
 * @param array $data -- The data to insert.
 * @param array $args (optional) -- Additional args.
 * @return int
 */
function queryInsert(string|array $table, array $data, array $args = array()): int {
	global $rs_query;
	
	return $rs_query->insert($table, $data, $args);
}

/**
 * Alias for the Query class' update method.
 * @since 1.3.8-beta
 *
 * @see Query::update()
 * @param string|array $table -- The table name and optionally, table prefix.
 * @param array $data -- The data to update.
 * @param array $where (optional) -- The where clause.
 * @param array $args (optional) -- Additional args.
 */
function queryUpdate(string|array $table, array $data, array $where = array(), array $args = array()): void {
	global $rs_query;
	
	$rs_query->update($table, $data, $where, $args);
}

/**
 * Alias for the Query class' delete method.
 * @since 1.3.8-beta
 *
 * @see Query::delete()
 * @param string|array $table -- The table name and optionally, table prefix.
 * @param array $where (optional) -- The where clause.
 * @param array $args (optional) -- Additional args.
 */
function queryDelete(string|array $table, array $where = array(), array $args = array()): void {
	global $rs_query;
	
	$rs_query->delete($table, $where, $args);
}

/*------------------------------------*\
    MISCELLANEOUS
\*------------------------------------*/

/**
 * Check whether a page template exists.
 * @since 2.3.3-alpha
 *
 * @param string $template -- The template's name.
 * @param string $dir -- The template's directory.
 * @return bool
 */
function templateExists(string $template, string $dir): bool {
    return file_exists(slash($dir) . $template);
}

/**
 * Fetch the theme's header template.
 * @since 1.5.5-alpha
 *
 * @param string $template (optional) -- The template's name.
 * @return bool
 */
function getHeader(string $template = ''): bool {
	global $rs_theme_path;
	
	if(!file_exists($rs_theme_path . '/header.php') && !file_exists(slash($rs_theme_path) . $template . '.php')) {
		// Don't load anything; our header template doesn't exist
		return false;
	} else {
		// Include the header template
		requireFile(slash($rs_theme_path) . (!empty($template) ? $template : 'header') . '.php');
		return true;
	}
}

/**
 * Fetch the theme's footer template.
 * @since 1.5.5-alpha
 *
 * @param string $template (optional) -- The template's name.
 * @return bool
 */
function getFooter(string $template = ''): bool {
	global $rs_theme_path;
	
	if(!file_exists($rs_theme_path . '/footer.php') && !file_exists(slash($rs_theme_path) . $template . '.php')) {
		// Don't load anything; our footer template doesn't exist
		return false;
	} else {
		// Include the footer template
		requireFile(slash($rs_theme_path) . (!empty($template) ? $template : 'footer') . '.php');
		return true;
	}
}

/**
 * Construct and display the page title.
 * @since 1.1.3-beta
 */
function pageTitle(): void {
	if(isPost())
		!empty(getPostMeta('title')) ? putPostMeta('title') : putPostTitle();
	else
		putTermName();
	?> ▸ <?php putSetting('site_title');
}

/**
 * Set up all of the meta tags for the <head> section.
 * @since 1.1.3-beta
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
	<?php if(isPost() && !getPostMeta('index_post')): ?>
		<meta name="robots" content="noindex, follow">
	<?php endif; ?>
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