<?php

class TestKademliaPing extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia Ping Test');
  }

  
  public function testPingForwardsToNode() {
    $settings = new Kademlia\Settings;
    $mock_node = &new MockNode;
    $mock_task = &new MockTask;

    $mock_node->returns('sendPingRequest', $mock_task);

    $task = new Kademlia\Ping($settings, $mock_node);
    $task->enqueue();

    $mock_node->expectOnce('sendPingRequest');
    $mock_task->expect('done', []);
    $mock_task->expectOnce('done', [$task, 'evaluate']);
  }
}

?>
