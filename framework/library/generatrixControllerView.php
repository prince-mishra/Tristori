<?php

	//
	// The Generatrix Controller for utils
	//

	require_once(DISK_ROOT . 'framework/library/controller.php');

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

			exec('mysqldump ' . DATABASE_NAME . ' -u' . DATABASE_USER . ' -p' . DATABASE_PASS . ' > ' . path('/app/model/' . DATABASE_NAME . '.sql'), $output);
			$dump = implode("\n", $output);
			display($dump);
		}

		public function importDb() {
			if(!$this->isCli())
				return;

			exec('mysql -u' . DATABASE_USER . ' -p' . DATABASE_PASS . ' ' . DATABASE_NAME . ' < ' . path('/app/model/' . DATABASE_NAME . '.sql'), $output);
			$dump = implode("\n", $output);
			display($dump);
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
6. ./generatrix importDb								 (to import the exported database)
-----------------------------------------------------------------------------------------------------------------------
			");
		}
		public function help() { $this->base(); } 
		public function addPage() { }
		public function prepareModel() { }
		public function exportDb() { }
		public function importDb() { }
	}

?>
