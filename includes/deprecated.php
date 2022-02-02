<?php
/**
 * A list of deprecated functions that may be used again later on (ordered by descending deprecated version).
 * @since 1.1.0[a]
 */

/**
 * Check whether a filename exists in the database.
 * @since 2.1.0[a]
 * @deprecated since 1.0.9[b]
 *
 * @param string $filename
 * @return bool
 */
function filenameExists($filename) {
	// Extend the Query object
	global $rs_query;
	
	// Return true if the filename appears in the database
	return $rs_query->select('postmeta', 'COUNT(*)', array('_key' => 'filename', 'value' => array('LIKE', $filename.'%'))) > 0;
}

/**
 * Check whether the current 'page' is a category archive.
 * @since 2.4.0[a]
 * @deprecated since 1.0.6[b]
 *
 * @param string $base (optional; default: 'category')
 * @return bool
 */
function isCategory($base = 'category') {
	return strpos($_SERVER['REQUEST_URI'], $base) !== false;
}

/**
 * Construct a post's permalink. (Admin Post class)
 * @since 1.4.9[a]
 * @deprecated since 1.0.0[b]
 *
 * @access private
 * @param int $parent
 * @param string $slug (optional; default: '')
 * @return string
 */
private function getPermalink($parent, $slug = '') {
	return getPermalink('post', $parent, $slug);
}

/**
 * Fetch the slug from the URL.
 * @since 2.2.3[a]
 * @deprecated since 2.2.5[a]
 *
 * @return string
 */
function getPageSlug() {
	// Check whether the current page is the home page
	if($_SERVER['REQUEST_URI'] === '/') {
		// Extend the Query object
		global $rs_query;
		
		// Fetch the home page's id from the database
		$home_page = $rs_query->selectField('settings', 'value', array('name' => 'home_page'));
		
		// Create a Post object
		$rs_post = new Post;
		
		// Return the slug
		return $rs_post->getPostSlug($home_page, false);
	} else {
		// Create an array from the page's URI
		$uri = explode('/', $_SERVER['REQUEST_URI']);
		
		// Return the slug
		return array_pop($uri);
	}
}

/**
 * Fetch a post's data.
 * @since 2.2.0[a]
 * @deprecated since 2.2.3[a]
 *
 * @param string $callback
 * @param string|array $data (optional; default: '')
 * @return object
 */
function getPost($callback, $data = array()) {
	// Create a Post object
	$rs_post = new Post;
	
	// Check whether the data is an array and turn it into one if not
	if(!is_array($data)) $data = array($data);
	
	// Return the post's data
	return call_user_func_array(array($rs_post, 'getPost'.$callback), $data);
}

/**
 * Check whether a menu item is a sibling of another menu item.
 * @since 1.8.9[a]
 * @deprecated since 1.8.12[a]
 *
 * @access private
 * @param int $id
 * @param int $sibling
 * @return bool
 */
private function isSibling($id, $sibling) {
	// Extend the Query class
	global $rs_query;
	
	// Fetch the parent of the menu item from the database
	$parent = (int)$rs_query->selectField('posts', 'parent', array('id' => $id));
	
	// Fetch the parent of the potential sibling from the database
	$sibling_parent = (int)$rs_query->selectField('posts', 'parent', array('id' => $sibling));
	
	// Return true if both menu items share the same parent
	return $parent === $sibling_parent;
}

/**
 * Populate the term_relationships table.
 * @since 1.5.0[a]
 * @deprecated since 1.7.0[a]
 *
 * @param array $data
 * @return null
 */
function populateTermRelationships($data) {
	// Extend the Query class
	global $rs_query;
	
	// Insert the term relationships into the database
	$rs_query->insert('term_relationships', array('term' => $data['term'], 'post' => $data['post']));
	
	// Update the term's count
	$rs_query->update('terms', array('count' => 1), array('id' => $data['term']));
}

/**
 * Select one or more rows from the database and return them.
 * @since 1.0.1[a]
 * @deprecated since 1.1.0[a]
 *
 * @param array $args
 * @return array
 */
function selectQuery($args) {
	if(!empty($args)) {
		$data = array();
		$table = !empty($args['table']) ? $args['table'] : '';
		$cols = !empty($args['cols']) ? implode(', ', $args['cols']) : '*';
		$field = !empty($args['where']['field']) ? $args['where']['field'] : '';
		$values = !empty($args['where']['values']) ? $args['where']['values'] : '';
		$operator = !empty($args['where']['operator']) ? ' '.strtoupper($args['where']['operator']).' ' : ' IN ';
		$placeholders = is_array($values) ? implode(', ', array_fill(0, count($values), '?')) : '?';
		$params = is_array($values) ? $values : explode(', ', $values);
		
		//$raw_keys = !empty($args['cols']) ? $args['cols'] : array();
		//$undup_keys = $this->unduplicateKeys($raw_keys);
		//$keys = ':'.implode(', :', $undup_keys);
		
		if(!empty($table)) {
			$query_string = 'SELECT '.$cols;
			$query_string .= ' FROM '.$table;
			$query_string .= !empty($args['where']) ? (!empty($field) ? ' WHERE '.$field.$operator.'('.$placeholders.')' : '') : '';
			
			echo '<pre>'.$query_string.'</pre>';
			
			try {
				$select_query = $this->conn->prepare($query_string);
				!empty($values) ? $select_query->execute($params) : $select_query->execute();
				
				while($row = $select_query->fetch(PDO::FETCH_ASSOC))
					$data[] = $row;
				
				return $data;
			} catch(PDOException $e) {
				logError($e);
			}
		} else {
			echo 'Table not specified!';
		}
	} else {
		echo 'Query parameters not selected!';
	}
}

/**
 * Insert a row into the database.
 * @since 1.0.1[a]
 * @deprecated since 1.1.0[a]
 *
 * @param array $args
 * @return null
 */
function insertQuery($args) {
	if(!empty($args)) {
		$table = !empty($args['table']) ? $args['table'] : '';
		$cols = !empty($args['cols']) ? implode(', ', $args['cols']) : '';
		$values = !empty($args['values']) ? $args['values'] : '';
		$placeholders = is_array($values) ? implode(', ', array_fill(0, count($values), '?')) : '?';
		$params = is_array($values) ? $values : explode(', ', $values);
		
		if(!empty($table)) {
			if(!empty($values)) {
				$query_string = 'INSERT INTO '.$table;
				$query_string .= !empty($cols) ? ' ('.$cols.') ' : ' ';
				$query_string .= 'VALUES ('.$placeholders.')';
				
				echo '<pre>'.$query_string.'</pre>';
				
				try {
					$insert_query = $this->conn->prepare($query_string);
					$insert_query->execute($params);
					
					echo 'Record inserted successfully!';
				} catch(PDOException $e) {
					logError($e);
				}
			} else {
				echo 'At least one value must be provided!';
			}
		} else {
			echo 'Table not specified!';
		}
	} else {
		echo 'Query parameters not selected!';
	}
}

/**
 * Update an existing row in the database.
 * @since 1.0.2[a]
 * @deprecated since 1.1.0[a]
 *
 * @param array $args
 * @return null
 */
function updateQuery($args) {
	if(!empty($args)) {
		$data = array();
		$table = !empty($args['table']) ? $args['table'] : '';
		$cols = !empty($args['cols']) ? (is_array($args['cols']) ? $args['cols'] : explode(', ', $args['cols'])) : '';
		$values = !empty($args['values']) ? (is_array($args['values']) ? $args['values'] : explode(', ', $args['values'])) : '';
		$placeholders = is_array($values) ? array_fill(0, count($values), '?') : '?';
		$params = is_array($values) ? $values : explode(', ', $values);
		$field = !empty($args['where']['field']) ? $args['where']['field'] : '';
		$w_values = !empty($args['where']['values']) ? $args['where']['values'] : '';
		$operator = !empty($args['where']['operator']) ? ' '.strtoupper($args['where']['operator']).' ' : ' IN ';
		$w_placeholders = is_array($w_values) ? implode(', ', array_fill(0, count($w_values), '?')) : '?';
		$w_params = is_array($w_values) ? $w_values : explode(', ', $w_values);
		$params = !empty($w_params) ? array_merge($params, $w_params) : $params;
		
		if(!empty($table)) {
			if(!empty($cols) && !empty($values)) {
				$query_string = 'UPDATE '.$table;
				$query_string .= ' SET ';
				
				for($i = 0; $i < count($cols); $i++)
					$query_string .= $cols[$i].' = '.$placeholders[$i].($i < count($cols) - 1 ? ', ' : '');
				
				$query_string .= !empty($args['where']) ? (!empty($field) ? ' WHERE '.$field.$operator.'('.$w_placeholders.')' : '') : '';
				
				echo '<pre>'.$query_string.'</pre>';
				
				try {
					$update_query = $this->conn->prepare($query_string);
					$update_query->execute($params);
					
					echo 'Record(s) updated successfully!';
				} catch(PDOException $e) {
					logError($e);
				}
			} else {
				echo 'At least one column and value must be provided!';
			}
		} else {
			echo 'Table not specified!';
		}
	} else {
		echo 'Query parameters not selected!';
	}
}

/**
 * Unduplicate query placeholder keys.
 * @since 1.0.1[a]
 * @deprecated since 1.1.0[a]
 *
 * @param array $keys
 * @return array
 */
function unduplicateKeys($keys) {
	$undup_keys = $arr_values = array();
	$arr_count = array_count_values($keys);
	$arr_keys = array_keys($arr_count);
	$index = 0;
	
	for($i = 0; $i < count($arr_keys); $i++)
		$arr_values[] = 2;
	
	foreach($keys as $key) {
		if(in_array($key, $undup_keys, true)) {
			for($j = 0; $j < count($arr_keys); $j++) {
				if($arr_keys[$j] === $key) {
					$undup_keys[$index] = $key.'_'.$arr_values[$j];
					$arr_values[$j]++;
				}
			}
		} else {
			$undup_keys[$index] = $key;
		}
		
		$index++;
	}
	
	return $undup_keys;
}

/**
 * Delete a row from the database.
 * @since 1.0.1[a]
 * @deprecated since 1.0.3[a]
 *
 * @param array $args
 * @return null
 */
function deleteQuery($args) {
	if(!empty($args)) {
		$table = !empty($args['table']) ? $args['table'] : '';
		$field = !empty($args['where']['field']) ? $args['where']['field'] : '';
		$values = !empty($args['where']['values']) ? $args['where']['values'] : '';
		$operator = !empty($args['where']['operator']) ? ' '.strtoupper($args['where']['operator']).' ' : ' IN ';
		$placeholders = is_array($values) ? implode(', ', array_fill(0, count($values), '?')) : '?';
		$params = is_array($values) ? $values : explode(', ', $values);
		
		if(!empty($table)) {
			$query_string = 'DELETE FROM '.$table;
			$query_string .= !empty($args['where']) ? (!empty($field) ? ' WHERE '.$field.$operator.'('.$placeholders.')' : '') : '';
			
			echo '<pre>'.$query_string.'</pre>';
			
			try {
				$delete_query = $this->conn->prepare($query_string);
				!empty($values) ? $delete_query->execute($params) : $delete_query->execute();
				
				echo 'Record(s) deleted successfully!';
			} catch(PDOException $e) {
				logError($e);
			}
		} else {
			echo 'Table not specified!';
		}
	} else {
		echo 'Query parameters not selected!';
	}
}