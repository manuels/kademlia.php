<?php

namespace Kademlia;

class Settings {
  public $own_node_id = '';

  public $queue_system = 'sync';

  public $bucket_size = 20;

  public $supported_protocols = [80];

  # query $alpha many new nodes each hop
  public $alpha = 3;
}

?>
