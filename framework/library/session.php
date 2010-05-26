<?php

  class Session extends AssocList {
    public function __construct() {
      $session = isset($_SESSION) ? $_SESSION : '';
      $this->setData($session);
      $this->processParameters();
    }

    private function processParameters() {
      $tags = array_keys($this->getData());
      foreach($tags as $tag) {
        $this->addParameter($tag, $_SESSION[$tag]);
      }
    }

    public function add($tag, $value) {
      $_SESSION[$tag] = $value;
      $this->addParameter($tag, $value);
      return $this;
    }

    public function remove($tag) {
      unset($_SESSION[$tag]);
      $this->removeParameter($tag);
      return $this;
    }
  }

?>
