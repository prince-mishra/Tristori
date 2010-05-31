<?php

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
	function display_404 ($message, $file = '', $line = '') { 
		header("HTTP/1.1 404 Not Found");
		display_message($message, $file, $line, 'error');  
	}

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


	// Adds a DTD to the page by default
	function addDTD($type = null) {
		switch($type) {
			case 'strict':
				echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n";
			default:
				echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
		}
	}

	// Read the json in the config and create defines (eg. 'time-zone' in config creates define('TIME_ZONE', 'value')
	function prepare_config() {

		$complete_config = array();

		// Find out where the file is. path() will not work yet because config is not loaded
		// Load the default values from config.json.defaults
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

?>
