<?php

namespace Kademlia;

class Protocol {
  function __construct(&$settings) {
    $this->settings = $settings;
  }

  static function instantiateByProtocolId($settings, $prot_id) {
    switch($prot_id) {
      case 80:
        return new Http($settings);
    }
    return NULL;
  }


  public function sendPingRequest($node) {
    throw 'Protocol::sendPingRequest is abstract';
  }
}

?>
