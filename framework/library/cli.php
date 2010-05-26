<?php

  //
  // This class is an associative list, used with the comman line interface
  //

  class Cli extends AssocList {
    public function __construct($argv) {
      $this->setData($argv);
      $this->processParameters();
    }

    // Process the data array to create the key/value pair structure
    private function processParameters() {
      $value = $this->getData('data');
      for($i = 0; $i < count($this->getData('data')); $i++) {
        if(isset($value[$i])) {
          if($i == 0)
            $this->addParameter('file', $value[$i]);
          else if($i == 1)
            $this->addParameter('controller', $value[$i]);
          else if($i == 2)
            $this->addParameter('method', $value[$i]);
          else
            $this->addParameter('p' . ($i - 3), $value[$i]);
        }
      }
      // If $_SERVER varaible is not set, the request is coming via CLI
      if(!isset($_SERVER['HTTP_USER_AGENT']))
        $this->addParameter('enabled', true);
    }

  }

?>
