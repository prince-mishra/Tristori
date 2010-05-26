<?php

	/*
		You can do the following in the controller

		1. TO DISPLAY ERRORS :
				display_error("Calls to the function <strong>display_error($message)</strong> are displayed like this");
				display_warning("Calls to the function <strong>display_warning($message)</strong> are displayed like this");
				display_system("Calls to the function <strong>display_system($message)</strong> are displayed like this");
				display("Calls to the function <strong>display($message)</strong> are displayed like this");

		2. TO HANDLE DATABASES :
				If you have the following table
				CREATE TABLE IF NOT EXISTS `students` (
					`id` int(11) NOT NULL auto_increment,
					`name` varchar(64) NOT NULL,
					`phone` varchar(64) NOT NULL,
					`status` varchar(128) NOT NULL,
				PRIMARY KEY  (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

				Then run php index.php generatrix preparedb

				This would create a class students and you can run
				$students = new students($this->getDb());
				$students_data = $students->select("*", "WHERE id=5");
				$students_data = $students->delete("WHERE id=5");
				$students_data = $students->update(array("name" => "sudhanshu"), "WHERE id=5");
				$students_data = $students->insert(array("name" => "sudhanshu", "phone" => "1234567890", "status" => "working on generatrix"));

		3. TO PASS VALUES TO THE VIEW :
				$this->set("sample", "This is sample content which was set in the controller");
				$this->set("students_data", $students_data);
	*/

	class imagesController extends Controller {

		public function base() {

		}

		public function process() {
			$http_host = $_SERVER['HTTP_HOST'];
			if($http_host == 'localhost:8080') $http_host = 'localhost';
			$host_name = 'http://' . $http_host;
			$actual_url = str_replace($host_name, '', APPLICATION_ROOT);
			$actual_url = str_replace($actual_url, '', $_SERVER['REQUEST_URI']);

			$size = $this->getP3();
			if(!$size) {
				location('/error/base/' . urlencode('The url was invalid'));
			}

			$size_ex = explode('x', $size);
			$height = isset($size_ex[0]) ? $size_ex[0] : '100';
			$width = isset($size_ex[1]) ? $size_ex[1] : '100';

			$image_path = str_replace('/images/process/' . $size, '', $actual_url);
			$full_image_path = 'http://' . $http_host . $image_path;

			$headers = get_headers($full_image_path);
			foreach($headers as $header) {
				if(strpos($header, 'Content-Type') !== false) {
					header($header);
				}
			}

			$new_url = 'http://' . $http_host . href('/framework/external/timthumb/timthumb.php'); 
			$new_url .= '?src=' . $image_path;
			$new_url .= '&h=' . $height;
			$new_url .= '&w=' . $width;

			echo file_get_contents($new_url);
		}

	}

?>
