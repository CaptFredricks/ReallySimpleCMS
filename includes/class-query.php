<?php
/**
 * Core class used to implement the Query object.
 * @since 1.0.0[a]
 *
 * This class is the heart of the CMS, providing the primary interface with the database.
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
	 * The database connection status.
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
	 */
	public function __construct() {
		try {
			// Create a PDO object and plug in the database constant values
			$this->conn = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' .
				DB_CHAR,
				DB_USER,
				DB_PASS
			);
			
			// Turn off emulation of prepared statements
			$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			
			// Turn on error reporting
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			// Check whether any of the database constants are empty and update the connection status as necessary
			if(empty(DB_HOST) || empty(DB_NAME) || empty(DB_CHAR) || empty(DB_USER))
				$this->conn_status = false;
			else
				$this->conn_status = true;
		} catch(PDOException $e) {
			logError($e);
			
			$this->conn_status = false;
		}
	}
	
	/**
	 * Select one or more rows from the database and return them.
	 * @since 1.1.0[a]
	 *
	 * @access public
	 * @param string $table -- The table name.
	 * @param string|array $cols (optional) -- The column(s) to query.
	 * @param array $where (optional) -- The where clause.
	 * @param string $order_by (optional) -- The column to order results by.
	 * @param string $order (optional) -- The sort order (ASC|DESC).
	 * @param string|array $limit (optional) -- Limit the results.
	 * @return int|array
	 */
	public function select($table, $cols = '*', $where = array(), $order_by = '', $order = 'ASC', $limit = ''
		): int|array {
			
		// Stop execution and throw an error if no table is specified
		if(empty($table)) exit($this->errorMsg('table'));
		
		if(is_array($cols)) {
			// DISTINCT clause
			if(in_array('DISTINCT', $cols, true)) {
				$distinct = true;
				
				// Remove 'DISTINCT' from the array
				array_splice($cols, array_search('DISTINCT', $cols), 1);
			}
			
			$cols = implode(', ', $cols);
		}
		
		$sql = 'SELECT ' . (isset($distinct) ? 'DISTINCT ' : '') . $cols . ' FROM `' . $table . '`';
		
		// WHERE clause
		if(!empty($where)) {
			// Stop execution and throw an error if the WHERE clause is not an array
			if(!is_array($where)) exit($this->errorMsg('where'));
			
			$conditions = $values = $vals = $placeholders = array();
			
			// Accepted operators
			$operators = array(
				'=', '>', '<', '>=', '<=', '<>',
				'LIKE', 'IN', 'NOT IN',
				'IS NULL', 'IS NOT NULL'
			);
			
			// Default operator
			$operator = '<>';
			
			// Default logic
			$logic = 'AND';
			
			foreach($where as $field => $value) {
				if($field === 'logic') {
					$logic = strtoupper($value);
					continue;
				}
				
				if(is_array($value)) {
					foreach($value as $val) {
						if(is_string($val)) {
							// Check whether the value is an operator
							if(in_array(strtoupper($val), $operators, true))
								$operator = strtoupper($val);
						}
						
						// Skip over the operator value
						if(strtoupper($val) === $operator) continue;
						
						$vals[] = $val;
						$placeholders[] = '?';
					}
					
					switch($operator) {
						case 'IN': case 'NOT IN':
							$conditions[] = $field . ' ' . $operator . ' (' . implode(', ', $placeholders) . ')';
							break;
						case 'IS NULL': case 'IS NOT NULL':
							$conditions[] = $field . ' ' . $operator;
							break;
						default:
							$conditions[] = $field . ' ' . $operator . ' ?';
					}
					
					// Merge the two values arrays into one
					$values = array_merge($values, $vals);
					$vals = array();
				} else {
					$conditions[] = $field . ' = ?';
					$values[] = $value;
				}
			}
			
			$conditions = implode(' ' . $logic . ' ', $conditions);
			$sql .= ' WHERE ' . $conditions;
		}
		
		// ORDER BY clause
		if(!empty($order_by)) $sql .= ' ORDER BY ' . $order_by . ' ' . strtoupper($order);
		
		// LIMIT clause
		if(!empty($limit)) {
			if(is_array($limit)) $limit = implode(', ', $limit);
			
			$sql .= ' LIMIT ' . $limit;
		}
		
		try {
			$select_query = $this->conn->prepare($sql);
			isset($values) ? $select_query->execute($values) : $select_query->execute();
			
			if(str_starts_with(strtoupper($cols), 'COUNT(')) {
                return $select_query->fetchColumn();
            } else {
				$data = array();
				
     			while($row = $select_query->fetch(PDO::FETCH_ASSOC))
     				$data[] = $row;
				
                return $data;
            }
		} catch(PDOException $e) {
			logError($e);
			return -1;
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
	 * @return int|array
	 */
	public function selectRow($table, $data = '*', $where = array(), $order_by = '', $order = 'ASC', $limit = ''
		): int|array {
			
		$db_data = $this->select($table, $data, $where, $order_by, $order, $limit);
		
		if(is_array($db_data) && !empty($db_data))
			return array_merge(...$db_data);
		else
			return $db_data;
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
	public function selectField($table, $field, $where = array(), $order_by = '', $order = 'ASC', $limit = ''): string {
		// Stop execution and throw an error if no field is specified
		if(empty($field)) exit($this->errorMsg('field'));
		
		$data = $this->selectRow($table, $field, $where, $order_by, $order, $limit);
		
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
	public function insert($table, $data): int {
		// Stop execution and throw an error if no table is specified
		if(empty($table)) exit($this->errorMsg('table'));
		
		// Stop execution and throw an error if no data is specified
		if(empty($data)) exit($this->errorMsg('data'));
		
		// Stop execution and throw an error if the data is not provided as an array
		if(!is_array($data)) exit($this->errorMsg('data_arr'));
		
		$fields = $values = $placeholders = array();
		
		foreach($data as $field => $value) {
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
		$sql = 'INSERT INTO `' . $table . '` (' . $fields . ') VALUES (' . $placeholders . ')';
		
		try {
			$insert_query = $this->conn->prepare($sql);
			$insert_query->execute($values);
			
			return $this->conn->lastInsertId();
		} catch(PDOException $e) {
			logError($e);
			return -1;
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
	 */
	public function update($table, $data, $where = array()): void {
		// Stop execution and throw an error if no table is specified
		if(empty($table)) exit($this->errorMsg('table'));
		
		// Stop execution and throw an error if no data is specified
		if(empty($data)) exit($this->errorMsg('data'));
		
		// Stop execution and throw an error if the data is not provided as an array
		if(!is_array($data)) exit($this->errorMsg('data_arr'));
		
		$fields = $values = array();
		
		foreach($data as $field => $value) {
			if(strtoupper($value) === 'NOW()') {
				$fields[] = $field . ' = ' . $value;
			} else {
				$fields[] = $field . ' = ?';
				$values[] = $value;
			}
		}
		
		$fields = implode(', ', $fields);
		$sql = 'UPDATE `' . $table . '` SET ' . $fields;
		
		// WHERE clause
		if(!empty($where)) {
			// Stop execution and throw an error if the where clause is not an array
			if(!is_array($where)) exit($this->errorMsg('where'));
			
			$conditions = $vals = $placeholders = array();
			
			// Default operator
			$operator = 'IN';
			
			// Default logic
			$logic = 'AND';
			
			foreach($where as $field => $value) {
				if($field === 'logic') {
					$logic = strtoupper($value);
					continue;
				}
				
				if(is_array($value)) {
					foreach($value as $val) {
						if($val === 'IN' || $val === 'NOT IN') {
							$operator = $val;
						} else {
							$vals[] = $val;
							$placeholders[] = '?';
						}
					}
					
					$conditions[] = $field . ' ' . $operator . ' (' . implode(', ', $placeholders) . ')';
					
					// Merge the two values arrays into one
					$values = array_merge($values, $vals);
				} else {
					$conditions[] = $field . ' = ?';
					$values[] = $value;
				}
			}
			
			$conditions = implode(' ' . $logic . ' ', $conditions);
			$sql .= ' WHERE ' . $conditions;
		}
		
		try {
			$update_query = $this->conn->prepare($sql);
			$update_query->execute($values);
		} catch(PDOException $e) {
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
	 */
	public function delete($table, $where = array()): void {
		// Stop execution and throw an error if no table is specified
		if(empty($table)) exit($this->errorMsg('table'));
		
		$sql = 'DELETE FROM `' . $table . '`';
		
		// WHERE clause
		if(!empty($where)) {
			// Stop execution and throw an error if the where clause is not an array
			if(!is_array($where)) exit($this->errorMsg('where'));
			
			$conditions = $values = $vals = $placeholders = array();
			
			// Default operator
			$operator = 'IN';
			
			// Default logic
			$logic = 'AND';
			
			foreach($where as $field => $value) {
				if($field === 'logic') {
					$logic = strtoupper($value);
					continue;
				}
				
				if(is_array($value)) {
					foreach($value as $val) {
						if($val === 'IN' || $val === 'NOT IN') {
							$operator = $val;
						} else {
							$vals[] = $val;
							$placeholders[] = '?';
						}
					}
					
					$conditions[] = $field . ' ' . $operator . ' (' . implode(', ', $placeholders) . ')';
					
					// Merge the two values arrays into one
					$values = array_merge($values, $vals);
				} else {
					$conditions[] = $field . ' = ?';
					$values[] = $value;
				}
			}
			
			$conditions = implode(' ' . $logic . ' ', $conditions);
			$sql .= ' WHERE ' . $conditions;
		}
		
		try {
			$delete_query = $this->conn->prepare($sql);
			isset($values) ? $delete_query->execute($values) : $delete_query->execute();
		} catch(PDOException $e) {
			logError($e);
		}
	}
	
	/**
	 * Run a generic SQL query. Does not return data.
	 * @since 1.3.0[a]
	 *
	 * @access public
	 * @param string $sql
	 */
	public function doQuery($sql): void {
		try {
			$query = $this->conn->prepare($sql);
			$query->execute();
		} catch(PDOException $e) {
			logError($e);
		}
	}
	
	/**
	 * Show tables in the database.
	 * @since 1.3.3[a]
	 *
	 * @access public
	 * @param string $table (optional; default: '')
	 * @return array
	 */
	public function showTables($table = ''): array {
		$data = array();
		$sql = 'SHOW TABLES';
		
		if(!empty($table)) $sql .= ' LIKE \'' . $table . '\'';
		
		try {
			$query = $this->conn->prepare($sql);
			$query->execute();
			
			while($row = $query->fetch()) $data[] = $row;
			
			return $data;
		} catch(PDOException $e) {
			logError($e);
		}
	}
	
	/**
	 * Show indexes in a table.
	 * @since 1.2.1[b]
	 *
	 * @access public
	 * @param string $table
	 * @return array
	 */
	public function showIndexes($table): array {
		// Stop execution and throw an error if no table is specified
		if(empty($table)) exit($this->errorMsg('table'));
		
		$sql = 'SHOW INDEXES FROM `' . $table . '`;';
		
		try {
			$query = $this->conn->prepare($sql);
			$query->execute();
			
			while($row = $query->fetch()) $data[] = $row;
			
			return $data;
		} catch(PDOException $e) {
			logError($e);
		}
	}
	
	/**
	 * Check whether a table already exists in the database.
	 * @since 1.0.8[b]
	 *
	 * @access public
	 * @param string $table
	 * @return bool
	 */
	public function tableExists($table): bool {
		return !empty($this->showTables($table));
	}
	
	/**
	 * Check whether a column exists in a database table.
	 * @since 1.3.5[b]
	 *
	 * @access public
	 * @param string $table
	 * @param string $column
	 * @return bool
	 */
	public function columnExists($table, $column): bool {
		// Stop execution and throw an error if no table or column is specified
		if(empty($table)) exit($this->errorMsg('table'));
		if(empty($column)) exit($this->errorMsg('column'));
		
		$sql = 'SHOW COLUMNS FROM `' . $table . '` LIKE \'' . $column . '\';';
		
		try {
			$query = $this->conn->prepare($sql);
			$query->execute();
			
			return !empty($query->fetch());
		} catch(PDOException $e) {
			logError($e);
			return false;
		}
	}
	
	/**
	 * Drop a table from the database.
	 * @since 1.2.0[b]
	 *
	 * @access public
	 * @param string $table
	 */
	public function dropTable($table): void {
		// Stop execution and throw an error if no table is specified
		if(empty($table)) exit($this->errorMsg('table'));
		
		$this->doQuery('DROP TABLE `' . $table . '`;');
	}
	
	/**
	 * Drop multiple tables from the database.
	 * @since 1.2.0[b]
	 *
	 * @access public
	 * @param array $tables
	 */
	public function dropTables($tables): void {
		// Stop execution and throw an error if no tables are specified
		if(empty($tables)) exit($this->errorMsg('table'));
		
		if(!is_array($tables)) $tables = (array)$tables;
		
		$sql = 'DROP TABLE ';
		
		for($i = 0; $i < count($tables); $i++)
			$sql .= '`' . $tables[$i] . '`' . ($i < count($tables) - 1 ? ', ' : ';');
		
		$this->doQuery($sql);
	}
	
	/**
	 * Return an error message for poorly executed queries.
	 * @since 1.0.3[a]
	 *
	 * @access private
	 * @param string $type
	 */
	private function errorMsg($type): void {
		$error = 'Query Error: ';
		
		switch($type) {
			case 'table':
				$error .= 'A table or tables must be specified!';
				break;
			case 'column': case 'field':
				$error .= 'A column or field must be specified!';
				break;
			case 'where':
				$error .= 'Where clause parameters must be in an array.';
				break;
			case 'data':
				$error .= 'Missing required data!';
				break;
			case 'data_arr':
				$error .= 'Data must be presented as an associative array.';
				break;
			default:
				$error .= 'An error of type `' . $type . '` occurred.';
		}
		
		echo $error;
	}
}