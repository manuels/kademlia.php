<?php

namespace Kademlia;

class KBuckets implements \JsonSerializable {
  function __construct(&$settings) {
    $this->settings = $settings;
    $this->buckets = array_fill(0, N, NULL);
  }


  public function nodeListOnline($node_list) {
    foreach($node_list->toArray() as $node)
      $this->nodeOnline($node);
  }


  public function nodeOffline($node) {
    $bucket_id = $node->logDistanceTo($this->settings->own_node_id)-1;
    if($bucket_id < 0)
      return;

    if($this->buckets[$bucket_id] === NULL)
      return;

    $res = $this->buckets[$bucket_id]->removeNode($node);
    return $res;
  }


  public function nodeOnline($node) {
    if(empty($this->settings->own_node_id))
      return;

#    print Node::binId2hex($this->settings->own_node_id)." saw ".$node->idStr()."\n";

    $bucket_id = $node->logDistanceTo($this->settings->own_node_id)-1;
    if($bucket_id < 0)
      return;

    if($this->buckets[$bucket_id] === NULL)
      $this->buckets[$bucket_id] = &new Bucket($this->settings);

    $res = $this->buckets[$bucket_id]->addNode($node);
    return $res;
  }


  public function toNodeList() {
    $nodes = new NodeList([]);
    foreach($this->buckets as $id => $bucket) {
      if($bucket !== NULL)
        $nodes->addNodeList( $bucket->toNodeList() );
    }
    return $nodes;
  }

 
  public function closestNodes($needle_id, $count) {
    return $this->toNodeList()->closestNodes($needle_id, $count);
  }


  public function jsonSerialize() {
    return "TODO: Implement KBuckets::jsonSerialize";
  }
};

?>
