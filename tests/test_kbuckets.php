<?php
class TestKademliaKBuckets extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia KBuckets Test');
  }


  public function testToNodeArray() {
    $settings = new Kademlia\Settings();
    $zeros = str_repeat('00', N/8);
    $settings->own_node_id = $zeros;
    $kbuckets = new Kademlia\KBuckets($settings);

    $node = KademliaTestFactory::constructNode();
    $kbuckets->nodeOnline($node);

    $this->assertEqual($kbuckets->toNodeList()->size(), 1);
  }


  public function testClosestNodes() {
    $zeros = str_repeat('00', N/8);

    $settings = new Kademlia\Settings();
    $settings->own_node_id = $zeros;
    $kbuckets = new Kademlia\KBuckets($settings);

    for($i = 0; $i < N/8; $i++) {
      $id = str_repeat('00', N/8);
      $id[2*$i+1] = '1';
      $node = KademliaTestFactory::constructNode(['id' => $id]);
      $kbuckets->nodeOnline($node);
    }

    $expected_nearest_node_id = str_repeat('00', N/8-1).'01';
    $expected_second_nearest_node_id = str_repeat('00', N/8-2).'0100';

    $two_closest_nodes = $kbuckets->closestNodes($zeros, 2)->toArray();
    $nearest_node = $two_closest_nodes[0];
    $second_nearest_node = $two_closest_nodes[1];

    $this->assertNotEqual($nearest_node->idStr(), $second_nearest_node->idStr());

    $this->assertEqual($nearest_node->idStr(), $expected_nearest_node_id);
    $this->assertEqual($second_nearest_node->idStr(), $expected_second_nearest_node_id);
  }
}

?>
