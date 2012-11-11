<?php

class TestKademliaProtocol extends UnitTestCase {
  function __construct() {
    parent::__construct('Kademlia HTTP Test');
  }


  public function testFindNodeResponse() {
    return; #####################################
    $zeros = str_repeat('00', N/8);
    $FFs = str_repeat('FF', N/8);

    $settings = new Kademlia\Settings;
    $settings->own_node_id = $FFs;
    $protocol = new Kademlia\Protocol($settings);

    # fill kbuckets with some nodes
    $node_list = new Kademlia\NodeList([]);
    for($i = 0; $i < N/8; $i++) {
      $node_id = $zeros;
      $hex = str_pad(dechex($i), 2, '0', STR_PAD_LEFT);
      $node_id[2*$i]   = $hex[0];
      $node_id[2*$i+1] = $hex[1];

      for($j = $i+1; $j < N/8; $j++) {
        $hex = str_pad(dechex($j), 2, '0', STR_PAD_LEFT);
        $node_id[2*$j]   = $hex[0];
        $node_id[2*$j+1] = $hex[1];
      
        $data = ['id' => $node_id];
        $node = new Kademlia\Node($data);

        $node_list->addNode($node);
        $settings->kbuckets->nodeOnline($node);
      }
    }

    $response = $protocol->createFindResponse($zeros);

    $expected_ids = [
      "0000000000000000000000000000000000001213",
      "0000000000000000000000000000000000111200",
      "0000000000000000000000000000000000111213",
      "0000000000000000000000000000000010110000",
      "0000000000000000000000000000000010111200",
      "0000000000000000000000000000000010111213",
      "0000000000000000000000000000000f10000000",
      "0000000000000000000000000000000f10110000",
      "0000000000000000000000000000000f10111200",
      "0000000000000000000000000000000f10111213",
      "00000000000000000000000000000e0f00000000",
      "00000000000000000000000000000e0f10000000",
      "00000000000000000000000000000e0f10110000",
      "00000000000000000000000000000e0f10111200",
      "00000000000000000000000000000e0f10111213",
      "000000000000000000000000000d0e0000000000",
      "000000000000000000000000000d0e0f00000000",
      "000000000000000000000000000d0e0f10000000",
      "000000000000000000000000000d0e0f10110000",
      "000000000000000000000000000d0e0f10111200"
    ];

    $getId = function($node) { return $node->idStr(); };
    $node_ids = array_map($getId, $response['nodes']->toArray());

    $this->assertEqual($node_ids, $expected_ids);
  }
}

?>
