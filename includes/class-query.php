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
	private $conn = null;
	
	/**
	 * The database connection status.
	 * @since 1.3.0[a]
	 *
	 * @access public
	 * @var bool
	 */
	public $conn_status = false;
	
	/**
	 * The database character set.
	 * @since 1.3.10[b]
	 *
	 * @access public
	 * @var string
	 */
	public $charset;
	
	/**
	 * The database collation.
	 * @since 1.3.10[b]
	 *
	 * @access public
	 * @var string
	 */
	public $collate;
	
	/**
	 * The database server version.
	 * @since 1.3.10[b]
	 *
	 * @access public
	 * @var string
	 */
	public $server_version;
	
	/**
	 * The database client version.
	 * @since 1.3.10[b]
	 *
	 * @access public
	 * @var string
	 */
	public $client_version;
	
	/**
	 * Class constructor. Initializes the database connection.
	 * @since 1.0.0[a]
	 *
	 * @access public
	 */
	public function __construct() {
		try {
			// Create a PDO object and plug in the database constant values
			$this->conn = new PDO('mysql' .
				':dbname=' . DB_NAME .
				';host=' . DB_HOST,
				DB_USER,
				DB_PASS
			);
			
			// Turn off emulation of prepared statements
			$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			
			// Turn on error reporting
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			// Fetch the database software info
			$this->server_version = $this->getAttr('SERVER_VERSION');
			$this->client_version = $this->getAttr('CLIENT_VERSION');
			
			if(!$this->conn_status) $this->initCharset();
			
			$this->conn_status = true;
			$this->setCharset();
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
	public function select(
		string $table,
		string|array $cols = '*',
		array $where = array(),
		string $order_by = '',
		string $order = 'ASC',
		string|array $limit = ''
	): int|array {
		if(empty($table)) exit($this->errorMsg('table'));
		
		if(is_array($cols)) {
			// DISTINCT clause
			if(in_array('DISTINCT', $cols, true)) {
				$distinct = true;
				
				// Remove `DISTINCT` from the array
				array_splice($cols, array_search('DISTINCT', $cols), 1);
			}
			
			$cols = implode(', ', $cols);
		}
		
		$sql = 'SELECT ' . (isset($distinct) ? 'DISTINCT ' : '') . $cols . ' FROM `' . $table . '`';
		
		// WHERE clause
		if(!empty($where)) {
			$conditions = $values = $vals = $placeholders = array();
			
			// Accepted operators
			$operators = array(
				'=', '>', '<', '>=', '<=', '<>',
				'LIKE', 'IN', 'NOT IN',
				'BETWEEN', 'NOT BETWEEN',
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
						case 'BETWEEN': case 'NOT BETWEEN':
							$conditions[] = $field . ' ' . $operator . ' ' .
								implode(' AND ', $placeholders);
							break;
						case 'IN': case 'NOT IN':
							$conditions[] = $field . ' ' . $operator . ' (' .
								implode(', ', $placeholders) . ')';
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
			
			$sql .= ' WHERE ' . implode(' ' . $logic . ' ', $conditions);
		}
		
		// ORDER BY clause
		if(!empty($order_by)) {
			$order = strtoupper($order);
			
			if($order !== 'ASC' && $order !== 'DESC') $order = 'ASC';
			
			$sql .= ' ORDER BY ' . $order_by . ' ' . $order;
		}
		
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
	 * @param string $table -- The table name.
	 * @param string|array $cols (optional) -- The column(s) to query.
	 * @param array $where (optional) -- The where clause.
	 * @param string $order_by (optional) -- The column to order results by.
	 * @param string $order (optional) -- The sort order (ASC|DESC).
	 * @param string|array $limit (optional) -- Limit the results.
	 * @return int|array
	 */
	public function selectRow(
		string $table,
		string|array $cols = '*',
		array $where = array(),
		string $order_by = '',
		string $order = 'ASC',
		string|array $limit = ''
	): int|array {
		$data = $this->select($table, $cols, $where, $order_by, $order, $limit);
		
		if(is_array($data) && !empty($data))
			return array_merge(...$data);
		else
			return $data;
	}
	
	/**
	 * Select only a single field from the database and return it.
	 * @since 1.8.10[a]
	 *
	 * @access public
	 * @param string $table -- The table name.
	 * @param string $col -- The column to query.
	 * @param array $where (optional) -- The where clause.
	 * @param string $order_by (optional) -- The column to order results by.
	 * @param string $order (optional) -- The sort order (ASC|DESC).
	 * @param string|array $limit (optional) -- Limit the results.
	 * @return string
	 */
	public function selectField(
		string $table,
		string $col,
		array $where = array(),
		string $order_by = '',
		string $order = 'ASC',
		string|array $limit = ''
	): string {
		if(empty($col)) exit($this->errorMsg('field'));
		
		$data = $this->selectRow($table, $col, $where, $order_by, $order, $limit);
		
		return implode('', $data);
	}
	
	/**
	 * Insert a row into the database.
	 * @since 1.1.0[a]
	 *
	 * @access public
	 * @param string $table -- The table name.
	 * @param array $data -- The data to insert.
	 * @return int
	 */
	public function insert(string $table, array $data): int {
		if(empty($table)) exit($this->errorMsg('table'));
		if(empty($data)) exit($this->errorMsg('data'));
		
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
	 * @param string $table -- The table name.
	 * @param array $data -- The data to update.
	 * @param array $where (optional) -- The where clause.
	 */
	public function update(string $table, array $data, array $where = array()): void {
		if(empty($table)) exit($this->errorMsg('table'));
		if(empty($data)) exit($this->errorMsg('data'));
		
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
					
					$conditions[] = $field . ' ' . $operator . ' (' .
						implode(', ', $placeholders) . ')';
					
					// Merge the two values arrays into one
					$values = array_merge($values, $vals);
				} else {
					$conditions[] = $field . ' = ?';
					$values[] = $value;
				}
			}
			
			$sql .= ' WHERE ' . implode(' ' . $logic . ' ', $conditions);
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
	 * @param string $table -- The table name.
	 * @param array $where (optional) -- The where clause.
	 */
	public function delete(string $table, array $where = array()): void {
		if(empty($table)) exit($this->errorMsg('table'));
		
		$sql = 'DELETE FROM `' . $table . '`';
		
		// WHERE clause
		if(!empty($where)) {
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
					
					$conditions[] = $field . ' ' . $operator . ' (' .
						implode(', ', $placeholders) . ')';
					
					// Merge the two values arrays into one
					$values = array_merge($values, $vals);
				} else {
					$conditions[] = $field . ' = ?';
					$values[] = $value;
				}
			}
			
			$sql .= ' WHERE ' . implode(' ' . $logic . ' ', $conditions);
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
	 * @param string $sql -- The SQL statement to execute.
	 */
	public function doQuery(string $sql): void {
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
	 * @param string $table (optional) -- The table name.
	 * @return int|array
	 */
	public function showTables(string $table = ''): int|array {
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
			return -1;
		}
	}
	
	/**
	 * Show indexes in a table.
	 * @since 1.2.1[b]
	 *
	 * @access public
	 * @param string $table -- The table name.
	 * @return int|array
	 */
	public function showIndexes(string $table): int|array {
		if(empty($table)) exit($this->errorMsg('table'));
		
		$sql = 'SHOW INDEXES FROM `' . $table . '`;';
		
		try {
			$query = $this->conn->prepare($sql);
			$query->execute();
			
			while($row = $query->fetch()) $data[] = $row;
			
			return $data;
		} catch(PDOException $e) {
			logError($e);
			return -1;
		}
	}
	
	/**
	 * Check whether a table already exists in the database.
	 * @since 1.0.8[b]
	 *
	 * @access public
	 * @param string $table -- The table name.
	 * @return bool
	 */
	public function tableExists(string $table): bool {
		return !empty($this->showTables($table));
	}
	
	/**
	 * Check whether a column exists in a database table.
	 * @since 1.3.5[b]
	 *
	 * @access public
	 * @param string $table -- The table name.
	 * @param string $col -- The column to query.
	 * @return int|bool
	 */
	public function columnExists(string $table, string $col): int|bool {
		if(empty($table)) exit($this->errorMsg('table'));
		if(empty($col)) exit($this->errorMsg('column'));
		
		$sql = 'SHOW COLUMNS FROM `' . $table . '` LIKE \'' . $col . '\';';
		
		try {
			$query = $this->conn->prepare($sql);
			$query->execute();
			
			return !empty($query->fetch());
		} catch(PDOException $e) {
			logError($e);
			return -1;
		}
	}
	
	/**
	 * Drop a table from the database.
	 * @since 1.2.0[b]
	 *
	 * @access public
	 * @param string $table -- The table name.
	 */
	public function dropTable(string $table): void {
		if(empty($table)) exit($this->errorMsg('table'));
		
		$this->doQuery('DROP TABLE `' . $table . '`;');
	}
	
	/**
	 * Drop multiple tables from the database.
	 * @since 1.2.0[b]
	 *
	 * @access public
	 * @param array $tables -- The table names.
	 */
	public function dropTables(array $tables): void {
		if(empty($tables)) exit($this->errorMsg('table'));
		
		if(!is_array($tables)) $tables = (array)$tables;
		
		$sql = 'DROP TABLE ';
		
		for($i = 0; $i < count($tables); $i++)
			$sql .= '`' . $tables[$i] . '`' . ($i < count($tables) - 1 ? ', ' : ';');
		
		$this->doQuery($sql);
	}
	
	/**
	 * Fetch a PDO attribute.
	 * @since 1.3.10[b]
	 *
	 * @access private
	 * @param string $attr -- The attribute's name.
	 * @return string
	 */
	private function getAttr(string $attr): string {
		return $this->conn->getAttribute(constant('PDO::ATTR_' . $attr));
	}
	
	/**
	 * Initialize the charset and collation.
	 * @since 1.3.10[b]
	 *
	 * @access private
	 */
	private function initCharset(): void {
		$charset = $collate = '';
		
		if(defined('DB_CHARSET')) $charset = DB_CHARSET;
		if(defined('DB_COLLATE')) $collate = DB_COLLATE;
		
		if($charset === 'utf8' && $this->hasCap('utf8mb4'))
			$charset = 'utf8mb4';
		
		if($charset === 'utf8mb4' && !$this->hasCap('utf8mb4')) {
			$charset = 'utf8';
			$collate = str_replace('utf8mb4_', 'utf8_', $collate);
		}
		
		if($charset === 'utf8mb4') {
			if(!$collate || $collate === 'utf8_general_ci')
				$collate = 'utf8mb4_unicode_ci';
			else
				$collate = str_replace('utf8_', 'utf8mb4_', $collate);
		}
		
		if($collate === 'utf8mb4_unicode_ci' && $this->hasCap('utf8mb4_520'))
			$collate = 'utf8mb4_unicode_520_ci';
		
		$this->charset = $charset;
		$this->collate = $collate;
	}
	
	/**
	 * Set the charset and collation.
	 * @since 1.3.10[b]
	 *
	 * @access private
	 * @param string|null $charset (optional) -- The character set.
	 * @param string|null $collate (optional) -- The collation.
	 */
	private function setCharset(?string $charset = null, ?string $collate = null): void {
		if(!isset($charset)) $charset = $this->charset;
		if(!isset($collate)) $collate = $this->collate;
		
		if($this->hasCap('collation') && !empty($charset)) {
			$change_charset = $change_collate = false;
			
			if(file_exists(DB_CONFIG)) {
				$config_file = file(DB_CONFIG);
				
				foreach($config_file as $line_num => $line) {
					// Skip over unmatched lines
					if(!preg_match('/^define\(\s*\'([A-Z_]+)\',\s+\'([a-z0-9_]*)\'/', $line, $match))
						continue;
					
					$constant = $match[1];
					$value = $match[2];
					
					switch($constant) {
						case 'DB_CHARSET':
							if($value !== $charset) {
								$change_charset = true;
								
								$config_file[$line_num] = "define('" . $constant . "', '" .
									$charset . "');" . chr(10);
							}
							break;
						case 'DB_COLLATE':
							if($value !== $collate) {
								$change_collate = true;
								
								$config_file[$line_num] = "define('" . $constant . "', '" .
									$collate . "');" . chr(10);
							}
							break;
					}
				}
				
				unset($line);
				
				if($change_charset || $change_collate) {
					// Open the file stream
					$handle = fopen(DB_CONFIG, 'w');
					
					// Write to the file
					if($handle !== false) {
						foreach($config_file as $line) fwrite($handle, $line);
						
						fclose($handle);
					}
					
					$sql = 'SET NAMES ' . $charset;
					
					if(!empty($collate)) $sql .= ' COLLATE ' . $collate . ';';
					
					$query = $this->conn->prepare($sql);
					$query->execute();
				}
			}
		}
	}
	
	/**
	 * Check whether the database supports a particular capability.
	 * @since 1.3.10[b]
	 *
	 * @access public
	 * @param string $cap -- The capability.
	 * @return bool
	 */
	public function hasCap(string $cap): bool {
		$server_version = preg_replace('/[^0-9.].*/', '', $this->server_version);
		
		switch(strtolower($cap)) {
			case 'collation':
				return version_compare($server_version, '4.1', '>=');
			case 'utf8mb4':
				if(version_compare($server_version, '5.5.3', '<'))
					return false;
				
				$client_version = $this->client_version;
				
				if(str_contains($client_version, 'mysqlnd')) {
					$client_version = preg_replace('/^\D+([\d.]+).*/', '$1', $client_version);
					return version_compare($client_version, '5.0.9', '>=');
				} else {
					return version_compare($client_version, '5.5.3', '>=');
				}
			case 'utf8mb4_520':
				return version_compare($server_version, '5.6', '>=');
		}
		
		return false;
	}
	
	/**
	 * Return an error message for poorly executed queries.
	 * @since 1.0.3[a]
	 *
	 * @access private
	 * @param string $type -- The type of error.
	 */
	private function errorMsg(string $type): void {
		$error = 'Query Error: ';
		
		switch($type) {
			case 'table':
				$error .= 'A table or tables must be specified!';
				break;
			case 'column': case 'field':
				$error .= 'A column or field must be specified!';
				break;
			case 'data':
				$error .= 'Missing required data!';
				break;
			default:
				$error .= 'An error of type `' . $type . '` occurred.';
		}
		
		echo $error;
	}
}