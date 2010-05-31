<?php

	//
	// A class for CURL
	//

	class Curl {

		private $callback = false;
		private $secure = false;
		private $connection = false;
		private $user_agent = '';
		private $user_cookie = '';

		public function __construct() {
			if(!function_exists('curl_version')) {
				display_error('The extension <strong>php5-curl</strong> has not been installed.');
				return false;
			}
			$this->connection = curl_init();
			$this->setUserAgent('Generatrix 0.47');
			$this->setUserCookie('/app/cache/curl-cookie.txt');
		}

		public function __destruct() {
			if(isset($this->connection)) {
				curl_close($this->connection);
			}
		}
	
		private function setCallback($func_name) {
			$this->callback = $func_name;
		}

		private function doRequest($method, $url, $vars) {
			curl_setopt($this->connection, CURLOPT_URL, $url);
			curl_setopt($this->connection, CURLOPT_HEADER, 1);
			curl_setopt($this->connection, CURLOPT_USERAGENT,$this->user_agent);

			if($this->secure) {
				curl_setopt($this->connection, CURLOPT_SSL_VERIFYHOST,  0);
				curl_setopt($this->connection, CURLOPT_SSL_VERIFYPEER, 0);
			}

			curl_setopt($this->connection, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->connection, CURLOPT_COOKIEJAR, $this->user_cookie);
			curl_setopt($this->connection, CURLOPT_COOKIEFILE, $this->user_cookie); 

			if ($method == 'POST') {
				curl_setopt($this->connection, CURLOPT_POST, 1);
				curl_setopt($this->connection, CURLOPT_POSTFIELDS, $vars);
			}

			if ($data = curl_exec($this->connection))
				return $data;
			return curl_error($this->connection);
		}

		public function isSecure() {
			$this->secure = true;
		}

		public function get($url) {
			return $this->doRequest('GET', $url, 'NULL');
		}

		public function post($url, $vars) {
			return $this->doRequest('POST', $url, $vars);
		}

		public function setUserAgent($string) {
			$this->user_agent = $string;
		}

		public function setUserCookie($path) {
			if(!file_exists(path($path))) {
				$file = new File();
				if(perms('/app/cache/') != '777') {
					display_system('The path to the file ' . path($path) . ' is not writable.<br />Please enter chmod -R 777 ' . path('/app/cache/') . ' on your terminal to fix this.');
				} else {
					$file->write($path, ' ');
					chmod(path($path), 0755);
				}
			}
			$this->user_cookie = path($path);
		}
	}

?>
