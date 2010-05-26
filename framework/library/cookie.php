<?php

  // This is the base class for cookies. It turns the cookie varaible in an assoc list so that you can use the AssocList functions

  class Cookie extends AssocList {
    public function __construct() {
      $cookie = isset($_COOKIE) ? $_COOKIE: '';
      $this->setData($cookie);
      $this->processParameters();
    }

    private function processParameters() {
      $tags = array_keys($this->getData());
      foreach($tags as $tag) {
        $this->addParameter($tag, $_COOKIE[$tag]);
      }
    }

    // Add a cookie
    public function add($tag, $value, $path = '/') {
      if(setcookie($tag, $value, time() + (30 * 24 * 3600), $path)) {
        $this->addParameter($tag, $value);
        return $this;
      }
      return false;
    }

    // Delete a cookie
    public function delete($tag, $path = '/') {
      if(setcookie($tag, '', time() - (30 * 24 * 3600), $path)) {
        $this->removeParameter($tag);
        return $this;
      }
      return false;
    }
  }

?>
