<?php

namespace Kademlia;

class Settings implements \JsonSerializable {
  public $own_node_id = '';

  public $queue_system = 'sync';

  public $bucket_size = 20;

  public $supported_protocols = [80=>[]];

  # query $alpha many new nodes each hop
  public $alpha = 3;

  public $kbuckets;

  public $value_storage = [];

  public $value_storage_limit = 52428800; // 50 MB

  public $verbosity = 0;

  public $max_expire = 43200; # 12h in seconds

  public $max_store = 4096; # max value for one key in bytes


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


  public function fromJson($json) {
    $data = json_decode($json, true);
    assert($data !== NULL);

    $this->own_node_id = Node::hexId2bin($data['own_node_id']);
    foreach($data['kbuckets'] as $node_data) {
      $node = new Node($node_data);
      $this->kbuckets->nodeOnline($node);
    }
    if(isset($data['supported_protocols']))
      $this->supported_protocols = $data['supported_protocols'];

    if(isset($data['value_storage'])) {
      $values = [];
      foreach($data['value_storage'] as $hex1 => $array) {
        $key1 = Node::hexId2bin($hex1);
        $values[$key1] = [];
        foreach($array as $hex2 => $data) {
          $key2 = Node::hexId2bin($hex2);
          $values[$key1][$key2] = [
            'value' => base64_decode($data['value']),
            'expire' => $data['expire'],
          ];
        }
      }
      $this->value_storage = $values;
    }
  }


  public function jsonSerialize() {
    $values = [];
    foreach($this->value_storage as $bin1 => $array) {
      $key1 = Node::binId2hex($bin1);
      $values[$key1] = [];
      foreach($array as $bin2 => $data) {
        $key2 = Node::binId2hex($bin2);
        $values[$key1][$key2] = [
          'value' => base64_encode($data['value']),
          'expire' => $data['expire'],
        ];
      }
    }

    $data = [
      'kbuckets'      => $this->kbuckets->toNodeList()->toArray(),
      'own_node_id'   => Node::binId2hex($this->own_node_id),
      'value_storage' => $values,
      'supported_protocols' => $this->supported_protocols,
    ];
    return $data;
  }


  public function valueStorageSize() {
    $size = 0;
    foreach($this->value_storage as $value_id => $values) {
      foreach($values as $node_id => $pair) {
        $size += $pair['value'];
      }
    }
    return $size;
  }
}

?>
