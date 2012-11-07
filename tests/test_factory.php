<?php

class KademliaTestFactory {
  static function constructNode($overwrite_data = array(), $overwrite_host = NULL) {
    $valid_host = "8.8.8.8";
    $valid_data =  [ "id" => $first_node = Kademlia\Node::randomNodeId() ];

    $host = ($overwrite_host !== NULL ? $overwrite_host : $valid_host);
    $data = array_merge($valid_data, $overwrite_data);

    return new Kademlia\Node($data, $host);
  }
}

?>
