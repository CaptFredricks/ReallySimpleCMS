<?php
/**
 * Front end functions.
 * @since 1.0.0[a]
 */

// Autoload classes
spl_autoload_register(function($class_name) {
	require_once 'class-'.strtolower($class_name).'.php';
});

//$rs_post = new Post;

function getTestId($id) {
	global $rs_query;
	
	//$values = $rs_query->select('test', array('id', 'name'), array('id'=>array('not IN', 1, 2)), '', '', '1');
	//$values = $rs_query->selectRow('test', array('id', 'name'), '', 'id', 'DESC', 1);
	$values1 = $rs_query->selectRow('users', 'COUNT(*)');
	//$rs_query->insert('test', array('id'=>6, 'name'=>'Andrew Jackson'));
	//$rs_query->update('test', array('birthdate'=>'NOW()'), array('id'=>array(6)));
	//$rs_query->delete('test', array('id'=>array(6, 7)));
	
	var_dump($values1);
	
	echo '<table>';
	
	/*if(count($values) !== count($values, COUNT_RECURSIVE)) {
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
	}*/
	
	echo '</table>';
}