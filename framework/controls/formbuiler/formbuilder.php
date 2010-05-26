<?php

	require_once(DISK_ROOT . '/framework/controls/form/form_html.php');

	$css = '<link href="' . href('/framework/controls/form/form.css') . '" rel="stylesheet" media="screen" type="text/css" />';
	$this->getHead()->appendContent($css);


	$form = new FormHTML($this->control_options);

	if($form->checkRequired()) {
		$form->addMethod();
		$form->process();
		echo $form->getForm();
	}

?>
