<?php

namespace Kademlia;

class Settings {
  public $own_node_id = '';

  public $queue_system = 'sync';

  public $bucket_size = 20;

  public $supported_protocols = [80=>[]];

  # query $alpha many new nodes each hop
  public $alpha = 3;

  public $kbuckets;

  public $value_storage = [];

  public $verbosity = 0;

  function __construct() {
    $this->kbuckets = new KBuckets($this);
  }


  public function protocolInfo($prot_id) {
    if(!isset($this->supported_protocols[$prot_id]))
      return NULL;
    return $this->supported_protocols[$prot_id];
  }


  public function protocolNamespace($protocol_id) {
    switch($protocol_id) {
      case 80:
        return 'Http';
      case -80:
        return 'MockHttp';
    }
  }
 
  
  public function instantiateProtocolById($prot_id) {
    $ns = $this->protocolNamespace($prot_id);

    $reflect = new \ReflectionClass('Kademlia\\'.$ns.'\\Protocol');
    $instance = $reflect->newInstanceArgs([&$this]);

    return $instance;
  }
}

?>
