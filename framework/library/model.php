<?php

	class Model {

		public $database;

		public $name;
		public $columns;
		public $columns_name;

		private $is_join;
		private $join;

		public function __construct() {
			$this->columns = array();
			$this->columns_name = array();
			$this->is_join = false;
		}

		private function escapeString($string) {
			return str_replace('"', '\"', str_replace("'", "\'", $string));
		}

		public function construct($database, $name, $columns) {
			$this->name = $name;
			$this->database = $database;
			$this->columns = $columns;
			foreach($columns as $key => $value) {
				$this->columns_name[] = $key;
			}
		}

		public function getColumns() {
			return $this->columns;
		}

		public function insert($values) {
			$keys = array();
			$vals = array();

			foreach($values as $key => $value) {
				if(in_array($key, $this->columns_name)) {
					$keys[] = $key;
					$key_type = $this->columns[$key];
					switch($key_type) {
						case 'text':
							$vals[] = mysql_real_escape_string(htmlentities($value), $this->database->getConnection());
							break;
						case 'int':
							$vals[] = is_numeric($value) ? $value : 0;
							break;
						default:
							// Check for varchar_64
							$varchar_split = explode('_', $key_type);
							if($varchar_split[0] == 'varchar') {
								$vals[] = substr($value, 0, $varchar_split[1]);
								break;
							}
							display_error("The key type '{$key_type}' is not defined in the loop");
							break;
					}
				} else {
					display_error("The key '{$key}' does not exist in table '{$this->name}'");
					return false;
				}
			}

			$sql = 'INSERT INTO ' . $this->name . ' (`' . implode('`, `', $keys) . '`) VALUES("' . implode('", "', $vals) . '");';
			return $this->database->query($sql);
		}

		public function delete($condition) {
			$sql = 'DELETE FROM ' . $this->name . ' ' . $condition . ';';
			return $this->database->query($sql);
		}

		public function select($columns, $condition = '') {
			if(!$this->is_join) {
				$sql = 'SELECT ' . $columns . ' FROM ' . $this->name . ' ' . $condition;
				return $this->database->query($sql);
			} else {
				$columns = explode(',', $columns);
				foreach($columns as $column) {
					$column = trim($column);
					$dots = ( (strpos($column, 'AS') === false) && (strpos($column, 'COUNT') === false) && (strpos($column, '*') === false)) ? explode('.', $column) : array();
					$cols[] = isset($dots[1]) ? ($column . ' AS ' . $dots[0] . '_' . $dots[1]) : $column;
				}
				$join_columns = implode(', ', $cols);
				$sql = 'SELECT ' . $join_columns . ' FROM ' . $this->join . ' ' . $condition;
				return $this->database->query($sql);
			}
		}

		public function update($columns, $condition = '') {
			$updates = array();
			foreach($columns as $key => $value) {
				$updates[] = ' `' . $key . '` = "' . mysql_real_escape_string(stripslashes(htmlentities($value)), $this->database->getConnection()) . '" ';
			}
			$sql = 'UPDATE ' . $this->name . ' SET ' . implode(', ', $updates) . ' ' . $condition;
			return $this->database->query($sql);
		}

		public function join($type, $table, $condition) {
			if(!$this->is_join) {
				$this->join = "$this->name $type JOIN $table ON $condition";
				$this->is_join = true;
			} else {
				$this->join = "( $this->join ) $type JOIN $table ON $condition";
			}
		}

		public function innerjoin($table, $condition) {
			$this->join('INNER', $table, $condition);
		}

		public function leftjoin($table, $condition) {
			$this->join('LEFT', $table, $condition);
		}
	}

?>
