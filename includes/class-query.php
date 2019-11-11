<?php
/**
 * Core class used to implement the Query object.
 * @since 1.0.0[a]
 * @author Jace Fincham <finchamjace@gmail.com>
 * @copyright (c) 2019, Jace Fincham. All rights reserved.
 */
class Query {
	/**
	 * The database connection.
	 * @since 1.0.0[a]
	 *
	 * @access private
	 * @var object
	 */
	private $conn;
	
	/**
	 * The status of the database connection.
	 * @since 1.3.0[a]
	 *
	 * @access public
	 * @var bool
	 */
	public $conn_status;
	
	/**
	 * Class constructor. Initializes the database connection.
	 * @since 1.0.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function __construct() {
		try {
			// Create a PDO object and plug in the database constant values
			$this->conn = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHAR, DB_USER, DB_PASS);
			
			// Turn off emulation of prepared statements
			$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			
			// Turn on error reporting
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			// Check whether any of the database constants are empty
			if(empty(DB_HOST) || empty(DB_NAME) || empty(DB_CHAR) || empty(DB_USER)) {
				// Set the connection status to false
				$this->conn_status = false;
			} else {
				// Set the connection status to true
				$this->conn_status = true;
			}
		} catch(PDOException $e) {
			// Log any errors
			logError($e);
			
			// Set the connection status to false
			$this->conn_status = false;
		}
	}
	
	/**
	 * Select one or more rows from the database and return them.
	 * @since 1.1.0[a]
	 *
	 * @access public
	 * @param string $table
	 * @param string|array $data (optional; default: '*')
	 * @param array $where (optional; default: array())
	 * @param string $order_by (optional; default: '')
	 * @param string $order (optional; default: 'ASC')
	 * @param string|array $limit (optional; default: '')
	 * @return array
	 */
	public function select($table, $data = '*', $where = array(), $order_by = '', $order = 'ASC', $limit = '') {
		// Stop execution and throw an error if no table is specified
		if(empty($table)) exit($this->errorMsg('table'));
		
		// Merge the data into a string if it's an array
		if(is_array($data)) $data = implode(', ', $data);
		
		// Construct the basic SQL statement
		$sql = 'SELECT '.$data.' FROM `'.$table.'`';
		
		// Check whether or not there is a where clause
		if(!empty($where)) {
			// Stop execution and throw an error if the where clause is not an array
			if(!is_array($where)) exit($this->errorMsg('where'));
			
			// Create empty arrays to hold portions of the where clause
			$conditions = $values = $vals = $placeholders = array();
			
			// Set the initial operator value
			$operator = '<>';
			
			// Loop through the where clause array
			foreach($where as $field=>$value) {
				// Check whether the value is an array
				if(is_array($value)) {
					// Loop through the values
					foreach($value as $val) {
						if(is_string($val)) {
							if($val === '<>' || $val === '!')
								$operator = '<>';
							elseif(strtoupper($val) === 'IN' || strtoupper($val) === 'NOT IN' || strtoupper($val) === 'LIKE')
								$operator = strtoupper($val);
						}
						
						if(strtoupper($val) === $operator) continue;
						
						$vals[] = $val;
						$placeholders[] = '?';
					}
					
					switch($operator) {
						case '<>': case 'LIKE':
							// Add a condition to the conditions array
							$conditions[] = $field.' '.$operator.' ?';
							break;
						case 'IN': case 'NOT IN':
							// Add a condition to the conditions array
							$conditions[] = $field.' '.$operator.' ('.implode(', ', $placeholders).')';
							break;
					}
					
					// Merge the two values arrays into one
					$values = array_merge($values, $vals);
				} else {
					// Add a condition to the conditions array
					$conditions[] = $field.' = ?';
					
					// Add the value to the values array
					$values[] = $value;
				}
			}
			
			// Merge the conditions array into a string
			$conditions = implode(' AND ', $conditions);
			
			// Add the where clause to the SQL statement
			$sql .= ' WHERE '.$conditions;
		}
		
		// Add the order by clause if it's been provided
		if(!empty($order_by)) $sql .= ' ORDER BY '.$order_by.' '.strtoupper($order);
		
		// Check whether or not there is a limit clause
		if(!empty($limit)) {
			// Merge the limit clause into a string if it's an array
			if(is_array($limit)) $limit = implode(', ', $limit);
			
			// Add the limit clause to the SQL statement
			$sql .= ' LIMIT '.$limit;
		}
		
		// Create an empty array to hold data from the database
		$db_data = array();
		
		try {
			// Prepare and execute the query
			$select_query = $this->conn->prepare($sql);
			isset($values) ? $select_query->execute($values) : $select_query->execute();
			
			// Check whether the query is a row count
			if(strpos(strtoupper($data), 'COUNT') !== false) {
				// Return the query data
                return $select_query->fetchColumn();
            } else {
     			while($row = $select_query->fetch(PDO::FETCH_ASSOC))
     				$db_data[] = $row;
				
				// Return the query data
                return $db_data;
            }
		} catch(PDOException $e) {
			// Log any errors
			logError($e);
		}
	}
	
	/**
	 * Select only a single row from the database and return it.
	 * @since 1.1.1[a]
	 *
	 * @access public
	 * @param string $table
	 * @param string|array $data (optional; default: '*')
	 * @param array $where (optional; default: array())
	 * @param string $order_by (optional; default: '')
	 * @param string $order (optional; default: 'ASC')
	 * @param string|array $limit (optional; default: '')
	 * @return array
	 */
	public function selectRow($table, $data = '*', $where = array(), $order_by = '', $order = 'ASC', $limit = '') {
		// Fetch the data from the database
		$db_data = $this->select($table, $data, $where, $order_by, $order, $limit);
		
		// Check whether the data is an array and is not empty
		if(is_array($db_data) && !empty($db_data)) {
			// Merge and return the data
			return array_merge(...$db_data);
		} else {
			// Return the data
			return $db_data;
		}
	}
	
	/**
	 * Select only a single field from the database and return it.
	 * @since 1.8.10[a]
	 *
	 * @access public
	 * @param string $table
	 * @param string $field
	 * @param array $where (optional; default: array())
	 * @param string $order_by (optional; default: '')
	 * @param string $order (optional; default: 'ASC')
	 * @param string|array $limit (optional; default: '')
	 * @return string
	 */
	public function selectField($table, $field, $where = array(), $order_by = '', $order = 'ASC', $limit = '') {
		// Stop execution and throw an error if no field is specified
		if(empty($field)) exit($this->errorMsg('field'));
		
		// Fetch the field data from the database
		$data = $this->selectRow($table, $field, $where, $order_by, $order, $limit);
		
		// Return the field data
		return implode('', $data);
	}
	
	/**
	 * Insert a row into the database.
	 * @since 1.1.0[a]
	 *
	 * @access public
	 * @param string $table
	 * @param array $data
	 * @return int
	 */
	public function insert($table, $data) {
		// Stop execution and throw an error if no table is specified
		if(empty($table)) exit($this->errorMsg('table'));
		
		// Stop execution and throw an error if no data is specified
		if(empty($data)) exit($this->errorMsg('data'));
		
		if(!is_array($data)) exit($this->errorMsg('data_arr'));
		
		$fields = $values = $placeholders = array();
		
		foreach($data as $field=>$value) {
			if(strtoupper($value) === 'NOW()') {
				$fields[] = $field;
				$placeholders[] = $value;
			} else {
				$fields[] = $field;
				$values[] = $value;
				$placeholders[] = '?';
			}
		}
		
		$fields = implode(', ', $fields);
		$placeholders = implode(', ', $placeholders);
		
		// Construct the SQL statement
		$sql = 'INSERT INTO `'.$table.'` ('.$fields.') VALUES ('.$placeholders.')';
		
		try {
			// Prepare and execute the query
			$insert_query = $this->conn->prepare($sql);
			$insert_query->execute($values);
			
			// Return the insert id for the last entry
			return $this->conn->lastInsertId();
		} catch(PDOException $e) {
			// Log any errors
			logError($e);
		}
	}
	
	/**
	 * Update an existing row in the database.
	 * @since 1.1.0[a]
	 *
	 * @access public
	 * @param string $table
	 * @param array $data
	 * @param array $where (optional; default: array())
	 * @return null
	 */
	public function update($table, $data, $where = array()) {
		// Stop execution and throw an error if no table is specified
		if(empty($table)) exit($this->errorMsg('table'));
		
		// Stop execution and throw an error if no data is specified
		if(empty($data)) exit($this->errorMsg('data'));
		
		if(!is_array($data)) exit($this->errorMsg('data_arr'));
		
		$fields = $values = array();
		
		foreach($data as $field=>$value) {
			if(strtoupper($value) === 'NOW()') {
				$fields[] = $field.' = '.$value;
			} else {
				$fields[] = $field.' = ?';
				$values[] = $value;
			}
		}
		
		$fields = implode(', ', $fields);
		
		// Construct the basic SQL statement
		$sql = 'UPDATE `'.$table.'` SET '.$fields;
		
		// Check whether or not there is a where clause
		if(!empty($where)) {
			// Stop execution and throw an error if the where clause is not an array
			if(!is_array($where)) exit($this->errorMsg('where'));
			
			// Create empty arrays to hold portions of the where clause
			$conditions = $vals = $placeholders = array();
			
			// Set the initial operator value
			$operator = 'IN';
			
			// Loop through the where clause array
			foreach($where as $field=>$value) {
				// Check whether the value is an array
				if(is_array($value)) {
					// Loop through the values
					foreach($value as $val) {
						if($val === 'IN' || $val === 'NOT IN') {
							$operator = $val;
						} else {
							$vals[] = $val;
							$placeholders[] = '?';
						}
					}
					
					// Add a condition to the conditions array
					$conditions[] = $field.' '.$operator.' ('.implode(', ', $placeholders).')';
					
					// Merge the two values arrays into one
					$values = array_merge($values, $vals);
				} else {
					// Add a condition to the conditions array
					$conditions[] = $field.' = ?';
					
					// Add the value to the values array
					$values[] = $value;
				}
			}
			
			// Merge the conditions array into a string
			$conditions = implode(' AND ', $conditions);
			
			// Add the where clause to the SQL statement
			$sql .= ' WHERE '.$conditions;
		}
		
		try {
			// Prepare and execute the query
			$update_query = $this->conn->prepare($sql);
			$update_query->execute($values);
		} catch(PDOException $e) {
			// Log any errors
			logError($e);
		}
	}
	
	/**
	 * Delete a row from the database.
	 * @since 1.0.3[a]
	 *
	 * @access public
	 * @param string $table
	 * @param array $where (optional; default: array())
	 * @return null
	 */
	public function delete($table, $where = array()) {
		// Stop execution and throw an error if no table is specified
		if(empty($table)) exit($this->errorMsg('table'));
		
		// Construct the basic SQL statement
		$sql = 'DELETE FROM `'.$table.'`';
		
		// Check whether or not there is a where clause
		if(!empty($where)) {
			// Stop execution and throw an error if the where clause is not an array
			if(!is_array($where)) exit($this->errorMsg('where'));
			
			// Create empty arrays to hold portions of the where clause
			$conditions = $values = $vals = $placeholders = array();
			
			// Set the initial operator value
			$operator = 'IN';
			
			// Loop through the where clause array
			foreach($where as $field=>$value) {
				// Check whether the value is an array
				if(is_array($value)) {
					// Loop through the values
					foreach($value as $val) {
						// Check whether the value is 'IN' or 'NOT IN'
						if($val === 'IN' || $val === 'NOT IN') {
							// Set the operator's new value
							$operator = $val;
						} else {
							// Add the value to the vals array
							$vals[] = $val;
							
							// Add a placeholder to the placeholders array
							$placeholders[] = '?';
						}
					}
					
					// Add a condition to the conditions array
					$conditions[] = $field.' '.$operator.' ('.implode(', ', $placeholders).')';
					
					// Merge the two values arrays into one
					$values = array_merge($values, $vals);
				} else {
					// Add a condition to the conditions array
					$conditions[] = $field.' = ?';
					
					// Add the value to the values array
					$values[] = $value;
				}
			}
			
			// Merge the conditions array into a string
			$conditions = implode(' AND ', $conditions);
			
			// Add the where clause to the SQL statement
			$sql .= ' WHERE '.$conditions;
		}
		
		try {
			// Prepare and execute the query
			$delete_query = $this->conn->prepare($sql);
			isset($values) ? $delete_query->execute($values) : $delete_query->execute();
		} catch(PDOException $e) {
			// Log any errors
			logError($e);
		}
	}
	
	/**
	 * Run a generic SQL query. Does not return data.
	 * @since 1.3.0[a]
	 *
	 * @access public
	 * @param string $sql
	 * @return null
	 */
	public function doQuery($sql) {
		try {
			// Prepare and execute the query
			$query = $this->conn->prepare($sql);
			$query->execute();
		} catch(PDOException $e) {
			// Log any errors
			logError($e);
		}
	}
	
	/**
	 * Show tables in the database.
	 * @since 1.3.3[a]
	 *
	 * @access public
	 * @return array
	 */
	public function showTables() {
		// Create an empty array to hold the table data
		$data = array();
		
		try {
			// Prepare and execute the query
			$query = $this->conn->prepare("SHOW TABLES");
			$query->execute();
			
			// Loop through the query data and assign it to the array
			while($row = $query->fetch()) $data[] = $row;
			
			// Return the data
			return $data;
		} catch(PDOException $e) {
			// Log any errors
			logError($e);
		}
	}
	
	/**
	 * Return an error message for poorly executed queries.
	 * @since 1.0.3[a]
	 *
	 * @access private
	 * @param string $type
	 * @return null
	 */
	private function errorMsg($type) {
		$error = 'Query Error: ';
		
		switch($type) {
			case 'table':
				$error .= 'a table must be specified!';
				break;
			case 'field':
				$error .= 'a field must be specified!';
				break;
			case 'where':
				$error .= 'where clause parameters must be in an array.';
				break;
			case 'data':
				$error .= 'missing required data!';
				break;
			case 'data_arr':
				$error .= 'data must be presented as an associative array.';
				break;
			default:
				$error .= $type;
		}
		
		// Display the appropriate error message
		echo $error;
	}
}