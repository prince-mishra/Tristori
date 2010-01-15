<div class='span-24'>
	<h2>
		<?php echo ucwords($this->get('table')) ?>
		<span class='generatrix-admin-small'>
			(<a href='<?php echo href('/admin/dashboard/' . $this->get('table') . '/add/') ?>'>Add New</a>)
		</span>
	</h2>
	<?php
		$options = array();
		$options['data'] = $this->get('display');
		$options['management'] = array();
		$options['management']['path'] = '/admin/dashboard/' . $this->get('table');
		$options['management']['column'] = 'id';

		echo $this->loadControl('table', $options);
	?>
</div>
