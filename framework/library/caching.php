<?php

	class Cache {

		private $location;
		private $duration;

		public function __construct($duration = '', $location = '') {
			$this->duration = ($duration != '') ? $duration : CACHE_TIME;
			$this->location = ($location != '') ? $location : '/app/cache';
			if(perms($this->location) != '777') {
				display_error('Please make your cache writable by running the following command $ chmod -R 777 ' . path($this->location));
			}
		}

		private function getFileName($key) {
			return path($this->location . '/' . md5($key) . '.cac');
		}

		public function set($key, $value) {
			return file_put_contents($this->getFileName($key), $value);
		}

		public function get($key, $time = '') {
			$time = ($time != '') ? $time : $this->duration;
			$file_name = $this->getFileName($key);
			if( (time() - filemtime($file_name)) > $time ) {
				return false;
			} else {
				return file_get_contents($file_name);
			}
		}

	}

?>
