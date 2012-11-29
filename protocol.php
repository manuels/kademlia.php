<?php

namespace Kademlia;

class Protocol {
  function __construct(&$settings) {
    $this->settings = &$settings;
  }


  public function createFindNodeResponse($needle_id, $sender_node) {
    $nodes = $this->settings->kbuckets->toNodeList()->closestNodes($needle_id, $this->settings->bucket_size);

    if($sender_node->isValid())
      $nodes = $nodes->without(new \Kademlia\NodeList([$sender_node]));
    return $nodes;
  }


  public function createFindValueResponse($key_id, $sender_node) {
    $this->removeExpiredValues();

    $found_values = [];
    if(isset($this->settings->value_storage[$key_id])) {
      foreach($this->settings->value_storage[$key_id] as $node_id => $value)
        array_push($found_values, $value['value']);
    }

    if(count($found_values) > 0)
      return ['values' => $found_values];

    return $this->createFindNodeResponse($key_id, $sender_node);
  }


  public function removeExpiredValues() {
    foreach($this->settings->value_storage as $key_id => $values) {
      foreach($values as $node_id => $v) {
        $is_expired = ($v['expire'] < time());
        if($is_expired)
          unset($this->settings->value_storage[$key_id][$node_id]);
      }
      if(count($this->settings->value_storage[$key_id]) === 0)
        unset($this->settings->value_storage[$key_id]);
    }
  }


  public function createStoreResponse($sender_node, $key_id, $value, $expire) {
    assert(is_int($expire));
    assert($expire > 0);
    assert(strlen($value) <= $this->settings->max_store);
    $expire = min($expire, $this->settings->max_expire);

    if(!isset($this->settings->value_storage[$key_id]))
      $this->settings->value_storage[$key_id] = [];

    $this->removeExpiredValues();

    $this->settings->value_storage[$key_id][$sender_node->idBin()] = [
      'value' => $value,
      'expire' => $expire+time()
    ];

    return [];
  }


  public function sendPingRequest($node) {
    throw Exception('Protocol::sendPingRequest not implemented');
  }


  public function sendFindRequest($type, $needle_id, $recipients_node_list) {
    $ns = $this->settings->protocolNamespace($this->protocol_id);

    assert(gettype($type) === 'integer');
    assert(gettype($needle_id) === 'string');
    assert(gettype($recipients_node_list) === 'object');
    assert(get_class($recipients_node_list) === 'Kademlia\NodeList');

    $args = [
      &$this->settings,
      $needle_id,
      $recipients_node_list
    ];

    assert($ns !== '');

    $type_str = ($type === \Kademlia\Find::NODE ? 'Node' : 'Value');
    $reflect = new \ReflectionClass('Kademlia\\'.$ns.'\\Find'.$type_str);
    $instance = $reflect->newInstanceArgs($args);

    return $instance;
  }
}

?>
