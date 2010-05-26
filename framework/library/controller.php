<?php

	//
	// This is the base controller class
	//

	class Controller {
		private $generatrix;
		private $view;
		private $is_html;

		public function __construct() {
			$this->is_html = true;
		}

		public function getP1() {
			$url = $this->getURL();
			$p1 = isset($url[0]) ? mysql_real_escape_string($url[0], $this->getDb()->getConnection()) : false;
			return $p1;
		}

		public function getP2() {
			$url = $this->getURL();
			$p2 = isset($url[1]) ? mysql_real_escape_string($url[1], $this->getDb()->getConnection()) : false;
			return $p2;
		}

		public function getP3() {
			$url = $this->getURL();
			$p3 = isset($url[2]) ? mysql_real_escape_string($url[2], $this->getDb()->getConnection()) : false;
			return $p3;
		}

		public function getP4() {
			$url = $this->getURL();
			$p4 = isset($url[3]) ? mysql_real_escape_string($url[3], $this->getDb()->getConnection()) : false;
			return $p4;
		}

		public function getUploadedFile($tag, $where = '/app/cache/uploads/') {

			$return = array('error' => false, 'path' => false, 'name' => false);

			if(isset($_FILES[$tag]['name']) && ($_FILES[$tag]['name'] != '')) {
				$image = $_FILES[$tag];

				$orig_name = isset($image['name']) ? $image['name'] : false;
				$type = isset($image['type']) ? $image['type'] : false;
				$tmp_name = isset($image['tmp_name']) ? $image['tmp_name'] : false;
				$error_value = isset($image['error']) ? $image['error'] : false;

				if($error_value === false) {
					$return['error'] = 'The file could not be uploaded because ' . $error_value;
				} else {
					$dots = explode('.', $orig_name);
					if(count($dots) == 1) {
						$return['error'] = 'Your filename does not have an extension';
					} else {
						$extension = $dots[count($dots) - 1];
						unset($dots[count($dots) - 1]);
						$orig_file_name = implode('.', $dots);
						$new_name = sanitize(trim($orig_file_name)) . '_' . md5(time() . $orig_name) . '.' . $extension;
						while(file_exists(path($where . $new_name))) {
							$new_name = (time() % 100) . $new_name;
						}

						if(!move_uploaded_file($tmp_name, path($where . $new_name))) {
							$return['error'] = 'The file could not be uploaded. Please try again.';
						} else {
							$return['path'] = $where . $new_name;
							$return['name'] = $orig_name;
						}
					}
				}
			}

			return $return;
		}

		// Set the view for this controller
		public function setView($view_class) {
			$this->view = $view_class;
		}

		public function getView() {
			return $this->view;
		}

		public function getGeneratrix() {
			return $this->generatrix;
		}

		// If you want to send out an email, use the mail controller defined in generatrix
		public function getMailer() {
			return $this->generatrix->getMail();
		}

		public function setGeneratrix($generatrix) {
			$this->generatrix = $generatrix;
			$this->is_html = true;
		}

		// Set a variable which will be used inside the view
		public function set($tag_name, $tag_value) {
			$this->getView()->set($tag_name, $tag_value);
		}

		// Get access to the database class created inside Generatrix
		// TODO : Enable access for multiple databases
		public function getDb() {
			$default_database = $this->getGeneratrix()->getDatabase();
			//if(isset($default_database) && ($default_database != "")) {
			if(isset($default_database)) {
				return $default_database;
			} else {
				$database = new Database();
				$this->getGeneratrix()->setDatabase($database);
				return $this->getGeneratrix()->getDatabase();
			}
		}

		public function saveCookie($tag, $value) {
			$this->getGeneratrix()->getCookie()->add($tag, $value);
		}

		public function deleteCookie($tag) {
			$this->getGeneratrix()->getCookie()->delete($tag);
		}

		public function getPostValue($tag_name) {
			return checkArray($_POST, $tag_name) ? mysql_real_escape_string($_POST[$tag_name], $this->getDb()->getConnection()) : false;
		}

		public function getCookieValue($tag_name) {
			return checkArray($_COOKIE, $tag_name) ? mysql_real_escape_string($_COOKIE[$tag_name], $this->getDb()->getConnection()) : false;
		}

		public function getSessionValue($tag_name) {
			return checkArray($_SESSION, $tag_name) ? mysql_real_escape_string($_SESSION[$tag_name], $this->getDb()->getConnection()) : false;
		}

    public function getGetValue($tag_name) {
			return checkArray($_GET, $tag_name) ? mysql_real_escape_string($_GET[$tag_name], $this->getDb()->getConnection()) : false;
    }

		public function getURL() {
			return $this->getGeneratrix()->getRequestArray();
		}

		public function isHtml($value = '') {
			if($value === '') {
				// Getter
				return $this->is_html;
			} else {
				// Setter
				$this->is_html = $value;
			}
		}
	}

?>
