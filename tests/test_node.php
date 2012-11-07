<?php

class TestKademliaNode extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia Network Test');

    $this->http_node = KademliaTestFactory::constructNode([
      'protocols' => [80 => [
        'host' => '10.0.0.100',
        'port' => 80,
        'path' => '/'
      ]]
    ]);

    $this->valid_node = KademliaTestFactory::constructNode(['id' => str_repeat('AA', N/8)]);
  }


  public function testNormalizeNodeId() {
    $id_a = str_repeat('AA', N/8);
    $id_b = str_repeat('aa', N/8);
    
    $this->assertEqual(Kademlia\Node::hexId2bin($id_a),
                       Kademlia\Node::hexId2bin($id_b));
  }


  public function testValid() {
    $this->assertTrue($this->valid_node->isValid());
  }


  public function testInvalidId() {
    $invalid_data = array('id' => 'foobar');
    $invalid_node = KademliaTestFactory::constructNode($invalid_data);
    $this->assertFalse($invalid_node->isValid());
  }


  public function testfavoriteProtocol() {
    $settings = new Kademlia\Settings;
    $protocol = $this->http_node->favoriteProtocol($settings);

    $this->assertEqual(get_class($protocol), 'Kademlia\Http');
  }


  public function testSendPingRequest() {
    $settings = new Kademlia\Settings;
    $protocol = &new MockProtocol($settings);

    $this->http_node->sendPingRequest($protocol);

    $protocol->expectOnce('sendPingRequest', [$this->http_node]);
  }


  public function testLogDistance() {
    $data_a = array('id' => str_repeat('00', N/8));
    $node_a = KademliaTestFactory::constructNode($data_a);

    $data_b = array('id' => str_repeat('FF', N/8));
    $node_b = KademliaTestFactory::constructNode($data_b);
    $this->assertEqual($node_a->LogDistanceTo($node_b), N);

    $data_b = $data_a;
    $data_b['id'][strlen($data_b['id'])-1] = '1';
    $node_b = KademliaTestFactory::constructNode($data_b);
    $this->assertEqual($node_a->LogDistanceTo($node_b), 1);

    $data_b = $data_a;
    $data_b['id'][strlen($data_b['id'])/2] = '8';
    $node_b = KademliaTestFactory::constructNode($data_b);
    $this->assertEqual($node_a->LogDistanceTo($node_b), N/2);
  }


  public function testDistance() {
    $data_a = ['id' => str_repeat('FF', N/8)];
    $node_a = KademliaTestFactory::constructNode($data_a);

    $zeros = Kademlia\Node::hexId2bin(str_repeat('00', N/8));
    $this->assertEqual($node_a->distanceTo($node_a), $zeros);


    $data_b = ['id' => str_repeat('FF', N/8)];
    $data_b['id'][2*N/8-1] = 'E';
    $node_b = KademliaTestFactory::constructNode($data_b);

    $one = Kademlia\Node::hexid2bin(str_repeat('0', 2*N/8-1).'1');
    $this->assertEqual($node_a->distanceTo($node_b), $one);
  }
}
?>
