<?php

	class Element {
		private $type;
		private $content;
		private $attributes = array();
	
		// Check if the element has the particular key
		public function hasKey($key) {
			if(isset($this->attributes[$key]))
				return true;
			return false;
		}

		// Get a list of attributes
		public function getAttributes() {
			return $this->attributes;
		}

		// Get the value for a particular attribute
		public function get($key) {
			if($this->hasKey($key))
				return $this->attributes[$key];
			return false;
		}

		// Set a particular attribute in the HTML Element
		public function set($key, $value) {
			$this->attributes[$key] = $value;
			return $this;
		}

		// Get the type (name) of the HTML element
		public function getType() {
			return $this->type;
		}

		// Set the type (name) of the HTML element
		public function setType($type) {
			$this->type = $type;
			return $this;
		}

		// Get the content inside the HTML element
		public function getContent() {
			return $this->content;
		}

		// Set the content inside the HTML element
		public function setContent($content) {
			$this->content = $content;
			return $this;
		}

		// Add content to the HTML element
		public function appendContent($content) {
			if(version_compare(PHP_VERSION, '5.2.0') >= 0) {
				$this->content .= ("\n" . $content);
			} else {
				if(is_object($content))
					$this->content .= ("\n" . $content->_toString());
				else
					$this->content .= ("\n" . $content);
			}
			return $this;
		}

		// Convert the class to a string : Compatible only with PHP 5.2.0+
		public function _toString() {
			$html = '';
			$html .= '<' . $this->type;

			$keys = array_keys($this->attributes);
			foreach($keys as $key) {
				$html .= ' ' . $key . '="' . $this->attributes[$key] . '" ';
			}

			$require_closing_bracket = array('div', 'span', 'script');
			if(isset($this->content) || in_array($this->type, $require_closing_bracket)) {
				$content = isset($this->content) ? $this->content : "";
				$html .= " >" . $content;
				$html .= "</" . $this->type . ">";
			} else {
				$html .= " />\n";
			}
			return $html;
		}

		public function __toString() {
			$html = '';
			$html .= '<' . $this->type;

			$keys = array_keys($this->attributes);
			foreach($keys as $key) {
				$html .= ' ' . $key . '="' . $this->attributes[$key] . '" ';
			}

			$require_closing_bracket = array('div', 'span', 'script');
			if(isset($this->content) || in_array($this->type, $require_closing_bracket)) {
				$content = isset($this->content) ? $this->content : "";
				$html .= " >" . $content;
				$html .= "</" . $this->type . ">";
			} else {
				$html .= " />\n";
			}
			return $html;
		}
	}

	class Html extends Element {
		public function __construct() {
			$this->setType('html');
		}
	}

	class Head extends Element {
		public function __construct() {
			$this->setType('head');
		}
	}

	class Body extends Element {
		public function __construct() {
			$this->setType('body');
		}
	}

?>
