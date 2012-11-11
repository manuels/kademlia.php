<?php

namespace Kademlia\MockHttp;
const protocol_id = -80;

class Protocol extends \Kademlia\Http\Protocol {
  public $protocol_id = protocol_id;

  public function updateKBuckets($request) {
    $request['protocols'][\Kademlia\Http\protocol_id] = $request['protocols'][protocol_id];
    return parent::updateKBuckets($request);
  }
}


function mock_download(&$settings, $urls) {
  $all_settings = $settings->supported_protocols[protocol_id]['all_settings'];

  foreach($urls as $u => $node) {
    foreach($all_settings as $remote_settings) {
      if($remote_settings->own_node_id === $node->idBin())
        break;
    }
    $remote_protocol = $remote_settings->instantiateProtocolById(protocol_id);

    parse_str($u, $vars);
    $request = json_decode($vars['q'], true);
    $res = $remote_protocol->processRequest($request);

    $results[$u] = [
      'node' => $node,
      'data' => $res
    ];
  }
  
  return $results;
}


class FindNode extends \Kademlia\Http\FindNode {
  public function download($urls) {
    return mock_download($this->settings, $urls);
  }
}

?>
