<?php
/**
 * Administrative functions.
 * @since Alpha 1.0.2
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
 * @since Alpha 1.2.0
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
 * @since Alpha 1.2.0
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
 * @since Alpha 1.2.0
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
 * Populate the settings table.
 * @since Alpha 1.3.0
 *
 * @param array $data
 * @return null
 */
function populateSettings($data) {
	global $rs_query;
	
	// Settings
	$settings = array('site_title'=>$data['site_title'], 'description'=>'', 'site_url'=>'', 'admin_email'=>$data['admin_email'], 'default_user_role'=>'', 'home_page'=>'', 'do_robots'=>$data['do_robots']);
	
	// Create the settings
	foreach($settings as $name=>$value)
		$rs_query->insert('settings', array('name'=>$name, 'value'=>$value));
}

/**
 * Populate the users table.
 * @since Alpha 1.3.1
 *
 * @param array $data
 * @return null
 */
function populateUsers($data) {
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
}

/**
 * Create a nav item for the admin navigation.
 * @since Alpha 1.2.5
 *
 * @param string $caption (optional; default: 'Nav Item')
 * @param string $link (optional; default: '')
 * @param string|array $subnav (optional; default: '')
 * @return null;
 */
function adminNavItem($caption = 'Nav Item', $link = '', $subnav = '') {
	$content = '<li><a href="'.(!empty($link) ? trailingSlash(ADMIN).$link : 'javascript:void(0)').'">'.$caption.'</a>';
	
	if(!empty($subnav)) {
		if(!is_array($subnav)) return;
		
		$content .= '<ul class="subnav">';
		
		for($i = 0; $i < count($subnav[0]); $i++)
			$content .= '<li><a href="'.(!empty($subnav[1][$i]) ? trailingSlash(ADMIN).$subnav[1][$i] : 'javascript:void(0)').'">'.(!empty($subnav[0][$i]) ? $subnav[0][$i] : 'Subnav Item').'</a></li>';
		
		$content .= '</ul>';
	}
	
	$content .= '</li>';
	
	echo $content;
}

/**
 * Get statistics for a specific set of table entries.
 * @since Alpha 1.2.5
 *
 * @param string $table
 * @param string $field (optional; default: '')
 * @param string $value (optional; default: '')
 * @return int
 */
function getStatistics($table, $field = '', $value = '') {
	global $rs_query;
	
	if(empty($field) || empty($value))
		return $rs_query->select($table, 'COUNT(*)');
	else
		return $rs_query->select($table, 'COUNT(*)', array($field=>$value));
}

/**
 * Create and display a bar graph of site statistics.
 * @since Alpha 1.2.4
 *
 * @param array $bars
 * @return null
 */
function statsBarGraph($bars) {
	//if(!is_countable($bars)) return;  Requires PHP 7.3
	
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
 * @since Alpha 1.2.1
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
 * @since Alpha 1.2.1
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
 * @since Alpha 1.2.1
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
 * Construct a table cell.
 * @since Alpha 1.2.1
 *
 * @param string $data
 * @param string $class (optional; default: '')
 * @return string
 */
function tableCell($data, $class = '') {
	return '<td'.(!empty($class) ? ' class="'.$class.'"' : '').'>'.$data.'</td>';
}

/**
 * Construct a form HTML tag.
 * @since Alpha 1.2.0
 *
 * @param array $args
 * @return string
 */
function formTag($args) {
	switch($args['tag']) {
		case 'input':
			$tag = '<input type="'.($args['type'] ?? 'text').'"'.(!empty($args['id']) ? ' id="'.$args['id'].'"' : '').(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').(!empty($args['name']) ? ' name="'.$args['name'].'"' : '').(!empty($args['value']) ? ' value="'.$args['value'].'"' : '').'>';
			break;
		case 'select':
			$tag = '<select'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').(!empty($args['name']) ? ' name="'.$args['name'].'"' : '').'>'.$args['content'].'</select>';
			break;
		case 'textarea':
			$tag = '<textarea'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').(!empty($args['name']) ? ' name="'.$args['name'].'"' : '').(!empty($args['cols']) ? ' cols="'.$args['cols'].'"' : '').(!empty($args['rows']) ? ' rows="'.$args['rows'].'"' : '').'>'.$args['value'].'</textarea>';
			break;
		case 'img':
			$tag = '<img'.(!empty($args['src']) ? ' src="'.$args['src'].'"' : '').(!empty($args['width']) ? ' width="'.$args['width'].'"' : '').'>';
			break;
		case 'hr':
			$tag = '<hr'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').'>';
			break;
		case 'br':
			$tag = '<br'.(!empty($args['class']) ? ' class="'.$args['class'].'"' : '').'>';
			break;
		default:
			$tag = '';
	}
	
	if(!empty($args['label'])) {
		$label = '<label'.(!empty($args['label']['id']) ? ' id="'.$args['label']['id'].'"' : '').(!empty($args['label']['class']) ? ' class="'.$args['label']['class'].'"' : '').'>';
		$content = (!empty($args['label']['content']) ? $args['label']['content'] : '').'</label>';
		$tag = $label.$tag.$content;
	}
	
	return $tag;
}

/**
 * Construct a form row.
 * @since Alpha 1.1.2
 *
 * @param string|array $label (optional; default: '')
 * @param array $args (optional; unlimited)
 * @return string
 */
function formRow($label = '', ...$args) {
	if(count($args) !== count($args, COUNT_RECURSIVE) && count($args) === 1)
		$args = array_merge(...$args);
	
	if(!empty($label)) {
		if(is_array($label)) {
			$required = array_pop($label);
			$label = implode('', $label);
		}
		
		$row = '<th><label'.(!empty($args['name']) ? ' for="'.$args['name'].'"' : '').'>'.$label.(!empty($required) && $required === true ? ' <span class="small">(required)</span>' : '').'</label></th>';
		$row .= '<td>';
		
		if(count($args) > 0) {
			if(count($args) !== count($args, COUNT_RECURSIVE)) {
				foreach($args as $arg)
					$row .= formTag($arg);
			} else {
				$row .= formTag($args);
			}
		}
		
		$row .= '</td>';
	} else {
		$row = '<td colspan="2">';
		
		if(count($args) > 0) {
			if(count($args) !== count($args, COUNT_RECURSIVE)) {
				foreach($args as $arg)
					$row .= formTag($arg);
			} else {
				$row .= formTag($args);
			}
		}
		
		$row .= '</td>';
	}
	
	return '<tr>'.$row.'</tr>';
}

/**
 * Format a date string.
 * @since Alpha 1.2.1
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
 * @since Alpha 1.3.0
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