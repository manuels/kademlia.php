<?php

namespace Kademlia;

class Bootstrap extends Task {
  public $find_node_task = NULL;

  function __construct(&$settings, $bootstrap_nodes) {
    parent::__construct($settings);

    $this->settings = &$settings;
    $this->bootstrap_nodes = $bootstrap_nodes;
    if($settings->own_node_id === '')
      $settings->own_node_id = Node::randomNodeId();
  }


  public function enqueueSelf() {
    $this->find_node_task = new FindNode($this->settings, $this->settings->own_node_id, $this->bootstrap_nodes);
    $this->find_node_task->enqueue()->done([$this, 'perform']);

    return $this;
  }


  public function perform($results) {
    $node_list = new NodeList([]);

    foreach($results as $nodes) {
      $node_list->addNodeArray($nodes);
    }

    if($node_list->containsNodeId($this->settings->own_node_id)) {
      $this->settings->own_node_id = Node::randomNodeId();

      $this->find_node_task = new Bootstrap($this->settings, $this->bootstrap_nodes);
      $this->find_node_task->enqueue()->done([$this, 'emitSuccess']);
    }
    else {
      $this->emitSuccess($this->settings->own_node_id);
    }
  } 


  public function emitSuccess($own_node_id) {
    $this->settings->own_node_id = $own_node_id;
    $this->emit('success', $own_node_id);

    $kbuckets_size = $this->settings->kbuckets->toNodeList()->size();

    $this->fillBuckets();
  }


  public function fillBuckets() {
    for($i = 0; $i < N/8; $i++) {
      for($j = 0; $j < 8; $j++) {
        $node_id = $this->settings->own_node_id;

        $byte = $node_id[$i];
        $byte ^= chr(1 << $j);
        $node_id[$i] = $byte;

        $task = new FindNode($this->settings, $node_id);
        $task->enqueue();
      }
    }
  }
}

?>
