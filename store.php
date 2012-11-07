<?php

namespace Kademlia;

class Store extends Task {
  function __construct($settings, $key_id, $value) {
    parent::__construct();
    $this->key_id = $key_id;
    $this->value = $value;
  }


  public function enqueueSelf() {
    $class_name = get_class($this);

    $task = new FindNode($settings, $key_id);
    $task->enqueue()->done([$this, 'perform']);
    
    return $this;
  }


  public function perform($closest_nodes_list) {
    $class_name = get_class($this);
    $closest_nodes_list->sendStoreRequest($this->key_id, $this->value).allDone([$this, 'evaluate']);
  }


  public function evaluate() {
    $this->emit('done');
  }
}

?>
