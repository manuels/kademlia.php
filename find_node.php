<?php

namespace Kademlia;

class FindNode extends Find {
  public $type = Find::NODE;

  public function idFound($node_list, $new_values__ignore) {
    if($node_list->containsNodeId($this->needle_id)) {
      $this->emit('success', $this->asked_nodes);
      return true;
    }

    if($this->min_distance === $this->previous_distance) {
      $this->emit('done', $this->asked_nodes);
      return true;
    }

    return false;
  }
}

?>
