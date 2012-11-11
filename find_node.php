<?php

namespace Kademlia;

class FindNode extends Find {
  public $type = Find::NODE;

  public function idFound($node_list, $new_values__ignore) {
    print "idFound 1\n";
    if($node_list->containsNodeId($this->needle_id)) {
      $this->emit('success', $this->asked_nodes);
      return true;
    }
    print "idFound 2\n";

#    if($this->min_distance === $this->previous_distance) {
#      $this->emit('done', $this->asked_nodes);
#      return true;
#    }
    print "idFound 3\n";

    return false;
  }
}

?>
