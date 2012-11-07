<?php

namespace Kademlia;

class Node implements \JsonSerializable {
  function __construct($data, $hard_host = NULL) {
    $this->hard_host = $hard_host; # IP address as found in layer 3 of packets
    $this->data = $data;

    if(isset($this->data['id']))
      if(strlen($this->data['id']) === N/8)
        $this->data['binary_id'] = $data['id'];
      else
        $this->data['binary_id'] = self::hexId2bin($this->data['id']);
  }


  static function randomNodeId() {
    $id = '';
    for($i = 0; $i < N/8; $i++)
      $id .= chr(rand(0, 255));
    return $id;
  }


  public function favoriteProtocolId($settings) {
    $protocol_ids = array_intersect(array_keys($this->data['protocols']), $settings->supported_protocols);
    if(count($protocol_ids) === 0)
      return NULL;

    return $protocol_ids[0];
  }


  public function favoriteProtocol($settings) {
    $protocol_id = $this->favoriteProtocolId($settings);
    if($protocol_id === NULL)
      return NULL;

    return Protocol::instantiateByProtocolId($settings, $protocol_id);
  }


  static function hexId2bin($string_id) {
    if(strlen($string_id) === N/8) {
      $binary_id = $string_id;
      return $binary_id;
    }
    if(strlen($string_id) !== 2*N/8) {
      return NULL;
    }

    $binary_id = '';
    for($i = 0; $i < N/8; $i++)
      $binary_id .= chr(hexdec( substr($string_id, 2*$i, 2) ));
    return $binary_id;
  }


  static function binId2hex($binary_id) {
    $string_id = '';
    for($i = 0; $i < N/8; $i++)
      $string_id .= str_pad( dechex($binary_id[$i]), 2, '0', STR_PAD_LEFT);
    return $string_id;
  }


  public function isValid() {
    $ok = true;
    $ok = $ok && isset($this->data['binary_id']);
    $ok = $ok && (strlen($this->idBin()) === N/8);

    return $ok;
  }


  public function sendPingRequest($protocol = NULL) {
    if($protocol === NULL)
      $protocol = $this->favoriteProtocol();
    return $protocol->sendPingRequest($this);
  }


  public function host() {
    return $this->hard_host;
  }


  public function idBin() {
    return $this->data['binary_id'];
  }


  public function idStr() {
    return self::binId2hex($this->data['binary_id']);
  }


  public function distanceTo($other_node) {
    if(is_string($other_node))
      $other_node = new Node(['id' => $other_node]);

    $other_node_id = $other_node->idBin();
    $own_node_id = $this->idBin();

    return ($own_node_id ^ $other_node_id);
  }


  public function logDistanceTo($other_node) {
    $dist = $this->distanceTo($other_node);
    for($i = 0; $i < N/8; $i++) {
      $byte = ord($dist[$i]);
      if($byte !== 0) {
        for($j = 8; $j >= 1; $j--) {
          $mask = 1 << ($j-1);
          if(($byte & $mask) === $mask)
            return 8*(N/8-$i-1)+$j;
        }
        assert('THIS MAY NEVER HAPPEN');
      }
    }
    return 0;
  }

  public function jsonSerialize() {
    return $data;
  }
}

?>
