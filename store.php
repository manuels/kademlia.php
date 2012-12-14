<?php

namespace Kademlia;

class Store extends Task {
  function __construct(&$settings, $key_id, $value, $expire) {
    parent::__construct($settings);
    $this->settings = &$settings;
    $this->key_id = $key_id;
    $this->value = $value;
    $this->expire = $expire;
  }


  public function enqueueSelf() {
    $class_name = get_class($this);

    $task = new FindNode($this->settings, $this->key_id);
    $task->enqueue()->done([$this, 'perform']);
    
    return $this;
  }


  public function perform($found_nodes_list) {
    $this->found_nodes_list = $found_nodes_list;

    $closest_nodes = $found_nodes_list->closestNodes($this->key_id, $this->settings->bucket_size);
    $task = $closest_nodes->sendStoreRequest($this->settings, $this->key_id, $this->value, $this->expire);

    $task->enqueue();
    $task->allSucceeded([$this, 'emitSuccess']);
    $task->allDone([$this, 'emitDone']);
  }


  public function emitDone() {
    $this->emit('done');
  }


  public function emitSuccess() {
    $this->emit('success');
  }
}

?>
