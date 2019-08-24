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
	 * @access private
	 * @var object
	 */
	private $conn;
	
	/**
	 * The status of the database connection.
	 * @since 1.3.0[a]
	 * @access public
	 * @var bool
	 */
	public $conn_status;
	
	/**
	 * Class constructor. Initializes the database connection.
	 * @since 1.0.0[a]
	 * @access public
	 */
	public function __construct() {
		try {
			$this->conn = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHAR, DB_USER, DB_PASS);
			$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
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
	 * @param string $table
	 * @param string|array $data (optional; default: '*')
	 * @param string|array $where (optional; default: '')
	 * @param string $order_by (optional; default: '')
	 * @param string $order (optional; default: 'ASC')
	 * @param string|array $limit (optional; default: '')
	 * @return array
	 */
	public function select($table, $data = '*', $where = '', $order_by = '', $order = 'ASC', $limit = '') {
		if(empty($table)) exit($this->errorMsg('table'));
		if(is_array($data)) $data = implode(', ', $data);
		
		$db_data = array();
		$sql = 'SELECT '.$data.' FROM `'.$table.'`';
		
		if(!empty($where)) {
			if(!is_array($where)) exit($this->errorMsg('where'));
			
			$conditions = $values = $vals = $placeholders = array();
			$operator = '<>';
			
			foreach($where as $field=>$value) {
				if(is_array($value)) {
					foreach($value as $val) {
						if(is_string($val)) {
							if($val === '<>' || $val === '!')
								$operator = '<>';
							elseif(strtoupper($val) === 'IN' || strtoupper($val) === 'NOT IN')
								$operator = strtoupper($val);
						}
						
						if(strtoupper($val) === $operator) continue;
						
						$vals[] = $val;
						$placeholders[] = '?';
					}
					
					if($operator === '<>')
						$conditions[] = $field.' <> ?';
					elseif($operator === 'IN' || $operator === 'NOT IN')
						$conditions[] = $field.' '.$operator.' ('.implode(', ', $placeholders).')';
					
					$values = array_merge($values, $vals);
				} else {
					$conditions[] = $field.' = ?';
					$values[] = $value;
				}
			}
			
			$conditions = implode(' AND ', $conditions);
			$sql .= ' WHERE '.$conditions;
		}
		
		if(!empty($order_by)) $sql .= ' ORDER BY '.$order_by.' '.strtoupper($order);
		
		if(!empty($limit)) {
			if(is_array($limit)) $limit = implode(', ', $limit);
			
			$sql .= ' LIMIT '.$limit;
		}
		
		try {
			$select_query = $this->conn->prepare($sql);
			isset($values) ? $select_query->execute($values) : $select_query->execute();
			
			if(strpos(strtoupper($data), 'COUNT') !== false) {
                return $select_query->fetchColumn();
            } else {
     			while($row = $select_query->fetch(PDO::FETCH_ASSOC))
     				$db_data[] = $row;
				
                return $db_data;
            }
		} catch(PDOException $e) {
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
	 * @param string|array $where (optional; default: '')
	 * @param string $order_by (optional; default: '')
	 * @param string $order (optional; default: 'ASC')
	 * @param string|array $limit (optional; default: '')
	 * @return array
	 */
	public function selectRow($table, $data = '*', $where = '', $order_by = '', $order = 'ASC', $limit = '') {
		$db_data = $this->select($table, $data, $where, $order_by, $order, $limit);
		
		if(is_array($db_data) && !empty($db_data))
			return array_merge(...$db_data);
		else
			return $db_data;
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
		if(empty($table)) exit($this->errorMsg('table'));
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
		
		$sql = 'INSERT INTO `'.$table.'` ('.$fields.') VALUES ('.$placeholders.')';
		
		try {
			$insert_query = $this->conn->prepare($sql);
			$insert_query->execute($values);
			
			return $this->conn->lastInsertId();
		} catch(PDOException $e) {
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
	 * @param string|array $where (optional; default: '')
	 * @return null
	 */
	public function update($table, $data, $where = '') {
		if(empty($table)) exit($this->errorMsg('table'));
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
		$sql = 'UPDATE `'.$table.'` SET '.$fields;
		
		if(!empty($where)) {
			if(!is_array($where)) exit($this->errorMsg('where'));
			
			$conditions = $vals = $placeholders = array();
			$operator = 'IN';
			
			foreach($where as $field=>$value) {
				if(is_array($value)) {
					foreach($value as $val) {
						if($val === 'IN' || $val === 'NOT IN') {
							$operator = $val;
						} else {
							$vals[] = $val;
							$placeholders[] = '?';
						}
					}
					
					$conditions[] = $field.' '.$operator.' ('.implode(', ', $placeholders).')';
					$values = array_merge($values, $vals);
				} else {
					$conditions[] = $field.' = ?';
					$values[] = $value;
				}
			}
			
			$conditions = implode(' AND ', $conditions);
			$sql .= ' WHERE '.$conditions;
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
	 * @param string|array $where (optional; default: '')
	 * @return null
	 */
	public function delete($table, $where = '') {
		if(empty($table)) exit($this->errorMsg('table'));
		
		$sql = 'DELETE FROM `'.$table.'`';
		
		if(!empty($where)) {
			if(!is_array($where)) exit($this->errorMsg('where'));
			
			$conditions = $values = $vals = $placeholders = array();
			$operator = 'IN';
			
			foreach($where as $field=>$value) {
				if(is_array($value)) {
					foreach($value as $val) {
						if($val === 'IN' || $val === 'NOT IN') {
							$operator = $val;
						} else {
							$vals[] = $val;
							$placeholders[] = '?';
						}
					}
					
					$conditions[] = $field.' '.$operator.' ('.implode(', ', $placeholders).')';
					$values = array_merge($values, $vals);
				} else {
					$conditions[] = $field.' = ?';
					$values[] = $value;
				}
			}
			
			$conditions = implode(' AND ', $conditions);
			$sql .= ' WHERE '.$conditions;
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
	 * @return null
	 */
	public function doQuery($sql) {
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
	 * @return string
	 */
	public function showTables() {
		$data = array();
		
		try {
			$query = $this->conn->prepare("SHOW TABLES");
			$query->execute();
			
			while($row = $query->fetch())
				$data[] = $row;
			
			return $data;
		} catch(PDOException $e) {
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
		
		echo $error;
	}
}