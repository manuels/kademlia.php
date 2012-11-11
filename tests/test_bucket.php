<?php

class TestKademliaBucket extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia Bucket Test');
    $settings = new Kademlia\Settings;
    $settings->own_node_id = Kademlia\Node::randomNodeId();

    $this->settings = $settings;
  }



  public function testAddingWhenFullTriggersPing() {
    $settings = $this->settings;
    $bucket = new Kademlia\Bucket($settings);

    $ping_task = &new MockPing();

    $first_node = KademliaTestFactory::constructNode();
    $bucket->addNode($first_node, $ping_task);

    for($i = 0; $i < ($settings->bucket_size-1); $i++) {
      $node = KademliaTestFactory::constructNode();
      $bucket->addNode($node, $ping_task);
    }

    $another_node = KademliaTestFactory::constructNode();
    $ping_task->expectOnce('failed', ['Kademlia\Bucket', 'replaceNode', $another_node, $first_node->idBin()]);
    $bucket->addNode($another_node, $ping_task);

    $this->assertFalse($bucket->positionOfNodeId($another_node));
  }


  public function testReAddingMovesToFront() {
    $settings = $this->settings;
    $bucket = new Kademlia\Bucket($settings);

    $first_node = KademliaTestFactory::constructNode();
    $bucket->addNode($first_node);

    for($i = 0; $i < ($settings->bucket_size-1); $i++) {
      $node = KademliaTestFactory::constructNode();
      $bucket->addNode($node);
    }

    $this->assertNotEqual($bucket->positionOfNodeId($first_node), 0);

    $bucket->addNode($first_node);
    $this->assertEqual($bucket->positionOfNodeId($first_node), 0);
  }


  public function testAddingOwnNodeIdDoesNothing() {
    $settings = $this->settings;

    $bucket = new Kademlia\Bucket($settings);

    $first_node = KademliaTestFactory::constructNode();
    $bucket->addNode($first_node);

    $self_node = KademliaTestFactory::constructNode(['id' => $settings->own_node_id]);
    $bucket->addNode($self_node);

    $this->assertEqual($bucket->positionOfNodeId($first_node), 0);
    $this->assertFalse($bucket->positionOfNodeId($self_node));
  }


  public function testAddingInvalidNodeIdDoesNothing() {
    $settings = $this->settings;

    $bucket = new Kademlia\Bucket($settings);

    $first_node = KademliaTestFactory::constructNode();
    $bucket->addNode($first_node);

    $invalid_node = KademliaTestFactory::constructNode(['id' => 'foobar']);
    $bucket->addNode($invalid_node);

    $this->assertEqual($bucket->positionOfNodeId($first_node), 0);
    $this->assertFalse($bucket->positionOfNodeId($invalid_node));
  }
}
?>
