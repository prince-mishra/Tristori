<?php

	// Example
	//   URL = /this-is-my-tag/generatrix-can-now-reorder-urls/comments
	//	 Here we need to go to blogController and call the function comments
	//	 URL2 = /admin/comments/all
	//	 Here we need to go to admincommentsController and call the function all
	//
	// PLEASE SET $output['controller'] and $output['method']

	function mapping($request) {
		$output = array();

		if(($request[0] == 'admin') && ($request[1] == 'gallery')) {
			$output['controller'] = $request[2];
			$output['method'] = $request[3];
		}

		return $output;
	}

?>
