<?php
	class FormHTML {

		public $html;

		public $values_count;
		public $upload_count;

		public $will_upload_file;
		public $will_add_datepicker;

		public $table;
		public $action;
		public $form_name;
		public $fields;

		public $form_action;
		public $form_method;
		public $form_class;
		public $form_label_class;
		public $form_value_class;
		public $values;
		public $ignore;
		public $select;

		public function __construct($options) {
			$this->html = '';
			$this->will_upload_file = false;
			$this->will_add_datepicker= false;

  		$this->table = checkArray($options, 'table') ? $options['table'] : false;
  		$this->action = checkArray($options, 'action') ? $options['action'] : false;
  		$this->form_name = $this->table . '-' . $this->action;
  		$this->fields = checkArray($options, 'fields') ? $options['fields'] : false;

  		$this->form_action = checkArray($options, 'form-action') ? $options['form-action'] : '';
  		$this->form_method = checkArray($options, 'form-method') ? $options['form-method'] : 'post';
  		$this->form_class = checkArray($options, 'form-class') ? $options['form-class'] : 'generatrix-control-form-main';
  		$this->form_label_class = checkArray($options, 'form-label-class') ? $options['form-label-class'] : 'generatrix-control-form-label';
  		$this->form_value_class = checkArray($options, 'form-value-class') ? $options['form-value-class'] : 'generatrix-control-form-value';
  		$this->values = checkArray($options, 'values') ? $options['values'] : array();
			$this->ignore = checkArray($options, 'ignore') ? $options['ignore'] : array();
			$this->select = checkArray($options, 'select') ? $options['select'] : array();
		}

		public function checkRequired() {
			if($this->table && $this->action && $this->form_name && $this->fields) {
				return true;
			}
			return false;
		}

		public function addMethod() {
			$this->html .= "<input type='hidden' name='method' value='generatrix.admin." . $this->table . "." . $this->action . "' />";
		}

		public function getForm() {
			$html = "";
			$html .= "<form ";
			$html .= "name='generatrix-admin-" . $this->form_name . "-form' ";
			$html .= "action='" . $this->form_action . "' ";
			$html .= "method='" . $this->form_method . "' ";
			$html .= "class='" . $this->form_class . "' ";

			if($this->will_upload_file)
				$html .= "enctype='multipart/form-data' ";

			$html .= "/>";
			$html .= $this->html;
			$html .= "</form>";
			return $html;
		}

		public function process() {
			foreach($this->fields as $param_name => $param_type) {
				$param_value = isset($this->values[0][$param_name]) ? $this->values[0][$param_name] : '';

				if(in_array($param_name, $this->ignore)) {
					$this->html .= $this->row('hidden', $param_name, $param_value);
				} else {
					if($this->isBool($param_name)) {
						$this->html .= $this->row('checkbox', $param_name, $param_value);
					} else if($this->isFileUpload($param_name)) {
						$this->html .= $this->row('file', $param_name, $param_value);
					} else if($this->isSelect($param_name)) {
						$this->html .= $this->row('select', $param_name, $param_value);
					} else if($this->hasDatePicker($param_name)) {
						$this->html .= $this->row('datepicker', $param_name, $param_value);
					} else {
						switch($param_type) {
							case 'int':
							case 'timestamp':
								$this->html .= $this->row('text', $param_name, $param_value);
								break;
							case 'text':
								$this->html .= $this->row('textarea', $param_name, $param_value);
								break;
							default:
								if($this->isLongVarchar($param_type)) {
									$this->html .= $this->row('textarea', $param_name, $param_value, $param_type);
								} else if($this->isShortVarchar($param_type)) {
									$this->html .= $this->row('text', $param_name, $param_value);
								}
						}
					}
				}
			}
			$this->html .= $this->row('submit');
		}

		private function processName($name) {
			$transforms = array(
				'__slash__' => '/',
				'__space__' => ' ',
				'_file' => '',
				'_bool' => '',
				'_date' => ''
			);

			$final_name = $name;
			foreach($transforms as $key => $value) {
				$final_name = str_replace($key, $value, $final_name);
			}
			return ucwords($final_name);
		}

		private function row($type, $param_name = '', $param_value = '', $param_type = '') {

			$text = ($type == 'text') ? true : false;
			$textarea = ($type == 'textarea') ? true : false;
			$checkbox = ($type == 'checkbox') ? true : false;
			$file = ($type == 'file') ? true : false;
			$submit = ($type == 'submit') ? true : false;
			$hidden = ($type == 'hidden') ? true : false;
			$select = ($type == 'select') ? true : false;
			$datepicker= ($type == 'datepicker') ? true : false;

			if(!$hidden) $this->values_count++;
			if($file) $this->upload_count++;

			$html = "";

			if($text || $textarea || $checkbox || $file || $submit || $select || $datepicker) {
				if($param_name == 'state') {
					$html .= "<div class='" . $this->form_label_class . "' id='store_state_name'>";
				} elseif($param_name == 'fun_category_id') {
						$html .= "<div class='" . $this->form_label_class . "' id='fun_admin' style='display:none'>";
				} elseif($param_name == 'city') {
					$html .= "<div class='" . $this->form_label_class . "' id='store_city_name'>";
				} elseif($param_name == 'categoryid' && $param_value == '') {
						$html .= "<div class='" . $this->form_label_class . "' id='categoryid' style='display: none'>";
				//} elseif($param_name == 'latitude' || $param_name == 'longitude') {
				//		$html .= "<div class='" . $this->form_label_class . " store_latlng'  style='display: none'>";
				} else {
					$html .= "<div class='" . $this->form_label_class . "'>";
				}
			}

			if($text || $textarea || $checkbox || $file || $submit || $select || $datepicker)
				$html .= "<div class='generatrix-control-form-label-internal'>";

			if($text || $textarea || $checkbox || $file || $select || $datepicker)
				$html .= $this->processName($param_name);

			if($submit)
				$html .= "&nbsp;";

			if($text || $textarea || $checkbox || $file || $submit || $select || $datepicker)
				$html .= "</div>";

			if($text || $textarea || $checkbox || $file || $submit || $select || $datepicker)
				$html .= "</div>";

			if($text || $textarea || $checkbox || $file || $submit || $select || $datepicker) 
				$html .= "<div class='" . $this->form_value_class . "'>";

			$textarea_size = '50px';
			if($textarea) {
				if($param_type != '') {
					$size = str_replace('varchar_', '', $param_type);
					if(($size > 256) && ($size < 512)) {
						$textarea_size = '80px';
					} else if($size > 512) {
						$textarea_size = '200px';
					}
				}
			}

			if($text) {
				
				if(!$param_value && ($param_name == 'categorytype' || $param_name == 'categoryname' || $param_name == 'fun_category_id')) {
					$selected = isset($_POST['selectcat']) ? $_POST['selectcat'] : '';
					$split = explode('$##$', $selected );
					$category_type =  checkArray($split, 0)? $split[0]: '';
					$category_name =  checkArray($split, 1)? $split[1]: '';
					$category_id =  checkArray($split, 2)? $split[2]: '';
					if($param_name == 'categorytype')
						$html .= "<input type='text' class='generatrix-control-form-element-text'  name='" . $param_name . "' value='" . $category_type . "' readonly />";
					if($param_name == 'categoryname')
						$html .= "<input type='text' class='generatrix-control-form-element-text'  name='" . $param_name . "' value='" . $category_name . "' readonly />";
					if($param_name == 'fun_category_id' )  
						$html .= "<input type='hidden' class='generatrix-control-form-element-text'  name='" . $param_name . "' value='" . $category_id ."' />";
				} elseif($param_value && ($param_name == 'categorytype' || $param_name == 'categoryname' || $param_name == 'fun_category_id')) { 
          if($param_name == 'categorytype')
            $html .= "<input type='text' class='generatrix-control-form-element-text'  name='" . $param_name . "' value='" . $param_value . "' readonly />";
          if($param_name == 'categoryname')
            $html .= "<input type='text' class='generatrix-control-form-element-text'  name='" . $param_name . "' value='" . $param_value . "' readonly />";

				}	elseif ($param_name == 'width' || $param_name == 'height') {
					$html .= "<input type='text' maxlength='3' size='3' style='width: auto;' class='generatrix-control-form-element-text' name='" . $param_name . "' value='" . $param_value . "' />";
				} elseif ( $param_name == 'country') {
					$html .= "<input type='text' class='generatrix-control-form-element-text' id='store_country'  name='" . $param_name . "' value='" . $param_value . "' />";

				}	elseif ($param_name == 'state' ) {
					$html .= "<input type='text' class='generatrix-control-form-element-text' id='store_state'  name='" . $param_name . "' value='" . $param_value . "' />";

				} elseif($param_name == 'city') {
					$html .= "<input type='text' class='generatrix-control-form-element-text' id='store_city'  name='" . $param_name . "' value='" . $param_value . "' />";

				} elseif($param_name == 'categoryid' && $param_value == '')  {
				 		$category_id = isset($_POST['selectcat']) ? $_POST['selectcat'] : '';
						$html .= "<input type='hidden' class='generatrix-control-form-element-text' name='" . $param_name . "' value='" . $category_id . "' />";
				//} elseif($param_name == 'latitude' || $param_name == 'longitude') {
				//		$html .= "<input type='hidden' class='generatrix-control-form-element-text' name='" . $param_name . "' value='" . $param_value . "' />";
				}	elseif($param_name == 'fun_category_id' )  {
						$html .= "<input type='hidden' class='generatrix-control-form-element-text'  name='" . $param_name . "' value='" . $category_id ."' />";
				}else {
					$html .= "<input type='text' class='generatrix-control-form-element-text' name='" . $param_name . "' value='" . str_replace ( "'", "&#039;",$param_value) . "' />";
				}
			}
			if($textarea) {
				if($param_name == 'address') {
					$html .= "<textarea class='generatrix-control-form-element-textarea' style='height: " . $textarea_size . "' name='" . $param_name . "'>" . $param_value . "</textarea><span id='span_store_address'><img src='".href('/public/style/../images/loading.gif')."'>&nbsp;&nbsp;Checking address..</span>";
				} else { 
					$html .= "<textarea class='generatrix-control-form-element-textarea' style='height: " . $textarea_size . "' name='" . $param_name . "'>" . $param_value . "</textarea>";
				}
			}

			if($checkbox) {
				$checked = ($param_value == 'off') ? '' : 'checked="CHECKED"';
				$html .= "<input type='checkbox' class='generatrix-control-form-element-checkbox' name='" . $param_name . "' " . $checked . " />";
			}

			if($file) {
				$html .= "<input type='file' class='generatrix-control-form-element-file' name='" . $param_name . "' />";
				if($param_value != '') {
					$dots = explode('.', $param_value);
					$extension = strtolower($dots[count($dots) - 1]);

					$images = array('png', 'jpg', 'tiff', 'gif');
					if(in_array($extension, $images)) {
						if(file_exists(path($param_value))) {
							$html .= '<a target="_blank" href="' . href($param_value) . '" class="generatrix-control-table-link"><img src="' . image($param_value) . '" /></a>';
						} else {
							$html .= 'File Missing';
						}
					} else {
						$slashes = explode('/', $param_value);
						$file_name = $slashes[count($slashes) - 1];
						$file_name = substr($file_name, 7, strlen($file_name) - 1);
						$html .= '<a target="_blank" href="' . href($param_value) . '">' . $file_name . '</a>';
					}
				}
			}
		
			if($datepicker) {
				$date = check($param_value) ? $param_value : date('m/d/y');
				$html .= "<input type='text' name='" . $param_name . "'  value='" . $date . "' class='generatrix-control-form-element-date' id='insert_date' /></div>";
			}

			if($submit) {
				$message = (($this->upload_count / $this->values_count) > 0.2) ? 'Upload' : 'Save';
				if($this->table == 'stores') {
					$html .= "<input type='submit' id='stores_submit' name='submit' value='" . $message . "' class='generatrix-control-form-element-submit' /> or <a href='" . href('/admin/dashboard/' . $this->table . '/view/1') . "'>Cancel</a>";
				} else {
					$html .= "<input type='submit' name='submit' value='" . $message . "' class='generatrix-control-form-element-submit' /> or <a href='" . href('/admin/dashboard/' . $this->table . '/view/1') . "'>Cancel</a>";
				}
			}

			if($select) {
				$temp_html = '<option value="0">Please select a value</option>';
				foreach($this->select[$param_name] as $key => $value) {
					$selected = ($param_value == $value) ? 'SELECTED' : '';
					$temp_html .= '<option value="' . $value . '" ' . $selected . '>' . $key . '</option>';
				}
				$html .= '<select name="' . $param_name . '">' . $temp_html . '</select>';
			}

			if($text || $textarea || $checkbox || $file || $submit || $select)
				$html .= "</div>";

			$html .= '<br clear="all" />';

			if($hidden)
				$html = '<input type="hidden" name="' . $param_name . '" value="' . $param_value . '" />';

			return $html;
		}

		private function isSelect($name) {
			$selects = array_keys($this->select);
			if(in_array($name, $selects)) {
				return true;
			}
			return false;
		}

		private function isFileUpload($name) {
			$name_split = explode('_', $name);
			if(
				isset($name_split[0]) &&
				isset($name_split[1]) &&
				($name_split[1] == 'file')
			) {
				$this->will_upload_file = true;
				return true;
			}
			return false;
		}
	
		private function hasDatePicker($name) {
			$name_split = explode('_', $name);
			if(
				isset($name_split[0]) &&
				isset($name_split[1]) &&
				($name_split[1] == 'date')
			) {
				$this->will_add_datepicker = true;
				return true;
			}
			return false;
		}

		private function isBool($name) {
			$name_split = explode('_', $name);
			if(
				isset($name_split[0]) &&
				isset($name_split[1]) &&
				($name_split[1] == 'bool')
			) {
				return true;
			}
			return false;
		}

		private function isLongVarchar($type) {
			if(($split = $this->isVarchar($type))) {
				if(isset($split[1]) && ($split[1] > 128)) {
					return true;
				}
			}
			return false;
		}

		private function isShortVarchar($type) {
			if(($split = $this->isVarchar($type))) {
				if(isset($split[1]) && ($split[1] <= 128)) {
					return true;
				}
			}
			return false;
		}

		private function isVarchar($type) {
			$type_split = explode('_', $type);
			if(
				isset($type_split[0]) &&
				isset($type_split[1]) &&
				($type_split[0] == 'varchar') &&
				is_numeric($type_split[1])
			) {
				return $type_split;
			}
			return false;
		}

	}

?>
