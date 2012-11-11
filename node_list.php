<?php

namespace Kademlia;

class NodeList {
  function __construct($node_array = []) {
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


  public function addNodeList($node_list) {
    return $this->addNodeArray($node_list->toArray());
  }


  public function addNodeArray($node_array) {
    foreach($node_array as $node) {
      $this->addNode($node);
    }
  }


  public function addNode($new_node) {
    $found = false;
    foreach($this->node_array as $node)
      if($node->idBin() === $new_node->idBin()) {
        $found = true;
        break;
      }
    if(!$found)
      array_push($this->node_array, $new_node);
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


  public function groupByProtocols($settings) {
    $groups = [];

    foreach($this->node_array as $node) {
      $prot_id = $node->favoriteProtocolId($settings);
      if(!isset($groups[$prot_id]))
        $groups[$prot_id] = [];
      array_push($groups[$prot_id], $node);
    }

    foreach($groups as $protocol_id => $node_array) {
      $groups[$protocol_id] = new NodeList($node_array);
    }

    return $groups;
  }


  public function sendFindNodeRequest(&$settings, $node_id) {
    return $this->sendFindRequest(Find\NODE, $settings, $node_id);
  }


  public function sendFindValueRequest(&$settings, $node_id) {
    return $this->sendFindRequest(Find\VALUE, $settings, $node_id);
  }


  public function sendFindRequest($type, &$settings, $node_id) {
    $protocol_groups = $this->groupByProtocols($settings);
    unset($protocol_groups['']);

    $task_group = new TaskGroup($settings);
    foreach($protocol_groups as $prot_id => $node_list) {
      $protocol = $settings->instantiateProtocolById($prot_id);

      $task = $protocol->sendFindRequest($type, $node_id, $node_list);
      $task_group->add($task);
    }
    return $task_group;
  }


  public function sendStoreRequest($settings, $key_id, $value) {
    $protocol_groups = $this->groupByProtocols($settings);
    unset($protocol_groups['']);

    $task_group = new TaskGroup($settings);
    foreach($protocol_groups as $prot_id => $nodes) {
      $protocol = &$settings->instantiateProtocolById($prot_id);
      $task = $protocol->sendStoreRequest($key_id, $value);
      $task_group->add($task);
    }
    return $task_group;
  }


  public function size() {
    return count($this->node_array);
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
    $nodes = array_slice($this->node_array, 0, $count);

    return new NodeList($nodes);
  }
}

?>
