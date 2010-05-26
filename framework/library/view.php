<?php

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
			if(JS_JQUERY != '') {
				$content .=  '<script type="text/javascript" src="' . href('/public/javascript/jquery-' . JS_JQUERY . '.min.js') . '"></script>';	
			}

			if(JS_JQUERY_OFFLINE == '1') {
				$content .= '<script type="text/javascript" src="' . href('/public/javascript/json.js') . '"></script>';
				$content .= '<script type="text/javascript" src="' . href('/public/javascript/jquery.offline.js') . '"></script>';
			}

			if(JS_COOKIE == '1') {
				$content .= '<script type="text/javascript" src="' . href('/public/javascript/jquery.cookie.min.js') . '"></script>';
			}

			$content .= "
				<script type='text/javascript'>
					var Generatrix = {
						basepath: '" . href('') . "',
						href: function(path) { return this.basepath + path; },
						loading: function(where) { $(where).html(\"<img src='\" + this.href('/images/gears.gif') + \"' />\"); },
						timestamp: function() { var d = new Date(); return d.getTime() / 1000; },
						rand: function(max) { return Math.ceil(Math.random() * max); }
					};
					String.prototype.trim = function() {
						return this.replace(/^\s*/, \"\").replace(/\s*$/, \"\");
					};
				</script>
			";
			$this->getHead()->appendContent($content);
			//return;

			$loadGoogle = false;
			$content = '<script type="text/javascript" src="http://www.google.com/jsapi"></script>';
			if(JS_COOKIE) {
				$content .= '<script type="text/javascript" src="' . href('/public/javascript/jquery.cookie.min.js') . '"></script>';
			}
			$content .= '<script type="text/javascript">';

			if( (JS_JQUERYUI != '') || (JS_PROTOTYPE != '') || (JS_SCRIPTACULOUS != '') || (JS_MOOTOOLS != '') || (JS_DOJO != '') || (JS_SWFOBJECT != '') || (JS_YUI != '') || (JS_EXT_CORE != '') ) {
				$loadGoogle = true;
			}

			//if(JS_JQUERY != '') $content .= 'google.load("jquery", "' . JS_JQUERY . '");';
			if(JS_JQUERYUI != '') $content .= 'google.load("jquery", "' . JS_JQUERYUI . '");';
			if(JS_PROTOTYPE != '') $content .= 'google.load("jquery", "' . JS_PROTOTYPE . '");';
			if(JS_SCRIPTACULOUS != '') $content .= 'google.load("jquery", "' . JS_SCRIPTACULOUS . '");';
			if(JS_MOOTOOLS != '') $content .= 'google.load("jquery", "' . JS_MOOTOOLS . '");';
			if(JS_DOJO != '') $content .= 'google.load("jquery", "' . JS_DOJO . '");';
			if(JS_SWFOBJECT != '') $content .= 'google.load("jquery", "' . JS_SWFOBJECT . '");';
			if(JS_YUI != '') $content .= 'google.load("jquery", "' . JS_YUI . '");';
			if(JS_EXT_CORE != '') $content .= 'google.load("jquery", "' . JS_EXT_CORE . '");';

			$content .= '</script>';
			if($loadGoogle) {
				$this->getHead()->appendContent($content);
			}
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

?>
