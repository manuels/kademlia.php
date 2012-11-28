<?php

namespace Kademlia;

class Protocol {
  function __construct(&$settings) {
    $this->settings = &$settings;
  }


  public function createFindNodeResponse($needle_id) {
    $nodes = $this->settings->kbuckets->toNodeList()->closestNodes($needle_id, $this->settings->bucket_size);
    return $nodes;
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
