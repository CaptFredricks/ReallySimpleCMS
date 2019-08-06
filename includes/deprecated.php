<?php // A list of deprecated functions that may be used again later on.

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