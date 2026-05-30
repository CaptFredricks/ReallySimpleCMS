<?php
/**
 * Core class used to implement the Query object.
 * This class is the heart of the system, providing the primary interface with the database.
 * @since 1.0.0-alpha
 *
 * @package ReallySimpleCMS
 * @subpackage Engine
 *
 * ## OBJECT VAR ##
 * - $rs_query
 *
 * ## VARIABLES [6] ##
 * - private null|object $conn
 * - public bool $conn_status
 * - public string $charset
 * - public string $collate
 * - public string $server_version
 * - public string $client_version
 *
 * ## METHODS [19] ##
 * - public __construct()
 * { BASIC QUERIES [7] }
 * - public select(string|array $table, string|array $cols, array $where, array $args): int|array
 * - public selectRow(string|array $table, string|array $cols, array $where, array $args): int|array
 * - public selectField(string|array $table, string $col, array $where, array $args): string
 * - public insert(string|array $table, array $data, array $args): int
 * - public update(string|array $table, array $data, array $where, array $args): void
 * - public delete(string|array $table, array $where, array $args): void
 * - public doQuery(string $sql): void
 * { ADVANCED QUERIES & ACTIONS [7] }
 * - public showTables(string $table): int|array
 * - public showIndexes(string $table): int|array
 * - public tableExists(string $table): bool
 * - public columnExists(string $table, string $col): int|bool
 * - public createTable(string $table, array $data): void
 * - public dropTable(string $table): void
 * - public dropTables(array $tables): void
 * { MISCELLANEOUS [4] }
 * - private getAttr(string $attr): string
 * - private initCharset(): void
 * - private setCharset(?string $charset, ?string $collate): void
 * - public hasCap(string $cap): bool
 */
namespace Engine;

class Query {
	/**
	 * The database connection.
	 * @since 1.0.0-alpha
	 *
	 * @access private
	 * @var null|object
	 */
	private $conn = null;
	
	/**
	 * The database connection status.
	 * @since 1.3.0-alpha
	 *
	 * @access public
	 * @var bool
	 */
	public $conn_status = false;
	
	/**
	 * The database character set.
	 * @since 1.3.10-beta
	 *
	 * @access public
	 * @var string
	 */
	public $charset;
	
	/**
	 * The database collation.
	 * @since 1.3.10-beta
	 *
	 * @access public
	 * @var string
	 */
	public $collate;
	
	/**
	 * The database server version.
	 * @since 1.3.10-beta
	 *
	 * @access public
	 * @var string
	 */
	public $server_version;
	
	/**
	 * The database client version.
	 * @since 1.3.10-beta
	 *
	 * @access public
	 * @var string
	 */
	public $client_version;
	
	/**
	 * Class constructor. Initializes the database connection.
	 * @since 1.0.0-alpha
	 *
	 * @access public
	 */
	public function __construct() {
		global $rs_error;
		
		try {
			// Initialize database connection using PDO (PHP Data Objects).
			// @see https://www.php.net/manual/en/book.pdo.php
			$drivers = \PDO::getAvailableDrivers();
			$supported = 'mysql';
			$db_driver = null;
			
			// Abort if driver is not currently supported
			if(!in_array($supported, $drivers, true)) {
				exit('<p><code>ERROR: Database driver is unsupported! Please contact your web host.' .
					'<br><br>Supported driver(s): `' . $supported . '`</code></p>');
			}
			
			$db_driver = $supported;
			
			$this->conn = new \PDO($db_driver .
				':host=' . DB_HOST .
				';dbname=' . DB_NAME,
				DB_USER,
				DB_PASS
			);
			
			// Turn off emulation of prepared statements
			$this->conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
			
			// Turn on error reporting
			$this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			
			// Fetch the database software info
			$this->server_version = $this->getAttr('SERVER_VERSION');
			$this->client_version = $this->getAttr('CLIENT_VERSION');
			
			if(!$this->conn_status) $this->initCharset();
			
			$this->conn_status = true;
			$this->setCharset();
		} catch(\PDOException $e) {
			$this->conn_status = false;
			$rs_error->logError($e);
			
			if(isDebugMode())
				$rs_error->triggerError();
		}
	}
	
	/*------------------------------------*\
		BASIC QUERIES
	\*------------------------------------*/
	
	/**
	 * Select one or more rows from the database and return them.
	 * @since 1.1.0-alpha
	 *
	 * @access public
	 * @param string|array $table -- The table name and optionally, table prefix.
	 * @param string|array $cols (optional) -- The column(s) to query.
	 * @param array $where (optional) -- The where clause.
	 * @param array $args (optional) -- Additional args (e.g., `order_by`, `order`, `limit`).
	 * @return int|array
	 */
	public function select(
		string|array $table, string|array $cols = '*', array $where = array(), array $args = array()
	): int|array {
		global $rs_error;
		
		$debug = false;
		
		if(isset($args['debug']) && $args['debug']) $debug = true;
		
		// Check if the table is prefixed
		if(is_array($table)) {
			$px = array_pop($table);
			$table = implode('', $table);
		}
		
		if(is_array($cols)) {
			// DISTINCT CLAUSE
			if(in_array('DISTINCT', $cols, true)) {
				$distinct = true;
				
				// Remove `DISTINCT` from the array
				array_splice($cols, array_search('DISTINCT', $cols), 1);
			}
			
			// Prefix cols
			if(isset($px)) {
				$px_cols = array();
				
				foreach($cols as $col)
					$px_cols[] = $px . $col;
			}
			
			$cols = implode(', ', ($px_cols ?? $cols));
		} else {
			// Prefix col
			if(isset($px)) {
				if(str_starts_with($cols, 'COUNT('))
					$cols = preg_replace('/(COUNT\()(\w+\))/', '$1' . $px . '$2', $cols);
				elseif($cols !== '*')
					$cols = $px . $cols;
			}
		}
		
		// SELECT FROM CLAUSE
		$sql = 'SELECT ' . (isset($distinct) ? 'DISTINCT ' : '') . $cols . ' FROM `' . $table . '`';
		
		// WHERE CLAUSE
		if(!empty($where)) {
			$conditions = $values = $vals = $placeholders = array();
			
			// Accepted operators
			$operators = array(
				'=', '>', '<', '>=', '<=', '<>',
				'LIKE', 'IN', 'NOT IN',
				'BETWEEN', 'NOT BETWEEN',
				'IS NULL', 'IS NOT NULL'
			);
			
			// Defaults
			list($operator, $logic) = array('<>', 'AND');
			
			foreach($where as $field => $value) {
				if($field === 'logic') {
					$logic = strtoupper($value);
					continue;
				}
				
				// Prefix fields
				if(isset($px))
					$field = $px . $field;
				
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
					
					$conditions[] = match($operator) {
						'IN', 'NOT IN' => $field . ' ' . $operator . ' (' . implode(', ', $placeholders) . ')',
						'BETWEEN', 'NOT BETWEEN' => $field . ' ' . $operator . ' ' . implode(' AND ', $placeholders),
						'IS NULL', 'IS NOT NULL' => $field . ' ' . $operator,
						default => $field . ' ' . $operator . ' ?'
					};
					
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
		
		// ADDITIONAL ARGS
		$defaults = array(
			'order_by' => '', ## type: string
			'order' => 'ASC', ## type: string
			'limit' => '' ## type: string|array
		);
		
		$args = array_merge($defaults, $args);
		
		foreach($args as $key => $value)
			if(!array_key_exists($key, $defaults)) unset($args[$key]);
		
		list(
			'order_by' => $order_by,
			'order' => $order,
			'limit' => $limit
		) = $args;
		
		// ORDER BY CLAUSE
		if(!empty($order_by)) {
			$order = strtoupper($order);
			
			if($order !== 'ASC' && $order !== 'DESC') $order = 'ASC';
			
			$sql .= ' ORDER BY ' . (isset($px) ? $px . $order_by : $order_by) . ' ' . $order;
		}
		
		// LIMIT CLAUSE
		if(!empty($limit)) {
			if(is_array($limit)) $limit = implode(', ', $limit);
			
			$sql .= ' LIMIT ' . $limit;
		}
		
		try {
			// Output SQL statement if using debug mode
			if($debug) var_dump($sql);
			
			$select_query = $this->conn->prepare($sql);
			isset($values) ? $select_query->execute($values) : $select_query->execute();
			
			if(str_starts_with(strtoupper($cols), 'COUNT(')) {
				return $select_query->fetchColumn();
			} else {
				$data = array();
				
				while($row = $select_query->fetch(\PDO::FETCH_ASSOC)) {
					// Remove prefixes from return values
					if(isset($px)) {
						foreach($row as $key => $val) {
							if(str_starts_with($key, $px)) {
								$no_px = str_replace($px, '', $key);
								$row[$no_px] = $row[$key];
								unset($row[$key]);
							}
						}
					}
					
     				$data[] = $row;
				}
				
				return $data;
			}
		} catch(\PDOException $e) {
			$rs_error->logError($e);
			
			if(isDebugMode())
				$rs_error->triggerError();
			
			return array();
		}
	}
	
	/**
	 * Select only a single row from the database and return it.
	 * @since 1.1.1-alpha
	 *
	 * @access public
	 * @param string|array $table -- The table name and optionally, table prefix.
	 * @param string|array $cols (optional) -- The column(s) to query.
	 * @param array $where (optional) -- The where clause.
	 * @param array $args (optional) -- Additional args (e.g., `order_by`, `order`, `limit`).
	 * @return int|array
	 */
	public function selectRow(
		string|array $table, string|array $cols = '*', array $where = array(), array $args = array()
	): int|array {
		$data = $this->select($table, $cols, $where, $args);
		
		if(is_array($data) && !empty($data))
			return array_merge(...$data);
		else
			return $data;
	}
	
	/**
	 * Select only a single field from the database and return it.
	 * @since 1.8.10-alpha
	 *
	 * @access public
	 * @param string|array $table -- The table name and optionally, table prefix.
	 * @param string $col -- The column to query.
	 * @param array $where (optional) -- The where clause.
	 * @param array $args (optional) -- Additional args (e.g., `order_by`, `order`, `limit`).
	 * @return string
	 */
	public function selectField(string|array $table, string $col, array $where = array(), array $args = array()): string {
		$data = $this->selectRow($table, $col, $where, $args);
		
		if(is_array($data))
			return implode('', $data);
		else
			return $data;
	}
	
	/**
	 * Insert a row into the database.
	 * @since 1.1.0-alpha
	 *
	 * @access public
	 * @param string|array $table -- The table name and optionally, table prefix.
	 * @param array $data -- The data to insert.
	 * @param array $args (optional) -- Additional args.
	 * @return int
	 */
	public function insert(string|array $table, array $data, array $args = array()): int {
		global $rs_error;
		
		$debug = false;
		
		if(isset($args['debug']) && $args['debug']) $debug = true;
		
		// Check if the table is prefixed
		if(is_array($table)) {
			$px = array_pop($table);
			$table = implode('', $table);
		}
		
		$fields = $values = $placeholders = array();
		
		foreach($data as $field => $value) {
			// Prefix fields
			if(isset($px))
				$field = $px . $field;
			
			if(!is_null($value) && strtoupper($value) === 'NOW()') {
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
		
		// INSERT INTO CLAUSE
		$sql = 'INSERT INTO `' . $table . '` (' . $fields . ') VALUES (' . $placeholders . ')';
		
		try {
			// Output SQL statement if using debug mode
			if($debug) var_dump($sql);
			
			$insert_query = $this->conn->prepare($sql);
			$insert_query->execute($values);
			
			return $this->conn->lastInsertId();
		} catch(\PDOException $e) {
			$rs_error->logError($e);
			
			if(isDebugMode())
				$rs_error->triggerError();
			
			return -1;
		}
	}
	
	/**
	 * Update an existing row in the database.
	 * @since 1.1.0-alpha
	 *
	 * @access public
	 * @param string|array $table -- The table name and optionally, table prefix.
	 * @param array $data -- The data to update.
	 * @param array $where (optional) -- The where clause.
	 * @param array $args (optional) -- Additional args.
	 */
	public function update(string|array $table, array $data, array $where = array(), array $args = array()): void {
		global $rs_error;
		
		$debug = false;
		
		if(isset($args['debug']) && $args['debug']) $debug = true;
		
		// Check if the table is prefixed
		if(is_array($table)) {
			$px = array_pop($table);
			$table = implode('', $table);
		}
		
		$fields = $values = array();
		
		foreach($data as $field => $value) {
			// Prefix fields
			if(isset($px))
				$field = $px . $field;
			
			if(!is_null($value) && strtoupper($value) === 'NOW()') {
				$fields[] = $field . ' = ' . $value;
			} else {
				$fields[] = $field . ' = ?';
				$values[] = $value;
			}
		}
		
		$fields = implode(', ', $fields);
		
		// UPDATE SET CLAUSE
		$sql = 'UPDATE `' . $table . '` SET ' . $fields;
		
		// WHERE CLAUSE
		if(!empty($where)) {
			$conditions = $vals = $placeholders = array();
			
			// Accepted operators
			$operators = array(
				'=', '>', '<', '>=', '<=', '<>',
				'LIKE', 'IN', 'NOT IN'
			);
			
			// Defaults
			list($operator, $logic) = array('IN', 'AND');
			
			foreach($where as $field => $value) {
				if($field === 'logic') {
					$logic = strtoupper($value);
					continue;
				}
				
				// Prefix fields
				if(isset($px))
					$field = $px . $field;
				
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
					
					$conditions[] = match($operator) {
						'IN', 'NOT IN' => $field . ' ' . $operator . ' (' . implode(', ', $placeholders) . ')',
						default => $field . ' ' . $operator . ' ?'
					};
					
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
		
		try {
			// Output SQL statement if using debug mode
			if($debug) var_dump($sql);
			
			$update_query = $this->conn->prepare($sql);
			$update_query->execute($values);
		} catch(\PDOException $e) {
			$rs_error->logError($e);
			
			if(isDebugMode())
				$rs_error->triggerError();
		}
	}
	
	/**
	 * Delete a row from the database.
	 * @since 1.0.3-alpha
	 *
	 * @access public
	 * @param string|array $table -- The table name and optionally, table prefix.
	 * @param array $where (optional) -- The where clause.
	 * @param array $args (optional) -- Additional args.
	 */
	public function delete(string|array $table, array $where = array(), array $args = array()): void {
		global $rs_error;
		
		$debug = false;
		
		if(isset($args['debug']) && $args['debug']) $debug = true;
		
		// Check if the table is prefixed
		if(is_array($table)) {
			$px = array_pop($table);
			$table = implode('', $table);
		}
		
		// DELETE FROM CLAUSE
		$sql = 'DELETE FROM `' . $table . '`';
		
		// WHERE CLAUSE
		if(!empty($where)) {
			$conditions = $values = $vals = $placeholders = array();
			
			// Defaults
			list($operator, $logic) = array('IN', 'AND');
			
			foreach($where as $field => $value) {
				if($field === 'logic') {
					$logic = strtoupper($value);
					continue;
				}
				
				// Prefix fields
				if(isset($px))
					$field = $px . $field;
				
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
					$vals = array();
				} else {
					$conditions[] = $field . ' = ?';
					$values[] = $value;
				}
			}
			
			$sql .= ' WHERE ' . implode(' ' . $logic . ' ', $conditions);
		}
		
		try {
			// Output SQL statement if using debug mode
			if($debug) var_dump($sql);
			
			$delete_query = $this->conn->prepare($sql);
			isset($values) ? $delete_query->execute($values) : $delete_query->execute();
		} catch(\PDOException $e) {
			$rs_error->logError($e);
			
			if(isDebugMode())
				$rs_error->triggerError();
		}
	}
	
	/**
	 * Run a generic SQL query. Does not return data.
	 * @since 1.3.0-alpha
	 *
	 * @access public
	 * @param string $sql -- The SQL statement to execute.
	 */
	public function doQuery(string $sql): void {
		global $rs_error;
		
		try {
			$query = $this->conn->prepare($sql);
			$query->execute();
		} catch(\PDOException $e) {
			$rs_error->logError($e);
			
			if(isDebugMode())
				$rs_error->triggerError();
		}
	}
	
	/*------------------------------------*\
		ADVANCED QUERIES & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Show tables in the database.
	 * @since 1.3.3-alpha
	 *
	 * @access public
	 * @param string $table (optional) -- The table name.
	 * @return int|array
	 */
	public function showTables(string $table = ''): int|array {
		global $rs_error;
		
		$data = array();
		$sql = 'SHOW TABLES';
		
		if(!empty($table)) $sql .= ' LIKE \'' . $table . '\'';
		
		try {
			$query = $this->conn->prepare($sql);
			$query->execute();
			
			while($row = $query->fetch()) $data[] = $row;
			
			return $data;
		} catch(\PDOException $e) {
			$rs_error->logError($e);
			
			if(isDebugMode())
				$rs_error->triggerError();
			
			return -1;
		}
	}
	
	/**
	 * Show indexes in a table.
	 * @since 1.2.1-beta
	 *
	 * @access public
	 * @param string $table -- The table name.
	 * @return int|array
	 */
	public function showIndexes(string $table): int|array {
		global $rs_error;
		
		$sql = 'SHOW INDEXES FROM `' . $table . '`;';
		
		try {
			$query = $this->conn->prepare($sql);
			$query->execute();
			
			while($row = $query->fetch()) $data[] = $row;
			
			return $data;
		} catch(\PDOException $e) {
			$rs_error->logError($e);
			
			if(isDebugMode())
				$rs_error->triggerError();
			
			return -1;
		}
	}
	
	/**
	 * Check whether a table already exists in the database.
	 * @since 1.0.8-beta
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
	 * @since 1.3.5-beta
	 *
	 * @access public
	 * @param string $table -- The table name.
	 * @param string $col -- The column to query.
	 * @return int|bool
	 */
	public function columnExists(string $table, string $col): int|bool {
		global $rs_error;
		
		$sql = 'SHOW COLUMNS FROM `' . $table . '` LIKE \'' . $col . '\';';
		
		try {
			$query = $this->conn->prepare($sql);
			$query->execute();
			
			return !empty($query->fetch());
		} catch(\PDOException $e) {
			$rs_error->logError($e);
			
			if(isDebugMode())
				$rs_error->triggerError();
			
			return -1;
		}
	}
	
	/**
	 * Create a database table.
	 * @since 1.3.14-beta
	 *
	 * @access public
	 * @param string $table -- The table name.
	 * @param array $data -- All data to add to the table (e.g., columns and indexes).
	 */
	public function createTable(string $table, array $data): void {
		$sql = 'CREATE TABLE ' . $table . ' (';
		
		foreach($data as $line) $sql .= $line;
		
		$sql .= ');';
		
		$this->doQuery($sql);
	}
	
	/**
	 * Drop a table from the database.
	 * @since 1.2.0-beta
	 *
	 * @access public
	 * @param string $table -- The table name.
	 */
	public function dropTable(string $table): void {
		# Add protections against malicious dropping of tables
		
		$this->doQuery('DROP TABLE IF EXISTS `' . $table . '`;');
	}
	
	/**
	 * Drop multiple tables from the database.
	 * @since 1.2.0-beta
	 *
	 * @access public
	 * @param array $tables -- The table names.
	 */
	public function dropTables(array $tables): void {
		if(!is_array($tables)) $tables = (array)$tables;
		
		# ditto
		
		$sql = 'DROP TABLE IF EXISTS ';
		
		for($i = 0; $i < count($tables); $i++)
			$sql .= '`' . $tables[$i] . '`' . ($i < count($tables) - 1 ? ', ' : ';');
		
		$this->doQuery($sql);
	}
	
	/*------------------------------------*\
		MISCELLANEOUS
	\*------------------------------------*/
	
	/**
	 * Fetch a PDO attribute.
	 * @since 1.3.10-beta
	 *
	 * @access private
	 * @param string $attr -- The attribute's name.
	 * @return string
	 */
	private function getAttr(string $attr): string {
		return $this->conn->getAttribute(constant('\PDO::ATTR_' . $attr));
	}
	
	/**
	 * Initialize the charset and collation.
	 * @since 1.3.10-beta
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
	 * @since 1.3.10-beta
	 *
	 * @access private
	 * @param string|null $charset (optional) -- The character set.
	 * @param string|null $collate (optional) -- The collation.
	 */
	private function setCharset(?string $charset = null, ?string $collate = null): void {
		global $rs_error;
		
		if(!isset($charset)) $charset = $this->charset;
		if(!isset($collate)) $collate = $this->collate;
		
		if($this->hasCap('collation') && !empty($charset)) {
			$change_charset = $change_collate = false;
			
			if(file_exists(RS_CONFIG)) {
				$config_file = file(RS_CONFIG);
				
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
					$handle = fopen(RS_CONFIG, 'w');
					
					if($handle !== false) {
						foreach($config_file as $line) fwrite($handle, $line);
						
						fclose($handle);
					}
					
					$sql = 'SET NAMES ' . $charset;
					
					if(!empty($collate)) $sql .= ' COLLATE ' . $collate . ';';
					
					try {
						$query = $this->conn->prepare($sql);
						$query->execute();
					} catch(\PDOException $e) {
						$rs_error->logError($e);
						
						if(isDebugMode())
							$rs_error->triggerError();
					}
				}
			}
		}
	}
	
	/**
	 * Check whether the database supports a particular capability.
	 * @since 1.3.10-beta
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
}