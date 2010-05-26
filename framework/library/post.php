<?php

  // This class creates an associative list for $_POST, so that you can use specific functions

  class Post extends AssocList {
    public function __construct() {
      $post = isset($_POST) ? $_POST : '';
      $this->setData($post);
      $this->processParameters();
    }

    private function processParameters() {
      $tags = array_keys($this->getData());
      foreach($tags as $tag) {
        $this->addParameter($tag, $_POST[$tag]);
      }
    }
  }

?>
