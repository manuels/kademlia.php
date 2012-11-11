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
    print "Bootstrap::enqueueSelf\n";
    $this->find_node_task->enqueue()->done([$this, 'perform']);

    return $this;
  }


  public function perform($results) {
    $node_list = new NodeList([]);
    print "Bootstrap::Perfrom\n";

    foreach($results as $nodes) {
      $node_list->addNodeList($nodes);
    }

    if($node_list->containsNodeId($this->settings->own_node_id)) {
      $this->settings->own_node_id = Node::randomNodeId();

      $this->find_node_task = new Bootstrap($this->settings, $this->bootstrap_nodes);
      print "Bootstrap::Perfrom A\n";
      $this->find_node_task->enqueue()->done([$this, 'emitSuccess']);
    }
    else {
      print "Bootstrap::Perfrom B\n";
      $this->emitSuccess($this->settings->own_node_id);
    }
  }


  public function emitSuccess($own_node_id) {
    $this->settings->own_node_id = $own_node_id;
    $this->emit('success', $own_node_id);

    $kbuckets_size = $this->settings->kbuckets->toNodeList()->size();
    print Node::binId2hex($this->settings->own_node_id)." is done with bootstrapping and populating it's address (kbuckets: ".$kbuckets_size.")\n";

    $z = 0;
    for($i = 0; $i < N/8; $i++) {
      for($j = 0; $j < 8; $j++) {
        $node_id = $own_node_id;

        $byte = $node_id[$i];
        $byte ^= chr(1 << $j);
        $node_id[$i] = $byte;

        $task = new FindNode($this->settings, $node_id);
        $task->enqueue();

        $z++;
        if($z == 2)
          return; ##################################################
      }
    }
  }
}

?>
