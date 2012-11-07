<?php

namespace Kademlia;

class Ping extends Task {
  function __construct(&$settings, $node) {
    parent::__construct($settings);
    $this->node = $node;
  }

  public function enqueueSelf() {
    $this->enqueueSelfPrototype();
  }

  public function perform() {
    $this->node->sendPingRequest()->done([$this, 'evaluate']);
  }

  public function evaluate($success) {
    if($success)
      $this->emit('success');
    else
      $this->emit('failed');
  }
}

?>
