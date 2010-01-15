<div class='span-24'>
<?php
	echo $this->loadControl('form', array(
		'table' => $this->get('table'),
		'action' => $this->get('action'),
		'fields' => $this->get('table_info'),
		'values' => $this->get('display')
	));
?>
</div>
