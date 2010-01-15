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

	class adminController extends Controller {

		private $use;

		private $params;

		private $table;
		private $action;
		private $id;

		private $controller_method;

		public function dashboard() {
			// The urls would decide which page to show
			// They can be of the format
			// 		/admin/dashboard/stores/view/1  	where 1 is the page number
			//		/admin/dashboard/stores/edit/54		where 54 is the id
			//		/admin/dashboard/stores/delete/4	where 4 is the id
			//		/admin/dahsboard/stores/add/			

			// Please mention which tables would you like to create an admin for
			$this->checkIfUserLoggedIn();
			$this->useTables(array(
				'accounts',
				'account_authentication',
				'activity',
				'authentication',
				'bullet_list',
				'college',
				'company',
				'company_college',
				'invites',
				'job_meta',
				'job_profiles',
				'job_profle_search_criteria',
				'lists',
				'list_company',
				'list_institute',
				'list_university',
				'shortlisted_institutes',
				'shortlisted_student',
				'student',
				'student_bullets',
				'student_resume',
				'gtx_users'
			));

			$method = $this->getPostValue('method');
			if($method) {
				// A form was posted

				switch($this->action) {
					case 'add':
						$this->_insert($this->table, $this->getRequiredFields());
						location('/admin/dashboard/' . $this->table . '/view/1');
						break;
					case 'edit':
						$this->_update($this->table, $this->id, $this->getRequiredFields());
						location('/admin/dashboard/' . $this->table . '/edit/' . $this->id);
						break;
					case 'view':
					case 'delete':
					default:
						break;
				}
				location('/admin/dashboard/' . $this->table . '/view/1');

			} else {
				// Just show me the bloody page

				$this->set('use', $this->use);
				$this->set('table', $this->table);
				$this->set('action', $this->action);
				$this->set('id', $this->id);
				$this->set('table_info', $this->_getTableInfo($this->table));
		
				switch($this->action) {
					case 'view':
						$this->set('display', $this->_display($this->table, $this->id, 25));
						break;
					case 'edit':
						$this->set('display', $this->_display($this->table, '', '', 'id', $this->id));
						break;
					case 'delete':
						$del = $this->_delete($this->table, $this->id);
						location('/admin/dashboard/' . $this->table . '/view/1');
						break;
					default:
						break;
				}
			}
		}


		//
		// Core Admin Functions
		//

		public function base() {
			location('/admin/login');
		}

		public function login() {
			$this->checkIfUsersTableExists();
			$this->checkIfUserLoggedIn();

			$username = $this->getPostValue('username');
			$password = $this->getPostValue('password');
			$method = $this->getPostValue('method');

			if($method == 'generatrix.admin.login') {
				if(!$username) {
					$this->set('errors', 'Please enter a username');
				} else if(!$password) {
					$this->set('errors', 'Please enter a password');
				} else {
					$users = new gtx_users($this->getDb());
					$users_data = $users->select('*', 'WHERE username = "' . $username . '"');

					if(isset($users_data[0]['password']) && ($users_data[0]['password'] != '') && ($users_data[0]['password'] == md5('gtx_users' . $password))) {
						$this->saveCookie('user-id', $users_data[0]['id']);
						$this->saveCookie('user-username', $users_data[0]['username']);
						$this->saveCookie('user-email', $users_data[0]['email']);
						$this->saveCookie('user-hash', $users_data[0]['hash']);
						$this->saveCookie('user-name', $users_data[0]['name']);
						
						location('/admin/dashboard');
					} else {
						$this->set('errors', 'The username / password did not match');
					}
				}
			}
		}

		public function logout() {
			$this->deleteCookie('user-id');
			$this->deleteCookie('user-username');
			$this->deleteCookie('user-email');
			$this->deleteCookie('user-hash');
			$this->deleteCookie('user-name');
			location('/admin/login');
		}

		//
		// Private Functions
		//

		private function _getTableInfo($table_name) {
			if(class_exists($table_name)) {
				$table = new $table_name($this->getDb());
				return $table->getColumns();
			}
			return false;
		}

		private function getRequiredFields() {
			$columns = $this->_getTableInfo($this->table);
			$column_names = array_keys($columns);

			$final = array();
			foreach($column_names as $column_name) {
				if($column_data = $this->getPostValue($column_name)) {
					$final[$column_name] = $column_data;
				}
			}
			return $final;
		}

		private function _display($table_name, $page = '', $per_page = '', $column = '', $column_value = '', $comparison = '=') {
			// The comparison values can be as follows:
			//		1. Numeric 	
			//				<  <= = >= >
			//    2. String   LIKE %%
			if(!class_exists($table_name))
				return false;

			$table = new $table_name($this->getDb());
			$condition = '';

			if(($column != '') && ($column_value != '')) {
				if($comparison == 'LIKE') {
					$condition .= ' WHERE `' . $column . '` LIKE "%' . $column_value . '%" ';
				} else {
					$condition .= ' WHERE `' . $column . '` ' . $comparison . ' "' . $column_value . '" ';
				}
			}

			if(($page != '') && ($per_page != '') && is_numeric($page) && is_numeric($per_page) && ($page > 0) && ($per_page > 0)) {
				$condition .= ' LIMIT ' . (($page - 1) * $per_page) . ', ' . $per_page;
			}

			$table_data = $table->select('*', $condition);
			return $table_data;
		}

		private function _insert($table_name, $data) {
			if(class_exists($table_name) && ($data != '')) {

				$table = new $table_name($this->getDb());

				$columns = $table->getColumns();
				$column_names = array_keys($columns);

				$selected_data = array();
				foreach($data as $key => $value) {
					if((in_array($key, $column_names)) && ($key != 'id')) {
						$selected_data[$key] = $value;
					}
				}

				$table_data = $table->insert($selected_data);
				return $table_data;
			}
			return false;
		}

		private function _update($table_name, $id, $data) {
			if(class_exists($table_name) && is_numeric($id) && ($id > 0) && is_array($data)) {
				$table = new $table_name($this->getDb());

				$columns = $table->getColumns();
				$column_names = array_keys($columns);

				$selected_data = array();
				foreach($data as $key => $value) {
					if((in_array($key, $column_names)) && ($key != 'id')) {
						$selected_data[$key] = $value;
					}
				}

				$table_data = $table->update($selected_data, 'WHERE id="' . $id . '"');
				return $table_data;
			}
			return false;
		}

		private function _delete($table_name, $id) {
			if(class_exists($table_name) && is_numeric($id) && ($id > 0)) {
				$table = new $table_name($this->getDb());
				$table_data = $table->delete('WHERE id="' . $id . '"');
				return $table_data;
			} else {
				display_error('Could not delete value for ' . $id);
			}
		}

		private function parseURL() {

			$url = $this->getURL();
			$this->controller_method = $url[1];
			while($url[0] != 'dashboard') {
				array_shift($url);
			}
			array_shift($url);

			// 		/admin/dashboard/stores/view/1  	where 1 is the page number
			//		/admin/dashboard/stores/edit/54		where 54 is the id
			//		/admin/dashboard/stores/delete/4	where 4 is the id
			//		/admin/dahsboard/stores/add/			
			if( ($url[0] == '') || ( !in_array($url[0], $this->use) ) ) {
				$this->_dashboardHome($this->use[0]);
			} else {
				if($url[1] == '') {
					$this->_dashboardHome($url[0]);
				} else {
					switch($url[1]) {
						case 'view':
							if(($url[2] == '') || ($url[2] < 1)) {
								$this->_dashboardHome($url[0]);
							}
							break;
						case 'edit':
							if(($url[2] == '') || ($url[2] < 1)) {
								$this->_dashboardHome($url[0]);
							}
							break;
						case 'delete':
							if(($url[2] == '') || ($url[2] < 1)) {
								$this->_dashboardHome($url[0]);
							}
							break;
						case 'add':
							break;
						default:
							$this->_dashboardHome($url[0]);
							break;
					}
				}
			}

			$this->table = $url[0];
			$this->action = $url[1];
			$this->id = $url[2];

			return $url;
		}

		private function _dashboardHome($which) {
			location('/admin/dashboard/' . $which . '/view/1');
		}

		private function useTables($tables) {
			if(is_array($tables) && isset($tables[0])) {
				$this->use = $tables;
			} else {
				display_error('Please select which tables you would like to show');
				exit();
			}

			// Prase urls
			$this->params = $this->parseURL();

		}

		private function checkIfUserLoggedIn() {
			$user_id = $this->getCookieValue('user-id');
			if($user_id && ($user_id > 0)) {
				if($this->controller_method != 'dashboard') {
					//location('/admin/dashboard');
				}
			} else {
				if($this->controller_method != 'login') {
					//location('/admin/login');
				}
			}
		}

		private function checkIfUsersTableExists() {
			if(!class_exists('gtx_users')) {
				$this->getDb()->query('
					CREATE TABLE  `gtx_users` (
						`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
						`username` VARCHAR( 64 ) NOT NULL ,
						`password` VARCHAR( 64 ) NOT NULL ,
						`email` VARCHAR( 64 ) NOT NULL ,
						`lastlogin` VARCHAR( 32 ) NOT NULL ,
						`ip` VARCHAR( 32 ) NOT NULL ,
						`name` VARCHAR( 64 ) NOT NULL ,
						`hash` VARCHAR( 64 ) NOT NULL
					) ENGINE = MYISAM
				');

				$password = substr(md5(time()), 0, 5);

				$this->getDb()->query('
					INSERT INTO `gtx_users` ( `username` , `password` , `email` , `lastlogin` , `ip` , `name` , `hash`) VALUES ( "admin", "' . md5('gtx_users' . $password) . '", "admin@site.com", "0", "0", "admin", "' . md5($password) . '"); 
				');

				display('Your users table wasn\'t found. We have created a new table. You would now need to run prepareDb again from the command line to be able to access it.');
				display('<br /><br />
					Your username and password are as below :<br />
					Username : admin<br />
					Password : ' . $password . '<br /><br />
				');
			}
		}

	}

?>
