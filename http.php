<?php

namespace Kademlia\Http;
const protocol_id = 80;

class Protocol extends \Kademlia\Protocol {
  public $protocol_id = protocol_id;

  public function updateKBuckets($node) {
    if($this->settings->verbosity >= 4) {
      print "Updating kbuckets for ".\Kademlia\Node::binId2hex($this->settings->own_node_id)." with ".$node->idStr()."\n";
      var_dump($node->data);
    }

    $this->settings->kbuckets->nodeOnline($node);
  }


  public function parseSenderNode($request) {
    $keys = ['id' => '', 'protocols', ''];
    $data = [
      'id' => $request['id'],
      'protocols' => $request['protocols']
    ];

    $node = new \Kademlia\Node($data);
    assert(count($node->data['protocols']) > 0);
    return $node;
  }


  public function processRequest($request) {
    $sender_node = $this->parseSenderNode($request);
    $this->updateKBuckets($sender_node);
    
    switch($request['query']['type']) {
      case 'FIND_NODE':
        $needle_id = \Kademlia\Node::hexId2bin($request['query']['node_id']);
        $response = @json_encode($this->createFindNodeResponse($needle_id, $sender_node));
        break;
      case 'FIND_VALUE':
        $key_id = \Kademlia\Node::hexId2bin($request['query']['key_id']);
        $response = @json_encode($this->createFindValueResponse($key_id, $sender_node));
        break;
      case 'STORE':
        $key_id = \Kademlia\Node::hexId2bin($request['query']['key_id']);
        $value = base64_decode($request['query']['value']);
        $expire = $request['query']['expire'];
        $response = @json_encode($this->createStoreResponse($sender_node, $key_id, $value, $expire));
        break;
      default:
        $response = '{}';
        break;
    }

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
      $str = 'q='.urlencode(@json_encode([
        'id'        => \Kademlia\Node::binId2hex($this->settings->own_node_id),
        'protocols' => $this->settings->supported_protocols,
        'query'     => $data
      ]));

      $urls[$str] = $node;
    }
    $responses = $this->download($urls);

    if($this->settings->verbosity > 0) {
      print \Kademlia\Node::binId2hex($this->settings->own_node_id)." got these responses:\n";
      foreach($responses as $r)
        var_dump(json_decode($r['data'], true));
      print "# end of responses\n";
    }

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
    $result = json_decode($encoded_result, true);
    if($result === NULL)
      $result = [];

    $nodes = [];
    foreach($result['node_array'] as $data) {
      $n = new \Kademlia\Node($data);
      assert(count($data['protocols']) > 0);

      if(count($n->data['protocols']) === 0) {
        print_r($result['node_array']);
        assert(false);
      }

      if($n->isValid())
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
      $node_list->addNodeList($nodes);
    }

    $emitted_result = [ 'node_list' => $node_list ];

    if($this->settings->verbosity >= 4) {
      print \Kademlia\Node::binId2hex($this->settings->own_node_id).": FindNode process found this result\n";
      var_dump($emitted_result['node_list']);
      print \Kademlia\Node::binId2hex($this->settings->own_node_id).": end of FindNode process result\n";
    }

    $this->emit('done', $emitted_result);
  }
}

?>
