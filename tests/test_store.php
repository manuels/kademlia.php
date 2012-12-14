<?php

class TestKademliaStore extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia Store Test');
  }

  public function testRawStoring() {
    $settings = new \Kademlia\Settings();
    $protocol = new \Kademlia\Http\Protocol($settings);

    $expire = 10;

    $sender_node = KademliaTestFactory::constructNode();
    $key_id_A = \Kademlia\Node::randomNodeId();
    $value = 'foobar';
    $protocol->createStoreResponse($sender_node, $key_id_A, $value, $expire);

    $key_id_B = \Kademlia\Node::randomNodeId();
    $protocol->createStoreResponse($sender_node, $key_id_B, $value, $expire);

    $sender_node = KademliaTestFactory::constructNode();
    $value = 'barfoo';
    $protocol->createStoreResponse($sender_node, $key_id_B, $value, $expire);

    $this->assertEqual(count($settings->value_storage), 2);

    $response = $protocol->createFindValueResponse($key_id_B, $sender_node);
    $this->assertEqual($response['values'], ['foobar', 'barfoo']);

    $response = $protocol->createFindValueResponse($key_id_A, $sender_node);
    $this->assertEqual($response['values'], ['foobar']);
  }
}

?>
