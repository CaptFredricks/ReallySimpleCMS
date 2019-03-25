<?php
/**
 * Front end functions.
 * @since Alpha 1.0.0
 */

// Autoload classes
spl_autoload_register(function($class_name) {
	require_once 'class-'.strtolower($class_name).'.php';
});

// Create a Query object
$rs_query = new Query;

//$rs_post = new Post;

/**
 * Fetch a stylesheet.
 * @since Alpha 1.3.3
 *
 * @param string $stylesheet
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getStylesheet($stylesheet, $echo = true) {
	if($echo)
		echo '<link rel="stylesheet" href="'.trailingSlash(STYLES).$stylesheet.'">';
	else
		return '<link rel="stylesheet" href="'.trailingSlash(STYLES).$stylesheet.'">';
}

/**
 * Fetch a script.
 * @since Alpha 1.3.3
 *
 * @param string $script
 * @param bool $echo (optional; default: true)
 * @return null|string (null on $echo == true; string on $echo == false)
 */
function getScript($script, $echo = true) {
	if($echo)
		echo '<script src="'.trailingSlash(SCRIPTS).$script.'"></script>';
	else
		return '<script src="'.trailingSlash(SCRIPTS).$script.'"></script>';
}

function getTestId($id) {
	global $rs_query;
	
	//$values = $rs_query->select('test', array('id', 'name'), array('id'=>array('not IN', 1, 2)), '', '', '1');
	$values = $rs_query->selectRow('test', array('id', 'name'), '', 'id', 'DESC', 1);
	$values1 = $rs_query->selectRow('test', 'COUNT(*)');
	//$rs_query->insert('test', array('id'=>6, 'name'=>'Andrew Jackson'));
	//$rs_query->update('test', array('birthdate'=>'NOW()'), array('id'=>array(6)));
	//$rs_query->delete('test', array('id'=>array(6, 7)));
	
	var_dump($values1);
	
	echo '<table>';
	
	if(count($values) !== count($values, COUNT_RECURSIVE)) {
		foreach($values as $value) {
			echo '<tr>';
			echo '<th>ID</th>';
			echo '<td>'.$value['id'].'</td>';
			echo '</tr><tr>';
			echo '<th>Name</th>';
			echo '<td>'.$value['name'].'</td>';
			echo '</tr>';
		}
	} else {
		echo '<tr>';
		echo '<th>ID</th>';
		echo '<td>'.$values['id'].'</td>';
		echo '</tr><tr>';
		echo '<th>Name</th>';
		echo '<td>'.$values['name'].'</td>';
		echo '</tr>';
	}
	
	echo '</table>';
}