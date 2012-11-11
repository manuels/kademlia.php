<?php

namespace Kademlia;

abstract class Task extends TaskBase {
  public function done($callback) {
    $this->registerCallback('done', $callback);
  }


  public function failed($callback) {
    $this->registerCallback('failed', $callback);
  }


  public function success($callback) {
    $this->registerCallback('success', $callback);
  }
}

?>
