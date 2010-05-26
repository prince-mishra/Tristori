<?php

  // A class to handle the request

  class Request extends AssocList {

    public function __construct() {

      $url = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';
      $this->setData($url);
      $this->processParameters();
    }

    private function processParameters() {
      $split_array = explode("/", $this->getData());
      for($i = 0; $i < count($split_array); $i++) {
        $value = $split_array[$i];
        if($i == 0)
          $this->addParameter('controller', $value);
        else if($i == 1)
          $this->addParameter('method', $value);
        else
          $this->addParameter('p' . ($i - 2), $value);
      }
      if($this->getValue('controller') != "")
        $this->addParameter('enabled', true);
    }

	}

?>
