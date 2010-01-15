<?php

	/*
		Inside a view, you can do the following (inside any function declared as public function funcName() {} )
		1. ADDING CSS AND JAVASCRIPT TO THE PAGE <head> TAG
				You can add css and js in a JSON array
				The names of the files should be relative to the root folder of generatrix (the folder which contains the "app" and "framework" folders)
					(Tip : A leading / is required in the path)
				$this->add('
					"css" : [ "/public/style/batman.css", "/public/style/watchmen.css" ],
					"js" : [ "/public/javascript/batman.js", "/public/javascript/watchmen.js" ]
				');
				or
				$this->add(\'\');

		2. TO ADD TAGS TO THE <head> TAG
				$content_to_add = "<meta name=\'superhero\' value=\'spiderman\' />";
				$head = $this->getHead();
				$head->appendContent($content_to_add);

		3. TO LOAD A SUB VIEW
				$content = $this->loadSubView("home-body");
				This will load the subview located in app/subviews/home-body.php
					(Tip : .php is automatically appended)

		4. TO ADD CONTENT TO THE <body> TAG
				$body = $this->getBody();
				$content = "Hello how are you doing?";
				$body->appendContent($content);

		5. TO CLOSE THE PAGE (REQUIRED)
				return $this->endPage();
	*/

	class adminView extends View {

		public function base() {

		}

		public function dashboard() {
			$this->title('Generatrix | Admin | Dashboard');
			$this->add('{
				"css" : [ "/public/style/admin.css" ],
				"js"  : []
			}');

			$content = '';
			$content .= $this->loadSubView('generatrix-admin-line');
			$content .= $this->loadSubView('generatrix-admin-header');

			$action = $this->get('action');
			switch($action) {
				case 'view':
					$content .= $this->loadSubView('generatrix-admin-sidebar');
					$content .= $this->loadSubView('generatrix-admin-display');
					break;
				case 'edit':
					$content .= $this->loadSubView('generatrix-admin-sidebar');
					$content .= $this->loadSubView('generatrix-admin-edit');
					break;
				case 'add':
					$content .= $this->loadSubView('generatrix-admin-sidebar');
					$content .= $this->loadSubView('generatrix-admin-add');
					break;
				default:
					break;
			}

			$content .= $this->loadSubView('generatrix-admin-footer');

			$this->getBody()->appendContent($content);
			return $this->endPage();
		}

		public function login() {
			$this->title('Generatrix | Admin | Login');
			$this->add('{
				"css": [ "/public/style/admin.css" ],
				"js" : []
			}');

			$content = '';
			$content .= $this->loadSubView('generatrix-admin-line');
			$content .= $this->loadSubView('generatrix-admin-login');

			$this->getBody()->appendContent($content);
			return $this->endPage();
		}

		public function logout() {

		}

	}

?>
