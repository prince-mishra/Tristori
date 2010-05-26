<?php

  function sanitize($term) {
    return preg_replace('/-+/', '_', trim(preg_replace('/[^a-zA-Z0-9]/', '_', trim(strtolower(str_replace('_', ' ', $term))) ) ) );
  }

  // Creates a link based on the APPLICATION PATH defined in /app/settings/config.json
  // USAGE : href('/app/views/homeView.php');
  function href($path) {
    $relative_root = '';
    $slashes = explode('/', APPLICATION_ROOT);
    for($i = 0; $i < count($slashes); $i++) {
      if(($i > 2) && (isset($slashes[$i])) && ($slashes[$i] != '')) {
        $relative_root .= ( '/' . $slashes[$i]);
      }
    }
    return $relative_root . $path;
  }

  // Calculate time in readable format
  function getReadableTime($seconds) {
    if($seconds < 60) {
      return $seconds . " seconds";
    }
    $minutes = round($seconds / 60);
    if($minutes < 60) {
      return $minutes . " minutes";
    }
    $hours = round($minutes / 60);
    if($hours < 24) {
      return $hours . " hours";
    }
    $days = round($hours / 24);
    return $days . " days";
  }

  // Use timthumb to create smaller iamges
  function image($path, $width = '', $height = '') {
    if(check($path)) {
      return href('/framework/external/timthumb/timthumb.php?src=' . href($path) . '&w=' . $width . '&h=' . $height);
    } else {
      display_error('The image ' . href($path) . ' was not found');
    }
  }

  // Checks the file permission for a file inside generatrix
  function perms($path) {
    if(file_exists(path($path))) {
      return substr(sprintf('%o', fileperms(path($path))), -3);
    } else {
      display_error('You are trying to edit the permissions, but the <strong>file ' . path($path) . ' does not exist</strong>');
      return false;
    }
  }

  // Returns the full path of the file (use the property defined in /app/settings/config.json
  // USAGE : path('/app/views/homeView.php');
  function path($path) {
    $relative_root = substr(DISK_ROOT, 0, strlen(DISK_ROOT) - 1);
    return $relative_root . $path;
  }

  // check if a value has been set and is not null
  function check($value) {
    if(isset($value) && ($value != ''))
      return true;
    return false;
  }

  // Check if a value inside an array isset and is not null
  function checkArray($array, $value) {
    if(is_array($array) && isset($array[$value]) && ($array[$value] != ''))
      return true;
    return false;
  }

  // Create json object
  function json($data) {
    header('Content-Type: application/json');
    return json_encode($data);
  }

  // Redirect to a particular path
  // USAGE : location('/user/forgotpass');
  function location($path) {
    $file_name = '';
    $line_number = '';
    if(!headers_sent($file_name, $line_number)) {
      header("Location: " . href($path));
      exit();
    } else {
      display_system('Cannot redirect the page to <strong>' . href($path) . '</strong> because headers have already been sent. The headers were started by <strong>' . $file_name. ' [ Line Number : '. $line_number . ']</strong>');
    }
  }

  function bt() {
    $bt = debug_backtrace();
    $output = array(
      'file' => $bt[0]['file'],
      'line' => $bt[0]['line']
    );
    return $output;
  }

  function ut($variable) {
    return urlencode(trim($variable));
  }

  function facebook_like() {
    $url = APPLICATION_ROOT . href('/' . $_GET['url']);
    return '<iframe src="http://www.facebook.com/plugins/like.php?href=' . urlencode($url) . '&amp;layout=button_count&amp;show_faces=true&amp;width=150&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:150px; height:21px;" allowTransparency="true"></iframe>';
  }

  function prepare($text) {
    return stripslashes(html_entity_decode($text));
  }

  function prepareExcerpt($text) {
    return prepare(str_replace('&lt;br&gt;', ' ', $text));
  }

?>
