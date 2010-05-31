<?php

	//
	// A singleton class for accessing the database
	//

	class Database {

		private $debug;

		private $database;
		private $connection;
		private $tables;
		private $errors = array();

		private $file;

		private $query;

		public function __construct() {
			// If the debugger is one, show the query time on the screen
			$this->debug = false;
			if(DEBUG_QUERIES)
				$this->debug = true;
			$this->file = new File();
		}

		public function getConnection() {
			if(!$this->connection) {
				$this->connect();
			}
			return $this->connection;
		}

		public function getInstance() {
			if(!$this->database)
				$this->database = new Database();
			return $this->database;
		}

		public function getError() {
			return $this->error;
		}

		private function foundErrors() {
			if(!empty($this->errors))
				return true;
			return false;
		}

		// Set the error string if there was an error, you can retrieve it later
		private function setError($error_string = "") {
			if($error_string == "") {
				// get the error message
				if($error = mysql_error()) {
					// If there are already errors present in the error string or the last error is the same as this error, we don't need to save this
					// TODO : Handle multiple errors
					if((count($this->errors) > 0) && ($this->errors[count($this->errors) - 1 ] == $error)) {
						return;
					}
					// Add this error to the errors array
					$this->errors[] = $error . "<br /><br />\n\nYou ran the following query : " . $this->query;
				}
			} else {
				// If this is a user defined error, just add it to the list if it is not null
				if(isset($error_string) && ($error_string != "")) {
					$this->errors[] = $error_string;
				}
			}
		}

		public function setTables($tables) {
			// Not used now
			$this->tables = $tables;
			return $this;
		}

		private function connect() {
			// Connect to the database
			// TODO : Connect to multiple databases
			if(!$this->connection) {
				// Connect to the database
				if(!$this->connection = mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS))
					$this->setError();
				// Select the database
				if(!mysql_select_db(DATABASE_NAME))
					$this->setError();
			}
		}

		private function checkQuery($sql) {
			// Check if query is empty
			if($sql == "")
				$this->setError("The query string was empty");
		}

		private function getQueryType($sql) {
			// Get the query type (from the first word in the query) to define if the query is select/insert/something else
			$sql = str_replace("\n", " ", $sql);
			$split_array = explode(" ", trim($sql));
			return strtolower($split_array[0]);
		}

		private function getResults($sql, $query_type) {
			// Get the results in an array
      $start_time = 0;
      $end_time = 0;
      $cached_query = false;

			// If debugger is on, start the clock for the query
      if($this->debug) {
        $start_time = microtime(true);
      }

      $results;
			$results = $this->createArray($query_type, mysql_query($sql));

			// If debugging is one, display the time required in the query string
      if($this->debug) {
        $end_time = microtime(true);
        if($cached_query)
          display_system("[" . round(( 1000 * ($end_time - $start_time)), 2) . " ms][cached] $sql");
        else
          display_system("[" . round(( 1000 * ($end_time - $start_time)), 2) . " ms] $sql");
      }

			return $results;
		}

		private function createArray($query_type, $output) {
			// Create an array out of the output data strucutre of mysql_query
			$results = array();
			$this->setError();
			// Get query type from the function above
			switch($query_type) {
				case 'select':
				case 'describe':
				case 'show':
					if(!$this->foundErrors()) {
						while($ret = mysql_fetch_assoc($output)) {
							// add all results to an array
							array_push($results, $ret);
						}
					}
					break;
				case 'insert':
					// For insert, return an array with the latest insert id
					array_push($results, array('id', mysql_insert_id()));
					break;
				default:
					// For others, just return true
					array_push($results, array('result', 'true'));
					break;
			}
			$this->setError();
			return $results;
		}

		public function query($sql) {
			$this->query = $sql;
			$this->errors = array();
			// This is the public function
			$this->checkQuery($sql);
			// Connect to the database, if you haven't done it already
			$this->connect();

			// Get the results form the database/cache
			$results = $this->getResults($sql, $this->getQueryType($sql));

			// If you find any errors, display them
			if($this->foundErrors()) {
				if(is_array($this->errors)) {
					foreach($this->errors as $error) {
						display_error($error);
					}
				} else {
					display_error($this->errors);
				}
				return false;
			}

			$this->errors = array();
			return $results;
		}

		// Table Manipulation Functions
		private function escapeString($data) {
			// Escape a string without using mysql_real_escape_string
			return str_replace("'", "\'", str_replace('"', '\"', $data));
		}

		private function convertUnixToMysqlDate($unix_time) {
			// TODO : Convert a unix time to mysql date
		}

		public function __destruct() {
			// In destructor, close database connection if we opened one
			if($this->connection) {
				mysql_close($this->connection);
			}
		}

	}

?>
