<?php

namespace Kademlia;

class FindValue extends Task {
  function __construct($settings, $key_id) {
    parent::__construct();

    $nodes_to_ask = $kbuckets->closestNodes($key_id);
    enqueue('perform', $settings, $key_id, $nodes_to_ask)
  }

  static function perform($settings, $key_id, $new_nodes_list, $polled_nodes = [], $new_values = [], $values = []) {
    
  }
}

?>
