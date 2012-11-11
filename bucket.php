<?php

namespace Kademlia;

class Bucket implements \JsonSerializable {
  function __construct(&$settings) {
    $this->settings = &$settings;
    $this->nodes = [];
  }


  public function replaceNode($new_node, $old_node_id) {
    assert($new_node->isValid());

    $pos = $this->positionOfNodeId($old_node_id);
    unset($this->nodes[$pos]);

    array_push($this->nodes, $new_node);
  }


  public function addNode($new_node, $ping = NULL) {
    $settings = $this->settings;

    if((!$new_node->isValid()) || ($new_node->idBin() === $settings->own_node_id))
      return false;

    $found = $this->positionOfNodeId($new_node);
    if($found !== FALSE) {
      # node with the same node id found
      $pos = $found;
      $same_node = $this->nodes[$pos];
      $nodes_are_identical = ($same_node->host() === $new_node->host());

      if($nodes_are_identical) {
        unset($this->nodes[$pos]);
        array_unshift($this->nodes, $same_node);
      }
      return false;
    }

    if(count($this->nodes) < $settings->bucket_size) {
      array_unshift($this->nodes, $new_node);
      return false;
    }

    $last_node = end($this->nodes);
    $task = ($ping === NULL ? new Ping($settings, $last_node) : $ping);
    $task->failed(get_class($this), 'replaceNode', $new_node, $last_node->idBin());

    return true;
  }


  public function toNodeList() {
    return new NodeList($this->nodes);
  }


  public function positionOfNodeId($needle_node) {
    if(is_string($needle_node))
      $needle_node = (new Node(['id' => $needle_node]));
    $needle_node_id = $needle_node->idBin();

    $pos = FALSE;
    foreach($this->nodes as $key => $node)
      if($needle_node_id === $node->idBin()) {
        $pos = $key;
        break;
      }
    return $pos;
  }


  public function jsonSerialize() {
    return "TODO: Implement KBuckets::jsonSerialize";
  }
};

?>
