<?php

namespace Kademlia\Http;
const protocol_id = 80;

class Protocol extends \Kademlia\Protocol {
  public $protocol_id = protocol_id;

  public function updateKBuckets($request) {
    $data = [
      'id' => $request['id'],
      'protocols' => $request['protocols']
    ];
    $node = new \Kademlia\Node($data);

    $kbuckets_size = $this->settings->kbuckets->toNodeList()->size();
    $this->settings->kbuckets->nodeOnline($node);
    $kbuckets_size = $this->settings->kbuckets->toNodeList()->size();
  }


  public function processRequest($request) {
    $this->updateKBuckets($request);
    
    switch($request['query']['type']) {
      case 'FIND_NODE':
        $needle_id = \Kademlia\Node::hexId2bin($request['query']['node_id']);
        $response = json_encode($this->createFindNodeResponse($needle_id));
        break;
      default:
        $response = '{}';
        break;
    }

    var_dump(\Kademlia\Node::binId2hex($this->settings->own_node_id).' got REQUEST:', $request, 'sending RESPONSE', $response);
    return $response;
  }
}


class HttpTask extends \Kademlia\Task {
  function __construct(&$settings, $recipients_node_list) {
    assert(gettype($recipients_node_list) === 'object');
    assert(get_class($recipients_node_list) === 'Kademlia\NodeList');

    parent::__construct($settings);
    $this->recipients_node_list = $recipients_node_list;
  }


  public function perform($data) {
    $urls = [];
    foreach($this->recipients_node_list->toArray() as $node) {
      $str = 'q='.urlencode(json_encode([
        'id'        => \Kademlia\Node::binId2hex($this->settings->own_node_id),
        'protocols' => $this->settings->supported_protocols,
        'query'     => $data
      ]));
      $urls[$str] = $node;
    }
    $responses = $this->download($urls);

    foreach($responses as $resp) {
      if($resp['data'] !== NULL)
        $this->settings->kbuckets->nodeOnline($resp['node']);
      else
        $this->settings->kbuckets->nodeOffline($resp['node']);
    }

    return $responses;
  }


  public function enqueueSelf() {
    return parent::enqueueSelfPrototype();
  }


  public function download($urls) {
    $results = [];
    foreach($urls as $u => $node) {
      $results[$u] = ['node' => $node, 'data' => NULL];
    }

    return $results;
  }
}



class Find extends HttpTask {
  function __construct(&$settings, $needle_id, $recipients_node_list) {
    parent::__construct($settings, $recipients_node_list);

    $n = new \Kademlia\Node(['id' => $needle_id]);
    $this->needle_id = $n->idBin();
  }


  public function enqueueSelf() {
    return parent::enqueueSelfPrototype();
  }


  public function parseNodeList($encoded_result = '{}') {
    $result = json_decode($encoded_result, false);
    if($result === NULL)
      $result = [];

    $nodes = [];
    foreach($result as $data) {
      $n = new \Kademlia\Node($data);
      array_push($nodes, $n);
    }
  
    return new \Kademlia\NodeList($nodes);
  }
}



class FindNode extends Find {
  public function perform() {
    $data = [
      'type' => 'FIND_NODE',
      'node_id' => \Kademlia\Node::binId2hex($this->needle_id)
    ];

    $results = parent::perform($data);

    $node_list = new \Kademlia\NodeList;
    foreach($results as $res) {
      $nodes = $this->parseNodeList($res['data']);
      $node_list->addNode($nodes);
    }

    $emitted_result = [ 'node_list' => $node_list ];
    $this->emit('done', $emitted_result);
  }
}

?>
