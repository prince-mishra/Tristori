<?php

	$form = isset($this->control_options['form']) ? $this->control_options['form'] : false;
	$terms = isset($this->control_options['terms']) ? $this->control_options['terms'] : false;

	if(!$form) {
		display('The control options weren\'t sent properly');
	}

	$fc = new FormCreator($form, $terms);

	class FormCreator {
		private $form;
		private $size;
		private $terms;
		private $float;
		private $javascript;

		public function __construct($form, $terms = '') {
			$this->form = json_decode($form, true);
			if(!isset($this->form['elements'][0]['label'])) {
				display('The JSON could not be parsed. Please check the following JSON at <a href="http://www.jsonlint.com">JSON LINT</a>');
				display($form);
			} else {
				$this->size = checkArray($this->form, 'size') ? $this->form['size'] : '16';
				$this->float = checkArray($this->form, 'float') ? $this->form['float'] : false;
				$this->terms = is_array($terms) ? $terms : array();
				$this->processForm();
			}
		}

		private function sanitize($term) {
			return preg_replace('/-+/', '_', trim(preg_replace('/[^a-zA-Z0-9]/', '_', trim(strtolower(str_replace('_', ' ', $term))) ) ) );
		}

		private function processForm() {
			$elements = checkArray($this->form, 'elements')? $this->form['elements'] : false;
			if(!is_array($elements)) {
				display('No Elements found');
			} else {
				foreach($elements as $row) {
					$label = checkArray($row, 'label') ? $row['label'] : false;
					$type = checkArray($row, 'type') ? $row['type'] : false;
					$value = checkArray($row, 'value') ? $row['value'] : false;
					$required = checkArray($row, 'required') ? $row['required'] : false;
					$selected = checkArray($row, 'selected') ? $row['selected'] : false;
					$subtext = checkArray($row, 'subtext') ? $row['subtext'] : false;

					if(!$type || !$label) {
						$label = ($label) ? $label : 'No label set';
						display('Nothing found for row "' . $label . '"');
					} else {
						switch($type) {
							case 'text':
							case 'password':
								$this->rowText($label, $value, $subtext, $required, $type);
								break;
							case 'select':
								$this->rowSelect($label, $value, $subtext, $required, $selected);
								break;
							case 'checkbox':
								$this->rowCheckbox($label, $value, $subtext, $required, $selected);
								break;
							case 'html':
								$this->rowHtml($label, $value, $subtext, $required);
								break;
							case 'submit':
								$this->rowSubmit($label, $value, $required);
								break;
							case 'hidden':
								$this->rowHidden($label, $value, $required);
								break;
							case 'textarea':
								$this->rowTextArea($label, $value, $subtext, $required);
								break;
							case 'file':
								$this->rowFile($label, $value, $subtext, $required);
								break;
							default:
								display('Type "' . $type . '" is not supported');
								break;
						}
					}
				}
			}
		}

		private function noFloat() {
			if($this->float == 'none') {
				return ' style="float: none;" ';
			}
		}

		private function rowHidden($label, $value, $required) {
			echo "<input type='hidden' name='" . $label . "' value='" . $value . "' />";
		}

		private function rowSubmit($label, $value, $required) {
    	$cancel_link = ($value != '') ? href($value) : '';

			$html = '';
			$html .= '<div class="row">';
			$html .= '	<div class="label-' . $this->size . '" ' . $this->noFloat() . '>&nbsp;</div>';
			$html .= '	<div class="element-' . $this->size. '" ' . $this->noFloat() . '>';
			$html .= '		<input type="submit" name="submit" value="' . $label . '" class="form-input-submit" /> or <a href="' . $cancel_link . '">Cancel</a>';
			$html .= '	</div>';
			if($this->float != 'none') $html .= '	<br clear="all" />';
			$html .= '</div>';
			echo $html;
		}

		private function rowHtml($label, $value, $subtext, $required) {
			$html = '';
			$html .= '<div class="row">';
			$html .= '	<div class="label-' . $this->size . '" ' . $this->noFloat() . '>';
			$html .= '		' . $label;
			$html .= '	</div>';
			$html .= '	<div class="element-' . $this->size. '" ' . $this->noFloat() . '>';
			$termValue = isset($this->terms[$value]) ? $this->terms[$value] : $value;
			$html .= '		<div class="form-input-html">';
			$html .= '		' . prepare(urldecode($termValue));
			$html .= '		</div>';
			$html .= '		<div class="form-input-subtext">';
			$html .= '		' . $subtext;
			$html .= '		</div>';
			$html .= '	</div>';
			if($this->float != 'none') $html .= '	<br clear="all" />';
			$html .= '</div>';
			echo $html;
		}

		private function rowCheckbox($label, $value, $subtext, $required, $selected) {
			$html = '';
			$html .= '<div class="row">';
			$html .= '	<div class="label-' . $this->size . '" ' . $this->noFloat() . '>';
			$html .= '		' . $label;
			if(($label != ' ') && $required) {
				$html .= ' *';
			}
			$html .= '	</div>';
			$html .= '	<div class="element-' . $this->size. '" ' . $this->noFloat() . '>';
			$html .= '		<div class="form-input-html">';
			$html .= '			<input type="checkbox" id="' . $this->sanitize($value) . '" name="' . $this->sanitize($value) . '" value="" ' . $selected . ' class="" />';
			$termValue = (checkArray($this->terms, $value)) ? $this->terms[$value] : $value;
			$html .= '			' . $termValue;
			$html .= '		</div>';
			$html .= '		<div class="form-input-subtext">';
			$html .= '			' . $subtext;
			$html .= '		</div>';
			$html .= '	</div>';
			if($this->float != 'none') $html .= '	<br clear="all" />';
			$html .= '</div>';
			echo $html;
		}

		private function rowSelect($label, $values, $subtext, $required, $selected) {
			$html = '';
			$html .= '<div class="row">';
			$html .= '	<div class="label-' . $this->size . '" ' . $this->noFloat() . '>';
			$html .= '		' . $label;
			if($required) {
				$html .= ' *';
			}
			$html .= '	</div>';
			$html .= '	<div class="element-' . $this->size. '" ' . $this->noFloat() . '>';
			$html .= '		<div>';
			$html .= '			<select id="' . $this->sanitize($label) . '" name="' . $this->sanitize($label) . '" class="form-input-text input-' . $this->size . '">';
			foreach($values as $key => $value) {
				$select_this = ($value == $selected) ? ' SELECTED ' : '';
				$html .= '			<option ' . $select_this . ' value="' . $value . '">' . $key . '</option>';
			}
			$html .= '			</select>';
			$html .= '		</div>';
			$html .= '		<div class="form-input-subtext">';
			$html .= '			' . $subtext;
			$html .= '		</div>';
			$html .= '	</div>';
			if($this->float != 'none') $html .= '	<br clear="all" />';
			$html .= '</div>';
			echo $html;
		}

		private function rowFile($label, $value, $subtext, $required) {
			$html = '';
			$html .= '<div class="row">';
			$html .= '	<div class="label-' . $this->size . '" ' . $this->noFloat() . '>';
			$html .= '		' . $label;
			if($required) {
				$html .= ' *';
			}
			$html .= '	</div>';
			$html .= '	<div class="element-' . $this->size . '" ' . $this->noFloat() . '>';
			$html .= '		<div>';
			$html .= '			<input type="file" id="' . $this->sanitize($label) . '" name="' . $this->sanitize($label) . '" value="" class="form-input-text input-' . $this->size . '" />';
			if($value != '') {
				$actual_value = checkArray($this->terms, $value) ? $this->terms[$value] : $value;
				$html .= '		<br />' . urldecode($actual_value);
			}
			$html .= '		</div>';
			$html .= '		<div class="form-input-subtext">';
			$html .= '			' . $subtext;
			$html .= '		</div>';
			$html .= '	</div>';
			if($this->float != 'none') $html .= '	<br clear="all" />';
			$html .= '</div>';
			echo $html;
		}

		private function rowText($label, $value, $subtext, $required, $type) {
			$html = '';
			$html .= '<div class="row">';
			$html .= '	<div class="label-' . $this->size . '" ' . $this->noFloat() . '>';
			$html .= '		' . $label;
			if($required) {
				$html .= ' *';
			}
			$html .= '	</div>';
			$html .= '	<div class="element-' . $this->size . '" ' . $this->noFloat() . '>';
			$html .= '		<div>';
			$value = str_replace('%26lt%3Bbr%26gt%3B', urlencode("\n"), $value);
			$html .= '			<input type="' . $type . '" id="' . $this->sanitize($label) . '" name="' . $this->sanitize($label) . '" value="' . stripslashes(urldecode($value)) . '" class="form-input-text input-' . $this->size . '" />';
			$html .= '		</div>';
			$html .= '		<div class="form-input-subtext">';
			$html .= '			' . $subtext;
			$html .= '		</div>';
			$html .= '	</div>';
			if($this->float != 'none') $html .= '	<br clear="all" />';
			$html .= '</div>';
			echo $html;
		}

		private function rowTextArea($label, $value, $subtext, $required) {
			$html = '';
			$html .= '<div class="row">';
			$html .= '	<div class="label-' . $this->size . '" ' . $this->noFloat() . '>';
			$html .= '		' . $label;
			if($required) {
				$html .= ' *';
			}
			$html .= '	</div>';
			$html .= '	<div class="element-' . $this->size . '" ' . $this->noFloat() . '>';
			$html .= '		<div>';
			$value = str_replace('%26lt%3Bbr%26gt%3B', urlencode("\n"), $value);
			$html .= '			<textarea id="' . $this->sanitize($label) . '" name="' . $this->sanitize($label) . '" class="form-input-textarea input-' . $this->size . '">' . stripslashes(urldecode($value)) . '</textarea>';
			$html .= '		</div>';
			$html .= '		<div class="form-input-subtext">';
			$html .= '			' . $subtext;
			$html .= '		</div>';
			$html .= '	</div>';
			if($this->float != 'none') $html .= '	<br clear="all" />';
			$html .= '</div>';
			echo $html;
		}
	}

?>
