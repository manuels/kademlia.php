<?php

namespace Kademlia;

class KBuckets implements \JsonSerializable {
  function __construct(&$settings) {
    $this->buckets = array_fill(0, N, new Buckets($settings));
  }

  public function add($node) {
    $bucket_id = $node->logDistanceTo($own_node_id);
    $this->buckets[$bucket_id]->add($node);
  }

  public function jsonSerialize() {
    return "TODO: Implement KBuckets::jsonSerialize";
  }
};

?>
