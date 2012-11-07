<?php

namespace Kademlia;

class FindNode extends Task {
  function __construct(&$settings, $node_id, $initial_node_list) {
    parent::__construct($settings);

    $this->settings = &$settings;
    $this->needle_id = $node_id;
    $this->initial_node_list = $initial_node_list;
    $this->asked_nodes = [];
    $this->hop = 0;
  }


  public function enqueueSelf() {
    return $this->enqueueSelfPrototype();
  }


  public function perform($node_list = NULL) {
    if($node_list === NULL)
      $node_list = $this->initial_node_list;

    # done?
    $unasked_nodes = $node_list->without($this->asked_nodes);
    if(count($unasked_nodes) === 0) {
      if($this->asked_nodes->containsNodeId($this->needle_id))
        $signal = 'success';
      else
        $signal = 'done';

      $this->emit($signal, $this->asked_nodes);
      return;
    }

    $closest_nodes = $unasked_nodes->closestNodes($this->node_id, $this->settings->alpha);

    $this->hop++;
    $this->asked_nodes += closest_nodes;

    $requests = $closest_nodes->sendFindNodeRequest($this->settings, $this->node_id);
    $requests->enqueue()->allDone([$this, 'perform']);
  }
}

?>
