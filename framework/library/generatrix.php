<?php

	define('DISK_ROOT', str_replace('framework/library/generatrix.php', '', __FILE__));

	class Generatrix {
		private $request;
		private $cli;

		private $post;
		private $session;
		private $cookie;

		private $database;

		private $controller;
		private $method;

		private $mail;

		private $file;

		private $is_page_cached;

		private $start_time;

		public function __construct($argv = '') {
			$this->is_page_cached = 'NOT CACHED';
			$this->start_time = microtime(true);
			$this->debugValues();
			$this->bootstrap($argv);
			set_error_handler('handle_errors');
			$this->handleRequest();
		}

		public function __destruct() {
			// If values are to be debugged, update the caching status
			if(DEBUG_VALUES) {
				if(CACHE_PAGES) {
					display_system("[" . round( (1000 * ( microtime(true) - $this->start_time )), 2) . " ms] Caching Status : " . $this->is_page_cached);
				}
			}
		}

		public function setDatabase($database) {
			// Create a copy of the database class
			$this->database = $database;
			return $this;
		}

		public function getDatabase() {
			return $this->database;
		}

		private function debugValues() {
			// Check if system wide error messages are to be shown
			if(DEBUG_VALUES) {
				ini_set('error_reporting', E_ALL);
				error_reporting(E_ALL);
			}
		}

		private function checkCache() {
			// Check if cache exists and if the page requested is available in the cache
			$this->file = new File();

			if((perms('/app/cache') != '777') || (perms('/app/cache/database') != '777') || (perms('/app/cache/pages') != '777')) {
				display_system('The cache is <strong>not writable</strong>. You can fix it by running <strong>chmod -R 777 ' . path('/app/cache') . '</strong>');
			}

			// If were are caching pages and there are no post values set then try to get the file from the cache
			// (We check for $_POST because if say a search form is sending post values, we don't want to show the cached page)
			if(CACHE_PAGES && (count($_POST) == 0)) {
				// If url is defined (from .htaccess) use that, for the home page, use index
				$file_name = isset($_GET['url']) ? md5($_GET['url']) : md5('index');
				// All cached files are located in cache/pages and have the extension .cac
				$file_name = '/app/cache/pages/' . $file_name . '.cac';
				if(file_exists(path($file_name))) {
					// Check if the file was created within the time duration
					if((time() - filemtime(path($file_name))) < CACHE_PAGES_TIME) {
						$this->is_page_cached = 'CACHED';
						echo $this->file->read($file_name);
						return true;
					}
				}
			}
			// File not available in cache, run the code
			return false;
		}

		public function getController() {
			return $this->controller;
		}

		public function getMethod() {
			return $this->method;
		}

		public function getMail() {
			return $this->mail;
		}

		public function getRequestArray() {
			$request = explode('/', $this->request->getData());
			for($i = 0; $i < 10; $i++) {
				if(!isset($request[$i]))
					$request[$i] = '';
			}	
			return $request;
		}

		public function getCliArray() {
			return $this->cli->getData();
		}

		private function bootstrap($argv) {
			// Bootstrap the framework and calcuate all values
			$this->requireFiles();
			$this->cli = new Cli($argv);
			$this->request = new Request();
			$this->post = new Post();
			$this->mail = new Mail();
		}

		private function handleRequest() {
			// We have got the url value from .htaccess, use it to find which page is to be displayed
			$details = $this->getControllerAndMethod();
			$controller_class = $details['controller'] . 'Controller';
			$view_class = $details['controller'] . 'View';
			$controller_method = $details['method'];

			// Check if the page is available in the cache
			$found_cached_page = $this->checkCache();
			if($found_cached_page == true)
				return;
			
			if(class_exists($controller_class)) {
				if(method_exists($controller_class, $controller_method)) {
					if(class_exists($view_class)) {
						if(method_exists($view_class, $controller_method)) {
							// Everything is perfect, create the controller and view classes
							$controller = new $controller_class;
							$view = new $view_class;

							// Set the generatrix value in both controller and view so that they can use the other components
							$controller->setGeneratrix($this);
							$controller->setView($view);
							$view->setGeneratrix($this);

							// Execute the controller
							$controller->$controller_method();

							$final_page = '';
							// If the page is running via CLI (Comman Line Interface) don't show the DTD
							if(!$this->cli->isEnabled() && $controller->isHtml())
								$final_page = addDTD(DTD_TYPE);
							// Create the header etc
							$view->startPage();
							// Get the final page to be displayed
							if(version_compare(PHP_VERSION, '5.2.0') >= 0) {
								$final_page .= $view->$controller_method();
							} else {
								$html_object = $view->$controller_method();
								if ( is_object ( $html_object ) ) {
									$final_page .= $html_object->_toString();
								}
							}
							echo $final_page;

							// Cache the page, if caching is turned on
							if(CACHE_PAGES && (count($_POST) == 0)) {
								$file_name = isset($_GET['url']) ? md5($_GET['url']) : md5('index');
								$file_name = '/app/cache/pages/' . $file_name . '.cac';
								$this->file->write($file_name, $final_page);
							}
						} else {
							display_error('The method <strong>"'. $controller_method . '"</strong> in class <strong>"'. $view_class .'"</strong> does not exist');
						}
					} else {
						display_error('The class <strong>"'. $view_class . '"</strong> does not exist');
					}
				} else {
					display_error('The method <strong>"'. $controller_method . '"</strong> in class <strong>"'. $controller_class .'"</strong> does not exist');
				}
			} else {
				//if(!$this->handleCatchAllRequest())
					display_error('The class <strong>"' . $controller_class .'"</strong> does not exist');
			}
		}

		// NOT USED ANYMORE
		private function handleCatchAllRequest() {
			// Catch all controller is used to override the default site.com/controller/function style of writing the URL
			//		This is required when for example you create a blog where the url is like site.com/this-is-a-post
			//		Here the controller comes directly from the database and you can't create a controller class of the same name everytime.
			if(!USE_CATCH_ALL)
				return false;
			if(class_exists('catchAllController')) {
				if(class_exists('catchAllView')) {
					// Create instances of the controller and view
					$catchAllController = new catchAllController();
					$catchAllView = new catchAllView();

					$catchAllController->setGeneratrix($this);
					$catchAllController->setView($catchAllView);
					$catchAllView->setGeneratrix($this);

					// Call the base function so that it can decide internally which controller to use
					$catchAllController->base();

					if(!$this->cli->isEnabled())
						addDTD(DTD_TYPE);
					echo $catchAllView->base();
					// TODO : Add caching for catch all controller
				} else {
					display_error('The class <strong>catchAllView</strong> does not exist');
				}
				return true;
			} else {
				return false;
			}
		}

		private function getControllerAndMethod() {
			// Parse the values obtained from the url (obtained from .htaccess) to get the controller and view
			$details = array();

			if(USE_CATCH_ALL) {
				require_once(path('/app/settings/mapping.php'));

				$request = array();
				if($this->cli->isEnabled())
					$request = $this->getCliArray();
				else
					$request = $this->getRequestArray();

				$details = mapping($request);

				if(!checkArray($details, 'controller')) {
					$details['controller'] = (isset($request[0]) && ($request[0] != '')) ? $request[0] : DEFAULT_CONTROLLER;
				}

				if(!checkArray($details, 'method')) {
					$details['method'] = (isset($request[1]) && ($request[1] != '')) ? $request[1] : 'base';
				}

				// Do not destroy the generatrix controller
				$c_id = ($this->cli->isEnabled()) ? 1 : 0;
				if(isset($request[$c_id]) && ($request[$c_id] == 'generatrix')) {
					$details['controller'] = $request[$c_id];
					$c_id++;
					if(isset($request[$c_id]) && ($request[$c_id] != '')) {
						$details['method'] = $request[$c_id];
					} else {
						$details['method'] = 'base';
					}
				}
			} else {
				// If no controller or method is defined, we need to use the DEFAULT_CONTROLLER (defined in app/settings/config.php)
				// If cli is enabled, we use the format site.com/index.php controller function
				// 		Hence we need to get the values from the arguments as $argv[0], $argv[1] etc
				if($this->cli->isEnabled()) {
					if($this->cli->getValue('controller') == "")
						location('/' . DEFAULT_CONTROLLER);
					$details['controller'] = $this->cli->getValue('controller') == "" ? DEFAULT_CONTROLLER : $this->cli->getValue('controller');
					$details['method'] = $this->cli->getValue('method') == "" ? 'base' : $this->cli->getValue('method');
				} else {
					// If this request is coming from the browser, we need to get the value from url (obtained from .htaccess)
					if($this->request->getValue('controller') == "")
						location('/' . DEFAULT_CONTROLLER);
					$details['controller'] = $this->request->getValue('controller') == "" ? DEFAULT_CONTROLLER : $this->request->getValue('controller');
					$details['method'] = $this->request->getValue('method') == "" ? 'base' : $this->request->getValue('method');
				}

				// TODO : Add customHandlers
				// We need to set the $controller and $method for the generatrix class
				$this->controller = (function_exists('customHandlers')) ? customHandlers($details, 'controller') : $details['controller'];
				$this->method = (function_exists('customHandlers')) ? customHandlers($details, 'method') : $details['method'];

				// set the controller and method again (depending on the customHandlers)
				$details['controller'] = $this->controller;
				$details['method'] = $this->method;
			}

			return $details;
		}

		private function requireFiles() {
			// Include all files in the /app/external folder (but not the ones inside sub-folders)
			$requires_directories = array('app/external');
			$core_requires = array('app/model', 'app/controllers', 'app/views');

			$all_requires = array_concat($core_requires, $requires_directories);
			foreach($all_requires as $dir) {
				$dir_handle = opendir(DISK_ROOT . $dir);
				while(false != ($file = readdir($dir_handle))) {
					if(substr($file, strlen($file) - strlen(".php") ) === ".php") {
						require_once(DISK_ROOT . $dir . '/' . $file);
					}
				}
			}

			// Also require the phpmailer control
			require_once(DISK_ROOT . 'framework/external/phpmailer/class.phpmailer.php');
		}

		public function getPost() {
			// Get all post values
			return $this->post;
		}

		public function getSession() {
			// Get the session values
			return $this->session;
		}

		public function getCookie() {
			// Get the cookie values
			if($this->cookie == NULL)
				$this->cookie = new Cookie();
			return $this->cookie;
		}

		public function getRequest() {
			return explode('/', $this->request->getData());
		}

		// Get memory footprint
		public function getMemoryFootprint($message) {
			display($message . '  Usage: ' . memory_get_usage(true) . ' Peak: ' . memory_get_peak_usage(true));
		}
	}

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
			return $this->getGeneratrix()->getPost()->getValue($tag_name);
		}

		public function getCookieValue($tag_name) {
			return $this->getGeneratrix()->getCookie()->getValue($tag_name);
		}

		public function getSessionValue($tag_name) {
			if(checkArray($_SESSION, $tag_name)) {
				return $_SESSION[$tag_name];
			}
			return false;
		}

    public function getGetValue($tag_name) {
			if(checkArray($_GET, $tag_name)) {
        return $_GET[$tag_name];
      }
      return false;
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

	//
	// The base class for all views
	//

	class View {
		private $generatrix;
		private $variables;

		private $head;
		private $body;

		private $control_name;
		private $control_options;

		private $added_generated_css;

		public function __construct() {
			$this->added_generated_css = false;
		}

		// Get a variable from the controller
		public function get($var_name) {
			if($this->hasVariable($var_name)) {
				return $this->variables[$var_name];
			} else {
				$bt = bt();
				display_error('The variable <strong>"' . $var_name . '"</strong> is not available in <strong>' . $bt['file'] . ' [ Line ' . $bt['line'] . ']</strong>');
			}
		}

		// Set a variable from the controller
		public function set($var_name, $var_value) {
			$this->variables[$var_name] = $var_value;
		}

		// Check if a variable is defined
		public function hasVariable($var_name) {
			if(isset($this->variables[$var_name]))
				return true;
			return false;
		}

		// Check if the variable has a value
		public function hasValue($var_name) {
			if(isset($this->variables[$var_name]) && ($this->variables[$var_name] != ''))
				return true;
			return false;
		}

		public function getGeneratrix() {
			return $this->generatrix;
		}

		public function setGeneratrix($generatrix) {
			$this->generatrix = $generatrix;
		}

		// Get the <head> object
		public function getHead() {
			return $this->head;
		}

		// Set the <head> object
		public function setHead($head) {
			$this->head = $head;
			return $this;
		}

		// Get the <body> object
		public function getBody() {
			return $this->body;
		}

		// Set the <body> object
		public function setBody($body) {
			$this->body = $body;
			return $this;
		}

		// Start the page, create <head> and <body> elements
		public function startPage() {
			$this->setHead(new Head());
			$this->setBody(new Body());
		}

		// End the page, close the <head> and <body> tags and add them to <html>
		public function endPage() {
			$html = new Html();
			$html->appendContent($this->getHead());
			$html->appendContent($this->getBody());
			return $html;
		}

		// Get the post value
		public function getPostValue($tag_name) {
			return $this->getGeneratrix()->getPost()->getValue($tag_name);
		}

		// Get the cookie value
		public function getCookieValue($tag_name) {
			return $this->getGeneratrix()->getCookie()->getValue($tag_name);
		}

		// Get the cookie value
		public function getSessionValue($tag_name) {
			return $this->getGeneratrix()->getSession()->getValue($tag_name);
		}

		// Add a CSS file <head>, check for IE condition
		private function addCss($file, $is_IE = false) {
			if(!file_exists(path($file))) {
				display_error('The file <strong>' . path($file) . '</strong> does not exist');
				return;
			}
			if($is_IE) {
				return '
					<!--[if IE]>
						<link rel="stylesheet" href="' . href($file) . '" type="text/css" media="screen, projection">
					<![endif]-->
				';
			}
			return '<link media="screen, projection" type="text/css" href="' . href($file) . '" rel="stylesheet" />';
		}

		private function loadGenerated() {
			/*
			ob_start();
			require_once(path('/public/style/generated.php'));
			$generated_css = ob_get_contents();
			ob_end_clean();
			return "<style type='text/css'>" . $generated_css . "</style>";
			*/
			$content = "";
			$content .= "<link media='screen' type='text/css' href='" . href('/public/style/generated.phpx') . "' rel='stylesheet' />";
			return $content;
		}

		// Add a javscript file to <head>
		private function addJavascript($file) {
			if(file_exists(path($file)))
				return '<script type="text/javascript" src="' . href($file) . '"></script>';
			else
				display_system('The file <strong>' . path($file) . '</strong> does not exist');
		}

		// Add the generated css
		private function addGeneratedCss() {
			if(!$this->added_generated_css) {
				$head = $this->getHead();
				$head->appendContent(
					$this->addCss('/public/style/generatrix.css') .
					$this->loadGenerated() .
					$this->addCss('/public/style/generatrix-ie.css', true)
				);
				$this->setHead($head);
				$this->added_generated_css = true;
			}
		}

		// Add the GOOGLE Ajax Libraries
		private function addGoogleAjaxLibraries() {
			$content = '';
			//$content = '<script type="text/javascript" src="' . href('/public/javascript/jquery-1.3.2.min.js') . '"></script>';	
			$content .= "
				<script type='text/javascript'>
					var Generatrix = {
						basepath: '" . href('') . "',
						href: function(path) { return this.basepath + path; },
						loading: function(where) { $(where).html(\"<img src='\" + this.href('/images/gears.gif') + \"' />\"); },
						timestamp: function() { var d = new Date(); return d.getTime() / 1000; },
						rand: function(max) { return Math.ceil(Math.random() * max); }
					};
				</script>
			";
			$this->getHead()->appendContent($content);
			//return;

			$content = '<script type="text/javascript" src="http://www.google.com/jsapi"></script>';
			$content .= '<script type="text/javascript">';

			if(JS_JQUERY != '') $content .= 'google.load("jquery", "' . JS_JQUERY . '");';
			if(JS_JQUERYUI != '') $content .= 'google.load("jquery", "' . JS_JQUERYUI . '");';
			if(JS_PROTOTYPE != '') $content .= 'google.load("jquery", "' . JS_PROTOTYPE . '");';
			if(JS_SCRIPTACULOUS != '') $content .= 'google.load("jquery", "' . JS_SCRIPTACULOUS . '");';
			if(JS_MOOTOOLS != '') $content .= 'google.load("jquery", "' . JS_MOOTOOLS . '");';
			if(JS_DOJO != '') $content .= 'google.load("jquery", "' . JS_DOJO . '");';
			if(JS_SWFOBJECT != '') $content .= 'google.load("jquery", "' . JS_SWFOBJECT . '");';
			if(JS_YUI != '') $content .= 'google.load("jquery", "' . JS_YUI . '");';
			if(JS_EXT_CORE != '') $content .= 'google.load("jquery", "' . JS_EXT_CORE . '");';

			$content .= '</script>';
			$this->getHead()->appendContent($content);
		}

		// Add the libraries
		private function addLibraries() {
			$this->addGeneratedCss();
			$this->addGoogleAjaxLibraries();
		}

		// Public function which adds stuff to the <head> from the view
		public function add($list) {
			$this->addLibraries();
			$head = $this->getHead();
			$files = (array) json_decode($list, true);

			if(checkArray($files, 'css')) {
				foreach($files['css'] as $file) {
					$head->appendContent($this->addCss($file));
				}
			}

			if(checkArray($files, 'js')) {
				foreach($files['js'] as $file) {
					$head->appendContent($this->addJavascript($file));
				}
			}

			$this->setHead($head);
		}

		// Public function to set the title for a page
		public function title($title) {
			$head = $this->getHead();
			$head->appendContent('<title>' . $title . '</title>');
			$this->setHead($head);
		}

		// The function loads the sub views with html in them
		public function loadSubView($sub_view) {
			ob_start();
			include(path('/app/subviews/' . $sub_view . '.php'));
			$data = ob_get_contents();
			ob_end_clean();
			return $data;
		}

		// This function loads the control from the framework directory
		public function loadControl($control_name, $control_options) {
			$this->control_name = $control_name;
			$this->control_options = $control_options;

			ob_start();
			include(path('/framework/controls/' . $control_name . '/' . $control_name . '.php'));
			$data = ob_get_contents();
			ob_end_clean();

			$this->control_name = '';
			$this->control_options = array();

			return $data;
		}
	}

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

		public function __construct() {
			// If the debugger is one, show the query time on the screen
			$this->debug = false;
			if(DEBUG_QUERIES)
				$this->debug = true;
			$this->file = new File();
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
					$this->errors[] = $error;
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
			// If caching results, check if the result is given in the database
      if(CACHE_DB) {
				// All cache files are located in /app/cache/database
				// All cache files have the extension .cac
        $file_name = '/app/cache/database/' . md5($sql) . '.cac';
        if(file_exists(path($file_name))) {
					// Check if the file was created in the time duration
          if((time() - filemtime(path($file_name))) > CACHE_DB_TIME) {
						$results = $this->createArray($query_type, mysql_query($sql));
						// Serialize results and write to a file
            $this->file->write($file_name, serialize($results));
            display('File was old, updating it');
          } else {
						// Get the results from a file and unserialize them
            $results = unserialize($this->file->read($file_name));
            $cached_query = true;
            display('Found the right file, showing data from file');
          }
        } else {
					// If the results are not in the cahce, create an entry
					$results = $this->createArray($query_type, mysql_query($sql));
          $this->file->write($file_name, serialize($results));
          display('File did not exist, creating it');
        }
      } else {
				$results = $this->createArray($query_type, mysql_query($sql));
      }

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

	class Model {

		public $database;

		public $name;
		public $columns;
		public $columns_name;

		public function __construct() {
			$this->columns = array();
			$this->columns_name = array();
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
							$vals[] = $this->escapeString($value);
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
			$sql = 'SELECT ' . $columns . ' FROM ' . $this->name . ' ' . $condition;
			return $this->database->query($sql);
		}

		public function update($columns, $condition = '') {
			$updates = array();
			foreach($columns as $key => $value) {
				$updates[] = ' `' . $key . '` = "' . $value . '" ';
			}
			$sql = 'UPDATE ' . $this->name . ' SET ' . implode(', ', $updates) . ' ' . $condition;
			return $this->database->query($sql);
		}

	}

	//
	// This is the base class for each HTML element
	//

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

	//
	// This class is an associative list, used with the comman line interface
	//

	class Cli extends AssocList {
		public function __construct($argv) {
			$this->setData($argv);
			$this->processParameters();
		}

		// Process the data array to create the key/value pair structure
		private function processParameters() {
			$value = $this->getData('data');
			for($i = 0; $i < count($this->getData('data')); $i++) {
				if(isset($value[$i])) {
					if($i == 0)
						$this->addParameter('file', $value[$i]);
					else if($i == 1)
						$this->addParameter('controller', $value[$i]);
					else if($i == 2)
						$this->addParameter('method', $value[$i]);
					else
						$this->addParameter('p' . ($i - 3), $value[$i]);
				}
			}
			// If $_SERVER varaible is not set, the request is coming via CLI
			if(!isset($_SERVER['HTTP_USER_AGENT']))
				$this->addParameter('enabled', true);
		}

	}


	// This is the base class for cookies. It turns the cookie varaible in an assoc list so that you can use the AssocList functions

	class Cookie extends AssocList {
		public function __construct() {
			$cookie = isset($_COOKIE) ? $_COOKIE: '';
			$this->setData($cookie);
			$this->processParameters();
		}

		private function processParameters() {
			$tags = array_keys($this->getData());
			foreach($tags as $tag) {
				$this->addParameter($tag, $_COOKIE[$tag]);
			}
		}

		// Add a cookie
		public function add($tag, $value, $path = '/') {
			if(setcookie($tag, $value, time() + (30 * 24 * 3600), $path)) {
				$this->addParameter($tag, $value);
				return $this;
			}
			return false;
		}

		// Delete a cookie
		public function delete($tag, $path = '/') {
			if(setcookie($tag, '', time() - (30 * 24 * 3600), $path)) {
				$this->removeParameter($tag);
				return $this;
			}
			return false;
		}
	}

	// This class creates an associative list for $_POST, so that you can use specific functions

	class Post extends AssocList {
		public function __construct() {
			$post = isset($_POST) ? $_POST : '';
			$this->setData($post);
			$this->processParameters();
		}

		private function processParameters() {
			$tags = array_keys($this->getData());
			foreach($tags as $tag) {
				$this->addParameter($tag, $_POST[$tag]);
			}
		}
	}

	// A class to handle the request

	class Request extends AssocList {

		public function __construct() {

			$url = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';
			$this->setData($url);
			$this->processParameters();
		}

		private function processParameters() {
			$split_array = explode("/", $this->getData());
			for($i = 0; $i < count($split_array); $i++) {
				$value = $split_array[$i];
				if($i == 0)
					$this->addParameter('controller', $value);
				else if($i == 1)
					$this->addParameter('method', $value);
				else
					$this->addParameter('p' . ($i - 2), $value);
			}
			if($this->getValue('controller') != "")
				$this->addParameter('enabled', true);
		}

	}



	class Session extends AssocList {
		public function __construct() {
			$session = isset($_SESSION) ? $_SESSION : '';
			$this->setData($session);
			$this->processParameters();
		}

		private function processParameters() {
			$tags = array_keys($this->getData());
			foreach($tags as $tag) {
				$this->addParameter($tag, $_SESSION[$tag]);
			}
		}

		public function add($tag, $value) {
			$_SESSION[$tag] = $value;
			$this->addParameter($tag, $value);
			return $this;
		}

		public function remove($tag) {
			unset($_SESSION[$tag]);
			$this->removeParameter($tag);
			return $this;
		}
	}


	// This class uses phpmailer to send out emails, one at a time from the app

	class Mail {
		private $mailer;
		private $application_name;
		private $application_email;

		public function __construct() {
			// Create an instance of the PHPMailer
			$this->mailer = new PHPMailer();
			$this->setApplicationName(APPLICATION_NAME);
			$this->setApplicationEmail(APPLICATION_EMAIL);
		}

		public function setApplicationName($app_name) {
			// Define an applicatin name in /app/settings/config.php
			$this->application_name = $app_name;
			return $this;
		}

		public function getApplicationName() {
			return $this->application_name;
		}

		public function setApplicationEmail($app_email) {
			// Define an application email in /app/settings/config.php
			$this->application_email = $app_email;
			return $this;
		}

		public function getApplicationEmail() {
			return $this->application_email;
		}

		// Sendmail function, use this to send outgoing emails
		public function sendmail($to_name = '.', $to_email = '.', $from_name = '.', $from_email = '.', $subject, $body) {
			if($to_name == '') $to_name = $this->application_name;
			if($to_email == '') $to_email = $this->application_email;
			if($from_name == '') $from_name = $this->application_name;
			if($from_email == '') $from_email = $this->application_email;

			if(!$subject) {
				display_system("The subject has not been set");
			} else if(!$body) {
				display_system("The body has not been set");
			} else {
				$this->mailer->From = $from_email;
				$this->mailer->FromName = $from_name;
				$this->mailer->AddAddress($to_email, $to_name);
				$this->mailer->AddCC($from_email, $from_name);
				$this->mailer->IsHTML(true);

				$this->mailer->Subject = $subject;
				$this->mailer->Body = $body;

				if(!$this->mailer->Send()) {
					display_error("The email from " . $from_name. " (" . $from_email. ") to " . $to_name. " (" . $to_email. ") could not be sent. The mailer replied with the following error :: " . $this->mailer->ErrorInfo . ".<br />The contents of the email were as follows :<br /><b>" . $subject . "</b><br />" . $body . "");
					return false;
				}

				// Need to do this, otherwise recipients will keep adding up
				$this->mailer->ClearAllRecipients();
				return true;
			}
		}
	}

	//
	// This class helps you read and write on a file
	//

	class File {

		// Write some data to a file, the permissions are set to w+, can change if you like
		public function write($file_name, $data, $file_permission = "w+") {
			// Open the path and prepare to write
			if($fp = fopen(path($file_name), $file_permission)) {
				fwrite($fp, $data);
				fclose($fp);
				return true;
			} else {
				display_error('The file <strong>' . path($file_name) . '</strong> could not be opened');
				return false;
			}
		}

		// Read data from a file, line by line, just mention the path and get started
		public function read($file_name) {
			if(file_exists(path($file_name))) {
				if($fp = fopen(path($file_name), "r")) {
					$data = '';
					while(!feof($fp)) {
						$data .= fgets($fp);
					}
					fclose($fp);
					return $data;
				} else {
					display_error('The file <strong>' . path($file_name) . '</strong> could not be opened');
					return false;
				}
			} else {
				display_error('The file <strong>' . path($file_name) . '</strong> does not exist');
				return false;
			}
		}

	}

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
			$this->setUserCookie('/app/cache/cookies/curl-cookie.txt');
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
				if(perms('/app/cache/cookies/') != '777') {
					display_system('The path to the file ' . path($path) . ' is not writable.<br />Please enter chmod -R 777 ' . path('/app/cache/cookies/') . ' on your terminal to fix this.');
				} else {
					$file->write($path, ' ');
					chmod(path($path), 0755);
				}
			}
			$this->user_cookie = path($path);
		}
	}


	//
	// The Generatrix Controller for utils
	//

	class generatrixController extends Controller {
		private function isCli() {
			$cli_array = $this->getGeneratrix()->getCliArray();
			return (isset($cli_array[0])) ? true : false;
		}

		public function base() { } 

		public function help() { } 

		public function addPage() {
			if(!$this->isCli())
				return;

			// Get the name of the required page
			$cli_array = $this->getGeneratrix()->getCliArray();
			if(isset($cli_array[3])) {

				$page_name = $cli_array[3];

				$not_allowed = array('generatrix');

				// Check if a controller or view of the page exists
				if(
					!in_array($page_name, $not_allowed) &&
					!file_exists(path('/app/controllers/' . $page_name . 'Controller.php')) &&
					!file_exists(path('/app/views/' . $page_name . 'View.php'))
				) {

					// Write the data
					$file = new File();
					$file->write(
						'/app/controllers/' . $page_name . 'Controller.php',
						str_replace('___PAGE_NAME___', $page_name, $file->read('/framework/data/addpage_new_controller.data'))
					);
					$file->write(
						'/app/views/' . $page_name . 'View.php',
						str_replace('___PAGE_NAME___', $page_name, $file->read('/framework/data/addpage_new_view.data'))
					);
					display('SUCCESSFULLY Added a controller and a view');
				} else {
					display_error('FAILED : A file with the view or controller by the same name already exists');
				}
			} else {
				display_error('FAILED : Please specify the page name as "php index.php generatrix addPage the-page-name"');
			}	
		}

		public function prepareModel() {
			if(!$this->isCli())
				return;

			$db = $this->getDb();

			if(file_exists(path('/app/model/databases.php'))) {
				display_error('FAILED : The databases file already exists, please delete it by running rm ' . path('/app/model/databases.php') . ' and run this query again to recreate the file');
				return;
			}
			
			$model_class_start = '
  class ___TABLE_CLASS_NAME___ extends Model {
    public function __construct($database) {
      $this->construct($database, "___TABLE_NAME___", array(' . "\n";
			$model_class_end = '
      ));
    }
  }' . "\n";

			$file_content = '';

			$tables = $db->query('SHOW TABLES');
			foreach($tables as $table) {
				$table_name = current($table);

				$class_start = str_replace('___TABLE_NAME___', $table_name, $model_class_start);
				$class_start = str_replace('___TABLE_CLASS_NAME___', str_replace(DATABASE_PREFIX, '', $table_name), $class_start);
				
				$file_content .= $class_start;

				$columns = $db->query('DESCRIBE ' . $table_name);
				$column_table = array();
				foreach($columns as $column) {
					$type = str_replace('(', '_', str_replace(')', '', $column['Type']));
					$spaces = explode(' ', $type);
					$type = $spaces[0];
					$underscores = explode('_', $type);
					switch($underscores[0]) {
						case 'int':
							$type = 'int';
							break;
						case 'varchar':
						default:
							break;
					}
					$column_table[] = '				"' . $column['Field'] . '" => "' . $type . '"';
				}
				$file_content .= implode(",\n", $column_table);

				$file_content .= $model_class_end;
			}

			$file_content = "<?php\n" . $file_content . "\n" . '?' . '>';
			$file = new File();
			$file->write(
				'/app/model/databases.php',
				$file_content
			);
		}

		public function exportDb() {
			if(!$this->isCli())
				return;

			exec('mysqldump ' . DATABASE_NAME . ' -u' . DATABASE_USER . ' -p', $output);
			$dump = implode("\n", $output);

			if(trim($dump) != '') {
				$file = new File();
				$file->write(
					'/app/model/' . DATABASE_NAME . '.sql',
					$dump
				);
			} else {
				display_error('FAILD : Could not connect to database server');
			}
		}
	}

	//
	// The Generatrix View for utils
	//

	class generatrixView extends View {
		public function base() {
			display("
-----------------------------------------------------------------------------------------------------------------------
Welcome to the Generatrix help. You can use any of the following options
-----------------------------------------------------------------------------------------------------------------------
1. ./generatrix                          (to show this help screen)
2. ./generatrix help                     (to show this help screen)
3. ./generatrix addPage test             (to add a new controller testController and view testView with base functions)
4. ./generatrix prepareModel             (to create the model file for use)
5. ./generatrix exportDb                 (to export the complete database)
-----------------------------------------------------------------------------------------------------------------------
			");
		}
		public function help() { $this->base(); } 
		public function addPage() { }
		public function prepareModel() { }
		public function exportDb() { }
	}


	// This is where the procedural part begins. This isn't a static class becuase you need to use too many words to write to everytime.


	session_start();
	prepare_config();

	// Use this to catch all errors other than 'Fatal' and display them in a box above the screen
	function handle_errors($errlevel, $errstr, $errfile = '', $errline = '', $errcontext = '') {
		$message = htmlentities($errstr) . " [ On <strong>" . $errfile . "</strong> Line " . $errline . " ]";
		if(($errlevel == E_WARNING) && (DEBUG_VALUES)) {
			display_warning($message);
		} else {
			display_error($message);
		}
	}

	// Display an error message properly. CSS has to be defined inline because we're not sure if the page has started yet
	function display($message, $file = '', $line = '') { display_message($message, $file, $line, 'normal', true); }
	function display_warning($message, $file = '', $line = '') { display_message($message, $file, $line, 'warning'); } 
	function display_error($message, $file = '', $line = '') { display_message($message, $file, $line, 'error'); } 
	function display_system($message, $file = '', $line = '') { display_message('SYSTEM: ' . $message, $file, $line, 'system'); }

	function add_file_and_line($file, $line) {
		$return = (($file != '') || ($line != '')) ? '<br />In file <strong>'. str_replace(DISK_ROOT, '', $file) . '</strong> on line <strong>' . $line . '</strong>' : '';
		return $return;
	}

	// This displays an error in php, shows up in red
	function display_message($message, $file, $line, $level, $dump = false) {
		$using_cli = (isset($_SERVER['HTTP_USER_AGENT'])) ? false : true;

		display_message_start($using_cli, $level);
		display_message_echo($message, $dump, $file, $line);
		display_message_end($using_cli, $level);
	}

	function display_message_echo($message, $dump, $file, $line) {
		if($dump && is_array($message)) {
			var_dump($message);
			echo add_file_and_line($file, $line, true);
		} else {
			echo $message . add_file_and_line($file, $line);
		}
	}

	// Helper functions for display_error
	function display_message_start($using_cli, $level) {
		$background_color = '';
		$tag = '';
		switch($level) {
			case 'error': $background_color = '#D02733'; $tag = 'div'; break;
			case 'warning': $background_color = '#FF8110'; $tag = 'div'; break;
			case 'system': $background_color = '#000000'; $tag = 'div'; break;
			case 'normal': $background_color = '#000000'; $tag = 'pre'; break;
			default: $background_color = '#F2F2F2'; break;
		}
		$output = $using_cli ? '' : "<" . $tag . " style='" . create_css($background_color) . "'>";
		echo $output;
	}

	// Helper function for display_error
	function display_message_end($using_cli, $level) {
		$tag = '';
		switch($level) {
			case 'normal': $tag = 'pre'; break;
			default: $tag = 'div'; break;
		}
		$output = $using_cli ? "\n\n" : "</" . $tag . ">";
		echo $output;
	}

	function create_css($background_color) {
		$css = array(
			'margin' => '2px',
			'padding' => '8px 16px',
			'font-size' => '12px',
			'background-color' => $background_color,
			'border' => '1px solid #444444',
			'color' => '#FFFFFF',
			'font-family' => 'arial, sans-serif',
			'text-align' => 'left'
		);
		$string = '';
		foreach($css as $key => $value) {
			$string .= $key . ': ' . $value . ';';
		}
		return $string;
	}


	// Helps to add two arrays
	function array_concat($first_array, $second_array) {
		$final_array = $first_array;
		foreach($second_array as $row) {
			$final_array[] = $row;
		}
		return $final_array;
	}

	// Adds a DTD to the page by default
	function addDTD($type = null) {
		switch($type) {
			case 'strict':
				echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
			default:
				echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
		}
	}

	// Creates a link based on the APPLICATION PATH defined in /app/settings/config.json
	// USAGE : href('/app/views/homeView.php');
	function href($path) {
		$relative_root = '';
		$slashes = explode('/', APPLICATION_ROOT);
		for($i = 0; $i < count($slashes); $i++) {
			if(($i > 2) && (isset($slashes[$i])) && ($slashes[$i] != '')) {
				$relative_root .= ( '/' . $slashes[$i]);
			}
		}
		return $relative_root . $path;
	}
	
	// Use timthumb to create smaller iamges
	function image($path, $width = '100', $height = '100') {
		if(check($path)) {
			return href('/framework/external/timthumb/timthumb.php?src=' . href($path) . '&w=' . $width . '&h=' . $height);
		} else {
			display_error('The image ' . href($path) . ' was not found');
		}
	}

	// Checks the file permission for a file inside generatrix
	function perms($path) {
		if(file_exists(path($path))) {
			return substr(sprintf('%o', fileperms(path($path))), -3);
		} else {
			display_error('You are trying to edit the permissions, but the <strong>file ' . path($path) . ' does not exist</strong>');
			return false;
		}
	}

	// Returns the full path of the file (use the property defined in /app/settings/config.json
	// USAGE : path('/app/views/homeView.php');
	function path($path) {
		$relative_root = substr(DISK_ROOT, 0, strlen(DISK_ROOT) - 1);
		return $relative_root . $path;
	}

	// check if a value has been set and is not null
	function check($value) {
		if(isset($value) && ($value != ''))
			return true;
		return false;
	}

	// Check if a value inside an array isset and is not null
	function checkArray($array, $value) {
		if(is_array($array) && isset($array[$value]) && ($array[$value] != ''))
			return true;
		return false;
	}

	// Create json object
	function json($data) {
		header('Content-Type: application/json');
		return json_encode($data);
	}

	// Redirect to a particular path
	// USAGE : location('/user/forgotpass');
	function location($path) {
		$file_name = '';
		$line_number = '';
		if(!headers_sent($file_name, $line_number)) {
			header("Location: " . href($path));
			exit();
		} else {
			display_system('Cannot redirect the page to <strong>' . href($path) . '</strong> because headers have already been sent. The headers were started by <strong>' . $file_name. ' [ Line Number : '. $line_number . ']</strong>');
		}
	}

	// Read the json in the config and create defines (eg. 'time-zone' in config creates define('TIME_ZONE', 'value')
	function prepare_config() {

		$complete_config = array();

		// Find out where the file is. path() will not work yet because config is not loaded
		// Load the default values from config.json.defaults
		require_once(DISK_ROOT . 'framework/external/json/json.php');
		$config_defaults_file = DISK_ROOT . 'app/settings/config.json.defaults';
		$config_defaults = (array) json_decode(file_get_contents($config_defaults_file));

		// Foreach value store them in an array
		foreach($config_defaults as $key => $value) {
			$define_element_key = str_replace("-", "_", strtoupper($key));
			$define_element_value = $value;

			if($value == "true")
				$define_element_value = 1;
			if($value == "false")
				$define_element_value = 0;

			$complete_config[$define_element_key] = $define_element_value;
		}

		// Read the user config file
		$config_file = DISK_ROOT . 'app/settings/config.json';

		if(file_exists($config_file)) {
			$config = (array) json_decode(file_get_contents($config_file));

			foreach($config as $key => $value) {
				$define_element_key = str_replace("-", "_", strtoupper($key));
				$define_element_value = $value;

				if($value == "true")
					$define_element_value = 1;
				if($value == "false")
					$define_element_value = 0;

				$complete_config[$define_element_key] = $define_element_value;
			}
		} else {
			display_system("You have not created a <strong>config file</strong> yet. You can fix that by going to the generatrix folder and typing <strong>cp " . path('/app/settings/config.json.defaults') . " " . path('/app/settings/config.json') . "</strong>");
		}

		// Create Macros (define(SOMETHING, VALUE);)
		foreach($complete_config as $key => $value) {
			define($key, $value);
		}

		checkDefaults();

		// Set the default time zone by using the config value 'time-zone'
		date_default_timezone_set(TIME_ZONE);
	}

	function checkDefaults() {
		checkMinimumPHPVersion();
	}

	function checkMinimumPHPVersion() {
		$status = false;
		if(function_exists('version_compare') && version_compare(phpversion(), MIN_PHP_VERSION, '>=')) {
		} else {
			// Show an error
			display_system("The <strong>version of PHP</strong> (" . phpversion() . ") is not greater than the minimum version supported [" . MIN_PHP_VERSION . "]");
		}
	}

	function bt() {
		$bt = debug_backtrace();
		$output = array(
			'file' => $bt[0]['file'],
			'line' => $bt[0]['line']
		);
		return $output;
	}

	function ut($variable) {
		return urlencode(trim($variable));
	}

?>
