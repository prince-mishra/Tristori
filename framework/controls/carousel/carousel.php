<?php

	// Example Usage

	/*
	      $content = $this->loadControl('carousel', array(
        'id' => '1',
        'selector' => 'test',
        'btnNext' => '.next',
        'btnPrev' => '.prev',
        'images' => array(
          '/public/home.jpg',
          '/public/star.jpg'
        ),
        'image_height' => 400,
        'image_width' => 400,
        'visible' => 1,
        'circular' => false
      ));

	*/

	$options = $this->control_options;
	$keys = array_keys($options);

	$required = array('id', 'selector', 'btnNext', 'btnPrev', 'images', 'image_height', 'image_width');
	$allowed = array('btnNext', 'btnPrev', 'auto', 'speed', 'scroll', 'visible', 'vertical', 'circular');
	$allowed_but_not_options = array('id', 'selector', 'images', 'image_height', 'image_width');

	$should_continue = true;
	foreach($required as $item) {
		if(!checkArray($options, $item)) {
			display('The option "' . $item . '" is missing in the control "' . $this->control_name . '"');
			$should_continue = false;
		}
	}

	if($should_continue) {

		$selected_options = array();

		foreach($keys as $item) {
			if(!in_array($item, $allowed)) {
				if(!in_array($item, $allowed_but_not_options)) {
					display_error('The option "' . $item . '" is not allowed in the control "' . $this->control_name . '". Please visit the original site of <a href="http://www.gmarwaha.com/jquery/jcarousellite/">jCarouselLite</a> for more details. (Some options might not be supported by Generatrix)');
				}
			} else {
				$selected_options[$item] = $options[$item];
			}
		}

		// The mouseWheel option requires jquery.mousewheel_1.3.0.2.min.js
		// The option easing requires the easing library
		// beforeStart is a function callback
		// afterEnd is a function callback

		$json = array();
		foreach($selected_options as $key => $value) {
			$json[] = (' ' . $key. ': "' . $value . '" ');
		}

		$js = '
			<script type="text/javascript" src="' . href('/framework/controls/carousel/js/jcarousellite_1.0.1.min.js') . '"></script>
			<script type="text/javascript">
				$(function() {
					$(".' . $options['selector'] . '").jCarouselLite({' . implode(', ', $json) . '});
				});
			</script>
		';

		$this->getHead()->appendContent($js);

		echo '
<div class="next">Next</div><div class="prev">Prev</div>
<div style="width: 500px;" id="control-carousel-' . $options['id'] . '" class="' . $options['selector'] . ' control-carousel">
	<ul>
		';

		foreach($options['images'] as $image_name) {
			echo '<li><img src="' . image($image_name, $options['image_height'], $options['image_width']) . '" /></li>';
		}

		echo '
	</ul>
</div>
		';

	}

?>
