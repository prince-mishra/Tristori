<?php

	//require_once(DISK_ROOT . 'generatrix/external/curl.php');

	class DomParser {

		private $curl;

		private $url;
		private $data;
		private $dom;

		public function __construct() {
			$this->curl = new Curl();
		}

		public function load($url, $create_dom = true, $data_type = 'html') {
			$this->url = $url;
			$this->data = $this->curl->get($url);

			if($create_dom) {
				if($data_type == 'html') {
					$this->dom = new DOMDocument();
					@$this->dom->loadHTML($this->data);
				} else if($data_type == 'xml') {
					// Remove the headers
					$temp_data_array = explode('<?xml', $this->data);
					$this->data = "<?xml" . $temp_data_array[1];
					$this->dom = new DOMDocument();
					@$this->dom->loadXML($this->data);
				}
			}
			return $this;
		}

		public function getData() {
			return $this->data;
		}

		public function getDom() {
			return $this->dom;
		}

		// Special Functions
		public function matchAttribute($node, $att_name, $att_value) {
			$values = explode(' ', $this->att($node, $att_name));
			foreach($values as $value) {
				if(($value != '') && ($value == $att_value))
					return true;
			}
			return false;
		}

		public function href($node) {
			return $node->getAttribute('href');
		}

		public function src($node) {
			return $node->getAttribute('src');
		}

		public function elements($node, $tag_name) {
			return $node->getElementsByTagName($tag_name);
		}

		public function att($node, $att_name) {
			return $node->getAttribute($att_name);
		}

		public function name($node) {
			return $node->nodeName;
		}

		public function text($node) {
			return $node->nodeValue;
		}

	}

?>
