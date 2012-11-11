<?php

namespace Kademlia;

class FindValue extends Find {
  public $type = Find::VALUE;

  public function initiateCaching() {
    # TODO: caching is currently not implemented
    return;
    $count = $this->settings->bucket_size;
    $closest_nodes = $this->found_nodes->closestNodes($this->needle_id, $count+1);
    $very_closest_nodes = $this->found_nodes->closestNodes($this->needle_id, $count);
    
    $near_node = $closest_nodes->without($very_closest_nodes);
    $task = $closest_nodes->sendStoreRequest($this->settings, $this->needle_id, current($new_values));
  }

  public function idFound($node_list, $new_values) {
    if(count($new_values) > 0) {
      $this->emit('success', $this->values);
      $this->initiateCaching();
      return true;
    }

    $unasked_nodes = $node_list->without($this->asked_nodes);
    if($unasked_nodes->size() === 0) {
      $this->emit('done', $this->asked_nodes);
      return true;
    }

    return false;
  }
}

?>
