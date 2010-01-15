<div class='span-24 generatrix-admin-sidebar'>
	<ul>
	<?php
		$use = $this->get('use');
		foreach($use as $table) {
	?>
			<li> <a href='<?php echo href('/admin/dashboard/' . $table . '/view/1') ?>'><?php echo ucwords($table) ?></a> &nbsp; &nbsp;</li>
	<?php
		}
	?>
	<br clear='all' />
	</ul>
</div>
