<?php

namespace Kademlia;

abstract class Task extends TaskBase {
  public function done($callback) {
    $this->registerCallback('done', $callback);
    return $this;
  }


  public function failed($callback) {
    $this->registerCallback('failed', $callback);
    return $this;
  }


  public function success($callback) {
    $this->registerCallback('success', $callback);
    return $this;
  }
}

?>
