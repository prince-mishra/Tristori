<?php

	$data = $this->control_options['data'];
	$management = isset($this->control_options['management']) ? $this->control_options['management'] : false;

	$ignore = array();

	if(isset($management['ignore']) && is_array($management['ignore'])) {
		$ignore = isset($management['ignore']) ? $management['ignore'] : '';
	}
		
	if(isset($management['ignore']) && !is_array($management['ignore'])) {
		$ignore = isset($management['ignore']) ? array($management['ignore']) : array();
	}

	$keys = array();
	if(isset($data[0])) {
		$keys = array_keys($data[0]);
	}

	$content = '<link href="' . href('/framework/controls/table/css/generatrix-control-table.css') . '" media="screen" rel="stylesheet" type="text/css" />';

	$content .= '<script type="text/javascript" src="' . href('/public/javascript/jquery.dataTables.min.js') . '"></script>';
	$this->getHead()->appendContent($content);

?>
<script type='text/javascript'>
$(document).ready(function() {
	$("#generatrix-control-table-table").dataTable({
		"bPaginate": true,
		"bProcessing": true,
		"bSort": true,
		"bStateSave": true,
		"bAutoWidth": false
	});
});
</script>
<table cellspacing='0' cellpadding='0' border='0' class='generatrix-control-table-table' id='generatrix-control-table-table'>
	<?php if(count($keys) > 0) { ?>
		<thead>
		<tr>
			<?php if($management) { ?>
				<th></th>
				<th></th>
			<?php } ?>
			<?php foreach($keys as $key) { ?>
				<?php if(!in_array($key, $ignore)) { ?>
					<th>
						<?php	
							$replace = array(
								'_file' => '',
								'_date' => '',
								'_bool' => '',
								'__space__' => ' ',
								'__slash__' => '/'
							);
							$key_display = $key;
							foreach($replace as $key => $value) {
								$key_display = str_replace($key, $value, $key_display);
							}
							echo strtoupper($key_display)
						?>
					</th>
				<?php } ?>
			<?php } ?>
		</tr>
		</thead>
		<tbody>
		<?php if(count($data) > 0) { ?>
			<?php foreach($data as $row) { ?>
				<tr>
					<?php
						if($management) {
							$edit_link = href($management['path'] . '/edit/' . $row[$management['column']]);
							$delete_link = href($management['path'] . '/delete/' . $row[$management['column']]);
					?>
						<td class="generatrix-control-table-edit-delete" width="2%">
							<a href='<?php echo $edit_link ?>'>Edit</a>
						</td>
						<td width="2%">
							<a onclick="return confirm('You are about to delete this entry. Are you sure?');" href='<?php echo $delete_link ?>' ><img src="<?php echo href('/public/images/trash.gif') ?>" /></a>
						</td>
					<?php } ?>
					<?php foreach($keys as $col) { ?>
						<?php if(!in_array($col, $ignore)) { ?>
							<td>
								<?php
									$display_value = '';
									if(strpos($col, '_file') !== false) {
										$dots = explode('.', $row[$col]);
										$extension = strtolower($dots[count($dots) - 1]);

										$images = array('png', 'jpg', 'tiff', 'gif');
										if(in_array($extension, $images)) {
											if(file_exists(path($row[$col]))) {
												$display_value = '<a target="_blank" href="' . href($row[$col]) . '" class="generatrix-control-table-link"><img src="' . image($row[$col]) . '" /></a>';
											} else {
												$display_value = 'File Missing';
											}
										} else {
											$slashes = explode('/', $row[$col]);
											$file_name = $slashes[count($slashes) - 1];
											$file_name = substr($file_name, 7, strlen($file_name) - 1);

											$display_value = '<a target="_blank" href="' . href($row[$col]) . '">' . $file_name . '</a>';
										}
									} else if(strpos($col, '_date') !== false) {
										if(strtotime($row[$col]) != 0) {
											$display_value = date("M j, Y, H:i:s", strtotime($row[$col]));
										} elseif(is_numeric($row[$col])) {
											$display_value = date("M j, Y, H:i:s", $row[$col]);
										} 
										else {
											$display_value = $row[$col];
										}
									} else if(checkArray($row, $col)) {
										$display_value = substr(strip_tags($row[$col]), 0, 63);
									}
									echo $display_value;
								?>
							</td>
						<?php } ?>
					<?php } ?>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<tr><td>No data is available for this table</td></tr>
		<?php } ?>
	<?php } else { ?>
		<tr>
			<th>No data is available for this table</th>
		</tr>
	<?php } ?>
	</tbody>
</table>
<br clear='all' />
