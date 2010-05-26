<?php

	// This class creates a template for an associated list.
	// You can use this for any kind of any key value pairs and use the functions provided

	class AssocList {
		private $data;
		private $parameters;

		// Check if the particular key is available
		public function hasKey($key) {
			if(isset($this->parameters[$key]))
				return true;
			return false;
		}

		// Check if the particular key has a value
		public function hasValue($key) {
			if(isset($this->parameters[$key]) && ($this->parameters[$key] != ''))
				return true;
			return false;
		}

		// Get the value of the particular key, if available
		public function getValue($key) {
			if($this->hasKey($key))
				return $this->parameters[$key];
			return false;
		}

		// Set the value of a particular key
		public function setValue($key, $value) {
			$this->parameters[$key] = $value;
			return true;
		}

		// Add a key, value pair
		public function addParameter($key, $value) {
			$this->parameters[$key] = $value;
			return true;
		}

		// Remove a key value pair
		public function removeParameter($key) {
			unset($this->parameters[$key]);
			return true;
		}

		// Set the data array into the class for reference later on
		public function setData($data) {
			$this->data = $data;
		}

		// Get the data array from the class
		public function getData() {
			return $this->data;
		}

		public function getDataArray($parse = '/') {
			$data_array = explode($parse, $this->data);
			return $data_array;
		}

		// Check if the enabled key has been set, and if yes, return the value
		public function isEnabled() {
			return $this->getValue('enabled');
		}
	}

?>
