<?php

class TestKademliaNodeList extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia Node List Test');

    $this->node_array = [];
    for($i = 0; $i < 20; $i++)
      array_push($this->node_array, KademliaTestFactory::constructNode());
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

    $node_list_a = new Kademlia\NodeList($included_nodes + $intersection_nodes);
    $node_list_b = new Kademlia\NodeList($excluded_nodes + $intersection_nodes);
    $without = $node_list_a->without($node_list_b);

    $actual_count = count($without->toArray());
    $expected_count = count($included_nodes);
    $this->assertEqual($actual_count, $expected_count);
  }


  public function testContainsNodeId() {
    $contained_node = $this->node_array[0];
    $not_contained_node = KademliaTestFactory::constructNode();

    $node_list = new Kademlia\NodeList($this->node_array);

    $this->assertTrue($node_list->containsNodeId($contained_node->idBin()));
    $this->assertFalse($node_list->containsNodeId($not_contained_node->idStr()));
  }


  public function testClosestNodes() {
    $zeros = str_pad('', 2*N/8, '0');

    $node_array = [];
    for($i = 0; $i < N/8; $i++) {
      $id = str_pad('', 2*N/8, '0');
      $id[2*$i+1] = '1';
      array_push($node_array, KademliaTestFactory::constructNode(['id' => $id]));
    }

    $expected_nearest_node = $node_array[0];
    $expected_second_nearest_node = $node_array[1];
    shuffle($node_array);

    $node_list = new Kademlia\NodeList($node_array);
    $two_closest_nodes = $node_list->closestNodes($zeros, 2);
    $nearest_node = $two_closest_nodes[0];
    $second_nearest_node = $two_closest_nodes[1];

    $this->assertEqual($nearest_node->idStr(), $expected_nearest_node->idStr());
    $this->assertEqual($second_nearest_node->idStr(), $expected_second_nearest_node->idStr());
  }
}
?>
