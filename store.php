<?php

namespace Kademlia;

class Store extends Task {
  function __construct(&$settings, $key_id, $value) {
    parent::__construct($settings);
    $this->settings = &$settings;
    $this->key_id = $key_id;
    $this->value = $value;
  }


  public function enqueueSelf() {
    $class_name = get_class($this);

    $task = new FindNode($settings, $key_id);
    $task->enqueue()->done([$this, 'perform']);
    
    return $this;
  }


  public function perform($found_nodes_list) {
    $this->found_nodes_list = $found_nodes_list;

    $closest_nodes = $found_nodes_list->closestNodes($this->key_id, $this->settings->bucket_size);
    $task = $cloest_nodes->sendStoreRequest($this->settings, $this->key_id, $this->value);
    $task->allDone([$this, 'evaluate']);
  }


  public function evaluate() {
    $this->emit('done');
  }
}

?>
