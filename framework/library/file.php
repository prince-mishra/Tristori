<?php

  class File {

    // Write some data to a file, the permissions are set to w+, can change if you like
    public function write($file_name, $data, $file_permission = "w+") {
      // Open the path and prepare to write
      if($fp = fopen(path($file_name), $file_permission)) {
        fwrite($fp, $data);
        fclose($fp);
        return true;
      } else {
        display_error('The file <strong>' . path($file_name) . '</strong> could not be opened');
        return false;
      }
    }

    // Read data from a file, line by line, just mention the path and get started
    public function read($file_name) {
      if(file_exists(path($file_name))) {
        if($fp = fopen(path($file_name), "r")) {
          $data = '';
          while(!feof($fp)) {
            $data .= fgets($fp);
          }
          fclose($fp);
          return $data;
        } else {
          display_error('The file <strong>' . path($file_name) . '</strong> could not be opened');
          return false;
        }
      } else {
        display_error('The file <strong>' . path($file_name) . '</strong> does not exist');
        return false;
      }
    }

  }

?>
