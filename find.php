<?php

namespace Kademlia;

abstract class Find extends Task {
  const NODE  = 1;
  const VALUE = 2;

  function __construct(&$settings, $key_id, $initial_node_list = NULL) {
    parent::__construct($settings);

    if($initial_node_list === NULL)
      $this->initial_node_list = $settings->kbuckets->closestNodes($key_id, $settings->alpha);
    else
      $this->initial_node_list = $initial_node_list;

    assert(get_class($this->initial_node_list) === 'Kademlia\NodeList');

    $this->settings = &$settings;
    $this->needle_id = Node::hexId2bin($key_id);
    $this->asked_nodes = new NodeList([]);
    $this->found_nodes = new NodeList([]);
    $this->hop = 0;
    $this->values = [];
    $this->previous_distance = NULL;
    $this->min_distance = str_repeat(chr(255), N/8);
  }


  public function enqueueSelf() {
    return $this->enqueueSelfPrototype();
  }


  abstract function idFound($node_list, $new_values);


  public function mergeNodeLists($results) {
    $node_list = new NodeList([]);
    foreach($results as $protocol_result) {
      foreach($protocol_result as $node_result) {
        if(isset($node_result['node_list'])) {
          $res = $node_result['node_list'];
          assert(get_class($res) === 'Kademlia\NodeList');

          foreach($res->toArray() as $n)
            assert(get_class($n) === 'Kademlia\Node' or get_class($n) === 'MockNode');
          $node_list->addNodeList($res);
        }
      }
    }
    return $node_list;
  }


  public function perform($results = NULL) {
    $needle_id = $this->needle_id;

    if($this->settings->verbosity >= 2) {
      print Node::binId2hex($this->settings->own_node_id).": FindNode got these results\n";
      print_r($results);
      print Node::binId2hex($this->settings->own_node_id).": end of FindNode results\n";
    }

    if($results === NULL) {
      $new_values = [];
      $node_list = $this->initial_node_list;
    }
    else {
      # merge subresults
      $node_list = $this->mergeNodeLists($results);

      $new_values = [];
      foreach($results as $protocol_results) {
        foreach($protocol_results as $node_result) {
          if(isset($node_result['values']))
            $new_values = array_merge($new_values, $node_result['values']);
        }
      }
      $this->settings->kbuckets->nodeListOnline($node_list);
      $this->found_nodes->addNodeList($node_list);

      assert(get_class($node_list) === 'Kademlia\NodeList');
      if($this->found_nodes->size() === 0) {
        $this->previous_distance = $this->min_distance;
      }

      if($this->found_nodes->size() > 0) {
        $this->previous_distance = $this->min_distance;
        $closest_node = $this->found_nodes->closestNodes($needle_id, 1)->toArray()[0];

        #print_r($closest_node);

        $this->min_distance = $closest_node->distanceTo($needle_id);
        if(!is_string($this->min_distance)) {
          print Node::binId2hex($needle_id)." ".$needle_id." ".gettype($needle_id)."\n";
          print Node::binId2hex($this->min_distance)." ".$this->min_distance." ".gettype($this->min_distance)."\n";
          assert(false);
        }
      }
      assert(is_string($this->min_distance));
      assert(is_string($this->previous_distance));

      array_merge($this->values, $new_values);
      if($this->idFound($node_list, $new_values)) {
        return;
      }
    }

    $query_count = ($this->hop === 0 ? $this->settings->alpha : $this->settings->bucket_size);

    $unasked_nodes = $node_list->without($this->asked_nodes);
    $closest_nodes = $unasked_nodes->closestNodes($needle_id, $query_count);

    $this->hop++;
    $this->asked_nodes->addNodeList($closest_nodes);

    $requests = $closest_nodes->sendFindRequest($this->type, $this->settings, $needle_id);
    $requests->enqueue()->allDone([$this, 'perform']);
  }
}

?>
