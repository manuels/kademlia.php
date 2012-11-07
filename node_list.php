<?php

namespace Kademlia;

class NodeList {
  function __construct($node_array) {
    $this->node_array = $node_array;
  }

  
  public function containsNodeId($needle_id) {
    $needle_id = Node::hexId2bin($needle_id);

    foreach($this->node_array as $other_node) {
      if($needle_id === $other_node->idBin())
        return true;
    }
    return false;
  }


  public function toArray() {
    return $this->node_array;
  }


  public function without($other_node_list) {
    $this_node_array = $this->toArray();
    $other_node_array = $other_node_list->toArray();

    $without = [];
    foreach($this_node_array as $own_node) {
      $found = false;
      foreach($other_node_array as $other_node) {
        if($own_node->idBin() === $other_node->idBin()) {
          $found = true;
          break;
        }
      }
      if(!$found)
        array_push($without, $own_node);
    }
    return new NodeList($without);
  }


  public function groupByProtocols() {
    $groups = [];

    # TODO: write a test
    foreach($this->nodes as $n) {
      $prot_id = $n->favouriteProtocolId();

      if(!isset($groups[$prot_id]))
        $groups[$prot_id] = [];
      array_push($groups[$prot_id], $n);
    }
    return $groups;
  }


  public function sendFindNodeRequest($settings, $node_id) {
    $protocol_groups = $this->groupByProtocols();

    $task_group = new TaskGroup($settings);
    foreach($protocol_groups as $prot_id => $nodes) {
      $protocol = Protocol::instantiateByProtocolId($settings, $prot_id);
      $task = $protocol->sendFindNodeRequest($node_id);
      $task_group->add($task_group);
    }
    return $task_group;
  }


  public function closestNodes($needle_id, $count) {
    $cmp = function ($node_a, $node_b) use ($needle_id) {
      $dist_a = $node_a->distanceTo($needle_id);
      $dist_b = $node_b->distanceTo($needle_id);
      if($dist_a === $dist_b)
        return 0;

      return ($dist_a < $dist_b ? -1 : +1);
    };

    usort($this->node_array, $cmp);
    return array_slice($this->node_array, 0, $count);
  }
}

?>
