<?php

class TestKademliaFindValue extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia FindValue Test');
  }


  public function testEmitDoneWhenNoNewNodesFound() {
    $settings = new Kademlia\Settings;
    $settings->own_node_id = Kademlia\Node::randomNodeId();

    $callback_done = &new TestCallback;
    $callback_success = &new TestCallback;

    $task = new Kademlia\FindValue($settings, Kademlia\Node::randomNodeId(), new Kademlia\NodeList([]));
    $task->done([$callback_done, 'callme']);
    $task->done([$callback_success, 'callme']);

    $task->perform([[['node_list' =>new Kademlia\NodeList([])]]]);

    $callback_done->expectOnce('callme');
    $callback_success->expectNever('callme');
  }


  public function testEmitSuccessWhenValueFound() {

    $settings = new Kademlia\Settings;

    $callback_done = &new TestCallback;
    $callback_success = &new TestCallback;

    $node = KademliaTestFactory::constructNode();

    $task = new Kademlia\FindValue($settings, $node->idBin(), new Kademlia\NodeList([]));
    $task->done([$callback_done, 'callme']);
    $task->done([$callback_success, 'callme']);

    $task->perform([[['node_list' => new Kademlia\NodeList([$node]), 'values' => ['abc'=>'d']]]]);

    $callback_success->expectOnce('callme');
    $callback_done->expectOnce('callme');
  }


  public function testCallsFindValueForNodes() {
    print "Skipping FindValue tests...\n";
    return;

    $mock_protocol = &new MockProtocol();
    $mock_task = &new MockTask();
    $mock_settings = &new MockSettings();

    $mock_settings->returns('instantiateProtocolById', $mock_protocol);
    $mock_protocol->setReturnValue('sendFindRequest', $mock_task);

    $mock_nodes = [];
    $M = $mock_settings->alpha+10;
    for($i = 0; $i < $M; $i++) {
      $mock_nodes[$i] = &new MockNode;
      $mock_nodes[$i]->returns('favoriteProtocolId', 0);

      $mock_node_id = str_repeat('00', N/8);
      $mock_node_id[N/8-1-$i] = '1';

      $mock_nodes[$i]->returns('idBin', Kademlia\Node::hexId2bin($mock_node_id));
    }

    $needle_id = str_repeat('00', N/8);
    $needle_id = Kademlia\Node::hexId2bin($needle_id);

    $task = new Kademlia\FindValue($mock_settings, $needle_id, new Kademlia\NodeList($mock_nodes));
    $task->enqueue();

    $mock_protocol->expectOnce('sendFindRequest', [Kademlia\Find::VALUE, array_slice($mock_nodes, 0, $mock_settings->alpha)]);
  }
}

?>
