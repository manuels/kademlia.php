<?php

namespace Kademlia;

class Instance implements \JsonSerializable {
  function __construct($json, $supported_protocols) {
    $s = new Settings();
    if($json !== "")
      $s->fromJson($json);
    $this->settings = $s;
  }


  public function bootstrap($bootstrap_nodes = NULL) {
    $nodes = $this->settings->kbuckets->toNodeList();
    if($bootstrap_nodes !== NULL)
      $nodes->addNodeList($bootstrap_nodes);

    $task = new Bootstrap($this->settings, $nodes);
    return $task->enqueue();
  }


  public function processRequest($protocol_id, $request) {
    $prot = $this->settings->instantiateProtocolById($protocol_id);
    return $prot->processRequest($request);
  }
  

  public function findValue($needle_id) {
    $task = new FindValue($this->settings, $needle_id);
    return $task->enqueue();
  }


  public function store($key_id, $value, $expire) {
    $task = new Store($this->settings, $key_id, $value, $expire);
    return $task->enqueue();
  }


  public function jsonSerialize() {
    return $this->settings;
  }
}

?>
