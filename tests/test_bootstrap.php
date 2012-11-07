<?php

class TestKademliaBootstrap extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia Bootstrap Test');

    $this->node_list = $this->generateBootstrapNodeList();
  }

  
  private function generateBootstrapNodeList() {
    $node = new Kademlia\Node(['id' => str_repeat('F', N/8)]);
    return new Kademlia\NodeList([$node]);
  }


  public function testSetsOwnNodeIdWhenNodeNotFound() {
    $settings = new Kademlia\Settings;
    $settings->id = NULL;

    $bootstrap = new Kademlia\Bootstrap($settings, $this->node_list);

    $callback = new TestCallback;
    $bootstrap->enqueue()->done([$callback, 'callMe']);

    var_dump($bootstrap->find_node_task);
    $bootstrap->find_node_task->emit('success', Kademlia\Node::randomNodeId());
  }
}

?>
