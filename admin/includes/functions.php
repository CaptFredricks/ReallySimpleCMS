<?php
/**
 * Administrative functions.
 * @since 1.0.2[a]
 */

// Path to the admin stylesheets directory
if(!defined('ADMIN_STYLES')) define('ADMIN_STYLES', ADMIN.INC.'/css');

// Path to the admin scripts directory
if(!defined('ADMIN_SCRIPTS')) define('ADMIN_SCRIPTS', ADMIN.INC.'/js');

// Autoload classes
spl_autoload_register(function($class_name) {
	require_once 'class-'.strtolower($class_name).'.php';
});

/**
 * Fetch an admin stylesheet.
 * @since 1.2.0[a]
 *
 * @param string $stylesheet
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getAdminStylesheet($stylesheet, $echo = true) {
	if($echo)
		echo '<link rel="stylesheet" href="'.trailingSlash(ADMIN_STYLES).$stylesheet.'">';
	else
		return '<link rel="stylesheet" href="'.trailingSlash(ADMIN_STYLES).$stylesheet.'">';
}

/**
 * Fetch an admin script.
 * @since 1.2.0[a]
 *
 * @param string $script
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getAdminScript($script, $echo = true) {
	if($echo)
		echo '<script src="'.trailingSlash(ADMIN_SCRIPTS).$script.'"></script>';
	else
		return '<script src="'.trailingSlash(ADMIN_SCRIPTS).$script.'"></script>';
}

/**
 * Construct a status message.
 * @since 1.2.0[a]
 *
 * @param string $text
 * @param bool $success (optional; default: false)
 * @return string
 */
function statusMessage($text, $success = false) {
	if($success === true) {
		$class = 'success';
	} else {
		$class = 'failure';
		
		switch($text) {
			case 'E': case 'e':
				$text = 'An unexpected error occurred. Please contact the system administrator.';
				break;
			case 'R': case 'r':
				$text = 'Required fields cannot be left blank!';
				break;
		}
	}
	
	return '<div class="status-message '.$class.'">'.$text.'</div>';
}

/**
 * Populate the users table.
 * @since 1.3.1[a]
 *
 * @param array $data
 * @return null
 */
function populateUsers($data) {
	// Extend the Query class
	global $rs_query;
	
	// Encrypt password
	$hashed_password = password_hash($data['password'], PASSWORD_BCRYPT, array('cost'=>10));
	
	// Create admin user
	$user = $rs_query->insert('users', array('username'=>$data['username'], 'password'=>$hashed_password, 'email'=>$data['email'], 'registered'=>'NOW()'));
	
	// Metadata
	$usermeta = array('first_name'=>'', 'last_name'=>'', 'avatar'=>0);
	
	// Create user metadata
	foreach($usermeta as $key=>$value)
		$rs_query->insert('usermeta', array('user'=>$user, '_key'=>$key, 'value'=>$value));
	
	// Return the user id (for posts table)
	return $user;
}

/**
 * Populate the posts table.
 * @since 1.3.7[a]
 *
 * @param int $author
 * @return null
 */
function populatePosts($author) {
	// Extend the Query class
	global $rs_query;
	
	// Create sample page
	$post = $rs_query->insert('posts', array('title'=>'Sample Page', 'author'=>$author, 'date'=>'NOW()', 'content'=>'This is just a sample page to get you started.', 'status'=>'published', 'slug'=>'sample', 'type'=>'page'));
	
	// Metadata
	$postmeta = array('title'=>'Sample Page', 'description'=>'Just a simple meta description for your sample page.');
	
	// Create post metadata
	foreach($postmeta as $key=>$value)
		$rs_query->insert('postmeta', array('post'=>$post, '_key'=>$key, 'value'=>$value));
	
	// Return the post id (for settings table)
	return $post;
}

/**
 * Populate the settings table.
 * @since 1.3.0[a]
 *
 * @param array $data
 * @return null
 */
function populateSettings($data) {
	// Extend the Query class
	global $rs_query;
	
	// Settings
	$settings = array('site_title'=>$data['site_title'], 'description'=>'', 'site_url'=>$data['site_url'], 'admin_email'=>$data['admin_email'], 'default_user_role'=>'', 'home_page'=>$data['home_page'], 'do_robots'=>$data['do_robots']);
	
	// Create the settings
	foreach($settings as $name=>$value)
		$rs_query->insert('settings', array('name'=>$name, 'value'=>$value));
}

/**
 * Create a nav item for the admin navigation.
 * @since 1.2.5[a]
 *
 * @param string $caption (optional; default: 'Nav Item')
 * @param string $link (optional; default: '')
 * @param string|array $subnav (optional; default: '')
 * @return null;
 */
function adminNavItem($caption = 'Nav Item', $link = '', $subnav = '') {
	// Get the current page
	$current = basename($_SERVER['PHP_SELF']);
	
	// Set current class
	//if($link === $current)
	?>
	<li <?php echo $link === $current ? 'class="current"' : ''; ?>>
		<a href="<?php echo !empty($link) ? trailingSlash(ADMIN).$link : 'javascript:void(0)'; ?>"><?php echo $caption; ?></a>
		<?php
		// Construct the subnav if parameters are provided
		if(!empty($subnav)) {
			// Return if the subnav isn't an array
			if(!is_array($subnav)) return;
			?>
			<ul class="subnav">
				<?php
				// Loop through the subnav items
				for($i = 0; $i < count($subnav[0]); $i++) {
					?>
					<li><a href="<?php echo !empty($subnav[1][$i]) ? trailingSlash(ADMIN).$subnav[1][$i] : 'javascript:void(0)'; ?>"><?php echo !empty($subnav[0][$i]) ? $subnav[0][$i] : 'Subnav Item'; ?></a></li>
					<?php
				}
				?>
			</ul>
			<?php
		}
		?>
	</li>
	<?php
}

/**
 * Get statistics for a specific set of table entries.
 * @since 1.2.5[a]
 *
 * @param string $table
 * @param string $field (optional; default: '')
 * @param string $value (optional; default: '')
 * @return int
 */
function getStatistics($table, $field = '', $value = '') {
	// Extend the Query class
	global $rs_query;
	
	if(empty($field) || empty($value))
		return $rs_query->select($table, 'COUNT(*)');
	else
		return $rs_query->select($table, 'COUNT(*)', array($field=>$value));
}

/**
 * Create and display a bar graph of site statistics.
 * @since 1.2.4[a]
 *
 * @param array $bars
 * @return null
 */
function statsBarGraph($bars) {
	//if(!is_countable($bars)) return;  <-- Requires PHP 7.3
	
	$stats = $links = array();
	
	foreach($bars as $bar) {
		if(!is_array($bar)) return;
		
		if(count($bar) === 3) {
			$stats[] = getStatistics($bar[0], $bar[1], $bar[2]);
			$links[] = $bar[0].'.php?'.$bar[1].'='.$bar[2];
		} else {
			$stats[] = getStatistics($bar[0]);
			$links[] = $bar[0].'.php';
		}
	}
	
	$max_count = max($stats);
	$num = ceil($max_count / 25);
	$num *= 5;
	
	$content = '<input type="hidden" id="max-ct" value="'.($num * 5).'">';
	$content .= '<div id="graph"><ul id="graph-y">';
	
	for($i = 5; $i >= 0; $i--)
		$content .= '<li><div class="y-value">'.($i * $num).'</div></li>';
	
	$content .= '</ul><ul id="graph-content">';
	$j = 0;
	
	foreach($bars as $bar) {
		$content .= '<li style="width:'.(1 / count($bars) * 100).'%;"><a class="bar" href="'.$links[$j].'" title="'.ucfirst(isset($bar[2]) ? $bar[2].'s' : $bar[0]).': '.$stats[$j].($stats[$j] === 1 ? ' entry' : ' entries').'">'.$stats[$j].'</a></li>';
		$j++;
	}
	
	$content .= '<ul id="graph-overlay">';
	
	for($k = 5; $k >= 0; $k--)
		$content .= '<li></li>';
	
	$content .= '</ul></ul><ul id="graph-x">';
	$l = 0;
	
	foreach($bars as $bar) {
		$content .= '<li style="width:'.(1 / count($bars) * 100).'%;"><a href="'.$links[$l].'" title="'.ucfirst(isset($bar[2]) ? $bar[2].'s' : $bar[0]).': '.$stats[$l].($stats[$l] === 1 ? ' entry' : ' entries').'">'.ucfirst(isset($bar[2]) ? $bar[2].'s' : $bar[0]).'</a></li>';
		$l++;
	}
	
	$content .= '</ul><span id="y-title">Count</span><span id="x-title">Category</span></div>';
	
	echo $content;
}

/**
 * Enable pagination.
 * @since 1.2.1[a]
 *
 * @param int $current (optional; default: 1)
 * @param int $per_page (optional; default: 20)
 * @return array
 */
function paginate($current = 1, $per_page = 20) {
	$page['current'] = $current;
	$page['per_page'] = $per_page;
	
	if($page['current'] === 1)
		$page['start'] = 0;
	else
		$page['start'] = ($page['current'] * $page['per_page']) - $page['per_page'];
	
	return $page;
}

/**
 * Construct pager navigation.
 * @since 1.2.1[a]
 *
 * @param int $current
 * @param int $page_count
 * @return string
 */
function pagerNav($current, $page_count) {
	return '<div class="pager">'.($current > 1 ? '<a class="pager-nav" href="?page=1" title="First Page">&laquo;</a><a class="pager-nav" href="?page='.($current - 1).'" title="Previous Page">&lsaquo;</a>' : '').($page_count > 0 ? ' Page '.$current.' of '.$page_count.' ' : '').($current < $page_count ? '<a class="pager-nav" href="?page='.($current + 1).'" title="Next Page">&rsaquo;</a><a class="pager-nav" href="?page='.$page_count.'" title="Last Page">&raquo;</a>' : '').'</div>';
}

/**
 * Construct a table header row.
 * @since 1.2.1[a]
 *
 * @param array $items
 * @return string
 */
function tableHeaderRow($items) {
	$row = '';
	
	foreach($items as $item)
		$row .= '<th>'.$item.'</th>';
	
	return '<tr>'.$row.'</tr>';
}

/**
 * Construct a table row.
 * @since 1.4.0[a]
 *
 * @param array $cells (optional; unlimited)
 * @return string
 */
function tableRow(...$cells) {
	// Return the table row (at least one cell must be provided)
	if(!empty($cells)) return '<tr>'.implode('', $cells).'</tr>';
}

/**
 * Construct a table cell.
 * @since 1.2.1[a]
 *
 * @param string $data
 * @param string $class (optional; default: '')
 * @param int $colspan (optional; default: 1)
 * @return string
 */
function tableCell($data, $class = '', $colspan = 1) {
	// Return the table cell
	return '<td'.(!empty($class) ? ' class="'.$class.'"' : '').($colspan > 1 ? ' colspan="'.$colspan.'"' : '').'>'.$data.'</td>';
}

/**
 * Construct a form HTML tag.
 * @since 1.2.0[a]
 *
 * @param string $tag
 * @param array $args (optional; default: null)
 * @return string
 */
function formTag($tag, $args = null) {
	switch($tag) {
		case 'input':
			// Construct an input tag
			$tag = '<input type="'.($args['type'] ?? 'text').'"'.(!empty($args['id']) ? ' id="'.$args['id'].'"' : '').(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').(!empty($args['name']) ? ' name="'.$args['name'].'"' : '').(!empty($args['maxlength']) ? ' maxlength="'.$args['maxlength'].'"' : '').(!empty($args['value']) || (isset($args['value']) && $args['value'] == 0) ? ' value="'.$args['value'].'"' : '').(!empty($args['placeholder']) ? ' placeholder="'.$args['placeholder'].'"' : '').(!empty($args['*']) ? $args['*'] : '').'>';
			break;
		case 'select':
			// Construct a select tag
			$tag = '<select'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').(!empty($args['name']) ? ' name="'.$args['name'].'"' : '').'>'.$args['content'].'</select>';
			break;
		case 'textarea':
			// Construct a textarea tag
			$tag = '<textarea'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').(!empty($args['name']) ? ' name="'.$args['name'].'"' : '').(!empty($args['cols']) ? ' cols="'.$args['cols'].'"' : '').(!empty($args['rows']) ? ' rows="'.$args['rows'].'"' : '').'>'.$args['content'].'</textarea>';
			break;
		case 'img':
			// Construct an img tag
			$tag = '<img'.(!empty($args['src']) ? ' src="'.$args['src'].'"' : '').(!empty($args['width']) ? ' width="'.$args['width'].'"' : '').'>';
			break;
		case 'hr':
			// Construct an hr tag
			$tag = '<hr'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').'>';
			break;
		case 'br':
			// Construct a br tag
			$tag = '<br'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').'>';
			break;
		case 'label':
			// Construct a label tag
			$tag = '<label'.(!empty($args['id']) ? ' id="'.$args['id'].'"' : '').(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').(!empty($args['for']) ? ' for="'.$args['for'].'"' : '').'>'.$args['content'].'</label>';
			break;
		default:
			// Don't construct a tag
			$tag = '';
	}
	
	if(!empty($args['label'])) {
		$label = '<label'.(!empty($args['label']['id']) ? ' id="'.$args['label']['id'].'"' : '').(!empty($args['label']['class']) ? ' class="'.$args['label']['class'].'"' : '').'>';
		$content = (!empty($args['label']['content']) ? $args['label']['content'] : '').'</label>';
		$tag = $label.$tag.$content;
	}
	
	// Return the constructed tag
	return $tag;
}

/**
 * Construct a form row.
 * @since 1.1.2[a]
 *
 * @param string|array $label (optional; default: '')
 * @param array $args (optional; unlimited)
 * @return string
 */
function formRow($label = '', ...$args) {
	// Breaks formTag if only one arg is supplied with a label
	//if(count($args) !== count($args, COUNT_RECURSIVE) && count($args) === 1)
		//$args = array_merge(...$args);
	
	if(!empty($label)) {
		// Check if the label is an array
		if(is_array($label)) {
			// Pop second value from the array
			$required = array_pop($label);
			
			// Convert the label array to a string
			$label = implode('', $label);
		}
		
		for($i = 0; $i < count($args); $i++) {
			// Break out of the loop if 'name' key is found
			if(array_key_exists('name', $args[$i])) break;
		}
		
		$row = '<th><label'.(!empty($args[$i]['name']) ? ' for="'.$args[$i]['name'].'"' : '').'>'.$label.(!empty($required) && $required === true ? ' <span class="required">*</span>' : '').'</label></th>';
		$row .= '<td>';
		
		if(count($args) > 0) {
			if(count($args) !== count($args, COUNT_RECURSIVE)) {
				foreach($args as $arg) {
					$tag = $arg['tag'];
					$row .= formTag($tag, $arg);
				}
			} else {
				$tag = $arg['tag'];
				$row .= formTag($tag, $args);
			}
		}
		
		$row .= '</td>';
	} else {
		$row = '<td colspan="2">';
		
		if(count($args) > 0) {
			if(count($args) !== count($args, COUNT_RECURSIVE)) {
				foreach($args as $arg) {
					$tag = $arg['tag'];
					$row .= formTag($tag, $arg);
				}
			} else {
				$tag = $arg['tag'];
				$row .= formTag($tag, $args);
			}
		}
		
		$row .= '</td>';
	}
	
	return '<tr>'.$row.'</tr>';
}

/**
 * Format a date string.
 * @since 1.2.1[a]
 *
 * @param string $date
 * @param string $format (optional; default: 'Y-m-d H:i:s')
 * @return string
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
	return date_format(date_create($date), $format);
}

/**
 * Generate a random password.
 * @since 1.3.0[a]
 *
 * @param int $length (optional; default: 15)
 * @param bool $special_chars (optional; default: true)
 * @param bool $extra_special_chars (optional; default: false)
 * @return string
 */
function generatePassword($length = 15, $special_chars = true, $extra_special_chars = false) {
	// Regular characters
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	
	// If desired, add the special characters
	if($special_chars) $chars .= '!@#$%^&*()';
	
	// If desired, add the extra special characters
	if($extra_special_chars) $chars .= '-_ []{}<>~`+=,.;:/?|';
	
	// Empty password
	$password = '';
	
	// Generate a random password
	for($i = 0; $i < $length; $i++)
		$password .= substr($chars, rand(0, strlen($chars) - 1), 1);
	
	return $password;
}

// For PHP < 7.3 compatibility
if(!function_exists('array_key_last')) {
	function array_key_last($array) {
		$key = null;

		if(is_array($array)) {
			end($array);
			$key = key($array);
		}

		return $key;
	}
}