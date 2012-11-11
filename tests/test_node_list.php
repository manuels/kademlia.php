<?php

class TestKademliaNodeList extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia Node List Test');

    $this->node_array = [];
    for($i = 0; $i < 20; $i++)
      array_push($this->node_array, KademliaTestFactory::constructNode());
  }


  public function testAddNodeList() {
    $node = KademliaTestFactory::constructNode();

    $node_list_a = new Kademlia\NodeList([$node]);
    $node_list_b = new Kademlia\NodeList();
    
    $node_list_b->addNodeList($node_list_a);
    
    $this->assertEqual($node_list_a->size(), $node_list_b->size());
  }    


  public function testWithout() {
    $included_nodes = [
      KademliaTestFactory::constructNode(),
      KademliaTestFactory::constructNode(),
      KademliaTestFactory::constructNode(),
      KademliaTestFactory::constructNode()
    ];

    $excluded_nodes = [
      KademliaTestFactory::constructNode(),
      KademliaTestFactory::constructNode(),
      KademliaTestFactory::constructNode()
    ];

    $intersection_nodes = [
      KademliaTestFactory::constructNode(),
      KademliaTestFactory::constructNode()
    ];

    $node_list_a = new Kademlia\NodeList(array_merge($included_nodes, $intersection_nodes));
    $node_list_b = new Kademlia\NodeList(array_merge($excluded_nodes, $intersection_nodes));
    $without = $node_list_a->without($node_list_b);

    $actual_count = count($without->toArray());
    $expected_count = count($included_nodes);
    $this->assertEqual($actual_count, $expected_count);
  }


  public function testGroupByProtocols() {
    $settings = new Kademlia\Settings;
    $settings->supported_protocols = [1=>[], 2=>[], 3=>[]];

    $protocol_one_nodes = [];
    for($i = 0; $i < 10; $i++)
      $protocol_one_nodes[$i] = KademliaTestFactory::constructNode(
        [ 'protocols'=> [ 1 => [] ] ]);

    $protocol_two_nodes = [];
    for($i = 0; $i < 20; $i++)
      $protocol_two_nodes[$i] = KademliaTestFactory::constructNode(
        [ 'protocols'=> [ 2 => [] ] ]);

    $protocol_three_nodes = [];
    for($i = 0; $i < 30; $i++)
      $protocol_three_nodes[$i] = KademliaTestFactory::constructNode(
        [ 'protocols'=> [ 3 => [] ] ]);
    
    $node_list = new Kademlia\NodeList(array_merge($protocol_one_nodes, $protocol_two_nodes, $protocol_three_nodes));
    
    $groups = $node_list->groupByProtocols($settings);

    $this->assertEqual(array_keys($groups), [1,2,3]);
    $this->assertEqual($groups[1]->size(), count($protocol_one_nodes));
    $this->assertEqual($groups[2]->size(), count($protocol_two_nodes));
    $this->assertEqual($groups[3]->size(), count($protocol_three_nodes));
  }


  public function testContainsNodeId() {
    $contained_node = $this->node_array[0];
    $not_contained_node = KademliaTestFactory::constructNode();

    $node_list = new Kademlia\NodeList($this->node_array);

    $this->assertTrue($node_list->containsNodeId($contained_node->idBin()));
    $this->assertFalse($node_list->containsNodeId($not_contained_node->idStr()));
  }


  public function testClosestNodes() {
    $zeros = str_repeat('00', N/8);

    $node_array = [];
    for($i = 0; $i < N/8; $i++) {
      $id = str_repeat('00', N/8);
      $id[2*$i+1] = '1';
      array_push($node_array, KademliaTestFactory::constructNode(['id' => $id]));
    }

    $expected_nearest_node_id = str_repeat('00', N/8-1).'01';
    $expected_second_nearest_node_id = str_repeat('00', N/8-2).'0100';
    shuffle($node_array);

    $node_list = new Kademlia\NodeList($node_array);
    $two_closest_nodes = $node_list->closestNodes($zeros, 2)->toArray();
    $nearest_node = $two_closest_nodes[0];
    $second_nearest_node = $two_closest_nodes[1];

    $this->assertNotEqual($nearest_node->idStr(), $second_nearest_node->idStr());

    $this->assertEqual($nearest_node->idStr(), $expected_nearest_node_id);
    $this->assertEqual($second_nearest_node->idStr(), $expected_second_nearest_node_id);
  }
}
?>
