<?php

namespace Kademlia;

class Bootstrap extends Task {
  public $find_node_task = NULL;

  function __construct(&$settings, $bootstrap_nodes) {
    parent::__construct($settings);

    $this->settings = &$settings;
    $this->bootstrap_nodes = $bootstrap_nodes;
    $this->query_node_id = Node::randomNodeId();
  }


  public function enqueueSelf() {
    var_dump('Bootstrap::enqueueSelf');
    $this->find_node_task = new FindNode($this->settings, $this->query_node_id, $this->bootstrap_nodes);
    $this->find_node_task->enqueue()->done([$this, 'perform']);
  }


  public function perform($closest_nodes_list = NULL) {
    if($closest_nodes_list === NULL)
      $closest_nodes_list = $this->bootstrap_nodes;

    if($closest_nodes_list->containsNodeId($this->query_node_id)) {
      $this->find_node_task = new Bootstrap($this->settings, $this->bootstrap_nodes);
      $this->find_node_task->enqueue()->success([$this, 'emitSuccess']);
    }
    else {
      $this->settings->own_node_id = $this->query_node_id;
      $this->emitSuccess($this->query_node_id);
    }
  }


  public function emitSuccess($own_node_id) {
    $this->emit('success', $own_node_id);
  }
}

?>
